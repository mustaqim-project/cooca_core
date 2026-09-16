@extends('layouts.admin', [
    'title' => 'CMS Template Excel - Admin Console',
    'headerTitle' => 'CMS Template Excel',
    'headerSubtitle' => 'Kelola berkas spreadsheet Excel gratis untuk materi prospek edukasi UMKM',
])

@section('content')
    <div class="space-y-6 w-full max-w-full min-w-0 pb-28 lg:pb-10">

        @if (session('success'))
            <div
                class="p-3.5 sm:p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] dark:text-[#30D158] text-[13px] font-medium flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span class="leading-snug">{{ session('success') }}</span>
            </div>
        @endif

        <!-- KPI Metric Bento Cards (Apple HIG v2.0) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            <!-- Total Template -->
            <div
                class="p-3.5 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50 truncate">Total
                        Template</span>
                    <div
                        class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-[#007AFF] shrink-0">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div>
                    <div class="text-[22px] sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                        {{ $totalTemplates }}
                    </div>
                    <div class="text-[11px] sm:text-[12px] text-black/40 dark:text-white/40 mt-0.5 truncate">
                        Katalog terdaftar
                    </div>
                </div>
            </div>

            <!-- Template Aktif -->
            <div
                class="p-3.5 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50 truncate">Template
                        Aktif</span>
                    <div
                        class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-[#34C759] dark:text-[#30D158] shrink-0">
                        <i data-lucide="check-circle" class="w-4 h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div>
                    <div
                        class="text-[22px] sm:text-[26px] font-bold tracking-tight text-[#34C759] dark:text-[#30D158] tabular-nums">
                        {{ $activeTemplates }}
                    </div>
                    <div class="text-[11px] sm:text-[12px] text-black/40 dark:text-white/40 mt-0.5 truncate">
                        Tampil di portal publik
                    </div>
                </div>
            </div>

            <!-- Total Unduhan -->
            <div
                class="p-3.5 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50 truncate">Total
                        Unduhan</span>
                    <div
                        class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6] shrink-0">
                        <i data-lucide="download" class="w-4 h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div>
                    <div
                        class="text-[22px] sm:text-[26px] font-bold tracking-tight text-[#5856D6] dark:text-[#5E5CE6] tabular-nums">
                        {{ number_format($totalDownloads) }}
                    </div>
                    <div class="text-[11px] sm:text-[12px] text-black/40 dark:text-white/40 mt-0.5 truncate">
                        Akumulasi file terunduh
                    </div>
                </div>
            </div>

            <!-- Total Leads Masuk -->
            <div
                class="p-3.5 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/50 dark:text-white/50 truncate">Total
                        Leads</span>
                    <div
                        class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-[#FF9500] dark:text-[#FF9F0A] shrink-0">
                        <i data-lucide="users-round" class="w-4 h-4" stroke-width="1.8"></i>
                    </div>
                </div>
                <div>
                    <div
                        class="text-[22px] sm:text-[26px] font-bold tracking-tight text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">
                        {{ number_format($totalLeads) }}
                    </div>
                    <div class="text-[11px] sm:text-[12px] text-black/40 dark:text-white/40 mt-0.5 truncate">
                        Kontak prospek UMKM
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar: Search, Filter & Upload Button -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 min-w-0">
            <form method="GET" action="{{ route('admin.templates.index') }}"
                class="flex flex-col sm:flex-row items-stretch gap-2.5 flex-1 max-w-2xl min-w-0">
                <div class="relative flex-1 min-w-0">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        stroke-width="1.8"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama template, slug, atau deskripsi..."
                        class="w-full h-11 sm:h-10 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                </div>
                <select name="category" onchange="this.form.submit()"
                    class="h-11 sm:h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black/80 dark:text-white/80 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 focus:border-[#007AFF] transition-all">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}
                        </option>
                    @endforeach
                </select>
                @if ($search !== '' || $category !== '')
                    <a href="{{ route('admin.templates.index') }}"
                        class="h-11 sm:h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center justify-center gap-1.5 self-end sm:self-auto active:scale-[0.98]">
                        <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="1.8"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.templates.create') }}"
                class="h-11 sm:h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] active:scale-[0.98] transition-all shrink-0">
                <i data-lucide="upload" class="w-4 h-4" stroke-width="1.8"></i>
                <span>Upload Template Baru</span>
            </a>
        </div>

        <!-- Table-to-Card Responsive Transformation (Section 13.3) -->
        <div class="space-y-3">
            <!-- Mobile Card List View (< md) -->
            <div class="block md:hidden space-y-3">
                @forelse($templates as $template)
                    <div
                        class="p-4 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-3 min-w-0">
                        <!-- Header Row -->
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                <div
                                    class="w-9 h-9 rounded-[10px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-[15px] text-black dark:text-white leading-snug break-words">
                                        {{ $template->name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                        <span class="text-[12px] font-medium text-black/60 dark:text-white/60">
                                            {{ $template->category }}
                                        </span>
                                        <span class="text-black/30 dark:text-white/30">•</span>
                                        <span class="text-[12px] font-semibold text-black/75 dark:text-white/75">
                                            {{ $template->format }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Toggle Badge (Lifecycle State Only) -->
                            <form action="{{ route('admin.templates.toggle', $template) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all active:scale-[0.98] {{ $template->is_active ? 'bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 hover:bg-black/15' }}"
                                    title="Klik untuk ubah status">
                                    <span>{{ $template->is_active ? 'Aktif' : 'Draft' }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- Slug & Public Link -->
                        <div class="pt-1 flex items-center gap-2 text-[11px]">
                            <code
                                class="font-mono text-black/45 dark:text-white/45 truncate">/template/{{ $template->slug }}</code>
                            <a href="{{ route('template.show', $template->slug) }}" target="_blank"
                                class="text-[#007AFF] font-medium hover:underline inline-flex items-center gap-0.5 shrink-0">
                                <span>Lihat</span>
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </div>

                        <!-- File & Downloads Info Tile -->
                        <div
                            class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-2 text-[12px]">
                            <div class="min-w-0 flex-1 truncate text-black/70 dark:text-white/70 font-mono">
                                {{ $template->file_name }}
                                <span class="text-[11px] text-black/40 dark:text-white/40 font-sans ml-1">({{ $template->formatted_file_size }})</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0 font-semibold tabular-nums text-black/80 dark:text-white/80">
                                <i data-lucide="download" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                <span>{{ number_format($template->downloads_count) }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-1 border-t border-black/[0.04] dark:border-white/[0.06]">
                            <a href="{{ route('admin.templates.download', $template) }}"
                                class="flex-1 h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[#007AFF] text-[13px] font-semibold flex items-center justify-center gap-1.5 active:scale-[0.98] transition-all">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span>Unduh</span>
                            </a>
                            <a href="{{ route('admin.templates.edit', $template) }}"
                                class="h-10 px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/75 dark:text-white/75 text-[13px] font-semibold flex items-center justify-center gap-1.5 active:scale-[0.98] transition-all">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </a>
                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST"
                                onsubmit="return confirm('Hapus template ini beserta file fisiknya?')"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="h-10 w-10 rounded-[12px] bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center active:scale-[0.98] transition-all"
                                    title="Hapus Template">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div
                        class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-10 text-center space-y-3">
                        <div
                            class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                            <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                        </div>
                        <p class="font-medium text-[14px] text-black/60 dark:text-white/60">Belum ada template Excel yang diunggah.</p>
                        <a href="{{ route('admin.templates.create') }}"
                            class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#007AFF] hover:underline">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <span>Upload template pertama sekarang</span>
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View (>= md) -->
            <div
                class="hidden md:block overflow-hidden rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr
                                class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                                <th class="py-3 px-4">Template &amp; Slug</th>
                                <th class="py-3 px-4">Kategori &amp; Format</th>
                                <th class="py-3 px-4">File Spreadsheet</th>
                                <th class="py-3 px-4 text-center">Unduhan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.06] dark:divide-white/[0.08]">
                            @forelse($templates as $template)
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                    <!-- Template & Slug -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-start gap-3 min-w-0">
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
                                                        <span>Lihat</span>
                                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kategori & Format (Pure Typography - Anti-Pill Abuse) -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-[13px] text-black dark:text-white">
                                                {{ $template->category }}
                                            </span>
                                            <span class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                                Format {{ $template->format }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- File Fisik -->
                                    <td class="py-3.5 px-4">
                                        <div class="text-[12px] font-mono text-black/80 dark:text-white/80 truncate max-w-[220px]"
                                            title="{{ $template->file_name }}">
                                            {{ $template->file_name }}
                                        </div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">
                                            Ukuran: {{ $template->formatted_file_size }}
                                        </div>
                                    </td>

                                    <!-- Unduhan (Pure Typography Tabular Figures) -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-[13px] font-semibold tabular-nums text-black/80 dark:text-white/80">
                                            <i data-lucide="download"
                                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                            {{ number_format($template->downloads_count) }}
                                        </span>
                                    </td>

                                    <!-- Status (Lifecycle State Only, Clean No-Dot) -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <form action="{{ route('admin.templates.toggle', $template) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all active:scale-[0.98] {{ $template->is_active ? 'bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 dark:bg-white/10 text-black/45 dark:text-white/45 hover:bg-black/15' }}"
                                                title="Klik untuk ubah status publikasi">
                                                <span>{{ $template->is_active ? 'Aktif' : 'Draft' }}</span>
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Download -->
                                            <a href="{{ route('admin.templates.download', $template) }}"
                                                class="p-2 rounded-[10px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 hover:text-[#007AFF] active:scale-[0.98] transition-all"
                                                title="Download File Excel">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>

                                            <!-- Edit -->
                                            <a href="{{ route('admin.templates.edit', $template) }}"
                                                class="p-2 rounded-[10px] text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 hover:text-[#007AFF] active:scale-[0.98] transition-all"
                                                title="Edit Template">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>

                                            <!-- Delete -->
                                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST"
                                                onsubmit="return confirm('Hapus template ini beserta file fisiknya?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 rounded-[10px] text-black/60 dark:text-white/60 hover:bg-red-500/10 hover:text-red-500 active:scale-[0.98] transition-all"
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
                                        <p class="font-medium text-[14px]">Belum ada template Excel yang diunggah.</p>
                                        <a href="{{ route('admin.templates.create') }}"
                                            class="mt-3 inline-flex items-center gap-2 text-[13px] font-semibold text-[#007AFF] hover:underline">
                                            <i data-lucide="upload" class="w-4 h-4"></i>
                                            <span>Upload template pertama sekarang</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($templates->hasPages())
                    <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>

            <!-- Mobile Pagination -->
            @if ($templates->hasPages())
                <div class="block md:hidden pt-2">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
