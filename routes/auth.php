<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Web\AccountRecoveryWebController;
use App\Http\Controllers\Web\AuthOtpController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\EmailVerificationWebController;
use App\Http\Controllers\Web\PasswordResetWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Owner & Employee Web Authentication Routes (Guard: web)
|--------------------------------------------------------------------------
|
| Handles business owner and staff authentication, Google login, OTP
| verification, password reset, account recovery, and tenant switching.
|
*/

// 1. Google OAuth for Business Owners & Staff
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// 2. Guest Authentication Routes
Route::middleware('guest:web')->group(function (): void {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->middleware('throttle:5,1');

    // Registration & OTP Verification
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register']);
    Route::get('/register/verify', [AuthWebController::class, 'showRegisterOtp'])->name('register.verify');
    Route::post('/register/verify', [AuthWebController::class, 'verifyRegisterOtp'])->middleware('throttle:10,1')->name('register.verify.submit');
    Route::post('/register/verify/resend', [AuthWebController::class, 'resendRegisterOtp'])->middleware('throttle:3,1')->name('register.verify.resend');
    Route::post('/register/verify/change', [AuthWebController::class, 'changeRegisterPhone'])->middleware('throttle:3,1')->name('register.verify.change');

    // Google-Assisted Registration
    Route::get('/register/google', [GoogleAuthController::class, 'showGoogleRegistration'])->name('register.google');
    Route::post('/register/google', [GoogleAuthController::class, 'beginGoogleRegistration'])->middleware('throttle:5,1')->name('register.google.submit');

    // Password Reset Flow
    Route::get('/forgot-password', [PasswordResetWebController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetWebController::class, 'store'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetWebController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetWebController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

// 3. Account Recovery & Emergency Verification Reset (Lost Phone / Locked Email)
Route::prefix('account-recovery')->name('account-recovery.')->group(function (): void {
    Route::get('/request', [AccountRecoveryWebController::class, 'create'])->name('create');
    Route::post('/request', [AccountRecoveryWebController::class, 'store'])->middleware('throttle:6,1')->name('store');
    Route::get('/status/{ticket}', [AccountRecoveryWebController::class, 'status'])->name('status');
    Route::get('/check', [AccountRecoveryWebController::class, 'checkForm'])->name('check');
});

// 4. Authenticated User Session, 2FA OTP & Business Selection
Route::middleware(['auth:web'])->group(function (): void {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    // WhatsApp 2FA OTP for Web Login
    Route::get('/auth/otp', [AuthOtpController::class, 'show'])->name('auth.otp');
    Route::post('/auth/otp', [AuthOtpController::class, 'verify'])->middleware('throttle:10,1')->name('auth.otp.verify');
    Route::post('/auth/otp/resend', [AuthOtpController::class, 'resend'])->middleware('throttle:3,1')->name('auth.otp.resend');

    // Email Verification Notice
    Route::get('/email/verify', [EmailVerificationWebController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationWebController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationWebController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');

    // Complete Profile Onboarding
    Route::get('/complete-profile', [AuthWebController::class, 'showCompleteProfile'])->name('profile.complete');
    Route::post('/complete-profile', [AuthWebController::class, 'updateCompleteProfile'])->name('profile.complete.save');

    // Tenant Selection & Creation
    Route::get('/select-business', [AuthWebController::class, 'selectBusiness'])->name('businesses.select');
    Route::post('/select-business', [AuthWebController::class, 'switchBusiness'])->name('businesses.switch');
    Route::post('/businesses', [AuthWebController::class, 'storeBusiness'])->name('businesses.store');
});
