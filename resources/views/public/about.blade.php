@extends('layouts.public_marketing')

@section('title', 'Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia')
@section('description', 'Kisah di balik COOCA: Dibangun untuk menyelesaikan masalah fragmentasi aplikasi bisnis, menyatukan kasir, stok, keuangan, dan otomasi dalam satu ekosistem yang terhubung.')
@section('keywords', 'tentang cooca, visi cooca, business operating system indonesia, software erp umkm lokal, filosofi produk cooca')

@push('seo')
    <link rel="canonical" href="{{ route('public.about') }}">
    <meta property="og:title" content="Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia">
    <meta property="og:description" content="Mengapa COOCA dibangun: menyatukan operasional, penjualan, stok, dan pembukuan bisnis dalam satu sistem terpadu tanpa fragmentasi data.">
    <meta property="og:url" content="{{ route('public.about') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Tentang COOCA: Visi Business Operating System untuk UMKM Indonesia">
    <meta name="twitter:description" content="Kisah, filosofi produk, dan komitmen COOCA memberdayakan pelaku usaha mandiri di seluruh Indonesia.">

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
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span aria-hidden="true">/</span>
                <span>Perusahaan</span>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Tentang Kami</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Origin & Mission --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="compass" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Misi & Filosofi Kami</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Mengakhiri Fragmentasi Aplikasi Bisnis untuk Pemilik Usaha Indonesia
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        COOCA dibangun dari satu kegelisahan nyata: mengapa seorang pemilik bisnis harus berlangganan 5 aplikasi berbeda, mengetik ulang nota ke spreadsheet hingga larut malam, dan tetap tidak tahu persis berapa keuntungan bersih usahanya di akhir bulan?
                    </p>

                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Kami percaya bahwa teknologi bisnis kelas dunia bukan hanya hak korporasi raksasa bermodal miliaran. Setiap kafe, toko kelontong, bengkel, klinik, dan produsen UMKM berhak memiliki satu sistem operasi terpadu yang sederhana, indah, dan saling terhubung.
                    </p>

                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba COOCA Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('public.bos.overview') }}" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <span>Pelajari Arsitektur OS</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Philosophy Diagram (Bento Apple HIG Card) --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 sm:p-7 shadow-sm space-y-6">
                        <div class="border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <span class="text-xs uppercase font-mono font-bold text-[#007AFF] block">Satu Sumber Kebenaran Data</span>
                            <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-0.5">Filosofi Ekosistem COOCA</h3>
                        </div>

                        <div class="space-y-3.5 text-xs">
                            <div class="p-3.5 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <i data-lucide="shopping-cart" class="w-4 h-4" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Channel Penjualan Terbuka</p>
                                    <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">POS Kasir Toko • Storefront Online • WhatsApp</p>
                                </div>
                            </div>

                            <div class="flex justify-center text-neutral-300 dark:text-neutral-700">
                                <i data-lucide="arrow-down" class="w-4 h-4" aria-hidden="true"></i>
                            </div>

                            <div class="p-3.5 rounded-[16px] bg-neutral-900 text-white border border-neutral-800 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-white/10 text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="cpu" class="w-4 h-4" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-white">COOCA Business Operating Core</p>
                                    <p class="text-[11px] text-neutral-400">Sinkronisasi Stok Gudang • Jurnal Kas • Otomasi Pesanan</p>
                                </div>
                            </div>

                            <div class="flex justify-center text-neutral-300 dark:text-neutral-700">
                                <i data-lucide="arrow-down" class="w-4 h-4" aria-hidden="true"></i>
                            </div>

                            <div class="p-3.5 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="line-chart" class="w-4 h-4" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kendali Pemilik Bisnis (Owner)</p>
                                    <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Laporan Laba Riil • Arus Kas Bersih • Keputusan Cepat</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] text-xs font-semibold text-center">
                            Tanpa input ganda. Tanpa selisih data.
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4 Core Product Principles --}}
            <section class="space-y-8">
                <div class="max-w-2xl space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Prinsip Produk
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Empat Fondasi Utama di Setiap Baris Kode COOCA
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">1. Ekosistem Terhubung, Bukan Tambal Sulam</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kami menolak pendekatan aplikasi terpisah yang memaksa Anda menghubungkan API rumit atau menyalin file CSV manual. Di COOCA, penjualan kasir, stok gudang, dan pembukuan jurnal adalah satu kesatuan sejak hari pertama.
                        </p>
                    </div>

                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">2. Desain Humanis Tanpa Beban Belajar</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Terinspirasi oleh Apple Human Interface Guidelines, antarmuka COOCA dirancang agar kasir berusia 18 tahun maupun pemilik usaha berusia 60 tahun dapat langsung menggunakannya dengan nyaman tanpa perlu membaca buku manual tebal.
                        </p>
                    </div>

                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">3. Kedaulatan & Kerahasiaan Data Pemilik</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Formula resep, margin keuntungan, daftar supplier distributor, dan database pelanggan Anda adalah rahasia dapur bisnis Anda. Kami mengisolasi data tiap tenant secara ketat dan tidak pernah memonetisasi atau menjual data Anda ke pihak ketiga.
                        </p>
                    </div>

                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="heart-handshake" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">4. Harga Jujur Tanpa Jebakan Fitur Terkunci</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kami percaya pada transparansi harga. Fitur operasional esensial tersedia dapat diakses sejak awal agar usaha mikro dapat langsung beroperasi secara profesional tanpa dibebani biaya langganan yang mencekik modal awal.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Who We Serve (Target Audience) --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Siapa yang Kami Layani
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Dirancang untuk Bisnis Mandiri yang Siap Naik Kelas
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kuliner & Restoran (F&B)</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Kedai kopi, kafe, resto keluarga, dan cloud kitchen yang ingin mengontrol HPP bahan baku dan cetak tiket dapur KOT otomatis.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Toko Retail & Kelontong</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Minimarket dan toko fashion yang mengelola ribuan SKU barang, barcode scanner, multi-satuan dus/pcs, dan rekap kasbon.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Bengkel & Otomotif</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Bengkel motor dan mobil yang butuh SPK digital, lacak histori plat nomor, stok sparepart & oli, serta bagi hasil komisi montir.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Usaha Laundry</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Laundry kiloan dan dry cleaning yang memerlukan timbangan desimal akurat, nomor rak baju, dan notifikasi WA cucian siap jemput.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Manufaktur & Pabrikasi</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Konveksi, mebel, dan makanan olahan yang mengolah bahan mentah dengan formula Bill of Materials (BOM) dan kontrol HPP riil.</p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Bisnis Jasa & Servis</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Salon, servis AC panggilan, studio foto, dan konsultan yang butuh booking kalender reservasi serta invoice termin DP bertahap.</p>
                    </div>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Solusi Sistem Operasi Bisnis Indonesia</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Bersiap Mengambil Kendali Penuh Atas Bisnis Anda?
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Tinggalkan kerumitan spreadsheet dan nikmati kejelasan finansial bisnis Anda dengan ekosistem COOCA yang saling terhubung.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Mulai Pakai COOCA</span>
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
