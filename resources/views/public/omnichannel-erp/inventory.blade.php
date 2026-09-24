@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Inventori & Stok Multi-Gudang Terintegrasi | COOCA')
@section('description',
    'Aplikasi inventori stok gudang multi-cabang terintegrasi. Pantau kartu stok perpetual, mutasi
    antar cabang, reorder point otomatis, resep bahan baku (BOM), dan stock opname tanpa tutup toko.')
@section('keywords',
    'software inventori barang, aplikasi stok gudang, manajemen stok multi gudang, kartu stok otomatis,
    sistem inventory indonesia')

@section('og_title', 'Software Manajemen Inventori & Stok Multi-Gudang Terintegrasi | COOCA')
@section('og_description', 'Pantau kartu stok perpetual, mutasi antar cabang, resep bahan baku (BOM), dan stock opname tanpa tutup toko.')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Inventory & Warehouse Management",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Sistem kontrol inventori dan multi-gudang terintegrasi dengan POS, purchasing, dan akuntansi keuangan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pelacakan stok multi-gudang dan mutasi antar outlet secara real-time",
    "Kartu stok perpetual dengan riwayat audit trail setiap pergerakan barang",
    "Peringatan stok minimum (Reorder Point) dan Purchase Order otomatis",
    "Konversi satuan dan Bill of Materials (BOM) untuk F&B dan manufaktur ringan",
    "Stock opname terstruktur dengan scanner barcode tanpa harus menghentikan operasional toko"
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
      "name": "Bagaimana sistem COOCA menangani stok di banyak cabang atau gudang terpisah?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA mendukung multi-warehouse tanpa batas. Anda bisa membagi stok berdasarkan Gudang Pusat, Gudang Transit, maupun Rak Toko per cabang. Mutasi antar gudang tercatat lewat dokumen transfer resmi (Surat Jalan/Transfer Order) sehingga status stok dalam perjalanan (in-transit) selalu terlacak."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah COOCA mendukung resep makanan/minuman (Bill of Materials) untuk memotong bahan baku?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, sangat mendukung. Anda dapat membuat formula resep BOM. Contohnya, saat 1 cup Kopi Susu terjual di POS, sistem otomatis memotong 18 gram biji kopi, 120 ml susu segar, 20 ml sirup aren, dan 1 cup plastik dari stok gudang outlet."
      }
    },
    {
      "@type": "Question",
      "name": "Metode penilaian persediaan apa yang digunakan untuk menghitung HPP?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA menghitung Harga Pokok Penjualan (HPP) secara otomatis dengan metode Moving Average (Rata-rata Bergerak) yang sesuai standar SAK EMKM perpajakan Indonesia, menjaga nilai margin kotor Anda selalu akurat setiap kali ada pembelian barang baru."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah toko harus tutup total saat melakukan Stock Opname?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak perlu. COOCA memiliki fitur Stock Opname Parsial. Anda bisa melakukan perhitungan fisik per kategori atau per rak tertentu menggunakan scanner barcode/HP. Selisih barang akan dihitung otomatis dan sistem menerbitkan jurnal penyesuaian persediaan saat disetujui owner."
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
                                <span class="text-white font-semibold" aria-current="page">Manajemen Stok</span>
                            </nav>

                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                    Perpetual Stock &amp; Multi-Warehouse Control
                                </p>
                            </div>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Kendalikan Stok di Setiap Gudang <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Tanpa
                                    Selisih Misterius</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Hentikan barang hilang dan kehabisan stok saat pelanggan siap membeli. COOCA menyajikan kartu stok perpetual real-time, mutasi antar cabang dengan surat jalan, resep bahan baku (BOM), dan peringatan restock sebelum terlambat.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Coba Modul Stok</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.pos') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Koneksi ke POS Kasir</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>WH</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>BOM</span>
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
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">Akurasi Audit Stok &amp; Multi-Gudang</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Kartu Stok Perpetual</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>HPP Moving Average</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Stock Opname Tanpa Tutup Toko</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated Warehouse Inventory Dashboard UI with Apple HIG Cockpit Window & Mobile Dynamic Island Strip -->
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
                                <span class="text-slate-400 font-medium">Gudang Cakung</span>
                                <span class="font-extrabold text-white">1.420 SKU</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="truck" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span class="text-slate-400 font-medium">Surat Jalan</span>
                                <span class="font-extrabold text-white">In-Transit</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Akurasi Stok (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Akurasi Kartu Stok</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">Perpetual 100%</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>Audit Trail Lengkap</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: Reorder Alert (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Reorder Alert</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">Auto-Draft PO</div>
                            <div class="text-[11px] font-semibold text-rose-400 flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                                <span>3 SKU Low Stock</span>
                            </div>
                        </div>

                        <!-- Floating Notification Toast (MD+ / Desktop) -->
                        <div
                            class="hidden md:flex items-center gap-2.5 absolute bottom-8 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[16px] px-3.5 py-2.5 shadow-[0_20px_40px_-5px_rgba(0,0,0,0.7)] backdrop-blur-2xl max-w-xs text-white">
                            <div
                                class="w-8 h-8 rounded-full bg-sky-500/20 flex items-center justify-center shrink-0 text-sky-400">
                                <i data-lucide="file-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-medium">Surat Jalan Terbit</div>
                                <div class="text-xs font-bold text-white">#TR-108 Menuju Cabang Sudirman</div>
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
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">Warehouse OS &bull; Gudang Cakung</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold shrink-0">
                                    Online Sync
                                </span>
                            </div>

                            <!-- Desktop/Tablet macOS Window Top Bar (hidden sm:flex with traffic lights, URL bar & status) -->
                            <div
                                class="hidden sm:flex items-center justify-between border-b border-white/10 pb-2 sm:pb-3 gap-2">
                                <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                                    <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
                                        <span
                                            class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#FF5F56] shadow-inner"></span>
                                        <span
                                            class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#FFBD2E] shadow-inner"></span>
                                        <span
                                            class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-[#27C93F] shadow-inner"></span>
                                    </div>
                                    <!-- URL Address Bar with SSL Lock Icon -->
                                    <div
                                        class="py-0.5 sm:py-1 px-2.5 sm:px-3 rounded-full bg-white/[0.06] border border-white/10 text-[9px] sm:text-[11px] font-mono text-slate-300 flex items-center gap-1.5 truncate max-w-[150px] sm:max-w-[280px]">
                                        <i data-lucide="lock"
                                            class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-400 shrink-0"></i>
                                        <span class="truncate">https://cooca.id/app/inventory/warehouse-01</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Perpetual Sync Aktif
                                    </span>
                                </div>
                            </div>

                            {{-- Warehouse Control Filter Header --}}
                            <div
                                class="flex items-center justify-between gap-2.5 pb-2 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <span class="p-1.5 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                        <i data-lucide="warehouse" class="w-4 h-4"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">Gudang Utama Cakung</div>
                                        <div class="text-[9px] sm:text-[11px] text-slate-400 truncate">Kapasitas: 74% &bull; 1.420 SKU</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span
                                        class="px-2 py-0.5 rounded bg-white/10 text-[10px] sm:text-[11px] text-slate-300 font-mono">#TR-108</span>
                                    <span
                                        class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[9px] sm:text-[10px] font-semibold">In-Transit</span>
                                </div>
                            </div>

                            {{-- Low Stock Reorder Notification Banner --}}
                            <div
                                class="p-2 sm:p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-1.5 sm:gap-2 text-rose-300 min-w-0 flex-1">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-rose-400 shrink-0"></i>
                                    <span class="text-[10px] sm:text-[11px] truncate"><strong>3 Item Low Stock:</strong> Rekomendasi terbitkan PO</span>
                                </div>
                                <span
                                    class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded bg-rose-500/30 text-rose-200 font-bold shrink-0">Buat PO</span>
                            </div>

                            {{-- Inventory Data Table Mockup --}}
                            <div class="space-y-1.5 overflow-hidden text-left">
                                <div
                                    class="grid grid-cols-12 gap-1 text-[9px] sm:text-[10px] uppercase font-mono text-slate-400 px-2 py-1 bg-[#060B1E]/80 rounded-lg border border-white/5">
                                    <div class="col-span-5 truncate">Barang &amp; SKU</div>
                                    <div class="col-span-2 text-center truncate">Fisik</div>
                                    <div class="col-span-2 text-center truncate">Tersedia</div>
                                    <div class="col-span-3 text-right truncate">Status</div>
                                </div>

                                {{-- Item 1 --}}
                                <div
                                    class="grid grid-cols-12 gap-1 items-center p-1.5 sm:p-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs transition-colors">
                                    <div class="col-span-5 min-w-0">
                                        <div class="font-medium text-white text-[11px] sm:text-xs truncate">Biji Kopi Arabika 1kg</div>
                                        <div class="text-[9px] sm:text-[10px] text-slate-400 font-mono truncate">SKU-KOP-01 &bull; Rak B-02</div>
                                    </div>
                                    <div class="col-span-2 text-center font-mono text-[11px] sm:text-xs text-slate-300 truncate">142 kg</div>
                                    <div class="col-span-2 text-center font-mono text-[11px] sm:text-xs text-emerald-400 truncate">128 kg</div>
                                    <div class="col-span-3 text-right shrink-0">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 whitespace-nowrap">Stok Aman</span>
                                    </div>
                                </div>

                                {{-- Item 2 (Low stock) --}}
                                <div
                                    class="grid grid-cols-12 gap-1 items-center p-1.5 sm:p-2 rounded-xl bg-white/5 hover:bg-white/10 border border-rose-500/30 text-xs transition-colors">
                                    <div class="col-span-5 min-w-0">
                                        <div class="font-medium text-white text-[11px] sm:text-xs truncate">Paper Cup 12oz Cold</div>
                                        <div class="text-[9px] sm:text-[10px] text-slate-400 font-mono truncate">SKU-PKG-44 &bull; Rak A-01</div>
                                    </div>
                                    <div class="col-span-2 text-center font-mono text-[11px] sm:text-xs text-slate-300 truncate">80 pcs</div>
                                    <div class="col-span-2 text-center font-mono text-[11px] sm:text-xs text-rose-400 truncate">40 pcs</div>
                                    <div class="col-span-3 text-right shrink-0">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold whitespace-nowrap">Reorder</span>
                                    </div>
                                </div>

                                {{-- Item 3 --}}
                                <div
                                    class="grid grid-cols-12 gap-1 items-center p-1.5 sm:p-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs transition-colors">
                                    <div class="col-span-5 min-w-0">
                                        <div class="font-medium text-white text-[11px] sm:text-xs truncate">Fresh Milk 1L</div>
                                        <div class="text-[9px] sm:text-[10px] text-slate-400 font-mono truncate">SKU-DRY-12 &bull; Chiller 01</div>
                                    </div>
                                    <div class="col-span-2 text-center font-mono text-[11px] sm:text-xs text-slate-300 truncate">48 btl</div>
                                    <div class="col-span-2 text-center font-mono text-[11px] sm:text-xs text-slate-200 truncate">48 btl</div>
                                    <div class="col-span-3 text-right shrink-0">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 whitespace-nowrap">Stok Aman</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stock Card Audit Trail Footer --}}
                            <div
                                class="pt-2 border-t border-white/10 flex items-center justify-between gap-2 text-[10px] sm:text-[11px] text-slate-400">
                                <span class="flex items-center gap-1.5 truncate">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                                    <span class="truncate">HPP Moving Average: Aktif</span>
                                </span>
                                <a href="{{ route('public.demo') }}"
                                    class="text-[#00C4D8] hover:underline font-medium shrink-0">Buka Kartu Stok &rarr;</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- 2. PAIN POINT HIGHLIGHT: Kerugian Akibat Manajemen Stok Berantakan --}}
        <section
            class="py-16 sm:py-24 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10 text-slate-900 dark:text-white transition-colors">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#38BDF8] text-xs font-bold uppercase tracking-wider mb-3">
                        <span>Tantangan Inventori Retail &amp; UMKM</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Di Mana Sebenarnya Uang Bisnis Anda Menguap di Gudang?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3 font-normal">
                        Banyak bisnis bangkrut bukan karena tidak laku, melainkan karena modal tertimbun mati dalam bentuk
                        barang slow-moving atau bocor akibat catatan stok yang tidak diaudit.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Dead Stock &amp; Modal Mati</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Membeli barang berlebihan tanpa data perputaran barang yang jelas. Barang menumpuk
                            berbulan-bulan hingga kedaluwarsa atau rusak, membakar cash flow perusahaan secara perlahan.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Stok Hilang Saat Pengiriman Cabang
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Gudang pusat mengirim 50 unit barang ke outlet, namun cabang hanya menerima 45 unit. Tanpa
                            sistem Surat Jalan digital dan konfirmasi terima barang, selisih 5 unit tidak pernah diketahui
                            siapa yang bertanggung jawab.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#38BDF8]">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">HPP Salah, Laba Semu</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Harga beli dari supplier sering naik-turun, namun Anda memakai HPP tebak-tebakan. Akibatnya,
                            laporan laba rugi bulanan tampak untung besar padahal uang riil di bank justru terus berkurang.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE INVENTORY CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 text-slate-900 dark:text-white">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00C4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-xs font-bold uppercase tracking-wider mb-3">
                    <span>Fitur Cerdas Manajemen Stok</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Visibilitas Total dari Pembelian Sampai Penjualan Akhir
                </h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3 font-normal">
                    Dirancang khusus untuk mendukung operasional yang dinamis, mulai dari gudang bahan baku hingga display
                    rak toko.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Multi-Warehouse & Transfer Order (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="network" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Multi-Gudang &amp; Mutasi Antar Cabang Terkontrol
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Kelola gudang pusat, gudang display, dan stok toko per cabang secara mandiri. Ketika stok di
                            satu outlet menipis, buat Transfer Order resmi. Sistem mencatat status pengiriman, pengemudi,
                            nomor plat, serta verifikasi penerimaan dengan barcode scan di cabang tujuan.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">Status Pengiriman Digital:</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#38BDF8] font-semibold">Draft</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span
                                class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 font-semibold">Kirim
                                (In-Transit)</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span
                                class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 font-semibold">Diterima
                                Cabang</span>
                        </div>
                    </div>
                </div>

                {{-- Bento Card 2: BOM Recipe & Bundling (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Resep Bahan Baku (BOM) &amp; Bundling
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Sempurna untuk kafe, bakery, dan bengkel perakitan. Definisikan bahan mentah untuk setiap item
                            jadi. Saat produk terjual di kasir, stok biji kopi, gula, bumbu dapur, atau baut spare part
                            otomatis berkurang di gudang.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3.5 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs font-mono text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                        <span>1 Menu Kopi Susu:</span>
                        <span class="text-teal-600 dark:text-teal-400 font-semibold">18g Kopi + 120ml Susu</span>
                    </div>
                </div>

                {{-- Bento Card 3: Kartu Stok Perpetual & Audit Log (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#38BDF8]">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kartu Stok Perpetual</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Setiap mutasi barang memiliki referensi nomor dokumen yang sah (PO, Faktur Penjualan, atau Berita
                        Acara Kerusakan). Tidak ada angka stok yang berubah secara tiba-tiba tanpa jejak staf penginput.
                    </p>
                </div>

                {{-- Bento Card 4: Reorder Point & PO Otomatis (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="bell-ring" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Reorder Point &amp; PO Cerdas</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Tentukan batas minimum stok per gudang. Saat barang mendekati ambang batas, COOCA memberi notifikasi
                        dan menyiapkan draf Purchase Order ke supplier yang biasa menyediakan barang tersebut.
                    </p>
                </div>

                {{-- Bento Card 5: Stock Opname Tanpa Tutup Toko (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="check-square" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Stock Opname Parsial</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Lakukan opname berkala per rak atau kategori tertentu tanpa perlu meliburkan toko. Masukkan hasil
                        fisik lewat scanner smartphone, tinjau selisih, dan terbitkan adjustment journal dengan satu klik.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED WORKFLOW: Siklus Pengadaan hingga Penjualan --}}
        <section class="py-16 sm:py-24 bg-[#060B1E] text-white border-y border-white/10 relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/20 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Siklus Inventori End-to-End</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Bagaimana Stok Mengalir dalam Ekosistem COOCA
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3 font-normal">
                        Inventori COOCA terhubung erat dengan modul Pembelian (Purchasing), Kasir (POS), dan Akuntansi tanpa
                        sekat.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Phase 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/30 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Purchase Order &amp; Penerimaan</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Barang dipesan ke supplier melalui PO. Saat barang tiba di gudang, staf gudang mencocokkan fisik
                            dengan surat jalan melalui Good Receipt Note (GRN). Stok bertambah otomatis.
                        </p>
                    </div>

                    {{-- Phase 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-teal-500/30 text-teal-300 flex items-center justify-center font-bold text-xs border border-teal-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Perhitungan HPP Otomatis</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Nilai pembelian baru dikalkulasi dengan saldo stok lama menggunakan metode Moving Average. Modul
                            akuntansi menerbitkan hutang usaha (AP) dan menambah nilai aset persediaan.
                        </p>
                    </div>

                    {{-- Phase 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-500/30 text-blue-300 flex items-center justify-center font-bold text-xs border border-blue-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Distribusi ke Cabang Toko</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Gudang pusat mendistribusikan barang ke cabang-cabang ritel dengan Surat Jalan Transfer. Stok
                            gudang pusat berkurang, status in-transit aktif sampai kasir cabang melakukan scan terima.
                        </p>
                    </div>

                    {{-- Phase 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/30 text-purple-300 flex items-center justify-center font-bold text-xs border border-purple-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Penjualan &amp; Pengurangan Stok</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Kasir scan barcode di POS atau pesanan masuk dari marketplace online. Stok cabang terpotong,
                            nilai HPP langsung dicatat ke laporan laba rugi, dan sisa stok siap dijual kembali.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-slate-900 dark:text-white">
            <div class="text-center mb-12">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00C4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-xs font-bold uppercase tracking-wider mb-2">
                    <span>Pertanyaan Umum</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">Tanya Jawab
                    Seputar Inventori &amp; Gudang</h2>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana sistem COOCA menangani stok di banyak cabang atau gudang terpisah?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        COOCA mendukung multi-warehouse tanpa batas. Anda bisa membagi stok berdasarkan Gudang Pusat, Gudang
                        Transit, maupun Rak Toko per cabang. Mutasi antar gudang tercatat lewat dokumen transfer resmi
                        (Surat Jalan/Transfer Order) sehingga status stok dalam perjalanan (in-transit) selalu terlacak.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah COOCA mendukung resep makanan/minuman (Bill of Materials) untuk memotong bahan
                            baku?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        Ya, sangat mendukung. Anda dapat membuat formula resep BOM. Contohnya, saat 1 cup Kopi Susu terjual
                        di POS, sistem otomatis memotong 18 gram biji kopi, 120 ml susu segar, 20 ml sirup aren, dan 1 cup
                        plastik dari stok gudang outlet.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Metode penilaian persediaan apa yang digunakan untuk menghitung HPP?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        COOCA menghitung Harga Pokok Penjualan (HPP) secara otomatis dengan metode Moving Average (Rata-rata
                        Bergerak) yang sesuai standar SAK EMKM perpajakan Indonesia, menjaga nilai margin kotor Anda selalu
                        akurat setiap kali ada pembelian barang baru.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah toko harus tutup total saat melakukan Stock Opname?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        Tidak perlu. COOCA memiliki fitur Stock Opname Parsial. Anda bisa melakukan perhitungan fisik per
                        kategori atau per rak tertentu menggunakan scanner barcode/HP. Selisih barang akan dihitung otomatis
                        dan sistem menerbitkan jurnal penyesuaian persediaan saat disetujui owner.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER (Related Pages) --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#38BDF8] font-semibold mb-1">
                        Modul Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Ekosistem Pengendalian Stok</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#38BDF8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Kasir cepat yang langsung memotong stok
                        outlet secara real-time.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Akuntansi &amp; HPP</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Jurnal otomatis untuk mutasi, penyusutan,
                        dan nilai persediaan aset.</p>
                </a>

                <a href="{{ route('public.solutions.retail') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Solusi Retail</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Kelola ribuan SKU produk, barcode, dan
                        rantai pasok multi-toko.</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Persediaan</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Pantau rasio perputaran stok (Inventory
                        Turnover) dan dead stock.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                <!-- Subtle Ambient Background Glows -->
                <div
                    class="absolute -top-24 right-1/4 w-[400px] h-[400px] bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-1/4 w-[350px] h-[350px] bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Hentikan Kerugian Akibat Stok Selisih Mulai Hari Ini
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 font-normal">
                        Dapatkan visibilitas 100% atas persediaan barang Anda di setiap gudang dan cabang toko dengan COOCA
                        Inventory.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Modul Stok
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 transition-all">
                            Konsultasi Kebutuhan Gudang
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
