@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#0B0F19' : $activeTheme['bg_color'] ?? '#FAF7F2' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO & Social Media Metadata Architecture --}}
    @php
        $defaultBrandImg = $business->logo_url ?: ($landingPage->logo_url ?: $landingPage->hero_image_url);
    @endphp
    @include('public.partials.seo', [
        'siteName' => $business->name,
        'fallbackImage' => $defaultBrandImg ?: asset('assets/seo/cooca-og-default.jpg'),
        'url' => $canonicalUrl ?? url('/' . $business->slug),
        'title' => $pageTitle ?? ($landingPage->meta_title ?: $business->name . ' - ' . ($landingPage->headline ?: 'Toko Online Resmi')),
        'description' => $ogDescription ?? ($landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Belanja aneka produk dan layanan berkualitas langsung dari ' . $business->name . ' dengan jaminan kualitas dan pengiriman terpercaya.'))),
        'keywords' => $landingPage->meta_keywords ?? null,
    ])

    @stack('seo')

    {{-- Google Fonts for Authenticated Theme --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?{!! $activeTheme['google_fonts'] ?? 'family=Plus+Jakarta+Sans:wght@400;500;600;700;800' !!}&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"{{ $activeTheme['font_heading'] }}"', 'sans-serif'],
                        body: ['"{{ $activeTheme['font_body'] }}"', 'sans-serif'],
                        sans: ['"{{ $activeTheme['font_body'] }}"', '-apple-system', 'BlinkMacSystemFont',
                            'sans-serif'
                        ],
                    },
                    colors: {
                        theme: {
                            primary: '{{ $activeTheme['primary_color'] }}',
                            accent: '{{ $activeTheme['accent_color'] }}',
                            bg: '{{ $activeTheme['bg_color'] }}',
                            card: '{{ $activeTheme['card_bg'] }}',
                            badgeBg: '{{ $activeTheme['badge_bg'] ?? 'rgba(0,0,0,0.05)' }}',
                            badgeText: '{{ $activeTheme['badge_text'] ?? '#111827' }}',
                        }
                    },
                    borderRadius: {
                        'theme': '{{ $activeTheme['border_radius'] ?? '16px' }}',
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js & Lucide Icons --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Authentic Theme Dynamic CSS Tokens --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --theme-primary: {{ $activeTheme['primary_color'] }};
            --theme-accent: {{ $activeTheme['accent_color'] }};
            --theme-bg: {{ $activeTheme['bg_color'] }};
            --theme-card: {{ $activeTheme['card_bg'] }};
            --theme-radius: {{ $activeTheme['border_radius'] ?? '16px' }};
            --font-heading: '{{ $activeTheme['font_heading'] }}', sans-serif;
            --font-body: '{{ $activeTheme['font_body'] }}', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--theme-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1,
        h2,
        h3,
        h4,
        .font-heading {
            font-family: var(--font-heading);
        }

        .theme-surface {
            background-color: var(--theme-card);
            border-radius: var(--theme-radius);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }

        .dark .theme-surface {
            background-color: #181E2A;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 24px -2px rgba(0, 0, 0, 0.4);
        }

        .theme-btn-primary {
            background-color: var(--theme-primary);
            color: #FFFFFF;
            border-radius: var(--theme-radius);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .theme-btn-primary:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .theme-btn-primary:active {
            transform: translateY(0) scale(0.98);
        }

        .theme-badge {
            background-color: {{ $activeTheme['badge_bg'] ?? 'rgba(0,0,0,0.05)' }};
            color: {{ $activeTheme['badge_text'] ?? 'var(--theme-primary)' }};
            border-radius: 9999px;
        }
    </style>

    {{-- Universal Typography Hierarchy (H1 - H6 & Typographic Roles) --}}
    @include('layouts.partials.typography')
</head>

<body class="min-h-screen flex flex-col text-neutral-900 dark:text-neutral-100 transition-colors duration-300"
    x-data="{
        mobileMenuOpen: false,
        waChatOpen: false
    }">

    {{-- Private Draft Preview Banner --}}
    @if (!$landingPage->is_published)
        <div
            class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Bisnis &mdash; Halaman ini masih berstatus draft privat dan belum dipublikasikan ke
                umum.</span>
        </div>
    @endif

    {{-- Announcement Bar (If enabled) --}}
    @if ($landingPage->announcement_badge)
        <div
            class="bg-gradient-to-r from-theme-primary to-theme-accent text-white text-xs sm:text-sm font-medium py-2 px-4 text-center tracking-wide shadow-sm flex items-center justify-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>{{ $landingPage->announcement_badge }}</span>
        </div>
    @endif

    {{-- Operating Days / Holiday Status Banner --}}
    @php
        $dayMap = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        $operatingDays = (array) ($storeSetting?->operating_days ?? []);
        $currentDayOfWeek = strtolower(now()->format('l'));
        $isStoreOpenToday = empty($operatingDays) || in_array($currentDayOfWeek, $operatingDays, true);
    @endphp
    @if (!$isStoreOpenToday)
        <div
            class="bg-amber-500/10 border-b border-amber-500/20 py-2 px-4 text-amber-800 dark:text-amber-300 text-xs text-center flex items-center justify-center gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-amber-500 shrink-0"></i>
            <span>Outlet toko libur hari ini ({{ $dayMap[$currentDayOfWeek] ?? '' }}). Pesanan online tetap dapat
                dibuat dan akan diproses pada hari kerja berikutnya.</span>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- AUTO-HIDE DYNAMIC NAVBAR (§PRD-07 §2.B)                                    --}}
    {{-- Only renders navigation tabs where active_pages[page] === true.           --}}
    {{-- ========================================================================= --}}
    <header
        class="sticky top-0 z-40 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-xl border-b border-black/5 dark:border-white/10 transition-colors">
        <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">

            {{-- Brand Logo & Name --}}
            <a href="{{ url('/' . $business->slug) }}" class="flex items-center gap-3 shrink-0 group">
                @if ($business->logo_url || $landingPage->logo_url)
                    <img src="{{ $business->logo_url ?: $landingPage->logo_url }}" alt="{{ $business->name }}"
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover border border-black/5 dark:border-white/10 shadow-sm group-hover:scale-105 transition duration-200">
                @else
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-theme flex items-center justify-center font-heading font-bold text-lg text-white shadow-sm"
                        style="background-color: var(--theme-primary);">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <span
                        class="font-heading font-bold text-base sm:text-lg tracking-tight text-neutral-900 dark:text-white leading-tight">
                        {{ $business->name }}
                    </span>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">
                        {{ $activeTheme['industry'] ?? 'Toko Resmi' }}
                    </span>
                </div>
            </a>

            {{-- Desktop Navigation (Strict Auto-Hide) --}}
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                {{-- Home is always active --}}
                <a href="{{ url('/' . $business->slug) }}"
                    class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->is($business->slug) ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-white/5' }}">
                    {{ $landingPage->getNavLabel('home', 'Beranda') }}
                </a>

                {{-- Catalog --}}
                @if ($landingPage->isPageActive('catalog'))
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->is($business->slug . '/katalog*') || request()->is($business->slug . '/produk*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-white/5' }}">
                        {{ $landingPage->getNavLabel('catalog', 'Katalog Produk') }}
                    </a>
                @endif

                {{-- About Us --}}
                @if ($landingPage->isPageActive('about'))
                    <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                        class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->is($business->slug . '/tentang-kami*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-white/5' }}">
                        {{ $landingPage->getNavLabel('about', 'Tentang Kami') }}
                    </a>
                @endif

                {{-- Reservation / Booking --}}
                @if ($landingPage->isPageActive('reservation'))
                    <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                        class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->is($business->slug . '/reservasi*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-white/5' }}">
                        {{ $landingPage->getNavLabel('reservation', 'Reservasi') }}
                    </a>
                @endif

                {{-- Contact & Branches --}}
                @if ($landingPage->isPageActive('contact'))
                    <a href="{{ url('/' . $business->slug . '/kontak') }}"
                        class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->is($business->slug . '/kontak*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-white/5' }}">
                        {{ $landingPage->getNavLabel('contact', 'Kontak & Cabang') }}
                    </a>
                @endif

                {{-- Blog & Articles --}}
                @if ($landingPage->isPageActive('blog'))
                    <a href="{{ url('/' . $business->slug . '/artikel') }}"
                        class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->is($business->slug . '/artikel*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-50 dark:hover:bg-white/5' }}">
                        {{ $landingPage->getNavLabel('blog', 'Artikel') }}
                    </a>
                @endif
            </nav>


            {{-- Action Tools: Customer Auth, Cart, Checkout & Mobile Menu --}}
            <div class="flex items-center gap-2 sm:gap-3">

                {{-- Customer Auth State --}}
                @if (auth('customer')->check())
                    {{-- Logged-in Customer Avatar + Dropdown --}}
                    <div x-data="{ profileOpen: false }" class="relative">
                        <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false"
                            class="flex items-center gap-2 p-1.5 pr-3 rounded-[12px] bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition"
                            aria-label="Akun Saya">
                            @if (auth('customer')->user()->avatar_url)
                                <img src="{{ auth('customer')->user()->avatar_url }}" alt=""
                                    class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div
                                    class="w-7 h-7 rounded-full bg-theme-primary text-white flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr(auth('customer')->user()->name ?? 'C', 0, 1)) }}
                                </div>
                            @endif
                            <span
                                class="hidden sm:inline text-xs font-medium text-neutral-700 dark:text-neutral-300 max-w-[80px] truncate">
                                {{ auth('customer')->user()->name }}
                            </span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-neutral-400"></i>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="profileOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-52 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] shadow-xl py-1.5 z-50">
                            <a href="{{ route('customer.dashboard') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                            </a>
                            <a href="{{ route('customer.orders') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                                <i data-lucide="package" class="w-4 h-4"></i> Pesanan Saya
                            </a>
                            <a href="{{ route('customer.cart') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Keranjang
                            </a>
                            <a href="{{ route('customer.profile') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/5 transition">
                                <i data-lucide="user" class="w-4 h-4"></i> Profil
                            </a>
                            <div class="border-t border-black/[0.06] dark:border-white/[0.08] my-1"></div>
                            <form method="POST" action="{{ route('customer.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition w-full text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Guest: Login Button --}}
                    <a href="{{ route('customer.login', ['store' => $business->slug, 'redirect' => url()->current()]) }}"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[12px] text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition min-h-[40px]">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Masuk</span>
                    </a>
                @endif

                {{-- Cart Trigger with Live Counter --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="relative p-2.5 rounded-[12px] bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition flex items-center justify-center text-neutral-800 dark:text-neutral-200"
                    aria-label="Keranjang Belanja">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    <span x-show="$store.cart.count() > 0" x-cloak x-text="$store.cart.count()"
                        class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1 rounded-full bg-theme-primary text-white text-[11px] font-bold flex items-center justify-center shadow-sm">
                    </span>
                </a>

                {{-- Direct Checkout Button (Desktop) --}}
                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="hidden lg:inline-flex items-center gap-2 px-4 py-2.5 rounded-[12px] font-medium text-sm text-white shadow-sm transition hover:opacity-95 active:scale-[0.98] min-h-[44px]"
                    style="background-color: var(--theme-primary);">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Checkout</span>
                </a>

                {{-- Mobile Hamburger Trigger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2.5 rounded-[12px] bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition"
                    aria-label="Menu Navigasi">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" x-cloak></i>
                </button>
            </div>
        </div>

        {{-- Mobile Slide-Down Menu (Strict Auto-Hide) --}}
        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-black/5 dark:border-white/10 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-2xl px-4 py-4 space-y-1">

            <a href="{{ url('/' . $business->slug) }}" @click="mobileMenuOpen = false"
                class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is($business->slug) ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-700 dark:text-neutral-300' }}">
                {{ $landingPage->getNavLabel('home', 'Beranda') }}
            </a>

            @if ($landingPage->isPageActive('catalog'))
                <a href="{{ url('/' . $business->slug . '/katalog') }}" @click="mobileMenuOpen = false"
                    class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is($business->slug . '/katalog*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-700 dark:text-neutral-300' }}">
                    {{ $landingPage->getNavLabel('catalog', 'Katalog Produk') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('about'))
                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}" @click="mobileMenuOpen = false"
                    class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is($business->slug . '/tentang-kami*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-700 dark:text-neutral-300' }}">
                    {{ $landingPage->getNavLabel('about', 'Tentang Kami') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('reservation'))
                <a href="{{ url('/' . $business->slug . '/reservasi') }}" @click="mobileMenuOpen = false"
                    class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is($business->slug . '/reservasi*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-700 dark:text-neutral-300' }}">
                    {{ $landingPage->getNavLabel('reservation', 'Reservasi & Booking') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('contact'))
                <a href="{{ url('/' . $business->slug . '/kontak') }}" @click="mobileMenuOpen = false"
                    class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is($business->slug . '/kontak*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-700 dark:text-neutral-300' }}">
                    {{ $landingPage->getNavLabel('contact', 'Kontak & Cabang') }}
                </a>
            @endif

            @if ($landingPage->isPageActive('blog'))
                <a href="{{ url('/' . $business->slug . '/artikel') }}" @click="mobileMenuOpen = false"
                    class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is($business->slug . '/artikel*') ? 'text-theme-primary font-semibold bg-neutral-100 dark:bg-white/5' : 'text-neutral-700 dark:text-neutral-300' }}">
                    {{ $landingPage->getNavLabel('blog', 'Artikel & Tips') }}
                </a>
            @endif

            <div class="pt-2 border-t border-black/5 dark:border-white/10 space-y-1.5">
                {{-- Customer Auth in Mobile --}}
                @if (auth('customer')->check())
                    <a href="{{ route('customer.dashboard') }}" @click="mobileMenuOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-[12px] text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard Saya
                    </a>
                    <a href="{{ route('customer.orders') }}" @click="mobileMenuOpen = false"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-[12px] text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        <i data-lucide="package" class="w-4 h-4"></i> Pesanan Saya
                    </a>
                @else
                    <a href="{{ route('customer.login', ['store' => $business->slug, 'redirect' => url()->current()]) }}"
                        @click="mobileMenuOpen = false"
                        class="flex items-center justify-center gap-2 w-full py-3 rounded-[12px] font-semibold text-sm bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Masuk / Daftar Akun</span>
                    </a>
                @endif

                <a href="{{ url('/' . $business->slug . '/checkout') }}"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl font-semibold text-sm text-white shadow-sm"
                    style="background-color: var(--theme-primary);">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Keranjang & Checkout (<span x-text="$store.cart.count()"></span>)</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content Slot --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ========================================================================= --}}
    {{-- FLOATING CART BAR (Zero Modal Hell! Direct Link to Standalone Checkout)     --}}
    {{-- ========================================================================= --}}
    <div x-show="$store.cart.count() > 0 && !window.location.pathname.endsWith('/checkout')" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10"
        class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:max-w-md z-40">
        <div
            class="bg-neutral-900/95 dark:bg-white/95 text-white dark:text-neutral-900 backdrop-blur-2xl px-5 py-3.5 rounded-2xl shadow-2xl border border-white/10 dark:border-black/10 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-theme-primary text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <span x-text="$store.cart.count()"></span>
                </div>
                <div>
                    <div class="text-xs text-neutral-400 dark:text-neutral-600 font-medium">Subtotal Belanja</div>
                    <div class="text-base font-bold font-mono tracking-tight"
                        x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')"></div>
                </div>
            </div>
            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                class="px-4 py-2.5 rounded-xl font-semibold text-sm text-white shadow-sm flex items-center gap-2 hover:opacity-90 active:scale-95 transition shrink-0"
                style="background-color: var(--theme-primary);">
                <span>Lanjut Bayar</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (Direct Customer Support)                         --}}
    {{-- ========================================================================= --}}
    @if ($hasWhatsapp)
        <div class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-30 flex flex-col items-end">
            {{-- WhatsApp Greeting Bubble --}}
            <div x-show="waChatOpen" x-cloak x-transition.opacity
                class="mb-3 p-4 rounded-2xl bg-white/95 dark:bg-neutral-800/95 backdrop-blur-2xl shadow-xl border border-black/10 dark:border-white/10 max-w-xs text-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-neutral-900 dark:text-white">{{ $business->name }}</span>
                    <button type="button" @click="waChatOpen = false"
                        class="text-neutral-400 hover:text-neutral-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <p class="text-neutral-600 dark:text-neutral-300 leading-relaxed">
                    {{ $landingPage->whatsapp_welcome_message ?: 'Halo! Ada yang bisa kami bantu seputar produk atau layanan kami?' }}
                </p>
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full py-2 px-3 rounded-xl bg-[#25D366] text-white font-semibold text-xs text-center flex items-center justify-center gap-1.5 hover:opacity-95 transition shadow-sm">
                    <span>Chat di WhatsApp</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- WhatsApp Trigger Button --}}
            <button type="button" @click="waChatOpen = !waChatOpen"
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-lg hover:scale-105 active:scale-95 transition duration-200"
                aria-label="Hubungi WhatsApp">
                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z" />
                </svg>
            </button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- STOREFRONT FOOTER (Auto-Hide Active Links Only)                            --}}
    {{-- ========================================================================= --}}
    <footer
        class="mt-auto border-t border-black/5 dark:border-white/10 bg-white/60 dark:bg-neutral-900/60 backdrop-blur-lg pt-12 pb-8">
        <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">

                {{-- Col 1: Business Profile --}}
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        @if ($business->logo_url || $landingPage->logo_url)
                            <img src="{{ $business->logo_url ?: $landingPage->logo_url }}"
                                alt="{{ $business->name }}"
                                class="w-10 h-10 rounded-full object-cover border border-black/5 dark:border-white/10">
                        @endif
                        <span class="font-heading font-bold text-lg text-neutral-900 dark:text-white">
                            {{ $business->name }}
                        </span>
                    </div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-md leading-relaxed">
                        {{ $landingPage->footer_description ?: ($landingPage->subheadline ?: $business->description) }}
                    </p>
                    @if ($landingPage->custom_address || $business->address)
                        <div class="flex items-start gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                            <i data-lucide="map-pin" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span>{{ $landingPage->custom_address ?: $business->address }}</span>
                        </div>
                    @endif
                </div>

                {{-- Col 2: Active Navigation Links (Auto-Hide) --}}
                <div class="space-y-3">
                    <div
                        class="font-heading font-bold text-sm text-neutral-900 dark:text-white tracking-wide uppercase">
                        {{ $landingPage->footer_navigation_title ?: 'Navigasi Toko' }}
                    </div>
                    <ul class="space-y-2 text-sm text-neutral-600 dark:text-neutral-400">
                        <li>
                            <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">
                                {{ $landingPage->getNavLabel('home', 'Beranda') }}
                            </a>
                        </li>
                        @if ($landingPage->isPageActive('catalog'))
                            <li>
                                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                                    class="hover:text-theme-primary transition">
                                    {{ $landingPage->getNavLabel('catalog', 'Katalog Produk') }}
                                </a>
                            </li>
                        @endif
                        @if ($landingPage->isPageActive('about'))
                            <li>
                                <a href="{{ url('/' . $business->slug . '/tentang-kami') }}"
                                    class="hover:text-theme-primary transition">
                                    {{ $landingPage->getNavLabel('about', 'Tentang Kami') }}
                                </a>
                            </li>
                        @endif
                        @if ($landingPage->isPageActive('reservation'))
                            <li>
                                <a href="{{ url('/' . $business->slug . '/reservasi') }}"
                                    class="hover:text-theme-primary transition">
                                    {{ $landingPage->getNavLabel('reservation', 'Reservasi & Booking') }}
                                </a>
                            </li>
                        @endif
                        @if ($landingPage->isPageActive('contact'))
                            <li>
                                <a href="{{ url('/' . $business->slug . '/kontak') }}"
                                    class="hover:text-theme-primary transition">
                                    {{ $landingPage->getNavLabel('contact', 'Kontak & Cabang') }}
                                </a>
                            </li>
                        @endif
                        @if ($landingPage->isPageActive('blog'))
                            <li>
                                <a href="{{ url('/' . $business->slug . '/artikel') }}"
                                    class="hover:text-theme-primary transition">
                                    {{ $landingPage->getNavLabel('blog', 'Artikel & Tips') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Col 3: Customer Service & Jam Buka --}}
                <div class="space-y-3">
                    <div
                        class="font-heading font-bold text-sm text-neutral-900 dark:text-white tracking-wide uppercase">
                        {{ $landingPage->footer_contact_title ?: 'Layanan Pelanggan' }}
                    </div>
                    <div class="space-y-2 text-sm text-neutral-600 dark:text-neutral-400">
                        @if ($hasWhatsapp)
                            <div>
                                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 text-theme-primary font-medium hover:underline">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    <span>WhatsApp: {{ $landingPage->whatsapp_number ?: $business->phone }}</span>
                                </a>
                            </div>
                        @endif
                        @if ($landingPage->custom_email || $business->email)
                            <div class="flex items-center gap-1.5 text-xs">
                                <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $landingPage->custom_email ?: $business->email }}</span>
                            </div>
                        @endif
                        <div class="pt-2">
                            <a href="{{ url('/' . $business->slug . '/checkout') }}"
                                class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition">
                                Keranjang & Checkout
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Attribution & Copyright --}}
            <div
                class="pt-8 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-500">
                <div>
                    {{ $landingPage->footer_copyright ?: '© ' . date('Y') . ' ' . $business->name . '. Seluruh hak cipta dilindungi.' }}
                </div>
                <div class="flex items-center gap-1.5">
                    <span>Didukung oleh</span>
                    <a href="https://cooca.id" target="_blank"
                        class="font-semibold text-neutral-700 dark:text-neutral-300 hover:text-theme-primary transition">
                        Cooca Commerce
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ========================================================================= --}}
    {{-- ALPINE.JS GLOBAL CART STORE (Persistent LocalStorage + Customer DB Sync)    --}}
    {{-- ========================================================================= --}}
    <script>
        document.addEventListener('alpine:init', () => {
            const STORAGE_KEY = 'cooca_cart_{{ $business->id }}';

            Alpine.store('cart', {
                items: [],

                init() {
                    // 1. Initial hydration from server DB items (if logged in) or LocalStorage
                    const serverDbItems = @json($dbCartItems ?? null);
                    if (serverDbItems && serverDbItems.length > 0) {
                        this.items = serverDbItems;
                        this.save();
                    } else {
                        try {
                            const local = localStorage.getItem(STORAGE_KEY);
                            this.items = local ? JSON.parse(local) : [];
                        } catch (e) {
                            this.items = [];
                        }
                    }
                },

                save() {
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
                    } catch (e) {}
                },

                add(product, qty = 1, notes = '') {
                    qty = Math.max(1, parseInt(qty) || 1);
                    const existing = this.items.find(item => item.id === product.id);
                    if (existing) {
                        existing.quantity += qty;
                        if (notes) existing.notes = notes;
                    } else {
                        this.items.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price || product.selling_price || 0),
                            image_url: product.image_url || null,
                            quantity: qty,
                            notes: notes || '',
                        });
                    }
                    this.save();
                },

                remove(productId) {
                    this.items = this.items.filter(item => item.id !== productId);
                    this.save();
                },

                updateQty(productId, qty) {
                    qty = parseInt(qty) || 0;
                    if (qty <= 0) {
                        this.remove(productId);
                    } else {
                        const item = this.items.find(i => i.id === productId);
                        if (item) {
                            item.quantity = qty;
                            this.save();
                        }
                    }
                },

                clear() {
                    this.items = [];
                    this.save();
                },

                count() {
                    return this.items.reduce((acc, item) => acc + (parseInt(item.quantity) || 0), 0);
                },

                subtotal() {
                    return this.items.reduce((acc, item) => acc + ((parseFloat(item.price) || 0) * (
                        parseInt(item.quantity) || 1)), 0);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>

</html>
