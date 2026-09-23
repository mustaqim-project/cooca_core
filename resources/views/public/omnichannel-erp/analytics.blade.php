@extends('layouts.public_marketing')

@section('title', 'Software Analitik Bisnis & Dashboard KPI Pemilik Usaha | COOCA')
@section('description', 'Platform Business Intelligence (BI) dan dashboard eksekutif untuk pemilik bisnis retail, F&B,
    dan jasa. Pantau omzet real-time, margin kotor per SKU, perbandingan cabang, dan jam sibuk toko dalam satu layar.')
@section('keywords', 'software analitik bisnis, dashboard kpi penjualan, laporan performa cabang, business intelligence
    umkm, analisis profit margin produk')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Business Intelligence & Analytics",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Dashboard analitik eksekutif real-time untuk memantau performa penjualan, margin profit, perputaran stok, dan perbandingan antar cabang.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Dashboard ringkasan eksekutif owner yang dapat diakses langsung dari smartphone",
    "Analisis margin keuntungan kotor dan kontribusi laba bersih per SKU produk",
    "Heatmap jam sibuk transaksi untuk optimasi penugasan staf dan stok",
    "Laporan perbandingan kinerja omzet dan efisiensi biaya antar cabang",
    "Deteksi anomali otomatis atas penurunan margin atau lonjakan biaya tak wajar"
  ]
}
</script>
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Apakah data di dashboard analitik COOCA diperbarui secara real-time?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, 100% real-time. Setiap kali kasir menyelesaikan transaksi di outlet atau pesanan online masuk, grafik omzet dan margin di dashboard Anda langsung terupdate di detik yang sama tanpa jeda waktu (zero batch delay)."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah pemilik bisnis bisa membuka dashboard ini lewat smartphone di luar kantor?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat bisa. Dashboard COOCA Analytics dioptimalkan secara mobile-first untuk layar smartphone. Anda dapat memantau denyut nadi seluruh cabang, kas masuk, dan produk terlaris saat bepergian atau liburan."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah manajer cabang bisa melihat data profitabilitas cabang lain?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak bisa, kecuali Anda beri izin. Sistem memiliki pengaturan Role-Based Access Control yang ketat. Manajer cabang hanya dapat melihat data performa tokonya sendiri, sementara data konsolidasi seluruh cabang dan margin rahasia hanya dapat diakses oleh Owner."
      }
    },
    {
      "@type": "Question",
      "name": "Bisakah laporan analitik diekspor untuk bahan rapat mingguan manajemen?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mengunduh seluruh data analitik, grafik perbandingan cabang, dan performa produk ke format PDF eksekutif atau file Excel dalam satu kali klik."
      }
    }
  ]
}
</script>
    @endpush

