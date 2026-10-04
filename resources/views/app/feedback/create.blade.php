@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
    'headerTitle' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
    'headerSubtitle' => $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features'),
])

@section('content')
@php
    $canSeeDashboard = \App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view');
    $homeRoute = $canSeeDashboard ? route('dashboard') : route('portal');
    $homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';
    $parentRoute = route($type === 'bugs' ? 'feedback.bugs.index' : 'feedback.features.index');
    $parentLabel = $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features');
    $currentLabel = $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature');

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
        :title="$type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature')"
        :subtitle="$type === 'bugs' ? __('feedback.subtitle_bugs_detail') : __('feedback.subtitle_features_detail')"
        :breadcrumbs="$breadcrumbs">
        <a href="{{ route($type === 'bugs' ? 'feedback.bugs.index' : 'feedback.features.index') }}"
            class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-[13px] font-semibold transition-all flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>{{ __('feedback.back_to_list') }}</span>
        </a>
    </x-module-header>

    <x-module-tabs module="feedback" />

    @if ($errors->any())
        <div class="max-w-4xl mx-auto rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 text-[13px] text-[#FF3B30] space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="max-w-4xl mx-auto p-6 sm:p-8 rounded-[24px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_16px_rgba(0,0,0,0.03)] space-y-6">
        <div class="flex items-center gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                <i data-lucide="{{ $type === 'bugs' ? 'bug' : 'sparkles' }}" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-black dark:text-white">
                    {{ $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature') }}
                </h2>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    {{ $type === 'bugs' ? __('feedback.subtitle_bugs_detail') : __('feedback.subtitle_features_detail') }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route($type === 'bugs' ? 'feedback.bugs.store' : 'feedback.features.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Title -->
            <div class="space-y-1.5">
                <label class="block text-[13px] font-bold text-black dark:text-white">
                    {{ __('feedback.title_field') }} <span class="text-[#FF3B30]">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="180"
                    placeholder="{{ __('feedback.title_placeholder') }}"
                    class="w-full h-12 px-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                @error('title')
                    <p class="text-[11px] text-[#FF3B30] font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Severity/Priority -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-black dark:text-white">
                        {{ __('feedback.category_field') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="category" required
                        class="w-full h-12 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $cat)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-black dark:text-white">
                        {{ $type === 'bugs' ? __('feedback.severity_field') : __('feedback.priority_field') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="{{ $type === 'bugs' ? 'severity' : 'priority' }}" required
                        class="w-full h-12 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                        @foreach($type === 'bugs' ? ['low','normal','high','critical'] : ['low','normal','high'] as $level)
                            <option value="{{ $level }}" {{ old($type === 'bugs' ? 'severity' : 'priority') === $level ? 'selected' : '' }}>
                                {{ ucfirst($level) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <label class="block text-[13px] font-bold text-black dark:text-white">
                    {{ __('feedback.description_field') }} <span class="text-[#FF3B30]">*</span>
                </label>
                <textarea name="description" required rows="4"
                    placeholder="{{ __('feedback.description_placeholder') }}"
                    class="w-full p-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-[11px] text-[#FF3B30] font-semibold">{{ $message }}</p>
                @enderror
            </div>

            @if($type === 'bugs')
                <!-- Steps to reproduce -->
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-black dark:text-white">
                        {{ __('feedback.steps_reproduce') }}
                    </label>
                    <textarea name="steps_to_reproduce" rows="3"
                        placeholder="{{ __('feedback.steps_placeholder') }}"
                        class="w-full p-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">{{ old('steps_to_reproduce') }}</textarea>
                </div>

                <!-- Expected & Actual -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80">
                            {{ __('feedback.expected_result') }}
                        </label>
                        <textarea name="expected_behavior" rows="3"
                            class="w-full p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">{{ old('expected_behavior') }}</textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80">
                            {{ __('feedback.actual_result') }}
                        </label>
                        <textarea name="actual_behavior" rows="3"
                            class="w-full p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">{{ old('actual_behavior') }}</textarea>
                    </div>
                </div>

                <!-- Environment & Attachment -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80">
                            {{ __('feedback.environment_field') }}
                        </label>
                        <input type="text" name="environment" value="{{ old('environment') }}"
                            placeholder="{{ __('feedback.environment_placeholder') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-hidden">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80">
                            {{ __('feedback.attachment_field') }}
                        </label>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf,.txt,.log"
                            class="w-full h-11 px-3 py-1.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded-[8px] file:border-0 file:text-[11px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] cursor-pointer">
                    </div>
                </div>
            @else
                <!-- Business Value -->
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-black dark:text-white">
                        {{ __('feedback.business_value') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <textarea name="business_value" required rows="3"
                        placeholder="{{ __('feedback.business_value_placeholder') }}"
                        class="w-full p-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">{{ old('business_value') }}</textarea>
                </div>

                <!-- Use Case -->
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-black dark:text-white">
                        {{ __('feedback.use_case') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <textarea name="use_case" required rows="3"
                        placeholder="{{ __('feedback.use_case_placeholder') }}"
                        class="w-full p-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">{{ old('use_case') }}</textarea>
                </div>

                <!-- Proposed Solution -->
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80">
                        {{ __('feedback.proposed_solution') }}
                    </label>
                    <textarea name="proposed_solution" rows="3"
                        class="w-full p-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">{{ old('proposed_solution') }}</textarea>
                </div>
            @endif

            <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3">
                <a href="{{ route($type === 'bugs' ? 'feedback.bugs.index' : 'feedback.features.index') }}"
                    class="h-11 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:text-black dark:hover:text-white transition flex items-center justify-center cursor-pointer">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit"
                    class="h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] shadow-[0_2px_8px_rgba(0,122,255,0.3)] active:scale-[0.98] transition cursor-pointer flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>{{ $type === 'bugs' ? __('feedback.submit_bug') : __('feedback.submit_feature') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
