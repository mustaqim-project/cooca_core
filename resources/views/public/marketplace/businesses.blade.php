@extends('layouts.public_marketing')

@section('title', 'Direktori Toko & Bisnis UMKM Indonesia Terverifikasi | COOCA Marketplace')
@section('description',
    'Temukan ribuan toko online, bengkel, kafe, dan produsen UMKM lokal terverifikasi di seluruh
    Indonesia. Transaksi langsung ke pemilik usaha tanpa perantara.')
@section('keywords',
    'direktori toko umkm, daftar bisnis lokal indonesia, toko online terverifikasi, cari toko umkm,
    marketplace toko lokal cooca')

    @push('seo')
        <link rel="canonical" href="{{ route('marketplace.sub.businesses') }}">
        <meta property="og:title" content="Direktori Toko & Bisnis UMKM Indonesia Terverifikasi | COOCA Marketplace">
        <meta property="og:description"
            content="Jelajahi profil lengkap toko dan gerai fisik UMKM di seluruh Indonesia. Beli langsung dari produsen lokal terpercaya.">
        <meta property="og:url" content="{{ route('marketplace.sub.businesses') }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Direktori Toko & Bisnis UMKM Indonesia Terverifikasi | COOCA Marketplace">
        <meta name="twitter:description"
            content="Temukan ribuan bisnis UMKM binaan COOCA dengan katalog produk aktif dan transaksi terverifikasi.">

        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": "Direktori Bisnis UMKM COOCA",
      "description": "Direktori bisnis dan toko online UMKM terverifikasi di seluruh Indonesia.",
      "url": "{{ route('marketplace.sub.businesses') }}"
    }
    </script>
    @endpush

    @php
        $stores = \App\Models\Business::where('is_active', true)
            ->whereHas('storeSetting', function ($q): void {
                $q->where('is_storefront_enabled', true)->where('is_discoverable', true);
            })
            ->with(['storeSetting', 'landingPage'])
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->orderByDesc('products_count')
            ->paginate(18);
    @endphp

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

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <a href="{{ route('marketplace.index') }}" class="hover:text-white transition-colors">Marketplace</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Direktori Bisnis</span>
                </nav>

                {{-- Header & Search Bar --}}
                <div class="max-w-3xl space-y-4">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-sm">
                        <i data-lucide="store" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Direktori Toko Terverifikasi</span>
                    </div>

                    <h1
                        class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-white tracking-tight leading-[1.15]">
                        Temukan Bisnis & <span class="text-[#00C4D8]">UMKM Lokal Terpercaya</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                        Jelajahi profil lengkap toko ritel, kedai kopi, bengkel, konveksi, dan penyedia jasa lokal yang
                        mengelola operasionalnya secara profesional menggunakan ekosistem COOCA.
                    </p>

                    {{-- Quick Search Form --}}
                    <form method="GET" action="{{ route('marketplace.search') }}" class="pt-2">
                        <div
                            class="relative flex items-center bg-[#0E1E45]/80 rounded-[18px] border border-white/15 p-1.5 shadow-2xl backdrop-blur-md focus-within:ring-2 focus-within:ring-[#00C4D8] transition">
                            <i data-lucide="search" class="w-5 h-5 ml-3.5 text-slate-400 shrink-0" aria-hidden="true"></i>
                            <input type="text" name="q" placeholder="Cari nama toko, brand, atau jenis usaha..."
                                class="w-full bg-transparent border-0 px-3.5 py-3 text-sm sm:text-base text-white placeholder-slate-400 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition">
                                Cari Toko
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- Main Content Directory --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-12 sm:space-y-16">

            {{-- Category Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-semibold">
                <a href="{{ route('marketplace.sub.businesses') }}"
                    class="px-4 py-2 rounded-full bg-[#007AFF] text-white shadow-sm shrink-0 transition">
                    Semua Kategori
                </a>
                <a href="{{ route('marketplace.search', ['kategori' => 'fnb']) }}"
                    class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition flex items-center gap-1.5">
                    <i data-lucide="utensils" class="w-3.5 h-3.5 text-amber-500" aria-hidden="true"></i>
                    <span>Kuliner & F&B</span>
                </a>
                <a href="{{ route('marketplace.search', ['kategori' => 'retail']) }}"
                    class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition flex items-center gap-1.5">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-blue-500" aria-hidden="true"></i>
                    <span>Retail & Toko</span>
                </a>
                <a href="{{ route('marketplace.search', ['kategori' => 'workshop']) }}"
                    class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition flex items-center gap-1.5">
                    <i data-lucide="wrench" class="w-3.5 h-3.5 text-slate-500" aria-hidden="true"></i>
                    <span>Bengkel & Servis</span>
                </a>
                <a href="{{ route('marketplace.search', ['kategori' => 'laundry']) }}"
                    class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition flex items-center gap-1.5">
                    <i data-lucide="droplets" class="w-3.5 h-3.5 text-cyan-500" aria-hidden="true"></i>
                    <span>Laundry</span>
                </a>
                <a href="{{ route('marketplace.search', ['kategori' => 'manufacture']) }}"
                    class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition flex items-center gap-1.5">
                    <i data-lucide="factory" class="w-3.5 h-3.5 text-indigo-500" aria-hidden="true"></i>
                    <span>Produsen & Pabrik</span>
                </a>
                <a href="{{ route('marketplace.search', ['kategori' => 'service']) }}"
                    class="px-4 py-2 rounded-full bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:border-[#007AFF]/40 shrink-0 transition flex items-center gap-1.5">
                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-violet-500" aria-hidden="true"></i>
                    <span>Jasa Profesional</span>
                </a>
            </div>

            {{-- Store Directory Grid --}}
            @if ($stores->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($stores as $store)
                        <div
                            class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-4 flex flex-col justify-between group">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] font-bold text-base flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($store->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h3
                                                class="font-bold text-base text-slate-900 dark:text-white truncate group-hover:text-[#007AFF] transition">
                                                {{ $store->name }}
                                            </h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                {{ $store->storeSetting->city ?? 'Indonesia' }} •
                                                {{ $store->industry_category ?? 'UMKM' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-semibold shrink-0">
                                        <i data-lucide="badge-check" class="w-3 h-3" aria-hidden="true"></i>
                                        <span>Aktif</span>
                                    </span>
                                </div>

                                <p
                                    class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
                                    {{ $store->landingPage->subheadline ?? ($store->description ?? 'Toko resmi UMKM binaan dengan transaksi langsung dan terverifikasi.') }}
                                </p>
                            </div>

                            <div
                                class="pt-4 border-t border-slate-100 dark:border-white/10 flex items-center justify-between text-xs">
                                <span class="font-mono text-slate-500 dark:text-slate-400">
                                    <strong class="text-slate-900 dark:text-white">{{ $store->products_count }}</strong>
                                    Produk Aktif
                                </span>
                                <a href="{{ url('/' . ($store->slug ?? 'toko')) }}"
                                    class="font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline flex items-center gap-1">
                                    <span>Kunjungi Toko</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="pt-4">
                    {{ $stores->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div
                    class="p-12 text-center rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 max-w-xl mx-auto shadow-sm">
                    <div
                        class="w-12 h-12 rounded-[14px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center mx-auto">
                        <i data-lucide="store" class="w-6 h-6" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Belum Ada Toko Terdaftar</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Jadilah salah satu bisnis pertama yang membuka etalase toko online gratis di marketplace
                        terintegrasi COOCA.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('register') }}"
                            class="h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold inline-flex items-center gap-2 shadow-sm transition">
                            <span>Daftarkan Bisnis Anda</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Verification Standards Bento --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-6 shadow-sm">
                <div class="max-w-2xl">
                    <span
                        class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Keamanan
                        & Keaslian</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">Standar Verifikasi Toko
                        di COOCA Marketplace</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <div
                            class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Operasional Riil</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Setiap toko memiliki
                            aktivitas bisnis nyata yang menggunakan modul kasir atau inventaris COOCA.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="boxes" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Stok Terjamin Nyata</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Etalase toko terhubung
                            langsung ke gudang fisik penjual sehingga risiko barang habis ditekan ke nol.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <div
                            class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Kontak Langsung WhatsApp</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Pembeli dapat berkomunikasi
                            dan berkonsultasi langsung ke pemilik usaha tanpa perantara berbelit.</p>
                    </div>
                </div>
            </section>

            {{-- Final CTA --}}
            <section
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-5">
                <div
                    class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                        <i data-lucide="store" class="w-4 h-4 text-[#00C4D8]" aria-hidden="true"></i>
                        <span>Punya Usaha Sendiri? Buka Etalase Online Gratis</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Tampilkan Toko Anda di Direktori Marketplace COOCA
                    </h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Daftar akun COOCA hari ini dan aktifkan fitur storefront. Produk Anda langsung dapat diakses dan
                        dibeli oleh pelanggan di seluruh Indonesia.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Buka Toko Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('marketplace.index') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm">
                            <span>Kembali ke Beranda Marketplace</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
