<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('document', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.settings.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('admin.settings.suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'document' => ['nullable', 'string', 'max:20'],
            'document_type' => ['nullable', 'in:cpf,cnpj'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['document'] = preg_replace('/\D/', '', $validated['document'] ?? '');

        $supplier = Supplier::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Fornecedor '{$supplier->name}' criado",
            $supplier
        );

        return redirect()
            ->route('admin.settings.suppliers.index')
            ->with('success', 'Fornecedor criado com sucesso!');
    }

    public function show(Supplier $supplier): View
    {
        return view('admin.settings.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.settings.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'document' => ['nullable', 'string', 'max:20'],
            'document_type' => ['nullable', 'in:cpf,cnpj'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['document'] = preg_replace('/\D/', '', $validated['document'] ?? '');

        $oldValues = $supplier->toArray();
        $supplier->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Fornecedor '{$supplier->name}' atualizado",
            $supplier,
            $oldValues,
            $supplier->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.suppliers.index')
            ->with('success', 'Fornecedor atualizado com sucesso!');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Fornecedor '{$name}' excluído"
        );

        $supplier->delete();

        return redirect()
            ->route('admin.settings.suppliers.index')
            ->with('success', 'Fornecedor excluído com sucesso!');
    }
}
