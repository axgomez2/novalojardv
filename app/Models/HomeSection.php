<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'title',
        'subtitle',
        'type',
        'max_items',
        'sort_order',
        'is_active',
        'view_all_link',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_items' => 'integer',
        'sort_order' => 'integer',
    ];

    public const TYPES = [
        'discos_novos' => 'Discos Novos',
        'pre_venda' => 'Pré-Venda',
        'discos_usados' => 'Discos Usados',
        'discos_nacionais' => 'Discos Nacionais',
        'ofertas' => 'Ofertas',
        'destaques' => 'Destaques',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('position');
    }

    public function vinyls(): BelongsToMany
    {
        return $this->belongsToMany(VinylStock::class, 'home_section_items')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
