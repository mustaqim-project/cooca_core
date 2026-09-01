<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UnitConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property UnitConversion $resource
 */
final class UnitConversionResource extends JsonResource
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
            'is_system_default' => $this->resource->business_id === null,
            'from_unit' => new UnitResource($this->resource->fromUnit),
            'to_unit' => new UnitResource($this->resource->toUnit),
            'factor' => $this->resource->factor,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
