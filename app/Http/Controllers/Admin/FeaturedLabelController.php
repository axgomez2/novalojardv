<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\FeaturedLabel;
use App\Models\RecordLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FeaturedLabelController extends Controller
{
    public const MAX_LABELS = 8;

    public function index(): View
    {
        $featuredLabels = FeaturedLabel::with('recordLabel')
            ->ordered()
            ->get();

        // Gravadoras disponíveis (que ainda não estão em destaque)
        $usedLabelIds = $featuredLabels->pluck('record_label_id')->toArray();
        $availableLabels = RecordLabel::active()
            ->whereNotIn('id', $usedLabelIds)
            ->orderBy('name')
            ->get();

        return view('admin.featured-labels.index', [
            'featuredLabels' => $featuredLabels,
            'availableLabels' => $availableLabels,
            'max' => self::MAX_LABELS,
            'canCreate' => $featuredLabels->count() < self::MAX_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (FeaturedLabel::count() >= self::MAX_LABELS) {
            return back()->with('error', 'Limite de ' . self::MAX_LABELS . ' selos em destaque atingido.');
        }

        $validated = $request->validate([
            'record_label_id' => 'required|exists:record_labels,id|unique:featured_labels,record_label_id',
            'custom_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_active' => 'boolean',
        ], [
            'record_label_id.required' => 'Selecione uma gravadora.',
            'record_label_id.unique' => 'Esta gravadora já está em destaque.',
            'custom_logo.image' => 'O arquivo precisa ser uma imagem.',
            'custom_logo.max' => 'Imagem máxima de 2 MB.',
        ]);

        $data = [
            'record_label_id' => $validated['record_label_id'],
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) (FeaturedLabel::max('sort_order') ?? 0) + 1,
        ];

        if ($request->hasFile('custom_logo')) {
            $data['custom_logo'] = $request->file('custom_logo')->store('featured-labels', 'public');
        }

        $featuredLabel = FeaturedLabel::create($data);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Selo em destaque #{$featuredLabel->id} criado ({$featuredLabel->recordLabel->name})",
            $featuredLabel
        );

        return redirect()
            ->route('admin.featured-labels.index')
            ->with('success', 'Selo adicionado aos destaques!');
    }

    public function update(Request $request, FeaturedLabel $featuredLabel): RedirectResponse
    {
        $validated = $request->validate([
            'custom_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = [
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('custom_logo')) {
            // Remove logo antigo
            if ($featuredLabel->custom_logo && Storage::disk('public')->exists($featuredLabel->custom_logo)) {
                Storage::disk('public')->delete($featuredLabel->custom_logo);
            }
            $data['custom_logo'] = $request->file('custom_logo')->store('featured-labels', 'public');
        }

        if ($request->boolean('remove_custom_logo') && $featuredLabel->custom_logo) {
            Storage::disk('public')->delete($featuredLabel->custom_logo);
            $data['custom_logo'] = null;
        }

        $featuredLabel->update($data);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Selo em destaque #{$featuredLabel->id} atualizado",
            $featuredLabel
        );

        return back()->with('success', 'Selo atualizado!');
    }

    public function toggle(FeaturedLabel $featuredLabel): RedirectResponse
    {
        $featuredLabel->update(['is_active' => !$featuredLabel->is_active]);

        return back()->with('success', $featuredLabel->is_active ? 'Selo ativado!' : 'Selo desativado!');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:featured_labels,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            FeaturedLabel::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Ordem dos selos atualizada!');
    }

    public function destroy(FeaturedLabel $featuredLabel): RedirectResponse
    {
        if ($featuredLabel->custom_logo && Storage::disk('public')->exists($featuredLabel->custom_logo)) {
            Storage::disk('public')->delete($featuredLabel->custom_logo);
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Selo em destaque #{$featuredLabel->id} removido ({$featuredLabel->recordLabel->name})"
        );

        $featuredLabel->delete();

        return back()->with('success', 'Selo removido dos destaques.');
    }
}
