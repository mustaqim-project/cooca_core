<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MobileDeviceToken extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mobile_device_tokens';

    protected $fillable = [
        'user_id',
        'global_customer_id',
        'business_id',
        'token',
        'platform',
        'device_model',
        'app_type',
        'app_version',
        'last_seen_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<GlobalCustomer, $this> */
    public function globalCustomer(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
