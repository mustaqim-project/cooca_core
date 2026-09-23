@extends('layouts.public_marketing')

@section('title', 'Marketplace UMKM Indonesia | Cooca')
@section('description',
    'Belanja produk UMKM lokal terpercaya langsung dari pemilik usaha. Kuliner, busana, kerajinan,
    jasa, dan ribuan produk lainnya.')
@section('keywords',
    'marketplace umkm, belanja produk lokal, toko online umkm, produk umkm indonesia, cooca
    marketplace')

    @push('seo')
        <link rel="canonical" href="{{ route('marketplace.index') }}">
        <meta property="og:title" content="Marketplace UMKM Indonesia | Cooca">
        <meta property="og:description"
            content="Belanja produk UMKM lokal terpercaya langsung dari pemilik usaha tanpa perantara.">
        <meta property="og:url" content="{{ route('marketplace.index') }}">
        <meta property="og:type" content="website">

        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Cooca Marketplace",
      "url": "{{ route('marketplace.index') }}",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ route('marketplace.search') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- Hero Section (Midnight #060B1E Full-Bleed) --}}
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Marketplace UMKM</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {{-- Left: Headline, Search, Actions (7 cols) --}}
                    <div class="lg:col-span-7 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-sm">
                            <i data-lucide="store" class="w-4 h-4"></i>
                            <span>Direktori UMKM Indonesia Terverifikasi</span>
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words">
                            Belanja Langsung dari <span class="text-[#00C4D8]">Pemilik Usaha Lokal.</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl text-pretty break-words">
                            Temukan {{ number_format($totalProducts) }} produk unggulan dari
                            {{ number_format($totalStores) }}
                            toko UMKM binaan di seluruh Indonesia. Transaksi langsung, aman, dan tanpa biaya perantara
                            berlebih.
                        </p>

                        {{-- Search Input Form --}}
                        <form method="GET" action="{{ route('marketplace.search') }}" class="pt-1">
                            <div
                                class="relative flex items-center bg-[#0E1E45]/80 rounded-[18px] border border-white/15 p-1.5 shadow-2xl backdrop-blur-md focus-within:ring-2 focus-within:ring-[#00C4D8] transition">
                                <i data-lucide="search" class="w-5 h-5 ml-3.5 text-slate-400 shrink-0"></i>
                                <input type="text" name="q" placeholder="Cari nama produk, toko, atau kota..."
                                    class="w-full bg-transparent border-0 px-3.5 py-3 text-[16px] text-white placeholder-slate-400 focus:outline-none">
                                <button type="submit"
                                    class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition">
                                    Cari
                                </button>
                            </div>
                        </form>

                        {{-- Auth / Action Row --}}
                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            @if (auth('customer')->guest())
                                <a href="{{ route('customer.login') }}"
                                    class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 transition active:scale-[0.98]">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                    <span>Masuk Sebagai Pembeli</span>
                                </a>
                            @else
                                <a href="{{ route('customer.dashboard') }}"
                                    class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 transition active:scale-[0.98]">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    <span>Dashboard Saya</span>
                                </a>
                            @endif

                            <a href="{{ route('login') }}"
                                class="h-11 px-5 rounded-[12px] bg-white/10 hover:bg-white/15 text-white border border-white/15 text-xs sm:text-sm font-semibold transition active:scale-[0.98] flex items-center gap-2 backdrop-blur-sm">
                                <i data-lucide="store" class="w-4 h-4"></i>
                                <span>Masuk Toko</span>
                            </a>

                            @if (auth('web')->guest())
                                <a href="{{ route('register') }}"
                                    class="h-11 px-5 rounded-[12px] bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm font-semibold hover:bg-emerald-500/25 transition active:scale-[0.98] flex items-center gap-2">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span>Buka Toko Gratis</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Verified Marketplace Ecosystem Preview (5 cols) --}}
                    <div class="lg:col-span-5">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-5 sm:p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-4">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                    <span class="text-xs font-mono font-semibold text-slate-300 ml-2">Katalog UMKM
                                        Terverifikasi</span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <i data-lucide="badge-check" class="w-3 h-3"></i> Terkurasi
                                </span>
                            </div>

                            {{-- Highlighted Merchant Card Simulation --}}
                            <div class="p-4 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-11 h-11 rounded-[12px] bg-[#007AFF] text-white font-bold text-sm flex items-center justify-center shadow-md shadow-[#007AFF]/25">
                                        UM
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-sm text-white truncate">Sentra Kopi Nusantara</span>
                                            <i data-lucide="badge-check" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                        </div>
                                        <div class="text-xs text-slate-400">Kab. Bandung • Toko Aktif</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-[#060B1E]/80 border border-white/10 flex items-center justify-between text-xs">
                                    <div class="space-y-0.5">
                                        <span class="text-slate-400">Produk Unggulan</span>
                                        <div class="font-semibold text-white">Kopi Arabika Ciwidey (250g)</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-sm text-[#00C4D8] font-mono">Rp 65.000</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Trust Points Row --}}
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-[12px] bg-white/5 border border-white/10 flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                                    <span class="text-slate-200 font-medium">Bebas Penipuan</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-white/5 border border-white/10 flex items-center gap-2">
                                    <i data-lucide="truck" class="w-4 h-4 text-[#00C4D8]"></i>
                                    <span class="text-slate-200 font-medium">Kirim Seluruh RI</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Content Body with Light/Dark Mode --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-20 sm:space-y-28">

            {{-- Category Grid --}}
            <section class="space-y-6">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200/80 dark:border-white/10 pb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Kategori Usaha</h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-0.5">Temukan produk berdasarkan
                            bidang bisnis</p>
                    </div>
                    <a href="{{ route('public.discovery.index') }}"
                        class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline flex items-center gap-1">
                        <span>Lihat Semua Toko</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($categories as $key => $cat)
                        <a href="{{ route('marketplace.search', ['kategori' => $key]) }}"
                            class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-center gap-3 shadow-sm">
                            <div
                                class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                                <i data-lucide="{{ $cat['icon'] }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span
                                    class="font-bold text-sm text-slate-900 dark:text-white block group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">{{ $cat['label'] }}</span>
                                <span
                                    class="text-xs text-slate-600 dark:text-slate-300 tabular-nums mt-0.5 block">{{ $cat['count'] }}
                                    toko</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Popular Products Section --}}
            @if ($popularProducts->isNotEmpty())
                <section class="space-y-6">
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-200/80 dark:border-white/10 pb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Produk Pilihan</h2>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-0.5">Rekomendasi dari gerai
                                UMKM terverifikasi</p>
                        </div>
                        <a href="{{ route('marketplace.search') }}"
                            class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline flex items-center gap-1">
                            <span>Semua Produk</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($popularProducts as $product)
                            <a href="{{ url('/' . $product->business->slug . '/produk/' . ($product->slug ?: $product->id)) }}"
                                class="group bg-white dark:bg-[#0E172F]/70 rounded-[20px] border border-slate-200/80 dark:border-white/10 overflow-hidden hover:border-[#007AFF]/40 hover:shadow-md transition shadow-sm flex flex-col justify-between">
                                <div>
                                    <div
                                        class="aspect-square bg-slate-100 dark:bg-[#060B1E] overflow-hidden flex items-center justify-center">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-600">
                                                <i data-lucide="package" class="w-10 h-10"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-4 space-y-1.5">
                                        <h3
                                            class="font-bold text-sm text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition">
                                            {{ $product->name }}
                                        </h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-1">
                                            {{ $product->business->name }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-4 pt-0">
                                    <p
                                        class="font-extrabold text-base text-[#007AFF] dark:text-[#00C4D8] tabular-nums font-mono">
                                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Featured Stores Section --}}
            @if ($featuredStores->isNotEmpty())
                <section class="space-y-6">
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-200/80 dark:border-white/10 pb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Toko Terdaftar</h2>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-0.5">Jelajahi profil lengkap
                                usaha UMKM</p>
                        </div>
                        <a href="{{ route('public.discovery.index') }}"
                            class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline flex items-center gap-1">
                            <span>Semua Direktori Toko</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($featuredStores as $store)
                            <a href="{{ url('/' . $store->slug) }}"
                                class="group bg-white dark:bg-[#0E172F]/70 rounded-[20px] border border-slate-200/80 dark:border-white/10 p-5 hover:border-[#007AFF]/40 hover:shadow-md transition space-y-3 shadow-sm">
                                <div class="flex items-center gap-3">
                                    @if ($store->logo_url || $store->landingPage?->logo_url)
                                        <img src="{{ $store->logo_url ?: $store->landingPage?->logo_url }}"
                                            alt="{{ $store->name }}"
                                            class="w-12 h-12 rounded-[14px] object-cover border border-slate-200/80 dark:border-white/10">
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-[14px] bg-[#007AFF] flex items-center justify-center text-white font-bold text-sm shadow-md shadow-[#007AFF]/25">
                                            {{ strtoupper(substr($store->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h3
                                            class="font-bold text-sm text-slate-900 dark:text-white truncate group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition">
                                            {{ $store->name }}</h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 truncate">
                                            {{ $store->industry_category ?: 'Toko Online' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-xs text-slate-600 dark:text-slate-300">
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
                                    <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                        {{ \Illuminate\Support\Str::limit($store->landingPage->subheadline, 80) }}
                                    </p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Seller Acquisition Banner (Midnight #060B1E Card) --}}
            <section
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-6">
                <div
                    class="absolute -right-20 -top-20 w-80 h-80 bg-[#007AFF]/15 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-4 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5">
                        <i data-lucide="store" class="w-4 h-4 text-emerald-400"></i>
                        <span>Untuk Pengusaha &amp; Pemilik Toko</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Buka Katalog Toko Online Anda Sendiri di COOCA
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto leading-relaxed">
                        Kelola stok toko, catat penjualan kasir, dan terima pesanan online langsung tanpa potongan komisi
                        per
                        transaksi. 100% gratis selamanya.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3.5 pt-3">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition min-h-[48px]">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Daftar Buka Toko Gratis</span>
                        </a>
                        <a href="{{ route('login') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white border border-white/15 text-sm font-semibold transition active:scale-95 flex items-center gap-2 min-h-[48px]">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            <span>Masuk Dashboard Kasir</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
