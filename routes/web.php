<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes Registry
|--------------------------------------------------------------------------
|
| Routes are cleanly segregated into dedicated domain modules for maximum
| security, clear role boundaries, and ease of maintenance:
|
| 1. admin.php    - Platform Superadmin authentication and backoffice panel
| 2. auth.php     - Business owner & employee authentication (login, register, OTP)
| 3. customer.php - Customer Google-only OAuth, customer portal & checkout gates
| 4. owner.php    - Business owner workspace (ERP, POS, Inventory, Finance, Billing)
| 5. public.php   - Public marketing, calculators, blog, sitemap, and storefront landing
|
*/

require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/customer.php';
require __DIR__ . '/owner.php';
require __DIR__ . '/public.php';

// Protected Payout Transfer Proof Document (Authorized Superadmin or Merchant Owner Only)
\Illuminate\Support\Facades\Route::get('/settlements/{settlement}/proof', [\App\Http\Controllers\Common\SettlementProofController::class, 'show'])
    ->name('settlements.proof');

// Catch-all Canonical Redirection: Eliminate any accidental /public prefix
\Illuminate\Support\Facades\Route::any('/public/{any?}', function (?string $any = null) {
    return redirect('/' . ($any ?? ''), 301);
})->where('any', '.*');


