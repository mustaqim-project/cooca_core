{{-- TAB 1: OVERVIEW & EXECUTIVE SUMMARY --}}
<div class="space-y-6">
    {{-- KPI Cards Included --}}
    @include('app.pos.reports.partials.kpi_cards')

    {{-- Bento Grid Row 2: Sales Trend Chart + Goods vs Services Split --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Daily Sales & Profit Trend Chart (Span 2) --}}
        <div class="lg:col-span-2 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Tren Penjualan & Laba Harian</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Grafik omzet bersih dan perolehan laba kotor harian</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-medium">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span> Penjualan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span> Laba Kotor</span>
                </div>
            </div>

            <div class="relative h-[260px] w-full">
                <canvas id="posDailyTrendChart"></canvas>
            </div>
        </div>

        {{-- Goods vs Services Revenue Split (Span 1) --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">Distribusi Barang vs Jasa</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Komposisi pendapatan dari produk fisik dan layanan jasa</p>
            </div>

            <div class="space-y-4 my-auto py-4">
                {{-- Barang Fisik --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[13px]">
                        <span class="font-medium text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="package" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Produk Fisik (Barang)</span>
                        </span>
                        <span class="font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($kpi->goodsRevenue, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $goodsPct = $kpi->netSales > 0 ? round(($kpi->goodsRevenue / $kpi->netSales) * 100, 1) : 0;
                    @endphp
                    <div class="w-full h-2 bg-black/[0.04] dark:bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-[#007AFF] rounded-full" style="width: {{ $goodsPct }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-black/40 dark:text-white/40">
                        <span>{{ number_format($kpi->goodsQuantity, 1) }} qty terjual</span>
                        <span>{{ $goodsPct }}% porsi omzet</span>
                    </div>
                </div>

                {{-- Jasa Layanan --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[13px]">
                        <span class="font-medium text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="wrench" class="w-4 h-4 text-[#AF52DE]"></i>
                            <span>Jasa / Layanan Teknis</span>
                        </span>
                        <span class="font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($kpi->servicesRevenue, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $servicesPct = $kpi->netSales > 0 ? round(($kpi->servicesRevenue / $kpi->netSales) * 100, 1) : 0;
                    @endphp
                    <div class="w-full h-2 bg-black/[0.04] dark:bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-[#AF52DE] rounded-full" style="width: {{ $servicesPct }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-black/40 dark:text-white/40">
                        <span>{{ number_format($kpi->servicesQuantity, 1) }} order jasa</span>
                        <span>{{ $servicesPct }}% porsi omzet</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[12px]">
                <span class="text-black/50 dark:text-white/50">ASP (Harga Jual Rata-rata)</span>
                <span class="font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($kpi->averageSellingPrice, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Bento Grid Row 3: Top Products Fast View + Payment Methods Preview --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        {{-- Top 5 Products --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-black dark:text-white">5 Produk Terlaris</h3>
                <a href="{{ route('pos.reports.index', array_merge(request()->query(), ['tab' => 'products'])) }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($topProducts->take(5) as $idx => $prod)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-bold flex items-center justify-center text-black/60 dark:text-white/60 shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-[13px] font-semibold text-black dark:text-white truncate">{{ $prod->name }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40 truncate">{{ $prod->category ?? 'Produk' }} • {{ number_format($prod->qty, 1) }} terjual</div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[13px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($prod->sales, 0, ',', '.') }}</div>
                            <div class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158] tabular-nums">+Rp {{ number_format($prod->profit, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-[13px] text-black/40 dark:text-white/40">Belum ada transaksi pada periode ini</div>
                @endforelse
            </div>
        </div>

        {{-- Payment Methods Fast View --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-black dark:text-white">Metode Pembayaran</h3>
                <a href="{{ route('pos.reports.index', array_merge(request()->query(), ['tab' => 'payments'])) }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                    <span>Rincian Lengkap</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($paymentMethods as $pm)
                    <div class="py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <i data-lucide="credit-card" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                            </span>
                            <div>
                                <div class="text-[13px] font-semibold text-black dark:text-white">{{ $pm->method }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40">{{ number_format($pm->count, 0, ',', '.') }} transaksi</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[13px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($pm->amount, 0, ',', '.') }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">{{ number_format($pm->share, 1) }}% porsi</div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-[13px] text-black/40 dark:text-white/40">Belum ada data pembayaran</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('posDailyTrendChart');
    if (ctx) {
        const trendData = @json($dailyTrend);
        const labels = trendData.map(d => d.date);
        const sales = trendData.map(d => d.sales);
        const profit = trendData.map(d => d.profit);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan Bersih',
                        data: sales,
                        borderColor: '#007AFF',
                        backgroundColor: 'rgba(0, 122, 255, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 2.5,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Laba Kotor',
                        data: profit,
                        borderColor: '#34C759',
                        backgroundColor: 'rgba(52, 199, 89, 0.05)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 2.5,
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                return 'Rp ' + (value >= 1000000 ? (value / 1000000).toFixed(1) + 'M' : (value / 1000).toFixed(0) + 'k');
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
