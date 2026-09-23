@extends('layouts.public_marketing')

@section('title', 'Jelajahi Kategori Bisnis & Produk UMKM | COOCA Marketplace')
@section('description', 'Temukan produk dan layanan UMKM berdasarkan kategori industri: Kuliner & Kafe, Retail, Bengkel Otomotif, Laundry, Manufaktur Pabrikasi, dan Jasa Profesional.')
@section('keywords', 'kategori bisnis umkm, katalog industri umkm, produk kuliner fashion kerajinan jasa, direktori kategori umkm')

@push('seo')
    <link rel="canonical" href="{{ route('marketplace.sub.categories') }}">
    <meta property="og:title" content="Jelajahi Kategori Bisnis & Produk UMKM | COOCA Marketplace">
    <meta property="og:description" content="Navigasi terstruktur seluruh sektor usaha UMKM binaan COOCA di Indonesia.">
    <meta property="og:url" content="{{ route('marketplace.sub.categories') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Jelajahi Kategori Bisnis & Produk UMKM | COOCA Marketplace">
    <meta name="twitter:description" content="Katalog kategori industri UMKM terlengkap untuk kemudahan belanja produk lokal.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": "Kategori Marketplace UMKM COOCA",
      "description": "Klasifikasi industri dan kategori produk UMKM Indonesia.",
      "url": "{{ route('marketplace.sub.categories') }}"
    }
    </script>
@endpush

