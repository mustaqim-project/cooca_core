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

    public function isCorePlan(): bool
    {
        return in_array($this->plan_code, [self::PLAN_CORE_MONTHLY, self::PLAN_CORE_ANNUAL], true)
            && $this->status === self::STATUS_ACTIVE;
    }

    public function isFreePlan(): bool
    {
        return !$this->isCorePlan();
    }
}
