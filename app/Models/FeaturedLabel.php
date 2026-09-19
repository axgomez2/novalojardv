<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedLabel extends Model
{
    protected $fillable = [
        'record_label_id',
        'custom_logo',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function recordLabel(): BelongsTo
    {
        return $this->belongsTo(RecordLabel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Retorna a URL do logo (custom ou do record_label)
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->custom_logo) {
            return asset('storage/' . $this->custom_logo);
        }

        return $this->recordLabel?->logo;
    }
}
