<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Dimension;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DimensionController extends Controller
{
    public function index(): View
    {
        $dimensions = Dimension::orderBy('name')->paginate(15);

        return view('admin.settings.dimensions.index', compact('dimensions'));
    }

    public function create(): View
    {
        return view('admin.settings.dimensions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'height' => ['required', 'numeric', 'min:0.01'],
            'width' => ['required', 'numeric', 'min:0.01'],
            'depth' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $dimension = Dimension::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Dimensão '{$dimension->name}' criada",
            $dimension
        );

        return redirect()
            ->route('admin.settings.dimensions.index')
            ->with('success', 'Dimensão criada com sucesso!');
    }

    public function edit(Dimension $dimension): View
    {
        return view('admin.settings.dimensions.edit', compact('dimension'));
    }

    public function update(Request $request, Dimension $dimension): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'height' => ['required', 'numeric', 'min:0.01'],
            'width' => ['required', 'numeric', 'min:0.01'],
            'depth' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $dimension->toArray();
        $dimension->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Dimensão '{$dimension->name}' atualizada",
            $dimension,
            $oldValues,
            $dimension->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.dimensions.index')
            ->with('success', 'Dimensão atualizada com sucesso!');
    }

    public function destroy(Dimension $dimension): RedirectResponse
    {
        $name = $dimension->name;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Dimensão '{$name}' excluída"
        );

        $dimension->delete();

        return redirect()
            ->route('admin.settings.dimensions.index')
            ->with('success', 'Dimensão excluída com sucesso!');
    }
}
