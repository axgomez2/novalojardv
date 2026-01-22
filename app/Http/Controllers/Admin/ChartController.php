<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chart;
use App\Models\Category;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChartController extends Controller
{
    public function index()
    {
        $charts = Chart::with(['category', 'tracks'])
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

        return redirect()->route('admin.charts.edit', $chart)
            ->with('success', 'Chart criado com sucesso! Agora adicione as faixas.');
    }

    public function show(Chart $chart)
    {
        $chart->load(['category', 'tracks.vinylMaster.artist']);

        return view('admin.charts.show', compact('chart'));
    }

    public function edit(Chart $chart)
    {
        $chart->load(['tracks.vinylMaster.artist']);
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

        return redirect()->route('admin.charts.index')
            ->with('success', 'Chart atualizado com sucesso!');
    }

    public function destroy(Chart $chart)
    {
        $chart->tracks()->detach();
        $chart->delete();

        return redirect()->route('admin.charts.index')
            ->with('success', 'Chart excluído com sucesso!');
    }

    public function searchTracks(Request $request)
    {
        $query = $request->get('q', '');

        $tracks = Track::with(['vinylMaster.artist'])
            ->whereHas('vinylMaster', function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhereHas('artist', function ($q2) use ($query) {
                        $q2->where('name', 'like', "%{$query}%");
                    });
            })
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(20)
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'name' => $track->name,
                    'vinyl_title' => $track->vinylMaster?->title ?? 'N/A',
                    'artist' => $track->vinylMaster?->artist?->name ?? 'N/A',
                    'duration' => $track->duration,
                ];
            });

        return response()->json($tracks);
    }

    public function addTrack(Request $request, Chart $chart)
    {
        $validated = $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        if ($chart->tracks()->count() >= $chart->max_tracks) {
            return back()->with('error', "Este chart já atingiu o limite de {$chart->max_tracks} faixas.");
        }

        if ($chart->tracks()->where('track_id', $validated['track_id'])->exists()) {
            return back()->with('error', 'Esta faixa já está no chart.');
        }

        $nextPosition = $chart->tracks()->max('position') + 1;

        $chart->tracks()->attach($validated['track_id'], ['position' => $nextPosition]);
        $chart->update(['last_updated_at' => now()]);

        return back()->with('success', 'Faixa adicionada ao chart!');
    }

    public function removeTrack(Chart $chart, Track $track)
    {
        $chart->tracks()->detach($track->id);
        $chart->update(['last_updated_at' => now()]);

        $this->reorderTracks($chart);

        return back()->with('success', 'Faixa removida do chart!');
    }

    public function reorderTracks(Chart $chart)
    {
        $position = 1;
        foreach ($chart->tracks()->orderByPivot('position')->get() as $track) {
            $chart->tracks()->updateExistingPivot($track->id, ['position' => $position]);
            $position++;
        }
    }

    public function updateTrackOrder(Request $request, Chart $chart)
    {
        $validated = $request->validate([
            'tracks' => 'required|array',
            'tracks.*' => 'exists:tracks,id',
        ]);

        foreach ($validated['tracks'] as $position => $trackId) {
            $chart->tracks()->updateExistingPivot($trackId, ['position' => $position + 1]);
        }

        $chart->update(['last_updated_at' => now()]);

        return response()->json(['success' => true]);
    }
}
