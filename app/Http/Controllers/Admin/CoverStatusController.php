<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\CoverStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoverStatusController extends Controller
{
    public function index(): View
    {
        $coverStatuses = CoverStatus::ordered()->paginate(15);

        return view('admin.settings.cover-statuses.index', compact('coverStatuses'));
    }

    public function create(): View
    {
        return view('admin.settings.cover-statuses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $request->input('sort_order', 0);

        $coverStatus = CoverStatus::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Estado de capa '{$coverStatus->title}' criado",
            $coverStatus
        );

        return redirect()
            ->route('admin.settings.cover-statuses.index')
            ->with('success', 'Estado de capa criado com sucesso!');
    }

    public function edit(CoverStatus $coverStatus): View
    {
        return view('admin.settings.cover-statuses.edit', compact('coverStatus'));
    }

    public function update(Request $request, CoverStatus $coverStatus): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $request->input('sort_order', 0);

        $oldValues = $coverStatus->toArray();
        $coverStatus->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Estado de capa '{$coverStatus->title}' atualizado",
            $coverStatus,
            $oldValues,
            $coverStatus->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.cover-statuses.index')
            ->with('success', 'Estado de capa atualizado com sucesso!');
    }

    public function destroy(CoverStatus $coverStatus): RedirectResponse
    {
        $title = $coverStatus->title;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Estado de capa '{$title}' excluído"
        );

        $coverStatus->delete();

        return redirect()
            ->route('admin.settings.cover-statuses.index')
            ->with('success', 'Estado de capa excluído com sucesso!');
    }
}
