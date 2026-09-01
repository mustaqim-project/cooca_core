<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Domain\Calculation\Contracts\CostingStrategyInterface;
use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Domain\Calculation\Strategies\AbcCostStrategy;
use App\Domain\Calculation\Strategies\CustomFormulaCostStrategy;
use App\Domain\Calculation\Strategies\JobCostStrategy;
use App\Domain\Calculation\Strategies\RecipeBomCostStrategy;
use App\Domain\Calculation\Strategies\SimpleCostStrategy;
use App\Models\CostModel;
use InvalidArgumentException;

final class CalculationEngine
{
    /** @var array<int, CostingStrategyInterface> */
    private array $strategies;

    public function __construct(
        private readonly NormalizationService $normalizationService = new NormalizationService,
        private readonly CostClassificationService $classificationService = new CostClassificationService,
        ?array $strategies = null
    ) {
        $this->strategies = $strategies ?? [
            new SimpleCostStrategy,
            new RecipeBomCostStrategy,
            new JobCostStrategy,
            new AbcCostStrategy,
            new CustomFormulaCostStrategy,
        ];
    }

    /**
     * Run full calculation pipeline for a cost model.
     *
     * @throws InvalidArgumentException
     */
    public function calculate(CostModel $costModel): CostingResultDTO
    {
        // 1. Normalization Layer (§67)
        $this->normalizationService->validateAndNormalize($costModel);

        // 2. Classification Layer (§68)
        $classification = $this->classificationService->classify($costModel);

        // 3. Strategy Selection (§69)
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($costModel->method)) {
                $result = $strategy->calculate($costModel);

                // Attach classification to breakdown
                $breakdown = $result->breakdown;
                $breakdown['cost_classification'] = $classification;

                return new CostingResultDTO(
                    costModelId: $result->costModelId,
                    method: $result->method,
                    totalMaterialCost: $result->totalMaterialCost,
                    totalLaborCost: $result->totalLaborCost,
                    totalMachineCost: $result->totalMachineCost,
                    totalOverheadCost: $result->totalOverheadCost,
                    totalHpp: $result->totalHpp,
                    hppPerUnit: $result->hppPerUnit,
                    breakdown: $breakdown,
                    warnings: $result->warnings
                );
            }
        }

        throw new InvalidArgumentException("Unsupported costing method '{$costModel->method}'.");
    }
}
