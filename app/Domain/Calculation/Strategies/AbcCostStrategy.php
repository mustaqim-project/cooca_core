<?php

declare(strict_types=1);

namespace App\Domain\Calculation\Strategies;

use App\Domain\Calculation\Contracts\CostingStrategyInterface;
use App\Domain\Calculation\CostModelPreviewService;
use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Domain\Overhead\AbcCostingService;
use App\Models\CostModel;

final class AbcCostStrategy implements CostingStrategyInterface
{
    public function __construct(
        private readonly CostModelPreviewService $previewService = new CostModelPreviewService,
        private readonly AbcCostingService $abcService = new AbcCostingService
    ) {}

    public function supports(string $method): bool
    {
        return $method === CostModel::METHOD_ABC;
    }

    public function calculate(CostModel $costModel): CostingResultDTO
    {
        $preview = $this->previewService->preview($costModel);
        $summary = $preview['summary'];

        $material = (float) $summary['total_material_cost'];
        $labor = (float) $summary['total_labor_cost'];
        $machine = (float) $summary['total_machine_cost'];

        $abcResult = $this->abcService->calculateForCostModel($costModel);
        $abcOverhead = (float) $abcResult['total_abc_cost'];

        $totalHpp = $material + $labor + $machine + $abcOverhead;

        $breakdown = $preview;
        $breakdown['abc_activities'] = $abcResult;

        return new CostingResultDTO(
            costModelId: $costModel->id,
            method: $costModel->method,
            totalMaterialCost: $material,
            totalLaborCost: $labor,
            totalMachineCost: $machine,
            totalOverheadCost: $abcOverhead,
            totalHpp: $totalHpp,
            hppPerUnit: $totalHpp,
            breakdown: $breakdown
        );
    }
}
