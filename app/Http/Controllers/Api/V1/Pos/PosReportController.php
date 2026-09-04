<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Report\SalesReportService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PosReportController extends Controller
{
    public function __construct(
        private readonly SalesReportService $salesReport = new SalesReportService
    ) {}

    /**
     * Resolve date range from request.
     *
     * @return array{Carbon, Carbon}
     */
    private function resolvePeriod(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : Carbon::today()->endOfDay();

        return [$startDate, $endDate];
    }

    /**
     * Summary sales report for POS terminal & mobile dashboard.
     */
    public function summary(Request $request): JsonResponse
    {
        return $this->salesSummary($request);
    }

    /**
     * Sales summary berbasis SNAPSHOT transaksi (average modal & jual,
     * total modal, total penjualan, gross profit, margin).
     */
    public function salesSummary(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $ordersQuery = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('location_id')) {
            $ordersQuery->where('location_id', $request->get('location_id'));
        }

        $orderIds = (clone $ordersQuery)->pluck('id');

        $report = $this->salesReport->summary(
            businessId: $business->id,
            from: $startDate,
            to: $endDate,
            locationId: $request->filled('location_id') ? (string) $request->get('location_id') : null
        );

        $totalSales = (float) $report['summary']['total_sales'];
        $totalHpp = (float) $report['summary']['total_modal'];
        $totalGrossProfit = (float) $report['summary']['total_gross_profit'];

        // Payment breakdown
        $paymentBreakdown = PosOrderPayment::whereIn('pos_order_id', $orderIds)
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($p) => [
                'method' => $p->payment_method,
                'total_amount' => (float) $p->total_amount,
                'count' => (int) $p->count,
            ]);

        // Top selling products (snapshot)
        $topProducts = PosOrderItem::whereIn('pos_order_id', $orderIds)
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue, SUM(total_hpp) as total_cost')
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn ($i) => [
                'product_name' => $i->product_name,
                'quantity' => (float) $i->total_qty,
                'revenue' => (float) $i->total_revenue,
                'total_hpp' => (float) $i->total_cost,
                'gross_profit' => round((float) $i->total_revenue - (float) $i->total_cost, 2),
            ]);

        return response()->json([
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_sales' => $totalSales,
                'total_orders' => $report['summary']['total_orders'],
                'total_quantity' => $report['summary']['total_quantity'],
                'total_hpp' => $totalHpp,
                'total_gross_profit' => $totalGrossProfit,
                'total_discount' => (float) (clone $ordersQuery)->sum('discount_amount'),
                'average_selling_price' => $report['summary']['average_selling_price'],
                'average_cost_price' => $report['summary']['average_cost_price'],
                'margin_percentage' => $totalSales > 0 ? round(($totalGrossProfit / $totalSales) * 100, 2) : 0.0,
                'source' => 'transaction_snapshot',
            ],
            'payment_methods' => $paymentBreakdown,
            'top_products' => $topProducts,
            'by_product' => $report['by_product'],
            'daily_trend' => $report['daily_trend'],
        ], Response::HTTP_OK);
    }

    /**
     * Average harga jual & harga modal (weighted average) dari SNAPSHOT transaksi,
     * bukan dari harga master produk.
     */
    public function averagePrices(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $averages = $this->salesReport->averagePrices(
            businessId: $business->id,
            from: $startDate,
            to: $endDate,
            locationId: $request->filled('location_id') ? (string) $request->get('location_id') : null
        );

        return response()->json([
            'data' => $averages,
        ], Response::HTTP_OK);
    }

    /**
     * Payment method breakdown over period.
     */
    public function paymentBreakdown(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $orderIds = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($request->filled('location_id'), fn ($q, $id) => $q->where('location_id', $id))
            ->pluck('id');

        $breakdown = PosOrderPayment::whereIn('pos_order_id', $orderIds)
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($p) => [
                'method' => $p->payment_method,
                'total_amount' => (float) $p->total_amount,
                'count' => (int) $p->count,
            ]);

        return response()->json([
            'payment_methods' => $breakdown,
        ], Response::HTTP_OK);
    }

    /**
     * Top selling products (snapshot based).
     */
    public function topProducts(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $orderIds = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($request->filled('location_id'), fn ($q, $id) => $q->where('location_id', $id))
            ->pluck('id');

        $topProducts = PosOrderItem::whereIn('pos_order_id', $orderIds)
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue, SUM(total_hpp) as total_cost')
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get()
            ->map(fn ($i) => [
                'product_name' => $i->product_name,
                'quantity' => (float) $i->total_qty,
                'revenue' => (float) $i->total_revenue,
                'average_selling_price' => (float) $i->total_qty > 0 ? round((float) $i->total_revenue / (float) $i->total_qty, 2) : 0.0,
                'average_cost_price' => (float) $i->total_qty > 0 ? round((float) $i->total_cost / (float) $i->total_qty, 2) : 0.0,
                'total_hpp' => (float) $i->total_cost,
                'gross_profit' => round((float) $i->total_revenue - (float) $i->total_cost, 2),
            ]);

        return response()->json([
            'top_products' => $topProducts,
        ], Response::HTTP_OK);
    }

    /**
     * Hourly sales heatmap.
     */
    public function hourlyHeatmap(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $hourly = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($request->filled('location_id'), fn ($q, $id) => $q->where('location_id', $id))
            ->selectRaw('HOUR(created_at) as order_hour, COUNT(*) as orders_count, SUM(total_amount) as total_sales')
            ->groupBy('order_hour')
            ->orderBy('order_hour')
            ->get()
            ->map(fn ($r) => [
                'hour' => (int) $r->order_hour,
                'orders_count' => (int) $r->orders_count,
                'total_sales' => (float) $r->total_sales,
            ]);

        return response()->json([
            'hourly' => $hourly,
        ], Response::HTTP_OK);
    }
}