<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\IncomeSource;
use App\Models\PaymentCategory;
use App\Models\RecurringPayment;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = RecurringPayment::with(['category', 'supplier']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($request->get('active') !== null) {
            $query->where('is_active', $request->boolean('active'));
        }

        $payments = $query->orderBy('next_due_date')->paginate(20)->withQueryString();

        return view('admin.financial.recurring.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $type = $request->get('type', 'payable');
        $categories = PaymentCategory::active()
            ->when($type === 'payable', fn($q) => $q->expense())
            ->when($type === 'receivable', fn($q) => $q->income())
            ->orderBy('name')
            ->get();
        $incomeSources = IncomeSource::active()->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('admin.financial.recurring.create', compact('type', 'categories', 'incomeSources', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:payable,receivable',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,bimonthly,quarterly,semiannual,annual',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
            'income_source_id' => 'nullable|exists:income_sources,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'payment_method' => 'nullable|string|max:100',
            'auto_generate' => 'boolean',
            'days_before_notify' => 'nullable|integer|min:0|max:30',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['auto_generate'] = $request->boolean('auto_generate', true);
        $validated['created_by'] = auth('admin')->id();
        $validated['next_due_date'] = $validated['start_date'];

        $payment = RecurringPayment::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Pagamento recorrente criado: {$payment->name}",
            $payment
        );

        return redirect()
            ->route('admin.financial.recurring.index')
            ->with('success', 'Pagamento recorrente criado com sucesso!');
    }

    public function show(RecurringPayment $recurring): View
    {
        $recurring->load(['category', 'incomeSource', 'supplier', 'transactions' => function ($q) {
            $q->latest('due_date')->limit(10);
        }]);

        return view('admin.financial.recurring.show', ['payment' => $recurring]);
    }

    public function edit(RecurringPayment $recurring): View
    {
        $categories = PaymentCategory::active()
            ->when($recurring->type === 'payable', fn($q) => $q->expense())
            ->when($recurring->type === 'receivable', fn($q) => $q->income())
            ->orderBy('name')
            ->get();
        $incomeSources = IncomeSource::active()->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('admin.financial.recurring.edit', [
            'payment' => $recurring,
            'categories' => $categories,
            'incomeSources' => $incomeSources,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(Request $request, RecurringPayment $recurring): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,bimonthly,quarterly,semiannual,annual',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'end_date' => 'nullable|date',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
            'income_source_id' => 'nullable|exists:income_sources,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'payment_method' => 'nullable|string|max:100',
            'auto_generate' => 'boolean',
            'days_before_notify' => 'nullable|integer|min:0|max:30',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['auto_generate'] = $request->boolean('auto_generate', true);

        $recurring->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Pagamento recorrente atualizado: {$recurring->name}",
            $recurring
        );

        return redirect()
            ->route('admin.financial.recurring.index')
            ->with('success', 'Pagamento recorrente atualizado com sucesso!');
    }

    public function destroy(RecurringPayment $recurring): RedirectResponse
    {
        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Pagamento recorrente excluído: {$recurring->name}"
        );

        $recurring->delete();

        return redirect()
            ->route('admin.financial.recurring.index')
            ->with('success', 'Pagamento recorrente excluído com sucesso!');
    }

    public function generateTransaction(RecurringPayment $recurring): RedirectResponse
    {
        if (!$recurring->is_active) {
            return redirect()->back()->with('error', 'Pagamento recorrente está inativo.');
        }

        $transaction = $recurring->generateTransaction();

        if ($transaction) {
            AdminActivityLog::log(
                auth('admin')->user(),
                'create',
                "Transação gerada a partir de pagamento recorrente: {$recurring->name}",
                $transaction
            );

            return redirect()->back()->with('success', 'Transação gerada com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível gerar a transação.');
    }

    public function toggleActive(RecurringPayment $recurring): RedirectResponse
    {
        $recurring->update(['is_active' => !$recurring->is_active]);

        $status = $recurring->is_active ? 'ativado' : 'desativado';

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Pagamento recorrente {$status}: {$recurring->name}",
            $recurring
        );

        return redirect()->back()->with('success', "Pagamento recorrente {$status}!");
    }
}
