<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'location_id',
        'expense_number',
        'expense_date',
        'category',
        'amount',
        'payment_method',
        'account_id',
        'description',
        'receipt_image_path',
        'recorded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'float',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getProofUrlAttribute(): ?string
    {
        if (empty($this->receipt_image_path)) {
            return null;
        }

        return route('finance.expenses.receipt', $this->id);
    }

    public function approvalRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ApprovalRequest::class, 'document_id')
            ->where('document_type', ApprovalRule::DOC_EXPENSE);
    }
}
