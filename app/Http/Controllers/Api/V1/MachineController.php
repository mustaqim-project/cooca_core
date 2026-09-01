<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Machine\MachineCostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\MachineResource;
use App\Models\Machine;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MachineController extends Controller
{
    /**
     * List all machines.
     */
    public function index(Request $request): JsonResponse
    {
        $machines = Machine::latest()->get();

        return response()->json([
            'data' => MachineResource::collection($machines),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new machine.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'gte:0'],
            'residual_value' => ['nullable', 'numeric', 'gte:0'],
            'useful_life_hours' => ['required', 'numeric', 'gt:0'],
            'maintenance_cost_per_hour' => ['nullable', 'numeric', 'gte:0'],
            'electricity_cost_per_hour' => ['nullable', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var Machine $machine */
        $machine = Machine::create($validated);

        return response()->json([
            'message' => 'Machine created successfully.',
            'machine' => new MachineResource($machine),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show machine details.
     */
    public function show(Request $request, Machine $machine): JsonResponse
    {
        return response()->json([
            'machine' => new MachineResource($machine),
        ], Response::HTTP_OK);
    }

    /**
     * Update machine.
     */
    public function update(Request $request, Machine $machine): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['numeric', 'gte:0'],
            'residual_value' => ['numeric', 'gte:0'],
            'useful_life_hours' => ['numeric', 'gt:0'],
            'maintenance_cost_per_hour' => ['numeric', 'gte:0'],
            'electricity_cost_per_hour' => ['numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $machine->update($validated);

        return response()->json([
            'message' => 'Machine updated successfully.',
            'machine' => new MachineResource($machine),
        ], Response::HTTP_OK);
    }

    /**
     * Delete machine.
     */
    public function destroy(Request $request, Machine $machine): JsonResponse
    {
        $machine->delete();

        return response()->json([
            'message' => 'Machine deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Calculate hourly machine rate and depreciation breakdown.
     */
    public function costPerHour(Request $request, Machine $machine, MachineCostService $service): JsonResponse
    {
        $depreciation = $service->depreciationPerHour($machine);
        $totalCostPerHour = $service->costPerHour($machine);

        return response()->json([
            'machine' => new MachineResource($machine),
            'depreciation_per_hour' => $depreciation,
            'maintenance_cost_per_hour' => (float) $machine->maintenance_cost_per_hour,
            'electricity_cost_per_hour' => (float) $machine->electricity_cost_per_hour,
            'total_cost_per_hour' => $totalCostPerHour,
        ], Response::HTTP_OK);
    }
}
