<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\User;

final class GetSalesTrendTool extends BaseAiTool
{
    public function __construct(
        private readonly AiSalesAnalysisService $salesService = new AiSalesAnalysisService()
    ) {}

    public function getName(): string
    {
        return 'GetSalesTrend';
    }

    public function getDescription(): string
    {
        return 'Menganalisis tren penjualan dan memproyeksikan peramalan omzet untuk 7 hingga 14 hari ke depan berdasarkan dekomposisi musiman.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'days' => ['type' => 'integer', 'description' => 'Jumlah hari proyeksi ke depan (default: 7)'],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $days = (int) ($arguments['days'] ?? 7);
        $days = max(1, min(30, $days));

        return $this->salesService->getSalesForecasting($business, $days);
    }
}
