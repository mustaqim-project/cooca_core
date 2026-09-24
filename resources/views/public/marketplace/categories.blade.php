@extends('layouts.public_marketing')

@section('title', 'Jelajahi Kategori Bisnis & Produk UMKM | COOCA Marketplace')
@section('description', 'Temukan produk dan layanan UMKM berdasarkan kategori industri: Kuliner & Kafe, Retail, Bengkel Otomotif, Laundry, Manufaktur Pabrikasi, dan Jasa Profesional.')
@section('keywords', 'kategori bisnis umkm, katalog industri umkm, produk kuliner fashion kerajinan jasa, direktori kategori umkm')
@section('canonical', route('marketplace.sub.categories'))
@section('og_title', 'Jelajahi Kategori Bisnis & Produk UMKM | COOCA Marketplace')
@section('og_description', 'Navigasi terstruktur seluruh sektor usaha UMKM binaan COOCA di Indonesia.')
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Kategori Marketplace UMKM COOCA",
  "description": "Klasifikasi industri dan kategori produk UMKM Indonesia.",
  "url": "{{ route('marketplace.sub.categories') }}"
}
</script>
@endpush

@php
    $baseScope = fn($q) => $q
        ->where('is_active', true)
        ->whereHas(
            'storeSetting',
            fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true),
        );

    $categoriesData = [
        'fnb' => [
            'title' => 'Kuliner & F&B',
            'desc' =>
                'Kedai kopi, kafe, makanan beku (frozen food), camilan khas daerah, bumbu olahan, dan katering acara.',
            'icon' => 'utensils',
            'color' => 'amber',
            'count' => \App\Models\Business::where($baseScope)
                ->where(fn($q) => $q->where('industry_category', 'fnb')->orWhere('template_code', 'like', 'fnb%'))
                ->count(),
            'tags' => ['Kopi Arabika', 'Frozen Food', 'Kue Basah', 'Sambal Kemasan', 'Katering Sehat'],
        ],
        'retail' => [
            'title' => 'Retail & Swalayan',
            'desc' =>
                'Toko kelontong modern, minimarket sembako, butik pakaian, hijab, batik tradisional, dan perlengkapan harian.',
            'icon' => 'store',
            'color' => 'blue',
            'count' => \App\Models\Business::where($baseScope)
                ->where(
                    fn($q) => $q->where('industry_category', 'retail')->orWhere('template_code', 'like', 'retail%'),
                )
                ->count(),
            'tags' => ['Pakaian Muslim', 'Batik Tulis', 'Sembako Grosir', 'Kosmetik BPOM', 'Alat Tulis'],
        ],
        'workshop' => [
            'title' => 'Bengkel & Otomotif',
            'desc' =>
                'Suku cadang motor dan mobil, pelumas oli mesin, aksesoris variasi, ban, dan layanan perbaikan kendaraan.',
            'icon' => 'wrench',
            'color' => 'slate',
            'count' => \App\Models\Business::where($baseScope)
                ->where(
                    fn($q) => $q
                        ->where('industry_category', 'workshop')
                        ->orWhere('template_code', 'like', 'workshop%')
                        ->orWhere('template_code', 'like', 'bengkel%'),
                )
                ->count(),
            'tags' => ['Oli Mesin', 'Kampas Rem', 'Ban Tubeless', 'Aksesoris Motor', 'Servis Berkala'],
        ],
        'laundry' => [
            'title' => 'Laundry & Kebersihan',
            'desc' =>
                'Layanan cuci kiloan, dry cleaning pakaian formal, laundry sepatu, helm, bed cover, dan perlengkapan deterjen.',
            'icon' => 'droplets',
            'color' => 'cyan',
            'count' => \App\Models\Business::where($baseScope)
                ->where(
                    fn($q) => $q
                        ->where('industry_category', 'laundry')
                        ->orWhere('template_code', 'like', 'laundry%'),
                )
                ->count(),
            'tags' => ['Cuci Kiloan', 'Dry Cleaning Jas', 'Laundry Sepatu', 'Cuci Karpet', 'Parfum Laundry'],
        ],
        'manufacture' => [
            'title' => 'Produsen & Pabrikasi',
            'desc' =>
                'Konveksi garmen pakaian, pengrajin mebel kayu, produk anyaman lokal, kemasan produk, dan industri kreatif rumahan.',
            'icon' => 'factory',
            'color' => 'indigo',
            'count' => \App\Models\Business::where($baseScope)
                ->where(
                    fn($q) => $q
                        ->where('industry_category', 'manufacture')
                        ->orWhere('template_code', 'like', 'mfg%'),
                )
                ->count(),
            'tags' => ['Konveksi Kaos', 'Mebel Jepara', 'Kerajinan Kulit', 'Dus Kemasan', 'Pabrik Snack'],
        ],
        'service' => [
            'title' => 'Jasa & Servis Profesional',
            'desc' =>
                'Jasa servis AC rumah/kantor, barbershop, salon kecantikan, studio foto, desainer grafis, dan konsultasi legal usaha.',
            'icon' => 'briefcase',
            'color' => 'violet',
            'count' => \App\Models\Business::where($baseScope)
                ->where(
                    fn($q) => $q
                        ->where('industry_category', 'service')
                        ->orWhere('template_code', 'like', 'service%'),
                )
                ->count(),
            'tags' => ['Servis AC', 'Barbershop Pria', 'Studio Foto', 'Perawatan Wajah', 'Jasa Desain'],
        ],
    ];
