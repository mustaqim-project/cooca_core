<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CostDriverResource;
use App\Models\CostDriver;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class CostDriverController extends Controller
{
    /**
     * List all cost drivers.
     */
    public function index(Request $request): JsonResponse
    {
        $drivers = CostDriver::latest()->get();

        return response()->json([
            'data' => CostDriverResource::collection($drivers),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new cost driver.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(CostDriver::TYPES)],
            'custom_formula' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        /** @var CostDriver $driver */
        $driver = CostDriver::create($validated);

        return response()->json([
            'message' => 'Cost driver created successfully.',
            'cost_driver' => new CostDriverResource($driver),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show cost driver.
     */
    public function show(Request $request, CostDriver $costDriver): JsonResponse
    {
        return response()->json([
            'cost_driver' => new CostDriverResource($costDriver),
        ], Response::HTTP_OK);
    }

    /**
     * Update cost driver.
     */
    public function update(Request $request, CostDriver $costDriver): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'type' => ['string', Rule::in(CostDriver::TYPES)],
            'custom_formula' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $costDriver->update($validated);

        return response()->json([
            'message' => 'Cost driver updated successfully.',
            'cost_driver' => new CostDriverResource($costDriver),
        ], Response::HTTP_OK);
    }

    /**
     * Delete cost driver.
     */
    public function destroy(Request $request, CostDriver $costDriver): JsonResponse
    {
        $costDriver->delete();

        return response()->json([
            'message' => 'Cost driver deleted successfully.',
        ], Response::HTTP_OK);
    }
}
