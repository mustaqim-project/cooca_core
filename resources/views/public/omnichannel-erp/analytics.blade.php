@extends('layouts.public_marketing')

@section('title', 'Software Analitik Bisnis & Dashboard KPI Pemilik Usaha | COOCA')
@section('description',
    'Platform Business Intelligence (BI) dan dashboard eksekutif untuk pemilik bisnis retail, F&B, dan jasa. Pantau omzet real-time, margin kotor per SKU, perbandingan cabang, dan jam sibuk toko dalam satu layar.')
@section('og_title', 'Software Analitik Bisnis & Dashboard KPI Pemilik Usaha | COOCA')
@section('og_description',
    'Platform Business Intelligence (BI) dan dashboard eksekutif untuk pemilik bisnis retail, F&B, dan jasa. Pantau omzet real-time, margin kotor per SKU, perbandingan cabang, dan jam sibuk toko dalam satu layar.')
@section('keywords',
    'software analitik bisnis, dashboard kpi penjualan, laporan performa cabang, business intelligence umkm, analisis profit margin produk')

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
    <div
        class="relative overflow-hidden bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Full Viewport 50/50 Ratio - Apple HIG Cockpit) ══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">

            <!-- Subtle Ambient Background Glows (Pure CSS, No Heavy Images) -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Safe from Fixed Bottom Nav on Mobile) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-3 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-6 sm:pb-24 lg:py-14">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-6 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile and Desktop ~ 6 Cols) -->
                    <div
                        class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Breadcrumb & Overline Kicker -->
                        <div class="space-y-2">
                            <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-[11px] sm:text-xs text-slate-400">
                                <a href="{{ route('landing') }}" class="hover:text-[#00C4D8] transition-colors">Home</a>
                                <i data-lucide="chevron-right" class="w-3 h-3 text-white/30"></i>
                                <a href="{{ route('public.erp.erp') }}" class="hover:text-[#00C4D8] transition-colors">Omnichannel ERP</a>
                                <i data-lucide="chevron-right" class="w-3 h-3 text-white/30"></i>
                                <span class="text-white font-semibold" aria-current="page">Analitik Bisnis &amp; BI</span>
                            </nav>

                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                    Executive Cockpit &amp; Commercial Intelligence
                                </p>
                            </div>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Lihat Denyut Nadi Seluruh Bisnis <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Dalam
                                    Satu Layar Kendali</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Berhenti mengandalkan firasat untuk mengambil keputusan krusial. COOCA menyajikan dashboard bisnis terpusat yang memperlihatkan tren omzet, margin bersih per produk, cabang paling menguntungkan, dan jam sibuk toko secara real-time.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Lihat Demo Dashboard BI</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.erp') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Jelajahi Ekosistem ERP</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>BI</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>KPI</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>SKU</span>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center">
                                <div class="flex items-center gap-1 text-amber-400">
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <span class="text-xs sm:text-sm font-extrabold text-white ml-1 tabular-nums">4.9 /
                                        5.0</span>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">Akurasi Keputusan Bisnis Berbasis Data</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Real-Time Instant</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Drill-Down per SKU</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Multi-Device Ready</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated Live Owner Cockpit BI UI -->
                    <div class="lg:col-span-6 relative w-full max-w-xl mx-auto lg:max-w-none">
                        <!-- Ambient Spotlight Glow behind the Terminal Window -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] sm:rounded-[36px] blur-2xl sm:blur-3xl pointer-events-none -z-10">
                        </div>

                        <!-- Mobile Live Dynamic Island Metric Strip (Clean, non-colliding, zero overlap on Mobile) -->
                        <div class="flex sm:hidden items-center justify-between gap-2 mb-2 w-full">
                            <!-- Mobile Left Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-slate-400 font-medium">Omzet</span>
                                <span class="font-extrabold text-white">Rp 482.5M</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="trending-up" class="w-3 h-3 text-emerald-400"></i>
                                <span class="text-slate-400 font-medium">Growth</span>
                                <span class="font-extrabold text-emerald-400">+14.2% MoM</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Growth MoM (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Pertumbuhan Omzet</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-emerald-400 tabular-nums tracking-tight mt-0.5">+14.2% MoM</div>
                            <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1 mt-0.5">
                                <i data-lucide="bar-chart-2" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span>Margin Bersih 31.8%</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: Top Profit SKU (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Top Profit Leader</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">64% Margin</div>
                            <div class="text-[11px] font-semibold text-[#00C4D8] flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                <span>SKU Kopi Susu Aren</span>
                            </div>
                        </div>

                        <!-- Main Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-2.5 sm:p-4 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-2.5 sm:space-y-3 text-white relative z-10 overflow-hidden mb-6 sm:mb-0">

                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <!-- Mobile Window Header (sm:hidden - Clean title & status, zero truncation) -->
                            <div class="flex sm:hidden items-center justify-between border-b border-white/10 pb-2 gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">Executive Cockpit &bull; All Branches</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold shrink-0">
                                    +14.2% MoM
                                </span>
                            </div>

                            <!-- Tablet & Desktop macOS Window Title Bar (hidden sm:flex) -->
                            <div class="hidden sm:flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]/80"></span>
                                    <div
                                        class="ml-2 flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-[11px] text-slate-300 font-mono">
                                        <i data-lucide="lock" class="w-2.5 h-2.5 text-emerald-400"></i>
                                        <span>cooca.id/app/analytics/executive-cockpit</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span class="text-[11px] font-medium text-emerald-400 font-mono">+14.2% MoM Growth</span>
                                </div>
                            </div>

                            {{-- 4 Core KPI Tiles --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5 my-1.5 sm:my-2">
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 min-w-0">
                                    <div class="text-[10px] text-slate-400 truncate">Total Omzet</div>
                                    <div class="text-xs sm:text-sm font-bold text-white font-mono mt-0.5 truncate">Rp 482.5M</div>
                                    <div class="text-[10px] text-emerald-400 mt-1 font-medium truncate">↑ 18% vs Target</div>
                                </div>
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 min-w-0">
                                    <div class="text-[10px] text-slate-400 truncate">Margin Bersih</div>
                                    <div class="text-xs sm:text-sm font-bold text-emerald-400 font-mono mt-0.5 truncate">31.8%</div>
                                    <div class="text-[10px] text-slate-400 mt-1 truncate">Benchmark 25%</div>
                                </div>
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 min-w-0">
                                    <div class="text-[10px] text-slate-400 truncate">Perputaran Stok</div>
                                    <div class="text-xs sm:text-sm font-bold text-[#00C4D8] font-mono mt-0.5 truncate">4.2x</div>
                                    <div class="text-[10px] text-slate-400 mt-1 truncate">Siklus 28 Hari</div>
                                </div>
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 min-w-0">
                                    <div class="text-[10px] text-slate-400 truncate">Repeat Order</div>
                                    <div class="text-xs sm:text-sm font-bold text-purple-300 font-mono mt-0.5 truncate">42.6%</div>
                                    <div class="text-[10px] text-slate-400 mt-1 truncate">Member Aktif</div>
                                </div>
                            </div>

                            {{-- Branch Comparison & Hourly Heatmap Panel --}}
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-2.5 text-xs">
                                {{-- Branch Contribution (7 Cols) --}}
                                <div
                                    class="sm:col-span-7 p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 space-y-1.5 min-w-0">
                                    <div class="flex justify-between items-center text-[10px] font-mono text-slate-400">
                                        <span class="truncate">KONTRIBUSI OMZET CABANG</span>
                                        <span class="text-slate-500 shrink-0">Bulan Ini</span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <div>
                                            <div class="flex justify-between items-center text-[11px] mb-1 gap-2">
                                                <span class="text-white font-medium truncate">Cabang Sudirman (48%)</span>
                                                <span class="text-slate-300 font-mono shrink-0">Rp 231.6M</span>
                                            </div>
                                            <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#007AFF] rounded-full" style="width: 48%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between items-center text-[11px] mb-1 gap-2">
                                                <span class="text-white font-medium truncate">Cabang Senopati (34%)</span>
                                                <span class="text-slate-300 font-mono shrink-0">Rp 164.0M</span>
                                            </div>
                                            <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#00C4D8] rounded-full" style="width: 34%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between items-center text-[11px] mb-1 gap-2">
                                                <span class="text-white font-medium truncate">Kelapa Gading (18%)</span>
                                                <span class="text-slate-300 font-mono shrink-0">Rp 86.9M</span>
                                            </div>
                                            <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full bg-purple-400 rounded-full" style="width: 18%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hourly Peak Traffic (5 Cols) --}}
                                <div
                                    class="sm:col-span-5 p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex flex-col justify-between space-y-1.5 min-w-0">
                                    <div class="text-[10px] font-mono text-slate-400">JAM PUNCAK TOKO</div>
                                    <div class="space-y-1 text-[11px]">
                                        <div class="flex justify-between items-center gap-2">
                                            <span class="text-slate-300 truncate">12:00 - 14:00</span>
                                            <span
                                                class="px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-400 font-bold text-[10px] shrink-0 whitespace-nowrap">Super Sibuk</span>
                                        </div>
                                        <div class="flex justify-between items-center gap-2">
                                            <span class="text-slate-300 truncate">18:30 - 20:30</span>
                                            <span
                                                class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400 font-bold text-[10px] shrink-0 whitespace-nowrap">Ramai</span>
                                        </div>
                                        <div class="flex justify-between items-center gap-2">
                                            <span class="text-slate-400 truncate">09:00 - 11:00</span>
                                            <span
                                                class="text-slate-400 text-[10px] shrink-0 whitespace-nowrap">Normal</span>
                                        </div>
                                    </div>
                                    <div class="text-[9px] text-slate-400 pt-1 border-t border-white/10 leading-snug">
                                        Rekomendasi: Tambah 1 kasir shift siang
                                    </div>
                                </div>
                            </div>

                            {{-- Strategic Decision Action Footer --}}
                            <div
                                class="mt-2 pt-2 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[11px] text-slate-400">
                                <span class="flex items-center gap-1.5 min-w-0 truncate">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                    <span class="truncate">Margin Tertinggi: Kopi Susu Aren (64% Gross)</span>
                                </span>
                                <a href="{{ route('public.demo') }}"
                                    class="text-[#00C4D8] hover:underline font-semibold shrink-0">Buka Analisis SKU →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerugian Mengambil Keputusan Tanpa Data --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Tantangan Eksekutif Bisnis
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Apakah Anda Masih Menebak-nebak Arah Bisnis Menggunakan Perasaan?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Mengembangkan bisnis multi-cabang tanpa data real-time ibarat mengendarai mobil berkecepatan tinggi
                        dengan mata tertutup di malam hari.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="eye-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Buta Kondisi Bisnis Harian</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Anda harus terus-menerus menelepon manajer toko untuk menanyakan apakah cabang sedang ramai,
                            atau menunggu tim akunting menyusun rekapan spreadsheet yang baru selesai minggu depan.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="trending-down" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Omzet Tinggi, Tapi Laba Nol</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Toko tampak sangat sibuk dan menghasilkan omzet ratusan juta, namun saat dihitung di akhir bulan
                            ternyata rugi karena produk terlaris dijual dengan margin yang tergerus diskon dan biaya
                            operasional tinggi.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="scale-balanced" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Sulit Menilai Kinerja Antar Cabang
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Tidak ada standar perbandingan yang adil. Anda tidak tahu apakah Cabang A berkinerja lebih baik
                            karena lokasi yang strategis atau karena efisiensi tim dan pengelolaan biaya yang lebih
                            disiplin.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE ANALYTICS CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-white dark:bg-[#0B132B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                    Fitur Business Intelligence COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Mengubah Data Operasional Menjadi Keputusan Bisnis Cerdas
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Dirancang khusus untuk membantu pemilik usaha dan manajer eksekutif memimpin bisnis dengan data yang
                    tajam.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Profit Margin per SKU (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="badge-percent" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Analisis Kontribusi Laba Kotor per SKU
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Ketahui persis produk mana yang menjadi 'bintang' penghasil laba (Stars), produk yang laris tapi
                            margin tipis (Cash Cows), dan produk yang lambat laku sekaligus menggerus modal (Dogs).
                            Alokasikan anggaran belanja stok ke produk yang benar-benar menghasilkan uang.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium shrink-0">Metrik Profitabilitas:</span>
                        <span class="text-[#007AFF] dark:text-[#00C4D8] font-semibold flex items-center gap-1 min-w-0">
                            <i data-lucide="check" class="w-4 h-4 shrink-0 text-emerald-500"></i>
                            <span class="truncate">Margin dihitung otomatis dari HPP Moving Average</span>
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Peak Hours & Customer Heatmap (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-orange-100 dark:bg-orange-950 flex items-center justify-center text-orange-600 dark:text-orange-400">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Heatmap Jam Sibuk &amp; Hari Ramai
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Lihat pola kepadatan pengunjung per jam dan per hari. Atur roster shift staf toko lebih hemat
                            dan siapkan bahan baku segar sebelum jam makan siang atau jam pulang kantor dimulai.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-wrap items-center justify-between gap-2 font-mono">
                        <span class="text-slate-500 dark:text-slate-400 truncate">Efisiensi Biaya Staf</span>
                        <span class="text-orange-600 dark:text-orange-400 font-bold shrink-0">Hemat 15% Biaya Lembur</span>
                    </div>
                </div>

                {{-- Bento Card 3: Perbandingan Performa Cabang (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Benchmarking Cabang</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Bandingkan omzet harian, basket size rata-rata, dan efisiensi biaya antar outlet toko untuk
                        mereplikasi kesuksesan cabang terbaik ke cabang lainnya.
                    </p>
                </div>

                {{-- Bento Card 4: Deteksi Anomali Cerdas (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Peringatan Anomali Bisnis</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Sistem otomatis memberi notifikasi jika terjadi lonjakan pembatalan pesanan (void), selisih kas
                        kasir yang berulang, atau penurunan omzet drastis di salah satu cabang.
                    </p>
                </div>

                {{-- Bento Card 5: Mobile Executive View (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Mobile Executive View</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Dirancang seringkas mungkin untuk layar smartphone, memudahkan owner melihat grafik ringkas dan
                        saldo kas kapan pun diperlukan saat mobilitas tinggi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Bagaimana Data Diolah Menjadi Keputusan (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Siklus Intelijen Bisnis</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Dari Transaksi di Lapangan Hingga Keputusan Strategis
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Modul Analitik menghubungkan seluruh titik data operasional menjadi kesimpulan yang mudah dipahami
                        pemilik bisnis.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Pengumpulan Data Otomatis</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Data transaksi POS, mutasi gudang, pesanan online, absensi staf, dan pengeluaran kas dicatat
                            langsung saat operasional berjalan tanpa rekap manual.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs border border-orange-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Kalkulasi Metrik & Margin</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Sistem mengalkulasi HPP Moving Average, margin kotor, biaya operasional cabang, dan membaginya
                            menurut kategori serta rentang waktu tertentu.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-500/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-blue-500/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Visualisasi Eksekutif</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Angka-angka rumit disajikan dalam bentuk grafik tren, heatmap jam sibuk, dan kartu status
                            kesehatan finansial yang langsung dapat dipahami dalam 5 detik.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Keputusan Presisi</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Owner memutuskan kapan harus membuka cabang baru, produk mana yang harus dihentikan, dan promosi
                            apa yang terbukti meningkatkan profitabilitas riil.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 bg-white dark:bg-[#0B132B]">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar Analitik
                    Bisnis COOCA</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah data di dashboard analitik COOCA diperbarui secara real-time?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Ya, 100% real-time. Setiap kali kasir menyelesaikan transaksi di outlet atau pesanan online masuk,
                        grafik omzet dan margin di dashboard Anda langsung terupdate di detik yang sama tanpa jeda waktu
                        (zero batch delay).
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah pemilik bisnis bisa membuka dashboard ini lewat smartphone di luar kantor?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Sangat bisa. Dashboard COOCA Analytics dioptimalkan secara mobile-first untuk layar smartphone. Anda
                        dapat memantau denyut nadi seluruh cabang, kas masuk, dan produk terlaris saat bepergian atau
                        liburan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah manajer cabang bisa melihat data profitabilitas cabang lain?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Tidak bisa, kecuali Anda beri izin. Sistem memiliki pengaturan Role-Based Access Control yang ketat.
                        Manajer cabang hanya dapat melihat data performa tokonya sendiri, sementara data konsolidasi seluruh
                        cabang dan margin rahasia hanya dapat diakses oleh Owner.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bisakah laporan analitik diekspor untuk bahan rapat mingguan manajemen?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengunduh seluruh data analitik, grafik perbandingan cabang, dan performa produk ke
                        format PDF eksekutif atau file Excel dalam satu kali klik.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-1">
                        Modul Terkait
                    </h2>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Sumber Data Analitik
                        Terpadu</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sumber data transaksi penjualan kasir
                        langsung per detik.</p>
                </a>

                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Manajemen Stok & HPP
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Data perputaran barang dan harga pokok untuk
                        kalkulasi margin kotor.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Laporan Laba Rugi
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pencatatan akuntansi resmi yang memvalidasi
                        angka profit bersih perusahaan.</p>
                </a>

                <a href="{{ route('public.bos.overview') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Business Operating System
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Bagaimana seluruh layer bisnis dikendalikan
                        secara utuh dari satu sistem.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Ambient lights inside CTA --}}
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Mulai Kendalikan Bisnis Anda dengan Kejelasan Data
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Dapatkan visibilitas 360 derajat atas omzet, margin produk, dan performa cabang Anda setiap hari
                        bersama COOCA Analytics.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Dashboard Analytics
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Konsultasi Kebutuhan Pemilik Bisnis
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
