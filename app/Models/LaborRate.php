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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaborRate extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    public const BASIS_HOURLY = 'hourly';

    public const BASIS_DAILY = 'daily';

    public const BASIS_MONTHLY = 'monthly';

    public const BASIS_PER_UNIT = 'per_unit';

    public const BASIS_PER_PROJECT = 'per_project';

    public const BASIS_PER_TASK = 'per_task';

    public const BASES = [
        self::BASIS_HOURLY,
        self::BASIS_DAILY,
        self::BASIS_MONTHLY,
        self::BASIS_PER_UNIT,
        self::BASIS_PER_PROJECT,
        self::BASIS_PER_TASK,
    ];

    protected $fillable = [
        'business_id',
        'currency_id',
        'name',
        'slug',
        'basis',
        'rate_amount',
        'is_subcontractor',
        'overtime_multiplier',
        'working_days_per_month',
        'working_hours_per_day',
        'utilization_rate',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate_amount' => 'float',
            'is_subcontractor' => 'boolean',
            'overtime_multiplier' => 'float',
            'working_days_per_month' => 'integer',
            'working_hours_per_day' => 'float',
            'utilization_rate' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasMany<CostModelLabor, $this>
     */
    public function costModelLabors(): HasMany
    {
        return $this->hasMany(CostModelLabor::class);
    }
}
