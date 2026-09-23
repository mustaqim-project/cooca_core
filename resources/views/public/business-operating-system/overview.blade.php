@extends('layouts.public_marketing')

@section('title', 'Business Operating System untuk Kendali Operasional Bisnis | COOCA')
@section('description', 'COOCA adalah Business Operating System terpadu untuk UMKM Indonesia. Hubungkan kasir, gudang,
    keuangan, channel penjualan, dan otomasi dalam satu platform.')
@section('keywords', 'business operating system, sistem operasional bisnis, software manajemen bisnis terpadu, erp umkm
    indonesia, platform bisnis terintegrasi')

    @push('seo')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "COOCA Business Operating System",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud, Android, iOS",
        "description": "Business Operating System terintegrasi untuk mengendalikan seluruh operasional, inventory, keuangan, kasir, dan channel penjualan bisnis dari satu platform.",
        "url": "{{ route('public.bos.overview') }}",
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
                "name": "Business Operating System",
                "item": "{{ route('public.bos.overview') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Overview",
                "item": "{{ route('public.bos.overview') }}"
            }
        ]
    }
    </script>
    @endpush

@section('content')
    <div class="w-full bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (2-Column Apple HIG Bento Layout) ═══════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10">
            <!-- Ambient lighting -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[420px] h-[420px] bg-[#00C2FF]/10 rounded-full blur-[130px] pointer-events-none">
            </div>

            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Breadcrumbs -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 pb-6">
                    <a href="{{ route('landing') }}" class="hover:text-[#00C2FF] transition-colors">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <span class="text-slate-300">Business Operating System</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <span class="text-white font-semibold">Overview</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    <!-- Left: Copy & Actions (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-[#00C2FF] tracking-wide">
                            <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                            <span>Business Operating System</span>
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-black text-white tracking-tight leading-[1.2] text-balance break-words">
                            Satu Sistem Operasi untuk Mengendalikan Seluruh Bisnis Anda
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal text-pretty">
                            Bukan sekadar aplikasi kasir atau software pembukuan terpisah. COOCA adalah sistem operasional
                            bisnis terpadu yang menghubungkan kasir, persediaan gudang, arus kas, pesanan pelanggan, hingga
                            laporan pemilik dalam satu database yang saling memperbarui seketika.
                        </p>

                        <!-- CTAs -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('register') }}"
                                class="px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Coba COOCA Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                            </a>
                            <a href="{{ route('public.bos.how-it-works') }}"
                                class="px-6 py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 active:scale-[0.98] transition-all min-h-[48px]">
                                <i data-lucide="play" class="w-4 h-4 text-[#00C2FF] shrink-0"></i>
                                <span>Lihat Cara Kerja Sistem</span>
                            </a>
                        </div>

                        <!-- Micro Reassurance -->
                        <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-slate-400">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span class="truncate">Tanpa duplikasi input manual</span>
                            </div>
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span class="truncate">Multi-device: HP, tablet, laptop</span>
                            </div>
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span class="truncate">Isolasi data bisnis aman</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Visual BOS Simulation (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div
                            class="bg-[#0B132B]/90 border border-white/10 rounded-[24px] p-5 sm:p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] backdrop-blur-xl space-y-4">
                            <!-- macOS Window Header -->
                            <div class="flex items-center justify-between gap-2 border-b border-white/10 pb-3">
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                </div>
                                <span class="text-xs font-mono font-medium text-slate-300 truncate">COOCA Engine • Live Connected State</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Synced
                                </span>
                            </div>

                            <!-- 3 Layer Stack Visualizer -->
                            <div class="space-y-2.5">
                                <!-- Layer 1: Channels -->
                                <div
                                    class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-9 h-9 rounded-[10px] bg-[#007AFF]/20 text-[#00C2FF] flex items-center justify-center font-bold shrink-0">
                                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-white truncate">Channel Penjualan Terhubung</div>
                                            <div class="text-[11px] text-slate-400 truncate">Kasir POS • Web Store • WhatsApp</div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-mono text-emerald-400 font-semibold shrink-0">1 Realtime Feed</span>
                                </div>

                                <!-- Arrow down indicator -->
                                <div class="flex justify-center text-slate-500">
                                    <i data-lucide="arrow-down" class="w-4 h-4 text-[#00C2FF]"></i>
                                </div>

                                <!-- Layer 2: Operating Core -->
                                <div
                                    class="p-3.5 rounded-[16px] bg-gradient-to-r from-blue-950/60 to-cyan-950/60 border border-[#00C2FF]/30">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="w-9 h-9 rounded-[10px] bg-[#00C2FF]/20 text-[#00C2FF] flex items-center justify-center font-bold shrink-0">
                                                <i data-lucide="cpu" class="w-4 h-4"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-white truncate">COOCA Operating Engine</div>
                                                <div class="text-[11px] text-slate-300 truncate">Auto-BOM • Potong Stok • Jurnal Kas</div>
                                            </div>
                                        </div>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#00C2FF]/20 text-[#00C2FF] shrink-0">Otomatis</span>
                                    </div>
                                </div>

                                <!-- Arrow down indicator -->
                                <div class="flex justify-center text-slate-500">
                                    <i data-lucide="arrow-down" class="w-4 h-4 text-[#00C2FF]"></i>
                                </div>

                                <!-- Layer 3: Owner Control & Financial Ledger -->
                                <div
                                    class="p-3.5 rounded-[16px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-9 h-9 rounded-[10px] bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold shrink-0">
                                            <i data-lucide="line-chart" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-white truncate">Pusat Kendali Owner</div>
                                            <div class="text-[11px] text-slate-400 truncate">Laba Rugi Harian • Mutasi Kas • Margin</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-mono font-bold text-white tabular-nums shrink-0">0 Detik Delay</span>
                                </div>
                            </div>

                            <!-- Live Status Banner -->
                            <div
                                class="pt-2 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                                <span>Satu sumber kebenaran (Single Source of Truth)</span>
                                <span class="text-emerald-400 font-semibold font-mono">100% Akurat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. THE PROBLEM: KEKACAUAN SISTEM BISNIS TERFRAGMENTASI ═══════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#FF9500]">Realita di Lapangan</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Mengapa Memakai Banyak Aplikasi Terpisah Malah Membuat Bisnis Rumit?
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        Banyak pengusaha UMKM memulai dengan software kasir di toko, mencatat stok di buku tulis, merekap
                        pengeluaran di spreadsheet, lalu membalas chat pembeli di ponsel pribadi. Ketika cabang bertambah,
                        data tercecer di mana-mana.
                    </p>
                </div>

                <!-- Bento 3-Grid Pain Points -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pain 1 -->
                    <div
                        class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                            <i data-lucide="copy-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">Input Berulang Kali yang Membuang Waktu</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Kasir mengetik transaksi, admin menginput ulang nota ke Excel di malam hari, dan staf gudang
                            menghitung sisa stok fisik secara manual. Kesalahan ketik satu angka membuat pembukuan selisih
                            jutaan rupiah.
                        </p>
                    </div>

                    <!-- Pain 2 -->
                    <div
                        class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                            <i data-lucide="eye-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">Owner Kehilangan Visibilitas Real-Time</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Saat owner sedang di luar kota, tidak ada cara cepat untuk mengetahui berapa uang kas fisik di
                            laci kasir sekarang, apakah bahan baku utama menipis, atau cabang mana yang sedang merugi hari
                            ini.
                        </p>
                    </div>

                    <!-- Pain 3 -->
                    <div
                        class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">Biaya Langganan Banyak Aplikasi Membengkak</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Membayar software POS, software akuntansi, platform email/WA broadcast, dan tool inventory
                            secara terpisah menghasilkan tagihan berulang mahal dengan fungsionalitas yang tumpang tindih.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. ARSITEKTUR LAYER COOCA: BAGAIMANA SEMUA TERHUBUNG ═════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Arsitektur
                        Terpadu</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        5 Lapisan Sistem Operasi Bisnis COOCA
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        Setiap modul di COOCA bukan pulau terisolasi. Mereka berkomunikasi melalui protokol data otomatis
                        yang memastikan integritas stok, jurnal keuangan, dan analitik bisnis selalu sinkron.
                    </p>
                </div>

                <!-- Bento Asymmetric 5-Layer Composition -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Layer 1 (Span 2) -->
                    <div
                        class="lg:col-span-2 p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#007AFF] uppercase">Layer 01 &bull; Front
                                Office</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">Channel
                                Penjualan</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold leading-snug text-balance break-words">Kasir POS, Storefront Online, & Pesanan Meja</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            Pintu masuk seluruh transaksi: antarmuka kasir layar sentuh cepat, katalog toko online mandiri
                            (cooca.id/{slug}), pemesanan QR meja kafe, dan order pesanan via WhatsApp. Semua mengalir ke
                            satu antrean pemrosesan yang sama.
                        </p>
                        <div class="pt-2 flex flex-wrap gap-2 text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">Kasir
                                Kasir Cepat</span>
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">QR
                                Meja Kafe</span>
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">Katalog
                                Web</span>
                            <span
                                class="px-3 py-1 rounded-[8px] bg-white dark:bg-white/10 border border-black/5 dark:border-white/10">Pencatat
                                Pre-Order</span>
                        </div>
                    </div>

                    <!-- Layer 2 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#34C759] uppercase">Layer 02</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759]">Inventory
                                Engine</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Alokasi Stok & Resep Otomatis</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            Saat pesanan selesai, sistem otomatis memotong stok barang jadi atau bahan mentah sesuai Bill of
                            Materials (BOM) resep produk. Tidak ada lagi stok fiktif.
                        </p>
                    </div>

                    <!-- Layer 3 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#FF9500] uppercase">Layer 03</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#FF9500]">Financial
                                Ledger</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Jurnal Pembukuan Otomatis</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            Uang kas masuk, diskon, biaya admin QRIS, dan harga pokok penjualan (HPP) langsung terjurnal ke
                            buku besar akuntansi tanpa perlu sentuhan tangan staf akuntan.
                        </p>
                    </div>

                    <!-- Layer 4 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#AF52DE] uppercase">Layer 04</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">Automations</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Notifikasi & Komunikasi WhatsApp</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            Kirim nota struk belanja digital, konfirmasi pembayaran invoice, hingga pengingat kasbon jatuh
                            tempo secara sopan dan terotomasi langsung ke nomor WhatsApp pembeli.
                        </p>
                    </div>

                    <!-- Layer 5 -->
                    <div
                        class="p-7 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-[#00C2FF] uppercase">Layer 05</span>
                            <span
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#00C2FF]/10 text-[#00C2FF]">Executive
                                Intel</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Executive Control & AI Insights</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-pretty">
                            Pemilik bisnis memantau grafik margin laba bersih harian, produk paling laris, jam operasional
                            paling ramai, dan proyeksi belanja restock gudang dari layar ponsel.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. WORKFLOW NYATA: DARI TRANSAKSI HINGGA LAPORAN OWNER ══════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-2xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-500">Alur Bisnis Konkret</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Apa yang Terjadi Saat 1 Transaksi Selesai di COOCA?
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        Satu kali klik tombol bayar di kasir langsung menggerakkan seluruh ekosistem bisnis tanpa ada jeda
                        atau proses rekapitulasi ulang di malam hari.
                    </p>
                </div>

                <!-- Workflow 4-Step Interactive Card Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3 relative">
                        <span class="text-xs font-mono font-bold text-slate-400">LANGKAH 01</span>
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="scan" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-balance break-words">Transaksi Diterima</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Kasir scan barcode atau pembeli bayar via QRIS di meja. Bukti bayar terverifikasi seketika dan
                            nota struk tercetak via printer Bluetooth.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3 relative">
                        <span class="text-xs font-mono font-bold text-slate-400">LANGKAH 02</span>
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                            <i data-lucide="package-minus" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-balance break-words">Stok & Bahan Terpotong</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Kartu stok perpetual gudang cabang langsung berkurang. Jika barang melewati batas minimum,
                            notifikasi restock otomatis menyala.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3 relative">
                        <span class="text-xs font-mono font-bold text-slate-400">LANGKAH 03</span>
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold">
                            <i data-lucide="book-open-check" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-balance break-words">Jurnal Akuntansi Masuk</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Sistem mendebit Kas/Bank dan mengkredit Penjualan serta mengalokasikan Beban Pokok Penjualan
                            (HPP) otomatis ke jurnal umum.
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3 relative">
                        <span class="text-xs font-mono font-bold text-slate-400">LANGKAH 04</span>
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center font-bold">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-balance break-words">Owner Melihat Hasil</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Laba bersih, sisa kas riil, dan rekap omzet harian sudah terhitung rapi di dasbor ponsel pemilik
                            tanpa menunggu laporan akhir bulan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. FAQ SEO SPESIFIK BUSINESS OPERATING SYSTEM ═══════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Tanya
                        Jawab</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Pertanyaan Seputar Business Operating System
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-pretty">
                        Semua yang perlu Anda ketahui tentang transisi dari aplikasi terpisah ke sistem terpadu COOCA.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <!-- FAQ 1 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apa perbedaan Business Operating System dengan aplikasi kasir (POS) biasa?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Aplikasi kasir biasa hanya mencatat apa yang dijual di kasir dan mencetak struk belanja.
                            Sedangkan Business Operating System (COOCA) menghubungkan kasir tersebut dengan gudang bahan
                            baku (stok terpotong otomatis), pembukuan keuangan (jurnal laba rugi terbit otomatis), database
                            pelanggan CRM, notifikasi WhatsApp, hingga laporan manajemen owner secara real-time.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apakah bisnis saya yang masih 1 outlet cocok menggunakan sistem ini?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Sangat cocok. Justru memulai dengan sistem terpadu sejak 1 gerai akan mencegah kekacauan data
                            saat bisnis Anda berekspansi ke cabang 2, 3, dan seterusnya. Anda tidak perlu repot migrasi
                            sistem di kemudian hari karena COOCA dirancang modular dari skala pemula hingga multi-cabang.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apakah data keuangan dan resep bisnis saya aman dan terpisah dari bisnis lain?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Ya. COOCA menerapkan arsitektur isolasi multi-tenant yang ketat. Setiap bisnis memiliki partisi
                            data independen dengan enkripsi AES-256 dan kontrol hak akses berbasis role (Kasir, Manajer,
                            Owner). Staf kasir tidak akan bisa melihat laporan laba bersih atau resep rahasia yang hanya
                            boleh diakses oleh pemilik.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div
                        class="rounded-[18px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Perangkat apa saja yang dibutuhkan untuk menjalankan COOCA?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 4" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Anda tidak perlu membeli perangkat komputer mahal. Kasir dapat dijalankan cukup dari smartphone
                            Android atau tablet yang sudah ada, terhubung ke printer thermal Bluetooth portabel. Manajemen
                            stok dan pembukuan dapat diakses fleksibel dari browser laptop atau PC mana pun.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. RELATED MODULES (TOPICAL CLUSTER INTERNAL LINKS) ═════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span
                            class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Jelajahi
                            Ekosistem</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight leading-snug text-balance break-words">Modul Inti yang Mendukung Business
                            Operating System</h3>
                    </div>
                    <a href="{{ route('public.erp.erp') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline shrink-0">
                        <span>Lihat Seluruh Modul ERP</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <a href="{{ route('public.erp.pos') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="monitor" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#007AFF] transition-colors leading-snug text-balance">Point of Sale (POS)
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Kasir cepat, cetak
                            struk Bluetooth, barcode scan, & mutasi kas harian.</p>
                    </a>

                    <a href="{{ route('public.erp.inventory') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="boxes" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#34C759] transition-colors leading-snug text-balance">Software Inventory
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Multi-gudang, kartu
                            stok perpetual, resep BOM, & reorder point.</p>
                    </a>

                    <a href="{{ route('public.erp.accounting') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#FF9500] transition-colors leading-snug text-balance">Pembukuan Otomatis
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Jurnal umum otomatis,
                            buku besar, neraca, & laba rugi terbit tanpa delay.</p>
                    </a>

                    <a href="{{ route('public.omnichannel.whatsapp') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-emerald-500/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-emerald-500 transition-colors leading-snug text-balance">WhatsApp Commerce
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Kirim nota belanja
                            digital, tagihan piutang, dan update pesanan resmi via WA.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 7. BOTTOM CONVERSION CALL TO ACTION ══════════════════════════════════ -->
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
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">Langkah Praktis
                                Berikutnya</span>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-[1.2] text-balance break-words">
                                Mulai Kendalikan Bisnis Anda dengan Business Operating System
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed text-pretty">
                                Tinggalkan kerumitan spreadsheet tercecer dan input data berulang. Gabung bersama ratusan
                                pemilik UMKM Indonesia yang mengelola toko lebih tenang dan pasti profit bersama COOCA.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Daftar COOCA Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.pricing') }}"
                                class="w-full py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 transition-all min-h-[44px]">
                                <span>Lihat Daftar Paket & Harga</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
