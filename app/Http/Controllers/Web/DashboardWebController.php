<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Accounting\AutoJournalService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CostingRun;
use App\Models\CostModel;
use App\Models\Expense;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Overhead;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCostVersion;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class DashboardWebController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();
        $data = $this->getOverviewData($business);
        $analytics = $this->getAnalyticsData($business, $request);

        if ($request->ajax() || $request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'analytics' => $analytics,
                'stats' => $data['stats'],
            ]);
        }

        $materials = Material::where('business_id', $business->id)->orderBy('name')->get();
        $units = Unit::where('business_id', $business->id)->orWhereNull('business_id')->orderBy('name')->get();
        $categories = MaterialCategory::where('business_id', $business->id)->orderBy('name')->get();

        return view(
            'app.dashboard',
            array_merge(array_merge($data, compact('materials', 'units', 'categories')), compact('analytics'))
        );
    }

    /**
     * Get real-time cockpit stats via AJAX without full page reload.
     */
    public function quickStats(): JsonResponse
    {
        $business = Context::requireBusiness();
        $data = $this->getOverviewData($business);

        return response()->json([
            'success' => true,
            'stats' => $data['stats'],
            'sevenDaysTrend' => $data['sevenDaysTrend'],
        ]);
    }

    /**
     * Quick Record Expense (Beban Operasional Cepat) via AJAX.
     */
    public function quickExpense(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'in:cash,bank,qris,petty_cash'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (float) $validated['amount'];
        $location = \App\Models\Location::where('business_id', $business->id)->first() ?? \App\Models\Location::first();
        $locationId = $location?->id;
        $expenseNumber = 'EXP-' . date('Ymd') . '-' . rand(1000, 9999);

        $expense = Expense::create([
            'business_id' => $business->id,
            'location_id' => $locationId,
            'expense_number' => $expenseNumber,
            'expense_date' => Carbon::today()->toDateString(),
            'category' => $validated['category'] ?? 'Operasional Toko',
            'amount' => $amount,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'description' => $validated['name'] ?? 'Biaya Operasional Toko',
            'recorded_by' => $user?->id,
        ]);

        if ($user) {
            try {
                app(AutoJournalService::class)->recordExpenseJournal($expense, $user);
            } catch (\Throwable) {
                // Keep resilient
            }
        }

        $overview = $this->getOverviewData($business);

        return response()->json([
            'success' => true,
            'message' => "Beban '{$expense->description}' sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat.",
            'expense' => [
                'id' => $expense->id,
                'name' => $expense->description,
                'amount' => $amount,
                'category' => $expense->category,
                'date' => $expense->expense_date,
            ],
            'stats' => $overview['stats'],
        ]);
    }

    /**
     * Quick Instant Stock-In (Beli Langsung ke Stok) via AJAX.
     */
    public function quickStockIn(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'material_id' => ['nullable', 'exists:materials,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $qty = (float) $validated['quantity'];
        $unitCost = (float) $validated['unit_cost'];
        $totalCost = $qty * $unitCost;

        $targetName = '';
        $unitCode = 'pcs';

        $location = \App\Models\Location::where('business_id', $business->id)->first() ?? \App\Models\Location::first();
        $locationId = $location?->id;

        if (! empty($validated['material_id'])) {
            $material = Material::with('unit')->findOrFail($validated['material_id']);
            $targetName = $material->name;
            $unitCode = $material->unit?->code ?? 'satuan';

            $product = Product::firstOrCreate(
                ['business_id' => $business->id, 'name' => $material->name],
                [
                    'code' => $material->code ?? ('MAT-' . strtoupper(Str::random(6))),
                    'output_unit_id' => $material->unit_id,
                    'base_cost' => $unitCost,
                    'selling_price' => $unitCost,
                    'is_active' => true,
                ]
            );

            // Record latest price
            \App\Models\MaterialPrice::create([
                'business_id' => $business->id,
                'material_id' => $material->id,
                'purchase_price' => $unitCost,
                'purchase_unit_id' => $material->unit_id,
                'effective_date' => Carbon::today(),
            ]);
        } elseif (! empty($validated['product_id'])) {
            $product = Product::with('outputUnit')->findOrFail($validated['product_id']);
            $targetName = $product->name;
            $unitCode = $product->outputUnit?->code ?? 'pcs';
        } else {
            return response()->json(['success' => false, 'message' => 'Pilih bahan baku atau produk yang akan ditambah stoknya.'], 422);
        }

        $stock = InventoryStock::firstOrCreate(
            ['business_id' => $business->id, 'location_id' => $locationId, 'product_id' => $product->id],
            ['quantity' => 0, 'last_cost' => $unitCost]
        );

        $before = (float) $stock->quantity;
        $after = $before + $qty;
        $stock->update(['quantity' => $after, 'last_cost' => $unitCost]);

        StockMovement::create([
            'business_id' => $business->id,
            'location_id' => $locationId,
            'product_id' => $product->id,
            'movement_type' => 'goods_receipt',
            'quantity_change' => $qty,
            'balance_after' => $after,
            'unit_cost' => $unitCost,
            'notes' => $validated['notes'] ?? ('Stok masuk instan: ' . ($validated['supplier_name'] ?? 'Pemasok')),
        ]);

        // Record cash expense for this purchase
        if ($totalCost > 0) {
            $stockExpNumber = 'EXP-' . date('Ymd') . '-' . rand(1000, 9999);
            $expense = Expense::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'expense_number' => $stockExpNumber,
                'expense_date' => Carbon::today()->toDateString(),
                'category' => 'Pembelian Bahan/Stok',
                'amount' => $totalCost,
                'payment_method' => 'cash',
                'description' => 'Pembelian Stok: ' . $targetName,
                'recorded_by' => $user?->id,
            ]);

            if ($user) {
                try {
                    app(AutoJournalService::class)->recordExpenseJournal($expense, $user);
                } catch (\Throwable) {
                }
            }
        }

        $overview = $this->getOverviewData($business);

        return response()->json([
            'success' => true,
            'message' => "Stok {$targetName} bertambah +{$qty} {$unitCode} (Total Rp " . number_format($totalCost, 0, ',', '.') . ").",
            'stats' => $overview['stats'],
        ]);
    }

    /**
     * Quick Create Material (Tambah Bahan Baku Cepat) via AJAX.
     */
    public function quickMaterial(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'category_id' => ['nullable', 'exists:material_categories,id'],
            'sku' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $unitId = $validated['unit_id'] ?? null;
        if (! $unitId) {
            $defaultUnit = Unit::where('business_id', $business->id)->first() ?? Unit::first();
            $unitId = $defaultUnit?->id;
        }

        $code = $validated['sku'] ?? $validated['code'] ?? ('MAT-' . strtoupper(Str::random(6)));

        $material = Material::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'code' => $code,
            'unit_id' => $unitId,
            'category_id' => $validated['category_id'] ?? null,
        ]);

        // Create initial MaterialPrice record
        \App\Models\MaterialPrice::create([
            'business_id' => $business->id,
            'material_id' => $material->id,
            'purchase_price' => (float) $validated['cost_per_unit'],
            'purchase_unit_id' => $unitId,
            'effective_date' => Carbon::today(),
        ]);

        $material->load('unit', 'category');

        return response()->json([
            'success' => true,
            'message' => "Bahan baku '{$material->name}' berhasil ditambahkan (Rp " . number_format((float) $validated['cost_per_unit'], 0, ',', '.') . " / {$material->unit?->code}).",
            'material' => [
                'id' => $material->id,
                'name' => $material->name,
                'code' => $material->code,
                'cost_per_unit' => (float) $validated['cost_per_unit'],
                'unit_id' => $material->unit_id,
                'unit_code' => $material->unit?->code ?? 'satuan',
                'category_name' => $material->category?->name ?? 'Umum',
            ],
        ]);
    }

    /**
     * Internal calculation logic for dashboard overview.
     */
    private function getOverviewData(Business $business): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Sales & Cash Flow (POS + Invoices)
        $posToday = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereDate('order_date', $today);
        $posTodaySales = (float) $posToday->sum('total_amount');
        $posTodayCount = $posToday->count();

        $invoiceTodaySales = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereDate('invoice_date', $today)
            ->sum('paid_amount');

        $todayTotalRevenue = $posTodaySales + $invoiceTodaySales;

        $posMonthSales = (float) PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereDate('order_date', '>=', $startOfMonth)
            ->sum('total_amount');

        $invoiceMonthSales = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereDate('invoice_date', '>=', $startOfMonth)
            ->sum('paid_amount');

        $monthTotalRevenue = $posMonthSales + $invoiceMonthSales;

        // 2. Expenses & Net Profit
        $monthExpenses = (float) Expense::where('business_id', $business->id)
            ->whereDate('expense_date', '>=', $startOfMonth)
            ->sum('amount');

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
        $activeShift = PosShift::where('business_id', $business->id)
            ->where('status', PosShift::STATUS_OPEN)
            ->with(['user'])
            ->latest('opened_at')
            ->first();

        // 5. Inventory & Stocks
        $stockItems = InventoryStock::where('business_id', $business->id)
            ->with(['product.outputUnit'])
            ->get();
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

        // 7. Last 7 Days Sales Trend (Single Aggregated Query)
        $sevenDaysTrend = [];
        $maxDaySales = 1000;
        $dayNamesIndo = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();

        $dailySales = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->where('order_date', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(order_date) as order_dt, SUM(total_amount) as total_sales')
            ->groupBy('order_dt')
            ->pluck('total_sales', 'order_dt')
            ->all();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateKey = $date->toDateString();
            $daySales = (float) ($dailySales[$dateKey] ?? 0);

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
        $recentPosOrders = PosOrder::where('business_id', $business->id)
            ->latest('created_at')
            ->take(5)
            ->get();

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
            'today_transactions_count' => $posTodayCount,
            'month_sales' => $monthTotalRevenue,
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
     * Map a dashboard period key into a concrete [from, to] date range.
     * Week starts on Monday (Indonesian business convention).
     */
    private function resolvePeriodRange(string $period, string $fromInput = '', string $toInput = ''): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                return [$today->startOfDay(), $now];
            case 'week': {
                $monday = $today->subDays($today->dayOfWeekIso - 1);
                return [$monday->startOfDay(), $now];
            }
            case 'year':
                return [$today->startOfYear(), $now];
            case 'custom': {
                try {
                    $to = $toInput !== '' ? Carbon::parse($toInput)->endOfDay() : $now;
                    $from = $fromInput !== '' ? Carbon::parse($fromInput)->startOfDay() : $to->subDays(6)->startOfDay();
                } catch (\Throwable) {
                    $to = $now;
                    $from = $now->subDays(6)->startOfDay();
                }
                if ($from->gt($to)) {
                    [$from, $to] = [$to, $from];
                }
                return [$from, $to];
            }
            default:
                return [$today->startOfMonth(), $now];
        }
    }
    /**
     * Period-filtered analytics aggregation for the dashboard cockpit.
     * Pure read-only aggregates built on existing tables.
     */
    private function getAnalyticsData(Business $business, Request $request): array
    {
        $period = (string) $request->query('period', 'month');
        if (! in_array($period, ['today', 'week', 'month', 'year', 'custom'], true)) {
            $period = 'month';
        }

        $fromInput = (string) $request->query('from', '');
        $toInput = (string) $request->query('to', '');

        [$from, $to] = $this->resolvePeriodRange($period, $fromInput, $toInput);
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        $periodLabels = [
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
            'custom' => 'Range Custom',
        ];

        // 1. Finansial aggregate: POS kasir + Faktur
        $posAgg = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$fromStr, $toStr])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue, COALESCE(SUM(total_hpp_cost), 0) as hpp, COALESCE(SUM(total_gross_profit), 0) as profit, COUNT(*) as cnt')
            ->first();

        $invAgg = Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_PARTIALLY_PAID])
            ->whereBetween('invoice_date', [$fromStr, $toStr])
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as revenue, COALESCE(SUM(total_hpp_cost), 0) as hpp, COALESCE(SUM(total_gross_profit), 0) as profit, COUNT(*) as cnt')
            ->first();

        $posRevenue = (float) ($posAgg?->revenue ?? 0);
        $invRevenue = (float) ($invAgg?->revenue ?? 0);
        $posHpp = (float) ($posAgg?->hpp ?? 0);
        $invHpp = (float) ($invAgg?->hpp ?? 0);
        $posProfit = (float) ($posAgg?->profit ?? 0);
        $invProfit = (float) ($invAgg?->profit ?? 0);
        $posCount = (int) ($posAgg?->cnt ?? 0);
        $invCount = (int) ($invAgg?->cnt ?? 0);

        $omzet = $posRevenue + $invRevenue;
        $hppTotal = $posHpp + $invHpp;
        $profit = $posProfit + $invProfit;
        $transactions = $posCount + $invCount;
        $avgTicket = $transactions > 0 ? round($omzet / $transactions, 0) : 0.0;
        $marginPct = $omzet > 0 ? round(($profit / $omzet) * 100, 1) : null;

        // 2. Beban operasional dalam periode
        $expenses = (float) Expense::where('business_id', $business->id)
            ->whereBetween('expense_date', [$fromStr, $toStr])
            ->sum('amount');

        // 3. Tren omzet & profit: bucket per jam untuk 'today', harian untuk <= 62 hari, bulanan untuk > 62 hari
        $trend = [];
        if ($period === 'today') {
            $granularity = 'hour';
            $posTrendRows = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$fromStr, $toStr])
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

            $posTrendRows = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->whereBetween('order_date', [$fromStr, $toStr])
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

        // 4. Distribusi omzet oleh kanal: Kasir vs Faktur
        $channelSplit = [
            ['label' => 'Kasir (POS)', 'value' => round($posRevenue, 0)],
            ['label' => 'Faktur', 'value' => round($invRevenue, 0)],
        ];

        // 5. Distribusi oleh jenis order kasir
        $orderTypeRows = PosOrder::where('business_id', $business->id)
            ->where('status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('order_date', [$fromStr, $toStr])
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
        $categoryRows = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr])
            ->whereNull('products.deleted_at')
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
        $paymentRows = DB::table('pos_order_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_payments.pos_order_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr])
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
        $topProductsRows = PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr])
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

        // 9. Top 15 material paling sering digunakan (Kombinasi StockMovement riil + Resep BOM penjualan POS)
        $movementRows = StockMovement::where('business_id', $business->id)
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
            ->whereDate('created_at', '<=', $toStr)
            ->selectRaw('material_id as mid, SUM(ABS(quantity_change)) as used, COUNT(*) as cnt')
            ->groupBy('material_id')
            ->get();

        $bomMaterialRows = DB::table('pos_order_items')
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
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr])
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

        // 10. Top 10 Stok hampir habis (diurutkan dari kuantitas terendah)
        $lowStockCount = (int) InventoryStock::where('business_id', $business->id)
            ->where(function ($q) {
                $q->where('quantity', '<=', 5)
                  ->orWhereRaw('quantity <= COALESCE((SELECT min_stock FROM products WHERE products.id = inventory_stocks.product_id), 5)');
            })
            ->count();

        $stockRows = InventoryStock::where('business_id', $business->id)
            ->with(['product.outputUnit', 'material.unit'])
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
        $itemsSold = (float) PosOrderItem::query()
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.business_id', $business->id)
            ->where('pos_orders.status', PosOrder::STATUS_COMPLETED)
            ->whereBetween('pos_orders.order_date', [$fromStr, $toStr])
            ->sum('pos_order_items.quantity');

        // 12. Insight Cepat Komunikatif
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
            'period_label' => $periodLabels[$period],
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
        ];
    }
}
