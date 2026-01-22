<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ClientUser extends Authenticatable
{
    use HasUuids, Notifiable, HasApiTokens;

    const ROLE_CLIENT = 'client';
    const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'phone',
        'cpf',
        'birth_date',
        'email_verified_at',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role Methods

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    // Relationships

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(ClientAddress::class)->where('is_default', true);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(ClientWishlist::class);
    }

    public function wantlists(): HasMany
    {
        return $this->hasMany(ClientWantlist::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(ClientCart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ClientOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ClientPayment::class);
    }

    // Accessors

    public function getFormattedCpfAttribute(): ?string
    {
        if (!$this->cpf) {
            return null;
        }

        $cpf = preg_replace('/\D/', '', $this->cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $this->phone);
        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        }
        if (strlen($phone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
        }

        return $this->phone;
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    // Scopes

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeWithGoogleAccount($query)
    {
        return $query->whereNotNull('google_id');
    }

    // Methods

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function getOrCreateCart(): ClientCart
    {
        return $this->cart ?? $this->cart()->create();
    }

    public function addToWishlist(VinylStock $vinylStock, ?string $notes = null): ClientWishlist
    {
        return $this->wishlists()->firstOrCreate(
            ['vinyl_stock_id' => $vinylStock->id],
            ['notes' => $notes]
        );
    }

    public function removeFromWishlist(VinylStock $vinylStock): bool
    {
        return $this->wishlists()->where('vinyl_stock_id', $vinylStock->id)->delete() > 0;
    }

    public function isInWishlist(VinylStock $vinylStock): bool
    {
        return $this->wishlists()->where('vinyl_stock_id', $vinylStock->id)->exists();
    }

    public function getTotalSpent(): float
    {
        return $this->orders()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->sum('total');
    }

    public function getOrdersCount(): int
    {
        return $this->orders()
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();
    }
}
