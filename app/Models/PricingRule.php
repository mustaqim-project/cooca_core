<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STRATEGY_MARKUP = 'markup';

    public const STRATEGY_MARGIN = 'margin';

    public const STRATEGY_TARGET_PROFIT = 'target_profit';

    public const STRATEGY_FIXED_PRICE = 'fixed_price';

    public const STRATEGY_TIERED = 'tiered';

    public const STRATEGIES = [
        self::STRATEGY_MARKUP,
        self::STRATEGY_MARGIN,
        self::STRATEGY_TARGET_PROFIT,
        self::STRATEGY_FIXED_PRICE,
        self::STRATEGY_TIERED,
    ];

    protected $table = 'pricing_rules';

    protected $fillable = [
        'business_id',
        'cost_model_id',
        'product_id',
        'name',
        'strategy',
        'value',
        'target_profit_amount',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'float',
            'target_profit_amount' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CostModel, $this>
     */
    public function costModel(): BelongsTo
    {
        return $this->belongsTo(CostModel::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
