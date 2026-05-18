<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\HomeBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeBannerController extends Controller
{
    public function index(): View
    {
        $banners = HomeBanner::ordered()->get();
        $activeCount = $banners->where('is_active', true)->count();

        return view('admin.home-banners.index', [
            'banners' => $banners,
            'max' => HomeBanner::MAX_BANNERS,
            'activeCount' => $activeCount,
            'canCreate' => $banners->count() < HomeBanner::MAX_BANNERS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (HomeBanner::count() >= HomeBanner::MAX_BANNERS) {
            return back()->with('error', 'Limite de ' . HomeBanner::MAX_BANNERS . ' banners atingido. Exclua um antes de adicionar outro.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:120',
            'subtitle' => 'nullable|string|max:200',
            'link_url' => 'nullable|url|max:500',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'image.required' => 'A imagem é obrigatória.',
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'image.max' => 'Imagem máxima de 4 MB.',
            'link_url.url' => 'Informe uma URL válida (ex.: https://...).',
        ]);

        $path = $request->file('image')->store('home-banners', 'public');

        $banner = HomeBanner::create([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'is_active' => $request->boolean('is_active', true),
            'image_path' => $path,
            'sort_order' => (int) (HomeBanner::max('sort_order') ?? 0) + 1,
        ]);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Banner #{$banner->id} criado",
            $banner
        );

        return redirect()
            ->route('admin.home-banners.index')
            ->with('success', 'Banner adicionado com sucesso!');
    }

    public function update(Request $request, HomeBanner $homeBanner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:120',
            'subtitle' => 'nullable|string|max:200',
            'link_url' => 'nullable|url|max:500',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $data = [
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            // remove imagem antiga
            if ($homeBanner->image_path && Storage::disk('public')->exists($homeBanner->image_path)) {
                Storage::disk('public')->delete($homeBanner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('home-banners', 'public');
        }

        // Garante pelo menos 1 ativo
        if (!$data['is_active'] && $homeBanner->is_active) {
            $otherActive = HomeBanner::where('id', '!=', $homeBanner->id)->where('is_active', true)->count();
            if ($otherActive === 0) {
                return back()->with('error', 'É necessário ter pelo menos 1 banner ativo. Ative outro antes de desativar este.');
            }
        }

        $homeBanner->update($data);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Banner #{$homeBanner->id} atualizado",
            $homeBanner
        );

        return back()->with('success', 'Banner atualizado!');
    }

    public function toggle(HomeBanner $homeBanner): RedirectResponse
    {
        // Não permite desativar o último ativo
        if ($homeBanner->is_active) {
            $otherActive = HomeBanner::where('id', '!=', $homeBanner->id)->where('is_active', true)->count();
            if ($otherActive === 0) {
                return back()->with('error', 'Pelo menos 1 banner deve permanecer ativo.');
            }
        }

        $homeBanner->update(['is_active' => !$homeBanner->is_active]);

        return back()->with('success', $homeBanner->is_active ? 'Banner ativado!' : 'Banner desativado!');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:home_banners,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            HomeBanner::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Ordem dos banners atualizada!');
    }

    public function destroy(HomeBanner $homeBanner): RedirectResponse
    {
        // Não permite excluir o último ativo
        if ($homeBanner->is_active) {
            $otherActive = HomeBanner::where('id', '!=', $homeBanner->id)->where('is_active', true)->count();
            if ($otherActive === 0 && HomeBanner::count() > 1) {
                return back()->with('error', 'Este é o único banner ativo. Ative outro antes de excluí-lo.');
            }
        }

        if ($homeBanner->image_path && Storage::disk('public')->exists($homeBanner->image_path)) {
            Storage::disk('public')->delete($homeBanner->image_path);
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Banner #{$homeBanner->id} excluído"
        );

        $homeBanner->delete();

        return back()->with('success', 'Banner removido.');
    }
}
