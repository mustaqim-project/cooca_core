@extends('layouts.admin', [
    'title' => 'Pemulihan Akun - Admin Console',
    'headerTitle' => 'Pusat Verifikasi Pemulihan Akun',
    'headerSubtitle' => 'Verifikasi berkas identitas dan bukti usaha untuk pemulihan akses akun pemilik bisnis'
])

@section('content')
<div class="space-y-6 max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10"
     x-data="{
         copiedTicket: null,
         inspectionModalOpen: false,
         selectedRecovery: null,
         imageModalOpen: false,
         lightboxImageUrl: '',
         lightboxTitle: '',
         confirmApproveModal: false,
         confirmRejectModal: false,
         openInspection(rec) {
             this.selectedRecovery = rec;
             this.inspectionModalOpen = true;
         },
         closeInspection() {
             this.inspectionModalOpen = false;
             this.selectedRecovery = null;
             this.confirmApproveModal = false;
             this.confirmRejectModal = false;
         },
         openLightbox(url, title) {
             this.lightboxImageUrl = url;
             this.lightboxTitle = title;
             this.imageModalOpen = true;
         }
     }">

    <!-- ═══ BENTO STAT COUNTER HERO ═══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Menunggu Review (Hero Focus Card) -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'pending']) }}"
           class="p-4 sm:p-5 rounded-[20px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'pending' ? 'border-[#FF9500] ring-2 ring-[#FF9500]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#FF9500]/40 transition-all duration-200 block relative overflow-hidden group shadow-sm hover:shadow-md">
            @if($counts['pending'] > 0)
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#FF9500]/10 rounded-full blur-2xl pointer-events-none"></div>
            @endif
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Menunggu Review</span>
                <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-bold tracking-tight tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                {{ number_format($counts['pending'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Permohonan perlu verifikasi berkas</p>
        </a>

        <!-- 2. Total Pengajuan -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'all']) }}"
           class="p-4 sm:p-5 rounded-[20px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'all' ? 'border-[#007AFF] ring-2 ring-[#007AFF]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#007AFF]/40 transition-all duration-200 block shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Total Pengajuan</span>
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="inbox" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-bold tracking-tight tabular-nums text-black dark:text-white">
                {{ number_format($counts['all'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Seluruh tiket historis tercatat</p>
        </a>

        <!-- 3. Disetujui Resmi -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'approved']) }}"
           class="p-4 sm:p-5 rounded-[20px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'approved' ? 'border-[#34C759] ring-2 ring-[#34C759]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#34C759]/40 transition-all duration-200 block shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Disetujui Resmi</span>
                <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-bold tracking-tight tabular-nums text-[#34C759] dark:text-[#30D158]">
                {{ number_format($counts['approved'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-[#34C759]/80 mt-1">Akses akun berhasil dipulihkan</p>
        </a>

        <!-- 4. Permohonan Ditolak -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'rejected']) }}"
           class="p-4 sm:p-5 rounded-[20px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'rejected' ? 'border-[#FF3B30] ring-2 ring-[#FF3B30]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#FF3B30]/40 transition-all duration-200 block shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Permohonan Ditolak</span>
                <div class="w-8 h-8 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-bold tracking-tight tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                {{ number_format($counts['rejected'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-[#FF3B30]/80 mt-1">Dokumen tidak memenuhi syarat</p>
        </a>
    </div>

    <!-- ═══ BENTO TOOLBAR: SEGMENTED CONTROLS & SEARCH ═══ -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 sm:p-3.5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
        <!-- Segmented Filter Status -->
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-xs font-medium overflow-x-auto no-scrollbar">
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'all', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap transition-all {{ $status === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Semua ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'pending', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap flex items-center gap-1.5 transition-all {{ $status === 'pending' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] dark:text-[#FF9F0A] font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <span>Menunggu Review</span>
                @if($counts['pending'] > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A]">{{ $counts['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'approved', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap transition-all {{ $status === 'approved' ? 'bg-white dark:bg-[#2C2C2E] text-[#34C759] dark:text-[#30D158] font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Disetujui ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'rejected', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap transition-all {{ $status === 'rejected' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF3B30] dark:text-[#FF453A] font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Ditolak ({{ $counts['rejected'] }})
            </a>
        </div>

        <!-- Search Bar with Anti-Zoom input text-[16px] sm:text-xs -->
        <form method="GET" action="{{ route('admin.account-recoveries.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari tiket, pemohon, email, WA..."
                       class="w-full pl-9 pr-3.5 py-2 min-h-[40px] rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition-all">
            </div>
            @if($search)
                <a href="{{ route('admin.account-recoveries.index', ['status' => $status]) }}"
                   class="px-3 py-2 min-h-[40px] rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white text-xs font-medium transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- ═══ DATA LIST CONTAINER ═══ -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-sm">
        <!-- DESKTOP TABLE (hidden md:block) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-semibold text-black/45 dark:text-white/45">
                    <tr>
                        <th class="py-3.5 px-4">Nomor Tiket &amp; Waktu</th>
                        <th class="py-3.5 px-4">Pemohon &amp; Bisnis</th>
                        <th class="py-3.5 px-4">Jenis Kendala</th>
                        <th class="py-3.5 px-4">Kontak Lama</th>
                        <th class="py-3.5 px-4">Kontak Baru</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.05] text-black/80 dark:text-white/80">
                    @forelse ($recoveries as $rec)
                        @php
                            $recPayload = [
                                'id' => $rec->id,
                                'ticket_number' => $rec->ticket_number,
                                'applicant_name' => $rec->applicant_name,
                                'business_name' => $rec->business_name,
                                'old_email' => $rec->old_email,
                                'old_phone' => $rec->old_phone ?? '-',
                                'new_email' => $rec->new_email,
                                'new_phone' => $rec->new_phone,
                                'issue_type_label' => $rec->getIssueTypeLabel(),
                                'reason_description' => $rec->reason_description,
                                'status' => $rec->status,
                                'created_at_human' => $rec->created_at->format('d M Y, H:i') . ' WIB',
                                'identity_url' => $rec->getIdentityCardUrl(),
                                'business_url' => $rec->getBusinessProofUrl(),
                                'selfie_url' => $rec->getSelfieProofUrl(),
                                'is_business_pdf' => (bool) (preg_match('/\.pdf$/i', (string) $rec->business_proof_path)),
                                'show_url' => route('admin.account-recoveries.show', $rec),
                                'approve_url' => route('admin.account-recoveries.approve', $rec),
                                'reject_url' => route('admin.account-recoveries.reject', $rec),
                                'admin_notes' => $rec->admin_notes,
                                'rejection_reason' => $rec->rejection_reason,
                                'approver_name' => $rec->approver?->name,
                                'approved_at_human' => $rec->approved_at?->format('d M Y, H:i') . ' WIB',
                                'rejector_name' => $rec->rejector?->name,
                                'rejected_at_human' => $rec->rejected_at?->format('d M Y, H:i') . ' WIB',
                                'ip_address' => $rec->ip_address ?? '-',
                            ];
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                            <!-- Ticket Number & Clipboard Copy -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm">{{ $rec->ticket_number }}</span>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $rec->ticket_number }}'); copiedTicket = '{{ $rec->id }}'; setTimeout(() => copiedTicket = null, 2000)"
                                            class="p-1 rounded-[6px] text-black/35 dark:text-white/35 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                                            title="Salin nomor tiket">
                                        <i data-lucide="copy" class="w-3.5 h-3.5" x-show="copiedTicket !== '{{ $rec->id }}'"></i>
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copiedTicket === '{{ $rec->id }}'" style="display: none;"></i>
                                    </button>
                                </div>
                                <span class="text-[10px] text-black/40 dark:text-white/40 block mt-0.5 tabular-nums">{{ $rec->created_at->format('d M Y, H:i') }} WIB</span>
                            </td>

                            <!-- Applicant & Business -->
                            <td class="py-4 px-4">
                                <div class="font-semibold text-black dark:text-white text-xs sm:text-sm">{{ $rec->applicant_name }}</div>
                                <div class="text-[11px] text-black/55 dark:text-white/55 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="store" class="w-3 h-3 shrink-0 text-black/40 dark:text-white/40"></i>
                                    <span>{{ $rec->business_name }}</span>
                                </div>
                            </td>

                            <!-- Issue Type (Clean Typography, No Pill Abuse) -->
                            <td class="py-4 px-4">
                                <span class="text-[11.5px] font-medium text-black/75 dark:text-white/75 leading-tight block">
                                    {{ $rec->getIssueTypeLabel() }}
                                </span>
                            </td>

                            <!-- Old Contact -->
                            <td class="py-4 px-4 font-mono text-[11px]">
                                <span class="text-black/60 dark:text-white/60 block truncate max-w-[170px]">{{ $rec->old_email }}</span>
                                <span class="text-black/40 dark:text-white/40 text-[10px] tabular-nums">{{ $rec->old_phone ?? '-' }}</span>
                            </td>

                            <!-- New Contact -->
                            <td class="py-4 px-4 font-mono text-[11px]">
                                <span class="text-[#34C759] dark:text-[#30D158] font-semibold block truncate max-w-[170px]">{{ $rec->new_email }}</span>
                                <span class="text-black/70 dark:text-white/70 text-[10px] tabular-nums">{{ $rec->new_phone }}</span>
                            </td>

                            <!-- Status Badge (Only 1 Lifecycle Badge) -->
                            <td class="py-4 px-4 text-center">
                                @if ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A]">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>Menunggu Review</span>
                                    </span>
                                @elseif ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158]">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>Disetujui</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#FF3B30] dark:text-[#FF453A]">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons: Modal-First Inspection & Direct Link -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                            @click="openInspection({{ json_encode($recPayload) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 min-h-[36px] rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/18 text-[#007AFF] dark:text-[#0A84FF] font-semibold text-xs transition-all active:scale-[0.98]">
                                        <span>Periksa Berkas</span>
                                        <i data-lucide="scan-eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <a href="{{ route('admin.account-recoveries.show', $rec) }}"
                                       class="p-2 min-h-[36px] min-w-[36px] rounded-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] dark:hover:text-[#0A84FF] hover:bg-black/5 dark:hover:bg-white/10 transition-colors flex items-center justify-center"
                                       title="Buka halaman penuh">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center text-black/40 dark:text-white/40">
                                <div class="w-12 h-12 rounded-2xl bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mx-auto mb-3 text-black/30 dark:text-white/30">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <span class="font-medium text-xs sm:text-sm">Tidak ada permohonan pemulihan akun pada filter ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- MOBILE CARDS (md:hidden) -->
        <div class="md:hidden divide-y divide-black/[0.06] dark:divide-white/[0.08]">
            @forelse ($recoveries as $rec)
                @php
                    $recPayload = [
                        'id' => $rec->id,
                        'ticket_number' => $rec->ticket_number,
                        'applicant_name' => $rec->applicant_name,
                        'business_name' => $rec->business_name,
                        'old_email' => $rec->old_email,
                        'old_phone' => $rec->old_phone ?? '-',
                        'new_email' => $rec->new_email,
                        'new_phone' => $rec->new_phone,
                        'issue_type_label' => $rec->getIssueTypeLabel(),
                        'reason_description' => $rec->reason_description,
                        'status' => $rec->status,
                        'created_at_human' => $rec->created_at->format('d M Y, H:i') . ' WIB',
                        'identity_url' => $rec->getIdentityCardUrl(),
                        'business_url' => $rec->getBusinessProofUrl(),
                        'selfie_url' => $rec->getSelfieProofUrl(),
                        'is_business_pdf' => (bool) (preg_match('/\.pdf$/i', (string) $rec->business_proof_path)),
                        'show_url' => route('admin.account-recoveries.show', $rec),
                        'approve_url' => route('admin.account-recoveries.approve', $rec),
                        'reject_url' => route('admin.account-recoveries.reject', $rec),
                        'admin_notes' => $rec->admin_notes,
                        'rejection_reason' => $rec->rejection_reason,
                        'approver_name' => $rec->approver?->name,
                        'approved_at_human' => $rec->approved_at?->format('d M Y, H:i') . ' WIB',
                        'rejector_name' => $rec->rejector?->name,
                        'rejected_at_human' => $rec->rejected_at?->format('d M Y, H:i') . ' WIB',
                        'ip_address' => $rec->ip_address ?? '-',
                    ];
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-sm text-[#007AFF] dark:text-[#0A84FF]">{{ $rec->ticket_number }}</span>
                            <span class="text-[10px] text-black/40 dark:text-white/40 block mt-0.5 tabular-nums">{{ $rec->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div>
                            @if ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] inline-flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span>Menunggu</span>
                                </span>
                            @elseif ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158]">Disetujui</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#FF3B30]/12 text-[#FF3B30] dark:text-[#FF453A]">Ditolak</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="font-semibold text-sm text-black dark:text-white">{{ $rec->applicant_name }}</div>
                        <div class="text-xs text-black/55 dark:text-white/55 flex items-center gap-1 mt-0.5">
                            <i data-lucide="store" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                            <span>{{ $rec->business_name }}</span>
                        </div>
                    </div>

                    <!-- Contact Details Box -->
                    <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-black/45 dark:text-white/45 text-[11px]">Kendala:</span>
                            <span class="font-medium text-black/80 dark:text-white/80 text-[11px] text-right">{{ $rec->getIssueTypeLabel() }}</span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-black/45 dark:text-white/45 text-[11px]">Email Baru:</span>
                            <span class="font-semibold text-[#34C759] dark:text-[#30D158] text-[11px]">{{ $rec->new_email }}</span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-black/45 dark:text-white/45 text-[11px]">WA Baru:</span>
                            <span class="text-black/75 dark:text-white/75 text-[11px] tabular-nums">{{ $rec->new_phone }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons: Modal Sheet & Full Page -->
                    <div class="pt-1 flex items-center gap-2">
                        <button type="button"
                                @click="openInspection({{ json_encode($recPayload) }})"
                                class="flex-1 min-h-[48px] py-3 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98]">
                            <i data-lucide="scan-eye" class="w-4 h-4"></i>
                            <span>Periksa Berkas</span>
                        </button>
                        <a href="{{ route('admin.account-recoveries.show', $rec) }}"
                           class="min-h-[48px] px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white flex items-center justify-center transition-colors"
                           title="Buka halaman detail">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-black/40 dark:text-white/40 text-xs">
                    Tidak ada pengajuan pemulihan akun.
                </div>
            @endforelse
        </div>

        @if ($recoveries->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $recoveries->links() }}
            </div>
        @endif
    </div>

    <!-- ═══ MODAL-FIRST XXL INSPECTION DESK (Apple HIG Sheet) ═══ -->
    <div x-show="inspectionModalOpen"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 backdrop-blur-md"
         x-cloak style="display: none;">
        <div class="w-full max-w-full sm:max-w-5xl xl:max-w-6xl max-h-[94vh] sm:max-h-[90vh] bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl flex flex-col overflow-hidden"
             @click.stop>

            <!-- Mobile Grab Handle -->
            <div class="w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mt-2.5 sm:hidden shrink-0"></div>

            <!-- Modal Header (Sticky) -->
            <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-6 py-4 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-sm sm:text-base text-[#007AFF] dark:text-[#0A84FF]" x-text="selectedRecovery?.ticket_number"></span>
                            <!-- Status Pill inside Header -->
                            <template x-if="selectedRecovery?.status === 'pending'">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A]">Menunggu Review</span>
                            </template>
                            <template x-if="selectedRecovery?.status === 'approved'">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158]">Disetujui</span>
                            </template>
                            <template x-if="selectedRecovery?.status === 'rejected'">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/12 text-[#FF3B30] dark:text-[#FF453A]">Ditolak</span>
                            </template>
                        </div>
                        <h3 class="text-xs text-black/50 dark:text-white/50 truncate mt-0.5">
                            Permohonan oleh <strong class="text-black dark:text-white" x-text="selectedRecovery?.applicant_name"></strong> &bull; <span x-text="selectedRecovery?.business_name"></span>
                        </h3>
                    </div>
                </div>

                <!-- Right Actions: Dedicated Show Link + Circle Close Button -->
                <div class="flex items-center gap-2 shrink-0">
                    <a :href="selectedRecovery?.show_url"
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/70 dark:text-white/70 transition-colors">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Halaman Penuh</span>
                    </a>
                    <button type="button" @click="closeInspection()"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center transition-all active:scale-95">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body (Scrollable XXL Bento) -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 sidebar-scroll">

                <!-- 1. BENTO KOMPARASI KONTAK LAMA VS BARU -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Data Terdaftar Lama -->
                    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                        <div class="flex items-center gap-1.5 text-black/45 dark:text-white/45 font-semibold text-[11px] uppercase tracking-wider">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <span>Data Terdaftar Saat Ini (Lama)</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">Email Akun:</span>
                            <span class="font-mono text-xs sm:text-sm text-black/80 dark:text-white/80 select-all break-all" x-text="selectedRecovery?.old_email"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">WhatsApp Terdaftar:</span>
                            <span class="font-mono text-xs sm:text-sm text-black/80 dark:text-white/80 select-all tabular-nums" x-text="selectedRecovery?.old_phone"></span>
                        </div>
                    </div>

                    <!-- Kontak Baru Yang Diajukan -->
                    <div class="p-4 rounded-[18px] bg-[#34C759]/10 dark:bg-[#30D158]/10 border border-[#34C759]/20 space-y-2.5">
                        <div class="flex items-center gap-1.5 text-[#34C759] dark:text-[#30D158] font-semibold text-[11px] uppercase tracking-wider">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span>Kontak Baru Yang Diajukan</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">Alamat Email Baru:</span>
                            <span class="font-mono font-semibold text-xs sm:text-sm text-[#34C759] dark:text-[#30D158] select-all break-all" x-text="selectedRecovery?.new_email"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">Nomor WhatsApp Baru:</span>
                            <span class="font-mono font-bold text-xs sm:text-sm text-black dark:text-white select-all tabular-nums" x-text="selectedRecovery?.new_phone"></span>
                        </div>
                    </div>
                </div>

                <!-- Info Usaha & Catatan Pemohon -->
                <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">Jenis Kendala:</span>
                            <span class="font-semibold text-black dark:text-white" x-text="selectedRecovery?.issue_type_label"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">Waktu Pengajuan:</span>
                            <span class="font-medium text-black/70 dark:text-white/70 tabular-nums" x-text="selectedRecovery?.created_at_human"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/40 dark:text-white/40 block">Alamat IP:</span>
                            <span class="font-mono font-medium text-black/70 dark:text-white/70" x-text="selectedRecovery?.ip_address"></span>
                        </div>
                    </div>
                    <template x-if="selectedRecovery?.reason_description">
                        <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[10px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block mb-1">Pernyataan Pemohon Mengenai Kendala:</span>
                            <p class="text-xs text-black/75 dark:text-white/75 italic leading-relaxed" x-text="'&ldquo;' + selectedRecovery?.reason_description + '&rdquo;'"></p>
                        </div>
                    </template>
                </div>

                <!-- 2. MEJA UJI BUKTI OTENTIK (DOCUMENT TILES) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-check" class="w-4 h-4 text-[#5856D6]"></i>
                            <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Meja Uji Bukti Otentik Kepemilikan Akun</h4>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Klik dokumen untuk perbesar</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <!-- Dokumen 1: KTP Asli -->
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="credit-card" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>1. Foto KTP Asli</span>
                                </span>
                                <span class="text-[10px] font-bold text-[#007AFF] uppercase">Wajib</span>
                            </div>
                            <template x-if="selectedRecovery?.identity_url">
                                <div class="space-y-1.5">
                                    <div class="relative group rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 cursor-pointer aspect-video"
                                         @click="openLightbox(selectedRecovery.identity_url, 'Foto KTP: ' + selectedRecovery.applicant_name)">
                                        <img :src="selectedRecovery.identity_url" alt="KTP Pemohon"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium gap-1.5 backdrop-blur-[2px]">
                                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                            <span>Perbesar</span>
                                        </div>
                                    </div>
                                    <a :href="selectedRecovery.identity_url" target="_blank"
                                       class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        <span>Buka Resolusi Penuh</span>
                                    </a>
                                </div>
                            </template>
                            <template x-if="!selectedRecovery?.identity_url">
                                <div class="py-7 text-center text-xs text-black/40 dark:text-white/40">Tidak dilampirkan</div>
                            </template>
                        </div>

                        <!-- Dokumen 2: Bukti Legalitas Usaha -->
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                    <span>2. Dokumen Usaha</span>
                                </span>
                                <span class="text-[10px] font-bold text-[#5856D6] uppercase">Wajib</span>
                            </div>
                            <template x-if="selectedRecovery?.business_url">
                                <div class="space-y-1.5">
                                    <!-- Jika format PDF -->
                                    <template x-if="selectedRecovery?.is_business_pdf">
                                        <div class="p-4 rounded-[12px] bg-[#5856D6]/10 border border-[#5856D6]/20 flex flex-col items-center justify-center text-center aspect-video space-y-1.5">
                                            <i data-lucide="file-text" class="w-7 h-7 text-[#5856D6]"></i>
                                            <span class="text-[11px] font-semibold text-black dark:text-white">Dokumen PDF Usaha</span>
                                            <a :href="selectedRecovery.business_url" target="_blank"
                                               class="text-[10px] font-bold text-[#5856D6] hover:underline inline-flex items-center gap-1">
                                                <span>Buka PDF di Tab Baru &rarr;</span>
                                            </a>
                                        </div>
                                    </template>
                                    <!-- Jika format Gambar -->
                                    <template x-if="!selectedRecovery?.is_business_pdf">
                                        <div>
                                            <div class="relative group rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 cursor-pointer aspect-video"
                                                 @click="openLightbox(selectedRecovery.business_url, 'Bukti Usaha: ' + selectedRecovery.business_name)">
                                                <img :src="selectedRecovery.business_url" alt="Bukti Usaha"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium gap-1.5 backdrop-blur-[2px]">
                                                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                                    <span>Perbesar</span>
                                                </div>
                                            </div>
                                            <a :href="selectedRecovery.business_url" target="_blank"
                                               class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1 mt-1">
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                                <span>Buka Resolusi Penuh</span>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!selectedRecovery?.business_url">
                                <div class="py-7 text-center text-xs text-black/40 dark:text-white/40">Tidak dilampirkan</div>
                            </template>
                        </div>

                        <!-- Dokumen 3: Selfie dengan KTP -->
                        <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="camera" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                    <span>3. Selfie dengan KTP</span>
                                </span>
                                <span class="text-[10px] font-medium text-black/40 dark:text-white/40 uppercase">Pendukung</span>
                            </div>
                            <template x-if="selectedRecovery?.selfie_url">
                                <div class="space-y-1.5">
                                    <div class="relative group rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 cursor-pointer aspect-video"
                                         @click="openLightbox(selectedRecovery.selfie_url, 'Selfie Pemohon: ' + selectedRecovery.applicant_name)">
                                        <img :src="selectedRecovery.selfie_url" alt="Selfie Pemohon"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium gap-1.5 backdrop-blur-[2px]">
                                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                                            <span>Perbesar</span>
                                        </div>
                                    </div>
                                    <a :href="selectedRecovery.selfie_url" target="_blank"
                                       class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        <span>Buka Resolusi Penuh</span>
                                    </a>
                                </div>
                            </template>
                            <template x-if="!selectedRecovery?.selfie_url">
                                <div class="py-7 text-center text-xs text-black/40 dark:text-white/40">Tidak dilampirkan</div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 3. TINDAKAN ADMINISTRATOR (FORM APPROVE / REJECT LANGSUNG) -->
                <div class="pt-2">
                    <!-- Kasus Status PENDING: Meja Eksekusi -->
                    <template x-if="selectedRecovery?.status === 'pending'">
                        <div class="rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 space-y-4">
                            <div class="flex items-center gap-2">
                                <i data-lucide="shield-alert" class="w-4 h-4 text-[#007AFF]"></i>
                                <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Tindakan Administrator Langsung</h4>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Panel Setujui Permohonan -->
                                <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 space-y-3">
                                    <div class="flex items-center gap-2 font-bold text-xs text-[#34C759] dark:text-[#30D158]">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        <span>Setujui &amp; Sinkronkan Kontak Akun</span>
                                    </div>
                                    <p class="text-[11px] text-black/70 dark:text-white/70 leading-relaxed">
                                        Email login akan diganti ke <strong class="text-black dark:text-white" x-text="selectedRecovery?.new_email"></strong> dan WhatsApp ke <strong class="text-black dark:text-white" x-text="selectedRecovery?.new_phone"></strong>.
                                    </p>
                                    <form :action="selectedRecovery?.approve_url" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">Catatan Verifikasi (Opsional):</label>
                                            <input type="text" name="admin_notes" value="Dokumen KTP dan bukti usaha terverifikasi valid."
                                                   class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:border-[#34C759]">
                                        </div>
                                        <button type="submit"
                                                class="w-full min-h-[44px] py-2.5 px-4 rounded-[12px] bg-[#34C759] hover:bg-[#28A745] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98]">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Setujui &amp; Perbarui Akses Akun</span>
                                        </button>
                                    </form>
                                </div>

                                <!-- Panel Tolak Permohonan -->
                                <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 space-y-3">
                                    <div class="flex items-center gap-2 font-bold text-xs text-[#FF3B30] dark:text-[#FF453A]">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                        <span>Tolak Permohonan Pemulihan</span>
                                    </div>
                                    <p class="text-[11px] text-black/70 dark:text-white/70 leading-relaxed">
                                        Alasan penolakan akan dikirimkan otomatis ke nomor WhatsApp pemohon.
                                    </p>
                                    <form :action="selectedRecovery?.reject_url" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">Alasan Penolakan <span class="text-[#FF3B30]">*</span>:</label>
                                            <textarea name="rejection_reason" rows="2" required placeholder="Contoh: Foto KTP buram..."
                                                      class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:border-[#FF3B30]"></textarea>
                                        </div>
                                        <button type="submit"
                                                class="w-full min-h-[44px] py-2.5 px-4 rounded-[12px] bg-[#FF3B30] hover:bg-red-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98]">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                            <span>Tolak Permohonan</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Kasus Status APPROVED -->
                    <template x-if="selectedRecovery?.status === 'approved'">
                        <div class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/25 text-xs space-y-2">
                            <div class="flex items-center gap-2 font-bold text-sm text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                <span>Permohonan Telah Disetujui</span>
                            </div>
                            <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                                Disetujui oleh: <strong class="text-black dark:text-white" x-text="selectedRecovery?.approver_name || 'Administrator'"></strong><br>
                                Waktu: <span class="tabular-nums" x-text="selectedRecovery?.approved_at_human"></span>
                            </p>
                            <template x-if="selectedRecovery?.admin_notes">
                                <div class="pt-2 border-t border-[#34C759]/20 text-xs text-black/75 dark:text-white/75 italic" x-text="'&ldquo;' + selectedRecovery.admin_notes + '&rdquo;'"></div>
                            </template>
                        </div>
                    </template>

                    <!-- Kasus Status REJECTED -->
                    <template x-if="selectedRecovery?.status === 'rejected'">
                        <div class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-xs space-y-2">
                            <div class="flex items-center gap-2 font-bold text-sm text-[#FF3B30] dark:text-[#FF453A]">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                <span>Permohonan Telah Ditolak</span>
                            </div>
                            <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                                Ditolak oleh: <strong class="text-black dark:text-white" x-text="selectedRecovery?.rejector_name || 'Administrator'"></strong><br>
                                Waktu: <span class="tabular-nums" x-text="selectedRecovery?.rejected_at_human"></span>
                            </p>
                            <div class="pt-2 border-t border-[#FF3B30]/20 text-xs text-black/75 dark:text-white/75">
                                <strong class="block text-black dark:text-white mb-0.5">Alasan Penolakan:</strong>
                                <span x-text="'&ldquo;' + selectedRecovery?.rejection_reason + '&rdquo;'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer (Sticky Bottom) -->
            <div class="sticky bottom-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-6 py-3.5 flex items-center justify-between gap-3 shrink-0">
                <a :href="selectedRecovery?.show_url"
                   class="sm:hidden text-xs font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                    <span>Buka Halaman Penuh &rarr;</span>
                </a>
                <div class="ml-auto">
                    <button type="button" @click="closeInspection()"
                            class="min-h-[44px] px-5 py-2 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-xs font-semibold text-black dark:text-white transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ IMAGE LIGHTBOX MODAL ═══ -->
    <div x-show="imageModalOpen"
         @click="imageModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-lg"
         x-cloak style="display: none;">
        <div class="max-w-4xl max-h-[90vh] flex flex-col items-center gap-3 w-full" @click.stop>
            <div class="flex items-center justify-between w-full text-white text-xs font-semibold px-2">
                <span x-text="lightboxTitle"></span>
                <button type="button" @click="imageModalOpen = false"
                        class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <img :src="lightboxImageUrl"
                 alt="Dokumen Bukti"
                 class="max-w-full max-h-[80vh] rounded-[16px] object-contain shadow-2xl border border-white/20">
        </div>
    </div>
</div>
@endsection
