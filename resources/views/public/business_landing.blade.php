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

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <?php
        $themeColor = $landingPage->theme_color ?: '#10B981';
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
            $r = 16; $g = 185; $b = 129;
        }
        $themeRgb = "{$r}, {$g}, {$b}";
    ?>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '{{ $themeColor }}',
                            50: '{{ $themeColor }}',
                            100: '{{ $themeColor }}',
                            500: '{{ $themeColor }}',
                            600: '{{ $themeColor }}',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js & Lucide --}}
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
    </script>

    <style>
        [x-cloak] { display: none !important; }
        :root {
            --primary: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
            --primary-rgb: {{ $themeRgb }};
            --secondary-color: {{ $landingPage->dark_mode ? '#1e293b' : '#f1f5f9' }};
            --accent-color: {{ $themeColor }};
            --text-color: {{ $landingPage->dark_mode ? '#f8fafc' : '#0f172a' }};
            --background-color: {{ $landingPage->dark_mode ? '#020617' : '#f8fafc' }};
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-color);
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
        .glow-brand {
            box-shadow: 0 10px 35px -5px rgba(var(--primary-rgb), 0.35);
        }
            .catalog-slider { scrollbar-width: none; }
            .catalog-slider::-webkit-scrollbar { display: none; }
            /* Announcement marquee */
            .marquee-track { display: inline-flex; white-space: nowrap; will-change: transform; animation: marquee 28s linear infinite; }
            .marquee-track:hover { animation-play-state: paused; }
            @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; }
            }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>

    {{-- JSON-LD LocalBusiness Schema for Google Search --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "{{ $business->name }}",
        "description": "{{ $landingPage->subheadline }}",
        "url": "{{ url()->current() }}",
        "image": "{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $business->logo_url) }}",
        "telephone": "{{ $landingPage->whatsapp_number ?: $business->phone }}",
        "address": {
            "@type": "PostalAddress",
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

<?php
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
        ...$faq,
        'question' => $faq['question'] ?? $faq['q'] ?? '',
        'answer' => $faq['answer'] ?? $faq['a'] ?? '',
    ]);
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
        'gallery' => !empty($landingPage->gallery_images),
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
        ['key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'video', 'fallback' => $landingPage->tiktok_handle, 'color' => 'hover:bg-slate-900 hover:text-white'],
        ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'facebook', 'fallback' => $landingPage->facebook_url, 'color' => 'hover:bg-blue-600 hover:text-white'],
        ['key' => 'youtube', 'label' => 'YouTube', 'icon' => 'youtube', 'fallback' => null, 'color' => 'hover:bg-red-600 hover:text-white'],
        ['key' => 'twitter', 'label' => 'X (Twitter)', 'icon' => 'twitter', 'fallback' => null, 'color' => 'hover:bg-slate-900 hover:text-white'],
        ['key' => 'linkedin', 'label' => 'LinkedIn', 'icon' => 'linkedin', 'fallback' => null, 'color' => 'hover:bg-sky-700 hover:text-white'],
        ['key' => 'shopee', 'label' => 'Shopee', 'icon' => 'shopping-bag', 'fallback' => null, 'color' => 'hover:bg-orange-500 hover:text-white'],
        ['key' => 'tokopedia', 'label' => 'Tokopedia', 'icon' => 'store', 'fallback' => null, 'color' => 'hover:bg-emerald-600 hover:text-white'],
        ['key' => 'gofood', 'label' => 'GoFood', 'icon' => 'utensils', 'fallback' => null, 'color' => 'hover:bg-red-500 hover:text-white'],
        ['key' => 'grabfood', 'label' => 'GrabFood', 'icon' => 'bike', 'fallback' => null, 'color' => 'hover:bg-emerald-500 hover:text-white'],
        ['key' => 'lazada', 'label' => 'Lazada', 'icon' => 'shopping-cart', 'fallback' => null, 'color' => 'hover:bg-indigo-600 hover:text-white'],
        ['key' => 'blibli', 'label' => 'Blibli', 'icon' => 'package', 'fallback' => null, 'color' => 'hover:bg-blue-500 hover:text-white'],
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

<body class="{{ $landingPage->dark_mode ? 'dark bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-900' }} antialiased selection:bg-brand-primary selection:text-white pb-20 md:pb-0"
    x-data="{
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
            if (this.productCategory !== 'all') products = products.filter(product => String(product.category_id) === this.productCategory);
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
        waLink(label) { return '{{ $landingPage->getWhatsAppUrl() }}' + '&text=' + encodeURIComponent('Saya tertarik dengan ' + (label || 'Pesan/Layanan')); },
        openProduct(product) { this.activeItem = product; this.activeModal = 'product'; this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); },
        openService(service) { this.activeItem = service; this.activeModal = 'service'; this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); }
    }">

    {{-- ========================================================================= --}}
    {{-- ANNOUNCEMENT MARQUEE (CMS: announcement_badge) --}}
    {{-- ========================================================================= --}}
    @if($landingPage->announcement_badge)
        <div class="relative overflow-hidden bg-brand-primary text-white text-[11px] sm:text-xs font-bold leading-5 shadow-lg shadow-brand-primary/10">
            <div class="marquee-track py-2.5">
                <span class="px-8 shrink-0">{{ $landingPage->announcement_badge }} &nbsp;✦&nbsp; {{ $landingPage->announcement_badge }} &nbsp;✦&nbsp; {{ $landingPage->announcement_badge }} &nbsp;✦&nbsp; {{ $landingPage->announcement_badge }}</span>
                <span class="px-8 shrink-0" aria-hidden="true">{{ $landingPage->announcement_badge }} &nbsp;✦&nbsp; {{ $landingPage->announcement_badge }} &nbsp;✦&nbsp; {{ $landingPage->announcement_badge }} &nbsp;✦&nbsp; {{ $landingPage->announcement_badge }}</span>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TOPBAR / NAVBAR --}}
    {{-- ========================================================================= --}}
    <header class="sticky top-0 z-40 w-full backdrop-blur-md {{ $landingPage->dark_mode ? 'bg-slate-950/80 border-slate-800' : 'bg-white/80 border-slate-200' }} border-b transition-colors">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">

            {{-- Brand / Logo --}}
            <a href="#hero" class="flex items-center gap-3 group">
                @if($landingPage->logo_url ?: $business->logo_url)
                    <img src="{{ $landingPage->logo_url ?: $business->logo_url }}" alt="{{ $business->name }}" class="w-9 h-9 rounded-xl object-contain shadow-sm group-hover:scale-105 transition-transform" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="hidden w-9 h-9 rounded-xl bg-brand-primary text-white items-center justify-center font-black text-sm shadow-md">{{ strtoupper(substr($business->name, 0, 2)) }}</div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-brand-primary text-white flex items-center justify-center font-black text-sm shadow-md">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <span class="font-extrabold text-sm sm:text-base tracking-tight block leading-tight group-hover:opacity-80 transition">{{ $business->name }}</span>
                                @if(collect($landingPage->operational_hours ?? [])->contains(fn ($hours) => !empty($hours['is_open'])))
                                    <span class="text-[10px] font-bold text-emerald-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>{{ collect($landingPage->operational_hours)->first(fn ($hours) => !empty($hours['is_open']))['day'] ?? '' }}</span>
                                @endif
                </div>
            </a>

            {{-- Navigation Links (Desktop) --}}
            <nav class="hidden md:flex items-center gap-6 text-xs font-semibold {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }}">
                @if($sectionVisibility['services'] && $hasServices)
                    <a href="#layanan" class="hover:text-brand-primary transition">Layanan &amp; Menu</a>
                @endif
                @if($sectionVisibility['about'] && $hasAbout)
                    <a href="#tentang" class="hover:text-brand-primary transition">Tentang Kami</a>
                @endif
                @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
                    <a href="#galeri" class="hover:text-brand-primary transition">Galeri</a>
                @endif
                @if($sectionVisibility['faq'] && $faqs->isNotEmpty())
                    <a href="#faq" class="hover:text-brand-primary transition">Tanya Jawab</a>
                @endif
                @if($sectionVisibility['contact'] && $hasContact)
                    <a href="#lokasi" class="hover:text-brand-primary transition">Lokasi &amp; Kontak</a>
                @endif
            </nav>

            {{-- CTA WhatsApp Navbar Button --}}
            <div class="flex items-center gap-2">
                @if($landingPage->cta_primary_text && ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone))
                <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-primary text-white text-xs font-bold shadow-md hover:opacity-90 transition transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    <span>{{ $landingPage->cta_primary_text }}</span>
                </a>
                @endif

                {{-- Mobile Hamburger --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl border border-slate-300 dark:border-slate-800 text-slate-600 dark:text-slate-300">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Dropdown --}}
        <div x-show="mobileMenuOpen" x-transition.opacity class="md:hidden border-t {{ $landingPage->dark_mode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200' }} px-4 py-4 space-y-2 text-xs font-bold" style="display: none;">
            @if($sectionVisibility['services'] && $hasServices)<a href="#layanan" @click="mobileMenuOpen = false" class="block py-2">Layanan &amp; Produk</a>@endif
            @if($sectionVisibility['about'] && $hasAbout)<a href="#tentang" @click="mobileMenuOpen = false" class="block py-2">Tentang Kami</a>@endif
            @if($sectionVisibility['contact'] && $hasContact)<a href="#lokasi" @click="mobileMenuOpen = false" class="block py-2">Lokasi &amp; Kontak</a>@endif
                @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" class="w-full mt-2 py-2.5 rounded-xl bg-brand-primary text-white text-center font-bold block">{{ $landingPage->cta_primary_text }}</a>
                @endif
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['hero'] && $hasHero)
    <section id="hero" data-section="hero" class="relative overflow-hidden pt-12 pb-16 sm:pt-20 sm:pb-24">
        {{-- Background Glow --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-72 bg-brand-primary opacity-15 blur-[100px] pointer-events-none rounded-full"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

            <div class="text-center lg:text-left space-y-6">
            {{-- Announcement Badge --}}
            @if($landingPage->announcement_badge)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-primary/10 border border-brand-primary/30 text-brand-primary shadow-sm animate-pulse">
                    <span>{{ $landingPage->announcement_badge }}</span>
                </div>
            @endif

            {{-- Headline --}}
            @if($landingPage->headline)
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-3xl lg:max-w-none">
                    {{ $landingPage->headline }}
                </h1>
            @endif

            {{-- Subheadline --}}
            @if($landingPage->subheadline)
                <p class="text-sm sm:text-lg {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} max-w-2xl leading-relaxed">
                    {{ $landingPage->subheadline }}
                </p>
            @endif

            {{-- Action Buttons --}}
            @if($landingPage->cta_primary_text || $landingPage->cta_secondary_text)
            <div class="flex flex-col sm:flex-row items-center lg:justify-start gap-3 pt-2">
                @if($landingPage->cta_primary_text)
                <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full sm:w-auto px-7 py-3.5 rounded-2xl bg-brand-primary text-white font-extrabold text-sm shadow-xl glow-brand hover:opacity-95 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    <span>{{ $landingPage->cta_primary_text }}</span>
                </a>
                @endif

                @if($landingPage->cta_secondary_text)
                    <a href="{{ $landingPage->cta_secondary_url ?: '#layanan' }}"
                        class="w-full sm:w-auto px-6 py-3.5 rounded-2xl {{ $landingPage->dark_mode ? 'bg-slate-900 border-slate-700 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' }} border font-bold text-sm shadow-sm transition flex items-center justify-center gap-2">
                        <span>{{ $landingPage->cta_secondary_text }}</span>
                        <i data-lucide="arrow-down" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
            @endif
            </div>

            {{-- Hero Media Banner (Optional) --}}
            @if($landingPage->hero_image_url)
            <div class="pt-6 lg:pt-0">
                    <div class="rounded-3xl overflow-hidden shadow-2xl border {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }} aspect-[4/3] {{ $landingPage->dark_mode ? 'bg-slate-800' : 'bg-slate-200' }}">
                        <img src="{{ $landingPage->hero_image_url }}" alt="{{ $business->name }}" class="w-full h-full object-cover" fetchpriority="high" onerror="this.style.display='none';">
                    </div>
            </div>
            @endif

        </div>
    </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- VALUE PROPOSITIONS (4 PILAR KEUNGGULAN) --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['about'] && !empty($landingPage->values))
        <section class="py-12 border-y {{ $landingPage->dark_mode ? 'bg-slate-900/40 border-slate-800/80' : 'bg-white border-slate-200/80' }}">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($landingPage->values as $val)
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-brand-primary/10 border border-brand-primary/20 text-brand-primary flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $val['icon'] ?? 'check' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }}">{{ $val['title'] ?? '' }}</h3>
                                <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-500' }} mt-0.5 leading-relaxed">{{ $val['desc'] ?? $val['description'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- LAYANAN & PRODUK SHOWCASE --}}
    {{-- ========================================================================= --}}
    @if(($sectionVisibility['services'] || ($sectionVisibility['products'] && $landingPage->show_pos_products)) && $hasServices)
    <section id="layanan" data-section="services" class="py-16 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-12">

            {{-- Section Title --}}
            <div class="text-center max-w-2xl mx-auto space-y-2">
                @if($landingPage->services_title)<span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">{{ $landingPage->services_title }}</span>@endif
                @if($landingPage->services_title)
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">{{ $landingPage->services_title }}</h2>
                @endif
                @if($landingPage->services_subtitle)
                    <p class="text-xs sm:text-sm {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-600' }}">{{ $landingPage->services_subtitle }}</p>
                @endif
            </div>

            {{-- Custom Services Slider --}}
            @if($sectionVisibility['services'] && $services->isNotEmpty())
                <div class="space-y-6" x-data="makeSlider({{ $services->count() }}, { base: 1, sm: 2, lg: 3 }, 3600)">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Pilihan Layanan</span>
                            <h3 class="font-black text-lg sm:text-2xl text-slate-900 dark:text-white">Layanan Unggulan</h3>
                        </div>
                        @if($services->count() > 1)
                        <div class="flex items-center gap-2">
                            <button type="button" @click="prev()" aria-label="Layanan Sebelumnya" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-brand-primary hover:text-white transition shadow-sm">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button type="button" @click="next()" aria-label="Layanan Berikutnya" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-brand-primary hover:text-white transition shadow-sm">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="relative overflow-hidden rounded-3xl" @mouseenter="stop()" @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                        <div class="flex transition-transform duration-500 ease-out"
                             :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                            @foreach($services as $serviceIndex => $svc)
                                <div class="w-full sm:w-1/2 lg:w-1/3 shrink-0 p-2 sm:p-3">
                                    <button type="button" @click="openService({{ Js::from($svc) }})" class="w-full h-full text-left rounded-3xl glass-card p-5 sm:p-6 flex flex-col justify-between space-y-4 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-brand-primary transition-all duration-300 transform hover:-translate-y-1 border">
                                        <div>
                                            <div class="relative w-full h-44 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden mb-4 flex items-center justify-center">
                                                @if(!empty($svc['image_url']))
                                                    <img src="{{ $svc['image_url'] }}" alt="{{ $svc['title'] ?? 'Layanan' }}" loading="lazy" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-brand-primary">
                                                        <i data-lucide="{{ $svc['icon'] ?? 'sparkles' }}" class="w-10 h-10"></i>
                                                    </div>
                                                @endif
                                                @if(!empty($svc['badge']))
                                                    <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary text-white shadow-md">
                                                        {{ $svc['badge'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="font-extrabold text-base text-slate-900 dark:text-white line-clamp-1">{{ $svc['title'] ?? '' }}</h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-2 leading-relaxed">
                                                {{ $svc['description'] ?? '' }}
                                            </p>
                                        </div>

                                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                            <div class="font-mono font-black text-xs sm:text-sm text-brand-primary">
                                                @if(!empty($svc['price'])){{ $svc['price'] }}@else Hubungi kami @endif
                                            </div>
                                            <span class="px-3 py-1.5 rounded-xl bg-brand-primary/15 hover:bg-brand-primary text-brand-primary hover:text-white font-bold text-xs transition flex items-center gap-1">
                                                <span>Detail</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Dots Indicator --}}
                    <div class="flex items-center justify-center gap-2 pt-2" x-show="maxIndex() > 0">
                        <template x-for="idx in (maxIndex() + 1)" :key="idx">
                            <button type="button" @click="goTo(idx - 1)" :class="currentIndex === (idx - 1) ? 'w-6 bg-brand-primary' : 'w-2 bg-slate-300 dark:bg-slate-700'" class="h-2 rounded-full transition-all duration-300" :aria-label="'Slide ' + idx"></button>
                        </template>
                    </div>

                    @if($services->count() > 3)
                        <div class="text-center pt-2">
                            <button type="button" @click="activeModal = 'all-services'" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-bold hover:opacity-90 shadow-md shadow-brand-primary/20 transition">
                                <i data-lucide="grid" class="w-4 h-4"></i>
                                Lihat Semua Layanan ({{ $services->count() }})
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Live POS Products (Integrated from Cashier Database) Slider --}}
            @if($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty())
                <div class="pt-8 space-y-6" x-data="makeSlider({{ $posProducts->count() }}, { base: 1, sm: 2, lg: 4 }, 3200)">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Menu &amp; Produk</span>
                            <h3 class="font-black text-lg sm:text-2xl text-slate-900 dark:text-white">Katalog Produk</h3>
                        </div>
                        @if($posProducts->count() > 1)
                        <div class="flex items-center gap-2">
                            <button type="button" @click="prev()" aria-label="Produk Sebelumnya" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-brand-primary hover:text-white transition shadow-sm">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button type="button" @click="next()" aria-label="Produk Berikutnya" class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-brand-primary hover:text-white transition shadow-sm">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="relative overflow-hidden rounded-3xl" @mouseenter="stop()" @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                        <div class="flex transition-transform duration-500 ease-out"
                             :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                            @foreach($posProducts as $prod)
                                <div class="w-full sm:w-1/2 lg:w-1/4 shrink-0 p-2 sm:p-2.5">
                                    <button type="button" @click="openProduct(products.find(product => product.id === '{{ $prod->id }}'))" class="w-full h-full text-left rounded-2xl glass-card p-4 flex flex-col justify-between space-y-3 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-primary transition">
                                        <div>
                                            <div class="w-full h-36 rounded-xl {{ $landingPage->dark_mode ? 'bg-slate-800' : 'bg-slate-100' }} flex items-center justify-center mb-2.5 overflow-hidden">
                                                @if($prod->image_url)
                                                    <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"><i data-lucide="package" class="hidden w-8 h-8 text-slate-400"></i>
                                                @else
                                                    <i data-lucide="package" class="w-8 h-8 text-slate-400"></i>
                                                @endif
                                            </div>
                                            <h4 class="font-bold text-xs sm:text-sm {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }} line-clamp-2 leading-snug">{{ $prod->name }}</h4>
                                            @if($prod->category)
                                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $prod->category->name }}</span>
                                            @endif
                                        </div>
                                        <div class="pt-2 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-100' }} flex items-center justify-between">
                                            <span class="font-mono font-bold text-xs sm:text-sm text-brand-primary">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                                            <span class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500 text-emerald-500 hover:text-white transition">
                                                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Dots Indicator --}}
                    <div class="flex items-center justify-center gap-2 pt-2" x-show="maxIndex() > 0">
                        <template x-for="idx in (maxIndex() + 1)" :key="idx">
                            <button type="button" @click="goTo(idx - 1)" :class="currentIndex === (idx - 1) ? 'w-6 bg-brand-primary' : 'w-2 bg-slate-300 dark:bg-slate-700'" class="h-2 rounded-full transition-all duration-300" :aria-label="'Slide ' + idx"></button>
                        </template>
                    </div>

                    @if($posProducts->count() > 4)
                        <div class="text-center pt-2">
                            <button type="button" @click="activeModal = 'all-products'" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-bold hover:opacity-90 shadow-md shadow-brand-primary/20 transition">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                Lihat Semua Produk ({{ $posProducts->count() }})
                            </button>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </section>
    @endif

    @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))
        <section id="galeri" data-section="gallery" class="py-16 sm:py-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center space-y-2">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Galeri</span>
                    @if($landingPage->gallery_title)<h2 class="text-2xl sm:text-4xl font-black tracking-tight">{{ $landingPage->gallery_title }}</h2>@endif
                    @if($landingPage->gallery_subtitle)
                        <p class="text-sm {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} max-w-2xl mx-auto">{{ $landingPage->gallery_subtitle }}</p>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($landingPage->gallery_images as $image)
                        @php
                            $galleryUrl = is_array($image) ? ($image['url'] ?? '') : $image;
                            $caption = is_array($image) ? ($image['caption'] ?? '') : '';
                        @endphp
                        @if($galleryUrl)
                            <div class="group relative aspect-square rounded-2xl overflow-hidden border {{ $landingPage->dark_mode ? 'border-slate-800 bg-slate-900' : 'border-slate-200 bg-slate-100' }} shadow-md hover:shadow-xl transition-all duration-300">
                                <img src="{{ $galleryUrl }}" alt="{{ $caption ?: ('Galeri ' . $business->name) }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.parentElement.style.display='none';">
                                @if($caption)
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3.5 pointer-events-none">
                                        <p class="text-white text-xs font-semibold leading-snug drop-shadow">{{ $caption }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- TENTANG KAMI & JAM OPERASIONAL --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['about'] && $hasAbout)
    <section id="tentang" data-section="about" class="py-16 {{ $landingPage->dark_mode ? 'bg-slate-900/60' : 'bg-slate-100/60' }}">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                {{-- Left: Story --}}
                @if($landingPage->about_title || $landingPage->about_story || $landingPage->about_image_url)
                <div class="lg:col-span-7 space-y-4">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Tentang Kami</span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                        @if($landingPage->about_title){{ $landingPage->about_title }}@endif
                    </h2>
                    <div class="text-xs sm:text-sm {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} leading-relaxed space-y-3 whitespace-pre-wrap">
                        @if($landingPage->about_story){{ $landingPage->about_story }}@endif
                    </div>

                </div>
                @endif

                @if($landingPage->about_image_url)
                <div class="lg:col-span-5">
                    <img src="{{ $landingPage->about_image_url }}" alt="{{ $business->name }}" loading="lazy" class="w-full h-full min-h-64 object-cover rounded-3xl border {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }}">
                </div>
                @endif

                {{-- Right: Jam Operasional Card --}}
                @if(!empty($landingPage->operational_hours))
                <div class="lg:col-span-5">
                    <div class="rounded-3xl glass-card p-6 border space-y-4 shadow-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-brand-primary/10 text-brand-primary flex items-center justify-center">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">Jam Buka Operasional</h3>
                                <p class="text-[11px] text-slate-500">Waktu pelayanan toko kami</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs divide-y {{ $landingPage->dark_mode ? 'divide-slate-800' : 'divide-slate-200' }}">
                            @php $hours = $landingPage->operational_hours; @endphp
                            @foreach($hours as $h)
                                <div class="pt-2 flex justify-between items-center">
                                    <span class="font-semibold {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-700' }}">{{ $h['day'] }}</span>
                                    @if(!empty($h['is_open']))
                                        @if(!empty($h['hours']))<span class="font-mono text-emerald-500 font-bold">{{ $h['hours'] }}</span>@endif
                                    @else
                                        <span class="font-mono text-rose-500 font-bold">Tutup</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                            class="w-full py-2.5 rounded-xl bg-brand-primary text-white font-bold text-xs text-center block hover:opacity-90 transition">
                            Tanya Jadwal via WhatsApp
                        </a>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
    </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- TESTIMONI PELANGGAN --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['testimonials'] && $testimonials->isNotEmpty())
        <section class="py-16 sm:py-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-10">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Ulasan Pelanggan</span>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight">Apa Kata Mereka Tentang Kami</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($testimonials as $testi)
                        <div class="rounded-3xl glass-card p-6 flex flex-col justify-between space-y-4 border">
                            {{-- Star Rating --}}
                            <div class="flex items-center gap-1 text-amber-400">
                                @for($i = 0; $i < ($testi['rating'] ?? 5); $i++)
                                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                                @endfor
                            </div>
                            <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-600' }} italic leading-relaxed">
                                "{{ $testi['quote'] ?? '' }}"
                            </p>
                            <div class="pt-3 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-100' }}">
                                @if(!empty($testi['name']))<div class="font-extrabold text-xs {{ $landingPage->dark_mode ? 'text-white' : 'text-slate-900' }}">{{ $testi['name'] }}</div>@endif
                                @if(!empty($testi['role']))<div class="text-[10px] text-slate-500">{{ $testi['role'] }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- FAQ ACCORDION --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['faq'] && $faqs->isNotEmpty())
        <section id="faq" class="py-16 {{ $landingPage->dark_mode ? 'bg-slate-900/40' : 'bg-slate-100/60' }}">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 space-y-8" x-data="{ openFaq: null }">
                <div class="text-center space-y-2">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">FAQ</span>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Pertanyaan yang Sering Diajukan</h2>
                </div>

                <div class="space-y-3">
                    @foreach($faqs as $index => $faq)
                        <div class="rounded-2xl glass-card overflow-hidden border">
                            <button @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 font-bold text-xs sm:text-sm">
                                <span>{{ $faq['question'] ?? '' }}</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 transition-transform duration-200"
                                    :class="openFaq === {{ $index }} ? 'rotate-180 text-brand-primary' : ''"></i>
                            </button>
                            <div x-show="openFaq === {{ $index }}" x-transition.opacity class="px-5 pb-4 text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-600' }} leading-relaxed" style="display: none;">
                                {{ $faq['answer'] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- LOKASI, PETA & KONTAK --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['contact'] && $hasContact)
    <section id="lokasi" data-section="contact" class="py-16 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-10">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <span class="text-xs font-extrabold uppercase tracking-widest text-brand-primary">Kunjungi Kami</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">Lokasi &amp; Kontak</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Address & Details --}}
                <div class="lg:col-span-5 rounded-3xl glass-card p-6 space-y-6 border">
                    @if($landingPage->custom_address || $business->address)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Alamat Toko</h3>
                            <p class="text-xs {{ $landingPage->dark_mode ? 'text-slate-400' : 'text-slate-600' }} mt-1 leading-relaxed">
                                {{ $landingPage->custom_address ?: $business->address }}
                            </p>
                        </div>
                    </div>
                    @endif

                    @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Telepon / WhatsApp</h3>
                            <p class="text-xs font-mono {{ $landingPage->dark_mode ? 'text-slate-300' : 'text-slate-700' }} mt-1">
                                {{ $landingPage->whatsapp_number ?: ($business->phone ?: '-') }}
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Social Media & Marketplace Channels --}}
                    @if($activeChannels->isNotEmpty() || $hasWhatsapp)
                        <div class="pt-4 border-t {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }} space-y-2.5">
                            <span class="text-xs text-slate-500 font-semibold block">Media Sosial &amp; Marketplace:</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($activeChannels as $channel)
                                    @if(filled($channel['url']))
                                        <a href="{{ $channel['url'] }}" target="_blank" rel="noopener" aria-label="{{ $channel['label'] }}" title="{{ $channel['label'] }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold {{ $landingPage->dark_mode ? 'bg-slate-800/80 text-slate-200 border border-slate-700' : 'bg-slate-100 text-slate-700 border border-slate-200' }} {{ $channel['color'] }} transition shadow-sm group">
                                            <i data-lucide="{{ $channel['icon'] }}" class="w-4 h-4 shrink-0"></i>
                                            <span class="text-[11px]">{{ $channel['label'] }}</span>
                                        </a>
                                    @endif
                                @endforeach

                                @if($hasWhatsapp)
                                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener" aria-label="WhatsApp" title="WhatsApp" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold {{ $landingPage->dark_mode ? 'bg-slate-800/80 text-slate-200 border border-slate-700' : 'bg-slate-100 text-slate-700 border border-slate-200' }} hover:bg-emerald-500 hover:text-white transition shadow-sm group">
                                        <i data-lucide="message-circle" class="w-4 h-4 shrink-0 text-emerald-500 group-hover:text-white"></i>
                                        <span class="text-[11px]">WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                        @if($landingPage->custom_email ?: $business->email)
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0"><i data-lucide="mail" class="w-5 h-5"></i></div>
                                <div><h3 class="font-bold text-sm">Email</h3><a href="mailto:{{ $landingPage->custom_email ?: $business->email }}" class="text-xs text-brand-primary mt-1 block">{{ $landingPage->custom_email ?: $business->email }}</a></div>
                            </div>
                        @endif

                    @if($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone)
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                        class="w-full py-3 rounded-2xl bg-brand-primary text-white font-bold text-xs text-center block shadow-md hover:opacity-90 transition">
                        Chat Sekarang via WhatsApp
                    </a>
                    @endif
                </div>

                {{-- Map Embed --}}
                @if($landingPage->google_maps_embed_url)
                    <div class="lg:col-span-7 rounded-3xl overflow-hidden shadow-xl border {{ $landingPage->dark_mode ? 'border-slate-800' : 'border-slate-200' }} h-80">
                        <iframe src="{{ $landingPage->google_maps_embed_url }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- Shared catalog/detail modal architecture --}}
    <div x-show="activeModal" x-cloak @keydown.escape.window="activeModal = null" @click.self="activeModal = null" class="fixed inset-0 z-[70] bg-slate-950/80 backdrop-blur-sm p-4 flex items-center justify-center" role="dialog" aria-modal="true">
        <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 border border-slate-700 shadow-2xl p-5 sm:p-7">
            <div class="flex items-center justify-between gap-3 mb-5">
                <h2 class="text-lg font-black text-slate-900 dark:text-white" x-text="activeModal === 'service' ? 'Detail Layanan' : activeModal === 'product' ? 'Detail Produk' : activeModal === 'all-services' ? 'Semua Layanan' : 'Semua Produk'"></h2>
                <button type="button" @click="activeModal = null" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:hover:text-white" aria-label="Tutup dialog"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div x-show="activeModal === 'service' || activeModal === 'product'" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="relative aspect-[4/3] rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center">
                    <template x-if="activeItem && activeItem.image_url"><img :src="activeItem.image_url" :alt="activeItem.name || activeItem.title" class="w-full h-full object-cover" x-on:error="activeItem.image_url = null"></template>
                    <i x-show="!activeItem || !activeItem.image_url" data-lucide="package" class="w-14 h-14 text-slate-400"></i>
                    <span x-show="activeItem && (activeItem.category || activeModal === 'service')" class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 dark:bg-slate-950/80 backdrop-blur border border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase tracking-wide text-brand-primary shadow-sm" x-text="activeItem?.category || 'Layanan'"></span>
                </div>
                <div class="space-y-4 flex flex-col">
                    <div>
                        <p class="text-xs text-brand-primary font-bold uppercase tracking-widest" x-text="activeModal === 'service' ? 'Layanan Kami' : 'Produk Kami'"></p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1" x-text="activeItem?.title || activeItem?.name"></h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed grow" x-text="activeItem?.description || 'Informasi detail belum tersedia.'"></p>
                    <div class="flex items-end justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xl font-black text-brand-primary" x-text="activeItem?.price ? ((typeof activeItem.price === 'number') ? formatPrice(activeItem.price) : activeItem.price) : 'Hubungi kami'"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide text-right" x-text="activeModal === 'service' ? 'Estimasi / sesuaikan kebutuhan' : 'Harga dapat berubah sewaktu-waktu'"></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a :href="activeItem ? waLink(activeItem.title || activeItem.name) : '#'" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-brand-primary text-white text-xs font-bold shadow-lg glow-brand hover:opacity-95 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                            Pesan via WhatsApp <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <button type="button" @click="activeModal = null" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">Tutup</button>
                    </div>
                </div>
            </div>
            <div x-show="activeModal === 'all-services'" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
                    <label class="relative flex-1">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="search" x-model="serviceSearch" @input="filterServices()" placeholder="Cari nama atau deskripsi layanan..." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 pl-9 pr-3 py-2.5 text-xs text-slate-900 dark:text-white focus:border-brand-primary focus:outline-none">
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                    <template x-for="svc in filteredServices" :key="svc.title">
                        <button type="button" @click="openService(svc)" class="text-left p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary transition flex flex-col justify-between group bg-white dark:bg-slate-900 shadow-sm">
                            <div>
                                <div class="relative aspect-[16/10] rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center mb-2.5">
                                    <template x-if="svc.image_url">
                                        <img :src="svc.image_url" :alt="svc.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                    </template>
                                    <i data-lucide="sparkles" class="w-8 h-8 text-slate-400" :class="svc.image_url ? 'hidden' : ''"></i>
                                    <template x-if="svc.badge">
                                        <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-brand-primary text-white text-[9px] font-black uppercase tracking-wider shadow-sm" x-text="svc.badge"></span>
                                    </template>
                                </div>
                                <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white line-clamp-1 leading-snug group-hover:text-brand-primary transition" x-text="svc.title"></h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-1 leading-relaxed" x-text="svc.description || 'Layanan unggulan berkualitas siap memenuhi kebutuhan Anda.'"></p>
                            </div>
                            <div class="pt-2.5 mt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                                <span class="text-xs sm:text-sm font-black text-brand-primary font-mono" x-text="svc.price || 'Hubungi kami'"></span>
                                <span class="px-2.5 py-1 rounded-lg bg-brand-primary/10 text-brand-primary text-[11px] font-bold group-hover:bg-brand-primary group-hover:text-white transition flex items-center gap-1">
                                    <span>Detail</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="filteredServices.length === 0" class="py-12 text-center text-slate-500">
                    <i data-lucide="search-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                    <p class="text-xs font-semibold">Tidak ada layanan yang cocok dengan pencarian ini.</p>
                </div>
            </div>
            <div x-show="activeModal === 'all-products'" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
                    <label class="relative flex-1">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="search" x-model="productSearch" @input="filterProducts()" placeholder="Cari nama atau deskripsi produk..." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 pl-9 pr-3 py-2.5 text-xs text-slate-900 dark:text-white focus:border-brand-primary focus:outline-none">
                    </label>
                </div>

                {{-- Category Pill Tabs from Database --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    <button type="button" @click="productCategory = 'all'; filterProducts()"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap"
                            :class="productCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'">
                        Semua Kategori
                    </button>
                    @foreach($productCategories as $category)
                        <button type="button" @click="productCategory = '{{ $category->id }}'; filterProducts()"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap"
                                :class="productCategory === '{{ $category->id }}' ? 'bg-brand-primary text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 pt-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="openProduct(product)" class="text-left p-3 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary transition flex flex-col justify-between group">
                            <div>
                                <div class="aspect-square rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center mb-2.5">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                    </template>
                                    <i data-lucide="package" class="w-8 h-8 text-slate-400" :class="product.image_url ? 'hidden' : ''"></i>
                                </div>
                                <h3 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-brand-primary transition" x-text="product.name"></h3>
                                <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5" x-text="product.category || ''"></p>
                            </div>
                            <div class="pt-2 mt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                                <span class="text-xs sm:text-sm font-black text-brand-primary font-mono" x-text="'Rp ' + Number(product.price || 0).toLocaleString('id-ID')"></span>
                                <span class="p-1 rounded-lg bg-brand-primary/10 text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="filteredProducts.length === 0" class="py-12 text-center text-slate-500">
                    <i data-lucide="package-open" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                    <p class="text-xs font-semibold">Tidak ada produk yang cocok dengan pencarian / kategori ini.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile section navigation --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 dark:bg-slate-950/95 backdrop-blur border-t border-slate-200 dark:border-slate-800 px-2 pt-2 pb-[calc(.5rem+env(safe-area-inset-bottom))]" aria-label="Navigasi halaman">
        <div class="grid grid-cols-5 gap-1">
            @if($sectionVisibility['hero'] && $hasHero)<a href="#hero" class="text-center text-[10px] font-bold text-slate-500 py-1.5" :class="activeSection === 'hero' ? 'text-brand-primary' : ''"><i data-lucide="home" class="w-4 h-4 mx-auto mb-0.5"></i>Home</a>@endif
            @if($sectionVisibility['about'] && $hasAbout)<a href="#tentang" class="text-center text-[10px] font-bold text-slate-500 py-1.5" :class="activeSection === 'about' ? 'text-brand-primary' : ''"><i data-lucide="book-open" class="w-4 h-4 mx-auto mb-0.5"></i>About</a>@endif
            @if(($sectionVisibility['services'] && $services->isNotEmpty()) || ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty()))<a href="#layanan" class="text-center text-[10px] font-bold text-slate-500 py-1.5" :class="activeSection === 'services' ? 'text-brand-primary' : ''"><i data-lucide="package" class="w-4 h-4 mx-auto mb-0.5"></i>Katalog</a>@endif
            @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))<a href="#galeri" class="text-center text-[10px] font-bold text-slate-500 py-1.5" :class="activeSection === 'gallery' ? 'text-brand-primary' : ''"><i data-lucide="images" class="w-4 h-4 mx-auto mb-0.5"></i>Galeri</a>@endif
            @if($sectionVisibility['contact'] && $hasContact)<a href="#lokasi" class="text-center text-[10px] font-bold text-slate-500 py-1.5" :class="activeSection === 'contact' ? 'text-brand-primary' : ''"><i data-lucide="map-pin" class="w-4 h-4 mx-auto mb-0.5"></i>Kontak</a>@endif
        </div>
    </nav>

    {{-- FOOTER --}}
    {{-- ========================================================================= --}}
    @if($sectionVisibility['footer'])
    <footer class="border-t {{ $landingPage->dark_mode ? 'bg-slate-950 border-slate-800/80 text-slate-400' : 'bg-slate-950 border-slate-800 text-slate-400' }}">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <div class="grid grid-cols-1 sm:{{ $footerGridClass }} gap-10 lg:gap-12">
                {{-- Brand and social --}}
                @if($sectionVisibility['footer_brand'])
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        @if($landingPage->logo_url ?: $business->logo_url)
                            <img src="{{ $landingPage->logo_url ?: $business->logo_url }}" alt="{{ $business->name }}" class="w-11 h-11 rounded-xl object-contain bg-white/10 p-1" onerror="this.style.display='none';">
                        @endif
                        <div>
                            <div class="font-black text-lg text-white leading-tight">{{ $business->name }}</div>
                            @if($landingPage->industry_preset)
                                <div class="text-[10px] uppercase tracking-widest text-brand-primary font-bold">{{ $landingPage->industry_preset }}</div>
                            @endif
                        </div>
                    </div>
                    @if($landingPage->footer_description ?: ($business->description ?: $landingPage->subheadline))
                        <p class="text-xs leading-relaxed max-w-sm">{{ $landingPage->footer_description ?: ($business->description ?: $landingPage->subheadline) }}</p>
                    @endif
                    @if($activeChannels->isNotEmpty() || $hasWhatsapp)
                        <div class="space-y-2 pt-1">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Media Sosial &amp; Marketplace</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($activeChannels as $channel)
                                    @if(filled($channel['url']))
                                        <a href="{{ $channel['url'] }}" target="_blank" rel="noopener" aria-label="{{ $channel['label'] }}" title="{{ $channel['label'] }}" class="w-9 h-9 rounded-xl bg-white/10 text-slate-300 hover:text-white flex items-center justify-center transition shadow-sm {{ $channel['color'] }}">
                                            <i data-lucide="{{ $channel['icon'] }}" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                @endforeach
                                @if($hasWhatsapp)
                                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener" aria-label="WhatsApp" title="WhatsApp" class="w-9 h-9 rounded-xl bg-white/10 text-slate-300 hover:text-white hover:bg-emerald-500 flex items-center justify-center transition shadow-sm">
                                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 hover:text-white"></i>
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
                        <h3 class="text-sm font-black text-white mb-5">{{ $landingPage->footer_navigation_title ?: 'Navigasi' }}</h3>
                        <nav class="space-y-3 text-xs">
                            @if($sectionVisibility['hero'] && $hasHero)<a href="#hero" class="block hover:text-brand-primary transition">Beranda</a>@endif
                            @if($sectionVisibility['about'] && $hasAbout)<a href="#tentang" class="block hover:text-brand-primary transition">Tentang Kami</a>@endif
                            @if(($sectionVisibility['services'] && $services->isNotEmpty()) || ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty()))<a href="#layanan" class="block hover:text-brand-primary transition">Layanan</a>@endif
                            @if($sectionVisibility['gallery'] && !empty($landingPage->gallery_images))<a href="#galeri" class="block hover:text-brand-primary transition">Galeri</a>@endif
                            @if($sectionVisibility['contact'] && $hasContact)<a href="#lokasi" class="block hover:text-brand-primary transition">Kontak</a>@endif
                        </nav>
                    </div>
                @endif

                {{-- Services --}}
                @if($sectionVisibility['footer_services'] && $sectionVisibility['services'] && $services->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-black text-white mb-5">{{ $landingPage->footer_services_title ?: 'Layanan' }}</h3>
                        <nav class="space-y-3 text-xs">
                            @foreach($services->take(6) as $service)
                                @if(!empty($service['title']))<a href="#layanan" class="block hover:text-brand-primary transition">{{ $service['title'] }}</a>@endif
                            @endforeach
                        </nav>
                    </div>
                @endif

                {{-- Contact --}}
                @if($sectionVisibility['footer_contact'] && $hasContact)
                    <div>
                        <h3 class="text-sm font-black text-white mb-5">{{ $landingPage->footer_contact_title ?: 'Kontak' }}</h3>
                        <div class="space-y-3 text-xs">
                            @if($landingPage->custom_address ?: $business->address)<div class="flex items-start gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-brand-primary shrink-0"></i><span>{{ $landingPage->custom_address ?: $business->address }}</span></div>@endif
                            @if($landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone))<div class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-brand-primary shrink-0"></i><span>{{ $landingPage->whatsapp_number ?: ($landingPage->custom_phone ?: $business->phone) }}</span></div>@endif
                            @if($landingPage->custom_email ?: $business->email)<div class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-brand-primary shrink-0"></i><span>{{ $landingPage->custom_email ?: $business->email }}</span></div>@endif
                        </div>
                        @if(($landingPage->footer_cta_text ?: $landingPage->cta_primary_text) && ($landingPage->whatsapp_number || $landingPage->custom_phone || $business->phone))
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 mt-6 px-4 py-2.5 rounded-xl bg-brand-primary text-white text-xs font-black hover:opacity-90 transition">
                                <i data-lucide="calendar-check" class="w-4 h-4"></i>{{ $landingPage->footer_cta_text ?: $landingPage->cta_primary_text }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="border-t border-slate-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px]">
                <p>{{ $landingPage->footer_copyright ?: '© ' . date('Y') . ' ' . $business->name . '. All rights reserved.' }}</p>
                <p>Didukung oleh <a href="https://cooca.id" target="_blank" rel="noopener" class="font-bold hover:underline text-brand-primary">COOCA</a></p>
            </div>
        </div>
    </footer>
    @endif

    {{-- ========================================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (PULSING WIDGET) --}}
    {{-- ========================================================================= --}}
    <div class="fixed bottom-24 right-4 md:bottom-6 md:right-6 z-40 flex flex-col items-end">

        {{-- Floating Greeting Bubble --}}
        <div x-show="waChatOpen" x-transition.opacity
            class="mb-3 p-4 rounded-2xl glass-card shadow-2xl border max-w-xs text-xs space-y-2" style="display: none;">
            <div class="font-bold flex items-center justify-between">
                <span>{{ $business->name }}</span>
                <button @click="waChatOpen = false" class="text-slate-400 hover:text-slate-200">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <p class="text-[11px] text-slate-500 leading-relaxed">
                Halo! Ada yang bisa kami bantu seputar produk atau layanan kami?
            </p>
            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                class="w-full py-2 rounded-xl bg-brand-primary text-white font-bold text-center block">
                Mulai Obrolan WhatsApp →
            </a>
        </div>

        {{-- Button Trigger --}}
        <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
            class="w-14 h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-2xl shadow-emerald-600/40 hover:scale-110 transition-transform duration-200 group relative">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z"/></svg>
            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-black flex items-center justify-center animate-bounce">1</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
