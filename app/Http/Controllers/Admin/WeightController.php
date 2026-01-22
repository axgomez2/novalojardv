<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Weight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeightController extends Controller
{
    public function index(): View
    {
        $weights = Weight::orderBy('value')->paginate(15);

        return view('admin.settings.weights.index', compact('weights'));
    }

    public function create(): View
    {
        return view('admin.settings.weights.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $weight = Weight::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Peso '{$weight->name}' criado",
            $weight
        );

        return redirect()
            ->route('admin.settings.weights.index')
            ->with('success', 'Peso criado com sucesso!');
    }

    public function edit(Weight $weight): View
    {
        return view('admin.settings.weights.edit', compact('weight'));
    }

    public function update(Request $request, Weight $weight): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $weight->toArray();
        $weight->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Peso '{$weight->name}' atualizado",
            $weight,
            $oldValues,
            $weight->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.weights.index')
            ->with('success', 'Peso atualizado com sucesso!');
    }

    public function destroy(Weight $weight): RedirectResponse
    {
        $name = $weight->name;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Peso '{$name}' excluído"
        );

        $weight->delete();

        return redirect()
            ->route('admin.settings.weights.index')
            ->with('success', 'Peso excluído com sucesso!');
    }
}
