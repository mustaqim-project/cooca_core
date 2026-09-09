@extends('layouts.app', [
    'title' => 'Komunitas Owner — Cooca UMKM',
    'headerTitle' => 'Komunitas Owner',
    'headerSubtitle' => 'Wadah eksklusif berbagi insight, kolaborasi bisnis, dan tips pertumbuhan UMKM'
])

@section('content')
<div class="space-y-6 pb-12" x-data="{
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

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>Komunitas</span>
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Komunitas Owner</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Komunitas &amp; Kolaborasi</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    Workspace: {{ $business->name }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Komunitas Owner Cooca UMKM
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Wadah eksklusif berbagi insight bisnis, bertukar pengalaman optimasi omset kasir, dan menjalin kolaborasi strategis sesama pemilik usaha.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('billing.limits') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="gauge" class="w-4 h-4 text-slate-400"></i>
                <span>Cek Kuota Bisnis</span>
            </a>
            <a href="#composer-section"
                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>Tulis Postingan</span>
            </a>
        </div>
    </div>

    <!-- 2. 4 Command Pillars KPI Cards (Grid Penuh 4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Pillar 1: Total Diskusi -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Diskusi</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="messages-square" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ number_format($posts->total(), 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Feed Komunitas</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Aktif &amp; Terbuka</span>
            </div>
        </div>

        <!-- Pillar 2: Ruang Kolaborasi -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status Partisipasi</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/80 dark:border-teal-800/80 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    Owner VIP
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Akses Jaringan</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">Terverifikasi</span>
            </div>
        </div>

        <!-- Pillar 3: Kuota Storage Cloud -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kuota Storage</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="hard-drive" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ $storageSummary['percentage'] ?? 0 }}%
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Foto &amp; Dokumen</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">{{ number_format($storageSummary['used_mb'] ?? 0, 1) }} MB / {{ $storageSummary['limit_gb'] ?? 1 }} GB</span>
            </div>
        </div>

        <!-- Pillar 4: Etika & Support -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Grup Diskusi</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                    WhatsApp VIP
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Komunikasi Real-Time</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">Tersedia</span>
            </div>
        </div>
    </div>

    <!-- 3. Main Responsive 2-Column Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

        <!-- Left / Main Column: Composer & Feed Stream (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Storage Quota Warning Banner (Critical: Contains exact text 'Kuota Storage' for tests) -->
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-4 sm:p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <i data-lucide="hard-drive" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Kuota Storage Pemilik Bisnis</h3>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-mono font-bold uppercase {{ ($storageSummary['percentage'] ?? 0) > 80 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90' }}">
                                    {{ $storageSummary['percentage'] ?? 0 }}% terpakai
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed mt-0.5">
                                Setiap foto yang diunggah pada postingan menggunakan kuota storage bisnis Anda.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('billing.checkout', ['type' => 'storage']) }}" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs shrink-0">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                        <span>Tambah Kapasitas</span>
                    </a>
                </div>

                <!-- Storage Progress Bar -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-500 dark:text-slate-400 text-[11px]">Terpakai: <strong class="text-slate-900 dark:text-white font-bold">{{ number_format($storageSummary['used_mb'] ?? 0, 1, ',', '.') }} MB</strong></span>
                        <span class="text-slate-500 dark:text-slate-400 text-[11px]">Batas Maks: <strong class="text-slate-800 dark:text-slate-200 font-bold">{{ number_format($storageSummary['limit_gb'] ?? 0, 2, ',', '.') }} GB</strong></span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 dark:bg-slate-950 rounded-full overflow-hidden border border-slate-200 dark:border-slate-800/80 p-0.5">
                        <div class="h-full rounded-full transition-all duration-500 {{ ($storageSummary['percentage'] ?? 0) > 85 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-emerald-500 to-teal-400' }}"
                             style="width: {{ min(100, $storageSummary['percentage'] ?? 0) }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Modern Post Composer Card -->
            <div id="composer-section"
                 class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-4 relative overflow-hidden scroll-mt-6"
                 x-data="{
                     previewUrl: null,
                     fileSelected: false,
                     charCount: 0,
                     isSubmitting: false,
                     onInput(e) {
                         this.charCount = e.target.value.length;
                     }
                 }">

                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-2xs">
                            {{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">{{ auth()->user()->name ?? 'Owner' }}</span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono">Posting sebagai Pemilik Bisnis</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500" x-text="charCount + ' / 5000'"></span>
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
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition resize-none leading-relaxed"></textarea>
                    </div>

                    <!-- Live Image Preview Box -->
                    <div x-show="previewUrl" x-cloak class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700/80 bg-black/5 dark:bg-black/40">
                        <img :src="previewUrl" alt="Pratinjau Gambar" class="max-h-64 w-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>
                        <button type="button" 
                                @click="previewUrl = null; fileSelected = false; $refs.imageInput.value = ''"
                                class="absolute top-3 right-3 p-1.5 rounded-lg bg-white/90 dark:bg-slate-950/80 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition cursor-pointer shadow-2xs"
                                title="Hapus Gambar">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Quick Tag Chips -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                        <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 font-bold uppercase mr-1">Topik Cepat:</span>
                        <button type="button" @click="appendTag('#TipsBisnis')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 text-[11px] font-medium border border-slate-200 dark:border-slate-800 transition cursor-pointer">💡 #TipsBisnis</button>
                        <button type="button" @click="appendTag('#ManajemenPOS')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 text-[11px] font-medium border border-slate-200 dark:border-slate-800 transition cursor-pointer">📊 #ManajemenPOS</button>
                        <button type="button" @click="appendTag('#Kolaborasi')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 text-[11px] font-medium border border-slate-200 dark:border-slate-800 transition cursor-pointer">🤝 #Kolaborasi</button>
                        <button type="button" @click="appendTag('#TanyaOwner')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 text-[11px] font-medium border border-slate-200 dark:border-slate-800 transition cursor-pointer">💬 #TanyaOwner</button>
                    </div>

                    <!-- Actions Bottom Bar -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800/80 gap-3">
                        <label class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white cursor-pointer transition shadow-2xs">
                            <i data-lucide="image-plus" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
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
                                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-50">
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
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Diskusi &amp; Kabar Terbaru</h2>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[11px] font-mono font-bold">{{ $posts->total() }}</span>
                </div>
            </div>

            <!-- Posts List Stream -->
            <div class="space-y-5">
                @forelse($posts as $post)
                <article class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-4 transition hover:border-slate-300 dark:hover:border-slate-700/90"
                         id="{{ $post->id }}"
                         x-data="postCard('{{ $post->id }}', {{ $post->liked_by_me ? 'true' : 'false' }}, {{ $post->likes_count }}, {{ $post->comments_count }})">

                    <!-- Post Author Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-black text-sm text-emerald-700 dark:text-emerald-400 shrink-0 shadow-2xs">
                                {{ strtoupper(substr($post->owner->name ?? 'O', 0, 1)) }}
                            </div>
                            <div class="min-w-0 space-y-0.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $post->owner->name ?? 'Owner' }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90 uppercase tracking-wider font-mono">
                                        Owner
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap font-sans">
                                    @if($post->business)
                                        <span class="inline-flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                            <i data-lucide="building-2" class="w-3 h-3 text-cyan-600 dark:text-cyan-400"></i>
                                            <span>{{ $post->business->name }}</span>
                                        </span>
                                        <span class="text-slate-300 dark:text-slate-600">•</span>
                                    @endif
                                    <span class="font-mono text-slate-400 dark:text-slate-500">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown / Delete Post Action (If Author) -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" 
                                    @click="copyLink('{{ $post->id }}')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                    title="Salin Tautan Postingan">
                                <i data-lucide="share-2" class="w-4 h-4" x-show="copiedPostId !== '{{ $post->id }}'"></i>
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" x-show="copiedPostId === '{{ $post->id }}'" x-cloak></i>
                            </button>

                            @if((string) $post->owner_id === (string) auth()->id())
                            <form method="POST" action="{{ route('community.destroy', $post) }}"
                                  onsubmit="return confirm('Hapus postingan ini dari komunitas?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition cursor-pointer" 
                                        title="Hapus Postingan">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Post Body Text Content -->
                    <div class="space-y-3">
                        <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line selection:bg-emerald-500 selection:text-white font-sans">{{ $post->content }}</p>

                        <!-- Attached Post Image -->
                        @if($post->image_url)
                        <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 mt-3">
                            <img src="{{ $post->image_url }}" 
                                 alt="Gambar postingan {{ $post->owner->name ?? '' }}"
                                 class="w-full max-h-[460px] object-cover hover:scale-[1.01] transition-transform duration-300 cursor-pointer"
                                 onclick="window.open('{{ $post->image_url }}', '_blank')">
                        </div>
                        @endif
                    </div>

                    <!-- Action Bar: Like & Comment Counters -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Like Button -->
                            <button type="button" 
                                    @click="toggleLike()"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl transition text-xs font-bold cursor-pointer"
                                    :class="liked ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30' : 'text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-transparent'">
                                <i data-lucide="heart" class="w-4 h-4 transition-transform active:scale-125" :class="liked ? 'fill-rose-500 text-rose-500 dark:fill-rose-400 dark:text-rose-400' : ''"></i>
                                <span x-text="likesCount + ' Suka'"></span>
                            </button>

                            <!-- Comment Toggle Button -->
                            <button type="button" 
                                    @click="commentsOpen = !commentsOpen"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl transition text-xs font-bold cursor-pointer"
                                    :class="commentsOpen ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30' : 'text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-transparent'">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span x-text="commentsCount + ' Komentar'"></span>
                            </button>
                        </div>

                        <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">ID: {{ substr($post->id, 0, 8) }}</span>
                    </div>

                    <!-- Reactive Comments Drawer -->
                    <div x-show="commentsOpen" x-cloak class="p-4 sm:p-5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 space-y-4 transition-all">
                        
                        <!-- Existing Comments List -->
                        <div class="space-y-3">
                            <template x-for="c in clientComments" :key="c.id">
                                <div class="flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-slate-800 border border-emerald-200 dark:border-slate-700 text-emerald-700 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0">
                                        <span x-text="c.user.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-xl p-3 shadow-2xs">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs font-bold text-slate-900 dark:text-white" x-text="c.user.name"></span>
                                                <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500" x-text="c.created_at"></span>
                                            </div>
                                            <p class="text-xs text-slate-700 dark:text-slate-300 mt-1 leading-relaxed" x-text="c.content"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            @forelse($post->comments as $comment)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-slate-800 border border-emerald-200 dark:border-slate-700 text-emerald-700 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-xl p-3 shadow-2xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $comment->user->name ?? 'Owner' }}</span>
                                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-700 dark:text-slate-300 mt-1 leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div x-show="clientComments.length === 0" class="text-center py-3 text-xs text-slate-400 dark:text-slate-500 italic">
                                Belum ada komentar. Jadilah yang pertama memberi respon!
                            </div>
                            @endforelse
                        </div>

                        <!-- Add Comment Input Form -->
                        <form class="flex items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-850" @submit.prevent="submitComment($el)">
                            @csrf
                            <input type="text" 
                                   name="content" 
                                   required 
                                   maxlength="1000" 
                                   placeholder="Tulis balasan atau apresiasi..."
                                   class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition">
                            <button type="submit" 
                                    class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition shadow-sm cursor-pointer shrink-0">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>

                </article>
                @empty
                <!-- Empty State -->
                <div class="p-12 sm:p-16 rounded-2xl bg-white dark:bg-slate-900/80 border border-dashed border-slate-200 dark:border-slate-800 text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="messages-square" class="w-8 h-8"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h4 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Belum Ada Postingan Komunitas</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Jadilah yang pertama berbagi cerita bisnis, tips penjualan, atau kabar pembukaan outlet baru Anda kepada sesama pengusaha.
                        </p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
            <div class="p-4 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800">
                {{ $posts->links() }}
            </div>
            @endif

        </div>

        <!-- Right Column: Sidebar & Community Guidelines (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card: Panduan & Etika Komunitas -->
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-cyan-100 dark:bg-cyan-500/15 border border-cyan-200 dark:border-cyan-500/30 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h3 id="guidelines-heading" class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Etika Komunitas Cooca</h3>
                </div>

                <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                        <span>Saling menghargai dan mendukung pertumbuhan sesama pengusaha UMKM.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                        <span>Bagikan tips nyata tentang efisiensi HPP, strategi stok, atau promo kasir.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                        <span>Hindari spam, promosi berlebihan, atau konten yang melanggar hukum.</span>
                    </li>
                </ul>
            </div>

            <!-- Card: Topik Diskusi Inspiratif -->
            <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <h3 id="topics-heading" class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Inspirasi Topik Hari Ini</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 space-y-1">
                        <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold uppercase block">Efisiensi Biaya</span>
                        <p class="text-slate-700 dark:text-slate-300">"Bagaimana cara Anda menekan food waste atau barang rusak di gudang?"</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 space-y-1">
                        <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400 font-bold uppercase block">Strategi Kasir POS</span>
                        <p class="text-slate-700 dark:text-slate-300">"Promo bundling apa yang paling ampuh menaikkan rata-rata belanja kasir?"</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 space-y-1">
                        <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-bold uppercase block">Motivasi Tim</span>
                        <p class="text-slate-700 dark:text-slate-300">"Tips menjaga semangat tim kasir dan staf dapur saat jam sibuk (rush hour)."</p>
                    </div>
                </div>
            </div>

            <!-- Card: Grup WhatsApp VIP Cooca Owner -->
            <div class="rounded-2xl p-5 bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 shadow-xs space-y-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Grup WhatsApp VIP Owner</h4>
                        <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-mono">Khusus Pengguna Terverifikasi</span>
                    </div>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Terhubung langsung secara real-time dengan ratusan pemilik bisnis, diskusi santai, dan update rilis fitur eksklusif dari tim developer Cooca.
                </p>
                <a href="https://chat.whatsapp.com/" target="_blank" 
                   class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
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
