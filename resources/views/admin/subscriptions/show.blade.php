@extends('layouts.admin', [
    'title' => 'Verifikasi Pembayaran #' . $payment->order_number . ' — Admin Console',
    'headerTitle' => 'Verifikasi Pembayaran Langganan',
    'headerSubtitle' => 'Periksa kesesuaian mutasi bank dan setujui aktivasi lisensi Cooca UMKM'
])

@section('content')
@php
    $badge = $payment->getStatusBadge();
    $methodDetails = $payment->getPaymentMethodDetails();
@endphp
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    imagePreviewOpen: false
}">

    <!-- Top Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <a href="{{ route('admin.subscriptions.index') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i><span>Kembali ke Daftar Langganan</span>
        </a>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $badge['class'] }}"><i data-lucide="{{ $badge['icon'] }}" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>{{ $badge['label'] }}</span></span>
    </div>

    <!-- Status Alerts if already processed -->
    @if($payment->isApproved())
    <div class="rounded-[14px] px-4 py-3.5 bg-[#34C759]/10 border border-[#34C759]/25 flex items-center gap-3">
        <div class="p-2 rounded-[10px] bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] shrink-0"><i data-lucide="check-circle-2" class="w-5 h-5" stroke-width="1.5"></i></div>
        <div class="text-[13px] space-y-0.5">
            <div class="font-semibold text-black dark:text-white">Pembayaran Telah Disetujui</div>
            <div class="text-[#34C759] dark:text-[#30D158]">Disetujui oleh <strong class="text-black dark:text-white">{{ $payment->approver->name ?? 'Admin' }}</strong> pada {{ $payment->approved_at?->format('d M Y H:i:s') }}. Paket Cooca UMKM bisnis telah aktif.</div>
            @if($payment->admin_notes)
            <div class="text-[12px] text-black/50 dark:text-white/50">Catatan: {{ $payment->admin_notes }}</div>
            @endif
        </div>
    </div>
    @elseif($payment->isRejected())
    <div class="rounded-[14px] px-4 py-3.5 bg-[#FF3B30]/10 border border-[#FF3B30]/25 flex items-center gap-3">
        <div class="p-2 rounded-[10px] bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] shrink-0"><i data-lucide="x-circle" class="w-5 h-5" stroke-width="1.5"></i></div>
        <div class="text-[13px] space-y-0.5">
            <div class="font-semibold text-black dark:text-white">Pembayaran Telah Ditolak</div>
            <div class="text-[#FF3B30] dark:text-[#FF453A]">Ditolak pada {{ $payment->rejected_at?->format('d M Y H:i:s') }}. Alasan: <strong class="text-black dark:text-white">{{ $payment->admin_notes }}</strong></div>
        </div>
    </div>
    @endif

    <!-- Main Comparison Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. System Order Details -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 space-y-5">
            <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <span>Rincian Tagihan Sistem</span>
                </h3>
                <span class="font-mono text-[12px] text-black/50 dark:text-white/50 font-semibold tabular-nums">#{{ $payment->order_number }}</span>
            </div>

            <div class="space-y-3 text-[13px]">
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Workspace Bisnis:</span><span class="font-semibold text-black dark:text-white">{{ $payment->business->name ?? '-' }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Pemesan (User):</span><span class="font-medium text-black/80 dark:text-white/80">{{ $payment->user->name ?? '-' }} ({{ $payment->user->email ?? '-' }})</span></div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10">
                    <span class="text-black/50 dark:text-white/50">Paket Dipesan:</span>
                    <span class="font-semibold text-[#5856D6] dark:text-[#5E5CE6] uppercase">
                        @if($payment->payment_type === 'ai_token')
                            {{ $payment->package_name ?: 'Topup Token AI' }} (+{{ number_format($payment->topup_quantity ?? 0, 0, ',', '.') }} Token AI)
                        @elseif($payment->payment_type === 'storage')
                            {{ $payment->package_name ?: 'Topup Storage Disk' }} (+{{ $payment->topup_storage_bytes ? round($payment->topup_storage_bytes / 1073741824, 1) . ' GB' : '-' }})
                        @else
                            {{ $payment->package_name ?: ($payment->cycle === 'annual' || $payment->plan_code === 'core_annual' ? 'Cooca UMKM Tahunan' : 'Cooca UMKM Bulanan') }} ({{ $payment->package_duration_days ? $payment->package_duration_days . ' Hari' : ($payment->cycle === 'annual' ? '365 Hari' : '30 Hari') }})
                        @endif
                    </span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Nominal Pokok:</span><span class="tabular-nums text-black/80 dark:text-white/80">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Kode Verifikasi Unik:</span><span class="font-mono font-semibold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">{{ $payment->unique_code }}</span></div>
                <div class="flex justify-between py-2.5 px-3 rounded-[10px] bg-[#34C759]/8 border-b border-black/5 dark:border-white/10">
                    <span class="font-semibold text-black dark:text-white">Total Wajib Transfer:</span>
                    <span class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums">Rp {{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Rekening Tujuan Cooca:</span><span class="font-semibold text-black/80 dark:text-white/80">{{ $methodDetails['bank_name'] }} · {{ $methodDetails['account_number'] }}</span></div>
                <div class="flex justify-between py-1.5"><span class="text-black/50 dark:text-white/50">Waktu Order:</span><span class="tabular-nums text-black/50 dark:text-white/50">{{ $payment->created_at->format('d M Y H:i:s') }}</span></div>
            </div>
        </div>

        <!-- 2. Transfer Proof & Sender Details -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 space-y-5">
            <div class="border-b border-black/5 dark:border-white/10 pb-3 flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="image" class="w-5 h-5 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
                    <span>Konfirmasi Pengirim &amp; Bukti</span>
                </h3>
                <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">{{ $payment->proof_uploaded_at ? $payment->proof_uploaded_at->format('d M Y H:i') : 'Belum diunggah' }}</span>
            </div>

            <div class="space-y-3 text-[13px]">
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Nama Rekening Pengirim:</span><span class="font-semibold text-black dark:text-white">{{ $payment->sender_account_name ?? '-' }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">Bank / e-Wallet Pengirim:</span><span class="font-medium text-black/80 dark:text-white/80">{{ $payment->sender_bank ?? '-' }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50">No. Rekening / HP Pengirim:</span><span class="tabular-nums text-black/80 dark:text-white/80">{{ $payment->sender_account_number ?? '-' }}</span></div>
                @if($payment->notes)
                <div class="py-1.5 border-b border-black/5 dark:border-white/10"><span class="text-black/50 dark:text-white/50 block mb-1">Catatan Pengirim:</span><p class="text-black/70 dark:text-white/70 italic bg-black/[0.03] dark:bg-white/[0.05] p-2 rounded-[8px]">{{ $payment->notes }}</p></div>
                @endif
            </div>

            @if($payment->payment_proof_path)
            <div class="space-y-2 pt-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Foto Struk / Tangkapan Layar:</span>
                <div class="p-2 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] text-center overflow-hidden">
                    <a href="{{ $payment->getProofUrl() }}" target="_blank" class="block group"><img src="{{ $payment->getProofUrl() }}" alt="Bukti Transfer" class="max-h-56 mx-auto object-contain rounded-[10px] group-hover:scale-105 transition-transform"></a>
                </div>
                <div class="flex justify-center pt-1">
                    <a href="{{ $payment->getProofUrl() }}" target="_blank" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1"><i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Buka Gambar Ukuran Penuh</span></a>
                </div>
            </div>
            @else
            <div class="p-6 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-dashed border-black/10 dark:border-white/15 text-center text-[13px] text-black/45 dark:text-white/45">Pengguna belum mengunggah file bukti transfer.</div>
            @endif
        </div>
    </div>

    <!-- Action Buttons for Verification -->
    @if($payment->isAwaitingApproval() || $payment->isPending())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-[13px] text-black/50 dark:text-white/50">Pastikan dana sebesar <strong class="text-[#34C759] dark:text-[#30D158] tabular-nums">Rp {{ number_format($payment->total_payable, 0, ',', '.') }}</strong> telah masuk ke mutasi rekening bank resmi sebelum menyetujui.</div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="rejectModalOpen = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#FF3B30] dark:text-[#FF453A] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i><span>Tolak Pembayaran</span></button>
            <button type="button" @click="approveModalOpen = true" class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="check" class="w-4 h-4" stroke-width="1.5"></i><span>Setujui &amp; Aktifkan Paket Core</span></button>
        </div>
    </div>
    @endif

    <!-- Modal Approve (Sheet) -->
    <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div @click.away="approveModalOpen = false" class="sheet-material rounded-[20px] p-6 border border-[#34C759]/30 max-w-md w-full space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h4 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                    <span>Setujui &amp; Aktifkan Langganan</span>
                </h4>
                <button type="button" @click="approveModalOpen = false" class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i></button>
            </div>
            <div class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">Setujui pembayaran <strong class="text-black dark:text-white tabular-nums">#{{ $payment->order_number }}</strong>? Paket Cooca UMKM untuk bisnis <strong class="text-[#34C759] dark:text-[#30D158]">{{ $payment->business->name }}</strong> akan langsung diaktifkan dengan kuota 10.000.000 Token AI.</div>
            <form method="POST" action="{{ route('admin.subscriptions.approve', $payment) }}" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Catatan Admin (Opsional)</label>
                    <input type="text" name="admin_notes" placeholder="Contoh: Mutasi BCA terkonfirmasi masuk" class="w-full h-10 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#34C759]/50">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="approveModalOpen = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Ya, Setujui &amp; Aktifkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reject (Sheet) -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div @click.away="rejectModalOpen = false" class="sheet-material rounded-[20px] p-6 border border-[#FF3B30]/30 max-w-md w-full space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h4 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-[#FF3B30] dark:text-[#FF453A]" stroke-width="1.5"></i>
                    <span>Tolak Pembayaran</span>
                </h4>
                <button type="button" @click="rejectModalOpen = false" class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.subscriptions.reject', $payment) }}" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-black/70 dark:text-white/70 block">Alasan Penolakan <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="Contoh: Nominal transfer kurang Rp 200, atau struk buram/tidak terbaca" class="w-full px-3 py-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50"></textarea>
                    <p class="text-[11px] text-black/45 dark:text-white/45">Alasan ini akan ditampilkan ke pemilik bisnis agar dapat melakukan upload ulang.</p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all">Tolak Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection