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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Landing Page
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('landing');
})->name('landing');

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

    // User Profile & Password Management
    Route::get('/profile', [\App\Http\Controllers\Web\ProfileWebController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\ProfileWebController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Web\ProfileWebController::class, 'updatePassword'])->name('profile.password');

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
    Route::middleware('business.active')->group(function (): void {
        // Executive Dashboard & Zero-Navigation Quick Actions
        Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/quick-stats', [DashboardWebController::class, 'quickStats'])->name('dashboard.quick-stats');
        Route::post('/dashboard/quick-expense', [DashboardWebController::class, 'quickExpense'])->name('dashboard.quick-expense');
        Route::post('/dashboard/quick-stock-in', [DashboardWebController::class, 'quickStockIn'])->name('dashboard.quick-stock-in');
        Route::post('/dashboard/quick-material', [DashboardWebController::class, 'quickMaterial'])->name('dashboard.quick-material');

        // Interactive Live HPP Calculator
        Route::get('/calculator', [CalculatorWebController::class, 'index'])->name('calculator.index');
        Route::get('/calculator/calculate/{costModel}', [CalculatorWebController::class, 'calculate'])->name('calculator.calculate');
        Route::post('/calculator/save', [CalculatorWebController::class, 'saveResult'])->name('calculator.save');
        Route::post('/calculator/apply-to-product', [CalculatorWebController::class, 'applyToProduct'])->name('calculator.apply-to-product');
        Route::post('/calculator/quick-create-product', [CalculatorWebController::class, 'quickCreateProduct'])->name('calculator.quick-create-product');
        Route::get('/calculator/export-excel', [CalculatorWebController::class, 'exportExcel'])->name('calculator.export-excel');

        // Materials & Pricing Management
        Route::get('/materials', [MaterialWebController::class, 'index'])->name('materials.index');
        Route::post('/materials', [MaterialWebController::class, 'store'])->name('materials.store');
        Route::put('/materials/{material}', [MaterialWebController::class, 'update'])->name('materials.update');
        Route::post('/materials/{material}/prices', [MaterialWebController::class, 'storePrice'])->name('materials.store-price');
        Route::delete('/materials/{material}', [MaterialWebController::class, 'destroy'])->name('materials.destroy');

        // Supplier & Material Category Master Data (CMS)
        Route::get('/suppliers', [\App\Http\Controllers\Web\SupplierWebController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [\App\Http\Controllers\Web\SupplierWebController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [\App\Http\Controllers\Web\SupplierWebController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\Web\SupplierWebController::class, 'destroy'])->name('suppliers.destroy');
        Route::post('/material-categories', [\App\Http\Controllers\Web\MaterialCategoryWebController::class, 'store'])->name('material-categories.store');
        Route::put('/material-categories/{category}', [\App\Http\Controllers\Web\MaterialCategoryWebController::class, 'update'])->name('material-categories.update');
        Route::delete('/material-categories/{category}', [\App\Http\Controllers\Web\MaterialCategoryWebController::class, 'destroy'])->name('material-categories.destroy');

        // Products & BOM Management
        Route::get('/products', [ProductWebController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductWebController::class, 'store'])->middleware('entitlement:product')->name('products.store');
        Route::put('/products/{product}', [ProductWebController::class, 'update'])->name('products.update');
        Route::get('/products/{product}/bom', [ProductWebController::class, 'bom'])->name('products.bom');
        Route::post('/bom-headers/{bomHeader}/items', [ProductWebController::class, 'addBomItem'])->middleware('entitlement:recipe')->name('bom.items.store');
        Route::delete('/bom-items/{bomItem}', [ProductWebController::class, 'removeBomItem'])->name('bom.items.destroy');
        Route::delete('/products/{product}', [ProductWebController::class, 'destroy'])->name('products.destroy');

        // Product Categories & Units Master Data (CMS)
        Route::post('/product-categories', [\App\Http\Controllers\Web\ProductCategoryWebController::class, 'store'])->name('product-categories.store');
        Route::put('/product-categories/{category}', [\App\Http\Controllers\Web\ProductCategoryWebController::class, 'update'])->name('product-categories.update');
        Route::delete('/product-categories/{category}', [\App\Http\Controllers\Web\ProductCategoryWebController::class, 'destroy'])->name('product-categories.destroy');
        Route::post('/units', [\App\Http\Controllers\Web\UnitWebController::class, 'store'])->name('units.store');
        Route::put('/units/{unit}', [\App\Http\Controllers\Web\UnitWebController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}', [\App\Http\Controllers\Web\UnitWebController::class, 'destroy'])->name('units.destroy');

        // Customers Management (Commercial CRM)
        Route::resource('customers', \App\Http\Controllers\Web\CustomerWebController::class)->except(['create', 'show', 'edit']);

        // Purchase Orders (PO) Lifecycle & Management
        Route::get('/purchase-orders', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/print', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'print'])->name('purchase-orders.print');
        Route::post('/purchase-orders/{purchaseOrder}/confirm', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'confirm'])->name('purchase-orders.confirm');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::post('/purchase-orders/{purchaseOrder}/generate-invoice', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'generateInvoice'])->name('purchase-orders.generate-invoice');
        Route::delete('/purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Web\PurchaseOrderWebController::class, 'destroy'])->name('purchase-orders.destroy');

        // Goods Receipt (Penerimaan Barang Fisik dari PO & 1-Klik Beli ke Stok)
        Route::get('/purchasing/receipts/{purchaseOrder}/create', [\App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController::class, 'create'])->name('purchasing.receipts.create');
        Route::post('/purchasing/receipts/{purchaseOrder}', [\App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController::class, 'store'])->name('purchasing.receipts.store');
        Route::post('/purchasing/instant-stock-in', [\App\Http\Controllers\Web\Purchasing\GoodsReceiptWebController::class, 'instantStockIn'])->name('purchasing.instant-stock-in');

        // Sales Pipeline: Quotations (Surat Penawaran Harga)
        Route::get('/sales/quotations', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'index'])->name('sales.quotations.index');
        Route::get('/sales/quotations/create', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'create'])->name('sales.quotations.create');
        Route::post('/sales/quotations', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'store'])->name('sales.quotations.store');
        Route::get('/sales/quotations/{quotation}', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'show'])->name('sales.quotations.show');
        Route::post('/sales/quotations/{quotation}/convert', [\App\Http\Controllers\Web\Sales\QuotationWebController::class, 'convertToSalesOrder'])->name('sales.quotations.convert');

        // Sales Pipeline: Sales Orders (Pesanan Penjualan)
        Route::get('/sales/orders', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'index'])->name('sales.orders.index');
        Route::get('/sales/orders/{salesOrder}', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'show'])->name('sales.orders.show');
        Route::post('/sales/orders/{salesOrder}/generate-invoice', [\App\Http\Controllers\Web\Sales\SalesOrderWebController::class, 'generateInvoice'])->name('sales.orders.generate-invoice');

        // Invoices Management & Generator (Commerce Billing)
        Route::get('/invoices/export-excel', [\App\Http\Controllers\Web\InvoiceWebController::class, 'exportExcel'])->name('invoices.export-excel');
        Route::get('/invoices', [\App\Http\Controllers\Web\InvoiceWebController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [\App\Http\Controllers\Web\InvoiceWebController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [\App\Http\Controllers\Web\InvoiceWebController::class, 'store'])->middleware('entitlement:invoice')->name('invoices.store');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Web\InvoiceWebController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\Web\InvoiceWebController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\Web\InvoiceWebController::class, 'recordPayment'])->name('invoices.payments.store');
        Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Web\InvoiceWebController::class, 'destroy'])->name('invoices.destroy');

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
        Route::get('/reports', [ReportWebController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-excel', [ReportWebController::class, 'exportExcel'])->name('reports.export-excel');

        // Business Settings & Templates
        Route::get('/settings', [SettingWebController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingWebController::class, 'update'])->name('settings.update');
        Route::post('/settings/apply-template', [SettingWebController::class, 'applyTemplate'])->name('settings.apply-template');
        Route::post('/settings/members', [SettingWebController::class, 'storeMember'])->name('settings.members.store');
        Route::delete('/settings/members/{member}', [SettingWebController::class, 'destroyMember'])->name('settings.members.destroy');

        // SaaS Plan & Resource Quota Limits
        Route::get('/billing/limits', [\App\Http\Controllers\Web\Billing\BillingAndLimitWebController::class, 'index'])->name('billing.limits');
        Route::post('/billing/upgrade', [\App\Http\Controllers\Web\Billing\BillingAndLimitWebController::class, 'upgrade'])->name('billing.upgrade');
        Route::get('/billing/checkout', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/order', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'store'])->name('billing.order.store');
        Route::get('/billing/payments/{payment}', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'payment'])->name('billing.payment.show');
        Route::post('/billing/payments/{payment}/upload-proof', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'uploadProof'])->name('billing.payment.upload');
        Route::get('/billing/history', [\App\Http\Controllers\Web\Billing\SubscriptionCheckoutWebController::class, 'history'])->name('billing.history');

        /*
        |--------------------------------------------------------------------------
        | POS (Point of Sale) & Cashier Terminal Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/pos', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'index'])->name('pos.terminal');
        Route::get('/pos/search-products', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'searchProducts'])->name('pos.search-products');
        Route::post('/pos/checkout', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/hold', [\App\Http\Controllers\Web\Pos\PosTerminalWebController::class, 'holdOrder'])->name('pos.hold');
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
        Route::get('/pos/reports/export-excel', [\App\Http\Controllers\Web\Pos\PosReportWebController::class, 'exportExcel'])->name('pos.reports.export-excel');

        // AI POS & Predictive Analytics
        Route::get('/pos/ai', [\App\Http\Controllers\Web\Ai\PosAiWebController::class, 'index'])->name('pos.ai.index');
        Route::post('/pos/ai/ask', [\App\Http\Controllers\Web\Ai\PosAiWebController::class, 'ask'])->name('pos.ai.ask');
        Route::post('/pos/ai/execute-action', [\App\Http\Controllers\Web\Ai\PosAiWebController::class, 'executeAction'])->name('pos.ai.execute-action');

        /*
        |--------------------------------------------------------------------------
        | Inventory & Multi-Warehouse Routes
        |--------------------------------------------------------------------------
        */
        Route::get('/inventory/stocks', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'stocks'])->name('inventory.stocks');
        Route::post('/inventory/stocks/adjust', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'quickAdjust'])->name('inventory.stocks.adjust');
        Route::get('/inventory/movements', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'movements'])->name('inventory.movements');
        Route::get('/inventory/opnames', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'opnames'])->name('inventory.opnames.index');
        Route::post('/inventory/opnames', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'storeOpname'])->name('inventory.opnames.store');
        Route::post('/inventory/opnames/{opname}/reconcile', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'reconcileOpname'])->name('inventory.opnames.reconcile');
        Route::get('/inventory/transfers', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'transfers'])->name('inventory.transfers.index');
        Route::post('/inventory/transfers', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'storeTransfer'])->name('inventory.transfers.store');
        Route::post('/inventory/transfers/{transfer}/receive', [\App\Http\Controllers\Web\Inventory\InventoryWebController::class, 'receiveTransfer'])->name('inventory.transfers.receive');

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
        Route::get('/finance/expenses', [\App\Http\Controllers\Web\Finance\PosFinanceWebController::class, 'expenses'])->name('finance.expenses.index');
        Route::post('/finance/expenses', [\App\Http\Controllers\Web\Finance\PosFinanceWebController::class, 'storeExpense'])->name('finance.expenses.store');
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
    });

    // Authenticated Admin
    Route::middleware('auth:admin')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

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

        // SaaS Subscription Management & Payment Approval
        Route::get('/subscriptions', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{payment}', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{payment}/approve', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{payment}/reject', [\App\Http\Controllers\Admin\AdminSubscriptionController::class, 'reject'])->name('subscriptions.reject');

        // CMS Rekening & Payment Accounts
        Route::get('/payment-accounts', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'index'])->name('payment-accounts.index');
        Route::get('/payment-accounts/create', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'create'])->name('payment-accounts.create');
        Route::post('/payment-accounts', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'store'])->name('payment-accounts.store');
        Route::get('/payment-accounts/{paymentAccount}/edit', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'edit'])->name('payment-accounts.edit');
        Route::put('/payment-accounts/{paymentAccount}', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'update'])->name('payment-accounts.update');
        Route::delete('/payment-accounts/{paymentAccount}', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'destroy'])->name('payment-accounts.destroy');
        Route::post('/payment-accounts/{paymentAccount}/toggle-status', [\App\Http\Controllers\Admin\AdminPaymentAccountController::class, 'toggleStatus'])->name('payment-accounts.toggle-status');
    });
});
