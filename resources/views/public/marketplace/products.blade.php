@extends('layouts.public_marketing')

@section('title', 'Katalog Produk Unggulan UMKM Lokal Indonesia | COOCA Marketplace')
@section('description', 'Jelajahi ribuan produk kuliner, pakaian, kerajinan, suku cadang, dan jasa dari produsen dan UMKM binaan COOCA di seluruh Indonesia. Stok real-time dan harga terbaik.')
@section('keywords', 'katalog produk umkm, beli produk lokal indonesia, marketplace umkm terpercaya, produk toko online lokal, belanja produk binaan umkm')

@push('seo')
    <link rel="canonical" href="{{ route('marketplace.sub.products') }}">
    <meta property="og:title" content="Katalog Produk Unggulan UMKM Lokal Indonesia | COOCA Marketplace">
    <meta property="og:description" content="Temukan produk pilihan dari ribuan UMKM Indonesia. Belanja langsung dari pemilik usaha dengan stok nyata terhubung ke kasir.">
    <meta property="og:url" content="{{ route('marketplace.sub.products') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Katalog Produk Unggulan UMKM Lokal Indonesia | COOCA Marketplace">
    <meta name="twitter:description" content="Ribuan produk lokal berkualitas dengan harga bersaing langsung dari produsen UMKM terverifikasi.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": "Katalog Produk UMKM COOCA",
      "description": "Katalog produk dan etalase barang/jasa UMKM Indonesia terhubung dengan stok kasir real-time.",
      "url": "{{ route('marketplace.sub.products') }}"
    }
    </script>
@endpush

@php
    $products = \App\Models\Product::where('is_active', true)
        ->where('show_in_website', true)
        ->whereHas('business', function ($q): void {
            $q->where('is_active', true)
              ->whereHas('storeSetting', function ($sq): void {
                  $sq->where('is_storefront_enabled', true)
                    ->where('is_discoverable', true);
              });
        })
        ->with(['business.storeSetting', 'category'])
        ->latest()
        ->paginate(24);
@endphp

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('marketplace.index') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Marketplace</a>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Katalog Produk</span>
            </nav>

            {{-- Header & Search Bar --}}
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 dark:bg-emerald-400/15 border border-emerald-500/20 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5" aria-hidden="true"></i>
                    <span>Katalog Produk Asli UMKM</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                    Katalog Produk Pilihan Langsung dari Produsen & Penjual
                </h1>

                <p class="text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    Dukung produk lokal berkualitas. Setiap transaksi terhubung langsung ke persediaan inventaris gerai penjual, menjamin stok selalu siap kirim atau siap diambil di tempat.
                </p>

                {{-- Search Form --}}
                <form method="GET" action="{{ route('marketplace.search') }}" class="pt-2">
                    <div class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-neutral-200/80 dark:border-neutral-800 p-1.5 shadow-sm focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                        <i data-lucide="search" class="w-5 h-5 ml-3.5 text-[#6E6E73] dark:text-[#86868B] shrink-0" aria-hidden="true"></i>
                        <input type="text" name="q" placeholder="Cari nama barang, kuliner, pakaian, atau jasa..." class="w-full bg-transparent border-0 px-3.5 py-3 text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7] placeholder-[#6E6E73]/50 focus:outline-none">
                        <button type="submit" class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-sm font-semibold shadow-sm active:scale-[0.98] transition">
                            Cari Produk
                        </button>
                    </div>
                </form>
            </div>

            {{-- Quick Filter Chips --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-semibold">
                <a href="{{ route('marketplace.sub.products') }}" class="px-4 py-2 rounded-full bg-[#1D1D1F] text-white dark:bg-white dark:text-black shrink-0 transition">
                    Semua Produk
                </a>
                <a href="{{ route('marketplace.search', ['harga' => 'murah']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:border-[#007AFF]/40 shrink-0 transition">
                    Di Bawah Rp 50rb
                </a>
                <a href="{{ route('marketplace.search', ['harga' => 'sedang']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:border-[#007AFF]/40 shrink-0 transition">
                    Rp 50rb - Rp 200rb
                </a>
                <a href="{{ route('marketplace.search', ['harga' => 'mahal']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:border-[#007AFF]/40 shrink-0 transition">
                    Di Atas Rp 200rb
                </a>
                <a href="{{ route('marketplace.search', ['tipe' => 'goods']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:border-[#007AFF]/40 shrink-0 transition">
                    Barang Fisik
                </a>
                <a href="{{ route('marketplace.search', ['tipe' => 'service']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:border-[#007AFF]/40 shrink-0 transition">
                    Layanan Jasa
                </a>
            </div>

            {{-- Products Grid --}}
            @if ($products->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach ($products as $product)
                        <a href="{{ url('/' . ($product->business->slug ?? 'toko') . '/produk/' . ($product->slug ?: $product->id)) }}" class="group bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-neutral-200/80 dark:border-neutral-800 overflow-hidden hover:border-[#007AFF]/40 transition shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="aspect-square bg-neutral-100 dark:bg-neutral-800/50 overflow-hidden flex items-center justify-center relative">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                            <i data-lucide="package" class="w-10 h-10" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                    
                                    @if ($product->category)
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-black/60 text-white text-[10px] font-semibold backdrop-blur-sm">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="p-4 space-y-1.5">
                                    <h3 class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7] line-clamp-2 leading-snug group-hover:text-[#007AFF] transition">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] line-clamp-1">
                                        {{ $product->business->name ?? 'Toko Lokal' }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-4 pt-0 flex items-center justify-between">
                                <span class="font-mono font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-[#007AFF] font-semibold group-hover:underline">Beli</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="pt-4">
                    {{ $products->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="p-12 text-center rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-4 max-w-xl mx-auto shadow-sm">
                    <div class="w-12 h-12 rounded-[14px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center mx-auto">
                        <i data-lucide="package-search" class="w-6 h-6" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Belum Ada Produk Ditampilkan</h3>
                    <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Saat ini etalase produk publik sedang diperbarui oleh penjual. Anda juga dapat mendaftarkan produk usaha Anda sendiri.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('register') }}" class="h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition">
                            <span>Mulai Jual Produk</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Product Guarantee Bento --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-6 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Jaminan Berbelanja</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">Mengapa Berbelanja di COOCA Marketplace?</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Stok Kasir Sinkron</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Persediaan produk selalu real-time karena langsung tersambung ke sistem POS kasir penjual.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="handshake" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Harga Langsung Produsen</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Tanpa biaya perantara yang mencekik, Anda mendapatkan harga terbaik langsung dari produsen lokal.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pembayaran Mudah QRIS</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Dukungan pembayaran QRIS nasional, transfer bank, dan konfirmasi langsung ke nomor WhatsApp toko.</p>
                    </div>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Dukung Produk Buatan Indonesia</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Mulai Belanja atau Daftarkan Produk Usaha Anda
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Setiap pembelian yang Anda lakukan membantu perputaran modal pelaku usaha mikro, kecil, dan menengah di tanah air.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('marketplace.search') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Cari Produk Spesifik</span>
                        <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Beranda Marketplace</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
