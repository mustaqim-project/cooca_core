<?php

declare(strict_types=1);

namespace App\Domain\Tax;

use App\Domain\Report\FinancialReportService;
use App\Models\Business;
use Carbon\Carbon;

final class NetIncomeTaxService
{
    // Tarif PPh Badan sesuai UU HPP & Pasal 31E UU PPh
    public const CORPORATE_STANDARD_TAX_RATE = 0.22; // 22%
    public const CORPORATE_FACILITY_31E_RATE = 0.11; // 11% (50% dari 22% untuk omzet <= 4.8 Miliar)
    public const FACILITY_31E_THRESHOLD = 4_800_000_000.0; // Batas omzet fasilitas penuh Rp 4.8 Miliar
    public const FACILITY_31E_MAX_REVENUE = 50_000_000_000.0; // Batas fasilitas proporsional Rp 50 Miliar

    // Batasan Lapisan Tarif Progresif Orang Pribadi (Pasal 17 ayat 1a UU HPP No. 7/2021)
    public const BRACKET_1_CAP = 60_000_000.0; // 5% s/d 60 Juta
    public const BRACKET_2_CAP = 250_000_000.0; // 15% > 60 Juta s/d 250 Juta
    public const BRACKET_3_CAP = 500_000_000.0; // 25% > 250 Juta s/d 500 Juta
    public const BRACKET_4_CAP = 5_000_000_000.0; // 30% > 500 Juta s/d 5 Miliar
    public const RATE_BRACKET_1 = 0.05;
    public const RATE_BRACKET_2 = 0.15;
    public const RATE_BRACKET_3 = 0.25;
    public const RATE_BRACKET_4 = 0.30;
    public const RATE_BRACKET_5 = 0.35; // 35% > 5 Miliar

    // Standar Nilai PTKP (PMK 101/PMK.010/2016)
    public const PTKP_VALUES = [
        'TK/0' => 54_000_000.0,
        'TK/1' => 58_500_000.0,
        'TK/2' => 63_000_000.0,
        'TK/3' => 67_500_000.0,
        'K/0'  => 58_500_000.0,
        'K/1'  => 63_000_000.0,
        'K/2'  => 67_500_000.0,
        'K/3'  => 72_000_000.0,
    ];

    public function __construct(
        private readonly PPhFinalUMKMService $pphFinalService = new PPhFinalUMKMService(),
        private readonly ?FinancialReportService $financialReportService = null
    ) {}

