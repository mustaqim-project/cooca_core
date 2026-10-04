@extends('layouts.app', [
    'title' => __('purchasing.receipts.title', ['number' => $purchaseOrder->po_number]),
    'headerTitle' => __('purchasing.receipts.header_title'),
    'headerSubtitle' => __('purchasing.receipts.header_subtitle')
])

@section('content')
@php
    $showBatchExpiry = $business->isModuleEnabled('batch_expiry') || in_array($business->industry, ['pharmacy', 'fnb_resto', 'fnb_bakery'], true);
@endphp
<div class="max-w-[1080px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{ isSubmitting: false }">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <x-module-header
        title="{{ __('purchasing.receipts.header_title') }}"
        subtitle="{{ __('purchasing.receipts.header_subtitle') }}">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                PO: {{ $purchaseOrder->po_number }}
            </span>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.receipts.back_to_po') }}</span>
            </a>
        </div>
    </x-module-header>

    {{-- 2. MODULE TABS (SSOT) --}}
    <x-module-tabs module="purchasing" />

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
    <!-- 3. MAIN RECEIPT FORM                                  -->
    <!-- ===================================================== -->
    <form action="{{ route('purchasing.receipts.store', $purchaseOrder) }}" method="POST" @submit="isSubmitting = true" class="space-y-6">
        @csrf

        <!-- Section 1: Lokasi & Data Header -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('purchasing.receipts.section_location') }}</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">
                        {{ __('purchasing.receipts.field_location') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="location_id" required class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">
                        {{ __('purchasing.receipts.field_receipt_number') }}
                    </label>
                    <input type="text" name="receipt_number" value="{{ $nextReceiptNumber }}"
                           class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">
                        {{ __('purchasing.receipts.field_receipt_date') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required
                           class="w-full h-11 sm:h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">
                    {{ __('purchasing.receipts.field_notes') }}
                </label>
                <textarea name="notes" rows="2" placeholder="{{ __('purchasing.receipts.notes_placeholder') }}"
                          class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
            </div>
        </div>

        <!-- Section 2: Verifikasi Kuantitas Barang (Responsive Desktop + Mobile) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[14px] font-semibold text-black dark:text-white">{{ __('purchasing.receipts.section_items') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('purchasing.receipts.section_items_desc') }}</p>
                </div>
                <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ __('purchasing.receipts.items_count', ['count' => count($purchaseOrder->items)]) }}</span>
            </div>

            <!-- Desktop View (hidden on small screen) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.receipts.col_product_material') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('purchasing.receipts.col_ordered_qty') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-44">{{ __('purchasing.receipts.col_received_qty') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-40">{{ __('purchasing.receipts.col_unit_cost') }}</th>
                            @if($showBatchExpiry)
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-36">{{ __('purchasing.receipts.col_batch_number') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-36">{{ __('purchasing.receipts.col_expiry_date') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($purchaseOrder->items as $idx => $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-4 py-3 font-medium text-black dark:text-white">
                                <span>{{ $item->product ? $item->product->name : ($item->material ? $item->material->name : $item->item_name) }}</span>
                                @if($item->material_id)
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-500/10 text-purple-600 dark:text-purple-400">Bahan</span>
                                @elseif($item->product_id)
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-600 dark:text-blue-400">Produk</span>
                                @endif
                                <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id ?? '' }}">
                                <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $item->material_id ?? '' }}">
                                <input type="hidden" name="items[{{ $idx }}][item_name]" value="{{ $item->item_name ?? ($item->product ? $item->product->name : ($item->material ? $item->material->name : '')) }}">
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                                {{ (float) $item->quantity }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="number" name="items[{{ $idx }}][quantity]" value="{{ (float) $item->quantity }}" min="0" step="any" required
                                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-semibold tabular-nums text-[#007AFF] dark:text-[#0A84FF] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                            <td class="px-4 py-3 text-right">
                                <input type="number" name="items[{{ $idx }}][unit_cost]" value="{{ (float) $item->unit_cost }}" min="0" step="any" required
                                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                            @if($showBatchExpiry)
                            <td class="px-4 py-3">
                                <input type="text" name="items[{{ $idx }}][batch_number]" placeholder="BCH-..."
                                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                            <td class="px-4 py-3">
                                <input type="date" name="items[{{ $idx }}][expiry_date]"
                                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Card List Stack) -->
            <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @foreach($purchaseOrder->items as $idx => $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[14px] font-semibold text-black dark:text-white">
                            {{ $item->product ? $item->product->name : ($item->material ? $item->material->name : $item->item_name) }}
                        </span>
                        @if($item->material_id)
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-purple-500/10 text-purple-600 dark:text-purple-400">Bahan</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-blue-500/10 text-blue-600 dark:text-blue-400">Produk</span>
                        @endif
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        {{ __('purchasing.receipts.mobile_qty_ordered', ['qty' => (float)$item->quantity]) }} &bull;
                        {{ __('purchasing.receipts.mobile_unit_cost', ['cost' => 'Rp ' . number_format($item->unit_cost, 0, ',', '.')]) }}
                    </p>

                    <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id ?? '' }}">
                    <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $item->material_id ?? '' }}">
                    <input type="hidden" name="items[{{ $idx }}][item_name]" value="{{ $item->item_name ?? ($item->product ? $item->product->name : ($item->material ? $item->material->name : '')) }}">

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.receipts.col_received_qty') }} *</label>
                            <input type="number" name="items[{{ $idx }}][quantity]" value="{{ (float) $item->quantity }}" min="0" step="any" required
                                   class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] font-semibold tabular-nums text-[#007AFF] dark:text-[#0A84FF] focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.receipts.col_unit_cost') }} *</label>
                            <input type="number" name="items[{{ $idx }}][unit_cost]" value="{{ (float) $item->unit_cost }}" min="0" step="any" required
                                   class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] tabular-nums text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    @if($showBatchExpiry)
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.receipts.col_batch_number') }}</label>
                            <input type="text" name="items[{{ $idx }}][batch_number]" placeholder="BCH-..."
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] text-black dark:text-white placeholder:text-black/35">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">{{ __('purchasing.receipts.col_expiry_date') }}</label>
                            <input type="date" name="items[{{ $idx }}][expiry_date]"
                                   class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] text-black dark:text-white">
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="min-h-[44px] h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center">
                {{ __('purchasing.actions.cancel') }}
            </a>
            @if(\App\Support\Context::hasPermission('receiving.manage') || \App\Support\Context::hasPermission('purchasing.manage'))
            <button type="submit" :disabled="isSubmitting"
                    class="min-h-[44px] h-11 sm:h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] disabled:opacity-50">
                <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <i x-show="!isSubmitting" data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>{{ __('purchasing.receipts.btn_save_receipt') }}</span>
            </button>
            @endif
        </div>
    </form>
</div>
@endsection
