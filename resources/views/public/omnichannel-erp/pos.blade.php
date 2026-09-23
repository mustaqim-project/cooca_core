@extends('layouts.public_marketing')

@section('title', 'Aplikasi Kasir POS Multi-Outlet Terintegrasi ERP & Stok | COOCA')
@section('description', 'Software Point of Sale (POS) modern untuk retail, F&B, dan jasa. Transaksi kasir secepat kilat, cetak struk Bluetooth, barcode scanner, QRIS dinamis, dan langsung memotong stok serta membukukan jurnal keuangan otomatis.')
@section('keywords', 'software kasir online, aplikasi pos multi outlet, point of sale indonesia, pos terintegrasi stok, kasir barcode qris')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Point of Sale (POS)",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Aplikasi kasir online cepat dan fleksibel dengan integrasi real-time ke manajemen stok, akuntansi, dan database pelanggan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pencatatan transaksi super cepat dengan barcode scanner dan touchscreen",
    "Dukungan printer thermal 58mm & 80mm via Bluetooth/USB",
    "Sinkronisasi stok gudang dan outlet otomatis seketika",
    "Penerimaan multi-metode bayar: QRIS, Cash, Transfer, Debit Card",
    "Laporan pergantian shift kasir dan rekonsiliasi laci kas (X/Z Report)"
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
      "name": "Apakah COOCA POS bisa digunakan di beberapa cabang atau outlet sekaligus?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. COOCA POS dirancang native multi-outlet. Setiap outlet memiliki katalog harga, persediaan stok terpisah, serta laporan shift kasir mandiri yang semuanya terpantau secara terpusat oleh owner."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah kasir harus memakai hardware khusus atau bisa memakai tablet dan HP yang sudah ada?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak perlu hardware mahal khusus. COOCA POS berjalan lancar di browser tablet Android, iPad, laptop Windows, bahkan smartphone staf, serta kompatibel dengan printer struk thermal standar Bluetooth/USB dan barcode scanner."
      }
    },
    {
      "@type": "Question",
      "name": "Apa yang terjadi pada stok dan laporan keuangan saat transaksi kasir selesai?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Begitu tombol 'Selesaikan Pembayaran' ditekan, sistem otomatis memotong stok barang (atau bahan baku jika memakai resep), mencatat penerimaan kas ke modul Finance, dan membuat jurnal debit-kredit otomatis tanpa perlu input ulang di malam hari."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara mencegah kecurangan kasir saat pergantian shift?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA POS memiliki fitur Buka/Tutup Kasir (Shift Control). Kasir wajib memasukkan modal awal dan menghitung fisik kas saat tutup shift. Sistem membandingkan saldo sistem vs saldo fisik aktual (X & Z Report) sehingga selisih uang langsung terdeteksi."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Background Glow --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-blue-500/10 via-indigo-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Point of Sale (POS)</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200/60 dark:border-blue-800/40 text-blue-700 dark:text-blue-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
                    <span>Cloud Point of Sale & Kasir Kasir Cepat</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Aplikasi Kasir Cepat yang Langsung Terhubung ke <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500">Stok & Akuntansi</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Layani pelanggan tanpa antre berlama-lama. Transaksi kilat dengan barcode dan QRIS, cetak struk thermal, catat pelanggan, dan biarkan COOCA memotong stok fisik serta membukukan jurnal keuangan otomatis di detik yang sama.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Demo POS Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Lihat Paket & Harga</span>
                    </a>
                </div>

                {{-- Micro Trust Indicators --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kecepatan Checkout</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">&lt; 3 Detik / Order</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Sinkronisasi Data</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Real-time ke Gudang</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kompatibilitas</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Thermal BT & USB</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live POS Terminal UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-2 sm:p-3 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    {{-- Device Top Bar --}}
                    <div class="flex items-center justify-between px-3 py-2 bg-neutral-950 rounded-xl mb-2 text-xs border border-neutral-800/80">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-neutral-300 font-semibold">Kasir 01 — Outlet Sudirman</span>
                            <span class="text-neutral-500">| Shift: Pagi (Budi)</span>
                        </div>
                        <div class="flex items-center gap-2 text-neutral-400">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span class="text-[11px] text-neutral-300">BT-58mm Terhubung</span>
                        </div>
                    </div>

                    {{-- Main POS Dual Workspace (Items Grid + Cart Summary) --}}
                    <div class="grid grid-cols-12 gap-2 bg-neutral-950 p-2.5 rounded-xl border border-neutral-800/60">
                        
                        {{-- Items Catalog (7 Cols) --}}
                        <div class="col-span-7 space-y-2">
                            {{-- Category Tabs --}}
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]">
                                <span class="px-2.5 py-1 bg-blue-600 text-white font-medium rounded-lg shrink-0">Semua (24)</span>
                                <span class="px-2.5 py-1 bg-neutral-800 text-neutral-400 hover:text-white rounded-lg shrink-0">Favorit</span>
                                <span class="px-2.5 py-1 bg-neutral-800 text-neutral-400 hover:text-white rounded-lg shrink-0">Minuman</span>
                            </div>

                            {{-- Product Tiles --}}
                            <div class="grid grid-cols-2 gap-2 text-left">
                                <div class="p-2.5 rounded-xl bg-neutral-900/90 border border-neutral-800 hover:border-blue-500/50 transition-all cursor-pointer group">
                                    <div class="text-[10px] text-blue-400 font-mono">SKU-084</div>
                                    <div class="text-xs font-semibold text-white group-hover:text-blue-400 transition-colors mt-0.5 line-clamp-1">Kopi Susu Aren</div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-bold text-neutral-200">Rp 22.000</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800/40">Stok: 48</span>
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-neutral-900/90 border border-neutral-800 hover:border-blue-500/50 transition-all cursor-pointer group">
                                    <div class="text-[10px] text-blue-400 font-mono">SKU-112</div>
                                    <div class="text-xs font-semibold text-white group-hover:text-blue-400 transition-colors mt-0.5 line-clamp-1">Croissant Butter</div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-bold text-neutral-200">Rp 28.000</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800/40">Stok: 15</span>
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-neutral-900/90 border border-neutral-800 hover:border-blue-500/50 transition-all cursor-pointer group">
                                    <div class="text-[10px] text-blue-400 font-mono">SKU-209</div>
                                    <div class="text-xs font-semibold text-white group-hover:text-blue-400 transition-colors mt-0.5 line-clamp-1">Matcha Latte Ice</div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-bold text-neutral-200">Rp 26.000</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800/40">Stok: 32</span>
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-neutral-900/90 border border-neutral-800 hover:border-blue-500/50 transition-all cursor-pointer group">
                                    <div class="text-[10px] text-blue-400 font-mono">SKU-019</div>
                                    <div class="text-xs font-semibold text-white group-hover:text-blue-400 transition-colors mt-0.5 line-clamp-1">Earl Grey Tea</div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-bold text-neutral-200">Rp 18.000</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800/40">Stok: 60</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Active Cart & Payment Panel (5 Cols) --}}
                        <div class="col-span-5 bg-neutral-900/95 p-3 rounded-xl border border-neutral-800 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-2 border-b border-neutral-800 text-xs">
                                    <span class="font-semibold text-neutral-200">Order #TRX-9402</span>
                                    <span class="text-[11px] text-neutral-400">Meja 04</span>
                                </div>

                                {{-- Line Items --}}
                                <div class="space-y-2 py-2.5 text-left text-xs border-b border-neutral-800/80">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-neutral-200 font-medium">2x Kopi Susu Aren</div>
                                            <div class="text-[10px] text-neutral-500">Less Sugar, Ice Normal</div>
                                        </div>
                                        <div class="text-neutral-300 font-mono">Rp 44.000</div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-neutral-200 font-medium">1x Croissant Butter</div>
                                            <div class="text-[10px] text-neutral-500">Hangatkan</div>
                                        </div>
                                        <div class="text-neutral-300 font-mono">Rp 28.000</div>
                                    </div>
                                </div>

                                {{-- Calculations --}}
                                <div class="py-2 space-y-1 text-[11px] text-neutral-400">
                                    <div class="flex justify-between">
                                        <span>Subtotal</span>
                                        <span class="text-neutral-300 font-mono">Rp 72.000</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>PB1 (10%)</span>
                                        <span class="text-neutral-300 font-mono">Rp 7.200</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-bold text-white pt-1 border-t border-neutral-800">
                                        <span>Total Bayar</span>
                                        <span class="text-emerald-400 font-mono text-sm">Rp 79.200</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Action --}}
                            <div class="space-y-2 pt-2">
                                <div class="grid grid-cols-2 gap-1.5 text-[10px]">
                                    <button class="py-1 px-1.5 rounded-lg bg-neutral-800 text-neutral-300 border border-neutral-700 font-medium hover:bg-neutral-700">QRIS Dinamis</button>
                                    <button class="py-1 px-1.5 rounded-lg bg-neutral-800 text-neutral-300 border border-neutral-700 font-medium hover:bg-neutral-700">Tunai (Cash)</button>
                                </div>
                                <button class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>Bayar & Cetak Struk</span>
                                </button>
                            </div>
                        </div>

                    </div>
                    {{-- Automated Sync Note --}}
                    <div class="mt-2 text-center text-[11px] text-neutral-400 flex items-center justify-center gap-1.5 py-1">
                        <i data-lucide="refresh-cw" class="w-3 h-3 text-blue-400 animate-spin"></i>
                        <span>Otomatis memotong 2 botol susu, 1 pack butter, dan input kas ke Akuntansi</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- 2. PROBLEM IDENTIFICATION: Kenapa Kasir Standalone Bikin Owner Rugi --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-3">Friction di Meja Kasir</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Apakah Mesin Kasir Anda Saat Ini Membantu Bisnis, atau Justru Menambah Beban Rekonsiliasi?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Aplikasi kasir yang terisolasi dari inventori dan pembukuan hanya menyelesaikan transaksi saat itu juga, namun meninggalkan tumpukan masalah di belakang meja.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Rekonsiliasi Manual Setiap Malam</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Kasir dan supervisor harus menghitung manual struk kertas, mencatat total penjualan di Excel, lalu staf akunting menginput ulang esok harinya. Rawan selisih dan membuang 2 jam kerja setiap hari.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="package-minus" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Stok Fisik Selisih & Telat Reorder</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Barang laku keras di kasir, tetapi orang gudang tidak tahu stok sudah menipis. Saat pelanggan berikutnya ingin membeli, barang ternyata kosong. Penjualan hilang seketika karena sistem kasir tidak terhubung gudang.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Uang Kas Kasir Tidak Akurat</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Tanpa kontrol shift kasir yang ketat, uang modal awal tercampur dengan hasil penjualan tunai. Owner kesulitan membuktikan apakah selisih kas terjadi karena kelalaian kembalian atau kecurangan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE FEATURES: Bento Grid Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-3">Fitur Lengkap Kasir COOCA</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Didesain untuk Efisiensi Kasir & Ketenteraman Owner
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Setiap tombol dan alur transaksi dioptimalkan agar kasir baru dapat mengoperasikan sistem dalam waktu kurang dari 10 menit pelatihan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Kecepatan Transaksi & Barcode (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Scan Barcode Cepat & Pencarian Fleksibel
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Cukup tembak barcode dengan scanner USB/Bluetooth kamera atau cari nama barang dengan keyboard. COOCA menampilkan varian produk (ukuran, warna, rasa), catatan pesanan khusus, dan harga grosir/member secara instan tanpa memperlambat antrean kasir.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center gap-3 text-xs text-neutral-600 dark:text-neutral-300">
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <i data-lucide="check" class="w-4 h-4"></i> Support Barcode Scanner 1D & 2D
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <i data-lucide="check" class="w-4 h-4"></i> Varian & Modifier Dinamis
                        </span>
                    </div>
                </div>
            </div>

            {{-- Bento Card 2: Shift Control & X/Z Report (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Kontrol Shift & Laci Kasir (X/Z Report)
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Modal awal tercatat rapi saat kasir login. Saat tutup kasir, sistem meminta kasir memasukkan uang fisik yang ada, lalu secara otomatis menghasilkan laporan perbandingan untuk mendeteksi selisih secara transparan.
                    </p>
                </div>

                <div class="mt-6 p-3.5 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Laporan Kasir Z</span>
                    <span class="text-emerald-500 font-bold">Selisih: Rp 0 (Tepat)</span>
                </div>
            </div>

            {{-- Bento Card 3: Multi-Payment & QRIS Dinamis (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="qr-code" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Multi Pembayaran & QRIS</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Terima pembayaran tunai, kartu debit/kredit, transfer bank, hingga QRIS dinamis di layar kasir dengan nominal yang terkunci otomatis sehingga mencegah salah input angka oleh pelanggan.
                </p>
            </div>

            {{-- Bento Card 4: Multi-Outlet & Akses Terpusat (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-sky-100 dark:bg-sky-950 flex items-center justify-center text-sky-600 dark:text-sky-400">
                    <i data-lucide="store" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Multi-Outlet Terpadu</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Buka 1 cabang atau 50 cabang retail. Kelola seluruh menu harga, stok barang, diskon promosi, dan otorisasi hak akses staf kasir dari satu panel admin pusat milik owner.
                </p>
            </div>

            {{-- Bento Card 5: Split Bill & Struk Kustom (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pisah Tagihan (Split Bill)</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Pelanggan ingin bayar patungan atau bayar sebagian tunai dan sebagian QRIS? Fitur split payment dan split order memungkinkan pembagian tagihan per item dengan sangat leluasa.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. THE CONNECTED SYSTEM CHAIN: Apa yang Terjadi Setelah Tombol Bayar Ditekan --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-400 text-xs font-semibold mb-3 border border-blue-500/30">
                    <span>Satu Klik di Kasir, Sinkron ke Seluruh Ekosistem</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Mengapa COOCA POS Unggul Jauh dari Aplikasi Kasir Biasa?
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Saat kasir mencetak struk untuk pelanggan, sistem COOCA secara bersamaan mengeksekusi 4 proses backend bisnis tanpa jeda:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Stok Terpotong Otomatis</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Kartu stok outlet langsung berkurang. Jika produk menggunakan Resep/BOM (seperti di kafe atau bakery), bahan mentah (biji kopi, susu, butter) otomatis dipotong secara proporsional.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Jurnal Akuntansi Terbit</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Debit: Kas/Bank Outlet, Kredit: Pendapatan Penjualan, serta Debit: HPP vs Kredit: Persediaan. Buku besar dan laba rugi terupdate saat itu juga tanpa campur tangan admin.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Poin CRM Bertambah</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Jika kasir memasukkan nomor WhatsApp pelanggan, nominal belanja langsung masuk ke profil pelanggan, poin loyalitas bertambah, dan riwayat pesanan tercatat rapi.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-600/30 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Dashboard Owner Update</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Grafik omzet harian di smartphone owner langsung bergerak naik. Owner mengetahui cabang mana yang ramai pada jam berapa tanpa harus menelepon manajer toko.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. HARDWARE COMPATIBILITY --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="p-8 sm:p-10 rounded-3xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-5 space-y-4">
                    <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Gunakan Perangkat yang Sudah Anda Miliki
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Anda tidak perlu terkunci pada mesin POS proprietary mahal bernilai puluhan juta rupiah. COOCA POS fleksibel dan langsung siap digunakan pada perangkat standar yang beredar di pasaran.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('public.demo') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            <span>Hubungi kami untuk rekomendasi printer & scanner</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                        <i data-lucide="tablet" class="w-6 h-6 text-blue-600 dark:text-blue-400 mx-auto"></i>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">Tablet & HP</div>
                        <div class="text-[11px] text-neutral-500">Android & iPad iOS</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                        <i data-lucide="laptop" class="w-6 h-6 text-blue-600 dark:text-blue-400 mx-auto"></i>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">PC & Laptop</div>
                        <div class="text-[11px] text-neutral-500">Windows & macOS</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                        <i data-lucide="printer" class="w-6 h-6 text-blue-600 dark:text-blue-400 mx-auto"></i>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">Printer Thermal</div>
                        <div class="text-[11px] text-neutral-500">Bluetooth & USB 58/80mm</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200/80 dark:border-neutral-800 space-y-2">
                        <i data-lucide="scan" class="w-6 h-6 text-blue-600 dark:text-blue-400 mx-auto"></i>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">Barcode Scanner</div>
                        <div class="text-[11px] text-neutral-500">Kabel USB & Wireless</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar COOCA POS</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah COOCA POS bisa digunakan di beberapa cabang atau outlet sekaligus?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Bisa. COOCA POS dirancang native multi-outlet. Setiap outlet memiliki katalog harga, persediaan stok terpisah, serta laporan shift kasir mandiri yang semuanya terpantau secara terpusat oleh owner.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah kasir harus memakai hardware khusus atau bisa memakai tablet dan HP yang sudah ada?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Tidak perlu hardware mahal khusus. COOCA POS berjalan lancar di browser tablet Android, iPad, laptop Windows, bahkan smartphone staf, serta kompatibel dengan printer struk thermal standar Bluetooth/USB dan barcode scanner.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apa yang terjadi pada stok dan laporan keuangan saat transaksi kasir selesai?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Begitu tombol "Selesaikan Pembayaran" ditekan, sistem otomatis memotong stok barang (atau bahan baku jika memakai resep), mencatat penerimaan kas ke modul Finance, dan membuat jurnal debit-kredit otomatis tanpa perlu input ulang di malam hari.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana cara mencegah kecurangan kasir saat pergantian shift?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA POS memiliki fitur Buka/Tutup Kasir (Shift Control). Kasir wajib memasukkan modal awal dan menghitung fisik kas saat tutup shift. Sistem membandingkan saldo sistem vs saldo fisik aktual (X & Z Report) sehingga selisih uang langsung terdeteksi.
                </p>
            </details>
        </div>
    </section>

    {{-- 7. RELATED ERP MODULES (Topical Cluster) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Lengkapi Operasional POS Anda</p>
            </div>
            <a href="{{ route('public.erp.erp') }}" class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Jelajahi Semua Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.erp.inventory') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Manajemen Stok Gudang</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Stok terpusat, mutasi antar cabang, dan kartu stok akurat.</p>
            </a>

            <a href="{{ route('public.erp.finance') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Kas & Keuangan</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Penerimaan kas kasir otomatis masuk ke buku kas harian.</p>
            </a>

            <a href="{{ route('public.erp.crm') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">CRM & Pelanggan</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Database member, poin loyalitas, dan program promosi.</p>
            </a>

            <a href="{{ route('public.erp.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Analitik Penjualan</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pantau jam sibuk, produk terlaris, dan kinerja kasir.</p>
            </a>
        </div>
    </section>

    {{-- 8. CONVERSION CTA BOTTOM --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Siap Mempercepat Kasir dan Menghentikan Selisih Stok?
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Mulai operasikan POS modern yang terhubung langsung dengan gudang dan buku keuangan Anda hari ini juga.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Interaktif POS
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Kebutuhan Outlet
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
