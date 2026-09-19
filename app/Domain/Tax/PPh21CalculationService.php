<?php

declare(strict_types=1);

namespace App\Domain\Tax;

final class PPh21CalculationService
{
    // Nilai PTKP Tahunan Resmi (PMK 101/PMK.010/2016)
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

    // Pemetaan Status PTKP ke Kategori TER (PP 58/2023)
    public const TER_CATEGORIES = [
        'TK/0' => 'A',
        'TK/1' => 'A',
        'K/0'  => 'A',
        'TK/2' => 'B',
        'TK/3' => 'B',
        'K/1'  => 'B',
        'K/2'  => 'B',
        'K/3'  => 'C',
    ];

    // Batas Biaya Jabatan
    public const BIAYA_JABATAN_RATE = 0.05; // 5%
    public const BIAYA_JABATAN_MAX_MONTHLY = 500_000.0;
    public const BIAYA_JABATAN_MAX_ANNUAL = 6_000_000.0;

    /**
     * Dapatkan kategori TER (A, B, atau C) berdasarkan status PTKP.
     */
    public function getTerCategory(string $ptkpStatus): string
    {
        $status = strtoupper(trim($ptkpStatus));
        return self::TER_CATEGORIES[$status] ?? 'A';
    }

    /**
     * Dapatkan nilai nominal PTKP setahun.
     */
    public function getPtkpAnnual(string $ptkpStatus): float
    {
        $status = strtoupper(trim($ptkpStatus));
        return self::PTKP_VALUES[$status] ?? self::PTKP_VALUES['TK/0'];
    }

    /**
     * Hitung PPh 21 Bulanan (Masa Januari s/d November) menggunakan Tarif Efektif Rata-Rata (TER).
     *
     * @param float $grossMonthlyWage Total penghasilan bruto bulan bersangkutan (Gaji + Tunjangan + Lembur + Benefit BPJS Perusahaan)
     * @param string $ptkpStatus Status PTKP ('TK/0', 'K/1', dll)
     * @return array<string, mixed>
     */
    public function calculateMonthlyTer(float $grossMonthlyWage, string $ptkpStatus = 'TK/0'): array
    {
        $gross = max(0.0, $grossMonthlyWage);
        $category = $this->getTerCategory($ptkpStatus);
        $rate = $this->lookupTerRate($category, $gross);
        $pph21Amount = round($gross * $rate, 2);

        return [
            'gross_monthly_wage' => $gross,
            'ptkp_status' => $ptkpStatus,
            'ter_category' => $category,
            'ter_rate' => $rate,
            'ter_rate_percent' => ($rate * 100) . '%',
            'pph21_amount' => $pph21Amount,
            'net_take_home_adjustment' => $pph21Amount,
        ];
    }

    /**
     * Hitung PPh 21 Masa Pajak Terakhir (Desember atau Akhir Kontrak) menggunakan Tarif Progresif Pasal 17 UU HPP.
     *
     * @param float $annualGrossIncome Total akumulasi penghasilan bruto setahun
     * @param float $annualDeductibleContributions Akumulasi iuran JHT & JP karyawan setahun
     * @param float $totalPph21PaidJanToNov Total PPh 21 TER yang telah dipotong dari bulan 1 s/d 11
     * @param string $ptkpStatus Status PTKP
     * @return array<string, mixed>
     */
    public function calculateDecemberReconciliation(
        float $annualGrossIncome,
        float $annualDeductibleContributions,
        float $totalPph21PaidJanToNov,
        string $ptkpStatus = 'TK/0'
    ): array {
        $gross = max(0.0, $annualGrossIncome);
        $ptkp = $this->getPtkpAnnual($ptkpStatus);

        // 1. Hitung Biaya Jabatan (5% dari bruto, maks Rp 6.000.000 setahun)
        $biayaJabatan = min($gross * self::BIAYA_JABATAN_RATE, self::BIAYA_JABATAN_MAX_ANNUAL);

        // 2. Penghasilan Neto Setahun
        $netAnnualIncome = max(0.0, $gross - $biayaJabatan - $annualDeductibleContributions);

        // 3. Penghasilan Kena Pajak (PKP) - dibulatkan ke bawah per ribuan penuh sesuai UU KUP
        $rawPkp = max(0.0, $netAnnualIncome - $ptkp);
        $pkp = floor($rawPkp / 1000) * 1000;

        // 4. Hitung PPh 21 Tahunan Progresif Pasal 17
        $annualPph21Total = $this->calculatePasal17Tax($pkp);

        // 5. PPh 21 Masa Desember = PPh 21 Setahun - PPh 21 yang sudah dipotong (Jan-Nov)
        $pph21December = round($annualPph21Total - $totalPph21PaidJanToNov, 2);

        return [
            'annual_gross_income' => $gross,
            'biaya_jabatan' => round($biayaJabatan, 2),
            'annual_deductible_contributions' => $annualDeductibleContributions,
            'net_annual_income' => round($netAnnualIncome, 2),
            'ptkp_annual' => $ptkp,
            'pkp' => $pkp,
            'annual_pph21_total' => $annualPph21Total,
            'pph21_paid_jan_to_nov' => $totalPph21PaidJanToNov,
            'pph21_december' => $pph21December,
            'is_refund' => $pph21December < 0, // Jika lebih bayar (karyawan berhak refund kelebihan potong)
        ];
    }

