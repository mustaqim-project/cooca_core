@extends('layouts.public_marketing')

@section('title', 'Kalkulator BEP (Break Even Point) Online Gratis | Cooca')
@section('description',
    'Kalkulator BEP (Titik Impas) online gratis untuk UMKM. Hitung berapa unit produk atau nominal
    rupiah omzet yang harus dicapai agar bisnis tidak merugi.')
@section('keywords',
    'kalkulator bep, hitung titik impas online, rumus break even point rupiah, bep unit warung,
    kalkulator bep umkm')

@section('content')
    <div class="pt-8 pb-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('kalkulator.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
                <span>/</span>
                <span class="text-[#34C759] dark:text-[#30D158] font-semibold">Kalkulator BEP</span>
            </nav>

        <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- KIRI: Headline & Penjelasan (7 Cols) -->
            <div class="lg:col-span-7 space-y-5 text-left">
                <div class="space-y-2">
                    <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">
                        Titik Impas Bebas Rugi
                    </p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Kalkulator BEP <span class="text-[#34C759] dark:text-[#30D158]">(Break Even Point)</span>
                    </h1>
                </div>

                <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-xl font-normal">
                    Ketahui batas minimal penjualan bulanan Anda. Penjualan di atas titik BEP adalah keuntungan murni bagi usaha, sedangkan di bawahnya adalah kerugian operasional.
                </p>

                <!-- Reassurance Points for UMKM 40-65 -->
                <div class="pt-1 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Hitung Target Unit &amp; Rupiah</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Pisahkan Biaya Tetap &amp; Variabel</span>
                    </div>
                </div>
            </div>

            <!-- KANAN: Visual Formula Preview Card (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                            <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                            <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                        </div>
                        <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">Logika Titik Impas</span>
                        <div class="w-6"></div>
                    </div>

                    <div class="space-y-2.5 text-xs text-[#48484A] dark:text-[#AEAEB2]">
                        <div class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                            <span class="font-medium">Biaya Tetap (Sewa, Gaji)</span>
                            <span class="font-mono font-bold text-[#FF3B30] dark:text-[#FF453A]">Fixed Cost</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                            <span class="font-medium">Margin Kontribusi per Porsi</span>
                            <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158]">Harga - Biaya Variabel</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                        <span class="text-[#6E6E73] dark:text-[#86868B]">Formula:</span>
                        <span class="font-bold text-[#34C759] dark:text-[#30D158]">Biaya Tetap / Margin Kontribusi</span>
                    </div>
                </div>
            </div>

        </section>

        <!-- ═══ CALCULATOR INTERACTIVE APP ═══ -->
        <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm p-6 sm:p-8 rounded-[24px]" x-data="{
                fixedCost: 4500000,
                pricePerUnit: 25000,
                varCostPerUnit: 13000,
            
                get contributionMargin() {
                    return Math.max(0, this.pricePerUnit - this.varCostPerUnit);
                },
                get cmRatio() {
                    if (this.pricePerUnit <= 0) return 0;
                    return this.contributionMargin / this.pricePerUnit;
                },
                get bepUnits() {
                    if (this.contributionMargin <= 0) return 0;
                    return Math.ceil(this.fixedCost / this.contributionMargin);
                },
                get bepRevenue() {
                    if (this.cmRatio <= 0) return 0;
                    return Math.round(this.fixedCost / this.cmRatio);
                },
                get bepDailyUnits() {
                    return Math.ceil(this.bepUnits / 30);
                },
                get bepDailyRevenue() {
                    return Math.round(this.bepRevenue / 30);
                }
            }">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Apple Inset Input Controls (7 Kolom) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- 1. Biaya Tetap Bulanan -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#34C759] dark:text-[#30D158] uppercase tracking-wide">1.
                                    Total Biaya Tetap (Fixed Cost) / Bulan</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="Number(fixedCost).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="number" x-model.number="fixedCost"
                                class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-[16px] sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                            <input type="range" x-model.number="fixedCost" min="500000" max="30000000" step="250000"
                                class="w-full accent-[#34C759] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Sewa tempat, gaji pokok staf,
                                internet, retribusi toko.</p>
                        </div>

                        <!-- 2. Harga Jual Rata-rata per Unit -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] uppercase tracking-wide">2.
                                    Rata-rata Harga Jual per Porsi/Unit</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="Number(pricePerUnit).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="number" x-model.number="pricePerUnit"
                                class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-[16px] sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                            <input type="range" x-model.number="pricePerUnit" min="1000" max="250000" step="1000"
                                class="w-full accent-[#007AFF] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Harga jual rata-rata produk atau menu
                                andalan Anda.</p>
                        </div>

                        <!-- 3. Biaya Variabel per Unit -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#FF9500] dark:text-[#FF9F0A] uppercase tracking-wide">3.
                                    Biaya Variabel (Bahan Baku) per Unit</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="Number(varCostPerUnit).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="number" x-model.number="varCostPerUnit"
                                class="w-full h-11 px-3.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[#1D1D1F] dark:text-[#F5F5F7] font-mono text-[16px] sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all">
                            <input type="range" x-model.number="varCostPerUnit" min="500" max="150000"
                                step="500" class="w-full accent-[#FF9500] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Modal bahan baku yang keluar hanya
                                saat barang dibuat/terjual.</p>
                        </div>
                    </div>

                    <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                    <div class="lg:col-span-5 sticky top-24 space-y-5">
                        <div
                            class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="space-y-4">
                                <div>
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B]">Target
                                        BEP Penjualan Bulanan</span>
                                    <div
                                        class="text-3xl sm:text-4xl font-black text-[#34C759] dark:text-[#30D158] font-mono mt-1">
                                        Rp <span x-text="bepRevenue.toLocaleString('id-ID')"></span>
                                    </div>
                                    <div class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">
                                        Setara dengan: <strong
                                            class="text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold"><span
                                                x-text="bepUnits.toLocaleString('id-ID')"></span> unit / porsi</strong> per
                                        bulan.
                                    </div>
                                </div>

                                <!-- Target Harian -->
                                <div class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20 space-y-2">
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">Target
                                        Minimal Harian (30 Hari)</span>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-[#6E6E73] dark:text-[#86868B]">Omzet Harian:</span>
                                        <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                                x-text="bepDailyRevenue.toLocaleString('id-ID')"></span></span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-[#6E6E73] dark:text-[#86868B]">Penjualan Harian:</span>
                                        <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158]"><span
                                                x-text="bepDailyUnits"></span> unit/hari</span>
                                    </div>
                                </div>

                                <!-- Margin Kontribusi -->
                                <div
                                    class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-1.5">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#6E6E73] dark:text-[#86868B]">Margin Kontribusi / Unit:</span>
                                        <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                                x-text="contributionMargin.toLocaleString('id-ID')"></span></span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#6E6E73] dark:text-[#86868B]">Rasio Margin Kontribusi:</span>
                                        <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF]"><span
                                                x-text="Math.round(cmRatio * 100)"></span>%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="pt-5 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2">
                                <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] text-center">
                                    Pantau posisi BEP Anda otomatis setiap hari di dashboard Cooca.
                                </p>
                                <a href="{{ route('register') }}"
                                    class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Mulai Sekarang - 100% Gratis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
