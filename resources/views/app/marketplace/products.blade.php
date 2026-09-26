@extends('layouts.app', [
    'title' => 'Pemetaan Produk & Multi-Harga - ' . $business->name,
    'headerTitle' => 'Pemetaan Produk & Multi-Harga',
    'headerSubtitle' => 'Atur perbedaan harga jual dan alokasi stok untuk Shopee, TikTok Shop, dan Tokopedia',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    mappingModal: false,
    syncModal: false,
    selectedProduct: null,
    form: {
        product_id: '',
        channel: 'shopee',
        marketplace_item_id: '',
        marketplace_sku: '',
        channel_price: '',
        sync_price_auto: true,
        price_multiplier: 1.0,
        sync_stock_auto: true,
        stock_buffer: 0,
        custom_stock: '',
        is_active: true
    },
    openMapping(product, channel) {
        this.selectedProduct = product;
        this.form.product_id = product.id;
        this.form.channel = channel;
        
        // Find existing mapping if present
        const existing = (product.marketplace_mappings || []).find(m => m.channel === channel);
        if (existing) {
            this.form.marketplace_item_id = existing.marketplace_item_id || '';
            this.form.marketplace_sku = existing.marketplace_sku || '';
            this.form.channel_price = existing.channel_price || '';
            this.form.sync_price_auto = Boolean(existing.sync_price_auto);
            this.form.price_multiplier = existing.price_multiplier || 1.0;
            this.form.sync_stock_auto = Boolean(existing.sync_stock_auto);
            this.form.stock_buffer = existing.stock_buffer || 0;
            this.form.custom_stock = existing.custom_stock !== null ? existing.custom_stock : '';
            this.form.is_active = Boolean(existing.is_active);
        } else {
            this.form.marketplace_item_id = '';
            this.form.marketplace_sku = product.code || '';
            this.form.channel_price = product.selling_price || '';
            this.form.sync_price_auto = true;
            this.form.price_multiplier = 1.0;
            this.form.sync_stock_auto = true;
            this.form.stock_buffer = 0;
            this.form.custom_stock = '';
            this.form.is_active = true;
        }
        this.mappingModal = true;
    }
}">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
        <a href="{{ route('marketplace-hub.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Marketplace Hub</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
        <span class="text-black/80 dark:text-white/80 font-medium">Pemetaan Produk &amp; Multi-Harga</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                               -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                Pengaturan Multi-Harga &amp; Stok Marketplace
            </h1>
            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Tetapkan harga berbeda untuk tiap channel (Shopee, TikTok Shop, Tokopedia) untuk menutupi biaya admin marketplace atau jalankan sinkronisasi stok otomatis dari gudang utama.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <form method="POST" action="{{ route('marketplace-hub.products.sync-all') }}" onsubmit="return confirm('Mulai sinkronisasi seluruh harga & stok ke marketplace?')">
                @csrf
                <button type="submit"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Sinkronisasi Massal Semua</span>
                </button>
            </form>
            <a href="{{ route('marketplace-hub.index') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Hub</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. SEARCH & FILTER BAR                                -->
    <!-- ===================================================== -->
    <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <form method="GET" action="{{ route('marketplace-hub.products') }}" class="flex-1 w-full flex items-center gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Cari berdasarkan nama produk atau barcode/SKU..."
                    class="w-full h-10 pl-9 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
            </div>
            <button type="submit"
                class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-black dark:text-white bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] cursor-pointer">
                Cari
            </button>
            @if(request()->filled('q'))
                <a href="{{ route('marketplace-hub.products') }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center">
                    Reset
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 self-start sm:self-auto text-[12px] text-black/60 dark:text-white/60">
            <span class="font-medium">Total: {{ $products->total() }} Produk</span>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO PRODUCTS & MULTI-PRICING TABLE               -->
    <!-- ===================================================== -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/60 dark:text-white/60 font-semibold text-[11.5px] uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Produk Utama (COOCA)</th>
                        <th class="py-3.5 px-4 text-center">Harga COOCA</th>
                        <th class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 text-[#EE4D2D]">
                                <span class="w-2 h-2 rounded-full bg-[#EE4D2D]"></span>
                                <span>Shopee</span>
                            </span>
                        </th>
                        <th class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 text-black dark:text-white">
                                <span class="w-2 h-2 rounded-full bg-black dark:bg-white"></span>
                                <span>TikTok Shop</span>
                            </span>
                        </th>
                        <th class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 text-[#00AA5B]">
                                <span class="w-2 h-2 rounded-full bg-[#00AA5B]"></span>
                                <span>Tokopedia</span>
                            </span>
                        </th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi Sinkronisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($products as $product)
                        @php
                            $shopeeMapping = $product->marketplaceMappings->firstWhere('channel', 'shopee');
                            $tiktokMapping = $product->marketplaceMappings->firstWhere('channel', 'tiktok_shop');
                            $tokpedMapping = $product->marketplaceMappings->firstWhere('channel', 'tokopedia');
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <!-- Column 1: Product Name & Code -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">{{ $product->name }}</div>
                                <div class="flex items-center gap-2 text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">
                                    <span class="font-mono">{{ $product->code ?? 'NO-SKU' }}</span>
                                    <span>•</span>
                                    <span>Stok Fisik: <strong class="text-black dark:text-white">{{ $product->stock ?? 0 }}</strong> {{ $product->unit ?? 'pcs' }}</span>
                                </div>
                            </td>

                            <!-- Column 2: COOCA Base Price -->
                            <td class="py-4 px-4 text-center">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">
                                    Rp {{ number_format((float)$product->selling_price, 0, ',', '.') }}
                                </div>
                                <span class="text-[10.5px] font-medium text-black/45 dark:text-white/45">Toko Utama</span>
                            </td>

                            <!-- Column 3: Shopee Price & Config -->
                            <td class="py-4 px-4 text-center">
                                @if($shopeeMapping && $shopeeMapping->is_active)
                                    <div class="font-bold text-[#EE4D2D] text-[13px]">
                                        Rp {{ number_format((float)$shopeeMapping->getEffectivePrice(), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50">
                                        {{ $shopeeMapping->sync_price_auto ? 'Otomatis' : 'Harga Khusus' }}
                                    </div>
                                    <button type="button" @click="openMapping({{ json_encode($product) }}, 'shopee')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping({{ json_encode($product) }}, 'shopee')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Petakan</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Column 4: TikTok Shop Price & Config -->
                            <td class="py-4 px-4 text-center">
                                @if($tiktokMapping && $tiktokMapping->is_active)
                                    <div class="font-bold text-black dark:text-white text-[13px]">
                                        Rp {{ number_format((float)$tiktokMapping->getEffectivePrice(), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50">
                                        {{ $tiktokMapping->sync_price_auto ? 'Otomatis' : 'Harga Khusus' }}
                                    </div>
                                    <button type="button" @click="openMapping({{ json_encode($product) }}, 'tiktok_shop')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping({{ json_encode($product) }}, 'tiktok_shop')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Petakan</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Column 5: Tokopedia Price & Config -->
                            <td class="py-4 px-4 text-center">
                                @if($tokpedMapping && $tokpedMapping->is_active)
                                    <div class="font-bold text-[#00AA5B] text-[13px]">
                                        Rp {{ number_format((float)$tokpedMapping->getEffectivePrice(), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50">
                                        {{ $tokpedMapping->sync_price_auto ? 'Otomatis' : 'Harga Khusus' }}
                                    </div>
                                    <button type="button" @click="openMapping({{ json_encode($product) }}, 'tokopedia')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping({{ json_encode($product) }}, 'tokopedia')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Petakan</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Column 6: Sync Actions -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" action="{{ route('marketplace-hub.products.sync-price', $product->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="h-8 px-2.5 rounded-[9px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center gap-1 cursor-pointer" title="Push Harga ke Seluruh Marketplace">
                                            <i data-lucide="dollar-sign" class="w-3 h-3"></i>
                                            <span>Sync Harga</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('marketplace-hub.products.sync-stock', $product->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="h-8 px-2.5 rounded-[9px] text-[11.5px] font-semibold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 transition-all flex items-center gap-1 cursor-pointer" title="Push Stok ke Seluruh Marketplace">
                                            <i data-lucide="box" class="w-3 h-3"></i>
                                            <span>Sync Stok</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-black/50 dark:text-white/50">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6 text-black/40 dark:text-white/40"></i>
                                </div>
                                <div class="font-bold text-[14px]">Belum Ada Data Produk</div>
                                <p class="text-[12px] mt-0.5">Tambahkan produk di menu Produk &amp; Jasa terlebih dahulu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL PEMETAAN & ATUR HARGA PER CHANNEL            -->
    <!-- ===================================================== -->
    <div x-show="mappingModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="mappingModal = false"
            class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-6 shadow-2xl max-h-[90vh] overflow-y-auto">

            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="tag" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Atur Multi-Harga &amp; Stok Channel</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedProduct ? selectedProduct.name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="mappingModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('marketplace-hub.products.map') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="product_id" :value="form.product_id">

                <!-- Channel Selector Tabs -->
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Pilih Saluran Marketplace</label>
                    <div class="grid grid-cols-3 gap-2 p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <button type="button" @click="form.channel = 'shopee'"
                            :class="form.channel === 'shopee' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs font-bold text-[#EE4D2D]' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-2 text-[12px] rounded-[10px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Shopee</span>
                        </button>
                        <button type="button" @click="form.channel = 'tiktok_shop'"
                            :class="form.channel === 'tiktok_shop' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs font-bold text-black dark:text-white' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-2 text-[12px] rounded-[10px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>TikTok Shop</span>
                        </button>
                        <button type="button" @click="form.channel = 'tokopedia'"
                            :class="form.channel === 'tokopedia' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs font-bold text-[#00AA5B]' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-2 text-[12px] rounded-[10px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Tokopedia</span>
                        </button>
                    </div>
                    <input type="hidden" name="channel" :value="form.channel">
                </div>

                <!-- External Item ID / SKU in Marketplace -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Marketplace Item ID (Opsional)
                        </label>
                        <input type="text" name="marketplace_item_id" x-model="form.marketplace_item_id"
                            placeholder="Contoh: 1982739281"
                            class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Marketplace SKU
                        </label>
                        <input type="text" name="marketplace_sku" x-model="form.marketplace_sku"
                            placeholder="Contoh: SKU-SHP-001"
                            class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono">
                    </div>
                </div>

                <!-- Pricing Section: Auto vs Manual -->
                <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[13px] font-bold text-black dark:text-white">Skema Penetapan Harga</span>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Otomatis ikuti harga COOCA atau atur manual</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11.5px] font-semibold" :class="form.sync_price_auto ? 'text-[#007AFF]' : 'text-black/50'">
                                <span x-text="form.sync_price_auto ? 'Otomatis' : 'Manual'"></span>
                            </span>
                            <button type="button" @click="form.sync_price_auto = !form.sync_price_auto"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="form.sync_price_auto ? 'bg-[#007AFF]' : 'bg-black/20 dark:bg-white/20'">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="form.sync_price_auto ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="sync_price_auto" :value="form.sync_price_auto ? '1' : '0'">

                    <!-- If Auto: Multiplier -->
                    <div x-show="form.sync_price_auto" class="pt-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Faktor Pengali Harga (Price Multiplier)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" step="0.01" min="0.5" max="3.0" name="price_multiplier" x-model="form.price_multiplier"
                                class="w-32 h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono">
                            <div class="text-[12px] text-black/60 dark:text-white/60">
                                Contoh: <code>1.05</code> (+5% untuk biaya admin marketplace)
                            </div>
                        </div>
                    </div>

                    <!-- If Manual: Custom Price -->
                    <div x-show="!form.sync_price_auto" class="pt-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Harga Khusus Channel (Rp)
                        </label>
                        <input type="number" step="100" min="0" name="channel_price" x-model="form.channel_price"
                            placeholder="Contoh: 125000"
                            class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono font-bold">
                    </div>
                </div>

                <!-- Stock Section: Auto vs Manual & Buffer -->
                <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[13px] font-bold text-black dark:text-white">Skema Alokasi Stok</span>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Sinkronisasi stok fisik otomatis atau alokasi kuota khusus</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11.5px] font-semibold" :class="form.sync_stock_auto ? 'text-[#34C759]' : 'text-black/50'">
                                <span x-text="form.sync_stock_auto ? 'Otomatis' : 'Manual'"></span>
                            </span>
                            <button type="button" @click="form.sync_stock_auto = !form.sync_stock_auto"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="form.sync_stock_auto ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="form.sync_stock_auto ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="sync_stock_auto" :value="form.sync_stock_auto ? '1' : '0'">

                    <!-- If Auto: Stock Buffer -->
                    <div x-show="form.sync_stock_auto" class="pt-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Stok Pengaman (Safety Buffer Stock)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" min="0" name="stock_buffer" x-model="form.stock_buffer"
                                class="w-32 h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono">
                            <div class="text-[12px] text-black/60 dark:text-white/60">
                                Sisa unit yang disisihkan (tidak dipublish ke marketplace)
                            </div>
                        </div>
                    </div>

                    <!-- If Manual: Custom Stock -->
                    <div x-show="!form.sync_stock_auto" class="pt-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                            Alokasi Kuota Stok Manual
                        </label>
                        <input type="number" min="0" name="custom_stock" x-model="form.custom_stock"
                            placeholder="Contoh: 50"
                            class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono font-bold">
                    </div>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center justify-between pt-1">
                    <span class="text-[12.5px] font-bold text-black dark:text-white">Aktifkan Pemetaan Channel Ini</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="mappingModal = false"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-sm cursor-pointer">
                        Simpan Pengaturan Channel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
