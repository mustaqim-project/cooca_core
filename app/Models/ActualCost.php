<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActualCost extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'actual_costs';

    protected $fillable = [
        'business_id',
        'product_id',
        'period',
        'actual_material_cost',
        'actual_labor_cost',
        'actual_machine_cost',
        'actual_overhead_cost',
        'actual_total_cost',
        'actual_output_qty',
        'actual_hpp_per_unit',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actual_material_cost' => 'float',
            'actual_labor_cost' => 'float',
            'actual_machine_cost' => 'float',
            'actual_overhead_cost' => 'float',
            'actual_total_cost' => 'float',
            'actual_output_qty' => 'float',
            'actual_hpp_per_unit' => 'float',
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
     * @return HasMany<CostVariance, $this>
     */
    public function variances(): HasMany
    {
        return $this->hasMany(CostVariance::class);
    }
}
