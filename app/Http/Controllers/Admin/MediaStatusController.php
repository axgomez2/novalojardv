<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\MediaStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaStatusController extends Controller
{
    public function index(): View
    {
        $mediaStatuses = MediaStatus::ordered()->paginate(15);

        return view('admin.settings.media-statuses.index', compact('mediaStatuses'));
    }

    public function create(): View
    {
        return view('admin.settings.media-statuses.create');
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

        $mediaStatus = MediaStatus::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Estado de mídia '{$mediaStatus->title}' criado",
            $mediaStatus
        );

        return redirect()
            ->route('admin.settings.media-statuses.index')
            ->with('success', 'Estado de mídia criado com sucesso!');
    }

    public function edit(MediaStatus $mediaStatus): View
    {
        return view('admin.settings.media-statuses.edit', compact('mediaStatus'));
    }

    public function update(Request $request, MediaStatus $mediaStatus): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $request->input('sort_order', 0);

        $oldValues = $mediaStatus->toArray();
        $mediaStatus->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Estado de mídia '{$mediaStatus->title}' atualizado",
            $mediaStatus,
            $oldValues,
            $mediaStatus->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.media-statuses.index')
            ->with('success', 'Estado de mídia atualizado com sucesso!');
    }

    public function destroy(MediaStatus $mediaStatus): RedirectResponse
    {
        $title = $mediaStatus->title;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Estado de mídia '{$title}' excluído"
        );

        $mediaStatus->delete();

        return redirect()
            ->route('admin.settings.media-statuses.index')
            ->with('success', 'Estado de mídia excluído com sucesso!');
    }
}
