<?php

declare(strict_types=1);

namespace App\Domain\Overhead;

use App\Models\AllocationRule;
use App\Models\CostDriver;
use App\Models\CostPool;
use InvalidArgumentException;

final class AllocationEngineService
{
    /**
     * Traditional Per-Unit Allocation.
     * Formula: Pool Amount / Total Planned Production Units (§18 Blueprint).
     */
    public function perUnit(CostPool $pool, float $totalUnitsPlanned): float
    {
        if ($totalUnitsPlanned <= 0.0) {
            throw new InvalidArgumentException('Total planned units must be greater than zero.');
        }

        return (float) ($pool->totalAmount() / $totalUnitsPlanned);
    }

    /**
     * Revenue Percentage Allocation.
     * Formula: Pool Amount * (Product Expected Revenue / Total Business Revenue).
     */
    public function revenuePercentage(CostPool $pool, float $productRevenue, float $totalBusinessRevenue): float
    {
        if ($totalBusinessRevenue <= 0.0) {
            throw new InvalidArgumentException('Total business revenue must be greater than zero.');
        }

        return (float) ($pool->totalAmount() * ($productRevenue / $totalBusinessRevenue));
    }

    /**
     * Labor Hours Proportional Allocation.
     * Formula: Pool Amount * (Product Labor Hours / Total Facility Labor Hours).
     */
    public function laborHour(CostPool $pool, float $productLaborHours, float $totalLaborHours): float
    {
        if ($totalLaborHours <= 0.0) {
            throw new InvalidArgumentException('Total labor hours must be greater than zero.');
        }

        return (float) ($pool->totalAmount() * ($productLaborHours / $totalLaborHours));
    }

    /**
     * Machine Hours Proportional Allocation.
     * Formula: Pool Amount * (Product Machine Hours / Total Machine Hours).
     */
    public function machineHour(CostPool $pool, float $productMachineHours, float $totalMachineHours): float
    {
        if ($totalMachineHours <= 0.0) {
            throw new InvalidArgumentException('Total machine hours must be greater than zero.');
        }

        return (float) ($pool->totalAmount() * ($productMachineHours / $totalMachineHours));
    }

    /**
     * Physical Metric Proportional Allocation (Area, Weight, Volume).
     */
    public function physicalMetric(CostPool $pool, float $productMetric, float $totalMetric): float
    {
        if ($totalMetric <= 0.0) {
            throw new InvalidArgumentException('Total metric capacity must be greater than zero.');
        }

        return (float) ($pool->totalAmount() * ($productMetric / $totalMetric));
    }

    /**
     * Calculate allocated cost using an AllocationRule.
     */
    public function calculateForRule(AllocationRule $rule, float $productMetric, ?float $overrideTotalCapacity = null): float
    {
        $pool = $rule->costPool;
        $driver = $rule->costDriver;
        $totalCapacity = $overrideTotalCapacity ?? (float) $rule->total_driver_capacity;

        if ($pool === null || $driver === null) {
            return 0.0;
        }

        return match ($driver->type) {
            CostDriver::TYPE_PER_UNIT => $this->perUnit($pool, $totalCapacity),
            CostDriver::TYPE_REVENUE_PCT => $this->revenuePercentage($pool, $productMetric, $totalCapacity),
            CostDriver::TYPE_LABOR_HOUR => $this->laborHour($pool, $productMetric, $totalCapacity),
            CostDriver::TYPE_MACHINE_HOUR => $this->machineHour($pool, $productMetric, $totalCapacity),
            CostDriver::TYPE_AREA, CostDriver::TYPE_WEIGHT, CostDriver::TYPE_VOLUME, CostDriver::TYPE_PRODUCTION_HOUR => $this->physicalMetric($pool, $productMetric, $totalCapacity),
            default => (float) ($totalCapacity > 0 ? ($pool->totalAmount() * ($productMetric / $totalCapacity)) : 0.0),
        };
    }
}
