@extends('layouts.public_marketing')

@section('title', 'Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia')
@section('description', 'Kisah di balik COOCA: Dibangun untuk menyelesaikan masalah fragmentasi aplikasi bisnis,
    menyatukan kasir, stok, keuangan, dan otomasi dalam satu ekosistem yang terhubung.')
@section('og_title', 'Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia')
@section('og_description', 'Mengapa COOCA dibangun: menyatukan operasional, penjualan, stok, dan pembukuan bisnis dalam
    satu sistem terpadu tanpa fragmentasi data.')
@section('canonical', route('public.about'))
@section('og_type', 'website')
@section('keywords', 'tentang cooca, visi cooca, business operating system indonesia, software erp umkm lokal, filosofi
    produk cooca')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "AboutPage",
      "name": "Tentang COOCA",
      "description": "Filosofi, misi, dan latar belakang pengembangan COOCA sebagai Business Operating System terintegrasi untuk UMKM Indonesia.",
      "url": "{{ route('public.about') }}"
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

                    {{-- Left Column: Origin & Mission Narrative & CTA (6 Cols) --}}
                    <div class="lg:col-span-6 space-y-5 text-left">
                        {{-- Typographic Overline Kicker with Pulse Dot --}}
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Misi &amp; Filosofi Produk
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Mengakhiri Fragmentasi Aplikasi untuk <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Pemilik Usaha Indonesia</span>
                        </h1>

                        {{-- Subtitle Paragraphs --}}
                        <div class="space-y-3 text-slate-300 text-base sm:text-lg leading-relaxed font-normal text-pretty max-w-2xl">
                            <p>
                                COOCA dibangun dari satu kegelisahan nyata: mengapa pemilik bisnis harus berlangganan 5 aplikasi terpisah, mengetik ulang nota ke spreadsheet hingga larut malam, dan tetap tidak tahu persis laba bersih riil usahanya?
                            </p>
                            <p class="text-sm sm:text-base text-slate-300">
                                Kami percaya teknologi bisnis kelas dunia bukan hanya hak korporasi raksasa. Setiap kafe, toko, bengkel, klinik, dan produsen UMKM berhak memiliki satu sistem operasi terpadu yang sederhana, indah, dan terhubung.
                            </p>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba COOCA Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('public.bos.overview') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="cpu" class="w-4 h-4 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                                <span>Pelajari Arsitektur OS</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Satu Sumber Kebenaran Data</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Tanpa Fragmentasi &amp; Input Ganda</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Dibuat Khusus UMKM Indonesia</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Philosophy Diagram Bento Cockpit (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-4 sm:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                                <div>
                                    <span class="text-[11px] uppercase font-mono font-bold text-[#00C4D8] block">Satu Sumber Kebenaran Data</span>
                                    <h3 class="text-sm sm:text-base font-bold text-white mt-0.5 leading-snug">Filosofi Ekosistem Terpadu COOCA</h3>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-emerald-400 bg-emerald-400/15 border border-emerald-400/30 px-2.5 py-1 rounded-full flex items-center gap-1 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span> Terintegrasi
                                </span>
                            </div>

                            <div class="space-y-2.5 text-xs">
                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center shrink-0 border border-[#007AFF]/30">
                                        <i data-lucide="shopping-cart" class="w-4 h-4" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">Channel Penjualan Terbuka</p>
                                        <p class="text-[11px] text-slate-300 truncate">POS Kasir Toko • Toko Online • WhatsApp Orders</p>
                                    </div>
                                </div>

                                <div class="flex justify-center text-slate-500 py-0.5">
                                    <i data-lucide="arrow-down" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/30 flex items-center gap-3 shadow-md">
                                    <div class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center shrink-0 border border-[#00C4D8]/30">
                                        <i data-lucide="cpu" class="w-4 h-4" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">COOCA Business Operating Core</p>
                                        <p class="text-[11px] text-[#00C4D8] truncate">Sinkronisasi Stok Gudang • Jurnal Kas Otomatis • HPP Riil</p>
                                    </div>
                                </div>

                                <div class="flex justify-center text-slate-500 py-0.5">
                                    <i data-lucide="arrow-down" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                                        <i data-lucide="line-chart" class="w-4 h-4" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">Kendali Pemilik Bisnis (Owner)</p>
                                        <p class="text-[11px] text-slate-300 truncate">Laporan Laba Riil • Arus Kas Bersih • Keputusan Cepat</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="p-2.5 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 text-[#00C4D8] text-xs font-semibold text-center leading-snug">
                                Tanpa input ganda. Tanpa selisih data antar tim.
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Single Source of Truth</span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Zero Data Fragmentation</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Main Content Sections --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-16 sm:space-y-24">

            {{-- 4 Core Product Principles --}}
            <section class="space-y-8">
                <div class="max-w-2xl space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Prinsip Produk
                    </span>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                        Empat Fondasi Utama di Setiap Baris Kode COOCA
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            1. Ekosistem Terhubung, Bukan Tambal
                            Sulam</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Kami menolak pendekatan aplikasi terpisah yang memaksa Anda menghubungkan API rumit atau
                            menyalin file CSV manual. Di COOCA, penjualan kasir, stok gudang, dan pembukuan jurnal adalah
                            satu kesatuan sejak hari pertama.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="sparkles" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            2. Desain Humanis Tanpa Beban Belajar
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Terinspirasi oleh Apple Human Interface Guidelines, antarmuka COOCA dirancang agar kasir berusia
                            18 tahun maupun pemilik usaha berusia 60 tahun dapat langsung menggunakannya dengan nyaman tanpa
                            perlu membaca buku manual tebal.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            3. Kedaulatan & Kerahasiaan Data
                            Pemilik</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Formula resep, margin keuntungan, daftar supplier distributor, dan database pelanggan Anda
                            adalah rahasia dapur bisnis Anda. Kami mengisolasi data tiap tenant secara ketat dan tidak
                            pernah memonetisasi atau menjual data Anda ke pihak ketiga.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="heart-handshake" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">
                            4. Harga Jujur Tanpa Jebakan Fitur
                            Terkunci</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Kami percaya pada transparansi harga. Fitur operasional esensial tersedia dapat diakses sejak
                            awal agar usaha mikro dapat langsung beroperasi secara profesional tanpa dibebani biaya
                            langganan yang mencekik modal awal.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Who We Serve (Target Audience) --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Siapa yang Kami Layani
                    </span>
                    <h2
                        class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Dirancang untuk Bisnis Mandiri yang Siap Naik Kelas
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Kuliner & Restoran (F&B)
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Kedai kopi, kafe,
                            resto
                            keluarga, dan cloud kitchen yang ingin mengontrol HPP bahan baku dan cetak tiket dapur KOT
                            otomatis.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Toko Retail & Kelontong
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Minimarket dan
                            toko fashion
                            yang mengelola ribuan SKU barang, barcode scanner, multi-satuan dus/pcs, dan rekap kasbon.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Bengkel & Otomotif</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Bengkel motor dan
                            mobil yang
                            butuh SPK digital, lacak histori plat nomor, stok sparepart & oli, serta bagi hasil komisi
                            montir.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Usaha Laundry</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Laundry kiloan
                            dan dry
                            cleaning yang memerlukan timbangan desimal akurat, nomor rak baju, dan notifikasi WA cucian siap
                            jemput.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Manufaktur & Pabrikasi
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Konveksi, mebel,
                            dan makanan
                            olahan yang mengolah bahan mentah dengan formula Bill of Materials (BOM) dan kontrol HPP riil.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Bisnis Jasa & Servis</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Salon, servis AC
                            panggilan,
                            studio foto, dan konsultan yang butuh booking kalender reservasi serta invoice termin DP
                            bertahap.</p>
                    </div>
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
                        <span>Solusi Sistem Operasi Bisnis Indonesia</span>
                    </div>
                    <h3
                        class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                        Bersiap Mengambil Kendali Penuh Atas Bisnis Anda?
                    </h3>
                    <p class="text-sm text-slate-300 leading-relaxed text-pretty">
                        Tinggalkan kerumitan spreadsheet dan nikmati kejelasan finansial bisnis Anda dengan ekosistem COOCA
                        yang saling terhubung.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Mulai Pakai COOCA</span>
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
