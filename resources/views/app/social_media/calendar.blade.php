@extends('layouts.app', [
    'title' => __('social_media.calendar_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.calendar_header_title'),
    'headerSubtitle' => __('social_media.calendar_header_subtitle'),
])

@section('content')
    @php
        $monthName = $startDate->translatedFormat('F Y');
        $prevMonth = $startDate->copy()->subMonth();
        $nextMonth = $startDate->copy()->addMonth();

        // Calendar grid calculations (starting on Monday)
        $startDayOfWeek = ($startDate->dayOfWeekIso - 1); // 0 for Mon, 6 for Sun
        $daysInMonth = $startDate->daysInMonth;
        $today = now()->format('Y-m-d');

        // Group posts by day: 'YYYY-MM-DD'
        $postsByDate = [];
        foreach ($posts as $p) {
            if ($p->scheduled_at) {
                $d = $p->scheduled_at->format('Y-m-d');
                $postsByDate[$d][] = $p;
            }
        }
    @endphp

    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            title="{{ __('social_media.calendar_title') }}"
            subtitle="{{ __('social_media.calendar_subtitle') }}">
            <x-slot:actions>
                <a href="{{ route('social-media.posts.index') }}"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>{{ __('social_media.create_post_btn') }}</span>
                </a>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- 3. CALENDAR CONTROLS & NAVIGATION --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF]">
                    <i data-lucide="calendar-days" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-[17px] font-bold text-black dark:text-white capitalize">
                        {{ $monthName }}
                    </h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        {{ __('social_media.calendar_sub_info') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('social-media.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-8 px-3.5 sm:px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    <span>{{ __('social_media.prev_month') }}</span>
                </a>

                <a href="{{ route('social-media.calendar', ['month' => now()->month, 'year' => now()->year]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-8 px-3.5 sm:px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                    <span>{{ __('social_media.this_month') }}</span>
                </a>

                <a href="{{ route('social-media.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-8 px-3.5 sm:px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                    <span>{{ __('social_media.next_month') }}</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        {{-- 4A. DESKTOP/TABLET CALENDAR GRID (Hidden on Mobile) --}}
        <div class="hidden sm:block rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
            {{-- Day Header (Mon - Sun) --}}
            <div class="grid grid-cols-7 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-center text-[12px] font-semibold text-black/60 dark:text-white/60">
                <div class="py-2.5">{{ __('social_media.day_mon') }}</div>
                <div class="py-2.5">{{ __('social_media.day_tue') }}</div>
                <div class="py-2.5">{{ __('social_media.day_wed') }}</div>
                <div class="py-2.5">{{ __('social_media.day_thu') }}</div>
                <div class="py-2.5">{{ __('social_media.day_fri') }}</div>
                <div class="py-2.5 text-[#007AFF]">{{ __('social_media.day_sat') }}</div>
                <div class="py-2.5 text-[#FF3B30]">{{ __('social_media.day_sun') }}</div>
            </div>

            {{-- Grid Cells --}}
            <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-black/5 dark:border-white/10">
                {{-- Leading empty cells --}}
                @for ($i = 0; $i < $startDayOfWeek; $i++)
                    <div class="min-h-[110px] sm:min-h-[130px] p-2 bg-black/[0.01] dark:bg-white/[0.01] text-black/20 dark:text-white/20">
                    </div>
                @endfor

                {{-- Month Days --}}
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $isCurrentDay = ($currentDate === $today);
                        $dayPosts = $postsByDate[$currentDate] ?? [];
                    @endphp
                    <div class="min-h-[110px] sm:min-h-[130px] p-2 flex flex-col justify-between transition-colors hover:bg-black/[0.01] dark:hover:bg-white/[0.02] {{ $isCurrentDay ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.06]' : '' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[12px] font-bold {{ $isCurrentDay ? 'bg-[#007AFF] text-white' : 'text-black/80 dark:text-white/80' }}">
                                {{ $day }}
                            </span>
                            @if (count($dayPosts) > 0)
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                    {{ __('social_media.post_unit_badge', ['count' => count($dayPosts)]) }}
                                </span>
                            @endif
                        </div>

                        {{-- Post Cards on this Date --}}
                        <div class="space-y-1.5 overflow-y-auto max-h-[110px] pr-0.5 custom-scrollbar">
                            @foreach ($dayPosts as $post)
                                @php
                                    $time = $post->scheduled_at ? $post->scheduled_at->format('H:i') : '';
                                    $statusColor = match($post->status) {
                                        'published' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20',
                                        'failed' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                                        'publishing' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                                        default => 'bg-[#FF9500]/10 text-[#D97706] dark:text-[#FF9F0A] border-[#FF9500]/20'
                                    };
                                @endphp
                                <a href="{{ route('social-media.posts.index') }}"
                                    class="block p-1.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border {{ $statusColor }} shadow-2xs hover:shadow-xs transition-shadow text-left group">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <div class="flex items-center gap-1">
                                            @if ($post->targets->count() > 0)
                                                @foreach ($post->targets as $target)
                                                    @if ($target->channel === 'tiktok')
                                                        <span class="w-3.5 h-3.5 rounded-full bg-black text-white inline-flex items-center justify-center p-0.5" title="TikTok">
                                                            <x-social-icon platform="tiktok" class="w-2.5 h-2.5" />
                                                        </span>
                                                    @elseif ($target->channel === 'facebook')
                                                        <span class="text-[#1877F2]" title="Facebook"><x-social-icon platform="facebook" class="w-3.5 h-3.5" /></span>
                                                    @elseif ($target->channel === 'instagram')
                                                        <span class="text-[#E4405F]" title="Instagram"><x-social-icon platform="instagram" class="w-3.5 h-3.5" /></span>
                                                    @elseif ($target->channel === 'threads')
                                                        <span class="text-black dark:text-white" title="Threads"><x-social-icon platform="threads" class="w-3.5 h-3.5" /></span>
                                                    @elseif ($target->channel === 'linkedin')
                                                        <span class="text-[#0A66C2]" title="LinkedIn"><x-social-icon platform="linkedin" class="w-3.5 h-3.5" /></span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <i data-lucide="share-2" class="w-3.5 h-3.5 text-black/50"></i>
                                            @endif
                                        </div>
                                        <span class="text-[10px] font-bold opacity-75">{{ $time }}</span>
                                    </div>
                                    <p class="text-[11px] font-medium text-black/80 dark:text-white/80 line-clamp-1 group-hover:text-[#007AFF] transition-colors">
                                        {{ Str::limit($post->content, 35) }}
                                    </p>
                                </a>
                            @endforeach
                        </div>

                        {{-- Empty placeholder spacer --}}
                        @if (empty($dayPosts))
                            <div class="h-4"></div>
                        @endif
                    </div>
                @endfor

                {{-- Trailing empty cells --}}
                @php
                    $totalCells = $startDayOfWeek + $daysInMonth;
                    $trailingCells = (7 - ($totalCells % 7)) % 7;
                @endphp
                @for ($i = 0; $i < $trailingCells; $i++)
                    <div class="min-h-[110px] sm:min-h-[130px] p-2 bg-black/[0.01] dark:bg-white/[0.01] text-black/20 dark:text-white/20">
                    </div>
                @endfor
            </div>
        </div>

        {{-- 4B. MOBILE-FIRST AGENDA VIEW (Visible only on Mobile Screens < 640px) --}}
        <div class="sm:hidden space-y-3">
            <div class="flex items-center justify-between px-1">
                <div class="text-[12px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    {{ __('social_media.mobile_agenda_title') }}
                </div>
                <div class="text-[11px] font-semibold text-[#007AFF]">
                    {{ $monthName }}
                </div>
            </div>

            @php $hasScheduledPosts = false; @endphp
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $isCurrentDay = ($currentDate === $today);
                    $dayPosts = $postsByDate[$currentDate] ?? [];
                @endphp
                @if (!empty($dayPosts))
                    @php $hasScheduledPosts = true; @endphp
                    <div class="p-3.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-3 {{ $isCurrentDay ? 'ring-1 ring-[#007AFF]/50' : '' }}">
                        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[12px] font-bold {{ $isCurrentDay ? 'bg-[#007AFF] text-white' : 'bg-black/5 dark:bg-white/10 text-black dark:text-white' }}">
                                    {{ $day }}
                                </span>
                                <span class="text-[13px] font-bold text-black dark:text-white">
                                    {{ \Carbon\Carbon::parse($currentDate)->translatedFormat('l, d F Y') }}
                                </span>
                            </div>
                            <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                {{ __('social_media.post_unit_badge', ['count' => count($dayPosts)]) }}
                            </span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($dayPosts as $post)
                                @php
                                    $time = $post->scheduled_at ? $post->scheduled_at->format('H:i') : '';
                                    $statusColor = match($post->status) {
                                        'published' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20',
                                        'failed' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                                        'publishing' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                                        default => 'bg-[#FF9500]/10 text-[#D97706] dark:text-[#FF9F0A] border-[#FF9500]/20'
                                    };
                                    $statusLabel = match($post->status) {
                                        'published' => __('social_media.filter_status_published'),
                                        'failed' => __('social_media.filter_status_failed'),
                                        'publishing' => __('social_media.status_publishing'),
                                        default => __('social_media.filter_status_scheduled')
                                    };
                                @endphp
                                <a href="{{ route('social-media.posts.index') }}"
                                    class="min-h-[44px] p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3 hover:bg-black/[0.04] transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="flex items-center gap-1 shrink-0">
                                            @if ($post->targets->count() > 0)
                                                @foreach ($post->targets as $target)
                                                    @if ($target->channel === 'tiktok')
                                                        <span class="w-4 h-4 rounded-full bg-black text-white inline-flex items-center justify-center p-0.5" title="TikTok">
                                                            <x-social-icon platform="tiktok" class="w-3 h-3" />
                                                        </span>
                                                    @elseif ($target->channel === 'facebook')
                                                        <span class="text-[#1877F2]" title="Facebook"><x-social-icon platform="facebook" class="w-4 h-4" /></span>
                                                    @elseif ($target->channel === 'instagram')
                                                        <span class="text-[#E4405F]" title="Instagram"><x-social-icon platform="instagram" class="w-4 h-4" /></span>
                                                    @elseif ($target->channel === 'threads')
                                                        <span class="text-black dark:text-white" title="Threads"><x-social-icon platform="threads" class="w-4 h-4" /></span>
                                                    @elseif ($target->channel === 'linkedin')
                                                        <span class="text-[#0A66C2]" title="LinkedIn"><x-social-icon platform="linkedin" class="w-4 h-4" /></span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <i data-lucide="share-2" class="w-4 h-4 text-black/50"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[12.5px] font-semibold text-black dark:text-white truncate">
                                                {{ Str::limit($post->content, 45) }}
                                            </p>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-2 mt-0.5">
                                                <span>{{ $time }} WIB</span>
                                                <span>•</span>
                                                <span class="font-medium capitalize">{{ $post->media_type ?: 'text' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold border shrink-0 {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endfor

            @if (!$hasScheduledPosts)
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-8 text-center shadow-sm space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                        <i data-lucide="calendar-x" class="w-6 h-6"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-[14px] font-bold text-black dark:text-white">
                            {{ __('social_media.no_scheduled_posts_this_month') }}
                        </h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50">
                            {{ __('social_media.calendar_subtitle') }}
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('social-media.posts.index') }}"
                            class="min-h-[44px] px-4 rounded-[10px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] inline-flex items-center justify-center gap-1.5 w-full shadow-sm">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>{{ __('social_media.create_schedule_btn') }}</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
