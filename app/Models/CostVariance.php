<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostVariance extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_MATERIAL_PRICE = 'material_price';

    public const TYPE_MATERIAL_QUANTITY = 'material_quantity';

    public const TYPE_LABOR_RATE = 'labor_rate';

    public const TYPE_LABOR_EFFICIENCY = 'labor_efficiency';

    public const TYPE_OVERHEAD_SPENDING = 'overhead_spending';

    public const TYPE_YIELD = 'yield';

    public const TYPE_WASTE = 'waste';

    public const TYPE_TOTAL = 'total';

    public const NATURE_FAVORABLE = 'favorable';

    public const NATURE_UNFAVORABLE = 'unfavorable';

    public const NATURE_NEUTRAL = 'neutral';

    protected $table = 'cost_variances';

    protected $fillable = [
        'business_id',
        'standard_cost_id',
        'actual_cost_id',
        'product_id',
        'variance_type',
        'variance_nature',
        'amount',
        'percentage',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'percentage' => 'float',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<StandardCost, $this>
     */
    public function standardCost(): BelongsTo
    {
        return $this->belongsTo(StandardCost::class);
    }

    /**
     * @return BelongsTo<ActualCost, $this>
     */
    public function actualCost(): BelongsTo
    {
        return $this->belongsTo(ActualCost::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
