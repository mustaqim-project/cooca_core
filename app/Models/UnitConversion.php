<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasUuid;
use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitConversion extends Model
{
    use Auditable, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'from_unit_id',
        'to_unit_id',
        'factor',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'factor' => 'float',
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
     * Scope query to available conversions (system default + tenant).
     *
     * @param  Builder<UnitConversion>  $query
     * @return Builder<UnitConversion>
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
