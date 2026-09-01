<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitConversionResource;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class UnitConversionController extends Controller
{
    /**
     * List all unit conversions available for current business.
     */
    public function index(Request $request): JsonResponse
    {
        $conversions = UnitConversion::available()
            ->with(['fromUnit', 'toUnit'])
            ->get();

        return response()->json([
            'conversions' => UnitConversionResource::collection($conversions),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new custom conversion factor.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'from_unit_id' => ['required', 'string', 'exists:units,id'],
            'to_unit_id' => ['required', 'string', 'exists:units,id', 'different:from_unit_id'],
            'factor' => ['required', 'numeric', 'gt:0'],
        ]);

        /** @var Unit $fromUnit */
        $fromUnit = Unit::available()->findOrFail($validated['from_unit_id']);
        /** @var Unit $toUnit */
        $toUnit = Unit::available()->findOrFail($validated['to_unit_id']);

        // Check category compatibility unless one of them is custom
        if ($fromUnit->category !== $toUnit->category && $fromUnit->category !== Unit::CATEGORY_CUSTOM && $toUnit->category !== Unit::CATEGORY_CUSTOM) {
            return response()->json([
                'message' => "Cannot create conversion between different categories [{$fromUnit->category}] and [{$toUnit->category}] without a custom bridge.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var UnitConversion $conversion */
        $conversion = UnitConversion::updateOrCreate(
            [
                'business_id' => $business->id,
                'from_unit_id' => $fromUnit->id,
                'to_unit_id' => $toUnit->id,
            ],
            [
                'factor' => $validated['factor'],
            ]
        );

        $conversion->load(['fromUnit', 'toUnit']);

        return response()->json([
            'message' => 'Unit conversion factor saved successfully.',
            'conversion' => new UnitConversionResource($conversion),
        ], Response::HTTP_CREATED);
    }

    /**
     * Delete a tenant-specific conversion.
     */
    public function destroy(Request $request, UnitConversion $unitConversion): JsonResponse
    {
        if ($unitConversion->business_id === null) {
            return response()->json([
                'message' => 'Cannot delete system default conversion factor.',
            ], Response::HTTP_FORBIDDEN);
        }

        $unitConversion->delete();

        return response()->json([
            'message' => 'Unit conversion factor deleted successfully.',
        ], Response::HTTP_OK);
    }
}
