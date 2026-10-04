@extends('layouts.app', [
    'title' => 'Pemetaan Produk & Multi-Harga - ' . $business->name,
    'headerTitle' => 'Pemetaan Produk & Multi-Harga',
    'headerSubtitle' => 'Atur perbedaan harga jual dan alokasi stok untuk Shopee, TikTok Shop, dan Tokopedia',
])

@section('content')
@php
    $productsMap = $products->getCollection()->keyBy('id')->map(function ($p) {
        return [
            'id' => (string) $p->id,
            'name' => $p->name,
            'code' => $p->code ?? '',
            'selling_price' => (float) $p->selling_price,
            'base_cost' => (float) $p->base_cost,
            'stock' => $p->stock ?? 0,
            'unit' => $p->unit ?? 'pcs',
            'description' => $p->description ?? '',
            'category' => $p->category ? ['name' => $p->category->name] : null,
            'images' => $p->images ?? [],
            'image' => $p->image,
            'marketplace_mappings' => $p->marketplaceMappings->map(function ($m) {
                return [
                    'id' => (string) $m->id,
                    'channel' => $m->channel,
                    'marketplace_item_id' => $m->marketplace_item_id ?: $m->external_product_id,
                    'marketplace_sku' => $m->marketplace_sku ?: $m->external_sku_code,
                    'channel_price' => $m->channel_price,
                    'sync_price_auto' => (bool) $m->sync_price_auto,
                    'price_multiplier' => (float) ($m->price_multiplier ?? 1.0),
                    'sync_stock_auto' => (bool) $m->sync_stock_auto,
                    'stock_buffer' => (int) ($m->stock_buffer ?? 0),
                    'custom_stock' => $m->custom_stock,
                    'is_active' => (bool) $m->is_active,
                    'raw_metadata' => $m->raw_metadata,
                ];
            })->values()->all(),
        ];
    })->all();
@endphp

