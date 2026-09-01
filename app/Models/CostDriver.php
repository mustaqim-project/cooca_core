<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostDriver extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    public const TYPE_PER_UNIT = 'per_unit';

    public const TYPE_REVENUE_PCT = 'revenue_pct';

    public const TYPE_LABOR_HOUR = 'labor_hour';

    public const TYPE_MACHINE_HOUR = 'machine_hour';

    public const TYPE_PRODUCTION_HOUR = 'production_hour';

    public const TYPE_AREA = 'area';

    public const TYPE_WEIGHT = 'weight';

    public const TYPE_VOLUME = 'volume';

    public const TYPE_ABC = 'abc';

    public const TYPE_CUSTOM = 'custom';

    public const TYPES = [
        self::TYPE_PER_UNIT,
        self::TYPE_REVENUE_PCT,
        self::TYPE_LABOR_HOUR,
        self::TYPE_MACHINE_HOUR,
        self::TYPE_PRODUCTION_HOUR,
        self::TYPE_AREA,
        self::TYPE_WEIGHT,
        self::TYPE_VOLUME,
        self::TYPE_ABC,
        self::TYPE_CUSTOM,
    ];

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'type',
        'custom_formula',
        'description',
    ];

    /**
     * @return HasMany<AllocationRule, $this>
     */
    public function allocationRules(): HasMany
    {
        return $this->hasMany(AllocationRule::class);
    }
}
