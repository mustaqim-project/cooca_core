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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'category_id',
        'unit_id',
        'supplier_id',
        'code',
        'name',
        'slug',
        'description',
        'discontinued_at',
    ];

    /**
     * Resolve route binding by either UUID id or slug.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discontinued_at' => 'datetime',
        ];
    }

    /**
     * Determine if material is marked as discontinued.
     */
    public function isDiscontinued(): bool
    {
        return $this->discontinued_at !== null && $this->discontinued_at->isPast();
    }

    /**
     * @return BelongsTo<MaterialCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return HasMany<MaterialPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(MaterialPrice::class)->orderByDesc('effective_date');
    }

    /**
     * Get the latest active price record.
     *
     * @return HasOne<MaterialPrice, $this>
     */
    public function latestPrice(): HasOne
    {
        return $this->hasOne(MaterialPrice::class)->latestOfMany('effective_date');
    }

    /**
     * @return HasMany<InventoryStock, $this>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Total stock across all locations.
     */
    public function getTotalStockAttribute(): float
    {
        return (float) $this->stocks()->sum('quantity');
    }
}
