<?php

declare(strict_types=1);

namespace App\Domain\Report\Pos;

use App\Domain\Report\Pos\DTOs\PosDiscountAnalyticsDTO;
use App\Domain\Report\Pos\DTOs\PosFraudAuditDTO;
use App\Domain\Report\Pos\DTOs\PosKpiSummaryDTO;
use App\Domain\Report\Pos\DTOs\PosMarginAnalyticsDTO;
use App\Domain\Report\Pos\DTOs\PosReconciliationResultDTO;
use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosShift;
use App\Models\SalesReturn;
use App\Support\Math\FinancialMath;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PosReportingService
{
    public function __construct(
        private readonly PosReconciliationService $reconciliationService = new PosReconciliationService()
    ) {}
    /**
     * Bangun Query Dasar untuk Order POS dengan mematuhi seluruh parameter filter.
     *
     * @return Builder<PosOrder>
     */
    public function buildBaseOrdersQuery(PosReportFilterDTO $filter): Builder
    {
        $query = PosOrder::query()
            ->where('pos_orders.business_id', $filter->businessId);

        // Filter Status
        if ($filter->status !== null && $filter->status !== '') {
            $query->where('pos_orders.status', $filter->status);
        } else {
            $query->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        }

        // Filter Tanggal
        $query->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
              ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        // Filter Cabang / Lokasi
        if ($filter->locationId !== null && $filter->locationId !== '') {
            $query->where('pos_orders.location_id', $filter->locationId);
        }

        // Filter Kasir / User
        if ($filter->userId !== null && $filter->userId !== '') {
            $query->where('pos_orders.user_id', $filter->userId);
        }

        // Filter Shift
        if ($filter->posShiftId !== null && $filter->posShiftId !== '') {
            $query->where('pos_orders.pos_shift_id', $filter->posShiftId);
        }

        // Filter Pelanggan
        if ($filter->customerId !== null && $filter->customerId !== '') {
            $query->where('pos_orders.customer_id', $filter->customerId);
        }

        // Filter Saluran Penjualan (POS, Takeaway, GoFood, dll.)
        if ($filter->salesChannel !== null && $filter->salesChannel !== '') {
            $query->where('pos_orders.sales_channel', $filter->salesChannel);
        }

        // Filter Tipe Pesanan (dine_in, takeaway, delivery)
        if ($filter->orderType !== null && $filter->orderType !== '') {
            $query->where('pos_orders.order_type', $filter->orderType);
        }

        // Filter Kategori Produk
        if ($filter->categoryId !== null && $filter->categoryId !== '') {
            $query->whereHas('items.product', function ($pq) use ($filter): void {
                $pq->where('category_id', $filter->categoryId);
            });
        }

        // Filter Produk Spesifik
        if ($filter->productId !== null && $filter->productId !== '') {
            $query->whereHas('items', function ($iq) use ($filter): void {
                $iq->where('product_id', $filter->productId);
            });
        }

        // Filter Metode Pembayaran
        if ($filter->paymentMethod !== null && $filter->paymentMethod !== '') {
            $query->whereHas('payments', function ($payQ) use ($filter): void {
                $payQ->where('payment_method', $filter->paymentMethod);
            });
        }

        return $query;
    }

    /**
     * Hitung Ringkasan KPI Finansial Utama (Single Source of Truth).
     */
    public function getKpiSummary(PosReportFilterDTO $filter): PosKpiSummaryDTO
    {
        $ordersQuery = $this->buildBaseOrdersQuery($filter);

        // 1. Agregasi Level Order
        $orderAgg = (clone $ordersQuery)->selectRaw('
            COUNT(pos_orders.id) as orders_count,
            COALESCE(SUM(pos_orders.subtotal), 0) as total_subtotal,
            COALESCE(SUM(pos_orders.discount_amount), 0) as total_order_discount,
            COALESCE(SUM(pos_orders.voucher_discount_amount), 0) as total_voucher_discount,
            COALESCE(SUM(pos_orders.points_discount_amount), 0) as total_points_discount,
            COALESCE(SUM(pos_orders.tax_amount), 0) as total_tax,
            COALESCE(SUM(pos_orders.service_charge_amount), 0) as total_service_charge,
            COALESCE(SUM(pos_orders.rounding_amount), 0) as total_rounding,
            COALESCE(SUM(pos_orders.total_amount), 0) as total_revenue,
            COALESCE(SUM(pos_orders.total_hpp_cost), 0) as total_hpp_cost,
            COALESCE(SUM(pos_orders.total_gross_profit), 0) as total_gross_profit
        ')->first();

        $ordersCount = (int) ($orderAgg->orders_count ?? 0);
        $totalSubtotal = (float) ($orderAgg->total_subtotal ?? 0.0);
        $totalOrderDiscount = (float) ($orderAgg->total_order_discount ?? 0.0);
        $totalVoucherDiscount = (float) ($orderAgg->total_voucher_discount ?? 0.0);
        $totalPointsDiscount = (float) ($orderAgg->total_points_discount ?? 0.0);
        $totalTax = (float) ($orderAgg->total_tax ?? 0.0);
        $totalServiceCharge = (float) ($orderAgg->total_service_charge ?? 0.0);
        $totalRounding = (float) ($orderAgg->total_rounding ?? 0.0);
        $totalHpp = (float) ($orderAgg->total_hpp_cost ?? 0.0);

        // 2. Agregasi Level Item (Gross Item Sales & Item Discounts & Barang vs Jasa)
        $itemsQuery = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $filter->businessId);

        if ($filter->status !== null && $filter->status !== '') {
            $itemsQuery->where('pos_orders.status', $filter->status);
        } else {
            $itemsQuery->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        }

        $itemsQuery->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
                   ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId) $itemsQuery->where('pos_orders.location_id', $filter->locationId);
        if ($filter->userId) $itemsQuery->where('pos_orders.user_id', $filter->userId);
        if ($filter->posShiftId) $itemsQuery->where('pos_orders.pos_shift_id', $filter->posShiftId);
        if ($filter->customerId) $itemsQuery->where('pos_orders.customer_id', $filter->customerId);
        if ($filter->salesChannel) $itemsQuery->where('pos_orders.sales_channel', $filter->salesChannel);
        if ($filter->orderType) $itemsQuery->where('pos_orders.order_type', $filter->orderType);
        if ($filter->productId) $itemsQuery->where('pos_order_items.product_id', $filter->productId);

        $itemAgg = (clone $itemsQuery)->selectRaw('
            COALESCE(SUM(pos_order_items.quantity), 0) as total_qty,
            COALESCE(SUM(pos_order_items.quantity * pos_order_items.unit_price), 0) as total_gross_items,
            COALESCE(SUM(pos_order_items.discount_amount), 0) as total_item_discount,
            COALESCE(SUM(pos_order_items.total_price), 0) as total_item_net,
            COALESCE(SUM(pos_order_items.total_hpp), 0) as total_item_hpp
        ')->first();

        $totalItemsSold = (float) ($itemAgg->total_qty ?? 0.0);
        $totalGrossItems = (float) ($itemAgg->total_gross_items ?? 0.0);
        $totalItemDiscount = (float) ($itemAgg->total_item_discount ?? 0.0);

        // Jika tidak ada item terpisah, gunakan subtotal sebagai fallback gross sales
        $grossSales = $totalGrossItems > 0 ? $totalGrossItems : $totalSubtotal;
        $totalDiscount = $totalOrderDiscount + $totalVoucherDiscount + $totalPointsDiscount + $totalItemDiscount;
        $grossRevenue = max(0.0, $grossSales - $totalDiscount);

        // 3. Nilai Retur / Refund dari SalesReturn
        $refundsQuery = SalesReturn::query()
            ->where('business_id', $filter->businessId)
            ->whereIn('status', ['completed', 'approved'])
            ->whereDate('return_date', '>=', $filter->startDate->toDateString())
            ->whereDate('return_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId) $refundsQuery->where('location_id', $filter->locationId);
        if ($filter->customerId) $refundsQuery->where('customer_id', $filter->customerId);

        $refundAmount = (float) $refundsQuery->sum('total_amount');

        // Net Sales Sejati (Omzet Bersih setelah retur)
        $netSales = max(0.0, $grossRevenue - $refundAmount);
        $grandTotal = $netSales + $totalTax + $totalServiceCharge + $totalRounding;
        $grossProfit = $netSales - $totalHpp;
        $grossMarginPercent = FinancialMath::calculateMargin($grossProfit, $netSales);

        // AOV, Avg Items, Weighted ASP & Cost
        $aov = FinancialMath::safeDivide($netSales, (float) $ordersCount);
        $avgItemsPerTx = FinancialMath::safeDivide($totalItemsSold, (float) $ordersCount);
        $averageSellingPrice = FinancialMath::safeDivide($grossSales, $totalItemsSold);
        $averageCostPrice = FinancialMath::safeDivide($totalHpp, $totalItemsSold);

        // 4. Komposisi Barang Fisik vs Jasa Layanan
        $compQuery = (clone $itemsQuery)
            ->leftJoin('products', 'pos_order_items.product_id', '=', 'products.id')
            ->selectRaw("
                COALESCE(products.type, 'goods') as item_type,
                COALESCE(SUM(pos_order_items.total_price), 0) as revenue,
                COALESCE(SUM(pos_order_items.quantity), 0) as qty
            ")
            ->groupBy(DB::raw("COALESCE(products.type, 'goods')"))
            ->get()
            ->keyBy('item_type');

        $goodsRevenue = (float) ($compQuery->get('goods')?->revenue ?? 0.0);
        $goodsQuantity = (float) ($compQuery->get('goods')?->qty ?? 0.0);
        $servicesRevenue = (float) ($compQuery->get('service')?->revenue ?? 0.0);
        $servicesQuantity = (float) ($compQuery->get('service')?->qty ?? 0.0);

        // 5. Pembayaran (Tunai vs Non-Tunai)
        $paymentsQuery = PosOrderPayment::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_payments.pos_order_id')
            ->where('pos_orders.business_id', $filter->businessId);

        if ($filter->status !== null && $filter->status !== '') {
            $paymentsQuery->where('pos_orders.status', $filter->status);
        } else {
            $paymentsQuery->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        }

        $paymentsQuery->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
                      ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId) $paymentsQuery->where('pos_orders.location_id', $filter->locationId);
        if ($filter->userId) $paymentsQuery->where('pos_orders.user_id', $filter->userId);

        $payAgg = (clone $paymentsQuery)->selectRaw("
            COALESCE(SUM(pos_order_payments.amount), 0) as total_payments,
            COALESCE(SUM(CASE WHEN pos_order_payments.payment_method = 'cash' THEN pos_order_payments.amount ELSE 0 END), 0) as cash_sales,
            COALESCE(SUM(CASE WHEN pos_order_payments.payment_method != 'cash' THEN pos_order_payments.amount ELSE 0 END), 0) as non_cash_sales
        ")->first();

        $totalPayments = (float) ($payAgg->total_payments ?? 0.0);
        $cashSales = (float) ($payAgg->cash_sales ?? 0.0);
        $nonCashSales = (float) ($payAgg->non_cash_sales ?? 0.0);

        // 6. Ringkasan Hari Ini (Today's Quick Summary)
        $todayQuery = PosOrder::query()
            ->where('business_id', $filter->businessId)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->whereDate('order_date', Carbon::today());

        if ($filter->locationId) $todayQuery->where('location_id', $filter->locationId);

        $todayRevenue = (float) (clone $todayQuery)->sum('total_amount');
        $todayOrders = (int) (clone $todayQuery)->count();

        // 7. Komparasi Pertumbuhan Periode Sebelumnya (Growth %)
        $prevNetSales = null;
        $prevGrossProfit = null;
        $prevOrders = null;
        $salesGrowthPercent = null;
        $profitGrowthPercent = null;
        $ordersGrowthPercent = null;

        if ($filter->includeComparison) {
            [$prevStart, $prevEnd] = $filter->getPreviousPeriod();
            $prevFilter = new PosReportFilterDTO(
                businessId: $filter->businessId,
                startDate: $prevStart,
                endDate: $prevEnd,
                locationId: $filter->locationId,
                userId: $filter->userId,
                posShiftId: $filter->posShiftId,
                customerId: $filter->customerId,
                categoryId: $filter->categoryId,
                productId: $filter->productId,
                paymentMethod: $filter->paymentMethod,
                salesChannel: $filter->salesChannel,
                orderType: $filter->orderType,
                status: $filter->status,
                includeComparison: false
            );

            $prevSummary = $this->getKpiSummary($prevFilter);
            $prevNetSales = $prevSummary->netSales;
            $prevGrossProfit = $prevSummary->grossProfit;
            $prevOrders = $prevSummary->totalOrders;

            $salesGrowthPercent = FinancialMath::calculateGrowth($netSales, $prevNetSales);
            $profitGrowthPercent = FinancialMath::calculateGrowth($grossProfit, $prevGrossProfit);
            $ordersGrowthPercent = FinancialMath::calculateGrowth((float) $ordersCount, (float) $prevOrders);
        }

        return new PosKpiSummaryDTO(
            grossSales: FinancialMath::roundFinancial($grossSales),
            totalDiscount: FinancialMath::roundFinancial($totalDiscount),
            orderDiscount: FinancialMath::roundFinancial($totalOrderDiscount),
            voucherDiscount: FinancialMath::roundFinancial($totalVoucherDiscount),
            pointsDiscount: FinancialMath::roundFinancial($totalPointsDiscount),
            itemDiscount: FinancialMath::roundFinancial($totalItemDiscount),
            grossRevenue: FinancialMath::roundFinancial($grossRevenue),
            refundAmount: FinancialMath::roundFinancial($refundAmount),
            netSales: FinancialMath::roundFinancial($netSales),
            subtotal: FinancialMath::roundFinancial($totalSubtotal),
            taxAmount: FinancialMath::roundFinancial($totalTax),
            serviceChargeAmount: FinancialMath::roundFinancial($totalServiceCharge),
            roundingAmount: FinancialMath::roundFinancial($totalRounding),
            grandTotal: FinancialMath::roundFinancial($grandTotal),
            totalHpp: FinancialMath::roundFinancial($totalHpp),
            grossProfit: FinancialMath::roundFinancial($grossProfit),
            grossMarginPercent: $grossMarginPercent,
            totalOrders: $ordersCount,
            totalItemsSold: $totalItemsSold,
            averageOrderValue: FinancialMath::roundFinancial($aov),
            averageItemsPerTransaction: round($avgItemsPerTx, 2),
            averageSellingPrice: FinancialMath::roundFinancial($averageSellingPrice),
            averageCostPrice: FinancialMath::roundFinancial($averageCostPrice),
            goodsRevenue: FinancialMath::roundFinancial($goodsRevenue),
            goodsQuantity: $goodsQuantity,
            servicesRevenue: FinancialMath::roundFinancial($servicesRevenue),
            servicesQuantity: $servicesQuantity,
            cashSales: FinancialMath::roundFinancial($cashSales),
            nonCashSales: FinancialMath::roundFinancial($nonCashSales),
            totalPayments: FinancialMath::roundFinancial($totalPayments),
            todayRevenue: FinancialMath::roundFinancial($todayRevenue),
            todayOrders: $todayOrders,
            prevNetSales: $prevNetSales,
            prevGrossProfit: $prevGrossProfit,
            prevOrders: $prevOrders,
            salesGrowthPercent: $salesGrowthPercent,
            profitGrowthPercent: $profitGrowthPercent,
            ordersGrowthPercent: $ordersGrowthPercent
        );
    }

    /**
     * Tren Penjualan Harian.
     *
     * @return Collection<int, mixed>
     */
    public function getDailySalesTrend(PosReportFilterDTO $filter): Collection
    {
        return $this->buildBaseOrdersQuery($filter)
            ->selectRaw('
                pos_orders.order_date,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.total_amount), 0) as revenue,
                COALESCE(SUM(pos_orders.total_hpp_cost), 0) as hpp,
                COALESCE(SUM(pos_orders.total_gross_profit), 0) as profit
            ')
            ->groupBy('pos_orders.order_date')
            ->orderBy('pos_orders.order_date')
            ->get();
    }

    /**
     * Analisis Jam Ramai (Hourly Sales Heatmap).
     *
     * @return Collection<int, mixed>
     */
    public function getHourlyHeatmap(PosReportFilterDTO $filter): Collection
    {
        $hourExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', pos_orders.created_at) AS INTEGER)"
            : 'HOUR(pos_orders.created_at)';

        return $this->buildBaseOrdersQuery($filter)
            ->selectRaw("
                {$hourExpr} as order_hour,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.total_amount), 0) as total_sales,
                COALESCE(SUM(pos_orders.total_gross_profit), 0) as total_profit
            ")
            ->groupBy('order_hour')
            ->orderBy('order_hour')
            ->get();
    }

    /**
     * Matriks Performa Produk & Varian.
     *
     * @return Collection<int, mixed>
     */
    public function getProductPerformance(PosReportFilterDTO $filter, int $limit = 50): Collection
    {
        $itemsQuery = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->leftJoin('products', 'pos_order_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('pos_orders.business_id', $filter->businessId);

        if ($filter->status !== null && $filter->status !== '') {
            $itemsQuery->where('pos_orders.status', $filter->status);
        } else {
            $itemsQuery->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        }

        $itemsQuery->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
                   ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId) $itemsQuery->where('pos_orders.location_id', $filter->locationId);
        if ($filter->userId) $itemsQuery->where('pos_orders.user_id', $filter->userId);
        if ($filter->categoryId) $itemsQuery->where('products.category_id', $filter->categoryId);
        if ($filter->productId) $itemsQuery->where('pos_order_items.product_id', $filter->productId);

        return $itemsQuery
            ->selectRaw("
                pos_order_items.product_id,
                pos_order_items.product_name,
                COALESCE(pos_order_items.product_code, products.code, '-') as sku,
                COALESCE(product_categories.name, 'Tanpa Kategori') as category_name,
                COALESCE(products.type, 'goods') as item_type,
                COUNT(DISTINCT pos_orders.id) as transaction_count,
                COALESCE(SUM(pos_order_items.quantity), 0) as total_qty,
                COALESCE(SUM(pos_order_items.quantity * pos_order_items.unit_price), 0) as gross_sales,
                COALESCE(SUM(pos_order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(pos_order_items.total_price), 0) as net_sales,
                COALESCE(SUM(pos_order_items.total_hpp), 0) as total_hpp,
                COALESCE(SUM(pos_order_items.total_price - pos_order_items.total_hpp), 0) as gross_profit
            ")
            ->groupBy(
                'pos_order_items.product_id',
                'pos_order_items.product_name',
                DB::raw("COALESCE(pos_order_items.product_code, products.code, '-')"),
                DB::raw("COALESCE(product_categories.name, 'Tanpa Kategori')"),
                DB::raw("COALESCE(products.type, 'goods')")
            )
            ->orderByDesc('net_sales')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $sales = (float) $row->net_sales;
                $profit = (float) $row->gross_profit;
                $qty = (float) $row->total_qty;

                $row->margin_percent = FinancialMath::calculateMargin($profit, $sales);
                $row->asp = FinancialMath::safeDivide((float) $row->gross_sales, $qty);
                $row->unit_cost = FinancialMath::safeDivide((float) $row->total_hpp, $qty);

                return $row;
            });
    }

    /**
     * Matriks Kontribusi Kategori Produk (Analisis Pareto %).
     *
     * @return Collection<int, mixed>
     */
    public function getCategoryPerformance(PosReportFilterDTO $filter): Collection
    {
        $itemsQuery = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->leftJoin('products', 'pos_order_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('pos_orders.business_id', $filter->businessId);

        if ($filter->status !== null && $filter->status !== '') {
            $itemsQuery->where('pos_orders.status', $filter->status);
        } else {
            $itemsQuery->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        }

        $itemsQuery->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
                   ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId) $itemsQuery->where('pos_orders.location_id', $filter->locationId);
        if ($filter->userId) $itemsQuery->where('pos_orders.user_id', $filter->userId);

        $categories = $itemsQuery
            ->selectRaw("
                COALESCE(product_categories.id, 'uncategorized') as category_id,
                COALESCE(product_categories.name, 'Tanpa Kategori') as category_name,
                COUNT(DISTINCT pos_orders.id) as transaction_count,
                COALESCE(SUM(pos_order_items.quantity), 0) as total_qty,
                COALESCE(SUM(pos_order_items.quantity * pos_order_items.unit_price), 0) as gross_sales,
                COALESCE(SUM(pos_order_items.discount_amount), 0) as discount_amount,
                COALESCE(SUM(pos_order_items.total_price), 0) as net_sales,
                COALESCE(SUM(pos_order_items.total_hpp), 0) as total_hpp,
                COALESCE(SUM(pos_order_items.total_price - pos_order_items.total_hpp), 0) as gross_profit
            ")
            ->groupBy(
                DB::raw("COALESCE(product_categories.id, 'uncategorized')"),
                DB::raw("COALESCE(product_categories.name, 'Tanpa Kategori')")
            )
            ->orderByDesc('net_sales')
            ->get();

        $totalSalesSum = (float) $categories->sum('net_sales');

        return $categories->map(function ($row) use ($totalSalesSum) {
            $sales = (float) $row->net_sales;
            $profit = (float) $row->gross_profit;

            $row->margin_percent = FinancialMath::calculateMargin($profit, $sales);
            $row->contribution_percent = FinancialMath::safeDivide($sales, $totalSalesSum) * 100;

            return $row;
        });
    }

    /**
     * Kinerja & Integritas Kasir.
     *
     * @return Collection<int, mixed>
     */
    public function getCashierPerformance(PosReportFilterDTO $filter): Collection
    {
        return $this->buildBaseOrdersQuery($filter)
            ->leftJoin('users', 'pos_orders.user_id', '=', 'users.id')
            ->selectRaw("
                pos_orders.user_id,
                COALESCE(users.name, 'Kasir Terhapus') as cashier_name,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.subtotal), 0) as gross_sales,
                COALESCE(SUM(pos_orders.discount_amount + pos_orders.voucher_discount_amount + pos_orders.points_discount_amount), 0) as total_discount,
                COALESCE(SUM(pos_orders.total_amount), 0) as net_sales,
                COALESCE(SUM(pos_orders.total_hpp_cost), 0) as total_hpp,
                COALESCE(SUM(pos_orders.total_gross_profit), 0) as gross_profit
            ")
            ->groupBy('pos_orders.user_id', DB::raw("COALESCE(users.name, 'Kasir Terhapus')"))
            ->orderByDesc('net_sales')
            ->get()
            ->map(function ($row) {
                $count = (int) $row->orders_count;
                $sales = (float) $row->net_sales;
                $profit = (float) $row->gross_profit;

                $row->aov = FinancialMath::safeDivide($sales, (float) $count);
                $row->margin_percent = FinancialMath::calculateMargin($profit, $sales);

                return $row;
            });
    }

    /**
     * Performa Antar-Cabang / Outlet.
     *
     * @return Collection<int, mixed>
     */
    public function getOutletPerformance(PosReportFilterDTO $filter): Collection
    {
        $outlets = $this->buildBaseOrdersQuery($filter)
            ->leftJoin('locations', 'pos_orders.location_id', '=', 'locations.id')
            ->selectRaw("
                pos_orders.location_id,
                COALESCE(locations.name, 'Kantor Utama / Default') as location_name,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.subtotal), 0) as gross_sales,
                COALESCE(SUM(pos_orders.discount_amount + pos_orders.voucher_discount_amount + pos_orders.points_discount_amount), 0) as total_discount,
                COALESCE(SUM(pos_orders.total_amount), 0) as net_sales,
                COALESCE(SUM(pos_orders.total_hpp_cost), 0) as total_hpp,
                COALESCE(SUM(pos_orders.total_gross_profit), 0) as gross_profit
            ")
            ->groupBy('pos_orders.location_id', DB::raw("COALESCE(locations.name, 'Kantor Utama / Default')"))
            ->orderByDesc('net_sales')
            ->get();

        $totalSalesSum = (float) $outlets->sum('net_sales');

        return $outlets->map(function ($row) use ($totalSalesSum) {
            $count = (int) $row->orders_count;
            $sales = (float) $row->net_sales;
            $profit = (float) $row->gross_profit;

            $row->aov = FinancialMath::safeDivide($sales, (float) $count);
            $row->margin_percent = FinancialMath::calculateMargin($profit, $sales);
            $row->contribution_percent = FinancialMath::safeDivide($sales, $totalSalesSum) * 100;

            return $row;
        });
    }

    /**
     * Rincian Metode Pembayaran.
     *
     * @return Collection<int, mixed>
     */
    public function getPaymentMethodBreakdown(PosReportFilterDTO $filter): Collection
    {
        $payQuery = PosOrderPayment::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_payments.pos_order_id')
            ->where('pos_orders.business_id', $filter->businessId);

        if ($filter->status !== null && $filter->status !== '') {
            $payQuery->where('pos_orders.status', $filter->status);
        } else {
            $payQuery->whereIn('pos_orders.status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND]);
        }

        $payQuery->whereDate('pos_orders.order_date', '>=', $filter->startDate->toDateString())
                 ->whereDate('pos_orders.order_date', '<=', $filter->endDate->toDateString());

        if ($filter->locationId) $payQuery->where('pos_orders.location_id', $filter->locationId);
        if ($filter->userId) $payQuery->where('pos_orders.user_id', $filter->userId);

        $methods = $payQuery
            ->selectRaw('
                pos_order_payments.payment_method,
                COUNT(pos_order_payments.id) as tx_count,
                COALESCE(SUM(pos_order_payments.amount), 0) as total_amount,
                COALESCE(SUM(pos_order_payments.fee_amount), 0) as total_fee,
                COALESCE(SUM(CASE WHEN pos_order_payments.net_amount IS NOT NULL THEN pos_order_payments.net_amount ELSE pos_order_payments.amount END), 0) as net_settlement
            ')
            ->groupBy('pos_order_payments.payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $totalCollected = (float) $methods->sum('total_amount');

        return $methods->map(function ($row) use ($totalCollected) {
            $amount = (float) $row->total_amount;
            $row->contribution_percent = FinancialMath::safeDivide($amount, $totalCollected) * 100;

            return $row;
        });
    }

    /**
     * Rincian Diskon & Promosi.
     *
     * @return Collection<int, mixed>
     */
    public function getDiscountAnalytics(PosReportFilterDTO $filter): Collection
    {
        return $this->buildBaseOrdersQuery($filter)
            ->where(function ($q): void {
                $q->where('pos_orders.discount_amount', '>', 0)
                  ->orWhere('pos_orders.voucher_discount_amount', '>', 0)
                  ->orWhere('pos_orders.points_discount_amount', '>', 0);
            })
            ->selectRaw("
                pos_orders.discount_type,
                COALESCE(pos_orders.voucher_code, 'Diskon Langsung') as promo_code,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.subtotal), 0) as gross_sales,
                COALESCE(SUM(pos_orders.discount_amount), 0) as manual_discount,
                COALESCE(SUM(pos_orders.voucher_discount_amount), 0) as voucher_discount,
                COALESCE(SUM(pos_orders.points_discount_amount), 0) as points_discount,
                COALESCE(SUM(pos_orders.discount_amount + pos_orders.voucher_discount_amount + pos_orders.points_discount_amount), 0) as total_discount_given
            ")
            ->groupBy(
                'pos_orders.discount_type',
                DB::raw("COALESCE(pos_orders.voucher_code, 'Diskon Langsung')")
            )
            ->orderByDesc('total_discount_given')
            ->get();
    }

    /**
     * Breakdown Saluran Penjualan (POS vs Ojol Delivery vs Toko Online).
     *
     * @return Collection<int, mixed>
     */
    public function getSalesChannelBreakdown(PosReportFilterDTO $filter): Collection
    {
        $channels = $this->buildBaseOrdersQuery($filter)
            ->selectRaw("
                COALESCE(pos_orders.sales_channel, 'pos_direct') as channel_name,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.subtotal), 0) as gross_sales,
                COALESCE(SUM(pos_orders.discount_amount + pos_orders.voucher_discount_amount + pos_orders.points_discount_amount), 0) as total_discount,
                COALESCE(SUM(pos_orders.total_amount), 0) as net_sales,
                COALESCE(SUM(pos_orders.total_hpp_cost), 0) as total_hpp,
                COALESCE(SUM(pos_orders.total_gross_profit), 0) as gross_profit
            ")
            ->groupBy(DB::raw("COALESCE(pos_orders.sales_channel, 'pos_direct')"))
            ->orderByDesc('net_sales')
            ->get();

        $totalSalesSum = (float) $channels->sum('net_sales');

        return $channels->map(function ($row) use ($totalSalesSum) {
            $sales = (float) $row->net_sales;
            $row->contribution_percent = FinancialMath::safeDivide($sales, $totalSalesSum) * 100;

            return $row;
        });
    }

    /**
     * Ledger Retur & Refund Penjualan.
     *
     * @return Collection<int, SalesReturn>
     */
    public function getRefundsAndReturns(PosReportFilterDTO $filter, int $limit = 50): Collection
    {
        $query = SalesReturn::query()
            ->where('business_id', $filter->businessId)
            ->whereDate('return_date', '>=', $filter->startDate->toDateString())
            ->whereDate('return_date', '<=', $filter->endDate->toDateString())
            ->with(['customer', 'location', 'user', 'items.product']);

        if ($filter->locationId) $query->where('location_id', $filter->locationId);
        if ($filter->customerId) $query->where('customer_id', $filter->customerId);

        return $query->latest('return_date')->limit($limit)->get();
    }

    /**
     * Audit Trail Transaksi Void & Batal.
     *
     * @return Collection<int, PosOrder>
     */
    public function getVoidedOrders(PosReportFilterDTO $filter, int $limit = 50): Collection
    {
        $query = PosOrder::query()
            ->where('business_id', $filter->businessId)
            ->whereIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED])
            ->whereDate('order_date', '>=', $filter->startDate->toDateString())
            ->whereDate('order_date', '<=', $filter->endDate->toDateString())
            ->with(['user', 'location', 'customer', 'items']);

        if ($filter->locationId) $query->where('location_id', $filter->locationId);
        if ($filter->userId) $query->where('user_id', $filter->userId);

        return $query->latest('order_date')->limit($limit)->get();
    }

    /**
     * Rekonsiliasi Shift Kasir & Mutasi Kas Laci.
     *
     * @return Collection<int, PosShift>
     */
    public function getShiftReconciliation(PosReportFilterDTO $filter, int $limit = 50): Collection
    {
        $query = PosShift::query()
            ->where('business_id', $filter->businessId)
            ->whereDate('opened_at', '>=', $filter->startDate->toDateString())
            ->whereDate('opened_at', '<=', $filter->endDate->toDateString())
            ->with(['user', 'location', 'register']);

        if ($filter->locationId) $query->where('location_id', $filter->locationId);
        if ($filter->userId) $query->where('user_id', $filter->userId);

        return $query->latest('opened_at')->limit($limit)->get();
    }

    /**
     * Matriks Retensi & Loyalitas Pelanggan.
     *
     * @return Collection<int, mixed>
     */
    public function getCustomerSalesMatrix(PosReportFilterDTO $filter, int $limit = 50): Collection
    {
        return $this->buildBaseOrdersQuery($filter)
            ->leftJoin('customers', 'pos_orders.customer_id', '=', 'customers.id')
            ->selectRaw("
                pos_orders.customer_id,
                COALESCE(customers.name, pos_orders.customer_name_guest, 'Pelanggan Umum (Guest)') as customer_name,
                COALESCE(customers.phone, pos_orders.customer_phone_guest, '-') as customer_phone,
                CASE WHEN pos_orders.customer_id IS NOT NULL THEN 'Member' ELSE 'Guest' END as customer_type,
                COUNT(pos_orders.id) as orders_count,
                COALESCE(SUM(pos_orders.total_amount), 0) as total_spent,
                COALESCE(SUM(pos_orders.total_gross_profit), 0) as total_profit_contribution,
                MAX(pos_orders.order_date) as last_purchase_date
            ")
            ->groupBy(
                'pos_orders.customer_id',
                DB::raw("COALESCE(customers.name, pos_orders.customer_name_guest, 'Pelanggan Umum (Guest)')"),
                DB::raw("COALESCE(customers.phone, pos_orders.customer_phone_guest, '-')"),
                DB::raw("CASE WHEN pos_orders.customer_id IS NOT NULL THEN 'Member' ELSE 'Guest' END")
            )
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $count = (int) $row->orders_count;
                $spent = (float) $row->total_spent;

                $row->aov = FinancialMath::safeDivide($spent, (float) $count);

                return $row;
            });
    }

    /**
     * 3-Way Reconciliation Engine (Validasi Integritas Kasir & Gateway).
     */
    public function reconcile(PosReportFilterDTO $filter): PosReconciliationResultDTO
    {
        return $this->reconciliationService->reconcile($filter);
    }

    /**
     * Audit Void, Fraud & Pembatalan Transaksi.
     */
    public function auditVoidsAndFraud(PosReportFilterDTO $filter): PosFraudAuditDTO
    {
        return $this->reconciliationService->auditVoidsAndFraud($filter);
    }

    /**
     * Analisis Mendalam Margin & Profitabilitas Produk & Kategori.
     */
    public function analyzeMarginAndProfitability(PosReportFilterDTO $filter): PosMarginAnalyticsDTO
    {
        return $this->reconciliationService->analyzeMarginAndProfitability($filter);
    }

    /**
     * Analisis Kebocoran Diskon & Promosi.
     */
    public function analyzeDiscountsAndPromotions(PosReportFilterDTO $filter): PosDiscountAnalyticsDTO
    {
        return $this->reconciliationService->analyzeDiscountsAndPromotions($filter);
    }

    /**
     * Daftar Rekonsiliasi Kas Shift Kasir Granular.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getShiftReconciliationList(PosReportFilterDTO $filter): Collection
    {
        return $this->reconciliationService->getShiftReconciliationList($filter);
    }
}

