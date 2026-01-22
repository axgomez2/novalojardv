<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'vinyl_stock_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_price',
        'total_price',
        'reference',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function vinylStock(): BelongsTo
    {
        return $this->belongsTo(VinylStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'purchase' => 'Compra',
            'sale' => 'Venda',
            'adjustment' => 'Ajuste',
            'return' => 'Devolução',
            'loss' => 'Perda',
            'transfer' => 'Transferência',
            default => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'purchase' => 'green',
            'sale' => 'blue',
            'adjustment' => 'yellow',
            'return' => 'purple',
            'loss' => 'red',
            'transfer' => 'gray',
            default => 'gray',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'purchase' => 'arrow-down',
            'sale' => 'arrow-up',
            'adjustment' => 'adjustments',
            'return' => 'reply',
            'loss' => 'x-circle',
            'transfer' => 'switch-horizontal',
            default => 'document',
        };
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return $this->unit_price ? 'R$ ' . number_format($this->unit_price, 2, ',', '.') : '-';
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return $this->total_price ? 'R$ ' . number_format($this->total_price, 2, ',', '.') : '-';
    }

    public function isEntry(): bool
    {
        return $this->quantity > 0;
    }

    public function isExit(): bool
    {
        return $this->quantity < 0;
    }

    // Scopes
    public function scopeEntries($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeExits($query)
    {
        return $query->where('quantity', '<', 0);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
