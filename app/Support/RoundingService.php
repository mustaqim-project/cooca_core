<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Currency;
use App\Models\Unit;

final class RoundingService
{
    /**
     * Round quantity based on Unit precision.
     */
    public static function roundQty(float $qty, Unit $unit): float
    {
        return round($qty, $unit->default_precision);
    }

    /**
     * Format quantity as string with Unit precision.
     */
    public static function formatQty(float $qty, Unit $unit): string
    {
        return number_format($qty, $unit->default_precision, '.', '');
    }

    /**
     * Round currency amount based on Currency decimal places.
     */
    public static function roundCurrency(float $amount, ?Currency $currency = null): float
    {
        $decimals = $currency ? $currency->decimal_places : 2;

        return round($amount, $decimals);
    }
}
