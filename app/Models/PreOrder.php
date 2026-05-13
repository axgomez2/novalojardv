<?php

namespace App\Models;

use App\Enums\PreOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'client_user_id',
        'vinyl_stock_id',
        'quantity',
        'unit_price',
        'total_amount',
        'signal_amount',
        'signal_percentage',
        'status',
        'expected_arrival_date',
        'signal_due_date',
        'balance_due_date',
        'signal_paid_at',
        'balance_paid_at',
        'arrived_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'signal_payment_method',
        'balance_payment_method',
        'signal_payment_id',
        'balance_payment_id',
        'shipping_address',
        'shipping_cost',
        'customer_notes',
        'admin_notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'status' => PreOrderStatus::class,
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'signal_amount' => 'decimal:2',
        'signal_percentage' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipping_address' => 'array',
        'expected_arrival_date' => 'date',
        'signal_due_date' => 'date',
        'balance_due_date' => 'date',
        'signal_paid_at' => 'datetime',
        'balance_paid_at' => 'datetime',
        'arrived_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'signal_reminder_sent_at' => 'datetime',
        'signal_overdue_notified_at' => 'datetime',
        'arrival_notified_at' => 'datetime',
        'balance_reminder_sent_at' => 'datetime',
        'balance_overdue_notified_at' => 'datetime',
    ];

    // ========== Relations ==========
    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'client_user_id');
    }

    public function vinylStock(): BelongsTo
    {
        return $this->belongsTo(VinylStock::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PreOrderStatusHistory::class)->orderByDesc('created_at');
    }

    // ========== Computed ==========
    public function getBalanceAmountAttribute(): float
    {
        return round((float) $this->total_amount - (float) $this->signal_amount, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->total_amount, 2, ',', '.');
    }

    public function getFormattedSignalAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->signal_amount, 2, ',', '.');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'R$ ' . number_format($this->balance_amount, 2, ',', '.');
    }

    public function isSignalOverdue(): bool
    {
        return $this->status === PreOrderStatus::AwaitingSignal
            && $this->signal_due_date
            && $this->signal_due_date->isPast();
    }

    public function isBalanceOverdue(): bool
    {
        return $this->status === PreOrderStatus::AwaitingBalance
            && $this->balance_due_date
            && $this->balance_due_date->isPast();
    }

    // ========== Scopes ==========
    public function scopeActive($query)
    {
        return $query->whereIn('status', PreOrderStatus::activeStatuses());
    }

    public function scopeAwaitingSignal($query)
    {
        return $query->where('status', PreOrderStatus::AwaitingSignal->value);
    }

    public function scopeAwaitingBalance($query)
    {
        return $query->where('status', PreOrderStatus::AwaitingBalance->value);
    }

    public function scopeSignalOverdue($query)
    {
        return $query->where('status', PreOrderStatus::AwaitingSignal->value)
            ->whereNotNull('signal_due_date')
            ->whereDate('signal_due_date', '<', now());
    }

    public function scopeBalanceOverdue($query)
    {
        return $query->where('status', PreOrderStatus::AwaitingBalance->value)
            ->whereNotNull('balance_due_date')
            ->whereDate('balance_due_date', '<', now());
    }

    // ========== Helpers ==========
    public static function generateCode(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('code', 'like', "PV-{$year}-%")
            ->orderByDesc('id')
            ->value('code');

        $next = 1;
        if ($last) {
            $next = ((int) substr($last, -5)) + 1;
        }

        return 'PV-' . $year . '-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Registra mudança de status e dispara evento.
     */
    public function changeStatus(PreOrderStatus $newStatus, ?string $note = null, string $triggeredBy = 'admin', ?int $adminUserId = null): void
    {
        $oldStatus = $this->status;

        $this->status = $newStatus;

        // Preenche timestamp correspondente se aplicável
        match ($newStatus) {
            PreOrderStatus::SignalPaid => $this->signal_paid_at ??= now(),
            PreOrderStatus::Arrived => $this->arrived_at ??= now(),
            PreOrderStatus::BalancePaid => $this->balance_paid_at ??= now(),
            PreOrderStatus::Shipped => $this->shipped_at ??= now(),
            PreOrderStatus::Delivered => $this->delivered_at ??= now(),
            PreOrderStatus::Cancelled => $this->cancelled_at ??= now(),
            default => null,
        };

        $this->save();

        $this->statusHistories()->create([
            'from_status' => $oldStatus?->value,
            'to_status' => $newStatus->value,
            'admin_user_id' => $adminUserId,
            'triggered_by' => $triggeredBy,
            'note' => $note,
        ]);
    }
}
