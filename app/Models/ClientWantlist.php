<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientWantlist extends Model
{
    protected $fillable = [
        'client_user_id',
        'vinyl_master_id',
        'artist_name',
        'album_name',
        'release_year',
        'description',
        'priority',
        'max_price',
        'notify_when_available',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'max_price' => 'decimal:2',
            'notify_when_available' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    // Relationships

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    public function vinylMaster(): BelongsTo
    {
        return $this->belongsTo(VinylMaster::class);
    }

    // Accessors

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            default => 'Média',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'medium' => 'yellow',
            'high' => 'red',
            default => 'gray',
        };
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->vinylMaster) {
            return $this->vinylMaster->full_title;
        }

        $parts = [];
        if ($this->artist_name) {
            $parts[] = $this->artist_name;
        }
        if ($this->album_name) {
            $parts[] = $this->album_name;
        }
        if ($this->release_year) {
            $parts[] = "({$this->release_year})";
        }

        return implode(' - ', $parts) ?: 'Item sem nome';
    }

    // Scopes

    public function scopePendingNotification($query)
    {
        return $query->where('notify_when_available', true)
                     ->whereNull('notified_at');
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    // Methods

    public function markAsNotified(): void
    {
        $this->update(['notified_at' => now()]);
    }

    public function checkAvailability(): bool
    {
        if (!$this->vinyl_master_id) {
            return false;
        }

        return VinylStock::where('vinyl_master_id', $this->vinyl_master_id)
            ->where('stock', '>', 0)
            ->where('availability', '!=', 'unavailable')
            ->when($this->max_price, function ($query) {
                $query->where('sell_price', '<=', $this->max_price);
            })
            ->exists();
    }
}
