<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Meja {{ $table->table_number }} — {{ $business->name }}</title>

    <!-- Google Fonts (Inter as Apple SF Pro fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 4px; }
    </style>
</head>
<body class="bg-[#F2F2F7] text-black min-h-full flex flex-col antialiased select-none pb-24" x-data="qrOrderApp()">

    <!-- Top Sticky Header (Apple Navigation Bar) -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-black/10 transition-all">
        <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                @if($business->logo_url)
                    <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="w-8 h-8 rounded-full object-contain border border-black/5 p-0.5">
                @else
                    <div class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ substr($business->name, 0, 2) }}
                    </div>
                @endif
                <div>
                    <h1 class="font-bold text-[15px] tracking-tight text-black truncate max-w-[180px] leading-tight">{{ $business->name }}</h1>
                    <div class="text-[11px] font-medium text-black/50 flex items-center gap-1">
                        <span>Pesan dari Meja</span>
                        <span class="w-1 h-1 rounded-full bg-black/30"></span>
                        <span class="text-[#007AFF] font-bold">{{ $table->table_number }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Identity Pill -->
            <button type="button" @click="showCustomerModal = true" class="h-8 px-3 rounded-full bg-black/[0.04] hover:bg-black/[0.08] active:scale-95 border border-black/5 text-xs font-semibold flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5 text-black/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <span x-text="customerName ? customerName : 'Nama & HP'"></span>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="max-w-md mx-auto px-4 pb-2.5">
            <div class="relative">
                <input type="text" x-model="searchQuery" @input="filterProducts()" placeholder="Cari makanan atau minuman..." class="w-full h-9 pl-9 pr-4 rounded-[10px] bg-black/[0.05] border border-transparent focus:border-black/15 focus:bg-white text-xs text-black placeholder:text-black/40 focus:outline-none transition">
                <svg class="w-4 h-4 text-black/40 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </div>
        </div>

        <!-- Category Pills (Horizontal Scroll) -->
        <div class="max-w-md mx-auto px-4 pb-2.5 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button type="button" @click="selectCategory('all')" :class="selectedCategory === 'all' ? 'bg-black text-white' : 'bg-black/[0.04] text-black/70 hover:bg-black/[0.08]'" class="h-7 px-3 rounded-full text-xs font-semibold whitespace-nowrap transition">
                Semua Menu
            </button>
            @foreach($categories as $cat)
            <button type="button" @click="selectCategory('{{ $cat->id }}')" :class="selectedCategory === '{{ $cat->id }}' ? 'bg-black text-white' : 'bg-black/[0.04] text-black/70 hover:bg-black/[0.08]'" class="h-7 px-3 rounded-full text-xs font-semibold whitespace-nowrap transition">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-md mx-auto px-4 pt-3 flex-1 w-full space-y-4">

        <!-- Active Orders Alert Banner (if user already submitted orders in this session) -->
        <template x-if="recentOrders.length > 0">
            <div class="p-3 rounded-[14px] bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-[#007AFF] uppercase tracking-wider">Status Meja Anda</div>
                    <div class="text-xs font-semibold text-black mt-0.5" x-text="recentOrders.length + ' Pesanan sedang diproses' "></div>
                </div>
                <button type="button" @click="showTrackingModal = true" class="h-7 px-2.5 rounded-[8px] bg-[#007AFF] text-white text-[11px] font-bold transition shadow-sm">
                    Lacak Status
                </button>
            </div>
        </template>

        <!-- Product List Cards -->
        <div class="space-y-3">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="openCustomization(product)" class="p-3.5 rounded-[16px] bg-white border border-black/10 shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex gap-3.5 cursor-pointer active:scale-[0.99] transition hover:border-[#007AFF]/40">
                    <!-- Thumbnail -->
                    <div class="w-20 h-20 rounded-[12px] bg-black/[0.04] overflow-hidden flex-shrink-0 flex items-center justify-center relative">
                        <template x-if="product.image_url">
                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!product.image_url">
                            <svg class="w-8 h-8 text-black/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        </template>
                        <!-- Sold out badge -->
                        <div x-show="!product.is_available" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] flex items-center justify-center text-white text-[10px] font-bold tracking-wider uppercase">
                            Habis
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 flex flex-col justify-between min-w-0">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <h3 class="font-bold text-[14px] text-black truncate" x-text="product.name"></h3>
                            </div>
                            <p class="text-[11px] text-black/50 line-clamp-2 mt-0.5 leading-relaxed" x-text="product.description || 'Pilihan lezat favorit pelanggan.'"></p>
                        </div>

                        <div class="flex items-center justify-between mt-2 pt-1 border-t border-black/5">
                            <div class="font-extrabold text-[13px] text-black tabular-nums" x-text="formatRupiah(product.selling_price)"></div>

                            <button type="button" :disabled="!product.is_available" class="h-7 px-3 rounded-full bg-[#007AFF] disabled:bg-black/10 text-white disabled:text-black/30 text-xs font-semibold flex items-center gap-1 shadow-sm transition">
                                <span x-show="product.is_available">Tambah</span>
                                <span x-show="!product.is_available">Habis</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty Search State -->
            <div x-show="filteredProducts.length === 0" class="py-12 text-center text-xs text-black/40 space-y-1">
                <div>Tidak ada menu yang sesuai dengan pencarian Anda.</div>
            </div>
        </div>
    </main>

    <!-- ========================================================= -->
    <!-- STICKY FLOATING CART BAR                                  -->
    <!-- ========================================================= -->
    <div x-show="cartTotalItems > 0" class="fixed bottom-4 inset-x-0 z-40 px-4 max-w-md mx-auto" style="display: none;">
        <div @click="openCartReview()" class="p-3.5 rounded-[20px] bg-black text-white shadow-[0_12px_30px_rgba(0,0,0,0.3)] flex items-center justify-between cursor-pointer active:scale-[0.98] transition">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-white/20 text-white flex items-center justify-center font-bold text-xs tabular-nums" x-text="cartTotalItems"></div>
                <div>
                    <div class="text-[10px] text-white/60 uppercase tracking-wider font-semibold">Keranjang Pesanan</div>
                    <div class="font-bold text-sm tabular-nums" x-text="formatRupiah(cartTotalAmount)"></div>
                </div>
            </div>

            <div class="flex items-center gap-1 text-xs font-bold text-[#007AFF] bg-white rounded-full px-3.5 py-1.5 shadow-sm">
                <span>Lihat Pesanan</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: PRODUCT CUSTOMIZATION & MODIFIERS (Apple Sheet)    -->
    <!-- ========================================================= -->
    <div x-show="showCustomizationModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-[3px] p-0 sm:p-4" style="display: none;">
        <div class="w-full max-w-md bg-white rounded-t-[28px] sm:rounded-[24px] border border-black/10 p-5 shadow-2xl space-y-4 max-h-[85vh] flex flex-col" @click.outside="showCustomizationModal = false">
            <!-- Sheet Header -->
            <div class="flex items-start justify-between pb-2 border-b border-black/10">
                <div>
                    <h3 class="font-bold text-[17px] text-black" x-text="activeProduct ? activeProduct.name : ''"></h3>
                    <div class="text-xs font-extrabold text-[#007AFF] tabular-nums mt-0.5" x-text="formatRupiah(activeProduct ? activeProduct.selling_price : 0)"></div>
                </div>
                <button type="button" @click="showCustomizationModal = false" class="w-8 h-8 rounded-full bg-black/5 flex items-center justify-center text-black/50 hover:text-black">✕</button>
            </div>

            <!-- Scrollable Modifier Groups -->
            <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                <template x-for="group in (activeProduct ? activeProduct.modifier_groups : [])" :key="group.id">
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] border border-black/5 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-xs text-black flex items-center gap-1.5">
                                <span x-text="group.name"></span>
                                <span x-show="group.is_required" class="text-[#FF3B30] font-black text-xs">*</span>
                            </div>
                            <span class="text-[10px] text-black/40 font-semibold" x-text="group.selection_type === 'single' ? 'Pilih 1' : 'Pilihan bebas'"></span>
                        </div>

                        <!-- Options List -->
                        <div class="space-y-1.5">
                            <template x-for="opt in group.options" :key="opt.id">
                                <label :class="!opt.is_available ? 'opacity-40 pointer-events-none' : 'cursor-pointer hover:bg-black/5'" class="p-2 rounded-[10px] border border-black/5 flex items-center justify-between transition">
                                    <div class="flex items-center gap-2.5">
                                        <!-- Single Choice: Radio -->
                                        <template x-if="group.selection_type === 'single'">
                                            <input type="radio" :name="'mod_group_' + group.id" :value="opt.id" @change="selectSingleModifier(group.id, opt.id)" :checked="selectedModifiers[group.id] && selectedModifiers[group.id].includes(opt.id)" class="text-[#007AFF] focus:ring-[#007AFF]/50">
                                        </template>
                                        <!-- Multiple Choice: Checkbox -->
                                        <template x-if="group.selection_type === 'multiple'">
                                            <input type="checkbox" :value="opt.id" @change="toggleMultipleModifier(group.id, opt.id, group.max_selection)" :checked="selectedModifiers[group.id] && selectedModifiers[group.id].includes(opt.id)" class="rounded text-[#007AFF] focus:ring-[#007AFF]/50">
                                        </template>
                                        <span class="text-xs font-semibold text-black" x-text="opt.name"></span>
                                    </div>

                                    <div class="text-xs font-bold tabular-nums">
                                        <span x-show="opt.price_delta > 0" class="text-[#34C759]" x-text="'+ ' + formatRupiah(opt.price_delta)"></span>
                                        <span x-show="opt.price_delta <= 0" class="text-black/40">+Rp 0</span>
                                        <span x-show="!opt.is_available" class="ml-1.5 text-[10px] text-[#FF3B30] uppercase">Habis</span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Item Note Input -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-black/70">Catatan Khusus Menu Ini (Opsional)</label>
                    <input type="text" x-model="customItemNote" placeholder="Contoh: Less ice, gula pisah, pedas sedang..." class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] border border-black/10 text-xs text-black placeholder:text-black/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>

            <!-- Stepper and Submit -->
            <div class="pt-2 border-t border-black/10 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 bg-black/[0.05] rounded-full p-1 border border-black/5">
                    <button type="button" @click="customQty = Math.max(1, customQty - 1)" class="w-8 h-8 rounded-full bg-white text-black font-bold flex items-center justify-center shadow-sm active:scale-95 transition">-</button>
                    <span class="w-6 text-center text-xs font-bold tabular-nums" x-text="customQty"></span>
                    <button type="button" @click="customQty++" class="w-8 h-8 rounded-full bg-white text-black font-bold flex items-center justify-center shadow-sm active:scale-95 transition">+</button>
                </div>

                <button type="button" @click="commitCustomizationToCart()" class="flex-1 h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold flex items-center justify-between shadow-sm transition">
                    <span>Tambahkan Pesanan</span>
                    <span class="tabular-nums" x-text="formatRupiah(calculatedItemTotal)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: CART REVIEW & SUBMIT SHEET (Apple Sheet)           -->
    <!-- ========================================================= -->
    <div x-show="showCartModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-[3px] p-0 sm:p-4" style="display: none;">
        <div class="w-full max-w-md bg-white rounded-t-[28px] sm:rounded-[24px] border border-black/10 p-5 shadow-2xl space-y-4 max-h-[90vh] flex flex-col" @click.outside="showCartModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-black/10">
                <div>
                    <h3 class="font-bold text-[17px] text-black">Konfirmasi Pesanan</h3>
                    <div class="text-xs text-black/50" x-text="'Meja ' + '{{ $table->table_number }}' + ' • ' + (customerName || 'Tamu')"></div>
                </div>
                <button type="button" @click="showCartModal = false" class="w-8 h-8 rounded-full bg-black/5 flex items-center justify-center text-black/50 hover:text-black">✕</button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 divide-y divide-black/5">
                <template x-for="(item, idx) in cart" :key="idx">
                    <div class="pt-2.5 first:pt-0 flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <div class="font-bold text-xs text-black" x-text="item.product_name"></div>
                            <div class="text-[11px] text-[#007AFF] font-medium" x-show="item.modifiers_text" x-text="item.modifiers_text"></div>
                            <div class="text-[11px] text-black/50 italic" x-show="item.notes" x-text="'Catatan: ' + item.notes"></div>
                            <div class="text-xs text-black/60 mt-1 tabular-nums" x-text="item.quantity + ' × ' + formatRupiah(item.unit_price) + ' = ' + formatRupiah(item.quantity * item.unit_price)"></div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="removeFromCart(idx)" class="w-7 h-7 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center text-xs font-bold">
                                ✕
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Order Notes & Customer Info Confirmation -->
            <div class="space-y-2.5 pt-2 border-t border-black/10">
                <div>
                    <label class="block text-[11px] font-semibold text-black/60 mb-1">Catatan Keseluruhan Meja</label>
                    <input type="text" x-model="orderGeneralNotes" placeholder="Contoh: Tolong diantar bersamaan..." class="w-full h-9 px-3 rounded-[8px] bg-black/[0.04] border border-black/10 text-xs text-black placeholder:text-black/35 focus:outline-none">
                </div>

                <div class="p-3 rounded-[12px] bg-black/[0.03] border border-black/5 space-y-1.5 text-xs">
                    <div class="flex justify-between text-black/60">
                        <span>Pemesan:</span>
                        <span class="font-semibold text-black" x-text="customerName + ' (' + customerPhone + ')'"></span>
                    </div>
                    <div class="flex justify-between text-black/60">
                        <span>Nomor Meja:</span>
                        <span class="font-bold text-black">{{ $table->table_number }}</span>
                    </div>
                    <div class="flex justify-between text-black font-extrabold border-t border-black/5 pt-1 text-sm">
                        <span>Total Tagihan:</span>
                        <span class="text-[#34C759] tabular-nums" x-text="formatRupiah(cartTotalAmount)"></span>
                    </div>
                </div>
            </div>

            <!-- Submit Button with Double-Click Protection -->
            <div class="pt-2">
                <button type="button" @click="submitOrderToCashier()" :disabled="isSubmitting" class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-50 text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-md">
                    <span x-show="!isSubmitting">Kirim Pesanan ke Kasir</span>
                    <span x-show="isSubmitting">Mengirim Pesanan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: INPUT CUSTOMER NAME & PHONE (Required)             -->
    <!-- ========================================================= -->
    <div x-show="showCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[3px] p-4" style="display: none;">
        <div class="w-full max-w-sm bg-white rounded-[24px] border border-black/10 p-6 shadow-2xl space-y-4" @click.outside="if(customerName && customerPhone) showCustomerModal = false;">
            <div class="text-center space-y-1">
                <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </div>
                <h3 class="font-bold text-base text-black">Identitas Pemesan</h3>
                <p class="text-xs text-black/50">Mohon isi data diri untuk konfirmasi pengantaran ke Meja {{ $table->table_number }}.</p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-black/70 mb-1">Nama Lengkap *</label>
                    <input type="text" x-model="customerName" placeholder="Contoh: Budi Santoso" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] border border-black/10 text-xs text-black focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 mb-1">Nomor WhatsApp / HP *</label>
                    <input type="tel" x-model="customerPhone" placeholder="Contoh: 08123456789" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] border border-black/10 text-xs font-medium tabular-nums text-black focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>

            <div class="pt-2">
                <button type="button" @click="saveCustomerInfo()" class="w-full h-10 rounded-[10px] bg-[#007AFF] text-white font-bold text-xs shadow-sm">
                    Mulai Pilih Menu
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: LIVE ORDER TRACKING                                -->
    <!-- ========================================================= -->
    <div x-show="showTrackingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[3px] p-4" style="display: none;">
        <div class="w-full max-w-sm bg-white rounded-[24px] border border-black/10 p-6 shadow-2xl space-y-4 text-center" @click.outside="showTrackingModal = false">
            <div class="w-12 h-12 rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </div>

            <div>
                <h3 class="font-bold text-base text-black">Status Pesanan Meja {{ $table->table_number }}</h3>
                <p class="text-xs text-black/50 mt-0.5">Pesanan Anda telah diterima oleh kasir &amp; dapur.</p>
            </div>

            <!-- Recent Orders Feed -->
            <div class="max-h-48 overflow-y-auto space-y-2 text-left pr-1">
                <template x-for="ord in recentOrders" :key="ord.id">
                    <div class="p-3 rounded-[12px] bg-black/[0.03] border border-black/5 text-xs space-y-1">
                        <div class="flex justify-between font-bold text-black">
                            <span x-text="'#' + ord.order_number"></span>
                            <span class="text-[#007AFF] uppercase text-[10px]" x-text="ord.status"></span>
                        </div>
                        <div class="text-[11px] text-black/60" x-text="ord.items ? ord.items.map(i => i.product_name + ' (' + i.quantity + ')').join(', ') : ''"></div>
                        <div class="text-xs font-extrabold text-[#34C759] tabular-nums" x-text="formatRupiah(ord.total_amount)"></div>
                    </div>
                </template>
            </div>

            <div class="pt-2 space-y-2">
                <button type="button" @click="showTrackingModal = false" class="w-full h-10 rounded-[10px] bg-[#007AFF] text-white font-bold text-xs shadow-sm">
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

                // Customization modal state
                showCustomizationModal: false,
                activeProduct: null,
                selectedModifiers: {}, // { [groupId]: [optionId, ...] }
                customItemNote: '',
                customQty: 1,

                // Cart state
                cart: [],
                orderGeneralNotes: '',
                showCartModal: false,
                showTrackingModal: false,
                isSubmitting: false,

                init() {
                    this.filteredProducts = this.allProducts;
                    if (!this.customerName || !this.customerPhone) {
                        this.showCustomerModal = true;
                    }
                },

                formatRupiah(val) {
                    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
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
                        res = res.filter(p => p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q)));
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
                        this.showTrackingModal = true;
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