@endphp

@section('content')
<div class="bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors duration-300 min-h-screen">

    {{-- Hero Section (Midnight #060B1E Bento Canvas) --}}
    <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-20 overflow-hidden border-b border-white/10 w-full">
        {{-- Dual Ambient Glows --}}
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-white/20">/</span>
                <a href="{{ route('marketplace.index') }}" class="hover:text-white transition-colors">Marketplace</a>
                <span aria-hidden="true" class="text-white/20">/</span>
                <span class="text-[#00C4D8] font-semibold" aria-current="page">Kategori Usaha</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Header & Sector Narrative (7 Cols) --}}
                <div class="lg:col-span-7 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                        <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                            Klasifikasi Sektor Bisnis
                        </p>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Jelajahi Berbagai Kategori <span class="text-[#00C4D8]">Produk &amp; Layanan Bisnis</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Setiap toko dan produk di COOCA dikelompokkan secara terstruktur berdasarkan sektor industri, memudahkan Anda menemukan barang kebutuhan harian maupun layanan profesional terdekat.
                    </p>

                    {{-- Quick Action Links --}}
                    <div class="pt-1 flex flex-wrap items-center justify-center lg:justify-start gap-3 w-full sm:w-auto">
                        <a href="{{ route('marketplace.sub.businesses') }}"
                            class="h-11 px-6 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 transition active:scale-[0.98] min-h-[44px]">
                            <i data-lucide="store" class="w-4 h-4 shrink-0"></i>
                            <span>Buka Direktori Toko</span>
                        </a>
                        <a href="{{ route('marketplace.sub.products') }}"
                            class="h-11 px-5 rounded-[14px] bg-white/10 hover:bg-white/15 text-white border border-white/15 text-sm font-semibold transition active:scale-[0.98] flex items-center gap-2 backdrop-blur-sm min-h-[44px]">
                            <i data-lucide="package" class="w-4 h-4 shrink-0"></i>
                            <span>Lihat Katalog Produk</span>
                        </a>
                    </div>
                </div>

                {{-- Right: 4-Sector Bento Matrix Showcase (5 Cols) --}}
                <div class="lg:col-span-5 w-full">
                    <div class="rounded-[24px] bg-[#0E1E45]/80 border border-white/10 p-5 sm:p-6 shadow-2xl backdrop-blur-xl text-white space-y-4">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-300 ml-2">Sektor Terkoneksi</span>
                            </div>
                            <span class="text-[11px] font-mono text-[#00C4D8] font-bold">100% Terverifikasi</span>
                        </div>

                        {{-- 4 Sector Bento Tiles --}}
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-2">
                                <div class="w-8 h-8 rounded-[10px] bg-amber-500/15 text-amber-400 flex items-center justify-center">
                                    <i data-lucide="utensils" class="w-4 h-4"></i>
                                </div>
                                <div class="text-xs font-bold text-white">Kuliner &amp; F&amp;B</div>
                                <div class="text-[10px] text-slate-400">Resto &bull; Kafe &bull; Frozen</div>
                            </div>
                            <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-2">
                                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="store" class="w-4 h-4"></i>
                                </div>
                                <div class="text-xs font-bold text-white">Retail Swalayan</div>
                                <div class="text-[10px] text-slate-400">Kelontong &bull; Butik &bull; Grosir</div>
                            </div>
                            <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-2">
                                <div class="w-8 h-8 rounded-[10px] bg-slate-500/15 text-slate-300 flex items-center justify-center">
                                    <i data-lucide="wrench" class="w-4 h-4"></i>
                                </div>
                                <div class="text-xs font-bold text-white">Otomotif &amp; Bengkel</div>
                                <div class="text-[10px] text-slate-400">Sparepart &bull; Servis Motor</div>
                            </div>
                            <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-2">
                                <div class="w-8 h-8 rounded-[10px] bg-[#00C4D8]/15 text-[#00C4D8] flex items-center justify-center">
                                    <i data-lucide="droplets" class="w-4 h-4"></i>
                                </div>
                                <div class="text-xs font-bold text-white">Laundry &amp; Jasa</div>
                                <div class="text-[10px] text-slate-400">Kiloan &bull; Dry Cleaning</div>
                            </div>
                        </div>

                        {{-- Footer Badge --}}
                        <div class="p-2.5 rounded-[12px] bg-white/[0.04] border border-white/5 flex items-center justify-between text-xs text-slate-400">
                            <span class="truncate">Integrasi Inventaris Riil</span>
                            <span class="text-emerald-400 font-semibold shrink-0">Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content Categories --}}
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-12 sm:space-y-16">

        {{-- Category Bento Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($categoriesData as $key => $cat)
                <div class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-200 shadow-sm space-y-5 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i data-lucide="{{ $cat['icon'] }}" class="w-6 h-6" aria-hidden="true"></i>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-[#F2F2F7] dark:bg-[#2C2C2E] text-xs font-mono font-semibold text-[#86868B]">
                                {{ $cat['count'] }} Toko Terdaftar
                            </span>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                                {{ $cat['title'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-[#86868B] leading-relaxed">
                                {{ $cat['desc'] }}
                            </p>
                        </div>

                        {{-- Popular Tags --}}
                        <div class="flex flex-wrap gap-1.5 pt-2">
                            @foreach ($cat['tags'] as $tag)
                                <span class="px-2.5 py-1 rounded-lg bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] text-[11px] text-[#86868B]">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <a href="{{ route('marketplace.search', ['kategori' => $key]) }}"
                            class="w-full h-11 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-[#007AFF] hover:text-white dark:hover:bg-[#007AFF] text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-center gap-2 transition-all min-h-[44px]">
                            <span>Lihat Produk {{ $cat['title'] }}</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Related Links --}}
        <section class="border-t border-black/[0.06] dark:border-white/[0.08] pt-12 space-y-6">
            <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kanal Direktori Lainnya</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('marketplace.sub.businesses') }}"
                    class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all shadow-sm space-y-1 group">
                    <span class="text-xs uppercase font-bold text-[#007AFF]">Direktori Toko</span>
                    <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                        Semua Toko Terverifikasi</h4>
                </a>
                <a href="{{ route('marketplace.sub.products') }}"
                    class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all shadow-sm space-y-1 group">
                    <span class="text-xs uppercase font-bold text-[#007AFF]">Katalog Produk</span>
                    <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                        Semua Produk Unggulan</h4>
                </a>
                <a href="{{ route('marketplace.sub.locations') }}"
                    class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all shadow-sm space-y-1 group">
                    <span class="text-xs uppercase font-bold text-[#007AFF]">Wilayah Kota</span>
                    <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition-colors">
                        Cari Berdasarkan Kota</h4>
                </a>
            </div>
        </section>

        {{-- Final CTA --}}
        <section class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-5">
            <div class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                    <i data-lucide="store" class="w-4 h-4 text-[#00C4D8]" aria-hidden="true"></i>
                    <span>Tingkatkan Visibilitas Bisnis Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Daftarkan Usaha Anda ke Kategori yang Sesuai
                </h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Dapatkan calon pelanggan baru yang mencari produk atau layanan di industri Anda setiap hari melalui jaringan marketplace COOCA.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}"
                        class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all min-h-[48px]">
                        <span>Buka Toko Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('marketplace.index') }}"
                        class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm min-h-[48px]">
                        <span>Beranda Marketplace</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
