<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Support\Context;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class BusinessScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Context::hasBusiness()) {
            $builder->where($model->qualifyColumn('business_id'), Context::business()->id);
        }
    }
}
