@extends('layouts.admin', ['title' => $type === 'bugs' ? 'Kelola Bug' : 'Kelola Request Fitur', 'headerTitle' => $type === 'bugs' ? 'Laporan Bug' : 'Request Fitur', 'headerSubtitle' => 'Pantau dan kelola feedback dari business owner'])

@section('content')
<div class="space-y-6">

    <!-- Tab Switch (Segmented Control) -->
    <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
        <a href="{{ route('admin.feedback.bugs.index') }}" class="px-3 py-1.5 rounded-[7px] {{ $type === 'bugs' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80' }} transition-all">Laporan Bug</a>
        <a href="{{ route('admin.feedback.features.index') }}" class="px-3 py-1.5 rounded-[7px] {{ $type === 'features' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80' }} transition-all">Request Fitur</a>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-wrap items-center gap-3">
        <select name="status" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
            <option value="all">Semua Status</option>
            @foreach($statuses as $value)
            <option value="{{ $value }}" @selected($status === $value)>{{ str_replace('_', ' ', ucfirst($value)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
            <i data-lucide="filter" class="w-4 h-4" stroke-width="1.5"></i><span>Filter</span>
        </button>
    </form>

    <!-- Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Judul</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bisnis / Pengirim</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Progress</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($items as $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">{{ $item->title }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ ucfirst($item->priority ?? $item->severity) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-black/70 dark:text-white/70">{{ $item->business->name }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ ($item->reporter ?? $item->requester)->name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $st = $item->status;
                                $stClass = match($st) {
                                    'resolved', 'released' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                    'closed', 'rejected' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                    'in_progress' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]',
                                    default => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $stClass }}"><span class="w-1.5 h-1.5 rounded-full"></span> {{ str_replace('_', ' ', ucfirst($st)) }}</span>
                        </td>
                        <td class="px-4 py-3 min-w-[140px]">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-16 bg-black/[0.08] dark:bg-white/[0.1] rounded-full"><div class="h-full rounded-full" style="width:{{ $item->progress_percent }}%"></div></div>
                                <span class="text-[11px] text-black/50 dark:text-white/50 tabular-nums">{{ $item->progress_percent }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route($type === 'bugs' ? 'admin.feedback.bugs.show' : 'admin.feedback.features.show', $item) }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">Kelola <i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.5"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center"><p class="text-[15px] font-semibold text-black dark:text-white">Belum ada data</p><p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Belum ada item pada daftar ini.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection