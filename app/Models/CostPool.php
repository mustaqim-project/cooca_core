<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostPool extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'manual_override_amount',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'manual_override_amount' => 'float',
        ];
    }

    /**
     * Calculate total monthly pool amount.
     */
    public function totalAmount(): float
    {
        if ($this->manual_override_amount !== null) {
            return (float) $this->manual_override_amount;
        }

        return (float) $this->overheads->sum(fn (Overhead $o) => $o->monthlyAmount());
    }

    /**
     * @return BelongsToMany<Overhead, $this>
     */
    public function overheads(): BelongsToMany
    {
        return $this->belongsToMany(Overhead::class, 'cost_pool_overheads')
            ->using(CostPoolOverhead::class);
    }

    /**
     * @return HasMany<AllocationRule, $this>
     */
    public function allocationRules(): HasMany
    {
        return $this->hasMany(AllocationRule::class);
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
