<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicBusinessLandingController extends Controller
{
    /**
     * Render the public single-page landing for a business tenant.
     */
    public function show(string $slug): View
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
                'theme_color' => '#007AFF',
                'cta_primary_text' => 'Beli Sekarang',
                'whatsapp_number' => $business->phone ?: '081234567890',
            ]);
        }

        $isAuthorizedPreview = request()->boolean('preview')
            && request()->user()?->active_business_id === $business->id;

        abort_unless($landingPage->is_published || $isAuthorizedPreview, 404);

        // 0. Storefront settings & active payment methods & shipping rules
        $storeSetting = \App\Models\CommerceStoreSetting::where('business_id', $business->id)->first();
        if (! $storeSetting) {
            $storeSetting = \App\Models\CommerceStoreSetting::create([
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

        $paymentMethods = \App\Models\CommercePaymentMethod::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $shippingRules = \App\Models\CommerceShippingRule::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $posTables = \App\Models\PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        // Hitung daftar tanggal batch buka PO yang valid untuk pesanan terjadwal / pre-order FnB
        $leadTimeHours = (int) ($storeSetting->lead_time_hours ?? 0);
        $earliestAllowed = now()->addHours($leadTimeHours);
        if ($storeSetting->cut_off_time) {
            $cutoffToday = \Carbon\Carbon::parse($storeSetting->cut_off_time);
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

        $availableBatchDates = [];

        // Kamus lokalisasi hari dan bulan bahasa Indonesia deterministik
        $indonesianDays = [
            'sunday' => 'Minggu',
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
        ];
        $indonesianMonthsShort = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $indonesianMonthsFull = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $formatBatchInfo = function (\Carbon\Carbon $date) use ($indonesianDays, $indonesianMonthsShort, $indonesianMonthsFull): array {
            $engDay = strtolower($date->format('l'));
            $dayName = $indonesianDays[$engDay] ?? $date->format('l');
            $m = (int) $date->format('n');
            $shortMonth = $indonesianMonthsShort[$m] ?? $date->format('M');
            $fullMonth = $indonesianMonthsFull[$m] ?? $date->format('F');

            return [
                'day_name' => $dayName,
                'day_name_upper' => strtoupper($dayName),
                'formatted_date' => $date->format('d') . ' ' . $shortMonth . ' ' . $date->format('Y'),
                'full_date' => $dayName . ', ' . $date->format('d') . ' ' . $fullMonth . ' ' . $date->format('Y'),
                'short_date' => $date->format('d') . ' ' . $shortMonth,
                'day_short' => $date->format('d') . ' ' . $shortMonth,
                'is_today' => $date->isToday(),
                'is_tomorrow' => $date->isTomorrow(),
                'relative_label' => $date->isToday() ? 'Hari Ini' : ($date->isTomorrow() ? 'Besok' : null),
            ];
        };

        if ($batchDatesMode === 'custom_dates' && ! empty($customBatchDates)) {
            foreach ($customBatchDates as $cbd) {
                if (empty($cbd['date'])) {
                    continue;
                }
                try {
                    $cbdDate = \Carbon\Carbon::parse($cbd['date'])->startOfDay();
                } catch (\Throwable $e) {
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
                        $usedQuota = (int) \App\Models\CommerceOrderItem::whereHas('order', function ($q) use ($business, $dateStr) {
                            $q->where('business_id', $business->id)
                                ->whereDate('scheduled_date', $dateStr)
                                ->whereNotIn('status', [\App\Models\CommerceOrder::STATUS_CANCELLED, \App\Models\CommerceOrder::STATUS_EXPIRED]);
                        })->sum('quantity');
                    } else {
                        $usedQuota = \App\Models\CommerceOrder::where('business_id', $business->id)
                            ->whereDate('scheduled_date', $dateStr)
                            ->whereNotIn('status', [\App\Models\CommerceOrder::STATUS_CANCELLED, \App\Models\CommerceOrder::STATUS_EXPIRED])
                            ->count();
                    }
                }

                $remainingQuota = $batchQuota > 0 ? max(0, $batchQuota - $usedQuota) : null;
                $isFull = $batchQuota > 0 && $remainingQuota === 0;

                $info = $formatBatchInfo($cbdDate);

                $availableBatchDates[] = [
                    'date' => $dateStr,
                    ...$info,
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
                            $usedQuota = (int) \App\Models\CommerceOrderItem::whereHas('order', function ($q) use ($business, $dateStr) {
                                $q->where('business_id', $business->id)
                                    ->whereDate('scheduled_date', $dateStr)
                                    ->whereNotIn('status', [\App\Models\CommerceOrder::STATUS_CANCELLED, \App\Models\CommerceOrder::STATUS_EXPIRED]);
                            })->sum('quantity');
                        } else {
                            $usedQuota = \App\Models\CommerceOrder::where('business_id', $business->id)
                                ->whereDate('scheduled_date', $dateStr)
                                ->whereNotIn('status', [\App\Models\CommerceOrder::STATUS_CANCELLED, \App\Models\CommerceOrder::STATUS_EXPIRED])
                                ->count();
                        }
                    }
                    $remainingQuota = $dailyQuota > 0 ? max(0, $dailyQuota - $usedQuota) : null;
                    $isFull = $dailyQuota > 0 && $remainingQuota === 0;

                    $info = $formatBatchInfo($checkDate);

                    $availableBatchDates[] = [
                        'date' => $dateStr,
                        ...$info,
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

        // Resolusi Join Pre-Order / Pesanan Kolektif Kantoran via Shareable Link / Group Order Session
        $requestedBatch = request()->query('batch');
        $groupRef = trim((string) (request()->query('group') ?? request()->query('kantor') ?? request()->query('team') ?? ''));
        $groupOrderToken = trim((string) (request()->query('group_order') ?? ''));
        $activeGroupOrder = null;

        if ($groupOrderToken !== '') {
            $activeGroupOrder = \App\Models\CommerceGroupOrder::where('business_id', $business->id)
                ->where('share_token', $groupOrderToken)
                ->with(['host', 'items.product', 'order'])
                ->first();

            if ($activeGroupOrder) {
                if (empty($groupRef)) {
                    $groupRef = $activeGroupOrder->title;
                }
                if ($activeGroupOrder->scheduled_date) {
                    $requestedBatch = $activeGroupOrder->scheduled_date->toDateString();
                }
            }
        }

        $isJoinPoRequested = request()->boolean('join_po') || ! empty($requestedBatch) || ! empty($groupRef) || ! empty($groupOrderToken);

        $selectedBatch = null;
        $selectedBatchDate = null;
        if (! empty($availableBatchDates)) {
            if ($requestedBatch) {
                foreach ($availableBatchDates as $batchCandidate) {
                    if ($batchCandidate['date'] === $requestedBatch) {
                        $selectedBatch = $batchCandidate;
                        $selectedBatchDate = $batchCandidate['date'];
                        break;
                    }
                }
            }
            if (! $selectedBatch) {
                $selectedBatch = $availableBatchDates[0];
                $selectedBatchDate = $availableBatchDates[0]['date'];
            }
        }

        // 1. Physical Goods catalog (Produk Fisik)
        $posProducts = Product::where('business_id', $business->id)
            ->forStorefront()
            ->goods()
            ->with('category')
            ->orderBy('name')
            ->get();

        $productCategories = ProductCategory::where('business_id', $business->id)
            ->whereHas('products', fn ($query) => $query->forStorefront()->goods())
            ->orderBy('name')
            ->get();

        $productPayload = $posProducts->map(fn (Product $product): array => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => ($product->show_price_on_web ?? true) ? (float) $product->selling_price : 0.0,
            'raw_price' => (float) $product->selling_price,
            'show_price_on_web' => (bool) ($product->show_price_on_web ?? true),
            'is_preorder' => (bool) ($product->is_preorder ?? false),
            'preorder_mode' => (string) ($product->preorder_mode ?? 'customer_schedule'),
            'preorder_lead_days' => (int) ($product->preorder_lead_days ?? 1),
            'image_url' => $product->image_url,
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
            'type' => 'goods',
            'stock' => $product->calculateEffectiveAvailableStock(),
        ])->values();

        // 2. Services & Layanan: Sinkronkan otomatis dari master katalog Jasa & Layanan (/services)
        $dbServices = Product::where('business_id', $business->id)
            ->forStorefront()
            ->services()
            ->with('category')
            ->orderBy('name')
            ->get();

        $serviceCategories = ProductCategory::where('business_id', $business->id)
            ->whereHas('products', fn ($query) => $query->forStorefront()->services())
            ->orderBy('name')
            ->get();

        $mappedDbServices = $dbServices->map(fn (Product $p): array => [
            'id' => $p->id,
            'title' => $p->name,
            'name' => $p->name,
            'description' => $p->description ?: 'Layanan profesional terpercaya.',
            'price' => (($p->show_price_on_web ?? true) && $p->selling_price > 0) ? 'Rp ' . number_format((float) $p->selling_price, 0, ',', '.') : 'Hubungi kami',
            'raw_price' => (float) $p->selling_price,
            'show_price_on_web' => (bool) ($p->show_price_on_web ?? true),
            'is_preorder' => (bool) ($p->is_preorder ?? false),
            'preorder_mode' => (string) ($p->preorder_mode ?? 'customer_schedule'),
            'preorder_lead_days' => (int) ($p->preorder_lead_days ?? 1),
            'image_url' => $p->image_url,
            'category_id' => $p->category_id,
            'category' => $p->category?->name,
            'icon' => 'sparkles',
            'badge' => 'Layanan',
            'type' => 'service',
            'stock' => null,
        ])->all();

        $customServices = $landingPage->custom_services ?? [];
        $services = collect(array_merge($mappedDbServices, $customServices))->map(fn (array $service): array => [
            ...$service,
            'description' => $service['description'] ?? $service['desc'] ?? '',
        ]);

        $customer = auth('customer')->user();
        $dbCartItems = null;
        $openCheckoutModal = false;

        if ($customer) {
            $customerCart = \App\Models\CustomerCart::where('global_customer_id', $customer->id)
                ->where('business_id', $business->id)
                ->with(['items.product'])
                ->first();

            if ($customerCart && $customerCart->items->isNotEmpty()) {
                $dbCartItems = $customerCart->items->map(fn ($item) => [
                    'id'        => $item->product_id,
                    'name'      => $item->product?->name ?? 'Produk',
                    'price'     => (float) $item->unit_price,
                    'image_url' => $item->product?->image_url,
                    'quantity'  => (float) $item->quantity,
                    'notes'     => $item->notes ?? '',
                ])->values()->all();
            }

            if (session()->has("customer_cart_checkout_{$business->id}")) {
                $openCheckoutModal = true;
                session()->forget("customer_cart_checkout_{$business->id}");
            }
        }

        return view('public.business_landing', compact(
            'business',
            'landingPage',
            'services',
            'serviceCategories',
            'posProducts',
            'productCategories',
            'productPayload',
            'storeSetting',
            'paymentMethods',
            'shippingRules',
            'posTables',
            'dbCartItems',
            'openCheckoutModal',
            'availableBatchDates',
            'selectedBatchDate',
            'selectedBatch',
            'groupRef',
            'isJoinPoRequested',
            'activeGroupOrder',
            'groupOrderToken'
        ));
    }
}
