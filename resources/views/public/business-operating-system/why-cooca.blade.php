@extends('layouts.public_marketing')

@section('title', 'Mengapa Memilih COOCA: Solusi Bisnis Terintegrasi vs Terpisah | COOCA')
@section('description', 'Berhenti membayar 5 aplikasi berbeda dan spreadsheet tercecer. COOCA menyatukan kasir POS, stok gudang, pembukuan keuangan, dan WhatsApp dalam satu ekosistem.')
@section('keywords', 'kenapa pilih cooca, software terintegrasi vs aplikasi terpisah, kelebihan erp umkm, aplikasi bisnis tanpa langganan mahal, efisiensi operasional toko')

@section('og_title', 'Mengapa Memilih COOCA: Solusi Bisnis Terpadu vs Terpisah | COOCA')
@section('og_description', 'Berhenti bayar software terpisah. COOCA satukan kasir POS, stok resep, pembukuan riil, dan WhatsApp dalam satu sistem terpadu.')

@push('seo')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Mengapa Memilih COOCA Business Operating System",
        "description": "Perbandingan mendalam antara mengelola bisnis dengan software terpisah versus ekosistem terpadu COOCA.",
        "url": "{{ route('public.bos.why-cooca') }}",
        "publisher": {
            "@type": "Organization",
            "name": "COOCA Indonesia",
            "url": "{{ url('/') }}"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Beranda",
                "item": "{{ route('landing') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Business Operating System",
                "item": "{{ route('public.bos.overview') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Kenapa COOCA",
                "item": "{{ route('public.bos.why-cooca') }}"
            }
        ]
    }
    </script>
@endpush

