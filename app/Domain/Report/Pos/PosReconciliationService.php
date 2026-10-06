<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos;

use App\Domain\Report\Pos\DTOs\PosDiscountAnalyticsDTO;
use App\Domain\Report\Pos\DTOs\PosFraudAuditDTO;
use App\Domain\Report\Pos\DTOs\PosMarginAnalyticsDTO;
use App\Domain\Report\Pos\DTOs\PosReconciliationResultDTO;
use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\ProductCategory;
use App\Models\SalesReturn;
use App\Models\User;
use App\Support\Math\FinancialMath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PosReconciliationService
{
    /**
     * 1. 3-WAY RECONCILIATION ENGINE
     * Memvalidasi konsistensi antara Order Invoiced vs Payment Collected vs Cash Register Movement.
     */
    public function reconcile(PosReportFilterDTO $filter): PosReconciliationResultDTO
    {
        // 1. Order Query
        $orderQuery = PosOrder::query()
            ->where('business_id', $filter->businessId)
            ->whereDate('order_date', '>=', $filter->startDate->toDateString())
            ->whereDate('order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId !== null && $filter->locationId !== '') {
            $orderQuery->where('location_id', $filter->locationId);
        }
        if ($filter->userId !== null && $filter->userId !== '') {
            $orderQuery->where('user_id', $filter->userId);
        }
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $orderQuery->where('pos_shift_id', $filter->posShiftId);
        }

        $allOrders = (clone $orderQuery)->get();
        $completedOrders = $allOrders->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        
        $totalOrdersAmount = FinancialMath::roundFinancial((float) $completedOrders->sum('total_amount'));

        // 2. Payments Query
        $completedOrderIds = $completedOrders->pluck('id');
        $payments = PosOrderPayment::query()
            ->whereIn('pos_order_id', $completedOrderIds)
            ->whereIn('status', ['paid', 'success'])
            ->get();

        $totalPaymentsAmount = FinancialMath::roundFinancial((float) $payments->sum('amount'));
        $totalCashPayments = FinancialMath::roundFinancial((float) $payments->where('payment_method', PosOrderPayment::METHOD_CASH)->sum('amount'));
        $orderPaymentDiscrepancy = FinancialMath::roundFinancial($totalOrdersAmount - $totalPaymentsAmount);

        // 3. Shift Registers Query
        $shiftQuery = PosShift::query()
            ->where('business_id', $filter->businessId)
            ->whereDate('opened_at', '>=', $filter->startDate->toDateString())
            ->whereDate('opened_at', '<=', $filter->endDate->toDateString());

        if ($filter->locationId !== null && $filter->locationId !== '') {
            $shiftQuery->where('location_id', $filter->locationId);
        }
        if ($filter->userId !== null && $filter->userId !== '') {
            $shiftQuery->where('user_id', $filter->userId);
        }
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $shiftQuery->where('id', $filter->posShiftId);
        }

        $shifts = $shiftQuery->with(['user'])->get();
        $totalShiftsAudited = $shifts->count();
        $closedShifts = $shifts->where('status', PosShift::STATUS_CLOSED);

        $totalShiftExpectedCash = FinancialMath::roundFinancial((float) $closedShifts->sum('closing_cash_expected'));
        $totalShiftActualCash = FinancialMath::roundFinancial((float) $closedShifts->sum('closing_cash_actual'));
        $shiftCashDiscrepancy = FinancialMath::roundFinancial((float) $closedShifts->sum('cash_difference'));

        $balancedShiftsCount = $closedShifts->filter(fn(PosShift $s) => abs((float) ($s->cash_difference ?? 0.0)) < 0.01)->count();
        $shortShiftsCount = $closedShifts->filter(fn(PosShift $s) => (float) ($s->cash_difference ?? 0.0) < -0.01)->count();
        $overShiftsCount = $closedShifts->filter(fn(PosShift $s) => (float) ($s->cash_difference ?? 0.0) > 0.01)->count();

        // 4. Anomaly Detection & Flagging
        /** @var Collection<int, array{type: string, severity: 'critical'|'warning'|'info', reference: string, amount: float, description: string, detected_at: string, action: string}> $anomalies */
        $anomalies = collect();

        $unsettledOrders = $completedOrders->filter(fn(PosOrder $o) => (float) $o->paid_amount < ((float) $o->total_amount - 0.01));
        $unsettledOrdersCount = $unsettledOrders->count();
        $unsettledOrdersAmount = FinancialMath::roundFinancial((float) $unsettledOrders->sum(fn(PosOrder $o) => (float) $o->total_amount - (float) $o->paid_amount));

        foreach ($unsettledOrders as $unsettled) {
            $anomalies->push([
                'type' => 'UNSETTLED_ORDER',
                'severity' => 'critical',
                'reference' => (string) $unsettled->order_number,
                'amount' => FinancialMath::roundFinancial((float) $unsettled->total_amount - (float) $unsettled->paid_amount),
                'description' => "Pesanan status selesai tetapi pembayaran kurang Rp " . number_format((float) $unsettled->total_amount - (float) $unsettled->paid_amount, 0, ',', '.'),
                'detected_at' => $unsettled->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                'action' => 'Verifikasi bukti pembayaran / gateway gateway reference untuk nota ini.'
            ]);
        }

        $overpaidOrders = $completedOrders->filter(fn(PosOrder $o) => (float) $o->paid_amount > ((float) $o->total_amount + 0.01) && (float) $o->change_amount <= 0.01);
        $overpaidOrdersCount = $overpaidOrders->count();
        $overpaidOrdersAmount = FinancialMath::roundFinancial((float) $overpaidOrders->sum(fn(PosOrder $o) => (float) $o->paid_amount - (float) $o->total_amount));

        // Shift cash discrepancies
        foreach ($closedShifts as $shift) {
            $diff = (float) ($shift->cash_difference ?? 0.0);
            if ($diff < -0.01) {
                $cashierName = $shift->user?->name ?? 'Kasir';
                $anomalies->push([
                    'type' => 'SHORT_CASH',
                    'severity' => abs($diff) > 50000 ? 'critical' : 'warning',
                    'reference' => "Shift #{$shift->id}",
                    'amount' => abs($diff),
                    'description' => "Kas fisik tekor sebesar Rp " . number_format(abs($diff), 0, ',', '.') . " pada kasir {$cashierName}.",
                    'detected_at' => $shift->closed_at?->toDateTimeString() ?? now()->toDateTimeString(),
                    'action' => 'Lakukan konfirmasi fisik ke kasir bersangkutan dan audit mutasi kas register.'
                ]);
            } elseif ($diff > 0.01) {
                $cashierName = $shift->user?->name ?? 'Kasir';
                $anomalies->push([
                    'type' => 'OVER_CASH',
                    'severity' => 'warning',
                    'reference' => "Shift #{$shift->id}",
                    'amount' => $diff,
                    'description' => "Kas fisik berlebih sebesar Rp " . number_format($diff, 0, ',', '.') . " pada kasir {$cashierName}.",
                    'detected_at' => $shift->closed_at?->toDateTimeString() ?? now()->toDateTimeString(),
                    'action' => 'Periksa apakah ada transaksi tunai yang tidak diinput ke sistem POS.'
                ]);
            }
        }

        // Suspicious Voids after Print
        $voidedOrders = $allOrders->whereIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED]);
        foreach ($voidedOrders as $voidOrder) {
            $printCount = (int) ($voidOrder->print_count ?? 0);
            $wasPrinted = $printCount > 0 || $voidOrder->last_printed_at !== null;
            if ($wasPrinted) {
                $anomalies->push([
                    'type' => 'SUSPICIOUS_VOID_AFTER_PRINT',
                    'severity' => 'critical',
                    'reference' => (string) $voidOrder->order_number,
                    'amount' => (float) $voidOrder->total_amount,
                    'description' => "Struk nota telah dicetak {$printCount}x tetapi transaksi kemudian dibatalkan (Void). Potensi kebocoran uang kas.",
                    'detected_at' => $voidOrder->voided_at?->toDateTimeString() ?? $voidOrder->updated_at?->toDateTimeString() ?? now()->toDateTimeString(),
                    'action' => 'Audit rekaman CCTV saat transaksi dibatalkan dan cek kasir yang bertugas.'
                ]);
            }
        }

        $isBalanced = abs($orderPaymentDiscrepancy) < 0.01 
            && abs($shiftCashDiscrepancy) < 0.01 
            && $unsettledOrdersCount === 0;

        return new PosReconciliationResultDTO(
            totalOrdersAmount: $totalOrdersAmount,
            totalPaymentsAmount: $totalPaymentsAmount,
            orderPaymentDiscrepancy: $orderPaymentDiscrepancy,
            totalCashPayments: $totalCashPayments,
            totalShiftExpectedCash: $totalShiftExpectedCash,
            totalShiftActualCash: $totalShiftActualCash,
            shiftCashDiscrepancy: $shiftCashDiscrepancy,
            unsettledOrdersCount: $unsettledOrdersCount,
            unsettledOrdersAmount: $unsettledOrdersAmount,
            overpaidOrdersCount: $overpaidOrdersCount,
            overpaidOrdersAmount: $overpaidOrdersAmount,
            anomalies: $anomalies,
            isBalanced: $isBalanced,
            totalShiftsAudited: $totalShiftsAudited,
            balancedShiftsCount: $balancedShiftsCount,
            shortShiftsCount: $shortShiftsCount,
            overShiftsCount: $overShiftsCount
        );
    }

    /**
     * 2. VOID & FRAUD AUDIT ANALYTICS
     * Melakukan audit pembatalan transaksi, reprint struk, dan pemeringkatan risiko kasir.
     */
    public function auditVoidsAndFraud(PosReportFilterDTO $filter): PosFraudAuditDTO
    {
        $baseQuery = PosOrder::query()
            ->where('pos_orders.business_id', $filter->businessId)
            ->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
            ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId !== null && $filter->locationId !== '') {
            $baseQuery->where('pos_orders.location_id', $filter->locationId);
        }
        if ($filter->userId !== null && $filter->userId !== '') {
            $baseQuery->where('pos_orders.user_id', $filter->userId);
        }
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $baseQuery->where('pos_orders.pos_shift_id', $filter->posShiftId);
        }

        $allOrders = (clone $baseQuery)->with(['user', 'location'])->get();
        $totalOrders = $allOrders->count();

        $voidOrders = $allOrders->whereIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED]);
        $totalVoidOrders = $voidOrders->count();
        $totalVoidAmount = FinancialMath::roundFinancial((float) $voidOrders->sum('total_amount'));
        $voidRatePercent = FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $totalVoidOrders * 100.0, (float) max(1, $totalOrders)), 2);

        $voidAfterPrintOrders = $voidOrders->filter(fn(PosOrder $o) => ((int) ($o->print_count ?? 0)) > 0 || $o->last_printed_at !== null);
        $voidAfterPrintCount = $voidAfterPrintOrders->count();
        $voidAfterPrintAmount = FinancialMath::roundFinancial((float) $voidAfterPrintOrders->sum('total_amount'));

        $voidWithoutSupervisorCount = $voidOrders->filter(fn(PosOrder $o) => $o->supervisor_approved_by === null)->count();
        $totalReprintEvents = (int) $allOrders->sum(fn(PosOrder $o) => max(0, (int) ($o->reprint_count ?? 0)));

        // Cashier Void Rankings
        $cashierGroups = $allOrders->groupBy('user_id');
        /** @var Collection<int, array{cashier_id: string, cashier_name: string, total_orders: int, void_count: int, void_amount: float, void_rate_percent: float, reprint_count: int, risk_level: 'low'|'medium'|'high'|'critical'}> $cashierRankings */
        $cashierRankings = collect();

        foreach ($cashierGroups as $cashierId => $orders) {
            $cashierUser = $orders->first()?->user;
            $cashierName = $cashierUser?->name ?? 'Kasir #' . substr((string) $cashierId, 0, 6);
            $cTotalOrders = $orders->count();
            $cVoidOrders = $orders->whereIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED]);
            $cVoidCount = $cVoidOrders->count();
            $cVoidAmount = FinancialMath::roundFinancial((float) $cVoidOrders->sum('total_amount'));
            $cVoidRate = FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $cVoidCount * 100.0, (float) max(1, $cTotalOrders)), 2);
            $cReprintCount = (int) $orders->sum(fn(PosOrder $o) => max(0, (int) ($o->reprint_count ?? 0)));
            $cVoidAfterPrint = $cVoidOrders->filter(fn(PosOrder $o) => ((int) ($o->print_count ?? 0)) > 0 || $o->last_printed_at !== null)->count();

            $riskLevel = 'low';
            if ($cVoidAfterPrint > 0 || $cVoidRate >= 10.0) {
                $riskLevel = 'critical';
            } elseif ($cVoidRate >= 5.0 || $cReprintCount >= 5) {
                $riskLevel = 'high';
            } elseif ($cVoidRate >= 2.0 || $cReprintCount >= 2) {
                $riskLevel = 'medium';
            }

            $cashierRankings->push([
                'cashier_id' => (string) $cashierId,
                'cashier_name' => $cashierName,
                'total_orders' => $cTotalOrders,
                'void_count' => $cVoidCount,
                'void_amount' => $cVoidAmount,
                'void_rate_percent' => $cVoidRate,
                'reprint_count' => $cReprintCount,
                'risk_level' => $riskLevel,
            ]);
        }

        $cashierRankings = $cashierRankings->sortByDesc('void_rate_percent')->values();

        // Void Reasons Breakdown
        $voidReasons = $voidOrders->groupBy(fn(PosOrder $o) => !empty($o->void_reason) ? trim((string) $o->void_reason) : 'Tidak Disebutkan')
            ->map(function (Collection $group, string $reason) use ($totalVoidOrders) {
                $count = $group->count();
                $amount = FinancialMath::roundFinancial((float) $group->sum('total_amount'));
                $pct = FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $count * 100.0, (float) max(1, $totalVoidOrders)), 2);
                return [
                    'reason' => $reason,
                    'count' => $count,
                    'total_amount' => $amount,
                    'percentage_of_voids' => $pct,
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Suspicious Transactions
        /** @var Collection<int, array{order_id: string, order_number: string, order_date: string, cashier_name: string, total_amount: float, risk_type: string, severity: 'critical'|'warning'|'info', reason: ?string, print_count: int, notes: ?string}> $suspiciousTransactions */
        $suspiciousTransactions = collect();

        foreach ($voidOrders as $vOrder) {
            $printCount = (int) ($vOrder->print_count ?? 0);
            $hasPrinted = $printCount > 0 || $vOrder->last_printed_at !== null;
            $hasSupervisor = $vOrder->supervisor_approved_by !== null;

            if ($hasPrinted) {
                $suspiciousTransactions->push([
                    'order_id' => (string) $vOrder->id,
                    'order_number' => (string) $vOrder->order_number,
                    'order_date' => $vOrder->order_date?->toDateString() ?? $vOrder->created_at?->toDateString() ?? '',
                    'cashier_name' => $vOrder->user?->name ?? 'Kasir',
                    'total_amount' => (float) $vOrder->total_amount,
                    'risk_type' => 'Void Setelah Cetak Struk',
                    'severity' => 'critical',
                    'reason' => $vOrder->void_reason,
                    'print_count' => $printCount,
                    'notes' => 'Struk fisik sudah diserahkan ke pelanggan sebelum pesanan dibatalkan.',
                ]);
            } elseif (!$hasSupervisor && (float) $vOrder->total_amount > 100000) {
                $suspiciousTransactions->push([
                    'order_id' => (string) $vOrder->id,
                    'order_number' => (string) $vOrder->order_number,
                    'order_date' => $vOrder->order_date?->toDateString() ?? $vOrder->created_at?->toDateString() ?? '',
                    'cashier_name' => $vOrder->user?->name ?? 'Kasir',
                    'total_amount' => (float) $vOrder->total_amount,
                    'risk_type' => 'Void Bernilai Tinggi Tanpa Supervisor',
                    'severity' => 'warning',
                    'reason' => $vOrder->void_reason,
                    'print_count' => $printCount,
                    'notes' => 'Nominal void di atas Rp 100.000 tanpa approval supervisor.',
                ]);
            }
        }

        return new PosFraudAuditDTO(
            totalOrders: $totalOrders,
            totalVoidOrders: $totalVoidOrders,
            totalVoidAmount: $totalVoidAmount,
            voidRatePercent: $voidRatePercent,
            voidAfterPrintCount: $voidAfterPrintCount,
            voidAfterPrintAmount: $voidAfterPrintAmount,
            voidWithoutSupervisorCount: $voidWithoutSupervisorCount,
            totalReprintEvents: $totalReprintEvents,
            cashierRankings: $cashierRankings,
            voidReasons: $voidReasons,
            suspiciousTransactions: $suspiciousTransactions
        );
    }

    /**
     * 3. MARGIN & PROFITABILITY DEEP ANALYTICS
     * Mengaudit profitabilitas produk, loss leaders, missing COGS (Zero HPP), dan matriks kategori.
     */
    public function analyzeMarginAndProfitability(PosReportFilterDTO $filter): PosMarginAnalyticsDTO
    {
        $baseOrderQuery = PosOrder::query()
            ->where('pos_orders.business_id', $filter->businessId)
            ->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
            ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId !== null && $filter->locationId !== '') {
            $baseOrderQuery->where('pos_orders.location_id', $filter->locationId);
        }
        if ($filter->userId !== null && $filter->userId !== '') {
            $baseOrderQuery->where('pos_orders.user_id', $filter->userId);
        }
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $baseOrderQuery->where('pos_orders.pos_shift_id', $filter->posShiftId);
        }

        $orderIds = $baseOrderQuery->pluck('pos_orders.id');

        $items = PosOrderItem::query()
            ->whereIn('pos_order_id', $orderIds)
            ->with(['order', 'product.category'])
            ->get();

        $totalNetSales = FinancialMath::roundFinancial((float) $items->sum('total_price'));
        $totalCogs = FinancialMath::roundFinancial((float) $items->sum('total_hpp'));
        $totalGrossProfit = FinancialMath::roundFinancial($totalNetSales - $totalCogs);
        $overallGrossMarginPct = FinancialMath::calculateMargin($totalGrossProfit, $totalNetSales);

        // Group by product
        $productGroups = $items->groupBy('product_id');
        $productAnalytics = collect();
        $zeroCogsWarningProducts = collect();

        foreach ($productGroups as $productId => $pItems) {
            $first = $pItems->first();
            $productName = $first?->product_name ?? 'Produk';
            $productCode = $first?->product_code ?? ($first?->product?->code ?? '-');
            $categoryName = $first?->product?->category?->name ?? 'Tanpa Kategori';

            $qtySold = (float) $pItems->sum('quantity');
            $grossRev = FinancialMath::roundFinancial((float) $pItems->sum('total_price'));
            $cogs = FinancialMath::roundFinancial((float) $pItems->sum('total_hpp'));
            $profit = FinancialMath::roundFinancial($grossRev - $cogs);
            $marginPct = FinancialMath::calculateMargin($profit, $grossRev);

            // Cek Zero HPP
            $isZeroHpp = $cogs <= 0.001 && $grossRev > 0;
            if ($isZeroHpp) {
                $zeroCogsWarningProducts->push([
                    'product_id' => (string) $productId,
                    'product_name' => $productName,
                    'product_code' => $productCode,
                    'category_name' => $categoryName,
                    'quantity_sold' => $qtySold,
                    'gross_revenue' => $grossRev,
                    'unit_price' => (float) ($first?->unit_price ?? 0.0),
                ]);
            }

            $productAnalytics->push([
                'product_id' => (string) $productId,
                'product_name' => $productName,
                'product_code' => $productCode,
                'category_name' => $categoryName,
                'quantity_sold' => $qtySold,
                'gross_revenue' => $grossRev,
                'total_cogs' => $cogs,
                'gross_profit' => $profit,
                'margin_percent' => $marginPct,
            ]);
        }

        $zeroCogsItemsCount = $zeroCogsWarningProducts->count();
        $zeroCogsRevenue = FinancialMath::roundFinancial((float) $zeroCogsWarningProducts->sum('gross_revenue'));

        $topProfitableProducts = $productAnalytics->sortByDesc('gross_profit')->take(10)->values();
        $highestMarginProducts = $productAnalytics->filter(fn($p) => $p['total_cogs'] > 0)->sortByDesc('margin_percent')->take(10)->values();
        
        $lossLeaderProducts = $productAnalytics->filter(fn($p) => $p['gross_profit'] < 0 || $p['margin_percent'] < 0)
            ->map(function ($p) {
                $p['loss_amount'] = abs($p['gross_profit']);
                return $p;
            })
            ->sortByDesc('loss_amount')
            ->values();

        // Margin Tiers Breakdown (Produk dengan HPP terverifikasi)
        $highMargin = $productAnalytics->filter(fn($p) => $p['total_cogs'] > 0.001 && $p['margin_percent'] >= 50.0);
        $mediumMargin = $productAnalytics->filter(fn($p) => $p['total_cogs'] > 0.001 && $p['margin_percent'] >= 20.0 && $p['margin_percent'] < 50.0);
        $lowMargin = $productAnalytics->filter(fn($p) => $p['total_cogs'] > 0.001 && $p['margin_percent'] >= 0.0 && $p['margin_percent'] < 20.0);
        $negativeMargin = $productAnalytics->filter(fn($p) => $p['margin_percent'] < 0.0);

        $marginTiers = [
            'high_margin' => [
                'label' => 'Tinggi (≥ 50%)',
                'count' => $highMargin->count(),
                'revenue' => FinancialMath::roundFinancial((float) $highMargin->sum('gross_revenue')),
                'profit' => FinancialMath::roundFinancial((float) $highMargin->sum('gross_profit')),
                'share_percent' => FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $highMargin->sum('gross_revenue') * 100.0, (float) max(1, $totalNetSales)), 2),
            ],
            'medium_margin' => [
                'label' => 'Sehat (20% - 49.9%)',
                'count' => $mediumMargin->count(),
                'revenue' => FinancialMath::roundFinancial((float) $mediumMargin->sum('gross_revenue')),
                'profit' => FinancialMath::roundFinancial((float) $mediumMargin->sum('gross_profit')),
                'share_percent' => FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $mediumMargin->sum('gross_revenue') * 100.0, (float) max(1, $totalNetSales)), 2),
            ],
            'low_margin' => [
                'label' => 'Rendah (0% - 19.9%)',
                'count' => $lowMargin->count(),
                'revenue' => FinancialMath::roundFinancial((float) $lowMargin->sum('gross_revenue')),
                'profit' => FinancialMath::roundFinancial((float) $lowMargin->sum('gross_profit')),
                'share_percent' => FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $lowMargin->sum('gross_revenue') * 100.0, (float) max(1, $totalNetSales)), 2),
            ],
            'negative_margin' => [
                'label' => 'Minus / Rugi (< 0%)',
                'count' => $negativeMargin->count(),
                'revenue' => FinancialMath::roundFinancial((float) $negativeMargin->sum('gross_revenue')),
                'profit' => FinancialMath::roundFinancial((float) $negativeMargin->sum('gross_profit')),
                'share_percent' => FinancialMath::roundFinancial(FinancialMath::safeDivide((float) $negativeMargin->sum('gross_revenue') * 100.0, (float) max(1, $totalNetSales)), 2),
            ],
        ];

        // Category Profitability Breakdown
        $categoryGroups = $items->groupBy(fn(PosOrderItem $i) => $i->product?->category_id ?? 'uncategorized');
        $categoryProfitability = collect();

        foreach ($categoryGroups as $catId => $catItems) {
            $catName = $catItems->first()?->product?->category?->name ?? 'Tanpa Kategori';
            $catRev = FinancialMath::roundFinancial((float) $catItems->sum('total_price'));
            $catCogs = FinancialMath::roundFinancial((float) $catItems->sum('total_hpp'));
            $catProfit = FinancialMath::roundFinancial($catRev - $catCogs);
            $catMargin = FinancialMath::calculateMargin($catProfit, $catRev);
            $catContribution = FinancialMath::safeDivide($catProfit * 100.0, (float) max(1, $totalGrossProfit), 2);

            $categoryProfitability->push([
                'category_id' => $catId !== 'uncategorized' ? (string) $catId : null,
                'category_name' => $catName,
                'total_revenue' => $catRev,
                'total_cogs' => $catCogs,
                'gross_profit' => $catProfit,
                'margin_percent' => $catMargin,
                'profit_contribution_percent' => $catContribution,
            ]);
        }

        $categoryProfitability = $categoryProfitability->sortByDesc('gross_profit')->values();

        return new PosMarginAnalyticsDTO(
            overallGrossMarginPct: $overallGrossMarginPct,
            totalGrossProfit: $totalGrossProfit,
            totalCogs: $totalCogs,
            totalNetSales: $totalNetSales,
            zeroCogsItemsCount: $zeroCogsItemsCount,
            zeroCogsRevenue: $zeroCogsRevenue,
            topProfitableProducts: $topProfitableProducts,
            highestMarginProducts: $highestMarginProducts,
            lossLeaderProducts: $lossLeaderProducts,
            zeroCogsWarningProducts: $zeroCogsWarningProducts,
            marginTiers: $marginTiers,
            categoryProfitability: $categoryProfitability
        );
    }

    /**
     * 4. DISCOUNT & PROMOTION IMPACT ANALYTICS
     * Mengaudit kebocoran diskon, diskon manual per kasir, dan efektivitas voucher.
     */
    public function analyzeDiscountsAndPromotions(PosReportFilterDTO $filter): PosDiscountAnalyticsDTO
    {
        $baseOrderQuery = PosOrder::query()
            ->where('pos_orders.business_id', $filter->businessId)
            ->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
            ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId !== null && $filter->locationId !== '') {
            $baseOrderQuery->where('pos_orders.location_id', $filter->locationId);
        }
        if ($filter->userId !== null && $filter->userId !== '') {
            $baseOrderQuery->where('pos_orders.user_id', $filter->userId);
        }
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $baseOrderQuery->where('pos_orders.pos_shift_id', $filter->posShiftId);
        }

        $orders = (clone $baseOrderQuery)->with(['user', 'items'])->get();
        $orderIds = $orders->pluck('id');

        $items = PosOrderItem::query()->whereIn('pos_order_id', $orderIds)->get();

        $grossSales = FinancialMath::roundFinancial((float) $items->sum(fn(PosOrderItem $i) => (float) $i->unit_price * (float) $i->quantity));
        $itemDiscountAmount = FinancialMath::roundFinancial((float) $items->sum('discount_amount'));
        $orderDiscountAmount = FinancialMath::roundFinancial((float) $orders->sum('discount_amount'));
        $voucherDiscountAmount = FinancialMath::roundFinancial((float) $orders->sum('voucher_discount_amount'));
        $pointsDiscountAmount = FinancialMath::roundFinancial((float) $orders->sum('points_discount_amount'));

        $totalDiscountAmount = FinancialMath::roundFinancial($itemDiscountAmount + $orderDiscountAmount + $voucherDiscountAmount + $pointsDiscountAmount);
        $overallDiscountRatePct = FinancialMath::safeDivide($totalDiscountAmount * 100.0, (float) max(1, $grossSales), 2);

        // Cashier Discount Rankings
        $cashierGroups = $orders->groupBy('user_id');
        $cashierDiscountRankings = collect();

        foreach ($cashierGroups as $cUserId => $cOrders) {
            $cUser = $cOrders->first()?->user;
            $cName = $cUser?->name ?? 'Kasir #' . substr((string) $cUserId, 0, 6);
            $cTotalOrders = $cOrders->count();
            
            $cOrderIds = $cOrders->pluck('id');
            $cItems = $items->whereIn('pos_order_id', $cOrderIds);
            
            $cGrossSales = FinancialMath::roundFinancial((float) $cItems->sum(fn(PosOrderItem $i) => (float) $i->unit_price * (float) $i->quantity));
            $cItemDisc = (float) $cItems->sum('discount_amount');
            $cOrderDisc = (float) $cOrders->sum('discount_amount');
            $cVoucherDisc = (float) $cOrders->sum('voucher_discount_amount');
            $cPointsDisc = (float) $cOrders->sum('points_discount_amount');
            $cTotalDisc = FinancialMath::roundFinancial($cItemDisc + $cOrderDisc + $cVoucherDisc + $cPointsDisc);
            
            // Manual discount = diskon tanpa voucher & tanpa loyalty points
            $cManualDisc = FinancialMath::roundFinancial($cItemDisc + (float) $cOrders->whereNull('voucher_code')->where('points_redeemed', 0)->sum('discount_amount'));

            $discountedOrdersCount = $cOrders->filter(fn(PosOrder $o) => (float) $o->discount_amount > 0 || (float) $o->voucher_discount_amount > 0 || (float) $o->points_discount_amount > 0)->count();
            $cDiscountRatio = FinancialMath::safeDivide($cTotalDisc * 100.0, (float) max(1, $cGrossSales), 2);

            $cashierDiscountRankings->push([
                'cashier_id' => (string) $cUserId,
                'cashier_name' => $cName,
                'total_orders' => $cTotalOrders,
                'discounted_orders_count' => $discountedOrdersCount,
                'total_discount_given' => $cTotalDisc,
                'discount_to_sales_ratio' => $cDiscountRatio,
                'manual_discount_amount' => $cManualDisc,
            ]);
        }

        $cashierDiscountRankings = $cashierDiscountRankings->sortByDesc('total_discount_given')->values();

        // Top Discounted Products
        $productItemGroups = $items->where('discount_amount', '>', 0)->groupBy('product_id');
        $topDiscountedProducts = collect();

        foreach ($productItemGroups as $pId => $pDiscItems) {
            $first = $pDiscItems->first();
            $pName = $first?->product_name ?? 'Produk';
            $pCode = $first?->product_code ?? '-';
            $pGross = FinancialMath::roundFinancial((float) $pDiscItems->sum(fn(PosOrderItem $i) => (float) $i->unit_price * (float) $i->quantity));
            $pDisc = FinancialMath::roundFinancial((float) $pDiscItems->sum('discount_amount'));
            $pQty = (float) $pDiscItems->sum('quantity');
            $pPct = FinancialMath::safeDivide($pDisc * 100.0, (float) max(1, $pGross), 2);

            $topDiscountedProducts->push([
                'product_id' => (string) $pId,
                'product_name' => $pName,
                'product_code' => $pCode,
                'gross_amount' => $pGross,
                'total_discount' => $pDisc,
                'discount_percent_of_gross' => $pPct,
                'quantity_discounted' => $pQty,
            ]);
        }

        $topDiscountedProducts = $topDiscountedProducts->sortByDesc('total_discount')->take(10)->values();

        // Voucher Usage Summary
        $voucherOrders = $orders->filter(fn(PosOrder $o) => !empty($o->voucher_code))->groupBy('voucher_code');
        $voucherUsageSummary = collect();

        foreach ($voucherOrders as $vCode => $vList) {
            $vUsageCount = $vList->count();
            $vDiscAmount = FinancialMath::roundFinancial((float) $vList->sum('voucher_discount_amount'));
            $vSalesGen = FinancialMath::roundFinancial((float) $vList->sum('total_amount'));

            $voucherUsageSummary->push([
                'voucher_code' => (string) $vCode,
                'usage_count' => $vUsageCount,
                'total_discount_amount' => $vDiscAmount,
                'total_sales_generated' => $vSalesGen,
            ]);
        }

        $voucherUsageSummary = $voucherUsageSummary->sortByDesc('total_discount_amount')->values();

        return new PosDiscountAnalyticsDTO(
            totalGrossSales: $grossSales,
            totalDiscountAmount: $totalDiscountAmount,
            overallDiscountRatePct: $overallDiscountRatePct,
            itemDiscountAmount: $itemDiscountAmount,
            orderDiscountAmount: $orderDiscountAmount,
            voucherDiscountAmount: $voucherDiscountAmount,
            pointsDiscountAmount: $pointsDiscountAmount,
            cashierDiscountRankings: $cashierDiscountRankings,
            topDiscountedProducts: $topDiscountedProducts,
            voucherUsageSummary: $voucherUsageSummary
        );
    }

    /**
     * 5. GRANULAR SHIFT RECONCILIATION LIST
     */
    public function getShiftReconciliationList(PosReportFilterDTO $filter): Collection
    {
        $query = PosShift::query()
            ->where('pos_shifts.business_id', $filter->businessId)
            ->whereDate('pos_shifts.opened_at', '>=', $filter->startDate->toDateString())
            ->whereDate('pos_shifts.opened_at', '<=', $filter->endDate->toDateString());

        if ($filter->locationId !== null && $filter->locationId !== '') {
            $query->where('pos_shifts.location_id', $filter->locationId);
        }
        if ($filter->userId !== null && $filter->userId !== '') {
            $query->where('pos_shifts.user_id', $filter->userId);
        }
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $query->where('pos_shifts.id', $filter->posShiftId);
        }

        return $query->with(['user', 'register', 'location'])
            ->orderByDesc('opened_at')
            ->get()
            ->map(function (PosShift $shift) {
                $diff = (float) ($shift->cash_difference ?? 0.0);
                $statusLabel = 'Pas (Balanced)';
                if ($diff < -0.01) {
                    $statusLabel = 'Kurang / Short';
                } elseif ($diff > 0.01) {
                    $statusLabel = 'Lebih / Over';
                }

                return [
                    'shift_id' => (string) $shift->id,
                    'cashier_name' => $shift->user?->name ?? 'Kasir',
                    'register_name' => $shift->register?->name ?? 'Register POS',
                    'location_name' => $shift->location?->name ?? 'Outlet',
                    'opened_at' => $shift->opened_at?->format('d/m/Y H:i') ?? '-',
                    'closed_at' => $shift->closed_at?->format('d/m/Y H:i') ?? ($shift->isOpen() ? 'Masih Terbuka' : '-'),
                    'status' => $shift->status,
                    'opening_cash' => (float) ($shift->opening_cash ?? 0.0),
                    'total_cash_sales' => (float) ($shift->total_cash_sales ?? 0.0),
                    'total_non_cash_sales' => (float) ($shift->total_non_cash_sales ?? 0.0),
                    'total_cash_in' => (float) ($shift->total_cash_in ?? 0.0),
                    'total_cash_out' => (float) ($shift->total_cash_out ?? 0.0),
                    'closing_cash_expected' => (float) ($shift->closing_cash_expected ?? 0.0),
                    'closing_cash_actual' => (float) ($shift->closing_cash_actual ?? 0.0),
                    'cash_difference' => $diff,
                    'variance_label' => $shift->getVarianceLabel(),
                    'audit_status' => $statusLabel,
                    'notes' => $shift->notes ?? $shift->cashier_notes ?? '-',
                ];
            });
    }
}
