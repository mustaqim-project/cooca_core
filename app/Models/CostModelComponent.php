<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CostModelComponent extends Pivot
{
    use HasUuid;

    protected $table = 'cost_model_components';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'cost_model_id',
        'cost_component_id',
        'is_included_in_hpp',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_included_in_hpp' => 'boolean',
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
     * @return BelongsTo<CostComponent, $this>
     */
    public function costComponent(): BelongsTo
    {
        return $this->belongsTo(CostComponent::class);
    }
}
