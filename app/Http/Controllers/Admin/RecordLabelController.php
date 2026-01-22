<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\RecordLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecordLabelController extends Controller
{
    public function index(Request $request): View
    {
        $query = RecordLabel::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('discogs_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $recordLabels = $query->latest()->paginate(15)->withQueryString();

        return view('admin.settings.record-labels.index', compact('recordLabels'));
    }

    public function create(): View
    {
        return view('admin.settings.record-labels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:record_labels'],
            'discogs_id' => ['nullable', 'string', 'max:50', 'unique:record_labels'],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $recordLabel = RecordLabel::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Gravadora '{$recordLabel->name}' criada",
            $recordLabel
        );

        return redirect()
            ->route('admin.settings.record-labels.index')
            ->with('success', 'Gravadora criada com sucesso!');
    }

    public function edit(RecordLabel $recordLabel): View
    {
        return view('admin.settings.record-labels.edit', compact('recordLabel'));
    }

    public function update(Request $request, RecordLabel $recordLabel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('record_labels')->ignore($recordLabel->id)],
            'discogs_id' => ['nullable', 'string', 'max:50', Rule::unique('record_labels')->ignore($recordLabel->id)],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $recordLabel->toArray();
        $recordLabel->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Gravadora '{$recordLabel->name}' atualizada",
            $recordLabel,
            $oldValues,
            $recordLabel->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.record-labels.index')
            ->with('success', 'Gravadora atualizada com sucesso!');
    }

    public function destroy(RecordLabel $recordLabel): RedirectResponse
    {
        $name = $recordLabel->name;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Gravadora '{$name}' excluída"
        );

        $recordLabel->delete();

        return redirect()
            ->route('admin.settings.record-labels.index')
            ->with('success', 'Gravadora excluída com sucesso!');
    }
}
