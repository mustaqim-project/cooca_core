@extends('layouts.public_marketing')

@section('title', 'Software Omnichannel Customer & Single Customer View Terpadu | COOCA')
@section('description', 'Satukan data pelanggan dari toko fisik, website, WhatsApp, dan marketplace ke dalam Single Customer View. Kenali riwayat belanja utuh pelanggan di seluruh channel penjualan Anda.')
@section('keywords', 'software omnichannel customer, single customer view indonesia, database pelanggan lintas channel, konsolidasi data pembeli toko fisik online, resolusi identitas crm')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Omnichannel Customer View",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Platform integrasi data identitas pelanggan omnichannel yang menggabungkan riwayat belanja dari toko fisik, chat, dan marketplace.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Konsolidasi otomatis kontak pelanggan dari kasir POS, WhatsApp, dan toko online",
    "Penyatuan profil ganda (Identity Resolution) berbasis nomor telepon dan email",
    "Riwayat transaksi lintas channel terpadu (Cross-Channel Purchasing History)",
    "Catatan preferensi pribadi pelanggan yang dapat diakses oleh kasir cabang",
    "Kepatuhan standar keamanan privasi data konsumen Indonesia (UU PDP)"
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
      "name": "Bagaimana COOCA mengenali bahwa pembeli di toko fisik adalah orang yang sama dengan pembeli di marketplace?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA menggunakan mesin pencocokan identitas (Identity Resolution). Sistem mendeteksi kesamaan nomor telepon, alamat email, atau nama dan alamat pengiriman. Jika ditemukan kecocokan, data pembelian marketplace otomatis digabungkan ke profil pelanggan yang sama di database kasir POS."
      }
    },
    {
      "@type": "Question",
      "name": "Apa manfaat Single Customer View bagi staf kasir toko fisik?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Saat pelanggan menyebutkan nomor WhatsApp di kasir, staf langsung mengetahui bahwa pelanggan tersebut adalah pembeli setia online, mengetahui produk favoritnya, dan dapat menyapa dengan ramah tanpa menganggapnya sebagai orang asing."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah sistem bisa menggabungkan kontak ganda (duplicate contacts) secara otomatis?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. COOCA memiliki fitur Smart Merge Contact yang merekomendasikan penggabungan dua profil yang memiliki informasi identik, menggabungkan riwayat transaksi dan poin loyalitas mereka secara transparan."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana sistem menjamin keamanan data pribadi pelanggan sesuai UU PDP?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Data nomor kontak dan alamat pelanggan tersimpan dengan enkripsi standar industri. Hak akses dibatasi secara ketat berdasarkan peran staf, dan staf biasa tidak dapat mengekspor atau menyalin database kontak pelanggan ke perangkat pribadi."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-indigo-500/10 via-purple-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><span class="text-neutral-500 dark:text-neutral-400">Omnichannel</span></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Single Customer View</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/40 text-indigo-700 dark:text-indigo-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Unified Identity & Cross-Channel Profile</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Satu Pandangan Utuh Pelanggan <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">Dari Toko Fisik Sampai Marketplace</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Pelanggan Anda berbelanja di kasir toko fisik, memesan via WhatsApp, dan checkout di marketplace online. COOCA menyatukan seluruh jejak interaksi tersebut menjadi satu profil utuh (Single Customer View) sehingga Anda mengenali nilai sebenarnya dari setiap konsumen.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Demo Customer View</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.erp.crm') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Pelajari Modul CRM Poin</span>
                    </a>
                </div>

                {{-- Key Trust Specs --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Resolusi Identitas</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Pencocokan Cerdas</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Riwayat Transaksi</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Offline + Online</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Privasi Konsumen</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Sesuai UU PDP</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live Identity Resolution Graph UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Unified Profile Header --}}
                    <div class="p-3.5 rounded-xl bg-neutral-950/90 border border-neutral-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                RP
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-white text-sm">Rian Pratama</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/40">VIP Omnichannel</span>
                                </div>
                                <div class="text-[11px] text-neutral-400 font-mono mt-0.5">+62 813-1120-xxxx • rian.p@email.com</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-neutral-400">Total Belanja Semua Kanal</div>
                            <div class="text-base font-bold text-indigo-400 font-mono">Rp 6.940.000</div>
                        </div>
                    </div>

                    {{-- Connected Sales Channels Strip --}}
                    <div class="grid grid-cols-3 gap-2 my-3 text-xs">
                        <div class="p-2 rounded-xl bg-neutral-950/60 border border-neutral-800/80 text-left">
                            <div class="flex items-center gap-1.5 text-blue-400 text-[10px] font-semibold">
                                <i data-lucide="store" class="w-3.5 h-3.5"></i> Kasir POS Toko
                            </div>
                            <div class="text-[11px] text-white font-mono mt-1 font-bold">12x Belanja Fisik</div>
                            <div class="text-[9px] text-neutral-500">Cabang Senopati</div>
                        </div>

                        <div class="p-2 rounded-xl bg-neutral-950/60 border border-neutral-800/80 text-left">
                            <div class="flex items-center gap-1.5 text-orange-400 text-[10px] font-semibold">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Tokopedia & Shopee
                            </div>
                            <div class="text-[11px] text-white font-mono mt-1 font-bold">3x Belanja Online</div>
                            <div class="text-[9px] text-neutral-500">Username: @rian_p99</div>
                        </div>

                        <div class="p-2 rounded-xl bg-neutral-950/60 border border-neutral-800/80 text-left">
                            <div class="flex items-center gap-1.5 text-emerald-400 text-[10px] font-semibold">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> WhatsApp Order
                            </div>
                            <div class="text-[11px] text-white font-mono mt-1 font-bold">2x Takeaway Pesan</div>
                            <div class="text-[9px] text-neutral-500">Pickup Outlet</div>
                        </div>
                    </div>

                    {{-- Unified Timeline Log --}}
                    <div class="space-y-1.5 text-xs text-left">
                        <div class="text-[10px] uppercase font-mono text-neutral-500 px-1">Aktivitas Belanja Terakhir:</div>

                        <div class="p-2 rounded-xl bg-neutral-950/40 border border-neutral-800/60 flex items-center justify-between text-[11px]">
                            <div>
                                <span class="text-white font-medium">Beli di Tokopedia:</span>
                                <span class="text-neutral-400"> 1x French Press Coffee Glass</span>
                            </div>
                            <span class="text-neutral-500 text-[10px]">Kemarin</span>
                        </div>

                        <div class="p-2 rounded-xl bg-neutral-950/40 border border-neutral-800/60 flex items-center justify-between text-[11px]">
                            <div>
                                <span class="text-white font-medium">Mampir ke Kasir POS:</span>
                                <span class="text-neutral-400"> 2x Kopi Susu Aren (Outlet Senopati)</span>
                            </div>
                            <span class="text-neutral-500 text-[10px]">3 Hari Lalu</span>
                        </div>
                    </div>

                    {{-- Personalized Service Insight Footer --}}
                    <div class="mt-3 p-2.5 rounded-xl bg-indigo-950/30 border border-indigo-800/40 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-indigo-400"></i>
                            <div>
                                <span class="text-white font-medium text-[11px]">Preferensi Tersimpan:</span>
                                <span class="text-neutral-400 text-[10px]"> Suka biji kopi giling halus (Fine Grind)</span>
                            </div>
                        </div>
                        <span class="text-indigo-400 text-[10px] font-mono">Sinkron ke Kasir POS</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Jebakan Database Pelanggan Terpecah-Pecah --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-3">Tantangan Fragmentasi Data Konsumen</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Apakah Anda Menganggap Pelanggan Setia Anda Sebagai Orang Asing?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Ketika data pembeli di toko fisik dan toko online terpisah, pengalaman pelanggan menjadi canggung dan bisnis Anda kehilangan peluang up-selling yang besar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="user-x" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Pelanggan VIP Dianggap Pembeli Biasa</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Konsumen telah berbelanja jutaan rupiah di Shopee toko Anda, namun saat datang ke outlet fisik kasir memperlakukannya seperti pembeli pertama yang tidak dikenal.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Duplikasi Data & Kontak Ganda</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Satu orang pelanggan tercatat 3 kali di spreadsheet berbeda dengan nomor handphone yang sama, membuat analisis ukuran belanja (Customer Lifetime Value) menjadi keliru.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="target" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Promosi Tidak Tepat Sasaran</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Mengirimkan kupon diskon barang yang baru saja dibeli pelanggan kemarin di toko online. Konsumen merasa promosi Anda tidak dipersonalisasi dan mengabaikan penawaran berikutnya.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE OMNICHANNEL CUSTOMER CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-3">Fitur Single Customer View COOCA</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Membangun Hubungan Personal dengan Setiap Konsumen
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Menghubungkan titik-titik data yang terpisah menjadi gambaran komprehensif perjalanan belanja pelanggan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Identity Resolution Engine (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="merge" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Penyatuan Profil Otomatis (Smart Identity Merge)
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ketika pembeli memasukkan nomor HP atau email yang sama saat bertransaksi di kasir maupun saat checkout toko online, sistem secara otomatis menggabungkan profil mereka ke dalam satu identitas induk tanpa membuat catatan ganda.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Akurasi Identitas:</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i> Riwayat Belanja Gabungan Terhitung Akurat
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Preferensi & Catatan Khusus Kasir (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="heart" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Preferensi & Catatan Layanan Toko
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Simpan catatan penting seperti selera pesanan, tipe kendaraan, ukuran pakaian, hingga alamat pengiriman favorit yang langsung tampil di layar kasir POS saat nama pelanggan dipanggil.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Pengalaman Pelanggan</span>
                    <span class="text-purple-500 font-bold">Layanan Personal Bintang 5</span>
                </div>
            </div>

            {{-- Bento Card 3: Cross-Channel Purchase History (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Riwayat Transaksi Lengkap</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Lihat tanggal pembelian, produk yang dibeli, dan metode pembayaran di setiap channel penjualan dalam satu linimasa kronologis yang teratur.
                </p>
            </div>

            {{-- Bento Card 4: Kepatuhan Privasi Data (UU PDP) (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Kepatuhan UU PDP</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Lindungi privasi konsumen Anda dengan enkripsi database dan manajemen persetujuan (consent) untuk pengiriman materi promosi.
                </p>
            </div>

            {{-- Bento Card 5: Segmentasi Lintas Saluran (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="filter" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Segmentasi Lintas Channel</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Saring pelanggan yang hanya belanja online untuk diajak mengunjungi toko fisik terdekat dengan voucher eksklusif.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Dari Titik Temu Menjadi Data Terpadu --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-400 text-xs font-semibold mb-3 border border-indigo-500/30">
                    <span>Siklus Penyatuan Identitas Konsumen</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Bagaimana Data Pelanggan Bersatu di COOCA
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Mengintegrasikan setiap interaksi belanja menjadi hubungan bisnis jangka panjang.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Interaksi di Berbagai Kanal</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Pelanggan melakukan transaksi di kasir POS toko fisik, memesan lewat chat WhatsApp, atau membeli di marketplace online resmi Anda.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Deteksi Kesamaan Identitas</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Sistem mendeteksi nomor telepon atau alamat email yang cocok dan secara otomatis menautkan data transaksi ke profil induk pelanggan.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Profil 360° Terupdate</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Total belanja seumur hidup (CLV), status tier loyalty member, dan preferensi produk langsung terbarui di seluruh sistem secara real-time.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Layanan Lebih Personal</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Staf toko menyapa pelanggan dengan hangat, menawarkan produk pelengkap yang relevan, dan meningkatkan kepuasan serta loyalitas belanja.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Single Customer View</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana COOCA mengenali bahwa pembeli di toko fisik adalah orang yang sama dengan pembeli di marketplace?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA menggunakan mesin pencocokan identitas (Identity Resolution). Sistem mendeteksi kesamaan nomor telepon, alamat email, atau nama dan alamat pengiriman. Jika ditemukan kecocokan, data pembelian marketplace otomatis digabungkan ke profil pelanggan yang sama di database kasir POS.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apa manfaat Single Customer View bagi staf kasir toko fisik?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Saat pelanggan menyebutkan nomor WhatsApp di kasir, staf langsung mengetahui bahwa pelanggan tersebut adalah pembeli setia online, mengetahui produk favoritnya, dan dapat menyapa dengan ramah tanpa menganggapnya sebagai orang asing.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah sistem bisa menggabungkan kontak ganda (duplicate contacts) secara otomatis?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Bisa. COOCA memiliki fitur Smart Merge Contact yang merekomendasikan penggabungan dua profil yang memiliki informasi identik, menggabungkan riwayat transaksi dan poin loyalitas mereka secara transparan.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana sistem menjamin keamanan data pribadi pelanggan sesuai UU PDP?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Data nomor kontak dan alamat pelanggan tersimpan dengan enkripsi standar industri. Hak akses dibatasi secara ketat berdasarkan peran staf, dan staf biasa tidak dapat mengekspor atau menyalin database kontak pelanggan ke perangkat pribadi.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Pengalaman Pelanggan</p>
            </div>
            <a href="{{ route('public.erp.erp') }}" class="text-xs sm:text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lihat Seluruh Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.erp.crm') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">CRM & Loyalitas Poin</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Sistem tier membership dan reward poin belanja universal.</p>
            </a>

            <a href="{{ route('public.erp.pos') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Point of Sale (POS)</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Tampilkan profil dan preferensi pelanggan langsung di meja kasir.</p>
            </a>

            <a href="{{ route('public.omnichannel.marketplace') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-950 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Integrasi Marketplace</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Tarik histori pesanan online dari Shopee dan Tokopedia ke profil pembeli.</p>
            </a>

            <a href="{{ route('public.omnichannel.whatsapp') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Komunikasi WhatsApp</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kirim pesan personal, struk digital, dan penawaran khusus ke nomor kontak resmi.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Mulai Kenali Pelanggan Anda Secara Menyeluruh
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Satukan database pelanggan lintas saluran Anda dan hadirkan pengalaman belanja personal yang mengesankan bersama COOCA Omnichannel Customer.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Single Customer View
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Database Konsumen
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
