@extends('layouts.admin', [
    'title' => 'Tulis Artikel Baru - Admin Console',
    'headerTitle' => 'Tulis Artikel & Panduan Edukasi Baru',
    'headerSubtitle' => 'Publikasikan panduan baru untuk Cluster K (Tutorial cara) atau Cluster O (Edukasi topikal)',
])

@push('head')
<!-- TinyMCE Free CDN Script -->
<script src="https://cdn.tiny.cloud/1/2a7oruubgvqukc1gach4pq8j3pm4q12ooot480ieevdxu15m/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
@endpush

@section('content')
<div class="max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10 space-y-6">

    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.posts.index') }}"
            class="text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white inline-flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i>
            <span>Kembali ke Daftar Artikel</span>
        </a>
    </div>

    <!-- Main Form -->
    <form action="{{ route('admin.posts.store') }}" method="POST" id="postForm"
        onsubmit="if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Main Editor & Metadata (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Main Content Card -->
                <div class="p-6 sm:p-7 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] space-y-5">
                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Judul Artikel <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="title" id="postTitle" value="{{ old('title') }}" required
                            placeholder="Contoh: Cara Menghitung HPP Usaha Kuliner 2026"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[16px] font-medium text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        @error('title')
                            <span class="text-[12px] text-[#FF3B30] dark:text-[#FF453A] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Slug URL (Opsional, otomatis dari judul)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px] font-mono text-black/40 dark:text-white/40">/blog/</span>
                            <input type="text" name="slug" id="postSlug" value="{{ old('slug') }}"
                                placeholder="cara-menghitung-hpp-usaha-kuliner"
                                class="w-full h-10 pl-16 pr-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        @error('slug')
                            <span class="text-[12px] text-[#FF3B30] dark:text-[#FF453A] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Ringkasan / Excerpt
                        </label>
                        <textarea name="excerpt" rows="2"
                            placeholder="Ringkasan 1-2 kalimat untuk kartu preview di halaman blog..."
                            class="w-full px-4 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('excerpt') }}</textarea>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Ditampilkan di feed blog utama dan kartu preview media sosial.</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/80 dark:text-white/80">
                                Isi Artikel Lengkap <span class="text-[#FF3B30]">*</span>
                            </label>
                            <span class="text-[11px] text-black/40 dark:text-white/40">TinyMCE WYSIWYG Editor</span>
                        </div>
                        
                        <!-- Rich Text Area with TinyMCE -->
                        <div class="rounded-[14px] overflow-hidden border border-black/[0.06] dark:border-white/[0.08]">
                            <textarea id="post-content" name="content" rows="16"
                                placeholder="Tuliskan isi artikel Anda di sini...">{{ old('content') }}</textarea>
                        </div>
                        @error('content')
                            <span class="text-[12px] text-[#FF3B30] dark:text-[#FF453A] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SEO Card -->
                <div class="p-6 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center">
                            <i data-lucide="search" class="w-4 h-4" stroke-width="1.5"></i>
                        </div>
                        <h4 class="text-[14px] font-bold text-black dark:text-white">Optimasi Mesin Pencari (SEO)</h4>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                            placeholder="Biarkan kosong untuk menggunakan judul artikel"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="2"
                            placeholder="Deskripsi ringkas yang tampil di hasil pencarian Google (maks 155 karakter)"
                            class="w-full px-3.5 py-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:ring-2 focus:ring-[#007AFF]/50">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Settings (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Publishing Options Card -->
                <div class="p-6 rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_24px_rgba(0,0,0,0.03)] space-y-5">
                    <h4 class="text-[14px] font-bold text-black dark:text-white pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                        Publikasi &amp; Taksonomi
                    </h4>

                    <!-- Cluster Konten -->
                    <div>
                        <label class="block text-[12px] font-semibold text-black/80 dark:text-white/80 mb-1.5">
                            Cluster Konten <span class="text-[#FF3B30]">*</span>
                        </label>
                        <select name="cluster_id" id="cluster_id" required
                            class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            @foreach($clusters as $clus)
                                <option value="{{ $clus->id }}" {{ old('cluster_id') == $clus->id || old('cluster') === $clus->code ? 'selected' : '' }}>
                                    {{ $clus->name }} ({{ $clus->code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Cluster K untuk Tutorial cara operasional, Cluster O untuk Edukasi topikal.</p>
                    </div>

                    <!-- Kategori with Inline Quick-Add -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[12px] font-semibold text-black/80 dark:text-white/80">
                                Kategori <span class="text-[#FF3B30]">*</span>
                            </label>
                            <button type="button" onclick="openQuickAddCategory()"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                <i data-lucide="plus" class="w-3 h-3" stroke-width="2"></i>
                                <span>Kategori Baru</span>
                            </button>
                        </div>
                        
                        <select name="category_id" id="category_select" required
                            class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] font-medium text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Penulis -->
                    <div>
                        <label class="block text-[12px] font-semibold text-black/80 dark:text-white/80 mb-1">
                            Nama Penulis
                        </label>
                        <input type="text" name="author_name" value="{{ old('author_name', 'Tim Edukasi COOCA') }}"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>

                    <!-- Cover Image URL -->
                    <div>
                        <label class="block text-[12px] font-semibold text-black/80 dark:text-white/80 mb-1">
                            URL Gambar Sampul (Cover Image)
                        </label>
                        <input type="url" name="cover_image" id="cover_image_input" value="{{ old('cover_image') }}"
                            placeholder="https://images.unsplash.com/..."
                            oninput="previewCoverImage(this.value)"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:ring-2 focus:ring-[#007AFF]/50">
                        
                        <!-- Image Preview Container -->
                        <div id="cover_preview_wrapper" class="mt-2.5 hidden">
                            <div class="relative w-full h-32 rounded-[12px] overflow-hidden bg-black/5 border border-black/10">
                                <img id="cover_preview_img" src="" alt="Cover preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    <!-- Published Status Switch (Apple Toggle Style) -->
                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <label class="flex items-center justify-between cursor-pointer group py-1">
                            <div>
                                <span class="text-[13px] font-semibold text-black dark:text-white block">Langsung Terbitkan</span>
                                <span class="text-[11px] text-black/50 dark:text-white/50 block">Artikel langsung tampil di blog publik</span>
                            </div>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="is_published" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-2">
                        <button type="submit"
                            class="w-full h-11 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                            <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                            <span>Terbitkan Artikel</span>
                        </button>
                        <a href="{{ route('admin.posts.index') }}"
                            class="w-full h-10 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition-all flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- QUICK ADD CATEGORY MODAL (Inline Modal) -->
<div id="quickCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/40 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-sm rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-5 shadow-[0_20px_50px_rgba(0,0,0,0.3)] space-y-4">
        <div class="flex items-center justify-between pb-2.5 border-b border-black/[0.06] dark:border-white/[0.08]">
            <h4 class="text-[15px] font-bold text-black dark:text-white">Tambah Kategori Baru</h4>
            <button type="button" onclick="closeQuickAddCategory()" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 flex items-center justify-center hover:bg-black/[0.1]">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>

        <form id="quickCategoryForm" onsubmit="submitQuickCategory(event)" class="space-y-3.5">
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Kategori *</label>
                <input type="text" id="quickCatName" required placeholder="Contoh: Pajak & Legalitas"
                    class="w-full h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Deskripsi Singkat</label>
                <input type="text" id="quickCatDesc" placeholder="Fokus pembahasan..."
                    class="w-full h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" onclick="closeQuickAddCategory()"
                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08]">
                    Batal
                </button>
                <button type="submit" id="quickCatSubmitBtn"
                    class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98]">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init TinyMCE Free Rich Text Editor
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#post-content',
                height: 520,
                menubar: false,
                branding: false,
                promotion: false,
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #1c1c1e; }',
                setup: function(editor) {
                    editor.on('change keyup', function() {
                        editor.save();
                    });
                }
            });
        }

        // Auto-generate slug from title if slug field is empty
        const titleInput = document.getElementById('postTitle');
        const slugInput = document.getElementById('postSlug');
        let slugEdited = false;

        slugInput.addEventListener('input', function() {
            slugEdited = this.value.trim().length > 0;
        });

        titleInput.addEventListener('input', function() {
            if (!slugEdited) {
                slugInput.value = titleInput.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        // Initial preview if cover image present
        const initCover = document.getElementById('cover_image_input').value;
        if (initCover) {
            previewCoverImage(initCover);
        }
    });

    function previewCoverImage(url) {
        const wrapper = document.getElementById('cover_preview_wrapper');
        const img = document.getElementById('cover_preview_img');
        if (url && url.startsWith('http')) {
            img.src = url;
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
            img.src = '';
        }
    }

    function openQuickAddCategory() {
        document.getElementById('quickCatName').value = '';
        document.getElementById('quickCatDesc').value = '';
        const modal = document.getElementById('quickCategoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeQuickAddCategory() {
        const modal = document.getElementById('quickCategoryModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function submitQuickCategory(e) {
        e.preventDefault();
        const name = document.getElementById('quickCatName').value.trim();
        const desc = document.getElementById('quickCatDesc').value.trim();
        const btn = document.getElementById('quickCatSubmitBtn');

        if (!name) return;

        btn.disabled = true;
        btn.innerText = 'Menyimpan...';

        fetch("{{ route('admin.posts.categories.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                description: desc
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = 'Simpan';
            if (data.success && data.category) {
                const select = document.getElementById('category_select');
                const option = new Option(data.category.name, data.category.id, true, true);
                select.add(option);
                closeQuickAddCategory();
            } else {
                alert(data.message || 'Gagal menyimpan kategori.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = 'Simpan';
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan kategori.');
        });
    }
</script>
@endpush
@endsection
