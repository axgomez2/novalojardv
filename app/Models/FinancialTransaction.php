<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'description',
        'notes',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'payment_category_id',
        'income_source_id',
        'recurring_payment_id',
        'supplier_id',
        'reference',
        'payment_method',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    public function incomeSource(): BelongsTo
    {
        return $this->belongsTo(IncomeSource::class);
    }

    public function recurringPayment(): BelongsTo
    {
        return $this->belongsTo(RecurringPayment::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePayable($query)
    {
        return $query->where('type', 'payable');
    }

    public function scopeReceivable($query)
    {
        return $query->where('type', 'receivable');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                    ->where('due_date', '<', now()->startOfDay());
            });
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return $this->type === 'payable' ? 'A Pagar' : 'A Receber';
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Vencido',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->lt(now()->startOfDay());
    }

    // Methods
    public function markAsPaid(?string $paymentDate = null, ?string $paymentMethod = null): self
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);

        return $this;
    }

    public function markAsOverdue(): self
    {
        $this->update(['status' => 'overdue']);
        return $this;
    }

    public function cancel(): self
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }

    // Static methods for reports
    public static function getTotalPayable($startDate = null, $endDate = null)
    {
        $query = static::payable()->pending();
        
        if ($startDate && $endDate) {
            $query->byPeriod($startDate, $endDate);
        }
        
        return $query->sum('amount');
    }

    public static function getTotalReceivable($startDate = null, $endDate = null)
    {
        $query = static::receivable()->pending();
        
        if ($startDate && $endDate) {
            $query->byPeriod($startDate, $endDate);
        }
        
        return $query->sum('amount');
    }

    public static function getTotalPaidExpenses($startDate = null, $endDate = null)
    {
        $query = static::payable()->paid();
        
        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }
        
        return $query->sum('amount');
    }

    public static function getTotalReceivedIncome($startDate = null, $endDate = null)
    {
        $query = static::receivable()->paid();
        
        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }
        
        return $query->sum('amount');
    }
}
