<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialPrice extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'material_id',
        'supplier_id',
        'currency_id',
        'purchase_price',
        'shipping_cost',
        'handling_cost',
        'import_cost',
        'discount_amount',
        'purchase_unit_id',
        'yield_percentage',
        'waste_percentage',
        'effective_date',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'float',
            'shipping_cost' => 'float',
            'handling_cost' => 'float',
            'import_cost' => 'float',
            'discount_amount' => 'float',
            'yield_percentage' => 'float',
            'waste_percentage' => 'float',
            'effective_date' => 'date',
        ];
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
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }
}
