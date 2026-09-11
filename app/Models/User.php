<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'active_business_id',
    'onboarding_completed',
    'onboarding_completed_at',
    'onboarding_current_step',
    'onboarding_version',
    'google_id',
    'avatar',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'onboarding_current_step' => 'integer',
            'onboarding_version' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all businesses this user belongs to.
     *
     * @return BelongsToMany<Business, $this>
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_users', 'user_id', 'business_id')
            ->using(BusinessMembership::class)
            ->withPivot(['id', 'role', 'role_id'])
            ->withTimestamps();
    }

    /**
     * Get business memberships.
     *
     * @return HasMany<BusinessMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(BusinessMembership::class);
    }

    /**
     * Get the active business context.
     *
     * @return BelongsTo<Business, $this>
     */
    public function activeBusiness(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'active_business_id');
    }

    public function storageTopups(): HasMany
    {
        return $this->hasMany(OwnerStorageTopup::class, 'owner_id');
    }

    public function storageFiles(): HasMany
    {
        return $this->hasMany(StorageFile::class, 'owner_id');
    }
}
