<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\EmployeeCommission;
use App\Models\EmployeeLoan;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PayrollRunService
{
    public function __construct(
        private readonly PayrollCalculationService $payrollCalculationService
    ) {}

    /**
     * Generate or recalculate monthly payroll batch.
     *
     * @param Business $business
     * @param int $month (1-12)
     * @param int $year
     * @param array<string, array<string, mixed>> $employeeInputs Keyed by user_id
     * @param User $operator
     * @param bool $includeThr
     * @param string|null $notes
     * @return Payroll
     */
    public function generatePayrollRun(
        Business $business,
        int $month,
        int $year,
        array $employeeInputs = [],
        ?User $operator = null,
        bool $includeThr = false,
        ?string $notes = null
    ): Payroll {
        return DB::transaction(function () use ($business, $month, $year, $employeeInputs, $operator, $includeThr, $notes): Payroll {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthName = $monthNames[$month] ?? "Bulan $month";
            $title = "Penggajian $monthName $year";

            // Find existing payroll for this period
            $payroll = Payroll::where('business_id', $business->id)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->first();

            if ($payroll && $payroll->status === Payroll::STATUS_PAID) {
                throw new RuntimeException("Penggajian untuk periode $title sudah berstatus DIBAYAR dan tidak dapat diubah kembali.");
            }

            if (! $payroll) {
                $payroll = new Payroll([
                    'business_id' => $business->id,
                    'period_month' => $month,
                    'period_year' => $year,
                    'title' => $title,
                    'status' => Payroll::STATUS_DRAFT,
                    'processed_by' => $operator?->id,
                    'notes' => $notes,
                ]);
                $payroll->save();
            } else {
                $payroll->title = $title;
                $payroll->processed_by = $operator?->id ?? $payroll->processed_by;
                if ($notes !== null) {
                    $payroll->notes = $notes;
                }
                $payroll->save();

                // Clear previous draft items
                $payroll->items()->delete();
            }

            // Retrieve all active business memberships with user
            $memberships = BusinessMembership::where('business_id', $business->id)
                ->with(['user'])
                ->get();

            $totalGross = 0.0;
            $totalDeductions = 0.0;
            $totalTakeHome = 0.0;
            $totalCompanyCost = 0.0;
            $totalBpjsCompany = 0.0;
            $totalBpjsEmployee = 0.0;
            $totalPph21 = 0.0;
            $totalLoanDeduction = 0.0;
            $employeeCount = 0;

            $cutoffDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            foreach ($memberships as $membership) {
                $user = $membership->user;
                if (! $user) {
                    continue;
                }

                $userId = $user->id;
                $input = $employeeInputs[$userId] ?? [];

                // Skip if marked excluded
                if (! empty($input['exclude'])) {
                    continue;
                }

                $employmentType = (string) ($input['employment_type'] ?? $membership->employment_type ?? 'permanent');
                $jobTitle = (string) ($input['job_title'] ?? $membership->job_title ?? ucfirst($membership->role ?? 'Staf'));
                $joinDate = $input['join_date'] ?? $membership->join_date;
                $joinDateCarbon = $joinDate ? Carbon::parse($joinDate) : null;

                $tenureMonths = 0;
                if ($joinDateCarbon) {
                    $tenureMonths = max(0, $joinDateCarbon->diffInMonths($cutoffDate));
                }

                // Check active loans for this employee
                $activeLoan = EmployeeLoan::where('business_id', $business->id)
                    ->where('user_id', $userId)
                    ->where('status', EmployeeLoan::STATUS_ACTIVE)
                    ->where('remaining_balance', '>', 0)
                    ->first();

                $autoLoanDeduction = 0.0;
                if ($activeLoan) {
                    $autoLoanDeduction = min((float) $activeLoan->remaining_balance, (float) $activeLoan->monthly_installment);
                }

                $loanDeduction = isset($input['loan_deduction'])
                    ? (float) $input['loan_deduction']
                    : $autoLoanDeduction;

                $otherDeductions = (float) ($input['other_deductions'] ?? 0.0);
                $overtimePay = (float) ($input['overtime_pay'] ?? 0.0);

                // Commissions: check input or earned commissions
                $earnedCommissions = (float) EmployeeCommission::where('business_id', $business->id)
                    ->where('user_id', $userId)
                    ->whereIn('status', [EmployeeCommission::STATUS_EARNED, EmployeeCommission::STATUS_APPROVED])
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->sum('earned_amount');

                $commissions = isset($input['commissions'])
                    ? (float) $input['commissions']
                    : $earnedCommissions;

                if ($employmentType === 'daily_worker') {
                    $dailyRate = (float) ($input['daily_rate'] ?? $membership->daily_rate ?? 0.0);
                    $daysWorked = (int) ($input['days_worked'] ?? 25);
                    $incentives = $commissions;

                    $calc = $this->payrollCalculationService->calculateDailyWorkerPayroll(
                        dailyRate: $dailyRate,
                        daysWorked: $daysWorked,
                        overtimePay: $overtimePay,
                        incentives: $incentives,
                        loanDeduction: $loanDeduction
                    );

                    $baseSalary = 0.0;
                    $fixedAllowances = 0.0;
                    $variableAllowances = 0.0;
                    $thrAmount = 0.0;
                    $bpjsTkCompany = 0.0;
                    $bpjsTkEmployee = 0.0;
                    $bpjsKesCompany = 0.0;
                    $bpjsKesEmployee = 0.0;
                    $grossPay = (float) $calc['earnings']['gross_pay'];
                    $pph21Amount = (float) $calc['tax']['pph21_amount'];
                    $pph21Category = (string) $calc['tax']['category'];
                    $pph21Rate = (float) $calc['tax']['tax_rate_percent'];
                    $itemTakeHome = (float) $calc['take_home_pay'];
                    $itemCompanyCost = (float) $calc['company_total_cost'];
                    $itemTotalDeductions = (float) $calc['deductions']['total_deductions'];
                } else {
                    $baseSalary = (float) ($input['base_salary'] ?? $membership->base_salary ?? 0.0);
                    $dailyRate = 0.0;
                    $daysWorked = 0;
                    $fixedAllowances = (float) ($input['fixed_allowances'] ?? $membership->fixed_allowances ?? 0.0);
                    $variableAllowances = (float) ($input['variable_allowances'] ?? $membership->variable_allowances ?? 0.0);
                    $ptkpStatus = (string) ($input['tax_ptkp_status'] ?? $membership->tax_ptkp_status ?? 'TK/0');
                    $bpjsTkEnabled = isset($input['bpjs_tk_enabled']) ? (bool) $input['bpjs_tk_enabled'] : (bool) $membership->bpjs_tk_enabled;
                    $bpjsKesEnabled = isset($input['bpjs_kes_enabled']) ? (bool) $input['bpjs_kes_enabled'] : (bool) $membership->bpjs_kes_enabled;

                    $calc = $this->payrollCalculationService->calculateMonthlyPayroll(
                        baseSalary: $baseSalary,
                        fixedAllowances: $fixedAllowances,
                        variableAllowances: $variableAllowances,
                        overtimePay: $overtimePay,
                        commissions: $commissions,
                        loanDeduction: $loanDeduction,
                        otherDeductions: $otherDeductions,
                        ptkpStatus: $ptkpStatus,
                        bpjsTkEnabled: $bpjsTkEnabled,
                        bpjsKesEnabled: $bpjsKesEnabled,
                        includeThr: $includeThr,
                        joinDate: $joinDateCarbon,
                        cutoffDate: $cutoffDate
                    );

                    $thrAmount = (float) ($calc['earnings']['thr_amount'] ?? 0.0);
                    $grossPay = (float) $calc['earnings']['employee_gross_pay'];
                    $bpjsTkCompany = (float) ($calc['bpjs']['bpjs_tk']['total_company'] ?? 0.0);
                    $bpjsTkEmployee = (float) ($calc['bpjs']['bpjs_tk']['total_employee'] ?? 0.0);
                    $bpjsKesCompany = (float) ($calc['bpjs']['bpjs_kes']['total_company'] ?? 0.0);
                    $bpjsKesEmployee = (float) ($calc['bpjs']['bpjs_kes']['total_employee'] ?? 0.0);
                    $pph21Amount = (float) $calc['tax']['pph21_amount'];
                    $pph21Category = (string) $calc['tax']['ter_category'];
                    $pph21Rate = (float) $calc['tax']['ter_rate_percent'];
                    $itemTakeHome = (float) $calc['take_home_pay'];
                    $itemCompanyCost = (float) $calc['company_total_cost'];
                    $itemTotalDeductions = (float) $calc['deductions']['total_deductions'];
                }

                $payrollItem = new PayrollItem([
                    'payroll_id' => $payroll->id,
                    'business_id' => $business->id,
                    'user_id' => $userId,
                    'employee_name' => $user->name ?? 'Staf',
                    'job_title' => $jobTitle,
                    'employment_type' => $employmentType,
                    'join_date' => $joinDate,
                    'tenure_months' => $tenureMonths,
                    'base_salary' => $baseSalary,
                    'daily_rate' => $dailyRate,
                    'days_worked' => $daysWorked,
                    'fixed_allowances' => $fixedAllowances,
                    'variable_allowances' => $variableAllowances,
                    'overtime_pay' => $overtimePay,
                    'commissions' => $commissions,
                    'thr_amount' => $thrAmount,
                    'gross_pay' => $grossPay,
                    'bpjs_tk_company' => $bpjsTkCompany,
                    'bpjs_tk_employee' => $bpjsTkEmployee,
                    'bpjs_kes_company' => $bpjsKesCompany,
                    'bpjs_kes_employee' => $bpjsKesEmployee,
                    'pph21_amount' => $pph21Amount,
                    'pph21_ter_category' => $pph21Category,
                    'pph21_ter_rate' => $pph21Rate,
                    'loan_deduction' => $loanDeduction,
                    'other_deductions' => $otherDeductions,
                    'total_deductions' => $itemTotalDeductions,
                    'take_home_pay' => $itemTakeHome,
                    'company_total_cost' => $itemCompanyCost,
                    'bank_name' => $membership->bank_name,
                    'bank_account_number' => $membership->bank_account_number,
                    'bank_account_holder' => $membership->bank_account_holder ?? $user->name,
                    'whatsapp_number' => $membership->whatsapp_number ?? $user->phone,
                    'status' => $payroll->status,
                    'calculation_payload' => $calc,
                    'notes' => $input['notes'] ?? null,
                ]);

                $payrollItem->save();

                // Aggregate
                $totalGross += $grossPay;
                $totalDeductions += $itemTotalDeductions;
                $totalTakeHome += $itemTakeHome;
                $totalCompanyCost += $itemCompanyCost;
                $totalBpjsCompany += ($bpjsTkCompany + $bpjsKesCompany);
                $totalBpjsEmployee += ($bpjsTkEmployee + $bpjsKesEmployee);
                $totalPph21 += $pph21Amount;
                $totalLoanDeduction += $loanDeduction;
                $employeeCount++;
            }

            $payroll->update([
                'total_gross_pay' => round($totalGross, 2),
                'total_deductions' => round($totalDeductions, 2),
                'total_take_home_pay' => round($totalTakeHome, 2),
                'total_company_cost' => round($totalCompanyCost, 2),
                'total_bpjs_company' => round($totalBpjsCompany, 2),
                'total_bpjs_employee' => round($totalBpjsEmployee, 2),
                'total_pph21' => round($totalPph21, 2),
                'total_loan_deductions' => round($totalLoanDeduction, 2),
                'total_employees_count' => $employeeCount,
            ]);

            return $payroll->load('items.user');
        });
    }

    /**
     * Approve a payroll batch.
     */
    public function approvePayroll(Payroll $payroll, User $approver): Payroll
    {
        if ($payroll->status === Payroll::STATUS_PAID) {
            throw new RuntimeException("Penggajian ini sudah dibayar sebelumnya.");
        }

        $payroll->update([
            'status' => Payroll::STATUS_APPROVED,
            'approved_by' => $approver->id,
        ]);

        $payroll->items()->update(['status' => PayrollItem::STATUS_APPROVED]);

        return $payroll;
    }

    /**
     * Mark payroll as paid, deduct active loans, mark commissions as paid,
     * and automatically register an operational expense in finance.
     */
    public function markPayrollPaid(
        Payroll $payroll,
        string $paymentMethod = 'bank_transfer',
        ?string $notes = null,
        ?User $operator = null
    ): Payroll {
        return DB::transaction(function () use ($payroll, $paymentMethod, $notes, $operator): Payroll {
            if ($payroll->status === Payroll::STATUS_PAID) {
                return $payroll;
            }

            $now = now();

            $payroll->update([
                'status' => Payroll::STATUS_PAID,
                'paid_at' => $now,
                'payment_method' => $paymentMethod,
                'notes' => $notes ?? $payroll->notes,
            ]);

            $payroll->items()->update(['status' => PayrollItem::STATUS_PAID]);

            // Process each item for loans and commissions
            foreach ($payroll->items as $item) {
                // 1. Deduct loan balance if deduction exists
                if ($item->loan_deduction > 0) {
                    $activeLoan = EmployeeLoan::where('business_id', $payroll->business_id)
                        ->where('user_id', $item->user_id)
                        ->where('status', EmployeeLoan::STATUS_ACTIVE)
                        ->where('remaining_balance', '>', 0)
                        ->first();

                    if ($activeLoan) {
                        $newBalance = max(0.0, (float) $activeLoan->remaining_balance - (float) $item->loan_deduction);
                        $activeLoan->remaining_balance = $newBalance;
                        if ($newBalance <= 0) {
                            $activeLoan->status = EmployeeLoan::STATUS_COMPLETED;
                        }
                        $activeLoan->save();
                    }
                }

                // 2. Mark commissions paid for this period
                EmployeeCommission::where('business_id', $payroll->business_id)
                    ->where('user_id', $item->user_id)
                    ->whereIn('status', [EmployeeCommission::STATUS_EARNED, EmployeeCommission::STATUS_APPROVED])
                    ->whereMonth('date', $payroll->period_month)
                    ->whereYear('date', $payroll->period_year)
                    ->update(['status' => EmployeeCommission::STATUS_PAID]);
            }

            // 3. Register automated operational expense in finance
            $expNumber = 'EXP-PAY-' . $payroll->period_year . str_pad((string) $payroll->period_month, 2, '0', STR_PAD_LEFT);
            $existingExp = Expense::where('business_id', $payroll->business_id)
                ->where('expense_number', $expNumber)
                ->first();

            if (! $existingExp) {
                Expense::create([
                    'id' => (string) Str::uuid(),
                    'business_id' => $payroll->business_id,
                    'expense_number' => $expNumber,
                    'expense_date' => $now->toDateString(),
                    'category' => 'Gaji & Karyawan',
                    'amount' => $payroll->total_company_cost > 0 ? $payroll->total_company_cost : $payroll->total_take_home_pay,
                    'payment_method' => in_array($paymentMethod, ['cash', 'bank_transfer', 'petty_cash'], true) ? $paymentMethod : 'bank_transfer',
                    'description' => "Pembayaran {$payroll->title} ({$payroll->total_employees_count} orang)",
                    'recorded_by' => $operator?->id ?? $payroll->approved_by ?? $payroll->processed_by,
                ]);
            }

            return $payroll;
        });
    }

    /**
     * Delete a draft payroll.
     */
    public function deleteDraft(Payroll $payroll): bool
    {
        if ($payroll->status !== Payroll::STATUS_DRAFT) {
            throw new RuntimeException("Hanya draf penggajian yang dapat dihapus.");
        }

        return (bool) $payroll->delete();
    }

    /**
     * Build WhatsApp-ready digital payslip message.
     */
    public function buildWhatsAppSlipMessage(PayrollItem $item): string
    {
        $payroll = $item->payroll;
        $business = $payroll?->business ?? Business::find($item->business_id);
        $bizName = $business?->name ?? 'COOCA Business';
        $period = $payroll ? $payroll->formatted_period : Carbon::now()->translatedFormat('F Y');

        $fmt = fn (float $n) => 'Rp ' . number_format($n, 0, ',', '.');

        $msg = "📄 *SLIP GAJI KARYAWAN*\n";
        $msg .= "🏢 *{$bizName}*\n";
        $msg .= "🗓️ *Periode:* {$period}\n";
        $msg .= "────────────────────\n";
        $msg .= "👤 *Nama:* {$item->employee_name}\n";
        $msg .= "💼 *Jabatan:* " . ($item->job_title ?: 'Staf') . "\n";
        $msg .= "📌 *Status:* " . ($item->employment_type === 'daily_worker' ? 'Pekerja Harian' : 'Karyawan') . "\n";
        $msg .= "────────────────────\n";
        $msg .= "*PENERIMAAN:*\n";

        if ($item->employment_type === 'daily_worker') {
            $msg .= "• Upah Harian: {$fmt((float) $item->daily_rate)} x {$item->days_worked} hr = {$fmt((float) ($item->daily_rate * $item->days_worked))}\n";
        } else {
            $msg .= "• Gaji Pokok: {$fmt((float) $item->base_salary)}\n";
            if ($item->fixed_allowances > 0) {
                $msg .= "• Tunjangan Tetap: {$fmt((float) $item->fixed_allowances)}\n";
            }
            if ($item->variable_allowances > 0) {
                $msg .= "• Tunjangan Tambahan: {$fmt((float) $item->variable_allowances)}\n";
            }
        }

        if ($item->overtime_pay > 0) {
            $msg .= "• Upah Lembur: {$fmt((float) $item->overtime_pay)}\n";
        }
        if ($item->commissions > 0) {
            $msg .= "• Komisi/Bonus: {$fmt((float) $item->commissions)}\n";
        }
        if ($item->thr_amount > 0) {
            $msg .= "• THR Prorata: {$fmt((float) $item->thr_amount)}\n";
        }

        $msg .= "💰 *Total Bruto:* {$fmt((float) $item->gross_pay)}\n";
        $msg .= "────────────────────\n";
        $msg .= "*POTONGAN:*\n";

        $totalDeduct = (float) $item->total_deductions;
        if ($item->bpjs_tk_employee > 0) {
            $msg .= "• BPJS TK Karyawan: {$fmt((float) $item->bpjs_tk_employee)}\n";
        }
        if ($item->bpjs_kes_employee > 0) {
            $msg .= "• BPJS Kes Karyawan: {$fmt((float) $item->bpjs_kes_employee)}\n";
        }
        if ($item->pph21_amount > 0) {
            $msg .= "• PPh 21 TER: {$fmt((float) $item->pph21_amount)}\n";
        }
        if ($item->loan_deduction > 0) {
            $msg .= "• Cicilan Kasbon/Pinjaman: {$fmt((float) $item->loan_deduction)}\n";
        }
        if ($item->other_deductions > 0) {
            $msg .= "• Potongan Lain: {$fmt((float) $item->other_deductions)}\n";
        }

        if ($totalDeduct <= 0) {
            $msg .= "• Tidak ada potongan.\n";
        } else {
            $msg .= "🔻 *Total Potongan:* {$fmt($totalDeduct)}\n";
        }

        $msg .= "────────────────────\n";
        $msg .= "💵 *GAJI BERSIH (TAKE HOME PAY):*\n";
        $msg .= "👉 *{$fmt((float) $item->take_home_pay)}*\n";
        $msg .= "────────────────────\n";

        if ($item->bank_account_number) {
            $msg .= "💳 *Transfer ke:* {$item->bank_name} - {$item->bank_account_number} ({$item->bank_account_holder})\n";
        }

        $publicUrl = route('public.payslip', $item->payslip_token);
        $msg .= "\n🔗 *Tautan Slip Digital:* {$publicUrl}\n";
        $msg .= "\n_Slip gaji ini dibuat secara otomatis melalui sistem Cooca ERP._";

        return $msg;
    }
}
