<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Models\Business;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\ModifierOptionMaterial;
use App\Models\Product;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class ModifierService
{
    /**
     * Get all modifier groups for a business.
     *
     * @return Collection<int, ModifierGroup>
     */
    public function getGroups(Business $business): Collection
    {
        return ModifierGroup::where('business_id', $business->id)
            ->with(['options.materials.material.unit', 'products'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create a new modifier group.
     *
     * @param array<string, mixed> $data
     */
    public function createGroup(Business $business, array $data): ModifierGroup
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new DomainException('Nama grup modifier wajib diisi.');
        }

        $min = (int) ($data['min_selection'] ?? 0);
        $max = (int) ($data['max_selection'] ?? 1);
        if ($max < $min) {
            throw new DomainException('Maksimum pilihan tidak boleh lebih kecil dari minimum pilihan.');
        }

        return DB::transaction(function () use ($business, $name, $min, $max, $data) {
            $group = ModifierGroup::create([
                'business_id' => $business->id,
                'name' => $name,
                'description' => $data['description'] ?? null,
                'selection_type' => $data['selection_type'] ?? ModifierGroup::SELECTION_SINGLE,
                'min_selection' => $min,
                'max_selection' => $max,
                'is_required' => (bool) ($data['is_required'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            if (! empty($data['product_ids']) && is_array($data['product_ids'])) {
                $group->products()->sync($data['product_ids']);
            }

            return $group;
        });
    }

    /**
     * Update a modifier group.
     *
     * @param array<string, mixed> $data
     */
    public function updateGroup(ModifierGroup $group, array $data): ModifierGroup
    {
        if (isset($data['name'])) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new DomainException('Nama grup modifier tidak boleh kosong.');
            }
            $group->name = $name;
        }

        if (array_key_exists('description', $data)) {
            $group->description = $data['description'];
        }

        if (isset($data['selection_type'])) {
            $group->selection_type = $data['selection_type'];
        }

        if (isset($data['min_selection'])) {
            $group->min_selection = (int) $data['min_selection'];
        }

        if (isset($data['max_selection'])) {
            $group->max_selection = (int) $data['max_selection'];
        }

        if ($group->max_selection < $group->min_selection) {
            throw new DomainException('Maksimum pilihan tidak boleh lebih kecil dari minimum pilihan.');
        }

        if (array_key_exists('is_required', $data)) {
            $group->is_required = (bool) $data['is_required'];
        }

        if (array_key_exists('sort_order', $data)) {
            $group->sort_order = (int) $data['sort_order'];
        }

        if (array_key_exists('is_active', $data)) {
            $group->is_active = (bool) $data['is_active'];
        }

        $group->save();

        if (array_key_exists('product_ids', $data) && is_array($data['product_ids'])) {
            $group->products()->sync($data['product_ids']);
        }

        return $group;
    }

    /**
     * Delete a modifier group.
     */
    public function deleteGroup(ModifierGroup $group): bool
    {
        return DB::transaction(function () use ($group) {
            $group->products()->detach();
            foreach ($group->options as $opt) {
                $opt->materials()->delete();
                $opt->delete();
            }
            return (bool) $group->delete();
        });
    }

    /**
     * Add an option to a modifier group.
     *
     * @param array<string, mixed> $data
     */
    public function addOption(ModifierGroup $group, array $data): ModifierOption
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new DomainException('Nama opsi varian/add-on wajib diisi.');
        }

        return DB::transaction(function () use ($group, $name, $data) {
            $option = ModifierOption::create([
                'modifier_group_id' => $group->id,
                'name' => $name,
                'price_delta' => (float) ($data['price_delta'] ?? 0.0),
                'affects_material' => (bool) ($data['affects_material'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            if (! empty($data['materials']) && is_array($data['materials'])) {
                $this->mapOptionMaterials($option, $data['materials']);
            }

            return $option;
        });
    }

    /**
     * Update an existing modifier option.
     *
     * @param array<string, mixed> $data
     */
    public function updateOption(ModifierOption $option, array $data): ModifierOption
    {
        if (isset($data['name'])) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new DomainException('Nama opsi tidak boleh kosong.');
            }
            $option->name = $name;
        }

        if (array_key_exists('price_delta', $data)) {
            $option->price_delta = max(0.0, (float) $data['price_delta']);
        }

        if (array_key_exists('affects_material', $data)) {
            $option->affects_material = (bool) $data['affects_material'];
            if (! $option->affects_material) {
                $option->materials()->delete();
            }
        }

        if (array_key_exists('sort_order', $data)) {
            $option->sort_order = (int) $data['sort_order'];
        }

        if (array_key_exists('is_active', $data)) {
            $option->is_active = (bool) $data['is_active'];
        }

        $option->save();

        if ($option->affects_material && array_key_exists('materials', $data) && is_array($data['materials'])) {
            $this->mapOptionMaterials($option, $data['materials']);
        }

        return $option;
    }

    /**
     * Delete a modifier option.
     */
    public function deleteOption(ModifierOption $option): bool
    {
        return DB::transaction(function () use ($option) {
            $option->materials()->delete();
            return (bool) $option->delete();
        });
    }

    /**
     * Map material recipe to a modifier option.
     *
     * @param array<int, array<string, mixed>> $materials
     */
    public function mapOptionMaterials(ModifierOption $option, array $materials): void
    {
        $option->materials()->delete();

        foreach ($materials as $row) {
            $materialId = ! empty($row['material_id']) ? (string) $row['material_id'] : null;
            $qty = (float) ($row['quantity'] ?? 0.0);
            if (! $materialId || $qty <= 0) {
                continue;
            }

            ModifierOptionMaterial::create([
                'modifier_option_id' => $option->id,
                'material_id' => $materialId,
                'quantity' => $qty,
                'unit_id' => $row['unit_id'] ?? null,
            ]);
        }
    }

    /**
     * Validate client-submitted modifier selections against database rules and stock.
     * Prevents price tampering, enforces required/min/max rules, and checks ingredient stock.
     *
     * @param array<int|string, mixed> $selectedModifierOptionIds Array of modifier option IDs
     * @return array{options: array<int, ModifierOption>, total_price_delta: float, snapshots: array<int, array<string, mixed>>}
     * @throws DomainException
     */
    public function validateAndResolveModifiers(
        Product $product,
        array $selectedModifierOptionIds,
        ?string $locationId = null
    ): array {
        $allowedGroups = $product->modifierGroups()
            ->where('modifier_groups.is_active', true)
            ->with(['activeOptions.materials.material'])
            ->get();

        $selectedOptionIds = array_values(array_filter(array_map('strval', $selectedModifierOptionIds)));
        $resolvedOptions = [];
        $totalPriceDelta = 0.0;
        $snapshots = [];

        foreach ($allowedGroups as $group) {
            $groupOptions = $group->activeOptions;
            $selectedInThisGroup = $groupOptions->filter(fn (ModifierOption $opt) => in_array($opt->id, $selectedOptionIds, true));
            $count = $selectedInThisGroup->count();

            // 1. Enforce is_required & min_selection
            $min = $group->is_required ? max(1, $group->min_selection) : $group->min_selection;
            if ($count < $min) {
                throw new DomainException("Grup modifier '{$group->name}' wajib memilih minimal {$min} opsi.");
            }

            // 2. Enforce max_selection
            if ($group->max_selection > 0 && $count > $group->max_selection) {
                throw new DomainException("Grup modifier '{$group->name}' maksimal hanya boleh memilih {$group->max_selection} opsi.");
            }

            // 3. For each selected option: Validate stock & compute DB-driven price
            foreach ($selectedInThisGroup as $option) {
                if (! $option->isAvailableInStock($locationId)) {
                    throw new DomainException("Opsi modifier '{$option->name}' sedang habis (stok bahan baku tidak mencukupi).");
                }

                $resolvedOptions[] = $option;
                $priceDelta = (float) $option->price_delta;
                $totalPriceDelta += $priceDelta;

                // Build material snapshot for this modifier
                $matSnapshots = [];
                if ($option->affects_material) {
                    foreach ($option->materials as $optMat) {
                        $matSnapshots[] = [
                            'material_id' => $optMat->material_id,
                            'material_name' => $optMat->material?->name,
                            'quantity' => (float) $optMat->quantity,
                            'unit_id' => $optMat->unit_id,
                        ];
                    }
                }

                $snapshots[] = [
                    'modifier_group_id' => $group->id,
                    'modifier_group_name' => $group->name,
                    'modifier_option_id' => $option->id,
                    'modifier_option_name' => $option->name,
                    'unit_price' => $priceDelta,
                    'quantity' => 1.0,
                    'subtotal' => $priceDelta,
                    'material_snapshot' => $matSnapshots,
                ];
            }
        }

        return [
            'options' => $resolvedOptions,
            'total_price_delta' => $totalPriceDelta,
            'snapshots' => $snapshots,
        ];
    }
}
