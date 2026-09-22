<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Storefront;

use App\Domain\Storefront\StorefrontThemeService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceOrder;
use App\Models\CommerceOrderItem;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceShippingRule;
use App\Models\CommerceStoreSetting;
use App\Models\CustomerCart;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PublicStorefrontController extends Controller
{
    public function __construct(
        private readonly StorefrontThemeService $themeService
    ) {}

    /**
     * Storefront Homepage (/{slug}).
     */
    public function home(string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'home');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];
        $landingPage = $context['landingPage'];

        $featuredProducts = Product::where('business_id', $business->id)
            ->forStorefront()
            ->goods()
            ->with('category')
            ->orderBy('name')
            ->take(8)
            ->get();

        $services = Product::where('business_id', $business->id)
            ->forStorefront()
            ->services()
            ->with('category')
            ->orderBy('name')
            ->take(6)
            ->get();

        $productCategories = ProductCategory::where('business_id', $business->id)
            ->whereHas('products', fn ($q) => $q->forStorefront())
            ->orderBy('name')
            ->get();

        $recentArticles = [];
        if ($landingPage->isPageActive('blog')) {
            $recentArticles = Post::where('is_published', true)
                ->latest('published_at')
                ->take(3)
                ->get();
        }

        $posTables = PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        return view('public.storefront.home', array_merge($context, compact(
            'featuredProducts',
            'services',
            'productCategories',
            'recentArticles',
            'posTables'
        )));
    }

    /**
     * Dedicated Storefront Catalog (/{slug}/katalog).
     */
    public function catalog(Request $request, string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'catalog');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];

        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $type = $request->query('type', 'all'); // 'all', 'goods', 'service'
        $sort = $request->query('sort', 'popular'); // 'popular', 'price_asc', 'price_desc', 'newest'

        $query = Product::where('business_id', $business->id)
            ->forStorefront()
            ->with('category');

        if ($search !== '') {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                    ->orWhere('description', 'like', "%{$escapedSearch}%");
            });
        }

        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if ($type === 'goods') {
            $query->goods();
        } elseif ($type === 'service') {
            $query->services();
        }

        match ($sort) {
            'price_asc' => $query->orderBy('selling_price', 'asc'),
            'price_desc' => $query->orderBy('selling_price', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderBy('name', 'asc'),
        };

        $products = $query->paginate(16)->withQueryString();

        $categories = ProductCategory::where('business_id', $business->id)
            ->whereHas('products', fn ($q) => $q->forStorefront())
            ->orderBy('name')
            ->get();

        return view('public.storefront.catalog', array_merge($context, compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'type',
            'sort'
        )));
    }

    /**
     * Dedicated Product Detail Page / PDP (/{slug}/produk/{product}).
     */
    public function productDetail(Request $request, string $slug, string $productSlug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'catalog');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];
        $landingPage = $context['landingPage'];

        $product = Product::where('business_id', $business->id)
            ->forStorefront()
            ->where(function ($q) use ($productSlug) {
                $q->where('slug', $productSlug)
                    ->orWhere('id', $productSlug);
            })
            ->with('category')
            ->firstOrFail();

        $relatedProducts = Product::where('business_id', $business->id)
            ->where('id', '!=', $product->id)
            ->forStorefront()
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->take(4)
            ->get();

        // SEO & Rich Social Media OpenGraph Meta Tags
        $canonicalUrl = url("/{$business->slug}/produk/{$product->slug}");
        $ogTitle = "{$product->name} - {$business->name}";
        $rawDesc = strip_tags($product->description ?: ($landingPage->subheadline ?: $business->name));
        $ogDescription = Str::limit($rawDesc, 160);
        $ogImage = $product->image_url ?: ($landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url));
        $ogPrice = (float) $product->selling_price;

        return view('public.storefront.product_detail', array_merge($context, compact(
            'product',
            'relatedProducts',
            'canonicalUrl',
            'ogTitle',
            'ogDescription',
            'ogImage',
            'ogPrice'
        )));
    }

    /**
     * Dedicated Standalone 2-Column Checkout Page (/{slug}/checkout).
     */
    public function checkout(Request $request, string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, null);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];
        $storeSetting = $context['storeSetting'];

        $paymentMethods = CommercePaymentMethod::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $shippingRules = CommerceShippingRule::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $posTables = PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        $pickupLocations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('allow_storefront_pickup', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('public.storefront.checkout', array_merge($context, compact(
            'paymentMethods',
            'shippingRules',
            'posTables',
            'pickupLocations'
        )));
    }

    /**
     * Dedicated About Us Page (/{slug}/tentang-kami).
     */
    public function about(string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'about');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('public.storefront.about', $context);
    }

    /**
     * Dedicated Reservation / Booking Page (/{slug}/reservasi).
     */
    public function reservation(Request $request, string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'reservation');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];

        $posTables = PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        $services = Product::where('business_id', $business->id)
            ->forStorefront()
            ->services()
            ->orderBy('name')
            ->get();

        return view('public.storefront.reservation', array_merge($context, compact(
            'posTables',
            'services'
        )));
    }

    /**
     * Dedicated Contact & Branches Page (/{slug}/kontak).
     */
    public function contact(string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'contact');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('public.storefront.contact', $context);
    }

    /**
     * Dedicated Articles / Blog List Page (/{slug}/artikel).
     */
    public function articles(Request $request, string $slug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'blog');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];
        $articles = Post::where('business_id', $business->id)
            ->where('is_published', true)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.storefront.articles', array_merge($context, compact('articles')));
    }

    /**
     * Dedicated Article Detail Page (/{slug}/artikel/{article_slug}).
     */
    public function articleDetail(Request $request, string $slug, string $articleSlug): View|RedirectResponse
    {
        $context = $this->resolveContext($slug, 'blog');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $business = $context['business'];
        $article = Post::where('business_id', $business->id)
            ->where('slug', $articleSlug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->increment('views_count');

        $relatedArticles = Post::where('business_id', $business->id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.storefront.article_detail', array_merge($context, compact('article', 'relatedArticles')));
    }

    /**
     * Resolve Business, Landing Page, Theme, Settings, and Enforce Auto-Hide Guard.
     *
     * @return array<string, mixed>|RedirectResponse
     */
    private function resolveContext(string $slug, ?string $requiredPage = null): array|RedirectResponse
    {
        $business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $business) {
            $business = Business::where('is_active', true)
                ->get()
                ->first(fn (Business $candidate): bool => Str::slug($candidate->name) === $slug);
        }

        abort_unless($business, 404);

        $landingPage = BusinessLandingPage::where('business_id', $business->id)->first();
        if (! $landingPage) {
            $landingPage = BusinessLandingPage::create([
                'business_id' => $business->id,
                'headline' => $business->name,
                'subheadline' => $business->description ?: 'Selamat datang di toko & etalase resmi ' . $business->name . '.',
                'is_published' => true,
                'show_pos_products' => true,
                'theme_preset' => 'artisan_brew',
                'theme_color' => '#8B5A2B',
                'cta_primary_text' => 'Beli Sekarang',
                'whatsapp_number' => $business->phone ?: '081234567890',
                'active_pages' => [
                    'home' => true,
                    'catalog' => true,
                    'about' => true,
                    'reservation' => false,
                    'contact' => true,
                    'blog' => false,
                    'order_tracking' => true,
                ],
            ]);
        }

        $isAuthorizedPreview = request()->boolean('preview')
            && request()->user()?->active_business_id === $business->id;

        abort_unless($landingPage->is_published || $isAuthorizedPreview, 404);

        // Auto-Hide Dynamic Navigation Guard (§PRD-07 §2.B):
        // If a merchant has disabled this page, automatically redirect customer back to storefront home.
        if ($requiredPage !== null && ! $landingPage->isPageActive($requiredPage)) {
            return redirect()->to('/' . $business->slug)
                ->with('info', 'Halaman yang Anda tuju sedang tidak aktif.');
        }

        // Resolve authentic theme configuration
        $theme = $this->themeService->resolveTheme($landingPage);

        // Storefront settings
        $storeSetting = CommerceStoreSetting::where('business_id', $business->id)->first();
        if (! $storeSetting) {
            $storeSetting = CommerceStoreSetting::create([
                'business_id' => $business->id,
                'is_storefront_enabled' => true,
                'is_discoverable' => true,
                'allow_pickup' => true,
                'allow_delivery' => true,
                'allow_scheduled_order' => true,
                'allow_request_order' => true,
                'min_order_amount' => 10000,
            ]);
        }

        // Available Pre-Order / Batch Dates
        $availableBatchDates = $this->resolveAvailableBatchDates($business, $storeSetting);

        // Customer Cart synchronization if logged in
        $customer = auth('customer')->user();
        $dbCartItems = null;
        if ($customer) {
            $customerCart = CustomerCart::where('global_customer_id', $customer->id)
                ->where('business_id', $business->id)
                ->with(['items.product'])
                ->first();

            if ($customerCart && $customerCart->items->isNotEmpty()) {
                $dbCartItems = $customerCart->items->map(fn ($item) => [
                    'id' => $item->product_id,
                    'name' => $item->product?->name ?? 'Produk',
                    'price' => (float) $item->unit_price,
                    'image_url' => $item->product?->image_url,
                    'quantity' => (float) $item->quantity,
                    'notes' => $item->notes ?? '',
                ])->values()->all();
            }
        }

        return [
            'business' => $business,
            'landingPage' => $landingPage,
            'theme' => $theme,
            'storeSetting' => $storeSetting,
            'availableBatchDates' => $availableBatchDates,
            'dbCartItems' => $dbCartItems,
            'isAuthorizedPreview' => $isAuthorizedPreview,
            'hasWhatsapp' => ! empty($landingPage->whatsapp_number) || ! empty($business->phone),
        ];
    }

    /**
     * Resolve pre-order dates based on store settings.
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveAvailableBatchDates(Business $business, CommerceStoreSetting $storeSetting): array
    {
        $leadTimeHours = (int) ($storeSetting->lead_time_hours ?? 0);
        $earliestAllowed = now()->addHours($leadTimeHours);
        if ($storeSetting->cut_off_time) {
            $cutoffToday = Carbon::parse($storeSetting->cut_off_time);
            if (now()->isAfter($cutoffToday)) {
                $earliestAllowed = $earliestAllowed->addDay();
            }
        }

        $operatingDays = (array) ($storeSetting->operating_days ?? []);
        $dailyQuota = (int) ($storeSetting->daily_order_quota ?? 0);
        $quotaMetric = $storeSetting->quota_metric ?? 'orders';
        $quotaUnit = $storeSetting->preorder_quota_unit ?? 'PCS';
        $batchDatesMode = $storeSetting->batch_dates_mode ?? 'operating_days';
        $customBatchDates = (array) ($storeSetting->custom_batch_dates ?? []);

        $indonesianDays = [
            'sunday' => 'Minggu', 'monday' => 'Senin', 'tuesday' => 'Selasa',
            'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu',
        ];
        $indonesianMonthsShort = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $formatBatch = function (Carbon $date) use ($indonesianDays, $indonesianMonthsShort): array {
            $engDay = strtolower($date->format('l'));
            $dayName = $indonesianDays[$engDay] ?? $date->format('l');
            $m = (int) $date->format('n');
            $shortMonth = $indonesianMonthsShort[$m] ?? $date->format('M');

            return [
                'day_name' => $dayName,
                'formatted_date' => $date->format('d') . ' ' . $shortMonth . ' ' . $date->format('Y'),
                'short_date' => $date->format('d') . ' ' . $shortMonth,
                'is_today' => $date->isToday(),
                'is_tomorrow' => $date->isTomorrow(),
                'relative_label' => $date->isToday() ? 'Hari Ini' : ($date->isTomorrow() ? 'Besok' : null),
            ];
        };

        $availableBatchDates = [];

        if ($batchDatesMode === 'custom_dates' && ! empty($customBatchDates)) {
            foreach ($customBatchDates as $cbd) {
                if (empty($cbd['date'])) {
                    continue;
                }
                try {
                    $cbdDate = Carbon::parse($cbd['date'])->startOfDay();
                } catch (\Throwable) {
                    continue;
                }
                if ($cbdDate->endOfDay()->isBefore($earliestAllowed)) {
                    continue;
                }

                $dateStr = $cbdDate->format('Y-m-d');
                $batchQuota = isset($cbd['quota']) && is_numeric($cbd['quota']) ? (int) $cbd['quota'] : $dailyQuota;

                $usedQuota = 0;
                if ($batchQuota > 0) {
                    if ($quotaMetric === 'quantity') {
                        $usedQuota = (int) CommerceOrderItem::whereHas('order', function ($q) use ($business, $dateStr) {
                            $q->where('business_id', $business->id)
                                ->whereDate('scheduled_date', $dateStr)
                                ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
                        })->sum('quantity');
                    } else {
                        $usedQuota = CommerceOrder::where('business_id', $business->id)
                            ->whereDate('scheduled_date', $dateStr)
                            ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED])
                            ->count();
                    }
                }

                $remainingQuota = $batchQuota > 0 ? max(0, $batchQuota - $usedQuota) : null;
                $isFull = $batchQuota > 0 && $remainingQuota === 0;

                $availableBatchDates[] = [
                    'date' => $dateStr,
                    ...$formatBatch($cbdDate),
                    'remaining_quota' => $remainingQuota,
                    'quota_unit' => $quotaUnit,
                    'is_full' => $isFull,
                    'is_sold_out' => $isFull,
                    'note' => $cbd['note'] ?? null,
                ];
            }
        } else {
            $checkDate = $earliestAllowed->copy()->startOfDay();
            $daysChecked = 0;
            while (count($availableBatchDates) < 7 && $daysChecked < 30) {
                $dayName = strtolower($checkDate->format('l'));
                $isOpenDay = empty($operatingDays) || in_array($dayName, $operatingDays, true);

                if ($isOpenDay) {
                    $dateStr = $checkDate->format('Y-m-d');
                    $usedQuota = 0;
                    if ($dailyQuota > 0) {
                        if ($quotaMetric === 'quantity') {
                            $usedQuota = (int) CommerceOrderItem::whereHas('order', function ($q) use ($business, $dateStr) {
                                $q->where('business_id', $business->id)
                                    ->whereDate('scheduled_date', $dateStr)
                                    ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED]);
                            })->sum('quantity');
                        } else {
                            $usedQuota = CommerceOrder::where('business_id', $business->id)
                                ->whereDate('scheduled_date', $dateStr)
                                ->whereNotIn('status', [CommerceOrder::STATUS_CANCELLED, CommerceOrder::STATUS_EXPIRED])
                                ->count();
                        }
                    }
                    $remainingQuota = $dailyQuota > 0 ? max(0, $dailyQuota - $usedQuota) : null;
                    $isFull = $dailyQuota > 0 && $remainingQuota === 0;

                    $availableBatchDates[] = [
                        'date' => $dateStr,
                        ...$formatBatch($checkDate),
                        'remaining_quota' => $remainingQuota,
                        'quota_unit' => $quotaUnit,
                        'is_full' => $isFull,
                        'is_sold_out' => $isFull,
                        'note' => null,
                    ];
                }
                $checkDate->addDay();
                $daysChecked++;
            }
        }

        return $availableBatchDates;
    }
}
