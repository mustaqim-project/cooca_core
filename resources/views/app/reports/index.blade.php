@extends('layouts.app', [
    'title' => 'Laporan & Analitik Bisnis',
    'headerTitle' => 'Laporan & Analitik Finansial',
    'headerSubtitle' => 'Pantau Laba Rugi riil, Arus Kas, Umur Piutang/Hutang, dan Valuasi Stok dari snapshot transaksi'
])

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab }}',
    costBreakdown: {{ Js::from($costBreakdown) }},
    init() {
        this.$nextTick(() => {
            const ctx = document.getElementById('costDonutChart');
            if (ctx && typeof Chart !== 'undefined') {
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
                            backgroundColor: ['#3b82f6', '#a855f7', '#f59e0b', '#10b981'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 11 } } }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    }
}">

    <!-- Top Filter & Action Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        <!-- Date Preset Filter Form -->
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" :value="activeTab">
            
            <div class="flex items-center rounded-xl bg-slate-900 border border-slate-700/80 p-1 text-xs">
                <button type="submit" name="preset" value="today"
                    class="px-3 py-1.5 rounded-lg font-medium transition {{ $preset === 'today' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                    Hari Ini
                </button>
                <button type="submit" name="preset" value="7days"
                    class="px-3 py-1.5 rounded-lg font-medium transition {{ $preset === '7days' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                    7 Hari
                </button>
                <button type="submit" name="preset" value="this_month"
                    class="px-3 py-1.5 rounded-lg font-medium transition {{ $preset === 'this_month' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                    Bulan Ini
                </button>
                <button type="submit" name="preset" value="this_year"
                    class="px-3 py-1.5 rounded-lg font-medium transition {{ $preset === 'this_year' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                    Tahun Ini
                </button>
            </div>

            <!-- Custom Date Range -->
            <div class="flex items-center gap-2 text-xs">
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
                    class="bg-slate-900 border border-slate-700/80 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                <span class="text-slate-500">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
                    class="bg-slate-900 border border-slate-700/80 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 transition" title="Terapkan Filter Tanggal">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </form>

        <!-- Export & Print Actions -->
        <div class="flex items-center gap-2">
            <a :href="'{{ route('reports.export-excel') }}?type=' + activeTab + '&start_date={{ $startDate->toDateString() }}&end_date={{ $endDate->toDateString() }}'"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-emerald-600/20 transition whitespace-nowrap">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                <span>Export Excel (.CSV)</span>
            </a>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-bold text-slate-300 hover:text-white flex items-center gap-1.5 transition">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Cetak PDF</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-800 space-x-2 overflow-x-auto pb-1 text-xs font-bold">
        <button type="button" @click="activeTab = 'income_statement'"
            :class="activeTab === 'income_statement' ? 'border-b-2 border-emerald-400 text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-white'"
            class="px-4 py-2.5 rounded-t-xl flex items-center gap-2 transition whitespace-nowrap">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
            <span>1. Laba Rugi (P&amp;L)</span>
        </button>

        <button type="button" @click="activeTab = 'cash_flow'"
            :class="activeTab === 'cash_flow' ? 'border-b-2 border-emerald-400 text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-white'"
            class="px-4 py-2.5 rounded-t-xl flex items-center gap-2 transition whitespace-nowrap">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            <span>2. Arus Kas (Cash Flow)</span>
        </button>

        <button type="button" @click="activeTab = 'aging'"
            :class="activeTab === 'aging' ? 'border-b-2 border-emerald-400 text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-white'"
            class="px-4 py-2.5 rounded-t-xl flex items-center gap-2 transition whitespace-nowrap">
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span>3. Umur Piutang &amp; Hutang (AR/AP)</span>
        </button>

        <button type="button" @click="activeTab = 'stock_valuation'"
            :class="activeTab === 'stock_valuation' ? 'border-b-2 border-emerald-400 text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-white'"
            class="px-4 py-2.5 rounded-t-xl flex items-center gap-2 transition whitespace-nowrap">
            <i data-lucide="boxes" class="w-4 h-4"></i>
            <span>4. Valuasi &amp; Perputaran Stok</span>
        </button>

        <button type="button" @click="activeTab = 'hpp'"
            :class="activeTab === 'hpp' ? 'border-b-2 border-emerald-400 text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-white'"
            class="px-4 py-2.5 rounded-t-xl flex items-center gap-2 transition whitespace-nowrap">
            <i data-lucide="pie-chart" class="w-4 h-4"></i>
            <span>5. Struktur HPP &amp; Biaya</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: LABA RUGI RIIL (INCOME STATEMENT) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'income_statement'" class="space-y-6">
        <!-- Top KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card p-5 rounded-2xl border-emerald-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-wider">Penjualan Bersih</span>
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div class="text-2xl font-black text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['net_sales'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">POS + Invoice - Retur & Diskon</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-amber-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-amber-400 uppercase tracking-wider">HPP Barang Terjual (COGS)</span>
                    <i data-lucide="layers" class="w-4 h-4 text-amber-400"></i>
                </div>
                <div class="text-2xl font-black text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['total_cogs'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Snapshot modal barang yang laku</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-cyan-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-cyan-400 uppercase tracking-wider">Laba Kotor (Gross Profit)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-300 font-mono">
                        {{ $incomeStatement['gross_profit']['margin'] }}%
                    </span>
                </div>
                <div class="text-2xl font-black text-cyan-300 font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($incomeStatement['gross_profit']['amount'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Pendapatan Bersih - HPP</p>
            </div>

            <div class="glass-card p-5 rounded-2xl {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'border-emerald-500/30 bg-emerald-950/10' : 'border-rose-500/30 bg-rose-950/10' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        Laba Bersih Operasional
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black font-mono {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                        {{ $incomeStatement['net_profit']['margin'] }}%
                    </span>
                </div>
                <div class="text-2xl font-black font-mono mt-2 {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'text-emerald-300' : 'text-rose-400' }}">
                    {{ $business->currency_symbol }} {{ number_format($incomeStatement['net_profit']['amount'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Setelah dikurangi beban operasional</p>
            </div>
        </div>

        <!-- Detailed Statement Table -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
            <div class="p-4 bg-slate-900/90 border-b border-slate-800 flex justify-between items-center text-xs">
                <span class="font-black text-white uppercase tracking-wider">Rincian Laporan Laba Rugi</span>
                <span class="font-mono text-slate-400">Periode: {{ $incomeStatement['period']['label'] }}</span>
            </div>

            <div class="p-6 space-y-6 text-xs font-sans">
                <!-- Section 1: Pendapatan -->
                <div class="space-y-2">
                    <div class="font-black text-emerald-400 uppercase text-[11px] tracking-wider border-b border-slate-800 pb-1">
                        1. PENDAPATAN USAHA (REVENUES)
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span class="pl-3">Penjualan Kasir POS (Gross)</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['pos_gross_sales'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span class="pl-3">Penjualan Faktur Invoice (Gross)</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['invoice_gross_sales'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-rose-400">
                        <span class="pl-3">(-) Potongan Diskon Penjualan &amp; Voucher</span>
                        <span class="font-mono font-bold">({{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['total_discounts'], 0, ',', '.') }})</span>
                    </div>
                    <div class="flex justify-between py-1 text-rose-400">
                        <span class="pl-3">(-) Retur Penjualan &amp; Pengembalian</span>
                        <span class="font-mono font-bold">({{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['sales_returns'], 0, ',', '.') }})</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-800/80 font-bold text-white bg-slate-900/50 p-2 rounded-xl">
                        <span>TOTAL PENDAPATAN BERSIH (NET REVENUE)</span>
                        <span class="font-mono text-emerald-400 text-sm">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['net_sales'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Section 2: HPP -->
                <div class="space-y-2">
                    <div class="font-black text-amber-400 uppercase text-[11px] tracking-wider border-b border-slate-800 pb-1">
                        2. HARGA POKOK PENJUALAN (COGS)
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span class="pl-3">HPP Transaksi Kasir POS</span>
                        <span class="font-mono text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['pos_cogs'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span class="pl-3">HPP Penjualan Faktur Invoice</span>
                        <span class="font-mono text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['invoice_cogs'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-emerald-400">
                        <span class="pl-3">(-) Pemulihan HPP dari Retur Penjualan</span>
                        <span class="font-mono">({{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['returns_cogs_recovery'], 0, ',', '.') }})</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-800/80 font-bold text-white bg-slate-900/50 p-2 rounded-xl">
                        <span>TOTAL HPP BARANG TERJUAL</span>
                        <span class="font-mono text-amber-400 text-sm">{{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['total_cogs'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Gross Profit Callout -->
                <div class="flex justify-between p-3 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-white font-bold">
                    <div class="flex items-center gap-2">
                        <span class="text-cyan-400 text-sm">LABA KOTOR (GROSS PROFIT)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Margin: {{ $incomeStatement['gross_profit']['margin'] }}%</span>
                    </div>
                    <span class="font-mono text-cyan-300 text-base">{{ $business->currency_symbol }} {{ number_format($incomeStatement['gross_profit']['amount'], 0, ',', '.') }}</span>
                </div>

                <!-- Section 3: Beban Operasional -->
                <div class="space-y-2">
                    <div class="font-black text-purple-400 uppercase text-[11px] tracking-wider border-b border-slate-800 pb-1">
                        3. BEBAN OPERASIONAL (OPERATING EXPENSES)
                    </div>
                    @forelse($incomeStatement['expenses']['by_category'] as $catName => $catAmount)
                    <div class="flex justify-between py-1 text-slate-300">
                        <span class="pl-3">Beban {{ $catName }}</span>
                        <span class="font-mono text-white">{{ $business->currency_symbol }} {{ number_format($catAmount, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-slate-500 py-1 pl-3 italic">Tidak ada pencatatan beban operasional pada periode ini.</div>
                    @endforelse
                    <div class="flex justify-between pt-2 border-t border-slate-800/80 font-bold text-white bg-slate-900/50 p-2 rounded-xl">
                        <span>TOTAL BEBAN OPERASIONAL</span>
                        <span class="font-mono text-purple-400 text-sm">{{ $business->currency_symbol }} {{ number_format($incomeStatement['expenses']['total'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Net Profit Callout -->
                <div class="flex justify-between p-4 rounded-xl border {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' : 'bg-rose-500/15 border-rose-500/40 text-rose-300' }} font-black">
                    <div class="flex flex-col">
                        <span class="text-sm">LABA BERSIH OPERASIONAL (NET PROFIT)</span>
                        <span class="text-[10px] text-slate-400 font-normal mt-0.5">Margin Bersih Akhir: {{ $incomeStatement['net_profit']['margin'] }}%</span>
                    </div>
                    <span class="font-mono text-lg">{{ $business->currency_symbol }} {{ number_format($incomeStatement['net_profit']['amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: ARUS KAS (CASH FLOW STATEMENT) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'cash_flow'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="glass-card p-5 rounded-2xl border-emerald-500/20">
                <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-wider">Total Kas Masuk (Inflow)</span>
                <div class="text-2xl font-black text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['total'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Kasir + Pelunasan Piutang + Kas Masuk</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-rose-500/20">
                <span class="text-[10px] font-extrabold text-rose-400 uppercase tracking-wider">Total Kas Keluar (Outflow)</span>
                <div class="text-2xl font-black text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['total'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Bayar Hutang Supplier + Beban + Refund</p>
            </div>

            <div class="glass-card p-5 rounded-2xl {{ $cashFlow['net_cash_flow'] >= 0 ? 'border-cyan-500/30 bg-cyan-950/10' : 'border-rose-500/30 bg-rose-950/10' }}">
                <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $cashFlow['net_cash_flow'] >= 0 ? 'text-cyan-400' : 'text-rose-400' }}">
                    Arus Kas Bersih (Net Cash)
                </span>
                <div class="text-2xl font-black font-mono mt-2 {{ $cashFlow['net_cash_flow'] >= 0 ? 'text-cyan-300' : 'text-rose-400' }}">
                    {{ $business->currency_symbol }} {{ number_format($cashFlow['net_cash_flow'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Selisih Aliran Kas Masuk - Keluar</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Inflows Breakdown -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-4">
                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    <span>Rincian Penerimaan Kas (Inflows)</span>
                </h4>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Penerimaan Kasir POS</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['pos_payments'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Pelunasan Piutang Invoice</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['invoice_payments'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Pemasukan Kas Non-Penjualan</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['direct_cash_in'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Outflows Breakdown -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-4">
                <h4 class="text-xs font-bold text-rose-400 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                    <span>Rincian Pengeluaran Kas (Outflows)</span>
                </h4>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Pelunasan Tagihan Supplier (AP)</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['supplier_payments'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Pengeluaran Beban Operasional</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['expenses'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Pengembalian Refund Kas Retur</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['cash_refunds'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800/80">
                        <span class="text-slate-300">Pengeluaran Kas Langsung</span>
                        <span class="font-mono font-bold text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['direct_cash_out'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: UMUR PIUTANG (AR) & HUTANG (AP) AGING -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'aging'" x-cloak class="space-y-6">
        <!-- 3.1 AR Aging (Piutang Pelanggan) -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden space-y-4 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-cyan-400"></i>
                        <span>Analisis Umur Piutang Pelanggan (AR Aging)</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Klasifikasi invoice yang belum dibayar berdasarkan keterlambatan jatuh tempo</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Total Piutang Beredar:</span>
                    <div class="text-base font-black text-cyan-400 font-mono">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['total_balance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- AR Aging Buckets Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400">Belum Jatuh Tempo</span>
                    <div class="text-sm font-bold text-emerald-400 font-mono mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['current'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400">1 - 30 Hari</span>
                    <div class="text-sm font-bold text-blue-400 font-mono mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['1_30'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400">31 - 60 Hari</span>
                    <div class="text-sm font-bold text-amber-400 font-mono mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['31_60'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400">61 - 90 Hari</span>
                    <div class="text-sm font-bold text-orange-400 font-mono mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['61_90'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30">
                    <span class="text-[10px] font-bold text-rose-400">&gt; 90 Hari (Macet)</span>
                    <div class="text-sm font-bold text-rose-300 font-mono mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['over_90'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- AR Details Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-900/90 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 font-sans">No. Invoice</th>
                            <th class="py-2.5 px-3 font-sans">Pelanggan</th>
                            <th class="py-2.5 px-3 text-center">Tgl Faktur</th>
                            <th class="py-2.5 px-3 text-center">Jatuh Tempo</th>
                            <th class="py-2.5 px-3 text-center">Keterlambatan</th>
                            <th class="py-2.5 px-3 text-right">Sisa Piutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($agingSummary['ar']['details'] as $ar)
                        <tr class="hover:bg-slate-800/20">
                            <td class="py-2.5 px-3 font-bold text-white font-sans">{{ $ar['invoice_number'] }}</td>
                            <td class="py-2.5 px-3 font-sans">{{ $ar['customer_name'] }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $ar['invoice_date'] }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $ar['due_date'] }}</td>
                            <td class="py-2.5 px-3 text-center">
                                @if($ar['days_overdue'] > 60)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 font-sans">{{ $ar['days_overdue'] }} Hari</span>
                                @elseif($ar['days_overdue'] > 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 font-sans">{{ $ar['days_overdue'] }} Hari</span>
                                @else
                                    <span class="text-slate-500 text-[11px] font-sans">Belum Jatuh Tempo</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-white">
                                {{ $business->currency_symbol }} {{ number_format($ar['balance_due'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500 text-xs font-sans">Tidak ada piutang invoice yang belum lunas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3.2 AP Aging (Hutang Supplier) -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden space-y-4 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-purple-400"></i>
                        <span>Analisis Umur Hutang Vendor (AP Aging)</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Klasifikasi tagihan supplier yang belum dibayar berdasarkan keterlambatan jatuh tempo</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Total Hutang Beredar:</span>
                    <div class="text-base font-black text-purple-400 font-mono">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ap']['total_balance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- AP Details Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-900/90 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 font-sans">No. Tagihan Vendor</th>
                            <th class="py-2.5 px-3 font-sans">Supplier / Vendor</th>
                            <th class="py-2.5 px-3 text-center">Tgl Tagihan</th>
                            <th class="py-2.5 px-3 text-center">Jatuh Tempo</th>
                            <th class="py-2.5 px-3 text-center">Keterlambatan</th>
                            <th class="py-2.5 px-3 text-right">Sisa Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($agingSummary['ap']['details'] as $ap)
                        <tr class="hover:bg-slate-800/20">
                            <td class="py-2.5 px-3 font-bold text-white font-sans">{{ $ap['invoice_number'] }}</td>
                            <td class="py-2.5 px-3 font-sans">{{ $ap['supplier_name'] }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $ap['invoice_date'] }}</td>
                            <td class="py-2.5 px-3 text-center text-slate-400">{{ $ap['due_date'] }}</td>
                            <td class="py-2.5 px-3 text-center">
                                @if($ap['days_overdue'] > 30)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 font-sans">{{ $ap['days_overdue'] }} Hari</span>
                                @elseif($ap['days_overdue'] > 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 font-sans">{{ $ap['days_overdue'] }} Hari</span>
                                @else
                                    <span class="text-slate-500 text-[11px] font-sans">Belum Jatuh Tempo</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-white">
                                {{ $business->currency_symbol }} {{ number_format($ap['balance_due'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500 text-xs font-sans">Tidak ada tagihan hutang supplier yang belum lunas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: VALUASI & PERPUTARAN STOK -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'stock_valuation'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="glass-card p-5 rounded-2xl border-emerald-500/20">
                <span class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-wider">Total Nilai Valuasi Persediaan</span>
                <div class="text-2xl font-black text-white font-mono mt-2">
                    {{ $business->currency_symbol }} {{ number_format($stockValuation['summary']['total_valuation'], 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Total Aset Fisik Stok × WAC Modal</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-blue-500/20">
                <span class="text-[10px] font-extrabold text-blue-400 uppercase tracking-wider">Total Kuantitas Fisik</span>
                <div class="text-2xl font-black text-white font-mono mt-2">
                    {{ number_format($stockValuation['summary']['total_physical_units'], 0, ',', '.') }} <span class="text-sm font-normal text-slate-400">unit</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">{{ $stockValuation['summary']['total_products'] }} variasi produk aktif</p>
            </div>

            <div class="glass-card p-5 rounded-2xl border-purple-500/20">
                <span class="text-[10px] font-extrabold text-purple-400 uppercase tracking-wider">Perputaran Cepat (Fast Moving)</span>
                <div class="text-2xl font-black text-purple-300 font-mono mt-2">
                    {{ count($stockValuation['fast_moving']) }} <span class="text-sm font-normal text-slate-400">produk teratas</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Paling laku dalam 30 hari terakhir</p>
            </div>
        </div>

        <!-- Valuation Table -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <div class="p-4 bg-slate-900/90 border-b border-slate-800 text-xs font-bold text-white flex justify-between items-center">
                <span>Daftar Nilai Valuasi Stok per Produk</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 font-sans">Produk &amp; SKU</th>
                            <th class="py-2.5 px-3 font-sans">Kategori</th>
                            <th class="py-2.5 px-3 text-center">Stok Saat Ini</th>
                            <th class="py-2.5 px-3 text-right">Modal Rata-rata (WAC)</th>
                            <th class="py-2.5 px-3 text-right">Total Nilai Valuasi</th>
                            <th class="py-2.5 px-3 text-center">Penjualan 30 Hari</th>
                            <th class="py-2.5 px-3 text-center font-sans">Status Perputaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($stockValuation['all_items'] as $it)
                        <tr class="hover:bg-slate-800/20 font-sans">
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-white">{{ $it['name'] }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $it['sku'] }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-400">{{ $it['category'] }}</td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-white">{{ $it['current_stock'] }} {{ $it['unit'] }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-300">{{ $business->currency_symbol }} {{ number_format($it['unit_cost'], 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-400">{{ $business->currency_symbol }} {{ number_format($it['valuation'], 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-center font-mono text-cyan-300">{{ $it['sold_30d_qty'] }} unit</td>
                            <td class="py-2.5 px-3 text-center">
                                @if($it['velocity'] === 'fast_moving')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Fast Moving</span>
                                @elseif($it['velocity'] === 'dead_stock')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Slow / Dead Stock</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400">Reguler</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: STRUKTUR HPP & BIAYA POKOK PRODUKSI -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'hpp'" x-cloak class="space-y-6">
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
                    <p class="text-[10px] text-slate-400 mt-1">Bahan baku pokok &amp; penolong</p>
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
                        <span class="text-xs font-bold text-amber-400 uppercase">Machine &amp; Utility</span>
                        <span class="text-xs font-mono font-extrabold text-amber-400">{{ number_format($costBreakdown['machine_percentage'], 1) }}%</span>
                    </div>
                    <div class="text-2xl font-extrabold text-white font-mono mt-2">
                        {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_machine_cost'], 0, ',', '.') }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Depresiasi mesin, listrik &amp; servis</p>
                </div>

                <div class="glass-card p-5 rounded-2xl border-emerald-500/20">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-400 uppercase">Overhead Pabrikasi</span>
                        <span class="text-xs font-mono font-extrabold text-emerald-400">{{ number_format($costBreakdown['overhead_percentage'], 1) }}%</span>
                    </div>
                    <div class="text-2xl font-extrabold text-white font-mono mt-2">
                        {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_overhead_cost'], 0, ',', '.') }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Beban fasilitas &amp; operasional teralokasi</p>
                </div>
            </div>
        </div>

        <!-- HPP per Product Table -->
        <div class="glass-card rounded-2xl overflow-hidden border border-slate-800">
            <div class="p-4 bg-slate-900/90 border-b border-slate-800 text-xs font-bold text-white">
                Tabel Analisis Modal HPP, Harga Jual &amp; Margin per Produk
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-950/60 font-mono text-[10px] uppercase font-bold">
                            <th class="py-3 px-4 font-sans">Produk &amp; SKU</th>
                            <th class="py-3 px-4 font-sans">Kategori</th>
                            <th class="py-3 px-4 text-right">Modal Bahan</th>
                            <th class="py-3 px-4 text-right">Labor &amp; Mesin</th>
                            <th class="py-3 px-4 text-right">Overhead</th>
                            <th class="py-3 px-4 text-right text-white">Total HPP / Unit</th>
                            <th class="py-3 px-4 text-right text-emerald-400">Harga Rekomendasi</th>
                            <th class="py-3 px-4 text-right text-cyan-300">Laba Kotor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @foreach($hppReport as $row)
                        <tr class="hover:bg-slate-800/30 transition font-sans">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $row['product_name'] }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $row['sku'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-400">{{ $row['category'] }}</td>
                            <td class="py-3 px-4 text-right font-mono">{{ $business->currency_symbol }} {{ number_format($row['material_cost'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono">{{ $business->currency_symbol }} {{ number_format($row['labor_cost'] + $row['machine_cost'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono">{{ $business->currency_symbol }} {{ number_format($row['overhead_cost'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-white bg-slate-900/40">
                                {{ $business->currency_symbol }} {{ number_format($row['hpp_per_unit'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400 bg-emerald-950/10">
                                {{ $business->currency_symbol }} {{ number_format($row['recommended_price'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-cyan-300">
                                {{ $business->currency_symbol }} {{ number_format($row['gross_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
