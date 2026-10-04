<?php

declare(strict_types=1);

namespace App\Support\Math;

final class FinancialMath
{
    /**
     * Pembagian aman dengan perlindungan division by zero.
     */
    public static function safeDivide(float $numerator, float $denominator, float $fallback = 0.0): float
    {
        if (abs($denominator) < 0.0000001) {
            return $fallback;
        }

        return $numerator / $denominator;
    }

    /**
     * Hitung persentase margin keuntungan: (profit / revenue) * 100.
     * Jika revenue <= 0, return 0.0%.
     */
    public static function calculateMargin(float $profit, float $revenue): float
    {
        if ($revenue <= 0.0000001) {
            return 0.0;
        }

        return round(($profit / $revenue) * 100, 2);
    }

    /**
     * Hitung persentase pertumbuhan periode: ((current - previous) / previous) * 100.
     * Handle kondisi previous = 0.
     */
    public static function calculateGrowth(float $current, float $previous): float
    {
        if (abs($previous) < 0.0000001) {
            return $current > 0.0000001 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Pembulatan nominal finansial standar 2 desimal.
     */
    public static function roundFinancial(float $amount, int $precision = 2): float
    {
        return round($amount, $precision);
    }

    /**
     * Format quantity tanpa trailing zero berlebih (8.0000 => 8, 1.5000 => 1.5).
     */
    public static function formatQty(float $quantity): string
    {
        $formatted = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
