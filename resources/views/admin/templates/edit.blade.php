@extends('layouts.admin', [
    'title' => 'Edit Template Excel - Admin Console',
    'headerTitle' => 'Edit Template Excel: ' . $template->name,
    'headerSubtitle' => 'Perbarui informasi template atau unggah file spreadsheet pengganti',
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

        <form action="{{ route('admin.templates.update', $template) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" x-data="{
                newFileName: '',
                fileChosen(event) {
                    if (event.target.files.length > 0) {
                        this.newFileName = event.target.files[0].name + ' (' + (event.target.files[0].size / 1024).toFixed(1) + ' KB)';
                    }
                }
            }">
            @csrf
            @method('PUT')

            <!-- Card: Current File & Replacement -->
            <div
                class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-7 h-7 rounded-[7px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-[14px] text-black dark:text-white">File Excel Terpasang</h3>
                            <p class="text-[11px] text-black/45 dark:text-white/45">File spreadsheet aktif yang diunduh oleh
                                pengunjung saat ini.</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.templates.download', $template) }}"
                        class="h-8 px-3 rounded-[8px] bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-[#007AFF] text-[12px] font-semibold inline-flex items-center gap-1.5 transition-colors">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Download File Saat Ini</span>
                    </a>
                </div>

                <!-- Existing file info badge -->
                <div
                    class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[12px]">
                    <div class="flex items-center gap-2 font-mono text-black/80 dark:text-white/80">
                        <i data-lucide="file-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>{{ $template->file_name }}</span>
                    </div>
                    <div class="text-[11px] text-black/50 dark:text-white/50">
                        Format: <strong>{{ $template->format }}</strong> • Ukuran:
                        <strong>{{ $template->formatted_file_size }}</strong> • Diunduh:
                        <strong>{{ number_format($template->downloads_count) }} kali</strong>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">Unggah File
                        Pengganti (Opsional)</label>
                    <div
                        class="border-2 border-dashed border-black/15 dark:border-white/15 rounded-[12px] p-5 text-center hover:border-[#007AFF] transition-colors relative bg-black/[0.01] dark:bg-white/[0.01]">
                        <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" @change="fileChosen($event)"
                            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <div class="space-y-1.5 pointer-events-none">
                            <div class="text-[13px] font-semibold text-black dark:text-white">
                                <span class="text-[#007AFF]">Pilih file baru</span> untuk menggantikan file di atas (biarkan
                                kosong jika tidak ingin mengubah)
                            </div>
                            <p class="text-[11px] text-black/45 dark:text-white/45">Maksimal 25MB (.xlsx, .xls, .csv)</p>
                            <div x-show="newFileName" class="pt-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold bg-indigo-500/15 text-indigo-700 dark:text-indigo-300">
                                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                    <span>File Baru: </span>
                                    <span x-text="newFileName"></span>
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
                        <p class="text-[11px] text-black/45 dark:text-white/45">Atur judul, kategori, dan penjelasan
                            manfaat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Nama / Judul
                            Template *</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">URL Slug
                            *</label>
                        <input type="text" name="slug" value="{{ old('slug', $template->slug) }}" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">URL publik:
                            <code>/template/{{ $template->slug }}</code></p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Kategori
                            *</label>
                        <select name="category" required
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('category', $template->category) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Deskripsi
                            Singkat (Ringkasan Manfaat)</label>
                        <textarea name="description" rows="2"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('description', $template->description) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Fitur
                            Unggulan / Poin-Poin Manfaat (Highlights)</label>
                        <textarea name="highlights" rows="4"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('highlights', $highlightsText) }}</textarea>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Pisahkan tiap poin dengan baris baru
                            (Enter).</p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Urutan
                            Tampilan (Sort Order)</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order) }}"
                            min="0"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="relative flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $template->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/15 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#34C759]">
                            </div>
                            <span class="text-[13px] font-medium text-black dark:text-white">Status Publikasi (Aktif)</span>
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
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>

    </div>
@endsection
