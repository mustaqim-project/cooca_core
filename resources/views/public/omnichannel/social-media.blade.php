@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Media Sosial & Multi-Akun Bisnis | COOCA')
@section('description',
    'Kelola seluruh akun Instagram, Facebook, dan TikTok bisnis Anda dari satu dashboard terpadu. Rencanakan kalender konten, jadwalkan publikasi otomatis, dan hubungkan langsung dengan katalog produk toko.')
@section('og_title', 'Software Manajemen Media Sosial & Multi-Akun Bisnis | COOCA')
@section('og_description',
    'Kelola seluruh akun Instagram, Facebook, dan TikTok bisnis Anda dari satu dashboard terpadu. Rencanakan kalender konten, jadwalkan publikasi otomatis, dan hubungkan langsung dengan katalog produk toko.')
@section('keywords',
    'software manajemen media sosial, aplikasi jadwal posting medsos, kelola multi akun instagram tiktok, social media scheduler bisnis, konten terintegrasi pos')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
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
  "@@context": "https://schema.org",
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
    <div
        class="relative overflow-hidden bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION (Unified Bento Cockpit - No Breadcrumb) --}}
        <section
            class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Dual Ambient Glowing Blurs --}}
            <div
                class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none">
            </div>
            <div
                class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none">
            </div>

            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    {{-- Left Column: Copy & Value Proposition (6 Cols) --}}
                    <div class="lg:col-span-6 space-y-5 text-left">
                        {{-- Typographic Overline Kicker with Pulse Dot (Zero Pill Abuse) --}}
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Omnichannel Social Media &amp; Product Showcase
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Kelola Seluruh Kanal Media Sosial Bisnis <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Dari Satu Ruang Kerja Terpadu</span>
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed text-pretty max-w-2xl">
                            Hentikan repotnya berganti-ganti akun di smartphone. Jadwalkan konten promosi ke Instagram, Facebook, dan TikTok, sinkronkan langsung dengan katalog produk toko Anda, dan ubah pengikut media sosial menjadi pembeli nyata.
                        </p>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Coba Demo Media Sosial</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                            </a>
                            <a href="{{ route('public.content.calendar') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm font-semibold text-sm hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <span>Lihat Kalender Konten</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Multi-Akun: IG, FB, &amp; TikTok</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Hubungkan ke Katalog POS</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Aman Tanpa Bagi Password</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Apple Bento Social Media Cockpit (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-3.5 sm:p-5 lg:p-6 shadow-2xl backdrop-blur-2xl text-white">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            {{-- Channel Header Selector --}}
                            <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-white/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center shrink-0 border border-[#007AFF]/30">
                                        <i data-lucide="layers" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">Kanal Media Sosial Terhubung</div>
                                        <div class="text-[10px] text-slate-400 truncate">3 Akun Aktif • Status: Terjadwal</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] sm:text-xs font-mono font-bold shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Sync Normal</span>
                                </div>
                            </div>

                            {{-- Connected Accounts Strip --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 my-3.5 text-xs">
                                <div class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#007AFF] to-[#00C4D8] flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm">
                                        IG
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-white truncate text-[11px]">@kopiseduh</div>
                                        <div class="text-[9px] text-slate-400 truncate">12.4k Followers</div>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm">
                                        FB
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-white truncate text-[11px]">Kopi Seduh ID</div>
                                        <div class="text-[9px] text-slate-400 truncate">8.2k Likes</div>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-neutral-800 border border-white/10 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm">
                                        TT
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-white truncate text-[11px]">@kopiseduh.co</div>
                                        <div class="text-[9px] text-slate-400 truncate">24.5k Views</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Next Scheduled Post Preview Card --}}
                            <div class="p-3 sm:p-3.5 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-2.5 text-xs">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-mono text-[#00C4D8] font-semibold flex items-center gap-1.5 truncate">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span class="truncate">Tayang Hari Ini • 15:30 WIB</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded bg-white/10 text-[10px] text-slate-300 font-mono shrink-0">IG &amp; FB</span>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center pt-1">
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 shrink-0">
                                        <i data-lucide="image" class="w-6 h-6 text-[#00C4D8]"></i>
                                    </div>
                                    <div class="space-y-1 text-left min-w-0 flex-1">
                                        <div class="font-semibold text-white text-[11px] sm:text-xs truncate">Promo Spesial: Buy 1 Get 1 Kopi Susu Aren</div>
                                        <div class="text-[10px] text-slate-400 line-clamp-2 leading-relaxed">
                                            "Ajak teman terbaikmu mampir ke outlet Sudirman &amp; Senopati! Dapatkan promo spesial cukup tunjukkan postingan ini..."
                                        </div>
                                        <div class="flex items-center gap-2 pt-0.5 text-[9px] text-emerald-400 font-mono truncate">
                                            <span class="truncate">Terkait: SKU-KOP-01</span>
                                            <span>•</span>
                                            <span class="truncate">Link Order Aktif</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Performance Metric Summary --}}
                            <div class="mt-3 p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <i data-lucide="trending-up" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="text-white font-medium text-[11px]">Konversi Medsos:</span>
                                        <span class="text-slate-400 text-[10px]"> 148 Klik Link Toko • 32 Order POS</span>
                                    </div>
                                </div>
                                <a href="{{ route('public.content.analytics') }}"
                                    class="text-[#00C4D8] hover:text-white text-[10px] font-semibold transition-colors flex items-center gap-1 shrink-0 self-end sm:self-auto">
                                    <span>Analitik Konten</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="share-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Multi-Platform Sync: <strong class="text-emerald-400">Real-Time</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="link-2" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Auto-Catalog Link</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerumitan Mengurus Medsos Bisnis Secara Terpisah --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Hambatan Pemasaran Konten
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Mengapa Mengelola Medsos Bisnis Sering Menghabiskan Waktu Tanpa Menghasilkan Penjualan?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Banyak bisnis rajin mengunggah konten, namun materi postingan terlepas dari ketersediaan stok fisik
                        di toko sehingga pelanggan kecewa saat ingin membeli.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Risiko Berbagi Password Akun</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Memberikan login Instagram atau TikTok resmi ke staf admin lepas (freelancer). Risiko akun
                            terkunci karena verifikasi OTP atau terbawa saat staf berhenti kerja.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="calendar-x-2" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Jadwal Posting Bolong-Bolong</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Hari ini posting lima kali, lalu minggu berikutnya akun mati suri karena staf sibuk melayani
                            pembeli di toko. Algoritma media sosial menurunkan jangkauan akun Anda.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="unlink" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Konten Tidak Nyambung dengan Stok
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Admin mempromosikan produk tertentu hingga viral, namun ternyata stok di gudang outlet sudah
                            kosong dua hari lalu. Momen penjualan emas hilang sia-sia.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE SOCIAL MEDIA CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-white dark:bg-[#0B132B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                    Fitur Media Sosial COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Publikasi Konsisten yang Terhubung dengan Operasional Toko
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Dirancang khusus untuk membantu pelaku bisnis UMKM membangun kehadiran digital yang profesional tanpa
                    perlu merekrut agensi mahal.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Multi-Channel Publisher (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Publikasi Multi-Kanal Sekaligus
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Tulis teks promosi sekali, sesuaikan format visual per kanal, dan jadwalkan penayangan serentak
                            ke akun Instagram bisnis dan Facebook Page Anda. Hemat waktu operasional hingga 80% setiap
                            minggu.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex items-center justify-between text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Kanal Terhubung:</span>
                        <span class="text-[#007AFF] dark:text-[#00C4D8] font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> API Resmi Meta & Integrasi Terverifikasi
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Sinkronisasi Katalog Produk Toko (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Tautkan Produk & Stok Toko
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Saat membuat postingan, pilih produk langsung dari modul Inventori COOCA. Sistem menyertakan
                            harga terkini, ketersediaan stok cabang, dan tautan belanja yang siap diklik audiens.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex items-center justify-between font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Perlindungan Overselling</span>
                        <span class="text-purple-600 dark:text-purple-400 font-bold">Stok Habis = Link Nonaktif</span>
                    </div>
                </div>

                {{-- Bento Card 3: Kalender Editorial Visual (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kalender Editorial Interaktif</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Lihat rencana postingan bulanan dalam tampilan visual. Geser jadwal konten ke hari lain dengan
                        drag-and-drop jika ada perubahan agenda toko.
                    </p>
                </div>

                {{-- Bento Card 4: Persetujuan Draf Tim (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-400">
                        <i data-lucide="check-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Alur Review Draf Konten</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Staf konten mengunggah draf teks dan gambar. Konten baru akan tayang setelah disetujui owner,
                        memastikan pesan brand selalu sesuai standar.
                    </p>
                </div>

                {{-- Bento Card 5: Analitik Klik & Konversi (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="mouse-pointer-click" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pelacakan Konversi Riil</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Bukan hanya menghitung jumlah likes. Ketahui postingan mana yang mendatangkan klik pesanan paling
                        banyak ke toko Anda.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Dari Postingan Medsos Menjadi Penjualan Nyata (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Siklus Pemasaran Hingga Transaksi</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Menghubungkan Media Sosial dengan Kasir & Gudang
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Bagaimana konten digital Anda berinteraksi dengan seluruh ekosistem bisnis COOCA.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Pilih Produk Siap Jual</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Pilih produk unggulan yang stok fisiknya masih banyak di gudang cabang untuk dijadikan materi
                            promosi konten media sosial minggu ini.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Jadwal Publikasi Otomatis</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Atur jadwal tayang di jam santai audiens (misal 12:00 atau 19:30). Sistem otomatis mengunggah
                            materi ke Instagram dan Facebook tanpa campur tangan staf.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-500/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-blue-500/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Pesanan Masuk Terpusat</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Audiens mengklik link produk di bio/postingan untuk memesan atau datang ke toko fisik. Transaksi
                            langsung masuk ke modul Order terpadu.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Stok Terpotong & Laporan Rapi</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Stok gudang cabang berkurang, omzet tercatat di modul Finance, dan laporan menunjukkan berapa
                            rupiah keuntungan yang dihasilkan dari postingan tersebut.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 bg-white dark:bg-[#0B132B]">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar
                    Pengelolaan Medsos</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Platform media sosial apa saja yang didukung oleh modul COOCA Social Media?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        COOCA dirancang untuk menghubungkan akun profesional Instagram Business, Facebook Pages, serta
                        perencanaan konten untuk TikTok dan YouTube Shorts melalui API resmi yang aman.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah staf konten kreator kami memerlukan password utama akun media sosial bisnis?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Tidak perlu. Cukup hubungkan akun sekali saja oleh Owner. Staf konten hanya menyusun draf postingan
                        di dalam COOCA tanpa pernah mengetahui username dan password akun media sosial resmi perusahaan,
                        sehingga privasi dan keamanan akun bisnis Anda tetap terlindungi.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana konten di media sosial bisa langsung terhubung dengan produk di toko saya?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Anda dapat langsung memilih item dari modul Inventori & POS saat menyusun postingan. Sistem otomatis
                        menyematkan detail nama barang, harga resmi, serta link katalog digital untuk memudahkan audiens
                        langsung melakukan pemesanan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah bisa menjadwalkan konten postingan untuk sebulan ke depan sekaligus?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur tanggal dan jam tayang untuk puluhan postingan sekaligus melalui tampilan
                        kalender drag-and-drop, sehingga waktu Anda tidak tersita untuk mengunggah konten secara manual
                        setiap hari.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-1">
                        Modul Terkait
                    </h2>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Ekosistem Pemasaran &
                        Konten</p>
                </div>
                <a href="{{ route('public.content.creation') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Pelajari Content Automation</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.content.calendar') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Kalender Konten
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Susun jadwal kampanye promosi dan tema
                        postingan mingguan.</p>
                </a>

                <a href="{{ route('public.omnichannel.whatsapp') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        WhatsApp Komunikasi
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kirim pesan invoice digital dan follow-up
                        pesanan langsung ke chat.</p>
                </a>

                <a href="{{ route('public.omnichannel.orders') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Order Omnichannel
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pusat penanganan pesanan yang masuk dari
                        seluruh kanal penjualan.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Katalog & Stok Toko
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sumber produk resmi dan harga terkini untuk
                        materi promosi.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Ambient lights inside CTA --}}
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Mulai Publikasikan Konten Bisnis yang Menghasilkan Penjualan
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Satukan akun media sosial Anda dengan sistem operasional toko dan rasakan kemudahan membangun
                        kehadiran brand yang konsisten bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Media Sosial
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Konsultasi Kebutuhan Pemasaran
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
