@extends('layouts.admin', [
    'title' => 'Edit Template Excel - Admin Console',
    'headerTitle' => 'Edit Template Excel',
    'headerSubtitle' => 'Perbarui rincian metadata atau unggah berkas spreadsheet pengganti',
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

            <!-- Bento Card 1: Current File & Replacement -->
            <div
                class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-4">
                <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08] gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4" stroke-width="1.8"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-[15px] text-black dark:text-white leading-tight">Berkas Excel Terpasang</h3>
                            <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5 truncate">File spreadsheet aktif yang diunduh publik saat ini</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.templates.download', $template) }}"
                        class="h-9 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[#007AFF] text-[12px] font-semibold inline-flex items-center gap-1.5 transition-colors active:scale-[0.98] shrink-0">
                        <i data-lucide="download" class="w-3.5 h-3.5" stroke-width="1.8"></i>
                        <span>Unduh Berkas</span>
                    </a>
                </div>

                <!-- Existing File Info Tile -->
                <div
                    class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[13px]">
                    <div class="flex items-center gap-2.5 font-mono text-black/85 dark:text-white/85 min-w-0 truncate">
                        <i data-lucide="file-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span class="truncate">{{ $template->file_name }}</span>
                    </div>
                    <div class="text-[12px] text-black/55 dark:text-white/55 shrink-0">
                        Format: <strong class="text-black/80 dark:text-white/80">{{ $template->format }}</strong> • Ukuran:
                        <strong class="text-black/80 dark:text-white/80">{{ $template->formatted_file_size }}</strong> • Diunduh:
                        <strong class="text-black/80 dark:text-white/80 tabular-nums">{{ number_format($template->downloads_count) }} kali</strong>
                    </div>
                </div>

                <div class="pt-1">
                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-2">Unggah File Pengganti (Opsional)</label>
                    <div
                        class="border-2 border-dashed border-black/15 dark:border-white/15 rounded-[16px] p-5 text-center hover:border-[#007AFF] transition-all relative bg-black/[0.01] dark:bg-white/[0.01]">
                        <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" @change="fileChosen($event)"
                            class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <div class="space-y-1.5 pointer-events-none">
                            <div class="text-[14px] font-semibold text-black dark:text-white">
                                <span class="text-[#007AFF]">Pilih berkas baru</span> untuk menggantikan file di atas
                            </div>
                            <p class="text-[12px] text-black/45 dark:text-white/45">Biarkan kosong jika tidak ingin mengubah berkas • Maksimal 25MB (.xlsx, .xls, .csv)</p>
                            <div x-show="newFileName" class="pt-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold bg-indigo-500/15 text-indigo-700 dark:text-indigo-300">
                                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                    <span>Berkas Baru: </span>
                                    <span x-text="newFileName"></span>
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
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">URL Slug *</label>
                        <input type="text" name="slug" value="{{ old('slug', $template->slug) }}" required
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                        <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">URL publik:
                            <code class="font-mono text-black/60 dark:text-white/60">/template/{{ $template->slug }}</code></p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Kategori *</label>
                        <select name="category" required
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('category', $template->category) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Deskripsi Singkat</label>
                        <textarea name="description" rows="2"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all leading-relaxed">{{ old('description', $template->description) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Fitur Unggulan (Highlights)</label>
                        <textarea name="highlights" rows="4"
                            class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all leading-relaxed">{{ old('highlights', $highlightsText) }}</textarea>
                        <p class="text-[12px] text-black/45 dark:text-white/45 mt-1">Pisahkan tiap poin dengan Enter.</p>
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">Urutan Tampilan</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order) }}"
                            min="0"
                            class="w-full h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                    </div>

                    <div class="flex items-center pt-2 sm:pt-6">
                        <label class="relative flex items-center gap-3 cursor-pointer select-none">
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

            <!-- Action Buttons (Section 9.4 Concise Action Labels) -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.templates.index') }}"
                    class="h-11 sm:h-10 px-5 rounded-[12px] bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-black/70 dark:text-white/70 text-[13px] font-medium flex items-center justify-center transition-colors active:scale-[0.98]">
                    Batal
                </a>
                <button type="submit"
                    class="h-11 sm:h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] active:scale-[0.98] transition-all">
                    <i data-lucide="check" class="w-4 h-4" stroke-width="1.8"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>

    </div>
@endsection
