@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Pesanan Omnichannel & Order Fulfillment | COOCA')
@section('description', 'Satukan pesanan dari kasir POS toko fisik, website, WhatsApp, dan marketplace ke dalam satu pipeline pemrosesan terpadu. Rute pesanan ke cabang terdekat, cetak label massal, dan lacak status pengiriman.')
@section('keywords', 'software manajemen pesanan omnichannel, aplikasi order fulfillment, sistem proses pesanan toko online fisik, agregasi pesanan marketplace pos, order lifecycle indonesia')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Unified Order Management",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Sistem agregasi pesanan omnichannel dan pemenuhan order multi-cabang terpadu untuk retail, F&B, dan e-commerce.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Konsolidasi pesanan masuk dari kasir POS, WhatsApp, website, dan marketplace",
    "Pipeline pemrosesan visual (Kanban) dari order baru hingga serah terima kurir",
    "Rute cerdas pengiriman pesanan dari cabang outlet terdekat dengan pembeli",
    "Pencetakan nota kemas (picking slip) dan label resi ekspedisi terpadu",
    "Penanganan retur barang dan pengembalian stok gudang otomatis"
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
      "name": "Bagaimana COOCA menggabungkan pesanan dari kasir toko fisik dan marketplace online?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Setiap pesanan yang terjadi—baik pelanggan yang memesan langsung di kasir toko, pemesanan lewat pesan WhatsApp, maupun checkout di Shopee dan Tokopedia—langsung masuk ke antrean Unified Orders COOCA dengan tanda label asal channel yang jelas."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah pesanan online bisa diteruskan untuk diproses oleh cabang toko terdekat?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Fitur Smart Routing memungkinkan pesanan online dialokasikan ke cabang toko yang memiliki stok barang dan berlokasi paling dekat dengan alamat pelanggan, menghemat biaya ongkos kirim dan mempercepat waktu pengantaran."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana proses penanganan barang retur atau pesanan yang dibatalkan pembeli?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Saat retur disetujui, sistem membuat dokumen penerimaan barang kembali. Anda dapat memilih apakah barang dimasukkan kembali ke stok layak jual atau dicatat sebagai barang cacat, dan sistem otomatis menerbitkan jurnal penyesuaian keuangan yang sesuai."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah sistem mendukung pemesanan ambil di toko (Click & Collect / BOPIS)?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, sangat mendukung. Pelanggan dapat memesan dan membayar lewat website atau WhatsApp, memilih opsi 'Ambil di Toko Cabang Sudirman', dan staf toko akan menerima notifikasi untuk menyiapkan paket sebelum pelanggan tiba."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

    {{-- 1. HERO SECTION --}}
    <section class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
        {{-- Ambient Glows --}}
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-10 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            {{-- Breadcrumb --}}
            <nav class="pb-6" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-xs text-slate-400">
                    <li><a href="{{ route('landing') }}" class="hover:text-white transition-colors">Home</a></li>
                    <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                    <li><span class="text-slate-400">Omnichannel</span></li>
                    <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                    <li class="text-slate-200 font-semibold" aria-current="page">Manajemen Pesanan Terpadu</li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold tracking-wide">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                        <span>Omnichannel Order Lifecycle & Fulfillment</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-[1.15]">
                        Satukan Seluruh Alur Pesanan Bisnis <span class="text-[#00C4D8]">Dalam Satu Pipeline Cepat</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                        Pesanan datang dari kasir toko, WhatsApp, toko online, hingga marketplace. COOCA mengonsolidasikan semuanya ke dalam satu pipeline visual yang teratur, merutekan ke cabang terdekat, dan memastikan setiap pesanan terkirim tepat waktu.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200">
                            <span>Coba Demo Manajemen Order</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.omnichannel.marketplace') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                            <span>Koneksi ke Marketplace</span>
                        </a>
                    </div>

                    {{-- Key Trust Specs --}}
                    <div class="pt-4 border-t border-white/10 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Asal Pesanan</div>
                            <div class="text-sm font-bold text-white mt-0.5">POS, WA, & E-Commerce</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Routing Gudang</div>
                            <div class="text-sm font-bold text-white mt-0.5">Cabang Terdekat</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Kecepatan Kemas</div>
                            <div class="text-sm font-bold text-white mt-0.5">Picking Slip Massal</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live Unified Order Pipeline UI --}}
                <div class="lg:col-span-6">
                    <div class="relative rounded-2xl bg-[#0E1E45]/80 p-4 sm:p-5 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white">

                        {{-- Header Order Pipeline Control --}}
                        <div class="flex items-center justify-between pb-3 border-b border-white/10 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-[#007AFF]/20 text-[#00C4D8]">
                                    <i data-lucide="kanban" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-white">Pipeline Pesanan Hari Ini</div>
                                    <div class="text-[10px] text-slate-400">Konsolidasi Seluruh Channel • 84 Selesai</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">SLA: 98.4% Tepat Waktu</span>
                        </div>

                        {{-- 3 Kanban Pipeline Columns Simulation --}}
                        <div class="grid grid-cols-3 gap-2 my-3 text-xs">

                            {{-- Column 1: Order Baru --}}
                            <div class="p-2 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-2">
                                <div class="flex items-center justify-between pb-1 border-b border-white/10 text-[10px] font-semibold text-slate-400">
                                    <span>BARU MASUK</span>
                                    <span class="px-1.5 py-0.2 rounded bg-[#007AFF]/30 text-[#00C4D8] font-mono">3</span>
                                </div>

                                {{-- Card 1 --}}
                                <div class="p-2 rounded-lg bg-white/5 border border-white/10 space-y-1">
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="px-1 rounded bg-orange-500/20 text-orange-400 font-mono font-bold">Shopee</span>
                                        <span class="text-slate-400">2 mnt lalu</span>
                                    </div>
                                    <div class="font-semibold text-white text-[11px] truncate">#SHP-9102</div>
                                    <div class="text-[10px] text-slate-300">2x Biji Kopi Arabika</div>
                                    <div class="text-[10px] font-bold text-emerald-400 font-mono">Rp 280.000</div>
                                </div>

                                {{-- Card 2 --}}
                                <div class="p-2 rounded-lg bg-white/5 border border-white/10 space-y-1">
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="px-1 rounded bg-emerald-500/20 text-emerald-400 font-mono font-bold">WhatsApp</span>
                                        <span class="text-slate-400">5 mnt lalu</span>
                                    </div>
                                    <div class="font-semibold text-white text-[11px] truncate">#WA-4410</div>
                                    <div class="text-[10px] text-slate-300">Ambil di Toko Senopati</div>
                                    <div class="text-[10px] font-bold text-emerald-400 font-mono">Rp 95.000</div>
                                </div>
                            </div>

                            {{-- Column 2: Diproses / Packing --}}
                            <div class="p-2 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-2">
                                <div class="flex items-center justify-between pb-1 border-b border-white/10 text-[10px] font-semibold text-slate-400">
                                    <span>DIPACKING</span>
                                    <span class="px-1.5 py-0.2 rounded bg-amber-500/30 text-amber-300 font-mono">2</span>
                                </div>

                                {{-- Card 1 --}}
                                <div class="p-2 rounded-lg bg-white/5 border border-white/10 space-y-1">
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="px-1 rounded bg-emerald-500/20 text-emerald-300 font-mono font-bold">Tokopedia</span>
                                        <span class="text-slate-400">Gudang Cakung</span>
                                    </div>
                                    <div class="font-semibold text-white text-[11px] truncate">#TKP-8842</div>
                                    <div class="text-[10px] text-slate-300">1x French Press Coffee</div>
                                    <div class="text-[9px] text-amber-400 font-mono">Resi Siap Ditempel</div>
                                </div>

                                {{-- Card 2 --}}
                                <div class="p-2 rounded-lg bg-white/5 border border-white/10 space-y-1">
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="px-1 rounded bg-blue-500/20 text-blue-400 font-mono font-bold">Website</span>
                                        <span class="text-slate-400">Outlet Sudirman</span>
                                    </div>
                                    <div class="font-semibold text-white text-[11px] truncate">#WEB-1092</div>
                                    <div class="text-[10px] text-slate-300">Box Donat isi 6</div>
                                    <div class="text-[9px] text-amber-400 font-mono">Kitchen Menyiapkan</div>
                                </div>
                            </div>

                            {{-- Column 3: Siap Kirim --}}
                            <div class="p-2 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-2">
                                <div class="flex items-center justify-between pb-1 border-b border-white/10 text-[10px] font-semibold text-slate-400">
                                    <span>SIAP KIRIM</span>
                                    <span class="px-1.5 py-0.2 rounded bg-emerald-500/30 text-emerald-300 font-mono">4</span>
                                </div>

                                {{-- Card 1 --}}
                                <div class="p-2 rounded-lg bg-white/5 border border-white/10 space-y-1">
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="px-1 rounded bg-white/10 text-slate-300 font-mono">J&T Express</span>
                                        <span class="text-emerald-400">Kurir OTW</span>
                                    </div>
                                    <div class="font-semibold text-white text-[11px] truncate">#SHP-9080</div>
                                    <div class="text-[10px] text-slate-300">3 Paket Siap Serah</div>
                                    <div class="text-[9px] text-emerald-400 font-mono">Resi: JT8849102xx</div>
                                </div>
                            </div>

                        </div>

                        {{-- Unified Action Bar --}}
                        <div class="pt-2 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                Stok langsung dialokasikan ke masing-masing order
                            </span>
                            <a href="{{ route('public.demo') }}" class="text-[#00C4D8] hover:underline font-medium">Buka Pipeline Penuh →</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. PAIN POINTS: Kekacauan Memproses Pesanan yang Tercecer --}}
    <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-b border-slate-200/80 dark:border-white/10">
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                    Tantangan Pemenuhan Pesanan
                </h2>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Apakah Staf Gudang & Kasir Anda Sering Bingung Membedakan Pesanan Prioritas?
                </p>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3">
                    Ketika pesanan masuk dari kasir toko, WhatsApp, dan toko online tanpa urutan tunggal, pesanan penting sering terlewat dan waktu pengiriman membengkak.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                        <i data-lucide="hourglass" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Pesanan Terlewat & Batas Waktu Habis</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Pesanan masuk di WhatsApp terlupakan karena tertumpuk pesan chat baru dari pelanggan lain. Pesanan marketplace otomatis dibatalkan sistem karena melewati batas waktu pengiriman.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="package-search" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Salah Kirim Barang & Retur Tinggi</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Tanpa dokumen nota kemas (picking slip) yang jelas, staf gudang salah memasukkan ukuran baju atau varian rasa kopi, menyebabkan biaya ongkir retur ditanggung oleh penjual.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="map-pin-off" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Ongkos Kirim Mahal Karena Rute Salah</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Pelanggan tinggal di Surabaya, tetapi pesanan dikirim dari gudang utama di Jakarta padahal toko cabang Surabaya memiliki stok yang cukup. Waktu tempuh lama dan ongkir boros.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE ORDER MANAGEMENT CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                Kemampuan Manajemen Order COOCA
            </h2>
            <p class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                Kecepatan Fulfillment Tanpa Kesalahan Manusia
            </p>
            <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">
                Menghubungkan setiap pesanan langsung dengan staf kemas di gudang dan kasir outlet toko.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

            {{-- Bento Card 1: Unified Order Inbox (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="inbox" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Satu Pintu Pesanan untuk Seluruh Penjualan
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Semua transaksi masuk otomatis ke dalam antrean terpadu: pesanan kasir toko fisik, pesanan take-away WhatsApp, penjualan website toko, hingga order Shopee dan Tokopedia. Staf operasional tidak perlu memeriksa aplikasi berbeda satu per satu.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 flex items-center justify-between text-xs">
                    <span class="text-slate-600 dark:text-slate-300 font-medium">Prioritas Pengiriman:</span>
                    <span class="text-[#007AFF] font-semibold flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i> Disortir otomatis berdasarkan waktu deadline ekspedisi
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Smart Routing Cabang (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 flex items-center justify-center text-[#00C4D8]">
                        <i data-lucide="navigation" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Rute Pemenuhan Cerdas (Smart Routing)
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Teruskan pesanan secara cerdas ke cabang toko yang memiliki stok fisik dan berjarak paling dekat dengan alamat pembeli untuk pengantaran cepat instan (same-day).
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 text-xs flex items-center justify-between font-mono">
                    <span class="text-slate-500 dark:text-slate-400">Hemat Biaya Ongkir</span>
                    <span class="text-[#00C4D8] font-bold">Kirim dari Cabang Terdekat</span>
                </div>
            </div>

            {{-- Bento Card 3: Picking & Packing Slip (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-500">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Nota Kemas (Picking List)</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Daftar ambil barang mengelompokkan item berdasarkan lorong dan rak gudang, memudahkan staf mengambil puluhan barang secara efisien tanpa bolak-balik.
                </p>
            </div>

            {{-- Bento Card 4: Penanganan Retur Terintegrasi (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Alur Retur & Refund Rapi</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Verifikasi fisik barang yang diretur, kembalikan stok layak pakai ke gudang, dan sinkronkan pengembalian dana kas dengan jurnal akuntansi secara otomatis.
                </p>
            </div>

            {{-- Bento Card 5: SLA & Kecepatan Pengiriman (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                    <i data-lucide="gauge" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pemantauan SLA Pengiriman</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Lacak durasi rata-rata dari pesanan diterima hingga diserahkan ke kurir. Jaga reputasi toko tetap memiliki badge penjual terbaik di semua platform.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Dari Pesanan Hingga Sampai ke Pembeli --}}
    <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
        <div class="absolute top-1/4 left-10 w-96 h-96 bg-[#007AFF]/10 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                    <span>Siklus Pemenuhan Pesanan Terpadu</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                    Bagaimana Pesanan Diproses Cepat di COOCA
                </h2>
                <p class="text-slate-400 text-sm sm:text-base mt-3">
                    Setiap langkah pemrosesan terkoordinasi antara tim kasir, gudang, dan kurir logistik.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                        1
                    </div>
                    <h3 class="text-base font-bold text-white">Pesanan Terverifikasi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pesanan masuk dari channel manapun dan status pembayaran tervalidasi lunas. Stok fisik otomatis dialokasikan sehingga tidak bisa dijual ganda.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/30">
                        2
                    </div>
                    <h3 class="text-base font-bold text-white">Picking & Packing</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Staf gudang mencetak label resi dan nota kemas, mengambil barang sesuai rak, dan melakukan scan barcode untuk memastikan akurasi barang.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                    <div class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/30">
                        3
                    </div>
                    <h3 class="text-base font-bold text-white">Penyerahan ke Kurir</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kurir ekspedisi mengambil paket. Status pesanan langsung terupdate menjadi 'Dalam Perjalanan' dan nomor resi terkirim ke WhatsApp pelanggan.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                        4
                    </div>
                    <h3 class="text-base font-bold text-white">Pesanan Selesai</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pelanggan menerima barang dengan puas. Transaksi dibukukan tuntas ke laporan penjualan dan histori CRM pelanggan bertambah secara otomatis.
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
                Tanya Jawab Seputar Manajemen Pesanan
            </p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    <span>Bagaimana COOCA menggabungkan pesanan dari kasir toko fisik dan marketplace online?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                    Setiap pesanan yang terjadi—baik pelanggan yang memesan langsung di kasir toko, pemesanan lewat pesan WhatsApp, maupun checkout di Shopee dan Tokopedia—langsung masuk ke antrean Unified Orders COOCA dengan tanda label asal channel yang jelas.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    <span>Apakah pesanan online bisa diteruskan untuk diproses oleh cabang toko terdekat?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                    Bisa. Fitur Smart Routing memungkinkan pesanan online dialokasikan ke cabang toko yang memiliki stok barang dan berlokasi paling dekat dengan alamat pelanggan, menghemat biaya ongkos kirim dan mempercepat waktu pengantaran.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    <span>Bagaimana proses penanganan barang retur atau pesanan yang dibatalkan pembeli?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                    Saat retur disetujui, sistem membuat dokumen penerimaan barang kembali. Anda dapat memilih apakah barang dimasukkan kembali ke stok layak jual atau dicatat sebagai barang cacat, dan sistem otomatis menerbitkan jurnal penyesuaian keuangan yang sesuai.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    <span>Apakah sistem mendukung pemesanan ambil di toko (Click & Collect / BOPIS)?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                    Ya, sangat mendukung. Pelanggan dapat memesan dan membayar lewat website atau WhatsApp, memilih opsi 'Ambil di Toko Cabang Sudirman', dan staf toko akan menerima notifikasi untuk menyiapkan paket sebelum pelanggan tiba.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-1">
                    Modul Terkait
                </h2>
                <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                    Ekosistem Operasional Penjualan
                </p>
            </div>
            <a href="{{ route('public.erp.erp') }}"
                class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lihat Seluruh Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.omnichannel.marketplace') }}"
                class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Integrasi Marketplace
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tarik pesanan otomatis dari Shopee, Tokopedia, dan TikTok Shop.</p>
            </a>

            <a href="{{ route('public.erp.pos') }}"
                class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Point of Sale (POS)
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kasir cepat yang menyatu dengan data pesanan ambil di toko.</p>
            </a>

            <a href="{{ route('public.erp.inventory') }}"
                class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Stok Gudang Multi-Cabang
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Alokasi stok otomatis saat pesanan masuk agar tidak bentrok.</p>
            </a>

            <a href="{{ route('public.omnichannel.whatsapp') }}"
                class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                    Notifikasi Status WhatsApp
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kirim nomor resi pengiriman dan status order langsung ke chat pembeli.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
            {{-- Dual ambient glows inside CTA --}}
            <div class="absolute top-0 right-10 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-10 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                    Percepat Pemrosesan Pesanan Toko Anda Hari Ini
                </h2>
                <p class="text-sm sm:text-base text-slate-300">
                    Kendalikan seluruh order fisik dan online dalam satu pipeline rapi dan hilangkan risiko salah kirim barang bersama COOCA Orders.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
                    <a href="{{ route('public.demo') }}"
                        class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all">
                        Coba Demo Manajemen Pesanan
                    </a>
                    <a href="{{ route('public.pricing') }}"
                        class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                        Konsultasi Operasional Toko
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
