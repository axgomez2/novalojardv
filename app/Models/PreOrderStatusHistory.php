<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrderStatusHistory extends Model
{
    protected $fillable = [
        'pre_order_id',
        'from_status',
        'to_status',
        'admin_user_id',
        'triggered_by',
        'note',
    ];

    public function preOrder(): BelongsTo
    {
        return $this->belongsTo(PreOrder::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_user_id');
    }
}
