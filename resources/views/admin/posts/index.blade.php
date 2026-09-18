@extends('layouts.admin', [
    'title' => 'CMS Artikel & Edukasi - Admin Console',
    'headerTitle' => 'CMS Artikel & Edukasi UMKM',
    'headerSubtitle' => 'Kelola publikasi artikel blog, taksonomi kategori, dan cluster konten',
])

@section('content')
<div class="max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10 space-y-6">

    <!-- KPI Metric Cards (Bento HIG) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Posts -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Total Artikel</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-[26px] sm:text-[28px] font-bold tabular-nums tracking-tight text-black dark:text-white">{{ $totalPosts }}</span>
                    <span class="text-[11px] font-medium text-black/40 dark:text-white/40">({{ $publishedPosts }} publish)</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-5 h-5" stroke-width="1.5"></i>
            </div>
        </div>

        <!-- Cluster K -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Cluster K (Tutorial)</span>
                <span class="text-[26px] sm:text-[28px] font-bold tabular-nums tracking-tight text-[#34C759] dark:text-[#30D158] block mt-1">{{ $totalTutorial }}</span>
            </div>
            <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                <i data-lucide="wrench" class="w-5 h-5" stroke-width="1.5"></i>
            </div>
        </div>

        <!-- Cluster O -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Cluster O (Edukasi)</span>
                <span class="text-[26px] sm:text-[28px] font-bold tabular-nums tracking-tight text-[#30B0C7] dark:text-[#40C8E0] block mt-1">{{ $totalEdukasi }}</span>
            </div>
            <div class="w-11 h-11 rounded-[14px] bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center shrink-0">
                <i data-lucide="graduation-cap" class="w-5 h-5" stroke-width="1.5"></i>
            </div>
        </div>

        <!-- Categories & Clusters -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Taksonomi Konten</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-[26px] sm:text-[28px] font-bold tabular-nums tracking-tight text-[#5856D6] dark:text-[#5E5CE6]">{{ $totalCategories }}</span>
                    <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Kategori • {{ $totalClusters }} Cluster</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-[14px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                <i data-lucide="folder-tree" class="w-5 h-5" stroke-width="1.5"></i>
            </div>
        </div>
    </div>

    <!-- Apple HIG Segmented Control Navigation Tabs -->
    <div class="flex items-center justify-between gap-3 overflow-x-auto no-scrollbar pb-1">
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] backdrop-blur-md border border-black/[0.04] dark:border-white/[0.04]">
            <a href="{{ route('admin.posts.index', ['tab' => 'posts']) }}"
                class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all inline-flex items-center gap-2 {{ $activeTab === 'posts' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="file-text" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Semua Artikel</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'posts' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">{{ $totalPosts }}</span>
            </a>
            <a href="{{ route('admin.posts.index', ['tab' => 'categories']) }}"
                class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all inline-flex items-center gap-2 {{ $activeTab === 'categories' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="tag" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Kategori Post</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'categories' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">{{ $totalCategories }}</span>
            </a>
            <a href="{{ route('admin.posts.index', ['tab' => 'clusters']) }}"
                class="px-4 py-2 rounded-[10px] text-[13px] font-medium transition-all inline-flex items-center gap-2 {{ $activeTab === 'clusters' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="layers" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Cluster Konten</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'clusters' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">{{ $totalClusters }}</span>
            </a>
        </div>

        @if($activeTab === 'posts')
            <a href="{{ route('admin.posts.create') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] shrink-0">
                <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                <span>Tulis Artikel Baru</span>
            </a>
        @elseif($activeTab === 'categories')
            <button type="button" onclick="openCategoryModal()"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] shrink-0">
                <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                <span>Tambah Kategori</span>
            </button>
        @else
            <button type="button" onclick="openClusterModal()"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] shrink-0">
                <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                <span>Tambah Cluster</span>
            </button>
        @endif
    </div>

    <!-- TAB 1: DAFTAR ARTIKEL -->
    @if($activeTab === 'posts')
        <!-- Search & Filter Toolbar -->
        <div class="p-3 sm:p-4 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)]">
            <form method="GET" action="{{ route('admin.posts.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-2.5">
                <input type="hidden" name="tab" value="posts">
                
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari judul artikel, topik, atau kata kunci..."
                        class="w-full h-10 pl-10 pr-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                    <select name="cluster" onchange="this.form.submit()"
                        class="h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[13px] text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 flex-1 sm:flex-initial">
                        <option value="">Semua Cluster</option>
                        @foreach($clusters as $c)
                            <option value="{{ $c->code }}" {{ request('cluster') === $c->code ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="category_id" onchange="this.form.submit()"
                        class="h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[13px] text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 flex-1 sm:flex-initial">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()"
                        class="h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[13px] text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 flex-1 sm:flex-initial">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>

                    @if(request('search') || request('cluster') || request('category_id') || request('status'))
                        <a href="{{ route('admin.posts.index', ['tab' => 'posts']) }}"
                            class="h-10 px-3 rounded-[12px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition flex items-center justify-center shrink-0">
                            <i data-lucide="x" class="w-4 h-4 mr-1" stroke-width="1.5"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Posts Bento Table -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Judul &amp; Taksonomi</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Cluster</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider text-right">Views</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Terbit</th>
                            <th class="px-5 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($posts as $post)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-4 max-w-sm">
                                    <div class="font-semibold text-black dark:text-white leading-snug line-clamp-2">
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">
                                            <span>{{ $post->title }}</span>
                                            <i data-lucide="external-link" class="w-3.5 h-3.5 text-black/35 dark:text-white/35 shrink-0" stroke-width="1.5"></i>
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                            {{ $post->postCategory?->name ?? $post->category ?? 'Umum' }}
                                        </span>
                                        <span class="text-[11px] text-black/40 dark:text-white/40">
                                            oleh {{ $post->author_name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($post->cluster === 'tutorial' || ($post->postCluster && $post->postCluster->code === 'tutorial'))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="wrench" class="w-3 h-3" stroke-width="2"></i>
                                            <span>Tutorial K</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-[#30B0C7]/12 text-[#1F7A89] dark:text-[#40C8E0]">
                                            <i data-lucide="graduation-cap" class="w-3 h-3" stroke-width="2"></i>
                                            <span>Edukasi O</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <form action="{{ route('admin.posts.toggle-status', $post) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11px] font-semibold transition-all active:scale-[0.96] {{ $post->is_published ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:bg-black/[0.1] dark:hover:bg-white/[0.12]' }}">
                                            <i data-lucide="{{ $post->is_published ? 'check-circle' : 'circle' }}" class="w-3 h-3" stroke-width="2"></i>
                                            <span>{{ $post->is_published ? 'Published' : 'Draft' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums whitespace-nowrap font-medium text-black/70 dark:text-white/70">
                                    {{ number_format($post->views_count) }}
                                </td>
                                <td class="px-4 py-4 text-[12px] text-black/50 dark:text-white/50 tabular-nums whitespace-nowrap">
                                    {{ $post->published_at ? $post->published_at->translatedFormat('d M Y') : 'Belum Terbit' }}
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.posts.edit', $post) }}"
                                            class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus artikel ini secara permanen? Data yang sudah dihapus tidak dapat dipulihkan.', 'Hapus Artikel?', 'danger')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="file-x" class="w-6 h-6" stroke-width="1.5"></i>
                                    </div>
                                    <p class="text-[15px] font-semibold text-black dark:text-white">Tidak Ada Artikel</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-sm mx-auto">
                                        Tidak ditemukan artikel yang sesuai dengan filter pencarian. Mulai tulis artikel baru untuk mengedukasi pengguna Anda.
                                    </p>
                                    <div class="mt-4">
                                        <a href="{{ route('admin.posts.create') }}"
                                            class="inline-flex items-center gap-2 h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition">
                                            <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                                            <span>Tulis Artikel Baru</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($posts->hasPages())
                <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>

    <!-- TAB 2: KATEGORI POST -->
    @elseif($activeTab === 'categories')
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Daftar Kategori Artikel</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Taksonomi topik artikel yang digunakan untuk filter dan pengelompokan panduan</p>
                </div>
                <button type="button" onclick="openCategoryModal()"
                    class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_4px_rgba(0,122,255,0.2)]">
                    <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                    <span>Kategori Baru</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Nama Kategori</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Slug URL</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider text-center">Jumlah Artikel</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($categories as $category)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/70 dark:text-white/70">
                                            <i data-lucide="{{ $category->icon ?: 'tag' }}" class="w-4 h-4" stroke-width="1.5"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-black dark:text-white block">{{ $category->name }}</span>
                                            <span class="text-[11px] text-black/40 dark:text-white/40">Urutan: {{ $category->sort_order }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-mono text-[12px] text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $category->slug }}
                                </td>
                                <td class="px-4 py-4 text-black/70 dark:text-white/70 max-w-xs truncate">
                                    {{ $category->description ?: '-' }}
                                </td>
                                <td class="px-4 py-4 text-center tabular-nums font-semibold text-black dark:text-white">
                                    <a href="{{ route('admin.posts.index', ['tab' => 'posts', 'category_id' => $category->id]) }}"
                                        class="px-2 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-[#007AFF]/10 hover:text-[#007AFF] transition">
                                        {{ $category->posts_count }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold {{ $category->is_active ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/6 dark:bg-white/8 text-black/50 dark:text-white/50' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                            onclick='editCategory(@json($category))'
                                            class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                            <span>Edit</span>
                                        </button>
                                        <form action="{{ route('admin.posts.categories.destroy', $category) }}" method="POST"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus kategori {{ $category->name }}? Artikel terkait akan tetap ada namun tidak memiliki kategori.', 'Hapus Kategori?', 'danger')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-black/50 dark:text-white/50">
                                    Belum ada data kategori. Klik tombol "Kategori Baru" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- TAB 3: CLUSTER KONTEN -->
    @else
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Daftar Cluster Konten</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Pengelompokan pilar strategi konten (Cluster K untuk panduan cara dan Cluster O untuk edukasi topikal)</p>
                </div>
                <button type="button" onclick="openClusterModal()"
                    class="h-9 px-3.5 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-[0_1px_4px_rgba(0,122,255,0.2)]">
                    <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                    <span>Cluster Baru</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Kode &amp; Nama</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Slug URL</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Deskripsi Pilar</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider text-center">Jumlah Artikel</th>
                            <th class="px-4 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($clusters as $cluster)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/70 dark:text-white/70">
                                            <i data-lucide="{{ $cluster->icon ?: 'layers' }}" class="w-4 h-4" stroke-width="1.5"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-black dark:text-white block">{{ $cluster->name }}</span>
                                            <span class="text-[11px] font-mono text-black/50 dark:text-white/50">Code: {{ $cluster->code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-mono text-[12px] text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $cluster->slug }}
                                </td>
                                <td class="px-4 py-4 text-black/70 dark:text-white/70 max-w-sm truncate">
                                    {{ $cluster->description ?: '-' }}
                                </td>
                                <td class="px-4 py-4 text-center tabular-nums font-semibold text-black dark:text-white">
                                    <a href="{{ route('admin.posts.index', ['tab' => 'posts', 'cluster' => $cluster->code]) }}"
                                        class="px-2 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-[#007AFF]/10 hover:text-[#007AFF] transition">
                                        {{ $cluster->posts_count }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold {{ $cluster->is_active ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/6 dark:bg-white/8 text-black/50 dark:text-white/50' }}">
                                        {{ $cluster->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                            onclick='editCluster(@json($cluster))'
                                            class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                            <span>Edit</span>
                                        </button>
                                        @if(!in_array($cluster->code, ['tutorial', 'edukasi']))
                                            <form action="{{ route('admin.posts.clusters.destroy', $cluster) }}" method="POST"
                                                onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus cluster {{ $cluster->name }}? Artikel terkait akan tetap ada.', 'Hapus Cluster?', 'danger')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-black/50 dark:text-white/50">
                                    Belum ada data cluster.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<!-- MODAL SHEET: Kategori Baru / Edit -->
<div id="categoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.3)] space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
            <h3 id="categoryModalTitle" class="text-[16px] font-bold text-black dark:text-white">Tambah Kategori Baru</h3>
            <button type="button" onclick="closeCategoryModal()" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center hover:bg-black/[0.1]">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="categoryForm" method="POST" action="{{ route('admin.posts.categories.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" id="categoryMethod" name="_method" value="POST">

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Kategori *</label>
                <input type="text" id="catName" name="name" required placeholder="Contoh: Operasional & Stok"
                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Slug URL (Opsional)</label>
                <input type="text" id="catSlug" name="slug" placeholder="operasional-dan-stok"
                    class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Deskripsi Ringkas</label>
                <textarea id="catDesc" name="description" rows="2" placeholder="Deskripsi ringkas fokus kategori ini..."
                    class="w-full px-3.5 py-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Icon Lucide</label>
                    <input type="text" id="catIcon" name="icon" placeholder="tag, box, dollar-sign" value="tag"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Urutan Sort</label>
                    <input type="number" id="catSort" name="sort_order" placeholder="0" value="0"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" onclick="closeCategoryModal()"
                    class="h-9 px-4 rounded-[10px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08]">
                    Batal
                </button>
                <button type="submit" id="categorySubmitBtn"
                    class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SHEET: Cluster Baru / Edit -->
<div id="clusterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.3)] space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
            <h3 id="clusterModalTitle" class="text-[16px] font-bold text-black dark:text-white">Tambah Cluster Baru</h3>
            <button type="button" onclick="closeClusterModal()" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center hover:bg-black/[0.1]">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="clusterForm" method="POST" action="{{ route('admin.posts.clusters.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" id="clusterMethod" name="_method" value="POST">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Kode Cluster *</label>
                    <input type="text" id="clusCode" name="code" required placeholder="tutorial / edukasi"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Cluster *</label>
                    <input type="text" id="clusName" name="name" required placeholder="Cluster K - Tutorial"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Deskripsi Pilar</label>
                <textarea id="clusDesc" name="description" rows="2" placeholder="Fungsi pilar konten ini..."
                    class="w-full px-3.5 py-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Icon Lucide</label>
                    <input type="text" id="clusIcon" name="icon" placeholder="layers, wrench" value="layers"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Urutan Sort</label>
                    <input type="number" id="clusSort" name="sort_order" placeholder="0" value="0"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" onclick="closeClusterModal()"
                    class="h-9 px-4 rounded-[10px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08]">
                    Batal
                </button>
                <button type="submit" id="clusterSubmitBtn"
                    class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm">
                    Simpan Cluster
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCategoryModal() {
        document.getElementById('categoryModalTitle').innerText = 'Tambah Kategori Baru';
        document.getElementById('categoryForm').action = "{{ route('admin.posts.categories.store') }}";
        document.getElementById('categoryMethod').value = 'POST';
        document.getElementById('catName').value = '';
        document.getElementById('catSlug').value = '';
        document.getElementById('catDesc').value = '';
        document.getElementById('catIcon').value = 'tag';
        document.getElementById('catSort').value = '0';
        document.getElementById('categorySubmitBtn').innerText = 'Simpan Kategori';
        
        const modal = document.getElementById('categoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function editCategory(category) {
        document.getElementById('categoryModalTitle').innerText = 'Edit Kategori: ' + category.name;
        document.getElementById('categoryForm').action = "/admin/posts/categories/" + category.id;
        document.getElementById('categoryMethod').value = 'PUT';
        document.getElementById('catName').value = category.name || '';
        document.getElementById('catSlug').value = category.slug || '';
        document.getElementById('catDesc').value = category.description || '';
        document.getElementById('catIcon').value = category.icon || 'tag';
        document.getElementById('catSort').value = category.sort_order || 0;
        document.getElementById('categorySubmitBtn').innerText = 'Perbarui Kategori';

        const modal = document.getElementById('categoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openClusterModal() {
        document.getElementById('clusterModalTitle').innerText = 'Tambah Cluster Baru';
        document.getElementById('clusterForm').action = "{{ route('admin.posts.clusters.store') }}";
        document.getElementById('clusterMethod').value = 'POST';
        document.getElementById('clusCode').value = '';
        document.getElementById('clusName').value = '';
        document.getElementById('clusDesc').value = '';
        document.getElementById('clusIcon').value = 'layers';
        document.getElementById('clusSort').value = '0';
        document.getElementById('clusterSubmitBtn').innerText = 'Simpan Cluster';

        const modal = document.getElementById('clusterModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function editCluster(cluster) {
        document.getElementById('clusterModalTitle').innerText = 'Edit Cluster: ' + cluster.name;
        document.getElementById('clusterForm').action = "/admin/posts/clusters/" + cluster.id;
        document.getElementById('clusterMethod').value = 'PUT';
        document.getElementById('clusCode').value = cluster.code || '';
        document.getElementById('clusName').value = cluster.name || '';
        document.getElementById('clusDesc').value = cluster.description || '';
        document.getElementById('clusIcon').value = cluster.icon || 'layers';
        document.getElementById('clusSort').value = cluster.sort_order || 0;
        document.getElementById('clusterSubmitBtn').innerText = 'Perbarui Cluster';

        const modal = document.getElementById('clusterModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeClusterModal() {
        const modal = document.getElementById('clusterModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const catModal = document.getElementById('categoryModal');
        const clusModal = document.getElementById('clusterModal');
        if (e.target === catModal) closeCategoryModal();
        if (e.target === clusModal) closeClusterModal();
    });
</script>
@endpush
@endsection
