<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Chart extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'category_id',
        'description',
        'is_active',
        'max_tracks',
        'sort_order',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_tracks' => 'integer',
            'sort_order' => 'integer',
            'last_updated_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chart) {
            if (empty($chart->slug)) {
                $chart->slug = Str::slug($chart->title);
            }
        });

        static::updating(function ($chart) {
            if ($chart->isDirty('title') && empty($chart->slug)) {
                $chart->slug = Str::slug($chart->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'chart_tracks')
            ->withPivot('position')
            ->orderByPivot('position')
            ->withTimestamps();
    }

    public function vinyls(): BelongsToMany
    {
        return $this->belongsToMany(VinylMaster::class, 'chart_vinyls')
            ->withPivot('position')
            ->orderByPivot('position')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'style' => 'Por Estilo',
            'bestsellers' => 'Mais Vendidos',
            'new_releases' => 'Lançamentos',
            'custom' => 'Personalizado',
            default => $this->type,
        };
    }

    public static function getTypes(): array
    {
        return [
            'style' => 'Por Estilo',
            'bestsellers' => 'Mais Vendidos',
            'new_releases' => 'Lançamentos',
            'custom' => 'Personalizado',
        ];
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return \Illuminate\Support\Facades\Storage::url($this->cover_image);
        }
        return null;
    }

    public function getPeriodAttribute(): ?string
    {
        return $this->attributes['period'] ?? $this->getTypeLabel();
    }
}
