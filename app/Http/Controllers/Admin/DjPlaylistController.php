<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Models\DjPlaylist;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DjPlaylistController extends Controller
{
    public function index()
    {
        $playlists = DjPlaylist::with('tracks')
            ->orderBy('sort_order')
            ->orderBy('dj_name')
            ->paginate(15);

        return view('admin.dj-playlists.index', compact('playlists'));
    }

    public function create()
    {
        // Pré-selecionar cliente se passado via query string
        $selectedClient = null;
        if (request('client_id')) {
            $selectedClient = ClientUser::where('is_dj', true)->find(request('client_id'));
        }

        // Buscar DJs disponíveis (sem playlist vinculada)
        $availableDjs = ClientUser::where('is_dj', true)
            ->doesntHave('djPlaylist')
            ->orderBy('name')
            ->get();

        return view('admin.dj-playlists.create', compact('availableDjs', 'selectedClient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_user_id' => 'nullable|exists:client_users,id',
            'title' => 'required|string|max:255',
            'dj_name' => 'required|string|max:255',
            'dj_description' => 'nullable|string',
            'dj_image' => 'nullable|image|max:2048',
            'instagram' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'soundcloud' => 'nullable|url|max:255',
            'spotify' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['slug'] = Str::slug($validated['dj_name'] . '-' . $validated['title']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('dj_image')) {
            $validated['dj_image'] = $request->file('dj_image')->store('dj-images', 'public');
        }

        $playlist = DjPlaylist::create($validated);

        return redirect()->route('admin.music.dj-playlists.edit', $playlist)
            ->with('success', 'Playlist de DJ criada com sucesso! Agora adicione as faixas.');
    }

    public function show(DjPlaylist $djPlaylist)
    {
        $djPlaylist->load(['tracks.vinylMaster.artist']);

        return view('admin.dj-playlists.show', compact('djPlaylist'));
    }

    public function edit(DjPlaylist $djPlaylist)
    {
        $djPlaylist->load(['tracks.vinylMaster.artist']);

        return view('admin.dj-playlists.edit', compact('djPlaylist'));
    }

    public function update(Request $request, DjPlaylist $djPlaylist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'dj_name' => 'required|string|max:255',
            'dj_description' => 'nullable|string',
            'dj_image' => 'nullable|image|max:2048',
            'instagram' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'soundcloud' => 'nullable|url|max:255',
            'spotify' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('dj_image')) {
            if ($djPlaylist->dj_image) {
                Storage::disk('public')->delete($djPlaylist->dj_image);
            }
            $validated['dj_image'] = $request->file('dj_image')->store('dj-images', 'public');
        }

        $djPlaylist->update($validated);

        return redirect()->route('admin.music.dj-playlists.index')
            ->with('success', 'Playlist de DJ atualizada com sucesso!');
    }

    public function destroy(DjPlaylist $djPlaylist)
    {
        if ($djPlaylist->dj_image) {
            Storage::disk('public')->delete($djPlaylist->dj_image);
        }

        $djPlaylist->tracks()->detach();
        $djPlaylist->delete();

        return redirect()->route('admin.music.dj-playlists.index')
            ->with('success', 'Playlist de DJ excluída com sucesso!');
    }

    public function searchTracks(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $tracks = Track::with(['vinylMaster.mainArtists'])
                ->where('name', 'like', "%{$query}%")
                ->orWhereHas('vinylMaster', function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%");
                })
                ->orWhereHas('vinylMaster.mainArtists', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get()
                ->map(function ($track) {
                    return [
                        'id' => $track->id,
                        'name' => $track->name,
                        'position' => $track->position,
                        'duration' => $track->duration,
                        'vinyl_title' => $track->vinylMaster?->title ?? 'N/A',
                        'artist' => $track->vinylMaster?->artist_names ?? 'N/A',
                        'cover_url' => $track->vinylMaster?->cover_url,
                    ];
                });

            return response()->json($tracks);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addTrack(Request $request, DjPlaylist $djPlaylist)
    {
        $validated = $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        if ($djPlaylist->tracks()->count() >= 10) {
            return back()->with('error', 'Esta playlist já atingiu o limite de 10 faixas.');
        }

        if ($djPlaylist->tracks()->where('track_id', $validated['track_id'])->exists()) {
            return back()->with('error', 'Esta faixa já está na playlist.');
        }

        $nextPosition = $djPlaylist->tracks()->max('dj_playlist_tracks.position') + 1;

        $djPlaylist->tracks()->attach($validated['track_id'], ['position' => $nextPosition]);
        $djPlaylist->update(['last_updated_at' => now()]);

        return back()->with('success', 'Faixa adicionada à playlist!');
    }

    public function removeTrack(DjPlaylist $djPlaylist, Track $track)
    {
        $djPlaylist->tracks()->detach($track->id);
        $djPlaylist->update(['last_updated_at' => now()]);

        $this->reorderTracks($djPlaylist);

        return back()->with('success', 'Faixa removida da playlist!');
    }

    public function reorderTracks(DjPlaylist $djPlaylist)
    {
        $position = 1;
        foreach ($djPlaylist->tracks()->orderByPivot('position')->get() as $track) {
            $djPlaylist->tracks()->updateExistingPivot($track->id, ['position' => $position]);
            $position++;
        }
    }

    public function updateTrackOrder(Request $request, DjPlaylist $djPlaylist)
    {
        $validated = $request->validate([
            'tracks' => 'required|array',
            'tracks.*' => 'exists:tracks,id',
        ]);

        foreach ($validated['tracks'] as $position => $trackId) {
            $djPlaylist->tracks()->updateExistingPivot($trackId, ['position' => $position + 1]);
        }

        $djPlaylist->update(['last_updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function removeImage(DjPlaylist $djPlaylist)
    {
        if ($djPlaylist->dj_image) {
            Storage::disk('public')->delete($djPlaylist->dj_image);
            $djPlaylist->update(['dj_image' => null]);
        }

        return back()->with('success', 'Imagem removida com sucesso!');
    }
}
