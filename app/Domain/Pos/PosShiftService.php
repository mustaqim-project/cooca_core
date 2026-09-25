<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Models\Business;
use App\Models\PosCashMovement;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PosShiftService
{
    /**
     * Get currently active open shift for a user, or any open shift in the business if user is manager/supervisor.
     */
    public function getActiveShift(Business $business, User $user, ?string $locationId = null): ?PosShift
    {
        $query = PosShift::where('business_id', $business->id)
            ->where('status', PosShift::STATUS_OPEN);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $userShift = (clone $query)->where('user_id', $user->id)->first();
        if ($userShift) {
            return $userShift;
        }

        return $query->first();
    }

    /**
     * Open a new shift with starting cash and optional denominations breakdown.
     *
     * @param array<string, int> $openingDenominations
     */
    public function openShift(
        Business $business,
        User $user,
        float $openingCash = 0.0,
        ?string $posRegisterId = null,
        ?string $locationId = null,
        ?string $notes = null,
        array $openingDenominations = []
    ): PosShift {
        // 1. If register is specified, check if that register already has an active shift
        if ($posRegisterId) {
            $existingRegisterShift = PosShift::where('business_id', $business->id)
                ->where('pos_register_id', $posRegisterId)
                ->where('status', PosShift::STATUS_OPEN)
                ->first();

            if ($existingRegisterShift) {
                return $existingRegisterShift;
            }
        }

        // 2. Check if this cashier already has an active shift
        $existingUserShift = PosShift::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->where('status', PosShift::STATUS_OPEN)
            ->first();

        if ($existingUserShift) {
            return $existingUserShift;
        }

        // 3. Auto-calculate opening cash if denominations provided
        if (! empty($openingDenominations) && $openingCash <= 0.0) {
            $openingCash = $this->calculateDenominationTotal($openingDenominations);
        }

        return PosShift::create([
            'business_id' => $business->id,
            'pos_register_id' => $posRegisterId,
            'location_id' => $locationId,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'opening_denominations' => ! empty($openingDenominations) ? $openingDenominations : null,
            'status' => PosShift::STATUS_OPEN,
            'notes' => $notes,
        ]);
    }

    /**
     * Record cash-in or cash-out during shift.
     */
    public function recordCashMovement(
        PosShift $shift,
        User $user,
        string $type,
        float $amount,
        string $reason,
        ?string $notes = null
    ): PosCashMovement {
        if (! in_array($type, [PosCashMovement::TYPE_CASH_IN, PosCashMovement::TYPE_CASH_OUT], true)) {
            throw new InvalidArgumentException("Tipe pergerakan kas tidak valid: {$type}");
        }

        return DB::transaction(function () use ($shift, $user, $type, $amount, $reason, $notes) {
            $movement = PosCashMovement::create([
                'business_id' => $shift->business_id,
                'pos_shift_id' => $shift->id,
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'notes' => $notes,
            ]);

            if ($type === PosCashMovement::TYPE_CASH_IN) {
                $shift->increment('total_cash_in', $amount);
            } else {
                $shift->increment('total_cash_out', $amount);
            }

            return $movement;
        });
    }

    /**
     * Calculate live financial summary for a shift.
     *
     * @return array{
     *     opening_cash: float,
     *     cash_sales: float,
     *     non_cash_sales: float,
     *     gateway_sales: float,
     *     cash_in: float,
     *     cash_out: float,
     *     expected_cash: float,
     *     orders_count: int
     * }
     */
    public function getShiftSummary(PosShift $shift): array
    {
        $orders = $shift->orders()
            ->whereIn('status', [
                PosOrder::STATUS_COMPLETED,
                PosOrder::STATUS_CONFIRMED,
                PosOrder::STATUS_PREPARING,
                PosOrder::STATUS_READY,
                PosOrder::STATUS_SERVED,
            ])
            ->with('payments')
            ->get();
        $ordersCount = $orders->count();

        $cashSales = 0.0;
        $nonCashSales = 0.0;
        $gatewaySales = 0.0;

        foreach ($orders as $order) {
            foreach ($order->payments as $payment) {
                if ($payment->status !== 'paid') {
                    continue;
                }
                $amount = (float) $payment->amount;
                if ($payment->payment_method === PosOrderPayment::METHOD_CASH) {
                    $cashSales += $amount;
                } else {
                    $nonCashSales += $amount;
                    if ($payment->payment_method === PosOrderPayment::METHOD_QRIS_DYNAMIC || $order->payment_gateway === PosOrder::GATEWAY_TRIPAY) {
                        $gatewaySales += $amount;
                    }
                }
            }
        }

        $cashIn = (float) $shift->cashMovements()->where('type', PosCashMovement::TYPE_CASH_IN)->sum('amount');
        $cashOut = (float) $shift->cashMovements()->where('type', PosCashMovement::TYPE_CASH_OUT)->sum('amount');

        $opening = (float) $shift->opening_cash;
        $expectedCash = $opening + $cashSales + $cashIn - $cashOut;

        return [
            'opening_cash' => $opening,
            'cash_sales' => $cashSales,
            'non_cash_sales' => $nonCashSales,
            'gateway_sales' => $gatewaySales,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'expected_cash' => $expectedCash,
            'orders_count' => $ordersCount,
        ];
    }

    /**
     * Close a shift with actual cash count, optional denominations breakdown, and variance calculation.
     *
     * @param array<string, int> $closingDenominations
     */
    public function closeShift(
        PosShift $shift,
        float $actualCash,
        ?string $notes = null,
        array $closingDenominations = [],
        ?string $cashierNotes = null
    ): PosShift {
        if (! empty($closingDenominations) && $actualCash <= 0.0) {
            $actualCash = $this->calculateDenominationTotal($closingDenominations);
        }

        $summary = $this->getShiftSummary($shift);
        $expected = $summary['expected_cash'];
        $diff = $actualCash - $expected;

        $shift->update([
            'closed_at' => now(),
            'closing_cash_actual' => $actualCash,
            'closing_cash_expected' => $expected,
            'closing_denominations' => ! empty($closingDenominations) ? $closingDenominations : null,
            'cash_difference' => $diff,
            'total_cash_sales' => $summary['cash_sales'],
            'total_non_cash_sales' => $summary['non_cash_sales'],
            'total_cash_in' => $summary['cash_in'],
            'total_cash_out' => $summary['cash_out'],
            'status' => PosShift::STATUS_CLOSED,
            'notes' => $notes ?? $shift->notes,
            'cashier_notes' => $cashierNotes,
        ]);

        return $shift;
    }

    /**
     * Calculate total nominal cash from a denominations map.
     *
     * @param array<string, int> $denoms Key e.g. "100000", "50000", "20000", "coins" => Quantity or Total
     */
    public function calculateDenominationTotal(array $denoms): float
    {
        $total = 0.0;
        foreach ($denoms as $nominal => $qty) {
            if ($nominal === 'coins') {
                $total += (float) $qty;
                continue;
            }
            $cleanNominal = (float) preg_replace('/[^0-9]/', '', (string) $nominal);
            $cleanQty = (int) $qty;
            if ($cleanNominal > 0 && $cleanQty > 0) {
                $total += ($cleanNominal * $cleanQty);
            }
        }
        return $total;
    }
}
