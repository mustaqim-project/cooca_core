<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AllocationRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AllocationRule $resource
 */
final class AllocationRuleResource extends JsonResource
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
            'cost_pool' => new CostPoolResource($this->whenLoaded('costPool')),
            'cost_driver' => new CostDriverResource($this->whenLoaded('costDriver')),
            'cost_model' => new CostModelResource($this->whenLoaded('costModel')),
            'target_category' => new ProductCategoryResource($this->whenLoaded('targetCategory')),
            'total_driver_capacity' => $this->resource->total_driver_capacity,
            'name' => $this->resource->name,
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
