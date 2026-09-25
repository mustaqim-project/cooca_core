<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AnalyticsWebController extends Controller
{
    /**
     * Tampilkan Halaman Dedicated Suite Analitik & Tren Bisnis.
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        // 1. Lokasi / Cabang Filter
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $selectedLocationId = $request->query('location_id');
        if ($selectedLocationId && ! $locations->contains('id', $selectedLocationId)) {
            $selectedLocationId = null;
        }

        // 2. Periode Filter
        $period = (string) $request->query('period', 'month');
        if (! in_array($period, ['today', 'week', 'month', 'year', 'custom'], true)) {
            $period = 'month';
        }

        $fromInput = (string) $request->query('from', '');
        $toInput = (string) $request->query('to', '');
        [$from, $to, $prevFrom, $prevTo] = $this->resolvePeriodAndPreviousRange($period, $fromInput, $toInput);

        // 3. Hitung Agregasi Data Periode Ini & Periode Sebelumnya
        $currentMetrics = $this->calculateMetrics($business, $from, $to, $selectedLocationId);
        $prevMetrics = $this->calculateMetrics($business, $prevFrom, $prevTo, $selectedLocationId);

        // 4. Hitung Growth Rate (%)
        $growth = [
            'revenue' => $this->calculateGrowth($currentMetrics['revenue'], $prevMetrics['revenue']),
            'orders' => $this->calculateGrowth($currentMetrics['orders_count'], $prevMetrics['orders_count']),
            'gross_profit' => $this->calculateGrowth($currentMetrics['gross_profit'], $prevMetrics['gross_profit']),
            'net_profit' => $this->calculateGrowth($currentMetrics['net_profit'], $prevMetrics['net_profit']),
            'avg_order_value' => $this->calculateGrowth($currentMetrics['avg_order_value'], $prevMetrics['avg_order_value']),
        ];

        // 5. Tren Waktu (Time Series)
        $trendSeries = $this->buildTrendSeries($business, $period, $from, $to, $selectedLocationId);

        // 6. Jam Sibuk Kasir (Hourly Peak Hours Heatmap)
        $hourlyHeatmap = $this->buildHourlyHeatmap($business, $from, $to, $selectedLocationId);

        // 7. Top 10 Produk Terlaris & Margin
        $topProducts = $this->buildTopProducts($business, $from, $to, $selectedLocationId);

        // 8. Distribusi Metode Pembayaran
        $paymentMethods = $this->buildPaymentMethods($business, $from, $to, $selectedLocationId);

        // 9. Performa Cabang (Jika Multi-Cabang)
        $locationPerformance = $this->buildLocationPerformance($business, $from, $to);

        $analytics = [
            'period' => $period,
            'period_label' => $this->getPeriodLabel($period),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'prev_from' => $prevFrom->toDateString(),
            'prev_to' => $prevTo->toDateString(),
            'selected_location_id' => $selectedLocationId,
            'current' => $currentMetrics,
            'previous' => $prevMetrics,
            'growth' => $growth,
            'trend' => $trendSeries,
            'hourly' => $hourlyHeatmap,
            'top_products' => $topProducts,
            'payment_methods' => $paymentMethods,
            'location_performance' => $locationPerformance,
        ];

        if ($request->ajax() || $request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        }

        return view('app.analytics.index', compact('business', 'locations', 'analytics', 'selectedLocationId'));
    }

    /**
     * Hitung metrik finansial pada suatu rentang tanggal.
     */
    private function calculateMetrics(Business $business, Carbon $from, Carbon $to, ?string $locationId = null): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        // POS
        $posQuery = PosOrder::where('business_id', $business->id)
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
            $posQuery->where('location_id', $locationId);
        }

        $posAgg = $posQuery->selectRaw('
            COALESCE(SUM(total_amount), 0) as revenue,
            COALESCE(SUM(total_hpp_cost), 0) as hpp,
            COALESCE(SUM(total_gross_profit), 0) as profit,
            COUNT(*) as cnt
        ')->first();

        // Invoice B2B
        $invQuery = Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereBetween('invoice_date', [$fromStr, $toStr]);

        if ($locationId) {
            $invQuery->where('location_id', $locationId);
        }

        $invAgg = $invQuery->selectRaw('
            COALESCE(SUM(paid_amount), 0) as revenue,
            COALESCE(SUM(total_hpp_cost), 0) as hpp,
            COALESCE(SUM(total_gross_profit), 0) as profit,
            COUNT(*) as cnt
        ')->first();

        // Toko Online
        $onlineQuery = CommerceOrder::where('business_id', $business->id)
            ->whereIn('status', [
                CommerceOrder::STATUS_PAID,
                CommerceOrder::STATUS_PROCESSING,
                CommerceOrder::STATUS_READY,
                CommerceOrder::STATUS_FULFILLED,
                CommerceOrder::STATUS_COMPLETED,
            ])
            ->whereBetween('created_at', [$fromStr . ' 00:00:00', $toStr . ' 23:59:59']);

        $onlineAgg = $onlineQuery->selectRaw('
            COALESCE(SUM(total_amount), 0) as revenue,
            COUNT(*) as cnt
        ')->first();

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

        $totalRevenue = $posRevenue + $invRevenue + $onlineRevenue;
        $totalHpp = $posHpp + $invHpp;
        $grossProfit = $posProfit + $invProfit;
        $totalOrders = $posCount + $invCount + $onlineCount;
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 0) : 0.0;
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0.0;

        // Beban Operasional
        $expenseQuery = Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$fromStr, $toStr]);
        if ($locationId) {
            $expenseQuery->where('location_id', $locationId);
        }
        $totalExpenses = (float) $expenseQuery->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;
        $netMarginPct = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

        return [
            'revenue' => $totalRevenue,
            'pos_revenue' => $posRevenue,
            'inv_revenue' => $invRevenue,
            'online_revenue' => $onlineRevenue,
            'hpp' => $totalHpp,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => $grossMarginPct,
            'expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'net_margin_pct' => $netMarginPct,
            'orders_count' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
        ];
    }

    /**
     * Hitung persentase pertumbuhan dibandingkan periode sebelumnya.
     */
    private function calculateGrowth(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    /**
     * Tentukan rentang tanggal saat ini dan rentang pembanding (periode sebelumnya).
     */
    private function resolvePeriodAndPreviousRange(string $period, string $fromInput, string $toInput): array
    {
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                $from = $today->copy();
                $to = $today->copy();
                $prevFrom = $today->copy()->subDay();
                $prevTo = $today->copy()->subDay();
                break;
            case 'week':
                $from = $today->copy()->startOfWeek();
                $to = $today->copy()->endOfWeek();
                $prevFrom = $from->copy()->subWeek();
                $prevTo = $to->copy()->subWeek();
                break;
            case 'year':
                $from = $today->copy()->startOfYear();
                $to = $today->copy()->endOfYear();
                $prevFrom = $from->copy()->subYear();
                $prevTo = $to->copy()->subYear();
                break;
            case 'custom':
                if (! empty($fromInput) && ! empty($toInput)) {
                    $from = Carbon::parse($fromInput)->startOfDay();
                    $to = Carbon::parse($toInput)->endOfDay();
                } else {
                    $from = $today->copy()->startOfMonth();
                    $to = $today->copy()->endOfMonth();
                }
                $days = $from->diffInDays($to) + 1;
                $prevFrom = $from->copy()->subDays($days);
                $prevTo = $to->copy()->subDays($days);
                break;
            case 'month':
            default:
                $from = $today->copy()->startOfMonth();
                $to = $today->copy()->endOfMonth();
                $prevFrom = $from->copy()->subMonthNoOverflow()->startOfMonth();
                $prevTo = $from->copy()->subMonthNoOverflow()->endOfMonth();
                break;
        }

        return [$from, $to, $prevFrom, $prevTo];
    }

    /**
     * Bangun time series trend data untuk Chart.js.
     */
    private function buildTrendSeries(Business $business, string $period, Carbon $from, Carbon $to, ?string $locationId = null): array
    {
        $labels = [];
        $revenueData = [];
        $profitData = [];

        if ($period === 'today') {
            // Jam per jam
            $driver = DB::connection()->getDriverName();
            $hourBucketExpr = $driver === 'sqlite' ? "strftime('%H:00', created_at)" : "DATE_FORMAT(created_at, '%H:00')";
            $posTrend = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereDate('order_date', $from->toDateString())
                ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
                ->selectRaw("{$hourBucketExpr} as bucket, SUM(total_amount) as revenue, SUM(total_gross_profit) as profit")
                ->groupBy('bucket')
                ->pluck('revenue', 'bucket')
                ->toArray();

            for ($h = 8; $h <= 22; $h++) {
                $bucket = sprintf('%02d:00', $h);
                $labels[] = $bucket;
                $revenueData[] = (float) ($posTrend[$bucket] ?? 0);
            }
        } else {
            // Harian
            $current = $from->copy();
            $posQuery = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
                ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
                ->selectRaw('order_date as bucket, SUM(total_amount) as revenue, SUM(total_gross_profit) as profit')
                ->groupBy('bucket')
                ->get()
                ->keyBy('bucket');

            while ($current->lte($to)) {
                $dtStr = $current->toDateString();
                $labels[] = $current->format('d M');
                $rev = (float) ($posQuery[$dtStr]->revenue ?? 0);
                $prof = (float) ($posQuery[$dtStr]->profit ?? 0);
                $revenueData[] = $rev;
                $profitData[] = $prof;
                $current->addDay();
            }
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'profit' => $profitData,
        ];
    }

    /**
     * Analisis jam sibuk kasir (08:00 - 23:00).
     */
    private function buildHourlyHeatmap(Business $business, Carbon $from, Carbon $to, ?string $locationId = null): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpr = $driver === 'sqlite' ? "CAST(strftime('%H', created_at) AS INTEGER)" : "HOUR(created_at)";

        $hourly = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->selectRaw("{$hourExpr} as hr, COUNT(*) as cnt, SUM(total_amount) as rev")
            ->groupBy('hr')
            ->get()
            ->keyBy('hr');

        $result = [];
        for ($h = 7; $h <= 23; $h++) {
            $row = $hourly->get($h);
            $result[] = [
                'hour_label' => sprintf('%02d:00', $h),
                'count' => (int) ($row?->cnt ?? 0),
                'revenue' => (float) ($row?->rev ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Top 10 produk dengan kuantitas dan margin tertinggi.
     */
    private function buildTopProducts(Business $business, Carbon $from, Carbon $to, ?string $locationId = null): array
    {
        $items = PosOrderItem::whereHas('order', function ($q) use ($business, $from, $to, $locationId) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
                ->when($locationId, fn ($sub) => $sub->where('location_id', $locationId));
        })
        ->selectRaw('product_name, SUM(quantity) as qty, SUM(subtotal) as total_sales, SUM(subtotal - COALESCE(unit_cost_hpp * quantity, 0)) as gross_profit')
        ->groupBy('product_name')
        ->orderByDesc('qty')
        ->limit(10)
        ->get();

        return $items->map(function ($item) {
            $sales = (float) $item->total_sales;
            $profit = (float) $item->gross_profit;
            $marginPct = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0.0;

            return [
                'name' => $item->product_name,
                'quantity' => (float) $item->qty,
                'total_sales' => $sales,
                'gross_profit' => $profit,
                'margin_pct' => $marginPct,
            ];
        })->toArray();
    }

    /**
     * Distribusi metode pembayaran.
     */
    private function buildPaymentMethods(Business $business, Carbon $from, Carbon $to, ?string $locationId = null): array
    {
        $methods = PosOrderPayment::whereHas('order', function ($q) use ($business, $from, $to, $locationId) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
                ->when($locationId, fn ($sub) => $sub->where('location_id', $locationId));
        })
        ->where('status', 'paid')
        ->selectRaw('payment_method, COUNT(*) as cnt, SUM(amount) as total')
        ->groupBy('payment_method')
        ->get();

        $labels = [
            'cash' => 'Tunai (Cash)',
            'qris' => 'QRIS Instan',
            'qris_dynamic' => 'QRIS Dinamis',
            'transfer' => 'Transfer Bank',
            'bank_transfer' => 'Transfer Bank',
            'edc_debit' => 'Kartu Debit',
            'edc_credit' => 'Kartu Kredit',
            'customer_credit' => 'Kasbon / Piutang',
            'loyalty_points' => 'Poin Loyalitas',
        ];

        return $methods->map(function ($m) use ($labels) {
            return [
                'method' => $m->payment_method ?? 'other',
                'label' => $labels[$m->payment_method] ?? ucfirst(str_replace('_', ' ', (string) $m->payment_method)),
                'count' => (int) $m->cnt,
                'total' => (float) $m->total,
            ];
        })->toArray();
    }

    /**
     * Performa tiap cabang (jika ada lebih dari 1 lokasi).
     */
    private function buildLocationPerformance(Business $business, Carbon $from, Carbon $to): array
    {
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        if ($locations->count() <= 1) {
            return [];
        }

        $perf = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('location_id, COUNT(*) as orders_count, SUM(total_amount) as revenue, SUM(total_gross_profit) as gross_profit')
            ->groupBy('location_id')
            ->get()
            ->keyBy('location_id');

        return $locations->map(function ($loc) use ($perf) {
            $row = $perf->get($loc->id);
            return [
                'id' => $loc->id,
                'name' => $loc->name,
                'type' => $loc->type,
                'orders_count' => (int) ($row?->orders_count ?? 0),
                'revenue' => (float) ($row?->revenue ?? 0),
                'gross_profit' => (float) ($row?->gross_profit ?? 0),
            ];
        })->toArray();
    }

    private function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'year' => 'Tahun Ini',
            'custom' => 'Kustom Rentang',
            default => 'Bulan Ini',
        };
    }
}
