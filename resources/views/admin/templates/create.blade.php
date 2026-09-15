@extends('layouts.admin', [
    'title' => 'Upload Template Excel Baru - Admin Console',
    'headerTitle' => 'Upload Template Excel Baru',
    'headerSubtitle' => 'Unggah file spreadsheet asli (.xlsx/.xls/.csv) dan atur informasi publikasi untuk calon prospek UMKM',
])

@section('content')
    <div class="max-w-4xl">

        <div class="mb-5">
            <a href="{{ route('admin.templates.index') }}"
                class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/50 dark:text-white/50 hover:text-[#007AFF] transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Daftar Template</span>
            </a>
        </div>

        @if ($errors->any())
            <div
                class="mb-6 p-4 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] text-[13px] space-y-1">
                <div class="font-semibold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span>Terdapat kesalahan pada formulir:</span>
                </div>
                <ul class="list-disc list-inside pl-4 text-[12px] space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.templates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
            x-data="{
                name: '{{ old('name', '') }}',
                slug: '{{ old('slug', '') }}',
                fileName: '',
                generateSlug() {
                    if (!this.slug || this.slug === '') {
                        this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
                    }
                },
                fileChosen(event) {
                    if (event.target.files.length > 0) {
                        this.fileName = event.target.files[0].name + ' (' + (event.target.files[0].size / 1024).toFixed(1) + ' KB)';
                    }
                }
            }">
            @csrf

            <!-- Card: File Upload Section -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-black/5 dark:border-white/5">
                    <div
                        class="w-7 h-7 rounded-[7px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="file-up" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[14px] text-black dark:text-white">File Spreadsheet Asli (.xlsx / .xls /
                            .csv)</h3>
                        <p class="text-[11px] text-black/45 dark:text-white/45">File ini yang akan langsung diunduh oleh
                            pengunjung setelah mengisi form kontak.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">Pilih File Excel
                        *</label>
                    <div
                        class="border-2 border-dashed border-black/15 dark:border-white/15 rounded-[12px] p-6 text-center hover:border-[#007AFF] transition-colors relative bg-black/[0.01] dark:bg-white/[0.01]">
                        <input type="file" name="excel_file" required accept=".xlsx,.xls,.csv"
                            @change="fileChosen($event)"
                            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <div class="space-y-2 pointer-events-none">
                            <div
                                class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                                <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                            </div>
                            <div class="text-[13px] font-semibold text-black dark:text-white">
                                <span class="text-[#007AFF]">Klik untuk upload</span> atau drag &amp; drop file Excel ke
                                sini
                            </div>
                            <p class="text-[11px] text-black/45 dark:text-white/45">Mendukung format .XLSX, .XLS, atau .CSV
                                (Maksimal 25MB)</p>
                            <div x-show="fileName" class="pt-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span x-text="fileName"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Metadata Template -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-black/5 dark:border-white/5">
                    <div class="w-7 h-7 rounded-[7px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="align-left" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[14px] text-black dark:text-white">Informasi &amp; Tampilan Template</h3>
                        <p class="text-[11px] text-black/45 dark:text-white/45">Atur judul, kategori, dan penjelasan manfaat
                            yang tampil pada halaman web.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama / Judul
                            Template *</label>
                        <input type="text" name="name" x-model="name" @blur="generateSlug()" required
                            placeholder="Contoh: Template Laporan Arus Kas Bulanan UMKM"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">URL Slug
                            *</label>
                        <div class="relative">
                            <input type="text" name="slug" x-model="slug" required
                                placeholder="laporan-arus-kas-bulanan"
                                class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>
                        <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">Akan diakses publik di:
                            <code>/template/<span x-text="slug || 'slug-template'"></span></code></p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Kategori
                            *</label>
                        <select name="category" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Deskripsi
                            Singkat (Ringkasan Manfaat)</label>
                        <textarea name="description" rows="2"
                            placeholder="Jelaskan secara ringkas untuk apa template ini digunakan dan untuk jenis usaha apa..."
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('description') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Fitur
                            Unggulan / Poin-Poin Manfaat (Highlights)</label>
                        <textarea name="highlights" rows="4"
                            placeholder="Ketik satu poin per baris, contoh:&#10;Perhitungan otomatis kas masuk dan kas keluar&#10;Laporan laba rugi standar format bank&#10;Bisa langsung dicetak atau disalin ke HP"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('highlights') }}</textarea>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Pisahkan tiap poin dengan baris baru
                            (Enter). Poin ini akan tampil dengan centang hijau di halaman publik.</p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Urutan
                            Tampilan (Sort Order)</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">Angka lebih kecil tampil lebih awal (0,
                            1, 2, ...).</p>
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="relative flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/15 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#34C759]">
                            </div>
                            <span class="text-[13px] font-medium text-black dark:text-white">Publikasikan langsung
                                (Aktif)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.templates.index') }}"
                    class="h-10 px-5 rounded-[10px] bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-black/70 dark:text-white/70 text-[13px] font-medium flex items-center justify-center transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="h-10 px-6 rounded-[10px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold flex items-center justify-center gap-2 shadow-[0_1px_3px_rgba(0,122,255,0.3)] transition-colors">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>Simpan &amp; Upload Template</span>
                </button>
            </div>
        </form>

    </div>
@endsection
