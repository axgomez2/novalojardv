<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chart;
use App\Models\Category;
use App\Models\VinylMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChartController extends Controller
{
    public function index()
    {
        $charts = Chart::with(['category', 'vinyls'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15);

        return view('admin.charts.index', compact('charts'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $types = Chart::getTypes();

        return view('admin.charts.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:style,bestsellers,new_releases,custom',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'max_tracks' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active');

        $chart = Chart::create($validated);

        return redirect()->route('admin.music.charts.edit', $chart)
            ->with('success', 'Chart criado com sucesso! Agora adicione os discos.');
    }

    public function show(Chart $chart)
    {
        $chart->load(['category', 'vinyls.mainArtists']);

        return view('admin.charts.show', compact('chart'));
    }

    public function edit(Chart $chart)
    {
        $chart->load(['vinyls.mainArtists']);
        $categories = Category::orderBy('name')->get();
        $types = Chart::getTypes();

        return view('admin.charts.edit', compact('chart', 'categories', 'types'));
    }

    public function update(Request $request, Chart $chart)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:style,bestsellers,new_releases,custom',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'max_tracks' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($request->slug);
        }

        $chart->update($validated);

        return redirect()->route('admin.music.charts.index')
            ->with('success', 'Chart atualizado com sucesso!');
    }

    public function destroy(Chart $chart)
    {
        $chart->vinyls()->detach();
        $chart->delete();

        return redirect()->route('admin.music.charts.index')
            ->with('success', 'Chart excluído com sucesso!');
    }

    public function searchVinyls(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $vinyls = VinylMaster::with(['mainArtists'])
                ->where('title', 'like', "%{$query}%")
                ->orWhereHas('mainArtists', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get();

            $result = $vinyls->map(function ($vinyl) {
                return [
                    'id' => $vinyl->id,
                    'title' => $vinyl->title,
                    'artist' => $vinyl->artist_names ?? 'N/A',
                    'cover_url' => $vinyl->cover_url,
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addVinyl(Request $request, Chart $chart)
    {
        $validated = $request->validate([
            'vinyl_id' => 'required|exists:vinyl_masters,id',
        ]);

        if ($chart->vinyls()->count() >= $chart->max_tracks) {
            return back()->with('error', "Este chart já atingiu o limite de {$chart->max_tracks} discos.");
        }

        if ($chart->vinyls()->where('vinyl_master_id', $validated['vinyl_id'])->exists()) {
            return back()->with('error', 'Este disco já está no chart.');
        }

        $nextPosition = $chart->vinyls()->max('chart_vinyls.position') + 1;

        $chart->vinyls()->attach($validated['vinyl_id'], ['position' => $nextPosition]);
        $chart->update(['last_updated_at' => now()]);

        return back()->with('success', 'Disco adicionado ao chart!');
    }

    public function removeVinyl(Chart $chart, VinylMaster $vinyl)
    {
        $chart->vinyls()->detach($vinyl->id);
        $chart->update(['last_updated_at' => now()]);

        $this->reorderVinyls($chart);

        return back()->with('success', 'Disco removido do chart!');
    }

    private function reorderVinyls(Chart $chart): void
    {
        $position = 1;
        foreach ($chart->vinyls()->orderByPivot('position')->get() as $vinyl) {
            $chart->vinyls()->updateExistingPivot($vinyl->id, ['position' => $position]);
            $position++;
        }
    }

    public function updateVinylOrder(Request $request, Chart $chart)
    {
        $validated = $request->validate([
            'vinyls' => 'required|array',
            'vinyls.*' => 'exists:vinyl_masters,id',
        ]);

        foreach ($validated['vinyls'] as $position => $vinylId) {
            $chart->vinyls()->updateExistingPivot($vinylId, ['position' => $position + 1]);
        }

        $chart->update(['last_updated_at' => now()]);

        return response()->json(['success' => true]);
    }
}
