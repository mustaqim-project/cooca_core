<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CostComponent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CostComponent $resource
 */
final class CostComponentResource extends JsonResource
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
            'cost_category_id' => $this->resource->cost_category_id,
            'slug' => $this->resource->slug,
            'name' => $this->resource->name,
            'behavior' => $this->resource->behavior,
            'traceability' => $this->resource->traceability,
            'is_system' => $this->resource->is_system,
            'pivot' => $this->whenPivotLoaded('cost_model_components', function () {
                return [
                    'is_included_in_hpp' => (bool) $this->resource->pivot->is_included_in_hpp,
                    'notes' => $this->resource->pivot->notes,
                ];
            }),
        ];
    }
}
