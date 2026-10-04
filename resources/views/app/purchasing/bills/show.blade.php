@extends('layouts.app', [
    'title' => __('purchasing.bills.detail_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('purchasing.bills.title'),
    'headerSubtitle' => __('purchasing.bills.detail_subtitle')
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-28 lg:pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <x-module-header
        title="{{ $invoice->invoice_number }}"
        subtitle="{{ $invoice->supplier->name }} &bull; GR: {{ $invoice->goodsReceipt->receipt_number ?? '-' }}">
        <div class="flex items-center gap-2">
            @if($invoice->status === 'paid')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('purchasing.bills.status_paid') }}
                </span>
            @elseif($invoice->status === 'partial')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('purchasing.bills.status_partial') }}
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('purchasing.bills.status_unpaid') }}
                </span>
            @endif

            <a href="{{ route('purchasing.bills.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.bills.back_to_bills') }}</span>
            </a>
        </div>
    </x-module-header>

    {{-- 2. MODULE TABS (SSOT) --}}
    <x-module-tabs module="purchasing" />

    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. FINANCIAL KPI CARDS                                -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Total Tagihan (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.bills.col_total_amount') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('purchasing.bills.kpi_obligations') }}</span>
            </div>
        </div>

        <!-- Terbayar (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.bills.kpi_paid') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">{{ __('purchasing.bills.kpi_paid_done') }}</span>
            </div>
        </div>

        <!-- Sisa Hutang (System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.bills.col_balance_due') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black dark:text-white' }}">
                    Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black/40 dark:text-white/40' }}">
                    {{ $invoice->balance_due > 0 ? __('purchasing.bills.kpi_overdue') : __('purchasing.bills.kpi_zero') }}
                </span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. PAYMENT RECORD FORM (PROTECTED & MULTI-ACCOUNT)   -->
    <!-- ===================================================== -->
    @if($invoice->balance_due > 0 && (\App\Support\Context::hasPermission('purchasing.bills') || \App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('invoices.record_payment')))
    <div x-data="{ isSubmitting: false, amount: {{ old('amount', '') ?: 'null' }} }" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
        <div class="border-b border-black/5 dark:border-white/10 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-[15px] font-semibold text-black dark:text-white">{{ __('purchasing.bills.record_payment_title') }}</h2>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('purchasing.bills.record_payment_subtitle') }}</p>
            </div>
            <span class="text-[12px] font-medium text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">
                Maks: Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
            </span>
        </div>

        @if($errors->any())
        <div class="rounded-[10px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-3 text-[12px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
            @foreach($errors->all() as $error)
                <p>&bull; {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('purchasing.bills.payments.store', $invoice) }}" @submit="isSubmitting = true" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.bills.field_payment_amount') }}</label>
                    <input name="amount" type="number" min="0.01" max="{{ $invoice->balance_due }}" step="0.01" placeholder="Contoh: 500000" required
                           x-model="amount"
                           class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] font-medium tabular-nums text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.bills.field_payment_date') }}</label>
                    <input name="payment_date" type="date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required
                           class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.bills.field_payment_method') }}</label>
                    <select name="payment_method" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="bank_transfer">{{ __('purchasing.bills.method_bank_transfer') }}</option>
                        <option value="cash">{{ __('purchasing.bills.method_cash') }}</option>
                        <option value="qris">{{ __('purchasing.bills.method_qris') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.bills.field_cash_account') }}</label>
                    <select name="cash_account_id" class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">{{ __('purchasing.bills.field_cash_account_placeholder') }}</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('cash_account_id') === $account->id ? 'selected' : '' }}>
                                {{ $account->name }} (Saldo: Rp {{ number_format($account->current_balance ?? 0, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.bills.field_reference_number') }}</label>
                    <input name="reference_number" value="{{ old('reference_number') }}" placeholder="Contoh: REF-BCA-9821..."
                           class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <!-- Supervisor PIN conditional display if amount >= 5.000.000 -->
            <div x-show="amount >= 5000000" x-cloak class="rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/20 p-3 space-y-1">
                <label class="block text-[12px] font-semibold text-[#B25E00] dark:text-[#FF9F0A]">
                    {{ __('purchasing.bills.field_supervisor_pin') }}
                </label>
                <input name="supervisor_pin" type="password" maxlength="8" placeholder="••••••"
                       class="w-full sm:w-64 h-10 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-3 text-[16px] sm:text-[13px] tracking-widest text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" :disabled="isSubmitting"
                        class="min-h-[44px] w-full sm:w-auto h-11 sm:h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] disabled:opacity-50 disabled:pointer-events-none">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <i x-show="!isSubmitting" data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('purchasing.bills.btn_save_payment') }}</span>
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 5. PAYMENT HISTORY TABLE                              -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('purchasing.bills.payment_history_title') }}</h3>
            <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ __('purchasing.bills.payment_history_count', ['count' => count($invoice->payments)]) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_payment_number') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_payment_date') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_payment_method') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.bills.col_payment_amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoice->payments as $payment)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-medium text-black dark:text-white tabular-nums">
                            {{ $payment->payment_number }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                {{ strtoupper($payment->payment_method) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-[13px] text-black/40 dark:text-white/40">
                            {{ __('purchasing.bills.empty_payments') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
