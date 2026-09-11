<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO METADATA --}}
    <title>{{ $landingPage->meta_title ?: $business->name }}</title>
    @if($landingPage->meta_description ?: $landingPage->subheadline)<meta name="description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">@endif
    @if($landingPage->meta_keywords)<meta name="keywords" content="{{ $landingPage->meta_keywords }}">@endif
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Social Sharing --}}
    <meta property="og:type" content="business.business">
    <meta property="og:title" content="{{ $landingPage->meta_title ?: $business->name }}">
    @if($landingPage->meta_description ?: $landingPage->subheadline)<meta property="og:description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">@endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $business->name }}">
    @if($landingPage->og_image_url || $landingPage->hero_image_url || $business->logo_url)
        <meta property="og:image" content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $landingPage->meta_title ?: $business->name }}">
    @if($landingPage->meta_description ?: $landingPage->subheadline)<meta name="twitter:description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">@endif
    @if($landingPage->og_image_url || $landingPage->hero_image_url || $business->logo_url)
        <meta name="twitter:image" content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}">
    @endif

    {{-- Fonts: System Fonts & Inter fallback --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
            $r = 0; $g = 122; $b = 255;
        }
        $themeRgb = "{$r}, {$g}, {$b}";

        $savedSectionVisibility = $landingPage->section_visibility ?? [];
        $services = collect($landingPage->custom_services ?? [])->map(fn (array $service): array => [
            ...$service,
            'description' => $service['description'] ?? $service['desc'] ?? '',
        ]);
        $testimonials = collect($landingPage->testimonials ?? [])->map(fn (array $testimonial): array => [
            ...$testimonial,
            'quote' => $testimonial['quote'] ?? $testimonial['comment'] ?? '',
        ]);
        $faqs = collect($landingPage->faqs ?? [])->map(fn (array $faq): array => [
            'question' => $faq['question'] ?? $faq['q'] ?? '',
            'answer' => $faq['answer'] ?? $faq['a'] ?? '',
        ]);
        $allGalleryImages = collect($landingPage->gallery_images ?? [])->map(function ($img, $idx) {
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
        })->filter(fn ($item) => filled($item['url']))->values();
        $bentoGalleryImages = $allGalleryImages->take(5);
        $totalGalleryCount = $allGalleryImages->count();
        $extraGalleryCount = max(0, $totalGalleryCount - 5);

        $hasHero = filled($landingPage->headline)
            || filled($landingPage->subheadline)
            || filled($landingPage->announcement_badge)
            || filled($landingPage->hero_image_url)
            || filled($landingPage->cta_primary_text)
            || filled($landingPage->cta_secondary_text);
        $hasAbout = filled($landingPage->about_title)
            || filled($landingPage->about_story)
            || filled($landingPage->about_image_url)
            || !empty($landingPage->operational_hours);
        $hasServices = $services->isNotEmpty()
            || ($landingPage->show_pos_products && $posProducts->isNotEmpty());
        $hasContact = filled($landingPage->custom_address)
            || filled($business->address)
            || filled($landingPage->custom_phone)
            || filled($landingPage->whatsapp_number)
            || filled($business->phone)
            || filled($landingPage->custom_email)
            || filled($business->email)
            || filled($landingPage->google_maps_embed_url)
            || !empty($landingPage->social_links);
        $sectionVisibility = array_merge([
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
        ], $savedSectionVisibility);
        $footerGridKeys = ['footer_brand', 'footer_navigation', 'footer_services', 'footer_contact'];
        $footerGridCount = collect($footerGridKeys)->filter(fn (string $key): bool => $sectionVisibility[$key])->count();
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

        $activeChannels = collect($channelConfigs)->filter(function ($c) use ($landingPage) {
            $val = data_get($landingPage->social_links, $c['key']) ?: $c['fallback'];
            return filled($val);
        })->map(function ($c) use ($landingPage) {
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
        })->values();

        $hasWhatsapp = filled($landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone));
    ?>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"SF Pro Text"', '"Inter"', 'system-ui', 'sans-serif'],
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
                        products = products.filter(product => product.name.toLowerCase().includes(query) || (product.description || '').toLowerCase().includes(query));
                    }
                    this.filteredProducts = products;
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                },
                filterServices() {
                    let list = this.services;
                    if (this.serviceSearch.trim()) {
                        const query = this.serviceSearch.trim().toLowerCase();
                        list = list.filter(s => (s.title || '').toLowerCase().includes(query) || (s.description || '').toLowerCase().includes(query));
                    }
                    this.filteredServices = list;
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                },
                formatPrice(price) { return 'Rp ' + Number(price || 0).toLocaleString('id-ID'); },
                waLink(label) { return '{{ $landingPage->getWhatsAppUrl() }}' + '&text=' + encodeURIComponent('Halo ' + @json($business->name) + ', saya tertarik dengan: ' + (label || 'Produk/Layanan')); },
                openProduct(product) { this.activeItem = product; this.activeModal = 'product'; this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); },
                openService(service) { this.activeItem = service; this.activeModal = 'service'; this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); },

                // Gallery Lightbox & Modal Controls
                galleryImages: {{ Js::from($allGalleryImages) }},
                activeGalleryIndex: 0,
                openAllGallery() {
                    this.activeModal = 'all-gallery';
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                },
                openGalleryLightbox(idx = 0) {
                    if (!this.galleryImages.length) return;
                    this.activeGalleryIndex = Math.max(0, Math.min(idx, this.galleryImages.length - 1));
                    this.activeModal = 'gallery-lightbox';
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                },
                nextGalleryImage() {
                    if (this.galleryImages.length <= 1) return;
                    this.activeGalleryIndex = (this.activeGalleryIndex + 1) % this.galleryImages.length;
                },
                prevGalleryImage() {
                    if (this.galleryImages.length <= 1) return;
                    this.activeGalleryIndex = (this.activeGalleryIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
                }
            };
        };
    </script>

    <style>
        [x-cloak] { display: none !important; }
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
        /* Apple Bento UI Design System */
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
        .marquee-track:hover { animation-play-state: paused; }
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; }
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

