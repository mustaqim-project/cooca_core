<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'slug',
        'company_name',
        'email',
        'password',
        'avatar_url',
        'phone',
        'phone_verified_at',
        'email_verified_at',
        'billing_address',
        'shipping_address',
        'tax_identification_number',
        'payment_terms_days',
        'segment',
        'membership_tier',
        'points_balance',
        'total_spent',
        'total_orders_count',
        'birth_date',
        'anniversary_date',
        'credit_limit',
        'current_credit_balance',
        'notes',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'payment_terms_days' => 'integer',
            'points_balance' => 'integer',
            'total_spent' => 'float',
            'total_orders_count' => 'integer',
            'credit_limit' => 'float',
            'current_credit_balance' => 'float',
            'birth_date' => 'date',
            'anniversary_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Resolve route binding by either UUID id or slug.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    public function getLoyaltyPointsAttribute(): int
    {
        return (int) ($this->points_balance ?? 0);
    }

    public function setLoyaltyPointsAttribute($value): void
    {
        $this->attributes['points_balance'] = (int) $value;
    }

    /**
     * @return HasMany<PurchaseOrder, $this>
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<PosOrder, $this>
     */
    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    /**
     * @return HasMany<CustomerPointHistory, $this>
     */
    public function pointHistories(): HasMany
    {
        return $this->hasMany(CustomerPointHistory::class);
    }

    /**
     * @return HasMany<CustomerCreditTransaction, $this>
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CustomerCreditTransaction::class);
    }

    /**
     * @return HasMany<CommerceOrder, $this>
     */
    public function commerceOrders(): HasMany
    {
        return $this->hasMany(CommerceOrder::class, 'customer_id')->latest();
    }

    public function isProfileComplete(): bool
    {
        return ! empty($this->name) && ! empty($this->phone);
    }

    public function isPhoneVerified(): bool
    {
        return ! empty($this->phone_verified_at);
    }
}
