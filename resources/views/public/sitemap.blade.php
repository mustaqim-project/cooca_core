@extends('layouts.public_marketing')

@section('title', 'Peta Situs (HTML Sitemap) - Cooca')
@section('description',
    'Jelajahi seluruh halaman resmi Cooca: kalkulator bisnis gratis, panduan HPP & BEP, solusi
    kasir per industri, template pembukuan Excel, dan direktori bisnis.')
@section('keywords',
    'sitemap cooca, peta situs, navigasi Cooca, daftar kalkulator bisnis, direktori software
    kasir')

@section('content')
    <main class="relative z-10 pt-8 pb-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-12">
        <!-- Header Hero -->
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <div
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-xs font-bold">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span>Arsitektur &amp; Navigasi Terbuka</span>
            </div>
            <h1
                class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight">
                Peta Situs Resmi <span class="text-[#007AFF] dark:text-[#0A84FF]">Cooca</span>
            </h1>
            <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] max-w-2xl mx-auto leading-relaxed">
                Daftar lengkap seluruh halaman publik, modul kalkulator, solusi vertikal industri, dan pustaka edukasi
                gratis yang terindeks resmi di ekosistem Cooca.
            </p>
            <div class="pt-2 inline-flex items-center gap-3 text-xs font-mono">
                <span
                    class="px-3 py-1.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06] text-[#6E6E73] dark:text-[#86868B]">
                    Total Halaman Terindeks: <strong
                        class="text-[#34C759] dark:text-[#30D158] font-bold">{{ $totalUrls }}</strong> URL
                </span>
                <a href="{{ url('/sitemap.xml') }}" target="_blank"
                    class="text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1 font-semibold">
                    <span>Lihat XML Raw</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Sitemap Grid by Categories (Bento Modular Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($groupedUrls as $category => $items)
                <div
                    class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:shadow-md hover:border-[#007AFF]/30 flex flex-col justify-between transition-all duration-200">
                    <div>
                        <div
                            class="flex items-center justify-between pb-3.5 mb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <h2 class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-2">
                                @if (str_contains(strtolower($category), 'kalkulator'))
                                    <span
                                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-sm">🧮</span>
                                @elseif(str_contains(strtolower($category), 'solusi'))
                                    <span
                                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-sm">🎯</span>
                                @elseif(str_contains(strtolower($category), 'template'))
                                    <span
                                        class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center text-sm">📑</span>
                                @elseif(str_contains(strtolower($category), 'artikel') || str_contains(strtolower($category), 'edukasi'))
                                    <span
                                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-sm">📚</span>
                                @elseif(str_contains(strtolower($category), 'bisnis'))
                                    <span
                                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-sm">🏪</span>
                                @else
                                    <span
                                        class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[#6E6E73] flex items-center justify-center text-sm">🔗</span>
                                @endif
                                <span>{{ $category }}</span>
                            </h2>
                            <span
                                class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-black/[0.03] dark:bg-white/[0.05] text-[#6E6E73] dark:text-[#86868B] font-semibold">
                                {{ count($items) }} Link
                            </span>
                        </div>

                        <ul class="space-y-2.5">
                            @foreach ($items as $item)
                                <li>
                                    <a href="{{ $item['loc'] }}"
                                        class="group flex items-start justify-between text-xs text-[#6E6E73] dark:text-[#86868B] hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">
                                        <span
                                            class="line-clamp-2 pr-2 leading-relaxed group-hover:translate-x-0.5 transition-transform">
                                            {{ $item['title'] }}
                                        </span>
                                        <span
                                            class="text-[9px] font-mono text-[#6E6E73]/60 dark:text-[#86868B]/60 shrink-0 mt-0.5"
                                            title="Prioritas SEO">
                                            P:{{ $item['priority'] }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- SEO Directive Summary Box (Apple Inset Card) -->
        <div
            class="p-6 sm:p-8 rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
            <h3
                class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                <span>Kebijakan Indexing Mesin Pencari (Search Engine Directives)</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                <div
                    class="p-5 rounded-[22px] bg-[#34C759]/[0.05] dark:bg-[#30D158]/[0.08] border border-[#34C759]/20 space-y-1.5">
                    <div class="font-bold text-[#34C759] dark:text-[#30D158] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] dark:bg-[#30D158]"></span>
                        <span>INDEX, FOLLOW (Publik &amp; Terindeks)</span>
                    </div>
                    <p>Seluruh halaman pada peta situs ini (Homepage, Tools Kalkulator Bisnis, Solusi Industri, Template
                        Excel, Artikel Blog, dan Halaman Profil Bisnis Aktif) diinstruksikan kepada bot Google, Bing, dan
                        bot AI untuk di-crawl dan diindeks secara penuh.</p>
                </div>
                <div
                    class="p-5 rounded-[22px] bg-[#FF3B30]/[0.04] dark:bg-[#FF453A]/[0.08] border border-[#FF3B30]/20 space-y-1.5">
                    <div class="font-bold text-[#FF3B30] dark:text-[#FF453A] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#FF3B30] dark:bg-[#FF453A]"></span>
                        <span>NOINDEX, NOFOLLOW (Privasi &amp; Terlindungi)</span>
                    </div>
                    <p>Area aplikasi internal seperti Dasbor Transaksi (`/dashboard`), Kasir POS (`/pos`), Master Stok
                        Gudang (`/inventory`), Faktur (`/invoices`), Pembelian (`/purchasing`), Laporan Keuangan Rahasia
                        (`/reports`), Panel Admin (`/admin`), dan formulir autentikasi (`/login`, `/register`) secara tegas
                        dilarang untuk diindeks guna menjaga kerahasiaan data pengguna.</p>
                </div>
            </div>
        </div>
    </main>
@endsection
