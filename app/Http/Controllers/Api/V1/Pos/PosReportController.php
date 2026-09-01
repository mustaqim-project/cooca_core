<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

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
    /**
     * Summary sales report for POS terminal & mobile dashboard.
     */
    public function summary(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->get('end_date'))->endOfDay()
            : Carbon::today()->endOfDay();

        $ordersQuery = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('location_id')) {
            $ordersQuery->where('location_id', $request->get('location_id'));
        }

        $orderIds = (clone $ordersQuery)->pluck('id');

        $totalSales = (float) (clone $ordersQuery)->sum('total_amount');
        $totalOrders = (clone $ordersQuery)->count();
        $totalHpp = (float) (clone $ordersQuery)->sum('total_hpp_cost');
        $totalGrossProfit = (float) (clone $ordersQuery)->sum('total_gross_profit');
        $totalDiscount = (float) (clone $ordersQuery)->sum('discount_amount');

        // Payment breakdown
        $paymentBreakdown = PosOrderPayment::whereIn('pos_order_id', $orderIds)
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($p) => [
                'method' => $p->payment_method,
                'total_amount' => (float) $p->total_amount,
                'count' => (int) $p->count,
            ]);

        // Top selling products
        $topProducts = PosOrderItem::whereIn('pos_order_id', $orderIds)
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'product_name' => $i->product_name,
                'quantity' => (float) $i->total_qty,
                'revenue' => (float) $i->total_revenue,
            ]);

        return response()->json([
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'total_hpp' => $totalHpp,
                'total_gross_profit' => $totalGrossProfit,
                'total_discount' => $totalDiscount,
                'margin_percentage' => $totalSales > 0 ? round(($totalGrossProfit / $totalSales) * 100, 2) : 0.0,
            ],
            'payment_methods' => $paymentBreakdown,
            'top_products' => $topProducts,
        ], Response::HTTP_OK);
    }
}
