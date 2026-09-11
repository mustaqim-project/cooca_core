@extends('layouts.admin', [
    'title' => 'Edit Artikel — Admin Console',
    'headerTitle' => 'Edit Artikel: ' . Str::limit($post->title, 40),
    'headerSubtitle' => 'Perbarui konten, optimasi SEO, atau ubah status publikasi artikel'
])

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.posts.index') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i><span>Kembali ke Daftar Artikel</span>
        </a>
        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
            <span>Lihat di Website</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="1.5"></i>
        </a>
    </div>

    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8">
        <form action="{{ route('admin.posts.update', $post) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Judul Artikel *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    @error('title') <span class="text-[12px] text-[#FF3B30] dark:text-[#FF453A]">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Slug URL *</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    @error('slug') <span class="text-[12px] text-[#FF3B30] dark:text-[#FF453A]">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Cluster Konten *</label>
                        <select name="cluster" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="tutorial" {{ old('cluster', $post->cluster) === 'tutorial' ? 'selected' : '' }}>Cluster K — Tutorial "Cara"</option>
                            <option value="edukasi" {{ old('cluster', $post->cluster) === 'edukasi' ? 'selected' : '' }}>Cluster O — Edukasi Topikal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kategori *</label>
                        <input type="text" name="category" value="{{ old('category', $post->category) }}" required class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Penulis</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $post->author_name) }}" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">URL Cover Image</label>
                        <input type="url" name="cover_image" value="{{ old('cover_image', $post->cover_image) }}" class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Ringkasan / Excerpt</label>
                    <textarea name="excerpt" rows="2" class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Konten Lengkap (HTML Didukung) *</label>
                    <textarea name="content" rows="14" required class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ old('content', $post->content) }}</textarea>
                    @error('content') <span class="text-[12px] text-[#FF3B30] dark:text-[#FF453A]">{{ $message }}</span> @enderror
                </div>
                <div class="p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] space-y-3">
                    <span class="text-[12px] font-semibold text-[#5856D6] dark:text-[#5E5CE6] block">Pengaturan SEO (Meta Tags)</span>
                    <div>
                        <label class="block text-[13px] text-black/60 dark:text-white/60 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[13px] text-black/60 dark:text-white/60 mb-1">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }} class="rounded-[4px] border-black/20 text-[#007AFF] dark:text-[#0A84FF] focus:ring-[#007AFF]/30">
                    <label for="is_published" class="text-[13px] font-medium text-black dark:text-white cursor-pointer">Status Published (Tampil di Website)</label>
                </div>
            </div>

            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex justify-end gap-3">
                <a href="{{ route('admin.posts.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i>Batal</a>
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i>Perbarui Artikel</button>
            </div>
        </form>
    </div>
</div>
@endsection