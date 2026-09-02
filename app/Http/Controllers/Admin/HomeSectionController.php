<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\VinylStock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeSectionController extends Controller
{
    public function index()
    {
        $sections = HomeSection::with(['items.vinyl.vinylMaster'])
            ->ordered()
            ->get();

        return view('admin.home-sections.index', compact('sections'));
    }

    public function create()
    {
        $types = HomeSection::TYPES;
        return view('admin.home-sections.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(HomeSection::TYPES)),
            'max_items' => 'required|integer|min:1|max:50',
            'view_all_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = HomeSection::max('sort_order') + 1;

        $section = HomeSection::create($validated);

        return redirect()
            ->route('admin.home-sections.edit', $section)
            ->with('success', 'Seção criada com sucesso! Agora adicione os discos.');
    }

    public function edit(HomeSection $homeSection)
    {
        $homeSection->load(['items.vinyl.vinylMaster']);
        $types = HomeSection::TYPES;

        return view('admin.home-sections.edit', compact('homeSection', 'types'));
    }

    public function update(Request $request, HomeSection $homeSection)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(HomeSection::TYPES)),
            'max_items' => 'required|integer|min:1|max:50',
            'view_all_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $homeSection->update($validated);

        return redirect()
            ->route('admin.home-sections.index')
            ->with('success', 'Seção atualizada com sucesso!');
    }

    public function destroy(HomeSection $homeSection)
    {
        $homeSection->delete();

        return redirect()
            ->route('admin.home-sections.index')
            ->with('success', 'Seção removida com sucesso!');
    }

    public function searchVinyls(Request $request)
    {
        \Log::info('searchVinyls called', [
            'q' => $request->get('q'),
            'section_id' => $request->get('section_id'),
            'type' => $request->get('type'),
            'user' => auth('admin')->user()?->email,
        ]);
        
        $query = $request->get('q', '');
        $sectionId = $request->get('section_id');
        $type = $request->get('type');

        $existingIds = [];
        if ($sectionId) {
            $existingIds = HomeSectionItem::where('home_section_id', $sectionId)
                ->pluck('vinyl_stock_id')
                ->toArray();
        }

        $vinyls = VinylStock::with(['vinylMaster.mainArtists'])
            ->whereNotIn('id', $existingIds)
            ->where('visibility', 'public')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->whereHas('vinylMaster', function ($q3) use ($query) {
                        $q3->where('title', 'like', "%{$query}%");
                    })->orWhereHas('vinylMaster.mainArtists', function ($q3) use ($query) {
                        $q3->where('name', 'like', "%{$query}%");
                    });
                });
            })
            ->when($type === 'discos_novos', fn($q) => $q->where('is_new', true)->where('availability', 'available'))
            ->when($type === 'pre_venda', fn($q) => $q->where('availability', 'preorder'))
            ->when($type === 'discos_usados', fn($q) => $q->where('is_new', false)->where('availability', 'available'))
            ->limit(20)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'title' => $v->vinylMaster->title ?? 'Sem título',
                'artist' => $v->vinylMaster->artist_names ?? 'Sem artista',
                'cover' => $v->vinylMaster->cover_url,
                'price' => 'R$ ' . number_format($v->current_price, 2, ',', '.'),
                'stock' => $v->stock,
                'is_new' => $v->is_new,
                'availability' => $v->availability,
            ]);

        return response()->json($vinyls);
    }

    public function addItem(Request $request, HomeSection $homeSection)
    {
        $validated = $request->validate([
            'vinyl_stock_id' => 'required|exists:vinyl_stocks,id',
        ]);

        $exists = $homeSection->items()->where('vinyl_stock_id', $validated['vinyl_stock_id'])->exists();
        if ($exists) {
            return response()->json(['error' => 'Este disco já está na seção'], 422);
        }

        $maxPosition = $homeSection->items()->max('position') ?? 0;

        $item = $homeSection->items()->create([
            'vinyl_stock_id' => $validated['vinyl_stock_id'],
            'position' => $maxPosition + 1,
        ]);

        $item->load('vinyl.vinylMaster');

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'vinyl_stock_id' => $item->vinyl_stock_id,
                'position' => $item->position,
                'title' => $item->vinyl->vinylMaster->title ?? 'Sem título',
                'artist' => $item->vinyl->vinylMaster->artist_names ?? 'Sem artista',
                'cover' => $item->vinyl->vinylMaster->cover_url,
                'price' => 'R$ ' . number_format($item->vinyl->current_price, 2, ',', '.'),
            ],
        ]);
    }

    public function removeItem(HomeSection $homeSection, HomeSectionItem $item)
    {
        if ($item->home_section_id !== $homeSection->id) {
            return response()->json(['error' => 'Item não pertence a esta seção'], 403);
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    public function reorderItems(Request $request, HomeSection $homeSection)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|exists:home_section_items,id',
        ]);

        foreach ($validated['items'] as $position => $itemId) {
            HomeSectionItem::where('id', $itemId)
                ->where('home_section_id', $homeSection->id)
                ->update(['position' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleActive(HomeSection $homeSection)
    {
        $homeSection->update(['is_active' => !$homeSection->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $homeSection->is_active,
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*' => 'integer|exists:home_sections,id',
        ]);

        foreach ($validated['sections'] as $order => $sectionId) {
            HomeSection::where('id', $sectionId)->update(['sort_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }
}
