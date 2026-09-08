@extends('layouts.admin', [
    'title' => 'CMS Artikel & Edukasi — Admin Console',
    'headerTitle' => 'CMS Artikel & Edukasi UMKM',
    'headerSubtitle' => 'Kelola publikasi artikel blog, tutorial cara (Cluster K), dan materi edukasi topikal (Cluster O)'
])

@section('content')
<div class="space-y-6">

    <!-- Flash Message -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-3">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-card p-4 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Artikel</span>
                <span class="text-2xl font-black text-white font-mono">{{ $totalPosts }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-400 block">Cluster K (Tutorial)</span>
                <span class="text-2xl font-black text-emerald-400 font-mono">{{ $totalTutorial }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="wrench" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold text-cyan-400 block">Cluster O (Edukasi)</span>
                <span class="text-2xl font-black text-cyan-400 font-mono">{{ $totalEdukasi }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center">
                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Top Action Bar -->
    <div class="glass-card p-4 rounded-2xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="flex items-center gap-3 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul artikel atau kategori..." class="w-full pl-10 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
            </div>
            <select name="cluster" onchange="this.form.submit()" class="px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none">
                <option value="">Semua Cluster</option>
                <option value="tutorial" {{ request('cluster') === 'tutorial' ? 'selected' : '' }}>Tutorial (Cluster K)</option>
                <option value="edukasi" {{ request('cluster') === 'edukasi' ? 'selected' : '' }}>Edukasi (Cluster O)</option>
            </select>
        </form>

        <a href="{{ route('admin.posts.create') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 transition-all shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tulis Artikel Baru</span>
        </a>
    </div>

    <!-- Posts Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Judul &amp; Kategori</th>
                        <th class="py-3.5 px-4 font-semibold">Cluster</th>
                        <th class="py-3.5 px-4 font-semibold">Status</th>
                        <th class="py-3.5 px-4 font-semibold">Pembaca</th>
                        <th class="py-3.5 px-4 font-semibold">Tanggal Publish</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($posts as $post)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 max-w-xs sm:max-w-sm">
                            <div class="font-bold text-white leading-snug line-clamp-2">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="hover:text-indigo-400 transition-colors flex items-center gap-1.5">
                                    <span>{{ $post->title }}</span>
                                    <i data-lucide="external-link" class="w-3 h-3 text-slate-500 shrink-0"></i>
                                </a>
                            </div>
                            <span class="text-[10px] text-slate-500 block mt-0.5">{{ $post->category }} • {{ $post->author_name }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $post->cluster === 'tutorial' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }}">
                                {{ $post->cluster === 'tutorial' ? 'Cluster K' : 'Cluster O' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <form action="{{ route('admin.posts.toggle-status', $post) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $post->is_published ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $post->is_published ? 'Published' : 'Draft' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-300">
                            {{ number_format($post->views_count) }} views
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:border-indigo-500 transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Hapus artikel ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-rose-400 hover:bg-rose-950/40 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                            Belum ada artikel yang ditambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
        <div class="p-4 border-t border-slate-800/80">
            {{ $posts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
