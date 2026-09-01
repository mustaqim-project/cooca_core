<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BomItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property BomItem $resource
 */
final class BomItemResource extends JsonResource
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
            'bom_header_id' => $this->resource->bom_header_id,
            'is_sub_assembly' => $this->resource->isSubAssembly(),
            'material' => new MaterialResource($this->whenLoaded('material')),
            'sub_bom_header' => new BomHeaderResource($this->whenLoaded('subBomHeader')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'quantity' => $this->resource->quantity,
            'waste_percentage' => $this->resource->waste_percentage,
            'sort_order' => $this->resource->sort_order,
            'notes' => $this->resource->notes,
        ];
    }
}
