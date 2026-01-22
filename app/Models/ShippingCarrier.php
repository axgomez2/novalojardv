<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCarrier extends Model
{
    protected $fillable = [
        'melhor_envio_id',
        'name',
        'company',
        'logo',
        'is_active',
        'additional_cost',
        'additional_percentage',
        'additional_days',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_cost' => 'decimal:2',
        'additional_percentage' => 'decimal:2',
        'additional_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function calculateFinalCost(float $baseCost): float
    {
        $cost = $baseCost + $this->additional_cost;
        $cost += $cost * ($this->additional_percentage / 100);
        return round($cost, 2);
    }

    public function calculateFinalDays(int $baseDays): int
    {
        return $baseDays + $this->additional_days;
    }
}
