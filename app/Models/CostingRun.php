<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CostingRun extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const RUN_TYPE_MANUAL = 'manual';

    public const RUN_TYPE_SCHEDULED = 'scheduled';

    public const RUN_TYPE_API = 'api';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'costing_runs';

    protected $fillable = [
        'business_id',
        'cost_model_id',
        'triggered_by',
        'run_type',
        'status',
        'notes',
    ];

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
    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * @return HasOne<CostingResult, $this>
     */
    public function result(): HasOne
    {
        return $this->hasOne(CostingResult::class);
    }
}
