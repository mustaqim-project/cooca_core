<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PosReportWebController extends Controller
{
    /**
     * Display POS Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->subDays(29);
        $endDate = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today();

        $baseOrdersQuery = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED);

        // Filtered range
        $rangeOrders = (clone $baseOrdersQuery)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);

        // Key KPI metrics
        $totalRevenue = (float) (clone $rangeOrders)->sum('total_amount');
        $totalHpp = (float) (clone $rangeOrders)->sum('total_hpp_cost');
        $totalGrossProfit = (float) (clone $rangeOrders)->sum('total_gross_profit');
        $ordersCount = (clone $rangeOrders)->count();
        $averageOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0.0;
        $grossMarginPercent = $totalRevenue > 0 ? ($totalGrossProfit / $totalRevenue) * 100 : 0.0;

        // Today's summary
        $todayRevenue = (float) (clone $baseOrdersQuery)->whereDate('order_date', Carbon::today())->sum('total_amount');
        $todayOrders = (clone $baseOrdersQuery)->whereDate('order_date', Carbon::today())->count();

        // 1. Daily Sales Trend (Last 14 days)
        $dailyTrend = (clone $rangeOrders)
            ->selectRaw('order_date, SUM(total_amount) as revenue, SUM(total_hpp_cost) as hpp, SUM(total_gross_profit) as profit')
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        // 2. Peak Hours Analysis (Hourly Sales)
        $hourlyData = (clone $rangeOrders)
            ->selectRaw('HOUR(created_at) as order_hour, COUNT(*) as orders_count, SUM(total_amount) as total_sales')
            ->groupBy('order_hour')
            ->orderBy('order_hour')
            ->get();

        // 3. Payment Method Breakdown
        $paymentMethods = PosOrderPayment::whereHas('order', function ($q) use ($business, $startDate, $endDate) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as tx_count')
            ->groupBy('payment_method')
            ->get();

        // 4. Top 5 Selling Products
        $topProducts = PosOrderItem::whereHas('order', function ($q) use ($business, $startDate, $endDate) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue, SUM(total_hpp) as total_cost')
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // 5. Sales by Cashier
        $cashierPerformance = (clone $rangeOrders)
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as orders_count, SUM(total_amount) as total_sales')
            ->groupBy('user_id')
            ->get();

        return view('app.pos.reports', compact(
            'business',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalHpp',
            'totalGrossProfit',
            'grossMarginPercent',
            'ordersCount',
            'averageOrderValue',
            'todayRevenue',
            'todayOrders',
            'dailyTrend',
            'hourlyData',
            'paymentMethods',
            'topProducts',
            'cashierPerformance'
        ));
    }

    /**
     * Export POS Sales to Excel-compatible CSV.
     */
    public function exportExcel(Request $request): Response
    {
        $business = Context::requireBusiness();

        $orders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->with(['customer', 'user', 'location', 'payments'])
            ->latest('order_date')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-penjualan-pos-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders, $business) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'No. Order',
                'Tanggal',
                'Outlet',
                'Kasir',
                'Pelanggan',
                'Tipe Order',
                'Subtotal',
                'Diskon',
                'Pajak',
                'Service Charge',
                'Total Bayar',
                'HPP / Modal',
                'Laba Kotor',
                'Margin %',
                'Metode Pembayaran',
            ]);

            foreach ($orders as $o) {
                $payMethods = $o->payments->pluck('payment_method')->implode(', ');
                $margin = $o->total_amount > 0 ? round(($o->total_gross_profit / $o->total_amount) * 100, 2) : 0;

                fputcsv($file, [
                    $o->order_number,
                    $o->order_date->format('Y-m-d'),
                    $o->location->name ?? '-',
                    $o->user->name ?? '-',
                    $o->customer->name ?? $o->customer_name_guest ?? 'Umum',
                    strtoupper($o->order_type),
                    $o->subtotal,
                    $o->discount_amount + $o->voucher_discount_amount,
                    $o->tax_amount,
                    $o->service_charge_amount,
                    $o->total_amount,
                    $o->total_hpp_cost,
                    $o->total_gross_profit,
                    $margin . '%',
                    $payMethods,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
