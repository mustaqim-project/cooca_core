@extends('layouts.app', [
    'title' => 'Kotak Masuk & Komentar - ' . $business->name,
    'headerTitle' => 'Kotak Masuk & Komentar Media Sosial',
    'headerSubtitle' => 'Kelola dan balas interaksi pelanggan dari Facebook, Instagram, dan Threads',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="socialInboxManager()">

        {{-- 0. BREADCRUMB --}}
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <span>›</span>
            <a href="{{ route('social-media.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Media Sosial</a>
            <span>›</span>
            <span class="text-black/80 dark:text-white/80 font-medium">Kotak Masuk &amp; Komentar</span>
        </nav>

        {{-- 1. PAGE HEADER --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-sm">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#FF9500]/10 text-[#FF9500]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                        <span>Webhook Real-time</span>
                    </span>
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                        <span>Multi-Channel Inbox</span>
                    </span>
                </div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Interaksi Pelanggan &amp; Balas Komentar
                </h1>
                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pantau pertanyaan dan testimoni pelanggan di setiap postingan secara terpusat, lalu balas langsung melalui API resmi Meta.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button @click="window.location.reload()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Segarkan Pesan</span>
                </button>
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
                <a href="{{ route('social-media.inbox.index') }}"
                    class="h-8 px-4 rounded-[9px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i data-lucide="message-square" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Kotak Masuk &amp; Komentar</span>
                </a>
                <a href="{{ route('social-media.insights.index') }}"
                    class="h-8 px-4 rounded-[9px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    <span>Analitik &amp; Performa</span>
                </a>
            </div>
        </div>

        {{-- 3. FILTER PILLS --}}
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider mr-1">Status:</span>
                <a href="{{ route('social-media.inbox.index', ['status' => 'all']) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'all' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Semua Komentar ({{ $comments->total() }})
                </a>
                <a href="{{ route('social-media.inbox.index', ['status' => 'unread']) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'unread' ? 'bg-[#FF9500] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Belum Dibalas
                </a>
                <a href="{{ route('social-media.inbox.index', ['status' => 'replied']) }}"
                    class="h-7 px-3 rounded-full text-[12px] font-medium transition-colors {{ $status === 'replied' ? 'bg-[#34C759] text-white shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                    Sudah Dibalas
                </a>
            </div>
        </div>

        {{-- 4. COMMENTS LIST --}}
        @if($comments->isEmpty())
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-[20px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="message-square-off" class="w-8 h-8"></i>
                </div>
                <div class="max-w-md mx-auto space-y-1">
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Kotak Masuk Bersih</h3>
                    <p class="text-[13px] text-black/60 dark:text-white/60">
                        Belum ada komentar baru dari pelanggan di postingan media sosial toko Anda.
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
                                        {{ $c->sender_name ?: 'Pengguna ' . ucfirst($c->platform) }}
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
                                        <span class="truncate max-w-md">Di postingan: "{{ Str::limit($c->post->content, 60) }}"</span>
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
                                    <span>Sudah Dibalas</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#FF9500]/15 text-[#FF9500] inline-flex items-center gap-1.5"
                                      id="badge-{{ $c->id }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                    <span>Menunggu Balasan</span>
                                </span>
                            @endif

                            <button @click="prepareReply('{{ $c->id }}', '{{ addslashes($c->sender_name ?: 'Pengguna') }}', '{{ addslashes($c->message) }}', '{{ $c->platform }}')"
                                class="h-8 px-3.5 rounded-[9px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i>
                                <span>Balas</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $comments->links() }}
            </div>
        @endif

        {{-- 5. REPLY MODAL SHEET --}}
        <div x-show="openReplyModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4"
                @click.away="openReplyModal = false">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="corner-up-left" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Kirim Balasan Komentar</h3>
                            <p class="text-[11.5px] text-black/55 dark:text-white/55">Balasan akan dipublikasikan atas nama akun resmi toko</p>
                        </div>
                    </div>
                    <button @click="openReplyModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Original Comment Preview --}}
                <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-1">
                    <div class="text-[11.5px] font-bold text-black/60 dark:text-white/60 flex items-center gap-1.5">
                        <span x-text="activeSender"></span>
                        <span class="uppercase text-[10px] px-1.5 py-0.2 rounded bg-black/10 dark:bg-white/10 font-semibold" x-text="activePlatform"></span>
                    </div>
                    <p class="text-[12.5px] text-black/80 dark:text-white/80 italic" x-text="'&quot;' + activeMessage + '&quot;'"></p>
                </div>

                {{-- Reply Textarea --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[12px] font-semibold text-black/70 dark:text-white/70">
                        <label>Pesan Balasan Anda</label>
                        <span class="text-[11px] text-black/40 dark:text-white/40 tabular-nums font-normal" x-text="replyText.length + ' / 1000'"></span>
                    </div>
                    <textarea rows="3" x-model="replyText"
                        placeholder="Ketik balasan ramah untuk pelanggan Anda..."
                        class="w-full p-3 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                </div>

                {{-- Status / Error notification inside modal --}}
                <div x-show="errorMessage" style="display: none;"
                     class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-[#FF3B30]"
                     x-text="errorMessage"></div>

                {{-- Modal Footer --}}
                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="openReplyModal = false" :disabled="isSubmitting"
                        class="h-8 px-3.5 rounded-[9px] text-[12.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="sendReply()" :disabled="!replyText.trim() || isSubmitting"
                        class="h-8 px-4 rounded-[9px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none transition-all flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="send" class="w-3.5 h-3.5" x-show="!isSubmitting"></i>
                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="isSubmitting" style="display: none;"></i>
                        <span x-text="isSubmitting ? 'Mengirim...' : 'Kirim Balasan'"></span>
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
                errorMessage: '',

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
                                badge.innerHTML = '<svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span>Sudah Dibalas</span>';
                            }
                            alert('Balasan berhasil dikirim ke platform media sosial.');
                        } else {
                            this.errorMessage = data.error || 'Gagal mengirim balasan.';
                        }
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan jaringan atau server.';
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
@endsection
