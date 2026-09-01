<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CostPoolResource;
use App\Models\CostPool;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CostPoolController extends Controller
{
    /**
     * List all cost pools.
     */
    public function index(Request $request): JsonResponse
    {
        $pools = CostPool::with('overheads')->latest()->get();

        return response()->json([
            'data' => CostPoolResource::collection($pools),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new cost pool.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manual_override_amount' => ['nullable', 'numeric', 'gte:0'],
            'description' => ['nullable', 'string'],
            'overhead_ids' => ['nullable', 'array'],
            'overhead_ids.*' => ['string', 'exists:overheads,id'],
        ]);

        /** @var CostPool $pool */
        $pool = CostPool::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'manual_override_amount' => $validated['manual_override_amount'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['overhead_ids'])) {
            $pool->overheads()->sync($validated['overhead_ids']);
        }

        $pool->load('overheads');

        return response()->json([
            'message' => 'Cost pool created successfully.',
            'cost_pool' => new CostPoolResource($pool),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show cost pool details.
     */
    public function show(Request $request, CostPool $costPool): JsonResponse
    {
        $costPool->load('overheads');

        return response()->json([
            'cost_pool' => new CostPoolResource($costPool),
        ], Response::HTTP_OK);
    }

    /**
     * Update cost pool.
     */
    public function update(Request $request, CostPool $costPool): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'manual_override_amount' => ['nullable', 'numeric', 'gte:0'],
            'description' => ['nullable', 'string'],
            'overhead_ids' => ['nullable', 'array'],
            'overhead_ids.*' => ['string', 'exists:overheads,id'],
        ]);

        $costPool->update([
            'name' => $validated['name'] ?? $costPool->name,
            'manual_override_amount' => array_key_exists('manual_override_amount', $validated) ? $validated['manual_override_amount'] : $costPool->manual_override_amount,
            'description' => $validated['description'] ?? $costPool->description,
        ]);

        if (array_key_exists('overhead_ids', $validated)) {
            $costPool->overheads()->sync($validated['overhead_ids'] ?? []);
        }

        $costPool->load('overheads');

        return response()->json([
            'message' => 'Cost pool updated successfully.',
            'cost_pool' => new CostPoolResource($costPool),
        ], Response::HTTP_OK);
    }

    /**
     * Delete cost pool.
     */
    public function destroy(Request $request, CostPool $costPool): JsonResponse
    {
        $costPool->delete();

        return response()->json([
            'message' => 'Cost pool deleted successfully.',
        ], Response::HTTP_OK);
    }
}
