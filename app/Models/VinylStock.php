<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VinylStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vinyl_master_id',
        'product_type_id',
        'catalog_number',
        'barcode',
        'internal_code',
        'format',
        'num_discs',
        'speed',
        'color',
        'edition',
        'is_new',
        'media_status_id',
        'cover_status_id',
        'weight_id',
        'dimension_id',
        'supplier_id',
        'stock',
        'stock_min',
        'cost_price',
        'sell_price',
        'promotional_price',
        'is_promotional',
        'promo_starts_at',
        'promo_ends_at',
        'availability',
        'store_section',
        'release_date',
        'notes',
        'description',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'is_promotional' => 'boolean',
        'stock' => 'integer',
        'stock_min' => 'integer',
        'num_discs' => 'integer',
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'promotional_price' => 'decimal:2',
        'promo_starts_at' => 'datetime',
        'promo_ends_at' => 'datetime',
        'release_date' => 'date',
    ];

    // Relationships
    public function vinylMaster(): BelongsTo
    {
        return $this->belongsTo(VinylMaster::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function mediaStatus(): BelongsTo
    {
        return $this->belongsTo(MediaStatus::class);
    }

    public function coverStatus(): BelongsTo
    {
        return $this->belongsTo(CoverStatus::class);
    }

    public function weight(): BelongsTo
    {
        return $this->belongsTo(Weight::class);
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_vinyl_stock')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('availability', 'available')->where('stock', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('availability', 'featured');
    }

    public function scopePreorder($query)
    {
        return $query->where('availability', 'preorder');
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeUsed($query)
    {
        return $query->where('is_new', false);
    }

    public function scopeDjSection($query)
    {
        return $query->where('store_section', 'dj');
    }

    public function scopeAlbumsSection($query)
    {
        return $query->where('store_section', 'albums');
    }

    /**
     * Filtra stocks por slug do ProductType.
     * Ex.: VinylStock::ofProductType('discos-novos')->get()
     */
    public function scopeOfProductType($query, string $slug)
    {
        return $query->whereHas('productType', fn ($q) => $q->where('slug', $slug));
    }

    public function scopeOnPromotion($query)
    {
        return $query->where('is_promotional', true)
            ->where(function ($q) {
                $q->whereNull('promo_starts_at')
                    ->orWhere('promo_starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('promo_ends_at')
                    ->orWhere('promo_ends_at', '>=', now());
            });
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_min');
    }

    // Accessors
    public function getCurrentPriceAttribute(): float
    {
        if ($this->isOnPromotion()) {
            return (float) $this->promotional_price;
        }
        return (float) $this->sell_price;
    }

    public function getFormattedSellPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->sell_price, 2, ',', '.');
    }

    public function getFormattedCurrentPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->current_price, 2, ',', '.');
    }

    public function getFormattedCostPriceAttribute(): string
    {
        return $this->cost_price ? 'R$ ' . number_format($this->cost_price, 2, ',', '.') : '-';
    }

    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price == 0) {
            return null;
        }
        return (($this->sell_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getConditionLabelAttribute(): string
    {
        return $this->is_new ? 'Novo' : 'Usado';
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return match($this->availability) {
            'available' => 'Disponível',
            'unavailable' => 'Indisponível',
            'featured' => 'Destaque',
            'preorder' => 'Pré-venda',
            default => $this->availability,
        };
    }

    public function getAvailabilityColorAttribute(): string
    {
        return match($this->availability) {
            'available' => 'green',
            'unavailable' => 'red',
            'featured' => 'purple',
            'preorder' => 'blue',
            default => 'gray',
        };
    }

    public function getPrimaryCategoryAttribute(): ?Category
    {
        return $this->categories()->wherePivot('is_primary', true)->first();
    }

    // Helpers
    public function isOnPromotion(): bool
    {
        if (!$this->is_promotional || !$this->promotional_price) {
            return false;
        }

        $now = now();
        
        if ($this->promo_starts_at && $now->lt($this->promo_starts_at)) {
            return false;
        }
        
        if ($this->promo_ends_at && $now->gt($this->promo_ends_at)) {
            return false;
        }

        return true;
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_min;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    // Stock management
    public function addStock(int $quantity, float $unitPrice = null, string $reference = null, string $notes = null): StockMovement
    {
        $stockBefore = $this->stock;
        $this->increment('stock', $quantity);

        return $this->stockMovements()->create([
            'user_id' => auth()->id(),
            'type' => 'purchase',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $this->stock,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice ? $unitPrice * $quantity : null,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }

    public function removeStock(int $quantity, string $type = 'sale', float $unitPrice = null, string $reference = null, string $notes = null): StockMovement
    {
        $stockBefore = $this->stock;
        $this->decrement('stock', $quantity);

        return $this->stockMovements()->create([
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => -$quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $this->stock,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice ? $unitPrice * $quantity : null,
            'reference' => $reference,
            'notes' => $notes,
        ]);
    }

    public function adjustStock(int $newQuantity, string $notes = null): StockMovement
    {
        $stockBefore = $this->stock;
        $difference = $newQuantity - $stockBefore;
        $this->update(['stock' => $newQuantity]);

        return $this->stockMovements()->create([
            'user_id' => auth()->id(),
            'type' => 'adjustment',
            'quantity' => $difference,
            'stock_before' => $stockBefore,
            'stock_after' => $newQuantity,
            'notes' => $notes,
        ]);
    }

    // Statistics
    public function getAverageCostPriceAttribute(): ?float
    {
        $purchases = $this->stockMovements()
            ->where('type', 'purchase')
            ->whereNotNull('unit_price')
            ->get();

        if ($purchases->isEmpty()) {
            return $this->cost_price;
        }

        $totalValue = $purchases->sum('total_price');
        $totalQuantity = $purchases->sum('quantity');

        return $totalQuantity > 0 ? $totalValue / $totalQuantity : null;
    }

    public function getTotalPurchaseValueAttribute(): float
    {
        return (float) $this->stockMovements()
            ->where('type', 'purchase')
            ->sum('total_price');
    }

    public function getTotalSalesValueAttribute(): float
    {
        return (float) $this->stockMovements()
            ->where('type', 'sale')
            ->sum('total_price');
    }

    public function getStockValueAttribute(): float
    {
        return $this->stock * ($this->cost_price ?? $this->sell_price);
    }
}
