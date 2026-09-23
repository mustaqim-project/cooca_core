@extends('layouts.public_marketing')

@section('title', 'Software Integrasi Marketplace & Sinkronisasi Stok Toko Fisik | COOCA')
@section('description', 'Hubungkan toko Shopee, Tokopedia, dan TikTok Shop Anda dengan toko fisik. Sinkronisasi stok
    otomatis seketika (anti-overselling), proses pesanan terpusat, dan cetak resi pengiriman massal.')
@section('keywords', 'software integrasi marketplace, sinkronisasi stok shopee tokopedia, aplikasi omnichannel
    marketplace, kelola toko online terpusat, stok gudang marketplace pos')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Marketplace Integration",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Sistem integrasi multichannel marketplace dan sinkronisasi inventori real-time antara toko fisik dan toko online.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Sinkronisasi kuantitas stok otomatis antara kasir toko fisik dan seluruh toko marketplace",
    "Agregasi pesanan masuk dari Shopee, Tokopedia, dan TikTok Shop dalam satu layar",
    "Cetak resi pengiriman kurir (Awb/Shipping Label) secara massal",
    "Pembaruan harga jual dan diskon promosi serentak ke semua channel penjualan online",
    "Pencatatan pendapatan bersih setelah potongan komisi marketplace ke modul Akuntansi"
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
      "name": "Bagaimana sistem COOCA mencegah overselling (barang laku tapi stok fisik kosong)?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA menggunakan mesin inventori terpusat. Ketika 1 barang terjual di kasir outlet fisik, sistem langsung memperbarui sisa stok ke Shopee, Tokopedia, dan TikTok Shop dalam hitungan detik. Jika stok fisik habis, status produk di marketplace otomatis berubah menjadi 'Habis' sehingga pembeli online tidak bisa melakukan checkout."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah bisa menghubungkan lebih dari satu akun toko pada marketplace yang sama?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat menghubungkan beberapa toko Shopee atau Tokopedia sekaligus (misal Toko Resmi Utama dan Toko Cabang Kota Tertentu). Seluruh pesanan akan bermuara ke satu dashboard operasional COOCA."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara tim gudang memproses dan mengemas pesanan dari banyak marketplace?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tim gudang cukup membuka modul Pesanan di COOCA. Semua order masuk berurutan lengkap dengan status pembayaran. Anda dapat mencetak nota kemas (picking list) dan resi pengiriman kurir secara massal dalam satu klik tanpa perlu membuka seller center masing-masing marketplace."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana perhitungan biaya admin dan komisi potongan marketplace dicatat?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA memisahkan nilai penjualan kotor dengan potongan komisi platform dan biaya gratis ongkir. Saat dana dicairkan (settlement) ke rekening bank Anda, modul Akuntansi mencatat penerimaan kas bersih dan mengalokasikan potongan komisi ke pos beban penjualan secara otomatis."
      }
    }
  ]
}
</script>
    @endpush

