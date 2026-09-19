<?php

declare(strict_types=1);

namespace App\Domain\HRM;

final class BPJSCalculationService
{
    // Maksimum batas upah bulanan (Wage Cap) sesuai regulasi Indonesia
    public const JP_MAX_WAGE_CAP = 10_042_300.0; // Batas upah Jaminan Pensiun 2024-2026
    public const KES_MAX_WAGE_CAP = 12_000_000.0; // Batas upah BPJS Kesehatan

    // Persentase JHT
    public const JHT_COMPANY_RATE = 0.037; // 3.7%
    public const JHT_EMPLOYEE_RATE = 0.020; // 2.0%

    // Persentase JKM
    public const JKM_COMPANY_RATE = 0.003; // 0.3%

    // Persentase JP
    public const JP_COMPANY_RATE = 0.020; // 2.0%
    public const JP_EMPLOYEE_RATE = 0.010; // 1.0%

    // Persentase BPJS Kesehatan
    public const KES_COMPANY_RATE = 0.040; // 4.0%
    public const KES_EMPLOYEE_RATE = 0.010; // 1.0%

    // Tingkat Risiko JKK
    public const JKK_RATE_VERY_LOW = 0.0024;  // 0.24% (Kantor, Administrasi, Jasa)
    public const JKK_RATE_LOW = 0.0054;       // 0.54% (Retail, Pertokoan)
    public const JKK_RATE_MEDIUM = 0.0089;    // 0.89% (Restoran, Cafe, Bengkel)
    public const JKK_RATE_HIGH = 0.0127;      // 1.27% (Manufaktur, Perakitan)
    public const JKK_RATE_VERY_HIGH = 0.0174; // 1.74% (Fabrikasi, Konstruksi Berat)

    /**
     * Hitung iuran komprehensif BPJS Ketenagakerjaan dan Kesehatan.
     *
     * @param float $wage Gaji Pokok + Tunjangan Tetap
     * @param bool $bpjsTkEnabled Apakah BPJS Ketenagakerjaan aktif untuk karyawan ini
     * @param bool $bpjsKesEnabled Apakah BPJS Kesehatan aktif untuk karyawan ini
     * @param float $jkkRate Tarif JKK (default 0.24%)
     * @return array<string, mixed>
     */
    public function calculate(
        float $wage,
        bool $bpjsTkEnabled = true,
        bool $bpjsKesEnabled = true,
        float $jkkRate = self::JKK_RATE_VERY_LOW
    ): array {
        $wage = max(0.0, $wage);

        // 1. BPJS Ketenagakerjaan
        $jhtCompany = 0.0;
        $jhtEmployee = 0.0;
        $jkkCompany = 0.0;
        $jkmCompany = 0.0;
        $jpCompany = 0.0;
        $jpEmployee = 0.0;

        if ($bpjsTkEnabled) {
            // JHT: Dihitung dari upah penuh tanpa cap
            $jhtCompany = round($wage * self::JHT_COMPANY_RATE, 2);
            $jhtEmployee = round($wage * self::JHT_EMPLOYEE_RATE, 2);

            // JKK & JKM: 100% ditanggung perusahaan
            $jkkCompany = round($wage * $jkkRate, 2);
            $jkmCompany = round($wage * self::JKM_COMPANY_RATE, 2);

            // JP: Dihitung dengan plafon maksimum Rp 10.042.300
            $jpBaseWage = min($wage, self::JP_MAX_WAGE_CAP);
            $jpCompany = round($jpBaseWage * self::JP_COMPANY_RATE, 2);
            $jpEmployee = round($jpBaseWage * self::JP_EMPLOYEE_RATE, 2);
        }

        // 2. BPJS Kesehatan
        $kesCompany = 0.0;
        $kesEmployee = 0.0;

        if ($bpjsKesEnabled) {
            // BPJS Kesehatan memiliki plafon maksimum Rp 12.000.000
            $kesBaseWage = min($wage, self::KES_MAX_WAGE_CAP);
            $kesCompany = round($kesBaseWage * self::KES_COMPANY_RATE, 2);
            $kesEmployee = round($kesBaseWage * self::KES_EMPLOYEE_RATE, 2);
        }

        $totalCompanyTk = $jhtCompany + $jkkCompany + $jkmCompany + $jpCompany;
        $totalEmployeeTk = $jhtEmployee + $jpEmployee;

        $totalCompanyPaid = $totalCompanyTk + $kesCompany;
        $totalEmployeeDeduction = $totalEmployeeTk + $kesEmployee;

        // Komponen yang menambah penghasilan bruto untuk PPh 21 (Benefit dari Perusahaan)
        $taxableBenefitAddition = $jkkCompany + $jkmCompany + $kesCompany;

        // Komponen iuran yang dapat mengurangi penghasilan bruto untuk perhitungan PPh 21 tahunan
        $taxDeductibleEmployee = $jhtEmployee + $jpEmployee;

        return [
            'base_wage' => $wage,
            'bpjs_tk' => [
                'enabled' => $bpjsTkEnabled,
                'jht_company' => $jhtCompany,
                'jht_employee' => $jhtEmployee,
                'jkk_company' => $jkkCompany,
                'jkk_rate' => $jkkRate,
                'jkm_company' => $jkmCompany,
                'jp_company' => $jpCompany,
                'jp_employee' => $jpEmployee,
                'jp_base_wage' => min($wage, self::JP_MAX_WAGE_CAP),
                'total_company' => $totalCompanyTk,
                'total_employee' => $totalEmployeeTk,
            ],
            'bpjs_kes' => [
                'enabled' => $bpjsKesEnabled,
                'kes_company' => $kesCompany,
                'kes_employee' => $kesEmployee,
                'kes_base_wage' => min($wage, self::KES_MAX_WAGE_CAP),
                'total_company' => $kesCompany,
                'total_employee' => $kesEmployee,
            ],
            'summary' => [
                'total_company_paid' => $totalCompanyPaid,
                'total_employee_deduction' => $totalEmployeeDeduction,
                'grand_total_bpjs' => $totalCompanyPaid + $totalEmployeeDeduction,
                'taxable_benefit_addition' => $taxableBenefitAddition,
                'tax_deductible_employee' => $taxDeductibleEmployee,
            ],
        ];
    }
}
