<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Simulation\SimulationEngine;
use App\Http\Controllers\Controller;
use App\Models\CostModel;
use App\Models\PricingScenario;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SimulationWebController extends Controller
{
    public function __construct(private readonly SimulationEngine $engine = new SimulationEngine) {}

    /**
     * Show what-if simulation dashboard.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $products = Product::where('business_id', $business->id)
            ->with(['costModels' => fn ($q) => $q->where('business_id', $business->id)->where('is_active', true)])
            ->whereHas('costModels', fn ($q) => $q->where('business_id', $business->id))
            ->get();

        $selectedCostModel = null;
        if ($request->filled('cost_model_id')) {
            $selectedCostModel = CostModel::where('business_id', $business->id)
                ->with(['product', 'labors', 'machines', 'bomHeaders.items'])
                ->find($request->get('cost_model_id'));
        } elseif ($products->isNotEmpty() && $products->first()->costModels->isNotEmpty()) {
            $selectedCostModel = $products->first()->costModels->first();
        }

        $savedScenarios = $selectedCostModel
            ? PricingScenario::where('cost_model_id', $selectedCostModel->id)->latest()->take(10)->get()
            : collect();

        return view('app.simulator.index', compact('business', 'products', 'selectedCostModel', 'savedScenarios'));
    }

    /**
     * Run simulation and return live calculation.
     */
    public function run(Request $request, CostModel $costModel): JsonResponse
    {
        $validated = $request->validate([
            'material_change_pct' => ['nullable', 'numeric'],
            'labor_change_pct' => ['nullable', 'numeric'],
            'machine_change_pct' => ['nullable', 'numeric'],
            'overhead_change_pct' => ['nullable', 'numeric'],
            'target_markup_pct' => ['nullable', 'numeric'],
        ]);

        $scenarioInput = [
            'material_change_pct' => (float) ($validated['material_change_pct'] ?? 0.0),
            'labor_change_pct' => (float) ($validated['labor_change_pct'] ?? 0.0),
            'machine_change_pct' => (float) ($validated['machine_change_pct'] ?? 0.0),
            'overhead_change_pct' => (float) ($validated['overhead_change_pct'] ?? 0.0),
            'target_markup_pct' => (float) ($validated['target_markup_pct'] ?? 40.0),
        ];

        $result = $this->engine->run($costModel, $scenarioInput);

        return response()->json([
            'simulation' => $result,
        ]);
    }
}
