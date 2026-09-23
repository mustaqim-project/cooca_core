<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <!-- No-Flash Theme Bootstrap (Eliminates FOUC) -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('cooca-theme') || 'light';
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>
    @php
        $siteLogoLightSetting = \App\Models\SystemSetting::get('site_logo_light');
        $siteLogoDarkSetting = \App\Models\SystemSetting::get('site_logo_dark');
        $siteFaviconSetting = \App\Models\SystemSetting::get('site_favicon');
        $seoOgImageSetting = \App\Models\SystemSetting::get('seo_og_image');

        $siteLogoLightUrl =
            \App\Domain\Storage\AdminStorage::publicUrl($siteLogoLightSetting) ??
            asset('assets/image/1785229034_logo_dark.png');
        $siteLogoDarkUrl =
            \App\Domain\Storage\AdminStorage::publicUrl($siteLogoDarkSetting) ??
            asset('assets/image/1785229034_logo_dark.png');
        $siteFaviconUrl =
            \App\Domain\Storage\AdminStorage::publicUrl($siteFaviconSetting) ??
            asset('assets/image/1785229034_favicon.png');
        $seoOgImageUrl =
            \App\Domain\Storage\AdminStorage::publicUrl($seoOgImageSetting) ?? asset('assets/image/cooca.png');

        $siteAppName = \App\Models\SystemSetting::get('app_name', 'Cooca');
        $siteTagline = \App\Models\SystemSetting::get('site_tagline', 'Business Operating System & Omnichannel ERP');
        $seoMetaTitle = \App\Models\SystemSetting::get(
            'seo_meta_title',
            'Cooca - Business Operating System & Omnichannel ERP',
        );
        $seoMetaDesc = \App\Models\SystemSetting::get(
            'seo_meta_description',
            'Cooca: Software kasir POS, pembukuan otomatis, omnichannel media sosial & AI Assistant gratis selamanya untuk UMKM Indonesia.',
        );
        $seoKeywords = \App\Models\SystemSetting::get(
            'seo_meta_keywords',
            'Cooca, software kasir gratis, erp umkm, pos kasir toko, aplikasi pembukuan gratis, sistem operasional bisnis, cooca.id',
        );
        $seoAuthor = \App\Models\SystemSetting::get('seo_author', 'Cooca Indonesia');
        $seoRobots = \App\Models\SystemSetting::get('seo_robots', 'index, follow');
        $seoCanonical = \App\Models\SystemSetting::get('seo_canonical_url') ?: url()->current();
        $seoOgTitle = \App\Models\SystemSetting::get('seo_og_title') ?: $seoMetaTitle;
        $seoOgDesc = \App\Models\SystemSetting::get('seo_og_description') ?: $seoMetaDesc;
        $seoTwitterCard = \App\Models\SystemSetting::get('seo_twitter_card', 'summary_large_image');
        $seoTwitterSite = \App\Models\SystemSetting::get('seo_twitter_site', '@cooca_id');
        $seoGoogleVerification = \App\Models\SystemSetting::get('seo_google_verification');
        $seoBingVerification = \App\Models\SystemSetting::get('seo_bing_verification');
        $seoGaId = \App\Models\SystemSetting::get('seo_google_analytics_id');
        $seoCustomHeadScripts = \App\Models\SystemSetting::get('seo_custom_head_scripts');
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @hasSection('title')
            @yield('title')@else{{ $title ?? $seoMetaTitle }}
        @endif
    </title>
    <meta name="description" content="@yield('description', $seoMetaDesc)">
    <meta name="keywords" content="@yield('keywords', $seoKeywords)">
    <meta name="author" content="{{ $seoAuthor }}">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteAppName }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:title"
        content="{{ $seoOgTitle ?: $title ?? (View::hasSection('title') ? View::yieldContent('title') : $seoMetaTitle) }}">
    <meta property="og:description" content="@yield('description', $seoOgDesc ?: $seoMetaDesc)">
    <meta property="og:image" content="@yield('og_image', $seoOgImageUrl)">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="{{ $seoTwitterCard }}">
    <meta name="twitter:site" content="{{ $seoTwitterSite }}">
    <meta name="twitter:title"
        content="{{ $seoOgTitle ?: $title ?? (View::hasSection('title') ? View::yieldContent('title') : $seoMetaTitle) }}">
    <meta name="twitter:description" content="@yield('description', $seoOgDesc ?: $seoMetaDesc)">
    <meta name="twitter:image" content="@yield('og_image', $seoOgImageUrl)">

    <!-- Webmaster Verification -->
    @if (!empty($seoGoogleVerification))
        <meta name="google-site-verification" content="{{ $seoGoogleVerification }}">
    @endif
    @if (!empty($seoBingVerification))
        <meta name="msvalidate.01" content="{{ $seoBingVerification }}">
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $siteFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $siteFaviconUrl }}">

    <!-- SEO Canonical & Robots -->
    <link rel="canonical" href="{{ $seoCanonical }}">
    @if ($noindex ?? false)
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="@yield('robots', $seoRobots)">
    @endif
    @stack('seo')

    <!-- Google Analytics (GA4) -->
    @if (!empty($seoGaId))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoGaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ $seoGaId }}');
        </script>
    @endif

    <!-- Custom Head Scripts -->
    @if (!empty($seoCustomHeadScripts))
        {!! $seoCustomHeadScripts !!}
    @endif

    <!-- Fonts (SF Pro Fallback: Inter & JetBrains Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @stack('styles')

    <!-- Tailwind CSS with Apple HIG Design System Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', '"Inter"',
                            'system-ui', 'sans-serif'
                        ],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            'blue-dark': '#0A84FF',
                            green: '#34C759',
                            'green-dark': '#30D158',
                            orange: '#FF9500',
                            'orange-dark': '#FF9F0A',
                            red: '#FF3B30',
                            'red-dark': '#FF453A',
                            purple: '#AF52DE',
                            'purple-dark': '#BF5AF2',
                            teal: '#30B0C7',
                            'teal-dark': '#40C8E0',
                            indigo: '#5856D6',
                            'indigo-dark': '#5E5CE6',
                            yellow: '#FFCC00',
                            'yellow-dark': '#FFD60A',
                        },
                        systemGray: {
                            1: '#8E8E93',
                            2: '#AEAEB2',
                            3: '#C7C7CC',
                            4: '#D1D1D6',
                            5: '#E5E5EA',
                            6: '#F2F2F7',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons & Alpine.js -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --apple-blue: #007AFF;
            --apple-green: #34C759;
            --apple-orange: #FF9500;
            --apple-red: #FF3B30;
            --apple-bg: #F5F5F7;
            --apple-card: #FFFFFF;
            --apple-card-border: rgba(0, 0, 0, 0.06);
            --apple-text: #1D1D1F;
            --apple-text-muted: #6E6E73;
        }

        .dark {
            --apple-blue: #0A84FF;
            --apple-green: #30D158;
            --apple-orange: #FF9F0A;
            --apple-red: #FF453A;
            --apple-bg: #000000;
            --apple-card: #1C1C1E;
            --apple-card-border: rgba(255, 255, 255, 0.08);
            --apple-text: #F5F5F7;
            --apple-text-muted: #86868B;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: var(--apple-bg);
            color: var(--apple-text);
        }

        /* Apple Inset Glass Card (Squircle) */
        .glass-card {
            background-color: var(--apple-card);
            border: 1px solid var(--apple-card-border);
            border-radius: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .glass-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            border-color: rgba(0, 122, 255, 0.25);
        }

        /* Apple System Blue Filled Button */
        .glow-btn {
            background-color: #007AFF;
            color: #FFFFFF;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(0, 122, 255, 0.25);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dark .glow-btn {
            background-color: #0A84FF;
            box-shadow: 0 1px 2px rgba(10, 132, 255, 0.3);
        }

        .glow-btn:hover {
            background-color: #0071E3;
            box-shadow: 0 4px 14px rgba(0, 122, 255, 0.35);
            transform: translateY(-1px);
        }

        .dark .glow-btn:hover {
            background-color: #0077ED;
            box-shadow: 0 4px 14px rgba(10, 132, 255, 0.4);
        }

        .glow-btn:active {
            transform: scale(0.98);
            opacity: 0.9;
        }

        /* Apple Clean Text Accent (NO PURPLE) */
        .text-gradient-accent {
            color: #007AFF;
        }

        .dark .text-gradient-accent {
            color: #0A84FF;
        }

        /* COOCA Apple HIG Solid Header Dropdown */
        .cooca-header-dropdown {
            background-color: #0B132B !important;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.95), 0 0 0 1px rgba(0, 194, 255, 0.15) !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- Universal Typography Hierarchy (H1 - H6 & Typographic Roles) --}}
    @include('layouts.partials.typography')

    @stack('seo')
</head>

<body
    class="min-h-screen flex flex-col justify-between bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased transition-colors duration-200"
    x-data="{
        mobileMenu: false,
        platformDropdown: false,
        solutionDropdown: false,
        omniDropdown: false,
        resourceDropdown: false,
        marketplaceDropdown: false,
        isDark: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.isDark = !this.isDark;
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('cooca-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('cooca-theme', 'light');
            }
        }
    }" x-init="lucide.createIcons()">

    @php
        $isLandingPage = request()->routeIs('landing') || request()->is('/');
    @endphp

    <!-- ═══ APPLE FROSTED GLASS HEADER ═══ -->
    @if (!($hideHeader ?? false))
        <header
            class="sticky top-0 z-50 backdrop-blur-2xl bg-[#060913]/95 text-white border-b border-white/10 transition-colors">
            <div class="max-w-[1150px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">

                <!-- Logo Cooca -->
                <a href="{{ route('landing') }}" class="flex items-center gap-3 group shrink-0">
                    @if (!empty($siteLogoDarkUrl))
                        <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteAppName }}"
                            class="h-7 sm:h-8 xl:h-9 w-auto object-contain transition-transform group-hover:scale-105">
                    @else
                        <span
                            class="font-black text-xl sm:text-2xl xl:text-3xl tracking-tighter text-white font-sans">COOCA</span>
                    @endif
                </a>

                <!-- Desktop Nav Menu (Apple HIG Navigation Bar Style) -->
                <nav class="hidden lg:flex items-center gap-1.5 xl:gap-2.5 text-sm font-large text-slate-300">

                    <!-- 1. Platform (Mega Dropdown) -->
                    <div class="relative"
                        @mouseenter="solutionDropdown = false; omniDropdown = false; resourceDropdown = false; marketplaceDropdown = false; platformDropdown = true"
                        @mouseleave="platformDropdown = false">
                        <button
                            class="flex items-center gap-1 px-3 py-2 rounded-[10px] hover:text-white hover:bg-white/5 transition-all focus:outline-none {{ request()->routeIs('public.bos.*') || request()->routeIs('public.erp.*') || request()->routeIs('public.content.*') ? 'text-[#00C2FF] font-semibold' : '' }}">
                            <span>Platform</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform"
                                :class="platformDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="platformDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" style="background-color: #0B132B;"
                            class="cooca-header-dropdown absolute top-full left-0 mt-2 w-[520px] p-4 rounded-[20px] shadow-2xl z-50 grid grid-cols-2 gap-3 text-white">

                            <!-- Col 1: Business Operating System -->
                            <div class="space-y-1">
                                <span
                                    class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#00C2FF] flex items-center gap-1.5">
                                    <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                                    <span>Operating System</span>
                                </span>
                                <a href="{{ route('public.bos.overview') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#00C2FF] transition-colors block">Overview</span>
                                    <span class="text-[11px] text-slate-400 block">Pusat kendali bisnis</span>
                                </a>
                                <a href="{{ route('public.bos.how-it-works') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#00C2FF] transition-colors block">How
                                        It Works</span>
                                    <span class="text-[11px] text-slate-400 block">Alur otomatisasi</span>
                                </a>
                                <a href="{{ route('public.bos.why-cooca') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#00C2FF] transition-colors block">Why
                                        COOCA</span>
                                    <span class="text-[11px] text-slate-400 block">Keunggulan sistem</span>
                                </a>
                            </div>

                            <!-- Col 2: Omnichannel ERP Core -->
                            <div class="space-y-1">
                                <span
                                    class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-[#34C759] flex items-center gap-1.5">
                                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                    <span>Omnichannel ERP</span>
                                </span>
                                <a href="{{ route('public.erp.erp') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#34C759] transition-colors block">ERP
                                        Core</span>
                                    <span class="text-[11px] text-slate-400 block">Multi-cabang &amp; PO</span>
                                </a>
                                <a href="{{ route('public.erp.pos') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#34C759] transition-colors block">POS
                                        Kasir</span>
                                    <span class="text-[11px] text-slate-400 block">Transaksi kilat &amp; QRIS</span>
                                </a>
                                <a href="{{ route('public.erp.inventory') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#34C759] transition-colors block">Inventory</span>
                                    <span class="text-[11px] text-slate-400 block">Stok real-time &amp; HPP</span>
                                </a>
                                <a href="{{ route('public.erp.accounting') }}"
                                    class="group block px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                    <span
                                        class="font-semibold text-xs text-white group-hover:text-[#34C759] transition-colors block">Accounting</span>
                                    <span class="text-[11px] text-slate-400 block">Jurnal otomatis</span>
                                </a>
                            </div>

                            <div
                                class="col-span-2 border-t border-white/10 pt-2.5 mt-1 flex items-center justify-between text-xs px-2.5">
                                <a href="{{ route('public.content.creation') }}"
                                    class="text-[#00C2FF] hover:text-[#38BDF8] font-semibold flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    <span>Content Automation AI →</span>
                                </a>
                                <a href="{{ route('public.erp.analytics') }}"
                                    class="text-slate-400 hover:text-white flex items-center gap-1 transition-colors">
                                    <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                                    <span>Analytics &amp; AI</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Solutions Dropdown -->
                    <div class="relative"
                        @mouseenter="platformDropdown = false; omniDropdown = false; resourceDropdown = false; marketplaceDropdown = false; solutionDropdown = true"
                        @mouseleave="solutionDropdown = false">
                        <button
                            class="flex items-center gap-1 px-3 py-2 rounded-[10px] hover:text-white hover:bg-white/5 transition-all focus:outline-none {{ request()->routeIs('public.solutions.*') || request()->routeIs('solusi.*') ? 'text-[#00C2FF] font-semibold' : '' }}">
                            <span>Solutions</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform"
                                :class="solutionDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="solutionDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            style="background-color: #0B132B;"
                            class="cooca-header-dropdown absolute top-full left-0 mt-2 w-64 p-2 rounded-[18px] shadow-2xl space-y-0.5 z-50 text-white">
                            <a href="{{ route('public.solutions.fnb') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#FF9500]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="utensils" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#FF9500] transition-colors block">F&amp;B
                                        &amp; Resto</span>
                                    <span class="text-[10px] text-slate-400 block">Meja, menu, dapur</span>
                                </div>
                            </a>
                            <a href="{{ route('public.solutions.retail') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#00C2FF]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="store" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#00C2FF] transition-colors block">Retail
                                        &amp; Toko</span>
                                    <span class="text-[10px] text-slate-400 block">Barcode &amp; multi-cabang</span>
                                </div>
                            </a>
                            <a href="{{ route('public.solutions.workshop') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#FF3B30]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="wrench" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#FF3B30] transition-colors block">Bengkel
                                        &amp; Otomotif</span>
                                    <span class="text-[10px] text-slate-400 block">SPK &amp; riwayat servis</span>
                                </div>
                            </a>
                            <a href="{{ route('public.solutions.laundry') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#34C759] transition-colors block">Laundry</span>
                                    <span class="text-[10px] text-slate-400 block">Kiloan, satuan, rak</span>
                                </div>
                            </a>
                            <a href="{{ route('public.solutions.manufacturing') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#AF52DE]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="factory" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#AF52DE] transition-colors block">Manufacturing</span>
                                    <span class="text-[10px] text-slate-400 block">BOM &amp; produksi</span>
                                </div>
                            </a>
                            <a href="{{ route('public.solutions.services') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#00C2FF]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#00C2FF] transition-colors block">Services
                                        &amp; Jasa</span>
                                    <span class="text-[10px] text-slate-400 block">Booking &amp; invoicing</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Omnichannel Dropdown -->
                    <div class="relative"
                        @mouseenter="platformDropdown = false; solutionDropdown = false; resourceDropdown = false; marketplaceDropdown = false; omniDropdown = true"
                        @mouseleave="omniDropdown = false">
                        <button
                            class="flex items-center gap-1 px-3 py-2 rounded-[10px] hover:text-white hover:bg-white/5 transition-all focus:outline-none {{ request()->routeIs('public.omnichannel.*') ? 'text-[#00C2FF] font-semibold' : '' }}">
                            <span>Omnichannel</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform"
                                :class="omniDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="omniDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            style="background-color: #0B132B;"
                            class="cooca-header-dropdown absolute top-full left-0 mt-2 w-64 p-2 rounded-[18px] shadow-2xl space-y-0.5 z-50 text-white">
                            <a href="{{ route('public.omnichannel.social-media') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#FF2D55]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="share-2" class="w-3.5 h-3.5 text-[#FF2D55]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#FF2D55] transition-colors block">Social
                                        Media</span>
                                    <span class="text-[10px] text-slate-400 block">Jadwal &amp; multi-channel</span>
                                </div>
                            </a>
                            <a href="{{ route('public.omnichannel.whatsapp') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#34C759] transition-colors block">WhatsApp</span>
                                    <span class="text-[10px] text-slate-400 block">Broadcast &amp; multi-agent</span>
                                </div>
                            </a>
                            <a href="{{ route('public.omnichannel.marketplace') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#FF9500]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#FF9500] transition-colors block">Marketplace
                                        Hub</span>
                                    <span class="text-[10px] text-slate-400 block">Sinkronisasi stok</span>
                                </div>
                            </a>
                            <a href="{{ route('public.omnichannel.orders') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#00C2FF]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#00C2FF] transition-colors block">Central
                                        Orders</span>
                                    <span class="text-[10px] text-slate-400 block">Satu inbox pesanan</span>
                                </div>
                            </a>
                            <a href="{{ route('public.omnichannel.customer') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#5856D6]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#5856D6] transition-colors block">Customer
                                        Portal</span>
                                    <span class="text-[10px] text-slate-400 block">Loyalty &amp; poin</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Resources Dropdown -->
                    <div class="relative"
                        @mouseenter="platformDropdown = false; solutionDropdown = false; omniDropdown = false; marketplaceDropdown = false; resourceDropdown = true"
                        @mouseleave="resourceDropdown = false">
                        <button
                            class="flex items-center gap-1 px-3 py-2 rounded-[10px] hover:text-white hover:bg-white/5 transition-all focus:outline-none {{ request()->routeIs('public.resources.*') || request()->routeIs('blog.*') ? 'text-[#00C2FF] font-semibold' : '' }}">
                            <span>Resources</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform"
                                :class="resourceDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="resourceDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            style="background-color: #0B132B;"
                            class="cooca-header-dropdown absolute top-full left-0 mt-2 w-60 p-2 rounded-[18px] shadow-2xl space-y-0.5 z-50 text-white">
                            <a href="{{ route('blog.index') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#00C2FF]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#00C2FF] transition-colors block">Blog</span>
                                    <span class="text-[10px] text-slate-400 block">Artikel &amp; tips bisnis</span>
                                </div>
                            </a>
                            <a href="{{ route('public.resources.guides') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#34C759] transition-colors block">Guides</span>
                                    <span class="text-[10px] text-slate-400 block">Panduan operasional</span>
                                </div>
                            </a>
                            <a href="{{ route('public.resources.case-studies') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#FF9500]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="award" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#FF9500] transition-colors block">Case
                                        Studies</span>
                                    <span class="text-[10px] text-slate-400 block">Cerita sukses UMKM</span>
                                </div>
                            </a>
                            <a href="{{ route('public.resources.faq') }}"
                                class="group flex items-center gap-3 px-3 py-2 rounded-[12px] hover:bg-white/[0.08] border border-transparent hover:border-white/10 transition-all">
                                <div
                                    class="w-7 h-7 rounded-[9px] bg-[#AF52DE]/15 flex items-center justify-center shrink-0">
                                    <i data-lucide="help-circle" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                </div>
                                <div>
                                    <span
                                        class="text-xs font-semibold text-white group-hover:text-[#AF52DE] transition-colors block">FAQ</span>
                                    <span class="text-[10px] text-slate-400 block">Pertanyaan umum</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Pricing (Direct Link) -->
                    <a href="{{ route('public.pricing') }}"
                        class="px-3 py-2 rounded-[10px] hover:text-white hover:bg-white/5 transition-all {{ request()->routeIs('public.pricing') ? 'text-[#00C2FF] font-semibold' : '' }}">Pricing</a>
                </nav>

                <!-- Action Controls & Theme Toggle -->
                <div class="flex items-center gap-2">
                    <!-- Apple Theme Switcher Button -->
                    <button type="button" @click="toggleTheme()"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 active:scale-[0.95] transition-all"
                        title="Ganti Mode Terang/Gelap" aria-label="Toggle Theme">
                        <!-- Sun Icon for Dark Mode (Switch to Light) -->
                        <svg x-show="isDark" x-cloak class="w-4 h-4 text-[#FFD60A]" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <!-- Moon Icon for Light Mode (Switch to Dark) -->
                        <svg x-show="!isDark" class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <div class="hidden sm:flex items-center gap-3">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-5 py-2 rounded-full bg-[#00C2FF] hover:bg-[#00A3D7] text-slate-950 font-bold text-xs flex items-center gap-2 shadow-[0_0_20px_rgba(0,194,255,0.4)] min-h-[36px] transition-all">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        @elseif (auth('web')->check())
                            <a href="{{ route('dashboard') }}"
                                class="px-5 py-2 rounded-full bg-[#00C2FF] hover:bg-[#00A3D7] text-slate-950 font-bold text-xs flex items-center gap-2 shadow-[0_0_20px_rgba(0,194,255,0.4)] min-h-[36px] transition-all">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                <span>Ke Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white transition-all min-h-[36px] flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                <span>Login</span>
                            </a>
                            <a href="{{ route('register') }}"
                                class="bg-[#00C2FF] hover:bg-[#00A3D7] text-slate-950 font-bold px-5 py-2 rounded-full text-xs transition-all shadow-[0_0_20px_rgba(0,194,255,0.4)] hover:shadow-[0_0_25px_rgba(0,194,255,0.6)] min-h-[36px] flex items-center">
                                <span>Coba COOCA Gratis</span>
                            </a>
                        @endif
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <button @click="mobileMenu = !mobileMenu"
                        class="lg:hidden p-2 rounded-[12px] bg-white/10 text-white hover:bg-white/15 transition-colors min-w-[40px] min-h-[40px] flex items-center justify-center"
                        aria-label="Open Mobile Navigation">
                        <i :data-lucide="mobileMenu ? 'x' : 'menu'" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer Menu (Apple Control Center Bento Sheet) -->
            <div x-show="mobileMenu" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                style="background-color: #060913;"
                class="lg:hidden p-4 sm:p-5 bg-[#060913] border-b border-white/10 space-y-3.5 text-sm font-medium max-h-[85vh] overflow-y-auto text-slate-200">
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('landing') }}" @click="mobileMenu = false"
                        class="block py-2.5 px-3 rounded-[14px] bg-white/[0.04] border border-white/10 text-white font-semibold hover:bg-white/[0.08] transition-colors">
                        <span class="flex items-center gap-2 text-xs">
                            <i data-lucide="home" class="w-4 h-4 text-[#00C2FF]"></i>
                            <span>Beranda</span>
                        </span>
                    </a>
                    <a href="{{ route('marketplace.index') }}" @click="mobileMenu = false"
                        class="block py-2.5 px-3 rounded-[14px] bg-white/[0.04] border border-white/10 text-white font-semibold hover:bg-white/[0.08] transition-colors">
                        <span class="flex items-center gap-2 text-xs">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-[#FF9500]"></i>
                            <span>Marketplace</span>
                        </span>
                    </a>
                    <a href="{{ route('public.discovery.index') }}" @click="mobileMenu = false"
                        class="block py-2.5 px-3 rounded-[14px] bg-white/[0.04] border border-white/10 text-white font-semibold hover:bg-white/[0.08] transition-colors">
                        <span class="flex items-center gap-2 text-xs">
                            <i data-lucide="compass" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Jelajah Toko</span>
                        </span>
                    </a>
                </div>

                <!-- Bento Tile Section: Platform & ERP -->
                <div class="py-2 border-t border-white/10">
                    <div
                        class="text-[11px] font-bold text-[#00C2FF] uppercase tracking-wider px-1 mb-2 flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>Platform &amp; Omnichannel ERP</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('public.bos.overview') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="cpu" class="w-4 h-4 text-[#00C2FF] shrink-0"></i>
                            <span class="truncate font-medium">Overview BOS</span>
                        </a>
                        <a href="{{ route('public.erp.erp') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="box" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                            <span class="truncate font-medium">ERP Core</span>
                        </a>
                        <a href="{{ route('public.erp.pos') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                            <span class="truncate font-medium">POS Kasir</span>
                        </a>
                        <a href="{{ route('public.erp.inventory') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="archive" class="w-4 h-4 text-[#00C2FF] shrink-0"></i>
                            <span class="truncate font-medium">Inventory</span>
                        </a>
                        <a href="{{ route('public.omnichannel.whatsapp') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="message-square" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                            <span class="truncate font-medium">WhatsApp Hub</span>
                        </a>
                        <a href="{{ route('public.content.creation') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-cyan-950/60 border border-cyan-400/30 flex items-center gap-2 text-cyan-300 font-semibold active:scale-95 transition-all">
                            <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i>
                            <span class="truncate font-semibold">Content AI</span>
                        </a>
                    </div>
                </div>

                <!-- Bento Tile Section: Solusi Industri -->
                <div class="py-2 border-t border-white/10">
                    <div
                        class="text-[11px] font-bold text-[#34C759] uppercase tracking-wider px-1 mb-2 flex items-center gap-1.5">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        <span>Solusi Industri</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('public.solutions.fnb') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="utensils" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                            <span class="truncate font-medium">F&amp;B Resto</span>
                        </a>
                        <a href="{{ route('public.solutions.retail') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="store" class="w-4 h-4 text-[#00C2FF] shrink-0"></i>
                            <span class="truncate font-medium">Retail Toko</span>
                        </a>
                        <a href="{{ route('public.solutions.workshop') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="wrench" class="w-4 h-4 text-[#FF3B30] shrink-0"></i>
                            <span class="truncate font-medium">Bengkel</span>
                        </a>
                        <a href="{{ route('public.solutions.laundry') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                            <span class="truncate font-medium">Laundry</span>
                        </a>
                        <a href="{{ route('public.solutions.manufacturing') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="factory" class="w-4 h-4 text-[#AF52DE] shrink-0"></i>
                            <span class="truncate font-medium">Pabrik</span>
                        </a>
                        <a href="{{ route('public.solutions.services') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center gap-2 text-slate-200 hover:text-white hover:bg-white/[0.08] active:scale-95 transition-all">
                            <i data-lucide="briefcase" class="w-4 h-4 text-[#00C2FF] shrink-0"></i>
                            <span class="truncate font-medium">Services Jasa</span>
                        </a>
                    </div>
                </div>

                <!-- Inset Links -->
                <div class="py-2 border-t border-white/10 space-y-1 text-xs">
                    <a href="{{ route('public.pricing') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        <i data-lucide="tag" class="w-4 h-4 text-[#00C2FF]"></i>
                        <span>Harga &amp; Paket (Pricing)</span>
                    </a>
                    <a href="{{ route('public.demo') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        <i data-lucide="play" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Live Demo Sistem</span>
                    </a>
                    <a href="{{ route('blog.index') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        <i data-lucide="book-open" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>Blog &amp; Edukasi UMKM</span>
                    </a>
                    <a href="{{ route('public.support') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                        <i data-lucide="headphones" class="w-4 h-4 text-[#AF52DE]"></i>
                        <span>Bantuan &amp; Support</span>
                    </a>
                </div>

                @if (auth('admin')->check())
                    <div class="pt-3 border-t border-white/10">
                        <a href="{{ route('admin.dashboard') }}" @click="mobileMenu = false"
                            class="w-full py-3 rounded-[14px] bg-[#00C2FF] hover:bg-[#00A3D7] text-slate-950 font-bold text-center flex items-center justify-center gap-2 active:scale-95 transition-all shadow-[0_0_15px_rgba(0,194,255,0.35)]">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    </div>
                @elseif (auth('web')->check())
                    <div class="pt-3 border-t border-white/10">
                        <a href="{{ route('dashboard') }}" @click="mobileMenu = false"
                            class="w-full py-3 rounded-[14px] bg-[#00C2FF] hover:bg-[#00A3D7] text-slate-950 font-bold text-center flex items-center justify-center gap-2 active:scale-95 transition-all shadow-[0_0_15px_rgba(0,194,255,0.35)]">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Ke Dashboard</span>
                        </a>
                    </div>
                @else
                    <div class="pt-3 border-t border-white/10 grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}"
                            class="py-2.5 rounded-full bg-white/10 text-center text-white font-semibold text-xs active:scale-95 hover:bg-white/15 transition-all flex items-center justify-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            <span>Login</span>
                        </a>
                        <a href="{{ route('register') }}"
                            class="py-2.5 rounded-full bg-[#00C2FF] hover:bg-[#00A3D7] text-center text-slate-950 font-bold text-xs shadow-[0_0_15px_rgba(0,194,255,0.35)] active:scale-95 transition-all flex items-center justify-center">
                            <span>Coba COOCA Gratis</span>
                        </a>
                    </div>
                @endif
            </div>
        </header>
    @endif

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="flex-grow pb-16 lg:pb-0">
        @yield('content')
    </main>

    <!-- ═══ APPLE GROUPED INSET FOOTER ═══ -->
    @if (!($hideFooter ?? false))
        <footer
            class="border-t border-white/10 bg-[#060913] text-slate-400 mt-0 pt-16 pb-24 text-xs sm:text-sm transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 pb-10">

                    <!-- Col 1: Brand & Contact (Full width on mobile/tablet, 2 cols on desktop) -->
                    <div class="space-y-4 lg:col-span-2">
                        @php
                            $footerWaUrl = \App\Models\SystemSetting::get(
                                'social_whatsapp_url',
                                'https://wa.me/6285287864176',
                            );
                            $footerWaNum = \App\Models\SystemSetting::get('social_whatsapp_number', '0852 8786 4176');
                            $footerSocialChannels = [
                                'facebook' => [
                                    'active' => filter_var(
                                        \App\Models\SystemSetting::get('social_facebook_active', '1'),
                                        FILTER_VALIDATE_BOOLEAN,
                                    ),
                                    'url' => \App\Models\SystemSetting::get(
                                        'social_facebook_url',
                                        'https://facebook.com/cooca.id',
                                    ),
                                    'name' => 'Facebook',
                                    'handle' => \App\Models\SystemSetting::get(
                                        'social_facebook_name',
                                        'Cooca Indonesia',
                                    ),
                                    'svg' =>
                                        '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
                                    'hover' => 'hover:bg-[#1877F2]/15 hover:text-[#1877F2]',
                                ],
                                'instagram' => [
                                    'active' => filter_var(
                                        \App\Models\SystemSetting::get('social_instagram_active', '1'),
                                        FILTER_VALIDATE_BOOLEAN,
                                    ),
                                    'url' => \App\Models\SystemSetting::get(
                                        'social_instagram_url',
                                        'https://instagram.com/cooca.indonesia',
                                    ),
                                    'name' => 'Instagram',
                                    'handle' => \App\Models\SystemSetting::get(
                                        'social_instagram_handle',
                                        '@cooca.indonesia',
                                    ),
                                    'svg' =>
                                        '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
                                    'hover' => 'hover:bg-[#DD2A7B]/15 hover:text-[#DD2A7B]',
                                ],
                                'tiktok' => [
                                    'active' => filter_var(
                                        \App\Models\SystemSetting::get('social_tiktok_active', '1'),
                                        FILTER_VALIDATE_BOOLEAN,
                                    ),
                                    'url' => \App\Models\SystemSetting::get(
                                        'social_tiktok_url',
                                        'https://tiktok.com/@cooca.id',
                                    ),
                                    'name' => 'TikTok',
                                    'handle' => \App\Models\SystemSetting::get('social_tiktok_handle', '@cooca.id'),
                                    'svg' =>
                                        '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>',
                                    'hover' => 'hover:bg-white/15 hover:text-white',
                                ],
                                'linkedin' => [
                                    'active' => filter_var(
                                        \App\Models\SystemSetting::get('social_linkedin_active', '1'),
                                        FILTER_VALIDATE_BOOLEAN,
                                    ),
                                    'url' => \App\Models\SystemSetting::get(
                                        'social_linkedin_url',
                                        'https://linkedin.com/company/cooca',
                                    ),
                                    'name' => 'LinkedIn',
                                    'handle' => \App\Models\SystemSetting::get(
                                        'social_linkedin_name',
                                        'Cooca Indonesia',
                                    ),
                                    'svg' =>
                                        '<path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>',
                                    'hover' => 'hover:bg-[#0A66C2]/15 hover:text-[#0A66C2]',
                                ],
                                'twitter' => [
                                    'active' => filter_var(
                                        \App\Models\SystemSetting::get('social_twitter_active', '1'),
                                        FILTER_VALIDATE_BOOLEAN,
                                    ),
                                    'url' => \App\Models\SystemSetting::get(
                                        'social_twitter_url',
                                        'https://x.com/cooca_id',
                                    ),
                                    'name' => 'X (Twitter)',
                                    'handle' => \App\Models\SystemSetting::get('social_twitter_handle', '@cooca_id'),
                                    'svg' =>
                                        '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
                                    'hover' => 'hover:bg-white/15 hover:text-white',
                                ],
                            ];
                        @endphp

                        <div class="space-y-3">
                            <a href="{{ route('landing') }}" class="inline-block">
                                @if (!empty($siteLogoDarkUrl))
                                    <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteAppName }}"
                                        class="h-8 w-auto object-contain">
                                @else
                                    <span class="font-black text-2xl tracking-tight text-white font-sans">COOCA</span>
                                @endif
                            </a>
                            <p class="text-xs font-semibold text-slate-300">
                                Business Operating System &amp; Omnichannel ERP
                            </p>
                            <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                                Satu ekosistem untuk mengelola, menghubungkan, menghasilkan, dan mengembangkan bisnis
                                Anda.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @foreach ($footerSocialChannels as $key => $soc)
                                    @if ($soc['active'] && !empty($soc['url']))
                                        <a href="{{ $soc['url'] }}" target="_blank" rel="noopener noreferrer"
                                            title="{{ $soc['name'] }}: {{ $soc['handle'] }}"
                                            class="w-8 h-8 rounded-full bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/15 flex items-center justify-center active:scale-95 transition-all">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                                {!! $soc['svg'] !!}
                                            </svg>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Columns: 4 Columns (Platform, Solutions, Resources, Company) -->
                    <div class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-6 sm:gap-6 pt-4 lg:pt-0 border-t-0">

                        <!-- Col 1: Platform -->
                        <div class="space-y-3">
                            <p class="font-bold text-white uppercase text-[11px] tracking-wider">
                                Platform</p>
                            <nav class="flex flex-col space-y-2 text-xs text-slate-400">
                                <a href="{{ route('public.erp.erp') }}"
                                    class="hover:text-white transition-colors">ERP</a>
                                <a href="{{ route('public.omnichannel.whatsapp') }}"
                                    class="hover:text-white transition-colors">Omnichannel</a>
                                <a href="{{ route('public.content.creation') }}"
                                    class="hover:text-white transition-colors">Content Automation</a>
                                <a href="{{ route('marketplace.index') }}"
                                    class="hover:text-white transition-colors">Marketplace</a>
                                <a href="{{ route('public.erp.analytics') }}"
                                    class="hover:text-white transition-colors">AI Assistant</a>
                            </nav>
                        </div>

                        <!-- Col 2: Solutions -->
                        <div class="space-y-3">
                            <p class="font-bold text-white uppercase text-[11px] tracking-wider">
                                Solutions</p>
                            <nav class="flex flex-col space-y-2 text-xs text-slate-400">
                                <a href="{{ route('public.solutions.fnb') }}"
                                    class="hover:text-white transition-colors">POS</a>
                                <a href="{{ route('public.solutions.retail') }}"
                                    class="hover:text-white transition-colors">Retail</a>
                                <a href="{{ route('public.solutions.workshop') }}"
                                    class="hover:text-white transition-colors">Workshop</a>
                                <a href="{{ route('public.solutions.laundry') }}"
                                    class="hover:text-white transition-colors">Laundry</a>
                                <a href="{{ route('public.solutions.manufacturing') }}"
                                    class="hover:text-white transition-colors">Manufacturing</a>
                                <a href="{{ route('public.solutions.services') }}"
                                    class="hover:text-white transition-colors">Services</a>
                            </nav>
                        </div>

                        <!-- Col 3: Resources -->
                        <div class="space-y-3">
                            <p class="font-bold text-white uppercase text-[11px] tracking-wider">
                                Resources</p>
                            <nav class="flex flex-col space-y-2 text-xs text-slate-400">
                                <a href="{{ route('blog.index') }}"
                                    class="hover:text-white transition-colors">Blog</a>
                                <a href="{{ route('public.resources.guides') }}"
                                    class="hover:text-white transition-colors">Guides</a>
                                <a href="{{ route('public.resources.case-studies') }}"
                                    class="hover:text-white transition-colors">Case Studies</a>
                                <a href="{{ route('public.demo') }}"
                                    class="hover:text-white transition-colors">Webinar</a>
                                <a href="{{ route('public.resources.faq') }}"
                                    class="hover:text-white transition-colors">FAQ</a>
                            </nav>
                        </div>

                        <!-- Col 4: Company -->
                        <div class="space-y-3">
                            <p class="font-bold text-white uppercase text-[11px] tracking-wider">
                                Company</p>
                            <nav class="flex flex-col space-y-2 text-xs text-slate-400">
                                <a href="{{ route('public.about') }}"
                                    class="hover:text-white transition-colors">About Us</a>
                                <a href="{{ route('public.privacy') }}"
                                    class="hover:text-white transition-colors">Security</a>
                                <a href="{{ route('public.privacy') }}"
                                    class="hover:text-white transition-colors">Privacy</a>
                                <a href="{{ route('public.terms') }}"
                                    class="hover:text-white transition-colors">Terms &amp; Conditions</a>
                                <a href="{{ route('public.support') }}"
                                    class="hover:text-white transition-colors">Support</a>
                            </nav>
                        </div>

                    </div>
                </div>

                <!-- Bottom Copyright & Links -->
                <div
                    class="border-t border-white/10 text-slate-500 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px]">
                    <p class="text-center sm:text-left">© 2026 COOCA. All rights reserved.</p>
                    <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-4 gap-y-2">
                        <a href="{{ route('public.privacy') }}"
                            class="text-slate-400 hover:text-white transition-colors">Privacy</a>
                        <a href="{{ route('public.terms') }}"
                            class="text-slate-400 hover:text-white transition-colors">Terms</a>
                        <a href="{{ route('public.privacy') }}"
                            class="text-slate-400 hover:text-white transition-colors">Security</a>
                        <a href="{{ route('public.support') }}"
                            class="text-slate-400 hover:text-white transition-colors">Support</a>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    @if (!($hideBottomNav ?? false))
        <!-- ═══ APPLE FLOATING DOCK BOTTOM NAVBAR (MOBILE ONLY) ═══ -->
        <nav class="fixed bottom-0 inset-x-0 z-40 pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))] px-4 sm:px-6 lg:px-8 pointer-events-none lg:hidden"
            aria-label="Navigasi Bawah">
            <div class="max-w-[1250px] mx-auto pointer-events-auto">
                <div
                    class="w-full bg-[#060913]/90 backdrop-blur-2xl border border-white/10 rounded-[24px] sm:rounded-[28px] px-2 sm:px-6 lg:px-8 py-2 sm:py-2.5 shadow-[0_12px_40px_-6px_rgba(0,0,0,0.5)] transition-all duration-300">
                    <div class="grid grid-cols-5 items-center w-full">

                        <!-- 1. Beranda -->
                        @php
                            $isHomeActive = request()->routeIs('landing') || request()->is('/');
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center">
                            <a href="{{ route('landing') }}"
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isHomeActive ? 'text-[#00C2FF]' : 'text-slate-400 hover:text-white' }}">
                                <div
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isHomeActive ? 'bg-[#00C2FF]/15 shadow-sm' : 'group-hover:bg-white/5' }}">
                                    <i data-lucide="home"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isHomeActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                </div>
                                <span
                                    class="text-xs font-semibold tracking-tight mt-0.5 {{ $isHomeActive ? 'text-[#00C2FF]' : '' }}">Beranda</span>
                                @if ($isHomeActive)
                                    <span class="w-1 h-1 rounded-full bg-[#00C2FF] mt-0.5 opacity-60"></span>
                                @else
                                    <span class="w-1 h-1 mt-0.5 opacity-0"></span>
                                @endif
                            </a>
                        </div>

                        <!-- 2. Platform -->
                        @php
                            $isPlatformActive =
                                request()->routeIs('public.bos.*') || request()->routeIs('public.erp.*');
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center">
                            <a href="{{ route('public.bos.overview') }}"
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isPlatformActive ? 'text-[#00C2FF]' : 'text-slate-400 hover:text-white' }}">
                                <div
                                    class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isPlatformActive ? 'bg-[#00C2FF]/15 shadow-sm' : 'group-hover:bg-white/5' }}">
                                    <i data-lucide="layers"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isPlatformActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                </div>
                                <span
                                    class="text-xs font-semibold tracking-tight mt-0.5 {{ $isPlatformActive ? 'text-[#00C2FF]' : '' }}">Platform</span>
                                @if ($isPlatformActive)
                                    <span class="w-1 h-1 rounded-full bg-[#00C2FF] mt-0.5 opacity-60"></span>
                                @else
                                    <span class="w-1 h-1 mt-0.5 opacity-0"></span>
                                @endif
                            </a>
                        </div>

                        <!-- 3. Solusi POS (Center Elevated Squircle) -->
                        @php
                            $isSolusiActive = request()->routeIs('solusi*');
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center relative -mt-5 sm:-mt-6">
                            <a href="{{ route('solusi.show', 'kasir-warung') }}"
                                class="w-12 h-12 sm:w-13 sm:h-13 rounded-[20px] sm:rounded-[22px] bg-gradient-to-tr from-[#007AFF] via-[#0A84FF] to-[#5856D6] text-white flex items-center justify-center shadow-[0_8px_20px_rgba(0,122,255,0.45)] ring-4 ring-[#060913] active:scale-90 active:shadow-inner transition-all duration-200 group"
                                title="Solusi Bisnis & POS Kasir">
                                <i data-lucide="store"
                                    class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-white transition-transform group-hover:scale-110 group-active:scale-90 stroke-[2.2]"></i>
                            </a>
                            <span
                                class="text-xs font-bold tracking-tight mt-1.5 {{ $isSolusiActive ? 'text-[#00C2FF]' : 'text-slate-300' }}">Solusi
                                POS</span>
                            @if ($isSolusiActive)
                                <span class="w-1 h-1 rounded-full bg-[#00C2FF] mt-0.5 opacity-60"></span>
                            @else
                                <span class="w-1 h-1 mt-0.5 opacity-0"></span>
                            @endif
                        </div>

                        <!-- 4. Template Excel -->
                        @php
                            $isTemplateActive = request()->routeIs('template*');
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center">
                            <a href="{{ route('template.index') }}"
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isTemplateActive ? 'text-[#34C759]' : 'text-slate-400 hover:text-white' }}">
                                <div
                                    class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isTemplateActive ? 'bg-[#34C759]/15 shadow-sm' : 'group-hover:bg-white/5' }}">
                                    <i data-lucide="file-spreadsheet"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isTemplateActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                    <!-- Micro emerald indicator -->
                                    <span
                                        class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-[#34C759] border border-[#060913]"></span>
                                </div>
                                <span
                                    class="text-xs font-semibold tracking-tight mt-0.5 {{ $isTemplateActive ? 'text-[#34C759]' : '' }}">Template</span>
                                @if ($isTemplateActive)
                                    <span class="w-1 h-1 rounded-full bg-[#34C759] mt-0.5 opacity-60"></span>
                                @else
                                    <span class="w-1 h-1 mt-0.5 opacity-0"></span>
                                @endif
                            </a>
                        </div>

                        <!-- 5. Akun / Dashboard -->
                        @php
                            $isAdmin = auth('admin')->check();
                            $isWeb = auth('web')->check();
                            $isAuth = $isAdmin || $isWeb;
                            $isAccountActive =
                                request()->routeIs('login*') ||
                                request()->routeIs('register*') ||
                                request()->routeIs('dashboard*') ||
                                request()->routeIs('admin*');
                            $targetRoute = $isAdmin
                                ? route('admin.dashboard')
                                : ($isWeb
                                    ? route('dashboard')
                                    : route('login'));
                            $tabLabel = $isAdmin ? 'Admin' : ($isWeb ? 'Dashboard' : 'Masuk');
                            $tabIcon = $isAuth ? 'layout-dashboard' : 'user';
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center">
                            <a href="{{ $targetRoute }}"
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isAccountActive ? 'text-[#00C2FF]' : 'text-slate-400 hover:text-white' }}">
                                <div
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isAccountActive ? 'bg-[#00C2FF]/15 shadow-sm' : 'group-hover:bg-white/5' }}">
                                    <i data-lucide="{{ $tabIcon }}"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isAccountActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                </div>
                                <span
                                    class="text-xs font-semibold tracking-tight mt-0.5 {{ $isAccountActive ? 'text-[#00C2FF]' : '' }}">{{ $tabLabel }}</span>
                                @if ($isAccountActive)
                                    <span class="w-1 h-1 rounded-full bg-[#00C2FF] mt-0.5 opacity-60"></span>
                                @else
                                    <span class="w-1 h-1 mt-0.5 opacity-0"></span>
                                @endif
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </nav>
    @endif

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    @php
        $flashSuccess = session()->pull('success');
        $flashError = session()->pull('error');
        $flashWarning = session()->pull('warning');
        $flashStatus = session()->pull('status');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
            @if ($flashSuccess)
                if (window.AppAlert) AppAlert.success(@json($flashSuccess));
            @endif
            @if ($flashError)
                if (window.AppAlert) AppAlert.error(@json($flashError));
            @endif
            @if ($flashWarning)
                if (window.AppAlert) AppAlert.warning(@json($flashWarning));
            @endif
            @if ($flashStatus)
                if (window.AppAlert) AppAlert.info(@json($flashStatus));
            @endif
            @if (isset($errors) && $errors->any())
                if (window.AppAlert) AppAlert.error(@json($errors->first()));
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
