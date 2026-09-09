@extends('layouts.app', [
    'title' => 'Buat Retur Penjualan — Cooca UMKM',
    'headerTitle' => 'Formulir Retur Penjualan',
    'headerSubtitle' => 'Pilih sumber transaksi (Faktur Penjualan atau Kasir POS), tentukan kuantitas yang diretur, dan atur kompensasi pengembalian dana.'
])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="salesReturnForm()">

    <!-- Standard Breadcrumb & Header Bar -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800/80" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-rose-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <a href="{{ route('sales.returns.index') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-rose-500 rounded px-1">
                    Retur Penjualan
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-900 dark:text-slate-200 font-semibold" aria-current="page">Buat Retur Baru</span>
            </li>
        </ol>

        <a href="{{ route('sales.returns.index') }}"
            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4" aria-hidden="true"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </nav>

    <!-- Error Alert Box -->
    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-300 text-xs space-y-1">
        <div class="flex items-center gap-2 font-bold text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
            <span>Harap periksa isian formulir berikut:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 pl-6 pt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('sales.returns.store') }}" @submit="return validateForm($event)" class="space-y-6">
        @csrf
        <input type="hidden" name="source_type" :value="sourceType">

        <!-- 1. Pilih Sumber Transaksi -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
                        <span class="text-xs font-black">1</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pilih Sumber Transaksi Penjualan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tentukan apakah retur berasal dari Faktur B2B resmi atau Struk Transaksi Kasir POS.</p>
                    </div>
                </div>

                <!-- Source Switcher Tabs -->
                <div class="flex p-1 rounded-xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold shrink-0">
                    <button type="button" @click="setSourceType('invoice')"
                        :class="sourceType === 'invoice' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="receipt" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Faktur Penjualan (B2B)</span>
                    </button>
                    <button type="button" @click="setSourceType('pos_order')"
                        :class="sourceType === 'pos_order' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Kasir POS (Struk)</span>
                    </button>
                </div>
            </div>

            <!-- Invoice Selection Dropdown -->
            <div x-show="sourceType === 'invoice'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Pilih Dokumen Faktur Penjualan (Invoice) <span class="text-rose-500">*</span>
                    </label>
                    <select name="invoice_id" x-model="selectedInvoiceId" @change="onInvoiceChange()" :required="sourceType === 'invoice'"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-rose-500 transition cursor-pointer">
                        <option value="">— Pilih Dokumen Faktur —</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}">
                                {{ $invoice->invoice_number }} — {{ $invoice->customer?->name ?? 'Pelanggan Umum' }} (Rp {{ number_format($invoice->total_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Info Box Invoice Terpilih -->
                <div x-show="selectedRecord && sourceType === 'invoice'" x-cloak class="flex items-end">
                    <div class="w-full p-3.5 rounded-xl bg-rose-50/70 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-xs space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-rose-700 dark:text-rose-400">Rincian Faktur Terpilih</span>
                            <span class="font-mono text-slate-900 dark:text-white font-bold" x-text="selectedRecord?.number"></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400 text-[11px]">
                            <span>Pelanggan: <strong class="text-slate-900 dark:text-slate-200" x-text="selectedRecord?.customer_name"></strong></span>
                            <span>Tgl: <span x-text="selectedRecord?.date"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- POS Order Selection Dropdown -->
            <div x-show="sourceType === 'pos_order'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Pilih Transaksi Kasir POS <span class="text-rose-500">*</span>
                    </label>
                    <select name="pos_order_id" x-model="selectedPosOrderId" @change="onPosOrderChange()" :required="sourceType === 'pos_order'"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-rose-500 transition cursor-pointer">
                        <option value="">— Pilih Transaksi Kasir POS —</option>
                        @foreach($posOrders as $order)
                            <option value="{{ $order->id }}">
                                {{ $order->order_number }} — {{ $order->customer?->name ?? ($order->customer_name_guest ?: 'Tamu Kasir') }} (Rp {{ number_format($order->total_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Info Box POS Order Terpilih -->
                <div x-show="selectedRecord && sourceType === 'pos_order'" x-cloak class="flex items-end">
                    <div class="w-full p-3.5 rounded-xl bg-rose-50/70 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 text-xs space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase font-bold text-rose-700 dark:text-rose-400">Rincian Transaksi Kasir POS</span>
                            <span class="font-mono text-slate-900 dark:text-white font-bold" x-text="selectedRecord?.number"></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400 text-[11px]">
                            <span>Pelanggan: <strong class="text-slate-900 dark:text-slate-200" x-text="selectedRecord?.customer_name"></strong></span>
                            <span>Waktu: <span x-text="selectedRecord?.date"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Rincian Item yang Diretur -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
                        <span class="text-xs font-black">2</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pilih Item yang Dikembalikan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Centang produk yang ingin diretur dan sesuaikan jumlah kuantitas pengembalian.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" x-show="availableItems.length > 0">
                    <button type="button" @click="toggleSelectAll()"
                        class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition cursor-pointer">
                        <span x-text="isAllSelected() ? 'Batal Pilih Semua' : 'Pilih Semua Item'"></span>
                    </button>
                </div>
            </div>

            <!-- State Belum Pilih Transaksi -->
            <div x-show="!hasSelectedSource()" class="py-12 text-center text-slate-500 dark:text-slate-400 text-xs">
                <div class="flex flex-col items-center justify-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                        <i data-lucide="package-search" class="w-5 h-5" aria-hidden="true"></i>
                    </div>
                    <span class="font-medium">Silakan pilih sumber transaksi penjualan di atas untuk memuat rincian item produk.</span>
                </div>
            </div>

            <!-- State Dipilih tapi Tidak Ada Item Tersisa -->
            <div x-show="hasSelectedSource() && availableItems.length === 0" x-cloak class="py-8 text-center text-amber-700 dark:text-amber-400 text-xs bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20">
                <span class="font-medium">Transaksi ini tidak memiliki item atau seluruh item sudah selesai diretur sebelumnya.</span>
            </div>

            <!-- Tabel Item Transaksi -->
            <div x-show="availableItems.length > 0" x-cloak class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 whitespace-nowrap">
                        <tr>
                            <th scope="col" class="py-2.5 px-3 w-12 text-center">Retur</th>
                            <th scope="col" class="py-2.5 px-3">Produk / Item</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-28">Harga Satuan</th>
                            <th scope="col" class="py-2.5 px-3 text-center w-24">Qty Beli</th>
                            <th scope="col" class="py-2.5 px-3 text-center w-28">Batas Retur</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-32">Qty Retur</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-36">Subtotal Retur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                        <template x-for="(item, idx) in availableItems" :key="item.item_id">
                            <tr :class="item.selected ? 'bg-rose-50/50 dark:bg-rose-500/5' : 'hover:bg-slate-50/80 dark:hover:bg-slate-800/20'" class="transition-colors">
                                <!-- Checkbox -->
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox"
                                        :name="'items['+idx+'][selected]'"
                                        value="1"
                                        x-model="item.selected"
                                        :disabled="item.max_qty <= 0"
                                        @change="onItemToggle(item)"
                                        class="rounded border-slate-300 dark:border-slate-700 text-rose-600 focus:ring-rose-500 bg-white dark:bg-slate-900 w-4 h-4 cursor-pointer disabled:opacity-30">
                                    
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
                                    <div class="font-bold text-slate-900 dark:text-white" x-text="item.product_name"></div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" x-show="item.sku && item.sku !== '-'" x-text="'SKU/Kode: ' + item.sku"></div>
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400" x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></td>

                                <!-- Qty Asal -->
                                <td class="py-3 px-3 text-center text-slate-500 dark:text-slate-400" x-text="item.original_qty"></td>

                                <!-- Sisa Yang Bisa Diretur -->
                                <td class="py-3 px-3 text-center font-bold" :class="item.max_qty > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                                    <span x-text="item.max_qty"></span>
                                    <template x-if="item.returned_qty > 0">
                                        <span class="text-[10px] text-slate-400 block font-normal" x-text="'(Pernah: ' + item.returned_qty + ')'"></span>
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
                                            class="w-full px-2.5 py-1.5 rounded-lg text-xs text-right bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-rose-500 disabled:opacity-40 disabled:bg-slate-100 dark:disabled:bg-slate-950 font-mono transition">
                                    </template>
                                    <template x-if="item.max_qty <= 0">
                                        <span class="text-[10px] text-rose-600 dark:text-rose-400 font-sans block text-right font-bold">Habis Diretur</span>
                                    </template>
                                </td>

                                <!-- Subtotal Retur Baris -->
                                <td class="py-3 px-3 text-right font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap"
                                    x-text="item.selected && item.quantity > 0 ? 'Rp ' + Math.round(item.quantity * item.unit_price).toLocaleString('id-ID') : 'Rp 0'">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Total Retur Ringkasan -->
            <div x-show="availableItems.length > 0" x-cloak class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center font-sans">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    <span>Item dipilih: </span>
                    <strong class="text-slate-900 dark:text-white font-mono font-bold" x-text="getSelectedCount()"></strong> item
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Nilai Retur:</span>
                    <span class="text-lg font-black font-mono text-rose-600 dark:text-rose-400" x-text="'Rp ' + Math.round(calculateTotalReturn()).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </div>

        <!-- 3. Metode Pengembalian & Alasan -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
                    <span class="text-xs font-black">3</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kompensasi &amp; Alasan Pengembalian</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih bentuk pengembalian dana bagi pelanggan serta dokumentasikan alasan retur.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-sans pt-1">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Metode Pengembalian Dana / Kompensasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="refund_method" x-model="refundMethod" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-rose-500 transition cursor-pointer">
                        <option value="cash_refund">Refund Kas / Tunai Langsung</option>
                        <option value="credit_note">Credit Note (Potongan Tagihan / Faktur Berikutnya)</option>
                        <option value="store_credit">Store Credit / Saldo Deposit Pelanggan</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Alasan Retur Penjualan <span class="text-rose-500">*</span>
                    </label>
                    <input name="reason" placeholder="Contoh: Barang rusak saat pengiriman, salah varian, cacat pabrik..." required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-rose-500 transition">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2 pb-8">
            <a href="{{ route('sales.returns.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                Batal
            </a>
            <button type="submit"
                :disabled="!hasSelectedSource() || getSelectedCount() === 0"
                class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-sm shadow-rose-600/20 transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="check-circle" class="w-4 h-4" aria-hidden="true"></i>
                <span>Simpan Draft Retur</span>
            </button>
        </div>
    </form>
</div>

<script>
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
                const found = this.invoices.find(inv => String(inv.id) === String(this.selectedInvoiceId));
                if (found) {
                    this.selectedRecord = found;
                    this.availableItems = found.items.map(it => ({ ...it, selected: false }));
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
                const found = this.posOrders.find(ord => String(ord.id) === String(this.selectedPosOrderId));
                if (found) {
                    this.selectedRecord = found;
                    this.availableItems = found.items.map(it => ({ ...it, selected: false }));
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
                    item.quantity = item.max_qty > 0 ? Math.min(1, item.max_qty) : 0;
                }
            },

            validateItemQty(item) {
                if (item.quantity > item.max_qty) {
                    item.quantity = item.max_qty;
                }
                if (item.quantity < 0) {
                    item.quantity = 0;
                }
                if (item.quantity > 0) {
                    item.selected = true;
                }
            },

            toggleSelectAll() {
                const allSelected = this.isAllSelected();
                this.availableItems.forEach(it => {
                    if (it.max_qty > 0) {
                        it.selected = !allSelected;
                        if (it.selected && (!it.quantity || it.quantity <= 0)) {
                            it.quantity = Math.min(1, it.max_qty);
                        }
                    }
                });
            },

            isAllSelected() {
                const selectable = this.availableItems.filter(it => it.max_qty > 0);
                if (selectable.length === 0) return false;
                return selectable.every(it => it.selected);
            },

            getSelectedCount() {
                return this.availableItems.filter(it => it.selected && it.quantity > 0).length;
            },

            calculateTotalReturn() {
                return this.availableItems.reduce((acc, it) => {
                    if (it.selected && it.quantity > 0) {
                        return acc + (it.quantity * it.unit_price);
                    }
                    return acc;
                }, 0);
            },

            validateForm(e) {
                const count = this.getSelectedCount();
                if (count === 0) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Pilih Item Retur',
                            text: 'Pilih setidaknya 1 item produk dengan kuantitas lebih dari 0 untuk membuat retur.',
                            icon: 'warning',
                            confirmButtonColor: '#e11d48'
                        });
                    } else {
                        alert('Pilih setidaknya 1 item produk dengan kuantitas lebih dari 0.');
                    }
                    return false;
                }
                return AppAlert.confirmSubmit(e, e.target, 'Simpan draft retur penjualan ini? Dokumen akan menunggu persetujuan manajemen.', 'Simpan Draft Retur?');
            }
        };
    }
</script>
@endsection
