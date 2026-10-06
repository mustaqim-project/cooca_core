<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\User;

final class AnalyticsGetSalesForecastTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'analytics_get_sales_forecast';
    }

    public function getDescription(): string
    {
        return 'Mengambil prediksi omzet masa depan dan rekomendasi stok reorder menggunakan engine analitik COOCA.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:analytics:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'forecast_days' => [
                    'type' => 'integer',
                    'default' => 14,
                    'description' => 'Jumlah hari proyeksi ke depan (misal: 7, 14, 30 hari).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $forecastDays = max(1, (int) ($arguments['forecast_days'] ?? 14));

        $aiService = app(AiSalesAnalysisService::class);
        $forecast = $aiService->getSalesForecasting($business, $forecastDays);
        $stockPrediction = $aiService->getStockPredictionAndReorder($business);

        return [
            'status' => 'success',
            'forecast_days' => $forecastDays,
            'forecast' => $forecast,
            'reorder_recommendations' => $stockPrediction,
        ];
    }
}
