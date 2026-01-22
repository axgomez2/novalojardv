<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'notes',
        'changed_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromStatusLabelAttribute(): ?string
    {
        if (!$this->from_status) {
            return null;
        }

        return match ($this->from_status) {
            'pending' => 'Aguardando Pagamento',
            'paid' => 'Pago',
            'processing' => 'Em Processamento',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => $this->from_status,
        };
    }

    public function getToStatusLabelAttribute(): string
    {
        return match ($this->to_status) {
            'pending' => 'Aguardando Pagamento',
            'paid' => 'Pago',
            'processing' => 'Em Processamento',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => $this->to_status,
        };
    }
}
