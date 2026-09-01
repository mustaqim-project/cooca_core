<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Overhead\AbcCostingService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\CostModelActivityResource;
use App\Models\Activity;
use App\Models\CostModel;
use App\Models\CostModelActivity;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AbcActivityController extends Controller
{
    /**
     * List all ABC activities.
     */
    public function index(Request $request): JsonResponse
    {
        $activities = Activity::with('costPool')->latest()->get();

        return response()->json([
            'data' => ActivityResource::collection($activities),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new ABC activity.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'cost_pool_id' => ['required', 'string', 'exists:cost_pools,id'],
            'name' => ['required', 'string', 'max:255'],
            'cost_driver_name' => ['required', 'string', 'max:255'],
            'total_activity_capacity' => ['required', 'numeric', 'gt:0'],
        ]);

        /** @var Activity $activity */
        $activity = Activity::create($validated);
        $activity->load('costPool');

        return response()->json([
            'message' => 'Activity created successfully.',
            'activity' => new ActivityResource($activity),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show activity details and rate.
     */
    public function show(Request $request, Activity $activity, AbcCostingService $service): JsonResponse
    {
        $activity->load('costPool');
        $rate = $service->activityRate($activity);

        return response()->json([
            'activity' => new ActivityResource($activity),
            'cost_driver_rate' => $rate,
        ], Response::HTTP_OK);
    }

    /**
     * Update activity.
     */
    public function update(Request $request, Activity $activity): JsonResponse
    {
        $validated = $request->validate([
            'cost_pool_id' => ['string', 'exists:cost_pools,id'],
            'name' => ['string', 'max:255'],
            'cost_driver_name' => ['string', 'max:255'],
            'total_activity_capacity' => ['numeric', 'gt:0'],
        ]);

        $activity->update($validated);
        $activity->load('costPool');

        return response()->json([
            'message' => 'Activity updated successfully.',
            'activity' => new ActivityResource($activity),
        ], Response::HTTP_OK);
    }

    /**
     * Delete activity.
     */
    public function destroy(Request $request, Activity $activity): JsonResponse
    {
        $activity->delete();

        return response()->json([
            'message' => 'Activity deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Assign activity to cost model.
     */
    public function assignToCostModel(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'activity_id' => ['required', 'string', 'exists:activities,id'],
            'consumed_quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var CostModelActivity $item */
        $item = CostModelActivity::create([
            'cost_model_id' => $costModel->id,
            'activity_id' => $validated['activity_id'],
            'consumed_quantity' => $validated['consumed_quantity'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $item->load('activity.costPool');

        return response()->json([
            'message' => 'Activity assigned to cost model successfully.',
            'item' => new CostModelActivityResource($item),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update cost model activity consumption.
     */
    public function updateCostModelActivity(Request $request, CostModelActivity $costModelActivity): JsonResponse
    {
        $validated = $request->validate([
            'consumed_quantity' => ['numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $costModelActivity->update($validated);
        $costModelActivity->load('activity.costPool');

        return response()->json([
            'message' => 'Activity consumption updated successfully.',
            'item' => new CostModelActivityResource($costModelActivity),
        ], Response::HTTP_OK);
    }

    /**
     * Delete activity consumption from cost model.
     */
    public function removeCostModelActivity(Request $request, CostModelActivity $costModelActivity): JsonResponse
    {
        $costModelActivity->delete();

        return response()->json([
            'message' => 'Activity assignment removed successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Calculate ABC overhead total and breakdown for a cost model.
     */
    public function calculateForCostModel(Request $request, CostModel $costModel, AbcCostingService $service): JsonResponse
    {
        $result = $service->calculateForCostModel($costModel);

        return response()->json([
            'abc_calculation' => $result,
        ], Response::HTTP_OK);
    }
}
