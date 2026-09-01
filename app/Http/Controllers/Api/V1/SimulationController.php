<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Simulation\SimulationEngine;
use App\Http\Controllers\Controller;
use App\Models\CostModel;
use App\Models\PricingScenario;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SimulationController extends Controller
{
    public function __construct(private readonly SimulationEngine $engine = new SimulationEngine) {}

    /**
     * Run a what-if simulation for a cost model without modifying master data.
     */
    public function simulate(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'material_change_pct' => ['nullable', 'numeric'],
            'labor_change_pct' => ['nullable', 'numeric'],
            'machine_change_pct' => ['nullable', 'numeric'],
            'overhead_change_pct' => ['nullable', 'numeric'],
            'target_markup_pct' => ['nullable', 'numeric'],
            'save_scenario' => ['nullable', 'boolean'],
        ]);

        $scenarioInput = [
            'material_change_pct' => (float) ($validated['material_change_pct'] ?? 0.0),
            'labor_change_pct' => (float) ($validated['labor_change_pct'] ?? 0.0),
            'machine_change_pct' => (float) ($validated['machine_change_pct'] ?? 0.0),
            'overhead_change_pct' => (float) ($validated['overhead_change_pct'] ?? 0.0),
            'target_markup_pct' => (float) ($validated['target_markup_pct'] ?? 40.0),
        ];

        $result = $this->engine->run($costModel, $scenarioInput);

        $savedScenario = null;
        if (! empty($validated['save_scenario'])) {
            $user = Context::user();
            $savedScenario = PricingScenario::create([
                'business_id' => $costModel->business_id,
                'cost_model_id' => $costModel->id,
                'name' => $validated['name'] ?? ('Simulation '.now()->format('Y-m-d H:i')),
                'scenario_input' => $scenarioInput,
                'scenario_result' => $result,
                'created_by' => $user?->id,
            ]);
        }

        return response()->json([
            'simulation' => $result,
            'saved_scenario' => $savedScenario,
        ], Response::HTTP_OK);
    }

    /**
     * List saved scenarios for a cost model.
     */
    public function index(Request $request, CostModel $costModel): JsonResponse
    {
        $scenarios = PricingScenario::where('cost_model_id', $costModel->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $scenarios,
        ], Response::HTTP_OK);
    }
}
