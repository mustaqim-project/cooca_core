<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingScenario extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'pricing_scenarios';

    protected $fillable = [
        'business_id',
        'cost_model_id',
        'name',
        'scenario_input',
        'scenario_result',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scenario_input' => 'array',
            'scenario_result' => 'array',
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
