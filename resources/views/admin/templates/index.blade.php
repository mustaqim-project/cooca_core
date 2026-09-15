@extends('layouts.admin', [
    'title' => 'CMS Template Excel - Admin Console',
    'headerTitle' => 'Kelola Template Unduhan Excel',
    'headerSubtitle' => 'Upload dan kelola file spreadsheet Excel gratis untuk pengunjung website publik',
])

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div
                class="p-3.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] dark:text-[#30D158] text-[13px] flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Template</span>
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#007AFF]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ $totalTemplates }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Katalog template terdaftar</div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Template Aktif</span>
                    <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $activeTemplates }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Tampil di portal download publik</div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Unduhan</span>
                    <i data-lucide="download" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">
                    {{ number_format($totalDownloads) }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Akumulasi unduhan file Excel</div>
            </div>

            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Leads Masuk</span>
                    <i data-lucide="users-round" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    {{ number_format($totalLeads) }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Data kontak prospek UMKM</div>
            </div>
        </div>

        <!-- Toolbar: Search, Filter & Upload Button -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.templates.index') }}"
                class="flex flex-col sm:flex-row items-stretch gap-2 flex-1 max-w-2xl">
                <div class="relative flex-1">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2"
                        stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama template, slug, deskripsi..."
                        class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
                <select name="category" onchange="this.form.submit()"
                    class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}
                        </option>
                    @endforeach
                </select>
                @if ($search !== '' || $category !== '')
                    <a href="{{ route('admin.templates.index') }}"
                        class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center gap-1 self-end sm:self-auto">
                        <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.templates.create') }}"
                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold flex items-center justify-center gap-2 shadow-[0_1px_3px_rgba(0,122,255,0.3)] transition-colors shrink-0">
                <i data-lucide="upload" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Upload Template Baru</span>
            </a>
        </div>

        <!-- Table of Templates -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Template &amp; Slug</th>
                            <th class="py-3 px-4">Kategori &amp; Format</th>
                            <th class="py-3 px-4">File Fisik (.xlsx)</th>
                            <th class="py-3 px-4 text-center">Unduhan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($templates as $template)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-8 h-8 rounded-[8px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-black dark:text-white leading-tight">
                                                {{ $template->name }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <code
                                                    class="text-[11px] text-black/40 dark:text-white/40 font-mono">/template/{{ $template->slug }}</code>
                                                <a href="{{ route('template.show', $template->slug) }}" target="_blank"
                                                    class="text-[11px] text-[#007AFF] hover:underline inline-flex items-center gap-0.5">
                                                    <span>Lihat Publik</span>
                                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 w-fit">
                                            {{ $template->category }}
                                        </span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45">Format:
                                            <strong>{{ $template->format }}</strong></span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="text-[12px] font-medium text-black/80 dark:text-white/80 truncate max-w-[220px]"
                                        title="{{ $template->file_name }}">
                                        {{ $template->file_name }}
                                    </div>
                                    <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">
                                        Ukuran: {{ $template->formatted_file_size }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[12px] font-bold tabular-nums bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                        <i data-lucide="download"
                                            class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                        {{ number_format($template->downloads_count) }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <form action="{{ route('admin.templates.toggle', $template) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all {{ $template->is_active ? 'bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40 hover:bg-black/15' }}"
                                            title="Klik untuk ubah status">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $template->is_active ? 'bg-[#34C759]' : 'bg-black/40 dark:bg-white/40' }}"></span>
                                            <span>{{ $template->is_active ? 'Aktif' : 'Draft' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Download File Direct -->
                                        <a href="{{ route('admin.templates.download', $template) }}"
                                            class="p-1.5 rounded-[8px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 hover:text-[#007AFF] transition-colors"
                                            title="Download File Excel">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('admin.templates.edit', $template) }}"
                                            class="p-1.5 rounded-[8px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 hover:text-[#007AFF] transition-colors"
                                            title="Edit Template">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('admin.templates.destroy', $template) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini beserta file fisiknya?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-[8px] text-black/60 dark:text-white/60 hover:bg-red-500/10 hover:text-red-500 transition-colors"
                                                title="Hapus Template">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <div
                                        class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto mb-3 text-black/30 dark:text-white/30">
                                        <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                                    </div>
                                    <p class="font-medium">Belum ada template Excel yang diunggah.</p>
                                    <a href="{{ route('admin.templates.create') }}"
                                        class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-[#007AFF] hover:underline">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                        <span>Upload template pertama sekarang</span>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($templates->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
