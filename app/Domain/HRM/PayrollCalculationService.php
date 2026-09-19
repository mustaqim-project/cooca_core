<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use App\Domain\Tax\PPh21CalculationService;
use Carbon\Carbon;

final class PayrollCalculationService
{
    public function __construct(
        private readonly BPJSCalculationService $bpjsService,
        private readonly PPh21CalculationService $pph21Service,
        private readonly THRCalculationService $thrService
    ) {}

    /**
     * Hitung penggajian bulanan untuk Karyawan Tetap atau Kontrak.
     *
     * @param float $baseSalary Gaji pokok bulanan
     * @param float $fixedAllowances Tunjangan tetap (jabatan, tempat tinggal)
     * @param float $variableAllowances Tunjangan tidak tetap (transport, makan)
     * @param float $overtimePay Upah lembur
     * @param float $commissions Total komisi penjualan / SPK
     * @param float $loanDeduction Potongan cicilan pinjaman / kasbon
     * @param float $otherDeductions Potongan lainnya (keterlambatan, sanksi)
     * @param string $ptkpStatus Status PTKP ('TK/0', 'K/1', dll)
     * @param bool $bpjsTkEnabled Apakah terdaftar BPJS Ketenagakerjaan
     * @param bool $bpjsKesEnabled Apakah terdaftar BPJS Kesehatan
     * @param float $jkkRate Tarif JKK
     * @param bool $includeThr Apakah bulan ini mencakup pencairan THR
     * @param Carbon|string|null $joinDate Tanggal bergabung (jika includeThr)
     * @param Carbon|string|null $cutoffDate Tanggal cutoff THR
     * @return array<string, mixed>
     */
    public function calculateMonthlyPayroll(
        float $baseSalary,
        float $fixedAllowances = 0.0,
        float $variableAllowances = 0.0,
        float $overtimePay = 0.0,
        float $commissions = 0.0,
        float $loanDeduction = 0.0,
        float $otherDeductions = 0.0,
        string $ptkpStatus = 'TK/0',
        bool $bpjsTkEnabled = true,
        bool $bpjsKesEnabled = true,
        float $jkkRate = BPJSCalculationService::JKK_RATE_VERY_LOW,
        bool $includeThr = false,
        Carbon|string|null $joinDate = null,
        Carbon|string|null $cutoffDate = null
    ): array {
        $baseWageForBpjs = $baseSalary + $fixedAllowances;

        // 1. Kalkulasi BPJS
        $bpjs = $this->bpjsService->calculate($baseWageForBpjs, $bpjsTkEnabled, $bpjsKesEnabled, $jkkRate);

        // 2. Kalkulasi THR jika ada
        $thrDetails = null;
        $thrAmount = 0.0;
        if ($includeThr && $joinDate !== null) {
            $thrDetails = $this->thrService->calculate($joinDate, $cutoffDate, $baseWageForBpjs, false);
            $thrAmount = (float) ($thrDetails['thr_amount'] ?? 0.0);
        }

        // 3. Penghasilan Bruto Reguler Karyawan
        $employeeGrossPay = $baseSalary + $fixedAllowances + $variableAllowances + $overtimePay + $commissions + $thrAmount;

        // 4. Penghasilan Bruto untuk Pajak PPh 21 (TER)
        // Menurut PMK 168/2023: Bruto Pajak = Penghasilan Bruto Karyawan + Premi JKK, JKM, BPJS Kes yang dibayarkan Perusahaan
        $taxableGrossForTer = $employeeGrossPay + $bpjs['summary']['taxable_benefit_addition'];

        // 5. Kalkulasi PPh 21 TER
        $pph21 = $this->pph21Service->calculateMonthlyTer($taxableGrossForTer, $ptkpStatus);
        $pph21Amount = (float) $pph21['pph21_amount'];

        // 6. Total Potongan Karyawan
        $bpjsEmployeeDeduction = (float) $bpjs['summary']['total_employee_deduction'];
        $totalDeductions = $bpjsEmployeeDeduction + $pph21Amount + $loanDeduction + $otherDeductions;

        // 7. Take Home Pay (Gaji Bersih Karyawan)
        $takeHomePay = max(0.0, $employeeGrossPay - $totalDeductions);

        // 8. Total Beban Upah Perusahaan (Company Total Labor Cost)
        $companyCost = $employeeGrossPay + (float) $bpjs['summary']['total_company_paid'];

        return [
            'employment_type' => 'permanent_or_contract',
            'earnings' => [
                'base_salary' => $baseSalary,
                'fixed_allowances' => $fixedAllowances,
                'variable_allowances' => $variableAllowances,
                'overtime_pay' => $overtimePay,
                'commissions' => $commissions,
                'thr_amount' => $thrAmount,
                'thr_details' => $thrDetails,
                'employee_gross_pay' => $employeeGrossPay,
            ],
            'bpjs' => $bpjs,
            'tax' => [
                'taxable_gross_for_ter' => $taxableGrossForTer,
                'ptkp_status' => $ptkpStatus,
                'ter_category' => $pph21['ter_category'],
                'ter_rate_percent' => $pph21['ter_rate_percent'],
                'pph21_amount' => $pph21Amount,
            ],
            'deductions' => [
                'bpjs_employee' => $bpjsEmployeeDeduction,
                'pph21' => $pph21Amount,
                'loan_installment' => $loanDeduction,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
            ],
            'take_home_pay' => round($takeHomePay, 2),
            'company_total_cost' => round($companyCost, 2),
        ];
    }

