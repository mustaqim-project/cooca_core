<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\User;

final class GetAttendanceSummaryTool extends BaseAiTool
{
    public function __construct(
        private readonly AiSalesAnalysisService $salesService = new AiSalesAnalysisService()
    ) {}

    public function getName(): string
    {
        return 'GetAttendanceSummary';
    }

    public function getDescription(): string
    {
        return 'Mengambil ringkasan performa jam kerja karyawan, kepatuhan shift kasir, dan akurasi kas fisik oleh AI HR Agent.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $cashiers = $this->salesService->getCashierPerformance($business);

        return [
            'staff_count' => count($cashiers),
            'performance_scorecards' => array_slice($cashiers, 0, 5),
        ];
    }
}
