<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Global customer identity -- one account, many stores.
 * Authenticated exclusively via Google OAuth.
 * Separate from the per-tenant Customer CRM model.
 */
class GlobalCustomer extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'phone',
        'password',
        'avatar_url',
        'shipping_address',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------
    // Relations
    // ---------------------------------------------------------

    public function commerceOrders(): HasMany
    {
        return $this->hasMany(CommerceOrder::class, 'global_customer_id')->latest();
    }

    public function carts(): HasMany
    {
        return $this->hasMany(CustomerCart::class, 'global_customer_id');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function cartFor(string $businessId): CustomerCart
    {
        return $this->carts()->firstOrCreate(
            ['business_id' => $businessId],
            ['expires_at'  => null],
        );
    }

    /**
     * Profile is complete when name and phone are both filled.
     */
    public function isProfileComplete(): bool
    {
        return ! empty($this->name) && ! empty($this->phone);
    }

    /**
     * Phone is verified when phone_verified_at is set (permanent, lifetime).
     */
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Determine if customer email is verified.
     * Google registration is automatically verified.
     * Non-Google registration with email requires email verification.
     */
    public function hasVerifiedEmail(): bool
    {
        if (! empty($this->google_id)) {
            return true;
        }

        if (empty($this->email)) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }
}