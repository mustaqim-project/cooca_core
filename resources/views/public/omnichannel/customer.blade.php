@extends('layouts.public_marketing')

@section('title', 'Software Omnichannel Customer & Single Customer View Terpadu | COOCA')
@section('description',
    'Satukan data pelanggan dari toko fisik, website, WhatsApp, dan marketplace ke dalam Single Customer View. Kenali riwayat belanja utuh pelanggan di seluruh channel penjualan Anda.')
@section('og_title', 'Software Omnichannel Customer & Single Customer View Terpadu | COOCA')
@section('og_description',
    'Satukan data pelanggan dari toko fisik, website, WhatsApp, dan marketplace ke dalam Single Customer View. Kenali riwayat belanja utuh pelanggan di seluruh channel penjualan Anda.')
@section('keywords',
    'software omnichannel customer, single customer view indonesia, database pelanggan lintas channel, konsolidasi data pembeli toko fisik online, resolusi identitas crm')

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
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION (Type A Full Viewport) --}}
        <section
            class="relative bg-[#060B1E] text-white lg:min-h-[calc(100svh-84px)] lg:flex lg:items-center py-12 lg:py-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-10 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                {{-- Breadcrumb --}}
                <nav class="pb-6" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs text-slate-400">
                        <li><a href="{{ route('landing') }}" class="hover:text-white transition-colors">Home</a></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li><span class="text-slate-400">Omnichannel</span></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li class="text-slate-200 font-semibold" aria-current="page">Single Customer View</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
                    {{-- Left Column: Copy & Value Proposition (Mobile Center, Desktop Left ~ 5 Cols) --}}
                    <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Unified Identity &amp; Cross-Channel Profile
                            </p>

                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Satu Pandangan Utuh Pelanggan <span class="text-[#00C4D8]">Dari Toko Fisik Sampai
                                    Marketplace</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            Pelanggan Anda berbelanja di kasir toko fisik, memesan via WhatsApp, dan checkout di marketplace
                            online. COOCA menyatukan seluruh jejak interaksi tersebut menjadi satu profil utuh (Single
                            Customer View) sehingga Anda mengenali nilai sebenarnya dari setiap konsumen.
                        </p>

                        {{-- Action CTAs (Centered on Mobile, Row on Desktop) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 pt-2 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200 min-h-[48px]">
                                <span>Coba Demo Customer View</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                            </a>
                            <a href="{{ route('public.erp.crm') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all min-h-[48px]">
                                <span>Pelajari Modul CRM Poin</span>
                            </a>
                        </div>

                        {{-- Key Trust Specs (Centered on Mobile) --}}
                        <div
                            class="pt-4 border-t border-white/10 grid grid-cols-2 sm:grid-cols-3 gap-3.5 sm:gap-4 text-center sm:text-left w-full">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Resolusi Identitas</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Pencocokan Cerdas</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Riwayat Transaksi</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Offline + Online</div>
                            </div>
                            <div class="min-w-0 col-span-2 sm:col-span-1">
                                <div class="text-xs text-slate-400 font-medium truncate">Privasi Konsumen</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Sesuai UU PDP</div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Simulated Live Identity Resolution Graph UI (7 Cols ~ 58%) --}}
                    <div class="lg:col-span-7">
                        <div
                            class="relative rounded-2xl bg-[#0E1E45]/80 p-4 sm:p-5 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white">

                            {{-- Unified Profile Header --}}
                            <div
                                class="p-3.5 rounded-xl bg-[#060B1E]/90 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="w-11 h-11 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#00C4D8] flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0">
                                        RP
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-bold text-white text-sm truncate">Rian Pratama</h3>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/20 text-[#00C4D8] border border-[#00C4D8]/30 shrink-0">VIP
                                                Omnichannel</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5 truncate">+62 813-1120-xxxx
                                            • rian.p@email.com</div>
                                    </div>
                                </div>
                                <div
                                    class="text-left sm:text-right shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/10">
                                    <div class="text-[10px] text-slate-400">Total Belanja Semua Kanal</div>
                                    <div class="text-base font-bold text-[#00C4D8] font-mono">Rp 6.940.000</div>
                                </div>
                            </div>

                            {{-- Connected Sales Channels Strip --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 my-3 text-xs">
                                <div class="p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 text-left min-w-0">
                                    <div
                                        class="flex items-center gap-1.5 text-[#00C4D8] text-[10px] font-semibold truncate">
                                        <i data-lucide="store" class="w-3.5 h-3.5 shrink-0"></i> <span>Kasir POS Toko</span>
                                    </div>
                                    <div class="text-[11px] text-white font-mono mt-1 font-bold truncate">12x Belanja Fisik
                                    </div>
                                    <div class="text-[9px] text-slate-400 truncate">Cabang Senopati</div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 text-left min-w-0">
                                    <div
                                        class="flex items-center gap-1.5 text-orange-400 text-[10px] font-semibold truncate">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Marketplace</span>
                                    </div>
                                    <div class="text-[11px] text-white font-mono mt-1 font-bold truncate">3x Belanja Online
                                    </div>
                                    <div class="text-[9px] text-slate-400 truncate">@rian_p99</div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 text-left min-w-0">
                                    <div
                                        class="flex items-center gap-1.5 text-emerald-400 text-[10px] font-semibold truncate">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5 shrink-0"></i> <span>WhatsApp
                                            Order</span>
                                    </div>
                                    <div class="text-[11px] text-white font-mono mt-1 font-bold truncate">2x Takeaway Pesan
                                    </div>
                                    <div class="text-[9px] text-slate-400 truncate">Pickup Outlet</div>
                                </div>
                            </div>

                            {{-- Unified Timeline Log --}}
                            <div class="space-y-1.5 text-xs text-left">
                                <div class="text-[10px] uppercase font-mono text-slate-400 px-1">Aktivitas Belanja Terakhir:
                                </div>

                                <div
                                    class="p-2 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-2 text-[11px]">
                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="text-white font-medium">Beli di Tokopedia:</span>
                                        <span class="text-slate-300"> 1x French Press Coffee Glass</span>
                                    </div>
                                    <span class="text-slate-400 text-[10px] shrink-0">Kemarin</span>
                                </div>

                                <div
                                    class="p-2 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-2 text-[11px]">
                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="text-white font-medium">Mampir ke Kasir POS:</span>
                                        <span class="text-slate-300"> 2x Kopi Susu Aren (Outlet Senopati)</span>
                                    </div>
                                    <span class="text-slate-400 text-[10px] shrink-0">3 Hari Lalu</span>
                                </div>
                            </div>

                            {{-- Personalized Service Insight Footer --}}
                            <div
                                class="mt-3 p-2.5 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                    <div class="min-w-0">
                                        <span class="text-white font-medium text-[11px] truncate block">Preferensi
                                            Tersimpan:</span>
                                        <span class="text-slate-300 text-[10px] truncate block">Suka biji kopi giling halus
                                            (Fine Grind)</span>
                                    </div>
                                </div>
                                <span class="text-[#00C4D8] text-[10px] font-mono shrink-0">Sinkron ke Kasir POS</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Jebakan Database Pelanggan Terpecah-Pecah --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-b border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                        Tantangan Fragmentasi Data Konsumen
                    </h2>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Apakah Anda Menganggap Pelanggan Setia Anda Sebagai Orang Asing?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3">
                        Ketika data pembeli di toko fisik dan toko online terpisah, pengalaman pelanggan menjadi canggung
                        dan bisnis Anda kehilangan peluang up-selling yang besar.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                            <i data-lucide="user-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pelanggan VIP Dianggap Pembeli Biasa
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Konsumen telah berbelanja jutaan rupiah di Shopee toko Anda, namun saat datang ke outlet fisik
                            kasir memperlakukannya seperti pembeli pertama yang tidak dikenal.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="copy" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Duplikasi Data & Kontak Ganda</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Satu orang pelanggan tercatat 3 kali di spreadsheet berbeda dengan nomor handphone yang sama,
                            membuat analisis ukuran belanja (Customer Lifetime Value) menjadi keliru.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="target" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Promosi Tidak Tepat Sasaran</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Mengirimkan kupon diskon barang yang baru saja dibeli pelanggan kemarin di toko online. Konsumen
                            merasa promosi Anda tidak dipersonalisasi dan mengabaikan penawaran berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE OMNICHANNEL CUSTOMER CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                    Fitur Single Customer View COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Membangun Hubungan Personal dengan Setiap Konsumen
                </p>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">
                    Menghubungkan titik-titik data yang terpisah menjadi gambaran komprehensif perjalanan belanja pelanggan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Identity Resolution Engine (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="merge" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Penyatuan Profil Otomatis (Smart Identity Merge)
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Ketika pembeli memasukkan nomor HP atau email yang sama saat bertransaksi di kasir maupun saat
                            checkout toko online, sistem secara otomatis menggabungkan profil mereka ke dalam satu identitas
                            induk tanpa membuat catatan ganda.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">Akurasi Identitas:</span>
                        <span class="text-[#007AFF] font-semibold flex items-center gap-1 shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Riwayat Belanja Gabungan Terhitung Akurat
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Preferensi & Catatan Khusus Kasir (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                            <i data-lucide="heart" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Preferensi & Catatan Layanan Toko
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Simpan catatan penting seperti selera pesanan, tipe kendaraan, ukuran pakaian, hingga alamat
                            pengiriman favorit yang langsung tampil di layar kasir POS saat nama pelanggan dipanggil.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Pengalaman Pelanggan</span>
                        <span class="text-purple-500 font-bold shrink-0">Layanan Personal Bintang 5</span>
                    </div>
                </div>

                {{-- Bento Card 3: Cross-Channel Purchase History (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Riwayat Transaksi Lengkap</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Lihat tanggal pembelian, produk yang dibeli, dan metode pembayaran di setiap channel penjualan dalam
                        satu linimasa kronologis yang teratur.
                    </p>
                </div>

                {{-- Bento Card 4: Kepatuhan Privasi Data (UU PDP) (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-500">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kepatuhan UU PDP</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Lindungi privasi konsumen Anda dengan enkripsi database dan manajemen persetujuan (consent) untuk
                        pengiriman materi promosi.
                    </p>
                </div>

                {{-- Bento Card 5: Segmentasi Lintas Saluran (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="filter" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Segmentasi Lintas Channel</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Saring pelanggan yang hanya belanja online untuk diajak mengunjungi toko fisik terdekat dengan
                        voucher eksklusif.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Dari Titik Temu Menjadi Data Terpadu --}}
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
                        <span>Siklus Penyatuan Identitas Konsumen</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                        Bagaimana Data Pelanggan Bersatu di COOCA
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3">
                        Mengintegrasikan setiap interaksi belanja menjadi hubungan bisnis jangka panjang.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Interaksi di Berbagai Kanal</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Pelanggan melakukan transaksi di kasir POS toko fisik, memesan lewat chat WhatsApp, atau membeli
                            di marketplace online resmi Anda.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Deteksi Kesamaan Identitas</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Sistem mendeteksi nomor telepon atau alamat email yang cocok dan secara otomatis menautkan data
                            transaksi ke profil induk pelanggan.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Profil 360° Terupdate</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Total belanja seumur hidup (CLV), status tier loyalty member, dan preferensi produk langsung
                            terbarui di seluruh sistem secara real-time.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Layanan Lebih Personal</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Staf toko menyapa pelanggan dengan hangat, menawarkan produk pelengkap yang relevan, dan
                            meningkatkan kepuasan serta loyalitas belanja.
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
                    Tanya Jawab Seputar Single Customer View
                </p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana COOCA mengenali bahwa pembeli di toko fisik adalah orang yang sama dengan pembeli di
                            marketplace?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        COOCA menggunakan mesin pencocokan identitas (Identity Resolution). Sistem mendeteksi kesamaan nomor
                        telepon, alamat email, atau nama dan alamat pengiriman. Jika ditemukan kecocokan, data pembelian
                        marketplace otomatis digabungkan ke profil pelanggan yang sama di database kasir POS.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apa manfaat Single Customer View bagi staf kasir toko fisik?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Saat pelanggan menyebutkan nomor WhatsApp di kasir, staf langsung mengetahui bahwa pelanggan
                        tersebut adalah pembeli setia online, mengetahui produk favoritnya, dan dapat menyapa dengan ramah
                        tanpa menganggapnya sebagai orang asing.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah sistem bisa menggabungkan kontak ganda (duplicate contacts) secara otomatis?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Bisa. COOCA memiliki fitur Smart Merge Contact yang merekomendasikan penggabungan dua profil yang
                        memiliki informasi identik, menggabungkan riwayat transaksi dan poin loyalitas mereka secara
                        transparan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana sistem menjamin keamanan data pribadi pelanggan sesuai UU PDP?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Data nomor kontak dan alamat pelanggan tersimpan dengan enkripsi standar industri. Hak akses
                        dibatasi secara ketat berdasarkan peran staf, dan staf biasa tidak dapat mengekspor atau menyalin
                        database kontak pelanggan ke perangkat pribadi.
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
                        Ekosistem Pengalaman Pelanggan
                    </p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.erp.crm') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        CRM & Loyalitas Poin
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sistem tier membership dan reward poin
                        belanja universal.</p>
                </a>

                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tampilkan profil dan preferensi pelanggan
                        langsung di meja kasir.</p>
                </a>

                <a href="{{ route('public.omnichannel.marketplace') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Integrasi Marketplace
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tarik histori pesanan online dari Shopee dan
                        Tokopedia ke profil pembeli.</p>
                </a>

                <a href="{{ route('public.omnichannel.whatsapp') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Komunikasi WhatsApp
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kirim pesan personal, struk digital, dan
                        penawaran khusus ke nomor kontak resmi.</p>
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
                        Mulai Kenali Pelanggan Anda Secara Menyeluruh
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Satukan database pelanggan lintas saluran Anda dan hadirkan pengalaman belanja personal yang
                        mengesankan bersama COOCA Omnichannel Customer.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all">
                            Coba Demo Single Customer View
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                            Konsultasi Database Konsumen
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
