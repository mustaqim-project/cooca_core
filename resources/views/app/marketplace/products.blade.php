@extends('layouts.app', [
    'title' => 'Pemetaan Produk & Multi-Harga - ' . $business->name,
    'headerTitle' => 'Pemetaan Produk & Multi-Harga',
    'headerSubtitle' => 'Atur perbedaan harga jual dan alokasi stok untuk Shopee, TikTok Shop, dan Tokopedia',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    mappingModal: false,
    syncModal: false,
    submitting: false,
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
        is_active: true,
        allow_below_cost: false
    },
    openMapping(product, channel) {
        this.selectedProduct = product;
        this.form.product_id = product.id;
        this.form.allow_below_cost = false;
        this.switchChannel(channel || 'shopee');
        this.submitting = false;
        this.mappingModal = true;
    },
    switchChannel(channel) {
        this.form.channel = channel;
        this.form.allow_below_cost = false;
        if (!this.selectedProduct) return;
        const existing = (this.selectedProduct.marketplace_mappings || []).find(m => m.channel === channel);
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
            this.form.marketplace_sku = this.selectedProduct.code || '';
            this.form.channel_price = this.selectedProduct.selling_price || '';
            this.form.sync_price_auto = true;
            this.form.price_multiplier = 1.0;
            this.form.sync_stock_auto = true;
            this.form.stock_buffer = 0;
            this.form.custom_stock = '';
            this.form.is_active = true;
        }
    },
    get computedEffectivePrice() {
        if (!this.selectedProduct) return 0;
        if (this.form.sync_price_auto) {
            const mult = parseFloat(this.form.price_multiplier) || 1.0;
            return Math.round(Number(this.selectedProduct.selling_price || 0) * mult);
        }
        return Math.round(parseFloat(this.form.channel_price) || 0);
    },
    get computedAdminFee() {
        return Math.round(this.computedEffectivePrice * 0.08);
    },
    get computedNetReceived() {
        return this.computedEffectivePrice - this.computedAdminFee;
    },
    get computedBaseCost() {
        return Number(this.selectedProduct?.base_cost || 0);
    },
    get computedNetMargin() {
        return this.computedNetReceived - this.computedBaseCost;
    },
    get computedMarginPercent() {
        if (this.computedEffectivePrice <= 0) return 0;
        return Math.round((this.computedNetMargin / this.computedEffectivePrice) * 100);
    },
    get isBelowCost() {
        return this.computedBaseCost > 0 && this.computedEffectivePrice < this.computedBaseCost;
    },
    get costDeficit() {
        return Math.max(0, this.computedBaseCost - this.computedEffectivePrice);
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
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
            <form method="POST" action="{{ route('marketplace-hub.products.sync-all') }}" onsubmit="return AppAlert.confirmSubmit(event, 'Mulai Sinkronisasi Massal?', 'Data harga dan stok seluruh produk aktif akan dikirimkan serentak ke Shopee, TikTok Shop, dan Tokopedia.')">
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
    <!-- 1.1 SECTOR CONTEXT & COMPLIANCE BANNERS               -->
    <!-- ===================================================== -->
    @if($isPharmacy)
        <div class="rounded-[20px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 sm:p-5 flex items-start gap-3.5 text-[#FF3B30]">
            <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/15 flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-5 h-5 text-[#FF3B30]"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-[14px] font-bold text-black dark:text-white flex items-center gap-2 flex-wrap">
                    <span>Guardrail Regulasi BPOM RI (Sektor Farmasi &amp; Apotek)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30] text-white">RESTRISI HUKUM</span>
                </h4>
                <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    Sesuai Peraturan BPOM RI No. 8 Tahun 2020, obat keras (daftar G / lingkaran merah), psikotropika/narkotika, dan obat resep dokter <strong>dilarang keras dijual di marketplace umum</strong>. Sistem COOCA secara otomatis mengunci dan menolak pemetaan produk berlabel obat keras untuk melindungi izin operasional apotek Anda.
                </p>
            </div>
        </div>
    @endif

    @if($isServiceSector)
        <div class="rounded-[20px] bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 sm:p-5 flex items-start gap-3.5 text-[#007AFF]">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 flex items-center justify-center shrink-0">
                <i data-lucide="wrench" class="w-5 h-5 text-[#007AFF]"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-[14px] font-bold text-black dark:text-white flex items-center gap-2 flex-wrap">
                    <span>Pemisahan Barang Fisik vs Jasa Kasir (Sektor Servis/Bengkel/Salon)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF] text-white">LOGIKAL FILTER</span>
                </h4>
                <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    Item bertipe <strong>Jasa / Layanan</strong> (ongkos servis, cuci, pemasangan) tidak memerlukan pengiriman kurir ekspedisi dan dikunci dari sinkronisasi marketplace untuk mencegah kesalahan pesanan dan resi logistik. Hanya suku cadang / barang fisik yang dipetakan ke marketplace.
                </p>
            </div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. SEARCH & FILTER BAR                                -->
    <!-- ===================================================== -->
    <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3.5 shadow-xs">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
            <form method="GET" action="{{ route('marketplace-hub.products') }}" class="flex-1 flex items-center gap-2">
                <input type="hidden" name="type" value="{{ request('type', 'all') }}">
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
                    <a href="{{ route('marketplace-hub.products', ['type' => request('type', 'all')]) }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center">
                        Reset
                    </a>
                @endif
            </form>

            <!-- Type Filter Segmented Pill -->
            <div class="flex items-center gap-1 p-1 rounded-[13px] bg-black/[0.04] dark:bg-white/[0.06] shrink-0 self-start sm:self-auto">
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['type' => 'all', 'page' => 1])) }}"
                    class="px-3 py-1.5 rounded-[9px] text-[12px] font-semibold transition-all {{ request('type', 'all') === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-black dark:text-white' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Semua
                </a>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['type' => 'goods', 'page' => 1])) }}"
                    class="px-3 py-1.5 rounded-[9px] text-[12px] font-semibold transition-all {{ request('type', 'all') === 'goods' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#007AFF]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Barang Fisik
                </a>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['type' => 'service', 'page' => 1])) }}"
                    class="px-3 py-1.5 rounded-[9px] text-[12px] font-semibold transition-all {{ request('type', 'all') === 'service' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#FF9500]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Layanan Jasa
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto text-[12px] text-black/60 dark:text-white/60">
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

                            $isRestrictedDrug = $isPharmacy && $product->isRestrictedPharmacyProduct();
                            $isServiceItem    = $product->isService();
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <!-- Column 1: Product Name & Code -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="font-bold text-black dark:text-white text-[13.5px]">{{ $product->name }}</div>
                                    @if($isRestrictedDrug)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/20" title="Obat keras BPOM RI dilarang di marketplace">
                                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                            <span>Terkunci BPOM</span>
                                        </span>
                                    @elseif($isServiceItem)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/20" title="Layanan Jasa Kasir Offline">
                                            <i data-lucide="wrench" class="w-3 h-3"></i>
                                            <span>Jasa Offline</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 flex-wrap">
                                    <span class="font-mono">{{ $product->code ?? 'NO-SKU' }}</span>
                                    <span>•</span>
                                    @if($isServiceItem)
                                        <span class="text-[#FF9500] font-medium">Non-Fisik (Jasa)</span>
                                    @else
                                        <span>Stok Fisik: <strong class="text-black dark:text-white">{{ $product->stock ?? 0 }}</strong> {{ $product->unit ?? 'pcs' }}</span>
                                    @endif
                                    @if($product->base_cost > 0)
                                        <span>•</span>
                                        <span>HPP: <strong class="font-mono text-black dark:text-white">Rp {{ number_format((float)$product->base_cost, 0, ',', '.') }}</strong></span>
                                    @endif
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
                                @if($isRestrictedDrug)
                                    <span class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 inline-flex items-center gap-1 cursor-not-allowed" title="Obat keras dilarang di marketplace sesuai regulasi BPOM RI">
                                        <i data-lucide="lock" class="w-3 h-3"></i>
                                        <span>Terkunci BPOM</span>
                                    </span>
                                @elseif($isServiceItem)
                                    <span class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] inline-flex items-center gap-1 cursor-not-allowed" title="Jasa pengerjaan tidak dapat dikirim via kurir marketplace">
                                        <i data-lucide="ban" class="w-3 h-3"></i>
                                        <span>Jasa Offline</span>
                                    </span>
                                @elseif($shopeeMapping && $shopeeMapping->is_active)
                                    <div class="font-bold text-[#EE4D2D] text-[13px]">
                                        Rp {{ number_format((float)$shopeeMapping->getEffectivePrice(), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50">
                                        {{ $shopeeMapping->sync_price_auto ? 'Otomatis' : 'Harga Khusus' }}
                                    </div>
                                    <button type="button" @click="openMapping(@js($product), 'shopee')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping(@js($product), 'shopee')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Petakan</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Column 4: TikTok Shop Price & Config -->
                            <td class="py-4 px-4 text-center">
                                @if($isRestrictedDrug)
                                    <span class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 inline-flex items-center gap-1 cursor-not-allowed" title="Obat keras dilarang di marketplace sesuai regulasi BPOM RI">
                                        <i data-lucide="lock" class="w-3 h-3"></i>
                                        <span>Terkunci BPOM</span>
                                    </span>
                                @elseif($isServiceItem)
                                    <span class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] inline-flex items-center gap-1 cursor-not-allowed" title="Jasa pengerjaan tidak dapat dikirim via kurir marketplace">
                                        <i data-lucide="ban" class="w-3 h-3"></i>
                                        <span>Jasa Offline</span>
                                    </span>
                                @elseif($tiktokMapping && $tiktokMapping->is_active)
                                    <div class="font-bold text-black dark:text-white text-[13px]">
                                        Rp {{ number_format((float)$tiktokMapping->getEffectivePrice(), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50">
                                        {{ $tiktokMapping->sync_price_auto ? 'Otomatis' : 'Harga Khusus' }}
                                    </div>
                                    <button type="button" @click="openMapping(@js($product), 'tiktok_shop')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping(@js($product), 'tiktok_shop')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Petakan</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Column 5: Tokopedia Price & Config -->
                            <td class="py-4 px-4 text-center">
                                @if($isRestrictedDrug)
                                    <span class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 inline-flex items-center gap-1 cursor-not-allowed" title="Obat keras dilarang di marketplace sesuai regulasi BPOM RI">
                                        <i data-lucide="lock" class="w-3 h-3"></i>
                                        <span>Terkunci BPOM</span>
                                    </span>
                                @elseif($isServiceItem)
                                    <span class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] inline-flex items-center gap-1 cursor-not-allowed" title="Jasa pengerjaan tidak dapat dikirim via kurir marketplace">
                                        <i data-lucide="ban" class="w-3 h-3"></i>
                                        <span>Jasa Offline</span>
                                    </span>
                                @elseif($tokpedMapping && $tokpedMapping->is_active)
                                    <div class="font-bold text-[#00AA5B] text-[13px]">
                                        Rp {{ number_format((float)$tokpedMapping->getEffectivePrice(), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50">
                                        {{ $tokpedMapping->sync_price_auto ? 'Otomatis' : 'Harga Khusus' }}
                                    </div>
                                    <button type="button" @click="openMapping(@js($product), 'tokopedia')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping(@js($product), 'tokopedia')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Petakan</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Column 6: Sync Actions -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                @if($isRestrictedDrug || $isServiceItem)
                                    <div class="flex items-center justify-end">
                                        <span class="text-[11.5px] text-black/40 dark:text-white/40 italic font-medium">
                                            {{ $isRestrictedDrug ? 'Restrisi BPOM' : 'Jasa Non-Ekspedisi' }}
                                        </span>
                                    </div>
                                @else
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
                                @endif
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
    <!-- 4. MODAL PEMETAAN & ATUR HARGA PER CHANNEL (BENTO XXL)-->
    <!-- ===================================================== -->
    <div x-show="mappingModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/40 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="mappingModal = false"
            class="w-full max-w-5xl xl:max-w-6xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-8 space-y-6 shadow-2xl max-h-[92vh] overflow-y-auto">

            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-[18px] font-bold text-black dark:text-white tracking-tight">Atur Multi-Harga &amp; Alokasi Stok</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                Bento XXL v2.0
                            </span>
                        </div>
                        <p class="text-[12.5px] text-black/50 dark:text-white/50" x-text="selectedProduct ? (selectedProduct.name + ' • SKU: ' + (selectedProduct.code || '-')) : ''"></p>
                    </div>
                </div>
                <button type="button" @click="mappingModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 transition-all cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('marketplace-hub.products.map') }}" @submit="submitting = true" class="space-y-6">
                @csrf
                <input type="hidden" name="product_id" :value="form.product_id">
                <input type="hidden" name="channel" :value="form.channel">
                <input type="hidden" name="sync_price_auto" :value="form.sync_price_auto ? '1' : '0'">
                <input type="hidden" name="sync_stock_auto" :value="form.sync_stock_auto ? '1' : '0'">
                <input type="hidden" name="allow_below_cost" :value="form.allow_below_cost ? '1' : '0'">

                <!-- 2-Column Bento Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- ================= LEFT COLUMN: INPUTS & SETTINGS (7 COLS) ================= -->
                    <div class="lg:col-span-7 space-y-5">
                        
                        <!-- 1. Channel Selector Segmented Tabs -->
                        <div>
                            <label class="block text-[12px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                                Pilih Saluran Marketplace
                            </label>
                            <div class="grid grid-cols-3 gap-2 p-1.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <button type="button" @click="switchChannel('shopee')"
                                    :class="form.channel === 'shopee' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#EE4D2D]' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full" :class="form.channel === 'shopee' ? 'bg-[#EE4D2D]' : 'bg-transparent'"></span>
                                    <span>Shopee</span>
                                </button>
                                <button type="button" @click="switchChannel('tiktok_shop')"
                                    :class="form.channel === 'tiktok_shop' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-black dark:text-white' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full" :class="form.channel === 'tiktok_shop' ? 'bg-black dark:bg-white' : 'bg-transparent'"></span>
                                    <span>TikTok Shop</span>
                                </button>
                                <button type="button" @click="switchChannel('tokopedia')"
                                    :class="form.channel === 'tokopedia' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#00AA5B]' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full" :class="form.channel === 'tokopedia' ? 'bg-[#00AA5B]' : 'bg-transparent'"></span>
                                    <span>Tokopedia</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. External Item ID / SKU in Marketplace -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Marketplace Item ID (Opsional)
                                </label>
                                <input type="text" name="marketplace_item_id" x-model="form.marketplace_item_id"
                                    placeholder="Contoh: 1982739281"
                                    class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Marketplace SKU
                                </label>
                                <input type="text" name="marketplace_sku" x-model="form.marketplace_sku"
                                    placeholder="Contoh: SKU-SHP-001"
                                    class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                            </div>
                        </div>

                        <!-- 3. Pricing Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">Skema Penetapan Harga Saluran</span>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">Otomatis ikuti persentase pengali dari harga COOCA atau atur manual tetap</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11.5px] font-bold px-2 py-0.5 rounded-full" :class="form.sync_price_auto ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/10 text-black/60 dark:text-white/60'">
                                        <span x-text="form.sync_price_auto ? 'Otomatis Multiplier' : 'Manual Khusus'"></span>
                                    </span>
                                    <button type="button" @click="form.sync_price_auto = !form.sync_price_auto"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="form.sync_price_auto ? 'bg-[#007AFF]' : 'bg-black/20 dark:bg-white/20'">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            :class="form.sync_price_auto ? 'translate-x-5' : 'translate-x-0'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- If Auto: Multiplier with Quick Presets -->
                            <div x-show="form.sync_price_auto" class="pt-1 space-y-3">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                    Faktor Pengali Harga (Price Multiplier)
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" step="0.01" min="0.5" max="3.0" name="price_multiplier" x-model="form.price_multiplier"
                                        class="w-32 h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono font-bold">
                                    <span class="text-[12.5px] text-black/60 dark:text-white/60">
                                        = <strong class="text-black dark:text-white" x-text="formatRupiah(computedEffectivePrice)"></strong> di channel
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 pt-0.5">
                                    <span class="text-[11px] text-black/45 dark:text-white/45">Preset Cepat:</span>
                                    <button type="button" @click="form.price_multiplier = 1.0"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer">
                                        Sama (+0%)
                                    </button>
                                    <button type="button" @click="form.price_multiplier = 1.05"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer">
                                        +5%
                                    </button>
                                    <button type="button" @click="form.price_multiplier = 1.08"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer">
                                        +8% (Rekomendasi)
                                    </button>
                                    <button type="button" @click="form.price_multiplier = 1.10"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer">
                                        +10%
                                    </button>
                                </div>
                            </div>

                            <!-- If Manual: Custom Price -->
                            <div x-show="!form.sync_price_auto" class="pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                    Harga Tetap Saluran (Rp)
                                </label>
                                <input type="number" step="100" min="0" name="channel_price" x-model="form.channel_price"
                                    placeholder="Contoh: 125000"
                                    class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                            </div>
                        </div>

                        <!-- 4. Stock Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">Skema Alokasi Stok Saluran</span>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">Sinkronisasi stok fisik gudang otomatis atau alokasi kuota khusus</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11.5px] font-bold px-2 py-0.5 rounded-full" :class="form.sync_stock_auto ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/10 text-black/60 dark:text-white/60'">
                                        <span x-text="form.sync_stock_auto ? 'Otomatis Gudang' : 'Manual Kuota'"></span>
                                    </span>
                                    <button type="button" @click="form.sync_stock_auto = !form.sync_stock_auto"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="form.sync_stock_auto ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            :class="form.sync_stock_auto ? 'translate-x-5' : 'translate-x-0'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- If Auto: Stock Buffer -->
                            <div x-show="form.sync_stock_auto" class="pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                    Stok Pengaman Toko Fisik (Safety Buffer)
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" min="0" name="stock_buffer" x-model="form.stock_buffer"
                                        class="w-32 h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono font-bold">
                                    <div class="text-[12px] text-black/60 dark:text-white/60">
                                        Unit yang selalu dicadangkan untuk kasir toko offline (tidak dipublish ke marketplace)
                                    </div>
                                </div>
                            </div>

                            <!-- If Manual: Custom Stock -->
                            <div x-show="!form.sync_stock_auto" class="pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                                    Alokasi Kuota Stok Manual
                                </label>
                                <input type="number" min="0" name="custom_stock" x-model="form.custom_stock"
                                    placeholder="Contoh: 50"
                                    class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                            </div>
                        </div>

                        <!-- 5. Active Toggle -->
                        <div class="flex items-center justify-between p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <div>
                                <span class="text-[13px] font-bold text-black dark:text-white">Aktifkan Sinkronisasi Saluran Ini</span>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">Izinkan pengiriman perubahan harga &amp; stok ke channel yang dipilih</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>
                    </div>

                    <!-- ================= RIGHT COLUMN: LIVE FINANCIAL CALCULATOR & TIPS (5 COLS) ================= -->
                    <div class="lg:col-span-5 space-y-4">
                        
                        <!-- Financial Summary Card (Apple HIG Bento) -->
                        <div class="rounded-[22px] bg-gradient-to-br from-black/[0.02] to-black/[0.05] dark:from-white/[0.03] dark:to-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4 shadow-xs">
                            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">Kalkulasi Margin Saluran (Live)</h4>
                                </div>
                                <span class="text-[11px] font-mono text-black/50 dark:text-white/50 uppercase" x-text="form.channel"></span>
                            </div>

                            <div class="space-y-2.5 text-[12.5px]">
                                <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                    <span>Modal Dasar (HPP):</span>
                                    <span class="font-mono font-semibold text-black dark:text-white" x-text="formatRupiah(computedBaseCost)"></span>
                                </div>
                                <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                    <span>Harga Toko COOCA:</span>
                                    <span class="font-mono font-semibold text-black dark:text-white" x-text="formatRupiah(selectedProduct ? selectedProduct.selling_price : 0)"></span>
                                </div>
                                <div class="flex items-center justify-between text-black dark:text-white font-medium">
                                    <span>Harga Saluran Efektif:</span>
                                    <span class="font-mono font-bold text-[13.5px] text-[#007AFF]" x-text="formatRupiah(computedEffectivePrice)"></span>
                                </div>
                                <div class="flex items-center justify-between text-[#FF3B30] text-[12px]">
                                    <span>Estimasi Biaya Admin Saluran (~8%):</span>
                                    <span class="font-mono font-medium" x-text="'- ' + formatRupiah(computedAdminFee)"></span>
                                </div>

                                <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                    <div class="flex items-center justify-between text-[12.5px] text-black/70 dark:text-white/70">
                                        <span>Estimasi Payout Bersih:</span>
                                        <span class="font-mono font-bold text-black dark:text-white" x-text="formatRupiah(computedNetReceived)"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-[13px] pt-1">
                                        <span class="font-bold text-black dark:text-white">Estimasi Margin Bersih:</span>
                                        <div class="text-right">
                                            <span class="font-mono font-bold text-[14px]" :class="computedNetMargin < 0 ? 'text-[#FF3B30]' : 'text-[#34C759]'" x-text="formatRupiah(computedNetMargin)"></span>
                                            <span class="text-[11px] block font-mono font-semibold" :class="computedNetMargin < 0 ? 'text-[#FF3B30]' : 'text-[#34C759]'" x-text="'(' + computedMarginPercent + '%)'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Anti-Margin Bleed Alert & Risk Consent (Guardrail 3) -->
                            <div x-show="isBelowCost" class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/30 space-y-2.5 text-[#FF3B30]">
                                <div class="flex items-start gap-2.5">
                                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 mt-0.5 text-[#FF3B30]"></i>
                                    <div class="space-y-1">
                                        <div class="text-[12px] font-bold text-[#FF3B30]">
                                            PERINGATAN ANTI-MARGIN BLEED
                                        </div>
                                        <div class="text-[11.5px] leading-tight text-black/80 dark:text-white/80">
                                            Harga jual efektif (<span class="font-mono font-bold" x-text="formatRupiah(computedEffectivePrice)"></span>) berada di bawah modal dasar HPP (<span class="font-mono font-bold" x-text="formatRupiah(computedBaseCost)"></span>). Kerugian modal: <strong class="text-[#FF3B30] font-mono font-bold" x-text="formatRupiah(costDeficit)"></strong> per unit.
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-[#FF3B30]/20 flex items-start gap-2">
                                    <input type="checkbox" id="allow_below_cost" x-model="form.allow_below_cost"
                                        class="mt-0.5 rounded-[4px] border-[#FF3B30] text-[#FF3B30] focus:ring-[#FF3B30] cursor-pointer">
                                    <label for="allow_below_cost" class="text-[11.5px] font-semibold text-black dark:text-white cursor-pointer select-none leading-tight">
                                        Saya menyetujui risiko kerugian dan mengizinkan penetapan harga di bawah modal dasar (HPP).
                                    </label>
                                </div>
                            </div>

                            <!-- Bleed Warning if Margin is Negative but above Base Cost -->
                            <div x-show="!isBelowCost && computedNetMargin < 0" class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 flex items-start gap-2.5 text-[#FF3B30]">
                                <i data-lucide="alert-octagon" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <div class="text-[11.5px] leading-tight font-medium">
                                    <strong>Peringatan Potensi Rugi!</strong> Harga jual saluran setelah potongan fee platform (~8%) berada di bawah modal dasar (HPP).
                                </div>
                            </div>
                        </div>

                        <!-- Channel Guardrails & Rules Card -->
                        <div class="rounded-[22px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-3">
                            <div class="flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                <h4 class="text-[12.5px] font-bold text-black dark:text-white">Panduan Sinkronisasi</h4>
                            </div>
                            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-2 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                                    <span>Setiap pesanan baru di marketplace akan <strong>langsung memotong stok gudang</strong> utama COOCA secara otomatis.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                                    <span>Jika <strong>Stok Pengaman (Buffer)</strong> diset 5 unit dan stok gudang tersisa 8 unit, hanya 3 unit yang dikirimkan ke marketplace.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF] shrink-0 mt-0.5"></i>
                                    <span>Gunakan faktor pengali minimal <code>1.08</code> untuk menjaga margin toko dari potongan komisi marketplace.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="mappingModal = false" :disabled="submitting"
                        class="h-11 px-5 rounded-[14px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting || (isBelowCost && !form.allow_below_cost)"
                        class="h-11 px-6 rounded-[14px] text-[13.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                        <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Menyimpan Pengaturan...' : (isBelowCost && !form.allow_below_cost ? 'Buka Kunci Risiko untuk Simpan' : 'Simpan Pengaturan Saluran')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
