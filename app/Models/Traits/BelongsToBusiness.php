<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Business;
use App\Models\Scopes\BusinessScope;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    /**
     * Boot the BelongsToBusiness trait for the model.
     */
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new BusinessScope);

        static::creating(function (Model $model): void {
            if (empty($model->business_id) && Context::hasBusiness()) {
                $model->business_id = Context::business()->id;
            }
        });
    }

    /**
     * Get the business that owns this model.
     *
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
