<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Variance\VarianceEngine;
use App\Http\Controllers\Controller;
use App\Models\ActualCost;
use App\Models\CostVariance;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VarianceController extends Controller
{
    public function __construct(private readonly VarianceEngine $engine = new VarianceEngine) {}

    /**
     * Record actual production costs for a product and period.
     */
    public function recordActual(Request $request, Product $product): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'period' => ['required', 'string', 'max:50'],
            'actual_material_cost' => ['required', 'numeric', 'gte:0'],
            'actual_labor_cost' => ['required', 'numeric', 'gte:0'],
            'actual_machine_cost' => ['nullable', 'numeric', 'gte:0'],
            'actual_overhead_cost' => ['nullable', 'numeric', 'gte:0'],
            'actual_output_qty' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $mat = (float) $validated['actual_material_cost'];
        $lab = (float) $validated['actual_labor_cost'];
        $mac = (float) ($validated['actual_machine_cost'] ?? 0.0);
        $ovh = (float) ($validated['actual_overhead_cost'] ?? 0.0);
        $total = $mat + $lab + $mac + $ovh;
        $qty = (float) $validated['actual_output_qty'];

        /** @var ActualCost $actual */
        $actual = ActualCost::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'period' => $validated['period'],
            'actual_material_cost' => $mat,
            'actual_labor_cost' => $lab,
            'actual_machine_cost' => $mac,
            'actual_overhead_cost' => $ovh,
            'actual_total_cost' => $total,
            'actual_output_qty' => $qty,
            'actual_hpp_per_unit' => $total / $qty,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Actual cost record saved successfully.',
            'actual_cost' => $actual,
        ], Response::HTTP_CREATED);
    }

    /**
     * Analyze variances between standard and actual costs.
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'standard_material' => ['required', 'numeric'],
            'actual_material' => ['required', 'numeric'],
            'standard_labor' => ['required', 'numeric'],
            'actual_labor' => ['required', 'numeric'],
            'standard_overhead' => ['nullable', 'numeric'],
            'actual_overhead' => ['nullable', 'numeric'],
            'standard_material_price' => ['nullable', 'numeric'],
            'actual_material_price' => ['nullable', 'numeric'],
            'actual_material_qty' => ['nullable', 'numeric'],
            'standard_material_qty' => ['nullable', 'numeric'],
        ]);

        $variances = [];

        // Material Price & Quantity Variance if detailed inputs provided
        if (isset($validated['actual_material_price'], $validated['standard_material_price'], $validated['actual_material_qty'])) {
            $variances['Material Price'] = $this->engine->materialPriceVariance(
                (float) $validated['actual_material_price'],
                (float) $validated['standard_material_price'],
                (float) $validated['actual_material_qty']
            );
        }

        if (isset($validated['actual_material_qty'], $validated['standard_material_qty'], $validated['standard_material_price'])) {
            $variances['Material Quantity'] = $this->engine->materialQuantityVariance(
                (float) $validated['actual_material_qty'],
                (float) $validated['standard_material_qty'],
                (float) $validated['standard_material_price']
            );
        }

        $matDiff = (float) $validated['actual_material'] - (float) $validated['standard_material'];
        $variances['Total Material'] = [
            'amount' => $matDiff,
            'percentage' => (float) $validated['standard_material'] > 0 ? ($matDiff / (float) $validated['standard_material']) * 100 : 0,
            'nature' => $matDiff > 0 ? CostVariance::NATURE_UNFAVORABLE : CostVariance::NATURE_FAVORABLE,
        ];

        $labDiff = (float) $validated['actual_labor'] - (float) $validated['standard_labor'];
        $variances['Total Labor'] = [
            'amount' => $labDiff,
            'percentage' => (float) $validated['standard_labor'] > 0 ? ($labDiff / (float) $validated['standard_labor']) * 100 : 0,
            'nature' => $labDiff > 0 ? CostVariance::NATURE_UNFAVORABLE : CostVariance::NATURE_FAVORABLE,
        ];

        if (isset($validated['actual_overhead'], $validated['standard_overhead'])) {
            $variances['Total Overhead'] = $this->engine->overheadSpendingVariance(
                (float) $validated['actual_overhead'],
                (float) $validated['standard_overhead']
            );
        }

        $ranked = $this->engine->rankContributors($variances);

        return response()->json([
            'variances' => $variances,
            'ranked_contributors' => $ranked,
        ], Response::HTTP_OK);
    }
}
