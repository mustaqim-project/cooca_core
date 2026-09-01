<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostModelActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostModelActivity $resource
 */
final class CostModelActivityResource extends JsonResource
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
            'activity' => new ActivityResource($this->whenLoaded('activity')),
            'consumed_quantity' => $this->resource->consumed_quantity,
            'notes' => $this->resource->notes,
        ];
    }
}
