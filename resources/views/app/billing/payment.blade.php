@extends('layouts.app', [
    'title' => __('billing.payment_page_title', ['order' => $payment->order_number]),
    'headerTitle' => __('billing.header_payment_instructions'),
    'headerSubtitle' => __('billing.header_payment_subtitle'),
])

@section('content')
    <script>
        window.COOCA_I18N = window.COOCA_I18N || {};
        window.COOCA_I18N.billing = {
            payment_verified_success: @json(__('billing.payment_verified_success')),
            checking_gateway: @json(__('billing.checking_gateway')),
            payment_not_detected: @json(__('billing.payment_not_detected')),
            check_failed: @json(__('billing.check_failed')),
            check_status_now: @json(__('billing.check_status_now')),
            nominal_copied: @json(__('billing.nominal_copied')),
            copy_clean_amount: @json(__('billing.copy_clean_amount')),
            action_number_copied: @json(__('billing.action_number_copied')),
            action_copy_number: @json(__('billing.action_copy_number'))
        };
    </script>
    @php
        $badge = $payment->getStatusBadge();
        $methodDetails = $payment->getPaymentMethodDetails();
        $uniqueStr = str_pad((string) $payment->unique_code, 3, '0', STR_PAD_LEFT);
        $bankCode = strtolower($payment->payment_method ?? '');
    @endphp

    <div class="space-y-6 pb-28 lg:pb-10" x-data="{
        copiedText: null,
        isPaid: {{ $payment->isPaid() ? 'true' : 'false' }},
        orderStatus: '{{ $payment->status }}',
        checkingStatus: false,
        checkStatusFeedback: '',
        pollTimer: null,

        init() {
            if (!this.isPaid) {
                this.startAdaptivePolling();
            }
        },

        startAdaptivePolling() {
            const doPoll = async () => {
                if (document.hidden || this.isPaid) return;
                try {
                    const res = await fetch('{{ route('billing.payment.status', $payment) }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.is_paid) {
                            this.isPaid = true;
                            this.orderStatus = 'approved';
                            this.checkStatusFeedback = window.COOCA_I18N?.billing?.payment_verified_success || '{{ __('billing.payment_verified_success') }}';
                            if (this.pollTimer) clearInterval(this.pollTimer);
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        }
                    }
                } catch(e) {}
            };

            this.pollTimer = setInterval(doPoll, 4000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !this.isPaid) doPoll();
            });
        },

        async checkPaymentStatus() {
            this.checkingStatus = true;
            this.checkStatusFeedback = window.COOCA_I18N?.billing?.checking_gateway || '{{ __('billing.checking_gateway') }}';
            try {
                const res = await fetch('{{ route('billing.payment.status', $payment) }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.is_paid) {
                    this.isPaid = true;
                    this.orderStatus = 'approved';
                    this.checkStatusFeedback = window.COOCA_I18N?.billing?.payment_verified_success || '{{ __('billing.payment_verified_success') }}';
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                } else {
                    this.checkStatusFeedback = window.COOCA_I18N?.billing?.payment_not_detected || '{{ __('billing.payment_not_detected') }}';
                    setTimeout(() => this.checkStatusFeedback = '', 4500);
                }
            } catch (e) {
                this.checkStatusFeedback = window.COOCA_I18N?.billing?.check_failed || '{{ __('billing.check_failed') }}';
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

        <!-- Standard 3-Row Module Header Bento Apple HIG -->
        <x-module-header
            :title="__('billing.payment_instruction_title', ['package' => $payment->package_name ?? ($payment->cycle === 'annual' ? __('billing.plan_core_annual') : __('billing.plan_core_monthly'))])"
            :subtitle="__('billing.payment_instruction_subtitle')"
            :breadcrumbs="[
                ['label' => __('billing.breadcrumb_billing'), 'route' => 'billing.limits'],
                ['label' => __('billing.breadcrumb_history'), 'route' => 'billing.history'],
                ['label' => $payment->order_number],
            ]"
            :badge="$payment->order_number"
        >
            <x-slot:actions>
                <a href="{{ route('billing.history') }}"
                    class="h-10 px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>{{ __('billing.action_back') }}</span>
                </a>
                <a href="{{ route('billing.payment.invoice', $payment) }}" target="_blank"
                    class="h-10 px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition flex items-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('billing.action_print_invoice') }}</span>
                </a>
            </x-slot:actions>
        </x-module-header>

        <!-- 2. Lifecycle Workflow Stepper (4-Phase Progress Tracker) -->
        <section aria-labelledby="order-progress-heading"
            class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-5">
            <h3 id="order-progress-heading" class="sr-only">{{ __('billing.order_progress_title') }}</h3>
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
                            {{ __('billing.step_number', ['step' => 1]) }}</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">{{ __('billing.step_1_title') }}</div>
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
                            {{ __('billing.step_number', ['step' => 2]) }}</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">{{ __('billing.step_2_title') }}</div>
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
                            {{ __('billing.step_number', ['step' => 3]) }}</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">{{ __('billing.step_3_title') }}</div>
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
                            {{ __('billing.step_number', ['step' => 4]) }}</div>
                        <div class="text-xs font-bold text-black dark:text-white truncate">{{ __('billing.step_4_title') }}</div>
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
                            class="text-xs font-mono font-bold uppercase tracking-wider text-[#FF3B30]">{{ __('billing.status_notification') }}</span>
                        <span
                            class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">{{ $payment->rejected_at?->format('d M Y H:i') }}</span>
                    </div>
                    <h4 id="status-rejected-heading" class="font-bold text-black dark:text-white text-base">
                        {{ __('billing.payment_expired_cancelled') }}</h4>
                    <div
                        class="p-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-300">
                        <strong class="text-black dark:text-white">{{ __('billing.notes_label') }}</strong>
                        {{ $payment->admin_notes ?? __('billing.expired_default_note') }}
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        {{ __('billing.reorder_guidance') }}
                    </p>
                </div>
            </section>
        @endif

        @if ($payment->isAwaitingApproval())
            <section x-show="!isPaid" aria-labelledby="status-awaiting-heading"
                class="p-5 sm:p-6 rounded-[20px] bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/60 flex flex-col sm:flex-row items-start gap-5">
                <div class="w-11 h-11 rounded-[12px] bg-blue-100 dark:bg-blue-900/40 text-[#007AFF] flex items-center justify-center shrink-0"
                    aria-hidden="true">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-mono font-bold uppercase tracking-wider text-[#007AFF]">{{ __('billing.gateway_sync') }}</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">{{ __('billing.auto_process') }}</span>
                    </div>
                    <h4 id="status-awaiting-heading" class="font-bold text-black dark:text-white text-base">{{ __('billing.awaiting_verification') }}</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ __('billing.processing_gateway_note') }}
                    </p>
                </div>
            </section>
        @endif

        <section aria-labelledby="status-approved-heading" x-show="isPaid" x-cloak
            class="p-5 sm:p-6 rounded-[20px] bg-green-50/60 dark:bg-green-950/30 border border-green-200/80 dark:border-green-800/60 flex flex-col sm:flex-row items-center justify-between gap-6 transition-all duration-300">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-[12px] bg-green-100 dark:bg-green-900/40 text-[#34C759] flex items-center justify-center shrink-0"
                    aria-hidden="true">
                    <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-mono font-bold uppercase tracking-wider text-[#34C759]">{{ __('billing.payment_verified') }}</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                            {{ $payment->approved_at ? $payment->approved_at->format('d M Y H:i') : __('billing.instant_activation') }}
                        </span>
                    </div>
                    <h4 id="status-approved-heading"
                        class="font-bold text-black dark:text-white text-base sm:text-lg">{{ __('billing.plan_active_title') }}</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ __('billing.features_activated') }} <strong
                            class="text-black dark:text-white">{{ $payment->package_name ?? 'Cooca' }}</strong>
                        @if($payment->business->subscription?->ends_at)
                            {{ __('billing.until') }} <strong class="text-black dark:text-white font-mono">{{ $payment->business->subscription->ends_at->format('d M Y') }}</strong>.
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full sm:w-auto shrink-0">
                <a href="{{ route('dashboard') }}"
                    class="w-full sm:w-auto h-11 px-5 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                    <span>{{ __('billing.action_open_dashboard') }}</span>
                    <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                </a>
                <a href="{{ route('billing.limits') }}"
                    class="w-full sm:w-auto h-11 px-4 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.04] dark:hover:bg-white/[0.08] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                    <i data-lucide="gauge" class="w-4 h-4 text-gray-400" aria-hidden="true"></i>
                    <span>{{ __('billing.tab_limits') }}</span>
                </a>
                <a href="{{ route('billing.payment.invoice', $payment) }}" target="_blank"
                    class="w-full sm:w-auto h-11 px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] hover:bg-blue-50 dark:hover:bg-blue-900/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4" aria-hidden="true"></i>
                    <span>{{ __('billing.download_invoice') }}</span>
                </a>
            </div>
        </section>

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
                                {{ __('billing.total_tripay_bill') }}
                            </span>
                            <h3 id="nominal-transfer-heading" class="text-xs text-gray-600 dark:text-gray-300">
                                {{ __('billing.official_nominal_issued') }}
                            </h3>
                        </div>
                        <span class="text-xs font-semibold text-[#34C759] font-mono flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                            <span>{{ __('billing.instant_247') }}</span>
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
                                <span>{{ __('billing.payment_deadline_until', ['time' => $payment->gateway_expired_at ? $payment->gateway_expired_at->format('d M Y, H:i') . ' WIB' : '24 Jam']) }}</span>
                            </div>
                        </div>

                        <!-- 1-Click Copy Nominal Clean Button -->
                        <div class="flex items-center gap-2">
                            <button type="button"
                                @click="copyToClipboard('{{ (int) $payment->total_payable }}', 'nominal')"
                                class="h-11 px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-blue-50/60 hover:bg-blue-100/80 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/60 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 focus-visible:ring-2 focus-visible:ring-[#007AFF]"
                                aria-label="{{ __('billing.copy_clean_amount_aria') }}">
                                <i data-lucide="copy" class="w-4 h-4" x-show="copiedText !== 'nominal'"
                                    aria-hidden="true"></i>
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759]"
                                    x-show="copiedText === 'nominal'" x-cloak aria-hidden="true"></i>
                                <span
                                    x-text="copiedText === 'nominal' ? '{{ __('billing.nominal_copied') }}' : '{{ __('billing.copy_clean_amount') }}'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Nominal Breakdown Box -->
                    <div
                        class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5 font-mono">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400 font-sans">{{ __('billing.base_plan_price') }}</span>
                            <span class="font-bold tabular-nums text-black dark:text-white">Rp
                                {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between text-xs pt-1 border-t border-black/[0.06] dark:border-white/[0.08] font-bold">
                            <span class="text-black dark:text-white font-sans">{{ __('billing.final_total_bill') }}</span>
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
                            <strong class="font-semibold text-black dark:text-white">TriPay Gateway:</strong> {{ __('billing.tripay_verified_notice') }}
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
                                {{ ($payment->isTripay() || $payment->payment_method === 'qris') ? __('billing.official_payment_gateway') : __('billing.official_destination_account') }}
                            </span>
                            <h3 id="destination-account-heading"
                                class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                                <i data-lucide="{{ ($payment->isTripay() || $payment->payment_method === 'qris') ? 'qr-code' : 'building-2' }}" class="w-4 h-4 text-[#007AFF]"
                                    aria-hidden="true"></i>
                                <span>{{ ($payment->isTripay() || $payment->payment_method === 'qris') ? __('billing.tripay_qris_national') : $methodDetails['bank_name'] }}</span>
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
                                    <span>{{ __('billing.official_tripay_checkout') }}</span>
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ __('billing.official_tripay_checkout_desc') }}
                                </div>
                            </div>
                            <a href="{{ $payment->gateway_pay_url }}" target="_blank"
                                class="h-10 px-4 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition inline-flex items-center justify-center gap-2 shadow-sm shrink-0 cursor-pointer">
                                <span>{{ __('billing.open_tripay_page') }}</span>
                                <i data-lucide="arrow-up-right" class="w-4 h-4" aria-hidden="true"></i>
                            </a>
                        </div>
                    @endif

                    @if (($methodDetails['type'] ?? '') !== 'qris' && $payment->payment_method !== 'qris')
                        <!-- Account Number Display with Copy Action (Only for Non-QRIS Accounts) -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ ($payment->isTripay()) ? (str_contains(strtolower($methodDetails['bank_name']), 'gerai') || str_contains(strtolower($payment->payment_method), 'alfa') || str_contains(strtolower($payment->payment_method), 'indo') ? __('billing.retail_cashier_code') : __('billing.virtual_account_number')) : __('billing.destination_account_number') }}
                            </label>
                            <div
                                class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-0.5">
                                    <div
                                        class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-black dark:text-white tracking-wider">
                                        {{ $payment->gateway_pay_code ?: $methodDetails['account_number'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('billing.account_holder_prefix') }} <span
                                            class="font-semibold text-black dark:text-white">{{ $methodDetails['account_name'] }}</span>
                                    </div>
                                </div>

                                <button type="button"
                                    @click="copyToClipboard('{{ str_replace(['-', ' '], '', $payment->gateway_pay_code ?: $methodDetails['account_number']) }}', 'rekening')"
                                    class="h-10 px-3.5 rounded-[12px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/[0.04] dark:hover:bg-white/[0.08] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 shrink-0 focus-visible:ring-2 focus-visible:ring-[#007AFF]"
                                    aria-label="{{ __('billing.copy_destination_account_aria') }}">
                                    <i data-lucide="copy" class="w-3.5 h-3.5" x-show="copiedText !== 'rekening'"
                                        aria-hidden="true"></i>
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]"
                                        x-show="copiedText === 'rekening'" x-cloak aria-hidden="true"></i>
                                    <span
                                        x-text="copiedText === 'rekening' ? '{{ __('billing.action_number_copied') }}' : '{{ __('billing.action_copy_number') }}'"></span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- QRIS Visual Card if QRIS selected (Direct UI Presentation matching menu.blade.php concept) -->
                    @if (($methodDetails['type'] ?? '') === 'qris' || $payment->payment_method === 'qris')
                        <div
                            class="p-5 sm:p-6 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-slate-950 dark:text-white space-y-4 shadow-sm">
                            
                            <!-- Header Bar -->
                            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                <div class="text-left">
                                    <span class="text-[9px] font-bold tracking-widest text-black/50 dark:text-white/50 uppercase block font-mono">{{ __('billing.qris_national_standard') }}</span>
                                    <h3 class="text-sm sm:text-base font-black text-black dark:text-white tracking-tight">{{ __('billing.qris_title') }}</h3>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('billing.qris_subtitle') }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 font-mono shrink-0">
                                    {{ __('billing.qris_free_admin_fee') }}
                                </span>
                            </div>

                            <!-- TriPay Inactive Alert if needed -->
                            @if (empty($payment->gateway_reference) && !empty($payment->admin_notes) && str_contains($payment->admin_notes, 'TriPay'))
                                <div class="p-4 rounded-[14px] bg-amber-500/10 border border-amber-500/25 text-amber-900 dark:text-amber-200 text-xs text-left space-y-2">
                                    <div class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300">
                                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                        <span>{{ __('billing.tripay_channel_inactive') }}</span>
                                    </div>
                                    <p class="leading-relaxed">
                                        {{ str_replace(['TriPay Error: ', 'TriPay Exception: '], '', $payment->admin_notes) }}
                                    </p>
                                    <button type="button" @click="checkPaymentStatus()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-amber-600 hover:bg-amber-700 text-white font-semibold text-[11px] transition-all cursor-pointer">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="checkingStatus ? 'animate-spin' : ''"></i>
                                        <span>{{ __('billing.reload_reinit_bill') }}</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Amount Box (seperti konsep menu.blade.php) -->
                            <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                                <span class="text-xs text-black/60 dark:text-white/60 font-medium">{{ __('billing.total_payable') }}</span>
                                <span class="font-black text-xl text-[#34C759] dark:text-[#30D158] font-mono tabular-nums">
                                    Rp {{ number_format((float) $payment->total_payable, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- QR Code Render Card (White squircle canvas) -->
                            <div class="p-5 rounded-[20px] bg-white border border-black/10 shadow-inner flex flex-col items-center justify-center relative max-w-xs mx-auto">
                                @php
                                    $qrSrc = !empty($payment->gateway_qr_url)
                                        ? $payment->gateway_qr_url
                                        : (!empty($payment->gateway_qr_string)
                                            ? 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=8&data=' . urlencode($payment->gateway_qr_string)
                                            : (!empty($methodDetails['qr_image_url']) ? $methodDetails['qr_image_url'] : null));
                                @endphp

                                @if ($qrSrc)
                                    <img src="{{ $qrSrc }}" alt="QRIS Code" class="w-60 h-60 object-contain rounded-lg">
                                @else
                                    <div class="w-60 h-60 flex flex-col items-center justify-center text-center p-4">
                                        <i data-lucide="qr-code" class="w-24 h-24 text-black/20 mx-auto" aria-hidden="true"></i>
                                        <span class="text-xs font-semibold text-amber-600 mt-2 block">{{ __('billing.checking_gateway') }}</span>
                                    </div>
                                @endif

                                @if(!$payment->isPaid())
                                    <div class="mt-3 text-[11px] font-bold text-gray-600 uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                                        <span>{{ __('billing.qris_waiting_payment') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Auto-Verification Notice -->
                            <div class="space-y-1 text-center max-w-sm mx-auto">
                                <p class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed">
                                    {{ __('billing.qris_auto_verify_notice') }}
                                </p>
                            </div>

                            <!-- Actions (Unduh QR & Cek Status) -->
                            <div class="pt-2 flex flex-col sm:flex-row gap-2 max-w-sm mx-auto">
                                @if ($qrSrc)
                                    <a href="{{ $qrSrc }}" download="qris-cooca-{{ $payment->order_number }}.png" target="_blank"
                                        class="flex-1 h-10 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('billing.action_download_qr') }}</span>
                                    </a>
                                @endif
                                <button type="button" @click="checkPaymentStatus()" :disabled="checkingStatus"
                                    class="flex-1 h-10 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98] disabled:opacity-60 cursor-pointer">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="checkingStatus ? 'animate-spin' : ''"></i>
                                    <span>{{ __('billing.action_check_status_now') }}</span>
                                </button>
                            </div>

                            <!-- Supported Banking & e-Wallet Footnote -->
                            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] text-center space-y-1 text-[11px] text-black/40 dark:text-white/40">
                                <span class="font-semibold block text-black/60 dark:text-white/60">{{ __('billing.qris_supported_apps') }}</span>
                                <span>{{ __('billing.qris_supported_apps_list') }}</span>
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
                                <span>{{ __('billing.transfer_instructions') }}</span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 leading-relaxed text-[11px]">
                                {{ $methodDetails['instructions'] }}</p>
                        </div>
                    @endif


                    <!-- WhatsApp Support Callout -->
                    <div
                        class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('billing.payment_issues_question') }}</span>
                        <a href="https://wa.me/?text=Halo%20Admin%20Cooca,%20saya%20butuh%20konfirmasi%20pembayaran%20pesanan%20nomor%20{{ $payment->order_number }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-[#007AFF] hover:underline font-semibold transition">
                            <i data-lucide="message-circle" class="w-4 h-4" aria-hidden="true"></i>
                            <span>{{ __('billing.contact_wa_billing') }}</span>
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
                                <span>{{ __('billing.tripay_auto_gateway') }}</span>
                            </h3>
                            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('billing.instant_verification_no_upload') }}
                            </p>
                        </div>
                        @if ($payment->isPaid())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase font-mono bg-green-50 text-[#34C759] dark:bg-green-950/40 dark:text-[#30D158] border border-green-200/60 dark:border-green-800/40">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                {{ __('billing.status_paid_badge') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase font-mono bg-blue-50 text-[#007AFF] dark:bg-blue-950/40 dark:text-[#0A84FF] border border-blue-200/60 dark:border-blue-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-ping"></span>
                                {{ __('billing.status_auto_settle_badge') }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <!-- Key Metadata List -->
                        <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">{{ __('billing.order_number') }}:</span>
                                <span class="font-mono font-bold text-black dark:text-white">{{ $payment->order_number }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">{{ __('billing.tripay_reference') }}</span>
                                <span class="font-mono font-bold text-[#007AFF]">{{ $payment->gateway_reference ?: '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">{{ __('billing.payment_method_label') }}</span>
                                <span class="font-semibold text-black dark:text-white uppercase">{{ strtoupper($payment->payment_method) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400 font-sans">{{ __('billing.payment_deadline') }}</span>
                                <span class="font-mono text-amber-600 dark:text-amber-400 font-semibold">
                                    {{ $payment->gateway_expired_at ? $payment->gateway_expired_at->format('d M Y, H:i') . ' WIB' : ($payment->created_at ? $payment->created_at->addHours(24)->format('d M Y, H:i') . ' WIB' : '24 Jam') }}
                                </span>
                            </div>
                        </div>

                        <!-- Instant Auto-Verification Explanation -->
                        <div class="p-4 rounded-[14px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/50 dark:border-blue-800/30 text-xs text-blue-950 dark:text-blue-200 leading-relaxed space-y-2">
                            <div class="flex items-center gap-2 font-bold text-[#007AFF]">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0" aria-hidden="true"></i>
                                <span>{{ __('billing.realtime_auto_verification') }}</span>
                            </div>
                            <p class="text-[11px] text-gray-600 dark:text-gray-300">
                                {{ __('billing.realtime_auto_verification_desc') }}
                            </p>
                        </div>

                        <!-- Interactive Check Status Button -->
                        <div x-show="!isPaid" class="space-y-2 pt-1">
                            <button type="button" @click="checkPaymentStatus()" :disabled="checkingStatus"
                                class="w-full h-11 rounded-[14px] text-xs sm:text-sm font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60">
                                <span x-show="checkingStatus" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full" aria-hidden="true"></span>
                                <i data-lucide="refresh-cw" class="w-4 h-4" x-show="!checkingStatus" aria-hidden="true"></i>
                                <span x-text="checkingStatus ? '{{ __('billing.checking_gateway') }}' : '{{ __('billing.check_status_now') }}'"></span>
                            </button>
                            <div x-show="checkStatusFeedback" x-cloak
                                class="p-2.5 rounded-[10px] text-center text-xs font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black dark:text-white"
                                x-text="checkStatusFeedback"></div>
                        </div>

                        <!-- Paid Success Callout -->
                        <div x-show="isPaid" x-cloak class="p-4 rounded-[14px] bg-green-50 dark:bg-green-950/30 border border-green-200/60 dark:border-green-800/40 text-center space-y-2">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 text-[#34C759] flex items-center justify-center mx-auto">
                                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                            </div>
                            <div class="font-bold text-sm text-green-900 dark:text-green-200">{{ __('billing.payment_verified') }}</div>
                            <p class="text-xs text-green-700 dark:text-green-300">{{ __('billing.instant_activation') }}. {{ __('billing.features_activated') }}</p>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#007AFF] hover:underline pt-1">
                                <span>{{ __('billing.action_open_dashboard') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <!-- Step-by-Step Mini Guide -->
                        <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5 text-xs">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400 font-semibold block">{{ __('billing.how_to_pay_title') }}</span>
                            <ol class="space-y-2 text-[11px] text-gray-600 dark:text-gray-400 list-decimal list-inside leading-relaxed">
                                <li>{{ __('billing.how_to_pay_step_1') }}</li>
                                <li>{!! __('billing.how_to_pay_step_2', ['va' => '<strong class="text-black dark:text-white">Virtual Account</strong>', 'qris' => '<strong class="text-black dark:text-white">QRIS</strong>']) !!}</li>
                                <li>{{ __('billing.how_to_pay_step_3') }}</li>
                                <li>{{ __('billing.how_to_pay_step_4') }}</li>
                                <li>{{ __('billing.how_to_pay_step_5') }}</li>
                            </ol>
                        </div>

                        <!-- Historical Proof Preview if existed on legacy orders -->
                        @if ($payment->payment_proof_path)
                            <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] text-center space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-semibold text-gray-500 font-mono">{{ __('billing.transfer_proof_archive') }}</span>
                                    <a href="{{ $payment->getProofUrl() }}" target="_blank" class="text-xs text-[#007AFF] hover:underline inline-flex items-center gap-1 font-semibold">
                                        <span>{{ __('billing.open_file') }}</span>
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
                                <span>{{ __('billing.wa_help') }}</span>
                            </a>
                        </div>
                    </div>
                </section>

            </div>

        </div>

    </div>
@endsection
