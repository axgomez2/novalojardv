<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'vinyl_stock_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function vinylStock(): BelongsTo
    {
        return $this->belongsTo(VinylStock::class);
    }

    // Accessors

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_price, 2, ',', '.');
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }

    // Boot

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->total_price) {
                $item->total_price = $item->quantity * $item->unit_price;
            }
        });
    }
}
