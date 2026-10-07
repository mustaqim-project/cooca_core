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
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('social_media.create_post_btn') }}</span>
                </a>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- 3. MAIN COCKPIT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: CONNECTED ACCOUNTS & ONBOARDING --}}
            <div class="lg:col-span-7 space-y-6">

                <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#0A66C2] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#1877F2]/25">
                                <i data-lucide="share-2" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.connected_accounts') }}</h2>
                                <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">{{ __('social_media.connected_accounts_sub') }}</p>
                            </div>
                        </div>

                        <div>
                            <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5 {{ $accounts->where('status', 'active')->count() > 0 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10' }}">
                                <span class="w-2 h-2 rounded-full {{ $accounts->where('status', 'active')->count() > 0 ? 'bg-[#34C759]' : 'bg-[#FF9500]' }}"></span>
                                <span>{{ $accounts->where('status', 'active')->count() > 0 ? __('social_media.connected_count', ['count' => $accounts->where('status', 'active')->count()]) : __('social_media.not_connected') }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- Connected Accounts Cards List --}}
                    @if($accounts->where('status', 'active')->isNotEmpty())
                        <div class="space-y-3">
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">{{ __('social_media.active_assets') }}</span>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($accounts->where('status', 'active') as $acc)
                                    <div x-show="!disconnectedIds.includes('{{ $acc->id }}')" x-transition.duration.300ms class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-[12px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/12 text-[#1877F2]' : ($acc->platform === 'instagram' ? 'bg-[#E1306C]/12 text-[#E1306C]' : ($acc->platform === 'tiktok' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : ($acc->platform === 'linkedin' ? 'bg-[#0A66C2]/12 text-[#0A66C2]' : 'bg-black/10 text-black dark:text-white'))) }}">
                                                <x-social-icon :platform="$acc->platform" class="w-5 h-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-black dark:text-white text-[13.5px] truncate">{{ $acc->account_name }}</div>
                                                <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-2">
                                                    <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                    @if($acc->username)
                                                        <span>•</span>
                                                        <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                    @endif
                                                    @if(in_array($acc->platform, ['tiktok', 'linkedin']) && $acc->token_expires_at)
                                                        <span>•</span>
                                                        <span class="text-[10.5px] text-[#34C759]">{{ __('social_media.auto_refresh_active') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="disconnect('{{ $acc->id }}')"
                                                class="min-h-[34px] px-3 rounded-[9px] text-[11.5px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all">
                                                {{ __('social_media.disconnect') }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 1-Click Onboarding Banners: META, TIKTOK & LINKEDIN --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[14px] font-bold text-black dark:text-white">
                                {{ __('social_media.onboarding_section_title') }}
                            </h3>
                            <span class="text-[11px] text-black/45 dark:text-white/45">{{ __('social_media.onboarding_api_badge') }}</span>
                        </div>

                        {{-- Meta Connection Card --}}
                        <div class="p-5 sm:p-6 rounded-[18px] bg-gradient-to-br from-[#1877F2]/10 via-white dark:via-[#1C1C1E] to-[#E1306C]/8 border border-[#1877F2]/20 space-y-4">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white">
                                    {{ $accounts->whereIn('platform', ['facebook', 'instagram', 'threads'])->where('status', 'active')->isNotEmpty() ? __('social_media.meta_card_title_connected') : __('social_media.meta_card_title_new') }}
                                </h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">
                                    {{ __('social_media.meta_card_desc') }}
                                </p>
                            </div>

                            <button type="button" @click="launchMetaLogin()" :disabled="loading"
                                class="w-full min-h-[46px] rounded-[14px] bg-gradient-to-r from-[#1877F2] to-[#007AFF] hover:from-[#166FE5] hover:to-[#0071E3] text-white font-bold text-[13.5px] flex items-center justify-center gap-2 shadow-md shadow-[#1877F2]/25 active:scale-[0.98] transition-all disabled:opacity-50 cursor-pointer">
                                <i data-lucide="loader-2" x-show="loading" class="w-4 h-4 animate-spin"></i>
                                <span x-show="!loading" class="inline-flex items-center"><x-social-icon platform="meta" class="w-4.5 h-4.5" /></span>
                                <span x-text="loading ? '{{ __('social_media.meta_connecting_btn') }}' : '{{ __('social_media.meta_connect_btn') }}'"></span>
                            </button>
                        </div>

                        {{-- TikTok Connection Card --}}
                        <div class="p-5 sm:p-6 rounded-[18px] bg-gradient-to-br from-black/5 via-white dark:via-[#1C1C1E] to-black/10 dark:to-white/5 border border-black/10 dark:border-white/10 space-y-4">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white">
                                    {{ $accounts->where('platform', 'tiktok')->where('status', 'active')->isNotEmpty() ? __('social_media.tiktok_card_title_connected') : __('social_media.tiktok_card_title_new') }}
                                </h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">
                                    {{ __('social_media.tiktok_card_desc') }}
                                </p>
                            </div>

                            <a href="{{ route('social-media.tiktok.connect') }}"
                                class="w-full min-h-[46px] rounded-[14px] bg-black dark:bg-white text-white dark:text-black hover:bg-black/90 dark:hover:bg-white/90 font-bold text-[13.5px] flex items-center justify-center gap-2 shadow-md active:scale-[0.98] transition-all">
                                <x-social-icon platform="tiktok" class="w-4 h-4" />
                                <span>{{ __('social_media.tiktok_connect_btn') }}</span>
                            </a>
                        </div>

                        {{-- LinkedIn Connection Card --}}
                        <div class="p-5 sm:p-6 rounded-[18px] bg-gradient-to-br from-[#0A66C2]/10 via-white dark:via-[#1C1C1E] to-[#004182]/5 border border-[#0A66C2]/20 space-y-4">
                            <div class="space-y-0.5">
                                <h3 class="text-[15px] font-bold text-black dark:text-white">
                                    {{ $accounts->where('platform', 'linkedin')->where('status', 'active')->isNotEmpty() ? __('social_media.linkedin_card_title_connected') : __('social_media.linkedin_card_title_new') }}
                                </h3>
                                <p class="text-[12px] text-black/55 dark:text-white/55">
                                    {{ __('social_media.linkedin_card_desc') }}
                                </p>
                            </div>

                            <a href="{{ route('social-media.linkedin.connect') }}"
                                class="w-full min-h-[46px] rounded-[14px] bg-[#0A66C2] hover:bg-[#004182] text-white font-bold text-[13.5px] flex items-center justify-center gap-2 shadow-md shadow-[#0A66C2]/25 active:scale-[0.98] transition-all">
                                <x-social-icon platform="linkedin" class="w-4 h-4" />
                                <span>{{ __('social_media.linkedin_connect_btn') }}</span>
                            </a>
                        </div>

                        {{-- Alert Error --}}
                        <div x-show="errorMessage" x-transition class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        {{-- Alert Success --}}
                        <div x-show="successMessage" x-transition class="p-3.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="successMessage"></span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: RECENT POSTS & QUICK STATS --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Security & Privacy Bento Card --}}
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-3.5">
                    <div class="flex items-center gap-2.5 font-bold text-[14px] text-black dark:text-white">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                        <span>{{ __('social_media.security_bento_title') }}</span>
                    </div>
                    <ul class="space-y-2 text-[12px] text-black/65 dark:text-white/65">
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <span>{{ __('social_media.security_item_1') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <span>{{ __('social_media.security_item_2') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <span>{{ __('social_media.security_item_3') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Recent Posts List --}}
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-3.5">
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
