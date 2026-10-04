<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

use Illuminate\Support\Collection;

final class PosFraudAuditDTO
{
    /**
     * @param Collection<int, array{
     *     cashier_id: string,
     *     cashier_name: string,
     *     total_orders: int,
     *     void_count: int,
     *     void_amount: float,
     *     void_rate_percent: float,
     *     reprint_count: int,
     *     risk_level: 'low'|'medium'|'high'|'critical'
     * }> $cashierRankings
     * @param Collection<int, array{
     *     reason: string,
     *     count: int,
     *     total_amount: float,
     *     percentage_of_voids: float
     * }> $voidReasons
     * @param Collection<int, array{
     *     order_id: string,
     *     order_number: string,
     *     order_date: string,
     *     cashier_name: string,
     *     total_amount: float,
     *     risk_type: string,
     *     severity: 'critical'|'warning'|'info',
     *     reason: ?string,
     *     print_count: int,
     *     notes: ?string
     * }> $suspiciousTransactions
     */
    public function __construct(
        public readonly int $totalOrders,
        public readonly int $totalVoidOrders,
        public readonly float $totalVoidAmount,
        public readonly float $voidRatePercent,
        public readonly int $voidAfterPrintCount,
        public readonly float $voidAfterPrintAmount,
        public readonly int $voidWithoutSupervisorCount,
        public readonly int $totalReprintEvents,
        public readonly Collection $cashierRankings,
        public readonly Collection $voidReasons,
        public readonly Collection $suspiciousTransactions
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_orders' => $this->totalOrders,
            'total_void_orders' => $this->totalVoidOrders,
            'total_void_amount' => $this->totalVoidAmount,
            'void_rate_percent' => $this->voidRatePercent,
            'void_after_print_count' => $this->voidAfterPrintCount,
            'void_after_print_amount' => $this->voidAfterPrintAmount,
            'void_without_supervisor_count' => $this->voidWithoutSupervisorCount,
            'total_reprint_events' => $this->totalReprintEvents,
            'cashier_rankings' => $this->cashierRankings->toArray(),
            'void_reasons' => $this->voidReasons->toArray(),
            'suspicious_transactions' => $this->suspiciousTransactions->toArray(),
        ];
    }
}
