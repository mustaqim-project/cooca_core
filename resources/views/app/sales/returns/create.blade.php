@extends('layouts.app', [
    'title' => 'Buat Retur Penjualan — Cooca UMKM',
    'headerTitle' => 'Formulir Retur Penjualan',
    'headerSubtitle' => 'Pilih sumber transaksi (Faktur Penjualan atau Kasir POS), tentukan kuantitas yang diretur, dan atur kompensasi pengembalian dana.'
])

@section('content')
<div class="max-w-[1100px] mx-auto space-y-6 pb-12" x-data="salesReturnForm()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('sales.returns.index') }}" class="hover:text-[#007AFF] transition-colors">Retur Penjualan</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Buat Retur Baru</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Formulir Retur Penjualan</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Pengembalian barang atas transaksi faktur penjualan B2B atau struk kasir POS.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sales.returns.index') }}"
                class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                <span>Batal</span>
            </a>
            <button type="button" @click="promptSaveReturn()"
                :disabled="!hasSelectedSource() || getSelectedCount() === 0"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>Simpan Draft Retur</span>
            </button>
        </div>
    </header>

    <!-- Error Alert Box -->
    @if($errors->any())
    <div class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 p-4 text-[13px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
        <div class="flex items-center gap-2 font-semibold">
            <span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span>
            <span>Harap periksa isian formulir berikut:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 pl-4 text-[12px]">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="form-create-sales-return" method="POST" action="{{ route('sales.returns.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="source_type" :value="sourceType">

        <!-- 1. PILIH SUMBER TRANSAKSI -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                <div>
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">1. Pilih Sumber Transaksi Penjualan</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tentukan apakah retur berasal dari Faktur B2B resmi atau Struk Transaksi Kasir POS.</p>
                </div>

                <!-- Source Switcher Segmented Control -->
                <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium shrink-0">
                    <button type="button" @click="setSourceType('invoice')"
                        :class="sourceType === 'invoice' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                        class="px-3 py-1 rounded-[7px] transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>Faktur B2B</span>
                    </button>
                    <button type="button" @click="setSourceType('pos_order')"
                        :class="sourceType === 'pos_order' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                        class="px-3 py-1 rounded-[7px] transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>Kasir POS (Struk)</span>
                    </button>
                </div>
            </div>

            <!-- Invoice Selection Dropdown -->
            <div x-show="sourceType === 'invoice'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Pilih Dokumen Faktur Penjualan (Invoice) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="invoice_id" x-model="selectedInvoiceId" @change="onInvoiceChange()" :required="sourceType === 'invoice'"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
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
                    <div class="w-full p-3.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/15 text-[12px] space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">Faktur Terpilih</span>
                            <span class="font-mono text-black dark:text-white font-bold" x-text="selectedRecord?.number"></span>
                        </div>
                        <div class="flex justify-between text-black/60 dark:text-white/60 text-[12px]">
                            <span>Pelanggan: <strong class="text-black dark:text-white" x-text="selectedRecord?.customer_name"></strong></span>
                            <span>Tgl: <span class="tabular-nums" x-text="selectedRecord?.date"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- POS Order Selection Dropdown -->
            <div x-show="sourceType === 'pos_order'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Pilih Transaksi Kasir POS <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="pos_order_id" x-model="selectedPosOrderId" @change="onPosOrderChange()" :required="sourceType === 'pos_order'"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
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
                    <div class="w-full p-3.5 rounded-[10px] bg-[#5856D6]/8 border border-[#5856D6]/15 text-[12px] space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] font-semibold text-[#5856D6] uppercase tracking-wider">Transaksi Kasir POS</span>
                            <span class="font-mono text-black dark:text-white font-bold" x-text="selectedRecord?.number"></span>
                        </div>
                        <div class="flex justify-between text-black/60 dark:text-white/60 text-[12px]">
                            <span>Pelanggan: <strong class="text-black dark:text-white" x-text="selectedRecord?.customer_name"></strong></span>
                            <span>Waktu: <span class="tabular-nums" x-text="selectedRecord?.date"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. RINCIAN ITEM YANG DIRETUR -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                <div>
                    <h2 class="text-[17px] font-semibold text-black dark:text-white">2. Pilih Item yang Dikembalikan</h2>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Centang produk yang ingin diretur dan sesuaikan jumlah kuantitas pengembalian.</p>
                </div>
                <div class="flex items-center gap-2" x-show="availableItems.length > 0">
                    <button type="button" @click="toggleSelectAll()"
                        class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition cursor-pointer">
                        <span x-text="isAllSelected() ? 'Batal Pilih Semua' : 'Pilih Semua Item'"></span>
                    </button>
                </div>
            </div>

            <!-- State Belum Pilih Transaksi -->
            <div x-show="!hasSelectedSource()" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                <div class="flex flex-col items-center justify-center gap-2">
                    <svg class="w-8 h-8 text-black/25 dark:text-white/25 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span>Silakan pilih sumber transaksi penjualan di atas untuk memuat rincian item produk.</span>
                </div>
            </div>

            <!-- State Dipilih tapi Tidak Ada Item Tersisa -->
            <div x-show="hasSelectedSource() && availableItems.length === 0" x-cloak class="p-4 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-[13px] text-[#B25E00] dark:text-[#FF9F0A] text-center">
                Transaksi ini tidak memiliki item atau seluruh item sudah selesai diretur sebelumnya.
            </div>

            <!-- Tabel Item Transaksi -->
            <div x-show="availableItems.length > 0" x-cloak class="overflow-x-auto rounded-[10px] border border-black/5 dark:border-white/10">
                <table class="w-full text-left text-[13px] min-w-[700px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                            <th class="py-2.5 px-3 w-12 text-center">Retur</th>
                            <th class="py-2.5 px-3">Produk / Item</th>
                            <th class="py-2.5 px-3 text-right w-28">Harga Satuan</th>
                            <th class="py-2.5 px-3 text-center w-24">Qty Beli</th>
                            <th class="py-2.5 px-3 text-center w-28">Batas Retur</th>
                            <th class="py-2.5 px-3 text-right w-32">Qty Retur</th>
                            <th class="py-2.5 px-3 text-right w-36">Subtotal Retur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        <template x-for="(item, idx) in availableItems" :key="item.item_id">
                            <tr :class="item.selected ? 'bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.05]' : 'hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'" class="transition-colors">
                                <!-- Checkbox -->
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox"
                                        :name="'items['+idx+'][selected]'"
                                        value="1"
                                        x-model="item.selected"
                                        :disabled="item.max_qty <= 0"
                                        @change="onItemToggle(item)"
                                        class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF] w-4 h-4 cursor-pointer disabled:opacity-20">
                                    
                                    <!-- Input hidden item id sesuai sourceType -->
                                    <template x-if="sourceType === 'invoice'">
                                        <input type="hidden" :name="'items['+idx+'][invoice_item_id]'" :value="item.item_id">
                                    </template>
                                    <template x-if="sourceType === 'pos_order'">
                                        <input type="hidden" :name="'items['+idx+'][pos_order_item_id]'" :value="item.item_id">
                                    </template>
                                </td>

                                <!-- Nama Produk -->
                                <td class="py-3 px-3">
                                    <div class="font-medium text-black dark:text-white" x-text="item.product_name"></div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums" x-show="item.sku && item.sku !== '-'" x-text="'SKU: ' + item.sku"></div>
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-3 px-3 text-right tabular-nums text-black/60 dark:text-white/60" x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></td>

                                <!-- Qty Asal -->
                                <td class="py-3 px-3 text-center tabular-nums text-black/50 dark:text-white/50" x-text="item.original_qty"></td>

                                <!-- Sisa Yang Bisa Diretur -->
                                <td class="py-3 px-3 text-center tabular-nums font-semibold" :class="item.max_qty > 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/30 dark:text-white/30'">
                                    <span x-text="item.max_qty"></span>
                                    <template x-if="item.returned_qty > 0">
                                        <span class="text-[10px] text-black/40 dark:text-white/40 block font-normal" x-text="'(Pernah: ' + item.returned_qty + ')'"></span>
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
                                            class="w-full h-8 text-right tabular-nums bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[6px] px-2 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 disabled:opacity-30">
                                    </template>
                                    <template x-if="item.max_qty <= 0">
                                        <span class="text-[11px] text-[#FF3B30] block text-right font-medium">Habis Diretur</span>
                                    </template>
                                </td>

                                <!-- Subtotal Retur Baris -->
                                <td class="py-3 px-3 text-right font-semibold tabular-nums text-[#FF3B30] whitespace-nowrap"
                                    x-text="item.selected && item.quantity > 0 ? 'Rp ' + Math.round(item.quantity * item.unit_price).toLocaleString('id-ID') : 'Rp 0'">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Total Retur Ringkasan -->
            <div x-show="availableItems.length > 0" x-cloak class="pt-3 border-t border-black/5 dark:border-white/5 flex justify-between items-center text-[13px]">
                <div class="text-black/50 dark:text-white/50">
                    <span>Item dipilih: </span>
                    <strong class="text-black dark:text-white tabular-nums" x-text="getSelectedCount()"></strong> item
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-black/60 dark:text-white/60">Total Nilai Retur:</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#FF3B30]" x-text="'Rp ' + Math.round(calculateTotalReturn()).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </div>

        <!-- 3. METODE PENGEMBALIAN & ALASAN -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                <h2 class="text-[17px] font-semibold text-black dark:text-white">3. Kompensasi &amp; Alasan Pengembalian</h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Pilih bentuk pengembalian dana bagi pelanggan serta dokumentasikan alasan retur barang.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Metode Kompensasi / Pengembalian Dana <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="refund_method" x-model="refundMethod" required
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition cursor-pointer">
                        <option value="cash_refund">Refund Kas / Tunai Langsung</option>
                        <option value="credit_note">Credit Note (Potongan Tagihan Faktur Berikutnya)</option>
                        <option value="store_credit">Store Credit / Saldo Deposit Pelanggan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                        Alasan Retur Penjualan <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input name="reason" placeholder="Contoh: Barang rusak saat pengiriman, salah varian, cacat pabrik..." required
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- Action Submit Bar -->
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('sales.returns.index') }}"
                class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                Batal
            </a>
            <button type="button" @click="promptSaveReturn()"
                :disabled="!hasSelectedSource() || getSelectedCount() === 0"
                class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.97] active:opacity-80 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                Simpan Draft Retur
            </button>
        </div>
    </form>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG: KONFIRMASI DRAFT RETUR            -->
    <!-- ===================================================== -->
    <div x-show="confirmReturnModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="confirmReturnModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Simpan Draft Retur?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Simpan dokumen draft retur ini? Dokumen akan menunggu persetujuan manajemen untuk pemeriksaan fisik.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="confirmReturnModalOpen = false" class="py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitReturn()" class="py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </div>

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
            confirmReturnModalOpen: false,

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
                            it.quantity = Math.min(1, item.max_qty);
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

            promptSaveReturn() {
                const count = this.getSelectedCount();
                if (count === 0) {
                    alert('Pilih setidaknya 1 item produk dengan kuantitas lebih dari 0.');
                    return;
                }
                this.confirmReturnModalOpen = true;
            },

            submitReturn() {
                this.confirmReturnModalOpen = false;
                document.getElementById('form-create-sales-return').submit();
            }
        };
    }
</script>
@endsection
