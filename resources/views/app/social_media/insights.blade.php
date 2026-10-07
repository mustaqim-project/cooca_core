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
                <button @click="refreshAllInsights()" :disabled="isRefreshingAll"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-50 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': isRefreshingAll}"></i>
                    <span x-text="isRefreshingAll ? '{{ __('social_media.refreshing_data_btn') }}' : '{{ __('social_media.refresh_data_btn') }}'">{{ __('social_media.refresh_data_btn') }}</span>
                </button>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- 1. BENTO HERO KPI GRID --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Total Audience / Followers --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">{{ __('social_media.kpi_total_audience') }}</span>
                    <i data-lucide="users-round" class="w-4 h-4 text-[#007AFF]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_followers'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">
                    {{ __('social_media.connected_count', ['count' => $analytics['total_connected'] ?? 0]) }}
                </div>
            </div>

            {{-- Impressions --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">{{ __('social_media.kpi_impressions') }}</span>
                    <i data-lucide="eye" class="w-4 h-4 text-[#5856D6]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_impressions'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('social_media.kpi_impressions_sub') }}</div>
            </div>

            {{-- Reach --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">{{ __('social_media.kpi_reach') }}</span>
                    <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_reach'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('social_media.kpi_reach_sub') }}</div>
            </div>

            {{-- Engagement --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">{{ __('social_media.kpi_engagement') }}</span>
                    <i data-lucide="zap" class="w-4 h-4 text-[#FF9500]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_engagement'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('social_media.kpi_engagement_sub') }}</div>
            </div>

            {{-- Likes --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">{{ __('social_media.kpi_likes') }}</span>
                    <i data-lucide="heart" class="w-4 h-4 text-[#FF2D55]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_likes'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('social_media.kpi_likes_sub') }}</div>
            </div>

            {{-- Comments --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-1">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                    <span class="text-[12px] font-semibold">{{ __('social_media.kpi_comments') }}</span>
                    <i data-lucide="message-circle" class="w-4 h-4 text-[#AF52DE]"></i>
                </div>
                <div class="text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    {{ number_format($analytics['total_comments'] ?? 0) }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('social_media.kpi_comments_sub') }}</div>
            </div>
        </div>

        {{-- 2. CONNECTED CHANNELS PERFORMANCE BENTO HUB --}}
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.channels_hub_title') }}</h2>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">{{ __('social_media.channels_hub_subtitle') }}</p>
                </div>
                <button @click="syncAccountsMetrics()" :disabled="isSyncingAccounts"
                    class="min-h-[38px] px-3.5 rounded-[10px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.97] transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{'animate-spin': isSyncingAccounts}"></i>
                    <span x-text="isSyncingAccounts ? '{{ __('social_media.syncing_accounts_metrics_btn') }}' : '{{ __('social_media.sync_accounts_metrics_btn') }}'">{{ __('social_media.sync_accounts_metrics_btn') }}</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Facebook Page Card --}}
                @php
                    $fb = $channelInsights['facebook'] ?? [];
                    $isFbConnected = !empty($fb['connected']);
                    $fbAccount = $fb['account'] ?? null;
                    $fbMetrics = $fb['metrics'] ?? [];
                @endphp
                <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 space-y-3.5 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-[10px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center font-bold text-[14px]">
                                    <i data-lucide="facebook" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-bold text-black dark:text-white leading-none">Facebook Page</h3>
                                    <span class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $isFbConnected ? ($fbAccount->account_name ?? 'Halaman FB') : __('social_media.channel_status_not_connected') }}
                                    </span>
                                </div>
                            </div>
                            @if($isFbConnected)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    <span>{{ __('social_media.channel_status_connected') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ __('social_media.channel_status_not_connected') }}
                                </span>
                            @endif
                        </div>

                        @if($isFbConnected)
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">{{ __('social_media.stat_followers') }}</div>
                                    <div class="text-[15px] font-bold tabular-nums text-black dark:text-white mt-0.5">
                                        {{ number_format($fb['followers'] ?? 0) }}
                                    </div>
                                </div>
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">{{ __('social_media.stat_talking_about') }}</div>
                                    <div class="text-[15px] font-bold tabular-nums text-[#007AFF] mt-0.5">
                                        {{ number_format($fb['talking'] ?? 0) }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-3 text-center">
                                <a href="{{ route('social-media.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                                    <span>{{ __('social_media.channel_connect_cta') }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Instagram Business Card --}}
                @php
                    $ig = $channelInsights['instagram'] ?? [];
                    $isIgConnected = !empty($ig['connected']);
                    $igAccount = $ig['account'] ?? null;
                    $igMetrics = $ig['metrics'] ?? [];
                @endphp
                <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 space-y-3.5 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-[10px] bg-[#E1306C]/10 text-[#E1306C] flex items-center justify-center font-bold text-[14px]">
                                    <i data-lucide="instagram" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-bold text-black dark:text-white leading-none">Instagram Bisnis</h3>
                                    <span class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $isIgConnected ? ($igAccount->username ?? $igAccount->account_name ?? 'IG Account') : __('social_media.channel_status_not_connected') }}
                                    </span>
                                </div>
                            </div>
                            @if($isIgConnected)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    <span>{{ __('social_media.channel_status_connected') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ __('social_media.channel_status_not_connected') }}
                                </span>
                            @endif
                        </div>

                        @if($isIgConnected)
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">{{ __('social_media.stat_followers') }}</div>
                                    <div class="text-[15px] font-bold tabular-nums text-black dark:text-white mt-0.5">
                                        {{ number_format($ig['followers'] ?? 0) }}
                                    </div>
                                </div>
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">{{ __('social_media.stat_media_count') }}</div>
                                    <div class="text-[15px] font-bold tabular-nums text-[#E1306C] mt-0.5">
                                        {{ number_format($ig['media_count'] ?? 0) }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-3 text-center">
                                <a href="{{ route('social-media.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                                    <span>{{ __('social_media.channel_connect_cta') }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TikTok Creator Card --}}
                @php
                    $tt = $channelInsights['tiktok'] ?? [];
                    $isTtConnected = !empty($tt['connected']);
                    $ttAccount = $tt['account'] ?? null;
                    $ttMetrics = $tt['metrics'] ?? [];
                @endphp
                <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 space-y-3.5 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-[10px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold text-[14px]">
                                    <i data-lucide="video" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-bold text-black dark:text-white leading-none">TikTok</h3>
                                    <span class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $isTtConnected ? ($ttAccount->username ?? $ttAccount->account_name ?? 'TikTok Account') : __('social_media.channel_status_not_connected') }}
                                    </span>
                                </div>
                            </div>
                            @if($isTtConnected)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    <span>{{ __('social_media.channel_status_connected') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ __('social_media.channel_status_not_connected') }}
                                </span>
                            @endif
                        </div>

                        @if($isTtConnected)
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">Posting API</div>
                                    <div class="text-[12.5px] font-bold text-[#34C759] mt-0.5">Siap Terbit</div>
                                </div>
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">Privasi</div>
                                    <div class="text-[12.5px] font-bold text-black dark:text-white mt-0.5">Publik</div>
                                </div>
                            </div>
                        @else
                            <div class="py-3 text-center">
                                <a href="{{ route('social-media.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                                    <span>{{ __('social_media.channel_connect_cta') }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- LinkedIn Professional Card --}}
                @php
                    $li = $channelInsights['linkedin'] ?? [];
                    $isLiConnected = !empty($li['connected']);
                    $liAccount = $li['account'] ?? null;
                    $liMetrics = $li['metrics'] ?? [];
                @endphp
                <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 space-y-3.5 flex flex-col justify-between">
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-[10px] bg-[#0A66C2]/10 text-[#0A66C2] flex items-center justify-center font-bold text-[14px]">
                                    <i data-lucide="linkedin" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-bold text-black dark:text-white leading-none">LinkedIn</h3>
                                    <span class="text-[11px] text-black/45 dark:text-white/45">
                                        {{ $isLiConnected ? ($liAccount->account_name ?? 'LinkedIn Member') : __('social_media.channel_status_not_connected') }}
                                    </span>
                                </div>
                            </div>
                            @if($isLiConnected)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    <span>{{ __('social_media.channel_status_connected') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50">
                                    {{ __('social_media.channel_status_not_connected') }}
                                </span>
                            @endif
                        </div>

                        @if($isLiConnected)
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">Share API</div>
                                    <div class="text-[12.5px] font-bold text-[#34C759] mt-0.5">Aktif</div>
                                </div>
                                <div class="p-2 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-center">
                                    <div class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase">Profil</div>
                                    <div class="text-[12.5px] font-bold text-[#0A66C2] mt-0.5">Verified</div>
                                </div>
                            </div>
                        @else
                            <div class="py-3 text-center">
                                <a href="{{ route('social-media.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                                    <span>{{ __('social_media.channel_connect_cta') }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. POST PERFORMANCE TABLE --}}
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.recent_insights_title') }}</h2>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">{{ __('social_media.recent_insights_subtitle') }}</p>
                </div>
            </div>

            @if($posts->isEmpty())
                <div class="py-10 px-4 text-center space-y-4 max-w-lg mx-auto">
                    <div class="w-14 h-14 rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                        <i data-lucide="sparkles" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.no_insights_title') }}</h3>
                        <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                            {{ __('social_media.no_insights_desc') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('social-media.posts.index') }}"
                            class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#007AFF]/90 active:scale-[0.98] transition-all inline-flex items-center gap-2 shadow-sm">
                            <i data-lucide="pen-tool" class="w-4 h-4"></i>
                            <span>{{ __('social_media.write_post_now_btn') }}</span>
                        </a>
                    </div>
                </div>
            @else
                {{-- Desktop View: Table Layout --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 text-[11.5px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                <th class="pb-3 pl-2">{{ __('social_media.th_post') }}</th>
                                <th class="pb-3 px-3">{{ __('social_media.th_channel') }}</th>
                                <th class="pb-3 px-3 text-right">{{ __('social_media.th_impressions') }}</th>
                                <th class="pb-3 px-3 text-right">{{ __('social_media.th_reach') }}</th>
                                <th class="pb-3 px-3 text-right">{{ __('social_media.th_likes') }}</th>
                                <th class="pb-3 px-3 text-right">{{ __('social_media.th_comments') }}</th>
                                <th class="pb-3 px-3 text-right">{{ __('social_media.th_shares') }}</th>
                                <th class="pb-3 pr-2 text-right">{{ __('social_media.th_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[13px]">
                            @foreach($posts as $post)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3.5 pl-2 max-w-xs sm:max-w-md">
                                        <div class="font-medium text-black dark:text-white line-clamp-2">
                                            {{ $post->content }}
                                        </div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                            {{ $post->published_at ? $post->published_at->format('d M Y, H:i') : $post->created_at->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold
                                            {{ $post->platform === 'facebook' ? 'bg-[#1877F2]/10 text-[#1877F2]' : ($post->platform === 'instagram' ? 'bg-[#E1306C]/10 text-[#E1306C]' : ($post->platform === 'linkedin' ? 'bg-[#0A66C2]/10 text-[#0A66C2]' : 'bg-black/10 dark:bg-white/10 text-black dark:text-white')) }}">
                                            <span>{{ ucfirst($post->platform) }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-impressions-{{ $post->id }}">
                                        {{ number_format($post->getMetric('impressions')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-reach-{{ $post->id }}">
                                        {{ number_format($post->getMetric('reach')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-likes-{{ $post->id }}">
                                        {{ number_format($post->getMetric('likes')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-comments-{{ $post->id }}">
                                        {{ number_format($post->getMetric('comments')) }}
                                    </td>
                                    <td class="py-3.5 px-3 text-right font-semibold tabular-nums text-black dark:text-white" id="metric-shares-{{ $post->id }}">
                                        {{ number_format($post->getMetric('shares')) }}
                                    </td>
                                    <td class="py-3.5 pr-2 text-right whitespace-nowrap">
                                        <button id="sync-btn-{{ $post->id }}" @click="syncPostInsights('{{ $post->id }}')"
                                            class="min-h-[32px] px-3 rounded-[8px] text-[11.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.97] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="icon-sync-{{ $post->id }}"></i>
                                            <span>{{ __('social_media.fetch_live_btn') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile View: Stacked Cards (Apple HIG Standard) --}}
                <div class="md:hidden space-y-3">
                    @foreach($posts as $post)
                        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold
                                    {{ $post->platform === 'facebook' ? 'bg-[#1877F2]/10 text-[#1877F2]' : ($post->platform === 'instagram' ? 'bg-[#E1306C]/10 text-[#E1306C]' : ($post->platform === 'linkedin' ? 'bg-[#0A66C2]/10 text-[#0A66C2]' : 'bg-black/10 dark:bg-white/10 text-black dark:text-white')) }}">
                                    <span>{{ ucfirst($post->platform) }}</span>
                                </span>
                                <span class="text-[11px] text-black/45 dark:text-white/45">
                                    {{ $post->published_at ? $post->published_at->format('d M Y, H:i') : $post->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <p class="text-[13px] font-medium text-black dark:text-white line-clamp-2">
                                {{ $post->content }}
                            </p>

                            <div class="grid grid-cols-4 gap-2 pt-2 border-t border-black/5 dark:border-white/5 text-center">
                                <div class="p-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                    <div class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Imp</div>
                                    <div class="text-[12.5px] font-bold tabular-nums text-black dark:text-white" id="mobile-metric-impressions-{{ $post->id }}">
                                        {{ number_format($post->getMetric('impressions')) }}
                                    </div>
                                </div>
                                <div class="p-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                    <div class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Reach</div>
                                    <div class="text-[12.5px] font-bold tabular-nums text-black dark:text-white" id="mobile-metric-reach-{{ $post->id }}">
                                        {{ number_format($post->getMetric('reach')) }}
                                    </div>
                                </div>
                                <div class="p-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                    <div class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Likes</div>
                                    <div class="text-[12.5px] font-bold tabular-nums text-[#FF2D55]" id="mobile-metric-likes-{{ $post->id }}">
                                        {{ number_format($post->getMetric('likes')) }}
                                    </div>
                                </div>
                                <div class="p-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                                    <div class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Comments</div>
                                    <div class="text-[12.5px] font-bold tabular-nums text-[#007AFF]" id="mobile-metric-comments-{{ $post->id }}">
                                        {{ number_format($post->getMetric('comments')) }}
                                    </div>
                                </div>
                            </div>

                            <button id="mobile-sync-btn-{{ $post->id }}" @click="syncPostInsights('{{ $post->id }}')"
                                class="w-full min-h-[44px] rounded-[12px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="mobile-icon-sync-{{ $post->id }}"></i>
                                <span>{{ __('social_media.fetch_live_btn') }}</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <script>
        function socialInsightsManager() {
            return {
                isRefreshingAll: false,
                isSyncingAccounts: false,
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
                            // Reload to reflect all updated account metrics smoothly
                            setTimeout(() => window.location.reload(), 600);
                        } else {
                            if (window.AppAlert) {
                                AppAlert.error(data.error || '{{ __("social_media.error_sync_data") }}');
                            }
                        }
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error('{{ __("social_media.error_sync_data") }}');
                        }
                    } finally {
                        this.isSyncingAccounts = false;
                    }
                },
                async refreshAllInsights() {
                    this.isRefreshingAll = true;
                    try {
                        // 1. Sync all account metrics
                        await fetch('{{ route("social-media.insights.accounts.sync") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                        });

                        // 2. Sync each individual post
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
                            AppAlert.error(@js(__('social_media.error_refresh_metrics_partial')));
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
                            const errorMsg = data.error || @js(__('social_media.error_refresh_metrics'));
                            if (window.AppAlert) {
                                AppAlert.error(errorMsg);
                            }
                        }
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error(@js(__('social_media.error_sync_data')));
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

