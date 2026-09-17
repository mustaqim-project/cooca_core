<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentGatewayCallbackLog extends Model
{
    use HasUuid;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';
    public const STATUS_INVALID_SIGNATURE = 'invalid_signature';

    protected $fillable = [
        'business_id',
        'gateway',
        'event',
        'merchant_ref',
        'tripay_reference',
        'signature',
        'ip_address',
        'status_code',
        'status',
        'payload',
        'response_payload',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'payload' => 'array',
            'response_payload' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope query to a specific business.
     */
    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope query for successful callbacks.
     */
    public function scopeSuccess(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope query for failed callbacks.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_INVALID_SIGNATURE]);
    }
}
