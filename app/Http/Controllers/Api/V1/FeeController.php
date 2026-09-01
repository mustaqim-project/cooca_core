<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeeResource;
use App\Models\Fee;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class FeeController extends Controller
{
    /**
     * List all fees.
     */
    public function index(Request $request): JsonResponse
    {
        $fees = Fee::latest()->get();

        return response()->json([
            'data' => FeeResource::collection($fees),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new fee.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in([Fee::TYPE_COST, Fee::TYPE_PRICE_DEDUCTION])],
            'fee_type' => ['nullable', 'string', Rule::in([Fee::FEE_TYPE_PERCENTAGE, Fee::FEE_TYPE_FIXED])],
            'fee_value' => ['required', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var Fee $fee */
        $fee = Fee::create($validated);

        return response()->json([
            'message' => 'Fee created successfully.',
            'fee' => new FeeResource($fee),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show fee.
     */
    public function show(Request $request, Fee $fee): JsonResponse
    {
        return response()->json([
            'fee' => new FeeResource($fee),
        ], Response::HTTP_OK);
    }

    /**
     * Update fee.
     */
    public function update(Request $request, Fee $fee): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'type' => ['string', Rule::in([Fee::TYPE_COST, Fee::TYPE_PRICE_DEDUCTION])],
            'fee_type' => ['string', Rule::in([Fee::FEE_TYPE_PERCENTAGE, Fee::FEE_TYPE_FIXED])],
            'fee_value' => ['numeric', 'gte:0'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $fee->update($validated);

        return response()->json([
            'message' => 'Fee updated successfully.',
            'fee' => new FeeResource($fee),
        ], Response::HTTP_OK);
    }

    /**
     * Delete fee.
     */
    public function destroy(Request $request, Fee $fee): JsonResponse
    {
        $fee->delete();

        return response()->json([
            'message' => 'Fee deleted successfully.',
        ], Response::HTTP_OK);
    }
}
