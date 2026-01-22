<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\PaymentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentCategoryController extends Controller
{
    public function index(): View
    {
        $categories = PaymentCategory::with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.financial.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parentCategories = PaymentCategory::roots()->active()->orderBy('name')->get();
        return view('admin.financial.categories.create', compact('parentCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:100',
            'type' => 'required|in:expense,income,both',
            'parent_id' => 'nullable|exists:payment_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $category = PaymentCategory::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Categoria de pagamento criada: {$category->name}",
            $category
        );

        return redirect()
            ->route('admin.financial.categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(PaymentCategory $category): View
    {
        $parentCategories = PaymentCategory::roots()
            ->where('id', '!=', $category->id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.financial.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, PaymentCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:100',
            'type' => 'required|in:expense,income,both',
            'parent_id' => 'nullable|exists:payment_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Categoria de pagamento atualizada: {$category->name}",
            $category
        );

        return redirect()
            ->route('admin.financial.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(PaymentCategory $category): RedirectResponse
    {
        if ($category->transactions()->exists()) {
            return redirect()
                ->route('admin.financial.categories.index')
                ->with('error', 'Não é possível excluir categoria com transações vinculadas.');
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Categoria de pagamento excluída: {$category->name}"
        );

        $category->delete();

        return redirect()
            ->route('admin.financial.categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }
}
