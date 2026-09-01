<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overhead extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    public const PERIOD_MONTHLY = 'monthly';

    public const PERIOD_YEARLY = 'yearly';

    public const PERIOD_ONE_TIME = 'one_time';

    public const BEHAVIOR_FIXED = 'fixed';

    public const BEHAVIOR_VARIABLE = 'variable';

    protected $fillable = [
        'business_id',
        'currency_id',
        'name',
        'slug',
        'amount',
        'period',
        'behavior',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    /**
     * Get monthly normalized equivalent amount.
     */
    public function monthlyAmount(): float
    {
        return match ($this->period) {
            self::PERIOD_YEARLY => (float) ($this->amount / 12.0),
            default => (float) $this->amount,
        };
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return BelongsToMany<CostPool, $this>
     */
    public function costPools(): BelongsToMany
    {
        return $this->belongsToMany(CostPool::class, 'cost_pool_overheads')
            ->using(CostPoolOverhead::class);
    }
}
