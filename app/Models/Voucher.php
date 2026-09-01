<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'valid_from',
        'valid_until',
        'usage_limit',
        'used_count',
        'tier_eligibility',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_value' => 'float',
            'min_order_amount' => 'float',
            'max_discount_amount' => 'float',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForAmount(float $amount): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->valid_from && now()->lt($this->valid_from->startOfDay())) {
            return false;
        }

        if ($this->valid_until && now()->gt($this->valid_until->endOfDay())) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($amount < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (! $this->isValidForAmount($subtotal)) {
            return 0.0;
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = ($subtotal * $this->discount_value) / 100.0;
            if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
                $discount = min($discount, $this->max_discount_amount);
            }
            return $discount;
        }

        return min($this->discount_value, $subtotal);
    }
}
