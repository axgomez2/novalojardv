<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingCarrier;
use App\Services\MelhorEnvioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingSettingsController extends Controller
{
    /**
     * Exibir página de configurações de frete
     */
    public function index()
    {
        $carriers = ShippingCarrier::orderBy('sort_order')->get();
        
        $settings = DB::table('shipping_settings')
            ->pluck('value', 'key')
            ->toArray();

        return view('admin.settings.shipping.index', compact('carriers', 'settings'));
    }

    /**
     * Atualizar configurações gerais
     */
    public function updateSettings(Request $request)
    {
        $fields = [
            'sender_postal_code',
            'melhor_envio_token',
            'sandbox_mode',
            'preorder_additional_days',
            'preorder_message',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                
                if ($field === 'sandbox_mode') {
                    $value = $request->boolean('sandbox_mode') ? '1' : '0';
                }

                DB::table('shipping_settings')->updateOrInsert(
                    ['key' => $field],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        }

        return redirect()->route('admin.settings.shipping.index')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Sincronizar transportadoras do Melhor Envio
     */
    public function syncCarriers(MelhorEnvioService $melhorEnvio): JsonResponse
    {
        $result = $melhorEnvio->listAvailableCarriers();

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ]);
        }

        $count = 0;
        foreach ($result as $service) {
            if (!isset($service['id']) || !isset($service['name'])) {
                continue;
            }

            ShippingCarrier::updateOrCreate(
                ['melhor_envio_id' => (string) $service['id']],
                [
                    'name' => $service['name'],
                    'company' => $service['company']['name'] ?? 'Desconhecido',
                    'logo' => $service['company']['picture'] ?? null,
                ]
            );
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Sincronizadas {$count} transportadoras.",
        ]);
    }

    /**
     * Ativar/desativar transportadora
     */
    public function toggleCarrier(ShippingCarrier $carrier)
    {
        $carrier->update(['is_active' => !$carrier->is_active]);

        return redirect()->route('admin.settings.shipping.index')
            ->with('success', 'Transportadora ' . ($carrier->is_active ? 'ativada' : 'desativada') . '!');
    }

    /**
     * Atualizar campo de transportadora
     */
    public function updateCarrier(Request $request, ShippingCarrier $carrier)
    {
        $field = $request->input('field');
        $allowedFields = ['additional_cost', 'additional_percentage', 'additional_days', 'sort_order'];

        if (!in_array($field, $allowedFields)) {
            return redirect()->route('admin.settings.shipping.index')
                ->with('error', 'Campo inválido.');
        }

        $carrier->update([
            $field => $request->input($field),
        ]);

        return redirect()->route('admin.settings.shipping.index')
            ->with('success', 'Transportadora atualizada!');
    }
}
