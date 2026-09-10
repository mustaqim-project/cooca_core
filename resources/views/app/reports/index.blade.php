@extends('layouts.app', [
    'title' => 'Laporan & Analitik Bisnis',
    'headerTitle' => 'Laporan & Analitik Finansial',
    'headerSubtitle' => 'Pantau Laba Rugi riil, Arus Kas, Umur Piutang/Hutang, dan Valuasi Stok dari snapshot transaksi'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    activeTab: '{{ $activeTab }}',
    costBreakdown: {{ Js::from($costBreakdown) }},
    chartInstance: null,
    init() {
        this.initChart();
        const observer = new MutationObserver(() => {
            this.updateChartTheme();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },
    initChart() {
        this.$nextTick(() => {
            const ctx = document.getElementById('costDonutChart');
            if (ctx && typeof Chart !== 'undefined') {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#8E8E93' : '#3C3C43';
                
                if (this.chartInstance) {
                    this.chartInstance.destroy();
                }
                
                this.chartInstance = new Chart(ctx, {
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
                            backgroundColor: ['#007AFF', '#AF52DE', '#FF9500', '#34C759'],
                            borderWidth: 2,
                            borderColor: isDark ? '#1C1C1E' : '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    font: { size: 11, weight: '600' },
                                    padding: 12
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    },
    updateChartTheme() {
        if (this.chartInstance) {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#8E8E93' : '#3C3C43';
            this.chartInstance.options.plugins.legend.labels.color = textColor;
            if (this.chartInstance.data.datasets[0]) {
                this.chartInstance.data.datasets[0].borderColor = isDark ? '#1C1C1E' : '#ffffff';
            }
            this.chartInstance.update();
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. OFFICIAL PRINT LETTERHEAD (Print Only)             -->
    <!-- ===================================================== -->
    <div class="hidden print:block mb-6 border-b border-black/10 pb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-black uppercase tracking-wide">{{ $business->name }}</h1>
                <p class="text-xs text-black/60">Laporan Finansial Komprehensif &amp; Analitik Operasional ERP</p>
            </div>
            <div class="text-right text-xs text-black/60 tabular-nums">
                <p>Periode: <strong class="text-black">{{ $incomeStatement['period']['label'] ?? ($startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y')) }}</strong></p>
                <p class="text-[11px] text-black/40">Dicetak pada: {{ date('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 2. TOOLBAR & PRESET FILTER (macOS Sonoma Style)        -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 print:hidden">
        <!-- Date Preset Filter Form -->
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2.5">
            <input type="hidden" name="tab" :value="activeTab">
            
            <!-- Apple-Style Segmented Control for Presets -->
            <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                <button type="submit" name="preset" value="today"
                    class="px-3 py-1 rounded-[7px] transition-all {{ $preset === 'today' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                    Hari Ini
                </button>
                <button type="submit" name="preset" value="7days"
                    class="px-3 py-1 rounded-[7px] transition-all {{ $preset === '7days' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                    7 Hari
                </button>
                <button type="submit" name="preset" value="this_month"
                    class="px-3 py-1 rounded-[7px] transition-all {{ $preset === 'this_month' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                    Bulan Ini
                </button>
                <button type="submit" name="preset" value="this_year"
                    class="px-3 py-1 rounded-[7px] transition-all {{ $preset === 'this_year' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                    Tahun Ini
                </button>
            </div>

            <!-- Custom Date Range -->
            <div class="flex items-center gap-1.5 text-[12px]">
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}"
                    class="h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                <span class="text-black/40 dark:text-white/40 font-medium">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}"
                    class="h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                <button type="submit" class="h-8 w-8 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 active:scale-[0.97] transition-all flex items-center justify-center" title="Terapkan Filter Tanggal">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Export & Print Actions -->
        <div class="flex items-center gap-2">
            <!-- Export Excel (.xlsx) -->
            <a :href="'{{ route('reports.export-excel') }}?format=xlsx&start_date={{ $startDate->toDateString() }}&end_date={{ $endDate->toDateString() }}'"
                id="btnExportExcel"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Export Excel</span>
            </a>

            <!-- Tombol Cetak -->
            <button type="button" onclick="window.print()" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.37-2.2-.37-3.329 0-6.075 4.925-11 11-11s11 4.925 11 11c0 1.129-.13 2.252-.37 3.329M3.75 14.25h16.5M6 18h12m-9 3h6" />
                </svg>
                <span class="hidden sm:inline">Cetak</span>
            </button>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 3. NAVIGATION TABS (macOS Segmented Tab Bar)           -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-1.5 overflow-x-auto print:hidden">
        <div class="inline-flex p-0.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[13px] font-medium min-w-full sm:min-w-0">
            <button type="button" @click="activeTab = 'income_statement'"
                :class="activeTab === 'income_statement' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[8px] flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
                <span>1. Laba Rugi (P&amp;L)</span>
            </button>

            <button type="button" @click="activeTab = 'cash_flow'"
                :class="activeTab === 'cash_flow' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[8px] flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                </svg>
                <span>2. Arus Kas (Cash Flow)</span>
            </button>

            <button type="button" @click="activeTab = 'aging'"
                :class="activeTab === 'aging' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[8px] flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>3. Umur Piutang &amp; Hutang (AR/AP)</span>
            </button>

            <button type="button" @click="activeTab = 'stock_valuation'"
                :class="activeTab === 'stock_valuation' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[8px] flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-[#AF52DE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span>4. Valuasi &amp; Perputaran Stok</span>
            </button>

            <button type="button" @click="activeTab = 'hpp'"
                :class="activeTab === 'hpp' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                class="px-3.5 py-1.5 rounded-[8px] flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                </svg>
                <span>5. Struktur HPP &amp; Biaya</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: LABA RUGI RIIL (INCOME STATEMENT)                                  -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'income_statement'" class="space-y-6">
        <!-- Top KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Penjualan Bersih -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Penjualan Bersih</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                        {{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['net_sales'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">POS + Faktur - Retur &amp; Diskon</p>
                </div>
            </div>

            <!-- HPP (COGS) -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">HPP Barang Terjual (COGS)</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                        {{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['total_cogs'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Snapshot modal barang laku</p>
                </div>
            </div>

            <!-- Laba Kotor (Gross Profit) -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Laba Kotor (Gross Profit)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] tabular-nums">
                        {{ $incomeStatement['gross_profit']['margin'] }}%
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                        {{ $business->currency_symbol }} {{ number_format($incomeStatement['gross_profit']['amount'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Pendapatan Bersih - HPP</p>
                </div>
            </div>

            <!-- Laba Bersih Operasional -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Laba Bersih Operasional</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' }} tabular-nums">
                        {{ $incomeStatement['net_profit']['margin'] }}%
                    </span>
                </div>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                        {{ $business->currency_symbol }} {{ number_format($incomeStatement['net_profit']['amount'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Setelah dikurangi beban operasional</p>
                </div>
            </div>
        </div>

        <!-- Detailed Statement Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex justify-between items-center text-[13px]">
                <span class="font-semibold text-black dark:text-white">Rincian Laporan Laba Rugi</span>
                <span class="tabular-nums text-black/50 dark:text-white/50 text-[12px]">Periode: {{ $incomeStatement['period']['label'] }}</span>
            </div>

            <div class="p-5 sm:p-6 space-y-6 text-[13px]">
                <!-- Section 1: Pendapatan -->
                <div class="space-y-1.5">
                    <div class="font-semibold text-[#007AFF] dark:text-[#0A84FF] text-[11px] uppercase tracking-wide border-b border-black/5 dark:border-white/10 pb-1.5">
                        1. PENDAPATAN USAHA (REVENUES)
                    </div>
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">Penjualan Kasir POS (Gross)</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['pos_gross_sales'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">Penjualan Faktur Invoice (Gross)</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['invoice_gross_sales'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-[#FF3B30] dark:text-[#FF453A]">
                        <span class="pl-2">(-) Potongan Diskon Penjualan &amp; Voucher</span>
                        <span class="tabular-nums font-medium">({{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['total_discounts'], 0, ',', '.') }})</span>
                    </div>
                    <div class="flex justify-between py-1 text-[#FF3B30] dark:text-[#FF453A]">
                        <span class="pl-2">(-) Retur Penjualan &amp; Pengembalian</span>
                        <span class="tabular-nums font-medium">({{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['sales_returns'], 0, ',', '.') }})</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-black/5 dark:border-white/10 font-semibold text-black dark:text-white bg-black/[0.02] dark:bg-white/[0.03] p-2.5 rounded-[10px]">
                        <span>TOTAL PENDAPATAN BERSIH (NET REVENUE)</span>
                        <span class="tabular-nums text-[#34C759] dark:text-[#30D158] font-bold">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['net_sales'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Section 1b: Pajak yang Dipungut -->
                <div class="space-y-1.5">
                    <div class="font-semibold text-[#FF9500] dark:text-[#FF9F0A] text-[11px] uppercase tracking-wide border-b border-black/5 dark:border-white/10 pb-1.5">
                        PAJAK YANG DIPUNGUT (PPN / TAX)
                    </div>
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">Pajak PPN Transaksi Kasir POS</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['pos_tax'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">Pajak PPN Faktur Invoice</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['invoice_tax'], 0, ',', '.') }}</span>
                    </div>
                    @if(!empty($incomeStatement['revenues']['pos_service_fee']) && $incomeStatement['revenues']['pos_service_fee'] > 0)
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">Biaya Layanan (Service Charge) Kasir POS</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['pos_service_fee'], 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-black/5 dark:border-white/10 font-semibold text-black dark:text-white bg-black/[0.02] dark:bg-white/[0.03] p-2.5 rounded-[10px]">
                        <span>TOTAL PAJAK DIPUNGUT</span>
                        <span class="tabular-nums text-[#FF9500] dark:text-[#FF9F0A] font-bold">{{ $business->currency_symbol }} {{ number_format($incomeStatement['revenues']['total_tax'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Section 2: HPP -->
                <div class="space-y-1.5">
                    <div class="font-semibold text-[#FF9500] dark:text-[#FF9F0A] text-[11px] uppercase tracking-wide border-b border-black/5 dark:border-white/10 pb-1.5">
                        2. HARGA POKOK PENJUALAN (COGS)
                    </div>
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">HPP Transaksi Kasir POS</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['pos_cogs'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">HPP Penjualan Faktur Invoice</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['invoice_cogs'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-[#34C759] dark:text-[#30D158]">
                        <span class="pl-2">(-) Pemulihan HPP dari Retur Penjualan</span>
                        <span class="tabular-nums font-medium">({{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['returns_cogs_recovery'], 0, ',', '.') }})</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-black/5 dark:border-white/10 font-semibold text-black dark:text-white bg-black/[0.02] dark:bg-white/[0.03] p-2.5 rounded-[10px]">
                        <span>TOTAL HPP BARANG TERJUAL</span>
                        <span class="tabular-nums text-[#FF9500] dark:text-[#FF9F0A] font-bold">{{ $business->currency_symbol }} {{ number_format($incomeStatement['cogs']['total_cogs'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Gross Profit Callout -->
                <div class="flex justify-between p-3.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 text-black dark:text-white font-medium">
                    <div class="flex items-center gap-2">
                        <span class="text-[#007AFF] dark:text-[#0A84FF] text-[14px] font-bold">LABA KOTOR (GROSS PROFIT)</span>
                        <span class="text-[11px] text-black/50 dark:text-white/50">Margin: {{ $incomeStatement['gross_profit']['margin'] }}%</span>
                    </div>
                    <span class="tabular-nums text-[#007AFF] dark:text-[#0A84FF] text-[16px] font-bold">{{ $business->currency_symbol }} {{ number_format($incomeStatement['gross_profit']['amount'], 0, ',', '.') }}</span>
                </div>

                <!-- Section 3: Beban Operasional -->
                <div class="space-y-1.5">
                    <div class="font-semibold text-[#AF52DE] dark:text-[#BF5AF2] text-[11px] uppercase tracking-wide border-b border-black/5 dark:border-white/10 pb-1.5">
                        3. BEBAN OPERASIONAL (OPERATING EXPENSES)
                    </div>
                    @forelse($incomeStatement['expenses']['by_category'] as $catName => $catAmount)
                    <div class="flex justify-between py-1 text-black/70 dark:text-white/70">
                        <span class="pl-2">Beban {{ $catName }}</span>
                        <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($catAmount, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-black/40 dark:text-white/40 py-1 pl-2 italic">Tidak ada pencatatan beban operasional pada periode ini.</div>
                    @endforelse
                    <div class="flex justify-between pt-2 border-t border-black/5 dark:border-white/10 font-semibold text-black dark:text-white bg-black/[0.02] dark:bg-white/[0.03] p-2.5 rounded-[10px]">
                        <span>TOTAL BEBAN OPERASIONAL</span>
                        <span class="tabular-nums text-[#AF52DE] dark:text-[#BF5AF2] font-bold">{{ $business->currency_symbol }} {{ number_format($incomeStatement['expenses']['total'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Net Profit Callout -->
                <div class="flex justify-between p-4 rounded-[10px] border font-semibold {{ $incomeStatement['net_profit']['amount'] >= 0 ? 'bg-[#34C759]/8 border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/8 border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A]' }}">
                    <div class="flex flex-col">
                        <span class="text-[14px] font-bold">LABA BERSIH OPERASIONAL (NET PROFIT)</span>
                        <span class="text-[11px] opacity-75 font-normal mt-0.5">Margin Bersih Akhir: {{ $incomeStatement['net_profit']['margin'] }}%</span>
                    </div>
                    <span class="tabular-nums text-[18px] font-bold">{{ $business->currency_symbol }} {{ number_format($incomeStatement['net_profit']['amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: ARUS KAS (CASH FLOW STATEMENT)                                     -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'cash_flow'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kas Masuk (Inflow)</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                        {{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['total'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Kasir + Pelunasan Piutang + Kas Masuk</p>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kas Keluar (Outflow)</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                        {{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['total'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Bayar Tagihan + Beban + Refund</p>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Arus Kas Bersih (Net Cash)</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums {{ $cashFlow['net_cash_flow'] >= 0 ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                        {{ $business->currency_symbol }} {{ number_format($cashFlow['net_cash_flow'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Selisih Aliran Kas Masuk - Keluar</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Inflows Breakdown -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
                <h4 class="text-[13px] font-semibold text-[#34C759] dark:text-[#30D158] uppercase tracking-wide flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25" />
                    </svg>
                    <span>Rincian Penerimaan Kas (Inflows)</span>
                </h4>
                <div class="space-y-2 text-[13px]">
                    <div class="flex justify-between py-2 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/70 dark:text-white/70">Penerimaan Kasir POS</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['pos_payments'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/70 dark:text-white/70">Pelunasan Piutang Invoice</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['invoice_payments'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-black/70 dark:text-white/70">Pemasukan Kas Non-Penjualan</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['inflows']['direct_cash_in'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Outflows Breakdown -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
                <h4 class="text-[13px] font-semibold text-[#FF3B30] dark:text-[#FF453A] uppercase tracking-wide flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                    </svg>
                    <span>Rincian Pengeluaran Kas (Outflows)</span>
                </h4>
                <div class="space-y-2 text-[13px]">
                    <div class="flex justify-between py-2 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/70 dark:text-white/70">Pelunasan Tagihan Supplier (AP)</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['supplier_payments'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/70 dark:text-white/70">Pengeluaran Beban Operasional</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['expenses'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-black/5 dark:border-white/5">
                        <span class="text-black/70 dark:text-white/70">Pengembalian Refund Kas Retur</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['cash_refunds'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-black/70 dark:text-white/70">Pengeluaran Kas Langsung</span>
                        <span class="font-medium tabular-nums text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($cashFlow['outflows']['direct_cash_out'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: UMUR PIUTANG (AR) & HUTANG (AP) AGING                              -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'aging'" x-cloak class="space-y-6">
        <!-- 3.1 AR Aging (Piutang Pelanggan) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden space-y-4 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-[14px] font-semibold text-black dark:text-white">Analisis Umur Piutang Pelanggan (AR Aging)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Klasifikasi invoice yang belum dibayar berdasarkan keterlambatan jatuh tempo.</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[11px] text-black/50 dark:text-white/50 font-medium">Total Piutang Beredar:</span>
                    <div class="text-[18px] font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['total_balance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- AR Aging Buckets Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 sm:gap-3 text-[12px]">
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Belum Jatuh Tempo</span>
                    <div class="text-[15px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['current'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50">1 - 30 Hari</span>
                    <div class="text-[15px] font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['1_30'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50">31 - 60 Hari</span>
                    <div class="text-[15px] font-bold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['31_60'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <span class="text-[11px] font-medium text-black/50 dark:text-white/50">61 - 90 Hari</span>
                    <div class="text-[15px] font-bold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['61_90'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-[10px] bg-[#FF3B30]/8 border border-[#FF3B30]/20 col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-medium text-[#FF3B30] dark:text-[#FF453A]">&gt; 90 Hari (Macet)</span>
                    <div class="text-[15px] font-bold text-[#FF3B30] dark:text-[#FF453A] tabular-nums mt-1">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ar']['buckets']['over_90'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- AR Details Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Invoice</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pelanggan</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Tgl Faktur</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Jatuh Tempo</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Keterlambatan</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Sisa Piutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($agingSummary['ar']['details'] as $ar)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-3 py-2.5 font-medium text-black dark:text-white">{{ $ar['invoice_number'] }}</td>
                            <td class="px-3 py-2.5 text-black/80 dark:text-white/80">{{ $ar['customer_name'] }}</td>
                            <td class="px-3 py-2.5 text-center text-black/60 dark:text-white/60 tabular-nums">{{ $ar['invoice_date'] }}</td>
                            <td class="px-3 py-2.5 text-center text-black/60 dark:text-white/60 tabular-nums">{{ $ar['due_date'] }}</td>
                            <td class="px-3 py-2.5 text-center">
                                @if($ar['days_overdue'] > 60)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] tabular-nums">
                                        {{ $ar['days_overdue'] }} Hari
                                    </span>
                                @elseif($ar['days_overdue'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] tabular-nums">
                                        {{ $ar['days_overdue'] }} Hari
                                    </span>
                                @else
                                    <span class="text-black/40 dark:text-white/40 text-[11px]">Belum Jatuh Tempo</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right font-semibold tabular-nums text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format($ar['balance_due'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-black/40 dark:text-white/40 text-[13px] italic">Tidak ada piutang invoice yang belum lunas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3.2 AP Aging (Hutang Supplier) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden space-y-4 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-[14px] font-semibold text-black dark:text-white">Analisis Umur Hutang Vendor (AP Aging)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Klasifikasi tagihan supplier yang belum dibayar berdasarkan keterlambatan jatuh tempo.</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[11px] text-black/50 dark:text-white/50 font-medium">Total Hutang Beredar:</span>
                    <div class="text-[18px] font-bold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">
                        {{ $business->currency_symbol }} {{ number_format($agingSummary['ap']['total_balance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- AP Details Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Tagihan Vendor</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Supplier / Vendor</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Tgl Tagihan</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Jatuh Tempo</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Keterlambatan</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Sisa Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($agingSummary['ap']['details'] as $ap)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-3 py-2.5 font-medium text-black dark:text-white">{{ $ap['invoice_number'] }}</td>
                            <td class="px-3 py-2.5 text-black/80 dark:text-white/80">{{ $ap['supplier_name'] }}</td>
                            <td class="px-3 py-2.5 text-center text-black/60 dark:text-white/60 tabular-nums">{{ $ap['invoice_date'] }}</td>
                            <td class="px-3 py-2.5 text-center text-black/60 dark:text-white/60 tabular-nums">{{ $ap['due_date'] }}</td>
                            <td class="px-3 py-2.5 text-center">
                                @if($ap['days_overdue'] > 30)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] tabular-nums">
                                        {{ $ap['days_overdue'] }} Hari
                                    </span>
                                @elseif($ap['days_overdue'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] tabular-nums">
                                        {{ $ap['days_overdue'] }} Hari
                                    </span>
                                @else
                                    <span class="text-black/40 dark:text-white/40 text-[11px]">Belum Jatuh Tempo</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right font-semibold tabular-nums text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format($ap['balance_due'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-black/40 dark:text-white/40 text-[13px] italic">Tidak ada tagihan hutang supplier yang belum lunas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: VALUASI & PERPUTARAN STOK                                          -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'stock_valuation'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Nilai Valuasi Persediaan</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                        {{ $business->currency_symbol }} {{ number_format($stockValuation['summary']['total_valuation'], 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Total Aset Fisik Stok &times; WAC Modal</p>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Kuantitas Fisik</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                        {{ number_format($stockValuation['summary']['total_physical_units'], 0, ',', '.') }} <span class="text-[13px] font-normal text-black/40 dark:text-white/40">unit</span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $stockValuation['summary']['total_products'] }} variasi produk aktif</p>
                </div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Perputaran Cepat (Fast Moving)</span>
                <div class="mt-2">
                    <div class="text-[22px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">
                        {{ count($stockValuation['fast_moving']) }} <span class="text-[13px] font-normal text-black/40 dark:text-white/40">produk teratas</span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Paling laku dalam 30 hari terakhir</p>
                </div>
            </div>
        </div>

        <!-- Valuation Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white flex justify-between items-center">
                <span>Daftar Nilai Valuasi Stok per Produk</span>
                <span class="tabular-nums text-[12px] font-normal text-black/50 dark:text-white/50">Total: {{ count($stockValuation['all_items']) }} Produk</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk &amp; SKU</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kategori</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Stok Saat Ini</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Modal Rata-rata (WAC)</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Nilai Valuasi</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Penjualan 30 Hari</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status Perputaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($stockValuation['all_items'] as $it)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-3 py-2.5">
                                <div class="font-medium text-black dark:text-white">{{ $it['name'] }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">{{ $it['sku'] }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-black/60 dark:text-white/60">{{ $it['category'] }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums font-semibold text-black dark:text-white">{{ $it['current_stock'] }} {{ $it['unit'] }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums text-black/60 dark:text-white/60">{{ $business->currency_symbol }} {{ number_format($it['unit_cost'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format($it['valuation'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums text-[#007AFF] dark:text-[#0A84FF] font-medium">{{ $it['sold_30d_qty'] }} unit</td>
                            <td class="px-3 py-2.5 text-center">
                                @if($it['velocity'] === 'fast_moving')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                        Fast Moving
                                    </span>
                                @elseif($it['velocity'] === 'dead_stock')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                        Slow / Dead Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                        Reguler
                                    </span>
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
    <!-- TAB 5: STRUKTUR HPP & BIAYA POKOK PRODUKSI                                -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'hpp'" x-cloak class="space-y-6">
        <!-- Top Aggregate Summary Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Donut Chart Card -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white mb-0.5">Struktur Biaya Komposit</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Komposisi total biaya terakumulasi seluruh lini produk</p>
                </div>
                <div class="h-56 relative my-4 flex items-center justify-center">
                    <canvas id="costDonutChart"></canvas>
                </div>
                <div class="text-center text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                    Total HPP Terakumulasi: <strong class="text-black dark:text-white font-semibold">{{ $business->currency_symbol }} {{ number_format((float)$costBreakdown['total_hpp'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <!-- Metric Details Cards (2 Cols) -->
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] uppercase">Direct Material</span>
                        <span class="text-[12px] tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($costBreakdown['material_percentage'], 1) }}%</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[22px] font-bold text-black dark:text-white tabular-nums">
                            {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_material_cost'], 0, ',', '.') }}
                        </div>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Bahan baku pokok &amp; penolong</p>
                    </div>
                </div>

                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-medium text-[#AF52DE] dark:text-[#BF5AF2] uppercase">Direct Labor</span>
                        <span class="text-[12px] tabular-nums font-bold text-[#AF52DE] dark:text-[#BF5AF2]">{{ number_format($costBreakdown['labor_percentage'], 1) }}%</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[22px] font-bold text-black dark:text-white tabular-nums">
                            {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_labor_cost'], 0, ',', '.') }}
                        </div>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Tenaga kerja produksi langsung</p>
                    </div>
                </div>

                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-medium text-[#FF9500] dark:text-[#FF9F0A] uppercase">Machine &amp; Utility</span>
                        <span class="text-[12px] tabular-nums font-bold text-[#FF9500] dark:text-[#FF9F0A]">{{ number_format($costBreakdown['machine_percentage'], 1) }}%</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[22px] font-bold text-black dark:text-white tabular-nums">
                            {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_machine_cost'], 0, ',', '.') }}
                        </div>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Depresiasi mesin, listrik &amp; servis</p>
                    </div>
                </div>

                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158] uppercase">Overhead Pabrikasi</span>
                        <span class="text-[12px] tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">{{ number_format($costBreakdown['overhead_percentage'], 1) }}%</span>
                    </div>
                    <div class="mt-2">
                        <div class="text-[22px] font-bold text-black dark:text-white tabular-nums">
                            {{ $business->currency_symbol }} {{ number_format($costBreakdown['total_overhead_cost'], 0, ',', '.') }}
                        </div>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Beban fasilitas &amp; operasional teralokasi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- HPP per Product Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white flex justify-between items-center">
                <span>Tabel Analisis Modal HPP, Harga Jual &amp; Margin per Produk</span>
                <span class="tabular-nums text-[12px] font-normal text-black/50 dark:text-white/50">Total: {{ count($hppReport) }} Produk</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk &amp; SKU</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kategori</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Modal Bahan</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Labor &amp; Mesin</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Overhead</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right text-black dark:text-white">Total HPP / Unit</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right text-[#34C759] dark:text-[#30D158]">Harga Rekomendasi</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right text-[#007AFF] dark:text-[#0A84FF]">Laba Kotor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($hppReport as $row)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-black dark:text-white">{{ $row['product_name'] }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">{{ $row['sku'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-black/60 dark:text-white/60">{{ $row['category'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-black/70 dark:text-white/70">{{ $business->currency_symbol }} {{ number_format($row['material_cost'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-black/70 dark:text-white/70">{{ $business->currency_symbol }} {{ number_format($row['labor_cost'] + $row['machine_cost'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-black/70 dark:text-white/70">{{ $business->currency_symbol }} {{ number_format($row['overhead_cost'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white bg-black/[0.02] dark:bg-white/[0.02]">
                                {{ $business->currency_symbol }} {{ number_format($row['hpp_per_unit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-bold text-[#34C759] dark:text-[#30D158]">
                                {{ $business->currency_symbol }} {{ number_format($row['recommended_price'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF]">
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
