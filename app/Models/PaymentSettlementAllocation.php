<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSettlementAllocation extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = ['business_id', 'payment_settlement_id', 'payment_type', 'payment_id', 'amount'];
    protected function casts(): array { return ['amount' => 'float']; }
    public function settlement(): BelongsTo { return $this->belongsTo(PaymentSettlement::class, 'payment_settlement_id'); }
}
