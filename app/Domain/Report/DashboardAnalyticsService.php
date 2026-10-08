<?php

declare(strict_types=1);

namespace App\Domain\Report;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CostingRun;
use App\Models\CostModel;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\Overhead;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCostVersion;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single Source of Truth for Business Dashboard & Reports Analytics.
 * Shared between Web Executive Cockpit and Mobile Apps.
 */
final class DashboardAnalyticsService
{
    /**
     * Map a period key into a concrete [from, to] Carbon date range.
     * Week starts on Monday (Indonesian business standard).
     */
    public function resolvePeriodRange(string $period, string $fromInput = '', string $toInput = ''): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                return [$today->startOfDay(), $now];
            case 'yesterday':
                $yesterday = $today->copy()->subDay();
                return [$yesterday->startOfDay(), $yesterday->endOfDay()];
            case 'week': {
                $monday = $today->copy()->subDays($today->dayOfWeekIso - 1);
                return [$monday->startOfDay(), $now];
            }
            case 'last_7_days':
                return [$today->copy()->subDays(6)->startOfDay(), $now];
            case 'last_30_days':
                return [$today->copy()->subDays(29)->startOfDay(), $now];
            case 'year':
                return [$today->startOfYear(), $now];
            case 'custom': {
                try {
                    $to = $toInput !== '' ? Carbon::parse($toInput)->endOfDay() : $now;
                    $from = $fromInput !== '' ? Carbon::parse($fromInput)->startOfDay() : $to->copy()->subDays(6)->startOfDay();
                } catch (\Throwable) {
                    $to = $now;
                    $from = $now->copy()->subDays(6)->startOfDay();
                }
                if ($from->gt($to)) {
                    [$from, $to] = [$to, $from];
                }
                return [$from, $to];
            }
            default: // month
                return [$today->startOfMonth(), $now];
        }
    }

    /**
     * Internal calculation logic for dashboard overview & status cockpit.
     */
    public function getOverviewData(Business $business, ?string $locationId = null): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Sales & Cash Flow (POS + Invoices + Toko Online)
        $posTodayQuery = PosOrder::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('status', PosOrder::STATUS_COMPLETED)
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', [
                            PosOrder::STATUS_CONFIRMED,
                            PosOrder::STATUS_PREPARING,
                            PosOrder::STATUS_READY,
                            PosOrder::STATUS_SERVED,
                        ])->where('paid_amount', '>', 0);
                    });
            })
            ->whereDate('order_date', $today);

        if ($locationId) {
            $posTodayQuery->where('location_id', $locationId);
        }

        $posTodaySales = (float) $posTodayQuery->sum('total_amount');
        $posTodayCount = $posTodayQuery->count();

        $invoiceTodaySales = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereDate('invoice_date', $today)
            ->sum('paid_amount');

        $onlineTodayQuery = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->whereDate('created_at', $today);
        $onlineTodaySales = (float) $onlineTodayQuery->sum('total_amount');
        $onlineTodayCount = $onlineTodayQuery->count();

        $todayTotalRevenue = $posTodaySales + $invoiceTodaySales + $onlineTodaySales;

        $posMonthQuery = PosOrder::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('status', PosOrder::STATUS_COMPLETED)
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', [
                            PosOrder::STATUS_CONFIRMED,
                            PosOrder::STATUS_PREPARING,
                            PosOrder::STATUS_READY,
                            PosOrder::STATUS_SERVED,
                        ])->where('paid_amount', '>', 0);
                    });
            })
            ->whereDate('order_date', '>=', $startOfMonth);

        if ($locationId) {
            $posMonthQuery->where('location_id', $locationId);
        }

        $posMonthSales = (float) $posMonthQuery->sum('total_amount');

        $invoiceMonthSales = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereDate('invoice_date', '>=', $startOfMonth)
            ->sum('paid_amount');

        $onlineMonthSales = (float) CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->whereDate('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $monthTotalRevenue = $posMonthSales + $invoiceMonthSales + $onlineMonthSales;

        // 2. Expenses & Net Profit
        $expenseMonthQuery = Expense::where('business_id', $business->id)
            ->whereDate('expense_date', '>=', $startOfMonth);
        if ($locationId) {
            $expenseMonthQuery->where('location_id', $locationId);
        }
        $monthExpenses = (float) $expenseMonthQuery->sum('amount');

        $monthlyOverhead = (float) Overhead::where('business_id', $business->id)
            ->get()
            ->sum(fn(Overhead $o) => $o->monthlyAmount());

        $monthTotalExpenses = $monthExpenses + $monthlyOverhead;
        $monthNetProfitEst = $monthTotalRevenue - $monthTotalExpenses;

        // 3. Receivables (Piutang Belum Terbayar)
        $unpaidInvoicesQuery = Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_UNPAID, Invoice::STATUS_SENT, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE]);
        $unpaidAmount = (float) $unpaidInvoicesQuery->sum('balance_due');
        $unpaidCount = $unpaidInvoicesQuery->count();

        // 4. Active Shift Status
        $activeShiftQuery = PosShift::where('business_id', $business->id)
            ->where('status', PosShift::STATUS_OPEN)
            ->with(['user', 'location']);
        if ($locationId) {
            $activeShiftQuery->where('location_id', $locationId);
        }
        $activeShift = $activeShiftQuery->latest('opened_at')->first();

        // 5. Inventory & Stocks
        $stockQuery = InventoryStock::where('business_id', $business->id)
            ->with(['product.outputUnit', 'material.unit']);
        if ($locationId) {
            $stockQuery->where('location_id', $locationId);
        }
        $stockItems = $stockQuery->get();
        $totalStockValuation = (float) $stockItems->sum(fn($s) => (float) $s->quantity * (float) $s->last_cost);
        $lowStockItems = $stockItems->filter(fn($s) => (float) $s->quantity <= 5)->values();
        $lowStockCount = $lowStockItems->count();

        // 6. Products & Margin Health Analysis
        $products = Product::where('business_id', $business->id)
            ->with(['category', 'outputUnit', 'costModels'])
            ->latest()
            ->get();

        $totalProducts = $products->count();
        $margins = [];
        $lowMarginProducts = [];

        foreach ($products as $p) {
            if ($p->selling_price > 0 && $p->base_cost > 0) {
                $marginPct = (($p->selling_price - $p->base_cost) / $p->selling_price) * 100;
                $margins[] = $marginPct;
                if ($marginPct < 25) {
                    $lowMarginProducts[] = [
                        'product' => $p,
                        'margin' => round($marginPct, 1),
                        'cost' => (float) $p->base_cost,
                        'price' => (float) $p->selling_price,
                    ];
                }
            }
        }
        $avgMargin = count($margins) > 0 ? round(array_sum($margins) / count($margins), 1) : 0.0;

        // 7. Last 7 Days Sales Trend (POS + Online Storefront)
        $sevenDaysTrend = [];
        $maxDaySales = 1000;
        $dayNamesIndo = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();

        $posTrendQuery = PosOrder::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('status', PosOrder::STATUS_COMPLETED)
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', [
                            PosOrder::STATUS_CONFIRMED,
                            PosOrder::STATUS_PREPARING,
                            PosOrder::STATUS_READY,
                            PosOrder::STATUS_SERVED,
                        ])->where('paid_amount', '>', 0);
                    });
            })
            ->where('order_date', '>=', $sevenDaysAgo);

        if ($locationId) {
            $posTrendQuery->where('location_id', $locationId);
        }

        $dailyPosSales = $posTrendQuery
            ->selectRaw('DATE(order_date) as order_dt, SUM(total_amount) as total_sales')
            ->groupBy('order_dt')
            ->pluck('total_sales', 'order_dt')
            ->all();

        $dailyOnlineSales = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(created_at) as order_dt, SUM(total_amount) as total_sales')
            ->groupBy('order_dt')
            ->pluck('total_sales', 'order_dt')
            ->all();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateKey = $date->toDateString();
            $daySales = (float) ($dailyPosSales[$dateKey] ?? 0) + (float) ($dailyOnlineSales[$dateKey] ?? 0);

            if ($daySales > $maxDaySales) {
                $maxDaySales = $daySales;
            }

            $sevenDaysTrend[] = [
                'day_name' => $dayNamesIndo[$date->dayOfWeek],
                'date_formatted' => $date->format('d/m'),
                'amount' => $daySales,
            ];
        }

        foreach ($sevenDaysTrend as &$day) {
            $day['height_pct'] = $maxDaySales > 0 ? round(($day['amount'] / $maxDaySales) * 100) : 10;
            $day['height_pct'] = max(10, $day['height_pct']);
        }
        unset($day);

        // 8. Recent Activities
        $recentPosOrdersQuery = PosOrder::where('business_id', $business->id);
        if ($locationId) {
            $recentPosOrdersQuery->where('location_id', $locationId);
        }
        $recentPosOrders = $recentPosOrdersQuery->latest('created_at')->take(5)->get();

        $recentInvoices = Invoice::where('business_id', $business->id)
            ->with(['customer'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $recentProducts = $products->take(5);

        $recentCostingRuns = CostingRun::whereHas('costModel', fn ($q) => $q->where('business_id', $business->id))
            ->with(['costModel.product'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $stats = [
            'today_sales' => $todayTotalRevenue,
            'today_pos_sales' => $posTodaySales,
            'today_invoice_sales' => $invoiceTodaySales,
            'today_online_sales' => $onlineTodaySales,
            'today_transactions_count' => $posTodayCount + $onlineTodayCount,
            'month_sales' => $monthTotalRevenue,
            'month_online_sales' => $onlineMonthSales,
            'month_expenses' => $monthTotalExpenses,
            'month_net_profit_est' => $monthNetProfitEst,
            'unpaid_invoices_amount' => $unpaidAmount,
            'unpaid_invoices_count' => $unpaidCount,
            'total_stock_valuation' => $totalStockValuation,
            'inventory_valuation' => $totalStockValuation,
            'low_stock_count' => $lowStockCount,
            'total_products' => $totalProducts,
            'total_materials' => Material::where('business_id', $business->id)->count(),
            'active_cost_models' => CostModel::where('business_id', $business->id)->where('is_active', true)->count(),
            'approved_versions' => ProductCostVersion::where('business_id', $business->id)->where('status', ProductCostVersion::STATUS_APPROVED)->count(),
            'avg_margin_pct' => $avgMargin,
            'low_margin_count' => count($lowMarginProducts),
            'monthly_overhead' => $monthlyOverhead,
            'is_shift_open' => $activeShift !== null,
            'active_shift' => $activeShift,
        ];

        return compact(
            'business',
            'stats',
            'sevenDaysTrend',
            'recentPosOrders',
            'recentInvoices',
            'recentProducts',
            'lowMarginProducts',
            'lowStockItems',
            'recentCostingRuns'
        );
    }

    /**
     * Period-filtered analytics aggregation for the dashboard cockpit.
     * Pure read-only aggregates built on existing tables.
     */
    public function getAnalyticsData(
        Business $business,
        string $period = 'month',
        string $fromInput = '',
        string $toInput = '',
        ?string $locationId = null
    ): array {
        if (! in_array($period, ['today', 'yesterday', 'week', 'last_7_days', 'month', 'last_30_days', 'year', 'custom'], true)) {
            $period = 'month';
        }

        [$from, $to] = $this->resolvePeriodRange($period, $fromInput, $toInput);
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        $periodLabels = [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            'week' => 'Minggu Ini',
            'last_7_days' => '7 Hari Terakhir',
            'month' => 'Bulan Ini',
            'last_30_days' => '30 Hari Terakhir',
            'year' => 'Tahun Ini',
            'custom' => 'Range Kustom',
        ];

        // 1. Finansial aggregate: POS kasir + Faktur + Toko Online
        $posAggQuery = PosOrder::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('status', PosOrder::STATUS_COMPLETED)
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', [
                            PosOrder::STATUS_CONFIRMED,
                            PosOrder::STATUS_PREPARING,
                            PosOrder::STATUS_READY,
                            PosOrder::STATUS_SERVED,
                        ])->where('paid_amount', '>', 0);
                    });
            })
            ->whereBetween('order_date', [$fromStr, $toStr]);

        if ($locationId) {
            $posAggQuery->where('location_id', $locationId);
        }

        $posAgg = $posAggQuery->selectRaw('COALESCE(SUM(total_amount), 0) as revenue, COALESCE(SUM(total_hpp_cost), 0) as hpp, COALESCE(SUM(total_gross_profit), 0) as profit, COUNT(*) as cnt')
            ->first();

        $invAgg = Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereBetween('invoice_date', [$fromStr, $toStr])
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as revenue, COALESCE(SUM(total_hpp_cost), 0) as hpp, COALESCE(SUM(total_gross_profit), 0) as profit, COUNT(*) as cnt')
            ->first();

        $onlineAgg = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->whereBetween('created_at', [$fromStr . ' 00:00:00', $toStr . ' 23:59:59'])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as cnt')
            ->first();

        $posRevenue = (float) ($posAgg?->revenue ?? 0);
        $invRevenue = (float) ($invAgg?->revenue ?? 0);
        $onlineRevenue = (float) ($onlineAgg?->revenue ?? 0);
        $posHpp = (float) ($posAgg?->hpp ?? 0);
        $invHpp = (float) ($invAgg?->hpp ?? 0);
        $posProfit = (float) ($posAgg?->profit ?? 0);
        $invProfit = (float) ($invAgg?->profit ?? 0);
        $posCount = (int) ($posAgg?->cnt ?? 0);
        $invCount = (int) ($invAgg?->cnt ?? 0);
        $onlineCount = (int) ($onlineAgg?->cnt ?? 0);

        $omzet = $posRevenue + $invRevenue + $onlineRevenue;
        $hppTotal = $posHpp + $invHpp;
        $profit = $posProfit + $invProfit;
        $transactions = $posCount + $invCount + $onlineCount;
        $avgTicket = $transactions > 0 ? round($omzet / $transactions, 0) : 0.0;
        $marginPct = $omzet > 0 ? round(($profit / $omzet) * 100, 1) : null;

        // 2. Beban operasional dalam periode
        $expenseQuery = Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$fromStr, $toStr]);
        if ($locationId) {
            $expenseQuery->where('location_id', $locationId);
        }
        $expenses = (float) $expenseQuery->sum('amount');

        // 3. Tren omzet & profit: bucket per jam untuk 'today', harian untuk <= 62 hari, bulanan untuk > 62 hari
        $trend = [];
        if ($period === 'today' || $period === 'yesterday') {
            $granularity = 'hour';
            $posTrendRowsQuery = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$fromStr, $toStr]);
            if ($locationId) {
                $posTrendRowsQuery->where('location_id', $locationId);
            }
            $posTrendRows = $posTrendRowsQuery
                ->selectRaw("DATE_FORMAT(created_at, '%H:00') as bucket, SUM(total_amount) as revenue, SUM(total_gross_profit) as profit, COUNT(*) as cnt")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $invTrendRows = Invoice::where('business_id', $business->id)
                ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
                ->whereBetween('invoice_date', [$fromStr, $toStr])
                ->selectRaw("DATE_FORMAT(created_at, '%H:00') as bucket, SUM(paid_amount) as revenue, SUM(total_gross_profit) as profit, COUNT(*) as cnt")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $posTrend = [];
            foreach ($posTrendRows as $row) {
                $posTrend[(string) $row->bucket] = $row;
            }
            $invTrend = [];
            foreach ($invTrendRows as $row) {
                $invTrend[(string) $row->bucket] = $row;
            }

            $allHours = array_merge(array_keys($posTrend), array_keys($invTrend));
            $startHour = 8;
            $maxHour = 8;
            foreach ($allHours as $hKey) {
                $hVal = (int) substr($hKey, 0, 2);
                $startHour = min($startHour, $hVal);
                $maxHour = max($maxHour, $hVal);
            }
            $nowHour = (int) Carbon::now()->hour;
            $endHour = max($startHour + 3, min(22, max($maxHour + 1, min($nowHour, 22))));

            for ($h = $startHour; $h <= $endHour; $h++) {
                $hKey = sprintf('%02d:00', $h);
                $posRow = $posTrend[$hKey] ?? null;
                $invRow = $invTrend[$hKey] ?? null;
                $trend[] = [
                    'label' => $hKey,
                    'omzet' => round(($posRow ? (float) $posRow->revenue : 0.0) + ($invRow ? (float) $invRow->revenue : 0.0), 0),
                    'profit' => round(($posRow ? (float) $posRow->profit : 0.0) + ($invRow ? (float) $invRow->profit : 0.0), 0),
                    'count' => ($posRow ? (int) $posRow->cnt : 0) + ($invRow ? (int) $invRow->cnt : 0),
                ];
            }
        } else {
            $granularity = ((int) $from->diffInDays($to) <= 62) ? 'day' : 'month';
            $dateExpr = $granularity === 'day' ? 'DATE(order_date)' : "DATE_FORMAT(order_date, '%Y-%m')";
            $invDateExpr = $granularity === 'day' ? 'DATE(invoice_date)' : "DATE_FORMAT(invoice_date, '%Y-%m')";

            $posTrendRowsQuery = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$fromStr, $toStr]);
            if ($locationId) {
                $posTrendRowsQuery->where('location_id', $locationId);
            }

            $posTrendRows = $posTrendRowsQuery
                ->selectRaw("$dateExpr as bucket, SUM(total_amount) as revenue, SUM(total_gross_profit) as profit, COUNT(*) as cnt")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $invTrendRows = Invoice::where('business_id', $business->id)
                ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
                ->whereBetween('invoice_date', [$fromStr, $toStr])
                ->selectRaw("$invDateExpr as bucket, SUM(paid_amount) as revenue, SUM(total_gross_profit) as profit, COUNT(*) as cnt")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $posTrend = [];
            foreach ($posTrendRows as $row) {
                $posTrend[(string) $row->bucket] = $row;
            }
            $invTrend = [];
            foreach ($invTrendRows as $row) {
                $invTrend[(string) $row->bucket] = $row;
            }

            $cursor = $granularity === 'day' ? $from->copy()->startOfDay() : $from->copy()->startOfDay()->startOfMonth();
            $lastCursor = $to->copy()->startOfDay();
            while ($cursor->lte($lastCursor)) {
                $key = $granularity === 'day' ? $cursor->toDateString() : $cursor->format('Y-m');
                $posRow = $posTrend[$key] ?? null;
                $invRow = $invTrend[$key] ?? null;
                $trend[] = [
                    'label' => $granularity === 'day' ? $cursor->format('d/m') : $cursor->format('m/Y'),
                    'omzet' => round(($posRow ? (float) $posRow->revenue : 0.0) + ($invRow ? (float) $invRow->revenue : 0.0), 0),
                    'profit' => round(($posRow ? (float) $posRow->profit : 0.0) + ($invRow ? (float) $invRow->profit : 0.0), 0),
                    'count' => ($posRow ? (int) $posRow->cnt : 0) + ($invRow ? (int) $invRow->cnt : 0),
                ];
                $cursor = $granularity === 'day' ? $cursor->addDays(1) : $cursor->addMonths(1)->startOfMonth();
            }
        }

        // 4. Distribusi omzet oleh kanal: Kasir vs Faktur vs Toko Online
        $channelSplit = [
            ['label' => 'Kasir (POS)', 'value' => round($posRevenue, 0)],
            ['label' => 'Faktur B2B', 'value' => round($invRevenue, 0)],
            ['label' => 'Toko Online', 'value' => round($onlineRevenue, 0)],
        ];

        // 5. Distribusi oleh jenis order kasir
        $orderTypeQuery = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$fromStr, $toStr]);
        if ($locationId) {
            $orderTypeQuery->where('location_id', $locationId);
        }
        $orderTypeRows = $orderTypeQuery
            ->selectRaw("COALESCE(NULLIF(TRIM(order_type), ''), 'takeaway') as ot, SUM(total_amount) as total, COUNT(*) as cnt")
            ->groupBy('ot')
            ->orderByDesc('total')
            ->get();

        $orderTypeLabels = [
            'dine_in' => 'Dine In',
            'takeaway' => 'Take Away',
            'delivery' => 'Delivery',
            'online' => 'Online',
        ];
        $orderTypeSplit = [];
        foreach ($orderTypeRows as $row) {
            $orderTypeSplit[] = [
                'label' => $orderTypeLabels[$row->ot] ?? ucfirst(str_replace('_', ' ', (string) $row->ot)),
                'value' => round((float) $row->total, 0),
            ];
        }

        // 6. Omzet by kategori produk
        $categoryQuery = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr])
            ->whereNull('products.deleted_at');

        if ($locationId) {
            $categoryQuery->where('pos_orders.location_id', $locationId);
        }

        $categoryRows = $categoryQuery
            ->selectRaw("COALESCE(NULLIF(TRIM(product_categories.name), ''), 'Umum / Lainnya') as cat, SUM(pos_order_items.total_price) as total")
            ->groupBy('cat')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        $categorySplit = [];
        foreach ($categoryRows as $row) {
            $categorySplit[] = [
                'label' => (string) $row->cat,
                'value' => round((float) $row->total, 0),
            ];
        }

        // 7. Distribusi Metode Pembayaran
        $paymentQuery = DB::table('pos_order_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_payments.pos_order_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr]);

        if ($locationId) {
            $paymentQuery->where('pos_orders.location_id', $locationId);
        }

        $paymentRows = $paymentQuery
            ->selectRaw("COALESCE(NULLIF(TRIM(pos_order_payments.payment_method), ''), 'cash') as pm, SUM(pos_order_payments.amount) as total")
            ->groupBy('pm')
            ->orderByDesc('total')
            ->get();

        $paymentMethodLabels = [
            'cash' => 'Tunai (Cash)',
            'qris' => 'QRIS',
            'bank' => 'Transfer Bank',
            'transfer' => 'Transfer Bank',
            'card' => 'Kartu Debit/Kredit',
            'debit' => 'Kartu Debit',
            'credit' => 'Kartu Kredit',
            'ewallet' => 'E-Wallet',
        ];
        $paymentSplit = [];
        foreach ($paymentRows as $row) {
            $paymentSplit[] = [
                'label' => $paymentMethodLabels[$row->pm] ?? ucfirst((string) $row->pm),
                'value' => round((float) $row->total, 0),
            ];
        }

        // 8. Top 10 produk terlaris
        $topProductsQuery = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr]);

        if ($locationId) {
            $topProductsQuery->where('pos_orders.location_id', $locationId);
        }

        $topProductsRows = $topProductsQuery
            ->selectRaw('pos_order_items.product_id as pid, MAX(pos_order_items.product_name) as pname, MAX(pos_order_items.product_code) as pcode, SUM(pos_order_items.quantity) as qty, SUM(pos_order_items.total_price) as total')
            ->groupBy('pos_order_items.product_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $maxProductRevenue = 0.0;
        foreach ($topProductsRows as $row) {
            $maxProductRevenue = max($maxProductRevenue, (float) $row->total);
        }
        $topProducts = [];
        foreach ($topProductsRows as $row) {
            $revenue = round((float) $row->total, 0);
            $qty = round((float) $row->qty, 2);
            $topProducts[] = [
                'name' => (string) $row->pname,
                'code' => (string) ($row->pcode ?? ''),
                'qty' => $qty,
                'revenue' => $revenue,
                'avg_price' => $qty > 0 ? round($revenue / $qty, 0) : 0.0,
                'pct' => $maxProductRevenue > 0 ? (int) round(($revenue / $maxProductRevenue) * 100) : 0,
            ];
        }

        // 9. Top 15 material paling sering digunakan
        $movementQuery = StockMovement::where('business_id', $business->id)
            ->whereNotNull('material_id')
            ->where(function ($q) {
                $q->whereIn('movement_type', [
                    StockMovement::TYPE_GOODS_ISSUE,
                    StockMovement::TYPE_POS_SALE,
                    StockMovement::TYPE_INVOICE_SALE,
                    'production_consumption',
                ])->orWhere('quantity_change', '<', 0);
            })
            ->whereDate('created_at', '>=', $fromStr)
            ->whereDate('created_at', '<=', $toStr);

        if ($locationId) {
            $movementQuery->where('location_id', $locationId);
        }

        $movementRows = $movementQuery
            ->selectRaw('material_id as mid, SUM(ABS(quantity_change)) as used, COUNT(*) as cnt')
            ->groupBy('material_id')
            ->get();

        $bomMaterialQuery = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('cost_models', function ($j) use ($business) {
                $j->on('cost_models.product_id', '=', 'pos_order_items.product_id')
                  ->where('cost_models.business_id', '=', $business->id)
                  ->where('cost_models.is_active', '=', true);
            })
            ->join('bom_headers', 'bom_headers.cost_model_id', '=', 'cost_models.id')
            ->join('bom_items', 'bom_items.bom_header_id', '=', 'bom_headers.id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr]);

        if ($locationId) {
            $bomMaterialQuery->where('pos_orders.location_id', $locationId);
        }

        $bomMaterialRows = $bomMaterialQuery
            ->selectRaw('bom_items.material_id as mid, SUM(pos_order_items.quantity * bom_items.quantity) as used_qty, COUNT(DISTINCT pos_orders.id) as cnt')
            ->groupBy('bom_items.material_id')
            ->get();

        $mergedMaterials = [];
        foreach ($movementRows as $row) {
            $mid = (string) $row->mid;
            $mergedMaterials[$mid] = [
                'mid' => $mid,
                'used' => (float) $row->used,
                'cnt' => (int) $row->cnt,
            ];
        }
        foreach ($bomMaterialRows as $row) {
            $mid = (string) $row->mid;
            if (isset($mergedMaterials[$mid])) {
                $mergedMaterials[$mid]['used'] = max($mergedMaterials[$mid]['used'], (float) $row->used_qty);
                $mergedMaterials[$mid]['cnt'] = max($mergedMaterials[$mid]['cnt'], (int) $row->cnt);
            } else {
                $mergedMaterials[$mid] = [
                    'mid' => $mid,
                    'used' => (float) $row->used_qty,
                    'cnt' => (int) $row->cnt,
                ];
            }
        }

        uasort($mergedMaterials, fn ($a, $b) => $b['used'] <=> $a['used']);
        $mergedMaterials = array_slice($mergedMaterials, 0, 15, true);

        $materialIds = array_keys($mergedMaterials);
        $materialsById = [];
        if (! empty($materialIds)) {
            foreach (Material::with('unit')->whereIn('id', $materialIds)->get() as $mat) {
                $materialsById[$mat->id] = $mat;
            }
        }

        $maxMaterialUsage = 0.0;
        foreach ($mergedMaterials as $item) {
            $maxMaterialUsage = max($maxMaterialUsage, (float) $item['used']);
        }

        $topMaterials = [];
        foreach ($mergedMaterials as $item) {
            $mat = $materialsById[$item['mid']] ?? null;
            $used = round((float) $item['used'], 2);
            $topMaterials[] = [
                'name' => $mat?->name ?? 'Bahan Baku',
                'unit' => $mat?->unit?->code ?? 'satuan',
                'qty' => $used,
                'count' => (int) $item['cnt'],
                'pct' => $maxMaterialUsage > 0 ? (int) round(($used / $maxMaterialUsage) * 100) : 0,
            ];
        }

        // 10. Top 10 Stok hampir habis
        $lowStockCountQuery = InventoryStock::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('quantity', '<=', 5)
                  ->orWhereRaw('quantity <= COALESCE((SELECT min_stock FROM products WHERE products.id = inventory_stocks.product_id), 5)');
            });
        if ($locationId) {
            $lowStockCountQuery->where('location_id', $locationId);
        }
        $lowStockCount = (int) $lowStockCountQuery->count();

        $stockRowsQuery = InventoryStock::where('business_id', $business->id)
            ->with(['product.outputUnit', 'material.unit']);
        if ($locationId) {
            $stockRowsQuery->where('location_id', $locationId);
        }
        $stockRows = $stockRowsQuery
            ->orderBy('quantity', 'asc')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $lowStock = [];
        $atRiskValue = 0.0;
        foreach ($stockRows as $s) {
            $available = max(0.0, (float) $s->quantity - (float) $s->reserved_quantity);
            $isProduct = $s->product_id !== null;
            $name = $isProduct ? ($s->product?->name ?? 'Produk') : ($s->material?->name ?? 'Bahan Baku');
            $unit = $isProduct ? ($s->product?->outputUnit?->code ?? 'pcs') : ($s->material?->unit?->code ?? 'satuan');
            $minStock = $isProduct && $s->product?->min_stock ? (float) $s->product->min_stock : 10.0;
            $cost = (float) ($s->last_cost > 0 ? $s->last_cost : $s->avg_purchase_cost);
            $value = round($available * $cost, 0);
            $isCritical = $available <= 1.0 || $available <= ($minStock * 0.5);
            $isLow = $available <= $minStock;
            if ($isLow) {
                $atRiskValue += $value;
            }

            $lowStock[] = [
                'name' => $name,
                'kind' => $isProduct ? 'Produk' : 'Bahan',
                'unit' => $unit,
                'remaining' => round($available, 2),
                'min_stock' => round($minStock, 2),
                'critical' => $isCritical,
                'is_low' => $isLow,
                'status' => $isCritical ? 'Kritis' : ($isLow ? 'Menipis' : 'Aman'),
                'value' => $value,
            ];
        }

        // 11. Total Item Produk Terjual
        $itemsSoldQuery = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr]);
        if ($locationId) {
            $itemsSoldQuery->where('pos_orders.location_id', $locationId);
        }
        $itemsSold = (float) $itemsSoldQuery->sum('pos_order_items.quantity');

        // 12. Smart Communicative Insights
        $insights = [];
        if (count($topProducts) > 0) {
            $insights[] = [
                'icon' => 'trophy',
                'label' => 'Produk Terlaris #1',
                'value' => $topProducts[0]['name'] . ' (' . number_format($topProducts[0]['qty'], 0, ',', '.') . ' terjual)',
            ];
        }
        if (count($topMaterials) > 0) {
            $insights[] = [
                'icon' => 'package-check',
                'label' => 'Bahan Paling Sering Terpakai',
                'value' => $topMaterials[0]['name'] . ' (' . number_format($topMaterials[0]['qty'], 0, ',', '.') . ' ' . $topMaterials[0]['unit'] . ')',
            ];
        }
        if (count($categorySplit) > 0) {
            $insights[] = [
                'icon' => 'pie-chart',
                'label' => 'Kategori Paling Diminati',
                'value' => $categorySplit[0]['label'] . ' (Rp ' . number_format($categorySplit[0]['value'], 0, ',', '.') . ')',
            ];
        }
        if ($transactions > 0) {
            $insights[] = [
                'icon' => 'wallet',
                'label' => 'Rata-Rata Nilai Belanja',
                'value' => 'Rp ' . number_format($avgTicket, 0, ',', '.') . ' / transaksi',
            ];
        }
        if ($marginPct !== null) {
            $insights[] = [
                'icon' => 'sparkles',
                'label' => 'Kesehatan Margin Kotor',
                'value' => $marginPct . '% ' . ($marginPct >= 35 ? '(Sangat Sehat)' : ($marginPct >= 20 ? '(Cukup Baik)' : '(Perlu Evaluasi)')),
            ];
        }
        if ($lowStockCount > 0) {
            $insights[] = [
                'icon' => 'alert-triangle',
                'label' => 'Perhatian Stok Menipis',
                'value' => $lowStockCount . ' item perlu restock segera',
            ];
        }

        $kpis = [
            'omzet' => round($omzet, 0),
            'transactions' => $transactions,
            'avg_ticket' => round($avgTicket, 0),
            'profit' => round($profit, 0),
            'margin_pct' => $marginPct,
            'hpp' => round($hppTotal, 0),
            'net' => round($profit - $expenses, 0),
            'expenses' => round($expenses, 0),
            'low_stock' => $lowStockCount,
            'items_sold' => round($itemsSold, 0),
        ];

        return [
            'period' => $period,
            'period_label' => $periodLabels[$period] ?? 'Periode',
            'granularity' => $granularity,
            'range' => ['from' => $fromStr, 'to' => $toStr],
            'kpis' => $kpis,
            'trend' => $trend,
            'channel_split' => $channelSplit,
            'order_type_split' => $orderTypeSplit,
            'category_split' => $categorySplit,
            'payment_split' => $paymentSplit,
            'top_products' => $topProducts,
            'top_materials' => $topMaterials,
            'low_stock' => $lowStock,
            'insights' => $insights,
            'at_risk_value' => round($atRiskValue, 0),
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
