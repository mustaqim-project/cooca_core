<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BusinessTypeTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property BusinessTypeTemplate $resource
 */
final class BusinessTypeTemplateResource extends JsonResource
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
            'code' => $this->resource->code,
            'name' => $this->resource->name,
            'industry_category' => $this->resource->industry_category,
            'description' => $this->resource->description,
            'recommended_costing_method' => $this->resource->recommended_costing_method,
            'default_cost_components' => $this->resource->default_cost_components,
            'default_allocation_rules' => $this->resource->default_allocation_rules,
        ];
    }
}
