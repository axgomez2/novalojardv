<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'mercado_pago_id',
        'mercado_pago_status',
        'mercado_pago_status_detail',
        'amount',
        'fee',
        'net_amount',
        'installments',
        'payer_info',
        'payment_response',
        'pix_qr_code',
        'pix_qr_code_base64',
        'pix_expiration',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'installments' => 'integer',
        'payer_info' => 'array',
        'payment_response' => 'array',
        'pix_expiration' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->mercado_pago_status === 'approved';
    }

    public function isPending(): bool
    {
        return in_array($this->mercado_pago_status, ['pending', 'in_process']);
    }

    public function isRejected(): bool
    {
        return $this->mercado_pago_status === 'rejected';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->mercado_pago_status) {
            'approved' => 'Aprovado',
            'pending' => 'Pendente',
            'in_process' => 'Em processamento',
            'rejected' => 'Rejeitado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => $this->mercado_pago_status ?? 'Desconhecido',
        };
    }
}
