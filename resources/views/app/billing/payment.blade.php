@extends('layouts.app', [
    'title' => 'Pembayaran ' . $payment->order_number . ' - Cooca',
    'headerTitle' => 'Instruksi Pembayaran & Konfirmasi',
    'headerSubtitle' => 'Selesaikan transfer dana dan upload bukti struk untuk aktivasi instan paket Cooca',
])

@section('content')
    @php
        $badge = $payment->getStatusBadge();
        $methodDetails = $payment->getPaymentMethodDetails();
        $uniqueStr = str_pad((string) $payment->unique_code, 3, '0', STR_PAD_LEFT);
        $bankCode = strtolower($payment->payment_method ?? '');
    @endphp

    <div class="space-y-6 pb-28 lg:pb-10" x-data="{
        copiedText: null,
        proofPreview: null,
        fileName: '',
        fileSize: '',
        isDragging: false,
        isUploading: false,
        fileError: '',
        selectedQuickBank: '{{ old('sender_bank', $payment->sender_bank) }}',
        checkingStatus: false,
        checkStatusFeedback: '',
        showManualUpload: {{ $payment->isManual() ? 'true' : 'false' }},
        async checkPaymentStatus() {
            this.checkingStatus = true;
            this.checkStatusFeedback = 'Memeriksa mutasi gateway TriPay...';
            try {
                const res = await fetch('{{ route('billing.payment.status', $payment) }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.is_paid) {
                    this.checkStatusFeedback = 'Pembayaran sah terverifikasi! Memperbarui halaman...';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.checkStatusFeedback = 'Belum ada pembayaran terdeteksi di TriPay. Silakan selesaikan transaksi.';
                    setTimeout(() => this.checkStatusFeedback = '', 4500);
                }
            } catch (e) {
                this.checkStatusFeedback = 'Gagal memeriksa status ke server.';
                setTimeout(() => this.checkStatusFeedback = '', 3500);
            } finally {
                this.checkingStatus = false;
            }
        },
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
                this.fileError = 'Ukuran berkas melebihi batas 5 MB. Harap pilih foto atau dokumen yang lebih kecil.';
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
        <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}"
                class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors flex items-center gap-1.5 font-medium text-black dark:text-white">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-600"></i>
            <a href="{{ route('billing.limits') }}"
                class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                <span>Langganan &amp; Billing</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-600"></i>
            <a href="{{ route('billing.history') }}"
                class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                <span>Riwayat Tagihan</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-600"></i>
            <span class="text-black dark:text-white font-semibold flex items-center gap-1.5">
                <span>Pembayaran {{ $payment->order_number }}</span>
            </span>
        </nav>

        <!-- 1. Top Header Banner -->
        <div
            class="bg-white dark:bg-[#1C1C1E] p-5 sm:p-6 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-all">
            <div class="space-y-1.5 max-w-3xl">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 font-mono">
                        No. Pesanan: {{ $payment->order_number }}
                    </span>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold border inline-flex items-center gap-1.5 {{ $badge['class'] }}">
                        <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                        <span>{{ $badge['label'] }}</span>
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight">
                    Instruksi Pembayaran
                    {{ $payment->package_name ?? ($payment->cycle === 'annual' ? 'Cooca Tahunan' : 'Cooca Bulanan') }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    Selesaikan transfer dana sesuai nominal persis ke rekening resmi, lalu konfirmasi dengan mengunggah bukti transfer.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <a href="{{ route('billing.history') }}"
                    class="h-10 px-3.5 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 flex-1 sm:flex-none">
                    <i data-lucide="arrow-left" class="w-4 h-4" aria-hidden="true"></i>
                    <span>Riwayat Tagihan</span>
                </a>
                <a href="{{ route('billing.payment.invoice', $payment) }}" target="_blank"
                    class="h-10 px-3.5 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 group flex-1 sm:flex-none">
                    <i data-lucide="printer"
                        class="w-4 h-4 text-[#007AFF]"
                        aria-hidden="true"></i>
                    <span>Cetak Invoice</span>
                </a>
            </div>
        </div>

        <!-- 2. Lifecycle Workflow Stepper (4-Phase Progress Tracker) -->
        <section aria-labelledby="order-progress-heading"
            class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5">
            <h3 id="order-progress-heading" class="sr-only">Proses Verifikasi Pesanan</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative">
                <!-- Step 1: Order Dibuat -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-[12px] bg-green-50 dark:bg-green-900/30 border border-green-200/60 dark:border-green-800/60 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <div
                            class="text-[10px] font-mono text-[#34C759] uppercase tracking-wider font-semibold">
                            Langkah 1</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">Pesanan Dibuat</div>
                    </div>
                </div>

                <!-- Step 2: Transfer Nominal -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-[12px] {{ $payment->isPending() ? 'bg-amber-50 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 text-[#FF9500]' : 'bg-green-50 dark:bg-green-900/30 border border-green-200/60 dark:border-green-800/60 text-[#34C759]' }} flex items-center justify-center shrink-0">
                        @if ($payment->isPending())
                            <i data-lucide="credit-card" class="w-4 h-4" aria-hidden="true"></i>
                        @else
                            <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div
                            class="text-[10px] font-mono {{ $payment->isPending() ? 'text-[#FF9500]' : 'text-[#34C759]' }} uppercase tracking-wider font-semibold">
                            Langkah 2</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">Transfer Dana</div>
                    </div>
                </div>

                <!-- Step 3: Verifikasi Bukti -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-[12px] {{ $payment->isAwaitingApproval() ? 'bg-blue-50 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-700 text-[#007AFF]' : ($payment->isApproved() ? 'bg-green-50 dark:bg-green-900/30 border border-green-200/60 dark:border-green-800/60 text-[#34C759]' : 'bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-gray-400') }} flex items-center justify-center shrink-0">
                        @if ($payment->isApproved())
                            <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                        @elseif($payment->isAwaitingApproval())
                            <i data-lucide="hourglass" class="w-4 h-4" aria-hidden="true"></i>
                        @else
                            <i data-lucide="file-check" class="w-4 h-4" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div
                            class="text-[10px] font-mono {{ $payment->isAwaitingApproval() ? 'text-[#007AFF]' : ($payment->isApproved() ? 'text-[#34C759]' : 'text-gray-400') }} uppercase tracking-wider font-semibold">
                            Langkah 3</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">Verifikasi Admin</div>
                    </div>
                </div>

                <!-- Step 4: Paket Aktif -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-[12px] {{ $payment->isApproved() ? 'bg-green-50 dark:bg-green-900/30 border border-green-200/60 dark:border-green-800/60 text-[#34C759]' : 'bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-gray-400' }} flex items-center justify-center shrink-0">
                        <i data-lucide="sparkles" class="w-4 h-4" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <div
                            class="text-[10px] font-mono {{ $payment->isApproved() ? 'text-[#34C759]' : 'text-gray-400' }} uppercase tracking-wider font-semibold">
                            Langkah 4</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">Aktivasi Paket</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Alert Banners Based on Current Order State -->
        @if ($payment->isRejected())
            <section aria-labelledby="status-rejected-heading"
                class="p-5 sm:p-6 rounded-[20px] bg-red-50/60 dark:bg-red-950/30 border border-red-200/80 dark:border-red-800/60 flex flex-col sm:flex-row items-start gap-5">
                <div class="w-11 h-11 rounded-[12px] bg-red-100 dark:bg-red-900/40 text-[#FF3B30] flex items-center justify-center shrink-0"
                    aria-hidden="true">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-mono font-bold uppercase tracking-wider text-[#FF3B30]">Pemberitahuan
                            Status Tagihan</span>
                        <span
                            class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">{{ $payment->rejected_at?->format('d M Y H:i') }}</span>
                    </div>
                    <h4 id="status-rejected-heading" class="font-bold text-black dark:text-white text-base">
                        Pembayaran Belum Dapat Diverifikasi</h4>
                    <div
                        class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-300">
                        <strong class="text-black dark:text-white">Alasan:</strong>
                        {{ $payment->admin_notes ?? 'Nominal transfer tidak sesuai dengan kode unik atau mutasi bank belum teridentifikasi.' }}
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Anda dapat melakukan transfer ulang atau mengunggah foto struk transfer yang benar melalui formulir di bawah.
                    </p>
                </div>
            </section>
        @endif

        @if ($payment->isAwaitingApproval())
            <section aria-labelledby="status-awaiting-heading"
                class="p-5 sm:p-6 rounded-[20px] bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/60 flex flex-col sm:flex-row items-start gap-5">
                <div class="w-11 h-11 rounded-[12px] bg-blue-100 dark:bg-blue-900/40 text-[#007AFF] flex items-center justify-center shrink-0"
                    aria-hidden="true">
                    <i data-lucide="hourglass" class="w-6 h-6"></i>
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-mono font-bold uppercase tracking-wider text-[#007AFF]">Sedang Diproses</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">Estimasi: 5–15 Menit</span>
                    </div>
                    <h4 id="status-awaiting-heading" class="font-bold text-black dark:text-white text-base">Bukti
                        Pembayaran Berhasil Diterima</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Bukti transfer diterima pada <strong
                            class="text-black dark:text-white font-mono">{{ $payment->proof_uploaded_at?->format('d M Y, H:i') }}
                            WIB</strong>. Tim Billing Cooca sedang memverifikasi mutasi rekening. Akses paket akan terbuka otomatis setelah disetujui.
                    </p>
                </div>
            </section>
        @endif

        @if ($payment->isApproved())
            <section aria-labelledby="status-approved-heading"
                class="p-5 sm:p-6 rounded-[20px] bg-green-50/60 dark:bg-green-950/30 border border-green-200/80 dark:border-green-800/60 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-[12px] bg-green-100 dark:bg-green-900/40 text-[#34C759] flex items-center justify-center shrink-0"
                        aria-hidden="true">
                        <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-mono font-bold uppercase tracking-wider text-[#34C759]">Pembayaran Sah</span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">Disetujui
                                {{ $payment->approved_at?->format('d M Y H:i') }}</span>
                        </div>
                        <h4 id="status-approved-heading"
                            class="font-bold text-black dark:text-white text-base sm:text-lg">Paket Cooca Anda Telah Aktif</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            Fitur paket <strong
                                class="text-black dark:text-white">{{ $payment->package_name ?? 'Cooca' }}</strong>
                            kini aktif hingga <strong
                                class="text-black dark:text-white font-mono">{{ $payment->business->subscription?->ends_at?->format('d M Y') ?? 'Masa Berlaku Aktif' }}</strong>.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full sm:w-auto shrink-0">
                    <a href="{{ route('dashboard') }}"
                        class="w-full sm:w-auto h-11 px-5 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                        <span>Buka Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('billing.limits') }}"
                        class="w-full sm:w-auto h-11 px-4 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.04] dark:hover:bg-white/[0.08] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                        <i data-lucide="gauge" class="w-4 h-4 text-gray-400" aria-hidden="true"></i>
                        <span>Cek Kuota</span>
                    </a>
                    <a href="{{ route('billing.payment.invoice', $payment) }}" target="_blank"
                        class="w-full sm:w-auto h-11 px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] hover:bg-blue-50 dark:hover:bg-blue-900/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
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
                <section aria-labelledby="nominal-transfer-heading"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 relative overflow-hidden">

                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div>
                            <span
                                class="text-[10px] font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400 font-semibold block mb-1">
                                {{ ($payment->isTripay() || $payment->unique_code === 0) ? 'Total Tagihan TriPay Gateway' : 'Total Pembayaran Transfer' }}
                            </span>
                            <h3 id="nominal-transfer-heading" class="text-xs text-gray-600 dark:text-gray-300">
                                {{ ($payment->isTripay() || $payment->unique_code === 0) ? 'Nominal resmi yang diterbitkan untuk transaksi ini:' : 'Wajib transfer presisi tepat hingga 3 digit terakhir:' }}
                            </h3>
                        </div>
                        @if($payment->isTripay() || $payment->unique_code === 0)
                            <span class="text-xs font-semibold text-[#34C759] font-mono flex items-center gap-1">
                                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                <span>Verifikasi Instan</span>
                            </span>
                        @else
                            <span class="text-xs font-semibold text-[#FF9500] font-mono">
                                Kode Unik Aktif
                            </span>
                        @endif
                    </div>

                    <!-- Big Currency Display with 1-Click Copy Action -->
                    <div class="py-5 flex flex-col sm:flex-row sm:items-baseline justify-between gap-4">
                        <div>
                            <div
                                class="text-3xl sm:text-4xl font-bold font-mono tabular-nums tracking-tight text-black dark:text-white flex items-baseline gap-1">
                                <span class="text-gray-400 text-xl font-sans font-medium">Rp</span>
                                <span>{{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-3.5 h-3.5 {{ ($payment->isTripay() || $payment->unique_code === 0) ? 'text-[#007AFF]' : 'text-[#FF9500]' }}"
                                    aria-hidden="true"></i>
                                <span>Batas waktu pembayaran s/d {{ $payment->gateway_expired_at ? $payment->gateway_expired_at->format('d M Y, H:i') . ' WIB' : '24 Jam' }}</span>
                            </div>
                        </div>

                        <!-- 1-Click Copy Nominal Clean Button -->
                        <div class="flex items-center gap-2">
                            <button type="button"
                                @click="copyToClipboard('{{ (int) $payment->total_payable }}', 'nominal')"
                                class="h-11 px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-blue-50/60 hover:bg-blue-100/80 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/60 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 focus-visible:ring-2 focus-visible:ring-[#007AFF]"
                                aria-label="Salin Angka Bersih Nominal Transfer">
                                <i data-lucide="copy" class="w-4 h-4" x-show="copiedText !== 'nominal'"
                                    aria-hidden="true"></i>
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759]"
                                    x-show="copiedText === 'nominal'" x-cloak aria-hidden="true"></i>
                                <span
                                    x-text="copiedText === 'nominal' ? 'Nominal Tersalin!' : 'Salin Angka Bersih'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Nominal Breakdown Box -->
                    <div
                        class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5 font-mono">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400 font-sans">Harga Paket Dasar:</span>
                            <span class="font-bold tabular-nums text-black dark:text-white">Rp
                                {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                        @if($payment->gateway_fee > 0)
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-black/[0.04] dark:border-white/[0.04]">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">Biaya Layanan Gateway:</span>
                                <span class="font-bold tabular-nums text-black dark:text-white">Rp
                                    {{ number_format($payment->gateway_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(!$payment->isTripay() && $payment->unique_code > 0)
                            <div
                                class="flex items-center justify-between text-xs pt-1 border-t border-black/[0.04] dark:border-white/[0.04]">
                                <div class="flex items-center gap-1.5 font-sans">
                                    <span class="text-[#FF9500] font-semibold">Kode Verifikasi Unik:</span>
                                    <span class="text-[10px] text-gray-400">(Otomatisasi)</span>
                                </div>
                                <span
                                    class="font-bold tabular-nums text-[#FF9500] bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-[6px] border border-amber-200/60 dark:border-amber-800/60">
                                    +{{ $uniqueStr }}
                                </span>
                            </div>
                        @endif
                        <div
                            class="flex items-center justify-between text-xs pt-1 border-t border-black/[0.06] dark:border-white/[0.08] font-bold">
                            <span class="text-black dark:text-white font-sans">Total Tagihan Final:</span>
                            <span class="text-[#007AFF] dark:text-[#0A84FF] font-bold tabular-nums text-sm">Rp
                                {{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Notice Card -->
                    @if($payment->isTripay() || $payment->unique_code === 0)
                        <div
                            class="mt-4 p-3.5 rounded-[14px] bg-blue-50/60 dark:bg-blue-900/20 border border-blue-200/60 dark:border-blue-800/40 flex items-start gap-3">
                            <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"
                                aria-hidden="true"></i>
                            <p class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed">
                                <strong class="font-semibold text-black dark:text-white">TriPay Gateway Terverifikasi:</strong> Transaksi ini terhubung secara otomatis ke sistem perbankan nasional. Begitu Anda menyelesaikan transfer atau scan QR, paket akan langsung aktif tanpa perlu konfirmasi manual.
                            </p>
                        </div>
                    @else
                        <div
                            class="mt-4 p-3.5 rounded-[14px] bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 flex items-start gap-3">
                            <i data-lucide="info" class="w-4 h-4 text-[#FF9500] shrink-0 mt-0.5"
                                aria-hidden="true"></i>
                            <p class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed">
                                <strong class="font-semibold text-black dark:text-white">Penting:</strong> Mohon transfer dengan jumlah yang <strong
                                    class="text-[#FF9500] font-bold">PERSIS</strong> hingga 3 digit terakhir
                                (<span
                                    class="font-mono font-bold text-[#FF9500]">{{ $uniqueStr }}</span>) untuk mempercepat pencocokan mutasi rekening Anda secara otomatis.
                            </p>
                        </div>
                    @endif
                </section>

                <!-- Card: Rekening Tujuan Transfer -->
                <section aria-labelledby="destination-account-heading"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                    <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4 flex items-center justify-between">
                        <div>
                            <span
                                class="text-[10px] font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400 font-semibold block mb-1">Rekening
                                Tujuan Resmi</span>
                            <h3 id="destination-account-heading"
                                class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF]"
                                    aria-hidden="true"></i>
                                <span>{{ $methodDetails['bank_name'] }}</span>
                            </h3>
                        </div>
                        <span class="text-xs font-mono font-semibold uppercase text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_method }}
                        </span>
                    </div>

                    <!-- Account Number Display with Copy Action -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ ($payment->isTripay()) ? (str_contains(strtolower($methodDetails['bank_name']), 'gerai') || str_contains(strtolower($payment->payment_method), 'alfa') || str_contains(strtolower($payment->payment_method), 'indo') ? 'Kode Pembayaran Kasir Gerai:' : 'Nomor Virtual Account:') : 'Nomor Rekening Tujuan:' }}
                        </label>
                        <div
                            class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <div
                                    class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-black dark:text-white tracking-wider">
                                    {{ $payment->gateway_pay_code ?: $methodDetails['account_number'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    a/n <span
                                        class="font-semibold text-black dark:text-white">{{ $methodDetails['account_name'] }}</span>
                                </div>
                            </div>

                            @if (($methodDetails['type'] ?? '') !== 'qris')
                                <button type="button"
                                    @click="copyToClipboard('{{ str_replace(['-', ' '], '', $payment->gateway_pay_code ?: $methodDetails['account_number']) }}', 'rekening')"
                                    class="h-10 px-3.5 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.04] dark:hover:bg-white/[0.08] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 shrink-0 focus-visible:ring-2 focus-visible:ring-[#007AFF]"
                                    aria-label="Salin Nomor Rekening Tujuan">
                                    <i data-lucide="copy" class="w-3.5 h-3.5" x-show="copiedText !== 'rekening'"
                                        aria-hidden="true"></i>
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]"
                                        x-show="copiedText === 'rekening'" x-cloak aria-hidden="true"></i>
                                    <span
                                        x-text="copiedText === 'rekening' ? 'Nomor Tersalin!' : 'Salin Nomor'"></span>
                                </button>
                            @endif
                        </div>
                        @if(!empty($payment->gateway_pay_url))
                            <div class="pt-2">
                                <a href="{{ $payment->gateway_pay_url }}" target="_blank"
                                    class="h-10 px-4 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition inline-flex items-center gap-2 shadow-xs cursor-pointer">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    <span>Buka Halaman Pembayaran TriPay</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- QRIS Visual Card if QRIS selected -->
                    @if (($methodDetails['type'] ?? '') === 'qris' || $payment->payment_method === 'qris')
                        <div
                            class="p-5 rounded-[16px] bg-white border border-black/[0.06] text-slate-950 text-center space-y-4 shadow-xs">
                            <div class="flex items-center justify-between border-b border-black/[0.06] pb-3">
                                <div class="text-left">
                                    <span
                                        class="text-[9px] font-bold tracking-widest text-gray-500 uppercase block font-mono">STANDAR
                                        PEMBAYARAN NASIONAL</span>
                                    <span class="text-sm font-bold text-black font-sans tracking-tight">QRIS COOCA PAY
                                        INDONESIA</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-[6px] bg-green-50 text-[#34C759] text-[10px] font-bold font-mono">NMID:
                                    ID1020304050</span>
                            </div>

                            <div
                                class="p-4 bg-gray-50 rounded-[14px] border border-dashed border-gray-300 inline-block">
                                @if (!empty($payment->gateway_qr_url))
                                    <img src="{{ $payment->gateway_qr_url }}" alt="QRIS Dinamis TriPay"
                                        class="w-56 h-56 object-contain mx-auto rounded-[8px]">
                                    <div class="mt-2 text-center">
                                        <a href="{{ $payment->gateway_qr_url }}" download="qris-cooca-{{ $payment->order_number }}.png" target="_blank"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#007AFF] hover:underline">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                            <span>Unduh Gambar QRIS</span>
                                        </a>
                                    </div>
                                @elseif (!empty($methodDetails['qr_image_url']))
                                    <img src="{{ $methodDetails['qr_image_url'] }}" alt="QRIS QR Code"
                                        class="w-56 h-56 object-contain mx-auto rounded-[8px]">
                                @else
                                    <div class="w-56 h-56 flex flex-col items-center justify-center text-center p-3">
                                        <i data-lucide="qr-code" class="w-36 h-36 text-black mx-auto"
                                            aria-hidden="true"></i>
                                        <span
                                            class="text-[10px] font-mono font-bold text-gray-700 mt-2 block">{{ $methodDetails['account_number'] }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1 text-xs text-gray-600 max-w-sm mx-auto">
                                <p class="font-bold text-black">Mendukung Seluruh Aplikasi Perbankan &amp; e-Wallet</p>
                                <p class="text-[11px] text-gray-500">BCA, Mandiri Livin, BRImo, BNI+, GoPay, OVO, Dana,
                                    ShopeePay, LinkAja.</p>
                                @if(!$payment->isPaid() && ($payment->isTripay() || $payment->payment_method === 'qris'))
                                    <div class="pt-1 flex items-center justify-center gap-1.5 text-[11px] text-[#007AFF] font-bold">
                                        <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                                        <span>Menunggu Pembayaran (Auto-Verifikasi Instan)</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Payment Guide / Instructions -->
                    @if (!empty($methodDetails['instructions']))
                        <div
                            class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-xs space-y-1">
                            <div class="flex items-center gap-2 font-semibold text-black dark:text-white">
                                <i data-lucide="help-circle" class="w-4 h-4 text-[#007AFF]"
                                    aria-hidden="true"></i>
                                <span>Instruksi Transfer:</span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 leading-relaxed text-[11px]">
                                {{ $methodDetails['instructions'] }}</p>
                        </div>
                    @endif

                    @if(!$payment->isPaid() && ($payment->isTripay() || $payment->payment_method === 'qris'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const pollInterval = setInterval(async () => {
                                    try {
                                        const res = await fetch("{{ route('billing.payment.status', $payment) }}", {
                                            headers: { 'Accept': 'application/json' }
                                        });
                                        if (res.ok) {
                                            const data = await res.json();
                                            if (data.is_paid) {
                                                clearInterval(pollInterval);
                                                window.location.reload();
                                            }
                                        }
                                    } catch(e) {}
                                }, 4000);
                            });
                        </script>
                    @endif

                    <!-- WhatsApp Support Callout -->
                    <div
                        class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Ada kendala dalam pembayaran?</span>
                        <a href="https://wa.me/?text=Halo%20Admin%20Cooca,%20saya%20butuh%20konfirmasi%20pembayaran%20pesanan%20nomor%20{{ $payment->order_number }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-[#007AFF] hover:underline font-semibold transition">
                            <i data-lucide="message-circle" class="w-4 h-4" aria-hidden="true"></i>
                            <span>Hubungi WhatsApp Billing CS</span>
                        </a>
                    </div>
                </section>

            </div>            <!-- Right Column: Proof Upload Form OR Status Showcase (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

                @if ($payment->isTripay() && $payment->isPending())
                    <!-- TriPay Live Gateway Status Card -->
                    <section aria-labelledby="tripay-status-heading"
                        class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                        <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4 flex items-center justify-between">
                            <div>
                                <h3 id="tripay-status-heading"
                                    class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="zap" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                    <span>Gateway Otomatis TriPay</span>
                                </h3>
                                <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Verifikasi instan tanpa perlu menunggu verifikasi admin manual.
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase font-mono bg-blue-50 text-[#007AFF] dark:bg-blue-950/40 dark:text-[#0A84FF] border border-blue-200/60 dark:border-blue-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-ping"></span>
                                Auto-Settle
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Nomor Referensi:</span>
                                    <span class="font-mono font-bold text-black dark:text-white">{{ $payment->gateway_reference ?: '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Metode Bayar:</span>
                                    <span class="font-semibold text-black dark:text-white uppercase">{{ strtoupper($payment->payment_method) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Batas Waktu Bayar:</span>
                                    <span class="font-mono text-amber-600 dark:text-amber-400 font-semibold">
                                        {{ $payment->gateway_expired_at ? $payment->gateway_expired_at->format('d M Y, H:i') . ' WIB' : ($payment->created_at ? $payment->created_at->addHours(24)->format('d M Y, H:i') . ' WIB' : '-') }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/50 dark:border-blue-800/30 text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" aria-hidden="true"></i>
                                    <span>Setelah transfer berhasil dilakukan via Bank atau Scan QRIS, gateway TriPay akan mengirim notifikasi webhook dan langganan Cooca Anda aktif seketika.</span>
                                </div>
                            </div>

                            <!-- Manual check status button & feedback -->
                            <div class="space-y-2 pt-1">
                                <button type="button" @click="checkPaymentStatus()" :disabled="checkingStatus"
                                    class="w-full h-11 rounded-[14px] text-xs sm:text-sm font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60">
                                    <span x-show="checkingStatus" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full" aria-hidden="true"></span>
                                    <i data-lucide="refresh-cw" class="w-4 h-4" x-show="!checkingStatus" aria-hidden="true"></i>
                                    <span x-text="checkingStatus ? 'Memeriksa Status Pembayaran...' : 'Cek Status Pembayaran Sekarang'"></span>
                                </button>
                                <div x-show="checkStatusFeedback" x-cloak
                                    class="p-2.5 rounded-[10px] text-center text-xs font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white"
                                    x-text="checkStatusFeedback"></div>
                            </div>
                        </div>
                    </section>
                @endif

                <section aria-labelledby="confirmation-heading"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">

                    <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4 flex items-center justify-between">
                        <div>
                            <h3 id="confirmation-heading"
                                class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="receipt-text" class="w-4 h-4 text-[#007AFF]"
                                    aria-hidden="true"></i>
                                <span>Konfirmasi Bukti Transfer</span>
                            </h3>
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-1">
                                @if ($payment->isPending() || $payment->isRejected())
                                    @if ($payment->isTripay())
                                        (Opsional) Unggah struk hanya jika Anda membutuhkan konfirmasi manual dari tim billing Cooca.
                                    @else
                                        Unggah foto struk ATM, tangkapan layar m-banking, atau berkas bukti transfer Anda.
                                    @endif
                                @else
                                    Rincian berkas bukti pembayaran yang telah Anda kirimkan untuk pesanan ini.
                                @endif
                            </p>
                        </div>
                        @if ($payment->isTripay() && ($payment->isPending() || $payment->isRejected()))
                            <button type="button" @click="showManualUpload = !showManualUpload"
                                class="text-xs font-semibold text-[#007AFF] hover:underline cursor-pointer flex items-center gap-1 shrink-0">
                                <span x-text="showManualUpload ? 'Sembunyikan' : 'Buka Formulir'"></span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="showManualUpload ? 'rotate-180' : ''"></i>
                            </button>
                        @endif
                    </div>

                    @if ($payment->isPending() || $payment->isRejected())
                        @if (\App\Support\Context::hasPermission('billing.manage'))
                            <!-- Upload Form with Validation and Double-Submit Prevention -->
                            <div x-show="showManualUpload" x-transition>
                                <form method="POST" action="{{ route('billing.payment.upload', $payment) }}"
                                    enctype="multipart/form-data" @submit="isUploading = true" class="space-y-4">
                                    @csrf

                                    <!-- Error Alert from Client-Side Validation -->
                                    <div x-show="fileError" x-cloak
                                        class="p-3.5 rounded-[12px] bg-red-50/60 dark:bg-red-950/20 border border-red-200/60 dark:border-red-800/40 text-[#FF3B30] text-xs flex items-start gap-2">
                                        <i data-lucide="alert-circle"
                                            class="w-4 h-4 text-[#FF3B30] shrink-0 mt-0.5"
                                            aria-hidden="true"></i>
                                        <span x-text="fileError"></span>
                                    </div>

                                    <!-- Drag & Drop File Input Zone -->
                                    <div class="space-y-1.5">
                                        <label for="payment_proof_input"
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Berkas Struk Transfer <span class="text-[#FF3B30]">*</span>
                                        </label>

                                        <div class="relative group" @dragover.prevent="isDragging = true"
                                            @dragleave.prevent="isDragging = false" @drop.prevent="onDrop($event)">

                                            <input type="file" name="payment_proof" id="payment_proof_input" required
                                                accept="image/jpeg,image/png,image/webp,application/pdf"
                                                @change="onProofChange($event)"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                aria-label="Upload Berkas Bukti Transfer">

                                            <!-- Visual Placeholder Dropzone -->
                                            <div class="p-6 rounded-[16px] border-2 border-dashed transition-all text-center space-y-2"
                                                :class="isDragging ? 'border-[#007AFF] bg-blue-50/30 dark:bg-blue-900/10' :
                                                    'border-black/[0.08] dark:border-white/[0.1] group-hover:border-[#007AFF] bg-black/[0.01] dark:bg-white/[0.02]'">
                                                <div class="w-12 h-12 rounded-[12px] bg-white dark:bg-[#2C2C2E] group-hover:bg-blue-50 dark:group-hover:bg-blue-900/30 text-gray-400 group-hover:text-[#007AFF] flex items-center justify-center mx-auto transition shadow-xs"
                                                    aria-hidden="true">
                                                    <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                                </div>
                                                <div class="text-xs text-gray-700 dark:text-gray-300 font-medium">
                                                    <span
                                                        class="text-[#007AFF] underline decoration-[#007AFF]/40 font-semibold">Pilih
                                                        berkas</span> atau geser ke sini
                                                </div>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">JPG, PNG,
                                                    WEBP, atau PDF (Maks. 5 MB)</p>
                                            </div>
                                        </div>

                                        <!-- Live File Preview Box with Remove Action -->
                                        <div x-show="proofPreview" x-cloak
                                            class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3 mt-2">
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2 truncate">
                                                    <i data-lucide="file-text"
                                                        class="w-4 h-4 text-[#007AFF] shrink-0"
                                                        aria-hidden="true"></i>
                                                    <span
                                                        class="font-mono text-black dark:text-white truncate font-semibold"
                                                        x-text="fileName"></span>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400"
                                                        x-text="fileSize"></span>
                                                    <button type="button" @click="clearFile()"
                                                        class="p-1 rounded-[8px] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] text-gray-400 hover:text-[#FF3B30] transition cursor-pointer"
                                                        title="Hapus Berkas">
                                                        <i data-lucide="x" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <template x-if="proofPreview && proofPreview !== 'pdf'">
                                                <div
                                                    class="rounded-[10px] overflow-hidden border border-black/[0.06] dark:border-white/[0.08] bg-black/5 dark:bg-black/40">
                                                    <img :src="proofPreview" alt="Preview Struk Pembayaran"
                                                        class="max-h-48 w-full object-contain mx-auto">
                                                </div>
                                            </template>
                                            <template x-if="proofPreview === 'pdf'">
                                                <div
                                                    class="p-4 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] text-center flex items-center justify-center gap-2 text-xs text-gray-700 dark:text-gray-300 font-mono">
                                                    <i data-lucide="file" class="w-4 h-4 text-[#FF3B30]"
                                                        aria-hidden="true"></i>
                                                    <span>Dokumen PDF Siap Dikirim</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Quick Bank Name Selector (iOS Safari Anti Auto-Zoom: text-[16px] sm:text-[14px]) -->
                                    <div class="space-y-1.5">
                                        <label for="sender_bank_input"
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Bank / e-Wallet
                                            Pengirim</label>
                                        <input type="text" id="sender_bank_input" name="sender_bank"
                                            x-model="selectedQuickBank" placeholder="Contoh: BCA, Mandiri, BRI, GoPay, OVO"
                                            value="{{ old('sender_bank', $payment->sender_bank) }}"
                                            class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 py-2.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition">

                                        <!-- Quick bank chips -->
                                        <div class="flex flex-wrap gap-1.5 pt-1" aria-label="Pilihan Cepat Bank">
                                            <button type="button" @click="selectedQuickBank = 'BCA'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">BCA</button>
                                            <button type="button" @click="selectedQuickBank = 'Mandiri'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">Mandiri</button>
                                            <button type="button" @click="selectedQuickBank = 'BRI'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">BRI</button>
                                            <button type="button" @click="selectedQuickBank = 'BNI'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">BNI</button>
                                            <button type="button" @click="selectedQuickBank = 'GoPay'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">GoPay</button>
                                            <button type="button" @click="selectedQuickBank = 'OVO'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">OVO</button>
                                            <button type="button" @click="selectedQuickBank = 'Dana'"
                                                class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-[11px] font-mono font-semibold transition cursor-pointer active:scale-[0.98]">Dana</button>
                                        </div>
                                    </div>

                                    <!-- Sender Account Name (Required, text-[16px] sm:text-[14px]) -->
                                    <div class="space-y-1.5">
                                        <label for="sender_account_name_input"
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Nama Pemilik Rekening Pengirim <span class="text-[#FF3B30]">*</span>
                                        </label>
                                        <input type="text" id="sender_account_name_input" name="sender_account_name"
                                            required placeholder="Nama sesuai buku rekening atau profil m-banking"
                                            value="{{ old('sender_account_name', $payment->sender_account_name) }}"
                                            class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 py-2.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition">
                                    </div>

                                    <!-- Sender Account Number (Optional, text-[16px] sm:text-[14px]) -->
                                    <div class="space-y-1.5">
                                        <label for="sender_account_number_input"
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Nomor Rekening / No HP Pengirim <span
                                                class="text-gray-400 text-[11px] font-normal">(Opsional)</span>
                                        </label>
                                        <input type="text" id="sender_account_number_input" name="sender_account_number"
                                            placeholder="Contoh: 1234567890 / 08123456789"
                                            value="{{ old('sender_account_number', $payment->sender_account_number) }}"
                                            class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 py-2.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition font-mono">
                                    </div>

                                    <!-- Notes (Optional, text-[16px] sm:text-[14px]) -->
                                    <div class="space-y-1.5">
                                        <label for="notes_input"
                                            class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Catatan Tambahan <span
                                                class="text-gray-400 text-[11px] font-normal">(Opsional)</span>
                                        </label>
                                        <textarea id="notes_input" name="notes" rows="2" placeholder="Catatan untuk tim verifikasi billing Cooca..."
                                            class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 py-2.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition">{{ old('notes', $payment->notes) }}</textarea>
                                    </div>

                                    <!-- Submit CTA Button with Double-Submit Prevention -->
                                    <button type="submit" :disabled="isUploading"
                                        class="w-full h-12 rounded-[14px] text-[15px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm transition-all flex items-center justify-center gap-2 group disabled:opacity-60 disabled:cursor-not-allowed focus-visible:ring-2 focus-visible:ring-[#007AFF]">
                                        <span x-show="isUploading"
                                            class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                                            aria-hidden="true"></span>
                                        <i data-lucide="send" class="w-4 h-4"
                                            x-show="!isUploading" aria-hidden="true"></i>
                                        <span
                                            x-text="isUploading ? 'Mengunggah Bukti Pembayaran...' : 'Kirim Konfirmasi Pembayaran'"></span>
                                    </button>
                                </form>
                            </div>
                            <div x-show="!showManualUpload" class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] text-center text-xs text-gray-500">
                                Formulir upload disembunyikan karena transaksi menggunakan verifikasi otomatis TriPay.
                            </div>
                        @else
                            <div
                                class="p-6 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-gray-600 dark:text-gray-400 text-xs text-center space-y-2">
                                <i data-lucide="lock" class="w-8 h-8 text-[#FF9500] mx-auto" aria-hidden="true"></i>
                                <div class="font-bold text-black dark:text-white">Akses Terbatas</div>
                                <p>Anda hanya memiliki hak akses melihat tagihan (read-only). Hubungi Owner untuk mengunggah
                                    bukti pembayaran pesanan ini.</p>
                            </div>
                        @endif
                    @else
                        <!-- Preview Uploaded State (Awaiting Approval or Approved) -->
                        <div class="space-y-4 text-xs">
                            <div
                                class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                                <div
                                    class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.04]">
                                    <span class="text-gray-500 dark:text-gray-400">Pengirim:</span>
                                    <span
                                        class="font-semibold text-black dark:text-white font-mono">{{ $payment->sender_account_name }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.04]">
                                    <span class="text-gray-500 dark:text-gray-400">Saluran Bank:</span>
                                    <span
                                        class="font-semibold text-black dark:text-white">{{ $payment->sender_bank ?: '-' }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.04]">
                                    <span class="text-gray-500 dark:text-gray-400">Waktu Kirim:</span>
                                    <span
                                        class="font-mono text-gray-700 dark:text-gray-300">{{ $payment->proof_uploaded_at?->format('d M Y, H:i') }}
                                        WIB</span>
                                </div>
                                @if ($payment->notes)
                                    <div class="pt-1">
                                        <span class="text-gray-500 dark:text-gray-400 block mb-1">Catatan:</span>
                                        <p
                                            class="text-gray-700 dark:text-gray-300 italic text-[11px] bg-white dark:bg-[#2C2C2E] p-2.5 rounded-[10px] border border-black/[0.06] dark:border-white/[0.08]">
                                            {{ $payment->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($payment->payment_proof_path)
                                <div
                                    class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-center space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider font-mono">Lampiran
                                            Struk Transfer</span>
                                        <a href="{{ $payment->getProofUrl() }}" target="_blank"
                                            class="text-xs text-[#007AFF] hover:underline inline-flex items-center gap-1 font-semibold">
                                            <span>Buka Berkas</span>
                                            <i data-lucide="external-link" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                    <div
                                        class="rounded-[10px] overflow-hidden border border-black/[0.06] dark:border-white/[0.08] bg-white dark:bg-black/50 p-2">
                                        <a href="{{ $payment->getProofUrl() }}" target="_blank"
                                            class="block group overflow-hidden rounded-[8px]">
                                            <img src="{{ $payment->getProofUrl() }}" alt="Bukti Transfer Struk"
                                                class="max-h-64 mx-auto object-contain group-hover:scale-102 transition-transform duration-300">
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div
                                class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-gray-500 dark:text-gray-400 text-[11px] leading-relaxed space-y-1">
                                <div class="flex items-center gap-1.5 font-semibold text-black dark:text-white">
                                    <i data-lucide="lock" class="w-3.5 h-3.5 text-[#34C759]"
                                        aria-hidden="true"></i>
                                    <span>Verifikasi Terproteksi</span>
                                </div>
                                <p>Dokumen bukti transfer tersimpan dengan aman di server Cooca dan hanya dapat diakses oleh
                                    tim billing berwenang.</p>
                            </div>
                        </div>
                    @endif

                </section>

            </div>

        </div>

    </div>
@endsection
