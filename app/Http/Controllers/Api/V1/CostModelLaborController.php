<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CostModelLaborResource;
use App\Models\CostModel;
use App\Models\CostModelLabor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CostModelLaborController extends Controller
{
    /**
     * Add a labor assignment to a cost model.
     */
    public function store(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'labor_rate_id' => ['required', 'string', 'exists:labor_rates,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'regular_hours' => ['nullable', 'numeric', 'gte:0'],
            'overtime_hours' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var CostModelLabor $item */
        $item = CostModelLabor::create([
            'cost_model_id' => $costModel->id,
            'labor_rate_id' => $validated['labor_rate_id'],
            'quantity' => $validated['quantity'],
            'regular_hours' => $validated['regular_hours'] ?? $validated['quantity'],
            'overtime_hours' => $validated['overtime_hours'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        $item->load('laborRate');

        return response()->json([
            'message' => 'Labor item assigned to cost model successfully.',
            'labor' => new CostModelLaborResource($item),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update labor assignment.
     */
    public function update(Request $request, CostModelLabor $costModelLabor): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['numeric', 'gt:0'],
            'regular_hours' => ['nullable', 'numeric', 'gte:0'],
            'overtime_hours' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $costModelLabor->update($validated);
        $costModelLabor->load('laborRate');

        return response()->json([
            'message' => 'Labor item updated successfully.',
            'labor' => new CostModelLaborResource($costModelLabor),
        ], Response::HTTP_OK);
    }

    /**
     * Remove labor assignment from cost model.
     */
    public function destroy(Request $request, CostModelLabor $costModelLabor): JsonResponse
    {
        $costModelLabor->delete();

        return response()->json([
            'message' => 'Labor item removed from cost model successfully.',
        ], Response::HTTP_OK);
    }
}
