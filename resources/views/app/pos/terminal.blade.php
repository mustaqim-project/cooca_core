<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Terminal Kasir POS — {{ $business->name }}</title>

    <!-- Theme Initialization Script (Instant, prevents theme flashing) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('cooca-pos-theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Google Fonts (Inter as Apple SF Pro fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', '"Inter"', 'system-ui', 'sans-serif'],
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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
            -webkit-text-size-adjust: 100%;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        }
        *, *:before, *:after { box-sizing: inherit; }

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

        /* Modal Adaptive Sizing */
        .pos-modal-panel {
            max-height: min(90dvh, calc(100vh - 2rem)) !important;
            max-width: min(calc(100vw - 1.5rem), 42rem) !important;
            overflow-y: auto !important;
            overscroll-behavior: contain !important;
            -webkit-overflow-scrolling: touch !important;
        }

        input, select, textarea, button {
            touch-action: manipulation;
        }

        /* Sleek Apple-style scrollbars */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(120, 120, 128, 0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(120, 120, 128, 0.4); }

        [x-cloak] { display: none !important; }

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
            .pos-cart-backdrop { display: none !important; }
            .pos-cart-bar { display: none !important; }
            .pos-cart-btn-close { display: none !important; }
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
            .category-bar { scrollbar-width: none; }
            .category-bar::-webkit-scrollbar { display: none; }
        }
    </style>
