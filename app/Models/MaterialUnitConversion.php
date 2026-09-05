<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialUnitConversion extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'material_id',
        'supplier_id',
        'from_unit_id',
        'to_unit_id',
        'factor',
        'is_default',
        'effective_from',
        'effective_until',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'factor' => 'float',
            'is_default' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }

    /**
     * Scope query to active tenant plus defaults.
     *
     * @param  Builder<MaterialUnitConversion>  $query
     * @return Builder<MaterialUnitConversion>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('business_id');

            if (Context::hasBusiness()) {
                $q->orWhere('business_id', Context::business()?->id);
            }
        });
    }
}