@section('content')
    <div class="w-full bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Why COOCA & Cost Efficiency - Full Viewport) ═════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            <!-- Ambient Background Glows -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Without Breadcrumbs) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile, Tablet & Desktop) -->
                    <div class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Typographic Overline Kicker with Pulse Dot -->
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse shrink-0"></span>
                            <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Efisiensi Nyata Tanpa Biaya Tersembunyi
                            </p>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[2.75rem] xl:text-[3.5rem] font-extrabold text-white tracking-tight leading-[1.22] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Berhenti Membayar Banyak <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Software Terpisah yang Tidak Terhubung</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Saat bisnis Anda tumbuh, memakai POS terpisah, stok terpisah, spreadsheet spreadsheet tercecer, dan broadcast WA pihak ketiga justru memicu biaya langganan mahal, data selisih, dan waktu terbuang.
                        </p>

                        <!-- Action Buttons (Left-Aligned on Mobile, Tablet & Desktop) -->
                        <div class="pt-1 flex flex-wrap items-center justify-start gap-2.5 sm:gap-3.5 w-full sm:w-auto">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="h-10 sm:h-12 px-5 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                    <span>Dashboard</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="h-10 sm:h-12 px-5 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                    <span>Dashboard</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="h-10 sm:h-12 px-5 sm:px-8 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-base flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                    <span>Beralih ke COOCA Sekarang</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @endif
                            <a href="{{ route('public.pricing') }}"
                                class="h-10 sm:h-12 px-4 sm:px-6 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Bandingkan Paket &amp; Biaya</span>
                            </a>
                        </div>

                        <!-- Reassurance Checkpoints -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3.5 sm:gap-x-5 gap-y-1.5 text-[11px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400 shrink-0"></i>
                                <span>Hemat Biaya s/d 75% Tiap Bulan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400 shrink-0"></i>
                                <span>1 Akun untuk Seluruh Operasional</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-400 shrink-0"></i>
                                <span>Migrasi Data Mudah &amp; Didampingi</span>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Head-to-Head Value Cockpit (6 Cols) -->
                    <div class="lg:col-span-6 relative w-full max-w-xl mx-auto lg:max-w-none">
                        <!-- Ambient Spotlight Glow behind the Cockpit Window -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] sm:rounded-[36px] blur-2xl sm:blur-3xl pointer-events-none -z-10">
                        </div>

                        <!-- Floating Card Top-Right (Tablet & Desktop) -->
                        <div
                            class="hidden sm:block absolute -top-4 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.2)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[10.5px] text-slate-400 font-medium">Waktu Terhemat</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-base font-extrabold text-white tabular-nums tracking-tight mt-0.5">3 Jam / Hari</div>
                            <div class="text-[10.5px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                <span>Tanpa Rekonsiliasi</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left (Tablet & Desktop) -->
                        <div
                            class="hidden sm:block absolute -bottom-4 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[155px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[10.5px] text-slate-400 font-medium">Status Database</div>
                            <div class="text-base font-extrabold text-white tabular-nums tracking-tight mt-0.5">All-in-One</div>
                            <div class="text-[10.5px] font-semibold text-[#00C4D8] flex items-center gap-1.5 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <span>1 Sistem Terpadu</span>
                            </div>
                        </div>

                        <!-- Main Cockpit Window Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-3.5 sm:p-5 lg:p-6 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-3 sm:space-y-4 text-white relative z-10 overflow-hidden">
                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <div class="space-y-3">
                                <!-- Old Way Card (Fragmented Software) -->
                                <div class="p-3.5 sm:p-4 rounded-[16px] bg-rose-950/30 border border-rose-500/25 text-white space-y-2.5">
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <span class="font-bold text-rose-400 uppercase tracking-wider flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="x-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span class="truncate">Cara Lama (Terpisah-pisah &amp; Manual)</span>
                                        </span>
                                        <span class="text-rose-300 font-mono font-bold text-[11px] shrink-0">Beban Rp 1.2jt+ /bln</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <div class="p-2 rounded-[10px] bg-rose-900/20 border border-rose-500/20 flex items-start gap-1.5">
                                            <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-300 text-[11px] font-medium leading-snug">Langganan POS: Rp 250rb/bln</span>
                                        </div>
                                        <div class="p-2 rounded-[10px] bg-rose-900/20 border border-rose-500/20 flex items-start gap-1.5">
                                            <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-300 text-[11px] font-medium leading-snug">Software Gudang: Rp 350rb/bln</span>
                                        </div>
                                        <div class="p-2 rounded-[10px] bg-rose-900/20 border border-rose-500/20 flex items-start gap-1.5">
                                            <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-300 text-[11px] font-medium leading-snug">Software Akuntansi: Rp 300rb/bln</span>
                                        </div>
                                        <div class="p-2 rounded-[10px] bg-rose-900/20 border border-rose-500/20 flex items-start gap-1.5">
                                            <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-300 text-[11px] font-medium leading-snug">Rekonsiliasi 3 jam tiap malam</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- COOCA Way Card (All-in-One Synchronized OS) -->
                                <div
                                    class="p-4 sm:p-4.5 rounded-[18px] bg-[#0E1E45]/90 border border-[#00C4D8]/50 text-white space-y-2.5 shadow-xl backdrop-blur-xl">
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <span
                                            class="font-bold text-[#00C4D8] uppercase tracking-wider flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                            <span class="truncate">Sistem Terpadu COOCA</span>
                                        </span>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-extrabold bg-[#00C4D8]/20 text-[#00C4D8] shrink-0 border border-[#00C4D8]/30">
                                            Mulai Rp 0 - Rp 99rb /bln
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <div class="p-2.5 rounded-[12px] bg-white/[0.05] border border-white/10 flex items-start gap-2">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-100 text-[11px] font-medium leading-snug">POS + Resep BOM + Gudang + Jurnal include</span>
                                        </div>
                                        <div class="p-2.5 rounded-[12px] bg-white/[0.05] border border-white/10 flex items-start gap-2">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-100 text-[11px] font-medium leading-snug">Toko online &amp; WA nota otomatis include</span>
                                        </div>
                                        <div class="p-2.5 rounded-[12px] bg-white/[0.05] border border-white/10 flex items-start gap-2">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-100 text-[11px] font-medium leading-snug">Nol detik rekonsiliasi otomatis</span>
                                        </div>
                                        <div class="p-2.5 rounded-[12px] bg-white/[0.05] border border-white/10 flex items-start gap-2">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5"></i>
                                            <span class="text-slate-100 text-[11px] font-medium leading-snug">Enkripsi database aman AES-256</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="pt-2 border-t border-white/10 flex items-center justify-between text-xs text-slate-400 font-mono">
                                <span>Hemat Biaya &amp; Waktu</span>
                                <span class="text-emerald-400 font-semibold">1 Platform Terpadu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. 4 JEBAKAN APLIKASI BISNIS TERPISAH ════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-500">Biaya Tersembunyi</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        4 Dampak Buruk Menggunakan Sistem Terpisah pada Bisnis Anda
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                        Bukan hanya masalah uang langganan, aplikasi yang tidak terintegrasi menguras energi operasional dan
                        membahayakan kelangsungan usaha.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Point 1 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">1. Kebocoran Modal Akibat Selisih Stok & Kas</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Saat kasir tidak terhubung dengan kartu stok gudang, kehilangan barang sering kali baru disadari
                            berminggu-minggu kemudian saat stock opname. Anda tidak bisa melacak apakah barang hilang,
                            rusak, atau lupa diinput.
                        </p>
                    </div>

                    <!-- Point 2 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">2. Laporan Keuangan Selalu Terlambat Masuk</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Pemilik bisnis baru mengetahui apakah toko untung atau rugi di tanggal 15 bulan berikutnya,
                            setelah semua nota kertas diketik ulang. Keputusan bisnis penting jadi terlambat diambil karena
                            data selalu kedaluwarsa.
                        </p>
                    </div>

                    <!-- Point 3 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                            <i data-lucide="users-2" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">3. Karyawan Pusing Menghafal Banyak Akun</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Staf baru harus diajari 3-4 software berbeda: password kasir, password inventory, spreadsheet
                            drive, dan aplikasi perpesanan. Tingkat kesalahan operasional staf meningkat dan onboarding
                            karyawan memakan waktu berminggu-minggu.
                        </p>
                    </div>

                    <!-- Point 4 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="trending-down" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold leading-snug text-balance break-words">4. Sulit Buka Cabang Baru dengan Percaya Diri</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed text-pretty">
                            Setiap membuka outlet baru, kekacauan operasional berlipat ganda. Pemilik tidak berani
                            berekspansi karena satu cabang saja sudah menyita seluruh waktu dan perhatian akibat kontrol
                            sistem yang rapuh.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. TABEL PERBANDINGAN FITUR LENGKAP ══════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Perbandingan
                        Langsung</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        COOCA vs Kombinasi Aplikasi Konvensional
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-xl mx-auto text-pretty">
                        Ketahui mengapa ratusan pelaku usaha beralih ke satu sistem operasi terpadu.
                    </p>
                </div>

                <!-- Desktop & Tablet Comparison Table (hidden md:block) -->
                <div
                    class="hidden md:block rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr
                                    class="border-b border-black/[0.08] dark:border-white/[0.1] bg-black/[0.02] dark:bg-white/[0.02]">
                                    <th class="p-4 sm:p-5 font-bold text-neutral-800 dark:text-neutral-200 w-1/3">Kriteria
                                        Penilaian</th>
                                    <th class="p-4 sm:p-5 font-bold text-red-500 w-1/3">Banyak Aplikasi Terpisah</th>
                                    <th
                                        class="p-4 sm:p-5 font-extrabold text-[#007AFF] dark:text-[#00C2FF] w-1/3 bg-blue-50/50 dark:bg-blue-950/20">
                                        COOCA Business OS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium leading-snug">Biaya Berlangganan</td>
                                    <td class="p-4 sm:p-5 text-neutral-500 leading-snug">Bayar 3-5 vendor berbeda (Rp 1.000.000+ /bln)
                                    </td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10 leading-snug">
                                        1 Paket Terjangkau (Rp 0 - Rp 99rb /bln)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium leading-snug">Sinkronisasi Kasir & Gudang</td>
                                    <td class="p-4 sm:p-5 text-neutral-500 leading-snug">Manual rekap stok atau pakai API rumit</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10 leading-snug">
                                        Otomatis terpotong saat transaksi selesai</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium leading-snug">Pencatatan Jurnal Akuntansi</td>
                                    <td class="p-4 sm:p-5 text-neutral-500 leading-snug">Ketik ulang nota bon ke buku kas / Excel</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10 leading-snug">
                                        Auto-Journal debit-kredit secara real-time</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium leading-snug">Waktu Rekonsiliasi Owner</td>
                                    <td class="p-4 sm:p-5 text-neutral-500 leading-snug">2-3 jam setiap malam menjelang tutup toko</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10 leading-snug">
                                        0 menit (dashboard owner update seketika)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium leading-snug">Dukungan Multi-Outlet / Cabang</td>
                                    <td class="p-4 sm:p-5 text-neutral-500 leading-snug">Biaya tambahan mahal per outlet baru</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10 leading-snug">
                                        Satu kontrol pusat untuk seluruh cabang toko</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Bento Comparison Cards (block md:hidden) -->
                <div class="block md:hidden space-y-3.5">
                    <!-- Item 1: Biaya -->
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="wallet" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>Biaya Berlangganan</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-300 flex items-start gap-2">
                                <i data-lucide="x" class="w-3.5 h-3.5 text-red-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-red-500">Aplikasi Terpisah</span>
                                    <span class="leading-snug">Bayar 3-5 vendor berbeda (Rp 1.000.000+ /bln)</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">COOCA Business OS</span>
                                    <span class="font-semibold leading-snug">1 Paket Terjangkau (Rp 0 - Rp 99rb /bln)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2: Sinkronisasi Kasir & Gudang -->
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>Sinkronisasi Kasir &amp; Gudang</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-300 flex items-start gap-2">
                                <i data-lucide="x" class="w-3.5 h-3.5 text-red-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-red-500">Aplikasi Terpisah</span>
                                    <span class="leading-snug">Manual rekap stok atau pakai API rumit</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">COOCA Business OS</span>
                                    <span class="font-semibold leading-snug">Otomatis terpotong saat transaksi selesai</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3: Pencatatan Jurnal Akuntansi -->
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>Pencatatan Jurnal Akuntansi</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-300 flex items-start gap-2">
                                <i data-lucide="x" class="w-3.5 h-3.5 text-red-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-red-500">Aplikasi Terpisah</span>
                                    <span class="leading-snug">Ketik ulang nota bon ke buku kas / Excel</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">COOCA Business OS</span>
                                    <span class="font-semibold leading-snug">Auto-Journal debit-kredit secara real-time</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item 4: Waktu Rekonsiliasi Owner -->
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="clock" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>Waktu Rekonsiliasi Owner</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-300 flex items-start gap-2">
                                <i data-lucide="x" class="w-3.5 h-3.5 text-red-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-red-500">Aplikasi Terpisah</span>
                                    <span class="leading-snug">2-3 jam setiap malam menjelang tutup toko</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">COOCA Business OS</span>
                                    <span class="font-semibold leading-snug">0 menit (dashboard owner update seketika)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item 5: Dukungan Multi-Outlet -->
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="store" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>Dukungan Multi-Outlet / Cabang</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-[12px] bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-300 flex items-start gap-2">
                                <i data-lucide="x" class="w-3.5 h-3.5 text-red-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-red-500">Aplikasi Terpisah</span>
                                    <span class="leading-snug">Biaya tambahan mahal per outlet baru</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold block text-[11px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">COOCA Business OS</span>
                                    <span class="font-semibold leading-snug">Satu kontrol pusat untuk seluruh cabang toko</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ SEPUTAR PERBANDINGAN & MIGRASI ════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Tanya
                        Jawab</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Pertanyaan Seputar Beralih ke COOCA
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 text-pretty">
                        Ketahui betapa mudahnya memindahkan data bisnis lama Anda ke ekosistem terpadu COOCA.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apakah saya bisa memindahkan data produk dan pelanggan dari aplikasi lama?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Bisa dengan sangat mudah. COOCA menyediakan template impor Excel (XLSX/CSV) untuk produk, harga
                            jual, stok awal, dan database nomor pelanggan. Cukup salin data dari aplikasi lama Anda dan
                            unggah ke COOCA dalam satu klik.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apakah saya harus langsung menggunakan semua modul di COOCA sekaligus?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Tidak. COOCA dirancang bertahap (*modular adoption*). Anda dapat memulai hanya dari kasir POS
                            untuk melayani pelanggan. Setelah tim kasir terbiasa, Anda bisa mulai mengaktifkan pencatatan
                            stok gudang, lalu pembukuan otomatis, dan kampanye WhatsApp promosi secara bertahap sesuai
                            kenyamanan Anda.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span class="min-w-0 flex-1 leading-snug">Apakah ada biaya tersembunyi seperti biaya transaksi atau komisi omzet?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF] shrink-0"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3 text-pretty">
                            Tidak ada komisi penjualan sama sekali. Seluruh omzet dari toko Anda adalah 100% milik Anda.
                            Biaya layanan COOCA transparan sesuai paket yang Anda pilih, tanpa potongan komisi tersembunyi
                            pada transaksi kasir fisik.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. RELATED MODULES ═══════════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Langkah
                            Cerdas</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight leading-snug text-balance break-words">Pelajari Bagian Lain dari Ekosistem COOCA
                        </h3>
                    </div>
                    <a href="{{ route('public.pricing') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline shrink-0">
                        <span>Lihat Rincian Biaya</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    <a href="{{ route('public.bos.overview') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="cpu" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-[#007AFF] transition-colors leading-snug text-balance">Overview BOS</div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Konsep dasar sistem
                            operasi bisnis yang menyatukan seluruh toko.</p>
                    </a>

                    <a href="{{ route('public.bos.how-it-works') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="workflow" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-[#34C759] transition-colors leading-snug text-balance">Cara Kerja Sistem
                        </div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Alur data transaksi
                            kasir ke kartu gudang & laporan owner.</p>
                    </a>

                    <a href="{{ route('public.demo') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="play" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-[#FF9500] transition-colors leading-snug text-balance">Demo Interaktif</div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Uji coba langsung
                            antarmuka kasir dan dasbor tanpa registrasi.</p>
                    </a>

                    <a href="{{ route('public.pricing') }}"
                        class="group p-3.5 sm:p-5 rounded-[16px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-purple-500/40 hover:shadow-md transition-all">
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-[10px] sm:rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold mb-2.5 sm:mb-3">
                            <i data-lucide="tag" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="text-sm sm:text-base font-bold group-hover:text-purple-500 transition-colors leading-snug text-balance">Paket & Harga</div>
                        <p class="text-[11px] sm:text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug text-pretty">Pilihan harga
                            terjangkau mulai dari paket Free tanpa komisi.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. BOTTOM CONVERSION CTA ════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div
                        class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#007AFF]/20 rounded-full blur-[140px] pointer-events-none">
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-8 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">Ambil Kendali Bisnis
                                Anda</span>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-[1.2] text-balance break-words">
                                Sudah Waktunya Menyatukan Operasional Bisnis Anda
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed text-pretty">
                                Mulai dari versi gratis hari ini dan rasakan bagaimana rasanya pulang ke rumah dengan tenang
                                karena seluruh kasir, stok, dan kas toko terpantau rapi.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Daftar COOCA Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.support') }}"
                                class="w-full py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 transition-all min-h-[44px]">
                                <span>Konsultasi dengan Tim Support</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
