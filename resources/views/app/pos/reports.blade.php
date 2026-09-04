@extends('layouts.app', ['title' => 'Laporan & Analitik POS'])

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Laporan & Analitik POS</h1>
            <p class="text-sm text-slate-400 mt-1">Analisis performa penjualan kasir terintegrasi HPP (Cost of Goods Sold), laba kotor, jam ramai, dan metode bayar.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pos.reports.export-excel') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4 text-emerald-400"></i>
                <span>Ekspor Excel / CSV</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('pos.reports.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-semibold">Rentang Tanggal:</span>
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-white">
                <span class="text-slate-500">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-white">
            </div>
            <button type="submit" class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold transition">
                Terapkan Filter
            </button>
        </form>
    </div>

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Penjualan Kotor</div>
            <div class="text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="text-[11px] text-emerald-400 mt-1">Hari Ini: Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total HPP (Modal Pokok)</div>
            <div class="text-2xl font-black text-slate-300 font-mono mt-1">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Akumulasi biaya BOM / Recipe</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Laba Kotor (Gross Profit)</div>
            <div class="text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</div>
            <div class="text-[11px] text-teal-400 mt-1">Margin: {{ number_format($grossMarginPercent, 1) }}%</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transaksi & Nilai Rata-rata</div>
            <div class="text-2xl font-black text-cyan-400 font-mono mt-1">{{ number_format($ordersCount, 0, ',', '.') }} Order</div>
            <div class="text-[11px] text-slate-400 mt-1">AOV: Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Charts Row 1: Daily Trend & Peak Hours -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Sales Trend Chart -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-sm text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tren Penjualan & Laba Harian</span>
                </h3>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Peak Hours Analysis Chart -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-extrabold text-sm text-white flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                    <span>Analisis Jam Ramai (Peak Hours)</span>
                </h3>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="hourlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row: Top Products & Payment Methods -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Products -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <h3 class="font-extrabold text-sm text-white mb-3 flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-emerald-400"></i>
                <span>Top Produk Paling Laris</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/60 text-slate-400 uppercase text-[9px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2 px-3">Nama Produk</th>
                            <th class="py-2 px-3 text-right">Terjual</th>
                            <th class="py-2 px-3 text-right">Total Penjualan</th>
                            <th class="py-2 px-3 text-right">Laba Kotor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @forelse($topProducts as $tp)
                        <tr>
                            <td class="py-2.5 px-3 font-sans font-semibold text-white">{{ $tp->product_name }}</td>
                            <td class="py-2.5 px-3 text-right">{{ rtrim(rtrim((string)$tp->total_qty, '0'), '.') }}</td>
                            <td class="py-2.5 px-3 text-right text-emerald-400 font-bold">Rp {{ number_format($tp->total_revenue, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right text-teal-300">Rp {{ number_format($tp->total_revenue - $tp->total_cost, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500 font-sans">Belum ada data produk terjual.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <h3 class="font-extrabold text-sm text-white mb-3 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-cyan-400"></i>
                <span>Performa Metode Pembayaran</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/60 text-slate-400 uppercase text-[9px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2 px-3">Metode Bayar</th>
                            <th class="py-2 px-3 text-center">Jumlah Transaksi</th>
                            <th class="py-2 px-3 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @forelse($paymentMethods as $pm)
                        <tr>
                            <td class="py-2.5 px-3 font-sans font-bold uppercase text-white">{{ str_replace('_', ' ', $pm->payment_method) }}</td>
                            <td class="py-2.5 px-3 text-center">{{ $pm->tx_count }} Transaksi</td>
                            <td class="py-2.5 px-3 text-right text-emerald-400 font-bold">Rp {{ number_format($pm->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-slate-500 font-sans">Belum ada data pembayaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Average Harga Snapshot Transaksi (Bukan Harga Master) -->
    <div class="glass-card rounded-2xl p-5 border border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-sm text-white flex items-center gap-2">
                <i data-lucide="scale" class="w-4 h-4 text-amber-400"></i>
                <span>Average Harga dari Snapshot Transaksi</span>
            </h3>
            <span class="text-[10px] px-2 py-1 rounded-full bg-amber-500/10 text-amber-400 font-bold uppercase">Source: Transaction Snapshot</span>
        </div>
        <p class="text-[11px] text-slate-500 mb-4">Average harga dihitung dari snapshot harga yang tersimpan pada tiap detail transaksi (HPP & harga jual saat transaksi), bukan dari harga master produk saat ini. Perubahan harga master tidak akan mengubah angka di bawah ini.</p>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="rounded-xl bg-slate-900/60 border border-slate-800 p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Harga Jual</div>
                <div class="text-xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($averageSellingPrice, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-1">{{ number_format($snapshotTotalQty, 0, ',', '.') }} unit terjual</div>
            </div>
            <div class="rounded-xl bg-slate-900/60 border border-slate-800 p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Harga Modal (HPP)</div>
                <div class="text-xl font-black text-slate-300 font-mono mt-1">Rp {{ number_format($averageCostPrice, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-1">Weighted average by qty</div>
            </div>
            <div class="rounded-xl bg-slate-900/60 border border-slate-800 p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Modal (Snapshot)</div>
                <div class="text-xl font-black text-cyan-400 font-mono mt-1">Rp {{ number_format($snapshotTotalModal, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500 mt-1">Total Penjualan: Rp {{ number_format($snapshotTotalSales, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl bg-slate-900/60 border border-slate-800 p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gross Profit & Margin</div>
                <div class="text-xl font-black text-teal-400 font-mono mt-1">Rp {{ number_format($snapshotGrossProfit, 0, ',', '.') }}</div>
                <div class="text-[10px] text-emerald-400 mt-1">Margin: {{ number_format($snapshotMarginPercent, 1) }}%</div>
            </div>
        </div>

        @if(! empty($productAveragePrices))
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/60 text-slate-400 uppercase text-[9px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-2 px-3">Produk</th>
                        <th class="py-2 px-3 text-center">Qty Terjual</th>
                        <th class="py-2 px-3 text-right">Avg Harga Jual</th>
                        <th class="py-2 px-3 text-right">Avg Harga Modal</th>
                        <th class="py-2 px-3 text-right">Gross Profit</th>
                        <th class="py-2 px-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @foreach($productAveragePrices as $prod)
                    <tr>
                        <td class="py-2.5 px-3 font-sans font-bold text-white">{{ $prod['product_name'] }}</td>
                        <td class="py-2.5 px-3 text-center">{{ number_format($prod['total_quantity'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right">Rp {{ number_format($prod['average_selling_price'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right">Rp {{ number_format($prod['average_cost_price'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-emerald-400 font-bold">Rp {{ number_format($prod['gross_profit'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right">{{ number_format($prod['margin_percentage'], 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Daily Trend Chart
        const dailyData = @json($dailyTrend);
        const dailyCtx = document.getElementById('dailySalesChart');
        if (dailyCtx && dailyData.length > 0) {
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: dailyData.map(d => d.order_date),
                    datasets: [
                        {
                            label: 'Penjualan (Revenue)',
                            data: dailyData.map(d => d.revenue),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Laba Kotor (Profit)',
                            data: dailyData.map(d => d.profit),
                            borderColor: '#06b6d4',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans' } } }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }
                    }
                }
            });
        }

        // 2. Hourly Sales Chart
        const hourlyData = @json($hourlyData);
        const hourlyCtx = document.getElementById('hourlySalesChart');
        if (hourlyCtx) {
            const hours = Array.from({length: 24}, (_, i) => i);
            const hourMap = {};
            hourlyData.forEach(h => { hourMap[h.order_hour] = h.orders_count; });

            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: hours.map(h => String(h).padStart(2, '0') + ':00'),
                    datasets: [{
                        label: 'Jumlah Order',
                        data: hours.map(h => hourMap[h] || 0),
                        backgroundColor: 'rgba(245, 158, 11, 0.6)',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8' } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#94a3b8', maxRotation: 45 } },
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', precision: 0 } }
                    }
                }
            });
        }
    });
</script>
@endsection
