<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Report\DashboardAnalyticsService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CommerceOrder;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class DashboardWebController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:dashboard.view', only: ['index', 'quickStats']),
            new Middleware('require.permission:inventory.manage', only: ['quickMaterialsList', 'quickStockIn']),
            new Middleware('require.permission:expenses.manage', only: ['quickExpense', 'quickInflow']),
            new Middleware('require.permission:materials.create', only: ['quickMaterial']),
            new Middleware('throttle:30,1', only: ['quickExpense', 'quickInflow', 'quickStockIn', 'quickMaterial']),
        ];
    }

    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();

        // Smart redirect: non-dashboard users land directly on their personal staff portal
        if (! Context::isOwner() && ! Context::hasPermission('dashboard.view')) {
            return redirect()->route('portal');
        }

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
     * Get list of materials for Quick Stock-In modal via AJAX (Lazy-loaded, tenant cached).
     */
    public function quickMaterialsList(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $materials = Cache::remember("quick_materials_list_{$business->id}", 60, function () use ($business) {
            return Material::where('business_id', $business->id)
                ->whereNull('discontinued_at')
                ->with(['unit:id,name,code', 'latestPrice:id,material_id,purchase_price'])
                ->orderBy('name')
                ->get(['id', 'business_id', 'name', 'code', 'unit_id'])
                ->map(fn($m) => [
                    'id' => (string) $m->id,
                    'name' => (string) $m->name,
                    'code' => (string) ($m->code ?? ''),
                    'unit' => (string) ($m->unit?->code ?? 'satuan'),
                    'price' => (float) ($m->latestPrice?->purchase_price ?? 0),
                ])
                ->all();
        });

        return response()->json([
            'success' => true,
            'materials' => $materials,
        ]);
    }

    /**
     * Quick Record Expense (Beban Operasional Cepat) via AJAX.
     */
    public function quickExpense(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // Idempotency Key check to prevent double-submit
        $idempotencyKey = $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $cacheKey = "idemp_exp_{$business->id}_{$idempotencyKey}";
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'other_description' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'in:cash,bank,qris,petty_cash'],
            'notes' => ['nullable', 'string', 'max:500'],
            'supervisor_pin' => ['nullable', 'string', 'max:10'],
        ]);

        $cat = $validated['category'] ?? 'Operasional Toko';
        if (in_array(strtolower($cat), ['lainnya', 'other']) && empty(trim($validated['other_description'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => __('finance.category_other_required', [], null) ?: 'Keterangan rincian wajib diisi ketika kategori Lainnya dipilih.',
                'errors' => ['other_description' => [__('finance.category_other_required', [], null) ?: 'Keterangan rincian wajib diisi ketika kategori Lainnya dipilih.']],
            ], 422);
        }

        $amount = (float) $validated['amount'];

        // Maker-Checker Threshold: If amount >= 500,000 and business has supervisor PIN configured
        if ($amount >= 500000 && ! empty($business->pos_supervisor_pin)) {
            $pin = $request->input('supervisor_pin');
            if (empty($pin)) {
                return response()->json([
                    'success' => false,
                    'requires_pin' => true,
                    'message' => __('quick_actions.expense.pin_required_msg'),
                ], 422);
            }
            if (! Hash::check((string) $pin, $business->pos_supervisor_pin)) {
                return response()->json([
                    'success' => false,
                    'requires_pin' => true,
                    'message' => __('quick_actions.expense.pin_invalid_msg'),
                ], 422);
            }
        }

        $location = \App\Models\Location::where('business_id', $business->id)->first();
        $locationId = $location?->id;
        $expenseNumber = 'EXP-' . date('Ymd') . '-' . rand(1000, 9999);

        // Format description
        $rawName = trim($validated['name']);
        if (in_array(strtolower($cat), ['lainnya', 'other'])) {
            $otherDesc = trim($validated['other_description'] ?? '') ?: $rawName;
            $finalDescription = "[Lainnya] {$otherDesc}";
        } else {
            $finalDescription = $rawName;
        }

        $expense = DB::transaction(function () use ($business, $locationId, $expenseNumber, $validated, $cat, $finalDescription, $amount, $user) {
            $createdExpense = Expense::create([
                'business_id' => $business->id,
                'location_id' => $locationId,
                'expense_number' => $expenseNumber,
                'expense_date' => Carbon::today()->toDateString(),
                'category' => $cat,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'description' => $finalDescription,
                'recorded_by' => $user?->id,
            ]);

            if ($user) {
                try {
                    app(AutoJournalService::class)->recordExpenseJournal($createdExpense, $user);
                } catch (\Throwable) {
                    // Resilient fallback
                }
            }

            return $createdExpense;
        });

        $overview = $this->getOverviewData($business);

        $responseData = [
            'success' => true,
            'message' => __('quick_actions.expense.recorded_success_with_amount', [
                'name' => $expense->description,
                'amount' => number_format($amount, 0, ',', '.'),
            ]),
            'expense' => [
                'id' => $expense->id,
                'name' => $expense->description,
                'amount' => $amount,
                'category' => $expense->category,
                'date' => $expense->expense_date,
            ],
            'stats' => $overview['stats'],
        ];

        if ($idempotencyKey) {
            Cache::put("idemp_exp_{$business->id}_{$idempotencyKey}", $responseData, 60);
        }

        return response()->json($responseData);
    }

    /**
     * Quick Instant Income (Catat Pemasukan Kas Cepat) via AJAX.
     */
    public function quickInflow(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // Idempotency Key check to prevent double-submit
        $idempotencyKey = $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $cacheKey = "idemp_inflow_{$business->id}_{$idempotencyKey}";
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'other_description' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'in:cash,bank,qris'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $cat = $validated['category'] ?? 'Penjualan / Pendapatan Usaha';
        if (in_array(strtolower($cat), ['lainnya', 'other']) && empty(trim($validated['other_description'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => __('finance.category_other_required', [], null) ?: 'Keterangan rincian wajib diisi ketika kategori Lainnya dipilih.',
                'errors' => ['other_description' => [__('finance.category_other_required', [], null) ?: 'Keterangan rincian wajib diisi ketika kategori Lainnya dipilih.']],
            ], 422);
        }

        $amount = (float) $validated['amount'];
        $method = match ($validated['payment_method'] ?? 'cash') {
            'bank' => 'bank_transfer',
            'qris' => 'qris',
            default => 'cash',
        };

        $rawName = trim($validated['name']);
        if (in_array(strtolower($cat), ['lainnya', 'other'])) {
            $otherDesc = trim($validated['other_description'] ?? '') ?: $rawName;
            $finalDesc = "[Lainnya] {$otherDesc}";
        } else {
            $finalDesc = "[{$cat}] {$rawName}";
        }

        $ledger = app(\App\Domain\Finance\CashLedgerService::class);
        $account = $ledger->accountFor($business, $method);
        $referenceId = (string) Str::uuid();

        $trx = $ledger->recordInflow(
            $business,
            $amount,
            'quick_income',
            $referenceId,
            $finalDesc,
            $method,
            $user?->id,
            $account
        );

        $overview = $this->getOverviewData($business);

        $responseData = [
            'success' => true,
            'message' => __('quick_actions.income.recorded_success_with_amount', [
                'name' => $finalDesc,
                'amount' => number_format($amount, 0, ',', '.'),
            ]),
            'transaction' => [
                'id' => $trx->id,
                'name' => $finalDesc,
                'amount' => $amount,
                'category' => $cat,
                'date' => Carbon::today()->toDateString(),
            ],
            'stats' => $overview['stats'] ?? [],
        ];

        if ($idempotencyKey) {
            Cache::put("idemp_inflow_{$business->id}_{$idempotencyKey}", $responseData, 60);
        }

        return response()->json($responseData);
    }

    /**
     * Quick Instant Stock-In (Beli Langsung ke Stok) via AJAX.
     */
    public function quickStockIn(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // Idempotency Key check to prevent double-submit
        $idempotencyKey = $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $cacheKey = "idemp_stock_{$business->id}_{$idempotencyKey}";
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
        }

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

        $location = \App\Models\Location::where('business_id', $business->id)->first();
        $locationId = $location?->id;

        if (! empty($validated['material_id'])) {
            $material = Material::where('business_id', $business->id)->with('unit')->findOrFail($validated['material_id']);
            $targetName = $material->name;
            $unitCode = $material->unit?->code ?? 'satuan';

            $result = DB::transaction(function () use ($business, $material, $unitCost, $qty, $totalCost, $locationId, $validated, $targetName, $user) {
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

                return true;
            });
        } elseif (! empty($validated['product_id'])) {
            $product = Product::where('business_id', $business->id)->with('outputUnit')->findOrFail($validated['product_id']);
            $targetName = $product->name;
            $unitCode = $product->outputUnit?->code ?? 'pcs';

            $result = DB::transaction(function () use ($business, $product, $unitCost, $qty, $totalCost, $locationId, $validated, $targetName, $user) {
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

                return true;
            });
        } else {
            return response()->json(['success' => false, 'message' => __('quick_actions.stock_in.select_item_required')], 422);
        }

        // Invalidate cached material list
        Cache::forget("quick_materials_list_{$business->id}");
        Cache::forget("layout_modal_mat_{$business->id}");

        $overview = $this->getOverviewData($business);

        $responseData = [
            'success' => true,
            'message' => __('quick_actions.stock_in.stock_added_success', [
                'name' => $targetName,
                'qty' => $qty,
                'unit' => $unitCode,
                'total' => number_format($totalCost, 0, ',', '.'),
            ]),
            'stats' => $overview['stats'],
        ];

        if ($idempotencyKey) {
            Cache::put("idemp_stock_{$business->id}_{$idempotencyKey}", $responseData, 60);
        }

        return response()->json($responseData);
    }

    /**
     * Quick Create Material (Tambah Bahan Baku Cepat) via AJAX.
     */
    public function quickMaterial(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        // Idempotency Key check
        $idempotencyKey = $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $cacheKey = "idemp_mat_{$business->id}_{$idempotencyKey}";
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
        }

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

        $material = DB::transaction(function () use ($business, $validated, $code, $unitId) {
            $createdMaterial = Material::create([
                'business_id' => $business->id,
                'name' => $validated['name'],
                'code' => $code,
                'unit_id' => $unitId,
                'category_id' => $validated['category_id'] ?? null,
            ]);

            // Create initial MaterialPrice record
            \App\Models\MaterialPrice::create([
                'business_id' => $business->id,
                'material_id' => $createdMaterial->id,
                'purchase_price' => (float) $validated['cost_per_unit'],
                'purchase_unit_id' => $unitId,
                'effective_date' => Carbon::today(),
            ]);

            return $createdMaterial;
        });

        $material->load('unit', 'category');

        // Invalidate cached material list
        Cache::forget("quick_materials_list_{$business->id}");
        Cache::forget("layout_modal_mat_{$business->id}");
        Cache::forget("layout_modal_units_{$business->id}");

        $responseData = [
            'success' => true,
            'message' => __('quick_actions.material.material_added_success', [
                'name' => $material->name,
                'cost' => number_format((float) $validated['cost_per_unit'], 0, ',', '.'),
                'unit' => $material->unit?->code ?? 'satuan',
            ]),
            'material' => [
                'id' => $material->id,
                'name' => $material->name,
                'code' => $material->code,
                'cost_per_unit' => (float) $validated['cost_per_unit'],
                'unit_id' => $material->unit_id,
                'unit_code' => $material->unit?->code ?? 'satuan',
                'category_name' => $material->category?->name ?? 'Umum',
            ],
        ];

        if ($idempotencyKey) {
            Cache::put("idemp_mat_{$business->id}_{$idempotencyKey}", $responseData, 60);
        }

        return response()->json($responseData);
    }

    /**
     * Internal calculation logic for dashboard overview.
     */
    private function getOverviewData(Business $business): array
    {
        return app(DashboardAnalyticsService::class)->getOverviewData($business);
    }

    /**
     * Period-filtered analytics aggregation for the dashboard cockpit.
     */
    private function getAnalyticsData(Business $business, Request $request): array
    {
        $period = (string) $request->query('period', 'month');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');
        $locationId = (string) $request->query('location_id', '');

        return app(DashboardAnalyticsService::class)->getAnalyticsData(
            $business,
            $period,
            $from,
            $to,
            $locationId ?: null
        );
    }
}
