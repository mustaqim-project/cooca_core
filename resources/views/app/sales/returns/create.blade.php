@extends('layouts.app', ['title' => 'Buat Retur Penjualan'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="salesReturnForm()">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('sales.returns.index') }}" class="hover:text-white transition">Retur Penjualan</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-rose-400 font-semibold">Form Retur Baru</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Formulir Retur Penjualan</h1>
            <p class="text-xs text-slate-400 mt-0.5">Pilih sumber transaksi (Faktur Penjualan atau Kasir POS) untuk memuat item, tentukan kuantitas yang diretur, dan metode pengembalian dana.</p>
        </div>
        <a href="{{ route('sales.returns.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    @if($errors->any())
        <div class="glass-card rounded-2xl p-4 border border-rose-500/40 bg-rose-500/10 space-y-1">
            <p class="text-xs font-bold text-rose-400 mb-1">Harap perbaiki kesalahan berikut:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="text-xs text-rose-300">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.returns.store') }}" @submit="return validateForm($event)" class="space-y-6">
        @csrf
        <input type="hidden" name="source_type" :value="sourceType">

        <!-- 1. Pilih Sumber Transaksi -->
        <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-rose-500/20 border border-rose-500/40 flex items-center justify-center shrink-0">
                        <span class="text-xs font-black text-rose-400">1</span>
                    </div>
                    <h3 class="text-sm font-black text-rose-400 uppercase tracking-wider">Pilih Sumber Transaksi Penjualan</h3>
                </div>

                <!-- Source Switcher Tabs -->
                <div class="flex p-1 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold">
                    <button type="button" @click="setSourceType('invoice')"
                        :class="sourceType === 'invoice' ? 'bg-rose-500 text-white shadow-md' : 'text-slate-400 hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                        <span>Faktur Penjualan (B2B)</span>
                    </button>
                    <button type="button" @click="setSourceType('pos_order')"
                        :class="sourceType === 'pos_order' ? 'bg-rose-500 text-white shadow-md' : 'text-slate-400 hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                        <span>Kasir POS (Struk Kasir)</span>
                    </button>
                </div>
            </div>

            <!-- Invoice Selection Form -->
            <div x-show="sourceType === 'invoice'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">
                        Pilih Faktur Penjualan (Invoice) <span class="text-rose-400">*</span>
                    </label>
                    <select name="invoice_id" x-model="selectedInvoiceId" @change="onInvoiceChange()" :required="sourceType === 'invoice'"
                        class="w-full bg-slate-900 border border-slate-700 focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-white text-xs">
                        <option value="">— Pilih Dokumen Faktur —</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} — {{ $invoice->customer?->name ?? 'Pelanggan Umum' }} (Rp {{ number_format($invoice->total_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Info Box Invoice Terpilih -->
                <div x-show="selectedRecord && sourceType === 'invoice'" x-cloak class="flex items-center">
                    <div class="w-full glass-card rounded-xl p-3 border border-rose-500/30 bg-rose-500/5 text-xs space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-rose-400">Detail Faktur</span>
                            <span class="font-mono text-white font-bold" x-text="selectedRecord?.number"></span>
                        </div>
                        <div class="flex justify-between text-slate-400 text-[11px]">
                            <span>Pelanggan: <strong class="text-slate-200" x-text="selectedRecord?.customer_name"></strong></span>
                            <span>Tgl: <span x-text="selectedRecord?.date"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- POS Order Selection Form -->
            <div x-show="sourceType === 'pos_order'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">
                        Pilih Transaksi Kasir POS <span class="text-rose-400">*</span>
                    </label>
                    <select name="pos_order_id" x-model="selectedPosOrderId" @change="onPosOrderChange()" :required="sourceType === 'pos_order'"
                        class="w-full bg-slate-900 border border-slate-700 focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-white text-xs">
                        <option value="">— Pilih Transaksi Kasir POS —</option>
                        @foreach($posOrders as $order)
                            <option value="{{ $order->id }}">
                                {{ $order->order_number }} — {{ $order->customer?->name ?? ($order->customer_name_guest ?: 'Tamu Kasir') }} (Rp {{ number_format($order->total_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Info Box POS Order Terpilih -->
                <div x-show="selectedRecord && sourceType === 'pos_order'" x-cloak class="flex items-center">
                    <div class="w-full glass-card rounded-xl p-3 border border-rose-500/30 bg-rose-500/5 text-xs space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-rose-400">Detail Transaksi POS</span>
                            <span class="font-mono text-white font-bold" x-text="selectedRecord?.number"></span>
                        </div>
                        <div class="flex justify-between text-slate-400 text-[11px]">
                            <span>Pelanggan: <strong class="text-slate-200" x-text="selectedRecord?.customer_name"></strong></span>
                            <span>Waktu: <span x-text="selectedRecord?.date"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Rincian Item yang Diretur -->
        <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-rose-500/20 border border-rose-500/40 flex items-center justify-center shrink-0">
                        <span class="text-xs font-black text-rose-400">2</span>
                    </div>
                    <h3 class="text-sm font-black text-rose-400 uppercase tracking-wider">Pilih Item yang Dikembalikan</h3>
                </div>
                <div class="flex items-center gap-2" x-show="availableItems.length > 0">
                    <button type="button" @click="toggleSelectAll()" class="text-xs text-rose-400 hover:text-rose-300 font-semibold transition cursor-pointer">
                        <span x-text="isAllSelected() ? 'Batal Pilih Semua' : 'Pilih Semua Item'"></span>
                    </button>
                </div>
            </div>

            <!-- State Belum Pilih Transaksi -->
            <div x-show="!hasSelectedSource()" class="py-12 text-center text-slate-500 text-xs font-sans">
                <div class="flex flex-col items-center justify-center gap-2">
                    <i data-lucide="package-search" class="w-8 h-8 text-slate-600"></i>
                    <span>Silakan pilih transaksi penjualan di atas untuk memuat daftar item produk.</span>
                </div>
            </div>

            <!-- State Dipilih tapi Tidak Ada Item Tersisa -->
            <div x-show="hasSelectedSource() && availableItems.length === 0" x-cloak class="py-8 text-center text-amber-400 text-xs font-sans">
                <span>Transaksi ini tidak memiliki item atau semua item sudah selesai direfund/diretur sebelumnya.</span>
            </div>

            <!-- Tabel Item Transaksi -->
            <div x-show="availableItems.length > 0" x-cloak class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">Retur</th>
                            <th class="py-2.5 px-3">Produk / Item</th>
                            <th class="py-2.5 px-3 text-right w-28">Harga (Rp)</th>
                            <th class="py-2.5 px-3 text-center w-24">Qty Asal</th>
                            <th class="py-2.5 px-3 text-center w-28">Sisa Dpt Diretur</th>
                            <th class="py-2.5 px-3 text-right w-32">Qty Retur</th>
                            <th class="py-2.5 px-3 text-right w-36">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        <template x-for="(item, idx) in availableItems" :key="item.item_id">
                            <tr :class="item.selected ? 'bg-rose-500/5' : 'hover:bg-slate-800/20'" class="transition">
                                <!-- Checkbox -->
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox"
                                        :name="'items['+idx+'][selected]'"
                                        value="1"
                                        x-model="item.selected"
                                        :disabled="item.max_qty <= 0"
                                        @change="onItemToggle(item)"
                                        class="rounded border-slate-700 text-rose-500 focus:ring-rose-500/20 bg-slate-900 w-4 h-4 cursor-pointer disabled:opacity-30">
                                    
                                    <!-- Input hidden item id sesuai sourceType -->
                                    <template x-if="sourceType === 'invoice'">
                                        <input type="hidden" :name="'items['+idx+'][invoice_item_id]'" :value="item.item_id">
                                    </template>
                                    <template x-if="sourceType === 'pos_order'">
                                        <input type="hidden" :name="'items['+idx+'][pos_order_item_id]'" :value="item.item_id">
                                    </template>
                                </td>

                                <!-- Nama Produk -->
                                <td class="py-3 px-3 font-sans">
                                    <div class="font-bold text-white" x-text="item.product_name"></div>
                                    <div class="text-[10px] text-slate-500 font-mono" x-show="item.sku && item.sku !== '-'" x-text="'SKU/Kode: ' + item.sku"></div>
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-3 px-3 text-right text-slate-300" x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></td>

                                <!-- Qty Asal -->
                                <td class="py-3 px-3 text-center text-slate-400" x-text="item.original_qty"></td>

                                <!-- Sisa Yang Bisa Diretur -->
                                <td class="py-3 px-3 text-center font-bold" :class="item.max_qty > 0 ? 'text-emerald-400' : 'text-slate-600'">
                                    <span x-text="item.max_qty"></span>
                                    <template x-if="item.returned_qty > 0">
                                        <span class="text-[10px] text-slate-500 block font-normal" x-text="'(Pernah: ' + item.returned_qty + ')'"></span>
                                    </template>
                                </td>

                                <!-- Input Qty Retur -->
                                <td class="py-3 px-3">
                                    <template x-if="item.max_qty > 0">
                                        <input type="number"
                                            :name="'items['+idx+'][quantity]'"
                                            x-model.number="item.quantity"
                                            :disabled="!item.selected"
                                            @input="validateItemQty(item)"
                                            min="0.01"
                                            :max="item.max_qty"
                                            step="any"
                                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-right text-white focus:outline-none focus:border-rose-500 disabled:opacity-40 disabled:bg-slate-950 font-mono">
                                    </template>
                                    <template x-if="item.max_qty <= 0">
                                        <span class="text-[10px] text-rose-400/80 font-sans block text-right font-bold">Habis Diretur</span>
                                    </template>
                                </td>

                                <!-- Subtotal Retur Baris -->
                                <td class="py-3 px-3 text-right font-bold text-rose-400"
                                    x-text="item.selected && item.quantity > 0 ? 'Rp ' + Math.round(item.quantity * item.unit_price).toLocaleString('id-ID') : 'Rp 0'">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Total Retur Ringkasan -->
            <div x-show="availableItems.length > 0" x-cloak class="pt-4 border-t border-slate-800 flex justify-between items-center font-sans">
                <div class="text-xs text-slate-400">
                    <span>Item dipilih: </span>
                    <strong class="text-white font-mono" x-text="getSelectedCount()"></strong> item
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Nilai Retur:</span>
                    <span class="text-lg font-black font-mono text-rose-400" x-text="'Rp ' + Math.round(calculateTotalReturn()).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </div>

        <!-- 3. Metode Pengembalian & Alasan -->
        <div class="glass-card rounded-2xl border border-slate-800 p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-rose-500/20 border border-rose-500/40 flex items-center justify-center shrink-0">
                    <span class="text-xs font-black text-rose-400">3</span>
                </div>
                <h3 class="text-sm font-black text-rose-400 uppercase tracking-wider">Kompensasi &amp; Alasan Pengembalian</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-sans">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">
                        Metode Pengembalian Dana / Kompensasi <span class="text-rose-400">*</span>
                    </label>
                    <select name="refund_method" x-model="refundMethod" required class="w-full bg-slate-900 border border-slate-700 focus:border-rose-500 rounded-xl px-4 py-2.5 text-white text-xs">
                        <option value="cash_refund">Refund Kas / Tunai Langsung</option>
                        <option value="credit_note">Credit Note (Potongan Tagihan / Faktur Berikutnya)</option>
                        <option value="store_credit">Store Credit / Saldo Deposit Pelanggan</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-300">
                        Alasan Retur Penjualan <span class="text-rose-400">*</span>
                    </label>
                    <input name="reason" placeholder="Contoh: Barang cacat, rusak saat pengiriman, salah varian" required
                        class="w-full bg-slate-900 border border-slate-700 focus:border-rose-500 rounded-xl px-4 py-2.5 text-white text-xs">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 pb-6">
            <a href="{{ route('sales.returns.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                Batal
            </a>
            <button type="submit"
                :disabled="!hasSelectedSource() || getSelectedCount() === 0"
                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-400 hover:to-pink-400 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs shadow-lg shadow-rose-500/20 transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Simpan Draft Retur</span>
            </button>
        </div>
    </form>
</div>

<script>
    // Safe server data declarations without inline attribute string injection
    window.RETURN_INVOICES_DATA = {!! json_encode($invoicesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.RETURN_POS_ORDERS_DATA = {!! json_encode($posOrdersData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.RETURN_INIT_SOURCE = @json($initialSourceType);
    window.RETURN_PREFILL_INV = @json($prefillInvoiceId);
    window.RETURN_PREFILL_POS = @json($prefillPosOrderId);

    function salesReturnForm() {
        return {
            sourceType: window.RETURN_INIT_SOURCE || 'invoice',
            invoices: window.RETURN_INVOICES_DATA || [],
            posOrders: window.RETURN_POS_ORDERS_DATA || [],
            selectedInvoiceId: window.RETURN_PREFILL_INV || '',
            selectedPosOrderId: window.RETURN_PREFILL_POS || '',
            selectedRecord: null,
            availableItems: [],
            refundMethod: 'cash_refund',

            init() {
                if (this.sourceType === 'pos_order' && this.selectedPosOrderId) {
                    this.onPosOrderChange();
                } else if (this.sourceType === 'invoice' && this.selectedInvoiceId) {
                    this.onInvoiceChange();
                }
            },

            setSourceType(type) {
                this.sourceType = type;
                this.selectedRecord = null;
                this.availableItems = [];
                if (type === 'invoice') {
                    this.refundMethod = 'credit_note';
                    if (this.selectedInvoiceId) this.onInvoiceChange();
                } else {
                    this.refundMethod = 'cash_refund';
                    if (this.selectedPosOrderId) this.onPosOrderChange();
                }
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            hasSelectedSource() {
                return this.sourceType === 'invoice' ? !!this.selectedInvoiceId : !!this.selectedPosOrderId;
            },

            onInvoiceChange() {
                if (!this.selectedInvoiceId) {
                    this.selectedRecord = null;
                    this.availableItems = [];
                    return;
                }

                const inv = this.invoices.find(i => String(i.id) === String(this.selectedInvoiceId));
                if (inv) {
                    this.selectedRecord = inv;
                    this.availableItems = (inv.items || []).map(it => ({
                        ...it,
                        selected: it.max_qty > 0,
                        quantity: it.max_qty > 0 ? (it.max_qty >= 1 ? 1 : it.max_qty) : 0
                    }));
                } else {
                    this.selectedRecord = null;
                    this.availableItems = [];
                }

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            onPosOrderChange() {
                if (!this.selectedPosOrderId) {
                    this.selectedRecord = null;
                    this.availableItems = [];
                    return;
                }

                const order = this.posOrders.find(o => String(o.id) === String(this.selectedPosOrderId));
                if (order) {
                    this.selectedRecord = order;
                    this.availableItems = (order.items || []).map(it => ({
                        ...it,
                        selected: it.max_qty > 0,
                        quantity: it.max_qty > 0 ? (it.max_qty >= 1 ? 1 : it.max_qty) : 0
                    }));
                } else {
                    this.selectedRecord = null;
                    this.availableItems = [];
                }

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            onItemToggle(item) {
                if (item.selected && (!item.quantity || item.quantity <= 0)) {
                    item.quantity = item.max_qty >= 1 ? 1 : item.max_qty;
                }
            },

            validateItemQty(item) {
                if (item.quantity > item.max_qty) {
                    item.quantity = item.max_qty;
                }
                if (item.quantity < 0) {
                    item.quantity = 0;
                }
            },

            isAllSelected() {
                const returnable = this.availableItems.filter(i => i.max_qty > 0);
                return returnable.length > 0 && returnable.every(i => i.selected);
            },

            toggleSelectAll() {
                const nextState = !this.isAllSelected();
                this.availableItems.forEach(it => {
                    if (it.max_qty > 0) {
                        it.selected = nextState;
                        if (nextState && (!item.quantity || item.quantity <= 0)) {
                            item.quantity = it.max_qty >= 1 ? 1 : it.max_qty;
                        }
                    }
                });
            },

            getSelectedCount() {
                return this.availableItems.filter(i => i.selected && i.quantity > 0).length;
            },

            calculateTotalReturn() {
                return this.availableItems.reduce((sum, it) => {
                    if (it.selected && it.quantity > 0) {
                        return sum + (it.quantity * it.unit_price);
                    }
                    return sum;
                }, 0);
            },

            validateForm(event) {
                if (!this.hasSelectedSource()) {
                    AppAlert.warning('Silakan pilih dokumen transaksi penjualan terlebih dahulu.');
                    event.preventDefault();
                    return false;
                }
                if (this.getSelectedCount() === 0) {
                    AppAlert.warning('Pilih minimal 1 item dengan kuantitas lebih dari 0 untuk diretur.');
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        };
    }
</script>
@endsection
