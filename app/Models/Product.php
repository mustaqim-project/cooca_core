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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Product extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'category_id',
        'output_unit_id',
        'direct_material_id',
        'code',
        'name',
        'slug',
        'description',
        'image_path',
        'base_cost',
        'selling_price',
        'min_stock',
        'is_active',
        'business_type_hint',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_cost' => 'float',
            'selling_price' => 'float',
            'min_stock' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . ltrim($this->image_path, '/')) : null;
    }

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
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function outputUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'output_unit_id');
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function directMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'direct_material_id');
    }

    /**
     * @return HasMany<CostModel, $this>
     */
    public function costModels(): HasMany
    {
        return $this->hasMany(CostModel::class);
    }

    /**
     * @return HasOne<CostModel, $this>
     */
    public function activeCostModel(): HasOne
    {
        return $this->hasOne(CostModel::class)->where('is_active', true);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<PurchaseOrderItem, $this>
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
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
     * @return HasMany<PosOrderItem, $this>
     */
    public function posOrderItems(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    /**
     * Resolve constituent material deductions for a given product quantity.
     * Follows Rule 02, 03, 13, 14:
     * - Case A: Direct Material (1-to-1)
     * - Case B: Recipe / BOM (1-to-many materials with recipe quantities)
     * - Case C: No material link -> returns empty array (no deduction)
     *
     * @return array<int, array{material_id: string, material: Material|null, quantity: float, unit_id: string|null, unit_name: string|null}>
     */
    public function getMaterialDeductions(float $quantity = 1.0): array
    {
        if ($quantity <= 0) {
            return [];
        }

        // Case A: Direct Material (Rule 13)
        if ($this->direct_material_id) {
            $mat = $this->directMaterial ?? Material::find($this->direct_material_id);
            if ($mat) {
                return [
                    [
                        'material_id' => $mat->id,
                        'material' => $mat,
                        'quantity' => $quantity * 1.0,
                        'unit_id' => $mat->unit_id,
                        'unit_name' => $mat->unit?->name ?? 'Pcs',
                    ],
                ];
            }
        }

        // Case B: Recipe / BOM (Rule 14)
        $activeModel = $this->activeCostModel ?? $this->costModels()->where('is_active', true)->first();
        if ($activeModel) {
            $bomHeader = $activeModel->bomHeaders()->where('type', 'recipe')->first()
                ?? $activeModel->bomHeaders()->first();

            if ($bomHeader) {
                $items = $bomHeader->items()->whereNotNull('material_id')->with('material.unit')->get();
                if ($items->isNotEmpty()) {
                    $deductions = [];
                    foreach ($items as $item) {
                        $itemQty = (float) $item->quantity;
                        // Include waste % if specified
                        $wasteMultiplier = 1.0 + ((float) ($item->waste_percentage ?? 0) / 100.0);
                        $totalNeeded = $itemQty * $quantity * $wasteMultiplier;

                        $deductions[] = [
                            'material_id' => $item->material_id,
                            'material' => $item->material,
                            'quantity' => $totalNeeded,
                            'unit_id' => $item->unit_id ?? $item->material?->unit_id,
                            'unit_name' => $item->unit?->name ?? $item->material?->unit?->name ?? 'Unit',
                        ];
                    }
                    return $deductions;
                }
            }
        }

        // Case C: Check if a material with identical code/name exists as implicit direct material
        if ($this->code) {
            $mat = Material::where('business_id', $this->business_id)->where('code', $this->code)->first();
            if ($mat) {
                return [
                    [
                        'material_id' => $mat->id,
                        'material' => $mat,
                        'quantity' => $quantity * 1.0,
                        'unit_id' => $mat->unit_id,
                        'unit_name' => $mat->unit?->name ?? 'Pcs',
                    ],
                ];
            }
        }

        return [];
    }

    /**
     * Calculate effective stock based on Material master stock (Rule 01 & 21).
     */
    public function calculateEffectiveStock(?string $locationId = null): float
    {
        // If Direct Material
        if ($this->direct_material_id) {
            $query = InventoryStock::where('material_id', $this->direct_material_id);
            if ($locationId) {
                $query->where('location_id', $locationId);
            }
            return (float) $query->sum('quantity');
        }

        // If Recipe/BOM: compute minimum producible batches
        $deductions = $this->getMaterialDeductions(1.0);
        if (!empty($deductions)) {
            $minBatches = null;
            foreach ($deductions as $d) {
                $reqPerUnit = (float) $d['quantity'];
                if ($reqPerUnit <= 0) continue;

                $stockQuery = InventoryStock::where('material_id', $d['material_id']);
                if ($locationId) {
                    $stockQuery->where('location_id', $locationId);
                }
                $avail = (float) $stockQuery->sum('quantity');
                $batches = floor($avail / $reqPerUnit);

                if ($minBatches === null || $batches < $minBatches) {
                    $minBatches = max(0.0, (float) $batches);
                }
            }
            return $minBatches ?? 0.0;
        }

        // Fallback for legacy product stock records
        $query = $this->stocks();
        if ($locationId) {
            $query->where('location_id', $locationId);
        }
        return (float) $query->sum('quantity');
    }

    /**
     * Total stock across all locations.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->calculateEffectiveStock();
    }

    /**
     * Modifier groups assigned to this product.
     *
     * @return BelongsToMany<ModifierGroup, $this>
     */
    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups', 'product_id', 'modifier_group_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * Get active modifier groups with options and real-time stock availability.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableModifierGroupsWithStock(?string $locationId = null): array
    {
        return $this->modifierGroups()
            ->where('modifier_groups.is_active', true)
            ->with(['activeOptions.materials'])
            ->get()
            ->map(function (ModifierGroup $group) use ($locationId): array {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'selection_type' => $group->selection_type,
                    'min_selection' => (int) $group->min_selection,
                    'max_selection' => (int) $group->max_selection,
                    'is_required' => (bool) $group->is_required,
                    'options' => $group->activeOptions->map(function (ModifierOption $opt) use ($locationId): array {
                        return [
                            'id' => $opt->id,
                            'name' => $opt->name,
                            'price_delta' => (float) $opt->price_delta,
                            'affects_material' => (bool) $opt->affects_material,
                            'is_available' => $opt->isAvailableInStock($locationId),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}

