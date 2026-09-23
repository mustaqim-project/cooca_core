@extends('layouts.public_marketing')

@section('title', 'Pusat Bantuan & Tanya Jawab (FAQ) Lengkap: Solusi Kasir & Stok | COOCA')
@section('description', 'Temukan jawaban lengkap seputar skema lisensi gratis, kompatibilitas printer thermal, keamanan data cloud, mode kasir offline, dan panduan migrasi data bisnis ke COOCA.')
@section('keywords', 'faq cooca, tanya jawab aplikasi kasir, cara setting printer thermal bluetooth, aplikasi kasir offline, keamanan data erp umkm, cara impor data excel ke kasir')

@push('seo')
<link rel="canonical" href="{{ route('public.resources.faq') }}" />
<meta property="og:title" content="Pusat Bantuan & Tanya Jawab (FAQ) Lengkap | COOCA" />
<meta property="og:description" content="Pertanyaan yang sering diajukan seputar operasional, printer thermal, keamanan tenant, dan fitur kasir offline COOCA." />
<meta property="og:url" content="{{ route('public.resources.faq') }}" />
<meta property="og:type" content="website" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="FAQ & Pusat Informasi Operasional COOCA" />
<meta name="twitter:description" content="Semua jawaban teknis dan komersial untuk membantu operasional bisnis Anda berjalan lancar." />

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Apakah modul operasional esensial COOCA benar-benar gratis?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. COOCA menyediakan modul esensial tanpa biaya bulanan yang mencakup Kasir POS, Manajemen Produk dasar, dan Laporan Rekap Penjualan Harian untuk membantu UMKM Indonesia memulai digitalisasi tanpa beban modal di awal."
      }
    },
    {
      "@type": "Question",
      "name": "Perangkat apa saja yang didukung oleh aplikasi kasir COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA berjalan secara modern berbasis web responsif pada smartphone Android, iPhone, tablet iPad/Android, laptop Windows/macOS, dan komputer desktop PC kasir all-in-one tanpa perlu instalasi aplikasi rumit."
      }
    },
    {
      "@type": "Question",
      "name": "Printer kasir jenis apa saja yang bisa digunakan bersama COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA mendukung hampir semua printer thermal mini standar pasar dengan koneksi Bluetooth (58mm dan 80mm), printer thermal LAN/Ethernet untuk pesanan dapur (Kitchen Order Ticket), serta printer kabel USB untuk kasir komputer PC."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana keamanan data transaksi dan keuangan bisnis saya?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Data Anda terisolasi secara ketat dalam arsitektur multi-tenant. Seluruh transfer data dienkripsi dengan SSL 256-bit dan disimpan di server cloud bersertifikasi dengan pencadangan (backup) berkala. Pemilik bisnis memiliki kepemilikan penuh 100% atas datanya."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah kasir tetap bisa melayani pembeli saat koneksi internet toko mati?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. Modul kasir POS COOCA dirancang dengan kapabilitas offline fallback. Kasir tetap dapat memasukkan pesanan, menghitung kembalian uang tunai, dan mencetak struk belanja. Ketika koneksi internet menyala kembali, transaksi akan tersinkronisasi otomatis ke cloud."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara memindahkan daftar produk dan stok dari Excel lama?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Kami menyediakan template impor Excel resmi. Anda cukup mengisi kolom nama produk, harga jual, harga modal (HPP), dan stok awal, lalu mengunggahnya ke menu Produk COOCA dalam sekali klik."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100 overflow-hidden" 
     x-data="{ 
        activeCategory: 'all',
        searchQuery: '',
        openFaq: null,
        matches(category, title, content) {
            let matchesCat = this.activeCategory === 'all' || this.activeCategory === category;
            if (!this.searchQuery.trim()) return matchesCat;
            let q = this.searchQuery.toLowerCase();
            let matchesSearch = title.toLowerCase().includes(q) || content.toLowerCase().includes(q);
            return matchesCat && matchesSearch;
        }
     }">

    <!-- Background Ambient Gradients -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[450px] bg-gradient-to-b from-indigo-500/10 via-purple-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    <!-- 1. HERO SECTION -->
    <section class="pt-28 pb-16 lg:pt-36 lg:pb-20 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-700 dark:text-indigo-400 text-xs font-semibold uppercase tracking-wider mb-6">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>Pusat Bantuan & Tanya Jawab</span>
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15] mb-6">
                Semua Jawaban untuk Menjalankan Bisnis Tanpa Keraguan
            </h1>

            <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed mb-8 max-w-2xl mx-auto">
                Temukan penjelasan transparan mengenai biaya, dukungan printer thermal, keamanan data bisnis, hingga cara kerja saat internet toko Anda terputus.
            </p>

            <!-- Search Input Bar -->
            <div class="max-w-xl mx-auto relative mb-6">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Ketik kata kunci: 'printer bluetooth', 'offline', 'biaya', 'excel'..." 
                    class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm text-sm transition-all"
                />
            </div>

            <!-- Category Pills Bar -->
            <div class="flex flex-wrap items-center justify-center gap-2">
                <button 
                    @click="activeCategory = 'all'" 
                    :class="activeCategory === 'all' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Semua Pertanyaan
                </button>
                <button 
                    @click="activeCategory = 'pricing'" 
                    :class="activeCategory === 'pricing' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Biaya & Lisensi
                </button>
                <button 
                    @click="activeCategory = 'hardware'" 
                    :class="activeCategory === 'hardware' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Hardware & Printer
                </button>
                <button 
                    @click="activeCategory = 'security'" 
                    :class="activeCategory === 'security' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Keamanan & Data
                </button>
                <button 
                    @click="activeCategory = 'pos'" 
                    :class="activeCategory === 'pos' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Kasir & Fitur Offline
                </button>
                <button 
                    @click="activeCategory = 'inventory'" 
                    :class="activeCategory === 'inventory' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Stok & Resep BOM
                </button>
                <button 
                    @click="activeCategory = 'migration'" 
                    :class="activeCategory === 'migration' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition-all">
                    Migrasi & Excel
                </button>
            </div>
        </div>
    </section>

    <!-- 2. FAQ ACCORDION SECTION -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            
            <!-- Item 1: Pricing -->
            <div x-show="matches('pricing', 'Apakah modul operasional esensial COOCA benar-benar gratis?', 'COOCA menyediakan modul esensial tanpa biaya bulanan tersembunyi yang mencakup modul Kasir (POS), Manajemen Produk dasar, dan Laporan Rekap Penjualan Harian.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Apakah modul operasional esensial COOCA benar-benar gratis?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 1 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    Ya, sepenuhnya benar. Visi kami adalah mendigitalkan operasional UMKM Indonesia tanpa hambatan modal awal. Anda dapat menggunakan modul Kasir POS, input katalog produk, pencatatan harga beli (HPP), dan rekonsiliasi kasir harian selamanya tanpa biaya tersembunyi atau masa kedaluwarsa uji coba.
                </div>
            </div>

            <!-- Item 2: Pricing upgrade -->
            <div x-show="matches('pricing', 'Kapan saya perlu beralih ke paket langganan berbayar?', 'Upgrade hanya diperlukan saat bisnis Anda membutuhkan fitur lanjutan seperti integrasi multi-cabang terpusat')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Kapan bisnis saya perlu beralih ke paket berbayar?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 2 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    Anda hanya perlu upgrade jika skala bisnis Anda berkembang membutuhkan otomasi lebih tinggi, seperti:
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Pengelolaan lebih dari satu cabang fisik secara sentral dari satu akun owner.</li>
                        <li>Integrasi WhatsApp Gateway untuk kirim struk digital & penagihan kasbon otomatis.</li>
                        <li>Resep produksi multi-level (Bill of Materials) untuk F&B atau manufaktur konveksi.</li>
                        <li>Pengaturan hak akses bertingkat untuk puluhan kasir, supervisor, dan staf gudang.</li>
                    </ul>
                </div>
            </div>

            <!-- Item 3: Hardware Devices -->
            <div x-show="matches('hardware', 'Perangkat apa saja yang didukung oleh COOCA?', 'COOCA berjalan berbasis web modern di smartphone Android iOS, tablet kasir, iPad, laptop, dan komputer PC.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Perangkat apa saja yang didukung untuk kasir dan dashboard?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 3 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    COOCA dibangun menggunakan teknologi web app modern (PWA-ready). Anda tidak perlu membeli mesin kasir POS khusus yang mahal seharga belasan juta rupiah. Anda dapat menggunakan:
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Tablet Android (Samsung, Xiaomi, Advan, dll) atau Apple iPad untuk kasir meja.</li>
                        <li>Smartphone Android atau iPhone milik kasir untuk jualan keliling atau event bazar.</li>
                        <li>Laptop atau PC Windows/Macintosh apa pun menggunakan peramban Google Chrome atau Safari.</li>
                    </ul>
                </div>
            </div>

            <!-- Item 4: Hardware Printers -->
            <div x-show="matches('hardware', 'Printer thermal apa saja yang didukung?', 'COOCA mendukung printer thermal struk Bluetooth 58mm 80mm LAN Ethernet dan USB.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 4 ? null : 4" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Bagaimana kompatibilitas dengan printer thermal dan laci uang (cash drawer)?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 4 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    COOCA mendukung protokol standar industri ESC/POS yang digunakan oleh 99% printer thermal di pasaran:
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li><strong>Printer Bluetooth Portable (58mm):</strong> Merk Panda, VSC, Iware, Eppos, RPP02N, dll.</li>
                        <li><strong>Printer Struk Meja (80mm Auto-Cutter):</strong> Merk Epson TM-T82, Xprinter, Matrix Point.</li>
                        <li><strong>Printer Dapur LAN/KOT:</strong> Untuk cetak pesanan langsung ke koki dapur tanpa kabel USB.</li>
                        <li><strong>Cash Drawer (RJ11):</strong> Terhubung ke printer kasir dan otomatis terbuka saat transaksi tunai selesai dicetak.</li>
                    </ul>
                </div>
            </div>

            <!-- Item 5: Security -->
            <div x-show="matches('security', 'Bagaimana keamanan data bisnis saya?', 'Data Anda terisolasi dengan proteksi tenant ketat, dienkripsi saat transit dan di server cloud bersertifikasi.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 5 ? null : 5" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Apakah data keuangan dan resep bisnis saya aman dan terlindungi?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 5 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 5" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    Keamanan data adalah prioritas tertinggi arsitektur COOCA. Kami menerapkan isolasi multi-tenant ketat pada tingkat database, sehingga bisnis lain tidak akan pernah bisa mengakses data stok, supplier, maupun resep Anda. Seluruh transmisi dienkripsi dengan SSL TLS 1.3 standar perbankan dan pencadangan database dilakukan secara otomatis harian.
                </div>
            </div>

            <!-- Item 6: POS Offline Mode -->
            <div x-show="matches('pos', 'Apakah bisa jualan saat internet mati?', 'Ya, modul kasir POS dirancang memiliki kapabilitas offline ringan untuk tetap mencetak struk belanjaan.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 6 ? null : 6" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Bagaimana jika koneksi internet di toko fisik tiba-tiba mati saat jam sibuk?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 6 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 6" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    Jangan khawatir, antrean toko Anda tidak akan terhenti. Modul POS kasir COOCA dilengkapi mode offline lokal di peramban. Kasir tetap dapat mencari produk, memindai barcode, memasukkan nominal pembayaran tunai, dan mencetak struk belanja. Begitu Wi-Fi atau paket data kasir tersambung kembali, seluruh antrean transaksi akan otomatis sinkron ke server pusat.
                </div>
            </div>

            <!-- Item 7: Inventory & Recipe BOM -->
            <div x-show="matches('inventory', 'Bagaimana COOCA memotong stok untuk bisnis makanan & minuman (F&B)?', 'Melalui fitur Bill of Materials BOM Resep Otomatis. Stok bahan baku terpotong per gram saat menu terjual.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 7 ? null : 7" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                        Bagaimana sistem memotong stok bahan baku pada usaha kafe atau restoran?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 7 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 7" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    COOCA menggunakan fitur Bill of Materials (BOM) Resep Otomatis. Anda cukup memasukkan formula racikan sekali saja di awal (contoh: 1 porsi Kopi Latte = 18 gr Espresso Beans + 150 ml Fresh Milk + 1 pc Paper Cup). Setiap kali kasir menekan tombol bayar untuk menu tersebut, stok ketiga bahan baku di gudang otomatis terpotong secara riil. Anda bisa mensimulasikan perhitungan HPP di <a href="{{ route('kalkulator.hpp') }}" class="text-indigo-600 dark:text-indigo-400 underline font-semibold">Kalkulator HPP COOCA</a>.
                </div>
            </div>

            <!-- Item 8: Migration & Excel -->
            <div x-show="matches('migration', 'Bisakah memindahkan data dari Excel lama?', 'Bisa! Kami menyediakan template impor Excel untuk produk, stok, dan data pelanggan hanya dalam sekali klik.')"
                 class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm transition-all">
                <button @click="openFaq = openFaq === 8 ? null : 8" class="w-full py-4 px-6 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-base">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        Bisakah saya memindahkan ribuan data produk dari catatan Excel lama?
                    </span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 flex-shrink-0" :class="openFaq === 8 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="openFaq === 8" x-collapse class="px-6 pb-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80 pt-4">
                    Tentu saja bisa. Anda tidak perlu mengetik ulang produk satu per satu. Di COOCA telah disediakan fitur Impor Excel massal. Anda cukup mengunduh template spreadsheet resmi, menempelkan daftar SKU barang, harga modal, harga jual, dan stok gudang Anda, lalu mengunggahnya. Sistem akan memvalidasi dan memasukkan ribuan data dalam hitungan detik. Kunjungi halaman <a href="{{ route('template.index') }}" class="text-indigo-600 dark:text-indigo-400 underline font-semibold">Template Operasional</a> untuk mengunduh template resmi.
                </div>
            </div>

        </div>
    </section>

    <!-- 3. ESCALATION SUPPORT BENTO -->
    <section class="py-16 bg-white dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 tracking-wider uppercase">Pusat Eskalasi Dukungan</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                    Pertanyaan Anda Belum Terjawab di Sini?
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                    Tim dukungan spesialis COOCA siap membantu menjawab kendala teknis dan mendampingi alur digitalisasi bisnis Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <!-- Card 1: WhatsApp Hotline -->
                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">WhatsApp Fast Support</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Konsultasi langsung dengan tim teknis kami setiap hari kerja pukul 08.00 - 21.00 WIB.</p>
                    </div>
                    <a href="https://wa.me/6281222222222?text=Halo%20Tim%20COOCA,%20saya%20ingin%20konsultasi%20mengenai%20implementasi%20sistem" target="_blank" rel="noopener" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs text-center transition-all">
                        Hubungi via WhatsApp
                    </a>
                </div>

                <!-- Card 2: Visual Guides -->
                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Panduan & SOP Kasir</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Pelajari petunjuk teknis langkah demi langkah mulai dari pairing printer hingga opname stok.</p>
                    </div>
                    <a href="{{ route('public.resources.guides') }}" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs text-center transition-all">
                        Buka Panduan Operasional
                    </a>
                </div>

                <!-- Card 3: Interactive Demo -->
                <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Demonstrasi Interaktif</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Uji coba simulasi kasir POS langsung di peramban Anda tanpa perlu mendaftar akun terlebih dahulu.</p>
                    </div>
                    <a href="{{ route('public.demo') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 font-semibold text-xs text-center transition-all">
                        Coba Demo Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. FINAL CONVERSION CTA -->
    <section class="py-16 bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
                Mulai Digitalisasi Bisnis Anda Hari Ini
            </h2>
            <p class="text-slate-300 max-w-2xl mx-auto text-base sm:text-lg mb-8 leading-relaxed">
                Daftar sekarang dan nikmati modul kasir esensial gratis tanpa batasan waktu. Kelola transaksi, stok barang, dan rekap keuangan dalam satu sentuhan.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all text-center">
                    Buat Akun Bisnis Gratis
                </a>
                <a href="{{ route('public.resources.case-studies') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-slate-200 font-semibold text-sm transition-all text-center">
                    Baca Kisah Sukses UMKM
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
