<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-bisnis pencacah pemakaian kuota transaksi bulanan (PO, POS) untuk entitas gratis.
 */
class QuotaMonthlyUsage extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_PO = 'purchase_order';

    public const TYPE_POS = 'pos_transaction';

    protected $fillable = ['business_id', 'resource_type', 'year', 'month', 'usage_count'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year'        => 'integer',
            'month'       => 'integer',
            'usage_count' => 'integer',
        ];
    }
}