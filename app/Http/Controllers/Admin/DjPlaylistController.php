<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('admin.dj-playlists.create');
    }

    public function store(Request $request)
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

        $validated['slug'] = Str::slug($validated['dj_name'] . '-' . $validated['title']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('dj_image')) {
            $validated['dj_image'] = $request->file('dj_image')->store('dj-images', 'public');
        }

        $playlist = DjPlaylist::create($validated);

        return redirect()->route('admin.dj-playlists.edit', $playlist)
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

        return redirect()->route('admin.dj-playlists.index')
            ->with('success', 'Playlist de DJ atualizada com sucesso!');
    }

    public function destroy(DjPlaylist $djPlaylist)
    {
        if ($djPlaylist->dj_image) {
            Storage::disk('public')->delete($djPlaylist->dj_image);
        }

        $djPlaylist->tracks()->detach();
        $djPlaylist->delete();

        return redirect()->route('admin.dj-playlists.index')
            ->with('success', 'Playlist de DJ excluída com sucesso!');
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

        $nextPosition = $djPlaylist->tracks()->max('position') + 1;

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
