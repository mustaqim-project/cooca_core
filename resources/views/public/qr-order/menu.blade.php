<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Meja {{ $table->table_number }} - {{ $business->name }}</title>

    <!-- Google Fonts (Inter as Apple SF Pro fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Anti-FOUC Theme Bootstrap Script -->
    <script>
        (function() {
            try {
                var stored = localStorage.getItem('cooca-theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

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

    <style>
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
            -webkit-tap-highlight-color: transparent;
        }

        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }

        /* Adaptive POS & Public Shell Architecture */
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

        /* Responsive Desktop Split Screen vs Mobile Bottom Sheet */
        @media (min-width: 1024px) {
            .pos-cart {
                display: flex !important;
                flex-direction: column;
                width: 390px;
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

        @media (max-width: 1023px) {
            .pos-cart {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100% !important;
                max-width: 100% !important;
                height: min(88dvh, 54rem);
                max-height: calc(100dvh - env(safe-area-inset-top, 0px));
                border-radius: 24px 24px 0 0;
                z-index: 120 !important;
                transform: translate3d(0, 102%, 0);
                transition: transform .28s cubic-bezier(.25, .1, .25, 1);
                box-shadow: 0 -20px 50px rgba(0, 0, 0, 0.4);
            }

            .pos-cart.pos-cart-open {
                transform: translate3d(0, 0, 0);
            }

            .pos-cart-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.55);
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
                left: 0.875rem;
                right: 0.875rem;
                bottom: calc(0.875rem + env(safe-area-inset-bottom, 0px));
                z-index: 100 !important;
                transition: opacity .2s, transform .2s;
            }

            .pos-products {
                padding-bottom: 6.5rem;
            }
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

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-[#F2F2F7] dark:bg-[#000000] text-black dark:text-white select-none antialiased transition-colors duration-200"
    x-data="qrOrderApp()"
    x-init="initApp()">

    <div class="pos-shell">

        <!-- ========================================================= -->
        <!-- 0. FLOATING TOAST NOTIFICATION (Apple HIG)               -->
        <!-- ========================================================= -->
        <div x-show="toast.show" x-cloak
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300 transform"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="fixed top-4 inset-x-0 z-[150] flex justify-center px-4 pointer-events-none"
            style="display: none;">
            <div class="pointer-events-auto max-w-md w-full p-3.5 rounded-[18px] backdrop-blur-2xl border shadow-2xl flex items-center gap-3 transition-all"
                :class="{
                    'bg-white/95 dark:bg-[#1C1C1E]/95 border-black/10 dark:border-white/10 text-black dark:text-white': toast.type === 'info',
                    'bg-[#34C759] border-[#34C759]/30 text-white shadow-[#34C759]/20': toast.type === 'success',
                    'bg-[#FF3B30] border-[#FF3B30]/30 text-white shadow-[#FF3B30]/20': toast.type === 'error',
                    'bg-[#FF9500] border-[#FF9500]/30 text-white shadow-[#FF9500]/20': toast.type === 'warning'
                }">
                <!-- Semantic Icon -->
                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0"
                    :class="toast.type === 'info' ? 'bg-[#007AFF]/15 text-[#007AFF]' : 'bg-white/20 text-white'">
                    <template x-if="toast.type === 'error'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </template>
                    <template x-if="toast.type === 'success'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    </template>
                </div>
                <div class="flex-1 text-xs font-semibold leading-snug" x-text="toast.message"></div>
                <button type="button" @click="toast.show = false" class="text-white/80 hover:text-white p-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- 1. TOP HEADER (POS TERMINAL STYLE: NAVY / DARK)           -->
        <!-- ========================================================= -->
        <header class="h-14 sm:h-16 bg-[#0B1528] text-white border-b border-white/10 px-3.5 sm:px-6 flex items-center justify-between gap-3 shrink-0 z-30">
            <!-- Left: Restaurant Info & Table Badge -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                @if ($business->logo_url)
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-[12px] bg-white/10 p-0.5 border border-white/15 overflow-hidden shrink-0 flex items-center justify-center">
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-[12px] bg-[#007AFF] text-white flex items-center justify-center font-black text-sm shadow-sm shadow-[#007AFF]/30 shrink-0">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="font-bold text-[14px] sm:text-[16px] text-white tracking-tight truncate leading-tight">
                            {{ $business->name }}
                        </h1>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-[#007AFF] text-white shadow-sm shrink-0">
                            Meja {{ $table->table_number }}
                        </span>
                    </div>
                    <div class="text-[11px] text-white/50 flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                        <span class="truncate">Pemesanan Mandiri Pelanggan (QR Order)</span>
                    </div>
                </div>
            </div>

            <!-- Center (Desktop Search Bar) -->
            <div class="hidden lg:flex flex-1 max-w-md mx-4">
                <div class="relative w-full">
                    <input type="text" x-model="searchQuery" @input="filterProducts()"
                        placeholder="Cari menu makanan, minuman, cemilan..."
                        class="w-full h-10 pl-9 pr-8 rounded-[12px] bg-white/10 hover:bg-white/15 focus:bg-white/20 border border-white/10 text-xs text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <svg class="w-4 h-4 text-white/40 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''; filterProducts()"
                        class="absolute right-2.5 top-2.5 text-white/50 hover:text-white p-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Right Actions: Orders Tracker, Customer Profile, Theme Toggle -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Tracking Status Button -->
                <button type="button" @click="showTrackingModal = true"
                    class="h-9 px-3 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-95 text-white text-[12px] font-semibold flex items-center gap-1.5 transition border border-white/10"
                    title="Riwayat & Status Pesanan">
                    <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                    <span class="hidden sm:inline">Status Pesanan</span>
                    <span x-show="recentOrders.length > 0" class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-[#34C759] text-white tabular-nums" x-text="recentOrders.length"></span>
                </button>

                <!-- Customer Identity Capsule -->
                <button type="button" @click="showCustomerModal = true"
                    class="h-9 px-3 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-95 text-white text-[12px] font-semibold flex items-center gap-1.5 transition border border-white/10"
                    title="Ganti Identitas Pemesan">
                    <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span class="truncate max-w-[90px] sm:max-w-[120px]" x-text="customerName ? customerName : 'Tamu'"></span>
                </button>

                <!-- Theme Toggle -->
                <button type="button" @click="toggleTheme()"
                    class="w-9 h-9 rounded-[10px] bg-white/10 hover:bg-white/15 active:scale-95 text-white flex items-center justify-center transition border border-white/10"
                    title="Mode Gelap / Terang">
                    <svg x-show="!isDarkMode" class="w-4 h-4 text-[#FFCC00]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    <svg x-show="isDarkMode" class="w-4 h-4 text-[#0A84FF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- ========================================================= -->
        <!-- 2. MAIN SPLIT SCREEN LAYOUT CONTAINER                     -->
        <!-- ========================================================= -->
        <div class="pos-main">

            <!-- ===================================================== -->
            <!-- LEFT / CENTER: CATALOG & CATEGORY TABS                -->
            <!-- ===================================================== -->
            <section class="pos-catalog bg-[#F2F2F7] dark:bg-[#000000]">

                <!-- Subheader Bar: Mobile Search & Category Segmented Pills -->
                <div class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border-b border-black/[0.06] dark:border-white/[0.08] px-3.5 sm:px-6 py-2.5 space-y-2.5 shrink-0 z-20">
                    <!-- Mobile Search Bar (< lg) -->
                    <div class="lg:hidden">
                        <div class="relative w-full">
                            <input type="text" x-model="searchQuery" @input="filterProducts()"
                                placeholder="Cari menu makanan atau minuman..."
                                class="w-full h-10 pl-9 pr-8 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <svg class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''; filterProducts()"
                                class="absolute right-2.5 top-2.5 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Category Pills List (Horizontal Scrollable) -->
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
                        <button type="button" @click="selectCategory('all')"
                            :class="selectedCategory === 'all' ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/30' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                            class="h-8 sm:h-9 px-4 rounded-[10px] text-xs font-semibold whitespace-nowrap transition-all active:scale-[0.97] flex items-center gap-1.5">
                            <span>Semua Menu</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold tabular-nums"
                                :class="selectedCategory === 'all' ? 'bg-white/20 text-white' : 'bg-black/10 dark:bg-white/15 text-black/60 dark:text-white/60'"
                                x-text="allProducts.length"></span>
                        </button>

                        @foreach ($categories as $cat)
                            <button type="button" @click="selectCategory('{{ $cat->id }}')"
                                :class="selectedCategory === '{{ $cat->id }}' ? 'bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/30' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                                class="h-8 sm:h-9 px-4 rounded-[10px] text-xs font-semibold whitespace-nowrap transition-all active:scale-[0.97] flex items-center gap-1.5">
                                <span>{{ $cat->name }}</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold tabular-nums"
                                    :class="selectedCategory === '{{ $cat->id }}' ? 'bg-white/20 text-white' : 'bg-black/10 dark:bg-white/15 text-black/60 dark:text-white/60'"
                                    x-text="getCategoryCount('{{ $cat->id }}')"></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid Scroll Container -->
                <div class="pos-products p-3.5 sm:p-6">

                    <!-- Active Session Banner (if existing orders in table session) -->
                    <template x-if="recentOrders.length > 0">
                        <div class="mb-4 sm:mb-6 p-4 rounded-[20px] bg-[#007AFF]/10 border border-[#007AFF]/25 backdrop-blur-md flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm shadow-[#007AFF]/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wider">Status Meja {{ $table->table_number }}</div>
                                    <div class="text-[13px] font-bold text-black dark:text-white mt-0.5" x-text="recentOrders.length + ' Pesanan sedang diproses'"></div>
                                </div>
                            </div>
                            <button type="button" @click="showTrackingModal = true"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-98 text-white text-xs font-bold transition shadow-sm self-start sm:self-auto">
                                Pantau Status Pesanan
                            </button>
                        </div>
                    </template>

                    <!-- Bento Product Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3.5 sm:gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="openCustomization(product)"
                                class="group relative rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_20px_rgba(0,0,0,0.08)] hover:border-[#007AFF]/40 dark:hover:border-[#0A84FF]/40 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer active:scale-[0.98]">

                                <!-- Top Image Container -->
                                <div class="relative w-full aspect-[4/3] bg-black/[0.03] dark:bg-white/[0.04] overflow-hidden">
                                    <template x-if="product.image_url">
                                        <img :src="product.image_url" :alt="product.name"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </template>
                                    <template x-if="!product.image_url">
                                        <div class="w-full h-full flex flex-col items-center justify-center text-black/20 dark:text-white/20 p-4">
                                            <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </div>
                                    </template>

                                    <!-- Modifier Badge Pill -->
                                    <template x-if="product.modifier_groups && product.modifier_groups.length > 0">
                                        <div class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-black/60 backdrop-blur-md text-white text-[10px] font-semibold flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                                            <span>Opsi Varian</span>
                                        </div>
                                    </template>

                                    <!-- Sold Out Overlay -->
                                    <div x-show="!product.is_available"
                                        class="absolute inset-0 bg-black/75 backdrop-blur-[2px] flex items-center justify-center text-white text-[11px] font-black tracking-widest uppercase">
                                        Habis
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between space-y-2">
                                    <div>
                                        <h3 class="font-bold text-[13px] sm:text-[14px] text-black dark:text-white line-clamp-1 group-hover:text-[#007AFF] transition-colors"
                                            x-text="product.name"></h3>
                                        <p class="text-[11px] text-black/50 dark:text-white/50 line-clamp-2 mt-0.5 leading-snug"
                                            x-text="product.description || 'Pilihan lezat favorit pelanggan.'"></p>
                                    </div>

                                    <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-1.5">
                                        <div>
                                            <div class="font-extrabold text-[13px] sm:text-[14px] text-black dark:text-white tabular-nums tracking-tight"
                                                x-text="formatRupiah(product.selling_price)"></div>
                                            <span class="text-[10px] text-black/40 dark:text-white/40" x-text="'/' + (product.unit_name || 'Porsi')"></span>
                                        </div>

                                        <button type="button" :disabled="!product.is_available"
                                            class="h-8 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-95 disabled:bg-black/10 dark:disabled:bg-white/10 text-white disabled:text-black/30 dark:disabled:text-white/30 text-[11.5px] font-semibold flex items-center gap-1 shadow-sm transition">
                                            <span x-show="product.is_available && (!product.modifier_groups || product.modifier_groups.length === 0)">+ Tambah</span>
                                            <span x-show="product.is_available && product.modifier_groups && product.modifier_groups.length > 0">Pilih</span>
                                            <span x-show="!product.is_available">Habis</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty Catalog State -->
                    <div x-show="filteredProducts.length === 0"
                        class="py-16 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-2 mt-4">
                        <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </div>
                        <h4 class="font-bold text-[15px] text-black dark:text-white">Tidak Ada Menu Ditemukan</h4>
                        <p class="text-xs text-black/50 dark:text-white/50 max-w-xs mx-auto">
                            Coba ubah kata kunci pencarian atau pilih kategori menu lainnya.
                        </p>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- RIGHT: DESKTOP PERMANENT CART SIDEBAR                 -->
            <!-- (ALSO SERVES AS MOBILE APPLE HIG BOTTOM SHEET MODAL)  -->
            <!-- ===================================================== -->
            <!-- Mobile Backdrop Blur Overlay -->
            <div class="pos-cart-backdrop" x-show="showCartModal" x-cloak
                @click="showCartModal = false"></div>

            <aside class="pos-cart bg-white dark:bg-[#1C1C1E] border-l border-black/[0.08] dark:border-white/[0.1] shadow-2xl flex flex-col justify-between"
                :class="{ 'pos-cart-open': showCartModal }">

                <!-- Mobile Top Grab Handle -->
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 lg:hidden shrink-0"></div>

                <!-- Cart Header Bar -->
                <div class="px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-[16px] text-black dark:text-white tracking-tight">Keranjang Pesanan</h2>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] tabular-nums"
                                x-text="cartTotalItems + ' Item'"></span>
                        </div>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">Meja {{ $table->table_number }} • {{ $business->name }}</p>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" x-show="cart.length > 0" @click="clearCart()"
                            class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition">
                            Kosongkan
                        </button>
                        <button type="button" @click="showCartModal = false"
                            class="pos-cart-btn-close w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Customer Identity Inset Card -->
                <div class="px-5 py-2.5 bg-black/[0.015] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[12px] font-bold text-black dark:text-white truncate" x-text="customerName ? customerName : 'Tamu Tanpa Nama'"></div>
                            <div class="text-[10.5px] text-black/45 dark:text-white/45 tabular-nums truncate" x-text="customerPhone ? customerPhone : 'Klik ubah untuk isi No. WhatsApp'"></div>
                        </div>
                    </div>
                    <button type="button" @click="showCustomerModal = true"
                        class="text-[11.5px] font-semibold text-[#007AFF] hover:underline shrink-0">
                        Ubah
                    </button>
                </div>

                <!-- Cart Items Scrollable List -->
                <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3 overscroll-contain">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-black/40 dark:text-white/40 space-y-2">
                            <div class="w-14 h-14 rounded-full bg-black/[0.03] dark:bg-white/[0.04] flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                            </div>
                            <div class="font-bold text-[14px] text-black dark:text-white">Keranjang Kosong</div>
                            <p class="text-[12px] leading-relaxed max-w-[200px]">Pilih menu lezat di sebelah kiri untuk mulai membuat pesanan.</p>
                        </div>
                    </template>

                    <template x-for="(item, idx) in cart" :key="idx">
                        <div class="p-3 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-[13.5px] text-black dark:text-white truncate" x-text="item.product_name"></h4>
                                    <template x-if="item.modifiers_text">
                                        <p class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] mt-0.5 leading-tight" x-text="item.modifiers_text"></p>
                                    </template>
                                    <template x-if="item.notes">
                                        <p class="text-[11px] text-black/50 dark:text-white/50 italic mt-0.5 leading-tight" x-text="'Catatan: ' + item.notes"></p>
                                    </template>
                                </div>
                                <button type="button" @click="removeFromCart(idx)"
                                    class="w-6 h-6 rounded-full text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center transition shrink-0" title="Hapus item">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <div class="text-[13px] font-extrabold text-black dark:text-white tabular-nums"
                                    x-text="formatRupiah(item.quantity * item.unit_price)"></div>

                                <!-- Quantity Stepper -->
                                <div class="flex items-center gap-1.5 bg-white dark:bg-[#2C2C2E] rounded-full p-0.5 border border-black/[0.08] dark:border-white/[0.1] shadow-xs">
                                    <button type="button" @click="updateCartQty(idx, item.quantity - 1)"
                                        class="w-6 h-6 rounded-full bg-black/[0.05] dark:bg-white/[0.1] text-black dark:text-white font-bold text-xs flex items-center justify-center active:scale-95 transition">-</button>
                                    <span class="w-5 text-center text-xs font-bold tabular-nums text-black dark:text-white" x-text="item.quantity"></span>
                                    <button type="button" @click="updateCartQty(idx, item.quantity + 1)"
                                        class="w-6 h-6 rounded-full bg-black/[0.05] dark:bg-white/[0.1] text-black dark:text-white font-bold text-xs flex items-center justify-center active:scale-95 transition">+</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer Checkout & Payment Bento Area -->
                <div class="p-5 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border-t border-black/[0.06] dark:border-white/[0.08] space-y-3 shrink-0">
                    <!-- General Table Notes -->
                    <div>
                        <input type="text" x-model="orderGeneralNotes"
                            placeholder="Catatan Meja (Contoh: Antar bersamaan, sendok 3)..."
                            class="w-full h-9 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Payment Mode Switcher (Bento Tiles) -->
                    <div class="grid grid-cols-2 gap-2">
                        <!-- QRIS Direct Pay -->
                        <button type="button" @click="paymentMode = 'pay_now'"
                            :class="paymentMode === 'pay_now' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] ring-1.5 ring-[#007AFF] shadow-xs' : 'border-black/[0.08] dark:border-white/[0.1] bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70 hover:border-black/20'"
                            class="p-2.5 rounded-[12px] border text-left transition flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[11.5px] flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                                    Bayar QRIS
                                </span>
                                <span class="w-2 h-2 rounded-full" :class="paymentMode === 'pay_now' ? 'bg-[#007AFF]' : 'bg-black/20 dark:bg-white/20'"></span>
                            </div>
                            <span class="text-[9.5px] text-[#34C759] font-bold mt-1">Langsung Diproses</span>
                        </button>

                        <!-- Pay at Cashier -->
                        <button type="button" @click="paymentMode = 'pay_at_cashier'"
                            :class="paymentMode === 'pay_at_cashier' ? 'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] ring-1.5 ring-[#007AFF] shadow-xs' : 'border-black/[0.08] dark:border-white/[0.1] bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70 hover:border-black/20'"
                            class="p-2.5 rounded-[12px] border text-left transition flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[11.5px] flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.25A2.25 2.25 0 010 18.75V10.5M18 21h3.75A2.25 2.25 0 0024 18.75V10.5m-24 0l12-7.5 12 7.5M3.75 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21" /></svg>
                                    Bayar di Kasir
                                </span>
                                <span class="w-2 h-2 rounded-full" :class="paymentMode === 'pay_at_cashier' ? 'bg-[#007AFF]' : 'bg-black/20 dark:bg-white/20'"></span>
                            </div>
                            <span class="text-[9.5px] text-black/45 dark:text-white/45 mt-1">Tunai / EDC Kasir</span>
                        </button>
                    </div>

                    <!-- Total Amount Breakdown -->
                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <div>
                            <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">Total Pembayaran</span>
                            <div class="text-[18px] sm:text-[20px] font-black text-[#34C759] dark:text-[#30D158] tabular-nums leading-tight"
                                x-text="formatRupiah(cartTotalAmount)"></div>
                        </div>

                        <!-- Checkout Button -->
                        <button type="button" @click="submitOrderToCashier()" :disabled="cart.length === 0 || isSubmitting"
                            class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-98 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-md shadow-[#007AFF]/25 transition flex items-center gap-2">
                            <span x-show="!isSubmitting" x-text="paymentMode === 'pay_now' ? 'Bayar QRIS' : 'Kirim Pesanan'"></span>
                            <span x-show="isSubmitting">Memproses...</span>
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>
                </div>
            </aside>
        </div>

        <!-- ========================================================= -->
        <!-- 3. MOBILE FLOATING CART PILL BAR (< 1024px)              -->
        <!-- ========================================================= -->
        <div class="pos-cart-bar" x-show="cartTotalItems > 0" x-cloak
            style="display: none;">
            <div @click="openCartReview()"
                class="w-full p-3.5 rounded-[20px] bg-[#007AFF] hover:bg-[#0071E3] text-white backdrop-blur-xl shadow-[0_12px_36px_rgba(0,122,255,0.4)] flex items-center justify-between cursor-pointer active:scale-[0.98] transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/20 text-white flex items-center justify-center font-black text-xs tabular-nums"
                        x-text="cartTotalItems"></div>
                    <div>
                        <div class="text-[10px] text-white/80 uppercase tracking-wider font-semibold">Pesanan Meja {{ $table->table_number }}</div>
                        <div class="font-extrabold text-[15px] tabular-nums leading-tight" x-text="formatRupiah(cartTotalAmount)"></div>
                    </div>
                </div>

                <div class="flex items-center gap-1 text-xs font-bold bg-white text-[#007AFF] rounded-full px-3.5 py-1.5 shadow-sm">
                    <span>Lihat Keranjang</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================= -->
    <!-- MODAL 1: PRODUCT CUSTOMIZATION & MODIFIERS (Apple Sheet)  -->
    <!-- ========================================================= -->
    <div x-show="showCustomizationModal" x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-md p-0 sm:p-4"
        style="display: none;">
        <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl max-h-[90vh] flex flex-col overflow-hidden transition-all"
            @click.outside="showCustomizationModal = false">

            <!-- Mobile Grab Handle -->
            <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 shrink-0">
                <div class="min-w-0">
                    <h3 class="font-bold text-[17px] text-black dark:text-white truncate"
                        x-text="activeProduct ? activeProduct.name : ''"></h3>
                    <div class="text-xs font-extrabold text-[#007AFF] dark:text-[#0A84FF] tabular-nums mt-0.5"
                        x-text="formatRupiah(activeProduct ? activeProduct.selling_price : 0)"></div>
                </div>
                <button type="button" @click="showCustomizationModal = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/60 hover:text-black dark:hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Scrollable Modifier Groups -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 pr-6 overscroll-contain">
                <template x-for="group in (activeProduct ? activeProduct.modifier_groups : [])" :key="group.id">
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-[13px] text-black dark:text-white flex items-center gap-2">
                                <span x-text="group.name"></span>
                                <span x-show="group.is_required" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">Wajib Diisi</span>
                                <span x-show="!group.is_required" class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50">Opsional</span>
                            </div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 font-semibold"
                                x-text="group.selection_type === 'single' ? 'Pilih 1 Opsi' : 'Bisa Pilih Banyak (Maks ' + group.max_selection + ')'"></span>
                        </div>

                        <!-- Options List -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="opt in group.options" :key="opt.id">
                                <label
                                    :class="!opt.is_available ? 'opacity-40 pointer-events-none' :
                                        (isModifierOptionSelected(group.id, opt.id) ? 'border-[#007AFF] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 ring-1.5 ring-[#007AFF]' : 'border-black/[0.06] dark:border-white/[0.08] bg-white dark:bg-[#1C1C1E] hover:border-black/20')"
                                    class="p-3 rounded-[14px] border flex items-center justify-between transition cursor-pointer">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <!-- Single Choice: Radio -->
                                        <template x-if="group.selection_type === 'single'">
                                            <input type="radio" :name="'mod_group_' + group.id" :value="opt.id"
                                                @change="selectSingleModifier(group.id, opt.id)"
                                                :checked="isModifierOptionSelected(group.id, opt.id)"
                                                class="text-[#007AFF] focus:ring-[#007AFF]">
                                        </template>
                                        <!-- Multiple Choice: Checkbox -->
                                        <template x-if="group.selection_type === 'multiple'">
                                            <input type="checkbox" :value="opt.id"
                                                @change="toggleMultipleModifier(group.id, opt.id, group.max_selection)"
                                                :checked="isModifierOptionSelected(group.id, opt.id)"
                                                class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                        </template>
                                        <span class="text-xs font-semibold text-black dark:text-white truncate" x-text="opt.name"></span>
                                    </div>

                                    <div class="text-xs font-bold tabular-nums shrink-0 ml-2">
                                        <span x-show="opt.price_delta > 0" class="text-[#34C759] dark:text-[#30D158]"
                                            x-text="'+ ' + formatRupiah(opt.price_delta)"></span>
                                        <span x-show="opt.price_delta <= 0" class="text-black/40 dark:text-white/40">+Rp 0</span>
                                        <span x-show="!opt.is_available" class="ml-1 text-[10px] text-[#FF3B30] uppercase">Habis</span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Item Note Input -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70">Catatan Khusus Menu Ini (Opsional)</label>
                    <input type="text" x-model="customItemNote"
                        placeholder="Contoh: Gula 50%, Es sedikit, sambal dipisah..."
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-xs text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <!-- Modal Footer: Stepper and Add Button -->
            <div class="p-5 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.015] dark:bg-white/[0.02] flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2 bg-black/[0.05] dark:bg-white/[0.08] rounded-full p-1 border border-black/5 dark:border-white/10">
                    <button type="button" @click="customQty = Math.max(1, customQty - 1)"
                        class="w-8 h-8 rounded-full bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold flex items-center justify-center shadow-sm active:scale-95 transition">-</button>
                    <span class="w-7 text-center text-xs font-extrabold tabular-nums text-black dark:text-white" x-text="customQty"></span>
                    <button type="button" @click="customQty++"
                        class="w-8 h-8 rounded-full bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold flex items-center justify-center shadow-sm active:scale-95 transition">+</button>
                </div>

                <button type="button" @click="commitCustomizationToCart()"
                    class="flex-1 h-12 px-5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold flex items-center justify-between shadow-sm transition">
                    <span>Tambahkan ke Pesanan</span>
                    <span class="tabular-nums font-black text-sm" x-text="formatRupiah(calculatedItemTotal)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL 2: DYNAMIC QRIS PAY-AT-TABLE (Apple Bento Sheet)    -->
    <!-- ========================================================= -->
    <div x-show="showQrisModal" x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/70 backdrop-blur-md p-0 sm:p-4"
        style="display: none;">
        <div class="w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-center transition-all"
            @click.outside="closeQrisModal()">
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <div class="text-left">
                    <h3 class="font-bold text-[16px] text-black dark:text-white">QRIS Meja {{ $table->table_number }}</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50">Scan dengan GoPay, OVO, BCA, Mandiri, atau m-Banking</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                    Bebas Biaya Admin
                </span>
            </div>

            <!-- Amount Box -->
            <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                <span class="text-xs text-black/60 dark:text-white/60 font-medium">Total Pembayaran</span>
                <span class="font-black text-[18px] text-[#34C759] dark:text-[#30D158] tabular-nums"
                    x-text="activePaymentData ? formatRupiah(activePaymentTotal) : ''"></span>
            </div>

            <!-- QR Code Render Card -->
            <div class="p-4 rounded-[20px] bg-white border border-black/10 shadow-inner flex flex-col items-center justify-center relative">
                <template x-if="activePaymentData && activePaymentData.qr_url">
                    <img :src="activePaymentData.qr_url" alt="QRIS Code" class="w-56 h-56 object-contain rounded-lg">
                </template>
                <div class="mt-2 text-[10.5px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-ping"></span>
                    <span>Menunggu Pembayaran...</span>
                </div>
            </div>

            <!-- Countdown Timer & Auto Polling Notice -->
            <div class="space-y-1 text-xs">
                <div class="flex items-center justify-center gap-1.5 text-black/70 dark:text-white/70 font-semibold">
                    <span>Sisa Waktu Bayar:</span>
                    <span class="font-mono text-[#FF9500] font-extrabold tabular-nums" x-text="qrisCountdownFormatted">15:00</span>
                </div>
                <p class="text-[11px] text-black/40 dark:text-white/40">
                    Halaman akan otomatis terverifikasi begitu Anda selesai membayar.
                </p>
            </div>

            <!-- Actions -->
            <div class="pt-2 space-y-2">
                <template x-if="activePaymentData && activePaymentData.qr_url">
                    <a :href="activePaymentData.qr_url" target="_blank" download="qris-meja-{{ $table->table_number }}.png"
                        class="w-full h-10 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Unduh / Simpan QR</span>
                    </a>
                </template>
                <button type="button" @click="closeQrisModal()"
                    class="w-full h-10 rounded-[12px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black/60 dark:text-white/60 font-semibold text-xs transition">
                    Tutup Sementara (Bayar Nanti di Kasir)
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL 3: INPUT CUSTOMER NAME & PHONE (Required)           -->
    <!-- ========================================================= -->
    <div x-show="showCustomerModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-4"
        style="display: none;">
        <div class="w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4"
            @click.outside="if(isCustomerValid) showCustomerModal = false;">
            <div class="text-center space-y-1">
                <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">Identitas Pemesan</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mohon isi data diri untuk konfirmasi pengantaran ke Meja {{ $table->table_number }}.</p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama Lengkap *</label>
                    <input type="text" x-model="customerName" placeholder="Contoh: Budi Santoso"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70">Nomor WhatsApp / HP *</label>
                        <span x-show="customerPhone.length > 0" class="text-[10px] font-bold"
                            :class="isPhoneValid ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]'"
                            x-text="isPhoneValid ? 'Nomor Valid' : 'Format Belum Tepat'"></span>
                    </div>
                    <input type="tel" x-model="customerPhone"
                        @input="customerPhone = formatPhoneNumber($event.target.value)"
                        placeholder="Contoh: 08123456789 atau 628123456789"
                        :class="{
                            'border-[#34C759] dark:border-[#30D158] focus:ring-[#34C759]/50': isPhoneValid,
                            'border-[#FF3B30] dark:border-[#FF453A] focus:ring-[#FF3B30]/50': customerPhone.length > 0 && !isPhoneValid,
                            'border-black/10 dark:border-white/10 focus:ring-[#007AFF]/50': customerPhone.length === 0
                        }"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border text-[16px] sm:text-xs font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 transition">
                    <p class="text-[10.5px] text-black/45 dark:text-white/45 mt-1">
                        Gunakan nomor aktif 10–15 digit untuk notifikasi pesanan & bukti pembayaran.
                    </p>
                </div>
            </div>

            <div class="pt-2">
                <button type="button" @click="saveCustomerInfo()" :disabled="!isCustomerValid"
                    class="w-full h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                    <span>Mulai Pilih Menu</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL 4: LIVE ORDER TRACKING MODAL                        -->
    <!-- ========================================================= -->
    <div x-show="showTrackingModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-4"
        style="display: none;">
        <div class="w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-center"
            @click.outside="showTrackingModal = false">
            <div class="w-12 h-12 rounded-full bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>

            <div>
                <h3 class="font-bold text-base text-black dark:text-white">Status Pesanan Meja {{ $table->table_number }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Pesanan Anda telah diterima oleh kasir &amp; dapur.</p>
            </div>

            <!-- Recent Orders Feed -->
            <div class="max-h-56 overflow-y-auto space-y-2.5 text-left pr-1">
                <template x-if="recentOrders.length === 0">
                    <div class="text-center py-6 text-xs text-black/40 dark:text-white/40">Belum ada riwayat pesanan di meja ini.</div>
                </template>

                <template x-for="ord in recentOrders" :key="ord.id">
                    <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 text-xs space-y-1.5">
                        <div class="flex justify-between items-center font-bold text-black dark:text-white">
                            <span x-text="'#' + ord.order_number"></span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="ord.is_paid ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20'"
                                    x-text="ord.is_paid ? 'Lunas' : 'Belum Bayar'"></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] uppercase"
                                    x-text="ord.status"></span>
                            </div>
                        </div>
                        <div class="text-[11px] text-black/60 dark:text-white/60"
                            x-text="ord.items ? ord.items.map(i => (i.product_name || i.product?.name || 'Item') + ' (' + i.quantity + ')').join(', ') : ''">
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-black/5 dark:border-white/5">
                            <div class="text-xs font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums"
                                x-text="formatRupiah(ord.total_amount)"></div>
                            <template x-if="!ord.is_paid && ord.gateway_qr_url">
                                <button type="button" @click="reopenPaymentModal(ord)"
                                    class="h-7 px-3 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[11px] font-bold shadow-xs active:scale-95 transition flex items-center gap-1">
                                    <span>Bayar QRIS</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-2 space-y-2">
                <button type="button" @click="showTrackingModal = false"
                    class="w-full h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-xs shadow-sm transition">
                    Pesan Menu Tambahan
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- APPLICATION ALPINE LOGIC                                  -->
    <!-- ========================================================= -->
    <script>
        function qrOrderApp() {
            return {
                allProducts: @json($products),
                filteredProducts: [],
                recentOrders: @json($recentOrders),
                selectedCategory: 'all',
                searchQuery: '',

                customerName: localStorage.getItem('cooca_qr_customer_name') || '',
                customerPhone: localStorage.getItem('cooca_qr_customer_phone') || '',
                showCustomerModal: false,

                // Apple HIG Toast notification state
                toast: {
                    show: false,
                    message: '',
                    type: 'info',
                    timer: null,
                },

                // Theme state
                isDarkMode: document.documentElement.classList.contains('dark'),

                // Customization modal state
                showCustomizationModal: false,
                activeProduct: null,
                selectedModifiers: {}, // { [groupId]: [optionId, ...] }
                customItemNote: '',
                customQty: 1,

                // Cart state
                cart: [],
                orderGeneralNotes: '',
                paymentMode: 'pay_now',
                showCartModal: false,
                showTrackingModal: false,
                isSubmitting: false,

                // QRIS Pay-at-Table state
                showQrisModal: false,
                activePaymentData: null,
                activePaymentTotal: 0,
                activeOrderId: null,
                pollingTimer: null,
                countdownTimer: null,
                qrisCountdown: 900,
                qrisCountdownFormatted: '15:00',

                initApp() {
                    this.filteredProducts = this.allProducts;
                    if (!this.isCustomerValid) {
                        this.showCustomerModal = true;
                    }
                },

                showToast(message, type = 'info', duration = 3500) {
                    if (this.toast.timer) clearTimeout(this.toast.timer);
                    this.toast = {
                        show: true,
                        message: message,
                        type: type,
                        timer: null,
                    };
                    this.toast.timer = setTimeout(() => {
                        this.toast.show = false;
                    }, duration);
                },

                formatPhoneNumber(raw) {
                    let cleaned = (raw || '').replace(/[^0-9+]/g, '');
                    if (cleaned.startsWith('+62')) {
                        cleaned = '0' + cleaned.substring(3);
                    } else if (cleaned.startsWith('62')) {
                        cleaned = '0' + cleaned.substring(2);
                    }
                    return cleaned;
                },

                get isPhoneValid() {
                    const clean = (this.customerPhone || '').replace(/[^0-9]/g, '');
                    return clean.length >= 10 && clean.length <= 15 && (clean.startsWith('08') || clean.startsWith('628'));
                },

                get isCustomerValid() {
                    return this.customerName.trim().length >= 2 && this.isPhoneValid;
                },

                toggleTheme() {
                    if (this.isDarkMode) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('cooca-theme', 'light');
                        this.isDarkMode = false;
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('cooca-theme', 'dark');
                        this.isDarkMode = true;
                    }
                },

                formatRupiah(val) {
                    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
                },

                formatCountdown(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
                },

                startCountdown(duration = 900) {
                    if (this.countdownTimer) clearInterval(this.countdownTimer);
                    this.qrisCountdown = duration;
                    this.qrisCountdownFormatted = this.formatCountdown(this.qrisCountdown);
                    this.countdownTimer = setInterval(() => {
                        if (this.qrisCountdown > 0) {
                            this.qrisCountdown--;
                            this.qrisCountdownFormatted = this.formatCountdown(this.qrisCountdown);
                        } else {
                            clearInterval(this.countdownTimer);
                            if (this.pollingTimer) clearInterval(this.pollingTimer);
                            this.showToast('Waktu pembayaran QRIS telah kedaluwarsa. Silakan lakukan pembayaran di kasir.', 'warning', 5000);
                            this.closeQrisModal();
                        }
                    }, 1000);
                },

                reopenPaymentModal(ord) {
                    this.activeOrderId = ord.id;
                    this.activePaymentTotal = ord.total_amount;
                    this.activePaymentData = {
                        qr_url: ord.gateway_qr_url,
                        pay_code: ord.gateway_pay_code,
                        channel: ord.payment_channel || 'QRIS'
                    };
                    this.showTrackingModal = false;
                    this.showQrisModal = true;
                    this.startCountdown(900);
                    this.startQrisPolling(ord.id);
                },

                closeQrisModal() {
                    if (this.pollingTimer) clearInterval(this.pollingTimer);
                    if (this.countdownTimer) clearInterval(this.countdownTimer);
                    this.showQrisModal = false;
                    this.showTrackingModal = true;
                },

                startQrisPolling(orderId) {
                    if (this.pollingTimer) clearInterval(this.pollingTimer);
                    const baseUrl = "{{ route('public.qr.order.status', [$table->qr_token, 'ORDER_ID_PLACEHOLDER']) }}";
                    const statusUrl = baseUrl.replace('ORDER_ID_PLACEHOLDER', orderId);

                    this.pollingTimer = setInterval(async () => {
                        try {
                            const resp = await fetch(statusUrl, {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (!resp.ok) return;
                            const data = await resp.json();
                            if (data.is_paid) {
                                clearInterval(this.pollingTimer);
                                if (this.countdownTimer) clearInterval(this.countdownTimer);

                                const targetOrd = this.recentOrders.find(o => o.id === orderId);
                                if (targetOrd) {
                                    targetOrd.is_paid = true;
                                    targetOrd.status = data.status || 'confirmed';
                                }

                                this.showToast('Pembayaran QRIS berhasil dikonfirmasi!', 'success');
                                this.showQrisModal = false;
                                this.showTrackingModal = true;
                            }
                        } catch (e) {
                            // network retry silent
                        }
                    }, 3000);
                },

                selectCategory(catId) {
                    this.selectedCategory = catId;
                    this.filterProducts();
                },

                getCategoryCount(catId) {
                    return this.allProducts.filter(p => p.category_id === catId).length;
                },

                filterProducts() {
                    let res = this.allProducts;
                    if (this.selectedCategory !== 'all') {
                        res = res.filter(p => p.category_id === this.selectedCategory);
                    }
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        res = res.filter(p => p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q)));
                    }
                    this.filteredProducts = res;
                },

                saveCustomerInfo() {
                    if (!this.isCustomerValid) {
                        this.showToast('Mohon lengkapi nama dan nomor HP/WhatsApp yang valid (10–15 digit).', 'error');
                        return;
                    }
                    localStorage.setItem('cooca_qr_customer_name', this.customerName.trim());
                    localStorage.setItem('cooca_qr_customer_phone', this.customerPhone.trim());
                    this.showCustomerModal = false;
                    this.showToast('Data pemesan berhasil disimpan.', 'success');
                },

                openCustomization(product) {
                    if (!product.is_available) return;

                    // If product has no modifiers, add directly to cart
                    if (!product.modifier_groups || product.modifier_groups.length === 0) {
                        this.addProductDirectly(product);
                        return;
                    }

                    this.activeProduct = product;
                    this.customQty = 1;
                    this.customItemNote = '';
                    this.selectedModifiers = {};

                    // Pre-select default single choice options if required
                    if (product.modifier_groups) {
                        product.modifier_groups.forEach(g => {
                            if (g.selection_type === 'single' && g.options.length > 0) {
                                const availableFirst = g.options.find(o => o.is_available);
                                if (availableFirst) {
                                    this.selectedModifiers[g.id] = [availableFirst.id];
                                }
                            }
                        });
                    }

                    this.showCustomizationModal = true;
                },

                addProductDirectly(product) {
                    const existingIdx = this.cart.findIndex(i => i.product_id === product.id && (!i.selected_modifiers || i.selected_modifiers.length === 0) && !i.notes);
                    if (existingIdx >= 0) {
                        this.cart[existingIdx].quantity += 1;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            product_name: product.name,
                            unit_price: Number(product.selling_price || 0),
                            quantity: 1,
                            selected_modifiers: [],
                            modifiers_text: '',
                            notes: '',
                        });
                    }
                    this.showToast(product.name + ' ditambahkan ke pesanan.', 'info');
                },

                isModifierOptionSelected(groupId, optionId) {
                    return this.selectedModifiers[groupId] && this.selectedModifiers[groupId].includes(optionId);
                },

                selectSingleModifier(groupId, optionId) {
                    this.selectedModifiers[groupId] = [optionId];
                },

                toggleMultipleModifier(groupId, optionId, maxSelect) {
                    if (!this.selectedModifiers[groupId]) {
                        this.selectedModifiers[groupId] = [];
                    }
                    const idx = this.selectedModifiers[groupId].indexOf(optionId);
                    if (idx >= 0) {
                        this.selectedModifiers[groupId].splice(idx, 1);
                    } else {
                        if (maxSelect > 0 && this.selectedModifiers[groupId].length >= maxSelect) {
                            this.showToast('Maksimal hanya boleh memilih ' + maxSelect + ' opsi.', 'warning');
                            return;
                        }
                        this.selectedModifiers[groupId].push(optionId);
                    }
                },

                get calculatedItemTotal() {
                    if (!this.activeProduct) return 0;
                    let unitPrice = Number(this.activeProduct.selling_price || 0);

                    // Add selected modifiers delta
                    if (this.activeProduct.modifier_groups) {
                        this.activeProduct.modifier_groups.forEach(g => {
                            const selectedIds = this.selectedModifiers[g.id] || [];
                            g.options.forEach(opt => {
                                if (selectedIds.includes(opt.id)) {
                                    unitPrice += Number(opt.price_delta || 0);
                                }
                            });
                        });
                    }
                    return unitPrice * this.customQty;
                },

                commitCustomizationToCart() {
                    // Validate required groups
                    if (this.activeProduct.modifier_groups) {
                        for (const g of this.activeProduct.modifier_groups) {
                            const selectedIds = this.selectedModifiers[g.id] || [];
                            if (g.is_required && selectedIds.length === 0) {
                                this.showToast("Mohon pilih opsi untuk '" + g.name + "'.", 'warning');
                                return;
                            }
                        }
                    }

                    // Gather selected option IDs and display text
                    const allSelectedOptionIds = [];
                    const selectedTextParts = [];
                    let finalUnitPrice = Number(this.activeProduct.selling_price || 0);

                    if (this.activeProduct.modifier_groups) {
                        this.activeProduct.modifier_groups.forEach(g => {
                            const selectedIds = this.selectedModifiers[g.id] || [];
                            g.options.forEach(opt => {
                                if (selectedIds.includes(opt.id)) {
                                    allSelectedOptionIds.push(opt.id);
                                    finalUnitPrice += Number(opt.price_delta || 0);
                                    const deltaStr = opt.price_delta > 0 ? ' (+Rp ' + Number(opt.price_delta).toLocaleString('id-ID') + ')' : '';
                                    selectedTextParts.push(opt.name + deltaStr);
                                }
                            });
                        });
                    }

                    this.cart.push({
                        product_id: this.activeProduct.id,
                        product_name: this.activeProduct.name,
                        unit_price: finalUnitPrice,
                        quantity: this.customQty,
                        selected_modifiers: allSelectedOptionIds,
                        modifiers_text: selectedTextParts.join(', '),
                        notes: this.customItemNote.trim(),
                    });

                    this.showCustomizationModal = false;
                    this.showToast('Menu ditambahkan ke keranjang.', 'info');
                },

                get cartTotalItems() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get cartTotalAmount() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },

                updateCartQty(idx, newQty) {
                    if (newQty <= 0) {
                        this.removeFromCart(idx);
                    } else {
                        this.cart[idx].quantity = newQty;
                    }
                },

                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                    if (this.cart.length === 0) {
                        this.showCartModal = false;
                    }
                },

                clearCart() {
                    this.cart = [];
                    this.showCartModal = false;
                    this.showToast('Keranjang pesanan dikosongkan.', 'info');
                },

                openCartReview() {
                    if (!this.isCustomerValid) {
                        this.showCustomerModal = true;
                        return;
                    }
                    this.showCartModal = true;
                },

                async submitOrderToCashier() {
                    if (!this.isCustomerValid) {
                        this.showCartModal = false;
                        this.showCustomerModal = true;
                        this.showToast('Mohon lengkapi identitas pemesan terlebih dahulu.', 'warning');
                        return;
                    }

                    if (this.cart.length === 0) return;
                    this.isSubmitting = true;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch("{{ route('public.qr.order', $table->qr_token) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({
                                customer_name: this.customerName.trim(),
                                customer_phone: this.customerPhone.trim(),
                                notes: this.orderGeneralNotes.trim(),
                                payment_mode: this.paymentMode,
                                payment_channel: 'QRIS',
                                items: this.cart.map(item => ({
                                    product_id: item.product_id,
                                    quantity: item.quantity,
                                    selected_modifiers: item.selected_modifiers,
                                    notes: item.notes,
                                }))
                            })
                        });

                        const res = await response.json();
                        if (!res.success) {
                            this.showToast(res.message || 'Gagal mengirim pesanan.', 'error');
                            this.isSubmitting = false;
                            return;
                        }

                        // Order success
                        this.cart = [];
                        this.orderGeneralNotes = '';
                        this.showCartModal = false;
                        this.recentOrders.unshift(res.order);
                        this.showToast(res.message || 'Pesanan berhasil dikirim ke kasir!', 'success');

                        if (res.payment && res.payment.qr_url) {
                            this.activePaymentData = res.payment;
                            this.activePaymentTotal = res.order.total_amount;
                            this.activeOrderId = res.order.id;
                            this.showQrisModal = true;
                            this.startCountdown(900);
                            this.startQrisPolling(res.order.id);
                        } else {
                            this.showTrackingModal = true;
                        }
                    } catch (e) {
                        this.showToast('Terjadi kesalahan jaringan: ' + e.message, 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>

</html>
