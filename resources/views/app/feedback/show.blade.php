@extends('layouts.app', [
    'title' => $item->title,
    'headerTitle' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerSubtitle' => $item->title,
])

@section('content')
@php
    $canSeeDashboard = \App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view');
    $homeRoute = $canSeeDashboard ? route('dashboard') : route('portal');
    $homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';
    $parentRoute = route($type === 'bugs' ? 'feedback.bugs.index' : 'feedback.features.index');
    $parentLabel = $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features');
    $currentLabel = '#' . $item->id . ' - ' . \Illuminate\Support\Str::limit($item->title, 25);

    $breadcrumbs = [
        ['label' => $homeLabel, 'url' => $homeRoute],
        ['label' => __('feedback.product_support'), 'url' => route('feedback.bugs.index')],
        ['label' => $parentLabel, 'url' => $parentRoute],
        ['label' => $currentLabel, 'url' => null],
    ];
@endphp

<div class="space-y-6 pb-16">
    <x-module-header
        module="feedback"
        :title="$item->title"
        :subtitle="($type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features')) . ' #' . $item->id . ' • ' . $item->created_at->format('d M Y H:i')"
        :breadcrumbs="$breadcrumbs">
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
            <a href="{{ route($type === 'bugs' ? 'feedback.bugs.index' : 'feedback.features.index') }}"
                class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[13px] font-semibold transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('feedback.back_to_list') }}</span>
            </a>
            <a href="{{ route($type === 'bugs' ? 'feedback.bugs.create' : 'feedback.features.create') }}"
                class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.97] transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature') }}</span>
            </a>
        </div>
    </x-module-header>

    <x-module-tabs module="feedback" />

    @if (session('success'))
        <div class="max-w-5xl mx-auto p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-2.5 text-[#34C759] text-[13px] font-semibold">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Detail Bento Card -->
    <div class="max-w-5xl mx-auto p-6 sm:p-8 rounded-[24px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_16px_rgba(0,0,0,0.03)] space-y-6">
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

        <!-- Header Row -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-6">
            <div class="space-y-1.5 flex-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                        {{ __('feedback.status_' . $item->status) !== 'feedback.status_' . $item->status ? __('feedback.status_' . $item->status) : str_replace('_', ' ', ucfirst($item->status)) }}
                    </span>
                    <span class="text-[11px] text-black/40 dark:text-white/40 font-mono">
                        {{ $item->created_at->format('d M Y H:i') }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white leading-snug">
                    {{ $item->title }}
                </h1>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    {{ __('feedback.category_label') }}: <strong class="text-black dark:text-white">{{ ucfirst(str_replace('_', ' ', $item->category)) }}</strong>
                    @if ($item->reporter ?? $item->requester)
                        • {{ __('feedback.reporter_label') }}: <strong class="text-black dark:text-white">{{ ($item->reporter ?? $item->requester)->name }}</strong>
                    @endif
                </p>
            </div>

            <!-- Progress Box -->
            <div class="sm:text-right space-y-1 p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] shrink-0 sm:min-w-[140px]">
                <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase">{{ __('feedback.progress_work') }}</div>
                <div class="text-2xl font-black font-mono text-[#007AFF] tabular-nums">
                    {{ $item->progress_percent }}%
                </div>
                <div class="w-full h-1.5 bg-black/[0.06] dark:bg-white/[0.08] rounded-full overflow-hidden mt-1">
                    <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#34C759] rounded-full transition-all duration-500"
                        style="width: {{ $item->progress_percent }}%"></div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
            <h3 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('feedback.description_field') }}</h3>
            <p class="text-[14px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">
                {{ $item->description }}
            </p>
        </div>

        @if($type === 'bugs')
            <!-- Bug Technical Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                @if($item->steps_to_reproduce)
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('feedback.steps_reproduce') }}</span>
                        <p class="text-[13px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">{{ $item->steps_to_reproduce }}</p>
                    </div>
                @endif

                @if($item->environment)
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('feedback.environment_field') }}</span>
                        <p class="text-[13px] text-black/80 dark:text-white/80">{{ $item->environment }}</p>
                    </div>
                @endif

                @if($item->expected_behavior)
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#34C759]">{{ __('feedback.expected_result') }}</span>
                        <p class="text-[13px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">{{ $item->expected_behavior }}</p>
                    </div>
                @endif

                @if($item->actual_behavior)
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#FF3B30]">{{ __('feedback.actual_result') }}</span>
                        <p class="text-[13px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">{{ $item->actual_behavior }}</p>
                    </div>
                @endif
            </div>

            <!-- Bug Attachment File Tile -->
            @if($item->attachment_path)
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="paperclip" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[13px] font-bold text-black dark:text-white">{{ __('feedback.attachment_label') }}</span>
                            <p class="text-[11px] text-black/40 dark:text-white/40 font-mono">{{ basename($item->attachment_path) }}</p>
                        </div>
                    </div>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->attachment_path) }}" target="_blank"
                        class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>{{ __('feedback.view_attachment') }}</span>
                    </a>
                </div>
            @endif
        @else
            <!-- Feature Request Detail Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('feedback.business_value') }}</span>
                    <p class="text-[13px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">{{ $item->business_value }}</p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('feedback.use_case') }}</span>
                    <p class="text-[13px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">{{ $item->use_case }}</p>
                </div>

                @if($item->proposed_solution)
                    <div class="md:col-span-2 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('feedback.proposed_solution') }}</span>
                        <p class="text-[13px] text-black/80 dark:text-white/80 whitespace-pre-line leading-relaxed">{{ $item->proposed_solution }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Stepper Progress Timeline -->
    <div class="max-w-5xl mx-auto p-6 sm:p-8 rounded-[24px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_16px_rgba(0,0,0,0.03)] space-y-5">
        <div class="flex items-center gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
            <i data-lucide="history" class="w-5 h-5 text-[#007AFF]"></i>
            <h2 class="text-base font-bold text-black dark:text-white">
                {{ __('feedback.progress_tracking') }}
            </h2>
        </div>

        <div class="space-y-4">
            @forelse($item->updates as $update)
                <div class="flex items-start gap-3.5 pl-2 relative before:absolute before:left-3.5 before:top-6 before:bottom-0 before:w-0.5 before:bg-black/[0.08] dark:before:bg-white/[0.1] last:before:hidden">
                    <div class="w-7 h-7 rounded-full bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0 z-10">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="space-y-1 flex-1 pb-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[13px] font-bold text-black dark:text-white">
                                {{ $update->status ? str_replace('_', ' ', ucfirst($update->status)) : __('feedback.update_work') }}
                            </span>
                            <span class="text-[11px] font-mono text-black/40 dark:text-white/40">
                                {{ $update->created_at->format('d M Y H:i') }}
                            </span>
                        </div>
                        <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                            {{ $update->comment ?: __('feedback.progress_updated', ['percent' => $update->progress_percent]) }}
                        </p>
                        @if($update->user || $update->admin)
                            <span class="text-[10px] text-black/40 dark:text-white/40">{{ __('feedback.by_label') }}: {{ ($update->user ?? $update->admin)->name }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-[13px] text-black/40 dark:text-white/40">
                    {{ __('feedback.no_updates_yet') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
