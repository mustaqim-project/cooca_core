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
use App\Http\Controllers\Api\V1\Finance\CashLedgerController;
use App\Http\Controllers\Api\V1\Finance\PaymentSettlementController;
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
use App\Http\Controllers\Api\V1\Purchasing\PurchaseReportController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierInvoiceController;
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
use App\Http\Controllers\Api\V1\MaterialUnitConversionController;
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

// WhatsApp Gateway Incoming Webhook (from Render.com Node.js Baileys)
// NOTE: Only registered under /v1 prefix with throttle. Bare /wa/webhook routes removed
//       to eliminate the unthrottled attack surface.
Route::prefix('v1')->middleware('throttle:60,1')->group(function (): void {
    Route::post('/wa/webhook', [\App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'handle']);
    Route::post('/wa/admin-webhook', [\App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'handle']);

    // Official Meta WhatsApp Cloud API Webhook (Verification & Event Handlers)
    Route::get('/wa/meta/webhook', [\App\Http\Controllers\Api\V1\WhatsApp\MetaWhatsAppWebhookController::class, 'verify']);
    Route::post('/wa/meta/webhook', [\App\Http\Controllers\Api\V1\WhatsApp\MetaWhatsAppWebhookController::class, 'handle']);

    // Official Meta Social Media Webhook (Facebook Pages & Instagram Business)
    Route::get('/social-media/meta/webhook', [\App\Http\Controllers\Api\V1\SocialMedia\MetaSocialMediaWebhookController::class, 'verify']);
    Route::post('/social-media/meta/webhook', [\App\Http\Controllers\Api\V1\SocialMedia\MetaSocialMediaWebhookController::class, 'handle']);

    // TriPay Payment Gateway Incoming Webhook (Model B)
    Route::post('/payments/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);
});


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
        Route::get('/businesses/{business:slug}/members', [MemberController::class, 'index'])->middleware('require.permission:users.view');
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
            Route::get('/members', [MemberController::class, 'index'])->middleware('require.permission:users.view');
            Route::post('/members', [MemberController::class, 'store'])
                ->middleware('require.role:owner,admin');
            Route::patch('/members/{member}', [MemberController::class, 'update'])
                ->middleware('require.role:owner,admin');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])
                ->middleware('require.role:owner,admin');

            // Tenant Custom Units & Conversions
            Route::post('/units', [UnitController::class, 'store'])->middleware('require.permission:master_data.units.manage');
            Route::get('/units/{unit}', [UnitController::class, 'show'])->middleware('require.permission:master_data.units.view');
            Route::put('/units/{unit}', [UnitController::class, 'update'])->middleware('require.permission:master_data.units.manage');
            Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->middleware('require.permission:master_data.units.manage');

            Route::get('/unit-conversions', [UnitConversionController::class, 'index'])->middleware('require.permission:master_data.units.view');
            Route::post('/unit-conversions', [UnitConversionController::class, 'store'])->middleware('require.permission:master_data.units.manage');
            Route::delete('/unit-conversions/{unitConversion}', [UnitConversionController::class, 'destroy'])->middleware('require.permission:master_data.units.manage');

            Route::get('/material-unit-conversions', [MaterialUnitConversionController::class, 'index'])->middleware('require.permission:master_data.units.view');
            Route::post('/material-unit-conversions', [MaterialUnitConversionController::class, 'store'])->middleware('require.permission:master_data.units.manage');
            Route::delete('/material-unit-conversions/{materialUnitConversion}', [MaterialUnitConversionController::class, 'destroy'])->middleware('require.permission:master_data.units.manage');

            // Multi-Location & Multi-Currency per Business
            Route::get('/locations', [LocationController::class, 'index'])->middleware('require.permission:warehouse.view');
            Route::post('/locations', [LocationController::class, 'store'])->middleware('require.permission:warehouse.manage');
            Route::get('/locations/{location:slug}', [LocationController::class, 'show'])->middleware('require.permission:warehouse.view');
            Route::put('/locations/{location:slug}', [LocationController::class, 'update'])->middleware('require.permission:warehouse.manage');
            Route::delete('/locations/{location:slug}', [LocationController::class, 'destroy'])->middleware('require.permission:warehouse.manage');

            Route::get('/exchange-rates', [ExchangeRateController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/exchange-rates', [ExchangeRateController::class, 'store'])->middleware('require.permission:costing.manage');

            // Suppliers CRUD
            Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('require.permission:master_data.suppliers.view');
            Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('require.permission:master_data.suppliers.manage');
            Route::get('/suppliers/{supplier:slug}', [SupplierController::class, 'show'])->middleware('require.permission:master_data.suppliers.view');
            Route::put('/suppliers/{supplier:slug}', [SupplierController::class, 'update'])->middleware('require.permission:master_data.suppliers.manage');
            Route::delete('/suppliers/{supplier:slug}', [SupplierController::class, 'destroy'])->middleware('require.permission:master_data.suppliers.manage');

            // Material Categories CRUD
            Route::get('/material-categories', [MaterialCategoryController::class, 'index'])->middleware('require.permission:master_data.material_categories.view');
            Route::post('/material-categories', [MaterialCategoryController::class, 'store'])->middleware('require.permission:master_data.material_categories.manage');
            Route::get('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'show'])->middleware('require.permission:master_data.material_categories.view');
            Route::put('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'update'])->middleware('require.permission:master_data.material_categories.manage');
            Route::delete('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'destroy'])->middleware('require.permission:master_data.material_categories.manage');

            // Materials & Prices CRUD
            Route::get('/materials', [MaterialController::class, 'index'])->middleware('require.permission:materials.view');
            Route::post('/materials', [MaterialController::class, 'store'])->middleware('require.permission:materials.create');
            Route::get('/materials/{material:slug}', [MaterialController::class, 'show'])->middleware('require.permission:materials.view');
            Route::put('/materials/{material:slug}', [MaterialController::class, 'update'])->middleware('require.permission:materials.edit');
            Route::delete('/materials/{material:slug}', [MaterialController::class, 'destroy'])->middleware('require.permission:materials.delete');
            Route::post('/materials/{material:slug}/prices', [MaterialController::class, 'storePrice'])->middleware('require.permission:materials.edit');
            Route::get('/materials/{material:slug}/effective-cost', [MaterialController::class, 'effectiveCost'])->middleware('require.permission:materials.view');

            // Product Categories CRUD
            Route::get('/product-categories', [ProductCategoryController::class, 'index'])->middleware('require.permission:master_data.product_categories.view');
            Route::post('/product-categories', [ProductCategoryController::class, 'store'])->middleware('require.permission:master_data.product_categories.manage');
            Route::get('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'show'])->middleware('require.permission:master_data.product_categories.view');
            Route::put('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'update'])->middleware('require.permission:master_data.product_categories.manage');
            Route::delete('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'destroy'])->middleware('require.permission:master_data.product_categories.manage');

            // Products CRUD
            Route::get('/products', [ProductController::class, 'index'])->middleware('require.permission:products.view');
            Route::post('/products', [ProductController::class, 'store'])->middleware('require.permission:products.create');
            Route::get('/products/{product:slug}', [ProductController::class, 'show'])->middleware('require.permission:products.view');
            Route::put('/products/{product:slug}', [ProductController::class, 'update'])->middleware('require.permission:products.edit');
            Route::delete('/products/{product:slug}', [ProductController::class, 'destroy'])->middleware('require.permission:products.delete');

            // Cost Categories & Cost Models
            Route::get('/cost-categories', [CostModelController::class, 'categories'])->middleware('require.permission:costing.view_margin');
            Route::post('/products/{product:slug}/cost-models', [CostModelController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}', [CostModelController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/cost-models/{costModel:slug}', [CostModelController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-models/{costModel:slug}', [CostModelController::class, 'destroy'])->middleware('require.permission:costing.manage');
            Route::put('/cost-models/{costModel:slug}/components', [CostModelController::class, 'syncComponents'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}/preview', [CostModelMachineController::class, 'preview'])->middleware('require.permission:costing.view_margin');

            // BOM / Recipe Engine
            Route::post('/cost-models/{costModel:slug}/bom', [BomController::class, 'createOrGetHeader'])->middleware('require.permission:costing.manage');
            Route::post('/bom-headers/{bomHeader}/items', [BomController::class, 'addItem'])->middleware('require.permission:costing.manage');
            Route::put('/bom-items/{bomItem}', [BomController::class, 'updateItem'])->middleware('require.permission:costing.manage');
            Route::delete('/bom-items/{bomItem}', [BomController::class, 'deleteItem'])->middleware('require.permission:costing.manage');
            Route::get('/bom-headers/{bomHeader}/explode', [BomController::class, 'explode'])->middleware('require.permission:costing.view_margin');

            // Labor Rates & Labor Assignments
            Route::get('/labor-rates', [LaborRateController::class, 'index'])->middleware('require.permission:labor_machines.view');
            Route::post('/labor-rates', [LaborRateController::class, 'store'])->middleware('require.permission:labor_machines.manage');
            Route::get('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'show'])->middleware('require.permission:labor_machines.view');
            Route::put('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'update'])->middleware('require.permission:labor_machines.manage');
            Route::delete('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'destroy'])->middleware('require.permission:labor_machines.manage');
            Route::get('/labor-rates/{laborRate:slug}/hourly-equivalent', [LaborRateController::class, 'hourlyEquivalent'])->middleware('require.permission:labor_machines.view');
            Route::post('/cost-models/{costModel:slug}/labor', [CostModelLaborController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::put('/cost-model-labors/{costModelLabor}', [CostModelLaborController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-model-labors/{costModelLabor}', [CostModelLaborController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Machines & Machine Assignments
            Route::get('/machines', [MachineController::class, 'index'])->middleware('require.permission:labor_machines.view');
            Route::post('/machines', [MachineController::class, 'store'])->middleware('require.permission:labor_machines.manage');
            Route::get('/machines/{machine:slug}', [MachineController::class, 'show'])->middleware('require.permission:labor_machines.view');
            Route::put('/machines/{machine:slug}', [MachineController::class, 'update'])->middleware('require.permission:labor_machines.manage');
            Route::delete('/machines/{machine:slug}', [MachineController::class, 'destroy'])->middleware('require.permission:labor_machines.manage');
            Route::get('/machines/{machine:slug}/cost-per-hour', [MachineController::class, 'costPerHour'])->middleware('require.permission:labor_machines.view');
            Route::post('/cost-models/{costModel:slug}/machines', [CostModelMachineController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::put('/cost-model-machines/{costModelMachine}', [CostModelMachineController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-model-machines/{costModelMachine}', [CostModelMachineController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Overheads & Cost Pools
            Route::get('/overheads', [OverheadController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/overheads', [OverheadController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/overheads/{overhead:slug}', [OverheadController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/overheads/{overhead:slug}', [OverheadController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/overheads/{overhead:slug}', [OverheadController::class, 'destroy'])->middleware('require.permission:costing.manage');

            Route::get('/cost-pools', [CostPoolController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-pools', [CostPoolController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-pools/{costPool:slug}', [CostPoolController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/cost-pools/{costPool:slug}', [CostPoolController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-pools/{costPool:slug}', [CostPoolController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Cost Drivers & Allocation Rules
            Route::get('/cost-drivers', [CostDriverController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-drivers', [CostDriverController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'destroy'])->middleware('require.permission:costing.manage');

            Route::get('/allocation-rules', [AllocationRuleController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/allocation-rules', [AllocationRuleController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'destroy'])->middleware('require.permission:costing.manage');
            Route::post('/allocation-rules/{allocationRule}/preview', [AllocationRuleController::class, 'preview'])->middleware('require.permission:costing.view_margin');

            // Activity-Based Costing (ABC)
            Route::get('/abc/activities', [AbcActivityController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/abc/activities', [AbcActivityController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/abc/activities/{activity:slug}', [AbcActivityController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/abc/activities/{activity:slug}', [AbcActivityController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/abc/activities/{activity:slug}', [AbcActivityController::class, 'destroy'])->middleware('require.permission:costing.manage');
            Route::post('/cost-models/{costModel:slug}/abc-activities', [AbcActivityController::class, 'assignToCostModel'])->middleware('require.permission:costing.manage');
            Route::put('/cost-model-activities/{costModelActivity}', [AbcActivityController::class, 'updateCostModelActivity'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-model-activities/{costModelActivity}', [AbcActivityController::class, 'removeCostModelActivity'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}/abc-cost', [AbcActivityController::class, 'calculateForCostModel'])->middleware('require.permission:costing.view_margin');

            // Calculation Engine Execution
            Route::post('/costing/calculate/{costModel:slug}', [CalculationController::class, 'calculate'])->middleware('require.permission:costing.manage');

            // Costing Runs & Official History
            Route::get('/cost-models/{costModel:slug}/costing-runs', [CostingRunController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-models/{costModel:slug}/costing-runs', [CostingRunController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/costing-runs/{costingRun}', [CostingRunController::class, 'show'])->middleware('require.permission:costing.view_margin');

            // Product Cost Versioning & Approval Workflow
            Route::get('/products/{product:slug}/cost-versions', [ProductCostVersionController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/products/{product:slug}/cost-versions', [ProductCostVersionController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-versions/{productCostVersion}', [ProductCostVersionController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-versions/{productCostVersion}/submit-review', [ProductCostVersionController::class, 'submitForReview'])->middleware('require.permission:costing.manage');
            Route::post('/cost-versions/{productCostVersion}/approve', [ProductCostVersionController::class, 'approve'])->middleware('require.permission:costing.manage');
            Route::post('/cost-versions/{productCostVersion}/publish', [ProductCostVersionController::class, 'publish'])->middleware('require.permission:costing.manage');
            Route::post('/cost-versions/{productCostVersion}/archive', [ProductCostVersionController::class, 'archive'])->middleware('require.permission:costing.manage');

            // Pricing Engine & Fee Deductions
            Route::post('/pricing/calculate', [PricingController::class, 'calculate'])->middleware('require.permission:costing.view_margin');
            Route::get('/pricing-rules', [PricingController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/pricing-rules', [PricingController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/pricing-rules/{pricingRule}', [PricingController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/pricing-rules/{pricingRule}', [PricingController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/pricing-rules/{pricingRule}', [PricingController::class, 'destroy'])->middleware('require.permission:costing.manage');

            Route::get('/fees', [FeeController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/fees', [FeeController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/fees/{fee:slug}', [FeeController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/fees/{fee:slug}', [FeeController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/fees/{fee:slug}', [FeeController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Profitability & BEP
            Route::post('/profitability/analyze', [ProfitabilityController::class, 'analyze'])->middleware('require.permission:costing.view_margin');
            Route::post('/bep/calculate', [BepController::class, 'calculate'])->middleware('require.permission:costing.view_margin');

            // Actual Costs & Variance Analysis
            Route::post('/products/{product:slug}/actual-cost', [VarianceController::class, 'recordActual'])->middleware('require.permission:costing.manage');
            Route::post('/variance/analyze', [VarianceController::class, 'analyze'])->middleware('require.permission:costing.view_margin');

            // What-If Simulation Engine
            Route::post('/cost-models/{costModel:slug}/simulate', [SimulationController::class, 'simulate'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}/scenarios', [SimulationController::class, 'index'])->middleware('require.permission:costing.view_margin');

            // Reports Suite (§45 Blueprint)
            Route::get('/reports/hpp-per-product', [ReportController::class, 'hppPerProduct'])->middleware('require.permission:costing.view_margin');
            Route::get('/reports/cost-breakdown', [ReportController::class, 'costBreakdown'])->middleware('require.permission:costing.view_margin');

            // Sales Reports berbasis SNAPSHOT transaksi
            Route::get('/reports/sales-summary', [PosReportController::class, 'salesSummary'])->middleware('require.permission:pos.reports');
            Route::get('/reports/sales-average-prices', [PosReportController::class, 'averagePrices'])->middleware('require.permission:pos.reports');

            // Purchase Reports berbasis SNAPSHOT PO
            Route::get('/reports/purchase-summary', [PurchaseReportController::class, 'summary'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-history', [PurchaseReportController::class, 'history'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-period', [PurchaseReportController::class, 'period'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-supplier', [PurchaseReportController::class, 'supplier'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-product', [PurchaseReportController::class, 'product'])->middleware('require.permission:purchasing.view');

            // Audit Logs (Restricted to Owner and Admin)
            Route::get('/audit-logs', [AuditLogController::class, 'index'])
                ->middleware('require.role:owner,admin');

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Dashboard
            // ──────────────────────────────────────────────────────────────────
            Route::get('/dashboard', [DashboardController::class, 'summary'])->middleware('require.permission:dashboard.view');

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Profile & Business Profile
            // ──────────────────────────────────────────────────────────────────
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
            Route::get('/auth/verification-status', [AuthController::class, 'verificationStatus']);
            Route::post('/auth/verification-notification', [AuthController::class, 'resendVerification']);
            Route::post('/profile/logout', [ProfileController::class, 'logout']);
            Route::post('/profile/logout-all', [ProfileController::class, 'logoutAllDevices']);
            Route::get('/profile/businesses/{business}', [ProfileController::class, 'businessProfile']);
            Route::put('/profile/businesses/{business}', [ProfileController::class, 'updateBusinessProfile']);

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – POS (Point-of-Sale)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('pos')->group(function (): void {
                // Terminal / Register
                Route::get('/terminal/bootstrap', [PosTerminalController::class, 'terminalData'])->middleware('require.permission:pos.terminal');
                Route::get('/terminal/search-products', [PosTerminalController::class, 'searchProducts'])->middleware('require.permission:pos.terminal');
                Route::post('/terminal/checkout', [PosTerminalController::class, 'checkout'])->middleware('require.permission:pos.terminal');
                Route::post('/orders/{posOrder}/hold', [PosTerminalController::class, 'holdOrder'])->middleware('require.permission:pos.terminal');
                Route::post('/orders/{posOrder}/resume', [PosTerminalController::class, 'resumeOrder'])->middleware('require.permission:pos.terminal');

                // Supervisor Authorization (mirror of web /pos/verify-pin)
                Route::post('/verify-pin', [PosTerminalController::class, 'verifySupervisorPin'])->middleware('throttle:5,1');

                // Shift Management
                Route::post('/shifts/open', [PosShiftController::class, 'open'])->middleware('require.permission:pos.orders');
                Route::post('/shifts/{posShift}/close', [PosShiftController::class, 'close'])->middleware('require.permission:pos.orders');
                Route::get('/shifts/{posShift}/summary', [PosShiftController::class, 'summary'])->middleware('require.permission:pos.orders');
                Route::post('/shifts/{posShift}/cash-movement', [PosShiftController::class, 'recordCashMovement'])->middleware('require.permission:pos.orders');
                Route::get('/shifts/active', [PosShiftController::class, 'activeShift'])->middleware('require.permission:pos.orders');

                // Orders
                Route::get('/orders', [PosOrderController::class, 'index'])->middleware('require.permission:pos.orders');
                Route::get('/orders/{posOrder}', [PosOrderController::class, 'show'])->middleware('require.permission:pos.orders');
                Route::post('/orders/{posOrder}/void', [PosOrderController::class, 'void'])->middleware('require.permission:pos.supervisor_pin');
                Route::post('/orders/{posOrder}/refund', [PosOrderController::class, 'refund'])->middleware('require.permission:pos.supervisor_pin');

                // Reports
                Route::get('/reports/sales-summary', [PosReportController::class, 'salesSummary'])->middleware('require.permission:pos.reports');
                Route::get('/reports/average-prices', [PosReportController::class, 'averagePrices'])->middleware('require.permission:pos.reports');
                Route::get('/reports/payment-breakdown', [PosReportController::class, 'paymentBreakdown'])->middleware('require.permission:pos.reports');
                Route::get('/reports/top-products', [PosReportController::class, 'topProducts'])->middleware('require.permission:pos.reports');
                Route::get('/reports/hourly-heatmap', [PosReportController::class, 'hourlyHeatmap'])->middleware('require.permission:pos.reports');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Inventory Management
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('inventory')->group(function (): void {
                Route::get('/stocks', [InventoryController::class, 'stocks'])->middleware('require.permission:inventory.view');
                Route::get('/stocks/{product}/movements', [InventoryController::class, 'movements'])->middleware('require.permission:inventory.view');
                Route::post('/adjustments', [InventoryController::class, 'adjust'])->middleware('require.permission:inventory.manage');

                // Stock Opname (Physical Count)
                Route::get('/opnames', [StockOpnameController::class, 'index'])->middleware('require.permission:inventory.view');
                Route::post('/opnames', [StockOpnameController::class, 'store'])->middleware('require.permission:inventory.manage');
                Route::get('/opnames/{stockOpname}', [StockOpnameController::class, 'show'])->middleware('require.permission:inventory.view');
                Route::post('/opnames/{stockOpname}/reconcile', [StockOpnameController::class, 'reconcile'])->middleware('require.permission:inventory.manage');

                // Stock Transfers (Inter-Location)
                Route::get('/transfers', [StockTransferController::class, 'index'])->middleware('require.permission:inventory.view');
                Route::post('/transfers', [StockTransferController::class, 'store'])->middleware('require.permission:inventory.manage');
                Route::get('/transfers/{stockTransfer}', [StockTransferController::class, 'show'])->middleware('require.permission:inventory.view');
                Route::post('/transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->middleware('require.permission:inventory.manage');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Purchasing
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('purchasing')->group(function (): void {
                // Purchase Orders
                Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('require.permission:purchasing.view');
                Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('require.permission:purchasing.manage');
                Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('require.permission:purchasing.view');
                Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->middleware('require.permission:purchasing.manage');
                Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm'])->middleware('require.permission:purchasing.manage');
                Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->middleware('require.permission:purchasing.manage');

                // Goods Receipts
                Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->middleware('require.permission:receiving.manage');
                Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->middleware('require.permission:receiving.manage');
                Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->middleware('require.permission:receiving.manage');
                Route::post('/stock-in', [GoodsReceiptController::class, 'instantStockIn'])->middleware('require.permission:receiving.manage');
                Route::get('/supplier-invoices', [SupplierInvoiceController::class, 'index'])->middleware('require.permission:purchasing.bills');
                Route::get('/supplier-invoices/{supplierInvoice}', [SupplierInvoiceController::class, 'show'])->middleware('require.permission:purchasing.bills');
                Route::post('/supplier-invoices/{supplierInvoice}/payments', [SupplierInvoiceController::class, 'recordPayment'])->middleware('require.permission:purchasing.bills');
                Route::get('/returns', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'index'])->middleware('require.permission:purchase.returns');
                Route::post('/returns', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'store'])->middleware('require.permission:purchase.returns');
                Route::post('/returns/{return}/approve', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'approve'])->middleware('require.permission:purchase.returns');
                Route::post('/returns/{return}/complete', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'complete'])->middleware('require.permission:purchase.returns');

                // Purchase Reports (snapshot harga beli PO)
                Route::prefix('reports')->group(function (): void {
                    Route::get('/summary', [PurchaseReportController::class, 'summary'])->middleware('require.permission:purchasing.view');
                    Route::get('/history', [PurchaseReportController::class, 'history'])->middleware('require.permission:purchasing.view');
                    Route::get('/period', [PurchaseReportController::class, 'period'])->middleware('require.permission:purchasing.view');
                    Route::get('/supplier', [PurchaseReportController::class, 'supplier'])->middleware('require.permission:purchasing.view');
                    Route::get('/product', [PurchaseReportController::class, 'product'])->middleware('require.permission:purchasing.view');
                });
            });

            // Cash & bank ledger for the active business.
            Route::prefix('finance/cash-ledger')->group(function (): void {
                Route::get('/accounts', [CashLedgerController::class, 'accounts'])->middleware('require.permission:finance.cash_bank');
                Route::get('/transactions', [CashLedgerController::class, 'transactions'])->middleware('require.permission:finance.cash_bank');
                Route::post('/inflows', [CashLedgerController::class, 'inflow'])->middleware('require.permission:finance.cash_bank');
                Route::post('/outflows', [CashLedgerController::class, 'outflow'])->middleware('require.permission:finance.cash_bank');
                Route::post('/transfers', [CashLedgerController::class, 'transfer'])->middleware('require.permission:finance.cash_bank');
            });
            Route::prefix('finance/settlements')->group(function (): void {
                Route::get('/', [PaymentSettlementController::class, 'index'])->middleware('require.permission:finance.cash_bank');
                Route::post('/reconcile', [PaymentSettlementController::class, 'reconcile'])->middleware('require.permission:finance.cash_bank');
                Route::get('/{settlement}', [PaymentSettlementController::class, 'show'])->middleware('require.permission:finance.cash_bank');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Sales Pipeline
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('sales')->group(function (): void {
                // Quotations
                Route::get('/quotations', [QuotationController::class, 'index'])->middleware('require.permission:sales.view');
                Route::post('/quotations', [QuotationController::class, 'store'])->middleware('require.permission:sales.pipeline');
                Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->middleware('require.permission:sales.view');
                Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->middleware('require.permission:sales.pipeline');

                // Sales Orders
                Route::get('/orders', [SalesOrderController::class, 'index'])->middleware('require.permission:sales.view');
                Route::get('/orders/{salesOrder}', [SalesOrderController::class, 'show'])->middleware('require.permission:sales.view');
                Route::post('/orders/{salesOrder}/generate-invoice', [SalesOrderController::class, 'generateInvoice'])->middleware('require.permission:sales.pipeline');

                // Invoices
                Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('require.permission:invoices.view');
                Route::post('/invoices', [InvoiceController::class, 'store'])->middleware('require.permission:invoices.create');
                Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('require.permission:invoices.view');
                Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->middleware('require.permission:invoices.record_payment');
                Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('require.permission:invoices.delete');
                Route::get('/returns', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'index'])->middleware('require.permission:sales.returns');
                Route::post('/returns', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'store'])->middleware('require.permission:sales.returns');
                Route::post('/returns/{return}/approve', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'approve'])->middleware('require.permission:sales.returns');
                Route::post('/returns/{return}/complete', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'complete'])->middleware('require.permission:sales.returns');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – CRM (Customer Relationship Management)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('crm')->group(function (): void {
                Route::get('/customers', [CustomerController::class, 'index'])->middleware('require.permission:customers.view');
                Route::post('/customers', [CustomerController::class, 'store'])->middleware('require.permission:customers.create');
                Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('require.permission:customers.view');
                Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('require.permission:customers.edit');
                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('require.permission:customers.delete');

                // Loyalty
                Route::get('/loyalty/program', [CrmLoyaltyController::class, 'program'])->middleware('require.permission:crm.view');
                Route::get('/loyalty/customers/{customer}', [CrmLoyaltyController::class, 'customerAccount'])->middleware('require.permission:crm.view');
                Route::post('/loyalty/customers/{customer}/adjust', [CrmLoyaltyController::class, 'adjustPoints'])->middleware('require.permission:crm.manage');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Finance
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('finance')->group(function (): void {
                Route::get('/dashboard', [FinanceController::class, 'dashboard'])->middleware('require.permission:accounting.view');
                Route::get('/expenses', [FinanceController::class, 'expenses'])->middleware('require.permission:expenses.view');
                Route::post('/expenses', [FinanceController::class, 'storeExpense'])->middleware('require.permission:expenses.manage');
                Route::get('/chart-of-accounts', [FinanceController::class, 'chartOfAccounts'])->middleware('require.permission:accounting.view');
                Route::get('/journal-entries', [FinanceController::class, 'journalEntries'])->middleware('require.permission:accounting.view');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – AI Assistant (Cooca Intelligence)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('ai')->group(function (): void {
                Route::post('/chat', [AiAssistantController::class, 'chat'])->middleware('require.permission:ai.access');
                Route::post('/execute-action', [AiAssistantController::class, 'executeAction'])->middleware('require.permission:ai.access');
                Route::get('/token-usage', [AiAssistantController::class, 'tokenUsage'])->middleware('require.permission:ai.access');
                Route::get('/forecasting', [AiAssistantController::class, 'salesForecasting'])->middleware('require.permission:ai.access');
                Route::get('/stock-prediction', [AiAssistantController::class, 'stockPrediction'])->middleware('require.permission:ai.access');
                Route::get('/fraud-detection', [AiAssistantController::class, 'fraudDetection'])->middleware('require.permission:ai.access');
                Route::get('/profitability-matrix', [AiAssistantController::class, 'profitabilityMatrix'])->middleware('require.permission:ai.access');
                Route::get('/smart-pricing', [AiAssistantController::class, 'smartPricing'])->middleware('require.permission:ai.access');
                Route::get('/smart-promo', [AiAssistantController::class, 'smartPromo'])->middleware('require.permission:ai.access');
                Route::get('/customer-rfm', [AiAssistantController::class, 'customerRfm'])->middleware('require.permission:ai.access');
                Route::get('/cashier-performance', [AiAssistantController::class, 'cashierPerformance'])->middleware('require.permission:ai.access');
            });
        });
    });
});
