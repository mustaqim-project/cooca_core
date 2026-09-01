<?php

declare(strict_types=1);

namespace App\Domain\Calculation\DTO;

final class CostingResultDTO
{
    /**
     * @param  array<string, mixed>  $breakdown
     * @param  array<int, string>  $warnings
     */
    public function __construct(
        public readonly string $costModelId,
        public readonly string $method,
        public readonly float $totalMaterialCost,
        public readonly float $totalLaborCost,
        public readonly float $totalMachineCost,
        public readonly float $totalOverheadCost,
        public readonly float $totalHpp,
        public readonly float $hppPerUnit,
        public readonly array $breakdown = [],
        public readonly array $warnings = []
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cost_model_id' => $this->costModelId,
            'method' => $this->method,
            'total_material_cost' => $this->totalMaterialCost,
            'total_labor_cost' => $this->totalLaborCost,
            'total_machine_cost' => $this->totalMachineCost,
            'total_overhead_cost' => $this->totalOverheadCost,
            'total_hpp' => $this->totalHpp,
            'hpp_per_unit' => $this->hppPerUnit,
            'breakdown' => $this->breakdown,
            'warnings' => $this->warnings,
        ];
    }
}
