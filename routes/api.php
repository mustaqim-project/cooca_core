<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AbcActivityController;
use App\Http\Controllers\Api\V1\AiAssistantController;
use App\Http\Controllers\Api\V1\AllocationRuleController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BepController;
use App\Http\Controllers\Api\V1\BomController;
use App\Http\Controllers\Api\V1\Crm\CrmLoyaltyController;
use App\Http\Controllers\Api\V1\Crm\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Finance\FinanceController;
use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Http\Controllers\Api\V1\Inventory\StockOpnameController;
use App\Http\Controllers\Api\V1\Inventory\StockTransferController;
use App\Http\Controllers\Api\V1\Pos\PosOrderController;
use App\Http\Controllers\Api\V1\Pos\PosReportController;
use App\Http\Controllers\Api\V1\Pos\PosShiftController;
use App\Http\Controllers\Api\V1\Pos\PosTerminalController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Sales\InvoiceController;
use App\Http\Controllers\Api\V1\Sales\QuotationController;
use App\Http\Controllers\Api\V1\Sales\SalesOrderController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\BusinessTypeTemplateController;
use App\Http\Controllers\Api\V1\CalculationController;
use App\Http\Controllers\Api\V1\CostDriverController;
use App\Http\Controllers\Api\V1\CostingRunController;
use App\Http\Controllers\Api\V1\CostModelController;
use App\Http\Controllers\Api\V1\CostModelLaborController;
use App\Http\Controllers\Api\V1\CostModelMachineController;
use App\Http\Controllers\Api\V1\CostPoolController;
use App\Http\Controllers\Api\V1\CurrencyController;
use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Http\Controllers\Api\V1\FeeController;
use App\Http\Controllers\Api\V1\FormulaController;
use App\Http\Controllers\Api\V1\LaborRateController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\MachineController;
use App\Http\Controllers\Api\V1\MaterialCategoryController;
use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\OverheadController;
use App\Http\Controllers\Api\V1\PricingController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductCostVersionController;
use App\Http\Controllers\Api\V1\ProfitabilityController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SimulationController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UnitConversionController;
use App\Http\Controllers\Api\V1\VarianceController;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Health Check & API Docs
    Route::get('/health', function (): JsonResponse {
        return response()->json([
            'status' => 'ok',
            'service' => 'Universal HPP Calculator Engine',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    });

    Route::get('/docs', [ReportController::class, 'docs']);

    // Currencies (Global List)
    Route::get('/currencies', [CurrencyController::class, 'index']);

    // Units Public/Available Listing & Live Converter
    Route::get('/units', [UnitController::class, 'index']);
    Route::post('/units/convert', [UnitController::class, 'convert']);

    // Business Type Templates (Public list)
    Route::get('/business-type-templates', [BusinessTypeTemplateController::class, 'index']);
    Route::get('/business-type-templates/{businessTypeTemplate}', [BusinessTypeTemplateController::class, 'show']);

    // Formula Engine Utilities (Public preview / dry-run)
    Route::post('/formulas/evaluate', [FormulaController::class, 'evaluate']);
    Route::post('/formulas/validate', [FormulaController::class, 'validateFormula']);

    // Currency Conversion Live
    Route::post('/exchange-rates/convert', [ExchangeRateController::class, 'convert']);

    // Authentication (Public with 60 req/min rate limit)
    Route::middleware('throttle:60,1')->prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Authenticated User Routes (with 300 req/min rate limit)
    Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // User & Business Management
        Route::get('/me/businesses', [BusinessController::class, 'index']);
        Route::post('/me/active-business', [BusinessController::class, 'setActive']);
        Route::post('/businesses', [BusinessController::class, 'store']);
        Route::get('/businesses/{business:slug}', [BusinessController::class, 'show']);
        Route::post('/businesses/{business:slug}/apply-template', [BusinessTypeTemplateController::class, 'apply']);

        // Business Members Management
        Route::get('/businesses/{business:slug}/members', [MemberController::class, 'index']);
        Route::post('/businesses/{business:slug}/members', [MemberController::class, 'store'])
            ->middleware('business.active', 'require.role:owner,admin');
        Route::patch('/businesses/{business:slug}/members/{member}', [MemberController::class, 'update'])
            ->middleware('business.active', 'require.role:owner,admin');
        Route::delete('/businesses/{business:slug}/members/{member}', [MemberController::class, 'destroy'])
            ->middleware('business.active', 'require.role:owner,admin');

        // Tenant-Aware Routes (Requires Active Business Context)
        Route::middleware('business.active')->group(function (): void {
            Route::get('/context/current', function (): JsonResponse {
                $business = Context::requireBusiness();

                return response()->json([
                    'business' => [
                        'id' => $business->id,
                        'name' => $business->name,
                        'slug' => $business->slug,
                    ],
                    'role' => Context::role(),
                    'is_owner' => Context::isOwner(),
                    'permissions' => Context::permissions(),
                    'can_view_margin' => Context::hasPermission('costing.view_margin'),
                ]);
            });

            // Convenience Members endpoints for active business
            Route::get('/members', [MemberController::class, 'index']);
            Route::post('/members', [MemberController::class, 'store'])
                ->middleware('require.role:owner,admin');
            Route::patch('/members/{member}', [MemberController::class, 'update'])
                ->middleware('require.role:owner,admin');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])
                ->middleware('require.role:owner,admin');

            // Tenant Custom Units & Conversions
            Route::post('/units', [UnitController::class, 'store']);
            Route::get('/units/{unit}', [UnitController::class, 'show']);
            Route::put('/units/{unit}', [UnitController::class, 'update']);
            Route::delete('/units/{unit}', [UnitController::class, 'destroy']);

            Route::get('/unit-conversions', [UnitConversionController::class, 'index']);
            Route::post('/unit-conversions', [UnitConversionController::class, 'store']);
            Route::delete('/unit-conversions/{unitConversion}', [UnitConversionController::class, 'destroy']);

            // Multi-Location & Multi-Currency per Business
            Route::get('/locations', [LocationController::class, 'index']);
            Route::post('/locations', [LocationController::class, 'store']);
            Route::get('/locations/{location:slug}', [LocationController::class, 'show']);
            Route::put('/locations/{location:slug}', [LocationController::class, 'update']);
            Route::delete('/locations/{location:slug}', [LocationController::class, 'destroy']);

            Route::get('/exchange-rates', [ExchangeRateController::class, 'index']);
            Route::post('/exchange-rates', [ExchangeRateController::class, 'store']);

            // Suppliers CRUD
            Route::get('/suppliers', [SupplierController::class, 'index']);
            Route::post('/suppliers', [SupplierController::class, 'store']);
            Route::get('/suppliers/{supplier:slug}', [SupplierController::class, 'show']);
            Route::put('/suppliers/{supplier:slug}', [SupplierController::class, 'update']);
            Route::delete('/suppliers/{supplier:slug}', [SupplierController::class, 'destroy']);

            // Material Categories CRUD
            Route::get('/material-categories', [MaterialCategoryController::class, 'index']);
            Route::post('/material-categories', [MaterialCategoryController::class, 'store']);
            Route::get('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'show']);
            Route::put('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'update']);
            Route::delete('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'destroy']);

            // Materials & Prices CRUD
            Route::get('/materials', [MaterialController::class, 'index']);
            Route::post('/materials', [MaterialController::class, 'store']);
            Route::get('/materials/{material:slug}', [MaterialController::class, 'show']);
            Route::put('/materials/{material:slug}', [MaterialController::class, 'update']);
            Route::delete('/materials/{material:slug}', [MaterialController::class, 'destroy']);
            Route::post('/materials/{material:slug}/prices', [MaterialController::class, 'storePrice']);
            Route::get('/materials/{material:slug}/effective-cost', [MaterialController::class, 'effectiveCost']);

            // Product Categories CRUD
            Route::get('/product-categories', [ProductCategoryController::class, 'index']);
            Route::post('/product-categories', [ProductCategoryController::class, 'store']);
            Route::get('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'show']);
            Route::put('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'update']);
            Route::delete('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'destroy']);

            // Products CRUD
            Route::get('/products', [ProductController::class, 'index']);
            Route::post('/products', [ProductController::class, 'store']);
            Route::get('/products/{product:slug}', [ProductController::class, 'show']);
            Route::put('/products/{product:slug}', [ProductController::class, 'update']);
            Route::delete('/products/{product:slug}', [ProductController::class, 'destroy']);

            // Cost Categories & Cost Models
            Route::get('/cost-categories', [CostModelController::class, 'categories']);
            Route::post('/products/{product:slug}/cost-models', [CostModelController::class, 'store']);
            Route::get('/cost-models/{costModel:slug}', [CostModelController::class, 'show']);
            Route::put('/cost-models/{costModel:slug}', [CostModelController::class, 'update']);
            Route::delete('/cost-models/{costModel:slug}', [CostModelController::class, 'destroy']);
            Route::put('/cost-models/{costModel:slug}/components', [CostModelController::class, 'syncComponents']);
            Route::get('/cost-models/{costModel:slug}/preview', [CostModelMachineController::class, 'preview']);

            // BOM / Recipe Engine
            Route::post('/cost-models/{costModel:slug}/bom', [BomController::class, 'createOrGetHeader']);
            Route::post('/bom-headers/{bomHeader}/items', [BomController::class, 'addItem']);
            Route::put('/bom-items/{bomItem}', [BomController::class, 'updateItem']);
            Route::delete('/bom-items/{bomItem}', [BomController::class, 'deleteItem']);
            Route::get('/bom-headers/{bomHeader}/explode', [BomController::class, 'explode']);

            // Labor Rates & Labor Assignments
            Route::get('/labor-rates', [LaborRateController::class, 'index']);
            Route::post('/labor-rates', [LaborRateController::class, 'store']);
            Route::get('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'show']);
            Route::put('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'update']);
            Route::delete('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'destroy']);
            Route::get('/labor-rates/{laborRate:slug}/hourly-equivalent', [LaborRateController::class, 'hourlyEquivalent']);
            Route::post('/cost-models/{costModel:slug}/labor', [CostModelLaborController::class, 'store']);
            Route::put('/cost-model-labors/{costModelLabor}', [CostModelLaborController::class, 'update']);
            Route::delete('/cost-model-labors/{costModelLabor}', [CostModelLaborController::class, 'destroy']);

            // Machines & Machine Assignments
            Route::get('/machines', [MachineController::class, 'index']);
            Route::post('/machines', [MachineController::class, 'store']);
            Route::get('/machines/{machine:slug}', [MachineController::class, 'show']);
            Route::put('/machines/{machine:slug}', [MachineController::class, 'update']);
            Route::delete('/machines/{machine:slug}', [MachineController::class, 'destroy']);
            Route::get('/machines/{machine:slug}/cost-per-hour', [MachineController::class, 'costPerHour']);
            Route::post('/cost-models/{costModel:slug}/machines', [CostModelMachineController::class, 'store']);
            Route::put('/cost-model-machines/{costModelMachine}', [CostModelMachineController::class, 'update']);
            Route::delete('/cost-model-machines/{costModelMachine}', [CostModelMachineController::class, 'destroy']);

            // Overheads & Cost Pools
            Route::get('/overheads', [OverheadController::class, 'index']);
            Route::post('/overheads', [OverheadController::class, 'store']);
            Route::get('/overheads/{overhead:slug}', [OverheadController::class, 'show']);
            Route::put('/overheads/{overhead:slug}', [OverheadController::class, 'update']);
            Route::delete('/overheads/{overhead:slug}', [OverheadController::class, 'destroy']);

            Route::get('/cost-pools', [CostPoolController::class, 'index']);
            Route::post('/cost-pools', [CostPoolController::class, 'store']);
            Route::get('/cost-pools/{costPool:slug}', [CostPoolController::class, 'show']);
            Route::put('/cost-pools/{costPool:slug}', [CostPoolController::class, 'update']);
            Route::delete('/cost-pools/{costPool:slug}', [CostPoolController::class, 'destroy']);

            // Cost Drivers & Allocation Rules
            Route::get('/cost-drivers', [CostDriverController::class, 'index']);
            Route::post('/cost-drivers', [CostDriverController::class, 'store']);
            Route::get('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'show']);
            Route::put('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'update']);
            Route::delete('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'destroy']);

            Route::get('/allocation-rules', [AllocationRuleController::class, 'index']);
            Route::post('/allocation-rules', [AllocationRuleController::class, 'store']);
            Route::get('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'show']);
            Route::put('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'update']);
            Route::delete('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'destroy']);
            Route::post('/allocation-rules/{allocationRule}/preview', [AllocationRuleController::class, 'preview']);

            // Activity-Based Costing (ABC)
            Route::get('/abc/activities', [AbcActivityController::class, 'index']);
            Route::post('/abc/activities', [AbcActivityController::class, 'store']);
            Route::get('/abc/activities/{activity:slug}', [AbcActivityController::class, 'show']);
            Route::put('/abc/activities/{activity:slug}', [AbcActivityController::class, 'update']);
            Route::delete('/abc/activities/{activity:slug}', [AbcActivityController::class, 'destroy']);
            Route::post('/cost-models/{costModel:slug}/abc-activities', [AbcActivityController::class, 'assignToCostModel']);
            Route::put('/cost-model-activities/{costModelActivity}', [AbcActivityController::class, 'updateCostModelActivity']);
            Route::delete('/cost-model-activities/{costModelActivity}', [AbcActivityController::class, 'removeCostModelActivity']);
            Route::get('/cost-models/{costModel:slug}/abc-cost', [AbcActivityController::class, 'calculateForCostModel']);

            // Calculation Engine Execution
            Route::post('/costing/calculate/{costModel:slug}', [CalculationController::class, 'calculate']);

            // Costing Runs & Official History
            Route::get('/cost-models/{costModel:slug}/costing-runs', [CostingRunController::class, 'index']);
            Route::post('/cost-models/{costModel:slug}/costing-runs', [CostingRunController::class, 'store']);
            Route::get('/costing-runs/{costingRun}', [CostingRunController::class, 'show']);

            // Product Cost Versioning & Approval Workflow
            Route::get('/products/{product:slug}/cost-versions', [ProductCostVersionController::class, 'index']);
            Route::post('/products/{product:slug}/cost-versions', [ProductCostVersionController::class, 'store']);
            Route::get('/cost-versions/{productCostVersion}', [ProductCostVersionController::class, 'show']);
            Route::post('/cost-versions/{productCostVersion}/submit-review', [ProductCostVersionController::class, 'submitForReview']);
            Route::post('/cost-versions/{productCostVersion}/approve', [ProductCostVersionController::class, 'approve']);
            Route::post('/cost-versions/{productCostVersion}/publish', [ProductCostVersionController::class, 'publish']);
            Route::post('/cost-versions/{productCostVersion}/archive', [ProductCostVersionController::class, 'archive']);

            // Pricing Engine & Fee Deductions
            Route::post('/pricing/calculate', [PricingController::class, 'calculate']);
            Route::get('/pricing-rules', [PricingController::class, 'index']);
            Route::post('/pricing-rules', [PricingController::class, 'store']);
            Route::get('/pricing-rules/{pricingRule}', [PricingController::class, 'show']);
            Route::put('/pricing-rules/{pricingRule}', [PricingController::class, 'update']);
            Route::delete('/pricing-rules/{pricingRule}', [PricingController::class, 'destroy']);

            Route::get('/fees', [FeeController::class, 'index']);
            Route::post('/fees', [FeeController::class, 'store']);
            Route::get('/fees/{fee:slug}', [FeeController::class, 'show']);
            Route::put('/fees/{fee:slug}', [FeeController::class, 'update']);
            Route::delete('/fees/{fee:slug}', [FeeController::class, 'destroy']);

            // Profitability & BEP
            Route::post('/profitability/analyze', [ProfitabilityController::class, 'analyze']);
            Route::post('/bep/calculate', [BepController::class, 'calculate']);

            // Actual Costs & Variance Analysis
            Route::post('/products/{product:slug}/actual-cost', [VarianceController::class, 'recordActual']);
            Route::post('/variance/analyze', [VarianceController::class, 'analyze']);

            // What-If Simulation Engine
            Route::post('/cost-models/{costModel:slug}/simulate', [SimulationController::class, 'simulate']);
            Route::get('/cost-models/{costModel:slug}/scenarios', [SimulationController::class, 'index']);

            // Reports Suite (§45 Blueprint)
            Route::get('/reports/hpp-per-product', [ReportController::class, 'hppPerProduct']);
            Route::get('/reports/cost-breakdown', [ReportController::class, 'costBreakdown']);

            // Audit Logs (Restricted to Owner and Admin)
            Route::get('/audit-logs', [AuditLogController::class, 'index'])
                ->middleware('require.role:owner,admin');

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Dashboard
            // ──────────────────────────────────────────────────────────────────
            Route::get('/dashboard', [DashboardController::class, 'summary']);

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Profile & Business Profile
            // ──────────────────────────────────────────────────────────────────
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
            Route::post('/profile/logout', [ProfileController::class, 'logout']);
            Route::post('/profile/logout-all', [ProfileController::class, 'logoutAllDevices']);
            Route::get('/profile/businesses/{business}', [ProfileController::class, 'businessProfile']);
            Route::put('/profile/businesses/{business}', [ProfileController::class, 'updateBusinessProfile']);

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – POS (Point-of-Sale)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('pos')->group(function (): void {
                // Terminal / Register
                Route::get('/terminal/bootstrap', [PosTerminalController::class, 'terminalData']);
                Route::get('/terminal/search-products', [PosTerminalController::class, 'searchProducts']);
                Route::post('/terminal/checkout', [PosTerminalController::class, 'checkout']);
                Route::post('/orders/{posOrder}/hold', [PosTerminalController::class, 'holdOrder']);
                Route::post('/orders/{posOrder}/resume', [PosTerminalController::class, 'resumeOrder']);

                // Shift Management
                Route::post('/shifts/open', [PosShiftController::class, 'open']);
                Route::post('/shifts/{posShift}/close', [PosShiftController::class, 'close']);
                Route::get('/shifts/{posShift}/summary', [PosShiftController::class, 'summary']);
                Route::post('/shifts/{posShift}/cash-movement', [PosShiftController::class, 'recordCashMovement']);
                Route::get('/shifts/active', [PosShiftController::class, 'activeShift']);

                // Orders
                Route::get('/orders', [PosOrderController::class, 'index']);
                Route::get('/orders/{posOrder}', [PosOrderController::class, 'show']);
                Route::post('/orders/{posOrder}/void', [PosOrderController::class, 'void']);
                Route::post('/orders/{posOrder}/refund', [PosOrderController::class, 'refund']);

                // Reports
                Route::get('/reports/sales-summary', [PosReportController::class, 'salesSummary']);
                Route::get('/reports/payment-breakdown', [PosReportController::class, 'paymentBreakdown']);
                Route::get('/reports/top-products', [PosReportController::class, 'topProducts']);
                Route::get('/reports/hourly-heatmap', [PosReportController::class, 'hourlyHeatmap']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Inventory Management
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('inventory')->group(function (): void {
                Route::get('/stocks', [InventoryController::class, 'stocks']);
                Route::get('/stocks/{product}/movements', [InventoryController::class, 'movements']);
                Route::post('/adjustments', [InventoryController::class, 'adjust']);

                // Stock Opname (Physical Count)
                Route::get('/opnames', [StockOpnameController::class, 'index']);
                Route::post('/opnames', [StockOpnameController::class, 'store']);
                Route::get('/opnames/{stockOpname}', [StockOpnameController::class, 'show']);
                Route::post('/opnames/{stockOpname}/reconcile', [StockOpnameController::class, 'reconcile']);

                // Stock Transfers (Inter-Location)
                Route::get('/transfers', [StockTransferController::class, 'index']);
                Route::post('/transfers', [StockTransferController::class, 'store']);
                Route::get('/transfers/{stockTransfer}', [StockTransferController::class, 'show']);
                Route::post('/transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Purchasing
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('purchasing')->group(function (): void {
                // Purchase Orders
                Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
                Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
                Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
                Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
                Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm']);
                Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);

                // Goods Receipts
                Route::get('/goods-receipts', [GoodsReceiptController::class, 'index']);
                Route::post('/goods-receipts', [GoodsReceiptController::class, 'store']);
                Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show']);
                Route::post('/stock-in', [GoodsReceiptController::class, 'instantStockIn']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Sales Pipeline
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('sales')->group(function (): void {
                // Quotations
                Route::get('/quotations', [QuotationController::class, 'index']);
                Route::post('/quotations', [QuotationController::class, 'store']);
                Route::get('/quotations/{quotation}', [QuotationController::class, 'show']);
                Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert']);

                // Sales Orders
                Route::get('/orders', [SalesOrderController::class, 'index']);
                Route::get('/orders/{salesOrder}', [SalesOrderController::class, 'show']);
                Route::post('/orders/{salesOrder}/generate-invoice', [SalesOrderController::class, 'generateInvoice']);

                // Invoices
                Route::get('/invoices', [InvoiceController::class, 'index']);
                Route::post('/invoices', [InvoiceController::class, 'store']);
                Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
                Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment']);
                Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – CRM (Customer Relationship Management)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('crm')->group(function (): void {
                Route::get('/customers', [CustomerController::class, 'index']);
                Route::post('/customers', [CustomerController::class, 'store']);
                Route::get('/customers/{customer}', [CustomerController::class, 'show']);
                Route::put('/customers/{customer}', [CustomerController::class, 'update']);
                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);

                // Loyalty
                Route::get('/loyalty/program', [CrmLoyaltyController::class, 'program']);
                Route::get('/loyalty/customers/{customer}', [CrmLoyaltyController::class, 'customerAccount']);
                Route::post('/loyalty/customers/{customer}/adjust', [CrmLoyaltyController::class, 'adjustPoints']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Finance
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('finance')->group(function (): void {
                Route::get('/dashboard', [FinanceController::class, 'dashboard']);
                Route::get('/expenses', [FinanceController::class, 'expenses']);
                Route::post('/expenses', [FinanceController::class, 'storeExpense']);
                Route::get('/chart-of-accounts', [FinanceController::class, 'chartOfAccounts']);
                Route::get('/journal-entries', [FinanceController::class, 'journalEntries']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – AI Assistant (Cooca Intelligence)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('ai')->group(function (): void {
                Route::post('/chat', [AiAssistantController::class, 'chat']);
                Route::post('/execute-action', [AiAssistantController::class, 'executeAction']);
                Route::get('/token-usage', [AiAssistantController::class, 'tokenUsage']);
                Route::get('/forecasting', [AiAssistantController::class, 'salesForecasting']);
                Route::get('/stock-prediction', [AiAssistantController::class, 'stockPrediction']);
                Route::get('/fraud-detection', [AiAssistantController::class, 'fraudDetection']);
                Route::get('/profitability-matrix', [AiAssistantController::class, 'profitabilityMatrix']);
                Route::get('/smart-pricing', [AiAssistantController::class, 'smartPricing']);
                Route::get('/smart-promo', [AiAssistantController::class, 'smartPromo']);
                Route::get('/customer-rfm', [AiAssistantController::class, 'customerRfm']);
                Route::get('/cashier-performance', [AiAssistantController::class, 'cashierPerformance']);
            });
        });
    });
});
