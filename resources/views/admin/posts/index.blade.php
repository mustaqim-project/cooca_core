@extends('layouts.admin', [
    'title' => 'CMS Artikel & Edukasi — Admin Console',
    'headerTitle' => 'CMS Artikel & Edukasi UMKM',
    'headerSubtitle' => 'Kelola publikasi artikel blog, tutorial cara (Cluster K), dan materi edukasi topikal (Cluster O)'
])

@section('content')
<div class="space-y-6">

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Total Artikel</span>
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $totalPosts }}</span>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center"><i data-lucide="file-text" class="w-5 h-5" stroke-width="1.5"></i></div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158] block">Cluster K (Tutorial)</span>
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $totalTutorial }}</span>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center"><i data-lucide="wrench" class="w-5 h-5" stroke-width="1.5"></i></div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
            <div>
                <span class="text-[12px] font-medium text-[#30B0C7] dark:text-[#40C8E0] block">Cluster O (Edukasi)</span>
                <span class="text-[24px] font-bold tabular-nums text-[#30B0C7] dark:text-[#40C8E0]">{{ $totalEdukasi }}</span>
            </div>
            <div class="w-10 h-10 rounded-[10px] bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center"><i data-lucide="graduation-cap" class="w-5 h-5" stroke-width="1.5"></i></div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul artikel atau kategori..." class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <select name="cluster" onchange="this.form.submit()" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                <option value="">Semua Cluster</option>
                <option value="tutorial" {{ request('cluster') === 'tutorial' ? 'selected' : '' }}>Tutorial (Cluster K)</option>
                <option value="edukasi" {{ request('cluster') === 'edukasi' ? 'selected' : '' }}>Edukasi (Cluster O)</option>
            </select>
        </form>
        <a href="{{ route('admin.posts.create') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] shrink-0">
            <i data-lucide="plus" class="w-4 h-4" stroke-width="1.5"></i><span>Tulis Artikel Baru</span>
        </a>
    </div>

    <!-- Posts Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Judul &amp; Kategori</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Cluster</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Pembaca</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Tanggal Publish</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($posts as $post)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 max-w-xs sm:max-w-sm">
                            <div class="font-medium text-black dark:text-white leading-snug line-clamp-2">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="flex items-center gap-1.5 hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">
                                    <span>{{ $post->title }}</span>
                                    <i data-lucide="external-link" class="w-3 h-3 text-black/40 dark:text-white/40 shrink-0" stroke-width="1.5"></i>
                                </a>
                            </div>
                            <span class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5">{{ $post->category }} • {{ $post->author_name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $post->cluster === 'tutorial' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#30B0C7]/12 text-[#1F7A89] dark:text-[#40C8E0]' }}"><span class="w-1.5 h-1.5 rounded-full"></span>{{ $post->cluster === 'tutorial' ? 'Cluster K' : 'Cluster O' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.posts.toggle-status', $post) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $post->is_published ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}"><span class="w-1.5 h-1.5 rounded-full"></span>{{ $post->is_published ? 'Published' : 'Draft' }}</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">{{ number_format($post->views_count) }} views</td>
                        <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/8 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1"><i data-lucide="pencil" class="w-3.5 h-3.5" stroke-width="1.5"></i>Edit</a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus artikel ini secara permanen?', 'Hapus Artikel?', 'danger')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/8 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1"><i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="1.5"></i>Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center"><p class="text-[15px] font-semibold text-black dark:text-white">Belum ada artikel</p><p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Belum ada artikel yang ditambahkan.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($posts->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection