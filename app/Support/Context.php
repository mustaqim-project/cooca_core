<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use RuntimeException;

final class Context
{
    private static ?Business $business = null;

    private static ?BusinessMembership $membership = null;

    /**
     * Get the active business from context.
     */
    public static function business(): ?Business
    {
        return self::$business;
    }

    /**
     * Get the active business or throw an exception if missing.
     */
    public static function requireBusiness(): Business
    {
        if (self::$business === null) {
            throw new RuntimeException('No active business context is bound to the current request.');
        }

        return self::$business;
    }

    /**
     * Determine if an active business context is present.
     */
    public static function hasBusiness(): bool
    {
        return self::$business !== null;
    }

    /**
     * Get the active membership for current user & business.
     */
    public static function membership(): ?BusinessMembership
    {
        return self::$membership;
    }

    /**
     * Get the authenticated user.
     */
    public static function user(): ?User
    {
        /** @var User|null */
        return auth()->user() ?: auth('sanctum')->user() ?: auth('web')->user();
    }

    /**
     * Get the active role in current business.
     */
    public static function role(): ?string
    {
        return self::$membership?->role;
    }

    /**
     * Determine if current user is owner of the active business.
     */
    public static function isOwner(): bool
    {
        return self::role() === 'owner';
    }

    /**
     * Determine if current user is admin or owner.
     */
    public static function isAdminOrOwner(): bool
    {
        return in_array(self::role(), ['owner', 'admin'], true);
    }

    /**
     * Get list of permission slugs for the active user in current business.
     *
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        $roleSlug = self::role();
        if ($roleSlug === null) {
            return [];
        }

        if ($roleSlug === 'owner') {
            return \App\Models\Permission::pluck('slug')->all();
        }

        /** @var \App\Models\Role|null $role */
        $role = \App\Models\Role::where('slug', $roleSlug)
            ->where(function ($query) {
                if ($businessId = self::business()?->id) {
                    $query->where('business_id', $businessId)->orWhereNull('business_id');
                } else {
                    $query->whereNull('business_id');
                }
            })
            ->first();

        return $role ? $role->permissions()->pluck('slug')->all() : [];
    }

    /**
     * Check if active user has a specific permission.
     */
    public static function hasPermission(string $permission): bool
    {
        if (self::isOwner()) {
            return true;
        }

        return in_array($permission, self::permissions(), true);
    }

    /**
     * Set the active business context.
     */
    public static function setBusiness(?Business $business, ?BusinessMembership $membership = null): void
    {
        self::$business = $business;
        self::$membership = $membership;
    }

    /**
     * Flush current context state (useful for tests and octane-safe cleanup).
     */
    public static function flush(): void
    {
        self::$business = null;
        self::$membership = null;
    }
}

