<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Overhead\AllocationEngineService;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllocationRuleResource;
use App\Models\AllocationRule;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AllocationRuleController extends Controller
{
    /**
     * List all allocation rules.
     */
    public function index(Request $request): JsonResponse
    {
        $rules = AllocationRule::with(['costPool.overheads', 'costDriver', 'costModel', 'targetCategory'])
            ->latest()
            ->get();

        return response()->json([
            'data' => AllocationRuleResource::collection($rules),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new allocation rule.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'cost_pool_id' => ['required', 'string', 'exists:cost_pools,id'],
            'cost_driver_id' => ['required', 'string', 'exists:cost_drivers,id'],
            'cost_model_id' => ['nullable', 'string', 'exists:cost_models,id'],
            'target_category_id' => ['nullable', 'string', 'exists:product_categories,id'],
            'total_driver_capacity' => ['nullable', 'numeric', 'gt:0'],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var AllocationRule $rule */
        $rule = AllocationRule::create($validated);
        $rule->load(['costPool.overheads', 'costDriver', 'costModel', 'targetCategory']);

        return response()->json([
            'message' => 'Allocation rule created successfully.',
            'allocation_rule' => new AllocationRuleResource($rule),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show allocation rule.
     */
    public function show(Request $request, AllocationRule $allocationRule): JsonResponse
    {
        $allocationRule->load(['costPool.overheads', 'costDriver', 'costModel', 'targetCategory']);

        return response()->json([
            'allocation_rule' => new AllocationRuleResource($allocationRule),
        ], Response::HTTP_OK);
    }

    /**
     * Update allocation rule.
     */
    public function update(Request $request, AllocationRule $allocationRule): JsonResponse
    {
        $validated = $request->validate([
            'cost_pool_id' => ['string', 'exists:cost_pools,id'],
            'cost_driver_id' => ['string', 'exists:cost_drivers,id'],
            'cost_model_id' => ['nullable', 'string', 'exists:cost_models,id'],
            'target_category_id' => ['nullable', 'string', 'exists:product_categories,id'],
            'total_driver_capacity' => ['nullable', 'numeric', 'gt:0'],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $allocationRule->update($validated);
        $allocationRule->load(['costPool.overheads', 'costDriver', 'costModel', 'targetCategory']);

        return response()->json([
            'message' => 'Allocation rule updated successfully.',
            'allocation_rule' => new AllocationRuleResource($allocationRule),
        ], Response::HTTP_OK);
    }

    /**
     * Delete allocation rule.
     */
    public function destroy(Request $request, AllocationRule $allocationRule): JsonResponse
    {
        $allocationRule->delete();

        return response()->json([
            'message' => 'Allocation rule deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Preview calculation of an allocation rule.
     */
    public function preview(Request $request, AllocationRule $allocationRule, AllocationEngineService $engine): JsonResponse
    {
        $validated = $request->validate([
            'product_metric' => ['required', 'numeric', 'gte:0'],
            'override_total_capacity' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $allocatedAmount = $engine->calculateForRule(
            $allocationRule,
            (float) $validated['product_metric'],
            isset($validated['override_total_capacity']) ? (float) $validated['override_total_capacity'] : null
        );

        return response()->json([
            'allocation_rule' => new AllocationRuleResource($allocationRule),
            'product_metric' => (float) $validated['product_metric'],
            'allocated_overhead_amount' => $allocatedAmount,
        ], Response::HTTP_OK);
    }
}
