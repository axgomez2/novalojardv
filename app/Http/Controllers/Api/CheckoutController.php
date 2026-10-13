<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientOrder;
use App\Models\ClientAddress;
use App\Models\VinylStock;
use App\Services\MelhorEnvioService;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected MelhorEnvioService $melhorEnvio;
    protected MercadoPagoService $mercadoPago;

    public function __construct(MelhorEnvioService $melhorEnvio, MercadoPagoService $mercadoPago)
    {
        $this->melhorEnvio = $melhorEnvio;
        $this->mercadoPago = $mercadoPago;
    }

    /**
     * Verificar requisitos para checkout
     * Retorna quais dados estão faltando para finalizar a compra
     */
    public function checkRequirements(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $missingFields = [];
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'cpf' => $user->cpf,
        ];
        
        // Verificar campos obrigatórios do usuário
        if (empty($user->name) || strlen(trim($user->name)) < 3) {
            $missingFields[] = 'name';
        }
        
        if (empty($user->email)) {
            $missingFields[] = 'email';
        }
        
        if (empty($user->phone)) {
            $missingFields[] = 'phone';
        }
        
        if (empty($user->cpf)) {
            $missingFields[] = 'cpf';
        }
        
        // Verificar se tem pelo menos um endereço
        $hasAddress = $user->addresses()->exists();
        $addresses = $user->addresses()->get()->map(fn($addr) => [
            'id' => $addr->id,
            'label' => $addr->label,
            'full_address' => $addr->full_address,
            'formatted_zip_code' => $addr->formatted_zip_code,
            'is_default' => $addr->is_default,
        ]);
        
        $canProceed = empty($missingFields) && $hasAddress;
        
        return response()->json([
            'can_proceed' => $canProceed,
            'has_address' => $hasAddress,
            'addresses_count' => $addresses->count(),
            'addresses' => $addresses,
            'missing_fields' => $missingFields,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->formatted_phone,
                'phone_raw' => $user->phone,
                'cpf' => $user->formatted_cpf,
                'cpf_raw' => $user->cpf,
            ],
        ]);
    }

    /**
     * Calcular frete para os itens do carrinho
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|min:8|max:9',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:vinyl_stocks,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $postalCode = preg_replace('/\D/', '', $validated['postal_code']);

        $items = collect($validated['items'])->map(function ($item) {
            $stock = VinylStock::with('vinylMaster')->find($item['id']);
            
            if (!$stock) {
                return null;
            }

            return [
                'id' => $stock->id,
                'format' => $stock->format ?? 'LP',
                'quantity' => $item['quantity'],
                'price' => $stock->current_price,
            ];
        })->filter()->values()->toArray();

        if (empty($items)) {
            return response()->json(['error' => 'Nenhum item válido no carrinho'], 422);
        }

        $products = MelhorEnvioService::calculateProductDimensions($items);
        $result = $this->melhorEnvio->calculateShipping($postalCode, $products);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json([
            'data' => $result['quotes'] ?? [],
            'postal_code' => $postalCode,
        ]);
    }

    /**
     * Criar pedido
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:vinyl_stocks,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address_id' => 'required_without:shipping_address|integer|exists:client_addresses,id',
            'shipping_address' => 'required_without:shipping_address_id|array',
            'shipping_address.street' => 'required_with:shipping_address|string',
            'shipping_address.number' => 'required_with:shipping_address|string',
            'shipping_address.neighborhood' => 'required_with:shipping_address|string',
            'shipping_address.city' => 'required_with:shipping_address|string',
            'shipping_address.state' => 'required_with:shipping_address|string|size:2',
            'shipping_address.zip_code' => 'required_with:shipping_address|string',
            'shipping_service_id' => 'required|string',
            'shipping_service_name' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'shipping_deadline' => 'required|integer|min:1',
            'payment_method' => 'required|in:pix,credit_card,checkout_pro,whatsapp',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        $subtotal = 0;
        $orderItems = [];
        $hasPreorder = false;
        $latestPreorderDate = null;

        foreach ($validated['items'] as $item) {
            $stock = VinylStock::with('vinylMaster')->find($item['id']);
            
            if (!$stock) {
                return response()->json(['error' => 'Produto não encontrado: ' . $item['id']], 422);
            }

            if ($stock->stock < $item['quantity'] && $stock->availability !== 'preorder') {
                return response()->json([
                    'error' => 'Estoque insuficiente para: ' . $stock->vinylMaster?->title
                ], 422);
            }

            if ($stock->availability === 'preorder') {
                $hasPreorder = true;
                if ($stock->release_date && (!$latestPreorderDate || $stock->release_date > $latestPreorderDate)) {
                    $latestPreorderDate = $stock->release_date;
                }
            }

            $itemTotal = $stock->current_price * $item['quantity'];
            $subtotal += $itemTotal;

            $orderItems[] = [
                'vinyl_stock_id' => $stock->id,
                'quantity' => $item['quantity'],
                'unit_price' => $stock->current_price,
                'total_price' => $itemTotal,
            ];
        }

        $shippingAddressId = null;
        $shippingAddressData = null;

        if (isset($validated['shipping_address_id'])) {
            $address = ClientAddress::where('client_user_id', $user->id)
                ->find($validated['shipping_address_id']);
            
            if (!$address) {
                return response()->json(['error' => 'Endereço não encontrado'], 422);
            }
            $shippingAddressId = $address->id;
        } else {
            $shippingAddressData = json_encode($validated['shipping_address']);
        }

        $total = $subtotal + $validated['shipping_cost'];

        $order = ClientOrder::create([
            'order_number' => $this->generateOrderNumber(),
            'client_user_id' => $user->id,
            'shipping_address_id' => $shippingAddressId,
            'shipping_address_data' => $shippingAddressData,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'shipping_cost' => $validated['shipping_cost'],
            'total' => $total,
            'shipping_method' => $validated['shipping_service_name'],
            'shipping_service_id' => $validated['shipping_service_id'],
            'shipping_service_name' => $validated['shipping_service_name'],
            'shipping_deadline' => $validated['shipping_deadline'],
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        $paymentResult = null;

        if ($validated['payment_method'] === 'pix') {
            $paymentResult = $this->mercadoPago->createPixPayment($order);
        } elseif ($validated['payment_method'] === 'checkout_pro') {
            $paymentResult = $this->mercadoPago->createPreference($order);
        }

        if (isset($paymentResult['error'])) {
            $order->delete();
            return response()->json(['error' => $paymentResult['error']], 500);
        }

        // Registro inicial para Checkout Pro / WhatsApp: identifica a forma de pagamento
        // (o pagamento real do Checkout Pro é registrado depois via webhook/sync)
        if (in_array($validated['payment_method'], ['checkout_pro', 'whatsapp'])) {
            $order->payments()->create([
                'payment_method' => $validated['payment_method'],
                'mercado_pago_id' => $paymentResult['preference_id'] ?? null,
                'mercado_pago_status' => $validated['payment_method'] === 'whatsapp' ? 'pending' : 'preference',
                'amount' => $order->total,
                'payment_response' => $paymentResult,
            ]);
        }

        // Limpar carrinho do usuário após criar pedido
        $cart = $user->cart;
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'formatted_total' => 'R$ ' . number_format($order->total, 2, ',', '.'),
                'status' => $order->status,
                'has_preorder' => $hasPreorder,
                'estimated_shipping_date' => $hasPreorder && $latestPreorderDate 
                    ? $latestPreorderDate->addDays($validated['shipping_deadline'])->format('d/m/Y')
                    : now()->addDays($validated['shipping_deadline'])->format('d/m/Y'),
                'payment' => $paymentResult,
            ],
        ], 201);
    }

    /**
     * Obter detalhes do pedido
     */
    public function getOrder(Request $request, string $orderNumber): JsonResponse
    {
        $order = ClientOrder::with(['items.vinylStock.vinylMaster', 'shippingAddress', 'payments'])
            ->where('order_number', $orderNumber)
            ->where('client_user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        return response()->json([
            'data' => $this->formatOrder($order),
        ]);
    }

    /**
     * Listar pedidos do usuário
     */
    public function listOrders(Request $request): JsonResponse
    {
        $orders = ClientOrder::with(['items.vinylStock.vinylMaster'])
            ->where('client_user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'data' => $orders->map(fn($order) => $this->formatOrder($order, false)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Obter chave pública do Mercado Pago
     */
    public function getMercadoPagoPublicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => $this->mercadoPago->getPublicKey(),
        ]);
    }

    /**
     * Gerar número do pedido
     */
    protected function generateOrderNumber(): string
    {
        $prefix = date('Ymd');
        $random = strtoupper(Str::random(6));
        return $prefix . '-' . $random;
    }

    /**
     * Formatar pedido para resposta
     */
    protected function formatOrder(ClientOrder $order, bool $detailed = true): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'subtotal' => $order->subtotal,
            'shipping_cost' => $order->shipping_cost,
            'discount' => $order->discount,
            'total' => $order->total,
            'formatted_total' => 'R$ ' . number_format($order->total, 2, ',', '.'),
            'shipping_method' => $order->shipping_service_name,
            'tracking_code' => $order->tracking_code,
            'created_at' => $order->created_at->toISOString(),
            'items_count' => $order->items->sum('quantity'),
            'payment_method' => $order->payments->first()?->payment_method ?? $order->payment_method,
            'items' => $order->items->map(fn($item) => [
                'id' => $item->id,
                'vinyl_stock_id' => $item->vinyl_stock_id,
                'title' => $item->vinylStock?->vinylMaster?->title,
                'artist' => $item->vinylStock?->vinylMaster?->artist_names,
                'cover_image' => $item->vinylStock?->vinylMaster?->cover_url,
                'format' => $item->vinylStock?->format,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]),
        ];

        if ($detailed) {
            // shipping_address_data já é array via cast do model — não usar json_decode
            $data['shipping_address'] = $order->shippingAddress ? [
                'street' => $order->shippingAddress->street,
                'number' => $order->shippingAddress->number,
                'complement' => $order->shippingAddress->complement,
                'neighborhood' => $order->shippingAddress->neighborhood,
                'city' => $order->shippingAddress->city,
                'state' => $order->shippingAddress->state,
                'zip_code' => $order->shippingAddress->zip_code,
            ] : $order->shipping_address_data;

            $lastPayment = $order->payments->sortByDesc('id')->first();
            $isPending = $order->status === 'pending';
            $pixPayment = $order->payments->where('payment_method', 'pix')->sortByDesc('id')->first();
            $proPayment = $order->payments->where('payment_method', 'checkout_pro')->whereNotNull('payment_response')->sortByDesc('id')->first();

            $data['payment'] = $lastPayment ? [
                'method' => $lastPayment->payment_method,
                'status' => $lastPayment->mercado_pago_status,
                'status_detail' => $lastPayment->mercado_pago_status_detail,
                'status_label' => $lastPayment->status_label,
                'pix_expiration' => $pixPayment?->pix_expiration?->toISOString(),
                'pix_expired' => $pixPayment?->pix_expiration?->isPast() ?? false,
                // Link do Checkout Pro para retomar o pagamento enquanto a preferência é válida
                'checkout_url' => $isPending && $proPayment && $order->created_at->gt(now()->subHours(MercadoPagoService::CHECKOUT_PRO_EXPIRATION_HOURS))
                    ? ($proPayment->payment_response['init_point'] ?? null)
                    : null,
            ] : null;

            $data['customer_notes'] = $order->customer_notes;
        }

        return $data;
    }

    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Aguardando Pagamento',
            'paid' => 'Pago',
            'processing' => 'Em Processamento',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => $status,
        };
    }

    /**
     * Verificar status do pagamento PIX
     */
    public function checkPaymentStatus(Request $request, string $orderNumber): JsonResponse
    {
        $order = ClientOrder::where('order_number', $orderNumber)
            ->where('client_user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        // Só consulta o Mercado Pago enquanto o pedido ainda aguarda pagamento.
        // payment_id vem da query string no retorno do Checkout Pro.
        if ($order->status === 'pending') {
            $this->mercadoPago->syncOrderPayment($order, $request->query('payment_id'));
            $order->refresh();
        }

        $payment = $order->payments()->latest('id')->first();

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'payment_method' => $payment?->payment_method,
                'payment_status' => $payment?->mercado_pago_status,
                'payment_status_detail' => $payment?->mercado_pago_status_detail,
                'is_approved' => $order->is_paid,
                'is_cancelled' => in_array($order->status, ['cancelled', 'refunded']),
                'is_rejected' => $payment?->mercado_pago_status === 'rejected',
            ],
        ]);
    }

    /**
     * Dados do PIX de um pedido pendente (para reabrir a tela de pagamento).
     */
    public function getPixPayment(Request $request, string $orderNumber): JsonResponse
    {
        $order = ClientOrder::where('order_number', $orderNumber)
            ->where('client_user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $payment = $order->payments()->where('payment_method', 'pix')->latest('id')->first();

        if (!$payment) {
            return response()->json(['error' => 'Este pedido não possui pagamento PIX'], 404);
        }

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'is_approved' => $order->is_paid,
                'total' => $order->total,
                'formatted_total' => $order->formatted_total,
                'qr_code' => $order->status === 'pending' ? $payment->pix_qr_code : null,
                'qr_code_base64' => $order->status === 'pending' ? $payment->pix_qr_code_base64 : null,
                'expiration' => $payment->pix_expiration?->toISOString(),
                'is_expired' => $payment->pix_expiration?->isPast() ?? false,
                'server_time' => now()->toISOString(),
                'is_sandbox' => (bool) config('services.mercado_pago.sandbox', false),
            ],
        ]);
    }

    /**
     * Simular aprovação de pagamento PIX (apenas sandbox)
     */
    public function simulatePaymentApproval(Request $request, string $orderNumber): JsonResponse
    {
        // Apenas em ambiente de desenvolvimento/sandbox
        if (!config('services.mercado_pago.sandbox', false)) {
            return response()->json(['error' => 'Disponível apenas em ambiente sandbox'], 403);
        }

        $order = ClientOrder::with('payments')
            ->where('order_number', $orderNumber)
            ->where('client_user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $payment = $order->payments->first();

        if (!$payment) {
            return response()->json(['error' => 'Pagamento não encontrado'], 404);
        }

        // Simular aprovação
        $this->mercadoPago->applyPaymentStatus($order, (string) $payment->mercado_pago_id, 'approved', 'accredited');

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'order_status' => 'paid',
                'payment_status' => 'approved',
                'message' => 'Pagamento simulado com sucesso!',
            ],
        ]);
    }
}
