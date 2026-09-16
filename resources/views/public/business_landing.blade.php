<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO METADATA --}}
    <title>{{ $landingPage->meta_title ?: $business->name }}</title>
    @if ($landingPage->meta_description ?: $landingPage->subheadline)
        <meta name="description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">
    @endif
    @if ($landingPage->meta_keywords)
        <meta name="keywords" content="{{ $landingPage->meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Social Sharing --}}
    <meta property="og:type" content="business.business">
    <meta property="og:title" content="{{ $landingPage->meta_title ?: $business->name }}">
    @if ($landingPage->meta_description ?: $landingPage->subheadline)
        <meta property="og:description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $business->name }}">
    @if ($landingPage->og_image_url || $landingPage->hero_image_url || $business->logo_url)
        <meta property="og:image"
            content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $landingPage->meta_title ?: $business->name }}">
    @if ($landingPage->meta_description ?: $landingPage->subheadline)
        <meta name="twitter:description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">
    @endif
    @if ($landingPage->og_image_url || $landingPage->hero_image_url || $business->logo_url)
        <meta name="twitter:image"
            content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}">
    @endif

    {{-- Schema.org JSON-LD LocalBusiness Structured Data --}}
    @php
        $schemaType = match (true) {
            str_starts_with((string) $business->template_code, 'fnb') => 'Restaurant',
            str_starts_with((string) $business->template_code, 'retail') => 'Store',
            str_starts_with((string) $business->template_code, 'service_salon'),
            str_starts_with((string) $business->template_code, 'service_barber')
                => 'BeautySalon',
            str_starts_with((string) $business->template_code, 'service_workshop') => 'AutoRepair',
            default => 'LocalBusiness',
        };

        $localBusinessSchema = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $business->name,
            'description' => $landingPage->meta_description ?: ($landingPage->subheadline ?: $business->description),
            'url' => url()->current(),
            'telephone' => $landingPage->whatsapp_number ?: ($business->phone ?: null),
            'email' => $business->email ?: null,
            'priceRange' => 'Rp',
            'currenciesAccepted' => $business->currency_code ?: 'IDR',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $landingPage->contact_address ?: ($business->address ?: 'Indonesia'),
                'addressCountry' => 'ID',
            ],
        ];

        if ($business->logo_url || $landingPage->hero_image_url) {
            $localBusinessSchema['image'] = $business->logo_url ?: $landingPage->hero_image_url;
        }

        if (!empty($landingPage->opening_hours) && is_array($landingPage->opening_hours)) {
            $hours = [];
            foreach ($landingPage->opening_hours as $day => $config) {
                if (is_array($config) && !empty($config['open']) && !empty($config['close'])) {
                    $hours[] = ucfirst((string) $day) . ' ' . $config['open'] . '-' . $config['close'];
                }
            }
            if (!empty($hours)) {
                $localBusinessSchema['openingHours'] = $hours;
            }
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode(array_filter($localBusinessSchema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Fonts: System Fonts & Inter fallback --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Anti-FOUC Theme Script --}}
    <script>
        (function() {
            try {
                var isLandingDark = @json((bool) ($landingPage->dark_mode ?? false));
                var stored = localStorage.getItem('cooca-theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (isLandingDark || stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <?php
    $themeColor = $landingPage->theme_color ?: '#007AFF';
    $hex = ltrim($themeColor, '#');
    if (strlen($hex) === 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } elseif (strlen($hex) === 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    } else {
        $r = 0;
        $g = 122;
        $b = 255;
    }
    $themeRgb = "{$r}, {$g}, {$b}";
    
    $savedSectionVisibility = $landingPage->section_visibility ?? [];
    $services = isset($services) && $services instanceof \Illuminate\Support\Collection ? $services : collect($landingPage->custom_services ?? [])->map(fn(array $service): array => [...$service, 'description' => $service['description'] ?? ($service['desc'] ?? '')]);
    $testimonials = collect($landingPage->testimonials ?? [])->map(fn(array $testimonial): array => [...$testimonial, 'quote' => $testimonial['quote'] ?? ($testimonial['comment'] ?? '')]);
    $faqs = collect($landingPage->faqs ?? [])->map(
        fn(array $faq): array => [
            'question' => $faq['question'] ?? ($faq['q'] ?? ''),
            'answer' => $faq['answer'] ?? ($faq['a'] ?? ''),
        ],
    );
    $allGalleryImages = collect($landingPage->gallery_images ?? [])
        ->map(function ($img, $idx) {
            if (is_array($img)) {
                return [
                    'id' => $idx,
                    'url' => $img['url'] ?? '',
                    'caption' => $img['caption'] ?? '',
                ];
            }
            return [
                'id' => $idx,
                'url' => (string) $img,
                'caption' => '',
            ];
        })
        ->filter(fn($item) => filled($item['url']))
        ->values();
    $bentoGalleryImages = $allGalleryImages->take(5);
    $totalGalleryCount = $allGalleryImages->count();
    $extraGalleryCount = max(0, $totalGalleryCount - 5);
    
    $hasHero = filled($landingPage->headline) || filled($landingPage->subheadline) || filled($landingPage->announcement_badge) || filled($landingPage->hero_image_url) || filled($landingPage->cta_primary_text) || filled($landingPage->cta_secondary_text);
    $hasAbout = filled($landingPage->about_title) || filled($landingPage->about_story) || filled($landingPage->about_image_url) || !empty($landingPage->operational_hours);
    $hasServices = $services->isNotEmpty() || ($landingPage->show_pos_products && $posProducts->isNotEmpty());
    $hasContact = filled($landingPage->custom_address) || filled($business->address) || filled($landingPage->custom_phone) || filled($landingPage->whatsapp_number) || filled($business->phone) || filled($landingPage->custom_email) || filled($business->email) || filled($landingPage->google_maps_embed_url) || !empty($landingPage->social_links);
    $sectionVisibility = array_merge(
        [
            'hero' => $hasHero,
            'about' => $hasAbout,
            'products' => $landingPage->show_pos_products && $posProducts->isNotEmpty(),
            'services' => $services->isNotEmpty(),
            'gallery' => $allGalleryImages->isNotEmpty(),
            'testimonials' => $testimonials->isNotEmpty(),
            'faq' => $faqs->isNotEmpty(),
            'contact' => $hasContact,
            'footer' => $hasContact || $services->isNotEmpty() || $landingPage->logo_url || $business->logo_url,
            'footer_brand' => filled($business->name) || filled($landingPage->logo_url) || filled($business->logo_url),
            'footer_navigation' => true,
            'footer_services' => $services->isNotEmpty(),
            'footer_contact' => $hasContact,
        ],
        $savedSectionVisibility,
    );
    $footerGridKeys = ['footer_brand', 'footer_navigation', 'footer_services', 'footer_contact'];
    $footerGridCount = collect($footerGridKeys)->filter(fn(string $key): bool => $sectionVisibility[$key])->count();
    $footerGridClass = match ($footerGridCount) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        default => 'grid-cols-4',
    };
    
    $channelConfigs = [
        ['key' => 'instagram', 'label' => 'Instagram', 'icon' => 'instagram', 'fallback' => $landingPage->instagram_handle, 'color' => 'hover:bg-gradient-to-tr hover:from-amber-500 hover:via-rose-500 hover:to-purple-600 hover:text-white'],
        ['key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'video', 'fallback' => $landingPage->tiktok_handle, 'color' => 'hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black'],
        ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'facebook', 'fallback' => $landingPage->facebook_url, 'color' => 'hover:bg-[#1877F2] hover:text-white'],
        ['key' => 'youtube', 'label' => 'YouTube', 'icon' => 'youtube', 'fallback' => null, 'color' => 'hover:bg-[#FF0000] hover:text-white'],
        ['key' => 'twitter', 'label' => 'X (Twitter)', 'icon' => 'twitter', 'fallback' => null, 'color' => 'hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black'],
        ['key' => 'linkedin', 'label' => 'LinkedIn', 'icon' => 'linkedin', 'fallback' => null, 'color' => 'hover:bg-[#0A66C2] hover:text-white'],
        ['key' => 'shopee', 'label' => 'Shopee', 'icon' => 'shopping-bag', 'fallback' => null, 'color' => 'hover:bg-[#EE4D2D] hover:text-white'],
        ['key' => 'tokopedia', 'label' => 'Tokopedia', 'icon' => 'store', 'fallback' => null, 'color' => 'hover:bg-[#03AC0E] hover:text-white'],
        ['key' => 'gofood', 'label' => 'GoFood', 'icon' => 'utensils', 'fallback' => null, 'color' => 'hover:bg-[#EE2737] hover:text-white'],
        ['key' => 'grabfood', 'label' => 'GrabFood', 'icon' => 'bike', 'fallback' => null, 'color' => 'hover:bg-[#00B14F] hover:text-white'],
        ['key' => 'lazada', 'label' => 'Lazada', 'icon' => 'shopping-cart', 'fallback' => null, 'color' => 'hover:bg-[#0F146D] hover:text-white'],
        ['key' => 'blibli', 'label' => 'Blibli', 'icon' => 'package', 'fallback' => null, 'color' => 'hover:bg-[#0095DA] hover:text-white'],
    ];
    
    $activeChannels = collect($channelConfigs)
        ->filter(function ($c) use ($landingPage) {
            $val = data_get($landingPage->social_links, $c['key']) ?: $c['fallback'];
            return filled($val);
        })
        ->map(function ($c) use ($landingPage) {
            $raw = trim((string) (data_get($landingPage->social_links, $c['key']) ?: $c['fallback']));
            if ($c['key'] === 'instagram' && !str_starts_with($raw, 'http')) {
                $c['url'] = 'https://instagram.com/' . ltrim($raw, '@');
            } elseif ($c['key'] === 'tiktok' && !str_starts_with($raw, 'http')) {
                $c['url'] = 'https://tiktok.com/@' . ltrim($raw, '@');
            } elseif (!str_starts_with($raw, 'http')) {
                $c['url'] = 'https://' . ltrim($raw, '/');
            } else {
                $c['url'] = $raw;
            }
            return $c;
        })
        ->values();
    
    $hasWhatsapp = filled($landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone));
    ?>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"SF Pro Text"', '"Inter"',
                            'system-ui', 'sans-serif'
                        ],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '{{ $themeColor }}',
                            primary: '{{ $themeColor }}',
                            50: 'rgba({{ $themeRgb }}, 0.05)',
                            100: 'rgba({{ $themeRgb }}, 0.1)',
                            500: '{{ $themeColor }}',
                            600: '{{ $themeColor }}',
                        },
                        apple: {
                            blue: '#007AFF',
                            green: '#34C759',
                            orange: '#FF9500',
                            red: '#FF3B30',
                            purple: '#AF52DE',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js & Lucide Icons --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        window.makeSlider = function(totalItems, perViewConfig, intervalMs = 3500) {
            return {
                total: totalItems,
                currentIndex: 0,
                perView: 1,
                timer: null,
                init() {
                    this.updatePerView();
                    window.addEventListener('resize', () => this.updatePerView());
                    this.start();
                },
                updatePerView() {
                    const w = window.innerWidth;
                    if (w >= 1024) {
                        this.perView = perViewConfig.lg || 3;
                    } else if (w >= 640) {
                        this.perView = perViewConfig.sm || 2;
                    } else {
                        this.perView = perViewConfig.base || 1;
                    }
                    if (this.currentIndex > this.maxIndex()) {
                        this.currentIndex = 0;
                    }
                },
                maxIndex() {
                    return Math.max(0, this.total - this.perView);
                },
                next() {
                    if (this.maxIndex() <= 0) return;
                    if (this.currentIndex >= this.maxIndex()) {
                        this.currentIndex = 0;
                    } else {
                        this.currentIndex++;
                    }
                },
                prev() {
                    if (this.maxIndex() <= 0) return;
                    if (this.currentIndex <= 0) {
                        this.currentIndex = this.maxIndex();
                    } else {
                        this.currentIndex--;
                    }
                },
                goTo(index) {
                    this.currentIndex = Math.min(Math.max(0, index), this.maxIndex());
                },
                start() {
                    if (this.total <= this.perView) return;
                    this.stop();
                    this.timer = setInterval(() => {
                        this.next();
                    }, intervalMs);
                },
                stop() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                }
            };
        };

        window.landingPageState = function() {
            return {
                mobileMenuOpen: false,
                waChatOpen: false,
                activeModal: null,
                activeItem: null,
                activeSection: 'hero',
                productCategory: 'all',
                productSearch: '',
                products: {{ Js::from($productPayload) }},
                filteredProducts: {{ Js::from($productPayload) }},
                serviceSearch: '',
                services: {{ Js::from($services) }},
                filteredServices: {{ Js::from($services) }},
                filterProducts() {
                    let products = this.products;
                    if (this.productCategory !== 'all') {
                        products = products.filter(product => String(product.category_id) === this.productCategory);
                    }
                    if (this.productSearch.trim()) {
                        const query = this.productSearch.trim().toLowerCase();
                        products = products.filter(product => product.name.toLowerCase().includes(query) || (product
                            .description || '').toLowerCase().includes(query));
                    }
                    this.filteredProducts = products;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                filterServices() {
                    let list = this.services;
                    if (this.serviceSearch.trim()) {
                        const query = this.serviceSearch.trim().toLowerCase();
                        list = list.filter(s => (s.title || '').toLowerCase().includes(query) || (s.description || '')
                            .toLowerCase().includes(query));
                    }
                    this.filteredServices = list;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                formatPrice(price) {
                    return 'Rp ' + Number(price || 0).toLocaleString('id-ID');
                },
                waLink(label, isService = false) {
                    const actionText = isService ? 'saya ingin booking / reservasi layanan: ' :
                        'saya tertarik untuk memesan produk: ';
                    return '{{ $landingPage->getWhatsAppUrl() }}' + '&text=' + encodeURIComponent('Halo ' +
                        @json($business->name) + ', ' + actionText + (label || ''));
                },
                modalQty: 1,
                toastMessage: null,
                toastTimeout: null,
                showToast(msg) {
                    this.toastMessage = msg;
                    clearTimeout(this.toastTimeout);
                    this.toastTimeout = setTimeout(() => {
                        this.toastMessage = null;
                    }, 3000);
                },
                openProduct(product) {
                    this.activeItem = product;
                    this.modalQty = 1;
                    this.activeModal = 'product';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                openService(service) {
                    this.activeItem = service;
                    this.modalQty = 1;
                    this.activeModal = 'service';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                // Storefront & Shopping Cart Engine
                isStorefrontEnabled: {{ json_encode($storeSetting?->is_storefront_enabled ?? true) }},
                minOrderAmount: {{ (float) ($storeSetting?->min_order_amount ?? 0) }},
                allowPickup: {{ json_encode($storeSetting?->allow_pickup ?? true) }},
                allowDelivery: {{ json_encode($storeSetting?->allow_delivery ?? true) }},
                allowScheduledOrder: {{ json_encode($storeSetting?->allow_scheduled_order ?? false) }},
                allowRequestOrder: {{ json_encode($storeSetting?->allow_request_order ?? false) }},
                allowReservation: {{ json_encode($storeSetting?->allow_reservation ?? true) }},
                cartDrawerOpen: false,
                checkoutModalOpen: {{ json_encode($openCheckoutModal ?? false) }},
                requestOrderModalOpen: false,
                reservationModalOpen: false,
                isSubmittingReservation: false,
                reservationSuccess: false,
                reservationError: null,
                isCheckingOut: false,
                checkoutError: null,
                cart: @json($dbCartItems ?? null) || JSON.parse(localStorage.getItem(
                    'cooca_cart_{{ $business->id }}') || '[]'),
                @php
                    $authCust = auth('customer')->user();
                @endphp
                checkoutForm: {
                    customer_name: @json($authCust?->name ?? ''),
                    customer_phone: @json($authCust?->phone ?? ''),
                    customer_email: @json($authCust?->email ?? ''),
                    fulfillment_type: '{{ $storeSetting?->allow_pickup ?? true ? 'pickup' : 'merchant_delivery' }}',
                    shipping_address: @json($authCust?->shipping_address ?? ''),
                    shipping_rule_id: '',
                    shipping_fee: 0,
                    is_free_shipping: false,
                    shipping_options: [],
                    is_loading_shipping: false,
                    payment_method_id: '{{ $paymentMethods->first()?->id ?? '' }}',
                    notes: '',
                    is_scheduled: false,
                    scheduled_date: '',
                    scheduled_time_slot: '',
                },
                requestOrderForm: {
                    customer_name: @json($authCust?->name ?? ''),
                    customer_phone: @json($authCust?->phone ?? ''),
                    customer_email: @json($authCust?->email ?? ''),
                    fulfillment_type: '{{ $storeSetting?->allow_pickup ?? true ? 'pickup' : 'merchant_delivery' }}',
                    shipping_address: @json($authCust?->shipping_address ?? ''),
                    scheduled_date: '',
                    scheduled_time_slot: '',
                    notes: '',
                    item_name: '',
                    item_qty: 1,
                    item_notes: '',
                    is_submitting: false,
                    error: null,
                },
                reservationForm: {
                    customer_name: @json($authCust?->name ?? ''),
                    customer_phone: @json($authCust?->phone ?? ''),
                    customer_email: @json($authCust?->email ?? ''),
                    reservation_date: '{{ date('Y-m-d') }}',
                    time_slot: '12:00 - 14:00',
                    guest_count: 2,
                    notes: '',
                },
                saveCart() {
                    localStorage.setItem('cooca_cart_{{ $business->id }}', JSON.stringify(this.cart));
                },
                directCheckout(product, qty = 1) {
                    if (!product) return;
                    this.addToCart(product, qty, false);
                    this.checkoutModalOpen = true;
                },
                addToCart(product, qty = 1, openDrawer = false) {
                    if (!product) return;
                    const existing = this.cart.find(item => item.id === product.id);
                    if (existing) {
                        existing.quantity += qty;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name || product.title,
                            price: Number(product.price || product.raw_price || 0),
                            image_url: product.image_url || null,
                            quantity: qty,
                            notes: ''
                        });
                    }
                    this.saveCart();
                    if (this.checkoutForm.fulfillment_type === 'merchant_delivery') {
                        this.fetchShippingQuote();
                    }
                    this.showToast((product.name || product.title) + ' ditambahkan ke keranjang');
                    if (openDrawer) {
                        this.cartDrawerOpen = true;
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                updateQuantity(productId, delta) {
                    const item = this.cart.find(i => i.id === productId);
                    if (!item) return;
                    item.quantity += delta;
                    if (item.quantity <= 0) {
                        this.removeFromCart(productId);
                    } else {
                        this.saveCart();
                        if (this.checkoutForm.fulfillment_type === 'merchant_delivery') {
                            this.fetchShippingQuote();
                        }
                    }
                },
                removeFromCart(productId) {
                    this.cart = this.cart.filter(i => i.id !== productId);
                    this.saveCart();
                    if (this.checkoutForm.fulfillment_type === 'merchant_delivery') {
                        this.fetchShippingQuote();
                    }
                },
                clearCart() {
                    this.cart = [];
                    this.saveCart();
                },
                get cartCount() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },
                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                get grandTotal() {
                    const fee = (this.checkoutForm.fulfillment_type === 'merchant_delivery') ? Number(this
                        .checkoutForm.shipping_fee || 0) : 0;
                    return this.cartTotal + fee;
                },
                async setFulfillment(type) {
                    this.checkoutForm.fulfillment_type = type;
                    if (type === 'merchant_delivery') {
                        await this.fetchShippingQuote();
                    } else {
                        this.checkoutForm.shipping_fee = 0;
                        this.checkoutForm.is_free_shipping = false;
                    }
                },
                async fetchShippingQuote(preferredRuleId = null) {
                    if (this.checkoutForm.fulfillment_type !== 'merchant_delivery') return;
                    this.checkoutForm.is_loading_shipping = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '';
                        const response = await fetch(
                            '{{ route('public.storefront.shipping.calculate', $business->slug) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    subtotal: this.cartTotal,
                                    shipping_rule_id: preferredRuleId || this.checkoutForm
                                        .shipping_rule_id || null
                                })
                            });
                        const resData = await response.json();
                        if (response.ok && resData.success && resData.data) {
                            this.checkoutForm.shipping_options = resData.data.options || [];
                            this.checkoutForm.shipping_fee = Number(resData.data.shipping_fee || 0);
                            this.checkoutForm.is_free_shipping = Boolean(resData.data.is_free);
                            if (resData.data.applied_rule_id) {
                                this.checkoutForm.shipping_rule_id = resData.data.applied_rule_id;
                            }
                        }
                    } catch (err) {
                        console.error('Failed to calculate shipping:', err);
                    } finally {
                        this.checkoutForm.is_loading_shipping = false;
                    }
                },
                async submitCheckout() {
                    if (this.cart.length === 0) return;
                    if (this.cartTotal < this.minOrderAmount) {
                        this.checkoutError = 'Total belanja minimal ' + this.formatPrice(this.minOrderAmount);
                        return;
                    }
                    this.isCheckingOut = true;
                    this.checkoutError = null;

                    const payload = {
                        customer_name: this.checkoutForm.customer_name,
                        customer_phone: this.checkoutForm.customer_phone,
                        customer_email: this.checkoutForm.customer_email || null,
                        fulfillment_type: this.checkoutForm.fulfillment_type,
                        shipping_address: this.checkoutForm.fulfillment_type === 'merchant_delivery' ? this
                            .checkoutForm.shipping_address : null,
                        shipping_rule_id: this.checkoutForm.fulfillment_type === 'merchant_delivery' ? (this
                            .checkoutForm.shipping_rule_id || null) : null,
                        payment_method_id: this.checkoutForm.payment_method_id || null,
                        notes: this.checkoutForm.notes || null,
                        scheduled_date: this.checkoutForm.is_scheduled ? (this.checkoutForm.scheduled_date ||
                            null) : null,
                        scheduled_time_slot: this.checkoutForm.is_scheduled ? (this.checkoutForm
                            .scheduled_time_slot || null) : null,
                        items: this.cart.map(item => ({
                            product_id: item.id,
                            quantity: item.quantity,
                            notes: item.notes || null
                        }))
                    };

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '';
                        const response = await fetch(
                        '{{ route('public.storefront.checkout', $business->slug) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.clearCart();
                            window.location.href = data.order.tracking_url;
                        } else {
                            if (response.status === 401) {
                                window.location.href = '{{ route('customer.auth.google') }}?redirect=' +
                                    encodeURIComponent(window.location.href);
                                return;
                            }
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                                return;
                            }
                            this.checkoutError = data.message || 'Terjadi kesalahan saat memproses pesanan.';
                        }
                    } catch (e) {
                        this.checkoutError = 'Koneksi bermasalah. Silakan periksa jaringan Anda.';
                    } finally {
                        this.isCheckingOut = false;
                    }
                },
                async submitRequestOrder() {
                    this.requestOrderForm.is_submitting = true;
                    this.requestOrderForm.error = null;

                    const itemName = this.requestOrderForm.item_name || 'Pesanan Khusus / Custom Request';
                    const payload = {
                        customer_name: this.requestOrderForm.customer_name,
                        customer_phone: this.requestOrderForm.customer_phone,
                        customer_email: this.requestOrderForm.customer_email || null,
                        fulfillment_type: this.requestOrderForm.fulfillment_type,
                        shipping_address: this.requestOrderForm.fulfillment_type === 'merchant_delivery' ? this
                            .requestOrderForm.shipping_address : null,
                        scheduled_date: this.requestOrderForm.scheduled_date || null,
                        scheduled_time_slot: this.requestOrderForm.scheduled_time_slot || null,
                        notes: this.requestOrderForm.notes || null,
                        items: [{
                            product_name: itemName,
                            quantity: Number(this.requestOrderForm.item_qty || 1),
                            notes: this.requestOrderForm.item_notes || null
                        }]
                    };

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '';
                        const response = await fetch(
                            '{{ route('public.storefront.request_order', $business->slug) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify(payload)
                            });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.requestOrderModalOpen = false;
                            window.location.href = data.order.tracking_url;
                        } else {
                            if (response.status === 401) {
                                window.location.href = '{{ route('customer.auth.google') }}?redirect=' +
                                    encodeURIComponent(window.location.href);
                                return;
                            }
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                                return;
                            }
                            this.requestOrderForm.error = data.message ||
                                'Gagal mengirim permintaan pesanan khusus.';
                        }
                    } catch (e) {
                        this.requestOrderForm.error = 'Koneksi bermasalah. Silakan periksa jaringan Anda.';
                    } finally {
                        this.requestOrderForm.is_submitting = false;
                    }
                },
                async submitReservation() {
                    this.isSubmittingReservation = true;
                    this.reservationError = null;

                    const payload = {
                        customer_name: this.reservationForm.customer_name,
                        customer_phone: this.reservationForm.customer_phone,
                        customer_email: this.reservationForm.customer_email || null,
                        reservation_date: this.reservationForm.reservation_date,
                        time_slot: this.reservationForm.time_slot,
                        guest_count: Number(this.reservationForm.guest_count || 2),
                        notes: this.reservationForm.notes || null,
                    };

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '';
                        const response = await fetch(
                            '{{ route('public.storefront.reservation.submit', $business->slug) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify(payload)
                            });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.reservationSuccess = true;
                            this.showToast('Reservasi berhasil dikirim!');
                        } else {
                            if (response.status === 401) {
                                window.location.href = '{{ route('customer.auth.google') }}?redirect=' +
                                    encodeURIComponent(window.location.href);
                                return;
                            }
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                                return;
                            }
                            this.reservationError = data.message || 'Gagal mengirim formulir reservasi.';
                        }
                    } catch (e) {
                        this.reservationError = 'Koneksi bermasalah. Silakan periksa jaringan Anda.';
                    } finally {
                        this.isSubmittingReservation = false;
                    }
                },
                openReservationModal(serviceTitle = '', productId = null) {
                    if (serviceTitle) {
                        this.reservationForm.notes = 'Layanan yang dipilih: ' + serviceTitle;
                    }
                    if (productId) {
                        this.reservationForm.product_id = productId;
                    }
                    this.reservationSuccess = false;
                    this.reservationError = null;
                    this.reservationModalOpen = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                // Gallery Lightbox & Modal Controls
                galleryImages: {{ Js::from($allGalleryImages) }},
                activeGalleryIndex: 0,
                openAllGallery() {
                    this.activeModal = 'all-gallery';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                openGalleryLightbox(idx = 0) {
                    if (!this.galleryImages.length) return;
                    this.activeGalleryIndex = Math.max(0, Math.min(idx, this.galleryImages.length - 1));
                    this.activeModal = 'gallery-lightbox';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                nextGalleryImage() {
                    if (this.galleryImages.length <= 1) return;
                    this.activeGalleryIndex = (this.activeGalleryIndex + 1) % this.galleryImages.length;
                },
                prevGalleryImage() {
                    if (this.galleryImages.length <= 1) return;
                    this.activeGalleryIndex = (this.activeGalleryIndex - 1 + this.galleryImages.length) % this
                        .galleryImages.length;
                }
            };
        };
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --primary: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
            --primary-rgb: {{ $themeRgb }};
        }

        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .bg-brand-primary {
            background-color: var(--primary-color);
        }

        .text-brand-primary {
            color: var(--primary-color);
        }

        .border-brand-primary {
            border-color: var(--primary-color);
        }

        /* Apple Modular UI Design System */
        .bento-card {
            background-color: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 28px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 12px 28px -4px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dark .bento-card {
            background-color: rgba(28, 28, 30, 0.82);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2), 0 14px 32px -4px rgba(0, 0, 0, 0.35);
        }

        .bento-card-interactive {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .bento-card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04), 0 20px 40px -8px rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 0, 0, 0.12);
        }

        .dark .bento-card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 24px 48px -8px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.16);
        }

        .marquee-track {
            display: inline-flex;
            white-space: nowrap;
            will-change: transform;
            animation: marquee 28s linear infinite;
        }

        .marquee-track:hover {
            animation-play-state: paused;
        }

        @keyframes marquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
            }
        }
    </style>

    {{-- JSON-LD LocalBusiness Schema for Google Search --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "LocalBusiness",
        "name": "{{ $business->name }}",
        "description": "{{ $landingPage->subheadline }}",
        "url": "{{ url()->current() }}",
        "image": "{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}",
        "telephone": "{{ $landingPage->whatsapp_number ?: $business->phone }}",
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "{{ $landingPage->custom_address ?: $business->address }}",
            "addressCountry": "ID"
        },
        "sameAs": [
            @foreach(collect($landingPage->social_links ?? [])->filter() as $socialUrl)
                "{{ $socialUrl }}"{{ !$loop->last ? ',' : '' }}
            @endforeach
        ]
    }
    </script>
