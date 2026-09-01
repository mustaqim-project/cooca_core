<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Product $resource
 */
final class ProductResource extends JsonResource
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
            'code' => $this->resource->code,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'business_type_hint' => $this->resource->business_type_hint,
            'output_unit' => new UnitResource($this->whenLoaded('outputUnit')),
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'cost_models' => CostModelResource::collection($this->whenLoaded('costModels')),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
