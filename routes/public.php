<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Commerce\PublicOrderTrackingController;
use App\Http\Controllers\Web\Commerce\PublicReservationController;
use App\Http\Controllers\Web\Pos\PosTerminalWebController;
use App\Http\Controllers\Web\Pos\PublicQrOrderWebController;
use App\Http\Controllers\Web\PublicBlogController;
use App\Http\Controllers\Web\PublicBusinessLandingController;
use App\Http\Controllers\Web\PublicCalculatorController;
use App\Http\Controllers\Web\PublicContactController;
use App\Http\Controllers\Web\PublicDiscoveryController;
use App\Http\Controllers\Web\PublicSolutionController;
use App\Http\Controllers\Web\PublicTemplateController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Landing Page & Multipage Ecosystem
|--------------------------------------------------------------------------
|
| Accessible by any visitor, web crawlers, prospective clients, and customers
| without requiring authentication.
|
*/

// Main Marketing Homepage
Route::get('/', function () {
    return view('landing');
})->name('landing');

// 1. Business Calculators
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
Route::post('/template/{slug}/download', [PublicTemplateController::class, 'captureLead'])->middleware('throttle:5,5')->name('template.download');
Route::get('/template/{slug}/file', [PublicTemplateController::class, 'downloadFile'])->name('template.file');

// 3. Solusi per Vertikal Niche UMKM
Route::get('/solusi/{slug}', [PublicSolutionController::class, 'show'])->name('solusi.show');

// 4. Blog & Edukasi Bisnis
Route::get('/blog', [PublicBlogController::class, 'index'])->middleware('throttle:60,1')->name('blog.index');
Route::get('/blog/{slug}', [PublicBlogController::class, 'show'])->middleware('throttle:30,1')->name('blog.show');

// 5. Halaman Kontak
Route::get('/kontak', [PublicContactController::class, 'show'])->name('contact');
Route::post('/kontak', [PublicContactController::class, 'submit'])->middleware('throttle:5,5')->name('contact.submit');

// 6. Public Business Discovery Directory (/jelajah & /direktori)
Route::get('/jelajah', [PublicDiscoveryController::class, 'index'])->name('public.discovery.index');
Route::get('/direktori', [PublicDiscoveryController::class, 'index'])->name('public.directory.index');

// 7. Public Business Single-Page Landing Pages
Route::get('/b/{slug}', [PublicBusinessLandingController::class, 'show'])->name('public.business.landing.legacy');

// 8. Public Storefront Tracking & Calculation
Route::post('/b/{slug}/order/{token}/proof', [PublicOrderTrackingController::class, 'uploadProof'])
    ->middleware('throttle:10,60')
    ->name('public.storefront.order.upload_proof');
Route::post('/b/{slug}/shipping/calculate', [PublicOrderTrackingController::class, 'calculateShippingQuote'])
    ->middleware('throttle:30,1')
    ->name('public.storefront.shipping.calculate');
Route::get('/b/{slug}/order/{token}', [PublicOrderTrackingController::class, 'show'])->name('public.storefront.order.track');
Route::get('/b/{slug}/reservasi/check', [PublicReservationController::class, 'checkAvailability'])->name('public.storefront.reservation.check');

// 9. Public Customer QR Table Ordering
Route::get('/t/{qrToken}', [PublicQrOrderWebController::class, 'showMenu'])->name('public.qr.menu');
Route::post('/t/{qrToken}/order', [PublicQrOrderWebController::class, 'submitOrder'])->middleware('throttle:20,1')->name('public.qr.order');
Route::get('/t/{qrToken}/order/{order}/track', [PublicQrOrderWebController::class, 'trackOrder'])->name('public.qr.track');
Route::get('/b/{slug}/table/{qrToken}', [PublicQrOrderWebController::class, 'showMenu'])->name('public.qr.menu.slug');
Route::post('/b/{slug}/table/{qrToken}/order', [PublicQrOrderWebController::class, 'submitOrder'])->middleware('throttle:20,1')->name('public.qr.order.slug');

// 10. Sitemap XML & HTML (SEO & Web Crawlers)
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/sitemap', [SitemapController::class, 'html'])->name('sitemap.html');

// 11. Public Customer Receipt & Receipt Image View
Route::get('/receipt/{order}', [PosTerminalWebController::class, 'printReceipt'])->name('public.receipt');
Route::get('/receipt/{order}/image', [PosTerminalWebController::class, 'receiptImage'])->name('public.receipt.image');

// 12. Public business landing pages using business name as direct URL slug (Must be last)
Route::get('/{slug}', [PublicBusinessLandingController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('public.business.landing');
