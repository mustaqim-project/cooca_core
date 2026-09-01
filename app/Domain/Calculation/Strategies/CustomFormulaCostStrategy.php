<?php

declare(strict_types=1);

namespace App\Domain\Calculation\Strategies;

use App\Domain\Calculation\Contracts\CostingStrategyInterface;
use App\Domain\Calculation\CostModelPreviewService;
use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Domain\Formula\FormulaEvaluator;
use App\Models\CostModel;
use InvalidArgumentException;

final class CustomFormulaCostStrategy implements CostingStrategyInterface
{
    public function __construct(
        private readonly CostModelPreviewService $previewService = new CostModelPreviewService,
        private readonly FormulaEvaluator $evaluator = new FormulaEvaluator
    ) {}

    public function supports(string $method): bool
    {
        return $method === CostModel::METHOD_CUSTOM;
    }

    public function calculate(CostModel $costModel): CostingResultDTO
    {
        $preview = $this->previewService->preview($costModel);
        $summary = $preview['summary'];

        $material = (float) $summary['total_material_cost'];
        $labor = (float) $summary['total_labor_cost'];
        $machine = (float) $summary['total_machine_cost'];
        $primeCost = (float) $summary['prime_cost'];
        $conversionCost = (float) $summary['conversion_cost'];

        $formulaDef = $costModel->formula_definition;
        if (empty($formulaDef) || empty($formulaDef['ast'])) {
            throw new InvalidArgumentException("Cost model '{$costModel->name}' is set to custom method but has no formula definition.");
        }

        $variables = [
            'material' => $material,
            'labor' => $labor,
            'machine' => $machine,
            'prime_cost' => $primeCost,
            'conversion_cost' => $conversionCost,
            'direct_cost' => $material + $labor + $machine,
        ];

        $totalHpp = $this->evaluator->evaluate((array) $formulaDef['ast'], $variables);

        return new CostingResultDTO(
            costModelId: $costModel->id,
            method: $costModel->method,
            totalMaterialCost: $material,
            totalLaborCost: $labor,
            totalMachineCost: $machine,
            totalOverheadCost: max(0.0, $totalHpp - ($material + $labor + $machine)),
            totalHpp: $totalHpp,
            hppPerUnit: $totalHpp,
            breakdown: [
                'preview' => $preview,
                'formula' => $formulaDef['expression'] ?? null,
                'variables_used' => $variables,
            ]
        );
    }
}
