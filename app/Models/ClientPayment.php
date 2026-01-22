<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPayment extends Model
{
    protected $fillable = [
        'order_id',
        'client_user_id',
        'payment_method',
        'status',
        'amount',
        'fee',
        'net_amount',
        'gateway',
        'gateway_transaction_id',
        'gateway_payment_id',
        'gateway_response',
        'card_brand',
        'card_last_digits',
        'installments',
        'pix_qr_code',
        'pix_qr_code_base64',
        'pix_expiration',
        'boleto_url',
        'boleto_barcode',
        'boleto_due_date',
        'paid_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'gateway_response' => 'array',
            'installments' => 'integer',
            'pix_expiration' => 'datetime',
            'boleto_due_date' => 'date',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    // Accessors

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'boleto' => 'Boleto Bancário',
            'bank_transfer' => 'Transferência Bancária',
            default => 'Outro',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Aguardando',
            'processing' => 'Processando',
            'approved' => 'Aprovado',
            'declined' => 'Recusado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'chargeback' => 'Estorno',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'approved' => 'green',
            'declined' => 'red',
            'cancelled' => 'gray',
            'refunded' => 'purple',
            'chargeback' => 'red',
            default => 'gray',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getInstallmentsLabelAttribute(): string
    {
        if ($this->installments <= 1) {
            return 'À vista';
        }

        $installmentValue = $this->amount / $this->installments;
        return "{$this->installments}x de R$ " . number_format($installmentValue, 2, ',', '.');
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsPixExpiredAttribute(): bool
    {
        if ($this->payment_method !== 'pix' || !$this->pix_expiration) {
            return false;
        }

        return $this->pix_expiration->isPast();
    }

    public function getIsBoletoExpiredAttribute(): bool
    {
        if ($this->payment_method !== 'boleto' || !$this->boleto_due_date) {
            return false;
        }

        return $this->boleto_due_date->isPast();
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    // Methods

    public function markAsApproved(): void
    {
        $this->update([
            'status' => 'approved',
            'paid_at' => now(),
        ]);

        // Update order status
        $this->order->markAsPaid();
    }

    public function markAsDeclined(): void
    {
        $this->update(['status' => 'declined']);
    }

    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }

    public function calculateNetAmount(): void
    {
        $this->net_amount = $this->amount - $this->fee;
        $this->save();
    }
}
