<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Ai\PosAiWebController;
use App\Http\Controllers\Web\Billing\BillingAndLimitWebController;
use App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController;
use App\Http\Controllers\Web\BusinessLandingPageWebController;
use App\Http\Controllers\Web\CalculatorWebController;
use App\Http\Controllers\Web\Commerce\MerchantOrderController;
use App\Http\Controllers\Web\Commerce\MerchantReservationController;
use App\Http\Controllers\Web\Commerce\MerchantShippingRuleController;
use App\Http\Controllers\Web\Commerce\MerchantStoreSettingController;
use App\Http\Controllers\Web\CommunityWebController;
use App\Http\Controllers\Web\Crm\CrmWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\FeedbackWebController;
use App\Http\Controllers\Web\Finance\CashLedgerWebController;
use App\Http\Controllers\Web\Finance\PaymentSettlementWebController;
use App\Http\Controllers\Web\Finance\PosFinanceWebController;
use App\Http\Controllers\Web\ImportWebController;
use App\Http\Controllers\Web\Inventory\InventoryWebController;
use App\Http\Controllers\Web\InvoiceWebController;
use App\Http\Controllers\Web\LaborMachineWebController;
use App\Http\Controllers\Web\MasterDataWebController;
use App\Http\Controllers\Web\MaterialCategoryWebController;
use App\Http\Controllers\Web\MaterialUnitConversionWebController;
use App\Http\Controllers\Web\MaterialWebController;
use App\Http\Controllers\Web\OnboardingWebController;
use App\Http\Controllers\Web\Pos\ModifierWebController;
use App\Http\Controllers\Web\Pos\PosKitchenWebController;
use App\Http\Controllers\Web\Pos\PosOrderWebController;
use App\Http\Controllers\Web\Pos\PosReportWebController;
use App\Http\Controllers\Web\Pos\PosShiftWebController;
use App\Http\Controllers\Web\Pos\PosTableWebController;
use App\Http\Controllers\Web\Pos\PosTerminalWebController;
use App\Http\Controllers\Web\ProductCategoryWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\ProfileWebController;
use App\Http\Controllers\Web\ProfitabilityWebController;
use App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController;
use App\Http\Controllers\Web\PurchaseReturnWebController;
use App\Http\Controllers\Web\Purchasing\SupplierInvoiceWebController;
use App\Http\Controllers\Web\PurchaseOrderWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\RoleWebController;
use App\Http\Controllers\Web\Sales\QuotationWebController;
use App\Http\Controllers\Web\Sales\SalesOrderWebController;
use App\Http\Controllers\Web\SalesReturnWebController;
use App\Http\Controllers\Web\ServiceWebController;
use App\Http\Controllers\Web\SettingWebController;
use App\Http\Controllers\Web\SimulationWebController;
use App\Http\Controllers\Web\SocialMedia\SocialMediaWebController;
use App\Http\Controllers\Web\SupplierWebController;
use App\Http\Controllers\Web\UnitConversionWebController;
use App\Http\Controllers\Web\UnitWebController;
use App\Http\Controllers\Web\Warehouse\WarehouseWebController;
use App\Http\Controllers\Web\WhatsApp\MetaWhatsAppOnboardingController;
use App\Http\Controllers\Web\WhatsApp\WhatsAppBroadcastWebController;
use App\Http\Controllers\Web\WhatsApp\WhatsAppWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authenticated Owner & Merchant Workspace Panel (Guard: web)
|--------------------------------------------------------------------------
|
| Houses the full ERP, POS, inventory, finance, purchasing, sales, and
| business management operations for business owners and authorized staff.
|
*/
Route::middleware(['auth:web', 'wa.otp'])->group(function (): void {

    // 1. User Profile & Password Management
    Route::get('/profile', [ProfileWebController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileWebController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/email', [ProfileWebController::class, 'updateEmail'])->name('profile.email.update');
    Route::post('/profile/phone', [ProfileWebController::class, 'requestPhoneChange'])->name('profile.phone.request');
    Route::post('/profile/contact', [ProfileWebController::class, 'requestContactChange'])->name('profile.contact.request');
    Route::get('/profile/contact/verify', function () {
        $pending = session('profile_phone_change') ?: session('profile_contact_change');
        if (! $pending) {
            return redirect()->route('profile.edit');
        }

        $phone = (string) ($pending['phone'] ?? (request()->user()?->phone ?? ''));
        $masked = strlen($phone) > 6
            ? substr($phone, 0, 4) . str_repeat('*', max(2, strlen($phone) - 7)) . substr($phone, -3)
            : ($phone !== '' ? $phone : 'nomor WhatsApp owner');

        return view('app.profile.verify-contact', [
            'phone' => $masked,
            'expiresAt' => (int) ($pending['expires_at'] ?? 0),
            'resendIn' => max(0, ((int) ($pending['last_sent_at'] ?? 0)) + 60 - time()),
        ]);
    })->name('profile.contact.verify');
    Route::post('/profile/contact/verify', [ProfileWebController::class, 'verifyContactChange'])->middleware('throttle:10,1')->name('profile.contact.verify.submit');
    Route::post('/profile/contact/verify/resend', [ProfileWebController::class, 'resendContactChange'])->middleware('throttle:3,1')->name('profile.contact.verify.resend');
    Route::put('/profile/password', [ProfileWebController::class, 'updatePassword'])->name('profile.password');

    // 2. Onboarding / Guided Product Tour Persistence Routes
    Route::get('/onboarding/status', [OnboardingWebController::class, 'status'])->name('onboarding.status');
    Route::post('/onboarding/step', [OnboardingWebController::class, 'updateStep'])->name('onboarding.step');
    Route::post('/onboarding/complete', [OnboardingWebController::class, 'complete'])->name('onboarding.complete');
    Route::post('/onboarding/restart', [OnboardingWebController::class, 'restart'])->name('onboarding.restart');

    // 3. Tenant Protected Core Web Panel
    Route::middleware(['business.active', 'profile.complete', 'verified'])->group(function (): void {
        // Executive Dashboard & Quick Actions
        Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/quick-stats', [DashboardWebController::class, 'quickStats'])->name('dashboard.quick-stats');
        Route::post('/dashboard/quick-expense', [DashboardWebController::class, 'quickExpense'])->name('dashboard.quick-expense');
        Route::post('/dashboard/quick-stock-in', [DashboardWebController::class, 'quickStockIn'])->name('dashboard.quick-stock-in');
        Route::post('/dashboard/quick-material', [DashboardWebController::class, 'quickMaterial'])->name('dashboard.quick-material');

        // Interactive Live HPP Calculator
        Route::get('/calculator', [CalculatorWebController::class, 'index'])->middleware('require.permission:costing.view_margin')->name('calculator.index');
        Route::get('/calculator/calculate/{costModel}', [CalculatorWebController::class, 'calculate'])->name('calculator.calculate');
        Route::post('/calculator/save', [CalculatorWebController::class, 'saveResult'])->name('calculator.save');
        Route::post('/calculator/apply-to-product', [CalculatorWebController::class, 'applyToProduct'])->name('calculator.apply-to-product');
        Route::post('/calculator/quick-create-product', [CalculatorWebController::class, 'quickCreateProduct'])->name('calculator.quick-create-product');
        Route::get('/calculator/export-excel', [CalculatorWebController::class, 'exportExcel'])->middleware('entitlement:export')->name('calculator.export-excel');

        // Materials & Pricing Management
        Route::get('/materials', [MaterialWebController::class, 'index'])->middleware('require.permission:materials.view')->name('materials.index');
        Route::post('/materials', [MaterialWebController::class, 'store'])->middleware(['require.permission:materials.create', 'entitlement:material'])->name('materials.store');
        Route::put('/materials/{material}', [MaterialWebController::class, 'update'])->middleware('require.permission:materials.edit')->name('materials.update');
        Route::post('/materials/{material}/prices', [MaterialWebController::class, 'storePrice'])->middleware('require.permission:materials.edit')->name('materials.store-price');
        Route::delete('/materials/{material}', [MaterialWebController::class, 'destroy'])->middleware('require.permission:materials.delete')->name('materials.destroy');

        // Supplier & Material Category Master Data
        Route::get('/suppliers', [SupplierWebController::class, 'index'])->middleware('require.permission:master_data.suppliers.view')->name('suppliers.index');
        Route::post('/suppliers', [SupplierWebController::class, 'store'])->middleware(['require.permission:master_data.suppliers.manage', 'entitlement:supplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierWebController::class, 'update'])->middleware('require.permission:master_data.suppliers.manage')->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierWebController::class, 'destroy'])->middleware('require.permission:master_data.suppliers.manage')->name('suppliers.destroy');
        Route::get('/material-categories', [MasterDataWebController::class, 'materialCategories'])->middleware('require.permission:master_data.material_categories.view')->name('material-categories.index');
        Route::post('/material-categories', [MaterialCategoryWebController::class, 'store'])->middleware('require.permission:master_data.material_categories.manage')->name('material-categories.store');
        Route::put('/material-categories/{category}', [MaterialCategoryWebController::class, 'update'])->middleware('require.permission:master_data.material_categories.manage')->name('material-categories.update');
        Route::delete('/material-categories/{category}', [MaterialCategoryWebController::class, 'destroy'])->middleware('require.permission:master_data.material_categories.manage')->name('material-categories.destroy');

        // Products & BOM Management
        Route::middleware('require.permission:products.view')->group(function (): void {
            Route::get('/products', [ProductWebController::class, 'index'])->name('products.index');
            Route::get('/products/{product}/bom', [ProductWebController::class, 'bom'])->name('products.bom');
        });
        Route::post('/products', [ProductWebController::class, 'store'])->middleware(['require.permission:products.create', 'entitlement:product'])->name('products.store');
        Route::post('/products/toggle-pos-images', [ProductWebController::class, 'togglePosImageVisibility'])->middleware('require.permission:products.edit')->name('products.toggle-pos-images');
        Route::post('/products/{product}/toggle-setting', [ProductWebController::class, 'toggleSetting'])->middleware('require.permission:products.edit')->name('products.toggle-setting');
        Route::put('/products/{product}', [ProductWebController::class, 'update'])->middleware('require.permission:products.edit')->name('products.update');
        Route::post('/bom-headers/{bomHeader}/items', [ProductWebController::class, 'addBomItem'])->middleware(['require.permission:products.edit', 'entitlement:recipe'])->name('bom.items.store');
        Route::delete('/bom-items/{bomItem}', [ProductWebController::class, 'removeBomItem'])->middleware('require.permission:products.edit')->name('bom.items.destroy');
        Route::delete('/products/{product}', [ProductWebController::class, 'destroy'])->middleware('require.permission:products.delete')->name('products.destroy');

        // Jasa & Layanan Management
        Route::middleware('require.permission:products.view')->group(function (): void {
            Route::get('/services', [ServiceWebController::class, 'index'])->name('services.index');
        });
        Route::post('/services', [ServiceWebController::class, 'store'])->middleware(['require.permission:products.create', 'entitlement:product'])->name('services.store');
        Route::put('/services/{product}', [ServiceWebController::class, 'update'])->middleware('require.permission:products.edit')->name('services.update');
        Route::delete('/services/{product}', [ServiceWebController::class, 'destroy'])->middleware('require.permission:products.delete')->name('services.destroy');

        // Mass Excel / CSV Import (Materials, Products, Recipes & Inventory)
        Route::middleware('require.permission:materials.view')->group(function (): void {
            Route::get('/import', [ImportWebController::class, 'index'])->name('import.index');
            Route::get('/import/materials/template', [ImportWebController::class, 'downloadMaterialTemplate'])->name('import.materials.template');
            Route::post('/import/materials/preview', [ImportWebController::class, 'previewMaterials'])->middleware('entitlement:import')->name('import.materials.preview');
            Route::post('/import/materials/execute', [ImportWebController::class, 'executeMaterials'])->middleware('entitlement:import')->name('import.materials.execute');
            Route::get('/import/products/template', [ImportWebController::class, 'downloadProductTemplate'])->name('import.products.template');
            Route::post('/import/products/preview', [ImportWebController::class, 'previewProducts'])->middleware('entitlement:import')->name('import.products.preview');
            Route::post('/import/products/execute', [ImportWebController::class, 'executeProducts'])->middleware('entitlement:import')->name('import.products.execute');
            Route::get('/import/recipes/template', [ImportWebController::class, 'downloadRecipeTemplate'])->name('import.recipes.template');
            Route::post('/import/recipes/preview', [ImportWebController::class, 'previewRecipes'])->middleware('entitlement:import')->name('import.recipes.preview');
            Route::post('/import/recipes/execute', [ImportWebController::class, 'executeRecipes'])->middleware('entitlement:import')->name('import.recipes.execute');
            Route::get('/import/inventory/template', [ImportWebController::class, 'downloadInventoryTemplate'])->name('import.inventory.template');
            Route::post('/import/inventory/preview', [ImportWebController::class, 'previewInventory'])->middleware('entitlement:import')->name('import.inventory.preview');
            Route::post('/import/inventory/execute', [ImportWebController::class, 'executeInventory'])->middleware('entitlement:import')->name('import.inventory.execute');
        });

        // Product Categories & Units Master Data
        Route::get('/product-categories', [MasterDataWebController::class, 'productCategories'])->middleware('require.permission:master_data.product_categories.view')->name('product-categories.index');
        Route::post('/product-categories', [ProductCategoryWebController::class, 'store'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.store');
        Route::put('/product-categories/{category}', [ProductCategoryWebController::class, 'update'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.update');
        Route::delete('/product-categories/{category}', [ProductCategoryWebController::class, 'destroy'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.destroy');
        Route::get('/units', [MasterDataWebController::class, 'units'])->middleware('require.permission:master_data.units.view')->name('units.index');
        Route::post('/units', [UnitWebController::class, 'store'])->middleware('require.permission:master_data.units.manage')->name('units.store');
        Route::put('/units/{unit}', [UnitWebController::class, 'update'])->middleware('require.permission:master_data.units.manage')->name('units.update');
        Route::delete('/units/{unit}', [UnitWebController::class, 'destroy'])->middleware('require.permission:master_data.units.manage')->name('units.destroy');
        Route::post('/unit-conversions', [UnitConversionWebController::class, 'store'])->middleware('require.permission:master_data.units.manage')->name('unit-conversions.store');
        Route::delete('/unit-conversions/{unitConversion}', [UnitConversionWebController::class, 'destroy'])->middleware('require.permission:master_data.units.manage')->name('unit-conversions.destroy');
        Route::post('/material-unit-conversions', [MaterialUnitConversionWebController::class, 'store'])->name('material-unit-conversions.store');
        Route::delete('/material-unit-conversions/{materialUnitConversion}', [MaterialUnitConversionWebController::class, 'destroy'])->name('material-unit-conversions.destroy');

        // Customers Management (Commercial CRM)
        Route::get('/customers', [CustomerWebController::class, 'index'])->middleware('require.permission:customers.view')->name('customers.index');
        Route::post('/customers', [CustomerWebController::class, 'store'])->middleware(['require.permission:customers.create', 'entitlement:customer'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerWebController::class, 'update'])->middleware('require.permission:customers.edit')->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerWebController::class, 'destroy'])->middleware('require.permission:customers.delete')->name('customers.destroy');

        // Purchase Orders (PO) Lifecycle & Management
        Route::middleware('require.permission:purchasing.view')->group(function (): void {
            Route::get('/purchase-orders', [PurchaseOrderWebController::class, 'index'])->name('purchase-orders.index');
            Route::get('/purchase-orders/create', [PurchaseOrderWebController::class, 'create'])->name('purchase-orders.create');
            Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderWebController::class, 'show'])->name('purchase-orders.show');
            Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderWebController::class, 'print'])->name('purchase-orders.print');
        });
        Route::post('/purchase-orders', [PurchaseOrderWebController::class, 'store'])->middleware(['require.permission:purchasing.manage', 'entitlement:po'])->name('purchase-orders.store');
        Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderWebController::class, 'confirm'])->middleware('require.permission:purchasing.manage')->name('purchase-orders.confirm');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderWebController::class, 'cancel'])->middleware('require.permission:purchasing.manage')->name('purchase-orders.cancel');
        Route::post('/purchase-orders/{purchaseOrder}/generate-invoice', [PurchaseOrderWebController::class, 'generateInvoice'])->middleware('require.permission:purchasing.manage')->name('purchase-orders.generate-invoice');
        Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderWebController::class, 'destroy'])->middleware('require.permission:purchasing.manage')->name('purchase-orders.destroy');

        // Goods Receipt (Penerimaan Barang Fisik dari PO & 1-Klik Beli ke Stok)
        Route::get('/purchasing/receipts/{purchaseOrder}/create', [GoodsReceiptWebController::class, 'create'])->middleware('require.permission:receiving.manage')->name('purchasing.receipts.create');
        Route::post('/purchasing/receipts/{purchaseOrder}', [GoodsReceiptWebController::class, 'store'])->middleware('require.permission:receiving.manage')->name('purchasing.receipts.store');
        Route::post('/purchasing/instant-stock-in', [GoodsReceiptWebController::class, 'instantStockIn'])->middleware('require.permission:receiving.manage')->name('purchasing.instant-stock-in');
        Route::middleware('require.permission:purchasing.bills')->group(function (): void {
            Route::get('/purchasing/bills', [SupplierInvoiceWebController::class, 'index'])->name('purchasing.bills.index');
            Route::get('/purchasing/bills/{invoice}', [SupplierInvoiceWebController::class, 'show'])->name('purchasing.bills.show');
            Route::post('/purchasing/bills/{invoice}/payments', [SupplierInvoiceWebController::class, 'recordPayment'])->name('purchasing.bills.payments.store');
        });

        // Sales Pipeline: Quotations & Sales Orders
        Route::get('/sales/quotations', [QuotationWebController::class, 'index'])->middleware('require.permission:sales.view')->name('sales.quotations.index');
        Route::get('/sales/quotations/create', [QuotationWebController::class, 'create'])->middleware('require.permission:sales.pipeline')->name('sales.quotations.create');
        Route::post('/sales/quotations', [QuotationWebController::class, 'store'])->middleware('require.permission:sales.pipeline')->name('sales.quotations.store');
        Route::get('/sales/quotations/{quotation}', [QuotationWebController::class, 'show'])->middleware('require.permission:sales.view')->name('sales.quotations.show');
        Route::post('/sales/quotations/{quotation}/convert', [QuotationWebController::class, 'convertToSalesOrder'])->middleware('require.permission:sales.pipeline')->name('sales.quotations.convert');
        Route::get('/sales/orders', [SalesOrderWebController::class, 'index'])->middleware('require.permission:sales.view')->name('sales.orders.index');
        Route::get('/sales/orders/create', [SalesOrderWebController::class, 'create'])->middleware('require.permission:sales.pipeline')->name('sales.orders.create');
        Route::post('/sales/orders', [SalesOrderWebController::class, 'store'])->middleware('require.permission:sales.pipeline')->name('sales.orders.store');
        Route::get('/sales/orders/{salesOrder}', [SalesOrderWebController::class, 'show'])->middleware('require.permission:sales.view')->name('sales.orders.show');
        Route::post('/sales/orders/{salesOrder}/generate-invoice', [SalesOrderWebController::class, 'generateInvoice'])->middleware('require.permission:sales.pipeline')->name('sales.orders.generate-invoice');

        // Invoices Management & Generator (Commerce Billing)
        Route::get('/invoices/create', [InvoiceWebController::class, 'create'])->middleware('require.permission:invoices.create')->name('invoices.create');
        Route::post('/invoices', [InvoiceWebController::class, 'store'])->middleware(['require.permission:invoices.create', 'entitlement:invoice'])->name('invoices.store');
        Route::middleware('require.permission:invoices.view')->group(function (): void {
            Route::get('/invoices/export-excel', [InvoiceWebController::class, 'exportExcel'])->middleware('require.permission:invoices.export')->name('invoices.export-excel');
            Route::get('/invoices', [InvoiceWebController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
            Route::get('/invoices/{invoice}/print', [InvoiceWebController::class, 'print'])->name('invoices.print');
        });
        Route::post('/invoices/{invoice}/payments', [InvoiceWebController::class, 'recordPayment'])->middleware('require.permission:invoices.record_payment')->name('invoices.payments.store');
        Route::post('/invoices/{invoice}/confirm', [InvoiceWebController::class, 'confirm'])->middleware('require.permission:invoices.create')->name('invoices.confirm');
        Route::post('/invoices/{invoice}/void', [InvoiceWebController::class, 'void'])->middleware('require.permission:invoices.edit')->name('invoices.void');
        Route::delete('/invoices/{invoice}', [InvoiceWebController::class, 'destroy'])->middleware('require.permission:invoices.delete')->name('invoices.destroy');

        // Sales Returns
        Route::middleware('require.permission:sales.returns')->group(function (): void {
            Route::get('/sales/returns', [SalesReturnWebController::class, 'index'])->name('sales.returns.index');
            Route::get('/sales/returns/create', [SalesReturnWebController::class, 'create'])->name('sales.returns.create');
            Route::post('/sales/returns', [SalesReturnWebController::class, 'store'])->name('sales.returns.store');
            Route::get('/sales/returns/{return}', [SalesReturnWebController::class, 'show'])->name('sales.returns.show');
            Route::post('/sales/returns/{return}/approve', [SalesReturnWebController::class, 'approve'])->name('sales.returns.approve');
            Route::post('/sales/returns/{return}/complete', [SalesReturnWebController::class, 'complete'])->name('sales.returns.complete');
        });

        // Purchase Returns
        Route::middleware('require.permission:purchase.returns')->group(function (): void {
            Route::get('/purchasing/returns', [PurchaseReturnWebController::class, 'index'])->name('purchase.returns.index');
            Route::get('/purchasing/returns/create', [PurchaseReturnWebController::class, 'create'])->name('purchase.returns.create');
            Route::post('/purchasing/returns', [PurchaseReturnWebController::class, 'store'])->name('purchase.returns.store');
            Route::get('/purchasing/returns/{return}', [PurchaseReturnWebController::class, 'show'])->name('purchase.returns.show');
            Route::post('/purchasing/returns/{return}/approve', [PurchaseReturnWebController::class, 'approve'])->name('purchase.returns.approve');
            Route::post('/purchasing/returns/{return}/complete', [PurchaseReturnWebController::class, 'complete'])->name('purchase.returns.complete');
        });

        // Labor Rates & Machine Costs
        Route::middleware('require.permission:labor_machines.view')->group(function (): void {
            Route::get('/labor-machines', [LaborMachineWebController::class, 'index'])->name('labor-machines.index');
        });
        Route::middleware('require.permission:costing.manage')->group(function (): void {
            Route::post('/labor-rates', [LaborMachineWebController::class, 'storeLabor'])->name('labor-rates.store');
            Route::put('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'updateLabor'])->name('labor-rates.update');
            Route::delete('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'destroyLabor'])->name('labor-rates.destroy');
            Route::post('/machines', [LaborMachineWebController::class, 'storeMachine'])->name('machines.store');
            Route::put('/machines/{machine}', [LaborMachineWebController::class, 'updateMachine'])->name('machines.update');
            Route::delete('/machines/{machine}', [LaborMachineWebController::class, 'destroyMachine'])->name('machines.destroy');
        });

        // What-If Scenario Simulator
        Route::middleware('require.permission:costing.view_margin')->group(function (): void {
            Route::get('/simulator', [SimulationWebController::class, 'index'])->name('simulator.index');
            Route::post('/simulator/{costModel}/run', [SimulationWebController::class, 'run'])->name('simulator.run');
        });

        // BEP & Profitability Analyzer
        Route::middleware('require.permission:costing.view_margin')->group(function (): void {
            Route::get('/profitability', [ProfitabilityWebController::class, 'index'])->name('profitability.index');
            Route::post('/profitability/bep', [ProfitabilityWebController::class, 'calculateBep'])->name('profitability.bep');
        });

        // Reports & Analytics Suite
        Route::get('/reports', [ReportWebController::class, 'index'])->middleware('require.permission:reports.view')->name('reports.index');
        Route::get('/reports/export-excel', [ReportWebController::class, 'exportExcel'])->middleware(['require.permission:reports.export', 'entitlement:export'])->name('reports.export-excel');

        // Business Settings (Profil Usaha, POS & Template Industri)
        Route::get('/settings', [SettingWebController::class, 'index'])->middleware('require.permission:settings.view')->name('settings.index');
        Route::put('/settings', [SettingWebController::class, 'update'])->middleware('require.permission:settings.edit')->name('settings.update');
        Route::put('/settings/modules', [SettingWebController::class, 'updateModules'])->middleware('require.role:owner')->name('settings.modules.update');
        Route::post('/settings/apply-template', [SettingWebController::class, 'applyTemplate'])->name('settings.apply-template');

        // Dedicated Role & Access Control (RBAC)
        Route::get('/roles', [RoleWebController::class, 'index'])->middleware('require.permission:roles.view')->name('roles.index');
        Route::post('/roles', [RoleWebController::class, 'store'])->middleware('require.permission:roles.manage')->name('roles.store');
        Route::put('/roles/{role}', [RoleWebController::class, 'update'])->middleware('require.permission:roles.manage')->name('roles.update');
        Route::delete('/roles/{role}', [RoleWebController::class, 'destroy'])->middleware('require.permission:roles.manage')->name('roles.destroy');
        Route::post('/roles/members', [SettingWebController::class, 'storeMember'])->middleware(['require.permission:users.manage', 'entitlement:member'])->name('roles.members.store');
        Route::put('/roles/members/{member}/role', [SettingWebController::class, 'updateMemberRole'])->middleware(['require.permission:users.manage'])->name('roles.members.role');
        Route::delete('/roles/members/{member}', [SettingWebController::class, 'destroyMember'])->middleware(['require.permission:users.manage'])->name('roles.members.destroy');

        // Legacy compatibility routes
        Route::get('/settings/roles', [RoleWebController::class, 'index'])->middleware('require.permission:roles.view')->name('settings.roles.index');
        Route::post('/settings/roles', [RoleWebController::class, 'store'])->middleware('require.permission:roles.manage')->name('settings.roles.store');
        Route::put('/settings/roles/{role}', [RoleWebController::class, 'update'])->middleware('require.permission:roles.manage')->name('settings.roles.update');
        Route::delete('/settings/roles/{role}', [RoleWebController::class, 'destroy'])->middleware('require.permission:roles.manage')->name('settings.roles.destroy');
        Route::post('/settings/members', [SettingWebController::class, 'storeMember'])->middleware(['require.permission:users.manage', 'entitlement:member'])->name('settings.members.store');
        Route::put('/settings/members/{member}/role', [SettingWebController::class, 'updateMemberRole'])->middleware(['require.permission:users.manage'])->name('settings.members.role');
        Route::delete('/settings/members/{member}', [SettingWebController::class, 'destroyMember'])->middleware(['require.permission:users.manage'])->name('settings.members.destroy');

        // Owner Feedback & Community
        Route::middleware('require.role:owner')->group(function (): void {
            Route::get('/feedback/bugs', [FeedbackWebController::class, 'bugs'])->name('feedback.bugs.index');
            Route::get('/feedback/bugs/create', [FeedbackWebController::class, 'createBug'])->name('feedback.bugs.create');
            Route::post('/feedback/bugs', [FeedbackWebController::class, 'storeBug'])->name('feedback.bugs.store');
            Route::get('/feedback/bugs/{bugReport}', [FeedbackWebController::class, 'showBug'])->name('feedback.bugs.show');
            Route::get('/feedback/features', [FeedbackWebController::class, 'features'])->name('feedback.features.index');
            Route::get('/feedback/features/create', [FeedbackWebController::class, 'createFeature'])->name('feedback.features.create');
            Route::post('/feedback/features', [FeedbackWebController::class, 'storeFeature'])->name('feedback.features.store');
            Route::get('/feedback/features/{featureRequest}', [FeedbackWebController::class, 'showFeature'])->name('feedback.features.show');

            Route::get('/community', [CommunityWebController::class, 'index'])->name('community.index');
            Route::post('/community', [CommunityWebController::class, 'store'])->name('community.store');
            Route::post('/community/{post}/like', [CommunityWebController::class, 'toggleLike'])->name('community.like');
            Route::post('/community/{post}/comment', [CommunityWebController::class, 'comment'])->name('community.comment');
            Route::delete('/community/{post}', [CommunityWebController::class, 'destroy'])->name('community.destroy');
        });

        // Dedicated SaaS Billing & Limits
        Route::get('/billing', [BillingAndLimitWebController::class, 'index'])->middleware('require.permission:billing.view')->name('billing');
        Route::get('/billing/limits', [BillingAndLimitWebController::class, 'index'])->middleware('require.permission:billing.view')->name('billing.limits');
        Route::get('/patungan', [SubscriptionCheckoutWebController::class, 'checkout'])->name('billing.patungan');
        Route::post('/billing/upgrade', [BillingAndLimitWebController::class, 'upgrade'])->name('billing.upgrade');
        Route::get('/billing/checkout', [SubscriptionCheckoutWebController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/order', [SubscriptionCheckoutWebController::class, 'store'])->name('billing.order.store');
        Route::get('/billing/payments/{payment}', [SubscriptionCheckoutWebController::class, 'payment'])->name('billing.payment.show');
        Route::get('/billing/payments/{payment}/status', [SubscriptionCheckoutWebController::class, 'checkStatus'])->name('billing.payment.status');
        Route::get('/billing/payments/{payment}/invoice', [SubscriptionCheckoutWebController::class, 'invoice'])->name('billing.payment.invoice');
        Route::post('/billing/payments/{payment}/upload-proof', [SubscriptionCheckoutWebController::class, 'uploadProof'])->name('billing.payment.upload');
        Route::get('/billing/history', [SubscriptionCheckoutWebController::class, 'history'])->name('billing.history');
        Route::post('/billing/storage/recalculate', [BillingAndLimitWebController::class, 'recalculateStorage'])->name('billing.storage.recalculate');

        // POS (Point of Sale) & Cashier Terminal
        Route::get('/pos', [PosTerminalWebController::class, 'index'])->middleware('require.permission:pos.terminal')->name('pos.terminal');
        Route::get('/pos/search-products', [PosTerminalWebController::class, 'searchProducts'])->middleware('require.permission:pos.terminal')->name('pos.search-products');
        Route::post('/pos/checkout', [PosTerminalWebController::class, 'checkout'])->middleware(['require.permission:pos.terminal', 'entitlement:pos'])->name('pos.checkout');
        Route::post('/pos/hold', [PosTerminalWebController::class, 'holdOrder'])->middleware('require.permission:pos.terminal')->name('pos.hold');
        Route::get('/pos/held-orders', [PosTerminalWebController::class, 'getHeldOrders'])->name('pos.held-orders');
        Route::post('/pos/resume/{order}', [PosTerminalWebController::class, 'resumeOrder'])->name('pos.resume');
        Route::get('/pos/receipt/{order}', [PosTerminalWebController::class, 'printReceipt'])->name('pos.receipt');
        Route::get('/pos/receipt/{order}/image', [PosTerminalWebController::class, 'receiptImage'])->name('pos.receipt.image');
        Route::post('/pos/verify-pin', [PosTerminalWebController::class, 'verifySupervisorPin'])->middleware('throttle:5,1')->name('pos.verify-pin');

        // POS Shifts
        Route::middleware('require.permission:pos.orders')->group(function (): void {
            Route::get('/pos/shifts', [PosShiftWebController::class, 'index'])->name('pos.shifts.index');
            Route::post('/pos/shifts/open', [PosShiftWebController::class, 'open'])->name('pos.shifts.open');
            Route::get('/pos/shifts/{shift}/summary', [PosShiftWebController::class, 'summary'])->name('pos.shifts.summary');
            Route::post('/pos/shifts/{shift}/close', [PosShiftWebController::class, 'close'])->name('pos.shifts.close');
            Route::post('/pos/shifts/{shift}/cash-movement', [PosShiftWebController::class, 'recordCashMovement'])->name('pos.shifts.cash-movement');
        });

        // POS Orders & Void/Refund
        Route::middleware('require.permission:pos.orders')->group(function (): void {
            Route::get('/pos/orders', [PosOrderWebController::class, 'index'])->name('pos.orders.index');
            Route::get('/pos/orders/{order}', [PosOrderWebController::class, 'show'])->name('pos.orders.show');
            Route::post('/pos/orders/{order}/sync-gateway', [PosOrderWebController::class, 'syncGatewayStatus'])->name('pos.orders.sync_gateway');
        });
        Route::post('/pos/orders/{order}/void', [PosOrderWebController::class, 'void'])->middleware('require.permission:pos.supervisor_pin,pos.orders')->name('pos.orders.void');
        Route::post('/pos/orders/{order}/refund', [PosOrderWebController::class, 'refund'])->middleware('require.permission:pos.supervisor_pin,pos.orders,sales.returns')->name('pos.orders.refund');

        // POS Reports
        Route::middleware('require.permission:pos.reports')->group(function (): void {
            Route::get('/pos/reports', [PosReportWebController::class, 'index'])->name('pos.reports.index');
            Route::get('/pos/reports/export-excel', [PosReportWebController::class, 'exportExcel'])->middleware(['require.permission:pos.reports_export', 'entitlement:export'])->name('pos.reports.export-excel');
        });

        // AI POS & Predictive Analytics
        Route::middleware('require.permission:ai.access')->group(function (): void {
            Route::get('/pos/ai', [PosAiWebController::class, 'index'])->name('pos.ai.index');
            Route::post('/pos/ai/ask', [PosAiWebController::class, 'ask'])->middleware('entitlement:ai')->name('pos.ai.ask');
            Route::post('/pos/ai/execute-action', [PosAiWebController::class, 'executeAction'])->middleware('entitlement:ai')->name('pos.ai.execute-action');
        });

        // POS Incoming Online/QR Orders
        Route::get('/pos/incoming-orders', [PosTerminalWebController::class, 'getIncomingOrders'])->name('pos.incoming-orders');
        Route::post('/pos/incoming-orders/{order}/accept', [PosTerminalWebController::class, 'acceptIncomingOrder'])->name('pos.incoming-orders.accept');
        Route::post('/pos/incoming-orders/{order}/reject', [PosTerminalWebController::class, 'rejectIncomingOrder'])->name('pos.incoming-orders.reject');
        Route::get('/pos/tables/{table}/details', [PosTerminalWebController::class, 'getTableDetails'])->name('pos.tables.details');
        Route::post('/pos/orders/{order}/pay-table', [PosTerminalWebController::class, 'payTableOrder'])->name('pos.orders.pay-table');

        // Table Management
        Route::middleware('require.permission:pos.tables')->group(function (): void {
            Route::get('/pos/tables', [PosTableWebController::class, 'index'])->name('pos.tables.index');
            Route::get('/pos/tables/qr-cards', [PosTableWebController::class, 'allQrCards'])->name('pos.tables.qr-cards');
            Route::post('/pos/tables', [PosTableWebController::class, 'store'])->name('pos.tables.store');
            Route::put('/pos/tables/{table}', [PosTableWebController::class, 'update'])->name('pos.tables.update');
            Route::delete('/pos/tables/{table}', [PosTableWebController::class, 'destroy'])->name('pos.tables.destroy');
            Route::post('/pos/tables/{table}/regenerate-qr', [PosTableWebController::class, 'regenerateQr'])->name('pos.tables.regenerate-qr');
            Route::get('/pos/tables/{table}/qr-card', [PosTableWebController::class, 'qrCard'])->name('pos.tables.qr-card');
            Route::get('/pos/tables/{table}/qr-svg', [PosTableWebController::class, 'downloadSvg'])->name('pos.tables.qr-svg');
            Route::post('/pos/sessions/{session}/close', [PosTableWebController::class, 'closeSession'])->name('pos.sessions.close');
        });

        // Modifiers & Add-ons
        Route::middleware('require.permission:pos.modifiers')->group(function (): void {
            Route::get('/pos/modifiers', [ModifierWebController::class, 'index'])->name('pos.modifiers.index');
            Route::post('/pos/modifiers/groups', [ModifierWebController::class, 'storeGroup'])->name('pos.modifiers.groups.store');
            Route::put('/pos/modifiers/groups/{group}', [ModifierWebController::class, 'updateGroup'])->name('pos.modifiers.groups.update');
            Route::delete('/pos/modifiers/groups/{group}', [ModifierWebController::class, 'destroyGroup'])->name('pos.modifiers.groups.destroy');
            Route::post('/pos/modifiers/groups/{group}/options', [ModifierWebController::class, 'storeOption'])->name('pos.modifiers.options.store');
            Route::put('/pos/modifiers/options/{option}', [ModifierWebController::class, 'updateOption'])->name('pos.modifiers.options.update');
            Route::delete('/pos/modifiers/options/{option}', [ModifierWebController::class, 'destroyOption'])->name('pos.modifiers.options.destroy');
        });

        // Kitchen & Bar Display (KDS)
        Route::middleware('require.permission:pos.kitchen')->group(function (): void {
            Route::get('/pos/kitchen', [PosKitchenWebController::class, 'index'])->name('pos.kitchen.index');
            Route::get('/pos/kitchen/orders', [PosKitchenWebController::class, 'getActiveOrders'])->name('pos.kitchen.orders');
            Route::get('/pos/kitchen/active', [PosKitchenWebController::class, 'getActiveOrders'])->name('pos.kitchen.active');
            Route::post('/pos/kitchen/{order}/status', [PosKitchenWebController::class, 'updateStatus'])->name('pos.kitchen.status');
        });

        // Warehouse Management Hub
        Route::get('/warehouse', [WarehouseWebController::class, 'index'])->middleware('require.permission:inventory.view')->name('warehouse.index');
        Route::post('/warehouse', [WarehouseWebController::class, 'store'])->middleware(['require.permission:inventory.manage', 'entitlement:warehouse'])->name('warehouse.store');
        Route::get('/warehouse/{location}', [WarehouseWebController::class, 'show'])->middleware('require.permission:inventory.view')->name('warehouse.show');
        Route::put('/warehouse/{location}', [WarehouseWebController::class, 'update'])->middleware('require.permission:inventory.manage')->name('warehouse.update');
        Route::delete('/warehouse/{location}', [WarehouseWebController::class, 'destroy'])->middleware('require.permission:inventory.manage')->name('warehouse.destroy');

        // Inventory & Multi-Warehouse Operations
        Route::middleware('require.permission:inventory.view')->group(function (): void {
            Route::get('/inventory/stocks', [InventoryWebController::class, 'stocks'])->name('inventory.stocks');
            Route::get('/inventory/movements', [InventoryWebController::class, 'movements'])->name('inventory.movements');
            Route::get('/inventory/opnames', [InventoryWebController::class, 'opnames'])->name('inventory.opnames.index');
            Route::get('/inventory/transfers', [InventoryWebController::class, 'transfers'])->name('inventory.transfers.index');
        });
        Route::post('/inventory/stocks/adjust', [InventoryWebController::class, 'quickAdjust'])->middleware('require.permission:inventory.manage')->name('inventory.stocks.adjust');
        Route::post('/inventory/opnames', [InventoryWebController::class, 'storeOpname'])->middleware('require.permission:inventory.manage')->name('inventory.opnames.store');
        Route::post('/inventory/opnames/{opname}/reconcile', [InventoryWebController::class, 'reconcileOpname'])->middleware('require.permission:inventory.manage')->name('inventory.opnames.reconcile');
        Route::post('/inventory/transfers', [InventoryWebController::class, 'storeTransfer'])->middleware('require.permission:inventory.manage')->name('inventory.transfers.store');
        Route::post('/inventory/transfers/{transfer}/receive', [InventoryWebController::class, 'receiveTransfer'])->middleware('require.permission:inventory.manage')->name('inventory.transfers.receive');

        // CRM & Loyalty
        Route::middleware('require.permission:crm.view')->group(function (): void {
            Route::get('/crm/members', [CrmWebController::class, 'members'])->name('crm.members.index');
            Route::get('/crm/customers/{customer}/points', [CrmWebController::class, 'pointHistories'])->name('crm.customers.points');
            Route::get('/crm/vouchers', [CrmWebController::class, 'vouchers'])->name('crm.vouchers.index');
        });
        Route::post('/crm/customers/{customer}/credit-payment', [CrmWebController::class, 'recordCreditPayment'])->middleware('require.permission:crm.manage')->name('crm.customers.credit-payment');
        Route::post('/crm/vouchers', [CrmWebController::class, 'storeVoucher'])->middleware('require.permission:crm.manage')->name('crm.vouchers.store');
        Route::post('/crm/vouchers/{voucher}/toggle', [CrmWebController::class, 'toggleVoucher'])->middleware('require.permission:crm.manage')->name('crm.vouchers.toggle');

        // Finance & Automated Journals
        Route::get('/finance/journals', [PosFinanceWebController::class, 'journals'])->middleware('require.permission:accounting.view')->name('finance.journals.index');
        Route::get('/finance/expenses', [PosFinanceWebController::class, 'expenses'])->middleware('require.permission:expenses.view')->name('finance.expenses.index');
        Route::post('/finance/expenses', [PosFinanceWebController::class, 'storeExpense'])->middleware('require.permission:expenses.manage')->name('finance.expenses.store');
        Route::middleware('require.permission:finance.cash_bank')->group(function (): void {
            Route::get('/finance/cash-bank', [CashLedgerWebController::class, 'index'])->name('finance.cash-bank.index');
            Route::get('/finance/cash-bank/ledger', [CashLedgerWebController::class, 'ledger'])->name('finance.cash-bank.ledger');
            Route::post('/finance/cash-bank/inflow', [CashLedgerWebController::class, 'storeInflow'])->name('finance.cash-bank.inflow');
            Route::post('/finance/cash-bank/outflow', [CashLedgerWebController::class, 'storeOutflow'])->name('finance.cash-bank.outflow');
            Route::post('/finance/cash-bank/transfer', [CashLedgerWebController::class, 'transfer'])->name('finance.cash-bank.transfer');

            // Payment Gateway Settlement & Reconciliation
            Route::get('/finance/settlements', [PaymentSettlementWebController::class, 'index'])->name('finance.settlements.index');
            Route::get('/finance/settlements/unsettled', [PaymentSettlementWebController::class, 'getUnsettled'])->name('finance.settlements.unsettled');
            Route::post('/finance/settlements/reconcile', [PaymentSettlementWebController::class, 'reconcile'])->name('finance.settlements.reconcile');
            Route::get('/finance/settlements/{settlement}', [PaymentSettlementWebController::class, 'show'])->name('finance.settlements.show');
        });
        Route::get('/finance/receivables', [CashLedgerWebController::class, 'receivables'])->middleware('require.permission:finance.receivables')->name('finance.receivables');
        Route::get('/finance/payables', [CashLedgerWebController::class, 'payables'])->middleware('require.permission:finance.payables')->name('finance.payables');

        // WhatsApp Gateway Toko
        Route::prefix('whatsapp')->name('whatsapp.')->middleware('require.permission:whatsapp.view')->group(function (): void {
            Route::get('/', [WhatsAppWebController::class, 'index'])->name('index');
            Route::get('/qr', [WhatsAppWebController::class, 'getQr'])->name('qr');
            Route::get('/status', [WhatsAppWebController::class, 'checkStatus'])->name('status');
            Route::get('/logs', [WhatsAppWebController::class, 'logs'])->name('logs.index');
            Route::post('/orders/{order}/receipt', [WhatsAppWebController::class, 'sendOrderReceipt'])->name('orders.receipt');

            Route::middleware('require.permission:whatsapp.manage')->group(function (): void {
                Route::post('/start', [WhatsAppWebController::class, 'startSession'])->name('start');
                Route::post('/disconnect', [WhatsAppWebController::class, 'disconnect'])->name('disconnect');
                Route::post('/settings', [WhatsAppWebController::class, 'updateSettings'])->name('settings');
                Route::post('/test', [WhatsAppWebController::class, 'testSend'])->name('test');
                Route::post('/verify-meta', [WhatsAppWebController::class, 'verifyMetaCredentials'])->name('verify-meta');
            });

            // Meta WhatsApp Cloud API Official Embedded Signup & Onboarding
            Route::prefix('meta')->name('meta.')->group(function (): void {
                Route::get('/config', [MetaWhatsAppOnboardingController::class, 'getSignupConfig'])->name('config');
                Route::get('/status', [MetaWhatsAppOnboardingController::class, 'getStatus'])->name('status');

                Route::middleware('require.permission:whatsapp.manage')->group(function (): void {
                    Route::post('/exchange-code', [MetaWhatsAppOnboardingController::class, 'exchangeCode'])->name('exchange-code');
                    Route::post('/disconnect', [MetaWhatsAppOnboardingController::class, 'disconnect'])->name('disconnect');
                });
            });

            // WhatsApp Broadcast Promosi
            Route::middleware('require.permission:whatsapp.manage')->group(function (): void {
                Route::get('/broadcast', [WhatsAppBroadcastWebController::class, 'index'])->name('broadcast.index');
                Route::get('/broadcast/create', [WhatsAppBroadcastWebController::class, 'create'])->name('broadcast.create');
                Route::get('/broadcast/estimate', [WhatsAppBroadcastWebController::class, 'estimateRecipients'])->name('broadcast.estimate');
                Route::post('/broadcast', [WhatsAppBroadcastWebController::class, 'store'])->middleware('entitlement:whatsapp')->name('broadcast.store');
                Route::get('/broadcast/{campaign}', [WhatsAppBroadcastWebController::class, 'show'])->name('broadcast.show');
            });
        });

        // Integrasi Media Sosial (Meta Facebook, Instagram, Threads, & TikTok)
        Route::prefix('social-media')->name('social-media.')->middleware('require.permission:whatsapp.view')->group(function (): void {
            Route::get('/', [SocialMediaWebController::class, 'index'])->name('index');
            Route::get('/config', [SocialMediaWebController::class, 'getOAuthConfig'])->name('config');
            Route::post('/exchange-token', [SocialMediaWebController::class, 'exchangeToken'])->name('exchange-token');
            Route::post('/disconnect', [SocialMediaWebController::class, 'disconnect'])->name('disconnect');

            // TikTok OAuth 2.0 Connect & Callback
            Route::get('/tiktok/connect', [SocialMediaWebController::class, 'getTikTokAuthUrl'])->name('tiktok.connect');
            Route::get('/tiktok/callback', [SocialMediaWebController::class, 'handleTikTokCallback'])->name('tiktok.callback');

            // Posts, Targets & Publishing
            Route::get('/posts', [SocialMediaWebController::class, 'posts'])->name('posts.index');
            Route::post('/posts', [SocialMediaWebController::class, 'storePost'])->middleware('entitlement:social_post')->name('posts.store');
            Route::post('/targets/{target}/retry', [SocialMediaWebController::class, 'retryTarget'])->name('targets.retry');
            Route::get('/calendar', [SocialMediaWebController::class, 'calendar'])->name('calendar');

            // Comments & Inbox
            Route::get('/inbox', [SocialMediaWebController::class, 'inbox'])->name('inbox.index');
            Route::post('/comments/{comment}/reply', [SocialMediaWebController::class, 'replyComment'])->name('comments.reply');

            // Analytics & Insights
            Route::get('/insights', [SocialMediaWebController::class, 'insights'])->name('insights.index');
            Route::post('/insights/{post}/sync', [SocialMediaWebController::class, 'syncInsights'])->name('insights.sync');
        });

        // Business Landing Page & Mini Website CMS
        Route::prefix('landing-page')->name('landing-page.')->middleware('require.permission:cms.manage')->group(function (): void {
            Route::get('/', [BusinessLandingPageWebController::class, 'edit'])->name('edit');
            Route::put('/', [BusinessLandingPageWebController::class, 'update'])->name('update');
            Route::post('/preset', [BusinessLandingPageWebController::class, 'applyPreset'])->name('preset');
            Route::post('/toggle-publish', [BusinessLandingPageWebController::class, 'togglePublish'])->name('toggle-publish');
        });

        // Omnichannel Storefront & Order Management
        Route::prefix('storefront')->name('storefront.')->group(function (): void {
            Route::get('/orders', [MerchantOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [MerchantOrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{order}/shipping-label', [MerchantOrderController::class, 'shippingLabel'])->name('orders.shipping_label');
            Route::post('/orders/{order}/waybill', [MerchantOrderController::class, 'updateWaybill'])->name('orders.waybill.update');
            Route::post('/orders/{order}/verify-payment', [MerchantOrderController::class, 'verifyPayment'])->name('orders.verify_payment');
            Route::post('/orders/{order}/reject-payment', [MerchantOrderController::class, 'rejectPayment'])->name('orders.reject_payment');
            Route::post('/orders/{order}/status', [MerchantOrderController::class, 'updateStatus'])->name('orders.update_status');
            Route::post('/orders/{order}/quote', [MerchantOrderController::class, 'quoteRequestOrder'])->name('orders.quote');
            Route::post('/orders/{order}/batches/{batch}/status', [MerchantOrderController::class, 'updateBatchStatus'])->name('orders.batches.status');
            Route::post('/orders/{order}/sync-gateway', [MerchantOrderController::class, 'syncGatewayStatus'])->name('orders.sync_gateway');
            Route::post('/orders/{order}/biteship/create', [MerchantOrderController::class, 'createBiteshipOrder'])->name('orders.biteship.create');
            Route::post('/orders/{order}/biteship/track', [MerchantOrderController::class, 'trackBiteshipOrder'])->name('orders.biteship.track');
            Route::post('/orders/{order}/biteship/cancel', [MerchantOrderController::class, 'cancelBiteshipOrder'])->name('orders.biteship.cancel');
            Route::get('/proofs/{proof}/stream', [MerchantOrderController::class, 'streamProof'])->name('proofs.stream');

            Route::get('/shipping', [MerchantShippingRuleController::class, 'index'])->name('shipping.index');
            Route::post('/shipping/origin', [MerchantShippingRuleController::class, 'saveOrigin'])->name('shipping.origin.save');
            Route::post('/shipping/test-rate', [MerchantShippingRuleController::class, 'testRate'])->name('shipping.test_rate');
            Route::get('/shipping/search-areas', [MerchantShippingRuleController::class, 'searchAreas'])->name('shipping.search_areas');
            Route::post('/shipping', [MerchantShippingRuleController::class, 'store'])->name('shipping.store');
            Route::put('/shipping/{shippingRule}', [MerchantShippingRuleController::class, 'update'])->name('shipping.update');
            Route::post('/shipping/{shippingRule}/toggle', [MerchantShippingRuleController::class, 'toggle'])->name('shipping.toggle');
            Route::delete('/shipping/{shippingRule}', [MerchantShippingRuleController::class, 'destroy'])->name('shipping.destroy');

            Route::get('/reservations', [MerchantReservationController::class, 'index'])->name('reservations.index');
            Route::post('/reservations/{reservation}/status', [MerchantReservationController::class, 'updateStatus'])->name('reservations.status');
            Route::post('/reservations/{reservation}/assign-table', [MerchantReservationController::class, 'assignTable'])->name('reservations.assign_table');

            Route::get('/settings', [MerchantStoreSettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [MerchantStoreSettingController::class, 'update'])->name('settings.update');
            Route::post('/settings/payment-methods', [MerchantStoreSettingController::class, 'storePaymentMethod'])->name('settings.payment_methods.store');
            Route::post('/settings/payment-methods/{paymentMethod}/toggle', [MerchantStoreSettingController::class, 'togglePaymentMethod'])->name('settings.payment_methods.toggle');
            Route::delete('/settings/payment-methods/{paymentMethod}', [MerchantStoreSettingController::class, 'deletePaymentMethod'])->name('settings.payment_methods.destroy');
        });
    });
});
