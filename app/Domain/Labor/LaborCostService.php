<?php

declare(strict_types=1);

namespace App\Domain\Labor;

use App\Models\CostModelLabor;
use App\Models\LaborRate;

final class LaborCostService
{
    /**
     * Calculate total monthly productive hours.
     * Formula: Working Days * Working Hours/Day * Utilization Rate%.
     */
    public function monthlyProductiveHours(LaborRate $rate): float
    {
        $days = $rate->working_days_per_month ?: 22;
        $hoursPerDay = (float) ($rate->working_hours_per_day ?: 8.0);
        $utilization = (float) (($rate->utilization_rate ?: 80.0) / 100.0);

        return (float) ($days * $hoursPerDay * $utilization);
    }

    /**
     * Convert rate to hourly equivalent based on rate basis (§16 Blueprint).
     */
    public function hourlyRateEquivalent(LaborRate $rate): float
    {
        return match ($rate->basis) {
            LaborRate::BASIS_HOURLY => (float) $rate->rate_amount,
            LaborRate::BASIS_DAILY => (float) ($rate->rate_amount / ($rate->working_hours_per_day ?: 8.0)),
            LaborRate::BASIS_MONTHLY => (function () use ($rate): float {
                $hours = $this->monthlyProductiveHours($rate);

                return $hours > 0 ? (float) ($rate->rate_amount / $hours) : 0.0;
            })(),
            default => (float) $rate->rate_amount,
        };
    }

    /**
     * Calculate cost for a single labor assignment line, including overtime.
     */
    public function calculateLaborCost(CostModelLabor $item): float
    {
        $rate = $item->laborRate;
        if ($rate === null) {
            return 0.0;
        }

        if (in_array($rate->basis, [LaborRate::BASIS_HOURLY, LaborRate::BASIS_DAILY, LaborRate::BASIS_MONTHLY], true)) {
            $hourlyRate = $this->hourlyRateEquivalent($rate);
            $regularHours = (float) ($item->regular_hours ?? $item->quantity);
            $overtimeHours = (float) ($item->overtime_hours ?? 0.0);
            $multiplier = (float) ($rate->overtime_multiplier ?: 1.0);

            return (float) (($regularHours * $hourlyRate) + ($overtimeHours * $hourlyRate * $multiplier));
        }

        return (float) ($item->quantity * $rate->rate_amount);
    }
}
