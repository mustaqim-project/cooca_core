<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculation\CostModelPreviewService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CostModelMachineResource;
use App\Models\CostModel;
use App\Models\CostModelMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CostModelMachineController extends Controller
{
    /**
     * Assign a machine to a cost model.
     */
    public function store(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'string', 'exists:machines,id'],
            'hours_used' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var CostModelMachine $item */
        $item = CostModelMachine::create([
            'cost_model_id' => $costModel->id,
            'machine_id' => $validated['machine_id'],
            'hours_used' => $validated['hours_used'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $item->load('machine');

        return response()->json([
            'message' => 'Machine assigned to cost model successfully.',
            'machine_item' => new CostModelMachineResource($item),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update machine assignment.
     */
    public function update(Request $request, CostModelMachine $costModelMachine): JsonResponse
    {
        $validated = $request->validate([
            'hours_used' => ['numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $costModelMachine->update($validated);
        $costModelMachine->load('machine');

        return response()->json([
            'message' => 'Machine assignment updated successfully.',
            'machine_item' => new CostModelMachineResource($costModelMachine),
        ], Response::HTTP_OK);
    }

    /**
     * Remove machine assignment from cost model.
     */
    public function destroy(Request $request, CostModelMachine $costModelMachine): JsonResponse
    {
        $costModelMachine->delete();

        return response()->json([
            'message' => 'Machine assignment removed successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Get live combined preview of Material + Labor + Machine costs for a cost model.
     */
    public function preview(Request $request, CostModel $costModel, CostModelPreviewService $previewService): JsonResponse
    {
        $preview = $previewService->preview($costModel);

        return response()->json([
            'preview' => $preview,
        ], Response::HTTP_OK);
    }
}
