<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProductCostVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ProductCostVersion $resource
 */
final class ProductCostVersionResource extends JsonResource
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
            'cost_model_id' => $this->resource->cost_model_id,
            'version_number' => $this->resource->version_number,
            'version_label' => $this->resource->version_label,
            'status' => $this->resource->status,
            'total_hpp' => $this->resource->total_hpp,
            'hpp_per_unit' => $this->resource->hpp_per_unit,
            'hpp_snapshot' => $this->resource->hpp_snapshot,
            'formula_definition_snapshot' => $this->resource->formula_definition_snapshot,
            'status_history' => $this->resource->status_history,
            'effective_from' => $this->resource->effective_from?->toIso8601String(),
            'effective_to' => $this->resource->effective_to?->toIso8601String(),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