</head>

<body
    class="{{ $landingPage->dark_mode ? 'dark bg-[#000000] text-[#FFFFFF]' : 'bg-[#F5F5F7] text-[#1D1D1F]' }} antialiased selection:bg-brand-primary selection:text-white pb-20 md:pb-0"
    x-data="landingPageState()">

    {{-- Dynamic Island Notification Toast --}}
    <div x-show="toastMessage" x-cloak x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-y-8 opacity-0 scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100 scale-100"
        x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
        class="fixed top-16 left-1/2 -translate-x-1/2 z-[100] pointer-events-none select-none max-w-sm w-full px-4">
        <div
            class="px-5 py-2.5 rounded-full bg-black/90 dark:bg-white/95 text-white dark:text-black shadow-2xl backdrop-blur-xl text-[13px] font-semibold flex items-center justify-center gap-2 border border-white/15 dark:border-black/15 text-center">
            <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
            <span x-text="toastMessage"></span>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ANNOUNCEMENT MARQUEE (Apple Banner Style)                                  --}}
    {{-- ========================================================================= --}}
    @if ($landingPage->announcement_badge)
        <aside
            class="relative overflow-hidden bg-brand-primary text-white text-[12px] font-medium leading-tight select-none">
            <div class="marquee-track py-2">
                <span class="px-8 shrink-0 flex items-center gap-3">
                    <span>{{ $landingPage->announcement_badge }}</span>
                    <span class="opacity-60 text-[10px]">✦</span>
                    <span>{{ $landingPage->announcement_badge }}</span>
                    <span class="opacity-60 text-[10px]">✦</span>
                    <span>{{ $landingPage->announcement_badge }}</span>
                </span>
                <span class="px-8 shrink-0 flex items-center gap-3" aria-hidden="true">
                    <span>{{ $landingPage->announcement_badge }}</span>
                    <span class="opacity-60 text-[10px]">✦</span>
                    <span>{{ $landingPage->announcement_badge }}</span>
                    <span class="opacity-60 text-[10px]">✦</span>
                    <span>{{ $landingPage->announcement_badge }}</span>
                </span>
            </div>
        </aside>
    @endif

    {{-- ========================================================================= --}}
    {{-- TOPBAR / NAVBAR (macOS Sonoma / Apple Store Frosted Glass Style)            --}}
    {{-- ========================================================================= --}}
    <header
        class="sticky top-0 z-40 w-full backdrop-blur-xl {{ $landingPage->dark_mode ? 'bg-[#000000]/80 border-white/10' : 'bg-[#FFFFFF]/80 border-black/5' }} border-b transition-colors">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-4">

            {{-- Brand / Logo (Apple Squircle Icon) --}}
            <a href="#hero" class="flex items-center gap-3 group select-none">
                @if ($landingPage->logo_url ?: $business->logo_url)
                    <img src="{{ $landingPage->logo_url ?: $business->logo_url }}" alt="{{ $business->name }}"
                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] object-contain border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] p-1 shadow-sm group-hover:scale-105 transition-transform"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div
                        class="hidden w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-brand-primary text-white items-center justify-center font-bold text-xs shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}</div>
                @else
                    <div
                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-brand-primary text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <span
                        class="font-semibold text-[14px] sm:text-[15px] tracking-tight block leading-tight text-black dark:text-white group-hover:opacity-80 transition">{{ $business->name }}</span>
                    @if (collect($landingPage->operational_hours ?? [])->contains(fn($hours) => !empty($hours['is_open'])))
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#34C759]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>Buka Hari Ini</span>
                        </span>
                    @endif
                </div>
            </a>

            {{-- Desktop Navigation Links --}}
            <nav class="hidden md:flex items-center gap-1 text-[13px] font-medium text-black/70 dark:text-white/70">
                @if ($sectionVisibility['services'] && $hasServices)
                    <a href="#layanan"
                        class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Menu
                        &amp; Katalog</a>
                @endif
                @if ($sectionVisibility['about'] && $hasAbout)
                    <a href="#tentang"
                        class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Tentang
                        Kami</a>
                @endif
                @if ($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
                    <a href="#galeri"
                        class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Galeri</a>
                @endif
                @if ($sectionVisibility['faq'] && $faqs->isNotEmpty())
                    <a href="#faq"
                        class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Tanya
                        Jawab</a>
                @endif
                @if ($sectionVisibility['contact'] && $hasContact)
                    <a href="#lokasi"
                        class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Lokasi
                        &amp; Kontak</a>
                @endif
            </nav>

            {{-- CTA & Cart Navbar Actions --}}
            <div class="flex items-center gap-2">
                {{-- Storefront Shopping Cart Button --}}
                <button type="button" @click="cartDrawerOpen = true"
                    class="relative h-9 px-3.5 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[13px] font-semibold flex items-center gap-1.5 transition active:scale-95"
                    title="Keranjang Belanja">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span x-show="cartCount > 0"
                        class="px-1.5 py-0.2 rounded-full text-[10.5px] font-extrabold bg-[#007AFF] text-white tabular-nums shadow-xs"
                        x-text="cartCount"></span>
                </button>

                {{-- Customer Account Pill / Login Button --}}
                @if (auth('customer')->check())
                    @php
                        $navCust = auth('customer')->user();
                    @endphp
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button type="button" @click="userMenuOpen = !userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            class="h-9 pl-1.5 pr-3 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12.5px] font-semibold flex items-center gap-2 transition active:scale-95 border border-black/5 dark:border-white/10">
                            <span
                                class="w-6 h-6 rounded-full bg-[#007AFF] text-white text-[11px] font-bold flex items-center justify-center">
                                {{ strtoupper(mb_substr($navCust->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:inline max-w-[90px] truncate">{{ $navCust->name }}</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                        </button>

                        <div x-show="userMenuOpen" x-transition.opacity
                            class="absolute right-0 mt-2 w-56 p-2 rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl shadow-2xl border border-black/10 dark:border-white/10 text-[13px] z-50 space-y-1"
                            style="display: none;">
                            <div class="px-3 py-2 border-b border-black/5 dark:border-white/10 mb-1">
                                <p class="font-bold text-black dark:text-white truncate">{{ $navCust->name }}</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50 truncate">{{ $navCust->phone }}
                                </p>
                                <div class="mt-1.5 flex items-center justify-between text-[11px]">
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] font-bold capitalize">{{ $navCust->membership_tier ?? 'Bronze' }}</span>
                                    <span
                                        class="font-semibold text-black/70 dark:text-white/70">{{ number_format($navCust->loyalty_points ?? 0) }}
                                        Poin</span>
                                </div>
                            </div>
                            <a href="{{ route('customer.dashboard') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-[12px] text-black/80 dark:text-white/80 hover:bg-black/5 dark:hover:bg-white/10 transition font-medium">
                                <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Dashboard Saya</span>
                            </a>
                            <a href="{{ route('customer.orders') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-[12px] text-black/80 dark:text-white/80 hover:bg-black/5 dark:hover:bg-white/10 transition font-medium">
                                <i data-lucide="shopping-bag" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Riwayat Belanja</span>
                            </a>
                            <a href="{{ route('customer.cart') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-[12px] text-black/80 dark:text-white/80 hover:bg-black/5 dark:hover:bg-white/10 transition font-medium">
                                <i data-lucide="shopping-cart" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>Keranjang Belanja</span>
                            </a>
                            <a href="{{ route('customer.profile') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-[12px] text-black/80 dark:text-white/80 hover:bg-black/5 dark:hover:bg-white/10 transition font-medium">
                                <i data-lucide="user" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                                <span>Profil &amp; Alamat</span>
                            </a>
                            <div class="border-t border-black/5 dark:border-white/10 my-1 pt-1">
                                <form method="POST" action="{{ route('customer.logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-[12px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition font-medium text-left">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('customer.login', ['store' => $business->slug]) }}"
                        class="h-9 px-3 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-95 border border-black/5 dark:border-white/10"
                        title="Masuk / Daftar Akun">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Masuk</span>
                    </a>
                @endif

                @if ($storeSetting?->allow_reservation)
                    <button type="button" @click="reservationModalOpen = true; reservationSuccess = false;"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-3.5 rounded-full bg-[#34C759]/10 hover:bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[12.5px] font-semibold transition border border-[#34C759]/20 shadow-xs">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Reservasi</span>
                    </button>
                @endif

                @if ($landingPage->cta_primary_text && ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone))
                    <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank"
                        rel="noopener"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold hover:opacity-90 active:scale-[0.97] transition-all shadow-[0_1px_2px_rgba(0,0,0,0.08)]">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                        </svg>
                        <span>{{ $landingPage->cta_primary_text }}</span>
                    </a>
                @endif

                {{-- Mobile Hamburger --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/70 dark:text-white/70 active:scale-95 transition"
                    aria-label="Toggle menu">
                    <i data-lucide="menu" class="w-4 h-4" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-4 h-4" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Dropdown (Apple Sheet Dropdown) --}}
        <div x-show="mobileMenuOpen" x-transition.opacity
            class="md:hidden border-t {{ $landingPage->dark_mode ? 'bg-[#1C1C1E]/95 border-white/10' : 'bg-white/95 border-black/5' }} backdrop-blur-xl px-5 py-4 space-y-2 text-[14px] font-medium"
            style="display: none;">

            {{-- Mobile Customer Account Status --}}
            @if (auth('customer')->check())
                @php
                    $mCust = auth('customer')->user();
                @endphp
                <div
                    class="p-3.5 rounded-[18px] bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 mb-2 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-9 h-9 rounded-full bg-[#007AFF] text-white text-[13px] font-bold flex items-center justify-center">
                                {{ strtoupper(mb_substr($mCust->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-[14px] text-black dark:text-white leading-tight">
                                    {{ $mCust->name }}</p>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ $mCust->phone }}</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[11px] font-bold capitalize">{{ $mCust->membership_tier ?? 'Bronze' }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 pt-1 text-center text-[12px]">
                        <a href="{{ route('customer.dashboard') }}"
                            class="py-1.5 rounded-[10px] bg-white dark:bg-black/30 border border-black/5 dark:border-white/10 text-black/80 dark:text-white/80 font-medium">Dashboard</a>
                        <a href="{{ route('customer.orders') }}"
                            class="py-1.5 rounded-[10px] bg-white dark:bg-black/30 border border-black/5 dark:border-white/10 text-black/80 dark:text-white/80 font-medium">Pesanan</a>
                        <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="w-full py-1.5 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] font-semibold">Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mb-3">
                    <a href="{{ route('customer.login', ['store' => $business->slug]) }}"
                        class="w-full h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] font-bold text-[13.5px] flex items-center justify-center gap-2 hover:bg-[#007AFF]/15 transition">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Masuk / Daftar Akun Pelanggan</span>
                    </a>
                </div>
            @endif

            @if ($sectionVisibility['services'] && $hasServices)
                <a href="#layanan" @click="mobileMenuOpen = false"
                    class="block py-2 text-black/80 dark:text-white/80">Menu &amp; Layanan</a>
            @endif
            @if ($storeSetting?->allow_reservation)
                <button type="button"
                    @click="mobileMenuOpen = false; reservationModalOpen = true; reservationSuccess = false;"
                    class="block w-full text-left py-2 text-[#34C759] font-semibold">Reservasi Meja / Booking</button>
            @endif
            <button type="button" @click="mobileMenuOpen = false; cartDrawerOpen = true;"
                class="block w-full text-left py-2 text-[#007AFF] font-semibold">Keranjang Belanja (<span
                    x-text="cartCount"></span>)</button>
            @if ($sectionVisibility['about'] && $hasAbout)
                <a href="#tentang" @click="mobileMenuOpen = false"
                    class="block py-2 text-black/80 dark:text-white/80">Tentang Kami</a>
            @endif
            @if ($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
                <a href="#galeri" @click="mobileMenuOpen = false"
                    class="block py-2 text-black/80 dark:text-white/80">Galeri Foto</a>
            @endif
            @if ($sectionVisibility['faq'] && $faqs->isNotEmpty())
                <a href="#faq" @click="mobileMenuOpen = false"
                    class="block py-2 text-black/80 dark:text-white/80">Tanya Jawab (FAQ)</a>
            @endif
            @if ($sectionVisibility['contact'] && $hasContact)
                <a href="#lokasi" @click="mobileMenuOpen = false"
                    class="block py-2 text-black/80 dark:text-white/80">Lokasi &amp; Kontak</a>
            @endif
            @if ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                    class="w-full mt-3 h-11 rounded-full bg-brand-primary text-white text-center font-semibold text-[14px] flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                    </svg>
                    <span>{{ $landingPage->cta_primary_text ?: 'Chat WhatsApp' }}</span>
                </a>
            @endif
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- HERO SECTION (2-Grid Clean Apple HIG Architecture)                        --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['hero'] && $hasHero)
        <section id="hero" data-section="hero" class="relative overflow-hidden pt-12 pb-16 sm:pt-20 sm:pb-24">
            {{-- Ambient Depth Glow Mesh --}}
            <div
                class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-72 bg-brand-primary opacity-[0.12] dark:opacity-[0.18] blur-[120px] pointer-events-none rounded-full">
            </div>

            <div
                class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10 grid grid-cols-1 {{ $landingPage->hero_image_url ? 'lg:grid-cols-12 gap-10 lg:gap-16' : 'max-w-3xl' }} items-center">

                {{-- Left Column: Brand, Badges, Headline, Subheadline, CTAs --}}
                <div
                    class="{{ $landingPage->hero_image_url ? 'lg:col-span-7' : 'w-full text-center' }} text-center lg:text-left space-y-6">
                    {{-- Status Pill & Badges --}}
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2.5">
                        @if ($landingPage->announcement_badge)
                            <div
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[12px] sm:text-[12.5px] font-semibold bg-brand-primary/10 border border-brand-primary/25 text-brand-primary shadow-sm tracking-[-0.01em]">
                                <span class="w-2 h-2 rounded-full bg-brand-primary animate-ping"></span>
                                <span>{{ $landingPage->announcement_badge }}</span>
                            </div>
                        @endif

                        @if (collect($landingPage->operational_hours ?? [])->contains(fn($hours) => !empty($hours['is_open'])))
                            <div
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] sm:text-[12.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20 tracking-[-0.01em]">
                                <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                                <span>Buka Hari Ini</span>
                            </div>
                        @endif
                    </div>

                    {{-- Display Headline (SF Pro Typography Style) --}}
                    @if ($landingPage->headline)
                        <h1
                            class="text-3xl sm:text-5xl lg:text-[52px] xl:text-[56px] font-extrabold tracking-tight lg:tracking-[-0.035em] leading-[1.12] text-black dark:text-white max-w-3xl lg:max-w-none">
                            {{ $landingPage->headline }}
                        </h1>
                    @endif

                    {{-- Subheadline --}}
                    @if ($landingPage->subheadline)
                        <p
                            class="text-[15px] sm:text-[17px] text-black/65 dark:text-white/65 max-w-2xl leading-relaxed font-normal tracking-[-0.01em] {{ $landingPage->hero_image_url ? '' : 'mx-auto' }}">
                            {{ $landingPage->subheadline }}
                        </p>
                    @endif

                    {{-- Action Buttons & Micro Trust --}}
                    @if ($landingPage->cta_primary_text || $landingPage->cta_secondary_text)
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                            @if ($landingPage->cta_primary_text)
                                <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}"
                                    target="_blank" rel="noopener"
                                    class="w-full sm:w-auto h-12 px-7 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(0,0,0,0.15)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                    </svg>
                                    <span>{{ $landingPage->cta_primary_text }}</span>
                                </a>
                            @endif

                            @if ($landingPage->cta_secondary_text)
                                <a href="{{ $landingPage->cta_secondary_url ?: '#layanan' }}"
                                    class="w-full sm:w-auto h-12 px-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-center gap-2">
                                    <span>{{ $landingPage->cta_secondary_text }}</span>
                                    <i data-lucide="arrow-down" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- Micro Trust Tags --}}
                    <div
                        class="flex flex-wrap items-center justify-center lg:justify-start gap-4 text-[12px] sm:text-[12.5px] font-medium text-black/55 dark:text-white/55 pt-1">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check"
                                class="w-4 h-4 text-brand-primary"></i>100% Autentik</span>
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="sparkles"
                                class="w-4 h-4 text-amber-500"></i>Kualitas Terjamin</span>
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="smile"
                                class="w-4 h-4 text-[#34C759]"></i>Pelayanan Ramah</span>
                    </div>
                </div>

                {{-- Right Column: Media Hardware Squircle Showcase --}}
                @if ($landingPage->hero_image_url)
                    <div class="lg:col-span-5 pt-6 lg:pt-0">
                        <div
                            class="rounded-[28px] overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.12)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.45)] border border-black/10 dark:border-white/10 aspect-[4/3] bg-black/[0.03] dark:bg-white/[0.05] relative group">
                            <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                fetchpriority="high" onerror="this.style.display='none';">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent pointer-events-none">
                            </div>
                            <div
                                class="absolute bottom-4 inset-x-4 flex items-center justify-between text-white pointer-events-none">
                                <div class="text-xs font-semibold drop-shadow-md flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-brand-primary"></i>
                                    <span>{{ $business->name }}</span>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-md text-white border border-white/30 shadow-sm">
                                    Resmi
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- VALUE PROPOSITIONS (Apple Bento Feature Grid)                             --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['about'] && !empty($landingPage->values))
        <section class="py-4 sm:py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach ($landingPage->values as $val)
                        <div class="bento-card bento-card-interactive p-6 flex flex-col justify-between space-y-4">
                            <div
                                class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $val['icon'] ?? 'check' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3
                                    class="font-semibold text-[15.5px] sm:text-[16px] text-black dark:text-white tracking-[-0.015em] leading-snug">
                                    {{ $val['title'] ?? '' }}</h3>
                                <p
                                    class="text-[13px] sm:text-[13.5px] text-black/60 dark:text-white/60 mt-1.5 leading-relaxed font-normal">
                                    {{ $val['desc'] ?? ($val['description'] ?? '') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- LAYANAN & PRODUK SHOWCASE (Apple Store Product Row Style)                 --}}
    {{-- ========================================================================= --}}
    @if (
        ($sectionVisibility['services'] || ($sectionVisibility['products'] && $landingPage->show_pos_products)) &&
            $hasServices)
        <section id="layanan" data-section="services" class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-12">

                {{-- Section Title --}}
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Menu
                        &amp; Layanan</span>
                    @if ($landingPage->services_title)
                        <h2
                            class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                            {{ $landingPage->services_title }}</h2>
                    @endif
                    @if ($landingPage->services_subtitle)
                        <p
                            class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 leading-relaxed font-normal tracking-[-0.01em]">
                            {{ $landingPage->services_subtitle }}</p>
                    @endif
                </div>

                {{-- Custom Services Slider --}}
                @if ($sectionVisibility['services'] && $services->isNotEmpty())
                    <div class="space-y-5" x-data="makeSlider({{ $services->count() }}, { base: 1, sm: 2, lg: 3 }, 3600)">
                        <div class="flex items-center justify-between">
                            <div>
                                <span
                                    class="text-[11px] font-semibold uppercase tracking-[0.05em] text-black/45 dark:text-white/45 block mb-0.5">Pilihan
                                    Layanan</span>
                                <h3
                                    class="font-bold text-[18px] sm:text-[20px] text-black dark:text-white tracking-[-0.015em] leading-snug">
                                    Layanan Unggulan</h3>
                            </div>
                            @if ($services->count() > 1)
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="prev()" aria-label="Layanan Sebelumnya"
                                        class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition">
                                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" @click="next()" aria-label="Layanan Berikutnya"
                                        class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="relative overflow-hidden rounded-[28px]" @mouseenter="stop()"
                            @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                            <div class="flex transition-transform duration-500 ease-out"
                                :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                                @foreach ($services as $serviceIndex => $svc)
                                    <div class="w-full sm:w-1/2 lg:w-1/3 shrink-0 p-2 sm:p-2.5">
                                        <button type="button" @click="openService({{ Js::from($svc) }})"
                                            class="w-full h-full text-left bento-card bento-card-interactive p-5 flex flex-col justify-between space-y-4 active:scale-[0.98]">
                                            <div>
                                                <div
                                                    class="relative w-full aspect-[16/10] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden mb-3.5 flex items-center justify-center">
                                                    @if (!empty($svc['image_url']))
                                                        <img src="{{ $svc['image_url'] }}"
                                                            alt="{{ $svc['title'] ?? 'Layanan' }}" loading="lazy"
                                                            class="w-full h-full object-cover">
                                                    @else
                                                        <div
                                                            class="w-full h-full flex items-center justify-center text-brand-primary">
                                                            <i data-lucide="{{ $svc['icon'] ?? 'sparkles' }}"
                                                                class="w-8 h-8"></i>
                                                        </div>
                                                    @endif
                                                    @if (!empty($svc['badge']))
                                                        <span
                                                            class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold uppercase tracking-wider bg-brand-primary text-white shadow-sm">
                                                            {{ $svc['badge'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <h4
                                                    class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white line-clamp-1 tracking-tight leading-snug">
                                                    {{ $svc['title'] ?? '' }}</h4>
                                                <p
                                                    class="text-[12.5px] sm:text-[13px] text-black/60 dark:text-white/60 mt-1 line-clamp-2 leading-relaxed font-normal">
                                                    {{ $svc['description'] ?? '' }}
                                                </p>
                                            </div>

                                            <div
                                                class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                                <div
                                                    class="font-bold text-[14px] sm:text-[14.5px] text-brand-primary tabular-nums tracking-tight">
                                                    @if (!empty($svc['price']))
                                                        {{ $svc['price'] }}
                                                    @else
                                                        Hubungi kami
                                                    @endif
                                                </div>
                                                <span
                                                    class="px-3 py-1 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-semibold text-[11.5px] tracking-tight transition flex items-center gap-1">
                                                    <span>Detail</span>
                                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Minimalist Dots Indicator --}}
                        <div class="flex items-center justify-center gap-1.5 pt-1" x-show="maxIndex() > 0">
                            <template x-for="idx in (maxIndex() + 1)" :key="idx">
                                <button type="button" @click="goTo(idx - 1)"
                                    :class="currentIndex === (idx - 1) ? 'w-5 bg-brand-primary' :
                                        'w-1.5 bg-black/20 dark:bg-white/20'"
                                    class="h-1.5 rounded-full transition-all duration-300"
                                    :aria-label="'Slide ' + idx"></button>
                            </template>
                        </div>

                        @if ($services->count() > 3)
                            <div class="text-center pt-2">
                                <button type="button" @click="activeModal = 'all-services'"
                                    class="inline-flex items-center gap-2 h-10 px-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[13px] font-semibold tracking-tight active:scale-[0.97] transition">
                                    <i data-lucide="grid" class="w-4 h-4"></i>
                                    <span>Lihat Semua Layanan ({{ $services->count() }})</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Live POS Products Slider --}}
                @if ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty())
                    <div class="pt-6 space-y-5" x-data="makeSlider({{ $posProducts->count() }}, { base: 1, sm: 2, lg: 4 }, 3200)">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span
                                    class="text-[11px] font-semibold uppercase tracking-[0.05em] text-black/45 dark:text-white/45 block mb-0.5">Menu
                                    &amp; Produk</span>
                                <div class="flex items-center gap-3">
                                    <h3
                                        class="font-bold text-[18px] sm:text-[20px] text-black dark:text-white tracking-[-0.015em] leading-snug">
                                        Katalog Produk</h3>
                                    @if ($storeSetting?->allow_request_order)
                                        <button type="button" @click="requestOrderModalOpen = true"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] text-[11.5px] font-semibold transition border border-[#007AFF]/20 shadow-xs">
                                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                            <span>Request Order Khusus</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if ($posProducts->count() > 1)
                                <div class="flex items-center gap-2 self-end sm:self-center">
                                    <button type="button" @click="prev()" aria-label="Produk Sebelumnya"
                                        class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition">
                                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" @click="next()" aria-label="Produk Berikutnya"
                                        class="w-8 h-8 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="relative overflow-hidden rounded-[28px]" @mouseenter="stop()"
                            @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                            <div class="flex transition-transform duration-500 ease-out"
                                :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                                @foreach ($posProducts as $prod)
                                    <div class="w-full sm:w-1/2 lg:w-1/4 shrink-0 p-2 sm:p-2.5">
                                        <button type="button"
                                            @click="openProduct(products.find(product => product.id === '{{ $prod->id }}'))"
                                            class="w-full h-full text-left bento-card bento-card-interactive p-4 flex flex-col justify-between space-y-3 active:scale-[0.98]">
                                            <div>
                                                <div
                                                    class="w-full aspect-square rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mb-2.5 overflow-hidden">
                                                    @if ($prod->image_url)
                                                        <img src="{{ $prod->image_url }}"
                                                            alt="{{ $prod->name }}" loading="lazy"
                                                            class="w-full h-full object-cover"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"><i
                                                            data-lucide="package"
                                                            class="hidden w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                    @else
                                                        <i data-lucide="package"
                                                            class="w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                    @endif
                                                </div>
                                                <h4
                                                    class="font-semibold text-[13.5px] sm:text-[14px] text-black dark:text-white line-clamp-2 tracking-tight leading-snug">
                                                    {{ $prod->name }}</h4>
                                                @if ($prod->category)
                                                    <span
                                                        class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5 font-normal">{{ $prod->category->name }}</span>
                                                @endif
                                            </div>
                                            <div
                                                class="pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1.5">
                                                <span
                                                    class="font-bold text-[13.5px] sm:text-[14px] text-brand-primary tabular-nums tracking-tight">Rp
                                                    {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button"
                                                        @click.stop="addToCart(products.find(p => p.id === '{{ $prod->id }}'), 1, false)"
                                                        class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:bg-brand-primary/15 hover:text-brand-primary flex items-center justify-center transition active:scale-90"
                                                        title="Tambah ke Keranjang">
                                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <button type="button"
                                                        @click.stop="directCheckout(products.find(p => p.id === '{{ $prod->id }}'), 1)"
                                                        class="h-7 px-2.5 rounded-full bg-brand-primary text-white text-[11px] font-semibold hover:opacity-90 flex items-center gap-1 transition active:scale-95 shadow-sm"
                                                        title="Beli Langsung">
                                                        <span>Beli</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Minimalist Dots Indicator --}}
                        <div class="flex items-center justify-center gap-1.5 pt-1" x-show="maxIndex() > 0">
                            <template x-for="idx in (maxIndex() + 1)" :key="idx">
                                <button type="button" @click="goTo(idx - 1)"
                                    :class="currentIndex === (idx - 1) ? 'w-5 bg-brand-primary' :
                                        'w-1.5 bg-black/20 dark:bg-white/20'"
                                    class="h-1.5 rounded-full transition-all duration-300"
                                    :aria-label="'Slide ' + idx"></button>
                            </template>
                        </div>

                        @if ($posProducts->count() > 4)
                            <div class="text-center pt-2">
                                <button type="button" @click="activeModal = 'all-products'"
                                    class="inline-flex items-center gap-2 h-9 px-5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[13px] font-semibold tracking-tight active:scale-[0.97] transition">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>Lihat Semua Produk ({{ $posProducts->count() }})</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- GALERI FOTO (Apple Bento Photo Grid - Max 5 Bento Showcase)               --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['gallery'] && $allGalleryImages->isNotEmpty())
        <section id="galeri" data-section="gallery" class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center space-y-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Galeri
                        Foto</span>
                    @if ($landingPage->gallery_title)
                        <h2
                            class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                            {{ $landingPage->gallery_title }}</h2>
                    @endif
                    @if ($landingPage->gallery_subtitle)
                        <p
                            class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 max-w-xl mx-auto leading-relaxed font-normal tracking-[-0.01em]">
                            {{ $landingPage->gallery_subtitle }}</p>
                    @endif
                </div>

                {{-- Optimized Apple Bento Photo Grid (Max 5 items) --}}
                @if ($totalGalleryCount === 1)
                    <div class="max-w-3xl mx-auto bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer"
                        @click="openGalleryLightbox(0)">
                        <div
                            class="w-full aspect-[16/9] rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                            <img src="{{ $bentoGalleryImages[0]['url'] }}"
                                alt="{{ $bentoGalleryImages[0]['caption'] ?: 'Galeri ' . $business->name }}"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                <p
                                    class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">
                                    {{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                            </div>
                        </div>
                    </div>
                @elseif($totalGalleryCount === 2)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        @foreach ($bentoGalleryImages as $idx => $img)
                            <div class="bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-[4/3]"
                                @click="openGalleryLightbox({{ $idx }})">
                                <div
                                    class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                    <img src="{{ $img['url'] }}"
                                        alt="{{ $img['caption'] ?: 'Galeri ' . $business->name }}" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                        <p
                                            class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">
                                            {{ $img['caption'] ?: $business->name }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($totalGalleryCount === 3)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="sm:col-span-2 bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer aspect-[16/9] min-h-[260px] sm:min-h-[340px]"
                            @click="openGalleryLightbox(0)">
                            <div
                                class="w-full h-full rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[0]['url'] }}"
                                    alt="{{ $bentoGalleryImages[0]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <p
                                        class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        @foreach ($bentoGalleryImages->slice(1) as $sliceIdx => $img)
                            @php $actualIdx = $sliceIdx + 1; @endphp
                            <div class="bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                                @click="openGalleryLightbox({{ $actualIdx }})">
                                <div
                                    class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                    <img src="{{ $img['url'] }}"
                                        alt="{{ $img['caption'] ?: 'Galeri ' . $business->name }}" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                        <p class="text-white text-[12px] font-semibold drop-shadow tracking-tight">
                                            {{ $img['caption'] ?: $business->name }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($totalGalleryCount === 4)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 items-stretch">
                        <div class="sm:col-span-2 lg:col-span-2 lg:row-span-2 bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer min-h-[280px] sm:min-h-[360px] lg:min-h-[440px]"
                            @click="openGalleryLightbox(0)">
                            <div
                                class="w-full h-full rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[0]['url'] }}"
                                    alt="{{ $bentoGalleryImages[0]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute top-4 left-4 z-10">
                                    <span
                                        class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-white/85 dark:bg-black/85 backdrop-blur-md text-brand-primary shadow-sm">
                                        Unggulan
                                    </span>
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <p
                                        class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                            @click="openGalleryLightbox(1)">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[1]['url'] }}"
                                    alt="{{ $bentoGalleryImages[1]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[1]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                            @click="openGalleryLightbox(2)">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[2]['url'] }}"
                                    alt="{{ $bentoGalleryImages[2]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[2]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-2 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer min-h-[160px] aspect-[16/9] sm:aspect-auto"
                            @click="openGalleryLightbox(3)">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[3]['url'] }}"
                                    alt="{{ $bentoGalleryImages[3]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                    <p class="text-white text-[12px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[3]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- 5 Images Asymmetric Apple Bento Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 items-stretch">
                        {{-- Tile 0: Featured Hero Bento (Row 1-2, Col 1-2) --}}
                        <div class="sm:col-span-2 lg:col-span-2 lg:row-span-2 bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer min-h-[280px] sm:min-h-[360px] lg:min-h-[460px]"
                            @click="openGalleryLightbox(0)">
                            <div
                                class="w-full h-full rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[0]['url'] }}"
                                    alt="{{ $bentoGalleryImages[0]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute top-4 left-4 z-10">
                                    <span
                                        class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-white/85 dark:bg-black/85 backdrop-blur-md text-brand-primary shadow-sm">
                                        Unggulan
                                    </span>
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <div class="space-y-1">
                                        <span
                                            class="text-white/70 text-[11px] font-semibold uppercase tracking-[0.05em] block">Foto
                                            Suasana</span>
                                        <p
                                            class="text-white text-[13px] sm:text-[13.5px] font-semibold drop-shadow tracking-tight">
                                            {{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 1: Top Right 1 --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                            @click="openGalleryLightbox(1)">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[1]['url'] }}"
                                    alt="{{ $bentoGalleryImages[1]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[1]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 2: Top Right 2 --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                            @click="openGalleryLightbox(2)">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[2]['url'] }}"
                                    alt="{{ $bentoGalleryImages[2]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[2]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 3: Bottom Right 1 --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                            @click="openGalleryLightbox(3)">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[3]['url'] }}"
                                    alt="{{ $bentoGalleryImages[3]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">
                                        {{ $bentoGalleryImages[3]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 4: Bottom Right 2 (5th item, with +X overlay if extra images exist) --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                            @click="{{ $extraGalleryCount > 0 ? 'openAllGallery()' : 'openGalleryLightbox(4)' }}">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[4]['url'] }}"
                                    alt="{{ $bentoGalleryImages[4]['caption'] ?: 'Galeri ' . $business->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">

                                @if ($extraGalleryCount > 0)
                                    {{-- Frosted Glass +X Overlay --}}
                                    <div
                                        class="absolute inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-[3px] group-hover:backdrop-blur-[1px] transition-all flex flex-col items-center justify-center text-center p-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white mb-2 group-hover:scale-110 transition-transform">
                                            <i data-lucide="images" class="w-5 h-5"></i>
                                        </div>
                                        <span
                                            class="text-white font-extrabold text-lg sm:text-xl tracking-tight leading-tight">+{{ $extraGalleryCount }}
                                            Foto</span>
                                        <span
                                            class="text-white/80 text-[11px] sm:text-[11.5px] font-medium mt-1 tracking-tight">Lihat
                                            Semua</span>
                                    </div>
                                @else
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                        <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">
                                            {{ $bentoGalleryImages[4]['caption'] ?: $business->name }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Bar: View All Gallery Pop-up Trigger --}}
                <div class="flex items-center justify-center pt-2">
                    <button type="button" @click="openAllGallery()"
                        class="h-11 px-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white font-semibold text-[13px] sm:text-[13.5px] tracking-tight border border-black/5 dark:border-white/10 shadow-sm active:scale-[0.98] transition flex items-center gap-2.5">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-brand-primary"></i>
                        <span>Lihat Semua Galeri ({{ $totalGalleryCount }} Foto)</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 opacity-50"></i>
                    </button>
                </div>

            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- TENTANG KAMI & JAM OPERASIONAL (Apple Bento Split Architecture)            --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['about'] && $hasAbout)
        <section id="tentang" data-section="about" class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center space-y-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Cerita
                        Kami</span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                        Tentang {{ $business->name }}</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

                    {{-- Left Bento Card: Story & Philosophy --}}
                    @if ($landingPage->about_title || $landingPage->about_story || $landingPage->about_image_url)
                        <div
                            class="{{ !empty($landingPage->operational_hours) ? 'lg:col-span-7' : 'lg:col-span-12' }} bento-card p-7 sm:p-9 flex flex-col justify-between space-y-6">
                            <div class="space-y-4">
                                <div
                                    class="inline-flex items-center gap-2 text-brand-primary text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.05em]">
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                    <span>Dedikasi &amp; Cita Rasa</span>
                                </div>
                                @if ($landingPage->about_title)
                                    <h3
                                        class="text-xl sm:text-[23px] font-bold tracking-tight lg:tracking-[-0.02em] text-black dark:text-white leading-snug">
                                        {{ $landingPage->about_title }}
                                    </h3>
                                @endif
                                <div
                                    class="text-[14px] sm:text-[15px] text-black/70 dark:text-white/70 leading-[1.7] space-y-3 whitespace-pre-wrap font-normal tracking-[-0.01em]">
                                    @if ($landingPage->about_story)
                                        {{ $landingPage->about_story }}
                                    @endif
                                </div>
                            </div>

                            @if ($landingPage->about_image_url)
                                <div
                                    class="rounded-[20px] overflow-hidden aspect-[16/9] border border-black/5 dark:border-white/10 mt-3">
                                    <img src="{{ $landingPage->about_image_url }}" alt="{{ $business->name }}"
                                        loading="lazy" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Right Bento Card: Jam Operasional (Apple Inset Group Table) --}}
                    @if (!empty($landingPage->operational_hours))
                        <div class="lg:col-span-5 bento-card p-7 flex flex-col justify-between space-y-6">
                            <div class="space-y-5">
                                <div class="flex items-center gap-3.5">
                                    <div
                                        class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                        <i data-lucide="clock" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3
                                            class="font-bold text-[16px] sm:text-[17px] text-black dark:text-white tracking-tight leading-snug">
                                            Jadwal Operasional</h3>
                                        <p
                                            class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 leading-normal">
                                            Waktu pelayanan resmi outlet</p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] p-3 divide-y divide-black/5 dark:divide-white/5 text-[13px]">
                                    @foreach ($landingPage->operational_hours as $h)
                                        <div class="py-2.5 px-2 flex justify-between items-center">
                                            <span
                                                class="font-medium text-[13px] sm:text-[13.5px] text-black/75 dark:text-white/75">{{ $h['day'] }}</span>
                                            @if (!empty($h['is_open']))
                                                @if (!empty($h['hours']))
                                                    <span
                                                        class="inline-flex items-center gap-1.5 font-semibold text-[13px] sm:text-[13.5px] text-[#34C759] tabular-nums tracking-tight">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                        <span>{{ $h['hours'] }}</span>
                                                    </span>
                                                @endif
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 font-medium text-[12px] sm:text-[12.5px] text-[#FF3B30]">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                                    <span>Tutup</span>
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                                    class="w-full h-11 rounded-full bg-brand-primary text-white font-semibold text-[13px] sm:text-[13.5px] tracking-tight flex items-center justify-center gap-2 hover:opacity-95 active:scale-[0.98] transition shadow-sm">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>Tanya Jadwal / Reservasi</span>
                                </a>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- TESTIMONI PELANGGAN (Apple Bento Review Cards)                            --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['testimonials'] && $testimonials->isNotEmpty())
        <section class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Ulasan
                        Pelanggan</span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                        Apa Kata Mereka</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($testimonials as $testi)
                        <div class="bento-card bento-card-interactive p-6 flex flex-col justify-between space-y-5">
                            {{-- Star Rating in Apple Gold --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 text-[#FFCC00]">
                                    @for ($i = 0; $i < ($testi['rating'] ?? 5); $i++)
                                        <i data-lucide="star" class="w-4 h-4 fill-[#FFCC00]"></i>
                                    @endfor
                                </div>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] sm:text-[10.5px] font-semibold uppercase tracking-wider bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                    Verified
                                </span>
                            </div>

                            <p
                                class="text-[13.5px] sm:text-[14px] text-black/75 dark:text-white/75 italic leading-relaxed font-normal tracking-[-0.01em]">
                                "{{ $testi['quote'] ?? '' }}"
                            </p>

                            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-[10px] bg-brand-primary/10 text-brand-primary font-bold text-xs flex items-center justify-center">
                                    {{ strtoupper(substr($testi['name'] ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    @if (!empty($testi['name']))
                                        <div
                                            class="font-semibold text-[13px] sm:text-[13.5px] text-black dark:text-white tracking-tight">
                                            {{ $testi['name'] }}</div>
                                    @endif
                                    @if (!empty($testi['role']))
                                        <div
                                            class="text-[11px] sm:text-[11.5px] text-black/45 dark:text-white/45 font-normal">
                                            {{ $testi['role'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- FAQ ACCORDION (Apple Bento Inset Card)                                    --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['faq'] && $faqs->isNotEmpty())
        <section id="faq" class="py-12 sm:py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-7" x-data="{ openFaq: null }">
                <div class="text-center space-y-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Bantuan
                        &amp; Info</span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                        Pertanyaan Umum (FAQ)</h2>
                </div>

                <div class="bento-card overflow-hidden divide-y divide-black/5 dark:divide-white/5 p-2 sm:p-3">
                    @foreach ($faqs as $index => $faq)
                        <div class="transition-colors">
                            <button
                                @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 font-semibold text-[14.5px] sm:text-[15.5px] text-black dark:text-white hover:text-brand-primary tracking-tight leading-snug transition">
                                <span>{{ $faq['question'] ?? '' }}</span>
                                <div
                                    class="w-7 h-7 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center shrink-0">
                                    <i data-lucide="chevron-down"
                                        class="w-4 h-4 text-black/50 dark:text-white/50 transition-transform duration-300"
                                        :class="openFaq === {{ $index }} ? 'rotate-180 text-brand-primary' : ''"></i>
                                </div>
                            </button>
                            <div x-show="openFaq === {{ $index }}" x-transition.opacity
                                class="px-5 pb-5 text-[13.5px] sm:text-[14px] text-black/65 dark:text-white/65 leading-relaxed font-normal"
                                style="display: none;">
                                {{ $faq['answer'] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- LOKASI & KONTAK (Apple Bento Hardware Map & Details Grid)                 --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['contact'] && $hasContact)
        <section id="lokasi" data-section="contact" class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Kunjungi
                        Outlet</span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                        Lokasi &amp; Kontak</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                    {{-- Address & Details Bento Card --}}
                    <div class="lg:col-span-5 bento-card p-6 sm:p-8 flex flex-col justify-between space-y-6">
                        <div class="space-y-5">
                            @if ($landingPage->custom_address || $business->address)
                                <div class="flex items-start gap-3.5">
                                    <div
                                        class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3
                                            class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white tracking-tight leading-snug">
                                            Alamat Resmi</h3>
                                        <p
                                            class="text-[13px] sm:text-[13.5px] text-black/65 dark:text-white/65 mt-1 leading-relaxed font-normal">
                                            {{ $landingPage->custom_address ?: $business->address }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                                <div class="flex items-start gap-3.5">
                                    <div
                                        class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                        <i data-lucide="phone" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3
                                            class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white tracking-tight leading-snug">
                                            Telepon / WhatsApp</h3>
                                        <p
                                            class="text-[13.5px] sm:text-[14px] font-medium text-black/80 dark:text-white/80 mt-1 tabular-nums tracking-tight">
                                            {{ $landingPage->whatsapp_number ?: ($business->phone ?: '-') }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if ($landingPage->custom_email ?: $business->email)
                                <div class="flex items-start gap-3.5">
                                    <div
                                        class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                        <i data-lucide="mail" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3
                                            class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white tracking-tight leading-snug">
                                            Email Bisnis</h3>
                                        <a href="mailto:{{ $landingPage->custom_email ?: $business->email }}"
                                            class="text-[13px] sm:text-[13.5px] text-brand-primary mt-1 block hover:underline tracking-tight font-medium">
                                            {{ $landingPage->custom_email ?: $business->email }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- Social Media & Marketplace Channels --}}
                            @if ($activeChannels->isNotEmpty() || $hasWhatsapp)
                                <div class="pt-4 border-t border-black/5 dark:border-white/5 space-y-2.5">
                                    <span
                                        class="text-[11px] sm:text-[11.5px] text-black/45 dark:text-white/45 font-semibold uppercase tracking-[0.05em] block">Kanal
                                        Resmi &amp; Medsos:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($activeChannels as $channel)
                                            @if (filled($channel['url']))
                                                <a href="{{ $channel['url'] }}" target="_blank" rel="noopener"
                                                    aria-label="{{ $channel['label'] }}"
                                                    title="{{ $channel['label'] }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-95 transition">
                                                    <i data-lucide="{{ $channel['icon'] }}"
                                                        class="w-3.5 h-3.5 shrink-0"></i>
                                                    <span>{{ $channel['label'] }}</span>
                                                </a>
                                            @endif
                                        @endforeach

                                        @if ($hasWhatsapp)
                                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                                                rel="noopener" aria-label="WhatsApp" title="WhatsApp"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 active:scale-95 transition">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                                <span>WhatsApp</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                                class="w-full h-12 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-tight flex items-center justify-center gap-2 hover:opacity-95 active:scale-[0.98] transition shadow-[0_2px_8px_rgba(0,0,0,0.12)]">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                                <span>Mulai Chat via WhatsApp</span>
                            </a>
                        @endif
                    </div>

                    {{-- Map Embed (Bento Hardware Squircle Bezel) --}}
                    @if ($landingPage->google_maps_embed_url)
                        <div
                            class="lg:col-span-7 bento-card p-3 overflow-hidden min-h-[350px] sm:min-h-[420px] flex flex-col">
                            <div
                                class="w-full h-full rounded-[20px] overflow-hidden border border-black/5 dark:border-white/5">
                                <iframe src="{{ $landingPage->google_maps_embed_url }}" width="100%"
                                    height="100%" style="border:0; min-height: 340px;" allowfullscreen=""
                                    loading="lazy"></iframe>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- DETAIL & CATALOG MODAL (Apple Sheet Style)                                --}}
    {{-- ========================================================================= --}}
    <div x-show="activeModal && activeModal !== 'gallery-lightbox'" x-cloak
        @keydown.escape.window="activeModal = null" @click.self="activeModal = null"
        class="fixed inset-0 z-[70] bg-black/35 backdrop-blur-[3px] p-4 flex items-center justify-center"
        role="dialog" aria-modal="true">
        <div class="w-full max-h-[90vh] overflow-y-auto rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] p-5 sm:p-7 transition-all duration-300"
            :class="activeModal === 'all-gallery' ? 'max-w-4xl' : 'max-w-3xl'">

            <div class="flex items-center justify-between gap-3 mb-5 border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white tracking-tight"
                    x-text="activeModal === 'service' ? 'Detail Layanan' : activeModal === 'product' ? 'Detail Produk' : activeModal === 'all-services' ? 'Semua Layanan' : activeModal === 'all-products' ? 'Semua Produk' : 'Koleksi Galeri Foto'">
                </h2>
                <button type="button" @click="activeModal = null"
                    class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 flex items-center justify-center hover:bg-black/10 active:scale-95 transition"
                    aria-label="Tutup dialog">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Single Product / Service Detail View --}}
            <div x-show="activeModal === 'service' || activeModal === 'product'"
                class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div
                    class="relative aspect-[4/3] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden flex items-center justify-center border border-black/5 dark:border-white/10">
                    <template x-if="activeItem && activeItem.image_url">
                        <img :src="activeItem.image_url" :alt="activeItem.name || activeItem.title"
                            class="w-full h-full object-cover" x-on:error="activeItem.image_url = null">
                    </template>
                    <i x-show="!activeItem || !activeItem.image_url" data-lucide="package"
                        class="w-12 h-12 text-black/25 dark:text-white/25"></i>
                    <span x-show="activeItem && (activeItem.category || activeModal === 'service')"
                        class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full bg-white/90 dark:bg-black/80 backdrop-blur text-[10.5px] font-semibold uppercase tracking-wider text-brand-primary shadow-sm"
                        x-text="activeItem?.category || 'Layanan'"></span>
                </div>
                <div class="space-y-4 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] text-brand-primary font-semibold uppercase tracking-[0.05em]"
                            x-text="activeModal === 'service' ? 'Layanan Kami' : 'Produk Kami'"></span>
                        <h3 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight leading-snug mt-0.5"
                            x-text="activeItem?.title || activeItem?.name"></h3>
                        <p class="text-[13px] sm:text-[13.5px] text-black/65 dark:text-white/65 whitespace-pre-wrap leading-relaxed mt-2 font-normal"
                            x-text="activeItem?.description || 'Informasi detail belum tersedia.'"></p>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-black/5 dark:border-white/5">
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="text-[20px] font-bold text-brand-primary tabular-nums tracking-tight"
                                x-text="activeItem?.price ? ((typeof activeItem.price === 'number') ? formatPrice(activeItem.price) : activeItem.price) : 'Hubungi kami'"></span>
                            <span class="text-[11px] text-black/45 dark:text-white/45 font-normal"
                                x-text="activeModal === 'service' ? 'Estimasi biaya' : 'Harga resmi'"></span>
                        </div>
                        {{-- Stepper for physical products --}}
                        <div class="flex items-center justify-between py-1 bg-black/[0.02] dark:bg-white/[0.03] px-3.5 py-2 rounded-[14px] border border-black/5 dark:border-white/5"
                            x-show="activeModal === 'product' && isStorefrontEnabled">
                            <span class="text-[12.5px] font-semibold text-black/70 dark:text-white/70">Jumlah
                                Pesanan:</span>
                            <div
                                class="flex items-center gap-2.5 bg-white dark:bg-black/40 px-3 py-1 rounded-full border border-black/10 dark:border-white/10 shadow-xs">
                                <button type="button" @click="if (modalQty > 1) modalQty--"
                                    class="w-6 h-6 flex items-center justify-center text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-bold text-[14px] active:scale-90 transition">&minus;</button>
                                <span
                                    class="text-[13px] font-extrabold text-black dark:text-white min-w-5 text-center tabular-nums"
                                    x-text="modalQty"></span>
                                <button type="button" @click="modalQty++"
                                    class="w-6 h-6 flex items-center justify-center text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-bold text-[14px] active:scale-90 transition">&plus;</button>
                            </div>
                        </div>

                        {{-- Product Modal Actions --}}
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2"
                            x-show="activeModal === 'product'">
                            <template x-if="isStorefrontEnabled">
                                <button type="button"
                                    @click="directCheckout(activeItem, modalQty); activeModal = null;"
                                    class="flex-1 h-10 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-2">
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                    <span>Beli Sekarang</span>
                                </button>
                            </template>
                            <template x-if="isStorefrontEnabled">
                                <button type="button"
                                    @click="addToCart(activeItem, modalQty, true); activeModal = null;"
                                    class="h-10 px-4 rounded-full bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF] hover:text-white text-[13px] font-semibold tracking-tight active:scale-[0.97] transition flex items-center justify-center gap-1.5">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>+ Keranjang</span>
                                </button>
                            </template>
                            <a :href="activeItem ? waLink(activeItem.title || activeItem.name, false) : '#'"
                                target="_blank" rel="noopener"
                                class="h-10 px-3.5 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 text-[13px] font-semibold tracking-tight transition flex items-center justify-center gap-1.5"
                                title="Tanya WhatsApp">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                                <span class="hidden sm:inline">WhatsApp</span>
                            </a>
                            <button type="button" @click="activeModal = null"
                                class="h-10 px-4 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 text-[13px] font-medium tracking-tight hover:bg-black/[0.08] transition">
                                Tutup
                            </button>
                        </div>

                        {{-- Service Modal Actions --}}
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2"
                            x-show="activeModal === 'service'">
                            <template
                                x-if="activeItem && activeItem.raw_price && activeItem.raw_price > 0 && isStorefrontEnabled">
                                <button type="button"
                                    @click="directCheckout({ id: activeItem.id, name: activeItem.title || activeItem.name, price: activeItem.raw_price, image_url: activeItem.image_url }, 1); activeModal = null;"
                                    class="flex-1 h-10 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-2">
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                    <span>Pesan Online</span>
                                </button>
                            </template>
                            <button type="button"
                                @click="activeModal = null; openReservationModal(activeItem?.title || activeItem?.name)"
                                class="h-10 px-4 rounded-full bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF] hover:text-white text-[13px] font-semibold tracking-tight active:scale-[0.97] transition flex items-center justify-center gap-1.5"
                                :class="!(activeItem && activeItem.raw_price && activeItem.raw_price > 0 &&
                                isStorefrontEnabled) ? 'flex-1' : ''">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                <span>Reservasi Jadwal</span>
                            </button>
                            <a :href="activeItem ? waLink(activeItem.title || activeItem.name, true) : '#'"
                                target="_blank" rel="noopener"
                                class="h-10 px-3.5 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 text-[13px] font-semibold tracking-tight transition flex items-center justify-center gap-1.5"
                                title="Konsultasi WhatsApp">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                                <span class="hidden sm:inline">WhatsApp</span>
                            </a>
                            <button type="button" @click="activeModal = null"
                                class="h-10 px-4 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 text-[13px] font-medium tracking-tight hover:bg-black/[0.08] transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- All Services Grid View --}}
            <div x-show="activeModal === 'all-services'" class="space-y-4">
                <!-- Capsule Search -->
                <div class="relative">
                    <i data-lucide="search"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/35 dark:text-white/35"></i>
                    <input type="search" x-model="serviceSearch" @input="filterServices()"
                        placeholder="Cari nama atau deskripsi layanan..."
                        class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                    <template x-for="svc in filteredServices" :key="svc.title">
                        <button type="button" @click="openService(svc)"
                            class="text-left p-3.5 rounded-[16px] border border-black/5 dark:border-white/5 hover:border-black/15 dark:hover:border-white/15 transition flex flex-col justify-between group bg-black/[0.02] dark:bg-white/[0.03]">
                            <div>
                                <div
                                    class="relative aspect-[16/10] rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex items-center justify-center mb-2.5">
                                    <template x-if="svc.image_url">
                                        <img :src="svc.image_url" :alt="svc.title"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                            x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                    </template>
                                    <i data-lucide="sparkles" class="w-7 h-7 text-black/30 dark:text-white/30"
                                        :class="svc.image_url ? 'hidden' : ''"></i>
                                    <template x-if="svc.badge">
                                        <span
                                            class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-brand-primary text-white text-[9.5px] font-semibold uppercase tracking-wider shadow-sm"
                                            x-text="svc.badge"></span>
                                    </template>
                                </div>
                                <h3 class="font-semibold text-[13.5px] text-black dark:text-white line-clamp-1 tracking-tight leading-snug group-hover:text-brand-primary transition"
                                    x-text="svc.title"></h3>
                                <p class="text-[11.5px] text-black/55 dark:text-white/55 line-clamp-2 mt-0.5 leading-relaxed font-normal"
                                    x-text="svc.description || 'Layanan unggulan berkualitas siap memenuhi kebutuhan Anda.'">
                                </p>
                            </div>
                            <div
                                class="pt-2.5 mt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                <span class="text-[13px] font-bold text-brand-primary tabular-nums tracking-tight"
                                    x-text="svc.price || 'Hubungi kami'"></span>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-[11px] font-semibold text-black/70 dark:text-white/70 group-hover:bg-brand-primary group-hover:text-white transition flex items-center gap-0.5 tracking-tight">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="filteredServices.length === 0"
                    class="py-10 text-center text-black/40 dark:text-white/40">
                    <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-1 opacity-50"></i>
                    <p class="text-[13px] font-medium">Tidak ada layanan yang cocok dengan pencarian ini.</p>
                </div>
            </div>

            {{-- All Products Grid View --}}
            <div x-show="activeModal === 'all-products'" class="space-y-4">
                <!-- Capsule Search -->
                <div class="relative">
                    <i data-lucide="search"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/35 dark:text-white/35"></i>
                    <input type="search" x-model="productSearch" @input="filterProducts()"
                        placeholder="Cari nama atau deskripsi produk..."
                        class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition">
                </div>

                {{-- Category Pill Tabs (Apple Segmented Scroll) --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    <button type="button" @click="productCategory = 'all'; filterProducts()"
                        class="px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95"
                        :class="productCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' :
                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08]'">
                        Semua Kategori
                    </button>
                    @foreach ($productCategories as $category)
                        <button type="button" @click="productCategory = '{{ $category->id }}'; filterProducts()"
                            class="px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95"
                            :class="productCategory === '{{ $category->id }}' ? 'bg-brand-primary text-white shadow-sm' :
                                'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08]'">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 pt-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="openProduct(product)"
                            class="text-left p-3 rounded-[16px] border border-black/5 dark:border-white/5 hover:border-black/15 dark:hover:border-white/15 transition flex flex-col justify-between group bg-black/[0.02] dark:bg-white/[0.03]">
                            <div>
                                <div
                                    class="aspect-square rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex items-center justify-center mb-2">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                            x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                    </template>
                                    <i data-lucide="package" class="w-7 h-7 text-black/30 dark:text-white/30"
                                        :class="product.image_url ? 'hidden' : ''"></i>
                                </div>
                                <h3 class="font-semibold text-[13px] text-black dark:text-white line-clamp-2 tracking-tight leading-snug group-hover:text-brand-primary transition"
                                    x-text="product.name"></h3>
                                <p class="text-[11px] text-black/45 dark:text-white/45 line-clamp-1 mt-0.5 font-normal"
                                    x-text="product.category || ''"></p>
                            </div>
                            <div
                                class="pt-2 mt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                                <span class="text-[12.5px] font-bold text-brand-primary tabular-nums tracking-tight"
                                    x-text="'Rp ' + Number(product.price || 0).toLocaleString('id-ID')"></span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click.stop="addToCart(product, 1, false)"
                                        class="w-6 h-6 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:bg-brand-primary/15 hover:text-brand-primary transition flex items-center justify-center active:scale-90"
                                        title="Tambah ke Keranjang">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                    </button>
                                    <button type="button"
                                        @click.stop="activeModal = null; directCheckout(product, 1)"
                                        class="h-6 px-2 rounded-full bg-brand-primary text-white text-[10.5px] font-semibold hover:opacity-90 transition active:scale-95 shadow-sm"
                                        title="Beli Langsung">
                                        <span>Beli</span>
                                    </button>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="filteredProducts.length === 0"
                    class="py-10 text-center text-black/40 dark:text-white/40">
                    <i data-lucide="package-open" class="w-8 h-8 mx-auto mb-1 opacity-50"></i>
                    <p class="text-[13px] font-medium">Tidak ada produk yang cocok dengan pencarian / kategori ini.</p>
                </div>
            </div>

            {{-- All Gallery Grid View --}}
            <div x-show="activeModal === 'all-gallery'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[13px] text-black/65 dark:text-white/65 font-normal leading-relaxed">
                        Dokumentasi suasana, fasilitas, dan momen resmi {{ $business->name }}.
                    </p>
                    <span
                        class="px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-brand-primary/10 text-brand-primary border border-brand-primary/20 shrink-0">
                        <span x-text="galleryImages.length"></span> Foto
                    </span>
                </div>

                <div
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 pt-1 max-h-[60vh] overflow-y-auto pr-1">
                    <template x-for="(img, idx) in galleryImages" :key="idx">
                        <button type="button" @click="openGalleryLightbox(idx)"
                            class="group relative aspect-square rounded-[18px] overflow-hidden bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-left transition hover:scale-[1.02] active:scale-[0.98]">
                            <img :src="img.url" :alt="img.caption || 'Foto Galeri'" loading="lazy"
                                class="w-full h-full object-cover">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex flex-col justify-end p-2.5">
                                <p x-show="img.caption" x-text="img.caption"
                                    class="text-white text-[11.5px] font-medium line-clamp-2 drop-shadow tracking-tight">
                                </p>
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] text-white/90 font-semibold tracking-wide mt-1">
                                    <i data-lucide="zoom-in" class="w-3 h-3"></i>
                                    <span>Perbesar</span>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[12px] text-black/50 dark:text-white/50 font-normal">
                        Klik foto apa saja untuk membuka penampil layar penuh.
                    </span>
                    <button type="button" @click="activeModal = null"
                        class="h-9 px-5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/70 dark:text-white/70 text-[13px] font-medium tracking-tight transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- GALLERY LIGHTBOX MODAL (Apple Photos Viewer Style)                        --}}
    {{-- ========================================================================= --}}
    <div x-show="activeModal === 'gallery-lightbox'" x-cloak @keydown.escape.window="activeModal = null"
        @keydown.right.window="activeModal === 'gallery-lightbox' && nextGalleryImage()"
        @keydown.left.window="activeModal === 'gallery-lightbox' && prevGalleryImage()"
        class="fixed inset-0 z-[80] bg-black/95 backdrop-blur-2xl p-4 sm:p-6 flex flex-col justify-between"
        role="dialog" aria-modal="true">

        {{-- Top Bar: Status, Action Pills, Close --}}
        <div class="flex items-center justify-between text-white z-10">
            <div class="flex items-center gap-3">
                <span
                    class="px-3 py-1 rounded-full bg-white/10 text-xs font-semibold backdrop-blur-md tabular-nums tracking-tight">
                    <span x-text="activeGalleryIndex + 1"></span> / <span x-text="galleryImages.length"></span>
                </span>
                <span
                    class="text-xs font-medium text-white/60 tracking-tight hidden sm:inline">{{ $business->name }}</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="openAllGallery()"
                    class="h-8 px-3 rounded-full bg-white/10 hover:bg-white/20 text-white text-[12px] font-semibold flex items-center gap-1.5 backdrop-blur-md transition tracking-tight">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Lihat Semua Grid</span>
                </button>
                <button type="button" @click="activeModal = null"
                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center backdrop-blur-md transition"
                    aria-label="Tutup foto">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        {{-- Main Stage: Image & Left/Right Navigation --}}
        <div class="relative flex-1 flex items-center justify-center my-2 sm:my-4 select-none">
            {{-- Previous Button --}}
            <button type="button" @click="prevGalleryImage()" x-show="galleryImages.length > 1"
                class="absolute left-2 sm:left-6 z-10 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-white/10 hover:bg-white/25 active:scale-95 text-white flex items-center justify-center backdrop-blur-xl transition border border-white/10 shadow-lg"
                aria-label="Foto sebelumnya">
                <i data-lucide="chevron-left" class="w-6 h-6"></i>
            </button>

            {{-- Active Image with animation --}}
            <div class="max-w-4xl max-h-[70vh] sm:max-h-[78vh] flex items-center justify-center p-2">
                <img :src="galleryImages[activeGalleryIndex]?.url"
                    :alt="galleryImages[activeGalleryIndex]?.caption || 'Foto Galeri'"
                    class="max-w-full max-h-[68vh] sm:max-h-[76vh] object-contain rounded-[18px] sm:rounded-[24px] shadow-2xl transition-all duration-300">
            </div>

            {{-- Next Button --}}
            <button type="button" @click="nextGalleryImage()" x-show="galleryImages.length > 1"
                class="absolute right-2 sm:right-6 z-10 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-white/10 hover:bg-white/25 active:scale-95 text-white flex items-center justify-center backdrop-blur-xl transition border border-white/10 shadow-lg"
                aria-label="Foto selanjutnya">
                <i data-lucide="chevron-right" class="w-6 h-6"></i>
            </button>
        </div>

        {{-- Bottom Bar: Caption & Mini-thumbnails --}}
        <div class="space-y-3 z-10 max-w-2xl mx-auto w-full text-center">
            <template x-if="galleryImages[activeGalleryIndex]?.caption">
                <p x-text="galleryImages[activeGalleryIndex].caption"
                    class="text-xs sm:text-[13px] text-white/90 font-medium leading-relaxed px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md inline-block max-w-xl shadow-sm tracking-tight">
                </p>
            </template>

            {{-- Thumbnails strip if multiple images --}}
            <div x-show="galleryImages.length > 1"
                class="flex items-center justify-center gap-2 overflow-x-auto py-1 px-4 scrollbar-none">
                <template x-for="(thumb, idx) in galleryImages" :key="idx">
                    <button type="button" @click="activeGalleryIndex = idx"
                        class="w-11 h-11 sm:w-12 sm:h-12 rounded-[10px] overflow-hidden border-2 transition shrink-0"
                        :class="activeGalleryIndex === idx ? 'border-brand-primary scale-105 shadow-md' :
                            'border-transparent opacity-50 hover:opacity-100'">
                        <img :src="thumb.url" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MOBILE BOTTOM BAR (iOS 18 Tab Bar Style)                                  --}}
    {{-- ========================================================================= --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/85 dark:bg-[#161617]/85 backdrop-blur-xl border-t border-black/5 dark:border-white/10 px-2 pt-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))]"
        aria-label="Navigasi halaman">
        <div class="grid grid-cols-5 gap-1">
            <a href="#hero"
                class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1"
                :class="activeSection === 'hero' ? 'text-brand-primary font-semibold' : ''">
                <i data-lucide="home" class="w-4 h-4 mx-auto mb-0.5"></i>
                <span>Home</span>
            </a>
            <a href="#layanan"
                class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1"
                :class="activeSection === 'services' ? 'text-brand-primary font-semibold' : ''">
                <i data-lucide="package" class="w-4 h-4 mx-auto mb-0.5"></i>
                <span>Katalog</span>
            </a>

            {{-- Cart Tab with dynamic badge --}}
            <button type="button" @click="cartDrawerOpen = true"
                class="relative text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1 hover:text-brand-primary transition">
                <div class="relative w-4 h-4 mx-auto mb-0.5 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span x-show="cartCount > 0"
                        class="absolute -top-1.5 -right-2.5 px-1 py-0.2 rounded-full text-[9px] font-bold bg-[#007AFF] text-white tabular-nums leading-none shadow-xs"
                        x-text="cartCount"></span>
                </div>
                <span>Keranjang</span>
            </button>

            {{-- Reservation / Booking Tab --}}
            <button type="button" @click="openReservationModal()"
                class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1 hover:text-brand-primary transition">
                <i data-lucide="calendar" class="w-4 h-4 mx-auto mb-0.5"></i>
                <span>Reservasi</span>
            </button>

            <a href="#lokasi"
                class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1"
                :class="activeSection === 'contact' ? 'text-brand-primary font-semibold' : ''">
                <i data-lucide="map-pin" class="w-4 h-4 mx-auto mb-0.5"></i>
                <span>Kontak</span>
            </a>
        </div>
    </nav>

    {{-- ========================================================================= --}}
    {{-- FOOTER (Apple Clean Dark/Light Grounding)                                 --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['footer'])
        <footer
            class="border-t {{ $landingPage->dark_mode ? 'bg-[#161617] border-white/10 text-white/60' : 'bg-[#F5F5F7] border-black/5 text-black/60' }}">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
                <div class="grid grid-cols-1 sm:{{ $footerGridClass }} gap-10 lg:gap-12">
                    {{-- Brand and social --}}
                    @if ($sectionVisibility['footer_brand'])
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                @if ($landingPage->logo_url ?: $business->logo_url)
                                    <img src="{{ $landingPage->logo_url ?: $business->logo_url }}"
                                        alt="{{ $business->name }}"
                                        class="w-10 h-10 rounded-[10px] object-contain bg-white dark:bg-black/20 p-1 border border-black/5 dark:border-white/10"
                                        onerror="this.style.display='none';">
                                @endif
                                <div>
                                    <div
                                        class="font-bold text-[16px] text-black dark:text-white leading-tight tracking-tight">
                                        {{ $business->name }}</div>
                                    @if ($landingPage->industry_preset)
                                        <div
                                            class="text-[10px] sm:text-[10.5px] uppercase tracking-[0.06em] text-brand-primary font-semibold">
                                            {{ $landingPage->industry_preset }}</div>
                                    @endif
                                </div>
                            </div>
                            @if ($landingPage->footer_description ?: ($business->description ?: $landingPage->subheadline))
                                <p
                                    class="text-[13px] leading-relaxed max-w-sm text-black/60 dark:text-white/60 font-normal">
                                    {{ $landingPage->footer_description ?: ($business->description ?: $landingPage->subheadline) }}
                                </p>
                            @endif
                            @if ($activeChannels->isNotEmpty() || $hasWhatsapp)
                                <div class="space-y-2 pt-1">
                                    <span
                                        class="text-[11px] sm:text-[11.5px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-[0.05em] block">Media
                                        Sosial &amp; Marketplace</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($activeChannels as $channel)
                                            @if (filled($channel['url']))
                                                <a href="{{ $channel['url'] }}" target="_blank" rel="noopener"
                                                    aria-label="{{ $channel['label'] }}"
                                                    title="{{ $channel['label'] }}"
                                                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-white flex items-center justify-center transition shadow-sm {{ $channel['color'] }}">
                                                    <i data-lucide="{{ $channel['icon'] }}" class="w-4 h-4"></i>
                                                </a>
                                            @endif
                                        @endforeach
                                        @if ($hasWhatsapp)
                                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                                                rel="noopener" aria-label="WhatsApp" title="WhatsApp"
                                                class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] hover:bg-[#34C759] hover:text-white flex items-center justify-center transition shadow-sm">
                                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Navigation --}}
                    @if (
                        $sectionVisibility['footer_navigation'] &&
                            (($sectionVisibility['hero'] && $hasHero) ||
                                ($sectionVisibility['about'] && $hasAbout) ||
                                $hasServices ||
                                ($sectionVisibility['gallery'] && !empty($landingPage->gallery_images)) ||
                                ($sectionVisibility['contact'] && $hasContact)))
                        <div>
                            <h3
                                class="text-[12px] font-semibold uppercase tracking-[0.06em] text-black dark:text-white mb-3.5">
                                {{ $landingPage->footer_navigation_title ?: 'Navigasi' }}</h3>
                            <nav class="space-y-2.5 text-[13px] font-normal tracking-tight">
                                @if ($sectionVisibility['hero'] && $hasHero)
                                    <a href="#hero" class="block hover:text-brand-primary transition">Beranda</a>
                                @endif
                                @if ($sectionVisibility['about'] && $hasAbout)
                                    <a href="#tentang" class="block hover:text-brand-primary transition">Tentang
                                        Kami</a>
                                @endif
                                @if (
                                    ($sectionVisibility['services'] && $services->isNotEmpty()) ||
                                        ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty()))
                                    <a href="#layanan" class="block hover:text-brand-primary transition">Menu &amp;
                                        Layanan</a>
                                @endif
                                @if ($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
                                    <a href="#galeri" class="block hover:text-brand-primary transition">Galeri
                                        Foto</a>
                                @endif
                                @if ($sectionVisibility['contact'] && $hasContact)
                                    <a href="#lokasi" class="block hover:text-brand-primary transition">Kontak &amp;
                                        Lokasi</a>
                                @endif
                            </nav>
                        </div>
                    @endif

                    {{-- Services --}}
                    @if ($sectionVisibility['footer_services'] && $sectionVisibility['services'] && $services->isNotEmpty())
                        <div>
                            <h3
                                class="text-[12px] font-semibold uppercase tracking-[0.06em] text-black dark:text-white mb-3.5">
                                {{ $landingPage->footer_services_title ?: 'Layanan' }}</h3>
                            <nav class="space-y-2.5 text-[13px] font-normal tracking-tight">
                                @foreach ($services->take(6) as $service)
                                    @if (!empty($service['title']))
                                        <a href="#layanan"
                                            class="block hover:text-brand-primary transition">{{ $service['title'] }}</a>
                                    @endif
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    {{-- Contact --}}
                    @if ($sectionVisibility['footer_contact'] && $hasContact)
                        <div>
                            <h3
                                class="text-[12px] font-semibold uppercase tracking-[0.06em] text-black dark:text-white mb-3.5">
                                {{ $landingPage->footer_contact_title ?: 'Kontak' }}</h3>
                            <div class="space-y-2.5 text-[13px] font-normal tracking-tight">
                                @if ($landingPage->custom_address ?: $business->address)
                                    <div class="flex items-start gap-2"><i data-lucide="map-pin"
                                            class="w-4 h-4 text-brand-primary shrink-0 mt-0.5"></i><span>{{ $landingPage->custom_address ?: $business->address }}</span>
                                    </div>
                                @endif
                                @if ($landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone))
                                    <div class="flex items-center gap-2"><i data-lucide="phone"
                                            class="w-4 h-4 text-brand-primary shrink-0"></i><span
                                            class="tabular-nums">{{ $landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone) }}</span>
                                    </div>
                                @endif
                                @if ($landingPage->custom_email ?: $business->email)
                                    <div class="flex items-center gap-2"><i data-lucide="mail"
                                            class="w-4 h-4 text-brand-primary shrink-0"></i><span>{{ $landingPage->custom_email ?: $business->email }}</span>
                                    </div>
                                @endif
                            </div>
                            @if (
                                ($landingPage->footer_cta_text ?: $landingPage->cta_primary_text) &&
                                    ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone))
                                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 mt-4 h-9 px-4 rounded-full bg-brand-primary text-white text-[12px] font-semibold tracking-tight hover:opacity-90 active:scale-95 transition">
                                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                                    <span>{{ $landingPage->footer_cta_text ?: $landingPage->cta_primary_text }}</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="border-t border-black/5 dark:border-white/10">
                <div
                    class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px] text-black/50 dark:text-white/50 font-normal">
                    <p>{{ $landingPage->footer_copyright ?: '© ' . date('Y') . ' ' . $business->name . '. Hak cipta dilindungi undang-undang.' }}
                    </p>
                    <p>Didukung oleh <a href="https://cooca.id" target="_blank" rel="noopener"
                            class="font-semibold hover:underline text-brand-primary">Cooca</a></p>
                </div>
            </div>
        </footer>
    @endif

    {{-- ========================================================================= --}}
    {{-- FLOATING CART PILL (Apple HIG Dynamic Island Widget)                     --}}
    {{-- ========================================================================= --}}
    <div x-show="isStorefrontEnabled && cartCount > 0" x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-10 opacity-0 scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100 scale-100"
        x-transition:leave-end="translate-y-10 opacity-0 scale-95"
        class="fixed bottom-20 md:bottom-6 left-1/2 -translate-x-1/2 z-40 select-none">
        <button type="button" @click="cartDrawerOpen = true"
            class="h-12 sm:h-13 px-5 sm:px-6 rounded-full bg-black/90 dark:bg-white/95 text-white dark:text-black backdrop-blur-2xl shadow-[0_12px_40px_rgba(0,0,0,0.35)] border border-white/20 dark:border-black/20 flex items-center gap-3.5 hover:scale-105 active:scale-95 transition-all">
            <div class="relative flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span
                    class="absolute -top-2 -right-2.5 px-1.5 py-0.2 rounded-full text-[10.5px] font-extrabold bg-[#007AFF] text-white tabular-nums shadow-sm"
                    x-text="cartCount"></span>
            </div>
            <div class="h-4 w-px bg-white/20 dark:bg-black/20"></div>
            <div class="text-left leading-tight">
                <span class="text-[11px] opacity-70 block">Keranjang Belanja</span>
                <span class="text-[13.5px] font-bold tabular-nums" x-text="formatPrice(cartTotal)"></span>
            </div>
            <div class="w-7 h-7 rounded-full bg-white/20 dark:bg-black/10 flex items-center justify-center ml-1">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </div>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- SLIDE-OVER SHOPPING BAG DRAWER (Apple HIG Architecture)                   --}}
    {{-- ========================================================================= --}}
    <div x-show="cartDrawerOpen" x-cloak class="fixed inset-0 z-[70] overflow-hidden" role="dialog"
        aria-modal="true">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
            @click="cartDrawerOpen = false" x-show="cartDrawerOpen" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white dark:bg-[#1C1C1E] shadow-2xl border-l border-black/5 dark:border-white/10 flex flex-col justify-between"
                x-show="cartDrawerOpen" x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                {{-- Drawer Header --}}
                <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Keranjang
                                Belanja</h3>
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 tabular-nums"><span
                                    x-text="cartCount"></span> item dipilih</span>
                        </div>
                    </div>
                    <button type="button" @click="cartDrawerOpen = false"
                        class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/60 dark:text-white/60">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Drawer Item List --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4 divide-y divide-black/5 dark:divide-white/10">
                    <template x-for="item in cart" :key="item.id">
                        <div class="pt-4 first:pt-0 flex items-start gap-3.5">
                            <div
                                class="w-16 h-16 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 shrink-0 overflow-hidden flex items-center justify-center">
                                <template x-if="item.image_url">
                                    <img :src="item.image_url" :alt="item.name"
                                        class="w-full h-full object-cover">
                                </template>
                                <i x-show="!item.image_url" data-lucide="package"
                                    class="w-6 h-6 text-black/30 dark:text-white/30"></i>
                            </div>
                            <div class="flex-1 min-w-0 space-y-1">
                                <h4 class="text-[14px] font-bold text-black dark:text-white truncate"
                                    x-text="item.name"></h4>
                                <span class="text-[12.5px] font-semibold text-[#007AFF] tabular-nums block"
                                    x-text="formatPrice(item.price)"></span>

                                <div class="flex items-center justify-between pt-1">
                                    <div
                                        class="flex items-center gap-2 bg-black/5 dark:bg-white/5 px-2 py-0.5 rounded-full">
                                        <button type="button" @click="updateQuantity(item.id, -1)"
                                            class="w-5 h-5 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-bold">&minus;</button>
                                        <span
                                            class="text-[12px] font-bold text-black dark:text-white min-w-4 text-center tabular-nums"
                                            x-text="item.quantity"></span>
                                        <button type="button" @click="updateQuantity(item.id, 1)"
                                            class="w-5 h-5 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-bold">&plus;</button>
                                    </div>
                                    <button type="button" @click="removeFromCart(item.id)"
                                        class="text-black/40 hover:text-[#FF3B30] text-[11.5px] transition">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="cart.length === 0" class="py-12 text-center text-black/40 dark:text-white/40">
                        <i data-lucide="shopping-bag" class="w-10 h-10 mx-auto stroke-1 mb-2 opacity-50"></i>
                        <p class="text-[13px] font-medium">Keranjang belanja Anda masih kosong.</p>
                    </div>
                </div>

                {{-- Drawer Footer & Checkout Action --}}
                <div
                    class="p-5 border-t border-black/5 dark:border-white/10 space-y-3 bg-black/[0.02] dark:bg-white/[0.02]">
                    <div class="flex items-center justify-between text-[14px]">
                        <span class="font-medium text-black/60 dark:text-white/60">Subtotal Belanja</span>
                        <span class="font-extrabold text-[17px] text-black dark:text-white tabular-nums"
                            x-text="formatPrice(cartTotal)"></span>
                    </div>

                    <button type="button" @click="cartDrawerOpen = false; checkoutModalOpen = true;"
                        :disabled="cart.length === 0"
                        class="w-full h-12 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm">
                        <span>Lanjut ke Formulir Pembayaran</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- CHECKOUT MODAL (Apple HIG Checkout Architecture)                          --}}
    {{-- ========================================================================= --}}
    <div x-show="checkoutModalOpen" x-cloak
        class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[28px] p-6 shadow-2xl border border-black/10 dark:border-white/10 max-h-[90vh] overflow-y-auto space-y-5"
            @click.outside="if(!isCheckingOut) checkoutModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Checkout Pesanan
                        </h3>
                        <span class="text-[11.5px] text-black/50 dark:text-white/50">{{ $business->name }}</span>
                    </div>
                </div>
                <button type="button" @click="checkoutModalOpen = false"
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="checkoutError">
                <div
                    class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="checkoutError"></span>
                </div>
            </template>

            @guest('customer')
                <div
                    class="p-3.5 rounded-[16px] bg-gradient-to-br from-[#007AFF]/10 to-[#5856D6]/10 border border-[#007AFF]/20 space-y-2.5 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <p class="text-[13px] font-bold text-black dark:text-white">Masuk dengan Google</p>
                            <p class="text-[11.5px] text-black/60 dark:text-white/60">Wajib login &amp; verifikasi
                                WhatsApp sebelum memesan.</p>
                        </div>
                        <a href="{{ route('customer.auth.google') }}?redirect={{ urlencode(url()->current()) }}"
                            class="px-4 py-2 bg-[#007AFF] text-white rounded-xl text-[12.5px] font-bold hover:bg-[#0062CC] transition active:scale-95 shrink-0 flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="currentColor"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="currentColor"
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="currentColor"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                            Login Google
                        </a>
                    </div>
                </div>
            @else
                <div
                    class="p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[12px] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                        <span class="text-black/70 dark:text-white/70">Akun: <strong
                                class="text-black dark:text-white">{{ auth('customer')->user()->name }}</strong></span>
                    </div>
                    @if (!auth('customer')->user()->isPhoneVerified())
                        <a href="{{ route('customer.otp') }}"
                            class="text-[#FF9500] font-semibold hover:underline">Verifikasi OTP &rarr;</a>
                    @else
                        <span class="text-[#34C759] font-semibold text-[11.5px]">✓ Terverifikasi</span>
                    @endif
                </div>
            @endguest

            <form @submit.prevent="submitCheckout()" class="space-y-4">
                {{-- Data Pelanggan --}}
                <div class="space-y-3">
                    <span
                        class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">1.
                        Data Pemesan</span>
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama
                            Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" x-model="checkoutForm.customer_name" required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                WhatsApp <span class="text-red-500">*</span></label>
                            <input type="tel" x-model="checkoutForm.customer_phone" required
                                placeholder="08123456789"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Email
                                (Opsional)</label>
                            <input type="email" x-model="checkoutForm.customer_email"
                                placeholder="budi@example.com"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                </div>

                {{-- Opsi Pengiriman --}}
                <div class="space-y-3 pt-2">
                    <span
                        class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">2.
                        Pengiriman</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="setFulfillment('pickup')"
                            :class="checkoutForm.fulfillment_type === 'pickup' ?
                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] font-bold' :
                                'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                            class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2">
                            <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                            <span>Ambil Sendiri</span>
                        </button>
                        <button type="button" @click="setFulfillment('merchant_delivery')"
                            :class="checkoutForm.fulfillment_type === 'merchant_delivery' ?
                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] font-bold' :
                                'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                            class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                            <span>Kurir Toko</span>
                        </button>
                    </div>

                    <div x-show="checkoutForm.fulfillment_type === 'merchant_delivery'" class="space-y-3 pt-1">
                        <div>
                            <label
                                class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Alamat
                                Pengiriman Lengkap <span class="text-red-500">*</span></label>
                            <textarea x-model="checkoutForm.shipping_address" rows="2"
                                placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan..."
                                class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                        </div>

                        {{-- Aturan Ongkir / Pilihan Kurir Toko --}}
                        <div x-show="checkoutForm.shipping_options && checkoutForm.shipping_options.length > 0"
                            class="space-y-1.5">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Pilihan
                                Kurir &amp; Tarif Ongkir</label>
                            <div class="space-y-1.5">
                                <template x-for="opt in checkoutForm.shipping_options" :key="opt.id">
                                    <label
                                        class="p-2.5 rounded-[12px] border border-black/10 dark:border-white/10 flex items-center justify-between cursor-pointer hover:bg-black/5 dark:hover:bg-white/5 transition"
                                        :class="checkoutForm.shipping_rule_id === opt.id ? 'border-[#007AFF] bg-[#007AFF]/5' :
                                            ''">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="shipping_rule_choice"
                                                :value="opt.id" x-model="checkoutForm.shipping_rule_id"
                                                @change="fetchShippingQuote(opt.id)"
                                                class="text-[#007AFF] focus:ring-[#007AFF]">
                                            <div>
                                                <span class="text-[13px] font-bold text-black dark:text-white block"
                                                    x-text="opt.name"></span>
                                                <span class="text-[11px] text-black/50 dark:text-white/50"
                                                    x-text="opt.description"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <span x-show="opt.is_free"
                                                class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759]">Gratis</span>
                                            <span x-show="!opt.is_free"
                                                class="text-[12.5px] font-bold text-black dark:text-white tabular-nums"
                                                x-text="formatPrice(opt.fee)"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div x-show="checkoutForm.is_loading_shipping"
                            class="text-[11.5px] text-[#007AFF] flex items-center gap-1.5 py-1">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                            <span>Menghitung ongkos kirim...</span>
                        </div>
                    </div>
                </div>

                {{-- Opsi Jadwal Pemesanan (Scheduled Order) --}}
                @if ($storeSetting?->allow_scheduled_order)
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Jadwal
                                Pesanan</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="checkoutForm.is_scheduled" class="sr-only peer">
                                <div
                                    class="w-9 h-5 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                </div>
                                <span class="ml-2 text-[12px] font-medium text-black/70 dark:text-white/70">Pesan
                                    Terjadwal</span>
                            </label>
                        </div>
                        <div x-show="checkoutForm.is_scheduled" x-transition
                            class="space-y-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10">
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Pilih
                                    Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" min="{{ date('Y-m-d') }}"
                                    x-model="checkoutForm.scheduled_date" :required="checkoutForm.is_scheduled"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Slot
                                    Waktu (Opsional)</label>
                                <select x-model="checkoutForm.scheduled_time_slot"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="">-- Bebas Waktu / Jam Buka Outlet --</option>
                                    <option value="Pagi (08:00 - 11:30)">Pagi (08:00 - 11:30)</option>
                                    <option value="Siang (12:00 - 15:30)">Siang (12:00 - 15:30)</option>
                                    <option value="Sore / Malam (16:00 - 20:00)">Sore / Malam (16:00 - 20:00)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Rekening Pembayaran --}}
                @if ($paymentMethods->isNotEmpty())
                    <div class="space-y-3 pt-2">
                        <span
                            class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">3.
                            Pembayaran Transfer Bank / QRIS</span>
                        <div class="space-y-2">
                            @foreach ($paymentMethods as $pm)
                                <label
                                    class="p-3 rounded-[14px] border border-black/10 dark:border-white/10 flex items-center justify-between cursor-pointer hover:bg-black/5 dark:hover:bg-white/5 transition"
                                    :class="checkoutForm.payment_method_id === '{{ $pm->id }}' ?
                                        'border-[#007AFF] bg-[#007AFF]/5' : ''">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="payment_method_id"
                                            value="{{ $pm->id }}" x-model="checkoutForm.payment_method_id"
                                            class="text-[#007AFF] focus:ring-[#007AFF]">
                                        <div>
                                            <span
                                                class="text-[13.5px] font-bold text-black dark:text-white block">{{ $pm->bank_name }}</span>
                                            @if ($pm->account_number)
                                                <span
                                                    class="text-[11.5px] font-mono text-black/60 dark:text-white/60">{{ $pm->account_number }}
                                                    a/n {{ $pm->account_holder }}</span>
                                            @else
                                                <span class="text-[11px] text-[#007AFF] font-medium">QRIS Langsung
                                                    Toko</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] font-bold uppercase px-2 py-0.5 rounded-full {{ $pm->type === 'qris' ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                                        {{ $pm->type === 'qris' ? 'QRIS' : 'Transfer' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Catatan --}}
                <div class="pt-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Catatan
                        Pesanan (Opsional)</label>
                    <input type="text" x-model="checkoutForm.notes"
                        placeholder="Contoh: Jangan terlalu pedas, titip di satpam, dll."
                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                {{-- Total & Submit --}}
                <div class="pt-4 border-t border-black/5 dark:border-white/10 space-y-2">
                    <div class="flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
                        <span>Subtotal Belanja</span>
                        <span class="tabular-nums font-semibold" x-text="formatPrice(cartTotal)"></span>
                    </div>

                    <div x-show="checkoutForm.fulfillment_type === 'merchant_delivery'"
                        class="flex items-center justify-between text-[13px]">
                        <span class="text-black/60 dark:text-white/60">Ongkos Kirim</span>
                        <template x-if="checkoutForm.is_free_shipping">
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759]">Bebas
                                Ongkir</span>
                        </template>
                        <template x-if="!checkoutForm.is_free_shipping">
                            <span class="tabular-nums font-semibold text-black dark:text-white"
                                x-text="formatPrice(checkoutForm.shipping_fee)"></span>
                        </template>
                    </div>

                    <div class="flex items-center justify-between text-[15px] pt-1">
                        <span class="text-black/70 dark:text-white/70 font-semibold">Total Pembayaran</span>
                        <span class="text-[20px] font-extrabold text-[#007AFF] tabular-nums"
                            x-text="formatPrice(grandTotal)"></span>
                    </div>

                    <button type="submit" :disabled="isCheckingOut"
                        class="w-full h-12 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm">
                        <span x-show="!isCheckingOut">Konfirmasi Pesanan &amp; Dapatkan Instruksi Bayar</span>
                        <span x-show="isCheckingOut">Memproses Pesanan...</span>
                        <i x-show="!isCheckingOut" data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REQUEST ORDER MODAL (Apple Bento Sheet for RFQ / Custom Orders)            --}}
    {{-- ========================================================================= --}}
    <div x-show="requestOrderModalOpen" x-transition.opacity @keydown.escape.window="requestOrderModalOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-md"
        style="display: none;">
        <div @click.away="requestOrderModalOpen = false" x-show="requestOrderModalOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white dark:bg-[#1C1C1E] rounded-[28px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-5">

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="space-y-0.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] block">Request Order
                        Khusus</span>
                    <h3 class="text-[18px] font-bold text-black dark:text-white">Pesan Kustom / Pre-Order</h3>
                </div>
                <button type="button" @click="requestOrderModalOpen = false"
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div
                class="p-3.5 rounded-[14px] bg-[#007AFF]/5 border border-[#007AFF]/15 text-[12px] text-black/70 dark:text-white/70 flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                <span>Pesanan khusus memerlukan peninjauan oleh tim kami. Anda akan menerima notifikasi penawaran harga
                    &amp; konfirmasi ketersediaan melalui WhatsApp.</span>
            </div>

            <template x-if="requestOrderForm.error">
                <div
                    class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="requestOrderForm.error"></span>
                </div>
            </template>

            <form @submit.prevent="submitRequestOrder()" class="space-y-4">
                {{-- Data Pemesan --}}
                <div class="space-y-3">
                    <span
                        class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">1.
                        Data Pemesan</span>
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama
                            Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" x-model="requestOrderForm.customer_name" required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                WhatsApp <span class="text-red-500">*</span></label>
                            <input type="tel" x-model="requestOrderForm.customer_phone" required
                                placeholder="08123456789"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Email
                                (Opsional)</label>
                            <input type="email" x-model="requestOrderForm.customer_email"
                                placeholder="budi@example.com"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                </div>

                {{-- Detail Kebutuhan Kustom --}}
                <div class="space-y-3 pt-2">
                    <span
                        class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">2.
                        Kebutuhan Pesanan</span>
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Item /
                            Menu yang Diminta <span class="text-red-500">*</span></label>
                        <input type="text" x-model="requestOrderForm.item_name" required
                            placeholder="Contoh: Paket Katering 50 Porsi, Tumpeng Mini, Souvenir Khusus"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label
                                class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Perkiraan
                                Jumlah / Porsi <span class="text-red-500">*</span></label>
                            <input type="number" min="1" x-model="requestOrderForm.item_qty" required
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label
                                class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Target
                                Tanggal Dibutuhkan</label>
                            <input type="date" min="{{ date('Y-m-d') }}"
                                x-model="requestOrderForm.scheduled_date"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Detail
                            &amp; Spesifikasi Kebutuhan</label>
                        <textarea x-model="requestOrderForm.notes" rows="3"
                            placeholder="Tuliskan selengkap mungkin: jenis masakan, kemasan box/bungkus, pantangan/alergi, budget target, dll."
                            class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                    </div>
                </div>

                {{-- Pengiriman --}}
                <div class="space-y-3 pt-2">
                    <span
                        class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">3.
                        Opsi Pengambilan / Kirim</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="requestOrderForm.fulfillment_type = 'pickup'"
                            :class="requestOrderForm.fulfillment_type === 'pickup' ?
                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] font-bold' :
                                'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                            class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2">
                            <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                            <span>Ambil Sendiri</span>
                        </button>
                        <button type="button" @click="requestOrderForm.fulfillment_type = 'merchant_delivery'"
                            :class="requestOrderForm.fulfillment_type === 'merchant_delivery' ?
                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] font-bold' :
                                'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                            class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                            <span>Kirim ke Alamat</span>
                        </button>
                    </div>
                    <div x-show="requestOrderForm.fulfillment_type === 'merchant_delivery'" class="pt-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Alamat
                            Tujuan Pengiriman</label>
                        <textarea x-model="requestOrderForm.shipping_address" rows="2"
                            placeholder="Nama jalan, nomor gedung/rumah, kelurahan..."
                            class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="pt-4 border-t border-black/5 dark:border-white/10">
                    <button type="submit" :disabled="requestOrderForm.is_submitting"
                        class="w-full h-12 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm">
                        <span x-show="!requestOrderForm.is_submitting">Kirim Permintaan Pesanan Khusus</span>
                        <span x-show="requestOrderForm.is_submitting">Mengirim Permintaan...</span>
                        <i x-show="!requestOrderForm.is_submitting" data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- RESERVATION MODAL (Apple HIG Sheet for Table & Service Bookings)           --}}
    {{-- ========================================================================= --}}
    <div x-show="reservationModalOpen" x-cloak @keydown.escape.window="reservationModalOpen = false"
        class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[28px] p-6 shadow-2xl border border-black/10 dark:border-white/10 max-h-[90vh] overflow-y-auto space-y-5"
            @click.outside="if(!isSubmittingReservation) reservationModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Reservasi &amp;
                            Booking Jadwal</h3>
                        <span class="text-[11.5px] text-black/50 dark:text-white/50">{{ $business->name }}</span>
                    </div>
                </div>
                <button type="button" @click="reservationModalOpen = false"
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="reservationSuccess">
                <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-center space-y-2">
                    <div
                        class="w-10 h-10 rounded-full bg-[#34C759]/20 text-[#34C759] flex items-center justify-center mx-auto">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </div>
                    <h4 class="font-bold text-[15px] text-black dark:text-white">Permintaan Reservasi Terkirim!</h4>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70">
                        Tim kami akan segera memeriksa ketersediaan jadwal/meja dan mengirimkan konfirmasi melalui
                        WhatsApp ke nomor Anda.
                    </p>
                    <div class="pt-2">
                        <button type="button" @click="reservationModalOpen = false; reservationSuccess = false;"
                            class="px-5 py-2 rounded-full bg-[#34C759] text-white font-semibold text-[13px] hover:opacity-95 transition active:scale-95">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="!reservationSuccess">
                <div>
                    <template x-if="reservationError">
                        <div
                            class="mb-4 p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="reservationError"></span>
                        </div>
                    </template>

                    @guest('customer')
                        <div
                            class="mb-4 p-3.5 rounded-[16px] bg-gradient-to-br from-[#007AFF]/10 to-[#5856D6]/10 border border-[#007AFF]/20 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                            <div class="space-y-0.5">
                                <p class="text-[13px] font-bold text-black dark:text-white">Masuk dengan Google</p>
                                <p class="text-[11.5px] text-black/60 dark:text-white/60">Wajib login &amp; verifikasi
                                    WhatsApp untuk reservasi.</p>
                            </div>
                            <a href="{{ route('customer.auth.google') }}?redirect={{ urlencode(url()->current()) }}"
                                class="px-4 py-2 bg-[#007AFF] text-white rounded-xl text-[12.5px] font-bold hover:bg-[#0062CC] transition active:scale-95 shrink-0 flex items-center gap-1.5 shadow-sm">
                                Login Google
                            </a>
                        </div>
                    @else
                        <div
                            class="mb-4 p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[12px] flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                <span class="text-black/70 dark:text-white/70">Akun: <strong
                                        class="text-black dark:text-white">{{ auth('customer')->user()->name }}</strong></span>
                            </div>
                            @if (!auth('customer')->user()->isPhoneVerified())
                                <a href="{{ route('customer.otp') }}"
                                    class="text-[#FF9500] font-semibold hover:underline">Verifikasi OTP &rarr;</a>
                            @else
                                <span class="text-[#34C759] font-semibold text-[11.5px]">✓ Terverifikasi</span>
                            @endif
                        </div>
                    @endguest

                    <form @submit.prevent="submitReservation()" class="space-y-4">
                        {{-- Data Tamu --}}
                        <div class="space-y-3">
                            <span
                                class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">1.
                                Data Pemesan</span>
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama
                                    Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" x-model="reservationForm.customer_name" required
                                    placeholder="Contoh: Budi Santoso"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                        WhatsApp <span class="text-red-500">*</span></label>
                                    <input type="tel" x-model="reservationForm.customer_phone" required
                                        placeholder="08123456789"
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Email
                                        (Opsional)</label>
                                    <input type="email" x-model="reservationForm.customer_email"
                                        placeholder="budi@example.com"
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                </div>
                            </div>
                        </div>

                        {{-- Waktu & Jumlah Tamu --}}
                        <div class="space-y-3 pt-2">
                            <span
                                class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">2.
                                Jadwal &amp; Tamu</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tanggal
                                        Reservasi <span class="text-red-500">*</span></label>
                                    <input type="date" min="{{ date('Y-m-d') }}"
                                        x-model="reservationForm.reservation_date" required
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Waktu
                                        / Jam Kunjungan <span class="text-red-500">*</span></label>
                                    <select x-model="reservationForm.time_slot" required
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                        <option value="10:00 - 12:00">10:00 - 12:00 (Pagi)</option>
                                        <option value="12:00 - 14:00">12:00 - 14:00 (Makan Siang)</option>
                                        <option value="14:00 - 16:00">14:00 - 16:00 (Siang)</option>
                                        <option value="16:00 - 18:00">16:00 - 18:00 (Sore)</option>
                                        <option value="18:30 - 20:30">18:30 - 20:30 (Makan Malam)</option>
                                        <option value="20:30 - 22:00">20:30 - 22:00 (Malam)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Jumlah
                                    Orang / Tamu <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center gap-3 bg-black/5 dark:bg-white/5 px-3 py-1.5 rounded-[14px] border border-black/10 dark:border-white/10">
                                        <button type="button"
                                            @click="if (reservationForm.guest_count > 1) reservationForm.guest_count--"
                                            class="w-7 h-7 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black dark:text-white font-bold">&minus;</button>
                                        <span
                                            class="text-[14px] font-extrabold text-black dark:text-white min-w-8 text-center tabular-nums"
                                            x-text="reservationForm.guest_count + ' Orang'"></span>
                                        <button type="button" @click="reservationForm.guest_count++"
                                            class="w-7 h-7 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black dark:text-white font-bold">&plus;</button>
                                    </div>
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50">Dapat disesuaikan
                                        jika rombongan</span>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan / Permintaan Khusus --}}
                        <div class="space-y-2 pt-1">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Catatan
                                Tambahan (Layanan / Meja Khusus)</label>
                            <textarea x-model="reservationForm.notes" rows="2"
                                placeholder="Contoh: Meja non-smoking, baby chair, dekorasi ulang tahun, dll."
                                class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759] resize-none"></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-3 border-t border-black/5 dark:border-white/10">
                            <button type="submit" :disabled="isSubmittingReservation"
                                class="w-full h-12 rounded-[16px] bg-[#34C759] hover:bg-[#2FB350] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm">
                                <span x-show="!isSubmittingReservation">Kirim Permintaan Reservasi</span>
                                <span x-show="isSubmittingReservation">Memproses Reservasi...</span>
                                <i x-show="!isSubmittingReservation" data-lucide="arrow-right"
                                    class="w-4 h-4"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (Apple Frosted Glass Widget)                     --}}
    {{-- ========================================================================= --}}
    <div class="fixed bottom-24 right-4 md:bottom-6 md:right-6 z-40 flex flex-col items-end select-none">

        {{-- Floating Greeting Bubble --}}
        <div x-show="waChatOpen" x-transition.opacity
            class="mb-3 p-4 rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl shadow-xl border border-black/10 dark:border-white/10 max-w-xs text-[12px] space-y-2.5"
            style="display: none;">
            <div class="flex items-center justify-between">
                <span
                    class="font-semibold text-[13px] text-black dark:text-white tracking-tight">{{ $business->name }}</span>
                <button @click="waChatOpen = false"
                    class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed font-normal">
                Halo! Ada yang bisa kami bantu seputar produk atau layanan kami?
            </p>
            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                class="w-full h-9 rounded-full bg-brand-primary text-white font-semibold text-[12.5px] tracking-tight text-center flex items-center justify-center gap-1.5 hover:opacity-90 active:scale-95 transition">
                <span>Mulai Obrolan WhatsApp</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Button Trigger (Apple Touch Circle) --}}
        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
            class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-[0_8px_25px_rgba(37,211,102,0.35)] hover:scale-105 active:scale-95 transition-all duration-200 group relative"
            aria-label="Hubungi WhatsApp">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z" />
            </svg>
            <span
                class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-[#FF3B30] border-2 border-white dark:border-black animate-pulse"></span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>

</html>
