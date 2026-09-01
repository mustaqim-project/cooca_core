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
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased select-none"
      x-data="posApp()"
      x-init="initPos()">

    <div class="h-screen flex flex-col overflow-hidden">
        
        <!-- POS Top Navbar -->
        <header class="h-16 glass-panel flex items-center justify-between px-4 sm:px-6 shrink-0 z-20">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 transition" title="Kembali ke Dashboard">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-base text-white tracking-tight leading-none">{{ $business->name }}</div>
                        <div class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider mt-0.5">Terminal Kasir POS</div>
                    </div>
                </div>

                <!-- Location Selector -->
                <div class="hidden md:flex items-center gap-2 ml-4 px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-800 text-xs">
                    <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400"></i>
                    <select x-model="selectedLocationId" @change="changeLocation()" class="bg-transparent text-slate-200 font-medium focus:outline-none cursor-pointer">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" class="bg-slate-900 text-slate-200" {{ $loc->id === $selectedLocationId ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ strtoupper($loc->type ?? 'Outlet') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Top Actions: Shift Status, Hold Carts, Time, Cashier -->
            <div class="flex items-center gap-3">
                <!-- Shift Indicator -->
                <template x-if="activeShift">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Shift Terbuka</span>
                        <button @click="openShiftCloseModal()" class="ml-1 px-1.5 py-0.5 rounded bg-emerald-500/20 hover:bg-emerald-500/30 text-[10px] font-bold text-white transition">Tutup Shift</button>
                    </div>
                </template>
                <template x-if="!activeShift">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Shift Belum Dibuka</span>
                        <button @click="showOpenShiftModal = true" class="ml-1 px-1.5 py-0.5 rounded bg-amber-500/20 hover:bg-amber-500/30 text-[10px] font-bold text-white transition">Buka Shift</button>
                    </div>
                </template>

                <!-- Held Carts Button -->
                <button @click="showHeldOrdersModal = true" 
                        class="relative flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 text-xs font-semibold transition">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                    <span>Antrean Hold</span>
                    <span x-show="heldOrders.length > 0" x-text="heldOrders.length" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold text-[10px] flex items-center justify-center"></span>
                </button>

                <!-- Cash In / Out Button -->
                <button @click="showCashMovementModal = true" 
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 text-xs font-semibold transition">
                    <i data-lucide="banknote" class="w-4 h-4 text-teal-400"></i>
                    <span>Kas Masuk/Keluar</span>
                </button>

                <!-- Cashier Info -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-800 text-xs">
                    <div class="w-7 h-7 rounded-lg bg-emerald-600/20 text-emerald-400 flex items-center justify-center font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="hidden lg:block text-left">
                        <div class="font-bold text-white leading-tight">{{ $user->name }}</div>
                        <div class="text-[10px] text-slate-400">Kasir</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- POS Main Work Area -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- Left Area: Catalog & Barcode Scanner -->
            <div class="flex-1 flex flex-col overflow-hidden p-4 gap-4">
                
                <!-- Search & Category Bar -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search Input with Barcode Icon -->
                    <div class="relative flex-1 min-w-[240px]">
                        <i data-lucide="barcode" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-400"></i>
                        <input type="text"
                               x-ref="barcodeSearchInput"
                               x-model="searchQuery" 
                               @keydown.enter="handleBarcodeOrSearch()"
                               placeholder="Cari nama produk atau Scan Barcode (Tekan Enter)..." 
                               class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-11 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50">
                        <button x-show="searchQuery" @click="searchQuery = ''; filterProducts()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto py-1 max-w-full">
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
                <div class="flex-1 overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="addToCart(product)"
                                 class="glass-card rounded-2xl p-3.5 cursor-pointer hover:border-emerald-500/40 hover:bg-slate-800/80 transition-all flex flex-col justify-between group active:scale-95">
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
                                    <div class="text-[11px] text-slate-400 font-mono mt-1" x-text="product.code || '-'"></div>
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
            <div class="w-96 sm:w-[420px] glass-panel border-l border-slate-800 flex flex-col shrink-0 z-10">
                
                <!-- Cart Header: Customer & Order Type -->
                <div class="p-4 border-b border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="font-extrabold text-base text-white flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-400"></i>
                            <span>Keranjang Pesanan</span>
                        </div>
                        <button @click="clearCart()" x-show="cart.length > 0" class="text-xs text-rose-400 hover:text-rose-300 font-medium transition">
                            Kosongkan
                        </button>
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
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
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
                                    <button @click="decrementQty(index)" class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-xs">-</button>
                                    <span class="w-8 text-center font-mono font-bold text-xs text-white" x-text="item.quantity"></span>
                                    <button @click="incrementQty(index)" class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-xs">+</button>
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
                <div class="p-4 border-t border-slate-800 bg-slate-950/90 space-y-3">
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-mono font-medium text-slate-200" x-text="formatRupiah(subtotal)"></span>
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

    <!-- MODAL 1: Payment Modal (Split Payment Ready!) -->
    <div x-show="showPaymentModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" 
         style="display: none;">
        <div class="w-full max-w-2xl glass-panel rounded-2xl border border-slate-700 p-6 flex flex-col max-h-[90vh] overflow-y-auto">
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
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
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
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
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
                    <div x-show="selectedPayMethod === 'cash'" class="grid grid-cols-4 gap-2 mt-2">
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
        <div class="w-full max-w-md glass-panel rounded-2xl border border-emerald-500/40 p-6 text-center space-y-4">
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
                <a :href="lastReceiptUrl" target="_blank" class="w-full py-3 rounded-xl bg-slate-900 border border-slate-700 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-2 transition">
                    <i data-lucide="printer" class="w-4 h-4 text-emerald-400"></i>
                    <span>Cetak Struk Thermal (58mm/80mm)</span>
                </a>
                <a :href="lastWhatsAppUrl" target="_blank" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-lg shadow-emerald-600/20">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Struk via WhatsApp</span>
                </a>
                <button @click="resetForNewOrder()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Open Shift Modal -->
    <div x-show="showOpenShiftModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
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
        <div class="w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
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
        <div class="w-full max-w-lg glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
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
        <div class="w-full max-w-md glass-panel rounded-2xl border border-slate-700 p-6 space-y-4">
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
                redeemPoints: false,
                pointsDiscount: 0,

                // Modals
                showPaymentModal: false,
                showSuccessModal: false,
                showOpenShiftModal: false,
                showCloseShiftModal: false,
                showHeldOrdersModal: false,
                showCashMovementModal: false,

                // Payment state
                selectedPayMethod: 'cash',
                currentTenderAmount: 0,
                isProcessing: false,
                lastCompletedOrder: null,
                lastReceiptUrl: '#',
                lastWhatsAppUrl: '#',

                // Shift state
                shiftOpeningCash: 100000,
                shiftNotes: '',
                shiftActualCash: 0,
                shiftSummary: { opening_cash: 0, cash_sales: 0, cash_in: 0, cash_out: 0, expected_cash: 0 },

                // Cash movement
                cashMovementType: 'cash_in',
                cashMovementAmount: 50000,
                cashMovementReason: '',

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
                        this.voucherDiscount = 0;
                        this.pointsDiscount = 0;
                        this.redeemPoints = false;
                    }
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },

                get taxAmount() {
                    @if($business->pos_enable_tax)
                        return (this.subtotal - this.voucherDiscount - this.pointsDiscount) * ({{ (float) $business->pos_tax_percent }} / 100);
                    @else
                        return 0;
                    @endif
                },

                get serviceChargeAmount() {
                    @if($business->pos_enable_service_charge)
                        return (this.subtotal - this.voucherDiscount - this.pointsDiscount) * ({{ (float) $business->pos_service_charge_percent }} / 100);
                    @else
                        return 0;
                    @endif
                },

                get grandTotal() {
                    const raw = this.subtotal - this.voucherDiscount - this.pointsDiscount + this.taxAmount + this.serviceChargeAmount;
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
                    this.showPaymentModal = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                async submitCheckout() {
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
                            this.showPaymentModal = false;
                            this.showSuccessModal = true;
                            this.cart = [];
                            this.voucherCode = '';
                            this.voucherDiscount = 0;
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
