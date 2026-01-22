<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInvoice extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_number',
        'type',
        'sender_name',
        'sender_cpf_cnpj',
        'sender_address',
        'recipient_name',
        'recipient_cpf_cnpj',
        'recipient_address',
        'total_value',
        'shipping_value',
        'items',
        'pdf_path',
        'nfe_key',
        'nfe_protocol',
        'nfe_xml',
        'nfe_issued_at',
    ];

    protected function casts(): array
    {
        return [
            'total_value' => 'decimal:2',
            'shipping_value' => 'decimal:2',
            'items' => 'array',
            'nfe_issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'content_declaration' => 'Declaração de Conteúdo',
            'nfe' => 'NF-e',
            default => $this->type,
        };
    }

    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return asset('storage/' . $this->pdf_path);
    }

    public function isNfe(): bool
    {
        return $this->type === 'nfe';
    }

    public function isContentDeclaration(): bool
    {
        return $this->type === 'content_declaration';
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = static::where('invoice_number', 'like', "{$prefix}{$year}%")
            ->orderByDesc('invoice_number')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $year . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }
}
