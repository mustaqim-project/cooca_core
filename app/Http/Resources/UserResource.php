<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = \App\Support\Context::role();
        if ($role === null && $this->resource->active_business_id) {
            $role = \App\Models\BusinessMembership::where('business_id', $this->resource->active_business_id)
                ->where('user_id', $this->resource->id)
                ->value('role');
        }
        $role = $role ?? 'owner';

        $permissions = \App\Support\Context::permissions();
        if (empty($permissions) && $role === 'owner') {
            $permissions = \App\Models\Permission::pluck('slug')->all();
        }

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone ?? null,
            'avatar_url' => $this->resource->avatar ?? null,
            'active_business_id' => $this->resource->active_business_id,
            'role' => $role,
            'is_owner' => $role === 'owner',
            'permissions' => $permissions,
            'can_view_margin' => $role === 'owner' || in_array('costing.view_margin', $permissions, true),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