    /**
     * Hitung PPh 21 untuk Pekerja Harian Lepas (Daily Worker) sesuai PMK 168/2023.
     *
     * @param float $dailyWage Upah harian yang diterima hari ini
     * @param float $cumulativeMonthlyWage Total kumulatif upah bulan berjalan termasuk hari ini
     * @return array<string, mixed>
     */
    public function calculateDailyWorker(float $dailyWage, float $cumulativeMonthlyWage): array
    {
        $daily = max(0.0, $dailyWage);
        $cumulative = max(0.0, $cumulativeMonthlyWage);

        // Ambang batas PMK 168/2023:
        // 1. Upah sehari s/d Rp 450.000 dan kumulatif bulanan <= Rp 4.500.000: Bebas Pajak (0%)
        if ($daily <= 450_000.0 && $cumulative <= 4_500_000.0) {
            return [
                'daily_wage' => $daily,
                'cumulative_monthly_wage' => $cumulative,
                'tax_rate' => 0.0,
                'tax_rate_percent' => '0%',
                'pph21_daily_amount' => 0.0,
                'category' => 'Bebas PPh 21 (Harian <= Rp 450rb & Kumulatif <= Rp 4.5jt)',
            ];
        }

        // 2. Upah sehari > Rp 450.000 tetapi kumulatif bulanan <= Rp 4.500.000:
        // Dasar pengenaan: (Upah Harian - Rp 450.000) x 0.5% (TER harian)
        if ($daily > 450_000.0 && $cumulative <= 4_500_000.0) {
            $taxableBase = $daily - 450_000.0;
            $taxAmount = round($taxableBase * 0.005, 2); // Tarif 0.5% TER Harian
            return [
                'daily_wage' => $daily,
                'cumulative_monthly_wage' => $cumulative,
                'taxable_base' => $taxableBase,
                'tax_rate' => 0.005,
                'tax_rate_percent' => '0.5%',
                'pph21_daily_amount' => $taxAmount,
                'category' => 'TER Harian (Selisih di atas Rp 450rb)',
            ];
        }

        // 3. Kumulatif bulanan > Rp 4.500.000 s/d Rp 10.200.000:
        // Tarif 0.5% dari seluruh penghasilan bruto harian
        if ($cumulative <= 10_200_000.0) {
            $taxAmount = round($daily * 0.005, 2);
            return [
                'daily_wage' => $daily,
                'cumulative_monthly_wage' => $cumulative,
                'taxable_base' => $daily,
                'tax_rate' => 0.005,
                'tax_rate_percent' => '0.5%',
                'pph21_daily_amount' => $taxAmount,
                'category' => 'TER Harian Kumulatif Menengah (0.5% Bruto)',
            ];
        }

        // 4. Kumulatif bulanan > Rp 10.200.000: Dikenakan Tarif TER Bulanan A
        $terLookup = $this->calculateMonthlyTer($cumulative, 'TK/0');
        $rate = $terLookup['ter_rate'];
        $taxAmount = round($daily * $rate, 2);

        return [
            'daily_wage' => $daily,
            'cumulative_monthly_wage' => $cumulative,
            'taxable_base' => $daily,
            'tax_rate' => $rate,
            'tax_rate_percent' => ($rate * 100) . '%',
            'pph21_daily_amount' => $taxAmount,
            'category' => 'TER Bulanan Pegawai Tidak Tetap (> Rp 10.2jt)',
        ];
    }

