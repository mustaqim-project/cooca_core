<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Location $resource
 */
final class LocationResource extends JsonResource
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
            'address' => $this->resource->address,
            'is_primary' => $this->resource->is_primary,
            'is_active' => $this->resource->is_active,
        ];
    }
}
