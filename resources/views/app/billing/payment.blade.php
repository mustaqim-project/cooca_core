@extends('layouts.app', [
    'title' => 'Pembayaran ' . $payment->order_number . ' - Cooca',
    'headerTitle' => 'Instruksi Pembayaran TriPay Gateway',
    'headerSubtitle' => 'Selesaikan pembayaran via TriPay Payment Gateway. Paket aktif otomatis seketika tanpa perlu kirim bukti bayar.',
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
        checkingStatus: false,
        checkStatusFeedback: '',
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
                    setTimeout(() => window.location.reload(), 800);
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
                    Selesaikan pembayaran via TriPay Gateway resmi di bawah. Paket Anda akan aktif otomatis seketika setelah pembayaran berhasil tanpa verifikasi manual.
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

                <!-- Step 2: Bayar TriPay -->
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
                        <div class="text-xs font-bold text-black dark:text-white truncate">Bayar via TriPay</div>
                    </div>
                </div>

                <!-- Step 3: Verifikasi Otomatis Gateway -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-[12px] {{ $payment->isApproved() ? 'bg-green-50 dark:bg-green-900/30 border border-green-200/60 dark:border-green-800/60 text-[#34C759]' : ($payment->isPending() ? 'bg-blue-50 dark:bg-blue-900/30 border border-blue-300 dark:border-blue-700 text-[#007AFF]' : 'bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-gray-400') }} flex items-center justify-center shrink-0">
                        @if ($payment->isApproved())
                            <i data-lucide="check" class="w-4 h-4" aria-hidden="true"></i>
                        @else
                            <i data-lucide="zap" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div
                            class="text-[10px] font-mono {{ $payment->isApproved() ? 'text-[#34C759]' : 'text-[#007AFF]' }} uppercase tracking-wider font-semibold">
                            Langkah 3</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">Verifikasi Otomatis</div>
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
                        Pembayaran Kedaluwarsa atau Dibatalkan</h4>
                    <div
                        class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-300">
                        <strong class="text-black dark:text-white">Catatan:</strong>
                        {{ $payment->admin_notes ?? 'Batas waktu pembayaran telah habis atau transaksi dibatalkan oleh gateway.' }}
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        Silakan buat pesanan baru melalui halaman paket atau hubungi customer support kami jika dana Anda telah terpotong.
                    </p>
                </div>
            </section>
        @endif

        @if ($payment->isAwaitingApproval())
            <section aria-labelledby="status-awaiting-heading"
                class="p-5 sm:p-6 rounded-[20px] bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/60 flex flex-col sm:flex-row items-start gap-5">
                <div class="w-11 h-11 rounded-[12px] bg-blue-100 dark:bg-blue-900/40 text-[#007AFF] flex items-center justify-center shrink-0"
                    aria-hidden="true">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-mono font-bold uppercase tracking-wider text-[#007AFF]">Sinkronisasi Gateway</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">Proses Otomatis</span>
                    </div>
                    <h4 id="status-awaiting-heading" class="font-bold text-black dark:text-white text-base">Pembayaran Sedang Diverifikasi Gateway TriPay</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Sistem sedang memproses konfirmasi pelunasan dari jaringan perbankan TriPay. Paket langganan Anda akan langsung aktif tanpa perlu konfirmasi manual.
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
                                Total Tagihan TriPay Gateway
                            </span>
                            <h3 id="nominal-transfer-heading" class="text-xs text-gray-600 dark:text-gray-300">
                                Nominal resmi yang diterbitkan untuk transaksi ini:
                            </h3>
                        </div>
                        <span class="text-xs font-semibold text-[#34C759] font-mono flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                            <span>Verifikasi Instan 24/7</span>
                        </span>
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
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#007AFF]"
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
                        <div
                            class="flex items-center justify-between text-xs pt-1 border-t border-black/[0.06] dark:border-white/[0.08] font-bold">
                            <span class="text-black dark:text-white font-sans">Total Tagihan Final:</span>
                            <span class="text-[#007AFF] dark:text-[#0A84FF] font-bold tabular-nums text-sm">Rp
                                {{ number_format($payment->total_payable, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Notice Card -->
                    <div
                        class="mt-4 p-3.5 rounded-[14px] bg-blue-50/60 dark:bg-blue-900/20 border border-blue-200/60 dark:border-blue-800/40 flex items-start gap-3">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"
                            aria-hidden="true"></i>
                        <p class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed">
                            <strong class="font-semibold text-black dark:text-white">TriPay Gateway Terverifikasi:</strong> Transaksi ini terhubung secara otomatis ke sistem perbankan nasional. Begitu Anda menyelesaikan transfer atau scan QR, paket akan langsung aktif seketika tanpa perlu konfirmasi manual atau upload struk.
                        </p>
                    </div>
                </section>

                <!-- Card: Rekening Tujuan Transfer -->
                <section aria-labelledby="destination-account-heading"
                    class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5 sm:p-6 space-y-5">
                    <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4 flex items-center justify-between">
                        <div>
                            <span
                                class="text-[10px] font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400 font-semibold block mb-1">
                                {{ ($payment->isTripay() || $payment->payment_method === 'qris') ? 'Gateway Pembayaran Resmi' : 'Rekening Tujuan Resmi' }}
                            </span>
                            <h3 id="destination-account-heading"
                                class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="{{ ($payment->isTripay() || $payment->payment_method === 'qris') ? 'qr-code' : 'building-2' }}" class="w-4 h-4 text-[#007AFF]"
                                    aria-hidden="true"></i>
                                <span>{{ ($payment->isTripay() || $payment->payment_method === 'qris') ? 'TriPay Gateway (QRIS Nasional)' : $methodDetails['bank_name'] }}</span>
                            </h3>
                        </div>
                        <span class="text-xs font-mono font-semibold uppercase text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_method }}
                        </span>
                    </div>

                    @if(!empty($payment->gateway_pay_url))
                        <div class="p-4 rounded-[14px] bg-[#007AFF]/10 border border-[#007AFF]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="external-link" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>Halaman Checkout TriPay Resmi</span>
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Buka langsung halaman checkout resmi TriPay untuk menyelesaikan pembayaran atau mengunduh QRIS.
                                </div>
                            </div>
                            <a href="{{ $payment->gateway_pay_url }}" target="_blank"
                                class="h-10 px-4 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition inline-flex items-center justify-center gap-2 shadow-sm shrink-0 cursor-pointer">
                                <span>Buka Halaman TriPay</span>
                                <i data-lucide="arrow-up-right" class="w-4 h-4" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endif

                    @if (($methodDetails['type'] ?? '') !== 'qris' && $payment->payment_method !== 'qris')
                        <!-- Account Number Display with Copy Action (Only for Non-QRIS Accounts) -->
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
                            </div>
                        </div>
                    @endif

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

                            <!-- TriPay Channel Disabled / Inactive Alert -->
                            @if (empty($payment->gateway_reference) && !empty($payment->admin_notes) && str_contains($payment->admin_notes, 'TriPay'))
                                <div class="p-4 rounded-[14px] bg-amber-500/10 border border-amber-500/25 text-amber-900 dark:text-amber-200 text-xs text-left space-y-2">
                                    <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300">
                                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                        <span>Saluran Pembayaran TriPay Belum Aktif</span>
                                    </div>
                                    <p class="leading-relaxed">
                                        {{ str_replace(['TriPay Error: ', 'TriPay Exception: '], '', $payment->admin_notes) }}
                                    </p>
                                    <div class="text-[11px] text-amber-800/90 dark:text-amber-300/90 bg-amber-500/10 p-2.5 rounded-[10px] space-y-1">
                                        <div class="font-semibold">Cara Mengaktifkan di Akun TriPay:</div>
                                        <ol class="list-decimal list-inside space-y-0.5">
                                            <li>Buka dasbor TriPay (<a href="https://tripay.co.id" target="_blank" class="underline font-bold text-amber-700 dark:text-amber-200">tripay.co.id</a>)</li>
                                            <li>Pilih menu <strong>Merchant &gt; Saluran Pembayaran</strong></li>
                                            <li>Aktifkan (centang) saluran <strong>{{ strtoupper($payment->payment_method) }}</strong></li>
                                        </ol>
                                    </div>
                                    <button type="button" @click="checkPaymentStatus()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-amber-600 hover:bg-amber-700 text-white font-semibold text-[11px] transition-all cursor-pointer">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="checkingStatus ? 'animate-spin' : ''"></i>
                                        <span>Muat Ulang / Inisialisasi Ulang Tagihan</span>
                                    </button>
                                </div>
                            @endif

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
                                @elseif (!empty($payment->gateway_qr_string))
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data={{ urlencode($payment->gateway_qr_string) }}" alt="QRIS Dinamis TriPay"
                                        class="w-56 h-56 object-contain mx-auto rounded-[8px]">
                                    <div class="mt-2 text-center">
                                        <span class="text-[10px] text-gray-500 font-mono">Dibuat dari kode QRIS resmi TriPay</span>
                                    </div>
                                @elseif (!empty($methodDetails['qr_image_url']))
                                    <img src="{{ $methodDetails['qr_image_url'] }}" alt="QRIS QR Code"
                                        class="w-56 h-56 object-contain mx-auto rounded-[8px]">
                                @else
                                    <div class="w-56 h-56 flex flex-col items-center justify-center text-center p-3">
                                        <i data-lucide="qr-code" class="w-28 h-28 text-black/25 dark:text-white/25 mx-auto"
                                            aria-hidden="true"></i>
                                        <span
                                            class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 mt-2 block">Menunggu Inisialisasi QR TriPay...</span>
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

            </div>            <!-- Right Column: TriPay Real-Time Gateway Status & Auto-Activation (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

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
                                Verifikasi instan seketika tanpa upload bukti struk.
                            </p>
                        </div>
                        @if ($payment->isPaid())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase font-mono bg-green-50 text-[#34C759] dark:bg-green-950/40 dark:text-[#30D158] border border-green-200/60 dark:border-green-800/40">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                Lunas
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase font-mono bg-blue-50 text-[#007AFF] dark:bg-blue-950/40 dark:text-[#0A84FF] border border-blue-200/60 dark:border-blue-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-ping"></span>
                                Auto-Settle
                            </span>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <!-- Key Metadata List -->
                        <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">No. Pesanan:</span>
                                <span class="font-mono font-bold text-black dark:text-white">{{ $payment->order_number }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">Referensi TriPay:</span>
                                <span class="font-mono font-bold text-[#007AFF]">{{ $payment->gateway_reference ?: '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">Metode Bayar:</span>
                                <span class="font-semibold text-black dark:text-white uppercase">{{ strtoupper($payment->payment_method) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">Batas Waktu Bayar:</span>
                                <span class="font-mono text-amber-600 dark:text-amber-400 font-semibold">
                                    {{ $payment->gateway_expired_at ? $payment->gateway_expired_at->format('d M Y, H:i') . ' WIB' : ($payment->created_at ? $payment->created_at->addHours(24)->format('d M Y, H:i') . ' WIB' : '24 Jam') }}
                                </span>
                            </div>
                        </div>

                        <!-- Instant Auto-Verification Explanation -->
                        <div class="p-4 rounded-[14px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/50 dark:border-blue-800/30 text-xs text-blue-950 dark:text-blue-200 leading-relaxed space-y-2">
                            <div class="flex items-center gap-2 font-bold text-[#007AFF]">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0" aria-hidden="true"></i>
                                <span>Verifikasi Otomatis 100% Real-Time</span>
                            </div>
                            <p class="text-[11px] text-gray-600 dark:text-gray-300">
                                Setelah Anda menyelesaikan pembayaran via m-Banking atau Scan QRIS, TriPay akan mengirim konfirmasi otomatis ke sistem Cooca. Paket langsung aktif seketika tanpa perlu menunggu verifikasi admin atau mengunggah bukti bayar.
                            </p>
                        </div>

                        @if (!$payment->isPaid())
                            <!-- Interactive Check Status Button -->
                            <div class="space-y-2 pt-1">
                                <button type="button" @click="checkPaymentStatus()" :disabled="checkingStatus"
                                    class="w-full h-11 rounded-[14px] text-xs sm:text-sm font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60">
                                    <span x-show="checkingStatus" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full" aria-hidden="true"></span>
                                    <i data-lucide="refresh-cw" class="w-4 h-4" x-show="!checkingStatus" aria-hidden="true"></i>
                                    <span x-text="checkingStatus ? 'Memeriksa Mutasi TriPay...' : 'Cek Status Pembayaran Sekarang'"></span>
                                </button>
                                <div x-show="checkStatusFeedback" x-cloak
                                    class="p-2.5 rounded-[10px] text-center text-xs font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white"
                                    x-text="checkStatusFeedback"></div>
                            </div>
                        @else
                            <!-- Paid Success Callout -->
                            <div class="p-4 rounded-[14px] bg-green-50 dark:bg-green-950/30 border border-green-200/60 dark:border-green-800/40 text-center space-y-2">
                                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 text-[#34C759] flex items-center justify-center mx-auto">
                                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                                </div>
                                <div class="font-bold text-sm text-green-900 dark:text-green-200">Pembayaran Sah Terverifikasi</div>
                                <p class="text-xs text-green-700 dark:text-green-300">Paket Cooca Anda telah aktif. Anda dapat langsung menggunakan semua fitur.</p>
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#007AFF] hover:underline pt-1">
                                    <span>Buka Dashboard</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        @endif

                        <!-- Step-by-Step Mini Guide -->
                        <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5 text-xs">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400 font-semibold block">Cara Selesaikan Pembayaran:</span>
                            <ol class="space-y-2 text-[11px] text-gray-600 dark:text-gray-400 list-decimal list-inside leading-relaxed">
                                <li>Buka aplikasi m-Banking, ATM, atau e-Wallet yang Anda gunakan.</li>
                                <li>Pilih menu transfer <strong class="text-black dark:text-white">Virtual Account</strong> atau scan <strong class="text-black dark:text-white">QRIS</strong>.</li>
                                <li>Masukkan kode/nomor sesuai instruksi pada kolom di sebelah kiri.</li>
                                <li>Pastikan nominal pembayaran sesuai sebelum mengonfirmasi PIN.</li>
                                <li>Selesai! Sistem akan mendeteksi pelunasan secara otomatis.</li>
                            </ol>
                        </div>

                        <!-- Historical Proof Preview if existed on legacy orders -->
                        @if ($payment->payment_proof_path)
                            <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-center space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-semibold text-gray-500 font-mono">Arsip Bukti Transfer</span>
                                    <a href="{{ $payment->getProofUrl() }}" target="_blank" class="text-xs text-[#007AFF] hover:underline inline-flex items-center gap-1 font-semibold">
                                        <span>Buka File</span>
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Security & Guarantee Footer -->
                        <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>PCI-DSS &amp; BI Compliant</span>
                            </span>
                            <a href="https://wa.me/?text=Halo%20Admin%20Cooca,%20saya%20butuh%20bantuan%20pembayaran%20TriPay%20pesanan%20nomor%20{{ $payment->order_number }}"
                                target="_blank" class="text-[#007AFF] hover:underline font-semibold flex items-center gap-1">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                <span>Bantuan WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </section>

            </div>

        </div>

    </div>
@endsection
