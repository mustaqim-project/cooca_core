<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Activity $resource
 */
final class ActivityResource extends JsonResource
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
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'cost_driver_name' => $this->resource->cost_driver_name,
            'total_activity_capacity' => $this->resource->total_activity_capacity,
            'cost_pool' => new CostPoolResource($this->whenLoaded('costPool')),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
