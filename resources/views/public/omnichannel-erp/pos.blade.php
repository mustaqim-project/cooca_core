@extends('layouts.public_marketing')

@section('title', 'Aplikasi Kasir POS Multi-Outlet Terintegrasi ERP & Stok | COOCA')
@section('description',
    'Software Point of Sale (POS) modern untuk retail, F&B, dan jasa. Transaksi kasir secepat kilat,
    cetak struk Bluetooth, barcode scanner, QRIS dinamis, dan langsung memotong stok serta membukukan jurnal keuangan
    otomatis.')
@section('keywords',
    'software kasir online, aplikasi pos multi outlet, point of sale indonesia, pos terintegrasi stok,
    kasir barcode qris')

@section('og_title', 'Aplikasi Kasir POS Multi-Outlet Terintegrasi ERP & Stok | COOCA')
@section('og_description', 'Kasir POS cepat dengan cetak struk Bluetooth, barcode scanner, QRIS dinamis, dan potong stok bahan baku otomatis seketika.')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Point of Sale (POS)",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Aplikasi kasir online cepat dan fleksibel dengan integrasi real-time ke manajemen stok, akuntansi, dan database pelanggan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pencatatan transaksi super cepat dengan barcode scanner dan touchscreen",
    "Dukungan printer thermal 58mm & 80mm via Bluetooth/USB",
    "Sinkronisasi stok gudang dan outlet otomatis seketika",
    "Penerimaan multi-metode bayar: QRIS, Cash, Transfer, Debit Card",
    "Laporan pergantian shift kasir dan rekonsiliasi laci kas (X/Z Report)"
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
      "name": "Apakah COOCA POS bisa digunakan di beberapa cabang atau outlet sekaligus?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. COOCA POS dirancang native multi-outlet. Setiap outlet memiliki katalog harga, persediaan stok terpisah, serta laporan shift kasir mandiri yang semuanya terpantau secara terpusat oleh owner."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah kasir harus memakai hardware khusus atau bisa memakai tablet dan HP yang sudah ada?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak perlu hardware mahal khusus. COOCA POS berjalan lancar di browser tablet Android, iPad, laptop Windows, bahkan smartphone staf, serta kompatibel dengan printer struk thermal standar Bluetooth/USB dan barcode scanner."
      }
    },
    {
      "@type": "Question",
      "name": "Apa yang terjadi pada stok dan laporan keuangan saat transaksi kasir selesai?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Begitu tombol 'Selesaikan Pembayaran' ditekan, sistem otomatis memotong stok barang (atau bahan baku jika memakai resep), mencatat penerimaan kas ke modul Finance, dan membuat jurnal debit-kredit otomatis tanpa perlu input ulang di malam hari."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara mencegah kecurangan kasir saat pergantian shift?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA POS memiliki fitur Buka/Tutup Kasir (Shift Control). Kasir wajib memasukkan modal awal dan menghitung fisik kas saat tutup shift. Sistem membandingkan saldo sistem vs saldo fisik aktual (X & Z Report) sehingga selisih uang langsung terdeteksi."
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
                        <!-- Pure Typographic Overline Kicker with Pulse Dot -->
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Cloud Point of Sale &amp; Kasir Cepat
                            </p>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Aplikasi Kasir Cepat Terhubung ke <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Stok
                                    &amp; Akuntansi</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Layani pelanggan tanpa antre berlama-lama. Transaksi kilat dengan barcode dan QRIS, cetak struk thermal, catat pelanggan, dan biarkan COOCA memotong stok fisik serta membukukan jurnal keuangan otomatis di detik yang sama.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Coba Demo POS</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.pricing') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Lihat Paket &amp; Harga</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>POS</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>QR</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>ERP</span>
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
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">Rating Keandalan Kasir &amp; POS</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>&lt; 3 Detik Checkout</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Auto Potong Stok Gudang</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Cetak Struk Thermal</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated Live POS Terminal UI with Apple HIG Cockpit Window & Mobile Dynamic Island Strip -->
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
                                <span class="text-slate-400 font-medium">Kasir 01</span>
                                <span class="font-extrabold text-white">Online</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="printer" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span class="text-slate-400 font-medium">Thermal</span>
                                <span class="font-extrabold text-white">Terhubung</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Kecepatan Checkout (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Kecepatan Transaksi</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">&lt; 3 Detik / Order</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="zap" class="w-3 h-3"></i>
                                <span>QRIS &amp; Thermal Instan</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: Sinkronisasi Gudang (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Sinkronisasi Gudang</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">Auto Potong BOM</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Jurnal Otomatis</span>
                            </div>
                        </div>

                        <!-- Floating Notification Toast (MD+ / Desktop) -->
                        <div
                            class="hidden md:flex items-center gap-2.5 absolute bottom-8 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[16px] px-3.5 py-2.5 shadow-[0_20px_40px_-5px_rgba(0,0,0,0.7)] backdrop-blur-2xl max-w-xs text-white">
                            <div
                                class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0 text-emerald-400">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-medium">Struk Otomatis Terbit</div>
                                <div class="text-xs font-bold text-white">TRX-9402 Dicetak &amp; Input Akuntansi</div>
                            </div>
                        </div>

                        <!-- Main Terminal Window Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-2.5 sm:p-4 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-2 sm:space-y-3 text-white relative z-10 overflow-hidden mb-6 sm:mb-0">

                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <!-- Mobile Window Header (sm:hidden - Clean title & status, zero truncation) -->
                            <div class="flex sm:hidden items-center justify-between border-b border-white/10 pb-2 gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">POS Terminal &bull; Outlet Sudirman</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold shrink-0">
                                    Shift Pagi
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
                                        <span class="truncate">https://cooca.id/app/pos/terminal-01</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[11px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Kasir Aktif (Shift Pagi)
                                    </span>
                                </div>
                            </div>

                            <!-- POS Sub-Banner Info -->
                            <div class="flex items-center justify-between text-xs pb-0.5">
                                <div>
                                    <div class="text-xs sm:text-sm font-bold text-white">Kasir 01 - Kasir Cepat</div>
                                    <div class="text-[9px] sm:text-xs text-slate-400">Kasir: Budi &bull; Meja 04</div>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-md bg-cyan-500/15 text-[#00C4D8] text-[9px] sm:text-xs font-semibold flex items-center gap-1 border border-cyan-500/20">
                                    <i data-lucide="printer" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                                    <span>BT-58mm Terhubung</span>
                                </span>
                            </div>

                            {{-- Main POS Dual Workspace (Items Grid + Cart Summary) --}}
                            <div
                                class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 bg-[#060B1E]/90 p-2 sm:p-3 rounded-xl border border-white/10">

                                {{-- Items Catalog (7 Cols) --}}
                                <div class="col-span-1 sm:col-span-7 space-y-2 min-w-0">
                                    {{-- Category Tabs --}}
                                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[10px] sm:text-[11px]">
                                        <span
                                            class="px-2.5 py-1 bg-[#007AFF] text-white font-medium rounded-lg shrink-0">Semua
                                            (24)</span>
                                        <span
                                            class="px-2.5 py-1 bg-white/10 text-slate-300 hover:text-white rounded-lg shrink-0">Favorit</span>
                                        <span
                                            class="px-2.5 py-1 bg-white/10 text-slate-300 hover:text-white rounded-lg shrink-0">Minuman</span>
                                    </div>

                                    {{-- Product Tiles --}}
                                    <div class="grid grid-cols-2 gap-2 text-left">
                                        <div
                                            class="p-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-[#00C4D8]/50 transition-all cursor-pointer group min-w-0">
                                            <div class="text-[10px] text-[#00C4D8] font-mono truncate">SKU-084</div>
                                            <div
                                                class="text-xs font-semibold text-white group-hover:text-[#00C4D8] transition-colors mt-0.5 truncate">
                                                Kopi Susu Aren</div>
                                            <div class="flex items-center justify-between gap-1 mt-2">
                                                <span class="text-xs font-bold text-slate-200 truncate">Rp 22.000</span>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shrink-0">Stok:
                                                    48</span>
                                            </div>
                                        </div>
                                        <div
                                            class="p-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-[#00C4D8]/50 transition-all cursor-pointer group min-w-0">
                                            <div class="text-[10px] text-[#00C4D8] font-mono truncate">SKU-112</div>
                                            <div
                                                class="text-xs font-semibold text-white group-hover:text-[#00C4D8] transition-colors mt-0.5 truncate">
                                                Croissant Butter</div>
                                            <div class="flex items-center justify-between gap-1 mt-2">
                                                <span class="text-xs font-bold text-slate-200 truncate">Rp 28.000</span>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shrink-0">Stok:
                                                    15</span>
                                            </div>
                                        </div>
                                        <div
                                            class="p-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-[#00C4D8]/50 transition-all cursor-pointer group min-w-0">
                                            <div class="text-[10px] text-[#00C4D8] font-mono truncate">SKU-209</div>
                                            <div
                                                class="text-xs font-semibold text-white group-hover:text-[#00C4D8] transition-colors mt-0.5 truncate">
                                                Matcha Latte Ice</div>
                                            <div class="flex items-center justify-between gap-1 mt-2">
                                                <span class="text-xs font-bold text-slate-200 truncate">Rp 26.000</span>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shrink-0">Stok:
                                                    32</span>
                                            </div>
                                        </div>
                                        <div
                                            class="p-2.5 rounded-xl bg-white/5 border border-white/10 hover:border-[#00C4D8]/50 transition-all cursor-pointer group min-w-0">
                                            <div class="text-[10px] text-[#00C4D8] font-mono truncate">SKU-019</div>
                                            <div
                                                class="text-xs font-semibold text-white group-hover:text-[#00C4D8] transition-colors mt-0.5 truncate">
                                                Earl Grey Tea</div>
                                            <div class="flex items-center justify-between gap-1 mt-2">
                                                <span class="text-xs font-bold text-slate-200 truncate">Rp 18.000</span>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shrink-0">Stok:
                                                    60</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Active Cart & Payment Panel (5 Cols) --}}
                                <div
                                    class="col-span-1 sm:col-span-5 bg-[#0E1E45]/90 p-3 rounded-xl border border-white/10 flex flex-col justify-between min-w-0">
                                    <div>
                                        <div
                                            class="flex items-center justify-between pb-2 border-b border-white/10 text-xs">
                                            <span class="font-semibold text-slate-200 truncate">Order #TRX-9402</span>
                                            <span class="text-[11px] text-slate-400 shrink-0">Meja 04</span>
                                        </div>

                                        {{-- Line Items --}}
                                        <div class="space-y-2 py-2.5 text-left text-xs border-b border-white/10">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-slate-200 font-medium truncate">2x Kopi Susu Aren</div>
                                                    <div class="text-[10px] text-slate-400 truncate">Less Sugar, Ice Normal
                                                    </div>
                                                </div>
                                                <div class="text-slate-300 font-mono shrink-0 whitespace-nowrap">Rp 44.000
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-slate-200 font-medium truncate">1x Croissant Butter
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 truncate">Hangatkan</div>
                                                </div>
                                                <div class="text-slate-300 font-mono shrink-0 whitespace-nowrap">Rp 28.000
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Calculations --}}
                                        <div class="py-2 space-y-1 text-[11px] text-slate-400">
                                            <div class="flex justify-between">
                                                <span>Subtotal</span>
                                                <span class="text-slate-300 font-mono">Rp 72.000</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>PB1 (10%)</span>
                                                <span class="text-slate-300 font-mono">Rp 7.200</span>
                                            </div>
                                            <div
                                                class="flex justify-between text-xs font-bold text-white pt-1 border-t border-white/10">
                                                <span>Total Bayar</span>
                                                <span class="text-[#00C4D8] font-mono text-sm">Rp 79.200</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Payment Action --}}
                                    <div class="space-y-2 pt-2">
                                        <div class="grid grid-cols-2 gap-1.5 text-[10px]">
                                            <button
                                                class="py-1 px-1.5 rounded-lg bg-white/10 text-slate-300 border border-white/15 font-medium hover:bg-white/15">QRIS
                                                Dinamis</button>
                                            <button
                                                class="py-1 px-1.5 rounded-lg bg-white/10 text-slate-300 border border-white/15 font-medium hover:bg-white/15">Tunai
                                                (Cash)</button>
                                        </div>
                                        <button
                                            class="w-full py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            <span>Bayar &amp; Cetak Struk</span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                            {{-- Automated Sync Note --}}
                            <div
                                class="mt-2 text-center text-[10px] sm:text-[11px] text-slate-400 flex items-center justify-center gap-1.5 py-1 px-2 text-balance break-words">
                                <i data-lucide="refresh-cw" class="w-3 h-3 text-[#00C4D8] animate-spin shrink-0"></i>
                                <span>Otomatis memotong 2 botol susu, 1 pack butter, dan input kas ke Akuntansi</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- 2. PROBLEM IDENTIFICATION: Kenapa Kasir Standalone Bikin Owner Rugi --}}
        <section
            class="py-16 sm:py-24 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10 text-slate-900 dark:text-white transition-colors">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#38BDF8] text-xs font-bold uppercase tracking-wider mb-3">
                        <span>Friction di Meja Kasir</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Apakah Mesin Kasir Anda Saat Ini Membantu Bisnis, atau Justru Menambah Beban Rekonsiliasi?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3 font-normal">
                        Aplikasi kasir yang terisolasi dari inventori dan pembukuan hanya menyelesaikan transaksi saat itu
                        juga, namun meninggalkan tumpukan masalah di belakang meja.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Rekonsiliasi Manual Setiap Malam
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Kasir dan supervisor harus menghitung manual struk kertas, mencatat total penjualan di Excel,
                            lalu staf akunting menginput ulang esok harinya. Rawan selisih dan membuang 2 jam kerja setiap
                            hari.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="package-minus" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Stok Fisik Selisih &amp; Telat
                            Reorder</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Barang laku keras di kasir, tetapi orang gudang tidak tahu stok sudah menipis. Saat pelanggan
                            berikutnya ingin membeli, barang ternyata kosong. Penjualan hilang seketika karena sistem kasir
                            tidak terhubung gudang.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Uang Kas Kasir Tidak Akurat</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Tanpa kontrol shift kasir yang ketat, uang modal awal tercampur dengan hasil penjualan tunai.
                            Owner kesulitan membuktikan apakah selisih kas terjadi karena kelalaian kembalian atau
                            kecurangan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE FEATURES: Bento Grid Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 text-slate-900 dark:text-white">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00C4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-xs font-bold uppercase tracking-wider mb-3">
                    <span>Fitur Kasir Modern</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Didesain untuk Efisiensi Kasir &amp; Ketenteraman Owner
                </h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3 font-normal">
                    Setiap tombol dan alur transaksi dioptimalkan agar kasir baru dapat mengoperasikan sistem dalam waktu
                    kurang dari 10 menit pelatihan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Kecepatan Transaksi & Barcode (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#38BDF8]">
                            <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Scan Barcode Cepat &amp; Pencarian Fleksibel
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Cukup tembak barcode dengan scanner USB/Bluetooth kamera atau cari nama barang dengan keyboard.
                            COOCA menampilkan varian produk (ukuran, warna, rasa), catatan pesanan khusus, dan harga
                            grosir/member secara instan tanpa memperlambat antrean kasir.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-600 dark:text-slate-300">
                            <span
                                class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                <i data-lucide="check" class="w-4 h-4"></i> Support Barcode Scanner 1D &amp; 2D
                            </span>
                            <span class="hidden sm:inline">•</span>
                            <span
                                class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                <i data-lucide="check" class="w-4 h-4"></i> Varian &amp; Modifier Dinamis
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Bento Card 2: Shift Control & X/Z Report (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Kontrol Shift &amp; Laci Kasir (X/Z Report)
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Modal awal tercatat rapi saat kasir login. Saat tutup kasir, sistem meminta kasir memasukkan
                            uang fisik yang ada, lalu secara otomatis menghasilkan laporan perbandingan untuk mendeteksi
                            selisih secara transparan.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3.5 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Laporan Kasir Z</span>
                        <span class="text-emerald-500 font-bold">Selisih: Rp 0 (Tepat)</span>
                    </div>
                </div>

                {{-- Bento Card 3: Multi-Payment & QRIS Dinamis (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="qr-code" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Multi Pembayaran &amp; QRIS</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Terima pembayaran tunai, kartu debit/kredit, transfer bank, hingga QRIS dinamis di layar kasir
                        dengan nominal yang terkunci otomatis sehingga mencegah salah input angka oleh pelanggan.
                    </p>
                </div>

                {{-- Bento Card 4: Multi-Outlet & Akses Terpusat (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-sky-100 dark:bg-sky-950 flex items-center justify-center text-[#007AFF] dark:text-[#38BDF8]">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Multi-Outlet Terpadu</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Buka 1 cabang atau 50 cabang retail. Kelola seluruh menu harga, stok barang, diskon promosi, dan
                        otorisasi hak akses staf kasir dari satu panel admin pusat milik owner.
                    </p>
                </div>

                {{-- Bento Card 5: Split Bill & Struk Kustom (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pisah Tagihan (Split Bill)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Pelanggan ingin bayar patungan atau bayar sebagian tunai dan sebagian QRIS? Fitur split payment dan
                        split order memungkinkan pembagian tagihan per item dengan sangat leluasa.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. THE CONNECTED SYSTEM CHAIN: Apa yang Terjadi Setelah Tombol Bayar Ditekan --}}
        <section class="py-16 sm:py-24 bg-[#060B1E] text-white border-y border-white/10 relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/20 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Satu Klik di Kasir, Sinkron ke Seluruh Ekosistem</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Mengapa COOCA POS Unggul Jauh dari Aplikasi Kasir Biasa?
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3 font-normal">
                        Saat kasir mencetak struk untuk pelanggan, sistem COOCA secara bersamaan mengeksekusi 4 proses
                        backend bisnis tanpa jeda:
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/30 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Stok Terpotong Otomatis</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Kartu stok outlet langsung berkurang. Jika produk menggunakan Resep/BOM (seperti di kafe atau
                            bakery), bahan mentah (biji kopi, susu, butter) otomatis dipotong secara proporsional.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Jurnal Akuntansi Terbit</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Debit: Kas/Bank Outlet, Kredit: Pendapatan Penjualan, serta Debit: HPP vs Kredit: Persediaan.
                            Buku besar dan laba rugi terupdate saat itu juga tanpa campur tangan admin.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/30 text-purple-300 flex items-center justify-center font-bold text-xs border border-purple-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Poin CRM Bertambah</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Jika kasir memasukkan nomor WhatsApp pelanggan, nominal belanja langsung masuk ke profil
                            pelanggan, poin loyalitas bertambah, dan riwayat pesanan tercatat rapi.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-500/30 text-amber-300 flex items-center justify-center font-bold text-xs border border-amber-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Dashboard Owner Update</h3>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Grafik omzet harian di smartphone owner langsung bergerak naik. Owner mengetahui cabang mana
                            yang ramai pada jam berapa tanpa harus menelepon manajer toko.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. HARDWARE COMPATIBILITY --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 text-slate-900 dark:text-white">
            <div
                class="p-8 sm:p-10 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/50 border border-slate-200/80 dark:border-white/10 shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-5 space-y-4">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Gunakan Perangkat yang Sudah Anda Miliki
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                            Anda tidak perlu terkunci pada mesin POS proprietary mahal bernilai puluhan juta rupiah. COOCA
                            POS fleksibel dan langsung siap digunakan pada perangkat standar yang beredar di pasaran.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-[#007AFF] hover:underline">
                                <span>Hubungi kami untuk rekomendasi printer &amp; scanner</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div
                            class="p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 space-y-2">
                            <i data-lucide="tablet" class="w-6 h-6 text-[#007AFF] dark:text-[#38BDF8] mx-auto"></i>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Tablet &amp; HP</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Android &amp; iPad iOS</div>
                        </div>
                        <div
                            class="p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 space-y-2">
                            <i data-lucide="laptop" class="w-6 h-6 text-[#007AFF] dark:text-[#38BDF8] mx-auto"></i>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">PC &amp; Laptop</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Windows &amp; macOS</div>
                        </div>
                        <div
                            class="p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 space-y-2">
                            <i data-lucide="printer" class="w-6 h-6 text-[#007AFF] dark:text-[#38BDF8] mx-auto"></i>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Printer Thermal</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Bluetooth &amp; USB 58/80mm</div>
                        </div>
                        <div
                            class="p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 space-y-2">
                            <i data-lucide="scan" class="w-6 h-6 text-[#007AFF] dark:text-[#38BDF8] mx-auto"></i>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Barcode Scanner</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Kabel USB &amp; Wireless</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-slate-900 dark:text-white">
            <div class="text-center mb-12">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00C4D8]/15 text-[#0096B4] dark:text-[#00C4D8] text-xs font-bold uppercase tracking-wider mb-2">
                    <span>Pertanyaan Umum</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">Tanya Jawab
                    Seputar COOCA POS</h2>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah COOCA POS bisa digunakan di beberapa cabang atau outlet sekaligus?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        Bisa. COOCA POS dirancang native multi-outlet. Setiap outlet memiliki katalog harga, persediaan stok
                        terpisah, serta laporan shift kasir mandiri yang semuanya terpantau secara terpusat oleh owner.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah kasir harus memakai hardware khusus atau bisa memakai tablet dan HP yang sudah
                            ada?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        Tidak perlu hardware mahal khusus. COOCA POS berjalan lancar di browser tablet Android, iPad, laptop
                        Windows, bahkan smartphone staf, serta kompatibel dengan printer struk thermal standar Bluetooth/USB
                        dan barcode scanner.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apa yang terjadi pada stok dan laporan keuangan saat transaksi kasir selesai?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        Begitu tombol "Selesaikan Pembayaran" ditekan, sistem otomatis memotong stok barang (atau bahan baku
                        jika memakai resep), mencatat penerimaan kas ke modul Finance, dan membuat jurnal debit-kredit
                        otomatis tanpa perlu input ulang di malam hari.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara mencegah kecurangan kasir saat pergantian shift?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed font-normal">
                        COOCA POS memiliki fitur Buka/Tutup Kasir (Shift Control). Kasir wajib memasukkan modal awal dan
                        menghitung fisik kas saat tutup shift. Sistem membandingkan saldo sistem vs saldo fisik aktual (X
                        &amp; Z Report) sehingga selisih uang langsung terdeteksi.
                    </p>
                </details>
            </div>
        </section>

        {{-- 7. RELATED ERP MODULES (Topical Cluster) --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#38BDF8] font-semibold mb-1">
                        Modul Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Lengkapi Operasional POS Anda
                    </p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Jelajahi Semua Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.erp.inventory') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#38BDF8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="boxes" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Manajemen Stok Gudang</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Stok terpusat, mutasi antar cabang, dan
                        kartu stok akurat.</p>
                </a>

                <a href="{{ route('public.erp.finance') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Kas &amp; Keuangan</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Penerimaan kas kasir otomatis masuk ke buku
                        kas harian.</p>
                </a>

                <a href="{{ route('public.erp.crm') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        CRM &amp; Pelanggan</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Database member, poin loyalitas, dan program
                        promosi.</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Penjualan</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Pantau jam sibuk, produk terlaris, dan
                        kinerja kasir.</p>
                </a>
            </div>
        </section>

        {{-- 8. CONVERSION CTA BOTTOM --}}
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
                        Siap Mempercepat Kasir dan Menghentikan Selisih Stok?
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 font-normal">
                        Mulai operasikan POS modern yang terhubung langsung dengan gudang dan buku keuangan Anda hari ini
                        juga.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Interaktif POS
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 transition-all">
                            Konsultasi Kebutuhan Outlet
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
