@extends('layouts.public_marketing')

@section('title', 'Peta Situs (HTML Sitemap) - Cooca')
@section('description',
    'Jelajahi seluruh halaman resmi Cooca: kalkulator bisnis gratis, panduan HPP & BEP, solusi
    kasir per industri, template pembukuan Excel, dan direktori bisnis.')
@section('keywords',
    'sitemap cooca, peta situs, navigasi Cooca, daftar kalkulator bisnis, direktori software
    kasir')

@section('content')
    <div class="w-full font-sans antialiased overflow-hidden">
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout with Midnight Blue Glow) ═══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-8 sm:pt-12 pb-16 lg:pb-20 overflow-hidden border-b border-white/10">

            <!-- Subtle Ambient Background Glows -->
            <div
                class="absolute -top-24 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none -z-0">
            </div>

            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full space-y-6">

                <!-- Breadcrumbs -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 overflow-x-auto py-1">
                    <a href="{{ route('landing') }}" class="hover:text-[#00C4D8] transition-colors shrink-0">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/20 shrink-0"></i>
                    <span class="text-white font-semibold shrink-0">Peta Situs</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                    <!-- KIRI: Headline, Subtitle, Info Pills & XML Link (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div class="space-y-3">
                            <div
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold tracking-wide">
                                <i data-lucide="map" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Arsitektur &amp; Navigasi Terbuka</span>
                            </div>
                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words">
                                Peta Situs Resmi <span class="text-[#00C4D8]">Cooca</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal text-pretty break-words">
                            Daftar lengkap seluruh halaman publik, modul kalkulator, solusi vertikal industri, dan pustaka
                            edukasi gratis yang terindeks resmi di ekosistem Cooca.
                        </p>

                        <!-- Pills & Raw XML Link -->
                        <div class="pt-2 flex flex-wrap items-center gap-3 text-xs font-mono">
                            <span class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-slate-300">
                                Total URL Terindeks: <strong class="text-emerald-400 font-bold">{{ $totalUrls }}</strong>
                                URL
                            </span>
                            <a href="{{ url('/sitemap.xml') }}" target="_blank" rel="noopener"
                                class="px-4 py-2 rounded-xl bg-[#007AFF]/20 hover:bg-[#007AFF]/30 border border-[#007AFF]/40 text-[#00C4D8] hover:text-white transition-all inline-flex items-center gap-2 font-semibold">
                                <i data-lucide="file-code-2" class="w-3.5 h-3.5"></i>
                                <span>Lihat Sitemap XML</span>
                                <i data-lucide="external-link" class="w-3 h-3 text-white/50"></i>
                            </a>
                        </div>
                    </div>

                    <!-- KANAN: Sitemap Directory Topology Bento Card (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div
                            class="relative rounded-2xl bg-white/[0.04] border border-white/10 backdrop-blur-xl p-6 shadow-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                    <span class="text-xs font-mono text-slate-400 ml-2">cooca://sitemap.index</span>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[11px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    HTTP 200 OK
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                    <div class="text-[11px] text-slate-400 font-mono">Modul Publik</div>
                                    <div class="text-white font-bold flex items-center justify-between">
                                        <span>Kalkulator &amp; Tools</span>
                                        <i data-lucide="calculator" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                    <div class="text-[11px] text-slate-400 font-mono">Vertikal Industri</div>
                                    <div class="text-white font-bold flex items-center justify-between">
                                        <span>12+ Solusi Kasir</span>
                                        <i data-lucide="target" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                    <div class="text-[11px] text-slate-400 font-mono">Aset Pembukuan</div>
                                    <div class="text-white font-bold flex items-center justify-between">
                                        <span>Template Excel</span>
                                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-amber-400"></i>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                    <div class="text-[11px] text-slate-400 font-mono">Edukasi &amp; Toko</div>
                                    <div class="text-white font-bold flex items-center justify-between">
                                        <span>Blog &amp; Profil UMKM</span>
                                        <i data-lucide="store" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Protocol Notice -->
                            <div
                                class="pt-2 flex items-center justify-between text-[11px] text-slate-400 font-mono border-t border-white/10">
                                <span>Schema: sitemaps.org/0.9</span>
                                <span>Update: Harian Otomatis</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Main Directory Grid Content -->
        <main class="relative z-10 py-12 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-[1250px] mx-auto space-y-12">

            <!-- Sitemap Grid by Categories (Bento Modular Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($groupedUrls as $category => $items)
                    <div
                        class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:shadow-md hover:border-[#007AFF]/30 flex flex-col justify-between transition-all duration-200">
                        <div>
                            <div
                                class="flex items-center justify-between gap-3 pb-3.5 mb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                                <h2 class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-2 min-w-0 flex-1 leading-snug break-words">
                                    @if (str_contains(strtolower($category), 'kalkulator'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="calculator" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'solusi'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="target" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'template'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="file-spreadsheet" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'artikel') || str_contains(strtolower($category), 'edukasi'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="book-open" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'bisnis'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="store" class="w-4 h-4"></i></span>
                                    @else
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[#6E6E73] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="link" class="w-4 h-4"></i></span>
                                    @endif
                                    <span class="truncate">{{ $category }}</span>
                                </h2>
                                <span
                                    class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-black/[0.03] dark:bg-white/[0.05] text-[#6E6E73] dark:text-[#86868B] font-semibold shrink-0">
                                    {{ count($items) }} Link
                                </span>
                            </div>

                            <ul class="space-y-2.5">
                                @foreach ($items as $item)
                                    <li>
                                        <a href="{{ $item['loc'] }}"
                                            class="group flex items-start justify-between gap-2 text-xs text-[#6E6E73] dark:text-[#86868B] hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">
                                            <span
                                                class="min-w-0 flex-1 line-clamp-2 pr-2 leading-relaxed group-hover:translate-x-0.5 transition-transform break-words">
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
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    <div
                        class="p-5 rounded-[22px] bg-[#34C759]/[0.05] dark:bg-[#30D158]/[0.08] border border-[#34C759]/20 space-y-1.5">
                        <div class="font-bold text-[#34C759] dark:text-[#30D158] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] dark:bg-[#30D158]"></span>
                            <span>INDEX, FOLLOW (Publik &amp; Terindeks)</span>
                        </div>
                        <p>Seluruh halaman pada peta situs ini (Homepage, Tools Kalkulator Bisnis, Solusi Industri, Template
                            Excel, Artikel Blog, dan Halaman Profil Bisnis Aktif) diinstruksikan kepada bot Google, Bing,
                            dan
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
                            (`/reports`), Panel Admin (`/admin`), dan formulir autentikasi (`/login`, `/register`) secara
                            tegas
                            dilarang untuk diindeks guna menjaga kerahasiaan data pengguna.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
