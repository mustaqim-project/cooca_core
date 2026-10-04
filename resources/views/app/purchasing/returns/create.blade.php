@extends('layouts.app', [
    'title' => __('purchasing.returns.create_title'),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.create_subtitle')
])

@section('content')
<div class="max-w-[880px] mx-auto space-y-6 pb-28 lg:pb-12"
     x-data="returnCreator({ receipts: {{ Js::from($receipts) }}, oldReceiptId: '{{ old('goods_receipt_id', '') }}' })"
     x-init="init()">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <x-module-header
        title="{{ __('purchasing.returns.create_title') }}"
        subtitle="{{ __('purchasing.returns.create_subtitle') }}">
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase.returns.index') }}"
               class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.returns.back_to_list') }}</span>
            </a>
        </div>
    </x-module-header>

    {{-- 2. MODULE TABS (SSOT) --}}
    <x-module-tabs module="purchasing" />

    @if ($errors->any())
        <div class="rounded-[12px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 3. RETURN CREATION FORM (ZERO-MANUAL UI)              -->
    <!-- ===================================================== -->
    <form method="POST" action="{{ route('purchase.returns.store') }}" @submit="isSubmitting = true" class="space-y-6">
        @csrf

        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5">
            <!-- 1. Dokumen Penerimaan -->
            <div>
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                    {{ __('purchasing.returns.field_goods_receipt') }} <span class="text-[#FF3B30]">*</span>
                </label>
                <select name="goods_receipt_id" x-model="selectedReceiptId" @change="onReceiptChange()" required
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">{{ __('purchasing.returns.field_goods_receipt_placeholder') }}</option>
                    <template x-for="r in receipts" :key="r.id">
                        <option :value="r.id" x-text="`${r.receipt_number} - ${r.supplier ? r.supplier.name : 'Supplier'} (${r.receipt_date || '-'})`"></option>
                    </template>
                </select>
            </div>

            <!-- 2. Detail Item yang Dikembalikan (Zero-Manual UI via Alpine.js) -->
            <div x-show="selectedItems.length > 0" x-cloak class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-[13px] font-semibold text-black dark:text-white">{{ __('purchasing.returns.section_items') }}</h4>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Centang dan tentukan jumlah barang yang ingin dikembalikan.</p>
                    </div>
                    <span class="text-[12px] font-medium text-[#007AFF] tabular-nums" x-text="`${activeItemsCount()} item dipilih`"></span>
                </div>

                <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden divide-y divide-black/5 dark:divide-white/5">
                    <template x-for="(item, index) in selectedItems" :key="item.id">
                        <div class="p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors"
                             :class="item.selected ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.05]' : 'bg-black/[0.01] dark:bg-white/[0.01]'">
                            
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <input type="checkbox" :id="`item_chk_${index}`" x-model="item.selected" @change="onToggle(index)"
                                       class="mt-1 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                                
                                <div class="min-w-0 flex-1 cursor-pointer" @click="onToggle(index)">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[14px] font-semibold text-black dark:text-white truncate" x-text="item.name"></p>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium"
                                              :class="item.type === 'Bahan' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400'"
                                              x-text="item.type"></span>
                                    </div>
                                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                                        <span>Diterima di GR: </span><span class="font-medium text-black dark:text-white tabular-nums" x-text="item.max_quantity"></span> &bull; 
                                        <span>Harga Satuan: </span><span class="font-medium text-black dark:text-white tabular-nums" x-text="formatRupiah(item.unit_cost)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pl-7 sm:pl-0 shrink-0">
                                <input type="hidden" :name="item.selected ? `items[${index}][goods_receipt_item_id]` : ''" :value="item.id" :disabled="!item.selected">
                                
                                <label class="text-[12px] font-medium text-black/60 dark:text-white/60">Qty Retur:</label>
                                <input type="number" :name="item.selected ? `items[${index}][quantity]` : ''"
                                       min="0.0001" :max="item.max_quantity" step="any"
                                       x-model="item.return_quantity"
                                       :disabled="!item.selected"
                                       class="w-24 h-10 sm:h-9 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[8px] px-2.5 text-right font-semibold text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] disabled:opacity-40 disabled:bg-black/5 transition">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- 3. Alasan Pengembalian -->
            <div>
                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                    {{ __('purchasing.returns.field_reason') }} <span class="text-[#FF3B30]">*</span>
                </label>
                <input name="reason" placeholder="{{ __('purchasing.returns.reason_placeholder') }}" required value="{{ old('reason') }}"
                       class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-black/5 dark:border-white/10">
                <a href="{{ route('purchase.returns.index') }}"
                   class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center">
                    {{ __('purchasing.actions.cancel') }}
                </a>
                @if (\App\Support\Context::hasPermission('purchase.returns') || \App\Support\Context::hasPermission('purchasing.manage'))
                    <button type="submit" :disabled="isSubmitting || activeItemsCount() === 0"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] disabled:opacity-40 disabled:pointer-events-none">
                        <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <i x-show="!isSubmitting" data-lucide="check" class="w-4 h-4"></i>
                        <span>{{ __('purchasing.returns.btn_save_draft') }}</span>
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>

<script>
function returnCreator(config) {
    return {
        receipts: config.receipts || [],
        selectedReceiptId: config.oldReceiptId || '',
        selectedItems: [],
        isSubmitting: false,

        init() {
            if (this.selectedReceiptId) {
                this.onReceiptChange();
            }
        },

        onReceiptChange() {
            const r = this.receipts.find(item => item.id === this.selectedReceiptId);
            if (!r || !r.items) {
                this.selectedItems = [];
                return;
            }
            this.selectedItems = r.items.map(item => ({
                id: item.id,
                name: item.item_name || (item.product ? item.product.name : (item.material ? item.material.name : 'Item')),
                type: item.material_id ? 'Bahan' : 'Produk',
                unit_cost: item.unit_cost,
                max_quantity: item.quantity,
                return_quantity: item.quantity,
                selected: true
            }));
        },

        onToggle(index) {
            const item = this.selectedItems[index];
            if (item.selected && (!item.return_quantity || item.return_quantity <= 0)) {
                item.return_quantity = item.max_quantity;
            }
        },

        activeItemsCount() {
            return this.selectedItems.filter(i => i.selected && i.return_quantity > 0).length;
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endsection
