<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CalculatorWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\LaborMachineWebController;
use App\Http\Controllers\Web\MaterialWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\ProfitabilityWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\SettingWebController;
use App\Http\Controllers\Web\SimulationWebController;
use App\Http\Controllers\Admin\AdminLeadController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Web\PublicBlogController;
use App\Http\Controllers\Web\PublicCalculatorController;
use App\Http\Controllers\Web\PublicContactController;
use App\Http\Controllers\Web\PublicSolutionController;
use App\Http\Controllers\Web\PublicTemplateController;
use App\Http\Controllers\Web\Warehouse\WarehouseWebController;
use App\Http\Controllers\Web\WhatsApp\WhatsAppWebController;
use App\Http\Controllers\Web\WhatsApp\WhatsAppBroadcastWebController;
use App\Http\Controllers\Web\BusinessLandingPageWebController;
use App\Http\Controllers\Web\PublicBusinessLandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Landing Page & Multipage Ecosystem
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('landing');
})->name('landing');

// 1. Tools / Kalkulator Bisnis
Route::prefix('kalkulator')->name('kalkulator.')->group(function (): void {
    Route::get('/', [PublicCalculatorController::class, 'index'])->name('index');
    Route::get('/hpp', [PublicCalculatorController::class, 'hpp'])->name('hpp');
    Route::get('/bep', [PublicCalculatorController::class, 'bep'])->name('bep');
    Route::get('/harga-jual', [PublicCalculatorController::class, 'hargaJual'])->name('harga-jual');
    Route::get('/laba-bersih', [PublicCalculatorController::class, 'labaBersih'])->name('laba-bersih');
    Route::get('/gaji-karyawan', [PublicCalculatorController::class, 'gajiKaryawan'])->name('gaji-karyawan');
    Route::get('/pph-final', [PublicCalculatorController::class, 'pphFinal'])->name('pph-final');
    Route::get('/omzet-harian', [PublicCalculatorController::class, 'omzetHarian'])->name('omzet-harian');
    Route::get('/simulasi-what-if', [PublicCalculatorController::class, 'simulasiWhatIf'])->name('simulasi-what-if');
});

// Alias direct slug URLs for SEO convenience
Route::get('/kalkulator-hpp', [PublicCalculatorController::class, 'hpp']);
Route::get('/kalkulator-bep', [PublicCalculatorController::class, 'bep']);
Route::get('/kalkulator-harga-jual', [PublicCalculatorController::class, 'hargaJual']);
Route::get('/kalkulator-laba-bersih', [PublicCalculatorController::class, 'labaBersih']);
Route::get('/kalkulator-gaji-karyawan', [PublicCalculatorController::class, 'gajiKaryawan']);
Route::get('/kalkulator-pph-final', [PublicCalculatorController::class, 'pphFinal']);
Route::get('/kalkulator-omzet-harian', [PublicCalculatorController::class, 'omzetHarian']);
Route::get('/simulasi-what-if', [PublicCalculatorController::class, 'simulasiWhatIf']);

// 2. Template / Resource Downloads (Lead Capture)
Route::get('/template-pembukuan-gratis', [PublicTemplateController::class, 'index'])->name('template.index');
Route::get('/template/{slug}', [PublicTemplateController::class, 'show'])->name('template.show');
Route::post('/template/{slug}/download', [PublicTemplateController::class, 'captureLead'])->name('template.download');

// 3. Solusi per Vertikal Niche UMKM
Route::get('/solusi/{slug}', [PublicSolutionController::class, 'show'])->name('solusi.show');

// 4. Blog & Edukasi Bisnis
Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [PublicBlogController::class, 'show'])->name('blog.show');

// 5. Halaman Kontak
Route::get('/kontak', [PublicContactController::class, 'show'])->name('contact');
Route::post('/kontak', [PublicContactController::class, 'submit'])->name('contact.submit');

// 6. Public Business Single-Page Landing Pages
Route::get('/b/{slug}', [PublicBusinessLandingController::class, 'show'])->name('public.business.landing');

// 7. Public Customer QR Table Ordering
Route::get('/t/{qrToken}', [\App\Http\Controllers\Web\Pos\PublicQrOrderWebController::class, 'showMenu'])->name('public.qr.menu');
Route::post('/t/{qrToken}/order', [\App\Http\Controllers\Web\Pos\PublicQrOrderWebController::class, 'submitOrder'])->name('public.qr.order');
Route::get('/t/{qrToken}/order/{order}/track', [\App\Http\Controllers\Web\Pos\PublicQrOrderWebController::class, 'trackOrder'])->name('public.qr.track');
Route::get('/b/{slug}/table/{qrToken}', [\App\Http\Controllers\Web\Pos\PublicQrOrderWebController::class, 'showMenu'])->name('public.qr.menu.slug');
Route::post('/b/{slug}/table/{qrToken}/order', [\App\Http\Controllers\Web\Pos\PublicQrOrderWebController::class, 'submitOrder'])->name('public.qr.order.slug');

