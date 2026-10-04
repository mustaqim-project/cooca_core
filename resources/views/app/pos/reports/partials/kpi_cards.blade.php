{{-- BENTO APPLE HIG KPI SUMMARY CARDS --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
    <!-- Tile 1: Total Omzet Bersih (Net Sales) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Penjualan Bersih</span>
            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
        </div>
        <div class="mt-3">
            <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white">
                Rp {{ number_format($kpi->netSales, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium">
                @if ($kpi->salesGrowthPercent !== null)
                    <span class="{{ $kpi->salesGrowthPercent >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }} tabular-nums flex items-center gap-0.5 font-semibold">
                        <i data-lucide="{{ $kpi->salesGrowthPercent >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                        {{ $kpi->salesGrowthPercent >= 0 ? '+' : '' }}{{ number_format($kpi->salesGrowthPercent, 1) }}%
                    </span>
                    <span class="text-black/40 dark:text-white/40">vs lalu</span>
                @else
                    <span class="text-black/40 dark:text-white/40">Hari Ini: Rp {{ number_format($kpi->todayRevenue, 0, ',', '.') }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Tile 2: Total HPP Modal (COGS) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total HPP (Modal)</span>
            <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span>
        </div>
        <div class="mt-3">
            <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                Rp {{ number_format($kpi->totalHpp, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1 tabular-nums truncate">
                Rata-rata modal: Rp {{ number_format($kpi->averageCostPrice, 0, ',', '.') }}/item
            </div>
        </div>
    </div>

    <!-- Tile 3: Gross Profit & Margin -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Laba Kotor (Gross Profit)</span>
            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
        </div>
        <div class="mt-3">
            <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                Rp {{ number_format($kpi->grossProfit, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-1 text-[11px]">
                <span class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums">Margin: {{ number_format($kpi->grossMarginPercent, 1) }}%</span>
                @if ($kpi->profitGrowthPercent !== null)
                    <span class="{{ $kpi->profitGrowthPercent >= 0 ? 'text-[#34C759]' : 'text-[#FF3B30]' }} font-semibold text-[10px] tabular-nums">
                        ({{ $kpi->profitGrowthPercent >= 0 ? '+' : '' }}{{ number_format($kpi->profitGrowthPercent, 1) }}%)
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Tile 4: Total Transaksi & AOV -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Transaksi & AOV</span>
            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
        </div>
        <div class="mt-3">
            <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#007AFF]">
                {{ number_format($kpi->totalOrders, 0, ',', '.') }} <span class="text-[14px] font-normal text-black/40 dark:text-white/40">nota</span>
            </div>
            <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 tabular-nums truncate font-medium">
                AOV: Rp {{ number_format($kpi->averageOrderValue, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Tile 5: Total Diskon & Potongan -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Diskon</span>
            <span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span>
        </div>
        <div class="mt-3">
            <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                Rp {{ number_format($kpi->totalDiscount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1 tabular-nums truncate">
                Voucher: Rp {{ number_format($kpi->voucherDiscount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Tile 6: Pajak & Layanan (Tax & Service) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Pajak & Service</span>
            <span class="w-2 h-2 rounded-full bg-[#AF52DE]"></span>
        </div>
        <div class="mt-3">
            <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#AF52DE]">
                Rp {{ number_format($kpi->taxAmount + $kpi->serviceChargeAmount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1 tabular-nums truncate">
                PPN: Rp {{ number_format($kpi->taxAmount, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
