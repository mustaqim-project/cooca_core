@extends('layouts.public_marketing')

@section('title', 'Software ERP Terintegrasi untuk UMKM & Bisnis Berkembang | COOCA')
@section('description', 'COOCA Omnichannel ERP menyatukan kasir POS, stok multi-gudang, purchasing, keuangan, akuntansi, CRM, dan karyawan dalam satu sistem terpadu tanpa biaya mahal.')
@section('keywords', 'software erp umkm, omnichannel erp indonesia, sistem erp toko, software manajemen operasional terintegrasi, erp kasir gudang akuntansi')

@section('og_title', 'Software ERP Terintegrasi untuk UMKM & Bisnis Berkembang | COOCA')
@section('og_description', 'COOCA Omnichannel ERP menyatukan kasir POS, persediaan gudang, purchasing, akuntansi riil, dan CRM dalam satu sistem terpadu.')

@push('seo')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "COOCA Omnichannel ERP",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud, Android, iOS",
        "description": "Sistem Enterprise Resource Planning (ERP) modular dan terintegrasi untuk bisnis berkembang dan UMKM Indonesia.",
        "url": "{{ route('public.erp.erp') }}",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR"
        },
        "publisher": {
            "@type": "Organization",
            "name": "COOCA Indonesia",
            "url": "{{ url('/') }}"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Beranda",
                "item": "{{ route('landing') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Omnichannel ERP",
                "item": "{{ route('public.erp.erp') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Core ERP",
                "item": "{{ route('public.erp.erp') }}"
            }
        ]
    }
    </script>
@endpush

