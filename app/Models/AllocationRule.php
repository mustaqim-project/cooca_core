<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationRule extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'cost_pool_id',
        'cost_driver_id',
        'cost_model_id',
        'target_category_id',
        'total_driver_capacity',
        'name',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_driver_capacity' => 'float',
        ];
    }

    /**
     * @return BelongsTo<CostPool, $this>
     */
    public function costPool(): BelongsTo
    {
        return $this->belongsTo(CostPool::class);
    }

    /**
     * @return BelongsTo<CostDriver, $this>
     */
    public function costDriver(): BelongsTo
    {
        return $this->belongsTo(CostDriver::class);
    }

    /**
     * @return BelongsTo<CostModel, $this>
     */
    public function costModel(): BelongsTo
    {
        return $this->belongsTo(CostModel::class);
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'target_category_id');
    }
}