    /**
     * Hitung PPh Pasal 17 UU HPP Progresif.
     */
    public function calculatePasal17Tax(float $pkp): float
    {
        if ($pkp <= 0) {
            return 0.0;
        }

        $tax = 0.0;
        $remaining = $pkp;

        // Lapis 1: 0 s/d 60 juta (5%)
        $bracket1 = min($remaining, 60_000_000.0);
        $tax += $bracket1 * 0.05;
        $remaining -= $bracket1;

        if ($remaining <= 0) {
            return round($tax, 2);
        }

        // Lapis 2: di atas 60 juta s/d 250 juta (15% untuk porsi 190jt)
        $bracket2 = min($remaining, 190_000_000.0);
        $tax += $bracket2 * 0.15;
        $remaining -= $bracket2;

        if ($remaining <= 0) {
            return round($tax, 2);
        }

        // Lapis 3: di atas 250 juta s/d 500 juta (25% untuk porsi 250jt)
        $bracket3 = min($remaining, 250_000_000.0);
        $tax += $bracket3 * 0.25;
        $remaining -= $bracket3;

        if ($remaining <= 0) {
            return round($tax, 2);
        }

        // Lapis 4: di atas 500 juta s/d 5 Miliar (30% untuk porsi 4.5 M)
        $bracket4 = min($remaining, 4_500_000_000.0);
        $tax += $bracket4 * 0.30;
        $remaining -= $bracket4;

        if ($remaining <= 0) {
            return round($tax, 2);
        }

        // Lapis 5: di atas 5 Miliar (35%)
        $tax += $remaining * 0.35;

        return round($tax, 2);
    }

    /**
     * Cari tarif TER resmi berdasarkan Kategori dan Penghasilan Bruto (PP 58/2023).
     */
    public function lookupTerRate(string $category, float $gross): float
    {
        return match ($category) {
            'B' => $this->lookupTerRateB($gross),
            'C' => $this->lookupTerRateC($gross),
            default => $this->lookupTerRateA($gross),
        };
    }

    private function lookupTerRateA(float $gross): float
    {
        return match (true) {
            $gross <= 5_400_000.0 => 0.000,
            $gross <= 5_650_000.0 => 0.0025,
            $gross <= 5_950_000.0 => 0.005,
            $gross <= 6_300_000.0 => 0.0075,
            $gross <= 6_750_000.0 => 0.010,
            $gross <= 7_500_000.0 => 0.0125,
            $gross <= 8_550_000.0 => 0.015,
            $gross <= 9_650_000.0 => 0.0175,
            $gross <= 10_050_000.0 => 0.020,
            $gross <= 10_350_000.0 => 0.0225,
            $gross <= 10_700_000.0 => 0.025,
            $gross <= 11_050_000.0 => 0.030,
            $gross <= 11_600_000.0 => 0.035,
            $gross <= 12_500_000.0 => 0.040,
            $gross <= 13_750_000.0 => 0.050,
            $gross <= 15_100_000.0 => 0.060,
            $gross <= 16_950_000.0 => 0.070,
            $gross <= 19_750_000.0 => 0.080,
            $gross <= 24_150_000.0 => 0.090,
            $gross <= 26_450_000.0 => 0.100,
            $gross <= 28_000_000.0 => 0.110,
            $gross <= 30_050_000.0 => 0.120,
            $gross <= 32_400_000.0 => 0.130,
            $gross <= 35_400_000.0 => 0.140,
            $gross <= 39_100_000.0 => 0.150,
            $gross <= 43_850_000.0 => 0.160,
            $gross <= 47_800_000.0 => 0.170,
            $gross <= 51_400_000.0 => 0.180,
            $gross <= 56_300_000.0 => 0.190,
            $gross <= 62_200_000.0 => 0.200,
            $gross <= 68_600_000.0 => 0.210,
            $gross <= 77_500_000.0 => 0.220,
            $gross <= 89_000_000.0 => 0.230,
            $gross <= 103_000_000.0 => 0.240,
            $gross <= 125_000_000.0 => 0.250,
            $gross <= 157_000_000.0 => 0.260,
            $gross <= 206_000_000.0 => 0.270,
            $gross <= 337_000_000.0 => 0.280,
            $gross <= 454_000_000.0 => 0.290,
            $gross <= 550_000_000.0 => 0.300,
            $gross <= 695_000_000.0 => 0.310,
            $gross <= 910_000_000.0 => 0.320,
            $gross <= 1_400_000_000.0 => 0.330,
            default => 0.340,
        };
    }

