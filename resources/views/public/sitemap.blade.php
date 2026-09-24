@extends('layouts.public_marketing')

@section('title', 'Peta Situs (HTML Sitemap) | COOCA')
@section('description', 'Daftar lengkap seluruh halaman resmi COOCA: modul POS, ERP, kalkulator bisnis gratis, solusi industri, template pembukuan, dan direktori bisnis.')
@section('keywords', 'sitemap cooca, peta situs, navigasi Cooca, daftar kalkulator bisnis, direktori software kasir')

@section('og_title', 'Peta Situs (HTML Sitemap) | COOCA')
@section('og_description', 'Daftar lengkap struktur navigasi, solusi industri, alat kalkulator, dan halaman resmi COOCA.')

@section('content')
    <div class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">
        
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ TYPE D SITEMAP HERO (Midnight #060B1E Bento Canvas - No Breadcrumb) ══ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <header class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none"></div>

            <div class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14 space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Column: Copy, Stats & XML Action (7 Cols) -->
                    <div class="lg:col-span-7 space-y-5 text-left">
                        {{-- Typographic Overline Kicker with Pulse Dot --}}
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8] flex items-center gap-1.5">
                                <i data-lucide="map" class="w-4 h-4"></i>
                                <span>Arsitektur Informasi &amp; Navigasi Publik</span>
                            </p>
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Peta Situs Resmi <span class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Ekosistem COOCA</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Daftar lengkap seluruh halaman publik, modul kalkulator bisnis gratis, solusi per industri UMKM, dan pustaka edukasi operasional yang terindeks resmi di platform COOCA.
                        </p>

                        <!-- Meta Pills & XML Sitemap Link -->
                        <div class="pt-1 flex flex-wrap items-center gap-2.5 text-xs text-slate-300 w-full">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 border border-white/10 font-mono">
                                <i data-lucide="link-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Total URL: <strong class="text-white font-bold">{{ $totalUrls }}</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 border border-white/10 font-mono">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>sitemaps.org/0.9</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-medium">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                <span>Auto-Sync XML</span>
                            </div>
                            <a href="{{ url('/sitemap.xml') }}" target="_blank" rel="noopener"
                                class="h-8 px-3.5 rounded-xl text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0066DF] transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-[0.98]">
                                <i data-lucide="file-code-2" class="w-3.5 h-3.5"></i>
                                <span>Buka Raw XML</span>
                                <i data-lucide="external-link" class="w-3 h-3 text-white/70"></i>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Terindeks Otomatis</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Struktur Semantik W3C</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>SEO Ready</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Simulated Ecosystem Architecture Matrix Bento Cockpit (5 Cols) -->
                    <div class="lg:col-span-5 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-4 sm:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                    <span class="text-xs font-mono font-semibold text-slate-300 ml-2">Indeks Pilar Ekosistem</span>
                                </div>
                                <span class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <i data-lucide="layers" class="w-3 h-3"></i> 5 Pilar Terpadu
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1">
                                    <div class="w-7 h-7 rounded-lg bg-[#007AFF]/15 text-[#00C4D8] flex items-center justify-center font-bold">
                                        <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-xs">Platform BOS</div>
                                    <div class="text-[10px] text-slate-300">Overview, why-cooca, alur kerja</div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center font-bold">
                                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-xs">Omnichannel ERP</div>
                                    <div class="text-[10px] text-slate-300">POS, stok, keuangan, CRM</div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1">
                                    <div class="w-7 h-7 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center font-bold">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-xs">Content Studio</div>
                                    <div class="text-[10px] text-slate-300">Jadwal medsos, auto-post</div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1">
                                    <div class="w-7 h-7 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center font-bold">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-xs">Marketplace UMKM</div>
                                    <div class="text-[10px] text-slate-300">Katalog produk, profil toko</div>
                                </div>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>XML Feed: <strong class="text-emerald-400">Live</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="map" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Verified Site Architecture</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </header>

        <!-- Main Directory Grid Content -->
        <main class="py-12 lg:py-16 px-4 sm:px-6 lg:px-8 max-w-[1250px] mx-auto space-y-12">

            <!-- Sitemap Grid by Categories (Bento Modular Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($groupedUrls as $category => $items)
                    <div
                        class="rounded-[20px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-6 shadow-xs hover:border-[#007AFF]/30 flex flex-col justify-between transition-all duration-200">
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
