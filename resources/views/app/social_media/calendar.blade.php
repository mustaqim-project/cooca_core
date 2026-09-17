@extends('layouts.app', [
    'title' => 'Kalender Konten Media Sosial - ' . $business->name,
    'headerTitle' => 'Kalender Konten',
    'headerSubtitle' => 'Visualisasikan jadwal publikasi konten multi-saluran toko Anda',
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

    <div class="max-w-[1360px] mx-auto space-y-6 pb-12">

        {{-- 0. BREADCRUMB --}}
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <a href="{{ route('social-media.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Media Sosial</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Kalender Konten</span>
        </nav>

        {{-- 1. PAGE HEADER --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-sm">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF]">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Visual Planner</span>
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        <span>{{ $posts->count() }} Postingan Terjadwal Bulan Ini</span>
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Kalender Jadwal Konten
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pantau alur kampanye postingan Anda di Facebook, Instagram, Threads, dan TikTok dalam satu kalender terpadu.
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full lg:w-auto">
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Tulis Postingan Baru</span>
                </a>
            </div>
        </header>

        {{-- 2. MODULE NAVIGATION SUB-TABS --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-sm">
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('social-media.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="link" class="w-4 h-4"></i>
                    <span>Koneksi Akun</span>
                </a>
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="image" class="w-4 h-4"></i>
                    <span>Posting Konten</span>
                </a>
                <a href="{{ route('social-media.calendar') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Kalender Konten</span>
                </a>
                <a href="{{ route('social-media.inbox.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Kotak Masuk &amp; Komentar</span>
                </a>
                <a href="{{ route('social-media.insights.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Insight &amp; Analitik</span>
                </a>
            </div>
        </div>

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
                        Jadwal tayang kampanye konten toko
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('social-media.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                    class="h-8 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    <span>Bulan Lalu</span>
                </a>

                <a href="{{ route('social-media.calendar', ['month' => now()->month, 'year' => now()->year]) }}"
                    class="h-8 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                    <span>Hari Ini</span>
                </a>

                <a href="{{ route('social-media.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                    class="h-8 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[12px] font-medium flex items-center gap-1.5 transition-colors">
                    <span>Bulan Depan</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        {{-- 4. CALENDAR GRID --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
            {{-- Day Header (Mon - Sun) --}}
            <div class="grid grid-cols-7 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-center text-[12px] font-semibold text-black/60 dark:text-white/60">
                <div class="py-2.5">Senin</div>
                <div class="py-2.5">Selasa</div>
                <div class="py-2.5">Rabu</div>
                <div class="py-2.5">Kamis</div>
                <div class="py-2.5">Jumat</div>
                <div class="py-2.5 text-[#007AFF]">Sabtu</div>
                <div class="py-2.5 text-[#FF3B30]">Minggu</div>
            </div>

            {{-- Grid Cells --}}
            <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-black/5 dark:divide-white/10">
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
                                    {{ count($dayPosts) }} post
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
                                                        <span class="w-3.5 h-3.5 rounded-full bg-black text-white inline-flex items-center justify-center text-[8px] font-black" title="TikTok">T</span>
                                                    @elseif ($target->channel === 'facebook')
                                                        <i data-lucide="facebook" class="w-3.5 h-3.5 text-[#1877F2]"></i>
                                                    @elseif ($target->channel === 'instagram')
                                                        <i data-lucide="instagram" class="w-3.5 h-3.5 text-[#E4405F]"></i>
                                                    @elseif ($target->channel === 'threads')
                                                        <i data-lucide="at-sign" class="w-3.5 h-3.5 text-black dark:text-white"></i>
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

    </div>
@endsection
