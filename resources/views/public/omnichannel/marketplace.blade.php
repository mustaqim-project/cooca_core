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
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION --}}
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-10 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                {{-- Breadcrumb --}}
                <nav class="pb-6" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs text-slate-400">
                        <li><a href="{{ route('landing') }}" class="hover:text-white transition-colors">Home</a></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li><span class="text-slate-400">Omnichannel</span></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li class="text-slate-200 font-semibold" aria-current="page">Integrasi Marketplace</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    {{-- Left Column: Copy & Value Proposition --}}
                    <div class="lg:col-span-6 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold tracking-wide">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                            <span>Omnichannel Marketplace & Centralized Inventory</span>
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold tracking-tight text-white leading-[1.2] text-balance break-words">
                            Sinkronkan Stok Toko Fisik & Marketplace <span class="text-[#00C4D8]">Tanpa Risiko Kehabisan
                                Stok</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                            Hentikan mimpi buruk membatalkan pesanan online karena barang sudah terlanjur dibeli orang di
                            toko fisik. COOCA menyatukan stok gudang Anda ke Shopee, Tokopedia, dan TikTok Shop secara
                            real-time, memproses seluruh pesanan, dan mencetak resi pengiriman massal dari satu dashboard.
                        </p>

                        {{-- Action CTAs --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200">
                                <span>Coba Integrasi Marketplace</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.omnichannel.orders') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                                <span>Lihat Alur Pesanan</span>
                            </a>
                        </div>

                        {{-- Key Trust Specs --}}
                        <div
                            class="pt-4 border-t border-white/10 grid grid-cols-2 sm:grid-cols-3 gap-3.5 sm:gap-4 text-left">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Sinkronisasi Stok</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Real-Time Instan</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Proses Pengiriman</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Cetak Resi Massal</div>
                            </div>
                            <div class="min-w-0 col-span-2 sm:col-span-1">
                                <div class="text-xs text-slate-400 font-medium truncate">Akuntansi Komisi</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Otomatis Terpotong</div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Simulated Live Marketplace Channel Sync UI --}}
                    <div class="lg:col-span-6">
                        <div
                            class="relative rounded-2xl bg-[#0E1E45]/80 p-4 sm:p-5 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white">

                            {{-- Channel Header Selector --}}
                            <div class="flex items-center justify-between gap-2 pb-3 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="p-1.5 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                        <i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white truncate">Kanal Marketplace Terhubung</div>
                                        <div class="text-[10px] text-slate-400 truncate">Stok Terpusat: Gudang Utama Cakung
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold shrink-0">Stok:
                                    100%</span>
                            </div>

                            {{-- Connected Marketplace Badges Strip --}}
                            <div class="grid grid-cols-3 gap-2 my-3 text-xs">
                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/90 border border-white/10 flex items-center gap-2 min-w-0">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-orange-600 flex items-center justify-center text-white text-[11px] font-bold shrink-0">
                                        SHP
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-white truncate text-[11px]">Shopee</div>
                                        <div class="text-[9px] text-emerald-400 truncate">42 Order Baru</div>
                                    </div>
                                </div>

                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/90 border border-white/10 flex items-center gap-2 min-w-0">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-emerald-700 flex items-center justify-center text-white text-[11px] font-bold shrink-0">
                                        TKP
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-white truncate text-[11px]">Tokopedia</div>
                                        <div class="text-[9px] text-emerald-400 truncate">28 Order Baru</div>
                                    </div>
                                </div>

                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/90 border border-white/10 flex items-center gap-2 min-w-0">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-slate-800 border border-white/10 flex items-center justify-center text-white text-[11px] font-bold shrink-0">
                                        TTS
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-white truncate text-[11px]">TikTok</div>
                                        <div class="text-[9px] text-emerald-400 truncate">19 Order Baru</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Real-Time Central Stock Allocation Card --}}
                            <div class="p-3 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-2 text-[11px]">
                                    <span
                                        class="font-mono text-slate-300 font-semibold flex items-center gap-1.5 min-w-0 flex-1">
                                        <i data-lucide="package" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                        <span class="truncate">SKU-KOP-01 (Kopi Arabika 1kg)</span>
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono shrink-0">Total:
                                        142 pcs</span>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-center pt-1 font-mono text-[10px]">
                                    <div class="p-1.5 rounded-lg bg-white/5 border border-white/10 min-w-0">
                                        <span class="text-slate-400 block text-[9px] truncate">Kasir POS</span>
                                        <span class="text-white font-bold block truncate">60 pcs</span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white/5 border border-white/10 min-w-0">
                                        <span class="text-slate-400 block text-[9px] truncate">Shopee</span>
                                        <span class="text-white font-bold block truncate">42 pcs</span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white/5 border border-white/10 min-w-0">
                                        <span class="text-slate-400 block text-[9px] truncate">Tokopedia</span>
                                        <span class="text-white font-bold block truncate">40 pcs</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Live Incoming Order Event Log --}}
                            <div
                                class="mt-3 p-2.5 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 flex items-center justify-between gap-2.5 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="w-2 h-2 rounded-full bg-[#00C4D8] animate-ping shrink-0"></span>
                                    <div class="min-w-0">
                                        <span class="text-white font-medium text-[11px] block truncate">Order Shopee
                                            (#2409SHP-88):</span>
                                        <span class="text-slate-300 text-[10px] block truncate">2x Kopi Arabika • Stok POS &
                                            Toko otomatis potong</span>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded bg-[#007AFF] text-white font-bold text-[10px] shrink-0">Cetak
                                    Resi</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerumitan Mengelola Banyak Marketplace Manual --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-b border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                        Tantangan Penjual Multi-Channel
                    </h2>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Mengapa Toko Online yang Berkembang Sering Terjebak Masalah Operasional?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3">
                        Semakin banyak marketplace yang Anda buka, semakin besar risiko penalti reputasi toko akibat
                        keterlambatan pengiriman dan stok kosong yang tidak terpantau.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                            <i data-lucide="package-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Overselling & Penalti Pembatalan
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Barang terakhir sudah dibeli oleh pengunjung toko fisik, tetapi di Shopee masih tertulis ada
                            stok. Anda terpaksa membatalkan pesanan pembeli online dan toko Anda terkena poin penalti
                            platform.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="printer" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Buka-Tutup Banyak Tab Seller Center
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Staf gudang harus login ke 4 tab browser terpisah untuk mencetak label pengiriman satu per satu.
                            Rentan salah tempel resi yang berujung retur barang dan komplain pembeli.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Hitung Laba Bersih Bikin Pusing</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
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
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                    Kemampuan Integrasi Marketplace COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Efisiensi Tanpa Batas untuk Penjual Modern
                </p>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">
                    Memadukan kecepatan operasional gudang dengan keteraturan pembukuan finansial dalam satu ekosistem
                    terpadu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Real-Time Stock Engine (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Sinkronisasi Stok Otomatis Seketika (Zero Delay)
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Mesin inventori terpusat COOCA secara aktif memperbarui kuantitas persediaan di seluruh toko
                            online saat ada pembelian di toko offline maupun kanal online lainnya. Hilangkan potensi
                            overselling dan jaga skor performa toko Anda selalu sempurna.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">Kecepatan Respons API:</span>
                        <span
                            class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1 shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Stok Terpotong Serentak &lt; 5 Detik
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Cetak Resi & Packing List Massal (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="printer" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Cetak Resi & Nota Kemas Massal
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Pilih 50 pesanan dari berbagai marketplace sekaligus dan cetak label pengiriman thermal dalam
                            satu klik. Dokumen packing list menyortir lokasi rak gudang agar proses pengambilan barang
                            berjalan kilat.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Efisiensi Gudang</span>
                        <span class="text-amber-500 font-bold shrink-0">Proses 100 Order dalam 15 Menit</span>
                    </div>
                </div>

                {{-- Bento Card 3: Update Harga Serentak (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="tags" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pembaruan Harga Fleksibel</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Ubah harga jual atau naikkan harga sedikit di marketplace untuk menutup biaya komisi platform tanpa
                        perlu menyunting satu per satu di setiap seller center.
                    </p>
                </div>

                {{-- Bento Card 4: Konsolidasi Keuangan & Komisi (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-500">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Auto-Jurnal Settlement</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Saat saldo marketplace dicairkan, COOCA secara cerdas membukukan uang kas masuk, potongan fee
                        platform, dan HPP produk ke laporan Laba Rugi.
                    </p>
                </div>

                {{-- Bento Card 5: Integrasi Barcode Verifikasi Kemas (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                        <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Scan Barcode Sebelum Kirim</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Verifikasi barang yang dimasukkan ke kardus dengan barcode scanner untuk memastikan tipe dan varian
                        warna sesuai dengan yang dipesan pembeli.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Alur Order Marketplace Hingga Akuntansi --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="absolute top-1/4 left-10 w-96 h-96 bg-[#007AFF]/10 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute bottom-10 right-10 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Siklus Penjualan Multi-Channel</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                        Bagaimana Transaksi Marketplace Mengalir di COOCA
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3">
                        Mengintegrasikan dunia e-commerce dengan operasional fisik toko secara mulus.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Pembeli Checkout Online</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Pelanggan bertransaksi di Shopee atau Tokopedia. Pesanan langsung masuk ke antrean COOCA Orders
                            dan stok di kasir toko fisik terpotong otomatis.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Pengambilan & Kemas Gudang</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Staf gudang mencetak resi pengiriman kurir dan nota ambil barang, lalu melakukan scan barcode
                            untuk memastikan barang yang dikemas tepat.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Serah Terima Kurir</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Paket diserahkan ke kurir ekspedisi. Status pesanan di marketplace otomatis berubah menjadi
                            'Sedang Dikirim' tanpa perlu konfirmasi manual.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Pencairan Dana Bersih</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
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
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                    Tanya Jawab Seputar Integrasi Marketplace
                </p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana sistem COOCA mencegah overselling (barang laku tapi stok fisik kosong)?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        COOCA menggunakan mesin inventori terpusat. Ketika 1 barang terjual di kasir outlet fisik, sistem
                        langsung memperbarui sisa stok ke Shopee, Tokopedia, dan TikTok Shop dalam hitungan detik. Jika stok
                        fisik habis, status produk di marketplace otomatis berubah menjadi 'Habis' sehingga pembeli online
                        tidak bisa melakukan checkout.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah bisa menghubungkan lebih dari satu akun toko pada marketplace yang sama?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat menghubungkan beberapa toko Shopee atau Tokopedia sekaligus (misal Toko Resmi Utama
                        dan Toko Cabang Kota Tertentu). Seluruh pesanan akan bermuara ke satu dashboard operasional COOCA.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara tim gudang memproses dan mengemas pesanan dari banyak marketplace?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Tim gudang cukup membuka modul Pesanan di COOCA. Semua order masuk berurutan lengkap dengan status
                        pembayaran. Anda dapat mencetak nota kemas (picking list) dan resi pengiriman kurir secara massal
                        dalam satu klik tanpa perlu membuka seller center masing-masing marketplace.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana perhitungan biaya admin dan komisi potongan marketplace dicatat?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        COOCA memisahkan nilai penjualan kotor dengan potongan komisi platform dan biaya gratis ongkir. Saat
                        dana dicairkan (settlement) ke rekening bank Anda, modul Akuntansi mencatat penerimaan kas bersih
                        dan mengalokasikan potongan komisi ke pos beban penjualan secara otomatis.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-1">
                        Modul Terkait
                    </h2>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                        Ekosistem Penjualan Multi-Channel
                    </p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.omnichannel.orders') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Manajemen Pesanan Terpadu
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola siklus pesanan dari checkout,
                        packing, hingga siap kirim.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Stok Gudang Terpusat
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kartu stok perpetual yang menjadi sumber
                        kebenaran tunggal stok barang.</p>
                </a>

                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 text-[#007AFF] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kasir toko fisik yang berbagi kuantitas stok
                        yang sama dengan marketplace.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Akuntansi & Laba Bersih
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Laporan Laba Rugi yang mencatat potongan fee
                        dan komisi e-commerce.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Dual ambient glows inside CTA --}}
                <div
                    class="absolute top-0 right-10 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-10 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Tingkatkan Penjualan E-Commerce Tanpa Kekacauan Gudang
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Mulai operasikan integrasi marketplace otomatis dan nikmati kemudahan mengelola stok multi-channel
                        bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all">
                            Coba Demo Integrasi Marketplace
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                            Konsultasi Channel Penjualan
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
