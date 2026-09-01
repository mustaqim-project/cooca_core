<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostingResult extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'costing_results';

    protected $fillable = [
        'costing_run_id',
        'total_material_cost',
        'total_labor_cost',
        'total_machine_cost',
        'total_overhead_cost',
        'total_hpp',
        'hpp_per_unit',
        'currency_id',
        'breakdown_snapshot',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_material_cost' => 'float',
            'total_labor_cost' => 'float',
            'total_machine_cost' => 'float',
            'total_overhead_cost' => 'float',
            'total_hpp' => 'float',
            'hpp_per_unit' => 'float',
            'breakdown_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CostingRun, $this>
     */
    public function costingRun(): BelongsTo
    {
        return $this->belongsTo(CostingRun::class);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasMany<CostingResultItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CostingResultItem::class);
    }
}
