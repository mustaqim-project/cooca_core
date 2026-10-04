<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\User;

final class GetStockLevelsTool extends BaseAiTool
{
    public function __construct(
        private readonly AiSalesAnalysisService $salesService = new AiSalesAnalysisService()
    ) {}

    public function getName(): string
    {
        return 'GetStockLevels';
    }

    public function getDescription(): string
    {
        return 'Memeriksa status stok inventori produk, memproyeksikan hari habis (runout days), dan memberikan peringatan produk kritis.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $stockData = $this->salesService->getStockPredictionAndReorder($business);

        $critical = array_filter($stockData['products'] ?? [], fn($p) => ($p['urgency'] ?? '') === 'critical');
        $warning = array_filter($stockData['products'] ?? [], fn($p) => ($p['urgency'] ?? '') === 'warning');

        return [
            'total_monitored' => count($stockData['products'] ?? []),
            'critical_count' => count($critical),
            'warning_count' => count($warning),
            'critical_items' => array_values(array_slice($critical, 0, 8)),
            'warning_items' => array_values(array_slice($warning, 0, 8)),
        ];
    }
}
