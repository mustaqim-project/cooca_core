<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Profitability\ProfitabilityEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProfitabilityController extends Controller
{
    public function __construct(private readonly ProfitabilityEngine $engine = new ProfitabilityEngine) {}

    /**
     * Analyze profitability metrics for given selling price and costs.
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'selling_price' => ['required', 'numeric', 'gt:0'],
            'hpp_per_unit' => ['required', 'numeric', 'gte:0'],
            'variable_cost_per_unit' => ['required', 'numeric', 'gte:0'],
            'units_sold' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $analysis = $this->engine->analyze(
            (float) $validated['selling_price'],
            (float) $validated['hpp_per_unit'],
            (float) $validated['variable_cost_per_unit'],
            isset($validated['units_sold']) ? (float) $validated['units_sold'] : 1.0
        );

        return response()->json([
            'profitability' => $analysis,
        ], Response::HTTP_OK);
    }
}
