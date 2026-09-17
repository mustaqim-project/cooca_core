@php
    // Normalize CMS booleans, including string "false" / "0"; default to light.
    $initialDarkMode = filter_var($landingPage->dark_mode ?? false, FILTER_VALIDATE_BOOLEAN);
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth {{ $initialDarkMode ? 'dark' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="{{ $initialDarkMode ? '#000000' : '#F2F2F7' }}">
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
    @php
        $canonicalBrandLogo = $business->logo_url ?: $landingPage->logo_url;
    @endphp
    @if ($landingPage->og_image_url || $landingPage->hero_image_url || $canonicalBrandLogo)
        <meta property="og:image"
            content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $canonicalBrandLogo) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $landingPage->meta_title ?: $business->name }}">
    @if ($landingPage->meta_description ?: $landingPage->subheadline)
        <meta name="twitter:description" content="{{ $landingPage->meta_description ?: $landingPage->subheadline }}">
    @endif
    @if ($landingPage->og_image_url || $landingPage->hero_image_url || $canonicalBrandLogo)
        <meta name="twitter:image"
            content="{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: $canonicalBrandLogo) }}">
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

        if ($canonicalBrandLogo || $landingPage->hero_image_url) {
            $localBusinessSchema['image'] = $canonicalBrandLogo ?: $landingPage->hero_image_url;
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

    {{-- Fonts: System Fonts & Custom Google Fonts --}}
    @php
        $configuredFont = $landingPage->font_family ?: 'Inter';
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family={{ urlencode($configuredFont) }}:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Anti-FOUC Theme Script (Strictly synchronized with Website & Toko CMS Settings) --}}
    <script>
        var isLandingDark = {{ $initialDarkMode ? 'true' : 'false' }};
        (function() {
            try {
                // Clear any legacy client overrides to ensure Website & Toko CMS is the authoritative Single Source of Truth
                try {
                    localStorage.removeItem('cooca_storefront_theme_{{ $business->id }}');
                    localStorage.removeItem('cooca_storefront_theme');
                } catch (e) {}

                window.storefrontTheme = {
                    initialDark: isLandingDark,
                    apply(isDark) {
                        const dark = isDark === true;
                        const root = document.documentElement;
                        root.classList.toggle('dark', dark);
                        root.style.colorScheme = dark ? 'dark' : 'light';
                        const browserTheme = document.querySelector('meta[name="theme-color"]');
                        if (browserTheme) browserTheme.content = dark ? '#000000' : '#F2F2F7';
                    }
                };
                // Backend is authoritative on every load; device preference is not an override.
                window.storefrontTheme.apply(window.storefrontTheme.initialDark);
            } catch (e) {}
        })();
    </script>

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
    ?>

    {{-- Load the CDN before assigning config so class-based dark mode is registered. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"{{ $configuredFont }}"', '-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"',
                            '"SF Pro Text"', '"Inter"',
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
                            200: 'rgba({{ $themeRgb }}, 0.2)',
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

    {{-- Anti-FOUC, Cloak, and Core Bento Styling --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --primary: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
            --primary-rgb: {{ $themeRgb }};
            --brand-primary: {{ $themeColor }};
            --brand-rgb: {{ $themeRgb }};
        }

        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .bg-brand-primary {
            background-color: var(--primary-color) !important;
        }

        .text-brand-primary {
            color: var(--primary-color) !important;
        }

        .border-brand-primary {
            border-color: var(--primary-color) !important;
        }

        .focus\:ring-brand-primary:focus {
            --tw-ring-color: var(--primary-color) !important;
        }

        /* Apple Modular UI Design System */
        .bento-card {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 12px 28px -4px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dark .bento-card,
        html.dark .bento-card {
            background-color: rgba(28, 28, 30, 0.85) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2), 0 14px 32px -4px rgba(0, 0, 0, 0.35) !important;
        }

        .no-scrollbar::-webkit-scrollbar,
        .scrollbar-none::-webkit-scrollbar {
            display: none !important;
        }

        .no-scrollbar,
        .scrollbar-none {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
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

        /* STOREFRONT REFINEMENT START
   Presentation only. Keep Blade, Alpine state, routes and slider geometry intact.
   Brand variables are supplied by the existing template; no industry is hardcoded.
*/
        :root {
            --sf-canvas: #f2f2f7;
            --sf-surface: #ffffff;
            --sf-subtle: #eaeaef;
            --sf-ink: #1d1d1f;
            --sf-muted: #626268;
            --sf-border: #dedee5;
            --sf-header: rgba(250, 250, 252, .92);
            --sf-field: #f2f2f7;
            --sf-shadow: 0 2px 4px rgba(24, 33, 47, .025), 0 12px 32px -20px rgba(24, 33, 47, .16);
            --sf-shadow-hover: 0 16px 36px -20px rgba(24, 33, 47, .25);
            --sf-radius: 20px;
        }

        html.dark {
            color-scheme: dark;
            --sf-canvas: #000000;
            --sf-surface: #1c1c1e;
            --sf-subtle: #252528;
            --sf-ink: #f5f5f7;
            --sf-muted: #b5b5bd;
            --sf-border: #38383d;
            --sf-header: rgba(28, 28, 30, .92);
            --sf-field: #2c2c2e;
            --sf-shadow: 0 4px 20px -12px rgba(0, 0, 0, .45);
            --sf-shadow-hover: 0 20px 36px -20px rgba(0, 0, 0, .65);
        }

        html:not(.dark) {
            color-scheme: light;
        }

        html {
            scroll-padding-top: 100px;
        }

        body[x-data] {
            background-color: var(--sf-canvas);
            color: var(--sf-ink);
            line-height: 1.65;
            text-rendering: optimizeLegibility;
        }

        body>section[id] {
            scroll-margin-top: 100px;
        }

        body>section.py-12 {
            padding-block: clamp(3.25rem, 6vw, 5.5rem);
        }

        body>section> :is(.max-w-6xl, .max-w-7xl),
        body>header>.max-w-6xl,
        body>footer>.max-w-6xl {
            max-width: 1200px;
            padding-inline: clamp(1rem, 3.4vw, 2.5rem);
        }

        body>section :is(h1, h2, h3, h4),
        body>footer :is(p, a),
        body [role="dialog"] :is(h2, h3, p) {
            overflow-wrap: anywhere;
        }

        body>section :is(h1, h2) {
            text-wrap: balance;
        }

        body>section h2 {
            font-size: clamp(1.65rem, 3vw, 2.5rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -.035em;
        }

        body>section :is(h1, h2, h3, h4).text-black {
            color: var(--sf-ink);
        }

        html.dark body>section :is(h1, h2, h3, h4).text-black {
            color: var(--sf-ink);
        }

        body>section>div>.text-center>span.uppercase {
            color: var(--sf-muted);
            font-size: 11px;
            letter-spacing: .14em;
            margin-bottom: 12px;
        }

        body>section>div>.text-center>p {
            color: var(--sf-muted);
            line-height: 1.8;
            margin-top: 14px;
        }

        /* Navigation: same links, same breakpoints, quieter surfaces. */
        body>header {
            background-color: var(--sf-header) !important;
            border-color: var(--sf-border) !important;
            box-shadow: 0 4px 24px -20px rgba(24, 33, 47, .2);
            -webkit-backdrop-filter: blur(16px);
            backdrop-filter: blur(16px);
        }

        body>header>div:first-child {
            min-height: 72px;
            height: auto;
            padding-block: 12px;
        }

        body>header a[href="#hero"] {
            min-width: 0;
        }

        body>header a[href="#hero"]> :is(img, div:first-child) {
            flex-shrink: 0;
        }

        body>header a[href="#hero"]>div:last-child {
            min-width: 0;
        }

        body>header a[href="#hero"]>div:last-child>span:first-child {
            overflow-wrap: anywhere;
        }

        body>header nav a {
            border-radius: 10px;
            padding-block: 10px;
            line-height: 1.4;
        }

        body>header>div:first-child>.flex:last-child {
            flex-shrink: 0;
        }

        /* Hero: clean editorial hierarchy with the original media and CTA structure. */
        body #hero {
            padding-top: clamp(2.5rem, 5.5vw, 5rem);
            padding-bottom: clamp(3rem, 6vw, 5.5rem);
            background-image: radial-gradient(ellipse at 90% 10%, rgba(var(--primary-rgb), .065), transparent 58%);
            border-bottom: 1px solid var(--sf-border);
        }

        body #hero h1 {
            font-size: clamp(2.125rem, 4.6vw, 3.75rem);
            font-weight: 750;
            line-height: 1.1;
            letter-spacing: -.045em;
        }

        body #hero h1+p {
            color: var(--sf-muted);
            font-size: clamp(1rem, 1.4vw, 1.125rem);
            line-height: 1.85;
            padding-top: 3px;
        }

        body #hero .inline-flex.uppercase {
            font-size: 11px;
            letter-spacing: .1em;
            line-height: 1.6;
        }

        body #hero :is(a, button).h-12 {
            min-height: 50px;
            height: auto;
            padding-block: 14px;
            border-radius: 12px;
            line-height: 1.45;
            text-align: center;
        }

        body #hero :is(a, button).bg-brand-primary {
            box-shadow: 0 6px 18px -9px rgba(var(--primary-rgb), .5);
        }

        body #hero .flex.flex-wrap {
            gap: 12px 20px;
            color: var(--sf-muted);
            padding-top: 8px;
        }

        body #hero [class~="aspect-[4/3]"] {
            border-radius: 24px;
            border-color: var(--sf-border);
            box-shadow: 0 24px 56px -30px rgba(24, 33, 47, .35);
        }

        /* Surface system. Never modify slider widths, gaps or transform bindings. */
        body .bento-card,
        body .bento-card-interactive,
        html.dark body .bento-card,
        html.dark body .bento-card-interactive {
            background-color: var(--sf-surface) !important;
            border-color: var(--sf-border) !important;
            border-radius: var(--sf-radius);
            box-shadow: var(--sf-shadow) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition-property: border-color, box-shadow, background-color, transform;
            transition-duration: 200ms;
            transition-timing-function: ease;
        }

        body .bento-card :is(h3, h4) {
            letter-spacing: -.02em;
        }

        body>section .bento-card>p {
            font-size: 14px;
            line-height: 1.85;
        }

        body #layanan .bento-card-interactive :is(h3, h4) {
            font-size: 16px;
            line-height: 1.45;
        }

        body #layanan .bento-card-interactive p {
            line-height: 1.75;
        }

        body #layanan .bento-card-interactive [class~="aspect-[16/10]"] {
            border-radius: 12px;
        }

        body #layanan .overflow-x-auto>button {
            min-height: 40px;
            padding-inline: 16px;
            border-radius: 10px;
            font-size: 12px;
        }

        body #layanan input[type="search"] {
            min-height: 44px;
            background-color: var(--sf-surface);
            border-color: var(--sf-border);
            border-radius: 12px;
            font-size: 14px;
        }

        body #layanan button.whitespace-nowrap {
            min-height: 40px;
        }

        body #galeri .bento-card {
            padding: 8px;
        }

        body #galeri .bento-card>.overflow-hidden {
            border-radius: 14px;
        }

        body #tentang .whitespace-pre-wrap {
            line-height: 1.9;
            color: var(--sf-muted);
        }

        body #tentang .divide-y {
            background-color: var(--sf-canvas);
            border-color: var(--sf-border);
            border-radius: 14px;
        }

        body #tentang .divide-y>div {
            gap: 12px;
            padding-block: 13px;
        }

        body #lokasi .bento-card p {
            color: var(--sf-muted);
            line-height: 1.85;
            overflow-wrap: anywhere;
        }

        body #lokasi .flex.items-start>div:last-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        body #faq>div {
            max-width: 880px;
        }

        body #faq .bento-card {
            padding: 4px 12px;
        }

        body #faq .bento-card button {
            padding-block: 22px;
            line-height: 1.6;
        }

        body #faq .bento-card button>div {
            border-radius: 9px;
        }

        body #faq .bento-card [x-show] {
            color: var(--sf-muted);
            line-height: 1.85;
        }

        /* Shared input styling leaves validation, disabled and Alpine visibility intact. */
        body :is(input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="range"]):not([type="color"]), select, textarea) {
            border-radius: 12px;
            accent-color: var(--primary-color);
        }

        body :is(input[type="checkbox"], input[type="radio"]) {
            accent-color: var(--primary-color);
        }

        body textarea {
            line-height: 1.75;
        }

        body :is(button, a, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--primary-color) !important;
            outline-offset: 4px;
        }

        body :is(button, input, select, textarea):disabled {
            cursor: not-allowed;
        }

        body [role="dialog"] .bento-card {
            border-radius: 24px;
            box-shadow: 0 24px 80px -24px rgba(0, 0, 0, .35) !important;
        }

        body [role="dialog"] :is(input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]), select) {
            min-height: 44px;
        }

        body [role="dialog"] button[type="submit"] {
            min-height: 48px;
            border-radius: 12px;
        }

        body [role="dialog"] .overflow-y-auto {
            overscroll-behavior-y: contain;
        }

        /* Footer keeps the configured column count and original visibility. */
        body>footer {
            background-color: var(--sf-subtle) !important;
            border-color: var(--sf-border) !important;
            color: var(--sf-muted);
        }

        body>footer .grid>div {
            min-width: 0;
        }

        body>footer nav a {
            line-height: 1.8;
        }

        body>nav[aria-label="Navigasi halaman"] {
            background-color: var(--sf-header) !important;
            border-color: var(--sf-border) !important;
            border-radius: 20px;
            box-shadow: 0 8px 32px -12px rgba(0, 0, 0, .22);
        }

        body>nav[aria-label="Navigasi halaman"]>div> :is(a, button) {
            min-height: 48px;
            min-width: 0;
            gap: 3px;
        }

        @media (hover: hover) and (pointer: fine) {

            body .bento-card-interactive:hover,
            html.dark body .bento-card-interactive:hover {
                transform: translateY(-3px);
                border-color: rgba(var(--primary-rgb), .35) !important;
                box-shadow: var(--sf-shadow-hover) !important;
            }

            body>header nav a:hover {
                background-color: var(--sf-subtle);
            }

            body #faq .bento-card button:hover {
                background-color: rgba(var(--primary-rgb), .045);
                border-radius: 10px;
            }
        }

        @media (hover: none) {

            body .bento-card-interactive:hover,
            html.dark body .bento-card-interactive:hover {
                transform: none;
            }
        }

        @media (min-width: 1024px) {
            body #hero .lg\:col-span-7 {
                padding-right: 12px;
            }
        }

        @media (min-width: 768px) and (max-width: 1199px) {
            body>header>div:first-child {
                gap: 10px;
                flex-wrap: wrap;
            }

            body>header>div:first-child>nav {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                border-top: 1px solid var(--sf-border);
                padding-top: 8px;
            }

            html {
                scroll-padding-top: 150px;
            }

            body>section[id] {
                scroll-margin-top: 150px;
            }
        }

        @media (max-width: 1023px) {
            body #hero h1+p {
                margin-inline: auto;
            }

            body #hero .lg\:col-span-5 {
                max-width: 640px;
                width: 100%;
                margin-inline: auto;
            }
        }

        @media (max-width: 767px) {
            body[x-data] {
                padding-bottom: calc(100px + env(safe-area-inset-bottom, 0px));
            }

            body>header>div:first-child {
                min-height: 64px;
                gap: 8px;
                flex-wrap: wrap;
            }

            body>header a[href="#hero"] {
                flex: 1 1 130px;
            }

            body>header a[href="#hero"]>div:last-child>span:first-child {
                font-size: 13px;
            }

            body>header>div:first-child>.flex:last-child {
                gap: 6px;
            }

            body>section.py-12 {
                padding-block: 44px;
            }

            body #hero h1 {
                font-size: clamp(2rem, 7.5vw, 3rem);
                letter-spacing: -.035em;
            }

            body #hero .flex.flex-wrap {
                font-size: 11px;
                gap: 10px 14px;
            }

            body #hero [class~="aspect-[4/3]"] {
                border-radius: 20px;
            }

            body #layanan input[type="search"],
            body [role="dialog"] :is(input, select, textarea) {
                font-size: 16px;
            }

            body #faq .bento-card {
                padding-inline: 2px;
            }

            body #faq .bento-card button {
                padding: 18px 14px;
                font-size: 14px;
            }

            body #faq .bento-card [x-show] {
                padding-inline: 14px;
            }

            body>nav[aria-label="Navigasi halaman"] {
                bottom: 10px;
                inset-inline: 10px;
            }
        }

        @media (min-width: 640px) and (max-width: 1023px) {
            body>footer>div>.grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (prefers-reduced-motion: reduce) {

            html,
            body,
            body *,
            body *::before,
            body *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }

            body .bento-card-interactive:hover,
            html.dark body .bento-card-interactive:hover {
                transform: none;
            }
        }

        /* APPLE-INSPIRED ADAPTIVE UI
   Shared theme tokens apply to surfaces, not image overlays or status colors.
   Visibility and slider transforms remain controlled by the original Alpine code.
*/
        body {
            -webkit-text-size-adjust: 100%;
        }

        body :is(button, a, [role="button"]) {
            touch-action: manipulation;
        }

        body :is(button, a)>svg {
            flex-shrink: 0;
        }

        body>header>div:first-child>nav {
            min-width: 0;
        }

        body .storefront-header-actions> :is(a, button),
        body .storefront-header-actions>.relative>button {
            min-height: 44px;
            border-radius: 14px;
        }

        body .storefront-theme-toggle {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--sf-field);
            color: var(--sf-ink);
            border: 1px solid var(--sf-border);
            border-radius: 14px;
            transition: background-color .2s, border-color .2s, transform .2s;
        }

        body .storefront-theme-toggle svg {
            width: 20px;
            height: 20px;
        }

        body .storefront-theme-toggle:active {
            transform: scale(.94);
        }

        body .storefront-theme-toggle[aria-checked="true"] {
            border-color: rgba(var(--primary-rgb), .55);
        }

        body #storefront-mobile-menu {
            background-color: var(--sf-surface);
            border-color: var(--sf-border);
            max-height: calc(100vh - 160px);
            max-height: calc(100dvh - 160px - env(safe-area-inset-top, 0px));
            overflow-y: auto;
            overscroll-behavior: contain;
            padding-bottom: max(20px, env(safe-area-inset-bottom, 0px));
        }

        body #storefront-mobile-menu> :is(a, button) {
            min-height: 48px;
            padding: 12px 14px;
            border-radius: 12px;
            line-height: 1.5;
        }

        body #storefront-mobile-menu> :is(a, button).block {
            background-color: var(--sf-field);
        }

        /* Only neutral primary / secondary text gets remapped; keep semantic colors. */
        body :is(.bento-card, .bento-card-interactive, .storefront-sheet, #storefront-mobile-menu) .text-black {
            color: var(--sf-ink);
        }

        body :is(.bento-card, .bento-card-interactive, .storefront-sheet, #storefront-mobile-menu):is([class~="text-black/40"], [class~="text-black/45"], [class~="text-black/50"], [class~="text-black/55"], [class~="text-black/60"], [class~="text-black/65"]) {
            color: var(--sf-muted);
        }

        html.dark body :is(.bento-card, .bento-card-interactive, .storefront-sheet, #storefront-mobile-menu):is([class~="text-black/40"], [class~="text-black/45"], [class~="text-black/50"], [class~="text-black/55"], [class~="text-black/60"], [class~="text-black/65"]) {
            color: var(--sf-muted);
        }

        body :is(input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="file"]), select, textarea) {
            max-width: 100%;
            min-width: 0;
            color: var(--sf-ink);
            caret-color: var(--primary-color);
        }

        body :is(input, textarea)::placeholder {
            color: var(--sf-muted);
            opacity: 1;
        }

        body select option {
            color: var(--sf-ink);
            background-color: var(--sf-surface);
        }

        body .storefront-sheet {
            background-color: var(--sf-surface) !important;
            color: var(--sf-ink);
            border-color: var(--sf-border) !important;
            border-radius: 26px;
            max-height: calc(100vh - 48px);
            max-height: calc(100dvh - 48px);
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: var(--sf-muted) transparent;
        }

        body .storefront-sheet :is(h2, h3, h4, p, label) {
            overflow-wrap: break-word;
            word-break: normal;
        }

        body .storefront-sheet :is(button, .badge, [class*="rounded-full"]) {
            white-space: nowrap;
        }

        body .storefront-sheet :is(input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]), select, textarea) {
            min-height: 46px;
            background-color: var(--sf-field);
            border-color: var(--sf-border);
            border-radius: 12px;
        }

        body .storefront-sheet :is(.grid, .flex)>div {
            min-width: 0;
        }

        body .storefront-sheet .flex-1 {
            min-height: 0;
        }

        body .storefront-sheet button[type="submit"] {
            min-height: 50px;
        }

        body .storefront-sheet button:has(> :is(svg, i)[data-lucide="x"]),
            body .storefront-sheet button[aria-label="Tutup dialog"] {
                width: 44px;
                height: 44px;
                flex-shrink: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background-color: var(--sf-field);
            }

            body .storefront-sheet .overflow-y-auto {
                overscroll-behavior: contain;
            }

            body .storefront-drawer {
                border-radius: 0; max-height: 100%;
            }

            body .storefront-drawer > :first-child {
                padding-top: max(20px, env(safe-area-inset-top, 0px)); flex-shrink: 0;
            }

            body .storefront-drawer > :last-child {
                padding-bottom: max(20px, env(safe-area-inset-bottom, 0px)); flex-shrink: 0;
            }

            @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
                body > header, body > nav[aria-label="Navigasi halaman"] {
                    background-color: var(--sf-surface) !important;
                }
            }

            @media (hover: hover) and (pointer: fine) {
                body .storefront-theme-toggle:hover {
                    background-color: var(--sf-subtle);
                }

                body #storefront-mobile-menu > .block:hover {
                    background-color: var(--sf-subtle);
                }
            }

            @media (max-width: 767px) {
                body {
                    --sf-radius: 22px;
                }

                body > header {
                    padding-top: env(safe-area-inset-top, 0px);
                }

                body > header > div:first-child {
                    flex-wrap: nowrap;
                    padding: 8px 12px;
                    gap: 6px;
                }

                body > header a[href="#hero"] {
                    flex: 1 1 0%; gap: 8px;
                }

                body > header a[href="#hero"] > div:last-child > span:first-child {
                    font-size: 13px;
                    line-height: 1.35;
                    display: -webkit-box;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 2;
                    overflow: hidden;
                }

                body > header .storefront-header-actions {
                    gap: 2px !important;
                }

                body .storefront-header-actions > :is(a, button):not(.storefront-theme-toggle),
                body .storefront-header-actions > .relative > button {
                    width: 44px;
                    padding-inline: 0;
                    justify-content: center;
                }

                body .storefront-header-actions > .relative > button > .hidden,
                body .storefront-header-actions > .relative > button > [data-lucide="chevron-down"],
                body .storefront-header-actions > a[title="Masuk / Daftar Akun"] > span,
                body .storefront-header-actions > :is(a, button)[title="Reservasi"],
                    body .storefront-header-actions > :is(a, button)[title="Pesan PO"],
                        body .storefront-header-actions > :is(a, button)[title="Pesan Pre-Order"],
                            body .storefront-header-actions > :is(a, button)[title="Pesan"],
                                body .storefront-header-actions > :is(a, button)[title="Hubungi WA"] {
                                        display: none;
                                    }

                                    body .storefront-header-actions [title="Keranjang Belanja"] > span[x-show] {
                                        position: absolute;
                                        top: 1px;
                                        right: 0;
                                        min-width: 16px;
                                        padding: 1px 4px;
                                        line-height: 1.4;
                                        border: 2px solid var(--sf-surface);
                                    }

                                    body > section > :is(.max-w-6xl, .max-w-7xl) {
                                        padding-inline: 20px;
                                    }

                                    body #hero {
                                        padding-top: 32px;
                                        padding-bottom: 32px;
                                        background-image: none;
                                        border-bottom: 0;
                                    }

                                    body #hero h1 {
                                        font-size: clamp(1.875rem, 7.8vw, 2.75rem); line-height: 1.12; font-weight: 700;
                                    }

                                    body #hero h1 + p {
                                        font-size: 15px; line-height: 1.75;
                                    }

                                    body #hero :is(a, button).h-12 {
                                        min-height: 52px; border-radius: 16px; font-size: 15px;
                                    }

                                    body #hero [class~="aspect-[4/3]"] {
                                        border-radius: 24px; box-shadow: var(--sf-shadow);
                                    }

                                    body #hero .lg\:col-span-5 {
                                        padding-top: 0;
                                    }

                                    body > section.py-12 {
                                        padding-block: 28px;
                                    }

                                    body > section h2 {
                                        font-size: clamp(1.5rem, 5.6vw, 1.875rem); letter-spacing: -.025em;
                                    }

                                    body > section > div > .text-center {
                                        text-align: left;
                                    }

                                    body > section > div > .text-center > span.uppercase {
                                        font-size: 10px; letter-spacing: .1em; margin-bottom: 8px;
                                    }

                                    body > section > div > .text-center > p {
                                        font-size: 14px; margin-top: 10px; line-height: 1.7;
                                    }

                                    body #layanan > div.space-y-12 > :not([hidden]) ~ :not([hidden]) {
                                        margin-top: 28px;
                                    }

                                    body #layanan .overflow-x-auto {
                                        padding-block: 4px 8px; overscroll-behavior-x: contain;
                                    }

                                    body #layanan .overflow-x-auto > button {
                                        min-height: 44px; border-radius: 12px;
                                    }

                                    body #layanan input[type="search"] {
                                        background-color: var(--sf-surface); min-height: 46px; border-radius: 14px;
                                    }

                                    body #tentang .bento-card, body #lokasi .bento-card {
                                        padding: 22px;
                                    }

                                    body #faq .bento-card {
                                        border-radius: 20px;
                                    }

                                    body #faq .bento-card button {
                                        min-height: 64px;
                                    }

                                    body #faq .bento-card button > span {
                                        min-width: 0; overflow-wrap: anywhere;
                                    }

                                    body #galeri .bento-card {
                                        border-radius: 22px;
                                    }

                                    body > nav[aria-label="Navigasi halaman"] {
                                        bottom: max(12px, env(safe-area-inset-bottom, 12px));
                                        inset-inline: 12px;
                                        border-radius: 24px;
                                        border-width: 1px;
                                        padding: 6px 10px;
                                        box-shadow: 0 12px 36px -8px rgba(0, 0, 0, .25);
                                        -webkit-backdrop-filter: blur(24px) saturate(160%);
                                        backdrop-filter: blur(24px) saturate(160%);
                                    }

                                    body > nav[aria-label="Navigasi halaman"] > div {
                                        align-items: flex-end; gap: 4px; max-width: 540px;
                                    }

                                    body > nav[aria-label="Navigasi halaman"] > div > :is(a, button):not([class*="-mt-"]) {
                                        min-height: 46px;
                                        border-radius: 14px;
                                        gap: 3px;
                                        padding-block: 4px;
                                    }

                                    body > nav[aria-label="Navigasi halaman"] > div > :is(a, button) > span {
                                        font-size: 10px; line-height: 1.25;
                                    }

                                    body > footer {
                                        padding-bottom: 0;
                                    }

                                    body > footer > div {
                                        padding-top: 32px; padding-bottom: 32px;
                                    }

                                    body > footer .grid {
                                        gap: 28px;
                                    }

                                    body > footer nav a {
                                        min-height: 40px;
                                    }

                                    body .storefront-sheet-overlay {
                                        align-items: flex-end; padding: 0;
                                    }

                                    body .storefront-sheet-overlay > .storefront-sheet {
                                        max-width: 100%;
                                        max-height: calc(100vh - 16px);
                                        max-height: calc(100dvh - 16px - env(safe-area-inset-top, 0px));
                                        border-radius: 28px 28px 0 0;
                                        border-width: 1px 0 0;
                                        padding: 22px 20px calc(24px + env(safe-area-inset-bottom, 0px));
                                        box-shadow: 0 -16px 60px -20px rgba(0, 0, 0, .28);
                                    }

                                    body .storefront-sheet-overlay > .storefront-catalog-sheet {
                                        padding: 0 0 env(safe-area-inset-bottom, 0px);
                                    }

                                    body .storefront-sheet :is(input, select, textarea) {
                                        font-size: 16px;
                                    }

                                    body .storefront-drawer-frame {
                                        padding-left: 0; width: 100%;
                                    }

                                    body .storefront-drawer {
                                        max-width: 100%; width: 100%;
                                    }
                                }

                                @media (max-width: 374px) {
                                    body > header > div:first-child {
                                        padding-inline: 8px; gap: 4px;
                                    }

                                    body > header a[href="#hero"] {
                                        gap: 6px;
                                    }

                                    body > header a[href="#hero"] > :is(img, div:first-child) {
                                        width: 26px; height: 26px;
                                    }

                                    body > header a[href="#hero"] > div:last-child > span:first-child {
                                        font-size: 12px;
                                    }

                                    body > header a[href="#hero"] > div:last-child > .inline-flex {
                                        font-size: 9px; gap: 3px;
                                    }

                                    body > section > :is(.max-w-6xl, .max-w-7xl) {
                                        padding-inline: 16px;
                                    }

                                    body #hero .flex.flex-wrap {
                                        gap: 8px; font-size: 10px;
                                    }

                                    body #tentang .grid.grid-cols-3 {
                                        gap: 8px;
                                    }

                                    body .storefront-sheet-overlay > .storefront-sheet:not(.storefront-catalog-sheet) {
                                        padding-inline: 16px;
                                    }
                                }

                                @media (max-height: 500px) and (orientation: landscape) {
                                    body .storefront-sheet-overlay {
                                        align-items: center;
                                    }

                                    body .storefront-sheet-overlay > .storefront-sheet {
                                        max-height: calc(100dvh - 12px);
                                    }

                                    body > header > div:first-child {
                                        min-height: 56px; padding-block: 6px;
                                    }
                                }

                                @media (prefers-reduced-motion: reduce) {
                                    body .storefront-theme-toggle {
                                        transition: none;
                                    }

                                    body .storefront-theme-toggle:active {
                                        transform: none;
                                    }
                                }

                                /* STOREFRONT REFINEMENT END */
    </style>
    <?php
    
    // Multi-Industry Context Detection (25 Official Industry Templates)
    $industryPresetKey = (string) ($landingPage->industry_preset ?: ($business->template_code ?: 'retail_reseller'));
    $industryGroup = match (true) {
        str_starts_with($industryPresetKey, 'fnb') || in_array($industryPresetKey, ['resto', 'cafe', 'bakery', 'catering', 'frozen_food']) => 'fnb',
        str_starts_with($industryPresetKey, 'mfg') || in_array($industryPresetKey, ['garment', 'furniture', 'percetakan', 'bengkel', 'precision', 'craft', 'cosmetics']) => 'manufacturing',
        str_starts_with($industryPresetKey, 'service') || in_array($industryPresetKey, ['laundry', 'barbershop', 'salon', 'carwash', 'studio_foto', 'jasa', 'workshop', 'autodetailing', 'agency', 'contractor', 'event']) => 'service',
        str_starts_with($industryPresetKey, 'retail') || in_array($industryPresetKey, ['retail', 'sembako', 'fashion', 'toko_hp', 'petshop', 'apotek', 'pharmacy', 'reseller']) => 'retail',
        default => 'trading',
    };
    
    // Adaptive Content Labels based on Industry Group
    $industryLabels = match ($industryGroup) {
        'fnb' => [
            'catalog_overline' => 'Daftar Menu & Kuliner',
            'catalog_title' => 'Menu & Kuliner Pilihan',
            'services_title' => 'Menu Spesial',
            'products_title' => 'Daftar Menu & Minuman',
            'hero_primary' => 'Pesan Menu Sekarang',
            'hero_secondary' => 'Lihat Menu',
            'action_verb' => 'Pesan',
            'booking_label' => 'Reservasi Meja',
            'request_order_item_label' => 'Menu / Produk Katering / Kue',
            'request_order_qty_label' => 'Jumlah Porsi / Box / Paket',
            'request_order_date_label' => 'Tanggal Acara / Pengiriman',
        ],
        'manufacturing' => [
            'catalog_overline' => 'Katalog Produk & Spesifikasi',
            'catalog_title' => 'Katalog & Spesifikasi Produksi',
            'services_title' => 'Layanan Fabrikasi & Custom',
            'products_title' => 'Produk Jadi & Komponen',
            'hero_primary' => 'Minta Penawaran / PO',
            'hero_secondary' => 'Katalog Produk',
            'action_verb' => 'Pesan',
            'booking_label' => 'Konsultasi Teknis',
            'request_order_item_label' => 'Nama Barang / Komponen / Spesifikasi',
            'request_order_qty_label' => 'Jumlah Unit / Pcs / Lusin',
            'request_order_date_label' => 'Target Tanggal Selesai / Deadline',
        ],
        'service' => [
            'catalog_overline' => 'Pilihan Layanan & Tarif',
            'catalog_title' => 'Katalog Layanan Profesional',
            'services_title' => 'Paket Layanan',
            'products_title' => 'Produk & Perlengkapan',
            'hero_primary' => 'Booking Jadwal',
            'hero_secondary' => 'Pilihan Layanan',
            'action_verb' => 'Beli',
            'booking_label' => 'Booking Jadwal',
            'request_order_item_label' => 'Layanan / Kebutuhan Khusus',
            'request_order_qty_label' => 'Jumlah Unit / Kendaraan / Tamu',
            'request_order_date_label' => 'Target Tanggal Pengerjaan',
        ],
        'trading' => [
            'catalog_overline' => 'Katalog Pasokan & Komoditas',
            'catalog_title' => 'Distribusi & Komoditas Usaha',
            'services_title' => 'Layanan Pasokan',
            'products_title' => 'Katalog Komoditas',
            'hero_primary' => 'Minta Penawaran',
            'hero_secondary' => 'Katalog Pasokan',
            'action_verb' => 'Pesan',
            'booking_label' => 'Konsultasi Pasokan',
            'request_order_item_label' => 'Nama Komoditas / Barang Pasokan',
            'request_order_qty_label' => 'Volume / Ton / Sak / Karton',
            'request_order_date_label' => 'Target Tanggal Pengiriman',
        ],
        default => [
            'catalog_overline' => 'Etalase Produk Pilihan',
            'catalog_title' => 'Katalog & Etalase Produk',
            'services_title' => 'Layanan Unggulan',
            'products_title' => 'Katalog Produk',
            'hero_primary' => 'Belanja Sekarang',
            'hero_secondary' => 'Lihat Etalase',
            'action_verb' => 'Beli',
            'booking_label' => 'Booking / Konsul',
            'request_order_item_label' => 'Item / Produk yang Diminta',
            'request_order_qty_label' => 'Perkiraan Jumlah / Pcs',
            'request_order_date_label' => 'Target Tanggal Dibutuhkan',
        ],
    };
    
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
    
    $normalizedOperationalHours = method_exists($landingPage, 'getNormalizedOperationalHours') ? $landingPage->getNormalizedOperationalHours() : (is_array($landingPage->operational_hours ?? null) ? $landingPage->operational_hours : []);
    
    $hasHero = filled($landingPage->headline) || filled($landingPage->subheadline) || filled($landingPage->announcement_badge) || filled($landingPage->hero_image_url) || filled($landingPage->cta_primary_text) || filled($landingPage->cta_secondary_text);
    $hasAbout = filled($landingPage->about_title) || filled($landingPage->about_story) || filled($landingPage->about_image_url) || !empty($normalizedOperationalHours);
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
            'footer' => $hasContact || $services->isNotEmpty() || $business->logo_url || $landingPage->logo_url,
            'footer_brand' => filled($business->name) || filled($business->logo_url) || filled($landingPage->logo_url),
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
    
    // Operational Capabilities & Industry Logic (Automated & Dynamic based on Owner Config)
    $isReservableIndustry = in_array($industryPresetKey, ['fnb_resto', 'resto', 'fnb_cafe', 'cafe', 'klinik', 'gym']) || in_array($industryGroup, ['service']);
    $isUmkmRumahan = in_array($industryPresetKey, ['fnb_bakery', 'bakery', 'fnb_katering', 'catering', 'frozen_food', 'manufaktur_konveksi', 'garment', 'manufaktur_percetakan', 'percetakan', 'craft', 'manufaktur_furniture', 'furniture']);
    $isProductionB2b = in_array($industryGroup, ['manufacturing', 'trading']);
    
    // Storefront owner settings
    $allowStorefront = (bool) ($storeSetting?->is_storefront_enabled ?? true);
    $hasPosGoods = $posProducts->isNotEmpty();
    $hasPricedServices = $services->contains(fn($s) => !empty($s['raw_price'] ?? null) && (float) ($s['raw_price'] ?? 0) > 0);
    $allowCart = $allowStorefront && ($hasPosGoods || $hasPricedServices);
    
    // Reservation setting: can be dynamically toggled by owner in CommerceStoreSetting
    $allowReservation = (bool) ($storeSetting?->allow_reservation ?? $isReservableIndustry);
    
    // Request Order / Customer PO: can be dynamically toggled by owner
    $allowCustomerPo = (bool) ($storeSetting?->allow_customer_po ?? $isProductionB2b);
    $allowRequestOrder = (bool) ($storeSetting?->allow_request_order ?? $isUmkmRumahan || $isProductionB2b);
    // Storefront Operational & Delivery Rules
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
    
    // Free shipping threshold from active shipping rules
    $freeShippingRule = isset($shippingRules) ? $shippingRules->first(fn($r) => (float) ($r->min_order_for_free ?? 0) > 0) : null;
    $freeShippingThreshold = $freeShippingRule ? (float) $freeShippingRule->min_order_for_free : 0;
    
    // Minimum lead time date for scheduling
    $leadTimeHours = (int) ($storeSetting?->lead_time_hours ?? 0);
    $minLeadTimeDate = now()->addHours($leadTimeHours)->format('Y-m-d');
    
    // Stats config from landing page
    $statsConfig = $landingPage->stats ?? data_get($landingPage->values, 'stats', []);
    
    // Determine Single Primary Action for the single page: 'reservation', 'order', or 'wa'
    $pagePrimaryAction = match (true) {
        $allowReservation => 'reservation',
        $allowCart || $allowCustomerPo || $allowRequestOrder || $hasPosGoods => 'order',
        $hasWhatsapp => 'wa',
        default => 'order',
    };
    
    // Dynamic 3 to 5 Buttons for Mobile Bottom Navigation
    $bottomNavButtons = [];
    
    // Button 1: Home
    $bottomNavButtons[] = [
        'key' => 'home',
        'label' => 'Beranda',
        'type' => 'link',
        'href' => '#hero',
        'icon' => 'home',
        'section' => 'hero',
        'is_accent' => false,
    ];
    
    // Button 2: Catalog / Menu / Layanan
    $catIcon = match (true) {
        $industryGroup === 'fnb' => 'utensils',
        $industryGroup === 'manufacturing' => 'layers',
        $industryGroup === 'trading' => 'boxes',
        in_array($industryGroup, ['service']) => match (true) {
            str_contains($industryPresetKey, 'salon') || str_contains($industryPresetKey, 'barber') => 'scissors',
            str_contains($industryPresetKey, 'car') || str_contains($industryPresetKey, 'bengkel') => 'wrench',
            str_contains($industryPresetKey, 'klinik') => 'activity',
            default => 'sparkles',
        },
        default => 'package',
    };
    $catLabel = match (true) {
        $industryGroup === 'fnb' => 'Menu',
        in_array($industryGroup, ['service']) => 'Layanan',
        $industryGroup === 'manufacturing' => 'Katalog',
        $industryGroup === 'trading' => 'Pasokan',
        default => 'Katalog',
    };
    $bottomNavButtons[] = [
        'key' => 'catalog',
        'label' => $catLabel,
        'type' => 'link',
        'href' => '#layanan',
        'icon' => $catIcon,
        'section' => 'services',
        'is_accent' => false,
    ];
    
    // Button 3: Prominent Center / Accent Action (Reservasi OR Pesan)
    if ($allowReservation) {
        $bottomNavButtons[] = [
            'key' => 'reservation',
            'label' => 'Reservasi',
            'type' => 'reservation',
            'icon' => 'calendar',
            'is_accent' => true,
        ];
    } else {
        $bottomNavButtons[] = [
            'key' => 'order',
            'label' => 'Pesan',
            'type' => $allowCart ? 'cart' : ($allowRequestOrder ? 'request_order' : 'link_services'),
            'icon' => 'shopping-bag',
            'has_badge' => $allowCart,
            'is_accent' => true,
        ];
    }
    
    // Button 4: Contextual Supporting Action (Pesan OR WA)
    if ($allowReservation) {
        if ($allowCart) {
            $bottomNavButtons[] = [
                'key' => 'cart',
                'label' => 'Pesan',
                'type' => 'cart',
                'icon' => 'shopping-bag',
                'has_badge' => true,
                'is_accent' => false,
            ];
        } elseif ($hasWhatsapp) {
            $bottomNavButtons[] = [
                'key' => 'whatsapp',
                'label' => 'WA',
                'type' => 'whatsapp',
                'icon' => 'message-circle',
                'is_accent' => false,
            ];
        }
    } else {
        if ($hasWhatsapp) {
            $bottomNavButtons[] = [
                'key' => 'whatsapp',
                'label' => 'WA',
                'type' => 'whatsapp',
                'icon' => 'message-circle',
                'is_accent' => false,
            ];
        }
    }
    
    // Button 5: Location / Contact
    $locLabel = match (true) {
        in_array($industryGroup, ['service']) => 'Outlet',
        $industryGroup === 'manufacturing' => 'Workshop',
        $industryGroup === 'trading' => 'Gudang',
        default => 'Lokasi',
    };
    $bottomNavButtons[] = [
        'key' => 'location',
        'label' => $locLabel,
        'type' => 'link',
        'href' => '#lokasi',
        'icon' => 'map-pin',
        'section' => 'contact',
        'is_accent' => false,
    ];
    
    // Ensure within 3 to 5 buttons
    if (count($bottomNavButtons) > 5) {
        $bottomNavButtons = array_slice($bottomNavButtons, 0, 5);
    }
    $navButtonCount = count($bottomNavButtons);
    $bottomNavGridClass = match ($navButtonCount) {
        3 => 'grid-cols-3',
        4 => 'grid-cols-4',
        default => 'grid-cols-5',
    };
    $hasBottomWa = collect($bottomNavButtons)->contains('type', 'whatsapp');

    // Dynamic Storefront Content Tabs (Bento Apple HIG Segmented Navigation)
    $storefrontTabs = [];

    // 1. Catalog / Menu / Layanan
    $storefrontTabs[] = [
        'id' => 'catalog',
        'key' => 'catalog',
        'label' => $industryLabels['catalog_title'] ?? 'Katalog & Menu',
        'short_label' => $industryGroup === 'fnb' ? 'Menu' : ($industryGroup === 'service' ? 'Layanan' : 'Katalog'),
        'icon' => $catIcon,
        'badge' => ($posProducts->count() + $services->count()) > 0 ? ($posProducts->count() + $services->count()) : null,
    ];

    // 2. Pre-Order Batch Hub (Dynamic: only if scheduling / batch PO active and dates available)
    $hasBatchFeature = !empty($availableBatchDates) && ($storeSetting?->allow_scheduled_order || $storeSetting?->allow_customer_po);
    if ($hasBatchFeature) {
        $storefrontTabs[] = [
            'id' => 'batch',
            'key' => 'batch',
            'label' => 'Pre-Order Batch',
            'short_label' => 'PO Batch',
            'icon' => 'calendar',
            'badge' => count($availableBatchDates) . ' Batch',
        ];
    }

    // 3. Tentang Kami (Dynamic: only if section enabled and story/description exists)
    $hasAboutContent = ($sectionVisibility['about'] ?? true) && filled($landingPage->about_story ?: $business->description);
    if ($hasAboutContent) {
        $storefrontTabs[] = [
            'id' => 'about',
            'key' => 'about',
            'label' => 'Tentang Kami',
            'short_label' => 'Tentang',
            'icon' => 'book-open',
            'badge' => null,
        ];
    }

    // 4. Galeri Suasana (Dynamic: only if section enabled and images exist)
    $hasGalleryContent = ($sectionVisibility['gallery'] ?? true) && $allGalleryImages->isNotEmpty();
    if ($hasGalleryContent) {
        $storefrontTabs[] = [
            'id' => 'gallery',
            'key' => 'gallery',
            'label' => 'Galeri Foto',
            'short_label' => 'Galeri',
            'icon' => 'image',
            'badge' => $allGalleryImages->count() > 0 ? $allGalleryImages->count() : null,
        ];
    }

    // 5. Info, Jam Buka & Lokasi (Dynamic: only if contact section enabled)
    if ($sectionVisibility['contact'] ?? true) {
        $storefrontTabs[] = [
            'id' => 'info',
            'key' => 'info',
            'label' => 'Info & Lokasi',
            'short_label' => 'Lokasi',
            'icon' => 'map-pin',
            'badge' => null,
        ];
    }

    // 6. View All Tab (Option to view continuous page)
    $storefrontTabs[] = [
        'id' => 'all',
        'key' => 'all',
        'label' => 'Semua Bagian',
        'short_label' => 'Semua',
        'icon' => 'layout-grid',
        'badge' => null,
    ];
    ?>
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
                activeMainTab: (function() {
                    try {
                        const urlParams = new URLSearchParams(window.location.search);
                        const tabParam = urlParams.get('tab');
                        if (tabParam) return tabParam;
                        if (urlParams.has('batch')) return 'batch';
                    } catch(e) {}
                    return 'catalog';
                })(),
                setMainTab(tabKey) {
                    this.activeMainTab = tabKey;
                    try {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', tabKey);
                        window.history.replaceState({}, '', url);
                    } catch(e) {}
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        const tabContainer = document.getElementById('storefront-tabs-bar');
                        if (tabContainer) {
                            const rect = tabContainer.getBoundingClientRect();
                            if (rect.top < 0) {
                                tabContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }
                    });
                },
                isDark: @json($initialDarkMode),
                applyTheme(dark) {
                    this.isDark = dark === true;
                    window.storefrontTheme.apply(this.isDark);
                },
                toggleTheme() {
                    this.applyTheme(!this.isDark);
                },
                init() {
                    this.applyTheme(@json($initialDarkMode));
                    this.$watch('isDark', (dark) => window.storefrontTheme.apply(dark));
                    this.$watch('checkoutModalOpen', (val) => {
                        if (val) this.checkoutStep = 1;
                    });
                    this.initGroupOrder();
                    const sections = document.querySelectorAll('section[data-section]');
                    if ('IntersectionObserver' in window && sections.length) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    this.activeSection = entry.target.dataset.section;
                                }
                            });
                        }, {
                            threshold: 0.25
                        });
                        sections.forEach(s => observer.observe(s));
                    }
                    this.$watch('activeModal', (val) => {
                        if (val) {
                            document.body.classList.add('overflow-hidden');
                        } else {
                            document.body.classList.remove('overflow-hidden');
                        }
                    });
                },
                productCategory: 'all',
                productSearch: '',
                products: {{ Js::from($productPayload) }},
                filteredProducts: {{ Js::from($productPayload) }},
                serviceCategory: 'all',
                serviceSearch: '',
                services: {{ Js::from($services) }},
                filteredServices: {{ Js::from($services) }},
                filterProducts() {
                    let products = this.products;
                    if (this.productCategory !== 'all') {
                        products = products.filter(product => String(product.category_id) === String(this
                            .productCategory));
                    }
                    if (this.productSearch.trim()) {
                        const query = this.productSearch.trim().toLowerCase();
                        products = products.filter(product =>
                            (product.name || '').toLowerCase().includes(query) ||
                            ((product.description || '')).toLowerCase().includes(query) ||
                            ((product.category || '')).toLowerCase().includes(query)
                        );
                    }
                    this.filteredProducts = products;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                setProductCategory(catId) {
                    this.productCategory = String(catId);
                    this.filterProducts();
                },
                clearProductFilter() {
                    this.productCategory = 'all';
                    this.productSearch = '';
                    this.filterProducts();
                },
                filterServices() {
                    let list = this.services;
                    if (this.serviceCategory !== 'all') {
                        list = list.filter(s => String(s.category_id) === String(this.serviceCategory));
                    }
                    if (this.serviceSearch.trim()) {
                        const query = this.serviceSearch.trim().toLowerCase();
                        list = list.filter(s =>
                            (s.title || s.name || '').toLowerCase().includes(query) ||
                            ((s.description || '')).toLowerCase().includes(query) ||
                            ((s.category || '')).toLowerCase().includes(query)
                        );
                    }
                    this.filteredServices = list;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                setServiceCategory(catId) {
                    this.serviceCategory = String(catId);
                    this.filterServices();
                },
                clearServiceFilter() {
                    this.serviceCategory = 'all';
                    this.serviceSearch = '';
                    this.filterServices();
                },
                openAllProductsModal(catId = null) {
                    if (catId !== null) {
                        this.productCategory = String(catId);
                    }
                    this.filterProducts();
                    this.activeModal = 'all-products';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                openAllServicesModal(catId = null) {
                    if (catId !== null) {
                        this.serviceCategory = String(catId);
                    }
                    this.filterServices();
                    this.activeModal = 'all-services';
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
                allowCustomerPo: {{ json_encode($storeSetting?->allow_customer_po ?? true) }},
                cartDrawerOpen: false,
                checkoutModalOpen: {{ json_encode($openCheckoutModal ?? false) }},
                requestOrderModalOpen: false,
                reservationModalOpen: false,
                customerPoModalOpen: false,
                isSubmittingReservation: false,
                reservationSuccess: false,
                reservationError: null,
                isCheckingOut: false,
                checkoutError: null,
                checkoutStep: 1,
                goToCheckoutStep(step) {
                    if (step === 2) {
                        if (!this.checkoutForm.customer_name || !this.checkoutForm.customer_name.trim()) {
                            this.showToast('Silakan isi nama lengkap pemesan');
                            return;
                        }
                        if (!this.checkoutForm.customer_phone || !this.checkoutForm.customer_phone.trim()) {
                            this.showToast('Silakan isi nomor WhatsApp pemesan');
                            return;
                        }
                        if (this.checkoutForm.fulfillment_type === 'merchant_delivery' && (!this.checkoutForm.shipping_address || !this.checkoutForm.shipping_address.trim())) {
                            this.showToast('Silakan lengkapi alamat pengiriman');
                            return;
                        }
                        const hasScheduling = this.checkoutForm.is_scheduled || this.hasPreorderItems;
                        if (!hasScheduling) {
                            this.checkoutStep = 3;
                            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                            return;
                        }
                    }
                    if (step === 3) {
                        if (this.checkoutForm.is_scheduled && !this.checkoutForm.scheduled_date) {
                            this.showToast('Silakan tentukan jadwal batch atau tanggal pengiriman');
                            return;
                        }
                    }
                    this.checkoutStep = step;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                cart: @json($dbCartItems ?? null) || JSON.parse(localStorage.getItem(
                    'cooca_cart_{{ $business->id }}') || '[]'),
                @php
                    $authCust = auth('customer')->user();
                    $initialGroupOrderData = $activeGroupOrder ? [
                        'id' => $activeGroupOrder->id,
                        'title' => $activeGroupOrder->title,
                        'share_token' => $activeGroupOrder->share_token,
                        'status' => $activeGroupOrder->status,
                        'is_open' => $activeGroupOrder->isOpen(),
                        'is_locked' => $activeGroupOrder->isLocked(),
                        'is_checked_out' => $activeGroupOrder->isCheckedOut(),
                        'is_host' => $activeGroupOrder->isHost($authCust),
                        'host_name' => $activeGroupOrder->host?->name ?? 'Host',
                        'scheduled_date' => $activeGroupOrder->scheduled_date?->toDateString(),
                        'subtotal' => (float) $activeGroupOrder->subtotal,
                        'total_quantity' => (float) $activeGroupOrder->total_quantity,
                        'members_count' => (int) $activeGroupOrder->members_count,
                    ] : null;
                    $initialGroupOrderSplitBill = $activeGroupOrder ? $activeGroupOrder->getSplitBillSummary() : [];
                    $initialGroupOrderToken = $groupOrderToken ?? ($activeGroupOrder?->share_token ?? '');
                @endphp
                isCustomerLoggedIn: {{ auth('customer')->check() ? 'true' : 'false' }},
                customerLoginUrl: '{{ route('customer.login') }}?redirect=' + encodeURIComponent(window.location.href),
                isGroupOrderCheckout: false,
                groupOrder: {
                    active: {{ !empty($activeGroupOrder) ? 'true' : 'false' }},
                    token: {!! json_encode($initialGroupOrderToken) !!},
                    data: {!! json_encode($initialGroupOrderData) !!},
                    splitBill: {!! json_encode($initialGroupOrderSplitBill) !!},
                    isDrawerOpen: false,
                    isCreateModalOpen: false,
                    isSplitBillOpen: false,
                    orderTrackingUrl: '{{ $activeGroupOrder?->order ? route('public.storefront.order.track', ['slug' => $business->slug, 'token' => $activeGroupOrder->order->tracking_token]) : '' }}',
                    pollTimer: null,
                    itemNotes: '',
                    createForm: {
                        title: '',
                        scheduled_date: '{{ $selectedBatchDate ?: (!empty($availableBatchDates) ? $availableBatchDates[0]['date'] : '') }}',
                        scheduled_time_slot: '09:00 - 12:00',
                        delivery_address: @json($authCust?->shipping_address ?? ''),
                        delivery_notes: '',
                        is_submitting: false,
                        error: null
                    },
                    actionLoading: false,
                    actionError: null
                },
                currentBatchDate: '{{ !empty($selectedBatchDate) ? $selectedBatchDate : (!empty($availableBatchDates) ? $availableBatchDates[0]['date'] : '') }}',
                selectBatch(date) {
                    this.checkoutForm.scheduled_date = date;
                    this.currentBatchDate = date;
                },
                copyBatchLink(date, day, formatted) {
                    const targetDate = date || this.checkoutForm.scheduled_date || this.currentBatchDate;
                    const baseUrl = window.location.origin + window.location.pathname;
                    const groupParam = (this.checkoutForm.group_name || @json($groupRef ?? '')).trim();
                    let shareUrl = baseUrl + '?batch=' + encodeURIComponent(targetDate);
                    if (groupParam) {
                        shareUrl += '&group=' + encodeURIComponent(groupParam);
                    }
                    const msg = '🍱 Pre-Order {{ addslashes($business->name) }}\n' +
                              (day ? '📅 Pengiriman: ' + day + (formatted ? ', ' + formatted : '') + '\n' : '') +
                              (groupParam ? '🏢 Pesanan Kantor/Tim: ' + groupParam + '\n' : '') +
                              '✨ Yuk ikutan pesan bareng! Pilih menu favoritmu di sini:\n👉 ' + shareUrl;

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(msg).then(() => {
                            this.showToast('Tautan Pre-Order berhasil disalin! Siap dikirim ke WhatsApp kantor.');
                        }).catch(() => {
                            prompt('Salin tautan Pre-Order:', shareUrl);
                        });
                    } else {
                        prompt('Salin tautan Pre-Order:', shareUrl);
                    }
                },
                shareBatchWa(date, day, formatted) {
                    const targetDate = date || this.checkoutForm.scheduled_date || this.currentBatchDate;
                    const baseUrl = window.location.origin + window.location.pathname;
                    const groupParam = (this.checkoutForm.group_name || @json($groupRef ?? '')).trim();
                    let shareUrl = baseUrl + '?batch=' + encodeURIComponent(targetDate);
                    if (groupParam) {
                        shareUrl += '&group=' + encodeURIComponent(groupParam);
                    }
                    const msg = '🍱 Pre-Order {{ addslashes($business->name) }}\n' +
                              (day ? '📅 Pengiriman: ' + day + (formatted ? ', ' + formatted : '') + '\n' : '') +
                              (groupParam ? '🏢 Pesanan Kantor/Tim: ' + groupParam + '\n' : '') +
                              '✨ Yuk ikutan pesan bareng! Pilih menu favoritmu di sini:\n👉 ' + shareUrl;
                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(msg), '_blank');
                },
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
                    payment_gateway: 'tripay',
                    payment_channel: 'QRIS',
                    payment_method_id: '{{ $paymentMethods->first()?->id ?? '' }}',
                    notes: '',

                    group_name: @json($groupRef ?? ''),
                    is_scheduled: {{ (($isUmkmRumahan || $industryGroup === 'fnb') && ($storeSetting?->allow_scheduled_order || $storeSetting?->allow_customer_po || !empty($availableBatchDates))) ? 'true' : 'false' }},
                    scheduled_date: '{{ !empty($selectedBatchDate) ? $selectedBatchDate : (!empty($availableBatchDates) ? $availableBatchDates[0]['date'] : '') }}',
                    scheduled_time_slot: '',
                },
                requestOrderForm: {
                    customer_name: @json($authCust?->name ?? ''),
                    customer_phone: @json($authCust?->phone ?? ''),
                    customer_email: @json($authCust?->email ?? ''),
                    fulfillment_type: '{{ $storeSetting?->allow_pickup ?? true ? 'pickup' : 'merchant_delivery' }}',
                    shipping_address: @json($authCust?->shipping_address ?? ''),
                    scheduled_date: '{{ !empty($selectedBatchDate) ? $selectedBatchDate : (!empty($availableBatchDates) ? $availableBatchDates[0]['date'] : '') }}',
                    scheduled_time_slot: '',
                    notes: '',
                    item_name: '',
                    item_qty: 1,
                    item_notes: '',
                    is_submitting: false,
                    error: null,
                },
                customerPoForm: {
                    customer_name: @json($authCust?->name ?? ''),
                    customer_phone: @json($authCust?->phone ?? ''),
                    customer_email: @json($authCust?->email ?? ''),
                    company_name: '',
                    customer_po_number: '',
                    shipping_address: @json($authCust?->shipping_address ?? ''),
                    payment_method_id: '{{ $paymentMethods->first()?->id ?? '' }}',
                    notes: '',
                    items: [{
                        product_id: '',
                        product_name: '',
                        quantity: 1,
                        unit_price: 0,
                        notes: ''
                    }],
                    batches: [{
                        batch_number: 1,
                        scheduled_date: '{{ date('Y-m-d', strtotime('+1 day')) }}',
                        scheduled_time_slot: '09:00 - 12:00',
                        quantity: 1,
                        shipping_address: '',
                        notes: ''
                    }],
                    is_submitting: false,
                    error: null,
                },
                reservationForm: {
                    customer_name: @json($authCust?->name ?? ''),
                    customer_phone: @json($authCust?->phone ?? ''),
                    customer_email: @json($authCust?->email ?? ''),
                    reservation_date: '{{ $minLeadTimeDate }}',
                    time_slot: '{{ $storeSetting?->available_slots[0] ?? '12:00 - 14:00' }}',
                    guest_count: 2,
                    pos_table_id: '',
                    product_id: '',
                    notes: '',
                },
                saveCart() {
                    localStorage.setItem('cooca_cart_{{ $business->id }}', JSON.stringify(this.cart));
                },
                directCheckout(product, qty = 1) {
                    if (!product) return;
                    const parsedQty = Number(qty);
                    if (isNaN(parsedQty) || parsedQty <= 0) {
                        this.showToast('Jumlah pesanan harus lebih dari 0');
                        return;
                    }
                    if (product.is_preorder) {
                        this.checkoutForm.is_scheduled = true;
                        if (!this.checkoutForm.scheduled_date || this.checkoutForm.scheduled_date < this.minPreorderDate) {
                            this.checkoutForm.scheduled_date = this.minPreorderDate;
                        }
                    }
                    this.addToCart(product, parsedQty, false);
                    this.checkoutModalOpen = true;
                },
                addToCart(product, qty = 1, openDrawer = false) {
                    if (!product) return;
                    const parsedQty = Number(qty);
                    if (isNaN(parsedQty) || parsedQty <= 0) {
                        this.showToast('Jumlah pesanan harus lebih dari 0');
                        return;
                    }
                    if (product.is_preorder) {
                        this.checkoutForm.is_scheduled = true;
                        if (!this.checkoutForm.scheduled_date || this.checkoutForm.scheduled_date < this.minPreorderDate) {
                            this.checkoutForm.scheduled_date = this.minPreorderDate;
                        }
                    }
                    const existing = this.cart.find(item => item.id === product.id);
                    if (existing) {
                        existing.quantity += parsedQty;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name || product.title,
                            price: Number(product.price || product.raw_price || 0),
                            image_url: product.image_url || null,
                            quantity: parsedQty,
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
                get hasPreorderItems() {
                    return this.cart.some(item => {
                        const p = this.products.find(prod => String(prod.id) === String(item.id));
                        return p && Boolean(p.is_preorder);
                    });
                },
                get maxPreorderLeadDays() {
                    let maxDays = 0;
                    this.cart.forEach(item => {
                        const p = this.products.find(prod => String(prod.id) === String(item.id));
                        if (p && p.is_preorder) {
                            const days = Number(p.preorder_lead_days || 1);
                            if (days > maxDays) maxDays = days;
                        }
                    });
                    return maxDays;
                },
                get minPreorderDate() {
                    if (!this.hasPreorderItems) {
                        return '{{ $minLeadTimeDate }}';
                    }
                    const d = new Date();
                    d.setDate(d.getDate() + this.maxPreorderLeadDays);
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    const computedDate = `${yyyy}-${mm}-${dd}`;
                    const baseMin = '{{ $minLeadTimeDate }}';
                    return computedDate > baseMin ? computedDate : baseMin;
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
                    if (!this.isCustomerLoggedIn) {
                        window.location.href = this.customerLoginUrl;
                        return;
                    }
                    if (this.isGroupOrderCheckout) {
                        await this.submitGroupOrderCheckout();
                        return;
                    }
                    if (!this.checkoutForm.customer_name || !this.checkoutForm.customer_phone) {
                        this.checkoutError = 'Nama dan Nomor WhatsApp wajib diisi.';
                        this.checkoutStep = 1;
                        return;
                    }
                    if (this.checkoutForm.fulfillment_type === 'merchant_delivery' && !this.checkoutForm.shipping_address) {
                        this.checkoutError = 'Alamat pengiriman wajib diisi untuk kurir toko.';
                        this.checkoutStep = 1;
                        return;
                    }
                    if (this.cart.length === 0) {
                        this.checkoutError = 'Keranjang pesanan masih kosong.';
                        return;
                    }
                    if (this.cart.some(it => !it.quantity || Number(it.quantity) <= 0)) {
                        this.checkoutError = 'Jumlah item pesanan harus lebih dari 0.';
                        return;
                    }
                    if (this.cartTotal <= 0) {
                        this.checkoutError = 'Total belanja harus lebih dari Rp 0.';
                        return;
                    }
                    if (this.cartTotal < this.minOrderAmount) {
                        this.checkoutError = 'Total belanja minimal ' + this.formatPrice(this.minOrderAmount);
                        return;
                    }
                    if (this.hasPreorderItems) {
                        this.checkoutForm.is_scheduled = true;
                        if (!this.checkoutForm.scheduled_date) {
                            this.checkoutError = 'Pesanan memuat produk Pre-Order. Silakan tentukan tanggal jadwal pengiriman/pengambilan.';
                            return;
                        }
                        if (this.checkoutForm.scheduled_date < this.minPreorderDate) {
                            this.checkoutError = 'Produk Pre-Order dalam keranjang membutuhkan waktu persiapan minimal ' + this.maxPreorderLeadDays + ' hari (paling cepat tanggal ' + this.minPreorderDate + ').';
                            return;
                        }
                    }
                    if (this.checkoutForm.is_scheduled && !this.checkoutForm.scheduled_date) {
                        this.checkoutError = 'Silakan pilih jadwal batch pengiriman yang tersedia.';
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
                        payment_gateway: this.checkoutForm.payment_gateway || 'tripay',
                        payment_channel: this.checkoutForm.payment_channel || 'QRIS',
                        payment_method_id: this.checkoutForm.payment_gateway === 'manual' ? (this.checkoutForm.payment_method_id || null) : null,
                        notes: ((this.checkoutForm.group_name ? ('[Kantor/Tim: ' + this.checkoutForm.group_name + '] ') : '') + (this.checkoutForm.notes || '')).trim() || null,

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

                // =========================================================================
                // GROUP ORDER METHODS (Pesan Bareng ShopeeFood / GrabFood)
                // =========================================================================
                initGroupOrder() {
                    if (this.groupOrder.active && this.groupOrder.token) {
                        this.startGroupOrderPolling();
                    }
                },
                startGroupOrderPolling() {
                    if (this.groupOrder.pollTimer) clearInterval(this.groupOrder.pollTimer);
                    this.groupOrder.pollTimer = setInterval(() => {
                        if (!this.groupOrder.actionLoading && !this.isCheckingOut) {
                            this.refreshGroupOrderData(false);
                        }
                    }, 4000);
                },
                stopGroupOrderPolling() {
                    if (this.groupOrder.pollTimer) {
                        clearInterval(this.groupOrder.pollTimer);
                        this.groupOrder.pollTimer = null;
                    }
                },
                async refreshGroupOrderData(showLoading = false) {
                    if (!this.groupOrder.token) return;
                    if (showLoading) this.groupOrder.actionLoading = true;
                    try {
                        const res = await fetch('/b/{{ $business->slug }}/group-order/' + encodeURIComponent(this.groupOrder.token) + '/data');
                        const data = await res.json();
                        if (res.ok && data.success && data.group) {
                            this.groupOrder.data = data.group;
                            this.groupOrder.splitBill = data.split_bill || [];
                            if (data.group.order_tracking_url) {
                                this.groupOrder.orderTrackingUrl = data.group.order_tracking_url;
                            }
                        }
                    } catch (e) {
                        console.error('Group order polling error:', e);
                    } finally {
                        if (showLoading) this.groupOrder.actionLoading = false;
                    }
                },
                openCreateGroupOrderModal() {
                    if (!this.isCustomerLoggedIn) {
                        window.location.href = this.customerLoginUrl;
                        return;
                    }
                    this.groupOrder.isCreateModalOpen = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                async submitCreateGroupOrder() {
                    if (!this.isCustomerLoggedIn) {
                        window.location.href = this.customerLoginUrl;
                        return;
                    }
                    this.groupOrder.createForm.is_submitting = true;
                    this.groupOrder.createForm.error = null;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch('/b/{{ $business->slug }}/group-order', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                title: this.groupOrder.createForm.title || null,
                                scheduled_date: this.groupOrder.createForm.scheduled_date || null,
                                scheduled_time_slot: this.groupOrder.createForm.scheduled_time_slot || null,
                                delivery_address: this.groupOrder.createForm.delivery_address || null,
                                delivery_notes: this.groupOrder.createForm.delivery_notes || null,
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.groupOrder.isCreateModalOpen = false;
                            this.showToast('Sesi Pesan Bareng berhasil dibuat! Bagikan tautan ke teman Anda.');
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            }
                        } else {
                            if (res.status === 401 && data.login_url) {
                                window.location.href = data.login_url;
                                return;
                            }
                            this.groupOrder.createForm.error = data.message || 'Gagal membuat sesi Pesan Bareng.';
                        }
                    } catch (e) {
                        this.groupOrder.createForm.error = 'Koneksi bermasalah. Silakan periksa jaringan Anda.';
                    } finally {
                        this.groupOrder.createForm.is_submitting = false;
                    }
                },
                async addGroupOrderItem(product, qty = 1, notes = '') {
                    if (!this.isCustomerLoggedIn) {
                        window.location.href = this.customerLoginUrl;
                        return;
                    }
                    if (!this.groupOrder.token) return;
                    this.groupOrder.actionLoading = true;
                    this.groupOrder.actionError = null;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch('/b/{{ $business->slug }}/group-order/' + encodeURIComponent(this.groupOrder.token) + '/items', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                product_id: product.id,
                                quantity: Number(qty || 1),
                                notes: notes || ''
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.groupOrder.data = data.group;
                            this.groupOrder.splitBill = data.split_bill || [];
                            this.showToast((product.name || product.title) + ' ditambahkan ke Pesan Bareng!');
                            this.groupOrder.isDrawerOpen = true;
                            if (this.activeModal === 'product') {
                                this.activeModal = null;
                            }
                        } else {
                            this.groupOrder.actionError = data.message || 'Gagal menambahkan menu ke Pesan Bareng.';
                            this.showToast(this.groupOrder.actionError);
                        }
                    } catch (e) {
                        this.showToast('Koneksi bermasalah.');
                    } finally {
                        this.groupOrder.actionLoading = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },
                async updateGroupOrderItem(itemId, deltaQty, notes = null) {
                    if (!this.groupOrder.token) return;
                    this.groupOrder.actionLoading = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        let targetQty = 1;
                        for (const member of this.groupOrder.splitBill) {
                            const found = (member.items || []).find(it => String(it.id) === String(itemId));
                            if (found) {
                                targetQty = Number(found.quantity) + deltaQty;
                                if (notes === null) notes = found.notes;
                                break;
                            }
                        }

                        if (targetQty <= 0) {
                            await this.removeGroupOrderItem(itemId);
                            return;
                        }

                        const res = await fetch('/b/{{ $business->slug }}/group-order/' + encodeURIComponent(this.groupOrder.token) + '/items/' + encodeURIComponent(itemId), {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                quantity: targetQty,
                                notes: notes
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.groupOrder.data = data.group;
                            this.groupOrder.splitBill = data.split_bill || [];
                        } else {
                            this.showToast(data.message || 'Gagal mengubah jumlah.');
                        }
                    } catch (e) {
                        this.showToast('Koneksi bermasalah.');
                    } finally {
                        this.groupOrder.actionLoading = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },
                async removeGroupOrderItem(itemId) {
                    if (!this.groupOrder.token) return;
                    this.groupOrder.actionLoading = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch('/b/{{ $business->slug }}/group-order/' + encodeURIComponent(this.groupOrder.token) + '/items/' + encodeURIComponent(itemId), {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.groupOrder.data = data.group;
                            this.groupOrder.splitBill = data.split_bill || [];
                            this.showToast('Item berhasil dihapus dari keranjang bersama.');
                        } else {
                            this.showToast(data.message || 'Gagal menghapus item.');
                        }
                    } catch (e) {
                        this.showToast('Koneksi bermasalah.');
                    } finally {
                        this.groupOrder.actionLoading = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },
                async toggleLockGroupOrder() {
                    if (!this.groupOrder.token || !this.groupOrder.data?.is_host) return;
                    this.groupOrder.actionLoading = true;
                    const action = this.groupOrder.data.is_locked ? 'unlock' : 'lock';
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch('/b/{{ $business->slug }}/group-order/' + encodeURIComponent(this.groupOrder.token) + '/' + action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.groupOrder.data = data.group;
                            this.groupOrder.splitBill = data.split_bill || [];
                            this.showToast(action === 'lock' ? '🔒 Pesanan bersama berhasil dikunci!' : '🔓 Pesanan bersama dibuka kembali!');
                        } else {
                            this.showToast(data.message || 'Gagal mengubah status pesanan.');
                        }
                    } catch (e) {
                        this.showToast('Koneksi bermasalah.');
                    } finally {
                        this.groupOrder.actionLoading = false;
                    }
                },
                openGroupOrderCheckout() {
                    if (!this.groupOrder.data?.is_host) {
                        this.showToast('Hanya Host (' + (this.groupOrder.data?.host_name || 'Pembuat Grup') + ') yang dapat melakukan checkout.');
                        return;
                    }
                    if ((this.groupOrder.data?.total_quantity || 0) <= 0) {
                        this.showToast('Keranjang bersama masih kosong.');
                        return;
                    }
                    this.isGroupOrderCheckout = true;
                    this.checkoutForm.is_scheduled = true;
                    if (this.groupOrder.data?.scheduled_date) {
                        this.checkoutForm.scheduled_date = this.groupOrder.data.scheduled_date;
                    }
                    this.groupOrder.isDrawerOpen = false;
                    this.checkoutModalOpen = true;
                },
                async submitGroupOrderCheckout() {
                    if (!this.groupOrder.token) return;
                    this.isCheckingOut = true;
                    this.checkoutError = null;
                    const payload = {
                        payment_method_id: this.checkoutForm.payment_method_id,
                        delivery_address: this.checkoutForm.fulfillment_type === 'merchant_delivery' ? this.checkoutForm.shipping_address : null,
                        delivery_notes: this.checkoutForm.notes || null,
                        fulfillment_type: this.checkoutForm.fulfillment_type || 'pickup',
                        shipping_fee: this.checkoutForm.fulfillment_type === 'merchant_delivery' ? Number(this.checkoutForm.shipping_fee || 0) : 0,
                    };
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch('/b/{{ $business->slug }}/group-order/' + encodeURIComponent(this.groupOrder.token) + '/checkout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.checkoutModalOpen = false;
                            this.isGroupOrderCheckout = false;
                            await this.refreshGroupOrderData(false);
                            if (data.tracking_url) {
                                this.groupOrder.orderTrackingUrl = data.tracking_url;
                            }
                            this.groupOrder.isSplitBillOpen = true;
                            this.showToast('Pesanan bersama berhasil dicheckout!');
                        } else {
                            this.checkoutError = data.message || 'Gagal melakukan checkout pesanan bersama.';
                        }
                    } catch (e) {
                        this.checkoutError = 'Koneksi bermasalah saat checkout pesanan bersama.';
                    } finally {
                        this.isCheckingOut = false;
                    }
                },
                copyGroupOrderLink() {
                    const url = window.location.origin + window.location.pathname + '?group_order=' + encodeURIComponent(this.groupOrder.token);
                    const msg = '🍱 *Pesan Bareng (Group Order) di {{ addslashes($business->name) }}*\n' +
                                '🏢 *Grup:* ' + (this.groupOrder.data?.title || 'Pesanan Bersama') + '\n' +
                                '👤 *Host:* ' + (this.groupOrder.data?.host_name || 'Rekan') + '\n' +
                                (this.groupOrder.data?.scheduled_date ? ('📅 *Jadwal Pengiriman:* ' + this.groupOrder.data.scheduled_date + '\n') : '') +
                                '\n✨ Yuk pilih makanan favoritmu! Semua pesanan akan otomatis masuk ke 1 keranjang:\n👉 ' + url;

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(msg).then(() => {
                            this.showToast('Tautan Pesan Bareng berhasil disalin!');
                        }).catch(() => {
                            prompt('Salin link Pesan Bareng:', url);
                        });
                    } else {
                        prompt('Salin link Pesan Bareng:', url);
                    }
                },
                shareGroupOrderWa() {
                    const url = window.location.origin + window.location.pathname + '?group_order=' + encodeURIComponent(this.groupOrder.token);
                    const msg = '🍱 *Pesan Bareng (Group Order) di {{ addslashes($business->name) }}*\n' +
                                '🏢 *Grup:* ' + (this.groupOrder.data?.title || 'Pesanan Bersama') + '\n' +
                                '👤 *Host:* ' + (this.groupOrder.data?.host_name || 'Rekan') + '\n' +
                                (this.groupOrder.data?.scheduled_date ? ('📅 *Jadwal Pengiriman:* ' + this.groupOrder.data.scheduled_date + '\n') : '') +
                                '\n✨ Yuk pilih makanan favoritmu! Semua pesanan akan otomatis masuk ke 1 keranjang:\n👉 ' + url;
                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(msg), '_blank');
                },
                copySplitBillText() {
                    if (!this.groupOrder.splitBill || !this.groupOrder.splitBill.length) return;
                    let text = '🧾 *RINCIAN PATUNGAN (SPLIT BILL)*\n' +
                               '🍱 {{ addslashes($business->name) }}\n' +
                               '🏢 Grup: ' + (this.groupOrder.data?.title || 'Pesanan Bersama') + '\n' +
                               (this.groupOrder.data?.scheduled_date ? ('📅 Tanggal: ' + this.groupOrder.data.scheduled_date + '\n') : '') +
                               '--------------------------------\n';

                    this.groupOrder.splitBill.forEach(member => {
                        text += '\n👤 *' + member.member_name + '* (Total: ' + this.formatPrice(member.member_subtotal) + ')\n';
                        (member.items || []).forEach(it => {
                            text += '  • ' + it.quantity + 'x ' + it.product_name + ' (' + this.formatPrice(it.line_total) + ')' + (it.notes ? (' [' + it.notes + ']') : '') + '\n';
                        });
                    });

                    text += '\n--------------------------------\n' +
                            '💰 *TOTAL AKHIR:* ' + this.formatPrice(this.groupOrder.data?.subtotal || 0) + '\n\n' +
                            '💳 *Pembayaran/Transfer ke Host (' + (this.groupOrder.data?.host_name || 'Host') + '):*\n' +
                            'Mohon transfer sesuai nominal di atas ya. Terima kasih! 🙏';

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(() => {
                            this.showToast('Rincian Patungan berhasil disalin! Siap dikirim ke WhatsApp.');
                        }).catch(() => {
                            prompt('Salin teks patungan:', text);
                        });
                    } else {
                        prompt('Salin teks patungan:', text);
                    }
                },
                async submitRequestOrder() {
                    const reqQty = Number(this.requestOrderForm.item_qty || 0);
                    if (isNaN(reqQty) || reqQty <= 0) {
                        this.requestOrderForm.error = 'Jumlah pesanan harus lebih dari 0.';
                        return;
                    }
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
                        pos_table_id: this.reservationForm.pos_table_id || null,
                        product_id: this.reservationForm.product_id || null,
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

                // Customer PO & Batch Dispatch Engine
                addPoItem() {
                    this.customerPoForm.items.push({
                        product_id: '',
                        product_name: '',
                        quantity: 1,
                        unit_price: 0,
                        notes: ''
                    });
                },
                removePoItem(idx) {
                    if (this.customerPoForm.items.length > 1) {
                        this.customerPoForm.items.splice(idx, 1);
                    }
                },
                onPoProductChange(idx) {
                    const selId = this.customerPoForm.items[idx].product_id;
                    const prod = this.products.find(p => p.id === selId);
                    if (prod) {
                        this.customerPoForm.items[idx].product_name = prod.name;
                        this.customerPoForm.items[idx].unit_price = Number(prod.price || 0);
                    }
                },
                addPoBatch() {
                    const num = this.customerPoForm.batches.length + 1;
                    this.customerPoForm.batches.push({
                        batch_number: num,
                        scheduled_date: '{{ $minLeadTimeDate }}',
                        scheduled_time_slot: '09:00 - 12:00',
                        quantity: 1,
                        shipping_address: this.customerPoForm.shipping_address || '',
                        notes: ''
                    });
                },
                removePoBatch(idx) {
                    if (this.customerPoForm.batches.length > 1) {
                        this.customerPoForm.batches.splice(idx, 1);
                    }
                },
                get poTotalItemQty() {
                    return this.customerPoForm.items.reduce((s, it) => s + (Number(it.quantity) || 0), 0);
                },
                get poTotalBatchQty() {
                    return this.customerPoForm.batches.reduce((s, b) => s + (Number(b.quantity) || 0), 0);
                },
                get poEstimatedSubtotal() {
                    return this.customerPoForm.items.reduce((s, it) => s + ((Number(it.quantity) || 0) * (Number(it
                        .unit_price) || 0)), 0);
                },
                openCustomerPoModal(product = null) {
                    if (product) {
                        this.customerPoForm.items = [{
                            product_id: product.id || '',
                            product_name: product.name || product.title || '',
                            quantity: 1,
                            unit_price: Number(product.price || product.raw_price || 0),
                            notes: ''
                        }];
                    }
                    this.customerPoForm.error = null;
                    this.customerPoModalOpen = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },
                async submitCustomerPo() {
                    this.customerPoForm.is_submitting = true;
                    this.customerPoForm.error = null;

                    if (!this.customerPoForm.customer_name || !this.customerPoForm.customer_phone) {
                        this.customerPoForm.error = 'Nama pemesan dan nomor WhatsApp wajib diisi.';
                        this.customerPoForm.is_submitting = false;
                        return;
                    }

                    if (this.customerPoForm.items.length === 0 || this.customerPoForm.items.some(it => !it
                            .product_name || !it.quantity || isNaN(Number(it.quantity)) || Number(it.quantity) <= 0
                            )) {
                        this.customerPoForm.error =
                            'Lengkapi nama produk dan pastikan jumlah unit lebih dari 0 pada daftar item PO.';
                        this.customerPoForm.is_submitting = false;
                        return;
                    }

                    if (this.customerPoForm.batches.length === 0 || this.customerPoForm.batches.some(b => !b
                            .scheduled_date || !b.quantity || isNaN(Number(b.quantity)) || Number(b.quantity) <= 0
                            )) {
                        this.customerPoForm.error =
                            'Lengkapi tanggal target kirim dan pastikan jumlah unit lebih dari 0 pada setiap batch.';
                        this.customerPoForm.is_submitting = false;
                        return;
                    }

                    const payload = {
                        customer_name: this.customerPoForm.customer_name,
                        customer_phone: this.customerPoForm.customer_phone,
                        customer_email: this.customerPoForm.customer_email || null,
                        company_name: this.customerPoForm.company_name || null,
                        customer_po_number: this.customerPoForm.customer_po_number || null,
                        shipping_address: this.customerPoForm.shipping_address || null,
                        payment_method_id: this.customerPoForm.payment_method_id || null,
                        notes: this.customerPoForm.notes || null,
                        items: this.customerPoForm.items.map(it => ({
                            product_id: it.product_id || null,
                            product_name: it.product_name,
                            quantity: Number(it.quantity),
                            unit_price: Number(it.unit_price || 0),
                            notes: it.notes || null
                        })),
                        batches: this.customerPoForm.batches.map(b => ({
                            scheduled_date: b.scheduled_date,
                            scheduled_time_slot: b.scheduled_time_slot || null,
                            quantity: Number(b.quantity),
                            shipping_address: b.shipping_address || this.customerPoForm
                                .shipping_address || null,
                            notes: b.notes || null
                        }))
                    };

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '';
                        const response = await fetch(
                            '{{ route('public.storefront.customer_po', $business->slug) }}', {
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
                            this.customerPoModalOpen = false;
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
                            this.customerPoForm.error = data.message || 'Gagal mengirim formulir Customer PO.';
                        }
                    } catch (e) {
                        this.customerPoForm.error = 'Koneksi bermasalah. Silakan periksa jaringan Anda.';
                    } finally {
                        this.customerPoForm.is_submitting = false;
                    }
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


    {{-- JSON-LD LocalBusiness Schema for Google Search --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "LocalBusiness",
        "name": "{{ $business->name }}",
        "description": "{{ $landingPage->subheadline }}",
        "url": "{{ url()->current() }}",
        "image": "{{ $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: $landingPage->logo_url)) }}",
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
    class="bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#FFFFFF] antialiased selection:bg-brand-primary selection:text-white pb-28 sm:pb-32 md:pb-28 transition-colors duration-300"
    x-data="landingPageState()">

    {{-- Mode Preview Indicator (Hanya saat diakses preview oleh pemilik sebelum terbit) --}}
    @if (!$landingPage->is_published)
        <div
            class="sticky top-0 z-[100] w-full bg-[#FF9500] text-black font-semibold text-[12px] sm:text-[12.5px] py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
            <span>Mode Preview Bisnis &mdash; Halaman ini masih berstatus draft privat dan belum dipublikasikan ke
                umum.</span>
        </div>
    @endif

    {{-- Hari Libur / Status Operasional Toko --}}
    @if (!$isStoreOpenToday)
        <div
            class="bg-amber-500/10 border-b border-amber-500/20 py-2 px-4 text-amber-800 dark:text-amber-300 text-[12px] text-center flex items-center justify-center gap-2">
            <i data-lucide="clock" class="w-4 h-4 text-amber-500 shrink-0"></i>
            <span>Outlet toko libur hari ini ({{ $dayMap[$currentDayOfWeek] ?? '' }}). Pesanan online tetap dapat
                dibuat dan akan diproses pada hari kerja berikutnya.</span>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TOP STICKY BANNER: GROUP ORDER (Pesan Bareng ShopeeFood / GrabFood)      --}}
    {{-- ========================================================================= --}}
    <template x-if="groupOrder.active && groupOrder.data">
        <aside aria-label="Status Pesan Bareng"
            class="sticky top-0 z-[60] w-full bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border-b border-black/10 dark:border-white/10 shadow-md py-2.5 px-3.5 sm:px-6 transition-all duration-200">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wide"
                        :class="groupOrder.data.is_locked ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : (groupOrder.data.is_checked_out ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-brand-primary text-white shadow-2xs')">
                        <i :data-lucide="groupOrder.data.is_locked ? 'lock' : (groupOrder.data.is_checked_out ? 'check-circle' : 'users')" class="w-3 h-3"></i>
                        <span x-text="groupOrder.data.is_locked ? 'Pesanan Dikunci' : (groupOrder.data.is_checked_out ? 'Selesai Dicheckout' : 'Pesan Bareng Aktif')"></span>
                    </span>
                    <h2 class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tracking-tight"
                        x-text="groupOrder.data.title || 'Pesanan Bersama'"></h2>
                    <span class="text-[11.5px] text-black/55 dark:text-white/55">
                        &bull; Host: <strong class="text-black dark:text-white" x-text="groupOrder.data.host_name"></strong>
                        <span x-show="groupOrder.data.is_host" class="text-brand-primary font-bold">(Anda)</span>
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11.5px] text-black/60 dark:text-white/60 bg-black/5 dark:bg-white/5 px-2 py-0.5 rounded-md tabular-nums">
                        <i data-lucide="shopping-bag" class="w-3 h-3 text-brand-primary"></i>
                        <strong class="text-black dark:text-white" x-text="groupOrder.data.total_quantity || 0"></strong> item (<span x-text="formatPrice(groupOrder.data.subtotal || 0)"></span>)
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11.5px] text-black/60 dark:text-white/60 bg-black/5 dark:bg-white/5 px-2 py-0.5 rounded-md tabular-nums">
                        <i data-lucide="user-check" class="w-3 h-3 text-[#34C759]"></i>
                        <strong class="text-black dark:text-white" x-text="groupOrder.data.members_count || 1"></strong> orang
                    </span>
                </div>
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                    <button type="button" @click="copyGroupOrderLink()"
                        class="px-3 py-1.5 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white text-[11.5px] font-bold transition flex items-center gap-1 cursor-pointer active:scale-95"
                        title="Salin Link Pesan Bareng">
                        <i data-lucide="copy" class="w-3.5 h-3.5 text-brand-primary"></i>
                        <span class="hidden sm:inline">Salin Link</span>
                    </button>
                    <button type="button" @click="shareGroupOrderWa()"
                        class="px-3 py-1.5 rounded-[12px] bg-[#25D366] hover:bg-[#20bd5a] text-white text-[11.5px] font-bold transition flex items-center gap-1 cursor-pointer active:scale-95 shadow-2xs"
                        title="Ajak Teman via WhatsApp">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        <span>Ajak Teman</span>
                    </button>
                    <button type="button" @click="groupOrder.isDrawerOpen = true"
                        class="px-3.5 py-1.5 rounded-[12px] bg-brand-primary hover:opacity-90 text-white text-[11.5px] font-bold transition flex items-center gap-1.5 cursor-pointer active:scale-95 shadow-2xs">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        <span>Keranjang Bersama</span>
                    </button>
                    <template x-if="groupOrder.splitBill && groupOrder.splitBill.length > 0">
                        <button type="button" @click="groupOrder.isSplitBillOpen = true"
                            class="px-3 py-1.5 rounded-[12px] bg-[#5856D6]/10 hover:bg-[#5856D6]/20 text-[#5856D6] dark:text-[#AF52DE] text-[11.5px] font-bold transition flex items-center gap-1 cursor-pointer active:scale-95 border border-[#5856D6]/20 shadow-2xs"
                            title="Lihat Rincian Patungan (Split Bill)">
                            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                            <span>Rincian Patungan</span>
                        </button>
                    </template>
                </div>
            </div>
        </aside>
    </template>

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
    @php
        $announcementParts = array_filter([
            $storeSetting?->announcement_text,
            $landingPage->announcement_badge,
            $storeSetting?->cut_off_time
                ? 'Batas pesanan masuk hari ini: ' . substr((string) $storeSetting->cut_off_time, 0, 5) . ' WIB'
                : null,
            ($storeSetting?->daily_order_quota ?? 0) > 0
                ? 'Kuota harian terbatas: maks. ' . $storeSetting->daily_order_quota . ' order/hari'
                : null,
        ]);
        $announcementBanner = implode('  •  ', $announcementParts);
    @endphp
    @if ($announcementBanner)
        <aside x-data="{ bannerDismissed: false }" x-show="!bannerDismissed"
            class="relative overflow-hidden bg-brand-primary text-white text-[12px] font-medium leading-tight select-none">
            <div class="flex items-center justify-between pr-3">
                <div class="marquee-track py-2 flex-1">
                    <span class="px-8 shrink-0 flex items-center gap-3">
                        <span>{{ $announcementBanner }}</span>
                        <span class="opacity-60 text-[10px]">&bull;</span>
                        <span>{{ $announcementBanner }}</span>
                        <span class="opacity-60 text-[10px]">&bull;</span>
                        <span>{{ $announcementBanner }}</span>
                    </span>
                    <span class="px-8 shrink-0 flex items-center gap-3" aria-hidden="true">
                        <span>{{ $announcementBanner }}</span>
                        <span class="opacity-60 text-[10px]">&bull;</span>
                        <span>{{ $announcementBanner }}</span>
                        <span class="opacity-60 text-[10px]">&bull;</span>
                        <span>{{ $announcementBanner }}</span>
                    </span>
                </div>
                <button type="button" @click="bannerDismissed = true"
                    class="relative z-10 w-5 h-5 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition active:scale-95 shrink-0 cursor-pointer ml-2"
                    title="Tutup pengumuman">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            </div>
        </aside>
    @endif

    {{-- ========================================================================= --}}
    {{-- TOPBAR / NAVBAR (macOS Sonoma / Apple Store Frosted Glass Style)            --}}
    {{-- ========================================================================= --}}
    <header
        class="sticky top-0 z-40 w-full backdrop-blur-xl bg-white/80 dark:bg-[#000000]/80 border-black/5 dark:border-white/10 border-b transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-4">

            {{-- Brand / Logo (Apple Squircle Icon) --}}
            <a href="#hero" class="flex items-center gap-3 group select-none">
                @php
                    $headerBrandLogo = $business->logo_url ?: $landingPage->logo_url;
                @endphp
                @if ($headerBrandLogo)
                    <img src="{{ $headerBrandLogo }}" alt="{{ $business->name }}"
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
                    @if (collect($normalizedOperationalHours)->contains(fn($hours) => !empty($hours['is_open'])))
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[#34C759]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
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
            <div class="storefront-header-actions flex items-center gap-2">
                {{-- Storefront Shopping Cart Button --}}
                @if ($allowCart)
                    <button type="button" @click="cartDrawerOpen = true"
                        class="relative h-9 px-3 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-95 border border-black/5 dark:border-white/10 cursor-pointer"
                        title="Keranjang Belanja">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span class="hidden lg:inline">Keranjang</span>
                        <span x-show="cartCount > 0"
                            class="px-1.5 py-0.2 rounded-full text-[10.5px] font-extrabold bg-brand-primary text-white tabular-nums shadow-xs"
                            x-text="cartCount"></span>
                    </button>
                @endif

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
                                class="w-6 h-6 rounded-full bg-brand-primary text-white text-[11px] font-bold flex items-center justify-center">
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
                                        class="px-2 py-0.5 rounded-full bg-brand-100 text-brand-primary font-bold capitalize">{{ $navCust->membership_tier ?? 'Bronze' }}</span>
                                    <span
                                        class="font-semibold text-black/70 dark:text-white/70">{{ number_format($navCust->loyalty_points ?? 0) }}
                                        Poin</span>
                                </div>
                            </div>
                            <a href="{{ route('customer.dashboard') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-[12px] text-black/80 dark:text-white/80 hover:bg-black/5 dark:hover:bg-white/10 transition font-medium">
                                <i data-lucide="layout-grid" class="w-4 h-4 text-brand-primary"></i>
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

                {{-- Adaptive Single Primary Action Button (Apple HIG Minimalist Bento) --}}
                @if ($allowReservation)
                    <button type="button" @click="openReservationModal()"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-primary text-white text-[12.5px] font-semibold hover:opacity-90 active:scale-95 transition shadow-xs cursor-pointer"
                        title="Reservasi">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>{{ $landingPage->cta_primary_text ?: 'Reservasi' }}</span>
                    </button>
                @elseif ($allowCustomerPo && $isProductionB2b)
                    <button type="button" @click="openCustomerPoModal()"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-primary text-white text-[12.5px] font-semibold hover:opacity-90 active:scale-95 transition shadow-xs cursor-pointer"
                        title="Pesan PO">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        <span>{{ $landingPage->cta_primary_text ?: 'Pesan' }}</span>
                    </button>
                @elseif ($allowRequestOrder && !in_array($industryGroup, ['retail', 'service']))
                    <button type="button" @click="requestOrderModalOpen = true"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-primary text-white text-[12.5px] font-semibold hover:opacity-90 active:scale-95 transition shadow-xs cursor-pointer"
                        title="Pesan Pre-Order">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        <span>{{ $landingPage->cta_primary_text ?: 'Pesan' }}</span>
                    </button>
                @elseif ($hasServices || $hasPosGoods)
                    <a href="{{ $landingPage->cta_primary_url ?: '#layanan' }}"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-primary text-white text-[12.5px] font-semibold hover:opacity-90 active:scale-95 transition shadow-xs"
                        title="Pesan">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        <span>{{ $landingPage->cta_primary_text ?: 'Pesan' }}</span>
                    </a>
                @elseif ($hasWhatsapp)
                    <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}" target="_blank"
                        rel="noopener"
                        class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-[#25D366] text-white text-[12.5px] font-semibold hover:opacity-90 active:scale-95 transition shadow-xs"
                        title="Hubungi via WhatsApp">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                        </svg>
                        <span>{{ $landingPage->cta_primary_text ?: 'WhatsApp' }}</span>
                    </a>
                @endif


                {{-- Theme control on every device; no persistent override of CMS settings. --}}
                <button id="storefront-theme-toggle" type="button" class="storefront-theme-toggle hidden md:inline-flex"
                    @click="toggleTheme()" role="switch" aria-label="Mode gelap"
                    :aria-checked="isDark ? 'true' : 'false'"
                    :title="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'" x-cloak>
                    <svg x-show="!isDark" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.8 13.1A9 9 0 0 1 10.9 3.2a9 9 0 1 0 9.9 9.9Z" />
                    </svg>
                    <svg x-show="isDark" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path
                            d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                    </svg>
                </button>

                {{-- Mobile Hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/70 dark:text-white/70 active:scale-95 transition"
                    aria-label="Menu navigasi" aria-controls="storefront-mobile-menu"
                    :aria-expanded="mobileMenuOpen ? 'true' : 'false'">
                    <i data-lucide="menu" class="w-4 h-4" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-4 h-4" x-show="mobileMenuOpen" style="display: none;"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Dropdown (Apple Sheet Dropdown) --}}
        <div id="storefront-mobile-menu" x-show="mobileMenuOpen" x-transition.opacity
            class="md:hidden border-t bg-white/95 dark:bg-[#1C1C1E]/95 border-black/5 dark:border-white/10 backdrop-blur-xl px-5 py-4 space-y-2 text-[14px] font-medium"
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
                                class="w-9 h-9 rounded-full bg-brand-primary text-white text-[13px] font-bold flex items-center justify-center">
                                {{ strtoupper(mb_substr($mCust->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-[14px] text-black dark:text-white leading-tight">
                                    {{ $mCust->name }}</p>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ $mCust->phone }}</p>
                            </div>
                        </div>
                        <span
                            class="px-2 py-0.5 rounded-full bg-brand-100 text-brand-primary text-[11px] font-bold capitalize">{{ $mCust->membership_tier ?? 'Bronze' }}</span>
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
                        class="w-full h-11 rounded-[14px] bg-brand-100 text-brand-primary font-bold text-[13.5px] flex items-center justify-center gap-2 hover:bg-brand-200 transition">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Masuk / Daftar Akun Pelanggan</span>
                    </a>
                </div>
            @endif

            @if ($sectionVisibility['services'] && $hasServices)
                <a href="#layanan" @click="mobileMenuOpen = false"
                    class="block py-2 text-black/80 dark:text-white/80">{{ $industryLabels['catalog_title'] ?? 'Menu & Layanan' }}</a>
            @endif
            @if ($allowReservation)
                <button type="button"
                    @click="mobileMenuOpen = false; reservationModalOpen = true; reservationSuccess = false;"
                    class="block w-full text-left py-2 text-[#34C759] font-semibold">Reservasi</button>
            @endif
            @if ($allowRequestOrder || $allowCustomerPo || $allowCart || $hasPosGoods)
                <button type="button"
                    @click="mobileMenuOpen = false; @if ($allowCustomerPo && $isProductionB2b) openCustomerPoModal() @elseif($allowRequestOrder && !in_array($industryGroup, ['retail', 'service'])) requestOrderModalOpen = true @elseif($allowCart) cartDrawerOpen = true @else document.getElementById('layanan')?.scrollIntoView({behavior: 'smooth'}) @endif"
                    class="block w-full text-left py-2 text-brand-primary font-semibold">Pesan</button>
            @endif
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
            {{-- Mobile Theme Switcher --}}
            <div class="pt-3 pb-1 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                <span class="text-[13px] text-black/60 dark:text-white/60">Tampilan Layar</span>
                <button type="button" @click="toggleTheme()"
                    class="h-8 px-3 rounded-full bg-black/5 dark:bg-white/10 text-[12px] font-semibold text-black/80 dark:text-white/80 flex items-center gap-1.5 active:scale-95 transition cursor-pointer"
                    role="switch" :aria-checked="isDark ? 'true' : 'false'">
                    <span x-text="isDark ? 'Mode Gelap' : 'Mode Terang'"></span>
                    <svg x-show="!isDark" class="w-3.5 h-3.5 text-black/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 13.1A9 9 0 0 1 10.9 3.2a9 9 0 1 0 9.9 9.9Z" /></svg>
                    <svg x-show="isDark" class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4" /><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" /></svg>
                </button>
            </div>

            @if ($hasWhatsapp)
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                    class="w-full mt-3 h-11 rounded-full bg-brand-primary text-white text-center font-semibold text-[14px] flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                    </svg>
                    <span>Hubungi via WhatsApp</span>
                </a>
            @endif
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- HERO SECTION (Apple Hardware Spotlight Bento Banner)                     --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['hero'])
        @php
            $heroDisplayImage = $landingPage->hero_image_url ?: ($business->logo_url ?: $landingPage->logo_url);
        @endphp
        <section id="hero" class="relative pt-6 sm:pt-8 pb-12 sm:pb-16 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="{{ $heroDisplayImage ? 'grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center' : 'max-w-3xl mx-auto text-center space-y-6' }}">

                    {{-- Left Column: Hero Typography & Actions --}}
                    <div
                        class="{{ $heroDisplayImage ? 'lg:col-span-7 text-center lg:text-left space-y-5' : 'space-y-6' }}">
                        @if ($landingPage->announcement_badge)
                            <p class="text-[11px] sm:text-[12px] font-bold uppercase tracking-wider text-brand-primary">
                                {{ $landingPage->announcement_badge }}
                            </p>
                        @endif

                        <h1
                            class="text-[34px] sm:text-[46px] lg:text-[54px] font-extrabold text-black dark:text-white tracking-[-0.035em] leading-[1.08]">
                            {{ $landingPage->headline ?: $business->name }}
                        </h1>

                        @if ($landingPage->subheadline)
                            <p
                                class="text-[15px] sm:text-[17px] text-black/65 dark:text-white/65 leading-relaxed font-normal tracking-[-0.01em] {{ $landingPage->hero_image_url ? 'max-w-xl' : 'max-w-2xl mx-auto' }}">
                                {{ $landingPage->subheadline }}
                            </p>
                        @endif

                        {{-- Action Buttons (Apple HIG Rule: Maximum 2 CTAs, Concise Labels: Pesan, Reservasi, WhatsApp) --}}
                        <div
                            class="flex flex-col sm:flex-row items-center {{ $landingPage->hero_image_url ? 'justify-center lg:justify-start' : 'justify-center' }} gap-3 pt-2">
                            {{-- Primary CTA --}}
                            @if ($allowReservation)
                                <button type="button" @click="openReservationModal()"
                                    class="w-full sm:w-auto h-12 px-7 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(0,0,0,0.15)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                                    title="Reservasi">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>{{ $landingPage->cta_primary_text ?: 'Reservasi' }}</span>
                                </button>
                            @elseif ($allowCustomerPo && $isProductionB2b)
                                <button type="button" @click="openCustomerPoModal()"
                                    class="w-full sm:w-auto h-12 px-7 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(0,0,0,0.15)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                                    title="Pesan PO">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>{{ $landingPage->cta_primary_text ?: 'Pesan' }}</span>
                                </button>
                            @elseif ($allowRequestOrder && !in_array($industryGroup, ['retail', 'service']))
                                <button type="button" @click="requestOrderModalOpen = true"
                                    class="w-full sm:w-auto h-12 px-7 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(0,0,0,0.15)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                                    title="Pesan Pre-Order">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>{{ $landingPage->cta_primary_text ?: 'Pesan' }}</span>
                                </button>
                            @elseif ($hasServices || $hasPosGoods)
                                <a href="{{ $landingPage->cta_primary_url ?: '#layanan' }}"
                                    class="w-full sm:w-auto h-12 px-7 rounded-full bg-brand-primary text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(0,0,0,0.15)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                                    title="Pesan">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>{{ $landingPage->cta_primary_text ?: 'Pesan' }}</span>
                                </a>
                            @elseif ($hasWhatsapp)
                                <a href="{{ $landingPage->cta_primary_url ?: $landingPage->getWhatsAppUrl() }}"
                                    target="_blank" rel="noopener"
                                    class="w-full sm:w-auto h-12 px-7 rounded-full bg-[#25D366] text-white font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] shadow-[0_4px_16px_rgba(37,211,102,0.25)] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                                    title="Hubungi via WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                    </svg>
                                    <span>{{ $landingPage->cta_primary_text ?: 'WhatsApp' }}</span>
                                </a>
                            @endif

                            {{-- Secondary Contextual Action (Pesan, Reservasi, atau WhatsApp) --}}
                            @if ($allowReservation)
                                @if ($allowCart || $hasPosGoods || $allowCustomerPo || $allowRequestOrder)
                                    <a href="{{ $landingPage->cta_secondary_url ?: '#layanan' }}"
                                        class="w-full sm:w-auto h-12 px-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-center gap-2"
                                        title="Pesan">
                                        <i data-lucide="shopping-bag"
                                            class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                        <span>{{ $landingPage->cta_secondary_text ?: 'Pesan' }}</span>
                                    </a>
                                @elseif ($hasWhatsapp)
                                    <a href="{{ $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl() }}"
                                        target="_blank" rel="noopener"
                                        class="w-full sm:w-auto h-12 px-6 rounded-full bg-[#34C759]/10 hover:bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] border border-[#34C759]/20 active:scale-[0.98] transition flex items-center justify-center gap-2"
                                        title="Hubungi via WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                        </svg>
                                        <span>{{ $landingPage->cta_secondary_text ?: 'WhatsApp' }}</span>
                                    </a>
                                @endif
                            @else
                                @if ($hasWhatsapp)
                                    <a href="{{ $landingPage->cta_secondary_url ?: $landingPage->getWhatsAppUrl() }}"
                                        target="_blank" rel="noopener"
                                        class="w-full sm:w-auto h-12 px-6 rounded-full bg-[#34C759]/10 hover:bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-semibold text-[13.5px] sm:text-[14px] tracking-[-0.01em] border border-[#34C759]/20 active:scale-[0.98] transition flex items-center justify-center gap-2"
                                        title="Hubungi via WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                        </svg>
                                        <span>{{ $landingPage->cta_secondary_text ?: 'WhatsApp' }}</span>
                                    </a>
                                @endif
                            @endif
                        </div>

                        {{-- Micro Trust Tags --}}
                        <div
                            class="flex flex-wrap items-center {{ $heroDisplayImage ? 'justify-center lg:justify-start' : 'justify-center' }} gap-4 text-[12px] sm:text-[12.5px] font-medium text-black/55 dark:text-white/55 pt-1">
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check"
                                    class="w-4 h-4 text-brand-primary"></i>100% Autentik</span>
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="award"
                                    class="w-4 h-4 text-amber-500"></i>Kualitas Terjamin</span>
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="smile"
                                    class="w-4 h-4 text-[#34C759]"></i>Pelayanan Ramah</span>
                        </div>
                    </div>

                    {{-- Right Column: Media Hardware Squircle Showcase --}}
                    @if ($heroDisplayImage)
                        <div class="lg:col-span-5 pt-6 lg:pt-0">
                            <div
                                class="rounded-[28px] overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.12)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.45)] border border-black/10 dark:border-white/10 aspect-[4/3] bg-black/[0.03] dark:bg-white/[0.05] relative group flex items-center justify-center">
                                <img src="{{ $heroDisplayImage }}" alt="{{ $business->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                    fetchpriority="high"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div
                                    class="hidden w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-primary/10 via-black/5 dark:via-white/5 to-brand-primary/20">
                                    <i data-lucide="image" class="w-12 h-12 text-brand-primary/40"></i>
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent pointer-events-none">
                                </div>
                                <div
                                    class="absolute bottom-4 inset-x-4 flex items-center justify-between text-white pointer-events-none">
                                    <div class="text-xs font-semibold drop-shadow-md flex items-center gap-1.5">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-brand-primary"></i>
                                        <span>{{ $business->name }}</span>
                                    </div>
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
    {{-- STOREFRONT TAB NAVIGATION BAR (Bento Apple HIG Segmented Control)         --}}
    {{-- ========================================================================= --}}
    @if (!empty($storefrontTabs) && count($storefrontTabs) > 1)
        <div id="storefront-tabs-bar"
            class="sticky top-14 sm:top-16 z-30 backdrop-blur-xl bg-white/85 dark:bg-[#000000]/85 border-b border-black/5 dark:border-white/10 shadow-xs py-2.5 transition-all duration-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between gap-3">
                    {{-- Scrollable Segmented Control Pill Container --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 px-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] border border-black/5 dark:border-white/10 max-w-full"
                        role="tablist" aria-label="Kategori Etalase">
                        @foreach ($storefrontTabs as $tab)
                            <button type="button" role="tab"
                                :aria-selected="activeMainTab === '{{ $tab['id'] }}' ? 'true' : 'false'"
                                @click="setMainTab('{{ $tab['id'] }}')"
                                class="relative flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-[13px] text-[12.5px] sm:text-[13px] font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer select-none"
                                :class="activeMainTab === '{{ $tab['id'] }}'
                                    ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-xs font-bold'
                                    : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'">
                                <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5 transition-colors"
                                    :class="activeMainTab === '{{ $tab['id'] }}' ? 'text-brand-primary' : 'opacity-60'"></i>
                                <span>{{ $tab['label'] }}</span>
                                @if (!empty($tab['badge']))
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold tabular-nums"
                                        :class="activeMainTab === '{{ $tab['id'] }}'
                                            ? 'bg-brand-primary/10 text-brand-primary'
                                            : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50'">
                                        {{ $tab['badge'] }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Quick Tab Indicator info --}}
                    <div class="hidden lg:flex items-center text-[12px] text-black/40 dark:text-white/40 font-medium shrink-0">
                        <span x-show="activeMainTab !== 'all'" class="inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                            <span x-text="activeMainTab === 'catalog' ? 'Katalog & Layanan' : (activeMainTab === 'batch' ? 'Jadwal Pre-Order' : (activeMainTab === 'about' ? 'Profil & Dedikasi' : (activeMainTab === 'gallery' ? 'Galeri Foto' : 'Informasi & Lokasi')))"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- LAYANAN & PRODUK SHOWCASE (Apple Store Product Row Style)                 --}}
    {{-- ========================================================================= --}}
    @if (
        ($sectionVisibility['services'] || ($sectionVisibility['products'] && $landingPage->show_pos_products)) &&
            $hasServices)
        <section id="layanan" data-section="services" class="py-12 sm:py-16"
            x-show="activeMainTab === 'catalog' || activeMainTab === 'batch' || activeMainTab === 'all'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-12">

                {{-- Section Title --}}
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 block mb-1">
                        {{ $industryLabels['catalog_overline'] }}
                    </span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                        {{ $landingPage->services_title ?: $industryLabels['catalog_title'] }}
                    </h2>
                    @if ($landingPage->services_subtitle)
                        <p
                            class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 leading-relaxed font-normal tracking-[-0.01em]">
                            {{ $landingPage->services_subtitle }}</p>
                    @endif
                </div>

                {{-- Custom Services Slider & Live Search --}}
                @if ($sectionVisibility['services'] && $services->isNotEmpty())
                    <div class="space-y-5" x-data="makeSlider({{ $services->count() }}, { base: 1, sm: 2, lg: 3 }, 3600)"
                        x-show="activeMainTab === 'catalog' || activeMainTab === 'all'">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span
                                    class="text-[11px] font-semibold uppercase tracking-[0.05em] text-black/45 dark:text-white/45 block mb-0.5">Pilihan
                                    Layanan</span>
                                <h3
                                    class="font-bold text-[18px] sm:text-[20px] text-black dark:text-white tracking-[-0.015em] leading-snug">
                                    {{ $industryLabels['services_title'] }}</h3>
                            </div>

                            <div
                                class="flex items-center gap-2 flex-wrap self-stretch sm:self-auto justify-between sm:justify-end">
                                {{-- Capsule Live Search on Single Page --}}
                                <div class="relative flex-1 sm:w-60">
                                    <i data-lucide="search"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                    <input type="search" x-model="serviceSearch" @input="filterServices()"
                                        placeholder="Cari layanan..."
                                        class="w-full h-8 sm:h-9 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 pl-8 pr-7 text-[16px] sm:text-[12.5px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition">
                                    <button type="button" x-show="serviceSearch" @click="clearServiceFilter()"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>

                                {{-- Lihat Semua Pop-up Trigger --}}
                                <button type="button" @click="openAllServicesModal()"
                                    class="h-8 sm:h-9 px-3.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.14] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95 border border-black/5 dark:border-white/10 cursor-pointer whitespace-nowrap">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    <span>Lihat Semua ({{ $services->count() }})</span>
                                </button>

                                @if ($services->count() > 1)
                                    <div x-show="!serviceSearch && serviceCategory === 'all'"
                                        class="hidden sm:flex items-center gap-1.5">
                                        <button type="button" @click="prev()" aria-label="Layanan Sebelumnya"
                                            class="w-11 h-11 sm:w-10 sm:h-10 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition cursor-pointer">
                                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="next()" aria-label="Layanan Berikutnya"
                                            class="w-11 h-11 sm:w-10 sm:h-10 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition cursor-pointer">
                                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Service Category Filter Pills on Single Page --}}
                        @if ($serviceCategories->isNotEmpty())
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none pt-1">
                                <button type="button" @click="setServiceCategory('all')"
                                    class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                                    :class="serviceCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' :
                                        'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'">
                                    Semua ({{ $services->count() }})
                                </button>
                                @foreach ($serviceCategories as $sCat)
                                    @php
                                        $sCatCount = $services
                                            ->filter(fn($s) => ($s['category_id'] ?? null) == $sCat->id)
                                            ->count();
                                    @endphp
                                    <button type="button" @click="setServiceCategory('{{ $sCat->id }}')"
                                        class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                                        :class="serviceCategory === '{{ $sCat->id }}' ?
                                            'bg-brand-primary text-white shadow-sm' :
                                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'">
                                        {{ $sCat->name }}
                                        @if ($sCatCount > 0)
                                            <span
                                                class="opacity-70 tabular-nums text-[11px]">({{ $sCatCount }})</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Live Filtered Results on Single Page (when searching or category selected) --}}
                        <div x-show="serviceSearch.trim() !== '' || serviceCategory !== 'all'" x-cloak class="pt-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="svc in filteredServices" :key="svc.title">
                                    <div role="button" tabindex="0" @click="openService(svc)"
                                        class="w-full text-left bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] rounded-[24px] shadow-sm hover:shadow-md bento-card-interactive p-5 flex flex-col justify-between space-y-4 active:scale-[0.98] cursor-pointer">
                                        <div>
                                            <div
                                                class="relative w-full aspect-[16/10] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden mb-3.5 flex items-center justify-center border border-black/5 dark:border-white/10">
                                                <template x-if="svc.image_url">
                                                    <img :src="svc.image_url" :alt="svc.title" loading="lazy"
                                                        class="w-full h-full object-cover"
                                                        x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                                </template>
                                                <div :class="svc.image_url ? 'hidden' : ''"
                                                    class="w-full h-full flex items-center justify-center text-brand-primary">
                                                    <i data-lucide="sparkles" class="w-8 h-8"></i>
                                                </div>
                                                <template x-if="svc.badge">
                                                    <span
                                                        class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold uppercase tracking-wider bg-brand-primary text-white shadow-xs"
                                                        x-text="svc.badge"></span>
                                                </template>
                                            </div>
                                            <h4 class="font-semibold text-[14.5px] sm:text-[15px] text-black dark:text-white line-clamp-1 tracking-tight leading-snug"
                                                x-text="svc.title"></h4>
                                            <p class="text-[12.5px] sm:text-[13px] text-black/60 dark:text-white/60 mt-1 line-clamp-2 leading-relaxed font-normal"
                                                x-text="svc.description || 'Layanan unggulan berkualitas siap memenuhi kebutuhan Anda.'">
                                            </p>
                                        </div>
                                        <div
                                            class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                                            <div class="font-bold text-[13.5px] sm:text-[14px] text-brand-primary tabular-nums tracking-tight"
                                                x-text="svc.price || 'Hubungi kami'"></div>
                                            <span
                                                class="h-7 px-3 rounded-full bg-brand-primary text-white text-[11.5px] font-semibold flex items-center gap-1">Detail</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="filteredServices.length === 0"
                                class="py-10 text-center text-black/50 dark:text-white/50">
                                <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p class="text-[13px] font-medium">Tidak ada layanan yang cocok dengan pencarian atau
                                    kategori ini.</p>
                                <button type="button" @click="clearServiceFilter()"
                                    class="mt-2.5 inline-flex items-center gap-1.5 text-[12px] text-brand-primary font-semibold hover:underline cursor-pointer">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                    <span>Reset Filter</span>
                                </button>
                            </div>
                        </div>

                        {{-- Standard Slider when Not Searching or Filtering --}}
                        <div x-show="!serviceSearch && serviceCategory === 'all'">
                            <div class="relative overflow-hidden rounded-[28px]" @mouseenter="stop()"
                                @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                                <div class="flex transition-transform duration-500 ease-out"
                                    :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                                    @foreach ($services as $serviceIndex => $svc)
                                        <div class="w-full sm:w-1/2 lg:w-1/3 shrink-0 p-2 sm:p-2.5">
                                            <div role="button" tabindex="0"
                                                @click="openService({{ Js::from($svc) }})"
                                                class="w-full h-full text-left bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] rounded-[24px] shadow-sm hover:shadow-md bento-card-interactive p-5 flex flex-col justify-between space-y-4 active:scale-[0.98] cursor-pointer">
                                                <div>
                                                    <div
                                                        class="relative w-full aspect-[16/10] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden mb-3.5 flex items-center justify-center border border-black/5 dark:border-white/10">
                                                        @if (!empty($svc['image_url']))
                                                            <img src="{{ $svc['image_url'] }}"
                                                                alt="{{ $svc['title'] ?? 'Layanan' }}"
                                                                loading="lazy" class="w-full h-full object-cover"
                                                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                            <div
                                                                class="hidden w-full h-full flex items-center justify-center text-brand-primary">
                                                                <i data-lucide="{{ $svc['icon'] ?? 'sparkles' }}"
                                                                    class="w-8 h-8"></i>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="w-full h-full flex items-center justify-center text-brand-primary">
                                                                <i data-lucide="{{ $svc['icon'] ?? 'sparkles' }}"
                                                                    class="w-8 h-8"></i>
                                                            </div>
                                                        @endif
                                                        @if (!empty($svc['badge']))
                                                            <span
                                                                class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold uppercase tracking-wider bg-brand-primary text-white shadow-xs">
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
                                                    class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                                                    <div
                                                        class="font-bold text-[13.5px] sm:text-[14px] text-brand-primary tabular-nums tracking-tight">
                                                        @if (!empty($svc['price']))
                                                            {{ $svc['price'] }}
                                                        @else
                                                            Hubungi kami
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        @if ($allowReservation)
                                                            <button type="button"
                                                                @click.stop="openReservationModal('{{ addslashes($svc['title'] ?? ($svc['name'] ?? 'Layanan')) }}', '{{ $svc['id'] ?? '' }}')"
                                                                class="h-7 px-3 rounded-full bg-brand-primary hover:opacity-90 text-white text-[11.5px] font-semibold flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                                title="Reservasi">
                                                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                                                <span>Reservasi</span>
                                                            </button>
                                                        @elseif ($allowStorefront && !empty($svc['raw_price']) && (float) $svc['raw_price'] > 0)
                                                            <button type="button"
                                                                @click.stop="directCheckout({ id: '{{ $svc['id'] ?? '' }}', name: '{{ addslashes($svc['title'] ?? ($svc['name'] ?? 'Layanan')) }}', price: {{ (float) $svc['raw_price'] }}, image_url: '{{ $svc['image_url'] ?? '' }}' }, 1)"
                                                                class="h-7 px-3 rounded-full bg-brand-primary hover:opacity-90 text-white text-[11.5px] font-semibold flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                                title="Pesan">
                                                                <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                                                <span>Pesan</span>
                                                            </button>
                                                        @elseif ($hasWhatsapp)
                                                            <a :href="waLink('{{ addslashes($svc['title'] ?? ($svc['name'] ?? 'Layanan')) }}',
                                                                true)"
                                                                target="_blank" @click.stop
                                                                class="h-7 px-3 rounded-full bg-[#25D366] hover:opacity-90 text-white text-[11.5px] font-semibold flex items-center gap-1 transition active:scale-95 shadow-xs"
                                                                title="WA">
                                                                <svg class="w-3 h-3" fill="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                                                </svg>
                                                                <span>WA</span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Minimalist Dots Indicator --}}
                            <div class="flex items-center justify-center gap-1.5 pt-3" x-show="maxIndex() > 0">
                                <template x-for="idx in (maxIndex() + 1)" :key="idx">
                                    <button type="button" @click="goTo(idx - 1)"
                                        :class="currentIndex === (idx - 1) ? 'w-5 bg-brand-primary' :
                                            'w-1.5 bg-black/20 dark:bg-white/20'"
                                        class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                                        :aria-label="'Slide ' + idx"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Bento Hub: Pre-Order Batch & Join PO Kantoran (Bento Apple HIG) --}}
                @if (!empty($availableBatchDates) && ($storeSetting?->allow_scheduled_order || $storeSetting?->allow_customer_po))
                    @php
                        $activeBatchDate = $selectedBatchDate ?: $availableBatchDates[0]['date'];
                        $activeBatch = $selectedBatch ?: $availableBatchDates[0];
                        $shareUrl = url()->current() . '?batch=' . $activeBatchDate . (!empty($groupRef) ? '&group=' . urlencode($groupRef) : '');
                        $activeDayNameUpper = $activeBatch['day_name_upper'] ?? strtoupper($activeBatch['day_name'] ?? '');
                        $activeDayShort = $activeBatch['day_short'] ?? $activeBatch['short_date'] ?? $activeBatchDate;
                        $activeFullDate = $activeBatch['full_date'] ?? ($activeDayNameUpper . ', ' . $activeDayShort);
                        $activeQuota = $activeBatch['remaining_quota'] ?? null;
                        $activeUnit = $activeBatch['quota_unit'] ?? ($storeSetting?->preorder_quota_unit ?? 'PCS');
                        $shareWaText = "🍱 *Pre-Order {$business->name}*\n📅 *Pengiriman:* {$activeFullDate}\n"
                            . (!empty($groupRef) ? "🏢 *Pesanan Kantor / Tim:* {$groupRef}\n" : "")
                            . "✨ Yuk pesan bareng! Pilih menu favoritmu di link berikut:\n👉 " . $shareUrl;
                    @endphp
                    <div class="pt-4" x-show="activeMainTab === 'batch' || activeMainTab === 'catalog' || activeMainTab === 'all'">
                        <div class="p-4 sm:p-6 rounded-[24px] bg-gradient-to-br from-brand-primary/[0.06] via-brand-primary/[0.02] to-black/[0.02] dark:to-white/[0.03] border border-brand-primary/20 shadow-xs space-y-4">
                            {{-- Header Banner --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-brand-primary text-white shadow-2xs">
                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                            <span>Pre-Order Terbuka</span>
                                        </span>
                                        <template x-if="checkoutForm.group_name">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6] dark:text-[#AF52DE]">
                                                <i data-lucide="users" class="w-3 h-3"></i>
                                                <span>Pesanan Kantor: <strong x-text="checkoutForm.group_name"></strong></span>
                                            </span>
                                        </template>
                                        @if (!empty($groupRef))
                                            <span x-show="!checkoutForm.group_name" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6] dark:text-[#AF52DE]">
                                                <i data-lucide="users" class="w-3 h-3"></i>
                                                <span>Pesanan Kantor: {{ $groupRef }}</span>
                                            </span>
                                        @endif
                                        @if ($storeSetting?->cut_off_time)
                                            <span class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3 h-3 text-amber-500"></i>
                                                <span>Batas pesan: {{ substr((string)$storeSetting->cut_off_time, 0, 5) }} WIB</span>
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-[17px] sm:text-[19px] font-extrabold text-black dark:text-white tracking-tight">
                                        Pesan Bareng / Join Pre-Order
                                    </h3>
                                    <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed max-w-2xl">
                                        Pilih jadwal batch pengiriman di bawah, tentukan menu pilihan Anda, atau bagikan link ini ke WhatsApp rekan kantor agar pesanan diantar bersamaan.
                                    </p>
                                </div>

                                {{-- Action Buttons: Copy Link & WA Share & Group Order --}}
                                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                    <button type="button"
                                        @click="openCreateGroupOrderModal()"
                                        class="px-3.5 py-2 rounded-[12px] bg-gradient-to-r from-[#5856D6] to-brand-primary hover:opacity-95 text-white text-[12px] font-bold transition active:scale-95 flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        <span>Pesan Bareng (Group Order)</span>
                                    </button>
                                    <button type="button"
                                        @click="copyBatchLink(checkoutForm.scheduled_date || '{{ $activeBatchDate }}', '{{ $activeDayNameUpper }}', '{{ $activeDayShort }}')"
                                        class="px-3.5 py-2 rounded-[12px] bg-white dark:bg-black/40 border border-black/10 dark:border-white/10 hover:border-brand-primary text-black dark:text-white text-[12px] font-bold transition active:scale-95 flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                        <i data-lucide="copy" class="w-3.5 h-3.5 text-brand-primary"></i>
                                        <span>Salin Link Batch</span>
                                    </button>
                                    <button type="button"
                                        @click="shareBatchWa(checkoutForm.scheduled_date || '{{ $activeBatchDate }}', '{{ $activeDayNameUpper }}', '{{ $activeDayShort }}')"
                                        class="px-3.5 py-2 rounded-[12px] bg-[#25D366] hover:bg-[#20bd5a] text-white text-[12px] font-bold transition active:scale-95 flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Ajak Teman Kantor (WA)</span>
                                        <span class="sm:hidden">WA</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Batch Date Chips Grid --}}
                            <div>
                                <div class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-2 flex items-center justify-between">
                                    <span>Pilihan Jadwal Batch Pengiriman</span>
                                    <span class="text-[11px] font-normal text-brand-primary">Pilihan Anda otomatis tersimpan saat memesan</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                    @foreach ($availableBatchDates as $batch)
                                        @php
                                            $bSoldOut = (bool) ($batch['is_sold_out'] ?? $batch['is_full'] ?? false);
                                            $bQuota = $batch['remaining_quota'] ?? null;
                                            $bDayName = $batch['day_name_upper'] ?? strtoupper($batch['day_name'] ?? '');
                                            $bDayShort = $batch['day_short'] ?? $batch['short_date'] ?? ($batch['date'] ?? '');
                                            $bDate = $batch['date'] ?? '';
                                            $bUnit = $batch['quota_unit'] ?? ($storeSetting?->preorder_quota_unit ?? 'PCS');
                                        @endphp
                                        <button type="button"
                                            @if (!$bSoldOut)
                                                @click="selectBatch('{{ $bDate }}')"
                                            @endif
                                            class="p-3 rounded-[16px] border text-left transition relative flex flex-col justify-between cursor-pointer active:scale-[0.98] {{ $bSoldOut ? 'opacity-50 cursor-not-allowed bg-black/5 dark:bg-white/5 border-dashed border-black/10 dark:border-white/10' : '' }}"
                                            :class="checkoutForm.scheduled_date === '{{ $bDate }}' ? 'border-brand-primary bg-white dark:bg-black/40 text-brand-primary ring-2 ring-brand-primary/30 shadow-xs' : 'border-black/10 dark:border-white/10 bg-white/70 dark:bg-black/20 text-black/80 dark:text-white/80 hover:border-black/20 dark:hover:border-white/20'">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[11px] font-extrabold uppercase tracking-wider opacity-80">{{ $bDayName }}</span>
                                                @if ($bSoldOut)
                                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">Penuh</span>
                                                @elseif ($bQuota !== null)
                                                    <span class="text-[10px] font-bold opacity-75 tabular-nums">Sisa {{ $bQuota }} {{ $bUnit }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 font-extrabold text-[14px] tabular-nums tracking-tight">
                                                {{ $bDayShort }}
                                            </div>
                                            @if (!empty($batch['note']))
                                                <div class="text-[10.5px] text-black/50 dark:text-white/50 truncate mt-0.5">{{ $batch['note'] }}</div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Live POS Products Slider & Interactive Catalog --}}
                @if ($sectionVisibility['products'] && $landingPage->show_pos_products && $posProducts->isNotEmpty())
                    <div class="pt-6 space-y-5" x-data="makeSlider({{ $posProducts->count() }}, { base: 1, sm: 2, lg: 4 }, 3200)"
                        x-show="activeMainTab === 'catalog' || activeMainTab === 'batch' || activeMainTab === 'all'">
                        {{-- Catalog Header & Search Controls --}}
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
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 hover:bg-brand-200 text-brand-primary text-[11.5px] font-semibold transition border border-brand-primary/20 shadow-xs">
                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                            <span>Pesan</span>
                                        </button>
                                    @endif
                                    @if ($allowCustomerPo && $isProductionB2b)
                                        <button type="button" @click="openCustomerPoModal()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#5856D6]/10 hover:bg-[#5856D6]/15 text-[#5856D6] dark:text-[#AF52DE] text-[11.5px] font-semibold transition border border-[#5856D6]/20 shadow-xs">
                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                            <span>Pesan PO</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-2 flex-wrap self-stretch sm:self-auto justify-between sm:justify-end">
                                {{-- Capsule Live Search on Single Page --}}
                                <div class="relative flex-1 sm:w-60">
                                    <i data-lucide="search"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                    <input type="search" x-model="productSearch" @input="filterProducts()"
                                        placeholder="Cari nama produk..."
                                        class="w-full h-8 sm:h-9 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 pl-8 pr-7 text-[16px] sm:text-[12.5px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition">
                                    <button type="button" x-show="productSearch"
                                        @click="productSearch = ''; filterProducts()"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>

                                {{-- Lihat Semua Pop-up Trigger --}}
                                <button type="button" @click="openAllProductsModal()"
                                    class="h-8 sm:h-9 px-3.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.14] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95 border border-black/5 dark:border-white/10 cursor-pointer whitespace-nowrap">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                    <span>Lihat Semua ({{ $posProducts->count() }})</span>
                                </button>

                                @if ($posProducts->count() > 1)
                                    <div x-show="!productSearch && productCategory === 'all'"
                                        class="hidden sm:flex items-center gap-1.5">
                                        <button type="button" @click="prev()" aria-label="Produk Sebelumnya"
                                            class="w-11 h-11 sm:w-10 sm:h-10 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition cursor-pointer">
                                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="next()" aria-label="Produk Berikutnya"
                                            class="w-11 h-11 sm:w-10 sm:h-10 rounded-full bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-black/70 dark:text-white/70 flex items-center justify-center transition cursor-pointer">
                                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Category Filter Pills on Single Page --}}
                        @if ($productCategories->isNotEmpty())
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none pt-1">
                                <button type="button" @click="setProductCategory('all')"
                                    class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                                    :class="productCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' :
                                        'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'">
                                    Semua ({{ $posProducts->count() }})
                                </button>
                                @foreach ($productCategories as $category)
                                    @php
                                        $catCount = $posProducts
                                            ->filter(fn($p) => $p->category_id == $category->id)
                                            ->count();
                                    @endphp
                                    <button type="button" @click="setProductCategory('{{ $category->id }}')"
                                        class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                                        :class="productCategory === '{{ $category->id }}' ?
                                            'bg-brand-primary text-white shadow-sm' :
                                            'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'">
                                        {{ $category->name }}
                                        @if ($catCount > 0)
                                            <span
                                                class="opacity-70 tabular-nums text-[11px]">({{ $catCount }})</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Filtered Results Grid on Single Page (when searching or category selected) --}}
                        <div x-show="productSearch.trim() !== '' || productCategory !== 'all'" x-cloak class="pt-2">
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-4">
                                <template x-for="prod in filteredProducts" :key="prod.id">
                                    <div role="button" tabindex="0" @click="openProduct(prod)"
                                        class="text-left bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] rounded-[24px] shadow-sm hover:shadow-md bento-card-interactive p-4 flex flex-col justify-between space-y-3 active:scale-[0.98] cursor-pointer">
                                        <div>
                                            <div
                                                class="w-full aspect-square rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mb-2.5 overflow-hidden border border-black/5 dark:border-white/10 relative">
                                                <template x-if="prod.image_url">
                                                    <img :src="prod.image_url" :alt="prod.name" loading="lazy"
                                                        class="w-full h-full object-cover"
                                                        x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                                </template>
                                                <i data-lucide="package" :class="prod.image_url ? 'hidden' : ''"
                                                    class="w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                <template x-if="prod.is_preorder">
                                                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-[6px] bg-amber-500/90 dark:bg-amber-600/90 text-white backdrop-blur text-[10px] font-semibold tracking-wide shadow-xs tabular-nums"
                                                        x-text="prod.preorder_lead_days > 1 ? 'PO H-' + prod.preorder_lead_days : 'Pre-Order'"></span>
                                                </template>
                                            </div>
                                            <h4 class="font-semibold text-[13.5px] sm:text-[14px] text-black dark:text-white line-clamp-2 tracking-tight leading-snug"
                                                x-text="prod.name"></h4>
                                            <span x-show="prod.category"
                                                class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5 font-normal"
                                                x-text="prod.category"></span>
                                        </div>
                                        <div
                                            class="pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1.5">
                                            <template x-if="Number(prod.price || 0) > 0">
                                                <span
                                                    class="font-bold text-[13.5px] sm:text-[14px] text-brand-primary tabular-nums tracking-tight"
                                                    x-text="'Rp ' + Number(prod.price || 0).toLocaleString('id-ID')"></span>
                                            </template>
                                            <template x-if="Number(prod.price || 0) <= 0">
                                                <span
                                                    class="font-medium text-[12px] sm:text-[12.5px] text-black/60 dark:text-white/60 italic">Hubungi Kami</span>
                                            </template>
                                            <div class="flex items-center gap-1.5">
                                                <template x-if="Number(prod.price || 0) > 0">
                                                    <button type="button" @click.stop="directCheckout(prod, 1)"
                                                        class="h-7 px-3 rounded-full bg-brand-primary text-white text-[11.5px] font-semibold hover:opacity-90 flex items-center gap-1.5 transition active:scale-95 shadow-xs cursor-pointer"
                                                        title="Pesan">
                                                        <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                                        <span>Pesan</span>
                                                    </button>
                                                </template>
                                                <template x-if="Number(prod.price || 0) <= 0">
                                                    <a :href="waLink(prod.name, false)" target="_blank" rel="noopener" @click.stop
                                                        class="h-7 px-2.5 rounded-full bg-[#25D366] text-white text-[11px] font-semibold hover:opacity-90 flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                        title="Hubungi via WhatsApp">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                                                        <span>Tanya WA</span>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="filteredProducts.length === 0"
                                class="py-10 text-center text-black/50 dark:text-white/50">
                                <i data-lucide="package-open" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p class="text-[13px] font-medium">Tidak ada produk yang cocok dengan pencarian atau
                                    kategori ini.</p>
                                <button type="button" @click="clearProductFilter()"
                                    class="mt-2.5 inline-flex items-center gap-1.5 text-[12px] text-brand-primary font-semibold hover:underline cursor-pointer">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                    <span>Reset Filter</span>
                                </button>
                            </div>
                        </div>

                        {{-- Standard Slider when No Filter/Search is Active --}}
                        <div x-show="!productSearch && productCategory === 'all'">
                            <div class="relative overflow-hidden rounded-[28px]" @mouseenter="stop()"
                                @mouseleave="start()" @touchstart="stop()" @touchend="start()">
                                <div class="flex transition-transform duration-500 ease-out"
                                    :style="'transform: translateX(-' + (currentIndex * (100 / perView)) + '%)'">
                                    @foreach ($posProducts as $prod)
                                        <div class="w-full sm:w-1/2 lg:w-1/4 shrink-0 p-2 sm:p-2.5">
                                            <div role="button" tabindex="0"
                                                @click="openProduct(products.find(product => product.id === '{{ $prod->id }}'))"
                                                class="w-full h-full text-left bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] rounded-[24px] shadow-sm hover:shadow-md bento-card-interactive p-4 flex flex-col justify-between space-y-3 active:scale-[0.98] cursor-pointer">
                                                <div>
                                                    <div
                                                        class="w-full aspect-square rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mb-2.5 overflow-hidden border border-black/5 dark:border-white/10 relative">
                                                        @if ($prod->image_url)
                                                            <img src="{{ $prod->image_url }}"
                                                                alt="{{ $prod->name }}" loading="lazy"
                                                                class="w-full h-full object-cover"
                                                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                            <i data-lucide="package"
                                                                class="hidden w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                        @else
                                                            <i data-lucide="package"
                                                                class="w-8 h-8 text-black/30 dark:text-white/30"></i>
                                                        @endif
                                                        @if ($prod->is_preorder)
                                                            <span class="absolute top-2 right-2 px-2 py-0.5 rounded-[6px] bg-amber-500/90 dark:bg-amber-600/90 text-white backdrop-blur text-[10px] font-semibold tracking-wide shadow-xs tabular-nums">
                                                                {{ $prod->preorder_lead_days > 1 ? 'PO H-' . $prod->preorder_lead_days : 'Pre-Order' }}
                                                            </span>
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
                                                    @if (($prod->show_price_on_web ?? true) && $prod->selling_price > 0)
                                                        <span
                                                            class="font-bold text-[13.5px] sm:text-[14px] text-brand-primary tabular-nums tracking-tight">Rp
                                                            {{ number_format($prod->selling_price, 0, ',', '.') }}</span>
                                                        <div class="flex items-center gap-1.5">
                                                            <button type="button"
                                                                @click.stop="directCheckout(products.find(p => p.id === '{{ $prod->id }}'), 1)"
                                                                class="h-7 px-3 rounded-full bg-brand-primary text-white text-[11.5px] font-semibold hover:opacity-90 flex items-center gap-1.5 transition active:scale-95 shadow-xs cursor-pointer"
                                                                title="Pesan">
                                                                <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                                                <span>Pesan</span>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="font-medium text-[12px] sm:text-[12.5px] text-black/60 dark:text-white/60 italic">Hubungi Kami</span>
                                                        <div class="flex items-center gap-1.5">
                                                            <a :href="waLink('{{ addslashes($prod->name) }}', false)" target="_blank" rel="noopener" @click.stop
                                                                class="h-7 px-2.5 rounded-full bg-[#25D366] text-white text-[11px] font-semibold hover:opacity-90 flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                                title="Hubungi via WhatsApp">
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                                                                <span>Tanya WA</span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Minimalist Dots Indicator --}}
                            <div class="flex items-center justify-center gap-1.5 pt-3" x-show="maxIndex() > 0">
                                <template x-for="idx in (maxIndex() + 1)" :key="idx">
                                    <button type="button" @click="goTo(idx - 1)"
                                        :class="currentIndex === (idx - 1) ? 'w-5 bg-brand-primary' :
                                            'w-1.5 bg-black/20 dark:bg-white/20'"
                                        class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                                        :aria-label="'Slide ' + idx"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- GALERI FOTO (Apple Bento Photo Grid - Max 5 Bento Showcase)               --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['gallery'] && $allGalleryImages->isNotEmpty())
        <section id="galeri" data-section="gallery" class="py-12 sm:py-16"
            x-show="activeMainTab === 'gallery' || activeMainTab === 'all'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center space-y-2">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 block mb-1">Galeri
                        Suasana</span>
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
        <section id="tentang" data-section="about" class="py-12 sm:py-16"
            x-show="activeMainTab === 'about' || activeMainTab === 'all'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center space-y-2">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 block mb-1">Profil
                        &amp; Dedikasi</span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-[36px] font-bold tracking-tight lg:tracking-[-0.025em] leading-[1.2] text-black dark:text-white">
                        Tentang {{ $business->name }}</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

                    {{-- Left Bento Card: Story & Philosophy --}}
                    @if ($landingPage->about_title || $landingPage->about_story || $landingPage->about_image_url)
                        <div
                            class="{{ !empty($normalizedOperationalHours) ? 'lg:col-span-7' : 'lg:col-span-12' }} bento-card p-7 sm:p-9 flex flex-col justify-between space-y-6">
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

                                {{-- Dynamic Stats (Clients, Experience, Rating from Landing Page CMS) --}}
                                @if (
                                    !empty($statsConfig) &&
                                        (!empty($statsConfig['clients']) || !empty($statsConfig['experience']) || !empty($statsConfig['rating'])))
                                    <div
                                        class="grid grid-cols-3 gap-3 pt-4 border-t border-black/5 dark:border-white/10">
                                        @if (!empty($statsConfig['clients']))
                                            <div class="space-y-0.5">
                                                <div
                                                    class="text-[17px] sm:text-[20px] font-extrabold text-brand-primary tabular-nums tracking-tight">
                                                    {{ $statsConfig['clients'] }}
                                                </div>
                                                <div
                                                    class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 font-medium leading-tight">
                                                    {{ $statsConfig['clients_label'] ?? 'Pelanggan Puas' }}
                                                </div>
                                            </div>
                                        @endif
                                        @if (!empty($statsConfig['experience']))
                                            <div class="space-y-0.5">
                                                <div
                                                    class="text-[17px] sm:text-[20px] font-extrabold text-brand-primary tabular-nums tracking-tight">
                                                    {{ $statsConfig['experience'] }}
                                                </div>
                                                <div
                                                    class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 font-medium leading-tight">
                                                    {{ $statsConfig['experience_label'] ?? 'Jam Terbang' }}
                                                </div>
                                            </div>
                                        @endif
                                        @if (!empty($statsConfig['rating']))
                                            <div class="space-y-0.5">
                                                <div
                                                    class="text-[17px] sm:text-[20px] font-extrabold text-amber-500 tabular-nums tracking-tight">
                                                    {{ $statsConfig['rating'] }}
                                                </div>
                                                <div
                                                    class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 font-medium leading-tight">
                                                    {{ $statsConfig['rating_label'] ?? 'Rating Ulasan' }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
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
                    @if (!empty($normalizedOperationalHours))
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
                                    @foreach ($normalizedOperationalHours as $h)
                                        <div class="py-2.5 px-2 flex justify-between items-center">
                                            <span
                                                class="font-medium text-[13px] sm:text-[13.5px] text-black/75 dark:text-white/75">{{ $h['day'] ?? 'Hari' }}</span>
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

                            @if ($allowReservation)
                                <button type="button" @click="openReservationModal()"
                                    class="w-full h-11 rounded-full bg-brand-primary text-white font-semibold text-[13px] sm:text-[13.5px] tracking-tight flex items-center justify-center gap-2 hover:opacity-95 active:scale-[0.98] transition shadow-sm cursor-pointer"
                                    title="Reservasi">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>Reservasi</span>
                                </button>
                            @elseif ($hasWhatsapp)
                                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank"
                                    class="w-full h-11 rounded-full bg-[#25D366] text-white font-semibold text-[13px] sm:text-[13.5px] tracking-tight flex items-center justify-center gap-2 hover:opacity-95 active:scale-[0.98] transition shadow-sm"
                                    title="Hubungi WA">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                    </svg>
                                    <span>WA</span>
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
        <section class="py-12 sm:py-16"
            x-show="activeMainTab === 'about' || activeMainTab === 'all'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 block mb-1">Kepuasan
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
        <section id="faq" class="py-12 sm:py-16"
            x-show="activeMainTab === 'info' || activeMainTab === 'all'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-7" x-data="{ openFaq: null }">
                <div class="text-center space-y-2">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 block mb-1">Informasi
                        &amp; Panduan</span>
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
        <section id="lokasi" data-section="contact" class="py-12 sm:py-16"
            x-show="activeMainTab === 'info' || activeMainTab === 'all'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 block mb-1">Alamat
                        &amp; Akses</span>
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
    <div x-show="activeModal && activeModal !== 'gallery-lightbox' && activeModal !== 'all-products' && activeModal !== 'all-services'"
        x-cloak @keydown.escape.window="activeModal = null" @click.self="activeModal = null"
        class="storefront-sheet-overlay fixed inset-0 z-[70] bg-black/40 backdrop-blur-sm p-4 flex items-center justify-center"
        role="dialog" aria-modal="true">
        <div class="storefront-sheet w-full max-h-[90vh] overflow-y-auto rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] p-5 sm:p-7 transition-all duration-300"
            :class="activeModal === 'all-gallery' ? 'w-full max-w-full sm:max-w-4xl lg:max-w-5xl' : 'w-full max-w-full sm:max-w-2xl md:max-w-3xl lg:max-w-4xl'">

            <div
                class="flex items-center justify-between gap-3 mb-5 border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white tracking-tight"
                    x-text="activeModal === 'service' ? 'Detail Layanan' : activeModal === 'product' ? 'Detail Produk' : 'Koleksi Galeri Foto'">
                </h2>
                <button type="button" @click="activeModal = null"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 flex items-center justify-center hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition cursor-pointer shrink-0"
                    aria-label="Tutup dialog">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Single Product / Service Detail View --}}
            <div x-show="activeModal === 'service' || activeModal === 'product'"
                class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 items-start">
                <div
                    class="relative aspect-[4/3] rounded-[18px] bg-black/[0.03] dark:bg-white/[0.05] overflow-hidden flex items-center justify-center border border-black/5 dark:border-white/10">
                    <template x-if="activeItem && activeItem.image_url">
                        <img :src="activeItem.image_url" :alt="activeItem.name || activeItem.title"
                            class="w-full h-full object-cover" x-on:error="activeItem.image_url = null">
                    </template>
                    <i x-show="!activeItem || !activeItem.image_url" data-lucide="package"
                        class="w-12 h-12 text-black/25 dark:text-white/25"></i>
                    <span x-show="activeItem && (activeItem.category || activeModal === 'service')"
                        class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full bg-white/90 dark:bg-black/80 backdrop-blur text-[10.5px] font-semibold uppercase tracking-wider text-brand-primary shadow-sm shrink-0 whitespace-nowrap"
                        x-text="activeItem?.category || 'Layanan'"></span>
                    <template x-if="activeItem?.is_preorder">
                        <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-[6px] bg-amber-500/90 dark:bg-amber-600/90 text-white backdrop-blur text-[10.5px] font-semibold tracking-wide shadow-xs tabular-nums shrink-0 whitespace-nowrap"
                            x-text="activeItem.preorder_lead_days > 1 ? 'PO H-' + activeItem.preorder_lead_days : 'Pre-Order'"></span>
                    </template>
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
                            <span class="text-[11px] text-black/45 dark:text-white/45 font-normal shrink-0"
                                x-text="activeModal === 'service' ? 'Estimasi biaya' : 'Harga resmi'"></span>
                        </div>
                        {{-- Stepper for physical products --}}
                        <div class="flex items-center justify-between py-1 bg-black/[0.02] dark:bg-white/[0.03] px-3.5 py-2 rounded-[14px] border border-black/5 dark:border-white/5"
                            x-show="activeModal === 'product' && isStorefrontEnabled && activeItem?.show_price_on_web !== false && Number(activeItem?.price || 0) > 0">
                            <span class="text-[12.5px] font-semibold text-black/70 dark:text-white/70">Jumlah
                                Pesanan:</span>
                            <div
                                class="flex items-center gap-2.5 bg-white dark:bg-black/40 px-3 py-1 rounded-full border border-black/10 dark:border-white/10 shadow-xs">
                                <button type="button" @click="if (modalQty > 1) modalQty--"
                                    class="w-6 h-6 flex items-center justify-center text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-bold text-[14px] active:scale-90 transition cursor-pointer">&minus;</button>
                                <span
                                    class="text-[13px] font-extrabold text-black dark:text-white min-w-5 text-center tabular-nums"
                                    x-text="modalQty"></span>
                                <button type="button" @click="modalQty++"
                                    class="w-6 h-6 flex items-center justify-center text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-bold text-[14px] active:scale-90 transition cursor-pointer">&plus;</button>
                            </div>
                        </div>

                        {{-- Group Order Item Notes (if active) --}}
                        <div x-show="activeModal === 'product' && groupOrder.active && !groupOrder.data?.is_locked && !groupOrder.data?.is_checked_out && Number(activeItem?.price || 0) > 0" class="pt-1">
                            <input type="text" x-model="groupOrder.itemNotes"
                                placeholder="Catatan untuk Anda (cth: Budi - Pedas sedang, sambal pisah)"
                                class="w-full h-9 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-1 focus:ring-brand-primary">
                        </div>

                        {{-- Group Order Locked Notice --}}
                        <div x-show="activeModal === 'product' && groupOrder.active && groupOrder.data?.is_locked"
                            class="p-2.5 rounded-[12px] bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[11.5px] font-semibold flex items-center gap-1.5">
                            <i data-lucide="lock" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Pesanan bersama sedang dikunci oleh Host. Anda tidak dapat menambah item baru.</span>
                        </div>

                        {{-- Product Modal Actions --}}
                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap" x-show="activeModal === 'product'">
                            <template x-if="groupOrder.active && !groupOrder.data?.is_locked && !groupOrder.data?.is_checked_out && isStorefrontEnabled && activeItem?.show_price_on_web !== false && Number(activeItem?.price || 0) > 0">
                                <button type="button"
                                    @click="addGroupOrderItem(activeItem, modalQty, groupOrder.itemNotes); groupOrder.itemNotes = '';"
                                    :disabled="groupOrder.actionLoading"
                                    class="flex-1 h-10 px-3 rounded-full bg-gradient-to-r from-[#5856D6] to-brand-primary text-white text-[12.5px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 whitespace-nowrap shrink-0">
                                    <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                                    <span>+ Pesan Bareng</span>
                                </button>
                            </template>
                            <template x-if="isStorefrontEnabled && activeItem?.show_price_on_web !== false && Number(activeItem?.price || 0) > 0">
                                <button type="button"
                                    @click="directCheckout(activeItem, modalQty); activeModal = null;"
                                    class="flex-1 h-10 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap shrink-0">
                                    <i data-lucide="shopping-bag" class="w-4 h-4 shrink-0"></i>
                                    <span>Pesan Sendiri</span>
                                </button>
                            </template>
                            <a :href="activeItem ? waLink(activeItem.title || activeItem.name, false) : '#'"
                                target="_blank" rel="noopener"
                                class="h-10 px-4 rounded-full text-[13px] font-semibold tracking-tight transition flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0"
                                :class="!isStorefrontEnabled || activeItem?.show_price_on_web === false || Number(activeItem?.price || 0) <= 0 ? 'flex-1 bg-[#25D366] text-white hover:opacity-90 shadow-xs' : 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20'"
                                title="WhatsApp">
                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                                <span x-text="(!isStorefrontEnabled || activeItem?.show_price_on_web === false || Number(activeItem?.price || 0) <= 0) ? 'Hubungi via WhatsApp' : 'WA'"></span>
                            </a>
                        </div>

                        {{-- Service Modal Actions --}}
                        <div class="flex items-center gap-2" x-show="activeModal === 'service'">
                            @if ($allowReservation)
                                <button type="button"
                                    @click="activeModal = null; openReservationModal(activeItem?.title || activeItem?.name, activeItem?.id)"
                                    class="flex-1 h-10 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span>Reservasi</span>
                                </button>
                            @elseif ($allowStorefront)
                                <button type="button"
                                    @click="activeModal = null; directCheckout({ id: activeItem?.id, name: (activeItem?.title || activeItem?.name), price: (activeItem?.raw_price || 0), image_url: activeItem?.image_url }, 1)"
                                    class="flex-1 h-10 px-4 rounded-full bg-brand-primary text-white text-[13px] font-semibold tracking-tight shadow-sm hover:opacity-90 active:scale-[0.97] transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>Pesan</span>
                                </button>
                            @endif
                            <a :href="activeItem ? waLink(activeItem.title || activeItem.name, true) : '#'"
                                target="_blank" rel="noopener"
                                class="h-10 px-4 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 text-[13px] font-semibold tracking-tight transition flex items-center justify-center gap-1.5 cursor-pointer"
                                :class="!{{ json_encode($allowReservation || $allowStorefront) }} ? 'flex-1' : ''"
                                title="WhatsApp">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                                <span>WA</span>
                            </a>
                        </div>
                    </div>
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

                <div class="pt-3 border-t border-black/5 dark:border-white/5">
                    <span class="text-[12px] text-black/50 dark:text-white/50 font-normal">
                        Klik foto apa saja untuk membuka penampil layar penuh.
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- FULL LAYOUT CATALOG MODAL (Apple Storefront Full View for Desktop/Tablet)  --}}
    {{-- ========================================================================= --}}
    <div x-show="activeModal === 'all-products' || activeModal === 'all-services'" x-cloak
        @keydown.escape.window="activeModal = null"
        class="storefront-sheet-overlay fixed inset-0 z-[80] bg-black/60 backdrop-blur-md p-2 sm:p-4 md:p-6 lg:p-8 flex items-center justify-center transition-all duration-300"
        role="dialog" aria-modal="true">

        <div class="storefront-sheet storefront-catalog-sheet w-full h-full max-h-[96vh] md:max-h-[90vh] md:w-[94vw] lg:w-[92vw] xl:max-w-7xl rounded-[20px] sm:rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.5)] flex flex-col overflow-hidden transition-all duration-300"
            @click.outside="activeModal = null">

            {{-- 1. Pinned Header --}}
            <div
                class="px-5 py-3.5 sm:px-7 sm:py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md z-10 shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center shrink-0">
                        <i :data-lucide="activeModal === 'all-products' ? 'shopping-bag' : 'sparkles'"
                            class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[16px] sm:text-[19px] font-bold text-black dark:text-white tracking-tight leading-tight"
                            x-text="activeModal === 'all-products' ? 'Semua Produk' : 'Semua Layanan'"></h2>
                        <p class="text-[11.5px] sm:text-[12.5px] text-black/50 dark:text-white/50 font-normal mt-0.5">
                            <span
                                x-text="activeModal === 'all-products' ? (filteredProducts.length + ' produk ditemukan') : (filteredServices.length + ' layanan ditemukan')"></span>
                            <span>&bull; {{ $business->name }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center p-0.5 rounded-full bg-black/5 dark:bg-white/10 border border-black/5 dark:border-white/5 text-[11.5px] sm:text-[12px] font-semibold">
                        <button type="button" @click="openAllProductsModal()"
                            class="px-3 py-1 rounded-full transition cursor-pointer"
                            :class="activeModal === 'all-products' ?
                                'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                            Produk ({{ $posProducts->count() }})
                        </button>
                        <button type="button" @click="openAllServicesModal()"
                            class="px-3 py-1 rounded-full transition cursor-pointer"
                            :class="activeModal === 'all-services' ?
                                'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' :
                                'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                            Layanan ({{ $services->count() }})
                        </button>
                    </div>

                    <button type="button" @click="activeModal = null"
                        class="h-9 px-3 sm:px-4 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 flex items-center gap-1.5 transition text-[12px] sm:text-[13px] font-semibold active:scale-95 cursor-pointer"
                        aria-label="Tutup katalog">
                        <span class="hidden sm:inline">Tutup</span>
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- 2. Pinned Search & Filter Controls --}}
            <div
                class="px-5 py-3 sm:px-7 sm:py-3.5 border-b border-black/[0.05] dark:border-white/[0.06] bg-black/[0.015] dark:bg-white/[0.02] space-y-2.5 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    {{-- Search Capsule --}}
                    <div class="relative flex-1">
                        <i data-lucide="search"
                            class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
                        <template x-if="activeModal === 'all-products'">
                            <input type="search" x-model="productSearch" @input="filterProducts()"
                                placeholder="Cari nama atau kategori produk..."
                                class="w-full h-9 sm:h-10 rounded-full bg-white dark:bg-white/[0.08] border border-black/10 dark:border-white/15 pl-10 pr-9 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition shadow-xs">
                        </template>
                        <template x-if="activeModal === 'all-services'">
                            <input type="search" x-model="serviceSearch" @input="filterServices()"
                                placeholder="Cari nama atau kategori layanan..."
                                class="w-full h-9 sm:h-10 rounded-full bg-white dark:bg-white/[0.08] border border-black/10 dark:border-white/15 pl-10 pr-9 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 transition shadow-xs">
                        </template>
                        <button type="button"
                            x-show="(activeModal === 'all-products' && productSearch) || (activeModal === 'all-services' && serviceSearch)"
                            @click="activeModal === 'all-products' ? (productSearch = '', filterProducts()) : (serviceSearch = '', filterServices())"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Reset Filter Button --}}
                    <button type="button"
                        x-show="(activeModal === 'all-products' && (productSearch || productCategory !== 'all')) || (activeModal === 'all-services' && (serviceSearch || serviceCategory !== 'all'))"
                        @click="activeModal === 'all-products' ? clearProductFilter() : clearServiceFilter()"
                        class="h-9 sm:h-10 px-3.5 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 text-[11.5px] sm:text-[12px] font-semibold flex items-center justify-center gap-1.5 transition active:scale-95 whitespace-nowrap cursor-pointer">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset Filter</span>
                    </button>
                </div>

                {{-- Category Filter Pills in Modal --}}
                <template x-if="activeModal === 'all-products'">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 scrollbar-none">
                        <button type="button" @click="setProductCategory('all')"
                            class="px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full text-[11.5px] sm:text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                            :class="productCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' :
                                'bg-white dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.12] border border-black/5 dark:border-white/10'">
                            Semua ({{ $posProducts->count() }})
                        </button>
                        @foreach ($productCategories as $cat)
                            @php
                                $catCount = $posProducts->filter(fn($p) => $p->category_id == $cat->id)->count();
                            @endphp
                            <button type="button" @click="setProductCategory('{{ $cat->id }}')"
                                class="px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full text-[11.5px] sm:text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                                :class="productCategory === '{{ $cat->id }}' ?
                                    'bg-brand-primary text-white shadow-sm' :
                                    'bg-white dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.12] border border-black/5 dark:border-white/10'">
                                {{ $cat->name }}
                                @if ($catCount > 0)
                                    <span class="opacity-70 tabular-nums text-[10.5px]">({{ $catCount }})</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </template>

                <template x-if="activeModal === 'all-services'">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 scrollbar-none">
                        <button type="button" @click="setServiceCategory('all')"
                            class="px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full text-[11.5px] sm:text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                            :class="serviceCategory === 'all' ? 'bg-brand-primary text-white shadow-sm' :
                                'bg-white dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.12] border border-black/5 dark:border-white/10'">
                            Semua ({{ $services->count() }})
                        </button>
                        @foreach ($serviceCategories as $sCat)
                            @php
                                $sCatCount = $services
                                    ->filter(fn($s) => ($s['category_id'] ?? null) == $sCat->id)
                                    ->count();
                            @endphp
                            <button type="button" @click="setServiceCategory('{{ $sCat->id }}')"
                                class="px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full text-[11.5px] sm:text-[12px] font-semibold tracking-tight transition whitespace-nowrap active:scale-95 cursor-pointer"
                                :class="serviceCategory === '{{ $sCat->id }}' ?
                                    'bg-brand-primary text-white shadow-sm' :
                                    'bg-white dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.12] border border-black/5 dark:border-white/10'">
                                {{ $sCat->name }}
                                @if ($sCatCount > 0)
                                    <span class="opacity-70 tabular-nums text-[10.5px]">({{ $sCatCount }})</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </template>
            </div>

            {{-- 3. Scrollable Grid Container (Full Layout on Desktop & Tablet) --}}
            <div class="flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6 lg:p-7">
                {{-- Product Grid --}}
                <div x-show="activeModal === 'all-products'">
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 lg:gap-4.5">
                        <template x-for="prod in filteredProducts" :key="prod.id">
                            <div role="button" tabindex="0" @click="openProduct(prod)"
                                class="text-left p-3 sm:p-3.5 rounded-[18px] border border-black/5 dark:border-white/10 hover:border-brand-primary/40 dark:hover:border-brand-primary/40 transition flex flex-col justify-between group bg-black/[0.02] dark:bg-white/[0.03] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] cursor-pointer">
                                <div>
                                    <div
                                        class="aspect-square rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex items-center justify-center mb-2.5 relative border border-black/5 dark:border-white/10">
                                        <template x-if="prod.image_url">
                                            <img :src="prod.image_url" :alt="prod.name"
                                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                                x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                        </template>
                                        <i data-lucide="package" class="w-7 h-7 text-black/30 dark:text-white/30"
                                            :class="prod.image_url ? 'hidden' : ''"></i>
                                        <template x-if="prod.is_preorder">
                                            <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-[5px] bg-amber-500/90 dark:bg-amber-600/90 text-white backdrop-blur text-[9.5px] font-semibold tracking-wide shadow-xs tabular-nums"
                                                x-text="prod.preorder_lead_days > 1 ? 'PO H-' + prod.preorder_lead_days : 'Pre-Order'"></span>
                                        </template>
                                    </div>
                                    <h3 class="font-semibold text-[13px] sm:text-[13.5px] text-black dark:text-white line-clamp-2 tracking-tight leading-snug group-hover:text-brand-primary transition"
                                        x-text="prod.name"></h3>
                                    <p class="text-[11px] text-black/45 dark:text-white/45 line-clamp-1 mt-0.5 font-normal"
                                        x-text="prod.category || ''"></p>
                                </div>
                                <div
                                    class="pt-2.5 mt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                                    <template x-if="Number(prod.price || 0) > 0">
                                        <span
                                            class="text-[12.5px] sm:text-[13px] font-bold text-brand-primary tabular-nums tracking-tight"
                                            x-text="'Rp ' + Number(prod.price || 0).toLocaleString('id-ID')"></span>
                                    </template>
                                    <template x-if="Number(prod.price || 0) <= 0">
                                        <span
                                            class="text-[11.5px] sm:text-[12px] font-medium text-black/60 dark:text-white/60 italic">Hubungi Kami</span>
                                    </template>
                                    <div class="flex items-center gap-1">
                                        <template x-if="isStorefrontEnabled && Number(prod.price || 0) > 0">
                                            <button type="button"
                                                @click.stop="directCheckout(prod, 1); activeModal = null;"
                                                class="h-6 sm:h-7 px-2.5 sm:px-3 rounded-full bg-brand-primary text-white text-[10.5px] sm:text-[11.5px] font-semibold hover:opacity-90 flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                title="Pesan">
                                                <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                                <span>Pesan</span>
                                            </button>
                                        </template>
                                        <template x-if="Number(prod.price || 0) <= 0">
                                            <a :href="waLink(prod.name, false)" target="_blank" rel="noopener" @click.stop
                                                class="h-6 sm:h-7 px-2 sm:px-2.5 rounded-full bg-[#25D366] text-white text-[10.5px] sm:text-[11px] font-semibold hover:opacity-90 flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                title="Hubungi via WhatsApp">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                                                <span>Tanya WA</span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="filteredProducts.length === 0"
                        class="py-16 text-center text-black/40 dark:text-white/40">
                        <i data-lucide="package-open" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                        <p class="text-[13.5px] font-medium text-black/60 dark:text-white/60">Tidak ada produk yang
                            cocok dengan kriteria pencarian ini.</p>
                        <button type="button" @click="clearProductFilter()"
                            class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 text-brand-primary text-[12px] font-semibold transition cursor-pointer">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Reset Pencarian & Filter</span>
                        </button>
                    </div>
                </div>

                {{-- Services Grid --}}
                <div x-show="activeModal === 'all-services'">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-4 lg:gap-5">
                        <template x-for="svc in filteredServices" :key="svc.title">
                            <div role="button" tabindex="0" @click="openService(svc)"
                                class="text-left p-4 rounded-[20px] border border-black/5 dark:border-white/10 hover:border-brand-primary/40 dark:hover:border-brand-primary/40 transition flex flex-col justify-between group bg-black/[0.02] dark:bg-white/[0.03] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] cursor-pointer">
                                <div>
                                    <div
                                        class="relative aspect-[16/10] rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex items-center justify-center mb-3 border border-black/5 dark:border-white/10">
                                        <template x-if="svc.image_url">
                                            <img :src="svc.image_url" :alt="svc.title"
                                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                                x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                        </template>
                                        <i data-lucide="sparkles" class="w-8 h-8 text-black/30 dark:text-white/30"
                                            :class="svc.image_url ? 'hidden' : ''"></i>
                                        <template x-if="svc.badge">
                                            <span
                                                class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-brand-primary text-white text-[9.5px] font-semibold uppercase tracking-wider shadow-sm"
                                                x-text="svc.badge"></span>
                                        </template>
                                    </div>
                                    <h3 class="font-semibold text-[14px] text-black dark:text-white line-clamp-1 tracking-tight leading-snug group-hover:text-brand-primary transition"
                                        x-text="svc.title"></h3>
                                    <p class="text-[12px] text-black/55 dark:text-white/55 line-clamp-2 mt-1 leading-relaxed font-normal"
                                        x-text="svc.description || 'Layanan unggulan berkualitas siap memenuhi kebutuhan Anda.'">
                                    </p>
                                </div>
                                <div
                                    class="pt-3 mt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1.5">
                                    <span class="text-[13px] font-bold text-brand-primary tabular-nums tracking-tight"
                                        x-text="svc.price || 'Hubungi kami'"></span>
                                    <div class="flex items-center gap-1.5">
                                        @if ($allowReservation)
                                            <button type="button"
                                                @click.stop="activeModal = null; openReservationModal(svc.title, svc.id)"
                                                class="h-7 px-3 rounded-full bg-brand-primary hover:opacity-90 text-white text-[11px] font-semibold flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                title="Reservasi">
                                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                                <span>Reservasi</span>
                                            </button>
                                        @elseif ($allowStorefront)
                                            <button type="button"
                                                @click.stop="activeModal = null; directCheckout({ id: svc.id, name: (svc.title || svc.name), price: (svc.raw_price || 0), image_url: svc.image_url }, 1)"
                                                class="h-7 px-3 rounded-full bg-brand-primary text-white text-[11px] font-semibold flex items-center gap-1 transition active:scale-95 shadow-xs cursor-pointer"
                                                title="Pesan">
                                                <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                                <span>Pesan</span>
                                            </button>
                                        @elseif ($hasWhatsapp)
                                            <a :href="waLink(svc.title, true)" target="_blank" rel="noopener"
                                                @click.stop
                                                class="h-7 px-3 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20 text-[11px] font-semibold flex items-center gap-1 transition active:scale-95 cursor-pointer"
                                                title="WhatsApp">
                                                <span>WA</span>
                                            </a>
                                        @endif
                                        <span
                                            class="h-7 px-2 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-[11px] font-semibold text-black/70 dark:text-white/70 flex items-center gap-0.5 tracking-tight">
                                            <span>Detail</span>
                                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="filteredServices.length === 0"
                        class="py-16 text-center text-black/40 dark:text-white/40">
                        <i data-lucide="search-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                        <p class="text-[13.5px] font-medium text-black/60 dark:text-white/60">Tidak ada layanan yang
                            cocok dengan kriteria pencarian ini.</p>
                        <button type="button" @click="clearServiceFilter()"
                            class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 text-brand-primary text-[12px] font-semibold transition cursor-pointer">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Reset Pencarian & Filter</span>
                        </button>
                    </div>
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
    {{-- MOBILE FLOATING ISLAND NAVBAR (iOS 18 Island Pill Architecture)           --}}
    {{-- ========================================================================= --}}
    <nav class="md:hidden fixed bottom-3 inset-x-3 sm:inset-x-6 z-50 rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] px-2.5 py-1.5 shadow-[0_8px_32px_rgba(0,0,0,0.12)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.45)] pb-[calc(0.4rem+env(safe-area-inset-bottom))]"
        aria-label="Navigasi halaman">
        <div class="grid {{ $bottomNavGridClass }} gap-1 items-end max-w-md mx-auto">
            @foreach ($bottomNavButtons as $btn)
                @if ($btn['type'] === 'link')
                    <a href="{{ $btn['href'] }}"
                        class="flex flex-col items-center justify-center py-1 px-0.5 text-[10px] sm:text-[10.5px] font-semibold tracking-tight transition active:scale-95 text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white"
                        :class="activeSection === '{{ $btn['section'] ?? '' }}' ?
                            'text-brand-primary dark:text-brand-primary font-bold' : ''">
                        <i data-lucide="{{ $btn['icon'] }}" class="w-[18px] h-[18px] mb-0.5"></i>
                        <span class="truncate max-w-[62px] text-center leading-tight">{{ $btn['label'] }}</span>
                    </a>
                @elseif ($btn['type'] === 'cart')
                    <button type="button" @click="cartDrawerOpen = true"
                        class="flex flex-col items-center justify-center {{ $btn['is_accent'] ? 'py-1.5 px-1 -mt-3.5 rounded-[18px] bg-brand-primary text-white shadow-[0_6px_16px_rgba(var(--primary-rgb),0.35)] active:scale-95 transition-transform border-2 border-white dark:border-[#161617]' : 'py-1 px-0.5 text-[10px] sm:text-[10.5px] font-semibold tracking-tight text-black/55 dark:text-white/55 hover:text-brand-primary transition active:scale-95' }}">
                        <div class="relative {{ $btn['is_accent'] ? 'mb-0.5' : 'mb-0.5' }}">
                            <i data-lucide="{{ $btn['icon'] }}"
                                class="{{ $btn['is_accent'] ? 'w-5 h-5' : 'w-[18px] h-[18px]' }}"></i>
                            <span x-show="cartCount > 0"
                                class="absolute -top-1.5 -right-2 px-1 py-0.2 rounded-full text-[9px] font-extrabold {{ $btn['is_accent'] ? 'bg-white text-brand-primary' : 'bg-brand-primary text-white' }} tabular-nums leading-none shadow-xs"
                                x-text="cartCount"></span>
                        </div>
                        <span
                            class="text-[10px] {{ $btn['is_accent'] ? 'font-bold' : 'font-semibold' }} truncate max-w-[62px] text-center leading-tight">{{ $btn['label'] }}</span>
                    </button>
                @elseif ($btn['type'] === 'reservation')
                    <button type="button" @click="openReservationModal()"
                        class="flex flex-col items-center justify-center {{ $btn['is_accent'] ? 'py-1.5 px-1 -mt-3.5 rounded-[18px] bg-brand-primary text-white shadow-[0_6px_16px_rgba(var(--primary-rgb),0.35)] active:scale-95 transition-transform border-2 border-white dark:border-[#161617]' : 'py-1 px-0.5 text-[10px] sm:text-[10.5px] font-semibold tracking-tight text-black/55 dark:text-white/55 hover:text-brand-primary transition active:scale-95' }}">
                        <i data-lucide="{{ $btn['icon'] }}"
                            class="{{ $btn['is_accent'] ? 'w-5 h-5 mb-0.5' : 'w-[18px] h-[18px] mb-0.5' }}"></i>
                        <span
                            class="text-[10px] {{ $btn['is_accent'] ? 'font-bold' : 'font-semibold' }} truncate max-w-[62px] text-center leading-tight">{{ $btn['label'] }}</span>
                    </button>
                @elseif ($btn['type'] === 'request_order')
                    <button type="button" @click="requestOrderModalOpen = true"
                        class="flex flex-col items-center justify-center {{ $btn['is_accent'] ? 'py-1.5 px-1 -mt-3.5 rounded-[18px] bg-brand-primary text-white shadow-[0_6px_16px_rgba(var(--primary-rgb),0.35)] active:scale-95 transition-transform border-2 border-white dark:border-[#161617]' : 'py-1 px-0.5 text-[10px] sm:text-[10.5px] font-semibold tracking-tight text-black/55 dark:text-white/55 hover:text-brand-primary transition active:scale-95' }}">
                        <i data-lucide="{{ $btn['icon'] }}"
                            class="{{ $btn['is_accent'] ? 'w-5 h-5 mb-0.5' : 'w-[18px] h-[18px] mb-0.5' }}"></i>
                        <span
                            class="text-[10px] {{ $btn['is_accent'] ? 'font-bold' : 'font-semibold' }} truncate max-w-[62px] text-center leading-tight">{{ $btn['label'] }}</span>
                    </button>
                @elseif ($btn['type'] === 'customer_po')
                    <button type="button" @click="openCustomerPoModal()"
                        class="flex flex-col items-center justify-center {{ $btn['is_accent'] ? 'py-1.5 px-1 -mt-3.5 rounded-[18px] bg-[#5856D6] text-white shadow-[0_6px_16px_rgba(88,86,214,0.35)] active:scale-95 transition-transform border-2 border-white dark:border-[#161617]' : 'py-1 px-0.5 text-[10px] sm:text-[10.5px] font-semibold tracking-tight text-black/55 dark:text-white/55 hover:text-[#5856D6] transition active:scale-95' }}">
                        <i data-lucide="{{ $btn['icon'] }}"
                            class="{{ $btn['is_accent'] ? 'w-5 h-5 mb-0.5' : 'w-[18px] h-[18px] mb-0.5' }}"></i>
                        <span
                            class="text-[10px] {{ $btn['is_accent'] ? 'font-bold' : 'font-semibold' }} truncate max-w-[62px] text-center leading-tight">{{ $btn['label'] }}</span>
                    </button>
                @elseif ($btn['type'] === 'whatsapp')
                    <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                        class="flex flex-col items-center justify-center {{ $btn['is_accent'] ? 'py-1.5 px-1 -mt-3.5 rounded-[18px] bg-[#25D366] text-white shadow-[0_6px_16px_rgba(37,211,102,0.35)] active:scale-95 transition-transform border-2 border-white dark:border-[#161617]' : 'py-1 px-0.5 text-[10px] sm:text-[10.5px] font-semibold tracking-tight text-black/55 dark:text-white/55 hover:text-[#25D366] transition active:scale-95' }}">
                        <i data-lucide="{{ $btn['icon'] }}"
                            class="{{ $btn['is_accent'] ? 'w-5 h-5 mb-0.5' : 'w-[18px] h-[18px] mb-0.5' }}"></i>
                        <span
                            class="text-[10px] {{ $btn['is_accent'] ? 'font-bold' : 'font-semibold' }} truncate max-w-[62px] text-center leading-tight">{{ $btn['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    {{-- ========================================================================= --}}
    {{-- FOOTER (Apple Clean Dark/Light Grounding)                                 --}}
    {{-- ========================================================================= --}}
    @if ($sectionVisibility['footer'])
        <footer
            class="border-t pb-28 sm:pb-32 lg:pb-12 bg-[#F5F5F7] dark:bg-[#161617] border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 transition-colors duration-300">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
                <div class="grid grid-cols-1 sm:{{ $footerGridClass }} gap-10 lg:gap-12">
                    {{-- Brand and social --}}
                    @if ($sectionVisibility['footer_brand'])
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                @php
                                    $footerBrandLogo = $business->logo_url ?: $landingPage->logo_url;
                                @endphp
                                @if ($footerBrandLogo)
                                    <img src="{{ $footerBrandLogo }}" alt="{{ $business->name }}"
                                        class="w-10 h-10 rounded-[10px] object-contain bg-white dark:bg-black/20 p-1 border border-black/5 dark:border-white/10 shadow-xs"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div
                                        class="hidden w-10 h-10 rounded-[10px] bg-brand-primary text-white items-center justify-center font-bold text-sm shadow-xs">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </div>
                                @else
                                    <div
                                        class="w-10 h-10 rounded-[10px] bg-brand-primary text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                        {{ strtoupper(substr($business->name, 0, 2)) }}
                                    </div>
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
        class="hidden md:flex fixed md:bottom-6 left-1/2 -translate-x-1/2 z-40 select-none">
        <button type="button" @click="cartDrawerOpen = true"
            class="h-12 sm:h-13 px-5 sm:px-6 rounded-full bg-black/90 dark:bg-white/95 text-white dark:text-black backdrop-blur-2xl shadow-[0_12px_40px_rgba(0,0,0,0.35)] border border-white/20 dark:border-black/20 flex items-center gap-3.5 hover:scale-105 active:scale-95 transition-all">
            <div class="relative flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span
                    class="absolute -top-2 -right-2.5 px-1.5 py-0.2 rounded-full text-[10.5px] font-extrabold bg-brand-primary text-white tabular-nums shadow-sm"
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
    {{-- FLOATING PILL: GROUP ORDER SHARED BASKET (Apple Island Architecture)      --}}
    {{-- ========================================================================= --}}
    <div x-show="groupOrder.active && groupOrder.data && !groupOrder.isDrawerOpen" x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-10 opacity-0 scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100 scale-100"
        x-transition:leave-end="translate-y-10 opacity-0 scale-95"
        class="fixed bottom-20 md:bottom-6 left-4 sm:left-6 z-40 select-none">
        <button type="button" @click="groupOrder.isDrawerOpen = true"
            class="h-12 sm:h-13 px-4 sm:px-5 rounded-full bg-gradient-to-r from-[#5856D6] to-brand-primary text-white backdrop-blur-2xl shadow-[0_12px_36px_rgba(88,86,214,0.4)] border border-white/20 flex items-center gap-3 hover:scale-105 active:scale-95 transition-all cursor-pointer">
            <div class="relative flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
                <span
                    class="absolute -top-2 -right-2.5 px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-white text-[#5856D6] tabular-nums shadow-xs leading-none"
                    x-text="groupOrder.data.total_quantity || 0"></span>
            </div>
            <div class="h-4 w-px bg-white/25"></div>
            <div class="text-left leading-tight">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10.5px] opacity-85 font-medium">Keranjang Bersama</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                </div>
                <span class="text-[13px] font-extrabold tabular-nums" x-text="formatPrice(groupOrder.data.subtotal || 0)"></span>
            </div>
            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center ml-0.5">
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
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

        <div class="storefront-drawer-frame fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="storefront-sheet storefront-drawer w-screen max-w-md sm:max-w-lg bg-white dark:bg-[#1C1C1E] shadow-2xl border-l border-black/5 dark:border-white/10 flex flex-col justify-between"
                x-show="cartDrawerOpen" x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                {{-- Drawer Header --}}
                <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-brand-100 text-brand-primary flex items-center justify-center">
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

                {{-- Free Shipping Rule Progress Banner --}}
                @if ($freeShippingThreshold > 0)
                    <div
                        class="px-5 py-2.5 bg-emerald-500/10 border-b border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-[12px] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                            <span x-show="cartTotal >= {{ $freeShippingThreshold }}"
                                class="font-bold text-[#34C759]">Bebas Ongkir Aktif!</span>
                            <span x-show="cartTotal < {{ $freeShippingThreshold }}">Tambah <strong
                                    class="tabular-nums"
                                    x-text="formatPrice({{ $freeShippingThreshold }} - cartTotal)"></strong> utk
                                Bebas Ongkir</span>
                        </div>
                        <span
                            class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full bg-white dark:bg-black/40 shadow-xs tabular-nums">Min.
                            Rp {{ number_format($freeShippingThreshold, 0, ',', '.') }}</span>
                    </div>
                @endif

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
                                <span class="text-[12.5px] font-semibold text-brand-primary tabular-nums block"
                                    x-text="formatPrice(item.price)"></span>

                                <div class="pt-1">
                                    <input type="text" x-model="item.notes" @input="saveCart()"
                                        placeholder="Nama pemesan / catatan (cth: Budi - Lt. 4)"
                                        class="w-full h-7 px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[11px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-brand-primary transition">
                                </div>

                                <div class="flex items-center justify-between pt-1.5">
                                    <div
                                        class="flex items-center gap-2 bg-black/5 dark:bg-white/5 px-2.5 py-1 rounded-full border border-black/5 dark:border-white/5">
                                        <button type="button" @click="updateQuantity(item.id, -1)"
                                            class="w-7 h-7 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-bold text-[14px] active:scale-90 transition cursor-pointer">&minus;</button>
                                        <span
                                            class="text-[13px] font-bold text-black dark:text-white min-w-5 text-center tabular-nums"
                                            x-text="item.quantity"></span>
                                        <button type="button" @click="updateQuantity(item.id, 1)"
                                            class="w-7 h-7 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-bold text-[14px] active:scale-90 transition cursor-pointer">&plus;</button>
                                    </div>
                                    <button type="button" @click="removeFromCart(item.id)"
                                        class="text-black/40 hover:text-[#FF3B30] text-[12px] font-medium transition cursor-pointer">Hapus</button>
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

                    <template x-if="minOrderAmount > 0 && cartTotal < minOrderAmount && cart.length > 0">
                        <div
                            class="p-2.5 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-[#FF9500] text-[11.5px] font-semibold text-center flex items-center justify-center gap-1.5">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Minimum belanja adalah <strong x-text="formatPrice(minOrderAmount)"></strong></span>
                        </div>
                    </template>

                    <button type="button" @click="cartDrawerOpen = false; checkoutModalOpen = true;"
                        :disabled="cart.length === 0 || (minOrderAmount > 0 && cartTotal < minOrderAmount)"
                        class="w-full h-12 rounded-[16px] bg-brand-primary hover:opacity-90 disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer disabled:cursor-not-allowed">
                        <span>Pesan</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    <div x-show="checkoutModalOpen" x-cloak
        class="storefront-sheet-overlay fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="storefront-sheet w-full max-w-full sm:max-w-[94vw] md:max-w-4xl lg:max-w-5xl xl:max-w-6xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] p-5 sm:p-7 shadow-2xl border-t sm:border border-black/10 dark:border-white/10 max-h-[94vh] sm:max-h-[90vh] overflow-y-auto space-y-5"
            @click.outside="if(!isCheckingOut) checkoutModalOpen = false">

            {{-- Mobile Touch Grab Bar --}}
            <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2 shrink-0"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-full bg-brand-100 text-brand-primary flex items-center justify-center">
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
                    class="p-3.5 rounded-[16px] bg-gradient-to-br from-brand-primary/10 to-[#5856D6]/10 border border-brand-primary/20 space-y-2.5 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <p class="text-[13px] font-bold text-black dark:text-white">Masuk Akun Pelanggan</p>
                            <p class="text-[11.5px] text-black/60 dark:text-white/60">Wajib login untuk join Pre-Order &amp; unggah bukti pembayaran.</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            <a :href="customerLoginUrl"
                                class="px-3.5 py-2 bg-brand-primary text-white rounded-xl text-[12px] font-bold hover:opacity-90 transition active:scale-95 flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                <span>Masuk Akun / Demo</span>
                            </a>
                            <a href="{{ route('customer.auth.google') }}?redirect={{ urlencode(url()->current()) }}"
                                class="px-3 py-2 bg-white dark:bg-black/40 border border-black/10 dark:border-white/10 text-black dark:text-white rounded-xl text-[12px] font-bold hover:border-brand-primary transition active:scale-95 flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                <span>Google</span>
                            </a>
                        </div>
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
                        <span class="text-[#34C759] font-semibold text-[11.5px] inline-flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Terverifikasi</span>
                    @endif
                </div>
            @endguest

            {{-- Stepped Checkout Navigation Indicator (Bento Apple HIG Segmented Bar) --}}
            <div class="flex items-center justify-between gap-1 p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] border border-black/5 dark:border-white/10"
                role="tablist" aria-label="Tahapan Checkout">
                <button type="button" role="tab" @click="goToCheckoutStep(1)"
                    class="flex-1 flex items-center justify-center gap-2 py-2 px-2.5 sm:px-3 rounded-[12px] text-[12px] sm:text-[13px] font-semibold transition-all cursor-pointer"
                    :class="checkoutStep === 1
                        ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold'
                        : (checkoutStep > 1 ? 'text-brand-primary hover:bg-brand-primary/5' : 'text-black/50 dark:text-white/50')">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10.5px] font-bold"
                        :class="checkoutStep === 1
                            ? 'bg-brand-primary text-white'
                            : (checkoutStep > 1 ? 'bg-brand-100 text-brand-primary' : 'bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60')">
                        <template x-if="checkoutStep > 1">
                            <i data-lucide="check" class="w-3 h-3"></i>
                        </template>
                        <template x-if="checkoutStep <= 1">
                            <span>1</span>
                        </template>
                    </span>
                    <span class="truncate">1. Pengiriman</span>
                </button>

                @if ($hasBatchFeature)
                    <button type="button" role="tab" @click="goToCheckoutStep(2)"
                        class="flex-1 flex items-center justify-center gap-2 py-2 px-2.5 sm:px-3 rounded-[12px] text-[12px] sm:text-[13px] font-semibold transition-all cursor-pointer"
                        :class="checkoutStep === 2
                            ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold'
                            : (checkoutStep > 2 ? 'text-brand-primary hover:bg-brand-primary/5' : 'text-black/50 dark:text-white/50')">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10.5px] font-bold"
                            :class="checkoutStep === 2
                                ? 'bg-brand-primary text-white'
                                : (checkoutStep > 2 ? 'bg-brand-100 text-brand-primary' : 'bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60')">
                            <template x-if="checkoutStep > 2">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </template>
                            <template x-if="checkoutStep <= 2">
                                <span>2</span>
                            </template>
                        </span>
                        <span class="truncate">2. Jadwal PO</span>
                    </button>
                @endif

                <button type="button" role="tab" @click="goToCheckoutStep(3)"
                    class="flex-1 flex items-center justify-center gap-2 py-2 px-2.5 sm:px-3 rounded-[12px] text-[12px] sm:text-[13px] font-semibold transition-all cursor-pointer"
                    :class="checkoutStep === 3
                        ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold'
                        : 'text-black/50 dark:text-white/50'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10.5px] font-bold"
                        :class="checkoutStep === 3
                            ? 'bg-brand-primary text-white'
                            : 'bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60'">
                        <span>{{ $hasBatchFeature ? '3' : '2' }}</span>
                    </span>
                    <span class="truncate">{{ $hasBatchFeature ? '3. Pembayaran' : '2. Pembayaran' }}</span>
                </button>
            </div>

            <form @submit.prevent="submitCheckout()" class="space-y-6">
                {{-- STEP 1: Data Pemesan & Pengiriman --}}
                <div x-show="checkoutStep === 1" class="space-y-6 max-w-4xl mx-auto"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    {{-- Data Pemesan --}}
                    <div class="space-y-3">
                        <span
                            class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">1.
                            Data Pemesan</span>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama
                                Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" x-model="checkoutForm.customer_name"
                                placeholder="Contoh: Budi Santoso"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                    WhatsApp <span class="text-red-500">*</span></label>
                                <input type="tel" x-model="checkoutForm.customer_phone"
                                    placeholder="08123456789"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Email
                                    (Opsional)</label>
                                <input type="email" x-model="checkoutForm.customer_email"
                                    placeholder="budi@example.com"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Nama Kantor / Perusahaan / Drop Point <span class="text-[11px] font-normal text-black/40 dark:text-white/40">(Opsional pesanan bersama)</span>
                            </label>
                            <input type="text" x-model="checkoutForm.group_name"
                                placeholder="Contoh: PT Telkom Lantai 8 / Gedung Menara Mandiri"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                        </div>
                    </div>

                    {{-- Opsi Pengiriman --}}
                    <div class="space-y-3 pt-2">
                        <span
                            class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">2.
                            Pengiriman</span>
                        @if (($storeSetting?->allow_pickup ?? true) && ($storeSetting?->allow_delivery ?? true))
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="setFulfillment('pickup')"
                                    :class="checkoutForm.fulfillment_type === 'pickup' ?
                                        'border-brand-primary bg-brand-100 text-brand-primary font-bold' :
                                        'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                    class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2 cursor-pointer">
                                    <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                                    <span>Ambil Sendiri</span>
                                </button>
                                <button type="button" @click="setFulfillment('merchant_delivery')"
                                    :class="checkoutForm.fulfillment_type === 'merchant_delivery' ?
                                        'border-brand-primary bg-brand-100 text-brand-primary font-bold' :
                                        'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                    class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2 cursor-pointer">
                                    <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                                    <span>Kurir Toko</span>
                                </button>
                            </div>
                        @elseif ($storeSetting?->allow_delivery ?? true)
                            <div
                                class="p-3 rounded-[14px] border border-brand-primary/30 bg-brand-50 text-brand-primary font-semibold text-[12.5px] flex items-center gap-2">
                                <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                                <span>Pengiriman Langsung via Kurir Toko</span>
                            </div>
                        @elseif ($storeSetting?->allow_pickup ?? true)
                            <div
                                class="p-3 rounded-[14px] border border-brand-primary/30 bg-brand-50 text-brand-primary font-semibold text-[12.5px] flex items-center gap-2">
                                <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                                <span>Pengambilan Mandiri di Outlet / Toko</span>
                            </div>
                        @else
                            <div
                                class="p-3 rounded-[14px] border border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-400 font-semibold text-[12.5px] flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                                <span>Metode pengiriman disesuaikan saat konfirmasi pesanan</span>
                            </div>
                        @endif

                        <div x-show="checkoutForm.fulfillment_type === 'merchant_delivery'" class="space-y-3 pt-1">
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Alamat
                                    Pengiriman Lengkap <span class="text-red-500">*</span></label>
                                <textarea x-model="checkoutForm.shipping_address" rows="2"
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan..."
                                    class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary resize-none"></textarea>
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
                                            :class="checkoutForm.shipping_rule_id === opt.id ? 'border-brand-primary bg-brand-50' :
                                                ''">
                                            <div class="flex items-center gap-2.5">
                                                <input type="radio" name="shipping_rule_choice"
                                                    :value="opt.id" x-model="checkoutForm.shipping_rule_id"
                                                    @change="fetchShippingQuote(opt.id)"
                                                    class="text-brand-primary focus:ring-brand-primary">
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
                                class="text-[11.5px] text-brand-primary flex items-center gap-1.5 py-1">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                <span>Menghitung ongkos kirim...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Step 1 Navigation Buttons --}}
                    <div class="pt-4 border-t border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                        <button type="button" @click="checkoutModalOpen = false"
                            class="h-11 px-5 rounded-[14px] bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 font-semibold text-[13px] transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" @click="goToCheckoutStep({{ $hasBatchFeature ? 2 : 3 }})"
                            class="h-11 px-6 rounded-[14px] bg-brand-primary hover:opacity-90 text-white font-bold text-[13px] transition flex items-center gap-2 shadow-xs cursor-pointer">
                            <span>{{ $hasBatchFeature ? 'Lanjut ke Jadwal PO' : 'Lanjut ke Pembayaran' }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                @if ($hasBatchFeature)
                    {{-- STEP 2: Jadwal Pre-Order & Batch --}}
                    <div x-show="checkoutStep === 2" class="space-y-6 max-w-4xl mx-auto"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        {{-- Opsi Jadwal Pemesanan / Batch Pre-Order --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span
                                        class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                                        {{ ($isUmkmRumahan || $industryGroup === 'fnb') ? 'Jadwal Pre-Order / Batch Pengiriman' : 'Jadwal Pesanan' }}
                                    </span>
                                    <template x-if="hasPreorderItems">
                                        <span class="text-[11px] text-amber-600 dark:text-amber-400 font-medium block">Wajib dijadwalkan (memuat produk Pre-Order)</span>
                                    </template>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="checkoutForm.is_scheduled" :disabled="hasPreorderItems" class="sr-only peer">
                                    <div
                                        class="w-9 h-5 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-primary"
                                        :class="hasPreorderItems ? 'opacity-70 cursor-not-allowed' : ''">
                                    </div>
                                    <span class="ml-2 text-[12px] font-medium text-black/70 dark:text-white/70">
                                        {{ ($isUmkmRumahan || $industryGroup === 'fnb') ? 'Pilih Batch' : 'Pesan Terjadwal' }}
                                    </span>
                                </label>
                            </div>
                            <div x-show="checkoutForm.is_scheduled" x-transition
                                class="space-y-3.5 p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10">
                                
                                {{-- Interactive Batch Date Chips (for Pre-Order B2C) --}}
                                @if (!empty($availableBatchDates))
                                    <div>
                                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">
                                            Pilih Batch Pengiriman <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                                            @foreach ($availableBatchDates as $batch)
                                                @php
                                                    $bSoldOut = (bool) ($batch['is_sold_out'] ?? $batch['is_full'] ?? false);
                                                    $bQuota = $batch['remaining_quota'] ?? null;
                                                    $bDayName = $batch['day_name_upper'] ?? strtoupper($batch['day_name'] ?? '');
                                                    $bDayShort = $batch['day_short'] ?? $batch['short_date'] ?? ($batch['date'] ?? '');
                                                    $bDate = $batch['date'] ?? '';
                                                    $bUnit = $batch['quota_unit'] ?? ($storeSetting?->preorder_quota_unit ?? 'PCS');
                                                @endphp
                                                <button type="button"
                                                    @if (!$bSoldOut)
                                                        @click="checkoutForm.scheduled_date = '{{ $bDate }}'"
                                                    @endif
                                                    class="p-2.5 rounded-[12px] border text-left transition relative flex flex-col justify-between cursor-pointer active:scale-[0.98] {{ $bSoldOut ? 'opacity-50 cursor-not-allowed bg-black/5 dark:bg-white/5 border-dashed border-black/10 dark:border-white/10' : '' }}"
                                                    :class="checkoutForm.scheduled_date === '{{ $bDate }}' ? 'border-brand-primary bg-brand-primary/10 text-brand-primary ring-1 ring-brand-primary/30' : 'border-black/10 dark:border-white/10 bg-white dark:bg-black/20 text-black/80 dark:text-white/80 hover:border-black/20 dark:hover:border-white/20'">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[11px] font-bold uppercase tracking-wider opacity-80">{{ $bDayName }}</span>
                                                        @if ($bSoldOut)
                                                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">Penuh</span>
                                                        @elseif ($bQuota !== null)
                                                            <span class="text-[10px] font-semibold opacity-70 tabular-nums">Sisa {{ $bQuota }} {{ $bUnit }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1 font-bold text-[13px] tabular-nums tracking-tight">
                                                        {{ $bDayShort }}
                                                    </div>
                                                    @if (!empty($batch['note']))
                                                        <div class="text-[10px] text-black/50 dark:text-white/50 truncate mt-0.5">{{ $batch['note'] }}</div>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $allowCustomDate = (bool) ($storeSetting?->allow_custom_date ?? true);
                                @endphp

                                @if ($allowCustomDate || empty($availableBatchDates))
                                    {{-- Custom Date Picker Fallback / Alternative --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                                {{ !empty($availableBatchDates) ? 'Atau Pilih Tanggal Sendiri' : 'Pilih Tanggal' }} <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                        <input type="date" :min="minPreorderDate"
                                            x-model="checkoutForm.scheduled_date"
                                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                                        @if ($storeSetting?->cut_off_time)
                                            <p class="text-[11px] text-black/50 dark:text-white/50 mt-1 flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3 h-3 text-amber-500"></i>
                                                <span>Batas pesanan masuk hari ini: {{ substr((string) $storeSetting->cut_off_time, 0, 5) }} WIB.</span>
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    {{-- Batch Only Guide Notice --}}
                                    <div class="flex items-center gap-2 p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] text-[11.5px] text-black/60 dark:text-white/60">
                                        <i data-lucide="info" class="w-4 h-4 text-brand-primary shrink-0"></i>
                                        <span>Pengiriman hanya dibuka pada tanggal batch di atas. Silakan pilih salah satu batch pengiriman yang tersedia.</span>
                                    </div>
                                @endif

                                {{-- Time Slot Selector --}}
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                        Slot Waktu Pengantaran / Pengambilan (Opsional)
                                    </label>
                                    @php
                                        $storeAvailableSlots = (array) ($storeSetting?->available_slots ?? []);
                                        $fallbackSlots = [
                                            'Pagi (08:00 - 11:30)',
                                            'Siang (12:00 - 15:30)',
                                            'Sore / Malam (16:00 - 20:00)',
                                        ];
                                        $checkoutSlots = !empty($storeAvailableSlots)
                                            ? $storeAvailableSlots
                                            : $fallbackSlots;
                                    @endphp
                                    <select x-model="checkoutForm.scheduled_time_slot"
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary cursor-pointer">
                                        <option value="">-- Bebas Waktu / Jam Buka Outlet --</option>
                                        @foreach ($checkoutSlots as $slot)
                                            <option value="{{ $slot }}">{{ $slot }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2 Navigation Buttons --}}
                        <div class="pt-4 border-t border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                            <button type="button" @click="goToCheckoutStep(1)"
                                class="h-11 px-5 rounded-[14px] bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 font-semibold text-[13px] transition flex items-center gap-2 cursor-pointer">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                <span>Kembali</span>
                            </button>
                            <button type="button" @click="goToCheckoutStep(3)"
                                class="h-11 px-6 rounded-[14px] bg-brand-primary hover:opacity-90 text-white font-bold text-[13px] transition flex items-center gap-2 shadow-xs cursor-pointer">
                                <span>Lanjut ke Pembayaran</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- STEP 3: Pembayaran & Rincian Pesanan --}}
                <div x-show="checkoutStep === 3" class="space-y-5"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        {{-- Left Column: Rekening & Catatan --}}
                        <div class="lg:col-span-7 space-y-5">
                            {{-- Metode Pembayaran (TriPay Otomatis + Rekening Manual) --}}
                            <div class="space-y-3.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap pb-0.5">
                                    <div>
                                        <span class="text-[12px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">
                                            Metode Pembayaran
                                        </span>
                                        <p class="text-[11.5px] text-black/45 dark:text-white/45">Pilih pembayaran instan QRIS / VA atau transfer manual.</p>
                                    </div>
                                    @if ($storeSetting?->order_auto_cancel_minutes)
                                        <span
                                            class="text-[11px] text-amber-600 dark:text-amber-400 font-medium inline-flex items-center gap-1 shrink-0 whitespace-nowrap bg-amber-500/10 px-2.5 py-0.5 rounded-full">
                                            <i data-lucide="clock" class="w-3 h-3 shrink-0"></i>
                                            <span>Batas: {{ $storeSetting->order_auto_cancel_minutes }} mnt</span>
                                        </span>
                                    @endif
                                </div>

                                {{-- Segmented Switcher (Otomatis vs Rekening Toko) --}}
                                @if ($paymentMethods->isNotEmpty())
                                    <div class="flex p-1 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 gap-1">
                                        <button type="button" @click="checkoutForm.payment_gateway = 'tripay'; if(!checkoutForm.payment_channel) checkoutForm.payment_channel = 'QRIS';"
                                            class="flex-1 h-9 rounded-[10px] text-[12.5px] font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                                            :class="checkoutForm.payment_gateway === 'tripay' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                                            <i data-lucide="qr-code" class="w-3.5 h-3.5 text-brand-primary"></i>
                                            <span>QRIS &amp; Virtual Account</span>
                                            <span class="px-1.5 py-0.2 rounded-full text-[9.5px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Instan</span>
                                        </button>
                                        <button type="button" @click="checkoutForm.payment_gateway = 'manual'; if(!checkoutForm.payment_method_id) checkoutForm.payment_method_id = '{{ $paymentMethods->first()?->id ?? '' }}';"
                                            class="flex-1 h-9 rounded-[10px] text-[12.5px] font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer"
                                            :class="checkoutForm.payment_gateway === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                                            <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                                            <span>Transfer Toko</span>
                                        </button>
                                    </div>
                                @endif

                                {{-- Pilihan TriPay (Otomatis) --}}
                                <div x-show="checkoutForm.payment_gateway === 'tripay'" class="space-y-2.5">
                                    {{-- QRIS Dinamis Card --}}
                                    <div class="rounded-[18px] border p-4 transition cursor-pointer relative"
                                        @click="checkoutForm.payment_channel = 'QRIS'"
                                        :class="checkoutForm.payment_channel === 'QRIS' ? 'border-brand-primary bg-brand-primary/[0.04] ring-1 ring-brand-primary/30 shadow-xs' : 'border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20 bg-black/[0.01] dark:bg-white/[0.02]'">
                                        <div class="flex items-start gap-3">
                                            <div class="w-5 h-5 rounded-full border flex items-center justify-center shrink-0 mt-0.5"
                                                :class="checkoutForm.payment_channel === 'QRIS' ? 'border-brand-primary bg-brand-primary text-white' : 'border-black/30 dark:border-white/30'">
                                                <div class="w-2 h-2 rounded-full bg-white" x-show="checkoutForm.payment_channel === 'QRIS'"></div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[14.5px] font-bold text-black dark:text-white">QRIS Dinamis</span>
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-primary/10 text-brand-primary">Paling Populer</span>
                                                    </div>
                                                    <span class="text-[11.5px] font-bold text-emerald-600 dark:text-emerald-400">Bebas Biaya Admin</span>
                                                </div>
                                                <p class="text-[12px] text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                                    Dapat dipindai dengan semua e-Wallet &amp; m-Banking (GoPay, OVO, Dana, ShopeePay, BCA, Livin Mandiri, BRImo, dll). Verifikasi otomatis detik itu juga tanpa perlu upload struk.
                                                </p>
                                                <div class="mt-2.5 pt-2 border-t border-black/5 dark:border-white/5 flex items-center gap-1.5 text-[11px] text-black/45 dark:text-white/45">
                                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                                                    <span>Biaya transaksi QRIS (Rp 750 + 0,7%) disubsidi oleh toko.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Virtual Account Selector Card --}}
                                    <div class="rounded-[18px] border border-black/10 dark:border-white/10 p-4 space-y-3 bg-black/[0.01] dark:bg-white/[0.02]">
                                        <span class="text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 block">
                                            Pilihan Virtual Account (Multi-Bank)
                                        </span>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @php
                                                $vaChannels = [
                                                    ['code' => 'BCAVA', 'name' => 'BCA VA'],
                                                    ['code' => 'BRIVA', 'name' => 'BRI VA'],
                                                    ['code' => 'MANDIRIVA', 'name' => 'Mandiri VA'],
                                                    ['code' => 'BNIVA', 'name' => 'BNI VA'],
                                                    ['code' => 'BSIVA', 'name' => 'BSI VA'],
                                                    ['code' => 'PERMATAVA', 'name' => 'Permata VA'],
                                                ];
                                            @endphp
                                            @foreach ($vaChannels as $va)
                                                <button type="button" @click="checkoutForm.payment_channel = '{{ $va['code'] }}'"
                                                    class="p-2.5 rounded-[12px] border text-left transition relative flex flex-col justify-between cursor-pointer active:scale-[0.98]"
                                                    :class="checkoutForm.payment_channel === '{{ $va['code'] }}' ? 'border-brand-primary bg-brand-primary/10 text-brand-primary ring-1 ring-brand-primary/30 font-bold' : 'border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black/80 dark:text-white/80 hover:border-black/20 dark:hover:border-white/20 font-semibold'">
                                                    <span class="text-[12.5px] leading-tight">{{ $va['name'] }}</span>
                                                    <span class="text-[10px] text-black/45 dark:text-white/45 mt-1">Otomatis Lunas</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Pilihan Rekening Manual Toko --}}
                                @if ($paymentMethods->isNotEmpty())
                                    <div x-show="checkoutForm.payment_gateway === 'manual'" class="space-y-2.5">
                                        @foreach ($paymentMethods as $pm)
                                            <div class="rounded-[16px] border border-black/10 dark:border-white/10 p-3.5 transition cursor-pointer"
                                                @click="checkoutForm.payment_method_id = '{{ $pm->id }}'"
                                                :class="checkoutForm.payment_method_id === '{{ $pm->id }}' ?
                                                    'border-brand-primary bg-brand-50/70 dark:bg-brand-primary/10 shadow-xs' : 'hover:bg-black/5 dark:hover:bg-white/5'">
                                                <label class="flex items-start gap-3 cursor-pointer w-full">
                                                    <input type="radio" name="payment_method_id"
                                                        value="{{ $pm->id }}"
                                                        x-model="checkoutForm.payment_method_id"
                                                        class="mt-1 text-brand-primary focus:ring-brand-primary shrink-0">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                                                            <span
                                                                class="text-[14px] font-bold text-black dark:text-white leading-tight">{{ $pm->bank_name }}</span>
                                                            <span
                                                                class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full shrink-0 whitespace-nowrap {{ $pm->type === 'qris' ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                                                                {{ $pm->type === 'qris' ? 'QRIS Toko' : 'Transfer Manual' }}
                                                            </span>
                                                        </div>
                                                        @if ($pm->account_number)
                                                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-black/5 dark:border-white/5 flex-wrap sm:flex-nowrap">
                                                                <div class="min-w-0 flex-1">
                                                                    <span
                                                                        class="text-[12.5px] font-mono font-semibold text-black/80 dark:text-white/80 tabular-nums block break-all">{{ $pm->account_number }}</span>
                                                                    @if ($pm->account_holder)
                                                                        <span
                                                                            class="text-[11px] text-black/55 dark:text-white/55 block truncate mt-0.5">a/n {{ $pm->account_holder }}</span>
                                                                    @endif
                                                                </div>
                                                                <button type="button"
                                                                    @click.stop="navigator.clipboard.writeText('{{ $pm->account_number }}'); showToast('Nomor rekening disalin!')"
                                                                    class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary text-[11px] font-semibold transition active:scale-95 whitespace-nowrap cursor-pointer">
                                                                    <i data-lucide="copy" class="w-3 h-3 shrink-0"></i>
                                                                    <span>Salin</span>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>


                            {{-- Catatan --}}
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Catatan
                                    Pesanan (Opsional)</label>
                                <input type="text" x-model="checkoutForm.notes"
                                    placeholder="{{ $storeSetting?->order_notes_placeholder ?: 'Contoh: Jangan terlalu pedas, titip di satpam, dll.' }}"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                        </div>

                        {{-- Right Column: Rincian Item Belanja & Total --}}
                        <div class="lg:col-span-5 space-y-5">
                            {{-- Rincian Item Belanja & Nama Pemesan --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                                    <span x-show="!isGroupOrderCheckout">Rincian Menu (<span x-text="cartCount"></span>)</span>
                                    <span x-show="isGroupOrderCheckout" class="text-brand-primary">Pesanan Bersama (<span x-text="groupOrder.data?.total_quantity || 0"></span> item)</span>
                                    <button x-show="!isGroupOrderCheckout" type="button" @click="checkoutModalOpen = false; cartDrawerOpen = true"
                                        class="text-brand-primary lowercase font-normal hover:underline cursor-pointer text-[11.5px]">Ubah di keranjang</button>
                                    <button x-show="isGroupOrderCheckout" type="button" @click="checkoutModalOpen = false; groupOrder.isDrawerOpen = true"
                                        class="text-brand-primary lowercase font-normal hover:underline cursor-pointer text-[11.5px]">Ubah di keranjang bersama</button>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-1.5 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 divide-y divide-black/5 dark:divide-white/5">
                                    <template x-if="!isGroupOrderCheckout">
                                        <div>
                                            <template x-for="item in cart" :key="item.id">
                                                <div class="pt-1.5 first:pt-0 flex items-start justify-between text-[12px] gap-2">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-semibold text-black dark:text-white truncate">
                                                            <span x-text="item.quantity"></span>x <span x-text="item.name"></span>
                                                        </div>
                                                        <template x-if="item.notes">
                                                            <div class="text-[11px] text-brand-primary font-medium truncate flex items-center gap-1 mt-0.5">
                                                                <i data-lucide="user" class="w-3 h-3 shrink-0"></i>
                                                                <span x-text="item.notes"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <span class="font-semibold tabular-nums text-black/70 dark:text-white/70 shrink-0"
                                                        x-text="formatPrice(item.price * item.quantity)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="isGroupOrderCheckout">
                                        <div class="space-y-3">
                                            <template x-for="member in groupOrder.splitBill" :key="member.member_id">
                                                <div class="space-y-1">
                                                    <div class="text-[11.5px] font-bold text-brand-primary flex items-center justify-between pb-0.5 border-b border-black/5 dark:border-white/5">
                                                        <span x-text="'👤 ' + member.member_name"></span>
                                                        <span x-text="formatPrice(member.member_subtotal)" class="tabular-nums"></span>
                                                    </div>
                                                    <template x-for="it in member.items" :key="it.id">
                                                        <div class="pl-2 flex items-start justify-between text-[11.5px] text-black/70 dark:text-white/70 gap-2">
                                                            <div class="truncate">
                                                                <span x-text="it.quantity + 'x ' + it.product_name"></span>
                                                                <span x-show="it.notes" class="text-[10.5px] text-black/50 dark:text-white/50 block truncate" x-text="'Catatan: ' + it.notes"></span>
                                                            </div>
                                                            <span class="tabular-nums shrink-0 font-medium" x-text="formatPrice(it.line_total)"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Total & Submit Card --}}
                            <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3">
                                <div class="flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
                                    <span x-text="isGroupOrderCheckout ? 'Total Pesanan Bersama' : 'Subtotal Belanja'"></span>
                                    <span class="tabular-nums font-semibold" x-text="formatPrice(isGroupOrderCheckout ? (groupOrder.data?.subtotal || 0) : cartTotal)"></span>
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

                                <div class="flex items-center justify-between text-[15px] pt-1 border-t border-black/5 dark:border-white/10">
                                    <span class="text-black/70 dark:text-white/70 font-semibold">Total Pembayaran</span>
                                    <span class="text-[20px] font-extrabold text-brand-primary tabular-nums"
                                        x-text="formatPrice(grandTotal)"></span>
                                </div>

                                @guest('customer')
                                    <a :href="customerLoginUrl"
                                        class="w-full h-12 rounded-[16px] bg-[#007AFF] hover:bg-[#007AFF]/90 active:scale-[0.98] text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                        <span>Masuk dengan Google untuk Melanjutkan</span>
                                    </a>
                                    <p class="text-[11.5px] text-center text-black/50 dark:text-white/50">
                                        Wajib login dengan akun Google agar pesanan batch kantor Anda tercatat dan siap unggah bukti transfer.
                                    </p>
                                @else
                                    <button type="submit" :disabled="isCheckingOut"
                                        class="w-full h-12 rounded-[16px] bg-brand-primary hover:opacity-90 active:scale-[0.98] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                        <span x-show="!isCheckingOut" x-text="isGroupOrderCheckout ? 'Konfirmasi Checkout Pesanan Bersama' : 'Konfirmasi Pesanan Pre-Order'"></span>
                                        <span x-show="isCheckingOut">Memproses...</span>
                                        <i x-show="!isCheckingOut" data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </button>
                                @endguest
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 Back Button --}}
                    <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center">
                        <button type="button" @click="goToCheckoutStep({{ $hasBatchFeature ? 2 : 1 }})"
                            class="h-10 px-4 rounded-[12px] bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 font-semibold text-[12.5px] transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                            <span>Kembali ke {{ $hasBatchFeature ? 'Jadwal PO' : 'Data Pengiriman' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REQUEST ORDER MODAL (Apple Bento Sheet for RFQ / Custom Orders)            --}}
    {{-- ========================================================================= --}}
    <div x-show="requestOrderModalOpen" x-cloak x-transition.opacity
        @keydown.escape.window="requestOrderModalOpen = false"
        class="storefront-sheet-overlay fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
        style="display: none;">
        <div @click.away="requestOrderModalOpen = false" x-show="requestOrderModalOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="storefront-sheet w-full max-w-full sm:max-w-[94vw] md:max-w-3xl lg:max-w-4xl xl:max-w-5xl max-h-[94vh] sm:max-h-[90vh] overflow-y-auto bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] p-5 sm:p-7 shadow-2xl border-t sm:border border-black/10 dark:border-white/10 space-y-5">

            {{-- Mobile Touch Grab Bar --}}
            <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2 shrink-0"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="space-y-0.5">
                    <span
                        class="text-[11px] font-bold uppercase tracking-wider text-brand-primary block">{{ $isUmkmRumahan ? 'Pemesanan Kustom / PO' : 'Request Order Khusus' }}</span>
                    <h3 class="text-[18px] font-bold text-black dark:text-white">
                        {{ $isUmkmRumahan ? 'Pre-Order / Pesanan Khusus' : ($industryGroup === 'manufacturing' ? 'Permintaan PO & Fabrikasi' : 'Pesan Kustom / Pre-Order') }}
                    </h3>
                </div>
                <button type="button" @click="requestOrderModalOpen = false"
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div
                class="p-3.5 rounded-[14px] bg-brand-50 border border-brand-primary/20 text-[12px] text-black/70 dark:text-white/70 flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-brand-primary shrink-0 mt-0.5"></i>
                <span>{{ $isUmkmRumahan
                    ? 'Pesanan pre-order dikerjakan fresh sesuai permintaan Anda. Kami akan mengonfirmasi ketersediaan slot jadwal dan rincian harga via WhatsApp.'
                    : 'Pesanan khusus memerlukan peninjauan oleh tim kami. Anda akan menerima notifikasi penawaran harga & konfirmasi ketersediaan melalui WhatsApp.' }}</span>
            </div>

            <template x-if="requestOrderForm.error">
                <div
                    class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="requestOrderForm.error"></span>
                </div>
            </template>

            <form @submit.prevent="submitRequestOrder()" class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 items-start">
                {{-- Left Column: Data Pemesan & Opsi Pengiriman --}}
                <div class="space-y-4">
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
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                    WhatsApp <span class="text-red-500">*</span></label>
                                <input type="tel" x-model="requestOrderForm.customer_phone" required
                                    placeholder="08123456789"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Email
                                    (Opsional)</label>
                                <input type="email" x-model="requestOrderForm.customer_email"
                                    placeholder="budi@example.com"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                        </div>
                    </div>

                    {{-- Pengiriman --}}
                    <div class="space-y-3 pt-2">
                        <span
                            class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">3.
                            Opsi Pengambilan / Kirim</span>
                        @if (($storeSetting?->allow_pickup ?? true) && ($storeSetting?->allow_delivery ?? true))
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="requestOrderForm.fulfillment_type = 'pickup'"
                                    :class="requestOrderForm.fulfillment_type === 'pickup' ?
                                        'border-brand-primary bg-brand-100 text-brand-primary font-bold' :
                                        'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                    class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2 cursor-pointer">
                                    <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                                    <span>Ambil Sendiri</span>
                                </button>
                                <button type="button" @click="requestOrderForm.fulfillment_type = 'merchant_delivery'"
                                    :class="requestOrderForm.fulfillment_type === 'merchant_delivery' ?
                                        'border-brand-primary bg-brand-100 text-brand-primary font-bold' :
                                        'border-black/10 dark:border-white/10 bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                    class="p-3 rounded-[14px] border text-left text-[12.5px] transition flex items-center gap-2 cursor-pointer">
                                    <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                                    <span>Kirim ke Alamat</span>
                                </button>
                            </div>
                        @elseif ($storeSetting?->allow_delivery ?? true)
                            <div
                                class="p-3 rounded-[14px] border border-brand-primary/30 bg-brand-50 text-brand-primary font-semibold text-[12.5px] flex items-center gap-2">
                                <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                                <span>Pengiriman Langsung ke Alamat Tujuan</span>
                            </div>
                        @else
                            <div
                                class="p-3 rounded-[14px] border border-brand-primary/30 bg-brand-50 text-brand-primary font-semibold text-[12.5px] flex items-center gap-2">
                                <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                                <span>Pengambilan Mandiri di Toko / Outlet</span>
                            </div>
                        @endif
                        <div x-show="requestOrderForm.fulfillment_type === 'merchant_delivery'" class="pt-1">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Alamat
                                Tujuan Pengiriman</label>
                            <textarea x-model="requestOrderForm.shipping_address" rows="2"
                                placeholder="Nama jalan, nomor gedung/rumah, kelurahan..."
                                class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Kebutuhan Pesanan & Action Button --}}
                <div class="space-y-4">
                    {{-- Detail Kebutuhan Kustom --}}
                    <div class="space-y-3">
                        <span
                            class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">2.
                            Kebutuhan Pesanan</span>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                {{ $industryLabels['request_order_item_label'] ?? 'Item / Menu yang Diminta' }} <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="requestOrderForm.item_name" required
                                placeholder="{{ $isUmkmRumahan ? 'Contoh: Kue Ulang Tahun Kustom 20cm, Tumpeng Mini 30 Box, Seragam 2 Lusin' : 'Contoh: Paket Katering 50 Porsi, Souvenir Khusus, Fabrikasi Komponen' }}"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                    {{ $industryLabels['request_order_qty_label'] ?? 'Perkiraan Jumlah' }} <span
                                        class="text-red-500">*</span>
                                </label>
                                <input type="number" min="1" x-model="requestOrderForm.item_qty" required
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                    {{ $industryLabels['request_order_date_label'] ?? 'Target Tanggal Dibutuhkan' }}
                                </label>
                                <input type="date" min="{{ $minLeadTimeDate }}"
                                    x-model="requestOrderForm.scheduled_date"
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Detail
                                &amp; Spesifikasi Kebutuhan</label>
                            <textarea x-model="requestOrderForm.notes" rows="3"
                                placeholder="{{ $storeSetting?->order_notes_placeholder ?: ($isUmkmRumahan ? 'Tuliskan selengkap mungkin: varian rasa, tulisan ucapan pada kue, pilihan warna kemasan box, pantangan alergi, dll.' : 'Tuliskan selengkap mungkin: jenis pesanan, kemasan, target budget, spesifikasi bahan/ukuran, dll.') }}"
                                class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-3 border-t border-black/5 dark:border-white/10">
                        <button type="submit" :disabled="requestOrderForm.is_submitting"
                            class="w-full h-12 rounded-[16px] bg-brand-primary hover:opacity-90 active:scale-[0.98] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <span x-show="!requestOrderForm.is_submitting">Pesan</span>
                            <span x-show="requestOrderForm.is_submitting">Memproses...</span>
                            <i x-show="!requestOrderForm.is_submitting" data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- CUSTOMER PO & MULTI-DROP BATCH MODAL (Apple Bento Architecture for B2B)  --}}
    {{-- ========================================================================= --}}
    <div x-show="customerPoModalOpen" x-cloak @keydown.escape.window="customerPoModalOpen = false"
        class="storefront-sheet-overlay fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="storefront-sheet w-full max-w-full sm:max-w-[94vw] md:max-w-3xl lg:max-w-5xl xl:max-w-6xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] p-5 sm:p-7 shadow-2xl border-t sm:border border-black/10 dark:border-white/10 max-h-[94vh] sm:max-h-[92vh] overflow-y-auto space-y-6"
            @click.outside="if(!customerPoForm.is_submitting) customerPoModalOpen = false">

            {{-- Mobile Touch Grab Bar --}}
            <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2 shrink-0"></div>

            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-[14px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#AF52DE] flex items-center justify-center">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[18px] font-bold text-black dark:text-white tracking-tight">Purchase Order
                            (PO) &amp; Batch</h3>
                        <span class="text-[12px] text-black/50 dark:text-white/50">Formulir Pemesanan Grosir, B2B, dan
                            Jadwal Pengiriman Bertahap</span>
                    </div>
                </div>
                <button type="button" @click="customerPoModalOpen = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/60 dark:text-white/60 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Auth / Guest Banner --}}
            @guest('customer')
                <div
                    class="p-4 rounded-[18px] bg-gradient-to-br from-[#5856D6]/10 to-brand-primary/10 border border-[#5856D6]/20 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                    <div class="space-y-0.5">
                        <p class="text-[13px] font-bold text-black dark:text-white">Identitas Pembeli Resmi</p>
                        <p class="text-[11.5px] text-black/60 dark:text-white/60">Wajib login Google &amp; verifikasi
                            WhatsApp untuk menerbitkan PO resmi dan melacak status batch.</p>
                    </div>
                    <a href="{{ route('customer.auth.google') }}?redirect={{ urlencode(url()->current()) }}"
                        class="px-4 py-2 bg-[#5856D6] hover:bg-[#4745B8] text-white rounded-xl text-[12.5px] font-bold transition active:scale-95 shrink-0 flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Login Google</span>
                    </a>
                </div>
            @else
                <div
                    class="p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[12px] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                        <span class="text-black/70 dark:text-white/70">Akun Terdaftar: <strong
                                class="text-black dark:text-white">{{ auth('customer')->user()->name }}</strong>
                            ({{ auth('customer')->user()->phone ?? 'Belum ada No. WA' }})</span>
                    </div>
                    @if (!auth('customer')->user()->isPhoneVerified())
                        <a href="{{ route('customer.otp') }}"
                            class="text-[#FF9500] font-semibold hover:underline">Verifikasi OTP &rarr;</a>
                    @else
                        <span class="text-[#34C759] font-semibold text-[11.5px] inline-flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Akun Terverifikasi</span>
                    @endif
                </div>
            @endguest

            {{-- Error Notification --}}
            <template x-if="customerPoForm.error">
                <div
                    class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="customerPoForm.error"></span>
                </div>
            </template>

            <form @submit.prevent="submitCustomerPo()" class="space-y-6">
                {{-- 1. IDENTITAS PEMESAN & INSTANSI --}}
                <div
                    class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3.5">
                    <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/5 pb-2">
                        <span
                            class="w-5 h-5 rounded-full bg-[#5856D6] text-white text-[11px] font-bold flex items-center justify-center">1</span>
                        <h4 class="text-[13px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70">
                            Data Pemesan &amp; Instansi / Perusahaan</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Nama Lengkap PIC <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="customerPoForm.customer_name" required
                                placeholder="Contoh: Hendra Gunawan"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Nomor WhatsApp PIC <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" x-model="customerPoForm.customer_phone" required
                                placeholder="08123456789"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Nama Perusahaan / Usaha
                            </label>
                            <input type="text" x-model="customerPoForm.company_name"
                                placeholder="PT / CV / Katering ..."
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                No. PO Internal Pembeli
                            </label>
                            <input type="text" x-model="customerPoForm.customer_po_number"
                                placeholder="Contoh: PO-2026/09/012"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Email Instansi / Billing
                            </label>
                            <input type="email" x-model="customerPoForm.customer_email"
                                placeholder="purchasing@company.com"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                        </div>
                    </div>
                </div>

                {{-- 2. DAFTAR ITEM BARANG PO --}}
                <div
                    class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3.5">
                    <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-[#5856D6] text-white text-[11px] font-bold flex items-center justify-center">2</span>
                            <h4
                                class="text-[13px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70">
                                Daftar Produk &amp; Volume PO</h4>
                        </div>
                        <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                            Total Item: <strong class="text-black dark:text-white font-bold"
                                x-text="poTotalItemQty"></strong> unit
                        </span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(it, idx) in customerPoForm.items" :key="idx">
                            <div
                                class="p-3.5 rounded-[16px] bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 space-y-2.5 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11.5px] font-bold text-[#5856D6] dark:text-[#AF52DE]"
                                        x-text="'Item #' + (idx + 1)"></span>
                                    <button type="button" @click="removePoItem(idx)"
                                        x-show="customerPoForm.items.length > 1"
                                        class="text-red-500 hover:text-red-600 text-[11px] font-semibold flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                    <div class="sm:col-span-6">
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Pilih dari Katalog (Opsional)
                                        </label>
                                        <select x-model="it.product_id" @change="onPoProductChange(idx)"
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                            <option value="">-- Tulis Kustom / Bebas --</option>
                                            <template x-for="p in products" :key="p.id">
                                                <option :value="p.id"
                                                    x-text="p.name + ' (' + formatPrice(p.price) + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-6">
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Nama Produk / Spesifikasi PO <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="it.product_name" required
                                            placeholder="Contoh: Baju Seragam drill, Kardus 30x20x10..."
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                    <div class="sm:col-span-4">
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Jumlah Unit <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" min="0.01" step="any"
                                            x-model="it.quantity" required
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Estimasi Harga Satuan (Rp)
                                        </label>
                                        <input type="number" min="0" step="any"
                                            x-model="it.unit_price"
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Catatan / Kode Varian
                                        </label>
                                        <input type="text" x-model="it.notes" placeholder="Warna, ukuran, dll."
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addPoItem()"
                        class="w-full py-2.5 rounded-[14px] border border-dashed border-[#5856D6]/40 text-[#5856D6] dark:text-[#AF52DE] hover:bg-[#5856D6]/5 font-semibold text-[12.5px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Tambah Baris Produk PO</span>
                    </button>
                </div>

                {{-- 3. JADWAL PENGIRIMAN BERTAHAP / MULTI-DROP BATCHES --}}
                <div
                    class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3.5">
                    <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-5 h-5 rounded-full bg-[#5856D6] text-white text-[11px] font-bold flex items-center justify-center">3</span>
                            <h4
                                class="text-[13px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70">
                                Jadwal Kirim Bertahap (Multi-Drop)</h4>
                        </div>
                        <span class="text-[11.5px] px-2 py-0.5 rounded-full"
                            :class="poTotalBatchQty === poTotalItemQty ? 'bg-[#34C759]/15 text-[#34C759] font-bold' :
                                'bg-[#FF9500]/15 text-[#FF9500] font-semibold'">
                            <span x-text="'Alokasi: ' + poTotalBatchQty + ' / ' + poTotalItemQty + ' unit'"></span>
                        </span>
                    </div>

                    <p class="text-[11.5px] text-black/60 dark:text-white/60">
                        Atur pengiriman barang secara bertahap dalam beberapa kali kirim ke satu atau beberapa alamat
                        tujuan berbeda.
                    </p>

                    <div class="space-y-3">
                        <template x-for="(b, bIdx) in customerPoForm.batches" :key="bIdx">
                            <div
                                class="p-3.5 rounded-[16px] bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 space-y-2.5 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="truck" class="w-4 h-4 text-[#5856D6]"></i>
                                        <span class="text-[12.5px] font-bold text-black dark:text-white"
                                            x-text="'Pengiriman Batch #' + (bIdx + 1)"></span>
                                    </div>
                                    <button type="button" @click="removePoBatch(bIdx)"
                                        x-show="customerPoForm.batches.length > 1"
                                        class="text-red-500 hover:text-red-600 text-[11px] font-semibold flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Hapus Batch</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <div>
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Target Tanggal Kirim <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" min="{{ $minLeadTimeDate }}"
                                            x-model="b.scheduled_date" required
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Slot Jam Pengiriman
                                        </label>
                                        <input type="text" x-model="b.scheduled_time_slot"
                                            placeholder="09:00 - 12:00"
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                            Jumlah Kirim Batch <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" min="0.01" step="any" x-model="b.quantity"
                                            required
                                            class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">
                                        Alamat Tujuan Batch Ini (Kosongkan bila sama dengan alamat utama)
                                    </label>
                                    <input type="text" x-model="b.shipping_address"
                                        placeholder="Alamat drop point / gudang cabang..."
                                        class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addPoBatch()"
                        class="w-full py-2.5 rounded-[14px] border border-dashed border-[#5856D6]/40 text-[#5856D6] dark:text-[#AF52DE] hover:bg-[#5856D6]/5 font-semibold text-[12.5px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        <span>+ Tambah Jadwal Pengiriman (Batch Berikutnya)</span>
                    </button>
                </div>

                {{-- 4. ALAMAT UTAMA & METODE PEMBAYARAN --}}
                <div
                    class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3.5">
                    <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/5 pb-2">
                        <span
                            class="w-5 h-5 rounded-full bg-[#5856D6] text-white text-[11px] font-bold flex items-center justify-center">4</span>
                        <h4 class="text-[13px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70">
                            Alamat Utama &amp; Rekomendasi Pembayaran</h4>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Alamat Utama / Kantor Pembeli
                        </label>
                        <textarea x-model="customerPoForm.shipping_address" rows="2"
                            placeholder="Alamat kantor pusat, jalan, nomor gedung, kota..."
                            class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6] resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Rekomendasi Pembayaran
                            </label>
                            <select x-model="customerPoForm.payment_method_id"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                <option value="">Transfer Bank / Invoice Termin PO</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }}
                                        ({{ strtoupper($pm->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                Catatan / Syarat Khusus PO
                            </label>
                            <input type="text" x-model="customerPoForm.notes"
                                placeholder="Termin pembayaran, syarat faktur pajak, dll."
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                        </div>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="submit" :disabled="customerPoForm.is_submitting"
                        class="w-full h-13 rounded-[16px] bg-[#5856D6] hover:bg-[#4745B8] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-md cursor-pointer">
                        <span x-show="!customerPoForm.is_submitting">Pesan</span>
                        <span x-show="customerPoForm.is_submitting">Memproses...</span>
                        <i x-show="!customerPoForm.is_submitting" data-lucide="send" class="w-4 h-4"></i>
                    </button>
                    <p class="text-center text-[11px] text-black/45 dark:text-white/45 mt-2">
                        Nomor tracking token PO dan ringkasan jadwal batch akan otomatis diterbitkan setelah formulir
                        dikirim.
                    </p>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    <div x-show="reservationModalOpen" x-cloak @keydown.escape.window="reservationModalOpen = false"
        class="storefront-sheet-overlay fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="storefront-sheet w-full max-w-full sm:max-w-[94vw] md:max-w-3xl lg:max-w-4xl xl:max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] p-5 sm:p-7 shadow-2xl border-t sm:border border-black/10 dark:border-white/10 max-h-[94vh] sm:max-h-[90vh] overflow-y-auto space-y-5"
            @click.outside="if(!isSubmittingReservation) reservationModalOpen = false">

            {{-- Mobile Touch Grab Bar --}}
            <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2 shrink-0"></div>

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
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
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
                    <p class="text-[12px] text-[#34C759] font-medium pt-1">
                        Ketuk di luar area untuk menutup jendela ini.
                    </p>
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
                            class="mb-4 p-3.5 rounded-[16px] bg-gradient-to-br from-brand-primary/10 to-[#5856D6]/10 border border-brand-primary/20 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                            <div class="space-y-0.5">
                                <p class="text-[13px] font-bold text-black dark:text-white">Masuk dengan Google</p>
                                <p class="text-[11.5px] text-black/60 dark:text-white/60">Wajib login &amp; verifikasi
                                    WhatsApp untuk reservasi.</p>
                            </div>
                            <a href="{{ route('customer.auth.google') }}?redirect={{ urlencode(url()->current()) }}"
                                class="px-4 py-2 bg-brand-primary text-white rounded-xl text-[12.5px] font-bold hover:opacity-90 transition active:scale-95 shrink-0 flex items-center gap-1.5 shadow-sm">
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
                                <span class="text-[#34C759] font-semibold text-[11.5px] inline-flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Terverifikasi</span>
                            @endif
                        </div>
                    @endguest

                    <form @submit.prevent="submitReservation()" class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 items-start">
                        {{-- Left Column: Data Tamu --}}
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
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                        WhatsApp <span class="text-red-500">*</span></label>
                                    <input type="tel" x-model="reservationForm.customer_phone" required
                                        placeholder="08123456789"
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Email
                                        (Opsional)</label>
                                    <input type="email" x-model="reservationForm.customer_email"
                                        placeholder="budi@example.com"
                                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Jadwal, Tamu & Catatan --}}
                        <div class="space-y-4">
                            {{-- Waktu & Jumlah Tamu --}}
                            <div class="space-y-3">
                                <span
                                    class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">2.
                                    Jadwal &amp; Tamu</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Tanggal
                                            Reservasi <span class="text-red-500">*</span></label>
                                        <input type="date" min="{{ $minLeadTimeDate }}"
                                            x-model="reservationForm.reservation_date" required
                                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Waktu
                                            / Jam Kunjungan <span class="text-red-500">*</span></label>
                                        @php
                                            $resAvailableSlots = (array) ($storeSetting?->available_slots ?? []);
                                            $fallbackResSlots = [
                                                '10:00 - 12:00 (Pagi)',
                                                '12:00 - 14:00 (Makan Siang)',
                                                '14:00 - 16:00 (Siang)',
                                                '16:00 - 18:00 (Sore)',
                                                '18:30 - 20:30 (Makan Malam)',
                                                '20:30 - 22:00 (Malam)',
                                            ];
                                            $reservationSlots = !empty($resAvailableSlots)
                                                ? $resAvailableSlots
                                                : $fallbackResSlots;
                                        @endphp
                                        <select x-model="reservationForm.time_slot" required
                                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                            @foreach ($reservationSlots as $slot)
                                                <option value="{{ $slot }}">{{ $slot }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="grid grid-cols-1 {{ isset($posTables) && $posTables->isNotEmpty() ? 'sm:grid-cols-2' : '' }} gap-3">
                                    <div>
                                        <label
                                            class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Jumlah
                                            Orang / Tamu <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center gap-3 bg-black/5 dark:bg-white/5 px-3 py-1.5 rounded-[14px] border border-black/10 dark:border-white/10">
                                                <button type="button"
                                                    @click="if (reservationForm.guest_count > 1) reservationForm.guest_count--"
                                                    class="w-7 h-7 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black dark:text-white font-bold cursor-pointer">&minus;</button>
                                                <span
                                                    class="text-[14px] font-extrabold text-black dark:text-white min-w-8 text-center tabular-nums"
                                                    x-text="reservationForm.guest_count + ' Orang'"></span>
                                                <button type="button" @click="reservationForm.guest_count++"
                                                    class="w-7 h-7 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black dark:text-white font-bold cursor-pointer">&plus;</button>
                                            </div>
                                            <span class="text-[11.5px] text-black/50 dark:text-white/50">Dapat disesuaikan
                                                jika rombongan</span>
                                        </div>
                                    </div>
                                    @if (isset($posTables) && $posTables->isNotEmpty())
                                        <div>
                                            <label
                                                class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                                Pilihan Meja / Ruangan (Opsional)
                                            </label>
                                            <select x-model="reservationForm.pos_table_id"
                                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]">
                                                <option value="">-- Bebas / Ditentukan Otomatis --</option>
                                                @foreach ($posTables as $pt)
                                                    <option value="{{ $pt->id }}">
                                                        {{ $pt->name ?: 'Meja ' . $pt->table_number }} (Kapasitas {{ $pt->capacity }} Orang)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Catatan / Permintaan Khusus --}}
                            <div class="space-y-2 pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Catatan
                                    Tambahan (Layanan / Meja Khusus)</label>
                                <textarea x-model="reservationForm.notes" rows="2"
                                    placeholder="{{ $storeSetting?->order_notes_placeholder ?: 'Contoh: Meja non-smoking, baby chair, dekorasi ulang tahun, dll.' }}"
                                    class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759] resize-none"></textarea>
                            </div>

                            {{-- Submit Button --}}
                            <div class="pt-3 border-t border-black/5 dark:border-white/10">
                                <button type="submit" :disabled="isSubmittingReservation"
                                    class="w-full h-12 rounded-[16px] bg-[#34C759] hover:bg-[#2FB350] active:scale-[0.98] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                    <span x-show="!isSubmittingReservation">Reservasi</span>
                                    <span x-show="isSubmittingReservation">Memproses...</span>
                                    <i x-show="!isSubmittingReservation" data-lucide="arrow-right"
                                        class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: MULAI PESAN BARENG / GROUP ORDER (Bento Apple HIG Architecture)   --}}
    {{-- ========================================================================= --}}
    <div x-show="groupOrder.isCreateModalOpen" x-cloak @keydown.escape.window="groupOrder.isCreateModalOpen = false"
        class="storefront-sheet-overlay fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="storefront-sheet w-full max-w-full sm:max-w-[92vw] md:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] p-5 sm:p-7 shadow-2xl border-t sm:border border-black/10 dark:border-white/10 max-h-[94vh] sm:max-h-[90vh] overflow-y-auto space-y-5"
            @click.outside="if(!groupOrder.createForm.is_submitting) groupOrder.isCreateModalOpen = false">

            {{-- Mobile Touch Grab Bar --}}
            <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2 shrink-0"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-[12px] bg-gradient-to-br from-[#5856D6] to-brand-primary text-white flex items-center justify-center shadow-xs">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Mulai Pesan Bareng (Group Order)</h3>
                        <span class="text-[11.5px] text-black/50 dark:text-white/50">{{ $business->name }}</span>
                    </div>
                </div>
                <button type="button" @click="groupOrder.isCreateModalOpen = false"
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Value Prop Bento Mini Banner --}}
            <div class="p-3.5 rounded-[16px] bg-gradient-to-br from-[#5856D6]/10 via-brand-primary/10 to-transparent border border-[#5856D6]/20 space-y-2">
                <div class="text-[12.5px] font-bold text-black dark:text-white flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                    <span>Solusi Pesan Makanan Rame-Rame Tanpa Oper HP</span>
                </div>
                <ul class="text-[11.5px] text-black/70 dark:text-white/70 space-y-1 pl-1">
                    <li class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                        <span>Bagikan 1 link undangan ke WhatsApp tim/teman kantor.</span>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                        <span>Masing-masing teman login & pilih menu favoritnya sendiri.</span>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                        <span>Rincian patungan (split bill) dihitung otomatis setelah checkout.</span>
                    </li>
                </ul>
            </div>

            <template x-if="groupOrder.createForm.error">
                <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="groupOrder.createForm.error"></span>
                </div>
            </template>

            <form @submit.prevent="submitCreateGroupOrder()" class="space-y-4">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Nama Grup / Acara Kantor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="groupOrder.createForm.title" required
                        placeholder="Contoh: Makan Siang Divisi IT Lt. 5 / Tim BCA Thamrin"
                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                </div>

                @if (!empty($availableBatchDates))
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Pilih Batch Tanggal Pengiriman
                        </label>
                        <select x-model="groupOrder.createForm.scheduled_date"
                            class="w-full h-11 px-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                            @foreach ($availableBatchDates as $b)
                                <option value="{{ $b['date'] }}" {{ ($selectedBatchDate == $b['date']) ? 'selected' : '' }}>
                                    {{ $b['day_name'] ?? '' }}, {{ $b['date'] }} {{ !empty($b['remaining_quota']) ? ('(Sisa ' . $b['remaining_quota'] . ' pcs)') : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Alamat Pengiriman Kantor / Drop Point
                    </label>
                    <textarea x-model="groupOrder.createForm.delivery_address" rows="2"
                        placeholder="Nama gedung, lantai, ruangan, atau patokan serah terima pesanan..."
                        class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Catatan Khusus Pengiriman (Opsional)
                    </label>
                    <input type="text" x-model="groupOrder.createForm.delivery_notes"
                        placeholder="Contoh: Titip di lobby satpam / telepon saat tiba"
                        class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-primary">
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="groupOrder.createForm.is_submitting"
                        class="w-full h-12 rounded-[16px] bg-gradient-to-r from-[#5856D6] to-brand-primary hover:opacity-95 active:scale-[0.98] disabled:opacity-50 text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                        <span x-show="!groupOrder.createForm.is_submitting">Buat Sesi &amp; Dapatkan Link</span>
                        <span x-show="groupOrder.createForm.is_submitting">Membuat Sesi...</span>
                        <i x-show="!groupOrder.createForm.is_submitting" data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DRAWER: KERANJANG BERSAMA / SHARED CART (Apple Slide-Over Architecture)   --}}
    {{-- ========================================================================= --}}
    <div x-show="groupOrder.isDrawerOpen" x-cloak class="fixed inset-0 z-[75] overflow-hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
            @click="groupOrder.isDrawerOpen = false" x-show="groupOrder.isDrawerOpen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="storefront-drawer-frame fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
            <div class="storefront-sheet storefront-drawer w-screen max-w-lg sm:max-w-xl bg-white dark:bg-[#1C1C1E] shadow-2xl border-l border-black/5 dark:border-white/10 flex flex-col justify-between"
                x-show="groupOrder.isDrawerOpen" x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                {{-- Drawer Header --}}
                <div class="p-5 border-b border-black/5 dark:border-white/10 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-[12px] bg-gradient-to-br from-[#5856D6] to-brand-primary text-white flex items-center justify-center shadow-xs">
                                <i data-lucide="users" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight"
                                    x-text="groupOrder.data?.title || 'Keranjang Bersama'"></h3>
                                <div class="flex items-center gap-2 text-[11.5px] text-black/55 dark:text-white/55">
                                    <span>Host: <strong class="text-black dark:text-white" x-text="groupOrder.data?.host_name"></strong></span>
                                    <span class="inline-flex items-center gap-1 text-[#34C759] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                                        Realtime
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="groupOrder.isDrawerOpen = false"
                            class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center text-black/60 dark:text-white/60 cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Link Sharing Quick Bar --}}
                    <div class="p-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between gap-2">
                        <div class="text-[11.5px] text-black/65 dark:text-white/65 truncate">
                            <span>Ajak rekan kerja dengan link ini</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="copyGroupOrderLink()"
                                class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-black/40 border border-black/10 dark:border-white/10 text-black dark:text-white text-[11px] font-bold hover:border-brand-primary transition flex items-center gap-1 cursor-pointer">
                                <i data-lucide="copy" class="w-3 h-3 text-brand-primary"></i>
                                <span>Salin</span>
                            </button>
                            <button type="button" @click="shareGroupOrderWa()"
                                class="px-2.5 py-1 rounded-[8px] bg-[#25D366] text-white text-[11px] font-bold hover:bg-[#20bd5a] transition flex items-center gap-1 cursor-pointer shadow-2xs">
                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                <span>WA</span>
                            </button>
                        </div>
                    </div>

                    {{-- Lock Status Notification --}}
                    <div x-show="groupOrder.data?.is_locked"
                        class="p-2.5 rounded-[12px] bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-[11.5px] font-medium flex items-center gap-2">
                        <i data-lucide="lock" class="w-3.5 h-3.5 shrink-0 text-amber-500"></i>
                        <span>Pesanan dikunci oleh Host. Anggota tidak dapat menambah atau mengubah item.</span>
                    </div>

                    {{-- Checked Out Notification --}}
                    <div x-show="groupOrder.data?.is_checked_out"
                        class="p-2.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] text-[11.5px] font-medium flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Pesanan sudah dicheckout oleh Host.</span>
                        </div>
                        <button type="button" @click="groupOrder.isSplitBillOpen = true"
                            class="font-bold underline cursor-pointer">Rincian Patungan</button>
                    </div>
                </div>

                {{-- Drawer Items Grouped by Member --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <template x-if="groupOrder.splitBill && groupOrder.splitBill.length > 0">
                        <div class="space-y-4">
                            <template x-for="member in groupOrder.splitBill" :key="member.member_id">
                                <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                                    {{-- Member Header --}}
                                    <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-brand-primary/15 text-brand-primary text-[11px] font-bold flex items-center justify-center">
                                                <span x-text="member.member_name ? member.member_name.substring(0, 2).toUpperCase() : 'ME'"></span>
                                            </div>
                                            <div class="leading-tight">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[13px] font-bold text-black dark:text-white" x-text="member.member_name"></span>
                                                    <span x-show="member.is_host" class="px-1.5 py-0.2 rounded-full text-[9.5px] font-bold bg-[#5856D6]/15 text-[#5856D6]">Host</span>
                                                </div>
                                                <span class="text-[10.5px] text-black/45 dark:text-white/45" x-text="(member.items ? member.items.length : 0) + ' jenis menu'"></span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[13px] font-extrabold text-black dark:text-white tabular-nums" x-text="formatPrice(member.member_subtotal)"></span>
                                        </div>
                                    </div>

                                    {{-- Member Items --}}
                                    <div class="space-y-2.5 divide-y divide-black/5 dark:divide-white/5">
                                        <template x-for="it in member.items" :key="it.id">
                                            <div class="pt-2 first:pt-0 flex items-start justify-between gap-3">
                                                <div class="flex-1 min-w-0 space-y-0.5">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-[12.5px] font-bold text-black dark:text-white" x-text="it.product_name"></span>
                                                    </div>
                                                    <div class="text-[11.5px] text-brand-primary font-semibold tabular-nums" x-text="formatPrice(it.unit_price) + ' x ' + it.quantity + ' = ' + formatPrice(it.line_total)"></div>
                                                    <template x-if="it.notes">
                                                        <div class="text-[11px] text-black/50 dark:text-white/50 italic" x-text="'Catatan: ' + it.notes"></div>
                                                    </template>
                                                </div>

                                                {{-- Edit / Stepper (only if owned or host, and open) --}}
                                                <template x-if="(it.is_mine || groupOrder.data.is_host) && !groupOrder.data.is_locked && !groupOrder.data.is_checked_out">
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <div class="flex items-center gap-1 bg-black/5 dark:bg-white/5 px-2 py-0.5 rounded-full border border-black/5 dark:border-white/5">
                                                            <button type="button" @click="updateGroupOrderItem(it.id, -1)"
                                                                class="w-5 h-5 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-black font-bold text-[12px] active:scale-90 transition cursor-pointer">&minus;</button>
                                                            <span class="text-[11.5px] font-bold text-black dark:text-white min-w-4 text-center tabular-nums" x-text="it.quantity"></span>
                                                            <button type="button" @click="updateGroupOrderItem(it.id, 1)"
                                                                class="w-5 h-5 flex items-center justify-center rounded-full bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70 hover:text-black font-bold text-[12px] active:scale-90 transition cursor-pointer">&plus;</button>
                                                        </div>
                                                        <button type="button" @click="removeGroupOrderItem(it.id)"
                                                            class="w-7 h-7 flex items-center justify-center text-red-500 hover:text-red-600 active:scale-90 transition cursor-pointer"
                                                            title="Hapus menu ini">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!groupOrder.splitBill || groupOrder.splitBill.length === 0">
                        <div class="py-16 text-center text-black/40 dark:text-white/40 space-y-3">
                            <div class="w-14 h-14 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                                <i data-lucide="users" class="w-7 h-7"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-[15px] text-black dark:text-white">Keranjang Bersama Masih Kosong</h4>
                                <p class="text-[12.5px] max-w-xs mx-auto text-black/60 dark:text-white/60">
                                    Pilih menu favorit Anda dari katalog dan klik <strong>+ Pesan Bareng</strong>, lalu bagikan tautan ini ke WhatsApp rekan kerja.
                                </p>
                            </div>
                            <button type="button" @click="shareGroupOrderWa()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#25D366] text-white text-[12px] font-bold shadow-xs cursor-pointer active:scale-95 transition">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Bagikan Link ke WhatsApp</span>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Drawer Footer & Checkout Controls --}}
                <div class="p-5 border-t border-black/5 dark:border-white/10 space-y-3.5 bg-black/[0.02] dark:bg-white/[0.02]">
                    {{-- Summary Total Row --}}
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-[12px] text-black/60 dark:text-white/60">
                            <span>Total Item Terkumpul</span>
                            <span class="font-bold tabular-nums text-black dark:text-white">
                                <span x-text="groupOrder.data?.total_quantity || 0"></span> unit
                                (Maks. 150 PCS Batch)
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[14px]">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-black/60 dark:text-white/60">Subtotal Bersama</span>
                                <button type="button" @click="groupOrder.isSplitBillOpen = true"
                                    x-show="groupOrder.splitBill && groupOrder.splitBill.length > 0"
                                    class="px-2 py-0.5 rounded-full bg-[#5856D6]/10 hover:bg-[#5856D6]/20 text-[#5856D6] dark:text-[#AF52DE] text-[10.5px] font-bold transition flex items-center gap-1 cursor-pointer"
                                    title="Lihat Rincian Patungan Masing-Masing">
                                    <i data-lucide="receipt" class="w-3 h-3"></i>
                                    <span>Rincian Patungan</span>
                                </button>
                            </div>
                            <span class="font-extrabold text-[18px] text-brand-primary tabular-nums"
                                x-text="formatPrice(groupOrder.data?.subtotal || 0)"></span>
                        </div>
                    </div>

                    {{-- Actions for Host --}}
                    <template x-if="groupOrder.data && groupOrder.data.is_host">
                        <div class="space-y-2">
                            <template x-if="!groupOrder.data.is_checked_out">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="toggleLockGroupOrder()"
                                        :disabled="groupOrder.actionLoading"
                                        class="h-11 rounded-[14px] border border-black/10 dark:border-white/10 bg-white dark:bg-black/40 text-black dark:text-white text-[12px] font-bold hover:bg-black/5 transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                                        <i :data-lucide="groupOrder.data.is_locked ? 'unlock' : 'lock'" class="w-3.5 h-3.5"></i>
                                        <span x-text="groupOrder.data.is_locked ? 'Buka Kunci' : 'Kunci Pesanan'"></span>
                                    </button>
                                    <button type="button" @click="openGroupOrderCheckout()"
                                        :disabled="(groupOrder.data.total_quantity || 0) <= 0 || groupOrder.actionLoading"
                                        class="h-11 rounded-[14px] bg-brand-primary hover:opacity-90 disabled:opacity-50 text-white font-bold text-[12.5px] transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer disabled:cursor-not-allowed">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                        <span>Checkout Bersama</span>
                                    </button>
                                </div>
                            </template>
                            <template x-if="groupOrder.data.is_checked_out">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="groupOrder.isSplitBillOpen = true"
                                        class="h-11 rounded-[14px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-[12.5px] font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                        <span>Rincian Patungan</span>
                                    </button>
                                    <a :href="groupOrder.orderTrackingUrl || '#'"
                                        class="h-11 rounded-[14px] bg-brand-primary hover:opacity-90 text-white text-[12.5px] font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                        <span>Lacak Pesanan</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Notice for Members (Non-Host) --}}
                    <template x-if="groupOrder.data && !groupOrder.data.is_host">
                        <div class="space-y-2">
                            <template x-if="!groupOrder.data.is_checked_out">
                                <div class="p-2.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/5 text-[11.5px] text-black/60 dark:text-white/60 text-center">
                                    <span>Pesanan ini akan dicheckout dan divalidasi oleh Host: <strong class="text-black dark:text-white" x-text="groupOrder.data.host_name"></strong></span>
                                </div>
                            </template>
                            <template x-if="groupOrder.data.is_checked_out">
                                <button type="button" @click="groupOrder.isSplitBillOpen = true"
                                    class="w-full h-11 rounded-[14px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-[12.5px] font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                    <span>Lihat Rincian Patungan (Split Bill)</span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: RINCIAN PATUNGAN (SPLIT BILL) (Apple Bento Architecture)           --}}
    {{-- ========================================================================= --}}
    <div x-show="groupOrder.isSplitBillOpen" x-cloak @keydown.escape.window="groupOrder.isSplitBillOpen = false"
        class="storefront-sheet-overlay fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="storefront-sheet w-full max-w-full sm:max-w-[92vw] md:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[28px] p-5 sm:p-7 shadow-2xl border-t sm:border border-black/10 dark:border-white/10 max-h-[94vh] sm:max-h-[90vh] overflow-y-auto space-y-5"
            @click.outside="groupOrder.isSplitBillOpen = false">

            {{-- Mobile Touch Grab Bar --}}
            <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2 shrink-0"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#AF52DE] flex items-center justify-center">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Rincian Patungan (Split Bill)</h3>
                        <span class="text-[11.5px] text-black/50 dark:text-white/50" x-text="groupOrder.data?.title || 'Pesanan Bersama'"></span>
                    </div>
                </div>
                <button type="button" @click="groupOrder.isSplitBillOpen = false"
                    class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Summary Card --}}
            <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider block">Total Tagihan Pesanan</span>
                    <span class="text-[20px] font-extrabold text-brand-primary tabular-nums" x-text="formatPrice(groupOrder.data?.subtotal || 0)"></span>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider block">Jumlah Pemesan</span>
                    <span class="text-[15px] font-bold text-black dark:text-white tabular-nums" x-text="(groupOrder.data?.members_count || 1) + ' orang'"></span>
                </div>
            </div>

            {{-- Breakdown per Member --}}
            <div class="space-y-3">
                <span class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Rincian Pembayaran Masing-Masing:</span>
                <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                    <template x-for="member in groupOrder.splitBill" :key="member.member_id">
                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 shadow-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-bold flex items-center justify-center">
                                        <span x-text="member.member_name ? member.member_name.substring(0, 2).toUpperCase() : 'M'"></span>
                                    </span>
                                    <span class="text-[13px] font-bold text-black dark:text-white" x-text="member.member_name"></span>
                                    <span x-show="member.is_host" class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#5856D6]/10 text-[#5856D6]">Host</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[13.5px] font-extrabold text-brand-primary tabular-nums" x-text="formatPrice(member.member_subtotal)"></span>
                                </div>
                            </div>
                            <div class="space-y-1 pl-8 text-[11.5px] text-black/60 dark:text-white/60">
                                <template x-for="it in member.items" :key="it.id">
                                    <div class="flex items-center justify-between">
                                        <span x-text="it.quantity + 'x ' + it.product_name + (it.notes ? ' (' + it.notes + ')' : '')"></span>
                                        <span class="tabular-nums" x-text="formatPrice(it.line_total)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Reimbursement Notice --}}
            <div class="p-3.5 rounded-[16px] bg-[#5856D6]/10 border border-[#5856D6]/20 text-[12px] text-black/70 dark:text-white/70 space-y-1.5">
                <div class="font-bold text-[#5856D6] dark:text-[#AF52DE] flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                    <span>Informasi Pembayaran Patungan:</span>
                </div>
                <p class="leading-relaxed">
                    Pesanan telah dibayar terpusat oleh Host (<strong class="text-black dark:text-white" x-text="groupOrder.data?.host_name"></strong>). Setiap rekan dapat mentransfer nominal di atas ke Host dan mengonfirmasi bukti transfer melalui chat.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-2 space-y-2">
                <button type="button" @click="copySplitBillText()"
                    class="w-full h-12 rounded-[16px] bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-[13.5px] transition flex items-center justify-center gap-2 shadow-xs cursor-pointer active:scale-98">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Salin Rincian Patungan ke WhatsApp</span>
                </button>
                <template x-if="groupOrder.orderTrackingUrl">
                    <a :href="groupOrder.orderTrackingUrl"
                        class="w-full h-11 rounded-[16px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white font-bold text-[13px] transition flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="truck" class="w-4 h-4 text-brand-primary"></i>
                        <span>Buka Halaman Lacak Pesanan (Tracking)</span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (Apple Frosted Glass Widget)                     --}}
    {{-- ========================================================================= --}}
    @if ($hasWhatsapp)
        <div class="fixed flex bottom-20 right-4 md:bottom-6 md:right-6 z-50 flex-col items-end select-none">

            {{-- Floating Greeting Bubble --}}
            <div x-show="waChatOpen" x-transition.opacity
                class="mb-3 p-4 rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl shadow-xl border border-black/10 dark:border-white/10 max-w-xs text-[12px] space-y-2.5"
                style="display: none;">
                <div class="flex items-center justify-between">
                    <span
                        class="font-semibold text-[13px] text-black dark:text-white tracking-tight">{{ $business->name }}</span>
                    <button type="button" @click="waChatOpen = false"
                        class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed font-normal">
                    {{ $landingPage->whatsapp_welcome_message ?: 'Halo! Ada yang bisa kami bantu seputar produk atau layanan kami?' }}
                </p>
                <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                    class="w-full h-9 rounded-full bg-[#25D366] text-white font-semibold text-[12.5px] tracking-tight text-center flex items-center justify-center gap-1.5 hover:opacity-90 active:scale-95 transition shadow-sm">
                    <span>Hubungi via WhatsApp</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- Button Trigger (Apple Touch Circle) --}}
            <button type="button" @click="waChatOpen = !waChatOpen"
                class="w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-[0_8px_25px_rgba(37,211,102,0.35)] hover:scale-105 active:scale-95 transition-all duration-200 group relative cursor-pointer"
                aria-label="Hubungi WhatsApp">
                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0zm0 21.785a9.874 9.874 0 0 1-5.032-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.861 9.861 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.888 9.884z" />
                </svg>
            </button>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>

</html>
