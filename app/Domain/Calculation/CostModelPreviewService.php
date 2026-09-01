<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Domain\Labor\LaborCostService;
use App\Domain\Machine\MachineCostService;
use App\Domain\Product\BomExplosionService;
use App\Models\CostModel;

final class CostModelPreviewService
{
    public function __construct(
        private readonly BomExplosionService $bomExplosionService = new BomExplosionService,
        private readonly LaborCostService $laborCostService = new LaborCostService,
        private readonly MachineCostService $machineCostService = new MachineCostService
    ) {}

    /**
     * Generate a live non-persisted preview calculation of a cost model.
     *
     * @return array<string, mixed>
     */
    public function preview(CostModel $costModel): array
    {
        // 1. Material Cost (BOM)
        $materialCost = 0.0;
        $bomData = null;

        if ($costModel->bomHeader !== null) {
            $bomData = $this->bomExplosionService->explode($costModel->bomHeader);
            $materialCost = (float) $bomData['total_material_cost'];
        }

        // 2. Labor Cost
        $laborItems = [];
        $totalLaborCost = 0.0;
        $costModel->loadMissing('labors.laborRate');

        foreach ($costModel->labors as $laborItem) {
            $cost = $this->laborCostService->calculateLaborCost($laborItem);
            $totalLaborCost += $cost;

            $laborItems[] = [
                'id' => $laborItem->id,
                'labor_rate' => [
                    'id' => $laborItem->laborRate?->id,
                    'name' => $laborItem->laborRate?->name,
                    'basis' => $laborItem->laborRate?->basis,
                    'rate_amount' => (float) $laborItem->laborRate?->rate_amount,
                    'is_subcontractor' => (bool) $laborItem->laborRate?->is_subcontractor,
                ],
                'quantity' => (float) $laborItem->quantity,
                'regular_hours' => (float) ($laborItem->regular_hours ?? $laborItem->quantity),
                'overtime_hours' => (float) $laborItem->overtime_hours,
                'total_cost' => $cost,
            ];
        }

        // 3. Machine Cost
        $machineItems = [];
        $totalMachineCost = 0.0;
        $costModel->loadMissing('machines.machine');

        foreach ($costModel->machines as $machineItem) {
            $cost = $this->machineCostService->calculateMachineCost($machineItem);
            $totalMachineCost += $cost;

            $machineItems[] = [
                'id' => $machineItem->id,
                'machine' => [
                    'id' => $machineItem->machine?->id,
                    'name' => $machineItem->machine?->name,
                    'cost_per_hour' => $machineItem->machine ? $this->machineCostService->costPerHour($machineItem->machine) : 0.0,
                ],
                'hours_used' => (float) $machineItem->hours_used,
                'total_cost' => $cost,
            ];
        }

        $primeCost = $materialCost + $totalLaborCost;
        $conversionCost = $totalLaborCost + $totalMachineCost;
        $directManufacturingCost = $materialCost + $totalLaborCost + $totalMachineCost;

        return [
            'cost_model' => [
                'id' => $costModel->id,
                'name' => $costModel->name,
                'slug' => $costModel->slug,
                'method' => $costModel->method,
                'output_basis' => $costModel->output_basis,
            ],
            'summary' => [
                'total_material_cost' => $materialCost,
                'total_labor_cost' => $totalLaborCost,
                'total_machine_cost' => $totalMachineCost,
                'prime_cost' => $primeCost,
                'conversion_cost' => $conversionCost,
                'direct_cost_subtotal' => $directManufacturingCost,
            ],
            'material_breakdown' => $bomData,
            'labor_breakdown' => $laborItems,
            'machine_breakdown' => $machineItems,
        ];
    }
}
