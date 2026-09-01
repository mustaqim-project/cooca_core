<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AiTokenTopup extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'business_id', 'payment_id', 'purchased_tokens', 'remaining_tokens', 'purchased_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_tokens' => 'integer',
            'remaining_tokens' => 'integer',
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'payment_id');
    }
}
