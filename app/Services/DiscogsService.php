<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiscogsService
{
    protected string $baseUrl = 'https://api.discogs.com';
    protected ?string $token;
    protected string $userAgent;

    public function __construct()
    {
        $this->token = config('services.discogs.token');
        $this->userAgent = config('services.discogs.user_agent', 'VinilStore/1.0');
    }

    /**
     * Check if Discogs is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Search for releases on Discogs
     */
    public function search(string $query, string $type = 'release', int $perPage = 20, int $page = 1): array
    {
        $cacheKey = "discogs_search_{$type}_" . md5($query) . "_{$page}_{$perPage}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query, $type, $perPage, $page) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->get("{$this->baseUrl}/database/search", [
                        'q' => $query,
                        'type' => $type,
                        'per_page' => $perPage,
                        'page' => $page,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Discogs search failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['results' => [], 'pagination' => []];
            } catch (\Exception $e) {
                Log::error('Discogs search exception', ['message' => $e->getMessage()]);
                return ['results' => [], 'pagination' => []];
            }
        });
    }

    /**
     * Get release details by ID
     */
    public function getRelease(int $releaseId): ?array
    {
        $cacheKey = "discogs_release_{$releaseId}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($releaseId) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->get("{$this->baseUrl}/releases/{$releaseId}");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Discogs get release failed', [
                    'release_id' => $releaseId,
                    'status' => $response->status(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Discogs get release exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Get master release details by ID
     */
    public function getMaster(int $masterId): ?array
    {
        $cacheKey = "discogs_master_{$masterId}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($masterId) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->get("{$this->baseUrl}/masters/{$masterId}");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Discogs get master failed', [
                    'master_id' => $masterId,
                    'status' => $response->status(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Discogs get master exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Get artist details by ID
     */
    public function getArtist(int $artistId): ?array
    {
        $cacheKey = "discogs_artist_{$artistId}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($artistId) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->get("{$this->baseUrl}/artists/{$artistId}");

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Discogs get artist exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Get label details by ID
     */
    public function getLabel(int $labelId): ?array
    {
        $cacheKey = "discogs_label_{$labelId}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($labelId) {
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->get("{$this->baseUrl}/labels/{$labelId}");

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Discogs get label exception', ['message' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Parse release data to our format
     */
    public function parseReleaseData(array $release): array
    {
        return [
            'discogs_release_id' => $release['id'] ?? null,
            'discogs_master_id' => $release['master_id'] ?? null,
            'title' => $release['title'] ?? '',
            'artists' => $this->parseArtists($release['artists'] ?? []),
            'year' => $release['year'] ?? null,
            'country' => $release['country'] ?? null,
            'genres' => $release['genres'] ?? [],
            'styles' => $release['styles'] ?? [],
            'tracklist' => $this->parseTracklist($release['tracklist'] ?? []),
            'labels' => $this->parseLabels($release['labels'] ?? []),
            'images' => $this->parseImages($release['images'] ?? []),
            'cover_image' => $this->getCoverImage($release['images'] ?? []),
            'discogs_url' => $release['uri'] ?? null,
            'notes' => $release['notes'] ?? null,
        ];
    }

    /**
     * Parse artists from Discogs format
     */
    protected function parseArtists(array $artists): array
    {
        return collect($artists)->map(function ($artist) {
            return [
                'discogs_id' => (string) ($artist['id'] ?? ''),
                'name' => $artist['name'] ?? '',
                'role' => $artist['role'] ?? 'main',
                'discogs_url' => $artist['resource_url'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Parse tracklist from Discogs format
     */
    protected function parseTracklist(array $tracklist): array
    {
        return collect($tracklist)
            ->filter(fn($track) => ($track['type_'] ?? '') === 'track')
            ->values()
            ->map(function ($track, $index) {
                return [
                    'position' => $track['position'] ?? '',
                    'name' => $track['title'] ?? '',
                    'duration' => $track['duration'] ?? null,
                    'sort_order' => $index,
                ];
            })->toArray();
    }

    /**
     * Parse labels from Discogs format
     */
    protected function parseLabels(array $labels): array
    {
        return collect($labels)->map(function ($label) {
            return [
                'discogs_id' => (string) ($label['id'] ?? ''),
                'name' => $label['name'] ?? '',
                'catno' => $label['catno'] ?? null,
                'discogs_url' => $label['resource_url'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Parse images from Discogs format
     */
    protected function parseImages(array $images): array
    {
        return collect($images)->map(function ($image) {
            return [
                'type' => $image['type'] ?? 'secondary',
                'uri' => $image['uri'] ?? $image['resource_url'] ?? '',
                'uri150' => $image['uri150'] ?? '',
                'width' => $image['width'] ?? 0,
                'height' => $image['height'] ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get the primary cover image
     */
    protected function getCoverImage(array $images): ?string
    {
        $primary = collect($images)->firstWhere('type', 'primary');
        if ($primary) {
            return $primary['uri'] ?? $primary['resource_url'] ?? null;
        }

        $first = collect($images)->first();
        return $first['uri'] ?? $first['resource_url'] ?? null;
    }

    /**
     * Get headers for API requests
     */
    protected function getHeaders(): array
    {
        return [
            'User-Agent' => $this->userAgent,
            'Authorization' => "Discogs token={$this->token}",
        ];
    }

    /**
     * Clear cache for a specific release
     */
    public function clearReleaseCache(int $releaseId): void
    {
        Cache::forget("discogs_release_{$releaseId}");
    }

    /**
     * Clear all Discogs cache
     */
    public function clearAllCache(): void
    {
        // This would require cache tagging which depends on the cache driver
        // For now, we'll just log that it was called
        Log::info('Discogs cache clear requested');
    }
}
