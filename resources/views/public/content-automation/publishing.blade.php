@extends('layouts.public_marketing')

@section('title', 'Software Publikasi Otomatis Media Sosial & Antrean Tayang | COOCA')
@section('description', 'Otomasi publikasi konten media sosial bisnis tanpa perlu unggah manual. Antrean penayangan otomatis ke Instagram dan Facebook, sinkronisasi waktu terjadwal, dan laporan status penerbitan real-time.')
@section('keywords', 'software publikasi otomatis media sosial, auto publish instagram facebook, aplikasi antrean posting medsos, penjadwalan konten otomatis, social media dispatch engine')

@push('seo')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Automated Content Publishing",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Mesin penerbitan konten otomatis multi-kanal berbasis cloud yang mengeksekusi penayangan materi promosi sesuai jadwal.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Eksekusi penayangan otomatis cloud-based tanpa perlu konfirmasi manual di HP",
    "Distribusi serentak ke akun Instagram Business dan Facebook Page",
    "Antrean konten cerdas (Smart Queue) dengan pengaturan jeda waktu optimal",
    "Mekanisme auto-retry dan pencatatan log status penayangan yang transparan",
    "Penggunaan token resmi Meta Graph API yang aman dari pemblokiran akun"
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
      "name": "Apakah postingan tetap tayang otomatis jika komputer atau HP saya dalam keadaan mati?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, 100% tetap tayang. Antrean publikasi COOCA berjalan di server cloud kami yang beroperasi 24 jam sehari. Begitu jadwal diatur, sistem akan mengeksekusi pengunggahan ke media sosial tepat waktu tanpa memerlukan perangkat Anda tetap menyala."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah penggunaan auto publish ini aman dan tidak melanggar aturan Instagram atau Facebook?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat aman. COOCA menggunakan integrasi resmi Meta Graph API untuk akun profesional. Kami tidak menggunakan bot ilegal atau scraping rahasia yang melanggar ketentuan layanan, sehingga akun bisnis Anda sepenuhnya aman dari shadowban atau suspend."
      }
    },
    {
      "@type": "Question",
      "name": "Apa yang terjadi jika jaringan internet atau API media sosial mengalami gangguan saat jadwal tayang?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA dilengkapi dengan mesin Auto-Retry Cerdas. Jika penayangan gagal karena server tujuan sedang sibuk, sistem akan mencoba ulang beberapa menit kemudian dan mengirimkan laporan kendala kepada admin jika membutuhkan tindakan lebih lanjut."
      }
    },
    {
      "@type": "Question",
      "name": "Format media apa saja yang didukung untuk publikasi otomatis?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sistem mendukung postingan gambar tunggal (JPG/PNG), multi-foto carousel, dan video pendek (Reels/Video Feed) dengan optimasi kompresi otomatis agar kualitas visual tetap tajam di feed audiens."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-blue-500/10 via-purple-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><span class="text-neutral-500 dark:text-neutral-400">Content Automation</span></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Publikasi Multi-Kanal Otomatis</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200/60 dark:border-blue-800/40 text-blue-700 dark:text-blue-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Cloud Publishing Queue & Multi-Platform Dispatch</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Tayangkan Konten Promosi Tepat Waktu <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-500">Tanpa Perlu Unggah Manual</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Lupakan alarm pengingat jam posting yang mengganggu aktivitas Anda. Mesin publikasi berbasis cloud COOCA mengeksekusi penayangan teks, foto, dan video promosi ke Instagram dan Facebook secara otomatis dan presisi sesuai menit yang telah Anda jadwalkan.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Modul Publikasi</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.content.analytics') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Lihat Analitik Jangkauan</span>
                    </a>
                </div>

                {{-- Key Trust Specs --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Metode Publikasi</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Cloud Auto-Post</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Keandalan API</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Meta API Resmi</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Penanganan Error</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Auto-Retry Cerdas</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live Publishing Engine Feed UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Header Engine Status --}}
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-blue-500/20 text-blue-400">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="font-bold text-neutral-200">Mesin Antrean Publikasi Aktif</div>
                                <div class="text-[10px] text-neutral-500">Kanal: Instagram Business & Facebook Page</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">Engine: Siaga 24/7</span>
                    </div>

                    {{-- Live Queue Dispatch Items --}}
                    <div class="space-y-2 my-3 text-xs text-left">
                        
                        {{-- Post 1: Success Live --}}
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-950/60 border border-emerald-800/50 flex items-center justify-center text-emerald-400 shrink-0">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-white text-[11px]">Promo Kopi Susu Aren Pagi (Feed IG + FB)</div>
                                    <div class="text-[10px] text-neutral-500">Tayang: 08:30 WIB • Sukses Diterbitkan via API</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 text-[10px] font-mono">Tayang</span>
                        </div>

                        {{-- Post 2: In Queue Today --}}
                        <div class="p-2.5 rounded-xl bg-neutral-950/90 border border-blue-500/40 ring-1 ring-blue-500/30 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-950/60 border border-blue-800/50 flex items-center justify-center text-blue-400 shrink-0">
                                    <i data-lucide="clock" class="w-4 h-4 animate-spin"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-white text-[11px]">Croissant Butter Beli 2 Gratis 1 (Story + Feed)</div>
                                    <div class="text-[10px] text-blue-400 font-mono">Jadwal: 15:30 WIB Hari Ini • Antrean Terkunci</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-blue-950 text-blue-400 text-[10px] font-mono font-bold">Siap Tayang</span>
                        </div>

                        {{-- Post 3: Scheduled Future --}}
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-neutral-800 flex items-center justify-center text-neutral-400 shrink-0">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-neutral-300 text-[11px]">Voucher Payday Spesial Akhir Bulan (Member WA)</div>
                                    <div class="text-[10px] text-neutral-500">Jadwal: Jumat, 25 Sep • 19:00 WIB</div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-neutral-800 text-neutral-400 text-[10px] font-mono">Terjadwal</span>
                        </div>

                    </div>

                    {{-- Technical Resilience Footer --}}
                    <div class="pt-2 border-t border-neutral-800 flex items-center justify-between text-[11px] text-neutral-400">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-blue-400"></i>
                            Koneksi Resmi Token Meta Graph API: Aman
                        </span>
                        <a href="{{ route('public.demo') }}" class="text-blue-400 hover:underline font-medium">Buka Log Dispatch →</a>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Kerugian Mengunggah Manual Setiap Hari --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-3">Tantangan Eksekusi Manual</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Apakah Waktu Berharga Anda Masih Tersita untuk Mengunggah Konten Manual?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Mengandalkan ingatan staf untuk memposting tepat waktu saat jam sibuk toko sering berakhir dengan keterlambatan tayang dan hilangnya momentum promosi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="clock-alert" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Terlambat Jam Tayang Emas</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Materi promo makan siang baru terunggah jam 14:00 karena admin kasir sibuk melayani antrean pembeli fisik. Momentum penawaran lewat sia-sia.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="smartphone-nfc" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Terkunci Notifikasi HP Manual</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Aplikasi scheduler lain hanya mengirimkan alarm ke smartphone dan Anda tetap harus mengklik posting manual di Instagram satu per satu.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Akun Diblokir Karena Bot Ilegal</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Menggunakan aplikasi pihak ketiga yang tidak resmi (scraping) yang berisiko membuat akun Instagram bisnis Anda diblokir permanen oleh Meta.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE PUBLISHING CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-3">Kemampuan Mesin Publikasi COOCA</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Ketepatan Penayangan Tanpa Kompromi
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Dirancang untuk memberikan ketenangan pikiran bagi pemilik bisnis dalam mengotomasi pemasaran digital.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Cloud Auto-Post Engine (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="cloud-lightning" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Publikasi Otomatis Berbasis Cloud (Zero Click)
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Cukup jadwalkan tanggal dan jam tayang sekali saja. Server cloud COOCA mengeksekusi penayangan materi gambar, video reels, dan teks promosi langsung ke media sosial tanpa membutuhkan sentuhan tombol di smartphone Anda saat jam tayang tiba.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Keandalan Penayangan:</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i> Tetap tayang saat smartphone dalam keadaan mati
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Auto-Retry & Error Handling (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Mekanisme Auto-Retry Cerdas
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Jika server media sosial mengalami kelambatan sesaat, sistem kami secara otomatis melakukan percobaan ulang berkala untuk memastikan konten Anda tidak hilang dari antrean.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Protokol Keamanan</span>
                    <span class="text-indigo-500 font-bold">Log Status Real-Time</span>
                </div>
            </div>

            {{-- Bento Card 3: Distribusi Serentak Lintas Platform (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Tayang Serentak Multi-Kanal</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Satu klik jadwalkan ke akun Instagram bisnis dan Facebook Page resmi sekaligus, menjaga pesan promosi brand selalu seragam di seluruh media sosial.
                </p>
            </div>

            {{-- Bento Card 4: Optimasi Rasio Format Visual (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                    <i data-lucide="aspect-ratio" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Format Gambar Presisi</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Dukungan rasio gambar persegi (1:1), portrait (4:5), dan video vertikal (9:16) agar materi promosi tampil tajam dan proporsional di feed pengguna.
                </p>
            </div>

            {{-- Bento Card 5: Kepatuhan API Resmi (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">100% API Resmi Meta</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Bekerja sepenuhnya melalui integrasi API resmi yang disetujui Meta, menjaga integritas dan keamanan akun bisnis Anda dalam jangka panjang.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Dari Antrean Hingga Terbit di Feed --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-400 text-xs font-semibold mb-3 border border-blue-500/30">
                    <span>Siklus Eksekusi Publikasi Otomatis</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Bagaimana Mesin Publishing COOCA Bekerja
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Setiap detik waktu tayang dikawal oleh infrastruktur cloud yang andal.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Jadwal Tervalidasi</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Materi konten yang telah disetujui owner di Kalender Konten masuk ke antrean mesin publishing dengan status terkunci.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Trigger Waktu Tiba</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Tepat pada menit yang ditentukan, server cloud mengaktifkan pengiriman data teks dan media ke endpoint API resmi platform.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Sukses Tayang di Feed</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Konten langsung tampil di feed Instagram dan Facebook audiens lengkap dengan link produk menuju toko Anda.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Pencatatan Analitik</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Sistem mencatat URL permanen postingan dan mulai menghitung interaksi serta klik pesanan di modul Analitik Konten.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Publikasi Otomatis</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah postingan tetap tayang otomatis jika komputer atau HP saya dalam keadaan mati?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Ya, 100% tetap tayang. Antrean publikasi COOCA berjalan di server cloud kami yang beroperasi 24 jam sehari. Begitu jadwal diatur, sistem akan mengeksekusi pengunggahan ke media sosial tepat waktu tanpa memerlukan perangkat Anda tetap menyala.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah penggunaan auto publish ini aman dan tidak melanggar aturan Instagram atau Facebook?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Sangat aman. COOCA menggunakan integrasi resmi Meta Graph API untuk akun profesional. Kami tidak menggunakan bot ilegal atau scraping rahasia yang melanggar ketentuan layanan, sehingga akun bisnis Anda sepenuhnya aman dari shadowban atau suspend.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apa yang terjadi jika jaringan internet atau API media sosial mengalami gangguan saat jadwal tayang?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA dilengkapi dengan mesin Auto-Retry Cerdas. Jika penayangan gagal karena server tujuan sedang sibuk, sistem akan mencoba ulang beberapa menit kemudian dan mengirimkan laporan kendala kepada admin jika membutuhkan tindakan lebih lanjut.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Format media apa saja yang didukung untuk publikasi otomatis?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Sistem mendukung postingan gambar tunggal (JPG/PNG), multi-foto carousel, dan video pendek (Reels/Video Feed) dengan optimasi kompresi otomatis agar kualitas visual tetap tajam di feed audiens.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Otomasi Konten</p>
            </div>
            <a href="{{ route('public.content.analytics') }}" class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lanjut ke Analitik Konten</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.content.calendar') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Kalender Konten</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Perencanaan jadwal tayang bulanan dengan antarmuka visual.</p>
            </a>

            <a href="{{ route('public.content.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Analitik Konten</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Ukur efektivitas jangkauan postingan dan konversi ke pesanan toko.</p>
            </a>

            <a href="{{ route('public.omnichannel.social-media') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Koneksi Akun Sosial</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kelola integrasi akun Instagram dan Facebook bisnis terpadu.</p>
            </a>

            <a href="{{ route('public.content.creation') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-blue-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-blue-600 transition-colors">Studio Kreasi Konten</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Hasilkan draf materi promosi langsung dari katalog produk toko.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Mulai Publikasi Otomatis Tanpa Khawatir Terlewat
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Otomasi jadwal penayangan media sosial bisnis Anda dengan mesin cloud terpercaya dan fokuskan waktu Anda untuk melayani pelanggan bersama COOCA.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Publikasi Otomatis
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Otomasi Konten
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
