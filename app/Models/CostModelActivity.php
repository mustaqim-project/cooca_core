<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostModelActivity extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'cost_model_activities';

    protected $fillable = [
        'cost_model_id',
        'activity_id',
        'consumed_quantity',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consumed_quantity' => 'float',
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
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
