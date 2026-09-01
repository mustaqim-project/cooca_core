<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use BelongsToBusiness, HasFactory, HasSlug, HasUuid;

    public const TYPE_COST = 'cost';

    public const TYPE_PRICE_DEDUCTION = 'price_deduction';

    public const FEE_TYPE_PERCENTAGE = 'percentage';

    public const FEE_TYPE_FIXED = 'fixed';

    protected $table = 'fees';

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'type',
        'fee_type',
        'fee_value',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee_value' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
