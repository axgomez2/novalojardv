<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class RecurringPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'type',
        'frequency',
        'day_of_month',
        'day_of_week',
        'start_date',
        'end_date',
        'next_due_date',
        'payment_category_id',
        'income_source_id',
        'supplier_id',
        'payment_method',
        'auto_generate',
        'days_before_notify',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'auto_generate' => 'boolean',
        'is_active' => 'boolean',
        'day_of_month' => 'integer',
        'day_of_week' => 'integer',
        'days_before_notify' => 'integer',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePayable($query)
    {
        return $query->where('type', 'payable');
    }

    public function scopeReceivable($query)
    {
        return $query->where('type', 'receivable');
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('next_due_date', '<=', now()->addDays($days))
            ->where('next_due_date', '>=', now());
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return $this->type === 'payable' ? 'A Pagar' : 'A Receber';
    }

    public function getFrequencyNameAttribute(): string
    {
        return match($this->frequency) {
            'daily' => 'Diário',
            'weekly' => 'Semanal',
            'biweekly' => 'Quinzenal',
            'monthly' => 'Mensal',
            'bimonthly' => 'Bimestral',
            'quarterly' => 'Trimestral',
            'semiannual' => 'Semestral',
            'annual' => 'Anual',
            default => $this->frequency,
        };
    }

    // Methods
    public function calculateNextDueDate(): ?Carbon
    {
        if (!$this->next_due_date) {
            return $this->start_date;
        }

        $next = match($this->frequency) {
            'daily' => $this->next_due_date->addDay(),
            'weekly' => $this->next_due_date->addWeek(),
            'biweekly' => $this->next_due_date->addWeeks(2),
            'monthly' => $this->next_due_date->addMonth(),
            'bimonthly' => $this->next_due_date->addMonths(2),
            'quarterly' => $this->next_due_date->addMonths(3),
            'semiannual' => $this->next_due_date->addMonths(6),
            'annual' => $this->next_due_date->addYear(),
            default => $this->next_due_date->addMonth(),
        };

        // Check if end_date is set and next is after it
        if ($this->end_date && $next->gt($this->end_date)) {
            return null;
        }

        return $next;
    }

    public function generateTransaction(): ?FinancialTransaction
    {
        if (!$this->is_active || !$this->next_due_date) {
            return null;
        }

        $transaction = FinancialTransaction::create([
            'type' => $this->type,
            'description' => $this->name,
            'notes' => $this->description,
            'amount' => $this->amount,
            'due_date' => $this->next_due_date,
            'status' => 'pending',
            'payment_category_id' => $this->payment_category_id,
            'income_source_id' => $this->income_source_id,
            'recurring_payment_id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'payment_method' => $this->payment_method,
            'created_by' => $this->created_by,
        ]);

        // Update next due date
        $this->next_due_date = $this->calculateNextDueDate();
        $this->save();

        return $transaction;
    }
}
