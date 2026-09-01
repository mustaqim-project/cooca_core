<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPointHistory extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_POS_EARN = 'pos_earn';

    public const TYPE_POS_REDEEM = 'pos_redeem';

    public const TYPE_BIRTHDAY_BONUS = 'birthday_bonus';

    public const TYPE_MANUAL = 'manual_adjustment';

    protected $fillable = [
        'business_id',
        'customer_id',
        'points_change',
        'type',
        'reference_id',
        'balance_after',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points_change' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
