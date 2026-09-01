<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Material\UnitConversionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Support\Context;
use App\Support\RoundingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class UnitController extends Controller
{
    /**
     * List all available units (system defaults + current business).
     */
    public function index(Request $request): JsonResponse
    {
        $units = Unit::available()->orderBy('category')->orderBy('name')->get();

        return response()->json([
            'units' => UnitResource::collection($units),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new unit for the active business.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', Rule::in(Unit::CATEGORIES)],
            'is_base' => ['boolean'],
            'default_precision' => ['integer', 'min:0', 'max:6'],
        ]);

        if (! empty($validated['is_base'])) {
            // Ensure only 1 base unit per category in this business
            Unit::where('business_id', $business->id)
                ->where('category', $validated['category'])
                ->update(['is_base' => false]);
        }

        /** @var Unit $unit */
        $unit = Unit::create([
            'business_id' => $business->id,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'category' => $validated['category'],
            'is_base' => $validated['is_base'] ?? false,
            'default_precision' => $validated['default_precision'] ?? 2,
        ]);

        return response()->json([
            'message' => 'Unit created successfully.',
            'unit' => new UnitResource($unit),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show unit details.
     */
    public function show(Request $request, Unit $unit): JsonResponse
    {
        return response()->json([
            'unit' => new UnitResource($unit),
        ], Response::HTTP_OK);
    }

    /**
     * Update tenant-specific unit.
     */
    public function update(Request $request, Unit $unit): JsonResponse
    {
        if ($unit->business_id === null) {
            return response()->json([
                'message' => 'Cannot modify system default units.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'code' => ['string', 'max:30'],
            'name' => ['string', 'max:100'],
            'category' => ['string', Rule::in(Unit::CATEGORIES)],
            'is_base' => ['boolean'],
            'default_precision' => ['integer', 'min:0', 'max:6'],
        ]);

        $unit->update($validated);

        return response()->json([
            'message' => 'Unit updated successfully.',
            'unit' => new UnitResource($unit),
        ], Response::HTTP_OK);
    }

    /**
     * Delete tenant-specific unit.
     */
    public function destroy(Request $request, Unit $unit): JsonResponse
    {
        if ($unit->business_id === null) {
            return response()->json([
                'message' => 'Cannot delete system default units.',
            ], Response::HTTP_FORBIDDEN);
        }

        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Convert quantity between units via calculation engine.
     */
    public function convert(Request $request, UnitConversionService $service): JsonResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'numeric'],
            'from_unit_id' => ['required', 'string'],
            'to_unit_id' => ['required', 'string'],
        ]);

        /** @var Unit $fromUnit */
        $fromUnit = Unit::available()->where('id', $validated['from_unit_id'])->firstOrFail();
        /** @var Unit $toUnit */
        $toUnit = Unit::available()->where('id', $validated['to_unit_id'])->firstOrFail();

        $convertedQty = $service->convert((float) $validated['qty'], $fromUnit, $toUnit);
        $roundedQty = RoundingService::roundQty($convertedQty, $toUnit);

        return response()->json([
            'original_qty' => (float) $validated['qty'],
            'from_unit' => new UnitResource($fromUnit),
            'to_unit' => new UnitResource($toUnit),
            'converted_qty' => $convertedQty,
            'rounded_qty' => $roundedQty,
            'formatted_qty' => RoundingService::formatQty($convertedQty, $toUnit),
        ], Response::HTTP_OK);
    }
}
