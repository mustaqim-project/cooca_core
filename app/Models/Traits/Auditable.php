<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\AuditLog;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the Auditable trait for the model.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            $model->recordAuditLog('created', null, $model->getAuditableAttributes());
        });

        static::updated(function (Model $model): void {
            $dirty = $model->getDirty();
            $ignored = $model->getAuditExcludedAttributes();

            $oldValues = [];
            $newValues = [];

            foreach ($dirty as $key => $newValue) {
                if (in_array($key, $ignored, true)) {
                    continue;
                }

                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $newValue;
            }

            if (! empty($newValues)) {
                $model->recordAuditLog('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model): void {
            $model->recordAuditLog('deleted', $model->getAuditableAttributes(), null);
        });
    }

    /**
     * Record an audit log entry.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected function recordAuditLog(string $action, ?array $oldValues, ?array $newValues): void
    {
        $businessId = $this->business_id ?? (Context::hasBusiness() ? Context::business()?->id : null);

        if ($businessId === null) {
            return;
        }

        AuditLog::create([
            'business_id' => $businessId,
            'user_id' => Auth::id(),
            'auditable_type' => static::class,
            'auditable_id' => (string) $this->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get auditable attributes filtered from sensitive keys.
     *
     * @return array<string, mixed>
     */
    public function getAuditableAttributes(): array
    {
        $attributes = $this->attributesToArray();
        $excluded = array_flip($this->getAuditExcludedAttributes());

        return array_diff_key($attributes, $excluded);
    }

    /**
     * Attributes excluded from audit trail.
     *
     * @return array<int, string>
     */
    public function getAuditExcludedAttributes(): array
    {
        return [
            'password',
            'remember_token',
            'updated_at',
        ];
    }
}
