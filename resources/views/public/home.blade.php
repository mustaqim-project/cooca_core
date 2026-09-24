@extends('layouts.public_marketing')

@section('title', 'COOCA - Business Operating System & Omnichannel ERP Terpadu')
@section('description',
    'Kelola operasional bisnis, kasir POS, stok inventaris, keuangan laba rugi, CRM pelanggan,
    marketplace, WhatsApp, dan otomasi dalam satu platform terhubung.')
@section('keywords',
    'business operating system, omnichannel erp indonesia, software kasir terintegrasi, aplikasi
    manajemen bisnis umkm, sistem operasional terpadu, software pos multi outlet')

@section('canonical', route('landing'))
@section('og_title', 'COOCA - Business Operating System & Omnichannel ERP Terpadu')
@section('og_description', 'Kendalikan seluruh bisnis Anda dari satu ekosistem: kasir, inventaris gudang, keuangan, marketplace, dan otomasi WhatsApp.')
@section('og_type', 'website')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Business Operating System & Omnichannel ERP untuk UMKM: Operasional, Penjualan, Keuangan, Inventory, Social Media, Marketplace, dan Otomasi.",
      "url": "{{ url('/') }}",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- 1. HERO SECTION (Midnight #060B1E Full-Bleed) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 min-h-[calc(100svh-84px)] lg:flex lg:items-center overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {{-- Left: Eyebrow, Headline, Value Proposition, Action CTAs --}}
                    <div class="lg:col-span-7 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Business Operating System &amp; Omnichannel ERP
                            </p>

                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Satu Sistem untuk <span class="text-[#00C4D8]">Mengendalikan Seluruh Bisnis Anda</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-[32rem] lg:max-w-xl font-normal text-pretty break-words mx-auto lg:mx-0">
                            Kelola operasional kasir, stok gudang, pembukuan keuangan, pelanggan, marketplace online,
                            komunikasi WhatsApp, hingga otomasi dalam satu platform yang saling terhubung tanpa jeda.
                        </p>

                        {{-- Tangible Proof Points (Centered on Mobile) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 w-full text-left">
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Bukan sekadar aplikasi POS kasir biasa</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Hentikan ketik ulang di spreadsheet
                                    terpisah</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Akses fleksibel dari HP, tablet, &amp; laptop</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Laba bersih &amp; arus kas terpantau real-time</span>
                            </div>
                        </div>

                        {{-- Action Buttons (Centered on Mobile) --}}
                        <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('register') }}"
                                class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Mulai Pakai COOCA</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('public.bos.how-it-works') }}"
                                class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] backdrop-blur-sm min-h-[48px]">
                                <span>Lihat Cara Kerja</span>
                            </a>
                            <a href="{{ route('public.demo') }}"
                                class="h-12 px-5 rounded-[14px] text-[#00C4D8] hover:text-white text-sm font-semibold flex items-center justify-center gap-1.5 transition min-h-[48px]">
                                <i data-lucide="play" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                <span>Coba Live Demo</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right: Master Operating System Bento Deck --}}
                    <div class="lg:col-span-5">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-5 sm:p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-5">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse shrink-0"></span>
                                    <span class="text-xs font-mono font-bold text-white truncate">COOCA Business OS • Live
                                        Pulse</span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-0.5 rounded-full shrink-0">
                                    4 Modul Terhubung
                                </span>
                            </div>

                            {{-- Multi-Channel Transaction Feed Simulation --}}
                            <div class="space-y-3 text-xs">
                                <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-2">
                                    <div class="flex justify-between items-start font-semibold text-white gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-2 h-2 rounded-full bg-[#007AFF] shrink-0"></span>
                                            <span class="truncate">Kasir POS Toko #TRX-8802</span>
                                        </div>
                                        <span class="font-mono text-emerald-400 font-bold shrink-0">+ Rp 185.000</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between text-[11px] text-slate-300 font-mono pt-1 border-t border-dashed border-white/10 gap-2">
                                        <span class="truncate">Gudang: 3 Item Terpotong</span>
                                        <span class="text-[#00C4D8] shrink-0">Jurnal Kasir Lunas</span>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 space-y-2">
                                    <div class="flex justify-between items-start font-semibold text-white gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-2 h-2 rounded-full bg-purple-400 shrink-0"></span>
                                            <span class="truncate">Pesanan Storefront Online #ORD-104</span>
                                        </div>
                                        <span class="font-mono text-emerald-400 font-bold shrink-0">+ Rp 320.000</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between text-[11px] text-slate-300 font-mono pt-1 border-t border-dashed border-white/10 gap-2">
                                        <span class="truncate">Resi Kurir Biteship Siap</span>
                                        <span class="text-emerald-400 shrink-0">Notif WA Terkirim</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Real-Time Owner Overview Breakdown --}}
                            <div class="p-4 rounded-[16px] bg-[#060B1E]/80 border border-white/10 text-white space-y-2.5">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Arus Kas Masuk Hari Ini</span>
                                    <span class="font-mono text-emerald-400 font-bold">Rp 4.820.000</span>
                                </div>
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Estimasi HPP & Beban Operasional</span>
                                    <span class="font-mono text-slate-300">- Rp 2.410.000</span>
                                </div>
                                <div
                                    class="pt-2 border-t border-white/10 flex justify-between items-center text-xs font-bold">
                                    <span class="text-slate-200">Laba Bersih Riil Owner</span>
                                    <span class="font-mono text-sm text-[#00C4D8]">+ Rp 2.410.000 (50.0%)</span>
                                </div>
                            </div>

                            {{-- Hardware & Channel Bar --}}
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-[12px] bg-white/5 border border-white/10 flex items-center gap-2">
                                    <i data-lucide="printer" class="w-4 h-4 text-[#007AFF] shrink-0"
                                        aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate">Printer Siap Cetak</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-white/5 border border-white/10 flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"
                                        aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate">Data Terisolasi Aman</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content Sections --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-20 sm:space-y-28">

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- 2. THE FRAGMENTED BUSINESS PROBLEM --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <section
                class="p-6 sm:p-10 rounded-[28px] bg-rose-500/[0.04] dark:bg-rose-500/[0.08] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Pengelolaan Bisnis
                    </span>
                    <h2
                        class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-1 tracking-tight leading-[1.2] text-balance break-words">
                        Mengapa Menggunakan Banyak Aplikasi Terpisah Menghambat Bisnis Anda?
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono shrink-0">
                            01</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Data Terpecah di Mana-Mana</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Kasir menggunakan satu aplikasi, stok dicatat di buku atau Excel, pesanan online masuk lewat
                            chat WhatsApp pribadi, dan pembukuan di software akuntansi terpisah.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono shrink-0">
                            02</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Stok Selisih & Laba Semu</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Karena sistem tidak terhubung, stok di kasir tidak mencerminkan sisa fisik di gudang. Penjualan
                            terlihat ramai setiap hari, tetapi kas akhir bulan selalu selisih tanpa jejak.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono shrink-0">
                            03</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Owner Terjebak Rutinitas Teknis</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Pemilik bisnis menghabiskan 2–3 jam setiap malam hanya untuk menyalin ulang transaksi dan
                            mencocokkan nota kasir, alih-alih fokus ekspansi cabang dan strategi penjualan.
                        </p>
                    </div>
                </div>
            </section>

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- 3. COOCA AS ONE BUSINESS OPERATING SYSTEM --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Konsep Business Operating System
                    </span>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                        Bagaimana COOCA Menyatukan Seluruh Lapisan Bisnis
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold shrink-0">
                            1</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Omnichannel Sales Layer</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            POS kasir toko fisik, storefront katalog online, pesanan QR meja, dan pesanan marketplace
                            bermuara ke satu antrean pemrosesan order terpusat.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                            2</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Operational & Inventory Engine</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Kartu stok perpetual, konversi multi-satuan dus/pcs, resep bahan baku (BOM) kuliner, dan
                            perintah kerja SPK bengkel/manufaktur terpotong otomatis.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">
                            3</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Financial & Accounting Core</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Setiap transaksi penjualan dan pembelian langsung menghasilkan jurnal kas, mencatat HPP riil,
                            dan mengupdate laporan laba rugi owner seketika.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0">
                            4</div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            Communication & Automation</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Notifikasi nota WhatsApp otomatis, pengingat jadwal booking servis, penerbitan resi kurir, dan
                            kalender konten promosi bisnis berbasis AI.
                        </p>
                    </div>
                </div>
            </section>

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- 4. CORE ECOSYSTEM MODULES --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Modul Unggulan Ekosistem
                    </span>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                        Modul Lengkap untuk Seluruh Aspek Bisnis Anda
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <a href="{{ route('public.erp.erp') }}"
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-3 group">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                            Omnichannel ERP Core</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Penyatuan
                            menyeluruh modul
                            kasir, stok gudang, pengadaan barang, akuntansi, dan analisis bisnis.</p>
                    </a>

                    <a href="{{ route('public.erp.pos') }}"
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-3 group">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="shopping-cart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                            Point of Sale (POS) Cepat</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Kasir responsif
                            untuk offline
                            dan online, scan barcode kilat, split bill meja, dan cetak struk Bluetooth.</p>
                    </a>

                    <a href="{{ route('public.erp.inventory') }}"
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-3 group">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="boxes" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                            Smart Inventory & Gudang</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Kartu stok
                            perpetual, resep
                            bahan baku BOM, mutasi multi-cabang, dan peringatan reorder point ke distributor.</p>
                    </a>

                    <a href="{{ route('public.erp.finance') }}"
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-3 group">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <i data-lucide="wallet" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                            Keuangan & Arus Kas</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Pencatatan kas
                            masuk dan
                            keluar otomatis, rekonsiliasi kasir tutup shift, dan transparansi arus kas bisnis.</p>
                    </a>

                    <a href="{{ route('public.omnichannel.whatsapp') }}"
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-3 group">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                            WhatsApp Customer CRM</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Kirim nota struk
                            belanja,
                            tagihan kasbon, reminder jadwal servis, dan broadcast ramah langsung ke WA pelanggan.</p>
                    </a>

                    <a href="{{ route('public.content.creation') }}"
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/40 hover:shadow-xl transition-all duration-300 shadow-sm space-y-3 group">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <i data-lucide="pen-tool" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                            Otomasi Konten & Sosmed</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Perencanaan
                            kalender konten
                            promosi, penjadwalan publikasi multi-channel, dan pembuatan copy otomatis.</p>
                    </a>
                </div>
            </section>

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- 5. INDUSTRY SOLUTIONS SHOWCASE --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <section
                class="p-6 sm:p-10 rounded-[28px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-8 shadow-sm">
                <div
                    class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200/80 dark:border-white/10 pb-6">
                    <div class="max-w-2xl">
                        <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                            Solusi Spesifik Industri
                        </span>
                        <h2
                            class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1 tracking-tight leading-[1.2] text-balance break-words">
                            Disesuaikan dengan Karakter Nyata Sektor Bisnis Anda
                        </h2>
                    </div>
                    <a href="{{ route('public.bos.overview') }}"
                        class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline flex items-center gap-1 shrink-0">
                        <span>Lihat Seluruh Solusi</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a href="{{ route('public.solutions.fnb') }}"
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-between gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="utensils" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4
                                class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                                F&B & Resto</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Resep & KOT Dapur</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.retail') }}"
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-between gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="store" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4
                                class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                                Retail & Toko</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Barcode & Dus/Pcs</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.workshop') }}"
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-between gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-slate-500/10 text-slate-600 flex items-center justify-center shrink-0">
                            <i data-lucide="wrench" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4
                                class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                                Bengkel Servis</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">SPK & Komisi Montir</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.laundry') }}"
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-between gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-cyan-500/10 text-cyan-600 flex items-center justify-center shrink-0">
                            <i data-lucide="droplets" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4
                                class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                                Laundry Kiloan</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Timbangan & Rak</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.manufacturing') }}"
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-between gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                            <i data-lucide="factory" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4
                                class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                                Manufaktur</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">BOM & HPP Pabrik</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.services') }}"
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 hover:border-[#007AFF]/40 hover:shadow-md transition text-center group flex flex-col items-center justify-between gap-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0">
                            <i data-lucide="briefcase" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4
                                class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug text-balance break-words">
                                Bisnis Jasa</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Booking & Termin DP</p>
                        </div>
                    </a>
                </div>
            </section>

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- 6. HOME FAQS --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab</span>
                    <h2
                        class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-[1.2] text-balance break-words">
                        Pertanyaan Sering Diajukan
                        Seputar COOCA</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apa perbedaan mendasar antara COOCA dan aplikasi POS
                                kasir biasa?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Aplikasi POS biasa umumnya hanya mencatat transaksi penjualan di meja kasir. COOCA adalah
                            <strong>Business Operating System & Omnichannel ERP</strong> yang menghubungkan kasir
                            kilat dengan gudang bahan baku (BOM), pembukuan jurnal akuntansi otomatis, integrasi katalog
                            online storefront, notifikasi WhatsApp, hingga kalender konten promosi bisnis dari satu
                            dashboard terpadu.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apakah saya harus membeli mesin kasir atau komputer
                                mahal untuk menggunakan COOCA?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Sama sekali tidak. COOCA berbasis web modern yang dapat langsung dioperasikan dari smartphone
                            Android, tablet, iPad, maupun laptop yang sudah Anda miliki saat ini, serta dapat terhubung
                            dengan printer struk thermal Bluetooth standar 58mm/80mm.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Bagaimana jika bisnis saya memiliki beberapa cabang
                                gerai yang lokasinya berjauhan?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            COOCA didesain secara native multi-outlet. Anda dapat memantau penjualan seluruh cabang secara
                            real-time dari satu smartphone, melakukan transfer stok antar gudang cabang, serta membatasi hak
                            akses kasir agar hanya bisa melihat data outletnya sendiri.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apakah data usaha dan database pelanggan saya aman di
                                COOCA?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Sangat aman. Kami menerapkan isolasi data multi-tenant ketat dan enkripsi standar industri. Kami
                            menjunjung tinggi privasi bisnis Anda sesuai regulasi UU PDP No. 27/2022 dan tidak pernah
                            menjual atau membagikan data Anda kepada pihak manapun.
                        </p>
                    </details>
                </div>
            </section>

            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            {{-- 7. FINAL HIGH-CONVERSION CTA BANNER --}}
            {{-- ══════════════════════════════════════════════════════════════════════ --}}
            <section
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-6">
                <div
                    class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-6 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#00C4D8]" aria-hidden="true"></i>
                        <span>Telah Membantu Ribuan Pengusaha Mandiri di Indonesia</span>
                    </div>
                    <h3
                        class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                        Mulai Operasikan Bisnis Anda dengan Standar Tertinggi Hari Ini
                    </h3>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed text-pretty">
                        Daftar akun gratis sekarang. Hubungkan kasir, gudang, keuangan, dan saluran penjualan bisnis Anda
                        dalam satu ekosistem yang terintegrasi.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Daftar Akun Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm">
                            <span>Lihat Paket Harga</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
