<?php

declare(strict_types=1);

namespace App\Domain\Ai;

use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosShift;
use App\Models\Product;
use App\Domain\Billing\EntitlementService;
use App\Domain\Sales\SalesPipelineService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

final class AiSalesAnalysisService
{
    /**
     * 1. Sales Forecasting: Linear Trend + Day-of-Week Seasonality Decomposition.
     *
     * @return array<string, mixed>
     */
    public function getSalesForecasting(Business $business, int $futureDays = 14): array
    {
        $today = Carbon::today();
        $historyDays = 30;
        $startDate = $today->copy()->subDays($historyDays);

        // Fetch daily revenue and order counts for the last 30 days
        $historyData = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('order_date', '>=', $startDate->toDateString())
            ->selectRaw('order_date, SUM(total_amount) as revenue, SUM(total_gross_profit) as profit, COUNT(*) as orders_count')
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get()
            ->keyBy('order_date');

        // Fill missing days with zero
        $dailyPoints = [];
        $dayOfWeekSums = array_fill(0, 7, ['total' => 0.0, 'count' => 0]);
        $allRevenues = [];

        for ($i = $historyDays; $i >= 1; $i--) {
            $d = $today->copy()->subDays($i);
            $dStr = $d->toDateString();
            $dow = (int) $d->dayOfWeek; // 0 (Sunday) - 6 (Saturday)

            $record = $historyData->get($dStr);
            $rev = $record ? (float) $record->revenue : 0.0;
            $orders = $record ? (int) $record->orders_count : 0;
            $profit = $record ? (float) $record->profit : 0.0;

            $dailyPoints[] = [
                'date' => $dStr,
                'day_name' => $d->translatedFormat('D'),
                'revenue' => $rev,
                'profit' => $profit,
                'orders' => $orders,
            ];

            $allRevenues[] = $rev;
            $dayOfWeekSums[$dow]['total'] += $rev;
            $dayOfWeekSums[$dow]['count']++;
        }

        $n = count($allRevenues);
        $meanRevenue = $n > 0 ? (array_sum($allRevenues) / $n) : 0.0;

        // Calculate Day of Week Seasonality Multipliers
        $dowMultipliers = [];
        for ($dow = 0; $dow < 7; $dow++) {
            $avgDow = $dayOfWeekSums[$dow]['count'] > 0 
                ? ($dayOfWeekSums[$dow]['total'] / $dayOfWeekSums[$dow]['count']) 
                : $meanRevenue;
            $dowMultipliers[$dow] = $meanRevenue > 0 ? ($avgDow / $meanRevenue) : 1.0;
        }

        // Linear Regression Trend (y = slope * x + intercept)
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;

        foreach ($allRevenues as $x => $y) {
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumXX += ($x * $x);
        }

        $denominator = ($n * $sumXX - ($sumX * $sumX));
        $slope = $denominator != 0 ? (($n * $sumXY - ($sumX * $sumY)) / $denominator) : 0.0;
        $intercept = $n > 0 ? (($sumY - $slope * $sumX) / $n) : 0.0;

        // Standard deviation for confidence interval
        $varianceSum = 0.0;
        foreach ($allRevenues as $rev) {
            $varianceSum += pow($rev - $meanRevenue, 2);
        }
        $stdDev = $n > 1 ? sqrt($varianceSum / ($n - 1)) : ($meanRevenue * 0.15);

        // Generate Future Projections
        $forecastList = [];
        $totalForecastRevenue = 0.0;
        $peakProjectedDay = null;
        $highestForecast = -1.0;

        for ($j = 0; $j < $futureDays; $j++) {
            $futureDate = $today->copy()->addDays($j);
            $xFuture = $n + $j;
            $dow = (int) $futureDate->dayOfWeek;

            // Trend base + seasonality multiplier
            $trendBase = max(0.0, $intercept + ($slope * $xFuture));
            if ($meanRevenue <= 0) {
                // Default baseline if no past orders
                $trendBase = 250000;
            }

            $predicted = max(0.0, round($trendBase * $dowMultipliers[$dow]));
            $lowerBound = max(0.0, round($predicted - ($stdDev * 1.2)));
            $upperBound = round($predicted + ($stdDev * 1.2));
            $predictedOrders = max(1, (int) round($predicted / max(1, ($meanRevenue > 0 && array_sum(array_column($dailyPoints, 'orders')) > 0 ? ($meanRevenue / (array_sum(array_column($dailyPoints, 'orders')) / $n)) : 35000))));

            $forecastList[] = [
                'date' => $futureDate->toDateString(),
                'formatted_date' => $futureDate->translatedFormat('d M'),
                'day_name' => $futureDate->translatedFormat('l'),
                'predicted_revenue' => $predicted,
                'predicted_orders' => $predictedOrders,
                'lower_bound' => $lowerBound,
                'upper_bound' => $upperBound,
            ];

            $totalForecastRevenue += $predicted;
            if ($predicted > $highestForecast) {
                $highestForecast = $predicted;
                $peakProjectedDay = $futureDate->translatedFormat('l, d F');
            }
        }

        $trendDirection = $slope > 500 ? 'naik' : ($slope < -500 ? 'turun' : 'stabil');
        $growthPercentage = $meanRevenue > 0 ? round(($slope * 7 / $meanRevenue) * 100, 1) : 0.0;

        return [
            'history' => $dailyPoints,
            'forecast' => $forecastList,
            'mean_revenue_past' => round($meanRevenue),
            'total_forecast_revenue' => round($totalForecastRevenue),
            'trend_direction' => $trendDirection,
            'growth_percentage' => $growthPercentage,
            'peak_projected_day' => $peakProjectedDay ?? 'Hari Libur / Akhir Pekan',
            'model_info' => 'Linear Trend + 7-Day Seasonality Decomposition',
        ];
    }

