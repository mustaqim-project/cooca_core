<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Models\CostingResult;
use App\Models\CostingResultItem;
use App\Models\CostingRun;
use App\Models\CostModel;
use App\Support\Context;
use Illuminate\Support\Facades\DB;

final class CostingResultService
{
    /**
     * Persist an official costing calculation run and its detailed results.
     */
    public function persist(CostModel $costModel, CostingResultDTO $dto, string $runType = CostingRun::RUN_TYPE_MANUAL): CostingRun
    {
        return DB::transaction(function () use ($costModel, $dto, $runType): CostingRun {
            $user = Context::user();

            /** @var CostingRun $run */
            $run = CostingRun::create([
                'business_id' => $costModel->business_id,
                'cost_model_id' => $costModel->id,
                'triggered_by' => $user?->id,
                'run_type' => $runType,
                'status' => CostingRun::STATUS_COMPLETED,
            ]);

            /** @var CostingResult $result */
            $result = CostingResult::create([
                'costing_run_id' => $run->id,
                'total_material_cost' => $dto->totalMaterialCost,
                'total_labor_cost' => $dto->totalLaborCost,
                'total_machine_cost' => $dto->totalMachineCost,
                'total_overhead_cost' => $dto->totalOverheadCost,
                'total_hpp' => $dto->totalHpp,
                'hpp_per_unit' => $dto->hppPerUnit,
                'breakdown_snapshot' => $dto->breakdown,
            ]);

            // Save line items
            if ($dto->totalMaterialCost > 0) {
                CostingResultItem::create([
                    'costing_result_id' => $result->id,
                    'item_type' => 'material',
                    'item_name' => 'Total Direct Materials',
                    'amount' => $dto->totalMaterialCost,
                ]);
            }

            if ($dto->totalLaborCost > 0) {
                CostingResultItem::create([
                    'costing_result_id' => $result->id,
                    'item_type' => 'labor',
                    'item_name' => 'Total Direct Labor',
                    'amount' => $dto->totalLaborCost,
                ]);
            }

            if ($dto->totalMachineCost > 0) {
                CostingResultItem::create([
                    'costing_result_id' => $result->id,
                    'item_type' => 'machine',
                    'item_name' => 'Total Machine Cost',
                    'amount' => $dto->totalMachineCost,
                ]);
            }

            if ($dto->totalOverheadCost > 0) {
                CostingResultItem::create([
                    'costing_result_id' => $result->id,
                    'item_type' => 'overhead',
                    'item_name' => 'Total Allocated Overhead',
                    'amount' => $dto->totalOverheadCost,
                ]);
            }

            $run->load(['result.items', 'costModel']);

            return $run;
        });
    }
}