    /**
     * Hitung Pajak Penghasilan (PPh) Berdasarkan Hasil Penjualan & Laba Bersih Usaha (Net Operating Profit).
     *
     * @param float $grossRevenue Total peredaran bruto / penjualan bersih
     * @param float $cogs Harga Pokok Penjualan / Biaya modal bahan & produk terjual
     * @param float $operatingExpenses Total biaya / pengeluaran operasional usaha
     * @param bool $isCorporate Apakah Wajib Pajak Badan PT/CV (true) atau Orang Pribadi (false)
     * @param string $ptkpStatus Status PTKP untuk Wajib Pajak Orang Pribadi ('TK/0', 'K/1', dll)
     * @param float $nppnRate Persentase Norma Penghitungan (opsional, jika menggunakan pencatatan norma 0.0-1.0)
     * @return array<string, mixed>
     */
    public function calculate(
        float $grossRevenue,
        float $cogs,
        float $operatingExpenses,
        bool $isCorporate = false,
        string $ptkpStatus = 'TK/0',
        float $nppnRate = 0.0
    ): array {
        $revenue = max(0.0, $grossRevenue);
        $costOfGoods = max(0.0, $cogs);
        $expenses = max(0.0, $operatingExpenses);

        $grossProfit = max(0.0, $revenue - $costOfGoods);
        $grossMargin = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0;

        // Laba Bersih Usaha Sebelum Pajak (Net Profit before Tax)
        $netOperatingIncome = $grossProfit - $expenses;
        $netMargin = $revenue > 0 ? round(($netOperatingIncome / $revenue) * 100, 2) : 0.0;

        // 1. PPh Final UMKM 0.5% (PP 55/2022) sebagai pembanding
        $umkmCalc = $this->pphFinalService->calculate($revenue, 0.0, ! $isCorporate);
        $pphFinalAmount = (float) $umkmCalc['tax_amount'];

        // 2. Perhitungan Pajak Penghasilan Berdasarkan Laba Bersih
        if ($isCorporate) {
            $corporateTax = $this->calculateCorporateTax($revenue, $netOperatingIncome);
            $taxScheme = 'PPh Badan (Pasal 17 jo. Pasal 31E UU PPh)';
            $taxAmount = $corporateTax['tax_amount'];
            $taxableIncome = $corporateTax['taxable_income'];
            $taxDetails = $corporateTax;
            $ptkpAmount = 0.0;
        } else {
            // Orang Pribadi
            $ptkpAmount = self::PTKP_VALUES[$ptkpStatus] ?? self::PTKP_VALUES['TK/0'];

            if ($nppnRate > 0.0) {
                // Skema Norma Penghitungan Penghasilan Neto (NPPN - Pasal 14 UU PPh)
                $netNorma = round($revenue * $nppnRate, 2);
                $pkp = max(0.0, floor(($netNorma - $ptkpAmount) / 1000) * 1000);
                $individualTax = $this->calculateProgressiveIndividualTax($pkp);
                $taxScheme = "NPPN Norma " . ($nppnRate * 100) . "% (Pasal 14 UU PPh)";
                $taxAmount = $individualTax['total_tax'];
                $taxableIncome = $pkp;
                $taxDetails = array_merge($individualTax, [
                    'nppn_rate' => $nppnRate,
                    'net_norma' => $netNorma,
                    'ptkp_amount' => $ptkpAmount,
                    'ptkp_status' => $ptkpStatus,
                ]);
            } else {
                // Skema Pembukuan Riil (Pasal 17 UU HPP)
                $pkp = $netOperatingIncome > $ptkpAmount
                    ? max(0.0, floor(($netOperatingIncome - $ptkpAmount) / 1000) * 1000)
                    : 0.0;

                $individualTax = $this->calculateProgressiveIndividualTax($pkp);
                $taxScheme = 'PPh Orang Pribadi Tarif Progresif (Pasal 17 UU HPP)';
                $taxAmount = $individualTax['total_tax'];
                $taxableIncome = $pkp;
                $taxDetails = array_merge($individualTax, [
                    'ptkp_amount' => $ptkpAmount,
                    'ptkp_status' => $ptkpStatus,
                ]);
            }
        }

        // 3. Analisis Rekomendasi Skema Pajak Paling Efisien (Tax Optimization)
        $taxDifference = $taxAmount - $pphFinalAmount;
        if ($netOperatingIncome <= 0) {
            $recommendation = 'Pembukuan Laba Bersih (Rugi Usaha: Pajak Rp 0)';
            $recommendedScheme = 'net_income';
            $taxSavings = $pphFinalAmount;
            $rationale = 'Usaha sedang mengalami rugi operasional. Dengan pembukuan riil, Anda tidak wajib membayar PPh terutang (Rp 0), sedangkan PPh Final 0.5% tetap menagih pajak dari omzet.';
        } elseif ($taxAmount < $pphFinalAmount) {
            $recommendation = 'Skema Pembukuan Laba Bersih';
            $recommendedScheme = 'net_income';
            $taxSavings = round($pphFinalAmount - $taxAmount, 2);
            $rationale = "Skema Laba Bersih lebih hemat Rp " . number_format($taxSavings, 0, ',', '.') . " dibanding PPh Final 0.5% karena margin laba operasional riil Anda relatif moderat.";
        } elseif ($taxAmount > $pphFinalAmount) {
            $recommendation = 'Skema PPh Final UMKM 0.5% (PP 55/2022)';
            $recommendedScheme = 'umkm_final';
            $taxSavings = round($taxAmount - $pphFinalAmount, 2);
            $rationale = "Skema PPh Final 0.5% lebih hemat Rp " . number_format($taxSavings, 0, ',', '.') . " karena perputaran omzet tinggi dengan margin laba bersih yang cukup tebal.";
        } else {
            $recommendation = 'Beban Pajak Setara';
            $recommendedScheme = 'equal';
            $taxSavings = 0.0;
            $rationale = 'Kedua skema menghasilkan nilai beban pajak yang sama.';
        }

        // Laba Bersih Setelah Pajak (Net Profit after Tax)
        $netProfitAfterTax = $netOperatingIncome - $taxAmount;

        return [
            'gross_revenue' => $revenue,
            'cogs' => $costOfGoods,
            'gross_profit' => $grossProfit,
            'gross_margin_percent' => $grossMargin,
            'operating_expenses' => $expenses,
            'net_operating_income' => $netOperatingIncome,
            'net_margin_percent' => $netMargin,
            'is_corporate' => $isCorporate,
            'ptkp_status' => $ptkpStatus,
            'ptkp_amount' => $ptkpAmount,
            'taxable_income' => $taxableIncome,
            'tax_scheme' => $taxScheme,
            'tax_amount' => $taxAmount,
            'effective_tax_rate_percent' => $revenue > 0 ? round(($taxAmount / $revenue) * 100, 2) : 0.0,
            'net_profit_after_tax' => $netProfitAfterTax,
            'tax_details' => $taxDetails,
            'comparison' => [
                'umkm_final_amount' => $pphFinalAmount,
                'net_income_tax_amount' => $taxAmount,
                'difference' => $taxDifference,
                'recommended_scheme' => $recommendedScheme,
                'recommendation_label' => $recommendation,
                'tax_savings' => $taxSavings,
                'rationale' => $rationale,
            ],
        ];
    }

