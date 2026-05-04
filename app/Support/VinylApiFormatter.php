<?php

namespace App\Support;

use App\Models\VinylStock;

/**
 * Formata um VinylStock para o payload usado pela API pública.
 *
 * Esta classe centraliza a serialização de discos para o frontend (Vue SPA),
 * substituindo a antiga função global `formatVinylForApi()` que estava em
 * `routes/api.php` e causava o erro "Cannot redeclare function" nos testes.
 */
class VinylApiFormatter
{
    /**
     * Formato compacto (listagens, vitrines).
     */
    public static function format(VinylStock $stock): array
    {
        return self::build($stock, false);
    }

    /**
     * Formato completo (página de detalhe).
     */
    public static function detailed(VinylStock $stock): array
    {
        return self::build($stock, true);
    }

    private static function build(VinylStock $stock, bool $detailed): array
    {
        $master = $stock->vinylMaster;
        $isPreorder = $stock->availability === 'preorder';
        $inStock = $stock->stock > 0;

        $tracks = $master?->tracks?->map(function ($track) {
            $audioUrl = null;
            $audioSource = null;

            if ($track->audio_path) {
                $audioUrl = asset('storage/' . $track->audio_path);
                $audioSource = 'local';
            } elseif ($track->youtube_url) {
                $audioUrl = $track->youtube_url;
                $audioSource = 'youtube';
            }

            return [
                'id' => $track->id,
                'position' => $track->position,
                'name' => $track->name,
                'duration' => $track->duration,
                'duration_seconds' => $track->duration_seconds,
                'audio_url' => $audioUrl,
                'audio_source' => $audioSource,
                'has_audio' => $audioUrl !== null,
            ];
        }) ?? collect();

        $data = [
            'id' => $stock->id,
            'title' => $master?->title ?? 'Sem Título',
            'slug' => $master?->slug,
            'artist' => $master?->artist_names ?? 'Artista Desconhecido',
            'record_label' => $master?->recordLabel?->name,
            'release_year' => $master?->release_year,
            'cover_image' => $master?->cover_url ?? '/images/vinyl-placeholder.jpg',
            'price' => $stock->current_price,
            'formatted_price' => $stock->formatted_current_price,
            'original_price' => $stock->is_promotional ? $stock->sell_price : null,
            'formatted_original_price' => $stock->is_promotional ? $stock->formatted_sell_price : null,
            'is_promotional' => $stock->isOnPromotion(),
            'is_new' => $stock->is_new,
            'is_preorder' => $isPreorder,
            'release_date' => $stock->release_date?->format('Y-m-d'),
            'formatted_release_date' => $stock->release_date?->format('d/m/Y'),
            'condition' => $stock->condition_label,
            'format' => $stock->format,
            'stock' => $stock->stock,
            'in_stock' => $inStock,
            'availability' => $stock->availability,
            'store_section' => $stock->store_section,
            'product_type' => $stock->productType ? [
                'id' => $stock->productType->id,
                'name' => $stock->productType->name,
                'slug' => $stock->productType->slug,
            ] : null,
            'can_buy' => $inStock || $isPreorder,
            'show_wishlist' => $inStock && !$isPreorder,
            'show_wantlist' => !$inStock || $isPreorder,
            'tracks' => $tracks->values()->toArray(),
            'tracks_count' => $tracks->count(),
            'has_playable_tracks' => $tracks->where('has_audio', true)->count() > 0,
        ];

        if ($detailed) {
            $data['description'] = $master?->description;
            $data['genres'] = $master?->genres ?? [];
            $data['styles'] = $master?->styles ?? [];
            $data['country'] = $master?->country;
            $data['catalog_number'] = $stock->catalog_number;
            $data['barcode'] = $stock->barcode;
            $data['color'] = $stock->color;
            $data['edition'] = $stock->edition;
            $data['num_discs'] = $stock->num_discs;
            $data['speed'] = $stock->speed;
            $data['media_status'] = $stock->mediaStatus?->name;
            $data['cover_status'] = $stock->coverStatus?->name;
            $data['notes'] = $stock->notes;
            $data['vinyl_master_id'] = $master?->id;
            $data['images'] = $master?->vinylImages?->map(fn ($img) => [
                'url' => $img->url,
                'is_primary' => $img->is_primary,
            ]) ?? [];

            $primaryCategory = $stock->categories()->wherePivot('is_primary', true)->first();
            $data['category'] = $primaryCategory ? [
                'id' => $primaryCategory->id,
                'name' => $primaryCategory->name,
                'slug' => $primaryCategory->slug,
                'parent_id' => $primaryCategory->parent_id,
                'parent' => $primaryCategory->parent ? [
                    'id' => $primaryCategory->parent->id,
                    'name' => $primaryCategory->parent->name,
                    'slug' => $primaryCategory->parent->slug,
                ] : null,
            ] : null;
        }

        return $data;
    }
}
