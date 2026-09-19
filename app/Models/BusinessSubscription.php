<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BusinessSubscription extends Model
{
    use HasUuids, BelongsToBusiness;

    public const PLAN_FREE = 'free';
    public const PLAN_CORE = 'core_monthly';
    public const PLAN_CORE_MONTHLY = 'core_monthly';
    public const PLAN_CORE_ANNUAL = 'core_annual';

    public const PLAN_STANDARD_MONTHLY = 'standard_monthly';
    public const PLAN_STANDARD_ANNUAL = 'standard_annual';
    public const PLAN_PREMIUM_MONTHLY = 'premium_monthly';
    public const PLAN_PREMIUM_ANNUAL = 'premium_annual';
    public const PLAN_PRESTIGE_MONTHLY = 'prestige_monthly';
    public const PLAN_PRESTIGE_ANNUAL = 'prestige_annual';

    public const TIER_FREE = 'free';
    public const TIER_STANDARD = 'standard';
    public const TIER_PREMIUM = 'premium';
    public const TIER_PRESTIGE = 'prestige';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_OVER_LIMIT = 'over_limit';

    protected $fillable = [
        'business_id',
        'plan_code',
        'price',
        'status',
        'starts_at',
        'ends_at',
        'ai_tokens_monthly_allowance',
        'ai_tokens_remaining',
        'last_token_reset_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'last_token_reset_at' => 'date',
        'price' => 'decimal:2',
        'ai_tokens_monthly_allowance' => 'integer',
        'ai_tokens_remaining' => 'integer',
    ];

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    public function isOperational(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAST_DUE], true);
    }

    public function isCorePlan(): bool
    {
        return $this->isOperational() && $this->plan_code !== self::PLAN_FREE;
    }

    public function isFreePlan(): bool
    {
        return ! $this->isCorePlan();
    }

    public function getTier(): string
    {
        if (! $this->isOperational()) {
            return self::TIER_FREE;
        }

        $code = (string) $this->plan_code;

        if (str_starts_with($code, 'prestige')) {
            return self::TIER_PRESTIGE;
        }

        if (str_starts_with($code, 'premium') || str_starts_with($code, 'core')) {
            return self::TIER_PREMIUM;
        }

        if (str_starts_with($code, 'standard')) {
            return self::TIER_STANDARD;
        }

        return self::TIER_FREE;
    }

    public function getTierLevel(): int
    {
        return match ($this->getTier()) {
            self::TIER_PRESTIGE => 3,
            self::TIER_PREMIUM => 2,
            self::TIER_STANDARD => 1,
            default => 0,
        };
    }

    public function hasTier(string $requiredTier): bool
    {
        $requiredLevel = match ($requiredTier) {
            self::TIER_PRESTIGE => 3,
            self::TIER_PREMIUM => 2,
            self::TIER_STANDARD => 1,
            default => 0,
        };

        return $this->getTierLevel() >= $requiredLevel;
    }
}
