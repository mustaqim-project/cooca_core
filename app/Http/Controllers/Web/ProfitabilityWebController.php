<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Profitability\BepEngine;
use App\Domain\Profitability\ProfitabilityEngine;
use App\Http\Controllers\Controller;
use App\Models\Overhead;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProfitabilityWebController extends Controller
{
    public function __construct(
        private readonly ProfitabilityEngine $profitabilityEngine = new ProfitabilityEngine,
        private readonly BepEngine $bepEngine = new BepEngine
    ) {}

    /**
     * Show Break-Even Point & Profitability Dashboard.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();

        $totalFixedOverhead = (float) Overhead::where('behavior', Overhead::BEHAVIOR_FIXED)
            ->get()
            ->sum(fn (Overhead $o) => $o->monthlyAmount());

        $products = Product::with(['costModels.latestVersion'])
            ->get();

        return view('app.profitability.index', compact('business', 'totalFixedOverhead', 'products'));
    }

    /**
     * Live BEP calculation AJAX.
     */
    public function calculateBep(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total_fixed_cost' => ['required', 'numeric', 'gte:0'],
            'selling_price' => ['required', 'numeric', 'gt:0'],
            'variable_cost_per_unit' => ['required', 'numeric', 'gte:0'],
            'expected_sales_units' => ['nullable', 'numeric', 'gte:0'],
            'target_profit' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $analysis = $this->bepEngine->analyze(
            (float) $validated['total_fixed_cost'],
            (float) $validated['selling_price'],
            (float) $validated['variable_cost_per_unit'],
            isset($validated['expected_sales_units']) ? (float) $validated['expected_sales_units'] : null
        );

        if (! empty($validated['target_profit'])) {
            $targetResult = $this->bepEngine->targetProfitBep(
                (float) $validated['total_fixed_cost'],
                (float) $validated['target_profit'],
                (float) $validated['selling_price'],
                (float) $validated['variable_cost_per_unit']
            );
            $analysis['target_profit_analysis'] = $targetResult;
        }

        return response()->json([
            'bep' => $analysis,
        ]);
    }
}
