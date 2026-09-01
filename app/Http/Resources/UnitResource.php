<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Unit $resource
 */
final class UnitResource extends JsonResource
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
            'code' => $this->resource->code,
            'name' => $this->resource->name,
            'category' => $this->resource->category,
            'is_base' => $this->resource->is_base,
            'default_precision' => $this->resource->default_precision,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
