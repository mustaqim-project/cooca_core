@extends('layouts.public_marketing')

@section('title', 'COOCA — Business Operating System & Omnichannel ERP Terpadu')
@section('description', 'Kelola operasional bisnis, kasir POS, stok inventaris, keuangan laba rugi, CRM pelanggan, marketplace, WhatsApp, dan otomasi dalam satu platform terhubung.')
@section('keywords', 'business operating system, omnichannel erp indonesia, software kasir terintegrasi, aplikasi manajemen bisnis umkm, sistem operasional terpadu, software pos multi outlet')

@push('seo')
    <link rel="canonical" href="{{ route('landing') }}">
    <meta property="og:title" content="COOCA — Business Operating System & Omnichannel ERP Terpadu">
    <meta property="og:description" content="Kendalikan seluruh bisnis Anda dari satu ekosistem: kasir, inventaris gudang, keuangan, marketplace, dan otomasi WhatsApp.">
    <meta property="og:url" content="{{ route('landing') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="COOCA — Business Operating System & Omnichannel ERP Terpadu">
    <meta name="twitter:description" content="Platform operasional bisnis terpadu untuk UMKM dan bisnis berkembang di Indonesia.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
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
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20 sm:space-y-28">

            {{-- 1. HERO SECTION (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-2">
                {{-- Left: Headline, Value Proposition, Action CTAs --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="layers" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>COOCA • Business Operating System & Omnichannel ERP</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-[3.25rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.12]">
                        Satu Sistem untuk Mengendalikan Seluruh Bisnis Anda
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl font-normal">
                        Kelola operasional kasir, stok gudang, pembukuan keuangan, pelanggan, marketplace online, komunikasi WhatsApp, hingga otomasi dalam satu platform yang saling terhubung tanpa jeda.
                    </p>

                    {{-- Tangible Proof Points --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Bukan sekadar aplikasi POS kasir biasa</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Hentikan ketik ulang di spreadsheet terpisah</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Akses fleksibel dari HP, tablet, & laptop</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Laba bersih & arus kas terpantau real-time</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-3 flex flex-wrap items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Pakai COOCA</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('public.bos.how-it-works') }}" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <span>Lihat Cara Kerja</span>
                        </a>
                        <a href="{{ route('public.demo') }}" class="h-12 px-5 rounded-[14px] text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/10 text-sm font-semibold flex items-center justify-center gap-1.5 transition">
                            <i data-lucide="play" class="w-4 h-4" aria-hidden="true"></i>
                            <span>Coba Live Demo</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Master Operating System Bento Deck --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">COOCA Business OS • Live Pulse</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 px-2.5 py-0.5 rounded-full">
                                4 Modul Terhubung
                            </span>
                        </div>

                        {{-- Multi-Channel Transaction Feed Simulation --}}
                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span>Kasir POS Toko #TRX-8802</span>
                                    </div>
                                    <span class="font-mono text-emerald-600 font-bold">+ Rp 185.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-[#6E6E73] dark:text-[#86868B] font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Gudang: 3 Item Terpotong</span>
                                    <span class="text-blue-600 dark:text-blue-400">Jurnal Kasir Lunas</span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                        <span>Pesanan Storefront Online #ORD-104</span>
                                    </div>
                                    <span class="font-mono text-emerald-600 font-bold">+ Rp 320.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-[#6E6E73] dark:text-[#86868B] font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Resi Kurir Biteship Siap</span>
                                    <span class="text-emerald-600 dark:text-emerald-400">Notif WA Terkirim</span>
                                </div>
                            </div>
                        </div>

                        {{-- Real-Time Owner Overview Breakdown --}}
                        <div class="p-4 rounded-[16px] bg-neutral-900 text-white space-y-2.5">
                            <div class="flex justify-between text-xs text-neutral-400">
                                <span>Arus Kas Masuk Hari Ini</span>
                                <span class="font-mono text-emerald-400 font-bold">Rp 4.820.000</span>
                            </div>
                            <div class="flex justify-between text-xs text-neutral-400">
                                <span>Estimasi HPP & Beban Operasional</span>
                                <span class="font-mono text-neutral-300">- Rp 2.410.000</span>
                            </div>
                            <div class="pt-2 border-t border-neutral-800 flex justify-between items-center text-xs font-bold">
                                <span class="text-neutral-200">Laba Bersih Riil Owner</span>
                                <span class="font-mono text-sm text-[#007AFF]">+ Rp 2.410.000 (50.0%)</span>
                            </div>
                        </div>

                        {{-- Hardware & Channel Bar --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Printer Siap Cetak</span>
                            </div>
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Data Terisolasi Aman</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. THE FRAGMENTED BUSINESS PROBLEM --}}
            <section class="p-6 sm:p-10 rounded-[28px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Pengelolaan Bisnis
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1 tracking-tight">
                        Mengapa Menggunakan Banyak Aplikasi Terpisah Menghambat Bisnis Anda?
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-9 h-9 rounded-[12px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">01</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Data Terpecah di Mana-Mana</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kasir menggunakan satu aplikasi, stok dicatat di buku atau Excel, pesanan online masuk lewat chat WhatsApp pribadi, dan pembukuan di software akuntansi terpisah.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-9 h-9 rounded-[12px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">02</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Stok Selisih & Laba Semu</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Karena sistem tidak terhubung, stok di kasir tidak mencerminkan sisa fisik di gudang. Penjualan terlihat ramai setiap hari, tetapi kas akhir bulan selalu selisih tanpa jejak.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-9 h-9 rounded-[12px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">03</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Owner Terjebak Rutinitas Teknis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Pemilik bisnis menghabiskan 2–3 jam setiap malam hanya untuk menyalin ulang transaksi dan mencocokkan nota kasir, alih-alih fokus ekspansi cabang dan strategi penjualan.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 3. COOCA AS ONE BUSINESS OPERATING SYSTEM --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Konsep Business Operating System
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Bagaimana COOCA Menyatukan Seluruh Lapisan Bisnis
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold">1</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Omnichannel Sales Layer</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            POS kasir toko fisik, storefront katalog online, pesanan QR meja, dan pesanan marketplace bermuara ke satu antrean pemrosesan order terpusat.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">2</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Operational & Inventory Engine</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kartu stok perpetual, konversi multi-satuan dus/pcs, resep bahan baku (BOM) kuliner, dan perintah kerja SPK bengkel/manufaktur terpotong otomatis.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center font-bold">3</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Financial & Accounting Core</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Setiap transaksi penjualan dan pembelian langsung menghasilkan jurnal kas, mencatat HPP riil, dan mengupdate laporan laba rugi owner seketika.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">4</div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Communication & Automation</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Notifikasi nota WhatsApp otomatis, pengingat jadwal booking servis, penerbitan resi kurir, dan kalender konten promosi bisnis berbasis AI.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 4. CORE ECOSYSTEM MODULES --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Modul Unggulan Ekosistem
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Modul Lengkap untuk Seluruh Aspek Bisnis Anda
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <a href="{{ route('public.erp.erp') }}" class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-3 group">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Omnichannel ERP Core</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Penyatuan menyeluruh modul kasir, stok gudang, pengadaan barang, akuntansi, dan analisis bisnis.</p>
                    </a>

                    <a href="{{ route('public.erp.pos') }}" class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-3 group">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Point of Sale (POS) Cepat</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Kasir responsif untuk offline dan online, scan barcode kilat, split bill meja, dan cetak struk Bluetooth.</p>
                    </a>

                    <a href="{{ route('public.erp.inventory') }}" class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-3 group">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="boxes" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Smart Inventory & Gudang</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Kartu stok perpetual, resep bahan baku BOM, mutasi multi-cabang, dan peringatan reorder point ke distributor.</p>
                    </a>

                    <a href="{{ route('public.erp.finance') }}" class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-3 group">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Keuangan & Arus Kas</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pencatatan kas masuk dan keluar otomatis, rekonsiliasi kasir tutup shift, dan transparansi arus kas bisnis.</p>
                    </a>

                    <a href="{{ route('public.omnichannel.whatsapp') }}" class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-3 group">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">WhatsApp Customer CRM</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Kirim nota struk belanja, tagihan kasbon, reminder jadwal servis, dan broadcast ramah langsung ke WA pelanggan.</p>
                    </a>

                    <a href="{{ route('public.content.creation') }}" class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 hover:border-[#007AFF]/40 transition shadow-sm space-y-3 group">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="pen-tool" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Otomasi Konten & Sosmed</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Perencanaan kalender konten promosi, penjadwalan publikasi multi-channel, dan pembuatan copy otomatis.</p>
                    </a>
                </div>
            </section>

            {{-- 5. INDUSTRY SOLUTIONS SHOWCASE --}}
            <section class="p-6 sm:p-10 rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-neutral-100 dark:border-neutral-800/80 pb-6">
                    <div class="max-w-2xl">
                        <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                            Solusi Spesifik Industri
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1 tracking-tight">
                            Disesuaikan dengan Karakter Nyata Sektor Bisnis Anda
                        </h2>
                    </div>
                    <a href="{{ route('public.bos.overview') }}" class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline flex items-center gap-1 shrink-0">
                        <span>Lihat Seluruh Solusi</span>
                        <i data-lucide="chevron-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a href="{{ route('public.solutions.fnb') }}" class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 hover:border-[#007AFF]/40 transition text-center group flex flex-col items-center justify-between gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="utensils" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">F&B & Resto</h4>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">Resep & KOT Dapur</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.retail') }}" class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 hover:border-[#007AFF]/40 transition text-center group flex flex-col items-center justify-between gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="store" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Retail & Toko</h4>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">Barcode & Dus/Pcs</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.workshop') }}" class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 hover:border-[#007AFF]/40 transition text-center group flex flex-col items-center justify-between gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-slate-500/10 text-slate-600 flex items-center justify-center">
                            <i data-lucide="wrench" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Bengkel Servis</h4>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">SPK & Komisi Montir</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.laundry') }}" class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 hover:border-[#007AFF]/40 transition text-center group flex flex-col items-center justify-between gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-cyan-500/10 text-cyan-600 flex items-center justify-center">
                            <i data-lucide="droplets" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Laundry Kiloan</h4>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">Timbangan & Rak</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.manufacturing') }}" class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 hover:border-[#007AFF]/40 transition text-center group flex flex-col items-center justify-between gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="factory" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Manufaktur</h4>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">BOM & HPP Pabrik</p>
                        </div>
                    </a>

                    <a href="{{ route('public.solutions.services') }}" class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 hover:border-[#007AFF]/40 transition text-center group flex flex-col items-center justify-between gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-violet-500/10 text-violet-600 flex items-center justify-center">
                            <i data-lucide="briefcase" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] transition">Bisnis Jasa</h4>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">Booking & Termin DP</p>
                        </div>
                    </a>
                </div>
            </section>

            {{-- 6. HOME FAQS --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Sering Diajukan Seputar COOCA</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apa perbedaan mendasar antara COOCA dan aplikasi POS kasir biasa?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Aplikasi POS biasa umumnya hanya mencatat transaksi penjualan di meja kasir. COOCA adalah <strong>Business Operating System & Omnichannel ERP</strong> yang menghubungkan kasir kasir kilat dengan gudang bahan baku (BOM), pembukuan jurnal akuntansi otomatis, integrasi katalog online storefront, notifikasi WhatsApp, hingga kalender konten promosi bisnis dari satu dashboard terpadu.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah saya harus membeli mesin kasir atau komputer mahal untuk menggunakan COOCA?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Sama sekali tidak. COOCA berbasis web modern yang dapat langsung dioperasikan dari smartphone Android, tablet, iPad, maupun laptop yang sudah Anda miliki saat ini, serta dapat terhubung dengan printer struk thermal Bluetooth standar 58mm/80mm.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana jika bisnis saya memiliki beberapa cabang gerai yang lokasinya berjauhan?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA didesain secara native multi-outlet. Anda dapat memantau penjualan seluruh cabang secara real-time dari satu smartphone, melakukan transfer stok antar gudang cabang, serta membatasi hak akses kasir agar hanya bisa melihat data outletnya sendiri.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah data usaha dan database pelanggan saya aman di COOCA?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Sangat aman. Kami menerapkan isolasi data multi-tenant ketat dan enkripsi standar industri. Kami menjunjung tinggi privasi bisnis Anda sesuai regulasi UU PDP No. 27/2022 dan tidak pernah menjual atau membagikan data Anda kepada pihak manapun.
                        </p>
                    </details>
                </div>
            </section>

            {{-- 7. FINAL HIGH-CONVERSION CTA BANNER --}}
            <section class="p-8 sm:p-14 rounded-[28px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-6 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Telah Membantu Ribuan Pengusaha Mandiri di Indonesia</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight max-w-2xl mx-auto leading-tight">
                    Mulai Operasikan Bisnis Anda dengan Standar Tertinggi Hari Ini
                </h3>
                <p class="text-sm sm:text-base text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun gratis sekarang. Hubungkan kasir, gudang, keuangan, dan saluran penjualan bisnis Anda dalam satu ekosistem yang terintegrasi.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Daftar Akun Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Lihat Paket Harga</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
