@extends('layouts.app', [
    'title' => 'Kotak Masuk Persetujuan - Cooca',
    'headerTitle' => 'Pusat Otorisasi Dokumen (MAR)',
    'headerSubtitle' => 'Kelola dan setujui permohonan anggaran pengadaan, biaya kas, dan faktur bertingkat.'
])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="approvalInbox()">

    <!-- Top Bento Navigation & Action Bar -->
    <header class="rounded-[20px] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="inbox" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    Otorisasi Bertingkat
                </div>
                <h1 class="text-xl font-bold text-black dark:text-white tracking-tight">
                    Kotak Masuk Persetujuan (Inbox)
                </h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('approvals.history') }}" class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-black/75 dark:text-white/75 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] active:scale-[0.98] transition-all flex items-center gap-1.5">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Riwayat Selesai</span>
            </a>
            @if(\App\Support\Context::isOwner() || \App\Support\Context::isAdminOrOwner())
            <a href="{{ route('approval-rules.index') }}" class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center gap-1.5">
                <i data-lucide="sliders" class="w-4 h-4"></i>
                <span>Aturan Plafon Approval</span>
            </a>
            @endif
        </div>
    </header>

    <!-- Filter Bar (Apple Segmented Style) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
        <a href="{{ route('approvals.inbox') }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ empty($filterType) ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <span>Semua Dokumen</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] tabular-nums font-bold {{ empty($filterType) ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                {{ $counts['all'] ?? 0 }}
            </span>
        </a>
        <a href="{{ route('approvals.inbox', ['type' => 'purchase_order']) }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ $filterType === 'purchase_order' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
            <span>Purchase Order</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] tabular-nums font-bold {{ $filterType === 'purchase_order' ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                {{ $counts['purchase_order'] ?? 0 }}
            </span>
        </a>
        <a href="{{ route('approvals.inbox', ['type' => 'expense']) }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ $filterType === 'expense' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
            <span>Biaya Kas</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] tabular-nums font-bold {{ $filterType === 'expense' ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                {{ $counts['expense'] ?? 0 }}
            </span>
        </a>
        <a href="{{ route('approvals.inbox', ['type' => 'supplier_invoice']) }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ $filterType === 'supplier_invoice' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
            <span>Faktur Supplier</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] tabular-nums font-bold {{ $filterType === 'supplier_invoice' ? 'bg-white/20 text-white' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70' }}">
                {{ $counts['supplier_invoice'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Ticket List Container -->
    <div class="space-y-4">
        @forelse ($pendingTickets as $ticket)
            @php
                $docModel = $ticket->getDocumentModel();
                $targetUrl = match ($ticket->document_type) {
                    'purchase_order' => route('purchase-orders.show', $ticket->document_id),
                    'supplier_invoice' => route('purchasing.bills.show', $ticket->document_id),
                    default => '#',
                };
            @endphp
            <div class="rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-sm hover:border-[#007AFF]/30 transition-all space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    
                    <!-- Left: Info Transaksi -->
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] uppercase tracking-wider">
                                {{ $ticket->getDocumentTypeLabel() }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#FF9500]">
                                Menunggu Level {{ $ticket->current_level }} dari {{ $ticket->total_levels }}
                            </span>
                            <span class="text-xs text-black/40 dark:text-white/40">·</span>
                            <span class="text-xs text-black/50 dark:text-white/50 tabular-nums">
                                Diajukan {{ $ticket->created_at->diffForHumans() }} ({{ $ticket->created_at->format('d/m/Y H:i') }})
                            </span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-3">
                            <h3 class="text-2xl sm:text-3xl font-bold text-black dark:text-white tracking-tight tabular-nums">
                                Rp {{ number_format($ticket->amount, 0, ',', '.') }}
                            </h3>
                            @if ($targetUrl !== '#')
                                <a href="{{ $targetUrl }}" target="_blank" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                    <span>Buka Dokumen Asli</span>
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>

                        <div class="text-xs text-black/60 dark:text-white/60 flex flex-wrap items-center gap-4">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                <span>Pembuat Draf (Maker): <strong class="text-black dark:text-white">{{ $ticket->requester->name ?? 'Staf' }}</strong></span>
                            </span>
                            @if ($ticket->rule)
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                    <span>Aturan: <strong class="text-black dark:text-white">{{ $ticket->rule->name }}</strong></span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Tindakan Otorisasi Cepat -->
                    <div class="flex items-center gap-3 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-black/[0.06] dark:border-white/[0.08]">
                        <button
                            type="button"
                            @click="openRejectModal('{{ $ticket->id }}', '{{ $ticket->getDocumentTypeLabel() }}', 'Rp {{ number_format($ticket->amount, 0, ',', '.') }}')"
                            class="min-h-[44px] px-4 py-2.5 rounded-[12px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] font-semibold text-xs transition-all active:scale-[0.98] flex items-center gap-1.5">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            <span>Tolak</span>
                        </button>
                        <button
                            type="button"
                            @click="openApproveModal('{{ $ticket->id }}', '{{ $ticket->getDocumentTypeLabel() }}', 'Rp {{ number_format($ticket->amount, 0, ',', '.') }}', {{ $ticket->current_level }})"
                            class="min-h-[44px] px-6 py-2.5 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white font-semibold text-xs shadow-sm shadow-[#34C759]/25 transition-all active:scale-[0.98] flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span>Setujui (Level {{ $ticket->current_level }})</span>
                        </button>
                    </div>

                </div>
            </div>
        @empty
            <div class="p-12 text-center rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                <div class="w-14 h-14 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center mx-auto">
                    <i data-lucide="check-check" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-black dark:text-white">Kotak Masuk Bersih</h3>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mx-auto">
                    Tidak ada permohonan persetujuan yang sedang menunggu tindakan Anda. Semua transaksi telah diproses.
                </p>
            </div>
        @endforelse
    </div>

    <!-- ======================================================= -->
    <!-- MODAL KONFIRMASI SETUJUI (APPLE HIG DIALOG / SHEET)     -->
    <!-- ======================================================= -->
    <div
        x-show="approveModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-md animate-fade-in"
        @keydown.escape.window="approveModalOpen = false">
        
        <div
            @click.outside="approveModalOpen = false"
            class="w-full sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-2xl space-y-4 max-h-[94vh] flex flex-col overflow-hidden">
            
            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-2 sm:hidden shrink-0"></div>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Setujui Dokumen</h3>
                    <p class="text-xs text-black/55 dark:text-white/55" x-text="activeDocTitle"></p>
                </div>
            </div>
            
            <form :action="'{{ url('/approvals') }}/' + activeTicketId + '/approve'" method="POST" class="space-y-4">
                @csrf
                <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-xs">
                    <div class="text-black/60 dark:text-white/60">Nominal Transaksi:</div>
                    <div class="text-lg font-bold text-black dark:text-white mt-0.5 tabular-nums" x-text="activeDocAmount"></div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Catatan Persetujuan (Opsional)
                    </label>
                    <textarea
                        name="notes"
                        rows="2"
                        placeholder="Contoh: Anggaran telah diperiksa dan disetujui."
                        class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none resize-none focus:ring-2 focus:ring-[#34C759]/50"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="approveModalOpen = false"
                        class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 active:scale-[0.98]">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-xs font-semibold shadow-sm active:scale-[0.98]">
                        Konfirmasi Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL KONFIRMASI TOLAK (APPLE HIG DIALOG / SHEET)       -->
    <!-- ======================================================= -->
    <div
        x-show="rejectModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-md animate-fade-in"
        @keydown.escape.window="rejectModalOpen = false">
        
        <div
            @click.outside="rejectModalOpen = false"
            class="w-full sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-2xl space-y-4 max-h-[94vh] flex flex-col overflow-hidden">
            
            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-2 sm:hidden shrink-0"></div>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Tolak Dokumen</h3>
                    <p class="text-xs text-black/55 dark:text-white/55" x-text="activeDocTitle"></p>
                </div>
            </div>
            
            <form :action="'{{ url('/approvals') }}/' + activeTicketId + '/reject'" method="POST" class="space-y-4">
                @csrf
                <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/5 border border-[#FF3B30]/15 text-xs">
                    <div class="text-[#FF3B30] font-semibold">Perhatian:</div>
                    <div class="text-black/70 dark:text-white/70 mt-0.5">Penolakan dokumen akan menghentikan alur pencairan anggaran. Pembuat draf (Maker) akan menerima alasan penolakan ini.</div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Alasan Penolakan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <textarea
                        name="reason"
                        rows="3"
                        required
                        x-model="rejectionReasonText"
                        placeholder="Contoh: Harga melampaui batas anggaran atau kuota bulan ini sudah habis."
                        class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none resize-none focus:ring-2 focus:ring-[#FF3B30]/50"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button
                        type="button"
                        @click="rejectModalOpen = false"
                        class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 active:scale-[0.98]">
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="!rejectionReasonText.trim()"
                        class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#FF3B30] hover:bg-[#E0352B] disabled:opacity-50 text-white text-xs font-semibold shadow-sm active:scale-[0.98]">
                        Tolak Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function approvalInbox() {
    return {
        approveModalOpen: false,
        rejectModalOpen: false,
        activeTicketId: '',
        activeDocTitle: '',
        activeDocAmount: '',
        rejectionReasonText: '',

        openApproveModal(ticketId, docTitle, docAmount, level) {
            this.activeTicketId = ticketId;
            this.activeDocTitle = docTitle + ' (Level ' + level + ')';
            this.activeDocAmount = docAmount;
            this.approveModalOpen = true;
        },

        openRejectModal(ticketId, docTitle, docAmount) {
            this.activeTicketId = ticketId;
            this.activeDocTitle = docTitle;
            this.activeDocAmount = docAmount;
            this.rejectionReasonText = '';
            this.rejectModalOpen = true;
        }
    };
}
</script>
@endsection
