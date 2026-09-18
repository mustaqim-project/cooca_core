<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminAccountRecoveryController;
use App\Http\Controllers\Admin\AdminAiTokenController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBillingPackageController;
use App\Http\Controllers\Admin\AdminBusinessController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminErrorLogController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminLeadController;
use App\Http\Controllers\Admin\AdminPasswordResetController;
use App\Http\Controllers\Admin\AdminPaymentAccountController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSettlementController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSmtpController;
use App\Http\Controllers\Admin\AdminSocialMediaController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminTemplateController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Guard: admin)
|--------------------------------------------------------------------------
|
| Dedicated superadmin authentication, platform settings, SaaS billing,
| tenant management, and platform-wide monitoring.
|
*/
Route::prefix('admin')->name('admin.')->group(function (): void {
    // 1. Guest Admin Authentication
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
        Route::get('/forgot-password', [AdminPasswordResetController::class, 'showForgot'])->name('password.request');
        Route::post('/forgot-password', [AdminPasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', [AdminPasswordResetController::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [AdminPasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
    });

    // 2. Authenticated Admin Workspace
    Route::middleware('auth:admin')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [AdminProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Business / Tenant Management
        Route::get('/businesses', [AdminBusinessController::class, 'index'])->name('businesses.index');
        Route::get('/businesses/{business}', [AdminBusinessController::class, 'show'])->name('businesses.show');
        Route::post('/businesses/{business}/toggle-status', [AdminBusinessController::class, 'toggleStatus'])->name('businesses.toggle-status');

        // User Management & CSV Export
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Account Recovery Verification & Approval
        Route::prefix('account-recoveries')->name('account-recoveries.')->group(function (): void {
            Route::get('/', [AdminAccountRecoveryController::class, 'index'])->name('index');
            Route::get('/{recovery}', [AdminAccountRecoveryController::class, 'show'])->name('show');
            Route::get('/{recovery}/document/{type}', [AdminAccountRecoveryController::class, 'document'])->name('document');
            Route::post('/{recovery}/approve', [AdminAccountRecoveryController::class, 'approve'])->name('approve');
            Route::post('/{recovery}/reject', [AdminAccountRecoveryController::class, 'reject'])->name('reject');
        });

        // AI Token Monitoring & Granting
        Route::get('/ai-tokens', [AdminAiTokenController::class, 'index'])->name('ai-tokens.index');
        Route::post('/ai-tokens/{business}/grant', [AdminAiTokenController::class, 'grant'])->name('ai-tokens.grant');

        // Google API, System Settings, Payment Gateway, WhatsApp & SMTP
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/billing', [AdminSettingController::class, 'updateBilling'])->name('settings.billing');
        Route::post('/settings/test-social-media', [AdminSettingController::class, 'testSocialMediaConfig'])->name('settings.test-social');
        Route::post('/settings/test-tripay', [AdminSettingController::class, 'testTripayConfig'])->name('settings.test-tripay');
        Route::post('/settings/test-whatsapp', [AdminSettingController::class, 'testWhatsAppConfig'])->name('settings.test-whatsapp');
        Route::post('/settings/test-instagram', [AdminSettingController::class, 'testInstagramConfig'])->name('settings.test-instagram');
        Route::post('/settings/test-biteship', [AdminSettingController::class, 'testBiteshipConfig'])->name('settings.test-biteship');
        Route::get('/smtp', [AdminSmtpController::class, 'index'])->name('smtp.index');
        Route::post('/smtp', [AdminSmtpController::class, 'update'])->name('smtp.update');
        Route::post('/smtp/test', [AdminSmtpController::class, 'test'])->name('smtp.test');

        // Billing Package Catalogs
        Route::get('/billing-packages/{type?}', [AdminBillingPackageController::class, 'index'])->name('billing-packages.index');
        Route::post('/billing-packages', [AdminBillingPackageController::class, 'store'])->name('billing-packages.store');
        Route::put('/billing-packages/{billingPackage}', [AdminBillingPackageController::class, 'update'])->name('billing-packages.update');
        Route::post('/billing-packages/{billingPackage}/toggle', [AdminBillingPackageController::class, 'toggle'])->name('billing-packages.toggle');

        // SaaS Subscription Management & Payment Approval
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{payment}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{payment}/approve', [AdminSubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::post('/subscriptions/{payment}/reject', [AdminSubscriptionController::class, 'reject'])->name('subscriptions.reject');

        // Merchant Gateway Settlement & Payout Hub (Bukti Transfer Upload & Verifikasi)
        Route::prefix('settlements')->name('settlements.')->group(function (): void {
            Route::get('/', [AdminSettlementController::class, 'index'])->name('index');
            Route::get('/{settlement}', [AdminSettlementController::class, 'show'])->name('show');
            Route::post('/{settlement}/approve', [AdminSettlementController::class, 'approve'])->name('approve');
            Route::post('/{settlement}/reject', [AdminSettlementController::class, 'reject'])->name('reject');
        });

        // Feedback: Bug Reports & Feature Requests
        Route::get('/feedback/bugs', [AdminFeedbackController::class, 'bugs'])->name('feedback.bugs.index');
        Route::get('/feedback/bugs/{bugReport}', [AdminFeedbackController::class, 'showBug'])->name('feedback.bugs.show');
        Route::patch('/feedback/bugs/{bugReport}', [AdminFeedbackController::class, 'updateBug'])->name('feedback.bugs.update');
        Route::get('/feedback/features', [AdminFeedbackController::class, 'features'])->name('feedback.features.index');
        Route::get('/feedback/features/{featureRequest}', [AdminFeedbackController::class, 'showFeature'])->name('feedback.features.show');
        Route::patch('/feedback/features/{featureRequest}', [AdminFeedbackController::class, 'updateFeature'])->name('feedback.features.update');

        // CMS Rekening Pembayaran Platform
        Route::get('/payment-accounts', [AdminPaymentAccountController::class, 'index'])->name('payment-accounts.index');
        Route::get('/payment-accounts/create', [AdminPaymentAccountController::class, 'create'])->name('payment-accounts.create');
        Route::post('/payment-accounts', [AdminPaymentAccountController::class, 'store'])->name('payment-accounts.store');
        Route::get('/payment-accounts/{paymentAccount}/edit', [AdminPaymentAccountController::class, 'edit'])->name('payment-accounts.edit');
        Route::put('/payment-accounts/{paymentAccount}', [AdminPaymentAccountController::class, 'update'])->name('payment-accounts.update');
        Route::delete('/payment-accounts/{paymentAccount}', [AdminPaymentAccountController::class, 'destroy'])->name('payment-accounts.destroy');
        Route::post('/payment-accounts/{paymentAccount}/toggle-status', [AdminPaymentAccountController::class, 'toggleStatus'])->name('payment-accounts.toggle-status');

        // CMS Artikel, Kategori, & Cluster
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [AdminPostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [AdminPostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}/edit', [AdminPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [AdminPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/toggle', [AdminPostController::class, 'toggleStatus'])->name('posts.toggle-status');

        // Post Categories CRUD
        Route::post('/posts/categories', [AdminPostController::class, 'storeCategory'])->name('posts.categories.store');
        Route::put('/posts/categories/{category}', [AdminPostController::class, 'updateCategory'])->name('posts.categories.update');
        Route::delete('/posts/categories/{category}', [AdminPostController::class, 'destroyCategory'])->name('posts.categories.destroy');

        // Post Clusters CRUD
        Route::post('/posts/clusters', [AdminPostController::class, 'storeCluster'])->name('posts.clusters.store');
        Route::put('/posts/clusters/{cluster}', [AdminPostController::class, 'updateCluster'])->name('posts.clusters.update');
        Route::delete('/posts/clusters/{cluster}', [AdminPostController::class, 'destroyCluster'])->name('posts.clusters.destroy');

        // CMS Leads
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [AdminLeadController::class, 'export'])->name('leads.export');

        // CMS Template Excel Unduhan
        Route::prefix('templates')->name('templates.')->group(function (): void {
            Route::get('/', [AdminTemplateController::class, 'index'])->name('index');
            Route::get('/create', [AdminTemplateController::class, 'create'])->name('create');
            Route::post('/', [AdminTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [AdminTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [AdminTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [AdminTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/toggle', [AdminTemplateController::class, 'toggle'])->name('toggle');
            Route::get('/{template}/download', [AdminTemplateController::class, 'download'])->name('download');
        });

        // WhatsApp Admin Center (Meta Official Platform Gateway, Reminders & Blast)
        Route::prefix('whatsapp')->name('whatsapp.')->group(function (): void {
            Route::get('/', [AdminWhatsAppController::class, 'index'])->name('index');
            Route::get('/status', [AdminWhatsAppController::class, 'checkStatus'])->name('status');
            Route::post('/test', [AdminWhatsAppController::class, 'testSend'])->name('test');
            Route::post('/send-otp', [AdminWhatsAppController::class, 'sendOtp'])->name('send-otp');
            Route::post('/verify-meta', [AdminWhatsAppController::class, 'verifyMetaCredentials'])->name('verify-meta');
            Route::post('/reminders/{subscription}/send', [AdminWhatsAppController::class, 'sendSingleReminder'])->name('reminders.send');
            Route::post('/reminders/send-all', [AdminWhatsAppController::class, 'sendAllReminders'])->name('reminders.send-all');
            Route::post('/reminders/templates', [AdminWhatsAppController::class, 'updateTemplates'])->name('reminders.templates');
            Route::post('/blasts', [AdminWhatsAppController::class, 'storeBlast'])->name('blasts.store');
            Route::get('/blasts/{blast}', [AdminWhatsAppController::class, 'showBlast'])->name('blasts.show');
            Route::post('/config', [AdminWhatsAppController::class, 'updateGatewayConfig'])->name('config');

            // Legacy stubs for graceful fallback
            Route::get('/qr', [AdminWhatsAppController::class, 'getQr'])->name('qr');
            Route::get('/sessions', [AdminWhatsAppController::class, 'getSessions'])->name('sessions.index');
        });

        // Social Media Platform Admin Center (Meta App, Webhook & Tenant Oversight)
        Route::prefix('social-media')->name('social-media.')->group(function (): void {
            Route::get('/', [AdminSocialMediaController::class, 'index'])->name('index');
            Route::post('/config', [AdminSocialMediaController::class, 'updateConfig'])->name('config');
            Route::post('/posts', [AdminSocialMediaController::class, 'storePost'])->name('posts.store');
            Route::post('/posts/{post}/retry', [AdminSocialMediaController::class, 'retryPost'])->name('posts.retry');
            Route::delete('/posts/{post}', [AdminSocialMediaController::class, 'destroyPost'])->name('posts.destroy');
            Route::post('/comments/{comment}/reply', [AdminSocialMediaController::class, 'replyComment'])->name('comments.reply');
        });

        // Error Logs & System Diagnostics
        Route::prefix('error-logs')->name('error-logs.')->group(function (): void {
            Route::get('/', [AdminErrorLogController::class, 'index'])->name('index');
            Route::get('/download', [AdminErrorLogController::class, 'download'])->name('download');
            Route::delete('/clear', [AdminErrorLogController::class, 'clear'])->name('clear');
        });
    });
});
