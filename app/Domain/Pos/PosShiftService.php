<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Models\Business;
use App\Models\PosCashMovement;
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
     * Open a new shift with starting cash.
     */
    public function openShift(
        Business $business,
        User $user,
        float $openingCash = 0.0,
        ?string $posRegisterId = null,
        ?string $locationId = null,
        ?string $notes = null
    ): PosShift {
        $existing = PosShift::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->where('status', PosShift::STATUS_OPEN)
            ->first();

        if ($existing) {
            return $existing;
        }

        return PosShift::create([
            'business_id' => $business->id,
            'pos_register_id' => $posRegisterId,
            'location_id' => $locationId,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
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
     *     cash_in: float,
     *     cash_out: float,
     *     expected_cash: float,
     *     orders_count: int
     * }
     */
    public function getShiftSummary(PosShift $shift): array
    {
        $orders = $shift->orders()->where('status', 'completed')->with('payments')->get();
        $ordersCount = $orders->count();

        $cashSales = 0.0;
        $nonCashSales = 0.0;

        foreach ($orders as $order) {
            foreach ($order->payments as $payment) {
                if ($payment->payment_method === PosOrderPayment::METHOD_CASH) {
                    $cashSales += (float) $payment->amount;
                } else {
                    $nonCashSales += (float) $payment->amount;
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
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'expected_cash' => $expectedCash,
            'orders_count' => $ordersCount,
        ];
    }

    /**
     * Close a shift with actual cash count and calculate reconciliation difference.
     */
    public function closeShift(PosShift $shift, float $actualCash, ?string $notes = null): PosShift
    {
        $summary = $this->getShiftSummary($shift);
        $expected = $summary['expected_cash'];
        $diff = $actualCash - $expected;

        $shift->update([
            'closed_at' => now(),
            'closing_cash_actual' => $actualCash,
            'closing_cash_expected' => $expected,
            'cash_difference' => $diff,
            'total_cash_sales' => $summary['cash_sales'],
            'total_non_cash_sales' => $summary['non_cash_sales'],
            'total_cash_in' => $summary['cash_in'],
            'total_cash_out' => $summary['cash_out'],
            'status' => PosShift::STATUS_CLOSED,
            'notes' => $notes ?? $shift->notes,
        ]);

        return $shift;
    }
}
