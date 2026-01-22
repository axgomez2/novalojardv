<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VinylStock;
use App\Services\MelhorEnvioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    /**
     * Calcular frete para os itens do carrinho
     */
    public function calculate(Request $request, MelhorEnvioService $melhorEnvio): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'postal_code' => 'required|string|min:8|max:9',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:vinyl_stocks,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'postal_code.required' => 'O CEP é obrigatório.',
            'postal_code.min' => 'CEP inválido.',
            'items.required' => 'É necessário informar os itens.',
            'items.min' => 'É necessário pelo menos um item.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $postalCode = preg_replace('/\D/', '', $request->postal_code);

        // Buscar os produtos do banco para obter formato e preço correto
        $itemIds = collect($request->items)->pluck('id')->toArray();
        $stocks = VinylStock::with('vinylMaster')->whereIn('id', $itemIds)->get()->keyBy('id');

        $products = [];
        $hasPreorder = false;
        $latestPreorderDate = null;

        foreach ($request->items as $item) {
            $stock = $stocks->get($item['id']);
            
            if (!$stock) {
                continue;
            }

            // Verificar se é pré-venda
            if ($stock->availability === 'preorder') {
                $hasPreorder = true;
                if ($stock->release_date && (!$latestPreorderDate || $stock->release_date > $latestPreorderDate)) {
                    $latestPreorderDate = $stock->release_date;
                }
            }

            // Usar preço promocional se disponível
            $price = $stock->current_price;

            // Determinar formato do disco
            $format = $stock->format ?? 'LP';

            $products[] = [
                'id' => $stock->id,
                'format' => $format,
                'price' => $price,
                'quantity' => $item['quantity'],
            ];
        }

        if (empty($products)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum produto válido encontrado.',
            ], 400);
        }

        // Calcular dimensões dos produtos
        $formattedProducts = MelhorEnvioService::calculateProductDimensions($products);

        // Calcular frete
        $result = $melhorEnvio->calculateShipping($postalCode, $formattedProducts);

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 400);
        }

        // Adicionar informações de pré-venda se houver
        $response = [
            'success' => true,
            'data' => [
                'quotes' => $result['quotes'] ?? [],
                'has_preorder' => $hasPreorder,
                'preorder_date' => $latestPreorderDate?->format('Y-m-d'),
                'preorder_date_formatted' => $latestPreorderDate?->format('d/m/Y'),
            ],
        ];

        // Se tem pré-venda, ajustar os prazos de entrega
        if ($hasPreorder && $latestPreorderDate) {
            $daysUntilRelease = now()->diffInDays($latestPreorderDate, false);
            $additionalDays = max(0, $daysUntilRelease) + 3; // +3 dias para processamento

            foreach ($response['data']['quotes'] as &$quote) {
                $quote['original_delivery_time'] = $quote['delivery_time'];
                $quote['delivery_time'] = $quote['delivery_time'] + $additionalDays;
                $quote['preorder_note'] = "Prazo considera a data de lançamento ({$latestPreorderDate->format('d/m/Y')})";
            }
        }

        return response()->json($response);
    }
}
