<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MaterialPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property MaterialPrice $resource
 */
final class MaterialPriceResource extends JsonResource
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
            'material_id' => $this->resource->material_id,
            'purchase_price' => $this->resource->purchase_price,
            'shipping_cost' => $this->resource->shipping_cost,
            'handling_cost' => $this->resource->handling_cost,
            'import_cost' => $this->resource->import_cost,
            'discount_amount' => $this->resource->discount_amount,
            'yield_percentage' => $this->resource->yield_percentage,
            'waste_percentage' => $this->resource->waste_percentage,
            'effective_date' => $this->resource->effective_date?->toDateString(),
            'purchase_unit' => new UnitResource($this->whenLoaded('purchaseUnit')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