@section('content')
    <div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

        {{-- Ambient Light Accent --}}
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1300px] h-[480px] bg-gradient-to-b from-orange-500/10 via-amber-500/5 to-transparent blur-3xl pointer-events-none -z-10">
        </div>

        {{-- Breadcrumb --}}
        <nav class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li><span class="text-neutral-500 dark:text-neutral-400">Omnichannel</span></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Integrasi Marketplace
                </li>
            </ol>
        </nav>

        {{-- 1. HERO SECTION (2 Columns) --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-50 dark:bg-orange-950/60 border border-orange-200/60 dark:border-orange-800/40 text-orange-700 dark:text-orange-400 text-xs font-semibold tracking-wide">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        <span>Omnichannel Marketplace & Centralized Inventory</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                        Sinkronkan Stok Toko Fisik & Marketplace <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-amber-600 to-rose-500">Tanpa
                            Risiko Kehabisan Stok</span>
                    </h1>

                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                        Hentikan mimpi buruk membatalkan pesanan online karena barang sudah terlanjur dibeli orang di toko
                        fisik. COOCA menyatukan stok gudang Anda ke Shopee, Tokopedia, dan TikTok Shop secara real-time,
                        memproses seluruh pesanan, dan mencetak resi pengiriman massal dari satu dashboard.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                            <span>Coba Integrasi Marketplace</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.omnichannel.orders') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                            <span>Lihat Alur Pesanan</span>
                        </a>
                    </div>

                    {{-- Key Trust Specs --}}
                    <div
                        class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Sinkronisasi Stok</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Real-Time Instan</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Proses Pengiriman</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Cetak Resi Massal</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Akuntansi Komisi</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Otomatis Terpotong</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live Marketplace Channel Sync UI --}}
                <div class="lg:col-span-6">
                    <div
                        class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">

                        {{-- Channel Header Selector --}}
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-orange-500/20 text-orange-400">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-neutral-200">Kanal Marketplace Terhubung</div>
                                    <div class="text-[10px] text-neutral-500">Stok Terpusat: Gudang Utama Cakung</div>
                                </div>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">Stok
                                Sinkron: 100%</span>
                        </div>

                        {{-- Connected Marketplace Badges Strip --}}
                        <div class="grid grid-cols-3 gap-2 my-3 text-xs">
                            <div
                                class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg bg-orange-600 flex items-center justify-center text-white text-[11px] font-bold">
                                    SHP
                                </div>
                                <div class="truncate">
                                    <div class="font-semibold text-white truncate text-[11px]">Shopee Mall</div>
                                    <div class="text-[9px] text-emerald-400">42 Pesanan Baru</div>
                                </div>
                            </div>

                            <div
                                class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg bg-emerald-700 flex items-center justify-center text-white text-[11px] font-bold">
                                    TKP
                                </div>
                                <div class="truncate">
                                    <div class="font-semibold text-white truncate text-[11px]">Tokopedia Pro</div>
                                    <div class="text-[9px] text-emerald-400">28 Pesanan Baru</div>
                                </div>
                            </div>

                            <div
                                class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg bg-neutral-800 border border-neutral-700 flex items-center justify-center text-white text-[11px] font-bold">
                                    TTS
                                </div>
                                <div class="truncate">
                                    <div class="font-semibold text-white truncate text-[11px]">TikTok Shop</div>
                                    <div class="text-[9px] text-emerald-400">19 Pesanan Baru</div>
                                </div>
                            </div>
                        </div>

                        {{-- Real-Time Central Stock Allocation Card --}}
                        <div class="p-3 rounded-xl bg-neutral-950/90 border border-neutral-800 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-mono text-neutral-300 font-semibold flex items-center gap-1.5">
                                    <i data-lucide="package" class="w-3.5 h-3.5 text-orange-400"></i> SKU-KOP-01 (Biji Kopi
                                    Arabika 1kg)
                                </span>
                                <span
                                    class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 text-[10px] font-mono">Total
                                    Fisik: 142 pcs</span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center pt-1 font-mono text-[10px]">
                                <div class="p-1.5 rounded-lg bg-neutral-900 border border-neutral-800">
                                    <span class="text-neutral-400 block text-[9px]">Toko Kasir POS</span>
                                    <span class="text-white font-bold">60 pcs</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-neutral-900 border border-neutral-800">
                                    <span class="text-neutral-400 block text-[9px]">Shopee Online</span>
                                    <span class="text-white font-bold">42 pcs</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-neutral-900 border border-neutral-800">
                                    <span class="text-neutral-400 block text-[9px]">Tokopedia Online</span>
                                    <span class="text-white font-bold">40 pcs</span>
                                </div>
                            </div>
                        </div>

                        {{-- Live Incoming Order Event Log --}}
                        <div
                            class="mt-3 p-2.5 rounded-xl bg-orange-950/30 border border-orange-800/40 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-orange-400 animate-ping"></span>
                                <div>
                                    <span class="text-white font-medium text-[11px]">Pesanan Masuk dari Shopee
                                        (#2409SHP-88):</span>
                                    <span class="text-neutral-400 text-[10px] block">2x Kopi Arabika • Stok POS & Tokopedia
                                        otomatis terpotong 2 pcs</span>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded bg-orange-600 text-white font-bold text-[10px] shrink-0">Cetak
                                Resi</span>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerumitan Mengelola Banyak Marketplace Manual --}}
        <section
            class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-orange-600 dark:text-orange-400 font-semibold mb-3">
                        Tantangan Penjual Multi-Channel</h2>
                    <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Mengapa Toko Online yang Berkembang Sering Terjebak Masalah Operasional?
                    </p>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                        Semakin banyak marketplace yang Anda buka, semakin besar risiko penalti reputasi toko akibat
                        keterlambatan pengiriman dan stok kosong yang tidak terpantau.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="package-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Overselling & Penalti Pembatalan
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Barang terakhir sudah dibeli oleh pengunjung toko fisik, tetapi di Shopee masih tertulis ada
                            stok. Anda terpaksa membatalkan pesanan pembeli online dan toko Anda terkena poin penalti
                            platform.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="printer" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Buka-Tutup Banyak Tab Seller
                            Center</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Staf gudang harus login ke 4 tab browser terpisah untuk mencetak label pengiriman satu per satu.
                            Rentan salah tempel resi yang berujung retur barang dan komplain pembeli.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/50 border border-orange-200 dark:border-orange-900/50 flex items-center justify-center text-orange-600 dark:text-orange-400">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Hitung Laba Bersih Bikin Pusing
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Potongan komisi layanan, biaya gratis ongkir xtra, dan biaya kampanye flash sale membuat Anda
                            tidak tahu berapa laba bersih riil yang sebenarnya masuk ke rekening bank.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE MARKETPLACE CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-orange-600 dark:text-orange-400 font-semibold mb-3">
                    Kemampuan Integrasi Marketplace COOCA</h2>
                <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Efisiensi Tanpa Batas untuk Penjual Modern
                </p>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                    Memadukan kecepatan operasional gudang dengan keteraturan pembukuan finansial dalam satu ekosistem
                    terpadu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Real-Time Stock Engine (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-orange-100 dark:bg-orange-950 flex items-center justify-center text-orange-600 dark:text-orange-400">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Sinkronisasi Stok Otomatis Seketika (Zero Delay)
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Mesin inventori terpusat COOCA secara aktif memperbarui kuantitas persediaan di seluruh toko
                            online saat ada pembelian di toko offline maupun kanal online lainnya. Hilangkan potensi
                            overselling dan jaga skor performa toko Anda selalu sempurna.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                        <span class="text-neutral-600 dark:text-neutral-300 font-medium">Kecepatan Respons API:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Stok Terpotong Serentak &lt; 5 Detik
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Cetak Resi & Packing List Massal (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="printer" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Cetak Resi & Nota Kemas Massal
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pilih 50 pesanan dari berbagai marketplace sekaligus dan cetak label pengiriman thermal dalam
                            satu klik. Dokumen packing list menyortir lokasi rak gudang agar proses pengambilan barang
                            berjalan kilat.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                        <span class="text-neutral-500">Efisiensi Gudang</span>
                        <span class="text-amber-500 font-bold">Proses 100 Order dalam 15 Menit</span>
                    </div>
                </div>

                {{-- Bento Card 3: Update Harga Serentak (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="tags" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pembaruan Harga Fleksibel</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ubah harga jual atau naikkan harga sedikit di marketplace untuk menutup biaya komisi platform tanpa
                        perlu menyunting satu per satu di setiap seller center.
                    </p>
                </div>

                {{-- Bento Card 4: Konsolidasi Keuangan & Komisi (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Auto-Jurnal Settlement</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Saat saldo marketplace dicairkan, COOCA secara cerdas membukukan uang kas masuk, potongan fee
                        platform, dan HPP produk ke laporan Laba Rugi.
                    </p>
                </div>

                {{-- Bento Card 5: Integrasi Barcode Verifikasi Kemas (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Scan Barcode Sebelum Kirim</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Verifikasi barang yang dimasukkan ke kardus dengan barcode scanner untuk memastikan tipe dan varian
                        warna sesuai dengan yang dipesan pembeli.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Alur Order Marketplace Hingga Akuntansi --}}
        <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 text-orange-400 text-xs font-semibold mb-3 border border-orange-500/30">
                        <span>Siklus Penjualan Multi-Channel</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Bagaimana Transaksi Marketplace Mengalir di COOCA
                    </h2>
                    <p class="text-neutral-400 text-sm sm:text-base mt-3">
                        Mengintegrasikan dunia e-commerce dengan operasional fisik toko secara mulus.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-orange-600/30 text-orange-400 flex items-center justify-center font-bold text-xs border border-orange-500/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Pembeli Checkout Online</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Pelanggan bertransaksi di Shopee atau Tokopedia. Pesanan langsung masuk ke antrean COOCA Orders
                            dan stok di kasir toko fisik terpotong otomatis.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-600/30 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Pengambilan & Kemas Gudang</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Staf gudang mencetak resi pengiriman kurir dan nota ambil barang, lalu melakukan scan barcode
                            untuk memastikan barang yang dikemas tepat.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Serah Terima Kurir</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Paket diserahkan ke kurir ekspedisi. Status pesanan di marketplace otomatis berubah menjadi
                            'Sedang Dikirim' tanpa perlu konfirmasi manual.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Pencairan Dana Bersih</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Saat pesanan selesai, dana yang masuk ke rekening bank dicatat bersih setelah dipotong komisi
                            resmi ke pos beban keuangan di modul Akuntansi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-orange-600 dark:text-orange-400 font-semibold mb-2">
                    Pertanyaan Umum</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Integrasi
                    Marketplace</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana sistem COOCA mencegah overselling (barang laku tapi stok fisik kosong)?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        COOCA menggunakan mesin inventori terpusat. Ketika 1 barang terjual di kasir outlet fisik, sistem
                        langsung memperbarui sisa stok ke Shopee, Tokopedia, dan TikTok Shop dalam hitungan detik. Jika stok
                        fisik habis, status produk di marketplace otomatis berubah menjadi 'Habis' sehingga pembeli online
                        tidak bisa melakukan checkout.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah bisa menghubungkan lebih dari satu akun toko pada marketplace yang sama?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat menghubungkan beberapa toko Shopee atau Tokopedia sekaligus (misal Toko Resmi Utama
                        dan Toko Cabang Kota Tertentu). Seluruh pesanan akan bermuara ke satu dashboard operasional COOCA.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana cara tim gudang memproses dan mengemas pesanan dari banyak marketplace?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Tim gudang cukup membuka modul Pesanan di COOCA. Semua order masuk berurutan lengkap dengan status
                        pembayaran. Anda dapat mencetak nota kemas (picking list) dan resi pengiriman kurir secara massal
                        dalam satu klik tanpa perlu membuka seller center masing-masing marketplace.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana perhitungan biaya admin dan komisi potongan marketplace dicatat?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        COOCA memisahkan nilai penjualan kotor dengan potongan komisi platform dan biaya gratis ongkir. Saat
                        dana dicairkan (settlement) ke rekening bank Anda, modul Akuntansi mencatat penerimaan kas bersih
                        dan mengalokasikan potongan komisi ke pos beban penjualan secara otomatis.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-orange-600 dark:text-orange-400 font-semibold mb-1">
                        Modul Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Penjualan
                        Multi-Channel</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-orange-600 dark:text-orange-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.omnichannel.orders') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-orange-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-950 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-orange-600 transition-colors">
                        Manajemen Pesanan Terpadu</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kelola siklus pesanan dari checkout,
                        packing, hingga siap kirim.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-orange-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-orange-600 transition-colors">
                        Stok Gudang Terpusat</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kartu stok perpetual yang menjadi sumber
                        kebenaran tunggal stok barang.</p>
                </a>

                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-orange-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-orange-600 transition-colors">
                        Point of Sale (POS)</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kasir toko fisik yang berbagi kuantitas
                        stok yang sama dengan marketplace.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-orange-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-orange-600 transition-colors">
                        Akuntansi & Laba Bersih</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Laporan Laba Rugi yang mencatat potongan
                        fee dan komisi e-commerce.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Tingkatkan Penjualan E-Commerce Tanpa Kekacauan Gudang
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-400">
                        Mulai operasikan integrasi marketplace otomatis dan nikmati kemudahan mengelola stok multi-channel
                        bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white font-semibold text-sm transition-all shadow-md">
                            Coba Demo Integrasi Marketplace
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                            Konsultasi Channel Penjualan
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
