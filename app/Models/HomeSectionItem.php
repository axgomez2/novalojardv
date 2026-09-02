<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeSectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_section_id',
        'vinyl_stock_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class, 'home_section_id');
    }

    public function vinyl(): BelongsTo
    {
        return $this->belongsTo(VinylStock::class, 'vinyl_stock_id');
    }
}
