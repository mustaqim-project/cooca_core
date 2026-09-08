<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Kasir POS — {{ $business->name }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
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
        html, body { overflow-x: hidden; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        .pos-shell { min-height: 100dvh; }
        [x-cloak] { display: none !important; }
        .pos-cart-bar { display: none; }
        .pos-cart-backdrop { display: none; }
        .pos-cart-btn-close { display: none; }
        @media (max-width: 767px) {
            .pos-shell { height: 100dvh; overflow: hidden; }
            .pos-header { min-height: 4.5rem; height: auto; padding: calc(.6rem + env(safe-area-inset-top, 0px)) .65rem .6rem; align-items: center; gap: .4rem; position: relative; flex-wrap: nowrap; z-index: 30; }
            .pos-header-brand { min-width: 0; flex: 1 1 auto; }
            .pos-header-brand .business-name { max-width: 40vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .85rem; }
            .pos-header-actions { flex-shrink: 0; flex-wrap: nowrap; gap: .35rem; }
            .pos-header-actions .shift-status { position: static; transform: none; width: auto; padding: .35rem .6rem; font-size: 10px; border-radius: .75rem; }
            .pos-header-actions .action-label, .pos-header-actions .cashier-name, .pos-header-actions .cashier-role { display: none; }
            .pos-header-actions .location-selector { display: none; }
            /* Do not create a stacking context here.
               The cart drawer/backdrop live in different DOM layers, so a z-index
               on .pos-main can cause the backdrop to render above the cart. */
            .pos-main { display: flex; flex-direction: column; overflow: hidden; position: relative; z-index: auto; }
            .pos-catalog { width: 100%; height: 100%; flex: 1 1 auto; padding: .65rem; padding-bottom: 5.4rem; gap: .65rem; }
            .pos-search-row { display: block; }
            .pos-search-row .search-box { min-width: 0; width: 100%; }
            .pos-search-row .category-bar { margin-top: .5rem; max-width: 100%; }
            .pos-products { padding-right: 0; }
            .pos-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
            .pos-product-card { padding: .65rem; border-radius: .85rem; }
            .pos-product-card .product-code { display: none; }
            .pos-product-card h4 { font-size: .8rem; }
            .pos-product-card .pos-price { font-size: .8rem; }

            /* Prevent flex/grid children from forcing horizontal overflow */
            .pos-catalog,
            .pos-search-row,
            .pos-products,
            .pos-product-grid,
            .pos-product-card { min-width: 0; }

            .pos-products { overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
            .category-bar { scrollbar-width: none; }
            .category-bar::-webkit-scrollbar { display: none; }

            /* Keep the mobile header usable on very narrow devices */
            .pos-header-brand > a { flex: 0 0 auto; }
            .pos-header-brand > div { min-width: 0; }
            .pos-header-actions { min-width: 0; }

            /* Cart becomes a bottom sheet drawer with higher z-index */
            .pos-cart {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100% !important;
                max-width: 100% !important;
                height: min(78dvh, 46rem);
                max-height: calc(100dvh - env(safe-area-inset-top, 0px));
                min-height: 0;
                overflow: hidden;
                border-radius: 1.25rem 1.25rem 0 0;
                border-left: 0;
                border-top: 1px solid #1e293b;
                border-bottom: 0;
                z-index: 120 !important;
                isolation: isolate;
                transform: translate3d(0, 102%, 0);
                transition: transform .28s cubic-bezier(.4,0,.2,1);
                box-shadow: 0 -16px 48px -12px rgba(0,0,0,.65);
            }
            .pos-cart.pos-cart-open { transform: translate3d(0, 0, 0); }
            .pos-cart-items {
                min-height: 0;
                max-height: none;
                flex: 1 1 auto;
                overflow-y: auto;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }
            .pos-cart-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(2,6,23,.62);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                z-index: 110 !important;
            }
            .pos-cart-btn-close { display: inline-flex !important; }
            .pos-cart-bar {
                display: flex;
                position: fixed;
                left: .65rem;
                right: .65rem;
                bottom: calc(.65rem + env(safe-area-inset-bottom, 0px));
                z-index: 100 !important;
                transition: opacity .2s, transform .2s;
                padding-bottom: max(.625rem, env(safe-area-inset-bottom, 0px));
            }
            .pos-modal-panel { max-height: calc(100dvh - 1rem); padding: 1rem !important; border-radius: 1.1rem; margin: 0 .5rem; }
            .pos-qty-btn { width: 2rem; height: 2rem; }

            /* Payment modal adaptability */
            .pos-pay-methods { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pos-pay-presets { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pos-total-line { flex-wrap: wrap; }

            /* Additional refined fixes */
            body:has(.pos-cart.pos-cart-open) .pos-cart-bar {
                opacity: 0;
                pointer-events: none;
                transform: translateY(100%);
            }
            /* Responsive mobile navbar */
            .pos-header {
                width: 100%;
                min-width: 0;
                padding-left: .6rem;
                padding-right: .6rem;
                gap: .5rem;
            }

            .pos-header-brand {
                min-width: 0;
                flex: 1 1 auto;
                overflow: hidden;
                gap: .45rem;
            }

            .pos-header-brand-main {
                min-width: 0;
                flex: 1 1 auto;
            }

            .pos-header-back {
                width: 2.15rem;
                height: 2.15rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0;
            }

            .pos-header-logo {
                width: 2.15rem;
                height: 2.15rem;
                border-radius: .7rem;
            }

            .pos-header-logo svg {
                width: 1rem;
                height: 1rem;
            }

            .pos-header-brand .business-name {
                display: block;
                width: 100%;
                max-width: none;
                font-size: .78rem;
                line-height: 1.05;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .pos-header-subtitle {
                font-size: 8px;
                line-height: 1;
                letter-spacing: .06em;
                white-space: nowrap;
            }

            .pos-header-actions {
                flex: 0 0 auto;
                min-width: 0;
                gap: .3rem;
            }

            .pos-header-shift-status {
                width: 2.15rem;
                height: 2.15rem;
                padding: 0;
                justify-content: center;
                gap: 0;
                border-radius: .7rem;
                font-size: 0;
            }

            .pos-header-shift-status .shift-dot {
                width: .5rem;
                height: .5rem;
            }

            .pos-header-shift-status .shift-label,
            .pos-header-shift-status .shift-action {
                display: none;
            }

            .pos-header-hold-btn,
            .pos-header-cash-btn {
                width: 2.15rem;
                height: 2.15rem;
                padding: 0;
                justify-content: center;
                gap: 0;
                border-radius: .7rem;
            }

            .pos-header-hold-btn .action-label,
            .pos-header-cash-btn .action-label {
                display: none;
            }

            .pos-header-hold-btn i,
            .pos-header-cash-btn i {
                width: 1rem;
                height: 1rem;
            }

            .pos-header-cashier-info {
                display: flex;
                padding-left: .3rem;
                margin-left: .05rem;
                border-left: 1px solid rgba(51,65,85,.8);
            }

            .pos-cashier-avatar {
                width: 2.15rem;
                height: 2.15rem;
                border-radius: .7rem;
                font-size: .75rem;
            }

            .pos-product-card { padding: .6rem; }
            .pos-product-card h4 { font-size: .75rem; }
            .pos-product-card .pos-price { font-size: .75rem; }
            .pos-cart-bar { transition: opacity .2s, transform .2s; }

            /* Ensure cart content is fully visible */
            .pos-cart-footer {
                position: relative;
                flex: 0 0 auto;
                background: rgba(15, 23, 42, 0.98);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                z-index: 2;
                padding-bottom: max(1rem, env(safe-area-inset-bottom, 0px));
            }

            /* Ensure cart doesn't get hidden behind header */
            .pos-cart {
                margin-top: 0;
            }
        }
        @media (max-width: 767px) and (orientation: landscape) {
            .pos-catalog { padding-bottom: 5.5rem; height: 100%; }
            .pos-product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .45rem; }
            .pos-cart {
                height: 100dvh;
                max-height: 100dvh;
                border-radius: .6rem 0 0 0;
                width: min(92vw, 30rem) !important;
                right: 0;
                left: auto;
                z-index: 120 !important;
            }
            .pos-cart-bar {
                max-width: calc(100vw - 1.3rem);
                padding: .5rem .65rem;
                z-index: 100 !important;
            }
            .pos-cart-items { max-height: none; }
            .pos-header {
                padding: calc(.4rem + env(safe-area-inset-top, 0px)) .65rem .4rem;
                z-index: 30;
            }
            body:has(.pos-cart.pos-cart-open) .pos-cart-bar {
                opacity: 0;
                pointer-events: none;
                transform: translateY(100%);
            }
        }
        @media (max-width: 380px) {
            .pos-product-grid { gap: .4rem; }
            .pos-product-card .pos-price { font-size: .72rem; }
            .pos-product-card .stock-chip { padding: .15rem .4rem; font-size: 9px; }
            .pos-header-hold-btn, .pos-header-cash-btn { padding: .3rem .4rem; }
            .pos-header-brand .business-name { font-size: .72rem; }
            .pos-header-subtitle { display: none; }
            .pos-header-back,
            .pos-header-logo,
            .pos-header-shift-status,
            .pos-header-hold-btn,
            .pos-header-cash-btn,
            .pos-cashier-avatar {
                width: 2rem;
                height: 2rem;
            }
            .pos-header-brand { gap: .3rem; }
            .pos-header-actions { gap: .2rem; }
        }
        @media (min-width: 768px) and (max-width: 1023px) {
            .pos-cart { width: 21rem; }
            .pos-product-grid { gap: .6rem; }
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased select-none"
      x-data="posApp()"
      x-init="initPos()">

    <div class="pos-shell h-screen flex flex-col overflow-hidden">

        <!-- POS Top Navbar -->
        <header class="pos-header h-16 glass-panel flex items-center justify-between px-4 sm:px-6 shrink-0 z-20">
            <!-- Brand / Navigation -->
            <div class="pos-header-brand flex items-center gap-3 min-w-0">
                <a href="{{ route('dashboard') }}"
                   class="pos-header-back shrink-0 p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition"
                   title="Kembali ke Dashboard"
                   aria-label="Kembali ke Dashboard">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>

                <div class="pos-header-brand-main flex items-center gap-2 min-w-0">
                    <div class="pos-header-logo w-9 h-9 shrink-0 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="business-name font-extrabold text-base text-white tracking-tight leading-none truncate">{{ $business->name }}</div>
                        <div class="pos-header-subtitle text-[11px] font-semibold text-emerald-400 uppercase tracking-wider mt-0.5">Terminal Kasir POS</div>
                    </div>
                </div>

                <!-- Location Selector -->
                <div class="location-selector hidden md:flex items-center gap-2 ml-4 px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-800 text-xs shrink-0">
                    <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400"></i>
                    <select x-model="selectedLocationId" @change="changeLocation()" class="bg-transparent text-slate-200 font-medium focus:outline-none cursor-pointer max-w-[180px]">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" class="bg-slate-900 text-slate-200" {{ $loc->id === $selectedLocationId ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Outlet') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Top Actions -->
            <div class="pos-header-actions flex items-center gap-2 shrink-0">
                <!-- Shift Indicator -->
                <template x-if="activeShift">
                    <div class="pos-header-shift-status shift-status flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-400">
                        <span class="shift-dot w-2 h-2 shrink-0 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="shift-label">Shift Terbuka</span>
                        <button @click="openShiftCloseModal()" class="shift-action ml-1 px-1.5 py-0.5 rounded bg-emerald-500/20 hover:bg-emerald-500/30 text-[10px] font-bold text-white transition">Tutup Shift</button>
                    </div>
                </template>
                <template x-if="!activeShift">
                    <div class="pos-header-shift-status shift-status flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-400">
                        <span class="shift-dot w-2 h-2 shrink-0 rounded-full bg-amber-500"></span>
                        <span class="shift-label">Shift Belum Dibuka</span>
                        <button @click="showOpenShiftModal = true" class="shift-action ml-1 px-1.5 py-0.5 rounded bg-amber-500/20 hover:bg-amber-500/30 text-[10px] font-bold text-white transition">Buka Shift</button>
                    </div>
                </template>

                <!-- Held Carts -->
                <button @click="showHeldOrdersModal = true"
                        class="pos-header-hold-btn relative flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 text-xs font-semibold transition"
                        title="Antrean Hold"
                        aria-label="Antrean Hold">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-400 shrink-0"></i>
                    <span class="action-label">Antrean Hold</span>
                    <span x-show="heldOrders.length > 0" x-text="heldOrders.length" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold text-[10px] flex items-center justify-center"></span>
                </button>

                <!-- Cash In / Out -->
                <button @click="showCashMovementModal = true"
                        class="pos-header-cash-btn flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 text-xs font-semibold transition"
                        title="Kas Masuk / Keluar"
                        aria-label="Kas Masuk / Keluar">
                    <i data-lucide="banknote" class="w-4 h-4 text-teal-400 shrink-0"></i>
                    <span class="action-label">Kas Masuk/Keluar</span>
                </button>

                <!-- Cashier -->
                <div class="pos-header-cashier-info flex items-center gap-2 pl-2 border-l border-slate-800 text-xs">
                    <div class="pos-cashier-avatar w-7 h-7 rounded-lg bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold shrink-0"
                         title="{{ $user->name }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="hidden lg:block text-left min-w-0">
                        <div class="cashier-name font-bold text-white leading-tight truncate max-w-[120px]">{{ $user->name }}</div>
                        <div class="cashier-role text-[10px] text-slate-400">Kasir</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- POS Main Work Area -->
        <div class="pos-main flex-1 flex overflow-hidden">

            <!-- Left Area: Catalog & Barcode Scanner -->
            <div class="pos-catalog flex-1 flex flex-col overflow-hidden p-4 gap-4">

                <!-- Search & Category Bar -->
                <div class="pos-search-row flex flex-wrap items-center gap-3">
                    <!-- Search Input with Barcode Icon -->
                    <div class="search-box relative flex-1 min-w-[240px]">
                        <i data-lucide="barcode" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-400"></i>
                        <input type="text"
                               x-ref="barcodeSearchInput"
                               x-model="searchQuery"
                               @keydown.enter="handleBarcodeOrSearch()"
                               placeholder="Cari nama produk atau Scan Barcode (Tekan Enter)..."
                               class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-11 pr-24 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50">
                        <button type="button" @click="requestBarcodeScannerAccess()"
                                class="absolute right-9 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-emerald-400 hover:bg-emerald-500/15 hover:text-emerald-300 transition"
                                title="Scan barcode dengan kamera" aria-label="Scan barcode dengan kamera">
                            <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                        </button>
                        <button x-show="searchQuery" @click="searchQuery = ''; filterProducts()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Category Pills -->
                    <div class="category-bar flex items-center gap-2 overflow-x-auto py-1 max-w-full">
                        <button @click="selectedCategory = 'all'; filterProducts()"
                                :class="selectedCategory === 'all' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:bg-slate-800'"
                                class="px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition">
                            Semua
                        </button>
                        @foreach($categories as $cat)
                            <button @click="selectedCategory = '{{ $cat->id }}'; filterProducts()"
                                    :class="selectedCategory === '{{ $cat->id }}' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:bg-slate-800'"
                                    class="px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid (Touch Cards) -->
                <div class="pos-products flex-1 overflow-y-auto pr-1">
                    <div class="pos-product-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                               <div @click="addToCart(product)"
                                   class="pos-product-card glass-card rounded-2xl p-3.5 cursor-pointer hover:border-emerald-500/40 hover:bg-slate-800/80 transition-all flex flex-col justify-between group active:scale-95">
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-slate-950 transition">
                                            <i data-lucide="package" class="w-4 h-4"></i>
                                        </div>
                                        <span :class="product.current_stock > 0 ? 'bg-slate-800 text-slate-300 border border-slate-700' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'"
                                              class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                              x-text="'Stok: ' + product.current_stock">
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-sm text-white line-clamp-2 leading-tight group-hover:text-emerald-300 transition" x-text="product.name"></h4>
                                    <div class="product-code text-[11px] text-slate-400 font-mono mt-1" x-text="product.code || '-'"></div>
                                </div>
                                <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between">
                                    <span class="font-extrabold text-sm text-emerald-400 font-mono" x-text="formatRupiah(product.selling_price)"></span>
                                    <div class="w-6 h-6 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-slate-950 transition">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="filteredProducts.length === 0" class="h-64 flex flex-col items-center justify-center text-center text-slate-500">
                        <i data-lucide="inbox" class="w-12 h-12 stroke-1 mb-2 text-slate-600"></i>
                        <div class="text-sm font-semibold text-slate-400">Tidak ada produk ditemukan</div>
                        <div class="text-xs text-slate-500">Coba ubah kata kunci pencarian atau kategori</div>
                    </div>
                </div>
            </div>

            <!-- Right Area: Cart & Checkout Panel -->
            <div class="pos-cart w-96 sm:w-[420px] glass-panel border-l border-slate-800 flex flex-col shrink-0 z-10"
                 :class="mobileCartOpen ? 'pos-cart-open' : ''">

                <!-- Draggable handle (mobile only) -->
                <div class="pos-cart-btn-close lg:hidden items-center justify-center pt-2 cursor-pointer" @click="mobileCartOpen = false">
                    <div class="w-10 h-1 rounded-full bg-slate-700"></div>
                </div>

                <!-- Cart Header: Customer & Order Type -->
                <div class="p-4 border-b border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="font-extrabold text-base text-white flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-400"></i>
                            <span>Keranjang Pesanan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="clearCart()" x-show="cart.length > 0" class="text-xs text-rose-400 hover:text-rose-300 font-medium transition">
                                Kosongkan
                            </button>
                            <button @click="closeMobileCart()" class="pos-cart-btn-close lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Tutup keranjang">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Customer Selector with Member Tier -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="user" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select x-model="selectedCustomerId" @change="onCustomerSelected()" class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-9 pr-8 py-2 text-xs text-slate-200 focus:outline-none focus:border-emerald-500/50 cursor-pointer">
                                <option value="">Pelanggan Umum (Guest)</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" class="bg-slate-900 text-slate-200">
                                        {{ $cust->name }} ({{ strtoupper($cust->membership_tier ?? 'Bronze') }} • {{ $cust->points_balance }} Poin)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" @click="openCustomerModal()"
                                class="shrink-0 w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 transition flex items-center justify-center"
                                title="Tambah pelanggan cepat" aria-label="Tambah pelanggan cepat">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </button>
                        <select x-model="orderType" class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none cursor-pointer">
                            <option value="takeaway">Bungkus</option>
                            <option value="dine_in">Makan di Tempat</option>
                            <option value="delivery">Kirim</option>
                        </select>
                    </div>

                    <!-- Member Loyalty Card Preview if selected -->
                    <div x-show="activeCustomer" class="p-2.5 rounded-xl bg-gradient-to-r from-emerald-950/60 to-teal-950/60 border border-emerald-500/30 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-white flex items-center gap-1.5">
                                <span x-text="activeCustomer ? activeCustomer.name : ''"></span>
                                <span class="px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 font-bold uppercase text-[9px]" x-text="activeCustomer ? activeCustomer.membership_tier : ''"></span>
                            </div>
                            <div class="text-[11px] text-emerald-400">
                                Saldo Poin: <span class="font-bold font-mono" x-text="activeCustomer ? activeCustomer.points_balance : 0"></span> Poin
                            </div>
                        </div>
                        <template x-if="activeCustomer && activeCustomer.points_balance > 0">
                            <button @click="togglePointsRedemption()"
                                    :class="redeemPoints ? 'bg-emerald-500 text-slate-950' : 'bg-slate-900 text-emerald-400 border border-emerald-500/30'"
                                    class="px-2 py-1 rounded-lg text-[10px] font-bold transition">
                                <span x-text="redeemPoints ? 'Batalkan Poin' : 'Tukar Poin'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="pos-cart-items flex-1 overflow-y-auto p-4 space-y-2.5">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800/90 flex flex-col gap-2 hover:border-slate-700 transition">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <div class="font-bold text-xs text-white leading-tight" x-text="item.product_name"></div>
                                    <div class="text-[11px] font-mono text-emerald-400 mt-0.5" x-text="formatRupiah(item.unit_price)"></div>
                                </div>
                                <div class="font-extrabold text-xs text-white font-mono" x-text="formatRupiah(item.quantity * item.unit_price)"></div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-800/60">
                                <div class="flex items-center gap-1.5 bg-slate-950 rounded-lg p-0.5 border border-slate-800">
                                    <button @click="decrementQty(index)" class="pos-qty-btn w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-xs">-</button>
                                    <span class="w-8 text-center font-mono font-bold text-xs text-white" x-text="item.quantity"></span>
                                    <button @click="incrementQty(index)" class="pos-qty-btn w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-xs">+</button>
                                </div>
                                <button @click="removeFromCart(index)" class="p-1 rounded-lg text-slate-500 hover:text-rose-400 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center text-slate-500">
                        <i data-lucide="shopping-bag" class="w-12 h-12 stroke-1 mb-2 text-slate-600"></i>
                        <div class="text-sm font-semibold text-slate-400">Keranjang Kosong</div>
                        <div class="text-xs text-slate-500 mt-0.5">Pilih produk di katalog atau scan barcode</div>
                    </div>
                </div>

                <!-- Cart Calculations & Total (Sticky Bottom) -->
                <div class="pos-cart-footer p-4 border-t border-slate-800 bg-slate-950/90 space-y-3">
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-mono font-medium text-slate-200" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <!-- Order Discount: percentage or fixed nominal -->
                        <div class="flex items-center gap-2 pt-1">
                            <select x-model="discountType" class="w-28 bg-slate-900 border border-slate-800 rounded-lg px-2 py-1.5 text-[11px] text-slate-200 focus:outline-none focus:border-emerald-500">
                                <option value="fixed">Diskon Rp</option>
                                <option value="percentage">Diskon %</option>
                            </select>
                            <input type="number" x-model.number="discountValue" min="0" :max="discountType === 'percentage' ? 100 : subtotal" step="any"
                                   :placeholder="discountType === 'percentage' ? 'Contoh: 10' : 'Contoh: 10000'"
                                   class="min-w-0 flex-1 bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                        </div>
                        <div x-show="orderDiscountAmount > 0" class="flex justify-between text-rose-300">
                            <span>Diskon Transaksi</span>
                            <span class="font-mono" x-text="'-' + formatRupiah(orderDiscountAmount)"></span>
                        </div>

                        <!-- Voucher / Promo Code -->
                        <div class="flex items-center gap-2 pt-1">
                            <input type="text" x-model="voucherCode" placeholder="Kode Voucher/Promo" class="flex-1 bg-slate-900 border border-slate-800 rounded-lg px-2.5 py-1 text-xs text-slate-200 uppercase font-mono focus:outline-none focus:border-emerald-500">
                            <button @click="applyVoucher()" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">Terapkan</button>
                        </div>

                        <div x-show="voucherDiscount > 0" class="flex justify-between text-emerald-400">
                            <span>Diskon Voucher</span>
                            <span class="font-mono" x-text="'-' + formatRupiah(voucherDiscount)"></span>
                        </div>

                        <div x-show="pointsDiscount > 0" class="flex justify-between text-teal-400">
                            <span>Tukar Poin</span>
                            <span class="font-mono" x-text="'-' + formatRupiah(pointsDiscount)"></span>
                        </div>

                        <div x-show="taxAmount > 0" class="flex justify-between text-slate-400">
                            <span>Pajak PPN ({{ $business->pos_tax_percent }}%)</span>
                            <span class="font-mono text-slate-200" x-text="formatRupiah(taxAmount)"></span>
                        </div>

                        <div x-show="serviceChargeAmount > 0" class="flex justify-between text-slate-400">
                            <span>Service Charge ({{ $business->pos_service_charge_percent }}%)</span>
                            <span class="font-mono text-slate-200" x-text="formatRupiah(serviceChargeAmount)"></span>
                        </div>
                    </div>

                    <!-- Grand Total -->
                    <div class="pt-2 border-t border-slate-800 flex items-baseline justify-between">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Tagihan</div>
                            <div class="text-xs text-slate-500 font-mono" x-text="cart.length + ' Item Terpilih'"></div>
                        </div>
                        <div class="text-2xl font-black text-emerald-400 font-mono tracking-tight" x-text="formatRupiah(grandTotal)"></div>
                    </div>

                    <!-- Actions: Hold Cart & Bayar / Checkout -->
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="promptHoldCart()"
                                :disabled="cart.length === 0"
                                class="col-span-1 px-3 py-3 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 font-bold text-xs flex flex-col items-center justify-center gap-1 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="pause-circle" class="w-4 h-4 text-amber-400"></i>
                            <span>Hold Cart</span>
                        </button>
                        <button @click="openPaymentModal()"
                                :disabled="cart.length === 0"
                                class="col-span-2 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-extrabold text-sm shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                            <span>Bayar Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Mobile Cart Drawer Backdrop -->
    <div x-show="mobileCartOpen" x-cloak class="pos-cart-backdrop" @click="closeMobileCart()"></div>

    <!-- Mobile Fixed Cart Bottom Bar -->
    <div class="pos-cart-bar items-center gap-2 glass-panel rounded-2xl px-3 py-2.5" x-cloak>
        <button @click="toggleMobileCart()" class="flex items-center gap-3 flex-1 min-w-0 text-left" type="button">
            <span class="relative shrink-0">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-400"></i>
                <span x-show="cart.length > 0" x-text="cart.length"
                      class="absolute -top-2 -right-2 min-w-[1.1rem] h-[1.1rem] px-0.5 rounded-full bg-amber-500 text-slate-950 font-black text-[10px] flex items-center justify-center"></span>
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-[10px] text-slate-400 uppercase tracking-wide">Keranjang</span>
                <span class="block font-black text-emerald-400 font-mono text-sm truncate" x-text="formatRupiah(grandTotal)"></span>
            </span>
        </button>
        <button @click="openPaymentModal()" :disabled="cart.length === 0" type="button"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-emerald-500/25 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5 transition">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>Bayar</span>
        </button>
    </div>

    <!-- MODAL: Quick Customer -->
    <div x-show="showCustomerModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
         @keydown.escape.window="showCustomerModal = false">
        <div class="pos-modal-panel w-full max-w-sm glass-panel rounded-2xl border border-slate-700 p-5 space-y-4"
             @click.outside="showCustomerModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-extrabold text-base text-white">Tambah Pelanggan Cepat</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Simpan nama dan nomor HP tanpa meninggalkan kasir.</p>
                </div>
                <button type="button" @click="showCustomerModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="createQuickCustomer" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Nama Pelanggan *</label>
                    <input x-ref="quickCustomerName" type="text" x-model="newCustomer.name" required maxlength="255"
                           placeholder="Contoh: Budi Santoso"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Nomor HP *</label>
                    <input type="tel" x-model="newCustomer.phone" required maxlength="50" inputmode="tel"
                           placeholder="08xxxxxxxxxx"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <p x-show="customerFormError" x-text="customerFormError" class="text-xs text-rose-300"></p>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showCustomerModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 font-semibold text-xs hover:bg-slate-800">Batal</button>
                    <button type="submit" :disabled="isCreatingCustomer"
                            class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs disabled:opacity-50">
                        <span x-text="isCreatingCustomer ? 'Menyimpan...' : 'Simpan Pelanggan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 1: Payment Modal (Split Payment Ready!) -->
    <div x-show="showPaymentModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
         style="display: none;">
        <div class="pos-modal-panel w-full max-w-2xl glass-panel rounded-2xl border border-slate-700 p-6 flex flex-col max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-extrabold text-lg text-white">Pembayaran Kasir</h3>
                </div>
                <button @click="showPaymentModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="py-4 space-y-4">
                <!-- Tagihan Summary -->
                <div class="pos-total-line p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-slate-400">Total yang harus dibayar:</div>
                        <div class="text-2xl font-black text-emerald-400 font-mono" x-text="formatRupiah(grandTotal)"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-400">Total Diterima:</div>
                        <div class="text-lg font-bold text-white font-mono" x-text="formatRupiah(totalTendered)"></div>
                    </div>
                </div>

                <!-- Payment Methods Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Metode Pembayaran</label>
                    <div class="pos-pay-methods grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button @click="selectedPayMethod = 'cash'" :class="selectedPayMethod === 'cash' ? 'bg-emerald-500 text-slate-950 font-bold border-emerald-400' : 'bg-slate-900 text-slate-300 border-slate-800'" class="p-3 rounded-xl border flex flex-col items-center gap-1.5 text-xs transition">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                            <span>Tunai (Cash)</span>
                        </button>
                        <button @click="selectedPayMethod = 'qris'" :class="selectedPayMethod === 'qris' ? 'bg-emerald-500 text-slate-950 font-bold border-emerald-400' : 'bg-slate-900 text-slate-300 border-slate-800'" class="p-3 rounded-xl border flex flex-col items-center gap-1.5 text-xs transition">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                            <span>QRIS / E-Wallet</span>
                        </button>
                        <button @click="selectedPayMethod = 'transfer'" :class="selectedPayMethod === 'transfer' ? 'bg-emerald-500 text-slate-950 font-bold border-emerald-400' : 'bg-slate-900 text-slate-300 border-slate-800'" class="p-3 rounded-xl border flex flex-col items-center gap-1.5 text-xs transition">
                            <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                            <span>Transfer Bank</span>
                        </button>
                        <button @click="selectedPayMethod = 'edc_debit'" :class="selectedPayMethod === 'edc_debit' ? 'bg-emerald-500 text-slate-950 font-bold border-emerald-400' : 'bg-slate-900 text-slate-300 border-slate-800'" class="p-3 rounded-xl border flex flex-col items-center gap-1.5 text-xs transition">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                            <span>Kartu Debit/EDC</span>
                        </button>
                    </div>
                </div>

                <!-- Input Nominal Tender -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nominal Bayar (Rp)</label>
                    <input type="number" x-model.number="currentTenderAmount" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-xl font-bold font-mono text-white focus:outline-none focus:border-emerald-500">

                    <!-- Quick Cash Presets (only for Cash) -->
                    <div x-show="selectedPayMethod === 'cash'" class="pos-pay-presets grid grid-cols-4 gap-2 mt-2">
                        <button @click="currentTenderAmount = grandTotal" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-bold text-emerald-400 font-mono">Uang Pas</button>
                        <button @click="currentTenderAmount = 20000" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 font-mono">20.000</button>
                        <button @click="currentTenderAmount = 50000" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 font-mono">50.000</button>
                        <button @click="currentTenderAmount = 100000" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 font-mono">100.000</button>
                    </div>
                </div>

                <!-- Kembalian / Sisa Bayar -->
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400">Kembalian:</span>
                    <span class="text-lg font-black text-emerald-400 font-mono" x-text="formatRupiah(Math.max(0, currentTenderAmount - grandTotal))"></span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <button @click="showPaymentModal = false" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 font-semibold text-sm">Batal</button>
                <button @click="submitCheckout()"
                        :disabled="isProcessing || currentTenderAmount < grandTotal"
                        class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-sm shadow-lg shadow-emerald-500/20 disabled:opacity-40 transition flex items-center gap-2">
                    <span x-show="!isProcessing">Proses Transaksi</span>
                    <span x-show="isProcessing">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Payment Success & Receipt Modal -->
    <div x-show="showSuccessModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
         style="display: none;">
        <div class="pos-modal-panel w-full max-w-md glass-panel rounded-2xl border border-emerald-500/40 p-6 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
                <i data-lucide="check" class="w-8 h-8"></i>
            </div>
            <div>
                <h3 class="font-black text-xl text-white">Transaksi Berhasil!</h3>
                <div class="text-xs text-slate-400 mt-1" x-text="'No. Struk: ' + (lastCompletedOrder ? lastCompletedOrder.order_number : '')"></div>
            </div>

            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Total Pembelian:</span>
                    <span class="font-bold text-white font-mono" x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.total_amount : 0)"></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Total Bayar:</span>
                    <span class="font-bold text-white font-mono" x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.paid_amount : 0)"></span>
                </div>
                <div class="flex justify-between text-emerald-400 font-bold border-t border-slate-800 pt-2 text-sm">
                    <span>Kembalian:</span>
                    <span class="font-mono text-base" x-text="formatRupiah(lastCompletedOrder ? lastCompletedOrder.change_amount : 0)"></span>
                </div>
            </div>

            <!-- Action Buttons: Print Thermal & WhatsApp -->
            <div class="space-y-2 pt-2">
                <!-- Bot WhatsApp Direct Send -->
                <button type="button" @click="sendWhatsAppBotReceipt()" :disabled="sendingWaBot"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#22c55e] hover:to-[#0f7a6a] text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-lg shadow-[#25D366]/25 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
                    <span x-text="sendingWaBot ? 'Mengirim Struk ke WA...' : (waBotSent ? '✓ Struk Terkirim ke WhatsApp' : 'Kirim Bot WhatsApp (Otomatis)')"></span>
                </button>
                <div x-show="waBotFeedback" class="text-[11px] font-semibold py-1 px-2 rounded-lg" :class="waBotFeedbackSuccess ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'" x-text="waBotFeedback"></div>

                <a :href="lastReceiptUrl" target="_blank" class="w-full py-3 rounded-xl bg-slate-900 border border-slate-700 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-2 transition">
                    <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                    <span>Cetak Struk Thermal (58mm/80mm)</span>
                </a>
                <a :href="lastWhatsAppUrl" target="_blank" class="w-full py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 font-medium text-xs flex items-center justify-center gap-2 transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Kirim Manual via WhatsApp Web / App</span>
                </a>
                <button @click="resetForNewOrder()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Open Shift Modal -->
    <div x-show="showOpenShiftModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Buka Shift Kasir Baru</h3>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Modal Awal Kasir (Rp)</label>
                    <input type="number" x-model.number="shiftOpeningCash" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold font-mono text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Catatan Shift (Opsional)</label>
                    <input type="text" x-model="shiftNotes" placeholder="Catatan shift..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-slate-200 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button @click="showOpenShiftModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 font-semibold text-xs">Batal</button>
                <button @click="submitOpenShift()" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Buka Shift</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: Close Shift Modal with Reconciliation -->
    <div x-show="showCloseShiftModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Tutup Shift Kasir & Rekonsiliasi</h3>

            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Modal Awal:</span>
                    <span class="font-mono text-slate-200" x-text="formatRupiah(shiftSummary.opening_cash)"></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Penjualan Tunai:</span>
                    <span class="font-mono text-slate-200" x-text="formatRupiah(shiftSummary.cash_sales)"></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Kas Masuk / Keluar:</span>
                    <span class="font-mono text-slate-200" x-text="formatRupiah(shiftSummary.cash_in - shiftSummary.cash_out)"></span>
                </div>
                <div class="flex justify-between text-emerald-400 font-bold border-t border-slate-800 pt-1.5 text-sm">
                    <span>Uang Fisik Diharapkan:</span>
                    <span class="font-mono" x-text="formatRupiah(shiftSummary.expected_cash)"></span>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Hitungan Kas Fisik Aktual di Laci (Rp)</label>
                    <input type="number" x-model.number="shiftActualCash" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold font-mono text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Selisih Kas:</span>
                    <span :class="(shiftActualCash - shiftSummary.expected_cash) === 0 ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold'"
                          x-text="formatRupiah(shiftActualCash - shiftSummary.expected_cash)"></span>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button @click="showCloseShiftModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 font-semibold text-xs">Batal</button>
                <button @click="submitCloseShift()" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs">Tutup & Rekonsiliasi</button>
            </div>
        </div>
    </div>

    <!-- MODAL 5: Held Orders Modal -->
    <div x-show="showHeldOrdersModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-lg glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="font-extrabold text-lg text-white">Daftar Antrean Transaksi (Held Carts)</h3>
                <button @click="showHeldOrdersModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="max-h-72 overflow-y-auto space-y-2">
                <template x-for="ho in heldOrders" :key="ho.id">
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white" x-text="ho.hold_label || 'Antrean'"></div>
                            <div class="text-[11px] text-slate-400 font-mono" x-text="ho.order_number + ' • ' + (ho.items ? ho.items.length : 0) + ' item'"></div>
                        </div>
                        <button @click="resumeHeldOrder(ho.id)" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition">
                            Lanjutkan
                        </button>
                    </div>
                </template>
                <div x-show="heldOrders.length === 0" class="text-center py-8 text-xs text-slate-500">
                    Tidak ada transaksi yang di-hold saat ini.
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 6: Cash Movement Modal (Kas Masuk / Keluar) -->
    <div x-show="showCashMovementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="pos-modal-panel w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Catat Kas Masuk / Kas Keluar</h3>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Tipe Pergerakan</label>
                    <select x-model="cashMovementType" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        <option value="cash_in">Kas Masuk (Tambah Modal/Uang Pecahan)</option>
                        <option value="cash_out">Kas Keluar (Biaya Operasional Toko / Setor)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Nominal (Rp)</label>
                    <input type="number" x-model.number="cashMovementAmount" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-lg font-bold font-mono text-white">
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Alasan / Keterangan</label>
                    <input type="text" x-model="cashMovementReason" placeholder="Misal: Beli gas, es batu, uang kembalian..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button @click="showCashMovementModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 font-semibold text-xs">Batal</button>
                <button @click="submitCashMovement()" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs">Simpan</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Camera Permission -->
    <div x-show="showScannerPermission" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-black/85 backdrop-blur-sm p-4">
        <div class="pos-modal-panel w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="camera" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-lg text-white">Izinkan Akses Kamera?</h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Kamera hanya digunakan saat Anda memilih Scan Barcode. Browser akan meminta izin kamera dan kamera berhenti setelah scanner ditutup.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" @click="showScannerPermission = false" class="px-4 py-2 rounded-xl bg-slate-900 text-slate-300 font-semibold text-xs">Batal</button>
                <button type="button" @click="confirmBarcodeScannerAccess()" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Lanjutkan</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Camera Barcode Scanner -->
    <div x-show="showBarcodeScanner" x-cloak @keydown.escape.window="closeBarcodeScanner()"
         class="fixed inset-0 z-[70] flex items-center justify-center bg-black/85 backdrop-blur-sm p-4"
         @click.self="closeBarcodeScanner()">
        <div class="pos-modal-panel w-full max-w-lg glass-panel rounded-2xl border border-slate-700 p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-extrabold text-lg text-white">Scan Barcode Produk</h3>
                    <p class="text-xs text-slate-400 mt-1">Arahkan kamera ke barcode sampai produk terdeteksi.</p>
                </div>
                <button type="button" @click="closeBarcodeScanner()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800" title="Tutup scanner" aria-label="Tutup scanner">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="relative aspect-video overflow-hidden rounded-2xl bg-slate-950 border border-slate-800">
                <video x-ref="barcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-[72%] h-[42%] rounded-xl border-2 border-emerald-400 shadow-[0_0_0_9999px_rgba(2,6,23,.38)]"></div>
                </div>
                <div x-show="scannerStarting" class="absolute inset-0 flex items-center justify-center bg-slate-950/70 text-xs text-slate-300">
                    <span class="flex items-center gap-2"><i data-lucide="loader-circle" class="w-4 h-4 animate-spin"></i> Menyiapkan kamera...</span>
                </div>
            </div>

            <div x-show="scannerError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300" x-text="scannerError"></div>

            <div x-show="scannerDevices.length > 1" class="space-y-1">
                <label class="block text-[10px] text-slate-400 font-bold uppercase">Kamera</label>
                <select x-model="selectedScannerDeviceId" @change="startBarcodeScanner()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    <template x-for="device in scannerDevices" :key="device.deviceId">
                        <option :value="device.deviceId" x-text="device.label || 'Kamera ' + (scannerDevices.indexOf(device) + 1)"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-800">
                <span class="text-[11px] text-slate-500">Scanner USB/Bluetooth tetap bisa digunakan melalui kolom pencarian.</span>
                <button type="button" @click="closeBarcodeScanner()" class="shrink-0 px-4 py-2 rounded-xl bg-slate-900 text-slate-300 hover:text-white font-semibold text-xs">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Alpine.js POS State Engine -->
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

                // Cart state
                cart: [],
                selectedCustomerId: '',
                activeCustomer: null,
                orderType: 'takeaway',
                voucherCode: '',
                voucherDiscount: 0,
                discountType: 'fixed',
                discountValue: 0,
                redeemPoints: false,
                pointsDiscount: 0,

                // Modals
                showPaymentModal: false,
                showSuccessModal: false,
                showOpenShiftModal: false,
                showCloseShiftModal: false,
                showHeldOrdersModal: false,
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
                    this.filteredProducts = this.allProducts;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                        existing.quantity++;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            product_name: product.name,
                            unit_price: Number(product.selling_price || 0),
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
                    this.cart[idx].quantity++;
                },

                decrementQty(idx) {
                    if (this.cart[idx].quantity > 1) {
                        this.cart[idx].quantity--;
                    } else {
                        this.removeFromCart(idx);
                    }
                },

                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                clearCart() {
                    if (confirm('Kosongkan semua item di keranjang?')) {
                        this.cart = [];
                        this.discountValue = 0;
                        this.discountType = 'fixed';
                        this.voucherDiscount = 0;
                        this.pointsDiscount = 0;
                        this.redeemPoints = false;
                    }
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
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
                        this.selectedCustomerId = data.customer.id;
                        this.onCustomerSelected();
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
                        // 1 point = Rp100
                        const maxPointsDiscount = this.activeCustomer.points_balance * 100;
                        this.pointsDiscount = Math.min(this.subtotal, maxPointsDiscount);
                    } else {
                        this.pointsDiscount = 0;
                    }
                },

                applyVoucher() {
                    if (!this.voucherCode.trim()) return;
                    // Simple AJAX or client validation
                    alert("Voucher " + this.voucherCode + " diterapkan!");
                    this.voucherDiscount = Math.min(this.subtotal * 0.1, 50000); // sample 10% voucher
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
                        alert('Shift kasir belum dibuka. Silakan buka shift terlebih dahulu.');
                        return;
                    }

                    if (this.currentTenderAmount < this.grandTotal) {
                        alert('Nominal pembayaran kurang.');
                        return;
                    }

                    this.isProcessing = true;

                    const payload = {
                        items: this.cart,
                        payments: [
                            { payment_method: this.selectedPayMethod, amount: this.currentTenderAmount }
                        ],
                        customer_id: this.selectedCustomerId || null,
                        order_type: this.orderType,
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
                            this.lastWhatsAppUrl = data.whatsapp_url;
                            this.waBotSent = data.whatsapp_bot_sent || false;
                            this.waBotFeedback = this.waBotSent ? '✓ Struk otomatis terkirim ke WhatsApp!' : '';
                            this.waBotFeedbackSuccess = this.waBotSent;
                            this.showPaymentModal = false;
                            this.showSuccessModal = true;
                            this.cart = [];
                            this.voucherCode = '';
                            this.voucherDiscount = 0;
                            this.discountValue = 0;
                            this.discountType = 'fixed';
                            this.pointsDiscount = 0;
                            this.redeemPoints = false;
                        } else {
                            alert('Gagal: ' + data.message);
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
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
                    if (!this.lastCompletedOrder) return;
                    this.sendingWaBot = true;
                    this.waBotFeedback = '';
                    const customer = this.customers.find(c => c.id === this.selectedCustomerId);
                    fetch("{{ url('/whatsapp/orders') }}/" + this.lastCompletedOrder.id + "/receipt", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            phone: customer ? customer.phone : ''
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
                    const label = prompt('Masukkan nama/label antrean meja atau pelanggan:');
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
                            alert(data.message);
                            this.cart = [];
                            this.refreshHeldOrders();
                        } else {
                            alert(data.message);
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
                            alert(data.message);
                        } else {
                            alert(data.message);
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
                            alert(data.message);
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
                            alert(data.message);
                            this.activeShift = null;
                            this.showCloseShiftModal = false;
                        }
                    });
                },

                submitCashMovement() {
                    if (!this.activeShift) {
                        alert('Silakan buka shift kasir terlebih dahulu.');
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
                            alert(data.message);
                            this.showCashMovementModal = false;
                            this.cashMovementReason = '';
                        }
                    });
                },

                changeLocation() {
                    window.location.href = "{{ route('pos.terminal') }}?location_id=" + this.selectedLocationId;
                }
            }
        }
    </script>
</body>
</html>
