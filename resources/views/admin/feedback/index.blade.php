@extends('layouts.admin', [
    'title' => $type === 'bugs' ? 'Kelola Bug - Admin Console' : 'Kelola Request Fitur - Admin Console',
    'headerTitle' => $type === 'bugs' ? 'Laporan Bug & Masalah' : 'Permintaan Fitur Baru',
    'headerSubtitle' => 'Pantau dan tindak lanjuti masukan langsung dari para pemilik bisnis UMKM',
])

@section('content')
<div class="space-y-6">

    <!-- Header Bento & Apple Pill Segmented Switch -->
    <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-[14px] {{ $type === 'bugs' ? 'bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A]' : 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]' }} flex items-center justify-center shrink-0">
                <i data-lucide="{{ $type === 'bugs' ? 'bug' : 'sparkles' }}" class="w-5 h-5" stroke-width="2"></i>
            </div>
            <div>
                <h1 class="text-[18px] font-bold text-black dark:text-white tracking-tight">
                    {{ $type === 'bugs' ? 'Pusat Penanganan Bug' : 'Pusat Aspirasi Fitur' }}
                </h1>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                    {{ $type === 'bugs' ? 'Prioritaskan perbaikan celah dan kendala sistem' : 'Koleksi ide pengembangan dari tenant Cooca' }}
                </p>
            </div>
        </div>

        <!-- Apple Pill Switch -->
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/[0.04] dark:border-white/[0.06] text-[13px] font-medium gap-1 self-start sm:self-auto">
            <a href="{{ route('admin.feedback.bugs.index') }}"
                class="px-4 py-2 rounded-[10px] font-bold transition-all flex items-center gap-2 {{ $type === 'bugs' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="bug" class="w-4 h-4 text-[#FF3B30]" stroke-width="2"></i>
                <span>Laporan Bug</span>
            </a>
            <a href="{{ route('admin.feedback.features.index') }}"
                class="px-4 py-2 rounded-[10px] font-bold transition-all flex items-center gap-2 {{ $type === 'features' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF]" stroke-width="2"></i>
                <span>Request Fitur</span>
            </a>
        </div>
    </div>

    <!-- Filter Bento Toolbar -->
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">Status:</span>
                <select name="status" onchange="this.form.submit()"
                    class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.06] rounded-[12px] text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <option value="all">Semua Status</option>
                    @foreach($statuses as $value)
                        <option value="{{ $value }}" @selected($status === $value)>{{ str_replace('_', ' ', ucfirst($value)) }}</option>
                    @endforeach
                </select>
            </div>
            
            @if(request('status') && request('status') !== 'all')
                <a href="{{ route($type === 'bugs' ? 'admin.feedback.bugs.index' : 'admin.feedback.features.index') }}"
                    class="h-9 px-3 rounded-[10px] text-[12px] font-bold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center gap-1.5">
                    <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span>Reset Filter</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Bento Table Container -->
    <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                        <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Judul &amp; Prioritas</th>
                        <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Bisnis / Pengirim</th>
                        <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Status</th>
                        <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Progress</th>
                        <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($items as $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-bold text-black dark:text-white text-[14px]">
                                    <a href="{{ route($type === 'bugs' ? 'admin.feedback.bugs.show' : 'admin.feedback.features.show', $item) }}"
                                        class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">{{ $item->title }}</a>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    @php
                                        $priorityVal = strtolower($item->priority ?? $item->severity ?? 'normal');
                                        $prioClass = match($priorityVal) {
                                            'critical', 'high' => 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]',
                                            'low' => 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55',
                                            default => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $prioClass }}">
                                        {{ $priorityVal }}
                                    </span>
                                    <span class="text-[11px] text-black/40 dark:text-white/40 font-mono">{{ $item->created_at?->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-black/75 dark:text-white/75">
                                <div class="font-bold">{{ $item->business->name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                    {{ ($item->reporter ?? $item->requester)->name }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $st = $item->status;
                                    $stClass = match($st) {
                                        'resolved', 'released' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]',
                                        'closed', 'rejected' => 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]',
                                        'in_progress' => 'bg-[#5856D6]/15 text-[#413FA6] dark:text-[#5E5CE6]',
                                        default => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $stClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span>{{ str_replace('_', ' ', ucfirst($st)) }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-4 min-w-[150px]">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-[11px] font-bold tabular-nums">
                                        <span class="text-black/50 dark:text-white/50">Penyelesaian</span>
                                        <span class="text-black dark:text-white">{{ $item->progress_percent }}%</span>
                                    </div>
                                    <div class="h-2 w-full bg-black/[0.06] dark:bg-white/[0.08] rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#34C759] rounded-full transition-all duration-300"
                                            style="width: {{ $item->progress_percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route($type === 'bugs' ? 'admin.feedback.bugs.show' : 'admin.feedback.features.show', $item) }}"
                                    class="h-8 px-3 rounded-[10px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-95 transition-all inline-flex items-center gap-1">
                                    <span>Kelola</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3" stroke-width="2"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/30 dark:text-white/30 mb-2.5">
                                    <i data-lucide="inbox" class="w-6 h-6" stroke-width="1.5"></i>
                                </div>
                                <p class="text-[15px] font-bold text-black dark:text-white">Tidak ada data feedback ditemukan</p>
                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">Belum ada item pada kategori ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection