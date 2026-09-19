<?php

declare(strict_types=1);

namespace App\Domain\HRM;

use Carbon\Carbon;

final class THRCalculationService
{
    /**
     * Hitung Tunjangan Hari Raya (THR) Keagamaan sesuai Permenaker No. 6 Tahun 2016.
     *
     * @param Carbon|string $joinDate Tanggal bergabung karyawan
     * @param Carbon|string|null $cutoffDate Tanggal acuan pembagian THR / Hari Raya
     * @param float $monthlyWage Gaji Pokok + Tunjangan Tetap (atau estimasi untuk daily worker)
     * @param bool $isDailyWorker Apakah pekerja harian lepas
     * @param array<int, float> $dailyWorkerMonthlyHistory Riwayat pendapatan upah bulanan (khusus daily worker)
     * @return array<string, mixed>
     */
    public function calculate(
        Carbon|string $joinDate,
        Carbon|string|null $cutoffDate = null,
        float $monthlyWage = 0.0,
        bool $isDailyWorker = false,
        array $dailyWorkerMonthlyHistory = []
    ): array {
        $join = $joinDate instanceof Carbon ? $joinDate->copy()->startOfDay() : Carbon::parse($joinDate)->startOfDay();
        $cutoff = $cutoffDate instanceof Carbon ? $cutoffDate->copy()->startOfDay() : ($cutoffDate ? Carbon::parse($cutoffDate)->startOfDay() : Carbon::today()->startOfDay());

        if ($join->isAfter($cutoff)) {
            return [
                'join_date' => $join->toDateString(),
                'cutoff_date' => $cutoff->toDateString(),
                'service_years' => 0,
                'service_months' => 0,
                'service_days' => 0,
                'is_eligible' => false,
                'basis_wage' => 0.0,
                'thr_amount' => 0.0,
                'calculation_formula' => 'Belum bergabung pada tanggal cutoff THR.',
            ];
        }

        // Hitung masa kerja
        $diff = $join->diff($cutoff);
        $totalMonths = ($diff->y * 12) + $diff->m;
        $remainingDays = $diff->d;

        // Proporsi bulan desimal (contoh: 6 bulan 15 hari = 6.5 bulan)
        $fractionalMonths = $totalMonths + round($remainingDays / 30, 2);

        // Ambang batas kelayakan: Minimal 1 bulan kerja (Permenaker 6/2016 Pasal 2 ayat 1)
        if ($totalMonths < 1 && $fractionalMonths < 1.0) {
            return [
                'join_date' => $join->toDateString(),
                'cutoff_date' => $cutoff->toDateString(),
                'service_years' => $diff->y,
                'service_months' => $diff->m,
                'service_days' => $diff->d,
                'total_months' => $totalMonths,
                'fractional_months' => $fractionalMonths,
                'is_eligible' => false,
                'basis_wage' => $monthlyWage,
                'thr_amount' => 0.0,
                'calculation_formula' => 'Masa kerja kurang dari 1 bulan (belum memenuhi syarat Permenaker No. 6/2016).',
            ];
        }

        // Tentukan upah dasar perhitungan
        $effectiveWage = $monthlyWage;
        if ($isDailyWorker && ! empty($dailyWorkerMonthlyHistory)) {
            // Pekerja harian lepas: rata-rata upah bulanan selama masa kerja / 12 bulan terakhir
            $count = count($dailyWorkerMonthlyHistory);
            $effectiveWage = $count > 0 ? round(array_sum($dailyWorkerMonthlyHistory) / $count, 2) : $monthlyWage;
        }

        $isFullYear = $totalMonths >= 12;
        $thrAmount = 0.0;
        $formula = '';

        if ($isFullYear) {
            // Masa kerja >= 12 bulan: 1 bulan upah penuh
            $thrAmount = round($effectiveWage, 2);
            $formula = "Masa kerja {$totalMonths} bulan (>= 12 bulan): 1 x Rp " . number_format($effectiveWage, 0, ',', '.');
        } else {
            // Masa kerja 1 s/d < 12 bulan: prorata (Masa Kerja / 12) * Upah
            // Gunakan pembulatan bulan sesuai kebiasaan ketenagakerjaan atau fraksi bulan
            $effectiveMonthFactor = $fractionalMonths;
            $thrAmount = round(($effectiveMonthFactor / 12) * $effectiveWage, 2);
            $formula = "Prorata masa kerja ({$fractionalMonths} bulan / 12) x Rp " . number_format($effectiveWage, 0, ',', '.');
        }

        return [
            'join_date' => $join->toDateString(),
            'cutoff_date' => $cutoff->toDateString(),
            'service_years' => $diff->y,
            'service_months' => $diff->m,
            'service_days' => $diff->d,
            'total_months' => $totalMonths,
            'fractional_months' => $fractionalMonths,
            'is_eligible' => true,
            'is_full_year' => $isFullYear,
            'is_daily_worker' => $isDailyWorker,
            'basis_wage' => $effectiveWage,
            'thr_amount' => $thrAmount,
            'calculation_formula' => $formula,
        ];
    }
}
