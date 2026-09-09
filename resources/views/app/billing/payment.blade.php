@extends('layouts.app', [
    'title' => 'Pembayaran ' . $payment->order_number . ' — Cooca UMKM',
    'headerTitle' => 'Instruksi Pembayaran & Konfirmasi',
    'headerSubtitle' => 'Selesaikan transfer dana dan upload bukti struk untuk aktivasi instan paket Cooca UMKM'
])

@section('content')
@php
    $badge = $payment->getStatusBadge();
    $methodDetails = $payment->getPaymentMethodDetails();
    $uniqueStr = str_pad((string)$payment->unique_code, 3, '0', STR_PAD_LEFT);
    $bankCode = strtolower($payment->payment_method ?? '');
@endphp

<div class="space-y-6 pb-12" x-data="{
    copiedText: null,
    proofPreview: null,
    fileName: '',
    fileSize: '',
    isDragging: false,
    isUploading: false,
    fileError: '',
    selectedQuickBank: '{{ old('sender_bank', $payment->sender_bank) }}',
    copyToClipboard(text, label) {
        if (!navigator.clipboard) {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        } else {
            navigator.clipboard.writeText(text);
        }
        this.copiedText = label;
        setTimeout(() => this.copiedText = null, 2500);
    },
    handleFileSelect(file) {
        this.fileError = '';
        if (!file) return;

        // Validation: Max 5MB
        if (file.size > 5 * 1024 * 1024) {
            this.fileError = 'Ukuran berkas melebihi batas 5 MB. Harap pilih foto/dokumen yang lebih kecil.';
            this.clearFile();
            return;
        }

        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            this.fileError = 'Format berkas tidak didukung. Harap gunakan format JPG, PNG, WEBP, atau PDF.';
            this.clearFile();
            return;
        }

        this.fileName = file.name;
        this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        if (file.type.startsWith('image/')) {
            this.proofPreview = URL.createObjectURL(file);
        } else {
            this.proofPreview = 'pdf';
        }
    },
    onProofChange(e) {
        const file = e.target.files[0];
        this.handleFileSelect(file);
    },
    onDrop(e) {
        this.isDragging = false;
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('payment_proof_input');
            if (input) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            }
            this.handleFileSelect(file);
        }
    },
    clearFile() {
        this.proofPreview = null;
        this.fileName = '';
        this.fileSize = '';
        const input = document.getElementById('payment_proof_input');
        if (input) input.value = '';
    }
}">

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('billing.limits') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
            <span>Langganan &amp; Billing</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('billing.history') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
            <span>Riwayat Tagihan</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Pembayaran {{ $payment->order_number }}</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Instruksi &amp; Konfirmasi Pembayaran</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    {{ $payment->order_number }}
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1.5 shadow-2xs {{ $badge['class'] }}">
                    <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                    <span>{{ $badge['label'] }}</span>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Instruksi Pembayaran {{ $payment->package_name ?? ($payment->cycle === 'annual' ? 'Cooca Tahunan' : 'Cooca Bulanan') }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Selesaikan transfer dana sesuai nominal persis ke rekening resmi, lalu konfirmasi dengan mengunggah bukti struk transfer.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('billing.history') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs inline-flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-500 flex-1 sm:flex-none">
                <i data-lucide="arrow-left" class="w-4 h-4" aria-hidden="true"></i>
                <span>Riwayat Tagihan</span>
            </a>
            <a href="{{ route('billing.payment.invoice', $payment) }}" 
               target="_blank"
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs inline-flex items-center justify-center gap-2 group focus-visible:ring-2 focus-visible:ring-emerald-500 flex-1 sm:flex-none">
                <i data-lucide="printer" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform" aria-hidden="true"></i>
                <span>Cetak Invoice</span>
            </a>
        </div>
    </div>

    <!-- 2. Lifecycle Workflow Stepper (4-Phase Progress Tracker) -->
    <section aria-labelledby="order-progress-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5">
        <h3 id="order-progress-heading" class="sr-only">Proses Verifikasi Pesanan</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative">
            <!-- Step 1: Order Dibuat -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-mono text-emerald-700 dark:text-emerald-400 uppercase tracking-wider font-bold">Langkah 1</div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Pesanan Dibuat</div>
                </div>
            </div>

            <!-- Step 2: Transfer Nominal -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl {{ $payment->isPending() ? 'bg-amber-100 dark:bg-amber-500/20 border border-amber-300 dark:border-amber-500/40 text-amber-700 dark:text-amber-300 ring-2 ring-amber-400/20' : 'bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400' }} flex items-center justify-center shrink-0 shadow-2xs">
                    @if($payment->isPending())
                        <i data-lucide="credit-card" class="w-4 h-4 animate-pulse" aria-hidden="true"></i>
                    @else
                        <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-mono {{ $payment->isPending() ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400' }} uppercase tracking-wider font-bold">Langkah 2</div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Transfer Dana</div>
                </div>
            </div>

            <!-- Step 3: Verifikasi Bukti -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl {{ $payment->isAwaitingApproval() ? 'bg-cyan-100 dark:bg-cyan-500/20 border border-cyan-300 dark:border-cyan-500/40 text-cyan-700 dark:text-cyan-300 ring-2 ring-cyan-400/20' : ($payment->isApproved() ? 'bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-slate-400') }} flex items-center justify-center shrink-0 shadow-2xs">
                    @if($payment->isApproved())
                        <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                    @elseif($payment->isAwaitingApproval())
                        <i data-lucide="hourglass" class="w-4 h-4 animate-spin" aria-hidden="true"></i>
                    @else
                        <i data-lucide="file-check" class="w-4 h-4" aria-hidden="true"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-mono {{ $payment->isAwaitingApproval() ? 'text-cyan-700 dark:text-cyan-400' : ($payment->isApproved() ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400') }} uppercase tracking-wider font-bold">Langkah 3</div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Verifikasi Admin</div>
                </div>
            </div>

            <!-- Step 4: Paket Aktif -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl {{ $payment->isApproved() ? 'bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-slate-400' }} flex items-center justify-center shrink-0 shadow-2xs">
                    <i data-lucide="sparkles" class="w-4 h-4" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-mono {{ $payment->isApproved() ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400' }} uppercase tracking-wider font-bold">Langkah 4</div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Aktivasi Paket</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Alert Banners Based on Current Order State -->
    @if($payment->isRejected())
    <section aria-labelledby="status-rejected-heading" class="p-5 sm:p-6 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-200 dark:border-rose-500/50 shadow-xs flex flex-col sm:flex-row items-start gap-5">
        <div class="w-11 h-11 rounded-xl bg-rose-100 dark:bg-rose-500/20 border border-rose-200 dark:border-rose-500/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 shadow-2xs" aria-hidden="true">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        <div class="space-y-2 flex-1">
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Pemberitahuan Status Tagihan</span>
                <span class="text-[11px] text-rose-600 dark:text-rose-300 font-mono">{{ $payment->rejected_at?->format('d M Y H:i') }}</span>
            </div>
            <h4 id="status-rejected-heading" class="font-black text-slate-900 dark:text-white text-base">Pembayaran Belum Dapat Diverifikasi</h4>
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-950/80 border border-rose-200 dark:border-rose-500/30 text-xs text-rose-800 dark:text-rose-200">
                <strong class="text-slate-900 dark:text-white">Alasan Penolakan:</strong> {{ $payment->admin_notes ?? 'Nominal transfer tidak sesuai dengan kode unik atau mutasi bank belum teridentifikasi.' }}
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-300">
                Jangan khawatir, Anda dapat melakukan transfer ulang atau mengunggah foto struk transfer yang benar melalui formulir di bawah.
            </p>
        </div>
    </section>
    @endif

    @if($payment->isAwaitingApproval())
    <section aria-labelledby="status-awaiting-heading" class="p-5 sm:p-6 rounded-2xl bg-cyan-50 dark:bg-gradient-to-r dark:from-cyan-950/50 dark:via-slate-900/60 dark:to-cyan-950/50 border-2 border-cyan-200 dark:border-cyan-500/40 shadow-xs flex flex-col sm:flex-row items-start gap-5">
        <div class="w-11 h-11 rounded-xl bg-cyan-100 dark:bg-cyan-500/20 border border-cyan-200 dark:border-cyan-500/40 text-cyan-600 dark:text-cyan-300 flex items-center justify-center shrink-0 shadow-2xs" aria-hidden="true">
            <i data-lucide="hourglass" class="w-6 h-6 animate-spin"></i>
        </div>
        <div class="space-y-2 flex-1">
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-700 dark:text-cyan-400">Sedang Diproses</span>
                <span class="text-[11px] text-cyan-600 dark:text-cyan-300 font-mono">Estimasi SLA: 5–15 Menit</span>
            </div>
            <h4 id="status-awaiting-heading" class="font-black text-slate-900 dark:text-white text-base">Bukti Pembayaran Anda Berhasil Diterima</h4>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Bukti transfer telah diterima pada <strong class="text-slate-900 dark:text-white font-mono">{{ $payment->proof_uploaded_at?->format('d M Y, H:i') }} WIB</strong>. Tim Billing Cooca sedang memverifikasi mutasi rekening. Akses paket Cooca UMKM bisnis Anda akan langsung terbuka otomatis setelah disetujui.
            </p>
            <div class="flex items-center gap-2 pt-1 text-[11px] text-cyan-700 dark:text-cyan-300 font-semibold">
                <i data-lucide="shield-check" class="w-4 h-4" aria-hidden="true"></i>
                <span>Halaman ini akan otomatis diperbarui atau Anda dapat menyegarkan browser sewaktu-waktu.</span>
            </div>
        </div>
    </section>
    @endif

    @if($payment->isApproved())
    <section aria-labelledby="status-approved-heading" class="p-5 sm:p-6 rounded-2xl bg-emerald-50 dark:bg-gradient-to-r dark:from-emerald-950/60 dark:via-slate-900/60 dark:to-emerald-950/60 border-2 border-emerald-200 dark:border-emerald-500/50 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-start gap-5">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-2xs" aria-hidden="true">
                <i data-lucide="check-circle-2" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Pembayaran Sah</span>
                    <span class="text-[11px] text-emerald-600 dark:text-emerald-300 font-mono">Disetujui {{ $payment->approved_at?->format('d M Y H:i') }}</span>
                </div>
                <h4 id="status-approved-heading" class="font-black text-slate-900 dark:text-white text-base sm:text-lg">Paket Cooca UMKM Anda Telah Aktif!</h4>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Terima kasih atas kepercayaan Anda. Fitur unggulan paket <strong class="text-emerald-700 dark:text-emerald-400 font-semibold">{{ $payment->package_name ?? 'Cooca UMKM' }}</strong> kini aktif untuk bisnis Anda hingga <strong class="text-slate-900 dark:text-white font-mono">{{ $payment->business->subscription?->ends_at?->format('d M Y') ?? 'Masa Berlaku Aktif' }}</strong>.
                </p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto shrink-0">
            <a href="{{ route('dashboard') }}" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 group focus-visible:ring-2 focus-visible:ring-emerald-400">
                <span>Buka Dashboard Utama</span>
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
            </a>
            <a href="{{ route('billing.limits') }}" 
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-500">
                <i data-lucide="gauge" class="w-4 h-4 text-slate-400" aria-hidden="true"></i>
                <span>Cek Kuota</span>
            </a>
            <a href="{{ route('billing.payment.invoice', $payment) }}" 
               target="_blank"
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-white dark:bg-slate-800 border border-emerald-200 dark:border-slate-700 hover:bg-emerald-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-500">
                <i data-lucide="printer" class="w-4 h-4" aria-hidden="true"></i>
                <span>Unduh Faktur</span>
            </a>
        </div>
    </section>
    @endif

    <!-- 4. Main Payment Details & Confirmation Action Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

        <!-- Left Column: Bank Destination & Exact Nominal Transfer (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card: Nominal Transfer -->
            <section aria-labelledby="nominal-transfer-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-4">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold block mb-1">Total Pembayaran Transfer</span>
                        <h3 id="nominal-transfer-heading" class="text-xs text-slate-600 dark:text-slate-300">Wajib transfer presisi tepat hingga 3 digit terakhir:</h3>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90 font-mono">
                        <i data-lucide="tag" class="w-3 h-3" aria-hidden="true"></i>
                        <span>Kode Unik Aktif</span>
                    </span>
                </div>

                <!-- Big Currency Display with 1-Click Copy Action -->
                <div class="py-5 flex flex-col sm:flex-row sm:items-baseline justify-between gap-4">
                    <div>
                        <div class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-slate-900 dark:text-white flex items-baseline gap-1">
                            <span class="text-slate-500 dark:text-slate-400 text-xl font-sans font-semibold">Rp</span>
                            <span>{{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400" aria-hidden="true"></i>
                            <span>Selesaikan transfer sebelum kedaluwarsa (24 Jam)</span>
                        </div>
                    </div>

                    <!-- 1-Click Copy Nominal Clean Button -->
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="copyToClipboard('{{ (int)$payment->total_payable }}', 'nominal')"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/20 dark:hover:bg-emerald-500/30 border border-emerald-200 dark:border-emerald-500/30 transition cursor-pointer flex items-center gap-2 shadow-2xs focus-visible:ring-2 focus-visible:ring-emerald-400"
                                aria-label="Salin Angka Bersih Nominal Transfer">
                            <i data-lucide="copy" class="w-4 h-4" x-show="copiedText !== 'nominal'" aria-hidden="true"></i>
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" x-show="copiedText === 'nominal'" x-cloak aria-hidden="true"></i>
                            <span x-text="copiedText === 'nominal' ? 'Nominal Tersalin!' : 'Salin Angka Bersih'"></span>
                        </button>
                    </div>
                </div>

                <!-- Nominal Breakdown Box -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-2.5 font-mono">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 dark:text-slate-400 font-sans">Harga Paket Dasar:</span>
                        <span class="font-bold text-slate-900 dark:text-slate-200">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/80 dark:border-slate-900">
                        <div class="flex items-center gap-1.5 font-sans">
                            <span class="text-amber-700 dark:text-amber-300 font-bold">Kode Verifikasi Unik:</span>
                            <span class="text-[10px] text-slate-500">(Otomatisasi)</span>
                        </div>
                        <span class="font-black text-amber-700 dark:text-amber-400 bg-amber-100/80 dark:bg-amber-500/10 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-500/30">
                            +{{ $uniqueStr }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200 dark:border-slate-800 font-bold">
                        <span class="text-slate-900 dark:text-white font-sans">Total Tagihan Final:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-black text-sm">Rp {{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Warning Notice -->
                <div class="mt-4 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 flex items-start gap-3">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" aria-hidden="true"></i>
                    <p class="text-[11px] text-amber-800 dark:text-amber-200/90 leading-relaxed">
                        <strong class="font-semibold">Penting:</strong> Mohon transfer dengan jumlah yang <strong class="underline decoration-amber-400 font-bold">PERSIS</strong> hingga 3 digit terakhir (<span class="font-mono font-bold text-amber-700 dark:text-amber-300">{{ $uniqueStr }}</span>). Perbedaan nominal akan memperlambat pencocokan mutasi rekening Anda.
                    </p>
                </div>
            </section>

            <!-- Card: Rekening Tujuan Transfer -->
            <section aria-labelledby="destination-account-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5">
                <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold block mb-1">Rekening Tujuan Resmi</span>
                        <h3 id="destination-account-heading" class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="building-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                            <span>{{ $methodDetails['bank_name'] }}</span>
                        </h3>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 font-mono uppercase tracking-wider">
                        {{ $payment->payment_method }}
                    </span>
                </div>

                <!-- Account Number Display with Copy Action -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nomor Rekening / Saluran Tujuan:</label>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-inner">
                        <div class="space-y-0.5">
                            <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-white tracking-wider selection:bg-emerald-500 selection:text-white">
                                {{ $methodDetails['account_number'] }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                a/n <span class="font-bold text-slate-800 dark:text-slate-200">{{ $methodDetails['account_name'] }}</span>
                            </div>
                        </div>

                        @if(($methodDetails['type'] ?? '') !== 'qris')
                        <button type="button"
                                @click="copyToClipboard('{{ str_replace(['-', ' '], '', $methodDetails['account_number']) }}', 'rekening')"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-2 shrink-0 focus-visible:ring-2 focus-visible:ring-emerald-400"
                                aria-label="Salin Nomor Rekening Tujuan">
                            <i data-lucide="copy" class="w-3.5 h-3.5" x-show="copiedText !== 'rekening'" aria-hidden="true"></i>
                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" x-show="copiedText === 'rekening'" x-cloak aria-hidden="true"></i>
                            <span x-text="copiedText === 'rekening' ? 'Nomor Tersalin!' : 'Salin Nomor Rekening'"></span>
                        </button>
                        @endif
                    </div>
                </div>

                <!-- QRIS Visual Card if QRIS selected -->
                @if(($methodDetails['type'] ?? '') === 'qris' || $payment->payment_method === 'qris')
                <div class="p-6 rounded-2xl bg-white border border-slate-200 text-slate-950 text-center space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <div class="text-left">
                            <span class="text-[9px] font-black tracking-widest text-slate-500 uppercase block font-mono">STANDAR PEMBAYARAN NASIONAL</span>
                            <span class="text-sm font-black text-slate-900 font-sans tracking-tight">QRIS COOCA PAY INDONESIA</span>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold font-mono">NMID: ID1020304050</span>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 inline-block shadow-inner">
                        @if(!empty($methodDetails['qr_image_url']))
                            <img src="{{ $methodDetails['qr_image_url'] }}" alt="QRIS QR Code" class="w-56 h-56 object-contain mx-auto rounded-lg">
                        @else
                            <div class="w-56 h-56 flex flex-col items-center justify-center text-center p-3">
                                <i data-lucide="qr-code" class="w-36 h-36 text-slate-900 mx-auto" aria-hidden="true"></i>
                                <span class="text-[10px] font-mono font-bold text-slate-700 mt-2 block">{{ $methodDetails['account_number'] }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-1 text-xs text-slate-600 max-w-sm mx-auto">
                        <p class="font-bold text-slate-900">Mendukung Seluruh Aplikasi Perbankan &amp; e-Wallet</p>
                        <p class="text-[11px] text-slate-500">BCA, Mandiri Livin, BRImo, BNI+, GoPay, OVO, Dana, ShopeePay, LinkAja.</p>
                    </div>
                </div>
                @endif

                <!-- Payment Guide / Instructions -->
                @if(!empty($methodDetails['instructions']))
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-300">
                        <i data-lucide="help-circle" class="w-4 h-4 text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                        <span>Instruksi Transfer:</span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-[11px]">{{ $methodDetails['instructions'] }}</p>
                </div>
                @endif

                <!-- WhatsApp Support Callout -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Ada kendala dalam pembayaran?</span>
                    <a href="https://wa.me/?text=Halo%20Admin%20Cooca,%20saya%20butuh%20konfirmasi%20pembayaran%20pesanan%20nomor%20{{ $payment->order_number }}" 
                       target="_blank" 
                       class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 font-bold transition focus-visible:ring-2 focus-visible:ring-emerald-500 rounded px-1">
                        <i data-lucide="message-circle" class="w-4 h-4" aria-hidden="true"></i>
                        <span>Hubungi WhatsApp Billing CS</span>
                    </a>
                </div>
            </section>

        </div>

        <!-- Right Column: Proof Upload Form OR Status Showcase (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

            <section aria-labelledby="confirmation-heading" class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5">

                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 id="confirmation-heading" class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="receipt-text" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        <span>Konfirmasi Bukti Transfer</span>
                    </h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-1">
                        @if($payment->isPending() || $payment->isRejected())
                            Unggah foto struk ATM, tangkapan layar m-banking, atau berkas bukti transfer Anda.
                        @else
                            Rincian berkas bukti pembayaran yang telah Anda kirimkan untuk pesanan ini.
                        @endif
                    </p>
                </div>

                @if($payment->isPending() || $payment->isRejected())
                <!-- Upload Form with Validation and Double-Submit Prevention -->
                <form method="POST" action="{{ route('billing.payment.upload', $payment) }}" enctype="multipart/form-data" @submit="isUploading = true" class="space-y-4">
                    @csrf

                    <!-- Error Alert from Client-Side Validation -->
                    <div x-show="fileError" x-cloak class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs flex items-start gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" aria-hidden="true"></i>
                        <span x-text="fileError"></span>
                    </div>

                    <!-- Drag & Drop File Input Zone -->
                    <div class="space-y-1.5">
                        <label for="payment_proof_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Berkas Struk Transfer <span class="text-rose-500">*</span>
                        </label>

                        <div class="relative group"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="onDrop($event)">
                            
                            <input type="file" 
                                   name="payment_proof" 
                                   id="payment_proof_input"
                                   required 
                                   accept="image/jpeg,image/png,image/webp,application/pdf" 
                                   @change="onProofChange($event)"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   aria-label="Upload Berkas Bukti Transfer">

                            <!-- Visual Placeholder Dropzone -->
                            <div class="p-6 rounded-2xl border-2 border-dashed transition-all text-center space-y-2"
                                 :class="isDragging ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700 group-hover:border-emerald-400 bg-slate-50/50 dark:bg-slate-950/60 group-hover:bg-slate-50 dark:group-hover:bg-slate-950'">
                                <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-800/80 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/20 text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 flex items-center justify-center mx-auto transition shadow-2xs" aria-hidden="true">
                                    <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                    <span class="text-emerald-600 dark:text-emerald-400 underline decoration-emerald-500/50 font-bold">Pilih berkas</span> atau geser ke sini
                                </div>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">JPG, PNG, WEBP, atau PDF (Maks. 5 MB)</p>
                            </div>
                        </div>

                        <!-- Live File Preview Box with Remove Action -->
                        <div x-show="proofPreview" x-cloak class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-emerald-300 dark:border-emerald-500/40 space-y-3 mt-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <i data-lucide="file-text" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
                                    <span class="font-mono text-slate-800 dark:text-slate-200 truncate font-semibold" x-text="fileName"></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400" x-text="fileSize"></span>
                                    <button type="button" @click="clearFile()" class="p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400 hover:text-rose-500 transition cursor-pointer" title="Hapus Berkas">
                                        <i data-lucide="x" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <template x-if="proofPreview && proofPreview !== 'pdf'">
                                <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-black/5 dark:bg-black/40">
                                    <img :src="proofPreview" alt="Preview Struk Pembayaran" class="max-h-48 w-full object-contain mx-auto">
                                </div>
                            </template>
                            <template x-if="proofPreview === 'pdf'">
                                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center flex items-center justify-center gap-2 text-xs text-slate-700 dark:text-slate-300 font-mono">
                                    <i data-lucide="file" class="w-4 h-4 text-rose-500" aria-hidden="true"></i>
                                    <span>Dokumen PDF Siap Dikirim</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Quick Bank Name Selector -->
                    <div class="space-y-1.5">
                        <label for="sender_bank_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Bank / e-Wallet Pengirim</label>
                        <input type="text" 
                               id="sender_bank_input"
                               name="sender_bank" 
                               x-model="selectedQuickBank"
                               placeholder="Contoh: BCA, Mandiri, BRI, GoPay, OVO"
                               value="{{ old('sender_bank', $payment->sender_bank) }}"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition">
                        
                        <!-- Quick bank chips -->
                        <div class="flex flex-wrap gap-1.5 pt-1" aria-label="Pilihan Cepat Bank">
                            <button type="button" @click="selectedQuickBank = 'BCA'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">BCA</button>
                            <button type="button" @click="selectedQuickBank = 'Mandiri'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">Mandiri</button>
                            <button type="button" @click="selectedQuickBank = 'BRI'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">BRI</button>
                            <button type="button" @click="selectedQuickBank = 'BNI'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">BNI</button>
                            <button type="button" @click="selectedQuickBank = 'GoPay'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">GoPay</button>
                            <button type="button" @click="selectedQuickBank = 'OVO'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">OVO</button>
                            <button type="button" @click="selectedQuickBank = 'Dana'" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-mono font-bold transition cursor-pointer">Dana</button>
                        </div>
                    </div>

                    <!-- Sender Account Name (Required) -->
                    <div class="space-y-1.5">
                        <label for="sender_account_name_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Nama Pemilik Rekening Pengirim <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="sender_account_name_input"
                               name="sender_account_name" 
                               required 
                               placeholder="Nama sesuai buku rekening atau profil m-banking"
                               value="{{ old('sender_account_name', $payment->sender_account_name) }}"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition">
                    </div>

                    <!-- Sender Account Number (Optional) -->
                    <div class="space-y-1.5">
                        <label for="sender_account_number_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Nomor Rekening / No HP Pengirim <span class="text-slate-500 text-[11px] font-normal">(Opsional)</span>
                        </label>
                        <input type="text" 
                               id="sender_account_number_input"
                               name="sender_account_number" 
                               placeholder="Contoh: 1234567890 / 08123456789"
                               value="{{ old('sender_account_number', $payment->sender_account_number) }}"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition font-mono">
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <label for="notes_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Catatan Tambahan <span class="text-slate-500 text-[11px] font-normal">(Opsional)</span>
                        </label>
                        <textarea id="notes_input"
                                  name="notes" 
                                  rows="2" 
                                  placeholder="Catatan untuk tim verifikasi billing Cooca..."
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition">{{ old('notes', $payment->notes) }}</textarea>
                    </div>

                    <!-- Submit CTA Button with Double-Submit Prevention -->
                    <button type="submit"
                            :disabled="isUploading"
                            class="w-full px-4 py-3 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 group disabled:opacity-60 disabled:cursor-not-allowed focus-visible:ring-2 focus-visible:ring-emerald-400">
                        <span x-show="isUploading" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full" aria-hidden="true"></span>
                        <i data-lucide="send" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" x-show="!isUploading" aria-hidden="true"></i>
                        <span x-text="isUploading ? 'Mengunggah Bukti Pembayaran...' : 'Kirim Konfirmasi Pembayaran'"></span>
                    </button>
                </form>

                @else
                <!-- Preview Uploaded State (Awaiting Approval or Approved) -->
                <div class="space-y-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-900">
                            <span class="text-slate-500 dark:text-slate-400">Pengirim:</span>
                            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $payment->sender_account_name }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-900">
                            <span class="text-slate-500 dark:text-slate-400">Saluran Bank:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $payment->sender_bank ?: '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-900">
                            <span class="text-slate-500 dark:text-slate-400">Waktu Kirim:</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ $payment->proof_uploaded_at?->format('d M Y, H:i') }} WIB</span>
                        </div>
                        @if($payment->notes)
                        <div class="pt-1">
                            <span class="text-slate-500 dark:text-slate-400 block mb-1">Catatan Tambahan:</span>
                            <p class="text-slate-700 dark:text-slate-300 italic text-[11px] bg-white dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">{{ $payment->notes }}</p>
                        </div>
                        @endif
                    </div>

                    @if($payment->payment_proof_path)
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider font-mono">Lampiran Struk Transfer</span>
                            <a href="{{ $payment->getProofUrl() }}" target="_blank" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 font-bold focus-visible:ring-1 focus-visible:ring-emerald-400 rounded">
                                <span>Buka Full</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-black/50 p-2">
                            <a href="{{ $payment->getProofUrl() }}" target="_blank" class="block group overflow-hidden rounded-lg">
                                <img src="{{ $payment->getProofUrl() }}" alt="Bukti Transfer Struk" class="max-h-64 mx-auto object-contain group-hover:scale-105 transition-transform duration-300">
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 text-[11px] leading-relaxed space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-300">
                            <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                            <span>Verifikasi Terproteksi</span>
                        </div>
                        <p>Dokumen bukti transfer tersimpan dengan aman di server Cooca dan hanya dapat diakses oleh tim billing berwenang.</p>
                    </div>
                </div>
                @endif

            </section>

        </div>

    </div>

</div>
@endsection
