<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierOption extends Model
{
    use Auditable, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'modifier_group_id',
        'name',
        'price_delta',
        'affects_material',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_delta' => 'float',
            'affects_material' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ModifierGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    /**
     * @return HasMany<ModifierOptionMaterial, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(ModifierOptionMaterial::class, 'modifier_option_id');
    }

    /**
     * Check if this option has enough stock of its associated materials.
     */
    public function isAvailableInStock(?string $locationId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->affects_material) {
            return true;
        }

        $recipeItems = $this->materials;
        if ($recipeItems->isEmpty()) {
            return true;
        }

        foreach ($recipeItems as $item) {
            $required = (float) $item->quantity;
            if ($required <= 0) {
                continue;
            }

            $query = InventoryStock::where('material_id', $item->material_id);
            if ($locationId) {
                $query->where('location_id', $locationId);
            }

            $available = (float) $query->sum('quantity');
            if ($available < $required) {
                return false;
            }
        }

        return true;
    }
}