    /**
     * Hitung Pajak Badan (PT/CV) sesuai UU PPh Pasal 17 jo. Pasal 31E.
     *
     * @return array<string, mixed>
     */
    private function calculateCorporateTax(float $revenue, float $netIncome): array
    {
        if ($netIncome <= 0) {
            return [
                'taxable_income' => 0.0,
                'facility_type' => 'Rugi Fiskal (Tidak Terutang PPh)',
                'facility_rate' => 0.0,
                'facility_tax' => 0.0,
                'non_facility_tax' => 0.0,
                'tax_amount' => 0.0,
                'is_loss' => true,
            ];
        }

        $pkp = floor($netIncome / 1000) * 1000;

        // 1. Omzet s/d 4.8 Miliar: Fasilitas Penuh Pasal 31E (Diskon 50% -> Tarif 11%)
        if ($revenue <= self::FACILITY_31E_THRESHOLD) {
            $tax = round($pkp * self::CORPORATE_FACILITY_31E_RATE, 2);

            return [
                'taxable_income' => $pkp,
                'facility_type' => 'Fasilitas Penuh Pasal 31E (Tarif 11% / Diskon 50% dari 22%)',
                'facility_rate' => self::CORPORATE_FACILITY_31E_RATE,
                'facility_pkp' => $pkp,
                'facility_tax' => $tax,
                'non_facility_pkp' => 0.0,
                'non_facility_tax' => 0.0,
                'tax_amount' => $tax,
                'is_loss' => false,
            ];
        }

        // 2. Omzet 4.8 M - 50 Miliar: Fasilitas Proporsional Pasal 31E
        if ($revenue <= self::FACILITY_31E_MAX_REVENUE) {
            $facilityPkp = round((self::FACILITY_31E_THRESHOLD / $revenue) * $pkp, 2);
            $nonFacilityPkp = max(0.0, $pkp - $facilityPkp);

            $facilityTax = round($facilityPkp * self::CORPORATE_FACILITY_31E_RATE, 2);
            $nonFacilityTax = round($nonFacilityPkp * self::CORPORATE_STANDARD_TAX_RATE, 2);
            $totalTax = $facilityTax + $nonFacilityTax;

            return [
                'taxable_income' => $pkp,
                'facility_type' => 'Fasilitas Proporsional Pasal 31E (Sebagian 11%, Sebagian 22%)',
                'facility_rate' => self::CORPORATE_FACILITY_31E_RATE,
                'facility_pkp' => $facilityPkp,
                'facility_tax' => $facilityTax,
                'non_facility_pkp' => $nonFacilityPkp,
                'non_facility_tax' => $nonFacilityTax,
                'tax_amount' => $totalTax,
                'is_loss' => false,
            ];
        }

        // 3. Omzet > 50 Miliar: Tarif Umum Penuh 22%
        $totalTax = round($pkp * self::CORPORATE_STANDARD_TAX_RATE, 2);

        return [
            'taxable_income' => $pkp,
            'facility_type' => 'Tarif Umum Normal 22% (Omzet di atas Rp 50 Miliar)',
            'facility_rate' => self::CORPORATE_STANDARD_TAX_RATE,
            'facility_pkp' => 0.0,
            'facility_tax' => 0.0,
            'non_facility_pkp' => $pkp,
            'non_facility_tax' => $totalTax,
            'tax_amount' => $totalTax,
            'is_loss' => false,
        ];
    }

