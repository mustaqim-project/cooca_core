<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'payrolls';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'business_id',
        'period_month',
        'period_year',
        'title',
        'status',
        'total_gross_pay',
        'total_deductions',
        'total_take_home_pay',
        'total_company_cost',
        'total_bpjs_company',
        'total_bpjs_employee',
        'total_pph21',
        'total_loan_deductions',
        'total_employees_count',
        'processed_by',
        'approved_by',
        'paid_at',
        'payment_method',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'total_gross_pay' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_take_home_pay' => 'decimal:2',
            'total_company_cost' => 'decimal:2',
            'total_bpjs_company' => 'decimal:2',
            'total_bpjs_employee' => 'decimal:2',
            'total_pph21' => 'decimal:2',
            'total_loan_deductions' => 'decimal:2',
            'total_employees_count' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'payroll_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedPeriodAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$this->period_month] ?? 'Bulan ' . $this->period_month) . ' ' . $this->period_year;
    }

    public function getPayrollNumberAttribute(): string
    {
        $monthPad = str_pad((string) $this->period_month, 2, '0', STR_PAD_LEFT);
        $shortId = strtoupper(substr((string) $this->id, 0, 6));

        return "PAY-{$this->period_year}{$monthPad}-{$shortId}";
    }

    public function getTotalNetSalaryAttribute(): float
    {
        return (float) $this->total_take_home_pay;
    }

    public function getTotalGrossSalaryAttribute(): float
    {
        return (float) $this->total_gross_pay;
    }

    public function getPaymentDateAttribute(): ?string
    {
        return $this->paid_at ? (string) $this->paid_at : null;
    }
}
