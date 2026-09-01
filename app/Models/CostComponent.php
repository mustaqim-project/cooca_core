<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CostComponent extends Model
{
    use HasFactory, HasSlug, HasUuid;

    public const BEHAVIOR_FIXED = 'fixed';

    public const BEHAVIOR_VARIABLE = 'variable';

    public const BEHAVIOR_SEMI_VARIABLE = 'semi_variable';

    public const TRACEABILITY_DIRECT = 'direct';

    public const TRACEABILITY_INDIRECT = 'indirect';

    protected $fillable = [
        'business_id',
        'cost_category_id',
        'slug',
        'name',
        'behavior',
        'traceability',
        'is_system',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id');
    }

    /**
     * @return BelongsToMany<CostModel, $this>
     */
    public function costModels(): BelongsToMany
    {
        return $this->belongsToMany(CostModel::class, 'cost_model_components')
            ->using(CostModelComponent::class)
            ->withPivot(['id', 'is_included_in_hpp', 'notes'])
            ->withTimestamps();
    }
}
