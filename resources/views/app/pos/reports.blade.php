@extends('layouts.app', ['title' => 'Laporan & Analitik POS'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">

    <!-- ===================================================== -->
    <!-- 0. BREADCRUMB & TOOLBAR HEADER (macOS Sonoma Style)   -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Penjualan</span>
                <span>›</span>
                <a href="{{ route('pos.terminal') }}" class="hover:text-[#007AFF] transition-colors">Kasir POS</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Laporan &amp; Analitik</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Laporan &amp; Analitik POS</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Analisis performa penjualan kasir terintegrasi HPP (BOM / Recipe), laba kotor, jam ramai, dan metode bayar.</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('pos.reports_export') || \App\Support\Context::hasPermission('pos.reports'))
            <a id="btnPosExportExcel" href="{{ route('pos.reports.export-excel', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}"
                class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Ekspor Excel / CSV</span>
            </a>

            <button type="button" onclick="window.print()"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex-1 sm:flex-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.049-.37-2.14-.37-3.254 0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.114-.13 2.205-.37 3.254M6.72 13.829A8.966 8.966 0 004 19.5h16a8.966 8.966 0 00-2.72-5.671M6.72 13.829l1.83 1.83m6.9-1.83l-1.83 1.83" />
                </svg>
                <span>Cetak / PDF</span>
            </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 1. DATE FILTER CONTROLS                               -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4">
        <form method="GET" action="{{ route('pos.reports.index') }}" class="flex flex-wrap items-center gap-2.5 text-[13px]">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Rentang Tanggal:</span>
                <input type="date" id="posStartDate" name="start_date" value="{{ $startDate->toDateString() }}"
                    class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                <span class="text-black/40 dark:text-white/40">s/d</span>
                <input type="date" id="posEndDate" name="end_date" value="{{ $endDate->toDateString() }}"
                    class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
            </div>
            <button type="submit"
                class="h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 font-medium active:scale-[0.97] transition-all">
                Terapkan Filter
            </button>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY GRID (6 Flat Neutral Cards)            -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Tile 1: Total Revenue -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Penjualan</span>
            <div class="mt-2">
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium mt-1 tabular-nums">Hari Ini: Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Tile 2: Total HPP Modal -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Modal HPP</span>
            <div class="mt-2">
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">Akumulasi BOM Recipe</div>
            </div>
        </div>

        <!-- Tile 3: Gross Profit -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Laba Kotor</span>
            <div class="mt-2">
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</div>
                <div class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium mt-1 tabular-nums">Margin: {{ number_format($grossMarginPercent, 1) }}%</div>
            </div>
        </div>

        <!-- Tile 4: Transaksi & AOV -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Transaksi &amp; AOV</span>
            <div class="mt-2">
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#007AFF]">{{ number_format($ordersCount, 0, ',', '.') }} Order</div>
                <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 tabular-nums">AOV: Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Tile 5: Total Diskon -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Diskon</span>
            <div class="mt-2">
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">Rp {{ number_format($totalDiscount + ($totalVoucherDiscount ?? 0) + ($totalPointsDiscount ?? 0), 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/40 dark:text-white/40 mt-1 tabular-nums truncate">Voucher: Rp {{ number_format($totalVoucherDiscount ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Tile 6: Pajak / PPN -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Total Pajak / PPN</span>
            <div class="mt-2">
                <div class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black/80 dark:text-white/80">Rp {{ number_format($totalTax, 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/40 dark:text-white/40 mt-1 tabular-nums truncate">Service: Rp {{ number_format($totalServiceCharge ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CHARTS ROW: TREN HARIAN & JAM RAMAI                -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Daily Sales Trend Chart -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                    <span>Tren Penjualan &amp; Laba Harian</span>
                </h3>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Peak Hours Analysis Chart -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span>
                    <span>Analisis Jam Ramai (Peak Hours)</span>
                </h3>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="hourlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. TABLES ROW: TOP PRODUCTS & PAYMENT METHODS         -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Top 5 Products -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5">
            <h3 class="text-[15px] font-semibold text-black dark:text-white mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                <span>Top Produk Paling Laris</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Nama Produk</th>
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Terjual</th>
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Penjualan</th>
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Laba Kotor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($topProducts as $tp)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-2.5 px-3 font-medium text-black dark:text-white">{{ $tp->product_name }}</td>
                            <td class="py-2.5 px-3 text-right tabular-nums text-black/70 dark:text-white/70">{{ rtrim(rtrim((string)$tp->total_qty, '0'), '.') }}</td>
                            <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-black dark:text-white">Rp {{ number_format($tp->total_revenue, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($tp->total_revenue - $tp->total_cost, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-black/40 dark:text-white/40">Belum ada data produk terjual.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5">
            <h3 class="text-[15px] font-semibold text-black dark:text-white mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                <span>Performa Metode Pembayaran</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Metode Bayar</th>
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Transaksi</th>
                            <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($paymentMethods as $pm)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-2.5 px-3 font-semibold uppercase text-black dark:text-white">{{ str_replace('_', ' ', $pm->payment_method) }}</td>
                            <td class="py-2.5 px-3 text-center tabular-nums text-black/70 dark:text-white/70">{{ $pm->tx_count }} Transaksi</td>
                            <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($pm->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-black/40 dark:text-white/40">Belum ada data pembayaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. AVERAGE HARGA SNAPSHOT TRANSAKSI                   -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span>
                <span>Average Harga dari Snapshot Transaksi</span>
            </h3>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] font-semibold uppercase self-start sm:self-auto">
                Source: Transaction Snapshot
            </span>
        </div>
        <p class="text-[12px] text-black/50 dark:text-white/50 mb-4 leading-relaxed">
            Average harga dihitung dari snapshot harga yang tersimpan pada tiap detail transaksi (HPP &amp; harga jual saat transaksi), bukan dari harga master saat ini.
        </p>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5">
                <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Avg Harga Jual</div>
                <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-1">Rp {{ number_format($averageSellingPrice, 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">{{ number_format($snapshotTotalQty, 0, ',', '.') }} unit terjual</div>
            </div>
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5">
                <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Avg Harga Modal (HPP)</div>
                <div class="text-[18px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] mt-1">Rp {{ number_format($averageCostPrice, 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Weighted average by qty</div>
            </div>
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5">
                <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Modal (Snapshot)</div>
                <div class="text-[18px] font-bold tabular-nums text-black dark:text-white mt-1">Rp {{ number_format($snapshotTotalModal, 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">Omzet: Rp {{ number_format($snapshotTotalSales, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5">
                <div class="text-[10px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Gross Profit &amp; Margin</div>
                <div class="text-[18px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">Rp {{ number_format($snapshotGrossProfit, 0, ',', '.') }}</div>
                <div class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium mt-0.5 tabular-nums">Margin: {{ number_format($snapshotMarginPercent, 1) }}%</div>
            </div>
        </div>

        @if(! empty($productAveragePrices))
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk</th>
                        <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Qty Terjual</th>
                        <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Avg Harga Jual</th>
                        <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Avg Harga Modal</th>
                        <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Gross Profit</th>
                        <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($productAveragePrices as $prod)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-2.5 px-3 font-semibold text-black dark:text-white">{{ $prod['product_name'] }}</td>
                        <td class="py-2.5 px-3 text-center tabular-nums text-black/70 dark:text-white/70">{{ number_format($prod['total_quantity'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right tabular-nums text-black dark:text-white font-medium">Rp {{ number_format($prod['average_selling_price'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right tabular-nums text-black/60 dark:text-white/60">Rp {{ number_format($prod['average_cost_price'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($prod['gross_profit'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right tabular-nums text-black/70 dark:text-white/70 font-medium">{{ number_format($prod['margin_percentage'], 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- ===================================================== -->
<!-- 6. CHART.JS INITIALIZATION WITH APPLE SYSTEM COLORS   -->
<!-- ===================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Daily Trend Chart (System Blue for revenue, System Green for profit)
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
                            borderColor: '#007AFF',
                            backgroundColor: 'rgba(0, 122, 255, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2
                        },
                        {
                            label: 'Laba Kotor (Profit)',
                            data: dailyData.map(d => d.profit),
                            borderColor: '#34C759',
                            backgroundColor: 'transparent',
                            borderDash: [4, 4],
                            tension: 0.35,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#8E8E93', font: { family: '-apple-system, sans-serif', size: 11 } } }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#8E8E93', font: { size: 10 } } },
                        y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#8E8E93', font: { size: 10 } } }
                    }
                }
            });
        }

        // 2. Hourly Sales Chart (System Orange for peak hours)
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
                        backgroundColor: 'rgba(255, 149, 0, 0.75)',
                        borderColor: '#FF9500',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#8E8E93', font: { family: '-apple-system, sans-serif', size: 11 } } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#8E8E93', font: { size: 10 }, maxRotation: 45 } },
                        y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#8E8E93', font: { size: 10 }, precision: 0 } }
                    }
                }
            });
        }

        // 3. Keep Export Link synced with date inputs
        const startInput = document.getElementById('posStartDate');
        const endInput = document.getElementById('posEndDate');
        const exportBtn = document.getElementById('btnPosExportExcel');
        function syncExportUrl() {
            if (!exportBtn || !startInput || !endInput) return;
            try {
                const url = new URL(exportBtn.href, window.location.origin);
                url.searchParams.set('start_date', startInput.value);
                url.searchParams.set('end_date', endInput.value);
                exportBtn.href = url.pathname + url.search;
            } catch (e) {
                // Ignore url parse failure
            }
        }
        if (startInput && endInput) {
            startInput.addEventListener('change', syncExportUrl);
            endInput.addEventListener('change', syncExportUrl);
        }
    });
</script>
@endsection
