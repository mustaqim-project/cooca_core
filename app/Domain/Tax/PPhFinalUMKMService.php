<?php

declare(strict_types=1);

namespace App\Domain\Tax;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\PosOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class PPhFinalUMKMService
{
    public const UMKM_TAX_RATE = 0.005; // 0.5% sesuai PP 55/2022
    public const INDIVIDUAL_TAX_FREE_THRESHOLD = 500_000_000.0; // Batas bebas pajak Rp 500 Juta per tahun untuk Orang Pribadi
    public const MAX_UMKM_GROSS_REVENUE_CAP = 4_800_000_000.0; // Maksimal omzet 4.8 Miliar / tahun

    /**
     * Hitung PPh Final UMKM 0.5% untuk suatu bulan dengan memperhitungkan kumulatif omzet tahun berjalan.
     *
     * @param float $currentMonthRevenue Omzet bruto bulan berjalan
     * @param float $cumulativeRevenueBeforeCurrentMonth Total akumulasi omzet dari bulan-bulan sebelumnya pada tahun pajak yang sama
     * @param bool $isIndividualTaxpayer Apakah Wajib Pajak Orang Pribadi (true) atau Badan PT/CV (false)
     * @return array<string, mixed>
     */
    public function calculate(
        float $currentMonthRevenue,
        float $cumulativeRevenueBeforeCurrentMonth = 0.0,
        bool $isIndividualTaxpayer = true
    ): array {
        $monthlyRevenue = max(0.0, $currentMonthRevenue);
        $priorCumulative = max(0.0, $cumulativeRevenueBeforeCurrentMonth);
        $newCumulative = $priorCumulative + $monthlyRevenue;

        // Jika Badan Usaha (PT / CV), tidak ada fasilitas batas Rp 500 juta
        if (! $isIndividualTaxpayer) {
            $taxableRevenue = $monthlyRevenue;
            $taxAmount = round($taxableRevenue * self::UMKM_TAX_RATE, 2);

            return [
                'is_individual' => false,
                'monthly_revenue' => $monthlyRevenue,
                'prior_cumulative' => $priorCumulative,
                'new_cumulative' => $newCumulative,
                'threshold' => 0.0,
                'taxable_revenue' => $taxableRevenue,
                'tax_rate' => self::UMKM_TAX_RATE,
                'tax_rate_percent' => '0.5%',
                'tax_amount' => $taxAmount,
                'status' => 'Dikenakan PPh Final Badan 0.5% (PP 55/2022)',
                'is_under_threshold' => false,
                'exceeds_4_8_billion_cap' => $newCumulative > self::MAX_UMKM_GROSS_REVENUE_CAP,
            ];
        }

        // Untuk Orang Pribadi: Ambang batas Rp 500.000.000 per tahun
        $threshold = self::INDIVIDUAL_TAX_FREE_THRESHOLD;

        // Case 1: Akumulasi baru masih di bawah atau sama dengan Rp 500 juta -> Bebas Pajak (0%)
        if ($newCumulative <= $threshold) {
            return [
                'is_individual' => true,
                'monthly_revenue' => $monthlyRevenue,
                'prior_cumulative' => $priorCumulative,
                'new_cumulative' => $newCumulative,
                'threshold' => $threshold,
                'threshold_remaining' => max(0.0, $threshold - $newCumulative),
                'taxable_revenue' => 0.0,
                'tax_rate' => 0.0,
                'tax_rate_percent' => '0% (Fasilitas Bebas Pajak s/d Rp 500 Juta)',
                'tax_amount' => 0.0,
                'status' => 'Bebas PPh Final (Omzet kumulatif masih di bawah Rp 500 Juta)',
                'is_under_threshold' => true,
                'exceeds_4_8_billion_cap' => false,
            ];
        }

        // Case 2: Bulan ini menembus batas Rp 500 juta (Transisi)
        if ($priorCumulative < $threshold && $newCumulative > $threshold) {
            $taxableRevenue = $newCumulative - $threshold;
            $taxAmount = round($taxableRevenue * self::UMKM_TAX_RATE, 2);

            return [
                'is_individual' => true,
                'monthly_revenue' => $monthlyRevenue,
                'prior_cumulative' => $priorCumulative,
                'new_cumulative' => $newCumulative,
                'threshold' => $threshold,
                'threshold_remaining' => 0.0,
                'taxable_revenue' => $taxableRevenue,
                'non_taxable_portion' => $threshold - $priorCumulative,
                'tax_rate' => self::UMKM_TAX_RATE,
                'tax_rate_percent' => '0.5%',
                'tax_amount' => $taxAmount,
                'status' => 'Bulan transisi: Hanya selisih di atas batas Rp 500 Juta yang dikenakan tarif 0.5%',
                'is_under_threshold' => false,
                'exceeds_4_8_billion_cap' => $newCumulative > self::MAX_UMKM_GROSS_REVENUE_CAP,
            ];
        }

        // Case 3: Akumulasi sebelumnya sudah melewati Rp 500 juta -> Seluruh omzet bulan ini dikenakan 0.5%
        $taxableRevenue = $monthlyRevenue;
        $taxAmount = round($taxableRevenue * self::UMKM_TAX_RATE, 2);

        return [
            'is_individual' => true,
            'monthly_revenue' => $monthlyRevenue,
            'prior_cumulative' => $priorCumulative,
            'new_cumulative' => $newCumulative,
            'threshold' => $threshold,
            'threshold_remaining' => 0.0,
            'taxable_revenue' => $taxableRevenue,
            'tax_rate' => self::UMKM_TAX_RATE,
            'tax_rate_percent' => '0.5%',
            'tax_amount' => $taxAmount,
            'status' => 'Dikenakan PPh Final 0.5% (Threshold Rp 500 Juta telah terlampaui)',
            'is_under_threshold' => false,
            'exceeds_4_8_billion_cap' => $newCumulative > self::MAX_UMKM_GROSS_REVENUE_CAP,
        ];
    }

