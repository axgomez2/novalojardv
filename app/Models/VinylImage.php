<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VinylImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'vinyl_master_id',
        'type',
        'url',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'is_primary',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function vinylMaster(): BelongsTo
    {
        return $this->belongsTo(VinylMaster::class);
    }

    // Accessors
    public function getFullUrlAttribute(): string
    {
        if ($this->type === 'discogs') {
            return $this->url;
        }

        return $this->path ? Storage::url($this->path) : $this->url;
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLocal($query)
    {
        return $query->where('type', 'local');
    }

    public function scopeDiscogs($query)
    {
        return $query->where('type', 'discogs');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_primary', 'desc')->orderBy('sort_order');
    }
}
