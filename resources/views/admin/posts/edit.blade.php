@extends('layouts.admin', [
    'title' => 'Edit Artikel — Admin Console',
    'headerTitle' => 'Edit Artikel: ' . Str::limit($post->title, 40),
    'headerSubtitle' => 'Perbarui konten, optimasi SEO, atau ubah status publikasi artikel'
])

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.posts.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Artikel</span>
        </a>
        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1">
            <span>Lihat di Website</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <div class="glass-card p-6 sm:p-8 rounded-2xl">
        <form action="{{ route('admin.posts.update', $post) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Judul Artikel *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                    @error('title') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Slug URL *</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white font-mono text-xs focus:border-indigo-500 focus:outline-none">
                    @error('slug') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Cluster Konten *</label>
                        <select name="cluster" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="tutorial" {{ old('cluster', $post->cluster) === 'tutorial' ? 'selected' : '' }}>Cluster K — Tutorial "Cara"</option>
                            <option value="edukasi" {{ old('cluster', $post->cluster) === 'edukasi' ? 'selected' : '' }}>Cluster O — Edukasi Topikal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Kategori *</label>
                        <input type="text" name="category" value="{{ old('category', $post->category) }}" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Nama Penulis</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $post->author_name) }}" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">URL Cover Image</label>
                        <input type="url" name="cover_image" value="{{ old('cover_image', $post->cover_image) }}" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Ringkasan / Excerpt</label>
                    <textarea name="excerpt" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Konten Lengkap (HTML Didukung) *</label>
                    <textarea name="content" rows="14" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-white font-mono text-xs focus:border-indigo-500 focus:outline-none leading-relaxed">{{ old('content', $post->content) }}</textarea>
                    @error('content') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                    <span class="text-xs font-bold text-indigo-400 block uppercase tracking-wider">Pengaturan SEO (Meta Tags)</span>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white text-xs focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_published" class="text-xs font-bold text-white cursor-pointer">Status Published (Tampil di Website)</label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.posts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-800 text-slate-400 hover:text-white text-xs font-semibold">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20">Perbarui Artikel</button>
            </div>
        </form>
    </div>

</div>
@endsection
