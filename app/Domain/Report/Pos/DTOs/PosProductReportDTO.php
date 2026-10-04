<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

final class PosProductReportDTO
{
    /**
     * @param array<int, array<string, mixed>> $products
     * @param array<int, array<string, mixed>> $categories
     */
    public function __construct(
        public readonly array $products,
        public readonly array $categories
    ) {}
}
