@extends('layouts.public_marketing')

@section('title', ($search ? "Hasil Pencarian: {$search}" : 'Katalog Produk & Direktori UMKM') . ' | Cooca Marketplace')
@section('description',
    'Jelajahi dan temukan aneka produk lokal berkualitas dari ribuan bisnis UMKM Indonesia. Belanja langsung, dukung produk lokal terpercaya.')

@section('canonical', route('marketplace.search'))
@section('og_title', ($search ? "Hasil Pencarian: {$search}" : 'Katalog Produk & Direktori UMKM') . ' | Cooca Marketplace')
@section('og_description', 'Jelajahi dan temukan aneka produk lokal berkualitas dari ribuan bisnis UMKM Indonesia.')
@section('og_type', 'website')
@if(request()->filled('q') || request()->filled('category') || request()->filled('city') || request()->filled('sort') || request()->filled('business_type'))
@section('robots', 'noindex, follow')
@endif

@push('seo')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SearchResultsPage",
      "name": "Pencarian Produk Cooca Marketplace",
      "url": "{{ route('marketplace.search') }}"
    }
    </script>
@endpush

@push('styles')
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('content')
    @php
        $hasActiveFilters =
            $search !== '' ||
            ($category && $category !== 'semua') ||
            ($minPrice !== null && $minPrice > 0) ||
            ($maxPrice !== null && $maxPrice > 0) ||
            $priceRange !== '' ||
            $type !== '';

        $activeFilterCount = 0;
        if ($category && $category !== 'semua') {
            $activeFilterCount++;
        }
        if ($minPrice !== null || $maxPrice !== null || $priceRange !== '') {
            $activeFilterCount++;
        }
        if ($type !== '') {
            $activeFilterCount++;
        }
        if ($search !== '') {
            $activeFilterCount++;
        }

        $sortOptions = [
            'terbaru' => ['label' => 'Terbaru', 'icon' => 'clock'],
            'terpopuler' => ['label' => 'Terpopuler', 'icon' => 'trending-up'],
            'harga_rendah' => ['label' => 'Termurah', 'icon' => 'arrow-down-narrow-wide'],
            'harga_tinggi' => ['label' => 'Termahal', 'icon' => 'arrow-up-wide-narrow'],
            'nama' => ['label' => 'Nama A-Z', 'icon' => 'arrow-down-a-z'],
        ];

        $typeOptions = [
            '' => ['label' => 'Semua Tipe', 'icon' => 'layers'],
            'goods' => ['label' => 'Barang Fisik', 'icon' => 'package'],
            'service' => ['label' => 'Jasa & Layanan', 'icon' => 'briefcase'],
            'preorder' => ['label' => 'Pre-Order', 'icon' => 'clock'],
        ];

        $pricePresetOptions = [
            'murah' => '< 50rb',
            'sedang' => '50rb - 200rb',
            'mahal' => '> 200rb',
        ];

        $categoryLabels = [
            'fnb' => 'Kuliner & F&B',
            'retail' => 'Ritel & Toko',
            'service' => 'Jasa & Layanan',
            'workshop' => 'Bengkel & Otomotif',
            'laundry' => 'Laundry & Cuci',
            'manufacture' => 'Produsen & Pabrik',
        ];
    @endphp

    <div x-data="{ mobileFilterOpen: false }"
        class="bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors duration-300 min-h-screen">

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- HERO SECTION (Midnight #060B1E Full Bleed) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-12 lg:pb-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
                {{-- Breadcrumb --}}
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('landing') }}" class="hover:text-white transition">Beranda</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <a href="{{ route('marketplace.index') }}" class="hover:text-white transition inline-flex items-center gap-1">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        <span>Marketplace</span>
                    </a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Cari Produk</span>
                    @if ($category && $category !== 'semua' && isset($categories[$category]))
                        <span aria-hidden="true" class="text-white/20">/</span>
                        <span class="text-white font-medium">{{ $categories[$category]['label'] }}</span>
                    @endif
                </nav>

                <div class="max-w-3xl space-y-3 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-2 w-full">
                        <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                        <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                            Pencarian Produk &amp; Direktori UMKM
                        </p>

                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            @if ($search)
                                Hasil Pencarian: <span class="text-[#00C4D8]">"{{ $search }}"</span>
                            @elseif ($category && $category !== 'semua' && isset($categories[$category]))
                                Kategori: <span class="text-[#00C4D8]">{{ $categories[$category]['label'] }}</span>
                            @else
                                Katalog Produk &amp; <span class="text-[#00C4D8]">Toko UMKM Lokal</span>
                            @endif
                        </h1>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-2xl mx-auto lg:mx-0">
                        Jelajahi produk lokal berkualitas langsung dari ribuan pemilik bisnis di seluruh Indonesia dengan stok sinkron kasir real-time.
                    </p>
                </div>

                {{-- Tokopedia / Shopee Search Form --}}
                <div class="bg-[#0E1E45]/80 p-3 sm:p-4 rounded-[24px] border border-white/15 shadow-2xl backdrop-blur-md">
                    <form method="GET" action="{{ route('marketplace.search') }}"
                        class="relative flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        {{-- Preserve existing active filters when searching --}}
                        @if ($category && $category !== 'semua')
                            <input type="hidden" name="kategori" value="{{ $category }}">
                        @endif
                        @if ($minPrice)
                            <input type="hidden" name="min_harga" value="{{ $minPrice }}">
                        @endif
                        @if ($maxPrice)
                            <input type="hidden" name="max_harga" value="{{ $maxPrice }}">
                        @endif
                        @if ($priceRange && !$minPrice && !$maxPrice)
                            <input type="hidden" name="harga" value="{{ $priceRange }}">
                        @endif
                        @if ($type)
                            <input type="hidden" name="tipe" value="{{ $type }}">
                        @endif
                        @if ($sort && $sort !== 'terbaru')
                            <input type="hidden" name="urut" value="{{ $sort }}">
                        @endif

                        <div class="relative flex-1 flex items-center bg-[#060B1E]/70 rounded-[16px] px-3.5 py-1 border border-white/10 focus-within:ring-2 focus-within:ring-[#00C4D8] focus-within:border-transparent transition">
                            <i data-lucide="search" class="w-5 h-5 text-slate-400 shrink-0"></i>
                            <input type="text" name="q" value="{{ $search }}"
                                placeholder="Cari produk, toko UMKM, atau kategori..." autocomplete="off"
                                class="w-full bg-transparent border-0 px-3 py-2.5 text-[16px] sm:text-sm text-white placeholder:text-slate-400 focus:outline-none">
                            @if ($search !== '')
                                <a href="{{ route('marketplace.search', request()->except(['q', 'page'])) }}"
                                    class="p-1 rounded-full text-slate-400 hover:text-white transition"
                                    title="Hapus kata kunci">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-6 py-3 rounded-[16px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-sm font-semibold shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition min-h-[44px]">
                                <i data-lucide="search" class="w-4 h-4"></i>
                                <span>Cari</span>
                            </button>

                            {{-- Mobile Filter Trigger Button --}}
                            <button type="button" @click="mobileFilterOpen = true"
                                class="lg:hidden inline-flex items-center justify-center gap-2 px-4 py-3 rounded-[16px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold transition min-h-[44px]">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                                <span>Filter</span>
                                @if ($activeFilterCount > 0)
                                    <span
                                        class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[10px] font-bold flex items-center justify-center tabular-nums">
                                        {{ $activeFilterCount }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </form>

                    {{-- Quick Keyword Suggestions --}}
                    <div class="mt-3 pt-3 border-t border-white/10 flex items-center gap-2 overflow-x-auto no-scrollbar text-xs">
                        <span class="text-slate-400 shrink-0 font-medium">Populer:</span>
                        @php
                            $popularKeywords = [
                                'Kopi',
                                'Oli',
                                'Laundry',
                                'Servis AC',
                                'Kaos',
                                'Kue',
                                'Madu',
                                'Sparepart',
                            ];
                        @endphp
                        @foreach ($popularKeywords as $kw)
                            <a href="{{ route('marketplace.search', array_merge(request()->except(['page']), ['q' => $kw])) }}"
                                class="px-2.5 py-1 rounded-full bg-white/10 hover:bg-[#007AFF]/30 hover:text-[#00C4D8] text-slate-300 transition whitespace-nowrap">
                                {{ $kw }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- MAIN LAYOUT: 2 COLUMNS (SIDEBAR + CATALOG) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- ────────────────────────────────────────────────────────────────── --}}
                {{-- DESKTOP SIDEBAR: SHOPEE & TOKOPEDIA FILTER NAVIGATION --}}
                {{-- ────────────────────────────────────────────────────────────────── --}}
                <aside class="hidden lg:block lg:col-span-3 space-y-6 sticky top-24">
                    <div class="bg-white dark:bg-[#0E172F]/70 rounded-[24px] border border-slate-200/80 dark:border-white/10 p-5 shadow-sm space-y-6 text-slate-900 dark:text-white">

                        {{-- Sidebar Header --}}
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-white/10">
                            <div class="flex items-center gap-2 font-bold text-base text-slate-900 dark:text-white">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Filter Produk</span>
                            </div>
                            @if ($hasActiveFilters)
                                <a href="{{ route('marketplace.search') }}"
                                    class="text-xs font-medium text-rose-500 hover:text-rose-600 dark:text-rose-400 flex items-center gap-1 transition"
                                    title="Reset semua filter">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Reset</span>
                                </a>
                            @endif
                        </div>

                        {{-- 1. KATEGORI FILTER (Shopee / Tokopedia tree list) --}}
                        <div class="space-y-3 pb-5 border-b border-slate-200/80 dark:border-white/10">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center justify-between">
                                <span>Kategori</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                            </h4>

                            <div class="space-y-1">
                                {{-- Semua Kategori --}}
                                @php
                                    $isAllActive = empty($category) || $category === 'semua';
                                @endphp
                                <a href="{{ route('marketplace.search', request()->except(['kategori', 'page'])) }}"
                                    class="group flex items-center justify-between px-3 py-2 rounded-[12px] text-xs font-semibold transition {{ $isAllActive ? 'bg-[#007AFF] text-white shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i data-lucide="layout-grid"
                                            class="w-3.5 h-3.5 {{ $isAllActive ? 'text-white' : 'text-[#007AFF]' }}"></i>
                                        <span>Semua Kategori</span>
                                    </div>
                                    @if ($isAllActive)
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>
                                    @endif
                                </a>

                                {{-- Industry Categories --}}
                                @foreach ($categories as $catKey => $catData)
                                    @php
                                        $isCatActive = $category === $catKey;
                                    @endphp
                                    <a href="{{ route('marketplace.search', array_merge(request()->except(['kategori', 'page']), ['kategori' => $catKey])) }}"
                                        class="group flex items-center justify-between px-3 py-2 rounded-[12px] text-xs font-medium transition {{ $isCatActive ? 'bg-[#007AFF] text-white font-semibold shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                                        <div class="flex items-center gap-2.5 truncate">
                                            <i data-lucide="{{ $catData['icon'] }}"
                                                class="w-3.5 h-3.5 shrink-0 {{ $isCatActive ? 'text-white' : 'text-slate-400 group-hover:text-[#007AFF]' }}"></i>
                                            <span class="truncate">{{ $catData['label'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                            <span
                                                class="px-1.5 py-0.5 rounded-full text-[10px] tabular-nums {{ $isCatActive ? 'bg-white/20 text-white font-semibold' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300' }}">
                                                {{ $catData['count'] }}
                                            </span>
                                            @if ($isCatActive)
                                                <i data-lucide="check" class="w-3 h-3 text-white"></i>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- 2. BATAS HARGA (Shopee / Tokopedia price input + presets) --}}
                        <div class="space-y-3 pb-5 border-b border-slate-200/80 dark:border-white/10">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Batas Harga
                            </h4>

                            <form method="GET" action="{{ route('marketplace.search') }}" class="space-y-2.5">
                                {{-- Preserve other query params --}}
                                @if ($search !== '')
                                    <input type="hidden" name="q" value="{{ $search }}">
                                @endif
                                @if ($category && $category !== 'semua')
                                    <input type="hidden" name="kategori" value="{{ $category }}">
                                @endif
                                @if ($type)
                                    <input type="hidden" name="tipe" value="{{ $type }}">
                                @endif
                                @if ($sort && $sort !== 'terbaru')
                                    <input type="hidden" name="urut" value="{{ $sort }}">
                                @endif

                                <div class="space-y-2">
                                    <div class="relative flex items-center bg-slate-50 dark:bg-[#070A14] rounded-[12px] px-3 py-1.5 border border-slate-200 dark:border-white/10 focus-within:ring-2 focus-within:ring-[#007AFF]">
                                        <span class="text-xs font-bold text-slate-400 mr-1.5">Rp</span>
                                        <input type="number" name="min_harga" value="{{ $minPrice }}"
                                            placeholder="Harga Minimum" min="0" step="1000"
                                            class="w-full bg-transparent border-0 p-0 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none tabular-nums">
                                    </div>
                                    <div class="relative flex items-center bg-slate-50 dark:bg-[#070A14] rounded-[12px] px-3 py-1.5 border border-slate-200 dark:border-white/10 focus-within:ring-2 focus-within:ring-[#007AFF]">
                                        <span class="text-xs font-bold text-slate-400 mr-1.5">Rp</span>
                                        <input type="number" name="max_harga" value="{{ $maxPrice }}"
                                            placeholder="Harga Maksimum" min="0" step="1000"
                                            class="w-full bg-transparent border-0 p-0 text-[16px] sm:text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none tabular-nums">
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full py-2 px-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold transition active:scale-[0.98] shadow-sm flex items-center justify-center gap-1.5 min-h-[36px]">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Terapkan Harga</span>
                                </button>
                            </form>

                            {{-- Quick Price Range Chips --}}
                            <div class="pt-1">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 block mb-1.5">Pilihan Cepat:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($pricePresetOptions as $prKey => $prLabel)
                                        @php
                                            $isPresetActive = $priceRange === $prKey && !$minPrice && !$maxPrice;
                                        @endphp
                                        <a href="{{ route('marketplace.search', array_merge(request()->except(['harga', 'min_harga', 'max_harga', 'page']), $isPresetActive ? [] : ['harga' => $prKey])) }}"
                                            class="px-2.5 py-1 rounded-[8px] text-[11px] font-medium transition {{ $isPresetActive ? 'bg-[#34C759] text-white font-semibold shadow-xs' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/15' }}">
                                            {{ $prLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- 3. TIPE PRODUK (Ready Stock, Jasa, Pre-Order) --}}
                        <div class="space-y-3 pb-5 border-b border-slate-200/80 dark:border-white/10">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Tipe Produk
                            </h4>
                            <div class="space-y-1">
                                @foreach ($typeOptions as $tKey => $tData)
                                    @php
                                        $isTypeActive = $type === $tKey;
                                    @endphp
                                    <a href="{{ route('marketplace.search', array_merge(request()->except(['tipe', 'page']), $tKey ? ['tipe' => $tKey] : [])) }}"
                                        class="flex items-center justify-between px-3 py-1.5 rounded-[10px] text-xs transition {{ $isTypeActive ? 'bg-[#007AFF]/10 text-[#007AFF] font-bold dark:bg-[#007AFF]/20 dark:text-[#38BDF8]' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="{{ $tData['icon'] }}"
                                                class="w-3.5 h-3.5 {{ $isTypeActive ? 'text-[#007AFF]' : 'text-slate-400' }}"></i>
                                            <span>{{ $tData['label'] }}</span>
                                        </div>
                                        @if ($isTypeActive)
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- 4. BENTO TRUST BADGES (Tokopedia Official Store / UMKM Trust Card) --}}
                        <div class="p-3.5 rounded-[16px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2.5 text-xs">
                            <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                <span>UMKM Terdaftar</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                Semua toko dan produk di Cooca Marketplace telah diverifikasi dan dikelola langsung oleh
                                pemilik usaha lokal.
                            </p>
                            <div class="pt-1 flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="message-circle" class="w-3 h-3 text-[#007AFF]"></i> WhatsApp
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="truck" class="w-3 h-3 text-[#FF9500]"></i> Ambil / Kirim
                                </span>
                            </div>
                        </div>

                    </div>
                </aside>

                {{-- ────────────────────────────────────────────────────────────────── --}}
                {{-- RIGHT COLUMN: SEARCH HEADER, SORT BAR, CATEGORY TABS & CATALOG --}}
                {{-- ────────────────────────────────────────────────────────────────── --}}
                <main class="lg:col-span-9 space-y-6">

                    {{-- ═══ TOP CONTROLS & SHOPEE-STYLE SORT BAR ═══ --}}
                    <div class="bg-white dark:bg-[#0E172F]/70 rounded-[24px] border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-sm space-y-4">

                        {{-- Header Summary & Sort Controls --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                    @if ($search)
                                        Hasil Pencarian: "{{ $search }}"
                                    @elseif ($category && $category !== 'semua' && isset($categories[$category]))
                                        {{ $categories[$category]['label'] }}
                                    @else
                                        Semua Produk UMKM
                                    @endif
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Menampilkan <span
                                        class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $products->total() }}</span>
                                    produk siap transaksi
                                </p>
                            </div>

                            {{-- Shopee / Tokopedia Segmented Sort Tabs --}}
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 hidden md:inline-block">Urutkan:</span>

                                {{-- Desktop Segmented Tabs --}}
                                <div class="hidden sm:inline-flex items-center bg-slate-100 dark:bg-[#070A14] p-1 rounded-[14px] border border-slate-200/80 dark:border-white/5">
                                    @foreach ($sortOptions as $sortKey => $sortData)
                                        @php
                                            $isSortActive = $sort === $sortKey;
                                        @endphp
                                        <a href="{{ route('marketplace.search', array_merge(request()->except(['urut', 'page']), ['urut' => $sortKey])) }}"
                                            class="px-3 py-1.5 rounded-[10px] text-xs font-medium transition inline-flex items-center gap-1.5 {{ $isSortActive ? 'bg-white dark:bg-[#0E172F] text-slate-900 dark:text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                            <i data-lucide="{{ $sortData['icon'] }}"
                                                class="w-3.5 h-3.5 {{ $isSortActive ? 'text-[#007AFF]' : '' }}"></i>
                                            <span>{{ $sortData['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>

                                {{-- Mobile Sort Dropdown --}}
                                <div class="sm:hidden w-full flex items-center gap-2">
                                    <select onchange="window.location.href=this.value"
                                        class="w-full bg-slate-100 dark:bg-[#070A14] border-0 rounded-[12px] px-3 py-2 text-[16px] sm:text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                                        @foreach ($sortOptions as $sortKey => $sortData)
                                            <option
                                                value="{{ route('marketplace.search', array_merge(request()->except(['urut', 'page']), ['urut' => $sortKey])) }}"
                                                {{ $sort === $sortKey ? 'selected' : '' }}>
                                                Urutkan: {{ $sortData['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Horizontal Quick Category Chips (Shopee-Style Quick Slider) --}}
                        <div class="pt-2 border-t border-slate-200/80 dark:border-white/10 flex items-center gap-2 overflow-x-auto no-scrollbar">
                            <a href="{{ route('marketplace.search', request()->except(['kategori', 'page'])) }}"
                                class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition inline-flex items-center gap-1.5 {{ empty($category) || $category === 'semua' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/15' }}">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                                <span>Semua</span>
                            </a>
                            @foreach ($categories as $catKey => $catData)
                                @php
                                    $isCatActive = $category === $catKey;
                                @endphp
                                <a href="{{ route('marketplace.search', array_merge(request()->except(['kategori', 'page']), ['kategori' => $catKey])) }}"
                                    class="px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition inline-flex items-center gap-1.5 {{ $isCatActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/15' }}">
                                    <i data-lucide="{{ $catData['icon'] }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $catData['label'] }}</span>
                                </a>
                            @endforeach
                        </div>

                        {{-- Active Filter Dismissal Tags (Tokopedia Style) --}}
                        @if ($hasActiveFilters)
                            <div class="pt-2 border-t border-slate-200/80 dark:border-white/10 flex flex-wrap items-center gap-2">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Filter Aktif:</span>

                                @if ($search !== '')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#38BDF8] border border-[#007AFF]/20">
                                        <span>Cari: "{{ $search }}"</span>
                                        <a href="{{ route('marketplace.search', request()->except(['q', 'page'])) }}"
                                            class="hover:opacity-75">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </span>
                                @endif

                                @if ($category && $category !== 'semua' && isset($categories[$category]))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#38BDF8] border border-[#007AFF]/20">
                                        <span>{{ $categories[$category]['label'] }}</span>
                                        <a href="{{ route('marketplace.search', request()->except(['kategori', 'page'])) }}"
                                            class="hover:opacity-75">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </span>
                                @endif

                                @if ($minPrice || $maxPrice)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#38BDF8] border border-[#007AFF]/20">
                                        <span>Rp {{ $minPrice ? number_format($minPrice, 0, ',', '.') : '0' }} -
                                            {{ $maxPrice ? number_format($maxPrice, 0, ',', '.') : 'Maks' }}</span>
                                        <a href="{{ route('marketplace.search', request()->except(['min_harga', 'max_harga', 'harga', 'page'])) }}"
                                            class="hover:opacity-75">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </span>
                                @elseif ($priceRange && isset($pricePresetOptions[$priceRange]))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#38BDF8] border border-[#007AFF]/20">
                                        <span>Harga: {{ $pricePresetOptions[$priceRange] }}</span>
                                        <a href="{{ route('marketplace.search', request()->except(['harga', 'min_harga', 'max_harga', 'page'])) }}"
                                            class="hover:opacity-75">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </span>
                                @endif

                                @if ($type && isset($typeOptions[$type]))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#38BDF8] border border-[#007AFF]/20">
                                        <span>{{ $typeOptions[$type]['label'] }}</span>
                                        <a href="{{ route('marketplace.search', request()->except(['tipe', 'page'])) }}"
                                            class="hover:opacity-75">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </span>
                                @endif

                                <a href="{{ route('marketplace.search') }}"
                                    class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 inline-flex items-center gap-1 px-2 py-1 rounded-md transition">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Hapus Semua</span>
                                </a>
                            </div>
                        @endif

                    </div>

                    {{-- ═══ PRODUCT CARDS GRID ═══ --}}
                    @if ($products->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                            @foreach ($products as $product)
                                <a href="{{ url('/' . ($product->business->slug ?? 'toko') . '/produk/' . ($product->slug ?: $product->id)) }}"
                                    class="group flex flex-col h-full bg-white dark:bg-[#0E172F]/70 rounded-[20px] border border-slate-200/80 dark:border-white/10 overflow-hidden hover:shadow-xl hover:border-[#007AFF]/40 hover:-translate-y-1 transition-all duration-300 relative text-slate-900 dark:text-white">

                                    {{-- Product Image & Badges --}}
                                    <div class="relative aspect-square w-full bg-slate-100 dark:bg-[#070A14] overflow-hidden">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                loading="lazy"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-600">
                                                <i data-lucide="package" class="w-10 h-10 stroke-[1.5]"></i>
                                                <span class="text-[10px] mt-1 font-medium tracking-tight">Cooca UMKM</span>
                                            </div>
                                        @endif

                                        {{-- Type Badges Overlay --}}
                                        @if ($product->is_preorder)
                                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-[8px] text-[10px] font-bold bg-[#FF9500] text-white shadow-sm flex items-center gap-1 backdrop-blur-sm">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span>PO {{ $product->preorder_lead_days ? $product->preorder_lead_days . ' hr' : 'Pre-Order' }}</span>
                                            </span>
                                        @elseif ($product->type === 'service')
                                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-[8px] text-[10px] font-bold bg-[#AF52DE] text-white shadow-sm flex items-center gap-1 backdrop-blur-sm">
                                                <i data-lucide="briefcase" class="w-3 h-3"></i>
                                                <span>Layanan</span>
                                            </span>
                                        @endif

                                        {{-- Category pill on image bottom --}}
                                        @if ($product->category?->name)
                                            <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-[6px] text-[10px] font-medium bg-black/60 backdrop-blur-md text-white/95 line-clamp-1 max-w-[85%]">
                                                {{ $product->category->name }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Product Content Body --}}
                                    <div class="p-3.5 sm:p-4 flex flex-col flex-1 justify-between space-y-2.5">
                                        <div class="space-y-1">
                                            <h3 class="font-semibold text-xs sm:text-sm text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-[#007AFF] transition-colors"
                                                title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </h3>

                                            <p class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tabular-nums tracking-tight pt-0.5">
                                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        {{-- Store and Location Info (Shopee / Tokopedia Element) --}}
                                        <div class="pt-2 border-t border-slate-100 dark:border-white/10 space-y-1">
                                            <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-600 dark:text-slate-300">
                                                <i data-lucide="store" class="w-3 h-3 text-[#007AFF] shrink-0"></i>
                                                <span class="truncate font-medium">{{ $product->business->name }}</span>
                                                <i data-lucide="badge-check" class="w-3 h-3 text-[#34C759] shrink-0"
                                                    title="Toko Terverifikasi"></i>
                                            </div>
                                            <div class="flex items-center gap-1 text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500">
                                                <i data-lucide="map-pin" class="w-2.5 h-2.5 shrink-0"></i>
                                                <span class="truncate">{{ $product->business->storeSetting?->city ?: 'Indonesia' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                </a>
                            @endforeach
                        </div>

                        {{-- Clean Bento Pagination --}}
                        <div class="pt-6">
                            {{ $products->links() }}
                        </div>
                    @else
                        {{-- ═══ EMPTY STATE ═══ --}}
                        <div class="bg-white dark:bg-[#0E172F]/70 rounded-[24px] border border-slate-200/80 dark:border-white/10 p-8 sm:p-16 text-center space-y-5 shadow-sm">
                            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-white/10 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center">
                                <i data-lucide="search-x" class="w-8 h-8 stroke-[1.5]"></i>
                            </div>

                            <div class="space-y-1.5 max-w-md mx-auto">
                                <h3 class="font-bold text-lg sm:text-xl text-slate-900 dark:text-white">
                                    Tidak Ada Produk yang Ditemukan
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Coba ubah kata kunci pencarian, sesuaikan rentang harga, atau reset filter kategori
                                    untuk melihat seluruh produk UMKM.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                                <a href="{{ route('marketplace.search') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs sm:text-sm font-semibold shadow-md active:scale-[0.98] transition min-h-[44px]">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    <span>Reset Semua Filter</span>
                                </a>
                                <a href="{{ route('marketplace.index') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] bg-slate-100 dark:bg-white/10 hover:bg-slate-200 dark:hover:bg-white/15 text-slate-900 dark:text-white text-xs sm:text-sm font-semibold transition min-h-[44px]">
                                    <i data-lucide="store" class="w-4 h-4"></i>
                                    <span>Kembali ke Beranda</span>
                                </a>
                            </div>
                        </div>
                    @endif

                </main>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- MOBILE FILTER BOTTOM SHEET DRAWER --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <div x-cloak x-show="mobileFilterOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 lg:hidden"
            @click="mobileFilterOpen = false">
        </div>

        <div x-cloak x-show="mobileFilterOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="fixed inset-x-0 bottom-0 max-h-[85vh] bg-white dark:bg-[#0E172F] rounded-t-[28px] shadow-2xl z-50 overflow-hidden flex flex-col lg:hidden border-t border-slate-200 dark:border-white/10">

            {{-- Drawer Handle & Header --}}
            <div class="px-5 pt-3 pb-3 border-b border-slate-200/80 dark:border-white/10 shrink-0">
                <div class="w-10 h-1.5 bg-slate-300 dark:bg-white/20 rounded-full mx-auto mb-3"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-bold text-base text-slate-900 dark:text-white">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Filter Produk</span>
                    </div>
                    <button type="button" @click="mobileFilterOpen = false"
                        class="p-1.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- Drawer Scrollable Content --}}
            <div class="p-5 overflow-y-auto space-y-6 flex-1 text-xs">

                {{-- Mobile Categories --}}
                <div class="space-y-2.5">
                    <h4 class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kategori Produk</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('marketplace.search', request()->except(['kategori', 'page'])) }}"
                            class="p-2.5 rounded-[12px] text-xs font-semibold flex items-center gap-2 transition {{ empty($category) || $category === 'semua' ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/10 text-slate-900 dark:text-white' }}">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                            <span>Semua</span>
                        </a>
                        @foreach ($categories as $catKey => $catData)
                            <a href="{{ route('marketplace.search', array_merge(request()->except(['kategori', 'page']), ['kategori' => $catKey])) }}"
                                class="p-2.5 rounded-[12px] text-xs font-medium flex items-center justify-between gap-1 transition {{ $category === $catKey ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'bg-slate-100 dark:bg-white/10 text-slate-900 dark:text-white' }}">
                                <div class="flex items-center gap-1.5 truncate">
                                    <i data-lucide="{{ $catData['icon'] }}" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span class="truncate">{{ $catData['label'] }}</span>
                                </div>
                                <span
                                    class="text-[10px] px-1.5 py-0.5 rounded-full {{ $category === $catKey ? 'bg-white/20' : 'bg-slate-200 dark:bg-white/10' }}">
                                    {{ $catData['count'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Mobile Price Range --}}
                <div class="space-y-3 pt-3 border-t border-slate-200/80 dark:border-white/10">
                    <h4 class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Batas Harga</h4>
                    <form method="GET" action="{{ route('marketplace.search') }}" class="space-y-2.5">
                        @if ($search !== '')
                            <input type="hidden" name="q" value="{{ $search }}">
                        @endif
                        @if ($category && $category !== 'semua')
                            <input type="hidden" name="kategori" value="{{ $category }}">
                        @endif
                        @if ($type)
                            <input type="hidden" name="tipe" value="{{ $type }}">
                        @endif
                        @if ($sort && $sort !== 'terbaru')
                            <input type="hidden" name="urut" value="{{ $sort }}">
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative flex items-center bg-slate-50 dark:bg-[#070A14] rounded-[12px] px-3 py-2 border border-slate-200 dark:border-white/10">
                                <span class="text-xs font-bold text-slate-400 mr-1.5">Rp</span>
                                <input type="number" name="min_harga" value="{{ $minPrice }}" placeholder="Min"
                                    min="0" step="1000"
                                    class="w-full bg-transparent border-0 p-0 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none tabular-nums">
                            </div>
                            <div class="relative flex items-center bg-slate-50 dark:bg-[#070A14] rounded-[12px] px-3 py-2 border border-slate-200 dark:border-white/10">
                                <span class="text-xs font-bold text-slate-400 mr-1.5">Rp</span>
                                <input type="number" name="max_harga" value="{{ $maxPrice }}" placeholder="Maks"
                                    min="0" step="1000"
                                    class="w-full bg-transparent border-0 p-0 text-[16px] sm:text-xs text-slate-900 dark:text-white focus:outline-none tabular-nums">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full py-2.5 rounded-[12px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm min-h-[44px]">
                            Terapkan Rentang Harga
                        </button>
                    </form>
                </div>

                {{-- Mobile Product Type --}}
                <div class="space-y-2 pt-3 border-t border-slate-200/80 dark:border-white/10">
                    <h4 class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipe Produk</h4>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($typeOptions as $tKey => $tData)
                            <a href="{{ route('marketplace.search', array_merge(request()->except(['tipe', 'page']), $tKey ? ['tipe' => $tKey] : [])) }}"
                                class="p-2.5 rounded-[12px] text-xs font-medium flex items-center gap-2 transition {{ $type === $tKey ? 'bg-[#007AFF] text-white font-semibold' : 'bg-slate-100 dark:bg-white/10 text-slate-900 dark:text-white' }}">
                                <i data-lucide="{{ $tData['icon'] }}" class="w-3.5 h-3.5"></i>
                                <span>{{ $tData['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Drawer Sticky Actions --}}
            <div class="p-4 border-t border-slate-200/80 dark:border-white/10 bg-slate-50 dark:bg-[#070A14] flex items-center gap-3 shrink-0">
                <a href="{{ route('marketplace.search') }}"
                    class="flex-1 py-3 px-4 rounded-[14px] bg-slate-200 dark:bg-white/10 text-center text-xs font-semibold text-slate-900 dark:text-white transition min-h-[44px] flex items-center justify-center">
                    Reset Semua
                </a>
                <button type="button" @click="mobileFilterOpen = false"
                    class="flex-1 py-3 px-4 rounded-[14px] bg-[#007AFF] text-center text-xs font-semibold text-white shadow-md transition min-h-[44px] flex items-center justify-center">
                    Tutup & Tampilkan
                </button>
            </div>

        </div>
    </div>
@endsection
