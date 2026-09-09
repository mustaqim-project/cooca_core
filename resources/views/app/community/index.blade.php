@extends('layouts.app', [
    'title' => 'Komunitas Owner — Cooca UMKM',
    'headerTitle' => 'Komunitas Owner',
    'headerSubtitle' => 'Wadah eksklusif berbagi insight, kolaborasi bisnis, dan tips pertumbuhan UMKM'
])

@section('content')
<div class="max-w-7xl mx-auto space-y-8" x-data="{
    activeTopic: 'all',
    filterTag: '',
    copiedPostId: null,
    copyLink(postId) {
        const url = window.location.origin + '/community#' + postId;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url);
        }
        this.copiedPostId = postId;
        setTimeout(() => this.copiedPostId = null, 2500);
    },
    appendTag(tag) {
        const textarea = document.querySelector('textarea[name=\'content\']');
        if (textarea) {
            textarea.value = (textarea.value.trim() ? textarea.value.trim() + ' ' : '') + tag + ' ';
            textarea.focus();
        }
    }
}">

    <!-- Top Navigation & Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-slate-800 p-6 sm:p-8 shadow-2xl">
        <!-- Ambient background lighting -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 shadow-sm">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span>Komunitas Owner</span>
                    </span>
                    <span class="text-slate-500 text-xs font-mono">•</span>
                    <span class="text-xs text-slate-400 font-medium">Jaringan Pemilik Usaha Cooca ID</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Ruang Berbagi &amp; Kolaborasi Sesama Owner</h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Diskusikan strategi operasional, bertukar tips pengelolaan omset kasir POS, dan temukan mitra kolaborasi baru untuk mengakselerasi bisnis UMKM Anda.
                </p>
            </div>

            <!-- Quick Action / Workspace Info Card -->
            <div class="flex items-center gap-3 self-start md:self-auto bg-slate-950/80 border border-slate-800/80 p-3.5 rounded-2xl shadow-inner shrink-0">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(substr($business->name ?? 'B', 0, 1)) }}
                </div>
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Bisnis Anda</span>
                    <div class="text-xs font-black text-white truncate max-w-[160px]">{{ $business->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Responsive 2-Column Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left / Main Column: Composer & Feed Stream (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Storage Quota Warning Banner (Required by Test & Business Logic) -->
            <div class="p-4 sm:p-5 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-3 backdrop-blur-xl">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="hard-drive" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs sm:text-sm font-bold text-white">Kuota Storage Pemilik Bisnis</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ ($storageSummary['percentage'] ?? 0) > 80 ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                    {{ $storageSummary['percentage'] ?? 0 }}% terpakai
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed mt-0.5">
                                Setiap foto yang diunggah pada postingan menggunakan kuota storage bisnis Anda.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('billing.checkout', ['type' => 'storage']) }}" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-bold transition border border-slate-700 shadow-sm shrink-0">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Tambah Kapasitas</span>
                    </a>
                </div>

                <!-- Storage Progress Bar -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-400 text-[11px]">Terpakai: <strong class="text-white">{{ number_format($storageSummary['used_mb'] ?? 0, 1, ',', '.') }} MB</strong></span>
                        <span class="text-slate-400 text-[11px]">Batas Maks: <strong class="text-slate-200">{{ number_format($storageSummary['limit_gb'] ?? 0, 2, ',', '.') }} GB</strong></span>
                    </div>
                    <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-slate-800/80 p-0.5">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($storageSummary['percentage'] ?? 0) > 85 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-emerald-500 to-teal-400' }}"
                             style="width: {{ min(100, $storageSummary['percentage'] ?? 0) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Modern Post Composer Card -->
            <div class="p-6 sm:p-7 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl space-y-4 backdrop-blur-xl relative overflow-hidden"
                 x-data="{
                     previewUrl: null,
                     fileSelected: false,
                     charCount: 0,
                     isSubmitting: false,
                     onInput(e) {
                         this.charCount = e.target.value.length;
                     }
                 }">

                <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-slate-950 font-black text-xs flex items-center justify-center shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white block">{{ auth()->user()->name ?? 'Owner' }}</span>
                            <span class="text-[10px] text-emerald-400 font-mono">Posting sebagai Pemilik Bisnis</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-500" x-text="charCount + ' / 5000'"></span>
                </div>

                <form method="POST" 
                      action="{{ route('community.store') }}" 
                      enctype="multipart/form-data" 
                      @submit="isSubmitting = true"
                      class="space-y-3.5">
                    @csrf

                    <!-- Content Textarea -->
                    <div class="relative">
                        <textarea name="content" 
                                  required 
                                  rows="3" 
                                  maxlength="5000"
                                  @input="onInput($event)"
                                  placeholder="Bagikan kabar terbaru tentang bisnis Anda, tips penjualan POS, atau inspirasi untuk sesama owner..."
                                  class="w-full bg-slate-950/90 border border-slate-800 focus:border-emerald-500 rounded-2xl p-4 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/40 transition resize-none leading-relaxed"></textarea>
                    </div>

                    <!-- Live Image Preview Box -->
                    <div x-show="previewUrl" x-cloak class="relative rounded-2xl overflow-hidden border border-slate-700/80 bg-black/40">
                        <img :src="previewUrl" alt="Pratinjau Gambar" class="max-h-64 w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>
                        <button type="button" 
                                @click="previewUrl = null; fileSelected = false; $refs.imageInput.value = ''"
                                class="absolute top-3 right-3 p-2 rounded-xl bg-slate-950/80 hover:bg-rose-500/20 text-slate-300 hover:text-rose-400 border border-slate-700 transition cursor-pointer"
                                title="Hapus Gambar">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Quick Tag Chips -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[10px] font-mono text-slate-500 font-bold uppercase mr-1">Topik Cepat:</span>
                        <button type="button" @click="appendTag('#TipsBisnis')" class="px-2.5 py-1 rounded-lg bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-emerald-400 text-[11px] font-medium border border-slate-800 transition">💡 #TipsBisnis</button>
                        <button type="button" @click="appendTag('#ManajemenPOS')" class="px-2.5 py-1 rounded-lg bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-emerald-400 text-[11px] font-medium border border-slate-800 transition">📊 #ManajemenPOS</button>
                        <button type="button" @click="appendTag('#Kolaborasi')" class="px-2.5 py-1 rounded-lg bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-emerald-400 text-[11px] font-medium border border-slate-800 transition">🤝 #Kolaborasi</button>
                        <button type="button" @click="appendTag('#TanyaOwner')" class="px-2.5 py-1 rounded-lg bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-emerald-400 text-[11px] font-medium border border-slate-800 transition">💬 #TanyaOwner</button>
                    </div>

                    <!-- Actions Bottom Bar -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 gap-3">
                        <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-300 hover:text-white cursor-pointer transition shadow-sm">
                            <i data-lucide="image-plus" class="w-4 h-4 text-emerald-400"></i>
                            <span x-text="fileSelected ? 'Gambar Dipilih' : 'Unggah Foto'"></span>
                            <input type="file" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/webp,image/gif"
                                   x-ref="imageInput"
                                   @change="previewUrl = URL.createObjectURL($event.target.files[0]); fileSelected = true"
                                   class="hidden">
                        </label>

                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition inline-flex items-center gap-2 disabled:opacity-50 cursor-pointer">
                            <i data-lucide="send" class="w-4 h-4" x-show="!isSubmitting"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSubmitting" x-cloak></i>
                            <span x-text="isSubmitting ? 'Mengirim...' : 'Bagikan'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Feed Stream Heading -->
            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-black text-white">Diskusi &amp; Kabar Terbaru</h2>
                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[11px] font-mono">{{ $posts->total() }}</span>
                </div>
            </div>

            <!-- Posts List Stream -->
            <div class="space-y-6">
                @forelse($posts as $post)
                <article class="p-6 sm:p-7 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4 backdrop-blur-xl transition hover:border-slate-700/90"
                         id="{{ $post->id }}"
                         x-data="postCard('{{ $post->id }}', {{ $post->liked_by_me ? 'true' : 'false' }}, {{ $post->likes_count }}, {{ $post->comments_count }})">

                    <!-- Post Author Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-slate-800 to-slate-700 border border-slate-700 flex items-center justify-center font-black text-sm text-emerald-400 shrink-0 shadow-inner">
                                {{ strtoupper(substr($post->owner->name ?? 'O', 0, 1)) }}
                            </div>
                            <div class="min-w-0 space-y-0.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-white text-sm truncate">{{ $post->owner->name ?? 'Owner' }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[9px] font-black uppercase tracking-wider">
                                        Owner
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center gap-2 flex-wrap font-sans">
                                    @if($post->business)
                                        <span class="inline-flex items-center gap-1 font-medium text-slate-300">
                                            <i data-lucide="building-2" class="w-3 h-3 text-cyan-400"></i>
                                            <span>{{ $post->business->name }}</span>
                                        </span>
                                        <span class="text-slate-600">•</span>
                                    @endif
                                    <span class="font-mono text-slate-500">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown / Delete Post Action (If Author) -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" 
                                    @click="copyLink('{{ $post->id }}')"
                                    class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition"
                                    title="Salin Tautan Postingan">
                                <i data-lucide="share-2" class="w-4 h-4" x-show="copiedPostId !== '{{ $post->id }}'"></i>
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400" x-show="copiedPostId === '{{ $post->id }}'" x-cloak></i>
                            </button>

                            @if((string) $post->owner_id === (string) auth()->id())
                            <form method="POST" action="{{ route('community.destroy', $post) }}"
                                  onsubmit="return confirm('Hapus postingan ini dari komunitas?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition cursor-pointer" 
                                        title="Hapus Postingan">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Post Body Text Content -->
                    <div class="space-y-3">
                        <p class="text-sm text-slate-200 leading-relaxed whitespace-pre-line selection:bg-emerald-500 selection:text-slate-950 font-sans">{{ $post->content }}</p>

                        <!-- Attached Post Image -->
                        @if($post->image_url)
                        <div class="rounded-2xl overflow-hidden border border-slate-800 bg-slate-950/60 mt-3">
                            <img src="{{ $post->image_url }}" 
                                 alt="Gambar postingan {{ $post->owner->name ?? '' }}"
                                 class="w-full max-h-[460px] object-cover hover:scale-[1.01] transition-transform duration-300 cursor-pointer"
                                 onclick="window.open('{{ $post->image_url }}', '_blank')">
                        </div>
                        @endif
                    </div>

                    <!-- Action Bar: Like & Comment Counters -->
                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Like Button -->
                            <button type="button" 
                                    @click="toggleLike()"
                                    class="flex items-center gap-2 px-3.5 py-2 rounded-xl transition text-xs font-bold shadow-sm"
                                    :class="liked ? 'text-rose-400 bg-rose-500/10 border border-rose-500/30' : 'text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 border border-transparent'">
                                <i data-lucide="heart" class="w-4 h-4 transition-transform active:scale-125" :class="liked ? 'fill-rose-400 text-rose-400' : ''"></i>
                                <span x-text="likesCount + ' Suka'"></span>
                            </button>

                            <!-- Comment Toggle Button -->
                            <button type="button" 
                                    @click="commentsOpen = !commentsOpen"
                                    class="flex items-center gap-2 px-3.5 py-2 rounded-xl transition text-xs font-bold border border-transparent"
                                    :class="commentsOpen ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30' : 'text-slate-400 hover:text-emerald-400 hover:bg-slate-800/80'">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span x-text="commentsCount + ' Komentar'"></span>
                            </button>
                        </div>

                        <span class="text-[10px] font-mono text-slate-500">ID: {{ substr($post->id, 0, 8) }}</span>
                    </div>

                    <!-- Reactive Comments Drawer -->
                    <div x-show="commentsOpen" x-cloak class="p-4 sm:p-5 rounded-2xl bg-slate-950/70 border border-slate-800/80 space-y-4 transition-all">
                        
                        <!-- Existing Comments List -->
                        <div class="space-y-3">
                            <template x-for="c in clientComments" :key="c.id">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0">
                                        <span x-text="c.user.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-3 shadow-inner">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs font-bold text-white" x-text="c.user.name"></span>
                                                <span class="text-[10px] font-mono text-slate-500" x-text="c.created_at"></span>
                                            </div>
                                            <p class="text-xs text-slate-300 mt-1 leading-relaxed" x-text="c.content"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            @forelse($post->comments as $comment)
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-3 shadow-inner">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-bold text-white">{{ $comment->user->name ?? 'Owner' }}</span>
                                            <span class="text-[10px] font-mono text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-300 mt-1 leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div x-show="clientComments.length === 0" class="text-center py-3 text-xs text-slate-500 italic">
                                Belum ada komentar. Jadilah yang pertama memberi respon!
                            </div>
                            @endforelse
                        </div>

                        <!-- Add Comment Input Form -->
                        <form class="flex items-center gap-2 pt-2 border-t border-slate-900" @submit.prevent="submitComment($el)">
                            @csrf
                            <input type="text" 
                                   name="content" 
                                   required 
                                   maxlength="1000" 
                                   placeholder="Tulis balasan atau apresiasi..."
                                   class="flex-1 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/40 transition">
                            <button type="submit" 
                                    class="p-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold transition shadow-sm cursor-pointer shrink-0">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>

                </article>
                @empty
                <!-- Empty State -->
                <div class="p-12 sm:p-16 rounded-3xl bg-slate-900/80 border border-dashed border-slate-800 text-center space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-slate-800/80 border border-slate-700/60 text-slate-500 flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="messages-square" class="w-8 h-8"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h4 class="text-base font-black text-white">Belum Ada Postingan Komunitas</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Jadilah yang pertama berbagi cerita bisnis, tips penjualan, atau kabar pembukaan outlet baru Anda kepada sesama pengusaha.
                        </p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
                {{ $posts->links() }}
            </div>
            @endif

        </div>

        <!-- Right Column: Sidebar & Community Guidelines (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card: Panduan & Etika Komunitas -->
            <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4 backdrop-blur-xl">
                <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-black text-white">Etika Komunitas Cooca</h3>
                </div>

                <ul class="space-y-2.5 text-xs text-slate-300">
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span>Saling menghargai dan mendukung pertumbuhan sesama pengusaha UMKM.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span>Bagikan tips nyata tentang efisiensi HPP, strategi stok, atau promo kasir.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                        <span>Hindari spam, promosi berlebihan, atau konten yang melanggar hukum.</span>
                    </li>
                </ul>
            </div>

            <!-- Card: Topik Diskusi Inspiratif -->
            <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-4 backdrop-blur-xl">
                <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-black text-white">Inspirasi Topik Hari Ini</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-1">
                        <span class="text-[10px] font-mono text-emerald-400 font-bold uppercase block">Efisiensi Biaya</span>
                        <p class="text-slate-300">"Bagaimana cara Anda menekan food waste atau barang rusak di gudang?"</p>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-1">
                        <span class="text-[10px] font-mono text-cyan-400 font-bold uppercase block">Strategi Kasir POS</span>
                        <p class="text-slate-300">"Promo bundling apa yang paling ampuh menaikkan rata-rata belanja kasir?"</p>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80 space-y-1">
                        <span class="text-[10px] font-mono text-amber-400 font-bold uppercase block">Motivasi Tim</span>
                        <p class="text-slate-300">"Tips menjaga semangat tim kasir dan staf dapur saat jam sibuk (rush hour)."</p>
                    </div>
                </div>
            </div>

            <!-- Card: Grup WhatsApp VIP Cooca Owner -->
            <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-950/60 via-slate-900 to-slate-900 border border-emerald-500/30 shadow-xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-white">Grup WhatsApp VIP Owner</h4>
                        <span class="text-[10px] text-emerald-400 font-mono">Khusus Pengguna Terverifikasi</span>
                    </div>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Terhubung langsung secara real-time dengan ratusan pemilik bisnis, diskusi santai, dan update rilis fitur eksklusif dari tim developer Cooca.
                </p>
                <a href="https://chat.whatsapp.com/" target="_blank" 
                   class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                    <span>Gabung Grup WhatsApp</span>
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </a>
            </div>

        </div>

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
            clientComments: [],
            csrfToken: document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '',

            async toggleLike() {
                try {
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
                } catch (e) {
                    console.error('Like error:', e);
                }
            },

            async submitComment(form) {
                const input = form.querySelector('input[name="content"]');
                const content = input.value.trim();
                if (!content) return;

                try {
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
                        input.value = '';
                        if (data.comment) {
                            this.clientComments.push(data.comment);
                            this.commentsCount = data.comments_count;
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (e) {
                    console.error('Comment error:', e);
                    window.location.reload();
                }
            }
        };
    }
</script>
@endpush
