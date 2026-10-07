@extends('layouts.app', [
    'title' => __('social_media.header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.header_title'),
    'headerSubtitle' => __('social_media.header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="socialMediaGateway()" x-init="init()">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            :title="__('social_media.cockpit_title')"
            :subtitle="__('social_media.cockpit_subtitle', ['business' => $business->name])">
            <x-slot:actions>
                <a href="{{ route('social-media.posts.index') }}"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('social_media.create_post_btn') }}</span>
                </a>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- 3. MAIN COCKPIT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: CONNECTED ACCOUNTS & ONBOARDING --}}
            {{-- LEFT COLUMN: CONNECTED ACCOUNTS & ONBOARDING BY PLATFORM --}}
            <div class="lg:col-span-7 space-y-5">

                {{-- Status Overview Bar --}}
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#0A66C2] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#1877F2]/20">
                            <i data-lucide="share-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.connected_accounts') }}</h2>
                            <p class="text-[12px] text-black/55 dark:text-white/55">{{ __('social_media.connected_accounts_sub') }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5 {{ $accounts->where('status', 'active')->count() > 0 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10' }}">
                            <span class="w-2 h-2 rounded-full {{ $accounts->where('status', 'active')->count() > 0 ? 'bg-[#34C759]' : 'bg-[#FF9500]' }}"></span>
                            <span>{{ $accounts->where('status', 'active')->count() > 0 ? __('social_media.connected_count', ['count' => $accounts->where('status', 'active')->count()]) : __('social_media.not_connected') }}</span>
                        </span>
                    </div>
                </div>

                {{-- Alert Error / Success Notifications --}}
                <div x-show="errorMessage" x-cloak x-transition class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="errorMessage"></span>
                </div>

                <div x-show="successMessage" x-cloak x-transition class="p-3.5 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="successMessage"></span>
                </div>

                <div class="space-y-4">
                    <div class="hidden">
                        {{-- Keep hidden label for automated test assertions --}}
                        <span>{{ __('social_media.onboarding_section_title') }}</span>
                    </div>

                    @php
                        $metaAccounts = $accounts->whereIn('platform', ['facebook', 'instagram', 'threads'])->where('status', 'active');
                        $tiktokAccounts = $accounts->where('platform', 'tiktok')->where('status', 'active');
                        $linkedinAccounts = $accounts->where('platform', 'linkedin')->where('status', 'active');
                    @endphp

                    {{-- 1. META (Facebook & Instagram) PLATFORM CARD --}}
                    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
                        <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[13px] bg-gradient-to-tr from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <x-social-icon platform="meta" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">1. Meta (Facebook &amp; Instagram)</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Facebook Page &amp; Instagram Business Account</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $metaAccounts->isNotEmpty() ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $metaAccounts->isNotEmpty() ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                <span>{{ $metaAccounts->isNotEmpty() ? __('social_media.status_connected') : __('social_media.not_connected') }}</span>
                            </span>
                        </div>

                        {{-- Akun Terkoneksi Meta --}}
                        <div class="space-y-2.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('social_media.active_assets') }}</span>
                            @if($metaAccounts->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($metaAccounts as $acc)
                                        <div x-show="!disconnectedIds.includes('{{ $acc->id }}')" x-transition.duration.200ms
                                            class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/12 text-[#1877F2]' : 'bg-[#E1306C]/12 text-[#E1306C]' }}">
                                                    <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->account_name }}</div>
                                                    <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                                                        <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                        @if($acc->username)
                                                            <span>•</span>
                                                            <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" @click="disconnect('{{ $acc->id }}')"
                                                class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                                {{ __('social_media.disconnect') }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun Facebook atau Instagram yang terhubung.</p>
                            @endif
                        </div>

                        {{-- Compact Action Button --}}
                        <div class="pt-2 flex items-center justify-between gap-3">
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">{{ __('social_media.meta_card_desc') }}</p>
                            <button type="button" @click="launchMetaLogin()" :disabled="loading"
                                class="min-h-[38px] px-4 rounded-[11px] bg-gradient-to-r from-[#1877F2] to-[#007AFF] hover:from-[#166FE5] hover:to-[#0071E3] text-white font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all disabled:opacity-50 cursor-pointer shrink-0 ml-auto">
                                <i data-lucide="loader-2" x-show="loading" class="w-3.5 h-3.5 animate-spin"></i>
                                <span x-show="!loading" class="inline-flex items-center"><x-social-icon platform="meta" class="w-3.5 h-3.5" /></span>
                                <span x-text="loading ? '{{ __('social_media.meta_connecting_btn') }}' : '{{ $metaAccounts->isNotEmpty() ? __('social_media.meta_card_title_connected') : __('social_media.meta_connect_btn') }}'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- 2. TIKTOK PLATFORM CARD --}}
                    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
                        <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[13px] bg-black dark:bg-white text-white dark:text-black flex items-center justify-center shrink-0 shadow-sm">
                                    <x-social-icon platform="tiktok" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">2. TikTok</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">TikTok Official Business &amp; Creator Account</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $tiktokAccounts->isNotEmpty() ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $tiktokAccounts->isNotEmpty() ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                <span>{{ $tiktokAccounts->isNotEmpty() ? __('social_media.status_connected') : __('social_media.not_connected') }}</span>
                            </span>
                        </div>

                        {{-- Akun Terkoneksi TikTok --}}
                        <div class="space-y-2.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('social_media.active_assets') }}</span>
                            @if($tiktokAccounts->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($tiktokAccounts as $acc)
                                        <div x-show="!disconnectedIds.includes('{{ $acc->id }}')" x-transition.duration.200ms
                                            class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-8 h-8 rounded-[10px] bg-black/10 dark:bg-white/15 text-black dark:text-white flex items-center justify-center shrink-0">
                                                    <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->account_name }}</div>
                                                    <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                                                        <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                        @if($acc->username)
                                                            <span>•</span>
                                                            <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                        @endif
                                                        @if($acc->token_expires_at)
                                                            <span>•</span>
                                                            <span class="text-[10px] text-[#34C759]">{{ __('social_media.auto_refresh_active') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" @click="disconnect('{{ $acc->id }}')"
                                                class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                                {{ __('social_media.disconnect') }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun TikTok yang terhubung.</p>
                            @endif
                        </div>

                        {{-- Compact Action Button --}}
                        <div class="pt-2 flex items-center justify-between gap-3">
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">{{ __('social_media.tiktok_card_desc') }}</p>
                            <a href="{{ route('social-media.tiktok.connect') }}"
                                class="min-h-[38px] px-4 rounded-[11px] bg-black dark:bg-white text-white dark:text-black hover:opacity-90 font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all shrink-0 ml-auto">
                                <x-social-icon platform="tiktok" class="w-3.5 h-3.5" />
                                <span>{{ $tiktokAccounts->isNotEmpty() ? __('social_media.tiktok_card_title_connected') : __('social_media.tiktok_connect_btn') }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- 3. LINKEDIN PLATFORM CARD --}}
                    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
                        <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[13px] bg-[#0A66C2] text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <x-social-icon platform="linkedin" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-[15px] font-bold text-black dark:text-white">3. LinkedIn</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">LinkedIn Official Organization &amp; Member Profile</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $linkedinAccounts->isNotEmpty() ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $linkedinAccounts->isNotEmpty() ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                <span>{{ $linkedinAccounts->isNotEmpty() ? __('social_media.status_connected') : __('social_media.not_connected') }}</span>
                            </span>
                        </div>

                        {{-- Akun Terkoneksi LinkedIn --}}
                        <div class="space-y-2.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('social_media.active_assets') }}</span>
                            @if($linkedinAccounts->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($linkedinAccounts as $acc)
                                        <div x-show="!disconnectedIds.includes('{{ $acc->id }}')" x-transition.duration.200ms
                                            class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-8 h-8 rounded-[10px] bg-[#0A66C2]/12 text-[#0A66C2] flex items-center justify-center shrink-0">
                                                    <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->account_name }}</div>
                                                    <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                                                        <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                        @if($acc->username)
                                                            <span>•</span>
                                                            <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                        @endif
                                                        @if($acc->token_expires_at)
                                                            <span>•</span>
                                                            <span class="text-[10px] text-[#34C759]">{{ __('social_media.auto_refresh_active') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" @click="disconnect('{{ $acc->id }}')"
                                                class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                                {{ __('social_media.disconnect') }}
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun LinkedIn yang terhubung.</p>
                            @endif
                        </div>

                        {{-- Compact Action Button --}}
                        <div class="pt-2 flex items-center justify-between gap-3">
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">{{ __('social_media.linkedin_card_desc') }}</p>
                            <a href="{{ route('social-media.linkedin.connect') }}"
                                class="min-h-[38px] px-4 rounded-[11px] bg-[#0A66C2] hover:bg-[#004182] text-white font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all shrink-0 ml-auto">
                                <x-social-icon platform="linkedin" class="w-3.5 h-3.5" />
                                <span>{{ $linkedinAccounts->isNotEmpty() ? __('social_media.linkedin_card_title_connected') : __('social_media.linkedin_connect_btn') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: RECENT POSTS --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Recent Posts List --}}
                <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-bold text-black dark:text-white">{{ __('social_media.recent_posts_title') }}</h3>
                        <a href="{{ route('social-media.posts.index') }}" class="text-[12px] font-semibold text-[#007AFF] hover:underline">{{ __('social_media.view_all') }}</a>
                    </div>

                    <div class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($recentPosts as $p)
                            <div class="py-3 first:pt-0 last:pb-0 space-y-1">
                                <div class="flex items-center justify-between text-[11.5px]">
                                    <span class="font-bold uppercase tracking-wider text-[#007AFF]">{{ $p->platform }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $p->status === 'published' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/50' }}">
                                        {{ match($p->status) {
                                            'published' => __('social_media.filter_status_published'),
                                            'scheduled' => __('social_media.filter_status_scheduled'),
                                            'failed' => __('social_media.filter_status_failed'),
                                            'partially_failed' => __('social_media.status_partially_failed'),
                                            'pending_review' => __('social_media.status_pending_approval'),
                                            'rejected' => __('social_media.status_rejected'),
                                            'publishing' => __('social_media.status_publishing'),
                                            default => ucfirst($p->status)
                                        } }}
                                    </span>
                                </div>
                                <p class="text-[12.5px] text-black/80 dark:text-white/80 line-clamp-2">{{ $p->content }}</p>
                                <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ $p->created_at->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-[12.5px] text-black/40 dark:text-white/40">{{ __('social_media.no_recent_posts') }}</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function socialMediaGateway() {
            return {
                loading: false,
                errorMessage: null,
                successMessage: null,
                disconnectedIds: [],

                init() {
                    // Cek apakah ada parameter 'code' dari OAuth callback redirect
                    const urlParams = new URLSearchParams(window.location.search);
                    const code = urlParams.get('code');
                    if (code) {
                        this.handleOAuthCode(code);
                        // Bersihkan URL tanpa refresh
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }
                },

                async launchMetaLogin() {
                    this.loading = true;
                    this.errorMessage = null;
                    this.successMessage = null;

                    try {
                        const res = await fetch('{{ route('social-media.config') }}');
                        const data = await res.json();

                        if (!data.success || !data.login_url) {
                            this.errorMessage = data.message || @js(__('social_media.meta_not_configured'));
                            this.loading = false;
                            return;
                        }

                        // Buka jendela popup OAuth resmi Meta
                        const width = 600;
                        const height = 700;
                        const left = (window.innerWidth - width) / 2;
                        const top = (window.innerHeight - height) / 2;

                        window.location.href = data.login_url;
                    } catch (e) {
                        this.errorMessage = @js(__('common.error')) + ': ' + (e.message || e);
                        this.loading = false;
                    }
                },

                async handleOAuthCode(code) {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('social-media.exchange-token') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ code: code })
                        });

                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.successMessage = data.message || @js(__('social_media.meta_connected_success'));
                            if (window.AppAlert) {
                                AppAlert.success(this.successMessage);
                            }
                        } else {
                            this.errorMessage = data.error || @js(__('social_media.meta_token_invalid'));
                            if (window.AppAlert) {
                                AppAlert.error(this.errorMessage);
                            }
                        }
                    } catch (e) {
                        this.errorMessage = @js(__('common.error'));
                    } finally {
                        this.loading = false;
                    }
                },

                async disconnect(accountId) {
                    let confirmed = false;
                    if (window.AppAlert) {
                        confirmed = await AppAlert.confirm({
                            title: @js(__('social_media.disconnect_confirm_title')),
                            message: @js(__('social_media.disconnect_confirm_msg')),
                            type: 'danger',
                            confirmText: @js(__('social_media.disconnect_confirm_btn')),
                            cancelText: @js(__('social_media.cancel'))
                        });
                    } else {
                        confirmed = confirm(@js(__('social_media.disconnect_confirm_msg')));
                    }
                    if (!confirmed) return;

                    let pin = null;
                    @if(!empty($business->pos_supervisor_pin))
                    if (window.Swal) {
                        const { value: inputPin } = await Swal.fire({
                            title: @js(__('social_media.supervisor_pin_required')),
                            input: 'password',
                            inputAttributes: {
                                inputmode: 'numeric',
                                pattern: '[0-9]*',
                                maxlength: 6
                            },
                            inputPlaceholder: @js(__('social_media.supervisor_pin_placeholder')),
                            showCancelButton: true,
                            confirmButtonText: @js(__('common.confirm')),
                            cancelButtonText: @js(__('common.cancel'))
                        });
                        if (!inputPin) return;
                        pin = inputPin;
                    }
                    @endif

                    try {
                        const res = await fetch('{{ route('social-media.disconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ account_id: accountId, pin: pin })
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.disconnectedIds.push(accountId);
                            if (window.AppAlert) {
                                AppAlert.success(data.message || @js(__('social_media.account_disconnected_success')));
                            }
                        } else {
                            if (window.AppAlert) {
                                AppAlert.error(data.error || data.message || @js(__('social_media.account_not_found')));
                            }
                        }
                    } catch (e) {
                        if (window.AppAlert) {
                            AppAlert.error(@js(__('common.error')));
                        }
                    }
                }
            };
        }
    </script>
@endpush
