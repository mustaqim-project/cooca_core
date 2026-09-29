@extends('layouts.app', [
    'title' => __('social_media.inbox_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.inbox_header_title'),
    'headerSubtitle' => __('social_media.inbox_header_subtitle'),
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="socialInboxManager()">

        {{-- MODULE HEADER & PERSISTENT COMMUNICATION TABS --}}
        <x-module-header
            module="communication"
            :title="__('social_media.inbox_title')"
            :subtitle="__('social_media.inbox_subtitle')">
            <x-slot:actions>
                <button @click="refreshInbox(true)" :disabled="isRefreshing"
                    class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-50 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="{'animate-spin': isRefreshing}"></i>
                    <span x-text="isRefreshing ? '{{ __('social_media.refreshing_inbox_btn') }}' : '{{ __('social_media.refresh_inbox_btn') }}'">{{ __('social_media.refresh_inbox_btn') }}</span>
                </button>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="communication" />

        {{-- 2. MODULE NAVIGATION SUB-TABS --}}
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-sm">
            <div class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('social-media.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="link" class="w-4 h-4"></i>
                    <span>{{ __('social_media.tab_connect') }}</span>
                </a>
                <a href="{{ route('social-media.posts.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="image" class="w-4 h-4"></i>
                    <span>{{ __('social_media.tab_posts') }}</span>
                </a>
                <a href="{{ route('social-media.calendar.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span>{{ __('social_media.tab_calendar') }}</span>
                </a>
                <a href="{{ route('social-media.inbox.index') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="message-square" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('social_media.tab_inbox') }}</span>
                </a>
                <a href="{{ route('social-media.insights.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>{{ __('social_media.tab_insights') }}</span>
                </a>
            </div>
        </div>

        {{-- 3. FILTER PILLS --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider mr-1">Status:</span>
                <a href="{{ route('social-media.inbox.index', ['status' => 'all']) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'all' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.all_comments_filter', ['count' => $comments->total()]) }}
                </a>
                <a href="{{ route('social-media.inbox.index', ['status' => 'unread']) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'unread' ? 'bg-[#FF9500] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.unread_comments_filter') }}
                </a>
                <a href="{{ route('social-media.inbox.index', ['status' => 'replied']) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'replied' ? 'bg-[#34C759] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    {{ __('social_media.replied_comments_filter') }}
                </a>
            </div>
        </div>

        {{-- 4. COMMENTS LIST --}}
        <div id="comments-container">
        @if($comments->isEmpty())
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-[20px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="message-square-off" class="w-8 h-8"></i>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.inbox_clean_title') }}</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60">
                        {{ __('social_media.inbox_clean_desc') }}
                    </p>
                </div>
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($comments as $c)
                    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                         id="comment-row-{{ $c->id }}">
                        <div class="flex items-start gap-3.5 min-w-0">
                            {{-- Platform Icon Avatar --}}
                            <div class="w-10 h-10 rounded-[12px] flex items-center justify-center text-white shrink-0 shadow-sm
                                {{ $c->platform === 'facebook' ? 'bg-[#1877F2]' : ($c->platform === 'instagram' ? 'bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF]' : 'bg-black dark:bg-white dark:text-black') }}">
                                @if($c->platform === 'facebook')
                                    <i data-lucide="facebook" class="w-5 h-5"></i>
                                @elseif($c->platform === 'instagram')
                                    <i data-lucide="instagram" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="at-sign" class="w-5 h-5"></i>
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="space-y-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">
                                        {{ $c->sender_name ?: __('social_media.user_label', ['platform' => ucfirst($c->platform)]) }}
                                    </span>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">•</span>
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50">
                                        {{ $c->created_time ? $c->created_time->diffForHumans() : $c->created_at->diffForHumans() }}
                                    </span>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">•</span>
                                    <span class="text-[11px] font-medium text-black/60 dark:text-white/60">
                                        {{ $c->account ? $c->account->account_name : ucfirst($c->platform) }}
                                    </span>
                                </div>

                                {{-- Comment message text --}}
                                <p class="text-[13.5px] text-black/90 dark:text-white/90 leading-relaxed font-normal">
                                    {{ $c->message }}
                                </p>

                                {{-- Post snippet reference if available --}}
                                @if($c->post)
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-1.5 pt-0.5">
                                        <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                                        <span class="truncate max-w-md">{{ __('social_media.in_post_reference', ['content' => Str::limit($c->post->content, 60)]) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Action & Status --}}
                        <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                            @if($c->status === 'replied')
                                <span class="px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1.5"
                                      id="badge-{{ $c->id }}">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('social_media.status_replied') }}</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1.5"
                                      id="badge-{{ $c->id }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                    <span>{{ __('social_media.status_waiting_reply') }}</span>
                                </span>
                            @endif

                            <button type="button" @click="prepareReply(@js($c->id), @js($c->sender_name ?: __('social_media.user_label', ['platform' => ucfirst($c->platform)])), @js($c->message), @js($c->platform))"
                                class="h-8 px-3.5 rounded-[9px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i>
                                <span>{{ __('social_media.reply_action_btn') }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $comments->links() }}
            </div>
        @endif
        </div>

        {{-- 5. REPLY MODAL SHEET (Apple HIG Bento Card Design) --}}
        <div x-show="openReplyModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 sm:p-7 space-y-5"
                @click.away="openReplyModal = false">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="corner-up-left" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.reply_modal_title') }}</h3>
                            <p class="text-[12px] text-black/55 dark:text-white/55">{{ __('social_media.reply_modal_subtitle') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="openReplyModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Original Comment Preview (Apple HIG Bento Card) --}}
                <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white text-[11px] font-bold flex items-center justify-center shadow-xs shrink-0"
                                x-text="activeSender ? activeSender.charAt(0).toUpperCase() : 'U'">
                            </div>
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-[13px] font-bold text-black dark:text-white truncate" x-text="activeSender"></span>
                                <span class="uppercase text-[10px] font-mono px-2 py-0.5 rounded-full bg-black/10 dark:bg-white/10 font-bold tracking-wider text-black/70 dark:text-white/70 shrink-0" x-text="activePlatform"></span>
                            </div>
                        </div>
                        <span class="text-[11px] font-medium text-black/40 dark:text-white/40 shrink-0">{{ __('social_media.customer_comment_badge') }}</span>
                    </div>
                    <p class="text-[13px] text-black/85 dark:text-white/85 leading-relaxed bg-white/70 dark:bg-black/25 p-3 rounded-[12px] border border-black/5 dark:border-white/5 italic" x-text="'&quot;' + activeMessage + '&quot;'"></p>
                </div>

                {{-- Reply Textarea --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[12.5px] font-semibold text-black/70 dark:text-white/70">
                        <label>{{ __('social_media.your_reply_label') }}</label>
                        <span class="text-[11px] text-black/40 dark:text-white/40 tabular-nums font-normal" x-text="replyText.length + ' / 1000'"></span>
                    </div>
                    <textarea rows="4" x-model="replyText"
                        placeholder="{{ __('social_media.your_reply_placeholder') }}"
                        class="w-full p-3.5 rounded-[14px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                </div>

                {{-- Status / Error notification inside modal --}}
                <div x-show="errorMessage" style="display: none;"
                     class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12.5px] text-[#FF3B30] flex items-center gap-2"
                     x-text="errorMessage"></div>

                {{-- Modal Footer --}}
                <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="openReplyModal = false" :disabled="isSubmitting"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
                        {{ __('social_media.cancel') }}
                    </button>
                    <button type="button" @click="sendReply()" :disabled="!replyText.trim() || isSubmitting"
                        class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none transition-all flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="send" class="w-3.5 h-3.5" x-show="!isSubmitting"></i>
                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="isSubmitting" style="display: none;"></i>
                        <span x-text="isSubmitting ? '{{ __('social_media.sending_reply_btn') }}' : '{{ __('social_media.send_reply_btn') }}'"></span>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <script>
        function socialInboxManager() {
            return {
                openReplyModal: false,
                activeCommentId: null,
                activeSender: '',
                activeMessage: '',
                activePlatform: '',
                replyText: '',
                isSubmitting: false,
                isRefreshing: false,
                errorMessage: '',
                pollingTimer: null,

                init() {
                    // Smart auto-polling background every 60 seconds when document is visible
                    this.pollingTimer = setInterval(() => {
                        if (!document.hidden && !this.openReplyModal && !this.isRefreshing) {
                            this.refreshInbox(false);
                        }
                    }, 60000);
                },

                async refreshInbox(showNotification = true) {
                    this.isRefreshing = true;
                    try {
                        const res = await fetch(window.location.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        });
                        if (res.ok) {
                            const html = await res.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContainer = doc.getElementById('comments-container');
                            const currentContainer = document.getElementById('comments-container');
                            if (newContainer && currentContainer) {
                                currentContainer.innerHTML = newContainer.innerHTML;
                                if (window.lucide) window.lucide.createIcons();
                            }
                            if (showNotification && window.AppAlert) {
                                AppAlert.success('{{ __('social_media.inbox_refreshed') }}');
                            }
                        }
                    } catch (e) {
                        if (showNotification && window.AppAlert) {
                            AppAlert.error('Gagal memperbarui pesan.');
                        }
                    } finally {
                        this.isRefreshing = false;
                    }
                },

                prepareReply(id, sender, message, platform) {
                    this.activeCommentId = id;
                    this.activeSender = sender;
                    this.activeMessage = message;
                    this.activePlatform = platform;
                    this.replyText = '';
                    this.errorMessage = '';
                    this.openReplyModal = true;
                    if (window.lucide) window.lucide.createIcons();
                },

                async sendReply() {
                    if (!this.replyText.trim() || !this.activeCommentId) return;
                    this.isSubmitting = true;
                    this.errorMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(`/social-media/comments/${this.activeCommentId}/reply`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ message: this.replyText })
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.openReplyModal = false;
                            const badge = document.getElementById(`badge-${this.activeCommentId}`);
                            if (badge) {
                                badge.className = 'px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1.5';
                                badge.replaceChildren();
                                const icon = document.createElement('i');
                                icon.setAttribute('data-lucide', 'check');
                                icon.className = 'w-3.5 h-3.5';
                                const textSpan = document.createElement('span');
                                textSpan.textContent = '{{ __('social_media.status_replied') }}';
                                badge.appendChild(icon);
                                badge.appendChild(textSpan);
                                if (window.lucide) window.lucide.createIcons();
                            }
                            if (window.AppAlert) {
                                AppAlert.success('{{ __('social_media.comment_replied', ['platform' => '']) }}');
                            }
                        } else {
                            this.errorMessage = data.error || 'Gagal mengirim balasan.';
                            if (window.AppAlert) {
                                AppAlert.error(this.errorMessage);
                            }
                        }
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan jaringan atau server.';
                        if (window.AppAlert) {
                            AppAlert.error(this.errorMessage);
                        }
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
@endsection
