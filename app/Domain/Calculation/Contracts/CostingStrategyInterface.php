<?php

declare(strict_types=1);

namespace App\Domain\Calculation\Contracts;

use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Models\CostModel;

interface CostingStrategyInterface
{
    /**
     * Determine if strategy supports the given cost model method.
     */
    public function supports(string $method): bool;

    /**
     * Execute the costing calculation for a cost model.
     */
    public function calculate(CostModel $costModel): CostingResultDTO;
}
