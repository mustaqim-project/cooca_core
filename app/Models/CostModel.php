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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CostModel extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid;

    public const METHOD_SIMPLE = 'simple';

    public const METHOD_PER_UNIT = 'per_unit';

    public const METHOD_RECIPE_BOM = 'recipe_bom';

    public const METHOD_JOB = 'job';

    public const METHOD_PROCESS = 'process';

    public const METHOD_ABC = 'abc';

    public const METHOD_SERVICE = 'service';

    public const METHOD_RETAIL = 'retail';

    public const METHOD_CUSTOM = 'custom';

    public const METHODS = [
        self::METHOD_SIMPLE,
        self::METHOD_PER_UNIT,
        self::METHOD_RECIPE_BOM,
        self::METHOD_JOB,
        self::METHOD_PROCESS,
        self::METHOD_ABC,
        self::METHOD_SERVICE,
        self::METHOD_RETAIL,
        self::METHOD_CUSTOM,
    ];

    public const BASIS_PLANNED = 'planned';

    public const BASIS_ACTUAL = 'actual';

    public const BASIS_SELLABLE = 'sellable';

    protected $fillable = [
        'business_id',
        'product_id',
        'name',
        'slug',
        'method',
        'output_basis',
        'formula_definition',
        'formula_version',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'formula_definition' => 'array',
            'formula_version' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsToMany<CostComponent, $this>
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(CostComponent::class, 'cost_model_components')
            ->using(CostModelComponent::class)
            ->withPivot(['id', 'is_included_in_hpp', 'notes'])
            ->withTimestamps();
    }

    /**
     * @return HasOne<BomHeader, $this>
     */
    public function bomHeader(): HasOne
    {
        return $this->hasOne(BomHeader::class);
    }

    /**
     * @return HasMany<BomHeader, $this>
     */
    public function bomHeaders(): HasMany
    {
        return $this->hasMany(BomHeader::class);
    }

    /**
     * @return HasMany<CostModelLabor, $this>
     */
    public function labors(): HasMany
    {
        return $this->hasMany(CostModelLabor::class);
    }

    /**
     * @return HasMany<CostModelMachine, $this>
     */
    public function machines(): HasMany
    {
        return $this->hasMany(CostModelMachine::class);
    }

    /**
     * @return HasMany<CostModelActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CostModelActivity::class);
    }

    /**
     * @return HasMany<ProductCostVersion, $this>
     */
    public function costVersions(): HasMany
    {
        return $this->hasMany(ProductCostVersion::class);
    }

    /**
     * @return HasOne<ProductCostVersion, $this>
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ProductCostVersion::class)->latestOfMany('version_number');
    }

    /**
     * @return HasMany<CostingRun, $this>
     */
    public function costingRuns(): HasMany
    {
        return $this->hasMany(CostingRun::class);
    }
}
