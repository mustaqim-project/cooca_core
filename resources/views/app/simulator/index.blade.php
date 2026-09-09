@extends('layouts.app', [
    'title' => 'What-If & Sensitivity Simulator',
    'headerTitle' => 'What-If & Sensitivity Simulator',
    'headerSubtitle' => 'Simulasikan dampak kenaikan harga bahan, UMR upah, dan overhead terhadap HPP dan laba tanpa merusak data master'
])

@section('content')
<div class="space-y-6" x-data="{
    costModelId: '{{ $selectedCostModel?->id ?? '' }}',
    matChange: 10,
    labChange: 5,
    macChange: 0,
    ovhChange: 0,
    markupPct: 40,

    // Result
    simResult: null,
    loading: false,
    chart: null,

    init() {
        if (this.costModelId) {
            this.runSimulation();
        }

        // Re-render chart on theme switch if chart exists
        window.addEventListener('theme-changed', () => {
            if (this.chart) {
                this.updateChart();
            }
        });
    },

    applyPreset(mat, lab, mac, ovh, markup) {
        this.matChange = mat;
        this.labChange = lab;
        this.macChange = mac;
        this.ovhChange = ovh;
        if (markup !== undefined && markup !== null) {
            this.markupPct = markup;
        }
        this.runSimulation();
    },

    runSimulation() {
        if (!this.costModelId) return;
        this.loading = true;

        fetch(`/simulator/${this.costModelId}/run`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
            },
            body: JSON.stringify({
                material_change_pct: this.matChange,
                labor_change_pct: this.labChange,
                machine_change_pct: this.macChange,
                overhead_change_pct: this.ovhChange,
                target_markup_pct: this.markupPct
            })
        })
        .then(res => res.json())
        .then(data => {
            this.simResult = data.simulation;
            this.loading = false;
            this.$nextTick(() => {
                this.updateChart();
            });
        })
        .catch(err => {
            console.error('Simulation error:', err);
            this.loading = false;
        });
    },

    updateChart() {
        if (!this.simResult) return;
        const ctx = document.getElementById('simChart');
        if (!ctx) return;

        const base = this.simResult.baseline;
        const sim = this.simResult.simulated;

        if (this.chart) {
            this.chart.destroy();
        }

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#94a3b8' : '#475569';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.06)';
        const baselineBg = isDark ? 'rgba(100, 116, 139, 0.5)' : 'rgba(148, 163, 184, 0.6)';
        const baselineBorder = isDark ? 'rgba(148, 163, 184, 0.8)' : 'rgba(100, 116, 139, 0.9)';
        const simBg = 'rgba(16, 185, 129, 0.7)';
        const simBorder = 'rgba(16, 185, 129, 1)';

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Bahan Baku', 'Tenaga Kerja', 'Mesin/Listrik', 'Overhead', 'Total HPP'],
                datasets: [
                    {
                        label: 'Baseline (Biaya Awal)',
                        data: [base.material_cost, base.labor_cost, base.machine_cost, base.overhead_cost, base.total_hpp],
                        backgroundColor: baselineBg,
                        borderColor: baselineBorder,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6
                    },
                    {
                        label: 'Hasil Simulasi (Skenario)',
                        data: [sim.material_cost, sim.labor_cost, sim.machine_cost, sim.overhead_cost, sim.total_hpp],
                        backgroundColor: simBg,
                        borderColor: simBorder,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            boxWidth: 12,
                            boxHeight: 12,
                            borderRadius: 3,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#ffffff' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#334155',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + Math.round(context.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 10 } },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    y: {
                        ticks: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans', size: 10 },
                            callback: function(val) {
                                return 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1) + 'M' : (val >= 1000 ? (val/1000).toFixed(0) + 'k' : val));
                            }
                        },
                        grid: { color: gridColor, drawBorder: false }
                    }
                }
            }
        });
    }
}">

    {{-- ===== SUB-NAVIGATION TABS ===== --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80 dark:border-slate-800/80 scrollbar-none">
        <a href="{{ route('calculator.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="calculator" class="w-4 h-4 text-slate-400"></i>
            <span>Kalkulator HPP Live</span>
        </a>
        <a href="{{ route('simulator.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-xs">
            <i data-lucide="sliders" class="w-4 h-4 text-emerald-500"></i>
            <span>What-If Simulator</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">Sandbox</span>
        </a>
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
            <span>Katalog Produk & BOM</span>
        </a>
        <a href="{{ route('materials.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
            <i data-lucide="boxes" class="w-4 h-4 text-slate-400"></i>
            <span>Katalog Bahan Baku</span>
        </a>
    </div>

    {{-- ===== MODEL SELECTOR TOPBAR ===== --}}
    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start md:items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        Pilih Produk & Model Biaya
                    </h2>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                        Sandbox Aman
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Simulasi terisolasi: Anda bebas bereksperimen tanpa memodifikasi data harga beli maupun histori resmi.
                </p>
            </div>
        </div>

        @if($products->isNotEmpty())
        <form method="GET" action="{{ route('simulator.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative w-full md:w-72">
                <select name="cost_model_id" onchange="this.form.submit()"
                        class="w-full pl-3.5 pr-8 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition cursor-pointer">
                    @foreach($products as $p)
                        @foreach($p->costModels as $cm)
                            <option value="{{ $cm->id }}" {{ ($selectedCostModel?->id === $cm->id) ? 'selected' : '' }}>
                                {{ $p->name }} — {{ $cm->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
        </form>
        @endif
    </div>

    @if(!$selectedCostModel)
    {{-- ===== EMPTY STATE: NO COST MODEL ===== --}}
    <div class="p-12 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs text-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4 text-slate-400">
            <i data-lucide="layers" class="w-8 h-8"></i>
        </div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Model Biaya (BOM) Aktif</h3>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 mb-5 max-w-md mx-auto">
            Untuk menjalankan simulasi What-If, buat minimal satu produk dengan susunan resep (BOM) atau model biaya di kalkulator.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('calculator.index') }}"
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm hover:shadow transition inline-flex items-center gap-2">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>Buka Kalkulator HPP</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition inline-flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Kelola Produk</span>
            </a>
        </div>
    </div>
    @else

    {{-- ===== 1-CLICK PRESET CHIPS ===== --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-2.5">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                    Skenario Cepat (1-Klik Presets)
                </span>
            </div>
            <span class="text-[11px] text-slate-400">Klik untuk menyimulasikan guncangan pasar riil secara instan</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="applyPreset(15, 0, 0, 0, null)"
                    class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-amber-50 dark:hover:bg-amber-500/10 text-slate-700 dark:text-slate-300 hover:text-amber-700 dark:hover:text-amber-300 border border-slate-200 dark:border-slate-800 hover:border-amber-300 dark:hover:border-amber-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <span>🌾 Inflasi Bahan Baku (+15%)</span>
            </button>
            <button type="button" @click="applyPreset(0, 10, 0, 0, null)"
                    class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-cyan-50 dark:hover:bg-cyan-500/10 text-slate-700 dark:text-slate-300 hover:text-cyan-700 dark:hover:text-cyan-300 border border-slate-200 dark:border-slate-800 hover:border-cyan-300 dark:hover:border-cyan-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <span>👥 Kenaikan UMR Upah (+10%)</span>
            </button>
            <button type="button" @click="applyPreset(0, 0, 20, 0, null)"
                    class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-blue-50 dark:hover:bg-blue-500/10 text-slate-700 dark:text-slate-300 hover:text-blue-700 dark:hover:text-blue-300 border border-slate-200 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <span>⚡ Lonjakan Listrik & Mesin (+20%)</span>
            </button>
            <button type="button" @click="applyPreset(0, 0, 0, 15, null)"
                    class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-purple-50 dark:hover:bg-purple-500/10 text-slate-700 dark:text-slate-300 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <span>📦 Kenaikan Overhead Sewa (+15%)</span>
            </button>
            <button type="button" @click="applyPreset(20, 10, 15, 10, null)"
                    class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <span>🔥 Krisis Pasokan (+20% Bahan, +10% UMR, +15% Mesin)</span>
            </button>
            <button type="button" @click="applyPreset(0, 0, 0, 0, 40)"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5 ml-auto">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset ke 0%</span>
            </button>
        </div>
    </div>

    {{-- ===== MAIN SIMULATOR DUAL-COLUMN GRID ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: PARAMETER SLIDERS (4 COLS) --}}
        <div class="lg:col-span-4 p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-emerald-500"></i>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Faktor Perubahan Biaya
                    </h3>
                </div>
                <div class="flex items-center gap-1" x-show="loading">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-[10px] font-mono text-emerald-500 font-bold">Menghitung...</span>
                </div>
            </div>

            {{-- Slider 1: Bahan Baku --}}
            <div class="space-y-1.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i data-lucide="boxes" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Harga Bahan Baku:</span>
                    </span>
                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-md"
                          :class="matChange > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30' : (matChange < 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')"
                          x-text="(matChange >= 0 ? '+' : '') + matChange + '%'"></span>
                </div>
                <input type="range" x-model.number="matChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 2: Upah Tenaga Kerja --}}
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-cyan-500"></i>
                        <span>Upah Tenaga Kerja:</span>
                    </span>
                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-md"
                          :class="labChange > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30' : (labChange < 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')"
                          x-text="(labChange >= 0 ? '+' : '') + labChange + '%'"></span>
                </div>
                <input type="range" x-model.number="labChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 3: Tarif Mesin & Listrik --}}
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i data-lucide="cpu" class="w-3.5 h-3.5 text-blue-500"></i>
                        <span>Tarif Mesin & Listrik:</span>
                    </span>
                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-md"
                          :class="macChange > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30' : (macChange < 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')"
                          x-text="(macChange >= 0 ? '+' : '') + macChange + '%'"></span>
                </div>
                <input type="range" x-model.number="macChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 4: Biaya Overhead --}}
            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-purple-500"></i>
                        <span>Overhead Pabrik / Toko:</span>
                    </span>
                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-md"
                          :class="ovhChange > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30' : (ovhChange < 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')"
                          x-text="(ovhChange >= 0 ? '+' : '') + ovhChange + '%'"></span>
                </div>
                <input type="range" x-model.number="ovhChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 5: Target Markup Jual --}}
            <div class="space-y-1.5 pt-3 border-t border-slate-200 dark:border-slate-700/80">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Target Markup Jual:</span>
                    </span>
                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30"
                          x-text="markupPct + '%'"></span>
                </div>
                <input type="range" x-model.number="markupPct" @input="runSimulation()" min="10" max="150" step="5"
                       class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>10% (Tipis)</span>
                    <span>40% (Standar)</span>
                    <span>150% (Premium)</span>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: COMPARISON RESULTS & SENSITIVITY (8 COLS) --}}
        <div class="lg:col-span-8 space-y-6">

            <template x-if="simResult">
                <div class="space-y-6">

                    {{-- 3 Bento Comparison Summary Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">

                        {{-- Baseline Card --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">
                                HPP Semula (Baseline)
                            </span>
                            <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                                {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.total_hpp).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                                <span>Harga Jual:</span>
                                <span class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.selling_price).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        {{-- Simulated Card --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 shadow-2xs">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                    HPP Hasil Simulasi
                                </span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight">
                                {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.total_hpp).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-emerald-500/20 text-[11px] text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                                <span>Rekomendasi Jual:</span>
                                <span class="font-mono font-bold">
                                    {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.selling_price).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        {{-- Delta Impact Card --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">
                                Dampak Selisih Biaya
                            </span>
                            <div class="text-xl sm:text-2xl font-black font-mono tracking-tight"
                                 :class="simResult.impact.delta_hpp_amount > 0 ? 'text-rose-600 dark:text-rose-400' : (simResult.impact.delta_hpp_amount < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300')">
                                <span x-text="(simResult.impact.delta_hpp_amount > 0 ? '+' : '') + '{{ $business->currency_symbol }} ' + Math.round(simResult.impact.delta_hpp_amount).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] font-bold flex items-center justify-between"
                                 :class="simResult.impact.delta_hpp_percentage > 0 ? 'text-rose-600 dark:text-rose-400' : (simResult.impact.delta_hpp_percentage < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500')">
                                <span>Pergeseran HPP:</span>
                                <span class="font-mono">
                                    <span x-text="(simResult.impact.delta_hpp_percentage > 0 ? '+' : '') + simResult.impact.delta_hpp_percentage.toFixed(1) + '%'"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Breakdown Table (Baseline vs Simulasi) --}}
                    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <i data-lucide="table" class="w-4 h-4 text-emerald-500"></i>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Tabel Rincian Komponen Biaya
                                </h3>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">Perbandingan nominal riil antar elemen biaya</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300 min-w-[540px]">
                                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                                    <tr>
                                        <th class="py-3 px-4">Komponen Biaya</th>
                                        <th class="py-3 px-4 text-right">Semula (Baseline)</th>
                                        <th class="py-3 px-4 text-right">Hasil Simulasi</th>
                                        <th class="py-3 px-4 text-right">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    {{-- Row 1: Material --}}
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                        <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>Bahan Baku (Raw Material)</span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.material_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.material_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold"
                                            :class="matChange > 0 ? 'text-rose-600 dark:text-rose-400' : (matChange < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400')">
                                            <span x-text="(matChange >= 0 ? '+' : '') + matChange + '%'"></span>
                                        </td>
                                    </tr>
                                    {{-- Row 2: Labor --}}
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                        <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                            <span>Tenaga Kerja Langsung</span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.labor_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.labor_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold"
                                            :class="labChange > 0 ? 'text-rose-600 dark:text-rose-400' : (labChange < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400')">
                                            <span x-text="(labChange >= 0 ? '+' : '') + labChange + '%'"></span>
                                        </td>
                                    </tr>
                                    {{-- Row 3: Machine --}}
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                        <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            <span>Tarif Mesin & Listrik</span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.machine_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.machine_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold"
                                            :class="macChange > 0 ? 'text-rose-600 dark:text-rose-400' : (macChange < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400')">
                                            <span x-text="(macChange >= 0 ? '+' : '') + macChange + '%'"></span>
                                        </td>
                                    </tr>
                                    {{-- Row 4: Overhead --}}
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                        <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            <span>Overhead Pabrik (BOP)</span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.overhead_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.overhead_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold"
                                            :class="ovhChange > 0 ? 'text-rose-600 dark:text-rose-400' : (ovhChange < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400')">
                                            <span x-text="(ovhChange >= 0 ? '+' : '') + ovhChange + '%'"></span>
                                        </td>
                                    </tr>
                                    {{-- Total Row --}}
                                    <tr class="bg-slate-50/90 dark:bg-slate-950/70 font-bold border-t-2 border-slate-200 dark:border-slate-700">
                                        <td class="py-3.5 px-4 text-slate-900 dark:text-white">
                                            Total HPP per Satuan
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.total_hpp).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.total_hpp).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono font-black"
                                            :class="simResult.impact.delta_hpp_percentage > 0 ? 'text-rose-600 dark:text-rose-400' : (simResult.impact.delta_hpp_percentage < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400')">
                                            <span x-text="(simResult.impact.delta_hpp_percentage >= 0 ? '+' : '') + simResult.impact.delta_hpp_percentage.toFixed(1) + '%'"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Visual Comparison Bar Chart --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-500"></i>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Grafik Komparasi Biaya (Baseline vs Simulasi)
                                </h3>
                            </div>
                            <span class="text-[11px] text-slate-400">Visualisasi selisih tiap elemen biaya</span>
                        </div>
                        <div class="h-64 sm:h-72 relative">
                            <canvas id="simChart"></canvas>
                        </div>
                    </div>

                    {{-- Executive AI Insight / Recommendation --}}
                    <div class="p-5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/50 flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center shrink-0 text-indigo-600 dark:text-indigo-400">
                            <i data-lucide="lightbulb" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-1 text-xs">
                            <h4 class="font-bold text-indigo-900 dark:text-indigo-200">
                                Rekomendasi Penyesuaian Harga Bisnis
                            </h4>
                            <p class="text-indigo-800/90 dark:text-indigo-300 leading-relaxed">
                                Jika biaya skenario ini terjadi secara riil, Anda disarankan menaikkan harga jual produk minimal menjadi
                                <strong class="font-mono font-black underline">{{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.selling_price).toLocaleString('id-ID')"></span></strong>
                                (kenaikan <span class="font-mono font-bold">{{ $business->currency_symbol }} <span x-text="Math.round(simResult.impact.delta_selling_price).toLocaleString('id-ID')"></span></span>)
                                guna mempertahankan margin keuntungan kotor <span class="font-mono font-bold" x-text="markupPct + '%'"></span>.
                            </p>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>
    @endif

</div>
@endsection
