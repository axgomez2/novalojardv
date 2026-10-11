<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientCart;
use App\Models\VinylStock;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Retorna o carrinho do usuário autenticado.
     * Valida estoque e ajusta/remove itens automaticamente.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->load([
            'items.vinylStock.vinylMaster.mainArtists',
            'items.vinylStock.vinylMaster.vinylImages',
        ]);

        // Validar e ajustar estoque de cada item
        $stockIssues = $this->validateAndAdjustCartStock($cart);

        $response = [
            'data' => $this->formatCart($cart),
        ];

        // Incluir avisos de ajustes de estoque
        if (!empty($stockIssues)) {
            $response['stock_warnings'] = $stockIssues;
        }

        return response()->json($response);
    }

    /**
     * Valida o estoque de cada item no carrinho e ajusta automaticamente.
     * Retorna array de avisos sobre itens removidos ou ajustados.
     */
    protected function validateAndAdjustCartStock(ClientCart $cart): array
    {
        $issues = [];

        foreach ($cart->items as $item) {
            $stock = $item->vinylStock;
            
            // Se o produto não existe mais ou foi removido
            if (!$stock) {
                $issues[] = [
                    'type' => 'removed',
                    'message' => 'Um item foi removido do carrinho pois não está mais disponível.',
                    'item_id' => $item->id,
                ];
                $item->delete();
                continue;
            }

            // Pular validação para pré-vendas
            if ($stock->availability === 'preorder') {
                continue;
            }

            // Se estoque zerou, remover item
            if ($stock->stock <= 0) {
                $issues[] = [
                    'type' => 'removed',
                    'message' => "'{$stock->vinylMaster?->title}' foi removido do carrinho pois está esgotado.",
                    'item_id' => $item->id,
                    'title' => $stock->vinylMaster?->title,
                ];
                $item->delete();
                continue;
            }

            // Se quantidade no carrinho excede estoque, ajustar
            if ($item->quantity > $stock->stock) {
                $oldQty = $item->quantity;
                $item->update(['quantity' => $stock->stock]);
                $issues[] = [
                    'type' => 'adjusted',
                    'message' => "Quantidade de '{$stock->vinylMaster?->title}' ajustada de {$oldQty} para {$stock->stock} (estoque disponível).",
                    'item_id' => $item->id,
                    'title' => $stock->vinylMaster?->title,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $stock->stock,
                ];
            }
        }

        // Recarregar itens após ajustes
        if (!empty($issues)) {
            $cart->load([
                'items.vinylStock.vinylMaster.mainArtists',
                'items.vinylStock.vinylMaster.vinylImages',
            ]);
        }

        return $issues;
    }

    /**
     * Adiciona ou incrementa um item.
     * Se 'replace' = true, define quantidade absoluta.
     */
    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'vinyl_stock_id' => 'required|exists:vinyl_stocks,id',
            'quantity'       => 'nullable|integer|min:1',
            'replace'        => 'nullable|boolean',
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $stock = VinylStock::findOrFail($data['vinyl_stock_id']);
        $qty = $data['quantity'] ?? 1;

        // Validar estoque (exceto pré-venda)
        if ($stock->availability !== 'preorder') {
            $existing = $cart->items()->where('vinyl_stock_id', $stock->id)->first();
            $currentQty = $existing ? $existing->quantity : 0;
            $newTotalQty = $request->boolean('replace') ? $qty : ($currentQty + $qty);

            if ($newTotalQty > $stock->stock) {
                return response()->json([
                    'message' => "Quantidade indisponível. Estoque atual: {$stock->stock} unidade(s).",
                    'available_stock' => $stock->stock,
                    'current_in_cart' => $currentQty,
                ], 422);
            }

            // Verificar se o produto está disponível para venda
            if ($stock->stock <= 0) {
                return response()->json([
                    'message' => 'Produto esgotado.',
                ], 422);
            }
        }

        if ($request->boolean('replace')) {
            $existing = $cart->items()->where('vinyl_stock_id', $stock->id)->first();
            if ($existing) {
                $existing->update(['quantity' => $qty, 'unit_price' => $stock->current_price]);
            } else {
                $cart->items()->create([
                    'vinyl_stock_id' => $stock->id,
                    'quantity'       => $qty,
                    'unit_price'     => $stock->current_price,
                ]);
            }
        } else {
            $cart->addItem($stock, $qty);
        }

        return $this->index($request);
    }

    /**
     * Atualiza a quantidade de um item.
     */
    public function updateItem(Request $request, int $vinylStockId)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();

        $item = $cart->items()->where('vinyl_stock_id', $vinylStockId)->first();
        if (!$item) {
            return response()->json(['message' => 'Item não encontrado.'], 404);
        }

        $stock = $item->vinylStock;
        $newQty = $data['quantity'];

        // Validar estoque (exceto pré-venda)
        if ($stock && $stock->availability !== 'preorder') {
            if ($newQty > $stock->stock) {
                return response()->json([
                    'message' => "Quantidade indisponível. Estoque atual: {$stock->stock} unidade(s).",
                    'available_stock' => $stock->stock,
                    'requested' => $newQty,
                ], 422);
            }
        }

        $item->update(['quantity' => $newQty]);

        return $this->index($request);
    }

    /**
     * Remove um item.
     */
    public function destroyItem(Request $request, int $vinylStockId)
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->items()->where('vinyl_stock_id', $vinylStockId)->delete();

        return $this->index($request);
    }

    /**
     * Limpa o carrinho.
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart;
        if ($cart) {
            $cart->clear();
        }

        return response()->json(['data' => ['items' => [], 'subtotal' => 0]]);
    }

    /**
     * Sincroniza um array de itens do localStorage com o carrinho do servidor.
     * Estratégia: para cada item enviado, faz upsert mantendo a maior quantidade.
     * Não remove itens já existentes no servidor.
     * Respeita o limite de estoque.
     */
    public function sync(Request $request)
    {
        $data = $request->validate([
            'items'              => 'required|array',
            'items.*.id'         => 'required|integer|exists:vinyl_stocks,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $errors = [];

        foreach ($data['items'] as $payload) {
            $stock = VinylStock::find($payload['id']);
            if (!$stock) continue;

            $existing = $cart->items()->where('vinyl_stock_id', $stock->id)->first();
            $qty = (int) $payload['quantity'];

            // Limitar quantidade ao estoque disponível (exceto pré-venda)
            if ($stock->availability !== 'preorder' && $qty > $stock->stock) {
                $qty = $stock->stock;
                if ($qty <= 0) {
                    $errors[] = "'{$stock->vinylMaster?->title}' está esgotado.";
                    continue;
                }
                $errors[] = "'{$stock->vinylMaster?->title}' ajustado para {$qty} unidade(s) (estoque limitado).";
            }

            if ($existing) {
                // mantém a maior quantidade entre local e servidor, respeitando estoque
                $finalQty = max($existing->quantity, $qty);
                if ($stock->availability !== 'preorder' && $finalQty > $stock->stock) {
                    $finalQty = $stock->stock;
                }
                $existing->update([
                    'quantity'   => $finalQty,
                    'unit_price' => $stock->current_price,
                ]);
            } else {
                $cart->items()->create([
                    'vinyl_stock_id' => $stock->id,
                    'quantity'       => $qty,
                    'unit_price'     => $stock->current_price,
                ]);
            }
        }

        $response = $this->index($request)->getData(true);
        
        if (!empty($errors)) {
            $response['warnings'] = $errors;
        }

        return response()->json($response);
    }

    /**
     * Formata o carrinho para resposta da API.
     */
    private function formatCart(ClientCart $cart): array
    {
        $items = $cart->items->map(function ($item) {
            $stock = $item->vinylStock;
            $master = $stock?->vinylMaster;

            $cover = null;
            if ($master) {
                $primary = $master->vinylImages->first(fn ($img) => $img->is_primary);
                $cover = $primary?->full_url
                    ?? $master->vinylImages->first()?->full_url
                    ?? $master->cover_url;
            }

            return [
                'id'             => $stock?->id,
                'cart_item_id'   => $item->id,
                'title'          => $master?->title,
                'artist'         => $master?->artist_names,
                'price'          => (float) $item->unit_price,
                'quantity'       => $item->quantity,
                'image'          => $cover ?? '/images/vinyl-placeholder.jpg',
                'format'         => $master?->format,
                'stock'          => $stock?->stock ?? 0,
                'isPreorder'     => $stock?->availability === 'preorder',
                'releaseDate'    => $stock?->release_date?->format('Y-m-d'),
                'isPromotional'  => (bool) ($stock?->isOnPromotion() ?? false),
                'originalPrice'  => $stock?->isOnPromotion() ? (float) $stock->sell_price : null,
            ];
        })->values();

        return [
            'items'    => $items,
            'count'    => $items->sum('quantity'),
            'subtotal' => (float) $cart->subtotal,
        ];
    }
}
