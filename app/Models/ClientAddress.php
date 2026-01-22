<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAddress extends Model
{
    protected $fillable = [
        'client_user_id',
        'label',
        'recipient_name',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'reference',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Relationships

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class);
    }

    // Accessors

    public function getFormattedZipCodeAttribute(): string
    {
        $zip = preg_replace('/\D/', '', $this->zip_code);
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $zip);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->street . ', ' . $this->number,
        ];

        if ($this->complement) {
            $parts[0] .= ' - ' . $this->complement;
        }

        $parts[] = $this->neighborhood;
        $parts[] = $this->city . '/' . $this->state;
        $parts[] = $this->formatted_zip_code;

        return implode(', ', $parts);
    }

    public function getShortAddressAttribute(): string
    {
        return "{$this->street}, {$this->number} - {$this->neighborhood}, {$this->city}/{$this->state}";
    }

    // Methods

    public function setAsDefault(): void
    {
        // Remove default from other addresses
        static::where('client_user_id', $this->client_user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    // Boot

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($address) {
            // If this is the first address, make it default
            if (!static::where('client_user_id', $address->client_user_id)->exists()) {
                $address->is_default = true;
            }
        });
    }
}
