@extends('layouts.app', [
    'title' => 'Dashboard Bisnis',
    'headerTitle' => 'Ringkasan Bisnis & Cockpit Operasional',
    'headerSubtitle' => 'Pemantauan menyeluruh omzet, HPP, margin laba, inventori, dan kasir ' . $business->name,
])

@section('content')
    <div class="space-y-6" x-data="{
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
        <!-- 1. TOP HEADER BANNER & ACTION COCKPIT -->
        <!-- ========================================== -->
        <div
            class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative overflow-hidden shadow-xl">
            <div class="space-y-1.5 z-10">
                <div class="flex items-center gap-2">
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Cockpit 360° Real-Time
                    </span>
                    @if ($stats['is_shift_open'])
                        <span
                            class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                            <span>Kasir Buka ({{ $stats['active_shift']->user?->name ?? 'Kasir' }} • sejak
                                {{ $stats['active_shift']->opened_at?->format('H:i') }})</span>
                        </span>
                    @else
                        <span
                            class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-800 text-slate-400 border border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                            <span>Kasir Belum Buka</span>
                        </span>
                    @endif
                </div>
                <h2 class="text-xl lg:text-2xl font-black text-white tracking-tight">
                    Selamat Datang di Cooca Core, {{ auth()->user()->name ?? 'Owner' }}!
                </h2>
                <p class="text-xs text-slate-400">
                    Berikut adalah ringkasan performa bisnis Anda untuk outlet <strong>{{ $business->name }}</strong> per
                    hari ini.
                </p>
            </div>

            <!-- Quick Action Buttons Hub -->
            <div class="flex flex-wrap items-center gap-2.5 z-10">
                <a href="{{ route('pos.terminal') }}"
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Terminal Kasir POS</span>
                </a>

                <button type="button" @click="$dispatch('open-quick-expense')"
                    class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-rose-500/40 text-white font-bold text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="receipt" class="w-4 h-4 text-rose-400"></i>
                    <span>+ Catat Beban</span>
                </button>

                <button type="button" @click="$dispatch('open-quick-stockin')"
                    class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-emerald-500/40 text-white font-bold text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="package-plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>+ Beli Stok</span>
                </button>

                <a href="{{ route('invoices.create') }}"
                    class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-blue-500/40 text-white font-bold text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="receipt" class="w-4 h-4 text-blue-400"></i>
                    <span>Buat Faktur</span>
                </a>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. THE 4 COMMAND PILLARS (TOP KPIS) -->
        <!-- ========================================== -->
        <div id="tour-dashboard-kpis" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Pillar 1: Penjualan & Kasir -->
            <div
                class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-slate-800 hover:border-emerald-500/40 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Omzet Hari Ini</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                        Rp <span
                            x-text="Number(stats.today_sales).toLocaleString('id-ID')">{{ number_format($stats['today_sales'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div
                    class="mt-3 pt-3 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
                    <span><span x-text="stats.today_transactions_count">{{ $stats['today_transactions_count'] }}</span>
                        Transaksi</span>
                    <a href="{{ route('pos.terminal') }}"
                        class="text-[10px] text-emerald-400 hover:underline font-bold flex items-center gap-1">
                        <span>Kasir</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

            <!-- Pillar 2: Laba & Margin HPP -->
            <div
                class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-slate-800 hover:border-teal-500/40 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Laba Bersih (MTD)</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 group-hover:scale-110 transition-transform">
                            <i data-lucide="trending-up" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-2xl lg:text-3xl font-extrabold font-mono tracking-tight text-teal-300"
                        :class="stats.month_net_profit_est >= 0 ? 'text-teal-300' : 'text-rose-400'">
                        Rp <span
                            x-text="Number(stats.month_net_profit_est).toLocaleString('id-ID')">{{ number_format($stats['month_net_profit_est'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div
                    class="mt-3 pt-3 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
                    <span>Margin: <strong class="text-emerald-400"><span
                                x-text="stats.avg_margin_pct">{{ $stats['avg_margin_pct'] }}</span>%</strong></span>
                    <button type="button" @click="$dispatch('open-quick-expense')"
                        class="text-[10px] text-rose-400 hover:text-rose-300 font-bold flex items-center gap-0.5">
                        <span>+ Beban</span>
                    </button>
                </div>
            </div>

            <!-- Pillar 3: Inventori & Valuasi Stok -->
            <div
                class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-slate-800 hover:border-cyan-500/40 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Valuasi Aset Stok</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 group-hover:scale-110 transition-transform">
                            <i data-lucide="warehouse" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                        Rp <span
                            x-text="Number(stats.total_stock_valuation).toLocaleString('id-ID')">{{ number_format($stats['total_stock_valuation'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-800/80 text-xs flex items-center justify-between">
                    <template x-if="stats.low_stock_count > 0">
                        <span class="text-amber-400 font-bold flex items-center gap-1 text-[11px]">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            <span
                                x-text="stats.low_stock_count + ' Stok Menipis'">{{ $stats['low_stock_count'] > 0 ? $stats['low_stock_count'] . ' Stok Menipis' : '' }}</span>
                        </span>
                    </template>
                    <template x-if="stats.low_stock_count <= 0">
                        <span class="text-emerald-400 font-medium flex items-center gap-1 text-[11px]">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Stok Aman</span>
                        </span>
                    </template>
                    <button type="button" @click="$dispatch('open-quick-stockin')"
                        class="text-[10px] text-cyan-400 hover:text-cyan-300 font-bold flex items-center gap-0.5">
                        <span>+ Beli</span>
                    </button>
                </div>
            </div>

            <!-- Pillar 4: Piutang & Faktur Klien -->
            <div
                class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-slate-800 hover:border-purple-500/40 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Piutang Belum Lunas</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 group-hover:scale-110 transition-transform">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-2xl lg:text-3xl font-extrabold text-white font-mono tracking-tight">
                        Rp <span
                            x-text="Number(stats.unpaid_invoices_amount).toLocaleString('id-ID')">{{ number_format($stats['unpaid_invoices_amount'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div
                    class="mt-3 pt-3 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
                    <span><span x-text="stats.unpaid_invoices_count">{{ $stats['unpaid_invoices_count'] }}</span> Faktur Tertunda</span>
                    <a href="{{ route('invoices.create') }}" class="text-[10px] text-purple-400 hover:underline font-bold">
                        + Faktur
                    </a>
                </div>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- 3. VISUAL 7-DAYS SALES CHART & AI ADVISOR -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 7-Days Visual Sales Trend -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 lg:col-span-2 space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-4">
                    <div>
                        <h3 class="text-base font-black text-white flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 text-emerald-400"></i>
                            <span>Tren Penjualan 7 Hari Terakhir</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pantauan omset harian kasir POS & order toko</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5 text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>Omset Bersih</span>
                        </span>
                        <a href="{{ route('pos.orders.index') }}"
                            class="text-emerald-400 hover:underline font-semibold text-xs">
                            Lihat Semua Riwayat
                        </a>
                    </div>
                </div>

                <!-- CSS/SVG Visual Bar Chart -->
                <div class="h-44 pt-4 flex items-end justify-between gap-2 sm:gap-4 px-2">
                    @foreach ($sevenDaysTrend as $day)
                        <div class="flex-1 flex flex-col items-center gap-2 group h-full justify-end">
                            <div
                                class="opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 border border-slate-700 text-white font-mono text-[10px] px-2 py-1 rounded-md shadow-lg pointer-events-none whitespace-nowrap z-20">
                                Rp {{ number_format($day['amount'], 0, ',', '.') }}
                            </div>
                            <div class="w-full max-w-[48px] rounded-xl bg-gradient-to-t from-emerald-600/60 to-teal-400 hover:to-emerald-300 transition-all duration-300 relative group-hover:shadow-lg group-hover:shadow-emerald-500/30"
                                style="height: {{ $day['height_pct'] }}%;">
                            </div>
                            <div class="text-center">
                                <div class="text-xs font-bold text-slate-300">{{ $day['day_name'] }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $day['date_formatted'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cooca AI Business Advisor Card -->
            <div
                class="glass-card p-6 rounded-3xl border border-purple-500/30 bg-gradient-to-b from-purple-950/20 via-slate-900 to-slate-950 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-purple-500/20 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                <i data-lucide="bot" class="w-4 h-4"></i>
                            </div>
                            <h3 class="text-sm font-black text-white">Cooca AI Business Advisor</h3>
                        </div>
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-black bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            SMART AI
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs text-slate-300 leading-relaxed">
                        @if ($stats['low_margin_count'] > 0)
                            <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-200">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Margin Tipis Terdeteksi</span>
                                </div>
                                <p class="text-[11px] text-amber-300/80">
                                    Ada {{ $stats['low_margin_count'] }} produk dengan margin di bawah 25%. Disarankan
                                    mengecek
                                    harga bahan atau menaikkan harga jual di kasir.
                                </p>
                            </div>
                        @else
                            <div class="p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-200">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Kesehatan Harga Prima</span>
                                </div>
                                <p class="text-[11px] text-emerald-300/80">
                                    Rata-rata margin kotor produk berada di angka
                                    <strong>{{ $stats['avg_margin_pct'] }}%</strong> (Standar ideal UMKM & Kuliner).
                                </p>
                            </div>
                        @endif

                        @if ($stats['low_stock_count'] > 0)
                            <div class="p-3 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-200">
                                <div class="font-bold flex items-center gap-1.5 mb-1">
                                    <i data-lucide="package-x" class="w-3.5 h-3.5 text-rose-400"></i>
                                    <span>Peringatan Stok Rendah</span>
                                </div>
                                <p class="text-[11px] text-rose-300/80">
                                    Terdapat {{ $stats['low_stock_count'] }} item stok yang hampir habis. Segera lakukan
                                    pemesanan ke supplier agar tidak menghambat penjualan.
                                </p>
                            </div>
                        @else
                            <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 text-slate-300 text-[11px]">
                                💡 <strong>Tips Cooca:</strong> Gunakan kalkulator HPP fitur <em>Ojek Online</em> untuk
                                otomatis
                                mengompensasi potongan komisi aplikasi 20%.
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('pos.ai.index') }}"
                    class="w-full py-2.5 rounded-2xl bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white font-black text-xs text-center shadow-lg shadow-purple-500/20 flex items-center justify-center gap-1.5 transition-all">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Buka AI Cockpit & Analisis POS</span>
                </a>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 4. LIVE TRANSACTION FEED & CATALOG HEALTH -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Left: Recent Sales & POS Orders Feed -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-emerald-400"></i>
                        <h3 class="text-base font-bold text-white">Transaksi Kasir Terbaru</h3>
                    </div>
                    <a href="{{ route('pos.orders.index') }}" class="text-xs text-emerald-400 hover:underline">Semua
                        Transaksi</a>
                </div>

                @if ($recentPosOrders->isEmpty())
                    <div class="text-center py-10 text-slate-500 text-xs">
                        <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-2 text-slate-600"></i>
                        <div>Belum ada transaksi kasir hari ini.</div>
                        <a href="{{ route('pos.terminal') }}"
                            class="text-emerald-400 font-semibold hover:underline block mt-1">+ Buka Terminal Kasir</a>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach ($recentPosOrders as $order)
                            <div
                                class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between text-xs hover:border-slate-700 transition">
                                <div class="space-y-0.5">
                                    <div class="font-bold text-white flex items-center gap-2">
                                        <span>{{ $order->order_number }}</span>
                                        <span
                                            class="text-[10px] text-slate-400 font-normal">({{ $order->customer_name_guest ?? 'Pelanggan Umum' }})</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-2">
                                        <span>{{ $order->created_at?->diffForHumans() ?? 'Baru saja' }}</span>
                                        <span>&bull;</span>
                                        <span
                                            class="capitalize text-slate-300">{{ $order->order_type ?? 'Dine In' }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-mono font-bold text-emerald-400">
                                        Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                        {{ $order->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right: Recent Products & Catalog Margins -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="package" class="w-5 h-5 text-cyan-400"></i>
                        <h3 class="text-base font-bold text-white">Katalog & Margin Produk</h3>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-xs text-cyan-400 hover:underline">Semua Produk
                        ({{ $stats['total_products'] }})</a>
                </div>

                @if ($recentProducts->isEmpty())
                    <div class="text-center py-10 text-slate-500 text-xs">
                        <i data-lucide="box" class="w-8 h-8 mx-auto mb-2 text-slate-600"></i>
                        <div>Belum ada produk terdaftar.</div>
                        <a href="{{ route('calculator.index') }}"
                            class="text-cyan-400 font-semibold hover:underline block mt-1">+ Hitung & Simpan Produk
                            Baru</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-800">
                                    <th class="pb-2.5 font-semibold">Produk</th>
                                    <th class="pb-2.5 font-semibold">HPP Dasar</th>
                                    <th class="pb-2.5 font-semibold">Harga Jual</th>
                                    <th class="pb-2.5 font-semibold text-right">Margin %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-mono">
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
                                    <tr class="hover:bg-slate-900/40 transition">
                                        <td class="py-2.5 font-sans font-medium text-white">
                                            <a href="{{ route('products.bom', $prod->slug) }}"
                                                class="hover:text-emerald-400 transition">
                                                {{ $prod->name }}
                                            </a>
                                            <div class="text-[10px] text-slate-500">{{ $prod->category?->name ?? 'pcs' }}
                                            </div>
                                        </td>
                                        <td class="py-2.5 text-slate-300">
                                            Rp {{ number_format((float) $prod->base_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 font-bold text-white">
                                            Rp {{ number_format((float) $prod->selling_price, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 text-right font-bold">
                                            @if ($margin !== null)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] {{ $margin >= 25 ? 'bg-emerald-500/10 text-emerald-300' : 'bg-rose-500/10 text-rose-300' }}">
                                                    {{ $margin }}%
                                                </span>
                                            @else
                                                <span class="text-slate-500 text-[10px]">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 5. INTERACTIVE QUICK CALCULATOR & ENGINE SHORTCUTS -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Quick Instant HPP Calculator Widget -->
            <div id="tour-quick-calc"
                class="glass-card p-6 rounded-3xl lg:col-span-2 relative overflow-hidden border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Kalkulator Cepat: HPP & Target Margin</h3>
                            <p class="text-xs text-slate-400">Simulasikan harga jual rekomendasi instan berdasarkan target
                                margin laba kotor</p>
                        </div>
                    </div>
                    <a href="{{ route('calculator.index') }}"
                        class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                        <span>Full Engine</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Estimasi
                            HPP
                            Dasar (Rp)</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs font-bold">
                                Rp
                            </div>
                            <input type="number" x-model.number="quickHpp" min="0" step="1000"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/90 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-sm font-mono text-white">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Target Gross
                                Margin (%)</label>
                            <span class="text-xs font-mono font-bold text-emerald-400" x-text="quickMargin + '%'"></span>
                        </div>
                        <input type="range" x-model.number="quickMargin" min="5" max="80" step="1"
                            class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                    </div>
                </div>

                <!-- Instant Calculation Result Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-950 border border-slate-800">
                    <div>
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Harga Rekomendasi</div>
                        <div class="text-xl font-extrabold text-emerald-400 font-mono mt-1">
                            Rp <span x-text="calculatedPrice.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Rumus: HPP / (1 - Margin%)</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Laba Kotor / Pcs</div>
                        <div class="text-xl font-extrabold text-teal-300 font-mono mt-1">
                            Rp <span x-text="grossProfit.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Nominal untung bersih</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Markup Setara</div>
                        <div class="text-xl font-extrabold text-blue-400 font-mono mt-1">
                            <span x-text="markupEquivalent"></span>%
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Dari modal dasar</div>
                    </div>
                </div>
            </div>

            <!-- Quick System Shortcuts -->
            <div class="glass-card p-6 rounded-3xl border border-slate-800 flex flex-col justify-between space-y-4">
                <div>
                    <h3 class="text-base font-bold text-white mb-1">Aksi Cepat Sistem</h3>
                    <p class="text-xs text-slate-400">Akses modul lanjutan untuk operasional bisnis</p>
                </div>

                <div class="space-y-2.5">
                    <a href="{{ route('materials.index') }}"
                        class="p-3 rounded-2xl bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/40 flex items-center justify-between text-xs font-semibold text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="boxes" class="w-4 h-4 text-emerald-400"></i>
                            <span>Bahan Baku & Riwayat Harga</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-500 group-hover:text-emerald-400 transition-colors"></i>
                    </a>

                    <a href="{{ route('simulator.index') }}"
                        class="p-3 rounded-2xl bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-blue-500/40 flex items-center justify-between text-xs font-semibold text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="sliders" class="w-4 h-4 text-blue-400"></i>
                            <span>What-If Scenario Simulator</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-500 group-hover:text-blue-400 transition-colors"></i>
                    </a>

                    <a href="{{ route('profitability.index') }}"
                        class="p-3 rounded-2xl bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-purple-500/40 flex items-center justify-between text-xs font-semibold text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-purple-400"></i>
                            <span>Kalkulator BEP & Titik Impas</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-500 group-hover:text-purple-400 transition-colors"></i>
                    </a>

                    <a href="{{ route('reports.index') }}"
                        class="p-3 rounded-2xl bg-slate-950/80 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/40 flex items-center justify-between text-xs font-semibold text-white transition-all group">
                        <div class="flex items-center gap-3">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-amber-400"></i>
                            <span>Laporan Laba & HPP Produk</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
