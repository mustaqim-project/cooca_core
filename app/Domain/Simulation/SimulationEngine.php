<?php

declare(strict_types=1);

namespace App\Domain\Simulation;

use App\Domain\Calculation\CalculationEngine;
use App\Domain\Pricing\PricingEngine;
use App\Models\CostModel;

final class SimulationEngine
{
    public function __construct(
        private readonly CalculationEngine $calculationEngine = new CalculationEngine,
        private readonly PricingEngine $pricingEngine = new PricingEngine
    ) {}

    /**
     * Run a sandboxed what-if simulation without mutating master data (§23 Blueprint).
     *
     * @param array{
     *     material_change_pct?: float,
     *     labor_change_pct?: float,
     *     machine_change_pct?: float,
     *     overhead_change_pct?: float,
     *     target_markup_pct?: float
     * } $scenarioInput
     * @return array{
     *     baseline: array<string, float>,
     *     simulated: array<string, float>,
     *     impact: array<string, float>
     * }
     */
    public function run(CostModel $costModel, array $scenarioInput): array
    {
        // 1. Calculate Baseline
        $baseDto = $this->calculationEngine->calculate($costModel);

        $matChange = (float) ($scenarioInput['material_change_pct'] ?? 0.0);
        $labChange = (float) ($scenarioInput['labor_change_pct'] ?? 0.0);
        $macChange = (float) ($scenarioInput['machine_change_pct'] ?? 0.0);
        $ovhChange = (float) ($scenarioInput['overhead_change_pct'] ?? 0.0);

        // 2. Simulate Adjusted Costs
        $simMaterial = $baseDto->totalMaterialCost * (1.0 + ($matChange / 100.0));
        $simLabor = $baseDto->totalLaborCost * (1.0 + ($labChange / 100.0));
        $simMachine = $baseDto->totalMachineCost * (1.0 + ($macChange / 100.0));
        $simOverhead = $baseDto->totalOverheadCost * (1.0 + ($ovhChange / 100.0));
        $simHpp = $simMaterial + $simLabor + $simMachine + $simOverhead;

        $markupPct = (float) ($scenarioInput['target_markup_pct'] ?? 40.0);
        $basePricing = $this->pricingEngine->fromMarkup($baseDto->totalHpp, $markupPct);
        $simPricing = $this->pricingEngine->fromMarkup($simHpp, $markupPct);

        $deltaHpp = $simHpp - $baseDto->totalHpp;
        $deltaHppPct = $baseDto->totalHpp > 0 ? ($deltaHpp / $baseDto->totalHpp) * 100.0 : 0.0;
        $deltaSellingPrice = $simPricing['selling_price'] - $basePricing['selling_price'];

        return [
            'baseline' => [
                'material_cost' => $baseDto->totalMaterialCost,
                'labor_cost' => $baseDto->totalLaborCost,
                'machine_cost' => $baseDto->totalMachineCost,
                'overhead_cost' => $baseDto->totalOverheadCost,
                'total_hpp' => $baseDto->totalHpp,
                'selling_price' => $basePricing['selling_price'],
                'gross_profit' => $basePricing['gross_profit'],
            ],
            'simulated' => [
                'material_cost' => $simMaterial,
                'labor_cost' => $simLabor,
                'machine_cost' => $simMachine,
                'overhead_cost' => $simOverhead,
                'total_hpp' => $simHpp,
                'selling_price' => $simPricing['selling_price'],
                'gross_profit' => $simPricing['gross_profit'],
            ],
            'impact' => [
                'delta_hpp_amount' => $deltaHpp,
                'delta_hpp_percentage' => $deltaHppPct,
                'delta_selling_price' => $deltaSellingPrice,
            ],
        ];
    }
}