</head>
<body class="h-full bg-[#F2F2F7] dark:bg-black text-[#000000] dark:text-[#F2F2F7] antialiased select-none"
      x-data="posApp()"
      x-init="initPos()">

    <!-- ===================================================== -->
    <!-- DYNAMIC FLOATING NOTIFICATION: NEW QR TABLE ORDER     -->
    <!-- ===================================================== -->
    <div x-show="latestQrNotification"
         x-cloak
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500 transform"
         x-transition:enter-start="-translate-y-12 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-250 transform"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="-translate-y-8 opacity-0 scale-95"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-[150] w-full max-w-lg px-4 pointer-events-none"
         @mouseenter="pauseNotificationTimer()"
         @mouseleave="resumeNotificationTimer()">
        <div class="pointer-events-auto backdrop-blur-2xl bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/10 dark:border-white/15 rounded-2xl p-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.6)] text-black dark:text-white relative overflow-hidden ring-1 ring-black/5 dark:ring-white/10">
            
            <!-- Subtle Top Accent Glow Gradient -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#007AFF] via-[#34C759] to-[#007AFF]"></div>

            <div class="flex items-start gap-3.5 pt-1">
                <!-- Pulsing Bell Icon Tile -->
                <div class="relative shrink-0 mt-0.5">
                    <div class="w-10 h-10 rounded-xl bg-[#007AFF]/15 dark:bg-[#007AFF]/25 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#34C759] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-[#34C759]"></span>
                    </span>
                </div>

                <!-- Content Area -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF]">
                                Order Masuk via QR
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]"
                                  x-text="'Meja ' + (latestQrNotification?.table_number || latestQrNotification?.pos_table?.table_number || latestQrNotification?.table_or_reference || '-')">
                            </span>
                            <template x-if="latestQrNotification?.totalNewCount > 1">
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF9500]/20 text-[#FF9500]"
                                      x-text="'+' + (latestQrNotification.totalNewCount - 1) + ' antrean'">
                                </span>
                            </template>
                        </div>
                        <!-- Close button -->
                        <button type="button" @click="dismissQrNotification()" class="w-6 h-6 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Customer and Items Summary -->
                    <div class="mt-1.5 flex items-baseline justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-black dark:text-white truncate" x-text="latestQrNotification?.customer_name || latestQrNotification?.customer_name_guest || 'Tamu / Pelanggan'"></div>
                            <div class="text-xs text-black/60 dark:text-white/60 truncate mt-0.5" x-text="formatNotificationItems(latestQrNotification)"></div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-semibold text-black/45 dark:text-white/45 block text-[10px]">Total</span>
                            <span class="text-sm font-extrabold text-[#007AFF] tabular-nums" x-text="formatRupiah(latestQrNotification?.total_amount)"></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button type="button"
                                @click="openIncomingOrdersModal(); dismissQrNotification();"
                                class="h-8 px-3 rounded-lg bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] text-xs font-semibold text-black/75 dark:text-white/80 transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>Lihat Antrean (<span x-text="pendingQrCount"></span>)</span>
                        </button>

                        <button type="button"
                                @click="acceptAndLoadToCart(latestQrNotification.id); dismissQrNotification();"
                                class="h-8 px-3.5 rounded-lg bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
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

    <div class="pos-shell h-screen flex flex-col overflow-hidden">

        <!-- ===================================================== -->
        <!-- 1. POS TOP TOOLBAR / NAVBAR (macOS Sonoma Style)       -->
        <!-- ===================================================== -->
        <header class="pos-header h-14 sm:h-16 backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border-b border-black/[0.06] dark:border-white/10 flex items-center justify-between px-3 sm:px-6 shrink-0 z-20 transition-colors">
            <!-- Left: Brand / Navigation / Location -->
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <!-- Back to Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="h-8.5 w-8.5 sm:h-9 sm:w-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/70 dark:text-white/80 flex items-center justify-center transition shrink-0"
                   title="Kembali ke Dashboard"
                   aria-label="Kembali ke Dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>

                <!-- Terminal Brand Tile -->
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8.5 h-8.5 sm:w-9 sm:h-9 shrink-0 rounded-[10px] bg-[#007AFF]/12 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-[13px] sm:text-[15px] text-black dark:text-white tracking-tight leading-none truncate max-w-[120px] sm:max-w-[200px] md:max-w-[260px]">{{ $business->name }}</div>
                        <div class="text-[10px] sm:text-[11px] font-medium text-[#007AFF] mt-0.5 sm:mt-1">Terminal Kasir POS</div>
                    </div>
                </div>

                <!-- Outlet Location Selector (Visible on sm+) -->
                <div class="hidden sm:flex items-center gap-1.5 ml-1 sm:ml-2 px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-xs shrink-0">
                    <svg class="w-3.5 h-3.5 text-black/45 dark:text-white/45" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <select x-model="selectedLocationId" @change="changeLocation()" class="bg-transparent text-black/80 dark:text-white/90 text-[11px] font-medium focus:outline-none cursor-pointer max-w-[140px] truncate">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $loc->id === $selectedLocationId ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Right: Status / Theme / Fullscreen / Actions -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <!-- Shift Indicator Pill -->
                <template x-if="activeShift">
                    <div class="flex items-center gap-1.5 px-2 sm:px-2.5 py-1 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] text-[11px]">
                        <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-[#34C759] animate-pulse"></span>
                        <span class="hidden md:inline font-medium">Shift Aktif</span>
                        <button @click="openShiftCloseModal()" class="px-1.5 py-0.5 rounded-[5px] bg-[#34C759]/20 hover:bg-[#34C759]/30 text-[10px] font-semibold transition">Tutup</button>
                    </div>
                </template>
                <template x-if="!activeShift">
                    <div class="flex items-center gap-1.5 px-2 sm:px-2.5 py-1 rounded-[8px] bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] text-[11px]">
                        <span class="w-1.5 h-1.5 shrink-0 rounded-full bg-[#FF9500]"></span>
                        <span class="hidden md:inline font-medium">Shift Belum Buka</span>
                        <button @click="showOpenShiftModal = true" class="px-1.5 py-0.5 rounded-[5px] bg-[#FF9500]/20 hover:bg-[#FF9500]/30 text-[10px] font-semibold transition">Buka</button>
                    </div>
                </template>

                <!-- QR Table Orders Button with Live Pulse Indicator -->
                <button @click="openIncomingOrdersModal()"
                        :class="pendingQrCount > 0 ? 'bg-[#007AFF]/15 dark:bg-[#007AFF]/25 text-[#007AFF] dark:text-[#0A84FF] ring-2 ring-[#007AFF]/50' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                        class="h-8 sm:h-9 px-2 sm:px-2.5 rounded-[8px] sm:rounded-[10px] active:scale-[0.97] text-[12px] font-medium transition flex items-center gap-1.5 relative"
                        title="Pesanan Masuk dari Meja QR">
                    <div class="relative flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#007AFF] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z" />
                        </svg>
                        <span x-show="pendingQrCount > 0" class="absolute -top-1 -right-1 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#007AFF] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#007AFF]"></span>
                        </span>
                    </div>
                    <span class="hidden xl:inline">Order QR</span>
                    <span x-show="pendingQrCount > 0" x-text="pendingQrCount" class="px-1.5 py-0.5 rounded-full bg-[#007AFF] text-white font-bold text-[9px] flex items-center justify-center tabular-nums"></span>
                </button>

                @if(\App\Support\Context::hasPermission('pos.tables'))
                <!-- Resto Meja Selector Button -->
                <button @click="openTablesModal()"
                        class="h-8 sm:h-9 px-2 sm:px-2.5 rounded-[8px] sm:rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/80 dark:text-white/80 text-[12px] font-medium transition flex items-center gap-1.5"
                        title="Daftar Meja & Sesi Tagihan Meja">
                    <svg class="w-4 h-4 text-[#34C759] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span class="hidden xl:inline">Meja</span>
                    <template x-if="selectedTable">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#34C759]/20 text-[#34C759]" x-text="'M-' + selectedTable.table_number"></span>
                    </template>
                </button>
                @endif

                @if(\App\Support\Context::hasPermission('pos.kitchen'))
                <!-- Kitchen Display Quick Link -->
                <a href="{{ route('pos.kitchen.index') }}"
                   class="h-8 sm:h-9 px-2 sm:px-2.5 rounded-[8px] sm:rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/80 dark:text-white/80 text-[12px] font-medium transition flex items-center gap-1.5"
                   title="Kitchen & Bar Display">
                    <svg class="w-4 h-4 text-[#FF9500] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                    </svg>
                    <span class="hidden 2xl:inline">Dapur</span>
                </a>
                @endif

                <!-- Antrean Hold Button -->
                <button @click="showHeldOrdersModal = true"
                        class="h-8 sm:h-9 px-2 sm:px-2.5 rounded-[8px] sm:rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/80 dark:text-white/80 text-[12px] font-medium transition flex items-center gap-1.5"
                        title="Antrean Transaksi (Hold)">
                    <svg class="w-4 h-4 text-[#FF9500] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden xl:inline">Hold</span>
                    <span x-show="heldOrders.length > 0" x-text="heldOrders.length" class="w-4 h-4 rounded-full bg-[#FF9500] text-black font-bold text-[9px] flex items-center justify-center tabular-nums"></span>
                </button>

                @if(\App\Support\Context::hasPermission('finance.cash_bank'))
                <!-- Kas Masuk / Keluar Button -->
                <button @click="showCashMovementModal = true"
                        class="h-8 sm:h-9 px-2 sm:px-2.5 rounded-[8px] sm:rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/80 dark:text-white/80 text-[12px] font-medium transition flex items-center gap-1.5"
                        title="Kas Masuk / Kas Keluar">
                    <svg class="w-4 h-4 text-black/50 dark:text-white/60 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <span class="hidden xl:inline">Kas</span>
                </button>
                @endif

                <!-- Fullscreen Toggle -->
                <div x-data="{
                    isFullscreen: false,
                    init() {
                        const handleFs = () => this.updateState();
                        document.addEventListener('fullscreenchange', handleFs);
                        document.addEventListener('webkitfullscreenchange', handleFs);
                    },
                    toggleFullscreen() {
                        if (!document.fullscreenElement) {
                            document.documentElement.requestFullscreen?.().catch(() => {});
                        } else {
                            document.exitFullscreen?.().catch(() => {});
                        }
                    },
                    updateState() {
                        this.isFullscreen = !!document.fullscreenElement;
                    }
                }" class="hidden sm:block">
                    <button type="button" @click="toggleFullscreen()"
                            class="h-9 px-2.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/80 dark:text-white/80 text-[12px] font-medium transition flex items-center gap-1"
                            :title="isFullscreen ? 'Keluar Fullscreen' : 'Mode Layar Penuh'">
                        <svg class="w-4 h-4 text-black/50 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                        </svg>
                    </button>
                </div>

                <!-- Theme Toggle Button (Light / Dark Mode) -->
                <button type="button" @click="toggleTheme()"
                        class="h-8 w-8 sm:h-9 sm:w-9 rounded-[8px] sm:rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/70 dark:text-white/80 transition flex items-center justify-center shrink-0"
                        :title="isDarkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'">
                    <!-- Sun when Dark -->
                    <svg x-show="isDarkMode" class="w-4 h-4 text-[#FFD60A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    <!-- Moon when Light -->
                    <svg x-show="!isDarkMode" class="w-4 h-4 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Cashier Avatar -->
                <div class="flex items-center gap-1.5 pl-1.5 border-l border-black/10 dark:border-white/10 text-xs">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[8px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold text-[12px] shrink-0"
                         title="{{ $user->name }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- ===================================================== -->
        <!-- 2. MAIN WORKSPACE (CATALOG & CART)                    -->
        <!-- ===================================================== -->
        <div class="pos-main flex-1 flex overflow-hidden">

            <!-- Left Area: Catalog & Products Touch Grid -->
            <div class="pos-catalog flex-1 flex flex-col overflow-hidden p-3 sm:p-4 gap-3">

                <!-- Search & Category Rows (Cleanly separated to eliminate any overlap) -->
                <div class="space-y-2.5 shrink-0">
                    <!-- Search Bar & Dedicated Camera Scanner Button (Apple HIG Pro Toolbar) -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <!-- Search Field with Focus State & Shortcut Hint -->
                        <div class="relative flex-1 min-w-0 group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-black/40 dark:text-white/40 group-focus-within:text-[#007AFF] transition-colors z-10">
                                <svg class="w-4 h-4 sm:w-[18px] sm:h-[18px] text-black/40 dark:text-white/40 group-focus-within:text-[#007AFF] shrink-0" style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <input type="text"
                                   x-ref="barcodeSearchInput"
                                   x-model="searchQuery"
                                   @keydown.enter="handleBarcodeOrSearch()"
                                   placeholder="Cari nama produk, SKU, atau scan barcode..."
                                   style="padding-left: 46px; padding-right: 90px;"
                                   class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 rounded-[14px] pl-12 pr-24 text-[13px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/25 focus:border-[#007AFF] shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition">
                            
                            <!-- Search Field Right Adornment (Clear Button & Enter Shortcut) -->
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2 z-10">
                                <button x-show="searchQuery" @click="searchQuery = ''; filterProducts()"
                                        type="button"
                                        class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/20 text-black/60 dark:text-white/70 hover:bg-black/20 dark:hover:bg-white/30 flex items-center justify-center transition"
                                        title="Hapus pencarian">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/10 text-black/40 dark:text-white/40 text-[10px] font-medium tracking-wide select-none">
                                    ↵ Enter
                                </span>
                            </div>
                        </div>

                        <!-- Dedicated Camera Scanner Action Button -->
                        <button type="button" @click="requestBarcodeScannerAccess()"
                                class="h-11 px-3.5 sm:px-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 hover:border-[#007AFF]/40 hover:bg-[#007AFF]/[0.04] dark:hover:bg-[#007AFF]/15 text-black/80 dark:text-white/90 hover:text-[#007AFF] dark:hover:text-[#0A84FF] active:scale-[0.97] transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.04)] shrink-0"
                                title="Scan barcode dengan kamera" aria-label="Scan barcode dengan kamera">
                            <div class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                </svg>
                            </div>
                            <span class="hidden sm:inline text-[13px] font-semibold tracking-tight">Scan Barcode</span>
                            <span class="sm:hidden text-[13px] font-semibold tracking-tight">Scan</span>
                        </button>
                    </div>

                    <!-- Category Cards Horizontal Scroll Strip (Larger touch-friendly cards) -->
                    <div class="category-bar flex items-center gap-2 overflow-x-auto pb-1 max-w-full scroll-smooth select-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                        <button type="button" @click="selectedCategory = 'all'; filterProducts()"
                                :class="selectedCategory === 'all'
                                    ? 'bg-[#007AFF] text-white shadow-xs font-semibold'
                                    : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.03] dark:hover:bg-white/[0.06] shadow-[0_1px_2px_rgba(0,0,0,0.03)]'"
                                class="h-10 sm:h-10.5 px-4 rounded-[12px] text-[13px] font-medium whitespace-nowrap transition-all shrink-0 flex items-center gap-1.5 active:scale-[0.97]">
                            <span>Semua Produk</span>
                            <span class="text-[11px] opacity-75 tabular-nums" x-text="'(' + allProducts.length + ')'"></span>
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" @click="selectedCategory = '{{ $cat->id }}'; filterProducts()"
                                    :class="selectedCategory === '{{ $cat->id }}'
                                        ? 'bg-[#007AFF] text-white shadow-xs font-semibold'
                                        : 'bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/10 text-black/75 dark:text-white/80 hover:bg-black/[0.03] dark:hover:bg-white/[0.06] shadow-[0_1px_2px_rgba(0,0,0,0.03)]'"
                                    class="h-10 sm:h-10.5 px-4 rounded-[12px] text-[13px] font-medium whitespace-nowrap transition-all shrink-0 flex items-center gap-1.5 active:scale-[0.97]">
                                <span>{{ $cat->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Touch Cards Grid -->
                <div class="pos-products pr-1">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2.5 sm:gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="handleProductClick(product)"
                                 class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-sm active:scale-[0.97] active:opacity-85 transition-all p-2.5 sm:p-3 cursor-pointer flex flex-col justify-between group select-none min-w-0">
                                <div>
                                    @if($posShowProductImages)
                                        <!-- Thumbnail Image with Aspect Ratio (Never squishing or overlapping) -->
                                        <div class="relative w-full aspect-[4/3] rounded-[10px] bg-black/[0.03] dark:bg-black/40 border border-black/[0.04] dark:border-white/5 mb-2 overflow-hidden items-center justify-center flex shrink-0">
                                            <template x-if="product.image_url">
                                                <img :src="product.image_url"
                                                     :alt="product.name"
                                                     loading="lazy"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                                     x-on:error="product.image_url = null">
                                            </template>
                                            <template x-if="!product.image_url">
                                                <div class="w-full h-full flex flex-col items-center justify-center text-black/20 dark:text-white/20">
                                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                    </svg>
                                                </div>
                                            </template>
                                            <!-- Stock Badge Pill -->
                                            <span class="absolute top-1.5 right-1.5 text-[9px] font-semibold px-1.5 py-0.5 rounded-full backdrop-blur-md tabular-nums"
                                                  :class="product.current_stock > 0 ? 'bg-white/80 dark:bg-black/70 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/30' : 'bg-white/80 dark:bg-black/70 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/30'"
                                                  x-text="'Stok: ' + product.current_stock">
                                            </span>
                                        </div>
                                    @else
                                        <!-- Code & Stock Header -->
                                        <div class="flex items-center justify-between gap-1 mb-1.5">
                                            <span class="text-[10px] font-medium text-black/40 dark:text-white/40 tabular-nums truncate" x-text="product.code || '-'"></span>
                                            <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full tabular-nums"
                                                  :class="product.current_stock > 0 ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'"
                                                  x-text="'Stok: ' + product.current_stock">
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Product Name (2 lines clamped) -->
                                    <h4 class="font-semibold text-[13px] sm:text-[14px] text-black dark:text-white line-clamp-2 leading-snug group-hover:text-[#007AFF] transition-colors" x-text="product.name"></h4>
                                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span class="text-[11px] text-black/40 dark:text-white/40 tabular-nums truncate" x-text="product.code || '-'"></span>
                                        <template x-if="product.modifier_groups && product.modifier_groups.length > 0">
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                                + Varian
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Price and Add Button -->
                                <div class="mt-2.5 pt-2 border-t border-black/[0.04] dark:border-white/5 flex items-center justify-between gap-1">
                                    <span class="font-bold text-[13px] sm:text-[14px] text-black dark:text-white tabular-nums truncate" x-text="formatRupiah(product.selling_price)"></span>
                                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/80 group-hover:bg-[#007AFF] group-hover:text-white transition-colors flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty Catalog State -->
                    <div x-show="filteredProducts.length === 0" class="h-64 flex flex-col items-center justify-center text-center text-black/40 dark:text-white/40">
                        <svg class="w-12 h-12 text-black/20 dark:text-white/20 mb-2" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <div class="text-[14px] font-semibold text-black/60 dark:text-white/60">Tidak ada produk ditemukan</div>
                        <div class="text-[12px] text-black/40 dark:text-white/40 mt-0.5">Coba ubah kata kunci pencarian atau kategori filter</div>
                    </div>
                </div>
            </div>

            <!-- Right Area: Cart & Checkout Inspector (Permanent side-by-side on desktop, slide-up sheet on mobile) -->
            <div class="pos-cart backdrop-blur-xl bg-white dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/10"
                 :class="mobileCartOpen ? 'pos-cart-open' : ''">

                <!-- Grabber Handle (iOS 18 Bottom Sheet) -->
                <div class="pos-cart-btn-close lg:hidden flex items-center justify-center pt-2.5 pb-1 cursor-pointer" @click="mobileCartOpen = false">
                    <div class="w-10 h-1 rounded-full bg-black/20 dark:bg-white/20"></div>
                </div>

                <!-- Cart Header -->
                <div class="p-3.5 sm:p-4 border-b border-black/[0.06] dark:border-white/10 space-y-2.5 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-[15px] text-black dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#007AFF] shrink-0" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            <span>Keranjang Pesanan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="clearCart()" x-show="cart.length > 0" class="text-[12px] text-[#FF3B30] hover:underline font-medium transition">
                                Kosongkan
                            </button>
                            <button @click="closeMobileCart()" class="pos-cart-btn-close lg:hidden p-1 rounded-md text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white" title="Tutup keranjang">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Customer Selector & Order Type -->
                    <div class="flex items-center gap-2">
                        <!-- Searchable Customer Combobox -->
                        <div class="relative flex-1" @click.outside="customerDropdownOpen = false">
                            <button type="button"
                                    @click="customerDropdownOpen = !customerDropdownOpen; if(customerDropdownOpen) $nextTick(() => $refs.customerSearchInput?.focus())"
                                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 rounded-[10px] pl-8 pr-7 text-[12px] text-left text-black dark:text-white flex items-center justify-between transition focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 cursor-pointer">
                                <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                <span class="truncate font-medium pr-1" x-text="activeCustomer ? activeCustomer.name : 'Pelanggan Umum (Guest)'"></span>
                                <svg class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0 transition-transform duration-200" :class="customerDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <!-- Clear Customer selection button (Reset to Guest) -->
                            <button x-show="activeCustomer"
                                    @click.stop="selectCustomer(null)"
                                    type="button"
                                    class="absolute right-7 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-black/10 dark:bg-white/20 text-black/60 dark:text-white/60 hover:bg-black/20 dark:hover:bg-white/30 flex items-center justify-center transition"
                                    title="Reset ke Pelanggan Umum">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Searchable Popover Dropdown Panel -->
                            <div x-show="customerDropdownOpen"
                                 x-cloak
                                 @keydown.escape.window="customerDropdownOpen = false"
                                 class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[14px] p-2 shadow-[0_12px_36px_rgba(0,0,0,0.25)] text-black dark:text-white min-w-[260px] max-w-[340px]">
                                
                                <!-- Search Input with icon and clear -->
                                <div class="relative mb-1.5">
                                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <input type="text"
                                           x-ref="customerSearchInput"
                                           x-model="customerSearchQuery"
                                           placeholder="Cari nama / nomor HP..."
                                           class="w-full h-8.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] pl-8 pr-7 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                    <button x-show="customerSearchQuery"
                                            @click="customerSearchQuery = ''; $refs.customerSearchInput?.focus()"
                                            type="button"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white p-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Scrollable Customers Results List -->
                                <div class="max-h-52 overflow-y-auto space-y-0.5 overscroll-contain pr-0.5">
                                    <!-- Pelanggan Umum (Guest) Option -->
                                    <div @click="selectCustomer(null)"
                                         :class="!selectedCustomerId ? 'bg-[#007AFF] text-white font-medium' : 'hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black dark:text-white'"
                                         class="flex items-center justify-between px-2.5 py-1.5 rounded-[8px] cursor-pointer text-xs transition">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0" :class="!selectedCustomerId ? 'bg-white/20 text-white' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                            </div>
                                            <span class="truncate">Pelanggan Umum (Guest)</span>
                                        </div>
                                        <svg x-show="!selectedCustomerId" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>

                                    <!-- Filtered Customers -->
                                    <template x-for="cust in filteredCustomers" :key="cust.id">
                                        <div @click="selectCustomer(cust)"
                                             :class="selectedCustomerId === cust.id ? 'bg-[#007AFF] text-white' : 'hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black dark:text-white'"
                                             class="flex items-center justify-between px-2.5 py-1.5 rounded-[8px] cursor-pointer text-xs transition">
                                            <div class="min-w-0 pr-1.5">
                                                <div class="font-semibold truncate flex items-center gap-1.5">
                                                    <span x-text="cust.name"></span>
                                                    <span x-show="cust.membership_tier"
                                                          :class="selectedCustomerId === cust.id ? 'bg-white/25 text-white' : 'bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2]'"
                                                          class="px-1.5 py-0.2 rounded-full text-[9px] uppercase font-bold"
                                                          x-text="cust.membership_tier"></span>
                                                </div>
                                                <div class="text-[11px] opacity-70 flex items-center gap-2 mt-0.5">
                                                    <span x-show="cust.phone" x-text="cust.phone"></span>
                                                    <span x-text="Number(cust.points_balance || 0) + ' Poin'"></span>
                                                </div>
                                            </div>
                                            <svg x-show="selectedCustomerId === cust.id" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </div>
                                    </template>

                                    <!-- Empty State -->
                                    <div x-show="filteredCustomers.length === 0" class="py-3 text-center text-xs text-black/45 dark:text-white/45">
                                        <p>Pelanggan tidak ditemukan.</p>
                                    </div>
                                </div>

                                <!-- Dropdown Footer -->
                                <div class="pt-2 mt-1 border-t border-black/10 dark:border-white/10 flex items-center justify-between text-[11px]">
                                    <span class="text-black/40 dark:text-white/40" x-text="filteredCustomers.length + ' data'"></span>
                                    <button type="button" @click="customerDropdownOpen = false; openCustomerModal()" class="text-[#007AFF] font-semibold hover:underline flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        <span>Pelanggan Baru</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" @click="openCustomerModal()"
                                class="shrink-0 h-9 w-9 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] hover:bg-[#007AFF] hover:text-white transition flex items-center justify-center active:scale-[0.97]"
                                title="Tambah pelanggan cepat" aria-label="Tambah pelanggan cepat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>
                        <select x-model="orderType" class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-2 text-[12px] text-black dark:text-white focus:outline-none cursor-pointer">
                            <option value="takeaway" class="bg-white dark:bg-[#1C1C1E]">Bungkus</option>
                            <option value="dine_in" class="bg-white dark:bg-[#1C1C1E]">Dine In</option>
                            <option value="delivery" class="bg-white dark:bg-[#1C1C1E]">Kirim</option>
                        </select>
                    </div>

                    <!-- Active Restaurant Table Card (Apple HIG Styled) -->
                    <div x-show="selectedTable" class="p-2.5 rounded-[12px] bg-[#007AFF]/[0.08] dark:bg-[#007AFF]/15 border border-[#007AFF]/25 space-y-1.5 transition">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-[8px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-[13px] text-black dark:text-white truncate" x-text="'Meja ' + (selectedTable ? selectedTable.table_number : '') + (selectedTable && selectedTable.name ? ' • ' + selectedTable.name : '')"></span>
                                        <span x-show="activeTableOrderId" class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] uppercase">QR Aktif</span>
                                    </div>
                                    <div class="text-[11px] text-black/60 dark:text-white/60 truncate">
                                        <span x-show="activeTableCustomerName" x-text="activeTableCustomerName + ' • '"></span>
                                        <span x-show="activeTableOrderNumber" class="font-mono text-[#007AFF]" x-text="'#' + activeTableOrderNumber"></span>
                                        <span x-show="!activeTableOrderId" class="italic">Meja Dine In Baru</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" @click="refreshTableCart()" x-show="activeTableOrderId" class="p-1 rounded-[6px] hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60 hover:text-[#007AFF] transition" title="Segarkan pesanan meja">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                </button>
                                <button type="button" @click="openTablesModal()" class="px-2 py-0.5 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-[11px] font-semibold text-black/70 dark:text-white/80 transition">
                                    Ganti
                                </button>
                                <button type="button" @click="detachTableFromCart()" class="p-1 rounded-[6px] text-black/40 hover:text-[#FF3B30] transition" title="Lepas meja dari keranjang">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Member Loyalty Card Preview -->
                    <div x-show="activeCustomer" class="p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                <span x-text="activeCustomer ? activeCustomer.name : ''"></span>
                                <span class="px-1.5 py-0.2 rounded-full bg-[#AF52DE]/15 text-[#AF52DE] dark:text-[#BF5AF2] font-semibold uppercase text-[9px]" x-text="activeCustomer ? activeCustomer.membership_tier : ''"></span>
                            </div>
                            <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] mt-0.5">
                                Saldo: <span class="font-semibold tabular-nums" x-text="activeCustomer ? activeCustomer.points_balance : 0"></span> Poin
                            </div>
                        </div>
                        <template x-if="activeCustomer && activeCustomer.points_balance > 0">
                            <button @click="togglePointsRedemption()"
                                    :class="redeemPoints ? 'bg-[#007AFF] text-white' : 'bg-black/[0.06] dark:bg-white/[0.08] text-[#007AFF] border border-[#007AFF]/30'"
                                    class="px-2.5 py-1 rounded-[7px] text-[10px] font-semibold transition active:scale-[0.97]">
                                <span x-text="redeemPoints ? 'Batalkan Poin' : 'Tukar Poin'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Cart Items Scrollable List -->
                <div class="flex-1 overflow-y-auto p-3.5 sm:p-4 space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/5 hover:border-black/10 dark:hover:border-white/10 transition flex flex-col gap-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-[13px] text-black dark:text-white leading-tight truncate" x-text="item.product_name"></div>
                                    <template x-if="item.modifiers_summary">
                                        <div class="text-[11px] text-[#007AFF] font-medium leading-tight mt-0.5" x-text="item.modifiers_summary"></div>
                                    </template>
                                    <template x-if="item.notes">
                                        <div class="inline-block text-[10px] text-[#FF9500] font-medium bg-[#FF9500]/10 border border-[#FF9500]/20 px-1.5 py-0.5 rounded mt-0.5" x-text="'📝 ' + item.notes"></div>
                                    </template>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums mt-0.5">
                                        <span x-text="formatRupiah(item.unit_price)"></span>
                                        <template x-if="item.unit_symbol">
                                            <span class="text-black/40 dark:text-white/40" x-text="'/' + item.unit_symbol"></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="font-bold text-[13px] text-black dark:text-white tabular-nums" x-text="formatRupiah((parseFloat(item.quantity) || 0) * item.unit_price)"></div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-black/[0.04] dark:border-white/5">
                                <div class="flex items-center gap-1 bg-black/[0.04] dark:bg-black/50 rounded-[8px] p-0.5 border border-black/[0.06] dark:border-white/10 focus-within:border-[#007AFF]/80 transition">
                                    <button type="button" @click="decrementQty(index)" class="w-6 h-6 rounded-[6px] bg-white dark:bg-white/[0.08] hover:bg-black/[0.05] dark:hover:bg-white/[0.14] text-black dark:text-white flex items-center justify-center font-bold text-xs shrink-0 active:scale-[0.95]" title="Kurangi 1">-</button>
                                    <div class="flex items-center">
                                        <input type="number"
                                               step="any"
                                               min="0.0001"
                                               :value="item.quantity"
                                               @input="updateItemQty(index, $event.target.value)"
                                               @blur="normalizeItemQty(index)"
                                               @click.stop
                                               @focus="$event.target.select()"
                                               class="w-14 bg-transparent border-none text-center font-semibold tabular-nums text-[12px] text-black dark:text-white focus:outline-none py-0.5 px-0.5"
                                               placeholder="Qty">
                                        <template x-if="item.unit_symbol">
                                            <span class="text-[10px] text-black/40 dark:text-white/40 font-medium pr-1 select-none" x-text="item.unit_symbol"></span>
                                        </template>
                                    </div>
                                    <button type="button" @click="incrementQty(index)" class="w-6 h-6 rounded-[6px] bg-white dark:bg-white/[0.08] hover:bg-black/[0.05] dark:hover:bg-white/[0.14] text-black dark:text-white flex items-center justify-center font-bold text-xs shrink-0 active:scale-[0.95]" title="Tambah 1">+</button>
                                </div>
                                <button type="button" @click="removeFromCart(index)" class="p-1 rounded-[6px] text-black/30 dark:text-white/30 hover:text-[#FF3B30] transition" title="Hapus dari keranjang">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center text-black/30 dark:text-white/30 py-8">
                        <svg class="w-10 h-10 text-black/20 dark:text-white/20 mb-2" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <div class="text-[13px] font-medium text-black/50 dark:text-white/50">Keranjang Kosong</div>
                        <div class="text-[11px] text-black/35 dark:text-white/35 mt-0.5">Pilih produk di katalog atau scan barcode</div>
                    </div>
                </div>

                <!-- Sticky Cart Footer -->
                <div class="p-3.5 sm:p-4 border-t border-black/[0.06] dark:border-white/10 bg-white dark:bg-[#1C1C1E] space-y-2 shrink-0">
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between text-black/60 dark:text-white/60">
                            <span>Subtotal</span>
                            <span class="tabular-nums font-semibold text-black dark:text-white" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <!-- Order Discount -->
                        <div class="flex items-center gap-1.5 pt-1">
                            <select x-model="discountType" class="w-24 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 py-1 text-[11px] text-black dark:text-white focus:outline-none">
                                <option value="fixed" class="bg-white dark:bg-[#1C1C1E]">Diskon Rp</option>
                                <option value="percentage" class="bg-white dark:bg-[#1C1C1E]">Diskon %</option>
                            </select>
                            <input type="number" x-model.number="discountValue" min="0" :max="discountType === 'percentage' ? 100 : subtotal" step="any"
                                   :placeholder="discountType === 'percentage' ? '10' : '10000'"
                                   class="min-w-0 flex-1 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 py-1 text-[12px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                        </div>
                        <div x-show="orderDiscountAmount > 0" class="flex justify-between text-[#FF3B30]">
                            <span>Diskon Transaksi</span>
                            <span class="tabular-nums font-medium" x-text="'-' + formatRupiah(orderDiscountAmount)"></span>
                        </div>

                        <!-- Voucher Code -->
                        <div class="flex items-center gap-1.5 pt-0.5">
                            <input type="text" x-model="voucherCode" placeholder="Kode Voucher..." class="flex-1 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 py-1 text-[11px] text-black dark:text-white uppercase tracking-wider focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                            <button @click="applyVoucher()" class="px-2.5 py-1 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-[11px] font-medium transition active:scale-[0.97]">Terapkan</button>
                        </div>

                        <div x-show="voucherDiscount > 0" class="flex justify-between text-[#34C759]">
                            <span>Diskon Voucher</span>
                            <span class="tabular-nums font-medium" x-text="'-' + formatRupiah(voucherDiscount)"></span>
                        </div>

                        <div x-show="pointsDiscount > 0" class="flex justify-between text-[#30B0C7]">
                            <span>Tukar Poin</span>
                            <span class="tabular-nums font-medium" x-text="'-' + formatRupiah(pointsDiscount)"></span>
                        </div>

                        <div x-show="taxAmount > 0" class="flex justify-between text-black/50 dark:text-white/50">
                            <span>PPN ({{ $business->pos_tax_percent }}%)</span>
                            <span class="tabular-nums text-black/80 dark:text-white/80" x-text="formatRupiah(taxAmount)"></span>
                        </div>

                        <div x-show="serviceChargeAmount > 0" class="flex justify-between text-black/50 dark:text-white/50">
                            <span>Service ({{ $business->pos_service_charge_percent }}%)</span>
                            <span class="tabular-nums text-black/80 dark:text-white/80" x-text="formatRupiah(serviceChargeAmount)"></span>
                        </div>
                    </div>

                    <!-- Grand Total Line -->
                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/10 flex items-baseline justify-between">
                        <div>
                            <div class="text-[11px] font-medium text-black/45 dark:text-white/45">Total Tagihan</div>
                            <div class="text-[12px] text-black/60 dark:text-white/60 tabular-nums" x-text="cart.length + ' item'"></div>
                        </div>
                        <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight" x-text="formatRupiah(grandTotal)"></div>
                    </div>

                    <!-- Action Buttons: Hold & Bayar -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <button @click="promptHoldCart()"
                                :disabled="cart.length === 0"
                                class="col-span-1 h-11 sm:h-12 rounded-[12px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black/80 dark:text-white/90 font-medium text-[13px] flex flex-col items-center justify-center transition disabled:opacity-30 disabled:cursor-not-allowed">
                            <span class="leading-tight">Hold Cart</span>
                        </button>
                        <button @click="openPaymentModal()"
                                :disabled="cart.length === 0"
                                class="col-span-2 h-11 sm:h-12 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-bold text-[15px] shadow-[0_2px_8px_rgba(0,122,255,0.3)] flex items-center justify-center gap-2 transition disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" style="width: 18px; height: 18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                            <span>Bayar Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Cart Backdrop -->
    <div x-show="mobileCartOpen" x-cloak class="pos-cart-backdrop" @click="closeMobileCart()"></div>

    <!-- Mobile Fixed Cart Bottom Bar (iOS 18 Floating Inset) -->
    <div class="pos-cart-bar items-center gap-3 backdrop-blur-2xl bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.08] dark:border-white/12 rounded-[20px] px-4 py-3 shadow-[0_12px_36px_rgba(0,0,0,0.18)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.6)]" x-cloak>
        <button @click="toggleMobileCart()" class="flex items-center gap-3 flex-1 min-w-0 text-left active:scale-[0.98] transition" type="button">
            <span class="relative shrink-0">
                <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                </div>
                <span x-show="cart.length > 0" x-text="cart.length"
                      class="absolute -top-1.5 -right-1.5 min-w-[1.25rem] h-[1.25rem] px-1 rounded-full bg-[#FF9500] text-white font-bold text-[11px] flex items-center justify-center tabular-nums shadow-sm border-2 border-white dark:border-[#1C1C1E]"></span>
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider leading-none mb-1">Keranjang</span>
                <span class="block font-extrabold text-black dark:text-white tabular-nums text-[16px] sm:text-[17px] leading-none truncate" x-text="formatRupiah(grandTotal)"></span>
            </span>
        </button>
        <button @click="openPaymentModal()" :disabled="cart.length === 0" type="button"
                class="h-12 px-6 sm:px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.96] text-white font-bold text-[15px] shadow-[0_4px_16px_rgba(0,122,255,0.35)] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition shrink-0">
            <span>Bayar</span>
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
                    <h3 class="font-semibold text-[16px] text-black dark:text-white">Tambah Pelanggan Cepat</h3>
                    <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Simpan nama dan nomor kontak pelanggan.</p>
                </div>
                <button type="button" @click="showCustomerModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="createQuickCustomer" class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nama Pelanggan *</label>
                    <input x-ref="quickCustomerName" type="text" x-model="newCustomer.name" required maxlength="255"
                           placeholder="Contoh: Budi Santoso"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nomor HP *</label>
                    <input type="tel" x-model="newCustomer.phone" required maxlength="50" inputmode="tel"
                           placeholder="08xxxxxxxxxx"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <p x-show="customerFormError" x-text="customerFormError" class="text-xs text-[#FF3B30]"></p>
                <div class="flex justify-end gap-2 pt-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showCustomerModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                    <button type="submit" :disabled="isCreatingCustomer"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] disabled:opacity-50 transition active:scale-[0.97]">
                        <span x-text="isCreatingCustomer ? 'Menyimpan...' : 'Simpan Pelanggan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL: PAYMENT MODAL (Apple Sheet Presentation)    -->
    <!-- ===================================================== -->
    <div x-show="showPaymentModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
         style="display: none;">
        <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 flex flex-col max-h-[90vh] overflow-y-auto space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-5 h-5 shrink-0" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-[17px] text-black dark:text-white">Pembayaran Kasir</h3>
                </div>
                <button @click="showPaymentModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Tagihan Summary Box -->
                <div class="p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-[12px] text-black/50 dark:text-white/50">Total yang harus dibayar:</div>
                        <div class="text-[22px] font-bold text-black dark:text-white tabular-nums tracking-tight" x-text="formatRupiah(grandTotal)"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[12px] text-black/50 dark:text-white/50">Total Diterima:</div>
                        <div class="text-[18px] font-semibold text-black/90 dark:text-white/90 tabular-nums" x-text="formatRupiah(totalTendered)"></div>
                    </div>
                </div>

                <!-- Payment Methods Segmented Grid -->
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="selectedPayMethod = 'cash'"
                                :class="selectedPayMethod === 'cash' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                                class="p-3 rounded-[10px] flex flex-col items-center gap-1.5 text-[12px] font-medium transition active:scale-[0.97]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            <span>Tunai (Cash)</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'qris'"
                                :class="selectedPayMethod === 'qris' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                                class="p-3 rounded-[10px] flex flex-col items-center gap-1.5 text-[12px] font-medium transition active:scale-[0.97]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                            </svg>
                            <span>QRIS / E-Wallet</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'transfer'"
                                :class="selectedPayMethod === 'transfer' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                                class="p-3 rounded-[10px] flex flex-col items-center gap-1.5 text-[12px] font-medium transition active:scale-[0.97]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            <span>Transfer Bank</span>
                        </button>
                        <button type="button" @click="selectedPayMethod = 'edc_debit'"
                                :class="selectedPayMethod === 'edc_debit' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/80 dark:text-white/80 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                                class="p-3 rounded-[10px] flex flex-col items-center gap-1.5 text-[12px] font-medium transition active:scale-[0.97]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                            <span>Kartu Debit/EDC</span>
                        </button>
                    </div>
                </div>

                <!-- Input Nominal Tender -->
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nominal Bayar (Rp)</label>
                    <input type="number" x-model.number="currentTenderAmount" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[18px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">

                    <!-- Quick Cash Presets (only for Cash) -->
                    <div x-show="selectedPayMethod === 'cash'" class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
                        <button type="button" @click="currentTenderAmount = grandTotal" class="h-9 rounded-[8px] bg-[#007AFF]/12 hover:bg-[#007AFF]/20 text-[12px] font-semibold text-[#007AFF] tabular-nums transition active:scale-[0.97]">Uang Pas</button>
                        <button type="button" @click="currentTenderAmount = 20000" class="h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[12px] font-medium text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">20.000</button>
                        <button type="button" @click="currentTenderAmount = 50000" class="h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[12px] font-medium text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">50.000</button>
                        <button type="button" @click="currentTenderAmount = 100000" class="h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[12px] font-medium text-black/80 dark:text-white/80 tabular-nums transition active:scale-[0.97]">100.000</button>
                    </div>
                </div>

                <!-- Kembalian / Sisa Bayar -->
                <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[12px] font-medium text-black/60 dark:text-white/60">Kembalian:</span>
                    <span class="text-[18px] font-bold text-[#34C759] tabular-nums" x-text="formatRupiah(Math.max(0, currentTenderAmount - grandTotal))"></span>
                </div>
            </div>

            <div class="pt-3 border-t border-black/10 dark:border-white/10 flex items-center justify-end gap-2.5">
                <button type="button" @click="showPaymentModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                <button type="button" @click="submitCheckout()"
                        :disabled="isProcessing || currentTenderAmount < grandTotal"
                        class="h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] shadow-sm disabled:opacity-30 transition flex items-center gap-2">
                    <span x-show="!isProcessing">Proses Transaksi</span>
                    <span x-show="isProcessing">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL: PAYMENT SUCCESS & RECEIPT (Bento UI Grid)  -->
    <!-- ===================================================== -->
    <div x-show="showSuccessModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[3px] p-4"
         @keydown.escape.window="if(showSuccessModal) resetForNewOrder()">
        <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden"
             @click.outside="resetForNewOrder()">

            <!-- Top Header Row -->
            <div class="flex items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/25 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-extrabold text-[17px] text-black dark:text-white tracking-tight leading-snug">Transaksi Berhasil!</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-[11px] font-bold px-1.5 py-0.2 rounded bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70"
                                  x-text="lastCompletedOrder ? ('#' + lastCompletedOrder.order_number) : ''"></span>
                            <span class="text-[11px] font-medium text-[#34C759]">Lunas</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="resetForNewOrder()"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition shrink-0"
                        title="Tutup (Esc)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Financial Summary Bento Strip -->
            <div class="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-center">
                <div class="px-2 py-1">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45 block">Total Belanja</span>
                    <span class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tabular-nums block mt-0.5 truncate"
                          x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.total_amount : 0)"></span>
                </div>
                <div class="px-2 py-1 border-x border-black/5 dark:border-white/5">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45 block">Total Bayar</span>
                    <span class="text-[13px] sm:text-[14px] font-bold text-black dark:text-white tabular-nums block mt-0.5 truncate"
                          x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.paid_amount : 0)"></span>
                </div>
                <div class="px-2 py-1">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-[#34C759] block">Kembalian</span>
                    <span class="text-[13px] sm:text-[14px] font-extrabold text-[#34C759] tabular-nums block mt-0.5 truncate"
                          x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.change_amount : 0)"></span>
                </div>
            </div>

            <!-- WhatsApp Feedback Toast (If Triggered) -->
            <div x-show="waBotFeedback" x-cloak
                 class="text-xs font-semibold py-2 px-3 rounded-xl border flex items-center justify-between gap-2"
                 :class="waBotFeedbackSuccess ? 'bg-[#34C759]/12 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A]'">
                <div class="flex items-center gap-2">
                    <svg x-show="waBotFeedbackSuccess" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <svg x-show="!waBotFeedbackSuccess" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <span x-text="waBotFeedback"></span>
                </div>
                <button type="button" @click="waBotFeedback = ''" class="text-xs opacity-60 hover:opacity-100">✕</button>
            </div>

            <!-- BENTO ACTIONS GRID -->
            <div class="space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40 px-0.5">
                    Aksi Tagihan &amp; Struk
                </div>

                <!-- Primary Row: Thermal Print & WhatsApp Bot -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    
                    <!-- Bento Tile 1: Cetak Struk Thermal (Hero Blue) -->
                    <a :href="lastReceiptUrl" target="_blank"
                       class="group rounded-2xl p-4 bg-gradient-to-br from-[#007AFF] to-[#0058C6] hover:from-[#0071E3] hover:to-[#004EB5] active:scale-[0.98] text-white flex flex-col justify-between shadow-md shadow-[#007AFF]/20 transition relative overflow-hidden min-h-[120px]">
                        <!-- Top Row: Icon + Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                                </svg>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/20 text-white backdrop-blur-xs">
                                58 &amp; 80mm
                            </span>
                        </div>
                        <!-- Bottom Info & CTA -->
                        <div class="mt-3">
                            <div class="font-extrabold text-[14px] leading-tight">Cetak Struk Thermal</div>
                            <div class="text-[11px] text-white/80 mt-0.5 flex items-center justify-between">
                                <span>Kirim ke printer kasir</span>
                                <span class="font-bold group-hover:translate-x-0.5 transition">Cetak ↗</span>
                            </div>
                        </div>
                    </a>

                    <!-- Bento Tile 2: WhatsApp Bot Otomatis (Hero Emerald) -->
                    <button type="button" @click.stop="!sendingWaBot && sendWhatsAppBotReceipt()" :disabled="sendingWaBot"
                            class="group rounded-2xl p-4 bg-[#25D366]/10 hover:bg-[#25D366]/18 dark:bg-[#25D366]/15 dark:hover:bg-[#25D366]/22 border border-[#25D366]/30 dark:border-[#25D366]/40 active:scale-[0.98] text-black dark:text-white flex flex-col justify-between transition shadow-xs disabled:opacity-50 text-left min-h-[120px]"
                            :class="sendingWaBot ? 'pointer-events-none opacity-60' : ''">
                        <!-- Top Row: Icon + Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-9 h-9 rounded-xl bg-[#25D366] text-white flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/>
                                </svg>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full"
                                  :class="waBotSent ? 'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : (sendingWaBot ? 'bg-[#FF9500]/20 text-[#FF9500]' : 'bg-[#25D366]/20 text-[#25D366]')">
                                <span x-text="sendingWaBot ? 'Mengirim...' : (waBotSent ? '✓ Terkirim' : 'Bot Otomatis')"></span>
                            </span>
                        </div>
                        <!-- Bottom Info & CTA -->
                        <div class="mt-3">
                            <div class="font-extrabold text-[14px] text-black dark:text-white leading-tight">Kirim WhatsApp</div>
                            <div class="text-[11px] text-black/60 dark:text-white/60 mt-0.5 flex items-center justify-between">
                                <span>Kirim e-struk ke pelanggan</span>
                                <span class="font-bold text-[#25D366] group-hover:translate-x-0.5 transition">
                                    <span x-text="waBotSent ? 'Kirim Ulang ➔' : 'Kirim ➔'"></span>
                                </span>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Secondary Row: WhatsApp Manual Web, Gambar Struk PNG & Lihat Struk Web -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <!-- Bento Tile 3: Manual WhatsApp Web -->
                    <a :href="lastWhatsAppUrl" target="_blank"
                       class="p-2.5 sm:p-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-between group">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.814-.814 6.002 6.002 0 011.057-3.035C4.646 15.688 4 13.928 4 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate">Manual WA</div>
                                <div class="text-[9px] text-black/45 dark:text-white/45 truncate">Chat browser</div>
                            </div>
                        </div>
                    </a>

                    <!-- Bento Tile 4: Gambar Struk PNG HD -->
                    <a :href="lastReceiptImageUrl" target="_blank"
                       class="p-2.5 sm:p-3 rounded-xl bg-[#007AFF]/8 hover:bg-[#007AFF]/15 border border-[#007AFF]/25 active:scale-[0.98] transition flex items-center justify-between group shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-[#007AFF] truncate">Gambar Struk</div>
                                <div class="text-[9px] text-[#007AFF]/70 truncate">Foto PNG WA</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-[#007AFF] group-hover:translate-x-0.5 transition shrink-0">↗</span>
                    </a>

                    <!-- Bento Tile 5: Lihat E-Bill Web -->
                    <a :href="lastReceiptUrl" target="_blank"
                       class="p-2.5 sm:p-3 rounded-xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 active:scale-[0.98] transition flex items-center justify-between group">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-lg bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate">E-Bill Web</div>
                                <div class="text-[9px] text-black/45 dark:text-white/45 truncate">Pratinjau HTML</div>
                            </div>
                        </div>
                        <span class="text-[10px] text-black/30 dark:text-white/30 group-hover:text-black dark:group-hover:text-white group-hover:translate-x-0.5 transition shrink-0">↗</span>
                    </a>
                </div>
            </div>

            <!-- Bottom Action: Transaksi Baru -->
            <div class="pt-1">
                <button type="button" @click="resetForNewOrder()"
                        class="w-full h-11 sm:h-12 rounded-2xl bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] active:scale-[0.98] text-black dark:text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition border border-black/5 dark:border-white/10">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Selesai &amp; Transaksi Baru</span>
                    <span class="text-[10px] text-black/40 dark:text-white/40 font-normal px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 ml-1">Esc</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: OPEN SHIFT (Apple Sheet)                    -->
    <!-- ===================================================== -->
    <div x-show="showOpenShiftModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <h3 class="font-semibold text-[17px] text-black dark:text-white">Buka Shift Kasir Baru</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Modal Awal Kasir (Rp)</label>
                    <input type="number" x-model.number="shiftOpeningCash" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Catatan Shift (Opsional)</label>
                    <input type="text" x-model="shiftNotes" placeholder="Catatan shift..." class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button @click="showOpenShiftModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                <button @click="submitOpenShift()" class="h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] transition shadow-sm">Buka Shift</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 7. MODAL: CLOSE SHIFT & RECONCILIATION (Apple Sheet)  -->
    <!-- ===================================================== -->
    <div x-show="showCloseShiftModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <h3 class="font-semibold text-[17px] text-black dark:text-white">Tutup Shift Kasir &amp; Rekonsiliasi</h3>

            <div class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Modal Awal:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatRupiah(shiftSummary.opening_cash)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Penjualan Tunai:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatRupiah(shiftSummary.cash_sales)"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Kas Masuk / Keluar:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatRupiah(shiftSummary.cash_in - shiftSummary.cash_out)"></span>
                </div>
                <div class="flex justify-between text-[#34C759] font-semibold border-t border-black/5 dark:border-white/5 pt-1.5 text-[13px]">
                    <span>Uang Fisik Diharapkan:</span>
                    <span class="tabular-nums font-bold" x-text="formatRupiah(shiftSummary.expected_cash)"></span>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Hitungan Kas Fisik Aktual di Laci (Rp)</label>
                    <input type="number" x-model.number="shiftActualCash" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="p-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex justify-between items-center text-xs">
                    <span class="text-black/60 dark:text-white/60">Selisih Kas:</span>
                    <span :class="(shiftActualCash - shiftSummary.expected_cash) === 0 ? 'text-[#34C759] font-bold' : 'text-[#FF3B30] font-bold'"
                          class="tabular-nums text-[13px]"
                          x-text="formatRupiah(shiftActualCash - shiftSummary.expected_cash)"></span>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button @click="showCloseShiftModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                <button @click="submitCloseShift()" class="h-9 px-5 rounded-[10px] bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] text-white font-semibold text-[13px] transition">Tutup &amp; Rekonsiliasi</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 8. MODAL: HELD ORDERS QUEUE (Apple Sheet)             -->
    <!-- ===================================================== -->
    <div x-show="showHeldOrdersModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <h3 class="font-semibold text-[17px] text-black dark:text-white">Daftar Antrean Transaksi (Held Carts)</h3>
                <button @click="showHeldOrdersModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-72 overflow-y-auto space-y-2">
                <template x-for="ho in heldOrders" :key="ho.id">
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-[14px] text-black dark:text-white" x-text="ho.hold_label || 'Antrean'"></div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5" x-text="ho.order_number + ' • ' + (ho.items ? ho.items.length : 0) + ' item'"></div>
                        </div>
                        <button @click="resumeHeldOrder(ho.id)" class="h-8 px-3 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[12px] transition">
                            Lanjutkan
                        </button>
                    </div>
                </template>
                <div x-show="heldOrders.length === 0" class="text-center py-8 text-xs text-black/40 dark:text-white/40">
                    Tidak ada transaksi yang di-hold saat ini.
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 9. MODAL: HOLD CART PROMPT (Apple Sheet - No prompt)  -->
    <!-- ===================================================== -->
    <div x-show="showHoldPromptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
        <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
             @click.outside="showHoldPromptModal = false">
            <div>
                <h3 class="font-semibold text-[17px] text-black dark:text-white">Simpan Antrean (Hold)</h3>
                <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Beri label meja atau nama pelanggan untuk melanjutkan nanti.</p>
            </div>
            <form @submit.prevent="confirmHoldCart()">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Label Antrean / No. Meja *</label>
                    <input x-ref="holdLabelInput" type="text" x-model="holdOrderLabel" required maxlength="100"
                           placeholder="Contoh: Meja 4 / Bpk. Rudi"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="flex justify-end gap-2 pt-3 mt-4 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showHoldPromptModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] transition shadow-sm">Simpan Antrean</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10. MODAL: CASH MOVEMENT (Apple Sheet)                -->
    <!-- ===================================================== -->
    <div x-show="showCashMovementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <h3 class="font-semibold text-[17px] text-black dark:text-white">Catat Kas Masuk / Kas Keluar</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Tipe Pergerakan</label>
                    <select x-model="cashMovementType" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none">
                        <option value="cash_in" class="bg-white dark:bg-[#1C1C1E]">Kas Masuk (Tambah Modal/Uang Pecahan)</option>
                        <option value="cash_out" class="bg-white dark:bg-[#1C1C1E]">Kas Keluar (Biaya Operasional Toko / Setor)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Nominal (Rp)</label>
                    <input type="number" x-model.number="cashMovementAmount" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Alasan / Keterangan</label>
                    <input type="text" x-model="cashMovementReason" placeholder="Misal: Beli es batu, gas, uang kembalian..." class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button @click="showCashMovementModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition">Batal</button>
                <button @click="submitCashMovement()" class="h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-semibold text-[13px] transition shadow-sm">Simpan</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 11. MODAL: CAMERA PERMISSION (Apple Alert Style)      -->
    <!-- ===================================================== -->
    <div x-show="showScannerPermission" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
        <div class="w-[300px] rounded-[14px] bg-white dark:bg-[#2C2C2E] overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/10 dark:border-white/10 text-black dark:text-white">
            <div class="px-5 pt-5 pb-4">
                <div class="w-10 h-10 rounded-full bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                    </svg>
                </div>
                <p class="text-[16px] font-semibold text-black dark:text-white">Izinkan Akses Kamera?</p>
                <p class="text-[12px] text-black/60 dark:text-white/60 mt-1 leading-snug">Kamera digunakan untuk memindai barcode produk secara otomatis.</p>
            </div>
            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[14px] font-medium">
                <button type="button" @click="showScannerPermission = false" class="py-2.5 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition">Batal</button>
                <button type="button" @click="confirmBarcodeScannerAccess()" class="py-2.5 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition">Izinkan</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 12. MODAL: CAMERA BARCODE SCANNER (Apple Sheet)       -->
    <!-- ===================================================== -->
    <div x-show="showBarcodeScanner" x-cloak @keydown.escape.window="closeBarcodeScanner()"
         class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
         @click.self="closeBarcodeScanner()">
        <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-4 sm:p-5 space-y-3.5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-[17px] text-black dark:text-white">Scan Barcode Produk</h3>
                    <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">Arahkan kamera ke barcode hingga produk terdeteksi.</p>
                </div>
                <button type="button" @click="closeBarcodeScanner()" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="relative aspect-video overflow-hidden rounded-[14px] bg-black border border-black/10 dark:border-white/10">
                <video x-ref="barcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-[70%] h-[40%] rounded-[12px] border-2 border-[#007AFF] shadow-[0_0_0_9999px_rgba(0,0,0,.45)]"></div>
                </div>
                <div x-show="scannerStarting" class="absolute inset-0 flex items-center justify-center bg-black/70 text-xs text-white/60">
                    <span class="flex items-center gap-2">Menyiapkan kamera...</span>
                </div>
            </div>

            <div x-show="scannerError" class="p-3 rounded-[10px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-xs text-[#FF3B30]" x-text="scannerError"></div>

            <div x-show="scannerDevices.length > 1" class="space-y-1">
                <label class="block text-[11px] text-black/50 dark:text-white/50 font-medium">Pilih Kamera</label>
                <select x-model="selectedScannerDeviceId" @change="startBarcodeScanner()" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-3 text-xs text-black dark:text-white">
                    <template x-for="device in scannerDevices" :key="device.deviceId">
                        <option :value="device.deviceId" x-text="device.label || 'Kamera ' + (scannerDevices.indexOf(device) + 1)"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2 border-t border-black/10 dark:border-white/10">
                <span class="text-[11px] text-black/40 dark:text-white/40">Scanner USB/Bluetooth juga tetap aktif via kolom pencarian.</span>
                <button type="button" @click="closeBarcodeScanner()" class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12px] font-medium transition">Tutup</button>
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
                    <div class="flex items-start justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <template x-if="activeModifierProduct.image_url">
                                <img :src="activeModifierProduct.image_url" class="w-12 h-12 rounded-[10px] object-cover border border-black/5 dark:border-white/10 shrink-0" alt="">
                            </template>
                            <div class="min-w-0">
                                <h3 class="font-bold text-[15px] text-black dark:text-white leading-snug truncate" x-text="activeModifierProduct.name"></h3>
                                <div class="text-xs text-[#007AFF] font-bold tabular-nums mt-0.5" x-text="formatRupiah(activeModifierProduct.selling_price)"></div>
                            </div>
                        </div>
                        <button type="button" @click="showModifierModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modifier Groups List -->
                    <div class="space-y-3.5 max-h-[50vh] overflow-y-auto pr-1">
                        <template x-for="group in activeModifierProduct.modifier_groups" :key="group.id">
                            <div class="space-y-2 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-xs text-black dark:text-white" x-text="group.name"></span>
                                        <span x-show="group.is_required" class="text-[9px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 px-1.5 py-0.2 rounded">Wajib</span>
                                    </div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45" x-text="group.selection_type === 'single' ? 'Pilih 1' : ('Pilih maks ' + group.max_selection)"></span>
                                </div>

                                <div class="space-y-1.5">
                                    <template x-for="opt in group.options" :key="opt.id">
                                        <label class="flex items-center justify-between p-2 rounded-[8px] border transition cursor-pointer text-xs select-none"
                                               :class="isModifierSelected(group.id, opt.id)
                                                   ? 'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF] font-semibold'
                                                   : ((opt.is_in_stock ?? opt.is_available ?? true) ? 'border-black/[0.06] dark:border-white/10 hover:bg-black/[0.03] dark:hover:bg-white/[0.05] text-black dark:text-white' : 'opacity-40 cursor-not-allowed border-dashed')">
                                            <div class="flex items-center gap-2">
                                                <input :type="group.selection_type === 'single' ? 'radio' : 'checkbox'"
                                                       :name="'mod_group_' + group.id"
                                                       :disabled="!(opt.is_in_stock ?? opt.is_available ?? true)"
                                                       :checked="isModifierSelected(group.id, opt.id)"
                                                       @change="toggleModifierOption(group, opt)"
                                                       class="w-4 h-4 text-[#007AFF] focus:ring-0 rounded">
                                                <span x-text="opt.name"></span>
                                                <span x-show="!(opt.is_in_stock ?? opt.is_available ?? true)" class="text-[9px] text-[#FF3B30] font-bold bg-[#FF3B30]/15 px-1 py-0.2 rounded">Stok Habis</span>
                                            </div>
                                            <span class="tabular-nums font-medium" x-text="opt.price_delta > 0 ? ('+' + formatRupiah(opt.price_delta)) : 'Gratis'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Item Notes Input -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-black dark:text-white">Catatan Item (Opsional)</label>
                            <input type="text" x-model="modifierItemNotes" placeholder="Cth: Less Sugar, No Ice, Pisah Saus..." class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs font-semibold text-black dark:text-white">Jumlah</span>
                            <div class="flex items-center gap-2 bg-black/[0.04] dark:bg-white/[0.06] rounded-[8px] p-1 border border-black/5 dark:border-white/10">
                                <button type="button" @click="modifierItemQty = Math.max(1, modifierItemQty - 1)" class="w-7 h-7 rounded-[6px] bg-white dark:bg-white/[0.1] text-black dark:text-white flex items-center justify-center font-bold text-sm shadow-xs">-</button>
                                <span class="w-8 text-center font-bold tabular-nums text-xs" x-text="modifierItemQty"></span>
                                <button type="button" @click="modifierItemQty++" class="w-7 h-7 rounded-[6px] bg-white dark:bg-white/[0.1] text-black dark:text-white flex items-center justify-center font-bold text-sm shadow-xs">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-between gap-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div>
                            <div class="text-[10px] text-black/50 dark:text-white/50 font-medium">Subtotal Item</div>
                            <div class="text-sm font-extrabold text-black dark:text-white tabular-nums" x-text="formatRupiah(calculateModifierTotalPrice() * modifierItemQty)"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showModifierModal = false" class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                            <button type="button" @click="addModifierItemToCart()" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                                Tambah ke Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

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
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-black dark:text-white">Pesanan Masuk dari Meja QR</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">Pelanggan scan QR meja dan mengirim pesanan langsung.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="fetchIncomingOrders()" class="p-2 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white" title="Segarkan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </button>
                    <button type="button" @click="showIncomingOrdersModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                <template x-for="order in incomingQrOrders" :key="order.id">
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/10 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#007AFF]/15 text-[#007AFF]" x-text="'Meja ' + (order.pos_table ? order.pos_table.table_number : '-')"></span>
                                    <span class="font-mono text-xs text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white mt-1">
                                    <span x-text="order.customer_name_guest || 'Pelanggan'"></span>
                                    <span class="text-black/45 dark:text-white/45" x-text="' • ' + (order.customer_phone_guest || '-')"></span>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-black/50 dark:text-white/50" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes" class="p-2 rounded-[8px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-xs text-[#FF9500] font-medium flex items-center gap-1.5">
                            <span class="font-bold">Catatan Meja:</span>
                            <span x-text="order.notes"></span>
                        </div>

                        <!-- Items Breakdown -->
                        <div class="space-y-1.5 border-t border-b border-black/[0.05] dark:border-white/5 py-2">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="text-xs">
                                    <div class="flex items-baseline justify-between">
                                        <div class="flex items-baseline gap-1.5 font-medium text-black dark:text-white">
                                            <span class="font-bold text-[#007AFF]" x-text="item.quantity + 'x'"></span>
                                            <span x-text="item.product_name"></span>
                                        </div>
                                        <span class="tabular-nums font-semibold" x-text="formatRupiah(item.total_price)"></span>
                                    </div>
                                    <div x-show="item.modifiers && item.modifiers.length > 0" class="pl-4 text-[11px] text-[#007AFF]">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <span class="mr-2" x-text="'• ' + mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                        </template>
                                    </div>
                                    <div x-show="item.notes" class="pl-4 text-[10px] text-[#FF9500] font-medium" x-text="'Catatan: ' + item.notes"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Total & Action Buttons -->
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <div>
                                <span class="text-[10px] text-black/50 dark:text-white/50 block">Total Tagihan</span>
                                <span class="text-base font-extrabold text-black dark:text-white tabular-nums" x-text="formatRupiah(order.total_amount)"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="promptRejectOrder(order)" class="h-9 px-3 rounded-[10px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] text-xs font-bold transition">
                                    Tolak
                                </button>
                                <button type="button" @click="acceptIncomingOrder(order.id)" class="h-9 px-3.5 rounded-[10px] bg-[#34C759]/15 hover:bg-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-xs font-bold transition flex items-center gap-1.5" title="Terima dan teruskan ke dapur">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span>Terima Saja</span>
                                </button>
                                <button type="button" @click="acceptAndLoadToCart(order.id)" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5" title="Terima pesanan dan langsung muat ke keranjang kasir">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                                    <span>Buka di Kasir</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="incomingQrOrders.length === 0" class="text-center py-16 text-black/40 dark:text-white/40 text-xs">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                <h3 class="font-bold text-base text-[#FF3B30]">Tolak Pesanan Meja</h3>
                <p class="text-xs text-black/60 dark:text-white/60 mt-1">Masukkan alasan penolakan agar tercatat di histori order.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-black dark:text-white mb-1">Alasan Penolakan</label>
                <textarea x-model="rejectionReason" rows="3" placeholder="Cth: Bahan baku habis, Meja sedang reservasi..." class="w-full rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 p-2.5 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="showRejectReasonModal = false" class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                <button type="button" @click="submitRejectOrder()" class="h-9 px-4 rounded-[10px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-xs font-bold transition">
                    Konfirmasi Tolak
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL: RESTAURANT TABLES & ACTIVE BILLS               -->
    <!-- ===================================================== -->
    <div x-show="showTablesModal" x-cloak
         class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
         @keydown.escape.window="showTablesModal = false">
        <div class="pos-modal-panel w-full max-w-3xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
             @click.outside="showTablesModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-black dark:text-white">Manajemen Meja &amp; Tagihan Restoran</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">Pilih meja untuk transaksi kasir atau bayar pesanan meja QR.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(\App\Support\Context::hasPermission('pos.tables'))
                    <a href="{{ route('pos.tables.index') }}" target="_blank" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>Kelola Meja &amp; QR</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    </a>
                    @endif
                    <button type="button" @click="showTablesModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-[60vh] overflow-y-auto pr-1">
                <template x-for="tbl in tables" :key="tbl.id">
                    <div class="p-3.5 rounded-[14px] border transition flex flex-col justify-between select-none"
                         :class="tbl.status === 'available' ? 'bg-[#34C759]/5 border-[#34C759]/30' : (tbl.status === 'waiting_payment' ? 'bg-[#FF9500]/10 border-[#FF9500]/40' : 'bg-[#007AFF]/10 border-[#007AFF]/40')">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <span class="font-extrabold text-base text-black dark:text-white" x-text="'Meja ' + tbl.table_number"></span>
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded"
                                      :class="tbl.status === 'available' ? 'bg-[#34C759]/20 text-[#34C759]' : (tbl.status === 'waiting_payment' ? 'bg-[#FF9500]/20 text-[#FF9500]' : 'bg-[#007AFF]/20 text-[#007AFF]')"
                                      x-text="tbl.status"></span>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5" x-text="(tbl.name ? tbl.name + ' • ' : '') + tbl.capacity + ' Kursi'"></div>

                            <!-- Session info if occupied -->
                            <div x-show="tbl.active_session" class="mt-2 pt-2 border-t border-black/5 dark:border-white/5 space-y-1">
                                <div class="text-[11px] font-bold text-black dark:text-white truncate" x-text="tbl.active_session?.customer_name"></div>
                                <div class="text-xs font-extrabold text-[#007AFF] tabular-nums" x-text="formatRupiah(tbl.active_session?.total_amount || 0)"></div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="mt-3 pt-2 border-t border-black/5 dark:border-white/5 flex flex-col gap-1.5">
                            <template x-if="tbl.active_session && tbl.active_session.orders && tbl.active_session.orders.length > 0">
                                <div class="flex flex-col gap-1.5">
                                    <button type="button" @click="loadTableOrderToCart(tbl)" class="w-full h-8 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[11px] font-bold transition flex items-center justify-center gap-1.5 shadow-sm" title="Muat seluruh pesanan meja ke keranjang kasir">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                                        <span>Cek &amp; Muat ke Keranjang</span>
                                    </button>
                                    <button type="button" @click="loadTableOrderToCart(tbl, true)" class="w-full h-7 rounded-[7px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[11px] font-bold transition flex items-center justify-center gap-1">
                                        <span>Bayar Cepat</span>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!tbl.active_session || !tbl.active_session.orders || tbl.active_session.orders.length === 0">
                                <button type="button" @click="selectTableForCart(tbl)" class="w-full h-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[11px] font-semibold transition">
                                    Pilih Meja Ini (Dine In)
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
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
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div>
                            <h3 class="font-bold text-base text-black dark:text-white">Pembayaran Pesanan Meja</h3>
                            <div class="text-xs text-black/50 dark:text-white/50" x-text="'Pesanan #' + activeTableOrder.order_number"></div>
                        </div>
                        <button type="button" @click="showTablePaymentModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Order Items Summary -->
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5 max-h-40 overflow-y-auto">
                        <template x-for="it in activeTableOrder.items" :key="it.id">
                            <div class="flex justify-between text-xs">
                                <div>
                                    <span class="font-bold" x-text="it.quantity + 'x '"></span>
                                    <span x-text="it.product_name"></span>
                                    <div x-show="it.modifiers && it.modifiers.length > 0" class="text-[10px] text-[#007AFF]">
                                        <template x-for="m in it.modifiers" :key="m.id">
                                            <span class="mr-1" x-text="'+ ' + m.modifier_option_name"></span>
                                        </template>
                                    </div>
                                </div>
                                <span class="font-semibold tabular-nums" x-text="formatRupiah(it.total_price)"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Amount Display -->
                    <div class="text-center py-2 bg-[#007AFF]/10 rounded-[12px] border border-[#007AFF]/20">
                        <span class="text-xs text-[#007AFF] font-medium block">Total yang Harus Dibayar</span>
                        <span class="text-2xl font-black text-[#007AFF] tabular-nums" x-text="formatRupiah(activeTableOrder.total_amount)"></span>
                    </div>

                    <!-- Payment Method Selector -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-black dark:text-white">Metode Pembayaran</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" @click="selectedTablePayMethod = 'cash'; tableTenderAmount = activeTableOrder.total_amount"
                                    :class="selectedTablePayMethod === 'cash' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                    class="h-9 rounded-[8px] text-xs transition">Tunai</button>
                            <button type="button" @click="selectedTablePayMethod = 'qris'; tableTenderAmount = activeTableOrder.total_amount"
                                    :class="selectedTablePayMethod === 'qris' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                    class="h-9 rounded-[8px] text-xs transition">QRIS</button>
                            <button type="button" @click="selectedTablePayMethod = 'transfer'; tableTenderAmount = activeTableOrder.total_amount"
                                    :class="selectedTablePayMethod === 'transfer' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                    class="h-9 rounded-[8px] text-xs transition">Transfer</button>
                            <button type="button" @click="selectedTablePayMethod = 'debit'; tableTenderAmount = activeTableOrder.total_amount"
                                    :class="selectedTablePayMethod === 'debit' ? 'bg-[#007AFF] text-white font-bold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white'"
                                    class="h-9 rounded-[8px] text-xs transition">Debit</button>
                        </div>
                    </div>

                    <!-- Tender Amount (for Cash) -->
                    <div x-show="selectedTablePayMethod === 'cash'" class="space-y-1.5">
                        <label class="block text-xs font-semibold text-black dark:text-white">Nominal Diterima</label>
                        <input type="number" x-model.number="tableTenderAmount" class="w-full h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 font-bold text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <div x-show="tableTenderAmount > activeTableOrder.total_amount" class="text-xs text-[#34C759] font-bold">
                            Kembalian: <span x-text="formatRupiah(tableTenderAmount - activeTableOrder.total_amount)"></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="showTablePaymentModal = false" class="h-10 px-4 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">Batal</button>
                        <button type="button" @click="submitPayTableOrder()" :disabled="tableTenderAmount < activeTableOrder.total_amount || isProcessing" class="h-10 px-5 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm disabled:opacity-40">
                            Konfirmasi Lunas
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

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
                customers: @json($customers),
                heldOrders: @json($heldOrders),
                selectedCategory: 'all',
                searchQuery: '',
                isDarkMode: true,

                // Cart state
                cart: [],
                selectedCustomerId: '',
                activeCustomer: null,
                customerSearchQuery: '',
                customerDropdownOpen: false,
                orderType: 'takeaway',
                voucherCode: '',
                voucherDiscount: 0,
                discountType: 'fixed',
                discountValue: 0,
                redeemPoints: false,
                pointsDiscount: 0,

                // F&B Tables & QR state
                tables: @json($tables ?? []),
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

                // Table payment modal state
                showTablePaymentModal: false,
                activeTableOrder: null,
                tableTenderAmount: 0,
                selectedTablePayMethod: 'cash',

                // Modals
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

                // Payment state
                selectedPayMethod: 'cash',
                currentTenderAmount: 0,
                isProcessing: false,
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
                shiftSummary: { opening_cash: 0, cash_sales: 0, cash_in: 0, cash_out: 0, expected_cash: 0 },

                // Cash movement
                cashMovementType: 'cash_in',
                cashMovementAmount: 50000,
                cashMovementReason: '',
                newCustomer: { name: '', phone: '' },
                customerFormError: '',
                isCreatingCustomer: false,

                initPos() {
                    this.initTheme();
                    this.filteredProducts = this.allProducts;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });

                    // Request desktop notification permission if supported
                    this.requestNotificationPermission();

                    // Unlock Web Audio API on first user interaction anywhere
                    this.setupAudioUnlock();

                    // Initial fetch & polling
                    this.fetchIncomingOrders();
                    this.incomingPollInterval = setInterval(() => {
                        this.fetchIncomingOrders();
                    }, 5000);
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

                formatRupiah(val) {
                    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
                },

                filterProducts() {
                    let res = this.allProducts;
                    if (this.selectedCategory !== 'all') {
                        res = res.filter(p => p.category_id === this.selectedCategory);
                    }
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        res = res.filter(p => (p.name && p.name.toLowerCase().includes(q)) || (p.code && p.code.toLowerCase().includes(q)));
                    }
                    this.filteredProducts = res;
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
                        this.scannerError = 'Browser ini belum mendukung scan barcode kamera. Gunakan Chrome/Android terbaru atau scanner USB/Bluetooth.';
                        return;
                    }

                    try {
                        const supportedFormats = await BarcodeDetector.getSupportedFormats();
                        const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'codabar', 'itf'].filter(format => supportedFormats.includes(format));
                        this.scannerDetector = new BarcodeDetector({ formats });
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
                        this.selectedScannerDeviceId = this.scannerDevices.find(device => /back|rear|environment/i.test(device.label))?.deviceId || this.scannerDevices[0].deviceId;
                    }
                },

                async startBarcodeScanner() {
                    this.stopBarcodeScanner();
                    this.scannerError = '';
                    this.scannerStarting = true;

                    try {
                        const videoConstraints = this.selectedScannerDeviceId
                            ? { deviceId: { exact: this.selectedScannerDeviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
                            : { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } };
                        this.scannerStream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints, audio: false });
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
                    const product = this.allProducts.find(item => item.code && item.code.trim().toLowerCase() === normalized);
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
                    if (error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError') return 'Akses kamera ditolak. Izinkan kamera di browser lalu coba lagi.';
                    if (error?.name === 'NotFoundError') return 'Kamera tidak ditemukan pada perangkat ini.';
                    if (error?.name === 'NotReadableError') return 'Kamera sedang digunakan aplikasi lain.';
                    if (window.isSecureContext === false) return 'Scanner kamera memerlukan HTTPS atau localhost.';
                    return 'Kamera tidak dapat dibuka. Periksa izin kamera lalu coba lagi.';
                },

                addToCart(product) {
                    const existing = this.cart.find(item => item.product_id === product.id);
                    if (existing) {
                        const current = parseFloat(existing.quantity) || 0;
                        existing.quantity = parseFloat((current + 1).toFixed(4));
                    } else {
                        const unitSymbol = product.output_unit?.symbol || product.output_unit?.code || product.output_unit?.name || '';
                        this.cart.push({
                            product_id: product.id,
                            product_name: product.name,
                            unit_price: Number(product.selling_price || 0),
                            unit_symbol: unitSymbol,
                            quantity: 1,
                            discount_amount: 0,
                            notes: ''
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
                    }
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
                },

                get orderDiscountAmount() {
                    const value = Math.max(0, Number(this.discountValue || 0));
                    return this.discountType === 'percentage'
                        ? Math.min(this.subtotal, (this.subtotal * Math.min(100, value)) / 100)
                        : Math.min(this.subtotal, value);
                },

                get taxAmount() {
                    @if($business->pos_enable_tax)
                        return Math.max(0, this.subtotal - this.orderDiscountAmount - this.voucherDiscount - this.pointsDiscount) * ({{ (float) $business->pos_tax_percent }} / 100);
                    @else
                        return 0;
                    @endif
                },

                get serviceChargeAmount() {
                    @if($business->pos_enable_service_charge)
                        return Math.max(0, this.subtotal - this.orderDiscountAmount - this.voucherDiscount - this.pointsDiscount) * ({{ (float) $business->pos_service_charge_percent }} / 100);
                    @else
                        return 0;
                    @endif
                },

                get grandTotal() {
                    const raw = this.subtotal - this.orderDiscountAmount - this.voucherDiscount - this.pointsDiscount + this.taxAmount + this.serviceChargeAmount;
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
                    this.newCustomer = { name: '', phone: '' };
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
                            const validationMessage = data.errors
                                ? Object.values(data.errors).flat()[0]
                                : data.message;
                            throw new Error(validationMessage || 'Pelanggan gagal disimpan.');
                        }

                        this.customers.push(data.customer);
                        this.selectCustomer(data.customer);
                        this.showCustomerModal = false;
                        this.newCustomer = { name: '', phone: '' };
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

                applyVoucher() {
                    if (!this.voucherCode.trim()) return;
                    AppAlert.success("Voucher " + this.voucherCode + " diterapkan!");
                    this.voucherDiscount = Math.min(this.subtotal * 0.1, 50000);
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return;
                    this.currentTenderAmount = this.grandTotal;
                    this.mobileCartOpen = false;
                    this.showPaymentModal = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                toggleMobileCart() {
                    this.mobileCartOpen = !this.mobileCartOpen;
                },

                closeMobileCart() {
                    this.mobileCartOpen = false;
                },

                async submitCheckout() {
                    if (!this.activeShift) {
                        this.showOpenShiftModal = true;
                        AppAlert.warning('Shift kasir belum dibuka. Silakan buka shift terlebih dahulu.');
                        return;
                    }

                    if (this.currentTenderAmount < this.grandTotal) {
                        AppAlert.warning('Nominal pembayaran kurang.');
                        return;
                    }

                    this.isProcessing = true;

                    const payload = {
                        items: this.cart,
                        payments: [
                            { payment_method: this.selectedPayMethod, amount: this.currentTenderAmount }
                        ],
                        customer_id: this.selectedCustomerId || null,
                        customer_name_guest: this.activeTableCustomerName || null,
                        order_type: this.orderType,
                        pos_table_id: this.selectedTable?.id || null,
                        pos_table_session_id: this.selectedTable?.active_session?.id || null,
                        existing_order_id: this.activeTableOrderId || null,
                        discount_type: this.discountType,
                        discount_value: Number(this.discountValue || 0),
                        voucher_code: this.voucherCode || null,
                        points_to_redeem: this.redeemPoints ? Math.round(this.pointsDiscount / 100) : 0,
                        location_id: this.selectedLocationId
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
                            this.lastCompletedOrder = data.order;
                            this.lastReceiptUrl = data.receipt_url;
                            this.lastReceiptImageUrl = data.receipt_image_url || ('/receipt/' + data.order.id + '/image');
                            this.lastWhatsAppUrl = data.whatsapp_url;
                            this.waBotSent = data.whatsapp_bot_sent || false;
                            this.waBotFeedback = this.waBotSent ? '✓ Struk otomatis terkirim ke WhatsApp!' : '';
                            this.waBotFeedbackSuccess = this.waBotSent;
                            this.showPaymentModal = false;
                            this.showSuccessModal = true;
                            this.cart = [];
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

                resetForNewOrder() {
                    this.showSuccessModal = false;
                    this.lastCompletedOrder = null;
                    this.waBotSent = false;
                    this.waBotFeedback = '';
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
                            phone: (customer ? customer.phone : '') || this.lastCompletedOrder?.customer_phone_guest || '',
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
                                this.shiftActualCash = data.summary.expected_cash;
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
                        AppAlert.warning('Silakan buka shift kasir terlebih dahulu.');
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
                            amount: this.cashMovementAmount,
                            reason: this.cashMovementReason
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            AppAlert.success(data.message);
                            this.showCashMovementModal = false;
                            this.cashMovementReason = '';
                        }
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
                            const firstAvailable = group.options.find(o => (o.is_in_stock ?? o.is_available ?? true));
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
                    let base = Number(this.activeModifierProduct.selling_price || 0);
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
                                    summaryParts.push(opt.price_delta > 0 ? `${opt.name} (+${this.formatRupiah(opt.price_delta)})` : opt.name);
                                }
                            });
                        } else if (val) {
                            const opt = group.options.find(o => o.id === val);
                            if (opt) {
                                selectedOptionIds.push(opt.id);
                                summaryParts.push(opt.price_delta > 0 ? `${opt.name} (+${this.formatRupiah(opt.price_delta)})` : opt.name);
                            }
                        }
                    });

                    const unitPrice = this.calculateModifierTotalPrice();
                    const unitSymbol = this.activeModifierProduct.output_unit?.symbol || this.activeModifierProduct.output_unit?.name || '';

                    this.cart.push({
                        cart_item_id: Date.now() + Math.random(),
                        product_id: this.activeModifierProduct.id,
                        product_name: this.activeModifierProduct.name,
                        unit_price: unitPrice,
                        unit_symbol: unitSymbol,
                        quantity: this.modifierItemQty,
                        selected_modifiers: selectedOptionIds,
                        modifiers_summary: summaryParts.join(', '),
                        notes: this.modifierItemNotes.trim(),
                        discount_amount: 0
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
                            headers: { 'Accept': 'application/json' }
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
                            body: JSON.stringify({ reason: this.rejectionReason })
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

                openTablesModal() {
                    this.showTablesModal = true;
                    this.fetchTables();
                },

                async fetchTables() {
                    try {
                        const res = await fetch("{{ route('pos.tables.index') }}", {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.tables = data.tables;
                        }
                    } catch (e) {
                        console.error('Fetch tables error:', e);
                    }
                },

                loadTableOrderToCart(table, autoOpenPayment = false) {
                    if (!table) return;

                    const session = table.active_session;
                    const unpaidOrders = session?.orders?.filter(o => !['completed', 'voided', 'rejected'].includes(o.status)) || [];

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
                    this.activeTableCustomerName = order.customer_name_guest || session?.customer_name || ('Pelanggan Meja ' + table.table_number);

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

                    AppAlert.success(`Pesanan Meja ${table.table_number} (${this.activeTableCustomerName}) berhasil dimuat ke keranjang.`);

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
                            headers: { 'Accept': 'application/json' }
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
                            const targetTable = this.tables.find(t => t.id === order?.pos_table_id || t.table_number == order?.table_or_reference);
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
                    const order = table.active_session.orders.find(o => o.status !== 'completed' && o.status !== 'voided' && o.status !== 'rejected') || table.active_session.orders[0];
                    this.activeTableOrder = order;
                    this.selectedTablePayMethod = 'cash';
                    this.tableTenderAmount = Number(order.total_amount || 0);
                    this.showTablesModal = false;
                    this.showTablePaymentModal = true;
                },

                async submitPayTableOrder() {
                    if (!this.activeTableOrder) return;
                    if (this.tableTenderAmount < this.activeTableOrder.total_amount) {
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
                                payments: [{ payment_method: this.selectedTablePayMethod, amount: this.tableTenderAmount }],
                                location_id: this.selectedLocationId
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            AppAlert.success(data.message || 'Pembayaran berhasil diselesaikan.');
                            this.showTablePaymentModal = false;
                            this.lastCompletedOrder = data.order;
                            this.lastReceiptUrl = data.receipt_url;
                            this.lastReceiptImageUrl = data.receipt_image_url || ('/receipt/' + data.order.id + '/image');
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
                                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            }
                            if (this.audioCtx.state === 'suspended') {
                                this.audioCtx.resume();
                            }
                        } catch (e) {}
                    };
                    ['click', 'touchstart', 'keydown'].forEach(evt => {
                        window.addEventListener(evt, unlock, { once: true, passive: true });
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
                            const tableNum = order.table_number || order.pos_table?.table_number || order.table_or_reference || '-';
                            const custName = order.customer_name || order.customer_name_guest || 'Pelanggan';
                            const title = `🛎️ Pesanan QR Masuk - Meja ${tableNum}` + (totalNewCount > 1 ? ` (+${totalNewCount - 1} pesanan)` : '');
                            const body = `${custName} memesan ${order.items?.length || 0} item • Total: ${this.formatRupiah(order.total_amount)}. Klik untuk buka di kasir.`;

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
                            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
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
