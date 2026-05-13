<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PreOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\ClientUser;
use App\Models\PreOrder;
use App\Models\VinylStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PreOrderController extends Controller
{
    /**
     * Dashboard de alertas e monitoramento.
     */
    public function dashboard(): View
    {
        $now = now();
        $in7 = $now->copy()->addDays(7);
        $in15 = $now->copy()->addDays(15);

        // Ações urgentes (atrasados)
        $signalOverdue = PreOrder::with(['client', 'vinylStock.vinylMaster'])
            ->signalOverdue()
            ->orderBy('signal_due_date')
            ->get();

        $balanceOverdue = PreOrder::with(['client', 'vinylStock.vinylMaster'])
            ->balanceOverdue()
            ->orderBy('balance_due_date')
            ->get();

        // Próximos vencimentos (não atrasados, em <=7 dias)
        $signalDueSoon = PreOrder::with(['client', 'vinylStock.vinylMaster'])
            ->awaitingSignal()
            ->whereNotNull('signal_due_date')
            ->whereDate('signal_due_date', '>=', $now->toDateString())
            ->whereDate('signal_due_date', '<=', $in7->toDateString())
            ->orderBy('signal_due_date')
            ->get();

        $balanceDueSoon = PreOrder::with(['client', 'vinylStock.vinylMaster'])
            ->awaitingBalance()
            ->whereNotNull('balance_due_date')
            ->whereDate('balance_due_date', '>=', $now->toDateString())
            ->whereDate('balance_due_date', '<=', $in7->toDateString())
            ->orderBy('balance_due_date')
            ->get();

        // Chegadas previstas em <=15 dias
        $arrivingSoon = PreOrder::with(['client', 'vinylStock.vinylMaster'])
            ->whereIn('status', [
                PreOrderStatus::SignalPaid->value,
                PreOrderStatus::InTransit->value,
            ])
            ->whereNotNull('expected_arrival_date')
            ->whereDate('expected_arrival_date', '<=', $in15->toDateString())
            ->orderBy('expected_arrival_date')
            ->get();

        // Chegaram e aguardam ação (arrived mas não cobrou saldo)
        $arrivedWaitingAction = PreOrder::with(['client', 'vinylStock.vinylMaster'])
            ->where('status', PreOrderStatus::Arrived->value)
            ->orderBy('arrived_at')
            ->get();

        // Distribuição por status (apenas ativos)
        $distribution = PreOrder::active()
            ->selectRaw('status, COUNT(*) as total, SUM(total_amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // KPIs gerais
        $stats = [
            'active' => PreOrder::active()->count(),
            'total_open' => (float) PreOrder::active()->sum('total_amount'),
            'signal_pending' => (float) PreOrder::awaitingSignal()->sum('signal_amount'),
            'balance_pending' => (float) PreOrder::awaitingBalance()->selectRaw('SUM(total_amount - signal_amount) as s')->value('s'),
            'this_month_signal_paid' => (float) PreOrder::whereMonth('signal_paid_at', $now->month)
                ->whereYear('signal_paid_at', $now->year)
                ->sum('signal_amount'),
            'this_month_balance_paid' => (float) PreOrder::whereMonth('balance_paid_at', $now->month)
                ->whereYear('balance_paid_at', $now->year)
                ->selectRaw('SUM(total_amount - signal_amount) as s')
                ->value('s'),
        ];

        // Últimas atividades (histórico)
        $recentActivity = \App\Models\PreOrderStatusHistory::with(['preOrder.client', 'adminUser'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('admin.pre-orders.dashboard', compact(
            'signalOverdue',
            'balanceOverdue',
            'signalDueSoon',
            'balanceDueSoon',
            'arrivingSoon',
            'arrivedWaitingAction',
            'distribution',
            'stats',
            'recentActivity'
        ));
    }

    /**
     * Lista de pré-vendas com filtros e alertas.
     */
    public function index(Request $request): View
    {
        $query = PreOrder::with(['client', 'vinylStock.vinylMaster.mainArtists']);

        // Filtros
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'signal_overdue') {
                $query->signalOverdue();
            } elseif ($status === 'balance_overdue') {
                $query->balanceOverdue();
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $preOrders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // KPIs e alertas
        $stats = [
            'active' => PreOrder::active()->count(),
            'awaiting_signal' => PreOrder::awaitingSignal()->count(),
            'awaiting_balance' => PreOrder::awaitingBalance()->count(),
            'signal_overdue' => PreOrder::signalOverdue()->count(),
            'balance_overdue' => PreOrder::balanceOverdue()->count(),
            'total_open' => PreOrder::active()->sum('total_amount'),
        ];

        return view('admin.pre-orders.index', [
            'preOrders' => $preOrders,
            'stats' => $stats,
            'statusOptions' => PreOrderStatus::options(),
            'filters' => $request->only(['status', 'q', 'from', 'to']),
        ]);
    }

    /**
     * Formulário de nova pré-venda.
     */
    public function create(Request $request): View
    {
        $vinylStocks = VinylStock::with('vinylMaster.mainArtists')
            ->where(function ($q) {
                $q->where('availability', 'preorder')
                    ->orWhere('visibility', 'private_preorder');
            })
            ->orderByDesc('created_at')
            ->get();

        $clients = ClientUser::where('role', ClientUser::ROLE_CLIENT)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $preselectedStockId = $request->get('vinyl_stock_id');

        return view('admin.pre-orders.create', compact('vinylStocks', 'clients', 'preselectedStockId'));
    }

    /**
     * Cria nova pré-venda.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_user_id' => 'required|exists:client_users,id',
            'vinyl_stock_id' => 'required|exists:vinyl_stocks,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'signal_amount' => 'required|numeric|min:0',
            'expected_arrival_date' => 'nullable|date',
            'signal_due_date' => 'nullable|date',
            'balance_due_date' => 'nullable|date',
            'customer_notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        $total = round($validated['unit_price'] * $validated['quantity'], 2);

        if ($validated['signal_amount'] > $total) {
            return back()->withErrors(['signal_amount' => 'O sinal não pode ser maior que o total.'])->withInput();
        }

        $signalPercentage = $total > 0 ? round(($validated['signal_amount'] / $total) * 100, 2) : null;

        $preOrder = PreOrder::create([
            'code' => PreOrder::generateCode(),
            'client_user_id' => $validated['client_user_id'],
            'vinyl_stock_id' => $validated['vinyl_stock_id'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $total,
            'signal_amount' => $validated['signal_amount'],
            'signal_percentage' => $signalPercentage,
            'status' => PreOrderStatus::AwaitingSignal,
            'expected_arrival_date' => $validated['expected_arrival_date'] ?? null,
            'signal_due_date' => $validated['signal_due_date'] ?? null,
            'balance_due_date' => $validated['balance_due_date'] ?? null,
            'customer_notes' => $validated['customer_notes'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $preOrder->statusHistories()->create([
            'from_status' => null,
            'to_status' => PreOrderStatus::AwaitingSignal->value,
            'admin_user_id' => auth('admin')->id(),
            'triggered_by' => 'admin',
            'note' => 'Pré-venda criada',
        ]);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Pré-venda {$preOrder->code} criada",
            $preOrder
        );

        return redirect()
            ->route('admin.pre-orders.show', $preOrder)
            ->with('success', 'Pré-venda criada com sucesso!');
    }

    /**
     * Detalhe + ações da pré-venda.
     */
    public function show(PreOrder $preOrder): View
    {
        $preOrder->load([
            'client',
            'vinylStock.vinylMaster.mainArtists',
            'vinylStock.vinylMaster.recordLabel',
            'statusHistories.adminUser',
        ]);

        return view('admin.pre-orders.show', [
            'preOrder' => $preOrder,
            'allowedTransitions' => $preOrder->status->allowedTransitions(),
        ]);
    }

    /**
     * Atualiza dados editáveis da pré-venda (notas, datas, valores se ainda awaiting_signal).
     */
    public function update(Request $request, PreOrder $preOrder): RedirectResponse
    {
        $rules = [
            'expected_arrival_date' => 'nullable|date',
            'signal_due_date' => 'nullable|date',
            'balance_due_date' => 'nullable|date',
            'customer_notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ];

        // Valores só podem ser alterados se ainda aguardando sinal
        if ($preOrder->status === PreOrderStatus::AwaitingSignal) {
            $rules['unit_price'] = 'required|numeric|min:0';
            $rules['quantity'] = 'required|integer|min:1';
            $rules['signal_amount'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        if ($preOrder->status === PreOrderStatus::AwaitingSignal) {
            $total = round($validated['unit_price'] * $validated['quantity'], 2);
            if ($validated['signal_amount'] > $total) {
                return back()->withErrors(['signal_amount' => 'O sinal não pode ser maior que o total.'])->withInput();
            }
            $validated['total_amount'] = $total;
            $validated['signal_percentage'] = $total > 0 ? round(($validated['signal_amount'] / $total) * 100, 2) : null;
        }

        $preOrder->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Pré-venda {$preOrder->code} atualizada",
            $preOrder
        );

        return back()->with('success', 'Pré-venda atualizada!');
    }

    /**
     * Muda status da pré-venda.
     */
    public function changeStatus(Request $request, PreOrder $preOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $newStatus = PreOrderStatus::tryFrom($validated['status']);
        if (!$newStatus) {
            return back()->withErrors(['status' => 'Status inválido.']);
        }

        if (!in_array($newStatus, $preOrder->status->allowedTransitions())) {
            return back()->withErrors(['status' => 'Transição de status não permitida.']);
        }

        $preOrder->changeStatus(
            $newStatus,
            $validated['note'] ?? null,
            'admin',
            auth('admin')->id()
        );

        AdminActivityLog::log(
            auth('admin')->user(),
            'status_change',
            "Pré-venda {$preOrder->code} → {$newStatus->label()}",
            $preOrder
        );

        return back()->with('success', 'Status atualizado para ' . $newStatus->label());
    }

    /**
     * Marca pagamento manual do sinal (PIX/transferência confirmado pelo admin).
     */
    public function markSignalPaid(Request $request, PreOrder $preOrder): RedirectResponse
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        if ($preOrder->status !== PreOrderStatus::AwaitingSignal) {
            return back()->withErrors(['status' => 'Pré-venda não está aguardando sinal.']);
        }

        $preOrder->signal_payment_method = 'manual';
        $preOrder->signal_paid_at = now();
        $preOrder->save();

        $preOrder->changeStatus(
            PreOrderStatus::SignalPaid,
            $request->get('note') ?: 'Sinal confirmado manualmente',
            'admin',
            auth('admin')->id()
        );

        return back()->with('success', 'Sinal marcado como pago!');
    }

    /**
     * Marca pagamento manual do saldo.
     */
    public function markBalancePaid(Request $request, PreOrder $preOrder): RedirectResponse
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        if ($preOrder->status !== PreOrderStatus::AwaitingBalance) {
            return back()->withErrors(['status' => 'Pré-venda não está aguardando saldo.']);
        }

        $preOrder->balance_payment_method = 'manual';
        $preOrder->balance_paid_at = now();
        $preOrder->save();

        $preOrder->changeStatus(
            PreOrderStatus::BalancePaid,
            $request->get('note') ?: 'Saldo confirmado manualmente',
            'admin',
            auth('admin')->id()
        );

        return back()->with('success', 'Saldo marcado como pago!');
    }

    /**
     * Cancela a pré-venda.
     */
    public function cancel(Request $request, PreOrder $preOrder): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:3',
        ]);

        if ($preOrder->status->isFinal()) {
            return back()->withErrors(['status' => 'Pré-venda já está em status final.']);
        }

        $preOrder->cancellation_reason = $validated['cancellation_reason'];
        $preOrder->save();

        $preOrder->changeStatus(
            PreOrderStatus::Cancelled,
            $validated['cancellation_reason'],
            'admin',
            auth('admin')->id()
        );

        AdminActivityLog::log(
            auth('admin')->user(),
            'cancel',
            "Pré-venda {$preOrder->code} cancelada",
            $preOrder
        );

        return redirect()
            ->route('admin.pre-orders.index')
            ->with('success', 'Pré-venda cancelada.');
    }

    /**
     * Export CSV das pré-vendas (respeita filtros do index).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = PreOrder::with(['client', 'vinylStock.vinylMaster.mainArtists']);

        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'signal_overdue') {
                $query->signalOverdue();
            } elseif ($status === 'balance_overdue') {
                $query->balanceOverdue();
            } else {
                $query->where('status', $status);
            }
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $filename = 'pre-vendas-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para abrir corretamente no Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Código', 'Cliente', 'Email', 'Disco', 'Artista',
                'Qtd', 'Preço Unit.', 'Total', 'Sinal', '% Sinal', 'Saldo',
                'Status', 'Vencimento Sinal', 'Sinal Pago em',
                'Chegada Prevista', 'Chegou em', 'Vencimento Saldo', 'Saldo Pago em',
                'Enviado em', 'Entregue em', 'Cancelado em', 'Motivo Cancelamento',
                'Criado em',
            ], ';');

            $query->orderByDesc('created_at')->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $po) {
                    $vm = $po->vinylStock?->vinylMaster;
                    $artists = $vm?->mainArtists?->pluck('name')->implode(', ');
                    fputcsv($out, [
                        $po->code,
                        $po->client?->name,
                        $po->client?->email,
                        $vm?->title,
                        $artists,
                        $po->quantity,
                        number_format((float) $po->unit_price, 2, ',', '.'),
                        number_format((float) $po->total_amount, 2, ',', '.'),
                        number_format((float) $po->signal_amount, 2, ',', '.'),
                        $po->signal_percentage,
                        number_format((float) ($po->total_amount - $po->signal_amount), 2, ',', '.'),
                        $po->status->label(),
                        $po->signal_due_date?->format('d/m/Y'),
                        $po->signal_paid_at?->format('d/m/Y H:i'),
                        $po->expected_arrival_date?->format('d/m/Y'),
                        $po->arrived_at?->format('d/m/Y H:i'),
                        $po->balance_due_date?->format('d/m/Y'),
                        $po->balance_paid_at?->format('d/m/Y H:i'),
                        $po->shipped_at?->format('d/m/Y H:i'),
                        $po->delivered_at?->format('d/m/Y H:i'),
                        $po->cancelled_at?->format('d/m/Y H:i'),
                        $po->cancellation_reason,
                        $po->created_at?->format('d/m/Y H:i'),
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Relatório financeiro mensal (recebido / a receber por mês).
     */
    public function report(Request $request): View
    {
        $months = (int) $request->get('months', 12);
        $months = max(1, min(36, $months));

        $start = now()->startOfMonth()->subMonths($months - 1);

        // Agrega por mês: sinal pago, saldo pago, criação
        $signalPaid = PreOrder::selectRaw("DATE_FORMAT(signal_paid_at, '%Y-%m') as ym, SUM(signal_amount) as total, COUNT(*) as qty")
            ->whereNotNull('signal_paid_at')
            ->where('signal_paid_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $balancePaid = PreOrder::selectRaw("DATE_FORMAT(balance_paid_at, '%Y-%m') as ym, SUM(total_amount - signal_amount) as total, COUNT(*) as qty")
            ->whereNotNull('balance_paid_at')
            ->where('balance_paid_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $created = PreOrder::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as qty, SUM(total_amount) as total")
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $rows = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $months; $i++) {
            $ym = $cursor->format('Y-m');
            $rows[] = [
                'ym' => $ym,
                'label' => $cursor->translatedFormat('M/Y'),
                'created_qty' => (int) ($created[$ym]->qty ?? 0),
                'created_total' => (float) ($created[$ym]->total ?? 0),
                'signal_received' => (float) ($signalPaid[$ym] ?? 0),
                'balance_received' => (float) ($balancePaid[$ym] ?? 0),
            ];
            $cursor->addMonth();
        }

        // Totais a receber (futuro)
        $pending = [
            'signal' => (float) PreOrder::awaitingSignal()->sum('signal_amount'),
            'balance' => (float) PreOrder::awaitingBalance()->selectRaw('SUM(total_amount - signal_amount) as s')->value('s'),
            'signal_overdue' => (float) PreOrder::signalOverdue()->sum('signal_amount'),
            'balance_overdue' => (float) PreOrder::balanceOverdue()->selectRaw('SUM(total_amount - signal_amount) as s')->value('s'),
        ];

        return view('admin.pre-orders.report', [
            'rows' => $rows,
            'pending' => $pending,
            'months' => $months,
        ]);
    }
}
