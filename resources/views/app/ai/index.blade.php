@extends('layouts.app', ['title' => 'AI POS & Prediksi Penjualan'])

@section('content')
<div class="space-y-8" x-data="aiPosApp()">
    
    <!-- AI POS Header & Status Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 shadow-sm">
                    AI-POWERED POS
                </span>
                <span class="text-xs text-slate-400 font-mono">Statistical & Heuristic Machine Learning Engine</span>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight mt-1">Pusat Intelijensi & Prediksi POS</h1>
            <p class="text-sm text-slate-400 mt-1">Analisis prediktif omzet, peramalan kehabisan stok, deteksi fraud, matriks profitabilitas BCG, dan rekomendasi harga pintar.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pos.terminal') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-2">
                <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-400"></i>
                <span>Terminal Kasir</span>
            </a>
            <a href="{{ route('pos.reports.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                <span>Laporan Kasir</span>
            </a>
        </div>
    </div>

    <!-- 1. Interactive Natural Language Assistant: "Tanya AI POS" -->
    <div class="glass-card rounded-3xl p-6 border border-emerald-500/30 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/20 shadow-2xl relative overflow-hidden">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center font-black shadow-lg shadow-emerald-500/25 shrink-0">
                <i data-lucide="bot" class="w-7 h-7"></i>
            </div>
            <div class="flex-1 space-y-3">
                <div>
                    <h3 class="font-black text-lg text-white flex items-center gap-2">
                        <span>Asisten Natural Language AI POS</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </h3>
                    <p class="text-xs text-slate-400">Tanyakan apapun tentang performa penjualan, prediksi stok, perilaku pelanggan, atau kecurigaan transaksi dalam Bahasa Indonesia.</p>
                </div>

                <!-- Input Box -->
                <form @submit.prevent="askAi()" class="relative">
                    <input type="text" 
                           x-model="nlQuery" 
                           placeholder="Contoh: Berapa penjualan hari ini? / Produk apa yang stoknya mau habis?..." 
                           class="w-full bg-slate-950/80 border border-slate-700/80 rounded-2xl pl-4 pr-28 py-3.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 shadow-inner">
                    <button type="submit" 
                            :disabled="isAsking || !nlQuery.trim()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-md transition disabled:opacity-40 flex items-center gap-1.5">
                        <span x-show="!isAsking">Tanya AI</span>
                        <span x-show="isAsking">Menganalisis...</span>
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                <!-- Quick Query Suggestions Chips -->
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 font-semibold">Pertanyaan Cepat:</span>
                    <button @click="quickAsk('Berapa penjualan hari ini?')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition border border-slate-700/60">
                        📈 Penjualan Hari Ini
                    </button>
                    <button @click="quickAsk('Produk apa yang stoknya mau habis?')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition border border-slate-700/60">
                        ⚠️ Prediksi Stok Habis
                    </button>
                    <button @click="quickAsk('Apakah ada transaksi mencurigakan atau fraud?')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition border border-slate-700/60">
                        🛡️ Deteksi Fraud & Void
                    </button>
                    <button @click="quickAsk('Prediksi penjualan minggu depan')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition border border-slate-700/60">
                        🔮 Proyeksi Minggu Depan
                    </button>
                    <button @click="quickAsk('Siapa kasir dengan performa terbaik?')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition border border-slate-700/60">
                        👤 Performa Kasir
                    </button>
                    <button @click="quickAsk('Buatkan draf invoice')" class="px-2.5 py-1 rounded-lg bg-purple-950/40 hover:bg-purple-900/50 text-purple-300 text-xs font-bold transition border border-purple-500/40">
                        ⚡ Buatkan Draf Invoice
                    </button>
                    <button @click="quickAsk('Buatkan draf penawaran')" class="px-2.5 py-1 rounded-lg bg-emerald-950/40 hover:bg-emerald-900/50 text-emerald-300 text-xs font-bold transition border border-emerald-500/40">
                        📋 Buatkan Draf Penawaran
                    </button>
                </div>

                <!-- AI Response Card -->
                <div x-show="aiAnswer" x-transition class="p-5 rounded-2xl bg-slate-950/90 border border-emerald-500/40 space-y-3 mt-4">
                    <div class="flex items-start justify-between">
                        <div class="font-extrabold text-base text-emerald-400" x-text="aiAnswer.headline"></div>
                        <button @click="aiAnswer = null" class="text-slate-500 hover:text-slate-300"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed" x-text="aiAnswer.details"></p>

                    <!-- Mini Table if available -->
                    <template x-if="aiAnswer.data && aiAnswer.data.length > 0">
                        <div class="overflow-x-auto rounded-xl border border-slate-800/80">
                            <table class="w-full text-left text-xs text-slate-300">
                                <tbody class="divide-y divide-slate-800/60 font-mono">
                                    <template x-for="(row, rIdx) in aiAnswer.data" :key="rIdx">
                                        <tr class="hover:bg-slate-900/60">
                                            <template x-for="(col, cIdx) in row" :key="cIdx">
                                                <td class="py-2 px-3 text-[11px]" :class="cIdx === 0 ? 'font-sans font-bold text-white' : 'text-slate-400'" x-text="col"></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <!-- Action Suggestion -->
                    <div x-show="aiAnswer.action_suggestion" class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/20 text-xs text-emerald-300 flex items-center gap-2">
                        <i data-lucide="lightbulb" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span><strong class="text-white">Rekomendasi Tindakan:</strong> <span x-text="aiAnswer.action_suggestion"></span></span>
                    </div>

                    <!-- Human-in-the-Loop Confirmation Card -->
                    <div x-show="aiAnswer.requires_confirmation" class="p-4 rounded-xl bg-purple-950/30 border border-purple-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-3">
                        <div class="text-xs text-purple-200">
                            <strong class="text-white">Human-in-the-Loop Safety:</strong> Transaksi belum disimpan. Konfirmasi persetujuan Anda untuk menerbitkan draf ini.
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="aiAnswer = null" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                                Batalkan
                            </button>
                            <button type="button" @click="confirmAction()" :disabled="isExecutingAction" class="px-4 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow transition flex items-center gap-1">
                                <span x-show="!isExecutingAction">Setujui & Terbitkan</span>
                                <span x-show="isExecutingAction">Memproses...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Sales Forecasting & Predictive Trend Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-emerald-400"></i>
                    <span>Prediksi & Peramalan Penjualan 14 Hari</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Dekomposisi regresi tren linear + bobot musiman 7 hari (senin–minggu).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono 
                    {{ $forecasting['trend_direction'] === 'naik' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($forecasting['trend_direction'] === 'turun' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-300') }}">
                    Tren: {{ strtoupper($forecasting['trend_direction']) }} ({{ $forecasting['growth_percentage'] >= 0 ? '+' : '' }}{{ $forecasting['growth_percentage'] }}%)
                </span>
            </div>
        </div>

        <!-- Predictive KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card rounded-2xl p-4 border border-slate-800">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Proyeksi Penjualan 14 Hari</div>
                <div class="text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($forecasting['total_forecast_revenue'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Rata-rata: Rp {{ number_format(round($forecasting['total_forecast_revenue'] / 14), 0, ',', '.') }}/hari</div>
            </div>

            <div class="glass-card rounded-2xl p-4 border border-slate-800">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-Rata Historis 30 Hari</div>
                <div class="text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($forecasting['mean_revenue_past'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Baseline penjualan riil harian</div>
            </div>

            <div class="glass-card rounded-2xl p-4 border border-slate-800">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hari Puncak Diproyeksikan</div>
                <div class="text-lg font-black text-amber-400 mt-1.5">{{ $forecasting['peak_projected_day'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Potensi omzet tertinggi siklus mingguan</div>
            </div>

            <div class="glass-card rounded-2xl p-4 border border-slate-800">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Model Algoritma</div>
                <div class="text-sm font-bold text-cyan-400 mt-1">Seasonality + OLS Linear</div>
                <div class="text-[11px] text-slate-500 mt-1">Tingkat akurasi confidence 85-95%</div>
            </div>
        </div>

        <!-- Forecasting Chart -->
        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <div class="h-72 w-full">
                <canvas id="forecastingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 3. Anomaly & Fraud Detection Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-rose-400"></i>
                    <span>Deteksi Transaksi Abnormal & Potensi Fraud</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Analisis Z-score outlier nilai transaksi, rasio void kasir di atas normal, dan selisih kas fisik.</p>
            </div>
            <div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                    {{ $anomalies['total_alerts'] > 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                    {{ $anomalies['total_alerts'] }} Indikasi Ditemukan
                </span>
            </div>
        </div>

        @if($anomalies['total_alerts'] > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($anomalies['alerts'] as $alert)
            <div class="p-4 rounded-2xl border flex flex-col justify-between space-y-3
                {{ $alert['severity'] === 'danger' ? 'bg-rose-950/20 border-rose-500/40 text-rose-300' : 'bg-amber-950/20 border-amber-500/40 text-amber-300' }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="{{ $alert['severity'] === 'danger' ? 'alert-octagon' : 'alert-triangle' }}" class="w-5 h-5 shrink-0"></i>
                        <h4 class="font-extrabold text-sm text-white">{{ $alert['title'] }}</h4>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">{{ $alert['date'] }}</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">{{ $alert['description'] }}</p>
                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">Kasir: <strong class="text-white">{{ $alert['cashier'] }}</strong></span>
                    <span class="text-emerald-400 font-semibold">{{ $alert['recommendation'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-6 glass-card rounded-2xl border border-slate-800 text-center space-y-2">
            <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <div class="font-bold text-white text-sm">Tidak Ditemukan Anomali / Potensi Kecurangan</div>
            <div class="text-xs text-slate-400">Seluruh pola pesanan, nilai transaksi, rasio void, dan selisih laci kasir berada dalam batas wajar.</div>
        </div>
        @endif
    </div>

    <!-- 4. Stock Runout & Reorder Prediction Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="package-search" class="w-5 h-5 text-amber-400"></i>
                    <span>Prediksi Kebutuhan Stok & Rekomendasi Reorder</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Dihitung berdasarkan laju penjualan harian (*velocity*), *runout days*, dan *safety stock*.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                    {{ $stockPrediction['critical_count'] }} Kritis
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    {{ $stockPrediction['warning_count'] }} Menipis
                </span>
            </div>
        </div>

        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4 text-right">Sisa Stok</th>
                            <th class="py-3 px-4 text-right">Kecepatan Jual</th>
                            <th class="py-3 px-4 text-center">Estimasi Habis</th>
                            <th class="py-3 px-4 text-center">Status Urgensi</th>
                            <th class="py-3 px-4 text-right">Titik Reorder</th>
                            <th class="py-3 px-4 text-right">Saran Pesan (Reorder)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @forelse(array_slice($stockPrediction['products'], 0, 8) as $sp)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-sans font-bold text-white">
                                {{ $sp['product_name'] }}
                                <div class="text-[10px] text-slate-500 font-mono">{{ $sp['product_code'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-white text-sm">
                                {{ $sp['current_stock'] }} {{ $sp['unit'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-300">
                                {{ $sp['daily_velocity'] }} / hari
                            </td>
                            <td class="py-3 px-4 text-center font-sans">
                                @if($sp['runout_days'] !== '999+')
                                    <div class="font-bold {{ $sp['runout_days'] <= 2 ? 'text-rose-400' : 'text-amber-400' }}">
                                        {{ $sp['runout_days'] }} Hari Lagi
                                    </div>
                                    <div class="text-[10px] text-slate-500">{{ $sp['estimated_stockout_date'] }}</div>
                                @else
                                    <span class="text-slate-500">Stok Berlimpah</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-sans">
                                @if($sp['urgency'] === 'critical')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">Kritis</span>
                                @elseif($sp['urgency'] === 'warning')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">Menipis</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Aman</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-slate-300">
                                {{ $sp['reorder_point'] }} {{ $sp['unit'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-400">
                                +{{ $sp['suggested_reorder_qty'] }} {{ $sp['unit'] }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500 font-sans">Belum ada data inventori untuk dianalisis.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. Product Profitability Matrix (BCG / Menu Engineering Matrix) -->
    <div class="space-y-4" x-data="{ bcgTab: 'stars' }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i data-lucide="grid" class="w-5 h-5 text-cyan-400"></i>
                    <span>Matriks Profitabilitas Produk (Menu Engineering)</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Klasifikasi kuadran volume penjualan vs margin laba kotor di atas HPP riil.</p>
            </div>
            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-900 border border-slate-800">
                <button @click="bcgTab = 'stars'" :class="bcgTab === 'stars' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1">
                    <span>⭐ Stars</span>
                    <span class="text-[10px] px-1 rounded bg-slate-950/40 text-white font-mono">{{ count($bcgMatrix['stars']) }}</span>
                </button>
                <button @click="bcgTab = 'plowhorses'" :class="bcgTab === 'plowhorses' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1">
                    <span>🐎 Plowhorses</span>
                    <span class="text-[10px] px-1 rounded bg-slate-950/40 text-white font-mono">{{ count($bcgMatrix['plowhorses']) }}</span>
                </button>
                <button @click="bcgTab = 'puzzles'" :class="bcgTab === 'puzzles' ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1">
                    <span>🧩 Puzzles</span>
                    <span class="text-[10px] px-1 rounded bg-slate-950/40 text-white font-mono">{{ count($bcgMatrix['puzzles']) }}</span>
                </button>
                <button @click="bcgTab = 'dogs'" :class="bcgTab === 'dogs' ? 'bg-rose-500 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1">
                    <span>🐶 Dogs</span>
                    <span class="text-[10px] px-1 rounded bg-slate-950/40 text-white font-mono">{{ count($bcgMatrix['dogs']) }}</span>
                </button>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800">
            <!-- Stars Panel -->
            <div x-show="bcgTab === 'stars'" class="space-y-3">
                <div class="p-3 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-xs text-emerald-300">
                    <strong>Kuadran Bintang (High Volume, High Margin):</strong> Produk-produk ini adalah tulang punggung keuntungan bisnis Anda. Jaga konsistensi kualitas rasa resep dan pastikan stok selalu tersedia.
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['stars'] as $s)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-white">{{ $s['name'] }}</div>
                            <div class="text-[11px] text-emerald-400 font-mono mt-0.5">Margin: {{ $s['margin_percent'] }}% • Terjual: {{ $s['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-2 pt-2 border-t border-slate-800/80">{{ $s['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">Belum ada produk di kuadran Stars.</div>
                    @endforelse
                </div>
            </div>

            <!-- Plowhorses Panel -->
            <div x-show="bcgTab === 'plowhorses'" class="space-y-3" style="display: none;">
                <div class="p-3 rounded-xl bg-amber-950/20 border border-amber-500/30 text-xs text-amber-300">
                    <strong>Kuadran Kuda Pekerja (High Volume, Low Margin):</strong> Sangat laku tapi keuntungan per cangkir/item tipis. Rekomendasi: Naikkan harga sedikit (Rp500 - Rp2.000) atau negosiasikan harga beli bahan baku.
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['plowhorses'] as $ph)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-white">{{ $ph['name'] }}</div>
                            <div class="text-[11px] text-amber-400 font-mono mt-0.5">Margin: {{ $ph['margin_percent'] }}% • Terjual: {{ $ph['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-2 pt-2 border-t border-slate-800/80">{{ $ph['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">Belum ada produk di kuadran Plowhorses.</div>
                    @endforelse
                </div>
            </div>

            <!-- Puzzles Panel -->
            <div x-show="bcgTab === 'puzzles'" class="space-y-3" style="display: none;">
                <div class="p-3 rounded-xl bg-cyan-950/20 border border-cyan-500/30 text-xs text-cyan-300">
                    <strong>Kuadran Teka-Teki (Low Volume, High Margin):</strong> Keuntungan tinggi namun kurang populer. Rekomendasi: Pasang poster di kasir, beri diskon bundling, atau minta staf kasir merekomendasikan item ini (*upselling*).
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['puzzles'] as $pz)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-white">{{ $pz['name'] }}</div>
                            <div class="text-[11px] text-cyan-400 font-mono mt-0.5">Margin: {{ $pz['margin_percent'] }}% • Terjual: {{ $pz['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-2 pt-2 border-t border-slate-800/80">{{ $pz['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">Belum ada produk di kuadran Puzzles.</div>
                    @endforelse
                </div>
            </div>

            <!-- Dogs Panel -->
            <div x-show="bcgTab === 'dogs'" class="space-y-3" style="display: none;">
                <div class="p-3 rounded-xl bg-rose-950/20 border border-rose-500/30 text-xs text-rose-300">
                    <strong>Kuadran Dogs (Low Volume, Low Margin):</strong> Tidak laku dan tidak menguntungkan. Rekomendasi: Evaluasi resep atau hapus dari menu untuk menghemat biaya modal stok bahan baku.
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['dogs'] as $dg)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-white">{{ $dg['name'] }}</div>
                            <div class="text-[11px] text-rose-400 font-mono mt-0.5">Margin: {{ $dg['margin_percent'] }}% • Terjual: {{ $dg['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-2 pt-2 border-t border-slate-800/80">{{ $dg['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">Belum ada produk di kuadran Dogs.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Smart Dynamic Pricing & Promo Bundling -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Smart Pricing Recommendations -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4 text-emerald-400"></i>
                <span>Rekomendasi Penyesuaian Harga Pintar</span>
            </h3>
            <div class="space-y-3">
                @forelse(array_slice($pricingRecommendations, 0, 3) as $pr)
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">{{ $pr['name'] }}</div>
                            <div class="text-[11px] text-slate-400">HPP Modal: Rp {{ number_format($pr['base_cost'], 0, ',', '.') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400 line-through">Rp {{ number_format($pr['current_price'], 0, ',', '.') }}</div>
                            <div class="text-base font-black text-emerald-400 font-mono">Rp {{ number_format($pr['recommended_price'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $pr['reason'] }}</p>
                </div>
                @empty
                <div class="glass-card rounded-2xl p-6 border border-slate-800 text-center text-xs text-slate-500">
                    Seluruh produk saat ini memiliki margin keuntungan sehat di atas 45-50%.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Smart Promo & Bundles -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                <i data-lucide="gift" class="w-4 h-4 text-teal-400"></i>
                <span>Rekomendasi Promo & Paket Bundling</span>
            </h3>
            <div class="space-y-3">
                @forelse(array_slice($promoBundles, 0, 3) as $pb)
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="font-bold text-sm text-white">{{ $pb['title'] }}</div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                            Diskon {{ $pb['discount_percent'] }}%
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-mono">
                        <span class="text-slate-400 line-through">Rp {{ number_format($pb['normal_price'], 0, ',', '.') }}</span>
                        <span class="font-black text-emerald-400 text-sm">Rp {{ number_format($pb['recommended_bundle_price'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $pb['insight'] }}</p>
                </div>
                @empty
                <div class="glass-card rounded-2xl p-6 border border-slate-800 text-center text-xs text-slate-500">
                    Perlu lebih banyak transaksi gabungan untuk menganalisis pola asosiasi keranjang (*Market Basket*).
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 7. Cashier Performance Scorecard & Customer RFM -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cashier Scorecard -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-amber-400"></i>
                <span>Scorecard Efisiensi Kasir (AI Score)</span>
            </h3>
            <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase text-[9px] font-extrabold tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">Nama Kasir</th>
                            <th class="py-2.5 px-3 text-right">Omset</th>
                            <th class="py-2.5 px-3 text-center">Akurasi Laci</th>
                            <th class="py-2.5 px-3 text-center">Void Rate</th>
                            <th class="py-2.5 px-3 text-right">Skor AI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @forelse($cashierPerformance as $cp)
                        <tr>
                            <td class="py-2.5 px-3 font-sans font-bold text-white">{{ $cp['name'] }}</td>
                            <td class="py-2.5 px-3 text-right text-emerald-400 font-bold">Rp {{ number_format($cp['total_revenue'], 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-center">{{ $cp['drawer_accuracy_percent'] }}%</td>
                            <td class="py-2.5 px-3 text-center {{ $cp['void_rate'] > 10 ? 'text-rose-400 font-bold' : 'text-slate-400' }}">{{ $cp['void_rate'] }}%</td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] 
                                    {{ $cp['score'] >= 85 ? 'bg-emerald-500/20 text-emerald-400' : ($cp['score'] >= 70 ? 'bg-amber-500/20 text-amber-400' : 'bg-rose-500/20 text-rose-400') }}">
                                    {{ $cp['score'] }}/100
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 font-sans">Belum ada data shift kasir.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RFM Customer Segmentation -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-cyan-400"></i>
                <span>Segmentasi Perilaku Pelanggan (RFM Matrix)</span>
            </h3>
            <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-3">
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/30">
                        <div class="font-black text-base text-emerald-400 font-mono">{{ $rfmSegments['summary']['champions_count'] }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Champions (VIP)</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-teal-950/20 border border-teal-500/30">
                        <div class="font-black text-base text-teal-400 font-mono">{{ $rfmSegments['summary']['loyal_count'] }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">Pelanggan Loyal</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-rose-950/20 border border-rose-500/30">
                        <div class="font-black text-base text-rose-400 font-mono">{{ $rfmSegments['summary']['at_risk_count'] }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">At-Risk (Churn)</div>
                    </div>
                </div>

                <div class="text-xs text-slate-300 leading-relaxed border-t border-slate-800/80 pt-3">
                    <strong class="text-white">Rekomendasi Retensi:</strong>
                    @if($rfmSegments['summary']['at_risk_count'] > 0)
                        Terdapat {{ $rfmSegments['summary']['at_risk_count'] }} pelanggan bernilai tinggi yang belum berbelanja dalam 30-75 hari terakhir. Kirimkan voucher promo diskon kangen via WhatsApp CRM untuk menarik mereka kembali.
                    @else
                        Retensi pelanggan berjalan baik. Terus tingkatkan program poin loyalitas bagi anggota Champions Anda.
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js & Chart.js Application Engine -->
<script>
    function aiPosApp() {
        return {
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            nlQuery: '',
            isAsking: false,
            isExecutingAction: false,
            aiAnswer: null,

            quickAsk(q) {
                this.nlQuery = q;
                this.askAi();
            },

            async askAi() {
                if (!this.nlQuery.trim()) return;
                this.isAsking = true;

                try {
                    const res = await fetch("{{ route('pos.ai.ask') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ query: this.nlQuery })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.aiAnswer = data.response;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    } else {
                        AppAlert.error(data.message || 'Gagal menganalisis pertanyaan.');
                    }
                } catch (e) {
                    AppAlert.error('Gagal menghubungi AI Engine.');
                } finally {
                    this.isAsking = false;
                }
            },

            async confirmAction() {
                if (!this.aiAnswer || !this.aiAnswer.payload) return;
                this.isExecutingAction = true;

                try {
                    const res = await fetch("{{ route('pos.ai.execute-action') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action_type: this.aiAnswer.action_type,
                            payload: this.aiAnswer.payload
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        AppAlert.success(data.message);
                        if (data.data && data.data.redirect_url) {
                            setTimeout(() => {
                                window.location.href = data.data.redirect_url;
                            }, 1000);
                        }
                    } else {
                        AppAlert.error(data.message || 'Gagal mengeksekusi aksi.');
                    }
                } catch (e) {
                    AppAlert.error('Gagal mengeksekusi aksi AI.');
                } finally {
                    this.isExecutingAction = false;
                }
            }
        };
    }

    // Chart.js Dual Forecast Chart
    document.addEventListener('DOMContentLoaded', () => {
        const forecastingData = @json($forecasting);
        const ctx = document.getElementById('forecastingChart');

        if (ctx && forecastingData) {
            const historyLabels = forecastingData.history.map(h => h.day_name + ' ' + h.date.slice(8));
            const historyRevenues = forecastingData.history.map(h => h.revenue);

            const forecastLabels = forecastingData.forecast.map(f => f.day_name.slice(0, 3) + ' ' + f.formatted_date);
            const forecastRevenues = forecastingData.forecast.map(f => f.predicted_revenue);
            const forecastUpper = forecastingData.forecast.map(f => f.upper_bound);
            const forecastLower = forecastingData.forecast.map(f => f.lower_bound);

            // Combine labels
            const allLabels = [...historyLabels, ...forecastLabels];
            const historicalSeries = [...historyRevenues, ...new Array(forecastLabels.length).fill(null)];
            const forecastSeries = [...new Array(historyLabels.length).fill(null), ...forecastRevenues];
            const upperSeries = [...new Array(historyLabels.length).fill(null), ...forecastUpper];
            const lowerSeries = [...new Array(historyLabels.length).fill(null), ...forecastLower];

            // Connect bridge point
            if (historyRevenues.length > 0 && forecastRevenues.length > 0) {
                const lastHistory = historyRevenues[historyRevenues.length - 1];
                forecastSeries[historyLabels.length - 1] = lastHistory;
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allLabels,
                    datasets: [
                        {
                            label: 'Historis Riil (30 Hari)',
                            data: historicalSeries,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 2,
                        },
                        {
                            label: 'Prediksi AI (14 Hari ke Depan)',
                            data: forecastSeries,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            borderDash: [5, 4],
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                        },
                        {
                            label: 'Batas Atas (Confidence)',
                            data: upperSeries,
                            borderColor: 'rgba(16, 185, 129, 0.3)',
                            borderDash: [2, 2],
                            fill: false,
                            pointRadius: 0,
                        },
                        {
                            label: 'Batas Bawah (Confidence)',
                            data: lowerSeries,
                            borderColor: 'rgba(16, 185, 129, 0.3)',
                            borderDash: [2, 2],
                            fill: false,
                            pointRadius: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + Number(context.parsed.y || 0).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#94a3b8', maxRotation: 45, font: { size: 10 } } },
                        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#94a3b8', callback: (v) => 'Rp ' + (v/1000) + 'k' } }
                    }
                }
            });
        }
    });
</script>
@endsection
