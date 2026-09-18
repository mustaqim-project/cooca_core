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
        $siteLogoDarkSetting  = \App\Models\SystemSetting::get('site_logo_dark');
        $siteFaviconSetting   = \App\Models\SystemSetting::get('site_favicon');
        $seoOgImageSetting    = \App\Models\SystemSetting::get('seo_og_image');

        $siteLogoLightUrl = $siteLogoLightSetting ? asset('storage/' . $siteLogoLightSetting) : asset('assets/image/1785229034_logo_dark.png');
        $siteLogoDarkUrl  = $siteLogoDarkSetting ? asset('storage/' . $siteLogoDarkSetting) : asset('assets/image/1785229034_logo_dark.png');
        $siteFaviconUrl   = $siteFaviconSetting ? asset('storage/' . $siteFaviconSetting) : asset('assets/image/1785229034_favicon.png');
        $seoOgImageUrl    = $seoOgImageSetting ? asset('storage/' . $seoOgImageSetting) : asset('assets/image/cooca.png');

        $siteAppName           = \App\Models\SystemSetting::get('app_name', 'Cooca UMKM');
        $siteTagline           = \App\Models\SystemSetting::get('site_tagline', 'Business Operating System & Omnichannel ERP');
        $seoMetaTitle          = \App\Models\SystemSetting::get('seo_meta_title', 'Cooca UMKM - Business Operating System & Omnichannel ERP');
        $seoMetaDesc           = \App\Models\SystemSetting::get('seo_meta_description', 'Cooca UMKM: Software kasir POS, pembukuan otomatis, kalkulator bisnis, omnichannel media sosial & AI Assistant gratis selamanya untuk UMKM Indonesia.');
        $seoKeywords           = \App\Models\SystemSetting::get('seo_meta_keywords', 'Cooca UMKM, software kasir gratis, erp umkm, pos kasir toko, aplikasi pembukuan gratis, kalkulator hpp, kalkulator bep, template pembukuan excel, cooca.id');
        $seoAuthor             = \App\Models\SystemSetting::get('seo_author', 'Cooca Indonesia');
        $seoRobots             = \App\Models\SystemSetting::get('seo_robots', 'index, follow');
        $seoCanonical          = \App\Models\SystemSetting::get('seo_canonical_url') ?: url()->current();
        $seoOgTitle            = \App\Models\SystemSetting::get('seo_og_title') ?: $seoMetaTitle;
        $seoOgDesc             = \App\Models\SystemSetting::get('seo_og_description') ?: $seoMetaDesc;
        $seoTwitterCard        = \App\Models\SystemSetting::get('seo_twitter_card', 'summary_large_image');
        $seoTwitterSite        = \App\Models\SystemSetting::get('seo_twitter_site', '@cooca_id');
        $seoGoogleVerification = \App\Models\SystemSetting::get('seo_google_verification');
        $seoBingVerification   = \App\Models\SystemSetting::get('seo_bing_verification');
        $seoGaId               = \App\Models\SystemSetting::get('seo_google_analytics_id');
        $seoCustomHeadScripts  = \App\Models\SystemSetting::get('seo_custom_head_scripts');
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title')@else{{ $title ?? $seoMetaTitle }}@endif</title>
    <meta name="description" content="@yield('description', $seoMetaDesc)">
    <meta name="keywords" content="@yield('keywords', $seoKeywords)">
    <meta name="author" content="{{ $seoAuthor }}">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteAppName }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:title" content="{{ $seoOgTitle ?: ($title ?? (View::hasSection('title') ? View::yieldContent('title') : $seoMetaTitle)) }}">
    <meta property="og:description" content="@yield('description', $seoOgDesc ?: $seoMetaDesc)">
    <meta property="og:image" content="@yield('og_image', $seoOgImageUrl)">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="{{ $seoTwitterCard }}">
    <meta name="twitter:site" content="{{ $seoTwitterSite }}">
    <meta name="twitter:title" content="{{ $seoOgTitle ?: ($title ?? (View::hasSection('title') ? View::yieldContent('title') : $seoMetaTitle)) }}">
    <meta name="twitter:description" content="@yield('description', $seoOgDesc ?: $seoMetaDesc)">
    <meta name="twitter:image" content="@yield('og_image', $seoOgImageUrl)">

    <!-- Webmaster Verification -->
    @if(!empty($seoGoogleVerification))
    <meta name="google-site-verification" content="{{ $seoGoogleVerification }}">
    @endif
    @if(!empty($seoBingVerification))
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
    @if(!empty($seoGaId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoGaId }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $seoGaId }}');
    </script>
    @endif

    <!-- Custom Head Scripts -->
    @if(!empty($seoCustomHeadScripts))
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

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('seo')
</head>