    /**
     * Hitung rekapitulasi omzet dan estimasi PPh Final UMKM untuk suatu bisnis dalam 1 tahun penuh.
     *
     * @return array<string, mixed>
     */
    public function getYearlySummary(Business $business, int $year, bool $isIndividual = true): array
    {
        $monthlyBreakdown = [];
        $cumulative = 0.0;
        $totalTaxPaid = 0.0;
        $totalRevenueYear = 0.0;

        for ($m = 1; $m <= 12; $m++) {
            // Ambil total omzet dari invoice berbayar & POS order lunas
            $invoiceRevenue = (float) Invoice::where('business_id', $business->id)
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $m)
                ->whereIn('status', [Invoice::STATUS_PAID, 'completed'])
                ->sum('total_amount');

            $posRevenue = (float) PosOrder::where('business_id', $business->id)
                ->where(function ($q) use ($year, $m) {
                    $q->where(function ($sub) use ($year, $m) {
                        $sub->whereNotNull('order_date')
                            ->whereYear('order_date', $year)
                            ->whereMonth('order_date', $m);
                    })->orWhere(function ($sub) use ($year, $m) {
                        $sub->whereNull('order_date')
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $m);
                    });
                })
                ->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_SERVED, PosOrder::STATUS_CONFIRMED])
                ->sum('total_amount');

            $monthGross = $invoiceRevenue + $posRevenue;
            $calc = $this->calculate($monthGross, $cumulative, $isIndividual);

            $monthlyBreakdown[$m] = [
                'month' => $m,
                'month_name' => Carbon::create($year, $m, 1)->translatedFormat('F'),
                'invoice_revenue' => $invoiceRevenue,
                'pos_revenue' => $posRevenue,
                'gross_revenue' => $monthGross,
                'taxable_revenue' => $calc['taxable_revenue'],
                'tax_amount' => $calc['tax_amount'],
                'cumulative_revenue' => $calc['new_cumulative'],
                'status' => $calc['status'],
            ];

            $cumulative = $calc['new_cumulative'];
            $totalTaxPaid += $calc['tax_amount'];
            $totalRevenueYear += $monthGross;
        }

        return [
            'business_id' => $business->id,
            'business_name' => $business->name,
            'year' => $year,
            'is_individual' => $isIndividual,
            'total_revenue_year' => $totalRevenueYear,
            'total_tax_year' => $totalTaxPaid,
            'threshold_used' => min($totalRevenueYear, self::INDIVIDUAL_TAX_FREE_THRESHOLD),
            'threshold_remaining' => max(0.0, self::INDIVIDUAL_TAX_FREE_THRESHOLD - $totalRevenueYear),
            'monthly_breakdown' => $monthlyBreakdown,
            'exceeds_4_8_billion_cap' => $totalRevenueYear > self::MAX_UMKM_GROSS_REVENUE_CAP,
        ];
    }
}
