<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AuditLog $resource
 */
final class AuditLogResource extends JsonResource
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
            'user_id' => $this->resource->user_id,
            'auditable_type' => class_basename($this->resource->auditable_type),
            'auditable_id' => $this->resource->auditable_id,
            'action' => $this->resource->action,
            'old_values' => $this->resource->old_values,
            'new_values' => $this->resource->new_values,
            'ip_address' => $this->resource->ip_address,
            'user_agent' => $this->resource->user_agent,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
