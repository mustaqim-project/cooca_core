@extends('layouts.admin', [
    'title' => 'Verifikasi Pembayaran #' . $payment->order_number . ' — Admin Console',
    'headerTitle' => 'Verifikasi Pembayaran Langganan',
    'headerSubtitle' => 'Periksa kesesuaian mutasi bank dan setujui aktivasi lisensi Cooca Core'
])

@section('content')
@php
    $badge = $payment->getStatusBadge();
@endphp
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    imagePreviewOpen: false
}">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.subscriptions.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Langganan</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5 {{ $badge['class'] }}">
                <i data-lucide="{{ $badge['icon'] }}" class="w-3.5 h-3.5"></i>
                <span>{{ $badge['label'] }}</span>
            </span>
        </div>
    </div>

    <!-- Status Alerts if already processed -->
    @if($payment->isApproved())
    <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 flex items-center gap-3">
        <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 shrink-0">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
        <div class="text-xs space-y-0.5">
            <div class="font-bold text-white">Pembayaran Telah Disetujui</div>
            <div class="text-emerald-300">
                Disetujui oleh <strong class="text-white">{{ $payment->approver->name ?? 'Admin' }}</strong> pada {{ $payment->approved_at?->format('d M Y H:i:s') }}. Paket Cooca Core bisnis telah aktif.
            </div>
            @if($payment->admin_notes)
                <div class="text-slate-400 text-[11px]">Catatan: {{ $payment->admin_notes }}</div>
            @endif
        </div>
    </div>
    @elseif($payment->isRejected())
    <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-500/40 flex items-center gap-3">
        <div class="p-2 rounded-xl bg-rose-500/20 text-rose-400 shrink-0">
            <i data-lucide="x-circle" class="w-5 h-5"></i>
        </div>
        <div class="text-xs space-y-0.5">
            <div class="font-bold text-white">Pembayaran Telah Ditolak</div>
            <div class="text-rose-300">
                Ditolak pada {{ $payment->rejected_at?->format('d M Y H:i:s') }}. Alasan: <strong class="text-white">{{ $payment->admin_notes }}</strong>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Comparison Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. System Order Details -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
            <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-400"></i>
                    <span>Rincian Tagihan Sistem</span>
                </h3>
                <span class="font-mono text-xs text-slate-400 font-bold">#{{ $payment->order_number }}</span>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Workspace Bisnis:</span>
                    <span class="font-bold text-white">{{ $payment->business->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Pemesan (User):</span>
                    <span class="font-semibold text-slate-200">{{ $payment->user->name ?? '-' }} ({{ $payment->user->email ?? '-' }})</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Paket Dipesan:</span>
                    <span class="font-bold text-indigo-300 uppercase">{{ $payment->cycle === 'annual' ? 'Cooca Core Tahunan' : 'Cooca Core Bulanan' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Nominal Pokok:</span>
                    <span class="font-mono text-slate-300">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Kode Verifikasi Unik:</span>
                    <span class="font-mono font-bold text-amber-400">{{ $payment->unique_code }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-800/80 bg-slate-900/60 px-3 rounded-xl">
                    <span class="font-bold text-white">Total Wajib Transfer:</span>
                    <span class="font-mono font-black text-base text-emerald-400">Rp {{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Rekening Tujuan Cooca:</span>
                    <span class="font-bold text-slate-200">{{ $methodDetails['bank_name'] }} • {{ $methodDetails['account_number'] }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Waktu Order:</span>
                    <span class="font-mono text-slate-400">{{ $payment->created_at->format('d M Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Transfer Proof & Sender Details -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
            <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <i data-lucide="image" class="w-5 h-5 text-cyan-400"></i>
                    <span>Konfirmasi Pengirim & Bukti</span>
                </h3>
                <span class="text-xs text-slate-400">
                    {{ $payment->proof_uploaded_at ? $payment->proof_uploaded_at->format('d M Y H:i') : 'Belum diunggah' }}
                </span>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Nama Rekening Pengirim:</span>
                    <span class="font-bold text-white">{{ $payment->sender_account_name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Bank / e-Wallet Pengirim:</span>
                    <span class="font-semibold text-slate-200">{{ $payment->sender_bank ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">No. Rekening / HP Pengirim:</span>
                    <span class="font-mono text-slate-300">{{ $payment->sender_account_number ?? '-' }}</span>
                </div>
                @if($payment->notes)
                <div class="py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400 block mb-1">Catatan Pengirim:</span>
                    <p class="text-slate-300 italic bg-slate-900/60 p-2 rounded-lg">{{ $payment->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Payment Proof Image Card -->
            @if($payment->payment_proof_path)
            <div class="space-y-2 pt-2">
                <span class="text-xs font-bold text-slate-400 block">Foto Struk / Tangkapan Layar:</span>
                <div class="p-2 rounded-2xl bg-slate-950 border border-slate-800 text-center overflow-hidden">
                    <a href="{{ $payment->getProofUrl() }}" target="_blank" class="block group">
                        <img src="{{ $payment->getProofUrl() }}" alt="Bukti Transfer"
                             class="max-h-56 mx-auto object-contain rounded-xl group-hover:scale-105 transition-transform">
                    </a>
                </div>
                <div class="flex justify-center pt-1">
                    <a href="{{ $payment->getProofUrl() }}" target="_blank" class="text-xs text-cyan-400 hover:underline inline-flex items-center gap-1 font-bold">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Gambar Ukuran Penuh</span>
                    </a>
                </div>
            </div>
            @else
            <div class="p-6 rounded-2xl bg-slate-950/60 border border-dashed border-slate-800 text-center text-xs text-slate-500">
                Pengguna belum mengunggah file bukti transfer.
            </div>
            @endif
        </div>

    </div>

    <!-- Action Buttons for Verification -->
    @if($payment->isAwaitingApproval() || $payment->isPending())
    <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-xs text-slate-400">
            Pastikan dana sebesar <strong class="text-emerald-400 font-mono">Rp {{ number_format($payment->total_payable, 0, ',', '.') }}</strong> telah masuk ke mutasi rekening bank resmi sebelum menyetujui.
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <!-- Reject Button -->
            <button type="button" @click="rejectModalOpen = true"
                    class="px-4 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-black transition flex items-center gap-1.5">
                <i data-lucide="x" class="w-4 h-4"></i>
                <span>Tolak Pembayaran</span>
            </button>

            <!-- Approve Button -->
            <button type="button" @click="approveModalOpen = true"
                    class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Setujui & Aktifkan Paket Core</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Modal Approve -->
    <div x-show="approveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80" style="display: none;">
        <div @click.away="approveModalOpen = false" class="glass-card rounded-3xl p-6 border border-emerald-500/40 max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h4 class="font-black text-white text-base flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                    <span>Konfirmasi Persetujuan</span>
                </h4>
                <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Apakah Anda yakin ingin menyetujui pembayaran <strong class="text-white font-mono">#{{ $payment->order_number }}</strong>? Paket Cooca Core untuk bisnis <strong class="text-emerald-400">{{ $payment->business->name }}</strong> akan langsung diaktifkan dengan kuota 10.000.000 Token AI.
            </p>

            <form method="POST" action="{{ route('admin.subscriptions.approve', $payment) }}" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400">Catatan Admin (Opsional)</label>
                    <input type="text" name="admin_notes" placeholder="Contoh: Mutasi BCA terkonfirmasi masuk"
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow transition">
                        Ya, Setujui & Aktifkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reject -->
    <div x-show="rejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80" style="display: none;">
        <div @click.away="rejectModalOpen = false" class="glass-card rounded-3xl p-6 border border-rose-500/40 max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h4 class="font-black text-white text-base flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
                    <span>Tolak Pembayaran</span>
                </h4>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.subscriptions.reject', $payment) }}" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-300">Alasan Penolakan <span class="text-rose-400">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="Contoh: Nominal transfer kurang Rp 200, atau struk buram/tidak terbaca"
                              class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-rose-500"></textarea>
                    <p class="text-[10px] text-slate-400">Alasan ini akan ditampilkan ke pemilik bisnis agar dapat melakukan upload ulang.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black shadow transition">
                        Tolak Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
