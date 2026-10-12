<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaffleAcceptance extends Model
{
    protected $fillable = [
        'client_user_id',
        'accepted',
        'accepted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'accepted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'client_user_id');
    }
}
