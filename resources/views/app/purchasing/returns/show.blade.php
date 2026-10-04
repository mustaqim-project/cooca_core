@extends('layouts.app', [
    'title' => __('purchasing.returns.detail_title', ['number' => $return->return_number]),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.detail_subtitle')
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{ showPinModal: false, pinAction: '', pinValue: '', isSubmitting: false }">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <x-module-header
        title="{{ $return->return_number }}"
        subtitle="Ref. GR: {{ $return->goodsReceipt->receipt_number ?? '-' }} &bull; Supplier: {{ $return->supplier->name ?? '-' }}">
        <div class="flex items-center gap-2">
            @if($return->status === 'completed')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('purchasing.returns.status_completed') }}
                </span>
            @elseif($return->status === 'approved')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> {{ __('purchasing.returns.status_approved') }}
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('purchasing.returns.status_draft') }}
                </span>
            @endif

            <a href="{{ route('purchase.returns.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.returns.back_to_list') }}</span>
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

    @if($errors->any())
    <div class="rounded-[12px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
        @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. STATUS & SUMMARY COCKPIT                           -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
            <div>
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide">{{ __('purchasing.returns.doc_status') }}</span>
                <div class="mt-1">
                    @if($return->status === 'completed')
                        <span class="text-[16px] font-semibold text-[#34C759] dark:text-[#30D158]">{{ __('purchasing.returns.status_completed_desc') }}</span>
                    @elseif($return->status === 'approved')
                        <span class="text-[16px] font-semibold text-[#007AFF] dark:text-[#0A84FF]">{{ __('purchasing.returns.status_approved_desc') }}</span>
                    @else
                        <span class="text-[16px] font-semibold text-[#FF9500] dark:text-[#FF9F0A]">{{ __('purchasing.returns.status_draft_desc') }}</span>
                    @endif
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wide">{{ __('purchasing.returns.kpi_total_value') }}</span>
                <div class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] mt-0.5">
                    Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="text-[13px] text-black/70 dark:text-white/70">
            <span class="font-semibold text-black dark:text-white">{{ __('purchasing.returns.reason_label') }}</span>
            <span class="ml-1">{{ $return->reason }}</span>
        </div>

        <!-- Action Buttons -->
        @if(($return->status === 'draft' || $return->status === 'approved') && (\App\Support\Context::hasPermission('purchase.returns') || \App\Support\Context::hasPermission('purchasing.manage')))
        <div class="flex flex-wrap items-center gap-3 pt-2">
            @if($return->status === 'draft')
            <form method="POST" action="{{ route('purchase.returns.approve', $return) }}" id="form-approve">
                @csrf
                <input type="hidden" name="pin" :value="pinValue">
                <button type="button" @click="pinAction = 'approve'; showPinModal = true;"
                        class="min-h-[44px] h-11 sm:h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('purchasing.returns.btn_approve') }}</span>
                </button>
            </form>
            @endif

            @if($return->status === 'approved')
            <form method="POST" action="{{ route('purchase.returns.complete', $return) }}" id="form-complete">
                @csrf
                <input type="hidden" name="pin" :value="pinValue">
                <button type="button" @click="pinAction = 'complete'; showPinModal = true;"
                        class="min-h-[44px] h-11 sm:h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB350] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>{{ __('purchasing.returns.btn_complete') }}</span>
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. ITEMS TABLE CARD (Responsive Desktop + Mobile)     -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('purchasing.returns.items_table_title') }}</h3>
            <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ count($return->items) }} Item Baris</span>
        </div>

        <!-- Desktop Table -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.returns.col_item_name') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">{{ __('purchasing.returns.col_quantity') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.returns.col_unit_price') }}</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.returns.col_subtotal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($return->items as $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            <span>{{ $item->item_name }}</span>
                            @if($item->material_id)
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-500/10 text-purple-600 dark:text-purple-400">Bahan</span>
                            @elseif($item->product_id)
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-600 dark:text-blue-400">Produk</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center tabular-nums font-semibold text-[#FF9500] dark:text-[#FF9F0A]">
                            {{ (float)$item->quantity }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($item->unit_cost ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List -->
        <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
            @foreach($return->items as $item)
            <div class="p-4 space-y-1">
                <div class="flex items-center justify-between">
                    <p class="text-[14px] font-semibold text-black dark:text-white">{{ $item->item_name }}</p>
                    <span class="text-[14px] font-bold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">Qty: {{ (float)$item->quantity }}</span>
                </div>
                <div class="flex items-center justify-between text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                    <span>Satuan: Rp {{ number_format($item->unit_cost ?? 0, 0, ',', '.') }}</span>
                    <span class="font-semibold text-black dark:text-white">Subtotal: Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. SUPERVISOR PIN CONFIRMATION MODAL                  -->
    <!-- ===================================================== -->
    <div x-show="showPinModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="showPinModal = false">
        <div class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[16px] max-w-sm w-full p-5 space-y-4 shadow-xl"
             @click.away="showPinModal = false">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white">Otorisasi Supervisor</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-1">
                    {{ __('purchasing.returns.supervisor_pin_prompt') }}
                </p>
            </div>

            <div>
                <input type="password" maxlength="8" placeholder="••••••" x-model="pinValue"
                       @keydown.enter.prevent="if(pinAction === 'approve') document.getElementById('form-approve').submit(); else document.getElementById('form-complete').submit();"
                       class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-center text-[20px] tracking-widest font-mono text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="showPinModal = false"
                        class="min-h-[44px] h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5">
                    Batal
                </button>
                <button type="button"
                        @click="if(pinAction === 'approve') document.getElementById('form-approve').submit(); else document.getElementById('form-complete').submit();"
                        class="min-h-[44px] h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
