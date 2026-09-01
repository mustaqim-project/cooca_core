<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Product\BomCircularValidator;
use App\Domain\Product\BomExplosionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BomHeaderResource;
use App\Http\Resources\BomItemResource;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\CostModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class BomController extends Controller
{
    /**
     * Create or retrieve the BOM header for a cost model.
     */
    public function createOrGetHeader(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:recipe,bom'],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var BomHeader $header */
        $header = BomHeader::firstOrCreate(
            ['cost_model_id' => $costModel->id],
            [
                'type' => $validated['type'] ?? BomHeader::TYPE_RECIPE,
                'level' => 1,
                'name' => $validated['name'] ?? $costModel->name,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $header->load(['items.material.latestPrice', 'items.unit', 'items.subBomHeader']);

        return response()->json([
            'bom_header' => new BomHeaderResource($header),
        ], Response::HTTP_OK);
    }

    /**
     * Add an item (material or sub-BOM) to a BOM header.
     */
    public function addItem(Request $request, BomHeader $bomHeader, BomCircularValidator $circularValidator): JsonResponse
    {
        $validated = $request->validate([
            'material_id' => ['nullable', 'string', 'exists:materials,id'],
            'sub_bom_header_id' => ['nullable', 'string', 'exists:bom_headers,id'],
            'unit_id' => ['required', 'string', 'exists:units,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'waste_percentage' => ['nullable', 'numeric', 'gte:0', 'lt:100'],
            'sort_order' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $hasMaterial = ! empty($validated['material_id']);
        $hasSubBom = ! empty($validated['sub_bom_header_id']);

        // XOR validation: exactly one must be provided
        if (($hasMaterial && $hasSubBom) || (! $hasMaterial && ! $hasSubBom)) {
            return response()->json([
                'message' => 'A BOM item must reference either a material_id or a sub_bom_header_id (not both, not neither).',
                'error_code' => 'BOM_ITEM_XOR_CONSTRAINT_FAILED',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Circular check if sub-BOM is specified
        if ($hasSubBom) {
            /** @var BomHeader $subBom */
            $subBom = BomHeader::findOrFail($validated['sub_bom_header_id']);

            try {
                $circularValidator->validate($bomHeader, $subBom);
            } catch (InvalidArgumentException $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_code' => 'CIRCULAR_BOM_REFERENCE_DETECTED',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        /** @var BomItem $item */
        $item = BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $validated['material_id'] ?? null,
            'sub_bom_header_id' => $validated['sub_bom_header_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'quantity' => $validated['quantity'],
            'waste_percentage' => $validated['waste_percentage'] ?? 0,
            'sort_order' => $validated['sort_order'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        $item->load(['material', 'subBomHeader', 'unit']);

        return response()->json([
            'message' => 'BOM item added successfully.',
            'item' => new BomItemResource($item),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update a BOM item.
     */
    public function updateItem(Request $request, BomItem $bomItem): JsonResponse
    {
        $validated = $request->validate([
            'unit_id' => ['string', 'exists:units,id'],
            'quantity' => ['numeric', 'gt:0'],
            'waste_percentage' => ['numeric', 'gte:0', 'lt:100'],
            'sort_order' => ['integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $bomItem->update($validated);
        $bomItem->load(['material', 'subBomHeader', 'unit']);

        return response()->json([
            'message' => 'BOM item updated successfully.',
            'item' => new BomItemResource($item = $bomItem),
        ], Response::HTTP_OK);
    }

    /**
     * Delete a BOM item.
     */
    public function deleteItem(Request $request, BomItem $bomItem): JsonResponse
    {
        $bomItem->delete();

        return response()->json([
            'message' => 'BOM item deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Explode and roll up entire BOM tree recursively.
     */
    public function explode(Request $request, BomHeader $bomHeader, BomExplosionService $explosionService): JsonResponse
    {
        $result = $explosionService->explode($bomHeader);

        return response()->json([
            'bom_explosion' => $result,
        ], Response::HTTP_OK);
    }
}
