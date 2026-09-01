@extends('layouts.app', [
    'title' => 'BEP & Profitabilitas',
    'headerTitle' => 'Break-Even Point (BEP) & Analisis Margin',
    'headerSubtitle' => 'Hitung titik impas unit & pendapatan, target profit goal, serta Margin of Safety (§24 Blueprint)'
])

@section('content')
<div class="space-y-6" x-data="{
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

    <!-- Input Configuration & Metric Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Inputs Card (5 Cols) -->
        <div class="glass-card p-6 rounded-2xl lg:col-span-5 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-purple-400"></i>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Parameter Biaya & Penjualan</h3>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Total Biaya Tetap / Bulan (Fixed Cost)</label>
                <input type="number" x-model.number="fixedCost" min="0" step="50000"
                       class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white">
                <p class="text-[10px] text-slate-400 mt-1">Contoh: Sewa outlet, gaji tetap manajerial, langganan software.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual / Unit</label>
                    <input type="number" x-model.number="sellingPrice" min="1" step="1000"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Biaya Variabel / Unit</label>
                    <input type="number" x-model.number="variableCost" min="0" step="1000"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Target Penjualan (Unit)</label>
                    <input type="number" x-model.number="expectedUnits" min="1"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Target Laba Bersih (Rp)</label>
                    <input type="number" x-model.number="targetProfit" min="0" step="500000"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-white">
                </div>
            </div>
        </div>

        <!-- BEP Analysis Output Results (7 Cols) -->
        <div class="lg:col-span-7 space-y-5">
            
            <!-- Core BEP Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Unit BEP Card -->
                <div class="glass-card p-5 rounded-2xl border-purple-500/30 bg-slate-900/90">
                    <div class="text-[11px] font-semibold text-purple-400 uppercase">Titik Impas Kuantitas (BEP Unit)</div>
                    <div class="text-3xl font-extrabold text-white font-mono mt-1">
                        <span x-text="bepUnits.toLocaleString('id-ID')"></span> <span class="text-xs font-sans text-slate-400 font-normal">Unit</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">Jumlah unit minimal agar tidak rugi</div>
                </div>

                <!-- Revenue BEP Card -->
                <div class="glass-card p-5 rounded-2xl border-emerald-500/30 bg-slate-900/90">
                    <div class="text-[11px] font-semibold text-emerald-400 uppercase">Titik Impas Omset (BEP Rupiah)</div>
                    <div class="text-3xl font-extrabold text-white font-mono mt-1">
                        {{ $business->currency_symbol }} <span x-text="bepRevenue.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">Omzet penjualan minimal impas</div>
                </div>
            </div>

            <!-- Contribution Margin & Margin of Safety Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Contribution Margin Card -->
                <div class="glass-card p-4 rounded-xl space-y-1">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase">Unit Contribution Margin</div>
                    <div class="text-xl font-bold text-teal-300 font-mono">
                        {{ $business->currency_symbol }} <span x-text="unitCm.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-bold" x-text="'CM Ratio: ' + cmRatio + '%'"></div>
                </div>

                <!-- Margin of Safety -->
                <div class="glass-card p-4 rounded-xl space-y-1">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase">Margin of Safety (Batas Aman)</div>
                    <div class="text-xl font-bold text-blue-400 font-mono">
                        <span x-text="safetyMarginUnits.toLocaleString('id-ID')"></span> Unit
                    </div>
                    <div class="text-[10px] text-blue-300 font-mono">
                        {{ $business->currency_symbol }} <span x-text="safetyMarginRevenue.toLocaleString('id-ID')"></span> di atas titik impas
                    </div>
                </div>
            </div>

            <!-- Target Profit Goal-Seeker Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-purple-950/40 to-slate-900 border border-purple-500/30 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="font-bold text-white text-sm flex items-center gap-2">
                        <i data-lucide="target" class="w-4 h-4 text-purple-400"></i>
                        <span>Penjualan Diperlukan untuk Laba Target</span>
                    </div>
                    <div class="text-xs font-mono font-bold text-purple-400">
                        Target: {{ $business->currency_symbol }} <span x-text="targetProfit.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-800">
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase">Wajib Menjual Sebanyak:</div>
                        <div class="text-lg font-extrabold text-white font-mono">
                            <span x-text="targetProfitUnits.toLocaleString('id-ID')"></span> Unit
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase">Target Omzet Minimum:</div>
                        <div class="text-lg font-extrabold text-emerald-400 font-mono">
                            {{ $business->currency_symbol }} <span x-text="targetProfitRevenue.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
