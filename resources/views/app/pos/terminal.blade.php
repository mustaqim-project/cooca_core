<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('pos.terminal_title') }} - {{ $business->name }}</title>

    <!-- Theme & I18N Initialization Script (Instant) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('cooca-pos-theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
        window.COOCA_I18N = @json(__('pos'));
    </script>

    <!-- Google Fonts (Inter as Apple SF Pro fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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
                        system: {
                            blue: '#007AFF',
                            green: '#34C759',
                            orange: '#FF9500',
                            red: '#FF3B30',
                            purple: '#AF52DE',
                            indigo: '#5856D6',
                            teal: '#30B0C7',
                            yellow: '#FFCC00',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    <style>
        /* Base typography and clean layout */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
            -webkit-text-size-adjust: 100%;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        }

        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }

        /* Universal Adaptive Layout Foundation (Zero overlap) */
        .pos-shell {
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .pos-main {
            flex: 1 1 auto;
            display: flex;
            min-height: 0;
            overflow: hidden;
            position: relative;
        }

        .pos-catalog {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
        }

        .pos-products {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        /* Modal Adaptive Sizing (Apple Bento Modal Sheet) */
        .pos-modal-panel {
            max-height: min(90dvh, calc(100vh - 1.5rem)) !important;
            max-width: min(calc(100vw - 1rem), 42rem) !important;
            overflow-y: auto !important;
            overscroll-behavior: contain !important;
            -webkit-overflow-scrolling: touch !important;
        }

        /* Prevent Safari iOS Viewport Auto-Zoom on form inputs */
        @media screen and (max-width: 768px) {
            input:not([type="checkbox"]):not([type="radio"]),
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        input,
        select,
        textarea,
        button {
            touch-action: manipulation;
        }

        /* Sleek Apple-style scrollbars */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(120, 120, 128, 0.2);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(120, 120, 128, 0.4);
        }

        [x-cloak] {
            display: none !important;
        }

        /* ===================================================== */
        /* RESPONSIVE DESKTOP VS MOBILE/TABLET (Apple HIG)        */
        /* ===================================================== */

        /* DESKTOP (>= 1024px): Side-by-side permanent columns */
        @media (min-width: 1024px) {
            .pos-cart {
                display: flex !important;
                flex-direction: column;
                width: 380px;
                flex-shrink: 0;
                position: relative;
                transform: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                z-index: 10;
            }

            .pos-cart-backdrop {
                display: none !important;
            }

            .pos-cart-bar {
                display: none !important;
            }

            .pos-cart-btn-close {
                display: none !important;
            }
        }

        /* MOBILE & TABLET PORTRAIT (< 1024px): Full-width catalog + iOS 18 Bottom Sheet */
        @media (max-width: 1023px) {
            .pos-cart {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100% !important;
                max-width: 100% !important;
                height: min(85dvh, 52rem);
                max-height: calc(100dvh - env(safe-area-inset-top, 0px));
                border-radius: 20px 20px 0 0;
                z-index: 120 !important;
                transform: translate3d(0, 102%, 0);
                transition: transform .28s cubic-bezier(.25, .1, .25, 1);
                box-shadow: 0 -20px 50px rgba(0, 0, 0, 0.35);
            }

            .pos-cart.pos-cart-open {
                transform: translate3d(0, 0, 0);
            }

            .pos-cart-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                z-index: 110 !important;
            }

            .pos-cart-btn-close {
                display: flex !important;
            }

            .pos-cart-bar {
                display: flex;
                position: fixed;
                left: 0.75rem;
                right: 0.75rem;
                bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));
                z-index: 100 !important;
                transition: opacity .2s, transform .2s;
            }

            body:has(.pos-cart.pos-cart-open) .pos-cart-bar {
                opacity: 0;
                pointer-events: none;
                transform: translateY(100%);
            }

            /* Extra bottom padding on catalog so bottom cards are never covered by floating cart bar */
            .pos-products {
                padding-bottom: 6.5rem;
            }

            .category-bar {
                scrollbar-width: none;
            }

            .category-bar::-webkit-scrollbar {
                display: none;
            }
        }
    </style>
</head>

