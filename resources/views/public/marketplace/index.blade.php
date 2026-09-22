@extends('layouts.public_marketing')

@section('title', 'Marketplace UMKM Indonesia | Cooca')
@section('description', 'Belanja produk UMKM lokal terpercaya langsung dari pemilik usaha. Kuliner, busana, kerajinan, jasa, dan ribuan produk lainnya.')
@section('keywords', 'marketplace umkm, belanja produk lokal, toko online umkm, produk umkm indonesia, cooca marketplace')

@section('content')
<div class="pt-8 sm:pt-12 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-20">

        {{-- ═══ HERO + SEARCH ═══ --}}
        <section class="text-center max-w-3xl mx-auto space-y-5">
            <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                Marketplace UMKM Terverifikasi
            </p>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.08]">
                Belanja Langsung dari <span class="text-[#007AFF] dark:text-[#0A84FF]">UMKM Lokal</span>
            </h1>

            <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto leading-relaxed">
                Temukan {{ number_format($totalProducts) }} produk dari {{ number_format($totalStores) }} toko UMKM terpercaya di seluruh Indonesia.
            </p>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('marketplace.search') }}" class="pt-2 max-w-2xl mx-auto">
                <div class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-[16px] shadow-lg border border-black/[0.06] dark:border-white/[0.08] p-1.5 focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                    <i data-lucide="search" class="w-5 h-5 ml-3.5 text-black/40 dark:text-white/40 shrink-0"></i>
                    <input type="text" name="q"
                        placeholder="Cari produk, toko, atau kategori..."
                        class="w-full bg-transparent border-0 px-3 py-2.5 text-[16px] sm:text-sm text-[#1D1D1F] dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none">
                    <button type="submit"
                        class="shrink-0 px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[44px]">
                        Cari
                    </button>
                </div>
            </form>

            {{-- Auth CTA Row --}}
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                @if (auth('customer')->guest())
                    <a href="{{ route('customer.login') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[44px]">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Masuk Sebagai Pembeli</span>
                    </a>
                @else
                    <a href="{{ route('customer.dashboard') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[44px]">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Dashboard Saya</span>
                    </a>
                @endif

                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-white border border-black/[0.06] dark:border-white/[0.08] text-sm font-semibold hover:bg-[#F2F2F7] dark:hover:bg-[#2C2C2E] active:scale-[0.98] transition min-h-[44px]">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Masuk Sebagai Penjual</span>
                </a>

                @if (auth('web')->guest())
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-sm font-semibold hover:bg-[#34C759]/20 active:scale-[0.98] transition min-h-[44px]">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Buka Toko Gratis</span>
                    </a>
                @endif
            </div>
        </section>

        {{-- ═══ CATEGORY FILTER ═══ --}}
        <section>
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kategori Toko</h2>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">Jelajahi berdasarkan jenis usaha</p>
                </div>
                <a href="{{ route('public.discovery.index') }}" class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                    <span>Semua Toko</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                @foreach ($categories as $key => $cat)
                    <a href="{{ route('marketplace.search', ['kategori' => $key]) }}"
                       class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:shadow-md hover:border-[#007AFF]/30 transition text-center group flex flex-col items-center justify-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF] group-hover:scale-110 transition duration-200">
                            <i data-lucide="{{ $cat['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">{{ $cat['label'] }}</span>
                            <span class="block text-xs text-[#6E6E73] dark:text-[#86868B] tabular-nums mt-0.5">{{ $cat['count'] }} toko</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ═══ POPULAR PRODUCTS ═══ --}}
        @if ($popularProducts->isNotEmpty())
        <section>
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Produk Pilihan</h2>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">Produk dari berbagai toko UMKM</p>
                </div>
                <a href="{{ route('marketplace.search') }}" class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach ($popularProducts as $product)
                    <a href="{{ url('/' . $product->business->slug . '/produk/' . ($product->slug ?: $product->id)) }}"
                       class="group bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden hover:shadow-lg hover:border-[#007AFF]/20 transition">
                        {{-- Product Image --}}
                        <div class="aspect-square bg-[#F2F2F7] dark:bg-[#2C2C2E] overflow-hidden">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-black/20 dark:text-white/20">
                                    <i data-lucide="package" class="w-10 h-10"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Product Info --}}
                        <div class="p-3 sm:p-4 space-y-1.5">
                            <h3 class="font-semibold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] line-clamp-2 leading-snug">{{ $product->name }}</h3>
                            <p class="text-xs text-[#6E6E73] dark:text-[#86868B] line-clamp-1">{{ $product->business->name }}</p>
                            <p class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7] tabular-nums">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ═══ FEATURED STORES ═══ --}}
        @if ($featuredStores->isNotEmpty())
        <section>
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Toko Terbaru</h2>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">UMKM yang baru bergabung di Cooca</p>
                </div>
                <a href="{{ route('public.discovery.index') }}" class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                    <span>Jelajahi Semua</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($featuredStores as $store)
                    <a href="{{ url('/' . $store->slug) }}"
                       class="group bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-5 hover:shadow-lg hover:border-[#007AFF]/20 transition space-y-3">
                        {{-- Store Header --}}
                        <div class="flex items-center gap-3">
                            @if ($store->logo_url || $store->landingPage?->logo_url)
                                <img src="{{ $store->logo_url ?: $store->landingPage?->logo_url }}"
                                     alt="{{ $store->name }}"
                                     class="w-12 h-12 rounded-full object-cover border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-full bg-[#007AFF] flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                    {{ strtoupper(substr($store->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] truncate group-hover:text-[#007AFF] transition">{{ $store->name }}</h3>
                                <p class="text-xs text-[#6E6E73] dark:text-[#86868B] truncate">{{ $store->industry_category ?: 'Toko Online' }}</p>
                            </div>
                        </div>

                        {{-- Store Meta --}}
                        <div class="flex items-center gap-4 text-xs text-[#6E6E73] dark:text-[#86868B]">
                            <span class="flex items-center gap-1 tabular-nums">
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

                        {{-- Store Description --}}
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

        {{-- ═══ CTA SELLER ACQUISITION ═══ --}}
        <section class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-8 sm:p-12 text-center space-y-5">
            <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                Untuk Pemilik Usaha
            </p>
            <h2 class="text-2xl sm:text-3xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                Buka Toko Online Anda di Cooca
            </h2>
            <p class="text-sm text-[#6E6E73] dark:text-[#86868B] max-w-lg mx-auto leading-relaxed">
                Kelola produk, terima pesanan, dan jangkau pelanggan baru. Gratis selamanya untuk UMKM Indonesia.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[48px]">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Daftar Gratis</span>
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] text-[#1D1D1F] dark:text-white border border-black/[0.06] dark:border-white/[0.08] text-sm font-semibold hover:bg-[#F2F2F7] dark:hover:bg-[#3A3A3C] active:scale-[0.98] transition min-h-[48px]">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk Dashboard</span>
                </a>
            </div>
        </section>

    </div>
</div>
@endsection
