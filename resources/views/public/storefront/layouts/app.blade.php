@php
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $activePages = $landingPage->getActivePages();
    $currentRoute = request()->path();
    $currentLocale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#0B0F19' : ($activeTheme['bg_color'] ?? '#FAF7F2') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Apple HIG Mobile Web App Compatibility --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $business->name }}">

    {{-- SEO & Social Media Metadata Architecture --}}
    @php
        $defaultBrandImg = $business->logo_url ?: ($landingPage->logo_url ?: $landingPage->hero_image_url);
    @endphp
    @include('public.partials.seo', [
        'siteName' => $business->name,
        'fallbackImage' => $defaultBrandImg ?: asset('assets/seo/cooca-og-default.jpg'),
        'url' => $canonicalUrl ?? (View::hasSection('canonical') ? trim((string) View::yieldContent('canonical')) : url('/' . $business->slug)),
        'title' => $pageTitle ?? (View::hasSection('title') ? trim((string) View::yieldContent('title')) : ($landingPage->meta_title ?: $business->name . ' - ' . ($landingPage->headline ?: 'Toko Online Resmi'))),
        'description' => $ogDescription ?? (View::hasSection('description') ? trim((string) View::yieldContent('description')) : ($landingPage->meta_description ?: ($landingPage->subheadline ?: ($business->description ?: 'Belanja aneka produk dan layanan berkualitas langsung dari ' . $business->name . ' dengan jaminan kualitas dan pengiriman terpercaya.')))),
        'keywords' => View::hasSection('keywords') ? trim((string) View::yieldContent('keywords')) : ($landingPage->meta_keywords ?? null),
    ])

    @stack('seo')

    {{-- Dynamic Google Fonts Loader (§PRD-07 & 25 Industry Themes) --}}
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
                        sans: ['"{{ $activeTheme['font_body'] }}"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
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

    {{-- Global JavaScript Bridge: i18n & Context Injection --}}
    <script>
        window.COOCA_I18N = @js(__('storefront.messages'));
        window.COOCA_LANG = @js($currentLocale);
        window.COOCA_CURRENCY = 'IDR';
        window.COOCA_BUSINESS_ID = @js($business->id);
    </script>

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

        html {
            color-scheme: light;
        }

        html.dark,
        .dark {
            color-scheme: dark;
        }

        /* Universal Apple HIG Form Controls & Dropdown Styling (§Dark Mode Accessibility) */
        select {
            color-scheme: light;
        }

        select option,
        select optgroup {
            background-color: #FFFFFF;
            color: #000000;
        }

        html.dark select,
        .dark select {
            color-scheme: dark;
        }

        html.dark select option,
        html.dark select optgroup,
        .dark select option,
        .dark select optgroup {
            background-color: #1C1C1E !important;
            color: #FFFFFF !important;
        }

        html.dark select option:checked,
        .dark select option:checked {
            background-color: #007AFF !important;
            color: #FFFFFF !important;
        }

        html.dark select option:hover,
        html.dark select option:focus,
        .dark select option:hover,
        .dark select option:focus {
            background-color: #2C2C2E !important;
            color: #FFFFFF !important;
        }

        html.dark select option:disabled,
        .dark select option:disabled {
            background-color: #1C1C1E !important;
            color: rgba(235, 235, 245, 0.38) !important;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--theme-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: var(--font-heading);
        }

        .font-mono, .tabular-nums {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
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

    {{-- Universal Typography Hierarchy --}}
    @include('layouts.partials.typography')

    @stack('styles')
</head>

<body class="min-h-screen flex flex-col text-neutral-900 dark:text-neutral-100 transition-colors duration-300 antialiased selection:bg-theme-primary selection:text-white"
    x-data="{
        mobileMenuOpen: false
    }">

    {{-- 1. Private Draft Preview Banner --}}
    @if (!$landingPage->is_published)
        <div class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Bisnis &mdash; Halaman ini masih berstatus draft privat dan belum dipublikasikan ke umum.</span>
        </div>
    @endif

    {{-- 2. Announcement Bar (If configured) --}}
    @if ($landingPage->announcement_badge)
        <div class="bg-gradient-to-r from-theme-primary to-theme-accent text-white text-xs sm:text-sm font-medium py-2 px-4 text-center tracking-wide shadow-sm flex items-center justify-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>{{ $landingPage->announcement_badge }}</span>
        </div>
    @endif

    {{-- 3. Store Operating Days Status Alert --}}
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
        <div class="bg-amber-500/10 border-b border-amber-500/20 py-2 px-4 text-amber-800 dark:text-amber-300 text-xs text-center flex items-center justify-center gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-amber-500 shrink-0"></i>
            <span>{{ __('storefront.contact.closed_today') }} ({{ $dayMap[$currentDayOfWeek] ?? '' }}). {{ __('storefront.messages.order_placed') }}</span>
        </div>
    @endif

    {{-- 4. Bento Apple HIG Navbar (§PRD-07 & §PRD-20) --}}
    @include('public.storefront.layouts.navbar')

    {{-- 5. Main Content Area --}}
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    {{-- 6. Reusable Bento Widgets & Floating Elements --}}
    @include('public.storefront.components.cart_floating_pill')

    @if ($hasWhatsapp)
        @include('public.storefront.components.whatsapp_inquiry', [
            'hasWhatsapp' => $hasWhatsapp,
            'business' => $business,
            'landingPage' => $landingPage,
            'mode' => 'floating'
        ])
    @endif

    @include('public.storefront.components.order_track_modal', ['business' => $business])

    {{-- 7. Bento Storefront Footer --}}
    @include('public.storefront.layouts.footer')

    {{-- 8. Alpine.js Global Cart Store --}}
    <script>
        document.addEventListener('alpine:init', () => {
            const STORAGE_KEY = 'cooca_cart_{{ $business->id }}';

            Alpine.store('cart', {
                items: [],

                init() {
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
                    return this.items.reduce((acc, item) => acc + ((parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1)), 0);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
