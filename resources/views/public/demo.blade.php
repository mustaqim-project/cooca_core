@extends('layouts.public_marketing')

@section('title', 'Coba Demo Interaktif COOCA: Simulasi Kasir & Ekosistem Bisnis')
@section('description', 'Uji coba langsung antarmuka kasir cepat, manajemen stok resep bahan baku, dan laporan laba bersih COOCA tanpa instalasi dan tanpa biaya.')
@section('keywords', 'demo cooca, coba aplikasi kasir gratis, simulasi software pos, demo erp umkm, uji coba kasir toko')

@push('seo')
    <link rel="canonical" href="{{ route('public.demo') }}">
    <meta property="og:title" content="Coba Demo Interaktif COOCA: Simulasi Kasir & Ekosistem Bisnis">
    <meta property="og:description" content="Eksplorasi modul kasir, pemotongan stok otomatis, dan dasbor pemilik bisnis COOCA secara langsung.">
    <meta property="og:url" content="{{ route('public.demo') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Coba Demo Interaktif COOCA: Simulasi Kasir & Ekosistem Bisnis">
    <meta name="twitter:description" content="Rasakan kemudahan antarmuka Apple Bento HIG COOCA dari smartphone atau laptop Anda.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Live Demo COOCA",
      "description": "Simulasi interaktif kasir dan sistem operasi bisnis COOCA.",
      "url": "{{ route('public.demo') }}"
    }
    </script>
@endpush

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span aria-hidden="true">/</span>
                <span>Produk</span>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Live Demo</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Headline & Value Proposition --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="play" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Uji Coba Langsung Tanpa Beban</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Rasakan Kemudahan COOCA di Layar Anda Tanpa Instalasi
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Buktikan sendiri betapa ringannya kasir kasir kilat, akuratnya pemotongan stok bahan baku per porsi, dan jernihnya laporan keuangan COOCA langsung dari perangkat yang Anda gunakan saat ini.
                    </p>

                    {{-- Tangible Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Tanpa perlu kartu kredit atau komitmen bayar</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Bisa dicoba di HP Android, iPad, maupun laptop</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Data simulasi siap pakai untuk berbagai industri</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Data uji coba dapat direset bersih dengan 1 klik</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba Demo Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20ingin%20jadwalkan%20demo%20privat%20COOCA" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="video" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                            <span>Minta Demo Panduan Video Call</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Interactive Station Simulator Card --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Simulasi Kasir & Dasbor Aktif</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 px-2.5 py-0.5 rounded-full">
                                Sandbox Siap
                            </span>
                        </div>

                        {{-- Sandbox Steps Preview --}}
                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold">1</div>
                                    <div>
                                        <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kasir POS Transaksi</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Coba scan barang & cetak nota simulasi</p>
                                    </div>
                                </div>
                                <span class="text-emerald-600 font-semibold">Tersedia</span>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">2</div>
                                    <div>
                                        <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Potong Stok Bahan Baku</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Lihat stok resep gramatur berkurang</p>
                                    </div>
                                </div>
                                <span class="text-emerald-600 font-semibold">Tersedia</span>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-purple-500/10 text-purple-600 flex items-center justify-center font-bold">3</div>
                                    <div>
                                        <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Laporan Laba Rugi Riil</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Cek margin kotor & omzet harian otomatis</p>
                                    </div>
                                </div>
                                <span class="text-emerald-600 font-semibold">Tersedia</span>
                            </div>
                        </div>

                        {{-- Direct Sandbox Button --}}
                        <div class="pt-2">
                            <a href="{{ route('register') }}" class="w-full h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center justify-center gap-2 shadow-sm transition">
                                <span>Buka Akses Demo Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4 Interactive Stations Detail --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Apa yang Akan Anda Pelajari
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Empat Stasiun Eksplorasi dalam Sesi Demo COOCA
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">1. Alur Transaksi Kasir POS Cepat</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Coba menambahkan produk dengan barcode, pilih modifier topping, masukkan diskon toko, pisah tagihan antar tamu (split bill), dan cetak struk pembayaran dalam hitungan detik.
                        </p>
                    </div>

                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">2. Manajemen Resep & Inventaris Otomatis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Lihat bagaimana bahan mentah (biji kopi, susu, kain, sparepart) terpotong otomatis di kartu stok begitu kasir menekan tombol bayar, tanpa perlu rekap fisik berulang.
                        </p>
                    </div>

                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">3. Notifikasi WhatsApp & Otomasi Pelanggan</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Simulasikan pengiriman nota digital ke nomor WhatsApp pelanggan, pengingat jadwal servis rutin, serta notifikasi saat cucian laundry telah selesai disetrika.
                        </p>
                    </div>

                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="line-chart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">4. Dasbor Eksekutif Pemilik Bisnis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Pantau pergerakan arus kas masuk dan keluar secara live, margin keuntungan kotor, jam-jam penjualan tersibuk, dan performa kasir per cabang toko.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 3-Step Demo Workflow --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Alur Uji Coba
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Tiga Langkah Mudah Menguji Coba COOCA
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Buka Akses Sandbox</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Daftarkan akun gratis dengan email aktif dan pilih jenis industri usaha Anda.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-amber-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Coba Transaksi Kasir</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Gunakan contoh produk yang telah kami sediakan untuk mencoba proses checkout.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Siap untuk Jualan Riil</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Hapus data uji coba kapan saja dan masukkan katalog produk toko Anda yang sebenarnya.</p>
                    </div>
                </div>
            </section>

            {{-- Demo FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Seputar Uji Coba Demo</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah saya harus menginstal aplikasi dari Play Store / App Store?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Tidak wajib. COOCA berbasis web modern (PWA) yang dapat langsung dibuka dari browser Chrome, Safari, atau Firefox di HP, tablet, maupun laptop Anda tanpa menghabiskan memori perangkat.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah data transaksi simulasi bisa dibersihkan sebelum mulai jualan sungguhan?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Bisa. Tersedia tombol "Reset Data Simulasi" di menu pengaturan. Anda dapat membersihkan seluruh transaksi percobaan dengan aman tanpa menghapus akun toko Anda.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah saya bisa meminta bantuan tim COOCA mendemokan via Google Meet / Zoom?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Sangat bisa. Anda dapat menghubungi tim kami lewat WhatsApp untuk menjadwalkan sesi onboarding privat secara gratis bersama tim spesialis implementasi kami.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Coba Sekarang Tanpa Komitmen Pembayaran</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Mulai Eksplorasi Demo COOCA dalam 60 Detik
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun gratis hari ini dan rasakan perbedaan sistem operasi bisnis yang dirancang dengan standar kualitas tinggi untuk bisnis Anda.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Akses Demo Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Lihat Paket Harga</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
