<?php

declare(strict_types=1);

namespace App\Domain\Overhead;

use App\Models\Activity;
use App\Models\CostModel;
use App\Models\CostModelActivity;
use InvalidArgumentException;

final class AbcCostingService
{
    /**
     * Calculate cost driver rate per unit of activity (§19 Blueprint).
     * Formula: Cost Pool Total Amount / Total Activity Capacity.
     */
    public function activityRate(Activity $activity): float
    {
        $pool = $activity->costPool;
        if ($pool === null) {
            return 0.0;
        }

        if ($activity->total_activity_capacity <= 0.0) {
            throw new InvalidArgumentException('Activity total capacity must be greater than zero.');
        }

        return (float) ($pool->totalAmount() / $activity->total_activity_capacity);
    }

    /**
     * Calculate allocated overhead cost for a consumed activity.
     */
    public function calculateActivityCost(CostModelActivity $item): float
    {
        $activity = $item->activity;
        if ($activity === null) {
            return 0.0;
        }

        $rate = $this->activityRate($activity);

        return (float) ($rate * $item->consumed_quantity);
    }

    /**
     * Calculate total ABC overhead cost for a cost model.
     *
     * @return array{
     *     total_abc_cost: float,
     *     items: array<int, mixed>
     * }
     */
    public function calculateForCostModel(CostModel $costModel): array
    {
        $costModel->loadMissing('activities.activity.costPool');

        $totalCost = 0.0;
        $breakdown = [];

        foreach ($costModel->activities as $item) {
            $activity = $item->activity;
            $rate = $activity ? $this->activityRate($activity) : 0.0;
            $cost = $rate * (float) $item->consumed_quantity;
            $totalCost += $cost;

            $breakdown[] = [
                'id' => $item->id,
                'activity_id' => $activity?->id,
                'activity_name' => $activity?->name,
                'driver_name' => $activity?->cost_driver_name,
                'rate_per_driver_unit' => $rate,
                'consumed_quantity' => (float) $item->consumed_quantity,
                'allocated_cost' => $cost,
            ];
        }

        return [
            'total_abc_cost' => $totalCost,
            'items' => $breakdown,
        ];
    }
}
