<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LocationController extends Controller
{
    /**
     * List all locations for active business.
     */
    public function index(Request $request): JsonResponse
    {
        $locations = Location::latest()->get();

        return response()->json([
            'data' => LocationResource::collection($locations),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new location.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        /** @var Location $location */
        $location = Location::create($validated);

        return response()->json([
            'message' => 'Location created successfully.',
            'location' => new LocationResource($location),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show location details.
     */
    public function show(Request $request, Location $location): JsonResponse
    {
        return response()->json([
            'location' => new LocationResource($location),
        ], Response::HTTP_OK);
    }

    /**
     * Update location.
     */
    public function update(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_primary' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Location updated successfully.',
            'location' => new LocationResource($location),
        ], Response::HTTP_OK);
    }

    /**
     * Delete location.
     */
    public function destroy(Request $request, Location $location): JsonResponse
    {
        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully.',
        ], Response::HTTP_OK);
    }
}
