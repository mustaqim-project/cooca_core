@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerTitle' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerSubtitle' => $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features'),
])

@section('content')
@php
    $canSeeDashboard = \App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view');
    $homeRoute = $canSeeDashboard ? route('dashboard') : route('portal');
    $homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';
    $currentLabel = $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features');
    $breadcrumbs = [
        ['label' => $homeLabel, 'url' => $homeRoute],
        ['label' => __('feedback.product_support'), 'url' => route('feedback.bugs.index')],
        ['label' => $currentLabel, 'url' => null],
    ];
@endphp

<div class="space-y-6 pb-16">
    <x-module-header
        module="feedback"
        :title="$type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features')"
        :subtitle="$type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features')"
        :breadcrumbs="$breadcrumbs">
        <a href="{{ route($type === 'bugs' ? 'feedback.bugs.create' : 'feedback.features.create') }}"
            class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.97] transition flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
            <i data-lucide="{{ $type === 'bugs' ? 'bug' : 'sparkles' }}" class="w-4 h-4"></i>
            <span>{{ $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature') }}</span>
        </a>
    </x-module-header>

    <x-module-tabs module="feedback" />

    <!-- Feedback List Grid Bento -->
    @if ($items->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($items as $item)
                @php
                    $statusStyles = [
                        'open' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                        'in_progress' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                        'resolved' => 'bg-[#34C759]/10 text-[#34C759] border-[#34C759]/20',
                        'closed' => 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 border-black/10',
                        'submitted' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                        'under_review' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                        'planned' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
                        'completed' => 'bg-[#34C759]/10 text-[#34C759] border-[#34C759]/20',
                        'rejected' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                    ];
                    $badgeClass = $statusStyles[$item->status] ?? 'bg-black/5 text-black/60 border-black/10';
                @endphp
                <a href="{{ route($type === 'bugs' ? 'feedback.bugs.show' : 'feedback.features.show', $item) }}"
                    class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4 transition hover:-translate-y-0.5 hover:border-[#007AFF]/40 flex flex-col justify-between group">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                {{ __('feedback.status_' . $item->status) !== 'feedback.status_' . $item->status ? __('feedback.status_' . $item->status) : str_replace('_', ' ', ucfirst($item->status)) }}
                            </span>
                            <span class="text-[11px] text-black/40 dark:text-white/40 font-mono">
                                {{ $item->created_at->format('d M Y') }}
                            </span>
                        </div>

                        <h3 class="font-bold text-[15px] text-black dark:text-white line-clamp-2 group-hover:text-[#007AFF] transition-colors">
                            {{ $item->title }}
                        </h3>

                        <p class="text-[12px] text-black/60 dark:text-white/60 line-clamp-3 leading-relaxed">
                            {{ $item->description }}
                        </p>
                    </div>

                    <div class="pt-3 border-t border-black/[0.05] dark:border-white/[0.06] space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-semibold">
                            <span class="text-black/50 dark:text-white/50">{{ __('feedback.progress_work') }}</span>
                            <span class="font-mono text-[#007AFF]">{{ $item->progress_percent }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-black/[0.06] dark:bg-white/[0.08] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#34C759] rounded-full transition-all duration-500"
                                style="width: {{ $item->progress_percent }}%"></div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($items->hasPages())
            <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08]">
                {{ $items->links() }}
            </div>
        @endif
    @else
        <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] py-16 px-6 text-center space-y-4">
            <div class="w-14 h-14 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                <i data-lucide="{{ $type === 'bugs' ? 'bug' : 'sparkles' }}" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1 max-w-sm mx-auto">
                <h4 class="text-[15px] font-bold text-black dark:text-white">
                    {{ $type === 'bugs' ? __('feedback.empty_bugs') : __('feedback.empty_features') }}
                </h4>
                <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed">
                    {{ $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features') }}
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route($type === 'bugs' ? 'feedback.bugs.create' : 'feedback.features.create') }}"
                    class="inline-flex items-center gap-2 h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature') }}</span>
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
