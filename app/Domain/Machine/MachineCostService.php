<?php

declare(strict_types=1);

namespace App\Domain\Machine;

use App\Models\CostModelMachine;
use App\Models\Machine;

final class MachineCostService
{
    /**
     * Calculate hourly depreciation using straight-line basis over useful life hours (§17 Blueprint).
     */
    public function depreciationPerHour(Machine $machine): float
    {
        $depreciable = max(0.0, (float) ($machine->purchase_price - $machine->residual_value));

        return (float) ($machine->useful_life_hours > 0 ? ($depreciable / $machine->useful_life_hours) : 0.0);
    }

    /**
     * Calculate total machine operating cost per hour (Depreciation + Maintenance + Electricity).
     */
    public function costPerHour(Machine $machine): float
    {
        return (float) (
            $this->depreciationPerHour($machine)
            + $machine->maintenance_cost_per_hour
            + $machine->electricity_cost_per_hour
        );
    }

    /**
     * Calculate cost for a machine assignment line.
     */
    public function calculateMachineCost(CostModelMachine $item): float
    {
        $machine = $item->machine;
        if ($machine === null) {
            return 0.0;
        }

        return (float) ($item->hours_used * $this->costPerHour($machine));
    }
}