    /**
     * Hitung Pajak Progresif Orang Pribadi sesuai Pasal 17 ayat (1) huruf a UU HPP.
     *
     * @return array<string, mixed>
     */
    private function calculateProgressiveIndividualTax(float $taxableIncome): array
    {
        $pkp = max(0.0, $taxableIncome);
        if ($pkp <= 0) {
            return [
                'total_tax' => 0.0,
                'brackets' => [],
                'is_tax_free' => true,
            ];
        }

        $remainingPkp = $pkp;
        $totalTax = 0.0;
        $brackets = [];

        // Bracket 1: s/d 60 Juta @ 5%
        if ($remainingPkp > 0) {
            $taxableInBracket = min($remainingPkp, self::BRACKET_1_CAP);
            $tax = round($taxableInBracket * self::RATE_BRACKET_1, 2);
            $brackets[] = [
                'bracket' => 'Lapisan 1 (s/d Rp 60 Juta)',
                'rate_percent' => '5%',
                'amount' => $taxableInBracket,
                'tax' => $tax,
            ];
            $totalTax += $tax;
            $remainingPkp -= $taxableInBracket;
        }

        // Bracket 2: > 60 Juta s/d 250 Juta (Range 190 Juta) @ 15%
        $bracket2Range = self::BRACKET_2_CAP - self::BRACKET_1_CAP;
        if ($remainingPkp > 0) {
            $taxableInBracket = min($remainingPkp, $bracket2Range);
            $tax = round($taxableInBracket * self::RATE_BRACKET_2, 2);
            $brackets[] = [
                'bracket' => 'Lapisan 2 (> Rp 60 Jt s/d Rp 250 Jt)',
                'rate_percent' => '15%',
                'amount' => $taxableInBracket,
                'tax' => $tax,
            ];
            $totalTax += $tax;
            $remainingPkp -= $taxableInBracket;
        }

        // Bracket 3: > 250 Juta s/d 500 Juta (Range 250 Juta) @ 25%
        $bracket3Range = self::BRACKET_3_CAP - self::BRACKET_2_CAP;
        if ($remainingPkp > 0) {
            $taxableInBracket = min($remainingPkp, $bracket3Range);
            $tax = round($taxableInBracket * self::RATE_BRACKET_3, 2);
            $brackets[] = [
                'bracket' => 'Lapisan 3 (> Rp 250 Jt s/d Rp 500 Jt)',
                'rate_percent' => '25%',
                'amount' => $taxableInBracket,
                'tax' => $tax,
            ];
            $totalTax += $tax;
            $remainingPkp -= $taxableInBracket;
        }

        // Bracket 4: > 500 Juta s/d 5 Miliar (Range 4.5 Miliar) @ 30%
        $bracket4Range = self::BRACKET_4_CAP - self::BRACKET_3_CAP;
        if ($remainingPkp > 0) {
            $taxableInBracket = min($remainingPkp, $bracket4Range);
            $tax = round($taxableInBracket * self::RATE_BRACKET_4, 2);
            $brackets[] = [
                'bracket' => 'Lapisan 4 (> Rp 500 Jt s/d Rp 5 Miliar)',
                'rate_percent' => '30%',
                'amount' => $taxableInBracket,
                'tax' => $tax,
            ];
            $totalTax += $tax;
            $remainingPkp -= $taxableInBracket;
        }

        // Bracket 5: > 5 Miliar @ 35%
        if ($remainingPkp > 0) {
            $taxableInBracket = $remainingPkp;
            $tax = round($taxableInBracket * self::RATE_BRACKET_5, 2);
            $brackets[] = [
                'bracket' => 'Lapisan 5 (> Rp 5 Miliar)',
                'rate_percent' => '35%',
                'amount' => $taxableInBracket,
                'tax' => $tax,
            ];
            $totalTax += $tax;
            $remainingPkp = 0.0;
        }

        return [
            'total_tax' => $totalTax,
            'brackets' => $brackets,
            'is_tax_free' => false,
        ];
    }

