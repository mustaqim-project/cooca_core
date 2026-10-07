@extends('layouts.app', [
    'title' => __('social_media.insights_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.insights_header_title'),
    'headerSubtitle' => __('social_media.insights_header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="socialInsightsManager()">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            :title="__('social_media.insights_title')"
            :subtitle="__('social_media.insights_subtitle')">
            <x-slot:actions>
                <button @click="syncAccountsMetrics()" :disabled="isSyncingAccounts"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-xs disabled:opacity-50 cursor-pointer">
                    <i data-lucide="radio" class="w-4 h-4 text-[#007AFF]" :class="{'animate-pulse': isSyncingAccounts}"></i>
                    <span x-text="isSyncingAccounts ? '{{ __('social_media.syncing_accounts_metrics_btn') }}' : '{{ __('social_media.sync_accounts_metrics_btn') }}'">{{ __('social_media.sync_accounts_metrics_btn') }}</span>
                </button>

                <button @click="refreshAllInsights()" :disabled="isRefreshingAll"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-black dark:bg-white dark:text-black hover:opacity-90 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-50 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': isRefreshingAll}"></i>
                    <span x-text="isRefreshingAll ? '{{ __('social_media.refreshing_data_btn') }}' : '{{ __('social_media.refresh_data_btn') }}'">{{ __('social_media.refresh_data_btn') }}</span>
                </button>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- 1. BENTO HERO KPI GRID --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
            {{-- Total Audience / Followers --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold truncate">{{ __('social_media.kpi_total_audience') }}</span>
                    <i data-lucide="users-round" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                </div>
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_followers'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 truncate">
                    {{ __('social_media.connected_count', ['count' => $analytics['total_connected'] ?? 0]) }}
                </div>
            </div>

            {{-- Impressions --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold truncate">{{ __('social_media.kpi_impressions') }}</span>
                    <i data-lucide="eye" class="w-4 h-4 text-[#5856D6] shrink-0"></i>
                </div>
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_impressions'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ __('social_media.kpi_impressions_sub') }}</div>
            </div>

            {{-- Reach --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold truncate">{{ __('social_media.kpi_reach') }}</span>
                    <i data-lucide="users" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                </div>
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_reach'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ __('social_media.kpi_reach_sub') }}</div>
            </div>

            {{-- Total Engagement --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold truncate">{{ __('social_media.kpi_engagement') }}</span>
                    <i data-lucide="zap" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                </div>
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_engagement'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ __('social_media.kpi_engagement_sub') }}</div>
            </div>

            {{-- Avg Engagement Rate --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold truncate">{{ __('social_media.kpi_engagement_rate') }}</span>
                    <i data-lucide="trending-up" class="w-4 h-4 text-[#FF2D55] shrink-0"></i>
                </div>
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['engagement_rate'] ?? 0, 2) }}%
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ __('social_media.kpi_engagement_rate_sub') }}</div>
            </div>

            {{-- Avg Reach per Post --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold truncate">{{ __('social_media.kpi_avg_reach') }}</span>
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-[#AF52DE] shrink-0"></i>
                </div>
                <div class="text-[22px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['avg_reach_per_post'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 truncate">{{ __('social_media.kpi_avg_reach_sub') }}</div>
            </div>
        </div>

        {{-- 2. INTERACTIVE CHARTS (14-DAY TRENDS) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Chart 1: Reach & Impressions Trend --}}
            <div class="lg:col-span-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                <i data-lucide="activity" class="w-4 h-4 text-[#007AFF]"></i>
                                {{ __('social_media.chart_reach_impressions_title') }}
                            </h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('social_media.chart_reach_impressions_subtitle') }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] font-medium text-black/60 dark:text-white/60">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span> {{ __('social_media.chart_legend_impressions') }}</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span> {{ __('social_media.chart_legend_reach') }}</span>
                        </div>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="chartReachImpressions"></canvas>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Customer Engagement & Reaction Dynamics --}}
            <div class="lg:col-span-5 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                <i data-lucide="zap" class="w-4 h-4 text-[#FF9500]"></i>
                                {{ __('social_media.chart_engagement_title') }}
                            </h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('social_media.chart_engagement_subtitle') }}</p>
                        </div>
                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#FF9500]/10 text-[#FF9500]">
                            {{ number_format($analytics['total_engagement'] ?? 0) }} Aksi
                        </span>
                    </div>
                    <div class="h-64 sm:h-72 w-full relative">
                        <canvas id="chartEngagementTrends"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. AUDIENCE TIMING & TRAFFIC INTELLIGENCE (PEAK HOURS, LOW HOURS & HEATMAP) --}}
        <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-6">
            {{-- Header & AI Highlights --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                        <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">
                            {{ __('social_media.timing_intelligence_title') }}
                        </h3>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        {{ __('social_media.timing_intelligence_subtitle') }}
                    </p>
                </div>

                {{-- AI Badge Pill --}}
                <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[12px] font-semibold shrink-0">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Analisis Algoritma Perilaku Audiens</span>
                </div>
            </div>

            {{-- 4 Golden Highlights Bento Strip --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                {{-- Peak Golden Hours --}}
                <div class="p-4 rounded-[18px] bg-[#34C759]/[0.08] dark:bg-[#34C759]/[0.12] border border-[#34C759]/20 space-y-1.5">
                    <div class="flex items-center justify-between text-[#248A3D] dark:text-[#30D158] font-semibold text-[12px]">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="sun-medium" class="w-4 h-4"></i>
                            {{ __('social_media.golden_hours_title') }}
                        </span>
                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded-md bg-[#34C759]/20">Puncak</span>
                    </div>
                    <div class="text-[15px] font-bold text-black dark:text-white">
                        {{ $trafficTimingData['peak_hours_text'] }}
                    </div>
                    <p class="text-[11px] text-black/55 dark:text-white/55 leading-snug">
                        Istirahat siang & waktu santai malam memiliki views 3x lebih tinggi.
                    </p>
                </div>

                {{-- Low Traffic Window --}}
                <div class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1.5">
                    <div class="flex items-center justify-between text-black/60 dark:text-white/60 font-semibold text-[12px]">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="moon" class="w-4 h-4 text-neutral-400"></i>
                            {{ __('social_media.low_traffic_title') }}
                        </span>
                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded-md bg-black/5 dark:bg-white/10">Sepi</span>
                    </div>
                    <div class="text-[15px] font-bold text-black dark:text-white">
                        {{ $trafficTimingData['low_hours_text'] }}
                    </div>
                    <p class="text-[11px] text-black/55 dark:text-white/55 leading-snug">
                        Hindari publikasi promo penting di jam ini karena audiens sedang offline.
                    </p>
                </div>

                {{-- Best Days to Post --}}
                <div class="p-4 rounded-[18px] bg-[#007AFF]/[0.08] dark:bg-[#007AFF]/[0.12] border border-[#007AFF]/20 space-y-1.5">
                    <div class="flex items-center justify-between text-[#007AFF] font-semibold text-[12px]">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="calendar-check-2" class="w-4 h-4"></i>
                            {{ __('social_media.best_days_title') }}
                        </span>
                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded-md bg-[#007AFF]/20">Efektif</span>
                    </div>
                    <div class="text-[15px] font-bold text-black dark:text-white">
                        {{ $trafficTimingData['best_days_text'] }}
                    </div>
                    <p class="text-[11px] text-black/55 dark:text-white/55 leading-snug">
                        Menjelang akhir pekan menghasilkan interaksi belanja paling tinggi.
                    </p>
                </div>

                {{-- Top Content Format --}}
                <div class="p-4 rounded-[18px] bg-[#AF52DE]/[0.08] dark:bg-[#AF52DE]/[0.12] border border-[#AF52DE]/20 space-y-1.5">
                    <div class="flex items-center justify-between text-[#AF52DE] font-semibold text-[12px]">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="clapperboard" class="w-4 h-4"></i>
                            {{ __('social_media.best_format_title') }}
                        </span>
                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded-md bg-[#AF52DE]/20">Viral</span>
                    </div>
                    <div class="text-[14px] font-bold text-black dark:text-white truncate">
                        {{ $trafficTimingData['best_format_text'] }}
                    </div>
                    <p class="text-[11px] text-black/55 dark:text-white/55 leading-snug">
                        Video pendek & multi-foto katalog menjangkau 2.4x akun baru.
                    </p>
                </div>
            </div>

            {{-- 2-Column: 24-Hour Curve vs Weekly Day Distribution --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 pt-2">
                {{-- 24-Hour Intensity Curve --}}
                <div class="lg:col-span-7 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-[13px] font-bold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-4 h-4 text-[#007AFF]"></i>
                                {{ __('social_media.hourly_traffic_curve') }}
                            </h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('social_media.hourly_traffic_curve_sub') }}</p>
                        </div>
                        <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">
                            Peak: 12.00 & 19.00
                        </span>
                    </div>
                    <div class="h-48 sm:h-56 w-full relative">
                        <canvas id="chartHourlyCurve"></canvas>
                    </div>
                </div>

                {{-- Weekly Day Breakdown --}}
                <div class="lg:col-span-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 sm:p-5 space-y-3">
                    <div>
                        <h4 class="text-[13px] font-bold text-black dark:text-white flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-[#AF52DE]"></i>
                            {{ __('social_media.weekly_traffic_overview') }}
                        </h4>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('social_media.weekly_traffic_overview_sub') }}</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @foreach($trafficTimingData['daily'] as $day)
                            <div class="flex items-center justify-between gap-3 text-[12px] p-2 rounded-[12px] {{ $day['is_best'] ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-white dark:bg-[#252528] border border-black/[0.04] dark:border-white/[0.06]' }}">
                                <div class="flex items-center gap-2.5 w-24 shrink-0">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold {{ $day['is_best'] ? 'bg-emerald-500 text-white' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                                        {{ $day['short'] }}
                                    </span>
                                    <span class="font-semibold text-black dark:text-white">{{ $day['name'] }}</span>
                                </div>

                                {{-- Progress Bar --}}
                                <div class="flex-1 h-2 rounded-full bg-black/5 dark:bg-white/10 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $day['is_best'] ? 'bg-emerald-500' : 'bg-[#007AFF]' }}"
                                        style="width: {{ $day['views_pct'] }}%"></div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[11px] font-bold tabular-nums text-black/70 dark:text-white/70">{{ $day['views_pct'] }}%</span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $day['badge_class'] }}">
                                        {{ $day['badge'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Strategic 7-Day x 6-TimeBlock Heatmap Grid --}}
            <div class="space-y-3 pt-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-[13px] font-bold text-black dark:text-white flex items-center gap-1.5">
                            <i data-lucide="grid" class="w-4 h-4 text-[#FF9500]"></i>
                            {{ __('social_media.traffic_heatmap_title') }}
                        </h4>
                        <p class="text-[11px] text-black/50 dark:text-white/50">{{ __('social_media.traffic_heatmap_subtitle') }}</p>
                    </div>

                    {{-- Heatmap Legend --}}
                    <div class="flex items-center gap-2 text-[10px] text-black/50 dark:text-white/50">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-black/5 dark:bg-white/10"></span> {{ __('social_media.intensity_low') }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-[#007AFF]/30"></span> {{ __('social_media.intensity_normal') }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-[#007AFF]/70"></span> {{ __('social_media.intensity_high') }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500"></span> {{ __('social_media.intensity_peak') }}</span>
                    </div>
                </div>

                {{-- Table Heatmap --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] border-collapse min-w-[620px]">
                        <thead>
                            <tr class="text-black/50 dark:text-white/50 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <th class="py-2 px-3 text-left font-semibold w-24">Hari</th>
                                <th class="py-2 px-3 text-center font-medium">{{ __('social_media.time_block_dawn') }}</th>
                                <th class="py-2 px-3 text-center font-medium">{{ __('social_media.time_block_morning') }}</th>
                                <th class="py-2 px-3 text-center font-medium">{{ __('social_media.time_block_work') }}</th>
                                <th class="py-2 px-3 text-center font-medium">{{ __('social_media.time_block_lunch') }}</th>
                                <th class="py-2 px-3 text-center font-medium">{{ __('social_media.time_block_evening') }}</th>
                                <th class="py-2 px-3 text-center font-medium">{{ __('social_media.time_block_night') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @foreach($trafficTimingData['heatmap'] as $dayName => $slots)
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-black dark:text-white">{{ $dayName }}</td>
                                    @foreach($slots as $slotScore)
                                        @php
                                            $cellBg = match($slotScore) {
                                                4 => 'bg-emerald-500 text-white shadow-xs font-bold',
                                                3 => 'bg-[#007AFF]/70 text-white font-medium',
                                                2 => 'bg-[#007AFF]/25 text-[#007AFF] dark:text-blue-300',
                                                default => 'bg-black/[0.03] dark:bg-white/[0.04] text-black/40 dark:text-white/40',
                                            };
                                            $cellText = match($slotScore) {
                                                4 => 'Puncak',
                                                3 => 'Ramai',
                                                2 => 'Normal',
                                                default => 'Sepi',
                                            };
                                        @endphp
                                        <td class="py-2 px-2 text-center">
                                            <div class="py-1.5 px-2 rounded-[8px] {{ $cellBg }} flex items-center justify-center gap-1 transition-all hover:scale-[1.03]">
                                                @if($slotScore === 4)
                                                    <i data-lucide="sparkles" class="w-3 h-3 text-amber-300 shrink-0"></i>
                                                @endif
                                                <span>{{ $cellText }}</span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Strategic Takeaway Card --}}
                <div class="p-3.5 rounded-[16px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/15 flex items-start gap-3 text-[12px] text-black/80 dark:text-white/85 leading-relaxed">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-black dark:text-white">{{ __('social_media.ai_timing_recommendation') }}</span>
                        {{ $trafficTimingData['summary'] }}
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. CONTENT FORMAT MATRIX & TOP PERFORMING POSTS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Content Format Matrix --}}
            <div class="lg:col-span-5 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-[#AF52DE]"></i>
                        {{ __('social_media.content_format_matrix_title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('social_media.content_format_matrix_subtitle') }}</p>
                </div>

                <div class="space-y-3 pt-1">
                    @foreach($formatStats as $key => $fmt)
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[12px] flex items-center justify-center shrink-0" style="background-color: {{ $fmt['color'] }}15; color: {{ $fmt['color'] }};">
                                    <i data-lucide="{{ $fmt['icon'] }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-[13px] font-bold text-black dark:text-white">{{ $fmt['name'] }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ $fmt['count'] }} Postingan Terbit</div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-[13px] font-bold text-black dark:text-white tabular-nums">
                                    {{ number_format($fmt['reach']) }} Reach
                                </div>
                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold tabular-nums">
                                    {{ number_format($fmt['engagement']) }} Interaksi
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Performing Posts --}}
            <div class="lg:col-span-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-[#FF9500]"></i>
                            {{ __('social_media.top_posts_title') }}
                        </h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('social_media.top_posts_subtitle') }}</p>
                    </div>

                    <a href="{{ route('social-media.posts.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                        Lihat Semua Postingan <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                @if($topPosts->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                        @foreach($topPosts as $tp)
                            @php
                                $tpImp = $tp->getMetric('impressions');
                                $tpReach = $tp->getMetric('reach');
                                $tpLikes = $tp->getMetric('likes');
                                $tpComments = $tp->getMetric('comments');
                                $tpShares = $tp->getMetric('shares');
                                $tpMediaUrl = ($tp->media_urls && count($tp->media_urls) > 0) ? $tp->media_urls[0] : null;
                            @endphp
                            <div class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.08] flex flex-col justify-between gap-3 hover:border-[#007AFF]/30 transition-all">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            @foreach($tp->targets as $t)
                                                @php
                                                    $plat = strtolower((string)$t->platform);
                                                    $badgeBg = match($plat) {
                                                        'facebook' => 'bg-[#1877F2]/10 text-[#1877F2]',
                                                        'instagram' => 'bg-[#E4405F]/10 text-[#E4405F]',
                                                        'tiktok' => 'bg-black/10 dark:bg-white/10 text-black dark:text-white',
                                                        'linkedin' => 'bg-[#0A66C2]/10 text-[#0A66C2]',
                                                        default => 'bg-neutral-100 text-neutral-600',
                                                    };
                                                @endphp
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $badgeBg }}">
                                                    {{ strtoupper($plat) }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <span class="text-[10px] font-semibold text-black/45 dark:text-white/45">
                                            {{ $tp->published_at ? $tp->published_at->format('d M H:i') : '' }}
                                        </span>
                                    </div>

                                    <div class="flex items-start gap-2.5">
                                        @if($tpMediaUrl)
                                            <img src="{{ $tpMediaUrl }}" alt="Media" class="w-12 h-12 rounded-[10px] object-cover shrink-0 border border-black/5 dark:border-white/10">
                                        @else
                                            <div class="w-12 h-12 rounded-[10px] bg-black/5 dark:bg-white/5 flex items-center justify-center shrink-0 text-black/40 dark:text-white/40">
                                                <i data-lucide="file-text" class="w-5 h-5"></i>
                                            </div>
                                        @endif
                                        <p class="text-[12px] text-black/80 dark:text-white/80 line-clamp-2 leading-relaxed">
                                            {{ $tp->content }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Metrics Row --}}
                                <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] grid grid-cols-4 gap-1 text-center text-[10px]">
                                    <div>
                                        <span class="text-black/45 dark:text-white/45 block">Reach</span>
                                        <span class="font-bold text-black dark:text-white tabular-nums">{{ number_format($tpReach) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-black/45 dark:text-white/45 block">Suka</span>
                                        <span class="font-bold text-[#FF2D55] tabular-nums">{{ number_format($tpLikes) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-black/45 dark:text-white/45 block">Komen</span>
                                        <span class="font-bold text-[#AF52DE] tabular-nums">{{ number_format($tpComments) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-black/45 dark:text-white/45 block">Share</span>
                                        <span class="font-bold text-[#007AFF] tabular-nums">{{ number_format($tpShares) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <i data-lucide="sparkles" class="w-8 h-8 text-black/20 dark:text-white/20 mx-auto mb-2"></i>
                        <p class="text-[13px] font-semibold text-black/70 dark:text-white/70">Belum Ada Riwayat Konten</p>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Postingan yang dibuat via Unified Composer akan diurutkan secara otomatis berdasarkan performa di sini.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 5. CONNECTED CHANNELS PERFORMANCE BENTO HUB --}}
        <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="share-2" class="w-4 h-4 text-[#007AFF]"></i>
                        {{ __('social_media.channels_hub_title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('social_media.channels_hub_subtitle') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Facebook Card --}}
                @php $fb = $channelInsights['facebook']; @endphp
                <div class="rounded-[20px] p-4.5 border transition-all flex flex-col justify-between gap-4 {{ $fb['connected'] ? 'bg-[#1877F2]/[0.03] dark:bg-[#1877F2]/[0.06] border-[#1877F2]/20' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/5' }}">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#1877F2] text-white flex items-center justify-center shrink-0">
                                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">Facebook</h4>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[140px]">{{ $fb['connected'] ? ($fb['account']->account_name ?? 'Halaman Facebook') : 'Halaman Bisnis' }}</p>
                                </div>
                            </div>
                            @if($fb['connected'])
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-full">Belum</span>
                            @endif
                        </div>

                        @if($fb['connected'])
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_followers') }}</span>
                                    <span class="text-[16px] font-bold text-black dark:text-white tabular-nums">{{ number_format($fb['followers']) }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_engagement_rate') }}</span>
                                    <span class="text-[16px] font-bold text-[#1877F2] tabular-nums">{{ number_format($fb['engagement_rate'], 1) }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(!$fb['connected'])
                        <a href="{{ route('social-media.index') }}" class="w-full h-8 text-[11px] font-semibold rounded-[10px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black dark:text-white flex items-center justify-center gap-1 transition-all">
                            {{ __('social_media.channel_connect_cta') }} <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                {{-- Instagram Card --}}
                @php $ig = $channelInsights['instagram']; @endphp
                <div class="rounded-[20px] p-4.5 border transition-all flex flex-col justify-between gap-4 {{ $ig['connected'] ? 'bg-[#E4405F]/[0.03] dark:bg-[#E4405F]/[0.06] border-[#E4405F]/20' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/5' }}">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#FFB700] via-[#E4405F] to-[#833AB4] text-white flex items-center justify-center shrink-0">
                                    <i data-lucide="instagram" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">Instagram</h4>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[140px]">{{ $ig['connected'] ? ($ig['account']->username ?? $ig['account']->account_name ?? '@toko') : 'Akun Profesional' }}</p>
                                </div>
                            </div>
                            @if($ig['connected'])
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-full">Belum</span>
                            @endif
                        </div>

                        @if($ig['connected'])
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_followers') }}</span>
                                    <span class="text-[16px] font-bold text-black dark:text-white tabular-nums">{{ number_format($ig['followers']) }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_engagement_rate') }}</span>
                                    <span class="text-[16px] font-bold text-[#E4405F] tabular-nums">{{ number_format($ig['engagement_rate'], 1) }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(!$ig['connected'])
                        <a href="{{ route('social-media.index') }}" class="w-full h-8 text-[11px] font-semibold rounded-[10px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black dark:text-white flex items-center justify-center gap-1 transition-all">
                            {{ __('social_media.channel_connect_cta') }} <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                {{-- TikTok Card --}}
                @php $tt = $channelInsights['tiktok']; @endphp
                <div class="rounded-[20px] p-4.5 border transition-all flex flex-col justify-between gap-4 {{ $tt['connected'] ? 'bg-black/[0.04] dark:bg-white/[0.06] border-black/15 dark:border-white/15' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/5' }}">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-black dark:bg-white text-white dark:text-black flex items-center justify-center shrink-0 font-bold text-[13px]">
                                    TT
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">TikTok</h4>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[140px]">{{ $tt['connected'] ? ($tt['account']->account_name ?? 'Akun TikTok') : 'Kreator Bisnis' }}</p>
                                </div>
                            </div>
                            @if($tt['connected'])
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-full">Belum</span>
                            @endif
                        </div>

                        @if($tt['connected'])
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_followers') }}</span>
                                    <span class="text-[16px] font-bold text-black dark:text-white tabular-nums">{{ number_format($tt['followers']) }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_engagement_rate') }}</span>
                                    <span class="text-[16px] font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($tt['engagement_rate'], 1) }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(!$tt['connected'])
                        <a href="{{ route('social-media.index') }}" class="w-full h-8 text-[11px] font-semibold rounded-[10px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black dark:text-white flex items-center justify-center gap-1 transition-all">
                            {{ __('social_media.channel_connect_cta') }} <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                {{-- LinkedIn Card --}}
                @php $li = $channelInsights['linkedin']; @endphp
                <div class="rounded-[20px] p-4.5 border transition-all flex flex-col justify-between gap-4 {{ $li['connected'] ? 'bg-[#0A66C2]/[0.03] dark:bg-[#0A66C2]/[0.06] border-[#0A66C2]/20' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/5 dark:border-white/5' }}">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#0A66C2] text-white flex items-center justify-center shrink-0">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.762-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">LinkedIn</h4>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[140px]">{{ $li['connected'] ? ($li['account']->account_name ?? 'Halaman Perusahaan') : 'Company Page' }}</p>
                                </div>
                            </div>
                            @if($li['connected'])
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-full">Belum</span>
                            @endif
                        </div>

                        @if($li['connected'])
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_followers') }}</span>
                                    <span class="text-[16px] font-bold text-black dark:text-white tabular-nums">{{ number_format($li['followers']) }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 block">{{ __('social_media.stat_engagement_rate') }}</span>
                                    <span class="text-[16px] font-bold text-[#0A66C2] tabular-nums">{{ number_format($li['engagement_rate'], 1) }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(!$li['connected'])
                        <a href="{{ route('social-media.index') }}" class="w-full h-8 text-[11px] font-semibold rounded-[10px] bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black dark:text-white flex items-center justify-center gap-1 transition-all">
                            {{ __('social_media.channel_connect_cta') }} <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- 6. RECENT POST INSIGHTS TABLE & LIST --}}
        <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF]"></i>
                        {{ __('social_media.recent_insights_title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('social_media.recent_insights_subtitle') }}</p>
                </div>

                {{-- Live Search & Channel Filter --}}
                <div class="flex items-center gap-2">
                    <div class="relative min-w-[200px]">
                        <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text" x-model="searchQuery" placeholder="Cari konten postingan..."
                            class="w-full h-9 pl-9 pr-3 rounded-[10px] text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-black dark:text-white placeholder:text-black/40 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    </div>
                </div>
            </div>

            @if($posts->isEmpty())
                <div class="p-8 sm:p-12 text-center rounded-[20px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-4">
                    <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                        <i data-lucide="bar-chart" class="w-6 h-6"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('social_media.no_insights_title') }}</h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50 leading-relaxed">{{ __('social_media.no_insights_desc') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('social-media.posts.index') }}" class="inline-flex items-center gap-2 px-4 py-2 min-h-[44px] sm:min-h-0 sm:h-9 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all shadow-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            {{ __('social_media.write_post_now_btn') }}
                        </a>
                    </div>
                </div>
            @else
                {{-- Desktop Table View --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-[13px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] text-black/50 dark:text-white/50 text-[11px] uppercase tracking-wider font-bold">
                                <th class="py-3 px-3">{{ __('social_media.th_post') }}</th>
                                <th class="py-3 px-3">{{ __('social_media.th_channel') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('social_media.th_impressions') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('social_media.th_reach') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('social_media.th_likes') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('social_media.th_comments') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('social_media.th_shares') }}</th>
                                <th class="py-3 px-3 text-right">{{ __('social_media.th_engagement_rate') }}</th>
                                <th class="py-3 px-3 text-center">{{ __('social_media.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach($posts as $post)
                                @php
                                    $pReach = $post->getMetric('reach');
                                    $pImp = $post->getMetric('impressions');
                                    $pLikes = $post->getMetric('likes');
                                    $pComments = $post->getMetric('comments');
                                    $pShares = $post->getMetric('shares');
                                    $pTotalEng = $pLikes + $pComments + $pShares;
                                    $pRate = $pReach > 0 ? round(($pTotalEng / $pReach) * 100, 1) : 0.0;
                                @endphp
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors"
                                    x-show="matchesSearch('{{ addslashes(strtolower($post->content)) }}')">
                                    <td class="py-3 px-3 max-w-[260px]">
                                        <div class="flex items-center gap-2.5">
                                            @if($post->media_urls && count($post->media_urls) > 0)
                                                <img src="{{ $post->media_urls[0] }}" alt="Thumbnail" class="w-9 h-9 rounded-[8px] object-cover shrink-0 border border-black/5 dark:border-white/10">
                                            @else
                                                <div class="w-9 h-9 rounded-[8px] bg-black/5 dark:bg-white/5 flex items-center justify-center shrink-0 text-black/40 dark:text-white/40">
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                </div>
                                            @endif
                                            <div class="overflow-hidden">
                                                <p class="text-black dark:text-white font-medium truncate">{{ $post->content }}</p>
                                                <span class="text-[11px] text-black/45 dark:text-white/45">{{ $post->published_at ? $post->published_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            @foreach($post->targets as $t)
                                                @php
                                                    $plat = strtolower((string)$t->platform);
                                                    $badgeStyle = match($plat) {
                                                        'facebook' => 'bg-[#1877F2]/10 text-[#1877F2]',
                                                        'instagram' => 'bg-[#E4405F]/10 text-[#E4405F]',
                                                        'tiktok' => 'bg-black/10 dark:bg-white/10 text-black dark:text-white',
                                                        'linkedin' => 'bg-[#0A66C2]/10 text-[#0A66C2]',
                                                        default => 'bg-neutral-100 text-neutral-600',
                                                    };
                                                @endphp
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $badgeStyle }}">
                                                    {{ strtoupper($plat) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-impressions-{{ $post->id }}">
                                        {{ number_format($pImp) }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-reach-{{ $post->id }}">
                                        {{ number_format($pReach) }}
                                    </td>
                                    <td class="py-3 px-3 text-right tabular-nums text-[#FF2D55] font-semibold" id="metric-likes-{{ $post->id }}">
                                        {{ number_format($pLikes) }}
                                    </td>
                                    <td class="py-3 px-3 text-right tabular-nums text-[#AF52DE] font-semibold" id="metric-comments-{{ $post->id }}">
                                        {{ number_format($pComments) }}
                                    </td>
                                    <td class="py-3 px-3 text-right tabular-nums text-[#007AFF] font-semibold" id="metric-shares-{{ $post->id }}">
                                        {{ number_format($pShares) }}
                                    </td>
                                    <td class="py-3 px-3 text-right tabular-nums text-emerald-600 dark:text-emerald-400 font-bold" id="metric-rate-{{ $post->id }}">
                                        {{ number_format($pRate, 1) }}%
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <button type="button" @click="syncPostInsights('{{ $post->id }}')"
                                            id="sync-btn-{{ $post->id }}"
                                            class="min-h-[36px] px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-95 transition-all text-black/70 dark:text-white/70 inline-flex items-center gap-1 cursor-pointer">
                                            <i data-lucide="refresh-cw" class="w-3 h-3" id="icon-sync-{{ $post->id }}"></i>
                                            <span>{{ __('social_media.fetch_live_btn') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Stack Cards --}}
                <div class="md:hidden space-y-3">
                    @foreach($posts as $post)
                        @php
                            $pReach = $post->getMetric('reach');
                            $pImp = $post->getMetric('impressions');
                            $pLikes = $post->getMetric('likes');
                            $pComments = $post->getMetric('comments');
                            $pShares = $post->getMetric('shares');
                            $pTotalEng = $pLikes + $pComments + $pShares;
                            $pRate = $pReach > 0 ? round(($pTotalEng / $pReach) * 100, 1) : 0.0;
                        @endphp
                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3"
                            x-show="matchesSearch('{{ addslashes(strtolower($post->content)) }}')">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-start gap-2.5">
                                    @if($post->media_urls && count($post->media_urls) > 0)
                                        <img src="{{ $post->media_urls[0] }}" alt="Thumb" class="w-10 h-10 rounded-[8px] object-cover shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-[8px] bg-black/5 dark:bg-white/5 flex items-center justify-center shrink-0 text-black/40 dark:text-white/40">
                                            <i data-lucide="file-text" class="w-4 h-4"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-[13px] font-semibold text-black dark:text-white line-clamp-2">{{ $post->content }}</p>
                                        <span class="text-[10px] text-black/45 dark:text-white/45">{{ $post->published_at ? $post->published_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                                    </div>
                                </div>

                                <button type="button" @click="syncPostInsights('{{ $post->id }}')"
                                    id="mobile-sync-btn-{{ $post->id }}"
                                    class="min-h-[44px] min-w-[44px] rounded-[10px] bg-black/5 dark:bg-white/5 flex items-center justify-center text-black/60 dark:text-white/60 shrink-0">
                                    <i data-lucide="refresh-cw" class="w-4 h-4" id="mobile-icon-sync-{{ $post->id }}"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-4 gap-2 pt-2 border-t border-black/5 dark:border-white/5 text-center text-[10px]">
                                <div>
                                    <span class="text-black/45 dark:text-white/45 block">Reach</span>
                                    <span class="font-bold text-black dark:text-white tabular-nums" id="mobile-metric-reach-{{ $post->id }}">{{ number_format($pReach) }}</span>
                                </div>
                                <div>
                                    <span class="text-black/45 dark:text-white/45 block">Suka</span>
                                    <span class="font-bold text-[#FF2D55] tabular-nums" id="mobile-metric-likes-{{ $post->id }}">{{ number_format($pLikes) }}</span>
                                </div>
                                <div>
                                    <span class="text-black/45 dark:text-white/45 block">Komentar</span>
                                    <span class="font-bold text-[#AF52DE] tabular-nums" id="mobile-metric-comments-{{ $post->id }}">{{ number_format($pComments) }}</span>
                                </div>
                                <div>
                                    <span class="text-black/45 dark:text-white/45 block">Rate</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($pRate, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- INTERACTIVE CHART.JS & ALPINE LOGIC --}}
    <script>
        function socialInsightsManager() {
            return {
                isRefreshingAll: false,
                isSyncingAccounts: false,
                searchQuery: '',
                trendData: @json($trendData),
                trafficTimingData: @json($trafficTimingData),
                chartReachInstance: null,
                chartEngagementInstance: null,
                chartHourlyInstance: null,

                init() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.initCharts();
                    });

                    // Re-render charts on dark mode change
                    const observer = new MutationObserver(() => {
                        this.initCharts();
                    });
                    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
                },

                matchesSearch(text) {
                    if (!this.searchQuery || this.searchQuery.trim() === '') return true;
                    return text.includes(this.searchQuery.toLowerCase().trim());
                },

                initCharts() {
                    if (typeof Chart === 'undefined') return;

                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#A1A1AA' : '#71717A';
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.04)';

                    // 1. Chart Reach & Impressions
                    const ctxReach = document.getElementById('chartReachImpressions');
                    if (ctxReach) {
                        if (this.chartReachInstance) this.chartReachInstance.destroy();
                        this.chartReachInstance = new Chart(ctxReach, {
                            type: 'line',
                            data: {
                                labels: this.trendData.labels,
                                datasets: [
                                    {
                                        label: 'Tayangan (Impressions)',
                                        data: this.trendData.impressions,
                                        borderColor: '#007AFF',
                                        backgroundColor: isDark ? 'rgba(0, 122, 255, 0.12)' : 'rgba(0, 122, 255, 0.06)',
                                        borderWidth: 2.5,
                                        fill: true,
                                        tension: 0.35,
                                        pointRadius: 3,
                                        pointHoverRadius: 6,
                                    },
                                    {
                                        label: 'Jangkauan (Reach)',
                                        data: this.trendData.reach,
                                        borderColor: '#34C759',
                                        backgroundColor: 'transparent',
                                        borderWidth: 2,
                                        borderDash: [3, 3],
                                        tension: 0.35,
                                        pointRadius: 3,
                                        pointHoverRadius: 6,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        padding: 10,
                                        cornerRadius: 10,
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
                                            callback: (v) => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 2. Chart Engagement Trends
                    const ctxEng = document.getElementById('chartEngagementTrends');
                    if (ctxEng) {
                        if (this.chartEngagementInstance) this.chartEngagementInstance.destroy();
                        this.chartEngagementInstance = new Chart(ctxEng, {
                            type: 'bar',
                            data: {
                                labels: this.trendData.labels,
                                datasets: [
                                    {
                                        label: 'Total Interaksi',
                                        data: this.trendData.engagement,
                                        backgroundColor: '#FF9500',
                                        borderRadius: 6,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: { color: textColor, font: { size: 10 } }
                                    },
                                    y: {
                                        grid: { color: gridColor },
                                        ticks: { color: textColor, font: { size: 10 } }
                                    }
                                }
                            }
                        });
                    }

                    // 3. Chart 24-Hour Traffic Curve
                    const ctxHourly = document.getElementById('chartHourlyCurve');
                    if (ctxHourly && this.trafficTimingData.hourly_labels) {
                        if (this.chartHourlyInstance) this.chartHourlyInstance.destroy();

                        const barColors = this.trafficTimingData.hourly_scores.map((score, idx) => {
                            if (idx >= 11 && idx <= 13) return '#34C759'; // Siang Peak
                            if (idx >= 19 && idx <= 21) return '#34C759'; // Malam Peak
                            if (idx >= 1 && idx <= 5) return isDark ? '#3F3F46' : '#E4E4E7'; // Low Hours
                            return '#007AFF'; // Normal
                        });

                        this.chartHourlyInstance = new Chart(ctxHourly, {
                            type: 'bar',
                            data: {
                                labels: this.trafficTimingData.hourly_labels,
                                datasets: [{
                                    label: 'Trafik Views (%)',
                                    data: this.trafficTimingData.hourly_scores,
                                    backgroundColor: barColors,
                                    borderRadius: 4,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => 'Aktivitas Trafik: ' + ctx.parsed.y + '%'
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false },
                                        ticks: { color: textColor, font: { size: 9 }, maxRotation: 0 }
                                    },
                                    y: {
                                        grid: { color: gridColor },
                                        ticks: { color: textColor, font: { size: 9 } }
                                    }
                                }
                            }
                        });
                    }
                },

                async syncAccountsMetrics() {
                    this.isSyncingAccounts = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch('{{ route("social-media.insights.accounts.sync") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        const data = await res.json();
                        if (data.success) {
                            if (window.AppAlert) {
                                AppAlert.success(data.message || '{{ __("social_media.accounts_insights_refreshed") }}');
                            }
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            if (window.AppAlert) {
                                AppAlert.error(data.error || 'Gagal menyinkronkan metrik akun.');
                            }
                        }
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error('Terjadi kesalahan jaringan saat menyinkronkan akun.');
                        }
                    } finally {
                        this.isSyncingAccounts = false;
                    }
                },

                async refreshAllInsights() {
                    this.isRefreshingAll = true;
                    try {
                        await this.syncAccountsMetrics();
                        const syncButtons = document.querySelectorAll('button[id^="sync-btn-"]');
                        if (syncButtons.length > 0) {
                            for (const btn of syncButtons) {
                                const postId = btn.id.replace('sync-btn-', '');
                                if (postId) {
                                    await this.syncPostInsights(postId);
                                }
                            }
                        }
                        if (window.AppAlert) {
                            AppAlert.success('{{ __("social_media.insights_refreshed") }}');
                        }
                        setTimeout(() => window.location.reload(), 600);
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error('Sebagian pembaruan data wawasan mengalami kendala.');
                        }
                    } finally {
                        this.isRefreshingAll = false;
                    }
                },

                async syncPostInsights(postId) {
                    const icon = document.getElementById(`icon-sync-${postId}`);
                    const mobileIcon = document.getElementById(`mobile-icon-sync-${postId}`);
                    if (icon) icon.classList.add('animate-spin');
                    if (mobileIcon) mobileIcon.classList.add('animate-spin');

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(`/social-media/insights/${postId}/sync`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        const data = await res.json();
                        if (data.success && data.metrics) {
                            const m = data.metrics;
                            const updateEl = (id, val) => {
                                const el = document.getElementById(id);
                                if (el) el.textContent = (val || 0).toLocaleString();
                            };
                            updateEl(`metric-impressions-${postId}`, m.impressions);
                            updateEl(`mobile-metric-impressions-${postId}`, m.impressions);
                            updateEl(`metric-reach-${postId}`, m.reach);
                            updateEl(`mobile-metric-reach-${postId}`, m.reach);
                            updateEl(`metric-likes-${postId}`, m.likes);
                            updateEl(`mobile-metric-likes-${postId}`, m.likes);
                            updateEl(`metric-comments-${postId}`, m.comments);
                            updateEl(`mobile-metric-comments-${postId}`, m.comments);
                            updateEl(`metric-shares-${postId}`, m.shares);

                            if (window.AppAlert) {
                                AppAlert.success('{{ __("social_media.insights_refreshed") }}');
                            }
                        } else {
                            const errorMsg = data.error || 'Gagal memperbarui metrik postingan.';
                            if (window.AppAlert) {
                                AppAlert.error(errorMsg);
                            }
                        }
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error('Terjadi kesalahan jaringan.');
                        }
                    } finally {
                        if (icon) icon.classList.remove('animate-spin');
                        if (mobileIcon) mobileIcon.classList.remove('animate-spin');
                    }
                }
            };
        }
    </script>
@endsection
