<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

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
            \App\Domain\Storage\AdminStorage::publicUrl($seoOgImageSetting) ?? asset('assets/seo/cooca-og-default.jpg');

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

    @include('public.partials.seo', [
        'fallbackImage' => asset('assets/seo/cooca-og-default.jpg'),
    ])

    <!-- Webmaster Verification -->
    @if (!empty($seoGoogleVerification))
        <meta name="google-site-verification" content="{{ $seoGoogleVerification }}">
    @endif
    @if (!empty($seoBingVerification))
        <meta name="msvalidate.01" content="{{ $seoBingVerification }}">
    @endif

    @php
        $faviconPath = parse_url($siteFaviconUrl, PHP_URL_PATH) ?? '';
        $faviconExt = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
        $siteFaviconType = match ($faviconExt) {
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    @endphp

    <!-- Favicon & Brand Icons (Dynamic from Admin Settings) -->
    <link rel="icon" type="{{ $siteFaviconType }}" href="{{ $siteFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $siteFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $siteFaviconUrl }}">
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

        /* Modern Clean Mega Dropdown (Full Width Attached Below Header Line) */
        .cooca-header-dropdown {
            background-color: #FFFFFF !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9) !important;
            border-radius: 0 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
            color: #0F172A !important;
        }

        .dark .cooca-header-dropdown {
            background-color: #0C1222 !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 0 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85) !important;
            color: #FFFFFF !important;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Floating WhatsApp Button Elevation (Safe clearance above Mobile Floating Bottom Dock) */
        .floating-wa-fab {
            bottom: calc(5.75rem + env(safe-area-inset-bottom, 0px)) !important;
        }
        @media (min-width: 1024px) {
            .floating-wa-fab {
                bottom: 1.5rem !important;
            }
        }
    </style>

    {{-- Universal Typography Hierarchy (H1 - H6 & Typographic Roles) --}}
    @include('layouts.partials.typography')

    @stack('seo')
</head>

<body
    class="min-h-screen flex flex-col justify-between bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased transition-colors duration-200 overflow-x-hidden w-full max-w-full"
    x-data="{
        mobileMenu: false,
        mobileSection: null,
        toggleMobileSection(sec) {
            this.mobileSection = this.mobileSection === sec ? null : sec;
            this.$nextTick(() => { if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons(); });
        },
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
    }" x-init="lucide.createIcons(); $watch('mobileMenu', value => { if (value) { document.body.classList.add('overflow-hidden', 'lg:overflow-auto'); } else { document.body.classList.remove('overflow-hidden', 'lg:overflow-auto'); } })">

    @php
        $isLandingPage = request()->routeIs('landing') || request()->is('/');
    @endphp

    @if (!($hideHeader ?? false))
        <header
            class="sticky top-0 z-50 backdrop-blur-2xl bg-[#060913]/95 text-white border-b border-white/10 relative transition-colors"
            @mouseleave="platformDropdown = false; solutionDropdown = false; omniDropdown = false; resourceDropdown = false"
            @keydown.escape.window="platformDropdown = false; solutionDropdown = false; omniDropdown = false; resourceDropdown = false; mobileMenu = false"
            @resize.window="if (window.innerWidth >= 1024) mobileMenu = false">
            <div
                class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 lg:h-[84px] flex items-center justify-between">

                <!-- Logo Cooca (Perbesar Skala Desktop & Mobile) -->
                <a href="{{ route('landing') }}"
                    @mouseenter="platformDropdown = false; solutionDropdown = false; omniDropdown = false; resourceDropdown = false"
                    class="flex items-center gap-3 sm:gap-3.5 group shrink-0">
                    @if (!empty($siteLogoDarkUrl))
                        <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteAppName }}"
                            class="h-8 sm:h-9 lg:h-10 xl:h-11 w-auto object-contain transition-transform group-hover:scale-105">
                    @else
                        <span
                            class="font-black text-2xl sm:text-3xl xl:text-4xl tracking-tighter text-white font-sans">COOCA</span>
                    @endif
                </a>

                <!-- Desktop Nav Menu (Perbesar Font & Target Sentuh) -->
                <nav
                    class="hidden lg:flex items-center gap-2 xl:gap-3 text-[15px] xl:text-[16px] font-semibold text-slate-200">

                    <!-- 1. Platform Trigger -->
                    <button type="button"
                        @mouseenter="solutionDropdown = false; omniDropdown = false; resourceDropdown = false; marketplaceDropdown = false; platformDropdown = true"
                        @click="platformDropdown = !platformDropdown; solutionDropdown = false; omniDropdown = false; resourceDropdown = false"
                        class="flex items-center gap-1.5 px-3.5 xl:px-4 py-2.5 rounded-[12px] hover:text-white hover:bg-white/10 transition-all focus:outline-none"
                        :class="platformDropdown ? 'bg-white/10 text-white' : (
                            {{ request()->routeIs('public.bos.*') || request()->routeIs('public.erp.*') || request()->routeIs('public.content.*') ? 'true' : 'false' }} ?
                            'text-[#00C2FF] font-semibold' : '')">
                        <span>Platform</span>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 xl:w-4 xl:h-4 transition-transform duration-200"
                            :class="platformDropdown ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- 2. Solutions Trigger -->
                    <button type="button"
                        @mouseenter="platformDropdown = false; omniDropdown = false; resourceDropdown = false; marketplaceDropdown = false; solutionDropdown = true"
                        @click="solutionDropdown = !solutionDropdown; platformDropdown = false; omniDropdown = false; resourceDropdown = false"
                        class="flex items-center gap-1.5 px-3.5 xl:px-4 py-2.5 rounded-[12px] hover:text-white hover:bg-white/10 transition-all focus:outline-none"
                        :class="solutionDropdown ? 'bg-white/10 text-white' : (
                            {{ request()->routeIs('public.solutions.*') || request()->routeIs('solusi.*') ? 'true' : 'false' }} ?
                            'text-[#00C2FF] font-semibold' : '')">
                        <span>Solutions</span>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 xl:w-4 xl:h-4 transition-transform duration-200"
                            :class="solutionDropdown ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- 3. Omnichannel Trigger -->
                    <button type="button"
                        @mouseenter="platformDropdown = false; solutionDropdown = false; resourceDropdown = false; marketplaceDropdown = false; omniDropdown = true"
                        @click="omniDropdown = !omniDropdown; platformDropdown = false; solutionDropdown = false; resourceDropdown = false"
                        class="flex items-center gap-1.5 px-3.5 xl:px-4 py-2.5 rounded-[12px] hover:text-white hover:bg-white/10 transition-all focus:outline-none"
                        :class="omniDropdown ? 'bg-white/10 text-white' : (
                            {{ request()->routeIs('public.omnichannel.*') ? 'true' : 'false' }} ?
                            'text-[#00C2FF] font-semibold' : '')">
                        <span>Omnichannel</span>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 xl:w-4 xl:h-4 transition-transform duration-200"
                            :class="omniDropdown ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- 4. Resources Trigger -->
                    <button type="button"
                        @mouseenter="platformDropdown = false; solutionDropdown = false; omniDropdown = false; marketplaceDropdown = false; resourceDropdown = true"
                        @click="resourceDropdown = !resourceDropdown; platformDropdown = false; solutionDropdown = false; omniDropdown = false"
                        class="flex items-center gap-1.5 px-3.5 xl:px-4 py-2.5 rounded-[12px] hover:text-white hover:bg-white/10 transition-all focus:outline-none"
                        :class="resourceDropdown ? 'bg-white/10 text-white' : (
                            {{ request()->routeIs('public.resources.*') || request()->routeIs('blog.*') ? 'true' : 'false' }} ?
                            'text-[#00C2FF] font-semibold' : '')">
                        <span>Resources</span>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 xl:w-4 xl:h-4 transition-transform duration-200"
                            :class="resourceDropdown ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- 5. Pricing (Direct Link) -->
                    <a href="{{ route('public.pricing') }}"
                        @mouseenter="platformDropdown = false; solutionDropdown = false; omniDropdown = false; resourceDropdown = false"
                        class="px-3.5 xl:px-4 py-2.5 rounded-[12px] hover:text-white hover:bg-white/10 transition-all {{ request()->routeIs('public.pricing') ? 'text-[#00C2FF] font-semibold' : '' }}">Pricing</a>
                </nav>

                <!-- Action Controls & Theme Toggle -->
                <div class="flex items-center gap-2 sm:gap-2.5"
                    @mouseenter="platformDropdown = false; solutionDropdown = false; omniDropdown = false; resourceDropdown = false">
                    <!-- Apple Theme Switcher Button (Desktop & Tablet only) -->
                    <button type="button" @click="toggleTheme()"
                        class="hidden sm:flex w-10 h-10 sm:w-11 sm:h-11 lg:w-10 lg:h-10 rounded-full items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 active:scale-[0.95] transition-all"
                        title="Ganti Mode Terang/Gelap" aria-label="Toggle Theme">
                        <!-- Sun Icon for Dark Mode (Switch to Light) -->
                        <svg x-show="isDark" x-cloak class="w-5 h-5 sm:w-5.5 sm:h-5.5 lg:w-5 lg:h-5 text-[#FFD60A]" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <!-- Moon Icon for Light Mode (Switch to Dark) -->
                        <svg x-show="!isDark" class="w-5 h-5 sm:w-5.5 sm:h-5.5 lg:w-5 lg:h-5 text-slate-300" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <!-- Mobile Quick Action CTA (sm:hidden) -->
                    <div class="sm:hidden flex items-center">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-[10px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white font-bold text-xs shadow-xs active:scale-95 transition-all">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                <span>Admin</span>
                            </a>
                        @elseif (auth('web')->check())
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-[10px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white font-bold text-xs shadow-xs active:scale-95 transition-all">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                <span>Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-[10px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white font-bold text-xs shadow-xs active:scale-95 transition-all">
                                <span>Coba Gratis</span>
                            </a>
                        @endif
                    </div>

                    <!-- Desktop & Tablet Action Buttons (hidden sm:flex) -->
                    <div class="hidden sm:flex items-center gap-3">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}"
                                class="group inline-flex items-center gap-2 px-5.5 py-2.5 rounded-[12px] xl:rounded-[14px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white font-bold text-sm shadow-[0_4px_16px_rgba(0,194,255,0.35),inset_0_1px_0_rgba(255,255,255,0.35)] hover:shadow-[0_6px_24px_rgba(0,194,255,0.55),inset_0_1px_0_rgba(255,255,255,0.5)] hover:scale-[1.02] active:scale-[0.98] min-h-[42px] xl:min-h-[44px] transition-all">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Dashboard</span>
                            </a>
                        @elseif (auth('web')->check())
                            <a href="{{ route('dashboard') }}"
                                class="group inline-flex items-center gap-2 px-5.5 py-2.5 rounded-[12px] xl:rounded-[14px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white font-bold text-sm shadow-[0_4px_16px_rgba(0,194,255,0.35),inset_0_1px_0_rgba(255,255,255,0.35)] hover:shadow-[0_6px_24px_rgba(0,194,255,0.55),inset_0_1px_0_rgba(255,255,255,0.5)] hover:scale-[1.02] active:scale-[0.98] min-h-[42px] xl:min-h-[44px] transition-all">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="group px-3.5 py-2.5 text-sm font-semibold text-slate-200 hover:text-white hover:bg-white/10 rounded-[12px] transition-all min-h-[42px] flex items-center gap-1.5">
                                <i data-lucide="user"
                                    class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors"></i>
                                <span>Login</span>
                            </a>
                            <a href="{{ route('register') }}"
                                class="group relative inline-flex items-center justify-center gap-2 px-6 xl:px-7 py-2.5 rounded-[12px] xl:rounded-[14px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm xl:text-[14.5px] tracking-tight shadow-[0_4px_18px_rgba(0,194,255,0.4),inset_0_1px_0_rgba(255,255,255,0.4)] hover:shadow-[0_6px_28px_rgba(0,194,255,0.65),inset_0_1px_0_rgba(255,255,255,0.6)] hover:scale-[1.02] active:scale-[0.98] min-h-[42px] xl:min-h-[44px] transition-all">
                                <span>Coba COOCA Gratis</span>
                                <i data-lucide="arrow-right"
                                    class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Mobile Hamburger Button (Apple HIG Touch Target & Clean Responsive SVG) -->
                    <button type="button" @click="mobileMenu = !mobileMenu; $nextTick(() => { if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons(); })"
                        class="lg:hidden w-9 h-9 sm:w-10 sm:h-10 rounded-[11px] sm:rounded-[12px] bg-white/10 hover:bg-white/15 text-slate-200 hover:text-white active:scale-95 transition-all flex items-center justify-center shadow-xs focus:outline-none shrink-0 border border-white/10"
                        :class="mobileMenu ? 'bg-white/20 text-[#00C2FF] border-[#00C2FF]/40 ring-2 ring-[#00C2FF]/30' : ''"
                        aria-label="Toggle Mobile Navigation"
                        :aria-expanded="mobileMenu ? 'true' : 'false'">
                        <svg x-show="!mobileMenu" class="w-5 h-5 stroke-current stroke-2" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileMenu" x-cloak class="w-5 h-5 stroke-current stroke-2" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ═══ 1. PLATFORM MEGA DROPDOWN (FULL WIDTH UNDER HEADER LINE) ═══ -->
            <div x-show="platformDropdown" x-cloak @mouseenter="platformDropdown = true"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="cooca-header-dropdown absolute top-full inset-x-0 w-full left-0 right-0 z-50">

                <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-9">
                    <div class="grid grid-cols-12 gap-8 xl:gap-10 items-start">

                        <!-- LEFT 8 COLS: FITUR & PLATFORM -->
                        <div class="col-span-8 grid grid-cols-3 gap-8">

                            <!-- SUB-SECTION 1: FITUR (Span 2 Columns) -->
                            <div class="col-span-2 space-y-4">
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                                    Fitur
                                </p>

                                <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                                    <!-- Point of Sale -->
                                    <a href="{{ route('public.erp.pos') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="monitor" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Point of Sale
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Hubungkan pesanan, pembayaran, dan pembukuan
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Dynamic Budgeting -->
                                    <a href="{{ route('public.erp.accounting') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Dynamic Budgeting
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Atur dan pantau dana secara real-time
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Vendor Spend -->
                                    <a href="{{ route('public.erp.erp') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Vendor Spend
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Kelola pembayaran SaaS &amp; vendor
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Revenue Sync -->
                                    <a href="{{ route('public.erp.inventory') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Revenue Sync
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Tarik data retainer &amp; POS otomatis
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Receipt Capture -->
                                    <a href="{{ route('public.omnichannel.orders') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="receipt" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Receipt Capture
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Otomatiskan urusan struk
                                            </span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <!-- SUB-SECTION 2: PLATFORM (Span 1 Column) -->
                            <div class="col-span-1 space-y-4">
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                                    Platform
                                </p>

                                <div class="space-y-4">
                                    <!-- COOCA AI Agents -->
                                    <a href="{{ route('public.content.creation') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="zap" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                COOCA AI Agents
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Lipat-gandakan efisiensi keuangan
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Siap Global -->
                                    <a href="{{ route('public.bos.overview') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="globe" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Siap Global
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Invoice dalam IDR, USD, dan SGD
                                            </span>
                                        </div>
                                    </a>

                                    <!-- Integrasi Bawaan -->
                                    <a href="{{ route('public.bos.how-it-works') }}"
                                        class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                        <div
                                            class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                            <i data-lucide="code-2" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                                Integrasi Bawaan
                                            </span>
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                                Hubungkan ERP, HRIS &amp; tools
                                            </span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT 4 COLS: RILIS TERBARU (With Vertical Divider) -->
                        <div
                            class="col-span-4 pl-8 border-l border-slate-200 dark:border-white/10 flex flex-col justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-3">
                                    Rilis Terbaru
                                </p>

                                <!-- Showcase Banner Card (Dark Spring Release Card) -->
                                <a href="{{ route('public.omnichannel.whatsapp') }}"
                                    class="group block relative rounded-2xl overflow-hidden bg-[#0A0E1A] border border-slate-800/80 p-5 shadow-sm transition-all hover:scale-[1.01] hover:border-slate-700">
                                    <!-- Ambient Glow -->
                                    <div
                                        class="absolute -right-6 -top-6 w-32 h-32 bg-[#00C2FF]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>
                                    <div
                                        class="absolute -left-6 -bottom-6 w-32 h-32 bg-[#007AFF]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>

                                    <div class="relative z-10">
                                        <div
                                            class="text-2xl sm:text-[28px] font-black text-white tracking-tighter leading-[1.05] uppercase font-sans">
                                            SPRING<br>RELEASE<br><span class="text-slate-300">2026</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Sub-link below banner -->
                                <div class="mt-4">
                                    <a href="{{ route('public.omnichannel.whatsapp') }}"
                                        class="group inline-flex items-center gap-1.5 text-sm font-bold text-slate-900 dark:text-white hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">
                                        <span>WhatsApp AI Agents</span>
                                        <i data-lucide="arrow-right"
                                            class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        Chat langsung dengan ledger Anda untuk menyelesaikan struk hilang, sync vendor,
                                        dan cek budget di mana saja.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ═══ 2. SOLUTIONS MEGA DROPDOWN (FULL WIDTH UNDER HEADER LINE) ═══ -->
            <div x-show="solutionDropdown" x-cloak @mouseenter="solutionDropdown = true"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="cooca-header-dropdown absolute top-full inset-x-0 w-full left-0 right-0 z-50">

                <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-9">
                    <div class="grid grid-cols-12 gap-8 xl:gap-10 items-start">

                        <!-- LEFT 8 COLS: SOLUSI SEKTOR INDUSTRI -->
                        <div class="col-span-8 space-y-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                                Solusi Sektor Industri
                            </p>

                            <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                                <!-- F&B & Resto -->
                                <a href="{{ route('public.solutions.fnb') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#FF9500] group-hover:text-[#FF9500] group-hover:bg-amber-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="utensils" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#FF9500] transition-colors block">
                                            F&amp;B &amp; Resto
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Meja, menu QR, dapur, &amp; split bill
                                        </span>
                                    </div>
                                </a>

                                <!-- Retail & Toko -->
                                <a href="{{ route('public.solutions.retail') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#00C2FF] group-hover:text-[#00C2FF] group-hover:bg-cyan-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="store" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#00C2FF] transition-colors block">
                                            Retail &amp; Toko
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Barcode scanner, varian, &amp; multi-cabang
                                        </span>
                                    </div>
                                </a>

                                <!-- Bengkel & Otomotif -->
                                <a href="{{ route('public.solutions.workshop') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#FF3B30] group-hover:text-[#FF3B30] group-hover:bg-red-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="wrench" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#FF3B30] transition-colors block">
                                            Bengkel &amp; Otomotif
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            SPK, antrean servis, part &amp; mekanik
                                        </span>
                                    </div>
                                </a>

                                <!-- Laundry -->
                                <a href="{{ route('public.solutions.laundry') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#34C759] group-hover:text-[#34C759] group-hover:bg-emerald-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#34C759] transition-colors block">
                                            Laundry Kiloan &amp; Satuan
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Kiloan, satuan, barcode rak &amp; status cuci
                                        </span>
                                    </div>
                                </a>

                                <!-- Manufacturing -->
                                <a href="{{ route('public.solutions.manufacturing') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#AF52DE] group-hover:text-[#AF52DE] group-hover:bg-purple-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="factory" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#AF52DE] transition-colors block">
                                            Manufaktur &amp; Produksi
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            BOM, work order, &amp; alokasi bahan baku
                                        </span>
                                    </div>
                                </a>

                                <!-- Services & Jasa -->
                                <a href="{{ route('public.solutions.services') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] group-hover:text-[#007AFF] group-hover:bg-blue-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors block">
                                            Services &amp; Jasa
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Booking appointment, termin, &amp; invoicing
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- RIGHT 4 COLS: KONSULTASI BISNIS (With Vertical Divider) -->
                        <div
                            class="col-span-4 pl-8 border-l border-slate-200 dark:border-white/10 flex flex-col justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-3">
                                    Konsultasi Solusi
                                </p>

                                <!-- Showcase Banner Card -->
                                <a href="{{ route('public.demo') }}"
                                    class="group block relative rounded-2xl overflow-hidden bg-[#0A0E1A] border border-slate-800/80 p-5 shadow-sm transition-all hover:scale-[1.01] hover:border-slate-700">
                                    <!-- Ambient Glow -->
                                    <div
                                        class="absolute -right-6 -top-6 w-32 h-32 bg-[#FF9500]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>
                                    <div
                                        class="absolute -left-6 -bottom-6 w-32 h-32 bg-[#007AFF]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>

                                    <div class="relative z-10">
                                        <span
                                            class="text-[11px] font-bold uppercase tracking-wider text-[#FF9500] block mb-1">UMKM
                                            Architecture</span>
                                        <div
                                            class="text-xl sm:text-2xl font-black text-white tracking-tighter leading-tight font-sans">
                                            Sistem Khusus Sesuai SOP Industri Anda
                                        </div>
                                    </div>
                                </a>

                                <!-- Sub-link below banner -->
                                <div class="mt-4">
                                    <a href="{{ route('public.demo') }}"
                                        class="group inline-flex items-center gap-1.5 text-sm font-bold text-slate-900 dark:text-white hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">
                                        <span>Jadwalkan Konsultasi Demo</span>
                                        <i data-lucide="arrow-right"
                                            class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        Diskusikan konfigurasi alur kerja, integrasi hardware, dan modul yang sesuai
                                        skala bisnis Anda bersama tim kami.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ═══ 3. OMNICHANNEL MEGA DROPDOWN (FULL WIDTH UNDER HEADER LINE) ═══ -->
            <div x-show="omniDropdown" x-cloak @mouseenter="omniDropdown = true"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="cooca-header-dropdown absolute top-full inset-x-0 w-full left-0 right-0 z-50">

                <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-9">
                    <div class="grid grid-cols-12 gap-8 xl:gap-10 items-start">

                        <!-- LEFT 8 COLS: KANAL PENJUALAN & CRM -->
                        <div class="col-span-8 space-y-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                                Kanal Penjualan &amp; CRM Terpadu
                            </p>

                            <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                                <!-- Social Media -->
                                <a href="{{ route('public.omnichannel.social-media') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#FF2D55] group-hover:text-[#FF2D55] group-hover:bg-pink-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="share-2" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#FF2D55] transition-colors block">
                                            Social Media Commerce
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Jadwal konten, auto-reply, &amp; katalog multi-channel
                                        </span>
                                    </div>
                                </a>

                                <!-- WhatsApp -->
                                <a href="{{ route('public.omnichannel.whatsapp') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#34C759] group-hover:text-[#34C759] group-hover:bg-emerald-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#34C759] transition-colors block">
                                            WhatsApp Official API
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Broadcast pesan massal &amp; multi-agent live chat
                                        </span>
                                    </div>
                                </a>

                                <!-- Marketplace Hub -->
                                <a href="{{ route('public.omnichannel.marketplace') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#FF9500] group-hover:text-[#FF9500] group-hover:bg-amber-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#FF9500] transition-colors block">
                                            Marketplace Hub
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Sinkronisasi stok Shopee, Tokopedia, &amp; TikTok
                                        </span>
                                    </div>
                                </a>

                                <!-- Central Orders -->
                                <a href="{{ route('public.omnichannel.orders') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] group-hover:text-[#007AFF] group-hover:bg-blue-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors block">
                                            Central Orders
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Satu inbox pesanan terpadu untuk semua saluran
                                        </span>
                                    </div>
                                </a>

                                <!-- Customer Portal -->
                                <a href="{{ route('public.omnichannel.customer') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#5856D6] group-hover:text-[#5856D6] group-hover:bg-indigo-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="user-check" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#5856D6] transition-colors block">
                                            Customer Portal &amp; CRM
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Program loyalty, membership tier, &amp; poin belanja
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- RIGHT 4 COLS: SOROTAN OMNICHANNEL (With Vertical Divider) -->
                        <div
                            class="col-span-4 pl-8 border-l border-slate-200 dark:border-white/10 flex flex-col justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-3">
                                    Sorotan Omnichannel
                                </p>

                                <!-- Showcase Banner Card -->
                                <a href="{{ route('public.omnichannel.whatsapp') }}"
                                    class="group block relative rounded-2xl overflow-hidden bg-[#0A0E1A] border border-slate-800/80 p-5 shadow-sm transition-all hover:scale-[1.01] hover:border-slate-700">
                                    <!-- Ambient Glow -->
                                    <div
                                        class="absolute -right-6 -top-6 w-32 h-32 bg-[#34C759]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>
                                    <div
                                        class="absolute -left-6 -bottom-6 w-32 h-32 bg-[#00C2FF]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>

                                    <div class="relative z-10">
                                        <span
                                            class="text-[11px] font-bold uppercase tracking-wider text-[#34C759] block mb-1">Automasi
                                            WhatsApp &amp; POS</span>
                                        <div
                                            class="text-xl sm:text-2xl font-black text-white tracking-tighter leading-tight font-sans">
                                            Semua Chat &amp; Order Terhubung Otomatis
                                        </div>
                                    </div>
                                </a>

                                <!-- Sub-link below banner -->
                                <div class="mt-4">
                                    <a href="{{ route('public.omnichannel.whatsapp') }}"
                                        class="group inline-flex items-center gap-1.5 text-sm font-bold text-slate-900 dark:text-white hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">
                                        <span>Eksplorasi Fitur WhatsApp</span>
                                        <i data-lucide="arrow-right"
                                            class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        Kelola pesanan dari toko fisik, chat WhatsApp, dan e-commerce dalam satu
                                        dashboard tanpa takut selisih stok.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ═══ 4. RESOURCES MEGA DROPDOWN (FULL WIDTH UNDER HEADER LINE) ═══ -->
            <div x-show="resourceDropdown" x-cloak @mouseenter="resourceDropdown = true"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="cooca-header-dropdown absolute top-full inset-x-0 w-full left-0 right-0 z-50">

                <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-9">
                    <div class="grid grid-cols-12 gap-8 xl:gap-10 items-start">

                        <!-- LEFT 8 COLS: PUSAT EDUKASI & DOKUMENTASI -->
                        <div class="col-span-8 space-y-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">
                                Pusat Edukasi &amp; Dokumentasi
                            </p>

                            <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                                <!-- Blog -->
                                <a href="{{ route('blog.index') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#007AFF] dark:group-hover:border-[#00C2FF] group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] group-hover:bg-blue-50/50 dark:group-hover:bg-cyan-500/10 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="book-open" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#00C2FF] transition-colors block">
                                            Blog &amp; Insight Bisnis
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Artikel edukasi, tren pasar, &amp; strategi pertumbuhan
                                        </span>
                                    </div>
                                </a>

                                <!-- Guides -->
                                <a href="{{ route('public.resources.guides') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#34C759] group-hover:text-[#34C759] group-hover:bg-emerald-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#34C759] transition-colors block">
                                            Panduan &amp; Tutorial
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Tutorial step-by-step implementasi SOP dan POS
                                        </span>
                                    </div>
                                </a>

                                <!-- Case Studies -->
                                <a href="{{ route('public.resources.case-studies') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#FF9500] group-hover:text-[#FF9500] group-hover:bg-amber-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="award" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#FF9500] transition-colors block">
                                            Studi Kasus UMKM
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Kisah nyata pebisnis Indonesia scaling bersama COOCA
                                        </span>
                                    </div>
                                </a>

                                <!-- FAQ -->
                                <a href="{{ route('public.resources.faq') }}"
                                    class="group flex items-start gap-3.5 p-1 rounded-xl hover:bg-slate-50 dark:hover:bg-white/[0.05] transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.06] flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:border-[#AF52DE] group-hover:text-[#AF52DE] group-hover:bg-purple-50/50 transition-all shrink-0 mt-0.5">
                                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#AF52DE] transition-colors block">
                                            Pusat Bantuan &amp; FAQ
                                        </span>
                                        <span
                                            class="text-xs text-slate-500 dark:text-slate-400 leading-snug block mt-0.5">
                                            Jawaban cepat untuk pertanyaan teknis &amp; langganan
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- RIGHT 4 COLS: PUSAT PENGETAHUAN (With Vertical Divider) -->
                        <div
                            class="col-span-4 pl-8 border-l border-slate-200 dark:border-white/10 flex flex-col justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-3">
                                    Pusat Pengetahuan
                                </p>

                                <!-- Showcase Banner Card -->
                                <a href="{{ route('public.resources.guides') }}"
                                    class="group block relative rounded-2xl overflow-hidden bg-[#0A0E1A] border border-slate-800/80 p-5 shadow-sm transition-all hover:scale-[1.01] hover:border-slate-700">
                                    <!-- Ambient Glow -->
                                    <div
                                        class="absolute -right-6 -top-6 w-32 h-32 bg-[#AF52DE]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>
                                    <div
                                        class="absolute -left-6 -bottom-6 w-32 h-32 bg-[#007AFF]/20 rounded-full blur-2xl pointer-events-none">
                                    </div>

                                    <div class="relative z-10">
                                        <span
                                            class="text-[11px] font-bold uppercase tracking-wider text-[#AF52DE] block mb-1">Knowledge
                                            Hub</span>
                                        <div
                                            class="text-xl sm:text-2xl font-black text-white tracking-tighter leading-tight font-sans">
                                            Kuasai Operasional Bisnis Bersama COOCA
                                        </div>
                                    </div>
                                </a>

                                <!-- Sub-link below banner -->
                                <div class="mt-4">
                                    <a href="{{ route('public.resources.guides') }}"
                                        class="group inline-flex items-center gap-1.5 text-sm font-bold text-slate-900 dark:text-white hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">
                                        <span>Baca Panduan Operasional</span>
                                        <i data-lucide="arrow-right"
                                            class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        Tutorial lengkap mulai dari setup awal toko, pencatatan jurnal keuangan, hingga
                                        strategi pemasaran omnichannel.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Mobile Drawer Menu (Full Width Absolute Sheet Attached Under Header Line) -->
            <div x-show="mobileMenu" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="absolute top-full inset-x-0 w-full lg:hidden bg-[#060913] text-slate-200 border-t border-b border-white/10 shadow-[0_30px_60px_rgba(0,0,0,0.95)] max-h-[calc(100dvh-4rem)] sm:max-h-[calc(100dvh-5rem)] overflow-y-auto overscroll-contain p-3.5 sm:p-5 space-y-3 pb-28 z-50">

                <!-- 1. Top Auth & User Profile Card -->
                @if (auth('admin')->check())
                    <div class="p-3.5 rounded-[16px] bg-gradient-to-r from-[#00C2FF]/15 via-white/[0.04] to-transparent border border-[#00C2FF]/30 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#00C2FF]/20 flex items-center justify-center text-[#00C2FF] shrink-0 font-bold text-xs ring-2 ring-[#00C2FF]/30">
                                AD
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-white tracking-tight truncate block">{{ auth('admin')->user()->name }}</span>
                                <span class="text-[10px] text-slate-400 block">Superadmin Ekosistem</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" @click="mobileMenu = false"
                            class="shrink-0 px-3.5 py-2 rounded-[12px] bg-[#00C2FF] hover:bg-[#00B0E8] text-slate-950 font-bold text-xs shadow-xs active:scale-95 transition-all flex items-center gap-1.5">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    </div>
                @elseif (auth('web')->check())
                    <div class="p-3.5 rounded-[16px] bg-gradient-to-r from-[#00C2FF]/15 via-white/[0.04] to-transparent border border-[#00C2FF]/30 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#00C2FF]/20 flex items-center justify-center text-[#00C2FF] shrink-0 font-bold text-xs ring-2 ring-[#00C2FF]/30">
                                {{ strtoupper(substr(auth('web')->user()->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-white tracking-tight truncate block">{{ auth('web')->user()->name }}</span>
                                <span class="text-[10px] text-slate-400 block">Akun Bisnis Aktif</span>
                            </div>
                        </div>
                        <a href="{{ route('dashboard') }}" @click="mobileMenu = false"
                            class="shrink-0 px-3.5 py-2 rounded-[12px] bg-[#00C2FF] hover:bg-[#00B0E8] text-slate-950 font-bold text-xs shadow-xs active:scale-95 transition-all flex items-center gap-1.5">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            <span>Ke Dashboard</span>
                        </a>
                    </div>
                @else
                    <div class="p-3.5 rounded-[16px] bg-gradient-to-r from-white/[0.08] via-white/[0.04] to-transparent border border-white/10 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#00C2FF] block">Ekosistem UMKM</span>
                            <span class="text-xs font-semibold text-white truncate block">Coba Gratis Tanpa Kartu Kredit</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('login') }}" @click="mobileMenu = false"
                                class="px-3 py-1.5 rounded-[10px] bg-white/10 hover:bg-white/15 text-white text-xs font-semibold active:scale-95 transition-all">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" @click="mobileMenu = false"
                                class="px-3.5 py-1.5 rounded-[10px] bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white text-xs font-bold shadow-xs active:scale-95 transition-all flex items-center gap-1">
                                <span>Daftar</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- 2. Primary Navigation Quick Trio (Beranda, Marketplace, Jelajah) -->
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('landing') }}" @click="mobileMenu = false"
                        class="py-2.5 px-2 rounded-[14px] bg-white/[0.04] border border-white/10 hover:bg-white/[0.08] active:scale-95 transition-all text-center flex flex-col items-center justify-center gap-1 group {{ request()->routeIs('landing') || request()->is('/') ? 'ring-1 ring-[#00C2FF]/50 bg-white/[0.08]' : '' }}">
                        <i data-lucide="home" class="w-4 h-4 text-[#00C2FF]"></i>
                        <span class="text-xs font-semibold text-white truncate w-full">Beranda</span>
                    </a>
                    <a href="{{ route('marketplace.index') }}" @click="mobileMenu = false"
                        class="py-2.5 px-2 rounded-[14px] bg-white/[0.04] border border-white/10 hover:bg-white/[0.08] active:scale-95 transition-all text-center flex flex-col items-center justify-center gap-1 group {{ request()->routeIs('marketplace.*') ? 'ring-1 ring-[#FF9500]/50 bg-white/[0.08]' : '' }}">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-[#FF9500]"></i>
                        <span class="text-xs font-semibold text-white truncate w-full">Marketplace</span>
                    </a>
                    <a href="{{ route('public.discovery.index') }}" @click="mobileMenu = false"
                        class="py-2.5 px-2 rounded-[14px] bg-white/[0.04] border border-white/10 hover:bg-white/[0.08] active:scale-95 transition-all text-center flex flex-col items-center justify-center gap-1 group {{ request()->routeIs('public.discovery.*') || request()->routeIs('public.directory.*') ? 'ring-1 ring-[#34C759]/50 bg-white/[0.08]' : '' }}">
                        <i data-lucide="compass" class="w-4 h-4 text-[#34C759]"></i>
                        <span class="text-xs font-semibold text-white truncate w-full">Jelajah Toko</span>
                    </a>
                </div>

                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <!-- ═══ 3. DROPDOWN 1: PLATFORM (ADOPT DESKTOP DROPDOWN CONCEPT) ════════════ -->
                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <div class="rounded-[16px] bg-white/[0.03] border border-white/10 overflow-hidden">
                    <button type="button" @click="toggleMobileSection('platform')"
                        class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/[0.04] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-[#007AFF]/20 text-[#00C2FF] flex items-center justify-center">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-white block">Platform</span>
                                <span class="text-[10px] text-slate-400 block">Fitur Operasional, ERP Core &amp; AI</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/10 text-slate-300">8 Modul</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                :class="mobileSection === 'platform' ? 'rotate-180 text-[#00C2FF]' : ''"></i>
                        </div>
                    </button>

                    <div x-show="mobileSection === 'platform'" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="p-3 pt-0 border-t border-white/10 space-y-3">
                        
                        <!-- Sub-Section 1: Fitur -->
                        <div class="space-y-2 pt-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Fitur Operasional</p>
                            <div class="space-y-1.5">
                                <!-- Point of Sale -->
                                <a href="{{ route('public.erp.pos') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#007AFF]/20 text-[#00C2FF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Point of Sale</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Hubungkan pesanan, pembayaran, dan pembukuan</span>
                                    </div>
                                </a>

                                <!-- Dynamic Budgeting -->
                                <a href="{{ route('public.erp.accounting') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#5856D6]/20 text-[#5856D6] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Dynamic Budgeting</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Atur dan pantau dana secara real-time</span>
                                    </div>
                                </a>

                                <!-- Vendor Spend -->
                                <a href="{{ route('public.erp.erp') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Vendor Spend</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Kelola pembayaran SaaS &amp; vendor</span>
                                    </div>
                                </a>

                                <!-- Revenue Sync -->
                                <a href="{{ route('public.erp.inventory') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#30B0C7]/20 text-[#30B0C7] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="arrow-left-right" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Revenue Sync</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Tarik data retainer &amp; POS otomatis</span>
                                    </div>
                                </a>

                                <!-- Receipt Capture -->
                                <a href="{{ route('public.omnichannel.orders') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Receipt Capture</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Otomatiskan urusan struk</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Sub-Section 2: Platform Core -->
                        <div class="space-y-2 pt-1 border-t border-white/5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Platform Core</p>
                            <div class="space-y-1.5">
                                <!-- COOCA AI Agents -->
                                <a href="{{ route('public.content.creation') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-[#00C2FF]/10 hover:bg-[#00C2FF]/15 border border-[#00C2FF]/30 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#00C2FF]/20 text-[#00C2FF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-[#00C2FF] text-xs">COOCA AI Agents</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Lipat-gandakan efisiensi keuangan</span>
                                    </div>
                                </a>

                                <!-- Siap Global -->
                                <a href="{{ route('public.bos.overview') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#007AFF]/20 text-[#00C2FF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Siap Global</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Invoice dalam IDR, USD, dan SGD</span>
                                    </div>
                                </a>

                                <!-- Integrasi Bawaan -->
                                <a href="{{ route('public.bos.how-it-works') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="code-2" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Integrasi Bawaan</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Hubungkan ERP, HRIS &amp; tools</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Showcase Banner Card: Rilis Terbaru -->
                        <div class="pt-1 border-t border-white/5">
                            <a href="{{ route('public.omnichannel.whatsapp') }}" @click="mobileMenu = false"
                                class="block relative rounded-xl overflow-hidden bg-[#0A0E1A] border border-slate-800 p-3 shadow-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-[#00C2FF] block">Rilis Terbaru</span>
                                        <span class="text-xs font-bold text-white block">WhatsApp AI Agents</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight mt-0.5">Chat langsung dengan ledger pembukuan Anda</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-[#00C2FF]/20 text-[#00C2FF] flex items-center justify-center shrink-0">
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <!-- ═══ 4. DROPDOWN 2: SOLUTIONS (SOLUSI INDUSTRI) ═══════════════════════════ -->
                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <div class="rounded-[16px] bg-white/[0.03] border border-white/10 overflow-hidden">
                    <button type="button" @click="toggleMobileSection('solutions')"
                        class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/[0.04] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-[#34C759]/20 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="store" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-white block">Solutions</span>
                                <span class="text-[10px] text-slate-400 block">Solusi Spesifik 6 Sektor Industri</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/10 text-slate-300">6 Industri</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                :class="mobileSection === 'solutions' ? 'rotate-180 text-[#34C759]' : ''"></i>
                        </div>
                    </button>

                    <div x-show="mobileSection === 'solutions'" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="p-3 pt-0 border-t border-white/10 space-y-3">
                        
                        <div class="space-y-2 pt-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Solusi Sektor Industri</p>
                            <div class="grid grid-cols-1 gap-1.5">
                                <!-- F&B & Resto -->
                                <a href="{{ route('public.solutions.fnb') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="utensils" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">F&amp;B &amp; Resto</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Meja, menu QR, dapur, &amp; split bill</span>
                                    </div>
                                </a>

                                <!-- Retail & Toko -->
                                <a href="{{ route('public.solutions.retail') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#00C2FF]/20 text-[#00C2FF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Retail &amp; Toko</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Barcode scanner, varian, &amp; multi-cabang</span>
                                    </div>
                                </a>

                                <!-- Bengkel & Otomotif -->
                                <a href="{{ route('public.solutions.workshop') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Bengkel &amp; Otomotif</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">SPK, antrean servis, part &amp; mekanik</span>
                                    </div>
                                </a>

                                <!-- Laundry -->
                                <a href="{{ route('public.solutions.laundry') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Laundry Kiloan &amp; Satuan</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Kiloan, satuan, barcode rak &amp; status cuci</span>
                                    </div>
                                </a>

                                <!-- Manufacturing -->
                                <a href="{{ route('public.solutions.manufacturing') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="factory" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Manufaktur &amp; Produksi</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">BOM, work order, &amp; alokasi bahan baku</span>
                                    </div>
                                </a>

                                <!-- Services & Jasa -->
                                <a href="{{ route('public.solutions.services') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Services &amp; Jasa</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Booking appointment, termin, &amp; invoicing</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Showcase Banner Card: Konsultasi Solusi -->
                        <div class="pt-1 border-t border-white/5">
                            <a href="{{ route('public.demo') }}" @click="mobileMenu = false"
                                class="block relative rounded-xl overflow-hidden bg-[#0A0E1A] border border-slate-800 p-3 shadow-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-[#FF9500] block">Konsultasi Solusi</span>
                                        <span class="text-xs font-bold text-white block">Jadwalkan Konsultasi Demo</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight mt-0.5">Sistem khusus sesuai SOP industri Anda</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0">
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <!-- ═══ 5. DROPDOWN 3: OMNICHANNEL ═══════════════════════════════════════════ -->
                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <div class="rounded-[16px] bg-white/[0.03] border border-white/10 overflow-hidden">
                    <button type="button" @click="toggleMobileSection('omnichannel')"
                        class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/[0.04] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-[#FF2D55]/20 text-[#FF2D55] flex items-center justify-center">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-white block">Omnichannel</span>
                                <span class="text-[10px] text-slate-400 block">Kanal Penjualan, WhatsApp &amp; CRM</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/10 text-slate-300">5 Kanal</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                :class="mobileSection === 'omnichannel' ? 'rotate-180 text-[#FF2D55]' : ''"></i>
                        </div>
                    </button>

                    <div x-show="mobileSection === 'omnichannel'" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="p-3 pt-0 border-t border-white/10 space-y-3">
                        
                        <div class="space-y-2 pt-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Kanal Penjualan &amp; CRM Terpadu</p>
                            <div class="space-y-1.5">
                                <!-- Social Media -->
                                <a href="{{ route('public.omnichannel.social-media') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#FF2D55]/20 text-[#FF2D55] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Social Media Commerce</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Jadwal konten, auto-reply, &amp; katalog multi-channel</span>
                                    </div>
                                </a>

                                <!-- WhatsApp -->
                                <a href="{{ route('public.omnichannel.whatsapp') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">WhatsApp Official API</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Broadcast pesan massal &amp; multi-agent live chat</span>
                                    </div>
                                </a>

                                <!-- Marketplace Hub -->
                                <a href="{{ route('public.omnichannel.marketplace') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Marketplace Hub</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Sinkronisasi stok Shopee, Tokopedia, &amp; TikTok</span>
                                    </div>
                                </a>

                                <!-- Central Orders -->
                                <a href="{{ route('public.omnichannel.orders') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Central Orders</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Satu inbox pesanan terpadu untuk semua saluran</span>
                                    </div>
                                </a>

                                <!-- Customer Portal -->
                                <a href="{{ route('public.omnichannel.customer') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#5856D6]/20 text-[#5856D6] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Customer Portal &amp; CRM</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Program loyalty, membership tier, &amp; poin belanja</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Showcase Banner Card: Sorotan Omnichannel -->
                        <div class="pt-1 border-t border-white/5">
                            <a href="{{ route('public.omnichannel.whatsapp') }}" @click="mobileMenu = false"
                                class="block relative rounded-xl overflow-hidden bg-[#0A0E1A] border border-slate-800 p-3 shadow-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-[#34C759] block">Automasi WhatsApp &amp; POS</span>
                                        <span class="text-xs font-bold text-white block">Eksplorasi Fitur WhatsApp</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight mt-0.5">Semua Chat &amp; Order Terhubung Otomatis</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0">
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <!-- ═══ 6. DROPDOWN 4: RESOURCES (PUSAT EDUKASI & BANTUAN) ══════════════════ -->
                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <div class="rounded-[16px] bg-white/[0.03] border border-white/10 overflow-hidden">
                    <button type="button" @click="toggleMobileSection('resources')"
                        class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/[0.04] transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center">
                                <i data-lucide="book-open" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-white block">Resources</span>
                                <span class="text-[10px] text-slate-400 block">Blog, Panduan, Studi Kasus &amp; FAQ</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/10 text-slate-300">4 Edukasi</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                :class="mobileSection === 'resources' ? 'rotate-180 text-[#AF52DE]' : ''"></i>
                        </div>
                    </button>

                    <div x-show="mobileSection === 'resources'" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="p-3 pt-0 border-t border-white/10 space-y-3">
                        
                        <div class="space-y-2 pt-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Pusat Edukasi &amp; Dokumentasi</p>
                            <div class="space-y-1.5">
                                <!-- Blog -->
                                <a href="{{ route('blog.index') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#007AFF]/20 text-[#00C2FF] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Blog &amp; Insight Bisnis</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Artikel edukasi, tren pasar, &amp; strategi</span>
                                    </div>
                                </a>

                                <!-- Guides -->
                                <a href="{{ route('public.resources.guides') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Panduan &amp; Tutorial</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Tutorial step-by-step implementasi SOP dan POS</span>
                                    </div>
                                </a>

                                <!-- Case Studies -->
                                <a href="{{ route('public.resources.case-studies') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Studi Kasus UMKM</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Kisah nyata pebisnis Indonesia scaling bersama COOCA</span>
                                    </div>
                                </a>

                                <!-- FAQ -->
                                <a href="{{ route('public.resources.faq') }}" @click="mobileMenu = false"
                                    class="p-2 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 flex items-start gap-2.5 text-slate-200 transition-all">
                                    <div class="w-7 h-7 rounded-lg bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center shrink-0 mt-0.5">
                                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold block text-white text-xs">Pusat Bantuan &amp; FAQ</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight">Jawaban cepat pertanyaan teknis &amp; langganan</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Showcase Banner Card: Knowledge Hub -->
                        <div class="pt-1 border-t border-white/5">
                            <a href="{{ route('public.resources.guides') }}" @click="mobileMenu = false"
                                class="block relative rounded-xl overflow-hidden bg-[#0A0E1A] border border-slate-800 p-3 shadow-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-[#AF52DE] block">Knowledge Hub</span>
                                        <span class="text-xs font-bold text-white block">Baca Panduan Operasional</span>
                                        <span class="text-[10px] text-slate-400 block leading-tight mt-0.5">Kuasai Operasional Bisnis Bersama COOCA</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center shrink-0">
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <!-- ═══ 7. PRICING & ESSENTIAL TOOLS ═════════════════════════════════════════ -->
                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <!-- Pricing Highlight -->
                    <a href="{{ route('public.pricing') }}" @click="mobileMenu = false"
                        class="p-3 rounded-[14px] bg-gradient-to-r from-[#00C2FF]/10 via-white/[0.04] to-transparent border border-[#00C2FF]/30 hover:bg-white/[0.07] flex items-center gap-2.5 transition-colors">
                        <i data-lucide="tag" class="w-4 h-4 text-[#00C2FF]"></i>
                        <div class="min-w-0">
                            <span class="font-bold text-white block truncate">Pricing</span>
                            <span class="text-[9px] text-[#00C2FF] block truncate">Paket &amp; Fitur</span>
                        </div>
                    </a>

                    <!-- Live Demo -->
                    <a href="{{ route('public.demo') }}" @click="mobileMenu = false"
                        class="p-3 rounded-[14px] bg-white/[0.03] border border-white/10 hover:bg-white/[0.07] flex items-center gap-2.5 transition-colors">
                        <i data-lucide="play" class="w-4 h-4 text-[#34C759]"></i>
                        <div class="min-w-0">
                            <span class="font-bold text-white block truncate">Live Demo</span>
                            <span class="text-[9px] text-slate-400 block truncate">Coba Sistem</span>
                        </div>
                    </a>

                    <!-- Template Excel -->
                    <a href="{{ route('template.index') }}" @click="mobileMenu = false"
                        class="p-3 rounded-[14px] bg-white/[0.03] border border-white/10 hover:bg-white/[0.07] flex items-center gap-2.5 transition-colors">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#34C759]"></i>
                        <div class="min-w-0">
                            <span class="font-bold text-white block truncate">Template Excel</span>
                            <span class="text-[9px] text-slate-400 block truncate">Download Gratis</span>
                        </div>
                    </a>

                    <!-- Kalkulator Keuangan -->
                    <a href="{{ url('/kalkulator') }}" @click="mobileMenu = false"
                        class="p-3 rounded-[14px] bg-white/[0.03] border border-white/10 hover:bg-white/[0.07] flex items-center gap-2.5 transition-colors">
                        <i data-lucide="calculator" class="w-4 h-4 text-[#FF9500]"></i>
                        <div class="min-w-0">
                            <span class="font-bold text-white block truncate">Kalkulator</span>
                            <span class="text-[9px] text-slate-400 block truncate">9 Tool Finansial</span>
                        </div>
                    </a>
                </div>

                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <!-- ═══ 8. THEME TOGGLE ROW (APPLE INSET SETTING STYLE) ══════════════════════ -->
                <!-- ════════════════════════════════════════════════════════════════════════ -->
                <div class="p-3 rounded-[14px] bg-white/[0.03] border border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center text-[#FFD60A]">
                            <svg x-show="isDark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <svg x-show="!isDark" class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-white block">Tema Tampilan</span>
                            <span class="text-[10px] text-slate-400 block" x-text="isDark ? 'Mode Gelap Aktif' : 'Mode Terang Aktif'"></span>
                        </div>
                    </div>
                    <button type="button" @click="toggleTheme()"
                        class="px-3 py-1.5 rounded-[10px] bg-white/10 hover:bg-white/15 text-xs font-semibold text-white active:scale-95 transition-all">
                        <span x-text="isDark ? 'Ganti ke Terang' : 'Ganti ke Gelap'"></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation Backdrop Scrim (Outside header for clean full viewport coverage) -->
        <div x-show="mobileMenu" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenu = false"
            class="fixed inset-0 top-16 sm:top-20 lg:hidden z-40 bg-black/75 backdrop-blur-sm pointer-events-auto">
        </div>
    @endif

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="flex-grow pb-16 lg:pb-0">
        @yield('content')
    </main>

    <!-- ═══ APPLE GROUPED INSET FOOTER ═══ -->
    @if (!($hideFooter ?? false))
        <footer
            class="border-t border-white/10 bg-[#060913] text-slate-400 mt-0 pt-16 sm:pt-20 pb-28 text-sm sm:text-[15px] transition-colors">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 pb-12">

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
                                'whatsapp' => [
                                    'active' => filter_var(
                                        \App\Models\SystemSetting::get('social_whatsapp_active', '1'),
                                        FILTER_VALIDATE_BOOLEAN,
                                    ),
                                    'url' => $footerWaUrl,
                                    'name' => 'WhatsApp',
                                    'handle' => $footerWaNum,
                                    'svg' =>
                                        '<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>',
                                    'hover' => 'hover:bg-[#25D366]/20 hover:text-[#25D366]',
                                ],
                            ];
                        @endphp

                        <div class="space-y-3.5">
                            <a href="{{ route('landing') }}" class="inline-block group">
                                @if (!empty($siteLogoDarkUrl))
                                    <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteAppName }}"
                                        class="h-9 sm:h-10 lg:h-12 w-auto object-contain transition-transform group-hover:scale-105">
                                @else
                                    <span
                                        class="font-black text-3xl sm:text-4xl tracking-tighter text-white font-sans">COOCA</span>
                                @endif
                            </a>
                            <p class="text-sm sm:text-base font-bold text-slate-200">
                                {{ $siteTagline }}
                            </p>
                            <p class="text-sm sm:text-[14.5px] text-slate-400 leading-relaxed max-w-sm sm:max-w-md">
                                Satu ekosistem untuk mengelola, menghubungkan, menghasilkan, dan mengembangkan bisnis
                                Anda.
                            </p>
                            <div class="flex flex-wrap items-center gap-2.5 pt-1.5">
                                @foreach ($footerSocialChannels as $key => $soc)
                                    @if ($soc['active'] && !empty($soc['url']))
                                        <a href="{{ $soc['url'] }}" target="_blank" rel="noopener noreferrer"
                                            title="{{ $soc['name'] }}: {{ $soc['handle'] }}"
                                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/15 flex items-center justify-center active:scale-95 transition-all">
                                            <svg class="w-4.5 h-4.5 sm:w-5 sm:h-5 fill-current" viewBox="0 0 24 24">
                                                {!! $soc['svg'] !!}
                                            </svg>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Columns: 4 Columns (Platform, Solutions, Resources, Company) -->
                    <div class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-6 sm:gap-8 pt-4 lg:pt-0 border-t-0">

                        <!-- Col 1: Platform -->
                        <div class="space-y-3.5">
                            <p class="font-bold text-white uppercase text-xs sm:text-[13px] tracking-wider">
                                Platform</p>
                            <nav
                                class="flex flex-col space-y-2.5 sm:space-y-3 text-sm sm:text-[14.5px] text-slate-400">
                                <a href="{{ route('public.erp.erp') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">ERP</a>
                                <a href="{{ route('public.omnichannel.whatsapp') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Omnichannel</a>
                                <a href="{{ route('public.content.creation') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Content
                                    Automation</a>
                                <a href="{{ route('marketplace.index') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Marketplace</a>
                                <a href="{{ route('public.erp.analytics') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">AI
                                    Assistant</a>
                            </nav>
                        </div>

                        <!-- Col 2: Solutions -->
                        <div class="space-y-3.5">
                            <p class="font-bold text-white uppercase text-xs sm:text-[13px] tracking-wider">
                                Solutions</p>
                            <nav
                                class="flex flex-col space-y-2.5 sm:space-y-3 text-sm sm:text-[14.5px] text-slate-400">
                                <a href="{{ route('public.solutions.fnb') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">POS
                                    Kasir</a>
                                <a href="{{ route('public.solutions.retail') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Retail
                                    &amp; Toko</a>
                                <a href="{{ route('public.solutions.workshop') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Bengkel</a>
                                <a href="{{ route('public.solutions.laundry') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Laundry</a>
                                <a href="{{ route('public.solutions.manufacturing') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Manufacturing</a>
                                <a href="{{ route('public.solutions.services') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Jasa
                                    &amp; Service</a>
                            </nav>
                        </div>

                        <!-- Col 3: Resources -->
                        <div class="space-y-3.5">
                            <p class="font-bold text-white uppercase text-xs sm:text-[13px] tracking-wider">
                                Resources</p>
                            <nav
                                class="flex flex-col space-y-2.5 sm:space-y-3 text-sm sm:text-[14.5px] text-slate-400">
                                <a href="{{ route('blog.index') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Blog
                                    &amp; Edukasi</a>
                                <a href="{{ route('public.resources.guides') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Guides</a>
                                <a href="{{ route('public.resources.case-studies') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Case
                                    Studies</a>
                                <a href="{{ route('public.demo') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Webinar
                                    &amp; Demo</a>
                                <a href="{{ route('public.resources.faq') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">FAQ</a>
                            </nav>
                        </div>

                        <!-- Col 4: Company -->
                        <div class="space-y-3.5">
                            <p class="font-bold text-white uppercase text-xs sm:text-[13px] tracking-wider">
                                Company</p>
                            <nav
                                class="flex flex-col space-y-2.5 sm:space-y-3 text-sm sm:text-[14.5px] text-slate-400">
                                <a href="{{ route('public.about') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">About
                                    Us</a>
                                <a href="{{ route('public.privacy') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Security</a>
                                <a href="{{ route('public.privacy') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Privacy
                                    Policy</a>
                                <a href="{{ route('public.terms') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Terms
                                    &amp; Conditions</a>
                                <a href="{{ route('public.support') }}"
                                    class="hover:text-white hover:translate-x-0.5 transition-all inline-block">Bantuan
                                    &amp; Kontak</a>
                            </nav>
                        </div>

                    </div>
                </div>

                <!-- Bottom Copyright & Links -->
                <div
                    class="border-t border-white/10 text-slate-400 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-[13px]">
                    <p class="text-center sm:text-left">© {{ date('Y') }} COOCA. All rights reserved.</p>
                    <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-6 gap-y-2">
                        <a href="{{ route('public.privacy') }}"
                            class="text-slate-400 hover:text-white transition-colors">Privacy</a>
                        <a href="{{ route('public.terms') }}"
                            class="text-slate-400 hover:text-white transition-colors">Terms</a>
                        <a href="{{ route('public.support') }}"
                            class="text-slate-400 hover:text-white transition-colors">Support</a>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    @if (!($hideBottomNav ?? false))
        <!-- ═══ APPLE FLOATING DOCK BOTTOM NAVBAR (MOBILE ONLY) ═══ -->
        <nav x-show="!mobileMenu" x-cloak class="fixed bottom-0 inset-x-0 z-40 pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))] px-4 sm:px-6 lg:px-8 pointer-events-none lg:hidden"
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

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ FLOATING WHATSAPP CHAT WIDGET (Configured via Admin Settings) ════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    @if (empty($hideFloatingWa))
        @php
            $waActiveSetting = \App\Models\SystemSetting::get('social_whatsapp_active', '1');
            $waIsActive = filter_var($waActiveSetting, FILTER_VALIDATE_BOOLEAN);
            $waConfiguredNumber = \App\Models\SystemSetting::get('social_whatsapp_number', '0852 8786 4176');
            $waConfiguredUrl = \App\Models\SystemSetting::get('social_whatsapp_url');

            // Format nomor telepon menjadi format internasional Indonesia (62xxx)
            $waDigits = preg_replace('/[^0-9]/', '', (string) $waConfiguredNumber);
            if (str_starts_with($waDigits, '0')) {
                $waDigits = '62' . substr($waDigits, 1);
            } elseif (str_starts_with($waDigits, '8')) {
                $waDigits = '62' . $waDigits;
            }

            $waDefaultMessage =
                'Halo Tim COOCA, saya ingin berkonsultasi mengenai platform ERP dan operasional bisnis saya.';

            if (
                !empty($waConfiguredUrl) &&
                (str_contains($waConfiguredUrl, 'wa.me') || str_contains($waConfiguredUrl, 'whatsapp.com'))
            ) {
                if (!str_contains($waConfiguredUrl, 'text=')) {
                    $separator = str_contains($waConfiguredUrl, '?') ? '&' : '?';
                    $waTargetUrl = $waConfiguredUrl . $separator . 'text=' . urlencode($waDefaultMessage);
                } else {
                    $waTargetUrl = $waConfiguredUrl;
                }
            } elseif (!empty($waDigits)) {
                $waTargetUrl =
                    'https://api.whatsapp.com/send?phone=' . $waDigits . '&text=' . urlencode($waDefaultMessage);
            } else {
                $waTargetUrl = 'https://wa.me/6285287864176?text=' . urlencode($waDefaultMessage);
            }
        @endphp

        @if ($waIsActive)
            <!-- Floating WhatsApp Button (Pure Icon FAB, Elevated safely above Mobile Floating Dock Navbar) -->
            <div x-show="!mobileMenu" x-cloak class="fixed floating-wa-fab right-4 sm:right-6 lg:right-6 z-50 print:hidden">
                <a href="{{ $waTargetUrl }}" target="_blank" rel="noopener noreferrer"
                    aria-label="Chat WhatsApp Tim COOCA"
                    title="Chat WhatsApp Tim COOCA"
                    class="group relative w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#25D366] hover:bg-[#20BD5A] text-white flex items-center justify-center shadow-[0_8px_30px_rgba(37,211,102,0.45)] hover:shadow-[0_12px_35px_rgba(37,211,102,0.65)] hover:scale-105 active:scale-95 transition-all">
                    <!-- Official WhatsApp SVG Icon -->
                    <svg class="w-7 h-7 sm:w-8.5 sm:h-8.5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    <span class="sr-only">Chat Kami</span>
                </a>
            </div>
        @endif
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
