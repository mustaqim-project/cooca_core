<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Labor\LaborCostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\LaborRateResource;
use App\Models\LaborRate;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class LaborRateController extends Controller
{
    /**
     * List all labor rates.
     */
    public function index(Request $request): JsonResponse
    {
        $rates = LaborRate::with('currency')->latest()->get();

        return response()->json([
            'data' => LaborRateResource::collection($rates),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new labor rate.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'basis' => ['required', 'string', Rule::in(LaborRate::BASES)],
            'rate_amount' => ['required', 'numeric', 'gte:0'],
            'is_subcontractor' => ['nullable', 'boolean'],
            'overtime_multiplier' => ['nullable', 'numeric', 'gte:1.0'],
            'working_days_per_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'working_hours_per_day' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'utilization_rate' => ['nullable', 'numeric', 'gt:0', 'lte:100'],
            'currency_id' => ['nullable', 'string', 'exists:currencies,id'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var LaborRate $rate */
        $rate = LaborRate::create($validated);
        $rate->load('currency');

        return response()->json([
            'message' => 'Labor rate created successfully.',
            'labor_rate' => new LaborRateResource($rate),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show labor rate.
     */
    public function show(Request $request, LaborRate $laborRate): JsonResponse
    {
        $laborRate->load('currency');

        return response()->json([
            'labor_rate' => new LaborRateResource($laborRate),
        ], Response::HTTP_OK);
    }

    /**
     * Update labor rate.
     */
    public function update(Request $request, LaborRate $laborRate): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'basis' => ['string', Rule::in(LaborRate::BASES)],
            'rate_amount' => ['numeric', 'gte:0'],
            'is_subcontractor' => ['boolean'],
            'overtime_multiplier' => ['numeric', 'gte:1.0'],
            'working_days_per_month' => ['integer', 'min:1', 'max:31'],
            'working_hours_per_day' => ['numeric', 'min:1', 'max:24'],
            'utilization_rate' => ['numeric', 'gt:0', 'lte:100'],
            'currency_id' => ['nullable', 'string', 'exists:currencies,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $laborRate->update($validated);
        $laborRate->load('currency');

        return response()->json([
            'message' => 'Labor rate updated successfully.',
            'labor_rate' => new LaborRateResource($laborRate),
        ], Response::HTTP_OK);
    }

    /**
     * Delete labor rate.
     */
    public function destroy(Request $request, LaborRate $laborRate): JsonResponse
    {
        $laborRate->delete();

        return response()->json([
            'message' => 'Labor rate deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Calculate hourly equivalent and productive hours breakdown.
     */
    public function hourlyEquivalent(Request $request, LaborRate $laborRate, LaborCostService $service): JsonResponse
    {
        $productiveHours = $service->monthlyProductiveHours($laborRate);
        $hourlyRate = $service->hourlyRateEquivalent($laborRate);

        return response()->json([
            'labor_rate' => new LaborRateResource($laborRate),
            'monthly_productive_hours' => $productiveHours,
            'hourly_rate_equivalent' => $hourlyRate,
        ], Response::HTTP_OK);
    }
}
