@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Inventori & Stok Multi-Gudang Terintegrasi | COOCA')
@section('description', 'Aplikasi inventori stok gudang multi-cabang terintegrasi. Pantau kartu stok perpetual, mutasi antar cabang, reorder point otomatis, resep bahan baku (BOM), dan stock opname tanpa tutup toko.')
@section('keywords', 'software inventori barang, aplikasi stok gudang, manajemen stok multi gudang, kartu stok otomatis, sistem inventory indonesia')

@push('seo')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Inventory & Warehouse Management",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Sistem kontrol inventori dan multi-gudang terintegrasi dengan POS, purchasing, dan akuntansi keuangan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pelacakan stok multi-gudang dan mutasi antar outlet secara real-time",
    "Kartu stok perpetual dengan riwayat audit trail setiap pergerakan barang",
    "Peringatan stok minimum (Reorder Point) dan Purchase Order otomatis",
    "Konversi satuan dan Bill of Materials (BOM) untuk F&B dan manufaktur ringan",
    "Stock opname terstruktur dengan scanner barcode tanpa harus menghentikan operasional toko"
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
      "name": "Bagaimana sistem COOCA menangani stok di banyak cabang atau gudang terpisah?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA mendukung multi-warehouse tanpa batas. Anda bisa membagi stok berdasarkan Gudang Pusat, Gudang Transit, maupun Rak Toko per cabang. Mutasi antar gudang tercatat lewat dokumen transfer resmi (Surat Jalan/Transfer Order) sehingga status stok dalam perjalanan (in-transit) selalu terlacak."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah COOCA mendukung resep makanan/minuman (Bill of Materials) untuk memotong bahan baku?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, sangat mendukung. Anda dapat membuat formula resep BOM. Contohnya, saat 1 cup Kopi Susu terjual di POS, sistem otomatis memotong 18 gram biji kopi, 120 ml susu segar, 20 ml sirup aren, dan 1 cup plastik dari stok gudang outlet."
      }
    },
    {
      "@type": "Question",
      "name": "Metode penilaian persediaan apa yang digunakan untuk menghitung HPP?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA menghitung Harga Pokok Penjualan (HPP) secara otomatis dengan metode Moving Average (Rata-rata Bergerak) yang sesuai standar SAK EMKM perpajakan Indonesia, menjaga nilai margin kotor Anda selalu akurat setiap kali ada pembelian barang baru."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah toko harus tutup total saat melakukan Stock Opname?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak perlu. COOCA memiliki fitur Stock Opname Parsial. Anda bisa melakukan perhitungan fisik per kategori atau per rak tertentu menggunakan scanner barcode/HP. Selisih barang akan dihitung otomatis dan sistem menerbitkan jurnal penyesuaian persediaan saat disetujui owner."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-emerald-500/10 via-teal-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Manajemen Stok & Inventori</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                    <span>Perpetual Stock & Multi-Warehouse Control</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Kendalikan Stok di Setiap Gudang & Cabang <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-500">Tanpa Selisih Misterius</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Hentikan barang hilang dan kehabisan stok saat pelanggan siap membeli. COOCA menyajikan kartu stok perpetual real-time, mutasi antar cabang dengan surat jalan, resep bahan baku (BOM), dan peringatan restock sebelum terlambat.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Modul Stok Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.erp.pos') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Lihat Koneksi ke POS Kasir</span>
                    </a>
                </div>

                {{-- Key Operational Metrics --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Metode Penilaian HPP</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Moving Average</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Akurasi Kartu Stok</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Perpetual 100%</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kapasitas Gudang</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Multi-Lokasi / Rak</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Warehouse Inventory Dashboard UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Warehouse Control Filter Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-neutral-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-400">
                                <i data-lucide="warehouse" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="font-bold text-neutral-200">Gudang Utama Cakung</div>
                                <div class="text-[10px] text-neutral-500">Kapasitas Terpakai: 74% • 1.420 SKU</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-1 rounded bg-neutral-800 text-[11px] text-neutral-300 font-mono">Transfer Order #TR-108</span>
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400 text-[10px] font-semibold">In Transit</span>
                        </div>
                    </div>

                    {{-- Low Stock Reorder Notification Banner --}}
                    <div class="my-3 p-2.5 rounded-xl bg-rose-950/40 border border-rose-800/40 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-rose-300">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0"></i>
                            <span class="text-[11px]"><strong>3 Barang di Bawah Batas Minimum:</strong> Segera terbitkan PO ke Supplier</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-rose-900 text-rose-200 font-bold shrink-0">Buat PO</span>
                    </div>

                    {{-- Inventory Data Table Mockup --}}
                    <div class="space-y-1.5 overflow-hidden text-left">
                        <div class="grid grid-cols-12 text-[10px] uppercase font-mono text-neutral-400 px-2 py-1 bg-neutral-950/80 rounded-lg">
                            <div class="col-span-5">Barang & SKU</div>
                            <div class="col-span-2 text-center">Fisik</div>
                            <div class="col-span-2 text-center">Tersedia</div>
                            <div class="col-span-3 text-right">Status</div>
                        </div>

                        {{-- Item 1 --}}
                        <div class="grid grid-cols-12 items-center p-2 rounded-xl bg-neutral-950/40 hover:bg-neutral-800/40 border border-neutral-800/60 text-xs transition-colors">
                            <div class="col-span-5">
                                <div class="font-medium text-white truncate">Biji Kopi Arabika 1kg</div>
                                <div class="text-[10px] text-neutral-500 font-mono">SKU-KOP-01 • Rak B-02</div>
                            </div>
                            <div class="col-span-2 text-center font-mono text-neutral-300">142 kg</div>
                            <div class="col-span-2 text-center font-mono text-emerald-400">128 kg</div>
                            <div class="col-span-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-950 text-emerald-400 border border-emerald-800/50">Stok Aman</span>
                            </div>
                        </div>

                        {{-- Item 2 (Low stock) --}}
                        <div class="grid grid-cols-12 items-center p-2 rounded-xl bg-neutral-950/40 hover:bg-neutral-800/40 border border-rose-900/30 text-xs transition-colors">
                            <div class="col-span-5">
                                <div class="font-medium text-white truncate">Paper Cup 12oz Cold</div>
                                <div class="text-[10px] text-neutral-500 font-mono">SKU-PKG-44 • Rak A-01</div>
                            </div>
                            <div class="col-span-2 text-center font-mono text-neutral-300">80 pcs</div>
                            <div class="col-span-2 text-center font-mono text-rose-400">40 pcs</div>
                            <div class="col-span-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-950 text-rose-400 border border-rose-800/50 font-bold">Reorder Segera</span>
                            </div>
                        </div>

                        {{-- Item 3 --}}
                        <div class="grid grid-cols-12 items-center p-2 rounded-xl bg-neutral-950/40 hover:bg-neutral-800/40 border border-neutral-800/60 text-xs transition-colors">
                            <div class="col-span-5">
                                <div class="font-medium text-white truncate">Fresh Milk Pasteurisasi 1L</div>
                                <div class="text-[10px] text-neutral-500 font-mono">SKU-DRY-12 • Chiller 01</div>
                            </div>
                            <div class="col-span-2 text-center font-mono text-neutral-300">48 btl</div>
                            <div class="col-span-2 text-center font-mono text-neutral-200">48 btl</div>
                            <div class="col-span-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-950 text-emerald-400 border border-emerald-800/50">Stok Aman</span>
                            </div>
                        </div>
                    </div>

                    {{-- Stock Card Audit Trail Footer --}}
                    <div class="mt-3 pt-2.5 border-t border-neutral-800 flex items-center justify-between text-[11px] text-neutral-400">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                            Sinkronisasi HPP Moving Average: Aktif
                        </span>
                        <a href="{{ route('public.demo') }}" class="text-emerald-400 hover:underline font-medium">Buka Kartu Stok Detail →</a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINT HIGHLIGHT: Kerugian Akibat Manajemen Stok Berantakan --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-3">Tantangan Inventori Retail & UMKM</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Di Mana Sebenarnya Uang Bisnis Anda Menguap di Gudang?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Banyak bisnis bangkrut bukan karena tidak laku, melainkan karena modal tertimbun mati dalam bentuk barang slow-moving atau bocor akibat catatan stok yang tidak diaudit.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Dead Stock & Modal Mati</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Membeli barang berlebihan tanpa data perputaran barang yang jelas. Barang menumpuk berbulan-bulan hingga kedaluwarsa atau rusak, membakar cash flow perusahaan secara perlahan.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="truck" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Stok Hilang Saat Pengiriman Cabang</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Gudang pusat mengirim 50 unit barang ke outlet, namun cabang hanya menerima 45 unit. Tanpa sistem Surat Jalan digital dan konfirmasi terima barang, selisih 5 unit tidak pernah diketahui siapa yang bertanggung jawab.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">HPP Salah, Laba Semu</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Harga beli dari supplier sering naik-turun, namun Anda memakai HPP tebak-tebakan. Akibatnya, laporan laba rugi bulanan tampak untung besar padahal uang riil di bank justru terus berkurang.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE INVENTORY CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-3">Fitur Cerdas Manajemen Stok</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Visibilitas Total dari Pembelian Sampai Penjualan Akhir
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Dirancang khusus untuk mendukung operasional yang dinamis, mulai dari gudang bahan baku hingga display rak toko.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Multi-Warehouse & Transfer Order (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="network" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Multi-Gudang & Mutasi Antar Cabang Terkontrol
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Kelola gudang pusat, gudang display, dan stok toko per cabang secara mandiri. Ketika stok di satu outlet menipis, buat Transfer Order resmi. Sistem mencatat status pengiriman, pengemudi, nomor plat, serta verifikasi penerimaan dengan barcode scan di cabang tujuan.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Status Pengiriman Digital:</span>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-semibold">Draft</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-neutral-400"></i>
                        <span class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-semibold">Kirim (In-Transit)</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-neutral-400"></i>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-semibold">Diterima Cabang</span>
                    </div>
                </div>
            </div>

            {{-- Bento Card 2: BOM Recipe & Bundling (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Resep Bahan Baku (BOM) & Bundling
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Sempurna untuk kafe, bakery, dan bengkel perakitan. Definisikan bahan mentah untuk setiap item jadi. Saat produk terjual di kasir, stok biji kopi, gula, bumbu dapur, atau baut spare part otomatis berkurang di gudang.
                    </p>
                </div>

                <div class="mt-6 p-3.5 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs font-mono text-neutral-500 flex justify-between">
                    <span>1 Menu Kopi Susu:</span>
                    <span class="text-teal-600 dark:text-teal-400 font-semibold">18g Kopi + 120ml Susu</span>
                </div>
            </div>

            {{-- Bento Card 3: Kartu Stok Perpetual & Audit Log (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Kartu Stok Perpetual</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Setiap mutasi barang memiliki referensi nomor dokumen yang sah (PO, Faktur Penjualan, atau Berita Acara Kerusakan). Tidak ada angka stok yang berubah secara tiba-tiba tanpa jejak staf penginput.
                </p>
            </div>

            {{-- Bento Card 4: Reorder Point & PO Otomatis (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="bell-ring" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Reorder Point & PO Cerdas</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Tentukan batas minimum stok per gudang. Saat barang mendekati ambang batas, COOCA memberi notifikasi dan menyiapkan draf Purchase Order ke supplier yang biasa menyediakan barang tersebut.
                </p>
            </div>

            {{-- Bento Card 5: Stock Opname Tanpa Tutup Toko (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Stock Opname Parsial</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Lakukan opname berkala per rak atau kategori tertentu tanpa perlu meliburkan toko. Masukkan hasil fisik lewat scanner smartphone, tinjau selisih, dan terbitkan adjustment journal dengan satu klik.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED WORKFLOW: Siklus Pengadaan hingga Penjualan --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-semibold mb-3 border border-emerald-500/30">
                    <span>Siklus Inventori End-to-End</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Bagaimana Stok Mengalir dalam Ekosistem COOCA
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Inventori COOCA terhubung erat dengan modul Pembelian (Purchasing), Kasir (POS), dan Akuntansi tanpa sekat.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Phase 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Purchase Order & Penerimaan</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Barang dipesan ke supplier melalui PO. Saat barang tiba di gudang, staf gudang mencocokkan fisik dengan surat jalan melalui Good Receipt Note (GRN). Stok bertambah otomatis.
                    </p>
                </div>

                {{-- Phase 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-600/30 text-teal-400 flex items-center justify-center font-bold text-xs border border-teal-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Perhitungan HPP Otomatis</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Nilai pembelian baru dikalkulasi dengan saldo stok lama menggunakan metode Moving Average. Modul akuntansi menerbitkan hutang usaha (AP) dan menambah nilai aset persediaan.
                    </p>
                </div>

                {{-- Phase 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Distribusi ke Cabang Toko</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Gudang pusat mendistribusikan barang ke cabang-cabang ritel dengan Surat Jalan Transfer. Stok gudang pusat berkurang, status in-transit aktif sampai kasir cabang melakukan scan terima.
                    </p>
                </div>

                {{-- Phase 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Penjualan & Pengurangan Stok</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Kasir scan barcode di POS atau pesanan masuk dari marketplace online. Stok cabang terpotong, nilai HPP langsung dicatat ke laporan laba rugi, dan sisa stok siap dijual kembali.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Inventori & Gudang</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana sistem COOCA menangani stok di banyak cabang atau gudang terpisah?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA mendukung multi-warehouse tanpa batas. Anda bisa membagi stok berdasarkan Gudang Pusat, Gudang Transit, maupun Rak Toko per cabang. Mutasi antar gudang tercatat lewat dokumen transfer resmi (Surat Jalan/Transfer Order) sehingga status stok dalam perjalanan (in-transit) selalu terlacak.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah COOCA mendukung resep makanan/minuman (Bill of Materials) untuk memotong bahan baku?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Ya, sangat mendukung. Anda dapat membuat formula resep BOM. Contohnya, saat 1 cup Kopi Susu terjual di POS, sistem otomatis memotong 18 gram biji kopi, 120 ml susu segar, 20 ml sirup aren, dan 1 cup plastik dari stok gudang outlet.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Metode penilaian persediaan apa yang digunakan untuk menghitung HPP?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA menghitung Harga Pokok Penjualan (HPP) secara otomatis dengan metode Moving Average (Rata-rata Bergerak) yang sesuai standar SAK EMKM perpajakan Indonesia, menjaga nilai margin kotor Anda selalu akurat setiap kali ada pembelian barang baru.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah toko harus tutup total saat melakukan Stock Opname?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Tidak perlu. COOCA memiliki fitur Stock Opname Parsial. Anda bisa melakukan perhitungan fisik per kategori atau per rak tertentu menggunakan scanner barcode/HP. Selisih barang akan dihitung otomatis dan sistem menerbitkan jurnal penyesuaian persediaan saat disetujui owner.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER (Related Pages) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Pengendalian Stok</p>
            </div>
            <a href="{{ route('public.erp.erp') }}" class="text-xs sm:text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lihat Seluruh Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.erp.pos') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">Point of Sale (POS)</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kasir cepat yang langsung memotong stok outlet secara real-time.</p>
            </a>

            <a href="{{ route('public.erp.accounting') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">Akuntansi & HPP</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Jurnal otomatis untuk mutasi, penyusutan, dan nilai persediaan aset.</p>
            </a>

            <a href="{{ route('public.solutions.retail') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">Solusi Retail</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kelola ribuan SKU produk, barcode, dan rantai pasok multi-toko.</p>
            </a>

            <a href="{{ route('public.erp.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">Analitik Persediaan</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pantau rasio perputaran stok (Inventory Turnover) dan dead stock.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Hentikan Kerugian Akibat Stok Selisih Mulai Hari Ini
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Dapatkan visibilitas 100% atas persediaan barang Anda di setiap gudang dan cabang toko dengan COOCA Inventory.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Modul Stok
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Kebutuhan Gudang
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
