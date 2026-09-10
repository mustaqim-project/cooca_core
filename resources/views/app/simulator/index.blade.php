@extends('layouts.app', [
    'title' => 'What-If & Sensitivity Simulator',
    'headerTitle' => 'What-If & Sensitivity Simulator',
    'headerSubtitle' => 'Simulasikan dampak kenaikan harga bahan, UMR upah, dan overhead terhadap HPP dan laba tanpa merusak data master'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
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
        const textColor = isDark ? '#EBEBF599' : '#3C3C4399';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
        const baselineBg = isDark ? 'rgba(142, 142, 147, 0.35)' : 'rgba(142, 142, 147, 0.45)';
        const baselineBorder = isDark ? '#8E8E93' : '#636366';
        const simBg = isDark ? 'rgba(10, 132, 255, 0.55)' : 'rgba(0, 122, 255, 0.65)';
        const simBorder = isDark ? '#0A84FF' : '#007AFF';

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Bahan Baku', 'Tenaga Kerja', 'Mesin & Listrik', 'Overhead', 'Total HPP'],
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
                            font: { family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", Inter, sans-serif', size: 12, weight: '500' },
                            boxWidth: 12,
                            boxHeight: 12,
                            borderRadius: 3,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? 'rgba(44, 44, 46, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: isDark ? '#ffffff' : '#000000',
                        bodyColor: isDark ? '#EBEBF5' : '#1C1C1E',
                        borderColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.08)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + Math.round(context.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", Inter, sans-serif', size: 11 } },
                        grid: { color: gridColor, drawBorder: false }
                    },
                    y: {
                        ticks: {
                            color: textColor,
                            font: { family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", Inter, sans-serif', size: 11 },
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

    {{-- ===================================================== --}}
    {{-- 1. SUB-NAVIGATION TABS (Apple Segmented Control)      --}}
    {{-- ===================================================== --}}
    <div class="overflow-x-auto pb-1 scrollbar-none">
        <div class="inline-flex p-1 rounded-[11px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            <a href="{{ route('calculator.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Z" />
                </svg>
                <span>Kalkulator HPP Live</span>
            </a>
            <a href="{{ route('simulator.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                <span>What-If Simulator</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">Sandbox</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span>Katalog Produk &amp; BOM</span>
            </a>
            <a href="{{ route('materials.index') }}"
               class="px-3.5 py-1.5 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <span>Katalog Bahan Baku</span>
            </a>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 2. MODEL SELECTOR TOPBAR (macOS Sonoma Style)         --}}
    {{-- ===================================================== --}}
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            {{-- Breadcrumb minimal --}}
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Biaya &amp; Kalkulator</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">What-If Simulator</span>
            </nav>
            <div class="flex items-center gap-2">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">
                    What-If &amp; Sensitivity Simulator
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                    Sandbox Terisolasi
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50">Eksperimen sensitivitas biaya tanpa mengubah master harga beli atau data transaksi riil</p>
        </div>

        @if($products->isNotEmpty())
        <form method="GET" action="{{ route('simulator.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <div class="relative w-full md:w-80">
                <select name="cost_model_id" onchange="this.form.submit()"
                        class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-3.5 pr-8 text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
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
    </header>

    @if(!$selectedCostModel)
    {{-- ===================================================== --}}
    {{-- EMPTY STATE: NO COST MODEL                            --}}
    {{-- ===================================================== --}}
    <div class="p-12 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 text-center">
        <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3 text-black/30 dark:text-white/30">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
        </div>
        <h3 class="text-[17px] font-semibold text-black dark:text-white">Belum Ada Model Biaya (BOM) Terdaftar</h3>
        <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 mb-5 max-w-md mx-auto">
            Untuk menjalankan simulasi What-If, buat minimal satu produk dengan susunan resep (BOM) atau model biaya di kalkulator HPP.
        </p>
        <div class="flex items-center justify-center gap-2.5">
            <a href="{{ route('calculator.index') }}"
               class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Z" />
                </svg>
                <span>Buka Kalkulator HPP</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors flex items-center gap-1.5">
                <span>Kelola Produk</span>
            </a>
        </div>
    </div>
    @else

    {{-- ===================================================== --}}
    {{-- 3. 1-CLICK PRESET CHIPS (Apple Segmented Style)       --}}
    {{-- ===================================================== --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 space-y-2.5">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-semibold text-black dark:text-white uppercase tracking-wider">
                    Skenario Cepat (1-Klik Presets)
                </span>
                <span class="text-[10px] text-black/40 dark:text-white/40 font-medium">· Guncangan Pasar Instan</span>
            </div>
            <span class="text-[12px] text-black/45 dark:text-white/45">Klik tombol untuk menguji respon HPP langsung</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="applyPreset(15, 0, 0, 0, null)"
                    class="h-8 px-3 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 active:scale-[0.97] text-[12px] font-medium transition-all flex items-center gap-1.5">
                <span>🌾 Inflasi Bahan Baku (+15%)</span>
            </button>
            <button type="button" @click="applyPreset(0, 10, 0, 0, null)"
                    class="h-8 px-3 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 active:scale-[0.97] text-[12px] font-medium transition-all flex items-center gap-1.5">
                <span>👥 Kenaikan UMR (+10%)</span>
            </button>
            <button type="button" @click="applyPreset(0, 0, 20, 0, null)"
                    class="h-8 px-3 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 active:scale-[0.97] text-[12px] font-medium transition-all flex items-center gap-1.5">
                <span>⚡ Lonjakan Listrik (+20%)</span>
            </button>
            <button type="button" @click="applyPreset(0, 0, 0, 15, null)"
                    class="h-8 px-3 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 active:scale-[0.97] text-[12px] font-medium transition-all flex items-center gap-1.5">
                <span>📦 Kenaikan Overhead (+15%)</span>
            </button>
            <button type="button" @click="applyPreset(20, 10, 15, 10, null)"
                    class="h-8 px-3 rounded-[8px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A] active:scale-[0.97] text-[12px] font-semibold transition-all flex items-center gap-1.5">
                <span>🔥 Krisis Pasokan (+20% Bahan, +10% UMR, +15% Mesin)</span>
            </button>
            <button type="button" @click="applyPreset(0, 0, 0, 0, 40)"
                    class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/60 dark:text-white/60 active:scale-[0.97] text-[12px] font-medium transition-all flex items-center gap-1.5 ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>Reset ke 0%</span>
            </button>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- 4. MAIN SIMULATOR DUAL-COLUMN GRID                    --}}
    {{-- ===================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: PARAMETER SLIDERS (4 COLS) --}}
        <div class="lg:col-span-4 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-5">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    <h3 class="text-[12px] font-semibold text-black dark:text-white uppercase tracking-wider">
                        Faktor Perubahan Biaya
                    </h3>
                </div>
                <div class="flex items-center gap-1.5" x-show="loading" x-cloak>
                    <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                    <span class="text-[11px] font-mono text-[#007AFF] font-medium">Menghitung...</span>
                </div>
            </div>

            {{-- Slider 1: Bahan Baku --}}
            <div class="space-y-1.5">
                <div class="flex justify-between items-center text-[13px]">
                    <span class="font-medium text-black/80 dark:text-white/80">
                        Harga Bahan Baku:
                    </span>
                    <span class="font-mono tabular-nums font-semibold text-[12px] px-2 py-0.5 rounded-full"
                          :class="matChange > 0 ? 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' : (matChange < 0 ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.06] text-black/60 dark:bg-white/[0.08] dark:text-white/60')"
                          x-text="(matChange >= 0 ? '+' : '') + matChange + '%'"></span>
                </div>
                <input type="range" x-model.number="matChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-1.5 bg-black/[0.08] dark:bg-white/[0.1] rounded-lg appearance-none cursor-pointer accent-[#007AFF]">
                <div class="flex justify-between text-[10px] text-black/40 dark:text-white/40 font-mono tabular-nums">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 2: Upah Tenaga Kerja --}}
            <div class="space-y-1.5 pt-2 border-t border-black/5 dark:border-white/5">
                <div class="flex justify-between items-center text-[13px]">
                    <span class="font-medium text-black/80 dark:text-white/80">
                        Upah Tenaga Kerja:
                    </span>
                    <span class="font-mono tabular-nums font-semibold text-[12px] px-2 py-0.5 rounded-full"
                          :class="labChange > 0 ? 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' : (labChange < 0 ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.06] text-black/60 dark:bg-white/[0.08] dark:text-white/60')"
                          x-text="(labChange >= 0 ? '+' : '') + labChange + '%'"></span>
                </div>
                <input type="range" x-model.number="labChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-1.5 bg-black/[0.08] dark:bg-white/[0.1] rounded-lg appearance-none cursor-pointer accent-[#007AFF]">
                <div class="flex justify-between text-[10px] text-black/40 dark:text-white/40 font-mono tabular-nums">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 3: Tarif Mesin & Listrik --}}
            <div class="space-y-1.5 pt-2 border-t border-black/5 dark:border-white/5">
                <div class="flex justify-between items-center text-[13px]">
                    <span class="font-medium text-black/80 dark:text-white/80">
                        Tarif Mesin &amp; Listrik:
                    </span>
                    <span class="font-mono tabular-nums font-semibold text-[12px] px-2 py-0.5 rounded-full"
                          :class="macChange > 0 ? 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' : (macChange < 0 ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.06] text-black/60 dark:bg-white/[0.08] dark:text-white/60')"
                          x-text="(macChange >= 0 ? '+' : '') + macChange + '%'"></span>
                </div>
                <input type="range" x-model.number="macChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-1.5 bg-black/[0.08] dark:bg-white/[0.1] rounded-lg appearance-none cursor-pointer accent-[#007AFF]">
                <div class="flex justify-between text-[10px] text-black/40 dark:text-white/40 font-mono tabular-nums">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 4: Biaya Overhead --}}
            <div class="space-y-1.5 pt-2 border-t border-black/5 dark:border-white/5">
                <div class="flex justify-between items-center text-[13px]">
                    <span class="font-medium text-black/80 dark:text-white/80">
                        Overhead Pabrik / Toko:
                    </span>
                    <span class="font-mono tabular-nums font-semibold text-[12px] px-2 py-0.5 rounded-full"
                          :class="ovhChange > 0 ? 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' : (ovhChange < 0 ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.06] text-black/60 dark:bg-white/[0.08] dark:text-white/60')"
                          x-text="(ovhChange >= 0 ? '+' : '') + ovhChange + '%'"></span>
                </div>
                <input type="range" x-model.number="ovhChange" @input="runSimulation()" min="-50" max="100" step="5"
                       class="w-full h-1.5 bg-black/[0.08] dark:bg-white/[0.1] rounded-lg appearance-none cursor-pointer accent-[#007AFF]">
                <div class="flex justify-between text-[10px] text-black/40 dark:text-white/40 font-mono tabular-nums">
                    <span>-50%</span>
                    <span>0% (Tetap)</span>
                    <span>+100%</span>
                </div>
            </div>

            {{-- Slider 5: Target Markup Jual --}}
            <div class="space-y-1.5 pt-3 border-t border-black/10 dark:border-white/10">
                <div class="flex justify-between items-center text-[13px]">
                    <span class="font-semibold text-black dark:text-white">
                        Target Markup Jual:
                    </span>
                    <span class="font-mono tabular-nums font-semibold text-[12px] px-2 py-0.5 rounded-full bg-[#007AFF]/12 text-[#007AFF]"
                          x-text="markupPct + '%'"></span>
                </div>
                <input type="range" x-model.number="markupPct" @input="runSimulation()" min="10" max="150" step="5"
                       class="w-full h-1.5 bg-black/[0.08] dark:bg-white/[0.1] rounded-lg appearance-none cursor-pointer accent-[#007AFF]">
                <div class="flex justify-between text-[10px] text-black/40 dark:text-white/40 font-mono tabular-nums">
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
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                            <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide block mb-1">
                                HPP Semula (Baseline)
                            </span>
                            <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white">
                                {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.total_hpp).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-black/5 dark:border-white/5 text-[12px] text-black/50 dark:text-white/50 flex items-center justify-between">
                                <span>Harga Jual:</span>
                                <span class="font-mono tabular-nums font-medium text-black/80 dark:text-white/80">
                                    {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.selling_price).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        {{-- Simulated Card --}}
                        <div class="rounded-[14px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-[11px] font-medium text-[#007AFF] uppercase tracking-wide">
                                    HPP Hasil Simulasi
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                            </div>
                            <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#007AFF]">
                                {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.total_hpp).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-[#007AFF]/15 text-[12px] text-[#007AFF] flex items-center justify-between">
                                <span>Rekomendasi Jual:</span>
                                <span class="font-mono tabular-nums font-semibold">
                                    {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.selling_price).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        {{-- Delta Impact Card --}}
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                            <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide block mb-1">
                                Dampak Selisih Biaya
                            </span>
                            <div class="text-[20px] sm:text-[22px] font-bold tabular-nums font-mono"
                                 :class="simResult.impact.delta_hpp_amount > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (simResult.impact.delta_hpp_amount < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black dark:text-white')">
                                <span x-text="(simResult.impact.delta_hpp_amount > 0 ? '+' : '') + '{{ $business->currency_symbol }} ' + Math.round(simResult.impact.delta_hpp_amount).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-black/5 dark:border-white/5 text-[12px] font-medium flex items-center justify-between"
                                 :class="simResult.impact.delta_hpp_percentage > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (simResult.impact.delta_hpp_percentage < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/50')">
                                <span>Pergeseran HPP:</span>
                                <span class="font-mono tabular-nums">
                                    <span x-text="(simResult.impact.delta_hpp_percentage > 0 ? '+' : '') + simResult.impact.delta_hpp_percentage.toFixed(1) + '%'"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Breakdown Table (Baseline vs Simulasi) --}}
                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
                        <div class="px-4 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between flex-wrap gap-2">
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">
                                Tabel Rincian Komponen Biaya
                            </h3>
                            <span class="text-[12px] text-black/45 dark:text-white/45">Perbandingan nominal riil antar elemen biaya</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]">
                                <thead>
                                    <tr class="border-b border-black/5 dark:border-white/10 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">
                                        <th class="px-4 py-2.5">Komponen Biaya</th>
                                        <th class="px-4 py-2.5 text-right">Semula (Baseline)</th>
                                        <th class="px-4 py-2.5 text-right">Hasil Simulasi</th>
                                        <th class="px-4 py-2.5 text-right">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    {{-- Row 1: Material --}}
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 py-3 font-medium text-black dark:text-white flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                            <span>Bahan Baku (Raw Material)</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums text-black/60 dark:text-white/60">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.material_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-black dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.material_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold"
                                            :class="matChange > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (matChange < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/40')">
                                            <span x-text="(matChange >= 0 ? '+' : '') + matChange + '%'"></span>
                                        </td>
                                    </tr>

                                    {{-- Row 2: Labor --}}
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 py-3 font-medium text-black dark:text-white flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                            <span>Tenaga Kerja Langsung</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums text-black/60 dark:text-white/60">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.labor_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-black dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.labor_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold"
                                            :class="labChange > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (labChange < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/40')">
                                            <span x-text="(labChange >= 0 ? '+' : '') + labChange + '%'"></span>
                                        </td>
                                    </tr>

                                    {{-- Row 3: Machine --}}
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 py-3 font-medium text-black dark:text-white flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#5856D6]"></span>
                                            <span>Tarif Mesin &amp; Listrik</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums text-black/60 dark:text-white/60">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.machine_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-black dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.machine_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold"
                                            :class="macChange > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (macChange < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/40')">
                                            <span x-text="(macChange >= 0 ? '+' : '') + macChange + '%'"></span>
                                        </td>
                                    </tr>

                                    {{-- Row 4: Overhead --}}
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-4 py-3 font-medium text-black dark:text-white flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>
                                            <span>Overhead Pabrik (BOP)</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums text-black/60 dark:text-white/60">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.overhead_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-black dark:text-white">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.overhead_cost).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold"
                                            :class="ovhChange > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (ovhChange < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/40')">
                                            <span x-text="(ovhChange >= 0 ? '+' : '') + ovhChange + '%'"></span>
                                        </td>
                                    </tr>

                                    {{-- Total Row --}}
                                    <tr class="bg-black/[0.02] dark:bg-white/[0.03] font-semibold border-t border-black/10 dark:border-white/10">
                                        <td class="px-4 py-3.5 text-black dark:text-white">
                                            Total HPP per Satuan
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-mono tabular-nums text-black/70 dark:text-white/70">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.baseline.total_hpp).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-mono tabular-nums text-[#007AFF] font-bold text-[14px]">
                                            {{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.total_hpp).toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-mono tabular-nums font-bold"
                                            :class="simResult.impact.delta_hpp_percentage > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : (simResult.impact.delta_hpp_percentage < 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/40')">
                                            <span x-text="(simResult.impact.delta_hpp_percentage >= 0 ? '+' : '') + simResult.impact.delta_hpp_percentage.toFixed(1) + '%'"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Visual Comparison Bar Chart --}}
                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-[15px] font-semibold text-black dark:text-white">
                                    Grafik Komparasi Biaya (Baseline vs Simulasi)
                                </h3>
                                <p class="text-[12px] text-black/45 dark:text-white/45">Visualisasi selisih tiap elemen biaya</p>
                            </div>
                        </div>
                        <div class="h-64 sm:h-72 relative">
                            <canvas id="simChart"></canvas>
                        </div>
                    </div>

                    {{-- Executive AI Insight (Apple Intelligence Style - System Purple) --}}
                    <div class="p-4 sm:p-5 rounded-[14px] bg-[#AF52DE]/10 border border-[#AF52DE]/20 flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-[8px] bg-[#AF52DE]/15 flex items-center justify-center shrink-0 text-[#AF52DE]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.516 0c.85.493 1.509 1.333 1.509 2.316V18" />
                            </svg>
                        </div>
                        <div class="space-y-1 text-[13px]">
                            <h4 class="font-semibold text-[#7C3AA6] dark:text-[#BF5AF2]">
                                Rekomendasi Penyesuaian Harga Bisnis
                            </h4>
                            <p class="text-[#7C3AA6]/90 dark:text-[#BF5AF2]/90 leading-relaxed">
                                Jika biaya skenario ini terjadi secara riil, Anda disarankan menaikkan harga jual produk minimal menjadi
                                <strong class="font-mono tabular-nums underline">{{ $business->currency_symbol }} <span x-text="Math.round(simResult.simulated.selling_price).toLocaleString('id-ID')"></span></strong>
                                (kenaikan <span class="font-mono tabular-nums font-semibold">{{ $business->currency_symbol }} <span x-text="Math.round(simResult.impact.delta_selling_price).toLocaleString('id-ID')"></span></span>)
                                guna mempertahankan target markup <span class="font-mono tabular-nums font-semibold" x-text="markupPct + '%'"></span>.
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
