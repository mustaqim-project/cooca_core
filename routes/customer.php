<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Commerce\CustomerAuthController;
use App\Http\Controllers\Web\Commerce\CustomerGoogleAuthController;
use App\Http\Controllers\Web\Commerce\CustomerOtpController;
use App\Http\Controllers\Web\Commerce\CustomerPortalController;
use App\Http\Controllers\Web\Commerce\PublicOrderTrackingController;
use App\Http\Controllers\Web\Commerce\PublicReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer & Storefront Order Routes (Guard: customer)
|--------------------------------------------------------------------------
|
| Handles customer Google-only authentication, multi-store shopping cart,
| order history portal, and gated storefront order/reservation actions.
|
*/

// Storefront convenience: direct Google login entry from store landing page
Route::get('/b/{slug}/login', [CustomerAuthController::class, 'showLoginForm'])->name('public.storefront.customer.login');

// Storefront Gated Actions (Requires GlobalCustomer Google Auth + Profile + Lifetime WhatsApp OTP)
Route::middleware(['auth:customer', 'customer.profile', 'customer.otp', 'throttle:10,1'])->group(function (): void {
    Route::post('/b/{slug}/checkout', [PublicOrderTrackingController::class, 'submitCheckout'])
        ->name('public.storefront.checkout');
    Route::post('/b/{slug}/request-order', [PublicOrderTrackingController::class, 'submitRequestOrder'])
        ->name('public.storefront.request_order');
    Route::post('/b/{slug}/customer-po', [PublicOrderTrackingController::class, 'submitCustomerPo'])
        ->name('public.storefront.customer_po');
    Route::post('/b/{slug}/reservasi', [PublicReservationController::class, 'submitReservation'])
        ->middleware('throttle:10,5')
        ->name('public.storefront.reservation.submit');
});

// Customer Portal (Google-Only Auth Guard)
Route::prefix('customer')->name('customer.')->group(function (): void {

    // 1. Google OAuth (Cross-Store SSO Identity)
    Route::get('/auth/google', [CustomerGoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [CustomerGoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // 2. Login & Registration Pages
    Route::middleware('guest:customer')->group(function (): void {
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
        Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
    });

    // Email verification link handler (signed public link)
    Route::get('/email/verify/{id}/{hash}', [CustomerAuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // 3. Authenticated Customer Dashboard & Cart Workspace
    Route::middleware('auth:customer')->group(function (): void {
        // Dashboard & Order Tracking
        Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [CustomerPortalController::class, 'orderDetail'])->name('orders.detail');
        Route::post('/orders/{id}/upload-proof', [CustomerPortalController::class, 'uploadProof'])->name('orders.upload_proof');

        // Profile & Address Completion
        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/profile/complete', [CustomerPortalController::class, 'profileComplete'])->name('profile.complete');
        Route::post('/profile/complete', [CustomerPortalController::class, 'storeProfileComplete'])->name('profile.complete.save');

        // Permanent WhatsApp OTP Verification
        Route::get('/otp', [CustomerOtpController::class, 'show'])->name('otp');
        Route::post('/otp/send', [CustomerOtpController::class, 'sendOtp'])->middleware('throttle:3,1')->name('otp.send');
        Route::post('/otp/verify', [CustomerOtpController::class, 'verify'])->middleware('throttle:10,1')->name('otp.verify');

        // Customer Email Verification
        Route::get('/email/verify', [CustomerAuthController::class, 'showVerificationNotice'])->name('verification.notice');
        Route::post('/email/verification-notification', [CustomerAuthController::class, 'resendVerificationEmail'])->middleware('throttle:6,1')->name('verification.send');

        // Store Directory Browser
        Route::get('/stores', [CustomerPortalController::class, 'stores'])->name('stores');
        Route::get('/stores/{slug}', [CustomerPortalController::class, 'storeProducts'])->name('stores.show');

        // Multi-Store Cart (Items grouped per business)
        Route::get('/cart', [CustomerPortalController::class, 'cart'])->name('cart');
        Route::post('/cart/{slug}/add', [CustomerPortalController::class, 'addToCart'])->name('cart.add');
        Route::patch('/cart/{slug}/item/{item}', [CustomerPortalController::class, 'updateCartItem'])->name('cart.item.update');
        Route::delete('/cart/{slug}/item/{item}', [CustomerPortalController::class, 'removeCartItem'])->name('cart.item.remove');
        Route::post('/cart/{slug}/checkout', [CustomerPortalController::class, 'checkout'])
            ->middleware(['customer.profile', 'customer.otp'])
            ->name('cart.checkout');
    });
});
