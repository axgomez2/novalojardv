<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaffleNumber extends Model
{
    protected $fillable = [
        'client_user_id',
        'number',
        'purchase_description',
        'order_number',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'created_by',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'client_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    public function getFormattedNumberAttribute(): string
    {
        return str_pad($this->number, 6, '0', STR_PAD_LEFT);
    }
}
