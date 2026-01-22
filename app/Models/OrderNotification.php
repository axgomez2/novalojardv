<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNotification extends Model
{
    protected $fillable = [
        'order_id',
        'channel',
        'type',
        'recipient',
        'subject',
        'content',
        'status',
        'error_message',
        'response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'email' => 'E-mail',
            'whatsapp' => 'WhatsApp',
            'sms' => 'SMS',
            default => $this->channel,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'order_created' => 'Pedido Criado',
            'payment_confirmed' => 'Pagamento Confirmado',
            'order_processing' => 'Pedido em Processamento',
            'order_shipped' => 'Pedido Enviado',
            'order_delivered' => 'Pedido Entregue',
            'order_cancelled' => 'Pedido Cancelado',
            'tracking_update' => 'Atualização de Rastreio',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'sent' => 'Enviado',
            'failed' => 'Falhou',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'sent' => 'green',
            'failed' => 'red',
            default => 'gray',
        };
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
}
