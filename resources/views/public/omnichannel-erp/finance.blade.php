@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Keuangan & Arus Kas Bisnis Terintegrasi | COOCA')
@section('description', 'Aplikasi manajemen keuangan bisnis dan cash flow operasional. Pantau saldo kas & bank multi-rekening, kontrol hutang piutang (AP/AR), kelola petty cash cabang, dan approval pengeluaran harian.')
@section('keywords', 'software manajemen keuangan, aplikasi cash flow bisnis, manajemen kas kecil petty cash, buku kas masuk keluar, kontrol hutang piutang')

@section('og_title', 'Software Manajemen Keuangan & Arus Kas Bisnis Terintegrasi | COOCA')
@section('og_description', 'Pantau saldo kas & bank multi-rekening, kontrol hutang piutang (AP/AR), kelola petty cash cabang, dan approval pengeluaran harian.')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Finance & Cash Flow Management",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Modul manajemen arus kas, hutang piutang, dan kas kecil operasional terpadu untuk pemilik bisnis dan manajer keuangan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Monitoring saldo rekening bank dan kas tunai outlet secara real-time",
    "Jadwal jatuh tempo hutang supplier (AP) dan penagihan piutang pelanggan (AR)",
    "Pencatatan kas kecil (Petty Cash) dengan bukti struk dan approval berjenjang",
    "Rekonsiliasi otomatis penerimaan kas kasir POS dan penjualan marketplace",
    "Proyeksi arus kas operasional untuk mencegah defisit kas mendadak"
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
      "name": "Apa perbedaan modul Keuangan (Finance) dengan modul Akuntansi (Accounting) di COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Modul Finance berfokus pada likuiditas kas nyata sehari-hari: uang masuk dari kasir, pembayaran tagihan supplier, approval kas kecil, serta jadwal penagihan piutang. Sedangkan modul Accounting berfokus pada pencatatan debit-kredit formal, buku besar, penyusutan aset, dan laporan laba rugi/neraca sesuai standar akuntansi."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah COOCA bisa mencatat rekening bank yang berbeda untuk operasional dan penerimaan?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mendaftarkan rekening bank tanpa batas (misal BCA Operasional, Mandiri Penerimaan POS, Kas Tunai Toko, dan Rekening Escrow). Perpindahan dana antar rekening tercatat rapi sebagai Mutasi Kas Internal."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara mengontrol staf cabang agar tidak asal mengeluarkan uang kas kecil?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA memiliki fitur Petty Cash dengan sistem Approval. Staf cabang mengajukan pencairan dana disertai foto struk/nota fisik. Uang tidak akan terpotong dari kas outlet sebelum disetujui oleh Supervisor atau Owner melalui aplikasi."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah ada peringatan saat piutang pelanggan atau invoice supplier mendekati jatuh tempo?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. Dashboard Finance menampilkan daftar tagihan piutang (Aging AR) dan hutang supplier (Aging AP) berdasarkan kategori: Belum Jatuh Tempo, 1-30 Hari, 31-60 Hari, dan Melewati Batas Waktu, sehingga cash flow Anda tetap terjaga sehat."
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
        <!-- ═══ 1. HERO SECTION (Full Viewport 45/55 Ratio) ══════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-84px)] lg:flex lg:items-center py-10 sm:py-14 w-full min-w-full">
            {{-- Ambient Glows --}}
            <div
                class="absolute -top-32 -right-32 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute -bottom-32 -left-32 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none -z-0">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full space-y-6">
                {{-- Breadcrumb --}}
                <nav class="pb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs text-slate-400">
                        <li><a href="{{ route('landing') }}" class="hover:text-[#00C4D8] transition-colors">Home</a></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li><a href="{{ route('public.erp.erp') }}"
                                class="hover:text-[#00C4D8] transition-colors">Omnichannel ERP</a></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li class="text-white font-semibold" aria-current="page">Keuangan &amp; Kas Operasional</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {{-- Left Column: Eyebrow, Headline, Value Proposition & Action CTAs (Mobile Center, Desktop Left ~ 5 Cols) --}}
                    <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Operational Cash Flow &amp; Treasury Control
                            </p>

                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Kendalikan Arus Kas, Hutang, &amp; Piutang <span class="text-[#00C4D8]">Secara Real-Time</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            Ketahui persis berapa uang kas nyata perusahaan Anda hari ini. Pantau saldo seluruh rekening bank, tagih piutang yang tertunda, jadwalkan pelunasan supplier, dan kunci pengeluaran kas kecil dengan sistem persetujuan digital.
                        </p>

                        {{-- Action CTAs (Centered on Mobile, Row on Desktop) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 pt-2 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200 min-h-[48px]">
                                <span>Coba Modul Keuangan</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.accounting') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm font-semibold text-sm transition-all min-h-[48px]">
                                <span>Pelajari Modul Akuntansi</span>
                            </a>
                        </div>

                        {{-- Financial Control Indicators (Centered on Mobile) --}}
                        <div class="pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-3 gap-3.5 text-center sm:text-left w-full">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Monitoring Kas</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Multi-Rekening Bank</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Kontrol Piutang</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Aging Report AR</div>
                            </div>
                            <div class="min-w-0 col-span-2 sm:col-span-1">
                                <div class="text-xs text-slate-400 font-medium truncate">Petty Cash</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Digital Approval</div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Simulated Financial Cash Flow Dashboard (7 Cols ~ 58%) --}}
                    <div class="lg:col-span-7">
                        <div
                            class="relative rounded-2xl bg-[#0E1E45]/80 border border-white/10 p-4 sm:p-5 shadow-2xl backdrop-blur-md text-white">

                            {{-- Dashboard Header --}}
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="p-1.5 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                        <i data-lucide="wallet-cards" class="w-4 h-4"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Ringkasan Likuiditas Bisnis</div>
                                        <div class="text-[10px] text-slate-400 truncate">Seluruh Entitas • Real-time Sync
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="self-start sm:self-auto px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold shrink-0">
                                    Cash Runway: 4.8 Bulan
                                </span>
                            </div>

                            {{-- Total Cash & Bank Accounts Cards --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 my-3">
                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 min-w-0">
                                    <div class="text-[10px] text-slate-400 font-medium truncate">BCA Operasional (Pusat)
                                    </div>
                                    <div class="text-sm sm:text-base font-bold text-white font-mono mt-0.5 truncate">Rp
                                        148.420.000</div>
                                    <div class="text-[10px] text-emerald-400 flex items-center gap-1 mt-1 truncate">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3 shrink-0"></i> <span
                                            class="truncate">Masuk: Rp 12.8M hari ini</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 min-w-0">
                                    <div class="text-[10px] text-slate-400 font-medium truncate">Kas Tunai Cabang (3 Toko)
                                    </div>
                                    <div class="text-sm sm:text-base font-bold text-white font-mono mt-0.5 truncate">Rp
                                        14.250.000</div>
                                    <div class="text-[10px] text-slate-300 flex items-center gap-1 mt-1 truncate">
                                        <i data-lucide="lock" class="w-3 h-3 text-[#00C4D8] shrink-0"></i> <span
                                            class="truncate">Rekonsiliasi Kasir Selesai</span>
                                    </div>
                                </div>
                            </div>

                            {{-- AP / AR Status Widget --}}
                            <div class="space-y-2 text-xs">
                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-white truncate">Piutang Pelanggan (AR) Jatuh Tempo
                                            </div>
                                            <div class="text-[10px] text-slate-400 truncate">3 Invoice Korporat • Status:
                                                Reminder Terkirim</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-emerald-400 font-mono whitespace-nowrap">Rp 32.500.000
                                        </div>
                                        <span class="text-[10px] text-slate-400">Tagih Sekarang</span>
                                    </div>
                                </div>

                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="w-2 h-2 rounded-full bg-rose-400 shrink-0"></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-white truncate">Hutang Supplier Bahan Baku (AP)
                                            </div>
                                            <div class="text-[10px] text-slate-400 truncate">Jatuh Tempo: 28 September (PT
                                                Sumber Kopi)</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-rose-400 font-mono whitespace-nowrap">Rp 18.200.000</div>
                                        <span class="text-[10px] text-slate-400">Jadwalkan Bayar</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Active Petty Cash Request Approval --}}
                            <div
                                class="mt-3 p-2.5 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <i data-lucide="receipt" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="text-white font-medium">Kas Kecil Toko Sudirman:</span>
                                        <span class="text-slate-300 text-[11px]"> Bensin & Galon (Rp 185.000)</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-auto">
                                    <button
                                        class="px-2 py-1 rounded bg-white/10 text-slate-300 text-[10px] hover:bg-white/20 transition-colors">Tinjau
                                        Struk</button>
                                    <button
                                        class="px-2.5 py-1 rounded bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-[10px] transition-colors">Setujui</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Jebakan Arus Kas yang Sering Menenggelamkan Bisnis --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Tantangan Likuiditas Nyata
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Mengapa Bisnis yang Kelihatannya Ramai Bisa Mengalami Krisis Kas?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Banyak pengusaha terkejut ketika saldo bank kosong di akhir bulan untuk membayar gaji staf dan sewa
                        tempat, padahal buku penjualan mencatat angka omzet yang tinggi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="clock-alert" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Piutang Macet Tak Tertagih</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Invoice dikirim tapi tidak ada sistem pengingat jatuh tempo. Pelanggan lupa membayar dan Anda
                            sungkan menagih karena data bukti penerimaan barang terselip di arsip kertas.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="receipt-text" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Bocor Halus di Kas Kecil Cabang</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pengeluaran operasional kecil-kecil (beli es batu, gas, ongkos kirim) tidak tercatat terpusat.
                            Uang kas laci kasir habis tanpa pertanggungjawaban nota fisik yang jelas.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="calendar-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Jadwal Bayar Supplier Bentrok</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Tiga tagihan bahan baku utama jatuh tempo di tanggal yang sama dengan waktu gajian karyawan.
                            Akibat tidak ada kalender arus kas, pemilik bisnis terpaksa mencari pinjaman darurat.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE FINANCE CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">Fitur
                    Manajemen Kas & Treasury</h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Alat Pengendalian Finansial yang Presisi untuk Owner
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Memberikan kepastian angka kas tanpa harus menunggu bagian keuangan menyusun laporan berhari-hari.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Multi-Akun Kas & Bank (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="landmark" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Konsolidasi Seluruh Rekening Bank & Kas Tunai
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Lihat posisi uang kas perusahaan dalam satu tampilan tunggal. Kelola rekening penampungan
                            pembayaran QRIS, rekening giro operasional, rekening payroll staf, hingga brankas kas fisik di
                            masing-masing cabang toko dengan mutasi saldo yang terverifikasi.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Buku Kas Harian:</span>
                        <span class="text-[#007AFF] dark:text-[#00C4D8] font-semibold flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Seluruh Mutasi Kas Terkunci Aman
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Petty Cash & Digital Approval (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="stamp" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Approval Kas Kecil & Pengeluaran Digital
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Staf mengajukan kebutuhan dana operasional langsung dari smartphone dengan lampiran foto nota
                            fisik. Uang kas hanya dapat dicairkan setelah disetujui manajer, menghentikan kebocoran anggaran
                            selamanya.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Sistem Plafon Kasir</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">Maks Rp 500rb / Transaksi</span>
                    </div>
                </div>

                {{-- Bento Card 3: Kontrol Hutang Supplier / AP (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Jadwal Pembayaran Supplier (AP)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Catat termin pembayaran tempo (TOP 14/30/60 hari) untuk setiap faktur supplier. Dapatkan notifikasi
                        sebelum jatuh tempo agar tidak terkena penalti dan menjaga hubungan baik dengan vendor.
                    </p>
                </div>

                {{-- Bento Card 4: Penagihan Piutang Pelanggan / AR (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Penagihan Piutang Cepat (AR)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Pantau umur piutang (Aging AR Report). Kirim pengingat invoice langsung ke WhatsApp atau email klien
                        dalam satu klik disertai link pembayaran untuk mempercepat pengembalian kas perusahaan.
                    </p>
                </div>

                {{-- Bento Card 5: Proyeksi Arus Kas Operasional (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="line-chart" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Proyeksi Likuiditas & Runway</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Simulasikan arus kas masuk yang diharapkan vs komitmen pengeluaran wajib 30 hari ke depan. Pastikan
                        bisnis selalu memiliki bantalan likuiditas yang cukup untuk ekspansi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Bagaimana Uang Mengalir di COOCA (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Arus Keuangan Terhubung</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Dari Transaksi Penjualan Hingga Saldo Kas Akhir
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Modul Finance bekerja berdampingan dengan Kasir POS, Gudang, dan Akuntansi untuk menjaga setiap
                        rupiah uang perusahaan tetap terpantau.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Kasir Terima Bayar</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Penjualan via POS kasir atau marketplace masuk. Pembayaran tunai masuk ke Kas Laci Cabang,
                            sedangkan pembayaran QRIS atau transfer masuk ke akun Bank Penampungan.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Rekonsiliasi Shift</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Saat tutup kasir, uang fisik disetor ke rekening bank atau diserahkan ke brankas pusat melalui
                            dokumen serah terima kas resmi. Saldo terverifikasi tanpa selisih.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-xs border border-indigo-500/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Pelunasan Kewajiban</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Bagian keuangan melihat jadwal tagihan supplier dan gaji staf yang telah jatuh tempo, lalu
                            mengeksekusi pembayaran sesuai prioritas ketersediaan dana kas.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Otomasi ke Akuntansi</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Setiap mutasi kas masuk dan keluar secara simultan membentuk jurnal akuntansi dan memperbarui
                            Laporan Arus Kas (Cash Flow Statement) perusahaan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar Keuangan
                    COOCA</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apa perbedaan modul Keuangan (Finance) dengan modul Akuntansi (Accounting) di COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Modul Finance berfokus pada likuiditas kas nyata sehari-hari: uang masuk dari kasir, pembayaran
                        tagihan supplier, approval kas kecil, serta jadwal penagihan piutang. Sedangkan modul Accounting
                        berfokus pada pencatatan debit-kredit formal, buku besar, penyusutan aset, dan laporan laba
                        rugi/neraca sesuai standar akuntansi.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah COOCA bisa mencatat rekening bank yang berbeda untuk operasional dan penerimaan?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Bisa. Anda dapat mendaftarkan rekening bank tanpa batas (misal BCA Operasional, Mandiri Penerimaan
                        POS, Kas Tunai Toko, dan Rekening Escrow). Perpindahan dana antar rekening tercatat rapi sebagai
                        Mutasi Kas Internal.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara mengontrol staf cabang agar tidak asal mengeluarkan uang kas kecil?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        COOCA memiliki fitur Petty Cash dengan sistem Approval. Staf cabang mengajukan pencairan dana
                        disertai foto struk/nota fisik. Uang tidak akan terpotong dari kas outlet sebelum disetujui oleh
                        Supervisor atau Owner melalui aplikasi.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah ada peringatan saat piutang pelanggan atau invoice supplier mendekati jatuh
                            tempo?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Ya. Dashboard Finance menampilkan daftar tagihan piutang (Aging AR) dan hutang supplier (Aging AP)
                        berdasarkan kategori: Belum Jatuh Tempo, 1-30 Hari, 31-60 Hari, dan Melewati Batas Waktu, sehingga
                        cash flow Anda tetap terjaga sehat.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-1">Modul
                        Terkait</h2>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Integrasi Pengelolaan
                        Finansial</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Semua Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Akuntansi & Jurnal
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Laporan Laba Rugi, Neraca, dan Buku Besar
                        otomatis sesuai SAK EMKM.</p>
                </a>

                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Penerimaan kas kasir yang langsung tercatat
                        ke buku kas harian.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Manajemen Stok & PO
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sinkronisasi faktur pembelian barang gudang
                        dengan hutang dagang (AP).</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Arus Kas
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Grafik tren kas masuk vs keluar dan proyeksi
                        modal kerja.</p>
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
                        Amankan Arus Kas dan Likuiditas Bisnis Anda Sekarang
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Dapatkan kejelasan posisi kas setiap hari, hentikan piutang macet, dan kelola keuangan bisnis dengan
                        tenang bersama COOCA Finance.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Modul Keuangan
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Konsultasi Finansial Bisnis
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
