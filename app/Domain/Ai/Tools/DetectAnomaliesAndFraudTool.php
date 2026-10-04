<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\User;

final class DetectAnomaliesAndFraudTool extends BaseAiTool
{
    public function __construct(
        private readonly AiSalesAnalysisService $salesService = new AiSalesAnalysisService()
    ) {}

    public function getName(): string
    {
        return 'DetectAnomaliesAndFraud';
    }

    public function getDescription(): string
    {
        return 'Mendeteksi anomali operasional kasir, pembatalan transaksi berlebih (abnormal voids), dan ketidaksesuaian fisik kas shift.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        return $this->salesService->detectAnomaliesAndFraud($business);
    }
}
