<?php

declare(strict_types=1);

namespace App\Domain\Product;

use App\Domain\Material\MaterialCostService;
use App\Models\BomHeader;
use App\Models\BomItem;
use RuntimeException;

final class BomExplosionService
{
    public const MAX_DEPTH = 10;

    public function __construct(private readonly MaterialCostService $materialCostService = new MaterialCostService) {}

    /**
     * Explode and calculate complete cost of a BOM header recursively.
     *
     * @return array{
     *     bom_header_id: string,
     *     name: ?string,
     *     type: string,
     *     total_material_cost: float,
     *     items: array<int, mixed>
     * }
     */
    public function explode(BomHeader $bomHeader, int $depth = 1): array
    {
        if ($depth > self::MAX_DEPTH) {
            throw new RuntimeException('Maximum BOM hierarchy depth of {self::MAX_DEPTH} exceeded.');
        }

        $items = BomItem::where('bom_header_id', $bomHeader->id)
            ->with(['material.latestPrice.purchaseUnit', 'material.latestPrice.currency', 'unit', 'subBomHeader'])
            ->orderBy('sort_order')
            ->get();

        $totalCost = 0.0;
        $explodedItems = [];

        foreach ($items as $item) {
            $itemWasteFactor = 1.0 + ((float) $item->waste_percentage / 100.0);
            $effectiveQuantity = (float) $item->quantity * $itemWasteFactor;

            if ($item->isSubAssembly()) {
                /** @var BomHeader $subBom */
                $subBom = $item->subBomHeader;
                $subResult = $this->explode($subBom, $depth + 1);
                $subTotalUnitCost = (float) $subResult['total_material_cost'];
                $itemTotalCost = $subTotalUnitCost * $effectiveQuantity;

                $totalCost += $itemTotalCost;

                $explodedItems[] = [
                    'item_id' => $item->id,
                    'is_sub_assembly' => true,
                    'sub_bom_header_id' => $subBom->id,
                    'sub_bom_name' => $subBom->name,
                    'depth' => $depth,
                    'quantity' => (float) $item->quantity,
                    'waste_percentage' => (float) $item->waste_percentage,
                    'effective_quantity' => $effectiveQuantity,
                    'unit' => [
                        'id' => $item->unit?->id,
                        'code' => $item->unit?->code,
                        'name' => $item->unit?->name,
                    ],
                    'unit_cost' => $subTotalUnitCost,
                    'total_cost' => $itemTotalCost,
                    'sub_bom' => $subResult,
                ];
            } else {
                $material = $item->material;
                $latestPrice = $material?->latestPrice;

                $unitCost = 0.0;
                if ($latestPrice !== null && $item->unit !== null) {
                    $unitCost = $this->materialCostService->calculateEffectiveUnitCost($latestPrice, $item->unit);
                }

                $itemTotalCost = $unitCost * $effectiveQuantity;
                $totalCost += $itemTotalCost;

                $explodedItems[] = [
                    'item_id' => $item->id,
                    'is_sub_assembly' => false,
                    'material' => [
                        'id' => $material?->id,
                        'name' => $material?->name,
                        'code' => $material?->code,
                        'slug' => $material?->slug,
                    ],
                    'depth' => $depth,
                    'quantity' => (float) $item->quantity,
                    'waste_percentage' => (float) $item->waste_percentage,
                    'effective_quantity' => $effectiveQuantity,
                    'unit' => [
                        'id' => $item->unit?->id,
                        'code' => $item->unit?->code,
                        'name' => $item->unit?->name,
                    ],
                    'unit_cost' => $unitCost,
                    'total_cost' => $itemTotalCost,
                ];
            }
        }

        return [
            'bom_header_id' => $bomHeader->id,
            'name' => $bomHeader->name,
            'type' => $bomHeader->type,
            'depth' => $depth,
            'total_material_cost' => $totalCost,
            'items' => $explodedItems,
        ];
    }
}
