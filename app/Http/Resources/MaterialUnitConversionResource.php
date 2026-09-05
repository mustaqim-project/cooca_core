<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MaterialUnitConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property MaterialUnitConversion $resource
 */
final class MaterialUnitConversionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'business_id' => $this->resource->business_id,
            'material' => new MaterialResource($this->whenLoaded('material')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'from_unit' => new UnitResource($this->whenLoaded('fromUnit')),
            'to_unit' => new UnitResource($this->whenLoaded('toUnit')),
            'factor' => $this->resource->factor,
            'is_default' => $this->resource->is_default,
            'effective_from' => $this->resource->effective_from?->toDateString(),
            'effective_until' => $this->resource->effective_until?->toDateString(),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
