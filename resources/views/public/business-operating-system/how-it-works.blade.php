@extends('layouts.public_marketing')

@section('title', 'Cara Kerja Business Operating System Terintegrasi | COOCA')
@section('description', 'Pelajari alur data otomatis COOCA: dari transaksi kasir dan pesanan online, pemotongan stok
    bahan baku, pembukuan jurnal otomatis, hingga analitik owner.')
@section('keywords', 'cara kerja sistem bisnis terintegrasi, alur operasional bisnis umkm, otomasi pembukuan toko,
    workflow kasir ke akuntansi, cara kerja cooca')

    @push('seo')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Cara Kerja COOCA Business Operating System",
        "description": "Panduan langkah demi langkah bagaimana data transaksi masuk, memotong stok, membukukan jurnal keuangan, dan memperbarui analisis laba owner di COOCA.",
        "url": "{{ route('public.bos.how-it-works') }}",
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
                "name": "Cara Kerja",
                "item": "{{ route('public.bos.how-it-works') }}"
            }
        ]
    }
    </script>
    @endpush

@section('content')
    <div class="w-full bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION ═════════════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10">
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
                    <a href="{{ route('public.bos.overview') }}" class="hover:text-[#00C2FF] transition-colors">Business
                        Operating System</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <span class="text-white font-semibold">Cara Kerja</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    <!-- Left: Headline & Copy -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-[#00C2FF] tracking-wide">
                            <i data-lucide="workflow" class="w-3.5 h-3.5"></i>
                            <span>End-to-End Business Flow</span>
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.75rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                            Bagaimana COOCA Mengotomasi Operasional Toko Anda dari Depan ke Belakang
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal text-pretty">
                            Lihat bagaimana satu transaksi yang terjadi di kasir atau website toko mengalir secara presisi
                            ke gudang, pembukuan kas, buku besar akuntansi, dan layar pantau owner tanpa rekonsiliasi
                            manual.
                        </p>

                        <!-- CTAs -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('register') }}"
                                class="px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Coba Alur Nyata Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.bos.why-cooca') }}"
                                class="px-6 py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Mengapa Harus COOCA?</span>
                            </a>
                        </div>

                        <!-- Bullet Points -->
                        <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Zero jeda waktu (Real-Time)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Otomatis potong resep & bahan baku</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Jurnal akuntansi otomatis terbit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Visual Flow Diagram Mockup -->
                    <div class="lg:col-span-5">
                        <div
                            class="bg-[#0B132B]/90 border border-white/10 rounded-[24px] p-5 sm:p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] backdrop-blur-xl space-y-4">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3 gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                    <span class="text-xs font-mono font-bold text-white uppercase truncate">Pipeline Transaksi Live</span>
                                </div>
                                <span class="text-[11px] font-mono text-slate-400 shrink-0">ID: TRX-2026-9041</span>
                            </div>

                            <!-- 4 Sequential Flow Step Cards -->
                            <div class="space-y-3">
                                <div
                                    class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-[8px] bg-blue-500/20 text-[#00C2FF] flex items-center justify-center font-mono font-bold text-xs shrink-0">
                                            1</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-white truncate">Kasir POS / Web Order</div>
                                            <div class="text-[11px] text-slate-400 truncate">Pembayaran QRIS Rp 48.000 Sukses</div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                </div>

                                <div
                                    class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-[8px] bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                                            2</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-white truncate">Inventory Engine</div>
                                            <div class="text-[11px] text-slate-400 truncate">Bahan Baku Terpotong via BOM Resep</div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-mono text-emerald-400 shrink-0">-1 Pack</span>
                                </div>

                                <div
                                    class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-[8px] bg-amber-500/20 text-amber-400 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                                            3</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-white truncate">General Ledger Akuntansi</div>
                                            <div class="text-[11px] text-slate-400 truncate">Debit Kas Bank, Kredit Penjualan & HPP
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-mono text-amber-300 shrink-0">Auto Journal</span>
                                </div>

                                <div
                                    class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-[8px] bg-purple-500/20 text-purple-400 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                                            4</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-bold text-white truncate">Owner Smartphone Feed</div>
                                            <div class="text-[11px] text-slate-400 truncate">Laba Bersih & Margin Terupdate Seketika
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-mono text-white font-bold shrink-0">+Rp 24.500</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-white/10 text-center">
                                <span class="text-[11px] text-slate-400">Seluruh proses di atas selesai dalam waktu <strong
                                        class="text-white">&lt; 0.5 detik</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. DETAIL TAHAPAN OPERASIONAL 4 FASE ═════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Rincian
                        Tahapan</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        4 Fase Eksekusi Data Tanpa Sentuhan Manual
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        Setiap modul bekerja secara otonom dan saling memvalidasi data untuk memastikan uang kas fisik toko
                        dan stok barang selalu cocok dengan catatan buku.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Fase 1 -->
                    <div
                        class="p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-sm shrink-0">Fase
                                1</span>
                            <span class="text-xs font-semibold text-neutral-500 shrink-0">Frontline Sales</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Penangkapan Transaksi di Titik Penjualan</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Saat pelanggan memesan di kasir toko fisik, memilih menu lewat QR order di meja, atau melakukan
                            checkout di katalog online toko Anda, COOCA langsung mengunci pesanan tersebut ke dalam antrean
                            terenkripsi.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Dukungan pembayaran tunai, transfer, QRIS otomatis, dan kasbon pelanggan.</span>
                            </div>
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Cetak nota kasir via Bluetooth printer atau kirim nota digital via WhatsApp.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fase 2 -->
                    <div
                        class="p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="w-9 h-9 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold text-sm shrink-0">Fase
                                2</span>
                            <span class="text-xs font-semibold text-neutral-500 shrink-0">Inventory & BOM</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Pemotongan Bahan & Alokasi Stok Gudang</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Sistem mengecek resep atau Bill of Materials (BOM) produk. Bila yang terjual adalah
                            makanan/minuman, takaran bahan mentah (kopi, susu, cup, kemasan) dipotong otomatis dari
                            persediaan gudang outlet terkait.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Mutasi stok tercatat perpetual dengan penomoran dokumen batch.</span>
                            </div>
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Notifikasi otomatis menyala jika persediaan mendekati titik reorder (ROP).</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fase 3 -->
                    <div
                        class="p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="w-9 h-9 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold text-sm shrink-0">Fase
                                3</span>
                            <span class="text-xs font-semibold text-neutral-500 shrink-0">Ledger Posting</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Penerbitan Jurnal Akuntansi Otomatis</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Tidak perlu mengumpulkan bon kertas dan mengetik jurnal di akhir bulan. COOCA membuat ayat
                            jurnal ganda (double-entry bookkeeping) otomatis untuk pendapatan, kas/bank, potongan harga, dan
                            beban pokok penjualan (HPP).
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Standar Akuntansi Keuangan Entitas Mikro Kecil Menengah (SAK EMKM).</span>
                            </div>
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Perhitungan margin laba kotor dan laba operasional bersih seketika.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fase 4 -->
                    <div
                        class="p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="w-9 h-9 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center font-bold text-sm shrink-0">Fase
                                4</span>
                            <span class="text-xs font-semibold text-neutral-500 shrink-0">Executive Insight</span>
                        </div>
                        <h3 class="text-xl font-bold leading-snug text-balance break-words">Penyajian Laporan & Notifikasi Pemilik</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Pemilik bisnis membuka dashboard di ponsel dan langsung melihat metrik bisnis terbaru: grafik
                            omzet harian, sisa kas fisik di kasir tiap outlet, produk paling menguntungkan, dan peringatan
                            potensi kebocoran biaya.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Rekap harian otomatis terkirim tanpa harus menelepon staf kasir.</span>
                            </div>
                            <div class="p-3 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs text-neutral-700 dark:text-neutral-300 leading-snug font-medium text-pretty">Rekomendasi stok cerdas untuk persiapan jam ramai akhir pekan.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. SKENARIO BISNIS NYATA (STUDI KASUS F&B & RETAIL) ══════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-2xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#FF9500]">Simulasi Konkret</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Lihat Contoh Nyata di Dua Jenis Bisnis
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        Bagaimana satu sistem yang sama menyelesaikan kebutuhan operasional bisnis kuliner (F&B) maupun toko
                        retail fisik.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Skenario 1: Kafe / F&B -->
                    <div
                        class="p-8 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold shrink-0">
                                <i data-lucide="utensils" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold leading-snug text-balance">Skenario: Kedai Kopi & F&B</h3>
                                <p class="text-xs text-neutral-500 truncate">1 Transaksi: Kopi Susu Gula Aren (Rp 22.000)</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs leading-relaxed text-neutral-600 dark:text-neutral-300">
                            <div
                                class="p-3.5 rounded-[14px] bg-white dark:bg-white/5 border border-black/5 dark:border-white/10 space-y-1.5">
                                <div
                                    class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate">1. Kasir POS</span>
                                    <span class="text-emerald-500 font-mono shrink-0">Lunas (QRIS)</span>
                                </div>
                                <p class="text-pretty">Kasir menekan menu Kopi Susu Aren. Tiket order langsung tercetak di printer meja barista
                                    tanpa pelayan perlu teriak ke bar.</p>
                            </div>

                            <div
                                class="p-3.5 rounded-[14px] bg-white dark:bg-white/5 border border-black/5 dark:border-white/10 space-y-1.5">
                                <div
                                    class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate">2. Stok Bahan Terpotong (BOM)</span>
                                    <span class="text-blue-500 font-mono shrink-0">BOM Triggered</span>
                                </div>
                                <p class="text-pretty">Stok berkurang: 18g espresso beans, 120ml susu fresh, 20ml sirup aren, 1 paper cup, 1
                                    sedotan. Total HPP terhitung: Rp 7.500.</p>
                            </div>

                            <div
                                class="p-3.5 rounded-[14px] bg-white dark:bg-white/5 border border-black/5 dark:border-white/10 space-y-1.5">
                                <div
                                    class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate">3. Jurnal Keuangan Masuk</span>
                                    <span class="text-purple-500 font-mono shrink-0">Auto Balance</span>
                                </div>
                                <p class="text-pretty">Kas bertambah Rp 22.000, Pendapatan bertambah Rp 22.000, Beban HPP Rp 7.500 tercatat,
                                    Laba Kotor langsung tercatat Rp 14.500.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Skenario 2: Toko Retail / Butik -->
                    <div
                        class="p-8 rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold shrink-0">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold leading-snug text-balance">Skenario: Toko Pakaian / Retail</h3>
                                <p class="text-xs text-neutral-500 truncate">1 Transaksi: Kemeja Linen Navy Size L (Rp 185.000)</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs leading-relaxed text-neutral-600 dark:text-neutral-300">
                            <div
                                class="p-3.5 rounded-[14px] bg-white dark:bg-white/5 border border-black/5 dark:border-white/10 space-y-1.5">
                                <div
                                    class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate">1. Scan Barcode Cepat</span>
                                    <span class="text-emerald-500 font-mono shrink-0">Lunas (Tunai)</span>
                                </div>
                                <p class="text-pretty">Kasir menembak barcode tag baju menggunakan barcode scanner kamera HP. Harga, diskon
                                    member, dan total terinput dalam 1 detik.</p>
                            </div>

                            <div
                                class="p-3.5 rounded-[14px] bg-white dark:bg-white/5 border border-black/5 dark:border-white/10 space-y-1.5">
                                <div
                                    class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate">2. Sinkronisasi Multi-Cabang</span>
                                    <span class="text-blue-500 font-mono shrink-0">Sync All Channels</span>
                                </div>
                                <p class="text-pretty">Stok Kemeja Navy Size L di outlet fisik berkurang 1. Etalase website toko online juga
                                    otomatis mengupdate sisa stok agar tidak dioverbooking.</p>
                            </div>

                            <div
                                class="p-3.5 rounded-[14px] bg-white dark:bg-white/5 border border-black/5 dark:border-white/10 space-y-1.5">
                                <div
                                    class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-between gap-2">
                                    <span class="min-w-0 truncate">3. CRM Pelanggan & Nota WA</span>
                                    <span class="text-purple-500 font-mono shrink-0">WhatsApp Sent</span>
                                </div>
                                <p class="text-pretty">Nomor HP pembeli tercatat ke profil member CRM, poin loyalti bertambah, dan nota belanja
                                    elektronik otomatis dikirimkan ke chat WA-nya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ SEPUTAR ALUR KERJA SISTEM ════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Tanya
                        Jawab</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Pertanyaan Seputar Alur Kerja COOCA
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-pretty">
                        Memahami detail teknis bagaimana data diproses di dalam ekosistem bisnis Anda.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apakah kasir tetap bisa digunakan jika koneksi internet toko sedang terputus?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Ya. Antarmuka kasir POS COOCA dirancang dengan arsitektur offline-first. Transaksi tetap dapat
                            diinput dan struk thermal tetap dapat dicetak secara lokal. Ketika koneksi internet kembali
                            tersambung, seluruh data antrean transaksi akan otomatis tersinkronisasi ke cloud tanpa ada data
                            yang hilang.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Bagaimana jika staf kasir salah memilih produk atau melakukan void transaksi?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Setiap pembatalan transaksi (void) atau pengembalian (refund) dilindungi oleh PIN otorisasi
                            supervisor/owner. Ketika void disetujui, COOCA otomatis menerbitkan jurnal pembalik dan
                            mengembalikan stok barang ke kartu gudang secara akurat dengan catatan audit trail lengkap
                            mengenai siapa yang membatalkan dan alasannya.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Berapa lama waktu setup awal untuk menyusun resep dan produk di COOCA?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Hanya butuh 5 hingga 15 menit. Anda dapat mengimpor daftar menu atau produk langsung dari file
                            Excel/CSV, atau memasukkannya satu per satu dengan formulir ringkas. Resep BOM bahan baku dapat
                            ditambahkan kapan saja tanpa mengganggu transaksi kasir yang sedang berjalan.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. RELATED MODULES CLUSTER ══════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span
                            class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Pelajari
                            Lebih Lanjut</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight leading-snug text-balance break-words">Eksplorasi Komponen Sistem Terhubung</h3>
                    </div>
                    <a href="{{ route('public.bos.overview') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline shrink-0">
                        <span>Kembali ke Overview BOS</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <a href="{{ route('public.bos.why-cooca') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#007AFF] transition-colors leading-snug text-balance">Kenapa Pilih COOCA?
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Perbandingan biaya dan
                            efisiensi COOCA vs banyak aplikasi terpisah.</p>
                    </a>

                    <a href="{{ route('public.erp.erp') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#34C759] transition-colors leading-snug text-balance">Omnichannel ERP Core
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Hubungkan operasional,
                            stok, purchasing, dan akuntansi.</p>
                    </a>

                    <a href="{{ route('public.omnichannel.orders') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="inbox" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#FF9500] transition-colors leading-snug text-balance">Manajemen Order</div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Kelola pesanan dari
                            berbagai channel dalam satu layar antrean.</p>
                    </a>

                    <a href="{{ route('public.erp.analytics') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#00C2FF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#00C2FF]/10 text-[#00C2FF] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#00C2FF] transition-colors leading-snug text-balance">Dasbor Analitik
                            Bisnis</div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Pantau performa omzet
                            dan margin cabang dari ponsel Anda.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. BOTTOM CONVERSION CTA ════════════════════════════════════════════ -->
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
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">Siap Mencoba?</span>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-[1.2] text-balance break-words">
                                Buktikan Kemudahan Alur Kerja COOCA pada Toko Anda
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed text-pretty">
                                Daftarkan toko Anda dalam 2 menit. Mulai gunakan kasir, pantau bahan baku, dan rasakan
                                nikmatnya pembukuan otomatis tanpa input ganda.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Daftar Gratis Sekarang</span>
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
