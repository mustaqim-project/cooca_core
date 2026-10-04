<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos\DTOs;

use Illuminate\Support\Collection;

final class PosReconciliationResultDTO
{
    /**
     * @param Collection<int, array{
     *     type: string,
     *     severity: 'critical'|'warning'|'info',
     *     reference: string,
     *     amount: float,
     *     description: string,
     *     detected_at: string,
     *     action: string
     * }> $anomalies
     */
    public function __construct(
        public readonly float $totalOrdersAmount,
        public readonly float $totalPaymentsAmount,
        public readonly float $orderPaymentDiscrepancy,
        public readonly float $totalCashPayments,
        public readonly float $totalShiftExpectedCash,
        public readonly float $totalShiftActualCash,
        public readonly float $shiftCashDiscrepancy,
        public readonly int $unsettledOrdersCount,
        public readonly float $unsettledOrdersAmount,
        public readonly int $overpaidOrdersCount,
        public readonly float $overpaidOrdersAmount,
        public readonly Collection $anomalies,
        public readonly bool $isBalanced,
        public readonly int $totalShiftsAudited,
        public readonly int $balancedShiftsCount,
        public readonly int $shortShiftsCount,
        public readonly int $overShiftsCount
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_orders_amount' => $this->totalOrdersAmount,
            'total_payments_amount' => $this->totalPaymentsAmount,
            'order_payment_discrepancy' => $this->orderPaymentDiscrepancy,
            'total_cash_payments' => $this->totalCashPayments,
            'total_shift_expected_cash' => $this->totalShiftExpectedCash,
            'total_shift_actual_cash' => $this->totalShiftActualCash,
            'shift_cash_discrepancy' => $this->shiftCashDiscrepancy,
            'unsettled_orders_count' => $this->unsettledOrdersCount,
            'unsettled_orders_amount' => $this->unsettledOrdersAmount,
            'overpaid_orders_count' => $this->overpaidOrdersCount,
            'overpaid_orders_amount' => $this->overpaidOrdersAmount,
            'anomalies' => $this->anomalies->toArray(),
            'is_balanced' => $this->isBalanced,
            'total_shifts_audited' => $this->totalShiftsAudited,
            'balanced_shifts_count' => $this->balancedShiftsCount,
            'short_shifts_count' => $this->shortShiftsCount,
            'over_shifts_count' => $this->overShiftsCount,
        ];
    }
}
