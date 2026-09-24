@extends('layouts.public_marketing')

@section('title', 'Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia')
@section('description', 'Kisah di balik COOCA: Dibangun untuk menyelesaikan masalah fragmentasi aplikasi bisnis, menyatukan kasir, stok, keuangan, dan otomasi dalam satu ekosistem yang terhubung.')
@section('og_title', 'Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia')
@section('og_description', 'Mengapa COOCA dibangun: menyatukan operasional, penjualan, stok, dan pembukuan bisnis dalam satu sistem terpadu tanpa fragmentasi data.')
@section('canonical', route('public.about'))
@section('og_type', 'website')
@section('keywords', 'tentang cooca, visi cooca, business operating system indonesia, software erp umkm lokal, filosofi produk cooca')

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
        {{-- HERO SECTION (Midnight #060B1E Full-Bleed - Type A Full Viewport) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section
            class="relative bg-[#060B1E] text-white min-h-[calc(100svh-84px)] lg:flex lg:items-center py-12 lg:py-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8 w-full">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span>Perusahaan</span>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Tentang Kami</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
                    {{-- Left: Origin & Mission (Mobile Center, Desktop Left ~ 5 Cols) --}}
                    <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Misi &amp; Filosofi Kami
                            </p>

                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Mengakhiri Fragmentasi Aplikasi Bisnis untuk <span class="text-[#00C4D8]">Pemilik Usaha
                                    Indonesia</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            COOCA dibangun dari satu kegelisahan nyata: mengapa seorang pemilik bisnis harus berlangganan 5
                            aplikasi berbeda, mengetik ulang nota ke spreadsheet hingga larut malam, dan tetap tidak tahu
                            persis berapa keuntungan bersih usahanya di akhir bulan?
                        </p>

                        <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            Kami percaya bahwa teknologi bisnis kelas dunia bukan hanya hak korporasi raksasa bermodal
                            miliaran. Setiap kafe, toko kelontong, bengkel, klinik, dan produsen UMKM berhak memiliki satu
                            sistem operasi terpadu yang sederhana, indah, dan saling terhubung.
                        </p>

                        {{-- CTAs (Centered on Mobile, Row on Desktop) --}}
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('register') }}"
                                class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Mulai Coba COOCA Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('public.bos.overview') }}"
                                class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] backdrop-blur-sm min-h-[48px]">
                                <span>Pelajari Arsitektur OS</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right: Philosophy Diagram (Bento Apple HIG Card - 7 Cols ~ 58%) --}}
                    <div class="lg:col-span-7">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-6 sm:p-7 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-6">
                            <div class="border-b border-white/10 pb-4">
                                <span class="text-xs uppercase font-mono font-bold text-[#00C4D8] block">Satu Sumber
                                    Kebenaran Data</span>
                                <h3 class="text-base font-bold text-white mt-0.5 leading-snug">Filosofi Ekosistem COOCA</h3>
                            </div>

                            <div class="space-y-3.5 text-xs">
                                <div
                                    class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                                        <i data-lucide="shopping-cart" class="w-4 h-4" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white truncate">Channel Penjualan Terbuka</p>
                                        <p class="text-[11px] text-slate-300 truncate">POS Kasir Toko • Storefront Online • WhatsApp
                                        </p>
                                    </div>
                                </div>

                                <div class="flex justify-center text-slate-500">
                                    <i data-lucide="arrow-down" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                </div>

                                <div
                                    class="p-3.5 rounded-[16px] bg-[#060B1E]/90 border border-white/15 flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-[10px] bg-white/10 text-[#00C4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="cpu" class="w-4 h-4" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white truncate">COOCA Business Operating Core</p>
                                        <p class="text-[11px] text-slate-300 truncate">Sinkronisasi Stok Gudang • Jurnal Kas •
                                            Otomasi Pesanan</p>
                                    </div>
                                </div>

                                <div class="flex justify-center text-slate-500">
                                    <i data-lucide="arrow-down" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                </div>

                                <div
                                    class="p-3.5 rounded-[16px] bg-[#060B1E]/60 border border-white/10 flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-[10px] bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0">
                                        <i data-lucide="line-chart" class="w-4 h-4" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white truncate">Kendali Pemilik Bisnis (Owner)</p>
                                        <p class="text-[11px] text-slate-300 truncate">Laporan Laba Riil • Arus Kas Bersih •
                                            Keputusan Cepat</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="p-3 rounded-[12px] bg-[#007AFF]/15 border border-[#007AFF]/30 text-[#00C4D8] text-xs font-semibold text-center leading-snug">
                                Tanpa input ganda. Tanpa selisih data.
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
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">1. Ekosistem Terhubung, Bukan Tambal
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">2. Desain Humanis Tanpa Beban Belajar
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">3. Kedaulatan & Kerahasiaan Data
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
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">4. Harga Jujur Tanpa Jebakan Fitur
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
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Dirancang untuk Bisnis Mandiri yang Siap Naik Kelas
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Kuliner & Restoran (F&B)</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Kedai kopi, kafe, resto
                            keluarga, dan cloud kitchen yang ingin mengontrol HPP bahan baku dan cetak tiket dapur KOT
                            otomatis.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Toko Retail & Kelontong</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Minimarket dan toko fashion
                            yang mengelola ribuan SKU barang, barcode scanner, multi-satuan dus/pcs, dan rekap kasbon.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Bengkel & Otomotif</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Bengkel motor dan mobil yang
                            butuh SPK digital, lacak histori plat nomor, stok sparepart & oli, serta bagi hasil komisi
                            montir.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Usaha Laundry</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Laundry kiloan dan dry
                            cleaning yang memerlukan timbangan desimal akurat, nomor rak baju, dan notifikasi WA cucian siap
                            jemput.</p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Manufaktur & Pabrikasi</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Konveksi, mebel, dan makanan
                            olahan yang mengolah bahan mentah dengan formula Bill of Materials (BOM) dan kontrol HPP riil.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-slate-50 dark:bg-[#070A14] border border-slate-200/60 dark:border-white/5 space-y-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">Bisnis Jasa & Servis</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">Salon, servis AC panggilan,
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
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
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
