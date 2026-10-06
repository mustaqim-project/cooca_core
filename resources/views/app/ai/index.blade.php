@extends('layouts.app', ['title' => __('ai.pos.title')])

@section('content')
<div class="space-y-8" x-data="aiPosApp()">
    
    <!-- Unified Apple HIG Navigation Hub -->
    @include('app.ai.partials.office_navigation', ['activeOffice' => 'pos'])

    <!-- AI POS Header & Status Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('ai.navigation.ai_pos') }}</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">{{ __('ai.pos.title') }}</h1>
            <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1">{{ __('ai.pos.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(\App\Support\Context::hasPermission('pos.terminal'))
            <a href="{{ route('pos.terminal') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-2">
                <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-400"></i>
                <span>{{ __('ai.pos.terminal_cashier') }}</span>
            </a>
            @endif
            @if(\App\Support\Context::hasPermission('pos.reports'))
            <a href="{{ route('pos.reports.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                <span>{{ __('ai.pos.cashier_reports') }}</span>
            </a>
            @endif
        </div>
    </div>

    @if(\App\Support\Context::hasPermission('ai.access'))
    <!-- 1. Interactive Natural Language Assistant: "Tanya AI POS" -->
    <div class="rounded-3xl p-6 border border-emerald-500/30 bg-white dark:bg-zinc-900 bg-gradient-to-br from-emerald-500/[0.03] via-transparent to-teal-500/[0.03] dark:from-slate-900 dark:via-slate-900 dark:to-emerald-950/20 shadow-sm border-slate-200 dark:border-white/10 relative overflow-hidden">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center font-black shadow-lg shadow-emerald-500/25 shrink-0">
                <i data-lucide="bot" class="w-7 h-7"></i>
            </div>
            <div class="flex-1 space-y-3">
                <div>
                    <h3 class="font-black text-lg text-slate-900 dark:text-white">
                        {{ __('ai.pos.natural_assistant') }}
                    </h3>
                    <p class="text-xs text-slate-400">{{ __('ai.pos.natural_assistant_desc') }}</p>
                </div>

                <!-- Input Box -->
                <form @submit.prevent="askAi()" class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="text" 
                               x-model="nlQuery" 
                               placeholder="{{ __('ai.pos.ask_placeholder') }}" 
                               class="w-full bg-slate-50 dark:bg-zinc-950/80 border border-slate-200 dark:border-slate-700/80 rounded-2xl px-4 py-3.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 shadow-inner">
                    </div>
                    <button type="submit" 
                            :disabled="isAsking || !nlQuery.trim()"
                            class="w-full sm:w-auto min-h-[44px] px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-md transition disabled:opacity-40 flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                        <span x-show="!isAsking">{{ __('ai.pos.ask_button') }}</span>
                        <span x-show="isAsking">{{ __('ai.pos.ask_loading') }}</span>
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                <!-- Quick Query Suggestions Chips (min 44px tap target) -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 pt-1">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold w-full sm:w-auto">{{ __('ai.pos.quick_questions') }}</span>
                    <button @click="quickAsk('Berapa penjualan hari ini?')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs transition border border-slate-200 dark:border-slate-700/60 inline-flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-4 h-4 text-emerald-500"></i>
                        <span>{{ __('ai.pos.quick_queries.sales_today') }}</span>
                    </button>
                    <button @click="quickAsk('Produk apa yang stoknya mau habis?')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs transition border border-slate-200 dark:border-slate-700/60 inline-flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                        <span>{{ __('ai.pos.quick_queries.low_stock') }}</span>
                    </button>
                    <button @click="quickAsk('Apakah ada transaksi mencurigakan atau fraud?')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs transition border border-slate-200 dark:border-slate-700/60 inline-flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-rose-500"></i>
                        <span>{{ __('ai.pos.quick_queries.cashier_anomaly') }}</span>
                    </button>
                    <button @click="quickAsk('Prediksi penjualan minggu depan')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs transition border border-slate-200 dark:border-slate-700/60 inline-flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-cyan-500"></i>
                        <span>{{ __('ai.pos.quick_queries.sales_prediction') }}</span>
                    </button>
                    <button @click="quickAsk('Siapa kasir dengan performa terbaik?')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs transition border border-slate-200 dark:border-slate-700/60 inline-flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-sky-500"></i>
                        <span>{{ __('ai.pos.quick_queries.cashier_performance') }}</span>
                    </button>
                    <button @click="quickAsk('Buatkan draf invoice')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/40 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-xs font-bold transition border border-purple-200 dark:border-purple-500/40 inline-flex items-center gap-2">
                        <i data-lucide="zap" class="w-4 h-4 text-purple-500"></i>
                        <span>{{ __('ai.pos.quick_queries.draft_invoice') }}</span>
                    </button>
                    <button @click="quickAsk('Buatkan draf penawaran')" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold transition border border-emerald-200 dark:border-emerald-500/40 inline-flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-emerald-500"></i>
                        <span>{{ __('ai.pos.quick_queries.draft_quotation') }}</span>
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
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5 font-mono">
                                    <template x-for="(row, rIdx) in aiAnswer.data" :key="rIdx">
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50">
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
                        <span><strong class="text-white">{{ __('ai.pos.action_recommendation') }}</strong> <span x-text="aiAnswer.action_suggestion"></span></span>
                    </div>

                    <!-- Human-in-the-Loop Confirmation Card -->
                    <div x-show="aiAnswer.requires_confirmation" class="p-4 rounded-xl bg-purple-950/30 border border-purple-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-3">
                        <div class="text-xs text-purple-200">
                            <strong class="text-white">Human-in-the-Loop Safety:</strong> {{ __('ai.pos.human_safety') }}
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="aiAnswer = null" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                                {{ __('ai.pos.cancel') }}
                            </button>
                            <button type="button" @click="confirmAction()" :disabled="isExecutingAction" class="px-4 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow transition flex items-center gap-1">
                                <span x-show="!isExecutingAction">{{ __('ai.pos.approve_publish') }}</span>
                                <span x-show="isExecutingAction">{{ __('ai.pos.executing') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. Sales Forecasting & Predictive Trend Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-emerald-400"></i>
                    <span>{{ __('ai.forecasting.title_14d') }}</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">{{ __('ai.forecasting.subtitle_decomp') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="exportForecastingCsv()" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/10 transition flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.forecasting.export_csv') }}</span>
                </button>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono 
                    {{ $forecasting['trend_direction'] === 'naik' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($forecasting['trend_direction'] === 'turun' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-300') }}">
                    {{ __('ai.forecasting.trend') }}: {{ strtoupper($forecasting['trend_direction']) }} ({{ $forecasting['growth_percentage'] >= 0 ? '+' : '' }}{{ $forecasting['growth_percentage'] }}%)
                </span>
            </div>
        </div>

        <!-- Predictive KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
                <div class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ai.forecasting.projected_total_14d') }}</div>
                <div class="text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($forecasting['total_forecast_revenue'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ __('ai.forecasting.avg_daily') }}: Rp {{ number_format(round($forecasting['total_forecast_revenue'] / 14), 0, ',', '.') }}/hari</div>
            </div>

            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
                <div class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ai.forecasting.historical_avg_30d') }}</div>
                <div class="text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($forecasting['mean_revenue_past'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ __('ai.forecasting.baseline_real_sales') }}</div>
            </div>

            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
                <div class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ai.forecasting.peak_day_projected') }}</div>
                <div class="text-lg font-black text-amber-400 mt-1.5">{{ $forecasting['peak_projected_day'] }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ __('ai.forecasting.peak_day_potential') }}</div>
            </div>

            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
                <div class="text-xs font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">{{ __('ai.forecasting.algorithm_model') }}</div>
                <div class="text-sm font-bold text-cyan-400 mt-1">Seasonality + OLS Linear</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ __('ai.forecasting.algorithm_desc') }}</div>
            </div>
        </div>

        <!-- Forecasting Chart -->
        <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
            <div class="h-72 w-full">
                <canvas id="forecastingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 3. Anomaly & Fraud Detection Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-rose-400"></i>
                    <span>{{ __('ai.anomalies.title') }}</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">{{ __('ai.anomalies.subtitle') }}</p>
            </div>
            <div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                    {{ $anomalies['total_alerts'] > 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                    {{ __('ai.anomalies.indications_found', ['count' => $anomalies['total_alerts']]) }}
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
                        <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $alert['title'] }}</h4>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">{{ $alert['date'] }}</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">{{ $alert['description'] }}</p>
                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">{{ __('ai.anomalies.cashier') }}: <strong class="text-white">{{ $alert['cashier'] }}</strong></span>
                    <span class="text-emerald-400 font-semibold">{{ $alert['recommendation'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm text-center space-y-2">
            <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ __('ai.anomalies.safe_title') }}</div>
            <div class="text-xs text-slate-400">{{ __('ai.anomalies.safe_desc') }}</div>
        </div>
        @endif
    </div>

    <!-- 4. Stock Runout & Reorder Prediction Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="package-search" class="w-5 h-5 text-amber-400"></i>
                    <span>{{ __('ai.inventory.title') }}</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">{{ __('ai.inventory.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(count($stockPrediction['products']) > 0)
                <button type="button" @click="showAllStockModal = true" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/10 transition flex items-center gap-1.5">
                    <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.inventory.view_all', ['count' => count($stockPrediction['products'])]) }}</span>
                </button>
                @endif
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                    {{ $stockPrediction['critical_count'] }} {{ __('ai.inventory.status_critical') }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    {{ $stockPrediction['warning_count'] }} {{ __('ai.inventory.status_warning') }}
                </span>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <!-- Mobile Card Stack (< md) -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-white/5">
                @forelse(array_slice($stockPrediction['products'], 0, 8) as $sp)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $sp['product_name'] }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $sp['product_code'] }}</div>
                        </div>
                        <div>
                            @if($sp['urgency'] === 'critical')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30">{{ __('ai.inventory.status_critical') }}</span>
                            @elseif($sp['urgency'] === 'warning')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30">{{ __('ai.inventory.status_warning') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">{{ __('ai.inventory.status_safe') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 dark:bg-zinc-800/40 p-2.5 rounded-xl border border-slate-100 dark:border-white/5">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">{{ __('ai.inventory.current_stock') }}:</span>
                            <div class="font-bold text-slate-900 dark:text-white font-mono">{{ $sp['current_stock'] }} {{ $sp['unit'] }}</div>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">{{ __('ai.inventory.sales_velocity') }}:</span>
                            <div class="font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $sp['daily_velocity'] }} / hari</div>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">{{ __('ai.inventory.days_remaining') }}:</span>
                            <div class="font-bold {{ $sp['runout_days'] !== '999+' && $sp['runout_days'] <= 2 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $sp['runout_days'] !== '999+' ? __('ai.inventory.days_left', ['days' => $sp['runout_days']]) : __('ai.inventory.status_safe') }}
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">{{ __('ai.inventory.suggested_reorder') }}:</span>
                            <div class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">+{{ $sp['suggested_reorder_qty'] }} {{ $sp['unit'] }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-500 text-xs">{{ __('ai.inventory.no_data') }}</div>
                @endforelse
            </div>

            <!-- Desktop Table (>= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-zinc-800/50 text-slate-600 dark:text-zinc-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200 dark:border-white/10">
                        <tr>
                            <th class="py-3 px-4">{{ __('ai.inventory.product') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('ai.inventory.current_stock') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('ai.inventory.sales_velocity') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('ai.inventory.days_remaining') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('ai.inventory.status') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('ai.inventory.reorder_point') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('ai.inventory.suggested_reorder') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 font-mono">
                        @forelse(array_slice($stockPrediction['products'], 0, 8) as $sp)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition">
                            <td class="py-3 px-4 font-sans font-bold text-slate-900 dark:text-white">
                                {{ $sp['product_name'] }}
                                <div class="text-[10px] text-slate-500 font-mono">{{ $sp['product_code'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white text-sm">
                                {{ $sp['current_stock'] }} {{ $sp['unit'] }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-300">
                                {{ $sp['daily_velocity'] }} / hari
                            </td>
                            <td class="py-3 px-4 text-center font-sans">
                                @if($sp['runout_days'] !== '999+')
                                    <div class="font-bold {{ $sp['runout_days'] <= 2 ? 'text-rose-500 dark:text-rose-400' : 'text-amber-500 dark:text-amber-400' }}">
                                        {{ __('ai.inventory.days_left', ['days' => $sp['runout_days']]) }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">{{ $sp['estimated_stockout_date'] }}</div>
                                @else
                                    <span class="text-slate-500">{{ __('ai.inventory.plenty') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-sans">
                                @if($sp['urgency'] === 'critical')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-500 dark:text-rose-400 border border-rose-500/30">{{ __('ai.inventory.status_critical') }}</span>
                                @elseif($sp['urgency'] === 'warning')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-500 dark:text-amber-400 border border-amber-500/30">{{ __('ai.inventory.status_warning') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-500 dark:text-emerald-400 border border-emerald-500/30">{{ __('ai.inventory.status_safe') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-300">
                                {{ $sp['reorder_point'] }} {{ $sp['unit'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                +{{ $sp['suggested_reorder_qty'] }} {{ $sp['unit'] }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500 font-sans">{{ __('ai.inventory.no_data') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @if(count($stockPrediction['products']) > 8)
            <div class="p-3 bg-slate-50 dark:bg-zinc-800/40 border-t border-slate-200 dark:border-white/10 text-center">
                <button type="button" @click="showAllStockModal = true" class="text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline inline-flex items-center gap-1">
                    <span>{{ __('ai.inventory.view_all_action', ['count' => count($stockPrediction['products'])]) }}</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- 5. Product Profitability Matrix (BCG Matrix) -->
    <div class="space-y-4" x-data="{ bcgTab: (new URLSearchParams(window.location.search).get('tab') && ['stars','plowhorses','puzzles','dogs'].includes(new URLSearchParams(window.location.search).get('tab'))) ? new URLSearchParams(window.location.search).get('tab') : 'stars' }" x-init="$watch('bcgTab', tab => { const url = new URL(window.location); url.searchParams.set('tab', tab); window.history.replaceState({}, '', url); })">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="grid" class="w-5 h-5 text-cyan-400"></i>
                    <span>{{ __('ai.bcg.title') }}</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">{{ __('ai.bcg.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-white/10">
                <button @click="bcgTab = 'stars'" :class="bcgTab === 'stars' ? 'bg-emerald-500 text-slate-950 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                    <i data-lucide="star" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.bcg.stars') }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-950/20 dark:bg-slate-950/40 text-slate-900 dark:text-white font-mono">{{ count($bcgMatrix['stars']) }}</span>
                </button>
                <button @click="bcgTab = 'plowhorses'" :class="bcgTab === 'plowhorses' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.bcg.plowhorses') }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-950/20 dark:bg-slate-950/40 text-slate-900 dark:text-white font-mono">{{ count($bcgMatrix['plowhorses']) }}</span>
                </button>
                <button @click="bcgTab = 'puzzles'" :class="bcgTab === 'puzzles' ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                    <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.bcg.puzzles') }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-950/20 dark:bg-slate-950/40 text-slate-900 dark:text-white font-mono">{{ count($bcgMatrix['puzzles']) }}</span>
                </button>
                <button @click="bcgTab = 'dogs'" :class="bcgTab === 'dogs' ? 'bg-rose-500 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                    <i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i>
                    <span>{{ __('ai.bcg.dogs') }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-950/20 dark:bg-slate-950/40 text-slate-900 dark:text-white font-mono">{{ count($bcgMatrix['dogs']) }}</span>
                </button>
            </div>
        </div>

        <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
            <!-- Stars Panel -->
            <div x-show="bcgTab === 'stars'" class="space-y-3">
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/30 text-xs text-emerald-800 dark:text-emerald-300">
                    {{ __('ai.bcg.stars_desc') }}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['stars'] as $s)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/70 border border-slate-200 dark:border-white/10 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $s['name'] }}</div>
                            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">{{ __('ai.bcg.margin') }}: {{ $s['margin_percent'] }}% • {{ __('ai.bcg.sold') }}: {{ $s['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-zinc-400 mt-2 pt-2 border-t border-slate-200 dark:border-white/10">{{ $s['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">{{ __('ai.bcg.empty_stars') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Plowhorses Panel -->
            <div x-show="bcgTab === 'plowhorses'" class="space-y-3" style="display: none;">
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-500/30 text-xs text-amber-800 dark:text-amber-300">
                    {{ __('ai.bcg.plowhorses_desc') }}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['plowhorses'] as $ph)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/70 border border-slate-200 dark:border-white/10 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $ph['name'] }}</div>
                            <div class="text-[11px] text-amber-600 dark:text-amber-400 font-mono mt-0.5">{{ __('ai.bcg.margin') }}: {{ $ph['margin_percent'] }}% • {{ __('ai.bcg.sold') }}: {{ $ph['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-zinc-400 mt-2 pt-2 border-t border-slate-200 dark:border-white/10">{{ $ph['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">{{ __('ai.bcg.empty_plowhorses') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Puzzles Panel -->
            <div x-show="bcgTab === 'puzzles'" class="space-y-3" style="display: none;">
                <div class="p-3 rounded-xl bg-cyan-50 dark:bg-cyan-950/30 border border-cyan-200 dark:border-cyan-500/30 text-xs text-cyan-800 dark:text-cyan-300">
                    {{ __('ai.bcg.puzzles_desc') }}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['puzzles'] as $pz)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/70 border border-slate-200 dark:border-white/10 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $pz['name'] }}</div>
                            <div class="text-[11px] text-cyan-600 dark:text-cyan-400 font-mono mt-0.5">{{ __('ai.bcg.margin') }}: {{ $pz['margin_percent'] }}% • {{ __('ai.bcg.sold') }}: {{ $pz['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-zinc-400 mt-2 pt-2 border-t border-slate-200 dark:border-white/10">{{ $pz['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">{{ __('ai.bcg.empty_puzzles') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Dogs Panel -->
            <div x-show="bcgTab === 'dogs'" class="space-y-3" style="display: none;">
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-500/30 text-xs text-rose-800 dark:text-rose-300">
                    {{ __('ai.bcg.dogs_desc') }}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($bcgMatrix['dogs'] as $dg)
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-zinc-800/70 border border-slate-200 dark:border-white/10 flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $dg['name'] }}</div>
                            <div class="text-[11px] text-rose-600 dark:text-rose-400 font-mono mt-0.5">{{ __('ai.bcg.margin') }}: {{ $dg['margin_percent'] }}% • {{ __('ai.bcg.sold') }}: {{ $dg['total_qty'] }}</div>
                        </div>
                        <div class="text-[11px] text-slate-600 dark:text-zinc-400 mt-2 pt-2 border-t border-slate-200 dark:border-white/10">{{ $dg['action'] }}</div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-500">{{ __('ai.bcg.empty_dogs') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Smart Dynamic Pricing & Promo Bundling -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Smart Pricing Recommendations -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                <span>{{ __('ai.pricing.title') }}</span>
            </h3>
            <div class="space-y-3">
                @forelse(array_slice($pricingRecommendations, 0, 3) as $pr)
                <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $pr['name'] }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-zinc-400">{{ __('ai.pricing.base_cost') }}: Rp {{ number_format($pr['base_cost'], 0, ',', '.') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400 line-through">Rp {{ number_format($pr['current_price'], 0, ',', '.') }}</div>
                            <div class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($pr['recommended_price'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">{{ $pr['reason'] }}</p>
                </div>
                @empty
                <div class="rounded-2xl p-6 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm text-center text-xs text-slate-500">
                    {{ __('ai.pricing.healthy_margin') }}
                </div>
                @endforelse
            </div>
        </div>

        <!-- Smart Promo & Bundles -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="gift" class="w-4 h-4 text-teal-500 dark:text-teal-400"></i>
                <span>{{ __('ai.bundles.title') }}</span>
            </h3>
            <div class="space-y-3">
                @forelse(array_slice($promoBundles, 0, 3) as $pb)
                <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $pb['title'] }}</div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-700 dark:text-teal-300 border border-teal-500/30">
                            {{ __('ai.bundles.discount', ['percent' => $pb['discount_percent']]) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-mono">
                        <span class="text-slate-400 line-through">Rp {{ number_format($pb['normal_price'], 0, ',', '.') }}</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">Rp {{ number_format($pb['recommended_bundle_price'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">{{ $pb['insight'] }}</p>
                </div>
                @empty
                <div class="rounded-2xl p-6 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm text-center text-xs text-slate-500">
                    {{ __('ai.bundles.need_more_data') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 7. Cashier Performance Scorecard & Customer RFM -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cashier Scorecard -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-amber-500 dark:text-amber-400"></i>
                <span>{{ __('ai.cashier.title') }}</span>
            </h3>
            @if(isset($business) && !$business->isModuleEnabled('pos'))
            <div class="rounded-2xl p-6 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm text-center text-xs text-slate-500">
                {{ __('ai.cashier.module_disabled') }}
            </div>
            @else
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
                <!-- Mobile Card Stack (< sm) -->
                <div class="block sm:hidden divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($cashierPerformance as $cp)
                    <div class="p-3.5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $cp['name'] }}</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] 
                                {{ $cp['score'] >= 85 ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : ($cp['score'] >= 70 ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-rose-500/20 text-rose-600 dark:text-rose-400') }}">
                                {{ __('ai.cashier.score') }}: {{ $cp['score'] }}/100
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-[11px] bg-slate-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-slate-100 dark:border-white/5">
                            <div>
                                <span class="text-[9px] text-slate-500 uppercase font-semibold block">{{ __('ai.cashier.revenue') }}:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($cp['total_revenue'], 0, ',', '.') }}</span>
                            </div>
                            <div class="text-center">
                                <span class="text-[9px] text-slate-500 uppercase font-semibold block">{{ __('ai.cashier.drawer_accuracy') }}:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-300">{{ $cp['drawer_accuracy_percent'] }}%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] text-slate-500 uppercase font-semibold block">{{ __('ai.cashier.void_rate') }}:</span>
                                <span class="font-mono {{ $cp['void_rate'] > 10 ? 'text-rose-500 font-bold' : 'text-slate-700 dark:text-slate-300' }}">{{ $cp['void_rate'] }}%</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-slate-500 text-xs">{{ __('ai.cashier.no_data') }}</div>
                    @endforelse
                </div>

                <!-- Desktop Table (>= sm) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-zinc-800/50 text-slate-600 dark:text-zinc-400 uppercase text-[9px] font-extrabold tracking-wider border-b border-slate-200 dark:border-white/10">
                            <tr>
                                <th class="py-2.5 px-3">{{ __('ai.cashier.cashier_name') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('ai.cashier.revenue') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('ai.cashier.drawer_accuracy') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('ai.cashier.void_rate') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('ai.cashier.ai_score') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5 font-mono">
                            @forelse($cashierPerformance as $cp)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition">
                                <td class="py-2.5 px-3 font-sans font-bold text-slate-900 dark:text-white">{{ $cp['name'] }}</td>
                                <td class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400 font-bold">Rp {{ number_format($cp['total_revenue'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-center">{{ $cp['drawer_accuracy_percent'] }}%</td>
                                <td class="py-2.5 px-3 text-center {{ $cp['void_rate'] > 10 ? 'text-rose-500 font-bold' : 'text-slate-500 dark:text-slate-400' }}">{{ $cp['void_rate'] }}%</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] 
                                        {{ $cp['score'] >= 85 ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : ($cp['score'] >= 70 ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-rose-500/20 text-rose-600 dark:text-rose-400') }}">
                                        {{ $cp['score'] }}/100
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500 font-sans">{{ __('ai.cashier.no_data') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- RFM Customer Segmentation -->
        <div class="space-y-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-cyan-500 dark:text-cyan-400"></i>
                <span>{{ __('ai.rfm.title') }}</span>
            </h3>
            <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm space-y-3">
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-500/30">
                        <div class="font-black text-base text-emerald-600 dark:text-emerald-400 font-mono">{{ $rfmSegments['summary']['champions_count'] }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">{{ __('ai.rfm.champions') }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-teal-50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-500/30">
                        <div class="font-black text-base text-teal-600 dark:text-teal-400 font-mono">{{ $rfmSegments['summary']['loyal_count'] }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">{{ __('ai.rfm.loyal') }}</div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-500/30">
                        <div class="font-black text-base text-rose-600 dark:text-rose-400 font-mono">{{ $rfmSegments['summary']['at_risk_count'] }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">{{ __('ai.rfm.at_risk') }}</div>
                    </div>
                </div>

                <div class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed border-t border-slate-200 dark:border-white/10 pt-3">
                    <strong class="text-slate-900 dark:text-white">{{ __('ai.rfm.recommendation') }}:</strong>
                    @if($rfmSegments['summary']['at_risk_count'] > 0)
                        {{ __('ai.rfm.at_risk_note', ['count' => $rfmSegments['summary']['at_risk_count']]) }}
                    @else
                        {{ __('ai.rfm.healthy_note') }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Drill-Down Modal Sheet -->
    <div x-show="showAllStockModal" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         style="display: none;" 
         @keydown.escape.window="showAllStockModal = false">
        
        <div @click.away="showAllStockModal = false" class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 rounded-3xl w-full max-w-5xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-white/10 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-base">{{ __('ai.inventory.modal_title') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">{{ __('ai.inventory.modal_subtitle', ['count' => count($stockPrediction['products'])]) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="exportStockCsv()" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/10 transition flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>{{ __('ai.inventory.export_csv') }}</span>
                    </button>
                    <button type="button" @click="showAllStockModal = false" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-zinc-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Search & Filters -->
            <div class="px-6 py-3 bg-slate-50 dark:bg-zinc-800/40 border-b border-slate-200 dark:border-white/10 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="relative w-full sm:w-72">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" x-model="stockSearch" placeholder="{{ __('ai.inventory.search_placeholder') }}" class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                    <button type="button" @click="stockFilter = 'all'" :class="stockFilter === 'all' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10'" class="px-3 py-1 rounded-lg text-xs transition">
                        {{ __('ai.inventory.filter_all', ['count' => count($stockPrediction['products'])]) }}
                    </button>
                    <button type="button" @click="stockFilter = 'critical'" :class="stockFilter === 'critical' ? 'bg-rose-500 text-white font-bold' : 'bg-white dark:bg-zinc-900 text-rose-500 border border-rose-500/20'" class="px-3 py-1 rounded-lg text-xs transition">
                        {{ __('ai.inventory.filter_critical', ['count' => $stockPrediction['critical_count']]) }}
                    </button>
                    <button type="button" @click="stockFilter = 'warning'" :class="stockFilter === 'warning' ? 'bg-amber-500 text-white font-bold' : 'bg-white dark:bg-zinc-900 text-amber-500 border border-amber-500/20'" class="px-3 py-1 rounded-lg text-xs transition">
                        {{ __('ai.inventory.filter_warning', ['count' => $stockPrediction['warning_count']]) }}
                    </button>
                    <button type="button" @click="stockFilter = 'safe'" :class="stockFilter === 'safe' ? 'bg-emerald-500 text-white font-bold' : 'bg-white dark:bg-zinc-900 text-emerald-500 border border-emerald-500/20'" class="px-3 py-1 rounded-lg text-xs transition">
                        {{ __('ai.inventory.filter_safe') }}
                    </button>
                </div>
            </div>

            <!-- Modal Content (Scrollable List/Table) -->
            <div class="overflow-y-auto flex-1 p-6">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-zinc-800/50 text-slate-600 dark:text-zinc-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-200 dark:border-white/10 sticky top-0">
                        <tr>
                            <th class="py-2.5 px-3">{{ __('ai.inventory.product') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('ai.inventory.current_stock') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('ai.inventory.sales_velocity') }}</th>
                            <th class="py-2.5 px-3 text-center">{{ __('ai.inventory.days_remaining') }}</th>
                            <th class="py-2.5 px-3 text-center">{{ __('ai.inventory.status') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('ai.inventory.reorder_point') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('ai.inventory.suggested_reorder') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 font-mono tabular-nums">
                        <template x-for="p in getFilteredStock()" :key="p.product_code + p.product_name">
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition">
                                <td class="py-2.5 px-3 font-sans">
                                    <div class="font-bold text-slate-900 dark:text-white" x-text="p.product_name"></div>
                                    <div class="text-[10px] text-slate-500 font-mono" x-text="p.product_code"></div>
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white" x-text="p.current_stock + ' ' + p.unit"></td>
                                <td class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-300" x-text="p.daily_velocity + ' / hari'"></td>
                                <td class="py-2.5 px-3 text-center font-sans">
                                    <span :class="p.runout_days !== '999+' && p.runout_days <= 2 ? 'text-rose-500 font-bold' : (p.runout_days !== '999+' ? 'text-amber-500 font-bold' : 'text-slate-500')" x-text="p.runout_days !== '999+' ? p.runout_days + ' Hari' : 'Aman'"></span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-sans">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                                          :class="p.urgency === 'critical' ? 'bg-rose-500/20 text-rose-500 border border-rose-500/30' : (p.urgency === 'warning' ? 'bg-amber-500/20 text-amber-500 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-500 border border-emerald-500/30')"
                                          x-text="p.urgency === 'critical' ? 'Kritis' : (p.urgency === 'warning' ? 'Menipis' : 'Aman')"></span>
                                </td>
                                <td class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-300" x-text="p.reorder_point + ' ' + p.unit"></td>
                                <td class="py-2.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400" x-text="'+' + p.suggested_reorder_qty + ' ' + p.unit"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
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

            showAllStockModal: false,
            stockSearch: '',
            stockFilter: 'all',
            allStockProducts: @json($stockPrediction['products']),

            getFilteredStock() {
                return (this.allStockProducts || []).filter(p => {
                    const matchesSearch = !this.stockSearch || 
                        p.product_name.toLowerCase().includes(this.stockSearch.toLowerCase()) || 
                        (p.product_code && p.product_code.toLowerCase().includes(this.stockSearch.toLowerCase()));
                    const matchesFilter = this.stockFilter === 'all' || p.urgency === this.stockFilter;
                    return matchesSearch && matchesFilter;
                });
            },

            exportStockCsv() {
                const products = this.getFilteredStock();
                if (!products.length) return;
                let csv = 'Kode Produk,Nama Produk,Stok Saat Ini,Satuan,Kecepatan Jual (per hari),Estimasi Habis (Hari),Tanggal Estimasi Habis,Status Urgensi,Titik Reorder,Saran Reorder\n';
                products.forEach(p => {
                    csv += `"${p.product_code || ''}","${p.product_name.replace(/"/g, '""')}",${p.current_stock},"${p.unit}",${p.daily_velocity},"${p.runout_days}","${p.estimated_stockout_date || ''}","${p.urgency}",${p.reorder_point},${p.suggested_reorder_qty}\n`;
                });
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `prediksi-stok-${new Date().toISOString().slice(0,10)}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },

            exportForecastingCsv() {
                const forecasting = @json($forecasting);
                if (!forecasting || !forecasting.forecast) return;
                let csv = 'Tanggal,Hari,Proyeksi Omset,Batas Bawah,Batas Atas\n';
                forecasting.forecast.forEach(f => {
                    csv += `"${f.date}","${f.day_name}",${f.predicted_revenue},${f.lower_bound},${f.upper_bound}\n`;
                });
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `proyeksi-omset-${new Date().toISOString().slice(0,10)}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },

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
                        AppAlert.error(data.message || window.COOCA_I18N?.ai?.alerts?.error || 'Gagal menganalisis pertanyaan.');
                    }
                } catch (e) {
                    AppAlert.error(window.COOCA_I18N?.ai?.alerts?.network_error || 'Gagal menghubungi AI Engine.');
                } finally {
                    this.isAsking = false;
                }
            },

            async confirmAction() {
                if (!this.aiAnswer || !this.aiAnswer.payload || this.isExecutingAction) return;

                const isHighRisk = this.aiAnswer.risk_level === 'HIGH' || this.aiAnswer.risk_level === 'CRITICAL';
                let confirmed = true;
                if (window.AppAlert && typeof window.AppAlert.confirm === 'function') {
                    confirmed = await window.AppAlert.confirm({
                        title: isHighRisk 
                            ? (window.COOCA_LOCALE === 'en' ? 'High Risk Warning: Execute AI Action' : 'Peringatan Risiko Tinggi: Eksekusi Aksi AI')
                            : (window.COOCA_LOCALE === 'en' ? 'Confirm AI Action Execution' : 'Konfirmasi Eksekusi Aksi AI'),
                        message: isHighRisk 
                            ? (window.COOCA_I18N?.ai?.actions?.risk_high_warn || 'Aksi ini memiliki dampak finansial atau perubahan data langsung pada sistem bisnis Anda. Apakah Anda yakin ingin melanjutkannya?') 
                            : (window.COOCA_I18N?.ai?.actions?.confirm_approve_msg || 'Aksi rekomendasi AI ini akan dieksekusi langsung dan dicatat dalam audit trail. Lanjutkan?'),
                        type: isHighRisk ? 'danger' : 'warning',
                        confirmText: window.COOCA_I18N?.ai?.alerts?.confirm_yes || 'Ya, Eksekusi Sekarang',
                        cancelText: window.COOCA_I18N?.ai?.alerts?.confirm_cancel || 'Batal'
                    });
                } else {
                    confirmed = confirm(isHighRisk 
                        ? 'PERINGATAN: Aksi berisiko tinggi. Eksekusi sekarang?' 
                        : 'Konfirmasi: Eksekusi aksi rekomendasi AI sekarang?');
                }

                if (!confirmed) return;

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
                        AppAlert.success(data.message || window.COOCA_I18N?.ai?.alerts?.action_approved || 'Aksi berhasil dieksekusi.');
                        if (data.data && data.data.redirect_url) {
                            setTimeout(() => {
                                window.location.href = data.data.redirect_url;
                            }, 1000);
                        }
                    } else {
                        AppAlert.error(data.message || window.COOCA_I18N?.ai?.alerts?.error || 'Gagal mengeksekusi aksi.');
                    }
                } catch (e) {
                    AppAlert.error(window.COOCA_I18N?.ai?.alerts?.network_error || 'Gagal mengeksekusi aksi AI.');
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
            const locale = window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID';
            const currPrefix = window.COOCA_LOCALE === 'en' ? 'IDR ' : 'Rp ';

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

            const labelHistorical = window.COOCA_LOCALE === 'en' ? 'Actual Revenue (30 Days)' : 'Historis Riil (30 Hari)';
            const labelForecast = window.COOCA_LOCALE === 'en' ? 'AI Projection (14 Days)' : 'Prediksi AI (14 Hari ke Depan)';
            const labelUpper = window.COOCA_LOCALE === 'en' ? 'Upper Bound (Confidence)' : 'Batas Atas (Confidence)';
            const labelLower = window.COOCA_LOCALE === 'en' ? 'Lower Bound (Confidence)' : 'Batas Bawah (Confidence)';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allLabels,
                    datasets: [
                        {
                            label: labelHistorical,
                            data: historicalSeries,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.08)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 2,
                        },
                        {
                            label: labelForecast,
                            data: forecastSeries,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            borderDash: [5, 4],
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                        },
                        {
                            label: labelUpper,
                            data: upperSeries,
                            borderColor: 'rgba(16, 185, 129, 0.3)',
                            borderDash: [2, 2],
                            fill: false,
                            pointRadius: 0,
                        },
                        {
                            label: labelLower,
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
                        legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + currPrefix + Number(context.parsed.y || 0).toLocaleString(locale);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(150,150,150,0.1)' }, ticks: { color: '#94a3b8', maxRotation: 45, font: { size: 10, family: 'Inter' } } },
                        y: { grid: { color: 'rgba(150,150,150,0.1)' }, ticks: { color: '#94a3b8', callback: (v) => currPrefix + (v/1000) + 'k', font: { family: 'JetBrains Mono', size: 10 } } }
                    }
                }
            });
        }
    });
</script>
@endsection