// 8. Sitemap XML & HTML (SEO & Web Crawlers)
Route::get('/sitemap.xml', [\App\Http\Controllers\Web\SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/sitemap', [\App\Http\Controllers\Web\SitemapController::class, 'html'])->name('sitemap.html');

/*
|--------------------------------------------------------------------------
| User Google OAuth Routes
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| User Web Authentication Routes (Guard: web)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:web')->group(function (): void {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register']);

    // Password Reset Flow
    Route::get('/forgot-password', [\App\Http\Controllers\Web\PasswordResetWebController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Web\PasswordResetWebController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Web\PasswordResetWebController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Web\PasswordResetWebController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Workspace Panel (Guard: web)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function (): void {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    Route::get('/email/verify', [\App\Http\Controllers\Web\EmailVerificationWebController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Web\EmailVerificationWebController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Web\EmailVerificationWebController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');

    // User Profile & Password Management
    Route::get('/profile', [\App\Http\Controllers\Web\ProfileWebController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\ProfileWebController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Web\ProfileWebController::class, 'updatePassword'])->name('profile.password');

    // Complete Profile Onboarding
    Route::get('/complete-profile', [AuthWebController::class, 'showCompleteProfile'])->name('profile.complete');
    Route::post('/complete-profile', [AuthWebController::class, 'updateCompleteProfile'])->name('profile.complete.save');

    // Tenant Switcher & Creation
    Route::get('/select-business', [AuthWebController::class, 'selectBusiness'])->name('businesses.select');
    Route::post('/select-business', [AuthWebController::class, 'switchBusiness'])->name('businesses.switch');
    Route::post('/businesses', [AuthWebController::class, 'storeBusiness'])->name('businesses.store');

    // Onboarding / Guided Product Tour Persistence Routes
    Route::get('/onboarding/status', [\App\Http\Controllers\Web\OnboardingWebController::class, 'status'])->name('onboarding.status');
    Route::post('/onboarding/step', [\App\Http\Controllers\Web\OnboardingWebController::class, 'updateStep'])->name('onboarding.step');
    Route::post('/onboarding/complete', [\App\Http\Controllers\Web\OnboardingWebController::class, 'complete'])->name('onboarding.complete');
    Route::post('/onboarding/restart', [\App\Http\Controllers\Web\OnboardingWebController::class, 'restart'])->name('onboarding.restart');

    // Tenant Protected Web Panel
    Route::middleware(['business.active', 'profile.complete'])->group(function (): void {
        // Executive Dashboard & Zero-Navigation Quick Actions
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
        Route::get('/materials', [MaterialWebController::class, 'index'])->name('materials.index');
        Route::post('/materials', [MaterialWebController::class, 'store'])->middleware('entitlement:material')->name('materials.store');
        Route::put('/materials/{material}', [MaterialWebController::class, 'update'])->name('materials.update');
        Route::post('/materials/{material}/prices', [MaterialWebController::class, 'storePrice'])->name('materials.store-price');
        Route::delete('/materials/{material}', [MaterialWebController::class, 'destroy'])->name('materials.destroy');

        // Supplier & Material Category Master Data (CMS)
        Route::get('/suppliers', [\App\Http\Controllers\Web\SupplierWebController::class, 'index'])->middleware('require.permission:master_data.suppliers.view')->name('suppliers.index');
        Route::post('/suppliers', [\App\Http\Controllers\Web\SupplierWebController::class, 'store'])->middleware(['require.permission:master_data.suppliers.manage', 'entitlement:supplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [\App\Http\Controllers\Web\SupplierWebController::class, 'update'])->middleware('require.permission:master_data.suppliers.manage')->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\Web\SupplierWebController::class, 'destroy'])->middleware('require.permission:master_data.suppliers.manage')->name('suppliers.destroy');
        Route::get('/material-categories', [\App\Http\Controllers\Web\MasterDataWebController::class, 'materialCategories'])->middleware('require.permission:master_data.material_categories.view')->name('material-categories.index');
        Route::post('/material-categories', [\App\Http\Controllers\Web\MaterialCategoryWebController::class, 'store'])->middleware('require.permission:master_data.material_categories.manage')->name('material-categories.store');
        Route::put('/material-categories/{category}', [\App\Http\Controllers\Web\MaterialCategoryWebController::class, 'update'])->middleware('require.permission:master_data.material_categories.manage')->name('material-categories.update');
        Route::delete('/material-categories/{category}', [\App\Http\Controllers\Web\MaterialCategoryWebController::class, 'destroy'])->middleware('require.permission:master_data.material_categories.manage')->name('material-categories.destroy');

        // Products & BOM Management
        Route::get('/products', [ProductWebController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductWebController::class, 'store'])->middleware('entitlement:product')->name('products.store');
        Route::post('/products/toggle-pos-images', [ProductWebController::class, 'togglePosImageVisibility'])->name('products.toggle-pos-images');
        Route::put('/products/{product}', [ProductWebController::class, 'update'])->name('products.update');
        Route::get('/products/{product}/bom', [ProductWebController::class, 'bom'])->name('products.bom');
        Route::post('/bom-headers/{bomHeader}/items', [ProductWebController::class, 'addBomItem'])->middleware('entitlement:recipe')->name('bom.items.store');
        Route::delete('/bom-items/{bomItem}', [ProductWebController::class, 'removeBomItem'])->name('bom.items.destroy');
        Route::delete('/products/{product}', [ProductWebController::class, 'destroy'])->name('products.destroy');

        // Mass Excel / CSV Import (Materials, Products & Recipes) - Entitlement Pro / Patungan / Core
        Route::get('/import', [\App\Http\Controllers\Web\ImportWebController::class, 'index'])->name('import.index');
        Route::get('/import/materials/template', [\App\Http\Controllers\Web\ImportWebController::class, 'downloadMaterialTemplate'])->name('import.materials.template');
        Route::post('/import/materials/preview', [\App\Http\Controllers\Web\ImportWebController::class, 'previewMaterials'])->middleware('entitlement:import')->name('import.materials.preview');
        Route::post('/import/materials/execute', [\App\Http\Controllers\Web\ImportWebController::class, 'executeMaterials'])->middleware('entitlement:import')->name('import.materials.execute');
        Route::get('/import/products/template', [\App\Http\Controllers\Web\ImportWebController::class, 'downloadProductTemplate'])->name('import.products.template');
        Route::post('/import/products/preview', [\App\Http\Controllers\Web\ImportWebController::class, 'previewProducts'])->middleware('entitlement:import')->name('import.products.preview');
        Route::post('/import/products/execute', [\App\Http\Controllers\Web\ImportWebController::class, 'executeProducts'])->middleware('entitlement:import')->name('import.products.execute');
        Route::get('/import/recipes/template', [\App\Http\Controllers\Web\ImportWebController::class, 'downloadRecipeTemplate'])->name('import.recipes.template');
        Route::post('/import/recipes/preview', [\App\Http\Controllers\Web\ImportWebController::class, 'previewRecipes'])->middleware('entitlement:import')->name('import.recipes.preview');
        Route::post('/import/recipes/execute', [\App\Http\Controllers\Web\ImportWebController::class, 'executeRecipes'])->middleware('entitlement:import')->name('import.recipes.execute');
        Route::get('/import/inventory/template', [\App\Http\Controllers\Web\ImportWebController::class, 'downloadInventoryTemplate'])->name('import.inventory.template');
        Route::post('/import/inventory/preview', [\App\Http\Controllers\Web\ImportWebController::class, 'previewInventory'])->middleware('entitlement:import')->name('import.inventory.preview');
        Route::post('/import/inventory/execute', [\App\Http\Controllers\Web\ImportWebController::class, 'executeInventory'])->middleware('entitlement:import')->name('import.inventory.execute');

        // Product Categories & Units Master Data (CMS)
        Route::get('/product-categories', [\App\Http\Controllers\Web\MasterDataWebController::class, 'productCategories'])->middleware('require.permission:master_data.product_categories.view')->name('product-categories.index');
        Route::post('/product-categories', [\App\Http\Controllers\Web\ProductCategoryWebController::class, 'store'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.store');
        Route::put('/product-categories/{category}', [\App\Http\Controllers\Web\ProductCategoryWebController::class, 'update'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.update');
        Route::delete('/product-categories/{category}', [\App\Http\Controllers\Web\ProductCategoryWebController::class, 'destroy'])->middleware('require.permission:master_data.product_categories.manage')->name('product-categories.destroy');
        Route::get('/units', [\App\Http\Controllers\Web\MasterDataWebController::class, 'units'])->middleware('require.permission:master_data.units.view')->name('units.index');
        Route::post('/units', [\App\Http\Controllers\Web\UnitWebController::class, 'store'])->middleware('require.permission:master_data.units.manage')->name('units.store');
        Route::put('/units/{unit}', [\App\Http\Controllers\Web\UnitWebController::class, 'update'])->middleware('require.permission:master_data.units.manage')->name('units.update');
        Route::delete('/units/{unit}', [\App\Http\Controllers\Web\UnitWebController::class, 'destroy'])->middleware('require.permission:master_data.units.manage')->name('units.destroy');
        Route::post('/unit-conversions', [\App\Http\Controllers\Web\UnitConversionWebController::class, 'store'])->middleware('require.permission:master_data.units.manage')->name('unit-conversions.store');
        Route::delete('/unit-conversions/{unitConversion}', [\App\Http\Controllers\Web\UnitConversionWebController::class, 'destroy'])->middleware('require.permission:master_data.units.manage')->name('unit-conversions.destroy');
        Route::post('/material-unit-conversions', [\App\Http\Controllers\Web\MaterialUnitConversionWebController::class, 'store'])->name('material-unit-conversions.store');
        Route::delete('/material-unit-conversions/{materialUnitConversion}', [\App\Http\Controllers\Web\MaterialUnitConversionWebController::class, 'destroy'])->name('material-unit-conversions.destroy');

        // Customers Management (Commercial CRM)
        Route::get('/customers', [\App\Http\Controllers\Web\CustomerWebController::class, 'index'])->name('customers.index');
        Route::post('/customers', [\App\Http\Controllers\Web\CustomerWebController::class, 'store'])->middleware('entitlement:customer')->name('customers.store');
        Route::put('/customers/{customer}', [\App\Http\Controllers\Web\CustomerWebController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [\App\Http\Controllers\Web\CustomerWebController::class, 'destroy'])->name('customers.destroy');

        // Purchase Orders (PO) Lifecycle & Management
        Route::get('/purchase-orders', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'store'])->middleware('entitlement:po')->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/print', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'print'])->name('purchase-orders.print');
        Route::post('/purchase-orders/{purchaseOrder}/confirm', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'confirm'])->middleware('require.permission:purchasing.manage')->name('purchase-orders.confirm');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'cancel'])->middleware('require.permission:purchasing.manage')->name('purchase-orders.cancel');
        Route::post('/purchase-orders/{purchaseOrder}/generate-invoice', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'generateInvoice'])->name('purchase-orders.generate-invoice');
        Route::delete('/purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'destroy'])->name('purchase-orders.destroy');

        // Goods Receipt (Penerimaan Barang Fisik dari PO & 1-Klik Beli ke Stok)
        Route::get('/purchasing/receipts/{purchaseOrder}/create', [\App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController::class, 'create'])->middleware('require.permission:receiving.manage')->name('purchasing.receipts.create');
        Route::post('/purchasing/receipts/{purchaseOrder}', [\App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController::class, 'store'])->middleware('require.permission:receiving.manage')->name('purchasing.receipts.store');
        Route::post('/purchasing/instant-stock-in', [\App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController::class, 'instantStockIn'])->middleware('require.permission:receiving.manage')->name('purchasing.instant-stock-in');
        Route::get('/purchasing/bills', [\App\Http\Controllers\Web\Purchasing\SupplierInvoiceWebController::class, 'index'])->name('purchasing.bills.index');
        Route::get('/purchasing/bills/{invoice}', [\App\Http\Controllers\Web\Purchasing\SupplierInvoiceWebController::class, 'show'])->name('purchasing.bills.show');
        Route::post('/purchasing/bills/{invoice}/payments', [\App\Http\Controllers\Web\Purchasing\SupplierInvoiceWebController::class, 'recordPayment'])->name('purchasing.bills.payments.store');

        // Sales Pipeline: Quotations (Surat Penawaran Harga)
        Route::get('/sales/quotations', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'index'])->name('sales.quotations.index');
        Route::get('/sales/quotations/create', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'create'])->name('sales.quotations.create');
        Route::post('/sales/quotations', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'store'])->name('sales.quotations.store');
        Route::get('/sales/quotations/{quotation}', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'show'])->name('sales.quotations.show');
        Route::post('/sales/quotations/{quotation}/convert', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'convertToSalesOrder'])->name('sales.quotations.convert');

        // Sales Pipeline: Sales Orders (Pesanan Penjualan)
        Route::get('/sales/orders', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'index'])->name('sales.orders.index');
        Route::get('/sales/orders/create', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'create'])->name('sales.orders.create');
        Route::post('/sales/orders', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'store'])->name('sales.orders.store');
        Route::get('/sales/orders/{salesOrder}', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'show'])->name('sales.orders.show');
        Route::post('/sales/orders/{salesOrder}/generate-invoice', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'generateInvoice'])->name('sales.orders.generate-invoice');

        // Invoices Management & Generator (Commerce Billing)
        Route::get('/invoices/export-excel', [\App\Http\Controllers\Web\InvoiceWebController::class, 'exportExcel'])->name('invoices.export-excel');
        Route::get('/invoices', [\App\Http\Controllers\Web\InvoiceWebController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [\App\Http\Controllers\Web\InvoiceWebController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [\App\Http\Controllers\Web\InvoiceWebController::class, 'store'])->middleware('entitlement:invoice')->name('invoices.store');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Web\InvoiceWebController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\Web\InvoiceWebController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\Web\InvoiceWebController::class, 'recordPayment'])->middleware('require.permission:invoices.record_payment')->name('invoices.payments.store');
        Route::post('/invoices/{invoice}/confirm', [\App\Http\Controllers\Web\InvoiceWebController::class, 'confirm'])->middleware('require.permission:invoices.create')->name('invoices.confirm');
        Route::post('/invoices/{invoice}/void', [\App\Http\Controllers\Web\InvoiceWebController::class, 'void'])->middleware('require.permission:invoices.edit')->name('invoices.void');
        Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Web\InvoiceWebController::class, 'destroy'])->name('invoices.destroy');

        Route::get('/sales/returns', [\App\Http\Controllers\Web\SalesReturnWebController::class, 'index'])->name('sales.returns.index');
        Route::get('/sales/returns/create', [\App\Http\Controllers\Web\SalesReturnWebController::class, 'create'])->name('sales.returns.create');
        Route::post('/sales/returns', [\App\Http\Controllers\Web\SalesReturnWebController::class, 'store'])->name('sales.returns.store');
        Route::get('/sales/returns/{return}', [\App\Http\Controllers\Web\SalesReturnWebController::class, 'show'])->name('sales.returns.show');
        Route::post('/sales/returns/{return}/approve', [\App\Http\Controllers\Web\SalesReturnWebController::class, 'approve'])->name('sales.returns.approve');
        Route::post('/sales/returns/{return}/complete', [\App\Http\Controllers\Web\SalesReturnWebController::class, 'complete'])->name('sales.returns.complete');

        Route::get('/purchasing/returns', [\App\Http\Controllers\Web\PurchaseReturnWebController::class, 'index'])->name('purchase.returns.index');
        Route::get('/purchasing/returns/create', [\App\Http\Controllers\Web\PurchaseReturnWebController::class, 'create'])->name('purchase.returns.create');
        Route::post('/purchasing/returns', [\App\Http\Controllers\Web\PurchaseReturnWebController::class, 'store'])->name('purchase.returns.store');
        Route::get('/purchasing/returns/{return}', [\App\Http\Controllers\Web\PurchaseReturnWebController::class, 'show'])->name('purchase.returns.show');
        Route::post('/purchasing/returns/{return}/approve', [\App\Http\Controllers\Web\PurchaseReturnWebController::class, 'approve'])->name('purchase.returns.approve');
        Route::post('/purchasing/returns/{return}/complete', [\App\Http\Controllers\Web\PurchaseReturnWebController::class, 'complete'])->name('purchase.returns.complete');

        // Labor Rates & Machine Costs
        Route::get('/labor-machines', [LaborMachineWebController::class, 'index'])->name('labor-machines.index');
        Route::post('/labor-rates', [LaborMachineWebController::class, 'storeLabor'])->name('labor-rates.store');
        Route::put('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'updateLabor'])->name('labor-rates.update');
        Route::delete('/labor-rates/{laborRate}', [LaborMachineWebController::class, 'destroyLabor'])->name('labor-rates.destroy');
        Route::post('/machines', [LaborMachineWebController::class, 'storeMachine'])->name('machines.store');
        Route::put('/machines/{machine}', [LaborMachineWebController::class, 'updateMachine'])->name('machines.update');
        Route::delete('/machines/{machine}', [LaborMachineWebController::class, 'destroyMachine'])->name('machines.destroy');

        // What-If Scenario Simulator
        Route::get('/simulator', [SimulationWebController::class, 'index'])->name('simulator.index');
        Route::post('/simulator/{costModel}/run', [SimulationWebController::class, 'run'])->name('simulator.run');

        // BEP & Profitability Analyzer
        Route::get('/profitability', [ProfitabilityWebController::class, 'index'])->name('profitability.index');
        Route::post('/profitability/bep', [ProfitabilityWebController::class, 'calculateBep'])->name('profitability.bep');

        // Reports & Analytics Suite
        Route::get('/reports', [ReportWebController::class, 'index'])->middleware('require.permission:reports.view')->name('reports.index');
        Route::get('/reports/export-excel', [ReportWebController::class, 'exportExcel'])->middleware(['require.permission:reports.export', 'entitlement:export'])->name('reports.export-excel');

        // Business Settings (Profil Usaha, POS & Template Industri)
        Route::get('/settings', [SettingWebController::class, 'index'])->middleware('require.permission:settings.view')->name('settings.index');
        Route::put('/settings', [SettingWebController::class, 'update'])->middleware('require.permission:settings.edit')->name('settings.update');
        Route::post('/settings/apply-template', [SettingWebController::class, 'applyTemplate'])->name('settings.apply-template');

        // Dedicated Role & Access Control (RBAC) - Menu & Route Mandiri
        Route::get('/roles', [\App\Http\Controllers\Web\RoleWebController::class, 'index'])->middleware('require.permission:roles.view')->name('roles.index');
        Route::post('/roles', [\App\Http\Controllers\Web\RoleWebController::class, 'store'])->middleware('require.permission:roles.manage')->name('roles.store');
        Route::put('/roles/{role}', [\App\Http\Controllers\Web\RoleWebController::class, 'update'])->middleware('require.permission:roles.manage')->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Web\RoleWebController::class, 'destroy'])->middleware('require.permission:roles.manage')->name('roles.destroy');
        Route::post('/roles/members', [SettingWebController::class, 'storeMember'])->middleware(['require.permission:users.manage', 'entitlement:member'])->name('roles.members.store');
        Route::put('/roles/members/{member}/role', [SettingWebController::class, 'updateMemberRole'])->middleware(['require.permission:users.manage'])->name('roles.members.role');
        Route::delete('/roles/members/{member}', [SettingWebController::class, 'destroyMember'])->middleware(['require.permission:users.manage'])->name('roles.members.destroy');

        // Backward compatibility for legacy settings.roles.* and settings.members.* routes
        Route::get('/settings/roles', [\App\Http\Controllers\Web\RoleWebController::class, 'index'])->middleware('require.permission:roles.view')->name('settings.roles.index');
        Route::post('/settings/roles', [\App\Http\Controllers\Web\RoleWebController::class, 'store'])->middleware('require.permission:roles.manage')->name('settings.roles.store');
        Route::put('/settings/roles/{role}', [\App\Http\Controllers\Web\RoleWebController::class, 'update'])->middleware('require.permission:roles.manage')->name('settings.roles.update');
        Route::delete('/settings/roles/{role}', [\App\Http\Controllers\Web\RoleWebController::class, 'destroy'])->middleware('require.permission:roles.manage')->name('settings.roles.destroy');
        Route::post('/settings/members', [SettingWebController::class, 'storeMember'])->middleware(['require.permission:users.manage', 'entitlement:member'])->name('settings.members.store');
        Route::put('/settings/members/{member}/role', [SettingWebController::class, 'updateMemberRole'])->middleware(['require.permission:users.manage'])->name('settings.members.role');
        Route::delete('/settings/members/{member}', [SettingWebController::class, 'destroyMember'])->middleware(['require.permission:users.manage'])->name('settings.members.destroy');

        Route::middleware('require.role:owner')->group(function (): void {
            Route::get('/feedback/bugs', [\App\Http\Controllers\Web\FeedbackWebController::class, 'bugs'])->name('feedback.bugs.index');
            Route::get('/feedback/bugs/create', [\App\Http\Controllers\Web\FeedbackWebController::class, 'createBug'])->name('feedback.bugs.create');
            Route::post('/feedback/bugs', [\App\Http\Controllers\Web\FeedbackWebController::class, 'storeBug'])->name('feedback.bugs.store');
            Route::get('/feedback/bugs/{bugReport}', [\App\Http\Controllers\Web\FeedbackWebController::class, 'showBug'])->name('feedback.bugs.show');
            Route::get('/feedback/features', [\App\Http\Controllers\Web\FeedbackWebController::class, 'features'])->name('feedback.features.index');
            Route::get('/feedback/features/create', [\App\Http\Controllers\Web\FeedbackWebController::class, 'createFeature'])->name('feedback.features.create');
            Route::post('/feedback/features', [\App\Http\Controllers\Web\FeedbackWebController::class, 'storeFeature'])->name('feedback.features.store');
            Route::get('/feedback/features/{featureRequest}', [\App\Http\Controllers\Web\FeedbackWebController::class, 'showFeature'])->name('feedback.features.show');

            // Owner Community: posts, likes & comments
            Route::get('/community', [\App\Http\Controllers\Web\CommunityWebController::class, 'index'])->name('community.index');
            Route::post('/community', [\App\Http\Controllers\Web\CommunityWebController::class, 'store'])->name('community.store');
            Route::post('/community/{post}/like', [\App\Http\Controllers\Web\CommunityWebController::class, 'toggleLike'])->name('community.like');
            Route::post('/community/{post}/comment', [\App\Http\Controllers\Web\CommunityWebController::class, 'comment'])->name('community.comment');
            Route::delete('/community/{post}', [\App\Http\Controllers\Web\CommunityWebController::class, 'destroy'])->name('community.destroy');
        });

        // Dedicated SaaS Billing, Plan & Resource Quota Limits - Menu & Route Mandiri
        Route::get('/billing', [\App\Http\Controllers\Web\Billing\BillingAndLimitWebController::class, 'index'])->middleware('require.permission:billing.view')->name('billing');
        Route::get('/billing/limits', [\App\Http\Controllers\Web\Billing\BillingAndLimitWebController::class, 'index'])->middleware('require.permission:billing.view')->name('billing.limits');
        Route::get('/patungan', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'checkout'])->name('billing.patungan');
        Route::post('/billing/upgrade', [\App\Http\Controllers\Web\Billing\BillingAndLimitWebController::class, 'upgrade'])->name('billing.upgrade');
        Route::get('/billing/checkout', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/order', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'store'])->name('billing.order.store');
        Route::get('/billing/payments/{payment}', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'payment'])->name('billing.payment.show');
        Route::get('/billing/payments/{payment}/invoice', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'invoice'])->name('billing.payment.invoice');
        Route::post('/billing/payments/{payment}/upload-proof', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'uploadProof'])->name('billing.payment.upload');
        Route::get('/billing/history', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'history'])->name('billing.history');

        /*
        |--------------------------------------------------------------------------
        | POS (Point of Sale) & Cashier Terminal Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/pos', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'index'])->middleware('require.permission:pos.terminal')->name('pos.terminal');
        Route::get('/pos/search-products', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'searchProducts'])->middleware('require.permission:pos.terminal')->name('pos.search-products');
        Route::post('/pos/checkout', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'checkout'])->middleware(['require.permission:pos.terminal', 'entitlement:pos'])->name('pos.checkout');
        Route::post('/pos/hold', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'holdOrder'])->middleware('require.permission:pos.terminal')->name('pos.hold');
        Route::get('/pos/held-orders', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'getHeldOrders'])->name('pos.held-orders');
        Route::post('/pos/resume/{order}', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'resumeOrder'])->name('pos.resume');
        Route::get('/pos/receipt/{order}', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'printReceipt'])->name('pos.receipt');
        Route::post('/pos/verify-pin', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'verifySupervisorPin'])->name('pos.verify-pin');

        // POS Shifts
        Route::get('/pos/shifts', [\App\Http\Controllers\Web\Pos\PosShiftWebController::class, 'index'])->name('pos.shifts.index');
        Route::post('/pos/shifts/open', [\App\Http\Controllers\Web\Pos\PosShiftWebController::class, 'open'])->name('pos.shifts.open');
        Route::get('/pos/shifts/{shift}/summary', [\App\Http\Controllers\Web\Pos\PosShiftWebController::class, 'summary'])->name('pos.shifts.summary');
        Route::post('/pos/shifts/{shift}/close', [\App\Http\Controllers\Web\Pos\PosShiftWebController::class, 'close'])->name('pos.shifts.close');
        Route::post('/pos/shifts/{shift}/cash-movement', [\App\Http\Controllers\Web\Pos\PosShiftWebController::class, 'recordCashMovement'])->name('pos.shifts.cash-movement');

        // POS Orders
        Route::get('/pos/orders', [\App\Http\Controllers\Web\Pos\PosOrderWebController::class, 'index'])->name('pos.orders.index');
        Route::get('/pos/orders/{order}', [\App\Http\Controllers\Web\Pos\PosOrderWebController::class, 'show'])->name('pos.orders.show');
        Route::post('/pos/orders/{order}/void', [\App\Http\Controllers\Web\Pos\PosOrderWebController::class, 'void'])->name('pos.orders.void');
        Route::post('/pos/orders/{order}/refund', [\App\Http\Controllers\Web\Pos\PosOrderWebController::class, 'refund'])->name('pos.orders.refund');

        // POS Reports & Dashboard
        Route::get('/pos/reports', [\App\Http\Controllers\Web\Pos\PosReportWebController::class, 'index'])->name('pos.reports.index');
        Route::get('/pos/reports/export-excel', [\App\Http\Controllers\Web\Pos\PosReportWebController::class, 'exportExcel'])->middleware('entitlement:export')->name('pos.reports.export-excel');

        // AI POS & Predictive Analytics
        Route::get('/pos/ai', [\App\Http\Controllers\Web\Ai\PosAiWebController::class, 'index'])->name('pos.ai.index');
        Route::post('/pos/ai/ask', [\App\Http\Controllers\Web\Ai\PosAiWebController::class, 'ask'])->middleware('entitlement:ai')->name('pos.ai.ask');
        Route::post('/pos/ai/execute-action', [\App\Http\Controllers\Web\Ai\PosAiWebController::class, 'executeAction'])->middleware('entitlement:ai')->name('pos.ai.execute-action');

        // POS Incoming QR Orders & Table Session Checkout
        Route::get('/pos/incoming-orders', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'getIncomingOrders'])->name('pos.incoming-orders');
        Route::post('/pos/incoming-orders/{order}/accept', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'acceptIncomingOrder'])->name('pos.incoming-orders.accept');
        Route::post('/pos/incoming-orders/{order}/reject', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'rejectIncomingOrder'])->name('pos.incoming-orders.reject');
        Route::get('/pos/tables/{table}/details', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'getTableDetails'])->name('pos.tables.details');
        Route::post('/pos/orders/{order}/pay-table', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'payTableOrder'])->name('pos.orders.pay-table');

        // Table Management
        Route::get('/pos/tables', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'index'])->name('pos.tables.index');
        Route::get('/pos/tables/qr-cards', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'allQrCards'])->name('pos.tables.qr-cards');
        Route::post('/pos/tables', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'store'])->name('pos.tables.store');
        Route::put('/pos/tables/{table}', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'update'])->name('pos.tables.update');
        Route::delete('/pos/tables/{table}', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'destroy'])->name('pos.tables.destroy');
        Route::post('/pos/tables/{table}/regenerate-qr', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'regenerateQr'])->name('pos.tables.regenerate-qr');
        Route::get('/pos/tables/{table}/qr-card', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'qrCard'])->name('pos.tables.qr-card');
        Route::get('/pos/tables/{table}/qr-svg', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'downloadSvg'])->name('pos.tables.qr-svg');
        Route::post('/pos/sessions/{session}/close', [\App\Http\Controllers\Web\Pos\PosTableWebController::class, 'closeSession'])->name('pos.sessions.close');

        // Modifiers & Add-ons Management
        Route::get('/pos/modifiers', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'index'])->name('pos.modifiers.index');
        Route::post('/pos/modifiers/groups', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'storeGroup'])->name('pos.modifiers.groups.store');
        Route::put('/pos/modifiers/groups/{group}', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'updateGroup'])->name('pos.modifiers.groups.update');
        Route::delete('/pos/modifiers/groups/{group}', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'destroyGroup'])->name('pos.modifiers.groups.destroy');
        Route::post('/pos/modifiers/groups/{group}/options', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'storeOption'])->name('pos.modifiers.options.store');
        Route::put('/pos/modifiers/options/{option}', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'updateOption'])->name('pos.modifiers.options.update');
        Route::delete('/pos/modifiers/options/{option}', [\App\Http\Controllers\Web\Pos\ModifierWebController::class, 'destroyOption'])->name('pos.modifiers.options.destroy');

        // Kitchen & Bar Display
        Route::get('/pos/kitchen', [\App\Http\Controllers\Web\Pos\PosKitchenWebController::class, 'index'])->name('pos.kitchen.index');
        Route::get('/pos/kitchen/orders', [\App\Http\Controllers\Web\Pos\PosKitchenWebController::class, 'getActiveOrders'])->name('pos.kitchen.orders');
        Route::get('/pos/kitchen/active', [\App\Http\Controllers\Web\Pos\PosKitchenWebController::class, 'getActiveOrders'])->name('pos.kitchen.active');
        Route::post('/pos/kitchen/{order}/status', [\App\Http\Controllers\Web\Pos\PosKitchenWebController::class, 'updateStatus'])->name('pos.kitchen.status');

        /*
        |--------------------------------------------------------------------------
        | Warehouse Management Hub (Gudang & Lokasi)
        |--------------------------------------------------------------------------
        */
        Route::get('/warehouse', [WarehouseWebController::class, 'index'])->middleware('require.permission:inventory.view')->name('warehouse.index');
        Route::post('/warehouse', [WarehouseWebController::class, 'store'])->middleware(['require.permission:inventory.manage', 'entitlement:warehouse'])->name('warehouse.store');
        Route::get('/warehouse/{location}', [WarehouseWebController::class, 'show'])->middleware('require.permission:inventory.view')->name('warehouse.show');
        Route::put('/warehouse/{location}', [WarehouseWebController::class, 'update'])->middleware('require.permission:inventory.manage')->name('warehouse.update');
        Route::delete('/warehouse/{location}', [WarehouseWebController::class, 'destroy'])->middleware('require.permission:inventory.manage')->name('warehouse.destroy');

        /*
        |--------------------------------------------------------------------------
        | Inventory & Multi-Warehouse Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/inventory/stocks', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'stocks'])->middleware('require.permission:inventory.view')->name('inventory.stocks');
        Route::post('/inventory/stocks/adjust', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'quickAdjust'])->middleware('require.permission:inventory.manage')->name('inventory.stocks.adjust');
        Route::get('/inventory/movements', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'movements'])->name('inventory.movements');
        Route::get('/inventory/opnames', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'opnames'])->name('inventory.opnames.index');
        Route::post('/inventory/opnames', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'storeOpname'])->middleware('require.permission:inventory.manage')->name('inventory.opnames.store');
        Route::post('/inventory/opnames/{opname}/reconcile', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'reconcileOpname'])->middleware('require.permission:inventory.manage')->name('inventory.opnames.reconcile');
        Route::get('/inventory/transfers', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'transfers'])->name('inventory.transfers.index');
        Route::post('/inventory/transfers', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'storeTransfer'])->middleware('require.permission:inventory.manage')->name('inventory.transfers.store');
        Route::post('/inventory/transfers/{transfer}/receive', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'receiveTransfer'])->middleware('require.permission:inventory.manage')->name('inventory.transfers.receive');

        /*
        |--------------------------------------------------------------------------
        | CRM & Loyalty Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/crm/members', [\App\Http\Controllers\Web\Crm\CrmWebController::class, 'members'])->name('crm.members.index');
        Route::get('/crm/customers/{customer}/points', [\App\Http\Controllers\Web\Crm\CrmWebController::class, 'pointHistories'])->name('crm.customers.points');
        Route::post('/crm/customers/{customer}/credit-payment', [\App\Http\Controllers\Web\Crm\CrmWebController::class, 'recordCreditPayment'])->name('crm.customers.credit-payment');
        Route::get('/crm/vouchers', [\App\Http\Controllers\Web\Crm\CrmWebController::class, 'vouchers'])->name('crm.vouchers.index');
        Route::post('/crm/vouchers', [\App\Http\Controllers\Web\Crm\CrmWebController::class, 'storeVoucher'])->name('crm.vouchers.store');
        Route::post('/crm/vouchers/{voucher}/toggle', [\App\Http\Controllers\Web\Crm\CrmWebController::class, 'toggleVoucher'])->name('crm.vouchers.toggle');

        /*
        |--------------------------------------------------------------------------
        | Finance & Automated Journals Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/finance/journals', [\App\Http\Controllers\Web\Finance\PosFinanceWebController::class, 'journals'])->name('finance.journals.index');
        Route::get('/finance/expenses', [\App\Http\Controllers\Web\Finance\PosFinanceWebController::class, 'expenses'])->middleware('require.permission:expenses.view')->name('finance.expenses.index');
        Route::post('/finance/expenses', [\App\Http\Controllers\Web\Finance\PosFinanceWebController::class, 'storeExpense'])->middleware('require.permission:expenses.manage')->name('finance.expenses.store');
        Route::get('/finance/cash-bank', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'index'])->name('finance.cash-bank.index');
        Route::get('/finance/cash-bank/ledger', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'ledger'])->name('finance.cash-bank.ledger');
        Route::post('/finance/cash-bank/inflow', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'storeInflow'])->name('finance.cash-bank.inflow');
        Route::post('/finance/cash-bank/outflow', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'storeOutflow'])->name('finance.cash-bank.outflow');
        Route::post('/finance/cash-bank/transfer', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'transfer'])->name('finance.cash-bank.transfer');
        Route::get('/finance/receivables', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'receivables'])->name('finance.receivables');
        Route::get('/finance/payables', [\App\Http\Controllers\Web\Finance\CashLedgerWebController::class, 'payables'])->name('finance.payables');

        /*
        |--------------------------------------------------------------------------
        | WhatsApp Gateway Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('whatsapp')->name('whatsapp.')->group(function (): void {
            Route::get('/', [WhatsAppWebController::class, 'index'])->name('index');
            Route::get('/qr', [WhatsAppWebController::class, 'getQr'])->name('qr');
            Route::get('/status', [WhatsAppWebController::class, 'checkStatus'])->name('status');
            Route::post('/start', [WhatsAppWebController::class, 'startSession'])->name('start');
            Route::post('/disconnect', [WhatsAppWebController::class, 'disconnect'])->name('disconnect');
            Route::post('/settings', [WhatsAppWebController::class, 'updateSettings'])->name('settings');
            Route::post('/test', [WhatsAppWebController::class, 'testSend'])->name('test');
            Route::post('/orders/{order}/receipt', [WhatsAppWebController::class, 'sendOrderReceipt'])->name('orders.receipt');
            Route::get('/logs', [WhatsAppWebController::class, 'logs'])->name('logs.index');

            // Broadcast Promosi
            Route::get('/broadcast', [WhatsAppBroadcastWebController::class, 'index'])->name('broadcast.index');
            Route::get('/broadcast/create', [WhatsAppBroadcastWebController::class, 'create'])->name('broadcast.create');
            Route::post('/broadcast', [WhatsAppBroadcastWebController::class, 'store'])->name('broadcast.store');
            Route::get('/broadcast/{campaign}', [WhatsAppBroadcastWebController::class, 'show'])->name('broadcast.show');
            Route::get('/broadcast/estimate', [WhatsAppBroadcastWebController::class, 'estimateRecipients'])->name('broadcast.estimate');
        });

        /*
        |--------------------------------------------------------------------------
        | Business Landing Page & Mini Website CMS
        |--------------------------------------------------------------------------
        */
        Route::prefix('landing-page')->name('landing-page.')->group(function (): void {
            Route::get('/', [BusinessLandingPageWebController::class, 'edit'])->name('edit');
            Route::put('/', [BusinessLandingPageWebController::class, 'update'])->name('update');
            Route::post('/preset', [BusinessLandingPageWebController::class, 'applyPreset'])->name('preset');
            Route::post('/toggle-publish', [BusinessLandingPageWebController::class, 'togglePublish'])->name('toggle-publish');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Guard: admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function (): void {
    // Guest Admin
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
        Route::get('/forgot-password', [\App\Http\Controllers\Admin\AdminPasswordResetController::class, 'showForgot'])->name('password.request');
        Route::post('/forgot-password', [\App\Http\Controllers\Admin\AdminPasswordResetController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [\App\Http\Controllers\Admin\AdminPasswordResetController::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [\App\Http\Controllers\Admin\AdminPasswordResetController::class, 'reset'])->name('password.update');
    });

    // Authenticated Admin
    Route::middleware('auth:admin')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Business / Tenant Management
        Route::get('/businesses', [\App\Http\Controllers\Admin\AdminBusinessController::class, 'index'])->name('businesses.index');
        Route::get('/businesses/{business}', [\App\Http\Controllers\Admin\AdminBusinessController::class, 'show'])->name('businesses.show');
        Route::post('/businesses/{business}/toggle-status', [\App\Http\Controllers\Admin\AdminBusinessController::class, 'toggleStatus'])->name('businesses.toggle-status');

        // User Management & CSV Export
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // AI Token Monitoring & Granting
        Route::get('/ai-tokens', [\App\Http\Controllers\Admin\AdminAiTokenController::class, 'index'])->name('ai-tokens.index');
        Route::post('/ai-tokens/{business}/grant', [\App\Http\Controllers\Admin\AdminAiTokenController::class, 'grant'])->name('ai-tokens.grant');

        // Google API & System Settings
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/billing', [AdminSettingController::class, 'updateBilling'])->name('settings.billing');
        Route::get('/smtp', [\App\Http\Controllers\Admin\AdminSmtpController::class, 'index'])->name('smtp.index');
        Route::post('/smtp', [\App\Http\Controllers\Admin\AdminSmtpController::class, 'update'])->name('smtp.update');
        Route::post('/smtp/test', [\App\Http\Controllers\Admin\AdminSmtpController::class, 'test'])->name('smtp.test');

        // Separate billing package catalogs
        Route::get('/billing-packages/{type}', [\App\Http\Controllers\Admin\AdminBillingPackageController::class, 'index'])->name('billing-packages.index');
        Route::post('/billing-packages', [\App\Http\Controllers\Admin\AdminBillingPackageController::class, 'store'])->name('billing-packages.store');
        Route::put('/billing-packages/{billingPackage}', [\App\Http\Controllers\Admin\AdminBillingPackageController::class, 'update'])->name('billing-packages.update');
        Route::post('/billing-packages/{billingPackage}/toggle', [\App\Http\Controllers\Admin\AdminBillingPackageController::class, 'toggle'])->name('billing-packages.toggle');

        // SaaS Subscription Management & Payment Approval
        Route::get('/subscriptions', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{payment}', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{payment}/approve', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{payment}/reject', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'reject'])->name('subscriptions.reject');

        Route::get('/feedback/bugs', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'bugs'])->name('feedback.bugs.index');
        Route::get('/feedback/bugs/{bugReport}', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'showBug'])->name('feedback.bugs.show');
        Route::patch('/feedback/bugs/{bugReport}', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'updateBug'])->name('feedback.bugs.update');
        Route::get('/feedback/features', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'features'])->name('feedback.features.index');
        Route::get('/feedback/features/{featureRequest}', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'showFeature'])->name('feedback.features.show');
        Route::patch('/feedback/features/{featureRequest}', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'updateFeature'])->name('feedback.features.update');

        // CMS Rekening & Payment Accounts
        Route::get('/payment-accounts', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'index'])->name('payment-accounts.index');
        Route::get('/payment-accounts/create', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'create'])->name('payment-accounts.create');
        Route::post('/payment-accounts', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'store'])->name('payment-accounts.store');
        Route::get('/payment-accounts/{paymentAccount}/edit', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'edit'])->name('payment-accounts.edit');
        Route::put('/payment-accounts/{paymentAccount}', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'update'])->name('payment-accounts.update');
        Route::delete('/payment-accounts/{paymentAccount}', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'destroy'])->name('payment-accounts.destroy');
        Route::post('/payment-accounts/{paymentAccount}/toggle-status', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'toggleStatus'])->name('payment-accounts.toggle-status');

        // CMS Artikel & Edukasi
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [AdminPostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [AdminPostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}/edit', [AdminPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [AdminPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/toggle', [AdminPostController::class, 'toggleStatus'])->name('posts.toggle-status');

        // CMS Unduhan & Leads
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [AdminLeadController::class, 'export'])->name('leads.export');

        // WhatsApp Admin Center (Pengingat Langganan H-7, H-3, H-1, Hari H & Blast Bisnis Owner)
        Route::prefix('whatsapp')->name('whatsapp.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'index'])->name('index');
            Route::get('/qr', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'getQr'])->name('qr');
            Route::get('/status', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'checkStatus'])->name('status');
            Route::post('/start', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'startSession'])->name('start');
            Route::post('/disconnect', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'disconnect'])->name('disconnect');
            Route::post('/test', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'testSend'])->name('test');
            Route::post('/reminders/{subscription}/send', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'sendSingleReminder'])->name('reminders.send');
            Route::post('/reminders/send-all', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'sendAllReminders'])->name('reminders.send-all');
            Route::post('/reminders/templates', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'updateTemplates'])->name('reminders.templates');
            Route::post('/blasts', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'storeBlast'])->name('blasts.store');
            Route::get('/blasts/{blast}', [\App\Http\Controllers\Admin\AdminWhatsAppController::class, 'showBlast'])->name('blasts.show');
        });
    });
});
