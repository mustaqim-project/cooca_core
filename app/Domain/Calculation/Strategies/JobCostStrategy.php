<?php

declare(strict_types=1);

namespace App\Domain\Calculation\Strategies;

use App\Domain\Calculation\Contracts\CostingStrategyInterface;
use App\Domain\Calculation\CostModelPreviewService;
use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Models\CostModel;

final class JobCostStrategy implements CostingStrategyInterface
{
    public function __construct(private readonly CostModelPreviewService $previewService = new CostModelPreviewService) {}

    public function supports(string $method): bool
    {
        return $method === CostModel::METHOD_JOB;
    }

    public function calculate(CostModel $costModel): CostingResultDTO
    {
        $preview = $this->previewService->preview($costModel);
        $summary = $preview['summary'];

        $material = (float) $summary['total_material_cost'];
        $labor = (float) $summary['total_labor_cost'];
        $machine = (float) $summary['total_machine_cost'];
        $totalHpp = $material + $labor + $machine;

        return new CostingResultDTO(
            costModelId: $costModel->id,
            method: $costModel->method,
            totalMaterialCost: $material,
            totalLaborCost: $labor,
            totalMachineCost: $machine,
            totalOverheadCost: 0.0,
            totalHpp: $totalHpp,
            hppPerUnit: $totalHpp,
            breakdown: $preview
        );
    }
}