<body
    class="min-h-screen flex flex-col justify-between bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased transition-colors duration-200"
    x-data="{
        mobileMenu: false,
        calcDropdown: false,
        solutionDropdown: false,
        templateDropdown: false,
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

    <!-- ═══ APPLE FROSTED GLASS HEADER ═══ -->
    @if (!($hideHeader ?? false))
        <header
            class="sticky top-0 z-50 backdrop-blur-2xl bg-white/80 dark:bg-[#1C1C1E]/80 border-b border-black/5 dark:border-white/10 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-18 flex items-center justify-between">

                <!-- Logo Cooca -->
                <a href="{{ route('landing') }}" class="flex items-center gap-3 group shrink-0">
                    <img src="{{ $siteLogoLightUrl }}" alt="{{ $siteAppName }}"
                        class="h-7 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105 dark:hidden">
                    <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteAppName }}"
                        class="h-7 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105 hidden dark:block">
                    <div class="border-l border-black/10 dark:border-white/10 pl-3 hidden xs:block">
                        <span
                            class="font-extrabold text-sm sm:text-base tracking-tight text-black dark:text-white block leading-tight">{{ $siteAppName }}</span>
                        <span
                            class="text-[9px] sm:text-[10px] uppercase font-bold text-[#34C759] dark:text-[#30D158] tracking-[0.15em]">{{ $siteTagline }}</span>
                    </div>
                </a>

                <!-- Desktop Nav Menu (Apple HIG Navigation Bar Style) -->
                <nav
                    class="hidden lg:flex items-center gap-5 xl:gap-7 text-[13px] font-medium text-black/70 dark:text-white/70">
                    <a href="{{ route('landing') }}"
                        class="hover:text-black dark:hover:text-white transition-colors {{ request()->routeIs('landing') ? 'text-[#007AFF] dark:text-[#0A84FF] font-semibold' : '' }}">Beranda</a>

                    <!-- Dropdown Kalkulator -->
                    <div class="relative" @click.outside="calcDropdown = false">
                        <button @click="calcDropdown = !calcDropdown"
                            class="flex items-center gap-1.5 hover:text-black dark:hover:text-white transition-colors py-2 focus:outline-none">
                            <span>Kalkulator</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform"
                                :class="calcDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="calcDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="absolute top-full left-0 w-72 p-2 bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.06] dark:border-white/[0.08] rounded-[20px] shadow-2xl backdrop-blur-2xl space-y-1 z-50">
                            <a href="{{ route('kalkulator.hpp') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator HPP</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Hitung biaya modal & harga
                                    pokok</span>
                            </a>
                            <a href="{{ route('kalkulator.bep') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator BEP</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Titik impas rupiah & unit
                                    produk</span>
                            </a>
                            <a href="{{ route('kalkulator.harga-jual') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator Harga
                                    Jual</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Simulasi markup vs margin
                                    laba</span>
                            </a>
                            <a href="{{ route('kalkulator.laba-bersih') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator Laba
                                    Bersih</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Proyeksi net profit bersih
                                    usaha</span>
                            </a>
                            <a href="{{ route('kalkulator.gaji-karyawan') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator Gaji
                                    Karyawan</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Take home pay, tunjangan &
                                    lembur</span>
                            </a>
                            <a href="{{ route('kalkulator.pph-final') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator PPh Final
                                    0.5%</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Pajak UMKM resmi PP
                                    55/2022</span>
                            </a>
                            <a href="{{ route('kalkulator.omzet-harian') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kalkulator Omzet
                                    Harian</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Target sales harian &
                                    rata-rata struk</span>
                            </a>
                            <a href="{{ route('kalkulator.simulasi-what-if') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs border-t border-black/5 dark:border-white/10 mt-1 transition">
                                <span class="font-semibold block text-[#007AFF] dark:text-[#0A84FF]">Simulasi
                                    What-If</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Uji ketahanan kenaikan biaya
                                    bahan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown Solusi Niche -->
                    <div class="relative" @click.outside="solutionDropdown = false">
                        <button @click="solutionDropdown = !solutionDropdown"
                            class="flex items-center gap-1.5 hover:text-black dark:hover:text-white transition-colors py-2 focus:outline-none">
                            <span>Solusi Industri</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform"
                                :class="solutionDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="solutionDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="absolute top-full left-0 w-72 p-2 bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.06] dark:border-white/[0.08] rounded-[20px] shadow-2xl backdrop-blur-2xl space-y-1 z-50">
                            <a href="{{ route('solusi.show', 'kasir-warung') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Warung & Sembako</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Grosir, eceran & catatan
                                    kasbon hutang</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-cafe-kecil') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kafe & Kedai Kopi</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Resep cup, split bill & QRIS
                                    statis</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-kios') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Kios & Konter HP</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Aksesoris & kasir kilat
                                    barcode</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-laundry') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Laundry Kiloan</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Nota otomatis WA & status
                                    cucian</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-salon') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Salon Kecantikan</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Treatment, paket & komisi
                                    kapster</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-barbershop') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Barbershop</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Antrean pangkas & komisi
                                    barber</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-bengkel-kecil') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Bengkel Motor Kecil</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Ongkos jasa servis + stok
                                    sparepart</span>
                            </a>
                            <a href="{{ route('solusi.show', 'kasir-irt') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Industri Rumah Tangga
                                    (IRT)</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Produksi snack, resep &
                                    katering</span>
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown Template Gratis -->
                    <div class="relative" @click.outside="templateDropdown = false">
                        <button @click="templateDropdown = !templateDropdown"
                            class="flex items-center gap-1.5 hover:text-black dark:hover:text-white transition-colors py-2 focus:outline-none">
                            <span>Template Gratis</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform"
                                :class="templateDropdown ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="templateDropdown" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="absolute top-full left-0 w-72 p-2 bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.06] dark:border-white/[0.08] rounded-[20px] shadow-2xl backdrop-blur-2xl space-y-1 z-50">
                            <a href="{{ route('template.index') }}"
                                class="block px-3 py-2 rounded-[12px] text-[#007AFF] dark:text-[#0A84FF] hover:bg-black/5 dark:hover:bg-white/10 text-xs font-semibold transition">
                                Semua Template Gratis →
                            </a>
                            <a href="{{ route('template.show', 'pembukuan-warung-excel') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Buku Kas Warung
                                    (Excel)</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Arus kas harian
                                    warung</span>
                            </a>
                            <a href="{{ route('template.show', 'laporan-keuangan-sederhana') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Laporan Keuangan
                                    Sederhana</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Laba rugi & neraca mini
                                    toko</span>
                            </a>
                            <a href="{{ route('template.show', 'stok-opname-excel') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Template Stok
                                    Opname</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Cek fisik selisih barang
                                    gudang</span>
                            </a>
                            <a href="{{ route('template.show', 'invoice-sederhana') }}"
                                class="block px-3 py-2 rounded-[12px] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                <span class="font-semibold block text-black dark:text-white">Pembuat Invoice
                                    Instan</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">Format nota penjualan &
                                    tagihan</span>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('public.discovery.index') }}"
                        class="hover:text-black dark:hover:text-white transition-colors {{ request()->routeIs('public.discovery.*') || request()->routeIs('public.directory.*') ? 'text-[#007AFF] dark:text-[#0A84FF] font-semibold' : '' }}">Jelajah
                        Toko</a>
                    <a href="{{ route('blog.index') }}"
                        class="hover:text-black dark:hover:text-white transition-colors {{ request()->routeIs('blog.*') ? 'text-[#007AFF] dark:text-[#0A84FF] font-semibold' : '' }}">Blog
                        & Edukasi</a>
                    <a href="{{ route('contact') }}"
                        class="hover:text-black dark:hover:text-white transition-colors {{ request()->routeIs('contact') ? 'text-[#007AFF] dark:text-[#0A84FF] font-semibold' : '' }}">Kontak</a>
                </nav>

                <!-- Action Controls & Theme Toggle -->
                <div class="flex items-center gap-2.5">
                    <!-- Apple Theme Switcher Button -->
                    <button type="button" @click="toggleTheme()"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all"
                        title="Ganti Mode Terang/Gelap" aria-label="Toggle Theme">
                        <!-- Sun Icon for Dark Mode (Switch to Light) -->
                        <svg x-show="isDark" x-cloak class="w-4 h-4 text-[#FFD60A]" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <!-- Moon Icon for Light Mode (Switch to Dark) -->
                        <svg x-show="!isDark" class="w-4 h-4 text-black/70" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <div class="hidden sm:flex items-center gap-2">
                        @if (auth('admin')->check())
                            <a href="{{ route('admin.dashboard') }}"
                                class="glow-btn px-4 py-2 rounded-[10px] text-white font-semibold text-xs flex items-center gap-2">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        @elseif (auth('web')->check())
                            <a href="{{ route('dashboard') }}"
                                class="glow-btn px-4 py-2 rounded-[10px] text-white font-semibold text-xs flex items-center gap-2">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                <span>Ke Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="px-3.5 py-2 text-xs font-semibold text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 rounded-[10px] transition-all">Masuk</a>
                            <a href="{{ route('register') }}"
                                class="glow-btn px-4 py-2 rounded-[10px] text-white font-semibold text-xs flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                                <span>Mulai Gratis</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <button @click="mobileMenu = !mobileMenu"
                        class="lg:hidden p-2 rounded-[10px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white transition-colors"
                        aria-label="Open Mobile Navigation">
                        <i :data-lucide="mobileMenu ? 'x' : 'menu'" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer Menu (Apple Control Center Bento Sheet) -->
            <div x-show="mobileMenu" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="lg:hidden p-4 sm:p-5 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border-b border-black/5 dark:border-white/10 space-y-3.5 text-sm font-medium max-h-[85vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('landing') }}" @click="mobileMenu = false"
                        class="block py-2.5 px-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        <span class="flex items-center gap-2 text-xs">
                            <i data-lucide="home" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <span>Beranda</span>
                        </span>
                    </a>
                    <a href="{{ route('public.discovery.index') }}" @click="mobileMenu = false"
                        class="block py-2.5 px-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        <span class="flex items-center gap-2 text-xs">
                            <i data-lucide="compass" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Jelajah Toko</span>
                        </span>
                    </a>
                </div>

                <!-- Bento Tile Section: Kalkulator Bisnis -->
                <div class="py-2 border-t border-black/5 dark:border-white/10">
                    <div
                        class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wider px-1 mb-2 flex items-center gap-1.5">
                        <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                        <span>Kalkulator Interaktif</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('kalkulator.hpp') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#007AFF]/10 active:scale-95 transition-all">
                            <i data-lucide="layers" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"></i>
                            <span class="truncate font-medium">HPP Biaya</span>
                        </a>
                        <a href="{{ route('kalkulator.bep') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="scale" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0"></i>
                            <span class="truncate font-medium">BEP Impas</span>
                        </a>
                        <a href="{{ route('kalkulator.harga-jual') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#FF9500]/10 active:scale-95 transition-all">
                            <i data-lucide="tag" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A] shrink-0"></i>
                            <span class="truncate font-medium">Harga Jual</span>
                        </a>
                        <a href="{{ route('kalkulator.laba-bersih') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#007AFF]/10 active:scale-95 transition-all">
                            <i data-lucide="pie-chart"
                                class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"></i>
                            <span class="truncate font-medium">Laba Bersih</span>
                        </a>
                        <a href="{{ route('kalkulator.gaji-karyawan') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#007AFF]/10 active:scale-95 transition-all">
                            <i data-lucide="users" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"></i>
                            <span class="truncate font-medium">Gaji Staf</span>
                        </a>
                        <a href="{{ route('kalkulator.pph-final') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#FF3B30]/10 active:scale-95 transition-all">
                            <i data-lucide="receipt" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A] shrink-0"></i>
                            <span class="truncate font-medium">PPh 0.5%</span>
                        </a>
                        <a href="{{ route('kalkulator.omzet-harian') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#007AFF]/10 active:scale-95 transition-all">
                            <i data-lucide="target" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"></i>
                            <span class="truncate font-medium">Omzet Harian</span>
                        </a>
                        <a href="{{ route('kalkulator.simulasi-what-if') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-semibold active:scale-95 transition-all">
                            <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i>
                            <span class="truncate font-semibold">What-If AI</span>
                        </a>
                    </div>
                </div>

                <!-- Bento Tile Section: Solusi Industri -->
                <div class="py-2 border-t border-black/5 dark:border-white/10">
                    <div
                        class="text-[11px] font-bold text-[#34C759] dark:text-[#30D158] uppercase tracking-wider px-1 mb-2 flex items-center gap-1.5">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        <span>Solusi 20+ Industri</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('solusi.show', 'kasir-warung') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="shopping-basket" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span class="truncate font-medium">Warung Toko</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-cafe-kecil') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="coffee" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                            <span class="truncate font-medium">Kafe & Kedai</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-kios') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="smartphone" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span class="truncate font-medium">Konter HP</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-laundry') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="shirt" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                            <span class="truncate font-medium">Laundry</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-salon') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                            <span class="truncate font-medium">Salon & Spa</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-barbershop') }}" @click="mobileMenu = false"
                            class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                            <i data-lucide="scissors" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span class="truncate font-medium">Barbershop</span>
                        </a>
                    </div>
                </div>

                <!-- Inset Links -->
                <div class="py-2 border-t border-black/5 dark:border-white/10 space-y-1 text-xs">
                    <a href="{{ route('template.index') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-[#1D1D1F]/80 dark:text-[#F5F5F7]/80 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Template Pembukuan Excel</span>
                    </a>
                    <a href="{{ route('blog.index') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-[#1D1D1F]/80 dark:text-[#F5F5F7]/80 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Blog &amp; Edukasi UMKM</span>
                    </a>
                    <a href="{{ route('contact') }}" @click="mobileMenu = false"
                        class="flex items-center gap-2 py-2 px-3 rounded-[12px] text-[#1D1D1F]/80 dark:text-[#F5F5F7]/80 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        <i data-lucide="phone" class="w-4 h-4 text-[#FF9500]"></i>
                        <span>Kontak &amp; Dukungan</span>
                    </a>
                </div>

                @if (auth('admin')->check())
                    <div class="pt-3 border-t border-black/5 dark:border-white/10">
                        <a href="{{ route('admin.dashboard') }}" @click="mobileMenu = false"
                            class="w-full py-3 rounded-[14px] glow-btn text-center text-white font-semibold flex items-center justify-center gap-2 active:scale-95 transition-all">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    </div>
                @elseif (auth('web')->check())
                    <div class="pt-3 border-t border-black/5 dark:border-white/10">
                        <a href="{{ route('dashboard') }}" @click="mobileMenu = false"
                            class="w-full py-3 rounded-[14px] glow-btn text-center text-white font-semibold flex items-center justify-center gap-2 active:scale-95 transition-all">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Ke Dashboard</span>
                        </a>
                    </div>
                @else
                    <div class="pt-3 border-t border-black/5 dark:border-white/10 grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}"
                            class="py-2.5 rounded-[14px] bg-black/5 dark:bg-white/10 text-center text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-xs active:scale-95 transition-all">Masuk</a>
                        <a href="{{ route('register') }}"
                            class="py-2.5 rounded-[14px] glow-btn text-center text-white font-semibold text-xs active:scale-95 transition-all">Mulai
                            Gratis</a>
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
            class="border-t border-black/5 dark:border-white/10 bg-white dark:bg-[#1C1C1E] pt-12 sm:pt-14 pb-32 sm:pb-36 lg:pb-14 mt-16 text-xs text-black/60 dark:text-white/60 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 pb-10">

                    <!-- Col 1: Brand & Contact (Full width on mobile/tablet, 2 cols on desktop) -->
                    <div class="space-y-4 lg:col-span-2">
                        <div class="flex items-center gap-3">
                            <img src="{{ $siteLogoLightUrl }}" alt="{{ $siteAppName }}"
                                class="h-7 w-auto object-contain dark:hidden">
                            <img src="{{ $siteLogoDarkUrl }}" alt="{{ $siteAppName }}"
                                class="h-7 w-auto object-contain hidden dark:block">
                            <div class="border-l border-black/10 dark:border-white/10 pl-3">
                                <span
                                    class="font-bold text-sm sm:text-base tracking-tight text-black dark:text-white block leading-tight">{{ $siteAppName }}</span>
                                <span
                                    class="text-[9px] uppercase font-bold text-[#34C759] dark:text-[#30D158] tracking-wider">{{ $siteTagline }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-black/50 dark:text-white/50 leading-relaxed max-w-sm">
                            Platform operasional bisnis 100% gratis untuk UMKM Indonesia. Dilengkapi kalkulator HPP
                            presisi, pembukuan kas, kasir POS, dan asisten AI pintar.
                        </p>

                        <!-- Contact Widgets -->
                        @php
                            $footerWaUrl = \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6282337499577');
                            $footerWaNum = \App\Models\SystemSetting::get('social_whatsapp_number', '0823 3749 9577');
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-sm pt-1">
                            <a href="{{ $footerWaUrl }}" target="_blank"
                                class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5 hover:bg-[#34C759]/10 active:scale-[0.98] transition-all">
                                <div
                                    class="w-7 h-7 rounded-[10px] bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span
                                        class="text-[9px] uppercase font-bold text-[#6E6E73] dark:text-[#86868B] block leading-tight">WhatsApp
                                        CS</span>
                                    <span
                                        class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] text-xs truncate block font-mono">{{ $footerWaNum }}</span>
                                </div>
                            </a>
                            <a href="mailto:support@cooca.id"
                                class="p-2.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2.5 hover:bg-[#007AFF]/10 active:scale-[0.98] transition-all">
                                <div
                                    class="w-7 h-7 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                    <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span
                                        class="text-[9px] uppercase font-bold text-[#6E6E73] dark:text-[#86868B] block leading-tight">Email
                                        Bantuan</span>
                                    <span
                                        class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7] text-xs truncate block">support@cooca.id</span>
                                </div>
                            </a>
                        </div>

                        <!-- Official Social Media Channels (CMS Dynamic) -->
                        @php
                            $footerSocialChannels = [
                                'instagram' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_instagram_active', '1'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_instagram_url', 'https://instagram.com/cooca.indonesia'),
                                    'name'   => 'Instagram',
                                    'handle' => \App\Models\SystemSetting::get('social_instagram_handle', '@cooca.indonesia'),
                                    'svg'    => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
                                    'hover'  => 'hover:bg-[#DD2A7B]/15 hover:text-[#DD2A7B] dark:hover:text-[#DD2A7B]',
                                ],
                                'facebook' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_facebook_active', '1'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_facebook_url', 'https://facebook.com/cooca.id'),
                                    'name'   => 'Facebook',
                                    'handle' => \App\Models\SystemSetting::get('social_facebook_name', 'Cooca Indonesia'),
                                    'svg'    => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
                                    'hover'  => 'hover:bg-[#1877F2]/15 hover:text-[#1877F2] dark:hover:text-[#1877F2]',
                                ],
                                'tiktok' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_tiktok_active', '1'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_tiktok_url', 'https://tiktok.com/@cooca.id'),
                                    'name'   => 'TikTok',
                                    'handle' => \App\Models\SystemSetting::get('social_tiktok_handle', '@cooca.id'),
                                    'svg'    => '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>',
                                    'hover'  => 'hover:bg-black/10 dark:hover:bg-white/15 hover:text-black dark:hover:text-white',
                                ],
                                'youtube' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_youtube_active', '1'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_youtube_url', 'https://youtube.com/@cooca_id'),
                                    'name'   => 'YouTube',
                                    'handle' => \App\Models\SystemSetting::get('social_youtube_name', 'Cooca UMKM Official'),
                                    'svg'    => '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
                                    'hover'  => 'hover:bg-[#FF0000]/15 hover:text-[#FF0000] dark:hover:text-[#FF0000]',
                                ],
                                'twitter' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_twitter_active', '1'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_twitter_url', 'https://x.com/cooca_id'),
                                    'name'   => 'X (Twitter)',
                                    'handle' => \App\Models\SystemSetting::get('social_twitter_handle', '@cooca_id'),
                                    'svg'    => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
                                    'hover'  => 'hover:bg-black/10 dark:hover:bg-white/15 hover:text-black dark:hover:text-white',
                                ],
                                'linkedin' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_linkedin_active', '1'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_linkedin_url', 'https://linkedin.com/company/cooca'),
                                    'name'   => 'LinkedIn',
                                    'handle' => \App\Models\SystemSetting::get('social_linkedin_name', 'Cooca Indonesia'),
                                    'svg'    => '<path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>',
                                    'hover'  => 'hover:bg-[#0A66C2]/15 hover:text-[#0A66C2] dark:hover:text-[#0A66C2]',
                                ],
                                'telegram' => [
                                    'active' => filter_var(\App\Models\SystemSetting::get('social_telegram_active', '0'), FILTER_VALIDATE_BOOLEAN),
                                    'url'    => \App\Models\SystemSetting::get('social_telegram_url', 'https://t.me/cooca_id'),
                                    'name'   => 'Telegram',
                                    'handle' => \App\Models\SystemSetting::get('social_telegram_name', 'Komunitas Cooca UMKM'),
                                    'svg'    => '<path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.536-.196 1.006.128.832.942z"/>',
                                    'hover'  => 'hover:bg-[#229ED9]/15 hover:text-[#229ED9] dark:hover:text-[#229ED9]',
                                ],
                            ];
                        @endphp
                        <div class="pt-2">
                            <span class="text-[11px] font-bold text-black/40 dark:text-white/40 uppercase tracking-wider block mb-2">Ikuti Kanal Resmi Cooca:</span>
                            <div class="flex flex-wrap items-center gap-2">
                                @foreach($footerSocialChannels as $key => $soc)
                                    @if($soc['active'] && !empty($soc['url']))
                                    <a href="{{ $soc['url'] }}" target="_blank" rel="noopener noreferrer"
                                        title="{{ $soc['name'] }}: {{ $soc['handle'] }}"
                                        class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.05] dark:border-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center {{ $soc['hover'] }} active:scale-95 transition-all">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                            {!! $soc['svg'] !!}
                                        </svg>
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-1">
                            <div
                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-[11px] font-medium border border-[#34C759]/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                                <span>Semua Sistem Operasional Normal</span>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Columns: 2-Cols on Mobile, 3-Cols on sm/md/lg -->
                    <div
                        class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-3 gap-6 sm:gap-8 pt-4 lg:pt-0 border-t lg:border-t-0 border-black/5 dark:border-white/10">

                        <!-- Col A: Kalkulator Bisnis -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-5 h-5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                    <i data-lucide="calculator" class="w-3 h-3"></i>
                                </div>
                                <p class="font-bold text-black dark:text-white uppercase text-[11px] tracking-wider">
                                    Kalkulator</p>
                            </div>
                            <nav class="flex flex-col space-y-1.5 text-xs">
                                <a href="{{ route('kalkulator.hpp') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kalkulator
                                    HPP</a>
                                <a href="{{ route('kalkulator.bep') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kalkulator
                                    BEP</a>
                                <a href="{{ route('kalkulator.harga-jual') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kalkulator
                                    Harga Jual</a>
                                <a href="{{ route('kalkulator.laba-bersih') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kalkulator
                                    Laba Bersih</a>
                                <a href="{{ route('kalkulator.pph-final') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">PPh
                                    Final UMKM 0.5%</a>
                                <a href="{{ route('kalkulator.simulasi-what-if') }}"
                                    class="text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors font-medium py-0.5">Simulasi
                                    What-If</a>
                            </nav>
                        </div>

                        <!-- Col B: Solusi & Template -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-5 h-5 rounded-[6px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                                    <i data-lucide="store" class="w-3 h-3"></i>
                                </div>
                                <p class="font-bold text-black dark:text-white uppercase text-[11px] tracking-wider">
                                    Solusi POS</p>
                            </div>
                            <nav class="flex flex-col space-y-1.5 text-xs">
                                <a href="{{ route('solusi.show', 'kasir-warung') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kasir
                                    Toko &amp; Warung</a>
                                <a href="{{ route('solusi.show', 'kasir-cafe-kecil') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kasir
                                    Kafe &amp; Kopi</a>
                                <a href="{{ route('solusi.show', 'kasir-laundry') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kasir
                                    Laundry</a>
                                <a href="{{ route('solusi.show', 'kasir-irt') }}"
                                    class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors py-0.5">Kasir
                                    Industri IRT</a>
                                <a href="{{ route('template.index') }}"
                                    class="text-[#007AFF] dark:text-[#0A84FF] font-semibold hover:underline transition-colors py-0.5">Download
                                    Excel →</a>
                            </nav>
                        </div>

                        <!-- Col C: Modul Cooca UMKM (Full width of subgrid on mobile, single col on sm/desktop) -->
                        <div
                            class="col-span-2 sm:col-span-1 space-y-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-black/5 dark:border-white/10">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-5 h-5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                    <i data-lucide="layers" class="w-3 h-3"></i>
                                </div>
                                <p class="font-bold text-black dark:text-white uppercase text-[11px] tracking-wider">
                                    Modul Cooca UMKM</p>
                            </div>
                            <nav class="grid grid-cols-2 sm:grid-cols-1 gap-1.5 text-xs">
                                <a href="{{ route('kalkulator.index') }}"
                                    class="hover:text-black dark:hover:text-white transition-colors flex items-center gap-1 py-0.5 truncate">
                                    <span class="truncate">Kalkulator HPP &amp; BEP</span>
                                </a>
                                <a href="{{ route('solusi.show', 'kasir-warung') }}"
                                    class="hover:text-black dark:hover:text-white transition-colors py-0.5 truncate">POS Kasir &amp; Toko</a>
                                <a href="{{ route('template.index') }}"
                                    class="hover:text-black dark:hover:text-white transition-colors py-0.5 truncate">Template Excel UMKM</a>
                                <a href="{{ route('blog.index') }}"
                                    class="hover:text-black dark:hover:text-white transition-colors py-0.5 truncate">Blog Edukasi Bisnis</a>
                                <a href="{{ route('contact') }}"
                                    class="col-span-2 sm:col-span-1 text-[#007AFF] dark:text-[#0A84FF] hover:underline font-medium py-0.5 truncate">Konsultasi
                                    Gratis →</a>
                            </nav>
                        </div>

                    </div>
                </div>

                <!-- Bottom Copyright & Links -->
                <div
                    class="border-t border-black/5 dark:border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-black/40 dark:text-white/40">
                    <p class="text-center sm:text-left">© 2026 COOCA.ID. Platform Bisnis UMKM Indonesia. All rights
                        reserved.</p>
                    <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-4 gap-y-2">
                        <a href="{{ route('contact') }}"
                            class="hover:text-black dark:hover:text-white transition-colors">Kontak</a>
                        <a href="{{ route('sitemap.html') }}"
                            class="hover:text-black dark:hover:text-white transition-colors">Peta Situs</a>
                        <a href="{{ route('sitemap.xml') }}" target="_blank"
                            class="hover:text-black dark:hover:text-white transition-colors">Sitemap XML</a>
                        <a href="{{ route('public.privacy') }}"
                            class="hover:text-black dark:hover:text-white transition-colors">Privasi</a>
                        <a href="{{ route('public.terms') }}"
                            class="hover:text-black dark:hover:text-white transition-colors">Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    @if (!($hideBottomNav ?? false))
        <!-- ═══ APPLE FLOATING DOCK BOTTOM NAVBAR (MOBILE ONLY) ═══ -->
        <nav class="fixed bottom-0 inset-x-0 z-40 pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))] px-4 sm:px-6 lg:px-8 pointer-events-none lg:hidden"
            aria-label="Navigasi Bawah">
            <div class="max-w-7xl mx-auto pointer-events-auto">
                <div
                    class="w-full bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] rounded-[24px] sm:rounded-[28px] px-2 sm:px-6 lg:px-8 py-2 sm:py-2.5 shadow-[0_12px_40px_-6px_rgba(0,0,0,0.18),0_2px_8px_rgba(0,0,0,0.06)] dark:shadow-[0_16px_48px_-6px_rgba(0,0,0,0.7)] transition-all duration-300">
                    <div class="grid grid-cols-5 items-center w-full">

                        <!-- 1. Beranda -->
                        @php
                            $isHomeActive = request()->routeIs('landing') || request()->is('/');
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center">
                            <a href="{{ route('landing') }}"
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isHomeActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#8E8E93] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                                <div
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isHomeActive ? 'bg-[#007AFF]/10 dark:bg-[#0A84FF]/20 shadow-sm' : 'group-hover:bg-black/5 dark:group-hover:bg-white/5' }}">
                                    <i data-lucide="home"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isHomeActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                </div>
                                <span
                                    class="text-[10.5px] sm:text-xs font-semibold tracking-tight mt-0.5 {{ $isHomeActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : '' }}">Beranda</span>
                                @if ($isHomeActive)
                                    <span
                                        class="w-1 h-1 rounded-full bg-[#007AFF] dark:bg-[#0A84FF] mt-0.5 animate-pulse"></span>
                                @else
                                    <span class="w-1 h-1 mt-0.5 opacity-0"></span>
                                @endif
                            </a>
                        </div>

                        <!-- 2. Kalkulator -->
                        @php
                            $isCalcActive = request()->routeIs('kalkulator*');
                        @endphp
                        <div class="col-span-1 flex flex-col items-center justify-center">
                            <a href="{{ route('kalkulator.index') }}"
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isCalcActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#8E8E93] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                                <div
                                    class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isCalcActive ? 'bg-[#007AFF]/10 dark:bg-[#0A84FF]/20 shadow-sm' : 'group-hover:bg-black/5 dark:group-hover:bg-white/5' }}">
                                    <i data-lucide="calculator"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isCalcActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                    <!-- Badge 8 Tools -->
                                    <span
                                        class="absolute -top-1 -right-1 px-1.5 py-0.2 bg-gradient-to-r from-[#007AFF] to-[#5856D6] text-white text-[8.5px] font-extrabold rounded-full shadow-sm leading-tight border border-white dark:border-[#1C1C1E]">
                                        8
                                    </span>
                                </div>
                                <span
                                    class="text-[10.5px] sm:text-xs font-semibold tracking-tight mt-0.5 {{ $isCalcActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : '' }}">Kalkulator</span>
                                @if ($isCalcActive)
                                    <span
                                        class="w-1 h-1 rounded-full bg-[#007AFF] dark:bg-[#0A84FF] mt-0.5 animate-pulse"></span>
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
                                class="w-12 h-12 sm:w-13 sm:h-13 rounded-[20px] sm:rounded-[22px] bg-gradient-to-tr from-[#007AFF] via-[#0A84FF] to-[#5856D6] text-white flex items-center justify-center shadow-[0_8px_20px_rgba(0,122,255,0.45)] dark:shadow-[0_8px_24px_rgba(10,132,255,0.55)] ring-4 ring-[#F5F5F7] dark:ring-[#121214] active:scale-90 active:shadow-inner transition-all duration-200 group"
                                title="Solusi Bisnis & POS Kasir">
                                <i data-lucide="store"
                                    class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-white transition-transform group-hover:scale-110 group-active:scale-90 stroke-[2.2]"></i>
                            </a>
                            <span
                                class="text-[10.5px] sm:text-xs font-bold tracking-tight mt-1.5 {{ $isSolusiActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#1D1D1F] dark:text-[#F5F5F7]' }}">Solusi
                                POS</span>
                            @if ($isSolusiActive)
                                <span
                                    class="w-1 h-1 rounded-full bg-[#007AFF] dark:bg-[#0A84FF] mt-0.5 animate-pulse"></span>
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
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isTemplateActive ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#8E8E93] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                                <div
                                    class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isTemplateActive ? 'bg-[#34C759]/10 dark:bg-[#30D158]/20 shadow-sm' : 'group-hover:bg-black/5 dark:group-hover:bg-white/5' }}">
                                    <i data-lucide="file-spreadsheet"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isTemplateActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                    <!-- Micro emerald indicator -->
                                    <span
                                        class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-[#34C759] dark:bg-[#30D158] border border-white dark:border-[#1C1C1E]"></span>
                                </div>
                                <span
                                    class="text-[10.5px] sm:text-xs font-semibold tracking-tight mt-0.5 {{ $isTemplateActive ? 'text-[#34C759] dark:text-[#30D158]' : '' }}">Template</span>
                                @if ($isTemplateActive)
                                    <span
                                        class="w-1 h-1 rounded-full bg-[#34C759] dark:bg-[#30D158] mt-0.5 animate-pulse"></span>
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
                                class="w-full flex flex-col items-center justify-center py-1 px-1 rounded-[20px] transition-all duration-200 active:scale-95 group relative {{ $isAccountActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#8E8E93] dark:text-[#98989D] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]' }}">
                                <div
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-all duration-200 {{ $isAccountActive ? 'bg-[#007AFF]/10 dark:bg-[#0A84FF]/20 shadow-sm' : 'group-hover:bg-black/5 dark:group-hover:bg-white/5' }}">
                                    <i data-lucide="{{ $tabIcon }}"
                                        class="w-[19px] h-[19px] sm:w-5 sm:h-5 transition-transform group-active:scale-90 {{ $isAccountActive ? 'stroke-[2.2]' : 'stroke-[1.75]' }}"></i>
                                </div>
                                <span
                                    class="text-[10.5px] sm:text-xs font-semibold tracking-tight mt-0.5 {{ $isAccountActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : '' }}">{{ $tabLabel }}</span>
                                @if ($isAccountActive)
                                    <span
                                        class="w-1 h-1 rounded-full bg-[#007AFF] dark:bg-[#0A84FF] mt-0.5 animate-pulse"></span>
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
            @if ($errors->any())
                if (window.AppAlert) AppAlert.error(@json($errors->first()));
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
