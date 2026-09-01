<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Material $resource
 */
final class MaterialResource extends JsonResource
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
            'is_discontinued' => $this->resource->isDiscontinued(),
            'discontinued_at' => $this->resource->discontinued_at?->toIso8601String(),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'category' => new MaterialCategoryResource($this->whenLoaded('category')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'latest_price' => new MaterialPriceResource($this->whenLoaded('latestPrice')),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
