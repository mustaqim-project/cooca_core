@extends('layouts.app', [
    'title' => 'Dashboard Bisnis',
    'headerTitle' => 'Ringkasan Bisnis & Cockpit Operasional',
    'headerSubtitle' => 'Pemantauan menyeluruh omzet, HPP, margin laba, inventori, dan kasir ' . ($business->name ?? 'Usaha'),
])

@section('content')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <div class="space-y-6 pb-12" x-data="{
        stats: {{ json_encode($stats ?? []) }},
        // Quick Calc Widget Data
        quickHpp: 15000,
        quickMargin: 40,
        get calculatedPrice() {
            if (this.quickMargin >= 100) return 0;
            return Math.round(this.quickHpp / (1 - (this.quickMargin / 100)));
        },
        get grossProfit() {
            return this.calculatedPrice - this.quickHpp;
        },
        get markupEquivalent() {
            if (this.quickHpp <= 0) return 0;
            return ((this.grossProfit / this.quickHpp) * 100).toFixed(1);
        },
        init() {
            window.addEventListener('expense-added', (e) => {
                if (e.detail && e.detail.stats) {
                    this.stats = e.detail.stats;
                }
            });
            window.addEventListener('stock-in-added', (e) => {
                if (e.detail && e.detail.stats) {
                    this.stats = e.detail.stats;
                }
            });
        }
    }">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Cockpit Bisnis &amp; Analisis Real-Time</span>
        </nav>

        <!-- ========================================== -->
        <!-- 1. TOP HEADER BANNER & ACTION COCKPIT      -->
        <!-- ========================================== -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div class="space-y-1.5 z-10 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                        <span>Cockpit 360° Real-Time</span>
                    </span>
                    @if (!empty($stats['is_shift_open']))
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>Kasir Buka ({{ $stats['active_shift']->user?->name ?? 'Kasir' }} • sejak {{ $stats['active_shift']->opened_at?->format('H:i') }})</span>
                        </span>
                    @else
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                            <span>Kasir Belum Buka</span>
                        </span>
                    @endif
                </div>

                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Halo, {{ auth()->user()->name ?? 'Owner' }}! Selamat Beraktivitas.
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pantau omzet, laba kotor, perputaran bahan, dan stok outlet <strong class="text-black dark:text-white font-medium">{{ $business->name ?? 'Usaha' }}</strong> secara instan &amp; akurat.
                </p>
            </div>

            <!-- Quick Action Buttons Hub (Apple HIG macOS Toolbar on Desktop, iOS 18 Control Center Bento on Mobile) -->
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full lg:w-auto z-10">
                @if(\App\Support\Context::hasPermission('pos.terminal'))
                <a href="{{ route('pos.terminal') }}"
                    class="col-span-2 sm:col-span-1 h-10 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] shrink-0">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Terminal Kasir POS</span>
                </a>
                @endif

                <a href="{{ route('calculator.index') }}"
                    class="col-span-1 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
                    <i data-lucide="calculator" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]"></i>
                    <span>Kalkulator HPP</span>
                </a>

                @if(\App\Support\Context::hasPermission('expenses.manage') || \App\Support\Context::hasPermission('expenses.view'))
                <button type="button" @click="$dispatch('open-quick-expense')"
                    class="col-span-1 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A]"></i>
                    <span>+ Catat Biaya</span>
                </button>
                @endif

                @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::hasPermission('purchasing.manage'))
                <button type="button" @click="$dispatch('open-quick-stockin')"
                    class="col-span-1 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
                    <i data-lucide="package-plus" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                    <span>+ Tambah Stok</span>
                </button>
                @endif

                @if(\App\Support\Context::hasPermission('invoices.create'))
                <a href="{{ route('invoices.create') }}"
                    class="col-span-1 h-10 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                    <span>+ Faktur Baru</span>
                </a>
                @endif
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- 2. DYNAMIC ANALYTICS COCKPIT (PERIOD FILTER, KPIS, CHARTS) -->
        <!-- ======================================================= -->
        <div id="analytics-cockpit" class="relative space-y-5 sm:space-y-6">
            
            <!-- Asynchronous Loading Spinner Overlay -->
            <div class="dash-loading-overlay rounded-[18px]" data-loading-overlay hidden>
                <div class="dash-spinner" aria-hidden="true"></div>
                <p class="text-xs font-medium text-black/70 dark:text-white/70">Memperbarui data analitik periode...</p>
            </div>

            <script>
                window.COOCA_DASHBOARD_ANALYTICS = @json($analytics ?? []);
            </script>

            <!-- 2.A Filter Periode Bar (Apple Segmented Control) -->
            <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]"
                x-data="{
                    period: '{{ $analytics['period'] ?? 'today' }}',
                    from: '{{ $analytics['range']['from'] ?? '' }}',
                    to: '{{ $analytics['range']['to'] ?? '' }}',
                    customOpen: {{ $analytics['period'] === 'custom' ? 'true' : 'false' }},
                    selectPeriod(p) {
                        this.period = p;
                        if (p === 'custom') {
                            this.customOpen = true;
                            return;
                        }
                        this.customOpen = false;
                        if (window.coocaFetchAnalytics) {
                            window.coocaFetchAnalytics(p);
                        } else {
                            document.getElementById('analytics-period-form').submit();
                        }
                    },
                    submitCustom() {
                        if (window.coocaFetchAnalytics) {
                            window.coocaFetchAnalytics('custom', this.from, this.to);
                        } else {
                            document.getElementById('analytics-period-form').submit();
                        }
                    }
                }">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-[16px] sm:text-[18px] font-semibold text-black dark:text-white tracking-tight">Analisis Kinerja Bisnis</h2>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]" data-analytics-period-badge>
                                {{ $analytics['period_label'] }} · {{ $analytics['range']['from'] }} → {{ $analytics['range']['to'] }}
                            </span>
                        </div>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Indikator kinerja, grafik tren, dan peringkat produk bergerak dinamis sesuai periode yang dipilih.</p>
                    </div>

                    <!-- Period Filter Controls (Apple Segmented Control Group) -->
                    <form id="analytics-period-form" method="GET" action="{{ route('dashboard') }}" @submit.prevent="submitCustom()" class="flex flex-wrap items-center gap-1.5">
                        <input type="hidden" name="period" :value="period">
                        <input type="hidden" name="from" :value="from">
                        <input type="hidden" name="to" :value="to">

                        <!-- Quick Segmented Buttons (macOS / iOS Segmented Pill) -->
                        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium overflow-x-auto no-scrollbar py-0.5 w-full sm:w-auto" x-show="!customOpen">
                            <button type="button" @click="selectPeriod('today')" :class="period === 'today' ? 'period-seg period-seg-active' : 'period-seg'" aria-label="Filter Hari Ini">Hari Ini</button>
                            <button type="button" @click="selectPeriod('week')" :class="period === 'week' ? 'period-seg period-seg-active' : 'period-seg'" aria-label="Filter Minggu Ini">Minggu Ini</button>
                            <button type="button" @click="selectPeriod('month')" :class="period === 'month' ? 'period-seg period-seg-active' : 'period-seg'" aria-label="Filter Bulan Ini">Bulan Ini</button>
                            <button type="button" @click="selectPeriod('year')" :class="period === 'year' ? 'period-seg period-seg-active' : 'period-seg'" aria-label="Filter Tahun Ini">Tahun Ini</button>
                            <button type="button" @click="selectPeriod('custom')" :class="period === 'custom' ? 'period-seg period-seg-active' : 'period-seg'" aria-label="Filter Custom Tanggal">Custom</button>
                        </div>

                        <!-- Custom Date Range Picker Mode -->
                        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto" x-show="customOpen">
                            <input type="date" class="period-date" x-model="from" :max="to" aria-label="Tanggal awal">
                            <span class="text-[12px] text-black/40 dark:text-white/40 font-medium">s/d</span>
                            <input type="date" class="period-date" x-model="to" :min="from" aria-label="Tanggal akhir">
                            <button type="submit" class="period-apply">Terapkan</button>
                            <button type="button" @click="selectPeriod('month')" class="px-2.5 py-1.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2.B KPI Cards (iOS 18 Bento Grid on Mobile, macOS 3-Col Grid on Desktop) -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4.5">
                
                <!-- KPI 1: Total Penjualan (Omzet) - Bento Hero Card -->
                <div class="kpi-card kpi-card-fixed kpi-bento-hero col-span-2 sm:col-span-1 group">
                    <div class="kpi-icon kpi-icon-green">
                        <i data-lucide="coins" class="w-5 h-5"></i>
                    </div>
                    <div class="kpi-body min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <span class="kpi-label">Omzet Hari Ini</span>
                            <span class="text-[10px] font-semibold text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 px-2 py-0.5 rounded-full shrink-0">Penjualan</span>
                        </div>
                        <div class="kpi-value tabular-nums font-bold text-black dark:text-white tracking-tight" data-kpi-value data-kpi="omzet">
                            Rp {{ number_format($analytics['kpis']['omzet'], 0, ',', '.') }}
                        </div>
                        <div class="kpi-foot">Akumulasi Kasir POS &amp; Faktur Tagihan</div>
                    </div>
                </div>

                <!-- KPI 2: Estimasi Laba Bersih - Bento Compact Tile -->
                <div class="kpi-card kpi-card-fixed kpi-bento-tile col-span-1 group">
                    <div class="kpi-icon kpi-icon-teal">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <div class="kpi-body min-w-0 flex-1 w-full">
                        <div class="flex items-center justify-between gap-1">
                            <span class="kpi-label truncate">Estimasi Untung Bersih</span>
                            <span class="text-[9.5px] sm:text-[10px] font-semibold text-[#30B0C7] dark:text-[#40C8E0] bg-[#30B0C7]/10 px-1.5 sm:px-2 py-0.5 rounded-full shrink-0 truncate max-w-[65px] sm:max-w-none">Estimasi Laba Bersih (MTD)</span>
                        </div>
                        <div class="kpi-value tabular-nums font-bold {{ $analytics['kpis']['net'] >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }} tracking-tight" data-kpi-value data-kpi="net">
                            Rp {{ number_format($analytics['kpis']['net'], 0, ',', '.') }}
                        </div>
                        <div class="kpi-foot" title="Setelah dikurangi beban toko & modal HPP">Setelah dikurangi beban toko &amp; modal HPP</div>
                    </div>
                </div>

                <!-- KPI 3: Laba Kotor (Gross Margin) - Bento Compact Tile -->
                <div class="kpi-card kpi-card-fixed kpi-bento-tile col-span-1 group">
                    <div class="kpi-icon kpi-icon-violet">
                        <i data-lucide="pie-chart" class="w-5 h-5"></i>
                    </div>
                    <div class="kpi-body min-w-0 flex-1 w-full">
                        <div class="flex items-center justify-between gap-1">
                            <span class="kpi-label truncate">Laba Kotor Produk</span>
                            <span class="text-[9.5px] sm:text-[10px] font-semibold text-[#5856D6] dark:text-[#5E5CE6] bg-[#5856D6]/10 px-1.5 sm:px-2 py-0.5 rounded-full tabular-nums shrink-0">Margin <b data-kpi="margin_pct">{{ $analytics['kpis']['margin_pct'] !== null ? $analytics['kpis']['margin_pct'] . '%' : '—' }}</b></span>
                        </div>
                        <div class="kpi-value tabular-nums font-bold text-black dark:text-white tracking-tight" data-kpi-value data-kpi="profit">
                            Rp {{ number_format($analytics['kpis']['profit'], 0, ',', '.') }}
                        </div>
                        <div class="kpi-foot" title="Selisih harga jual terhadap modal resep">Selisih harga jual terhadap modal resep</div>
                    </div>
                </div>

                <!-- KPI 4: Jumlah Transaksi - Bento Compact Tile -->
                <div class="kpi-card kpi-card-fixed kpi-bento-tile col-span-1 group">
                    <div class="kpi-icon kpi-icon-sky">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <div class="kpi-body min-w-0 flex-1 w-full">
                        <div class="flex items-center justify-between gap-1">
                            <span class="kpi-label truncate">Total Transaksi</span>
                            <span class="text-[9.5px] sm:text-[10px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 px-1.5 sm:px-2 py-0.5 rounded-full shrink-0">Volume</span>
                        </div>
                        <div class="kpi-value tabular-nums font-bold text-black dark:text-white tracking-tight" data-kpi-value data-kpi="transactions">
                            {{ number_format($analytics['kpis']['transactions'], 0, ',', '.') }}
                        </div>
                        <div class="kpi-foot" title="Rata-rata: Rp {{ number_format($analytics['kpis']['avg_ticket'], 0, ',', '.') }} / tx · Piutang Belum Lunas: Rp {{ number_format($stats['unpaid_invoices_amount'] ?? 0, 0, ',', '.') }}">
                            Rata-rata: <b data-kpi="avg_ticket">Rp {{ number_format($analytics['kpis']['avg_ticket'], 0, ',', '.') }}</b> / tx · Piutang Belum Lunas: <span class="tabular-nums font-semibold">Rp {{ number_format($stats['unpaid_invoices_amount'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- KPI 5: Biaya HPP & Beban Operasional - Bento Compact Tile -->
                <div class="kpi-card kpi-card-fixed kpi-bento-tile col-span-1 group">
                    <div class="kpi-icon kpi-icon-amber">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </div>
                    <div class="kpi-body min-w-0 flex-1 w-full">
                        <div class="flex items-center justify-between gap-1">
                            <span class="kpi-label truncate">Total Beban Toko</span>
                            <span class="text-[9.5px] sm:text-[10px] font-semibold text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 px-1.5 sm:px-2 py-0.5 rounded-full shrink-0">Pengeluaran</span>
                        </div>
                        <div class="kpi-value tabular-nums font-bold text-[#FF9500] dark:text-[#FF9F0A] tracking-tight" data-kpi-value data-kpi="expenses">
                            Rp {{ number_format($analytics['kpis']['expenses'], 0, ',', '.') }}
                        </div>
                        <div class="kpi-foot" title="HPP Modal Terjual: Rp {{ number_format($analytics['kpis']['hpp'], 0, ',', '.') }}">
                            HPP Modal Terjual: <b data-kpi="hpp">Rp {{ number_format($analytics['kpis']['hpp'], 0, ',', '.') }}</b>
                        </div>
                    </div>
                </div>

                <!-- KPI 6: Perhatian Stok Menipis - Bento Hero Card -->
                <div class="kpi-card kpi-card-fixed kpi-bento-hero col-span-2 sm:col-span-1 group">
                    <div class="kpi-icon kpi-icon-rose">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div class="kpi-body min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <span class="kpi-label">Perhatian Stok</span>
                            <span class="text-[10px] font-semibold text-[#FF3B30] dark:text-[#FF453A] bg-[#FF3B30]/10 px-2 py-0.5 rounded-full shrink-0">Valuasi Aset Stok</span>
                        </div>
                        <div class="kpi-value tabular-nums font-bold text-[#FF3B30] dark:text-[#FF453A] tracking-tight" data-kpi-value data-kpi="low_stock">
                            {{ $analytics['kpis']['low_stock'] }}
                        </div>
                        <div class="kpi-foot">
                            <span x-text="stats.low_stock_count > 0 ? stats.low_stock_count + ' Stok Menipis' : 'Stok Aman'">{{ !empty($stats['low_stock_count']) && $stats['low_stock_count'] > 0 ? $stats['low_stock_count'] . ' Stok Menipis' : 'Stok Aman' }}</span>
                            · Modal: <b data-kpi="at_risk_value">Rp {{ number_format($analytics['at_risk_value'], 0, ',', '.') }}</b>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.C Tren Penjualan & Distribusi Sumber Penjualan (Apple Flat Neutral Surface) -->
            <div>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5 items-stretch">
                    
                    <!-- Line Chart: Tren Penjualan & Keuntungan (2 Kolom) -->
                    <div class="xl:col-span-2 chart-wrap chart-card-lg" data-chart="trend">
                        <div class="chart-head justify-between flex-wrap gap-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                    <i data-lucide="line-chart" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[14px] sm:text-[15px] font-semibold text-black dark:text-white tracking-tight">
                                        Grafik Tren Penjualan &amp; Laba Kotor
                                    </h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">
                                        Tren Penjualan 7 Hari Terakhir &amp; ritme omzet serta laba kotor
                                    </p>
                                </div>
                            </div>

                            <!-- Line Chart Series Toggle & Granularity Badge -->
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 whitespace-nowrap" data-trend-granularity>
                                    {{ $analytics['granularity'] === 'hour' ? 'Jam Operasional' : ($analytics['granularity'] === 'day' ? 'Harian' : 'Bulanan') }}
                                </span>

                                <div class="inline-flex items-center p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[12px] font-medium">
                                    <button type="button" onclick="setTrendSeries('all')" data-trend-btn="all" class="px-2.5 py-0.5 rounded-[7px] transition bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]">Semua</button>
                                    <button type="button" onclick="setTrendSeries('omzet')" data-trend-btn="omzet" class="px-2.5 py-0.5 rounded-[7px] transition text-black/55 dark:text-white/55">Omzet</button>
                                    <button type="button" onclick="setTrendSeries('profit')" data-trend-btn="profit" class="px-2.5 py-0.5 rounded-[7px] transition text-black/55 dark:text-white/55">Laba Kotor</button>
                                </div>
                            </div>
                        </div>

                        <div class="chart-canvas-wrap chart-canvas-wrap-lg pt-1">
                            <div class="chart-skeleton" data-skeleton></div>
                            <canvas id="chart-trend" class="chart-canvas"></canvas>
                            
                            <!-- Empty State -->
                            <div class="chart-state chart-empty" data-empty hidden>
                                <i data-lucide="bar-chart-2" class="w-10 h-10 text-black/20 dark:text-white/20 mb-1"></i>
                                <p class="font-semibold text-black/70 dark:text-white/70">Belum ada transaksi pada periode ini.</p>
                                <span class="chart-state-note">Transaksi yang selesai di Kasir POS atau faktur terbayar akan otomatis tergambar di sini.</span>
                            </div>

                            <!-- Error State -->
                            <div class="chart-state chart-error" data-error hidden>
                                <i data-lucide="alert-triangle" class="w-10 h-10 text-[#FF3B30] mb-1"></i>
                                <p class="font-semibold text-black/70 dark:text-white/70">Grafik tidak dapat dimuat.</p>
                                <button type="button" onclick="renderTrend()" class="chart-retry">Coba Muat Ulang</button>
                            </div>
                        </div>
                    </div>

                    <!-- Donut 1: Sumber Penjualan (Kasir POS vs Faktur) (1 Kolom) -->
                    <div class="xl:col-span-1 chart-wrap chart-card-lg" data-chart="channel">
                        <div class="chart-head">
                            <div class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] sm:text-[15px] font-semibold text-black dark:text-white tracking-tight">Sumber Penjualan</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50">Kasir langsung vs faktur tagihan</p>
                            </div>
                        </div>
                        <div class="chart-canvas-wrap chart-canvas-wrap-lg chart-donut pt-1">
                            <div class="chart-skeleton" data-skeleton></div>
                            <canvas id="chart-channel" class="chart-canvas"></canvas>
                            <div class="chart-donut-center" data-donut-center="channel" hidden>
                                <span class="donut-total tabular-nums font-bold text-black dark:text-white" data-donut-total="channel">Rp 0</span>
                                <span class="donut-caption">Total Omzet</span>
                            </div>
                            <div class="chart-state chart-empty" data-empty hidden>
                                <i data-lucide="pie-chart" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <p>Belum ada penjualan</p>
                            </div>
                            <div class="chart-state chart-error" data-error hidden>
                                <i data-lucide="alert-triangle" class="w-8 h-8 text-[#FF3B30]"></i>
                                <p>Gagal memuat grafik</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2.D Distribusi Pendukung (3 Kolom Seimbang, Apple Flat Neutral Layout) -->
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-5 items-stretch">
                    
                    <!-- Donut 2: Tipe Layanan Kasir (Dine In, Take Away, Delivery) -->
                    <div class="chart-wrap chart-card-md" data-chart="orders">
                        <div class="chart-head">
                            <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <i data-lucide="utensils" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-semibold text-black dark:text-white tracking-tight">Metode Pelayanan</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Pilihan pesanan pelanggan kasir</p>
                            </div>
                        </div>
                        <div class="chart-canvas-wrap chart-canvas-wrap-md chart-donut pt-1">
                            <div class="chart-skeleton" data-skeleton></div>
                            <canvas id="chart-orders" class="chart-canvas"></canvas>
                            <div class="chart-donut-center" data-donut-center="orders" hidden>
                                <span class="donut-total tabular-nums font-bold text-black dark:text-white" data-donut-total="orders">0</span>
                                <span class="donut-caption">Transaksi</span>
                            </div>
                            <div class="chart-state chart-empty" data-empty hidden>
                                <i data-lucide="pie-chart" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <p>Belum ada order kasir</p>
                            </div>
                            <div class="chart-state chart-error" data-error hidden>
                                <i data-lucide="alert-triangle" class="w-8 h-8 text-[#FF3B30]"></i>
                                <p>Gagal memuat grafik</p>
                            </div>
                        </div>
                    </div>

                    <!-- Donut 3: Omzet Berdasarkan Kategori Produk -->
                    <div class="chart-wrap chart-card-md" data-chart="categories">
                        <div class="chart-head">
                            <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-semibold text-black dark:text-white tracking-tight">Kategori Terlaris</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Kategori paling berkontribusi</p>
                            </div>
                        </div>
                        <div class="chart-canvas-wrap chart-canvas-wrap-md chart-donut pt-1">
                            <div class="chart-skeleton" data-skeleton></div>
                            <canvas id="chart-categories" class="chart-canvas"></canvas>
                            <div class="chart-donut-center" data-donut-center="categories" hidden>
                                <span class="donut-total tabular-nums font-bold text-black dark:text-white" data-donut-total="categories">0</span>
                                <span class="donut-caption">Kategori</span>
                            </div>
                            <div class="chart-state chart-empty" data-empty hidden>
                                <i data-lucide="pie-chart" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <p>Belum ada kategori terjual</p>
                            </div>
                            <div class="chart-state chart-error" data-error hidden>
                                <i data-lucide="alert-triangle" class="w-8 h-8 text-[#FF3B30]"></i>
                                <p>Gagal memuat grafik</p>
                            </div>
                        </div>
                    </div>

                    <!-- Donut 4: Metode Pembayaran -->
                    <div class="chart-wrap chart-card-md" data-chart="payment">
                        <div class="chart-head">
                            <div class="w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-semibold text-black dark:text-white tracking-tight">Metode Bayar</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Tunai, QRIS, &amp; Transfer</p>
                            </div>
                        </div>
                        <div class="chart-canvas-wrap chart-canvas-wrap-md chart-donut pt-1">
                            <div class="chart-skeleton" data-skeleton></div>
                            <canvas id="chart-payment" class="chart-canvas"></canvas>
                            <div class="chart-donut-center" data-donut-center="payment" hidden>
                                <span class="donut-total tabular-nums font-bold text-black dark:text-white" data-donut-total="payment">0</span>
                                <span class="donut-caption">Pembayaran</span>
                            </div>
                            <div class="chart-state chart-empty" data-empty hidden>
                                <i data-lucide="pie-chart" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <p>Belum ada pembayaran</p>
                            </div>
                            <div class="chart-state chart-error" data-error hidden>
                                <i data-lucide="alert-triangle" class="w-8 h-8 text-[#FF3B30]"></i>
                                <p>Gagal memuat grafik</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2.E Tables: Top 10 Produk, Top 15 Bahan, Top 10 Stok Menipis (Apple Dense List) -->
            <div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 items-stretch">
                    
                    <!-- Table 1: Top 10 Produk Terlaris -->
                    <div class="dash-panel table-card-fixed">
                        <div class="panel-head">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                    <i data-lucide="trophy" class="w-3.5 h-3.5"></i>
                                </div>
                                <h3 class="text-[13px] sm:text-[14px] font-semibold text-black dark:text-white tracking-tight">
                                    Top 10 Produk Terlaris
                                </h3>
                            </div>
                            <span class="panel-count" data-panel-count="products">{{ count($analytics['top_products']) }} produk</span>
                        </div>

                        <div class="table-body-scroll custom-scrollbar">
                            <div data-panel-body="products">
                                @if (count($analytics['top_products']) > 0)
                                    <table class="dash-tbl w-full text-left text-[13px]">
                                        <thead>
                                            <tr class="border-b border-black/5 dark:border-white/10">
                                                <th class="w-8">#</th>
                                                <th>Produk Terlaris</th>
                                                <th class="text-right">Terjual</th>
                                                <th class="text-right">Omzet</th>
                                                <th class="w-16">Porsi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] tabular-nums">
                                            @php $rank = 0; @endphp
                                            @foreach ($analytics['top_products'] as $p)
                                                @php $rank++; @endphp
                                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                                    <td class="dash-rank">{{ $rank }}</td>
                                                    <td class="font-medium text-black dark:text-white truncate max-w-[150px]" title="{{ $p['name'] }}">
                                                        {{ $p['name'] }}
                                                        @if (!empty($p['code']))
                                                             <span class="dash-code">{{ $p['code'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right text-black/60 dark:text-white/60 font-semibold">{{ number_format($p['qty'], 0, ',', '.') }}</td>
                                                    <td class="text-right text-[#34C759] dark:text-[#30D158] font-semibold">Rp {{ number_format($p['revenue'], 0, ',', '.') }}</td>
                                                    <td>
                                                        <div class="pbar" title="{{ $p['pct'] }}% dari penjualan tertinggi"><i style="width: {{ $p['pct'] }}%"></i></div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            <div class="panel-empty" data-panel-empty="products" {{ count($analytics['top_products']) > 0 ? 'hidden' : '' }}>
                                <i data-lucide="trophy" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <p class="font-semibold text-black/70 dark:text-white/70">Belum ada produk terjual pada periode ini.</p>
                                <span class="chart-state-note">Produk yang terjual lewat kasir atau faktur akan muncul di daftar peringkat ini.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Table 2: Top 15 Material Paling Sering Digunakan -->
                    <div class="dash-panel table-card-fixed">
                        <div class="panel-head">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                                    <i data-lucide="box" class="w-3.5 h-3.5"></i>
                                </div>
                                <h3 class="text-[13px] sm:text-[14px] font-semibold text-black dark:text-white tracking-tight">
                                    Top 15 Bahan Paling Sering
                                </h3>
                            </div>
                            <span class="panel-count" data-panel-count="materials">{{ count($analytics['top_materials']) }} bahan</span>
                        </div>

                        <div class="table-body-scroll custom-scrollbar">
                            <div data-panel-body="materials">
                                @if (count($analytics['top_materials']) > 0)
                                    <table class="dash-tbl w-full text-left text-[13px]">
                                        <thead>
                                            <tr class="border-b border-black/5 dark:border-white/10">
                                                <th class="w-8">#</th>
                                                <th>Bahan Baku</th>
                                                <th class="text-right">Jumlah Pakai</th>
                                                <th class="w-16">Porsi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] tabular-nums">
                                            @php $rank = 0; @endphp
                                            @foreach ($analytics['top_materials'] as $m)
                                                @php $rank++; @endphp
                                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                                    <td class="dash-rank">{{ $rank }}</td>
                                                    <td class="font-medium text-black dark:text-white truncate max-w-[160px]" title="{{ $m['name'] }}">
                                                        {{ $m['name'] }}
                                                    </td>
                                                    <td class="text-right text-black/70 dark:text-white/70 font-semibold">
                                                        {{ number_format($m['qty'], 2, ',', '.') }} <span class="dash-unit">{{ $m['unit'] }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="pbar" title="{{ $m['pct'] }}% dari pemakaian tertinggi"><i style="width: {{ $m['pct'] }}%"></i></div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            <div class="panel-empty" data-panel-empty="materials" {{ count($analytics['top_materials']) > 0 ? 'hidden' : '' }}>
                                <i data-lucide="package-open" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <p class="font-semibold text-black/70 dark:text-white/70">Belum ada pemakaian bahan pada periode ini.</p>
                                <span class="chart-state-note">Pemakaian bahan otomatis dihitung dari resep menu terjual &amp; mutasi barang keluar.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Table 3: Top 10 Stok Hampir Habis -->
                    <div class="dash-panel table-card-fixed">
                        <div class="panel-head">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] text-[#FF3B30] flex items-center justify-center shrink-0">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                </div>
                                <h3 class="text-[13px] sm:text-[14px] font-semibold text-black dark:text-white tracking-tight">
                                    Stok Hampir Habis
                                </h3>
                            </div>
                            <span class="panel-count" data-panel-count="low_stock">{{ count($analytics['low_stock']) }} item</span>
                        </div>

                        <div class="table-body-scroll custom-scrollbar">
                            <div data-panel-body="low_stock">
                                @if (count($analytics['low_stock']) > 0)
                                    <table class="dash-tbl w-full text-left text-[13px]">
                                        <thead>
                                            <tr class="border-b border-black/5 dark:border-white/10">
                                                <th class="w-8">#</th>
                                                <th>Item</th>
                                                <th class="text-right">Sisa</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] tabular-nums">
                                            @php $rank = 0; @endphp
                                            @foreach ($analytics['low_stock'] as $it)
                                                @php $rank++; @endphp
                                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                                    <td class="dash-rank">{{ $rank }}</td>
                                                    <td class="font-medium text-black dark:text-white truncate max-w-[150px]" title="{{ $it['name'] }} ({{ $it['kind'] }})">
                                                        {{ $it['name'] }}
                                                        <span class="text-[11px] text-black/40 dark:text-white/40 ml-1">({{ $it['kind'] }})</span>
                                                    </td>
                                                    <td class="text-right {{ $it['critical'] ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : ($it['is_low'] ? 'text-[#FF9500] dark:text-[#FF9F0A] font-semibold' : 'text-black/70 dark:text-white/70 font-medium') }}">
                                                        {{ number_format($it['remaining'], 2, ',', '.') }} <span class="dash-unit">{{ $it['unit'] }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($it['critical'])
                                                            <span class="badge badge-critical inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] shrink-0"></span><span>Kritis</span></span>
                                                        @elseif ($it['is_low'])
                                                            <span class="badge badge-low inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span><span>Menipis</span></span>
                                                        @else
                                                            <span class="badge badge-safe inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span><span>Aman</span></span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            <div class="panel-empty" data-panel-empty="low_stock" {{ count($analytics['low_stock']) > 0 ? 'hidden' : '' }}>
                                <i data-lucide="check-circle-2" class="w-8 h-8 text-[#34C759]"></i>
                                <p class="font-semibold text-black/70 dark:text-white/70">Semua stok dalam batas aman!</p>
                                <span class="chart-state-note">Seluruh bahan baku dan produk di gudang masih mencukupi kebutuhan operasional.</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2.F Sorotan & Insight Cepat (Apple Intelligence Purple Theme) -->
            <div>
                <div class="insight-panel insight-card-fixed">
                    <div class="chart-head justify-between flex-shrink-0">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]"></i>
                            <h3 class="text-[13px] sm:text-[14px] font-semibold text-black dark:text-white tracking-tight">
                                Cooca AI Business Advisor
                            </h3>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-[#AF52DE]/12 text-[#AF52DE] dark:text-[#BF5AF2]">Analisis Otomatis</span>
                    </div>
                    <p class="chart-sub flex-shrink-0">Ringkasan penting dari data usaha Anda untuk pengambilan keputusan cepat.</p>

                    <!-- Operational Health Alerts -->
                    @if ((!empty($stats['low_margin_count']) && $stats['low_margin_count'] > 0) || (!empty($stats['low_stock_count']) && $stats['low_stock_count'] > 0))
                        <div class="space-y-2 mb-3">
                            @if (!empty($stats['low_margin_count']) && $stats['low_margin_count'] > 0)
                                <div class="p-3 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <div class="font-semibold flex items-center gap-1.5 mb-1 text-[12px]">
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-[#FF9500] shrink-0"></i>
                                        <span>Margin Tipis Terdeteksi</span>
                                    </div>
                                    <p class="text-[11px] text-black/70 dark:text-white/70 leading-normal">
                                        Ada <strong>{{ $stats['low_margin_count'] }} produk</strong> dengan margin di bawah 25%. Disarankan mengecek harga bahan baku atau menaikkan harga jual di kasir.
                                    </p>
                                </div>
                            @endif

                            @if (!empty($stats['low_stock_count']) && $stats['low_stock_count'] > 0)
                                <div class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A]">
                                    <div class="font-semibold flex items-center gap-1.5 mb-1 text-[12px]">
                                        <i data-lucide="package-x" class="w-3.5 h-3.5 text-[#FF3B30] shrink-0"></i>
                                        <span>Peringatan Stok Rendah</span>
                                    </div>
                                    <p class="text-[11px] text-black/70 dark:text-white/70 leading-normal">
                                        Terdapat <strong>{{ $stats['low_stock_count'] }} item stok</strong> hampir habis. Segera lakukan pesanan pembelian agar tidak menghambat penjualan.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="insight-list-scroll custom-scrollbar">
                        <div class="flex flex-col gap-2" data-insights-list>
                            @foreach ($analytics['insights'] as $ins)
                                <div class="insight-chip">
                                    <i data-lucide="{{ $ins['icon'] ?? 'sparkles' }}" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2] shrink-0"></i>
                                    <span class="insight-label">{{ $ins['label'] }}</span>
                                    <span class="insight-value">{{ $ins['value'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="insight-empty" data-insights-empty {{ count($analytics['insights']) > 0 ? 'hidden' : '' }}>
                            <i data-lucide="sparkles" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                            <p class="font-semibold text-black/70 dark:text-white/70">Belum ada sorotan untuk periode ini.</p>
                            <span class="chart-state-note">Setelah penjualan pertama tercatat, rangkuman rekomendasi otomatis muncul di sini.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. LIVE TRANSACTION FEED & CATALOG MARGINS -->
        <!-- ========================================== -->
        @php
            $recentPosOrders = $recentPosOrders ?? collect([]);
            $recentProducts = $recentProducts ?? collect([]);
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

            <!-- Left: Transaksi Kasir POS Terbaru -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col justify-between space-y-4 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3 mb-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white tracking-tight">Transaksi Kasir Terbaru</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Order dan transaksi kasir POS hari ini</p>
                        </div>
                    </div>
                    <a href="{{ route('pos.orders.index') }}" class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-1 transition-colors">
                        <span>Semua Transaksi</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                @if ($recentPosOrders->isEmpty())
                    <div class="text-center py-10 px-4 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                            <i data-lucide="receipt" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">Belum Ada Transaksi Hari Ini</h3>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Buka terminal kasir POS untuk mulai melayani pelanggan toko.</p>
                        </div>
                        <a href="{{ route('pos.terminal') }}"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] mt-2">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                            <span>Buka Terminal Kasir</span>
                        </a>
                    </div>
                @else
                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 divide-y divide-black/[0.04] dark:divide-white/[0.06] overflow-hidden">
                        @foreach ($recentPosOrders as $order)
                            <div class="p-3 hover:bg-black/[0.03] dark:hover:bg-white/[0.05] flex items-center justify-between transition-colors">
                                <div class="space-y-0.5 min-w-0 pr-2">
                                    <div class="font-medium text-[13px] text-black dark:text-white flex items-center gap-2 truncate">
                                        <span class="tabular-nums font-semibold">{{ $order->order_number }}</span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45 truncate">({{ $order->customer_name_guest ?? 'Pelanggan Umum' }})</span>
                                    </div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 flex items-center gap-2">
                                        <span>{{ $order->created_at?->diffForHumans() ?? 'Baru saja' }}</span>
                                        <span>&bull;</span>
                                        <span class="capitalize text-black/70 dark:text-white/70 font-medium">{{ str_replace('_', ' ', $order->order_type ?? 'Takeaway') }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="tabular-nums font-bold text-[13px] text-[#34C759] dark:text-[#30D158]">
                                        Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        <span>{{ $order->status }}</span>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Katalog & Margin Produk Toko -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col justify-between space-y-4 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3 mb-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="package" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white tracking-tight">Katalog &amp; Margin Produk</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">HPP dan persentase keuntungan produk</p>
                        </div>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-[13px] font-medium text-[#007AFF] hover:underline flex items-center gap-1 transition-colors">
                        <span>Semua ({{ $stats['total_products'] ?? 0 }})</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                @if ($recentProducts->isEmpty())
                    <div class="text-center py-10 px-4 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                            <i data-lucide="box" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">Belum Ada Produk Terdaftar</h3>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Daftarkan produk baru untuk mulai menghitung HPP &amp; menjual di kasir.</p>
                        </div>
                        <a href="{{ route('calculator.index') }}"
                            class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] mt-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Hitung &amp; Daftarkan Produk Baru</span>
                        </a>
                    </div>
                @else
                    <div class="rounded-[12px] border border-black/5 dark:border-white/5 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left text-[13px] min-w-[420px]">
                                <thead class="border-b border-black/5 dark:border-white/10 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">
                                    <tr>
                                        <th class="px-4 py-2.5">Produk</th>
                                        <th class="px-4 py-2.5 text-right">HPP Dasar</th>
                                        <th class="px-4 py-2.5 text-right">Harga Jual</th>
                                        <th class="px-4 py-2.5 text-right">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    @foreach ($recentProducts as $prod)
                                        @php
                                            $margin = $prod->selling_price > 0 && $prod->base_cost > 0
                                                ? round((($prod->selling_price - $prod->base_cost) / $prod->selling_price) * 100, 1)
                                                : null;
                                        @endphp
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                            <td class="px-4 py-2.5 font-medium text-black dark:text-white">
                                                <a href="{{ route('products.bom', $prod->slug) }}" class="hover:text-[#007AFF] transition-colors">
                                                    {{ $prod->name }}
                                                </a>
                                                <div class="text-[11px] text-black/45 dark:text-white/45">
                                                    {{ $prod->category?->name ?? 'pcs' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 text-right tabular-nums text-black/60 dark:text-white/60">
                                                Rp {{ number_format((float) $prod->base_cost, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right tabular-nums font-semibold text-black dark:text-white">
                                                Rp {{ number_format((float) $prod->selling_price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                                @if ($margin !== null)
                                                    @if ($margin >= 40)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold tabular-nums bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                            <span>{{ $margin }}%</span>
                                                        </span>
                                                    @elseif ($margin >= 25)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold tabular-nums bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                            <span>{{ $margin }}%</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold tabular-nums bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                                            <span>{{ $margin }}%</span>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-black/30 dark:text-white/30 text-[11px] tabular-nums">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 4. QUICK CALCULATOR WIDGET & SHORTCUTS     -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

            <!-- Quick Instant HPP Calculator Widget -->
            <div id="tour-quick-calc"
                class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 lg:col-span-2 relative overflow-hidden space-y-4 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3 mb-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-[15px] font-semibold text-black dark:text-white tracking-tight">Kalkulator HPP &amp; Target Margin</h2>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Simulasikan harga jual rekomendasi instan berdasarkan target margin kotor</p>
                        </div>
                    </div>
                    <a href="{{ route('calculator.index') }}"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1">
                        <span>Kalkulator Lengkap</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mb-4">
                    <div>
                        <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">Estimasi Modal HPP Dasar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 text-[13px] font-medium tabular-nums">
                                Rp
                            </div>
                            <input type="number" x-model.number="quickHpp" min="0" step="1000"
                                class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-10 pr-3.5 text-[14px] font-medium tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[12px] font-medium text-black/60 dark:text-white/60">Target Laba Kotor (Margin %)</label>
                            <span class="text-[13px] font-bold tabular-nums text-[#007AFF]" x-text="quickMargin + '%'"></span>
                        </div>
                        <input type="range" x-model.number="quickMargin" min="5" max="80" step="1"
                            class="w-full h-1.5 bg-black/[0.06] dark:bg-white/[0.08] rounded-full appearance-none cursor-pointer accent-[#007AFF]">
                        
                        <!-- Quick Margin Presets (Apple Segmented Control Style) -->
                        <div class="flex items-center gap-2 mt-2.5">
                            <span class="text-[11px] text-black/40 dark:text-white/40 font-medium">Pilihan Cepat:</span>
                            <div class="inline-flex p-0.5 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.06] text-[11px] font-semibold">
                                <template x-for="preset in [30, 40, 50, 60]">
                                    <button type="button" @click="quickMargin = preset"
                                        :class="quickMargin === preset ? 'bg-white dark:bg-[#3A3A3C] text-[#007AFF] shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white'"
                                        class="px-2.5 py-0.5 rounded-[6px] transition-all cursor-pointer tabular-nums"
                                        x-text="preset + '%'"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instant Calculation Result Grid (Apple Grouped Inset 3-Kolom) -->
                <div class="grid grid-cols-3 gap-2 sm:gap-4 p-3 sm:p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 transition-colors">
                    <div>
                        <div class="text-[11px] font-medium text-black/45 dark:text-white/45 truncate">Harga Rekomendasi</div>
                        <div class="text-[16px] sm:text-[20px] font-bold text-[#34C759] dark:text-[#30D158] tabular-nums mt-0.5 truncate">
                            Rp <span x-text="calculatedPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="text-[10px] text-black/35 dark:text-white/35 mt-0.5 hidden sm:block">Modal / (1 - Margin%)</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-medium text-black/45 dark:text-white/45 truncate">Laba Kotor / Satuan</div>
                        <div class="text-[16px] sm:text-[20px] font-bold text-[#007AFF] tabular-nums mt-0.5 truncate">
                            Rp <span x-text="grossProfit.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="text-[10px] text-black/35 dark:text-white/35 mt-0.5 hidden sm:block">Estimasi nominal untung</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-medium text-black/45 dark:text-white/45 truncate">Markup Modal</div>
                        <div class="text-[16px] sm:text-[20px] font-bold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums mt-0.5 truncate">
                            <span x-text="markupEquivalent"></span>%
                        </div>
                        <div class="text-[10px] text-black/35 dark:text-white/35 mt-0.5 hidden sm:block">Kenaikan harga dasar</div>
                    </div>
                </div>
            </div>

            <!-- Quick System Shortcuts (Apple Style List) -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col justify-between space-y-4 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                <div>
                    <h2 class="text-[15px] font-semibold text-black dark:text-white tracking-tight mb-1">Pintasan Menu Cepat</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Akses cepat ke modul analisis &amp; laporan</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                    @if(\App\Support\Context::hasPermission('materials.view'))
                    <a href="{{ route('materials.index') }}"
                        class="p-2.5 sm:p-3 rounded-[10px] bg-black/[0.02] hover:bg-black/[0.05] dark:bg-white/[0.03] dark:hover:bg-white/[0.06] border border-black/5 dark:border-white/5 flex items-center justify-between text-[13px] font-medium text-black/80 dark:text-white/80 transition-all group gap-2 active:scale-[0.98]">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-[7px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                                <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="truncate">Bahan Baku &amp; Riwayat</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#007AFF] transition-colors shrink-0"></i>
                    </a>
                    @endif

                    @if(\App\Support\Context::hasPermission('costing.view_margin'))
                    <a href="{{ route('simulator.index') }}"
                        class="p-2.5 sm:p-3 rounded-[10px] bg-black/[0.02] hover:bg-black/[0.05] dark:bg-white/[0.03] dark:hover:bg-white/[0.06] border border-black/5 dark:border-white/5 flex items-center justify-between text-[13px] font-medium text-black/80 dark:text-white/80 transition-all group gap-2 active:scale-[0.98]">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-[7px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="truncate">Simulator What-If</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#007AFF] transition-colors shrink-0"></i>
                    </a>

                    <a href="{{ route('profitability.index') }}"
                        class="p-2.5 sm:p-3 rounded-[10px] bg-black/[0.02] hover:bg-black/[0.05] dark:bg-white/[0.03] dark:hover:bg-white/[0.06] border border-black/5 dark:border-white/5 flex items-center justify-between text-[13px] font-medium text-black/80 dark:text-white/80 transition-all group gap-2 active:scale-[0.98]">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-[7px] bg-[#AF52DE]/12 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0">
                                <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="truncate">BEP Titik Impas</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#AF52DE] transition-colors shrink-0"></i>
                    </a>
                    @endif

                    @if(\App\Support\Context::hasPermission('reports.view'))
                    <a href="{{ route('reports.index') }}"
                        class="p-2.5 sm:p-3 rounded-[10px] bg-black/[0.02] hover:bg-black/[0.05] dark:bg-white/[0.03] dark:hover:bg-white/[0.06] border border-black/5 dark:border-white/5 flex items-center justify-between text-[13px] font-medium text-black/80 dark:text-white/80 transition-all group gap-2 active:scale-[0.98]">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-[7px] bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="truncate">Laporan Laba &amp; HPP</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#FF9500] transition-colors shrink-0"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
@endsection
