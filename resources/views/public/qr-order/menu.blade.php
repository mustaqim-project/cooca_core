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
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html,
        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(128, 128, 128, 0.2);
            border-radius: 4px;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body
    class="bg-[#F2F2F7] dark:bg-[#000000] text-black dark:text-white min-h-full flex flex-col antialiased select-none pb-28 transition-colors duration-200"
    x-data="qrOrderApp()">

    <!-- Top Sticky Header (Apple Navigation Bar) -->
    <header
        class="sticky top-0 z-30 bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border-b border-black/10 dark:border-white/10 transition-all">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                @if ($business->logo_url)
                    <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                        class="w-8 h-8 rounded-full object-contain border border-black/5 dark:border-white/10 p-0.5">
                @else
                    <div
                        class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ substr($business->name, 0, 2) }}
                    </div>
                @endif
                <div>
                    <h1
                        class="font-bold text-[15px] tracking-tight text-black dark:text-white truncate max-w-[170px] leading-tight">
                        {{ $business->name }}</h1>
                    <div class="text-[11px] font-medium text-black/50 dark:text-white/50 flex items-center gap-1">
                        <span>Pesan dari Meja</span>
                        <span class="w-1 h-1 rounded-full bg-black/30 dark:bg-white/30"></span>
                        <span class="text-[#007AFF] dark:text-[#0A84FF] font-bold">{{ $table->table_number }}</span>
                    </div>
                </div>
            </div>

            <!-- Action buttons: Theme Toggle & Customer Identity Pill -->
            <div class="flex items-center gap-1.5">
                <!-- Apple Theme Switcher Button -->
                <button type="button" @click="toggleTheme()"
                    class="w-8 h-8 rounded-full bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-95 border border-black/5 dark:border-white/10 flex items-center justify-center transition text-black/70 dark:text-white/80"
                    title="Ganti Mode Tampilan">
                    <svg x-show="!isDarkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                    <svg x-show="isDarkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                </button>

                <!-- Customer Identity Pill -->
                <button type="button" @click="showCustomerModal = true"
                    class="h-8 px-3 rounded-full bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-95 border border-black/5 dark:border-white/10 text-xs font-semibold text-black dark:text-white flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-black/60 dark:text-white/60" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span class="truncate max-w-[80px]" x-text="customerName ? customerName : 'Nama & HP'"></span>
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="max-w-md mx-auto px-4 pb-2.5">
            <div class="relative">
                <input type="text" x-model="searchQuery" @input="filterProducts()"
                    placeholder="Cari makanan atau minuman..."
                    class="w-full h-9 pl-9 pr-4 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] border border-transparent focus:border-black/15 dark:focus:border-white/20 focus:bg-white dark:focus:bg-[#2C2C2E] text-xs text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none transition">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3 top-2.5" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
        </div>

        <!-- Category Pills (Horizontal Scroll) -->
        <div class="max-w-md mx-auto px-4 pb-2.5 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button type="button" @click="selectCategory('all')"
                :class="selectedCategory === 'all' ? 'bg-black dark:bg-white text-white dark:text-black shadow-sm' :
                    'bg-black/[0.04] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                class="h-7 px-3 rounded-full text-xs font-semibold whitespace-nowrap transition">
                Semua Menu
            </button>
            @foreach ($categories as $cat)
                <button type="button" @click="selectCategory('{{ $cat->id }}')"
                    :class="selectedCategory === '{{ $cat->id }}' ?
                        'bg-black dark:bg-white text-white dark:text-black shadow-sm' :
                        'bg-black/[0.04] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'"
                    class="h-7 px-3 rounded-full text-xs font-semibold whitespace-nowrap transition">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-md mx-auto px-4 pt-3 flex-1 w-full space-y-4">

        <!-- Active Orders Alert Banner (if user already submitted orders in this session) -->
        <template x-if="recentOrders.length > 0">
            <div
                class="p-3.5 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wider">
                        Status Meja Anda</div>
                    <div class="text-xs font-semibold text-black dark:text-white mt-0.5"
                        x-text="recentOrders.length + ' Pesanan sedang diproses' "></div>
                </div>
                <button type="button" @click="showTrackingModal = true"
                    class="h-7 px-3 rounded-[8px] bg-[#007AFF] text-white text-[11px] font-bold transition shadow-sm active:scale-95">
                    Lacak Status
                </button>
            </div>
        </template>

        <!-- Product List Cards -->
        <div class="space-y-3">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="openCustomization(product)"
                    class="p-3.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex gap-3.5 cursor-pointer active:scale-[0.99] transition hover:border-[#007AFF]/40 dark:hover:border-[#0A84FF]/40">
                    <!-- Thumbnail -->
                    <div
                        class="w-20 h-20 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] overflow-hidden flex-shrink-0 flex items-center justify-center relative border border-black/5 dark:border-white/10">
                        <template x-if="product.image_url">
                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!product.image_url">
                            <svg class="w-8 h-8 text-black/20 dark:text-white/20" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </template>
                        <!-- Sold out badge -->
                        <div x-show="!product.is_available"
                            class="absolute inset-0 bg-black/70 backdrop-blur-[1px] flex items-center justify-center text-white text-[10px] font-bold tracking-wider uppercase">
                            Habis
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <h3 class="font-bold text-[14px] text-black dark:text-white truncate"
                                    x-text="product.name"></h3>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 line-clamp-2 mt-0.5 leading-relaxed"
                                x-text="product.description || 'Pilihan lezat favorit pelanggan.'"></p>
                        </div>

                        <div
                            class="flex items-center justify-between mt-2 pt-1.5 border-t border-black/5 dark:border-white/10">
                            <div class="font-extrabold text-[13px] text-black dark:text-white tabular-nums"
                                x-text="formatRupiah(product.selling_price)"></div>

                            <button type="button" :disabled="!product.is_available"
                                class="h-7 px-3 rounded-full bg-[#007AFF] hover:bg-[#0062CC] active:scale-95 disabled:bg-black/10 dark:disabled:bg-white/10 text-white disabled:text-black/30 dark:disabled:text-white/30 text-xs font-semibold flex items-center gap-1 shadow-sm transition">
                                <span x-show="product.is_available">Tambah</span>
                                <span x-show="!product.is_available">Habis</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty Search State -->
            <div x-show="filteredProducts.length === 0"
                class="py-12 text-center text-xs text-black/40 dark:text-white/40 space-y-1">
                <div>Tidak ada menu yang sesuai dengan pencarian Anda.</div>
            </div>
        </div>
    </main>

    <!-- ========================================================= -->
    <!-- STICKY FLOATING CART BAR                                  -->
    <!-- ========================================================= -->
    <div x-show="cartTotalItems > 0" class="fixed bottom-5 inset-x-0 z-40 px-4 max-w-md mx-auto"
        style="display: none;">
        <div @click="openCartReview()"
            class="p-3.5 rounded-[22px] bg-black/90 dark:bg-white/95 text-white dark:text-black backdrop-blur-xl border border-white/20 dark:border-black/10 shadow-[0_12px_36px_rgba(0,0,0,0.35)] flex items-center justify-between cursor-pointer active:scale-[0.98] transition">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs tabular-nums shadow-sm"
                    x-text="cartTotalItems"></div>
                <div>
                    <div class="text-[10px] text-white/70 dark:text-black/60 uppercase tracking-wider font-semibold">
                        Keranjang Pesanan</div>
                    <div class="font-bold text-sm tabular-nums" x-text="formatRupiah(cartTotalAmount)"></div>
                </div>
            </div>

            <div
                class="flex items-center gap-1 text-xs font-bold text-[#007AFF] bg-white dark:bg-black rounded-full px-4 py-1.5 shadow-sm">
                <span>Lihat Pesanan</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: PRODUCT CUSTOMIZATION & MODIFIERS (Apple Sheet)    -->
    <!-- ========================================================= -->
    <div x-show="showCustomizationModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-[4px] p-0 sm:p-4"
        style="display: none;">
        <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 p-5 shadow-2xl space-y-4 max-h-[85vh] flex flex-col transition-all"
            @click.outside="showCustomizationModal = false">
            <!-- Sheet Header -->
            <div class="flex items-start justify-between pb-2 border-b border-black/10 dark:border-white/10">
                <div>
                    <h3 class="font-bold text-[17px] text-black dark:text-white"
                        x-text="activeProduct ? activeProduct.name : ''"></h3>
                    <div class="text-xs font-extrabold text-[#007AFF] dark:text-[#0A84FF] tabular-nums mt-0.5"
                        x-text="formatRupiah(activeProduct ? activeProduct.selling_price : 0)"></div>
                </div>
                <button type="button" @click="showCustomizationModal = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/60 hover:text-black dark:hover:text-white transition">✕</button>
            </div>

            <!-- Scrollable Modifier Groups -->
            <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                <template x-for="group in (activeProduct ? activeProduct.modifier_groups : [])" :key="group.id">
                    <div
                        class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-xs text-black dark:text-white flex items-center gap-1.5">
                                <span x-text="group.name"></span>
                                <span x-show="group.is_required" class="text-[#FF3B30] font-black text-xs">*</span>
                            </div>
                            <span class="text-[10px] text-black/40 dark:text-white/40 font-semibold"
                                x-text="group.selection_type === 'single' ? 'Pilih 1' : 'Pilihan bebas'"></span>
                        </div>

                        <!-- Options List -->
                        <div class="space-y-1.5">
                            <template x-for="opt in group.options" :key="opt.id">
                                <label
                                    :class="!opt.is_available ? 'opacity-40 pointer-events-none' :
                                        'cursor-pointer hover:bg-black/5 dark:hover:bg-white/5'"
                                    class="p-2.5 rounded-[12px] border border-black/5 dark:border-white/10 flex items-center justify-between transition">
                                    <div class="flex items-center gap-2.5">
                                        <!-- Single Choice: Radio -->
                                        <template x-if="group.selection_type === 'single'">
                                            <input type="radio" :name="'mod_group_' + group.id"
                                                :value="opt.id"
                                                @change="selectSingleModifier(group.id, opt.id)"
                                                :checked="selectedModifiers[group.id] && selectedModifiers[group.id].includes(opt
                                                    .id)"
                                                class="text-[#007AFF] focus:ring-[#007AFF]/50">
                                        </template>
                                        <!-- Multiple Choice: Checkbox -->
                                        <template x-if="group.selection_type === 'multiple'">
                                            <input type="checkbox" :value="opt.id"
                                                @change="toggleMultipleModifier(group.id, opt.id, group.max_selection)"
                                                :checked="selectedModifiers[group.id] && selectedModifiers[group.id].includes(opt
                                                    .id)"
                                                class="rounded text-[#007AFF] focus:ring-[#007AFF]/50">
                                        </template>
                                        <span class="text-xs font-semibold text-black dark:text-white"
                                            x-text="opt.name"></span>
                                    </div>

                                    <div class="text-xs font-bold tabular-nums">
                                        <span x-show="opt.price_delta > 0" class="text-[#34C759] dark:text-[#30D158]"
                                            x-text="'+ ' + formatRupiah(opt.price_delta)"></span>
                                        <span x-show="opt.price_delta <= 0"
                                            class="text-black/40 dark:text-white/40">+Rp 0</span>
                                        <span x-show="!opt.is_available"
                                            class="ml-1.5 text-[10px] text-[#FF3B30] uppercase">Habis</span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Item Note Input -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70">Catatan Khusus Menu Ini
                        (Opsional)</label>
                    <input type="text" x-model="customItemNote"
                        placeholder="Contoh: Less ice, gula pisah, pedas sedang..."
                        class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <!-- Stepper and Submit -->
            <div class="pt-2 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-3">
                <div
                    class="flex items-center gap-2 bg-black/[0.05] dark:bg-white/[0.08] rounded-full p-1 border border-black/5 dark:border-white/10">
                    <button type="button" @click="customQty = Math.max(1, customQty - 1)"
                        class="w-8 h-8 rounded-full bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold flex items-center justify-center shadow-sm active:scale-95 transition">-</button>
                    <span class="w-6 text-center text-xs font-bold tabular-nums text-black dark:text-white"
                        x-text="customQty"></span>
                    <button type="button" @click="customQty++"
                        class="w-8 h-8 rounded-full bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold flex items-center justify-center shadow-sm active:scale-95 transition">+</button>
                </div>

                <button type="button" @click="commitCustomizationToCart()"
                    class="flex-1 h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] text-white text-xs font-bold flex items-center justify-between shadow-sm transition">
                    <span>Tambahkan Pesanan</span>
                    <span class="tabular-nums" x-text="formatRupiah(calculatedItemTotal)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: CART REVIEW & SUBMIT SHEET (Apple Sheet)           -->
    <!-- ========================================================= -->
    <div x-show="showCartModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-[4px] p-0 sm:p-4"
        style="display: none;">
        <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 p-5 shadow-2xl space-y-4 max-h-[90vh] flex flex-col transition-all"
            @click.outside="showCartModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
                <div>
                    <h3 class="font-bold text-[17px] text-black dark:text-white">Konfirmasi Pesanan</h3>
                    <div class="text-xs text-black/50 dark:text-white/50"
                        x-text="'Meja ' + '{{ $table->table_number }}' + ' • ' + (customerName || 'Tamu')"></div>
                </div>
                <button type="button" @click="showCartModal = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/60 hover:text-black dark:hover:text-white transition">✕</button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 divide-y divide-black/5 dark:divide-white/5">
                <template x-for="(item, idx) in cart" :key="idx">
                    <div class="pt-2.5 first:pt-0 flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <div class="font-bold text-xs text-black dark:text-white" x-text="item.product_name">
                            </div>
                            <div class="text-[11px] text-[#007AFF] dark:text-[#0A84FF] font-medium"
                                x-show="item.modifiers_text" x-text="item.modifiers_text"></div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 italic" x-show="item.notes"
                                x-text="'Catatan: ' + item.notes"></div>
                            <div class="text-xs text-black/60 dark:text-white/60 mt-1 tabular-nums"
                                x-text="item.quantity + ' × ' + formatRupiah(item.unit_price) + ' = ' + formatRupiah(item.quantity * item.unit_price)">
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="removeFromCart(idx)"
                                class="w-7 h-7 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center text-xs font-bold active:scale-90 transition">
                                ✕
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Order Notes & Customer Info Confirmation -->
            <div class="space-y-2.5 pt-2 border-t border-black/10 dark:border-white/10">
                <div>
                    <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Catatan
                        Keseluruhan Meja</label>
                    <input type="text" x-model="orderGeneralNotes"
                        placeholder="Contoh: Tolong diantar bersamaan..."
                        class="w-full h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1.5 text-xs">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>Pemesan:</span>
                        <span class="font-semibold text-black dark:text-white"
                            x-text="customerName + ' (' + customerPhone + ')'"></span>
                    </div>
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>Nomor Meja:</span>
                        <span class="font-bold text-black dark:text-white">{{ $table->table_number }}</span>
                    </div>
                    <div
                        class="flex justify-between text-black dark:text-white font-extrabold border-t border-black/5 dark:border-white/10 pt-1.5 text-sm">
                        <span>Total Tagihan:</span>
                        <span class="text-[#34C759] dark:text-[#30D158] tabular-nums"
                            x-text="formatRupiah(cartTotalAmount)"></span>
                    </div>
                </div>

                <!-- Payment Method Selection Bento -->
                <div class="space-y-1.5 pt-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Pilih Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Option 1: QRIS Pay Now -->
                        <button type="button" @click="paymentMode = 'pay_now'"
                            :class="paymentMode === 'pay_now' ? 'border-[#007AFF] bg-[#007AFF]/10 dark:bg-[#007AFF]/15 text-[#007AFF] ring-1 ring-[#007AFF]' : 'border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70'"
                            class="p-2.5 rounded-[12px] border text-left transition-all flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs">QRIS di Meja</span>
                                <span class="w-2 h-2 rounded-full" :class="paymentMode === 'pay_now' ? 'bg-[#007AFF]' : 'bg-transparent'"></span>
                            </div>
                            <span class="text-[10px] text-[#34C759] font-semibold mt-1">Bebas Biaya Admin</span>
                        </button>

                        <!-- Option 2: Pay Later at Cashier -->
                        <button type="button" @click="paymentMode = 'pay_at_cashier'"
                            :class="paymentMode === 'pay_at_cashier' ? 'border-[#007AFF] bg-[#007AFF]/10 dark:bg-[#007AFF]/15 text-[#007AFF] ring-1 ring-[#007AFF]' : 'border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-black/70 dark:text-white/70'"
                            class="p-2.5 rounded-[12px] border text-left transition-all flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs">Bayar di Kasir</span>
                                <span class="w-2 h-2 rounded-full" :class="paymentMode === 'pay_at_cashier' ? 'bg-[#007AFF]' : 'bg-transparent'"></span>
                            </div>
                            <span class="text-[10px] text-black/45 dark:text-white/45 mt-1">Tunai / Kartu EDC</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Button with Double-Click Protection -->
            <div class="pt-2">
                <button type="button" @click="submitOrderToCashier()" :disabled="isSubmitting"
                    class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] disabled:opacity-50 text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-md">
                    <span x-show="!isSubmitting" x-text="paymentMode === 'pay_now' ? 'Bayar Langsung via QRIS (' + formatRupiah(cartTotalAmount) + ')' : 'Kirim Pesanan ke Kasir'"></span>
                    <span x-show="isSubmitting">Memproses Pesanan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: PAY AT TABLE DYNAMIC QRIS (Apple Bento Sheet)      -->
    <!-- ========================================================= -->
    <div x-show="showQrisModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/70 backdrop-blur-md p-0 sm:p-4"
        style="display: none;">
        <div class="w-full max-w-sm bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-center transition-all"
            @click.outside="closeQrisModal()">
            <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                <div class="text-left">
                    <h3 class="font-bold text-[16px] text-black dark:text-white">QRIS Meja {{ $table->table_number }}</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50">Scan dengan aplikasi e-Wallet atau m-Banking</p>
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
    <!-- MODAL: INPUT CUSTOMER NAME & PHONE (Required)             -->
    <!-- ========================================================= -->
    <div x-show="showCustomerModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-[4px] p-4"
        style="display: none;">
        <div class="w-full max-w-sm bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4"
            @click.outside="if(customerName && customerPhone) showCustomerModal = false;">
            <div class="text-center space-y-1">
                <div
                    class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">Identitas Pemesan</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mohon isi data diri untuk konfirmasi pengantaran ke
                    Meja {{ $table->table_number }}.</p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama Lengkap
                        *</label>
                    <input type="text" x-model="customerName" placeholder="Contoh: Budi Santoso"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nomor WhatsApp /
                        HP *</label>
                    <input type="tel" x-model="customerPhone" placeholder="Contoh: 08123456789"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <div class="pt-2">
                <button type="button" @click="saveCustomerInfo()"
                    class="w-full h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] text-white font-bold text-xs shadow-sm transition">
                    Mulai Pilih Menu
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: LIVE ORDER TRACKING                                -->
    <!-- ========================================================= -->
    <div x-show="showTrackingModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-[4px] p-4"
        style="display: none;">
        <div class="w-full max-w-sm bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-center"
            @click.outside="showTrackingModal = false">
            <div
                class="w-12 h-12 rounded-full bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>

            <div>
                <h3 class="font-bold text-base text-black dark:text-white">Status Pesanan Meja
                    {{ $table->table_number }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Pesanan Anda telah diterima oleh kasir &amp;
                    dapur.</p>
            </div>

            <!-- Recent Orders Feed -->
            <div class="max-h-56 overflow-y-auto space-y-2.5 text-left pr-1">
                <template x-for="ord in recentOrders" :key="ord.id">
                    <div
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 text-xs space-y-1.5">
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
                                    class="h-7 px-3 rounded-[8px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-[11px] font-bold shadow-xs active:scale-95 transition flex items-center gap-1">
                                    <span>Bayar QRIS</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-2 space-y-2">
                <button type="button" @click="showTrackingModal = false"
                    class="w-full h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] text-white font-bold text-xs shadow-sm transition">
                    Pesan Menu Tambahan
                </button>
            </div>
        </div>
    </div>

    <!-- Application Script -->
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

                init() {
                    this.filteredProducts = this.allProducts;
                    if (!this.customerName || !this.customerPhone) {
                        this.showCustomerModal = true;
                    }
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
                            alert('Waktu pembayaran QRIS telah kedaluwarsa. Silakan lakukan pembayaran di kasir.');
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

                filterProducts() {
                    let res = this.allProducts;
                    if (this.selectedCategory !== 'all') {
                        res = res.filter(p => p.category_id === this.selectedCategory);
                    }
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        res = res.filter(p => p.name.toLowerCase().includes(q) || (p.description && p.description
                            .toLowerCase().includes(q)));
                    }
                    this.filteredProducts = res;
                },

                saveCustomerInfo() {
                    if (!this.customerName.trim() || !this.customerPhone.trim()) {
                        alert('Mohon isi nama lengkap dan nomor HP / WhatsApp.');
                        return;
                    }
                    localStorage.setItem('cooca_qr_customer_name', this.customerName.trim());
                    localStorage.setItem('cooca_qr_customer_phone', this.customerPhone.trim());
                    this.showCustomerModal = false;
                },

                openCustomization(product) {
                    if (!product.is_available) return;
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
                            alert('Maksimal hanya boleh memilih ' + maxSelect + ' opsi.');
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
                                alert("Mohon pilih opsi untuk '" + g.name + "'.");
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
                                    const deltaStr = opt.price_delta > 0 ? ' (+Rp ' + Number(opt
                                        .price_delta).toLocaleString('id-ID') + ')' : '';
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
                },

                get cartTotalItems() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get cartTotalAmount() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },

                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                    if (this.cart.length === 0) {
                        this.showCartModal = false;
                    }
                },

                openCartReview() {
                    if (!this.customerName.trim() || !this.customerPhone.trim()) {
                        this.showCustomerModal = true;
                        return;
                    }
                    this.showCartModal = true;
                },

                async submitOrderToCashier() {
                    if (!this.customerName.trim() || !this.customerPhone.trim()) {
                        this.showCartModal = false;
                        this.showCustomerModal = true;
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
                            alert(res.message || 'Gagal mengirim pesanan.');
                            this.isSubmitting = false;
                            return;
                        }

                        // Order success
                        this.cart = [];
                        this.orderGeneralNotes = '';
                        this.showCartModal = false;
                        this.recentOrders.unshift(res.order);

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
                        alert('Terjadi kesalahan jaringan: ' + e.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>

</html>
