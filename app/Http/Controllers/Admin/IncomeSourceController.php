<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\IncomeSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeSourceController extends Controller
{
    public function index(): View
    {
        $sources = IncomeSource::orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.financial.income-sources.index', compact('sources'));
    }

    public function create(): View
    {
        return view('admin.financial.income-sources.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $source = IncomeSource::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Origem de receita criada: {$source->name}",
            $source
        );

        return redirect()
            ->route('admin.financial.income-sources.index')
            ->with('success', 'Origem de receita criada com sucesso!');
    }

    public function edit(IncomeSource $incomeSource): View
    {
        return view('admin.financial.income-sources.edit', ['source' => $incomeSource]);
    }

    public function update(Request $request, IncomeSource $incomeSource): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $incomeSource->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Origem de receita atualizada: {$incomeSource->name}",
            $incomeSource
        );

        return redirect()
            ->route('admin.financial.income-sources.index')
            ->with('success', 'Origem de receita atualizada com sucesso!');
    }

    public function destroy(IncomeSource $incomeSource): RedirectResponse
    {
        if ($incomeSource->transactions()->exists()) {
            return redirect()
                ->route('admin.financial.income-sources.index')
                ->with('error', 'Não é possível excluir origem com transações vinculadas.');
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Origem de receita excluída: {$incomeSource->name}"
        );

        $incomeSource->delete();

        return redirect()
            ->route('admin.financial.income-sources.index')
            ->with('success', 'Origem de receita excluída com sucesso!');
    }
}
