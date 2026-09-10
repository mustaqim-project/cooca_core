@extends('layouts.app', [
    'title' => 'BEP & Profitabilitas',
    'headerTitle' => 'Break-Even Point (BEP) & Analisis Margin',
    'headerSubtitle' => 'Hitung titik impas unit & pendapatan, target profit goal, serta Margin of Safety (§24 Blueprint)'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    fixedCost: {{ $totalFixedOverhead ?: 10000000 }},
    sellingPrice: 50000,
    variableCost: 25000,
    expectedUnits: 500,
    targetProfit: 5000000,

    get unitCm() {
        return Math.max(0, this.sellingPrice - this.variableCost);
    },

    get cmRatio() {
        if (this.sellingPrice <= 0) return 0;
        return ((this.unitCm / this.sellingPrice) * 100).toFixed(1);
    },

    get bepUnits() {
        if (this.unitCm <= 0) return 0;
        return Math.ceil(this.fixedCost / this.unitCm);
    },

    get bepRevenue() {
        if (this.cmRatio <= 0) return 0;
        return Math.round(this.fixedCost / (this.cmRatio / 100));
    },

    get targetProfitUnits() {
        if (this.unitCm <= 0) return 0;
        return Math.ceil((this.fixedCost + parseFloat(this.targetProfit || 0)) / this.unitCm);
    },

    get targetProfitRevenue() {
        if (this.cmRatio <= 0) return 0;
        return Math.round((this.fixedCost + parseFloat(this.targetProfit || 0)) / (this.cmRatio / 100));
    },

    get safetyMarginUnits() {
        return Math.max(0, this.expectedUnits - this.bepUnits);
    },

    get safetyMarginRevenue() {
        return this.safetyMarginUnits * this.sellingPrice;
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Keuangan &amp; Biaya</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">BEP &amp; Margin</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Kalkulator BEP &amp; Profitabilitas</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Simulasi titik impas operasional, kontribusi margin, dan target laba bersih</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
                <span>Simulasi Real-Time</span>
            </span>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. PARAMETERS & OUTPUT RESULTS GRID                   -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        
        <!-- Input Configuration Card (5 Cols) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 lg:col-span-5 space-y-4 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                <div class="w-6 h-6 rounded-[6px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                    </svg>
                </div>
                <h2 class="text-[14px] font-semibold text-black dark:text-white">Parameter Biaya &amp; Penjualan</h2>
            </div>

            <!-- Fixed Cost -->
            <div>
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                    Total Biaya Tetap Bulanan (Fixed Cost)
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[13px] font-medium text-black/40 dark:text-white/40 pointer-events-none">
                        {{ $business->currency_symbol }}
                    </span>
                    <input type="number" x-model.number="fixedCost" min="0" step="50000"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-10 pr-3.5 text-[14px] font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Sewa outlet, gaji tetap manajerial, langganan utilitas &amp; software.</p>
            </div>

            <!-- Selling Price & Variable Cost -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Harga Jual / Unit</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] font-medium text-black/40 dark:text-white/40 pointer-events-none">
                            {{ $business->currency_symbol }}
                        </span>
                        <input type="number" x-model.number="sellingPrice" min="1" step="1000"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-8 pr-3 text-[14px] font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Biaya Variabel / Unit</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] font-medium text-black/40 dark:text-white/40 pointer-events-none">
                            {{ $business->currency_symbol }}
                        </span>
                        <input type="number" x-model.number="variableCost" min="0" step="1000"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-8 pr-3 text-[14px] font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
            </div>

            <!-- Target Penjualan & Target Laba -->
            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-black/5 dark:border-white/10">
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Target Penjualan (Unit)</label>
                    <input type="number" x-model.number="expectedUnits" min="1"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Target Laba Bersih</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] font-medium text-black/40 dark:text-white/40 pointer-events-none">
                            {{ $business->currency_symbol }}
                        </span>
                        <input type="number" x-model.number="targetProfit" min="0" step="500000"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-8 pr-3 text-[14px] font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- BEP Analysis Output Results (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- Core BEP Cards (2 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Unit BEP Card -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-[#AF52DE] dark:text-[#BF5AF2]">
                        Titik Impas Kuantitas (BEP Unit)
                    </span>
                    <div class="my-2">
                        <div class="text-[32px] font-bold text-black dark:text-white tabular-nums leading-none">
                            <span x-text="bepUnits.toLocaleString('id-ID')"></span>
                            <span class="text-[14px] font-normal text-black/50 dark:text-white/50">Unit</span>
                        </div>
                    </div>
                    <span class="text-[12px] text-black/50 dark:text-white/50">Volume minimal produksi agar tidak merugi</span>
                </div>

                <!-- Revenue BEP Card -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-[#34C759] dark:text-[#30D158]">
                        Titik Impas Omset (BEP Nominal)
                    </span>
                    <div class="my-2">
                        <div class="text-[32px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums leading-none">
                            {{ $business->currency_symbol }} <span x-text="bepRevenue.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                    <span class="text-[12px] text-black/50 dark:text-white/50">Omzet penjualan minimal penutup modal</span>
                </div>
            </div>

            <!-- Contribution Margin & Margin of Safety Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- Contribution Margin Card -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-1.5 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 block">
                        Unit Contribution Margin
                    </span>
                    <div class="text-[20px] font-bold text-black dark:text-white tabular-nums">
                        {{ $business->currency_symbol }} <span x-text="unitCm.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="pt-0.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                            <span x-text="'CM Ratio: ' + cmRatio + '%'"></span>
                        </span>
                    </div>
                </div>

                <!-- Margin of Safety -->
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-1.5 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-black/45 dark:text-white/45 block">
                        Margin of Safety (Batas Aman)
                    </span>
                    <div class="text-[20px] font-bold text-[#007AFF] tabular-nums">
                        <span x-text="safetyMarginUnits.toLocaleString('id-ID')"></span> Unit
                    </div>
                    <div class="text-[12px] text-black/60 dark:text-white/60 tabular-nums">
                        {{ $business->currency_symbol }} <span x-text="safetyMarginRevenue.toLocaleString('id-ID')"></span> di atas titik impas
                    </div>
                </div>
            </div>

            <!-- Target Profit Goal-Seeker Card -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between">
                    <div class="font-semibold text-black dark:text-white text-[14px] flex items-center gap-2">
                        <div class="w-6 h-6 rounded-[6px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            </svg>
                        </div>
                        <span>Kebutuhan untuk Mencapai Laba Target</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[12px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] tabular-nums">
                        Target: {{ $business->currency_symbol }} <span x-text="targetProfit.toLocaleString('id-ID')"></span>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-black/5 dark:border-white/10">
                    <div>
                        <span class="text-[11px] font-medium text-black/50 dark:text-white/50 block">Wajib Menjual Sebanyak:</span>
                        <div class="text-[20px] font-bold text-black dark:text-white tabular-nums mt-0.5">
                            <span x-text="targetProfitUnits.toLocaleString('id-ID')"></span> Unit
                        </div>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-black/50 dark:text-white/50 block">Target Omzet Minimum:</span>
                        <div class="text-[20px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums mt-0.5">
                            {{ $business->currency_symbol }} <span x-text="targetProfitRevenue.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
