<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PayrollItem extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'payroll_items';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'payroll_id',
        'business_id',
        'user_id',
        'employee_name',
        'job_title',
        'employment_type',
        'join_date',
        'tenure_months',
        'base_salary',
        'daily_rate',
        'days_worked',
        'fixed_allowances',
        'variable_allowances',
        'overtime_pay',
        'commissions',
        'thr_amount',
        'gross_pay',
        'bpjs_tk_company',
        'bpjs_tk_employee',
        'bpjs_kes_company',
        'bpjs_kes_employee',
        'pph21_amount',
        'pph21_ter_category',
        'pph21_ter_rate',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        'take_home_pay',
        'company_total_cost',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'whatsapp_number',
        'payslip_token',
        'status',
        'calculation_payload',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'tenure_months' => 'integer',
            'base_salary' => 'decimal:2',
            'daily_rate' => 'decimal:2',
            'days_worked' => 'integer',
            'fixed_allowances' => 'decimal:2',
            'variable_allowances' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'commissions' => 'decimal:2',
            'thr_amount' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'bpjs_tk_company' => 'decimal:2',
            'bpjs_tk_employee' => 'decimal:2',
            'bpjs_kes_company' => 'decimal:2',
            'bpjs_kes_employee' => 'decimal:2',
            'pph21_amount' => 'decimal:2',
            'pph21_ter_rate' => 'decimal:4',
            'loan_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'take_home_pay' => 'decimal:2',
            'company_total_cost' => 'decimal:2',
            'calculation_payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PayrollItem $item): void {
            if (empty($item->payslip_token)) {
                $item->payslip_token = Str::random(40) . bin2hex(random_bytes(12));
            }
        });
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNetSalaryAttribute(): float
    {
        return (float) $this->take_home_pay;
    }

    public function getGrossSalaryAttribute(): float
    {
        return (float) $this->gross_pay;
    }

    public function getTotalEmployerCostAttribute(): float
    {
        return (float) $this->company_total_cost;
    }

    public function getTaxPph21Attribute(): float
    {
        return (float) $this->pph21_amount;
    }

    public function getTaxTerCategoryAttribute(): ?string
    {
        return $this->pph21_ter_category;
    }

    public function getTaxTerRateAttribute(): float
    {
        return (float) $this->pph21_ter_rate;
    }

    public function getBpjsTkEmployerAttribute(): float
    {
        return (float) $this->bpjs_tk_company;
    }

    public function getBpjsKesEmployerAttribute(): float
    {
        return (float) $this->bpjs_kes_company;
    }

    public function getThrBonusAttribute(): float
    {
        return (float) $this->thr_amount;
    }
}
