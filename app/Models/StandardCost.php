<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardCost extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'standard_costs';

    protected $fillable = [
        'business_id',
        'product_cost_version_id',
        'product_id',
        'standard_material_cost',
        'standard_labor_cost',
        'standard_machine_cost',
        'standard_overhead_cost',
        'standard_hpp',
        'standard_snapshot',
        'effective_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'standard_material_cost' => 'float',
            'standard_labor_cost' => 'float',
            'standard_machine_cost' => 'float',
            'standard_overhead_cost' => 'float',
            'standard_hpp' => 'float',
            'standard_snapshot' => 'array',
            'effective_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<ProductCostVersion, $this>
     */
    public function productCostVersion(): BelongsTo
    {
        return $this->belongsTo(ProductCostVersion::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
