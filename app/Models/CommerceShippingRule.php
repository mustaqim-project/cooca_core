<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommerceShippingRule extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_FLAT           = 'flat';
    public const TYPE_DISTANCE_TIER  = 'distance_tier';
    public const TYPE_FREE_THRESHOLD = 'free_threshold';

    protected $table = 'commerce_shipping_rules';

    protected $fillable = [
        'business_id',
        'name',
        'rule_type',
        'min_distance_km',
        'max_distance_km',
        'rate_amount',
        'min_order_for_free',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_distance_km' => 'float',
            'max_distance_km' => 'float',
            'rate_amount' => 'float',
            'min_order_for_free' => 'float',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(CommerceOrder::class, 'shipping_rule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('rate_amount');
    }

    /**
     * Determine if subtotal qualifies for free shipping under this rule.
     */
    public function isFreeFor(float $subtotal): bool
    {
        if ($this->rule_type === self::TYPE_FREE_THRESHOLD && $this->min_order_for_free !== null) {
            return $subtotal >= $this->min_order_for_free;
        }

        if ($this->min_order_for_free !== null && $this->min_order_for_free > 0 && $subtotal >= $this->min_order_for_free) {
            return true;
        }

        return (float) $this->rate_amount <= 0.0;
    }

    /**
     * Calculate applicable shipping rate for a given order subtotal and optional distance.
     * Returns null if rule does not match distance criteria.
     */
    public function calculateRate(float $subtotal, ?float $distanceKm = null): ?float
    {
        if (! $this->is_active) {
            return null;
        }

        // Free shipping qualification
        if ($this->isFreeFor($subtotal)) {
            return 0.0;
        }

        // Distance tier check
        if ($this->rule_type === self::TYPE_DISTANCE_TIER) {
            if ($distanceKm === null) {
                return (float) $this->rate_amount;
            }

            if ($this->min_distance_km !== null && $distanceKm < $this->min_distance_km) {
                return null;
            }

            if ($this->max_distance_km !== null && $distanceKm > $this->max_distance_km) {
                return null;
            }
        }

        return (float) $this->rate_amount;
    }
}
