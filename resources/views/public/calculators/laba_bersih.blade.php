@extends('layouts.public_marketing')

@section('title', 'Kalkulator Laba Bersih (Net Profit) Usaha Online Gratis | Cooca')
@section('description',
    'Kalkulator simulasi laba bersih (net profit) UMKM online. Hitung pendapatan kotor, HPP barang
    terjual, biaya operasional, gaji, sewa, listrik, dan pajak untuk mengetahui laba bersih riil.')
@section('keywords',
    'kalkulator laba bersih, rumus net profit margin umkm, hitung keuntungan usaha bulanan, laporan
    laba rugi sederhana, simulasi laba kotor bersih')

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
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Kalkulator Laba Bersih</span>
            </nav>

            <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- KIRI: Headline & Penjelasan (7 Cols) -->
                <div class="lg:col-span-7 space-y-5 text-left">
                    <div class="space-y-2">
                        <p
                            class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            Simulasi Profitabilitas Riil
                        </p>
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15] text-balance break-words">
                            Kalkulator Laba Bersih &amp; <span class="text-[#007AFF] dark:text-[#0A84FF]">Sisa Kas
                                Usaha</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-xl font-normal text-pretty break-words">
                        Omzet besar belum tentu untung besar. Masukkan pendapatan kotor dan seluruh pos pengeluaran
                        operasional Anda untuk melihat berapa rupiah uang yang benar-benar bisa dibawa pulang.
                    </p>

                    <!-- Reassurance Points for UMKM 40-65 -->
                    <div
                        class="pt-1 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Hitung Laba Kotor (Gross Profit)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Potong Beban Operasional Lengkap</span>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Visual Formula Preview Card (5 Cols) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-5 sm:p-6 space-y-4">
                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                            </div>
                            <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">Alur Laba Riil</span>
                            <div class="w-6"></div>
                        </div>

                        <div class="space-y-2.5 text-xs text-[#48484A] dark:text-[#AEAEB2]">
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Total Omzet Penjualan</span>
                                <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF]">Pendapatan</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Modal Pokok (HPP)</span>
                                <span class="font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]">- Biaya Bahan</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Gaji, Sewa, Listrik, Pajak</span>
                                <span class="font-mono font-bold text-[#FF3B30] dark:text-[#FF453A]">- Beban Usaha</span>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                            <span class="text-[#6E6E73] dark:text-[#86868B]">Hasil Akhir:</span>
                            <span class="font-bold text-[#34C759] dark:text-[#30D158]">= Uang Masuk Bersih</span>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ═══ CALCULATOR INTERACTIVE APP ═══ -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm p-6 sm:p-8 rounded-[24px]"
                x-data="{
                    revenue: 35000000,
                    cogsRate: 55,
                    staffSalaries: 4000000,
                    rentExpense: 2000000,
                    utilityExpense: 1200000,
                    taxRate: 0.5,
                
                    get totalCogs() {
                        return Math.round(this.revenue * (this.cogsRate / 100));
                    },
                    get grossProfit() {
                        return this.revenue - this.totalCogs;
                    },
                    get totalOpex() {
                        return (parseFloat(this.staffSalaries) || 0) + (parseFloat(this.rentExpense) || 0) + (parseFloat(this.utilityExpense) || 0);
                    },
                    get operatingProfit() {
                        return this.grossProfit - this.totalOpex;
                    },
                    get taxAmount() {
                        return Math.round(this.revenue * (this.taxRate / 100));
                    },
                    get netProfit() {
                        return this.operatingProfit - this.taxAmount;
                    },
                    get netMargin() {
                        if (this.revenue <= 0) return 0;
                        return Math.round((this.netProfit / this.revenue) * 100);
                    }
                }">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Apple Inset Inputs (7 Kolom) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- 1. Omzet -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">1.
                                    Total Omzet (Pendapatan Bulanan)</label>
                                <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span
                                        x-text="Number(revenue).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="revenue" min="5000000" max="250000000" step="1000000"
                                class="w-full accent-[#007AFF] cursor-pointer">
                        </div>

                        <!-- 2. HPP Rate -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">2.
                                    Estimasi HPP / Modal Bahan Baku</label>
                                <span class="font-mono text-sm font-bold text-[#FF9500] dark:text-[#FF9F0A]"><span
                                        x-text="cogsRate"></span>% (Rp <span
                                        x-text="totalCogs.toLocaleString('id-ID')"></span>)</span>
                            </div>
                            <input type="range" x-model.number="cogsRate" min="10" max="85" step="1"
                                class="w-full accent-[#FF9500] cursor-pointer">
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Standar F&amp;B berkisar 30-40%,
                                Retail kelontong 60-80%.</p>
                        </div>

                        <!-- 3. Biaya Operasional (Opex) -->
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-3">
                            <span
                                class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide block">3.
                                Biaya Operasional (Opex)</span>

                            <div>
                                <div class="flex justify-between text-xs mb-1 text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Total Gaji Staf:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold">Rp <span
                                            x-text="Number(staffSalaries).toLocaleString('id-ID')"></span></span>
                                </div>
                                <input type="range" x-model.number="staffSalaries" min="0" max="30000000"
                                    step="500000" class="w-full accent-[#007AFF] cursor-pointer">
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1 text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Sewa Tempat:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold">Rp <span
                                            x-text="Number(rentExpense).toLocaleString('id-ID')"></span></span>
                                </div>
                                <input type="range" x-model.number="rentExpense" min="0" max="15000000"
                                    step="250000" class="w-full accent-[#007AFF] cursor-pointer">
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1 text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Listrik, Air, Wifi &amp; Beban Lain:</span>
                                    <span class="font-mono text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold">Rp <span
                                            x-text="Number(utilityExpense).toLocaleString('id-ID')"></span></span>
                                </div>
                                <input type="range" x-model.number="utilityExpense" min="100000" max="10000000"
                                    step="100000" class="w-full accent-[#007AFF] cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                    <div class="lg:col-span-5 sticky top-24 space-y-5">
                        <div
                            class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Laporan
                                Laba Rugi Mini</span>

                            <div class="space-y-2 text-xs border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                                <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                    <span>Omzet Penjualan:</span>
                                    <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                            x-text="Number(revenue).toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A]">
                                    <span>- HPP (<span x-text="cogsRate"></span>%):</span>
                                    <span class="font-mono">Rp <span
                                            x-text="totalCogs.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div
                                    class="flex justify-between font-bold pt-1 border-t border-black/[0.04] dark:border-white/[0.06] text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span>= Laba Kotor (Gross Profit):</span>
                                    <span class="font-mono text-[#34C759] dark:text-[#30D158]">Rp <span
                                            x-text="grossProfit.toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>

                            <div class="space-y-2 text-xs border-b border-black/[0.04] dark:border-white/[0.06] pb-3">
                                <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A]">
                                    <span>- Biaya Operasional (Opex):</span>
                                    <span class="font-mono">Rp <span
                                            x-text="totalOpex.toLocaleString('id-ID')"></span></span>
                                </div>
                                <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A]">
                                    <span>- Pajak PPh Final 0.5%:</span>
                                    <span class="font-mono">Rp <span
                                            x-text="taxAmount.toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>

                            <!-- Laba Bersih Final -->
                            <div class="p-4 rounded-[18px] border transition-all"
                                :class="netProfit >= 0 ? 'bg-[#34C759]/10 border-[#34C759]/20' :
                                    'bg-[#FF3B30]/10 border-[#FF3B30]/20'">
                                <span class="text-xs font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Laba
                                    Bersih Akhir (Net Profit)</span>
                                <div class="text-2xl sm:text-3xl font-black font-mono mt-1"
                                    :class="netProfit >= 0 ? 'text-[#34C759] dark:text-[#30D158]' :
                                        'text-[#FF3B30] dark:text-[#FF453A]'">
                                    Rp <span x-text="netProfit.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">
                                    Net Profit Margin: <strong class="text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold"
                                        x-text="netMargin + '%'"></strong>
                                </div>
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('register') }}"
                                    class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Otomatiskan Pembukuan Usaha</span>
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
