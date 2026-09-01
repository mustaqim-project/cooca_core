<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostModelMachine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostModelMachine $resource
 */
final class CostModelMachineResource extends JsonResource
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
            'machine' => new MachineResource($this->whenLoaded('machine')),
            'hours_used' => $this->resource->hours_used,
            'notes' => $this->resource->notes,
        ];
    }
}
