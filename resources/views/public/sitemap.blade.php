@extends('layouts.public_marketing')

@section('title', 'Peta Situs (HTML Sitemap) | COOCA')
@section('description', 'Daftar lengkap seluruh halaman resmi COOCA: modul POS, ERP, kalkulator bisnis gratis, solusi industri, template pembukuan, dan direktori bisnis.')
@section('keywords', 'sitemap cooca, peta situs, navigasi Cooca, daftar kalkulator bisnis, direktori software kasir')

@section('og_title', 'Peta Situs (HTML Sitemap) | COOCA')
@section('og_description', 'Daftar lengkap struktur navigasi, solusi industri, alat kalkulator, dan halaman resmi COOCA.')

@section('content')
    <div class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">
        
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ TYPE D SITEMAP HEADER (Calm, Precise, System Navigation) ═════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <header class="bg-white dark:bg-[#1C1C1E] border-b border-black/[0.06] dark:border-white/[0.08] pt-8 sm:pt-12 pb-8 sm:pb-10 transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Breadcrumbs -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 overflow-x-auto py-1">
                    <a href="{{ route('landing') }}" class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors shrink-0">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0"></i>
                    <span class="text-slate-900 dark:text-white font-semibold shrink-0">Peta Situs</span>
                </nav>

                <div class="space-y-3">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-2">
                        <i data-lucide="map" class="w-4 h-4"></i>
                        <span>Arsitektur &amp; Navigasi Publik</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.2]">
                        Peta Situs Resmi COOCA
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed max-w-3xl font-normal pt-1">
                        Daftar lengkap seluruh halaman publik, modul kalkulator bisnis gratis, solusi per industri UMKM, dan pustaka edukasi operasional yang terindeks resmi di platform COOCA.
                    </p>
                </div>

                <!-- Meta Pills & XML Sitemap Link -->
                <div class="pt-2 flex flex-wrap items-center justify-between gap-4 border-t border-black/[0.06] dark:border-white/[0.08] pt-4">
                    <div class="flex flex-wrap items-center gap-2.5 text-xs text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-slate-100 dark:bg-white/[0.06] font-mono">
                            <i data-lucide="link-2" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Total URL: <strong class="text-slate-900 dark:text-white font-bold">{{ $totalUrls }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-slate-100 dark:bg-white/[0.06] font-mono">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <span>Schema: sitemaps.org/0.9</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-medium">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Update Harian Otomatis</span>
                        </div>
                    </div>

                    <a href="{{ url('/sitemap.xml') }}" target="_blank" rel="noopener"
                        class="h-9 px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-blue-50 hover:bg-blue-100 dark:bg-blue-500/10 dark:hover:bg-blue-500/20 transition-all inline-flex items-center gap-2 cursor-pointer shadow-xs active:scale-[0.98]">
                        <i data-lucide="file-code-2" class="w-3.5 h-3.5"></i>
                        <span>Lihat Raw Sitemap XML</span>
                        <i data-lucide="external-link" class="w-3 h-3 text-[#007AFF]/60 dark:text-[#0A84FF]/60"></i>
                    </a>
                </div>

            </div>
        </header>

        <!-- Main Directory Grid Content -->
        <main class="py-12 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-[1250px] mx-auto space-y-12">

            <!-- Sitemap Grid by Categories (Bento Modular Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($groupedUrls as $category => $items)
                    <div
                        class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-xs hover:border-[#007AFF]/30 flex flex-col justify-between transition-all duration-200">
                        <div>
                            <div
                                class="flex items-center justify-between gap-3 pb-3.5 mb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                                <h2 class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-2 min-w-0 flex-1 leading-snug break-words">
                                    @if (str_contains(strtolower($category), 'kalkulator'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="calculator" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'solusi'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="target" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'template'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="file-spreadsheet" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'artikel') || str_contains(strtolower($category), 'edukasi'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="book-open" class="w-4 h-4"></i></span>
                                    @elseif(str_contains(strtolower($category), 'bisnis'))
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="store" class="w-4 h-4"></i></span>
                                    @else
                                        <span
                                            class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-slate-500 flex items-center justify-center text-sm shrink-0"><i
                                                data-lucide="link" class="w-4 h-4"></i></span>
                                    @endif
                                    <span class="truncate">{{ $category }}</span>
                                </h2>
                                <span
                                    class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-black/[0.03] dark:bg-white/[0.05] text-slate-500 dark:text-slate-400 font-semibold shrink-0">
                                    {{ count($items) }} Link
                                </span>
                            </div>

                            <div class="space-y-1.5">
                                @foreach ($items as $item)
                                    <a href="{{ $item['loc'] }}"
                                        class="group flex items-center justify-between gap-2.5 p-2 sm:p-2.5 rounded-[12px] bg-slate-50 dark:bg-white/[0.03] hover:bg-blue-50/80 dark:hover:bg-blue-500/10 border border-black/[0.03] dark:border-white/[0.05] hover:border-[#007AFF]/30 transition-all text-xs text-[#1D1D1F] dark:text-[#F5F5F7] min-h-[40px]">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#007AFF]/60 group-hover:text-[#007AFF] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                                            <span class="truncate font-medium group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                                {{ $item['title'] }}
                                            </span>
                                        </div>
                                        <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-black/[0.04] dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 shrink-0" title="Prioritas SEO">
                                            P:{{ $item['priority'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- SEO Directive Summary Box (Apple Inset Card) -->
            <div
                class="p-6 sm:p-8 rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
                <h3
                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                    <span>Kebijakan Indexing Mesin Pencari (Search Engine Directives)</span>
                </h3>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    <div
                        class="p-5 rounded-[22px] bg-emerald-500/[0.05] dark:bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1.5">
                        <div class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>INDEX, FOLLOW (Publik &amp; Terindeks)</span>
                        </div>
                        <p>Seluruh halaman pada peta situs ini (Homepage, Tools Kalkulator Bisnis, Solusi Industri, Template
                            Excel, Artikel Blog, dan Halaman Profil Bisnis Aktif) diinstruksikan kepada bot Google, Bing,
                            dan bot AI untuk di-crawl dan diindeks secara penuh.</p>
                    </div>
                    <div
                        class="p-5 rounded-[22px] bg-rose-500/[0.04] dark:bg-rose-500/[0.08] border border-rose-500/20 space-y-1.5">
                        <div class="font-bold text-rose-700 dark:text-rose-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>NOINDEX, NOFOLLOW (Privasi &amp; Terlindungi)</span>
                        </div>
                        <p>Area aplikasi internal seperti Dasbor Transaksi (`/dashboard`), Kasir POS (`/pos`), Master Stok
                            Gudang (`/inventory`), Faktur (`/invoices`), Pembelian (`/purchasing`), Laporan Keuangan Rahasia
                            (`/reports`), Panel Admin (`/admin`), dan formulir autentikasi (`/login`, `/register`) secara
                            tegas dilarang untuk diindeks guna menjaga kerahasiaan data pengguna.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