<body class="h-full bg-[#F2F2F7] dark:bg-black text-[#000000] dark:text-[#F2F2F7] antialiased select-none"
    x-data="posApp()" x-init="initPos()">

    @php
        $isWorkshop = $business && $business->isWorkshop() && ! $business->isLaundry() && ! $business->isPharmacy() && ! $business->isFoodIndustry();
        $isLaundry = $business && $business->isLaundry();
        $isPharmacy = $business && $business->isPharmacy();
        $isFnB = $business && $business->isFoodIndustry();
        $hasDineIn = $business && $business->hasDineInFeature();
        $isRetail = ! $isWorkshop && ! $isLaundry && ! $isPharmacy && ! $isFnB;
    @endphp

    @if ($business && $business->hasDineInFeature())
    <!-- ===================================================== -->
    <!-- DYNAMIC FLOATING NOTIFICATION: NEW QR TABLE ORDER     -->
    <!-- ===================================================== -->
    <div x-show="latestQrNotification" x-cloak
        x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500 transform"
        x-transition:enter-start="-translate-y-12 opacity-0 scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-250 transform"
        x-transition:leave-start="translate-y-0 opacity-100 scale-100"
        x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[150] w-full max-w-lg px-4 pointer-events-none"
        @mouseenter="pauseNotificationTimer()" @mouseleave="resumeNotificationTimer()">
        <div
            class="pointer-events-auto backdrop-blur-2xl bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/10 dark:border-white/15 rounded-2xl p-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.6)] text-black dark:text-white relative overflow-hidden ring-1 ring-black/5 dark:ring-white/10">

            <!-- Subtle Top Accent Glow Gradient -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#007AFF] via-[#34C759] to-[#007AFF]">
            </div>

            <div class="flex items-start gap-3.5 pt-1">
                <!-- Pulsing Bell Icon Tile -->
                <div class="relative shrink-0 mt-0.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-[#007AFF]/15 dark:bg-[#007AFF]/25 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#34C759] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-[#34C759]"></span>
                    </span>
                </div>

                <!-- Content Area -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span
                                class="text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF]">
                                Order Masuk via QR
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]"
                                x-text="'Meja ' + (latestQrNotification?.table_number || latestQrNotification?.pos_table?.table_number || latestQrNotification?.table_or_reference || '-')">
                            </span>
                            <template x-if="latestQrNotification?.totalNewCount > 1">
                                <span
                                    class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF9500]/20 text-[#FF9500]"
                                    x-text="'+' + (latestQrNotification.totalNewCount - 1) + ' antrean'">
                                </span>
                            </template>
                        </div>
                        <!-- Close button -->
                        <button type="button" @click="dismissQrNotification()"
                            class="w-6 h-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Customer and Items Summary -->
                    <div class="mt-1.5 flex items-baseline justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-black dark:text-white truncate"
                                x-text="latestQrNotification?.customer_name || latestQrNotification?.customer_name_guest || 'Tamu / Pelanggan'">
                            </div>
                            <div class="text-xs text-black/60 dark:text-white/60 truncate mt-0.5"
                                x-text="formatNotificationItems(latestQrNotification)"></div>
                        </div>
                        <div class="text-right shrink-0">
                            <span
                                class="text-xs font-semibold text-black/45 dark:text-white/45 block text-[10px]">Total</span>
                            <span class="text-sm font-extrabold text-[#007AFF] tabular-nums"
                                x-text="formatRupiah(latestQrNotification?.total_amount)"></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button type="button" @click="openIncomingOrdersModal(); dismissQrNotification();"
                            class="h-8 px-3 rounded-lg bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] text-xs font-semibold text-black/75 dark:text-white/80 transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#FF9500]" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>Lihat Antrean (<span x-text="pendingQrCount"></span>)</span>
                        </button>

                        <button type="button"
                            @click="acceptAndLoadToCart(latestQrNotification.id); dismissQrNotification();"
                            class="h-8 px-3.5 rounded-lg bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            <span>Buka di Kasir</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Auto-dismiss Progress Bar -->
            <div class="mt-2.5 h-1 w-full bg-black/[0.05] dark:bg-white/[0.08] rounded-full overflow-hidden">
                <div class="h-full bg-[#007AFF] transition-all duration-100 ease-linear rounded-full"
                    :style="'width: ' + notificationProgressPercent + '%'"></div>
            </div>
        </div>
    </div>
    @endif

    <div class="pos-shell h-screen flex flex-col overflow-hidden">

        <!-- ===================================================== -->
        <!-- 1. POS TOP TOOLBAR / NAVBAR (macOS Sonoma Style)       -->
        <!-- ===================================================== -->
        <header
            class="pos-header h-14 sm:h-16 bg-[#0B1528] text-white border-b border-white/10 flex items-center justify-between px-3 sm:px-6 shrink-0 z-20 transition-colors">
            <!-- Left: Brand / Navigation / Location -->
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <!-- Back to Dashboard / Brand Logo -->
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-2 text-white hover:opacity-90 transition shrink-0"
                    title="Kembali ke Dashboard">
                    <div class="w-8.5 h-8.5 sm:w-9 sm:h-9 shrink-0 rounded-[10px] bg-[#007AFF] text-white flex items-center justify-center font-black text-sm shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009 9.35c.667 0 1.3-.217 1.815-.584a3 3 0 004.37 0c.515.367 1.148.584 1.815.584a3 3 0 002.25-.966 3 3 0 003.75.615m-16.5 0L12 3l7.5 6.35" />
                        </svg>
                    </div>
                    <span class="font-extrabold text-[16px] tracking-tight text-white hidden sm:inline">COOCA</span>
                </a>

                <div class="h-6 w-px bg-white/20 hidden sm:block"></div>

                <!-- Store Info Tile -->
                <div class="min-w-0">
                    <div
                        class="font-bold text-[13px] sm:text-[14px] text-white tracking-tight leading-none truncate max-w-[120px] sm:max-w-[200px] md:max-w-[260px]">
                        {{ $business->name }}</div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-white/50 mt-0.5 sm:mt-1">Terminal
                        Kasir POS</div>
                </div>

                <!-- Outlet Location Selector Pill -->
                <div
                    class="hidden sm:flex items-center gap-1.5 ml-1 sm:ml-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs shrink-0">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <select x-model="selectedLocationId" @change="changeLocation()"
                        class="bg-transparent text-white text-[11px] font-semibold focus:outline-none cursor-pointer max-w-[140px] truncate">
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}"
                                class="bg-[#0B1528] text-white"
                                {{ $loc->id === $selectedLocationId ? 'selected' : '' }}>
                                {{ $loc->parent ? ($loc->parent->name . ' ↳ ' . $loc->name) : $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Center: Search Input Bar with Barcode Scanner (Exact match to screenshot) -->
            <div class="flex-1 max-w-md xl:max-w-xl mx-3 min-w-0 hidden md:block">
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 z-10">
                        <svg class="w-4 h-4 text-black/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" x-ref="barcodeSearchInput" x-model="searchQuery"
                        @input="filterProducts()"
                        @keydown.enter="handleBarcodeOrSearch()"
                        placeholder="Cari nama produk, SKU, atau scan barcode..."
                        class="w-full h-10 bg-white border border-transparent rounded-[12px] pl-10 pr-11 text-[13px] text-black placeholder:text-black/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-sm transition">
                    <button type="button" @click="requestBarcodeScannerAccess()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-black/50 hover:text-[#007AFF] transition"
                        title="Scan Barcode Kamera">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Right: Actions Toolbar (Responsive: Desktop full toolbar, Mobile Hamburger + Scan + Fullscreen) -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">

                <!-- 1. MOBILE ONLY ACTION GROUP (< md): SCAN + FULLSCREEN + ENLARGED HAMBURGER -->
                <div class="flex md:hidden items-center gap-2 shrink-0">
                    <!-- Scan Barcode Kamera Button (Besar & Mudah Ditekan) -->
                    <button type="button" @click="requestBarcodeScannerAccess()"
                        class="h-10 w-10 sm:h-11 sm:w-11 rounded-[12px] bg-white/10 hover:bg-white/15 active:scale-95 text-white flex items-center justify-center border border-white/15 shadow-sm transition shrink-0"
                        title="Scan Barcode Kamera">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-white" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                        </svg>
                    </button>

                    <!-- Fullscreen Toggle Button (Besar & Mudah Ditekan) -->
                    <button type="button" @click="toggleFullscreen()"
                        class="h-10 w-10 sm:h-11 sm:w-11 rounded-[12px] bg-white/10 hover:bg-white/15 active:scale-95 text-white flex items-center justify-center border border-white/15 shadow-sm transition shrink-0"
                        :title="isFullscreen ? 'Keluar Fullscreen' : 'Mode Layar Penuh'">
                        <template x-if="!isFullscreen">
                            <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-white" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                            </svg>
                        </template>
                        <template x-if="isFullscreen">
                            <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-white" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
                            </svg>
                        </template>
                    </button>

                    <!-- Hamburger Drawer Toggle Button (Besar & Menonjol) -->
                    <button type="button" @click="mobileMenuOpen = true"
                        class="h-10 w-10 sm:h-11 sm:w-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0071EB] active:scale-95 text-white flex items-center justify-center border border-white/20 shadow-md transition shrink-0 relative"
                        title="Buka Menu Kasir POS">
                        <svg class="w-5.5 h-5.5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <!-- Notif badge on hamburger -->
                        <span x-show="pendingQrCount > 0 || (heldOrders && heldOrders.length > 0)"
                            class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FF3B30] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#FF3B30] border-2 border-[#0B1528]"></span>
                        </span>
                    </button>
                </div>

                <!-- 2. DESKTOP TOOLBAR (md:flex) -->
                <div class="hidden md:flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <!-- Online / Offline Connectivity Indicator Pill (Apple HIG) -->
                    <div :class="isOnline ? 'bg-[#34C759]/20 border-[#34C759]/30 text-[#30D158]' : 'bg-[#FF3B30]/20 border-[#FF3B30]/30 text-[#FF453A]'"
                        class="flex items-center gap-1.5 px-2.5 py-1 rounded-[10px] border text-[11px] font-semibold transition-colors shrink-0"
                        :title="isOnline ? 'Koneksi internet stabil (Online)' : 'Koneksi internet terputus (Offline)'">
                        <span :class="isOnline ? 'bg-[#34C759]' : 'bg-[#FF3B30]'" class="w-2 h-2 shrink-0 rounded-full animate-pulse"></span>
                        <span class="hidden lg:inline" x-text="isOnline ? 'Online' : 'Offline'"></span>
                    </div>

                    <!-- Shift Indicator Pill -->
                    <template x-if="activeShift">
                        <div
                            class="flex items-center gap-1.5 px-2.5 py-1 rounded-[10px] bg-[#34C759]/20 border border-[#34C759]/30 text-[#30D158] text-[11px]">
                            <span class="w-2 h-2 shrink-0 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span class="hidden md:inline font-semibold">Shift Aktif</span>
                            <button @click="openShiftCloseModal()"
                                class="px-2 py-0.5 rounded-[6px] bg-[#0B1528] hover:bg-black/50 text-white text-[10px] font-bold transition ml-0.5">Tutup</button>
                        </div>
                    </template>
                    <template x-if="!activeShift">
                        <div
                            class="flex items-center gap-1.5 px-2.5 py-1 rounded-[10px] bg-[#FF9500]/20 border border-[#FF9500]/30 text-[#FF9F0A] text-[11px]">
                            <span class="w-2 h-2 shrink-0 rounded-full bg-[#FF9500]"></span>
                            <span class="hidden md:inline font-semibold">Shift Tutup</span>
                            <button @click="showOpenShiftModal = true"
                                class="px-2 py-0.5 rounded-[6px] bg-[#0B1528] hover:bg-black/50 text-white text-[10px] font-bold transition ml-0.5">Buka</button>
                        </div>
                    </template>

                    @if ($business && $business->hasDineInFeature())
                    <!-- QR Table Orders Button -->
                    <button @click="openIncomingOrdersModal()"
                        :class="pendingQrCount > 0 ?
                            'bg-[#007AFF] text-white ring-2 ring-white/30' :
                            'bg-white/10 hover:bg-white/15 text-white border border-white/10'"
                        class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] active:scale-[0.97] text-[12px] font-medium transition flex items-center gap-1.5 relative"
                        title="Pesanan Masuk dari Meja QR">
                        <div class="relative flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#007AFF] shrink-0" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                            </svg>
                            <span x-show="pendingQrCount > 0" class="absolute -top-1 -right-1 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#007AFF] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#007AFF]"></span>
                            </span>
                        </div>
                        <span class="hidden md:inline">Order QR</span>
                        <span x-show="pendingQrCount > 0" x-text="pendingQrCount"
                            class="px-1.5 py-0.5 rounded-full bg-[#007AFF] text-white font-bold text-[9px] flex items-center justify-center tabular-nums"></span>
                    </button>

                    @if (\App\Support\Context::hasPermission('pos.tables'))
                        <!-- Resto Meja Selector Button -->
                        <button @click="openTablesModal('tables')"
                            class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-[0.97] text-white text-[12px] font-medium transition flex items-center gap-1.5 border border-white/10"
                            title="Daftar Meja & Sesi Tagihan Meja">
                            <svg class="w-4 h-4 text-[#34C759] shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span class="hidden md:inline">Meja</span>
                            <template x-if="selectedTable">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#34C759]/30 text-[#34C759]"
                                    x-text="'M-' + selectedTable.table_number"></span>
                            </template>
                        </button>

                        <!-- Reservasi Storefront Button -->
                        <button @click="openTablesModal('reservations')"
                            :class="todayReservations.length > 0 ? 'bg-[#5856D6] text-white ring-2 ring-white/20' : 'bg-white/10 hover:bg-white/15 text-white border border-white/10'"
                            class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] active:scale-[0.97] text-[12px] font-medium transition flex items-center gap-1.5 relative"
                            title="Jadwal Reservasi Meja dari Storefront">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span class="hidden md:inline">Reservasi</span>
                            <span x-show="todayReservations.length > 0" x-text="todayReservations.length"
                                class="px-1.5 py-0.5 rounded-full bg-white text-[#5856D6] font-bold text-[9px] flex items-center justify-center tabular-nums"></span>
                        </button>
                    @endif
                    @endif

                    @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                        <!-- Auto KDS (Paperless Kitchen) Toggle Button -->
                        <button type="button" @click="toggleAutoKds()"
                            :class="posAutoSendKds ? 'bg-[#34C759]/20 border-[#34C759]/40 text-[#30D158]' : 'bg-white/10 hover:bg-white/15 text-white/60 border-white/10'"
                            class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] active:scale-[0.97] text-[12px] font-medium transition flex items-center gap-1.5 border"
                            :title="posAutoSendKds ? 'Auto KDS Aktif: Order kasir otomatis masuk ke layar dapur tanpa perlu print struk kertas' : 'Auto KDS Nonaktif: Order kasir tidak otomatis diteruskan ke KDS'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693l-1.57-.393m15.6 0l1.4 3.5a2.25 2.25 0 01-2.09 3.085H5.89a2.25 2.25 0 01-2.09-3.085l1.4-3.5" />
                            </svg>
                            <span class="hidden md:inline font-semibold">KDS</span>
                            <span class="w-2 h-2 rounded-full" :class="posAutoSendKds ? 'bg-[#34C759] animate-pulse' : 'bg-white/40'"></span>
                            <span class="text-[10px] uppercase font-bold tracking-wider" :class="posAutoSendKds ? 'text-[#34C759]' : 'text-white/40'" x-text="posAutoSendKds ? 'ON' : 'OFF'"></span>
                        </button>
                    @endif

                    <!-- Antrean Hold Button -->
                    <button @click="showHeldOrdersModal = true"
                        class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-[0.97] text-white text-[12px] font-medium transition flex items-center gap-1.5 border border-white/10"
                        title="Antrean Transaksi (Hold)">
                        <svg class="w-4 h-4 text-[#FF9500] shrink-0" fill="none" stroke="currentColor"
                            stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="hidden md:inline">Hold</span>
                        <span x-show="heldOrders.length > 0" x-text="heldOrders.length"
                            class="w-4 h-4 rounded-full bg-[#FF9500] text-black font-bold text-[9px] flex items-center justify-center tabular-nums"></span>
                    </button>

                    @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                        <!-- Kas Masuk / Keluar Button -->
                        <button @click="showCashMovementModal = true"
                            class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-[0.97] text-white text-[12px] font-medium transition flex items-center gap-1.5 border border-white/10"
                            title="Kas Masuk / Kas Keluar">
                            <svg class="w-4 h-4 text-white/70 shrink-0" fill="none"
                                stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            <span class="hidden md:inline">Kas</span>
                        </button>
                    @endif

                    <!-- Dedicated Setting POS & Hardware Hub Button -->
                    <button type="button" @click="showPosSettingsModal = true"
                        class="h-8.5 sm:h-9 px-2.5 sm:px-3 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-[0.97] text-white text-[12px] font-medium transition flex items-center gap-1.5 border border-white/10"
                        title="Pusat Pengaturan POS & Hardware">
                        <svg class="w-4 h-4 text-white/80 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="hidden md:inline">Setting POS</span>
                    </button>

                    <!-- Fullscreen Toggle (Desktop) -->
                    <button type="button" @click="toggleFullscreen()"
                        class="h-8.5 w-8.5 sm:h-9 sm:w-9 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-[0.97] text-white flex items-center justify-center transition"
                        :title="isFullscreen ? 'Keluar Fullscreen' : 'Mode Layar Penuh'">
                        <template x-if="!isFullscreen">
                            <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                            </svg>
                        </template>
                        <template x-if="isFullscreen">
                            <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
                            </svg>
                        </template>
                    </button>

                    <!-- Theme Toggle Button (Light / Dark Mode) -->
                    <button type="button" @click="toggleTheme()"
                        class="h-8.5 w-8.5 sm:h-9 sm:w-9 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-[0.97] text-white transition flex items-center justify-center shrink-0"
                        :title="isDarkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'">
                        <!-- Sun when Dark -->
                        <svg x-show="isDarkMode" class="w-4 h-4 text-[#FFD60A]" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <!-- Moon when Light -->
                        <svg x-show="!isDarkMode" class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <!-- Cashier Avatar Circle (Blue with Initial) -->
                    <div class="flex items-center pl-1 text-xs">
                        <div class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                            title="{{ $user->name }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- ===================================================== -->
        <!-- MOBILE HAMBURGER SLIDE-OVER DRAWER (APPLE HIG DESIGN) -->
        <!-- ===================================================== -->
        <div x-show="mobileMenuOpen" x-cloak
            class="fixed inset-0 z-[60] md:hidden"
            @keydown.escape.window="mobileMenuOpen = false">

            <!-- Backdrop Blur Overlay -->
            <div x-show="mobileMenuOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="mobileMenuOpen = false"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Slide-Over Drawer Panel -->
            <div x-show="mobileMenuOpen"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 w-full max-w-[320px] sm:max-w-xs bg-[#0B1528] text-white flex flex-col shadow-2xl border-l border-white/10 z-10 overflow-hidden">

                <!-- Drawer Header -->
                <div class="p-4 border-b border-white/10 flex items-center justify-between bg-black/20">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-[14px] text-white leading-tight">Menu Kasir POS</h3>
                            <p class="text-[10px] text-white/50">Navigasi & Operasional</p>
                        </div>
                    </div>
                    <button type="button" @click="mobileMenuOpen = false"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/15 text-white/70 hover:text-white flex items-center justify-center transition active:scale-95"
                        title="Tutup Menu">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Drawer Scrollable Content -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4" style="scrollbar-width: thin;">

                    <!-- 0. Connectivity Status Bento Card -->
                    <div class="p-3 rounded-[16px] bg-white/[0.04] border border-white/10 flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-white/50">Status Koneksi</span>
                        <span :class="isOnline ? 'bg-[#34C759]/20 border-[#34C759]/30 text-[#30D158]' : 'bg-[#FF3B30]/20 border-[#FF3B30]/30 text-[#FF453A]'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border text-[11px] font-bold">
                            <span :class="isOnline ? 'bg-[#34C759]' : 'bg-[#FF3B30]'" class="w-1.5 h-1.5 rounded-full animate-pulse"></span>
                            <span x-text="isOnline ? 'Online' : 'Offline'"></span>
                        </span>
                    </div>

                    <!-- 1. Shift Status Bento Card -->
                    <div class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-white/50">Status Shift</span>
                            <template x-if="activeShift">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#34C759]/20 border border-[#34C759]/30 text-[#30D158] text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                                    Aktif
                                </span>
                            </template>
                            <template x-if="!activeShift">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#FF9500]/20 border border-[#FF9500]/30 text-[#FF9F0A] text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                    Tutup
                                </span>
                            </template>
                        </div>

                        <template x-if="activeShift">
                            <div class="space-y-2">
                                <div class="text-[12px] text-white/70">
                                    Shift dibuka oleh <strong class="text-white">{{ $user->name }}</strong>
                                </div>
                                <button type="button" @click="mobileMenuOpen = false; openShiftCloseModal()"
                                    class="w-full h-9 rounded-[10px] bg-[#FF3B30] hover:bg-[#FF453A] text-white text-[12px] font-bold transition flex items-center justify-center gap-2 active:scale-95 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                    Tutup Shift Sekarang
                                </button>
                            </div>
                        </template>

                        <template x-if="!activeShift">
                            <div class="space-y-2">
                                <p class="text-[11px] text-white/60">Shift belum dibuka. Buka shift kasir untuk mulai transaksi.</p>
                                <button type="button" @click="mobileMenuOpen = false; showOpenShiftModal = true"
                                    class="w-full h-9 rounded-[10px] bg-[#34C759] hover:bg-[#30D158] text-black font-bold text-[12px] transition flex items-center justify-center gap-2 active:scale-95 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Buka Shift Kasir
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- 2. Outlet Selector (for multi-location) -->
                    <div class="p-3 rounded-[16px] bg-white/[0.04] border border-white/10 space-y-2">
                        <label class="text-[11px] font-semibold uppercase tracking-wider text-white/50 block">Lokasi Outlet</label>
                        <div class="relative">
                            <select x-model="selectedLocationId" @change="changeLocation()"
                                class="w-full h-10 rounded-[10px] bg-white/10 border border-white/15 px-3 pr-8 text-white text-[12px] font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" class="bg-[#0B1528] text-white" {{ $loc->id === $selectedLocationId ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- 3. Operational Navigation Menu Items -->
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-white/50 px-1 block">Fitur Kasir</span>

                        @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                        <!-- Auto Kirim KDS Quick Toggle (Paperless) -->
                        <button type="button" @click="toggleAutoKds()"
                            class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                                    :class="posAutoSendKds ? 'bg-[#34C759]/20 text-[#34C759]' : 'bg-white/10 text-white/50'">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693l-1.57-.393m15.6 0l1.4 3.5a2.25 2.25 0 01-2.09 3.085H5.89a2.25 2.25 0 01-2.09-3.085l1.4-3.5" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-[13px] text-white">Auto Kirim ke Dapur (KDS)</div>
                                    <div class="text-[11px] text-white/50" x-text="posAutoSendKds ? 'Paperless: Order otomatis ke layar dapur' : 'Manual / Tidak otomatis ke KDS'"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px]"
                                    :class="posAutoSendKds ? 'bg-[#34C759]/20 text-[#34C759]' : 'bg-white/10 text-white/50'"
                                    x-text="posAutoSendKds ? 'AKTIF' : 'NONAKTIF'"></span>
                            </div>
                        </button>
                        @endif

                        @if ($business && $business->hasDineInFeature())
                        <!-- Pesanan Meja QR -->
                        <button type="button" @click="mobileMenuOpen = false; openIncomingOrdersModal()"
                            class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-[13px] text-white">Pesanan Meja QR</div>
                                    <div class="text-[11px] text-white/50">Order langsung via QR resto</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span x-show="pendingQrCount > 0" x-text="pendingQrCount + ' baru'"
                                    class="px-2 py-0.5 rounded-full bg-[#007AFF] text-white font-bold text-[10px] animate-pulse"></span>
                                <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </div>
                        </button>

                        @if (\App\Support\Context::hasPermission('pos.tables'))
                            <!-- Resto Meja -->
                            <button type="button" @click="mobileMenuOpen = false; openTablesModal('tables')"
                                class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/20 text-[#34C759] flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[13px] text-white">Daftar Meja Resto</div>
                                        <div class="text-[11px] text-white/50">Status & sesi tagihan meja</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <template x-if="selectedTable">
                                        <span class="px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#34C759] font-bold text-[10px]" x-text="'M-' + selectedTable.table_number"></span>
                                    </template>
                                    <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Reservasi Storefront -->
                            <button type="button" @click="mobileMenuOpen = false; openTablesModal('reservations')"
                                class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-[10px] bg-[#5856D6]/20 text-[#5856D6] flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[13px] text-white">Buku Reservasi Storefront</div>
                                        <div class="text-[11px] text-white/50">Jadwal booking tamu hari ini</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span x-show="todayReservations.length > 0" x-text="todayReservations.length" class="px-2 py-0.5 rounded-full bg-[#5856D6] text-white font-bold text-[10px]"></span>
                                    <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </button>
                        @endif
                        @endif

                        <!-- Antrean Transaksi (Hold) -->
                        <button type="button" @click="mobileMenuOpen = false; showHeldOrdersModal = true"
                            class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-[13px] text-white">Antrean Hold</div>
                                    <div class="text-[11px] text-white/50">Pesanan yang ditahan sementara</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span x-show="heldOrders.length > 0" x-text="heldOrders.length + ' hold'"
                                    class="px-2 py-0.5 rounded-full bg-[#FF9500] text-black font-bold text-[10px]"></span>
                                <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </div>
                        </button>

                        @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                            <!-- Kas Masuk / Keluar -->
                            <button type="button" @click="mobileMenuOpen = false; showCashMovementModal = true"
                                class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-[10px] bg-white/10 text-white flex items-center justify-center shrink-0">
                                        <svg class="w-4.5 h-4.5 text-white/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[13px] text-white">Kas Masuk / Kas Keluar</div>
                                        <div class="text-[11px] text-white/50">Petty cash & mutasi kasir</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        @endif

                        <!-- Setting POS & Hardware Hub -->
                        <button type="button" @click="mobileMenuOpen = false; showPosSettingsModal = true"
                            class="w-full p-3 rounded-[12px] bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/10 text-left transition flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-white/10 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5 text-white/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-[13px] text-white">Setting POS & Hardware</div>
                                    <div class="text-[11px] text-white/50">Printer Thermal, Mode POS, Kitchen</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-white/40 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>

                        <!-- Mode Tampilan (Dark / Light) -->
                        <div class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-white/10 text-white flex items-center justify-center shrink-0">
                                    <!-- Sun when Dark -->
                                    <svg x-show="isDarkMode" class="w-4.5 h-4.5 text-[#FFD60A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    </svg>
                                    <!-- Moon when Light -->
                                    <svg x-show="!isDarkMode" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-[13px] text-white">Mode Tampilan</div>
                                    <div class="text-[11px] text-white/50" x-text="isDarkMode ? 'Tema Gelap (Dark)' : 'Tema Terang (Light)'"></div>
                                </div>
                            </div>
                            <button type="button" @click="toggleTheme()"
                                class="px-3 py-1.5 rounded-[8px] bg-white/10 hover:bg-white/20 active:scale-95 text-white text-[11px] font-bold transition">
                                Ganti
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer (Cashier profile & Return to Dashboard) -->
                <div class="p-4 border-t border-white/10 bg-black/20 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-[13px] text-white truncate">{{ $user->name }}</div>
                            <div class="text-[11px] text-white/50 truncate">Kasir Bertugas</div>
                        </div>
                    </div>

                    <a href="{{ route('dashboard') }}"
                        class="w-full h-10 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-98 border border-white/15 text-white text-[12px] font-semibold transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 2. MAIN WORKSPACE (CATALOG & CART)                    -->
        <!-- ===================================================== -->
        <div class="pos-main flex-1 flex overflow-hidden">

            <!-- Left Area: Catalog & Products Touch Grid -->
            <div class="pos-catalog flex-1 flex flex-col overflow-hidden p-2 sm:p-4 gap-2 sm:gap-3">

                @if ($business && $business->isFoodIndustry())
                <!-- 0. F&B MULTI-CHANNEL PRICE SELECTOR (APPLE HIG BENTO SEGMENTED) -->
                <div class="channel-bar flex items-center justify-between gap-2 overflow-x-auto pb-0.5 max-w-full scroll-smooth select-none shrink-0"
                    style="scrollbar-width: none; -ms-overflow-style: none;">
                    <div class="inline-flex items-center p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/10 gap-1 shrink-0">
                        <!-- 1. Dine In -->
                        <button type="button" @click="setSalesChannel('dine_in')"
                            :class="salesChannel === 'dine_in'
                                ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold'
                                : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                            class="h-8 sm:h-9 px-3 sm:px-3.5 rounded-[10px] text-[12px] sm:text-[13px] whitespace-nowrap transition-all flex items-center gap-1.5 active:scale-[0.97]"
                            title="Harga Dine-in / Reguler">
                            <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span>Dine In</span>
                        </button>

                        <!-- 2. Takeaway -->
                        <button type="button" @click="setSalesChannel('takeaway')"
                            :class="salesChannel === 'takeaway'
                                ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold'
                                : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                            class="h-8 sm:h-9 px-3 sm:px-3.5 rounded-[10px] text-[12px] sm:text-[13px] whitespace-nowrap transition-all flex items-center gap-1.5 active:scale-[0.97]"
                            title="Harga Takeaway / Bawa Pulang">
                            <svg class="w-3.5 h-3.5 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>Takeaway</span>
                        </button>

                        <!-- 3. GoFood -->
                        <button type="button" @click="setSalesChannel('gofood')"
                            :class="salesChannel === 'gofood'
                                ? 'bg-[#00AA13] text-white shadow-sm font-bold ring-2 ring-[#00AA13]/30'
                                : 'text-black/60 dark:text-white/60 hover:text-[#00AA13] font-medium'"
                            class="h-8 sm:h-9 px-3 sm:px-3.5 rounded-[10px] text-[12px] sm:text-[13px] whitespace-nowrap transition-all flex items-center gap-1.5 active:scale-[0.97]"
                            title="Harga Khusus GoFood">
                            <span class="w-2 h-2 rounded-full" :class="salesChannel === 'gofood' ? 'bg-white' : 'bg-[#00AA13]'"></span>
                            <span>GoFood</span>
                        </button>

                        <!-- 4. GrabFood -->
                        <button type="button" @click="setSalesChannel('grabfood')"
                            :class="salesChannel === 'grabfood'
                                ? 'bg-[#00B14F] text-white shadow-sm font-bold ring-2 ring-[#00B14F]/30'
                                : 'text-black/60 dark:text-white/60 hover:text-[#00B14F] font-medium'"
                            class="h-8 sm:h-9 px-3 sm:px-3.5 rounded-[10px] text-[12px] sm:text-[13px] whitespace-nowrap transition-all flex items-center gap-1.5 active:scale-[0.97]"
                            title="Harga Khusus GrabFood">
                            <span class="w-2 h-2 rounded-full" :class="salesChannel === 'grabfood' ? 'bg-white' : 'bg-[#00B14F]'"></span>
                            <span>GrabFood</span>
                        </button>

                        <!-- 5. ShopeeFood -->
                        <button type="button" @click="setSalesChannel('shopeefood')"
                            :class="salesChannel === 'shopeefood'
                                ? 'bg-[#EE4D2D] text-white shadow-sm font-bold ring-2 ring-[#EE4D2D]/30'
                                : 'text-black/60 dark:text-white/60 hover:text-[#EE4D2D] font-medium'"
                            class="h-8 sm:h-9 px-3 sm:px-3.5 rounded-[10px] text-[12px] sm:text-[13px] whitespace-nowrap transition-all flex items-center gap-1.5 active:scale-[0.97]"
                            title="Harga Khusus ShopeeFood">
                            <span class="w-2 h-2 rounded-full" :class="salesChannel === 'shopeefood' ? 'bg-white' : 'bg-[#EE4D2D]'"></span>
                            <span>ShopeeFood</span>
                        </button>
                    </div>

                    <!-- External Order Reference (ID Order App) -->
                    <div x-show="['gofood', 'grabfood', 'shopeefood'].includes(salesChannel)"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="flex items-center gap-1.5 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[12px] px-2.5 py-1 shadow-xs shrink-0">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-black/50 dark:text-white/50"
                            x-text="salesChannel.toUpperCase() + ' REF:'"></span>
                        <input type="text" x-model="externalOrderRef"
                            :placeholder="'No. Order / ID ' + (salesChannel === 'gofood' ? 'GoFood' : (salesChannel === 'grabfood' ? 'GrabFood' : 'ShopeeFood'))"
                            class="bg-transparent border-0 p-0 text-[12px] font-semibold focus:ring-0 text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 w-32 sm:w-44">
                    </div>
                </div>
                @endif

                <!-- 1. DESKTOP TOP HORIZONTAL CATEGORY STRIP (ENLARGED) -->
                <div class="category-bar hidden md:flex items-center gap-2.5 overflow-x-auto pb-1.5 max-w-full scroll-smooth select-none shrink-0"
                    style="scrollbar-width: none; -ms-overflow-style: none;">
                    
                    <!-- 1. Semua Item (Active Blue when default) -->
                    <button type="button" @click="selectedCategory = 'all'; selectedTypeFilter = 'all'; filterProducts()"
                        :class="(selectedCategory === 'all' && selectedTypeFilter === 'all')
                            ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/25'
                            : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] shadow-[0_1px_2px_rgba(0,0,0,0.03)] font-semibold'"
                        class="h-12 px-5 rounded-[14px] text-[14px] whitespace-nowrap transition-all shrink-0 flex items-center gap-2.5 active:scale-[0.97]">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        <span>Semua Item</span>
                    </button>

                    <!-- 2. Produk Fisik -->
                    <button type="button" @click="selectedCategory = 'all'; selectedTypeFilter = 'goods'; filterProducts()"
                        :class="(selectedCategory === 'all' && selectedTypeFilter === 'goods')
                            ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/25'
                            : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] shadow-[0_1px_2px_rgba(0,0,0,0.03)] font-semibold'"
                        class="h-12 px-5 rounded-[14px] text-[14px] whitespace-nowrap transition-all shrink-0 flex items-center gap-2.5 active:scale-[0.97]">
                        <svg class="w-5 h-5 shrink-0 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                        <span>Produk Fisik</span>
                    </button>

                    <!-- 3. Jasa / Layanan -->
                    <button type="button" @click="selectedCategory = 'all'; selectedTypeFilter = 'service'; filterProducts()"
                        :class="(selectedCategory === 'all' && selectedTypeFilter === 'service')
                            ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/25'
                            : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] shadow-[0_1px_2px_rgba(0,0,0,0.03)] font-semibold'"
                        class="h-12 px-5 rounded-[14px] text-[14px] whitespace-nowrap transition-all shrink-0 flex items-center gap-2.5 active:scale-[0.97]">
                        <svg class="w-5 h-5 shrink-0 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.398-3.03 1.164l-4.5 4.5a2.25 2.25 0 01-3.182-3.182l4.5-4.5c.766-.766 1.255-1.954 1.164-3.03A4.5 4.5 0 0117.25 2.25a.75.75 0 01.53 1.28l-1.97 1.97a.75.75 0 001.06 1.06l1.97-1.97a.75.75 0 011.28.53z" />
                        </svg>
                        <span>Jasa / Layanan</span>
                    </button>

                    <!-- 4. Dynamic Business Categories -->
                    @foreach ($categories as $cat)
                        <button type="button"
                            @click="selectedCategory = '{{ $cat->id }}'; selectedTypeFilter = 'all'; filterProducts()"
                            :class="(selectedCategory === '{{ $cat->id }}')
                                ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/25'
                                : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] shadow-[0_1px_2px_rgba(0,0,0,0.03)] font-semibold'"
                            class="h-12 px-5 rounded-[14px] text-[14px] whitespace-nowrap transition-all shrink-0 flex items-center gap-2.5 active:scale-[0.97]">
                            @if(stripos($cat->name, 'Jasa') !== false || stripos($cat->name, 'Layanan') !== false || stripos($cat->name, 'Konsultasi') !== false)
                                <svg class="w-5 h-5 shrink-0 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            @elseif(stripos($cat->name, 'Pelengkap') !== false || stripos($cat->name, 'Tambahan') !== false)
                                <svg class="w-5 h-5 shrink-0 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif(stripos($cat->name, 'Utama') !== false || stripos($cat->name, 'Menu') !== false)
                                <svg class="w-5 h-5 shrink-0 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 shrink-0 text-black/50 dark:text-white/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                            @endif
                            <span>{{ $cat->name }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- 2. MAIN CATALOG WORKSPACE (SPLIT 1/3 SIDEBAR + 2/3 MENU ON MOBILE, FULL WIDTH ON DESKTOP) -->
                <div class="pos-catalog-body flex-1 flex overflow-hidden gap-2 sm:gap-3.5 min-w-0">

                    <!-- Mobile Left Category Sidebar (1/3 Width of Layout) -->
                    <div class="md:hidden w-[82px] sm:w-[94px] flex flex-col shrink-0 overflow-y-auto pr-1 pb-28 space-y-1.5 select-none border-r border-black/[0.06] dark:border-white/10"
                        style="scrollbar-width: none; -ms-overflow-style: none;">
                        
                        <!-- 1. Semua Item (Mobile) -->
                        <button type="button" @click="selectedCategory = 'all'; selectedTypeFilter = 'all'; filterProducts()"
                            :class="(selectedCategory === 'all' && selectedTypeFilter === 'all')
                                ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/30'
                                : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] font-semibold'"
                            class="w-full py-2 px-1 rounded-[12px] flex flex-col items-center justify-center gap-1 transition-all active:scale-95 text-center min-h-[58px] shadow-[0_1px_2px_rgba(0,0,0,0.03)] overflow-hidden shrink-0">
                            <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span class="text-[10.5px] font-semibold leading-tight text-center max-w-full px-0.5 break-words line-clamp-2"
                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">Semua</span>
                        </button>

                        <!-- 2. Produk Fisik (Mobile) -->
                        <button type="button" @click="selectedCategory = 'all'; selectedTypeFilter = 'goods'; filterProducts()"
                            :class="(selectedCategory === 'all' && selectedTypeFilter === 'goods')
                                ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/30'
                                : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] font-semibold'"
                            class="w-full py-2 px-1 rounded-[12px] flex flex-col items-center justify-center gap-1 transition-all active:scale-95 text-center min-h-[58px] shadow-[0_1px_2px_rgba(0,0,0,0.03)] overflow-hidden shrink-0">
                            <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                :class="(selectedCategory === 'all' && selectedTypeFilter === 'goods') ? 'text-white' : 'text-black/50 dark:text-white/50'"
                                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            <span class="text-[10.5px] font-semibold leading-tight text-center max-w-full px-0.5 break-words line-clamp-2"
                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">Fisik</span>
                        </button>

                        <!-- 3. Jasa / Layanan (Mobile) -->
                        <button type="button" @click="selectedCategory = 'all'; selectedTypeFilter = 'service'; filterProducts()"
                            :class="(selectedCategory === 'all' && selectedTypeFilter === 'service')
                                ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/30'
                                : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] font-semibold'"
                            class="w-full py-2 px-1 rounded-[12px] flex flex-col items-center justify-center gap-1 transition-all active:scale-95 text-center min-h-[58px] shadow-[0_1px_2px_rgba(0,0,0,0.03)] overflow-hidden shrink-0">
                            <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                :class="(selectedCategory === 'all' && selectedTypeFilter === 'service') ? 'text-white' : 'text-black/50 dark:text-white/50'"
                                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.398-3.03 1.164l-4.5 4.5a2.25 2.25 0 01-3.182-3.182l4.5-4.5c.766-.766 1.255-1.954 1.164-3.03A4.5 4.5 0 0117.25 2.25a.75.75 0 01.53 1.28l-1.97 1.97a.75.75 0 001.06 1.06l1.97-1.97a.75.75 0 011.28.53z" />
                            </svg>
                            <span class="text-[10.5px] font-semibold leading-tight text-center max-w-full px-0.5 break-words line-clamp-2"
                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">Jasa</span>
                        </button>

                        <!-- 4. Dynamic Business Categories (Mobile) -->
                        @foreach ($categories as $cat)
                            <button type="button"
                                @click="selectedCategory = '{{ $cat->id }}'; selectedTypeFilter = 'all'; filterProducts()"
                                :class="(selectedCategory === '{{ $cat->id }}')
                                    ? 'bg-[#007AFF] text-white shadow-sm font-bold ring-2 ring-[#007AFF]/30'
                                    : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.02] dark:hover:bg-white/[0.05] font-semibold'"
                                class="w-full py-2 px-1 rounded-[12px] flex flex-col items-center justify-center gap-1 transition-all active:scale-95 text-center min-h-[58px] shadow-[0_1px_2px_rgba(0,0,0,0.03)] overflow-hidden shrink-0">
                                @if(stripos($cat->name, 'Jasa') !== false || stripos($cat->name, 'Layanan') !== false || stripos($cat->name, 'Konsultasi') !== false)
                                    <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        :class="selectedCategory === '{{ $cat->id }}' ? 'text-white' : 'text-black/50 dark:text-white/50'"
                                        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                @elseif(stripos($cat->name, 'Pelengkap') !== false || stripos($cat->name, 'Tambahan') !== false)
                                    <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        :class="selectedCategory === '{{ $cat->id }}' ? 'text-white' : 'text-black/50 dark:text-white/50'"
                                        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif(stripos($cat->name, 'Utama') !== false || stripos($cat->name, 'Menu') !== false)
                                    <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        :class="selectedCategory === '{{ $cat->id }}' ? 'text-white' : 'text-black/50 dark:text-white/50'"
                                        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        :class="selectedCategory === '{{ $cat->id }}' ? 'text-white' : 'text-black/50 dark:text-white/50'"
                                        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                    </svg>
                                @endif
                                <span class="text-[10.5px] font-semibold leading-tight text-center max-w-full px-0.5 break-words line-clamp-2"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $cat->name }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- 3. PRODUCTS & MENU AREA (2/3 Width on Mobile, Full Width on Desktop) -->
                    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
                        <!-- Products Touch Cards Grid -->
                        <div class="pos-products flex-1 overflow-y-auto pr-1 pb-16 md:pb-2">
                            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3.5">
                                <template x-for="product in paginatedProducts" :key="product.id">
                                    <div @click="handleProductClick(product)"
                                        class="rounded-[14px] sm:rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-md active:scale-[0.98] transition-all p-2.5 sm:p-3 cursor-pointer flex flex-col justify-between group select-none min-w-0">
                                        <div>
                                            @if ($posShowProductImages)
                                                <!-- Thumbnail Image with 16/10 Aspect Ratio -->
                                                <div
                                                    class="relative w-full aspect-[16/10] rounded-[10px] sm:rounded-[12px] bg-black/[0.03] dark:bg-black/40 border border-black/[0.04] dark:border-white/5 mb-2 overflow-hidden items-center justify-center flex shrink-0">
                                                    <template x-if="product.image_url">
                                                        <img :src="product.image_url" :alt="product.name" loading="lazy"
                                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                                            x-on:error="product.image_url = null">
                                                    </template>
                                                    <template x-if="!product.image_url">
                                                        <div
                                                            class="w-full h-full flex flex-col items-center justify-center text-black/20 dark:text-white/20">
                                                            <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor"
                                                                stroke-width="1.5" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                            </svg>
                                                        </div>
                                                    </template>

                                                    <!-- Stock Badge Pill (Top-Right) -->
                                                    <template x-if="product.type === 'service'">
                                                        <span
                                                            class="absolute top-1.5 right-1.5 text-[9px] sm:text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#AF52DE] text-white shadow-xs">
                                                            Jasa
                                                        </span>
                                                    </template>
                                                    <template x-if="product.type !== 'service'">
                                                        <span
                                                            class="absolute top-1.5 right-1.5 text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-full bg-white/95 dark:bg-black/90 shadow-xs tabular-nums"
                                                            :class="product.current_stock > 0 ?
                                                                'text-[#34C759] border border-[#34C759]' :
                                                                'text-[#FF3B30] border border-[#FF3B30]'"
                                                            x-text="'Stok: ' + (product.current_stock ?? 0)">
                                                        </span>
                                                    </template>
                                                </div>
                                            @endif

                                            <!-- Product Name (2 lines clamped) -->
                                            <h4 class="font-bold text-[12px] sm:text-[14px] text-black dark:text-white line-clamp-2 leading-snug group-hover:text-[#007AFF] transition-colors"
                                                x-text="product.name"></h4>
                                            
                                            <!-- SKU / Code & Badges -->
                                            <div class="flex items-center gap-1 flex-wrap mt-0.5 sm:mt-1">
                                                <span class="text-[10px] sm:text-[11px] font-medium text-black/45 dark:text-white/45 tabular-nums truncate"
                                                    x-text="product.code || '-'"></span>
                                                <template x-if="product.type === 'service'">
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[8.5px] sm:text-[9px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                                        Jasa
                                                    </span>
                                                </template>
                                                <template x-if="product.modifier_groups && product.modifier_groups.length > 0">
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[8.5px] sm:text-[9px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                                        + Varian
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Price and Add Button -->
                                        <div class="mt-2 sm:mt-3 pt-2 sm:pt-2.5 flex items-center justify-between gap-1">
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-bold text-[12.5px] sm:text-[15px] text-black dark:text-white tabular-nums truncate"
                                                    x-text="formatRupiah(getProductPrice(product))"></span>
                                                @if ($business && $business->isFoodIndustry())
                                                <span x-show="product.channel_prices && salesChannel !== 'dine_in' && Number(product.channel_prices[salesChannel]) !== Number(product.selling_price)"
                                                    class="text-[9.5px] font-black uppercase tracking-wider rounded px-1 w-max"
                                                    :class="salesChannel === 'gofood' ? 'bg-[#00AA13]/15 text-[#00AA13]' : (salesChannel === 'grabfood' ? 'bg-[#00B14F]/15 text-[#00B14F]' : (salesChannel === 'shopeefood' ? 'bg-[#EE4D2D]/15 text-[#EE4D2D]' : 'bg-[#FF9500]/15 text-[#FF9500]'))"
                                                    x-text="salesChannel"></span>
                                                @endif
                                            </div>
                                            <button type="button" @click.stop="handleProductClick(product)"
                                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#007AFF] hover:bg-[#0062CC] text-white flex items-center justify-center font-bold text-sm sm:text-base shadow-sm shrink-0 active:scale-90 transition-all"
                                                title="Tambah ke Keranjang">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty Catalog State -->
                            <div x-show="filteredProducts.length === 0"
                                class="h-64 flex flex-col items-center justify-center text-center text-black/40 dark:text-white/40">
                                <svg class="w-12 h-12 text-black/20 dark:text-white/20 mb-2" fill="none"
                                    stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <div class="text-[13px] sm:text-[14px] font-semibold text-black/60 dark:text-white/60">Tidak ada produk ditemukan</div>
                                <div class="text-[11px] sm:text-[12px] text-black/40 dark:text-white/40 mt-0.5">Coba ubah kata kunci pencarian atau kategori filter</div>
                            </div>
                        </div>

                        <!-- Catalog Bottom Footer (Total Produk & Pagination) -->
                        <div class="mt-auto pt-2 sm:pt-2.5 border-t border-black/[0.06] dark:border-white/10 flex items-center justify-between shrink-0 gap-2">
                            <div class="flex items-center gap-1.5 text-[11px] sm:text-xs text-black/60 dark:text-white/60 truncate">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-black/40 dark:text-white/40 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span class="truncate">Total <strong class="text-black dark:text-white font-bold" x-text="filteredProducts.length"></strong> item</span>
                            </div>

                            <div class="flex items-center gap-1 select-none shrink-0" x-show="filteredProducts.length > 0">
                                <button type="button" @click="prevPage()" :disabled="currentPage <= 1"
                                    class="w-6.5 h-6.5 sm:w-8 sm:h-8 rounded-[8px] border border-black/10 dark:border-white/15 bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/80 hover:bg-black/[0.03] dark:hover:bg-white/[0.05] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition text-xs font-semibold">
                                    ‹
                                </button>
                                <template x-for="p in totalPages" :key="p">
                                    <button type="button" @click="goToPage(p)"
                                        :class="currentPage === p ? 'bg-[#007AFF] text-white shadow-xs font-bold' : 'border border-black/10 dark:border-white/15 bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/80 hover:bg-black/[0.03]'"
                                        class="w-6.5 h-6.5 sm:w-8 sm:h-8 rounded-[8px] flex items-center justify-center transition text-xs font-semibold"
                                        x-text="p">
                                    </button>
                                </template>
                                <button type="button" @click="nextPage()" :disabled="currentPage >= totalPages"
                                    class="w-6.5 h-6.5 sm:w-8 sm:h-8 rounded-[8px] border border-black/10 dark:border-white/15 bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/80 hover:bg-black/[0.03] dark:hover:bg-white/[0.05] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition text-xs font-semibold">
                                    ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Area: Cart & Checkout Inspector (Permanent side-by-side on desktop, slide-up sheet on mobile) -->
            <div class="pos-cart backdrop-blur-xl bg-white dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/10"
                :class="mobileCartOpen ? 'pos-cart-open' : ''">

                <!-- Grabber Handle (iOS 18 Bottom Sheet) -->
                <div class="pos-cart-btn-close lg:hidden flex items-center justify-center pt-2.5 pb-1 cursor-pointer"
                    @click="mobileCartOpen = false">
                    <div class="w-10 h-1 rounded-full bg-black/20 dark:bg-white/20"></div>
                </div>

                <!-- Cart Header (Exact 1:1 Reference Layout) -->
                <div class="p-3.5 sm:p-4 border-b border-black/[0.06] dark:border-white/10 space-y-2.5 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-[15px] text-black dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#007AFF] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>Keranjang Kasir</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/80 font-bold text-xs tabular-nums"
                                x-text="cart.length + ' item'">0 item</span>
                            <button type="button" @click="clearCart()"
                                class="text-[12px] text-[#FF3B30] hover:text-[#D70015] flex items-center gap-1 font-semibold transition"
                                title="Reset Keranjang">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                                <span>Reset</span>
                            </button>
                            <button @click="closeMobileCart()"
                                class="pos-cart-btn-close lg:hidden p-1 rounded-md text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white"
                                title="Tutup keranjang">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Single Inline Row: Customer Combobox, [+], [Bungkus], [Dine In v] (Exact Reference Layout) -->
                    <div class="flex items-center gap-1.5">
                        <!-- Searchable Customer Combobox -->
                        <div class="relative flex-1 min-w-0" @click.outside="customerDropdownOpen = false">
                            <button type="button"
                                @click="customerDropdownOpen = !customerDropdownOpen; if(customerDropdownOpen) $nextTick(() => $refs.customerSearchInput?.focus())"
                                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 rounded-[10px] pl-7.5 pr-7 text-[12px] text-left text-black dark:text-white flex items-center justify-between transition focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 cursor-pointer">
                                <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 pointer-events-none shrink-0"
                                    fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                <span class="truncate font-medium pr-1"
                                    x-text="activeCustomer ? activeCustomer.name : 'Pelanggan Umum (Guest)'"></span>
                                <svg class="w-3 h-3 text-black/40 dark:text-white/40 shrink-0 transition-transform duration-200"
                                    :class="customerDropdownOpen ? 'rotate-180' : ''" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Clear Customer selection button (Reset to Guest) -->
                            <button x-show="activeCustomer" @click.stop="selectCustomer(null)" type="button"
                                class="absolute right-6 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-black/10 dark:bg-white/20 text-black/60 dark:text-white/60 hover:bg-black/20 dark:hover:bg-white/30 flex items-center justify-center transition"
                                title="Reset ke Pelanggan Umum">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Searchable Popover Dropdown Panel -->
                            <div x-show="customerDropdownOpen" x-cloak
                                @keydown.escape.window="customerDropdownOpen = false"
                                class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] p-2 shadow-[0_12px_36px_rgba(0,0,0,0.25)] text-black dark:text-white min-w-[260px] max-w-[340px]">

                                <!-- Search Input with icon and clear -->
                                <div class="relative mb-1.5">
                                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 pointer-events-none shrink-0"
                                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <input type="text" x-ref="customerSearchInput" x-model="customerSearchQuery"
                                        placeholder="Cari nama / nomor HP..."
                                        class="w-full h-8.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] pl-8 pr-7 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                    <button x-show="customerSearchQuery"
                                        @click="customerSearchQuery = ''; $refs.customerSearchInput?.focus()"
                                        type="button"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white p-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Scrollable Customers Results List -->
                                <div class="max-h-52 overflow-y-auto space-y-0.5 overscroll-contain pr-0.5">
                                    <!-- Pelanggan Umum (Guest) Option -->
                                    <div @click="selectCustomer(null)"
                                        :class="!selectedCustomerId ? 'bg-[#007AFF] text-white font-medium' :
                                            'hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black dark:text-white'"
                                        class="flex items-center justify-between px-2.5 py-1.5 rounded-[8px] cursor-pointer text-xs transition">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0"
                                                :class="!selectedCustomerId ? 'bg-white/20 text-white' :
                                                    'bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                            </div>
                                            <span class="truncate">Pelanggan Umum (Guest)</span>
                                        </div>
                                        <svg x-show="!selectedCustomerId" class="w-3.5 h-3.5 shrink-0" fill="none"
                                            stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>

                                    <!-- Filtered Customers -->
                                    <template x-for="cust in filteredCustomers" :key="cust.id">
                                        <div @click="selectCustomer(cust)"
                                            :class="selectedCustomerId === cust.id ? 'bg-[#007AFF] text-white' :
                                                'hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black dark:text-white'"
                                            class="flex items-center justify-between px-2.5 py-1.5 rounded-[8px] cursor-pointer text-xs transition">
                                            <div class="min-w-0 pr-1.5">
                                                <div class="font-semibold truncate flex items-center gap-1.5">
                                                    <span x-text="cust.name"></span>
                                                    <span x-show="cust.membership_tier"
                                                        :class="selectedCustomerId === cust.id ? 'bg-white/25 text-white' :
                                                            'bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2]'"
                                                        class="px-1.5 py-0.2 rounded-full text-[9px] uppercase font-bold"
                                                        x-text="cust.membership_tier"></span>
                                                </div>
                                                <div class="text-[11px] opacity-70 flex items-center gap-2 mt-0.5">
                                                    <span x-show="cust.phone" x-text="cust.phone"></span>
                                                    <span x-text="Number(cust.points_balance || 0) + ' Poin'"></span>
                                                </div>
                                            </div>
                                            <svg x-show="selectedCustomerId === cust.id" class="w-3.5 h-3.5 shrink-0"
                                                fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </div>
                                    </template>

                                    <!-- Empty State -->
                                    <div x-show="filteredCustomers.length === 0"
                                        class="py-3 text-center text-xs text-black/45 dark:text-white/45">
                                        <p>Pelanggan tidak ditemukan.</p>
                                    </div>
                                </div>

                                <!-- Dropdown Footer -->
                                <div
                                    class="pt-2 mt-1 border-t border-black/10 dark:border-white/10 flex items-center justify-between text-[11px]">
                                    <span class="text-black/40 dark:text-white/40"
                                        x-text="filteredCustomers.length + ' data'"></span>
                                    <button type="button" @click="customerDropdownOpen = false; openCustomerModal()"
                                        class="text-[#007AFF] font-semibold hover:underline flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>Pelanggan Baru</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Add Customer Button -->
                        <button type="button" @click="openCustomerModal()"
                            class="shrink-0 h-9 w-9 rounded-[10px] bg-[#007AFF] text-white hover:bg-[#0062CC] transition flex items-center justify-center active:scale-[0.97] shadow-xs"
                            title="Tambah Pelanggan Baru" aria-label="Tambah Pelanggan Baru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>

                        @if ($business && $business->isFoodIndustry())
                        <!-- Bungkus Button -->
                        <button type="button" @click="setSalesChannel('takeaway'); detachTableFromCart()"
                            :class="salesChannel === 'takeaway' ? 'bg-[#007AFF] text-white shadow-xs font-semibold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.07] dark:hover:bg-white/[0.1]'"
                            class="shrink-0 h-9 px-3 rounded-[10px] border border-black/5 dark:border-white/10 text-[12px] transition flex items-center gap-1.5 active:scale-[0.97]"
                            title="Bungkus / Takeaway">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>Bungkus</span>
                        </button>

                        @if ($business->hasDineInFeature())
                        <!-- Dine In Dropdown / Toggle Button -->
                        <div class="relative shrink-0" x-data="{ dineDropdown: false }" @click.outside="dineDropdown = false">
                            <button type="button" @click="setSalesChannel('dine_in'); dineDropdown = !dineDropdown"
                                :class="salesChannel === 'dine_in' ? 'bg-[#007AFF] text-white shadow-xs font-semibold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.07] dark:hover:bg-white/[0.1]'"
                                class="h-9 px-3 rounded-[10px] border border-black/5 dark:border-white/10 text-[12px] transition flex items-center gap-1.5 active:scale-[0.97]"
                                title="Makan di Tempat / Dine In">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />
                                </svg>
                                <span x-text="selectedTable ? ('Meja ' + selectedTable.table_number) : 'Dine In'"></span>
                                <svg class="w-3 h-3 transition-transform" :class="dineDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="dineDropdown" x-cloak
                                class="absolute right-0 top-full mt-1.5 z-40 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[12px] p-1.5 shadow-[0_10px_30px_rgba(0,0,0,0.25)] text-black dark:text-white min-w-[160px]">
                                <button type="button" @click="orderType = 'dine_in'; dineDropdown = false; openTablesModal()"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[8px] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-xs font-medium flex items-center justify-between transition">
                                    <span>Pilih / Ganti Meja</span>
                                    <span class="text-[10px] text-[#007AFF] font-bold">F&B</span>
                                </button>
                                <button type="button" @click="orderType = 'delivery'; dineDropdown = false"
                                    class="w-full text-left px-2.5 py-1.5 rounded-[8px] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-xs font-medium flex items-center justify-between transition">
                                    <span>Kirim (Delivery)</span>
                                    <span class="text-[10px] opacity-60">Kurir</span>
                                </button>
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>

                    @if ($business && $business->hasDineInFeature())
                    <!-- Active Restaurant Table Card (Apple HIG Styled) -->
                    <div x-show="selectedTable"
                        class="p-2.5 rounded-[12px] bg-[#007AFF]/[0.08] dark:bg-[#007AFF]/15 border border-[#007AFF]/25 space-y-1.5 transition">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div
                                    class="w-7 h-7 rounded-[8px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                             d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-[13px] text-black dark:text-white truncate"
                                            x-text="'Meja ' + (selectedTable ? selectedTable.table_number : '') + (selectedTable && selectedTable.name ? ' • ' + selectedTable.name : '')"></span>
                                        <span x-show="activeTableOrderId"
                                            class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] uppercase">QR
                                            Aktif</span>
                                    </div>
                                    <div class="text-[11px] text-black/60 dark:text-white/60 truncate">
                                        <span x-show="activeTableCustomerName"
                                            x-text="activeTableCustomerName + ' • '"></span>
                                        <span x-show="activeTableOrderNumber" class="font-mono text-[#007AFF]"
                                            x-text="'#' + activeTableOrderNumber"></span>
                                        <span x-show="!activeTableOrderId" class="italic">Meja Dine In Baru</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" @click="refreshTableCart()" x-show="activeTableOrderId"
                                    class="p-1 rounded-[6px] hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60 hover:text-[#007AFF] transition"
                                    title="Segarkan pesanan meja">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </button>
                                <button type="button" @click="openTablesModal()"
                                    class="px-2 py-0.5 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-[11px] font-semibold text-black/70 dark:text-white/80 transition">
                                    Ganti
                                </button>
                                <button type="button" @click="detachTableFromCart()"
                                    class="p-1 rounded-[6px] text-black/40 hover:text-[#FF3B30] transition"
                                    title="Lepas meja dari keranjang">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if ($business && $business->isFoodIndustry())
                    <!-- Online Delivery Channel Indicator in Cart -->
                    <div x-show="['gofood', 'grabfood', 'shopeefood'].includes(salesChannel)"
                        class="p-2.5 rounded-[12px] text-white flex items-center justify-between shadow-xs transition"
                        :class="salesChannel === 'gofood' ? 'bg-[#00AA13]' : (salesChannel === 'grabfood' ? 'bg-[#00B14F]' : 'bg-[#EE4D2D]')">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse shrink-0"></span>
                            <div class="min-w-0">
                                <div class="text-[12px] font-bold uppercase tracking-wider truncate" x-text="salesChannel"></div>
                                <div x-show="externalOrderRef" class="text-[11px] font-mono opacity-90 truncate" x-text="'Ref: #' + externalOrderRef"></div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-black/20 px-2 py-0.5 rounded-[6px] shrink-0">Harga Khusus Online</span>
                    </div>
                    @endif

                    <!-- Member Loyalty Card Preview -->
                    <div x-show="activeCustomer"
                        class="p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <span x-text="activeCustomer ? activeCustomer.name : ''"></span>
                                <span
                                    class="px-1.5 py-0.2 rounded-full bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2] font-semibold uppercase text-[9px]"
                                    x-text="activeCustomer ? activeCustomer.membership_tier : ''"></span>
                            </div>
                            <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] mt-0.5">
                                Saldo: <span class="font-semibold tabular-nums"
                                    x-text="activeCustomer ? activeCustomer.points_balance : 0"></span> Poin
                            </div>
                        </div>
                        <template x-if="activeCustomer && activeCustomer.points_balance > 0">
                            <button @click="togglePointsRedemption()"
                                :class="redeemPoints ? 'bg-[#007AFF] text-white' :
                                    'bg-black/[0.06] dark:bg-white/[0.08] text-[#007AFF] border border-[#007AFF]/30'"
                                class="px-2.5 py-1 rounded-[7px] text-[10px] font-semibold transition active:scale-[0.97]">
                                <span x-text="redeemPoints ? 'Batalkan Poin' : 'Tukar Poin'"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Industry Vertical: Dynamic Service Data Trigger (20 Sector Adaptive) -->
                    @php
                        $isWorkshop = $business && $business->isWorkshop() && ! $business->isLaundry() && ! $business->isPharmacy() && ! $business->isFoodIndustry();
                        $isLaundry = $business && $business->isLaundry();
                        $isPharmacy = $business && $business->isPharmacy();
                        $isFnB = $business && $business->isFoodIndustry();
                        $isRetail = ! $isWorkshop && ! $isLaundry && ! $isPharmacy && ! $isFnB;
                    @endphp

                    @if ($isWorkshop)
                        <!-- Industry Vertical: SPK Bengkel & Otomotif -->
                        <div class="pt-1">
                            <button type="button" @click="serviceVerticalTab = 'workshop'; showServiceVerticalModal = true"
                                class="w-full flex items-center justify-between p-2 rounded-[10px] text-[12px] font-medium transition active:scale-[0.98] border"
                                :class="vehicleLicensePlate ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'bg-black/[0.03] dark:bg-white/[0.05] border-black/[0.05] dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 shrink-0 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                                    </svg>
                                    <span class="truncate font-medium" x-text="vehicleLicensePlate ? ('SPK: ' + vehicleLicensePlate + (vehicleModel ? ' • ' + vehicleModel : '')) : 'SPK Bengkel (Plat & Mekanik)'"></span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <span x-show="vehicleLicensePlate" class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#007AFF] text-white">Terisi</span>
                                    <svg class="w-3.5 h-3.5 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                    @elseif ($isLaundry)
                        <!-- Industry Vertical: Laundry & Timbangan Cucian -->
                        <div class="pt-1">
                            <button type="button" @click="serviceVerticalTab = 'laundry'; showServiceVerticalModal = true"
                                class="w-full flex items-center justify-between p-2 rounded-[10px] text-[12px] font-medium transition active:scale-[0.98] border"
                                :class="laundryWeightKg ? 'bg-[#5856D6]/10 border-[#5856D6]/30 text-[#5856D6]' : 'bg-black/[0.03] dark:bg-white/[0.05] border-black/[0.05] dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 shrink-0 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    <span class="truncate font-medium" x-text="laundryWeightKg ? ('Laundry: ' + laundryWeightKg + ' kg' + (rackLocation ? ' • Rak ' + rackLocation : '')) : 'Timbangan Cucian & Lokasi Rak'"></span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <span x-show="laundryWeightKg" class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#5856D6] text-white">Terisi</span>
                                    <svg class="w-3.5 h-3.5 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                    @elseif ($isPharmacy)
                        <!-- Industry Vertical: Apotek & Farmasi Batch/Dosage Quick Info -->
                        <div class="pt-1">
                            <div class="w-full flex items-center justify-between p-2 rounded-[10px] text-[11px] font-medium bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.942A4.5 4.5 0 0115.89 17H8.11a4.5 4.5 0 01-2.34-.658L4.2 15.3m15.6 0a2.25 2.25 0 00.2-.958V8.25a2.25 2.25 0 00-2.25-2.25H6.25A2.25 2.25 0 004 8.25v6.092c0 .332.072.658.2.958" />
                                    </svg>
                                    <span class="truncate font-semibold">Mode Apotek (Batch &amp; ED Obat Aktif)</span>
                                </div>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]">Farmasi</span>
                            </div>
                        </div>
                    @elseif (!$isFnB && !$isRetail)
                        <!-- Industry Vertical: Multi-Layanan Umum -->
                        <div class="pt-1">
                            <button type="button" @click="showServiceVerticalModal = true"
                                class="w-full flex items-center justify-between p-2 rounded-[10px] text-[12px] font-medium transition active:scale-[0.98] border"
                                :class="(vehicleLicensePlate || laundryWeightKg) ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'bg-black/[0.03] dark:bg-white/[0.05] border-black/[0.05] dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 shrink-0 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                                    </svg>
                                    <span class="truncate font-medium" x-text="vehicleLicensePlate ? ('Bengkel: ' + vehicleLicensePlate + (vehicleModel ? ' • ' + vehicleModel : '')) : (laundryWeightKg ? ('Laundry: ' + laundryWeightKg + ' kg' + (rackLocation ? ' • ' + rackLocation : '')) : 'Layanan Khusus (Bengkel / Laundry)')"></span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <span x-show="vehicleLicensePlate || laundryWeightKg" class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#007AFF] text-white">Terisi</span>
                                    <svg class="w-3.5 h-3.5 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                    @else
                        <!-- Fallback when data is already populated in F&B/Retail -->
                        <div class="pt-1" x-show="vehicleLicensePlate || laundryWeightKg" x-cloak>
                            <button type="button" @click="showServiceVerticalModal = true"
                                class="w-full flex items-center justify-between p-2 rounded-[10px] text-[12px] font-medium bg-[#007AFF]/10 border border-[#007AFF]/30 text-[#007AFF] transition active:scale-[0.98]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 shrink-0 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                                    </svg>
                                    <span class="truncate font-medium" x-text="vehicleLicensePlate ? ('Bengkel: ' + vehicleLicensePlate) : ('Laundry: ' + laundryWeightKg + ' kg')"></span>
                                </div>
                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-[#007AFF] text-white">Terisi</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Cart Items Scrollable List -->
                <div class="flex-1 overflow-y-auto p-3.5 sm:p-4 space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div
                            class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/5 hover:border-black/10 dark:hover:border-white/10 transition flex flex-col gap-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-[13px] text-black dark:text-white leading-tight truncate"
                                        x-text="item.product_name"></div>
                                    <template x-if="item.modifiers_summary">
                                        <div class="text-[11px] text-[#007AFF] font-medium leading-tight mt-0.5"
                                            x-text="item.modifiers_summary"></div>
                                    </template>
                                    <template x-if="item.notes">
                                        <div class="inline-block text-[10px] text-[#FF9500] font-medium bg-[#FF9500]/10 border border-[#FF9500]/20 px-1.5 py-0.5 rounded mt-0.5"
                                            x-text="'Catatan: ' + item.notes"></div>
                                    </template>
                                    <!-- Pharmacy / Apotek Badges -->
                                    <div x-show="item.batch_number || item.expired_date || item.dosage_instructions" class="flex flex-wrap gap-1 mt-0.5">
                                        <span x-show="item.batch_number" class="text-[10px] font-mono bg-[#007AFF]/10 text-[#007AFF] px-1.5 py-0.2 rounded" x-text="'Batch: ' + item.batch_number"></span>
                                        <span x-show="item.expired_date" class="text-[10px] font-mono bg-[#FF9500]/10 text-[#FF9500] px-1.5 py-0.2 rounded" x-text="'ED: ' + item.expired_date"></span>
                                        <span x-show="item.dosage_instructions" class="text-[10px] bg-[#AF52DE]/10 text-[#AF52DE] px-1.5 py-0.2 rounded" x-text="'Dosis: ' + item.dosage_instructions"></span>
                                    </div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums mt-0.5">
                                        <span x-text="formatRupiah(item.unit_price)"></span>
                                        <template x-if="item.unit_symbol">
                                            <span class="text-black/40 dark:text-white/40"
                                                x-text="'/' + item.unit_symbol"></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="font-bold text-[13px] text-black dark:text-white tabular-nums"
                                    x-text="formatRupiah((parseFloat(item.quantity) || 0) * item.unit_price)"></div>
                            </div>
                            <div
                                class="flex items-center justify-between pt-1 border-t border-black/[0.04] dark:border-white/5">
                                <div
                                    class="flex items-center gap-1 bg-black/[0.04] dark:bg-black/50 rounded-[8px] p-0.5 border border-black/[0.06] dark:border-white/10 focus-within:border-[#007AFF]/80 transition">
                                    <button type="button" @click="decrementQty(index)"
                                        class="w-6 h-6 rounded-[6px] bg-white dark:bg-white/[0.08] hover:bg-black/[0.05] dark:hover:bg-white/[0.14] text-black dark:text-white flex items-center justify-center font-bold text-xs shrink-0 active:scale-[0.95]"
                                        title="Kurangi 1">-</button>
                                    <div class="flex items-center">
                                        <input type="number" step="any" min="0.0001" :value="item.quantity"
                                            @input="updateItemQty(index, $event.target.value)"
                                            @blur="normalizeItemQty(index)" @click.stop
                                            @focus="$event.target.select()"
                                            class="w-14 bg-transparent border-none text-center font-semibold tabular-nums text-[12px] text-black dark:text-white focus:outline-none py-0.5 px-0.5"
                                            placeholder="Qty">
                                        <template x-if="item.unit_symbol">
                                            <span
                                                class="text-[10px] text-black/40 dark:text-white/40 font-medium pr-1 select-none"
                                                x-text="item.unit_symbol"></span>
                                        </template>
                                    </div>
                                    <button type="button" @click="incrementQty(index)"
                                        class="w-6 h-6 rounded-[6px] bg-white dark:bg-white/[0.08] hover:bg-black/[0.05] dark:hover:bg-white/[0.14] text-black dark:text-white flex items-center justify-center font-bold text-xs shrink-0 active:scale-[0.95]"
                                        title="Tambah 1">+</button>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="openItemDetailModal(index)"
                                        class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-[#007AFF] hover:bg-black/5 dark:hover:bg-white/10 transition"
                                        title="Catatan, Nomor Batch & Dosis Obat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="removeFromCart(index)"
                                        class="p-1 rounded-[6px] text-black/30 dark:text-white/30 hover:text-[#FF3B30] hover:bg-black/5 dark:hover:bg-white/10 transition"
                                        title="Hapus dari keranjang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Cart State (Exact to screenshot) -->
                    <div x-show="cart.length === 0"
                        class="h-full flex flex-col items-center justify-center text-center text-black/30 dark:text-white/30 py-10">
                        <div class="w-14 h-14 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-black/30 dark:text-white/30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <div class="text-[13.5px] font-semibold text-black/60 dark:text-white/60">Keranjang Kosong</div>
                        <div class="text-[11.5px] text-black/40 dark:text-white/40 mt-1">Pilih produk di katalog atau scan barcode</div>
                    </div>
                </div>

                <!-- Sticky Cart Footer (Exact 1:1 Reference Layout) -->
                <div
                    class="p-3.5 sm:p-4 border-t border-black/[0.06] dark:border-white/10 bg-white dark:bg-[#1C1C1E] space-y-2 shrink-0">
                    <div class="space-y-1.5 text-xs">
                        <!-- Subtotal -->
                        <div class="flex justify-between text-black/60 dark:text-white/60 font-medium">
                            <span>Subtotal</span>
                            <span class="tabular-nums font-semibold text-black dark:text-white"
                                x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <!-- Order Discount Row with Terapkan button -->
                        <div class="flex items-center gap-1.5 pt-0.5">
                            <div class="relative shrink-0">
                                <select x-model="discountType"
                                    class="h-8 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[8px] pl-2 pr-6 text-[11px] font-semibold text-black dark:text-white focus:outline-none appearance-none cursor-pointer">
                                    <option value="fixed" class="bg-white dark:bg-[#1C1C1E]">Diskon Rp</option>
                                    <option value="percentage" class="bg-white dark:bg-[#1C1C1E]">Diskon %</option>
                                </select>
                                <svg class="w-3 h-3 text-black/40 dark:text-white/40 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                            <input type="number" x-model.number="discountValue" min="0"
                                :max="discountType === 'percentage' ? 100 : subtotal" step="any"
                                placeholder="0"
                                class="h-8 min-w-0 flex-1 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[8px] px-2.5 text-[12px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                            <button type="button" @click="$refs.barcodeInput?.focus()"
                                class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-[11px] font-semibold transition active:scale-[0.97]">
                                Terapkan
                            </button>
                        </div>
                        <div x-show="orderDiscountAmount > 0" class="flex justify-between text-[#FF3B30] text-[11px]">
                            <span>Diskon Transaksi</span>
                            <span class="tabular-nums font-semibold"
                                x-text="'-' + formatRupiah(orderDiscountAmount)"></span>
                        </div>

                        <!-- Voucher Code Row with ticket icon and Terapkan button -->
                        <div class="flex items-center gap-1.5 pt-0.5">
                            <div class="relative flex-1">
                                <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                <input type="text" x-model="voucherCode" placeholder="KODE VOUCHER..."
                                    class="h-8 w-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[8px] pl-8 pr-2.5 text-[11px] text-black dark:text-white uppercase tracking-wider focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                            </div>
                            <button @click="applyVoucher()" type="button"
                                class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-[11px] font-semibold transition active:scale-[0.97]">
                                Terapkan
                            </button>
                        </div>

                        <div x-show="voucherDiscount > 0" class="flex justify-between text-[#34C759] text-[11px]">
                            <span>Diskon Voucher</span>
                            <span class="tabular-nums font-semibold"
                                x-text="'-' + formatRupiah(voucherDiscount)"></span>
                        </div>

                        <div x-show="pointsDiscount > 0" class="flex justify-between text-[#30B0C7] text-[11px]">
                            <span>Tukar Poin</span>
                            <span class="tabular-nums font-semibold" x-text="'-' + formatRupiah(pointsDiscount)"></span>
                        </div>

                        <div x-show="taxAmount > 0" class="flex justify-between text-black/50 dark:text-white/50 text-[11px]">
                            <span>PPN ({{ $business->pos_tax_percent }}%)</span>
                            <span class="tabular-nums font-semibold text-black/80 dark:text-white/80"
                                x-text="formatRupiah(taxAmount)"></span>
                        </div>

                        <div x-show="serviceChargeAmount > 0"
                            class="flex justify-between text-black/50 dark:text-white/50 text-[11px]">
                            <span>Service ({{ $business->pos_service_charge_percent }}%)</span>
                            <span class="tabular-nums font-semibold text-black/80 dark:text-white/80"
                                x-text="formatRupiah(serviceChargeAmount)"></span>
                        </div>
                    </div>

                    <!-- Grand Total Line (Exact to screenshot) -->
                    <div
                        class="pt-2.5 border-t border-black/[0.06] dark:border-white/10 flex items-baseline justify-between">
                        <div>
                            <div class="text-[12px] font-bold text-black/70 dark:text-white/70">Total Tagihan</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums"
                                x-text="cart.length + ' item'"></div>
                        </div>
                        <div class="text-[24px] sm:text-[26px] font-black text-black dark:text-white tabular-nums tracking-tight"
                            x-text="formatRupiah(grandTotal)"></div>
                    </div>

                    @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                    <!-- Kirim ke Dapur (KDS Paperless Button) -->
                    <div class="pt-2">
                        <button type="button" @click="sendCartToKitchen()" :disabled="cart.length === 0 || isSendingToKitchen"
                            class="w-full h-10 rounded-[12px] bg-[#34C759]/15 hover:bg-[#34C759]/25 active:scale-[0.98] text-[#248A3D] dark:text-[#30D158] font-bold text-[12.5px] border border-[#34C759]/30 flex items-center justify-center gap-2 transition disabled:opacity-35 disabled:cursor-not-allowed shadow-xs"
                            title="Kirim pesanan ke antrean layar dapur (KDS) tanpa perlu cetak struk kertas">
                            <template x-if="!isSendingToKitchen">
                                <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693l-1.57-.393m15.6 0l1.4 3.5a2.25 2.25 0 01-2.09 3.085H5.89a2.25 2.25 0 01-2.09-3.085l1.4-3.5" />
                                </svg>
                            </template>
                            <template x-if="isSendingToKitchen">
                                <svg class="w-4 h-4 shrink-0 animate-spin text-[#34C759]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span>Kirim ke Dapur (KDS &bull; Tanpa Cetak)</span>
                        </button>
                    </div>
                    @endif

                    <!-- Action Buttons: Hold & Bayar (Exact to screenshot) -->
                    <div class="grid grid-cols-3 gap-2 pt-1.5">
                        <button @click="promptHoldCart()" :disabled="cart.length === 0"
                            class="col-span-1 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] active:scale-[0.97] text-black/80 dark:text-white/90 font-semibold text-[13px] flex items-center justify-center gap-1.5 transition disabled:opacity-35 disabled:cursor-not-allowed border border-black/[0.06] dark:border-white/[0.08]"
                            title="Tahan transaksi keranjang">
                            <svg class="w-4 h-4 text-[#FF9500] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Hold</span>
                        </button>
                        <button @click="openPaymentModal()" :disabled="cart.length === 0"
                            class="col-span-2 h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] text-white font-bold text-[14px] sm:text-[15px] shadow-[0_4px_16px_rgba(0,122,255,0.3)] flex items-center justify-between px-3.5 sm:px-4 transition disabled:opacity-35 disabled:cursor-not-allowed overflow-hidden">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 shrink-0" width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <rect width="20" height="14" x="2" y="5" rx="2" />
                                    <line x1="2" x2="22" y1="10" y2="10" />
                                </svg>
                                <span>Bayar</span>
                                <span class="hidden sm:inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-white/20 uppercase tracking-wider">F9</span>
                            </div>
                            <span class="font-black tabular-nums text-[15px] sm:text-[16px]" x-text="formatRupiah(grandTotal)"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Cart Backdrop -->
    <div x-show="mobileCartOpen" x-cloak class="pos-cart-backdrop" @click="closeMobileCart()"></div>

    <!-- Mobile Fixed Cart Bottom Bar (iOS 18 Floating Inset) -->
    <div class="pos-cart-bar items-center gap-3 backdrop-blur-2xl bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.08] dark:border-white/12 rounded-[20px] px-4 py-3 shadow-[0_12px_36px_rgba(0,0,0,0.18)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.6)]"
        x-cloak>
        <button @click="toggleMobileCart()"
            class="flex items-center gap-3 flex-1 min-w-0 text-left active:scale-[0.98] transition" type="button">
            <span class="relative shrink-0">
                <div
                    class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                </div>
                <span x-show="cart.length > 0" x-text="cart.length"
                    class="absolute -top-1.5 -right-1.5 min-w-[1.25rem] h-[1.25rem] px-1 rounded-full bg-[#FF9500] text-white font-bold text-[11px] flex items-center justify-center tabular-nums shadow-sm border-2 border-white dark:border-[#1C1C1E]"></span>
            </span>
            <span class="flex-1 min-w-0">
                <span
                    class="block text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider leading-none mb-1">{{ __('pos.cart_short') }}</span>
                <span
                    class="block font-extrabold text-black dark:text-white tabular-nums text-[16px] sm:text-[17px] leading-none truncate"
                    x-text="formatRupiah(grandTotal)"></span>
            </span>
        </button>
        <button @click="openPaymentModal()" :disabled="cart.length === 0" type="button"
            class="h-12 px-6 sm:px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.96] text-white font-bold text-[15px] shadow-[0_4px_16px_rgba(0,122,255,0.35)] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition shrink-0">
            <span>{{ __('pos.pay') }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>

    <!-- ===================================================== -->
    <!-- 3. MODAL: QUICK CUSTOMER (Apple Sheet Presentation)   -->
    <!-- ===================================================== -->
    <div x-show="showCustomerModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showCustomerModal = false">
        <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
            @click.outside="showCustomerModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <div>
                    <h3 class="font-semibold text-[16px] text-black dark:text-white">{{ __('pos.quick_customer_title') }}</h3>
                    <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">{{ __('pos.quick_customer_desc') }}</p>
                </div>
                <button type="button" @click="showCustomerModal = false"
                    class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="createQuickCustomer" class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.customer_name') }} *</label>
                    <input x-ref="quickCustomerName" type="text" x-model="newCustomer.name" required
                        maxlength="255" placeholder="{{ __('pos.customer_name_placeholder') }}"
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.customer_phone') }} *</label>
                    <input type="tel" x-model="newCustomer.phone" required maxlength="50" inputmode="tel"
                        placeholder="{{ __('pos.customer_phone_placeholder') }}"
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <p x-show="customerFormError" x-text="customerFormError" class="text-xs text-[#FF3B30]"></p>
                <div class="flex justify-end gap-2 pt-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showCustomerModal = false"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">{{ __('pos.cancel') }}</button>
                    <button type="submit" :disabled="isCreatingCustomer"
                        class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] disabled:opacity-50 transition active:scale-[0.97]">
                        <span x-text="isCreatingCustomer ? (window.COOCA_I18N?.saving || '{{ __('pos.saving') }}') : (window.COOCA_I18N?.save_customer || '{{ __('pos.save_customer') }}')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL: PAYMENT MODAL (Apple Sheet Presentation)    -->
    <!-- ===================================================== -->
    <div x-show="showPaymentModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[4px] p-3 sm:p-4"
        style="display: none;"
        @keydown.escape.window="if(!isProcessing) showPaymentModal = false"
        @keydown.enter.prevent="if(!isProcessing && (isSplitPayment ? splitRemainingAmount === 0 : currentTenderAmount >= grandTotal)) submitCheckout()">
        <div
            class="pos-modal-panel w-full max-w-3xl lg:max-w-4xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 flex flex-col max-h-[92vh] overflow-y-auto space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white"
            @click.outside="if(!isProcessing) showPaymentModal = false">
            
            {{-- Modal Header & Mode Switcher --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-[17px] text-black dark:text-white leading-tight">Pembayaran Kasir POS</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Pilih metode pembayaran tunggal atau kombinasi (split)</p>
                    </div>
                </div>

                {{-- Mode Switcher (Apple Segmented Tab) --}}
                <div class="flex items-center p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/10 text-xs font-medium self-start sm:self-auto">
                    <button type="button" @click="isSplitPayment = false"
                        :class="!isSplitPayment ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        <span>Satu Metode</span>
                    </button>
                    <button type="button" @click="initSplitPayment()"
                        :class="isSplitPayment ? 'bg-[#007AFF] text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Split Payment (Majemuk)</span>
                    </button>
                    <button type="button" @click="showPaymentModal = false"
                        class="ml-2 w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/50 dark:text-white/50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- 1. MODE STANDAR (SINGLE PAYMENT) --}}
            <div x-show="!isSplitPayment" class="space-y-4">
                <!-- Tagihan Summary Box -->
                <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Total Tagihan</div>
                        <div class="text-[24px] sm:text-[28px] font-extrabold text-[#007AFF] tabular-nums tracking-tight"
                            x-text="formatRupiah(grandTotal)"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Uang Diterima</div>
                        <div class="text-[20px] font-bold text-black dark:text-white tabular-nums"
                            x-text="formatRupiah(currentTenderAmount)"></div>
                    </div>
                </div>

                <!-- Payment Methods Grid -->
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-2">Pilih Kanal Pembayaran</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                        <button type="button" @click="selectedPayMethod = 'cash'"
                            :class="selectedPayMethod === 'cash' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                            <span>Tunai (Cash)</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'qris'; currentTenderAmount = grandTotal;"
                            :class="selectedPayMethod === 'qris' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                            <span>QRIS Cooca Pay</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'edc_debit'"
                            :class="selectedPayMethod === 'edc_debit' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                            <span>EDC Debit</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'edc_credit'"
                            :class="selectedPayMethod === 'edc_credit' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                            <span>EDC Kredit</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'transfer'"
                            :class="selectedPayMethod === 'transfer' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            <span>Transfer Bank</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'customer_credit'"
                            :class="selectedPayMethod === 'customer_credit' ? 'bg-[#007AFF] text-white ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/80 dark:text-white/80 hover:bg-black/[0.06] dark:hover:bg-white/[0.08]'"
                            class="p-3 rounded-[14px] flex flex-col items-center justify-center gap-1.5 text-xs font-semibold transition active:scale-[0.97] border border-black/5 dark:border-white/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Kasbon / Piutang</span>
                        </button>
                    </div>
                </div>

                {{-- Opsi Mesin EDC & Nomor Approval jika metode EDC dipilih --}}
                <div x-show="selectedPayMethod === 'edc_debit' || selectedPayMethod === 'edc_credit'" class="p-3.5 rounded-[14px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">Pilih Mesin EDC Bank *</label>
                        <select x-model="selectedEdcTerminalId"
                            class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Pilih Mesin EDC --</option>
                            <template x-for="t in storeEdcTerminals" :key="t.id">
                                <option :value="t.id" x-text="t.terminal_name + ' (' + t.bank_name + ' - TID: ' + t.terminal_id_tid + ')'"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">No. Approval / Ref EDC (Opsional)</label>
                        <input type="text" x-model="paymentRefNumber" placeholder="Contoh: APPR-992019"
                            class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <!-- Input Nominal Tender -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70">Nominal Uang Bayar (Rp)</label>
                        <span x-show="selectedPayMethod === 'qris'" class="text-[10px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full border border-[#007AFF]/20">
                            QRIS TriPay Otomatis Pas
                        </span>
                    </div>
                    <input type="number" x-model.number="currentTenderAmount"
                        class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[20px] font-extrabold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">

                    <!-- QRIS Dynamic Info Alert -->
                    <div x-show="selectedPayMethod === 'qris'" class="mt-2 p-2.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 flex items-center gap-2 text-xs text-[#007AFF]">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                        <span>Kode QRIS dinamis akan dibuat otomatis dari gateway TriPay sesuai total tagihan saat tombol ditekan.</span>
                    </div>

                    <!-- Quick Cash Presets (only for Cash) -->
                    <div x-show="selectedPayMethod === 'cash'" class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
                        <button type="button" @click="currentTenderAmount = grandTotal"
                            class="h-9 rounded-[10px] bg-[#007AFF]/12 hover:bg-[#007AFF]/20 text-xs font-bold text-[#007AFF] tabular-nums transition active:scale-[0.97]">Uang Pas</button>
                        <button type="button" @click="currentTenderAmount = 50000"
                            class="h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">50.000</button>
                        <button type="button" @click="currentTenderAmount = 100000"
                            class="h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">100.000</button>
                        <button type="button" @click="currentTenderAmount = 200000"
                            class="h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">200.000</button>
                    </div>
                </div>

                <!-- Kembalian Live Indicator -->
                <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-xs font-medium text-black/60 dark:text-white/60">Kembalian:</span>
                    <span class="text-[20px] font-extrabold text-[#34C759] tabular-nums"
                        x-text="formatRupiah(Math.max(0, currentTenderAmount - grandTotal))"></span>
                </div>
            </div>

            {{-- 2. MODE SPLIT PAYMENT (MULTI-METODE) --}}
            <div x-show="isSplitPayment" class="space-y-4">
                {{-- Financial Balance Status Bar --}}
                <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-black/45 dark:text-white/45 block">Total Tagihan</span>
                        <span class="text-base font-extrabold text-black dark:text-white tabular-nums block mt-0.5" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-[#007AFF] block">Total Teralokasi</span>
                        <span class="text-base font-extrabold text-[#007AFF] tabular-nums block mt-0.5" x-text="formatRupiah(totalSplitPaid)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-black/45 dark:text-white/45 block">Sisa Kurang</span>
                        <span class="text-base font-extrabold tabular-nums block mt-0.5" :class="splitRemainingAmount > 0 ? 'text-[#FF3B30]' : 'text-black/40 dark:text-white/40'" x-text="formatRupiah(splitRemainingAmount)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-[#34C759] block">Kembalian</span>
                        <span class="text-base font-extrabold text-[#34C759] tabular-nums block mt-0.5" x-text="formatRupiah(splitChangeAmount)"></span>
                    </div>
                </div>

                {{-- Quick Bagi Rata (Split Evenly) Presets --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <span class="text-[11px] font-bold text-black/50 dark:text-white/50 shrink-0 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-8-6h16" /></svg>
                        <span>Bagi Rata:</span>
                    </span>
                    <button type="button" @click="splitEvenly(2)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">2 Orang</button>
                    <button type="button" @click="splitEvenly(3)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">3 Orang</button>
                    <button type="button" @click="splitEvenly(4)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">4 Orang</button>
                    <button type="button" @click="splitEvenly(5)" class="px-2.5 py-1 rounded-[8px] bg-black/5 dark:bg-white/10 hover:bg-[#007AFF] hover:text-white text-xs font-semibold text-black dark:text-white transition">5 Orang</button>
                </div>

                {{-- Dynamic Split Rows Container --}}
                <div class="space-y-2.5 max-h-[42vh] overflow-y-auto pr-1">
                    <template x-for="(row, idx) in splitPaymentRows" :key="idx">
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                                    <span>Metode Pembayaran #<span x-text="idx + 1"></span></span>
                                </span>
                                <button type="button" @click="removeSplitRow(idx)" x-show="splitPaymentRows.length > 1"
                                    class="w-6 h-6 rounded-full hover:bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                {{-- Dropdown Metode --}}
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] font-medium text-black/50 dark:text-white/50 mb-1">Kanal Bayar *</label>
                                    <select x-model="row.payment_method"
                                        class="w-full h-10 px-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                        <option value="cash">Tunai (Cash)</option>
                                        <option value="qris">QRIS Cooca Pay</option>
                                        <option value="edc_debit">EDC Debit</option>
                                        <option value="edc_credit">EDC Kredit</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="customer_credit">Kasbon / Piutang</option>
                                    </select>
                                </div>

                                {{-- Input Nominal & Auto Fill Sisa --}}
                                <div class="sm:col-span-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[10px] font-medium text-black/50 dark:text-white/50">Nominal (Rp) *</label>
                                        <button type="button" @click="fillRemainingSplit(idx)" x-show="splitRemainingAmount > 0"
                                            class="text-[10px] font-bold text-[#007AFF] hover:underline">
                                            + Sisa (<span x-text="formatRupiah(splitRemainingAmount)"></span>)
                                        </button>
                                    </div>
                                    <input type="number" min="0" x-model.number="row.amount" @input="sanitizeSplitAmount(idx)"
                                        class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-bold tabular-nums text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                </div>

                                {{-- Detail EDC / Ref --}}
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] font-medium text-black/50 dark:text-white/50 mb-1">
                                        <span x-text="row.payment_method.startsWith('edc') ? 'Mesin EDC / Approval' : 'No. Ref / Catatan'"></span>
                                    </label>
                                    <template x-if="row.payment_method.startsWith('edc') && storeEdcTerminals.length > 0">
                                        <select x-model="row.store_edc_terminal_id"
                                            class="w-full h-10 px-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                            <option value="">-- Pilih EDC --</option>
                                            <template x-for="t in storeEdcTerminals" :key="t.id">
                                                <option :value="t.id" x-text="t.terminal_name + ' (' + t.terminal_id_tid + ')'"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!row.payment_method.startsWith('edc') || storeEdcTerminals.length === 0">
                                        <input type="text" x-model="row.reference_number" placeholder="No. ref / approval..."
                                            class="w-full h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Button Tambah Baris Split --}}
                <button type="button" @click="addSplitRow()" :disabled="splitPaymentRows.length >= 5"
                    class="w-full h-10 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('pos.add_another_payment_method') }}</span>
                </button>
            </div>

            {{-- Modal Actions Footer --}}
            <div class="pt-3 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-3">
                <div class="text-xs">
                    <template x-if="isSplitPayment && splitRemainingAmount > 0">
                        <span class="text-[#FF3B30] font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                            <span>Alokasi kurang <strong x-text="formatRupiah(splitRemainingAmount)"></strong></span>
                        </span>
                    </template>
                    <template x-if="isSplitPayment && splitRemainingAmount === 0">
                        <span class="text-[#34C759] font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>Total Pembayaran Pas</span>
                        </span>
                    </template>
                    <template x-if="!isSplitPayment && currentTenderAmount >= grandTotal">
                        <span class="text-[#34C759] font-bold">Siap diproses</span>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showPaymentModal = false" :disabled="isProcessing"
                        class="h-10 px-4 rounded-[12px] text-xs font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition disabled:opacity-40">
                        {{ __('pos.cancel') }}
                    </button>
                    <button type="button" @click="submitCheckout()"
                        :disabled="isProcessing || (isSplitPayment ? splitRemainingAmount > 0 : currentTenderAmount < grandTotal)"
                        class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-bold text-xs shadow-sm disabled:opacity-35 transition flex items-center gap-2">
                        <span x-show="!isProcessing" x-text="isSplitPayment ? (window.COOCA_I18N?.finish_split_payment || '{{ __('pos.finish_split_payment') }}') : (selectedPayMethod === 'qris' ? 'Buat QRIS Dinamis (TriPay)' : (window.COOCA_I18N?.finish_transaction || '{{ __('pos.finish_transaction') }}'))"></span>
                        <span x-show="isProcessing" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memproses...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4.1. MODAL: DYNAMIC QRIS COOCA PAY (TriPay Gateway)   -->
    <!-- ===================================================== -->
    <div x-show="showQrisModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-3 sm:p-4"
        style="display: none;">
        <div class="pos-modal-panel w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[26px] border border-black/10 dark:border-white/15 p-5 sm:p-6 flex flex-col space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden transition-all"
            @click.outside="/* Terkunci: modal tidak dapat ditutup dengan klik luar hingga lunas atau klik Ganti Metode Bayar */">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-[15px] sm:text-base text-black dark:text-white tracking-tight leading-tight">QRIS Dinamis Kasir</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 truncate" x-text="activeQrisOrder ? ('Order #' + activeQrisOrder.order_number) : 'Memuat data order...'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1">
                        <svg class="w-3 h-3 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <span>Terkunci</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                        TriPay QRIS
                    </span>
                </div>
            </div>

            <!-- Amount Card -->
            <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-black/40 dark:text-white/40 block">Total Tagihan</span>
                    <span class="text-xs font-semibold text-black/70 dark:text-white/70">Scan lewat e-Wallet & m-Banking</span>
                </div>
                <span class="font-black text-[20px] sm:text-[22px] text-[#007AFF] tabular-nums"
                    x-text="activeQrisOrder ? formatRupiah(activeQrisOrder.total_amount) : ''"></span>
            </div>

            <!-- QR Code Card -->
            <div class="p-4 rounded-[20px] bg-white border border-black/10 shadow-inner flex flex-col items-center justify-center relative">
                <template x-if="activeQrisPayment && activeQrisPayment.qr_url">
                    <img :src="activeQrisPayment.qr_url" alt="QRIS Code" class="w-56 h-56 sm:w-60 sm:h-60 object-contain rounded-xl select-none">
                </template>
                <template x-if="!activeQrisPayment || !activeQrisPayment.qr_url">
                    <div class="w-56 h-56 flex flex-col items-center justify-center text-gray-400 gap-2">
                        <svg class="animate-spin h-7 w-7 text-[#007AFF]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-medium text-gray-500">Menyiapkan QR Code...</span>
                    </div>
                </template>
                <div class="mt-2.5 text-[10.5px] font-bold text-gray-600 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                    <span>Menunggu Pembayaran Pelanggan...</span>
                </div>
            </div>

            <!-- Countdown Timer & Auto Polling Notice -->
            <div class="space-y-1 text-center text-xs">
                <div class="flex items-center justify-center gap-1.5 text-black/70 dark:text-white/70 font-semibold">
                    <span>Masa Berlaku QR:</span>
                    <span class="font-mono text-[#FF9500] font-extrabold tabular-nums text-sm" x-text="qrisCountdownFormatted">15:00</span>
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45">
                    Sistem mendeteksi transaksi secara realtime (setiap 3 detik). Struk akan terbuka otomatis setelah dibayar.
                </p>
            </div>

            <!-- Actions Grid -->
            <div class="pt-2 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="checkQrisStatusManual()" :disabled="isCheckingQrisStatus"
                        class="h-10 px-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                        <svg x-show="!isCheckingQrisStatus" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        <svg x-show="isCheckingQrisStatus" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isCheckingQrisStatus ? 'Mengecek...' : 'Cek Status Sekarang'"></span>
                    </button>
                    <button type="button" @click="printQrisSlip()"
                        class="h-10 px-3 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] text-black dark:text-white font-bold text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.13-1.27-3.118m0 0A10.978 10.978 0 0112 7.5c2.47 0 4.757.818 6.55 2.211m-6.55 7.789a10.978 10.978 0 01-6.55-2.211M12 21a9 9 0 100-18 9 9 0 000 18z" /></svg>
                        <span>Cetak Slip QR</span>
                    </button>
                </div>

                @if(!config('tripay.is_production', false))
                <button type="button" @click="simulateQrisPayment()" :disabled="isSimulatingQris"
                    class="w-full h-9 rounded-[10px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759] font-bold text-xs transition flex items-center justify-center gap-1.5 disabled:opacity-40 border border-[#34C759]/25">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span x-text="isSimulatingQris ? 'Memproses Simulasi...' : '[Sandbox] Simulasi Pelanggan Bayar Berhasil'"></span>
                </button>
                @endif

                <button type="button" @click="cancelQrisPayment()" :disabled="isCancellingQris"
                    class="w-full h-10 rounded-[12px] text-xs font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition disabled:opacity-40 flex items-center justify-center gap-1.5 border border-[#FF3B30]/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                    <span x-text="isCancellingQris ? 'Membatalkan sesi QRIS...' : 'Ganti Metode Bayar'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL: PAYMENT SUCCESS & RECEIPT (Bento UI Grid)  -->
    <!-- ===================================================== -->
    <div x-show="showSuccessModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[3px] p-4"
        @keydown.escape.window="if(showSuccessModal) resetForNewOrder()">
        <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden"
            @click.outside="resetForNewOrder()">

            <!-- Top Header Row -->
            <div
                class="flex items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-11 h-11 rounded-2xl bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/25 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-[17px] text-black dark:text-white tracking-tight leading-snug">
                            Transaksi Berhasil!</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span
                                class="font-mono text-[11px] font-bold px-1.5 py-0.2 rounded bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70"
                                x-text="lastCompletedOrder ? ('#' + lastCompletedOrder.order_number) : ''"></span>
                            <span class="text-[11px] font-medium text-[#34C759]">Lunas</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="resetForNewOrder()"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition shrink-0"
                    title="Tutup (Esc)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Financial Summary Bento Strip -->
            <div
                class="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center">
                <div class="px-2 py-1">
                    <span
                        class="text-[10px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45 block">Total
                        Belanja</span>
                    <span
                        class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tabular-nums block mt-0.5 truncate"
                        x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.total_amount : 0)"></span>
                </div>
                <div class="px-2 py-1 border-x border-black/5 dark:border-white/5">
                    <span
                        class="text-[10px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45 block">Total
                        Bayar</span>
                    <span
                        class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tabular-nums block mt-0.5 truncate"
                        x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.paid_amount : 0)"></span>
                </div>
                <div class="px-2 py-1">
                    <span
                        class="text-[10px] uppercase tracking-wider font-semibold text-[#34C759] block">Kembalian</span>
                    <span
                        class="text-[13px] sm:text-[14px] font-extrabold text-[#34C759] tabular-nums block mt-0.5 truncate"
                        x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.change_amount : 0)"></span>
                </div>
            </div>

            <!-- Paperless Auto KDS Confirmation Badge -->
            <div x-show="lastOrderSentToKds" x-cloak
                class="py-2.5 px-3.5 rounded-xl bg-[#34C759]/12 border border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="truncate">Pesanan Otomatis Masuk ke Layar Dapur (KDS) &bull; Paperless</span>
                </div>
                <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] shrink-0 ml-2">KDS Live</span>
            </div>

            <!-- WhatsApp Feedback Toast (If Triggered) -->
            <div x-show="waBotFeedback" x-cloak
                class="text-xs font-semibold py-2 px-3 rounded-xl border flex items-center justify-between gap-2"
                :class="waBotFeedbackSuccess ? 'bg-[#34C759]/12 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' :
                    'bg-[#FF3B30]/12 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A]'">
                <div class="flex items-center gap-2">
                    <svg x-show="waBotFeedbackSuccess" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg x-show="!waBotFeedbackSuccess" class="w-4 h-4 shrink-0" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span x-text="waBotFeedback"></span>
                </div>
                <button type="button" @click="waBotFeedback = ''"
                    class="w-6 h-6 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- BENTO ACTIONS GRID -->
            <div class="space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40 px-0.5">
                    Aksi Tagihan &amp; Struk
                </div>

                <!-- Primary Row: Thermal Hardware ESC/POS & WhatsApp Bot -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">

                    <!-- Bento Tile 1: Cetak Struk ESC/POS Hardware (Hero Green/Blue) -->
                    <button type="button" @click.stop="!directPrinting && directPrintReceipt(lastCompletedOrder.id)"
                        :disabled="directPrinting"
                        class="group rounded-2xl p-4 bg-gradient-to-br from-[#34C759] to-[#248A3D] hover:from-[#30D158] hover:to-[#227D37] active:scale-[0.98] text-white flex flex-col justify-between shadow-md shadow-[#34C759]/20 transition relative overflow-hidden min-h-[120px] text-left disabled:opacity-60">
                        <!-- Top Row: Icon + Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                </svg>
                            </div>
                            <span
                                class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/20 text-white backdrop-blur-xs"
                                x-text="directPrinting ? 'Mencetak...' : 'Hardware ESC/POS'">
                            </span>
                        </div>
                        <!-- Bottom Info & CTA -->
                        <div class="mt-3">
                            <div class="font-extrabold text-[14px] leading-tight">Cetak Struk ESC/POS</div>
                            <div class="text-[11px] text-white/90 mt-0.5 flex items-center justify-between">
                                <span>Kirim ke printer thermal</span>
                                <span class="font-bold group-hover:translate-x-0.5 transition" x-text="directPrinting ? 'Mencetak...' : 'Cetak'"></span>
                            </div>
                        </div>
                    </button>

                    <!-- Bento Tile 2: WhatsApp Bot Otomatis (Hero Emerald) -->
                    <button type="button" @click.stop="!sendingWaBot && sendWhatsAppBotReceipt()"
                        :disabled="sendingWaBot"
                        class="group rounded-2xl p-4 bg-[#25D366]/10 hover:bg-[#25D366]/18 dark:bg-[#25D366]/15 dark:hover:bg-[#25D366]/22 border border-[#25D366]/30 dark:border-[#25D366]/40 active:scale-[0.98] text-black dark:text-white flex flex-col justify-between transition shadow-xs disabled:opacity-50 text-left min-h-[120px]"
                        :class="sendingWaBot ? 'pointer-events-none opacity-60' : ''">
                        <!-- Top Row: Icon + Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div
                                class="w-9 h-9 rounded-xl bg-[#25D366] text-white flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z" />
                                </svg>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                :class="waBotSent ? 'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : (sendingWaBot ?
                                    'bg-[#FF9500]/20 text-[#FF9500]' : 'bg-[#25D366]/20 text-[#25D366]')">
                                <span
                                    x-text="sendingWaBot ? 'Mengirim...' : (waBotSent ? 'Terkirim' : 'Bot Otomatis')"></span>
                            </span>
                        </div>
                        <!-- Bottom Info & CTA -->
                        <div class="mt-3">
                            <div class="font-extrabold text-[14px] text-black dark:text-white leading-tight">Kirim
                                WhatsApp</div>
                            <div
                                class="text-[11px] text-black/60 dark:text-white/60 mt-0.5 flex items-center justify-between">
                                <span>Kirim e-struk ke pelanggan</span>
                                <span class="font-bold text-[#25D366] group-hover:translate-x-0.5 transition">
                                    <span x-text="waBotSent ? 'Kirim Ulang' : 'Kirim'"></span>
                                </span>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Secondary Row: Cetak Dapur, Pratinjau Browser, & Gambar Struk PNG -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <!-- Bento Tile 3: Cetak Tiket Dapur / Bar (KOT) -->
                    <button type="button" @click.stop="!kitchenPrinting && printKitchenTickets(lastCompletedOrder.id)"
                        :disabled="kitchenPrinting"
                        class="p-2.5 sm:p-3 rounded-xl bg-[#FF9500]/10 hover:bg-[#FF9500]/18 border border-[#FF9500]/30 active:scale-[0.98] transition flex items-center justify-between group text-left disabled:opacity-50">
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate" x-text="kitchenPrinting ? 'Mengirim...' : 'Tiket Dapur'">Tiket Dapur</div>
                                <div class="text-[9px] text-black/45 dark:text-white/45 truncate">KOT Station</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-[#FF9500] group-hover:translate-x-0.5 transition shrink-0" x-text="kitchenPrinting ? 'Mengirim...' : 'Kirim'"></span>
                    </button>

                    <!-- Bento Tile 4: Pratinjau & Cetak Browser (Fallback) -->
                    <a :href="lastReceiptUrl" target="_blank"
                        class="p-2.5 sm:p-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-between group">
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate">Browser Print</div>
                                <div class="text-[9px] text-black/45 dark:text-white/45 truncate">Pratinjau HTML</div>
                            </div>
                        </div>
                        <span
                            class="text-[10px] text-black/30 dark:text-white/30 group-hover:text-black dark:group-hover:text-white group-hover:translate-x-0.5 transition shrink-0">↗</span>
                    </a>

                    <!-- Bento Tile 5: Gambar Struk PNG HD -->
                    <a :href="lastReceiptImageUrl" target="_blank"
                        class="p-2.5 sm:p-3 rounded-xl bg-[#007AFF]/8 hover:bg-[#007AFF]/15 border border-[#007AFF]/25 active:scale-[0.98] transition flex items-center justify-between group shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-[#007AFF] truncate">Gambar Struk</div>
                                <div class="text-[9px] text-[#007AFF]/70 truncate">Foto PNG WA</div>
                            </div>
                        </div>
                        <span
                            class="text-[10px] font-bold text-[#007AFF] group-hover:translate-x-0.5 transition shrink-0">↗</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Action: Transaksi Baru -->
            <div class="pt-1">
                <button type="button" @click="resetForNewOrder()"
                    class="w-full h-11 sm:h-12 rounded-2xl bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] active:scale-[0.98] text-black dark:text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition border border-black/5 dark:border-white/10">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2.2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Selesai &amp; Transaksi Baru</span>
                    <span
                        class="text-[10px] text-black/40 dark:text-white/40 font-normal px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 ml-1">Esc</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5B. MODAL: POS SETTINGS & HARDWARE HUB (Bento Apple Sheet) -->
    <!-- ===================================================== -->
    <div x-show="showPosSettingsModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        style="display: none;"
        @click.self="showPosSettingsModal = false"
        @keydown.escape.window="showPosSettingsModal = false">
        
        <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh] text-black dark:text-white"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-base sm:text-lg text-black dark:text-white leading-tight">Pengaturan &amp; Perangkat POS</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">Pusat kontrol printer, laci kasir, layar dapur &amp; operasional</p>
                    </div>
                </div>
                <button type="button" @click="showPosSettingsModal = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 active:scale-95 flex items-center justify-center text-black/60 dark:text-white/60 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Bento Grid Content -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    
                    <!-- 1. Pengaturan Printer Thermal -->
                    <a href="{{ route('pos.printers.index') }}"
                        class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF] uppercase tracking-wide">Hardware</span>
                            </div>
                            <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#007AFF] transition">Pengaturan Printer Thermal</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                Konfigurasi printer USB, LAN/Ethernet, Bluetooth, ukuran kertas 58/80mm, dan tes cetak struk.
                            </p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#007AFF] font-medium">
                            <span>Buka Konfigurasi</span>
                            <span class="group-hover:translate-x-0.5 transition">→</span>
                        </div>
                    </a>

                    <!-- 2. Buka Laci Kas (Cash Drawer) Manual Pop -->
                    <button type="button" @click="showPosSettingsModal = false; promptManualDrawerPop()"
                        class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between text-left hover:scale-[1.01] active:scale-[0.99]">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#34C759] uppercase tracking-wide">Aksi Cepat</span>
                            </div>
                            <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#34C759] transition">Buka Laci Kas (Cash Drawer)</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                Kirim sinyal manual (No-Sale Pop) untuk membuka laci kasir dengan pencatatan audit forensik.
                            </p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#34C759] font-medium">
                            <span>Buka Laci Sekarang</span>
                            <i data-lucide="zap" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition"></i>
                        </div>
                    </button>

                    <!-- 3. Kitchen Display System (KDS) -->
                    @if (\App\Support\Context::hasPermission('pos.kitchen') && (isset($business) && $business->hasDineInFeature()))
                        <a href="{{ route('pos.kitchen.index') }}" target="_blank"
                            class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] uppercase tracking-wide">Dapur &amp; Bar</span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#FF9500] transition">Layar Dapur &amp; Bar (KDS)</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    Tampilan tiket pesanan real-time untuk koki &amp; barista tanpa boros kertas struk.
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#FF9500] font-medium">
                                <span>Buka Layar KDS</span>
                                <span class="group-hover:translate-x-0.5 transition">↗</span>
                            </div>
                        </a>
                    @endif

                    <!-- 3.1 Mode Paperless Auto KDS -->
                    @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                        <div class="p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693l-1.57-.393m15.6 0l1.4 3.5a2.25 2.25 0 01-2.09 3.085H5.89a2.25 2.25 0 01-2.09-3.085l1.4-3.5" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                        :class="posAutoSendKds ? 'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50'"
                                        x-text="posAutoSendKds ? 'Aktif' : 'Nonaktif'"></span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white">Auto Kirim ke Dapur (KDS)</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    Kirim pesanan kasir langsung ke antrean layar dapur secara otomatis tanpa harus cetak kertas struk.
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                <span class="text-xs font-medium text-black/70 dark:text-white/70" x-text="posAutoSendKds ? 'Fitur Paperless Aktif' : 'Fitur Nonaktif'"></span>
                                <button type="button" @click="toggleAutoKds()"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                    :class="posAutoSendKds ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="posAutoSendKds ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- 4. Rekap Shift Kasir -->
                    <a href="{{ route('pos.shifts.index') }}"
                        class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#5856D6]/15 text-[#5856D6] uppercase tracking-wide">Rekonsiliasi</span>
                            </div>
                            <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#5856D6] transition">Rekapitulasi Shift Kasir</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                Riwayat buka/tutup shift kasir, selisih kas fisik, rekonsiliasi, dan cetak ulang ringkasan.
                            </p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#5856D6] font-medium">
                            <span>Lihat Riwayat Shift</span>
                            <span class="group-hover:translate-x-0.5 transition">→</span>
                        </div>
                    </a>

                    <!-- 5. Riwayat Transaksi & Order -->
                    @if (\App\Support\Context::hasPermission('pos.orders'))
                        <a href="{{ route('pos.orders.index') }}"
                            class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#AF52DE]/15 text-[#AF52DE] uppercase tracking-wide">Audit Transaksi</span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#AF52DE] transition">Daftar Transaksi &amp; Void</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    Pencarian riwayat struk, pembatalan pesanan (Void), dan retur penjualan dengan PIN otorisasi.
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#AF52DE] font-medium">
                                <span>Buka Transaksi</span>
                                <span class="group-hover:translate-x-0.5 transition">→</span>
                            </div>
                        </a>
                    @endif

                    <!-- 6. Denah Meja & QR Self-Order -->
                    @if (\App\Support\Context::hasPermission('pos.tables') && (isset($business) && $business->hasDineInFeature()))
                        <a href="{{ route('pos.tables.index') }}"
                            class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#00C7BE]/10 text-[#00C7BE] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#00C7BE]/15 text-[#00C7BE] uppercase tracking-wide">F&amp;B Dine-In</span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#00C7BE] transition">Manajemen Meja &amp; QR</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    Pengaturan layout meja, unduh kartu QR code pemesanan mandiri, dan monitoring sesi meja makan.
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#00C7BE] font-medium">
                                <span>Buka Meja &amp; QR</span>
                                <span class="group-hover:translate-x-0.5 transition">→</span>
                            </div>
                        </a>
                    @endif

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-xs text-black/50 dark:text-white/50">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <span>Kasir: <strong class="text-black/70 dark:text-white/70">{{ auth()->user()->name ?? 'Kasir' }}</strong></span>
                </div>
                <button type="button" @click="showPosSettingsModal = false"
                    class="h-9 px-4 rounded-xl bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-xs font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: OPEN SHIFT (Apple Sheet)                    -->
    <!-- ===================================================== -->
    <div x-show="showOpenShiftModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;">
        <div
            class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <h3 class="font-semibold text-[17px] text-black dark:text-white">Buka Shift Kasir Baru</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Modal Awal
                        Kasir (Rp)</label>
                    <input type="number" x-model.number="shiftOpeningCash"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Catatan Shift
                        (Opsional)</label>
                    <input type="text" x-model="shiftNotes" placeholder="Catatan shift..."
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button @click="showOpenShiftModal = false"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                <button @click="submitOpenShift()"
                    class="h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] transition shadow-sm">Buka
                    Shift</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 7. MODAL: CLOSE SHIFT & RECONCILIATION (Apple Sheet)  -->
    <!-- ===================================================== -->
    <div x-show="showCloseShiftModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;">
        <div
            class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <h3 class="font-semibold text-[17px] text-black dark:text-white">Tutup Shift Kasir &amp; Rekonsiliasi</h3>

            <div
                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Modal Awal:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white"
                        x-text="formatRupiah(shiftSummary.opening_cash)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Penjualan Tunai:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white"
                        x-text="formatRupiah(shiftSummary.cash_sales)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Penjualan Non-Tunai / QRIS:</span>
                    <span class="tabular-nums font-medium text-[#007AFF]"
                        x-text="formatRupiah(shiftSummary.non_cash_sales || 0)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Kas Masuk / Keluar:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white"
                        x-text="formatRupiah(shiftSummary.cash_in - shiftSummary.cash_out)"></span>
                </div>
                <div x-show="shiftSummary.expected_cash !== null && shiftSummary.expected_cash !== undefined"
                    class="flex justify-between text-[#34C759] font-semibold border-t border-black/5 dark:border-white/5 pt-1.5 text-[13px]">
                    <span>Uang Fisik Diharapkan:</span>
                    <span class="tabular-nums font-bold" x-text="formatRupiah(shiftSummary.expected_cash)"></span>
                </div>
                <div x-show="shiftSummary.expected_cash === null || shiftSummary.expected_cash === undefined"
                    class="p-2.5 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[11px] text-[#007AFF] font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span>Mode Blind Count: Hitung uang tunai fisik di laci tanpa estimasi sistem.</span>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Hitungan Kas
                        Fisik Aktual di Laci (Rp)</label>
                    <input type="number" x-model.number="shiftActualCash"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div x-show="shiftSummary.expected_cash !== null && shiftSummary.expected_cash !== undefined"
                    class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex justify-between items-center text-xs">
                    <span class="text-black/60 dark:text-white/60">Selisih Kas:</span>
                    <span
                        :class="(shiftActualCash - shiftSummary.expected_cash) === 0 ? 'text-[#34C759] font-bold' :
                            'text-[#FF3B30] font-bold'"
                        class="tabular-nums text-[13px]"
                        x-text="formatRupiah(shiftActualCash - shiftSummary.expected_cash)"></span>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button @click="showCloseShiftModal = false"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                <button @click="submitCloseShift()"
                    class="h-9 px-5 rounded-[10px] bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] text-white font-semibold text-[13px] transition">Tutup
                    &amp; Rekonsiliasi</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 8. MODAL: HELD ORDERS QUEUE (Apple Sheet)             -->
    <!-- ===================================================== -->
    <div x-show="showHeldOrdersModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;">
        <div
            class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <h3 class="font-semibold text-[17px] text-black dark:text-white">Daftar Antrean Transaksi (Held Carts)
                </h3>
                <button @click="showHeldOrdersModal = false"
                    class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-72 overflow-y-auto space-y-2">
                <template x-for="ho in heldOrders" :key="ho.id">
                    <div
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-[14px] text-black dark:text-white"
                                x-text="ho.hold_label || 'Antrean'"></div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5"
                                x-text="ho.order_number + ' • ' + (ho.items ? ho.items.length : 0) + ' item'"></div>
                        </div>
                        <button @click="resumeHeldOrder(ho.id)"
                            class="h-8 px-3 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[12px] transition">
                            Lanjutkan
                        </button>
                    </div>
                </template>
                <div x-show="heldOrders.length === 0"
                    class="text-center py-8 text-xs text-black/40 dark:text-white/40">
                    Tidak ada transaksi yang di-hold saat ini.
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 9. MODAL: HOLD CART PROMPT (Apple Sheet - No prompt)  -->
    <!-- ===================================================== -->
    <div x-show="showHoldPromptModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
        <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
            @click.outside="showHoldPromptModal = false">
            <div>
                <h3 class="font-semibold text-[17px] text-black dark:text-white">Simpan Antrean (Hold)</h3>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Beri label meja atau nama pelanggan
                    untuk melanjutkan nanti.</p>
            </div>
            <form @submit.prevent="confirmHoldCart()">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Label Antrean
                        / No. Meja *</label>
                    <input x-ref="holdLabelInput" type="text" x-model="holdOrderLabel" required
                        maxlength="100" placeholder="Contoh: Meja 4 / Bpk. Rudi"
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="flex justify-end gap-2 pt-3 mt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showHoldPromptModal = false"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                    <button type="submit"
                        class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] transition shadow-sm">Simpan
                        Antrean</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10. MODAL: CASH MOVEMENT (Apple Sheet)                -->
    <!-- ===================================================== -->
    <div x-show="showCashMovementModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;"
        @keydown.escape.window="showCashMovementModal = false">
        <div
            class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.3)] text-black dark:text-white">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] flex items-center justify-center font-bold"
                        :class="cashMovementType === 'cash_in' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]'">
                        <i :data-lucide="cashMovementType === 'cash_in' ? 'arrow-down-left' : 'arrow-up-right'" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[17px] text-black dark:text-white">{{ __('pos.cash_movement_modal_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('pos.cash_movement_modal_desc') }}</p>
                    </div>
                </div>
                <button type="button" @click="showCashMovementModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Segmented Control Apple HIG --}}
            <div class="grid grid-cols-2 p-1 bg-black/[0.05] dark:bg-white/[0.08] rounded-[12px] gap-1">
                <button type="button" @click="cashMovementType = 'cash_out'; cashMovementCategory = 'operational'"
                    :class="cashMovementType === 'cash_out' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF3B30] font-bold shadow-sm' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                    class="py-2 rounded-[9px] text-[13px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                    <span>{{ __('pos.cash_out_tab') }}</span>
                </button>
                <button type="button" @click="cashMovementType = 'cash_in'; cashMovementCategory = 'capital_injection'"
                    :class="cashMovementType === 'cash_in' ? 'bg-white dark:bg-[#1C1C1E] text-[#34C759] font-bold shadow-sm' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                    class="py-2 rounded-[9px] text-[13px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                    <span>{{ __('pos.cash_in_tab') }}</span>
                </button>
            </div>

            <div class="space-y-3.5">
                {{-- Nominal XXL & Fast Pills --}}
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70">{{ __('pos.movement_amount') }}</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-base">Rp</span>
                        <input type="number" x-model.number="cashMovementAmount" min="1" step="any" required placeholder="0"
                            class="w-full h-12 pl-12 pr-4 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[12px] text-xl font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-inner">
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                        <span class="text-[11px] text-black/40 dark:text-white/40 font-medium mr-1">{{ __('pos.quick_amount') }}</span>
                        <button type="button" @click="cashMovementAmount = 10000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">10 rb</button>
                        <button type="button" @click="cashMovementAmount = 20000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">20 rb</button>
                        <button type="button" @click="cashMovementAmount = 50000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">50 rb</button>
                        <button type="button" @click="cashMovementAmount = 100000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">100 rb</button>
                        <button type="button" @click="cashMovementAmount = 200000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">200 rb</button>
                    </div>
                </div>

                {{-- Kategori Dinamis --}}
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1.5">
                        <span x-text="cashMovementType === 'cash_out' ? '{{ __('pos.expense_category_label') }}' : '{{ __('pos.inflow_category_label') }}'"></span>
                    </label>
                    <template x-if="cashMovementType === 'cash_out'">
                        <select x-model="cashMovementCategory"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="operational">{{ __('finance.categories.operational') }}</option>
                            <option value="consumption">{{ __('finance.categories.consumption') }}</option>
                            <option value="supplies">{{ __('finance.categories.supplies') }}</option>
                            <option value="logistics">{{ __('finance.categories.logistics') }}</option>
                            <option value="utilities">{{ __('finance.categories.utilities') }}</option>
                            <option value="maintenance">{{ __('finance.categories.maintenance') }}</option>
                            <option value="bank_admin">{{ __('finance.categories.bank_admin') }}</option>
                            <option value="other">{{ __('finance.categories.other') }}</option>
                        </select>
                    </template>
                    <template x-if="cashMovementType === 'cash_in'">
                        <select x-model="cashMovementCategory"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="capital_injection">{{ __('finance.inflow_categories.capital_injection') }}</option>
                            <option value="sales_revenue">{{ __('finance.inflow_categories.sales_revenue') }}</option>
                            <option value="cash_refund">{{ __('finance.inflow_categories.cash_refund') }}</option>
                            <option value="receivable_payment">{{ __('finance.inflow_categories.receivable_payment') }}</option>
                            <option value="other">{{ __('finance.inflow_categories.other') }}</option>
                        </select>
                    </template>
                </div>

                {{-- Alert Callout Khusus Kategori Lainnya (Wajib Diisi) --}}
                <div x-show="cashMovementCategory === 'other'" x-transition
                    class="p-3.5 rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/30 space-y-1.5">
                    <label class="block text-[12px] font-bold text-[#FF9500] dark:text-[#FF9F0A] flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        <span>{{ __('pos.other_desc_label') }}</span>
                    </label>
                    <input type="text" x-model="cashMovementOtherDesc"
                        placeholder="{{ __('pos.other_desc_placeholder') }}"
                        class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-[#FF9500]/30 rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF9500]">
                    <p class="text-[11px] text-[#FF9500]/80">{{ __('pos.other_desc_hint') }}</p>
                </div>

                {{-- Catatan / Keterangan Tambahan --}}
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.movement_reason_optional') }}</label>
                    <input type="text" x-model="cashMovementReason"
                        placeholder="{{ __('pos.movement_reason_placeholder') }}"
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showCashMovementModal = false"
                    class="h-10 px-5 rounded-[12px] text-[13.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="button" @click="submitCashMovement()"
                    class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-bold text-[13.5px] transition shadow-[0_2px_10px_rgba(0,122,255,0.3)] cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('pos.save_cash_movement') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10B. MODAL: SUPERVISOR PIN AUTHORIZATION (Apple Alert Style) -->
    <!-- ===================================================== -->
    <div x-show="showSupervisorPinModal" x-cloak
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 backdrop-blur-[4px] p-4"
        @keydown.escape.window="if(!supervisorPinLoading) closeSupervisorPinModal()">
        <div class="pos-modal-panel w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[18px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="if(!supervisorPinLoading) closeSupervisorPinModal()">
            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 mx-auto rounded-full bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[17px] text-black dark:text-white">{{ __('pos.supervisor_pin_title') }}</h3>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    {{ __('pos.supervisor_pin_desc') }}
                </p>
            </div>

            <form @submit.prevent="verifySupervisorPinSubmit()" class="space-y-3.5">
                <div>
                    <input x-ref="supervisorPinInputRef" type="password" inputmode="numeric" maxlength="8"
                        x-model="supervisorPinInput" placeholder="••••••" autocomplete="off"
                        :disabled="supervisorPinLoading"
                        class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/15 rounded-[12px] text-center font-mono text-[22px] tracking-[0.35em] text-black dark:text-white placeholder:tracking-normal placeholder:font-sans placeholder:text-[14px] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/60 transition disabled:opacity-50">
                </div>

                <div x-show="supervisorPinError"
                    class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-[#FF3B30] text-center font-medium leading-relaxed"
                    x-text="supervisorPinError"></div>

                <!-- No-Panic Microcopy & Security Note (Apple HIG Trust Badge) -->
                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1 text-center">
                    <div class="flex items-center justify-center gap-1.5 text-[11px] font-medium text-[#34C759]">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>{{ __('pos.supervisor_pin_security_note') }}</span>
                    </div>
                    <p class="text-[10px] text-black/45 dark:text-white/45 leading-normal">
                        {{ __('pos.supervisor_pin_no_panic_guide') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" @click="closeSupervisorPinModal()" :disabled="supervisorPinLoading"
                        class="h-10 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/70 dark:text-white/70 font-semibold text-[13px] transition disabled:opacity-50">
                        {{ __('pos.cancel') }}
                    </button>
                    <button type="submit" :disabled="supervisorPinLoading || !supervisorPinInput.trim()"
                        class="h-10 rounded-[10px] bg-[#AF52DE] hover:bg-[#9B42C8] active:scale-[0.98] text-white font-semibold text-[13px] transition disabled:opacity-50 flex items-center justify-center gap-1.5 shadow-sm">
                        <span x-show="!supervisorPinLoading">{{ __('pos.authorize') }}</span>
                        <span x-show="supervisorPinLoading" class="inline-block animate-spin">⏳</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 11. MODAL: CAMERA PERMISSION (Apple Alert Style)      -->
    <!-- ===================================================== -->
    <div x-show="showScannerPermission" x-cloak
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
        <div
            class="w-[300px] rounded-[14px] bg-white dark:bg-[#2C2C2E] overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/10 dark:border-white/10 text-black dark:text-white">
            <div class="px-5 pt-5 pb-4">
                <div
                    class="w-10 h-10 rounded-full bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                    </svg>
                </div>
                <p class="text-[16px] font-semibold text-black dark:text-white">Izinkan Akses Kamera?</p>
                <p class="text-[12px] text-black/60 dark:text-white/60 mt-1 leading-snug">Kamera digunakan untuk
                    memindai barcode produk secara otomatis.</p>
            </div>
            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[14px] font-medium">
                <button type="button" @click="showScannerPermission = false"
                    class="py-2.5 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition">Batal</button>
                <button type="button" @click="confirmBarcodeScannerAccess()"
                    class="py-2.5 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition">Izinkan</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 12. MODAL: CAMERA BARCODE SCANNER (Apple Sheet)       -->
    <!-- ===================================================== -->
    <div x-show="showBarcodeScanner" x-cloak @keydown.escape.window="closeBarcodeScanner()"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @click.self="closeBarcodeScanner()">
        <div
            class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-4 sm:p-5 space-y-3.5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-[17px] text-black dark:text-white">{{ __('pos.scan_barcode_title') }}</h3>
                    <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Arahkan kamera ke barcode hingga
                        produk terdeteksi.</p>
                </div>
                <button type="button" @click="closeBarcodeScanner()"
                    class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                class="relative aspect-video overflow-hidden rounded-[14px] bg-black border border-black/10 dark:border-white/10">
                <video x-ref="barcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div
                        class="w-[70%] h-[40%] rounded-[12px] border-2 border-[#007AFF] shadow-[0_0_0_9999px_rgba(0,0,0,.45)]">
                    </div>
                </div>
                <div x-show="scannerStarting"
                    class="absolute inset-0 flex items-center justify-center bg-black/70 text-xs text-white/60">
                    <span class="flex items-center gap-2">Menyiapkan kamera...</span>
                </div>
            </div>

            <div x-show="scannerError"
                class="p-3 rounded-[10px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-xs text-[#FF3B30]"
                x-text="scannerError"></div>

            <div x-show="scannerDevices.length > 1" class="space-y-1">
                <label class="block text-[11px] text-black/50 dark:text-white/50 font-medium">Pilih Kamera</label>
                <select x-model="selectedScannerDeviceId" @change="startBarcodeScanner()"
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-3 text-xs text-black dark:text-white">
                    <template x-for="device in scannerDevices" :key="device.deviceId">
                        <option :value="device.deviceId"
                            x-text="device.label || 'Kamera ' + (scannerDevices.indexOf(device) + 1)"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2 border-t border-black/10 dark:border-white/10">
                <span class="text-[11px] text-black/40 dark:text-white/40">Scanner USB/Bluetooth juga tetap aktif via
                    kolom pencarian.</span>
                <button type="button" @click="closeBarcodeScanner()"
                    class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12px] font-medium transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL: CASHIER PRODUCT MODIFIER & VARIANT PICKER      -->
    <!-- ===================================================== -->
    <div x-show="showModifierModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showModifierModal = false">
        <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="showModifierModal = false">
            <template x-if="activeModifierProduct">
                <div class="space-y-4">
                    <!-- Product Header -->
                    <div
                        class="flex items-start justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <template x-if="activeModifierProduct.image_url">
                                <img :src="activeModifierProduct.image_url"
                                    class="w-12 h-12 rounded-[10px] object-cover border border-black/5 dark:border-white/10 shrink-0"
                                    alt="">
                            </template>
                            <div class="min-w-0">
                                <h3 class="font-bold text-[15px] text-black dark:text-white leading-snug truncate"
                                    x-text="activeModifierProduct.name"></h3>
                                <div class="text-xs text-[#007AFF] font-bold tabular-nums mt-0.5"
                                    x-text="formatRupiah(getProductPrice(activeModifierProduct))"></div>
                            </div>
                        </div>
                        <button type="button" @click="showModifierModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modifier Groups List -->
                    <div class="space-y-3.5 max-h-[50vh] overflow-y-auto pr-1">
                        <template x-for="group in activeModifierProduct.modifier_groups" :key="group.id">
                            <div
                                class="space-y-2 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-xs text-black dark:text-white"
                                            x-text="group.name"></span>
                                        <span x-show="group.is_required"
                                            class="text-[9px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 px-1.5 py-0.2 rounded">Wajib</span>
                                    </div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45"
                                        x-text="group.selection_type === 'single' ? 'Pilih 1' : ('Pilih maks ' + group.max_selection)"></span>
                                </div>

                                <div class="space-y-1.5">
                                    <template x-for="opt in group.options" :key="opt.id">
                                        <label
                                            class="flex items-center justify-between p-2 rounded-[8px] border transition cursor-pointer text-xs select-none"
                                            :class="isModifierSelected(group.id, opt.id) ?
                                                'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF] font-semibold' :
                                                ((opt.is_in_stock ?? opt.is_available ?? true) ?
                                                    'border-black/[0.06] dark:border-white/10 hover:bg-black/[0.03] dark:hover:bg-white/[0.05] text-black dark:text-white' :
                                                    'opacity-40 cursor-not-allowed border-dashed')">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    :type="group.selection_type === 'single' ? 'radio' : 'checkbox'"
                                                    :name="'mod_group_' + group.id"
                                                    :disabled="!(opt.is_in_stock ?? opt.is_available ?? true)"
                                                    :checked="isModifierSelected(group.id, opt.id)"
                                                    @change="toggleModifierOption(group, opt)"
                                                    class="w-4 h-4 text-[#007AFF] focus:ring-0 rounded">
                                                <span x-text="opt.name"></span>
                                                <span x-show="!(opt.is_in_stock ?? opt.is_available ?? true)"
                                                    class="text-[9px] text-[#FF3B30] font-bold bg-[#FF3B30]/15 px-1 py-0.2 rounded">Stok
                                                    Habis</span>
                                            </div>
                                            <span class="tabular-nums font-medium"
                                                x-text="opt.price_delta > 0 ? ('+' + formatRupiah(opt.price_delta)) : 'Gratis'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Item Notes Input -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-black dark:text-white">Catatan Item
                                (Opsional)</label>
                            <input type="text" x-model="modifierItemNotes"
                                placeholder="Cth: Less Sugar, No Ice, Pisah Saus..."
                                class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs font-semibold text-black dark:text-white">Jumlah</span>
                            <div
                                class="flex items-center gap-2 bg-black/[0.04] dark:bg-white/[0.06] rounded-[8px] p-1 border border-black/5 dark:border-white/10">
                                <button type="button" @click="modifierItemQty = Math.max(1, modifierItemQty - 1)"
                                    class="w-7 h-7 rounded-[6px] bg-white dark:bg-white/[0.1] text-black dark:text-white flex items-center justify-center font-bold text-sm shadow-xs">-</button>
                                <span class="w-8 text-center font-bold tabular-nums text-xs"
                                    x-text="modifierItemQty"></span>
                                <button type="button" @click="modifierItemQty++"
                                    class="w-7 h-7 rounded-[6px] bg-white dark:bg-white/[0.1] text-black dark:text-white flex items-center justify-center font-bold text-sm shadow-xs">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div
                        class="flex items-center justify-between gap-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div>
                            <div class="text-[10px] text-black/50 dark:text-white/50 font-medium">Subtotal Item</div>
                            <div class="text-sm font-extrabold text-black dark:text-white tabular-nums"
                                x-text="formatRupiah(calculateModifierTotalPrice() * modifierItemQty)"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showModifierModal = false"
                                class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                            <button type="button" @click="addModifierItemToCart()"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                                Tambah ke Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @if ($isWorkshop || $isLaundry)
    <!-- ===================================================== -->
    <!-- MODAL: DATA LAYANAN INDUSTRI (BENGKEL & LAUNDRY) -->
    <!-- ===================================================== -->
    <div x-show="showServiceVerticalModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showServiceVerticalModal = false">
        <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="showServiceVerticalModal = false">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-[15px] text-black dark:text-white leading-tight">
                            @if ($isWorkshop && !$isLaundry)
                                SPK Bengkel &amp; Kendaraan
                            @elseif ($isLaundry && !$isWorkshop)
                                Layanan Laundry Kiloan
                            @else
                                Data Layanan Khusus
                            @endif
                        </h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">
                            @if ($isWorkshop && !$isLaundry)
                                Formulir Surat Perintah Kerja, Kendaraan &amp; Mekanik
                            @elseif ($isLaundry && !$isWorkshop)
                                Formulir Timbangan Cucian &amp; Lokasi Rak
                            @else
                                Formulir SPK Bengkel &amp; Data Cucian Laundry
                            @endif
                        </p>
                    </div>
                </div>
                <button type="button" @click="showServiceVerticalModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Segmented Tab Selector (Apple HIG) - Displayed only if both or multi-service enabled -->
            @if (($isWorkshop && $isLaundry) || (!$isFnB && !$isRetail && !$isWorkshop && !$isLaundry))
            <div class="inline-flex w-full p-0.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-[12px] font-medium">
                <button type="button" @click="serviceVerticalTab = 'workshop'"
                    :class="serviceVerticalTab === 'workshop' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60'"
                    class="flex-1 py-1.5 rounded-[8px] transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V6.75A2.25 2.25 0 0012 4.5H6.75A2.25 2.25 0 004.5 6.75v12" />
                    </svg>
                    <span>Bengkel &amp; Kendaraan</span>
                </button>
                <button type="button" @click="serviceVerticalTab = 'laundry'"
                    :class="serviceVerticalTab === 'laundry' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60'"
                    class="flex-1 py-1.5 rounded-[8px] transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span>Laundry Kiloan</span>
                </button>
            </div>
            @endif

            <!-- Tab 1 Content: Bengkel Otomotif -->
            <div x-show="serviceVerticalTab === 'workshop'" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Nomor Polisi (Plat)</label>
                        <input type="text" x-model="vehicleLicensePlate" placeholder="Cth: B 1234 XYZ"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] font-mono font-bold uppercase text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Tipe / Model Kendaraan</label>
                        <input type="text" x-model="vehicleModel" placeholder="Cth: Honda Vario 160"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">KM Odometer</label>
                        <input type="number" x-model="vehicleMileage" placeholder="Cth: 24500"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Teknisi / Mekanik</label>
                        <select x-model="technicianId"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] cursor-pointer">
                            <option value="" class="bg-white dark:bg-[#1C1C1E]">-- Pilih Mekanik --</option>
                            <template x-for="t in technicians" :key="t.id">
                                <option :value="t.id" :selected="technicianId === t.id" x-text="t.name" class="bg-white dark:bg-[#1C1C1E]"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Catatan Servis / Keluhan (SPK)</label>
                    <textarea x-model="serviceNotes" rows="2" placeholder="Cth: Servis berkala, ganti oli mesin, cek bunyi di roda depan..."
                        class="w-full rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 p-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>
            </div>

            <!-- Tab 2 Content: Laundry -->
            <div x-show="serviceVerticalTab === 'laundry'" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Total Timbangan (Kg)</label>
                        <input type="number" step="0.01" min="0" x-model="laundryWeightKg" placeholder="Cth: 4.50"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] font-bold font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Nomor Rak / Loker Cucian</label>
                        <input type="text" x-model="rackLocation" placeholder="Cth: Rak B-02 / Loker 05"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Estimasi Selesai</label>
                        <input type="datetime-local" x-model="estimatedCompletionAt"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">Status Cucian</label>
                        <select x-model="laundryStatus"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] cursor-pointer">
                            <option value="received" class="bg-white dark:bg-[#1C1C1E]">Diterima</option>
                            <option value="washing" class="bg-white dark:bg-[#1C1C1E]">Sedang Dicuci</option>
                            <option value="drying" class="bg-white dark:bg-[#1C1C1E]">Pengeringan</option>
                            <option value="ironing" class="bg-white dark:bg-[#1C1C1E]">Penyetrikaan</option>
                            <option value="ready" class="bg-white dark:bg-[#1C1C1E]">Siap Diambil</option>
                            <option value="completed" class="bg-white dark:bg-[#1C1C1E]">Selesai / Diambil</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="vehicleLicensePlate = ''; vehicleModel = ''; vehicleMileage = null; technicianId = ''; serviceNotes = ''; laundryWeightKg = null; rackLocation = ''; estimatedCompletionAt = ''; laundryStatus = 'received';"
                    class="h-9 px-3 rounded-[10px] text-xs font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition">
                    Hapus Data Layanan
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showServiceVerticalModal = false"
                        class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                    <button type="button" @click="showServiceVerticalModal = false"
                        class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                        Simpan Data Layanan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- MODAL: DETAIL ITEM & APOTEK / OBAT (BATCH & DOSIS)    -->
    <!-- ===================================================== -->
    <div x-show="showItemDetailModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showItemDetailModal = false">
        <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="showItemDetailModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] {{ $isPharmacy ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#007AFF]/15 text-[#007AFF]' }} flex items-center justify-center">
                        @if ($isPharmacy)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                        </svg>
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-[15px] text-black dark:text-white leading-tight">
                            @if ($isPharmacy)
                                {{ __('pos.item_detail_pharmacy') ?? 'Detail & Dosis Obat' }}
                            @else
                                {{ __('pos.item_detail_notes') ?? 'Detail & Catatan Item' }}
                            @endif
                        </h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50" x-text="editingItemIndex !== null && cart[editingItemIndex] ? cart[editingItemIndex].product_name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="showItemDetailModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div class="space-y-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.item_notes_label') ?? 'Catatan Khusus Item' }}</label>
                    <input type="text" x-model="editingItemNotes" placeholder="{{ __('pos.item_notes_placeholder') ?? 'Cth: Diskon khusus, permintaan khusus...' }}"
                        class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                @if ($business->isPharmacy() || $business->isModuleEnabled('industry_pharmacy'))
                <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/5 space-y-2.5">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF]">{{ __('pos.pharmacy_mode') }}</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60">{{ __('pos.batch_number') }}</label>
                            <input type="text" x-model="editingItemBatchNumber" placeholder="Cth: BATCH-2026A"
                                class="w-full h-8 rounded-[7px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-2.5 text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60">{{ __('pos.expiry_date') }}</label>
                            <input type="date" x-model="editingItemExpiredDate"
                                class="w-full h-8 rounded-[7px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-2 text-[11px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60">{{ __('pos.dosage_instructions') }}</label>
                        <input type="text" x-model="editingItemDosage" placeholder="Cth: 3 x 1 tablet sehari setelah makan"
                            class="w-full h-8 rounded-[7px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="showItemDetailModal = false"
                    class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">{{ __('pos.cancel') }}</button>
                <button type="button" @click="saveItemDetail()"
                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                    {{ __('pos.confirm') }}
                </button>
            </div>
        </div>
    </div>

    @if ($business && $business->hasDineInFeature())
    <!-- ===================================================== -->
    <!-- MODAL: INCOMING QR TABLE ORDERS DRAWER                -->
    <!-- ===================================================== -->
    <div x-show="showIncomingOrdersModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showIncomingOrdersModal = false">
        <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="showIncomingOrdersModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-black dark:text-white">Pesanan Masuk dari Meja QR</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">Pelanggan scan QR meja dan mengirim
                            pesanan langsung.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="fetchIncomingOrders()"
                        class="p-2 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white"
                        title="Segarkan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                    <button type="button" @click="showIncomingOrdersModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                <template x-for="order in incomingQrOrders" :key="order.id">
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/10 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#007AFF]/15 text-[#007AFF]"
                                        x-text="'Meja ' + (order.pos_table ? order.pos_table.table_number : '-')"></span>
                                    <span class="font-mono text-xs text-black/50 dark:text-white/50"
                                        x-text="'#' + (order.order_number || order.id)"></span>
                                    <template x-if="order.is_paid">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            <span x-text="'Lunas (' + (order.payment_channel || 'QRIS') + ')'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!order.is_paid">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Bayar di Kasir</span>
                                        </span>
                                    </template>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white mt-1">
                                    <span x-text="order.customer_name_guest || 'Pelanggan'"></span>
                                    <span class="text-black/45 dark:text-white/45"
                                        x-text="' • ' + (order.customer_phone_guest || '-')"></span>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-black/50 dark:text-white/50"
                                x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes"
                            class="p-2 rounded-[8px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-xs text-[#FF9500] font-medium flex items-center gap-1.5">
                            <span class="font-bold">Catatan Meja:</span>
                            <span x-text="order.notes"></span>
                        </div>

                        <!-- Items Breakdown -->
                        <div class="space-y-1.5 border-t border-b border-black/[0.05] dark:border-white/5 py-2">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="text-xs">
                                    <div class="flex items-baseline justify-between">
                                        <div
                                            class="flex items-baseline gap-1.5 font-medium text-black dark:text-white">
                                            <span class="font-bold text-[#007AFF]"
                                                x-text="item.quantity + 'x'"></span>
                                            <span x-text="item.product_name"></span>
                                        </div>
                                        <span class="tabular-nums font-semibold"
                                            x-text="formatRupiah(item.total_price)"></span>
                                    </div>
                                    <div x-show="item.modifiers && item.modifiers.length > 0"
                                        class="pl-4 text-[11px] text-[#007AFF]">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <span class="mr-2"
                                                x-text="'• ' + mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                        </template>
                                    </div>
                                    <div x-show="item.notes" class="pl-4 text-[10px] text-[#FF9500] font-medium"
                                        x-text="'Catatan: ' + item.notes"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Total & Action Buttons -->
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <div>
                                <span class="text-[10px] text-black/50 dark:text-white/50 block">Total Tagihan</span>
                                <span class="text-base font-extrabold text-black dark:text-white tabular-nums"
                                    x-text="formatRupiah(order.total_amount)"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="!order.is_paid">
                                    <button type="button" @click="promptRejectOrder(order)"
                                        class="h-9 px-3 rounded-[10px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] text-xs font-bold transition">
                                        Tolak
                                    </button>
                                </template>
                                <template x-if="order.is_paid">
                                    <span class="text-[11px] text-[#34C759] font-medium px-2 py-1 bg-[#34C759]/10 rounded-md">
                                        Sudah Bayar
                                    </span>
                                </template>
                                <button type="button" @click="acceptIncomingOrder(order.id)"
                                    class="h-9 px-3.5 rounded-[10px] bg-[#34C759]/15 hover:bg-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-xs font-bold transition flex items-center gap-1.5"
                                    title="Terima dan teruskan ke dapur">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span>Terima Saja</span>
                                </button>
                                <button type="button" @click="acceptAndLoadToCart(order.id)"
                                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
                                    title="Terima pesanan dan langsung muat ke keranjang kasir">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                    </svg>
                                    <span>Buka di Kasir</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="incomingQrOrders.length === 0"
                    class="text-center py-16 text-black/40 dark:text-white/40 text-xs">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Tidak ada antrean pesanan QR baru.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL: REJECT REASON PROMPT                           -->
    <!-- ===================================================== -->
    <div x-show="showRejectReasonModal" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showRejectReasonModal = false">
        <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="showRejectReasonModal = false">
            <div>
                <h3 class="font-bold text-base text-[#FF3B30]">{{ __('pos.reject_table_order') }}</h3>
                <p class="text-xs text-black/60 dark:text-white/60 mt-1">Masukkan alasan penolakan agar tercatat di
                    histori order.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-black dark:text-white mb-1">Alasan Penolakan</label>
                <textarea x-model="rejectionReason" rows="3" placeholder="Cth: Bahan baku habis, Meja sedang reservasi..."
                    class="w-full rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 p-2.5 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="showRejectReasonModal = false"
                    class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                <button type="button" @click="submitRejectOrder()"
                    class="h-9 px-4 rounded-[10px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-xs font-bold transition">
                    Konfirmasi Tolak
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL: RESTAURANT TABLES & STOREFRONT RESERVATIONS   -->
    <!-- ===================================================== -->
    <div x-show="showTablesModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showTablesModal = false">
        <div class="pos-modal-panel w-full max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white flex flex-col max-h-[90vh]"
            @click.outside="showTablesModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-black dark:text-white">Manajemen Meja &amp; Reservasi Restoran</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">Pilih meja transaksi kasir, pantau tagihan, atau check-in reservasi storefront.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('storefront.reservations.index') }}" target="_blank"
                        class="text-xs font-semibold text-[#5856D6] hover:underline flex items-center gap-1 hidden sm:flex"
                        title="Buka Manajemen Reservasi Storefront">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>Data Reservasi</span>
                    </a>
                    <span class="text-black/20 dark:text-white/20 hidden sm:inline">•</span>
                    @if (\App\Support\Context::hasPermission('pos.tables'))
                        <a href="{{ route('pos.tables.index') }}" target="_blank"
                            class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Kelola Meja &amp; QR</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    @endif
                    <button type="button" @click="showTablesModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Apple HIG Segmented Bar -->
            <div class="flex items-center justify-between gap-2 p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[14px] shrink-0">
                <div class="flex items-center gap-1">
                    <button type="button" @click="tableModalTab = 'tables'"
                        :class="tableModalTab === 'tables' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        <span>Status Meja &amp; Tagihan</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/5 dark:bg-white/10" x-text="tables.length"></span>
                    </button>

                    <button type="button" @click="tableModalTab = 'reservations'"
                        :class="tableModalTab === 'reservations' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs transition flex items-center gap-1.5 relative">
                        <svg class="w-3.5 h-3.5 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>Reservasi Storefront Hari Ini</span>
                        <span x-show="todayReservations.length > 0" x-text="todayReservations.length"
                            class="px-1.5 py-0.2 rounded-full bg-[#5856D6] text-white font-bold text-[10px]"></span>
                    </button>
                </div>

                <div class="text-[11px] text-black/40 dark:text-white/40 hidden sm:block pr-2">
                    <span x-show="tableModalTab === 'tables'">Klik meja untuk sambungkan ke kasir</span>
                    <span x-show="tableModalTab === 'reservations'">Tamu datang? Klik Check-In untuk duduk di meja</span>
                </div>
            </div>

            <!-- TAB 1: Tables Grid -->
            <div x-show="tableModalTab === 'tables'" class="flex-1 overflow-y-auto pr-1">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <template x-for="tbl in tables" :key="tbl.id">
                        <div class="p-3.5 rounded-[14px] border transition flex flex-col justify-between select-none"
                            :class="tbl.status === 'available' ? 'bg-[#34C759]/5 border-[#34C759]/30' : (tbl
                                .status === 'waiting_payment' ? 'bg-[#FF9500]/10 border-[#FF9500]/40' :
                                'bg-[#007AFF]/10 border-[#007AFF]/40')">
                            <div>
                                <div class="flex items-start justify-between gap-1">
                                    <span class="font-extrabold text-base text-black dark:text-white"
                                        x-text="'Meja ' + tbl.table_number"></span>
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded"
                                        :class="tbl.status === 'available' ? 'bg-[#34C759]/20 text-[#34C759]' : (tbl
                                            .status === 'waiting_payment' ? 'bg-[#FF9500]/20 text-[#FF9500]' :
                                            'bg-[#007AFF]/20 text-[#007AFF]')"
                                        x-text="tbl.status"></span>
                                </div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5"
                                    x-text="(tbl.name ? tbl.name + ' • ' : '') + tbl.capacity + ' Kursi'"></div>

                                <!-- Reservation Indicator on Table Card -->
                                <div x-show="tbl.today_reservation"
                                    class="mt-2 p-1.5 rounded-[8px] bg-[#5856D6]/10 border border-[#5856D6]/20 text-[10px] text-[#5856D6] space-y-0.5">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="inline-flex items-center gap-1 truncate">
                                            <i data-lucide="calendar" class="w-3 h-3 text-[#5856D6]"></i>
                                            <span>Booking Hari Ini</span>
                                        </span>
                                        <span x-text="tbl.today_reservation?.time_slot"></span>
                                    </div>
                                    <div class="text-[10px] text-black/70 dark:text-white/70 truncate"
                                        x-text="(tbl.today_reservation?.customer_name || '') + ' (' + (tbl.today_reservation?.guest_count || 1) + ' org)'"></div>
                                </div>

                                <!-- Session info if occupied -->
                                <div x-show="tbl.active_session"
                                    class="mt-2 pt-2 border-t border-black/5 dark:border-white/5 space-y-1">
                                    <div class="text-[11px] font-bold text-black dark:text-white truncate"
                                        x-text="tbl.active_session?.customer_name"></div>
                                    <div class="text-xs font-extrabold text-[#007AFF] tabular-nums"
                                        x-text="formatRupiah(tbl.active_session?.total_amount || 0)"></div>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="mt-3 pt-2 border-t border-black/5 dark:border-white/5 flex flex-col gap-1.5">
                                <template
                                    x-if="tbl.active_session && tbl.active_session.orders && tbl.active_session.orders.length > 0">
                                    <div class="flex flex-col gap-1.5">
                                        <button type="button" @click="loadTableOrderToCart(tbl)"
                                            class="w-full h-8 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[11px] font-bold transition flex items-center justify-center gap-1.5 shadow-sm"
                                            title="Muat seluruh pesanan meja ke keranjang kasir">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                            <span>Cek &amp; Muat ke Keranjang</span>
                                        </button>
                                        <button type="button" @click="loadTableOrderToCart(tbl, true)"
                                            class="w-full h-7 rounded-[7px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[11px] font-bold transition flex items-center justify-center gap-1">
                                            <span>Bayar Cepat</span>
                                        </button>
                                    </div>
                                </template>
                                <template
                                    x-if="!tbl.active_session || !tbl.active_session.orders || tbl.active_session.orders.length === 0">
                                    <button type="button" @click="selectTableForCart(tbl)"
                                        class="w-full h-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[11px] font-semibold transition">
                                        Pilih Meja Ini (Dine In)
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- TAB 2: Today's Storefront Reservations -->
            <div x-show="tableModalTab === 'reservations'" class="flex-1 overflow-y-auto pr-1">
                <!-- Empty State -->
                <div x-show="!todayReservations || todayReservations.length === 0"
                    class="py-12 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-2xl bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-sm text-black dark:text-white">Tidak Ada Jadwal Reservasi Hari Ini</h4>
                    <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mt-1">Belum ada booking meja dari storefront untuk hari ini. Pelanggan dapat melakukan booking via katalog storefront online Anda.</p>
                    <a href="{{ route('storefront.reservations.index') }}" target="_blank"
                        class="mt-4 px-3.5 py-1.5 rounded-[10px] bg-[#5856D6] hover:bg-[#4B49C2] text-white text-xs font-semibold transition inline-flex items-center gap-1.5 shadow-sm">
                        <span>Buka Modul Reservasi</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                    </a>
                </div>

                <!-- Reservations List -->
                <div x-show="todayReservations && todayReservations.length > 0" class="space-y-3">
                    <template x-for="rsv in todayReservations" :key="rsv.id">
                        <div class="p-4 rounded-[14px] border border-black/10 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] transition flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <!-- Left: Guest Details & Booking Meta -->
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- Time Slot Pill -->
                                    <div class="px-2.5 py-0.5 rounded-full bg-[#5856D6]/15 text-[#5856D6] text-[11px] font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="rsv.time_slot"></span>
                                    </div>

                                    <!-- Guest Count -->
                                    <div class="px-2 py-0.5 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 text-[11px] font-semibold">
                                        <span x-text="rsv.guest_count + ' Tamu'"></span>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full"
                                        :class="{
                                            'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]': rsv.status === 'seated',
                                            'bg-[#007AFF]/20 text-[#007AFF]': rsv.status === 'confirmed',
                                            'bg-[#FF9500]/20 text-[#FF9500]': rsv.status === 'pending_confirmation'
                                        }"
                                        x-text="rsv.status === 'seated' ? 'Sudah Duduk' : (rsv.status === 'confirmed' ? 'Terkonfirmasi' : 'Menunggu Konfirmasi')"></span>

                                    <!-- Reservation Code -->
                                    <span class="font-mono text-[10px] text-black/40 dark:text-white/40" x-text="'#' + rsv.reservation_code"></span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <h4 class="font-bold text-sm text-black dark:text-white truncate" x-text="rsv.customer_name"></h4>
                                    <!-- WhatsApp Direct Link -->
                                    <template x-if="rsv.customer_phone">
                                        <a :href="'https://wa.me/' + rsv.customer_phone.replace(/[^0-9]/g, '')" target="_blank"
                                            class="text-[11px] font-medium text-[#34C759] hover:underline flex items-center gap-1"
                                            title="Chat Pelanggan di WhatsApp">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span x-text="rsv.customer_phone"></span>
                                        </a>
                                    </template>
                                </div>

                                <!-- Assigned Table Display & Selector -->
                                <div class="text-xs text-black/60 dark:text-white/60 flex items-center gap-1.5">
                                    <span>Penempatan:</span>
                                    <template x-if="rsv.pos_table_name">
                                        <span class="font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-[6px]" x-text="rsv.pos_table_name"></span>
                                    </template>
                                    <template x-if="!rsv.pos_table_name">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-[#FF9500] font-semibold italic">Belum ditentukan</span>
                                            <select x-model="rsv.pos_table_id"
                                                class="h-7 text-xs rounded-[6px] border border-black/15 dark:border-white/15 bg-white dark:bg-[#1C1C1E] text-black dark:text-white px-2 py-0.5 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                                <option value="">-- Pilih Meja --</option>
                                                <template x-for="tbl in tables" :key="tbl.id">
                                                    <option :value="tbl.id" x-text="'Meja ' + tbl.table_number + (tbl.name ? ' (' + tbl.name + ')' : '') + ' • ' + tbl.capacity + ' Kursi'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="rsv.notes" class="text-[11px] text-black/50 dark:text-white/50 italic" x-text="'Catatan: ' + rsv.notes"></div>
                            </div>

                            <!-- Right: Check-In & Action Buttons -->
                            <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-black/5 dark:border-white/5">
                                <template x-if="rsv.status !== 'seated'">
                                    <button type="button" @click="seatReservation(rsv)"
                                        :disabled="isSeatingReservation"
                                        class="h-9 px-4 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        <span x-text="isSeatingReservation ? 'Memproses...' : 'Check-In &amp; Dudukkan'"></span>
                                    </button>
                                </template>

                                <template x-if="rsv.status === 'seated'">
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                            @click="const tbl = tables.find(t => t.id === rsv.pos_table_id); if (tbl) { selectTableForCart(tbl); } else { showTablesModal = false; }"
                                            class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                            <span>Buka Kasir</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL: PAY TABLE QR ORDER (ATOMIC MATERIAL DEDUCTION)  -->
    <!-- ===================================================== -->
    <div x-show="showTablePaymentModal" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showTablePaymentModal = false">
        <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
            @click.outside="showTablePaymentModal = false">
            <template x-if="activeTableOrder">
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div>
                            <h3 class="font-bold text-base text-black dark:text-white">Pembayaran Pesanan Meja</h3>
                            <div class="text-xs text-black/50 dark:text-white/50"
                                x-text="'Pesanan #' + activeTableOrder.order_number"></div>
                        </div>
                        <button type="button" @click="showTablePaymentModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Order Items Summary -->
                    <div
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5 max-h-40 overflow-y-auto">
                        <template x-for="it in activeTableOrder.items" :key="it.id">
                            <div class="flex justify-between text-xs">
                                <div>
                                    <span class="font-bold" x-text="it.quantity + 'x '"></span>
                                    <span x-text="it.product_name"></span>
                                    <div x-show="it.modifiers && it.modifiers.length > 0"
                                        class="text-[10px] text-[#007AFF]">
                                        <template x-for="m in it.modifiers" :key="m.id">
                                            <span class="mr-1" x-text="'+ ' + m.modifier_option_name"></span>
                                        </template>
                                    </div>
                                </div>
                                <span class="font-semibold tabular-nums"
                                    x-text="formatRupiah(it.total_price)"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Amount & Payment Status Display -->
                    <template x-if="activeTableOrder.is_paid">
                        <div class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 space-y-2.5">
                            <div class="flex items-center gap-2 text-[#248A3D] dark:text-[#30D158]">
                                <div class="w-6 h-6 rounded-full bg-[#34C759]/20 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider">Sudah Lunas via TriPay QRIS</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-1">
                                <span class="text-xs text-black/60 dark:text-white/60 font-medium">Total Terbayar:</span>
                                <span class="text-xl font-black text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="formatRupiah(activeTableOrder.total_amount)"></span>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 border-t border-[#34C759]/15 pt-2 flex items-center justify-between">
                                <span>Kanal: <b class="font-semibold text-black/80 dark:text-white/80" x-text="activeTableOrder.payment_channel || 'QRIS Dinamis'"></b></span>
                                <span x-show="activeTableOrder.gateway_reference" class="font-mono text-[10px]" x-text="'Ref: ' + activeTableOrder.gateway_reference"></span>
                            </div>
                            <div class="p-2 rounded-[8px] bg-[#34C759]/15 text-[11px] text-[#248A3D] dark:text-[#30D158] font-medium leading-relaxed">
                                Tamu meja telah melunasi tagihan ini secara mandiri. Jangan menagih uang tunai kembali ke pelanggan.
                            </div>
                        </div>
                    </template>

                    <template x-if="!activeTableOrder.is_paid">
                        <div>
                            <!-- Total Amount Display -->
                            <div class="text-center py-2 bg-[#007AFF]/10 rounded-[12px] border border-[#007AFF]/20">
                                <span class="text-xs text-[#007AFF] font-medium block">Total yang Harus Dibayar</span>
                                <span class="text-2xl font-black text-[#007AFF] tabular-nums"
                                    x-text="formatRupiah(activeTableOrder.total_amount)"></span>
                            </div>

                            <!-- Payment Method Selector -->
                            <div class="space-y-1.5 mt-3">
                                <label class="block text-xs font-semibold text-black dark:text-white">Metode Pembayaran</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'cash'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'cash' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">Tunai</button>
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'qris'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'qris' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">QRIS</button>
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'transfer'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'transfer' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">Transfer</button>
                                    <button type="button"
                                        @click="selectedTablePayMethod = 'debit'; tableTenderAmount = activeTableOrder.total_amount"
                                        :class="selectedTablePayMethod === 'debit' ? 'bg-[#007AFF] text-white font-bold' :
                                            'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                        class="h-9 rounded-[8px] text-xs transition">Debit</button>
                                </div>
                            </div>

                            <!-- Tender Amount (for Cash) -->
                            <div x-show="selectedTablePayMethod === 'cash'" class="space-y-1.5 mt-3">
                                <label class="block text-xs font-semibold text-black dark:text-white">Nominal Diterima</label>
                                <input type="number" x-model.number="tableTenderAmount"
                                    class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 font-bold text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <div x-show="tableTenderAmount > activeTableOrder.total_amount"
                                    class="text-xs text-[#34C759] font-bold">
                                    Kembalian: <span
                                        x-text="formatRupiah(tableTenderAmount - activeTableOrder.total_amount)"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showTablePaymentModal = false"
                            class="h-10 px-4 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                        <template x-if="activeTableOrder.is_paid">
                            <button type="button" @click="submitPayTableOrder()"
                                :disabled="isProcessing"
                                class="h-10 px-5 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Selesaikan & Bersihkan Meja</span>
                            </button>
                        </template>
                        <template x-if="!activeTableOrder.is_paid">
                            <button type="button" @click="submitPayTableOrder()"
                                :disabled="tableTenderAmount < activeTableOrder.total_amount || isProcessing"
                                class="h-10 px-5 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40">
                                Konfirmasi Lunas
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 13. ALPINE.JS POS STATE ENGINE (100% PRESERVED)       -->
    <!-- ===================================================== -->
    <script>
        function posApp() {
            return {
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                selectedLocationId: '{{ $selectedLocationId }}',
                activeShift: @json($activeShift),
                allProducts: @json($products),
                filteredProducts: [],
                currentPage: 1,
                perPage: 8,
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredProducts.length / this.perPage));
                },
                get paginatedProducts() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredProducts.slice(start, start + this.perPage);
                },
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                    }
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                    }
                },
                goToPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                    }
                },
                customers: @json($customers),
                heldOrders: @json($heldOrders),
                selectedCategory: 'all',
                selectedTypeFilter: 'all',
                searchQuery: '',
                isDarkMode: true,
                mobileMenuOpen: false,
                isFullscreen: false,
                isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,

                // Cart state
                hasDineIn: {{ ($business && $business->hasDineInFeature()) ? 'true' : 'false' }},
                isFoodIndustry: {{ ($business && $business->isFoodIndustry()) ? 'true' : 'false' }},
                cart: [],
                selectedCustomerId: '',
                activeCustomer: null,
                customerSearchQuery: '',
                customerDropdownOpen: false,
                orderType: '{{ ($business && $business->hasDineInFeature()) ? 'dine_in' : 'takeaway' }}',
                salesChannel: '{{ ($business && $business->hasDineInFeature()) ? 'dine_in' : 'takeaway' }}',
                externalOrderRef: '',
                voucherCode: '',
                voucherDiscount: 0,
                discountType: 'fixed',
                discountValue: 0,
                redeemPoints: false,
                pointsDiscount: 0,

                // Supervisor PIN authorization state
                maxCashierDiscountPercent: {{ (float) ($business->pos_max_cashier_discount_percent ?? 0) }},
                canBypassSupervisor: {{ $canBypassSupervisor ?? false ? 'true' : 'false' }},
                supervisorApprovedForOrder: false,
                showSupervisorPinModal: false,
                supervisorPinInput: '',
                supervisorPinError: '',
                supervisorPinLoading: false,
                supervisorAttemptsRemaining: null,

                // F&B Tables & QR state
                tables: @json($tables ?? []),
                todayReservations: {{ \Illuminate\Support\Js::from($todayReservations ?? []) }},
                todayReservationsCount: {{ (int) ($todayReservationsCount ?? 0) }},
                tableModalTab: 'tables',
                isSeatingReservation: false,
                pendingQrCount: {{ $pendingQrOrdersCount ?? 0 }},
                incomingQrOrders: [],
                selectedTable: null,
                activeTableOrderId: null,
                activeTableOrderNumber: null,
                activeTableCustomerName: null,
                showIncomingOrdersModal: false,
                showTablesModal: false,
                showRejectReasonModal: false,
                rejectingOrder: null,
                rejectionReason: '',
                incomingPollInterval: null,
                audioCtx: null,

                // Realtime QR Notification state
                latestQrNotification: null,
                notificationProgressInterval: null,
                notificationProgressPercent: 100,
                isNotificationPaused: false,
                knownIncomingOrderIds: new Set(),
                isInitialIncomingFetch: true,

                // Cashier Modifier Modal state
                showModifierModal: false,
                activeModifierProduct: null,
                selectedModifiers: {},
                modifierItemNotes: '',
                modifierItemQty: 1,

                // Industry vertical fields (Bengkel / Laundry / Apotek)
                technicians: @json($technicians ?? []),
                showServiceVerticalModal: false,
                serviceVerticalTab: '{{ in_array(strtolower((string)($business->template_code ?? $business->industry_category ?? '')), ['service_laundry', 'laundry'], true) ? 'laundry' : 'workshop' }}',
                // Bengkel
                vehicleLicensePlate: '',
                vehicleModel: '',
                vehicleMileage: null,
                technicianId: '',
                serviceNotes: '',
                // Laundry
                laundryWeightKg: null,
                rackLocation: '',
                estimatedCompletionAt: '',
                laundryStatus: 'received',
                // Item detail modal for Apothecary (batch, ED, dosage, notes)
                showItemDetailModal: false,
                editingItemIndex: null,
                editingItemNotes: '',
                editingItemBatchNumber: '',
                editingItemExpiredDate: '',
                editingItemDosage: '',

                // Table payment modal state
                showTablePaymentModal: false,
                activeTableOrder: null,
                tableTenderAmount: 0,
                selectedTablePayMethod: 'cash',

                // Modals
                showPosSettingsModal: false,
                showPaymentModal: false,
                showSuccessModal: false,
                showOpenShiftModal: false,
                showCloseShiftModal: false,
                showHeldOrdersModal: false,
                showHoldPromptModal: false,
                holdOrderLabel: '',
                showCashMovementModal: false,
                showCustomerModal: false,
                showScannerPermission: false,
                showBarcodeScanner: false,
                scannerStarting: false,
                scannerError: '',
                scannerDevices: [],
                selectedScannerDeviceId: '',
                scannerStream: null,
                scannerDetector: null,
                scannerFrameId: null,
                scannerBusy: false,
                mobileCartOpen: false,

                // Payment & Multi-EDC state
                storeEdcTerminals: @json($storeEdcTerminals ?? []),
                selectedPayMethod: 'cash',
                selectedEdcTerminalId: '',
                paymentRefNumber: '',
                currentTenderAmount: 0,
                isSplitPayment: false,
                splitPaymentRows: [],

                // Dynamic QRIS Cooca Pay (TriPay Gateway) state
                showQrisModal: false,
                activeQrisOrder: null,
                activeQrisPayment: null,
                qrisCountdown: 900,
                qrisCountdownFormatted: '15:00',
                qrisCountdownTimer: null,
                qrisPollingTimer: null,
                isCheckingQrisStatus: false,
                isCancellingQris: false,
                isSimulatingQris: false,
                get totalSplitPaid() {
                    return this.splitPaymentRows.reduce((sum, r) => sum + (Number(r.amount) || 0), 0);
                },
                get splitRemainingAmount() {
                    return Math.max(0, this.grandTotal - this.totalSplitPaid);
                },
                get splitChangeAmount() {
                    return Math.max(0, this.totalSplitPaid - this.grandTotal);
                },
                initSplitPayment() {
                    this.isSplitPayment = true;
                    if (this.splitPaymentRows.length === 0) {
                        this.splitEvenly(2);
                    }
                },
                splitEvenly(n) {
                    if (n < 2) return;
                    const total = Math.round(this.grandTotal);
                    const base = Math.floor(total / n);
                    const remainder = total - (base * n);
                    const methods = ['cash', 'qris', 'transfer', 'edc_debit', 'edc_credit'];
                    this.splitPaymentRows = [];
                    for (let i = 0; i < n; i++) {
                        this.splitPaymentRows.push({
                            payment_method: methods[i % methods.length] || 'cash',
                            amount: i === 0 ? (base + remainder) : base,
                            store_edc_terminal_id: '',
                            reference_number: ''
                        });
                    }
                },
                sanitizeSplitAmount(idx) {
                    if (this.splitPaymentRows[idx]) {
                        const val = Number(this.splitPaymentRows[idx].amount) || 0;
                        this.splitPaymentRows[idx].amount = Math.max(0, val);
                        if (this.splitPaymentRows.length === 2) {
                            const otherIdx = idx === 0 ? 1 : 0;
                            const remaining = Math.max(0, Math.round(this.grandTotal) - this.splitPaymentRows[idx].amount);
                            this.splitPaymentRows[otherIdx].amount = remaining;
                        }
                    }
                },
                addSplitRow() {
                    if (this.splitPaymentRows.length >= 5) {
                        const maxMsg = window.COOCA_I18N?.max_split_rows_reached || '{{ __('pos.max_split_rows_reached') }}';
                        if (typeof AppAlert !== 'undefined') {
                            AppAlert.warning(maxMsg);
                        } else if (window.AppAlert) {
                            window.AppAlert.warning(maxMsg);
                        }
                        return;
                    }
                    const rem = this.splitRemainingAmount;
                    this.splitPaymentRows.push({
                        payment_method: 'cash',
                        amount: rem > 0 ? rem : 0,
                        store_edc_terminal_id: '',
                        reference_number: ''
                    });
                },
                removeSplitRow(idx) {
                    if (this.splitPaymentRows.length > 1) {
                        this.splitPaymentRows.splice(idx, 1);
                    }
                },
                fillRemainingSplit(idx) {
                    if (this.splitPaymentRows[idx]) {
                        this.splitPaymentRows[idx].amount = Math.max(0, (Number(this.splitPaymentRows[idx].amount) || 0) + this.splitRemainingAmount);
                    }
                },
                isProcessing: false,
                directPrinting: false,
                kitchenPrinting: false,
                posAutoSendKds: {{ (isset($business) && $business->autoSendToKds()) ? 'true' : 'false' }},
                isSendingToKitchen: false,
                lastOrderSentToKds: false,
                drawerPopping: false,
                lastCompletedOrder: null,
                lastReceiptUrl: '#',
                lastReceiptImageUrl: '#',
                lastWhatsAppUrl: '#',
                sendingWaBot: false,
                waBotSent: false,
                waBotFeedback: '',
                waBotFeedbackSuccess: false,

                // Shift state
                shiftOpeningCash: 100000,
                shiftNotes: '',
                shiftActualCash: 0,
                shiftSummary: {
                    opening_cash: 0,
                    cash_sales: 0,
                    cash_in: 0,
                    cash_out: 0,
                    expected_cash: 0
                },

                // Cash movement
                cashMovementType: 'cash_out',
                cashMovementCategory: 'operational',
                cashMovementOtherDesc: '',
                cashMovementAmount: 50000,
                cashMovementReason: '',
                newCustomer: {
                    name: '',
                    phone: ''
                },
                customerFormError: '',
                isCreatingCustomer: false,

                initPos() {
                    this.initTheme();
                    this.filteredProducts = this.allProducts;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });

                    // Connectivity status listeners (Online / Offline detection)
                    window.addEventListener('online', () => {
                        this.isOnline = true;
                        const onlineMsg = window.COOCA_I18N?.connection_restored || '{{ __('pos.connection_restored') }}';
                        if (typeof AppAlert !== 'undefined') {
                            AppAlert.success(onlineMsg);
                        } else if (window.AppAlert) {
                            window.AppAlert.success(onlineMsg);
                        }
                    });
                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                        const offlineMsg = window.COOCA_I18N?.connection_lost || '{{ __('pos.connection_lost') }}';
                        if (typeof AppAlert !== 'undefined') {
                            AppAlert.warning(offlineMsg);
                        } else if (window.AppAlert) {
                            window.AppAlert.warning(offlineMsg);
                        }
                    });

                    // Request desktop notification permission if supported
                    this.requestNotificationPermission();

                    // Unlock Web Audio API on first user interaction anywhere
                    this.setupAudioUnlock();

                    // Initial fetch & smart polling (Page Visibility Aware) - F&B Dine-In Only
                    if (this.hasDineIn) {
                        this.fetchIncomingOrders();
                        const startIncomingPolling = () => {
                            if (this.incomingPollInterval) clearInterval(this.incomingPollInterval);
                            this.incomingPollInterval = setInterval(() => {
                                if (!document.hidden) {
                                    this.fetchIncomingOrders();
                                }
                            }, 5000);
                        };
                        startIncomingPolling();

                        document.addEventListener('visibilitychange', () => {
                            if (document.hidden) {
                                if (this.incomingPollInterval) {
                                    clearInterval(this.incomingPollInterval);
                                    this.incomingPollInterval = null;
                                }
                            } else {
                                this.fetchIncomingOrders();
                                startIncomingPolling();
                            }
                        });
                    }

                    // Initialize fullscreen listeners
                    this.initFullscreen();

                    // Check URL query parameters for auto-selection (from Storefront Reservations / Tables floor plan)
                    const urlParams = new URLSearchParams(window.location.search);
                    const paramTableId = urlParams.get('table_id');
                    const paramReservationId = urlParams.get('reservation_id');
                    const openModalParam = urlParams.get('open_modal');

                    if (paramTableId) {
                        const tbl = (this.tables || []).find(t => t.id === paramTableId || t.table_number == paramTableId);
                        if (tbl) {
                            this.selectTableForCart(tbl);
                        }
                    }
                    if (openModalParam === 'reservations' || paramReservationId) {
                        this.openTablesModal('reservations');
                    }
                },

                initTheme() {
                    const saved = localStorage.getItem('cooca-pos-theme');
                    if (saved) {
                        this.isDarkMode = saved === 'dark';
                    } else {
                        this.isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    }
                    this.applyTheme();
                },

                toggleTheme() {
                    this.isDarkMode = !this.isDarkMode;
                    localStorage.setItem('cooca-pos-theme', this.isDarkMode ? 'dark' : 'light');
                    this.applyTheme();
                },

                applyTheme() {
                    if (this.isDarkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },

                initFullscreen() {
                    const handleFs = () => {
                        this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
                    };
                    document.addEventListener('fullscreenchange', handleFs);
                    document.addEventListener('webkitfullscreenchange', handleFs);
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                        const elem = document.documentElement;
                        if (elem.requestFullscreen) {
                            elem.requestFullscreen().catch(() => {});
                        } else if (elem.webkitRequestFullscreen) {
                            elem.webkitRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen().catch(() => {});
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        }
                    }
                },

                formatRupiah(val) {
                    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
                },

                filterProducts() {
                    let res = this.allProducts;
                    if (this.selectedTypeFilter === 'goods') {
                        res = res.filter(p => p.type !== 'service');
                    } else if (this.selectedTypeFilter === 'service') {
                        res = res.filter(p => p.type === 'service');
                    }
                    if (this.selectedCategory !== 'all') {
                        res = res.filter(p => p.category_id === this.selectedCategory);
                    }
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        res = res.filter(p => (p.name && p.name.toLowerCase().includes(q)) || (p.code && p.code
                        .toLowerCase().includes(q)));
                    }
                    this.filteredProducts = res;
                    this.currentPage = 1;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                handleBarcodeOrSearch() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) return;

                    // Direct match by barcode / code
                    const exact = this.allProducts.find(p => p.code && p.code.toLowerCase() === q);
                    if (exact) {
                        this.addToCart(exact);
                        this.searchQuery = '';
                        this.filterProducts();
                        return;
                    }
                    this.filterProducts();
                },

                requestBarcodeScannerAccess() {
                    if (localStorage.getItem('cooca-camera-permission-intro-seen') === '1') {
                        this.openBarcodeScanner();
                        return;
                    }
                    this.showScannerPermission = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                confirmBarcodeScannerAccess() {
                    localStorage.setItem('cooca-camera-permission-intro-seen', '1');
                    this.showScannerPermission = false;
                    this.openBarcodeScanner();
                },

                async openBarcodeScanner() {
                    this.showBarcodeScanner = true;
                    this.scannerError = '';
                    await this.$nextTick();

                    if (!('BarcodeDetector' in window)) {
                        this.scannerError =
                            'Browser ini belum mendukung scan barcode kamera. Gunakan Chrome/Android terbaru atau scanner USB/Bluetooth.';
                        return;
                    }

                    try {
                        const supportedFormats = await BarcodeDetector.getSupportedFormats();
                        const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'codabar', 'itf']
                            .filter(format => supportedFormats.includes(format));
                        this.scannerDetector = new BarcodeDetector({
                            formats
                        });
                        await this.loadScannerDevices();
                        await this.startBarcodeScanner();
                    } catch (error) {
                        this.scannerError = this.scannerMessage(error);
                    }
                },

                async loadScannerDevices() {
                    if (!navigator.mediaDevices?.enumerateDevices) return;
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.scannerDevices = devices.filter(device => device.kind === 'videoinput');
                    if (!this.selectedScannerDeviceId && this.scannerDevices.length > 0) {
                        this.selectedScannerDeviceId = this.scannerDevices.find(device => /back|rear|environment/i.test(
                            device.label))?.deviceId || this.scannerDevices[0].deviceId;
                    }
                },

                async startBarcodeScanner() {
                    this.stopBarcodeScanner();
                    this.scannerError = '';
                    this.scannerStarting = true;

                    try {
                        const videoConstraints = this.selectedScannerDeviceId ?
                            {
                                deviceId: {
                                    exact: this.selectedScannerDeviceId
                                },
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 720
                                }
                            } :
                            {
                                facingMode: {
                                    ideal: 'environment'
                                },
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 720
                                }
                            };
                        this.scannerStream = await navigator.mediaDevices.getUserMedia({
                            video: videoConstraints,
                            audio: false
                        });
                        this.$refs.barcodeVideo.srcObject = this.scannerStream;
                        await this.$refs.barcodeVideo.play();
                        await this.loadScannerDevices();
                        this.scannerStarting = false;
                        this.scanBarcodeFrame();
                    } catch (error) {
                        this.scannerStarting = false;
                        this.scannerError = this.scannerMessage(error);
                    }
                },

                async scanBarcodeFrame() {
                    if (!this.showBarcodeScanner || !this.scannerDetector || !this.$refs.barcodeVideo) return;
                    if (!this.scannerBusy && this.$refs.barcodeVideo.readyState >= 2) {
                        this.scannerBusy = true;
                        try {
                            const results = await this.scannerDetector.detect(this.$refs.barcodeVideo);
                            const barcode = results.find(result => result.rawValue)?.rawValue;
                            if (barcode) {
                                this.handleScannedBarcode(barcode);
                                return;
                            }
                        } catch (error) {
                            this.scannerError = 'Barcode belum terbaca. Posisikan barcode di dalam kotak.';
                        } finally {
                            this.scannerBusy = false;
                        }
                    }
                    this.scannerFrameId = requestAnimationFrame(() => this.scanBarcodeFrame());
                },

                handleScannedBarcode(value) {
                    const normalized = String(value).trim().toLowerCase();
                    const product = this.allProducts.find(item => item.code && item.code.trim().toLowerCase() ===
                        normalized);
                    if (!product) {
                        this.scannerError = 'Barcode ' + value + ' belum terdaftar sebagai produk.';
                        return;
                    }
                    this.addToCart(product);
                    this.searchQuery = '';
                    this.filterProducts();
                    this.closeBarcodeScanner();
                },

                closeBarcodeScanner() {
                    this.showBarcodeScanner = false;
                    this.stopBarcodeScanner();
                },

                stopBarcodeScanner() {
                    if (this.scannerFrameId) cancelAnimationFrame(this.scannerFrameId);
                    this.scannerFrameId = null;
                    this.scannerBusy = false;
                    if (this.scannerStream) this.scannerStream.getTracks().forEach(track => track.stop());
                    this.scannerStream = null;
                    if (this.$refs.barcodeVideo) this.$refs.barcodeVideo.srcObject = null;
                },

                scannerMessage(error) {
                    if (error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError')
                    return 'Akses kamera ditolak. Izinkan kamera di browser lalu coba lagi.';
                    if (error?.name === 'NotFoundError') return 'Kamera tidak ditemukan pada perangkat ini.';
                    if (error?.name === 'NotReadableError') return 'Kamera sedang digunakan aplikasi lain.';
                    if (window.isSecureContext === false) return 'Scanner kamera memerlukan HTTPS atau localhost.';
                    return 'Kamera tidak dapat dibuka. Periksa izin kamera lalu coba lagi.';
                },

                getProductPrice(product) {
                    if (!product) return 0;
                    if (product.channel_prices && product.channel_prices[this.salesChannel] !== undefined) {
                        return Number(product.channel_prices[this.salesChannel]);
                    }
                    return Number(product.selling_price || 0);
                },

                setSalesChannel(channel) {
                    this.salesChannel = channel;
                    if (channel === 'dine_in') {
                        this.orderType = 'dine_in';
                    } else {
                        this.orderType = 'takeaway';
                    }

                    // Recalculate existing items in cart with the new channel price
                    this.cart.forEach(item => {
                        const product = this.allProducts.find(p => p.id === item.product_id);
                        if (product) {
                            const basePrice = this.getProductPrice(product);
                            const delta = Number(item.modifier_delta || 0);
                            item.unit_price = basePrice + delta;
                        }
                    });
                },

                addToCart(product) {
                    const existing = this.cart.find(item => item.product_id === product.id && (!item.selected_modifiers || item.selected_modifiers.length === 0));
                    if (existing) {
                        const current = parseFloat(existing.quantity) || 0;
                        existing.quantity = parseFloat((current + 1).toFixed(4));
                    } else {
                        const unitSymbol = product.output_unit?.symbol || product.output_unit?.code || product.output_unit
                            ?.name || '';
                        this.cart.push({
                            product_id: product.id,
                            product_name: product.name,
                            unit_price: Number(this.getProductPrice(product)),
                            modifier_delta: 0,
                            unit_symbol: unitSymbol,
                            quantity: 1,
                            discount_amount: 0,
                            notes: '',
                            batch_number: '',
                            expired_date: '',
                            dosage_instructions: ''
                        });
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                incrementQty(idx) {
                    const current = parseFloat(this.cart[idx].quantity) || 0;
                    this.cart[idx].quantity = parseFloat((current + 1).toFixed(4));
                },

                decrementQty(idx) {
                    const current = parseFloat(this.cart[idx].quantity) || 0;
                    if (current > 1) {
                        this.cart[idx].quantity = parseFloat((current - 1).toFixed(4));
                    } else {
                        this.removeFromCart(idx);
                    }
                },

                updateItemQty(idx, val) {
                    if (val === '' || val === null) {
                        return;
                    }
                    const num = parseFloat(val);
                    if (!isNaN(num) && num >= 0) {
                        this.cart[idx].quantity = num;
                    }
                },

                normalizeItemQty(idx) {
                    const current = parseFloat(this.cart[idx].quantity);
                    if (isNaN(current) || current <= 0) {
                        this.cart[idx].quantity = 1;
                    } else {
                        this.cart[idx].quantity = parseFloat(current.toFixed(4));
                    }
                },

                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                async clearCart() {
                    const confirmed = await AppAlert.confirm({
                        title: 'Kosongkan Keranjang?',
                        message: 'Semua item pesanan yang dipilih akan dihapus dari keranjang transaksi.',
                        type: 'danger',
                        confirmText: 'Ya, Kosongkan',
                        cancelText: 'Batal'
                    });
                    if (confirmed) {
                        this.cart = [];
                        this.discountValue = 0;
                        this.discountType = 'fixed';
                        this.voucherDiscount = 0;
                        this.pointsDiscount = 0;
                        this.redeemPoints = false;
                        this.supervisorApprovedForOrder = false;
                    }
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item
                        .unit_price) || 0)), 0);
                },

                get orderDiscountAmount() {
                    const value = Math.max(0, Number(this.discountValue || 0));
                    return this.discountType === 'percentage' ?
                        Math.min(this.subtotal, (this.subtotal * Math.min(100, value)) / 100) :
                        Math.min(this.subtotal, value);
                },

                get taxAmount() {
                    @if ($business->pos_enable_tax)
                        return Math.max(0, this.subtotal - this.orderDiscountAmount - this.voucherDiscount - this
                            .pointsDiscount) * ({{ (float) $business->pos_tax_percent }} / 100);
                    @else
                        return 0;
                    @endif
                },

                get serviceChargeAmount() {
                    @if ($business->pos_enable_service_charge)
                        return Math.max(0, this.subtotal - this.orderDiscountAmount - this.voucherDiscount - this
                            .pointsDiscount) * ({{ (float) $business->pos_service_charge_percent }} / 100);
                    @else
                        return 0;
                    @endif
                },

                get grandTotal() {
                    const raw = this.subtotal - this.orderDiscountAmount - this.voucherDiscount - this.pointsDiscount +
                        this.taxAmount + this.serviceChargeAmount;
                    return Math.max(0, Math.round(raw));
                },

                get totalTendered() {
                    return Number(this.currentTenderAmount || 0);
                },

                get filteredCustomers() {
                    if (!this.customerSearchQuery || !this.customerSearchQuery.trim()) {
                        return this.customers;
                    }
                    const q = this.customerSearchQuery.toLowerCase().trim();
                    return this.customers.filter(c => {
                        const nameMatch = c.name && c.name.toLowerCase().includes(q);
                        const phoneMatch = c.phone && c.phone.toLowerCase().includes(q);
                        const tierMatch = c.membership_tier && c.membership_tier.toLowerCase().includes(q);
                        return nameMatch || phoneMatch || tierMatch;
                    });
                },

                selectCustomer(cust) {
                    if (!cust) {
                        this.selectedCustomerId = '';
                        this.activeCustomer = null;
                    } else {
                        this.selectedCustomerId = cust.id;
                        this.activeCustomer = cust;
                    }
                    this.redeemPoints = false;
                    this.pointsDiscount = 0;
                    this.customerDropdownOpen = false;
                    this.customerSearchQuery = '';
                },

                onCustomerSelected() {
                    this.activeCustomer = this.customers.find(c => c.id === this.selectedCustomerId) || null;
                    this.redeemPoints = false;
                    this.pointsDiscount = 0;
                },

                openCustomerModal() {
                    this.customerFormError = '';
                    this.newCustomer = {
                        name: '',
                        phone: ''
                    };
                    this.showCustomerModal = true;
                    this.$nextTick(() => this.$refs.quickCustomerName?.focus());
                },

                async createQuickCustomer() {
                    this.customerFormError = '';
                    this.isCreatingCustomer = true;

                    try {
                        const response = await fetch('{{ route('customers.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.newCustomer)
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            const validationMessage = data.errors ?
                                Object.values(data.errors).flat()[0] :
                                data.message;
                            throw new Error(validationMessage || 'Pelanggan gagal disimpan.');
                        }

                        this.customers.push(data.customer);
                        this.selectCustomer(data.customer);
                        this.showCustomerModal = false;
                        this.newCustomer = {
                            name: '',
                            phone: ''
                        };
                    } catch (error) {
                        this.customerFormError = error.message || 'Pelanggan gagal disimpan.';
                    } finally {
                        this.isCreatingCustomer = false;
                    }
                },

                togglePointsRedemption() {
                    if (!this.activeCustomer) return;
                    this.redeemPoints = !this.redeemPoints;
                    if (this.redeemPoints) {
                        const maxPointsDiscount = this.activeCustomer.points_balance * 100;
                        this.pointsDiscount = Math.min(this.subtotal, maxPointsDiscount);
                    } else {
                        this.pointsDiscount = 0;
                    }
                },

                async applyVoucher() {
                    const code = this.voucherCode ? this.voucherCode.trim() : '';
                    if (!code) {
                        AppAlert.warning(window.COOCA_I18N?.enter_voucher_code_first || '{{ __('pos.enter_voucher_code_first') }}');
                        return;
                    }
                    if (this.subtotal <= 0) {
                        AppAlert.warning(window.COOCA_I18N?.empty_cart_warning || '{{ __('pos.empty_cart_warning') }}');
                        return;
                    }

                    try {
                        const res = await fetch("{{ route('pos.validate-voucher') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                code: code,
                                subtotal: this.subtotal,
                                customer_id: this.selectedCustomerId || null
                            })
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            this.voucherDiscount = 0;
                            throw new Error(data.message || 'Kode voucher tidak valid.');
                        }
                        this.voucherDiscount = Number(data.discount_amount) || 0;
                        AppAlert.success(data.message || "Voucher berhasil diterapkan!");
                    } catch (error) {
                        this.voucherDiscount = 0;
                        AppAlert.error(error.message || 'Voucher tidak dapat diterapkan.');
                    }
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return;

                    // Supervisor discount limit check
                    if (this.maxCashierDiscountPercent > 0 && !this.canBypassSupervisor && !this
                        .supervisorApprovedForOrder) {
                        const discountPercent = this.discountType === 'percentage' ?
                            Number(this.discountValue || 0) :
                            (this.subtotal > 0 ? (Number(this.discountValue || 0) / this.subtotal) * 100 : 0);
                        if (discountPercent > this.maxCashierDiscountPercent) {
                            this.supervisorPinInput = '';
                            this.supervisorPinError = '';
                            this.supervisorAttemptsRemaining = null;
                            this.showSupervisorPinModal = true;
                            this.$nextTick(() => {
                                this.$refs.supervisorPinInputRef?.focus();
                            });
                            return;
                        }
                    }

                    this.isSplitPayment = false;
                    this.selectedPayMethod = 'cash';
                    this.selectedEdcTerminalId = '';
                    this.paymentRefNumber = '';
                    this.splitPaymentRows = [];
                    this.currentTenderAmount = this.grandTotal;
                    this.mobileCartOpen = false;
                    this.showPaymentModal = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                async verifySupervisorPinSubmit() {
                    if (this.supervisorPinLoading) return;
                    if (!this.supervisorPinInput.trim()) {
                        this.supervisorPinError = '{{ __('pos.supervisor_pin_required') }}';
                        return;
                    }
                    this.supervisorPinLoading = true;
                    this.supervisorPinError = '';

                    try {
                        const response = await fetch("{{ route('pos.verify-pin') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                pin: this.supervisorPinInput.trim()
                            })
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            if (response.status === 429) {
                                this.supervisorAttemptsRemaining = 0;
                            }
                            throw new Error(data.message || '{{ __('pos.supervisor_pin_invalid') }}');
                        }

                        this.supervisorApprovedForOrder = true;
                        this.closeSupervisorPinModal();
                        AppAlert.success('{{ __('pos.supervisor_auth_verified') }}');
                        this.openPaymentModal();
                    } catch (error) {
                        this.supervisorPinError = error.message || '{{ __('pos.supervisor_pin_invalid') }}';
                    } finally {
                        this.supervisorPinLoading = false;
                    }
                },

                closeSupervisorPinModal() {
                    this.showSupervisorPinModal = false;
                    this.supervisorPinInput = '';
                    this.supervisorPinError = '';
                    this.supervisorAttemptsRemaining = null;
                },

                toggleMobileCart() {
                    this.mobileCartOpen = !this.mobileCartOpen;
                },

                closeMobileCart() {
                    this.mobileCartOpen = false;
                },

                async submitCheckout() {
                    if (this.isProcessing) return;

                    if (!this.activeShift) {
                        this.showOpenShiftModal = true;
                        AppAlert.warning(window.COOCA_I18N?.shift_not_open || '{{ __('pos.shift_not_open') }}');
                        return;
                    }

                    if (this.isSplitPayment) {
                        if (this.splitRemainingAmount > 0) {
                            AppAlert.warning(window.COOCA_I18N?.split_allocation_insufficient || '{{ __('pos.split_allocation_insufficient') }}');
                            return;
                        }
                    } else {
                        if (this.currentTenderAmount < this.grandTotal) {
                            AppAlert.warning(window.COOCA_I18N?.payment_amount_insufficient || '{{ __('pos.payment_amount_insufficient') }}');
                            return;
                        }
                    }

                    this.isProcessing = true;

                    const paymentsPayload = this.isSplitPayment ?
                        this.splitPaymentRows.map(r => ({
                            payment_method: r.payment_method,
                            store_edc_terminal_id: (r.payment_method.startsWith('edc') && r.store_edc_terminal_id) ? r.store_edc_terminal_id : null,
                            amount: Number(r.amount) || 0,
                            reference_number: r.reference_number ? r.reference_number.trim() : null
                        })) :
                        [{
                            payment_method: this.selectedPayMethod,
                            store_edc_terminal_id: (this.selectedPayMethod.startsWith('edc') && this.selectedEdcTerminalId) ? this.selectedEdcTerminalId : null,
                            amount: Number(this.currentTenderAmount) || 0,
                            reference_number: this.paymentRefNumber ? this.paymentRefNumber.trim() : null
                        }];

                    const payload = {
                        items: this.cart.map(i => ({
                            product_id: i.product_id,
                            product_name: i.product_name,
                            unit_price: i.unit_price,
                            quantity: i.quantity,
                            discount_amount: i.discount_amount || 0,
                            notes: i.notes || null,
                            batch_number: i.batch_number || null,
                            expired_date: i.expired_date || null,
                            dosage_instructions: i.dosage_instructions || null,
                            selected_modifiers: i.selected_modifiers || []
                        })),
                        payments: paymentsPayload,
                        customer_id: this.selectedCustomerId || null,
                        customer_name_guest: this.activeTableCustomerName || null,
                        order_type: this.orderType,
                        sales_channel: this.salesChannel || 'dine_in',
                        external_order_ref: this.externalOrderRef ? this.externalOrderRef.trim() : null,
                        pos_table_id: this.selectedTable?.id || null,
                        pos_table_session_id: this.selectedTable?.active_session?.id || null,
                        existing_order_id: this.activeTableOrderId || null,
                        discount_type: this.discountType,
                        discount_value: Number(this.discountValue || 0),
                        voucher_code: this.voucherCode || null,
                        points_to_redeem: this.redeemPoints ? Math.round(this.pointsDiscount / 100) : 0,
                        location_id: this.selectedLocationId,
                        vehicle_license_plate: this.vehicleLicensePlate ? this.vehicleLicensePlate.toUpperCase().trim() : null,
                        vehicle_model: this.vehicleModel ? this.vehicleModel.trim() : null,
                        vehicle_mileage: this.vehicleMileage ? parseInt(this.vehicleMileage) : null,
                        technician_id: this.technicianId || null,
                        service_notes: this.serviceNotes ? this.serviceNotes.trim() : null,
                        laundry_weight_kg: this.laundryWeightKg ? parseFloat(this.laundryWeightKg) : null,
                        rack_location: this.rackLocation ? this.rackLocation.trim() : null,
                        estimated_completion_at: this.estimatedCompletionAt || null,
                        laundry_status: this.laundryStatus || 'received'
                    };

                    try {
                        const res = await fetch("{{ route('pos.checkout') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data.success) {
                            if (data.is_qris && data.payment && data.payment.qr_url) {
                                this.activeQrisOrder = data.order;
                                this.activeQrisPayment = data.payment;
                                this.showPaymentModal = false;
                                this.showQrisModal = true;
                                const remainingSecs = data.payment.expired_time ? Math.max(60, data.payment.expired_time - Math.floor(Date.now() / 1000)) : 900;
                                this.startQrisCountdown(remainingSecs);
                                this.startQrisPolling(data.order.id);
                                return;
                            }

                            this.handleOrderSuccess(data);
                        } else {
                            AppAlert.error('Gagal: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi kesalahan jaringan.');
                    } finally {
                        this.isProcessing = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },

                formatQrisCountdown(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
                },

                startQrisCountdown(duration = 900) {
                    if (this.qrisCountdownTimer) clearInterval(this.qrisCountdownTimer);
                    this.qrisCountdown = Math.max(0, duration);
                    this.qrisCountdownFormatted = this.formatQrisCountdown(this.qrisCountdown);
                    this.qrisCountdownTimer = setInterval(() => {
                        if (this.qrisCountdown > 0) {
                            this.qrisCountdown--;
                            this.qrisCountdownFormatted = this.formatQrisCountdown(this.qrisCountdown);
                        } else {
                            clearInterval(this.qrisCountdownTimer);
                            if (this.qrisPollingTimer) clearInterval(this.qrisPollingTimer);
                            AppAlert.warning('Waktu pembayaran QRIS telah kedaluwarsa. Kasir dapat membatalkan atau membuat ulang transaksi.');
                        }
                    }, 1000);
                },

                startQrisPolling(orderId) {
                    if (this.qrisPollingTimer) clearInterval(this.qrisPollingTimer);
                    const statusUrl = "/pos/orders/" + orderId + "/status";

                    this.qrisPollingTimer = setInterval(async () => {
                        try {
                            const resp = await fetch(statusUrl, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken
                                }
                            });
                            if (!resp.ok) return;
                            const data = await resp.json();
                            if (data.is_paid) {
                                if (this.qrisPollingTimer) clearInterval(this.qrisPollingTimer);
                                if (this.qrisCountdownTimer) clearInterval(this.qrisCountdownTimer);
                                this.showQrisModal = false;
                                this.handleOrderSuccess(data);
                                AppAlert.success(data.message || 'Pembayaran QRIS berhasil dikonfirmasi!');
                            }
                        } catch (e) {
                            // network retry silently
                        }
                    }, 3000);
                },

                async checkQrisStatusManual() {
                    if (!this.activeQrisOrder || this.isCheckingQrisStatus) return;
                    this.isCheckingQrisStatus = true;
                    try {
                        const statusUrl = "/pos/orders/" + this.activeQrisOrder.id + "/status";
                        const resp = await fetch(statusUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });
                        const data = await resp.json();
                        if (data.is_paid) {
                            if (this.qrisPollingTimer) clearInterval(this.qrisPollingTimer);
                            if (this.qrisCountdownTimer) clearInterval(this.qrisCountdownTimer);
                            this.showQrisModal = false;
                            this.handleOrderSuccess(data);
                            AppAlert.success(data.message || 'Pembayaran QRIS telah diverifikasi!');
                        } else {
                            AppAlert.info(data.message || 'Belum ada pembayaran masuk. Menunggu scan pelanggan...');
                        }
                    } catch (e) {
                        AppAlert.error('Gagal mengecek status: ' + e.message);
                    } finally {
                        this.isCheckingQrisStatus = false;
                    }
                },

                async cancelQrisPayment() {
                    if (!this.activeQrisOrder || this.isCancellingQris) return;
                    if (!confirm('Ganti metode pembayaran? Sesi QRIS ini akan dibatalkan di server sehingga kasir dapat memilih metode pembayaran lain.')) {
                        return;
                    }

                    this.isCancellingQris = true;
                    try {
                        const cancelUrl = "/pos/orders/" + this.activeQrisOrder.id + "/cancel-qris";
                        const resp = await fetch(cancelUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({ reason: 'Kasir mengganti metode pembayaran.' })
                        });
                        const data = await resp.json();
                        if (data.success) {
                            if (this.qrisPollingTimer) clearInterval(this.qrisPollingTimer);
                            if (this.qrisCountdownTimer) clearInterval(this.qrisCountdownTimer);
                            this.showQrisModal = false;
                            this.activeQrisOrder = null;
                            this.activeQrisPayment = null;
                            this.showPaymentModal = true;
                            AppAlert.info('Sesi QRIS dibatalkan. Silakan pilih metode pembayaran lain.');
                        } else {
                            AppAlert.error(data.message || 'Gagal membatalkan sesi QRIS.');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi kesalahan jaringan: ' + e.message);
                    } finally {
                        this.isCancellingQris = false;
                    }
                },

                async simulateQrisPayment() {
                    if (!this.activeQrisOrder || this.isSimulatingQris) return;
                    this.isSimulatingQris = true;
                    try {
                        const simUrl = "/pos/orders/" + this.activeQrisOrder.id + "/simulate-qris";
                        const resp = await fetch(simUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });
                        const data = await resp.json();
                        if (data.success && data.is_paid) {
                            if (this.qrisPollingTimer) clearInterval(this.qrisPollingTimer);
                            if (this.qrisCountdownTimer) clearInterval(this.qrisCountdownTimer);
                            this.showQrisModal = false;
                            this.handleOrderSuccess(data);
                            AppAlert.success('[Sandbox] Simulasi pembayaran QRIS sukses!');
                        } else {
                            AppAlert.error(data.message || 'Gagal simulasi pembayaran QRIS.');
                        }
                    } catch (e) {
                        AppAlert.error('Gagal simulasi: ' + e.message);
                    } finally {
                        this.isSimulatingQris = false;
                    }
                },

                printQrisSlip() {
                    if (!this.activeQrisPayment?.qr_url) return;
                    const orderNum = this.activeQrisOrder?.order_number || '';
                    const totalFormatted = this.formatRupiah(this.activeQrisOrder?.total_amount || 0);
                    const qrUrl = this.activeQrisPayment.qr_url;
                    const win = window.open('', '_blank', 'width=460,height=620');
                    if (!win) {
                        AppAlert.warning('Izinkan popup browser untuk membuka slip QRIS.');
                        return;
                    }
                    win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>QRIS TriPay - Order #${orderNum}</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;text-align:center;padding:24px;color:#111;margin:0;background:#fff}.header{margin-bottom:12px}.brand{font-size:16px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px}.sub{font-size:12px;color:#666;margin-top:4px}.badge{display:inline-block;padding:4px 10px;border-radius:999px;background:#e8f4fd;color:#007aff;font-size:11px;font-weight:700;margin-top:8px}.amount-box{margin:16px auto;padding:12px;border-radius:12px;background:#f8f9fa;border:1px dashed #d1d5db;max-width:320px}.amount-label{font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700}.amount-val{font-size:24px;font-weight:900;color:#007aff;margin-top:2px}.qr-box{margin:12px auto;padding:12px;border-radius:16px;border:1px solid #e5e7eb;display:inline-block;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.05)}.qr-img{width:240px;height:240px;object-fit:contain;display:block}.footer{margin-top:14px;font-size:11.5px;color:#4b5563;line-height:1.5}.actions{margin-top:18px}.btn{padding:9px 20px;border-radius:10px;background:#007aff;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer}@media print{.actions{display:none}body{padding:0}}</style></head><body><div class="header"><div class="brand">{{ $business->name ?? 'COOCA KASIR' }}</div><div class="sub">Pembayaran QRIS Dinamis • Pesanan #${orderNum}</div><span class="badge">NMID / Gateway TriPay QRIS</span></div><div class="amount-box"><div class="amount-label">Total Tagihan</div><div class="amount-val">${totalFormatted}</div></div><div class="qr-box"><img src="${qrUrl}" class="qr-img" alt="QRIS Code" /></div><div class="footer">Scan dengan GoPay, OVO, DANA, BCA, Livin, BRI, ShopeePay, atau m-Banking apa saja.</div><div class="actions"><button class="btn" onclick="window.print()">Cetak Slip QR</button></div></body></html>`);
                    win.document.close();
                },

                handleOrderSuccess(data) {
                    this.lastCompletedOrder = data.order;
                    this.lastReceiptUrl = data.receipt_url;
                    this.lastReceiptImageUrl = data.receipt_image_url || ('/receipt/' + data.order.id + '/image');
                    this.lastWhatsAppUrl = data.whatsapp_url;
                    this.waBotSent = data.whatsapp_bot_sent || false;
                    this.waBotFeedback = this.waBotSent ? 'Struk otomatis terkirim ke WhatsApp!' : '';
                    this.waBotFeedbackSuccess = this.waBotSent;
                    this.lastOrderSentToKds = !!data.sent_to_kds;
                    this.showPaymentModal = false;
                    this.showSuccessModal = true;
                    this.cart = [];
                    this.isSplitPayment = false;
                    this.splitPaymentRows = [];
                    this.selectedEdcTerminalId = '';
                    this.paymentRefNumber = '';
                    this.selectedTable = null;
                    this.activeTableOrderId = null;
                    this.activeTableOrderNumber = null;
                    this.activeTableCustomerName = null;
                    this.fetchTables();
                    this.fetchIncomingOrders();
                    this.voucherCode = '';
                    this.voucherDiscount = 0;
                    this.discountValue = 0;
                    this.discountType = 'fixed';
                    this.pointsDiscount = 0;
                    this.redeemPoints = false;
                    this.supervisorApprovedForOrder = false;

                    // Reset industry vertical fields
                    this.vehicleLicensePlate = '';
                    this.vehicleModel = '';
                    this.vehicleMileage = null;
                    this.technicianId = '';
                    this.serviceNotes = '';
                    this.laundryWeightKg = null;
                    this.rackLocation = '';
                    this.estimatedCompletionAt = '';
                    this.laundryStatus = 'received';
                    this.salesChannel = 'dine_in';
                    this.externalOrderRef = '';

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                openItemDetailModal(idx) {
                    this.editingItemIndex = idx;
                    const item = this.cart[idx];
                    if (!item) return;
                    this.editingItemNotes = item.notes || '';
                    this.editingItemBatchNumber = item.batch_number || '';
                    this.editingItemExpiredDate = item.expired_date || '';
                    this.editingItemDosage = item.dosage_instructions || '';
                    this.showItemDetailModal = true;
                },

                saveItemDetail() {
                    if (this.editingItemIndex !== null && this.cart[this.editingItemIndex]) {
                        this.cart[this.editingItemIndex].notes = this.editingItemNotes.trim();
                        this.cart[this.editingItemIndex].batch_number = this.editingItemBatchNumber.trim();
                        this.cart[this.editingItemIndex].expired_date = this.editingItemExpiredDate || null;
                        this.cart[this.editingItemIndex].dosage_instructions = this.editingItemDosage.trim();
                    }
                    this.showItemDetailModal = false;
                },

                resetForNewOrder() {
                    this.showSuccessModal = false;
                    this.lastCompletedOrder = null;
                    this.waBotSent = false;
                    this.waBotFeedback = '';
                    this.lastOrderSentToKds = false;
                },

                sendWhatsAppBotReceipt() {
                    if (!this.lastCompletedOrder || this.sendingWaBot) return;
                    this.sendingWaBot = true;
                    this.waBotFeedback = '';
                    const isResend = !!this.waBotSent;
                    const customer = this.customers.find(c => c.id === this.selectedCustomerId);
                    fetch("{{ url('/whatsapp/orders') }}/" + this.lastCompletedOrder.id + "/receipt", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                phone: (customer ? customer.phone : '') || this.lastCompletedOrder
                                    ?.customer_phone_guest || '',
                                force: isResend
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.waBotFeedback = data.message;
                            this.waBotFeedbackSuccess = data.success;
                            if (data.success) {
                                this.waBotSent = true;
                            }
                        })
                        .catch(() => {
                            this.waBotFeedback = 'Gagal menghubungi server WhatsApp';
                            this.waBotFeedbackSuccess = false;
                        })
                        .finally(() => {
                            this.sendingWaBot = false;
                        });
                },

                async directPrintReceipt(orderId) {
                    if (!orderId || this.directPrinting) return;
                    this.directPrinting = true;
                    try {
                        const res = await fetch(`/pos/orders/${orderId}/direct-print`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({ mode: 'direct' })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (data.mode === 'agent_dispatch' && data.payload_base64) {
                                try {
                                    const agentRes = await fetch('http://127.0.0.1:9898/print', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({
                                            payload_base64: data.payload_base64,
                                            job_id: data.job_id
                                        })
                                    });
                                    if (agentRes.ok) {
                                        AppAlert.success('Struk berhasil dicetak via Local POS Agent (USB/Bluetooth).');
                                    } else {
                                        AppAlert.success(data.message || 'Job antrean printer berhasil dibuat.');
                                    }
                                } catch (agentErr) {
                                    AppAlert.success(data.message || 'Job antrean printer berhasil dibuat.');
                                }
                            } else {
                                AppAlert.success(data.message || 'Struk berhasil dikirim ke printer thermal.');
                            }
                        } else {
                            AppAlert.error(data.message || 'Gagal mengirim cetakan ke printer.');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi gangguan jaringan printer.');
                    } finally {
                        this.directPrinting = false;
                    }
                },

                async printKitchenTickets(orderId) {
                    if (!orderId || this.kitchenPrinting) return;
                    this.kitchenPrinting = true;
                    try {
                        const res = await fetch(`/pos/orders/${orderId}/kitchen-print`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success(data.message || 'Tiket pesanan dapur berhasil dikirim.');
                        } else {
                            AppAlert.error(data.message || 'Gagal mencetak tiket dapur.');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi gangguan komunikasi dengan printer dapur.');
                    } finally {
                        this.kitchenPrinting = false;
                    }
                },

                async toggleAutoKds() {
                    const newStatus = !this.posAutoSendKds;
                    try {
                        const res = await fetch("{{ route('pos.toggle-auto-kds') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                pos_auto_send_kds: newStatus
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.posAutoSendKds = data.pos_auto_send_kds;
                            if (typeof AppAlert !== 'undefined') {
                                AppAlert.success(data.message);
                            }
                        } else {
                            if (typeof AppAlert !== 'undefined') {
                                AppAlert.error(data.message || 'Gagal mengubah pengaturan Auto KDS.');
                            }
                        }
                    } catch (e) {
                        if (typeof AppAlert !== 'undefined') {
                            AppAlert.error('Terjadi gangguan jaringan saat mengubah pengaturan KDS.');
                        }
                    }
                },

                async sendCartToKitchen() {
                    if (this.cart.length === 0 || this.isSendingToKitchen) return;

                    const confirmed = await AppAlert.confirm({
                        title: 'Kirim Pesanan ke Dapur (KDS)?',
                        message: 'Pesanan akan langsung muncul di antrean Layar Dapur/Bar secara real-time tanpa perlu cetak struk fisik.',
                        confirmText: 'Kirim ke Dapur',
                        cancelText: 'Batal',
                        type: 'info'
                    });
                    if (!confirmed) return;

                    this.isSendingToKitchen = true;
                    try {
                        const payload = {
                            items: this.cart.map(i => ({
                                product_id: i.product_id,
                                product_name: i.product_name,
                                unit_price: i.unit_price,
                                quantity: i.quantity,
                                discount_amount: i.discount_amount || 0,
                                notes: i.notes || null,
                                batch_number: i.batch_number || null,
                                expired_date: i.expired_date || null,
                                dosage_instructions: i.dosage_instructions || null,
                                selected_modifiers: i.selected_modifiers || []
                            })),
                            customer_id: this.selectedCustomerId || null,
                            customer_name_guest: this.activeTableCustomerName || null,
                            order_type: this.orderType,
                            sales_channel: this.salesChannel || 'dine_in',
                            pos_table_id: this.selectedTable?.id || null,
                            pos_table_session_id: this.selectedTable?.active_session?.id || null,
                            location_id: this.selectedLocationId,
                            notes: this.serviceNotes ? this.serviceNotes.trim() : null
                        };

                        const res = await fetch("{{ route('pos.send-to-kitchen') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success(data.message || 'Pesanan berhasil dikirim ke antrean Layar Dapur (KDS).');
                            if (data.order && this.selectedTable) {
                                this.activeTableOrderId = data.order.id;
                                this.activeTableOrderNumber = data.order.order_number;
                            }
                            this.cart = [];
                            if (typeof this.fetchTables === 'function') {
                                this.fetchTables();
                            }
                        } else {
                            AppAlert.error(data.message || 'Gagal mengirim pesanan ke dapur.');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi gangguan jaringan saat mengirim pesanan ke KDS.');
                    } finally {
                        this.isSendingToKitchen = false;
                    }
                },

                async promptManualDrawerPop() {
                    const confirmed = await AppAlert.confirm({
                        title: 'Buka Laci Kas (Cash Drawer)?',
                        message: 'Tindakan pembukaan laci kas secara manual (No-Sale Drawer Pop) akan dicatat dalam Log Audit Forensik Sistem.',
                        type: 'warning',
                        confirmText: 'Buka Laci Sekarang',
                        cancelText: 'Batal'
                    });
                    if (!confirmed) return;

                    try {
                        const res = await fetch("{{ route('pos.cash-drawer.manual-pop') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                reason: 'Manual Pop via Pusat Pengaturan POS'
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success(data.message || 'Sinyal buka laci kas berhasil dikirim.');
                        } else {
                            AppAlert.error(data.message || 'Gagal membuka laci kas.');
                        }
                    } catch (e) {
                        AppAlert.error('Gagal mengirim sinyal ke laci kas.');
                    }
                },

                promptHoldCart() {
                    this.holdOrderLabel = '';
                    this.showHoldPromptModal = true;
                    this.$nextTick(() => {
                        this.$refs.holdLabelInput?.focus();
                    });
                },

                confirmHoldCart() {
                    const label = this.holdOrderLabel ? this.holdOrderLabel.trim() : '';
                    if (!label) return;

                    fetch("{{ route('pos.hold') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                items: this.cart,
                                hold_label: label,
                                location_id: this.selectedLocationId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                AppAlert.success(data.message);
                                this.cart = [];
                                this.showHoldPromptModal = false;
                                this.holdOrderLabel = '';
                                this.refreshHeldOrders();
                            } else {
                                AppAlert.error(data.message);
                            }
                        });
                },

                refreshHeldOrders() {
                    fetch("{{ route('pos.held-orders') }}")
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.heldOrders = data.held_orders;
                            }
                        });
                },

                resumeHeldOrder(orderId) {
                    fetch("{{ url('/pos/resume') }}/" + orderId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.cart = data.items;
                                this.showHeldOrdersModal = false;
                                this.refreshHeldOrders();
                                AppAlert.success(data.message);
                            } else {
                                AppAlert.error(data.message);
                            }
                        });
                },

                submitOpenShift() {
                    fetch("{{ route('pos.shifts.open') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                opening_cash: this.shiftOpeningCash,
                                notes: this.shiftNotes,
                                location_id: this.selectedLocationId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.activeShift = data.shift;
                                this.showOpenShiftModal = false;
                                AppAlert.success(data.message);
                            }
                        });
                },

                openShiftCloseModal() {
                    if (!this.activeShift) return;
                    fetch("{{ url('/pos/shifts') }}/" + this.activeShift.id + "/summary")
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.shiftSummary = data.summary;
                                this.shiftActualCash = 0;
                                this.showCloseShiftModal = true;
                            }
                        });
                },

                submitCloseShift() {
                    if (!this.activeShift) return;
                    fetch("{{ url('/pos/shifts') }}/" + this.activeShift.id + "/close", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                closing_cash_actual: this.shiftActualCash
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                AppAlert.success(data.message);
                                this.activeShift = null;
                                this.showCloseShiftModal = false;
                            }
                        });
                },

                submitCashMovement() {
                    if (!this.activeShift) {
                        AppAlert.warning(window.COOCA_I18N?.shift_not_open || '{{ __('pos.shift_not_open') }}');
                        return;
                    }
                    if (!this.cashMovementAmount || this.cashMovementAmount <= 0) {
                        AppAlert.warning(window.COOCA_I18N?.movement_amount_invalid || '{{ __('pos.movement_amount_invalid') }}');
                        return;
                    }
                    if (this.cashMovementCategory === 'other' && (!this.cashMovementOtherDesc || !this.cashMovementOtherDesc.trim())) {
                        AppAlert.warning('{{ __('finance.category_other_required') }}');
                        return;
                    }
                    fetch("{{ url('/pos/shifts') }}/" + this.activeShift.id + "/cash-movement", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                type: this.cashMovementType,
                                category: this.cashMovementCategory,
                                other_description: this.cashMovementOtherDesc,
                                amount: this.cashMovementAmount,
                                reason: this.cashMovementReason
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                AppAlert.success(data.message);
                                this.showCashMovementModal = false;
                                this.cashMovementOtherDesc = '';
                                this.cashMovementReason = '';
                                this.cashMovementCategory = this.cashMovementType === 'cash_out' ? 'operational' : 'capital_injection';
                            } else {
                                AppAlert.error(data.message || (window.COOCA_I18N?.saving_failed || '{{ __('pos.saving_failed') }}'));
                            }
                        })
                        .catch(err => {
                            AppAlert.error(window.COOCA_I18N?.save_error || '{{ __('pos.save_error') }}');
                        });
                },

                // =====================================================
                // F&B METHODS: PRODUCT MODIFIERS, QR & TABLES
                // =====================================================
                handleProductClick(product) {
                    if (product.modifier_groups && product.modifier_groups.length > 0) {
                        this.openModifierModal(product);
                    } else {
                        this.addToCart(product);
                    }
                },

                openModifierModal(product) {
                    this.activeModifierProduct = product;
                    this.selectedModifiers = {};
                    this.modifierItemNotes = '';
                    this.modifierItemQty = 1;

                    // Pre-select default or first available option if single choice
                    product.modifier_groups.forEach(group => {
                        if (group.selection_type === 'single') {
                            const firstAvailable = group.options.find(o => (o.is_in_stock ?? o.is_available ??
                                true));
                            if (firstAvailable) {
                                this.selectedModifiers[group.id] = firstAvailable.id;
                            }
                        } else {
                            this.selectedModifiers[group.id] = [];
                        }
                    });

                    this.showModifierModal = true;
                },

                isModifierSelected(groupId, optionId) {
                    const val = this.selectedModifiers[groupId];
                    if (Array.isArray(val)) {
                        return val.includes(optionId);
                    }
                    return val === optionId;
                },

                toggleModifierOption(group, option) {
                    if (!(option.is_in_stock ?? option.is_available ?? true)) return;

                    if (group.selection_type === 'single') {
                        this.selectedModifiers[group.id] = option.id;
                    } else {
                        if (!Array.isArray(this.selectedModifiers[group.id])) {
                            this.selectedModifiers[group.id] = [];
                        }
                        const arr = this.selectedModifiers[group.id];
                        const idx = arr.indexOf(option.id);
                        if (idx > -1) {
                            arr.splice(idx, 1);
                        } else {
                            if (group.max_selection > 0 && arr.length >= group.max_selection) {
                                AppAlert.warning(`Maksimal pilihan untuk ${group.name} adalah ${group.max_selection}.`);
                                return;
                            }
                            arr.push(option.id);
                        }
                    }
                },

                calculateModifierTotalPrice() {
                    if (!this.activeModifierProduct) return 0;
                    let base = Number(this.getProductPrice(this.activeModifierProduct));
                    let delta = 0;

                    this.activeModifierProduct.modifier_groups.forEach(group => {
                        const val = this.selectedModifiers[group.id];
                        if (Array.isArray(val)) {
                            val.forEach(optId => {
                                const opt = group.options.find(o => o.id === optId);
                                if (opt) delta += Number(opt.price_delta || 0);
                            });
                        } else if (val) {
                            const opt = group.options.find(o => o.id === val);
                            if (opt) delta += Number(opt.price_delta || 0);
                        }
                    });

                    return base + delta;
                },

                addModifierItemToCart() {
                    if (!this.activeModifierProduct) return;

                    // Validate required groups
                    for (const group of this.activeModifierProduct.modifier_groups) {
                        if (group.is_required) {
                            const val = this.selectedModifiers[group.id];
                            if (!val || (Array.isArray(val) && val.length === 0)) {
                                AppAlert.warning(`Silakan pilih opsi untuk ${group.name}.`);
                                return;
                            }
                        }
                    }

                    const selectedOptionIds = [];
                    const summaryParts = [];

                    this.activeModifierProduct.modifier_groups.forEach(group => {
                        const val = this.selectedModifiers[group.id];
                        if (Array.isArray(val)) {
                            val.forEach(optId => {
                                const opt = group.options.find(o => o.id === optId);
                                if (opt) {
                                    selectedOptionIds.push(opt.id);
                                    summaryParts.push(opt.price_delta > 0 ?
                                        `${opt.name} (+${this.formatRupiah(opt.price_delta)})` : opt
                                        .name);
                                }
                            });
                        } else if (val) {
                            const opt = group.options.find(o => o.id === val);
                            if (opt) {
                                selectedOptionIds.push(opt.id);
                                summaryParts.push(opt.price_delta > 0 ?
                                    `${opt.name} (+${this.formatRupiah(opt.price_delta)})` : opt.name);
                            }
                        }
                    });

                    const basePrice = Number(this.getProductPrice(this.activeModifierProduct));
                    const unitPrice = this.calculateModifierTotalPrice();
                    const delta = unitPrice - basePrice;
                    const unitSymbol = this.activeModifierProduct.output_unit?.symbol || this.activeModifierProduct
                        .output_unit?.name || '';

                    this.cart.push({
                        cart_item_id: Date.now() + Math.random(),
                        product_id: this.activeModifierProduct.id,
                        product_name: this.activeModifierProduct.name,
                        unit_price: unitPrice,
                        modifier_delta: delta,
                        unit_symbol: unitSymbol,
                        quantity: this.modifierItemQty,
                        selected_modifiers: selectedOptionIds,
                        modifiers_summary: summaryParts.join(', '),
                        notes: this.modifierItemNotes.trim(),
                        discount_amount: 0,
                        batch_number: '',
                        expired_date: '',
                        dosage_instructions: ''
                    });

                    this.showModifierModal = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                openIncomingOrdersModal() {
                    this.showIncomingOrdersModal = true;
                    this.fetchIncomingOrders();
                },

                async fetchIncomingOrders() {
                    try {
                        const res = await fetch("{{ route('pos.incoming-orders') }}", {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const fetchedOrders = data.orders || [];

                            if (this.isInitialIncomingFetch) {
                                // Seed known IDs without triggering notifications on initial load
                                this.knownIncomingOrderIds = new Set(fetchedOrders.map(o => o.id));
                                this.isInitialIncomingFetch = false;
                                this.incomingQrOrders = fetchedOrders;
                                this.pendingQrCount = fetchedOrders.length;
                                return;
                            }

                            // Detect truly new orders by ID
                            const newOrders = fetchedOrders.filter(o => !this.knownIncomingOrderIds.has(o.id));

                            this.incomingQrOrders = fetchedOrders;
                            this.pendingQrCount = fetchedOrders.length;

                            if (newOrders.length > 0) {
                                // Update known set
                                newOrders.forEach(o => this.knownIncomingOrderIds.add(o.id));

                                // Trigger chime, floating island banner & desktop notification
                                this.triggerQrOrderNotification(newOrders[0], newOrders.length);
                            }

                            // Prune removed orders from known set
                            const currentIds = new Set(fetchedOrders.map(o => o.id));
                            this.knownIncomingOrderIds = currentIds;
                        }
                    } catch (e) {
                        console.error('Fetch incoming orders error:', e);
                    }
                },

                async acceptIncomingOrder(orderId) {
                    try {
                        const res = await fetch(`/pos/incoming-orders/${orderId}/accept`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success('Pesanan berhasil diterima dan diteruskan ke Dapur/Bar!');
                            this.incomingQrOrders = this.incomingQrOrders.filter(o => o.id !== orderId);
                            this.pendingQrCount = this.incomingQrOrders.length;
                            this.fetchTables();
                        } else {
                            AppAlert.error(data.message || 'Gagal menerima pesanan');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi kesalahan saat menerima pesanan');
                    }
                },

                promptRejectOrder(order) {
                    this.rejectingOrder = order;
                    this.rejectionReason = '';
                    this.showRejectReasonModal = true;
                },

                async submitRejectOrder() {
                    if (!this.rejectingOrder) return;
                    if (!this.rejectionReason.trim()) {
                        AppAlert.warning('Mohon masukkan alasan penolakan.');
                        return;
                    }

                    try {
                        const res = await fetch(`/pos/incoming-orders/${this.rejectingOrder.id}/reject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                reason: this.rejectionReason
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success('Pesanan ditolak.');
                            this.incomingQrOrders = this.incomingQrOrders.filter(o => o.id !== this.rejectingOrder.id);
                            this.pendingQrCount = this.incomingQrOrders.length;
                            this.showRejectReasonModal = false;
                            this.rejectingOrder = null;
                        } else {
                            AppAlert.error(data.message || 'Gagal menolak pesanan');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi kesalahan saat menolak pesanan');
                    }
                },

                openTablesModal(tab = 'tables') {
                    this.tableModalTab = tab;
                    this.showTablesModal = true;
                    this.fetchTables();
                },

                async fetchTables() {
                    try {
                        const res = await fetch("{{ route('pos.tables.index') }}", {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.tables = data.tables || [];
                            if (data.today_reservations) {
                                this.todayReservations = data.today_reservations;
                                this.todayReservationsCount = data.today_reservations.length;
                            }
                        }
                    } catch (e) {
                        console.error('Fetch tables error:', e);
                    }
                },

                async seatReservation(rsv, customTableId = null) {
                    const tableId = customTableId || rsv.pos_table_id;
                    if (!tableId) {
                        AppAlert.warning('Pilih meja terlebih dahulu untuk tamu reservasi ini.');
                        return;
                    }

                    if (this.isSeatingReservation) return;
                    this.isSeatingReservation = true;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            || '{{ csrf_token() }}';
                        const res = await fetch(`/pos/reservations/${rsv.id}/seat`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ pos_table_id: tableId })
                        });

                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success(data.message || 'Tamu berhasil check-in dan duduk di meja.');
                            await this.fetchTables();

                            const targetTable = (this.tables || []).find(t => t.id === tableId);
                            if (targetTable) {
                                this.selectTableForCart(targetTable);
                            } else if (data.table) {
                                this.selectedTable = data.table;
                                this.orderType = 'dine_in';
                                this.activeTableCustomerName = data.session?.customer_name || rsv.customer_name;
                                this.showTablesModal = false;
                            }
                        } else {
                            AppAlert.error(data.message || 'Gagal check-in reservasi');
                        }
                    } catch (e) {
                        console.error('Seat reservation error:', e);
                        AppAlert.error('Terjadi kesalahan saat check-in tamu reservasi.');
                    } finally {
                        this.isSeatingReservation = false;
                    }
                },

                loadTableOrderToCart(table, autoOpenPayment = false) {
                    if (!table) return;

                    const session = table.active_session;
                    const unpaidOrders = session?.orders?.filter(o => !['completed', 'voided', 'rejected'].includes(o
                        .status)) || [];

                    if (unpaidOrders.length === 0) {
                        // Meja kosong / belum ada order: jadikan meja aktif untuk pesanan baru
                        this.selectedTable = table;
                        this.orderType = 'dine_in';
                        this.activeTableOrderId = null;
                        this.activeTableOrderNumber = null;
                        this.activeTableCustomerName = null;
                        this.showTablesModal = false;
                        AppAlert.info(`Meja ${table.table_number} dipilih. Silakan tambahkan menu ke keranjang.`);
                        return;
                    }

                    // Ambil order aktif pertama pada meja ini
                    const order = unpaidOrders[0];
                    this.selectedTable = table;
                    this.orderType = 'dine_in';
                    this.activeTableOrderId = order.id;
                    this.activeTableOrderNumber = order.order_number;
                    this.activeTableCustomerName = order.customer_name_guest || session?.customer_name || (
                        'Pelanggan Meja ' + table.table_number);

                    // Konversi item order dari meja ke format keranjang kasir POS
                    const newCart = [];
                    (order.items || []).forEach(item => {
                        newCart.push({
                            cart_item_id: item.id || (Date.now() + Math.random()),
                            product_id: item.product_id || null,
                            product_name: item.product_name || item.name || 'Menu',
                            unit_price: Number(item.unit_price || 0),
                            unit_symbol: item.unit_symbol || '',
                            quantity: Number(item.quantity || 1),
                            selected_modifiers: item.selected_modifiers || [],
                            modifiers_summary: item.modifiers_summary || item.modifiers || '',
                            notes: item.notes || '',
                            discount_amount: 0,
                            from_table_order: true
                        });
                    });

                    this.cart = newCart;
                    this.showTablesModal = false;
                    this.showIncomingOrdersModal = false;

                    // Cocokkan data pelanggan jika ada nomor HP
                    if (session?.customer_phone) {
                        const found = this.customers.find(c => c.phone === session.customer_phone);
                        if (found) {
                            this.selectCustomer(found);
                        }
                    }

                    AppAlert.success(
                        `Pesanan Meja ${table.table_number} (${this.activeTableCustomerName}) berhasil dimuat ke keranjang.`
                        );

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        if (autoOpenPayment) {
                            this.openPaymentModal();
                        }
                    });
                },

                selectTableForCart(table) {
                    if (table.active_session && table.active_session.orders && table.active_session.orders.length > 0) {
                        this.loadTableOrderToCart(table);
                    } else {
                        this.selectedTable = table;
                        this.orderType = 'dine_in';
                        this.activeTableOrderId = null;
                        this.activeTableOrderNumber = null;
                        this.activeTableCustomerName = null;
                        this.showTablesModal = false;
                        AppAlert.success(`Meja ${table.table_number} dipilih untuk transaksi kasir ini.`);
                    }
                },

                detachTableFromCart() {
                    this.selectedTable = null;
                    this.activeTableOrderId = null;
                    this.activeTableOrderNumber = null;
                    this.activeTableCustomerName = null;
                    AppAlert.info('Koneksi meja dilepas dari keranjang.');
                },

                async refreshTableCart() {
                    if (!this.selectedTable) return;
                    try {
                        const res = await fetch(`/pos/tables/${this.selectedTable.id}/details`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success && data.table) {
                            const tbl = data.table;
                            const idx = this.tables.findIndex(t => t.id === tbl.id);
                            const enriched = {
                                ...tbl,
                                active_session: tbl.session ? {
                                    id: tbl.session.id,
                                    session_number: tbl.session.session_number,
                                    customer_name: tbl.session.customer_name,
                                    customer_phone: tbl.session.customer_phone,
                                    total_amount: tbl.session.total_amount,
                                    orders: tbl.session.unpaid_orders
                                } : null
                            };
                            if (idx !== -1) {
                                this.tables[idx] = enriched;
                            }
                            this.loadTableOrderToCart(enriched);
                        }
                    } catch (e) {
                        console.error('Refresh table cart error:', e);
                    }
                },

                async acceptAndLoadToCart(orderId) {
                    this.isProcessing = true;
                    try {
                        const res = await fetch(`/pos/incoming-orders/${orderId}/accept`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            await this.fetchTables();
                            this.incomingQrOrders = this.incomingQrOrders.filter(o => o.id !== orderId);
                            this.pendingQrCount = this.incomingQrOrders.length;
                            this.showIncomingOrdersModal = false;

                            const order = data.order;
                            const targetTable = this.tables.find(t => t.id === order?.pos_table_id || t.table_number ==
                                order?.table_or_reference);
                            if (targetTable) {
                                this.loadTableOrderToCart(targetTable);
                            } else {
                                AppAlert.success(data.message || 'Pesanan diterima.');
                            }
                        } else {
                            AppAlert.error(data.message || 'Gagal menerima pesanan');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi kesalahan saat menerima pesanan');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                openPayTableOrder(table) {
                    if (!table.active_session || !table.active_session.orders || table.active_session.orders.length === 0) {
                        AppAlert.warning('Tidak ada tagihan aktif untuk meja ini.');
                        return;
                    }
                    const order = table.active_session.orders.find(o => o.status !== 'completed' && o.status !== 'voided' &&
                        o.status !== 'rejected') || table.active_session.orders[0];
                    this.activeTableOrder = order;
                    this.selectedTablePayMethod = order.payment_channel || 'cash';
                    this.tableTenderAmount = Number(order.total_amount || 0);
                    this.showTablesModal = false;
                    this.showTablePaymentModal = true;
                },

                async submitPayTableOrder() {
                    if (!this.activeTableOrder) return;
                    if (!this.activeTableOrder.is_paid && this.tableTenderAmount < this.activeTableOrder.total_amount) {
                        AppAlert.warning('Nominal pembayaran kurang.');
                        return;
                    }

                    this.isProcessing = true;
                    try {
                        const res = await fetch(`/pos/orders/${this.activeTableOrder.id}/pay-table`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                payments: [{
                                    payment_method: this.selectedTablePayMethod,
                                    amount: this.tableTenderAmount
                                }],
                                location_id: this.selectedLocationId
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success(data.message || 'Pembayaran berhasil diselesaikan.');
                            this.showTablePaymentModal = false;
                            this.lastCompletedOrder = data.order;
                            this.lastReceiptUrl = data.receipt_url;
                            this.lastReceiptImageUrl = data.receipt_image_url || ('/receipt/' + data.order.id +
                                '/image');
                            this.lastWhatsAppUrl = data.whatsapp_url;
                            this.showSuccessModal = true;
                            this.fetchIncomingOrders();
                        } else {
                            AppAlert.error(data.message || 'Gagal memproses pembayaran meja.');
                        }
                    } catch (e) {
                        AppAlert.error('Terjadi kesalahan memproses pembayaran meja.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                setupAudioUnlock() {
                    const unlock = () => {
                        try {
                            if (!this.audioCtx) {
                                this.audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                            }
                            if (this.audioCtx.state === 'suspended') {
                                this.audioCtx.resume();
                            }
                        } catch (e) {}
                    };
                    ['click', 'touchstart', 'keydown'].forEach(evt => {
                        window.addEventListener(evt, unlock, {
                            once: true,
                            passive: true
                        });
                    });
                },

                requestNotificationPermission() {
                    try {
                        if ('Notification' in window && Notification.permission === 'default') {
                            Notification.requestPermission().catch(() => {});
                        }
                    } catch (e) {}
                },

                triggerQrOrderNotification(latestOrder, totalNewCount = 1) {
                    // 1. Play audible sound chime
                    this.playIncomingChime();

                    // 2. Launch floating dynamic notification card with auto-dismiss progress
                    this.dismissQrNotification();
                    this.latestQrNotification = latestOrder;
                    this.latestQrNotification.totalNewCount = totalNewCount;
                    this.notificationProgressPercent = 100;
                    this.isNotificationPaused = false;

                    const durationMs = 12000;
                    const stepMs = 100;
                    const decrement = (stepMs / durationMs) * 100;

                    this.notificationProgressInterval = setInterval(() => {
                        if (!this.isNotificationPaused) {
                            this.notificationProgressPercent -= decrement;
                            if (this.notificationProgressPercent <= 0) {
                                this.dismissQrNotification();
                            }
                        }
                    }, stepMs);

                    // 3. Dispatch OS/Browser native notification
                    this.sendDesktopNotification(latestOrder, totalNewCount);

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                pauseNotificationTimer() {
                    this.isNotificationPaused = true;
                },

                resumeNotificationTimer() {
                    this.isNotificationPaused = false;
                },

                dismissQrNotification() {
                    if (this.notificationProgressInterval) {
                        clearInterval(this.notificationProgressInterval);
                        this.notificationProgressInterval = null;
                    }
                    this.latestQrNotification = null;
                    this.notificationProgressPercent = 100;
                    this.isNotificationPaused = false;
                },

                formatNotificationItems(order) {
                    if (!order || !order.items || order.items.length === 0) return 'Tidak ada rincian item';
                    const firstItem = order.items[0];
                    const firstText = `${firstItem.quantity}x ${firstItem.product_name || firstItem.name}`;
                    if (order.items.length === 1) return firstText;
                    return `${firstText}, +${order.items.length - 1} item lainnya`;
                },

                sendDesktopNotification(order, totalNewCount = 1) {
                    try {
                        if ('Notification' in window && Notification.permission === 'granted') {
                            const tableNum = order.table_number || order.pos_table?.table_number || order
                                .table_or_reference || '-';
                            const custName = order.customer_name || order.customer_name_guest || 'Pelanggan';
                            const title = `Pesanan QR Masuk - Meja ${tableNum}` + (totalNewCount > 1 ?
                                ` (+${totalNewCount - 1} pesanan)` : '');
                            const body =
                                `${custName} memesan ${order.items?.length || 0} item • Total: ${this.formatRupiah(order.total_amount)}. Klik untuk buka di kasir.`;

                            const notif = new Notification(title, {
                                body: body,
                                icon: '/favicon.ico',
                                tag: 'pos-qr-order-' + order.id
                            });

                            notif.onclick = () => {
                                window.focus();
                                this.acceptAndLoadToCart(order.id);
                                this.dismissQrNotification();
                                notif.close();
                            };
                        }
                    } catch (e) {
                        console.warn('Desktop notification error:', e);
                    }
                },

                playIncomingChime() {
                    try {
                        if (!this.audioCtx) {
                            this.audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                        }
                        if (this.audioCtx.state === 'suspended') {
                            this.audioCtx.resume();
                        }

                        const now = this.audioCtx.currentTime;

                        // Chime Note 1: High crisp bell (D5 - 587.33 Hz)
                        const osc1 = this.audioCtx.createOscillator();
                        const gain1 = this.audioCtx.createGain();
                        osc1.type = 'triangle';
                        osc1.frequency.setValueAtTime(587.33, now);
                        gain1.gain.setValueAtTime(0.3, now);
                        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                        osc1.connect(gain1);
                        gain1.connect(this.audioCtx.destination);
                        osc1.start(now);
                        osc1.stop(now + 0.4);

                        // Chime Note 2: Higher shimmer bell (A5 - 880.00 Hz) at +120ms
                        const osc2 = this.audioCtx.createOscillator();
                        const gain2 = this.audioCtx.createGain();
                        osc2.type = 'sine';
                        osc2.frequency.setValueAtTime(880.00, now + 0.12);
                        gain2.gain.setValueAtTime(0.001, now);
                        gain2.gain.setValueAtTime(0.35, now + 0.12);
                        gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.85);
                        osc2.connect(gain2);
                        gain2.connect(this.audioCtx.destination);
                        osc2.start(now + 0.12);
                        osc2.stop(now + 0.85);

                        // Chime Note 3: Harmonic overtone (E6 - 1318.51 Hz) for Apple-like pleasant shimmer
                        const osc3 = this.audioCtx.createOscillator();
                        const gain3 = this.audioCtx.createGain();
                        osc3.type = 'sine';
                        osc3.frequency.setValueAtTime(1318.51, now + 0.12);
                        gain3.gain.setValueAtTime(0.001, now);
                        gain3.gain.setValueAtTime(0.12, now + 0.12);
                        gain3.gain.exponentialRampToValueAtTime(0.0001, now + 0.7);
                        osc3.connect(gain3);
                        gain3.connect(this.audioCtx.destination);
                        osc3.start(now + 0.12);
                        osc3.stop(now + 0.7);
                    } catch (e) {
                        console.warn('Audio chime error:', e);
                    }
                },

                formatTimeAgo(dateStr) {
                    if (!dateStr) return '';
                    const diffMs = new Date() - new Date(dateStr);
                    const diffMins = Math.floor(diffMs / 60000);
                    if (diffMins < 1) return 'Baru saja';
                    if (diffMins < 60) return `${diffMins} m lalu`;
                    const hours = Math.floor(diffMins / 60);
                    return `${hours} jam lalu`;
                },

                changeLocation() {
                    window.location.href = "{{ route('pos.terminal') }}?location_id=" + this.selectedLocationId;
                }
            }
        }
    </script>
</body>

</html>
