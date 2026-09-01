<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Context::hasBusiness();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        return $this->belongsToActiveBusiness($model);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Context::hasBusiness();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        return $this->belongsToActiveBusiness($model);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        return $this->belongsToActiveBusiness($model);
    }

    /**
     * Helper to verify if model belongs to the active tenant context.
     */
    protected function belongsToActiveBusiness(Model $model): bool
    {
        if (! Context::hasBusiness()) {
            return false;
        }

        return isset($model->business_id) && $model->business_id === Context::business()->id;
    }
}
