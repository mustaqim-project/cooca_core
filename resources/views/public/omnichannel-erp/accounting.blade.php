@extends('layouts.public_marketing')

@section('title', 'Software Akuntansi Online & Laporan Keuangan SAK EMKM | COOCA')
@section('description', 'Aplikasi akuntansi bisnis terintegrasi. Jurnal otomatis dari transaksi kasir dan stok, bagan akun (COA) standar SAK EMKM, buku besar, neraca saldo, serta laporan Laba Rugi dan Neraca real-time.')
@section('keywords', 'software akuntansi online, laporan keuangan umkm, aplikasi pembukuan laba rugi neraca, auto journal kasir pos, software akuntansi sak emkm')

@section('og_title', 'Software Akuntansi Online & Laporan Keuangan SAK EMKM | COOCA')
@section('og_description', 'Jurnal otomatis dari transaksi kasir dan stok, bagan akun (COA) standar SAK EMKM, buku besar, neraca saldo, serta laporan Laba Rugi real-time.')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Accounting & Financial Reporting",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Sistem akuntansi double-entry otomatis sesuai standar SAK EMKM dengan integrasi langsung ke operasional penjualan dan gudang.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Otomasi penjurnalan debit-kredit dari transaksi kasir POS dan penerimaan gudang",
    "Struktur Bagan Akun (Chart of Accounts) standar SAK EMKM siap pakai",
    "Laporan Laba Rugi, Neraca, dan Perubahan Modal real-time setiap saat",
    "Buku besar interaktif dengan audit trail drill-down ke faktur sumber",
    "Perhitungan penyusutan aset tetap (Depreciation) otomatis setiap akhir bulan"
  ]
}
</script>
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Apakah staf toko harus paham akuntansi debit-kredit untuk menggunakan sistem ini?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sama sekali tidak. Staf kasir dan staf gudang hanya bekerja seperti biasa melayani transaksi dan menerima barang. COOCA yang akan menerjemahkan setiap kejadian operasional menjadi jurnal akuntansi debit-kredit secara otomatis di belakang layar."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah bagan akun (COA) di COOCA bisa disesuaikan dengan kebutuhan bisnis kami?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. COOCA sudah menyediakan template COA standar SAK EMKM Indonesia, namun Anda bebas menambah akun induk, sub-akun cabang, kode departemen, maupun cost center sesuai struktur organisasi perusahaan Anda."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah akuntan eksternal atau konsultan pajak kami bisa diberikan akses khusus?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. Anda dapat membuatkan akun user khusus dengan role 'Akuntan / Auditor' yang hanya memiliki akses melihat laporan keuangan, buku besar, dan jurnal penyesuaian tanpa bisa mengubah data operasional kasir atau harga jual barang."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara COOCA mencatat penyusutan aset tetap seperti mesin dan kendaraan?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Daftarkan aset tetap Anda beserta nilai perolehan, umur ekonomis, dan metode penyusutan (garis lurus). Sistem secara otomatis menghitung dan membukukan jurnal beban penyusutan vs akumulasi penyusutan di akhir setiap bulan kalender."
      }
    }
  ]
}
</script>
    @endpush

