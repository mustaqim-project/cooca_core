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
      "@@context": "https://schema.org",
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
<div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

    {{-- Hero Section (Midnight #060B1E Full-Bleed) --}}
    <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
        {{-- Dual Ambient Glows --}}
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-white/20">/</span>
                <a href="{{ route('marketplace.index') }}" class="hover:text-white transition-colors">Marketplace</a>
                <span aria-hidden="true" class="text-white/20">/</span>
                <span class="text-[#00C4D8] font-semibold" aria-current="page">Katalog Produk</span>
            </nav>

            {{-- Header & Search Bar --}}
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-sm">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5" aria-hidden="true"></i>
                    <span>Katalog Produk Asli UMKM</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
                    Katalog Produk Pilihan Langsung dari <span class="text-[#00C4D8]">Produsen & Penjual</span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Dukung produk lokal berkualitas. Setiap transaksi terhubung langsung ke persediaan inventaris gerai penjual, menjamin stok selalu siap kirim atau siap diambil di tempat.
                </p>

                {{-- Search Form --}}
                <form method="GET" action="{{ route('marketplace.search') }}" class="pt-2">
                    <div class="relative flex items-center bg-[#0E1E45]/80 rounded-[18px] border border-white/15 p-1.5 shadow-2xl backdrop-blur-md focus-within:ring-2 focus-within:ring-[#00C4D8] transition">
                        <i data-lucide="search" class="w-5 h-5 ml-3.5 text-slate-400 shrink-0" aria-hidden="true"></i>
                        <input type="text" name="q" placeholder="Cari nama barang, kuliner, pakaian, atau jasa..." class="w-full bg-transparent border-0 px-3.5 py-3 text-sm sm:text-base text-white placeholder-slate-400 focus:outline-none">
                        <button type="submit" class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition">
                            Cari Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Main Content Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-12 sm:space-y-16">

        {{-- Quick Filter Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-semibold">
            <a href="{{ route('marketplace.sub.products') }}" class="px-4 py-2 rounded-full bg-[#007AFF] text-white shadow-sm shrink-0 transition">
                Semua Produk
            </a>
            <a href="{{ route('marketplace.search', ['harga' => 'murah']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition">
                Di Bawah Rp 50rb
            </a>
            <a href="{{ route('marketplace.search', ['harga' => 'sedang']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition">
                Rp 50rb - Rp 200rb
            </a>
            <a href="{{ route('marketplace.search', ['harga' => 'mahal']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition">
                Di Atas Rp 200rb
            </a>
            <a href="{{ route('marketplace.search', ['tipe' => 'goods']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition">
                Barang Fisik
            </a>
            <a href="{{ route('marketplace.search', ['tipe' => 'service']) }}" class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition">
                Layanan Jasa
            </a>
        </div>

        {{-- Products Grid --}}
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($products as $product)
                    <a href="{{ url('/' . ($product->business->slug ?? 'toko') . '/produk/' . ($product->slug ?: $product->id)) }}" class="group bg-white dark:bg-[#0E172F]/70 rounded-[20px] border border-slate-200/80 dark:border-white/10 overflow-hidden hover:border-[#007AFF]/40 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 shadow-sm flex flex-col justify-between text-slate-900 dark:text-white">
                        <div>
                            <div class="aspect-square bg-slate-100 dark:bg-[#070A14] overflow-hidden flex items-center justify-center relative">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-600">
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
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-[#007AFF] transition">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                                    {{ $product->business->name ?? 'Toko Lokal' }}
                                </p>
                            </div>
                        </div>

                        <div class="p-4 pt-0 flex items-center justify-between">
                            <span class="font-mono font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                                Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] text-[#007AFF] dark:text-[#00C4D8] font-semibold group-hover:underline">Beli</span>
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
            <div class="p-12 text-center rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 max-w-xl mx-auto shadow-sm">
                <div class="w-12 h-12 rounded-[14px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                    <i data-lucide="package-search" class="w-6 h-6" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Belum Ada Produk Ditampilkan</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Saat ini etalase produk publik sedang diperbarui oleh penjual. Anda juga dapat mendaftarkan produk usaha Anda sendiri.
                </p>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition">
                        <span>Mulai Jual Produk</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        @endif

        {{-- Product Guarantee Bento --}}
        <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-6 shadow-sm">
            <div class="max-w-2xl">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Jaminan Berbelanja</span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">Mengapa Berbelanja di COOCA Marketplace?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                    <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Stok Kasir Sinkron</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Persediaan produk selalu real-time karena langsung tersambung ke sistem POS kasir penjual.</p>
                </div>

                <div class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                    <div class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="handshake" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Harga Langsung Produsen</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Tanpa biaya perantara yang mencekik, Anda mendapatkan harga terbaik langsung dari produsen lokal.</p>
                </div>

                <div class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                    <div class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <i data-lucide="qr-code" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Pembayaran Mudah QRIS</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Dukungan pembayaran QRIS nasional, transfer bank, dan konfirmasi langsung ke nomor WhatsApp toko.</p>
                </div>
            </div>
        </section>

        {{-- Final CTA --}}
        <section class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-5">
            <div class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#00C4D8]" aria-hidden="true"></i>
                    <span>Dukung Produk Buatan Indonesia</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Mulai Belanja atau Daftarkan Produk Usaha Anda
                </h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Setiap pembelian yang Anda lakukan membantu perputaran modal pelaku usaha mikro, kecil, dan menengah di tanah air.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('marketplace.search') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                        <span>Cari Produk Spesifik</span>
                        <i data-lucide="search" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm">
                        <span>Beranda Marketplace</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
