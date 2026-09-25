<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceSyncLog extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'marketplace_account_id',
        'channel',
        'entity_type',
        'entity_id',
        'action',
        'status',
        'payload',
        'response',
        'error_message',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'response'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public static function log(
        string $businessId,
        string $channel,
        string $entityType,
        string $action,
        string $status,
        ?string $entityId = null,
        ?array $payload = null,
        ?array $response = null,
        ?string $errorMessage = null,
        ?string $marketplaceAccountId = null
    ): self {
        return self::create([
            'business_id'            => $businessId,
            'marketplace_account_id' => $marketplaceAccountId,
            'channel'                => $channel,
            'entity_type'            => $entityType,
            'entity_id'              => $entityId,
            'action'                 => $action,
            'status'                 => $status,
            'payload'                => $payload,
            'response'               => $response,
            'error_message'          => $errorMessage,
            'created_at'             => now(),
        ]);
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * @return BelongsTo<MarketplaceAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }
}
