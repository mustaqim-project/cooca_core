<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PricingRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PricingRule $resource
 */
final class PricingRuleResource extends JsonResource
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
            'cost_model_id' => $this->resource->cost_model_id,
            'product_id' => $this->resource->product_id,
            'name' => $this->resource->name,
            'strategy' => $this->resource->strategy,
            'value' => $this->resource->value,
            'target_profit_amount' => $this->resource->target_profit_amount,
            'is_active' => $this->resource->is_active,
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
