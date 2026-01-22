<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('children', 'parent')
            ->parents()
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::parents()->active()->orderBy('sort_order')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Categoria '{$category->name}' criada",
            $category
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category): View
    {
        $parents = Category::parents()
            ->where('id', '!=', $category->id)
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        // Prevent setting itself as parent
        if ($validated['parent_id'] == $category->id) {
            return back()->with('error', 'Uma categoria não pode ser pai de si mesma.');
        }

        $oldValues = $category->toArray();
        $category->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Categoria '{$category->name}' atualizada",
            $category,
            $oldValues,
            $category->fresh()->toArray()
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->hasChildren()) {
            return back()->with('error', 'Não é possível excluir uma categoria que possui subcategorias.');
        }

        $name = $category->name;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Categoria '{$name}' excluída"
        );

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }
}
