@extends('layouts.admin', [
    'title' => 'Upload Template Excel Baru - Admin Console',
    'headerTitle' => 'Upload Template Excel Baru',
    'headerSubtitle' => 'Unggah file spreadsheet asli (.xlsx/.xls/.csv) dan atur informasi publikasi untuk calon prospek UMKM',
])

@section('content')
    <div class="max-w-4xl mx-auto w-full min-w-0 pb-28 lg:pb-10 space-y-6">

        <div>
            <a href="{{ route('admin.templates.index') }}"
                class="inline-flex items-center gap-2 text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-[#007AFF] dark:hover:text-[#007AFF] transition-colors active:scale-[0.98]">
                <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.8"></i>
                <span>Kembali ke Daftar Template</span>
            </a>
        </div>

        @if ($errors->any())
            <div
                class="p-4 sm:p-5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] text-[13px] space-y-2">
                <div class="font-semibold flex items-center gap-2 text-[14px]">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>Terdapat kendala pada formulir:</span>
                </div>
                <ul class="list-disc list-inside pl-4 text-[13px] space-y-1">
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

            <!-- Bento Card 1: File Upload Section -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-4">
                <div class="flex items-center gap-3 pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i data-lucide="file-up" class="w-4 h-4" stroke-width="1.8"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[15px] text-black dark:text-white leading-tight">File Spreadsheet Asli (.xlsx / .xls / .csv)</h3>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 truncate">File ini yang akan langsung diunduh oleh calon prospek</p>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-2">Pilih Berkas Excel *</label>
                    <div
                        class="border-2 border-dashed border-black/15 dark:border-white/15 rounded-[16px] p-6 text-center hover:border-[#007AFF] transition-all relative bg-black/[0.01] dark:bg-white/[0.01]">
                        <input type="file" name="excel_file" required accept=".xlsx,.xls,.csv"
                            @change="fileChosen($event)"
                            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <div class="space-y-2 pointer-events-none">
                            <div
                                class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                                <i data-lucide="file-spreadsheet" class="w-6 h-6" stroke-width="1.8"></i>
                            </div>
                            <div class="text-[14px] font-semibold text-black dark:text-white">
                                <span class="text-[#007AFF]">Klik untuk memilih berkas</span> atau seret file Excel ke sini
                            </div>
                            <p class="text-[12px] text-black/45 dark:text-white/45">Mendukung format .XLSX, .XLS, atau .CSV (Maksimal 25MB)</p>
                            <div x-show="fileName" class="pt-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span x-text="fileName"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bento Card 2: Metadata Template -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-5">
                <div class="flex items-center gap-3 pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="align-left" class="w-4 h-4" stroke-width="1.8"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[15px] text-black dark:text-white leading-tight">Informasi &amp; Tampilan Template</h3>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 truncate">Atur judul, kategori, dan deskripsi manfaat di halaman publik</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Nama Template *</label>
                        <input type="text" name="name" x-model="name" @blur="generateSlug()" required
                            placeholder="Contoh: Template Laporan Arus Kas Bulanan UMKM"
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">URL Slug *</label>
                        <input type="text" name="slug" x-model="slug" required
                            placeholder="laporan-arus-kas-bulanan"
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white font-mono placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Akan diakses di:
                            <code class="font-mono text-black/60 dark:text-white/60">/template/<span x-text="slug || 'slug-template'"></span></code></p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Kategori *</label>
                        <select name="category" required
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Deskripsi Singkat</label>
                        <textarea name="description" rows="2"
                            placeholder="Jelaskan secara ringkas peruntukan template dan jenis usaha sasaran..."
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all leading-relaxed">{{ old('description') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Fitur Unggulan (Highlights)</label>
                        <textarea name="highlights" rows="4"
                            placeholder="Satu poin per baris, contoh:&#10;Perhitungan otomatis kas masuk dan keluar&#10;Laporan laba rugi format standar perbankan&#10;Siap dicetak atau disalin"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all leading-relaxed">{{ old('highlights') }}</textarea>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-1">Pisahkan tiap poin dengan Enter. Tampil dengan tanda centang di halaman publik.</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Urutan Tampilan</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Angka lebih kecil tampil lebih awal (0, 1, 2, ...).</p>
                    </div>

                    <div class="flex items-center pt-2 sm:pt-6">
                        <label class="relative flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/15 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#34C759]">
                            </div>
                            <span class="text-[13px] font-medium text-black dark:text-white">Publikasikan langsung (Aktif)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons (Section 9.4 Concise Action Labels) -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.templates.index') }}"
                    class="h-11 sm:h-10 px-5 rounded-[12px] bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-black/70 dark:text-white/70 text-[13px] font-medium flex items-center justify-center transition-colors active:scale-[0.98]">
                    Batal
                </a>
                <button type="submit"
                    class="h-11 sm:h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] active:scale-[0.98] transition-all">
                    <i data-lucide="upload" class="w-4 h-4" stroke-width="1.8"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>

    </div>
@endsection