@section('content')
    <div
        class="relative overflow-hidden bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

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
                                    Double-Entry &amp; Automated SAK EMKM Reporting
                                </p>
                            </div>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Laporan Laba Rugi &amp; Neraca Terbit Otomatis <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Tanpa
                                    Rekap Manual</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Tinggalkan lembur berhari-hari menjurnal nota kasir dan pembelian barang. Setiap transaksi penjualan, mutasi gudang, dan pengeluaran operasional otomatis membentuk jurnal debit-kredit rapi standar SAK EMKM.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Lihat Demo Akuntansi</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.finance') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Koneksi ke Manajemen Kas</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>GL</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>COA</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>EMKM</span>
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
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">Akurasi Jurnal &amp; Standar SAK EMKM</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Double-Entry Otomatis</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Standar SAK EMKM</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Real-Time Closing</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated Live General Ledger & Auto Journal UI -->
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
                                <span class="text-slate-400 font-medium">Balance</span>
                                <span class="font-extrabold text-white">Tepat 100%</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="scale" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span class="text-slate-400 font-medium">Standar</span>
                                <span class="font-extrabold text-white">SAK EMKM</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Balance Sempurna (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Double-Entry Real-Time</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">100% Klop</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>Auto-Jurnal POS &amp; Kas</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: Standar Laporan (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Standar Pelaporan</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">SAK EMKM</div>
                            <div class="text-[11px] font-semibold text-[#00C4D8] flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="file-check-2" class="w-3 h-3"></i>
                                <span>Neraca &amp; Laba Rugi</span>
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
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">General Ledger &bull; SAK EMKM</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold shrink-0">
                                    Real-Time Closing
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
                                        <span>cooca.id/app/accounting/general-ledger</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span class="text-[11px] font-medium text-emerald-400 font-mono">Balance: 100%</span>
                                </div>
                            </div>

                            {{-- Financial Statement Breakdown Card --}}
                            <div class="p-3 my-1.5 sm:my-2 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1.5 sm:space-y-2 text-xs">
                                <div class="flex justify-between items-center gap-2 pb-1.5 border-b border-white/10">
                                    <span class="text-slate-300 font-medium truncate">Pendapatan Usaha Bersih</span>
                                    <span class="font-mono font-bold text-white shrink-0">Rp 284.500.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-400 text-[11px]">
                                    <span class="truncate">Beban Pokok Penjualan (HPP)</span>
                                    <span class="font-mono text-rose-400 shrink-0">(Rp 114.200.000)</span>
                                </div>
                                <div
                                    class="flex justify-between items-center gap-2 py-1 border-t border-white/10 font-semibold text-emerald-400">
                                    <span class="truncate">Laba Kotor (Gross Profit)</span>
                                    <span class="font-mono shrink-0">Rp 170.300.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-400 text-[11px]">
                                    <span class="truncate">Beban Operasional &amp; Gaji</span>
                                    <span class="font-mono text-rose-400 shrink-0">(Rp 62.400.000)</span>
                                </div>
                                <div
                                    class="flex justify-between items-center gap-2 pt-1.5 border-t border-white/10 font-bold text-xs sm:text-sm text-white">
                                    <span class="text-emerald-400 truncate">Laba Bersih Operasional</span>
                                    <span class="font-mono text-emerald-400 shrink-0">Rp 107.900.000</span>
                                </div>
                            </div>

                            {{-- Auto Journal Audit Stream --}}
                            <div class="space-y-1 text-xs text-left">
                                <div class="text-[10px] uppercase font-mono text-slate-400 px-1">Log Auto-Journal Terbaru:</div>

                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 font-mono text-[10.5px] sm:text-[11px] space-y-1">
                                    <div class="flex items-center justify-between gap-2 text-slate-400 text-[10px]">
                                        <span class="truncate">Ref: POS#TRX-9402 (Kasir Sudirman)</span>
                                        <span class="text-slate-500 shrink-0">23 Sep • 14:22 WIB</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2 text-slate-200">
                                        <span class="pl-2 truncate">[1110] Kas Bank Penampungan QRIS</span>
                                        <span class="text-emerald-400 shrink-0">Debit: Rp 79.200</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2 text-slate-400">
                                        <span class="pl-6 truncate">[4100] Pendapatan Penjualan Outlet</span>
                                        <span class="shrink-0">Kredit: Rp 72.000</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2 text-slate-400">
                                        <span class="pl-6 truncate">[2150] Hutang Pajak Restoran (PB1)</span>
                                        <span class="shrink-0">Kredit: Rp 7.200</span>
                                    </div>
                                    <div
                                        class="flex justify-between items-center gap-2 text-slate-400 pt-1 border-t border-white/10">
                                        <span class="pl-2 truncate">[5100] Beban Pokok Penjualan (HPP)</span>
                                        <span class="text-emerald-400 shrink-0">Debit: Rp 24.000</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2 text-slate-400">
                                        <span class="pl-6 truncate">[1140] Persediaan Barang Dagang</span>
                                        <span class="shrink-0">Kredit: Rp 24.000</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Verification Status --}}
                            <div
                                class="mt-2 pt-2 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[11px] text-slate-400">
                                <span class="flex items-center gap-1.5 truncate">
                                    <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                    <span class="truncate">Bagan Akun: Template SAK EMKM Standar</span>
                                </span>
                                <a href="{{ route('public.demo') }}"
                                    class="text-[#00C4D8] hover:underline font-medium shrink-0">Buku Besar Detail →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Frustrasi Pembukuan Manual Akhir Bulan --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Tantangan Akuntansi Bisnis
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Apakah Anda Masih Menunggu Tanggal 20 Bulan Depan Hanya untuk Mengetahui Laba Bisnis?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Ketika pembukuan terpisah dari operasional sehari-hari, data keuangan selalu terlambat dan tidak
                        bisa lagi digunakan untuk mengambil keputusan taktis.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="file-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Laporan Keuangan Selalu Terlambat
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Akuntan harus menunggu tumpukan nota dari cabang dikumpulkan secara fisik, lalu mengetik ulang
                            ke software akuntansi terpisah. Laporan bulan lalu baru selesai saat bulan berikutnya hampir
                            habis.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="file-diff" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Data Penjualan & Kas Tidak Klop</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Total omzet di aplikasi kasir sering tidak sama dengan angka yang dicatat bagian akunting karena
                            ada retur, diskon voucher, atau biaya komisi payment gateway yang tidak terkoordinasi.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="shield-question" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Kesulitan Syarat Audit & Pajak</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Saat mengajukan kredit ke perbankan atau menyusun SPT Tahunan Badan, Anda panik karena tidak
                            memiliki Neraca Saldo dan Buku Besar yang rapi dan dapat dipertanggungjawabkan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE ACCOUNTING CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-white dark:bg-[#0B132B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                    Kemampuan Akuntansi COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Standar Akuntansi Profesional Tanpa Kerumitan Manual
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Dirancang untuk memudahkan pemilik usaha maupun tim profesional finance & tax dalam satu platform
                    terpadu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Jurnal Otomatis (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="zap" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Penjurnalan Otomatis dari Setiap Transaksi
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Setiap transaksi kasir, pelunasan invoice, pembelian bahan baku ke supplier, penyesuaian stok
                            opname, hingga penggajian karyawan langsung membentuk entri jurnal debit-kredit secara otomatis.
                            Anda tidak perlu mengetik ulang satu pun nomor akun secara manual.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Keandalan Sistem:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Debit & Kredit Selalu Seimbang (Zero
                            Discrepancy)
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: COA Fleksibel SAK EMKM (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="folder-tree" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Bagan Akun (COA) Standar SAK EMKM
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Tersedia bagan akun standar Indonesia siap pakai untuk Aset, Kewajiban, Ekuitas, Pendapatan, dan
                            Beban. Anda dapat menambahkan sub-akun spesifik per divisi atau outlet dengan hierarki tak
                            terbatas.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Kepatuhan Standar</span>
                        <span class="text-[#007AFF] dark:text-[#00C4D8] font-bold">SAK EMKM / EP Ready</span>
                    </div>
                </div>

                {{-- Bento Card 3: Drill-Down Buku Besar (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Buku Besar Interaktif</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Klik angka nominal mana saja di laporan Laba Rugi untuk langsung membuka Buku Besar dan melihat
                        dokumen sumbernya (struk kasir, invoice supplier, atau bukti transfer).
                    </p>
                </div>

                {{-- Bento Card 4: Penyusutan Aset Tetap Otomatis (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Penyusutan Aset Tetap</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Catat mesin kopi, kendaraan operasional, dan renovasi toko. COOCA menghitung penyusutan bulanan
                        secara otomatis sehingga nilai buku aset di Neraca selalu realistis.
                    </p>
                </div>

                {{-- Bento Card 5: Jurnal Penyesuaian & Manual Voucher (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="file-pen" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Jurnal Penyesuaian Manual</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Akuntan tetap memiliki fleksibilitas penuh untuk membuat Journal Voucher manual, amortisasi biaya
                        dibayar di muka, atau penyesuaian pajak akhir tahun dengan persetujuan owner.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. FINANCIAL STATEMENTS SUITE: Apa Saja Laporan yang Dihasilkan (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Laporan Standar Keuangan Lengkap</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Laporan Keuangan Siap Pajak & Audit Tanpa Stres
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Semua laporan dapat difilter per periode, dikonsolidasikan per entitas anak usaha, dan diekspor ke
                        PDF/Excel dalam hitungan detik.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Report 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Laporan Laba Rugi</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Lihat pendapatan bersih, HPP aktual, biaya operasional, dan margin laba bersih. Dukungan
                            perbandingan antar bulan untuk melihat tren performa bisnis.
                        </p>
                    </div>

                    {{-- Report 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Laporan Neraca (Balance Sheet)</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Keseimbangan total aset lancar dan aset tetap terhadap kewajiban hutang dagang serta ekuitas
                            modal pemilik secara akurat setiap akhir periode.
                        </p>
                    </div>

                    {{-- Report 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-500/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-blue-500/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Laporan Arus Kas</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Pemetaan arus kas masuk dan keluar yang dibagi menurut aktivitas operasional, aktivitas
                            investasi, dan aktivitas pendanaan bisnis.
                        </p>
                    </div>

                    {{-- Report 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Neraca Saldo & Buku Besar</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Daftar saldo debit-kredit seluruh akun buku besar untuk memudahkan verifikasi audit internal
                            sebelum proses tutup buku akhir tahun.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 bg-white dark:bg-[#0B132B]">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar Akuntansi
                    COOCA</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah staf toko harus paham akuntansi debit-kredit untuk menggunakan sistem ini?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Sama sekali tidak. Staf kasir dan staf gudang hanya bekerja seperti biasa melayani transaksi dan
                        menerima barang. COOCA yang akan menerjemahkan setiap kejadian operasional menjadi jurnal akuntansi
                        debit-kredit secara otomatis di belakang layar.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah bagan akun (COA) di COOCA bisa disesuaikan dengan kebutuhan bisnis kami?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Bisa. COOCA sudah menyediakan template COA standar SAK EMKM Indonesia, namun Anda bebas menambah
                        akun induk, sub-akun cabang, kode departemen, maupun cost center sesuai struktur organisasi
                        perusahaan Anda.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah akuntan eksternal atau konsultan pajak kami bisa diberikan akses khusus?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Ya. Anda dapat membuatkan akun user khusus dengan role 'Akuntan / Auditor' yang hanya memiliki akses
                        melihat laporan keuangan, buku besar, dan jurnal penyesuaian tanpa bisa mengubah data operasional
                        kasir atau harga jual barang.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara COOCA mencatat penyusutan aset tetap seperti mesin dan kendaraan?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Daftarkan aset tetap Anda beserta nilai perolehan, umur ekonomis, dan metode penyusutan (garis
                        lurus). Sistem secara otomatis menghitung dan membukukan jurnal beban penyusutan vs akumulasi
                        penyusutan di akhir setiap bulan kalender.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-1">
                        Modul Terkait
                    </h2>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Ekosistem Keuangan &
                        Operasional</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.erp.finance') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Keuangan & Kas Operasional
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola likuiditas harian, kas kecil cabang,
                        dan approval pengeluaran dana.</p>
                </a>

                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Transaksi kasir yang langsung menjurnal
                        pendapatan dan kas masuk.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Stok Gudang & HPP
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perhitungan HPP Moving Average yang langsung
                        masuk ke laporan Laba Rugi.</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Profitabilitas
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Visualisasi grafik margin kotor vs margin
                        bersih per unit bisnis.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Ambient lights inside CTA --}}
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Dapatkan Laporan Keuangan Rapi & Akurat Setiap Saat
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Bebaskan waktu Anda dari rutinitas rekap manual dan nikmati kepastian pembukuan otomatis berstandar
                        SAK EMKM dengan COOCA Accounting.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Modul Akuntansi
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Pelajari Paket Harga
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
