<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Machine $resource
 */
final class MachineResource extends JsonResource
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
            'code' => $this->resource->code,
            'purchase_price' => $this->resource->purchase_price,
            'residual_value' => $this->resource->residual_value,
            'useful_life_hours' => $this->resource->useful_life_hours,
            'maintenance_cost_per_hour' => $this->resource->maintenance_cost_per_hour,
            'electricity_cost_per_hour' => $this->resource->electricity_cost_per_hour,
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
