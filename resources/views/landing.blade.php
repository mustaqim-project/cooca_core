@extends('layouts.public_marketing')

    @push('seo')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "Cooca UMKM",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud-based",
        "description": "Business Operating System & Omnichannel ERP gratis selamanya untuk UMKM Indonesia: HPP presisi, AI Assistant, POS kasir, dan pembukuan otomatis.",
        "url": "https://cooca.id",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR",
            "availability": "https://schema.org/InStock"
        },
        "publisher": {
            "@@type": "Organization",
            "name": "COOCA.ID",
            "url": "https://cooca.id"
        }
    }
    </script>
    @endpush

@section('content')
    <div class="relative overflow-hidden">

        <!-- ═══ 1. HERO SECTION (Apple HIG Minimalist 2-Column Showcase) ═══ -->
        <section class="relative pt-10 pb-16 md:pt-20 md:pb-24 overflow-hidden">
            <!-- Apple Subtle Ambient Soft Glows (Strictly Monochromatic & Apple Blue / Green - Zero Purple) -->
            <div
                class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[520px] pointer-events-none opacity-40 dark:opacity-25 -z-10">
                <div
                    class="absolute top-12 left-12 w-96 h-96 bg-[#007AFF]/12 dark:bg-[#0A84FF]/15 rounded-full blur-[140px]">
                </div>
                <div
                    class="absolute top-24 right-16 w-80 h-80 bg-[#34C759]/10 dark:bg-[#30D158]/12 rounded-full blur-[130px]">
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                    <!-- Kiri: Value Proposition & Conversion Funnel -->
                    <div class="lg:col-span-6 space-y-6 text-center lg:text-left">


                        <!-- Apple SF Pro Headline (High Contrast - No Tacky Gradients) -->
                        <h1
                            class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-[#1D1D1F] dark:text-[#F5F5F7] leading-[1.08]">
                            <span class="block">Kelola Bisnis UMKM</span>
                            <span class="text-[#007AFF] dark:text-[#0A84FF] block">Lebih Cerdas &amp; Presisi</span>
                        </h1>

                        <p
                            class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] max-w-xl leading-relaxed font-normal mx-auto lg:mx-0">
                            <strong class="font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">Cooca</strong> adalah sistem
                            operasi bisnis terlengkap: <span class="text-[#007AFF] dark:text-[#0A84FF] font-medium">HPP
                                presisi</span>, <span class="text-[#34C759] dark:text-[#30D158] font-medium">POS
                                Kasir</span>, stok real-time, pembukuan otomatis, dan asisten AI tanpa biaya lisensi
                            bulanan.
                        </p>

                        <!-- CTAs dengan State Terautentikasi / Tamu -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-1">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="w-full sm:w-auto glow-btn px-7 py-3.5 rounded-[16px] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.3)] hover:shadow-[0_4px_16px_rgba(0,122,255,0.4)] active:scale-[0.98] transition-all">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    <span>Buka Dashboard Admin</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="w-full sm:w-auto glow-btn px-7 py-3.5 rounded-[16px] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.3)] hover:shadow-[0_4px_16px_rgba(0,122,255,0.4)] active:scale-[0.98] transition-all">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    <span>Buka Dashboard Bisnis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="w-full sm:w-auto glow-btn px-7 py-3.5 rounded-[16px] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.3)] hover:shadow-[0_4px_16px_rgba(0,122,255,0.4)] active:scale-[0.98] transition-all">
                                    <span>Mulai Sekarang - Gratis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('auth.google') }}"
                                    class="w-full sm:w-auto px-6 py-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-sm flex items-center justify-center gap-2.5 hover:bg-black/[0.02] dark:hover:bg-white/[0.04] shadow-sm active:scale-[0.98] transition-all">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                                        <path fill="#EA4335"
                                            d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z" />
                                        <path fill="#4285F4"
                                            d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z" />
                                        <path fill="#FBBC05"
                                            d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z" />
                                        <path fill="#34A853"
                                            d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z" />
                                    </svg>
                                    <span>Daftar via Google</span>
                                </a>
                            @endif
                        </div>

                        <!-- Trust Signals (Apple System Badges) -->
                        <div
                            class="flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 text-xs font-medium text-[#6E6E73] dark:text-[#86868B] pt-2">
                            <span class="flex items-center gap-1.5"><i data-lucide="shield-check"
                                    class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i> Tanpa Kartu Kredit</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="zap"
                                    class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]"></i> Setup Cepat 2 Menit</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="lock"
                                    class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i> Data Aman &amp;
                                Terenkripsi</span>
                        </div>

                        <!-- Social Proof Stats Bento -->
                        <div class="grid grid-cols-3 gap-3 pt-3 max-w-lg mx-auto lg:mx-0">
                            <div
                                class="p-3.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-center shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                                <div
                                    class="text-xl sm:text-2xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">
                                    10.000+</div>
                                <div class="text-[11px] font-medium text-[#6E6E73] dark:text-[#86868B] mt-0.5">UMKM
                                    Terdaftar</div>
                            </div>
                            <div
                                class="p-3.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-center shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                                <div
                                    class="text-xl sm:text-2xl font-extrabold text-[#34C759] dark:text-[#30D158] font-mono">
                                    99.8%</div>
                                <div class="text-[11px] font-medium text-[#6E6E73] dark:text-[#86868B] mt-0.5">Akurasi
                                    Finansial</div>
                            </div>
                            <div
                                class="p-3.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-center shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                                <div
                                    class="text-xl sm:text-2xl font-extrabold text-[#007AFF] dark:text-[#0A84FF] font-mono">
                                    100%</div>
                                <div class="text-[11px] font-medium text-[#6E6E73] dark:text-[#86868B] mt-0.5">Gratis
                                    Selamanya</div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Apple macOS Realistic Window Mockup Showcase -->
                    <div class="lg:col-span-6 flex justify-center lg:justify-end">
                        <div class="relative w-full max-w-lg xl:max-w-xl">
                            <!-- macOS Inset Squircle Window -->
                            <div
                                class="relative rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.08)] dark:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] p-5 sm:p-6 backdrop-blur-2xl transition-all">
                                <!-- macOS Window Header Bar -->
                                <div
                                    class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3.5 mb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></div>
                                        <div class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></div>
                                        <div class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></div>
                                        <span class="text-xs font-semibold text-[#6E6E73] dark:text-[#86868B] ml-2">Cooca OS
                                            • Executive Dashboard</span>
                                    </div>
                                </div>

                                <!-- 4 Key Performance Metrics (Bento 2x2) -->
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div
                                        class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                        <div class="text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">
                                            Omzet Hari Ini</div>
                                        <div
                                            class="text-base sm:text-lg font-bold text-[#34C759] dark:text-[#30D158] font-mono mt-0.5">
                                            Rp 4.250.000</div>
                                        <div
                                            class="text-[10px] text-[#34C759] dark:text-[#30D158] font-medium flex items-center gap-0.5 mt-0.5">
                                            <i data-lucide="trending-up" class="w-3 h-3"></i> +14.8% vs kemarin
                                        </div>
                                    </div>
                                    <div
                                        class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                        <div class="text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">
                                            Margin Laba Bersih</div>
                                        <div
                                            class="text-base sm:text-lg font-bold text-[#007AFF] dark:text-[#0A84FF] font-mono mt-0.5">
                                            32.4%</div>
                                        <div class="text-[10px] text-[#6E6E73] dark:text-[#86868B] mt-0.5">Net Profit Rp
                                            1.377.000</div>
                                    </div>
                                    <div
                                        class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                        <div class="text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">
                                            Stok Kritis Gudang</div>
                                        <div
                                            class="text-base sm:text-lg font-bold text-[#FF9500] dark:text-[#FF9F0A] font-mono mt-0.5">
                                            3 Bahan</div>
                                        <div
                                            class="text-[10px] text-[#FF9500] dark:text-[#FF9F0A] flex items-center gap-0.5 mt-0.5">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i> Segera restock
                                        </div>
                                    </div>
                                    <div
                                        class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                        <div class="text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">
                                            Transaksi POS</div>
                                        <div
                                            class="text-base sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono mt-0.5">
                                            142 Struk</div>
                                        <div class="text-[10px] text-[#34C759] dark:text-[#30D158] mt-0.5">AOV Rp 29.900
                                        </div>
                                    </div>
                                </div>

                                <!-- Mini 7-Day Sales Dynamic Sparkline Chart -->
                                <div
                                    class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] mb-3">
                                    <div
                                        class="flex items-center justify-between text-[11px] font-medium text-[#6E6E73] dark:text-[#86868B] mb-2">
                                        <span>Tren Penjualan 7 Hari Terakhir</span>
                                        <span class="text-[#34C759] dark:text-[#30D158] font-semibold">+23.5% minggu
                                            ini</span>
                                    </div>
                                    <div class="flex items-end gap-1.5 h-16 pt-2">
                                        <div class="flex-1 bg-[#007AFF]/25 dark:bg-[#0A84FF]/30 hover:bg-[#007AFF] h-[45%] rounded-[6px] transition-all"
                                            title="Senin: Rp 2.8Jt"></div>
                                        <div class="flex-1 bg-[#007AFF]/35 dark:bg-[#0A84FF]/40 hover:bg-[#007AFF] h-[60%] rounded-[6px] transition-all"
                                            title="Selasa: Rp 3.4Jt"></div>
                                        <div class="flex-1 bg-[#007AFF]/30 dark:bg-[#0A84FF]/35 hover:bg-[#007AFF] h-[40%] rounded-[6px] transition-all"
                                            title="Rabu: Rp 2.5Jt"></div>
                                        <div class="flex-1 bg-[#007AFF]/50 dark:bg-[#0A84FF]/55 hover:bg-[#007AFF] h-[75%] rounded-[6px] transition-all"
                                            title="Kamis: Rp 4.1Jt"></div>
                                        <div class="flex-1 bg-[#007AFF]/45 dark:bg-[#0A84FF]/50 hover:bg-[#007AFF] h-[65%] rounded-[6px] transition-all"
                                            title="Jumat: Rp 3.7Jt"></div>
                                        <div class="flex-1 bg-[#007AFF] dark:bg-[#0A84FF] h-[100%] rounded-[6px] shadow-sm transition-all"
                                            title="Sabtu: Rp 5.2Jt"></div>
                                        <div class="flex-1 bg-[#007AFF]/75 dark:bg-[#0A84FF]/80 hover:bg-[#007AFF] h-[85%] rounded-[6px] transition-all"
                                            title="Minggu: Rp 4.6Jt"></div>
                                    </div>
                                </div>

                                <!-- AI Assistant Bubble (Clean Apple Inset) -->
                                <div
                                    class="flex items-center gap-2.5 p-3 rounded-[16px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 border border-[#007AFF]/20 text-xs">
                                    <div
                                        class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center shrink-0">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-[#1D1D1F] dark:text-[#F5F5F7] font-medium truncate flex-1">
                                        AI: "Margin produk Kopi Susu naik 4% setelah revisi bahan baku."
                                    </span>
                                </div>
                            </div>


                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ 2. BENTO SECTION: MODUL EKOSISTEM (Apple Bento Grid 4 & 6 Col) ═══ -->
        <section id="modul"
            class="py-16 md:py-24 border-t border-black/[0.06] dark:border-white/[0.08] bg-white/40 dark:bg-[#121214]/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center space-y-3 max-w-2xl mx-auto">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Semua
                        Fitur Dalam Satu Ekosistem</h2>
                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B]">Dari penetapan harga modal hingga
                        pencatatan kas harian, seluruh modul saling terhubung otomatis.</p>
                </div>

                <!-- Bento Architecture: Desktop 6-col / Mobile Adaptif 2-col Bento Grid -->
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3 sm:gap-4 md:gap-6">

                    <!-- Hero Bento Card: Kasir POS & Food Costing (Spans 2-col on Mobile, 4-col on Desktop) -->
                    <div
                        class="col-span-2 md:col-span-4 glass-card p-5 sm:p-8 rounded-[24px] sm:rounded-[28px] flex flex-col justify-between group hover:border-[#34C759]/30 transition-all">
                        <div class="space-y-3.5 sm:space-y-4">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-[16px] sm:rounded-[18px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                                    <i data-lucide="shopping-cart" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                </div>
                                <span
                                    class="px-2.5 sm:px-3 py-1 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">
                                    Kasir Kilat &amp; POS
                                </span>
                            </div>
                            <h3 class="text-lg sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Terminal POS Kasir
                                &amp; HPP Terintegrasi</h3>
                            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl leading-relaxed">
                                Cetak struk bluetooth thermal, terima pembayaran QRIS instan, scan barcode kamera, dan
                                otomatis potong stok bahan resep saat transaksi berlangsung.
                            </p>

                            <!-- Inset Preview Strip (Apple 3-Item Bento Shelf on Mobile & Desktop) -->
                            <div class="grid grid-cols-3 gap-2 sm:gap-3 pt-2">
                                <div
                                    class="p-2.5 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div
                                        class="text-[10px] sm:text-[11px] font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1 sm:gap-1.5 truncate">
                                        <i data-lucide="printer" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i> <span
                                            class="truncate">Struk Thermal</span>
                                    </div>
                                    <div
                                        class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] mt-0.5 truncate">
                                        Bluetooth 58/80mm</div>
                                </div>
                                <div
                                    class="p-2.5 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div
                                        class="text-[10px] sm:text-[11px] font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1 sm:gap-1.5 truncate">
                                        <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i> <span
                                            class="truncate">QRIS Dinamis</span>
                                    </div>
                                    <div
                                        class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] mt-0.5 truncate">
                                        Scan cepat no fee</div>
                                </div>
                                <div
                                    class="p-2.5 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div
                                        class="text-[10px] sm:text-[11px] font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1 sm:gap-1.5 truncate">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#FF9500] shrink-0"></i> <span
                                            class="truncate">Bon WhatsApp</span>
                                    </div>
                                    <div
                                        class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] mt-0.5 truncate">
                                        Kirim nota 1-klik</div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="pt-4 sm:pt-6 mt-4 sm:mt-6 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                            <span class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B]">Bekerja offline &amp;
                                sync otomatis</span>
                            @if (auth('web')->check())
                                <a href="{{ route('pos.terminal') }}"
                                    class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                                    <span>Buka Kasir</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @elseif (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                                    <span>Dashboard Admin</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                                    <span>Coba Gratis</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Compact Bento Widget: Pembukuan & Laba Rugi (Spans 1-col on Mobile, 2-col on Desktop) -->
                    <div
                        class="col-span-1 md:col-span-2 glass-card p-4 sm:p-7 rounded-[22px] sm:rounded-[28px] flex flex-col justify-between group hover:border-[#007AFF]/30 transition-all">
                        <div class="space-y-2.5 sm:space-y-4">
                            <div
                                class="w-9 h-9 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="book-open-check" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                            </div>
                            <div>
                                <span
                                    class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Otomasi
                                    Akuntansi</span>
                                <h3
                                    class="text-sm sm:text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-0.5 leading-snug">
                                    Laba Rugi Riil</h3>
                            </div>
                            <p
                                class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed hidden sm:block">
                                Buku kas dan laporan laba bersih otomatis tersusun saat transaksi kasir input.
                            </p>

                            <div
                                class="p-3 sm:p-4 rounded-[16px] sm:rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                <div
                                    class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">
                                    Net Profit Akurat</div>
                                <div
                                    class="text-base sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono mt-0.5">
                                    Rp 18.4Jt</div>
                                <div
                                    class="text-[9px] sm:text-[10px] text-[#34C759] dark:text-[#30D158] font-medium mt-0.5">
                                    +18.2% bulan ini</div>
                            </div>
                        </div>

                        <div class="pt-3 sm:pt-4 border-t border-black/[0.06] dark:border-white/[0.08] mt-3 sm:mt-4">
                            <a href="{{ route('template.index') }}"
                                class="text-[11px] sm:text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center justify-between sm:justify-start gap-1">
                                <span>Template Excel</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Compact Bento Widget: Inventori & Stok (Spans 1-col on Mobile, 3-col on Desktop) -->
                    <div
                        class="col-span-1 md:col-span-3 glass-card p-4 sm:p-7 rounded-[22px] sm:rounded-[28px] flex flex-col justify-between group hover:border-[#FF9500]/30 transition-all">
                        <div class="space-y-2.5 sm:space-y-4">
                            <div
                                class="w-9 h-9 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[18px] bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                <i data-lucide="package" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                            </div>
                            <div>
                                <span
                                    class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#FF9500] dark:text-[#FF9F0A] block">Gudang
                                    &amp; Stok</span>
                                <h3
                                    class="text-sm sm:text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-0.5 leading-snug">
                                    Mutasi Stok</h3>
                            </div>
                            <p
                                class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed hidden sm:block">
                                Beli dus, jual sachet. Kartu stok otomatis memantau peringatan restock bahan.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                <div
                                    class="p-2.5 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <span
                                        class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">Stok
                                        SKU</span>
                                    <div
                                        class="text-sm sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono mt-0.5">
                                        1.240 Item</div>
                                </div>
                                <div
                                    class="p-2.5 sm:p-3.5 rounded-[14px] sm:rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                                    <span
                                        class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] uppercase font-semibold">Menipis</span>
                                    <div
                                        class="text-sm sm:text-lg font-bold text-[#FF9500] dark:text-[#FF9F0A] font-mono mt-0.5">
                                        3 SKU</div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 sm:pt-4 border-t border-black/[0.06] dark:border-white/[0.08] mt-3 sm:mt-4">
                            <a href="{{ route('kalkulator.hpp') }}"
                                class="text-[11px] sm:text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center justify-between sm:justify-start gap-1">
                                <span>Hitung Resep HPP</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Wide Bento Card: AI Business Assistant (Spans 2-col on Mobile, 3-col on Desktop) -->
                    <div
                        class="col-span-2 md:col-span-3 glass-card p-5 sm:p-7 rounded-[24px] sm:rounded-[28px] flex flex-col justify-between group hover:border-[#007AFF]/30 transition-all">
                        <div class="space-y-3.5 sm:space-y-4">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-[16px] sm:rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                    <i data-lucide="bot" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                </div>
                                <span
                                    class="px-2.5 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-[10px] font-bold uppercase flex items-center gap-1">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                                    <span>AI Assistant</span>
                                </span>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Konsultan
                                    Bisnis Pintar</h3>
                                <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed mt-1">
                                    Tanyakan strategi harga jual, simulasi harga bahan baku naik, atau deteksi produk
                                    penyedot modal tanpa return.
                                </p>
                            </div>

                            <!-- Prompt Pills (Apple Intelligence Bubble Style) -->
                            <div class="space-y-2">
                                <div
                                    class="p-2.5 sm:p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-xs text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-2">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                    <span class="truncate">"Berapa harga jual ideal ayam geprek jika cabai naik
                                        25%?"</span>
                                </div>
                                <div
                                    class="p-2.5 sm:p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-xs text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-2">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                    <span class="truncate">"Audit margin laba bersih produk terlaris minggu ini."</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 sm:pt-4 border-t border-black/[0.06] dark:border-white/[0.08] mt-3 sm:mt-4">
                            <a href="{{ route('kalkulator.simulasi-what-if') }}"
                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1">
                                <span>Coba Simulasi AI What-If</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ 3. BENTO SECTION: SOLUSI 20+ INDUSTRI (Responsive 2-Col Mobile Bento Grid) ═══ -->
        <section id="industri" class="py-16 md:py-24 border-t border-black/[0.06] dark:border-white/[0.08]"
            x-data="{
                activeTab: 'all',
                industries: [
                    { slug: 'kasir-warung', name: 'Warung & Kelontong', category: 'retail', icon: 'shopping-basket', badge: 'Grosir & Bon WA', desc: 'Jual eceran & dus, catat bon hutang langganan, dan scan barcode.' },
                    { slug: 'kasir-cafe-kecil', name: 'Kafe & Kedai Kopi', category: 'fnb', icon: 'coffee', badge: 'HPP Resep Cup', desc: 'Resep cup kopi, nomor meja, split bill, dan cetak tiket dapur.' },
                    { slug: 'kasir-kios', name: 'Konter HP & Pulsa', category: 'retail', icon: 'smartphone', badge: 'Barcode Kilat', desc: 'Pencarian casing, kabel data, voucher & nota servis HP.' },
                    { slug: 'kasir-laundry', name: 'Laundry Kiloan', category: 'service', icon: 'shirt', badge: 'Nota WA Otomatis', desc: 'Tracking status cuci, setrika, siap ambil, dan nomor rak.' },
                    { slug: 'kasir-salon', name: 'Salon & Spa', category: 'service', icon: 'sparkles', badge: 'Komisi Terapis', desc: 'Bagi hasil terapis, riwayat treatment, & paket bundling.' },
                    { slug: 'kasir-barbershop', name: 'Barbershop', category: 'service', icon: 'scissors', badge: 'Antrean Kursi', desc: 'Barberman favorit, komisi per kepala potong, & pomade.' },
                    { slug: 'kasir-bengkel-kecil', name: 'Bengkel Motor', category: 'service', icon: 'wrench', badge: 'Jasa + Part', desc: 'Gabungkan jasa montir dan sparepart, simpan riwayat nopol.' },
                    { slug: 'kasir-irt', name: 'Produsen Snack IRT', category: 'fnb', icon: 'cookie', badge: 'HPP 3-Pilar', desc: 'Modal bahan, gas & titip jual konsinyasi mitra.' }
                ],
                filtered() {
                    if (this.activeTab === 'all') return this.industries;
                    return this.industries.filter(i => i.category === this.activeTab);
                }
            }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3 max-w-2xl mx-auto">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold text-xs">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        <span>Solusi 20+ Vertikal Industri</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Disesuaikan untuk Setiap Usaha</h2>
                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B]">Setiap industri memiliki alur
                        operasional unik. Cooca siap mengakomodasi kebutuhan Anda.</p>
                </div>

                <!-- Apple Segmented Filter Chips -->
                <div class="flex items-center justify-center">
                    <div
                        class="inline-flex p-1 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold overflow-x-auto max-w-full">
                        <button type="button" @click="activeTab = 'all'"
                            :class="activeTab === 'all' ?
                                'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]'"
                            class="px-3 sm:px-4 py-1.5 rounded-full transition-all duration-150 whitespace-nowrap">Semua</button>
                        <button type="button" @click="activeTab = 'fnb'"
                            :class="activeTab === 'fnb' ?
                                'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]'"
                            class="px-3 sm:px-4 py-1.5 rounded-full transition-all duration-150 whitespace-nowrap">F&amp;B</button>
                        <button type="button" @click="activeTab = 'service'"
                            :class="activeTab === 'service' ?
                                'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]'"
                            class="px-3 sm:px-4 py-1.5 rounded-full transition-all duration-150 whitespace-nowrap">Jasa</button>
                        <button type="button" @click="activeTab = 'retail'"
                            :class="activeTab === 'retail' ?
                                'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                'text-[#6E6E73] dark:text-[#86868B] hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7]'"
                            class="px-3 sm:px-4 py-1.5 rounded-full transition-all duration-150 whitespace-nowrap">Retail</button>
                    </div>
                </div>

                <!-- Apple Bento Grid 2-Col on Mobile / 4-Col on Desktop -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                    <template x-for="ind in filtered()" :key="ind.slug">
                        <a :href="'/solusi/' + ind.slug"
                            class="glass-card p-3.5 sm:p-6 rounded-[20px] sm:rounded-[24px] flex flex-col justify-between group hover:border-[#007AFF]/30 active:scale-[0.98] transition-all">
                            <div class="space-y-2.5 sm:space-y-3.5">
                                <div class="flex items-center justify-between">
                                    <div
                                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-[12px] sm:rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center group-hover:scale-105 transition-transform">
                                        <i :data-lucide="ind.icon" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-[9px] sm:text-[10px] font-bold truncate max-w-[100px]"
                                        x-text="ind.badge"></span>
                                </div>

                                <h3 class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug line-clamp-2"
                                    x-text="ind.name"></h3>

                                <p class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed line-clamp-2"
                                    x-text="ind.desc"></p>
                            </div>

                            <div
                                class="pt-3 sm:pt-4 mt-3 sm:mt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px] sm:text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                <span>Lihat Solusi</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </section>

        <!-- ═══ 4. BENTO SECTION: MULTI-DEVICE & BROWSER (Apple Hardware Ecosystem Bento) ═══ -->
        <section
            class="py-16 md:py-24 border-t border-black/[0.06] dark:border-white/[0.08] bg-white/40 dark:bg-[#121214]/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center space-y-3 max-w-2xl mx-auto">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] font-semibold text-xs">
                        <i data-lucide="devices" class="w-3.5 h-3.5"></i>
                        <span>Akses Fleksibel Tanpa Install</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Satu
                        Akun di Semua Perangkat</h2>
                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B]">Tanpa install aplikasi. Buka browser
                        favorit Anda dan aplikasi langsung siap melayani transaksi.</p>
                </div>

                <!-- Bento 3 Devices Cards: 2-Col Mobile Bento (1 Wide + 2 Compact) / 3-Col Desktop -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 md:gap-6">

                    <!-- Device 1: Laptop & Desktop (Spans 2-col on Mobile, 1-col on Desktop) -->
                    <div
                        class="col-span-2 md:col-span-1 glass-card p-5 sm:p-7 rounded-[22px] sm:rounded-[26px] space-y-3 sm:space-y-4 group">
                        <div class="flex items-center justify-between">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="monitor" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-2.5 py-0.5 rounded-full bg-[#007AFF]/10">PC
                                &amp; Mac</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Executive Workstation
                        </h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Layar lebar optimal untuk formula HPP resep, impor katalog barang via Excel, dan audit pembukuan
                            bulanan.
                        </p>
                        <div
                            class="grid grid-cols-2 sm:grid-cols-1 gap-1.5 pt-1 text-[11px] sm:text-xs font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <div class="flex items-center gap-1.5"><i data-lucide="check"
                                    class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i><span class="truncate">Shortcut
                                    keyboard kilat</span></div>
                            <div class="flex items-center gap-1.5"><i data-lucide="check"
                                    class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i><span class="truncate">Export Excel
                                    &amp; PDF</span></div>
                        </div>
                    </div>

                    <!-- Device 2: Tablet & iPad (Spans 1-col on Mobile, 1-col on Desktop) -->
                    <div
                        class="col-span-1 md:col-span-1 glass-card p-4 sm:p-7 rounded-[22px] sm:rounded-[26px] space-y-2.5 sm:space-y-4 group">
                        <div
                            class="w-9 h-9 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                            <i data-lucide="tablet" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                        </div>
                        <span
                            class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158] block truncate">iPad
                            &amp; Tablet</span>
                        <h3 class="text-sm sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] leading-snug">Kasir Meja
                        </h3>
                        <p
                            class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed hidden sm:block">
                            Layar sentuh elegan untuk kafe, resto, dan salon. Grid foto menu sentuh cepat.
                        </p>
                        <div class="pt-1 text-[10px] sm:text-xs space-y-1 font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <div class="flex items-center gap-1"><i data-lucide="check"
                                    class="w-3 h-3 text-[#34C759] shrink-0"></i><span class="truncate">Split bill &amp;
                                    meja</span></div>
                            <div class="flex items-center gap-1"><i data-lucide="check"
                                    class="w-3 h-3 text-[#34C759] shrink-0"></i><span class="truncate">Thermal
                                    Bluetooth</span></div>
                        </div>
                    </div>

                    <!-- Device 3: Smartphone (Spans 1-col on Mobile, 1-col on Desktop) -->
                    <div
                        class="col-span-1 md:col-span-1 glass-card p-4 sm:p-7 rounded-[22px] sm:rounded-[26px] space-y-2.5 sm:space-y-4 group">
                        <div
                            class="w-9 h-9 sm:w-12 sm:h-12 rounded-[14px] sm:rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                            <i data-lucide="smartphone" class="w-4 h-4 sm:w-6 sm:h-6"></i>
                        </div>
                        <span
                            class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-[#FF9500] dark:text-[#FF9F0A] block truncate">iPhone
                            &amp; Android</span>
                        <h3 class="text-sm sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] leading-snug">Pantau
                            Mobile</h3>
                        <p
                            class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed hidden sm:block">
                            Cek omzet toko live dari mana saja. Kamera HP langsung berfungsi sebagai scanner barcode.
                        </p>
                        <div class="pt-1 text-[10px] sm:text-xs space-y-1 font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <div class="flex items-center gap-1"><i data-lucide="check"
                                    class="w-3 h-3 text-[#34C759] shrink-0"></i><span class="truncate">Scan kamera
                                    HP</span></div>
                            <div class="flex items-center gap-1"><i data-lucide="check"
                                    class="w-3 h-3 text-[#34C759] shrink-0"></i><span class="truncate">Live notifikasi
                                    kas</span></div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ 5. BENTO PLAYGROUND: LIVE INTERACTIVE HPP & PRICING SIMULATOR ═══ -->
        <section id="kalkulator-live" class="py-16 md:py-24 border-t border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center space-y-3 max-w-2xl mx-auto">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold text-xs">
                        <i data-lucide="play" class="w-3.5 h-3.5"></i>
                        <span>Interactive Playground</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Coba
                        Langsung di Browser Anda</h2>
                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B]">Geser nilai biaya di bawah ini dan
                        perhatikan bagaimana harga jual rekomendasi dan margin laba bereaksi secara real-time.</p>
                </div>

                <!-- Bento Playground (8-col input + 4-col sticky result) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
                    matCost: 25000,
                    labCost: 6500,
                    ovhCost: 8500,
                    targetRate: 40,
                    isMargin: true,
                    get hpp() { return this.matCost + this.labCost + this.ovhCost; },
                    get price() {
                        if (this.isMargin) {
                            let m = Math.min(this.targetRate, 95);
                            return Math.round(this.hpp / (1 - (m / 100)));
                        }
                        return Math.round(this.hpp * (1 + (this.targetRate / 100)));
                    },
                    get profit() { return this.price - this.hpp; },
                    get marginPct() { return this.price > 0 ? Math.round((this.profit / this.price) * 100) : 0; },
                    get markupPct() { return this.hpp > 0 ? Math.round((this.profit / this.hpp) * 100) : 0; }
                }">

                    <!-- Left: Apple Inset Controls (8 Kolom) -->
                    <div
                        class="glass-card p-6 sm:p-8 rounded-[28px] lg:col-span-8 flex flex-col justify-between space-y-6">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div>
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Simulasi
                                    HPP 3-Pilar</span>
                                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-0.5">Komponen Modal
                                    Langsung</h3>
                            </div>

                            <!-- Segmented Switcher -->
                            <div
                                class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-full border border-black/[0.04] dark:border-white/[0.06] text-xs font-semibold shrink-0">
                                <button type="button" @click="isMargin = true"
                                    :class="isMargin ?
                                        'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                        'text-[#6E6E73] dark:text-[#86868B]'"
                                    class="px-4 py-1 rounded-full transition-all">Margin</button>
                                <button type="button" @click="isMargin = false"
                                    :class="!isMargin ?
                                        'bg-white dark:bg-[#1C1C1E] text-[#1D1D1F] dark:text-[#F5F5F7] shadow-sm' :
                                        'text-[#6E6E73] dark:text-[#86868B]'"
                                    class="px-4 py-1 rounded-full transition-all">Markup</button>
                            </div>
                        </div>

                        <!-- 3 Sliders Inset Containers (Mobile 2-Col Bento Layout) -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
                            <div
                                class="col-span-1 p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1.5 sm:space-y-2">
                                <div
                                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs font-medium text-[#6E6E73] dark:text-[#86868B]">
                                    <span class="truncate">Bahan Baku</span>
                                    <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] font-mono">Rp <span
                                            x-text="matCost.toLocaleString('id-ID')"></span></span>
                                </div>
                                <input type="range" x-model.number="matCost" min="5000" max="60000"
                                    step="1000" class="w-full accent-[#007AFF] cursor-pointer">
                                <span
                                    class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] block truncate">Bahan
                                    per porsi</span>
                            </div>
                            <div
                                class="col-span-1 p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1.5 sm:space-y-2">
                                <div
                                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs font-medium text-[#6E6E73] dark:text-[#86868B]">
                                    <span class="truncate">Tenaga Kerja</span>
                                    <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">Rp <span
                                            x-text="labCost.toLocaleString('id-ID')"></span></span>
                                </div>
                                <input type="range" x-model.number="labCost" min="1000" max="30000"
                                    step="500" class="w-full accent-[#007AFF] cursor-pointer">
                                <span
                                    class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] block truncate">Upah
                                    per unit</span>
                            </div>
                            <div
                                class="col-span-2 sm:col-span-1 p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1.5 sm:space-y-2">
                                <div
                                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs font-medium text-[#6E6E73] dark:text-[#86868B]">
                                    <span class="truncate">Overhead Listrik/Gas</span>
                                    <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">Rp <span
                                            x-text="ovhCost.toLocaleString('id-ID')"></span></span>
                                </div>
                                <input type="range" x-model.number="ovhCost" min="1000" max="25000"
                                    step="500" class="w-full accent-[#007AFF] cursor-pointer">
                                <span
                                    class="text-[9px] sm:text-[10px] text-[#6E6E73] dark:text-[#86868B] block truncate">Gas,
                                    listrik, operasional</span>
                            </div>
                        </div>

                        <!-- Target Markup / Margin Slider & Result -->
                        <div
                            class="p-5 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-5">
                            <div class="space-y-1.5 w-full sm:w-1/2">
                                <div class="flex justify-between text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span>Target <span x-text="isMargin ? 'Gross Margin' : 'Markup'"></span>:</span>
                                    <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF]"
                                        x-text="targetRate + '%'"></span>
                                </div>
                                <input type="range" x-model.number="targetRate" min="10" max="80"
                                    step="5" class="w-full accent-[#007AFF] cursor-pointer">
                            </div>

                            <div
                                class="flex items-center gap-6 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 pt-3 sm:pt-0 border-black/[0.06] dark:border-white/[0.08]">
                                <div>
                                    <div class="text-[10px] font-bold uppercase text-[#6E6E73] dark:text-[#86868B]">Total
                                        HPP Modal</div>
                                    <div
                                        class="text-base sm:text-lg font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">
                                        Rp <span x-text="hpp.toLocaleString('id-ID')"></span></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase text-[#34C759] dark:text-[#30D158]">Harga
                                        Jual Saran</div>
                                    <div
                                        class="text-xl sm:text-2xl font-extrabold text-[#34C759] dark:text-[#30D158] font-mono">
                                        Rp <span x-text="price.toLocaleString('id-ID')"></span></div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="text-xs text-center text-[#6E6E73] dark:text-[#86868B] border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                            <span x-show="isMargin">Gross Margin: <strong class="text-[#34C759] dark:text-[#30D158]"
                                    x-text="marginPct + '%'"></strong> • Keuntungan per Satuan: <strong
                                    class="text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="profit.toLocaleString('id-ID')"></span></strong></span>
                            <span x-show="!isMargin">Markup Modal: <strong class="text-[#007AFF] dark:text-[#0A84FF]"
                                    x-text="markupPct + '%'"></strong> • Keuntungan per Satuan: <strong
                                    class="text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="profit.toLocaleString('id-ID')"></span></strong></span>
                        </div>
                    </div>

                    <!-- Right: Sticky Bento Output Card (4 Kolom) -->
                    <div
                        class="glass-card p-6 sm:p-7 rounded-[28px] lg:col-span-4 flex flex-col justify-between space-y-5">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">Rekomendasi
                                    Real-Time</span>
                                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                            </div>

                            <div>
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B]">Harga Jual Ideal:</div>
                                <div class="text-3xl font-black text-[#34C759] dark:text-[#30D158] font-mono mt-1">
                                    Rp <span x-text="price.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">
                                    Profit per porsi: <strong class="text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">Rp <span
                                            x-text="profit.toLocaleString('id-ID')"></span></strong>
                                </div>
                            </div>

                            <!-- Mini breakdown table -->
                            <div
                                class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2 text-xs">
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Bahan Baku:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="matCost.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Upah &amp; Overhead:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="(labCost + ovhCost).toLocaleString('id-ID')"></span></span>
                                </div>
                                <div
                                    class="flex justify-between font-semibold border-t border-black/[0.04] dark:border-white/[0.06] pt-1.5 text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span>Total Modal HPP:</span>
                                    <span class="font-mono">Rp <span x-text="hpp.toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            @if (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="w-full glow-btn py-3.5 rounded-[16px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Buka Dashboard Bisnis</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @elseif (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="w-full glow-btn py-3.5 rounded-[16px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Buka Dashboard Admin</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="w-full glow-btn py-3.5 rounded-[16px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Kunci HPP di Akun Kasir Anda</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                            <a href="{{ route('kalkulator.hpp') }}"
                                class="w-full py-2.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] text-center text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] block transition-colors">
                                Buka Versi Lengkap Detail HPP
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ 6. FAQ ACCORDION + BENTO SUPPORT DESK 24/7 ═══ -->
        <section id="faq"
            class="py-16 md:py-24 border-t border-black/[0.06] dark:border-white/[0.08] bg-white/40 dark:bg-[#121214]/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center space-y-3 max-w-2xl mx-auto">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold text-xs">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                        <span>Tanya Jawab &amp; Layanan Dukungan</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Frequently Asked Questions</h2>
                    <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B]">Jawaban transparan seputar komitmen
                        gratis selamanya, keamanan data, dan integrasi perangkat.</p>
                </div>

                <!-- 2-Column: Left Accordion (7 Col) + Right Bento Support Desk (5 Col) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- Left: Apple Inset Accordion (7 Col) -->
                    <div class="lg:col-span-7 space-y-3" x-data="{ openFaq: 1 }">
                        <div
                            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden transition-all shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                            <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                                class="w-full p-5 text-left font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] text-sm sm:text-base flex items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition-colors">
                                <span>Apakah Cooca benar-benar 100% gratis selamanya?</span>
                                <i data-lucide="chevron-down"
                                    class="w-4 h-4 text-[#6E6E73] dark:text-[#86868B] shrink-0 transition-transform duration-200"
                                    :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openFaq === 1" x-cloak x-transition
                                class="p-5 pt-0 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06]">
                                Ya! Seluruh fitur esensial seperti Kalkulator HPP, POS Kasir, mutasi inventori stok,
                                pembukuan kas otomatis, dan konsultasi AI dapat digunakan <strong
                                    class="text-[#34C759] dark:text-[#30D158]">100% gratis</strong> tanpa batasan jumlah
                                transaksi dan tanpa masa kadaluarsa.
                            </div>
                        </div>

                        <div
                            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden transition-all shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                            <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                                class="w-full p-5 text-left font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] text-sm sm:text-base flex items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition-colors">
                                <span>Apakah data keuangan dan pelanggan saya aman?</span>
                                <i data-lucide="chevron-down"
                                    class="w-4 h-4 text-[#6E6E73] dark:text-[#86868B] shrink-0 transition-transform duration-200"
                                    :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openFaq === 2" x-cloak x-transition
                                class="p-5 pt-0 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06]">
                                Sangat aman. Seluruh data transaksi dienkripsi dengan standar perbankan (TLS 1.3 / SSL
                                256-bit). Kami tidak pernah menjual data pelanggan, omzet, atau resep kuliner Anda ke pihak
                                mana pun.
                            </div>
                        </div>

                        <div
                            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden transition-all shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                            <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                                class="w-full p-5 text-left font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] text-sm sm:text-base flex items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition-colors">
                                <span>Apakah mendukung printer thermal Bluetooth dan scan barcode?</span>
                                <i data-lucide="chevron-down"
                                    class="w-4 h-4 text-[#6E6E73] dark:text-[#86868B] shrink-0 transition-transform duration-200"
                                    :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openFaq === 3" x-cloak x-transition
                                class="p-5 pt-0 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06]">
                                Ya. Sistem kasir kami kompatibel dengan printer kasir thermal Bluetooth ukuran 58mm dan 80mm
                                di Android, iOS, Windows, dan macOS, serta mendukung scan barcode langsung via kamera HP.
                            </div>
                        </div>

                        <div
                            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden transition-all shadow-[0_2px_8px_rgba(0,0,0,0.02)]">
                            <button type="button" @click="openFaq = openFaq === 4 ? null : 4"
                                class="w-full p-5 text-left font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] text-sm sm:text-base flex items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.02] transition-colors">
                                <span>Bagaimana jika koneksi internet di toko mendadak terputus?</span>
                                <i data-lucide="chevron-down"
                                    class="w-4 h-4 text-[#6E6E73] dark:text-[#86868B] shrink-0 transition-transform duration-200"
                                    :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openFaq === 4" x-cloak x-transition
                                class="p-5 pt-0 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06]">
                                Cooca dilengkapi teknologi local storage cache offline. Transaksi kasir tetap bisa dicetak
                                dan disimpan di browser, kemudian disinkronisasikan ke cloud saat internet kembali online.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Support Desk 24/7 (5 Col) -->
                    <div class="lg:col-span-5 glass-card p-6 sm:p-7 rounded-[28px] space-y-6">
                        <div>
                            <div
                                class="w-12 h-12 rounded-[18px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center mb-3">
                                <i data-lucide="headset" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Support Desk 24/7</h3>
                            <p class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1 leading-relaxed">
                                Butuh bantuan setup awal, cara hitung HPP resep produk Anda, atau konsultasi kendala
                                printer? Konsultan kami siap mendampingi Anda.
                            </p>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div
                                class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                                <div>
                                    <span
                                        class="text-[10px] font-bold uppercase text-[#34C759] dark:text-[#30D158] block">WhatsApp
                                        Resmi</span>
                                    <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-sm">0823 3749
                                        9577</span>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#34C759] text-white text-[10px] font-bold">
                                    &lt; 5 Menit
                                </span>
                            </div>

                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                                <div>
                                    <span
                                        class="text-[10px] font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Email
                                        Bantuan</span>
                                    <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">support@cooca.id</span>
                                </div>
                                <i data-lucide="mail" class="w-4 h-4 text-[#007AFF]"></i>
                            </div>
                        </div>

                        <a href="https://wa.me/6282337499577?text=Halo%20Tim%20Cooca%20UMKM,%20saya%20ingin%20konsultasi%20penggunaan%20aplikasi"
                            target="_blank" rel="noopener"
                            class="w-full py-3.5 rounded-[16px] bg-[#34C759] hover:bg-[#2DB84D] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Chat WhatsApp Konsultan Sekarang</span>
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ 7. ENTERPRISE BANNER (Apple Dark Slate Minimalist) ═══ -->
        <section class="py-16 md:py-24 border-t border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="rounded-[32px] bg-[#161618] border border-white/10 p-8 sm:p-14 text-center space-y-6 shadow-2xl text-white relative overflow-hidden">
                    <div class="relative z-10 space-y-4">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 text-white/90 text-xs font-semibold">
                            <i data-lucide="building" class="w-3.5 h-3.5 text-[#0A84FF]"></i>
                            <span>Kebutuhan Korporasi Multi-Cabang</span>
                        </div>

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                            Tingkatkan ke <span class="text-[#0A84FF]">COOCA.ID Enterprise</span>
                        </h2>

                        <p class="text-sm sm:text-base text-white/65 max-w-2xl mx-auto leading-relaxed font-normal">
                            Kelola jaringan puluhan cabang gerai, konsolidasi inventori gudang pusat, integrasi ERP klinik
                            apotek, dan laporan pajak holding terpusat.
                        </p>

                        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3.5 max-w-md mx-auto">
                            <a href="https://cooca.id" target="_blank" rel="noopener noreferrer"
                                class="w-full sm:w-auto px-8 py-3.5 rounded-[16px] bg-[#0A84FF] hover:bg-[#0077ED] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-lg active:scale-[0.98] transition-all">
                                <span>Kunjungi Portal Enterprise</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                            <a href="https://wa.me/6282337499577?text=Halo%20saya%20tertarik%20dengan%20COOCA%20Enterprise%20Multi-Cabang"
                                target="_blank" rel="noopener"
                                class="w-full sm:w-auto px-6 py-3.5 rounded-[16px] bg-white/10 hover:bg-white/15 border border-white/10 text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-all">
                                <i data-lucide="message-circle" class="w-4 h-4 text-[#30D158]"></i>
                                <span>Hubungi Sales Corporate</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