<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12"
    x-data="marketplaceProductManager({
        categories: {{ Js::from($marketplaceCategories ?? []) }},
        products: {{ Js::from($productsMap) }}
    })">

    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        title="Pengaturan Multi-Harga &amp; Stok Marketplace"
        subtitle="Tetapkan harga berbeda per channel (Shopee, TikTok Shop, Tokopedia) untuk menutupi biaya admin marketplace serta sinkronisasi stok otomatis.">
        <x-slot:actions>
            <form method="POST" action="{{ route('marketplace-hub.products.sync-all') }}" onsubmit="return AppAlert.confirmSubmit(event, 'Mulai Sinkronisasi Massal?', 'Data harga dan stok seluruh produk aktif akan dikirimkan serentak ke Shopee, TikTok Shop, dan Tokopedia.')">
                @csrf
                <button type="submit"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Sinkronisasi Massal Semua</span>
                </button>
            </form>
            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Hub</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

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
                                    <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#EE4D2D] bg-[#EE4D2D]/10 hover:bg-[#EE4D2D]/20 inline-flex items-center gap-1 cursor-pointer transition-colors" title="1-Click Terbitkan ke Shopee atau Petakan">
                                        <i data-lucide="cloud-upload" class="w-3 h-3"></i>
                                        <span>Terbitkan</span>
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
                                    <button type="button" @click="openMapping('{{ $product->id }}', 'tiktok_shop')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping('{{ $product->id }}', 'tiktok_shop')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-black dark:text-white bg-black/[0.06] dark:bg-white/[0.1] hover:bg-black/[0.1] inline-flex items-center gap-1 cursor-pointer transition-colors" title="1-Click Terbitkan ke TikTok Shop atau Petakan">
                                        <i data-lucide="cloud-upload" class="w-3 h-3"></i>
                                        <span>Terbitkan</span>
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
                                    <button type="button" @click="openMapping('{{ $product->id }}', 'tokopedia')"
                                        class="text-[11px] font-semibold text-[#007AFF] hover:underline mt-1 inline-block cursor-pointer">
                                        Ubah
                                    </button>
                                @else
                                    <button type="button" @click="openMapping('{{ $product->id }}', 'tokopedia')"
                                        class="h-7 px-2.5 rounded-[8px] text-[11px] font-semibold text-[#00AA5B] bg-[#00AA5B]/10 hover:bg-[#00AA5B]/20 inline-flex items-center gap-1 cursor-pointer transition-colors" title="1-Click Terbitkan ke Tokopedia atau Petakan">
                                        <i data-lucide="cloud-upload" class="w-3 h-3"></i>
                                        <span>Terbitkan</span>
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
                <input type="hidden" name="category_id" :value="form.category_id">
                <input type="hidden" name="category_name" :value="form.category_name">
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

                        <!-- 1.5 Official Marketplace Category Selector Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-[13px] font-bold text-black dark:text-white">
                                        Kategori Resmi Marketplace
                                    </label>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                        Wajib untuk listing TikTok Shop &amp; Tokopedia (30 Kategori Resmi)
                                    </p>
                                </div>
                                <template x-if="selectedCategoryObj">
                                    <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                        ID: <span x-text="form.category_id"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Active Selection Card / Trigger Button -->
                            <div class="relative">
                                <button type="button" @click="categoryDropdownOpen = !categoryDropdownOpen"
                                    class="w-full p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] hover:border-[#007AFF] transition-all flex items-center justify-between text-left gap-3 shadow-2xs cursor-pointer">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                            <i data-lucide="layers" class="w-5 h-5"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-[13px] font-bold text-black dark:text-white truncate" x-text="selectedCategoryObj ? selectedCategoryObj.name : 'Pilih Kategori Marketplace...'"></div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[280px] sm:max-w-md" x-text="selectedCategoryObj ? selectedCategoryObj.description : 'Klik untuk mencari atau memilih kategori resmi'"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 text-black/40 dark:text-white/40">
                                        <span class="text-[11.5px] font-medium hidden sm:inline" x-text="categoryDropdownOpen ? 'Tutup' : 'Ubah'"></span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="categoryDropdownOpen ? 'rotate-180' : ''"></i>
                                    </div>
                                </button>

                                <!-- Searchable Dropdown Overlay Card (Bento Sheet) -->
                                <div x-show="categoryDropdownOpen" @click.away="categoryDropdownOpen = false" x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                    class="absolute z-50 left-0 right-0 top-full mt-2 p-3 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-xl max-h-[340px] flex flex-col space-y-2.5">
                                    
                                    <!-- Search bar -->
                                    <div class="relative">
                                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                                        <input type="text" x-model="categorySearch" placeholder="Cari nama kategori, id, atau kata kunci (contoh: makanan, sepatu, baju)..."
                                            class="w-full h-9 pl-9 pr-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[12.5px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                                    </div>

                                    <!-- Categories List Scrollable -->
                                    <div class="overflow-y-auto space-y-1 pr-1 flex-1 max-h-[240px] custom-scrollbar">
                                        <template x-for="cat in filteredCategories" :key="cat.id">
                                            <button type="button" @click="selectCategory(cat)"
                                                :class="form.category_id === cat.id ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'hover:bg-black/[0.03] dark:hover:bg-white/[0.05] border-transparent text-black dark:text-white'"
                                                class="w-full p-2.5 rounded-[12px] border text-left flex items-center justify-between gap-2.5 transition-colors cursor-pointer group">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[12.5px] font-semibold group-hover:text-[#007AFF] transition-colors" x-text="cat.name"></span>
                                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60" x-text="cat.id"></span>
                                                    </div>
                                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate mt-0.5" x-text="cat.description"></p>
                                                </div>
                                                <template x-if="form.category_id === cat.id">
                                                    <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                                </template>
                                            </button>
                                        </template>
                                        <div x-show="filteredCategories.length === 0" class="py-6 text-center text-[12px] text-black/40 dark:text-white/40">
                                            Tidak ada kategori yang cocok dengan pencarian.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Marketplace Listing Status & Item ID/SKU -->
                        <div class="space-y-3">
                            <div class="p-3.5 rounded-[16px] border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 text-[12.5px]"
                                :class="form.marketplace_item_id ? 'bg-[#34C759]/10 border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF]'">
                                <div class="flex items-center gap-2.5">
                                    <i :data-lucide="form.marketplace_item_id ? 'check-circle-2' : 'sparkles'" class="w-4 h-4 shrink-0"></i>
                                    <div>
                                        <template x-if="form.marketplace_item_id">
                                            <span>Terhubung di channel: <strong class="font-mono" x-text="form.marketplace_item_id"></strong></span>
                                        </template>
                                        <template x-if="!form.marketplace_item_id">
                                            <span>Belum terdaftar di channel ini. Klik <strong>"1-Click Terbitkan"</strong> di bawah untuk otomatis upload galeri &amp; terbitkan listing baru.</span>
                                        </template>
                                    </div>
                                </div>
                                <div x-show="selectedProduct" class="shrink-0 text-[11px] font-semibold opacity-90 px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-black/20 border border-current/20">
                                    <span x-text="((selectedProduct?.images ? selectedProduct.images.length : 0) + (selectedProduct?.image ? 1 : 0)) + ' Foto Siap Terbit'"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        Marketplace Item ID (Manual)
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
                        </div>


                        <!-- 3. Pricing Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">Skema Penetapan Harga Saluran</span>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">Tentukan harga berdasarkan nominal Rupiah langsung atau persentase markup</p>
                                </div>
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" 
                                    :class="pricingMode === 'nominal' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#007AFF]/10 text-[#007AFF]'">
                                    <span x-text="pricingMode === 'nominal' ? 'Harga Tetap Rp' : 'Persentase %'"></span>
                                </span>
                            </div>

                            <!-- Segmented Control Tabs (Harga Langsung vs Persentase Markup) -->
                            <div class="grid grid-cols-2 gap-1.5 p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <button type="button" @click="setPricingMode('nominal')"
                                    :class="pricingMode === 'nominal' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="tag" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>Harga Langsung (Rp)</span>
                                </button>
                                <button type="button" @click="setPricingMode('percentage')"
                                    :class="pricingMode === 'percentage' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="percent" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>Persentase Markup (%)</span>
                                </button>
                            </div>

                            <!-- MODE 1: HARGA LANGSUNG / NOMINAL RP -->
                            <div x-show="pricingMode === 'nominal'" class="space-y-3 pt-1">
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        Masukkan Harga Jual di Marketplace (Rp)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[13px]">Rp</span>
                                        <input type="number" step="100" min="0" name="channel_price" x-model="form.channel_price"
                                            placeholder="Contoh: 150000"
                                            class="w-full h-11 pl-10 pr-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#34C759]/40">
                                    </div>
                                    
                                    <!-- Live comparison info with COOCA Store Price -->
                                    <div class="mt-2 text-[12px] flex items-center gap-1.5" x-show="form.channel_price && selectedProduct">
                                        <template x-if="nominalDiffVsStore.rawDiff > 0">
                                            <span class="text-[#34C759] font-medium flex items-center gap-1">
                                                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                                                <span>+<strong x-text="formatRupiah(nominalDiffVsStore.amount)"></strong> (+<span x-text="nominalDiffVsStore.percent"></span>%) dibanding harga toko COOCA (<span x-text="formatRupiah(selectedProduct.selling_price)"></span>)</span>
                                            </span>
                                        </template>
                                        <template x-if="nominalDiffVsStore.rawDiff < 0">
                                            <span class="text-[#FF9500] font-medium flex items-center gap-1">
                                                <i data-lucide="trending-down" class="w-3.5 h-3.5"></i>
                                                <span>-<strong x-text="formatRupiah(nominalDiffVsStore.amount)"></strong> (-<span x-text="nominalDiffVsStore.percent"></span>%) dibanding harga toko COOCA (<span x-text="formatRupiah(selectedProduct.selling_price)"></span>)</span>
                                            </span>
                                        </template>
                                        <template x-if="nominalDiffVsStore.rawDiff === 0">
                                            <span class="text-black/50 dark:text-white/50 font-medium">
                                                Sama persis dengan harga toko COOCA (<span x-text="formatRupiah(selectedProduct?.selling_price || 0)"></span>)
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Quick nominal presets -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-0.5" x-show="selectedProduct">
                                    <span class="text-[11px] text-black/45 dark:text-white/45">Preset Cepat:</span>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0))"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        Sama Harga Toko
                                    </button>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0) + 5000)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        +Rp 5.000
                                    </button>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0) + 10000)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        +Rp 10.000
                                    </button>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0) + 20000)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        +Rp 20.000
                                    </button>
                                </div>
                            </div>

                            <!-- MODE 2: PERSENTASE MARKUP (%) -->
                            <div x-show="pricingMode === 'percentage'" class="space-y-3 pt-1">
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        Persentase Markup dari Harga Toko COOCA
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-36">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[13px]">+</span>
                                            <input type="number" step="0.5" min="-50" max="200" x-model="markupPercent" @input="updateMultiplierFromPercent()"
                                                placeholder="Contoh: 10"
                                                class="w-full h-11 pl-8 pr-7 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[13px]">%</span>
                                        </div>
                                        <input type="hidden" name="price_multiplier" :value="form.price_multiplier">
                                        <div class="text-[13px] text-black/70 dark:text-white/70">
                                            = <strong class="text-black dark:text-white font-mono text-[14px]" x-text="formatRupiah(computedEffectivePrice)"></strong> di channel
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick percentage presets -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                    <span class="text-[11px] text-black/45 dark:text-white/45">Preset Persen:</span>
                                    <button type="button" @click="setMarkupPercent(0)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 0 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        Sama (+0%)
                                    </button>
                                    <button type="button" @click="setMarkupPercent(5)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 5 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +5%
                                    </button>
                                    <button type="button" @click="setMarkupPercent(8)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 8 ? 'border-[#007AFF] text-[#007AFF] ring-1 ring-[#007AFF]' : ''">
                                        +8% (Rekomendasi Fee)
                                    </button>
                                    <button type="button" @click="setMarkupPercent(10)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 10 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +10%
                                    </button>
                                    <button type="button" @click="setMarkupPercent(15)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 15 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +15%
                                    </button>
                                    <button type="button" @click="setMarkupPercent(20)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 20 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +20%
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Stock Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">Skema Alokasi Stok Saluran</span>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">Sinkronisasi otomatis stok fisik gudang atau tetapkan kuota stok mandiri</p>
                                </div>
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" :class="stockMode === 'auto' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/10 text-black/60 dark:text-white/60'">
                                    <span x-text="stockMode === 'auto' ? 'Otomatis Gudang' : 'Manual Kuota'"></span>
                                </span>
                            </div>

                            <!-- Segmented Control Tabs (Stok Otomatis vs Kuota Manual) -->
                            <div class="grid grid-cols-2 gap-1.5 p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <button type="button" @click="setStockMode('auto')"
                                    :class="stockMode === 'auto' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>Sinkron Stok Gudang (Auto)</span>
                                </button>
                                <button type="button" @click="setStockMode('manual')"
                                    :class="stockMode === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>Kuota Stok Khusus (Manual)</span>
                                </button>
                            </div>

                            <!-- If Auto: Stock Buffer -->
                            <div x-show="stockMode === 'auto'" class="space-y-2 pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                    Stok Pengaman Toko Fisik / Kasir Offline (Safety Buffer)
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" min="0" name="stock_buffer" x-model="form.stock_buffer"
                                        class="w-32 h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold">
                                    <div class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                        Unit yang selalu dicadangkan untuk kasir toko offline (tidak akan dikirim ke marketplace).
                                    </div>
                                </div>
                            </div>

                            <!-- If Manual: Custom Stock -->
                            <div x-show="stockMode === 'manual'" class="space-y-2 pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                    Alokasi Kuota Stok Manual di Marketplace
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" name="custom_stock" x-model="form.custom_stock"
                                        placeholder="Contoh: 50"
                                        class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                </div>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">Hanya angka ini yang akan dikirim ke marketplace, terlepas dari total stok aktual di gudang.</p>
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
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <div class="text-[12px] text-black/50 dark:text-white/50">
                        <span x-show="form.marketplace_item_id">Item ID terhubung: <strong class="font-mono text-black dark:text-white" x-text="form.marketplace_item_id"></strong></span>
                        <span x-show="!form.marketplace_item_id" class="inline-flex items-center gap-1 text-[#FF9500] font-semibold">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            <span>Listing belum diterbitkan ke marketplace</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 flex-wrap">
                        <button type="button" @click="mappingModal = false" :disabled="submitting || publishing"
                            class="h-11 px-4 rounded-[14px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="button" @click="publishToMarketplace()" :disabled="publishing || submitting || (isBelowCost && !form.allow_below_cost)"
                            class="h-11 px-5 rounded-[14px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer"
                            title="Upload listing baru dan seluruh foto galeri langsung ke marketplace">
                            <svg x-show="publishing" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <i x-show="!publishing" data-lucide="cloud-upload" class="w-4 h-4"></i>
                            <span x-text="publishing ? 'Menerbitkan Listing...' : '1-Click Terbitkan ke ' + (form.channel === 'tiktok_shop' ? 'TikTok Shop' : (form.channel === 'tokopedia' ? 'Tokopedia' : 'Shopee'))"></span>
                        </button>
                        <button type="submit" :disabled="submitting || publishing || (isBelowCost && !form.allow_below_cost)"
                            class="h-11 px-5 rounded-[14px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                            <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Menyimpan Pengaturan...' : (isBelowCost && !form.allow_below_cost ? 'Buka Kunci Risiko untuk Simpan' : 'Simpan Pengaturan Saluran')"></span>
                        </button>

                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function() {
    function initMarketplaceAlpine() {
        if (typeof Alpine !== 'undefined') {
            registerComponent();
        } else {
            document.addEventListener('alpine:init', registerComponent);
        }
    }

    function registerComponent() {
        if (!window.Alpine) return;
        Alpine.data('marketplaceProductManager', (config = {}) => ({
            mappingModal: false,
            syncModal: false,
            submitting: false,
            publishing: false,
            selectedProduct: null,
            categories: config.categories || [],
            productsMap: config.products || {},
            categorySearch: '',
            categoryDropdownOpen: false,
            pricingMode: 'nominal', // 'nominal' (Harga Langsung Rp) or 'percentage' (Markup %)
            markupPercent: 0,
            stockMode: 'auto', // 'auto' (Gudang) or 'manual' (Kuota Khusus)
            form: {
                product_id: '',
                channel: 'shopee',
                category_id: '',
                category_name: '',
                marketplace_item_id: '',
                marketplace_sku: '',
                channel_price: '',
                sync_price_auto: false,
                price_multiplier: 1.0,
                sync_stock_auto: true,
                stock_buffer: 0,
                custom_stock: '',
                is_active: true,
                allow_below_cost: false
            },
            openMapping(productOrId, channel) {
                const product = (typeof productOrId === 'object' && productOrId !== null)
                    ? productOrId
                    : (this.productsMap[productOrId] || null);
                if (!product) return;
                this.selectedProduct = product;
                this.form.product_id = product.id;
                this.form.allow_below_cost = false;
                this.categorySearch = '';
                this.categoryDropdownOpen = false;
                this.switchChannel(channel || 'shopee');
                this.submitting = false;
                this.publishing = false;
                this.mappingModal = true;
            },
            switchChannel(channel) {
                this.form.channel = channel;
                this.form.allow_below_cost = false;
                if (!this.selectedProduct) return;
                const existing = (this.selectedProduct.marketplace_mappings || []).find(m => m.channel === channel);
                if (existing) {
                    this.form.marketplace_item_id = existing.marketplace_item_id || existing.external_product_id || '';
                    this.form.marketplace_sku = existing.marketplace_sku || existing.external_sku_code || '';
                    this.form.category_id = (existing.raw_metadata && existing.raw_metadata.category_id) ? existing.raw_metadata.category_id : '';
                    this.form.category_name = (existing.raw_metadata && existing.raw_metadata.category_name) ? existing.raw_metadata.category_name : '';
                    if (!this.form.category_id) {
                        this.autoDetectCategory(this.selectedProduct);
                    }
                    this.form.channel_price = existing.channel_price || '';
                    this.form.sync_price_auto = Boolean(existing.sync_price_auto);
                    this.form.price_multiplier = existing.price_multiplier || 1.0;
                    this.pricingMode = this.form.sync_price_auto ? 'percentage' : 'nominal';
                    this.markupPercent = Math.round(((parseFloat(this.form.price_multiplier) || 1.0) - 1.0) * 100);
                    this.form.sync_stock_auto = Boolean(existing.sync_stock_auto);
                    this.stockMode = this.form.sync_stock_auto ? 'auto' : 'manual';
                    this.form.stock_buffer = existing.stock_buffer || 0;
                    this.form.custom_stock = existing.custom_stock !== null ? existing.custom_stock : '';
                    this.form.is_active = Boolean(existing.is_active);
                } else {
                    this.form.marketplace_item_id = '';
                    this.form.marketplace_sku = this.selectedProduct.code || '';
                    this.autoDetectCategory(this.selectedProduct);
                    this.form.channel_price = this.selectedProduct.selling_price || '';
                    this.form.sync_price_auto = false;
                    this.form.price_multiplier = 1.0;
                    this.pricingMode = 'nominal';
                    this.markupPercent = 0;
                    this.form.sync_stock_auto = true;
                    this.stockMode = 'auto';
                    this.form.stock_buffer = 0;
                    this.form.custom_stock = '';
                    this.form.is_active = true;
                }
            },
            autoDetectCategory(product) {
                if (!product || !this.categories || !this.categories.length) return;
                const text = ((product.name || '') + ' ' + (product.category?.name || '') + ' ' + (product.description || '')).toLowerCase();
                let bestMatch = null;
                let maxScore = 0;
                for (const cat of this.categories) {
                    let score = 0;
                    if (text.includes(cat.name.toLowerCase())) {
                        score += 10;
                    }
                    if (cat.keywords && Array.isArray(cat.keywords)) {
                        for (const kw of cat.keywords) {
                            if (text.includes(kw.toLowerCase())) {
                                score += 3;
                            }
                        }
                    }
                    if (score > maxScore) {
                        maxScore = score;
                        bestMatch = cat;
                    }
                }
                if (bestMatch && maxScore > 0) {
                    this.form.category_id = bestMatch.id;
                    this.form.category_name = bestMatch.name;
                } else {
                    this.form.category_id = this.categories[0]?.id || '';
                    this.form.category_name = this.categories[0]?.name || '';
                }
            },
            get filteredCategories() {
                if (!this.categorySearch || !this.categorySearch.trim()) return this.categories;
                const q = this.categorySearch.toLowerCase().trim();
                return this.categories.filter(c => 
                    c.name.toLowerCase().includes(q) || 
                    (c.id && c.id.includes(q)) || 
                    (c.description && c.description.toLowerCase().includes(q)) ||
                    (c.keywords && c.keywords.some(k => k.toLowerCase().includes(q)))
                );
            },
            get selectedCategoryObj() {
                return this.categories.find(c => c.id === this.form.category_id) || null;
            },
            selectCategory(cat) {
                this.form.category_id = cat.id;
                this.form.category_name = cat.name;
                this.categoryDropdownOpen = false;
                this.categorySearch = '';
            },
            setPricingMode(mode) {
                this.pricingMode = mode;
                if (mode === 'percentage') {
                    this.form.sync_price_auto = true;
                    this.updateMultiplierFromPercent();
                } else {
                    this.form.sync_price_auto = false;
                    if (!this.form.channel_price || Number(this.form.channel_price) === 0) {
                        this.form.channel_price = this.computedEffectivePrice || this.selectedProduct?.selling_price || 0;
                    }
                }
            },
            updateMultiplierFromPercent() {
                const p = parseFloat(this.markupPercent) || 0;
                this.form.price_multiplier = Math.round((1 + (p / 100)) * 1000) / 1000;
            },
            setMarkupPercent(p) {
                this.markupPercent = p;
                this.setPricingMode('percentage');
                this.updateMultiplierFromPercent();
            },
            setNominalPrice(price) {
                this.form.channel_price = Math.max(0, Math.round(price));
                this.setPricingMode('nominal');
            },
            setStockMode(mode) {
                this.stockMode = mode;
                this.form.sync_stock_auto = (mode === 'auto');
            },
            async publishToMarketplace() {
                if (!this.selectedProduct) return;
                if (this.isBelowCost && !this.form.allow_below_cost) {
                    if (window.AppAlert) {
                        AppAlert.toast('Buka kunci persetujuan risiko harga di bawah modal dasar (HPP) untuk melanjutkan.', 'warning');
                    }
                    return;
                }

                const channelName = this.form.channel === 'tiktok_shop' ? 'TikTok Shop' : (this.form.channel === 'tokopedia' ? 'Tokopedia' : 'Shopee');
                
                if (window.AppAlert && typeof window.AppAlert['confirm'] === 'function') {
                    const confirmed = await window.AppAlert['confirm'](
                        `1-Click Terbitkan ke ${channelName}?`,
                        `Produk "${this.selectedProduct.name}" beserta foto utama dan seluruh galeri fotonya akan langsung diunggah dan dibuatkan listing baru di ${channelName}.`
                    );
                    if (!confirmed) return;
                }

                this.publishing = true;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const url = `/marketplace-hub/products/${this.selectedProduct.id}/publish`;
                    
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify({
                            channel: this.form.channel,
                            category_id: this.form.category_id,
                            category_name: this.form.category_name,
                            channel_price: this.form.channel_price,
                            sync_price_auto: this.form.sync_price_auto,
                            price_multiplier: this.form.price_multiplier,
                            custom_stock: this.form.custom_stock,
                            sync_stock_auto: this.form.sync_stock_auto,
                            stock_buffer: this.form.stock_buffer,
                            allow_below_cost: this.form.allow_below_cost
                        })
                    });

                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.error || data.message || 'Gagal menerbitkan produk ke marketplace.');
                    }

                    this.form.marketplace_item_id = data.external_product_id || this.form.marketplace_item_id;
                    
                    if (window.AppAlert) {
                        AppAlert.toast(data.message || `Berhasil menerbitkan listing ke ${channelName}!`, 'success');
                    }

                    // Close modal sheet reactively and update in-memory product mapping
                    this.mappingModal = false;
                    if (this.selectedProduct) {
                        if (!this.selectedProduct.marketplace_mappings) {
                            this.selectedProduct.marketplace_mappings = [];
                        }
                        const existingIdx = this.selectedProduct.marketplace_mappings.findIndex(m => m.channel === this.form.channel);
                        const updatedMapping = {
                            channel: this.form.channel,
                            marketplace_item_id: this.form.marketplace_item_id,
                            channel_price: this.form.channel_price,
                            is_active: this.form.is_active,
                        };
                        if (existingIdx >= 0) {
                            this.selectedProduct.marketplace_mappings[existingIdx] = Object.assign(this.selectedProduct.marketplace_mappings[existingIdx], updatedMapping);
                        } else {
                            this.selectedProduct.marketplace_mappings.push(updatedMapping);
                        }
                    }
                } catch (err) {
                    if (window.AppAlert) {
                        AppAlert.toast(err.message, 'error');
                    } else {
                        alert(err.message);
                    }
                } finally {
                    this.publishing = false;
                }
            },
            get nominalDiffVsStore() {
                if (!this.selectedProduct) return { amount: 0, rawDiff: 0, percent: 0, isHigher: true };
                const storePrice = Number(this.selectedProduct.selling_price || 0);
                const channelPrice = Number(this.form.channel_price || 0);
                const diff = channelPrice - storePrice;
                const percent = storePrice > 0 ? Math.round((diff / storePrice) * 100) : 0;
                return {
                    amount: Math.abs(diff),
                    rawDiff: diff,
                    percent: Math.abs(percent),
                    isHigher: diff >= 0
                };
            },
            get computedEffectivePrice() {
                if (!this.selectedProduct) return 0;
                if (this.pricingMode === 'percentage' || this.form.sync_price_auto) {
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
        }));
    }

    initMarketplaceAlpine();
})();
</script>
@endpush
