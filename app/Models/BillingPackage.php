<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class BillingPackage extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_AI_TOKEN = 'ai_token';
    public const TYPE_STORAGE = 'storage';
    public const TYPES = [self::TYPE_SUBSCRIPTION, self::TYPE_AI_TOKEN, self::TYPE_STORAGE];

    protected $fillable = [
        'type', 'code', 'name', 'description', 'price', 'duration_days', 'token_quantity',
        'storage_bytes', 'token_expiry_days', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'duration_days' => 'integer',
            'token_quantity' => 'integer',
            'storage_bytes' => 'integer',
            'token_expiry_days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'billing_package_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