@section('content')
    <div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

        {{-- Ambient Light Accent --}}
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1300px] h-[480px] bg-gradient-to-b from-amber-500/10 via-orange-500/5 to-transparent blur-3xl pointer-events-none -z-10">
        </div>

        {{-- Breadcrumb --}}
        <nav class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a>
                </li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Analitik Bisnis &
                    Dashboard Owner</li>
            </ol>
        </nav>

        {{-- 1. HERO SECTION (2 Columns) --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-800/40 text-amber-700 dark:text-amber-400 text-xs font-semibold tracking-wide">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                        <span>Executive Cockpit & Commercial Intelligence</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                        Lihat Denyut Nadi Seluruh Bisnis <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 via-orange-600 to-rose-500">Dalam
                            Satu Layar Kendali</span>
                    </h1>

                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                        Berhenti mengandalkan firasat untuk mengambil keputusan krusial. COOCA menyajikan dashboard bisnis
                        terpusat yang memperlihatkan tren omzet, margin bersih per produk, cabang paling menguntungkan, dan
                        jam sibuk toko secara real-time.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                            <span>Lihat Demo Dashboard BI</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.erp.erp') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                            <span>Jelajahi Ekosistem ERP</span>
                        </a>
                    </div>

                    {{-- Key Operational Metrics --}}
                    <div
                        class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kecepatan Pembaruan
                            </div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Real-Time Instant</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kedalaman Data</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Drill-Down per SKU</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Akses Perangkat</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Mobile & Desktop</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live Owner Cockpit BI UI --}}
                <div class="lg:col-span-6">
                    <div
                        class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">

                        {{-- Executive Cockpit Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-amber-500/20 text-amber-400">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-neutral-200">Executive Overview — Seluruh Entitas</div>
                                    <div class="text-[10px] text-neutral-500">3 Cabang Toko • 2 Marketplace • Real-Time
                                    </div>
                                </div>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">+14.2%
                                MoM Growth</span>
                        </div>

                        {{-- 4 Core KPI Tiles --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 my-3">
                            <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                                <div class="text-[10px] text-neutral-400">Total Omzet</div>
                                <div class="text-xs sm:text-sm font-bold text-white font-mono mt-0.5">Rp 482.5M</div>
                                <div class="text-[9px] text-emerald-400 mt-1">↑ 18% vs Target</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                                <div class="text-[10px] text-neutral-400">Margin Bersih</div>
                                <div class="text-xs sm:text-sm font-bold text-emerald-400 font-mono mt-0.5">31.8%</div>
                                <div class="text-[9px] text-neutral-400 mt-1">Sehat (Benchmark 25%)</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                                <div class="text-[10px] text-neutral-400">Perputaran Stok</div>
                                <div class="text-xs sm:text-sm font-bold text-blue-400 font-mono mt-0.5">4.2x</div>
                                <div class="text-[9px] text-neutral-400 mt-1">Siklus 28 Hari</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800">
                                <div class="text-[10px] text-neutral-400">Repeat Order</div>
                                <div class="text-xs sm:text-sm font-bold text-purple-400 font-mono mt-0.5">42.6%</div>
                                <div class="text-[9px] text-neutral-400 mt-1">Member Aktif</div>
                            </div>
                        </div>

                        {{-- Branch Comparison & Hourly Heatmap Panel --}}
                        <div class="grid grid-cols-12 gap-2 text-xs">
                            {{-- Branch Contribution (7 Cols) --}}
                            <div
                                class="col-span-7 p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 space-y-2">
                                <div class="flex justify-between items-center text-[10px] font-mono text-neutral-400">
                                    <span>KONTRIBUSI OMZET CABANG</span>
                                    <span class="text-neutral-500">Bulan Ini</span>
                                </div>

                                <div class="space-y-1.5">
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-white font-medium">Cabang Sudirman (48%)</span>
                                            <span class="text-neutral-300 font-mono">Rp 231.6M</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-neutral-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-amber-500 rounded-full" style="width: 48%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-white font-medium">Cabang Senopati (34%)</span>
                                            <span class="text-neutral-300 font-mono">Rp 164.0M</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-neutral-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-500 rounded-full" style="width: 34%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-[11px] mb-1">
                                            <span class="text-white font-medium">Kelapa Gading (18%)</span>
                                            <span class="text-neutral-300 font-mono">Rp 86.9M</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-neutral-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-purple-500 rounded-full" style="width: 18%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hourly Peak Traffic (5 Cols) --}}
                            <div
                                class="col-span-5 p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex flex-col justify-between">
                                <div class="text-[10px] font-mono text-neutral-400">JAM PUNCAK TOKO</div>
                                <div class="space-y-1 text-[11px]">
                                    <div class="flex justify-between items-center">
                                        <span class="text-neutral-300">12:00 - 14:00</span>
                                        <span
                                            class="px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-400 font-bold text-[10px]">Super
                                            Sibuk</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-neutral-300">18:30 - 20:30</span>
                                        <span
                                            class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold text-[10px]">Ramai</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-neutral-400">09:00 - 11:00</span>
                                        <span class="text-neutral-500 text-[10px]">Normal</span>
                                    </div>
                                </div>
                                <div class="text-[9px] text-neutral-500 pt-1 border-t border-neutral-800">
                                    Rekomendasi: Tambah 1 kasir shift siang
                                </div>
                            </div>
                        </div>

                        {{-- Strategic Decision Action Footer --}}
                        <div
                            class="mt-3 pt-2 border-t border-neutral-800 flex items-center justify-between text-[11px] text-neutral-400">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                                Margin Tertinggi: Produk Kopi Susu Aren (64% Gross)
                            </span>
                            <a href="{{ route('public.demo') }}" class="text-amber-400 hover:underline font-medium">Buka
                                Analisis SKU →</a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerugian Mengambil Keputusan Tanpa Data --}}
        <section
            class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-amber-600 dark:text-amber-400 font-semibold mb-3">
                        Tantangan Eksekutif Bisnis</h2>
                    <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Apakah Anda Masih Menebak-nebak Arah Bisnis Menggunakan Perasaan?
                    </p>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                        Mengembangkan bisnis multi-cabang tanpa data real-time ibarat mengendarai mobil berkecepatan tinggi
                        dengan mata tertutup di malam hari.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="eye-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Buta Kondisi Bisnis Harian</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Anda harus terus-menerus menelepon manajer toko untuk menanyakan apakah cabang sedang ramai,
                            atau menunggu tim akunting menyusun rekapan spreadsheet yang baru selesai minggu depan.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="trending-down" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Omzet Tinggi, Tapi Laba Nol</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Toko tampak sangat sibuk dan menghasilkan omzet ratusan juta, namun saat dihitung di akhir bulan
                            ternyata rugi karena produk terlaris dijual dengan margin yang tergerus diskon dan biaya
                            operasional tinggi.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i data-lucide="scale-balanced" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Sulit Menilai Kinerja Antar Cabang
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Tidak ada standar perbandingan yang adil. Anda tidak tahu apakah Cabang A berkinerja lebih baik
                            karena lokasi yang strategis atau karena efisiensi tim dan pengelolaan biaya yang lebih
                            disiplin.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE ANALYTICS CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-amber-600 dark:text-amber-400 font-semibold mb-3">Fitur
                    Business Intelligence COOCA</h2>
                <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Mengubah Data Operasional Menjadi Keputusan Bisnis Cerdas
                </p>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                    Dirancang khusus untuk membantu pemilik usaha dan manajer eksekutif memimpin bisnis dengan data yang
                    tajam.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Profit Margin per SKU (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="badge-percent" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Analisis Kontribusi Laba Kotor per SKU
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Ketahui persis produk mana yang menjadi 'bintang' penghasil laba (Stars), produk yang laris tapi
                            margin tipis (Cash Cows), dan produk yang lambat laku sekaligus menggerus modal (Dogs).
                            Alokasikan anggaran belanja stok ke produk yang benar-benar menghasilkan uang.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                        <span class="text-neutral-600 dark:text-neutral-300 font-medium">Metrik Profitabilitas:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Margin dihitung otomatis dari HPP Moving Average
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Peak Hours & Customer Heatmap (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-orange-100 dark:bg-orange-950 flex items-center justify-center text-orange-600 dark:text-orange-400">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Heatmap Jam Sibuk & Hari Ramai
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Lihat pola kepadatan pengunjung per jam dan per hari. Atur roster shift staf toko lebih hemat
                            dan siapkan bahan baku segar sebelum jam makan siang atau jam pulang kantor dimulai.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                        <span class="text-neutral-500">Efisiensi Biaya Staf</span>
                        <span class="text-orange-500 font-bold">Hemat 15% Biaya Lembur</span>
                    </div>
                </div>

                {{-- Bento Card 3: Perbandingan Performa Cabang (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Benchmarking Cabang</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Bandingkan omzet harian, basket size rata-rata, dan efisiensi biaya antar outlet toko untuk
                        mereplikasi kesuksesan cabang terbaik ke cabang lainnya.
                    </p>
                </div>

                {{-- Bento Card 4: Deteksi Anomali Cerdas (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Peringatan Anomali Bisnis</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Sistem otomatis memberi notifikasi jika terjadi lonjakan pembatalan pesanan (void), selisih kas
                        kasir yang berulang, atau penurunan omzet drastis di salah satu cabang.
                    </p>
                </div>

                {{-- Bento Card 5: Mobile Executive View (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Mobile Executive View</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Dirancang seringkas mungkin untuk layar smartphone, memudahkan owner melihat grafik ringkas dan
                        saldo kas kapan pun diperlukan saat mobilitas tinggi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Bagaimana Data Diolah Menjadi Keputusan --}}
        <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-semibold mb-3 border border-amber-500/30">
                        <span>Siklus Intelijen Bisnis</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Dari Transaksi di Lapangan Hingga Keputusan Strategis
                    </h2>
                    <p class="text-neutral-400 text-sm sm:text-base mt-3">
                        Modul Analitik menghubungkan seluruh titik data operasional menjadi kesimpulan yang mudah dipahami
                        pemilik bisnis.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-600/30 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Pengumpulan Data Otomatis</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Data transaksi POS, mutasi gudang, pesanan online, absensi staf, dan pengeluaran kas dicatat
                            langsung saat operasional berjalan tanpa rekap manual.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-orange-600/30 text-orange-400 flex items-center justify-center font-bold text-xs border border-orange-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Kalkulasi Metrik & Margin</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Sistem mengalkulasi HPP Moving Average, margin kotor, biaya operasional cabang, dan membaginya
                            menurut kategori serta rentang waktu tertentu.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Visualisasi Eksekutif</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Angka-angka rumit disajikan dalam bentuk grafik tren, heatmap jam sibuk, dan kartu status
                            kesehatan finansial yang langsung dapat dipahami dalam 5 detik.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Keputusan Presisi</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Owner memutuskan kapan harus membuka cabang baru, produk mana yang harus dihentikan, dan promosi
                            apa yang terbukti meningkatkan profitabilitas riil.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-amber-600 dark:text-amber-400 font-semibold mb-2">
                    Pertanyaan Umum</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Analitik
                    Bisnis COOCA</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah data di dashboard analitik COOCA diperbarui secara real-time?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Ya, 100% real-time. Setiap kali kasir menyelesaikan transaksi di outlet atau pesanan online masuk,
                        grafik omzet dan margin di dashboard Anda langsung terupdate di detik yang sama tanpa jeda waktu
                        (zero batch delay).
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah pemilik bisnis bisa membuka dashboard ini lewat smartphone di luar kantor?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Sangat bisa. Dashboard COOCA Analytics dioptimalkan secara mobile-first untuk layar smartphone. Anda
                        dapat memantau denyut nadi seluruh cabang, kas masuk, dan produk terlaris saat bepergian atau
                        liburan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah manajer cabang bisa melihat data profitabilitas cabang lain?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Tidak bisa, kecuali Anda beri izin. Sistem memiliki pengaturan Role-Based Access Control yang ketat.
                        Manajer cabang hanya dapat melihat data performa tokonya sendiri, sementara data konsolidasi seluruh
                        cabang dan margin rahasia hanya dapat diakses oleh Owner.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bisakah laporan analitik diekspor untuk bahan rapat mingguan manajemen?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengunduh seluruh data analitik, grafik perbandingan cabang, dan performa produk ke
                        format PDF eksekutif atau file Excel dalam satu kali klik.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-amber-600 dark:text-amber-400 font-semibold mb-1">
                        Modul Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Sumber Data Analitik Terpadu
                    </p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-amber-600 dark:text-amber-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-amber-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-amber-600 transition-colors">
                        Point of Sale (POS)</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Sumber data transaksi penjualan kasir
                        langsung per detik.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-amber-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-amber-600 transition-colors">
                        Manajemen Stok & HPP</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Data perputaran barang dan harga pokok
                        untuk kalkulasi margin kotor.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-amber-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-amber-600 transition-colors">
                        Laporan Laba Rugi</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pencatatan akuntansi resmi yang
                        memvalidasi angka profit bersih perusahaan.</p>
                </a>

                <a href="{{ route('public.bos.overview') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-amber-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-amber-600 transition-colors">
                        Business Operating System</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Bagaimana seluruh layer bisnis
                        dikendalikan secara utuh dari satu sistem.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Mulai Kendalikan Bisnis Anda dengan Kejelasan Data
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-400">
                        Dapatkan visibilitas 360 derajat atas omzet, margin produk, dan performa cabang Anda setiap hari
                        bersama COOCA Analytics.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-sm transition-all shadow-md">
                            Coba Demo Dashboard Analytics
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                            Konsultasi Kebutuhan Pemilik Bisnis
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
