@extends('layouts.public_marketing')

@section('title', 'Software Laundry Kiloan & Satuan: Timbangan, Rak & Notifikasi WA | COOCA')
@section('description', 'Solusi aplikasi kasir laundry kiloan dan dry cleaning satuan. Input timbangan desimal presisi, penomoran rak baju, pantau status cuci multi-tahap, dan notifikasi WhatsApp otomatis saat cucian siap diambil.')
@section('keywords', 'software laundry kiloan, aplikasi kasir laundry, sistem manajemen laundry, software dry cleaning satuan, aplikasi laundry sepatu helm, cetak nota laundry bluetooth, notifikasi wa laundry')

@push('seo')
    <link rel="canonical" href="{{ route('public.solutions.laundry') }}">
    <meta property="og:title" content="Software Laundry Kiloan & Satuan: Timbangan, Rak & Notifikasi WA | COOCA">
    <meta property="og:description" content="Kelola cucian kiloan dan satuan tanpa takut baju tertukar. Dilengkapi nomor rak penyimpanan dan kirim pesan WhatsApp otomatis saat baju beres.">
    <meta property="og:url" content="{{ route('public.solutions.laundry') }}">
    <meta property="og:type" content="product">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software Laundry Kiloan & Satuan: Timbangan, Rak & Notifikasi WA | COOCA">
    <meta name="twitter:description" content="Sistem kasir laundry modern: timbangan digital, penomoran hanger, pelacak status pengerjaan, dan kontrol deterjen/parfum.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Laundry Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem operasi bisnis laundry kiloan, dry cleaning satuan, dan laundry sepatu untuk kontrol status cuci, rak pakaian, dan notifikasi WhatsApp.",
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
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Laundry & Dry Cleaning</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Narrative & CTA --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-500/10 dark:bg-cyan-400/15 border border-cyan-500/20 text-xs font-semibold text-cyan-700 dark:text-cyan-300">
                        <i data-lucide="droplets" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Sistem Kasir & Operasional Laundry Terpadu</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Timbangan Presisi, Lokasi Rak Teratur, & Pelanggan Tahu Saat Baju Selesai
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Kendalikan operasional laundry kiloan maupun layanan satuan tanpa drama baju tertukar. Mulai dari timbang desimal presisi, penomoran hanger rak, pantau tahapan cuci-kering-setrika, hingga notifikasi WhatsApp otomatis saat cucian siap diambil.
                    </p>

                    {{-- Tangible Value Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Input timbangan kiloan desimal (contoh: 4.85 kg)</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Penomoran rak & hanger anti baju tertukar</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Pesan WhatsApp otomatis saat selesai setrika</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Layanan khusus satuan: Jas, Sepatu, Bed Cover</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba Kasir Laundry</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Laundry%20COOCA" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                            <span>Konsultasi Laundry via WA</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Simulated Apple Bento Laundry Terminal --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Nota #LND-9012 - Ibu Maya</span>
                            </div>
                            <span class="text-[11px] font-semibold text-cyan-600 bg-cyan-500/10 px-2.5 py-0.5 rounded-full">
                                Rak Simpan: B-04
                            </span>
                        </div>

                        {{-- Digital Scale Readout Display --}}
                        <div class="p-4 rounded-[16px] bg-neutral-900 text-white flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-mono text-neutral-400 block">Timbangan Digital</span>
                                <div class="text-2xl font-bold font-mono text-cyan-400">4.85 <span class="text-sm text-neutral-300">Kg</span></div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-neutral-400">Tarif / Kg</span>
                                <p class="text-xs font-mono font-semibold text-white">Rp 8.000 / Kg</p>
                            </div>
                        </div>

                        {{-- Stepper Progress Status --}}
                        <div class="space-y-2">
                            <span class="text-[11px] font-semibold text-[#6E6E73] dark:text-[#86868B] block">Progres Pengerjaan:</span>
                            <div class="grid grid-cols-4 gap-1.5 text-center text-[10px] font-semibold">
                                <div class="p-1.5 rounded-lg bg-emerald-500/15 text-emerald-600 border border-emerald-500/30">1. Cuci ✓</div>
                                <div class="p-1.5 rounded-lg bg-emerald-500/15 text-emerald-600 border border-emerald-500/30">2. Kering ✓</div>
                                <div class="p-1.5 rounded-lg bg-cyan-500/20 text-cyan-600 border border-cyan-500/40">3. Setrika ⏳</div>
                                <div class="p-1.5 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-400">4. Selesai</div>
                            </div>
                        </div>

                        {{-- Order Line Items --}}
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Cuci Komplit Reguler (4.85 Kg)</p>
                                    <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Parfum: Lavender Floral • Selesai Besok 17:00</p>
                                </div>
                                <span class="font-mono font-bold">Rp 38.800</span>
                            </div>

                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">1x Bed Cover King Size (Satuan)</p>
                                    <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Plastik packing kedap udara</p>
                                </div>
                                <span class="font-mono font-bold">Rp 35.000</span>
                            </div>
                        </div>

                        {{-- Total Summary --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50/80 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-800 space-y-2 text-xs">
                            <div class="flex justify-between text-[#1D1D1F] dark:text-[#F5F5F7] font-bold">
                                <span>Total Tagihan (Status: Lunas)</span>
                                <span class="font-mono text-sm text-[#007AFF]">Rp 73.800</span>
                            </div>
                        </div>

                        {{-- WhatsApp Status Trigger --}}
                        <div class="p-3 rounded-[12px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                Notifikasi WA "Siap Ambil" Otomatis
                            </span>
                            <span class="text-[10px] bg-emerald-500/20 px-2 py-0.5 rounded">Aktif</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Deep Sector Pain Points --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Operasional Laundry
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Kendala Khas Usaha Laundry yang Sering Merusak Reputasi
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">01</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pakaian Pelanggan Tertukar atau Hilang</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Baju menumpuk tanpa sistem tagging nomor rak yang jelas, membuat kasir kebingungan mencari paket cucian saat pelanggan datang menjemput.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">02</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pelanggan Bolak-Balik Tanya Status Cuci</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            WhatsApp admin dibanjiri chat "Apakah cucian saya sudah selesai?", menyita waktu staf yang seharusnya fokus menyetrika dan membungkus pakaian.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">03</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Boros Deterjen & Bahan Kimia Parfum</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Penggunaan konsentrat sabun dan bibit parfum tidak pernah dihitung takaran standarnya, membuat biaya operasional membengkak tanpa disadari owner.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Kapabilitas Khusus Laundry
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Fitur Spesifik untuk Laundry Kiloan & Dry Cleaning Satuan
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-cyan-500/10 text-cyan-600 flex items-center justify-center">
                            <i data-lucide="scale" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Timbangan Kiloan Desimal Akurat</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Input berat timbangan hingga dua angka di belakang koma (misal 3.45 kg). Total tagihan terhitung otomatis sesuai tarif layanan (Reguler, Kilat, Express).
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="tag" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Penomoran Rak & Slot Hanger</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Tetapkan nomor rak penyimpanan (misal: Rak A-02 atau Hanger 15). Kasir dapat menemukan pakaian pesanan pelanggan dalam 5 detik saat penjemputan.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Notifikasi WhatsApp "Siap Ambil"</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Saat operator mengubah status menjadi 'Selesai', sistem secara instan mengirim pesan WhatsApp ke pelanggan bahwa pakaian sudah bersih, wangi, dan siap dijemput.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Layanan Satuan & Dry Cleaning</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Formulir pencatatan khusus untuk jas, gaun pesta, karpet, helm, sepatu, boneka, dan bed cover lengkap dengan catatan kondisi fisik awal barang sebelum dicuci.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="truck" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Manajemen Antar-Jemput (Delivery)</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Catat alamat penjemputan, jadwal kurir, dan biaya ongkir tambahan. Status pengantaran terintegrasi langsung dengan nomor kontak WhatsApp pemesan.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="flask-conical" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kontrol Stok Bahan Kimia Cuci</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Pantau konsumsi deterjen cair, softener, dan pelicin pakaian. Dapatkan estimasi biaya operasional kimia per kilogram pakaian untuk menjaga margin laba.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Alur Ekosistem Laundry
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Dari Penimbangan, Proses Cuci, Hingga Penjemputan Baju
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Timbang & Cetak Nota</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Kasir timbang pakaian, pilih parfum, cetak label tag anti air.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-cyan-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Proses Cuci & Kering</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Operator mengupdate status tahapan mesin secara realtime.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Setrika & Masuk Rak</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pakaian diplastik rapi dan diletakkan di nomor slot rak tujuan.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-purple-500">Langkah 04</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Notifikasi WhatsApp</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Sistem kirim info siap ambil otomatis ke WhatsApp pelanggan.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Umum Seputar COOCA Laundry</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana mencetak label tag anti air pada pakaian pelanggan?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA mendukung printer label thermal standar. Anda dapat mencetak stiker atau kertas tag kecil berisi Nomor Nota, Nama Pelanggan, dan Jumlah Potong pakaian yang disematkan ke keranjang atau pakaian agar tidak tertukar selama proses mencuci.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah pelanggan bisa membayar uang muka (DP) atau bayar saat cucian diambil?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Bisa. COOCA mendukung 3 status pembayaran: Bayar Lunas di Awal, Bayar DP Sebagian, atau Bayar Saat Ambil (COD). Kasir dapat dengan cepat melihat sisa tagihan yang harus dilunasi pelanggan saat baju diambil.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana jika usaha laundry saya memiliki beberapa cabang gerai penerimaan?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Sistem multi-outlet COOCA memungkinkan Anda membuka banyak titik drop-point/outlet penerimaan cucian, sementara proses pencucian dan setrika dilakukan di workshop utama (central laundry). Pergerakan cucian antar outlet tercatat rapi.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA membutuhkan perangkat komputer khusus?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Tidak. Kasir laundry dapat dioperasikan langsung dari smartphone Android, tablet, iPad, maupun laptop yang sudah Anda miliki saat ini, serta dapat terhubung ke printer Bluetooth portabel 58mm.
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
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">POS & Kasir Cepat</h4>
                    </a>
                    <a href="{{ route('public.omnichannel.whatsapp') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Omnichannel</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Notifikasi WhatsApp</h4>
                    </a>
                    <a href="{{ route('public.solutions.services') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Bisnis Jasa & Servis</h4>
                    </a>
                    <a href="{{ route('public.solutions.retail') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Retail & Swalayan</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Tingkatkan Kepercayaan Pelanggan Usaha Laundry Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Mulai Kelola Laundry Lebih Rapi, Cepat, dan Bebas Drama
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun COOCA sekarang. Nikmati kemudahan input timbangan digital, penomoran rak baju, dan notifikasi WhatsApp instan tanpa ribet.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Coba Software Laundry Gratis</span>
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
