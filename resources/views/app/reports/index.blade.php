@extends('layouts.app', [
    'title' => 'Laporan HPP & Analitik',
    'headerTitle' => 'Laporan HPP & Analitik Struktur Biaya',
    'headerSubtitle' => 'Ringkasan komprehensif HPP per produk, persentase struktur biaya, dan audit margin'
])

@section('content')
<div class="space-y-8" x-data="{
    costBreakdown: {{ Js::from($costBreakdown) }},
    init() {
        const ctx = document.getElementById('costDonutChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Material', 'Labor', 'Mesin', 'Overhead'],
                    datasets: [{
                        data: [
                            this.costBreakdown.total_material_cost,
                            this.costBreakdown.total_labor_cost,
                            this.costBreakdown.total_machine_cost,
                            this.costBreakdown.total_overhead_cost
                        ],
                        backgroundColor: [
                            '#3b82f6', // blue
                            '#a855f7', // purple
                            '#f59e0b', // amber
                            '#10b981'  // emerald
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 11 } }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    }
}">

    <!-- Top Aggregate Summary Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Donut Chart Card -->
        <div class="glass-card p-6 rounded-2xl flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-white mb-1">Struktur Biaya Komposit</h3>
                <p class="text-xs text-slate-400">Komposisi total biaya terakumulasi seluruh lini produk</p>
            </div>

            <div class="h-56 relative my-4 flex items-center justify-center">
                <canvas id="costDonutChart"></canvas>
            </div>

            <div class="text-center text-xs text-slate-400">
                Total HPP Terakumulasi: <strong class="text-white">{{ $business->currency_symbol }} {{ number_format((float)$costBreakdown['total_hpp'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- Metric Details Cards (2 Cols) -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <div class="glass-card p-5 rounded-2xl border-blue-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-400 uppercase">Direct Material</span>
                    <span class="text-xs font-mono font-extrabold text-blue-400">{{ number_format($costBreakdown['material_percentage'], 1) }}%</span>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_material_cost'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Bahan baku pokok & penolong</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-purple-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-400 uppercase">Direct Labor</span>
                    <span class="text-xs font-mono font-extrabold text-purple-400">{{ number_format($costBreakdown['labor_percentage'], 1) }}%</span>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_labor_cost'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Tenaga kerja produksi langsung</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-amber-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-400 uppercase">Machine & Utility</span>
                    <span class="text-xs font-mono font-extrabold text-amber-400">{{ number_format($costBreakdown['machine_percentage'], 1) }}%</span>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_machine_cost'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Depresiasi mesin, listrik & servis</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-emerald-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400 uppercase">Overhead Pabrikasi</span>
                    <span class="text-xs font-mono font-extrabold text-emerald-400">{{ number_format($costBreakdown['overhead_percentage'], 1) }}%</span>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_overhead_cost'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Beban fasilitas & operasional teralokasi</p>
            </div>
        </div>
    </div>

    <!-- HPP per Product Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="table" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tabel Analisis Lengkap: Modal HPP, Harga Jual & Margin</span>
                </h3>
                <p class="text-[11px] text-slate-400">Rincian modal produksi, estimasi harga jual standar (Margin 40%), dan laba kotor per unit</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export-excel') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 shadow-md shadow-emerald-500/20 transition-all">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Export Excel (.CSV)</span>
                </a>
                <button onclick="window.print()" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-white flex items-center gap-1.5 transition-colors">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak PDF</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3 px-4 font-semibold">Produk & SKU</th>
                        <th class="py-3 px-4 font-semibold">Kategori</th>
                        <th class="py-3 px-4 font-semibold text-right">Modal Bahan</th>
                        <th class="py-3 px-4 font-semibold text-right">Labor & Mesin</th>
                        <th class="py-3 px-4 font-semibold text-right">Overhead</th>
                        <th class="py-3 px-4 font-semibold text-right bg-slate-900/80">HPP / Unit (Modal)</th>
                        <th class="py-3 px-4 font-semibold text-right bg-emerald-950/20 text-emerald-300">Harga Jual</th>
                        <th class="py-3 px-4 font-semibold text-right text-teal-300">Laba Kotor</th>
                        <th class="py-3 px-4 font-semibold text-center">Margin / Markup</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($hppReport as $row)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 font-sans font-bold text-white">
                            <div>{{ $row['product_name'] }}</div>
                            <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $row['sku'] ?? 'PRD' }} • Satuan: {{ $row['output_unit'] }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-sans text-slate-300">
                            {{ $row['category'] }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-300">
                            {{ $business->currency_symbol }} {{ number_format((float)$row['material_cost'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-300">
                            {{ $business->currency_symbol }} {{ number_format((float)($row['labor_cost'] + $row['machine_cost']), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-300">
                            {{ $business->currency_symbol }} {{ number_format((float)$row['overhead_cost'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-extrabold text-white text-sm bg-slate-900/40">
                            {{ $business->currency_symbol }} {{ number_format((float)$row['hpp_per_unit'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-extrabold text-emerald-400 text-sm bg-emerald-950/10">
                            {{ $business->currency_symbol }} {{ number_format((float)$row['recommended_price'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-extrabold text-teal-300">
                            {{ $business->currency_symbol }} {{ number_format((float)$row['gross_profit'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">
                                {{ $row['margin_percentage'] }}% Margin
                            </span>
                            <div class="text-[9px] text-slate-400 mt-0.5">{{ $row['markup_percentage'] }}% Markup</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-500 font-sans">
                            Belum ada laporan HPP dan margin yang dapat ditampilkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