    /**
     * 2. Stock Runout & Reorder Optimization.
     *
     * @return array<string, mixed>
     */
    public function getStockPredictionAndReorder(Business $business): array
    {
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['stocks', 'outputUnit'])
            ->get();

        $thirtyDaysAgo = Carbon::today()->subDays(30)->toDateString();

        // Calculate sales volume in last 30 days per product
        $salesVolumes = PosOrderItem::whereHas('order', function ($q) use ($business, $thirtyDaysAgo) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->where('order_date', '>=', $thirtyDaysAgo);
        })
        ->selectRaw('product_id, SUM(quantity) as total_sold')
        ->groupBy('product_id')
        ->pluck('total_sold', 'product_id');

        $predictions = [];
        $criticalCount = 0;
        $warningCount = 0;

        foreach ($products as $product) {
            $totalStock = (float) $product->stocks->sum('quantity');
            $sold30 = (float) ($salesVolumes->get($product->id) ?? 0.0);
            
            // Daily sales velocity
            $dailyVelocity = $sold30 > 0 ? ($sold30 / 30.0) : 0.0;

            if ($dailyVelocity > 0) {
                $runoutDays = round($totalStock / $dailyVelocity, 1);
            } else {
                $runoutDays = $totalStock > 0 ? 999.0 : 0.0;
            }

            // Lead time standard assumption: 3 days
            $leadTimeDays = 3;
            $safetyStockDays = 4;
            $safetyStock = ceil($dailyVelocity * $safetyStockDays);
            $reorderPoint = ceil(($dailyVelocity * $leadTimeDays) + $safetyStock);

            // Suggested reorder quantity (e.g. 14 days of inventory)
            $suggestedOrderQty = ceil(max(10, $dailyVelocity * 14));

            // Status determination
            if ($totalStock <= 0 || $runoutDays <= 2) {
                $urgency = 'critical';
                $criticalCount++;
            } elseif ($runoutDays <= 7 || $totalStock <= $reorderPoint) {
                $urgency = 'warning';
                $warningCount++;
            } else {
                $urgency = 'safe';
            }

            $estimatedRunoutDate = $runoutDays < 999 
                ? Carbon::today()->addDays((int) floor($runoutDays))->translatedFormat('d M Y') 
                : 'Stok Aman (> 6 bulan)';

            $predictions[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code ?? '-',
                'unit' => $product->outputUnit->symbol ?? 'pcs',
                'current_stock' => $totalStock,
                'daily_velocity' => round($dailyVelocity, 2),
                'sold_last_30_days' => $sold30,
                'runout_days' => $runoutDays < 999 ? $runoutDays : '999+',
                'estimated_stockout_date' => $estimatedRunoutDate,
                'reorder_point' => $reorderPoint,
                'safety_stock' => $safetyStock,
                'suggested_reorder_qty' => $suggestedOrderQty,
                'urgency' => $urgency,
            ];
        }

        // Sort by urgency (critical first, then warning, then safe)
        usort($predictions, function ($a, $b) {
            $order = ['critical' => 1, 'warning' => 2, 'safe' => 3];
            if ($order[$a['urgency']] === $order[$b['urgency']]) {
                return (float) ($a['runout_days'] === '999+' ? 999 : $a['runout_days']) <=> (float) ($b['runout_days'] === '999+' ? 999 : $b['runout_days']);
            }
            return $order[$a['urgency']] <=> $order[$b['urgency']];
        });

        return [
            'products' => $predictions,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'total_analyzed' => count($predictions),
        ];
    }

    /**
     * 3. Anomaly & Fraud Detection Engine.
     *
     * @return array<string, mixed>
     */
    public function detectAnomaliesAndFraud(Business $business): array
    {
        $alerts = [];
        $now = Carbon::now();
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        // 1. Z-Score Transaction Outlier Detection
        $orders = PosOrder::where('business_id', $business->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();

        if ($orders->count() >= 5) {
            $amounts = $orders->pluck('total_amount')->map(fn($v) => (float) $v)->toArray();
            $mean = array_sum($amounts) / count($amounts);
            $stdDev = sqrt(array_sum(array_map(fn($x) => pow($x - $mean, 2), $amounts)) / count($amounts));

            $threshold = $mean + (2.5 * max(1000, $stdDev));

            foreach ($orders as $o) {
                if ($o->status === PosOrder::STATUS_COMPLETED && $o->total_amount > $threshold && $threshold > 0) {
                    $alerts[] = [
                        'type' => 'unusual_order_value',
                        'severity' => 'warning',
                        'title' => "Transaksi Bernilai Ekstrem: #{$o->order_number}",
                        'description' => "Order sebesar Rp " . number_format($o->total_amount, 0, ',', '.') . " berada 2.5x di atas rata-rata transaksi (Rp " . number_format($mean, 0, ',', '.') . ").",
                        'cashier' => $o->user->name ?? 'Kasir',
                        'date' => $o->created_at->format('d/m/Y H:i'),
                        'recommendation' => "Periksa keabsahan pesanan dan struk pembayaran.",
                    ];
                }
            }
        }

        // 2. Excessive Void / Refund per Cashier
        $cashiers = User::whereHas('businesses', fn($q) => $q->where('businesses.id', $business->id))->get();
        foreach ($cashiers as $c) {
            $totalOrders = PosOrder::where('business_id', $business->id)->where('user_id', $c->id)->where('created_at', '>=', $thirtyDaysAgo)->count();
            $voidOrders = PosOrder::where('business_id', $business->id)->where('user_id', $c->id)->where('status', PosOrder::STATUS_VOIDED)->where('created_at', '>=', $thirtyDaysAgo)->count();

            if ($totalOrders >= 5 && $voidOrders > 0) {
                $voidRate = ($voidOrders / $totalOrders) * 100;
                if ($voidRate >= 15.0) {
                    $alerts[] = [
                        'type' => 'excessive_voids',
                        'severity' => 'danger',
                        'title' => "Tingkat Pembatalan (Void) Tinggi: {$c->name}",
                        'description' => "Kasir {$c->name} mencatat {$voidOrders} kali void dari {$totalOrders} transaksi (" . round($voidRate, 1) . "%). Rata-rata normal toko adalah < 3%.",
                        'cashier' => $c->name,
                        'date' => $now->translatedFormat('d F Y'),
                        'recommendation' => "Lakukan audit transaksi void dan pastikan setiap pembatalan memerlukan persetujuan Supervisor PIN.",
                    ];
                }
            }
        }

        // 3. Cash Drawer Discrepancies
        $abnormalShifts = PosShift::where('business_id', $business->id)
            ->where('status', PosShift::STATUS_CLOSED)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereRaw('ABS(cash_difference) >= 50000')
            ->with(['user', 'location'])
            ->latest('closed_at')
            ->take(5)
            ->get();

        foreach ($abnormalShifts as $sh) {
            $diff = (float) $sh->cash_difference;
            $alerts[] = [
                'type' => 'cash_drawer_variance',
                'severity' => abs($diff) >= 100000 ? 'danger' : 'warning',
                'title' => "Selisih Laci Kas Signifikan: Rp " . number_format($diff, 0, ',', '.'),
                'description' => "Pada shift {$sh->user->name} (" . ($sh->closed_at ? $sh->closed_at->format('d/m H:i') : '') . "), uang fisik aktual laci kas tercatat " . ($diff < 0 ? 'kurang (minus)' : 'berlebih') . " Rp " . number_format(abs($diff), 0, ',', '.') . " dibanding sistem.",
                'cashier' => $sh->user->name ?? 'Kasir',
                'date' => $sh->closed_at ? $sh->closed_at->format('d/m/Y H:i') : '-',
                'recommendation' => "Cocokkan ulang dengan struk kas keluar (cash-out) atau nota pembelian operasional.",
            ];
        }

        // 4. Abnormal Discounts
        $highDiscountOrders = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->where('subtotal', '>', 0)
            ->whereRaw('(discount_amount / subtotal) >= 0.30')
            ->with('user')
            ->take(5)
            ->get();

        foreach ($highDiscountOrders as $hdo) {
            $pct = round(($hdo->discount_amount / $hdo->subtotal) * 100);
            $alerts[] = [
                'type' => 'unusual_discount',
                'severity' => 'warning',
                'title' => "Penerapan Diskon Manual Tinggi: {$pct}%",
                'description' => "Order #{$hdo->order_number} oleh {$hdo->user->name} diberikan diskon sebesar Rp " . number_format($hdo->discount_amount, 0, ',', '.') . " ({$pct}% dari subtotal).",
                'cashier' => $hdo->user->name ?? 'Kasir',
                'date' => $hdo->created_at->format('d/m/Y H:i'),
                'recommendation' => "Verifikasi apakah diskon tersebut memiliki otorisasi voucher atau promo resmi.",
            ];
        }

        return [
            'alerts' => $alerts,
            'total_alerts' => count($alerts),
            'danger_count' => count(array_filter($alerts, fn($a) => $a['severity'] === 'danger')),
            'warning_count' => count(array_filter($alerts, fn($a) => $a['severity'] === 'warning')),
        ];
    }

    /**
     * 4. BCG / Menu Engineering Matrix (Product Profitability Matrix).
     *
     * @return array<string, mixed>
     */
    public function getProductProfitabilityMatrix(Business $business): array
    {
        $items = PosOrderItem::whereHas('order', function ($q) use ($business) {
            $q->where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED);
        })
        ->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(total_price) as total_sales, SUM(total_hpp) as total_cost')
        ->groupBy('product_id', 'product_name')
        ->get();

        if ($items->isEmpty()) {
            return [
                'stars' => [],
                'plowhorses' => [],
                'puzzles' => [],
                'dogs' => [],
                'avg_volume' => 0,
                'avg_margin' => 0,
            ];
        }

        $totalVolume = $items->sum('total_qty');
        $avgVolumePerProduct = $items->count() > 0 ? ($totalVolume / $items->count()) : 0.0;

        // Calculate average margin % across all products
        $marginList = [];
        foreach ($items as $item) {
            $sales = (float) $item->total_sales;
            $cost = (float) $item->total_cost;
            $marginPct = $sales > 0 ? (($sales - $cost) / $sales) * 100 : 0.0;
            $marginList[] = $marginPct;
        }
        $avgMarginPct = count($marginList) > 0 ? (array_sum($marginList) / count($marginList)) : 50.0;

        $stars = [];
        $plowhorses = [];
        $puzzles = [];
        $dogs = [];

        foreach ($items as $item) {
            $qty = (float) $item->total_qty;
            $sales = (float) $item->total_sales;
            $cost = (float) $item->total_cost;
            $profit = $sales - $cost;
            $marginPct = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0.0;

            $highVolume = $qty >= $avgVolumePerProduct;
            $highMargin = $marginPct >= $avgMarginPct;

            $entry = [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'total_qty' => $qty,
                'total_sales' => $sales,
                'gross_profit' => $profit,
                'margin_percent' => $marginPct,
            ];

            if ($highVolume && $highMargin) {
                $entry['action'] = 'Bintang Utama! Pertahankan resep, konsistensi rasa, dan prioritaskan stok.';
                $stars[] = $entry;
            } elseif ($highVolume && ! $highMargin) {
                $entry['action'] = 'Sangat Laris tapi Margin Tipis! Pertimbangkan naikkan harga 5-10% atau negosiasi HPP bahan baku.';
                $plowhorses[] = $entry;
            } elseif (! $highVolume && $highMargin) {
                $entry['action'] = 'Margin Laba Tinggi! Tingkatkan promosi, pajang di kasir, atau tawarkan program bundling.';
                $puzzles[] = $entry;
            } else {
                $entry['action'] = 'Performa Lemah. Tinjau ulang resep atau ganti dengan varian baru yang lebih diminati pasar.';
                $dogs[] = $entry;
            }
        }

        return [
            'stars' => $stars,
            'plowhorses' => $plowhorses,
            'puzzles' => $puzzles,
            'dogs' => $dogs,
            'avg_volume' => round($avgVolumePerProduct, 1),
            'avg_margin' => round($avgMarginPct, 1),
        ];
    }

    /**
     * 5. Smart Dynamic Pricing Recommendations.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSmartPricingRecommendations(Business $business): array
    {
        $targetMargin = 55.0; // 55% standard benchmark target
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();
        $recommendations = [];

        foreach ($products as $p) {
            $baseCost = (float) $p->base_cost;
            $currentPrice = (float) $p->selling_price;

            if ($baseCost <= 0 || $currentPrice <= 0) {
                continue;
            }

            $currentMargin = (($currentPrice - $baseCost) / $currentPrice) * 100.0;

            if ($currentMargin < 45.0) {
                // Formula: Recommended Price = Base Cost / (1 - Target Margin %)
                $recPriceRaw = $baseCost / (1.0 - ($targetMargin / 100.0));
                $recPrice = ceil($recPriceRaw / 500.0) * 500.0; // Round to nearest 500

                $projectedMargin = round((($recPrice - $baseCost) / $recPrice) * 100.0, 1);
                $potentialProfitIncrease = $recPrice - $currentPrice;

                $recommendations[] = [
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'base_cost' => $baseCost,
                    'current_price' => $currentPrice,
                    'current_margin' => round($currentMargin, 1),
                    'recommended_price' => $recPrice,
                    'projected_margin' => $projectedMargin,
                    'profit_increase_per_unit' => $potentialProfitIncrease,
                    'reason' => "Margin saat ini (" . round($currentMargin, 1) . "%) di bawah standar aman 50%. Menaikkan ke Rp " . number_format($recPrice, 0, ',', '.') . " meningkatkan profit Rp " . number_format($potentialProfitIncrease, 0, ',', '.') . " per transaksi.",
                ];
            }
        }

        return $recommendations;
    }

    /**
     * 6. Smart Promo & Bundling Recommendations (Market Basket Analysis).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSmartPromoRecommendations(Business $business): array
    {
        // Find multi-item orders
        $orderIds = PosOrderItem::whereHas('order', fn($q) => $q->where('business_id', $business->id)->where('status', PosOrder::STATUS_COMPLETED))
            ->select('pos_order_id')
            ->groupBy('pos_order_id')
            ->havingRaw('COUNT(DISTINCT product_id) >= 2')
            ->pluck('pos_order_id');

        $pairsCount = [];
        $productNames = [];

        foreach ($orderIds as $oId) {
            $pIds = PosOrderItem::where('pos_order_id', $oId)->pluck('product_name', 'product_id')->toArray();
            $keys = array_keys($pIds);
            sort($keys);

            for ($i = 0; $i < count($keys); $i++) {
                for ($j = $i + 1; $j < count($keys); $j++) {
                    $pairKey = $keys[$i] . '||' . $keys[$j];
                    $pairsCount[$pairKey] = ($pairsCount[$pairKey] ?? 0) + 1;
                    $productNames[$keys[$i]] = $pIds[$keys[$i]];
                    $productNames[$keys[$j]] = $pIds[$keys[$j]];
                }
            }
        }

        arsort($pairsCount);
        $topPairs = array_slice($pairsCount, 0, 4, true);
        $bundles = [];

        foreach ($topPairs as $pairKey => $frequency) {
            [$idA, $idB] = explode('||', $pairKey);
            $nameA = $productNames[$idA] ?? 'Produk A';
            $nameB = $productNames[$idB] ?? 'Produk B';

            $prodA = Product::find($idA);
            $prodB = Product::find($idB);

            $normalTotal = ($prodA?->selling_price ?? 0) + ($prodB?->selling_price ?? 0);
            $bundlePrice = round(($normalTotal * 0.9) / 500) * 500; // 10% bundle discount

            $bundles[] = [
                'title' => "Paket Duet Hemat: {$nameA} + {$nameB}",
                'item_a' => $nameA,
                'item_b' => $nameB,
                'frequency_co_purchased' => $frequency,
                'normal_price' => $normalTotal,
                'recommended_bundle_price' => $bundlePrice,
                'discount_percent' => 10,
                'insight' => "Kedua item ini sering dibeli bersamaan sebanyak {$frequency} kali. Membuat paket bundling dapat meningkatkan Average Order Value (AOV).",
            ];
        }

        return $bundles;
    }

    /**
     * 7. Customer Behavior Analysis (RFM Segmentation).
     *
     * @return array<string, mixed>
     */
    public function getCustomerBehaviorRfm(Business $business): array
    {
        $today = Carbon::today();
        $customers = Customer::where('business_id', $business->id)->with('posOrders')->get();

        $segments = [
            'champions' => [],
            'loyal' => [],
            'potential' => [],
            'at_risk' => [],
            'hibernating' => [],
        ];

        foreach ($customers as $c) {
            $completedOrders = $c->posOrders->where('status', PosOrder::STATUS_COMPLETED);
            $orderCount = $completedOrders->count();

            if ($orderCount === 0) {
                continue;
            }

            $lastOrderDate = $completedOrders->max('order_date');
            $recencyDays = $lastOrderDate ? $today->diffInDays(Carbon::parse($lastOrderDate)) : 999;
            $monetary = (float) $completedOrders->sum('total_amount');

            $custData = [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone ?? '-',
                'tier' => $c->membership_tier ?? 'bronze',
                'points' => $c->points_balance,
                'recency_days' => $recencyDays,
                'order_count' => $orderCount,
                'total_spent' => $monetary,
            ];

            // RFM Matrix Rules
            if ($recencyDays <= 14 && $orderCount >= 5) {
                $segments['champions'][] = $custData;
            } elseif ($recencyDays <= 30 && $orderCount >= 3) {
                $segments['loyal'][] = $custData;
            } elseif ($recencyDays <= 30 && $orderCount < 3) {
                $segments['potential'][] = $custData;
            } elseif ($recencyDays > 30 && $recencyDays <= 75 && $orderCount >= 2) {
                $segments['at_risk'][] = $custData;
            } else {
                $segments['hibernating'][] = $custData;
            }
        }

        return [
            'segments' => $segments,
            'summary' => [
                'champions_count' => count($segments['champions']),
                'loyal_count' => count($segments['loyal']),
                'potential_count' => count($segments['potential']),
                'at_risk_count' => count($segments['at_risk']),
                'hibernating_count' => count($segments['hibernating']),
            ],
        ];
    }

    /**
     * 8. Cashier Scorecard & Performance Analytics.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCashierPerformance(Business $business): array
    {
        $thirtyDaysAgo = Carbon::today()->subDays(30);
        $users = User::whereHas('businesses', fn($q) => $q->where('businesses.id', $business->id))->get();
        $scorecards = [];

        foreach ($users as $u) {
            $orders = PosOrder::where('business_id', $business->id)
                ->where('user_id', $u->id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->get();

            if ($orders->isEmpty()) {
                continue;
            }

            $completed = $orders->where('status', PosOrder::STATUS_COMPLETED);
            $voids = $orders->where('status', PosOrder::STATUS_VOIDED);

            $revenue = (float) $completed->sum('total_amount');
            $profit = (float) $completed->sum('total_gross_profit');
            $txCount = $completed->count();
            $aov = $txCount > 0 ? ($revenue / $txCount) : 0.0;
            $voidCount = $voids->count();
            $voidRate = ($orders->count() > 0) ? round(($voidCount / $orders->count()) * 100, 1) : 0.0;

            // Cash drawer accuracy
            $shifts = PosShift::where('business_id', $business->id)
                ->where('user_id', $u->id)
                ->where('status', PosShift::STATUS_CLOSED)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->get();

            $totalVariance = (float) $shifts->sum(fn($s) => abs((float) $s->cash_difference));
            $perfectShifts = $shifts->where('cash_difference', 0)->count();
            $accuracyRate = $shifts->count() > 0 ? round(($perfectShifts / $shifts->count()) * 100) : 100;

            // Composite Performance Score (0 - 100)
            $score = 80;
            if ($voidRate > 10) $score -= 20;
            elseif ($voidRate > 5) $score -= 10;
            else $score += 10;

            if ($accuracyRate < 70) $score -= 15;
            elseif ($accuracyRate >= 90) $score += 10;

            $score = min(100, max(40, $score));

            $scorecards[] = [
                'user_id' => $u->id,
                'name' => $u->name,
                'total_revenue' => $revenue,
                'total_profit' => $profit,
                'transactions_count' => $txCount,
                'aov' => round($aov),
                'void_count' => $voidCount,
                'void_rate' => $voidRate,
                'closed_shifts_count' => $shifts->count(),
                'drawer_accuracy_percent' => $accuracyRate,
                'total_cash_variance' => $totalVariance,
                'score' => $score,
            ];
        }

        usort($scorecards, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);
        return $scorecards;
    }

    /**
     * 9. Natural Language Reporting (Interactive Indonesian AI Assistant).
     *
     * @return array<string, mixed>
     */
    public function askNaturalLanguage(Business $business, string $query): array
    {
        $q = strtolower(trim($query));
        $today = Carbon::today();

        // 1. Check if Gemini API key exists for generative expansion
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        // 0. AI Action Intent Recognition (Human-in-the-Loop DRAFT Preparation)
        if (str_contains($q, 'draf invoice') || str_contains($q, 'buat invoice') || str_contains($q, 'buatkan invoice')) {
            $customer = Customer::where('business_id', $business->id)->first();
            $product = Product::where('business_id', $business->id)->where('is_active', true)->first();

            $custName = $customer ? $customer->name : 'Pelanggan Walk-In';
            $prodName = $product ? $product->name : 'Produk Unggulan';
            $price = $product ? (float) $product->selling_price : 50000;
            $qty = 5;
            $total = $price * $qty;

            return [
                'type' => 'action_proposal',
                'action_id' => Str::uuid()->toString(),
                'action_type' => 'create_invoice',
                'headline' => 'Draf Faktur Penjualan Siap Diterbitkan',
                'details' => "AI telah menyiapkan draf faktur untuk {$custName}: {$qty}x {$prodName} senilai Rp " . number_format($total, 0, ',', '.') . ". Konfirmasi persetujuan Anda untuk menerbitkan transaksi ini.",
                'data' => [
                    ['Pelanggan', $custName],
                    ['Item', "{$qty}x {$prodName}"],
                    ['Harga Satuan', 'Rp ' . number_format($price, 0, ',', '.')],
                    ['Total Tagihan', 'Rp ' . number_format($total, 0, ',', '.')],
                ],
                'action_suggestion' => 'Klik tombol "Setujui & Terbitkan" di bawah untuk memproses transaksi ke database.',
                'payload' => [
                    'customer_id' => $customer?->id,
                    'customer_name' => $custName,
                    'product_id' => $product?->id,
                    'product_name' => $prodName,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => $total,
                ],
                'requires_confirmation' => true,
            ];
        }

        if (str_contains($q, 'draf penawaran') || str_contains($q, 'buat penawaran') || str_contains($q, 'buatkan penawaran') || str_contains($q, 'draf quotation')) {
            $customer = Customer::where('business_id', $business->id)->first();
            $product = Product::where('business_id', $business->id)->where('is_active', true)->first();

            $custName = $customer ? $customer->name : 'Calon Klien';
            $prodName = $product ? $product->name : 'Paket Penawaran';
            $price = $product ? (float) $product->selling_price : 100000;
            $qty = 10;
            $total = $price * $qty;

            return [
                'type' => 'action_proposal',
                'action_id' => Str::uuid()->toString(),
                'action_type' => 'create_quotation',
                'headline' => 'Draf Surat Penawaran Harga Siap Diterbitkan',
                'details' => "AI telah menyiapkan draf surat penawaran resmi untuk {$custName}: {$qty}x {$prodName} senilai Rp " . number_format($total, 0, ',', '.') . ". Konfirmasi persetujuan Anda untuk menerbitkan penawaran ini.",
                'data' => [
                    ['Calon Klien', $custName],
                    ['Paket Item', "{$qty}x {$prodName}"],
                    ['Harga Satuan', 'Rp ' . number_format($price, 0, ',', '.')],
                    ['Total Penawaran', 'Rp ' . number_format($total, 0, ',', '.')],
                ],
                'action_suggestion' => 'Klik tombol "Setujui & Terbitkan" di bawah untuk memproses surat penawaran.',
                'payload' => [
                    'customer_id' => $customer?->id,
                    'customer_name' => $custName,
                    'product_id' => $product?->id,
                    'product_name' => $prodName,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => $total,
                ],
                'requires_confirmation' => true,
            ];
        }

        // Rule-based semantic parser (100% offline & instantaneous)
        if (str_contains($q, 'hari ini') || str_contains($q, 'omset hari') || str_contains($q, 'penjualan hari')) {
            $todayOrders = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->where('order_date', $today->toDateString())
                ->get();

            $rev = (float) $todayOrders->sum('total_amount');
            $profit = (float) $todayOrders->sum('total_gross_profit');
            $count = $todayOrders->count();

            return [
                'headline' => "Penjualan Hari Ini: Rp " . number_format($rev, 0, ',', '.') . " (" . $count . " Transaksi)",
                'details' => "Hari ini tercatat {$count} transaksi dengan laba kotor sebesar Rp " . number_format($profit, 0, ',', '.') . " (Margin " . ($rev > 0 ? round(($profit / $rev) * 100, 1) : 0) . "%). Rata-rata nilai struk (AOV) adalah Rp " . ($count > 0 ? number_format(round($rev / $count), 0, ',', '.') : 0) . ".",
                'data' => [
                    ['Metrik', 'Nilai'],
                    ['Omset Kotor', 'Rp ' . number_format($rev, 0, ',', '.')],
                    ['Laba Kotor', 'Rp ' . number_format($profit, 0, ',', '.')],
                    ['Jumlah Order', $count . ' Transaksi'],
                ],
                'action_suggestion' => "Periksa grafik tren per jam di dashboard analitik untuk melihat jam paling ramai hari ini.",
            ];
        }

        if (str_contains($q, 'stok') || str_contains($q, 'habis') || str_contains($q, 'reorder') || str_contains($q, 'menipis')) {
            $stockData = $this->getStockPredictionAndReorder($business);
            $critical = array_filter($stockData['products'], fn($p) => $p['urgency'] === 'critical');
            $warning = array_filter($stockData['products'], fn($p) => $p['urgency'] === 'warning');

            $topCritical = array_slice($critical, 0, 3);
            $names = implode(', ', array_column($topCritical, 'product_name'));

            return [
                'headline' => count($critical) > 0 
                    ? "Peringatan: " . count($critical) . " Produk Kritis Perlu Reorder Segera!" 
                    : "Status Stok Inventori Aman.",
                'details' => count($critical) > 0 
                    ? "Produk yang paling mendesak: {$names}. Stok diprediksi habis dalam kurang dari 48 jam ke depan berdasarkan rata-rata laju penjualan."
                    : "Seluruh produk memiliki persediaan di atas batas safety stock minimum untuk 7 hari ke depan.",
                'data' => array_map(fn($p) => [$p['product_name'], 'Sisa: ' . $p['current_stock'] . ' ' . $p['unit'], 'Habis: ' . $p['runout_days'] . ' hari', 'Saran Order: ' . $p['suggested_reorder_qty']], array_slice($critical, 0, 5)),
                'action_suggestion' => "Segera buat Purchase Order (PO) atau transfer stok antar gudang sebelum kehabisan stok.",
            ];
        }

        if (str_contains($q, 'laris') || str_contains($q, 'favorit') || str_contains($q, 'terbanyak') || str_contains($q, 'top produk')) {
            $items = PosOrderItem::whereHas('order', fn($q) => $q->where('business_id', $business->id)->where('status', PosOrder::STATUS_COMPLETED))
                ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
                ->groupBy('product_name')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get();

            $topName = $items->first()?->product_name ?? 'Belum ada produk';
            $topQty = $items->first()?->total_qty ?? 0;

            return [
                'headline' => "Produk Paling Laris: {$topName} ({$topQty} Terjual)",
                'details' => "Top 5 produk memberikan kontribusi signifikan terhadap perputaran kas kasir toko.",
                'data' => $items->map(fn($it) => [$it->product_name, (int)$it->total_qty . ' Terjual', 'Rp ' . number_format($it->total_revenue, 0, ',', '.')])->toArray(),
                'action_suggestion' => "Pastikan ketersediaan bahan baku resep BOM untuk produk bintang ini selalu tersedia prima.",
            ];
        }

        if (str_contains($q, 'curiga') || str_contains($q, 'fraud') || str_contains($q, 'anomali') || str_contains($q, 'void') || str_contains($q, 'selisih')) {
            $anomalies = $this->detectAnomaliesAndFraud($business);
            $count = $anomalies['total_alerts'];

            return [
                'headline' => $count > 0 
                    ? "Terdeteksi {$count} Peringatan Anomali & Potensi Kecurangan" 
                    : "Sistem Kasir Bersih: Tidak Ditemukan Anomali Signifikan",
                'details' => $count > 0 
                    ? "Ditemukan {$anomalies['danger_count']} peringatan berisiko tinggi dan {$anomalies['warning_count']} indikasi tidak wajar (pembatalan void berlebih atau selisih fisik laci uang)."
                    : "Pola transaksi, rasio void kasir, dan rekonsiliasi kas berada dalam batas toleransi normal.",
                'data' => array_map(fn($a) => [$a['title'], $a['cashier'], $a['recommendation']], array_slice($anomalies['alerts'], 0, 4)),
                'action_suggestion' => "Aktifkan Supervisor PIN untuk pembatalan transaksi di atas batas nominal kasir.",
            ];
        }

        if (str_contains($q, 'kasir') || str_contains($q, 'performa') || str_contains($q, 'karyawan')) {
            $cashiers = $this->getCashierPerformance($business);
            $top = $cashiers[0] ?? null;

            return [
                'headline' => $top ? "Kasir Terbaik: {$top['name']} (Skor AI: {$top['score']}/100)" : "Belum ada riwayat kasir.",
                'details' => $top ? "Kasir {$top['name']} membukukan penjualan tertinggi sebesar Rp " . number_format($top['total_revenue'], 0, ',', '.') . " dengan tingkat akurasi laci kas {$top['drawer_accuracy_percent']}%." : "-",
                'data' => array_map(fn($c) => [$c['name'], 'Omset: Rp ' . number_format($c['total_revenue'], 0, ',', '.'), 'Order: ' . $c['transactions_count'], 'Skor: ' . $c['score']], $cashiers),
                'action_suggestion' => "Pertimbangkan pemberian insentif performa bagi kasir dengan akurasi laci 100%.",
            ];
        }

        if (str_contains($q, 'prediksi') || str_contains($q, 'forecast') || str_contains($q, 'minggu depan') || str_contains($q, 'proyeksi')) {
            $fc = $this->getSalesForecasting($business, 7);

            return [
                'headline' => "Proyeksi Penjualan 7 Hari Ke Depan: Rp " . number_format($fc['total_forecast_revenue'], 0, ',', '.'),
                'details' => "Tren penjualan diperkirakan cenderung {$fc['trend_direction']} dengan estimasi pertumbuhan {$fc['growth_percentage']}%. Hari penjualan tertinggi diproyeksikan pada {$fc['peak_projected_day']}.",
                'data' => array_map(fn($f) => [$f['day_name'] . ' (' . $f['formatted_date'] . ')', 'Rp ' . number_format($f['predicted_revenue'], 0, ',', '.'), $f['predicted_orders'] . ' Order'], $fc['forecast']),
                'action_suggestion' => "Siapkan staf tambahan dan stok bahan baku ekstra menjelang hari puncak penjualan.",
            ];
        }

        // Default General Intelligence Overview
        $todayRev = PosOrder::where('business_id', $business->id)->where('status', PosOrder::STATUS_COMPLETED)->where('order_date', $today->toDateString())->sum('total_amount');
        return [
            'headline' => "Ringkasan Eksekutif POS & Rekomendasi Pintar",
            'details' => "Toko aktif dengan penjualan hari ini sebesar Rp " . number_format((float)$todayRev, 0, ',', '.') . ". Anda dapat menanyakan tentang prediksi penjualan, stok kritis yang perlu reorder, produk terlaris, deteksi transaksi abnormal, atau performa kasir.",
            'data' => [
                ['Contoh Pertanyaan', 'Fungsi'],
                ['"Berapa penjualan hari ini?"', 'Cek omzet & laba kotor hari berjalan'],
                ['"Produk apa yang stoknya mau habis?"', 'Prediksi kehabisan stok & saran reorder'],
                ['"Apakah ada transaksi mencurigakan?"', 'Deteksi fraud, void abnormal & selisih laci'],
                ['"Prediksi penjualan minggu depan"', 'Proyeksi deret waktu omzet 7-14 hari'],
                ['"Siapa kasir dengan performa terbaik?"', 'Scorecard efisiensi kasir'],
            ],
            'action_suggestion' => "Ketik atau klik salah satu pertanyaan cepat di atas untuk menganalisis data secara instan.",
        ];
    }

    /**
     * Execute an AI draft proposal after explicit user confirmation (Human-in-the-Loop).
     *
     * @return array<string, mixed>
     */
    public function executeActionDraft(Business $business, User $user, string $actionType, array $payload): array
    {
        $entitlement = app(EntitlementService::class);
        $entitlement->deductAiTokens($business, 1500, 'execute_action', $user);

        if ($actionType === 'create_invoice') {
            $customer = !empty($payload['customer_id']) 
                ? Customer::find($payload['customer_id'])
                : Customer::where('business_id', $business->id)->first();

            if (!$customer) {
                $customer = Customer::create([
                    'business_id' => $business->id,
                    'name' => $payload['customer_name'] ?? 'Pelanggan Umum',
                    'phone' => '08123456789',
                ]);
            }

            $invPrefix = 'INV-' . date('Ym') . '-';
            $latest = Invoice::where('business_id', $business->id)
                ->where('invoice_number', 'LIKE', $invPrefix . '%')
                ->orderByDesc('invoice_number')
                ->value('invoice_number');
            $seq = 1;
            if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                $seq = ((int) $m[1]) + 1;
            }
            $invNumber = $invPrefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $qty = (float) ($payload['quantity'] ?? 1);
            $price = (float) ($payload['unit_price'] ?? 10000);
            $subtotal = $qty * $price;

            $product = !empty($payload['product_id']) ? Product::find($payload['product_id']) : null;
            $hpp = $product ? (float) $product->base_cost * $qty : 0.0;

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'invoice_number' => $invNumber,
                'invoice_date' => Carbon::today()->toDateString(),
                'due_date' => Carbon::today()->addDays(14)->toDateString(),
                'status' => Invoice::STATUS_UNPAID,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'balance_due' => $subtotal,
                'total_hpp_cost' => $hpp,
                'total_gross_profit' => $subtotal - $hpp,
                'payment_terms' => 'Net 14',
                'notes' => 'Diterbitkan otomatis via AI Assistant (Dikonfirmasi oleh Pengguna)',
            ]);

            $defaultUnitId = $product?->output_unit_id ?? Unit::where('business_id', $business->id)->first()?->id;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product?->id,
                'item_name' => $payload['product_name'] ?? 'Item Transaksi',
                'quantity' => $qty,
                'unit_id' => $defaultUnitId,
                'unit_price' => $price,
                'unit_hpp' => $product ? (float) $product->base_cost : 0.0,
                'subtotal' => $subtotal,
                'total_hpp' => $hpp,
            ]);

            return [
                'success' => true,
                'message' => "Faktur {$invoice->invoice_number} berhasil diterbitkan!",
                'redirect_url' => route('invoices.show', $invoice),
            ];
        }

        if ($actionType === 'create_quotation') {
            $customer = !empty($payload['customer_id']) 
                ? Customer::find($payload['customer_id'])
                : Customer::where('business_id', $business->id)->first();

            if (!$customer) {
                $customer = Customer::create([
                    'business_id' => $business->id,
                    'name' => $payload['customer_name'] ?? 'Calon Klien',
                    'phone' => '08123456789',
                ]);
            }

            $pipeline = app(SalesPipelineService::class);
            $quotation = $pipeline->createQuotation($business, [
                'customer_id' => $customer->id,
                'date' => Carbon::today()->toDateString(),
                'notes' => 'Diterbitkan otomatis via AI Assistant (Dikonfirmasi oleh Pengguna)',
                'items' => [
                    [
                        'product_id' => $payload['product_id'] ?? null,
                        'product_name' => $payload['product_name'] ?? 'Item Penawaran',
                        'unit_price' => $payload['unit_price'] ?? 10000,
                        'quantity' => $payload['quantity'] ?? 1,
                    ],
                ],
            ]);

            return [
                'success' => true,
                'message' => "Surat Penawaran {$quotation->quotation_number} berhasil diterbitkan!",
                'redirect_url' => route('sales.quotations.show', $quotation),
            ];
        }

        return ['success' => false, 'message' => 'Tipe aksi tidak dikenali.'];
    }
}
