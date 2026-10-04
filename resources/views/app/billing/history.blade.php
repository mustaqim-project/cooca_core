@extends('layouts.app', [
    'title' => __('billing.breadcrumb_history') . ' - Cooca',
    'headerTitle' => __('billing.history_title'),
    'headerSubtitle' => __('billing.history_subtitle'),
])

@section('content')
    <div class="space-y-6 pb-28 lg:pb-10" x-data="{
        searchQuery: new URLSearchParams(window.location.search).get('q') || '',
        statusFilter: new URLSearchParams(window.location.search).get('status') || '{{ request('status', 'all') }}',
        setStatus(status) {
            this.statusFilter = status;
            const url = new URL(window.location.href);
            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            window.history.replaceState({}, '', url);
        },
        matchesFilter(orderNumber, packageName, status) {
            const matchesSearch = !this.searchQuery ||
                (orderNumber && orderNumber.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                (packageName && packageName.toLowerCase().includes(this.searchQuery.toLowerCase()));
    
            const matchesStatus = this.statusFilter === 'all' || status === this.statusFilter;
            return matchesSearch && matchesStatus;
        }
    }">

        <!-- Standard 3-Row Module Header Bento Apple HIG -->
        <x-module-header
            :title="__('billing.history_title')"
            :subtitle="__('billing.history_subtitle')"
            :breadcrumbs="[
                ['label' => __('billing.breadcrumb_billing'), 'route' => 'billing.limits'],
                ['label' => __('billing.breadcrumb_history')],
            ]"
            :badge="__('billing.total_transactions_count', ['count' => number_format($payments->total(), 0, ',', '.')])"
        >
            <x-slot:actions>
                <a href="{{ route('billing.limits') }}"
                    class="h-10 px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>{{ __('billing.back') }}</span>
                </a>
                @if (\App\Support\Context::hasPermission('billing.manage'))
                    <a href="{{ route('billing.checkout') }}"
                        class="h-10 px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-xs active:scale-[0.98] transition flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>{{ __('billing.action_choose_plan') }}</span>
                    </a>
                @endif
            </x-slot:actions>
        </x-module-header>

        <!-- Submodule Navigation Tabs -->
        <x-module-tabs module="billing" class="mt-2 mb-2" />

        <!-- 2. 4 Command Pillars KPI Cards (Bento Metric Grid) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            <!-- Pillar 1: Total Transaksi -->
            <div
                class="bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between group hover:border-[#007AFF]/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider font-mono">{{ __('billing.total_transactions') }}</span>
                        <div
                            class="w-8 h-8 rounded-[10px] bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-xl sm:text-2xl font-bold text-black dark:text-white font-mono tracking-tight tabular-nums truncate">
                        {{ number_format($payments->total(), 0, ',', '.') }}
                    </div>
                </div>
                <div
                    class="mt-3.5 pt-2.5 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between">
                    <span>{{ __('billing.audit_trail') }}</span>
                    <span class="font-semibold text-black dark:text-white font-mono tabular-nums">{{ __('billing.displayed_count', ['count' => $payments->count()]) }}</span>
                </div>
            </div>

            <!-- Pillar 2: Status Langganan -->
            <div
                class="bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between group hover:border-[#34C759]/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider font-mono">{{ __('billing.pillar_plan_status') }}</span>
                        <div
                            class="w-8 h-8 rounded-[10px] bg-green-50 dark:bg-green-900/30 border border-green-200/60 dark:border-green-800/60 flex items-center justify-center text-[#34C759]">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight truncate">
                        {{ $business->subscription?->isActive() ? 'Aktif' : __('billing.plan_free') }}
                    </div>
                </div>
                <div
                    class="mt-3.5 pt-2.5 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between">
                    <span>{{ __('billing.active_until') }}</span>
                    <span
                        class="font-bold text-[#34C759] font-mono tabular-nums">{{ $business->subscription?->ends_at?->format('d/m/Y') ?? __('billing.forever') }}</span>
                </div>
            </div>

            <!-- Pillar 3: Outlet / Bisnis -->
            <div
                class="bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between group hover:border-[#5856D6]/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span
                            class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider font-mono">{{ __('billing.active_workspace') }}</span>
                        <div
                            class="w-8 h-8 rounded-[10px] bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center text-[#5856D6]">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight truncate">
                        {{ $business->name }}
                    </div>
                </div>
                <div
                    class="mt-3.5 pt-2.5 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between">
                    <span>{{ __('billing.resource_businesses') }}</span>
                    <span class="font-semibold text-black dark:text-white font-mono tabular-nums">ID #{{ $business->id }}</span>
                </div>
            </div>

            <!-- Pillar 4: Saluran Gateway -->
            <div
                class="bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between group hover:border-[#FF9500]/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span
                            class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider font-mono">{{ __('billing.payment_gateway') }}</span>
                        <div
                            class="w-8 h-8 rounded-[10px] bg-amber-50 dark:bg-amber-900/30 border border-amber-200/60 dark:border-amber-800/60 flex items-center justify-center text-[#FF9500]">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight truncate">
                        {{ __('billing.payment_gateway_tripay') }}
                    </div>
                </div>
                <div
                    class="mt-3.5 pt-2.5 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between">
                    <span>{{ __('billing.verification') }}</span>
                    <span class="font-bold text-[#34C759] font-mono">{{ __('billing.instant_247') }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Toolbar Filter & Search Container (Apple Segmented Control) -->
        <div
            class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] p-3.5 sm:p-4 space-y-3">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <!-- Search Input: Anti auto-zoom text-[16px] sm:text-[14px] -->
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"
                        aria-hidden="true"></i>
                    <input type="text" x-model="searchQuery" placeholder="{{ __('billing.search_placeholder') }}"
                        class="w-full bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] pl-9 pr-3.5 py-2 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-gray-400 focus:outline-hidden focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition">
                </div>

                <!-- Status Filter: Segmented Control -->
                <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[12px] border border-black/[0.04] dark:border-white/[0.06] overflow-x-auto max-w-full gap-1"
                    role="tablist" aria-label="Filter Status Tagihan">
                    <button type="button" @click="setStatus('all')"
                        class="min-h-[38px] px-3.5 py-2 rounded-[9px] text-xs font-semibold transition cursor-pointer shrink-0 active:scale-[0.98] flex items-center"
                        :class="statusFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' :
                            'text-gray-500 hover:text-black dark:hover:text-white'">
                        {{ __('billing.filter_all') }}
                    </button>
                    <button type="button" @click="setStatus('pending')"
                        class="min-h-[38px] px-3.5 py-2 rounded-[9px] text-xs font-semibold transition cursor-pointer shrink-0 active:scale-[0.98] flex items-center"
                        :class="statusFilter === 'pending' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] shadow-xs' :
                            'text-gray-500 hover:text-black dark:hover:text-white'">
                        {{ __('billing.filter_pending') }}
                    </button>
                    <button type="button" @click="setStatus('awaiting_approval')"
                        class="min-h-[38px] px-3.5 py-2 rounded-[9px] text-xs font-semibold transition cursor-pointer shrink-0 active:scale-[0.98] flex items-center"
                        :class="statusFilter === 'awaiting_approval' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' :
                            'text-gray-500 hover:text-black dark:hover:text-white'">
                        {{ __('billing.filter_verification') }}
                    </button>
                    <button type="button" @click="setStatus('approved')"
                        class="min-h-[38px] px-3.5 py-2 rounded-[9px] text-xs font-semibold transition cursor-pointer shrink-0 active:scale-[0.98] flex items-center"
                        :class="statusFilter === 'approved' ? 'bg-white dark:bg-[#2C2C2E] text-[#34C759] shadow-xs' :
                            'text-gray-500 hover:text-black dark:hover:text-white'">
                        {{ __('billing.filter_success') }}
                    </button>
                    <button type="button" @click="setStatus('rejected')"
                        class="min-h-[38px] px-3.5 py-2 rounded-[9px] text-xs font-semibold transition cursor-pointer shrink-0 active:scale-[0.98] flex items-center"
                        :class="statusFilter === 'rejected' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF3B30] shadow-xs' :
                            'text-gray-500 hover:text-black dark:hover:text-white'">
                        {{ __('billing.filter_rejected') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. High-Density Data Table Container -->
        <div
            class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">

            @if ($payments->count() > 0)
                <!-- Desktop & Tablet View: High Density Responsive Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse" aria-label="Tabel Riwayat Pembayaran Tagihan">
                        <thead
                            class="bg-black/[0.02] dark:bg-white/[0.02] text-gray-500 dark:text-gray-400 font-mono uppercase text-[10px] font-semibold border-b border-black/[0.06] dark:border-white/[0.08] tracking-wider whitespace-nowrap">
                            <tr>
                                <th scope="col" class="py-3.5 px-5">{{ __('billing.order_number') }}</th>
                                <th scope="col" class="py-3.5 px-4">{{ __('billing.package_and_cycle') }}</th>
                                <th scope="col" class="py-3.5 px-4">{{ __('billing.payment_method') }}</th>
                                <th scope="col" class="py-3.5 px-4 text-right">{{ __('billing.total_payable') }}</th>
                                <th scope="col" class="py-3.5 px-4 text-center">{{ __('billing.status') }}</th>
                                <th scope="col" class="py-3.5 px-4">{{ __('billing.order_date') }}</th>
                                <th scope="col" class="py-3.5 px-5 text-right">{{ __('billing.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04] font-sans">
                            @foreach ($payments as $p)
                                @php
                                    $badge = $p->getStatusBadge();
                                    $method = $p->getPaymentMethodDetails();
                                    $pPackageName =
                                        $p->package_name ??
                                        ($p->cycle === 'annual'
                                            ? 'Cooca Tahunan'
                                            : ($p->cycle === 'monthly'
                                                ? 'Cooca Bulanan'
                                                : 'Top-Up Kuota'));
                                @endphp
                                <tr x-show="matchesFilter('{{ $p->order_number }}', '{{ $pPackageName }}', '{{ $p->status }}')"
                                    class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                    <!-- Order Number -->
                                    <td class="py-3.5 px-5">
                                        <div
                                            class="font-mono font-bold text-black dark:text-white flex items-center gap-1.5">
                                            <i data-lucide="hash" class="w-3.5 h-3.5 text-gray-400"
                                                aria-hidden="true"></i>
                                            <span>{{ $p->order_number }}</span>
                                        </div>
                                    </td>

                                    <!-- Package -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-black dark:text-white">
                                            {{ $pPackageName }}
                                        </div>
                                        <div
                                            class="text-[10px] text-gray-500 dark:text-gray-400 font-mono uppercase mt-0.5">
                                            {{ $p->plan_code ?: 'SUBSCRIPTION' }}
                                        </div>
                                    </td>

                                    <!-- Method -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-black dark:text-white">
                                            {{ $method['name'] ?? strtoupper($p->payment_method) }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                            {{ $method['bank_name'] ?? 'TriPay Gateway' }}</div>
                                    </td>

                                    <!-- Total Amount -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="font-mono font-bold text-sm text-[#007AFF] dark:text-[#0A84FF] tabular-nums">
                                            Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                        </div>
                                        @if ($p->unique_code > 0)
                                            <div
                                                class="text-[10px] text-[#FF9500] font-mono font-semibold tabular-nums">
                                                Kode unik: +{{ str_pad((string) $p->unique_code, 3, '0', STR_PAD_LEFT) }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold border inline-flex items-center gap-1 {{ $badge['class'] }}">
                                            <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3"
                                                aria-hidden="true"></i>
                                            <span>{{ $badge['label'] }}</span>
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="py-3.5 px-4 font-mono text-gray-500 dark:text-gray-400 text-[11px] tabular-nums">
                                        {{ $p->created_at->format('d M Y, H:i') }}
                                    </td>

                                    <!-- Action CTA -->
                                    <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('billing.payment.invoice', $p) }}" target="_blank"
                                                title="{{ __('billing.print_receipt') }}"
                                                class="min-w-[36px] min-h-[36px] rounded-[10px] text-gray-500 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] active:scale-[0.98] transition cursor-pointer flex items-center justify-center">
                                                <i data-lucide="printer"
                                                    class="w-4 h-4 text-[#007AFF]"
                                                    aria-hidden="true"></i>
                                            </a>
                                            <a href="{{ route('billing.payment.show', $p) }}"
                                                class="min-h-[36px] px-3.5 rounded-[10px] text-xs font-semibold text-gray-700 dark:text-gray-300 bg-black/[0.03] hover:bg-black/[0.06] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] active:scale-[0.98] transition cursor-pointer inline-flex items-center gap-1.5">
                                                <span>{{ __('billing.quick_detail') }}</span>
                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"
                                                    aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View: Clean Card List Representation -->
                <div class="block md:hidden divide-y divide-black/[0.06] dark:divide-white/[0.08]">
                    @foreach ($payments as $p)
                        @php
                            $badge = $p->getStatusBadge();
                            $method = $p->getPaymentMethodDetails();
                            $pPackageName =
                                $p->package_name ??
                                ($p->cycle === 'annual'
                                    ? 'Cooca Tahunan'
                                    : ($p->cycle === 'monthly'
                                        ? 'Cooca Bulanan'
                                        : 'Top-Up Kuota'));
                        @endphp
                        <div x-show="matchesFilter('{{ $p->order_number }}', '{{ $pPackageName }}', '{{ $p->status }}')"
                            class="p-4 sm:p-5 space-y-3 hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">

                            <!-- Card Top: Order Number & Status Badge -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="hash" class="w-3.5 h-3.5 text-gray-400" aria-hidden="true"></i>
                                    <span
                                        class="font-mono font-bold text-sm text-black dark:text-white">{{ $p->order_number }}</span>
                                </div>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold border inline-flex items-center gap-1 {{ $badge['class'] }}">
                                    <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" aria-hidden="true"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </div>

                            <!-- Card Body: Package & Amount -->
                            <div
                                class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('billing.package_label') }}:</span>
                                    <span class="font-semibold text-black dark:text-white text-right">
                                        {{ $pPackageName }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('billing.method_label') }}:</span>
                                    <span
                                        class="font-medium text-black dark:text-white">{{ $method['name'] ?? strtoupper($p->payment_method) }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('billing.total_payable') }}:</span>
                                    <div class="text-right">
                                        <div class="font-mono font-bold text-sm text-[#007AFF] dark:text-[#0A84FF] tabular-nums">
                                            Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                        </div>
                                        @if ($p->unique_code > 0)
                                            <div class="text-[10px] text-[#FF9500] font-mono tabular-nums">
                                                {{ __('billing.unique_code_label') }}: +{{ str_pad((string) $p->unique_code, 3, '0', STR_PAD_LEFT) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer: Date & Quick Actions -->
                            <div class="flex items-center justify-between gap-3 pt-1">
                                <div
                                    class="text-[11px] font-mono text-gray-500 dark:text-gray-400 flex items-center gap-1 tabular-nums">
                                    <i data-lucide="clock" class="w-3 h-3 text-gray-400" aria-hidden="true"></i>
                                    <span>{{ $p->created_at->format('d M Y, H:i') }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('billing.payment.invoice', $p) }}" target="_blank"
                                        class="min-h-[44px] min-w-[44px] rounded-[12px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-[#007AFF] text-xs font-semibold transition active:scale-[0.98] flex items-center justify-center"
                                        title="{{ __('billing.print_receipt') }}">
                                        <i data-lucide="printer" class="w-4 h-4" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ route('billing.payment.show', $p) }}"
                                        class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition cursor-pointer flex items-center gap-1.5">
                                        <span>{{ __('billing.quick_detail') }}</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <!-- 6. Empty State Component -->
                <div class="py-16 px-6 text-center space-y-4">
                    <div class="w-14 h-14 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] text-gray-400 flex items-center justify-center mx-auto"
                        aria-hidden="true">
                        <i data-lucide="receipt" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h4 class="text-sm font-bold text-black dark:text-white">{{ __('billing.empty_history_title') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('billing.empty_history_desc') }}
                        </p>
                    </div>
                    @if (\App\Support\Context::hasPermission('billing.manage'))
                        <div class="pt-2">
                            <a href="{{ route('billing.checkout') }}"
                                class="h-10 px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition cursor-pointer inline-flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-4 h-4" aria-hidden="true"></i>
                                <span>{{ __('billing.action_choose_plan') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- 7. Pagination Bar -->
            @if ($payments->hasPages())
                <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02]">
                    {{ $payments->links() }}
                </div>
            @endif

        </div>

    </div>
@endsection
