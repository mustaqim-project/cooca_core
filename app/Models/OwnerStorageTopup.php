<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class OwnerStorageTopup extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['owner_id', 'payment_id', 'storage_bytes', 'approved_at'];

    protected function casts(): array
    {
        return ['storage_bytes' => 'integer', 'approved_at' => 'datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'payment_id');
    }
}