    /**
     * Hitung penggajian untuk Pekerja Harian Lepas (Daily Worker).
     *
     * @param float $dailyRate Upah harian yang disepakati
     * @param int $daysWorked Jumlah hari kerja pada periode ini
     * @param float $overtimePay Upah lembur harian/kumulatif
     * @param float $incentives Insentif harian / tips
     * @param float $loanDeduction Potongan kasbon
     * @param float $priorCumulativeWageThisMonth Akumulasi upah yang telah diterima bulan ini sebelum pembayaran hari ini
     * @return array<string, mixed>
     */
    public function calculateDailyWorkerPayroll(
        float $dailyRate,
        int $daysWorked,
        float $overtimePay = 0.0,
        float $incentives = 0.0,
        float $loanDeduction = 0.0,
        float $priorCumulativeWageThisMonth = 0.0
    ): array {
        $daysWorked = max(1, $daysWorked);
        $totalDailyWage = $dailyRate * $daysWorked;
        $grossPay = $totalDailyWage + $overtimePay + $incentives;

        // Hitung PPh 21 Daily Worker
        $newCumulativeWage = $priorCumulativeWageThisMonth + $grossPay;
        $dailyTaxCalc = $this->pph21Service->calculateDailyWorker($grossPay / $daysWorked, $newCumulativeWage);
        $pph21Amount = (float) ($dailyTaxCalc['pph21_daily_amount'] * $daysWorked);

        $totalDeductions = $pph21Amount + $loanDeduction;
        $takeHomePay = max(0.0, $grossPay - $totalDeductions);

        return [
            'employment_type' => 'daily_worker',
            'earnings' => [
                'daily_rate' => $dailyRate,
                'days_worked' => $daysWorked,
                'total_daily_wage' => $totalDailyWage,
                'overtime_pay' => $overtimePay,
                'incentives' => $incentives,
                'gross_pay' => $grossPay,
            ],
            'tax' => [
                'cumulative_wage_this_month' => $newCumulativeWage,
                'category' => $dailyTaxCalc['category'],
                'tax_rate_percent' => $dailyTaxCalc['tax_rate_percent'],
                'pph21_amount' => round($pph21Amount, 2),
            ],
            'deductions' => [
                'pph21' => round($pph21Amount, 2),
                'loan_installment' => $loanDeduction,
                'total_deductions' => round($totalDeductions, 2),
            ],
            'take_home_pay' => round($takeHomePay, 2),
            'company_total_cost' => round($grossPay, 2),
        ];
    }
}
