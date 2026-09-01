<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostPool;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostPool $resource
 */
final class CostPoolResource extends JsonResource
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
            'manual_override_amount' => $this->resource->manual_override_amount,
            'total_amount' => $this->resource->totalAmount(),
            'description' => $this->resource->description,
            'overheads' => OverheadResource::collection($this->whenLoaded('overheads')),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
