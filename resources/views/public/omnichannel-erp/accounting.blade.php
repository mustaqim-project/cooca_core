@extends('layouts.public_marketing')

@section('title', 'Software Akuntansi Online & Laporan Keuangan SAK EMKM | COOCA')
@section('description', 'Aplikasi akuntansi bisnis terintegrasi. Jurnal otomatis dari transaksi kasir dan stok, bagan akun (COA) standar SAK EMKM, buku besar, neraca saldo, serta laporan Laba Rugi dan Neraca real-time.')
@section('keywords', 'software akuntansi online, laporan keuangan umkm, aplikasi pembukuan laba rugi neraca, auto journal kasir pos, software akuntansi sak emkm')

@push('seo')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
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
  "@context": "https://schema.org",
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
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-indigo-500/10 via-blue-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Akuntansi & Pembukuan</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/40 text-indigo-700 dark:text-indigo-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    <span>Double-Entry & Automated SAK EMKM Reporting</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Laporan Laba Rugi & Neraca Terbit Otomatis <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-blue-600 to-emerald-500">Tanpa Rekap Manual</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Tinggalkan lembur berhari-hari menjurnal nota kasir dan pembelian barang. Setiap transaksi penjualan, mutasi gudang, dan pengeluaran operasional otomatis membentuk jurnal debit-kredit yang rapi sesuai standar akuntansi Indonesia.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Lihat Demo Akuntansi</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.erp.finance') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Koneksi ke Manajemen Kas</span>
                    </a>
                </div>

                {{-- Trust Specs --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Standar Akuntansi</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">SAK EMKM & SAK EP</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Metode Pembukuan</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Double-Entry Otomatis</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kecepatan Laporan</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Real-Time Closing</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live General Ledger & Auto Journal UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Financial Report Header Tabs --}}
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-500/20 text-indigo-400">
                                <i data-lucide="scale" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="font-bold text-neutral-200">Laporan Laba Rugi Komprehensif</div>
                                <div class="text-[10px] text-neutral-500">Periode: Berjalan (September 2026) • Standar EMKM</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">Balance: Tepat 100%</span>
                    </div>

                    {{-- Financial Statement Breakdown Card --}}
                    <div class="p-3 my-3 rounded-xl bg-neutral-950/80 border border-neutral-800 space-y-2 text-xs">
                        <div class="flex justify-between items-center pb-1.5 border-b border-neutral-800/80">
                            <span class="text-neutral-300 font-medium">Pendapatan Usaha Bersih</span>
                            <span class="font-mono font-bold text-white">Rp 284.500.000</span>
                        </div>
                        <div class="flex justify-between items-center text-neutral-400 text-[11px]">
                            <span>Beban Pokok Penjualan (HPP)</span>
                            <span class="font-mono text-rose-400">(Rp 114.200.000)</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-t border-neutral-800/60 font-semibold text-emerald-400">
                            <span>Laba Kotor (Gross Profit)</span>
                            <span class="font-mono">Rp 170.300.000</span>
                        </div>
                        <div class="flex justify-between items-center text-neutral-400 text-[11px]">
                            <span>Beban Operasional & Gaji</span>
                            <span class="font-mono text-rose-400">(Rp 62.400.000)</span>
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-neutral-800 font-bold text-sm text-white">
                            <span class="text-emerald-400">Laba Bersih Operasional</span>
                            <span class="font-mono text-emerald-400">Rp 107.900.000</span>
                        </div>
                    </div>

                    {{-- Auto Journal Audit Stream --}}
                    <div class="space-y-1.5 text-xs text-left">
                        <div class="text-[10px] uppercase font-mono text-neutral-500 px-1">Log Auto-Journal Terbaru:</div>
                        
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 font-mono text-[11px] space-y-1">
                            <div class="flex items-center justify-between text-neutral-400 text-[10px]">
                                <span>Ref: POS#TRX-9402 (Kasir Sudirman)</span>
                                <span class="text-neutral-500">23 Sep • 14:22 WIB</span>
                            </div>
                            <div class="flex justify-between text-neutral-300">
                                <span class="pl-2">[1110] Kas Bank Penampungan QRIS</span>
                                <span class="text-emerald-400">Debit: Rp 79.200</span>
                            </div>
                            <div class="flex justify-between text-neutral-400">
                                <span class="pl-6">[4100] Pendapatan Penjualan Outlet</span>
                                <span>Kredit: Rp 72.000</span>
                            </div>
                            <div class="flex justify-between text-neutral-400">
                                <span class="pl-6">[2150] Hutang Pajak Restoran (PB1)</span>
                                <span>Kredit: Rp 7.200</span>
                            </div>
                            <div class="flex justify-between text-neutral-400 pt-1 border-t border-neutral-800/60">
                                <span class="pl-2">[5100] Beban Pokok Penjualan (HPP)</span>
                                <span class="text-emerald-400">Debit: Rp 24.000</span>
                            </div>
                            <div class="flex justify-between text-neutral-400">
                                <span class="pl-6">[1140] Persediaan Barang Dagang</span>
                                <span>Kredit: Rp 24.000</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Verification Status --}}
                    <div class="mt-3 pt-2 border-t border-neutral-800 flex items-center justify-between text-[11px] text-neutral-400">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-indigo-400"></i>
                            Bagan Akun: Template SAK EMKM Standar
                        </span>
                        <a href="{{ route('public.demo') }}" class="text-indigo-400 hover:underline font-medium">Buku Besar Detail →</a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Frustrasi Pembukuan Manual Akhir Bulan --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-3">Tantangan Akuntansi Bisnis</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Apakah Anda Masih Menunggu Tanggal 20 Bulan Depan Hanya untuk Mengetahui Laba Bisnis?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Ketika pembukuan terpisah dari operasional sehari-hari, data keuangan selalu terlambat dan tidak bisa lagi digunakan untuk mengambil keputusan taktis.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="file-x" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Laporan Keuangan Selalu Terlambat</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Akuntan harus menunggu tumpukan nota dari cabang dikumpulkan secara fisik, lalu mengetik ulang ke software akuntansi terpisah. Laporan bulan lalu baru selesai saat bulan berikutnya hampir habis.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="file-diff" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Data Penjualan & Kas Tidak Klop</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Total omzet di aplikasi kasir sering tidak sama dengan angka yang dicatat bagian akunting karena ada retur, diskon voucher, atau biaya komisi payment gateway yang tidak terkoordinasi.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="shield-question" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Kesulitan Syarat Audit & Pajak</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Saat mengajukan kredit ke perbankan atau menyusun SPT Tahunan Badan, Anda panik karena tidak memiliki Neraca Saldo dan Buku Besar yang rapi dan dapat dipertanggungjawabkan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE ACCOUNTING CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-3">Kemampuan Akuntansi COOCA</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Standar Akuntansi Profesional Tanpa Kerumitan Manual
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Dirancang untuk memudahkan pemilik usaha maupun tim profesional finance & tax dalam satu platform terpadu.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Jurnal Otomatis (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Penjurnalan Otomatis dari Setiap Transaksi
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Setiap transaksi kasir, pelunasan invoice, pembelian bahan baku ke supplier, penyesuaian stok opname, hingga penggajian karyawan langsung membentuk entri jurnal debit-kredit secara otomatis. Anda tidak perlu mengetik ulang satu pun nomor akun secara manual.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Keandalan Sistem:</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Debit dan Kredit Selalu Seimbang (Zero Discrepancy)
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: COA Fleksibel SAK EMKM (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="folder-tree" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Bagan Akun (COA) Standar SAK EMKM
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Tersedia bagan akun standar Indonesia siap pakai untuk Aset, Kewajiban, Ekuitas, Pendapatan, dan Beban. Anda dapat menambahkan sub-akun spesifik per divisi atau outlet dengan hierarki tak terbatas.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Kepatuhan Standar</span>
                    <span class="text-blue-500 font-bold">SAK EMKM / EP Ready</span>
                </div>
            </div>

            {{-- Bento Card 3: Drill-Down Buku Besar (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Buku Besar Interaktif</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Klik angka nominal mana saja di laporan Laba Rugi untuk langsung membuka Buku Besar dan melihat dokumen sumbernya (struk kasir, invoice supplier, atau bukti transfer).
                </p>
            </div>

            {{-- Bento Card 4: Penyusutan Aset Tetap Otomatis (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i data-lucide="archive" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Penyusutan Aset Tetap</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Catat mesin kopi, kendaraan operasional, dan renovasi toko. COOCA menghitung penyusutan bulanan secara otomatis sehingga nilai buku aset di Neraca selalu realistis.
                </p>
            </div>

            {{-- Bento Card 5: Jurnal Penyesuaian & Manual Voucher (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="file-pen" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Jurnal Penyesuaian Manual</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Akuntan tetap memiliki fleksibilitas penuh untuk membuat Journal Voucher manual, amortisasi biaya dibayar di muka, atau penyesuaian pajak akhir tahun dengan persetujuan owner.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. FINANCIAL STATEMENTS SUITE: Apa Saja Laporan yang Dihasilkan --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-400 text-xs font-semibold mb-3 border border-indigo-500/30">
                    <span>Laporan Standar Keuangan Lengkap</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Laporan Keuangan Siap Pajak & Audit Tanpa Stres
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Semua laporan dapat difilter per periode, dikonsolidasikan per entitas anak usaha, dan diekspor ke PDF/Excel dalam hitungan detik.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Report 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Laporan Laba Rugi</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Lihat pendapatan bersih, HPP aktual, biaya operasional, dan margin laba bersih. Dukungan perbandingan antar bulan untuk melihat tren performa bisnis.
                    </p>
                </div>

                {{-- Report 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Laporan Neraca (Balance Sheet)</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Keseimbangan total aset lancar dan aset tetap terhadap kewajiban hutang dagang serta ekuitas modal pemilik secara akurat setiap akhir periode.
                    </p>
                </div>

                {{-- Report 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Laporan Arus Kas</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Pemetaan arus kas masuk dan keluar yang dibagi menurut aktivitas operasional, aktivitas investasi, dan aktivitas pendanaan bisnis.
                    </p>
                </div>

                {{-- Report 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-600/30 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Neraca Saldo & Buku Besar</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Daftar saldo debit-kredit seluruh akun buku besar untuk memudahkan verifikasi audit internal sebelum proses tutup buku akhir tahun.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Akuntansi COOCA</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah staf toko harus paham akuntansi debit-kredit untuk menggunakan sistem ini?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Sama sekali tidak. Staf kasir dan staf gudang hanya bekerja seperti biasa melayani transaksi dan menerima barang. COOCA yang akan menerjemahkan setiap kejadian operasional menjadi jurnal akuntansi debit-kredit secara otomatis di belakang layar.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah bagan akun (COA) di COOCA bisa disesuaikan dengan kebutuhan bisnis kami?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Bisa. COOCA sudah menyediakan template COA standar SAK EMKM Indonesia, namun Anda bebas menambah akun induk, sub-akun cabang, kode departemen, maupun cost center sesuai struktur organisasi perusahaan Anda.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah akuntan eksternal atau konsultan pajak kami bisa diberikan akses khusus?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Ya. Anda dapat membuatkan akun user khusus dengan role 'Akuntan / Auditor' yang hanya memiliki akses melihat laporan keuangan, buku besar, dan jurnal penyesuaian tanpa bisa mengubah data operasional kasir atau harga jual barang.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana cara COOCA mencatat penyusutan aset tetap seperti mesin dan kendaraan?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Daftarkan aset tetap Anda beserta nilai perolehan, umur ekonomis, dan metode penyusutan (garis lurus). Sistem secara otomatis menghitung dan membukukan jurnal beban penyusutan vs akumulasi penyusutan di akhir setiap bulan kalender.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Keuangan & Operasional</p>
            </div>
            <a href="{{ route('public.erp.erp') }}" class="text-xs sm:text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lihat Seluruh Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.erp.finance') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Keuangan & Kas Operasional</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kelola likuiditas harian, kas kecil cabang, dan approval pengeluaran dana.</p>
            </a>

            <a href="{{ route('public.erp.pos') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Point of Sale (POS)</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Transaksi kasir yang langsung menjurnal pendapatan dan kas masuk.</p>
            </a>

            <a href="{{ route('public.erp.inventory') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Stok Gudang & HPP</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Perhitungan HPP Moving Average yang langsung masuk ke laporan Laba Rugi.</p>
            </a>

            <a href="{{ route('public.erp.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Analitik Profitabilitas</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Visualisasi grafik margin kotor vs margin bersih per unit bisnis.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Dapatkan Laporan Keuangan Rapi & Akurat Setiap Saat
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Bebaskan waktu Anda dari rutinitas rekap manual dan nikmati kepastian pembukuan otomatis berstandar SAK EMKM dengan COOCA Accounting.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Modul Akuntansi
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Pelajari Paket Harga
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
