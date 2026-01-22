<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class DjPlaylist extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'dj_name',
        'dj_description',
        'dj_image',
        'instagram',
        'facebook',
        'twitter',
        'soundcloud',
        'spotify',
        'youtube',
        'website',
        'is_active',
        'is_featured',
        'sort_order',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'last_updated_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($playlist) {
            if (empty($playlist->slug)) {
                $playlist->slug = Str::slug($playlist->dj_name . '-' . $playlist->title);
            }
        });
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'dj_playlist_tracks')
            ->withPivot('position')
            ->orderByPivot('position')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getDjImageUrlAttribute(): ?string
    {
        if (!$this->dj_image) {
            return null;
        }

        return asset('storage/' . $this->dj_image);
    }

    public function getSocialLinks(): array
    {
        $links = [];

        if ($this->instagram) {
            $links['instagram'] = [
                'url' => $this->instagram,
                'icon' => 'fab fa-instagram',
                'label' => 'Instagram',
            ];
        }

        if ($this->facebook) {
            $links['facebook'] = [
                'url' => $this->facebook,
                'icon' => 'fab fa-facebook',
                'label' => 'Facebook',
            ];
        }

        if ($this->twitter) {
            $links['twitter'] = [
                'url' => $this->twitter,
                'icon' => 'fab fa-twitter',
                'label' => 'Twitter/X',
            ];
        }

        if ($this->soundcloud) {
            $links['soundcloud'] = [
                'url' => $this->soundcloud,
                'icon' => 'fab fa-soundcloud',
                'label' => 'SoundCloud',
            ];
        }

        if ($this->spotify) {
            $links['spotify'] = [
                'url' => $this->spotify,
                'icon' => 'fab fa-spotify',
                'label' => 'Spotify',
            ];
        }

        if ($this->youtube) {
            $links['youtube'] = [
                'url' => $this->youtube,
                'icon' => 'fab fa-youtube',
                'label' => 'YouTube',
            ];
        }

        if ($this->website) {
            $links['website'] = [
                'url' => $this->website,
                'icon' => 'fas fa-globe',
                'label' => 'Website',
            ];
        }

        return $links;
    }
}
