@extends('layouts.public_marketing')

@section('title', 'Direktori Bisnis & Toko UMKM Berdasarkan Kota | COOCA Marketplace')
@section('description', 'Temukan toko online, kafe, bengkel, dan UMKM terdekat di kota Anda. Jelajahi bisnis lokal dari Jakarta, Bandung, Surabaya, Medan, Bali, hingga seluruh nusantara.')
@section('keywords', 'direktori umkm per kota, toko umkm jakarta bandung surabaya, bisnis lokal terdekat, belanja produk kota terdekat, toko offline umkm')
@section('canonical', route('marketplace.sub.locations'))
@section('og_title', 'Direktori Bisnis & Toko UMKM Berdasarkan Kota | COOCA Marketplace')
@section('og_description', 'Temukan dan dukung pelaku usaha UMKM lokal di kota tempat tinggal Anda dengan opsi kurir instan dan self pick-up.')
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Direktori Lokasi Toko UMKM COOCA",
  "description": "Direktori pencarian toko dan bisnis UMKM berdasarkan cakupan wilayah dan kota di Indonesia.",
  "url": "{{ route('marketplace.sub.locations') }}"
}
</script>
@endpush

@php
    $regions = [
        [
            'region' => 'Jabodetabek & Banten',
            'cities' => [
                'Jakarta',
                'Bogor',
                'Depok',
                'Tangerang',
                'Bekasi',
                'Tangerang Selatan',
                'Serang',
                'Cilegon',
            ],
            'desc' =>
                'Pusat bisnis ritel, kedai kopi modern, studio jasa profesional, dan gudang pusat pengiriman cepat.',
        ],
        [
            'region' => 'Jawa Barat',
            'cities' => [
                'Bandung',
                'Cimahi',
                'Sukabumi',
                'Cianjur',
                'Tasikmalaya',
                'Cirebon',
                'Garut',
                'Purwakarta',
            ],
            'desc' =>
                'Sentra kuliner kreatif, konveksi fashion, produsen kopi priangan, dan kerajinan kulit berkualitas.',
        ],
        [
            'region' => 'Jawa Tengah & D.I. Yogyakarta',
            'cities' => [
                'Yogyakarta',
                'Semarang',
                'Solo (Surakarta)',
                'Magelang',
                'Pekalongan',
                'Tegal',
                'Purwokerto',
                'Klaten',
            ],
            'desc' =>
                'Pusat batik tulis, mebel kayu ukir, kuliner legendaris, dan kerajinan cinderamata tradisional.',
        ],
        [
            'region' => 'Jawa Timur',
            'cities' => ['Surabaya', 'Malang', 'Sidoarjo', 'Gresik', 'Kediri', 'Jember', 'Banyuwangi', 'Madiun'],
            'desc' =>
                'Hub manufaktur ringan, industri olahan hasil bumi, bengkel fabrikasi, dan kuliner khas Jawa Timuran.',
        ],
        [
            'region' => 'Sumatera',
            'cities' => [
                'Medan',
                'Palembang',
                'Pekanbaru',
                'Batam',
                'Padang',
                'Bandar Lampung',
                'Jambi',
                'Banda Aceh',
            ],
            'desc' => 'Sentra kopi gayo, tenun songket, makanan khas daerah, dan perdagangan grosir antarpulau.',
        ],
        [
            'region' => 'Bali & Nusa Tenggara',
            'cities' => ['Denpasar', 'Badung', 'Gianyar', 'Mataram', 'Kupang', 'Labuan Bajo'],
            'desc' =>
                'Industri pariwisata, kerajinan seni tangan, spa kecantikan, produk organik, dan kafe estetis.',
        ],
        [
            'region' => 'Kalimantan & Sulawesi',
            'cities' => [
                'Balikpapan',
                'Samarinda',
                'Banjarmasin',
                'Pontianak',
                'Makassar',
                'Manado',
                'Palu',
                'Kendari',
            ],
            'desc' =>
                'Pusat komoditas lokal, tenun khas, kuliner bahari laut segar, dan jaringan distribusi daerah berkembang.',
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
                <span class="text-[#00C4D8] font-semibold" aria-current="page">Cakupan Wilayah</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Header, City Search & Trust Info (7 Cols) --}}
                <div class="lg:col-span-7 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                        <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                            Cakupan Wilayah Nusantara
                        </p>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Temukan Bisnis &amp; <span class="text-[#00C4D8]">Toko Lokal di Kota Anda</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Dukung pertumbuhan perputaran ekonomi daerah. Jelajahi ribuan pelaku usaha yang memiliki gerai fisik maupun etalase online di kota tempat tinggal Anda.
                    </p>

                    {{-- City Search Form --}}
                    <form method="GET" action="{{ route('marketplace.search') }}" class="pt-1 w-full max-w-[32rem] lg:max-w-none">
                        <div class="relative flex items-center bg-[#0E1E45]/80 rounded-[20px] border border-white/15 p-1.5 shadow-2xl backdrop-blur-md focus-within:ring-2 focus-within:ring-[#00C4D8] transition">
                            <i data-lucide="map-pin" class="w-5 h-5 ml-3.5 text-slate-400 shrink-0" aria-hidden="true"></i>
                            <input type="text" name="q" placeholder="Ketik nama kota, misalnya: Bandung, Surabaya, Solo..."
                                class="w-full bg-transparent border-0 px-3.5 py-3 text-[16px] text-white placeholder-slate-400 focus:outline-none">
                            <button type="submit"
                                class="shrink-0 h-11 px-6 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition min-h-[44px]">
                                Cari Kota
                            </button>
                        </div>
                    </form>

                    {{-- Micro Trust Tags --}}
                    <div class="pt-1 flex flex-wrap items-center justify-center lg:justify-start gap-y-2 gap-x-5 text-xs text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="compass" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                            <span>Sentra Usaha Daerah</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="map" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>500+ Kota Terjangkau</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Regional Coverage Bento Cockpit (5 Cols) --}}
                <div class="lg:col-span-5 w-full">
                    <div class="rounded-[24px] bg-[#0E1E45]/80 border border-white/10 p-5 sm:p-6 shadow-2xl backdrop-blur-xl text-white space-y-4">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-300 ml-2">Jangkauan Wilayah</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Sync
                            </span>
                        </div>

                        {{-- 3 Key Regional Metrics Bento --}}
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-1">
                                <div class="text-[11px] text-slate-400">Total Hub Wilayah</div>
                                <div class="text-xl font-bold text-white">6 Koridor</div>
                                <div class="text-[10px] text-[#00C4D8]">Jawa hingga Papua</div>
                            </div>
                            <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-1">
                                <div class="text-[11px] text-slate-400">Ambil di Gerai</div>
                                <div class="text-xl font-bold text-emerald-400">Tersedia</div>
                                <div class="text-[10px] text-slate-400">Pick-up langsung</div>
                            </div>
                        </div>

                        {{-- Sample City Route Pill --}}
                        <div class="p-3 rounded-[16px] bg-white/[0.04] border border-white/10 space-y-2">
                            <div class="text-xs font-semibold text-slate-300 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="navigation" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                    <span>Kota Teraktif Minggu Ini</span>
                                </span>
                                <span class="text-[10px] font-mono text-emerald-400 font-bold">100% Realtime</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5 text-[11px] font-medium text-slate-300">
                                <span class="px-2.5 py-1 rounded-[8px] bg-white/5 border border-white/10">Bandung</span>
                                <span class="px-2.5 py-1 rounded-[8px] bg-white/5 border border-white/10">Surabaya</span>
                                <span class="px-2.5 py-1 rounded-[8px] bg-white/5 border border-white/10">Yogyakarta</span>
                                <span class="px-2.5 py-1 rounded-[8px] bg-white/5 border border-white/10">Medan</span>
                                <span class="px-2.5 py-1 rounded-[8px] bg-white/5 border border-white/10">Makassar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content Locations --}}
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-12 sm:space-y-16">

        {{-- Region Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($regions as $r)
                <div class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-200 shadow-sm space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="map" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            {{ $r['region'] }}
                        </h3>
                    </div>

                    <p class="text-xs sm:text-sm text-[#86868B] leading-relaxed">
                        {{ $r['desc'] }}
                    </p>

                    {{-- City Pills --}}
                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($r['cities'] as $city)
                            <a href="{{ route('marketplace.search', ['q' => $city]) }}"
                                class="px-3 py-1.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] hover:border-[#007AFF]/40 hover:text-[#007AFF] text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3 h-3 text-[#86868B]" aria-hidden="true"></i>
                                <span>{{ $city }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Advantages of Local Buying Bento --}}
        <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-6 shadow-sm">
            <div class="max-w-2xl">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] block">Keuntungan Berbelanja Lokal</span>
                <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">Mengapa Membeli dari UMKM di Kota Anda?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-5 rounded-[18px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] space-y-2">
                    <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="zap" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pengiriman Instan Cepat</h4>
                    <p class="text-xs text-[#86868B] leading-relaxed">Pesan barang kebutuhan atau kuliner dengan kurir instan sameday, tiba di tangan Anda dalam beberapa jam saja.</p>
                </div>

                <div class="p-5 rounded-[18px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] space-y-2">
                    <div class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="store" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Ambil Sendiri (Self Pick-up)</h4>
                    <p class="text-xs text-[#86868B] leading-relaxed">Pesan online lewat storefront penjual dan ambil paket pesanan langsung di toko fisik tanpa biaya kirim.</p>
                </div>

                <div class="p-5 rounded-[18px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.04] space-y-2">
                    <div class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <i data-lucide="coins" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Hemat Biaya Ongkos Kirim</h4>
                    <p class="text-xs text-[#86868B] leading-relaxed">Jarak pengiriman yang lebih dekat memangkas biaya ekspedisi secara signifikan dibandingkan memesan dari luar pulau.</p>
                </div>
            </div>
        </section>

        {{-- Final CTA --}}
        <section class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-5">
            <div class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#00C4D8]" aria-hidden="true"></i>
                    <span>Jangkau Pembeli di Kota Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Daftarkan Alamat Toko Fisik Anda Sekarang
                </h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Daftar akun COOCA dan cantumkan lokasi cabang gerai Anda agar calon pembeli di sekitar kota Anda dapat menemukan toko Anda dengan mudah.
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