@php
    $baseScope = fn ($q) => $q->where('is_active', true)
        ->whereHas('storeSetting', fn ($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));

    $categoriesData = [
        'fnb' => [
            'title' => 'Kuliner & F&B',
            'desc' => 'Kedai kopi, kafe, makanan beku (frozen food), camilan khas daerah, bumbu olahan, dan katering acara.',
            'icon' => 'utensils',
            'color' => 'amber',
            'count' => \App\Models\Business::where($baseScope)->where(fn ($q) => $q->where('industry_category', 'fnb')->orWhere('template_code', 'like', 'fnb%'))->count(),
            'tags' => ['Kopi Arabika', 'Frozen Food', 'Kue Basah', 'Sambal Kemasan', 'Katering Sehat']
        ],
        'retail' => [
            'title' => 'Retail & Swalayan',
            'desc' => 'Toko kelontong modern, minimarket sembako, butik pakaian, hijab, batik tradisional, dan perlengkapan harian.',
            'icon' => 'store',
            'color' => 'blue',
            'count' => \App\Models\Business::where($baseScope)->where(fn ($q) => $q->where('industry_category', 'retail')->orWhere('template_code', 'like', 'retail%'))->count(),
            'tags' => ['Pakaian Muslim', 'Batik Tulis', 'Sembako Grosir', 'Kosmetik BPOM', 'Alat Tulis']
        ],
        'workshop' => [
            'title' => 'Bengkel & Otomotif',
            'desc' => 'Suku cadang motor dan mobil, pelumas oli mesin, aksesoris variasi, ban, dan layanan perbaikan kendaraan.',
            'icon' => 'wrench',
            'color' => 'slate',
            'count' => \App\Models\Business::where($baseScope)->where(fn ($q) => $q->where('industry_category', 'workshop')->orWhere('template_code', 'like', 'workshop%')->orWhere('template_code', 'like', 'bengkel%'))->count(),
            'tags' => ['Oli Mesin', 'Kampas Rem', 'Ban Tubeless', 'Aksesoris Motor', 'Servis Berkala']
        ],
        'laundry' => [
            'title' => 'Laundry & Kebersihan',
            'desc' => 'Layanan cuci kiloan, dry cleaning pakaian formal, laundry sepatu, helm, bed cover, dan perlengkapan deterjen.',
            'icon' => 'droplets',
            'color' => 'cyan',
            'count' => \App\Models\Business::where($baseScope)->where(fn ($q) => $q->where('industry_category', 'laundry')->orWhere('template_code', 'like', 'laundry%'))->count(),
            'tags' => ['Cuci Kiloan', 'Dry Cleaning Jas', 'Laundry Sepatu', 'Cuci Karpet', 'Parfum Laundry']
        ],
        'manufacture' => [
            'title' => 'Produsen & Pabrikasi',
            'desc' => 'Konveksi garmen pakaian, pengrajin mebel kayu, produk anyaman lokal, kemasan produk, dan industri kreatif rumahan.',
            'icon' => 'factory',
            'color' => 'indigo',
            'count' => \App\Models\Business::where($baseScope)->where(fn ($q) => $q->where('industry_category', 'manufacture')->orWhere('template_code', 'like', 'mfg%'))->count(),
            'tags' => ['Konveksi Kaos', 'Mebel Jepara', 'Kerajinan Kulit', 'Dus Kemasan', 'Pabrik Snack']
        ],
        'service' => [
            'title' => 'Jasa & Servis Profesional',
            'desc' => 'Jasa servis AC rumah/kantor, barbershop, salon kecantikan, studio foto, desainer grafis, dan konsultasi legal usaha.',
            'icon' => 'briefcase',
            'color' => 'violet',
            'count' => \App\Models\Business::where($baseScope)->where(fn ($q) => $q->where('industry_category', 'service')->orWhere('template_code', 'like', 'service%'))->count(),
            'tags' => ['Servis AC', 'Barbershop Pria', 'Studio Foto', 'Perawatan Wajah', 'Jasa Desain']
        ],
    ];
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
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Kategori Usaha</span>
            </nav>

            {{-- Header --}}
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5" aria-hidden="true"></i>
                    <span>Klasifikasi Sektor Bisnis</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                    Jelajahi Berbagai Kategori Produk & Layanan Bisnis
                </h1>

                <p class="text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    Setiap toko dan produk di COOCA dikelompokkan secara terstruktur berdasarkan sektor industri, memudahkan Anda menemukan barang kebutuhan harian maupun layanan profesional terdekat.
                </p>
            </div>

            {{-- Category Bento Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($categoriesData as $key => $cat)
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-5 flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="{{ $cat['icon'] }}" class="w-6 h-6" aria-hidden="true"></i>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-xs font-mono font-semibold text-[#6E6E73] dark:text-[#86868B]">
                                    {{ $cat['count'] }} Toko Terdaftar
                                </span>
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">
                                    {{ $cat['title'] }}
                                </h3>
                                <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                                    {{ $cat['desc'] }}
                                </p>
                            </div>

                            {{-- Popular Tags --}}
                            <div class="flex flex-wrap gap-1.5 pt-2">
                                @foreach ($cat['tags'] as $tag)
                                    <span class="px-2.5 py-1 rounded-lg bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-100 dark:border-neutral-800 text-[11px] text-[#6E6E73] dark:text-[#86868B]">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80">
                            <a href="{{ route('marketplace.search', ['kategori' => $key]) }}" class="w-full h-11 rounded-[12px] bg-neutral-100 dark:bg-neutral-800 hover:bg-[#007AFF] hover:text-white dark:hover:bg-[#007AFF] text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-center gap-2 transition">
                                <span>Lihat Produk {{ $cat['title'] }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Related Links --}}
            <section class="border-t border-neutral-200/80 dark:border-neutral-800 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kanal Direktori Lainnya</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ route('marketplace.sub.businesses') }}" class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-1 group">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Direktori Toko</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Semua Toko Terverifikasi</h4>
                    </a>
                    <a href="{{ route('marketplace.sub.products') }}" class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-1 group">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Katalog Produk</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Semua Produk Unggulan</h4>
                    </a>
                    <a href="{{ route('marketplace.sub.locations') }}" class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-1 group">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Wilayah Kota</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Cari Berdasarkan Kota</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="store" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Tingkatkan Visibilitas Bisnis Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Daftarkan Usaha Anda ke Kategori yang Sesuai
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Dapatkan calon pelanggan baru yang mencari produk atau layanan di industri Anda setiap hari melalui jaringan marketplace COOCA.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Buka Toko Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Beranda Marketplace</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
