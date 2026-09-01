<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Models\CostModel;
use InvalidArgumentException;

final class HppEngine
{
    public function __construct(private readonly RoundingService $roundingService = new RoundingService) {}

    /**
     * Aggregate final HPP based on output basis (§15 Blueprint).
     *
     * @param array{
     *     planned_output?: float,
     *     actual_output?: float,
     *     defect_quantity?: float
     * } $outputMetrics
     * @return array{
     *     basis: string,
     *     total_cost: float,
     *     output_quantity: float,
     *     hpp_per_unit: float,
     *     hpp_per_unit_rounded: float
     * }
     */
    public function aggregate(CostingResultDTO $costingResult, string $outputBasis = CostModel::BASIS_PLANNED, array $outputMetrics = []): array
    {
        $totalCost = $costingResult->totalHpp;

        $outputQuantity = match ($outputBasis) {
            CostModel::BASIS_PLANNED => (float) ($outputMetrics['planned_output'] ?? 1.0),
            CostModel::BASIS_ACTUAL => (float) ($outputMetrics['actual_output'] ?? 1.0),
            CostModel::BASIS_SELLABLE => (function () use ($outputMetrics): float {
                $actual = (float) ($outputMetrics['actual_output'] ?? 1.0);
                $defect = (float) ($outputMetrics['defect_quantity'] ?? 0.0);
                $sellable = $actual - $defect;

                if ($sellable <= 0.0) {
                    throw new InvalidArgumentException('Sellable output quantity must be greater than zero.');
                }

                return $sellable;
            })(),
            default => 1.0,
        };

        if ($outputQuantity <= 0.0) {
            throw new InvalidArgumentException('Output quantity must be greater than zero.');
        }

        $hppPerUnit = $totalCost / $outputQuantity;
        $hppRounded = $this->roundingService->apply($hppPerUnit);

        return [
            'basis' => $outputBasis,
            'total_cost' => $totalCost,
            'output_quantity' => $outputQuantity,
            'hpp_per_unit' => $hppPerUnit,
            'hpp_per_unit_rounded' => $hppRounded,
        ];
    }
}
