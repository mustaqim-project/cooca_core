@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Keuangan & Arus Kas Bisnis Terintegrasi | COOCA')
@section('description', 'Aplikasi manajemen keuangan bisnis dan cash flow operasional. Pantau saldo kas & bank multi-rekening, kontrol hutang piutang (AP/AR), kelola petty cash cabang, dan approval pengeluaran harian.')
@section('keywords', 'software manajemen keuangan, aplikasi cash flow bisnis, manajemen kas kecil petty cash, buku kas masuk keluar, kontrol hutang piutang')

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
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-blue-500/10 via-emerald-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Keuangan & Kas Operasional</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200/60 dark:border-blue-800/40 text-blue-700 dark:text-blue-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                    <span>Operational Cash Flow & Treasury Control</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Kendalikan Arus Kas, Hutang, & Piutang Bisnis <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500">Secara Real-Time</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Ketahui persis berapa uang kas nyata perusahaan Anda hari ini. Pantau saldo seluruh rekening bank, tagih piutang yang tertunda, jadwalkan pelunasan supplier, dan kunci pengeluaran kas kecil dengan sistem persetujuan digital.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Modul Keuangan</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.erp.accounting') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Pelajari Modul Akuntansi</span>
                    </a>
                </div>

                {{-- Financial Control Indicators --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Monitoring Rekening</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Multi Bank & Kas Tunai</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kontrol Kas Kecil</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Approval Berjenjang</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Jadwal AP / AR</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Notifikasi Jatuh Tempo</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live Finance Treasury Dashboard --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Dashboard Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-blue-500/20 text-blue-400">
                                <i data-lucide="wallet-cards" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="font-bold text-neutral-200">Ringkasan Likuiditas Bisnis</div>
                                <div class="text-[10px] text-neutral-500">Seluruh Entitas • Real-time Sync</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">Cash Runway: 4.8 Bulan</span>
                    </div>

                    {{-- Total Cash & Bank Accounts Cards --}}
                    <div class="grid grid-cols-2 gap-2.5 my-3">
                        <div class="p-3 rounded-xl bg-neutral-950/80 border border-neutral-800">
                            <div class="text-[10px] text-neutral-400 font-medium">BCA Operasional (Pusat)</div>
                            <div class="text-sm sm:text-base font-bold text-white font-mono mt-0.5">Rp 148.420.000</div>
                            <div class="text-[10px] text-emerald-400 flex items-center gap-1 mt-1">
                                <i data-lucide="arrow-down-left" class="w-3 h-3"></i> Masuk: Rp 12.8M hari ini
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-neutral-950/80 border border-neutral-800">
                            <div class="text-[10px] text-neutral-400 font-medium">Kas Tunai Cabang (3 Toko)</div>
                            <div class="text-sm sm:text-base font-bold text-white font-mono mt-0.5">Rp 14.250.000</div>
                            <div class="text-[10px] text-neutral-400 flex items-center gap-1 mt-1">
                                <i data-lucide="lock" class="w-3 h-3 text-blue-400"></i> Rekonsiliasi Kasir Selesai
                            </div>
                        </div>
                    </div>

                    {{-- AP / AR Status Widget --}}
                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <div>
                                    <div class="font-medium text-white">Piutang Pelanggan (AR) Jatuh Tempo Minggu Ini</div>
                                    <div class="text-[10px] text-neutral-500">3 Invoice Korporat • Status: Reminder Terkirim</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-emerald-400 font-mono">Rp 32.500.000</div>
                                <span class="text-[10px] text-neutral-500">Tagih Sekarang</span>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <div>
                                    <div class="font-medium text-white">Hutang Supplier Bahan Baku (AP)</div>
                                    <div class="text-[10px] text-neutral-500">Jatuh Tempo: 28 September (PT Sumber Kopi)</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-rose-400 font-mono">Rp 18.200.000</div>
                                <span class="text-[10px] text-neutral-500">Jadwalkan Bayar</span>
                            </div>
                        </div>
                    </div>

                    {{-- Active Petty Cash Request Approval --}}
                    <div class="mt-3 p-2.5 rounded-xl bg-blue-950/30 border border-blue-800/40 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="receipt" class="w-4 h-4 text-blue-400"></i>
                            <div>
                                <span class="text-white font-medium">Kas Kecil Toko Sudirman:</span>
                                <span class="text-neutral-400 text-[11px]"> Bensin Operasional & Galon (Rp 185.000)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button class="px-2 py-1 rounded bg-neutral-800 text-neutral-300 text-[10px] hover:bg-neutral-700">Tinjau Struk</button>
                            <button class="px-2.5 py-1 rounded bg-blue-600 text-white font-bold text-[10px] hover:bg-blue-500">Setujui</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Jebakan Arus Kas yang Sering Menenggelamkan Bisnis --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-3">Tantangan Likuiditas Nyata</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Mengapa Bisnis yang Kelihatannya Ramai Bisa Mengalami Krisis Kas?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Banyak pengusaha terkejut ketika saldo bank kosong di akhir bulan untuk membayar gaji staf dan sewa tempat, padahal buku penjualan mencatat angka omzet yang tinggi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="clock-alert" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Piutang Macet Tak Tertagih</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Invoice dikirim tapi tidak ada sistem pengingat jatuh tempo. Pelanggan lupa membayar dan Anda sungkan menagih karena data bukti penerimaan barang terselip di arsip kertas.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="receipt-text" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Bocor Halus di Kas Kecil Cabang</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Pengeluaran operasional kecil-kecil (beli es batu, gas, ongkos kirim) tidak tercatat terpusat. Uang kas laci kasir habis tanpa pertanggungjawaban nota fisik yang jelas.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="calendar-x" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Jadwal Bayar Supplier Bentrok</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Tiga tagihan bahan baku utama jatuh tempo di tanggal yang sama dengan waktu gajian karyawan. Akibat tidak ada kalender arus kas, pemilik bisnis terpaksa mencari pinjaman darurat.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE FINANCE CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-3">Fitur Manajemen Kas & Treasury</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Alat Pengendalian Finansial yang Presisi untuk Owner
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Memberikan kepastian angka kas tanpa harus menunggu bagian keuangan menyusun laporan berhari-hari.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Multi-Akun Kas & Bank (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="landmark" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Konsolidasi Seluruh Rekening Bank & Kas Tunai
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Lihat posisi uang kas perusahaan dalam satu tampilan tunggal. Kelola rekening penampungan pembayaran QRIS, rekening giro operasional, rekening payroll staf, hingga brankas kas fisik di masing-masing cabang toko dengan mutasi saldo yang terverifikasi.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Buku Kas Harian:</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Seluruh Mutasi Kas Terkunci Aman
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Petty Cash & Digital Approval (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="stamp" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Approval Kas Kecil & Pengeluaran Digital
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Staf mengajukan kebutuhan dana operasional langsung dari smartphone dengan lampiran foto nota fisik. Uang kas hanya dapat dicairkan setelah disetujui manajer, menghentikan kebocoran anggaran selamanya.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Sistem Plafon Kasir</span>
                    <span class="text-emerald-500 font-bold">Maks Rp 500rb / Transaksi</span>
                </div>
            </div>

            {{-- Bento Card 3: Kontrol Hutang Supplier / AP (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="calendar-check" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Jadwal Pembayaran Supplier (AP)</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Catat termin pembayaran tempo (TOP 14/30/60 hari) untuk setiap faktur supplier. Dapatkan notifikasi sebelum jatuh tempo agar tidak terkena penalti dan menjaga hubungan baik dengan vendor.
                </p>
            </div>

            {{-- Bento Card 4: Penagihan Piutang Pelanggan / AR (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Penagihan Piutang Cepat (AR)</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Pantau umur piutang (Aging AR Report). Kirim pengingat invoice langsung ke WhatsApp atau email klien dalam satu klik disertai link pembayaran untuk mempercepat pengembalian kas perusahaan.
                </p>
            </div>

            {{-- Bento Card 5: Proyeksi Arus Kas Operasional (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="line-chart" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Proyeksi Likuiditas & Runway</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Simulasikan arus kas masuk yang diharapkan vs komitmen pengeluaran wajib 30 hari ke depan. Pastikan bisnis selalu memiliki bantalan likuiditas yang cukup untuk ekspansi.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Bagaimana Uang Mengalir di COOCA --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-400 text-xs font-semibold mb-3 border border-blue-500/30">
                    <span>Arus Keuangan Terhubung</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Dari Transaksi Penjualan Hingga Saldo Kas Akhir
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Modul Finance bekerja berdampingan dengan Kasir POS, Gudang, dan Akuntansi untuk menjaga setiap rupiah uang perusahaan tetap terpantau.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Kasir Terima Bayar</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Penjualan via POS kasir atau marketplace masuk. Pembayaran tunai masuk ke Kas Laci Cabang, sedangkan pembayaran QRIS atau transfer masuk ke akun Bank Penampungan.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Rekonsiliasi Shift</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Saat tutup kasir, uang fisik disetor ke rekening bank atau diserahkan ke brankas pusat melalui dokumen serah terima kas resmi. Saldo terverifikasi tanpa selisih.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Pelunasan Kewajiban</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Bagian keuangan melihat jadwal tagihan supplier dan gaji staf yang telah jatuh tempo, lalu mengeksekusi pembayaran sesuai prioritas ketersediaan dana kas.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Otomasi ke Akuntansi</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Setiap mutasi kas masuk dan keluar secara simultan membentuk jurnal akuntansi dan memperbarui Laporan Arus Kas (Cash Flow Statement) perusahaan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Keuangan COOCA</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apa perbedaan modul Keuangan (Finance) dengan modul Akuntansi (Accounting) di COOCA?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Modul Finance berfokus pada likuiditas kas nyata sehari-hari: uang masuk dari kasir, pembayaran tagihan supplier, approval kas kecil, serta jadwal penagihan piutang. Sedangkan modul Accounting berfokus pada pencatatan debit-kredit formal, buku besar, penyusutan aset, dan laporan laba rugi/neraca sesuai standar akuntansi.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah COOCA bisa mencatat rekening bank yang berbeda untuk operasional dan penerimaan?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Bisa. Anda dapat mendaftarkan rekening bank tanpa batas (misal BCA Operasional, Mandiri Penerimaan POS, Kas Tunai Toko, dan Rekening Escrow). Perpindahan dana antar rekening tercatat rapi sebagai Mutasi Kas Internal.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana cara mengontrol staf cabang agar tidak asal mengeluarkan uang kas kecil?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA memiliki fitur Petty Cash dengan sistem Approval. Staf cabang mengajukan pencairan dana disertai foto struk/nota fisik. Uang tidak akan terpotong dari kas outlet sebelum disetujui oleh Supervisor atau Owner melalui aplikasi.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah ada peringatan saat piutang pelanggan atau invoice supplier mendekati jatuh tempo?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Ya. Dashboard Finance menampilkan daftar tagihan piutang (Aging AR) dan hutang supplier (Aging AP) berdasarkan kategori: Belum Jatuh Tempo, 1-30 Hari, 31-60 Hari, dan Melewati Batas Waktu, sehingga cash flow Anda tetap terjaga sehat.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Integrasi Pengelolaan Finansial</p>
            </div>
            <a href="{{ route('public.erp.erp') }}" class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lihat Semua Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.erp.accounting') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Akuntansi & Jurnal</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Laporan Laba Rugi, Neraca, dan Buku Besar otomatis sesuai SAK EMKM.</p>
            </a>

            <a href="{{ route('public.erp.pos') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Point of Sale (POS)</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Penerimaan kas kasir yang langsung tercatat ke buku kas harian.</p>
            </a>

            <a href="{{ route('public.erp.inventory') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Manajemen Stok & PO</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Sinkronisasi faktur pembelian barang gudang dengan hutang dagang (AP).</p>
            </a>

            <a href="{{ route('public.erp.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Analitik Arus Kas</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Grafik tren kas masuk vs keluar dan proyeksi modal kerja.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Amankan Arus Kas dan Likuiditas Bisnis Anda Sekarang
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Dapatkan kejelasan posisi kas setiap hari, hentikan piutang macet, dan kelola keuangan bisnis dengan tenang bersama COOCA Finance.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Modul Keuangan
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Finansial Bisnis
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
