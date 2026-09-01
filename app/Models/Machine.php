<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'code',
        'purchase_price',
        'residual_value',
        'useful_life_hours',
        'maintenance_cost_per_hour',
        'electricity_cost_per_hour',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'float',
            'residual_value' => 'float',
            'useful_life_hours' => 'float',
            'maintenance_cost_per_hour' => 'float',
            'electricity_cost_per_hour' => 'float',
        ];
    }

    /**
     * @return HasMany<CostModelMachine, $this>
     */
    public function costModelMachines(): HasMany
    {
        return $this->hasMany(CostModelMachine::class);
    }
}
