<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\FinancialTransaction;
use App\Models\IncomeSource;
use App\Models\PaymentCategory;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = FinancialTransaction::with(['category', 'incomeSource', 'supplier']);

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by category
        if ($categoryId = $request->get('category')) {
            $query->where('payment_category_id', $categoryId);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->where('due_date', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->where('due_date', '<=', $endDate);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('due_date')->paginate(20)->withQueryString();

        $categories = PaymentCategory::active()->orderBy('name')->get();
        
        // Summary
        $summary = [
            'total_payable' => FinancialTransaction::payable()->pending()->sum('amount'),
            'total_receivable' => FinancialTransaction::receivable()->pending()->sum('amount'),
            'overdue' => FinancialTransaction::overdue()->sum('amount'),
        ];

        return view('admin.financial.transactions.index', compact('transactions', 'categories', 'summary'));
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

        return view('admin.financial.transactions.create', compact('type', 'categories', 'incomeSources', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:payable,receivable',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
            'income_source_id' => 'nullable|exists:income_sources,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('financial-attachments', 'public');
        }

        $validated['created_by'] = auth('admin')->id();
        $validated['status'] = 'pending';

        $transaction = FinancialTransaction::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Transação financeira criada: {$transaction->description}",
            $transaction
        );

        return redirect()
            ->route('admin.financial.transactions.index')
            ->with('success', 'Transação criada com sucesso!');
    }

    public function show(FinancialTransaction $transaction): View
    {
        $transaction->load(['category', 'incomeSource', 'supplier', 'recurringPayment', 'createdBy']);
        return view('admin.financial.transactions.show', compact('transaction'));
    }

    public function edit(FinancialTransaction $transaction): View
    {
        $categories = PaymentCategory::active()
            ->when($transaction->type === 'payable', fn($q) => $q->expense())
            ->when($transaction->type === 'receivable', fn($q) => $q->income())
            ->orderBy('name')
            ->get();
        $incomeSources = IncomeSource::active()->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('admin.financial.transactions.edit', compact('transaction', 'categories', 'incomeSources', 'suppliers'));
    }

    public function update(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
            'income_source_id' => 'nullable|exists:income_sources,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('financial-attachments', 'public');
        }

        $transaction->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Transação financeira atualizada: {$transaction->description}",
            $transaction
        );

        return redirect()
            ->route('admin.financial.transactions.index')
            ->with('success', 'Transação atualizada com sucesso!');
    }

    public function destroy(FinancialTransaction $transaction): RedirectResponse
    {
        if ($transaction->attachment) {
            Storage::disk('public')->delete($transaction->attachment);
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Transação financeira excluída: {$transaction->description}"
        );

        $transaction->delete();

        return redirect()
            ->route('admin.financial.transactions.index')
            ->with('success', 'Transação excluída com sucesso!');
    }

    public function markAsPaid(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
        ]);

        $transaction->markAsPaid($validated['payment_date'], $validated['payment_method'] ?? null);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Transação marcada como paga: {$transaction->description}",
            $transaction
        );

        return redirect()
            ->back()
            ->with('success', 'Transação marcada como paga!');
    }

    public function cancel(FinancialTransaction $transaction): RedirectResponse
    {
        $transaction->cancel();

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Transação cancelada: {$transaction->description}",
            $transaction
        );

        return redirect()
            ->back()
            ->with('success', 'Transação cancelada!');
    }
}
