<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostModelLabor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostModelLabor $resource
 */
final class CostModelLaborResource extends JsonResource
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
            'cost_model_id' => $this->resource->cost_model_id,
            'labor_rate' => new LaborRateResource($this->whenLoaded('laborRate')),
            'quantity' => $this->resource->quantity,
            'regular_hours' => $this->resource->regular_hours,
            'overtime_hours' => $this->resource->overtime_hours,
            'notes' => $this->resource->notes,
        ];
    }
}
