<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostModel $resource
 */
final class CostModelResource extends JsonResource
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
            'product_id' => $this->resource->product_id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'method' => $this->resource->method,
            'output_basis' => $this->resource->output_basis,
            'is_active' => $this->resource->is_active,
            'notes' => $this->resource->notes,
            'product' => new ProductResource($this->whenLoaded('product')),
            'components' => CostComponentResource::collection($this->whenLoaded('components')),
            'bom_header' => new BomHeaderResource($this->whenLoaded('bomHeader')),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
