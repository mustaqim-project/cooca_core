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
use App\Http\Controllers\Web\PublicMarketplaceController;
use App\Http\Controllers\Web\Storefront\PublicStorefrontController;
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
    $previewProducts = \App\Models\Product::where('is_active', true)
        ->where('show_in_website', true)
        ->whereHas('business', function ($q): void {
            $q->where('is_active', true)
              ->whereHas('storeSetting', function ($sq): void {
                  $sq->where('is_storefront_enabled', true)
                    ->where('is_discoverable', true);
              });
        })
        ->with(['business.storeSetting', 'category'])
        ->latest()
        ->take(3)
        ->get();

    return view('landing', compact('previewProducts'));
})->name('landing');

// Marketplace (SaaS + Toko Online Directory)
Route::get('/marketplace', [PublicMarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/cari', [PublicMarketplaceController::class, 'search'])->name('marketplace.search');

// GeoLocation & Indonesian Administrative Area Search (Biteship API + Reverse Geocoding)
Route::get('/geo/search-areas', [\App\Http\Controllers\Web\Common\GeoLocationController::class, 'searchAreas'])
    ->middleware('throttle:60,1')
    ->name('geo.search-areas');
Route::get('/geo/reverse-geocode', [\App\Http\Controllers\Web\Common\GeoLocationController::class, 'reverseGeocode'])
    ->middleware('throttle:60,1')
    ->name('geo.reverse-geocode');

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
Route::permanentRedirect('/contact', '/kontak');
Route::post('/kontak', [PublicContactController::class, 'submit'])->middleware('throttle:5,5')->name('contact.submit');

// 6. Public Business Discovery Directory (/jelajah & /direktori)
Route::get('/jelajah', [PublicDiscoveryController::class, 'index'])->name('public.discovery.index');
Route::get('/direktori', [PublicDiscoveryController::class, 'index'])->name('public.directory.index');

// 6b. Core Public Ecosystem Pages (BOS, ERP, Omnichannel, Content Automation, Solutions, Resources)
Route::get('/pricing', fn() => view('public.pricing'))->name('public.pricing');
Route::get('/demo', fn() => view('public.demo'))->name('public.demo');
Route::get('/about', fn() => view('public.about'))->name('public.about');
Route::get('/tentang', fn() => view('public.about'));
Route::get('/support', fn() => view('public.support'))->name('public.support');
Route::get('/bantuan', fn() => view('public.support'));

Route::prefix('business-operating-system')->name('public.bos.')->group(function (): void {
    Route::get('/overview', fn() => view('public.business-operating-system.overview'))->name('overview');
    Route::get('/how-it-works', fn() => view('public.business-operating-system.how-it-works'))->name('how-it-works');
    Route::get('/why-cooca', fn() => view('public.business-operating-system.why-cooca'))->name('why-cooca');
});

Route::prefix('omnichannel-erp')->name('public.erp.')->group(function (): void {
    Route::get('/erp', fn() => view('public.omnichannel-erp.erp'))->name('erp');
    Route::get('/pos', fn() => view('public.omnichannel-erp.pos'))->name('pos');
    Route::get('/finance', fn() => view('public.omnichannel-erp.finance'))->name('finance');
    Route::get('/inventory', fn() => view('public.omnichannel-erp.inventory'))->name('inventory');
    Route::get('/crm', fn() => view('public.omnichannel-erp.crm'))->name('crm');
    Route::get('/hrm', fn() => view('public.omnichannel-erp.hrm'))->name('hrm');
    Route::get('/accounting', fn() => view('public.omnichannel-erp.accounting'))->name('accounting');
    Route::get('/analytics', fn() => view('public.omnichannel-erp.analytics'))->name('analytics');
});

Route::prefix('omnichannel')->name('public.omnichannel.')->group(function (): void {
    Route::get('/social-media', fn() => view('public.omnichannel.social-media'))->name('social-media');
    Route::get('/whatsapp', fn() => view('public.omnichannel.whatsapp'))->name('whatsapp');
    Route::get('/marketplace', fn() => view('public.omnichannel.marketplace'))->name('marketplace');
    Route::get('/orders', fn() => view('public.omnichannel.orders'))->name('orders');
    Route::get('/customer', fn() => view('public.omnichannel.customer'))->name('customer');
});

Route::prefix('content-automation')->name('public.content.')->group(function (): void {
    Route::get('/content-creation', fn() => view('public.content-automation.content-creation'))->name('creation');
    Route::get('/content-calendar', fn() => view('public.content-automation.content-calendar'))->name('calendar');
    Route::get('/publishing', fn() => view('public.content-automation.publishing'))->name('publishing');
    Route::get('/analytics', fn() => view('public.content-automation.analytics'))->name('analytics');
});

Route::prefix('solutions')->name('public.solutions.')->group(function (): void {
    Route::get('/fnb', fn() => view('public.solutions.fnb'))->name('fnb');
    Route::get('/retail', fn() => view('public.solutions.retail'))->name('retail');
    Route::get('/workshop', fn() => view('public.solutions.workshop'))->name('workshop');
    Route::get('/laundry', fn() => view('public.solutions.laundry'))->name('laundry');
    Route::get('/manufacturing', fn() => view('public.solutions.manufacturing'))->name('manufacturing');
    Route::get('/services', fn() => view('public.solutions.services'))->name('services');
});

Route::prefix('marketplace')->name('marketplace.sub.')->group(function (): void {
    Route::get('/businesses', [PublicMarketplaceController::class, 'businesses'])->name('businesses');
    Route::get('/products', [PublicMarketplaceController::class, 'products'])->name('products');
    Route::get('/categories', [PublicMarketplaceController::class, 'categories'])->name('categories');
    Route::get('/locations', [PublicMarketplaceController::class, 'locations'])->name('locations');
});

Route::prefix('resources')->name('public.resources.')->group(function (): void {
    Route::get('/blog', fn() => view('public.resources.blog'))->name('blog');
    Route::get('/guides', fn() => view('public.resources.guides'))->name('guides');
    Route::get('/case-studies', fn() => view('public.resources.case-studies'))->name('case-studies');
    Route::get('/faq', fn() => view('public.resources.faq'))->name('faq');
});

// 7. Canonical Public Storefront Tracking, Multipage Pages, Table QR & Calculation (cooca.id/{slug-bisnis})
Route::prefix('{slug}')->where(['slug' => '^(?!(pos|admin|api|dashboard|auth|login|register|profile|calculator|settings|billing|customer|public|storage|up|settlements|payments|community)$)[a-z0-9]+(?:-[a-z0-9]+)*$'])->group(function (): void {
    // Dedicated Multipage Storefront Pages (§PRD-07)
    Route::get('/katalog', [PublicStorefrontController::class, 'catalog'])->name('public.storefront.catalog');
    Route::get('/produk/{product}', [PublicStorefrontController::class, 'productDetail'])->name('public.storefront.product.detail');
    Route::get('/checkout', [PublicStorefrontController::class, 'checkout'])->name('public.storefront.checkout.page');
    Route::get('/tentang-kami', [PublicStorefrontController::class, 'about'])->name('public.storefront.about');
    Route::get('/reservasi', [PublicStorefrontController::class, 'reservation'])->name('public.storefront.reservation');
    Route::get('/kontak', [PublicStorefrontController::class, 'contact'])->name('public.storefront.contact');
    Route::get('/artikel', [PublicStorefrontController::class, 'articles'])->name('public.storefront.articles');
    Route::get('/artikel/{article_slug}', [PublicStorefrontController::class, 'articleDetail'])->name('public.storefront.article.detail');

    Route::post('/order/{token}/proof', [PublicOrderTrackingController::class, 'uploadProof'])
        ->middleware('throttle:10,60')
        ->name('public.storefront.order.upload_proof');
    Route::post('/shipping/calculate', [PublicOrderTrackingController::class, 'calculateShippingQuote'])
        ->middleware('throttle:30,1')
        ->name('public.storefront.shipping.calculate');
    Route::get('/order/{token}', [PublicOrderTrackingController::class, 'show'])->name('public.storefront.order.track');
    Route::get('/order/{token}/status', [PublicOrderTrackingController::class, 'checkStatus'])->name('public.storefront.order.status');
    Route::get('/reservasi/check', [PublicReservationController::class, 'checkAvailability'])->name('public.storefront.reservation.check');

    // Public Customer QR Table Ordering
    Route::get('/table/{qrToken}', [PublicQrOrderWebController::class, 'showMenu'])->name('public.qr.menu.slug');
    Route::post('/table/{qrToken}/order', [PublicQrOrderWebController::class, 'submitOrder'])->middleware('throttle:20,1')->name('public.qr.order.slug');
    Route::get('/table/{qrToken}/order/{order}/status', [PublicQrOrderWebController::class, 'checkStatus'])->name('public.qr.order.status.slug');
});

// 8. Legacy /b/{slug} aliases for backward compatibility (0 broken links)
Route::get('/b/{slug}', [PublicBusinessLandingController::class, 'show'])->name('public.business.landing.legacy');
Route::prefix('b/{slug}')->where(['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'])->group(function (): void {
    Route::get('/katalog', [PublicStorefrontController::class, 'catalog']);
    Route::get('/produk/{product}', [PublicStorefrontController::class, 'productDetail']);
    Route::get('/checkout', [PublicStorefrontController::class, 'checkout']);
    Route::get('/tentang-kami', [PublicStorefrontController::class, 'about']);
    Route::get('/reservasi', [PublicStorefrontController::class, 'reservation']);
    Route::get('/kontak', [PublicStorefrontController::class, 'contact']);
    Route::get('/artikel', [PublicStorefrontController::class, 'articles']);
    Route::get('/artikel/{article_slug}', [PublicStorefrontController::class, 'articleDetail']);
    Route::post('/order/{token}/proof', [PublicOrderTrackingController::class, 'uploadProof'])->middleware('throttle:10,60');
    Route::post('/shipping/calculate', [PublicOrderTrackingController::class, 'calculateShippingQuote'])->middleware('throttle:30,1');
    Route::get('/order/{token}', [PublicOrderTrackingController::class, 'show']);
    Route::get('/order/{token}/status', [PublicOrderTrackingController::class, 'checkStatus']);
    Route::get('/reservasi/check', [PublicReservationController::class, 'checkAvailability']);
    Route::get('/table/{qrToken}', [PublicQrOrderWebController::class, 'showMenu']);
    Route::post('/table/{qrToken}/order', [PublicQrOrderWebController::class, 'submitOrder'])->middleware('throttle:20,1');
    Route::get('/table/{qrToken}/order/{order}/status', [PublicQrOrderWebController::class, 'checkStatus']);
});

// 9. Short QR Table Ordering Direct Route
Route::get('/t/{qrToken}', [PublicQrOrderWebController::class, 'showMenu'])->name('public.qr.menu');
Route::post('/t/{qrToken}/order', [PublicQrOrderWebController::class, 'submitOrder'])->middleware('throttle:20,1')->name('public.qr.order');
Route::get('/t/{qrToken}/order/{order}/track', [PublicQrOrderWebController::class, 'trackOrder'])->name('public.qr.track');
Route::get('/t/{qrToken}/order/{order}/status', [PublicQrOrderWebController::class, 'checkStatus'])->name('public.qr.order.status');

// 10. Sitemap XML & HTML (SEO & Web Crawlers)
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/sitemap', [SitemapController::class, 'html'])->name('sitemap.html');

// 11. Public Customer Receipt & Receipt Image View
Route::get('/receipt/{order}', [PosTerminalWebController::class, 'printReceipt'])->name('public.receipt');
Route::get('/receipt/{order}/image', [PosTerminalWebController::class, 'receiptImage'])->name('public.receipt.image');

// 12. Legal Policies (Privacy Policy & Terms of Service for Cooca, TikTok, Meta & Google)
Route::get('/privacy', function () {
    $page = \App\Models\LegalPage::findBySlug('privacy-policy');
    return view('public.privacy', compact('page'));
})->name('public.privacy');
Route::get('/kebijakan-privasi', function () {
    $page = \App\Models\LegalPage::findBySlug('privacy-policy');
    return view('public.privacy', compact('page'));
});

Route::get('/terms', function () {
    $page = \App\Models\LegalPage::findBySlug('terms-conditions');
    return view('public.terms', compact('page'));
})->name('public.terms');
Route::get('/syarat-ketentuan', function () {
    $page = \App\Models\LegalPage::findBySlug('terms-conditions');
    return view('public.terms', compact('page'));
});

Route::get('/data-deletion', function () {
    return redirect('/privacy#data-subject-rights');
})->name('public.data-deletion');
Route::get('/penghapusan-data', function () {
    return redirect('/privacy#data-subject-rights');
});

// 13. Public Digital Payslip (Token Access)
Route::get('/payslip/{token}', [\App\Http\Controllers\Web\Hrm\HrmWebController::class, 'publicPayslip'])->name('public.payslip');

// 14. Marketplace Integration OAuth Callbacks & Webhooks
Route::get('/integrations/{provider}/callback', [\App\Http\Controllers\Web\Marketplace\MarketplaceWebController::class, 'callback'])->name('integrations.marketplace.callback');
Route::post('/webhooks/marketplace/{provider}', [\App\Http\Controllers\Web\Marketplace\MarketplaceWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('webhooks.marketplace');

// 15. Public business landing pages using business name as direct URL slug (Must be last)
Route::get('/{slug}', [PublicStorefrontController::class, 'home'])
    ->where('slug', '^(?!(pos|admin|api|dashboard|auth|login|register|profile|calculator|settings|billing|customer|public|storage|up|settlements|payments|community)$)[a-z0-9]+(?:-[a-z0-9]+)*$')
    ->name('public.business.landing');
