<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostingRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostingRun $resource
 */
final class CostingRunResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'business_id' => $this->resource->business_id,
            'cost_model_id' => $this->resource->cost_model_id,
            'run_type' => $this->resource->run_type,
            'status' => $this->resource->status,
            'result' => $this->resource->result ? [
                'id' => $this->resource->result->id,
                'total_material_cost' => $this->resource->result->total_material_cost,
                'total_labor_cost' => $this->resource->result->total_labor_cost,
                'total_machine_cost' => $this->resource->result->total_machine_cost,
                'total_overhead_cost' => $this->resource->result->total_overhead_cost,
                'total_hpp' => $this->resource->result->total_hpp,
                'hpp_per_unit' => $this->resource->result->hpp_per_unit,
                'breakdown_snapshot' => $this->resource->result->breakdown_snapshot,
            ] : null,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
