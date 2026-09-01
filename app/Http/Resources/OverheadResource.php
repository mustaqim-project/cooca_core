<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Overhead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Overhead $resource
 */
final class OverheadResource extends JsonResource
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
            'amount' => $this->resource->amount,
            'monthly_amount' => $this->resource->monthlyAmount(),
            'period' => $this->resource->period,
            'behavior' => $this->resource->behavior,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
