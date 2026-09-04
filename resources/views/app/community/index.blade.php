@extends('layouts.app', ['title' => 'Komunitas Owner — Cooca UMKM'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-emerald-400 font-bold flex items-center gap-1.5">
                <i data-lucide="users" class="w-3.5 h-3.5"></i> Komunitas Owner
            </p>
            <h1 class="text-2xl font-black text-white">Aktivitas Komunitas</h1>
            <p class="text-xs text-slate-400 mt-1">Terhubung dengan sesama pemilik bisnis, bagikan pengalaman & inspirasi.</p>
        </div>
    </div>

    <!-- Storage Quota Warning Banner -->
    <div class="glass-card rounded-2xl border border-amber-500/30 p-4 space-y-2.5">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="hard-drive" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-white">Perhatian: Gambar Menggunakan Kuota Storage</p>
                <p class="text-xs text-slate-300 leading-relaxed mt-0.5">
                    Setiap <strong class="text-amber-300">foto yang diunggah</strong> pada postingan akan
                    <strong class="text-amber-300">memotong kuota storage</strong> milik bisnis Anda. Kuota saat ini:
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 pl-12">
            <div class="flex items-center gap-3">
                <span class="text-xs font-mono text-slate-200">
                    <span class="font-black text-amber-300">{{ number_format($storageSummary['used_mb'] ?? 0, 1, ',', '.') }} MB</span>
                    <span class="text-slate-500">/ {{ number_format($storageSummary['limit_gb'] ?? 0, 2, ',', '.') }} GB</span>
                </span>
                <div class="w-28 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full transition-all"
                         style="width: {{ min(100, $storageSummary['percentage'] ?? 0) }}%"></div>
                </div>
            </div>
            <span class="text-[11px] font-bold {{ ($storageSummary['percentage'] ?? 0) > 80 ? 'text-rose-400' : 'text-amber-300' }}">
                {{ $storageSummary['percentage'] ?? 0 }}% terpakai
            </span>
        </div>
    </div>

    <!-- Composer Card -->
    <form method="POST" action="{{ route('community.store') }}" enctype="multipart/form-data"
          class="glass-card rounded-2xl border border-slate-800 p-4 space-y-3"
          x-data="{ previewUrl: null, fileSelected: false }">
        @csrf

        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                {{ substr(auth()->user()->name ?? 'O', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <textarea name="content" required rows="3" maxlength="5000"
                          placeholder="Bagikan kabar terbaru tentang bisnis Anda, tips, atau inspirasi untuk sesama owner..."
                          class="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 resize-none"></textarea>

                <!-- Image Preview -->
                <div x-show="previewUrl" x-cloak class="mt-2 relative">
                    <img :src="previewUrl" alt="Pratinjau Gambar" class="max-h-56 w-full object-cover rounded-xl border border-slate-700">
                    <button type="button" @click="previewUrl = null; fileSelected = false; $refs.imageInput.value = ''"
                            class="absolute top-2 right-2 p-1.5 rounded-lg bg-slate-950/80 text-slate-300 hover:text-rose-400 border border-slate-700">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3">
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 text-xs font-semibold text-slate-300 hover:text-white cursor-pointer transition">
                        <i data-lucide="image-plus" class="w-4 h-4 text-emerald-400"></i>
                        <span x-text="fileSelected ? 'Gambar dipilih' : 'Tambah Gambar'"></span>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                               x-ref="imageInput"
                               @change="previewUrl = URL.createObjectURL($event.target.files[0]); fileSelected = true"
                               class="hidden">
                    </label>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition inline-flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Bagikan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
<!-- Feed / Posts -->
    <div class="space-y-5">
        @forelse($posts as $post)
        <article class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <!-- Post Header -->
            <div class="p-4 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-emerald-400 shrink-0">
                    {{ strtoupper(substr($post->owner->name ?? 'O', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-white text-sm">{{ $post->owner->name ?? 'Owner' }}</span>
                        <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[9px] font-bold uppercase">Owner</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                        @if($post->business)
                            <span class="inline-flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3 h-3"></i> {{ $post->business->name }}
                            </span>
                        @endif
                        <span class="text-slate-500">•</span>
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if((string) $post->owner_id === (string) auth()->id())
                <form method="POST" action="{{ route('community.destroy', $post) }}"
                      onsubmit="return confirm('Hapus postingan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Hapus Postingan">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
            </div>

            <!-- Post Content -->
            <div class="px-4 pb-4">
                <p class="text-sm text-slate-200 leading-relaxed whitespace-pre-line">{{ $post->content }}</p>
                @if($post->image_url)
                <img src="{{ $post->image_url }}" alt="Gambar postingan {{ $post->owner->name ?? '' }}"
                     class="mt-3 w-full max-h-[420px] object-cover rounded-xl border border-slate-700 cursor-pointer"
                     onclick="window.open('{{ $post->image_url }}', '_blank')">
                @endif
            </div>
<!-- Post Actions: Like & Comment -->
            <div class="px-4 py-2 border-t border-slate-800 flex items-center gap-1"
                 x-data="postCard('{{ $post->id }}', {{ $post->liked_by_me ? 'true' : 'false' }}, {{ $post->likes_count }}, {{ $post->comments_count }})">
                <button type="button" @click="toggleLike()"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl transition text-xs font-semibold"
                        :class="liked ? 'text-rose-400 bg-rose-500/10' : 'text-slate-400 hover:text-rose-400 hover:bg-slate-800/60'">
                    <i data-lucide="heart" class="w-4 h-4" :class="liked ? 'fill-rose-400' : ''"></i>
                    <span x-text="likesCount + ' Suka'"></span>
                </button>
                <button type="button" @click="commentsOpen = !commentsOpen"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl transition text-xs font-semibold text-slate-400 hover:text-emerald-400 hover:bg-slate-800/60">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span x-text="commentsCount + ' Komentar'"></span>
                </button>
            </div>

            <!-- Comments Section -->
            <div x-show="commentsOpen" x-cloak class="px-4 pb-4 space-y-3 bg-slate-950/40 border-t border-slate-800/60">
                <!-- Existing Comments -->
                <div class="space-y-3 pt-3">
                    @forelse($post->comments as $comment)
                    <div class="flex items-start gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-[10px] text-emerald-400 shrink-0">
                            {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2">
                                <span class="text-xs font-bold text-white">{{ $comment->user->name ?? 'User' }}</span>
                                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">{{ $comment->content }}</p>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 text-center py-2">Belum ada komentar. Jadilah yang pertama memberi komentar!</p>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                <form class="flex items-center gap-2" @submit.prevent="submitComment($el)">
                    @csrf
                    <input type="text" name="content" required maxlength="1000" placeholder="Tulis komentar..."
                           class="flex-1 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                    <button type="submit" class="p-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </article>
        @empty
        <div class="glass-card rounded-2xl p-12 text-center border border-dashed border-slate-800">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 mb-3">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <p class="text-sm font-bold text-slate-300">Belum ada postingan komunitas</p>
            <p class="text-xs text-slate-500 mt-1">Jadilah yang pertama berbagi kabar atau inspirasi untuk sesama owner.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div>
        {{ $posts->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function postCard(postId, initialLiked, initialLikesCount, initialCommentsCount) {
        return {
            liked: initialLiked,
            likesCount: initialLikesCount,
            commentsCount: initialCommentsCount,
            commentsOpen: false,
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            async toggleLike() {
                const res = await fetch('/community/' + postId + '/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.success) {
                    this.liked = data.liked;
                    this.likesCount = data.likes_count;
                }
            },

            async submitComment(form) {
                const input = form.querySelector('input[name="content"]');
                const content = input.value.trim();
                if (!content) return;

                const res = await fetch('/community/' + postId + '/comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content: content })
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.success) {
                    // Refresh page to show new comment cleanly.
                    window.location.reload();
                }
            }
        };
    }
</script>
@endpush
