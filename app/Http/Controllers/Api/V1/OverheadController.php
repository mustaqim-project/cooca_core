<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OverheadResource;
use App\Models\Overhead;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class OverheadController extends Controller
{
    /**
     * List all overheads.
     */
    public function index(Request $request): JsonResponse
    {
        $overheads = Overhead::with('currency')->latest()->get();

        return response()->json([
            'data' => OverheadResource::collection($overheads),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new overhead.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gte:0'],
            'period' => ['nullable', 'string', Rule::in([Overhead::PERIOD_MONTHLY, Overhead::PERIOD_YEARLY, Overhead::PERIOD_ONE_TIME])],
            'behavior' => ['nullable', 'string', Rule::in([Overhead::BEHAVIOR_FIXED, Overhead::BEHAVIOR_VARIABLE])],
            'currency_id' => ['nullable', 'string', 'exists:currencies,id'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var Overhead $overhead */
        $overhead = Overhead::create($validated);
        $overhead->load('currency');

        return response()->json([
            'message' => 'Overhead created successfully.',
            'overhead' => new OverheadResource($overhead),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show overhead details.
     */
    public function show(Request $request, Overhead $overhead): JsonResponse
    {
        $overhead->load('currency');

        return response()->json([
            'overhead' => new OverheadResource($overhead),
        ], Response::HTTP_OK);
    }

    /**
     * Update overhead.
     */
    public function update(Request $request, Overhead $overhead): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'amount' => ['numeric', 'gte:0'],
            'period' => ['string', Rule::in([Overhead::PERIOD_MONTHLY, Overhead::PERIOD_YEARLY, Overhead::PERIOD_ONE_TIME])],
            'behavior' => ['string', Rule::in([Overhead::BEHAVIOR_FIXED, Overhead::BEHAVIOR_VARIABLE])],
            'currency_id' => ['nullable', 'string', 'exists:currencies,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $overhead->update($validated);
        $overhead->load('currency');

        return response()->json([
            'message' => 'Overhead updated successfully.',
            'overhead' => new OverheadResource($overhead),
        ], Response::HTTP_OK);
    }

    /**
     * Delete overhead.
     */
    public function destroy(Request $request, Overhead $overhead): JsonResponse
    {
        $overhead->delete();

        return response()->json([
            'message' => 'Overhead deleted successfully.',
        ], Response::HTTP_OK);
    }
}
