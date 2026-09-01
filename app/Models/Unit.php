<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasUuid;
use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use Auditable, HasFactory, HasUuid;

    public const CATEGORY_WEIGHT = 'weight';

    public const CATEGORY_VOLUME = 'volume';

    public const CATEGORY_LENGTH = 'length';

    public const CATEGORY_QUANTITY = 'quantity';

    public const CATEGORY_TIME = 'time';

    public const CATEGORY_AREA = 'area';

    public const CATEGORY_CUSTOM = 'custom';

    public const CATEGORIES = [
        self::CATEGORY_WEIGHT,
        self::CATEGORY_VOLUME,
        self::CATEGORY_LENGTH,
        self::CATEGORY_QUANTITY,
        self::CATEGORY_TIME,
        self::CATEGORY_AREA,
        self::CATEGORY_CUSTOM,
    ];

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'category',
        'is_base',
        'default_precision',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_base' => 'boolean',
            'default_precision' => 'integer',
        ];
    }

    /**
     * Business relationship (nullable for global system units).
     *
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return HasMany<UnitConversion, $this>
     */
    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    /**
     * @return HasMany<UnitConversion, $this>
     */
    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    /**
     * Scope query to include system default units + active tenant's units.
     *
     * @param  Builder<Unit>  $query
     * @return Builder<Unit>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('business_id');

            if (Context::hasBusiness()) {
                $q->orWhere('business_id', Context::business()?->id);
            }
        });
    }
}