    /**
     * Hitung rekapitulasi 12 bulan penuh dari Laporan Laba Rugi riil bisnis di database tenant aktif.
     *
     * @return array<string, mixed>
     */
    public function getYearlyNetIncomeTaxSummary(
        Business $business,
        int $year,
        bool $isCorporate = false,
        string $ptkpStatus = 'TK/0'
    ): array {
        $financialService = $this->financialReportService ?? new FinancialReportService();
        $monthlyBreakdown = [];

        $totalGrossRevenueYear = 0.0;
        $totalCogsYear = 0.0;
        $totalGrossProfitYear = 0.0;
        $totalExpensesYear = 0.0;
        $totalNetOperatingIncomeYear = 0.0;
        $totalTaxPaidYear = 0.0;
        $totalUmkmFinalYear = 0.0;

        for ($m = 1; $m <= 12; $m++) {
            $startDate = Carbon::create($year, $m, 1)->startOfDay();
            $endDate = Carbon::create($year, $m, 1)->endOfMonth()->endOfDay();

            $incomeData = $financialService->getIncomeStatement($business, $startDate, $endDate);

            $grossRev = (float) ($incomeData['revenues']['net_sales'] ?? 0.0);
            $cogs = (float) ($incomeData['cogs']['total_cogs'] ?? 0.0);
            $expenses = (float) ($incomeData['expenses']['total'] ?? 0.0);
            $grossProfit = (float) ($incomeData['gross_profit']['amount'] ?? 0.0);
            $netIncome = (float) ($incomeData['net_profit']['amount'] ?? 0.0);

            // Hitung pajak bulan ini
            $calc = $this->calculate($grossRev, $cogs, $expenses, $isCorporate, $ptkpStatus);

            $monthlyBreakdown[$m] = [
                'month' => $m,
                'month_name' => $startDate->translatedFormat('F'),
                'revenue' => $grossRev,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'expenses' => $expenses,
                'net_income' => $netIncome,
                'tax_amount' => $calc['tax_amount'],
                'umkm_final_amount' => $calc['comparison']['umkm_final_amount'],
                'recommendation' => $calc['comparison']['recommended_scheme'],
            ];

            $totalGrossRevenueYear += $grossRev;
            $totalCogsYear += $cogs;
            $totalGrossProfitYear += $grossProfit;
            $totalExpensesYear += $expenses;
            $totalNetOperatingIncomeYear += $netIncome;
            $totalTaxPaidYear += $calc['tax_amount'];
            $totalUmkmFinalYear += $calc['comparison']['umkm_final_amount'];
        }

        // Perhitungan Pajak Tahunan Konsolidasi (Annual Consolidated Tax)
        $annualTaxCalculation = $this->calculate(
            $totalGrossRevenueYear,
            $totalCogsYear,
            $totalExpensesYear,
            $isCorporate,
            $ptkpStatus
        );

        return [
            'business_id' => $business->id,
            'business_name' => $business->name,
            'year' => $year,
            'is_corporate' => $isCorporate,
            'ptkp_status' => $ptkpStatus,
            'total_revenue_year' => $totalGrossRevenueYear,
            'total_cogs_year' => $totalCogsYear,
            'total_gross_profit_year' => $totalGrossProfitYear,
            'total_expenses_year' => $totalExpensesYear,
            'total_net_income_year' => $totalNetOperatingIncomeYear,
            'total_net_tax_year' => $annualTaxCalculation['tax_amount'],
            'total_umkm_final_year' => $totalUmkmFinalYear,
            'annual_calculation' => $annualTaxCalculation,
            'monthly_breakdown' => $monthlyBreakdown,
        ];
    }
}
