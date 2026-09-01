<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Calculation\CalculationEngine;
use App\Http\Controllers\Controller;
use App\Models\CostModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class CalculationController extends Controller
{
    public function __construct(private readonly CalculationEngine $calculationEngine = new CalculationEngine) {}

    /**
     * Run the calculation engine for a specific cost model.
     */
    public function calculate(Request $request, CostModel $costModel): JsonResponse
    {
        try {
            $result = $this->calculationEngine->calculate($costModel);

            return response()->json([
                'message' => 'Costing calculation completed successfully.',
                'result' => $result->toArray(),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'CALCULATION_VALIDATION_ERROR',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
