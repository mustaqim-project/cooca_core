<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialUnitConversionResource;
use App\Models\Material;
use App\Models\MaterialUnitConversion;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class MaterialUnitConversionController extends Controller
{
    /**
     * List material-specific conversion mappings for the current business.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $rows = MaterialUnitConversion::where('business_id', $business->id)
            ->with(['material', 'supplier', 'fromUnit', 'toUnit'])
            ->orderBy('effective_from')
            ->get();

        return response()->json([
            'conversions' => MaterialUnitConversionResource::collection($rows),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new material-specific conversion mapping.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'material_id' => ['required', 'string', 'exists:materials,id'],
            'supplier_id' => ['nullable', 'string', 'exists:suppliers,id'],
            'from_unit_id' => ['required', 'string', 'exists:units,id'],
            'to_unit_id' => ['required', 'string', 'exists:units,id', 'different:from_unit_id'],
            'factor' => ['required', 'numeric', 'gt:0'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_default' => ['boolean'],
        ]);

        $material = Material::where('business_id', $business->id)->findOrFail($validated['material_id']);
        $fromUnit = Unit::available()->findOrFail($validated['from_unit_id']);
        $toUnit = Unit::available()->findOrFail($validated['to_unit_id']);

        if ($fromUnit->category !== $toUnit->category && $fromUnit->category !== Unit::CATEGORY_CUSTOM && $toUnit->category !== Unit::CATEGORY_CUSTOM) {
            return response()->json([
                'message' => "Cannot create material conversion between different categories [{$fromUnit->category}] and [{$toUnit->category}] without a custom bridge.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! empty($validated['supplier_id'])) {
            $supplier = Supplier::where('business_id', $business->id)->findOrFail($validated['supplier_id']);
            $validated['supplier_id'] = $supplier->id;
        }

        $conversion = MaterialUnitConversion::updateOrCreate(
            [
                'business_id' => $business->id,
                'material_id' => $material->id,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'from_unit_id' => $fromUnit->id,
                'to_unit_id' => $toUnit->id,
            ],
            [
                'factor' => $validated['factor'],
                'is_default' => $validated['is_default'] ?? false,
                'effective_from' => $validated['effective_from'],
                'effective_until' => $validated['effective_until'] ?? null,
            ]
        );

        $conversion->load(['material', 'supplier', 'fromUnit', 'toUnit']);

        return response()->json([
            'message' => 'Material conversion saved successfully.',
            'conversion' => new MaterialUnitConversionResource($conversion),
        ], Response::HTTP_CREATED);
    }

    /**
     * Delete a material-specific conversion.
     */
    public function destroy(MaterialUnitConversion $materialUnitConversion): JsonResponse
    {
        if ($materialUnitConversion->business_id !== Context::requireBusiness()->id) {
            return response()->json([
                'message' => 'Conversion does not belong to the active business.',
            ], Response::HTTP_FORBIDDEN);
        }

        $materialUnitConversion->delete();

        return response()->json([
            'message' => 'Material conversion deleted successfully.',
        ], Response::HTTP_OK);
    }
}
