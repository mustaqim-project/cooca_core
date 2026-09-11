@extends('layouts.admin', ['title' => 'Kelola Feedback', 'headerTitle' => 'Kelola Feedback', 'headerSubtitle' => $item->title])

@section('content')
@php
    $st = $item->status;
    $stClass = match($st) {
        'resolved', 'released' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
        'closed', 'rejected' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
        'in_progress' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]',
        default => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]'
    };
@endphp
<div class="max-w-5xl space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <a href="{{ route($type === 'bugs' ? 'admin.feedback.bugs.index' : 'admin.feedback.features.index') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i><span>Kembali ke Daftar</span>
        </a>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $stClass }}"><span class="w-1.5 h-1.5 rounded-full"></span> {{ str_replace('_', ' ', ucfirst($st)) }}</span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <!-- Detail Card -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6">
                <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">{{ $item->title }}</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-2">{{ $item->business->name }} · {{ ($item->reporter ?? $item->requester)->name }}</p>
                <p class="text-[15px] text-black/80 dark:text-white/80 whitespace-pre-line mt-6 leading-relaxed">{{ $item->description }}</p>

                @if($type === 'bugs')
                <div class="grid md:grid-cols-2 gap-4 mt-6 text-[13px]">
                    <div><p class="text-[12px] font-medium text-black/50 dark:text-white/50">Reproduksi</p><p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1">{{ $item->steps_to_reproduce ?: '-' }}</p></div>
                    <div><p class="text-[12px] font-medium text-black/50 dark:text-white/50">Environment</p><p class="text-black/80 dark:text-white/80 mt-1">{{ $item->environment ?: '-' }}</p></div>
                    <div><p class="text-[12px] font-medium text-black/50 dark:text-white/50">Expected</p><p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1">{{ $item->expected_behavior ?: '-' }}</p></div>
                    <div><p class="text-[12px] font-medium text-black/50 dark:text-white/50">Actual</p><p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1">{{ $item->actual_behavior ?: '-' }}</p></div>
                </div>
                @else
                <div class="grid md:grid-cols-2 gap-4 mt-6 text-[13px]">
                    <div><p class="text-[12px] font-medium text-black/50 dark:text-white/50">Nilai Bisnis</p><p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1">{{ $item->business_value }}</p></div>
                    <div><p class="text-[12px] font-medium text-black/50 dark:text-white/50">Use Case</p><p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1">{{ $item->use_case }}</p></div>
                </div>
                @endif
            </div>

            <!-- Riwayat Progress -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6">
                <h2 class="text-[15px] font-semibold text-black dark:text-white">Riwayat Progress</h2>
                <div class="mt-4 space-y-4">
                    @foreach($item->updates as $update)
                    <div class="border-l-2 border-[#5856D6] pl-4 text-[13px]">
                        <b class="text-black dark:text-white">{{ ucfirst(str_replace('_', ' ', $update->status)) }}</b>
                        <span class="text-black/50 dark:text-white/50 tabular-nums"> · {{ $update->created_at->format('d M Y H:i') }}</span>
                        <p class="text-black/70 dark:text-white/70 mt-1">{{ $update->comment ?: 'Progress ' . $update->progress_percent . '%' }}</p>
                    </div>
                    @endforeach
                    @if($item->updates->isEmpty())
                    <p class="text-[13px] text-black/45 dark:text-white/45">Belum ada update riwayat.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Update Status Form -->
        <form method="POST" action="{{ route($type === 'bugs' ? 'admin.feedback.bugs.update' : 'admin.feedback.features.update', $item) }}" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4 h-fit">
            @csrf
            @method('PATCH')
            <h2 class="text-[15px] font-semibold text-black dark:text-white mb-1">Update Status</h2>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Status</label>
                <select name="status" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    @foreach($statuses as $value)
                    <option value="{{ $value }}" @selected($item->status === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Prioritas</label>
                <select name="priority" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    @foreach($type === 'bugs' ? ['low','normal','high','critical'] : ['low','normal','high'] as $value)
                    <option value="{{ $value }}" @selected($item->priority === $value || $item->severity === $value)>{{ ucfirst($value) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Progress (%)</label>
                <input type="number" name="progress_percent" min="0" max="100" value="{{ $item->progress_percent }}" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Assign Admin</label>
                <select name="assigned_admin_id" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <option value="">Belum ditugaskan</option>
                    @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" @selected($item->assigned_admin_id === $admin->id)>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Catatan Update</label>
                <textarea name="comment" rows="4" class="w-full px-3 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
            </div>

            @if($type === 'bugs')
            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Resolusi</label>
                <textarea name="resolution" rows="3" class="w-full px-3 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ $item->resolution }}</textarea>
            </div>
            @else
            <div>
                <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Catatan Admin</label>
                <textarea name="admin_notes" rows="3" class="w-full px-3 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ $item->admin_notes }}</textarea>
            </div>
            @endif

            <button type="submit" class="w-full h-10 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 text-white text-[13px] font-semibold transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i>Simpan Progress
            </button>
        </form>
    </div>
</div>
@endsection