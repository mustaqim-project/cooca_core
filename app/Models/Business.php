<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, HasSlug, HasUuid, SoftDeletes;

    public const ROUNDING_ROUND = 'ROUND';

    public const ROUNDING_CEIL = 'CEIL';

    public const ROUNDING_FLOOR = 'FLOOR';

    public const ROUNDING_ROUND_50 = 'ROUND_50';

    public const ROUNDING_ROUND_100 = 'ROUND_100';

    public const ROUNDING_ROUND_500 = 'ROUND_500';

    public const ROUNDING_ROUND_1000 = 'ROUND_1000';

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'description',
        'phone',
        'email',
        'address',
        'tax_identification_number',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'currency',
        'rounding_strategy',
        'currency_precision',
        'allow_negative_stock',
        'is_active',
        'suspended_reason',
        'suspended_at',
        'pos_supervisor_pin',
        'pos_max_cashier_discount_percent',
        'pos_require_pin_for_void',
        'pos_require_pin_for_refund',
        'pos_receipt_footer_note',
        'pos_show_product_images',
        'pos_enable_tax',
        'pos_tax_percent',
        'pos_enable_service_charge',
        'pos_service_charge_percent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allow_negative_stock' => 'boolean',
            'is_active' => 'boolean',
            'suspended_at' => 'datetime',
            'currency_precision' => 'integer',
            'pos_max_cashier_discount_percent' => 'float',
            'pos_require_pin_for_void' => 'boolean',
            'pos_require_pin_for_refund' => 'boolean',
            'pos_enable_tax' => 'boolean',
            'pos_show_product_images' => 'boolean',
            'pos_tax_percent' => 'float',
            'pos_enable_service_charge' => 'boolean',
            'pos_service_charge_percent' => 'float',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getCurrencyCodeAttribute(): string
    {
        return $this->currency ?? 'IDR';
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'SGD' => 'S$',
            'MYR' => 'RM',
            'JPY' => '¥',
            default => 'Rp',
        };
    }

    /**
     * Get users belonging to this business.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users', 'business_id', 'user_id')
            ->using(BusinessMembership::class)
            ->withPivot(['id', 'role', 'role_id'])
            ->withTimestamps();
    }

    /**
     * Get memberships for this business.
     *
     * @return HasMany<BusinessMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessMembership::class);
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BusinessSubscription::class)->latestOfMany();
    }

    public function landingPage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BusinessLandingPage::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function bomHeaders(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(BomHeader::class, CostModel::class, 'business_id', 'cost_model_id');
    }

    public function aiTokenUsages(): HasMany
    {
        return $this->hasMany(AiTokenUsage::class);
    }

    public function aiTokenTopups(): HasMany
    {
        return $this->hasMany(AiTokenTopup::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }
}