<body class="{{ $landingPage->dark_mode ? 'dark bg-[#000000] text-[#FFFFFF]' : 'bg-[#F5F5F7] text-[#1D1D1F]' }} antialiased selection:bg-brand-primary selection:text-white pb-20 md:pb-0"
    x-data="landingPageState()">

    {{-- ========================================================================= --}}
    {{-- ANNOUNCEMENT MARQUEE (Apple Banner Style)                                  --}}
    {{-- ========================================================================= --}}
    @if($landingPage->announcement_badge)
        <aside class="relative overflow-hidden bg-brand-primary text-white text-[12px] font-medium leading-tight select-none">
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
    <header class="sticky top-0 z-40 w-full backdrop-blur-xl {{ $landingPage->dark_mode ? 'bg-[#000000]/80 border-white/10' : 'bg-[#FFFFFF]/80 border-black/5' }} border-b transition-colors">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-4">

            {{-- Brand / Logo (Apple Squircle Icon) --}}
            <a href="#hero" class="flex items-center gap-3 group select-none">
                @if($landingPage->logo_url ?: $business->logo_url)
                    <img src="{{ $landingPage->logo_url ?: $business->logo_url }}" alt="{{ $business->name }}"
                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] object-contain border border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] p-1 shadow-sm group-hover:scale-105 transition-transform"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="hidden w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-brand-primary text-white items-center justify-center font-bold text-xs shadow-sm">{{ strtoupper(substr($business->name, 0, 2)) }}</div>
                @else
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-brand-primary text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <span class="font-semibold text-[14px] sm:text-[15px] tracking-tight block leading-tight text-black dark:text-white group-hover:opacity-80 transition">{{ $business->name }}</span>
                    @if(collect($landingPage->operational_hours ?? [])->contains(fn ($hours) => !empty($hours['is_open'])))
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#34C759]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>Buka Hari Ini</span>
                        </span>
                    @endif
                </div>
            </a>

            {{-- Navigation Links (Desktop Apple Segmented Capsule Style) --}}
            <nav class="hidden md:flex items-center gap-1 text-[13px] font-medium text-black/70 dark:text-white/70">
                @if($sectionVisibility['services'] && $hasServices)
                    <a href="#layanan" class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Menu &amp; Layanan</a>
                @endif
                @if($sectionVisibility['about'] && $hasAbout)
                    <a href="#tentang" class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Tentang Kami</a>
                @endif
                @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
                    <a href="#galeri" class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Galeri</a>
                @endif
                @if($sectionVisibility['faq'] && $faqs->isNotEmpty())
                    <a href="#faq" class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Tanya Jawab</a>
                @endif
                @if($sectionVisibility['contact'] && $hasContact)
                    <a href="#lokasi" class="px-3 py-1.5 rounded-full hover:bg-black/5 dark:hover:bg-white/10 hover:text-black dark:hover:text-white transition">Lokasi &amp; Kontak</a>
                @endif
            </nav>

            {{-- CTA WhatsApp Navbar Button (Apple Capsule Button) --}}
            <div class="flex items-center gap-2">
                @if($landingPage->cta_primary_text && ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone))
                <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold hover:opacity-90 active:scale-[0.97] transition-all shadow-[0_1px_2px_rgba(0,0,0,0.08)]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
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
            @if($sectionVisibility['services'] && $hasServices)
                <a href="#layanan" @click="mobileMenuOpen = false" class="block py-2 text-black/80 dark:text-white/80">Menu &amp; Layanan</a>
            @endif
            @if($sectionVisibility['about'] && $hasAbout)
                <a href="#tentang" @click="mobileMenuOpen = false" class="block py-2 text-black/80 dark:text-white/80">Tentang Kami</a>
            @endif
            @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
                <a href="#galeri" @click="mobileMenuOpen = false" class="block py-2 text-black/80 dark:text-white/80">Galeri Foto</a>
            @endif
            @if($sectionVisibility['faq'] && $faqs->isNotEmpty())
                <a href="#faq" @click="mobileMenuOpen = false" class="block py-2 text-black/80 dark:text-white/80">Tanya Jawab (FAQ)</a>
            @endif
            @if($sectionVisibility['contact'] && $hasContact)
                <a href="#lokasi" @click="mobileMenuOpen = false" class="block py-2 text-black/80 dark:text-white/80">Lokasi &amp; Kontak</a>
            @endif
            @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                    class="w-full mt-3 h-11 rounded-full bg-brand-primary text-white text-center font-semibold text-[14px] flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    <span>{{ $landingPage->cta_primary_text ?: 'Chat WhatsApp' }}</span>
                </a>
            @endif
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- HERO SECTION (2-Grid Clean Apple HIG Architecture)                        --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['hero'] && $hasHero)
    <section id="hero" data-section="hero" class="relative overflow-hidden pt-12 pb-16 sm:pt-20 sm:pb-24">
        {{-- Ambient Depth Glow Mesh --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-72 bg-brand-primary opacity-[0.12] dark:opacity-[0.18] blur-[120px] pointer-events-none rounded-full"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10 grid grid-cols-1 {{ $landingPage->hero_image_url ? 'lg:grid-cols-12 gap-10 lg:gap-16' : 'max-w-3xl' }} items-center">

            {{-- Left Column: Brand, Badges, Headline, Subheadline, CTAs --}}
            <div class="{{ $landingPage->hero_image_url ? 'lg:col-span-7' : 'w-full text-center' }} text-center lg:text-left space-y-6">
                {{-- Status Pill & Badges --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2.5">
                    @if($landingPage->announcement_badge)
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[12px] sm:text-[12.5px] font-semibold bg-brand-primary/10 border border-brand-primary/25 text-brand-primary shadow-sm tracking-[-0.01em]">
                            <span class="w-2 h-2 rounded-full bg-brand-primary animate-ping"></span>
                            <span>{{ $landingPage->announcement_badge }}</span>
                        </div>
                    @endif

                    @if(collect($landingPage->operational_hours ?? [])->contains(fn ($hours) => !empty($hours['is_open'])))
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] sm:text-[12.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20 tracking-[-0.01em]">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>Buka Hari Ini</span>
                        </div>
                    @endif
                </div>

                {{-- Display Headline (SF Pro Typography Style) --}}
                @if($landingPage->headline)
                    <h1 class="text-3xl sm:text-5xl lg:text-[52px] xl:text-[56px] font-extrabold tracking-tight lg:tracking-[-0.035em] leading-[1.12] text-black dark:text-white max-w-3xl lg:max-w-none">
                        {{ $landingPage->headline }}
                    </h1>
                @endif

                {{-- Subheadline --}}
                @if($landingPage->subheadline)
                    <p class="text-[15px] sm:text-[17px] text-black/65 dark:text-white/65 max-w-2xl leading-relaxed font-normal tracking-[-0.01em] {{ $landingPage->hero_image_url ? '' : 'mx-auto' }}">
                        {{ $landingPage->subheadline }}
                    </p>
                @endif

                {{-- Action Buttons & Micro Trust --}}
                @if($landingPage->cta_primary_text || $landingPage->cta_secondary_text)
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                    @if($landingPage->cta_primary_text)
                    <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                        class="w-full sm:w-auto h-12 px-7 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(0,0,0,0.15)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                        <span>{{ $landingPage->cta_primary_text }}</span>
                    </a>
                    @endif

                    @if($landingPage->cta_secondary_text)
                        <a href="{{ $landingPage->cta_secondary_url ?: '#layanan' }}"
                            class="w-full sm:w-auto h-12 px-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-center gap-2">
                            <span>{{ $landingPage->cta_secondary_text }}</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                        </a>
                    @endif
                </div>
                @endif

                {{-- Micro Trust Tags --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 text-[12px] sm:text-[12.5px] font-medium text-black/55 dark:text-white/55 pt-1">
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check" class="w-4 h-4 text-brand-primary"></i>100% Autentik</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>Kualitas Terjamin</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="smile" class="w-4 h-4 text-[#34C759]"></i>Pelayanan Ramah</span>
                </div>
            </div>

            {{-- Right Column: Media Hardware Squircle Showcase --}}
            @if($landingPage->hero_image_url)
            <div class="lg:col-span-5 pt-6 lg:pt-0">
                <div class="rounded-[28px] overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.12)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.45)] border border-black/10 dark:border-white/10 aspect-[4/3] bg-black/[0.03] dark:bg-white/[0.05] relative group">
                    <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" fetchpriority="high" onerror="this.style.display='none';">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent pointer-events-none"></div>
                    <div class="absolute bottom-4 inset-x-4 flex items-center justify-between text-white pointer-events-none">
                        <div class="text-xs font-semibold drop-shadow-md flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4 text-brand-primary"></i>
                            <span>{{ $business->name }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/20 backdrop-blur-md text-white border border-white/30 shadow-sm">
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
    @if($sectionVisibility['about'] && !empty($landingPage->values))
        <section class="py-4 sm:py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($landingPage->values as $val)
                        <div class="bento-card bento-card-interactive p-6 flex flex-col justify-between space-y-4">
                            <div class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $val['icon'] ?? 'check' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[15.5px] sm:text-[16px] text-black dark:text-white tracking-[-0.015em] leading-snug">{{ $val['title'] ?? '' }}</h3>
                                <p class="text-[13px] sm:text-[13.5px] text-black/60 dark:text-white/60 mt-1.5 leading-relaxed font-normal">{{ $val['desc'] ?? $val['description'] ?? '' }}</p>
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
    @if(($sectionVisibility['services'] || ($sectionVisibility['products'] && $landingPage->show_pos_products)) && $hasServices)
    <section id="layanan" data-section="services" class="py-12 sm:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-12">

            {{-- Section Title --}}
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Menu &amp; Layanan</span>
                @if($landingPage->services_title)
                    <h2 class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">{{ $landingPage->services_title }}</h2>
                @endif
                @if($landingPage->services_subtitle)
                    <p class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 leading-relaxed font-normal tracking-[-0.01em]">{{ $landingPage->services_subtitle }}</p>
                @endif
            </div>

            {{-- Custom Services Slider --}}
            @if($sectionVisibility['services'] && $services->isNotEmpty())
                <div class="space-y-5" x-data="makeSlider({{ $services->count() }}, { base: 1, sm: 2, lg: 3 }, 3600)">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-black/45 dark:text-white/45 block mb-0.5">Pilihan Layanan</span>
                            <h3 class="font-bold text-[18px] sm:text-[20px] text-black dark:text-white tracking-[-0.015em] leading-snug">Layanan Unggulan</h3>
                        </div>
                        @if($services->count() > 1)
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

                    <div class="relative overflow-hidden rounded-[28px]" @mouseenter="stop()" @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                        <div class="flex transition-transform duration-500 ease-out"
                             :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                            @foreach($services as $serviceIndex => $svc)
                                <div class="w-full sm:w-1/2 lg:w-1/3 shrink-0 p-2 sm:p-2.5">
                                    <button type="button" @click="openService({{ Js::from($svc) }})"
                                        class="w-full h-full text-left bento-card bento-card-interactive p-5 flex flex-col justify-between space-y-4 active:scale-[0.98]">
                                        <div>
                                            <div class="relative w-full aspect-[16/10] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden mb-3.5 flex items-center justify-center">
                                                @if(!empty($svc['image_url']))
                                                    <img src="{{ $svc['image_url'] }}" alt="{{ $svc['title'] ?? 'Layanan' }}" loading="lazy" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-brand-primary">
                                                        <i data-lucide="{{ $svc['icon'] ?? 'sparkles' }}" class="w-8 h-8"></i>
                                                    </div>
                                                @endif
                                                @if(!empty($svc['badge']))
                                                    <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold uppercase tracking-wider bg-brand-primary text-white shadow-sm">
                                                        {{ $svc['badge'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h4 class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white line-clamp-1 tracking-tight leading-snug">{{ $svc['title'] ?? '' }}</h4>
                                            <p class="text-[12.5px] sm:text-[13px] text-black/60 dark:text-white/60 mt-1 line-clamp-2 leading-relaxed font-normal">
                                                {{ $svc['description'] ?? '' }}
                                            </p>
                                        </div>

                                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                            <div class="font-bold text-[14px] sm:text-[14.5px] text-brand-primary tabular-nums tracking-tight">
                                                @if(!empty($svc['price'])){{ $svc['price'] }}@else Hubungi kami @endif
                                            </div>
                                            <span class="px-3 py-1 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-semibold text-[11.5px] tracking-tight transition flex items-center gap-1">
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
                                :class="currentIndex === (idx - 1) ? 'w-5 bg-brand-primary' : 'w-1.5 bg-black/20 dark:bg-white/20'"
                                class="h-1.5 rounded-full transition-all duration-300" :aria-label="'Slide ' + idx"></button>
                        </template>
                    </div>

                    @if($services->count() > 3)
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
            @if($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty())
                <div class="pt-6 space-y-5" x-data="makeSlider({{ $posProducts->count() }}, { base: 1, sm: 2, lg: 4 }, 3200)">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-[0.05em] text-black/45 dark:text-white/45 block mb-0.5">Menu &amp; Produk</span>
                            <h3 class="font-bold text-[18px] sm:text-[20px] text-black dark:text-white tracking-[-0.015em] leading-snug">Katalog Produk</h3>
                        </div>
                        @if($posProducts->count() > 1)
                        <div class="flex items-center gap-2">
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

                    <div class="relative overflow-hidden rounded-[28px]" @mouseenter="stop()" @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                        <div class="flex transition-transform duration-500 ease-out"
                             :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                            @foreach($posProducts as $prod)
                                <div class="w-full sm:w-1/2 lg:w-1/4 shrink-0 p-2 sm:p-2.5">
                                    <button type="button" @click="openProduct(products.find(product => product.id === '{{ $prod->id }}'))"
                                        class="w-full h-full text-left bento-card bento-card-interactive p-4 flex flex-col justify-between space-y-3 active:scale-[0.98]">
                                        <div>
                                            <div class="w-full aspect-square rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mb-2.5 overflow-hidden">
                                                @if($prod->image_url)
                                                    <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"><i data-lucide="package" class="hidden w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                @else
                                                    <i data-lucide="package" class="w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                @endif
                                            </div>
                                            <h4 class="font-semibold text-[13.5px] sm:text-[14px] text-black dark:text-white line-clamp-2 tracking-tight leading-snug">{{ $prod->name }}</h4>
                                            @if($prod->category)
                                                <span class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5 font-normal">{{ $prod->category->name }}</span>
                                            @endif
                                        </div>
                                        <div class="pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                            <span class="font-bold text-[14px] text-brand-primary tabular-nums tracking-tight">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                                            <span class="w-7 h-7 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center hover:bg-brand-primary hover:text-white transition">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
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
                                :class="currentIndex === (idx - 1) ? 'w-5 bg-brand-primary' : 'w-1.5 bg-black/20 dark:bg-white/20'"
                                class="h-1.5 rounded-full transition-all duration-300" :aria-label="'Slide ' + idx"></button>
                        </template>
                    </div>

                    @if($posProducts->count() > 4)
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
    @if($sectionVisibility['gallery'] && $allGalleryImages->isNotEmpty())
        <section id="galeri" data-section="gallery" class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Galeri Foto</span>
                    @if($landingPage->gallery_title)<h2 class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">{{ $landingPage->gallery_title }}</h2>@endif
                    @if($landingPage->gallery_subtitle)
                        <p class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 max-w-xl mx-auto leading-relaxed font-normal tracking-[-0.01em]">{{ $landingPage->gallery_subtitle }}</p>
                    @endif
                </div>
                
                {{-- Optimized Apple Bento Photo Grid (Max 5 items) --}}
                @if($totalGalleryCount === 1)
                    <div class="max-w-3xl mx-auto bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer" @click="openGalleryLightbox(0)">
                        <div class="w-full aspect-[16/9] rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                            <img src="{{ $bentoGalleryImages[0]['url'] }}" alt="{{ $bentoGalleryImages[0]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                <p class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                            </div>
                        </div>
                    </div>
                @elseif($totalGalleryCount === 2)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        @foreach($bentoGalleryImages as $idx => $img)
                            <div class="bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-[4/3]" @click="openGalleryLightbox({{ $idx }})">
                                <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                    <img src="{{ $img['url'] }}" alt="{{ $img['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                        <p class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">{{ $img['caption'] ?: $business->name }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($totalGalleryCount === 3)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="sm:col-span-2 bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer aspect-[16/9] min-h-[260px] sm:min-h-[340px]" @click="openGalleryLightbox(0)">
                            <div class="w-full h-full rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[0]['url'] }}" alt="{{ $bentoGalleryImages[0]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <p class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        @foreach($bentoGalleryImages->slice(1) as $sliceIdx => $img)
                            @php $actualIdx = $sliceIdx + 1; @endphp
                            <div class="bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square" @click="openGalleryLightbox({{ $actualIdx }})">
                                <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                    <img src="{{ $img['url'] }}" alt="{{ $img['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                        <p class="text-white text-[12px] font-semibold drop-shadow tracking-tight">{{ $img['caption'] ?: $business->name }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($totalGalleryCount === 4)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 items-stretch">
                        <div class="sm:col-span-2 lg:col-span-2 lg:row-span-2 bento-card bento-card-interactive p-3 group relative overflow-hidden cursor-pointer min-h-[280px] sm:min-h-[360px] lg:min-h-[440px]" @click="openGalleryLightbox(0)">
                            <div class="w-full h-full rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[0]['url'] }}" alt="{{ $bentoGalleryImages[0]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute top-4 left-4 z-10">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-white/85 dark:bg-black/85 backdrop-blur-md text-brand-primary shadow-sm">
                                        Unggulan
                                    </span>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <p class="text-white text-[12.5px] sm:text-[13px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square" @click="openGalleryLightbox(1)">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[1]['url'] }}" alt="{{ $bentoGalleryImages[1]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[1]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square" @click="openGalleryLightbox(2)">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[2]['url'] }}" alt="{{ $bentoGalleryImages[2]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[2]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-2 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer min-h-[160px] aspect-[16/9] sm:aspect-auto" @click="openGalleryLightbox(3)">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[3]['url'] }}" alt="{{ $bentoGalleryImages[3]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                    <p class="text-white text-[12px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[3]['caption'] ?: $business->name }}</p>
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
                            <div class="w-full h-full rounded-[22px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[0]['url'] }}" alt="{{ $bentoGalleryImages[0]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute top-4 left-4 z-10">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-white/85 dark:bg-black/85 backdrop-blur-md text-brand-primary shadow-sm">
                                        Unggulan
                                    </span>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <div class="space-y-1">
                                        <span class="text-white/70 text-[11px] font-semibold uppercase tracking-[0.05em] block">Foto Suasana</span>
                                        <p class="text-white text-[13px] sm:text-[13.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[0]['caption'] ?: $business->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 1: Top Right 1 --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                             @click="openGalleryLightbox(1)">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[1]['url'] }}" alt="{{ $bentoGalleryImages[1]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[1]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 2: Top Right 2 --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                             @click="openGalleryLightbox(2)">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[2]['url'] }}" alt="{{ $bentoGalleryImages[2]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[2]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 3: Bottom Right 1 --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                             @click="openGalleryLightbox(3)">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[3]['url'] }}" alt="{{ $bentoGalleryImages[3]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                    <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[3]['caption'] ?: $business->name }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Tile 4: Bottom Right 2 (5th item, with +X overlay if extra images exist) --}}
                        <div class="col-span-1 bento-card bento-card-interactive p-2.5 group relative overflow-hidden cursor-pointer aspect-square"
                             @click="{{ $extraGalleryCount > 0 ? 'openAllGallery()' : 'openGalleryLightbox(4)' }}">
                            <div class="w-full h-full rounded-[20px] overflow-hidden relative bg-black/[0.02] dark:bg-white/[0.04]">
                                <img src="{{ $bentoGalleryImages[4]['url'] }}" alt="{{ $bentoGalleryImages[4]['caption'] ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                
                                @if($extraGalleryCount > 0)
                                    {{-- Frosted Glass +X Overlay --}}
                                    <div class="absolute inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-[3px] group-hover:backdrop-blur-[1px] transition-all flex flex-col items-center justify-center text-center p-3">
                                        <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white mb-2 group-hover:scale-110 transition-transform">
                                            <i data-lucide="images" class="w-5 h-5"></i>
                                        </div>
                                        <span class="text-white font-extrabold text-lg sm:text-xl tracking-tight leading-tight">+{{ $extraGalleryCount }} Foto</span>
                                        <span class="text-white/80 text-[11px] sm:text-[11.5px] font-medium mt-1 tracking-tight">Lihat Semua</span>
                                    </div>
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5">
                                        <p class="text-white text-[11.5px] font-semibold drop-shadow tracking-tight">{{ $bentoGalleryImages[4]['caption'] ?: $business->name }}</p>
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
    @if($sectionVisibility['about'] && $hasAbout)
    <section id="tentang" data-section="about" class="py-12 sm:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
            <div class="text-center space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Cerita Kami</span>
                <h2 class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">Tentang {{ $business->name }}</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

                {{-- Left Bento Card: Story & Philosophy --}}
                @if($landingPage->about_title || $landingPage->about_story || $landingPage->about_image_url)
                <div class="{{ !empty($landingPage->operational_hours) ? 'lg:col-span-7' : 'lg:col-span-12' }} bento-card p-7 sm:p-9 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 text-brand-primary text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.05em]">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Dedikasi &amp; Cita Rasa</span>
                        </div>
                        @if($landingPage->about_title)
                            <h3 class="text-xl sm:text-[23px] font-bold tracking-tight lg:tracking-[-0.02em] text-black dark:text-white leading-snug">
                                {{ $landingPage->about_title }}
                            </h3>
                        @endif
                        <div class="text-[14px] sm:text-[15px] text-black/70 dark:text-white/70 leading-[1.7] space-y-3 whitespace-pre-wrap font-normal tracking-[-0.01em]">
                            @if($landingPage->about_story){{ $landingPage->about_story }}@endif
                        </div>
                    </div>

                    @if($landingPage->about_image_url)
                    <div class="rounded-[20px] overflow-hidden aspect-[16/9] border border-black/5 dark:border-white/10 mt-3">
                        <img src="{{ $landingPage->about_image_url }}" alt="{{ $business->name }}" loading="lazy" class="w-full h-full object-cover">
                    </div>
                    @endif
                </div>
                @endif

                {{-- Right Bento Card: Jam Operasional (Apple Inset Group Table) --}}
                @if(!empty($landingPage->operational_hours))
                <div class="lg:col-span-5 bento-card p-7 flex flex-col justify-between space-y-6">
                    <div class="space-y-5">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-[16px] sm:text-[17px] text-black dark:text-white tracking-tight leading-snug">Jadwal Operasional</h3>
                                <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 leading-normal">Waktu pelayanan resmi outlet</p>
                            </div>
                        </div>

                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] p-3 divide-y divide-black/5 dark:divide-white/5 text-[13px]">
                            @foreach($landingPage->operational_hours as $h)
                                <div class="py-2.5 px-2 flex justify-between items-center">
                                    <span class="font-medium text-[13px] sm:text-[13.5px] text-black/75 dark:text-white/75">{{ $h['day'] }}</span>
                                    @if(!empty($h['is_open']))
                                        @if(!empty($h['hours']))
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-[13px] sm:text-[13.5px] text-[#34C759] tabular-nums tracking-tight">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>{{ $h['hours'] }}</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1.5 font-medium text-[12px] sm:text-[12.5px] text-[#FF3B30]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                            <span>Tutup</span>
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
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
    @if($sectionVisibility['testimonials'] && $testimonials->isNotEmpty())
        <section class="py-12 sm:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Ulasan Pelanggan</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">Apa Kata Mereka</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($testimonials as $testi)
                        <div class="bento-card bento-card-interactive p-6 flex flex-col justify-between space-y-5">
                            {{-- Star Rating in Apple Gold --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 text-[#FFCC00]">
                                    @for($i = 0; $i < ($testi['rating'] ?? 5); $i++)
                                        <i data-lucide="star" class="w-4 h-4 fill-[#FFCC00]"></i>
                                    @endfor
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] sm:text-[10.5px] font-semibold uppercase tracking-wider bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                    Verified
                                </span>
                            </div>

                            <p class="text-[13.5px] sm:text-[14px] text-black/75 dark:text-white/75 italic leading-relaxed font-normal tracking-[-0.01em]">
                                "{{ $testi['quote'] ?? '' }}"
                            </p>

                            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-brand-primary/10 text-brand-primary font-bold text-xs flex items-center justify-center">
                                    {{ strtoupper(substr($testi['name'] ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    @if(!empty($testi['name']))<div class="font-semibold text-[13px] sm:text-[13.5px] text-black dark:text-white tracking-tight">{{ $testi['name'] }}</div>@endif
                                    @if(!empty($testi['role']))<div class="text-[11px] sm:text-[11.5px] text-black/45 dark:text-white/45 font-normal">{{ $testi['role'] }}</div>@endif
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
    @if($sectionVisibility['faq'] && $faqs->isNotEmpty())
        <section id="faq" class="py-12 sm:py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-7" x-data="{ openFaq: null }">
                <div class="text-center space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Bantuan &amp; Info</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">Pertanyaan Umum (FAQ)</h2>
                </div>

                <div class="bento-card overflow-hidden divide-y divide-black/5 dark:divide-white/5 p-2 sm:p-3">
                    @foreach($faqs as $index => $faq)
                        <div class="transition-colors">
                            <button @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 font-semibold text-[14.5px] sm:text-[15.5px] text-black dark:text-white hover:text-brand-primary tracking-tight leading-snug transition">
                                <span>{{ $faq['question'] ?? '' }}</span>
                                <div class="w-7 h-7 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center shrink-0">
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-black/50 dark:text-white/50 transition-transform duration-300"
                                        :class="openFaq === {{ $index }} ? 'rotate-180 text-brand-primary' : ''"></i>
                                </div>
                            </button>
                            <div x-show="openFaq === {{ $index }}" x-transition.opacity class="px-5 pb-5 text-[13.5px] sm:text-[14px] text-black/65 dark:text-white/65 leading-relaxed font-normal" style="display: none;">
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
    @if($sectionVisibility['contact'] && $hasContact)
    <section id="lokasi" data-section="contact" class="py-12 sm:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] sm:text-[11.5px] font-semibold uppercase tracking-[0.06em] text-brand-primary bg-brand-primary/10">Kunjungi Outlet</span>
                <h2 class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">Lokasi &amp; Kontak</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                {{-- Address & Details Bento Card --}}
                <div class="lg:col-span-5 bento-card p-6 sm:p-8 flex flex-col justify-between space-y-6">
                    <div class="space-y-5">
                        @if($landingPage->custom_address || $business->address)
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white tracking-tight leading-snug">Alamat Resmi</h3>
                                <p class="text-[13px] sm:text-[13.5px] text-black/65 dark:text-white/65 mt-1 leading-relaxed font-normal">
                                    {{ $landingPage->custom_address ?: $business->address }}
                                </p>
                            </div>
                        </div>
                        @endif

                        @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="phone" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white tracking-tight leading-snug">Telepon / WhatsApp</h3>
                                <p class="text-[13.5px] sm:text-[14px] font-medium text-black/80 dark:text-white/80 mt-1 tabular-nums tracking-tight">
                                    {{ $landingPage->whatsapp_number ?: ($business->phone ?: '-') }}
                                </p>
                            </div>
                        </div>
                        @endif

                        @if($landingPage->custom_email ?: $business->email)
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white tracking-tight leading-snug">Email Bisnis</h3>
                                <a href="mailto:{{ $landingPage->custom_email ?: $business->email }}" class="text-[13px] sm:text-[13.5px] text-brand-primary mt-1 block hover:underline tracking-tight font-medium">
                                    {{ $landingPage->custom_email ?: $business->email }}
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Social Media & Marketplace Channels --}}
                        @if($activeChannels->isNotEmpty() || $hasWhatsapp)
                            <div class="pt-4 border-t border-black/5 dark:border-white/5 space-y-2.5">
                                <span class="text-[11px] sm:text-[11.5px] text-black/45 dark:text-white/45 font-semibold uppercase tracking-[0.05em] block">Kanal Resmi &amp; Medsos:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($activeChannels as $channel)
                                        @if(filled($channel['url']))
                                            <a href="{{ $channel['url'] }}" target="_blank" rel="noopener" aria-label="{{ $channel['label'] }}" title="{{ $channel['label'] }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-95 transition">
                                                <i data-lucide="{{ $channel['icon'] }}" class="w-3.5 h-3.5 shrink-0"></i>
                                                <span>{{ $channel['label'] }}</span>
                                            </a>
                                        @endif
                                    @endforeach

                                    @if($hasWhatsapp)
                                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener" aria-label="WhatsApp" title="WhatsApp"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 active:scale-95 transition">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>WhatsApp</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                        class="w-full h-12 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-tight flex items-center justify-center gap-2 hover:opacity-95 active:scale-[0.98] transition shadow-[0_2px_8px_rgba(0,0,0,0.12)]">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                        <span>Mulai Chat via WhatsApp</span>
                    </a>
                    @endif
                </div>

                {{-- Map Embed (Bento Hardware Squircle Bezel) --}}
                @if($landingPage->google_maps_embed_url)
                    <div class="lg:col-span-7 bento-card p-3 overflow-hidden min-h-[350px] sm:min-h-[420px] flex flex-col">
                        <div class="w-full h-full rounded-[20px] overflow-hidden border border-black/5 dark:border-white/5">
                            <iframe src="{{ $landingPage->google_maps_embed_url }}" width="100%" height="100%" style="border:0; min-height: 340px;" allowfullscreen="" loading="lazy"></iframe>
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
        @keydown.escape.window="activeModal = null"
        @click.self="activeModal = null"
        class="fixed inset-0 z-[70] bg-black/35 backdrop-blur-[3px] p-4 flex items-center justify-center"
        role="dialog" aria-modal="true">
        <div class="w-full max-h-[90vh] overflow-y-auto rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] p-5 sm:p-7 transition-all duration-300"
             :class="activeModal === 'all-gallery' ? 'max-w-4xl' : 'max-w-3xl'">
            
            <div class="flex items-center justify-between gap-3 mb-5 border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white tracking-tight"
                    x-text="activeModal === 'service' ? 'Detail Layanan' : activeModal === 'product' ? 'Detail Produk' : activeModal === 'all-services' ? 'Semua Layanan' : activeModal === 'all-products' ? 'Semua Produk' : 'Koleksi Galeri Foto'"></h2>
                <button type="button" @click="activeModal = null"
                    class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 flex items-center justify-center hover:bg-black/10 active:scale-95 transition"
                    aria-label="Tutup dialog">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Single Product / Service Detail View --}}
            <div x-show="activeModal === 'service' || activeModal === 'product'" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="relative aspect-[4/3] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden flex items-center justify-center border border-black/5 dark:border-white/10">
                    <template x-if="activeItem && activeItem.image_url">
                        <img :src="activeItem.image_url" :alt="activeItem.name || activeItem.title" class="w-full h-full object-cover" x-on:error="activeItem.image_url = null">
                    </template>
                    <i x-show="!activeItem || !activeItem.image_url" data-lucide="package" class="w-12 h-12 text-black/25 dark:text-white/25"></i>
                    <span x-show="activeItem && (activeItem.category || activeModal === 'service')"
                        class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full bg-white/90 dark:bg-black/80 backdrop-blur text-[10.5px] font-semibold uppercase tracking-wider text-brand-primary shadow-sm"
                        x-text="activeItem?.category || 'Layanan'"></span>
                </div>
                <div class="space-y-4 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] text-brand-primary font-semibold uppercase tracking-[0.05em]" x-text="activeModal === 'service' ? 'Layanan Kami' : 'Produk Kami'"></span>
                        <h3 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight leading-snug mt-0.5" x-text="activeItem?.title || activeItem?.name"></h3>
                        <p class="text-[13px] sm:text-[13.5px] text-black/65 dark:text-white/65 whitespace-pre-wrap leading-relaxed mt-2 font-normal" x-text="activeItem?.description || 'Informasi detail belum tersedia.'"></p>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-black/5 dark:border-white/5">
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="text-[20px] font-bold text-brand-primary tabular-nums tracking-tight"
                                x-text="activeItem?.price ? ((typeof activeItem.price === 'number') ? formatPrice(activeItem.price) : activeItem.price) : 'Hubungi kami'"></span>
                            <span class="text-[11px] text-black/45 dark:text-white/45 font-normal" x-text="activeModal === 'service' ? 'Estimasi biaya' : 'Harga resmi'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a :href="activeItem ? waLink(activeItem.title || activeItem.name) : '#'" target="_blank" rel="noopener"
                                class="flex-1 h-10 rounded-full bg-brand-primary text-white text-[13px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                                <span>Pesan via WhatsApp</span>
                            </a>
                            <button type="button" @click="activeModal = null"
                                class="h-10 px-5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 text-[13px] font-medium tracking-tight hover:bg-black/[0.08] transition">
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
                    <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/35 dark:text-white/35"></i>
                    <input type="search" x-model="serviceSearch" @input="filterServices()"
                        placeholder="Cari nama atau deskripsi layanan..."
                        class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                    <template x-for="svc in filteredServices" :key="svc.title">
                        <button type="button" @click="openService(svc)"
                            class="text-left p-3.5 rounded-[16px] border border-black/5 dark:border-white/5 hover:border-black/15 dark:hover:border-white/15 transition flex flex-col justify-between group bg-black/[0.02] dark:bg-white/[0.03]">
                            <div>
                                <div class="relative aspect-[16/10] rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex items-center justify-center mb-2.5">
                                    <template x-if="svc.image_url">
                                        <img :src="svc.image_url" :alt="svc.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                    </template>
                                    <i data-lucide="sparkles" class="w-7 h-7 text-black/30 dark:text-white/30" :class="svc.image_url ? 'hidden' : ''"></i>
                                    <template x-if="svc.badge">
                                        <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-brand-primary text-white text-[9.5px] font-semibold uppercase tracking-wider shadow-sm" x-text="svc.badge"></span>
                                    </template>
                                </div>
                                <h3 class="font-semibold text-[13.5px] text-black dark:text-white line-clamp-1 tracking-tight leading-snug group-hover:text-brand-primary transition" x-text="svc.title"></h3>
                                <p class="text-[11.5px] text-black/55 dark:text-white/55 line-clamp-2 mt-0.5 leading-relaxed font-normal" x-text="svc.description || 'Layanan unggulan berkualitas siap memenuhi kebutuhan Anda.'"></p>
                            </div>
                            <div class="pt-2.5 mt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                <span class="text-[13px] font-bold text-brand-primary tabular-nums tracking-tight" x-text="svc.price || 'Hubungi kami'"></span>
                                <span class="px-2 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-[11px] font-semibold text-black/70 dark:text-white/70 group-hover:bg-brand-primary group-hover:text-white transition flex items-center gap-0.5 tracking-tight">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="filteredServices.length === 0" class="py-10 text-center text-black/40 dark:text-white/40">
                    <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-1 opacity-50"></i>
                    <p class="text-[13px] font-medium">Tidak ada layanan yang cocok dengan pencarian ini.</p>
                </div>
            </div>

            {{-- All Products Grid View --}}
            <div x-show="activeModal === 'all-products'" class="space-y-4">
                <!-- Capsule Search -->
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/35 dark:text-white/35"></i>
                    <input type="search" x-model="productSearch" @input="filterProducts()"
                        placeholder="Cari nama atau deskripsi produk..."
                        class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border-none pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition">
                </div>

                {{-- Category Pill Tabs (Apple Segmented Scroll) --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    <button type="button" @click="productCategory = 'all'; filterProducts()"
                            class="px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95"
                            :class="productCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08]'">
                        Semua Kategori
                    </button>
                    @foreach($productCategories as $category)
                        <button type="button" @click="productCategory = '{{ $category->id }}'; filterProducts()"
                                class="px-3 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95"
                                :class="productCategory === '{{ $category->id }}' ? 'bg-brand-primary text-white shadow-sm' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08]'">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 pt-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="openProduct(product)"
                            class="text-left p-3 rounded-[16px] border border-black/5 dark:border-white/5 hover:border-black/15 dark:hover:border-white/15 transition flex flex-col justify-between group bg-black/[0.02] dark:bg-white/[0.03]">
                            <div>
                                <div class="aspect-square rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex items-center justify-center mb-2">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                    </template>
                                    <i data-lucide="package" class="w-7 h-7 text-black/30 dark:text-white/30" :class="product.image_url ? 'hidden' : ''"></i>
                                </div>
                                <h3 class="font-semibold text-[13px] text-black dark:text-white line-clamp-2 tracking-tight leading-snug group-hover:text-brand-primary transition" x-text="product.name"></h3>
                                <p class="text-[11px] text-black/45 dark:text-white/45 line-clamp-1 mt-0.5 font-normal" x-text="product.category || ''"></p>
                            </div>
                            <div class="pt-2 mt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                <span class="text-[13px] font-bold text-brand-primary tabular-nums tracking-tight" x-text="'Rp ' + Number(product.price || 0).toLocaleString('id-ID')"></span>
                                <span class="w-6 h-6 rounded-full bg-brand-primary/10 text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition flex items-center justify-center">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="filteredProducts.length === 0" class="py-10 text-center text-black/40 dark:text-white/40">
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
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-brand-primary/10 text-brand-primary border border-brand-primary/20 shrink-0">
                        <span x-text="galleryImages.length"></span> Foto
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 pt-1 max-h-[60vh] overflow-y-auto pr-1">
                    <template x-for="(img, idx) in galleryImages" :key="idx">
                        <button type="button" @click="openGalleryLightbox(idx)"
                            class="group relative aspect-square rounded-[18px] overflow-hidden bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-left transition hover:scale-[1.02] active:scale-[0.98]">
                            <img :src="img.url" :alt="img.caption || 'Foto Galeri'" loading="lazy"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex flex-col justify-end p-2.5">
                                <p x-show="img.caption" x-text="img.caption" class="text-white text-[11.5px] font-medium line-clamp-2 drop-shadow tracking-tight"></p>
                                <span class="inline-flex items-center gap-1 text-[10px] text-white/90 font-semibold tracking-wide mt-1">
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
    <div x-show="activeModal === 'gallery-lightbox'" x-cloak
        @keydown.escape.window="activeModal = null"
        @keydown.right.window="activeModal === 'gallery-lightbox' && nextGalleryImage()"
        @keydown.left.window="activeModal === 'gallery-lightbox' && prevGalleryImage()"
        class="fixed inset-0 z-[80] bg-black/95 backdrop-blur-2xl p-4 sm:p-6 flex flex-col justify-between"
        role="dialog" aria-modal="true">

        {{-- Top Bar: Status, Action Pills, Close --}}
        <div class="flex items-center justify-between text-white z-10">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-white/10 text-xs font-semibold backdrop-blur-md tabular-nums tracking-tight">
                    <span x-text="activeGalleryIndex + 1"></span> / <span x-text="galleryImages.length"></span>
                </span>
                <span class="text-xs font-medium text-white/60 tracking-tight hidden sm:inline">{{ $business->name }}</span>
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
                <img :src="galleryImages[activeGalleryIndex]?.url" :alt="galleryImages[activeGalleryIndex]?.caption || 'Foto Galeri'"
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
            <div x-show="galleryImages.length > 1" class="flex items-center justify-center gap-2 overflow-x-auto py-1 px-4 scrollbar-none">
                <template x-for="(thumb, idx) in galleryImages" :key="idx">
                    <button type="button" @click="activeGalleryIndex = idx"
                        class="w-11 h-11 sm:w-12 sm:h-12 rounded-[10px] overflow-hidden border-2 transition shrink-0"
                        :class="activeGalleryIndex === idx ? 'border-brand-primary scale-105 shadow-md' : 'border-transparent opacity-50 hover:opacity-100'">
                        <img :src="thumb.url" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MOBILE BOTTOM BAR (iOS 18 Tab Bar Style)                                  --}}
    {{-- ========================================================================= --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/85 dark:bg-[#161617]/85 backdrop-blur-xl border-t border-black/5 dark:border-white/10 px-2 pt-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))]" aria-label="Navigasi halaman">
        <div class="grid grid-cols-5 gap-1">
            @if($sectionVisibility['hero'] && $hasHero)<a href="#hero" class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1" :class="activeSection === 'hero' ? 'text-brand-primary font-semibold' : ''"><i data-lucide="home" class="w-4 h-4 mx-auto mb-0.5"></i>Home</a>@endif
            @if($sectionVisibility['about'] && $hasAbout)<a href="#tentang" class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1" :class="activeSection === 'about' ? 'text-brand-primary font-semibold' : ''"><i data-lucide="book-open" class="w-4 h-4 mx-auto mb-0.5"></i>About</a>@endif
            @if(($sectionVisibility['services'] && $services->isNotEmpty()) || ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty()))<a href="#layanan" class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1" :class="activeSection === 'services' ? 'text-brand-primary font-semibold' : ''"><i data-lucide="package" class="w-4 h-4 mx-auto mb-0.5"></i>Katalog</a>@endif
            @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))<a href="#galeri" class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1" :class="activeSection === 'gallery' ? 'text-brand-primary font-semibold' : ''"><i data-lucide="images" class="w-4 h-4 mx-auto mb-0.5"></i>Galeri</a>@endif
            @if($sectionVisibility['contact'] && $hasContact)<a href="#lokasi" class="text-center text-[10.5px] font-medium tracking-tight text-black/55 dark:text-white/55 py-1" :class="activeSection === 'contact' ? 'text-brand-primary font-semibold' : ''"><i data-lucide="map-pin" class="w-4 h-4 mx-auto mb-0.5"></i>Kontak</a>@endif
        </div>
    </nav>

    {{-- ========================================================================= --}}
    {{-- FOOTER (Apple Clean Dark/Light Grounding)                                 --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['footer'])
    <footer class="border-t {{ $landingPage->dark_mode ? 'bg-[#161617] border-white/10 text-white/60' : 'bg-[#F5F5F7] border-black/5 text-black/60' }}">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <div class="grid grid-cols-1 sm:{{ $footerGridClass }} gap-10 lg:gap-12">
                {{-- Brand and social --}}
                @if($sectionVisibility['footer_brand'])
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        @if($landingPage->logo_url ?: $business->logo_url)
                            <img src="{{ $landingPage->logo_url ?: $business->logo_url }}" alt="{{ $business->name }}" class="w-10 h-10 rounded-[10px] object-contain bg-white dark:bg-black/20 p-1 border border-black/5 dark:border-white/10" onerror="this.style.display='none';">
                        @endif
                        <div>
                            <div class="font-bold text-[16px] text-black dark:text-white leading-tight tracking-tight">{{ $business->name }}</div>
                            @if($landingPage->industry_preset)
                                <div class="text-[10px] sm:text-[10.5px] uppercase tracking-[0.06em] text-brand-primary font-semibold">{{ $landingPage->industry_preset }}</div>
                            @endif
                        </div>
                    </div>
                    @if($landingPage->footer_description ?: ($business->description ?: $landingPage->subheadline))
                        <p class="text-[13px] leading-relaxed max-w-sm text-black/60 dark:text-white/60 font-normal">{{ $landingPage->footer_description ?: ($business->description ?: $landingPage->subheadline) }}</p>
                    @endif
                    @if($activeChannels->isNotEmpty() || $hasWhatsapp)
                        <div class="space-y-2 pt-1">
                            <span class="text-[11px] sm:text-[11.5px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-[0.05em] block">Media Sosial &amp; Marketplace</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($activeChannels as $channel)
                                    @if(filled($channel['url']))
                                        <a href="{{ $channel['url'] }}" target="_blank" rel="noopener" aria-label="{{ $channel['label'] }}" title="{{ $channel['label'] }}"
                                            class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-white flex items-center justify-center transition shadow-sm {{ $channel['color'] }}">
                                            <i data-lucide="{{ $channel['icon'] }}" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                @endforeach
                                @if($hasWhatsapp)
                                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener" aria-label="WhatsApp" title="WhatsApp"
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
                @if($sectionVisibility['footer_navigation'] && (($sectionVisibility['hero'] && $hasHero) || ($sectionVisibility['about'] && $hasAbout) || $hasServices || ($sectionVisibility['gallery'] && !empty($landingPage->gallery_images)) || ($sectionVisibility['contact'] && $hasContact)))
                    <div>
                        <h3 class="text-[12px] font-semibold uppercase tracking-[0.06em] text-black dark:text-white mb-3.5">{{ $landingPage->footer_navigation_title ?: 'Navigasi' }}</h3>
                        <nav class="space-y-2.5 text-[13px] font-normal tracking-tight">
                            @if($sectionVisibility['hero'] && $hasHero)<a href="#hero" class="block hover:text-brand-primary transition">Beranda</a>@endif
                            @if($sectionVisibility['about'] && $hasAbout)<a href="#tentang" class="block hover:text-brand-primary transition">Tentang Kami</a>@endif
                            @if(($sectionVisibility['services'] && $services->isNotEmpty()) || ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty()))<a href="#layanan" class="block hover:text-brand-primary transition">Menu &amp; Layanan</a>@endif
                            @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))<a href="#galeri" class="block hover:text-brand-primary transition">Galeri Foto</a>@endif
                            @if($sectionVisibility['contact'] && $hasContact)<a href="#lokasi" class="block hover:text-brand-primary transition">Kontak &amp; Lokasi</a>@endif
                        </nav>
                    </div>
                @endif

                {{-- Services --}}
                @if($sectionVisibility['footer_services'] && $sectionVisibility['services'] && $services->isNotEmpty())
                    <div>
                        <h3 class="text-[12px] font-semibold uppercase tracking-[0.06em] text-black dark:text-white mb-3.5">{{ $landingPage->footer_services_title ?: 'Layanan' }}</h3>
                        <nav class="space-y-2.5 text-[13px] font-normal tracking-tight">
                            @foreach($services->take(6) as $service)
                                @if(!empty($service['title']))<a href="#layanan" class="block hover:text-brand-primary transition">{{ $service['title'] }}</a>@endif
                            @endforeach
                        </nav>
                    </div>
                @endif

                {{-- Contact --}}
                @if($sectionVisibility['footer_contact'] && $hasContact)
                    <div>
                        <h3 class="text-[12px] font-semibold uppercase tracking-[0.06em] text-black dark:text-white mb-3.5">{{ $landingPage->footer_contact_title ?: 'Kontak' }}</h3>
                        <div class="space-y-2.5 text-[13px] font-normal tracking-tight">
                            @if($landingPage->custom_address ?: $business->address)<div class="flex items-start gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-brand-primary shrink-0 mt-0.5"></i><span>{{ $landingPage->custom_address ?: $business->address }}</span></div>@endif
                            @if($landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone))<div class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-brand-primary shrink-0"></i><span class="tabular-nums">{{ $landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone) }}</span></div>@endif
                            @if($landingPage->custom_email ?: $business->email)<div class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-brand-primary shrink-0"></i><span>{{ $landingPage->custom_email ?: $business->email }}</span></div>@endif
                        </div>
                        @if(($landingPage->footer_cta_text ?: $landingPage->cta_primary_text) && ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone))
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
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px] text-black/50 dark:text-white/50 font-normal">
                <p>{{ $landingPage->footer_copyright ?: '© ' . date('Y') . ' ' . $business->name . '. Hak cipta dilindungi undang-undang.' }}</p>
                <p>Didukung oleh <a href="https://cooca.id" target="_blank" rel="noopener" class="font-semibold hover:underline text-brand-primary">COOCA UMKM</a></p>
            </div>
        </div>
    </footer>
    @endif

    {{-- ========================================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (Apple Frosted Glass Widget)                     --}}
    {{-- ========================================================================= --}}
    <div class="fixed bottom-24 right-4 md:bottom-6 md:right-6 z-40 flex flex-col items-end select-none">

        {{-- Floating Greeting Bubble --}}
        <div x-show="waChatOpen" x-transition.opacity
            class="mb-3 p-4 rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl shadow-xl border border-black/10 dark:border-white/10 max-w-xs text-[12px] space-y-2.5"
            style="display: none;">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-[13px] text-black dark:text-white tracking-tight">{{ $business->name }}</span>
                <button @click="waChatOpen = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
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
            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
            <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-[#FF3B30] border-2 border-white dark:border-black animate-pulse"></span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
