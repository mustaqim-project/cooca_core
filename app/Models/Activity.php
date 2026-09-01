<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'cost_pool_id',
        'name',
        'slug',
        'cost_driver_name',
        'total_activity_capacity',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_activity_capacity' => 'float',
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
     * @return HasMany<CostModelActivity, $this>
     */
    public function costModelActivities(): HasMany
    {
        return $this->hasMany(CostModelActivity::class);
    }
}
