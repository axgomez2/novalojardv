<?php

namespace App\Services;

use App\Models\ClientOrder;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected string $baseUrl = 'https://api.mercadopago.com';
    protected ?string $accessToken;
    protected ?string $publicKey;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->accessToken = config('services.mercado_pago.access_token');
        $this->publicKey = config('services.mercado_pago.public_key');
        $this->isSandbox = config('services.mercado_pago.sandbox', true);
    }

    /**
     * Criar preferência de pagamento (Checkout Pro)
     */
    public function createPreference(ClientOrder $order): array
    {
        if (!$this->accessToken) {
            return ['error' => 'Token do Mercado Pago não configurado'];
        }

        try {
            $items = $order->items->map(function ($item) {
                return [
                    'id' => (string) $item->vinyl_stock_id,
                    'title' => $item->vinylStock?->vinylMaster?->full_title ?? 'Disco de Vinil',
                    'description' => $item->vinylStock?->format ?? 'LP',
                    'picture_url' => $item->vinylStock?->vinylMaster?->cover_url,
                    'category_id' => 'music',
                    'quantity' => $item->quantity,
                    'currency_id' => 'BRL',
                    'unit_price' => (float) $item->unit_price,
                ];
            })->toArray();

            if ($order->shipping_cost > 0) {
                $items[] = [
                    'id' => 'shipping',
                    'title' => 'Frete - ' . ($order->shipping_service_name ?? 'Envio'),
                    'category_id' => 'shipping',
                    'quantity' => 1,
                    'currency_id' => 'BRL',
                    'unit_price' => (float) $order->shipping_cost,
                ];
            }

            $frontendUrl = config('app.frontend_url') ?: env('FRONTEND_URL', 'http://localhost:3000');
            $backendUrl = config('app.url') ?: env('APP_URL', 'http://localhost');
            
            // Verifica se está em localhost (Mercado Pago não aceita auto_return com localhost)
            $isLocalhost = str_contains($frontendUrl, 'localhost') || str_contains($frontendUrl, '127.0.0.1');

            $payload = [
                'items' => $items,
                'payer' => [
                    'name' => $order->clientUser?->name ?? $order->guest_name,
                    'email' => $order->clientUser?->email ?? $order->guest_email,
                ],
                'external_reference' => $order->order_number,
                'statement_descriptor' => 'LOJA DISCOS',
            ];

            // Só adiciona back_urls e auto_return se NÃO for localhost
            if (!$isLocalhost) {
                $payload['back_urls'] = [
                    'success' => $frontendUrl . '/pedido/' . $order->order_number . '/sucesso',
                    'failure' => $frontendUrl . '/pedido/' . $order->order_number . '/erro',
                    'pending' => $frontendUrl . '/pedido/' . $order->order_number . '/pendente',
                ];
                $payload['auto_return'] = 'approved';
                $payload['notification_url'] = $backendUrl . '/api/webhooks/mercadopago';
            }

            Log::info('Mercado Pago - Criando preferência', [
                'order' => $order->order_number,
                'frontend_url' => $frontendUrl,
                'is_localhost' => $isLocalhost,
                'back_urls' => $payload['back_urls'] ?? 'não configurado (localhost)',
            ]);

            $response = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/checkout/preferences', $payload);

            if ($response->failed()) {
                Log::error('Mercado Pago - Erro ao criar preferência', [
                    'order' => $order->order_number,
                    'response' => $response->body(),
                ]);
                return ['error' => 'Erro ao criar preferência de pagamento'];
            }

            $data = $response->json();

            // Usa sandbox_init_point em ambiente de teste
            $initPoint = $this->isSandbox 
                ? ($data['sandbox_init_point'] ?? $data['init_point'])
                : $data['init_point'];

            return [
                'preference_id' => $data['id'],
                'init_point' => $initPoint,
                'sandbox_init_point' => $data['sandbox_init_point'] ?? null,
                'is_sandbox' => $this->isSandbox,
            ];
        } catch (\Exception $e) {
            Log::error('Mercado Pago - Exceção', ['error' => $e->getMessage()]);
            return ['error' => 'Erro ao conectar com Mercado Pago: ' . $e->getMessage()];
        }
    }

    /**
     * Criar preferência de pagamento genérica (Checkout Pro) — útil para
     * pagamentos parciais como sinal/saldo de pré-vendas.
     *
     * @param string $title Título que aparece no checkout (ex: "Sinal da pré-venda PV-2026-00001")
     * @param float $amount Valor único
     * @param string $externalReference ex: "PREORDER-123-SIGNAL" ou "PREORDER-123-BALANCE"
     * @param array $payer ['name' => ..., 'email' => ...]
     * @param string|null $returnPath rota do frontend para back_urls (ex: "/minhas-pre-vendas/PV-2026-00001")
     */
    public function createGenericPreference(string $title, float $amount, string $externalReference, array $payer, ?string $returnPath = null): array
    {
        if (!$this->accessToken) {
            return ['error' => 'Token do Mercado Pago não configurado'];
        }

        try {
            $frontendUrl = config('app.frontend_url') ?: env('FRONTEND_URL', 'http://localhost:3000');
            $backendUrl = config('app.url') ?: env('APP_URL', 'http://localhost');
            $isLocalhost = str_contains($frontendUrl, 'localhost') || str_contains($frontendUrl, '127.0.0.1');

            $payload = [
                'items' => [[
                    'id' => $externalReference,
                    'title' => $title,
                    'category_id' => 'music',
                    'quantity' => 1,
                    'currency_id' => 'BRL',
                    'unit_price' => round($amount, 2),
                ]],
                'payer' => [
                    'name' => $payer['name'] ?? null,
                    'email' => $payer['email'] ?? null,
                ],
                'external_reference' => $externalReference,
                'statement_descriptor' => 'LOJA DISCOS',
            ];

            if (!$isLocalhost && $returnPath) {
                $payload['back_urls'] = [
                    'success' => $frontendUrl . $returnPath . '?status=sucesso',
                    'failure' => $frontendUrl . $returnPath . '?status=erro',
                    'pending' => $frontendUrl . $returnPath . '?status=pendente',
                ];
                $payload['auto_return'] = 'approved';
                $payload['notification_url'] = $backendUrl . '/api/webhooks/mercadopago';
            }

            $response = Http::withToken($this->accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/checkout/preferences', $payload);

            if ($response->failed()) {
                Log::error('Mercado Pago - Erro ao criar preferência genérica', [
                    'ref' => $externalReference,
                    'response' => $response->body(),
                ]);
                return ['error' => 'Erro ao criar preferência de pagamento'];
            }

            $data = $response->json();
            $initPoint = $this->isSandbox
                ? ($data['sandbox_init_point'] ?? $data['init_point'])
                : $data['init_point'];

            return [
                'preference_id' => $data['id'],
                'init_point' => $initPoint,
                'is_sandbox' => $this->isSandbox,
            ];
        } catch (\Exception $e) {
            Log::error('Mercado Pago - Exceção preferência genérica', ['error' => $e->getMessage()]);
            return ['error' => 'Erro ao conectar com Mercado Pago: ' . $e->getMessage()];
        }
    }

    /**
     * Criar pagamento PIX usando a API de Orders (funciona com credenciais de teste)
     */
    public function createPixPayment(ClientOrder $order): array
    {
        if (!$this->accessToken) {
            return ['error' => 'Token do Mercado Pago não configurado'];
        }

        try {
            // Usa a nova API /v1/orders para sandbox (funciona com credenciais de teste)
            if ($this->isSandbox) {
                return $this->createPixPaymentWithOrders($order);
            }
            
            // Em produção, usa a API /v1/payments tradicional
            return $this->createPixPaymentWithPayments($order);
        } catch (\Exception $e) {
            Log::error('Mercado Pago - Exceção PIX', ['error' => $e->getMessage()]);
            return ['error' => 'Erro ao criar pagamento PIX: ' . $e->getMessage()];
        }
    }

    /**
     * Criar pagamento PIX usando API /v1/orders (para sandbox)
     */
    protected function createPixPaymentWithOrders(ClientOrder $order): array
    {
        $payerEmail = $order->clientUser?->email ?? $order->guest_email;
        $payerName = $order->clientUser?->name ?? $order->guest_name;
        
        // Para testes no sandbox, usar email de teste obrigatório (@testuser.com)
        // O first_name "APRO" faz o pagamento ser aprovado automaticamente
        $sandboxEmail = 'test_user_' . $order->id . '@testuser.com';
        
        $payload = [
            'type' => 'online',
            'external_reference' => $order->order_number,
            'total_amount' => number_format((float) $order->total, 2, '.', ''),
            'payer' => [
                'email' => $sandboxEmail, // Email obrigatório para sandbox
                'first_name' => 'APRO', // Isso faz o pagamento ser aprovado no sandbox
            ],
            'transactions' => [
                'payments' => [
                    [
                        'amount' => number_format((float) $order->total, 2, '.', ''),
                        'payment_method' => [
                            'id' => 'pix',
                            'type' => 'bank_transfer',
                        ],
                    ],
                ],
            ],
        ];

        Log::info('Mercado Pago - Criando PIX via Orders API', [
            'order' => $order->order_number,
            'payload' => $payload,
        ]);

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'pix-order-' . $order->order_number . '-' . time(),
            ])
            ->post($this->baseUrl . '/v1/orders', $payload);

        if ($response->failed()) {
            Log::error('Mercado Pago - Erro ao criar PIX via Orders', [
                'order' => $order->order_number,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            return ['error' => 'Erro ao criar pagamento PIX: ' . $response->body()];
        }

        $data = $response->json();

        Log::info('Mercado Pago - PIX criado via Orders', [
            'order' => $order->order_number,
            'response' => $data,
        ]);

        // Extrair dados do pagamento da resposta
        $paymentData = $data['transactions']['payments'][0] ?? [];
        $paymentMethod = $paymentData['payment_method'] ?? [];

        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'payment_method' => 'pix',
            'mercado_pago_id' => $data['id'] ?? $paymentData['id'] ?? null,
            'mercado_pago_status' => $data['status'] ?? 'pending',
            'mercado_pago_status_detail' => $data['status_detail'] ?? null,
            'amount' => (float) $order->total,
            'pix_qr_code' => $paymentMethod['qr_code'] ?? null,
            'pix_qr_code_base64' => $paymentMethod['qr_code_base64'] ?? null,
            'pix_expiration' => now()->addMinutes(30),
            'payment_response' => $data,
        ]);

        // A URL do ticket pode ser usada para mostrar o QR code
        $ticketUrl = $paymentMethod['ticket_url'] ?? null;

        return [
            'payment_id' => $payment->id,
            'mercado_pago_id' => $data['id'] ?? $paymentData['id'] ?? null,
            'status' => $data['status'] ?? 'action_required',
            'qr_code' => $paymentMethod['qr_code'] ?? null,
            'qr_code_base64' => $paymentMethod['qr_code_base64'] ?? null,
            'ticket_url' => $ticketUrl,
            'expiration' => $payment->pix_expiration?->toISOString(),
            'is_sandbox' => $this->isSandbox,
        ];
    }

    /**
     * Criar pagamento PIX usando API /v1/payments (para produção)
     */
    protected function createPixPaymentWithPayments(ClientOrder $order): array
    {
        $payload = [
            'transaction_amount' => (float) $order->total,
            'description' => 'Pedido #' . $order->order_number . ' - Loja de Discos',
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $order->clientUser?->email ?? $order->guest_email,
                'first_name' => explode(' ', $order->clientUser?->name ?? $order->guest_name)[0] ?? 'Cliente',
                'last_name' => explode(' ', $order->clientUser?->name ?? $order->guest_name, 2)[1] ?? '',
            ],
            'external_reference' => $order->order_number,
            'notification_url' => config('app.url') . '/api/webhooks/mercadopago',
        ];

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'pix-' . $order->order_number . '-' . time(),
            ])
            ->post($this->baseUrl . '/v1/payments', $payload);

        if ($response->failed()) {
            Log::error('Mercado Pago - Erro ao criar PIX', [
                'order' => $order->order_number,
                'response' => $response->body(),
            ]);
            return ['error' => 'Erro ao criar pagamento PIX'];
        }

        $data = $response->json();

        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'payment_method' => 'pix',
            'mercado_pago_id' => $data['id'],
            'mercado_pago_status' => $data['status'],
            'mercado_pago_status_detail' => $data['status_detail'] ?? null,
            'amount' => $data['transaction_amount'],
            'pix_qr_code' => $data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'pix_qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
            'pix_expiration' => isset($data['date_of_expiration']) ? now()->parse($data['date_of_expiration']) : now()->addMinutes(30),
            'payment_response' => $data,
        ]);

        return [
            'payment_id' => $payment->id,
            'mercado_pago_id' => $data['id'],
            'status' => $data['status'],
            'qr_code' => $data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
            'expiration' => $payment->pix_expiration?->toISOString(),
        ];
    }

    /**
     * Processar webhook do Mercado Pago
     */
    public function processWebhook(array $data): bool
    {
        if (!isset($data['data']['id']) || !isset($data['type'])) {
            return false;
        }

        if ($data['type'] !== 'payment') {
            return true;
        }

        $paymentId = $data['data']['id'];

        try {
            $response = Http::withToken($this->accessToken)
                ->get($this->baseUrl . '/v1/payments/' . $paymentId);

            if ($response->failed()) {
                Log::error('Mercado Pago - Erro ao buscar pagamento', ['id' => $paymentId]);
                return false;
            }

            $paymentData = $response->json();
            $externalReference = $paymentData['external_reference'] ?? null;

            if (!$externalReference) {
                return false;
            }

            // Pré-vendas usam external_reference no formato PREORDER-{id}-{SIGNAL|BALANCE}
            if (str_starts_with($externalReference, 'PREORDER-')) {
                return $this->handlePreOrderPaymentWebhook($externalReference, $paymentId, $paymentData);
            }

            $order = ClientOrder::where('order_number', $externalReference)->first();

            if (!$order) {
                Log::warning('Mercado Pago - Pedido não encontrado', ['reference' => $externalReference]);
                return false;
            }

            $payment = OrderPayment::where('mercado_pago_id', $paymentId)->first();

            if ($payment) {
                $payment->update([
                    'mercado_pago_status' => $paymentData['status'],
                    'mercado_pago_status_detail' => $paymentData['status_detail'] ?? null,
                    'fee' => $paymentData['fee_details'][0]['amount'] ?? 0,
                    'net_amount' => $paymentData['transaction_details']['net_received_amount'] ?? $paymentData['transaction_amount'],
                    'paid_at' => $paymentData['status'] === 'approved' ? now() : null,
                    'payment_response' => $paymentData,
                ]);
            } else {
                OrderPayment::create([
                    'order_id' => $order->id,
                    'payment_method' => $paymentData['payment_method_id'] ?? 'unknown',
                    'mercado_pago_id' => $paymentId,
                    'mercado_pago_status' => $paymentData['status'],
                    'mercado_pago_status_detail' => $paymentData['status_detail'] ?? null,
                    'amount' => $paymentData['transaction_amount'],
                    'fee' => $paymentData['fee_details'][0]['amount'] ?? 0,
                    'net_amount' => $paymentData['transaction_details']['net_received_amount'] ?? $paymentData['transaction_amount'],
                    'installments' => $paymentData['installments'] ?? 1,
                    'payer_info' => $paymentData['payer'] ?? null,
                    'paid_at' => $paymentData['status'] === 'approved' ? now() : null,
                    'payment_response' => $paymentData,
                ]);
            }

            if ($paymentData['status'] === 'approved' && $order->status === 'pending') {
                $order->update(['status' => 'paid']);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Mercado Pago - Erro no webhook', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Tratamento de pagamento via webhook para pré-vendas.
     */
    protected function handlePreOrderPaymentWebhook(string $externalReference, string $paymentId, array $paymentData): bool
    {
        // PREORDER-{id}-SIGNAL ou PREORDER-{id}-BALANCE
        $parts = explode('-', $externalReference);
        if (count($parts) < 3) {
            Log::warning('Webhook PREORDER com formato inválido', ['ref' => $externalReference]);
            return false;
        }

        $preOrderId = (int) $parts[1];
        $kind = strtoupper($parts[2]); // SIGNAL ou BALANCE

        $preOrder = \App\Models\PreOrder::find($preOrderId);
        if (!$preOrder) {
            Log::warning('Webhook: pré-venda não encontrada', ['id' => $preOrderId]);
            return false;
        }

        $isApproved = ($paymentData['status'] ?? null) === 'approved';

        if ($kind === 'SIGNAL') {
            $preOrder->signal_payment_id = $paymentId;
            $preOrder->signal_payment_method = 'gateway';
            if ($isApproved && !$preOrder->signal_paid_at) {
                $preOrder->changeStatus(
                    \App\Enums\PreOrderStatus::SignalPaid,
                    'Sinal pago via Mercado Pago (webhook)',
                    'system'
                );
            } else {
                $preOrder->save();
            }
        } elseif ($kind === 'BALANCE') {
            $preOrder->balance_payment_id = $paymentId;
            $preOrder->balance_payment_method = 'gateway';
            if ($isApproved && !$preOrder->balance_paid_at) {
                $preOrder->changeStatus(
                    \App\Enums\PreOrderStatus::BalancePaid,
                    'Saldo pago via Mercado Pago (webhook)',
                    'system'
                );
            } else {
                $preOrder->save();
            }
        }

        return true;
    }

    /**
     * Verificar status do pagamento
     */
    public function checkPaymentStatus(?string $paymentId): array
    {
        if (!$paymentId || !$this->accessToken) {
            return ['error' => 'ID do pagamento ou token não configurado'];
        }

        try {
            // Para Orders API, o ID é do order, não do payment
            // Tentar primeiro como order
            $response = Http::withToken($this->accessToken)
                ->get($this->baseUrl . '/v1/orders/' . $paymentId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => $data['status'] ?? 'unknown',
                    'status_detail' => $data['status_detail'] ?? null,
                    'type' => 'order',
                ];
            }

            // Se falhar, tentar como payment tradicional
            $response = Http::withToken($this->accessToken)
                ->get($this->baseUrl . '/v1/payments/' . $paymentId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => $data['status'] ?? 'unknown',
                    'status_detail' => $data['status_detail'] ?? null,
                    'type' => 'payment',
                ];
            }

            return ['error' => 'Pagamento não encontrado'];
        } catch (\Exception $e) {
            Log::error('Mercado Pago - Erro ao verificar status', ['error' => $e->getMessage()]);
            return ['error' => 'Erro ao verificar status: ' . $e->getMessage()];
        }
    }

    /**
     * Obter chave pública para o frontend
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }
}
