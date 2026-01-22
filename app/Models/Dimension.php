<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dimension extends Model
{
    protected $fillable = [
        'name',
        'height',
        'width',
        'depth',
        'unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'height' => 'decimal:2',
            'width' => 'decimal:2',
            'depth' => 'decimal:2',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedDimensionsAttribute(): string
    {
        return "{$this->height} x {$this->width} x {$this->depth} {$this->unit}";
    }
}
