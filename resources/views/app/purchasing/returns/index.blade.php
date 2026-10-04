@extends('layouts.app', [
    'title' => __('purchasing.returns.title'),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.subtitle')
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12">
    {{-- ===================================================== --}}
    {{-- 1. TOOLBAR / PAGE HEADER                                --}}
    {{-- ===================================================== --}}
    <x-module-header
        title="{{ __('purchasing.returns.title') }}"
        subtitle="{{ __('purchasing.returns.subtitle') }}">
        @if(\App\Support\Context::hasPermission('purchase.returns') || \App\Support\Context::hasPermission('purchasing.manage'))
            <a href="{{ route('purchase.returns.create') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('purchasing.returns.btn_create_return') }}</span>
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Total Dokumen Retur -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.returns.kpi_total_returns') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    {{ $returns->total() }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('purchasing.returns.kpi_claim_vendor') }}</span>
            </div>
        </div>

        <!-- Tile 2: Total Nilai Retur (System Orange Warning/Claim) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.returns.kpi_total_value') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    Rp {{ number_format($returns->sum('total_amount'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#FF9500] dark:text-[#FF9F0A]">{{ __('purchasing.returns.kpi_claim_value') }}</span>
            </div>
        </div>

        <!-- Tile 3: Status Selesai (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">{{ __('purchasing.returns.kpi_completed_returns') }}</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $returns->where('status', 'completed')->count() }}
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">{{ __('purchasing.returns.kpi_inventory_synced') }}</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.returns.col_return_number') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.returns.col_supplier') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.returns.col_ref_gr') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.returns.col_return_date') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.returns.col_total_amount') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">{{ __('purchasing.returns.col_status') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.returns.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($returns as $return)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('purchase.returns.show', $return) }}" class="font-medium text-[#007AFF] hover:underline">
                                {{ $return->return_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $return->supplier->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $return->goodsReceipt->receipt_number ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $return->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($return->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('purchasing.returns.status_completed') }}
                                </span>
                            @elseif($return->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> {{ __('purchasing.returns.status_approved') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('purchasing.returns.status_draft') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('purchase.returns.show', $return) }}" class="min-h-[44px] sm:min-h-0 h-10 sm:h-7 px-3 sm:px-2.5 rounded-[8px] sm:rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center justify-center">
                                {{ __('purchasing.bills.action_detail') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="undo-2" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                <span>{{ __('purchasing.returns.empty') }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (iOS 18 Bento Style)      -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($returns as $return)
        <a href="{{ route('purchase.returns.show', $return) }}" class="min-h-[44px] p-4 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-semibold text-black dark:text-white truncate">{{ $return->return_number }}</p>
                    @if($return->status === 'completed')
                        <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($return->status === 'approved')
                        <span class="w-2 h-2 rounded-full bg-[#007AFF] shrink-0"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate mt-0.5">{{ $return->supplier->name ?? '-' }} &bull; GR: {{ $return->goodsReceipt->receipt_number ?? '-' }}</p>
                <div class="flex items-center gap-2 mt-1 text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $return->created_at->format('d/m/Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold text-black dark:text-white">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-black/30 dark:text-white/30 shrink-0">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
            {{ __('purchasing.returns.empty') }}
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 6. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($returns->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $returns->links() }}
    </div>
    @endif
</div>
@endsection
