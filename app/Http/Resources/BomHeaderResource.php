<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BomHeader;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property BomHeader $resource
 */
final class BomHeaderResource extends JsonResource
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
            'cost_model_id' => $this->resource->cost_model_id,
            'type' => $this->resource->type,
            'level' => $this->resource->level,
            'name' => $this->resource->name,
            'notes' => $this->resource->notes,
            'items' => BomItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
