<?php

declare(strict_types=1);

namespace App\Domain\Calculation\Strategies;

use App\Domain\Calculation\Contracts\CostingStrategyInterface;
use App\Domain\Calculation\CostModelPreviewService;
use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Domain\Overhead\AllocationEngineService;
use App\Models\AllocationRule;
use App\Models\CostModel;

final class RecipeBomCostStrategy implements CostingStrategyInterface
{
    public function __construct(
        private readonly CostModelPreviewService $previewService = new CostModelPreviewService,
        private readonly AllocationEngineService $allocationEngine = new AllocationEngineService
    ) {}

    public function supports(string $method): bool
    {
        return in_array($method, [
            CostModel::METHOD_RECIPE_BOM,
            CostModel::METHOD_PROCESS,
        ], true);
    }

    public function calculate(CostModel $costModel): CostingResultDTO
    {
        $preview = $this->previewService->preview($costModel);
        $summary = $preview['summary'];

        $material = (float) $summary['total_material_cost'];
        $labor = (float) $summary['total_labor_cost'];
        $machine = (float) $summary['total_machine_cost'];

        // Overhead from matching allocation rules
        $rules = AllocationRule::with(['costPool.overheads', 'costDriver'])
            ->where(function ($q) use ($costModel): void {
                $q->where('cost_model_id', $costModel->id);
                if ($costModel->product?->category_id !== null) {
                    $q->orWhere('target_category_id', $costModel->product->category_id);
                }
            })
            ->get();

        $totalOverhead = 0.0;
        $overheadBreakdown = [];

        foreach ($rules as $rule) {
            $allocated = $this->allocationEngine->calculateForRule($rule, 1.0);
            $totalOverhead += $allocated;

            $overheadBreakdown[] = [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name ?? $rule->costPool?->name,
                'driver_type' => $rule->costDriver?->type,
                'allocated_amount' => $allocated,
            ];
        }

        $totalHpp = $material + $labor + $machine + $totalOverhead;

        $breakdown = $preview;
        $breakdown['overhead_breakdown'] = $overheadBreakdown;

        return new CostingResultDTO(
            costModelId: $costModel->id,
            method: $costModel->method,
            totalMaterialCost: $material,
            totalLaborCost: $labor,
            totalMachineCost: $machine,
            totalOverheadCost: $totalOverhead,
            totalHpp: $totalHpp,
            hppPerUnit: $totalHpp,
            breakdown: $breakdown
        );
    }
}
