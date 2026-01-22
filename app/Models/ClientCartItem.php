<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'vinyl_stock_id',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    // Relationships

    public function cart(): BelongsTo
    {
        return $this->belongsTo(ClientCart::class, 'cart_id');
    }

    public function vinylStock(): BelongsTo
    {
        return $this->belongsTo(VinylStock::class);
    }

    // Accessors

    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_price, 2, ',', '.');
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->vinylStock 
            && $this->vinylStock->stock >= $this->quantity
            && $this->vinylStock->availability !== 'unavailable';
    }

    public function getMaxQuantityAttribute(): int
    {
        return $this->vinylStock ? $this->vinylStock->stock : 0;
    }
}
