@extends('layouts.public_marketing')

@section('title', 'Direktori Bisnis & Toko UMKM Berdasarkan Kota | COOCA Marketplace')
@section('description', 'Temukan toko online, kafe, bengkel, dan UMKM terdekat di kota Anda. Jelajahi bisnis lokal dari Jakarta, Bandung, Surabaya, Medan, Bali, hingga seluruh nusantara.')
@section('keywords', 'direktori umkm per kota, toko umkm jakarta bandung surabaya, bisnis lokal terdekat, belanja produk kota terdekat, toko offline umkm')

@push('seo')
    <link rel="canonical" href="{{ route('marketplace.sub.locations') }}">
    <meta property="og:title" content="Direktori Bisnis & Toko UMKM Berdasarkan Kota | COOCA Marketplace">
    <meta property="og:description" content="Temukan dan dukung pelaku usaha UMKM lokal di kota tempat tinggal Anda dengan opsi kurir instan dan self pick-up.">
    <meta property="og:url" content="{{ route('marketplace.sub.locations') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Direktori Bisnis & Toko UMKM Berdasarkan Kota | COOCA Marketplace">
    <meta name="twitter:description" content="Jelajahi ekosistem toko fisik dan online UMKM lokal di berbagai kota di Indonesia.">

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
            'cities' => ['Jakarta', 'Bogor', 'Depok', 'Tangerang', 'Bekasi', 'Tangerang Selatan', 'Serang', 'Cilegon'],
            'desc' => 'Pusat bisnis ritel, kedai kopi modern, studio jasa profesional, dan gudang pusat pengiriman cepat.',
        ],
        [
            'region' => 'Jawa Barat',
            'cities' => ['Bandung', 'Cimahi', 'Sukabumi', 'Cianjur', 'Tasikmalaya', 'Cirebon', 'Garut', 'Purwakarta'],
            'desc' => 'Sentra kuliner kreatif, konveksi fashion, produsen kopi priangan, dan kerajinan kulit berkualitas.',
        ],
        [
            'region' => 'Jawa Tengah & D.I. Yogyakarta',
            'cities' => ['Yogyakarta', 'Semarang', 'Solo (Surakarta)', 'Magelang', 'Pekalongan', 'Tegal', 'Purwokerto', 'Klaten'],
            'desc' => 'Pusat batik tulis, mebel kayu ukir, kuliner legendaris, dan kerajinan cinderamata tradisional.',
        ],
        [
            'region' => 'Jawa Timur',
            'cities' => ['Surabaya', 'Malang', 'Sidoarjo', 'Gresik', 'Kediri', 'Jember', 'Banyuwangi', 'Madiun'],
            'desc' => 'Hub manufaktur ringan, industri olahan hasil bumi, bengkel fabrikasi, dan kuliner khas Jawa Timuran.',
        ],
        [
            'region' => 'Sumatera',
            'cities' => ['Medan', 'Palembang', 'Pekanbaru', 'Batam', 'Padang', 'Bandar Lampung', 'Jambi', 'Banda Aceh'],
            'desc' => 'Sentra kopi gayo, tenun songket, makanan khas daerah, dan perdagangan grosir antarpulau.',
        ],
        [
            'region' => 'Bali & Nusa Tenggara',
            'cities' => ['Denpasar', 'Badung', 'Gianyar', 'Mataram', 'Kupang', 'Labuan Bajo'],
            'desc' => 'Industri pariwisata, kerajinan seni tangan, spa kecantikan, produk organik, dan kafe estetis.',
        ],
        [
            'region' => 'Kalimantan & Sulawesi',
            'cities' => ['Balikpapan', 'Samarinda', 'Banjarmasin', 'Pontianak', 'Makassar', 'Manado', 'Palu', 'Kendari'],
            'desc' => 'Pusat komoditas lokal, tenun khas, kuliner bahari laut segar, dan jaringan distribusi daerah berkembang.',
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
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Cakupan Wilayah</span>
            </nav>

            {{-- Header & Search Bar --}}
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5" aria-hidden="true"></i>
                    <span>Cakupan Wilayah Nusantara</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                    Temukan Bisnis & Toko Lokal di Kota Anda
                </h1>

                <p class="text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    Dukung pertumbuhan perputaran ekonomi daerah. Jelajahi ribuan pelaku usaha yang memiliki gerai fisik maupun etalase online di kota tempat tinggal Anda.
                </p>

                {{-- City Search Form --}}
                <form method="GET" action="{{ route('marketplace.search') }}" class="pt-2">
                    <div class="relative flex items-center bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-neutral-200/80 dark:border-neutral-800 p-1.5 shadow-sm focus-within:ring-2 focus-within:ring-[#007AFF] transition">
                        <i data-lucide="map-pin" class="w-5 h-5 ml-3.5 text-[#6E6E73] dark:text-[#86868B] shrink-0" aria-hidden="true"></i>
                        <input type="text" name="q" placeholder="Ketik nama kota, misalnya: Bandung, Surabaya, Solo..." class="w-full bg-transparent border-0 px-3.5 py-3 text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7] placeholder-[#6E6E73]/50 focus:outline-none">
                        <button type="submit" class="shrink-0 h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-sm font-semibold shadow-sm active:scale-[0.98] transition">
                            Cari Kota
                        </button>
                    </div>
                </form>
            </div>

            {{-- Region Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($regions as $r)
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="map" class="w-5 h-5" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                {{ $r['region'] }}
                            </h3>
                        </div>

                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            {{ $r['desc'] }}
                        </p>

                        {{-- City Pills --}}
                        <div class="flex flex-wrap gap-2 pt-2">
                            @foreach ($r['cities'] as $city)
                                <a href="{{ route('marketplace.search', ['q' => $city]) }}" class="px-3 py-1.5 rounded-[10px] bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200/60 dark:border-neutral-800 hover:border-[#007AFF]/40 hover:text-[#007AFF] text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] transition flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-[#6E6E73]" aria-hidden="true"></i>
                                    <span>{{ $city }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Advantages of Local Buying Bento --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-6 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Keuntungan Berbelanja Lokal</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">Mengapa Membeli dari UMKM di Kota Anda?</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="zap" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pengiriman Instan Cepat</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pesan barang kebutuhan atau kuliner dengan kurir instan sameday, tiba di tangan Anda dalam beberapa jam saja.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="store" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Ambil Sendiri (Self Pick-up)</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pesan online lewat storefront penjual dan ambil paket pesanan langsung di toko fisik tanpa biaya kirim.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="coins" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Hemat Biaya Ongkos Kirim</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Jarak pengiriman yang lebih dekat memangkas biaya ekspedisi secara signifikan dibandingkan memesan dari luar pulau.</p>
                    </div>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Jangkau Pembeli di Kota Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Daftarkan Alamat Toko Fisik Anda Sekarang
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun COOCA dan cantumkan lokasi cabang gerai Anda agar calon pembeli di sekitar kota Anda dapat menemukan toko Anda dengan mudah.
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