    private function lookupTerRateB(float $gross): float
    {
        return match (true) {
            $gross <= 6_200_000.0 => 0.000,
            $gross <= 6_500_000.0 => 0.0025,
            $gross <= 6_850_000.0 => 0.005,
            $gross <= 7_300_000.0 => 0.0075,
            $gross <= 9_200_000.0 => 0.010,
            $gross <= 10_750_000.0 => 0.015,
            $gross <= 11_250_000.0 => 0.020,
            $gross <= 11_600_000.0 => 0.025,
            $gross <= 12_600_000.0 => 0.030,
            $gross <= 13_600_000.0 => 0.040,
            $gross <= 14_950_000.0 => 0.050,
            $gross <= 16_400_000.0 => 0.060,
            $gross <= 18_450_000.0 => 0.070,
            $gross <= 21_850_000.0 => 0.080,
            $gross <= 26_000_000.0 => 0.090,
            $gross <= 27_700_000.0 => 0.100,
            $gross <= 29_350_000.0 => 0.110,
            $gross <= 31_450_000.0 => 0.120,
            $gross <= 33_950_000.0 => 0.130,
            $gross <= 37_100_000.0 => 0.140,
            $gross <= 41_100_000.0 => 0.150,
            $gross <= 45_800_000.0 => 0.160,
            $gross <= 49_500_000.0 => 0.170,
            $gross <= 53_800_000.0 => 0.180,
            $gross <= 58_500_000.0 => 0.190,
            $gross <= 64_000_000.0 => 0.200,
            $gross <= 71_000_000.0 => 0.210,
            $gross <= 80_000_000.0 => 0.220,
            $gross <= 93_000_000.0 => 0.230,
            $gross <= 109_000_000.0 => 0.240,
            $gross <= 129_000_000.0 => 0.250,
            $gross <= 163_000_000.0 => 0.260,
            $gross <= 211_000_000.0 => 0.270,
            $gross <= 374_000_000.0 => 0.280,
            $gross <= 459_000_000.0 => 0.290,
            $gross <= 555_000_000.0 => 0.300,
            $gross <= 704_000_000.0 => 0.310,
            $gross <= 957_000_000.0 => 0.320,
            $gross <= 1_405_000_000.0 => 0.330,
            default => 0.340,
        };
    }

    private function lookupTerRateC(float $gross): float
    {
        return match (true) {
            $gross <= 6_600_000.0 => 0.000,
            $gross <= 6_950_000.0 => 0.0025,
            $gross <= 7_350_000.0 => 0.005,
            $gross <= 7_800_000.0 => 0.0075,
            $gross <= 8_850_000.0 => 0.010,
            $gross <= 9_800_000.0 => 0.0125,
            $gross <= 10_950_000.0 => 0.015,
            $gross <= 11_200_000.0 => 0.0175,
            $gross <= 12_050_000.0 => 0.020,
            $gross <= 12_950_000.0 => 0.030,
            $gross <= 14_150_000.0 => 0.040,
            $gross <= 15_550_000.0 => 0.050,
            $gross <= 17_050_000.0 => 0.060,
            $gross <= 19_500_000.0 => 0.070,
            $gross <= 22_700_000.0 => 0.080,
            $gross <= 26_600_000.0 => 0.090,
            $gross <= 28_100_000.0 => 0.100,
            $gross <= 30_100_000.0 => 0.110,
            $gross <= 32_600_000.0 => 0.120,
            $gross <= 35_400_000.0 => 0.130,
            $gross <= 38_900_000.0 => 0.140,
            $gross <= 43_000_000.0 => 0.150,
            $gross <= 47_400_000.0 => 0.160,
            $gross <= 51_200_000.0 => 0.170,
            $gross <= 55_800_000.0 => 0.180,
            $gross <= 60_600_000.0 => 0.190,
            $gross <= 66_400_000.0 => 0.200,
            $gross <= 73_000_000.0 => 0.210,
            $gross <= 83_000_000.0 => 0.220,
            $gross <= 97_000_000.0 => 0.230,
            $gross <= 114_000_000.0 => 0.240,
            $gross <= 134_000_000.0 => 0.250,
            $gross <= 169_000_000.0 => 0.260,
            $gross <= 216_000_000.0 => 0.270,
            $gross <= 385_000_000.0 => 0.280,
            $gross <= 464_000_000.0 => 0.290,
            $gross <= 561_000_000.0 => 0.300,
            $gross <= 709_000_000.0 => 0.310,
            $gross <= 965_000_000.0 => 0.320,
            $gross <= 1_419_000_000.0 => 0.330,
            default => 0.340,
        };
    }
}
