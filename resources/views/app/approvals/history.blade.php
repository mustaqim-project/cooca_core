@extends('layouts.app', [
    'title' => 'Riwayat Otorisasi Dokumen - Cooca',
    'headerTitle' => 'Riwayat Otorisasi Dokumen',
    'headerSubtitle' => 'Arsip audit trail persetujuan dan penolakan transaksi masa lalu.'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">

    <!-- Top Header Bar -->
    <header class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                <i data-lucide="history" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-black dark:text-white tracking-tight">
                    Riwayat Persetujuan Dokumen
                </h1>
                <p class="text-xs text-black/55 dark:text-white/55">
                    Catatan permanen persetujuan dan penolakan untuk kepatuhan tata kelola.
                </p>
            </div>
        </div>

        <a href="{{ route('approvals.inbox') }}" class="min-h-[40px] px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-95 transition-all flex items-center gap-1.5">
            <i data-lucide="inbox" class="w-4 h-4"></i>
            <span>Kembali ke Kotak Masuk</span>
        </a>
    </header>

    <!-- History Table / Cards -->
    <div class="glass-card bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] rounded-[22px] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/[0.06] dark:border-white/[0.06] text-black/60 dark:text-white/60 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Dokumen &amp; Jenis</th>
                        <th class="px-5 py-3.5">Pembuat (Maker)</th>
                        <th class="px-5 py-3.5">Nominal Transaksi</th>
                        <th class="px-5 py-3.5">Status Akhir</th>
                        <th class="px-5 py-3.5">Waktu Selesai</th>
                        <th class="px-5 py-3.5">Audit Log Penyetuju</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.05]">
                    @forelse ($historyTickets as $ticket)
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-bold text-black dark:text-white">{{ $ticket->getDocumentTypeLabel() }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-mono mt-0.5">ID: {{ substr($ticket->document_id, 0, 8) }}...</div>
                            </td>
                            <td class="px-5 py-4 text-black/80 dark:text-white/80 font-medium">
                                {{ $ticket->requester->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($ticket->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($ticket->isApproved())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                        <span>Disetujui</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-black/60 dark:text-white/60 tabular-nums">
                                {{ ($ticket->approved_at ?? $ticket->rejected_at)?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    @foreach ($ticket->logs as $log)
                                        <div class="text-[11px] text-black/70 dark:text-white/70">
                                            <strong>L{{ $log->level }}:</strong> {{ $log->approver->name ?? 'User' }}
                                            ({{ ucfirst($log->action) }})
                                            @if($log->notes)
                                                · <span class="italic text-black/50 dark:text-white/50">"{{ $log->notes }}"</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-black/40 dark:text-white/40">
                                Belum ada riwayat dokumen yang selesai diotorisasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
