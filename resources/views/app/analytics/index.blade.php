@extends('layouts.app', [
    'title' => 'Analitik Bisnis & Tren - Cooca',
    'headerTitle' => 'Analitik Bisnis & Tren',
    'headerSubtitle' => 'Visualisasi performa penjualan, laba kotor riil, dan jam sibuk kasir.'
])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    period: '{{ $analytics['period'] }}',
    from: '{{ $analytics['from'] }}',
    to: '{{ $analytics['to'] }}',
    customOpen: {{ $analytics['period'] === 'custom' ? 'true' : 'false' }},
    locationId: '{{ $selectedLocationId ?? '' }}',
    trendData: {{ Js::from($analytics['trend']) }},
    hourlyData: {{ Js::from($analytics['hourly']) }},
    paymentData: {{ Js::from($analytics['payment_methods']) }},
    chartTrend: null,
    chartHourly: null,
    chartPayment: null,

    init() {
        this.$nextTick(() => {
            this.initCharts();
        });

        const observer = new MutationObserver(() => {
            this.updateChartTheme();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    },

    selectPeriod(p) {
        this.period = p;
        if (p !== 'custom') {
            document.getElementById('form-analytics-filter').submit();
        } else {
            this.customOpen = true;
        }
    },

    initCharts() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#8E8E93' : '#3C3C43';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.05)';

        // 1. Trend Line Chart
        const ctxTrend = document.getElementById('analyticsTrendChart');
        if (ctxTrend && typeof Chart !== 'undefined') {
            if (this.chartTrend) this.chartTrend.destroy();
            this.chartTrend = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: this.trendData.labels,
                    datasets: [
                        {
                            label: 'Omzet Penjualan',
                            data: this.trendData.revenue,
                            borderColor: '#007AFF',
                            backgroundColor: isDark ? 'rgba(10, 132, 255, 0.15)' : 'rgba(0, 122, 255, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'Laba Kotor',
                            data: this.trendData.profit,
                            borderColor: '#34C759',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: { color: textColor, font: { size: 11, weight: '600' }, boxWidth: 12 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: gridColor },
                            ticks: { color: textColor, font: { size: 10 } }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { size: 10 },
                                callback: function(value) {
                                    if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'M';
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'Jt';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'Rb';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Hourly Peak Hours Chart
        const ctxHourly = document.getElementById('analyticsHourlyChart');
        if (ctxHourly && typeof Chart !== 'undefined') {
            if (this.chartHourly) this.chartHourly.destroy();
            const labels = this.hourlyData.map(d => d.hour_label);
            const counts = this.hourlyData.map(d => d.count);
            this.chartHourly = new Chart(ctxHourly, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Frekuensi Transaksi',
                        data: counts,
                        backgroundColor: '#AF52DE',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' Transaksi';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor, font: { size: 9 }, maxRotation: 45 }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: { color: textColor, font: { size: 10 }, stepSize: 1 }
                        }
                    }
                }
            });
        }

        // 3. Payment Methods Donut Chart
        const ctxPayment = document.getElementById('analyticsPaymentChart');
        if (ctxPayment && typeof Chart !== 'undefined') {
            if (this.chartPayment) this.chartPayment.destroy();
            const pLabels = this.paymentData.map(p => p.label);
            const pTotals = this.paymentData.map(p => p.total);
            const colors = ['#007AFF', '#34C759', '#FF9500', '#AF52DE', '#FF2D55', '#5856D6'];

            this.chartPayment = new Chart(ctxPayment, {
                type: 'doughnut',
                data: {
                    labels: pLabels.length ? pLabels : ['Belum Ada Transaksi'],
                    datasets: [{
                        data: pTotals.length ? pTotals : [1],
                        backgroundColor: pTotals.length ? colors.slice(0, pTotals.length) : ['#E5E5EA'],
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
                            labels: { color: textColor, font: { size: 11, weight: '500' }, padding: 10 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (!pTotals.length) return 'Belum ada data';
                                    return context.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed);
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    },

    updateChartTheme() {
        if (this.chartTrend) this.initCharts();
    }
}">

    <!-- ======================================================= -->
    <!-- 1. HEADER CONTROLS: PERIOD & LOCATION FILTER           -->
    <!-- ======================================================= -->
    <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Laporan Eksekutif Bisnis
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-[11px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </span>
                    <span>Analitik Bisnis &amp; Tren Pertumbuhan</span>
                </h1>
                <p class="text-xs text-black/55 dark:text-white/55">
                    Periode: <strong class="text-black dark:text-white">{{ $analytics['period_label'] }}</strong> ({{ Carbon\Carbon::parse($analytics['from'])->isoFormat('D MMMM Y') }} &rarr; {{ Carbon\Carbon::parse($analytics['to'])->isoFormat('D MMMM Y') }})
                </p>
            </div>

            <!-- Filter Actions Form -->
            <form id="form-analytics-filter" method="GET" action="{{ route('analytics.index') }}" class="flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="period" :value="period">

                <!-- Location Selector (Cabang/Outlet) -->
                @if($locations->count() > 1)
                    <div class="relative">
                        <select name="location_id" onchange="document.getElementById('form-analytics-filter').submit()"
                            class="h-10 pl-8 pr-8 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-[16px] sm:text-xs font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/40 transition appearance-none cursor-pointer">
                            <option value="">Semua Cabang / Outlet</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $selectedLocationId === $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->type === 'outlet' ? 'Cabang' : 'Gudang' }})
                                </option>
                            @endforeach
                        </select>
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 absolute left-2.5 top-3 pointer-events-none"></i>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 absolute right-2.5 top-3 pointer-events-none"></i>
                    </div>
                @endif

                <!-- Apple Segmented Control Group for Periods -->
                <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-xs font-semibold">
                    <button type="button" @click="selectPeriod('today')"
                        :class="period === 'today' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition-all active:scale-[0.98]">Hari Ini</button>
                    <button type="button" @click="selectPeriod('week')"
                        :class="period === 'week' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition-all active:scale-[0.98]">Minggu Ini</button>
                    <button type="button" @click="selectPeriod('month')"
                        :class="period === 'month' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition-all active:scale-[0.98]">Bulan Ini</button>
                    <button type="button" @click="selectPeriod('year')"
                        :class="period === 'year' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition-all active:scale-[0.98]">Tahun Ini</button>
                    <button type="button" @click="selectPeriod('custom')"
                        :class="period === 'custom' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-[9px] transition-all active:scale-[0.98]">Kustom</button>
                </div>

                <!-- Link to Formal Financial Reports -->
                <a href="{{ route('reports.index') }}"
                    class="h-10 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/75 dark:text-white/75 text-xs font-semibold transition-all inline-flex items-center gap-1.5 active:scale-[0.98]">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Laporan SAK EMKM</span>
                </a>
            </form>
        </div>

        <!-- Custom Date Range Picker Accordion -->
        <div x-show="customOpen" x-transition class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
            <form method="GET" action="{{ route('analytics.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="period" value="custom">
                @if($selectedLocationId)
                    <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
                @endif
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-black/60 dark:text-white/60">Dari:</span>
                    <input type="date" name="from" value="{{ $analytics['from'] }}" required
                        class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-black/60 dark:text-white/60">Sampai:</span>
                    <input type="date" name="to" value="{{ $analytics['to'] }}" required
                        class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                </div>
                <button type="submit" class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold transition-all shadow-sm active:scale-[0.98]">
                    Terapkan Rentang
                </button>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. BENTO KPIS: REVENUE, ORDERS, MARGIN, NET PROFIT     -->
    <!-- ======================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Omzet -->
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Omzet Penjualan</span>
                <span class="w-8 h-8 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    Rp {{ number_format($analytics['current']['revenue'], 0, ',', '.') }}
                </div>
                <div class="mt-1.5 flex items-center gap-1.5 text-xs">
                    @php $gRev = $analytics['growth']['revenue']; @endphp
                    @if($gRev !== null)
                        <span class="font-bold {{ $gRev >= 0 ? 'text-[#34C759]' : 'text-[#FF3B30]' }}">
                            {{ $gRev >= 0 ? '+' : '' }}{{ $gRev }}%
                        </span>
                        <span class="text-black/40 dark:text-white/40">vs periode lalu</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 2: Total Transaksi & AOV -->
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Pesanan / Transaksi</span>
                <span class="w-8 h-8 rounded-[9px] bg-[#AF52DE]/10 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2] flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ number_format($analytics['current']['orders_count'], 0, ',', '.') }}
                </div>
                <div class="mt-1.5 flex items-center gap-1.5 text-xs">
                    <span class="text-black/50 dark:text-white/50">Rata-rata Keranjang:</span>
                    <strong class="text-black dark:text-white tabular-nums font-semibold">Rp {{ number_format($analytics['current']['avg_order_value'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Card 3: Laba Kotor & Margin -->
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Laba Kotor (Gross Profit)</span>
                <span class="w-8 h-8 rounded-[9px] bg-[#34C759]/10 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    Rp {{ number_format($analytics['current']['gross_profit'], 0, ',', '.') }}
                </div>
                <div class="mt-1.5 flex items-center gap-1.5 text-xs">
                    <span class="text-black/50 dark:text-white/50">Gross Margin:</span>
                    <span class="font-bold text-[#34C759] tabular-nums">{{ $analytics['current']['gross_margin_pct'] }}%</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Estimasi Laba Bersih -->
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Estimasi Laba Bersih</span>
                <span class="w-8 h-8 rounded-[9px] bg-[#FF9500]/10 text-[#FF9500] dark:bg-[#FF9F0A]/20 dark:text-[#FF9F0A] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </span>
            </div>
            <div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight {{ $analytics['current']['net_profit'] >= 0 ? 'text-[#34C759]' : 'text-[#FF3B30]' }} tabular-nums">
                    Rp {{ number_format($analytics['current']['net_profit'], 0, ',', '.') }}
                </div>
                <div class="mt-1.5 flex items-center gap-1.5 text-xs">
                    <span class="text-black/50 dark:text-white/50">Beban Operasional:</span>
                    <span class="font-medium text-[#FF3B30] tabular-nums">Rp {{ number_format($analytics['current']['expenses'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 3. CHARTS ROW: TREND LINE & HOURLY PEAK HEATMAP        -->
    <!-- ======================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Tren Penjualan & Laba Kotor -->
        <div class="lg:col-span-2 rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Tren Omzet &amp; Pertumbuhan Laba</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Pergerakan kurva pendapatan vs margin laba kotor riil</p>
                </div>
            </div>
            <div class="h-[280px] w-full">
                <canvas id="analyticsTrendChart"></canvas>
            </div>
        </div>

        <!-- Distribusi Metode Pembayaran -->
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-bold text-black dark:text-white">Metode Pembayaran</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Porsi transaksi QRIS, Tunai, Bank &amp; Kasbon</p>
            </div>
            <div class="h-[280px] w-full flex items-center justify-center">
                <canvas id="analyticsPaymentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. JAM SIBUK KASIR & TOP 10 PRODUK                     -->
    <!-- ======================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Jam Sibuk Kasir (Hourly Peak Hours Heatmap) -->
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Jam Sibuk Kasir</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Frekuensi antrean order per jam (07:00 - 23:00)</p>
                </div>
            </div>
            <div class="h-[280px] w-full">
                <canvas id="analyticsHourlyChart"></canvas>
            </div>
        </div>

        <!-- Top 10 Produk Terlaris & Margin -->
        <div class="lg:col-span-2 rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Top 10 Produk Terlaris</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Kuantitas terjual, omzet, dan kontribusi margin per item</p>
                </div>
                <a href="{{ route('reports.index', ['tab' => 'stock']) }}" class="text-xs text-[#007AFF] hover:underline font-semibold flex items-center gap-1">
                    <span>Valuasi Stok</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Desktop View: Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/50 dark:text-white/50 font-bold uppercase tracking-wider">
                            <th class="py-3 px-3">#</th>
                            <th class="py-3 px-3">Nama Menu / Produk</th>
                            <th class="py-3 px-3 text-right">Terjual</th>
                            <th class="py-3 px-3 text-right">Total Penjualan</th>
                            <th class="py-3 px-3 text-right">Laba Kotor</th>
                            <th class="py-3 px-3 text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] text-black dark:text-white">
                        @forelse($analytics['top_products'] as $idx => $prod)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-2.5 px-3 text-black/40 dark:text-white/40 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-semibold">{{ $prod['name'] }}</td>
                                <td class="py-2.5 px-3 text-right tabular-nums font-medium">{{ number_format($prod['quantity'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-right tabular-nums">Rp {{ number_format($prod['total_sales'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-right tabular-nums text-[#34C759] font-medium">Rp {{ number_format($prod['gross_profit'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $prod['margin_pct'] >= 40 ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF9500]/15 text-[#FF9500]' }} tabular-nums">
                                        {{ $prod['margin_pct'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-black/40 dark:text-white/40">
                                    Belum ada data penjualan pada rentang periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Card List Tiles -->
            <div class="block md:hidden space-y-3">
                @forelse($analytics['top_products'] as $idx => $prod)
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 text-[10px] font-bold flex items-center justify-center text-black/60 dark:text-white/60 shrink-0">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="font-bold text-xs text-black dark:text-white">{{ $prod['name'] }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $prod['margin_pct'] >= 40 ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF9500]/15 text-[#FF9500]' }} tabular-nums shrink-0">
                                {{ $prod['margin_pct'] }}%
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-[11px] pt-1 border-t border-black/5 dark:border-white/5">
                            <div>
                                <span class="text-black/50 dark:text-white/50 block">Terjual:</span>
                                <strong class="text-black dark:text-white tabular-nums">{{ number_format($prod['quantity'], 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-black/50 dark:text-white/50 block">Omzet:</span>
                                <strong class="text-black dark:text-white tabular-nums">Rp {{ number_format($prod['total_sales'], 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-black/50 dark:text-white/50 block">Laba:</span>
                                <strong class="text-[#34C759] tabular-nums">Rp {{ number_format($prod['gross_profit'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-black/40 dark:text-white/40">
                        Belum ada data penjualan pada rentang periode ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 5. MULTI-CABANG PERFORMANCE (JIKA > 1 LOKASI)          -->
    <!-- ======================================================= -->
    @if(count($analytics['location_performance']) > 1)
        <div class="rounded-[20px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-bold text-black dark:text-white">Performa Antar-Cabang</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Perbandingan omzet dan transaksi di tiap cabang operasional</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($analytics['location_performance'] as $loc)
                    <div class="rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] p-4 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-black dark:text-white">{{ $loc['name'] }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $loc['type'] === 'outlet' ? 'bg-[#007AFF]/15 text-[#007AFF]' : 'bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60' }}">
                                {{ $loc['type'] === 'outlet' ? 'Cabang' : 'Gudang' }}
                            </span>
                        </div>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between text-black/60 dark:text-white/60">
                                <span>Total Omzet:</span>
                                <strong class="text-black dark:text-white tabular-nums">Rp {{ number_format($loc['revenue'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between text-black/60 dark:text-white/60">
                                <span>Transaksi:</span>
                                <span class="text-black dark:text-white tabular-nums font-semibold">{{ $loc['orders_count'] }} order</span>
                            </div>
                            <div class="flex justify-between text-black/60 dark:text-white/60">
                                <span>Laba Kotor:</span>
                                <span class="text-[#34C759] tabular-nums font-semibold">Rp {{ number_format($loc['gross_profit'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