@section('content')
    <div class="w-full bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Full Viewport 50/50 Ratio - Apple HIG Cockpit) ══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">

            <!-- Subtle Ambient Background Glows (Pure CSS, No Heavy Images) -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Safe from Fixed Bottom Nav on Mobile) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-3 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-6 sm:pb-24 lg:py-14">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-6 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile and Desktop ~ 6 Cols) -->
                    <div
                        class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Breadcrumb & Overline Kicker -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                    Unified ERP Architecture &amp; Modular Engine
                                </p>
                            </div>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Software ERP Lengkap <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Tanpa
                                    Kerumitan Korporasi</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Kendalikan rantai pasok, stok barang, kasir toko, pembelian supplier, hingga pembukuan akuntansi dalam satu sistem terintegrasi. Rapi tanpa biaya lisensi ratusan juta rupiah.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('register') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Mulai Pakai ERP Gratis</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.pos') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Lihat Fitur Kasir POS</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>ERP</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>POS</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>BOM</span>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center">
                                <div class="flex items-center gap-1 text-amber-400">
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <span class="text-xs sm:text-sm font-extrabold text-white ml-1 tabular-nums">4.9 /
                                        5.0</span>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">8 Modul Terpadu &amp; Terintegrasi Penuh</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Modular On-Demand</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Standar SAK EMKM</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Multi-Cabang &amp; Gudang</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated ERP Matrix Cockpit UI -->
                    <div class="lg:col-span-6 relative w-full max-w-xl mx-auto lg:max-w-none">
                        <!-- Ambient Spotlight Glow behind the Terminal Window -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] sm:rounded-[36px] blur-2xl sm:blur-3xl pointer-events-none -z-10">
                        </div>

                        <!-- Mobile Live Dynamic Island Metric Strip (Clean, non-colliding, zero overlap on Mobile) -->
                        <div class="flex sm:hidden items-center justify-between gap-2 mb-2 w-full">
                            <!-- Mobile Left Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-slate-400 font-medium">Modul</span>
                                <span class="font-extrabold text-white">8 Terhubung</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="layers" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span class="text-slate-400 font-medium">Sync</span>
                                <span class="font-extrabold text-white">24/7 Otomatis</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Ekosistem Terpadu (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Ekosistem ERP</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">8 Modul Aktif</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>Sinkronisasi Otomatis 24/7</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: Zero Integrasi (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Arsitektur Terpusat</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">Zero Manual</div>
                            <div class="text-[11px] font-semibold text-[#00C4D8] flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="database" class="w-3 h-3"></i>
                                <span>Single Source of Truth</span>
                            </div>
                        </div>

                        <!-- Main Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-2.5 sm:p-4 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-2.5 sm:space-y-3 text-white relative z-10 overflow-hidden mb-6 sm:mb-0">

                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <!-- Mobile Window Header (sm:hidden - Clean title & status, zero truncation) -->
                            <div class="flex sm:hidden items-center justify-between border-b border-white/10 pb-2 gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">ERP Ecosystem &bull; 8 Modul</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold shrink-0">
                                    Terpadu
                                </span>
                            </div>

                            <!-- Tablet & Desktop macOS Window Title Bar (hidden sm:flex) -->
                            <div class="hidden sm:flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]/80"></span>
                                    <div
                                        class="ml-2 flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-[11px] text-slate-300 font-mono">
                                        <i data-lucide="lock" class="w-2.5 h-2.5 text-emerald-400"></i>
                                        <span>cooca.id/app/erp/ecosystem-matrix</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span class="text-[11px] font-medium text-emerald-400 font-mono">8 Modul Aktif Terpadu</span>
                                </div>
                            </div>

                            <!-- 8 Mini Module Grid (High Fidelity Bento) -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs my-1 sm:my-2">
                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-[#00C2FF]/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-blue-500/20 text-[#00C2FF] flex items-center justify-center">
                                        <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">Kasir POS</div>
                                    <div class="text-[10px] text-slate-400 truncate">Cepat &amp; QRIS</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-[#34C759]/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-emerald-500/20 text-[#34C759] flex items-center justify-center">
                                        <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">Inventory</div>
                                    <div class="text-[10px] text-slate-400 truncate">Multi-Gudang</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-[#FF9500]/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-amber-500/20 text-[#FF9500] flex items-center justify-center">
                                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">Purchasing</div>
                                    <div class="text-[10px] text-slate-400 truncate">PO &amp; Hutang</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-[#007AFF]/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-sky-500/20 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">Finance</div>
                                    <div class="text-[10px] text-slate-400 truncate">Kas &amp; Piutang</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-emerald-400/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">Accounting</div>
                                    <div class="text-[10px] text-slate-400 truncate">Jurnal &amp; Neraca</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-purple-400/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-purple-500/20 text-purple-400 flex items-center justify-center">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">CRM Pelanggan</div>
                                    <div class="text-[10px] text-slate-400 truncate">Poin &amp; Loyalitas</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-rose-400/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-rose-500/20 text-rose-400 flex items-center justify-center">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">HRM Karyawan</div>
                                    <div class="text-[10px] text-slate-400 truncate">Presensi &amp; Gaji</div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-[12px] bg-white/[0.04] border border-white/10 space-y-1 hover:border-cyan-400/40 transition-colors">
                                    <div class="w-6 h-6 rounded-[6px] bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="font-bold text-white text-[11px] sm:text-xs truncate">Analytics</div>
                                    <div class="text-[10px] text-slate-400 truncate">Laba Rugi Riil</div>
                                </div>
                            </div>

                            <div
                                class="pt-2 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                <span>Zero Integrasi Manual</span>
                                <span class="text-emerald-400 font-semibold">Semua Modul Berkomunikasi 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. KENAPA ERP TRADISIONAL TIDAK COCOK UNTUK UMKM ════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#FF3B30]">Masalah ERP Konvensional</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Mengapa Kebanyakan Software ERP Gagal Diterapkan di Bisnis Berkembang?
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Banyak pengusaha mencoba beralih ke software ERP korporat, namun berujung proyek mangkrak dan uang
                        terbuang sia-sia karena tiga kendala utama.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">Biaya Awal Selangit</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            ERP tradisional mematok biaya implementasi puluhan hingga ratusan juta rupiah di muka, ditambah
                            biaya konsultan per jam dan biaya lisensi per user yang sangat membebani cash flow bisnis UMKM.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">Terlalu Rumit untuk Staf Toko</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Formulir puluhan kolom dan navigasi kaku membuat kasir dan staf gudang frustrasi. Akhirnya,
                            karyawan kembali mencatat di kertas atau Excel karena sistem baru terlalu lambat dan
                            membingungkan.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="hourglass" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">Implementasi Berbulan-bulan</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Proses setup memakan waktu 3-6 bulan hanya untuk kustomisasi modul. Bisnis Anda membutuhkan
                            solusi yang langsung siap dipakai dalam hitungan menit tanpa menghentikan operasional toko yang
                            sedang berjalan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. INTEGRASI LINTAS FUNGSI: POS KE AKUNTANSI ═════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-2xl space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Traceability
                        Utuh</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Satu Siklus Bisnis Tertutup (Closed-Loop Workflow)
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Bagaimana alur pengadaan barang supplier terhubung mulus hingga kasir dan laporan keuangan akhir
                        bulan.
                    </p>
                </div>

                <!-- Bento 4-Column Horizontal Step Chain -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-[#007AFF]">01 &bull; PURCHASING</span>
                        <h3 class="text-base font-bold">Order Pembelian Supplier</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Buat Purchase Order (PO) resmi ke supplier. Saat barang tiba, sistem memverifikasi kesesuaian
                            faktur dan mencatat hutang usaha (AP).
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-[#34C759]">02 &bull; GUDANG & STOK</span>
                        <h3 class="text-base font-bold">Penerimaan & Kartu Stok</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Barang masuk otomatis menambah kuantitas di gudang yang ditentukan. Nilai HPP modal rata-rata
                            tertimbang (FIFO/Average) terhitung presisi.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-[#FF9500]">03 &bull; KASIR POS</span>
                        <h3 class="text-base font-bold">Penjualan di Kasir Toko</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Barang dijual lewat kasir POS. Stok langsung terpotong, struk tercetak, dan uang kas masuk
                            diverifikasi sesuai metode pembayaran.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-purple-500">04 &bull; KEUANGAN & LABA</span>
                        <h3 class="text-base font-bold">Laporan Laba Rugi Terbit</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pendapatan, biaya HPP, dan margin laba bersih terakumulasi di buku besar akuntansi dan tersaji
                            di dasbor keuangan owner.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ SEPUTAR OMNICHANNEL ERP ═══════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Tanya
                        Jawab</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Pertanyaan Seputar COOCA Omnichannel ERP
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Ketahui kapabilitas dan batasan sistem agar sesuai dengan kebutuhan bisnis Anda.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah COOCA ID bisa digunakan jika saya punya beberapa cabang toko fisik?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Bisa. COOCA mendukung pengelolaan multi-outlet dan multi-gudang. Anda dapat memantau stok tiap
                            cabang, melakukan transfer stok antar cabang dengan surat jalan resmi, dan melihat laporan laba
                            rugi konsolidasi maupun per cabang secara terpisah.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah staf saya dibatasi aksesnya agar tidak melihat data rahasia?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Ya. COOCA dilengkapi sistem Role-Based Access Control (RBAC). Staf kasir hanya bisa mengakses
                            terminal kasir untuk menjual produk. Staf gudang hanya bisa melihat mutasi stok. Laporan
                            keuangan, margin laba, dan data sensitif lainnya hanya dapat dibuka oleh akun bertingkat Manajer
                            atau Owner.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah laporan pembukuan di COOCA sudah sesuai standar akuntansi?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Ya. COOCA mengadopsi standar Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK
                            EMKM) dengan bagan akun standar (Chart of Accounts), jurnal umum ganda, buku besar, neraca
                            saldo, dan laporan laba rugi yang siap dicetak untuk keperluan pajak atau pengajuan modal bank.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. RELATED ERP MODULES ═══════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Modul
                            Terkait</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight">Pelajari Modul Inti di Dalam ERP COOCA
                        </h3>
                    </div>
                    <a href="{{ route('public.pricing') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline">
                        <span>Lihat Harga Paket ERP</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    <a href="{{ route('public.erp.pos') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="monitor" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#007AFF] transition-colors">Point of Sale (POS)
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Kasir kilat, cetak
                            struk Bluetooth, barcode scan, & mutasi kas harian.</p>
                    </a>

                    <a href="{{ route('public.erp.inventory') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="boxes" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#34C759] transition-colors">Software Inventory
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Multi-gudang, kartu
                            stok perpetual, resep BOM, & reorder point.</p>
                    </a>

                    <a href="{{ route('public.erp.finance') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#FF9500] transition-colors">Manajemen Finance
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Catat arus kas
                            masuk-keluar, piutang, dan hutang operasional.</p>
                    </a>

                    <a href="{{ route('public.erp.accounting') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-emerald-500/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-emerald-500 transition-colors">Pembukuan Akuntansi
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Buku besar, neraca
                            saldo, dan laporan laba rugi otomatis.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. BOTTOM CTA ════════════════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div
                        class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#007AFF]/20 rounded-full blur-[140px] pointer-events-none">
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-8 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">Transformasi
                                Operasional</span>
                            <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                                Waktunya Mengelola Bisnis dengan ERP yang Ringan dan Handal
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed">
                                Mulai gratis tanpa kartu kredit. Aktifkan modul kasir dan persediaan toko Anda hari ini dan
                                rasakan efisiensi operasional tanpa batas.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Daftar Akun ERP Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.demo') }}"
                                class="w-full py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 transition-all min-h-[44px]">
                                <span>Coba Demo Interaktif</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
