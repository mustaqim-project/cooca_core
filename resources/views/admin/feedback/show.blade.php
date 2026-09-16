@extends('layouts.admin', [
    'title' => 'Kelola Feedback - Admin Console',
    'headerTitle' => 'Kelola Feedback & Masukan',
    'headerSubtitle' => $item->title,
])

@section('content')
@php
    $st = $item->status;
    $stClass = match($st) {
        'resolved', 'released' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]',
        'closed', 'rejected' => 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]',
        'in_progress' => 'bg-[#5856D6]/15 text-[#413FA6] dark:text-[#5E5CE6]',
        default => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]'
    };
@endphp
<div class="max-w-6xl space-y-6">

    <!-- Top Navigation & Status -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route($type === 'bugs' ? 'admin.feedback.bugs.index' : 'admin.feedback.features.index') }}"
            class="text-[13px] font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="2"></i>
            <span>Kembali ke Daftar Feedback</span>
        </a>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold {{ $stClass }}">
            <span class="w-2 h-2 rounded-full bg-current"></span>
            <span>Status: {{ str_replace('_', ' ', ucfirst($st)) }}</span>
        </span>
    </div>

    <!-- Master-Detail Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Col: Details & Timeline (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Detail Bento Card -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $type === 'bugs' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                            {{ $type === 'bugs' ? 'Laporan Bug' : 'Permintaan Fitur' }}
                        </span>
                        <span class="text-[12px] text-black/40 dark:text-white/40 font-mono">{{ $item->created_at?->format('d M Y H:i') }}</span>
                    </div>
                    <h1 class="text-[22px] font-extrabold text-black dark:text-white tracking-tight leading-snug">
                        {{ $item->title }}
                    </h1>
                    <p class="text-[13px] font-medium text-black/55 dark:text-white/55 mt-1.5 flex items-center gap-1.5">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF]" stroke-width="2"></i>
                        <span>{{ $item->business->name }}</span>
                        <span class="text-black/20 dark:text-white/20">·</span>
                        <i data-lucide="user" class="w-3.5 h-3.5" stroke-width="2"></i>
                        <span>{{ ($item->reporter ?? $item->requester)->name }}</span>
                    </p>
                </div>

                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-2">Deskripsi Masukan</h4>
                    <p class="text-[14px] text-black/85 dark:text-white/85 whitespace-pre-line leading-relaxed">{{ $item->description }}</p>
                </div>

                @if($type === 'bugs')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 text-[13px]">
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Langkah Reproduksi</p>
                            <p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1.5 leading-relaxed">{{ $item->steps_to_reproduce ?: '-' }}</p>
                        </div>
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Environment Perangkat</p>
                            <p class="text-black/80 dark:text-white/80 mt-1.5 font-mono text-[12px]">{{ $item->environment ?: '-' }}</p>
                        </div>
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Ekspektasi (Expected)</p>
                            <p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1.5 leading-relaxed">{{ $item->expected_behavior ?: '-' }}</p>
                        </div>
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Kondisi Nyata (Actual)</p>
                            <p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1.5 leading-relaxed">{{ $item->actual_behavior ?: '-' }}</p>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 text-[13px]">
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Nilai Bisnis (Value)</p>
                            <p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1.5 leading-relaxed">{{ $item->business_value }}</p>
                        </div>
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Use Case Skenario</p>
                            <p class="text-black/80 dark:text-white/80 whitespace-pre-line mt-1.5 leading-relaxed">{{ $item->use_case }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Riwayat Progress Timeline Bento Card -->
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm">
                <div class="flex items-center gap-2.5 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                        <i data-lucide="history" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">Riwayat Progress Penanganan</h2>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach($item->updates as $update)
                    <div class="flex items-start gap-3.5 text-[13px]">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#5856D6] mt-1.5 shrink-0 ring-4 ring-[#5856D6]/15"></div>
                        <div class="flex-1 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04]">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="font-bold text-black dark:text-white">{{ ucfirst(str_replace('_', ' ', $update->status)) }}</span>
                                <span class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $update->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <p class="text-black/75 dark:text-white/75 mt-1 leading-relaxed">{{ $update->comment ?: 'Pembaruan progress ke ' . $update->progress_percent . '%' }}</p>
                        </div>
                    </div>
                    @endforeach

                    @if($item->updates->isEmpty())
                        <div class="text-[13px] text-black/45 dark:text-white/45 py-6 text-center">
                            Belum ada riwayat update untuk item ini.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Col: Update Status Bento Form (5 cols) -->
        <form method="POST" action="{{ route($type === 'bugs' ? 'admin.feedback.bugs.update' : 'admin.feedback.features.update', $item) }}"
            class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-4 shadow-sm lg:col-span-5 lg:sticky lg:top-20">
            @csrf
            @method('PATCH')

            <div class="pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                <h3 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="check-square" class="w-4.5 h-4.5 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="2"></i>
                    <span>Tindak Lanjut &amp; Status</span>
                </h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Perbarui status progress penanganan feedback ini.</p>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Status Penanganan</label>
                <select name="status"
                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    @foreach($statuses as $value)
                        <option value="{{ $value }}" @selected($item->status === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tingkat Prioritas</label>
                <select name="priority"
                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    @foreach($type === 'bugs' ? ['low','normal','high','critical'] : ['low','normal','high'] as $value)
                        <option value="{{ $value }}" @selected($item->priority === $value || $item->severity === $value)>{{ ucfirst($value) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Persentase Selesai (%)</label>
                <input type="number" name="progress_percent" min="0" max="100" value="{{ $item->progress_percent }}"
                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] tabular-nums font-bold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Tugaskan ke Admin</label>
                <select name="assigned_admin_id"
                    class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <option value="">Belum ditugaskan</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected($item->assigned_admin_id === $admin->id)>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Pembaruan (Update Log)</label>
                <textarea name="comment" rows="3" placeholder="Tuliskan catatan perbaikan atau progres yang dilakukan..."
                    class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
            </div>

            @if($type === 'bugs')
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Resolusi Akhir Bug</label>
                    <textarea name="resolution" rows="2" placeholder="Jelaskan cara bug diselesaikan..."
                        class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ $item->resolution }}</textarea>
                </div>
            @else
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Catatan Internal Admin</label>
                    <textarea name="admin_notes" rows="2" placeholder="Catatan arsitektur atau pertimbangan rilis..."
                        class="w-full px-3.5 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">{{ $item->admin_notes }}</textarea>
                </div>
            @endif

            <button type="submit"
                class="w-full h-11 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25">
                <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                <span>Simpan Tindak Lanjut</span>
            </button>
        </form>

    </div>
</div>
@endsection