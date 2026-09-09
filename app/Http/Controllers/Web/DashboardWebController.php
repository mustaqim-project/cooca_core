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
use App\Models\PosShift;
use App\Models\Product;
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
    public function index(): View
    {
        $business = Context::requireBusiness();
        $data = $this->getOverviewData($business);

        $materials = Material::where('business_id', $business->id)->orderBy('name')->get();
        $units = Unit::where('business_id', $business->id)->orWhereNull('business_id')->orderBy('name')->get();
        $categories = MaterialCategory::where('business_id', $business->id)->orderBy('name')->get();

        return view('app.dashboard', array_merge($data, compact('materials', 'units', 'categories')));
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
}
