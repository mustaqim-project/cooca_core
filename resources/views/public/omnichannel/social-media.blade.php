@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Media Sosial & Multi-Akun Bisnis | COOCA')
@section('description', 'Kelola seluruh akun Instagram, Facebook, dan TikTok bisnis Anda dari satu dashboard terpadu. Rencanakan kalender konten, jadwalkan publikasi otomatis, dan hubungkan langsung dengan katalog produk toko.')
@section('keywords', 'software manajemen media sosial, aplikasi jadwal posting medsos, kelola multi akun instagram tiktok, social media scheduler bisnis, konten terintegrasi pos')

@push('seo')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Omnichannel Social Media Management",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Platform pengelolaan dan penjadwalan multi-akun media sosial yang terhubung langsung dengan katalog produk dan promosi bisnis.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pengelolaan multi-akun Instagram, Facebook Page, dan media sosial bisnis dari satu layar",
    "Kalender editorial interaktif untuk penjadwalan konten feed, carousel, dan video",
    "Integrasi langsung ke katalog produk dan foto inventori COOCA",
    "Alur kerja review draf konten bersama tim kreator sebelum diterbitkan",
    "Pelacakan jangkauan interaksi dan konversi klik ke link pesanan toko"
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
      "name": "Platform media sosial apa saja yang didukung oleh modul COOCA Social Media?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA dirancang untuk menghubungkan akun profesional Instagram Business, Facebook Pages, serta perencanaan konten untuk TikTok dan YouTube Shorts melalui API resmi yang aman."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah staf konten kreator kami memerlukan password utama akun media sosial bisnis?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak perlu. Cukup hubungkan akun sekali saja oleh Owner. Staf konten hanya menyusun draf postingan di dalam COOCA tanpa pernah mengetahui username dan password akun media sosial resmi perusahaan, sehingga privasi dan keamanan akun bisnis Anda tetap terlindungi."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana konten di media sosial bisa langsung terhubung dengan produk di toko saya?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Anda dapat langsung memilih item dari modul Inventori & POS saat menyusun postingan. Sistem otomatis menyematkan detail nama barang, harga resmi, serta link katalog digital untuk memudahkan audiens langsung melakukan pemesanan."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah bisa menjadwalkan konten postingan untuk sebulan ke depan sekaligus?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mengatur tanggal dan jam tayang untuk puluhan postingan sekaligus melalui tampilan kalender drag-and-drop, sehingga waktu Anda tidak tersita untuk mengunggah konten secara manual setiap hari."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-pink-500/10 via-rose-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><span class="text-neutral-500 dark:text-neutral-400">Omnichannel</span></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Manajemen Media Sosial</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-pink-50 dark:bg-pink-950/60 border border-pink-200/60 dark:border-pink-800/40 text-pink-700 dark:text-pink-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                    <span>Omnichannel Social Media & Product Showcase</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Kelola Seluruh Kanal Media Sosial Bisnis <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-600 via-rose-600 to-indigo-600">Dari Satu Ruang Kerja Terpadu</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Hentikan repotnya berganti-ganti akun di smartphone. Jadwalkan konten promosi ke Instagram, Facebook, dan kanal sosial lainnya, sinkronkan langsung dengan katalog produk toko Anda, dan ubah pengikut media sosial menjadi pembeli nyata.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Demo Media Sosial</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.content.calendar') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Lihat Kalender Konten</span>
                    </a>
                </div>

                {{-- Key Trust Specs --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Multi-Akun</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">IG, FB, & TikTok</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Katalog Toko</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Sync Stok Langsung</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Keamanan Sandi</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Tanpa Bagi Password</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Social Media Multi-Channel Cockpit UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Channel Header Selector --}}
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-pink-500/20 text-pink-400">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="font-bold text-neutral-200">Kanal Media Sosial Terhubung</div>
                                <div class="text-[10px] text-neutral-500">3 Akun Aktif • Status: Terjadwal</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[11px] text-neutral-300 font-medium">Sync Normal</span>
                        </div>
                    </div>

                    {{-- Connected Accounts Strip --}}
                    <div class="grid grid-cols-3 gap-2 my-3 text-xs">
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 flex items-center justify-center text-white text-[11px] font-bold">
                                IG
                            </div>
                            <div class="truncate">
                                <div class="font-semibold text-white truncate text-[11px]">@kopiseduh</div>
                                <div class="text-[9px] text-neutral-500">12.4k Followers</div>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center text-white text-[11px] font-bold">
                                FB
                            </div>
                            <div class="truncate">
                                <div class="font-semibold text-white truncate text-[11px]">Kopi Seduh ID</div>
                                <div class="text-[9px] text-neutral-500">8.2k Likes</div>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-neutral-800 border border-neutral-700 flex items-center justify-center text-white text-[11px] font-bold">
                                TT
                            </div>
                            <div class="truncate">
                                <div class="font-semibold text-white truncate text-[11px]">@kopiseduh.co</div>
                                <div class="text-[9px] text-neutral-500">24.5k Views</div>
                            </div>
                        </div>
                    </div>

                    {{-- Next Scheduled Post Preview Card --}}
                    <div class="p-3 rounded-xl bg-neutral-950/90 border border-neutral-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-mono text-pink-400 font-semibold flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i> Tayang Hari Ini • 15:30 WIB
                            </span>
                            <span class="px-2 py-0.5 rounded bg-neutral-800 text-[10px] text-neutral-300">Instagram & Facebook</span>
                        </div>

                        <div class="flex gap-3 items-center pt-1">
                            <div class="w-16 h-16 rounded-xl bg-neutral-800 border border-neutral-700 flex items-center justify-center text-neutral-500 shrink-0">
                                <i data-lucide="image" class="w-6 h-6 text-neutral-400"></i>
                            </div>
                            <div class="space-y-1 text-left">
                                <div class="font-semibold text-white text-[11px]">Promo Spesial Akhir Pekan: Buy 1 Get 1 Kopi Susu Aren</div>
                                <div class="text-[10px] text-neutral-400 line-clamp-2">
                                    "Ajak teman terbaikmu mampir ke outlet Sudirman & Senopati! Dapatkan promo spesial cukup dengan tunjukkan postingan ini..."
                                </div>
                                <div class="flex items-center gap-2 pt-0.5 text-[9px] text-emerald-400 font-mono">
                                    <span>Terkait Produk: SKU-KOP-01</span>
                                    <span>•</span>
                                    <span>Link Order Otomatis Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Performance Metric Summary --}}
                    <div class="mt-3 p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-4 h-4 text-emerald-400"></i>
                            <div>
                                <span class="text-white font-medium text-[11px]">Konversi Penjualan Medsos Minggu Ini:</span>
                                <span class="text-neutral-400 text-[10px]"> 148 Klik Link Toko • 32 Order POS</span>
                            </div>
                        </div>
                        <a href="{{ route('public.content.analytics') }}" class="text-pink-400 hover:underline text-[10px] font-semibold">Analitik Konten →</a>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Kerumitan Mengurus Medsos Bisnis Secara Terpisah --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-3">Hambatan Pemasaran Konten</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Mengapa Mengelola Medsos Bisnis Sering Menghabiskan Waktu Tanpa Menghasilkan Penjualan?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Banyak bisnis rajin mengunggah konten, namun materi postingan terlepas dari ketersediaan stok fisik di toko sehingga pelanggan kecewa saat ingin membeli.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="key-round" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Risiko Berbagi Password Akun</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Memberikan login Instagram atau TikTok resmi ke staf admin lepas (freelancer). Risiko akun terkunci karena verifikasi OTP atau terbawa saat staf berhenti kerja.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="calendar-x-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Jadwal Posting Bolong-Bolong</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Hari ini posting lima kali, lalu minggu berikutnya akun mati suri karena staf sibuk melayani pembeli di toko. Algoritma media sosial menurunkan jangkauan akun Anda.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-950/50 border border-pink-200 dark:border-pink-900/50 flex items-center justify-center text-pink-600 dark:text-pink-400">
                        <i data-lucide="unlink" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Konten Tidak Nyambung dengan Stok</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Admin mempromosikan produk tertentu hingga viral, namun ternyata stok di gudang outlet sudah kosong dua hari lalu. Momen penjualan emas hilang sia-sia.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE SOCIAL MEDIA CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-3">Fitur Media Sosial COOCA</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Publikasi Konsisten yang Terhubung dengan Operasional Toko
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Dirancang khusus untuk membantu pelaku bisnis UMKM membangun kehadiran digital yang profesional tanpa perlu merekrut agensi mahal.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Multi-Channel Publisher (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-pink-100 dark:bg-pink-950 flex items-center justify-center text-pink-600 dark:text-pink-400">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Publikasi Multi-Kanal Sekaligus
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Tulis teks promosi sekali, sesuaikan format visual per kanal, dan jadwalkan penayangan serentak ke akun Instagram bisnis dan Facebook Page Anda. Hemat waktu operasional hingga 80% setiap minggu.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Kanal Terhubung:</span>
                    <span class="text-pink-600 dark:text-pink-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i> API Resmi Meta & Integrasi Terverifikasi
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Sinkronisasi Katalog Produk Toko (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Tautkan Produk & Stok Toko
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Saat membuat postingan, pilih produk langsung dari modul Inventori COOCA. Sistem menyertakan harga terkini, ketersediaan stok cabang, dan tautan belanja yang siap diklik audiens.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Perlindungan Overselling</span>
                    <span class="text-purple-500 font-bold">Stok Habis = Link Nonaktif</span>
                </div>
            </div>

            {{-- Bento Card 3: Kalender Editorial Visual (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Kalender Editorial Interaktif</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Lihat rencana postingan bulanan dalam tampilan visual. Geser jadwal konten ke hari lain dengan drag-and-drop jika ada perubahan agenda toko.
                </p>
            </div>

            {{-- Bento Card 4: Persetujuan Draf Tim (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="check-check" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Alur Review Draf Konten</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Staf konten mengunggah draf teks dan gambar. Konten baru akan tayang setelah disetujui owner, memastikan pesan brand selalu sesuai standar.
                </p>
            </div>

            {{-- Bento Card 5: Analitik Klik & Konversi (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="mouse-pointer-click" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pelacakan Konversi Riil</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Bukan hanya menghitung jumlah likes. Ketahui postingan mana yang mendatangkan klik pesanan paling banyak ke toko Anda.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Dari Postingan Medsos Menjadi Penjualan Nyata --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 text-pink-400 text-xs font-semibold mb-3 border border-pink-500/30">
                    <span>Siklus Pemasaran Hingga Transaksi</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Menghubungkan Media Sosial dengan Kasir & Gudang
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Bagaimana konten digital Anda berinteraksi dengan seluruh ekosistem bisnis COOCA.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-600/30 text-pink-400 flex items-center justify-center font-bold text-xs border border-pink-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Pilih Produk Siap Jual</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Pilih produk unggulan yang stok fisiknya masih banyak di gudang cabang untuk dijadikan materi promosi konten media sosial minggu ini.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Jadwal Publikasi Otomatis</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Atur jadwal tayang di jam santai audiens (misal 12:00 atau 19:30). Sistem otomatis mengunggah materi ke Instagram dan Facebook tanpa campur tangan staf.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Pesanan Masuk Terpusat</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Audiens mengklik link produk di bio/postingan untuk memesan atau datang ke toko fisik. Transaksi langsung masuk ke modul Order terpadu.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Stok Terpotong & Laporan Rapi</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Stok gudang cabang berkurang, omzet tercatat di modul Finance, dan laporan menunjukkan berapa rupiah keuntungan yang dihasilkan dari postingan tersebut.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Pengelolaan Medsos</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Platform media sosial apa saja yang didukung oleh modul COOCA Social Media?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA dirancang untuk menghubungkan akun profesional Instagram Business, Facebook Pages, serta perencanaan konten untuk TikTok dan YouTube Shorts melalui API resmi yang aman.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah staf konten kreator kami memerlukan password utama akun media sosial bisnis?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Tidak perlu. Cukup hubungkan akun sekali saja oleh Owner. Staf konten hanya menyusun draf postingan di dalam COOCA tanpa pernah mengetahui username dan password akun media sosial resmi perusahaan, sehingga privasi dan keamanan akun bisnis Anda tetap terlindungi.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana konten di media sosial bisa langsung terhubung dengan produk di toko saya?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Anda dapat langsung memilih item dari modul Inventori & POS saat menyusun postingan. Sistem otomatis menyematkan detail nama barang, harga resmi, serta link katalog digital untuk memudahkan audiens langsung melakukan pemesanan.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah bisa menjadwalkan konten postingan untuk sebulan ke depan sekaligus?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Bisa. Anda dapat mengatur tanggal dan jam tayang untuk puluhan postingan sekaligus melalui tampilan kalender drag-and-drop, sehingga waktu Anda tidak tersita untuk mengunggah konten secara manual setiap hari.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Pemasaran & Konten</p>
            </div>
            <a href="{{ route('public.content.creation') }}" class="text-xs sm:text-sm font-semibold text-pink-600 dark:text-pink-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Pelajari Content Automation</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.content.calendar') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">Kalender Konten</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Susun jadwal kampanye promosi dan tema postingan mingguan.</p>
            </a>

            <a href="{{ route('public.omnichannel.whatsapp') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">WhatsApp Komunikasi</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kirim pesan invoice digital dan follow-up pesanan langsung ke chat.</p>
            </a>

            <a href="{{ route('public.omnichannel.orders') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">Order Omnichannel</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pusat penanganan pesanan yang masuk dari seluruh kanal penjualan.</p>
            </a>

            <a href="{{ route('public.erp.inventory') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">Katalog & Stok Toko</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Sumber produk resmi dan harga terkini untuk materi promosi.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Mulai Publikasikan Konten Bisnis yang Menghasilkan Penjualan
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Satukan akun media sosial Anda dengan sistem operasional toko dan rasakan kemudahan membangun kehadiran brand yang konsisten bersama COOCA.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-pink-600 hover:bg-pink-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Media Sosial
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Kebutuhan Pemasaran
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
