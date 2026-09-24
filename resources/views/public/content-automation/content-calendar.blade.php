@extends('layouts.public_marketing')

@section('title', 'Software Kalender Konten Editorial & Jadwal Promosi Bisnis | COOCA')
@section('description',
    'Rencanakan dan pantau seluruh jadwal publikasi konten media sosial bisnis Anda sebulan penuh di muka. Kalender editorial visual drag-and-drop terhubung ke Instagram, Facebook, dan WhatsApp.')
@section('og_title', 'Software Kalender Konten Editorial & Jadwal Promosi Bisnis | COOCA')
@section('og_description',
    'Rencanakan dan pantau seluruh jadwal publikasi konten media sosial bisnis Anda sebulan penuh di muka. Kalender editorial visual drag-and-drop terhubung ke Instagram, Facebook, dan WhatsApp.')
@section('keywords',
    'software kalender konten, aplikasi jadwal promosi bisnis, editorial calendar medsos, perencanaan konten produk, jadwal posting instagram facebook')

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
        "text": "Bisa. Anda dapat menyaring tampilan kalender per channel-misalnya hanya melihat jadwal postingan Instagram, atau hanya memantau jadwal broadcast promo WhatsApp pelanggan."
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
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION (Adopted from landing.blade.php concept - Full Viewport) --}}
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Subtle Ambient Background Glows (Pure CSS) --}}
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-10 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-center w-full">
                    
                    {{-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof --}}
                    <div
                        class="lg:col-span-5 space-y-5 sm:space-y-6 text-left flex flex-col items-start w-full">
                        
                        <!-- Pure Typographic Overline Kicker with Pulse Dot -->
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Editorial Planning &amp; Visual Campaign Grid
                            </p>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[2.65rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words">
                                Rencanakan Pemasaran Sebulan Penuh <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Dalam Satu Tampilan Visual.</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-xl">
                            Ketahui persis konten apa yang akan tayang besok, lusa, hingga akhir bulan. Susun tema promosi
                            mingguan, sesuaikan dengan tanggal gajian pembeli, dan geser jadwal tayang secara leluasa dengan
                            kalender editorial interaktif COOCA.
                        </p>

                        {{-- Action Buttons --}}
                        <div class="pt-1 flex flex-row flex-wrap items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-5 sm:px-8 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-base flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Coba Demo Kalender</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.content.publishing') }}"
                                class="h-10 sm:h-12 px-4 sm:px-6 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 border border-white/15 backdrop-blur-sm transition-all min-h-[40px] sm:min-h-[48px]">
                                <span>Lihat Alur Publikasi</span>
                            </a>
                        </div>

                        {{-- Key Trust Checkpoints --}}
                        <div
                            class="pt-1 flex flex-wrap items-center justify-start gap-x-4 gap-y-1.5 text-[11px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Pandangan Bulanan &amp; Mingguan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Drag &amp; Drop Jadwal Cepat</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Multi-Kanal (IG, FB, WA)</span>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: Simulated Live Monthly Editorial Calendar Bento Glass UI --}}
                    <div class="lg:col-span-7 relative w-full">
                        <!-- Ambient Spotlight Glow -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] blur-2xl pointer-events-none -z-10">
                        </div>

                        <div
                            class="relative rounded-2xl sm:rounded-3xl bg-[#0E1E45]/85 p-4 sm:p-6 shadow-2xl border border-white/15 ring-1 ring-white/10 backdrop-blur-xl text-white">

                            {{-- Calendar Header --}}
                            <div class="flex items-center justify-between gap-2 pb-3.5 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <span class="p-2 rounded-xl bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                        <i data-lucide="calendar-range" class="w-4 h-4"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-sm truncate">September 2026 - Jadwal Editorial</div>
                                        <div class="text-[11px] text-slate-400 truncate">24 Terjadwal • 4 Draf Review</div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-white/10 text-slate-300 text-[11px] shrink-0">Semua Kanal</span>
                            </div>

                            {{-- Calendar Days Grid Simulation --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 my-3.5 text-xs">

                                {{-- Day 1 --}}
                                <div
                                    class="p-3 rounded-2xl bg-[#060B1E]/90 border border-white/10 space-y-2 text-left min-w-0">
                                    <div class="flex justify-between items-center text-[10.5px] text-slate-400">
                                        <span class="font-bold text-white">Senin, 21</span>
                                        <span class="text-emerald-400 text-[9.5px]">Tayang</span>
                                    </div>
                                    <div class="p-2 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                        <div class="flex items-center gap-1 text-[9.5px] text-[#00C4D8] font-semibold">
                                            <i data-lucide="instagram" class="w-3 h-3 shrink-0"></i> <span>08:30 WIB</span>
                                        </div>
                                        <div class="text-[11px] text-slate-200 truncate">Kopi Pagi Semangat Kerja</div>
                                    </div>
                                </div>

                                {{-- Day 2 (Highlighted Active) --}}
                                <div
                                    class="p-3 rounded-2xl bg-[#060B1E]/95 border border-[#007AFF]/50 ring-1 ring-[#007AFF]/30 space-y-2 text-left min-w-0">
                                    <div class="flex justify-between items-center text-[10.5px] text-slate-400">
                                        <span class="font-bold text-[#00C4D8]">Hari Ini, 23</span>
                                        <span class="text-amber-400 text-[9.5px]">Antrean</span>
                                    </div>
                                    <div class="p-2 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 space-y-1">
                                        <div class="flex items-center gap-1 text-[9.5px] text-[#00C4D8] font-semibold">
                                            <i data-lucide="instagram" class="w-3 h-3 shrink-0"></i> <span>15:30 WIB</span>
                                        </div>
                                        <div class="text-[11px] text-white font-medium truncate">Croissant Butter Promo Beli 2</div>
                                    </div>
                                </div>

                                {{-- Day 3 --}}
                                <div
                                    class="p-3 rounded-2xl bg-[#060B1E]/90 border border-white/10 space-y-2 text-left min-w-0">
                                    <div class="flex justify-between items-center text-[10.5px] text-slate-400">
                                        <span class="font-bold text-white">Jumat, 25</span>
                                        <span class="text-blue-400 text-[9.5px]">Gajian</span>
                                    </div>
                                    <div class="p-2 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                        <div class="flex items-center gap-1 text-[9.5px] text-emerald-400 font-semibold">
                                            <i data-lucide="message-circle" class="w-3 h-3 shrink-0"></i> <span>19:00 WIB</span>
                                        </div>
                                        <div class="text-[11px] text-slate-200 truncate">Voucher Payday VIP Member</div>
                                    </div>
                                </div>

                            </div>

                            {{-- Scheduled Content Card Detail --}}
                            <div
                                class="p-3 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between gap-2.5 text-xs text-left">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-[#00C4D8] shrink-0">
                                        <i data-lucide="move" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-white text-[11.5px] truncate">Seret &amp; Geser (Drag &amp; Drop) Jadwal</div>
                                        <div class="text-[10.5px] text-slate-400 truncate">Pindahkan tanggal tayang promo akhir pekan ke Sabtu</div>
                                    </div>
                                </div>
                                <span class="text-[#00C4D8] text-[10.5px] font-mono shrink-0">Aktif</span>
                            </div>

                            {{-- Footer Action Bar --}}
                            <div
                                class="mt-3.5 pt-2.5 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11.5px] text-slate-400">
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <i data-lucide="bell" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                    <span class="truncate">Notifikasi pengingat tayang aktif untuk staf</span>
                                </span>
                                <a href="{{ route('public.content.publishing') }}"
                                    class="text-[#00C4D8] hover:underline font-medium shrink-0">Buka Antrean Tayang →</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerugian Tanpa Perencanaan Konten --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-b border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                        Tantangan Konsistensi Pemasaran
                    </h2>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Mengapa Pemasaran Toko Anda Sering Terasa Sporadis & Tanpa Arah?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3">
                        Tanpa kalender editorial terencana, postingan media sosial dibuat terburu-buru hanya saat toko
                        sedang sepi dan sering melewatkan momen penjualan penting.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                            <i data-lucide="calendar-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Melewatkan Momen Promo Emas</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Lupa menyiapkan materi promo untuk tanggal gajian atau hari libur panjang. Saat kompetitor sudah
                            panen pesanan, Anda baru mulai mencari ide materi promo.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="repeat-1" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Konten Berulang & Membosankan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Mempromosikan produk yang sama 3 hari berturut-turut karena tidak ada catatan apa yang sudah
                            tayang kemarin. Pengikut media sosial merasa bosan dan berhenti mengikuti akun Anda.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="eye-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Owner Buta Rencana Pemasaran</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
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
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                    Fitur Kalender Editorial COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Disiplin Pemasaran Tanpa Beban Eksekusi Rumit
                </p>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">
                    Memudahkan penyusunan strategi jangka panjang dan memastikan eksekusi harian berjalan otomatis.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Visual Monthly Grid (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Gambaran Utuh Kalender Sebulan Penuh
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Lihat peta rencana promosi harian Anda dalam satu layar. Setiap kartu menampilkan thumbnail
                            gambar produk, waktu jam tayang, dan target kanal (Instagram, Facebook, WhatsApp) secara
                            transparan.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">Perencanaan Cepat:</span>
                        <span class="text-[#007AFF] font-semibold flex items-center gap-1 shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Susun 30 materi promosi dalam 1 hari kerja
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Penjadwalan Drag-and-Drop (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                            <i data-lucide="move" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Penyesuaian Fleksibel (Drag & Drop)
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Toko sedang mengadakan renovasi atau stok bahan baku terlambat tiba? Cukup geser kartu konten ke
                            tanggal lain dengan mouse atau layar sentuh.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Rescheduling Instan</span>
                        <span class="text-purple-500 font-bold shrink-0">Waktu Otomatis Disesuaikan</span>
                    </div>
                </div>

                {{-- Bento Card 3: Penyelarasan Agenda Toko Fisik (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-500">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Penyelarasan Promo Toko</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Tandai tanggal gajian, hari libur nasional, dan promo bazar offline di kalender agar tim promosi
                        menyiapkan konten pendukung tepat waktu.
                    </p>
                </div>

                {{-- Bento Card 4: Filter Kanal Penjualan (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-teal-500/10 dark:bg-teal-500/20 flex items-center justify-center text-teal-500">
                        <i data-lucide="filter" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Penyaringan Kanal Spesifik</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Saring tampilan kalender berdasarkan platform: khusus Instagram, Facebook, atau WhatsApp untuk
                        memeriksa keseimbangan frekuensi postingan.
                    </p>
                </div>

                {{-- Bento Card 5: Status Transparan (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="tag" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Label Status Warna</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Identifikasi draf yang butuh review, konten yang sudah disetujui, dan materi yang telah sukses
                        tayang dengan kode warna intuitif.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Alur Penjadwalan Hingga Tayang --}}
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
                        <span>Siklus Penjadwalan Terkoordinasi</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                        Bagaimana Kalender Menggerakkan Pemasaran Toko
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3">
                        Mengubah ide promosi menjadi penayangan otomatis yang teratur tanpa keterlambatan.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Petakan Tema Mingguan</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Tentukan tema promosi mingguan di awal bulan (misal Minggu 1: Menu Baru, Minggu 4: Promo Gajian
                            Payday).
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Isi Slot Tanggal Tayang</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Tautkan materi visual dan caption dari modul Content Creation ke slot tanggal dan jam tayang
                            yang strategis di kalender.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Verifikasi Bersama Owner</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Owner meninjau kalender secara menyeluruh dan memastikan seluruh promosi selaras dengan kesiapan
                            stok fisik cabang toko.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Otomasi ke Publishing</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
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
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                    Tanya Jawab Seputar Kalender Konten
                </p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara kerja penjadwalan konten dengan kalender ini?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Anda cukup memilih tanggal dan jam pada kisi kalender, lalu melampirkan draf materi promosi yang
                        telah dibuat di modul Content Creation. Sistem akan menayangkan postingan tersebut secara otomatis
                        tepat pada waktu yang telah ditentukan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah saya bisa mengubah jadwal tayang jika ada perubahan agenda toko mendadak?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Sangat mudah. Anda cukup menyeret (drag-and-drop) kartu postingan dari tanggal lama ke tanggal baru
                        di tampilan kalender. Jam tayang dan antrean otomatis menyesuaikan tanpa perlu mengetik ulang
                        konten.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah bisa memfilter tampilan kalender hanya untuk satu kanal media sosial tertentu?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat menyaring tampilan kalender per channel-misalnya hanya melihat jadwal postingan
                        Instagram, atau hanya memantau jadwal broadcast promo WhatsApp pelanggan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana tim saya berkolaborasi menyusun jadwal promosi ini?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Tim konten Anda dapat menyusun draf rencana postingan sepanjang bulan. Anda sebagai pemilik bisnis
                        dapat melihat gambaran besar strategi promosi, memberikan masukan pada draf tertentu, dan memastikan
                        tidak ada hari tanpa kehadiran promosi.
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
                <a href="{{ route('public.content.publishing') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lanjut ke Modul Publikasi</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.content.creation') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-pink-500/10 dark:bg-pink-500/20 text-pink-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Pembuatan Konten Produk
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Buat materi promosi menarik langsung dari
                        katalog stok kasir.</p>
                </a>

                <a href="{{ route('public.content.publishing') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Publikasi Multi-Kanal
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Eksekusi tayang otomatis ke Instagram dan
                        Facebook sesuai jadwal.</p>
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
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Evaluasi performa postingan dan konversi
                        penjualan toko.</p>
                </a>

                <a href="{{ route('public.omnichannel.social-media') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Manajemen Akun Medsos
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola koneksi akun media sosial bisnis
                        secara terpusat.</p>
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
                        Mulai Rencanakan Pemasaran Toko Anda dengan Teratur
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Hentikan kebingungan postingan mendadak dan bangun kehadiran media sosial yang konsisten dan
                        berdampak bagi bisnis bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all">
                            Coba Demo Kalender Konten
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                            Konsultasi Rencana Pemasaran
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
