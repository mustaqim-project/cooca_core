@extends('layouts.app', [
    'title' => 'What-If Simulator',
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
    },

    runSimulation() {
        if (!this.costModelId) return;
        this.loading = true;

        fetch(`/simulator/${this.costModelId}/run`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
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
            this.updateChart();
        })
        .catch(err => {
            console.error(err);
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

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Material', 'Labor', 'Mesin', 'Overhead', 'Total HPP'],
                datasets: [
                    {
                        label: 'Baseline (Awal)',
                        data: [base.material_cost, base.labor_cost, base.machine_cost, base.overhead_cost, base.total_hpp],
                        backgroundColor: 'rgba(100, 116, 139, 0.6)',
                        borderColor: 'rgba(100, 116, 139, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Simulasi (+Kenaikan)',
                        data: [sim.material_cost, sim.labor_cost, sim.machine_cost, sim.overhead_cost, sim.total_hpp],
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans' } } }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    }
}">

    <!-- Model Selector Topbar -->
    <div class="glass-card p-6 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-white">Pilih Produk & Model Biaya untuk Simulasi</h2>
            <p class="text-xs text-slate-400">Sandbox terisolasi: eksperimen tanpa mengubah data inventori maupun histori resmi (§23 Blueprint)</p>
        </div>

        <form method="GET" action="{{ route('simulator.index') }}" class="flex items-center gap-3">
            <select name="cost_model_id" onchange="this.form.submit()"
                    class="px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-semibold text-white min-w-[240px]">
                @foreach($products as $p)
                    @foreach($p->costModels as $cm)
                        <option value="{{ $cm->id }}" {{ ($selectedCostModel?->id === $cm->id) ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $cm->name }})
                        </option>
                    @endforeach
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Simulator Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Controls & Sliders Panel (4 Cols) -->
        <div class="glass-card p-6 rounded-2xl lg:col-span-4 space-y-5">
            <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                <i data-lucide="sliders" class="w-4 h-4 text-emerald-400"></i>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Faktor Perubahan Skenario</h3>
            </div>

            <!-- Material Price Surge Slider -->
            <div>
                <div class="flex justify-between text-xs font-semibold mb-2">
                    <span class="text-slate-300">Harga Bahan Baku:</span>
                    <span class="font-mono font-bold" :class="matChange >= 0 ? 'text-red-400' : 'text-emerald-400'" x-text="(matChange >= 0 ? '+' : '') + matChange + '%'"></span>
                </div>
                <input type="range" x-model.number="matChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
            </div>

            <!-- Labor Rate Increase Slider -->
            <div>
                <div class="flex justify-between text-xs font-semibold mb-2">
                    <span class="text-slate-300">Upah Tenaga Kerja:</span>
                    <span class="font-mono font-bold" :class="labChange >= 0 ? 'text-red-400' : 'text-emerald-400'" x-text="(labChange >= 0 ? '+' : '') + labChange + '%'"></span>
                </div>
                <input type="range" x-model.number="labChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
            </div>

            <!-- Machine Cost Change -->
            <div>
                <div class="flex justify-between text-xs font-semibold mb-2">
                    <span class="text-slate-300">Tarif Mesin & Listrik:</span>
                    <span class="font-mono font-bold text-slate-300" x-text="(macChange >= 0 ? '+' : '') + macChange + '%'"></span>
                </div>
                <input type="range" x-model.number="macChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
            </div>

            <!-- Overhead Change -->
            <div>
                <div class="flex justify-between text-xs font-semibold mb-2">
                    <span class="text-slate-300">Biaya Overhead (Sewa/BOP):</span>
                    <span class="font-mono font-bold text-slate-300" x-text="(ovhChange >= 0 ? '+' : '') + ovhChange + '%'"></span>
                </div>
                <input type="range" x-model.number="ovhChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
            </div>

            <!-- Target Markup -->
            <div class="pt-3 border-t border-slate-800">
                <div class="flex justify-between text-xs font-semibold mb-2">
                    <span class="text-slate-300">Target Markup Jual:</span>
                    <span class="font-mono font-bold text-emerald-400" x-text="markupPct + '%'"></span>
                </div>
                <input type="range" x-model.number="markupPct" @input="runSimulation()" min="10" max="150" step="5"
                       class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
            </div>
        </div>

        <!-- Simulation Comparison Results (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <template x-if="simResult">
                <div class="space-y-6">
                    <!-- Before vs After Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        
                        <!-- Baseline Card -->
                        <div class="glass-card p-5 rounded-2xl">
                            <div class="text-[11px] font-semibold text-slate-400 uppercase">HPP Semula (Baseline)</div>
                            <div class="text-2xl font-extrabold text-slate-300 font-mono mt-1">
                                {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.total_hpp).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                Harga Jual: {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.selling_price).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <!-- Simulated Card -->
                        <div class="glass-card p-5 rounded-2xl border-emerald-500/40 bg-slate-900/90">
                            <div class="text-[11px] font-semibold text-emerald-400 uppercase">HPP Hasil Simulasi</div>
                            <div class="text-2xl font-extrabold text-white font-mono mt-1">
                                {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.total_hpp).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-[11px] text-emerald-400 mt-1">
                                Harga Jual: {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.selling_price).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <!-- Delta Impact Card -->
                        <div class="glass-card p-5 rounded-2xl border-blue-500/30">
                            <div class="text-[11px] font-semibold text-blue-400 uppercase">Dampak Selisih Biaya</div>
                            <div class="text-2xl font-extrabold font-mono mt-1" :class="simResult.impact.delta_hpp_amount >= 0 ? 'text-red-400' : 'text-emerald-400'">
                                <span x-text="(simResult.impact.delta_hpp_amount >= 0 ? '+' : '') + '{{ $business->currency_symbol }} ' + Math.round(simResult.impact.delta_hpp_amount).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="text-[11px] font-bold mt-1" :class="simResult.impact.delta_hpp_percentage >= 0 ? 'text-red-400' : 'text-emerald-400'">
                                <span x-text="(simResult.impact.delta_hpp_percentage >= 0 ? '+' : '') + simResult.impact.delta_hpp_percentage.toFixed(1) + '% HPP'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Comparison Bar Chart -->
                    <div class="glass-card p-6 rounded-2xl">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Grafik Komparasi Biaya (Baseline vs Simulasi)</h3>
                        <div class="h-64">
                            <canvas id="simChart"></canvas>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
