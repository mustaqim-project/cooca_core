@extends('layouts.public_marketing')

@section('title', 'Coba Demo Interaktif COOCA: Simulasi Kasir & Ekosistem Bisnis')
@section('description', 'Uji coba langsung antarmuka kasir cepat, manajemen stok resep bahan baku, dan laporan laba bersih COOCA tanpa instalasi dan tanpa biaya.')
@section('og_title', 'Coba Demo Interaktif COOCA: Simulasi Kasir & Ekosistem Bisnis')
@section('og_description', 'Eksplorasi modul kasir, pemotongan stok otomatis, dan dasbor pemilik bisnis COOCA secara langsung.')
@section('canonical', route('public.demo'))
@section('og_type', 'website')
@section('keywords', 'demo cooca, coba aplikasi kasir gratis, simulasi software pos, demo erp umkm, uji coba kasir toko')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Live Demo COOCA",
      "description": "Simulasi interaktif kasir dan sistem operasi bisnis COOCA.",
      "url": "{{ route('public.demo') }}"
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- 1. HERO SECTION (Unified Bento Cockpit - No Breadcrumb) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section
            class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Dual Ambient Glowing Blurs --}}
            <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none">
            </div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none">
            </div>

            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    {{-- Left Column: Headline & Value Proposition (6 Cols) --}}
                    <div class="lg:col-span-6 space-y-5 text-left">
                        {{-- Typographic Overline Kicker with Pulse Dot --}}
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Uji Coba Langsung Tanpa Beban
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Rasakan Kemudahan COOCA di Layar Anda <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Tanpa Instalasi</span>
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Buktikan sendiri betapa ringannya kasir kilat, akuratnya pemotongan stok bahan baku per porsi, dan jernihnya laporan keuangan COOCA langsung dari perangkat yang Anda gunakan saat ini.
                        </p>

                        {{-- Tangible Highlights Bento Tiles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-left w-full">
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Tanpa kartu kredit atau komitmen bayar</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="smartphone" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Bisa dicoba di HP, iPad, &amp; laptop</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="boxes" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Data simulasi siap pakai multi-industri</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Data uji coba dapat direset bersih 1 klik</span>
                            </div>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba Demo Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20ingin%20jadwalkan%20demo%20privat%20COOCA"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="video" class="w-4 h-4 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                                <span>Minta Demo Panduan Video Call</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Langsung di Browser</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Multi-Device Android &amp; iOS</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Data Demo Siap Pakai</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Interactive Station Simulator Card (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-4 sm:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse shrink-0"></span>
                                    <span class="text-xs font-mono font-bold text-white truncate">Simulasi Kasir &amp; Dasbor Aktif</span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-1 rounded-full shrink-0">
                                    Sandbox Siap
                                </span>
                            </div>

                            {{-- Sandbox Steps Preview --}}
                            <div class="space-y-2.5 text-xs">
                                <div
                                    class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold shrink-0 border border-[#007AFF]/30">
                                            1</div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-white truncate">Kasir POS Transaksi</p>
                                            <p class="text-[11px] text-slate-300 truncate">Coba scan barang &amp; cetak nota simulasi</p>
                                        </div>
                                    </div>
                                    <span class="text-emerald-400 font-semibold shrink-0 text-[11px]">Tersedia</span>
                                </div>

                                <div
                                    class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold shrink-0 border border-amber-500/30">
                                            2</div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-white truncate">Potong Stok Bahan Baku</p>
                                            <p class="text-[11px] text-slate-300 truncate">Lihat stok resep gramatur berkurang</p>
                                        </div>
                                    </div>
                                    <span class="text-emerald-400 font-semibold shrink-0 text-[11px]">Tersedia</span>
                                </div>

                                <div
                                    class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold shrink-0 border border-purple-400/30">
                                            3</div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-white truncate">Laporan Laba Rugi Riil</p>
                                            <p class="text-[11px] text-slate-300 truncate">Cek margin kotor &amp; omzet harian otomatis</p>
                                        </div>
                                    </div>
                                    <span class="text-emerald-400 font-semibold shrink-0 text-[11px]">Tersedia</span>
                                </div>
                            </div>

                            {{-- Direct Sandbox Button --}}
                            <div class="pt-1">
                                <a href="{{ route('register') }}"
                                    class="w-full h-11 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 transition">
                                    <span>Buka Akses Demo Sekarang</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                </a>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="play" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Interactive Sandbox: <strong class="text-emerald-400">Ready</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Reset Demo 1-Klik</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Main Content Sections --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-16 sm:space-y-24">

            {{-- 4 Interactive Stations Detail --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Apa yang Akan Anda Pelajari
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                        Empat Stasiun Eksplorasi dalam Sesi Demo COOCA
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="shopping-cart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">1. Alur Transaksi Kasir POS Cepat</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Coba menambahkan produk dengan barcode, pilih modifier topping, masukkan diskon toko, pisah
                            tagihan antar tamu (split bill), dan cetak struk pembayaran dalam hitungan detik.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">2. Manajemen Resep & Inventaris
                            Otomatis</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Lihat bagaimana bahan mentah (biji kopi, susu, kain, sparepart) terpotong otomatis di kartu stok
                            begitu kasir menekan tombol bayar, tanpa perlu rekap fisik berulang.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">3. Notifikasi WhatsApp & Otomasi
                            Pelanggan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Simulasikan pengiriman nota digital ke nomor WhatsApp pelanggan, pengingat jadwal servis rutin,
                            serta notifikasi saat cucian laundry telah selesai disetrika.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <i data-lucide="line-chart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">4. Dasbor Eksekutif Pemilik Bisnis
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Pantau pergerakan arus kas masuk dan keluar secara live, margin keuntungan kotor, jam-jam
                            penjualan tersibuk, dan performa kasir per cabang toko.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 3-Step Demo Workflow --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Alur Uji Coba
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Tiga Langkah Mudah Menguji Coba COOCA
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Buka Akses Sandbox</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Daftarkan akun gratis dengan
                            email aktif dan pilih jenis industri usaha Anda.</p>
                    </div>
                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <div class="text-xs font-mono font-bold text-amber-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Coba Transaksi Kasir</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Gunakan contoh produk yang
                            telah kami sediakan untuk mencoba proses checkout.</p>
                    </div>
                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Siap untuk Jualan Riil</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Hapus data uji coba kapan
                            saja dan masukkan katalog produk toko Anda yang sebenarnya.</p>
                    </div>
                </div>
            </section>

            {{-- Demo FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-[1.2] text-balance break-words">Pertanyaan Seputar Uji Coba
                        Demo</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apakah saya harus menginstal aplikasi dari Play Store / App Store?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Tidak wajib. COOCA berbasis web modern (PWA) yang dapat langsung dibuka dari browser Chrome,
                            Safari, atau Firefox di HP, tablet, maupun laptop Anda tanpa menghabiskan memori perangkat.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apakah data transaksi simulasi bisa dibersihkan sebelum mulai jualan sungguhan?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Bisa. Tersedia tombol "Reset Data Simulasi" di menu pengaturan. Anda dapat membersihkan seluruh
                            transaksi percobaan dengan aman tanpa menghapus akun toko Anda.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apakah saya bisa meminta bantuan tim COOCA mendemokan via Google Meet / Zoom?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Sangat bisa. Anda dapat menghubungi tim kami lewat WhatsApp untuk menjadwalkan sesi onboarding
                            privat secara gratis bersama tim spesialis implementasi kami.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Final CTA --}}
            <section
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-5">
                <div
                    class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                        <span>Coba Sekarang Tanpa Komitmen Pembayaran</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                        Mulai Eksplorasi Demo COOCA dalam 60 Detik
                    </h3>
                    <p class="text-sm text-slate-300 max-w-xl mx-auto leading-relaxed text-pretty">
                        Daftar akun gratis hari ini dan rasakan perbedaan sistem operasi bisnis yang dirancang dengan
                        standar kualitas tinggi untuk bisnis Anda.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Akses Demo Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm">
                            <span>Lihat Paket Harga</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
