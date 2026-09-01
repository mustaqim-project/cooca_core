<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ExchangeRate $resource
 */
final class ExchangeRateResource extends JsonResource
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
            'from_currency' => new CurrencyResource($this->whenLoaded('fromCurrency')),
            'to_currency' => new CurrencyResource($this->whenLoaded('toCurrency')),
            'rate' => $this->resource->rate,
            'snapshot_date' => $this->resource->snapshot_date?->format('Y-m-d'),
        ];
    }
}
