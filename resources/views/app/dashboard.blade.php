@extends('layouts.app', [
    'title' => 'Dashboard Bisnis',
    'headerTitle' => 'Ringkasan Bisnis & Cockpit Operasional',
    'headerSubtitle' => 'Pemantauan menyeluruh omzet, HPP, margin laba, inventori, dan kasir ' . $business->name,
])

@section('content')
    <div class="space-y-6 pb-12" x-data="{
        stats: {{ json_encode($stats) }},
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
        <!-- 0. BREADCRUMB BAR                         -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
            <span class="text-slate-600 dark:text-slate-400 font-medium">Cockpit Bisnis 360° Real-Time</span>
        </nav>

        <!-- ========================================== -->
        <!-- 1. TOP HEADER BANNER & ACTION COCKPIT      -->
        <!-- ========================================== -->
        <div
            class="bg-white dark:bg-slate-900/90 p-6 sm:p-7 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative overflow-hidden transition-colors">
            
            <!-- Subtle decorative background glow -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 z-10 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                        <i data-lucide="gauge" class="w-3.5 h-3.5"></i>
                        <span>Cockpit 360° Real-Time</span>
                    </span>
                    @if ($stats['is_shift_open'])
                        <span
                            class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1.5 bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border-teal-200/90 dark:border-teal-800/90">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                            <span>Kasir Buka ({{ $stats['active_shift']->user?->name ?? 'Kasir' }} • sejak {{ $stats['active_shift']->opened_at?->format('H:i') }})</span>
                        </span>
                    @else
                        <span
                            class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                            <span>Kasir Belum Buka</span>
                        </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Selamat Datang di Cooca UMKM, {{ auth()->user()->name ?? 'Owner' }}!
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Pemantauan performa bisnis operasional harian outlet <strong>{{ $business->name }}</strong> secara instan &amp; terintegrasi.
                </p>
            </div>

            <!-- Quick Action Buttons Hub -->
            <div class="flex flex-wrap items-center gap-2.5 z-10 w-full lg:w-auto">
                @if(\App\Support\Context::hasPermission('pos.terminal'))
                <a href="{{ route('pos.terminal') }}"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Terminal Kasir POS</span>
                </a>
                @endif

                @if(\App\Support\Context::hasPermission('expenses.manage') || \App\Support\Context::hasPermission('expenses.view'))
                <button type="button" @click="$dispatch('open-quick-expense')"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <i data-lucide="receipt" class="w-4 h-4 text-rose-500 dark:text-rose-400"></i>
                    <span>+ Catat Beban</span>
                </button>
                @endif

                @if(\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::hasPermission('purchasing.manage'))
                <button type="button" @click="$dispatch('open-quick-stockin')"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <i data-lucide="package-plus" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>+ Beli Stok</span>
                </button>
                @endif

                @if(\App\Support\Context::hasPermission('invoices.create'))
                <a href="{{ route('invoices.create') }}"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4 text-blue-500 dark:text-blue-400"></i>
                    <span>+ Buat Faktur</span>
                </a>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. THE 4 COMMAND PILLARS (TOP KPIS)        -->
        <!-- ========================================== -->
        <div id="tour-dashboard-kpis" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Pillar 1: Penjualan & Kasir -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs hover:border-emerald-500/40 dark:hover:border-emerald-500/50 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Omzet Hari Ini</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                                Real-Time
                            </span>
                        </div>
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                        Rp <span
                            x-text="Number(stats.today_sales).toLocaleString('id-ID')">{{ number_format($stats['today_sales'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span><strong class="font-mono font-bold text-slate-900 dark:text-white" x-text="stats.today_transactions_count">{{ $stats['today_transactions_count'] }}</strong> Transaksi</span>
                    <a href="{{ route('pos.terminal') }}"
                        class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-bold flex items-center gap-1 transition-colors">
                        <span>Terminal Kasir</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- Pillar 2: Laba & Margin HPP -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs hover:border-teal-500/40 dark:hover:border-teal-500/50 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Estimasi Laba Bersih</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border-teal-200/90 dark:border-teal-800/90">
                                MTD
                            </span>
                        </div>
                        <div
                            class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/80 dark:border-teal-800/80 flex items-center justify-center text-teal-600 dark:text-teal-400 group-hover:scale-105 transition-transform">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-3xl font-black font-mono tracking-tight truncate"
                        :class="stats.month_net_profit_est >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400'">
                        Rp <span
                            x-text="Number(stats.month_net_profit_est).toLocaleString('id-ID')">{{ number_format($stats['month_net_profit_est'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Avg Margin: <strong class="text-emerald-600 dark:text-emerald-400 font-mono font-bold"><span
                                x-text="stats.avg_margin_pct">{{ $stats['avg_margin_pct'] }}</span>%</strong></span>
                    <button type="button" @click="$dispatch('open-quick-expense')"
                        class="text-xs text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold flex items-center gap-0.5 transition-colors cursor-pointer">
                        <span>+ Beban</span>
                    </button>
                </div>
            </div>

            <!-- Pillar 3: Inventori & Valuasi Stok -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs hover:border-cyan-500/40 dark:hover:border-cyan-500/50 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Valuasi Aset Stok</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700">
                                Real-Time
                            </span>
                        </div>
                        <div
                            class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/80 flex items-center justify-center text-cyan-600 dark:text-cyan-400 group-hover:scale-105 transition-transform">
                            <i data-lucide="warehouse" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                        Rp <span
                            x-text="Number(stats.total_stock_valuation || stats.inventory_valuation || 0).toLocaleString('id-ID')">{{ number_format($stats['total_stock_valuation'] ?? $stats['inventory_valuation'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs flex items-center justify-between">
                    <template x-if="stats.low_stock_count > 0">
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90">
                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                            <span x-text="stats.low_stock_count + ' Stok Menipis'">{{ $stats['low_stock_count'] > 0 ? $stats['low_stock_count'] . ' Stok Menipis' : '' }}</span>
                        </span>
                    </template>
                    <template x-if="stats.low_stock_count <= 0">
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            <span>Stok Aman</span>
                        </span>
                    </template>
                    <button type="button" @click="$dispatch('open-quick-stockin')"
                        class="text-xs text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 font-bold flex items-center gap-0.5 transition-colors cursor-pointer">
                        <span>+ Beli</span>
                    </button>
                </div>
            </div>

            <!-- Pillar 4: Piutang & Faktur Klien -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs hover:border-purple-500/40 dark:hover:border-purple-500/50 transition-all flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Piutang Belum Lunas</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/90 dark:border-purple-800/90">
                                Tertunda
                            </span>
                        </div>
                        <div
                            class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/80 flex items-center justify-center text-purple-600 dark:text-purple-400 group-hover:scale-105 transition-transform">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                        Rp <span
                            x-text="Number(stats.unpaid_invoices_amount).toLocaleString('id-ID')">{{ number_format($stats['unpaid_invoices_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span><strong class="font-mono font-bold text-slate-900 dark:text-white" x-text="stats.unpaid_invoices_count">{{ $stats['unpaid_invoices_count'] }}</strong> Faktur</span>
                    <a href="{{ route('invoices.create') }}" class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-bold flex items-center gap-1 transition-colors">
                        <span>+ Faktur</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. VISUAL 7-DAYS SALES CHART & AI ADVISOR  -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 7-Days Visual Sales Trend -->
            <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs lg:col-span-2 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3 mb-4">
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Tren Penjualan 7 Hari Terakhir</span>
                        </h2>
                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pantauan omset harian kasir POS &amp; pesanan toko</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Omset Bersih</span>
                        </span>
                        <a href="{{ route('pos.orders.index') }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition cursor-pointer flex items-center gap-1">
                            <span>Riwayat</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Visual Bar Chart with Track Slots -->
                <div class="h-52 pt-4 flex items-end justify-between gap-2 sm:gap-4 px-1 sm:px-2">
                    @foreach ($sevenDaysTrend as $day)
                        <div class="flex-1 flex flex-col items-center gap-2 group h-full justify-end min-w-0">
                            <!-- Tooltip -->
                            <div
                                class="opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 dark:bg-slate-800 border border-slate-700 dark:border-slate-600 text-white font-mono text-[10px] px-2.5 py-1 rounded-lg shadow-xl pointer-events-none whitespace-nowrap z-20">
                                Rp {{ number_format($day['amount'], 0, ',', '.') }}
                            </div>
                            <!-- Bar with background track slot -->
                            <div class="w-full max-w-[48px] h-36 rounded-xl bg-slate-100 dark:bg-slate-800/60 relative overflow-hidden flex items-end border border-slate-200/50 dark:border-slate-700/50">
                                <div class="w-full rounded-b-lg rounded-t-xl bg-gradient-to-t from-emerald-600 to-teal-500 hover:to-emerald-400 transition-all duration-300 relative group-hover:shadow-md group-hover:shadow-emerald-500/20"
                                    style="height: {{ max((float)$day['height_pct'], 4) }}%;">
                                </div>
                            </div>
                            <!-- Date labels -->
                            <div class="text-center w-full min-w-0 pt-1">
                                <div class="text-[10px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 truncate">{{ $day['day_name'] }}</div>
                                <div class="text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-500 font-mono truncate">{{ $day['date_formatted'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cooca AI Business Advisor Card -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-purple-200/90 dark:border-purple-800/80 shadow-xs flex flex-col justify-between space-y-4 transition-colors">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-purple-100 dark:border-purple-900/40 pb-3 mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/80 text-purple-600 dark:text-purple-300 flex items-center justify-center">
                                <i data-lucide="bot" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">AI Business Advisor</h2>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/90 dark:border-purple-800/90">
                            SMART AI
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs leading-relaxed">
                        @if ($stats['low_margin_count'] > 0)
                            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/90 dark:border-amber-800/90 text-amber-900 dark:text-amber-200">
                                <div class="font-bold flex items-center gap-1.5 mb-1 text-xs">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                                    <span>Margin Tipis Terdeteksi</span>
                                </div>
                                <p class="text-[11px] text-amber-800 dark:text-amber-300/90 leading-normal">
                                    Ada <strong>{{ $stats['low_margin_count'] }} produk</strong> dengan margin di bawah 25%. Disarankan mengecek harga bahan baku atau menaikkan harga jual di kasir.
                                </p>
                            </div>
                        @else
                            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/90 dark:border-emerald-800/90 text-emerald-900 dark:text-emerald-200">
                                <div class="font-bold flex items-center gap-1.5 mb-1 text-xs">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                    <span>Kesehatan Margin Prima</span>
                                </div>
                                <p class="text-[11px] text-emerald-800 dark:text-emerald-300/90 leading-normal">
                                    Rata-rata margin kotor produk berada di angka
                                    <strong class="font-mono font-bold">{{ $stats['avg_margin_pct'] }}%</strong> (Standar ideal UMKM &amp; Kuliner).
                                </p>
                            </div>
                        @endif

                        @if ($stats['low_stock_count'] > 0)
                            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/90 dark:border-rose-800/90 text-rose-900 dark:text-rose-200">
                                <div class="font-bold flex items-center gap-1.5 mb-1 text-xs">
                                    <i data-lucide="package-x" class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400 shrink-0"></i>
                                    <span>Peringatan Stok Rendah</span>
                                </div>
                                <p class="text-[11px] text-rose-800 dark:text-rose-300/90 leading-normal">
                                    Terdapat <strong>{{ $stats['low_stock_count'] }} item stok</strong> hampir habis. Segera lakukan pesanan pembelian agar tidak menghambat penjualan.
                                </p>
                            </div>
                        @else
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 text-[11px] leading-normal">
                                💡 <strong>Tips Cooca:</strong> Gunakan kalkulator HPP fitur <em>Ojek Online</em> untuk otomatis mengompensasi potongan komisi aplikasi 20%.
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('pos.ai.index') }}"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 shadow-sm shadow-purple-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 w-full">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Buka AI Cockpit &amp; Analisis POS</span>
                </a>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 4. LIVE TRANSACTION FEED & CATALOG HEALTH  -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Left: Recent Sales & POS Orders Feed -->
            <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Transaksi Kasir Terbaru</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Order dan transaksi hari ini</p>
                        </div>
                    </div>
                    <a href="{{ route('pos.orders.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-bold flex items-center gap-1 transition-colors">
                        <span>Semua Transaksi</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                @if ($recentPosOrders->isEmpty())
                    <div class="text-center py-12 px-4 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
                            <i data-lucide="receipt" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Belum Ada Transaksi Hari Ini</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Buka terminal kasir POS untuk melayani pelanggan toko.</p>
                        </div>
                        <a href="{{ route('pos.terminal') }}"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer inline-flex items-center gap-1.5 mt-2">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                            <span>Buka Terminal Kasir</span>
                        </a>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach ($recentPosOrders as $order)
                            <div
                                class="p-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/90 dark:bg-slate-950/80 dark:hover:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700 flex items-center justify-between text-xs transition">
                                <div class="space-y-0.5">
                                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="font-mono">{{ $order->order_number }}</span>
                                        <span
                                            class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">({{ $order->customer_name_guest ?? 'Pelanggan Umum' }})</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <span>{{ $order->created_at?->diffForHumans() ?? 'Baru saja' }}</span>
                                        <span>&bull;</span>
                                        <span
                                            class="capitalize text-slate-700 dark:text-slate-300 font-medium">{{ $order->order_type ?? 'Dine In' }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                    </div>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[9px] font-bold border inline-flex items-center gap-0.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90 uppercase">
                                        {{ $order->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Recent Products & Catalog Margins -->
            <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/80 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                            <i data-lucide="package" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Katalog &amp; Margin Produk</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">HPP dan persentase keuntungan</p>
                        </div>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-xs text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 font-bold flex items-center gap-1 transition-colors">
                        <span>Semua ({{ $stats['total_products'] }})</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                @if ($recentProducts->isEmpty())
                    <div class="text-center py-12 px-4 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-400">
                            <i data-lucide="box" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Belum Ada Produk Terdaftar</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan produk baru untuk mulai menghitung HPP &amp; menjual di kasir.</p>
                        </div>
                        <a href="{{ route('calculator.index') }}"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-sm shadow-blue-600/20 active:scale-[0.98] transition cursor-pointer inline-flex items-center gap-1.5 mt-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Hitung &amp; Daftarkan Produk Baru</span>
                        </a>
                    </div>
                @else
                    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs min-w-[420px]">
                                <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 whitespace-nowrap">
                                    <tr>
                                        <th class="px-4 py-2.5 font-bold">Produk</th>
                                        <th class="px-4 py-2.5 font-bold text-right">HPP Dasar</th>
                                        <th class="px-4 py-2.5 font-bold text-right">Harga Jual</th>
                                        <th class="px-4 py-2.5 font-bold text-right">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono text-xs">
                                    @foreach ($recentProducts as $prod)
                                        @php
                                            $margin =
                                                $prod->selling_price > 0 && $prod->base_cost > 0
                                                    ? round(
                                                        (($prod->selling_price - $prod->base_cost) / $prod->selling_price) *
                                                            100,
                                                        1,
                                                    )
                                                    : null;
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                            <td class="px-4 py-2.5 font-sans font-bold text-slate-900 dark:text-white">
                                                <a href="{{ route('products.bom', $prod->slug) }}"
                                                    class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                                    {{ $prod->name }}
                                                </a>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">
                                                    {{ $prod->category?->name ?? 'pcs' }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-mono text-slate-600 dark:text-slate-300">
                                                Rp {{ number_format((float) $prod->base_cost, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                                Rp {{ number_format((float) $prod->selling_price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                                @if ($margin !== null)
                                                    <span
                                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 font-mono {{ $margin >= 40 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90' : ($margin >= 25 ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border-teal-200/90 dark:border-teal-800/90' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90') }}">
                                                        {{ $margin }}%
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-mono">-</span>
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
        <!-- 5. INTERACTIVE QUICK CALCULATOR & ENGINE   -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Quick Instant HPP Calculator Widget -->
            <div id="tour-quick-calc"
                class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl lg:col-span-2 relative overflow-hidden border border-slate-200/90 dark:border-slate-800/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Kalkulator Cepat: HPP &amp; Target Margin</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Simulasikan harga jual rekomendasi instan berdasarkan target margin kotor</p>
                        </div>
                    </div>
                    <a href="{{ route('calculator.index') }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition cursor-pointer flex items-center gap-1">
                        <span>Full Engine</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Estimasi HPP Dasar (Rp)</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-xs font-mono font-bold">
                                Rp
                            </div>
                            <input type="number" x-model.number="quickHpp" min="0" step="1000"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl pl-10 pr-3.5 py-2.5 text-xs font-mono font-bold text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Target Gross Margin (%)</label>
                            <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="quickMargin + '%'"></span>
                        </div>
                        <input type="range" x-model.number="quickMargin" min="5" max="80" step="1"
                            class="w-full h-2 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                        <!-- Quick Margin Presets -->
                        <div class="flex items-center gap-2 mt-2.5">
                            <span class="text-[10px] text-slate-400 font-bold uppercase">Preset:</span>
                            <template x-for="preset in [30, 40, 50, 60]">
                                <button type="button" @click="quickMargin = preset"
                                    :class="quickMargin === preset ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold border transition-colors cursor-pointer"
                                    x-text="preset + '%'"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Instant Calculation Result Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 transition-colors">
                    <div>
                        <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Harga Rekomendasi</div>
                        <div class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                            Rp <span x-text="calculatedPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Rumus: HPP / (1 - Margin%)</div>
                    </div>

                    <div>
                        <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Laba Kotor / Pcs</div>
                        <div class="text-lg sm:text-xl font-black text-teal-600 dark:text-teal-400 font-mono mt-1">
                            Rp <span x-text="grossProfit.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Nominal untung kotor</div>
                    </div>

                    <div>
                        <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Markup Setara</div>
                        <div class="text-lg sm:text-xl font-black text-blue-600 dark:text-blue-400 font-mono mt-1">
                            <span x-text="markupEquivalent"></span>%
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Dari modal HPP dasar</div>
                    </div>
                </div>
            </div>

            <!-- Quick System Shortcuts -->
            <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between space-y-4">
                <div>
                    <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-1">Aksi Cepat Sistem</h2>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Pintasan modul analisis &amp; operasional</p>
                </div>

                <div class="space-y-2.5">
                    @if(\App\Support\Context::hasPermission('materials.view'))
                    <a href="{{ route('materials.index') }}"
                        class="p-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/90 dark:bg-slate-950/80 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800 hover:border-emerald-500/40 dark:hover:border-emerald-500/40 flex items-center justify-between text-xs font-semibold text-slate-800 dark:text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>Bahan Baku &amp; Riwayat Harga</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"></i>
                    </a>
                    @endif

                    @if(\App\Support\Context::hasPermission('costing.view_margin'))
                    <a href="{{ route('simulator.index') }}"
                        class="p-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/90 dark:bg-slate-950/80 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800 hover:border-blue-500/40 dark:hover:border-blue-500/40 flex items-center justify-between text-xs font-semibold text-slate-800 dark:text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200/60 dark:border-blue-800/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>What-If Scenario Simulator</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"></i>
                    </a>

                    <a href="{{ route('profitability.index') }}"
                        class="p-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/90 dark:bg-slate-950/80 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800 hover:border-purple-500/40 dark:hover:border-purple-500/40 flex items-center justify-between text-xs font-semibold text-slate-800 dark:text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/60 border border-purple-200/60 dark:border-purple-800/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>Kalkulator BEP &amp; Titik Impas</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors"></i>
                    </a>
                    @endif

                    @if(\App\Support\Context::hasPermission('reports.view'))
                    <a href="{{ route('reports.index') }}"
                        class="p-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/90 dark:bg-slate-950/80 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800 hover:border-amber-500/40 dark:hover:border-amber-500/40 flex items-center justify-between text-xs font-semibold text-slate-800 dark:text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-800/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>Laporan Laba &amp; HPP Produk</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
