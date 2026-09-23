@extends('layouts.public_marketing')

@section('title', 'Software Publikasi Otomatis Media Sosial & Antrean Tayang | COOCA')
@section('description', 'Otomasi publikasi konten media sosial bisnis tanpa perlu unggah manual. Antrean penayangan
    otomatis ke Instagram dan Facebook, sinkronisasi waktu terjadwal, dan laporan status penerbitan real-time.')
@section('keywords', 'software publikasi otomatis media sosial, auto publish instagram facebook, aplikasi antrean
    posting medsos, penjadwalan konten otomatis, social media dispatch engine')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
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
  "@@context": "https://schema.org",
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
                        <li><span class="text-slate-400">Content Automation</span></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li class="text-slate-200 font-semibold" aria-current="page">Publikasi Multi-Kanal Otomatis</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    {{-- Left Column: Copy & Value Proposition --}}
                    <div class="lg:col-span-6 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold tracking-wide">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Cloud Publishing Queue & Multi-Platform Dispatch</span>
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-[1.2] text-balance break-words">
                            Tayangkan Konten Promosi Tepat Waktu <span class="text-[#00C4D8]">Tanpa Perlu Unggah Manual</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                            Lupakan alarm pengingat jam posting yang mengganggu aktivitas Anda. Mesin publikasi berbasis
                            cloud COOCA mengeksekusi penayangan teks, foto, dan video promosi ke Instagram dan Facebook
                            secara otomatis dan presisi sesuai menit yang telah Anda jadwalkan.
                        </p>

                        {{-- Action CTAs --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200">
                                <span>Coba Modul Publikasi</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.content.analytics') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                                <span>Lihat Analitik Jangkauan</span>
                            </a>
                        </div>

                        {{-- Key Trust Specs --}}
                        <div class="pt-4 border-t border-white/10 grid grid-cols-2 sm:grid-cols-3 gap-3.5 sm:gap-4 text-left">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Metode Publikasi</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Cloud Auto-Post</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Keandalan API</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Meta API Resmi</div>
                            </div>
                            <div class="min-w-0 col-span-2 sm:col-span-1">
                                <div class="text-xs text-slate-400 font-medium truncate">Penanganan Error</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Auto-Retry Cerdas</div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Simulated Live Publishing Engine Feed UI --}}
                    <div class="lg:col-span-6">
                        <div
                            class="relative rounded-2xl bg-[#0E1E45]/80 p-4 sm:p-5 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white">

                            {{-- Header Engine Status --}}
                            <div class="flex items-center justify-between gap-2 pb-3 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="p-1.5 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                        <i data-lucide="cpu" class="w-4 h-4"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white truncate">Mesin Antrean Publikasi Aktif</div>
                                        <div class="text-[10px] text-slate-400 truncate">Instagram Business & Facebook Page</div>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold shrink-0">Engine: Siaga 24/7</span>
                            </div>

                            {{-- Live Queue Dispatch Items --}}
                            <div class="space-y-2 my-3 text-xs text-left">

                                {{-- Post 1: Success Live --}}
                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/90 border border-white/10 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-white text-[11px] truncate">Promo Kopi Susu Aren Pagi (Feed IG + FB)</div>
                                            <div class="text-[10px] text-slate-400 truncate">Tayang: 08:30 WIB • Sukses via API</div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono shrink-0">Tayang</span>
                                </div>

                                {{-- Post 2: In Queue Today --}}
                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/95 border border-[#007AFF]/50 ring-1 ring-[#007AFF]/30 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 border border-[#007AFF]/30 flex items-center justify-center text-[#00C4D8] shrink-0">
                                            <i data-lucide="clock" class="w-4 h-4 animate-spin"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-white text-[11px] truncate">Croissant Butter Beli 2 Gratis 1 (Feed)</div>
                                            <div class="text-[10px] text-[#00C4D8] font-mono truncate">Jadwal: 15:30 WIB Hari Ini • Terkunci</div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded bg-[#007AFF]/30 text-[#00C4D8] text-[10px] font-mono font-bold shrink-0">Siap</span>
                                </div>

                                {{-- Post 3: Scheduled Future --}}
                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/90 border border-white/10 flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-slate-400 shrink-0">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-300 text-[11px] truncate">Voucher Payday Spesial (Member WA)</div>
                                            <div class="text-[10px] text-slate-400 truncate">Jadwal: Jumat, 25 Sep • 19:00 WIB</div>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded bg-white/10 text-slate-400 text-[10px] font-mono shrink-0">Terjadwal</span>
                                </div>

                            </div>

                            {{-- Technical Resilience Footer --}}
                            <div
                                class="pt-2 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-400">
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                    <span class="truncate">Koneksi Resmi Token Meta Graph API: Aman</span>
                                </span>
                                <a href="{{ route('public.demo') }}" class="text-[#00C4D8] hover:underline font-medium shrink-0">Buka Log Dispatch →</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerugian Mengunggah Manual Setiap Hari --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-b border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                        Tantangan Eksekusi Manual
                    </h2>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Apakah Waktu Berharga Anda Masih Tersita untuk Mengunggah Konten Manual?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3">
                        Mengandalkan ingatan staf untuk memposting tepat waktu saat jam sibuk toko sering berakhir dengan
                        keterlambatan tayang dan hilangnya momentum promosi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                            <i data-lucide="clock-alert" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Terlambat Jam Tayang Emas</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Materi promo makan siang baru terunggah jam 14:00 karena admin kasir sibuk melayani antrean
                            pembeli fisik. Momentum penawaran lewat sia-sia.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="smartphone-nfc" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Terkunci Notifikasi HP Manual</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Aplikasi scheduler lain hanya mengirimkan alarm ke smartphone dan Anda tetap harus mengklik
                            posting manual di Instagram satu per satu.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Akun Diblokir Karena Bot Ilegal</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Menggunakan aplikasi pihak ketiga yang tidak resmi (scraping) yang berisiko membuat akun
                            Instagram bisnis Anda diblokir permanen oleh Meta.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE PUBLISHING CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                    Kemampuan Mesin Publikasi COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Ketepatan Penayangan Tanpa Kompromi
                </p>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">
                    Dirancang untuk memberikan ketenangan pikiran bagi pemilik bisnis dalam mengotomasi pemasaran digital.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Cloud Auto-Post Engine (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="cloud-lightning" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Publikasi Otomatis Berbasis Cloud (Zero Click)
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Cukup jadwalkan tanggal dan jam tayang sekali saja. Server cloud COOCA mengeksekusi penayangan
                            materi gambar, video reels, dan teks promosi langsung ke media sosial tanpa membutuhkan sentuhan
                            tombol di smartphone Anda saat jam tayang tiba.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">Keandalan Penayangan:</span>
                        <span class="text-[#007AFF] font-semibold flex items-center gap-1 shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Tetap tayang saat HP dalam keadaan mati
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Auto-Retry & Error Handling (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-500">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Mekanisme Auto-Retry Cerdas
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Jika server media sosial mengalami kelambatan sesaat, sistem kami secara otomatis melakukan
                            percobaan ulang berkala untuk memastikan konten Anda tidak hilang dari antrean.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Protokol Keamanan</span>
                        <span class="text-indigo-500 font-bold shrink-0">Log Status Real-Time</span>
                    </div>
                </div>

                {{-- Bento Card 3: Distribusi Serentak Lintas Platform (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tayang Serentak Multi-Kanal</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Satu klik jadwalkan ke akun Instagram bisnis dan Facebook Page resmi sekaligus, menjaga pesan
                        promosi brand selalu seragam di seluruh media sosial.
                    </p>
                </div>

                {{-- Bento Card 4: Optimasi Rasio Format Visual (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-teal-500/10 dark:bg-teal-500/20 flex items-center justify-center text-teal-500">
                        <i data-lucide="aspect-ratio" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Format Gambar Presisi</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Dukungan rasio gambar persegi (1:1), portrait (4:5), dan video vertikal (9:16) agar materi promosi
                        tampil tajam dan proporsional di feed pengguna.
                    </p>
                </div>

                {{-- Bento Card 5: Kepatuhan API Resmi (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-500">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">100% API Resmi Meta</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Bekerja sepenuhnya melalui integrasi API resmi yang disetujui Meta, menjaga integritas dan keamanan
                        akun bisnis Anda dalam jangka panjang.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Dari Antrean Hingga Terbit di Feed --}}
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
                        <span>Siklus Eksekusi Publikasi Otomatis</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                        Bagaimana Mesin Publishing COOCA Bekerja
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3">
                        Setiap detik waktu tayang dikawal oleh infrastruktur cloud yang andal.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Jadwal Tervalidasi</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Materi konten yang telah disetujui owner di Kalender Konten masuk ke antrean mesin publishing
                            dengan status terkunci.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Trigger Waktu Tiba</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Tepat pada menit yang ditentukan, server cloud mengaktifkan pengiriman data teks dan media ke
                            endpoint API resmi platform.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Sukses Tayang di Feed</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Konten langsung tampil di feed Instagram dan Facebook audiens lengkap dengan link produk menuju
                            toko Anda.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Pencatatan Analitik</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Sistem mencatat URL permanen postingan dan mulai menghitung interaksi serta klik pesanan di
                            modul Analitik Konten.
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
                    Tanya Jawab Seputar Publikasi Otomatis
                </p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah postingan tetap tayang otomatis jika komputer atau HP saya dalam keadaan mati?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Ya, 100% tetap tayang. Antrean publikasi COOCA berjalan di server cloud kami yang beroperasi 24 jam
                        sehari. Begitu jadwal diatur, sistem akan mengeksekusi pengunggahan ke media sosial tepat waktu
                        tanpa memerlukan perangkat Anda tetap menyala.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah penggunaan auto publish ini aman dan tidak melanggar aturan Instagram atau
                            Facebook?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Sangat aman. COOCA menggunakan integrasi resmi Meta Graph API untuk akun profesional. Kami tidak
                        menggunakan bot ilegal atau scraping rahasia yang melanggar ketentuan layanan, sehingga akun bisnis
                        Anda sepenuhnya aman dari shadowban atau suspend.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apa yang terjadi jika jaringan internet atau API media sosial mengalami gangguan saat jadwal
                            tayang?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        COOCA dilengkapi dengan mesin Auto-Retry Cerdas. Jika penayangan gagal karena server tujuan sedang
                        sibuk, sistem akan mencoba ulang beberapa menit kemudian dan mengirimkan laporan kendala kepada
                        admin jika membutuhkan tindakan lebih lanjut.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Format media apa saja yang didukung untuk publikasi otomatis?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Sistem mendukung postingan gambar tunggal (JPG/PNG), multi-foto carousel, dan video pendek
                        (Reels/Video Feed) dengan optimasi kompresi otomatis agar kualitas visual tetap tajam di feed
                        audiens.
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
                        Ekosistem Otomasi Konten
                    </p>
                </div>
                <a href="{{ route('public.content.analytics') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lanjut ke Analitik Konten</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.content.calendar') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Kalender Konten
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perencanaan jadwal tayang bulanan dengan
                        antarmuka visual.</p>
                </a>

                <a href="{{ route('public.content.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Konten
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ukur efektivitas jangkauan postingan dan
                        konversi ke pesanan toko.</p>
                </a>

                <a href="{{ route('public.omnichannel.social-media') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Koneksi Akun Sosial
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola integrasi akun Instagram dan Facebook
                        bisnis terpadu.</p>
                </a>

                <a href="{{ route('public.content.creation') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-pink-500/10 dark:bg-pink-500/20 text-pink-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Studio Kreasi Konten
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Hasilkan draf materi promosi langsung dari
                        katalog produk toko.</p>
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
                        Mulai Publikasi Otomatis Tanpa Khawatir Terlewat
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Otomasi jadwal penayangan media sosial bisnis Anda dengan mesin cloud terpercaya dan fokuskan waktu
                        Anda untuk melayani pelanggan bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all">
                            Coba Demo Publikasi Otomatis
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                            Konsultasi Otomasi Konten
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
