<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Processar webhook do Mercado Pago
     */
    public function mercadoPago(Request $request, MercadoPagoService $mercadoPago): JsonResponse
    {
        // Valida assinatura HMAC (se secret estiver configurado)
        if (!$this->verifyMercadoPagoSignature($request)) {
            Log::warning('Mercado Pago webhook: assinatura inválida', [
                'ip' => $request->ip(),
                'x-signature' => $request->header('x-signature'),
            ]);
            return response()->json(['status' => 'invalid signature'], 401);
        }

        $data = $request->all();

        // Processa mas sempre responde 200: assinatura já foi validada acima.
        // Retornar 4xx faria o Mercado Pago retentar por horas para IDs inválidos
        // (ex: payloads de teste do painel). Erros reais ficam no log.
        try {
            $processed = $mercadoPago->processWebhook($data);
            return response()->json([
                'status'    => 'ok',
                'processed' => $processed,
            ]);
        } catch (\Throwable $e) {
            Log::error('Mercado Pago webhook: exceção no processamento', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
            // 200 mesmo assim: evita retries. O erro já ficou logado para investigação.
            return response()->json(['status' => 'logged']);
        }
    }

    /**
     * Verifica a assinatura x-signature do Mercado Pago.
     * Template: "id:<data.id>;request-id:<x-request-id>;ts:<ts>;"
     * HMAC SHA256 do template com o segredo (hex).
     *
     * Retorna true se o secret não estiver configurado (bypass em dev).
     */
    protected function verifyMercadoPagoSignature(Request $request): bool
    {
        $secret = config('services.mercado_pago.webhook_secret');
        if (empty($secret)) {
            return true; // não configurado → aceita (desenvolvimento)
        }

        $signatureHeader = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $dataId = $request->query('data.id') ?? ($request->input('data.id') ?? '');

        if (!$signatureHeader || !$requestId) {
            return false;
        }

        // Parse: "ts=1704908010,v1=abcdef..."
        $parts = [];
        foreach (explode(',', $signatureHeader) as $piece) {
            [$k, $v] = array_pad(explode('=', trim($piece), 2), 2, null);
            if ($k && $v !== null) {
                $parts[$k] = $v;
            }
        }

        $ts = $parts['ts'] ?? null;
        $v1 = $parts['v1'] ?? null;
        if (!$ts || !$v1) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $v1);
    }
}
