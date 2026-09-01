<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LaborRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property LaborRate $resource
 */
final class LaborRateResource extends JsonResource
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
            'basis' => $this->resource->basis,
            'rate_amount' => $this->resource->rate_amount,
            'is_subcontractor' => $this->resource->is_subcontractor,
            'overtime_multiplier' => $this->resource->overtime_multiplier,
            'working_days_per_month' => $this->resource->working_days_per_month,
            'working_hours_per_day' => $this->resource->working_hours_per_day,
            'utilization_rate' => $this->resource->utilization_rate,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
