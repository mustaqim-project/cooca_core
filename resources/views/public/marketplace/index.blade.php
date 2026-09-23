@extends('layouts.public_marketing')

@section('title', 'Marketplace UMKM Indonesia | Cooca')
@section('description', 'Belanja produk UMKM lokal terpercaya langsung dari pemilik usaha. Kuliner, busana, kerajinan,
    jasa, dan ribuan produk lainnya.')
@section('keywords', 'marketplace umkm, belanja produk lokal, toko online umkm, produk umkm indonesia, cooca
    marketplace')

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

            <!-- 2-Grid Hero Section with Live Search -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-4">
                <!-- Left: Headline, Search, Actions (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="store" class="w-4 h-4"></i>
                        <span>Direktori UMKM Indonesia Terverifikasi</span>
                    </div>

                    <h1
                        class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Belanja Langsung dari Pemilik Usaha Lokal.
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Temukan {{ number_format($totalProducts) }} produk unggulan dari {{ number_format($totalStores) }}
                        toko UMKM binaan di seluruh Indonesia. Transaksi langsung, aman, dan tanpa biaya perantara berlebih.
                    </p>

                    <!-- Search Input Form -->
                    <form method="GET" action="{{ route('marketplace.search') }}" class="pt-1">
                        <div
                            class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/[0.1] dark:border-white/[0.12] p-1.5 shadow-sm focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                            <i data-lucide="search" class="w-5 h-5 ml-3.5 text-[#6E6E73] dark:text-[#86868B] shrink-0"></i>
                            <input type="text" name="q" placeholder="Cari nama produk, toko, atau kota..."
                                class="w-full bg-transparent border-0 px-3.5 py-3 text-[16px] text-[#1D1D1F] dark:text-[#F5F5F7] placeholder-[#6E6E73]/50 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-sm font-semibold shadow-sm active:scale-[0.98] transition">
                                Cari
                            </button>
                        </div>
                    </form>

                    <!-- Auth / Action Row -->
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        @if (auth('customer')->guest())
                            <a href="{{ route('customer.login') }}"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-sm transition active:scale-[0.98]">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                <span>Masuk Sebagai Pembeli</span>
                            </a>
                        @else
                            <a href="{{ route('customer.dashboard') }}"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-sm transition active:scale-[0.98]">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Dashboard Saya</span>
                            </a>
                        @endif

                        <a href="{{ route('login') }}"
                            class="h-11 px-5 rounded-[12px] bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] border border-black/[0.1] dark:border-white/[0.12] text-xs sm:text-sm font-semibold hover:bg-black/[0.02] dark:hover:bg-white/[0.04] transition active:scale-[0.98] flex items-center gap-2">
                            <i data-lucide="store" class="w-4 h-4"></i>
                            <span>Masuk Toko</span>
                        </a>

                        @if (auth('web')->guest())
                            <a href="{{ route('register') }}"
                                class="h-11 px-5 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm font-semibold hover:bg-[#34C759]/20 transition active:scale-[0.98] flex items-center gap-2">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span>Buka Toko Gratis</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Right: Verified Marketplace Ecosystem Preview (5 cols) -->
                <div class="lg:col-span-5">
                    <div
                        class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">
                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span
                                    class="text-xs font-mono font-semibold text-[#6E6E73] dark:text-[#86868B] ml-2">Katalog
                                    UMKM Terverifikasi</span>
                            </div>
                            <span
                                class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <i data-lucide="badge-check" class="w-3 h-3"></i> Terkurasi
                            </span>
                        </div>

                        <!-- Highlighted Merchant Card Simulation -->
                        <div
                            class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 rounded-[12px] bg-[#007AFF] text-white font-bold text-sm flex items-center justify-center">
                                    UM
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] truncate">Sentra
                                            Kopi Nusantara</span>
                                        <i data-lucide="badge-check" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                    </div>
                                    <div class="text-xs text-[#6E6E73] dark:text-[#86868B]">Kab. Bandung • Toko Aktif</div>
                                </div>
                            </div>

                            <div
                                class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                                <div class="space-y-0.5">
                                    <span class="text-[#6E6E73] dark:text-[#86868B]">Produk Unggulan</span>
                                    <div class="font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">Kopi Arabika Ciwidey
                                        (250g)</div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">Rp
                                        65.000</span>
                                </div>
                            </div>
                        </div>

                        <!-- Trust Points Row -->
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div
                                class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                <span class="text-[#1D1D1F] dark:text-[#F5F5F7] font-medium">Bebas Penipuan</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2">
                                <i data-lucide="truck" class="w-4 h-4 text-[#007AFF]"></i>
                                <span class="text-[#1D1D1F] dark:text-[#F5F5F7] font-medium">Kirim Seluruh RI</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Category Grid -->
            <section class="space-y-6">
                <div
                    class="flex items-center justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kategori Usaha</h2>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] mt-0.5">Temukan produk berdasarkan
                            bidang bisnis</p>
                    </div>
                    <a href="{{ route('public.discovery.index') }}"
                        class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                        <span>Lihat Semua Toko</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($categories as $key => $cat)
                        <a href="{{ route('marketplace.search', ['kategori' => $key]) }}"
                            class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/30 transition text-center group flex flex-col items-center justify-center gap-3 shadow-sm">
                            <div
                                class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]">
                                <i data-lucide="{{ $cat['icon'] }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span
                                    class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] block">{{ $cat['label'] }}</span>
                                <span
                                    class="text-xs text-[#6E6E73] dark:text-[#86868B] tabular-nums mt-0.5 block">{{ $cat['count'] }}
                                    toko</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- Popular Products Section -->
            @if ($popularProducts->isNotEmpty())
                <section class="space-y-6">
                    <div
                        class="flex items-center justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Produk Pilihan</h2>
                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] mt-0.5">Rekomendasi dari gerai
                                UMKM terverifikasi</p>
                        </div>
                        <a href="{{ route('marketplace.search') }}"
                            class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                            <span>Semua Produk</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($popularProducts as $product)
                            <a href="{{ url('/' . $product->business->slug . '/produk/' . ($product->slug ?: $product->id)) }}"
                                class="group bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden hover:border-[#007AFF]/30 transition shadow-sm flex flex-col justify-between">
                                <div>
                                    <div
                                        class="aspect-square bg-black/[0.02] dark:bg-white/[0.04] overflow-hidden flex items-center justify-center">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-black/20 dark:text-white/20">
                                                <i data-lucide="package" class="w-10 h-10"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-4 space-y-1.5">
                                        <h3
                                            class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] line-clamp-2 leading-snug group-hover:text-[#007AFF] transition">
                                            {{ $product->name }}
                                        </h3>
                                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] line-clamp-1">
                                            {{ $product->business->name }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-4 pt-0">
                                    <p
                                        class="font-extrabold text-base text-[#1D1D1F] dark:text-[#F5F5F7] tabular-nums font-mono">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Featured Stores Section -->
            @if ($featuredStores->isNotEmpty())
                <section class="space-y-6">
                    <div
                        class="flex items-center justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Toko Terdaftar</h2>
                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] mt-0.5">Jelajahi profil lengkap
                                usaha UMKM</p>
                        </div>
                        <a href="{{ route('public.discovery.index') }}"
                            class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                            <span>Semua Direktori Toko</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($featuredStores as $store)
                            <a href="{{ url('/' . $store->slug) }}"
                                class="group bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 hover:border-[#007AFF]/30 transition space-y-3 shadow-sm">
                                <div class="flex items-center gap-3">
                                    @if ($store->logo_url || $store->landingPage?->logo_url)
                                        <img src="{{ $store->logo_url ?: $store->landingPage?->logo_url }}"
                                            alt="{{ $store->name }}"
                                            class="w-12 h-12 rounded-[14px] object-cover border border-black/[0.06] dark:border-white/[0.08]">
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-[14px] bg-[#007AFF] flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($store->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h3
                                            class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] truncate group-hover:text-[#007AFF] transition">
                                            {{ $store->name }}</h3>
                                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] truncate">
                                            {{ $store->industry_category ?: 'Toko Online' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-xs text-[#6E6E73] dark:text-[#86868B]">
                                    <span class="flex items-center gap-1 tabular-nums font-medium">
                                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                        {{ $store->products_count }} produk
                                    </span>
                                    @if ($store->address)
                                        <span class="flex items-center gap-1 truncate">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0"></i>
                                            {{ \Illuminate\Support\Str::limit($store->address, 20) }}
                                        </span>
                                    @endif
                                </div>

                                @if ($store->landingPage?->subheadline)
                                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] line-clamp-2 leading-relaxed">
                                        {{ \Illuminate\Support\Str::limit($store->landingPage->subheadline, 80) }}
                                    </p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Seller Acquisition Banner (Apple Inset Enterprise Style) -->
            <section
                class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div
                    class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="store" class="w-4 h-4 text-[#34C759]"></i>
                    <span>Untuk Pengusaha &amp; Pemilik Toko</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Buka Katalog Toko Online Anda Sendiri di COOCA
                </h2>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Kelola stok toko, catat penjualan kasir, dan terima pesanan online langsung tanpa potongan komisi per
                    transaksi. 100% gratis selamanya.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="{{ route('register') }}"
                        class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-sm font-semibold flex items-center gap-2 shadow-sm active:scale-95 transition min-h-[48px]">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Daftar Buka Toko Gratis</span>
                    </a>
                    <a href="{{ route('login') }}"
                        class="h-12 px-7 rounded-[14px] bg-white/[0.08] hover:bg-white/[0.12] text-white border border-white/[0.15] text-sm font-semibold transition active:scale-95 flex items-center gap-2 min-h-[48px]">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk Dashboard Kasir</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
