@extends('layouts.public_marketing')

@section('title', 'Software Kasir Retail & Minimarket Multi-Outlet Terintegrasi | COOCA')
@section('description', 'Solusi aplikasi kasir dan manajemen toko retail, minimarket, butik, dan swalayan. Barcode scanner cepat, konversi multi-satuan (dus ke pcs), harga grosir bertingkat, dan kontrol ribuan SKU.')
@section('keywords', 'software retail terintegrasi, pos toko minimarket, aplikasi kasir barcode, software swalayan, aplikasi kasir kelontong grosir, kasir toko pakaian, stok opname barcode')

@push('seo')
    <link rel="canonical" href="{{ route('public.solutions.retail') }}">
    <meta property="og:title" content="Software Kasir Retail & Minimarket Multi-Outlet Terintegrasi | COOCA">
    <meta property="og:description" content="Kelola ribuan SKU barang, scan barcode secepat kilat, konversi satuan dus ke pcs otomatis, dan kendalikan stok antar cabang toko.">
    <meta property="og:url" content="{{ route('public.solutions.retail') }}">
    <meta property="og:type" content="product">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software Kasir Retail & Minimarket Multi-Outlet Terintegrasi | COOCA">
    <meta name="twitter:description" content="Sistem kasir toko retail modern: barcode scanner kamera/USB, harga grosir bertingkat, dan rekap kasbon pelanggan via WhatsApp.">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Retail Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem operasi bisnis retail, toko kelontong modern, minimarket, dan butik untuk kontrol ribuan SKU, barcode, dan multi-cabang.",
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
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Retail & Toko</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Narrative & CTA --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="store" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Sistem Kasir & Operasional Retail Modern</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Kelola Ribuan SKU Barang, Scan Barcode Cepat, & Bebas Selisih Stok Toko
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Tinggalkan antrean kasir yang lambat dan kerumitan mencatat buku stok manual. COOCA Retail menghubungkan scan barcode scanner, konversi satuan dus ke pcs, harga grosir bertingkat, hingga kartu stok perpetual dalam satu layar yang mudah dipahami kasir.
                    </p>

                    {{-- Tangible Value Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Scan barcode USB & kamera smartphone</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Multi-satuan otomatis (Karton → Slop → Pcs)</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Harga grosir otomatis per jumlah pembelian</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Rekap kasbon & pengingat via WhatsApp</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba Retail POS</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Retail%20dan%20Toko%20COOCA" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                            <span>Konsultasi Retail via WA</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Simulated Apple Bento Retail Terminal --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kasir Kasir-01 - Siap Scan</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 px-2.5 py-0.5 rounded-full">
                                Barcode Aktif
                            </span>
                        </div>

                        {{-- Barcode Scanner Simulation Deck --}}
                        <div class="p-3.5 rounded-[14px] bg-neutral-900 text-white flex items-center justify-between text-xs font-mono">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="scan-barcode" class="w-5 h-5 text-[#007AFF] animate-pulse" aria-hidden="true"></i>
                                <div>
                                    <p class="text-[10px] text-neutral-400">Barcode EAN-13 Terdeteksi</p>
                                    <p class="font-bold text-emerald-400">8992761001235</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-white/10 px-2 py-0.5 rounded text-neutral-300">Auto Add</span>
                        </div>

                        {{-- Scanned Cart Items with Multi-Unit Trigger --}}
                        <div class="space-y-3">
                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">Minyak Goreng Premium 2L</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Konversi: 1 Karton (6 Pouch) @ Rp 33.500</p>
                                    </div>
                                    <span class="font-mono text-emerald-600 font-bold">Rp 201.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-neutral-500 font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Tipe Harga: Grosir Kartonan</span>
                                    <span>Sisa Gudang: 48 Karton</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">Sabun Cuci Piring Refill 750ml</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Qty: 3 Pcs (Tier Diskon Toko)</p>
                                    </div>
                                    <span class="font-mono">Rp 40.500</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-neutral-500 font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Hemat Rp 4.500 (Grosir 3+)</span>
                                    <span>Sisa Rak: 18 Pcs</span>
                                </div>
                            </div>
                        </div>

                        {{-- Total Summary --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50/80 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-800 space-y-2 text-xs">
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Total Item</span>
                                <span class="font-mono">2 Barang (7 Fisik Satuan)</span>
                            </div>
                            <div class="flex justify-between text-[#1D1D1F] dark:text-[#F5F5F7] font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <span>Total Belanja</span>
                                <span class="font-mono text-sm text-[#007AFF]">Rp 241.500</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="qr-code" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">QRIS Dinamis</span>
                            </div>
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="credit-card" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Kasbon / Tunai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Deep Sector Pain Points --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Operasional Toko Retail
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Kendala Klasik yang Sering Menghambat Pertumbuhan Toko Retail
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">01</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Stok Selisih Misterius & Tak Terlacak</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Barang di rak sering hilang atau tidak sinkron dengan catatan kasir karena tidak adanya kartu stok digital yang mencatat mutasi masuk, keluar, dan retur barang.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">02</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Ribetnya Menjual Dus Menjadi Eceran</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kulakan dari distributor dalam bentuk karton atau slop, tetapi kasir kesulitan membagi harga saat pembeli membeli eceran per botol atau sachet tanpa mengacaukan stok.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">03</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kasbon Pelanggan Macet & Buku Hilang</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Mencatat hutang belanja pelanggan langganan di buku kertas rentan robek, hilang, atau nominalnya diperdebatkan saat ditagih, sehingga modal kerja toko tergerus.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Kapabilitas Khusus Retail
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Fitur Kasir & Inventaris Cepat untuk Retail Modern
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="scan-barcode" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Scan Barcode Super Responsif</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Dukung barcode scanner USB, wireless Bluetooth, hingga pemindai kamera bawaan smartphone kasir. Temukan SKU barang dalam hitungan milidetik tanpa mengetik manual.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="boxes" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Konversi Multi-Satuan Otomatis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Beli 1 karton (isi 24 botol) dari supplier. Jual per karton, per slop (isi 6), atau eceran per botol. Stok dan nilai HPP terhitung proporsional secara otomatis.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="tag" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Tingkat Harga Grosir Fleksibel</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Atur harga bertingkat: beli 1 pcs Rp 15.000, beli 3 pcs Rp 14.000, dan beli 1 lusin Rp 13.000. Kasir tidak perlu menghafal diskon karena sistem otomatis mendeteksi kuantitas.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="refresh-cw" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Stok Opname Tanpa Tutup Toko</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Lakukan cycle counting berkala per rak atau per kategori saat jam sepi. Kasir tetap bisa melayani transaksi belanja tanpa perlu menutup gerai toko seharian.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="bell" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Peringatan Reorder Point Cerdas</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Hindari rak kosong untuk produk fast-moving. Sistem memberi sinyal peringatan dini saat persediaan mendekati batas minimum dan membuat draft PO pembelian ke supplier.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="message-square" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Buku Kasbon & Tagihan WhatsApp</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Catat transaksi hutang piutang pelanggan tetap dengan aman. Kirim rekapan nota belanja dan pengingat pembayaran langsung ke nomor WhatsApp pelanggan dalam 1 klik.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Alur Kerja Retail
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Siklus Penjualan, Stok, dan Restock Terintegrasi Otomatis
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Penerimaan Barang PO</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Barang masuk dari distributor di-scan dan langsung menambah kuota stok rak.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Transaksi Kasir Cepat</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Kasir scan barcode, kupon loyalty member terpasang, nota digital tercetak.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-amber-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kartu Stok Perpetual</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Stok fisik berkurang secara live dan HPP penjualan tercatat ke jurnal akuntansi.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-purple-500">Langkah 04</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Analisa Fast-Moving</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Owner melihat laporan laba kotor dan produk paling laku untuk restock berikutnya.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Umum Seputar COOCA Retail</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah saya harus membeli alat scanner barcode khusus?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Tidak wajib. COOCA mendukung kamera smartphone atau tablet Anda sebagai barcode scanner langsung. Namun jika toko Anda melayani antrean padat, Anda dapat menghubungkan barcode scanner USB atau Bluetooth standar (1D/2D) tanpa perlu install driver tambahan.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana mencetak label barcode untuk barang yang tidak memiliki barcode pabrik?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA memiliki generator barcode internal. Untuk produk lokal, jajanan kiloan, pakaian, atau produk custom, sistem akan men-generate kode barcode unik yang dapat langsung Anda cetak menggunakan printer label stiker thermal.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA bisa mentransfer stok barang antar cabang toko retail?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Bisa. Tersedia fitur Mutasi / Transfer Stok Antar Gudang & Cabang. Cabang A dapat mengajukan permintaan transfer, Cabang B melakukan konfirmasi pengiriman barang, dan stok di kedua cabang otomatis ter-update saat barang diterima di tujuan.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana keamanan uang kas kasir dari potensi kecurangan karyawan?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA menerapkan sistem Blind Close Cashier. Saat tutup shift, kasir harus menghitung uang tunai fisik di laci tanpa diberitahu total nominal di sistem. Jika terjadi selisih lebih atau kurang, owner akan langsung menerima notifikasi rekap detail per kasir.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-neutral-200/80 dark:border-neutral-800 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.inventory') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Stok</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Manajemen Gudang & SKU</h4>
                    </a>
                    <a href="{{ route('public.erp.pos') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Kasir</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Point of Sale Cepat</h4>
                    </a>
                    <a href="{{ route('public.solutions.fnb') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">F&B & Kuliner</h4>
                    </a>
                    <a href="{{ route('public.solutions.workshop') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Bengkel & Servis</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Tersedia untuk Komputer Kasir, Laptop, Tablet, & HP Android</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Modernisasi Toko Retail Anda dengan Sistem Kasir Terintegrasi
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Rasakan kemudahan transaksi scan barcode secepat kilat, akurasi stok otomatis, dan pembukuan toko yang rapi tanpa perlu aplikasi terpisah.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Coba Retail POS Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Lihat Paket Berlangganan</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
