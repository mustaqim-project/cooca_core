<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\User;

final class GetCustomerSummaryTool extends BaseAiTool
{
    public function __construct(
        private readonly AiSalesAnalysisService $salesService = new AiSalesAnalysisService()
    ) {}

    public function getName(): string
    {
        return 'GetCustomerSummary';
    }

    public function getDescription(): string
    {
        return 'Mengambil ringkasan segmentasi pelanggan RFM (Champions, Loyal, At Risk, Hibernating/Dormant) dan riwayat aktivitas pelanggan.';
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $rfmData = $this->salesService->getCustomerBehaviorRfm($business);
        $totalCustomers = Customer::where('business_id', $business->id)->count();

        return [
            'total_registered_customers' => $totalCustomers,
            'summary' => $rfmData['summary'] ?? [],
            'segments' => $rfmData['segments'] ?? [],
        ];
    }
}
