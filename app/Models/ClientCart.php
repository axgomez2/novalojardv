<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientCart extends Model
{
    protected $fillable = [
        'client_user_id',
        'session_id',
    ];

    // Relationships

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClientCartItem::class, 'cart_id');
    }

    // Accessors

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function getTotalAttribute(): float
    {
        return $this->subtotal;
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    // Methods

    public function addItem(VinylStock $vinylStock, int $quantity = 1): ClientCartItem
    {
        $existingItem = $this->items()->where('vinyl_stock_id', $vinylStock->id)->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem->fresh();
        }

        return $this->items()->create([
            'vinyl_stock_id' => $vinylStock->id,
            'quantity' => $quantity,
            'unit_price' => $vinylStock->current_price,
        ]);
    }

    public function updateItemQuantity(VinylStock $vinylStock, int $quantity): ?ClientCartItem
    {
        $item = $this->items()->where('vinyl_stock_id', $vinylStock->id)->first();

        if (!$item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->update(['quantity' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(VinylStock $vinylStock): bool
    {
        return $this->items()->where('vinyl_stock_id', $vinylStock->id)->delete() > 0;
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function hasItem(VinylStock $vinylStock): bool
    {
        return $this->items()->where('vinyl_stock_id', $vinylStock->id)->exists();
    }

    public function getItemQuantity(VinylStock $vinylStock): int
    {
        return $this->items()->where('vinyl_stock_id', $vinylStock->id)->value('quantity') ?? 0;
    }

    public function refreshPrices(): void
    {
        foreach ($this->items as $item) {
            $item->update([
                'unit_price' => $item->vinylStock->current_price,
            ]);
        }
    }

    public function mergeWithSession(string $sessionId): void
    {
        $sessionCart = static::where('session_id', $sessionId)
            ->whereNull('client_user_id')
            ->first();

        if (!$sessionCart) {
            return;
        }

        foreach ($sessionCart->items as $item) {
            $this->addItem($item->vinylStock, $item->quantity);
        }

        $sessionCart->delete();
    }
}
