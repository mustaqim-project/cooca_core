<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPromo extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const TYPES = [self::TYPE_PERCENTAGE, self::TYPE_FIXED];

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'valid_from',
        'valid_until',
        'usage_limit',
        'used_count',
        'usage_per_business_limit',
        'applicable_tiers',
        'applicable_cycles',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_value' => 'float',
            'max_discount_amount' => 'float',
            'min_order_amount' => 'float',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'usage_per_business_limit' => 'integer',
            'applicable_tiers' => 'array',
            'applicable_cycles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionPromoUsage::class, 'promo_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'promo_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if promo is currently valid for given checkout parameters.
     *
     * @return array{valid: bool, reason: ?string}
     */
    public function validateFor(
        float $subtotal,
        ?string $tier = null,
        ?string $cycle = null,
        ?string $businessId = null
    ): array {
        if (! $this->is_active) {
            return ['valid' => false, 'reason' => 'Kode promo sedang tidak aktif atau dinonaktifkan.'];
        }

        $today = Carbon::today();

        if ($this->valid_from && $today->lt($this->valid_from)) {
            return ['valid' => false, 'reason' => 'Kode promo belum mulai berlaku (mulai ' . $this->valid_from->format('d/m/Y') . ').'];
        }

        if ($this->valid_until && $today->gt($this->valid_until)) {
            return ['valid' => false, 'reason' => 'Kode promo telah kedaluwarsa sejak ' . $this->valid_until->format('d/m/Y') . '.'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'reason' => 'Kuota pemakaian kode promo ini sudah habis.'];
        }

        if ($this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            return [
                'valid' => false,
                'reason' => 'Minimum order untuk promo ini adalah Rp ' . number_format($this->min_order_amount, 0, ',', '.') . '.',
            ];
        }

        if (! empty($this->applicable_tiers) && $tier !== null) {
            $allowedTiers = array_map('strtolower', (array) $this->applicable_tiers);
            if (! in_array(strtolower($tier), $allowedTiers, true)) {
                return [
                    'valid' => false,
                    'reason' => 'Kode promo ini khusus untuk paket: ' . implode(', ', array_map('ucfirst', $allowedTiers)) . '.',
                ];
            }
        }

        if (! empty($this->applicable_cycles) && $cycle !== null) {
            $allowedCycles = array_map('strtolower', (array) $this->applicable_cycles);
            if (! in_array(strtolower($cycle), $allowedCycles, true)) {
                $labels = [
                    'monthly' => 'Bulanan',
                    'annual'  => 'Tahunan',
                ];
                $cycleNames = array_map(fn ($c) => $labels[$c] ?? ucfirst($c), $allowedCycles);

                return [
                    'valid' => false,
                    'reason' => 'Kode promo ini khusus untuk siklus langganan ' . implode(' atau ', $cycleNames) . '.',
                ];
            }
        }

        if ($businessId !== null && $this->usage_per_business_limit > 0) {
            $usedByBusiness = $this->usages()->where('business_id', $businessId)->count();
            if ($usedByBusiness >= $this->usage_per_business_limit) {
                return ['valid' => false, 'reason' => 'Bisnis Anda telah mencapai batas pemakaian untuk kode promo ini.'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Human-readable discount badge — e.g. "30%" or "Rp 50.000".
     */
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $pct = rtrim(rtrim(number_format($this->discount_value, 2, ',', '.'), '0'), ',');

            return "{$pct}%";
        }

        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }

    /**
     * Calculate discount amount from subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = ($subtotal * $this->discount_value) / 100.0;
            if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }

            return round(min($discount, $subtotal), 2);
        }

        return round(min((float) $this->discount_value, $subtotal), 2);
    }
}
