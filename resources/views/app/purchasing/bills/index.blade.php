@extends('layouts.app', [
    'title' => __('purchasing.bills.title'),
    'headerTitle' => __('purchasing.bills.title'),
    'headerSubtitle' => __('purchasing.bills.subtitle')
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12">
    {{-- ===================================================== --}}
    {{-- 1. TOOLBAR / PAGE HEADER                                --}}
    {{-- ===================================================== --}}
    <x-module-header
        title="{{ __('purchasing.bills.title') }}"
        subtitle="{{ __('purchasing.bills.subtitle') }}">
        @if(\App\Support\Context::hasPermission('finance.payables') || \App\Support\Context::hasPermission('accounting.view'))
            <a href="{{ route('finance.payables') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="clock" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                <span>{{ __('purchasing.bills.aging_analysis') }}</span>
            </a>
        @endif

        @if(\App\Support\Context::hasPermission('purchasing.view') || \App\Support\Context::hasPermission('purchasing.manage'))
            <a href="{{ route('purchase-orders.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.bills.po_list') }}</span>
            </a>
        @endif
    </x-module-header>

    {{-- ===================================================== --}}
    {{-- 2. MODULE TABS (SSOT)                                   --}}
    {{-- ===================================================== --}}
    <x-module-tabs module="purchasing" />

    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-[#34C759]"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. KPI SUMMARY ROW                                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Total Tagihan Masuk -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.bills.kpi_total_invoices') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    Rp {{ number_format($invoices->sum('total_amount'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('purchasing.kpis.accumulation') }}</span>
            </div>
        </div>

        <!-- Tile 2: Sisa Hutang Belum Lunas (Warning / System Orange) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.bills.kpi_unpaid_balance') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    Rp {{ number_format($invoices->sum('balance_due'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#FF9500] dark:text-[#FF9F0A]">{{ __('purchasing.bills.kpi_overdue') }}</span>
            </div>
        </div>

        <!-- Tile 3: Total Faktur Terdata (System Blue) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.bills.kpi_recorded_invoices') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                    {{ $invoices->total() }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Database AP</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. CONTROLS: SEGMENTED FILTER                          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('purchasing.bills.index') }}" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.fields.status') }}:</span>
                <!-- Segmented Control -->
                <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
                    <a href="{{ route('purchasing.bills.index') }}" class="px-3 py-1 rounded-[7px] transition-all {{ !request('status') ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        {{ __('purchasing.bills.status_all') }}
                    </a>
                    <a href="{{ route('purchasing.bills.index', ['status' => 'unpaid']) }}" class="px-3 py-1 rounded-[7px] transition-all {{ request('status') === 'unpaid' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        {{ __('purchasing.bills.status_unpaid') }}
                    </a>
                    <a href="{{ route('purchasing.bills.index', ['status' => 'partial']) }}" class="px-3 py-1 rounded-[7px] transition-all {{ request('status') === 'partial' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        {{ __('purchasing.bills.status_partial') }}
                    </a>
                    <a href="{{ route('purchasing.bills.index', ['status' => 'paid']) }}" class="px-3 py-1 rounded-[7px] transition-all {{ request('status') === 'paid' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                        {{ __('purchasing.bills.status_paid') }}
                    </a>
                </div>
            </div>

            @if(request('status'))
            <a href="{{ route('purchasing.bills.index') }}" class="min-h-[44px] sm:min-h-0 h-10 sm:h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition-colors inline-flex items-center gap-1.5 self-start sm:self-auto">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                <span>{{ __('purchasing.bills.reset_filter') }}</span>
            </a>
            @endif
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 5. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_invoice_number') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_supplier') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_invoice_date') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.bills.col_due_date') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.bills.col_total_amount') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.bills.col_balance_due') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">{{ __('purchasing.bills.col_status') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.bills.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('purchasing.bills.show', $invoice) }}" class="font-medium text-[#007AFF] hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $invoice->supplier->name ?? 'Supplier Umum' }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $invoice->invoice_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            @if($invoice->due_date)
                                <span class="{{ $invoice->due_date->isPast() && $invoice->balance_due > 0 ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : 'text-black/60 dark:text-white/60' }}">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black dark:text-white font-medium">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                            Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($invoice->status === 'paid')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('purchasing.bills.status_paid') }}
                                </span>
                            @elseif($invoice->status === 'partial')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('purchasing.bills.status_partial') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> {{ __('purchasing.bills.status_unpaid') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('purchasing.bills.show', $invoice) }}" class="min-h-[44px] sm:min-h-0 h-10 sm:h-7 px-3 sm:px-2.5 rounded-[8px] sm:rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center justify-center">
                                {{ __('purchasing.bills.action_detail') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="receipt" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <span>{{ __('purchasing.bills.empty') }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MOBILE GROUPED INSET LIST (iOS 18 Bento Style)      -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($invoices as $invoice)
        <a href="{{ route('purchasing.bills.show', $invoice) }}" class="min-h-[44px] p-4 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-semibold text-black dark:text-white truncate">{{ $invoice->invoice_number }}</p>
                    @if($invoice->status === 'paid')
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($invoice->status === 'partial')
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate mt-0.5">{{ $invoice->supplier->name ?? 'Supplier Umum' }}</p>
                <div class="flex items-center gap-2 mt-1 text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                        {{ __('purchasing.bills.col_balance_due') }}: Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-black/30 dark:text-white/30 shrink-0">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
            {{ __('purchasing.bills.empty') }}
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 7. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($invoices->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection
