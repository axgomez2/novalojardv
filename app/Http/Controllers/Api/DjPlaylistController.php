<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DjPlaylist;
use App\Models\Track;
use Illuminate\Http\Request;

class DjPlaylistController extends Controller
{
    /**
     * Obter a playlist do DJ autenticado
     */
    public function myPlaylist(Request $request)
    {
        $user = $request->user();

        if (!$user->is_dj) {
            return response()->json([
                'message' => 'Você não tem permissão de DJ.'
            ], 403);
        }

        $playlist = $user->djPlaylist;

        if (!$playlist) {
            return response()->json([
                'message' => 'Você ainda não possui uma playlist vinculada. Entre em contato com o administrador.',
                'has_playlist' => false
            ], 404);
        }

        $playlist->load(['tracks.vinylMaster.mainArtists', 'tracks.vinylMaster.recordLabel']);

        return response()->json([
            'has_playlist' => true,
            'playlist' => [
                'id' => $playlist->id,
                'title' => $playlist->title,
                'dj_name' => $playlist->dj_name,
                'dj_description' => $playlist->dj_description,
                'dj_image' => $playlist->dj_image_url,
                'is_active' => $playlist->is_active,
                'tracks_count' => $playlist->tracks->count(),
                'max_tracks' => 10,
                'tracks' => $playlist->tracks->map(function ($track) {
                    return [
                        'id' => $track->id,
                        'position' => $track->pivot->position,
                        'name' => $track->name,
                        'duration' => $track->duration,
                        'vinyl' => [
                            'id' => $track->vinylMaster?->id,
                            'title' => $track->vinylMaster?->title,
                            'artist' => $track->vinylMaster?->artist_names,
                            'cover_image' => $track->vinylMaster?->cover_url,
                            'record_label' => $track->vinylMaster?->recordLabel?->name,
                        ]
                    ];
                }),
                'social_links' => $playlist->getSocialLinks(),
                'last_updated_at' => $playlist->last_updated_at?->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Buscar faixas disponíveis para adicionar à playlist
     */
    public function searchTracks(Request $request)
    {
        $user = $request->user();

        if (!$user->is_dj || !$user->djPlaylist) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json(['data' => []]);
        }

        $existingTrackIds = $user->djPlaylist->tracks()->pluck('tracks.id')->toArray();

        $tracks = Track::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel'])
            ->whereNotIn('id', $existingTrackIds)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('vinylMaster', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vinylMaster.mainArtists', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->limit(20)
            ->get()
            ->map(function ($track) {
                return [
                    'id' => $track->id,
                    'name' => $track->name,
                    'duration' => $track->duration,
                    'vinyl' => [
                        'id' => $track->vinylMaster?->id,
                        'title' => $track->vinylMaster?->title,
                        'artist' => $track->vinylMaster?->artist_names,
                        'cover_image' => $track->vinylMaster?->cover_url,
                        'record_label' => $track->vinylMaster?->recordLabel?->name,
                    ]
                ];
            });

        return response()->json(['data' => $tracks]);
    }

    /**
     * Adicionar faixa à playlist
     */
    public function addTrack(Request $request)
    {
        $user = $request->user();

        if (!$user->is_dj || !$user->djPlaylist) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        $playlist = $user->djPlaylist;

        if ($playlist->tracks()->count() >= 10) {
            return response()->json([
                'message' => 'Sua playlist já atingiu o limite de 10 faixas.'
            ], 422);
        }

        if ($playlist->tracks()->where('track_id', $validated['track_id'])->exists()) {
            return response()->json([
                'message' => 'Esta faixa já está na sua playlist.'
            ], 422);
        }

        $nextPosition = $playlist->tracks()->max('dj_playlist_tracks.position') + 1;

        $playlist->tracks()->attach($validated['track_id'], ['position' => $nextPosition]);
        $playlist->update(['last_updated_at' => now()]);

        return response()->json([
            'message' => 'Faixa adicionada com sucesso!',
            'tracks_count' => $playlist->tracks()->count()
        ]);
    }

    /**
     * Remover faixa da playlist
     */
    public function removeTrack(Request $request, Track $track)
    {
        $user = $request->user();

        if (!$user->is_dj || !$user->djPlaylist) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $playlist = $user->djPlaylist;

        if (!$playlist->tracks()->where('track_id', $track->id)->exists()) {
            return response()->json([
                'message' => 'Esta faixa não está na sua playlist.'
            ], 404);
        }

        $playlist->tracks()->detach($track->id);
        $playlist->update(['last_updated_at' => now()]);

        // Reordenar posições
        $this->normalizeTrackPositions($playlist);

        return response()->json([
            'message' => 'Faixa removida com sucesso!',
            'tracks_count' => $playlist->tracks()->count()
        ]);
    }

    /**
     * Reordenar faixas da playlist
     */
    public function reorderTracks(Request $request)
    {
        $user = $request->user();

        if (!$user->is_dj || !$user->djPlaylist) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'tracks' => 'required|array',
            'tracks.*' => 'exists:tracks,id',
        ]);

        $playlist = $user->djPlaylist;

        foreach ($validated['tracks'] as $position => $trackId) {
            $playlist->tracks()->updateExistingPivot($trackId, ['position' => $position + 1]);
        }

        $playlist->update(['last_updated_at' => now()]);

        return response()->json(['message' => 'Ordem atualizada com sucesso!']);
    }

    /**
     * Helper para normalizar posições das faixas após remoção
     */
    private function normalizeTrackPositions(DjPlaylist $playlist): void
    {
        $position = 1;
        foreach ($playlist->tracks()->orderByPivot('position')->get() as $track) {
            $playlist->tracks()->updateExistingPivot($track->id, ['position' => $position]);
            $position++;
        }
    }
}
