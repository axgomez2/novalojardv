<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Track extends Model
{
    protected $fillable = [
        'vinyl_master_id',
        'position',
        'name',
        'duration',
        'duration_seconds',
        'youtube_url',
        'audio_path',
        'audio_original_name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function vinylMaster(): BelongsTo
    {
        return $this->belongsTo(VinylMaster::class);
    }

    /**
     * Convert duration string (3:45) to seconds
     */
    public static function durationToSeconds(?string $duration): ?int
    {
        if (!$duration) {
            return null;
        }

        $parts = explode(':', $duration);
        if (count($parts) === 2) {
            return (int)$parts[0] * 60 + (int)$parts[1];
        }
        if (count($parts) === 3) {
            return (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
        }

        return null;
    }

    /**
     * Convert seconds to duration string
     */
    public static function secondsToDuration(?int $seconds): ?string
    {
        if (!$seconds) {
            return null;
        }

        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $secs);
    }

    /**
     * Get the audio URL
     */
    public function getAudioUrlAttribute(): ?string
    {
        if (!$this->audio_path) {
            return null;
        }

        return asset('storage/' . $this->audio_path);
    }

    /**
     * Check if track has audio
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_path);
    }

    /**
     * Check if track has YouTube link
     */
    public function hasYoutube(): bool
    {
        return !empty($this->youtube_url);
    }

    /**
     * Get YouTube embed URL
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->youtube_url) {
            return null;
        }

        // Extract video ID from various YouTube URL formats
        $patterns = [
            '/youtube\.com\/watch\?v=([^&]+)/',
            '/youtu\.be\/([^?]+)/',
            '/youtube\.com\/embed\/([^?]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->youtube_url, $matches)) {
                return 'https://www.youtube.com/embed/' . $matches[1];
            }
        }

        return null;
    }
}
