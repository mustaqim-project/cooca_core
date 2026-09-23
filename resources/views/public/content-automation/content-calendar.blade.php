@extends('layouts.public_marketing')

@section('title', 'Software Kalender Konten Editorial & Jadwal Promosi Bisnis | COOCA')
@section('description', 'Rencanakan dan pantau seluruh jadwal publikasi konten media sosial bisnis Anda sebulan penuh di
    muka. Kalender editorial visual drag-and-drop terhubung ke Instagram, Facebook, dan WhatsApp.')
@section('keywords', 'software kalender konten, aplikasi jadwal promosi bisnis, editorial calendar medsos, perencanaan
    konten produk, jadwal posting instagram facebook')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Content Calendar & Campaign Planner",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Kalender editorial visual untuk merencanakan, mendistribusikan, dan memantau jadwal tayang konten pemasaran multi-kanal.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Tampilan kalender editorial bulanan dan mingguan interaktif drag-and-drop",
    "Distribusi kanal multi-platform (Instagram, Facebook, dan WhatsApp)",
    "Penyelarasan jadwal promo dengan tanggal gajian dan agenda toko offline",
    "Filter status konten transparan: Draf, Menunggu Approval, dan Terjadwal",
    "Sinkronisasi langsung ke antrean penerbitan otomatis COOCA Publishing"
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
      "name": "Bagaimana cara kerja penjadwalan konten dengan kalender ini?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Anda cukup memilih tanggal dan jam pada kisi kalender, lalu melampirkan draf materi promosi yang telah dibuat di modul Content Creation. Sistem akan menayangkan postingan tersebut secara otomatis tepat pada waktu yang telah ditentukan."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah saya bisa mengubah jadwal tayang jika ada perubahan agenda toko mendadak?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat mudah. Anda cukup menyeret (drag-and-drop) kartu postingan dari tanggal lama ke tanggal baru di tampilan kalender. Jam tayang dan antrean otomatis menyesuaikan tanpa perlu mengetik ulang konten."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah bisa memfilter tampilan kalender hanya untuk satu kanal media sosial tertentu?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat menyaring tampilan kalender per channel—misalnya hanya melihat jadwal postingan Instagram, atau hanya memantau jadwal broadcast promo WhatsApp pelanggan."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana tim saya berkolaborasi menyusun jadwal promosi ini?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tim konten Anda dapat menyusun draf rencana postingan sepanjang bulan. Anda sebagai pemilik bisnis dapat melihat gambaran besar strategi promosi, memberikan masukan pada draf tertentu, dan memastikan tidak ada hari tanpa kehadiran promosi."
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
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1300px] h-[480px] bg-gradient-to-b from-purple-500/10 via-pink-500/5 to-transparent blur-3xl pointer-events-none -z-10">
        </div>

        {{-- Breadcrumb --}}
        <nav class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li><span class="text-neutral-500 dark:text-neutral-400">Content Automation</span></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Kalender Konten
                    Editorial</li>
            </ol>
        </nav>

        {{-- 1. HERO SECTION (2 Columns) --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-50 dark:bg-purple-950/60 border border-purple-200/60 dark:border-purple-800/40 text-purple-700 dark:text-purple-400 text-xs font-semibold tracking-wide">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Editorial Planning & Visual Campaign Grid</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                        Rencanakan Pemasaran Sebulan Penuh <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600">Dalam
                            Satu Tampilan Visual</span>
                    </h1>

                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                        Ketahui persis konten apa yang akan tayang besok, lusa, hingga akhir bulan. Susun tema promosi
                        mingguan, sesuaikan dengan tanggal gajian pembeli, dan geser jadwal tayang secara leluasa dengan
                        kalender editorial interaktif COOCA.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                            <span>Coba Demo Kalender Konten</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.content.publishing') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                            <span>Lihat Alur Publikasi</span>
                        </a>
                    </div>

                    {{-- Key Trust Specs --}}
                    <div
                        class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Rentang Pandang</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Bulanan & Mingguan</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Pengaturan Waktu</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Drag & Drop Cepat</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kanal Tayang</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">IG, FB, & WhatsApp</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live Monthly Editorial Calendar UI --}}
                <div class="lg:col-span-6">
                    <div
                        class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">

                        {{-- Calendar Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-purple-500/20 text-purple-400">
                                    <i data-lucide="calendar-range" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-neutral-200">September 2026 — Jadwal Editorial</div>
                                    <div class="text-[10px] text-neutral-500">24 Konten Terjadwal • 4 Draf Review</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="px-2 py-0.5 rounded bg-neutral-800 text-neutral-300 text-[10px]">Filter: Semua
                                    Kanal</span>
                            </div>
                        </div>

                        {{-- Calendar Days Grid Simulation --}}
                        <div class="grid grid-cols-3 gap-2 my-3 text-xs">

                            {{-- Day 1 --}}
                            <div class="p-2 rounded-xl bg-neutral-950/80 border border-neutral-800 space-y-1.5 text-left">
                                <div class="flex justify-between items-center text-[10px] text-neutral-400">
                                    <span class="font-bold text-white">Senin, 21</span>
                                    <span class="text-emerald-400 text-[9px]">Tayang</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-neutral-900 border border-neutral-800/80 space-y-1">
                                    <div class="flex items-center gap-1 text-[9px] text-pink-400 font-semibold">
                                        <i data-lucide="instagram" class="w-3 h-3"></i> 08:30 WIB
                                    </div>
                                    <div class="text-[10px] text-neutral-200 truncate">Kopi Pagi Semangat Kerja</div>
                                </div>
                            </div>

                            {{-- Day 2 (Highlighted Active) --}}
                            <div
                                class="p-2 rounded-xl bg-neutral-950/90 border border-purple-500/40 ring-1 ring-purple-500/30 space-y-1.5 text-left">
                                <div class="flex justify-between items-center text-[10px] text-neutral-400">
                                    <span class="font-bold text-purple-400">Hari Ini, 23</span>
                                    <span class="text-amber-400 text-[9px]">Antrean</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-purple-950/30 border border-purple-800/40 space-y-1">
                                    <div class="flex items-center gap-1 text-[9px] text-pink-400 font-semibold">
                                        <i data-lucide="instagram" class="w-3 h-3"></i> 15:30 WIB
                                    </div>
                                    <div class="text-[10px] text-white font-medium truncate">Croissant Butter Promo Beli 2
                                    </div>
                                </div>
                            </div>

                            {{-- Day 3 --}}
                            <div class="p-2 rounded-xl bg-neutral-950/80 border border-neutral-800 space-y-1.5 text-left">
                                <div class="flex justify-between items-center text-[10px] text-neutral-400">
                                    <span class="font-bold text-white">Jumat, 25</span>
                                    <span class="text-blue-400 text-[9px]">Gajian</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-neutral-900 border border-neutral-800/80 space-y-1">
                                    <div class="flex items-center gap-1 text-[9px] text-emerald-400 font-semibold">
                                        <i data-lucide="message-circle" class="w-3 h-3"></i> 19:00 WIB
                                    </div>
                                    <div class="text-[10px] text-neutral-200 truncate">Voucher Payday VIP Member</div>
                                </div>
                            </div>

                        </div>

                        {{-- Scheduled Content Card Detail --}}
                        <div
                            class="p-3 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between text-xs text-left">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-lg bg-neutral-800 flex items-center justify-center text-purple-400 shrink-0">
                                    <i data-lucide="move" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-white text-[11px]">Seret & Geser (Drag-and-Drop) Jadwal
                                    </div>
                                    <div class="text-[10px] text-neutral-400">Pindahkan tanggal tayang promo akhir pekan ke
                                        hari Sabtu</div>
                                </div>
                            </div>
                            <span class="text-purple-400 text-[10px] font-mono">Aktif</span>
                        </div>

                        {{-- Footer Action Bar --}}
                        <div
                            class="mt-3 pt-2 border-t border-neutral-800 flex items-center justify-between text-[11px] text-neutral-400">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="bell" class="w-3.5 h-3.5 text-purple-400"></i>
                                Notifikasi pengingat tayang aktif untuk staf
                            </span>
                            <a href="{{ route('public.content.publishing') }}"
                                class="text-purple-400 hover:underline font-medium">Buka Antrean Tayang →</a>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerugian Tanpa Perencanaan Konten --}}
        <section
            class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-3">
                        Tantangan Konsistensi Pemasaran</h2>
                    <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Mengapa Pemasaran Toko Anda Sering Terasa Sporadis & Tanpa Arah?
                    </p>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                        Tanpa kalender editorial terencana, postingan media sosial dibuat terburu-buru hanya saat toko
                        sedang sepi dan sering melewatkan momen penjualan penting.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="calendar-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Melewatkan Momen Promo Emas</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Lupa menyiapkan materi promo untuk tanggal gajian atau hari libur panjang. Saat kompetitor sudah
                            panen pesanan, Anda baru mulai mencari ide materi promo.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="repeat-1" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Konten Berulang & Membosankan</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Mempromosikan produk yang sama 3 hari berturut-turut karena tidak ada catatan apa yang sudah
                            tayang kemarin. Pengikut media sosial merasa bosan dan berhenti mengikuti akun Anda.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="eye-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Owner Buta Rencana Pemasaran</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pemilik bisnis tidak memiliki visibilitas atas apa yang dikerjakan tim media sosial minggu
                            depan, sehingga tidak bisa menyelaraskan stok bahan baku di toko fisik.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE CALENDAR CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-3">Fitur
                    Kalender Editorial COOCA</h2>
                <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Disiplin Pemasaran Tanpa Beban Eksekusi Rumit
                </p>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                    Memudahkan penyusunan strategi jangka panjang dan memastikan eksekusi harian berjalan otomatis.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Visual Monthly Grid (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Gambaran Utuh Kalender Sebulan Penuh
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Lihat peta rencana promosi harian Anda dalam satu layar. Setiap kartu menampilkan thumbnail
                            gambar produk, waktu jam tayang, dan target kanal (Instagram, Facebook, WhatsApp) secara
                            transparan.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                        <span class="text-neutral-600 dark:text-neutral-300 font-medium">Perencanaan Cepat:</span>
                        <span class="text-purple-600 dark:text-purple-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Susun 30 materi promosi dalam 1 hari kerja
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Penjadwalan Drag-and-Drop (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-pink-100 dark:bg-pink-950 flex items-center justify-center text-pink-600 dark:text-pink-400">
                            <i data-lucide="move" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Penyesuaian Fleksibel (Drag & Drop)
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Toko sedang mengadakan renovasi atau stok bahan baku terlambat tiba? Cukup geser kartu konten ke
                            tanggal lain dengan mouse atau layar sentuh.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                        <span class="text-neutral-500">Rescheduling Instan</span>
                        <span class="text-pink-500 font-bold">Waktu Otomatis Disesuaikan</span>
                    </div>
                </div>

                {{-- Bento Card 3: Penyelarasan Agenda Toko Fisik (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Penyelarasan Promo Toko</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Tandai tanggal gajian, hari libur nasional, dan promo bazar offline di kalender agar tim promosi
                        menyiapkan konten pendukung tepat waktu.
                    </p>
                </div>

                {{-- Bento Card 4: Filter Kanal Penjualan (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="filter" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Penyaringan Kanal Spesifik</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Saring tampilan kalender berdasarkan platform: khusus Instagram, Facebook, atau WhatsApp untuk
                        memeriksa keseimbangan frekuensi postingan.
                    </p>
                </div>

                {{-- Bento Card 5: Status Transparan (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="tag" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Label Status Warna</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Identifikasi draf yang butuh review, konten yang sudah disetujui, dan materi yang telah sukses
                        tayang dengan kode warna intuitif.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Alur Penjadwalan Hingga Tayang --}}
        <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-400 text-xs font-semibold mb-3 border border-purple-500/30">
                        <span>Siklus Penjadwalan Terkoordinasi</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Bagaimana Kalender Menggerakkan Pemasaran Toko
                    </h2>
                    <p class="text-neutral-400 text-sm sm:text-base mt-3">
                        Mengubah ide promosi menjadi penayangan otomatis yang teratur tanpa keterlambatan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Petakan Tema Mingguan</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Tentukan tema promosi mingguan di awal bulan (misal Minggu 1: Menu Baru, Minggu 4: Promo Gajian
                            Payday).
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-pink-600/30 text-pink-400 flex items-center justify-center font-bold text-xs border border-pink-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Isi Slot Tanggal Tayang</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Tautkan materi visual dan caption dari modul Content Creation ke slot tanggal dan jam tayang
                            yang strategis di kalender.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Verifikasi Bersama Owner</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Owner meninjau kalender secara menyeluruh dan memastikan seluruh promosi selaras dengan kesiapan
                            stok fisik cabang toko.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Otomasi ke Publishing</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Konten yang telah disetujui otomatis masuk ke modul Publishing untuk diterbitkan sesuai jadwal
                            tanpa perlu pengunggahan manual.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-2">
                    Pertanyaan Umum</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Kalender
                    Konten</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana cara kerja penjadwalan konten dengan kalender ini?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Anda cukup memilih tanggal dan jam pada kisi kalender, lalu melampirkan draf materi promosi yang
                        telah dibuat di modul Content Creation. Sistem akan menayangkan postingan tersebut secara otomatis
                        tepat pada waktu yang telah ditentukan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah saya bisa mengubah jadwal tayang jika ada perubahan agenda toko mendadak?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Sangat mudah. Anda cukup menyeret (drag-and-drop) kartu postingan dari tanggal lama ke tanggal baru
                        di tampilan kalender. Jam tayang dan antrean otomatis menyesuaikan tanpa perlu mengetik ulang
                        konten.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah bisa memfilter tampilan kalender hanya untuk satu kanal media sosial tertentu?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat menyaring tampilan kalender per channel—misalnya hanya melihat jadwal postingan
                        Instagram, atau hanya memantau jadwal broadcast promo WhatsApp pelanggan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana tim saya berkolaborasi menyusun jadwal promosi ini?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Tim konten Anda dapat menyusun draf rencana postingan sepanjang bulan. Anda sebagai pemilik bisnis
                        dapat melihat gambaran besar strategi promosi, memberikan masukan pada draf tertentu, dan memastikan
                        tidak ada hari tanpa kehadiran promosi.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-1">
                        Modul Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Otomasi Konten</p>
                </div>
                <a href="{{ route('public.content.publishing') }}"
                    class="text-xs sm:text-sm font-semibold text-purple-600 dark:text-purple-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lanjut ke Modul Publikasi</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.content.creation') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">
                        Pembuatan Konten Produk</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Buat materi promosi menarik langsung
                        dari katalog stok kasir.</p>
                </a>

                <a href="{{ route('public.content.publishing') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">
                        Publikasi Multi-Kanal</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Eksekusi tayang otomatis ke Instagram
                        dan Facebook sesuai jadwal.</p>
                </a>

                <a href="{{ route('public.content.analytics') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">
                        Analitik Konten</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Evaluasi performa postingan dan konversi
                        penjualan toko.</p>
                </a>

                <a href="{{ route('public.omnichannel.social-media') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">
                        Manajemen Akun Medsos</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kelola koneksi akun media sosial bisnis
                        secara terpusat.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Mulai Rencanakan Pemasaran Toko Anda dengan Teratur
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-400">
                        Hentikan kebingungan postingan mendadak dan bangun kehadiran media sosial yang konsisten dan
                        berdampak bagi bisnis bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-semibold text-sm transition-all shadow-md">
                            Coba Demo Kalender Konten
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                            Konsultasi Rencana Pemasaran
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
