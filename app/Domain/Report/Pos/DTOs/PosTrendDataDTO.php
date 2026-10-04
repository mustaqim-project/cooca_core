<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

final class PosTrendDataDTO
{
    /**
     * @param array<int, array<string, mixed>> $dailyTrend
     * @param array<int, array<string, mixed>> $hourlyHeatmap
     */
    public function __construct(
        public readonly array $dailyTrend,
        public readonly array $hourlyHeatmap
    ) {}
}
