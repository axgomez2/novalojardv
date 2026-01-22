<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientOrder;
use App\Models\ClientUser;
use App\Models\ClientAddress;
use App\Models\ClientOrderItem;
use App\Models\OrderStatusHistory;
use App\Models\VinylStock;
use App\Services\OrderNotificationService;
use App\Services\OrderInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected OrderNotificationService $notificationService;
    protected OrderInvoiceService $invoiceService;

    public function __construct(
        OrderNotificationService $notificationService,
        OrderInvoiceService $invoiceService
    ) {
        $this->notificationService = $notificationService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Listagem de pedidos
     */
    public function index(Request $request)
    {
        $query = ClientOrder::with(['clientUser', 'items', 'lastPayment'])
            ->orderByDesc('created_at');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhereHas('clientUser', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Estatísticas
        $stats = [
            'total' => ClientOrder::count(),
            'pending' => ClientOrder::where('status', 'pending')->count(),
            'processing' => ClientOrder::whereIn('status', ['paid', 'processing'])->count(),
            'shipped' => ClientOrder::where('status', 'shipped')->count(),
            'today_revenue' => ClientOrder::whereDate('created_at', today())
                ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->sum('total'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Detalhes do pedido
     */
    public function show(ClientOrder $order)
    {
        $order->load([
            'clientUser',
            'shippingAddress',
            'billingAddress',
            'items.vinylStock.vinylMaster.mainArtists',
            'items.vinylStock.vinylMaster.vinylImages',
            'payments',
            'statusHistory.changedBy',
            'invoices',
            'notifications',
            'createdBy',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Formulário PDV - Novo pedido
     */
    public function create()
    {
        $clients = ClientUser::orderBy('name')->get();
        $products = VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.vinylImages'])
            ->where('stock', '>', 0)
            ->whereIn('availability', ['available', 'featured'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.create', compact('clients', 'products'));
    }

    /**
     * Criar pedido via PDV
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:registered,guest',
            'client_user_id' => 'required_if:client_type,registered|nullable|exists:client_users,id',
            'guest_name' => 'required_if:client_type,guest|nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'guest_cpf' => 'nullable|string|max:14',
            'shipping_type' => 'required|in:delivery,pickup',
            'shipping_address_id' => 'nullable|exists:client_addresses,id',
            'shipping_street' => 'required_if:shipping_type,delivery|nullable|string|max:255',
            'shipping_number' => 'required_if:shipping_type,delivery|nullable|string|max:20',
            'shipping_complement' => 'nullable|string|max:100',
            'shipping_neighborhood' => 'required_if:shipping_type,delivery|nullable|string|max:100',
            'shipping_city' => 'required_if:shipping_type,delivery|nullable|string|max:100',
            'shipping_state' => 'required_if:shipping_type,delivery|nullable|string|max:2',
            'shipping_zip_code' => 'required_if:shipping_type,delivery|nullable|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.vinyl_stock_id' => 'required|exists:vinyl_stocks,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:pix,credit_card,debit_card,cash,bank_transfer',
            'payment_status' => 'required|in:pending,paid',
            'customer_notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Calcular subtotal
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $stock = VinylStock::findOrFail($item['vinyl_stock_id']);
                
                if ($stock->stock < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para: {$stock->vinylMaster->title}");
                }

                $itemTotal = $stock->current_price * $item['quantity'];
                $subtotal += $itemTotal;

                $itemsData[] = [
                    'vinyl_stock_id' => $stock->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $stock->current_price,
                    'total_price' => $itemTotal,
                ];

                // Decrementar estoque
                $stock->decrement('stock', $item['quantity']);
            }

            $shippingCost = $validated['shipping_cost'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $total = $subtotal + $shippingCost - $discount;

            // Preparar dados do endereço para guest
            $shippingAddressData = null;
            if ($validated['shipping_type'] === 'delivery' && $validated['client_type'] === 'guest') {
                $shippingAddressData = [
                    'street' => $validated['shipping_street'],
                    'number' => $validated['shipping_number'],
                    'complement' => $validated['shipping_complement'] ?? null,
                    'neighborhood' => $validated['shipping_neighborhood'],
                    'city' => $validated['shipping_city'],
                    'state' => $validated['shipping_state'],
                    'zip_code' => $validated['shipping_zip_code'],
                ];
            }

            // Criar pedido
            $order = ClientOrder::create([
                'source' => 'pdv',
                'created_by' => Auth::guard('admin')->id(),
                'client_user_id' => $validated['client_type'] === 'registered' ? $validated['client_user_id'] : null,
                'guest_name' => $validated['client_type'] === 'guest' ? $validated['guest_name'] : null,
                'guest_email' => $validated['client_type'] === 'guest' ? $validated['guest_email'] : null,
                'guest_phone' => $validated['client_type'] === 'guest' ? $validated['guest_phone'] : null,
                'guest_cpf' => $validated['client_type'] === 'guest' ? $validated['guest_cpf'] : null,
                'shipping_address_id' => $validated['shipping_address_id'] ?? null,
                'shipping_address_data' => $shippingAddressData,
                'status' => $validated['payment_status'] === 'paid' ? 'paid' : 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'shipping_method' => $validated['shipping_type'] === 'pickup' ? 'Retirada na Loja' : 'Entrega',
                'customer_notes' => $validated['customer_notes'],
                'admin_notes' => $validated['admin_notes'],
            ]);

            // Criar itens
            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Registrar histórico
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $order->status,
                'notes' => 'Pedido criado via PDV',
                'changed_by' => Auth::guard('admin')->id(),
            ]);

            // Criar pagamento se já foi pago
            if ($validated['payment_status'] === 'paid') {
                $order->payments()->create([
                    'client_user_id' => $order->client_user_id,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'approved',
                    'amount' => $total,
                    'gateway' => 'pdv',
                    'paid_at' => now(),
                ]);
            }

            DB::commit();

            // Enviar notificações
            if ($order->customer_email || $order->customer_phone) {
                $this->notificationService->notifyOrderCreated($order);
                
                if ($validated['payment_status'] === 'paid') {
                    $this->notificationService->notifyPaymentConfirmed($order);
                }
            }

            return redirect()->route('admin.orders.show', $order)
                ->with('success', 'Pedido criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Atualizar status do pedido
     */
    public function updateStatus(Request $request, ClientOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled,refunded',
            'notes' => 'nullable|string',
            'tracking_code' => 'required_if:status,shipped|nullable|string|max:100',
            'shipping_carrier' => 'nullable|string|max:100',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status não alterado.');
        }

        DB::beginTransaction();

        try {
            $updateData = ['status' => $newStatus];

            // Dados específicos por status
            if ($newStatus === 'shipped') {
                $updateData['tracking_code'] = $validated['tracking_code'];
                $updateData['shipping_carrier'] = $validated['shipping_carrier'] ?? null;
                $updateData['shipped_at'] = now();
            } elseif ($newStatus === 'delivered') {
                $updateData['delivered_at'] = now();
            } elseif ($newStatus === 'cancelled') {
                // Restaurar estoque
                foreach ($order->items as $item) {
                    $item->vinylStock->increment('stock', $item->quantity);
                }
            }

            $order->update($updateData);

            // Registrar histórico
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $validated['notes'],
                'changed_by' => Auth::guard('admin')->id(),
            ]);

            DB::commit();

            // Enviar notificações
            $this->sendStatusNotification($order, $newStatus);

            return back()->with('success', 'Status atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificação baseada no status
     */
    protected function sendStatusNotification(ClientOrder $order, string $status): void
    {
        match ($status) {
            'paid' => $this->notificationService->notifyPaymentConfirmed($order),
            'processing' => $this->notificationService->notifyOrderProcessing($order),
            'shipped' => $this->notificationService->notifyOrderShipped($order),
            'delivered' => $this->notificationService->notifyOrderDelivered($order),
            'cancelled' => $this->notificationService->notifyOrderCancelled($order),
            default => null,
        };
    }

    /**
     * Gerar declaração de conteúdo
     */
    public function generateInvoice(ClientOrder $order)
    {
        try {
            $invoice = $this->invoiceService->generateContentDeclaration($order);

            return back()->with('success', "Declaração de conteúdo {$invoice->invoice_number} gerada com sucesso!");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar declaração: ' . $e->getMessage());
        }
    }

    /**
     * Download da declaração de conteúdo
     */
    public function downloadInvoice(ClientOrder $order)
    {
        $invoice = $order->invoices()->where('type', 'content_declaration')->latest()->first();

        if (!$invoice || !$invoice->pdf_path) {
            return back()->with('error', 'Declaração não encontrada.');
        }

        return response()->download(storage_path('app/public/' . $invoice->pdf_path));
    }

    /**
     * Buscar clientes (AJAX)
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q', '');

        $clients = ClientUser::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('cpf', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'cpf' => $client->formatted_cpf,
                ];
            });

        return response()->json($clients);
    }

    /**
     * Buscar endereços do cliente (AJAX)
     */
    public function getClientAddresses(ClientUser $client)
    {
        $addresses = $client->addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'label' => $address->label ?? 'Endereço',
                'full_address' => $address->full_address,
                'is_default' => $address->is_default,
            ];
        });

        return response()->json($addresses);
    }

    /**
     * Buscar produtos (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');

        $products = VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.vinylImages'])
            ->where('stock', '>', 0)
            ->whereIn('availability', ['available', 'featured'])
            ->whereHas('vinylMaster', function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhereHas('mainArtists', function ($q2) use ($query) {
                        $q2->where('name', 'like', "%{$query}%");
                    });
            })
            ->limit(20)
            ->get()
            ->map(function ($stock) {
                $vinyl = $stock->vinylMaster;
                $image = $vinyl->vinylImages->first();
                
                return [
                    'id' => $stock->id,
                    'title' => $vinyl->title,
                    'artist' => $vinyl->artist_names ?? 'N/A',
                    'price' => $stock->current_price,
                    'formatted_price' => $stock->formatted_current_price,
                    'stock' => $stock->stock,
                    'image' => $image ? asset('storage/' . $image->path) : null,
                ];
            });

        return response()->json($products);
    }

    /**
     * Adicionar nota administrativa
     */
    public function addNote(Request $request, ClientOrder $order)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $currentNotes = $order->admin_notes ?? '';
        $newNote = "[" . now()->format('d/m/Y H:i') . " - " . Auth::guard('admin')->user()->name . "]\n" . $validated['admin_notes'];
        
        $order->update([
            'admin_notes' => $currentNotes ? $currentNotes . "\n\n" . $newNote : $newNote,
        ]);

        return back()->with('success', 'Nota adicionada com sucesso!');
    }

    /**
     * Reenviar notificação
     */
    public function resendNotification(Request $request, ClientOrder $order)
    {
        $validated = $request->validate([
            'type' => 'required|in:order_created,payment_confirmed,order_processing,order_shipped,order_delivered',
            'channel' => 'required|in:email,whatsapp,both',
        ]);

        try {
            match ($validated['type']) {
                'order_created' => $this->notificationService->notifyOrderCreated($order),
                'payment_confirmed' => $this->notificationService->notifyPaymentConfirmed($order),
                'order_processing' => $this->notificationService->notifyOrderProcessing($order),
                'order_shipped' => $this->notificationService->notifyOrderShipped($order),
                'order_delivered' => $this->notificationService->notifyOrderDelivered($order),
            };

            return back()->with('success', 'Notificação reenviada com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao reenviar notificação: ' . $e->getMessage());
        }
    }
}
