<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Profitability\BepEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class BepController extends Controller
{
    public function __construct(private readonly BepEngine $bepEngine = new BepEngine) {}

    /**
     * Calculate Break-Even Point in Units, Revenue, and Target Profit.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total_fixed_cost' => ['required', 'numeric', 'gte:0'],
            'selling_price' => ['required', 'numeric', 'gt:0'],
            'variable_cost_per_unit' => ['required', 'numeric', 'gte:0'],
            'expected_sales_units' => ['nullable', 'numeric', 'gte:0'],
            'target_profit' => ['nullable', 'numeric', 'gte:0'],
        ]);

        try {
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
                'bep_analysis' => $analysis,
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
