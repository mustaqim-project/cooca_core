@extends('layouts.public_marketing')

@section('title', 'Software Analitik Konten Media Sosial & Konversi Penjualan | COOCA')
@section('description', 'Evaluasi performa konten media sosial bisnis Anda secara objektif. Pantau jangkauan impresi, engagement rate, klik tautan katalog toko, hingga konversi omzet penjualan nyata.')
@section('keywords', 'software analitik konten, laporan performa media sosial, metrik engagement instagram facebook, analitik konversi konten penjualan, evaluasi promosi toko')

@push('seo')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Content Performance Analytics",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Modul analitik performa konten pemasaran multi-kanal dan pelacakan konversi klik menuju transaksi pembelian toko.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pelacakan jangkauan (reach), impresi, dan interaksi (engagement) per postingan",
    "Pengukuran klik tautan produk yang mengarah ke kasir toko atau pemesanan online",
    "Perbandingan performa efektivitas antara Instagram, Facebook, dan WhatsApp",
    "Rekomendasi jam tayang terbaik (Best Time to Post) berbasis data audiens riil",
    "Laporan ringkas yang dapat diekspor untuk evaluasi strategi pemasaran bulanan"
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Bagaimana modul ini mengetahui bahwa sebuah postingan menghasilkan penjualan di toko?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA menggunakan tautan produk cerdas (smart product links) dan kupon kode unik yang disematkan pada setiap postingan. Ketika audiens mengklik link tersebut dan berbelanja di website atau kasir POS toko, sistem secara otomatis mengaitkan nilai transaksi tersebut ke postingan asal."
      }
    },
    {
      "@type": "Question",
      "name": "Apa perbedaan analitik konten COOCA dengan analitik bawaan di aplikasi Instagram?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Analitik bawaan media sosial hanya memperlihatkan metrik interaksi (likes, share, comments) yang terisolasi. COOCA menghubungkan data interaksi tersebut langsung dengan ketersediaan stok produk di gudang dan angka penjualan nyata di modul Akuntansi & Finance."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara mengetahui waktu terbaik untuk memposting konten toko saya?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sistem menganalisis riwayat postingan Anda sebelumnya dan memetakan pada hari apa dan jam berapa konten menerima interaksi dan klik pesanan tertinggi, memberikan rekomendasi jam tayang yang terbukti efektif untuk audiens Anda."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah data laporan konten ini bisa diunduh untuk bahan evaluasi bersama tim?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mengunduh ringkasan performa konten bulanan dalam format PDF atau Excel lengkap dengan metrik jangkauan dan peringkat konten terbaik."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-indigo-500/10 via-pink-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><span class="text-neutral-500 dark:text-neutral-400">Content Automation</span></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Analitik Konten & Performa</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/40 text-indigo-700 dark:text-indigo-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                    <span>Content Performance & Conversion Intelligence</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Ketahui Konten Mana yang Benar-Benar <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-pink-600 to-rose-500">Menghasilkan Penjualan</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Jumlah likes dan komentar tidak ada artinya jika tidak berujung pada pembelian. COOCA memperlihatkan metrik performa media sosial yang terhubung langsung dengan klik katalog produk dan transaksi kasir toko Anda.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Demo Analitik Konten</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.content.creation') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Buat Konten Baru</span>
                    </a>
                </div>

                {{-- Key Trust Specs --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Metrik Utama</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Reach & Klik Order</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Korelasi Penjualan</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Terkait Omzet POS</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Waktu Optimal</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Rekomendasi Jam Tayang</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live Content Performance Dashboard UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Dashboard Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-indigo-500/20 text-indigo-400">
                                <i data-lucide="trending-up" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <div class="font-bold text-neutral-200">Performa Konten Pemasaran</div>
                                <div class="text-[10px] text-neutral-500">30 Hari Terakhir • Seluruh Kanal Aktif</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">+24.8% Klik Toko</span>
                    </div>

                    {{-- 4 Metric Tiles --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 my-3">
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                            <div class="text-[10px] text-neutral-400">Total Jangkauan</div>
                            <div class="text-xs sm:text-sm font-bold text-white font-mono mt-0.5">184.2k</div>
                            <div class="text-[9px] text-emerald-400 mt-1">↑ 32% Akun Unik</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                            <div class="text-[10px] text-neutral-400">Engagement</div>
                            <div class="text-xs sm:text-sm font-bold text-pink-400 font-mono mt-0.5">4.6%</div>
                            <div class="text-[9px] text-neutral-400 mt-1">Interaksi Sehat</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                            <div class="text-[10px] text-neutral-400">Klik Link Toko</div>
                            <div class="text-xs sm:text-sm font-bold text-blue-400 font-mono mt-0.5">1.420</div>
                            <div class="text-[9px] text-neutral-400 mt-1">Menuju Katalog</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                            <div class="text-[10px] text-neutral-400">Konversi Penjualan</div>
                            <div class="text-xs sm:text-sm font-bold text-emerald-400 font-mono mt-0.5">Rp 42.8M</div>
                            <div class="text-[9px] text-neutral-400 mt-1">124 Transaksi POS</div>
                        </div>
                    </div>

                    {{-- Top Performing Posts List --}}
                    <div class="space-y-1.5 text-xs text-left">
                        <div class="text-[10px] uppercase font-mono text-neutral-500 px-1">Konten Penghasil Penjualan Tertinggi:</div>

                        {{-- Item 1 --}}
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded bg-pink-500/20 text-pink-400">
                                    <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                </span>
                                <div>
                                    <div class="font-medium text-white text-[11px] truncate">Reels: Resep Kopi Susu Aren Otentik</div>
                                    <div class="text-[10px] text-neutral-500">42.8k Tayang • 38 Pembelian Langsung di POS</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-emerald-400 font-mono text-[11px]">Rp 18.2M</div>
                                <div class="text-[9px] text-neutral-500">ROI Tertinggi</div>
                            </div>
                        </div>

                        {{-- Item 2 --}}
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded bg-blue-500/20 text-blue-400">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                </span>
                                <div>
                                    <div class="font-medium text-white text-[11px] truncate">Feed: Promo Beli 2 Croissant Gratis Kopi</div>
                                    <div class="text-[10px] text-neutral-500">28.1k Jangkauan • 52 Kupon Terpakai di Kasir</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-emerald-400 font-mono text-[11px]">Rp 15.6M</div>
                                <div class="text-[9px] text-neutral-500">Kupon Kasir</div>
                            </div>
                        </div>
                    </div>

                    {{-- Best Time to Post Insight --}}
                    <div class="mt-3 p-2.5 rounded-xl bg-indigo-950/30 border border-indigo-800/40 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-indigo-400"></i>
                            <div>
                                <span class="text-white font-medium text-[11px]">Waktu Tayang Paling Menghasilkan:</span>
                                <span class="text-neutral-400 text-[10px]"> Rabu & Jumat jam 15:00 - 17:00 WIB</span>
                            </div>
                        </div>
                        <span class="text-indigo-400 text-[10px] font-mono">Berdasarkan Data Toko</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Jebakan Vanity Metrics yang Menipu --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-3">Tantangan Evaluasi Pemasaran</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Apakah Anda Terjebak Menghitung Likes daripada Menghitung Omzet Penjualan?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Banyak bisnis bangga memiliki video yang ditonton puluhan ribu kali, namun toko fisiknya tetap sepi karena konten tidak dirancang untuk mendorong konversi nyata.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="thumbs-up" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Banyak Likes, Tapi Toko Sepi</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Konten hanya menghibur tanpa kejelasan ajakan bertindak (Call to Action) dan tanpa link produk, sehingga penonton tidak pernah datang ke toko atau memesan barang.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Tidak Tahu Konten yang Berhasil</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ketika penjualan toko mendadak ramai minggu ini, Anda tidak tahu postingan mana yang menjadi pemicunya sehingga tidak bisa mengulangi strategi promosi tersebut.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="split" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Kanal Medsos Berjalan Sendiri</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Data statistik Instagram terpisah dari data penjualan kasir toko. Tim pemasaran dan tim kasir berdebat tanpa memiliki satu sumber kebenaran angka yang sama.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE ANALYTICS CAPABILITIES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-3">Kemampuan Analitik Konten COOCA</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Mengukur Dampak Nyata Setiap Konten Terhadap Bisnis
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Memberikan wawasan strategis agar setiap rupiah biaya produksi konten memberikan hasil penjualan optimal.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: Pelacakan Klik ke Kasir POS (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="mouse-pointer" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Pelacakan Konversi ke Kasir & Toko Online
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Setiap postingan dilengkapi dengan tautan produk dan voucher diskon yang unik. Saat konsumen menggunakan kupon tersebut di kasir outlet fisik atau checkout online, sistem mencatat nilai transaksi tersebut ke postingan terkait secara transparan.
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Transparansi Omzet:</span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i> Lacak rupiah penjualan per postingan promosi
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Jam Tayang Terbaik (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-pink-100 dark:bg-pink-950 flex items-center justify-center text-pink-600 dark:text-pink-400">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Waktu Tayang Paling Efektif
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ketahui jam berapa audiens Anda paling aktif membuka penawaran. Sistem menyajikan rekomendasi waktu penayangan yang terbukti menghasilkan interaksi tertinggi.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Rekomendasi Cerdas</span>
                    <span class="text-pink-500 font-bold">Jam 15:00 - 17:00 Optimal</span>
                </div>
            </div>

            {{-- Bento Card 3: Evaluasi Format Konten (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Perbandingan Format Konten</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Bandingkan efektivitas antara video Reels pendek, postingan foto carousel, dan pesan siaran WhatsApp untuk mengetahui preferensi audiens Anda.
                </p>
            </div>

            {{-- Bento Card 4: Performa Antar Kanal (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Efisiensi Kanal Pemasaran</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Ketahui apakah pelanggan Anda lebih banyak datang dari Instagram atau respon pesan siaran WhatsApp untuk menentukan alokasi fokus promosi.
                </p>
            </div>

            {{-- Bento Card 5: Laporan Evaluasi Bulanan (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Ekspor Laporan Cepat</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Unduh rekapan metrik performa konten dalam format PDF yang rapi untuk bahan rapat evaluasi bulanan bersama tim pemasaran dan pemilik usaha.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Dari Data Analisis Hingga Perbaikan Konten --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-400 text-xs font-semibold mb-3 border border-indigo-500/30">
                    <span>Siklus Pembelajaran & Peningkatan Konten</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Bagaimana Data Menyempurnakan Pemasaran Toko
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Setiap interaksi audiens dijadikan panduan untuk menyusun kampanye promosi berikutnya.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Konten Tayang di Medsos</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Materi promosi diterbitkan otomatis ke Instagram dan Facebook lengkap dengan tautan pesanan produk yang unik.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-600/30 text-pink-400 flex items-center justify-center font-bold text-xs border border-pink-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Pencatatan Interaksi</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Sistem mengumpulkan data penayangan video, jumlah like, komentar, dan jumlah audiens yang mengklik tautan belanja ke toko Anda.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Validasi Transaksi Kasir</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Pembeli yang datang ke toko atau memesan online menukarkan kupon promosi. Nilai rupiah belanja langsung dikaitkan ke postingan tersebut.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Strategi Konten Baru</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Tim pemasaran mengetahui formula promo yang terbukti menghasilkan omzet dan mereplikasi kesuksesannya di jadwal kalender bulan depan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Analitik Konten</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana modul ini mengetahui bahwa sebuah postingan menghasilkan penjualan di toko?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    COOCA menggunakan tautan produk cerdas (smart product links) dan kupon kode unik yang disematkan pada setiap postingan. Ketika audiens mengklik link tersebut dan berbelanja di website atau kasir POS toko, sistem secara otomatis mengaitkan nilai transaksi tersebut ke postingan asal.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apa perbedaan analitik konten COOCA dengan analitik bawaan di aplikasi Instagram?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Analitik bawaan media sosial hanya memperlihatkan metrik interaksi (likes, share, comments) yang terisolasi. COOCA menghubungkan data interaksi tersebut langsung dengan ketersediaan stok produk di gudang dan angka penjualan nyata di modul Akuntansi & Finance.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana cara mengetahui waktu terbaik untuk memposting konten toko saya?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Sistem menganalisis riwayat postingan Anda sebelumnya dan memetakan pada hari apa dan jam berapa konten menerima interaksi dan klik pesanan tertinggi, memberikan rekomendasi jam tayang yang terbukti efektif untuk audiens Anda.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah data laporan konten ini bisa diunduh untuk bahan evaluasi bersama tim?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Bisa. Anda dapat mengunduh ringkasan performa konten bulanan dalam format PDF atau Excel lengkap dengan metrik jangkauan dan peringkat konten terbaik.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Otomasi Konten</p>
            </div>
            <a href="{{ route('public.content.creation') }}" class="text-xs sm:text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Kembali ke Studio Kreasi</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.content.publishing') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Publikasi Otomatis</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Antrean tayang multi-kanal yang dipantau oleh modul analitik.</p>
            </a>

            <a href="{{ route('public.content.calendar') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Kalender Konten</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Jadwalkan ulang konten berkinerja tinggi ke slot tanggal berikutnya.</p>
            </a>

            <a href="{{ route('public.erp.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Analitik Bisnis Eksekutif</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pantau dampak keseluruhan pemasaran terhadap laba bersih perusahaan.</p>
            </a>

            <a href="{{ route('public.omnichannel.social-media') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-indigo-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-indigo-600 transition-colors">Manajemen Akun Medsos</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kelola integrasi akun Instagram dan Facebook Page bisnis terpadu.</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Mulai Evaluasi Pemasaran Anda dengan Metrik Penjualan Nyata
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Hentikan tebak-tebakan performa konten dan dapatkan kejelasan ROI setiap postingan media sosial Anda bersama COOCA Content Analytics.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Analitik Konten
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Strategi Pemasaran
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
