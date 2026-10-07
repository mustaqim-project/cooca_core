@extends('layouts.app', [
    'title' => __('social_media.posts_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.posts_header_title'),
    'headerSubtitle' => __('social_media.posts_header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-36 lg:pb-12" x-data="socialPostsManager()">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            :title="__('social_media.posts_title')"
            :subtitle="__('social_media.posts_subtitle')">
            <x-slot:actions>
                @if(isset($canSchedulePost) && !$canSchedulePost && empty($hasSocialAddon))
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('open-quota-modal', {
                            detail: {
                                title: @js(__('social_media.quota_modal_title')),
                                desc: @js(__('social_media.quota_modal_desc', ['limit' => $socialPostLimit ?? 3])),
                                used: {{ $postsUsedThisMonth ?? 3 }},
                                limit: {{ $socialPostLimit ?? 3 }},
                                unit: 'posting',
                                upgradeUrl: '{{ route('billing.checkout') }}',
                                upgradeFee: 'Rp 89.000/bln',
                                isAddon: true
                            }
                        }))"
                        class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-black/80 dark:bg-white/20 hover:bg-black active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm cursor-pointer">
                        <i data-lucide="lock" class="w-4 h-4 text-[#FF3B30]"></i>
                        <span>{{ __('social_media.create_post_quota_reached') }}</span>
                    </button>
                @else
                    <button @click="openComposerModal = true"
                        class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>{{ __('social_media.create_post_btn') }}</span>
                    </button>
                @endif
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- QUOTA LIMIT BANNER IF REACHED --}}
        @if(isset($canSchedulePost) && !$canSchedulePost && empty($hasSocialAddon))
            <div class="rounded-[16px] p-4 sm:p-5 bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 backdrop-blur-md">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[14px] font-bold text-slate-900 dark:text-white">
                            {{ __('social_media.quota_banner_title', ['used' => $postsUsedThisMonth ?? 3, 'limit' => $socialPostLimit ?? 3]) }}
                        </div>
                        <div class="text-[12px] text-slate-600 dark:text-slate-400 mt-0.5">
                            {{ __('social_media.quota_banner_desc') }}
                        </div>
                    </div>
                </div>
                <button type="button"
                    @click="window.dispatchEvent(new CustomEvent('open-quota-modal', {
                        detail: {
                            title: '{{ __('social_media.quota_banner_title', ['used' => $postsUsedThisMonth ?? 3, 'limit' => $socialPostLimit ?? 3]) }}',
                            desc: '{{ __('social_media.quota_exceeded', ['used' => $postsUsedThisMonth ?? 3, 'limit' => $socialPostLimit ?? 3]) }}',
                            used: {{ $postsUsedThisMonth ?? 3 }},
                            limit: {{ $socialPostLimit ?? 3 }},
                            unit: 'posting',
                            upgradeUrl: '{{ route('billing.checkout') }}',
                            upgradeFee: 'Rp 89.000/bln',
                            isAddon: true
                        }
                    }))"
                    class="min-h-[44px] sm:min-h-0 h-9 px-4 rounded-[10px] text-[12px] font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-sm transition whitespace-nowrap cursor-pointer shrink-0">
                    {{ __('social_media.activate_unlimited') }}
                </button>
            </div>
        @endif

        {{-- FLASH ALERTS --}}
        @if(session('success'))
            <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/25 p-4 flex items-center gap-3 text-[#248A3D] dark:text-[#30D158]">
                <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                <div class="text-[13px] font-semibold">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 p-4 flex items-center gap-3 text-[#FF3B30]">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <div class="text-[13px] font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        {{-- 3. FILTERS & SEARCH BAR --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider mr-1">{{ __('social_media.filter_status_label') }}:</span>
                <a href="{{ route('social-media.posts.index', ['status' => 'all', 'platform' => $platform]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-7 px-3.5 sm:px-3 rounded-full text-[12px] font-medium transition-colors flex items-center {{ $status === 'all' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.filter_status_all') }} ({{ $posts->total() }})
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'published', 'platform' => $platform]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-7 px-3.5 sm:px-3 rounded-full text-[12px] font-medium transition-colors flex items-center {{ $status === 'published' ? 'bg-[#34C759] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.filter_status_published') }}
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'scheduled', 'platform' => $platform]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-7 px-3.5 sm:px-3 rounded-full text-[12px] font-medium transition-colors flex items-center {{ $status === 'scheduled' ? 'bg-[#5856D6] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.filter_status_scheduled') }}
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'pending_review', 'platform' => $platform]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-7 px-3.5 sm:px-3 rounded-full text-[12px] font-medium transition-colors flex items-center {{ $status === 'pending_review' ? 'bg-[#FF9500] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.filter_status_pending_review') }}
                </a>
                <a href="{{ route('social-media.posts.index', ['status' => 'failed', 'platform' => $platform]) }}"
                    class="min-h-[38px] sm:min-h-0 sm:h-7 px-3.5 sm:px-3 rounded-full text-[12px] font-medium transition-colors flex items-center {{ $status === 'failed' ? 'bg-[#FF3B30] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.filter_status_failed') }}
                </a>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('social_media.filter_platform_label') }}:</span>
                <select onchange="window.location.href=this.value"
                    class="min-h-[44px] sm:min-h-0 sm:h-8 px-3 rounded-[10px] text-[12.5px] font-medium bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'all']) }}" {{ $platform === 'all' ? 'selected' : '' }}>{{ __('social_media.filter_platform_all') }}</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'facebook']) }}" {{ $platform === 'facebook' ? 'selected' : '' }}>Facebook</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'instagram']) }}" {{ $platform === 'instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'threads']) }}" {{ $platform === 'threads' ? 'selected' : '' }}>Threads</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'tiktok']) }}" {{ $platform === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                    <option value="{{ route('social-media.posts.index', ['status' => $status, 'platform' => 'linkedin']) }}" {{ $platform === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                </select>
            </div>
        </div>

        {{-- 4. POSTS LIST / FEED CARDS --}}
        @if($posts->isEmpty())
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-6 sm:p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-[20px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="inbox" class="w-8 h-8"></i>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.empty_posts_title') }}</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60">
                        {{ __('social_media.empty_posts_desc') }}
                    </p>
                </div>
                <div>
                    <button @click="openComposerModal = true"
                        class="min-h-[44px] sm:min-h-0 h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('social_media.write_post_now_btn') }}</span>
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($posts as $post)
                    <div id="post-card-{{ $post->id }}" class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            {{-- Header Card --}}
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[12px] font-bold shadow-sm
                                        {{ $post->platform === 'facebook' ? 'bg-[#1877F2]' : ($post->platform === 'instagram' ? 'bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF]' : ($post->platform === 'tiktok' ? 'bg-black dark:bg-white dark:text-black' : ($post->platform === 'linkedin' ? 'bg-[#0A66C2]' : 'bg-black/10 text-black dark:text-white'))) }}">
                                        <x-social-icon :platform="$post->platform" class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                            {{ $post->account ? $post->account->account_name : ucfirst($post->platform) }}
                                        </div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50">
                                            {{ $post->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Format & Status Badges --}}
                                <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                    @if($post->media_type === 'carousel')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1">
                                            <i data-lucide="layers" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.format_carousel') }}</span>
                                        </span>
                                    @elseif($post->media_type === 'reels')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/15 text-[#AF52DE] inline-flex items-center gap-1">
                                            <i data-lucide="film" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.format_reels') }}</span>
                                        </span>
                                    @elseif($post->media_type === 'video')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF] inline-flex items-center gap-1">
                                            <i data-lucide="video" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.format_video') }}</span>
                                        </span>
                                    @elseif($post->media_type === 'image')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                            <i data-lucide="image" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.format_photo') }}</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 inline-flex items-center gap-1">
                                            <i data-lucide="align-left" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.format_text') }}</span>
                                        </span>
                                    @endif

                                    @if($post->status === 'published')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                            <span>{{ __('social_media.filter_status_published') }}</span>
                                        </span>
                                    @elseif($post->status === 'scheduled')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6] inline-flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.filter_status_scheduled') }}: {{ $post->scheduled_at ? $post->scheduled_at->format('d M H:i') : '-' }}</span>
                                        </span>
                                    @elseif($post->status === 'failed')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] inline-flex items-center gap-1">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.filter_status_failed') }}</span>
                                        </span>
                                    @elseif($post->status === 'partially_failed')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.status_partially_failed') }}</span>
                                        </span>
                                    @elseif($post->approval_status === 'pending_review' || $post->status === 'pending_review')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1">
                                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.status_pending_approval') }}</span>
                                        </span>
                                    @elseif($post->approval_status === 'rejected' || $post->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] inline-flex items-center gap-1">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                                            <span>{{ __('social_media.status_rejected') }}</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Multi-Channel Target Breakdown with Retry --}}
                            @if($post->targets->isNotEmpty())
                                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('social_media.post_target_channels') }}</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($post->targets as $target)
                                            <div class="px-2 py-0.5 rounded-[6px] text-[11px] font-semibold flex items-center gap-1.5 border
                                                {{ $target->status === 'published' ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20' : ($target->status === 'failed' ? 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20' : ($target->status === 'processing' ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 border-black/10')) }}">
                                                <span class="capitalize font-bold">{{ $target->channel }}</span>
                                                <span class="text-[10px] opacity-75">({{ $target->content_type }})</span>
                                                @if($target->status === 'published')
                                                    <i data-lucide="check" class="w-3 h-3 text-[#34C759]"></i>
                                                @elseif($target->status === 'failed')
                                                    <form method="POST" action="{{ route('social-media.targets.retry', $target) }}" class="inline ml-1" x-data="{ isSubmitting: false }" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true">
                                                        @csrf
                                                        <button type="submit" :disabled="isSubmitting" class="underline text-[#FF3B30] hover:text-black dark:hover:text-white font-bold inline-flex items-center gap-0.5 disabled:opacity-50" title="{{ __('social_media.retry_target_title') }}">
                                                            <i data-lucide="refresh-cw" class="w-2.5 h-2.5" :class="{ 'animate-spin': isSubmitting }"></i>
                                                            <span>{{ __('social_media.retry_target_btn') }}</span>
                                                        </button>
                                                    </form>
                                                @elseif($target->status === 'processing')
                                                    <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i>
                                                @else
                                                    <span class="text-[10px] opacity-75">{{ __('social_media.status_queued') }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($post->isPartiallyFailed())
                                <div class="p-2 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] text-[11.5px] font-semibold flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span>{{ __('social_media.partially_failed_alert') }}</span>
                                </div>
                            @endif

                            {{-- Media Preview if available --}}
                            @if(!empty($post->media_urls) && count($post->media_urls) > 0)
                                <div class="rounded-[14px] overflow-hidden border border-black/5 dark:border-white/10 bg-black aspect-video max-h-48 relative group flex items-center justify-center">
                                    @if(in_array($post->media_type, ['video', 'reels']))
                                        <video src="{{ $post->media_urls[0] }}" controls preload="metadata" class="w-full h-full object-cover"></video>
                                    @else
                                        <img src="{{ $post->media_urls[0] }}" alt="{{ __('social_media.media_preview_alt') }}" class="w-full h-full object-cover"
                                             onerror="this.onerror=null; this.src='https://placehold.co/600x400/1C1C1E/FFF?text=Media+Preview';">
                                    @endif
                                </div>
                            @elseif(in_array($post->media_type, ['video', 'reels', 'image']) && $post->status === 'published')
                                <div class="rounded-[12px] p-3 bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center justify-between text-[11.5px] text-black/60 dark:text-white/60">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $post->media_type === 'reels' ? 'film' : ($post->media_type === 'video' ? 'video' : 'image') }}" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span class="font-medium">{{ __('social_media.media_published_meta') }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                                        <span>{{ __('social_media.server_clean_badge') }}</span>
                                    </span>
                                </div>
                            @endif

                            {{-- Content text --}}
                            <p class="text-[13px] text-black/80 dark:text-white/80 leading-relaxed line-clamp-4 whitespace-pre-wrap">
                                {{ $post->content }}
                            </p>

                            @if($post->status === 'failed' && $post->error_message)
                                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[11.5px] text-[#FF3B30]">
                                    <strong>{{ __('social_media.cause_label') }}</strong> {{ $post->error_message }}
                                </div>
                            @endif

                            {{-- Risk Flags Warning --}}
                            @if(! empty($post->risk_flags) && in_array('unregistered_bank_account_detected', $post->risk_flags, true))
                                <div class="p-2.5 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[11.5px] text-[#FF9500] flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('social_media.anti_fraud_warning_bank') }}</span>
                                </div>
                            @endif

                            {{-- Rejection Reason --}}
                            @if(($post->approval_status === 'rejected' || $post->status === 'rejected') && $post->rejection_reason)
                                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[11.5px] text-[#FF3B30]">
                                    <strong>{{ __('social_media.rejection_reason_label') }}</strong> {{ $post->rejection_reason }}
                                </div>
                            @endif

                            {{-- Maker-Checker Action Buttons for Owner / Manager --}}
                            @if(($post->approval_status === 'pending_review' || $post->status === 'pending_review') && (\App\Support\Context::isAdminOrOwner() || \App\Support\Context::hasPermission('social_media.manage')))
                                <div class="pt-2 flex items-center gap-2 justify-end border-t border-black/5 dark:border-white/10">
                                    <form method="POST" action="{{ route('social-media.posts.approve', $post) }}" class="inline" x-data="{ isSubmitting: false }"
                                        @submit="if(isSubmitting) { $event.preventDefault(); return false; } if(typeof AppAlert !== 'undefined') { if(!AppAlert.confirmSubmit($event, $el, '{{ __('social_media.confirm_approve_msg') }}', '{{ __('social_media.confirm_approve_title') }}', 'info')) return false; } isSubmitting = true">
                                        @csrf
                                        <button type="submit" :disabled="isSubmitting" class="min-h-[44px] sm:min-h-0 sm:h-8 px-3.5 rounded-[9px] text-[12px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.97] transition-all inline-flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                                            <i data-lucide="check" class="w-3.5 h-3.5" :class="{ 'hidden': isSubmitting }"></i>
                                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" :class="{ 'hidden': !isSubmitting }"></i>
                                            <span>{{ __('social_media.approve_and_publish_btn') }}</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('social-media.posts.reject', $post) }}" class="inline" x-data="{ isSubmitting: false }"
                                        @submit="if(isSubmitting) { $event.preventDefault(); return false; } if(typeof AppAlert !== 'undefined') { if(!AppAlert.confirmSubmit($event, $el, '{{ __('social_media.confirm_reject_msg') }}', '{{ __('social_media.confirm_reject_title') }}', 'danger')) return false; } isSubmitting = true">
                                        @csrf
                                        <button type="submit" :disabled="isSubmitting" class="min-h-[44px] sm:min-h-0 sm:h-8 px-3.5 rounded-[9px] text-[12px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] transition-all inline-flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                                            <i data-lucide="x" class="w-3.5 h-3.5" :class="{ 'hidden': isSubmitting }"></i>
                                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" :class="{ 'hidden': !isSubmitting }"></i>
                                            <span>{{ __('social_media.reject_post_btn') }}</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Footer Card with Metrics + Quick Actions --}}
                        <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-2.5">
                            {{-- Metrics row --}}
                            <div class="flex items-center justify-between text-[12px] text-black/50 dark:text-white/50">
                                <div class="flex items-center gap-3 font-semibold tabular-nums">
                                    <span class="inline-flex items-center gap-1" title="{{ __('social_media.impressions_tooltip') }}">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>{{ number_format($post->getMetric('impressions')) }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1" title="{{ __('social_media.likes_tooltip') }}">
                                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                                        <span>{{ number_format($post->getMetric('likes')) }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1" title="{{ __('social_media.comments_tooltip') }}">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>{{ number_format($post->getMetric('comments')) }}</span>
                                    </span>
                                </div>
                                @if($post->status === 'published' && $post->published_at)
                                    <span class="text-[11px]">{{ $post->published_at->format('d/m/Y') }}</span>
                                @endif
                            </div>

                            {{-- Quick action buttons for manageable posts --}}
                            @if(in_array($post->status, ['scheduled', 'pending', 'failed', 'partially_failed']) && \App\Support\Context::hasPermission('social_media.manage'))
                                <div class="flex items-center gap-2">
                                    {{-- Preview / Kelola button --}}
                                    <button type="button"
                                        @click="openPostManager({
                                            id: '{{ $post->id }}',
                                            content: {{ Js::from($post->content) }},
                                            status: '{{ $post->status }}',
                                            platform: '{{ $post->platform }}',
                                            media_type: '{{ $post->media_type }}',
                                            media_urls: {{ Js::from($post->media_urls ?? []) }},
                                            scheduled_at_raw: '{{ $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '' }}',
                                            scheduled_at_label: '{{ $post->scheduled_at ? $post->scheduled_at->format('d M Y, H:i') : '-' }}',
                                            account_name: '{{ $post->account ? addslashes($post->account->account_name) : ucfirst($post->platform) }}',
                                            reschedule_url: '{{ route('social-media.posts.reschedule', $post) }}',
                                            publish_now_url: '{{ route('social-media.posts.publish-now', $post) }}',
                                            destroy_url: '{{ route('social-media.posts.destroy', $post) }}'
                                        })"
                                        class="flex-1 min-h-[44px] sm:min-h-[36px] px-3 rounded-[9px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.97] transition-all inline-flex items-center justify-center gap-1.5">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('social_media.preview_and_manage') }}</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $posts->links() }}
            </div>
        @endif

        {{-- 5. UNIFIED COMPOSER MODAL SHEET (Apple HIG Bento Design XXL) --}}
        <div x-show="openComposerModal" style="display: none;" x-cloak
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-3 sm:p-5"
            @keydown.escape.window="openComposerModal = false"
            role="dialog"
            aria-modal="true">
            <div class="relative w-full max-w-5xl xl:max-w-6xl 2xl:max-w-[1550px] rounded-[26px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-8 space-y-6 my-4 max-h-[94vh] overflow-y-auto"
                @click.away="openComposerModal = false">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="feather" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[17px] font-bold text-black dark:text-white">{{ __('social_media.composer_title') }}</h3>
                            <p class="text-[12.5px] text-black/55 dark:text-white/55">{{ __('social_media.composer_subtitle') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="openComposerModal = false" class="w-9 h-9 sm:w-8 sm:h-8 flex items-center justify-center rounded-full text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Form Content with Bento 2-Column Architecture --}}
                <form action="{{ route('social-media.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="isSubmitting = true">
                    @csrf
                    <input type="hidden" name="media_format" :value="mediaFormat">
                    <input type="hidden" name="social_media_account_id" :value="selectedAccounts[0] || ''">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        {{-- LEFT COLUMN: FORM CONTROLS (Col-span 7) --}}
                        <div class="lg:col-span-7 space-y-4">
                            {{-- 1. Target Accounts Multi-Selector (Bento Tiles) --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[12.5px] font-bold text-black/80 dark:text-white/80">
                                        {{ __('social_media.select_channels') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <button type="button" @click="toggleSelectAll()" class="text-[11.5px] font-bold text-[#007AFF] hover:underline">
                                        <span x-text="selectedAccounts.length === allAccountIds.length ? '{{ __('social_media.deselect_all_channels') }}' : '{{ __('social_media.select_all_channels') }}'"></span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($accounts as $acc)
                                        <label class="p-3 rounded-[14px] border cursor-pointer transition-all flex items-center justify-between gap-2.5"
                                            :class="selectedAccounts.includes('{{ $acc->id }}') ? 'bg-[#007AFF]/10 border-[#007AFF] text-black dark:text-white shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <input type="checkbox" name="target_accounts[]" value="{{ $acc->id }}"
                                                    x-model="selectedAccounts" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                                                <div class="min-w-0">
                                                    <div class="text-[13px] font-bold truncate">{{ $acc->account_name }}</div>
                                                    <div class="text-[11.5px] opacity-75 capitalize font-medium">
                                                        <span>{{ $acc->platform }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="w-7 h-7 rounded-[9px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : ($acc->platform === 'linkedin' ? 'bg-[#0A66C2]/15 text-[#0A66C2]' : 'bg-black/10 text-black dark:text-white'))) }}">
                                                <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                @if($accounts->isEmpty())
                                    <div class="p-3 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[12px] text-[#FF9500] flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.no_active_accounts_alert') }} <a href="{{ route('social-media.index') }}" class="underline font-bold">{{ __('social_media.connect_now_link') }}</a>.</span>
                                    </div>
                                @endif
                            </div>

                            {{-- 2. Format Selector (Bento Segmented Buttons) --}}
                            <div class="space-y-2">
                                <label class="text-[12.5px] font-bold text-black/80 dark:text-white/80">
                                    {{ __('social_media.post_format') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                    {{-- Foto Tunggal --}}
                                    <button type="button" @click="setMediaFormat('photo')"
                                        :class="mediaFormat === 'photo' ? 'bg-[#007AFF] text-white shadow-sm ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                        class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                        <div class="flex items-center justify-between">
                                            <i data-lucide="image" class="w-4 h-4"></i>
                                            <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'photo' ? 'bg-white' : 'bg-transparent'"></span>
                                        </div>
                                        <div class="text-[12px] font-bold leading-tight">{{ __('social_media.format_photo') }}</div>
                                        <div class="text-[10px] opacity-75 truncate">{{ __('social_media.format_photo_sub') }}</div>
                                    </button>

                                    {{-- Carousel (Instagram & Multi) --}}
                                    <button type="button" @click="setMediaFormat('carousel')"
                                        :class="mediaFormat === 'carousel' ? 'bg-[#FF9500] text-white shadow-sm ring-2 ring-[#FF9500]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                        class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                        <div class="flex items-center justify-between">
                                            <i data-lucide="layers" class="w-4 h-4"></i>
                                            <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'carousel' ? 'bg-white' : 'bg-transparent'"></span>
                                        </div>
                                        <div class="text-[12px] font-bold leading-tight">{{ __('social_media.format_carousel') }}</div>
                                        <div class="text-[10px] opacity-75 truncate">{{ __('social_media.format_carousel_sub') }}</div>
                                    </button>

                                    {{-- Video --}}
                                    <button type="button" @click="setMediaFormat('video')"
                                        :class="mediaFormat === 'video' ? 'bg-[#007AFF] text-white shadow-sm ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                        class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                        <div class="flex items-center justify-between">
                                            <i data-lucide="video" class="w-4 h-4"></i>
                                            <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'video' ? 'bg-white' : 'bg-transparent'"></span>
                                        </div>
                                        <div class="text-[12px] font-bold leading-tight">{{ __('social_media.format_video') }}</div>
                                        <div class="text-[10px] opacity-75 truncate">{{ __('social_media.format_video_sub') }}</div>
                                    </button>

                                    {{-- Reels --}}
                                    <button type="button" @click="setMediaFormat('reels')"
                                        :class="mediaFormat === 'reels' ? 'bg-[#AF52DE] text-white shadow-sm ring-2 ring-[#AF52DE]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                        class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                        <div class="flex items-center justify-between">
                                            <i data-lucide="film" class="w-4 h-4"></i>
                                            <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'reels' ? 'bg-white' : 'bg-transparent'"></span>
                                        </div>
                                        <div class="text-[12px] font-bold leading-tight">{{ __('social_media.format_reels') }}</div>
                                        <div class="text-[10px] opacity-75 truncate">{{ __('social_media.format_reels_sub') }}</div>
                                    </button>

                                    {{-- Teks --}}
                                    <button type="button" @click="setMediaFormat('text')"
                                        :class="mediaFormat === 'text' ? 'bg-[#34C759] text-white shadow-sm ring-2 ring-[#34C759]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                        class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1">
                                        <div class="flex items-center justify-between">
                                            <i data-lucide="align-left" class="w-4 h-4"></i>
                                            <span class="w-1.5 h-1.5 rounded-full" :class="mediaFormat === 'text' ? 'bg-white' : 'bg-transparent'"></span>
                                        </div>
                                        <div class="text-[12px] font-bold leading-tight">{{ __('social_media.format_text') }}</div>
                                        <div class="text-[10px] opacity-75 truncate">{{ __('social_media.format_text_sub') }}</div>
                                    </button>
                                </div>
                            </div>

                            {{-- 3. Media Upload Area (Single or Carousel) --}}
                            <div x-show="mediaFormat !== 'text'" class="space-y-3 pt-1">
                                <div class="flex items-center justify-between">
                                    <label class="text-[12.5px] font-bold text-black/80 dark:text-white/80">
                                        {{ __('social_media.media_files_label') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] text-[11.5px] font-medium">
                                        <button type="button" @click="mediaSourceTab = 'upload'"
                                            :class="mediaSourceTab === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60'"
                                            class="px-2.5 py-1 rounded-[7px] transition-colors">
                                            {{ __('social_media.tab_upload_file') }}
                                        </button>
                                        <button type="button" @click="mediaSourceTab = 'url'"
                                            :class="mediaSourceTab === 'url' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60'"
                                            class="px-2.5 py-1 rounded-[7px] transition-colors">
                                            {{ __('social_media.tab_direct_url') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Mode A: Carousel Multi-Upload (2 - 10 Media) --}}
                                <div x-show="mediaFormat === 'carousel' && mediaSourceTab === 'upload'" class="space-y-3">
                                    <input type="file" name="media_files[]" id="carousel_files_input" x-ref="carouselInput" multiple
                                        @change="handleCarouselFilesSelect($event)" accept="image/jpeg,image/png,image/webp,video/mp4" class="hidden">

                                    <div class="flex items-center justify-between text-[12px]">
                                        <span class="font-bold text-black/70 dark:text-white/70">
                                            {{ __('social_media.carousel_order_label') }} <span class="text-[#FF9500]" x-text="carouselItems.length + ' / 10 media'"></span>
                                        </span>
                                        <button type="button" @click="$refs.carouselInput.click()"
                                            class="min-h-[44px] sm:min-h-0 px-3 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 font-bold text-[11.5px] transition-colors flex items-center gap-1 cursor-pointer">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('social_media.add_media_btn') }}</span>
                                        </button>
                                    </div>

                                    {{-- Carousel Empty State --}}
                                    <div x-show="carouselItems.length === 0" @click="$refs.carouselInput.click()"
                                        class="rounded-[16px] border-2 border-dashed border-black/15 dark:border-white/15 p-6 text-center cursor-pointer hover:border-[#FF9500] hover:bg-[#FF9500]/5 transition-all group">
                                        <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center mx-auto mb-2">
                                            <i data-lucide="layers" class="w-6 h-6"></i>
                                        </div>
                                        <div class="text-[13px] font-bold text-black dark:text-white group-hover:text-[#FF9500]">
                                            {{ __('social_media.carousel_empty_title') }}
                                        </div>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">
                                            {{ __('social_media.carousel_empty_sub') }}
                                        </p>
                                    </div>

                                    {{-- Carousel Items Tray with Reordering & Badges --}}
                                    <div x-show="carouselItems.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                        <template x-for="(item, idx) in carouselItems" :key="idx">
                                            <div class="relative p-2 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 flex flex-col gap-2 group">
                                                {{-- Sort Order Badge --}}
                                                <div class="absolute top-3 left-3 w-5 h-5 rounded-full bg-black/80 text-white text-[10px] font-bold flex items-center justify-center z-10" x-text="idx + 1"></div>

                                                {{-- Thumbnail --}}
                                                <div class="w-full aspect-square rounded-[10px] overflow-hidden bg-black/5 flex items-center justify-center">
                                                    <template x-if="item.mime.startsWith('image/')">
                                                        <img :src="item.previewUrl" class="w-full h-full object-cover">
                                                    </template>
                                                    <template x-if="item.mime.startsWith('video/')">
                                                        <video :src="item.previewUrl" class="w-full h-full object-cover"></video>
                                                    </template>
                                                </div>

                                                {{-- Controls: Move Left, Move Right, Delete --}}
                                                <div class="flex items-center justify-between text-[11px] pt-1">
                                                    <div class="flex items-center gap-1">
                                                        <button type="button" @click="moveCarouselItem(idx, -1)" :disabled="idx === 0"
                                                            class="w-6 h-6 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center disabled:opacity-30">
                                                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <button type="button" @click="moveCarouselItem(idx, 1)" :disabled="idx === carouselItems.length - 1"
                                                            class="w-6 h-6 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center disabled:opacity-30">
                                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                    <button type="button" @click="removeCarouselItem(idx)"
                                                        class="w-6 h-6 rounded-[6px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Mode B: Single File Upload (Photo, Video, Reels) --}}
                                <div x-show="mediaFormat !== 'carousel' && mediaSourceTab === 'upload'" class="space-y-2.5">
                                    <input type="file" name="media_file" id="media_file_input" x-ref="fileInput"
                                        @change="handleFileSelect($event)"
                                        :accept="mediaFormat === 'photo' ? 'image/jpeg,image/png,image/webp,image/gif' : 'video/mp4,video/quicktime'"
                                        class="hidden">

                                    {{-- Drop Zone when empty --}}
                                    <div x-show="!filePreviewUrl"
                                        @click="$refs.fileInput.click()"
                                        class="rounded-[16px] border-2 border-dashed border-black/15 dark:border-white/15 p-6 text-center cursor-pointer hover:border-[#007AFF] hover:bg-[#007AFF]/5 transition-all group">
                                        <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 group-hover:text-[#007AFF] group-hover:scale-105 transition-all flex items-center justify-center mx-auto mb-2.5">
                                            <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                        </div>
                                        <div class="text-[13px] font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                                            {{ __('social_media.single_dropzone_title') }}
                                        </div>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5"
                                           x-text="mediaFormat === 'photo' ? '{{ __('social_media.single_dropzone_sub_photo') }}' : '{{ __('social_media.single_dropzone_sub_video') }}'">
                                        </p>
                                    </div>

                                    {{-- File Preview Card when selected --}}
                                    <div x-show="filePreviewUrl" style="display: none;"
                                        class="rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 p-3.5 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <template x-if="fileMime && fileMime.startsWith('image/')">
                                                <img :src="filePreviewUrl" alt="Pratinjau Foto" class="w-16 h-16 rounded-[10px] object-cover border border-black/10 dark:border-white/10 shrink-0">
                                            </template>
                                            <template x-if="fileMime && fileMime.startsWith('video/')">
                                                <video :src="filePreviewUrl" controls class="w-24 h-16 rounded-[10px] object-cover bg-black shrink-0"></video>
                                            </template>
                                            <div class="min-w-0">
                                                <div class="text-[13px] font-bold text-black dark:text-white truncate" x-text="fileName"></div>
                                                <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-2 mt-0.5">
                                                    <span x-text="fileSize"></span>
                                                    <span>•</span>
                                                    <span class="uppercase font-semibold text-[#007AFF]" x-text="mediaFormat"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <button type="button" @click="$refs.fileInput.click()"
                                                class="min-h-[44px] sm:min-h-0 h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                                {{ __('social_media.change_file_btn') }}
                                            </button>
                                            <button type="button" @click="clearFile()"
                                                class="min-h-[44px] sm:min-h-0 h-8 w-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors flex items-center justify-center">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Mode C: Direct URL --}}
                                <div x-show="mediaSourceTab === 'url'" style="display: none;" class="space-y-1.5">
                                    <div class="relative">
                                        <i data-lucide="link" class="w-4 h-4 absolute left-3 top-3 text-black/40 dark:text-white/40"></i>
                                        <input type="url" name="media_url" x-model="mediaUrl"
                                            placeholder="https://domain-anda.com/media/promo.mp4"
                                            class="w-full min-h-[44px] sm:min-h-0 h-10 pl-9 pr-3 rounded-[12px] text-[16px] sm:text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50">
                                        {{ __('social_media.direct_url_sub') }}
                                    </p>
                                </div>
                            </div>

                            {{-- 4. Text Content & STRICT COOCA 5-HASHTAG COUNTER & THREADS 500-CHAR GUARDRAIL --}}
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-[12.5px] font-bold text-black/80 dark:text-white/80">
                                    <div class="flex items-center gap-2">
                                        <label>{{ __('social_media.main_caption_label') }} <span class="text-[#FF3B30]">*</span></label>
                                        <span class="text-[11px] font-normal text-black/50 dark:text-white/50">{{ __('social_media.caption_mapping_subtitle') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        {{-- COOCA 5 Hashtag Badge --}}
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center gap-1 border transition-colors"
                                            :class="hashtagCount <= 5 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30'">
                                            <i data-lucide="hash" class="w-3 h-3"></i>
                                            <span x-text="'{{ __('social_media.hashtag_counter', ['count' => '']) }}' + hashtagCount + ' / 5'"></span>
                                        </span>
                                        <span class="text-[11.5px] text-black/40 dark:text-white/40 tabular-nums font-normal" x-text="captionText.length + ' / 5000'"></span>
                                    </div>
                                </div>

                                <textarea name="content" rows="4" required x-model="captionText"
                                    :placeholder="mediaFormat === 'reels' ? '{{ __('social_media.main_caption_placeholder_reels') }}' : (mediaFormat === 'video' ? '{{ __('social_media.main_caption_placeholder_video') }}' : '{{ __('social_media.main_caption_placeholder_default') }}')"
                                    class="w-full p-3.5 rounded-[14px] text-[16px] sm:text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>

                                {{-- Alert Exceeding 5 Hashtags --}}
                                <div x-show="hashtagCount > 5" style="display: none;" class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#FF3B30] flex items-start gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                    <div>
                                        <span>{!! __('social_media.hashtag_rule_alert', ['count' => '<strong x-text="hashtagCount"></strong>', 'reduce' => '<span x-text="hashtagCount - 5"></span>']) !!}</span>
                                    </div>
                                </div>

                                {{-- Peringatan Khusus Threads: Karakter > 500 --}}
                                <div x-show="hasThreadsSelected && isThreadsOverLimit" style="display: none;"
                                    class="p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/30 text-[#D97706] dark:text-[#F59E0B] space-y-2.5 shadow-xs transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/20 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="at-sign" class="w-4 h-4 text-[#D97706] dark:text-[#F59E0B]"></i>
                                        </div>
                                        <div class="space-y-0.5">
                                            <div class="text-[13px] font-bold text-black dark:text-white">
                                                {{ __('social_media.threads_over_limit_title') }}
                                            </div>
                                            <div class="text-[11.5px] opacity-90 leading-relaxed text-black/80 dark:text-white/80">
                                                {!! __('social_media.threads_over_limit_desc', ['count' => '<strong x-text="captionText.length"></strong>']) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-1 flex flex-wrap items-center gap-2">
                                        <button type="button" @click="createSeparateThreadsCaption()"
                                            class="min-h-[44px] sm:min-h-0 h-9 sm:h-8 px-3 rounded-[9px] text-[12px] sm:text-[11.5px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                                            <i data-lucide="scissors" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('social_media.threads_create_separate_btn') }}</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Status Caption Terpisah Threads Aktif --}}
                                <div x-show="hasThreadsSelected && isThreadsSeparateActive" style="display: none;"
                                    class="p-2.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] flex items-center justify-between gap-2 text-[12px] font-semibold transition-all">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                                        <span>{!! __('social_media.threads_separate_active', ['count' => '<span x-text="getCaptionLength(threadsAccountId)"></span>']) !!}</span>
                                    </div>
                                    <button type="button" @click="showOverrides = true" class="text-[11px] font-bold underline hover:opacity-80 min-h-[44px] sm:min-h-0 flex items-center">
                                        {{ __('social_media.threads_view_edit_btn') }}
                                    </button>
                                </div>
                            </div>

                            {{-- 5. Collapsible Channel Caption Overrides (Input Caption Terpisah per Saluran) --}}
                            <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                                <button type="button" @click="showOverrides = !showOverrides"
                                    class="w-full flex items-center justify-between text-left text-[12.5px] font-bold text-black/80 dark:text-white/80 min-h-[44px] sm:min-h-0">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span>{{ __('social_media.channel_overrides_toggle') }}</span>
                                    </span>
                                    <span class="text-[11.5px] text-[#007AFF] font-bold" x-text="showOverrides ? '{{ __('social_media.channel_overrides_close') }}' : '{{ __('social_media.channel_overrides_open') }}'"></span>
                                </button>

                                <div x-show="showOverrides" style="display: none;" class="space-y-3 pt-2 border-t border-black/5 dark:border-white/5">
                                    <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                        {{ __('social_media.channel_overrides_sub') }}
                                    </p>

                                    <div class="space-y-3">
                                        @foreach($accounts as $acc)
                                            <div class="space-y-2 p-3 rounded-[12px] bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10"
                                                 x-show="selectedAccounts.includes('{{ $acc->id }}')">
                                                <div class="flex items-center justify-between">
                                                    <label class="text-[12px] font-bold text-black/80 dark:text-white/80 capitalize flex items-center gap-2">
                                                        <div class="w-5 h-5 rounded-[6px] flex items-center justify-center text-[10px] {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : ($acc->platform === 'linkedin' ? 'bg-[#0A66C2]/15 text-[#0A66C2]' : 'bg-black/10 text-black dark:text-white'))) }}">
                                                            <x-social-icon :platform="$acc->platform" class="w-3 h-3" />
                                                        </div>
                                                        <span>{{ $acc->account_name }} ({{ ucfirst($acc->platform) }})</span>
                                                        <span class="text-[10.5px] font-normal px-2 py-0.5 rounded-full bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                                            {{ __('social_media.max_chars_badge', ['count' => number_format(\App\Domain\SocialMedia\Validation\SocialMediaContentValidator::CAPTION_LIMITS[$acc->platform] ?? 2200)]) }}
                                                        </span>
                                                    </label>

                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="copyFromMain('{{ $acc->id }}')"
                                                            class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 min-h-[44px] sm:min-h-0">
                                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                                            <span>{{ __('social_media.copy_from_main_btn') }}</span>
                                                        </button>
                                                        <template x-if="customCaptions['{{ $acc->id }}']">
                                                            <button type="button" @click="resetCustomCaption('{{ $acc->id }}')"
                                                                class="text-[11px] font-semibold text-[#FF3B30] hover:underline min-h-[44px] sm:min-h-0 flex items-center">
                                                                {{ __('social_media.reset_to_main_caption') }}
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>

                                                <textarea name="custom_captions[{{ $acc->id }}]"
                                                    id="custom_caption_{{ $acc->id }}"
                                                    rows="3"
                                                    x-model="customCaptions['{{ $acc->id }}']"
                                                    placeholder="{{ __('social_media.override_channel_placeholder') }}"
                                                    class="w-full p-2.5 rounded-[10px] text-[16px] sm:text-[12px] bg-black/[0.02] dark:bg-black/20 border text-black dark:text-white focus:outline-none focus:ring-1 resize-none transition-colors"
                                                    :class="getCaptionLength('{{ $acc->id }}') > (accountsMap['{{ $acc->id }}']?.limit || 2200) ? 'border-[#FF3B30] focus:ring-[#FF3B30]' : 'border-black/10 dark:border-white/10 focus:ring-[#007AFF]'"></textarea>

                                                {{-- Live Counters & State per Saluran --}}
                                                <div class="flex items-center justify-between text-[11px]">
                                                    <div class="flex items-center gap-2">
                                                        <template x-if="!customCaptions['{{ $acc->id }}']">
                                                            <span class="text-black/50 dark:text-white/50 italic">{{ __('social_media.using_main_caption') }}</span>
                                                        </template>
                                                        <template x-if="customCaptions['{{ $acc->id }}']">
                                                            <span class="text-[#007AFF] font-semibold">{{ __('social_media.custom_caption_active') }}</span>
                                                        </template>
                                                        <template x-if="getChannelHashtags('{{ $acc->id }}').length > 5">
                                                            <span class="text-[#FF3B30] font-bold">{{ __('social_media.tag_over_limit') }}</span>
                                                        </template>
                                                    </div>
                                                    <div class="flex items-center gap-2 font-mono">
                                                        <span :class="getChannelHashtags('{{ $acc->id }}').length <= 5 ? 'text-black/60 dark:text-white/60' : 'text-[#FF3B30] font-bold'">
                                                            # <span x-text="getChannelHashtags('{{ $acc->id }}').length"></span>/5
                                                        </span>
                                                        <span>•</span>
                                                        <span :class="getCaptionLength('{{ $acc->id }}') <= (accountsMap['{{ $acc->id }}']?.limit || 2200) ? 'text-black/60 dark:text-white/60' : 'text-[#FF3B30] font-bold'">
                                                            <span x-text="getCaptionLength('{{ $acc->id }}')"></span> / <span x-text="accountsMap['{{ $acc->id }}']?.limit || 2200"></span> Chars
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Channel Limit Alert --}}
                                                <template x-if="getCaptionLength('{{ $acc->id }}') > (accountsMap['{{ $acc->id }}']?.limit || 2200)">
                                                    <div class="p-2 rounded-[8px] bg-[#FF3B30]/10 text-[#FF3B30] text-[11px] font-semibold flex items-center gap-1.5">
                                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                                        <span>{!! __('social_media.channel_limit_exceeded', ['count' => '<span x-text="getCaptionLength(\'' . $acc->id . '\') - (accountsMap[\'' . $acc->id . '\']?.limit || 2200)"></span>']) !!}</span>
                                                    </div>
                                                </template>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- 6. Waktu & Penjadwalan Publikasi (Multi-Mode & Per-Channel Support) --}}
                            <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-[13px] font-bold text-black dark:text-white">{{ __('social_media.schedule_title') }}</span>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('social_media.schedule_subtitle') }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                        {{ __('social_media.flexible_badge') }}
                                    </span>
                                </div>

                                {{-- Mode Selector --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[12px]">
                                    <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                        :class="scheduleMode === 'all_now' ? 'bg-[#007AFF]/10 border-[#007AFF] text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                        <input type="radio" name="schedule_mode" value="all_now" x-model="scheduleMode" class="sr-only">
                                        <i data-lucide="zap" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ __('social_media.mode_all_now') }}</span>
                                    </label>

                                    <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                        :class="scheduleMode === 'all_same' ? 'bg-[#5856D6]/10 border-[#5856D6] text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                        <input type="radio" name="schedule_mode" value="all_same" x-model="scheduleMode" class="sr-only">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                        <span>{{ __('social_media.mode_all_same') }}</span>
                                    </label>

                                    <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                        :class="scheduleMode === 'per_channel' ? 'bg-[#FF9500]/10 border-[#FF9500] text-black dark:text-white font-bold shadow-xs' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                        <input type="radio" name="schedule_mode" value="per_channel" x-model="scheduleMode" class="sr-only">
                                        <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                        <span>{{ __('social_media.mode_per_channel') }}</span>
                                    </label>
                                </div>

                                {{-- Mode B: Jadwal Serentak (Satu waktu untuk semua saluran) --}}
                                <div x-show="scheduleMode === 'all_same'" style="display: none;" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-1.5">
                                    <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70">{{ __('social_media.global_schedule_label') }}</label>
                                    <input type="datetime-local" name="scheduled_at" x-model="globalScheduleTime"
                                        class="w-full h-11 sm:h-9 px-3 rounded-[10px] text-[16px] sm:text-[12.5px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                    <p class="text-[11px] text-black/50 dark:text-white/50">
                                        {{ __('social_media.global_schedule_sub') }}
                                    </p>
                                </div>

                                {{-- Mode C: Beda Waktu per Saluran (Beda Jam per Akun) --}}
                                <div x-show="scheduleMode === 'per_channel'" style="display: none;" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-2.5">
                                    <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                        {{ __('social_media.per_channel_schedule_sub') }}
                                    </p>
                                    <div class="space-y-2">
                                        @foreach($accounts as $acc)
                                            <div x-show="selectedAccounts.includes('{{ $acc->id }}')"
                                                class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <div class="w-6 h-6 rounded-[7px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : ($acc->platform === 'linkedin' ? 'bg-[#0A66C2]/15 text-[#0A66C2]' : 'bg-black/10 text-black dark:text-white'))) }}">
                                                            <x-social-icon :platform="$acc->platform" class="w-3.5 h-3.5" />
                                                        </div>
                                                        <span class="text-[12px] font-bold text-black dark:text-white truncate">{{ $acc->account_name }}</span>
                                                        <span class="text-[10.5px] text-black/50 dark:text-white/50 uppercase font-mono">({{ $acc->platform }})</span>
                                                    </div>
                                                    <div class="inline-flex p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px]">
                                                        <button type="button" @click="channelTiming['{{ $acc->id }}'] = 'now'"
                                                            :class="channelTiming['{{ $acc->id }}'] === 'now' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                            class="px-2.5 py-1 sm:py-0.5 rounded-[6px] min-h-[36px] sm:min-h-0 transition-colors">
                                                            {{ __('social_media.timing_now') }}
                                                        </button>
                                                        <button type="button" @click="channelTiming['{{ $acc->id }}'] = 'schedule'"
                                                            :class="channelTiming['{{ $acc->id }}'] === 'schedule' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF9500] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                            class="px-2.5 py-1 sm:py-0.5 rounded-[6px] min-h-[36px] sm:min-h-0 transition-colors">
                                                            {{ __('social_media.timing_schedule') }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <input type="hidden" :name="'channel_schedule_modes[{{ $acc->id }}]'" :value="channelTiming['{{ $acc->id }}']">

                                                <div x-show="channelTiming['{{ $acc->id }}'] === 'schedule'" class="pt-1.5 border-t border-black/5 dark:border-white/5">
                                                    <input type="datetime-local" :name="'channel_scheduled_at[{{ $acc->id }}]'" x-model="channelScheduledAts['{{ $acc->id }}']"
                                                        class="w-full h-11 sm:h-8 px-2.5 rounded-[8px] text-[16px] sm:text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#FF9500]">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: DYNAMIC PREVIEW & BENTO GUARDRAILS (Col-span 5) --}}
                        <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-2">
                            {{-- A. SECTOR CONTEXTUAL GUARDRAIL (20 Sektor Industri Terpadu) --}}
                            @php
                                $bizTemplate = strtolower((string) ($business->template_code ?? ''));
                                $bizCategory = strtolower((string) ($business->industry_category ?? ''));
                                $bizIndustry = strtolower((string) ($business->industry ?? ''));

                                $isPharmacy = str_contains($bizTemplate, 'pharmacy') || str_contains($bizTemplate, 'apotek') || str_contains($bizCategory, 'pharmacy') || str_contains($bizIndustry, 'obat') || str_contains($bizIndustry, 'apotek');
                                $isClinic = str_contains($bizTemplate, 'clinic') || str_contains($bizTemplate, 'dental') || str_contains($bizTemplate, 'healthcare') || str_contains($bizCategory, 'healthcare') || str_contains($bizIndustry, 'klinik') || str_contains($bizIndustry, 'dokter') || str_contains($bizIndustry, 'gigi');
                                $isPetshop = str_contains($bizTemplate, 'petshop') || str_contains($bizTemplate, 'veterinary') || str_contains($bizCategory, 'pet') || str_contains($bizIndustry, 'hewan') || str_contains($bizIndustry, 'kucing') || str_contains($bizIndustry, 'anjing') || str_contains($bizIndustry, 'petshop');
                                $isWorkshop = str_contains($bizTemplate, 'workshop') || str_contains($bizTemplate, 'autodetailing') || str_contains($bizCategory, 'workshop') || str_contains($bizIndustry, 'bengkel') || str_contains($bizIndustry, 'detailing');
                                $isSalon = str_contains($bizTemplate, 'barbershop') || str_contains($bizTemplate, 'salon') || str_contains($bizTemplate, 'cosmetics') || str_contains($bizCategory, 'salon') || str_contains($bizIndustry, 'barbershop');
                                $isFnb = str_starts_with($bizTemplate, 'fnb') || $bizCategory === 'fnb' || str_contains($bizIndustry, 'resto') || str_contains($bizIndustry, 'kuliner') || str_contains($bizIndustry, 'cafe');
                                $isMfgCreative = str_starts_with($bizTemplate, 'mfg') || str_contains($bizTemplate, 'garment') || str_contains($bizTemplate, 'printing') || str_contains($bizTemplate, 'agency') || $bizCategory === 'manufacturing' || $bizCategory === 'creative';
                            @endphp

                            @if($isPharmacy)
                                {{-- Apotek / Toko Obat: BPOM & Prescription drugs alert --}}
                                <div class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_pharmacy_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_pharmacy_desc') }}
                                    </p>
                                </div>
                            @elseif($isClinic)
                                {{-- Klinik / Praktik Medis & Dokter Gigi: Informed consent & UU PDP --}}
                                <div class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_clinic_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_clinic_desc') }}
                                    </p>
                                </div>
                            @elseif($isPetshop)
                                {{-- Petshop & Klinik Hewan: Animal welfare & prescription drugs --}}
                                <div class="p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_petshop_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_petshop_desc') }}
                                    </p>
                                </div>
                            @elseif($isWorkshop)
                                {{-- Bengkel & Detailing: License plate & Customer privacy --}}
                                <div class="p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_workshop_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_workshop_desc') }}
                                    </p>
                                </div>
                            @elseif($isSalon)
                                {{-- Salon & Barbershop: Client consent & before-after --}}
                                <div class="p-3.5 rounded-[16px] bg-[#AF52DE]/10 border border-[#AF52DE]/25 text-[#AF52DE] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="camera" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_salon_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_salon_desc') }}
                                    </p>
                                </div>
                            @elseif($isFnb)
                                {{-- F&B: Golden hours tips --}}
                                <div class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="clock-8" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_fnb_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_fnb_desc') }}
                                    </p>
                                </div>
                            @elseif($isMfgCreative)
                                {{-- Garment, Percetakan, Agency: NDA & Client Copyright --}}
                                <div class="p-3.5 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="file-check-2" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_mfg_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-90">
                                        {{ __('social_media.guardrail_mfg_desc') }}
                                    </p>
                                </div>
                            @else
                                {{-- General UMKM: Business Ethics & Anti-Fraud --}}
                                <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-black/75 dark:text-white/75 space-y-1.5 shadow-xs">
                                    <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                        <span>{{ __('social_media.guardrail_general_title') }}</span>
                                    </div>
                                    <p class="text-[11.5px] leading-relaxed opacity-85">
                                        {{ __('social_media.guardrail_general_desc') }}
                                    </p>
                                </div>
                            @endif

                            {{-- B. QUIET HOURS ALERT CHIP --}}
                            <div x-show="isQuietHours" style="display: none;"
                                class="p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] space-y-1 shadow-xs transition-all">
                                <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                    <i data-lucide="moon" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('social_media.quiet_hours_title') }}</span>
                                </div>
                                <p class="text-[11.5px] leading-relaxed opacity-90">
                                    {{ __('social_media.quiet_hours_desc') }}
                                </p>
                            </div>

                            {{-- C. VIDEO ASPECT RATIO INSPECTOR --}}
                            <div x-show="(mediaFormat === 'reels' || mediaFormat === 'video') && isLandscapeVideo" style="display: none;"
                                class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] space-y-1.5 shadow-xs">
                                <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                    <i data-lucide="smartphone" class="w-4 h-4 shrink-0"></i>
                                    <span>{!! __('social_media.landscape_warning_title', ['ratio' => '<span x-text="videoRatio ? videoRatio + \':1\' : \'\'"></span>']) !!}</span>
                                </div>
                                <p class="text-[11.5px] leading-relaxed opacity-90">
                                    {!! __('social_media.landscape_warning_desc', ['dim' => '<span x-text="videoWidth + \'x\' + videoHeight"></span>']) !!}
                                </p>
                            </div>

                            <div x-show="(mediaFormat === 'reels' || mediaFormat === 'video') && !isLandscapeVideo && videoWidth && videoHeight" style="display: none;"
                                class="p-2.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 text-[11.5px] font-semibold">
                                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                                <span>{!! __('social_media.vertical_detected_badge', ['dim' => '<span x-text="videoWidth + \'x\' + videoHeight"></span>']) !!}</span>
                            </div>

                            {{-- D. LIVE SMARTPHONE FEED PREVIEW CARD (Apple HIG Style) --}}
                            <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 p-3.5 space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                                    <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                                        <i data-lucide="smartphone" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ __('social_media.live_preview_title') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]" x-text="mediaFormat.toUpperCase()"></span>
                                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70" x-text="previewAspectRatio"></span>
                                    </div>
                                </div>

                                {{-- Aspect Ratio & Social Media Size Toolbar --}}
                                <div class="space-y-1.5" x-show="mediaFormat !== 'text'">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-black/60 dark:text-white/60">{{ __('social_media.aspect_ratio_label') }}:</span>
                                        <span class="text-[10px] font-medium text-[#007AFF] truncate max-w-[210px]" x-text="getAspectRatioDescription()"></span>
                                    </div>
                                    <div class="grid grid-cols-4 gap-1 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-bold">
                                        {{-- 4:5 Feed IG Portrait --}}
                                        <button type="button" @click="previewAspectRatio = '4:5'"
                                            :class="previewAspectRatio === '4:5' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                            <span class="text-[12px]">4:5</span>
                                            <span class="text-[9px] opacity-75 font-normal">Feed IG</span>
                                        </button>
                                        {{-- 1:1 Square Feed --}}
                                        <button type="button" @click="previewAspectRatio = '1:1'"
                                            :class="previewAspectRatio === '1:1' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                            <span class="text-[12px]">1:1</span>
                                            <span class="text-[9px] opacity-75 font-normal">Square</span>
                                        </button>
                                        {{-- 9:16 Reels / TikTok --}}
                                        <button type="button" @click="previewAspectRatio = '9:16'"
                                            :class="previewAspectRatio === '9:16' ? 'bg-white dark:bg-[#2C2C2E] text-[#AF52DE] shadow-xs ring-1 ring-[#AF52DE]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                            <span class="text-[12px]">9:16</span>
                                            <span class="text-[9px] opacity-75 font-normal">Reels</span>
                                        </button>
                                        {{-- 16:9 Landscape Video --}}
                                        <button type="button" @click="previewAspectRatio = '16:9'"
                                            :class="previewAspectRatio === '16:9' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] shadow-xs ring-1 ring-[#FF9500]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                            class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                            <span class="text-[12px]">16:9</span>
                                            <span class="text-[9px] opacity-75 font-normal">Landscape</span>
                                        </button>
                                    </div>

                                    {{-- Fit / Cover mode pill switcher --}}
                                    <div class="flex items-center justify-between text-[10.5px] px-1 pt-0.5 text-black/50 dark:text-white/50">
                                        <span>{{ __('social_media.fit_mode_label') }}:</span>
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" @click="mediaFitMode = 'cover'"
                                                :class="mediaFitMode === 'cover' ? 'text-[#007AFF] font-bold underline' : 'hover:text-black dark:hover:text-white'"
                                                class="cursor-pointer">
                                                {{ __('social_media.fit_mode_cover') }}
                                            </button>
                                            <span>•</span>
                                            <button type="button" @click="mediaFitMode = 'contain'"
                                                :class="mediaFitMode === 'contain' ? 'text-[#007AFF] font-bold underline' : 'hover:text-black dark:hover:text-white'"
                                                class="cursor-pointer">
                                                {{ __('social_media.fit_mode_contain') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Phone Card Container --}}
                                <div class="rounded-[18px] bg-white dark:bg-[#151516] border border-black/10 dark:border-white/10 shadow-md overflow-hidden text-black dark:text-white transition-all duration-300"
                                    :class="getPreviewCardWidthClass()">
                                    {{-- Reels Top Bar (when 9:16) --}}
                                    <div x-show="previewAspectRatio === '9:16'" class="px-3.5 py-2.5 bg-black text-white flex items-center justify-between text-[11px] font-bold">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[13px] tracking-tight">Reels</span>
                                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                                        </div>
                                        <div class="flex items-center gap-3 opacity-80">
                                            <i data-lucide="camera" class="w-4 h-4"></i>
                                        </div>
                                    </div>

                                    {{-- Post Card Header (Feed mode: 4:5, 1:1, 16:9) --}}
                                    <div x-show="previewAspectRatio !== '9:16'" class="p-3 flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-8 h-8 rounded-full bg-[#007AFF] text-white font-bold text-[12px] flex items-center justify-center shrink-0 shadow-xs">
                                                {{ strtoupper(substr($business->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-[12.5px] font-bold truncate">{{ $business->name }}</div>
                                                <div class="text-[10.5px] text-black/50 dark:text-white/50 flex items-center gap-1 truncate">
                                                    <span x-text="scheduleMode === 'all_now' ? '{{ __('social_media.preview_just_now') }}' : '{{ __('social_media.preview_scheduled') }}'"></span>
                                                    <span>•</span>
                                                    <span x-text="activePlatformNames.length ? activePlatformNames.join(', ') : '{{ __('social_media.preview_select_channels') }}'"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <i data-lucide="more-horizontal" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                                    </div>

                                    {{-- Post Media Display Area --}}
                                    <div class="bg-black/5 dark:bg-black/30 relative flex items-center justify-center overflow-hidden transition-all duration-200"
                                        :class="getPreviewAspectClass()">

                                        {{-- Photo Preview --}}
                                        <template x-if="mediaFormat === 'photo'">
                                            <div class="w-full h-full flex items-center justify-center">
                                                <template x-if="filePreviewUrl">
                                                    <img :src="filePreviewUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'">
                                                </template>
                                                <template x-if="!filePreviewUrl && mediaSourceTab === 'url' && mediaUrl">
                                                    <img :src="mediaUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'">
                                                </template>
                                                <template x-if="!filePreviewUrl && (!mediaUrl || mediaSourceTab !== 'url')">
                                                    <div class="text-center p-6 space-y-2">
                                                        <div class="w-12 h-12 rounded-[14px] bg-black/5 dark:bg-white/10 flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                                                            <i data-lucide="image" class="w-6 h-6"></i>
                                                        </div>
                                                        <div class="text-[11.5px] font-medium text-black/50 dark:text-white/50">{{ __('social_media.no_photo_preview') }}</div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Carousel Preview --}}
                                        <template x-if="mediaFormat === 'carousel'">
                                            <div class="w-full h-full relative flex items-center justify-center">
                                                <template x-if="carouselItems.length > 0">
                                                    <div class="w-full h-full relative">
                                                        <template x-if="carouselItems[activePreviewSlide] && carouselItems[activePreviewSlide].mime.startsWith('image/')">
                                                            <img :src="carouselItems[activePreviewSlide].previewUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'">
                                                        </template>
                                                        <template x-if="carouselItems[activePreviewSlide] && carouselItems[activePreviewSlide].mime.startsWith('video/')">
                                                            <video :src="carouselItems[activePreviewSlide].previewUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'" controls></video>
                                                        </template>
                                                        {{-- Slide Counter Pill --}}
                                                        <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-black/70 backdrop-blur-md text-white text-[10px] font-bold">
                                                            <span x-text="(activePreviewSlide + 1) + ' / ' + carouselItems.length"></span>
                                                        </div>
                                                        {{-- Slide Nav Arrows --}}
                                                        <div x-show="carouselItems.length > 1" class="absolute inset-x-2 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none">
                                                            <button type="button" @click="activePreviewSlide = Math.max(0, activePreviewSlide - 1)"
                                                                :disabled="activePreviewSlide === 0"
                                                                class="w-7 h-7 rounded-full bg-black/60 text-white flex items-center justify-center pointer-events-auto disabled:opacity-20 transition-opacity">
                                                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                                            </button>
                                                            <button type="button" @click="activePreviewSlide = Math.min(carouselItems.length - 1, activePreviewSlide + 1)"
                                                                :disabled="activePreviewSlide === carouselItems.length - 1"
                                                                class="w-7 h-7 rounded-full bg-black/60 text-white flex items-center justify-center pointer-events-auto disabled:opacity-20 transition-opacity">
                                                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="carouselItems.length === 0">
                                                    <div class="text-center p-6 space-y-2">
                                                        <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 flex items-center justify-center mx-auto text-[#FF9500]">
                                                            <i data-lucide="layers" class="w-6 h-6"></i>
                                                        </div>
                                                        <div class="text-[11.5px] font-medium text-black/50 dark:text-white/50">{{ __('social_media.carousel_empty_title') }}</div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Video / Reels Preview --}}
                                        <template x-if="mediaFormat === 'video' || mediaFormat === 'reels'">
                                            <div class="w-full h-full flex items-center justify-center">
                                                <template x-if="filePreviewUrl">
                                                    <video :src="filePreviewUrl" controls class="w-full h-full bg-black" :class="mediaFitMode === 'contain' ? 'object-contain' : 'object-cover'"></video>
                                                </template>
                                                <template x-if="!filePreviewUrl && mediaSourceTab === 'url' && mediaUrl">
                                                    <video :src="mediaUrl" controls class="w-full h-full bg-black" :class="mediaFitMode === 'contain' ? 'object-contain' : 'object-cover'"></video>
                                                </template>
                                                <template x-if="!filePreviewUrl && (!mediaUrl || mediaSourceTab !== 'url')">
                                                    <div class="text-center p-6 space-y-2">
                                                        <div class="w-12 h-12 rounded-[14px] bg-black/5 dark:bg-white/10 flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                                                            <i data-lucide="play" class="w-6 h-6"></i>
                                                        </div>
                                                        <div class="text-[11.5px] font-medium text-black/50 dark:text-white/50" x-text="mediaFormat === 'reels' ? '{{ __('social_media.video_reels_preview_hint') }}' : '{{ __('social_media.video_promo_preview_hint') }}'"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Text Status Preview --}}
                                        <template x-if="mediaFormat === 'text'">
                                            <div class="w-full h-full flex items-center justify-center text-center p-6 rounded-[14px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white">
                                                <p class="text-[14px] font-bold leading-relaxed line-clamp-5" x-text="captionText || '{{ __('social_media.no_caption_preview') }}'"></p>
                                            </div>
                                        </template>

                                        {{-- Reels Floating Actions Overlay (9:16) --}}
                                        <div x-show="previewAspectRatio === '9:16'" class="absolute right-2.5 bottom-12 flex flex-col items-center gap-3 z-10 text-white drop-shadow-md pointer-events-none">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <div class="w-7 h-7 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center">
                                                    <i data-lucide="heart" class="w-3.5 h-3.5 text-white"></i>
                                                </div>
                                                <span class="text-[9.5px] font-bold">12.5k</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <div class="w-7 h-7 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center">
                                                    <i data-lucide="message-circle" class="w-3.5 h-3.5 text-white"></i>
                                                </div>
                                                <span class="text-[9.5px] font-bold">342</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <div class="w-7 h-7 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center">
                                                    <i data-lucide="send" class="w-3.5 h-3.5 text-white"></i>
                                                </div>
                                                <span class="text-[9.5px] font-bold">1.2k</span>
                                            </div>
                                            <div class="w-7 h-7 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center border border-white/30 animate-spin" style="animation-duration: 4s;">
                                                <i data-lucide="music" class="w-3 h-3 text-white"></i>
                                            </div>
                                        </div>

                                        {{-- Reels Bottom Overlay (9:16) --}}
                                        <div x-show="previewAspectRatio === '9:16'" class="absolute inset-x-0 bottom-0 p-3 pt-6 bg-gradient-to-t from-black/85 via-black/45 to-transparent text-white z-10 text-left space-y-1 pointer-events-none">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-5 h-5 rounded-full bg-[#007AFF] text-white font-bold text-[9px] flex items-center justify-center shrink-0">
                                                    {{ strtoupper(substr($business->name, 0, 1)) }}
                                                </div>
                                                <span class="text-[11px] font-bold truncate">{{ $business->name }}</span>
                                                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-white/20 border border-white/30 backdrop-blur-sm">{{ __('social_media.follow_badge') }}</span>
                                            </div>
                                            <p class="text-[10.5px] leading-snug line-clamp-2 text-white/95" x-text="captionText || '{{ __('social_media.no_caption_preview') }}'"></p>
                                            <div class="flex items-center gap-1 text-[9.5px] text-white/80">
                                                <i data-lucide="music-2" class="w-3 h-3 text-[#34C759]"></i>
                                                <span class="truncate">{{ __('social_media.reels_audio_original', ['business' => $business->name]) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Post Action Buttons Mockup (Feed mode: 4:5, 1:1, 16:9) --}}
                                    <div x-show="previewAspectRatio !== '9:16'" class="p-3 border-t border-black/5 dark:border-white/10 space-y-2">
                                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                            <div class="flex items-center gap-3">
                                                <i data-lucide="heart" class="w-4 h-4"></i>
                                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                                <i data-lucide="send" class="w-4 h-4"></i>
                                            </div>
                                            <i data-lucide="bookmark" class="w-4 h-4"></i>
                                        </div>

                                        {{-- Caption Preview --}}
                                        <div class="space-y-1 text-[12px] leading-relaxed">
                                            <div class="font-bold inline mr-1">{{ $business->name }}</div>
                                            <span class="text-black/80 dark:text-white/80 whitespace-pre-line" x-text="captionText ? (captionText.length > 180 ? captionText.slice(0, 180) + '...' : captionText) : '{{ __('social_media.no_caption_preview') }}'"></span>
                                        </div>

                                        {{-- Detected Hashtags Chips --}}
                                        <div x-show="uniqueHashtags.length > 0" class="flex flex-wrap gap-1 pt-1">
                                            <template x-for="tag in uniqueHashtags" :key="tag">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]" x-text="tag"></span>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Reels Footer Info (9:16) --}}
                                    <div x-show="previewAspectRatio === '9:16'" class="p-2.5 px-3 border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] text-black/60 dark:text-white/60 flex items-center justify-between">
                                        <span class="font-medium">9:16 Vertikal Penuh</span>
                                        <span class="text-[10px] font-mono font-bold text-[#AF52DE]">Reels / Stories / TikTok</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-black/5 dark:border-white/10">
                        <button type="button" @click="openComposerModal = false"
                            class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors flex items-center justify-center">
                            {{ __('social_media.cancel') }}
                        </button>
                        <button type="submit" :disabled="isSubmitDisabled"
                            class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                            <template x-if="!isSubmitting">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </template>
                            <template x-if="isSubmitting">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            </template>
                            <span x-text="isSubmitting ? '{{ __('social_media.uploading_btn') }}' : (scheduleMode !== 'all_now' ? '{{ __('social_media.save_schedule_btn') }}' : '{{ __('social_media.publish_now_btn') }}')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 6. POST PREVIEW & MANAGEMENT MODAL (Apple HIG Bento Bottom Sheet) --}}
        <div x-show="postManagerOpen" style="display:none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-5"
             @keydown.escape.window="postManagerOpen = false">

            <div class="relative w-full sm:max-w-2xl rounded-t-[28px] sm:rounded-[26px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col max-h-[92vh]"
                 @click.away="postManagerOpen = false">

                {{-- Drag handle (mobile) --}}
                <div class="flex justify-center pt-3 pb-1 sm:hidden">
                    <div class="w-10 h-1 rounded-full bg-black/20 dark:bg-white/20"></div>
                </div>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[14px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                            <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.preview_manage_title') }}</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[12px] text-black/50 dark:text-white/50" x-text="@js(__('social_media.account_prefix')) + ': ' + (pmPost.account_name || '-')"></span>
                                <span class="text-black/30 dark:text-white/30">•</span>
                                <span class="text-[11.5px] uppercase font-bold text-[#5856D6]" x-text="pmPost.platform || '-'"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="postManagerOpen = false" class="w-9 h-9 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="overflow-y-auto flex-1 p-6 space-y-5">

                    {{-- Status Banner & Scheduled Time --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40">{{ __('social_media.filter_status_label') }}:</span>
                            <span x-text="pmPost.status === 'scheduled' ? @js(__('social_media.status_label_scheduled')) : (pmPost.status === 'pending' ? @js(__('social_media.status_label_pending')) : (pmPost.status === 'failed' ? @js(__('social_media.status_label_failed')) : @js(__('social_media.status_label_partially_failed'))))"
                                  class="px-2.5 py-1 rounded-full text-[12px] font-bold"
                                  :class="pmPost.status === 'scheduled' ? 'bg-[#5856D6]/15 text-[#5856D6]' : (pmPost.status === 'pending' ? 'bg-[#007AFF]/15 text-[#007AFF]' : 'bg-[#FF3B30]/15 text-[#FF3B30]')"></span>
                        </div>
                        <div x-show="pmPost.scheduled_at_label && pmPost.scheduled_at_label !== '-'" class="flex items-center gap-1.5 text-[12px] font-semibold text-black/60 dark:text-white/60">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                            <span x-text="pmPost.scheduled_at_label"></span>
                        </div>
                    </div>

                    {{-- Media Preview (Image / Video / Carousel) --}}
                    <template x-if="pmPost.media_urls && pmPost.media_urls.length > 0">
                        <div class="space-y-2.5">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40 flex items-center justify-between">
                                <span>{{ __('social_media.media_preview_title') }}</span>
                                <span class="font-normal lowercase" x-text="pmPost.media_urls.length + ' ' + @js(__('social_media.media_files_count'))"></span>
                            </div>
                            <div class="rounded-[18px] overflow-hidden border border-black/10 dark:border-white/10 bg-black aspect-video max-h-72 flex items-center justify-center relative shadow-inner">
                                <template x-if="pmPost.media_type === 'video' || pmPost.media_type === 'reels'">
                                    <video :src="pmPost.media_urls[pmActiveSlide]" controls preload="metadata" class="w-full h-full object-contain"></video>
                                </template>
                                <template x-if="pmPost.media_type !== 'video' && pmPost.media_type !== 'reels'">
                                    <img :src="pmPost.media_urls[pmActiveSlide]" alt="Media Preview" class="w-full h-full object-cover"
                                         onerror="this.src='https://placehold.co/600x400/1C1C1E/FFF?text=Preview'">
                                </template>
                            </div>
                            {{-- Carousel Thumbnail Strip --}}
                            <template x-if="pmPost.media_urls.length > 1">
                                <div class="flex items-center gap-2 overflow-x-auto py-1">
                                    <template x-for="(url, idx) in pmPost.media_urls" :key="idx">
                                        <button type="button" @click="pmActiveSlide = idx"
                                                :class="pmActiveSlide === idx ? 'ring-2 ring-[#007AFF] scale-105' : 'opacity-60 hover:opacity-100'"
                                                class="w-12 h-12 rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 shrink-0 transition-all cursor-pointer">
                                            <img :src="url" class="w-full h-full object-cover">
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- No Media Alert (Text-only) --}}
                    <template x-if="!pmPost.media_urls || pmPost.media_urls.length === 0">
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex items-center gap-2.5 text-[12px] text-black/60 dark:text-white/60">
                            <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span>{{ __('social_media.text_only_badge_desc') }}</span>
                        </div>
                    </template>

                    {{-- Caption Content --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40">
                            <span>{{ __('social_media.content_and_caption_label') }}</span>
                            <span class="font-normal" x-text="(pmPost.content ? pmPost.content.length : 0) + ' ' + @js(__('social_media.characters_unit'))"></span>
                        </div>
                        <div class="rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 p-4 text-[13.5px] text-black/85 dark:text-white/85 leading-relaxed whitespace-pre-wrap max-h-48 overflow-y-auto" x-text="pmPost.content"></div>
                    </div>

                    {{-- JADWAL ULANG FORM --}}
                    <div x-show="pmTab === 'reschedule'" class="space-y-3 pt-1">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#5856D6]">{{ __('social_media.select_new_datetime') }}</div>
                        <div class="space-y-2">
                            <input type="datetime-local" x-model="pmNewScheduledAt"
                                   :min="minScheduleTime"
                                   class="w-full h-11 px-4 rounded-[12px] border border-black/15 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#5856D6]/40 shadow-xs">
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 leading-relaxed">{{ __('social_media.new_schedule_sync_info') }}</p>
                        </div>
                    </div>

                    {{-- Feedback Message --}}
                    <div x-show="pmMessage" x-cloak
                         :class="pmSuccess ? 'bg-[#34C759]/10 border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/10 border-[#FF3B30]/25 text-[#FF3B30]'"
                         class="rounded-[12px] border p-3 text-[12.5px] font-semibold flex items-center gap-2">
                         <span x-show="pmSuccess" class="inline-flex"><i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i></span>
                         <span x-show="!pmSuccess" class="inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i></span>
                         <span x-text="pmMessage"></span>
                    </div>
                </div>

                {{-- Action Tabs + Buttons --}}
                <div class="border-t border-black/5 dark:border-white/10 px-6 py-4 space-y-3 bg-white dark:bg-[#1C1C1E] rounded-b-[26px]">

                    {{-- Tab switcher --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="pmTab = 'preview'"
                                :class="pmTab === 'preview' ? 'bg-black text-white dark:bg-white dark:text-black shadow-xs' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 hover:bg-black/10'"
                                class="min-h-[44px] sm:min-h-0 sm:h-8 px-4 sm:px-3.5 rounded-full text-[12px] font-semibold transition-colors cursor-pointer inline-flex items-center justify-center">{{ __('social_media.preview_content_tab') }}</button>
                        <button type="button" @click="pmTab = 'reschedule'"
                                :class="pmTab === 'reschedule' ? 'bg-[#5856D6] text-white shadow-xs' : 'bg-[#5856D6]/10 text-[#5856D6] hover:bg-[#5856D6]/20'"
                                class="min-h-[44px] sm:min-h-0 sm:h-8 px-4 sm:px-3.5 rounded-full text-[12px] font-semibold transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            <span>{{ __('social_media.reschedule_tab') }}</span>
                        </button>
                    </div>

                    {{-- Primary actions --}}
                    <div class="flex items-center gap-2">

                        {{-- Reschedule confirm (only when on reschedule tab) --}}
                        <button type="button" x-show="pmTab === 'reschedule'" x-cloak
                                @click="doReschedule()"
                                :disabled="pmLoading || !pmNewScheduledAt"
                                class="flex-1 min-h-[44px] sm:min-h-0 sm:h-10 rounded-[12px] text-[13px] font-bold text-white bg-[#5856D6] hover:bg-[#4F4EC2] active:scale-[0.97] transition-all disabled:opacity-50 inline-flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="pmLoading" x-cloak></i>
                            <i data-lucide="save" class="w-4 h-4" x-show="!pmLoading"></i>
                            <span>{{ __('social_media.save_new_schedule_btn') }}</span>
                        </button>

                        {{-- Publish Now --}}
                        <button type="button" x-show="pmTab === 'preview'" x-cloak
                                @click="doPublishNow()"
                                :disabled="pmLoading"
                                class="flex-1 min-h-[44px] sm:min-h-0 sm:h-10 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2FB34F] active:scale-[0.97] transition-all disabled:opacity-50 inline-flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="pmLoading" x-cloak></i>
                            <i data-lucide="send" class="w-4 h-4" x-show="!pmLoading"></i>
                            <span>{{ __('social_media.publish_now_btn') }}</span>
                        </button>

                        {{-- Delete --}}
                        <button type="button" x-show="pmTab === 'preview'" x-cloak
                                @click="doDelete()"
                                :disabled="pmLoading"
                                class="min-h-[44px] sm:min-h-0 sm:h-10 px-4 rounded-[12px] text-[13px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 active:scale-[0.97] transition-all disabled:opacity-50 inline-flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>{{ __('social_media.delete_post_btn') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function socialPostsManager() {
            return {
                openComposerModal: false,
                allAccountIds: [
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}',
                    @endforeach
                ],
                selectedAccounts: [
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}',
                    @endforeach
                ],
                accountsMap: {
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}': {
                            id: '{{ $acc->id }}',
                            platform: '{{ $acc->platform }}',
                            name: '{{ addslashes($acc->account_name) }}',
                            limit: {{ \App\Domain\SocialMedia\Validation\SocialMediaContentValidator::CAPTION_LIMITS[$acc->platform] ?? 2200 }},
                        },
                    @endforeach
                },
                customCaptions: {
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}': '',
                    @endforeach
                },
                mediaFormat: 'photo',
                mediaSourceTab: 'upload',
                captionText: '',
                mediaUrl: '',
                scheduleMode: 'all_now',
                globalScheduleTime: '',
                channelTiming: {
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}': 'now',
                    @endforeach
                },
                channelScheduledAts: {
                    @foreach($accounts as $acc)
                        '{{ $acc->id }}': '',
                    @endforeach
                },
                showOverrides: false,
                filePreviewUrl: null,
                fileName: '',
                fileSize: '',
                fileMime: '',
                carouselItems: [],
                activePreviewSlide: 0,
                videoWidth: null,
                videoHeight: null,
                videoRatio: null,
                isLandscapeVideo: false,
                isSubmitting: false,

                // Aspect Ratio & Preview Settings
                previewAspectRatio: '4:5',
                mediaFitMode: 'cover',

                // ── Post Preview & Manager Modal ──
                postManagerOpen: false,
                pmPost: {},
                pmTab: 'preview',
                pmNewScheduledAt: '',
                pmActiveSlide: 0,
                pmLoading: false,
                pmMessage: '',
                pmSuccess: false,

                get minScheduleTime() {
                    const d = new Date(Date.now() + 60000);
                    const pad = (n) => String(n).padStart(2, '0');
                    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                },

                openPostManager(post) {
                    this.pmPost = post;
                    this.pmTab = 'preview';
                    this.pmNewScheduledAt = post.scheduled_at_raw || '';
                    this.pmActiveSlide = 0;
                    this.pmMessage = '';
                    this.pmSuccess = false;
                    this.pmLoading = false;
                    this.postManagerOpen = true;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                },

                async doReschedule() {
                    if (!this.pmNewScheduledAt) return;
                    this.pmLoading = true;
                    this.pmMessage = '';
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const res = await fetch(this.pmPost.reschedule_url, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify({ scheduled_at: this.pmNewScheduledAt }),
                        });
                        const data = await res.json();
                        this.pmSuccess = data.success;
                        this.pmMessage = data.success ? (data.message + ' — ' + (data.scheduled_at || '')) : (data.message || data.error || @js(__('social_media.error_occurred')));
                        if (data.success) {
                            this.pmPost.scheduled_at_label = data.scheduled_at;
                            this.pmPost.status = 'scheduled';
                            if (window.AppAlert) {
                                AppAlert.success(data.message || @js(__('social_media.reschedule_success')));
                            }
                            setTimeout(() => { this.postManagerOpen = false; }, 800);
                        }
                    } catch (e) {
                        this.pmSuccess = false;
                        this.pmMessage = @js(__('social_media.connection_error_try_again'));
                    } finally {
                        this.pmLoading = false;
                        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                    }
                },

                async doPublishNow() {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: @js(__('social_media.confirm_publish_now_title')),
                            message: @js(__('social_media.confirm_publish_now_msg')),
                            type: 'info',
                            confirmText: @js(__('social_media.publish_now_btn')),
                            cancelText: @js(__('social_media.cancel')),
                        });
                    } else {
                        confirmed = confirm(@js(__('social_media.confirm_publish_now_msg')));
                    }
                    if (!confirmed) return;

                    this.pmLoading = true;
                    this.pmMessage = '';
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const res = await fetch(this.pmPost.publish_now_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        this.pmSuccess = data.success;
                        this.pmMessage = data.success ? @js(__('social_media.publish_now_dispatched')) : (data.error || @js(__('social_media.failed_to_publish')));
                        if (data.success) {
                            if (window.AppAlert) {
                                AppAlert.success(data.message || @js(__('social_media.publish_now_dispatched')));
                            }
                            this.pmPost.status = 'publishing';
                            setTimeout(() => { this.postManagerOpen = false; }, 800);
                        }
                    } catch (e) {
                        this.pmSuccess = false;
                        this.pmMessage = @js(__('social_media.connection_error_try_again'));
                    } finally {
                        this.pmLoading = false;
                        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                    }
                },

                async doDelete() {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: @js(__('social_media.confirm_delete_post_title')),
                            message: @js(__('social_media.confirm_delete_post_msg')),
                            type: 'danger',
                            confirmText: @js(__('social_media.delete_post_btn')),
                            cancelText: @js(__('social_media.cancel')),
                        });
                    } else {
                        confirmed = confirm(@js(__('social_media.confirm_delete_post_msg')));
                    }
                    if (!confirmed) return;

                    this.pmLoading = true;
                    this.pmMessage = '';
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const res = await fetch(this.pmPost.destroy_url, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        this.pmSuccess = data.success;
                        this.pmMessage = data.success ? @js(__('social_media.destroy_success')) : (data.error || data.message || @js(__('social_media.failed_to_delete')));
                        if (data.success) {
                            if (window.AppAlert) {
                                AppAlert.success(data.message || @js(__('social_media.destroy_success')));
                            }
                            const card = document.getElementById(`post-card-${this.pmPost.id}`);
                            if (card) {
                                card.classList.add('opacity-0', 'scale-95', 'transition-all', 'duration-300');
                                setTimeout(() => card.remove(), 300);
                            }
                            this.postManagerOpen = false;
                        }
                    } catch (e) {
                        this.pmSuccess = false;
                        this.pmMessage = @js(__('social_media.connection_error_try_again'));
                    } finally {
                        this.pmLoading = false;
                        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                    }
                },

                get uniqueHashtags() {
                    if (!this.captionText) return [];
                    const matches = this.captionText.match(/#[a-zA-Z0-9_\u0080-\uFFFF]+/gu) || [];
                    const unique = new Set(matches.map(t => t.toLowerCase()));
                    return Array.from(unique);
                },

                get hashtagCount() {
                    return this.uniqueHashtags.length;
                },

                get hasThreadsSelected() {
                    return this.selectedAccounts.some(id => this.accountsMap[id]?.platform === 'threads');
                },

                get threadsAccountId() {
                    const accId = this.selectedAccounts.find(id => this.accountsMap[id]?.platform === 'threads');
                    return accId || null;
                },

                getEffectiveCaption(accId) {
                    const custom = this.customCaptions[accId];
                    if (custom && custom.trim().length > 0) {
                        return custom.trim();
                    }
                    return this.captionText.trim();
                },

                getCaptionLength(accId) {
                    const custom = this.customCaptions[accId];
                    if (custom && custom.trim().length > 0) {
                        return custom.length;
                    }
                    return this.captionText.length;
                },

                getChannelHashtags(accId) {
                    const text = this.getEffectiveCaption(accId);
                    if (!text) return [];
                    const matches = text.match(/#[a-zA-Z0-9_\u0080-\uFFFF]+/gu) || [];
                    const unique = new Set(matches.map(t => t.toLowerCase()));
                    return Array.from(unique);
                },

                get isThreadsOverLimit() {
                    if (!this.hasThreadsSelected) return false;
                    const threadsId = this.threadsAccountId;
                    if (!threadsId) return false;
                    return this.getCaptionLength(threadsId) > 500;
                },

                get isThreadsSeparateActive() {
                    if (!this.hasThreadsSelected) return false;
                    const threadsId = this.threadsAccountId;
                    if (!threadsId) return false;
                    const custom = this.customCaptions[threadsId];
                    return custom && custom.trim().length > 0 && custom.length <= 500;
                },

                copyFromMain(accId) {
                    this.customCaptions[accId] = this.captionText;
                },

                resetCustomCaption(accId) {
                    this.customCaptions[accId] = '';
                },

                createSeparateThreadsCaption() {
                    const threadsId = this.threadsAccountId;
                    if (!threadsId) return;

                    this.showOverrides = true;
                    if (!this.customCaptions[threadsId] || this.customCaptions[threadsId].trim() === '') {
                        this.customCaptions[threadsId] = this.captionText.slice(0, 500);
                    }

                    this.$nextTick(() => {
                        const el = document.getElementById('custom_caption_' + threadsId);
                        if (el) {
                            el.focus();
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                },

                get hasAnyChannelOverLimit() {
                    for (const accId of this.selectedAccounts) {
                        const acc = this.accountsMap[accId];
                        if (!acc) continue;
                        const len = this.getCaptionLength(accId);
                        const limit = acc.limit || 2200;
                        if (len > limit) return true;
                        if (this.getChannelHashtags(accId).length > 5) return true;
                    }
                    return false;
                },

                get isSubmitDisabled() {
                    if (!this.captionText.trim()) return true;
                    if (this.selectedAccounts.length === 0) return true;
                    if (this.hashtagCount > 5) return true;
                    if (this.hasAnyChannelOverLimit) return true;
                    if (this.isSubmitting) return true;
                    return false;
                },

                get isQuietHours() {
                    let hour = null;
                    if (this.scheduleMode === 'all_same' && this.globalScheduleTime) {
                        hour = new Date(this.globalScheduleTime).getHours();
                    } else if (this.scheduleMode === 'all_now') {
                        hour = new Date().getHours();
                    } else if (this.scheduleMode === 'per_channel') {
                        for (const accId of this.selectedAccounts) {
                            if (this.channelTiming[accId] === 'schedule' && this.channelScheduledAts[accId]) {
                                const h = new Date(this.channelScheduledAts[accId]).getHours();
                                if (h >= 22 || h < 6) return true;
                            }
                        }
                        return false;
                    }
                    return hour !== null && (hour >= 22 || hour < 6);
                },

                get activePlatformNames() {
                    const names = [];
                    @foreach($accounts as $acc)
                        if (this.selectedAccounts.includes('{{ $acc->id }}')) {
                            names.push('{{ ucfirst($acc->platform) }}');
                        }
                    @endforeach
                    return names;
                },

                toggleSelectAll() {
                    if (this.selectedAccounts.length === this.allAccountIds.length) {
                        this.selectedAccounts = [];
                    } else {
                        this.selectedAccounts = [...this.allAccountIds];
                    }
                },

                getPreviewCardWidthClass() {
                    if (this.previewAspectRatio === '9:16') {
                        return 'w-full max-w-[320px] mx-auto';
                    }
                    if (this.previewAspectRatio === '16:9') {
                        return 'w-full max-w-[420px] mx-auto';
                    }
                    // default 4:5, 1:1, or text
                    return 'w-full max-w-[380px] mx-auto';
                },

                getPreviewAspectClass() {
                    if (this.mediaFormat === 'text') return 'min-h-[200px] p-4';
                    if (this.previewAspectRatio === '9:16') {
                        return 'aspect-[9/16]';
                    }
                    if (this.previewAspectRatio === '1:1') {
                        return 'aspect-square';
                    }
                    if (this.previewAspectRatio === '16:9') {
                        return 'aspect-[16/9]';
                    }
                    // default 4:5 (IG Feed portrait)
                    return 'aspect-[4/5]';
                },

                getAspectRatioDescription() {
                    switch (this.previewAspectRatio) {
                        case '4:5': return @js(__('social_media.aspect_ratio_desc_4_5'));
                        case '1:1': return @js(__('social_media.aspect_ratio_desc_1_1'));
                        case '9:16': return @js(__('social_media.aspect_ratio_desc_9_16'));
                        case '16:9': return @js(__('social_media.aspect_ratio_desc_16_9'));
                        default: return '';
                    }
                },

                setMediaFormat(fmt) {
                    this.mediaFormat = fmt;
                    if (fmt === 'text') {
                        this.clearFile();
                        this.carouselItems = [];
                        this.mediaUrl = '';
                        this.previewAspectRatio = 'text';
                    } else if (fmt === 'reels') {
                        this.previewAspectRatio = '9:16';
                    } else if (fmt === 'photo' || fmt === 'carousel') {
                        if (this.previewAspectRatio === 'text' || this.previewAspectRatio === '9:16') {
                            this.previewAspectRatio = '4:5';
                        }
                    } else if (fmt === 'video') {
                        if (this.isLandscapeVideo) {
                            this.previewAspectRatio = '16:9';
                        } else if (this.videoRatio && this.videoRatio < 0.75) {
                            this.previewAspectRatio = '9:16';
                        } else {
                            this.previewAspectRatio = '16:9';
                        }
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.fileName = file.name;
                    this.fileMime = file.type;
                    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    this.fileSize = sizeInMB >= 1 ? sizeInMB + ' MB' : Math.round(file.size / 1024) + ' KB';

                    if (this.filePreviewUrl) {
                        URL.revokeObjectURL(this.filePreviewUrl);
                    }
                    this.filePreviewUrl = URL.createObjectURL(file);

                    // Reset video inspection metadata
                    this.videoWidth = null;
                    this.videoHeight = null;
                    this.videoRatio = null;
                    this.isLandscapeVideo = false;

                    if (file.type.startsWith('video/')) {
                        const vid = document.createElement('video');
                        vid.preload = 'metadata';
                        vid.src = this.filePreviewUrl;
                        vid.onloadedmetadata = () => {
                            this.videoWidth = vid.videoWidth;
                            this.videoHeight = vid.videoHeight;
                            if (vid.videoHeight > 0) {
                                const r = vid.videoWidth / vid.videoHeight;
                                this.videoRatio = r.toFixed(2);
                                this.isLandscapeVideo = vid.videoWidth > vid.videoHeight;
                                if (r >= 1.2) {
                                    this.previewAspectRatio = '16:9';
                                } else if (r <= 0.65) {
                                    this.previewAspectRatio = '9:16';
                                } else if (r <= 0.85) {
                                    this.previewAspectRatio = '4:5';
                                } else {
                                    this.previewAspectRatio = '1:1';
                                }
                            }
                        };
                    } else if (file.type.startsWith('image/')) {
                        const img = new Image();
                        img.src = this.filePreviewUrl;
                        img.onload = () => {
                            if (img.height > 0) {
                                const r = img.width / img.height;
                                if (r >= 1.35) {
                                    this.previewAspectRatio = '16:9';
                                } else if (r <= 0.65) {
                                    this.previewAspectRatio = '9:16';
                                } else if (r <= 0.85) {
                                    this.previewAspectRatio = '4:5';
                                } else if (r >= 0.95 && r <= 1.05) {
                                    this.previewAspectRatio = '1:1';
                                } else {
                                    this.previewAspectRatio = '4:5';
                                }
                            }
                        };
                    }

                    // Auto-sync format if user chose video while in photo mode
                    if (file.type.startsWith('video/') && this.mediaFormat === 'photo') {
                        this.mediaFormat = 'video';
                    } else if (file.type.startsWith('image/') && (this.mediaFormat === 'video' || this.mediaFormat === 'reels')) {
                        this.mediaFormat = 'photo';
                    }

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                handleCarouselFilesSelect(event) {
                    const files = Array.from(event.target.files);
                    if (!files.length) return;

                    for (const file of files) {
                        if (this.carouselItems.length >= 10) break;
                        this.carouselItems.push({
                            file: file,
                            name: file.name,
                            mime: file.type,
                            previewUrl: URL.createObjectURL(file)
                        });
                    }

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                moveCarouselItem(index, direction) {
                    const targetIndex = index + direction;
                    if (targetIndex < 0 || targetIndex >= this.carouselItems.length) return;
                    const temp = this.carouselItems[index];
                    this.carouselItems[index] = this.carouselItems[targetIndex];
                    this.carouselItems[targetIndex] = temp;
                },

                removeCarouselItem(index) {
                    const item = this.carouselItems[index];
                    if (item && item.previewUrl) {
                        URL.revokeObjectURL(item.previewUrl);
                    }
                    this.carouselItems.splice(index, 1);
                    if (this.activePreviewSlide >= this.carouselItems.length) {
                        this.activePreviewSlide = Math.max(0, this.carouselItems.length - 1);
                    }
                },

                clearFile() {
                    if (this.filePreviewUrl) {
                        URL.revokeObjectURL(this.filePreviewUrl);
                    }
                    this.filePreviewUrl = null;
                    this.fileName = '';
                    this.fileSize = '';
                    this.fileMime = '';
                    this.videoWidth = null;
                    this.videoHeight = null;
                    this.videoRatio = null;
                    this.isLandscapeVideo = false;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            };
        }
    </script>
@endsection
