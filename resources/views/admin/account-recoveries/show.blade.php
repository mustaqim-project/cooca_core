@extends('layouts.admin', [
    'title' => "Verifikasi Tiket {$recovery->ticket_number} - Admin Console",
    'headerTitle' => "Verifikasi Pemulihan Akun #{$recovery->ticket_number}",
    'headerSubtitle' => "Tinjau kecocokan berkas identitas KTP dan dokumen usaha sebelum memperbarui akses login akun"
])

@section('content')
<div class="space-y-6" x-data="{
    imageModalOpen: false,
    modalImageUrl: '',
    modalTitle: '',
    showRejectModal: false,
    showApproveModal: false,
    copied: false,
    openImage(url, title) {
        this.modalImageUrl = url;
        this.modalTitle = title;
        this.imageModalOpen = true;
    }
}">

    <!-- ═══ BENTO TOP BAR: BREADCRUMB & STATUS ═══ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.account-recoveries.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/70 dark:text-white/70 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
            <div class="h-4 w-px bg-black/10 dark:bg-white/10 hidden sm:block"></div>
            <div>
                <span class="text-[10px] uppercase font-bold text-black/40 dark:text-white/40 tracking-wider block">Nomor Tiket</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-extrabold text-sm sm:text-base text-[#007AFF] dark:text-[#0A84FF]">{{ $recovery->ticket_number }}</span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $recovery->ticket_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                            title="Salin nomor tiket">
                        <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!copied"></i>
                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copied" style="display: none;"></i>
                    </button>
                    <span x-show="copied" class="text-[10px] font-semibold text-[#34C759]" style="display: none;">Tersalin!</span>
                </div>
            </div>
        </div>

        <!-- Status Indicator Pill -->
        <div>
            @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] border border-[#FF9500]/25">
                    <span class="w-2 h-2 rounded-full bg-[#FF9500] animate-pulse"></span>
                    <span>Menunggu Review Administrator</span>
                </span>
            @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/25">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                    <span>Disetujui Resmi &amp; Akses Diperbarui</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/25">
                    <i data-lucide="x-circle" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A]"></i>
                    <span>Permohonan Ditolak</span>
                </span>
            @endif
        </div>
    </div>

    <!-- ═══ MAIN BENTO GRID (2 Cols Left, 1 Col Right) ═══ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: COMPARISON, DETAILS & DOCUMENTS -->
        <div class="lg:col-span-2 space-y-6">

            <!-- 1. BENTO KOMPARASI KONTAK LAMA VS BARU -->
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Komparasi Data Akun Terdaftar vs Kontak Baru</h3>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                        {{ $recovery->getIssueTypeLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Data Lama (Neutral Card) -->
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center gap-1.5 text-black/50 dark:text-white/50 font-bold uppercase tracking-wider text-[10px]">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <span>Data Terdaftar Saat Ini (Lama)</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/45 dark:text-white/45 block">Email Akun:</span>
                            <span class="font-mono font-medium text-xs sm:text-sm text-black/80 dark:text-white/80 select-all break-all">{{ $recovery->old_email }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/45 dark:text-white/45 block">WhatsApp Terdaftar:</span>
                            <span class="font-mono font-medium text-xs sm:text-sm text-black/80 dark:text-white/80 select-all">{{ $recovery->old_phone ?? 'Tidak tercatat / Kosong' }}</span>
                        </div>
                    </div>

                    <!-- Data Baru (Vibrant Emerald Card) -->
                    <div class="p-4 rounded-[16px] bg-[#34C759]/10 dark:bg-[#30D158]/10 border border-[#34C759]/25 space-y-3">
                        <div class="flex items-center gap-1.5 text-[#34C759] dark:text-[#30D158] font-bold uppercase tracking-wider text-[10px]">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span>Kontak Baru Yang Diajukan</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/45 dark:text-white/45 block">Alamat Email Baru:</span>
                            <span class="font-mono font-bold text-xs sm:text-sm text-[#34C759] dark:text-[#30D158] select-all break-all">{{ $recovery->new_email }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-black/45 dark:text-white/45 block">Nomor WhatsApp Baru:</span>
                            <span class="font-mono font-bold text-xs sm:text-sm text-black dark:text-white select-all">{{ $recovery->new_phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pemohon & Bisnis Bar -->
                <div class="mt-4 pt-4 border-t border-black/[0.06] dark:border-white/[0.08] grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Nama Pemohon:</span>
                        <span class="font-bold text-black dark:text-white text-sm">{{ $recovery->applicant_name }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Nama Usaha / Outlet:</span>
                        <span class="font-bold text-black dark:text-white text-sm">{{ $recovery->business_name }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Waktu Permohonan:</span>
                        <span class="font-medium text-black/70 dark:text-white/70">{{ $recovery->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                </div>

                @if($recovery->reason_description)
                    <div class="mt-4 p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <span class="text-[10px] font-bold text-black/45 dark:text-white/45 uppercase tracking-wider block mb-1">Pernyataan Pemohon Mengenai Kendala Akun:</span>
                        <p class="text-xs text-black/75 dark:text-white/75 leading-relaxed italic">
                            "{{ $recovery->reason_description }}"
                        </p>
                    </div>
                @endif
            </div>

            <!-- 2. BENTO MEJA UJI BUKTI OTENTIK (DOCUMENT INSPECTION DESK) -->
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                            <i data-lucide="file-check" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Meja Uji Bukti Otentik Kepemilikan Akun</h3>
                    </div>
                    <span class="text-[11px] text-black/40 dark:text-white/40">Klik gambar untuk perbesar</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Dokumen 1: KTP Asli -->
                    <div class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="credit-card" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>1. Foto KTP Asli</span>
                            </span>
                            <span class="text-[10px] font-bold text-[#007AFF] uppercase">Wajib</span>
                        </div>

                        @if($recovery->identity_card_path)
                            <div class="relative group rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 cursor-pointer aspect-video"
                                 @click="openImage('{{ $recovery->getIdentityCardUrl() }}', 'Foto KTP Pemohon: {{ $recovery->applicant_name }}')">
                                <img src="{{ $recovery->getIdentityCardUrl() }}"
                                     alt="KTP Pemohon"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium gap-1.5 backdrop-blur-[2px]">
                                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                    <span>Perbesar</span>
                                </div>
                            </div>
                            <div class="pt-1">
                                <a href="{{ $recovery->getIdentityCardUrl() }}" target="_blank"
                                   class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                    <span>Buka Resolusi Penuh</span>
                                </a>
                            </div>
                        @else
                            <div class="w-full py-8 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] text-center text-xs text-black/40 dark:text-white/40">
                                Berkas tidak dilampirkan
                            </div>
                        @endif
                    </div>

                    <!-- Dokumen 2: Bukti Legalitas Usaha -->
                    <div class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-4 h-4 text-[#5856D6]"></i>
                                <span>2. Dokumen Usaha</span>
                            </span>
                            <span class="text-[10px] font-bold text-[#5856D6] uppercase">Wajib</span>
                        </div>

                        @if($recovery->business_proof_path)
                            <div class="relative group rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 cursor-pointer aspect-video"
                                 @click="openImage('{{ $recovery->getBusinessProofUrl() }}', 'Bukti Usaha: {{ $recovery->business_name }}')">
                                <img src="{{ $recovery->getBusinessProofUrl() }}"
                                     alt="Bukti Usaha"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium gap-1.5 backdrop-blur-[2px]">
                                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                    <span>Perbesar</span>
                                </div>
                            </div>
                            <div class="pt-1">
                                <a href="{{ $recovery->getBusinessProofUrl() }}" target="_blank"
                                   class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                    <span>Buka Resolusi Penuh</span>
                                </a>
                            </div>
                        @else
                            <div class="w-full py-8 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] text-center text-xs text-black/40 dark:text-white/40">
                                Berkas tidak dilampirkan
                            </div>
                        @endif
                    </div>

                    <!-- Dokumen 3: Foto Selfie dengan KTP -->
                    <div class="p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="camera" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>3. Selfie dengan KTP</span>
                            </span>
                            <span class="text-[10px] font-semibold text-black/40 dark:text-white/40 uppercase">Pendukung</span>
                        </div>

                        @if($recovery->selfie_proof_path)
                            <div class="relative group rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 cursor-pointer aspect-video"
                                 @click="openImage('{{ $recovery->getSelfieProofUrl() }}', 'Selfie Pemohon: {{ $recovery->applicant_name }}')">
                                <img src="{{ $recovery->getSelfieProofUrl() }}"
                                     alt="Selfie Pemohon"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium gap-1.5 backdrop-blur-[2px]">
                                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                                    <span>Perbesar</span>
                                </div>
                            </div>
                            <div class="pt-1">
                                <a href="{{ $recovery->getSelfieProofUrl() }}" target="_blank"
                                   class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                    <span>Buka Resolusi Penuh</span>
                                </a>
                            </div>
                        @else
                            <div class="w-full py-8 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] text-center text-xs text-black/40 dark:text-white/40">
                                Tidak dilampirkan oleh pemohon
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COL: DECISION ACTION BOX & AUDIT TRAIL -->
        <div class="space-y-6">

            <!-- 1. BENTO DECISION ACTION BOX -->
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 sm:p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="w-7 h-7 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Tindakan Administrator</h3>
                </div>

                @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                    <!-- Verification Checklist -->
                    <div class="mb-5 p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-xs text-black dark:text-white space-y-2">
                        <div class="font-bold flex items-center gap-1.5 text-[#FF9500] dark:text-[#FF9F0A]">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            <span>Checklist Sebelum Setuju:</span>
                        </div>
                        <ul class="space-y-1 text-[11px] text-black/70 dark:text-white/70 pl-1">
                            <li>✓ Nama di KTP cocok dengan nama pemohon.</li>
                            <li>✓ Nama usaha tercantum pada bukti legalitas/nota resmi.</li>
                            <li>✓ Nomor WA baru aktif &amp; siap menerima notifikasi.</li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        <!-- Approve Action Button (Min 50px Touch Target) -->
                        <button type="button" @click="showApproveModal = true"
                                class="w-full min-h-[50px] py-3.5 px-4 rounded-[14px] bg-[#34C759] hover:bg-[#28A745] text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#34C759]/25 transition-all active:scale-[0.98]">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Setujui &amp; Perbarui Akses Akun</span>
                        </button>

                        <!-- Reject Action Button (Min 48px Touch Target) -->
                        <button type="button" @click="showRejectModal = true"
                                class="w-full min-h-[48px] py-3 px-4 rounded-[14px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-colors active:scale-[0.98]">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            <span>Tolak Permohonan</span>
                        </button>
                    </div>

                @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                    <!-- Approved Info Card -->
                    <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-xs space-y-2.5">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#34C759] dark:text-[#30D158]">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span>Permohonan Telah Disetujui</span>
                        </div>
                        <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                            Disetujui oleh: <strong class="text-black dark:text-white">{{ $recovery->approver?->name ?? 'Administrator Platform' }}</strong><br>
                            Waktu: {{ $recovery->approved_at?->format('d M Y, H:i') }} WIB
                        </p>
                        @if($recovery->admin_notes)
                            <div class="pt-2 border-t border-[#34C759]/20 text-xs text-black/75 dark:text-white/75 italic">
                                "{{ $recovery->admin_notes }}"
                            </div>
                        @endif
                    </div>

                @else
                    <!-- Rejected Info Card -->
                    <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-xs space-y-2.5">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#FF3B30] dark:text-[#FF453A]">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            <span>Permohonan Telah Ditolak</span>
                        </div>
                        <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                            Ditolak oleh: <strong class="text-black dark:text-white">{{ $recovery->rejector?->name ?? 'Administrator Platform' }}</strong><br>
                            Waktu: {{ $recovery->rejected_at?->format('d M Y, H:i') }} WIB
                        </p>
                        <div class="pt-2 border-t border-[#FF3B30]/20 text-xs text-black/75 dark:text-white/75">
                            <strong class="block text-black dark:text-white mb-0.5">Alasan Penolakan Resmi:</strong>
                            "{{ $recovery->rejection_reason }}"
                        </div>
                    </div>
                @endif
            </div>

            <!-- 2. BENTO AUDIT TRAIL & METADATA -->
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm text-xs space-y-2.5 text-black/60 dark:text-white/60">
                <span class="text-[10px] font-bold text-black/40 dark:text-white/40 uppercase tracking-wider block mb-1">Informasi Jejak Audit Keamanan</span>
                <div class="flex justify-between py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                    <span>Alamat IP Pemohon:</span>
                    <span class="font-mono font-semibold text-black dark:text-white">{{ $recovery->ip_address ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                    <span>Waktu Submit:</span>
                    <span class="text-black dark:text-white font-medium">{{ $recovery->created_at->format('d/m/Y H:i:s') }} WIB</span>
                </div>
                @if($recovery->user)
                    <div class="pt-1">
                        <span class="text-[10px] text-black/40 dark:text-white/40 block">Tautan Akun Database:</span>
                        <span class="font-semibold text-black dark:text-white">User #{{ substr($recovery->user->id, 0, 8) }} ({{ $recovery->user->name }})</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ═══ MODAL APPROVE (Apple Inset Dialog) ═══ -->
    <div x-show="showApproveModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         x-cloak style="display: none;">
        <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 border border-black/10 dark:border-white/10 shadow-2xl space-y-4"
             @click.stop>
            <div class="flex items-center gap-2.5 text-[#34C759]">
                <div class="w-8 h-8 rounded-full bg-[#34C759]/15 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">Konfirmasi Persetujuan Pemulihan</h3>
            </div>
            <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                Tindakan ini akan <strong>memperbarui kontak email dan nomor WhatsApp akun</strong> menjadi kontak baru:
                <br><br>
                <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158] block select-all">{{ $recovery->new_email }}</span>
                <span class="font-mono font-bold text-black dark:text-white block select-all">{{ $recovery->new_phone }}</span>
                <br>
                Email pengguna akan otomatis ditandai terverifikasi, dan notifikasi konfirmasi akan dikirimkan otomatis via WhatsApp.
            </p>

            <form method="POST" action="{{ route('admin.account-recoveries.approve', $recovery) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="admin_notes" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Catatan Verifikasi Administrator (Opsional):
                    </label>
                    <input type="text" name="admin_notes" id="admin_notes"
                           value="Dokumen KTP dan legalitas usaha terverifikasi valid."
                           class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:border-[#34C759] focus:ring-1 focus:ring-[#34C759]">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="showApproveModal = false"
                            class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#34C759] hover:bg-[#28A745] text-white text-xs font-bold shadow-md shadow-[#34C759]/25 transition-all">
                        Ya, Setujui &amp; Update Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ MODAL REJECT (Apple Inset Dialog) ═══ -->
    <div x-show="showRejectModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         x-cloak style="display: none;">
        <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 border border-black/10 dark:border-white/10 shadow-2xl space-y-4"
             @click.stop>
            <div class="flex items-center gap-2.5 text-[#FF3B30]">
                <div class="w-8 h-8 rounded-full bg-[#FF3B30]/15 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">Tolak Permohonan Pemulihan</h3>
            </div>
            <p class="text-xs text-black/70 dark:text-white/70 leading-relaxed">
                Harap berikan alasan penolakan yang jelas dan terperinci. Alasan ini akan ditampilkan kepada pemohon pada halaman pelacakan tiket agar dapat diperbaiki.
            </p>

            <form method="POST" action="{{ route('admin.account-recoveries.reject', $recovery) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="rejection_reason" class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                        Alasan Penolakan <span class="text-[#FF3B30]">*</span>:
                    </label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                              placeholder="Contoh: Foto KTP buram dan nama usaha tidak sesuai dengan bukti kepemilikan yang dilampirkan..."
                              class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:border-[#FF3B30] focus:ring-1 focus:ring-[#FF3B30]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="showRejectModal = false"
                            class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#FF3B30] hover:bg-red-600 text-white text-xs font-bold shadow-md shadow-[#FF3B30]/25 transition-all">
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ IMAGE LIGHTBOX MODAL ═══ -->
    <div x-show="imageModalOpen"
         @click="imageModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-lg"
         x-cloak style="display: none;">
        <div class="max-w-4xl max-h-[90vh] flex flex-col items-center gap-3 w-full" @click.stop>
            <div class="flex items-center justify-between w-full text-white text-xs font-semibold px-2">
                <span x-text="modalTitle"></span>
                <button type="button" @click="imageModalOpen = false"
                        class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <img :src="modalImageUrl"
                 alt="Dokumen"
                 class="max-w-full max-h-[80vh] rounded-[16px] object-contain shadow-2xl border border-white/20">
        </div>
    </div>
</div>
@endsection
