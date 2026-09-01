@extends('layouts.app', [
    'title' => 'Pembayaran ' . $payment->order_number . ' — Cooca Core',
    'headerTitle' => 'Instruksi Pembayaran & Konfirmasi',
    'headerSubtitle' => 'Selesaikan transfer dan upload bukti transfer untuk aktivasi paket Cooca Core'
])

@section('content')
@php
    $badge = $payment->getStatusBadge();
@endphp
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    copiedText: null,
    copyToClipboard(text, label) {
        navigator.clipboard.writeText(text);
        this.copiedText = label;
        setTimeout(() => this.copiedText = null, 2500);
    }
}">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('billing.history') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Riwayat Tagihan</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5 {{ $badge['class'] }}">
                <i data-lucide="{{ $badge['icon'] }}" class="w-3.5 h-3.5"></i>
                <span>{{ $badge['label'] }}</span>
            </span>
        </div>
    </div>

    <!-- Rejection Alert (If Rejected) -->
    @if($payment->isRejected())
    <div class="p-5 rounded-2xl bg-rose-950/30 border border-rose-500/50 flex items-start gap-4">
        <div class="p-2.5 rounded-xl bg-rose-500/20 text-rose-400 shrink-0">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        <div class="space-y-1 text-xs">
            <h4 class="font-bold text-white text-sm">Pembayaran Ditolak oleh Admin</h4>
            <p class="text-rose-200">
                <strong>Alasan:</strong> {{ $payment->admin_notes ?? 'Bukti transfer tidak valid atau dana belum masuk rekening.' }}
            </p>
            <p class="text-slate-400">Silakan lakukan transfer ulang atau unggah bukti transfer baru yang benar di bawah ini.</p>
        </div>
    </div>
    @endif

    <!-- Awaiting Approval Banner -->
    @if($payment->isAwaitingApproval())
    <div class="p-5 rounded-2xl bg-cyan-950/30 border border-cyan-500/40 flex items-start gap-4">
        <div class="p-2.5 rounded-xl bg-cyan-500/20 text-cyan-400 shrink-0">
            <i data-lucide="hourglass" class="w-6 h-6 animate-spin"></i>
        </div>
        <div class="space-y-1 text-xs">
            <h4 class="font-bold text-white text-sm">Bukti Pembayaran Sedang Diverifikasi</h4>
            <p class="text-cyan-200">
                Bukti transfer Anda telah kami terima pada <strong class="text-white">{{ $payment->proof_uploaded_at?->format('d M Y H:i') }}</strong>. Tim Admin kami sedang mencocokkan mutasi bank Anda. Paket Cooca Core akan aktif otomatis setelah disetujui (biasanya 5–15 menit).
            </p>
        </div>
    </div>
    @endif

    <!-- Approved Banner -->
    @if($payment->isApproved())
    <div class="p-5 rounded-2xl bg-emerald-950/40 border border-emerald-500/50 flex items-start justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="p-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 shrink-0">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1 text-xs">
                <h4 class="font-bold text-white text-sm">Pembayaran Telah Disetujui!</h4>
                <p class="text-emerald-200">
                    Paket Cooca Core Anda telah aktif hingga <strong class="text-white">{{ $payment->business->subscription?->ends_at?->format('d M Y') }}</strong>. Nikmati seluruh fitur unlimited dan 10.000.000 Token AI!
                </p>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shrink-0 transition">
            Buka Dashboard
        </a>
    </div>
    @endif

    <!-- Payment Instruction Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Left: Destination Bank & Nominal -->
        <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Nomor Pesanan</span>
                    <h3 class="text-lg font-black text-white font-mono">{{ $payment->order_number }}</h3>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400">Paket:</span>
                    <div class="text-xs font-black text-emerald-400 uppercase">
                        {{ $payment->cycle === 'annual' ? 'Core Tahunan' : 'Core Bulanan' }}
                    </div>
                </div>
            </div>

            <!-- Transfer Amount Box with 3-digit Highlight -->
            <div class="p-5 rounded-2xl bg-slate-950/80 border border-indigo-500/30 space-y-2">
                <div class="text-xs text-slate-400">Total Transfer (Wajib Pas Hingga 3 Digit Terakhir):</div>
                <div class="flex items-baseline justify-between">
                    <div class="text-2xl sm:text-3xl font-black font-mono text-white tracking-tight">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}<span class="text-amber-400 underline decoration-2">{{ str_pad((string)$payment->unique_code, 3, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <button type="button"
                            @click="copyToClipboard('{{ (int)$payment->total_payable }}', 'nominal')"
                            class="px-3 py-1.5 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span x-text="copiedText === 'nominal' ? 'Tersalin!' : 'Salin'"></span>
                    </button>
                </div>
                <p class="text-[11px] text-amber-300/90 leading-relaxed pt-1">
                    ⚠️ <strong class="text-white">Penting:</strong> 3 digit terakhir (<span class="font-mono font-bold text-amber-400">{{ $payment->unique_code }}</span>) adalah kode identifikasi unik untuk mempercepat verifikasi otomatis rekening Anda.
                </p>
            </div>

            <!-- Destination Account Details -->
            <div class="space-y-4 text-xs">
                <div class="space-y-1">
                    <span class="text-slate-400">Bank Tujuan:</span>
                    <div class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-cyan-400"></i>
                        <span>{{ $methodDetails['bank_name'] }}</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-slate-400">Nomor Rekening Tujuan:</span>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="font-mono text-base font-black text-white tracking-wider">{{ $methodDetails['account_number'] }}</span>
                        @if($payment->payment_method !== 'qris')
                        <button type="button"
                                @click="copyToClipboard('{{ str_replace(['-', ' '], '', $methodDetails['account_number']) }}', 'rekening')"
                                class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-1">
                            <i data-lucide="copy" class="w-3 h-3"></i>
                            <span x-text="copiedText === 'rekening' ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                        @endif
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-slate-400">Atas Nama Rekening:</span>
                    <div class="font-bold text-slate-200">{{ $methodDetails['account_name'] }}</div>
                </div>
            </div>

            <!-- QRIS Display if QRIS method selected -->
            @if($payment->payment_method === 'qris')
            <div class="p-4 rounded-2xl bg-white text-slate-900 text-center space-y-2">
                <div class="font-black text-sm tracking-wider uppercase">QRIS COOCA PAY</div>
                <div class="w-48 h-48 mx-auto bg-slate-100 rounded-xl border border-slate-300 flex items-center justify-center p-2">
                    <!-- QRIS Placeholder Graphic -->
                    <div class="text-center space-y-1">
                        <i data-lucide="qr-code" class="w-32 h-32 text-slate-800 mx-auto"></i>
                        <span class="text-[10px] font-mono font-bold text-slate-600 block">NMID: ID1020304050</span>
                    </div>
                </div>
                <div class="text-[11px] text-slate-600 font-semibold">Scan menggunakan BCA Mobile, GoPay, OVO, ShopeePay, Dana, dll.</div>
            </div>
            @endif
        </div>

        <!-- Right: Upload Proof Form OR Status Preview -->
        <div class="space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-5">
                <div class="border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="upload" class="w-5 h-5 text-emerald-400"></i>
                        <span>Bukti Pembayaran Transfer</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Unggah foto struk ATM, tangkapan layar m-banking, atau file PDF transfer Anda.</p>
                </div>

                @if($payment->isPending() || $payment->isRejected())
                <!-- Upload Form -->
                <form method="POST" action="{{ route('billing.payment.upload', $payment) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <!-- File input -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300">File Struk Bukti Transfer <span class="text-rose-400">*</span></label>
                        <input type="file" name="payment_proof" required accept="image/*,.pdf"
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-slate-200 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500 file:text-slate-950 hover:file:bg-emerald-400 cursor-pointer">
                        <p class="text-[10px] text-slate-500">Format: JPG, PNG, WEBP, atau PDF (Maks. 5MB).</p>
                    </div>

                    <!-- Sender Bank -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300">Nama Bank / e-Wallet Pengirim</label>
                        <input type="text" name="sender_bank" placeholder="Contoh: BCA, Mandiri, GoPay, OVO"
                               value="{{ old('sender_bank', $payment->sender_bank) }}"
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Sender Account Name -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300">Nama Pemilik Rekening Pengirim <span class="text-rose-400">*</span></label>
                        <input type="text" name="sender_account_name" required placeholder="Sesuai nama di buku tabungan/aplikasi"
                               value="{{ old('sender_account_name', $payment->sender_account_name) }}"
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Sender Account Number -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300">Nomor Rekening / No HP Pengirim (Opsional)</label>
                        <input type="text" name="sender_account_number" placeholder="Nomor rekening Anda"
                               value="{{ old('sender_account_number', $payment->sender_account_number) }}"
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Catatan untuk tim verifikasi"
                                  class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500">{{ old('notes', $payment->notes) }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Bukti Pembayaran</span>
                    </button>
                </form>

                @else
                <!-- Preview Uploaded Proof -->
                <div class="space-y-4 text-xs">
                    <div class="space-y-1 text-slate-300">
                        <div class="flex justify-between py-1 border-b border-slate-800/80">
                            <span class="text-slate-400">Pengirim:</span>
                            <span class="font-bold text-white">{{ $payment->sender_account_name }} ({{ $payment->sender_bank ?? '-' }})</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800/80">
                            <span class="text-slate-400">Diunggah Pada:</span>
                            <span class="font-mono text-slate-300">{{ $payment->proof_uploaded_at?->format('d M Y H:i') }}</span>
                        </div>
                        @if($payment->notes)
                        <div class="py-1">
                            <span class="text-slate-400">Catatan:</span>
                            <p class="text-slate-300 italic">{{ $payment->notes }}</p>
                        </div>
                        @endif
                    </div>

                    @if($payment->payment_proof_path)
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 text-center space-y-2">
                        <span class="text-[11px] font-bold text-slate-400 block">Lampiran Bukti Transfer</span>
                        <a href="{{ $payment->getProofUrl() }}" target="_blank" class="inline-block group overflow-hidden rounded-xl border border-slate-700">
                            <img src="{{ $payment->getProofUrl() }}" alt="Bukti Transfer" class="max-h-60 mx-auto object-contain group-hover:scale-105 transition-transform">
                        </a>
                        <div class="pt-1">
                            <a href="{{ $payment->getProofUrl() }}" target="_blank" class="text-xs text-emerald-400 hover:underline inline-flex items-center gap-1 font-semibold">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                <span>Buka Ukuran Penuh</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

            </div>
        </div>

    </div>

</div>
@endsection
