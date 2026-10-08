<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CommerceOrder;
use App\Models\InventoryStock;
use App\Models\PosOrder;
use App\Models\PosShift;
use App\Models\Product;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MobileOwnerPulseApiController extends Controller
{
    /**
     * Real-time mobile heartbeat summary for Business Owner & Managers.
     * GET /api/v1/mobile/dashboard/pulse
     */
    public function pulse(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // 1. POS Sales Today & Yesterday
        $posToday = PosOrder::where('business_id', $business->id)
            ->whereDate('order_date', $today)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, 'closed'])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue, COALESCE(SUM(total_gross_profit), 0) as profit')
            ->first();

        $posYesterday = PosOrder::where('business_id', $business->id)
            ->whereDate('order_date', $yesterday)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, 'closed'])
            ->sum('total_amount');

        // 2. Online Store (Commerce Orders) Today & Yesterday
        $commerceToday = CommerceOrder::where('business_id', $business->id)
            ->whereDate('created_at', $today)
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        $commerceYesterday = CommerceOrder::where('business_id', $business->id)
            ->whereDate('created_at', $yesterday)
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])
            ->sum('total_amount');

        $totalRevenueToday = (float) (($posToday?->revenue ?? 0) + ($commerceToday?->revenue ?? 0));
        $totalRevenueYesterday = (float) ($posYesterday + $commerceYesterday);
        $totalTransactionsToday = (int) (($posToday?->count ?? 0) + ($commerceToday?->count ?? 0));
        $totalGrossProfitToday = (float) ($posToday?->profit ?? 0);

        $growthPct = 0.0;
        if ($totalRevenueYesterday > 0) {
            $growthPct = round((($totalRevenueToday - $totalRevenueYesterday) / $totalRevenueYesterday) * 100, 1);
        }

        $profitMarginPct = $totalRevenueToday > 0
            ? round(($totalGrossProfitToday / $totalRevenueToday) * 100, 1)
            : 0.0;

        $avgTicket = $totalTransactionsToday > 0
            ? round($totalRevenueToday / $totalTransactionsToday, 2)
            : 0.0;

        // 3. Active Cashier Shifts
        $activeShifts = PosShift::where('business_id', $business->id)
            ->where('status', 'open')
            ->with(['user:id,name', 'location:id,name'])
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'cashier_name' => $s->user?->name ?? 'Kasir',
                'location_name' => $s->location?->name ?? 'Utama',
                'opened_at' => $s->opened_at?->toIso8601String(),
                'opening_cash' => (float) $s->opening_cash,
            ]);

        // 4. Kitchen Queue (KDS) Active Tickets
        $kitchenPending = PosOrder::where('business_id', $business->id)
            ->whereIn('status', [
                PosOrder::STATUS_CONFIRMED,
                PosOrder::STATUS_PREPARING,
            ])
            ->count();

        // 5. Online Store Pending Orders (Awaiting Verification or Packing)
        $pendingCommerceOrders = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', ['pending_payment', 'payment_uploaded', 'paid'])
            ->count();

        // 6. Critical Low Stock Alerts (Top 5 items)
        $criticalStocks = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('min_stock', '>', 0)
            ->with(['stocks', 'outputUnit'])
            ->get()
            ->filter(function (Product $p) {
                $currentStock = (float) $p->stocks->sum('quantity');
                return $currentStock <= (float) $p->min_stock;
            })
            ->take(5)
            ->values()
            ->map(function (Product $p) {
                $currentStock = (float) $p->stocks->sum('quantity');
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'current_stock' => $currentStock,
                    'min_stock' => (float) $p->min_stock,
                    'unit' => $p->outputUnit?->code ?? 'pcs',
                ];
            });

        // 7. Today's Hourly Sparkline Trend
        $hourlyTrend = [];
        $currentHour = (int) now()->format('H');
        for ($h = max(0, $currentHour - 11); $h <= $currentHour; $h++) {
            $hourStart = now()->setTime($h, 0, 0);
            $hourEnd = now()->setTime($h, 59, 59);

            $hourlyRevenue = (float) PosOrder::where('business_id', $business->id)
                ->whereBetween('created_at', [$hourStart, $hourEnd])
                ->whereIn('status', [PosOrder::STATUS_COMPLETED, 'closed'])
                ->sum('total_amount');

            $hourlyTrend[] = [
                'hour' => sprintf('%02d:00', $h),
                'revenue' => $hourlyRevenue,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan operasional bisnis berhasil dimuat.',
            'data' => [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'currency' => $business->currency ?? 'IDR',
                ],
                'sales_today' => [
                    'total_revenue' => $totalRevenueToday,
                    'pos_revenue' => (float) ($posToday?->revenue ?? 0),
                    'commerce_revenue' => (float) ($commerceToday?->revenue ?? 0),
                    'total_transactions' => $totalTransactionsToday,
                    'avg_ticket' => $avgTicket,
                    'yesterday_revenue' => $totalRevenueYesterday,
                    'growth_pct' => $growthPct,
                ],
                'profitability' => [
                    'gross_profit' => $totalGrossProfitToday,
                    'margin_pct' => $profitMarginPct,
                ],
                'operations' => [
                    'active_cashiers_count' => $activeShifts->count(),
                    'active_cashiers' => $activeShifts,
                    'kitchen_queue_count' => $kitchenPending,
                    'pending_online_orders_count' => $pendingCommerceOrders,
                ],
                'critical_low_stocks' => $criticalStocks,
                'hourly_trend' => $hourlyTrend,
                'generated_at' => now()->toIso8601String(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Complete Production Analytics Cockpit for Mobile App with full parity to Web Dashboard.
     * GET /api/v1/mobile/dashboard/analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $period = (string) $request->query('period', 'month');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');
        $locationId = (string) ($request->query('location_id') ?? $request->header('X-Outlet-Id') ?? '');

        $service = app(\App\Domain\Report\DashboardAnalyticsService::class);
        $analytics = $service->getAnalyticsData($business, $period, $from, $to, $locationId ?: null);
        $overview = $service->getOverviewData($business, $locationId ?: null);

        $recentPosOrders = collect($overview['recentPosOrders'] ?? [])->map(function ($o) {
            return [
                'id' => (string) $o->id,
                'order_number' => (string) $o->order_number,
                'customer_name_guest' => (string) ($o->customer_name_guest ?? 'Pelanggan Umum'),
                'order_type' => (string) ($o->order_type ?? 'dine_in'),
                'total_amount' => (float) $o->total_amount,
                'status' => (string) $o->status,
                'created_at' => $o->created_at?->toIso8601String(),
                'time_ago' => $o->created_at?->diffForHumans() ?? 'Baru saja',
            ];
        })->values()->all();

        $recentProducts = collect($overview['recentProducts'] ?? [])->map(function ($p) {
            $baseCost = (float) ($p->base_cost ?? 0);
            $sellingPrice = (float) ($p->selling_price ?? 0);
            $marginPct = ($sellingPrice > 0 && $baseCost > 0)
                ? round((($sellingPrice - $baseCost) / $sellingPrice) * 100, 1)
                : null;

            return [
                'id' => (string) $p->id,
                'name' => (string) $p->name,
                'code' => (string) ($p->code ?? ''),
                'category' => (string) ($p->category?->name ?? 'Item'),
                'base_cost' => $baseCost,
                'selling_price' => $sellingPrice,
                'margin_pct' => $marginPct,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'analytics' => $analytics,
            'stats' => $overview['stats'] ?? [],
            'seven_days_trend' => $overview['sevenDaysTrend'] ?? [],
            'recent_pos_orders' => $recentPosOrders,
            'recent_products' => $recentProducts,
        ], Response::HTTP_OK);
    }
}
