@extends('layouts.app', [
    'title' => 'Detail Jejak Audit - Cooca',
    'headerTitle' => 'Detail Forensik Log Audit',
    'headerSubtitle' => 'Pemeriksaan komparasi data lama dan baru (Visual Diff).'
])

@section('content')
<div class="max-w-[1000px] mx-auto space-y-6 pb-14">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('settings.audit-logs.index') }}"
           class="min-h-[40px] px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75 font-semibold text-[13px] hover:bg-black/[0.08] active:scale-95 transition-all inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Explorer</span>
        </a>

        @if($auditLog->isHighRisk())
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12.5px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
            <i data-lucide="shield-alert" class="w-4 h-4"></i>
            <span>RISIKO TINGGI</span>
        </span>
        @endif
    </div>

    <!-- Forensics Card -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-6 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-5">
            <div>
                <h2 class="text-xl font-bold text-black dark:text-white">
                    {{ $auditLog->getModuleLabel() }} ({{ strtoupper($auditLog->action) }})
                </h2>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-0.5">
                    Dicatat pada {{ $auditLog->created_at ? $auditLog->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i:s') : '-' }} WIB
                </p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[12px] text-black/50 dark:text-white/50 block">ID Referensi Log</span>
                <span class="font-mono text-[13px] font-semibold text-black dark:text-white">{{ $auditLog->id }}</span>
            </div>
        </div>

        <!-- Forensics Detail Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] p-4 text-[13px]">
            <div>
                <span class="font-bold text-black/50 dark:text-white/50 block">Pelaku</span>
                <span class="font-semibold text-black dark:text-white block mt-0.5">{{ $auditLog->user?->name ?? 'Sistem / Otomatis' }}</span>
                <span class="text-black/40 dark:text-white/40 text-[11.5px] block">{{ $auditLog->user?->email ?? '-' }}</span>
            </div>
            <div>
                <span class="font-bold text-black/50 dark:text-white/50 block">Alamat IP & Perangkat</span>
                <span class="font-mono text-black dark:text-white block mt-0.5">{{ $auditLog->ip_address ?: '127.0.0.1' }}</span>
                <span class="text-black/40 dark:text-white/40 text-[11.5px] block truncate" title="{{ $auditLog->user_agent }}">{{ $auditLog->user_agent ?: '-' }}</span>
            </div>
            <div>
                <span class="font-bold text-black/50 dark:text-white/50 block">Status Peringatan WhatsApp</span>
                @if($auditLog->alert_sent_at)
                <span class="font-semibold text-emerald-600 dark:text-emerald-400 block mt-0.5">
                    Terkirim ke {{ $auditLog->alert_recipient }}
                </span>
                <span class="text-black/40 dark:text-white/40 text-[11.5px] block">
                    {{ $auditLog->alert_sent_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                </span>
                @else
                <span class="text-black/40 dark:text-white/40 block mt-0.5">Tidak dipicu</span>
                @endif
            </div>
        </div>

        @if($auditLog->risk_reason || $auditLog->notes)
        <div class="p-4 rounded-[16px] {{ $auditLog->isHighRisk() ? 'bg-red-500/10 border border-red-500/20 text-red-900 dark:text-red-200' : 'bg-amber-500/10 border border-amber-500/20 text-amber-900 dark:text-amber-200' }}">
            <div class="font-bold text-[13.5px]">{{ $auditLog->risk_reason ?: 'Aktivitas Tercatat' }}</div>
            <div class="text-[13px] mt-1 text-black/75 dark:text-white/80">Alasan/Catatan: {{ $auditLog->notes ?: '-' }}</div>
        </div>
        @endif

        <!-- Diff Table -->
        <div>
            <h3 class="text-[13.5px] font-bold text-black/70 dark:text-white/70 uppercase tracking-wider mb-3">
                Perbandingan Nilai Data (Old vs. New)
            </h3>

            <div class="rounded-[18px] border border-black/5 dark:border-white/10 overflow-hidden">
                <table class="w-full text-left text-[13.5px]">
                    <thead>
                        <tr class="bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-bold text-[12px] uppercase">
                            <th class="py-3 px-4 w-1/3">Nama Atribut</th>
                            <th class="py-3 px-4 w-1/3">Nilai Lama</th>
                            <th class="py-3 px-4 w-1/3">Nilai Baru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                        @forelse($diffs as $diff)
                        <tr class="{{ $diff['is_different'] ? 'bg-amber-500/[0.03]' : '' }}">
                            <td class="py-3.5 px-4 font-mono font-medium text-black dark:text-white">{{ $diff['label'] }}</td>
                            <td class="py-3.5 px-4">
                                <span class="{{ $diff['is_different'] ? 'line-through text-red-600 dark:text-red-400 bg-red-500/10 px-2 py-0.5 rounded font-mono text-[12.5px]' : 'text-black/50 dark:text-white/50 font-mono text-[12.5px]' }}">
                                    {{ $diff['old'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="{{ $diff['is_different'] ? 'font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded font-mono text-[12.5px]' : 'text-black/75 dark:text-white/75 font-mono text-[12.5px]' }}">
                                    {{ $diff['new'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-black/40 dark:text-white/40">
                                Tidak ada atribut data yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
