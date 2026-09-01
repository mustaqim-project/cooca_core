<?php

declare(strict_types=1);

namespace App\Domain\Currency;

use App\Models\Currency;
use App\Models\ExchangeRate;
use InvalidArgumentException;

final class CurrencyConversionService
{
    /**
     * Convert an amount from one currency to another using exchange rates (§38 Blueprint).
     */
    public function convert(float $amount, Currency $from, Currency $to, ?string $snapshotDate = null): float
    {
        if ($from->id === $to->id) {
            return $amount;
        }

        /** @var ExchangeRate|null $directRate */
        $directRate = ExchangeRate::where('from_currency_id', $from->id)
            ->where('to_currency_id', $to->id)
            ->when($snapshotDate, fn ($q) => $q->where('snapshot_date', '<=', $snapshotDate))
            ->latest('snapshot_date')
            ->first();

        if ($directRate !== null) {
            return (float) ($amount * $directRate->rate);
        }

        /** @var ExchangeRate|null $inverseRate */
        $inverseRate = ExchangeRate::where('from_currency_id', $to->id)
            ->where('to_currency_id', $from->id)
            ->when($snapshotDate, fn ($q) => $q->where('snapshot_date', '<=', $snapshotDate))
            ->latest('snapshot_date')
            ->first();

        if ($inverseRate !== null && $inverseRate->rate > 0) {
            return (float) ($amount / $inverseRate->rate);
        }

        throw new InvalidArgumentException("Exchange rate from {$from->code} to {$to->code} is not configured.");
    }
}
