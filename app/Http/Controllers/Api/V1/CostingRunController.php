<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculation\CalculationEngine;
use App\Domain\Calculation\CostingResultService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CostingRunResource;
use App\Models\CostingRun;
use App\Models\CostModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CostingRunController extends Controller
{
    /**
     * List all costing runs for a cost model.
     */
    public function index(Request $request, CostModel $costModel): JsonResponse
    {
        $runs = CostingRun::with(['result.items'])
            ->where('cost_model_id', $costModel->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => CostingRunResource::collection($runs),
        ], Response::HTTP_OK);
    }

    /**
     * Trigger an official calculation run and persist the result.
     */
    public function store(
        Request $request,
        CostModel $costModel,
        CalculationEngine $engine,
        CostingResultService $persistService
    ): JsonResponse {
        $validated = $request->validate([
            'run_type' => ['nullable', 'string', 'in:manual,scheduled,api'],
        ]);

        $dto = $engine->calculate($costModel);
        $run = $persistService->persist(
            $costModel,
            $dto,
            $validated['run_type'] ?? CostingRun::RUN_TYPE_MANUAL
        );

        return response()->json([
            'message' => 'Official costing run executed and recorded successfully.',
            'costing_run' => new CostingRunResource($run),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show details of a costing run.
     */
    public function show(Request $request, CostingRun $costingRun): JsonResponse
    {
        $costingRun->load(['result.items', 'costModel']);

        return response()->json([
            'costing_run' => new CostingRunResource($costingRun),
        ], Response::HTTP_OK);
    }
}
