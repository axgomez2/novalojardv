<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Track;
use App\Models\VinylMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VinylTrackController extends Controller
{
    public function index(VinylMaster $vinyl): View
    {
        $vinyl->load(['tracks' => fn($q) => $q->orderBy('sort_order')->orderBy('position')]);

        return view('admin.vinyls.tracks.index', compact('vinyl'));
    }

    public function store(Request $request, VinylMaster $vinyl): RedirectResponse
    {
        $validated = $request->validate([
            'position' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'duration' => 'nullable|string|max:10',
            'youtube_url' => 'nullable|url|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480', // 20MB max
        ]);

        $track = new Track([
            'vinyl_master_id' => $vinyl->id,
            'position' => $validated['position'] ?? null,
            'name' => $validated['name'],
            'duration' => $validated['duration'] ?? null,
            'duration_seconds' => Track::durationToSeconds($validated['duration'] ?? null),
            'youtube_url' => $validated['youtube_url'] ?? null,
            'sort_order' => $vinyl->tracks()->max('sort_order') + 1,
        ]);

        // Handle audio upload
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $path = $file->store('tracks/' . $vinyl->id, 'public');
            $track->audio_path = $path;
            $track->audio_original_name = $file->getClientOriginalName();
        }

        $track->save();

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Faixa '{$track->name}' adicionada ao disco '{$vinyl->title}'",
            $track
        );

        return redirect()->route('admin.vinyls.tracks.index', $vinyl)
            ->with('success', 'Faixa adicionada com sucesso.');
    }

    public function update(Request $request, VinylMaster $vinyl, Track $track): RedirectResponse
    {
        $validated = $request->validate([
            'position' => 'nullable|string|max:10',
            'name' => 'required|string|max:255',
            'duration' => 'nullable|string|max:10',
            'youtube_url' => 'nullable|url|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480',
        ]);

        $track->fill([
            'position' => $validated['position'] ?? null,
            'name' => $validated['name'],
            'duration' => $validated['duration'] ?? null,
            'duration_seconds' => Track::durationToSeconds($validated['duration'] ?? null),
            'youtube_url' => $validated['youtube_url'] ?? null,
        ]);

        // Handle audio upload
        if ($request->hasFile('audio')) {
            // Delete old audio if exists
            if ($track->audio_path) {
                Storage::disk('public')->delete($track->audio_path);
            }

            $file = $request->file('audio');
            $path = $file->store('tracks/' . $vinyl->id, 'public');
            $track->audio_path = $path;
            $track->audio_original_name = $file->getClientOriginalName();
        }

        $track->save();

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Faixa '{$track->name}' atualizada no disco '{$vinyl->title}'",
            $track
        );

        return redirect()->route('admin.vinyls.tracks.index', $vinyl)
            ->with('success', 'Faixa atualizada com sucesso.');
    }

    public function destroy(VinylMaster $vinyl, Track $track): RedirectResponse
    {
        // Delete audio file if exists
        if ($track->audio_path) {
            Storage::disk('public')->delete($track->audio_path);
        }

        $trackName = $track->name;
        $track->delete();

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Faixa '{$trackName}' excluída do disco '{$vinyl->title}'"
        );

        return redirect()->route('admin.vinyls.tracks.index', $vinyl)
            ->with('success', 'Faixa excluída com sucesso.');
    }

    public function deleteAudio(VinylMaster $vinyl, Track $track): RedirectResponse
    {
        if ($track->audio_path) {
            Storage::disk('public')->delete($track->audio_path);
            $track->update([
                'audio_path' => null,
                'audio_original_name' => null,
            ]);
        }

        return redirect()->route('admin.vinyls.tracks.index', $vinyl)
            ->with('success', 'Áudio removido com sucesso.');
    }

    public function updateOrder(Request $request, VinylMaster $vinyl): RedirectResponse
    {
        $validated = $request->validate([
            'tracks' => 'required|array',
            'tracks.*' => 'integer|exists:tracks,id',
        ]);

        foreach ($validated['tracks'] as $order => $trackId) {
            Track::where('id', $trackId)
                ->where('vinyl_master_id', $vinyl->id)
                ->update(['sort_order' => $order]);
        }

        return redirect()->route('admin.vinyls.tracks.index', $vinyl)
            ->with('success', 'Ordem das faixas atualizada.');
    }
}
