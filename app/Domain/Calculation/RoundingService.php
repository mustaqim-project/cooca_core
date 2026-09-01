<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Models\Business;
use App\Support\Context;

final class RoundingService
{
    public const STRATEGY_ROUND = 'ROUND';

    public const STRATEGY_CEIL = 'CEIL';

    public const STRATEGY_FLOOR = 'FLOOR';

    public const STRATEGY_ROUND_50 = 'ROUND_50';

    public const STRATEGY_ROUND_100 = 'ROUND_100';

    public const STRATEGY_ROUND_500 = 'ROUND_500';

    public const STRATEGY_ROUND_1000 = 'ROUND_1000';

    /**
     * Apply configured business rounding strategy on final output values.
     * WARNING (§37 Blueprint): Never invoke this method during intermediate calculations.
     */
    public function apply(float $amount, ?Business $business = null): float
    {
        $biz = $business ?? Context::business();
        $strategy = $biz?->rounding_strategy ?? self::STRATEGY_ROUND;
        $precision = $biz?->currency_precision ?? 2;

        return match ($strategy) {
            self::STRATEGY_CEIL => ceil($amount * (10 ** $precision)) / (10 ** $precision),
            self::STRATEGY_FLOOR => floor($amount * (10 ** $precision)) / (10 ** $precision),
            self::STRATEGY_ROUND_50 => round($amount / 50) * 50,
            self::STRATEGY_ROUND_100 => round($amount / 100) * 100,
            self::STRATEGY_ROUND_500 => round($amount / 500) * 500,
            self::STRATEGY_ROUND_1000 => round($amount / 1000) * 1000,
            default => round($amount, $precision),
        };
    }
}
