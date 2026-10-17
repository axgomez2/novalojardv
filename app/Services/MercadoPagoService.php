<?php

namespace App\Services;

use App\Models\ClientOrder;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public const PIX_EXPIRATION_MINUTES = 30;
    public const CHECKOUT_PRO_EXPIRATION_HOURS = 2;

    protected string $baseUrl = 'https://api.mercadopago.com';
    protected ?string $accessToken;
    protected ?string $publicKey;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->accessToken = config('services.mercado_pago.access_token');
        $this->publicKey = config('services.mercado_pago.public_key');
        $this->isSandbox = config('services.mercado_pago.sandbox', true);

        if ($this->accessToken) {
            MercadoPagoConfig::setAccessToken($this->accessToken);
        }
    }

    /**
     * Monta o bloco "payer" da preferência a partir do cliente do pedido.
     * Inclui CPF, telefone e endereço quando disponíveis (melhora taxa de aprovação).
     */
    protected function buildPayer(ClientOrder $order): array
    {
        $user = $order->clientUser;
        $fullName = $user?->name ?? $order->guest_name ?? '';
        $parts = explode(' ', trim($fullName), 2);

        $payer = [
            'name'    => $parts[0] ?? '',
            'surname' => $parts[1] ?? '',
            'email'   => $user?->email ?? $order->guest_email,
        ];

        $cpf = preg_replace('/\D/', '', (string) ($user?->cpf ?? $order->guest_cpf ?? ''));
        if (strlen($cpf) === 11) {
            $payer['identification'] = ['type' => 'CPF', 'number' => $cpf];
        }

        $phone = preg_replace('/\D/', '', (string) ($user?->phone ?? $order->guest_phone ?? ''));
        if (strlen($phone) >= 10) {
            $payer['phone'] = [
                'area_code' => substr($phone, 0, 2),
                'number'    => substr($phone, 2),
            ];
        }

        $address = $order->shippingAddress;
        if ($address) {
            $payer['address'] = [
                'zip_code'      => preg_replace('/\D/', '', (string) $address->zip_code),
                'street_name'   => (string) $address->street,
                'street_number' => (string) $address->number,
            ];
        }

        return $payer;
    }

    /**
     * Monta o bloco "shipments" da preferência (frete separado dos items).
     */
    protected function buildShipments(ClientOrder $order): ?array
    {
        if ((float) $order->shipping_cost <= 0) {
            return null;
        }

        $shipments = [
            'cost' => round((float) $order->shipping_cost, 2),
            'mode' => 'not_specified',
        ];

        $address = $order->shippingAddress;
        if ($address) {
            $shipments['receiver_address'] = [
                'zip_code'      => preg_replace('/\D/', '', (string) $address->zip_code),
                'street_name'   => (string) $address->street,
                'street_number' => (string) $address->number,
                'floor'         => '',
                'apartment'     => (string) ($address->complement ?? ''),
                'city_name'     => (string) $address->city,
                'state_name'    => (string) $address->state,
                'country_name'  => 'Brasil',
            ];
        }

        return $shipments;
    }

    /**
     * Criar preferência de pagamento (Checkout Pro) via SDK oficial.
     */
    public function createPreference(ClientOrder $order): array
    {
        if (!$this->accessToken) {
            return ['error' => 'Token do Mercado Pago não configurado'];
        }

        try {
            $items = $order->items->map(function ($item) {
                return [
                    'id'          => (string) $item->vinyl_stock_id,
                    'title'       => $item->vinylStock?->vinylMaster?->full_title ?? 'Disco de Vinil',
                    'description' => $item->vinylStock?->vinylMaster?->format ?? 'LP',
                    'picture_url' => $item->vinylStock?->vinylMaster?->cover_url,
                    'category_id' => 'music',
                    'quantity'    => (int) $item->quantity,
                    'currency_id' => 'BRL',
                    'unit_price'  => round((float) $item->unit_price, 2),
                ];
            })->values()->toArray();

            $frontendUrl = rtrim(config('app.frontend_url') ?: env('FRONTEND_URL', 'http://localhost:3000'), '/');
            $backendUrl  = rtrim(config('app.url') ?: env('APP_URL', 'http://localhost'), '/');

            // Mercado Pago não aceita auto_return com URLs localhost/http
            $isLocalhost = str_contains($frontendUrl, 'localhost')
                || str_contains($frontendUrl, '127.0.0.1')
                || str_starts_with($frontendUrl, 'http://');

            $request = [
                'items'                => $items,
                'payer'                => $this->buildPayer($order),
                'external_reference'   => $order->order_number,
                'statement_descriptor' => 'RDVDISCOS',
                'payment_methods'      => [
                    'installments'          => 12,
                    'default_installments'  => 1,
                ],
                'metadata' => [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                ],
                'expires'             => true,
                'expiration_date_to'  => now()->addHours(self::CHECKOUT_PRO_EXPIRATION_HOURS)->format('Y-m-d\TH:i:s.vP'),
            ];

            // Frete no bloco shipments (não como item)
            if ($shipments = $this->buildShipments($order)) {
                $request['shipments'] = $shipments;
            }

            // back_urls + notification_url só quando URL pública HTTPS
            if (!$isLocalhost) {
                $request['back_urls'] = [
                    'success' => $frontendUrl . '/pedido/' . $order->order_number . '/sucesso',
                    'failure' => $frontendUrl . '/pedido/' . $order->order_number . '/erro',
                    'pending' => $frontendUrl . '/pedido/' . $order->order_number . '/pendente',
                ];
                $request['auto_return']      = 'approved';
                $request['notification_url'] = $backendUrl . '/api/webhooks/mercadopago';
            }

            Log::info('Mercado Pago - Criando preferência', [
                'order'         => $order->order_number,
                'frontend_url'  => $frontendUrl,
                'is_localhost'  => $isLocalhost,
                'items_count'   => count($items),
                'has_shipments' => isset($request['shipments']),
            ]);

            $client = new PreferenceClient();
            $preference = $client->create($request);

            $initPoint = $this->isSandbox
                ? ($preference->sandbox_init_point ?? $preference->init_point)
                : $preference->init_point;

            return [
                'preference_id'      => $preference->id,
                'init_point'         => $initPoint,
                'sandbox_init_point' => $preference->sandbox_init_point ?? null,
                'is_sandbox'         => $this->isSandbox,
            ];
        } catch (MPApiException $e) {
            $body = method_exists($e, 'getApiResponse') ? $e->getApiResponse()->getContent() : null;
            Log::error('Mercado Pago - Erro API ao criar preferência', [
                'order'    => $order->order_number,
                'status'   => $e->getApiResponse()?->getStatusCode(),
                'response' => $body,
                'message'  => $e->getMessage(),
            ]);
            return ['error' => 'Erro ao criar preferência de pagamento'];
        } catch (\Throwable $e) {
            Log::error('Mercado Pago - Exceção createPreference', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
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
            'mercado_pago_status' => $this->normalizeStatus($data['status'] ?? 'pending'),
            'mercado_pago_status_detail' => $data['status_detail'] ?? null,
            'amount' => (float) $order->total,
            'pix_qr_code' => $paymentMethod['qr_code'] ?? null,
            'pix_qr_code_base64' => $paymentMethod['qr_code_base64'] ?? null,
            'pix_expiration' => now()->addMinutes(self::PIX_EXPIRATION_MINUTES),
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
            'date_of_expiration' => now()->addMinutes(self::PIX_EXPIRATION_MINUTES)->format('Y-m-d\TH:i:s.vP'),
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
            'pix_expiration' => isset($data['date_of_expiration']) ? \Carbon\Carbon::parse($data['date_of_expiration']) : now()->addMinutes(self::PIX_EXPIRATION_MINUTES),
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
        $type = $data['type'] ?? $data['topic'] ?? null;
        $resourceId = $data['data']['id'] ?? null;

        if (!$resourceId || !$type) {
            return false;
        }

        try {
            // PIX do sandbox é criado pela Orders API → notificação do tipo "order"
            if ($type === 'order') {
                $response = Http::withToken($this->accessToken)->get($this->baseUrl . '/v1/orders/' . $resourceId);
                if ($response->failed()) {
                    Log::error('Mercado Pago - Erro ao buscar order', ['id' => $resourceId]);
                    return false;
                }
                $orderData = $response->json();
                $order = ClientOrder::where('order_number', $orderData['external_reference'] ?? '')->first();
                if (!$order) {
                    return false;
                }
                $this->applyPaymentStatus($order, (string) $resourceId, $orderData['status'] ?? 'pending', $orderData['status_detail'] ?? null, [
                    'payment_response' => $orderData,
                ]);
                return true;
            }

            if ($type !== 'payment') {
                return true;
            }

            $paymentData = $this->fetchPayment((string) $resourceId);
            if (!$paymentData) {
                return false;
            }

            $externalReference = $paymentData['external_reference'] ?? null;
            if (!$externalReference) {
                return false;
            }

            // Pré-vendas usam external_reference no formato PREORDER-{id}-{SIGNAL|BALANCE}
            if (str_starts_with($externalReference, 'PREORDER-')) {
                return $this->handlePreOrderPaymentWebhook($externalReference, (string) $resourceId, $paymentData);
            }

            $order = ClientOrder::where('order_number', $externalReference)->first();
            if (!$order) {
                Log::warning('Mercado Pago - Pedido não encontrado', ['reference' => $externalReference]);
                return false;
            }

            $this->applyPaymentData($order, $paymentData);
            return true;
        } catch (\Exception $e) {
            Log::error('Mercado Pago - Erro no webhook', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Busca um pagamento na API /v1/payments.
     */
    public function fetchPayment(string $paymentId): ?array
    {
        $response = Http::withToken($this->accessToken)->get($this->baseUrl . '/v1/payments/' . $paymentId);

        if ($response->failed()) {
            Log::error('Mercado Pago - Erro ao buscar pagamento', ['id' => $paymentId, 'status' => $response->status()]);
            return null;
        }

        return $response->json();
    }

    /**
     * Busca o pagamento mais recente de um pedido pelo external_reference
     * (usado no Checkout Pro, onde não há OrderPayment antes do webhook).
     */
    public function searchLatestPaymentByReference(string $externalReference): ?array
    {
        $response = Http::withToken($this->accessToken)->get($this->baseUrl . '/v1/payments/search', [
            'external_reference' => $externalReference,
            'sort' => 'date_created',
            'criteria' => 'desc',
            'limit' => 1,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('results.0');
    }

    /**
     * Grava/atualiza um pagamento vindo da API /v1/payments e aplica o status no pedido.
     */
    public function applyPaymentData(ClientOrder $order, array $paymentData): OrderPayment
    {
        $amount = $paymentData['transaction_amount'] ?? $order->total;

        return $this->applyPaymentStatus($order, (string) $paymentData['id'], $paymentData['status'] ?? 'pending', $paymentData['status_detail'] ?? null, [
            'payment_method' => ($paymentData['payment_method_id'] ?? null) === 'pix' ? 'pix' : ($paymentData['payment_type_id'] ?? 'checkout_pro'),
            'amount' => $amount,
            'fee' => $paymentData['fee_details'][0]['amount'] ?? 0,
            'net_amount' => $paymentData['transaction_details']['net_received_amount'] ?? $amount,
            'installments' => $paymentData['installments'] ?? 1,
            'payer_info' => $paymentData['payer'] ?? null,
            'payment_response' => $paymentData,
        ]);
    }

    /**
     * Atualiza (ou cria) o OrderPayment e, se aprovado, marca o pedido como pago.
     * Ponto único usado por webhook, verificação manual e retorno do Checkout Pro.
     */
    public function applyPaymentStatus(ClientOrder $order, string $mercadoPagoId, string $rawStatus, ?string $statusDetail = null, array $extra = []): OrderPayment
    {
        $status = $this->normalizeStatus($rawStatus);

        $payment = OrderPayment::firstOrNew(['order_id' => $order->id, 'mercado_pago_id' => $mercadoPagoId]);
        if (!$payment->exists) {
            $payment->payment_method = $extra['payment_method'] ?? 'checkout_pro';
            $payment->amount = $extra['amount'] ?? $order->total;
        }

        $payment->fill(array_merge(
            array_diff_key($extra, array_flip(['payment_method', 'amount'])),
            [
                'mercado_pago_status' => $status,
                'mercado_pago_status_detail' => $statusDetail,
                'paid_at' => $status === 'approved' ? ($payment->paid_at ?? now()) : $payment->paid_at,
            ]
        ));
        $payment->save();

        if ($status === 'approved') {
            $this->markOrderAsPaid($order);
        }

        return $payment;
    }

    /**
     * Converte status da Orders API (sandbox) para o padrão da Payments API.
     */
    public function normalizeStatus(string $status): string
    {
        return match ($status) {
            'processed', 'accredited' => 'approved',
            'action_required', 'created', 'processing', 'waiting_payment' => 'pending',
            'canceled', 'expired' => 'cancelled',
            'failed' => 'rejected',
            default => $status,
        };
    }

    /**
     * Marca o pedido como pago de forma idempotente: baixa estoque e envia
     * e-mail apenas na primeira vez. Seguro para webhook + polling simultâneos.
     */
    public function markOrderAsPaid(ClientOrder $order): bool
    {
        $changed = \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $locked = ClientOrder::whereKey($order->id)->lockForUpdate()->first();

            $canPay = $locked->status === 'pending'
                || ($locked->status === 'cancelled' && $this->wasAutoExpired($locked));

            if (!$canPay) {
                return false;
            }

            $fromStatus = $locked->status;
            $locked->update(['status' => 'paid']);

            foreach ($locked->items()->with('vinylStock')->get() as $item) {
                $item->vinylStock?->decrement('stock', $item->quantity);
            }

            \App\Models\OrderStatusHistory::create([
                'order_id' => $locked->id,
                'from_status' => $fromStatus,
                'to_status' => 'paid',
                'notes' => 'Pagamento confirmado pelo Mercado Pago',
            ]);

            return true;
        });

        if ($changed) {
            $order->refresh();
            try {
                if ($email = $order->customer_email) {
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\OrderConfirmation($order));
                }
            } catch (\Exception $e) {
                Log::error('Erro ao enviar email de confirmação', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $changed;
    }

    public const AUTO_EXPIRED_NOTE = 'Cancelado automaticamente: pagamento não realizado no prazo';

    /**
     * Pedido cancelado pelo job de expiração (não pelo admin) pode ser
     * reativado se o pagamento chegar atrasado.
     */
    protected function wasAutoExpired(ClientOrder $order): bool
    {
        $last = $order->statusHistory()->latest('id')->first();

        return $last && $last->to_status === 'cancelled' && $last->notes === self::AUTO_EXPIRED_NOTE;
    }

    /**
     * Sincroniza o status do pagamento de um pedido direto com o Mercado Pago.
     * $paymentId vem da query string do retorno do Checkout Pro (opcional).
     */
    public function syncOrderPayment(ClientOrder $order, ?string $paymentId = null): void
    {
        if (!$this->accessToken) {
            return;
        }

        if ($paymentId) {
            $data = $this->fetchPayment($paymentId);
            // Garante que o payment_id informado pertence a este pedido
            if ($data && ($data['external_reference'] ?? null) === $order->order_number) {
                $this->applyPaymentData($order, $data);
            }
            return;
        }

        $payment = $order->payments()->latest('id')->first();

        if ($payment?->payment_method === 'whatsapp') {
            return;
        }

        if ($payment?->payment_method === 'pix' && $payment->mercado_pago_id) {
            $result = $this->checkPaymentStatus($payment->mercado_pago_id);
            if (isset($result['status'])) {
                $this->applyPaymentStatus($order, $payment->mercado_pago_id, $result['status'], $result['status_detail'] ?? null);
            }
            return;
        }

        // Checkout Pro: procura pelo external_reference
        if ($data = $this->searchLatestPaymentByReference($order->order_number)) {
            $this->applyPaymentData($order, $data);
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
