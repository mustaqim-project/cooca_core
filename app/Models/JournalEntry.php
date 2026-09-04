<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const REF_POS_ORDER = 'pos_order';

    public const REF_POS_REFUND = 'pos_refund';

    public const REF_INVOICE = 'invoice';

    public const REF_INVOICE_PAYMENT = 'invoice_payment';

    public const REF_EXPENSE = 'expense';

    public const REF_GOODS_RECEIPT = 'goods_receipt';

    public const REF_SUPPLIER_PAYMENT = 'supplier_payment';

    public const REF_SALES_RETURN = 'sales_return';

    public const REF_PURCHASE_RETURN = 'purchase_return';

    public const REF_SETTLEMENT = 'settlement';

    public const REF_STOCK_ADJUSTMENT = 'stock_adjustment';

    public const REF_MANUAL = 'manual';

    protected $fillable = [
        'business_id',
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'total_debit' => 'float',
            'total_credit' => 'float',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
