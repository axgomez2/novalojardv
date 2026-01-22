<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'type',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PaymentCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function recurringPayments(): HasMany
    {
        return $this->hasMany(RecurringPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpense($query)
    {
        return $query->whereIn('type', ['expense', 'both']);
    }

    public function scopeIncome($query)
    {
        return $query->whereIn('type', ['income', 'both']);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'expense' => 'Despesa',
            'income' => 'Receita',
            'both' => 'Ambos',
            default => $this->type,
        };
    }
}
