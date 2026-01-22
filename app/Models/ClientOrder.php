<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'source',
        'created_by',
        'client_user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_cpf',
        'shipping_address_id',
        'billing_address_id',
        'shipping_address_data',
        'status',
        'subtotal',
        'shipping_cost',
        'discount',
        'total',
        'coupon_code',
        'coupon_discount',
        'shipping_method',
        'shipping_service_id',
        'shipping_service_name',
        'shipping_carrier',
        'shipping_deadline',
        'shipping_response',
        'tracking_code',
        'shipped_at',
        'delivered_at',
        'customer_notes',
        'admin_notes',
        'invoice_number',
        'invoice_generated_at',
        'nfe_number',
        'nfe_key',
        'nfe_protocol',
        'nfe_issued_at',
        'nfe_status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'invoice_generated_at' => 'datetime',
            'nfe_issued_at' => 'datetime',
            'shipping_response' => 'array',
            'shipping_address_data' => 'array',
        ];
    }

    // Relationships

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class, 'billing_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClientOrderItem::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'order_id');
    }

    public function lastPayment(): HasOne
    {
        return $this->hasOne(OrderPayment::class, 'order_id')->latestOfMany();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(OrderInvoice::class, 'order_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(OrderNotification::class, 'order_id');
    }

    // Accessors

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Aguardando Pagamento',
            'paid' => 'Pago',
            'processing' => 'Em Processamento',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'paid' => 'blue',
            'processing' => 'indigo',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'red',
            'refunded' => 'gray',
            default => 'gray',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'R$ ' . number_format($this->total, 2, ',', '.');
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getIsPaidAttribute(): bool
    {
        return in_array($this->status, ['paid', 'processing', 'shipped', 'delivered']);
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, ['pending', 'paid']);
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->clientUser?->name ?? $this->guest_name ?? 'Cliente não identificado';
    }

    public function getCustomerEmailAttribute(): ?string
    {
        return $this->clientUser?->email ?? $this->guest_email;
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->clientUser?->phone ?? $this->guest_phone;
    }

    public function getIsPdvAttribute(): bool
    {
        return $this->source === 'pdv';
    }

    public function getIsGuestAttribute(): bool
    {
        return is_null($this->client_user_id);
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->source === 'pdv' ? 'PDV' : 'Online';
    }

    public function getShippingAddressFormattedAttribute(): ?string
    {
        if ($this->shippingAddress) {
            return $this->shippingAddress->full_address;
        }
        
        if ($this->shipping_address_data) {
            $addr = $this->shipping_address_data;
            return implode(', ', array_filter([
                ($addr['street'] ?? '') . ', ' . ($addr['number'] ?? ''),
                $addr['complement'] ?? null,
                $addr['neighborhood'] ?? null,
                ($addr['city'] ?? '') . '/' . ($addr['state'] ?? ''),
                $addr['zip_code'] ?? null,
            ]));
        }
        
        return null;
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', ['paid', 'processing', 'shipped', 'delivered']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->whereIn('status', ['cancelled', 'refunded']);
    }

    // Methods

    public static function generateOrderNumber(): string
    {
        $prefix = date('Ymd');
        $lastOrder = static::withTrashed()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsShipped(string $trackingCode, ?string $shippingMethod = null): void
    {
        $this->update([
            'status' => 'shipped',
            'tracking_code' => $trackingCode,
            'shipping_method' => $shippingMethod ?? $this->shipping_method,
            'shipped_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
        
        // Restore stock
        foreach ($this->items as $item) {
            $item->vinylStock->increment('stock', $item->quantity);
        }
    }

    public function refund(): void
    {
        $this->update(['status' => 'refunded']);
    }

    // Boot

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }
}
