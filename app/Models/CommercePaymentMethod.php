<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercePaymentMethod extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_BANK_TRANSFER = 'bank_transfer';
    public const TYPE_QRIS = 'qris';

    protected $table = 'commerce_payment_methods';

    protected $fillable = [
        'business_id',
        'type',
        'bank_name',
        'account_number',
        'account_holder',
        'qris_image_path',
        'instructions',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(CommerceOrder::class, 'payment_method_id');
    }

    public function getQrisImageUrlAttribute(): ?string
    {
        return $this->qris_image_path ? \App\Domain\Storage\TenantStorage::url($this->qris_image_path) : null;
    }
}
