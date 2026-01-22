<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Processar webhook do Mercado Pago
     */
    public function mercadoPago(Request $request, MercadoPagoService $mercadoPago): JsonResponse
    {
        $data = $request->all();

        $result = $mercadoPago->processWebhook($data);

        if ($result) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 400);
    }
}
