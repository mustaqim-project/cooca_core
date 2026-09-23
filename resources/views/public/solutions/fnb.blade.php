@extends('layouts.public_marketing')

@section('title', 'Software Restoran & POS Kafe Terintegrasi Resep & Dapur | COOCA')
@section('description', 'Solusi sistem operasi bisnis kuliner, kafe, resto, dan cloud kitchen. Manajemen meja, Kitchen Order Ticket (KOT), potong stok resep otomatis (BOM), dan pantau HPP porsi real-time.')
@section('keywords', 'software restoran, pos fnb indonesia, aplikasi kasir kafe, aplikasi kasir restoran, software manajemen resto, kitchen display system, food cost hpp, manajemen meja resto')

@push('seo')
    <link rel="canonical" href="{{ route('public.solutions.fnb') }}">
    <meta property="og:title" content="Software Restoran & POS Kafe Terintegrasi Resep & Dapur | COOCA">
    <meta property="og:description" content="Sistem operasi F&B terintegrasi: kasir meja cepat, tiket dapur KOT, pemotongan stok bahan baku otomatis, dan laporan HPP akurat.">
    <meta property="og:url" content="{{ route('public.solutions.fnb') }}">
    <meta property="og:type" content="product">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software Restoran & POS Kafe Terintegrasi Resep & Dapur | COOCA">
    <meta name="twitter:description" content="Kelola operasional restoran dan kafe dari meja, dapur, gudang bahan baku, hingga laporan keuangan dalam satu sistem.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA F&B Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem operasi bisnis kuliner, kafe, restoran, dan cloud kitchen untuk kontrol meja, dapur, bahan baku resep, dan HPP.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
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
                <span>Solusi Industri</span>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">F&B & Restoran</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Narrative & CTA --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 dark:bg-amber-400/15 border border-amber-500/20 text-xs font-semibold text-amber-700 dark:text-amber-300">
                        <i data-lucide="utensils" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Sistem Operasi Bisnis Kuliner & Kafe</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Dari Meja Tamu, Antrean Dapur, hingga Stok Resep Terhubung Tanpa Selisih
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Kendalikan operasional kafe, kedai kopi, restoran, dan cloud kitchen Anda dalam satu sistem. Setiap porsi pesanan di kasir otomatis memotong takaran bahan baku di dapur, mencetak tiket masak, dan menghitung laba bersih tanpa kalkulasi manual.
                    </p>

                    {{-- Tangible Value Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Potong stok bahan baku per gram & ml</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Cetak tiket KOT terpisah (Kitchen & Bar)</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Split bill & manajemen visual meja</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Pantau food cost & HPP porsi akurat</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba F&B POS</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20FnB%20dan%20Restoran%20COOCA" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                            <span>Tanya Solusi Kuliner</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Simulated Apple Bento Restaurant Deck --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Meja #04 - Sedang Makan</span>
                            </div>
                            <span class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2.5 py-0.5 rounded-full">
                                4 Menu Aktif
                            </span>
                        </div>

                        {{-- Order Line Items & BOM Recipe Deduction Preview --}}
                        <div class="space-y-3">
                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">2x Iced Caramel Macchiato</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Less ice, oatmilk substitution</p>
                                    </div>
                                    <span class="font-mono">Rp 76.000</span>
                                </div>
                                <div class="pt-2 border-t border-dashed border-neutral-200 dark:border-neutral-700/60 flex items-center justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="layers" class="w-3 h-3" aria-hidden="true"></i>
                                        BOM: Kopi 36g, Susu 300ml, Oatmilk
                                    </span>
                                    <span>Stok Potong Presisi</span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">1x Beef Truffle Pasta</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Tiket Dapur #KOT-082 Terkirim</p>
                                    </div>
                                    <span class="font-mono">Rp 68.000</span>
                                </div>
                                <div class="pt-2 border-t border-dashed border-neutral-200 dark:border-neutral-700/60 flex items-center justify-between text-[11px] text-amber-600 dark:text-amber-400 font-mono">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="chef-hat" class="w-3 h-3" aria-hidden="true"></i>
                                        Status: Sedang Dimasak
                                    </span>
                                    <span>Estimasi 8 Menit</span>
                                </div>
                            </div>
                        </div>

                        {{-- Calculation Breakdown --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50/80 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-800 space-y-2 text-xs">
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Subtotal Pesanan</span>
                                <span class="font-mono">Rp 144.000</span>
                            </div>
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Pajak Resto (PB1 10%)</span>
                                <span class="font-mono">Rp 14.400</span>
                            </div>
                            <div class="flex justify-between text-[#1D1D1F] dark:text-[#F5F5F7] font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <span>Total Tagihan Meja</span>
                                <span class="font-mono text-sm text-[#007AFF]">Rp 158.400</span>
                            </div>
                        </div>

                        {{-- Operational Quick Actions --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">KOT Dapur Otomatis</span>
                            </div>
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="split" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Split Bill Siap</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Deep Sector Pain Points --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Nyata Industri F&B
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Kendala Operasional yang Menggerus Margin Restoran dan Kafe
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">01</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Bocoran Bahan Baku & Food Waste</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Biji kopi, sirup, daging, dan susu sering habis lebih cepat dari omzet penjualan karena takaran porsi tidak terstandar dan ketiadaan sistem potong gramatur resep.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">02</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Tiket Dapur Tertukar Saat Rush Hour</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kertas bon kasir yang dibawa lari pelayan ke dapur sering hilang atau salah baca catatan pesanan khusus, memicu komplain tamu dan makanan terpaksa dibuang.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">03</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Laba Semu Akibat Fluktuasi Harga Bahan</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Resto terlihat ramai setiap malam tetapi saldo kas tidak bertambah karena HPP menu tidak di-update ketika harga telur, cabai, minyak, atau daging di pasar melonjak.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Kapabilitas Khusus F&B
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Fitur Operasional yang Dibangun Khusus untuk Kuliner
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Resep & Potong Stok Bahan (BOM)</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Definisikan resep standar menu Anda. Setiap 1 porsi nasi goreng atau 1 cup latte terjual, stok beras, telur, daging, dan biji kopi terpotong otomatis di inventaris.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="printer" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kitchen Order Ticket (KOT) Terpisah</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Sistem secara pintar membagi order: makanan tercetak di dapur utama (kitchen), sementara minuman langsung tercetak di printer station barista bar secara simultan.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="layout-grid" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Denah Meja Visual & Split Bill</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Pantau meja terisi, kosong, atau sedang reservasi. Kasir dapat dengan cepat memindahkan meja tamu, menggabungkan meja, atau memisahkan tagihan pembayaran per orang.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pemesanan Mandiri QR di Meja</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Tamu dapat memindai barcode QR yang ditempel di meja untuk melihat buku menu digital, memesan langsung dari HP, dan bayar lewat QRIS tanpa menunggu staf.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="calculator" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Food Costing & Margin Per Porsi</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Ketahui persis berapa keuntungan bersih dari setiap menu setelah dikurangi biaya bahan baku, bumbu, packaging takeaway, dan biaya operasional per porsi.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="clock" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Shift Kasir & Tutup Buku Anti Selisih</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Pencatatan kas modal awal, penerimaan tunai, QRIS, kartu debit, dan pengeluaran petty cash. Laporan tutup shift kasir tercetak rapi untuk mencegah kebocoran uang kas.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Alur Ekosistem Kuliner
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Bagaimana COOCA Menghubungkan Dapur, Kasir, dan Finansial
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pemesanan Masuk</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pelayan input via tablet atau tamu scan QR mandiri di meja.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-amber-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Tiket Dapur Cetak</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">KOT tercetak di printer koki dapur & bar barista secara otomatis.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Bahan Baku Terpotong</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Inventaris bahan segar di gudang berkurang sesuai gramatur resep.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-purple-500">Langkah 04</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jurnal Kas Masuk</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pembayaran lunas langsung tercatat di laporan laba rugi owner.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Umum Seputar COOCA F&B</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA bisa mencetak ke printer Bluetooth thermal dan LAN dapur?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Ya. COOCA mendukung koneksi printer thermal Bluetooth (58mm dan 80mm) yang biasa digunakan di kasir mobile, serta printer kabel LAN (Ethernet) yang biasa dipasang di dalam area dapur yang bersuhu panas.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana jika menu memiliki varian (contoh: Level Pedas, Less Sugar, Extra Shot)?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA memiliki fitur modifier dan add-on dinamis. Anda dapat menambahkan opsi modifier berbayar (seperti Extra Shot Espresso +Rp 5.000) yang ikut memotong bahan baku tambahan, maupun modifier gratis (seperti Less Sugar / No Ice) yang tercetak jelas sebagai catatan koki/barista.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA cocok untuk usaha kuliner yang memiliki banyak cabang kafe?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Sangat cocok. COOCA didesain multi-outlet secara bawaan. Anda dapat memantau penjualan seluruh cabang dari satu dashboard, mendistribusikan bahan baku dari central kitchen (gudang pusat) ke tiap outlet cabang, dan membandingkan performa omzet per gerai secara real-time.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana cara menangani bahan baku yang basi atau tumpah (spoilage/waste)?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Tersedia fitur Waste & Stock Adjustment. Staf dapur dapat mencatat bahan yang rusak atau tumpah disertai alasan. Sistem akan mencatatnya sebagai beban kerugian operasional sehingga catatan sisa fisik di gudang tetap sinkron dengan laporan keuangan.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-neutral-200/80 dark:border-neutral-800 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.pos') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Kasir</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">POS Kasir Cepat</h4>
                    </a>
                    <a href="{{ route('public.erp.inventory') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Stok</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Manajemen Stok & BOM</h4>
                    </a>
                    <a href="{{ route('public.solutions.retail') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Retail & Swalayan</h4>
                    </a>
                    <a href="{{ route('public.solutions.services') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Bisnis Jasa & Servis</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Tersedia untuk Android, Tablet, Laptop & Printer Kasir</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Tingkatkan Kecepatan Pelayanan Kafe dan Resto Anda Sekarang
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun COOCA hari ini dan nikmati kemudahan mencatat pesanan meja, sinkronisasi tiket dapur, dan kontrol food cost tanpa software rumit.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Coba F&B POS Gratis</span>
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
