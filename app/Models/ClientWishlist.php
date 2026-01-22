<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientWishlist extends Model
{
    protected $fillable = [
        'client_user_id',
        'vinyl_stock_id',
        'notes',
    ];

    // Relationships

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    public function vinylStock(): BelongsTo
    {
        return $this->belongsTo(VinylStock::class);
    }

    // Scopes

    public function scopeAvailable($query)
    {
        return $query->whereHas('vinylStock', function ($q) {
            $q->where('stock', '>', 0)
              ->where('availability', '!=', 'unavailable');
        });
    }
}
