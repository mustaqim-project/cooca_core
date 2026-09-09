@extends('layouts.app', [
    'title' => 'Buat Pesanan Penjualan (SO) — Cooca UMKM',
    'headerTitle' => 'Buat Pesanan Penjualan',
    'headerSubtitle' => 'Buat pesanan penjualan terkonfirmasi baru — langsung atau dari konversi penawaran harga (Quotation).'
])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="salesOrderForm()">

    <!-- Standard Breadcrumb & Header Bar -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800/80" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-cyan-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <a href="{{ route('sales.orders.index') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-cyan-500 rounded px-1">
                    Sales Orders
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-900 dark:text-slate-200 font-semibold" aria-current="page">Buat Baru</span>
            </li>
        </ol>

        <a href="{{ route('sales.orders.index') }}"
            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4" aria-hidden="true"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </nav>

    <!-- Error Summary Alerts -->
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

    <form action="{{ route('sales.orders.store') }}" method="POST" class="space-y-6"
        onsubmit="return AppAlert.confirmSubmit(event, this, 'Konfirmasi penerbitan Pesanan Penjualan (SO) ini? Stok dan status pesanan akan langsung tercatat.', 'Buat Sales Order?')">
        @csrf

        <!-- Step 1: Referensi Quotation (opsional) -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center shrink-0">
                    <span class="text-xs font-black">1</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Referensi Penawaran Harga (Quotation)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih penawaran yang telah disetujui pelanggan untuk mengisi item dan rincian secara otomatis.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Berasal dari Dokumen Quotation
                        <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <select name="quotation_id" x-model="selectedQuotationId" @change="onQuotationChange()"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 transition cursor-pointer">
                        <option value="">— Tanpa Quotation (Pesanan Langsung) —</option>
                        @foreach($quotations as $q)
                        <option value="{{ $q->id }}">{{ $q->quotation_number }} — {{ $q->customer->name }} (Rp {{ number_format($q->total_amount, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="selectedQuotationId" x-cloak class="flex items-end">
                    <div class="w-full p-3.5 rounded-xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-200 dark:border-cyan-500/30 text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-cyan-700 dark:text-cyan-400 uppercase tracking-wider">Quotation Aktif Terpilih</span>
                            <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                        </div>
                        <p class="text-slate-900 dark:text-white font-mono font-bold" x-text="getSelectedQuotationLabel()"></p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Data pelanggan, diskon, dan item produk otomatis dimuat ke formulir.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Informasi Pesanan & Pelanggan -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center shrink-0">
                    <span class="text-xs font-black">2</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Informasi Pesanan &amp; Pelanggan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tentukan kontak pembeli, jadwal pengiriman, dan alamat tujuan pengiriman barang.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
                <!-- Pelanggan -->
                <div class="col-span-1 sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <select name="customer_id" x-model="customerId" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 transition cursor-pointer @error('customer_id') border-rose-500 @enderror">
                        <option value="">— Pilih Pelanggan —</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}{{ $c->company ? ' (' . $c->company . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- No SO -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        No. Sales Order
                    </label>
                    <input type="text" name="so_number" value="{{ old('so_number', $nextNumber) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-cyan-600 dark:text-cyan-400 focus:outline-none focus:border-cyan-500 transition">
                </div>

                <!-- Tanggal Pesanan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Tanggal Pesanan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 transition">
                </div>

                <!-- Estimasi Kirim -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Estimasi Kirim
                    </label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+3 days'))) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 transition">
                </div>

                <!-- Alamat Pengiriman -->
                <div class="col-span-1 sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Alamat Pengiriman <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="shipping_address" value="{{ old('shipping_address') }}"
                        placeholder="Contoh: Jl. Sudirman No. 45, Kompleks Ruko Blok A..."
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-cyan-500 transition">
                </div>
            </div>
        </div>

        <!-- Step 3: Rincian Item Pesanan -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center shrink-0">
                        <span class="text-xs font-black">3</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rincian Item Pesanan (Line Items)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pilih produk dari katalog atau tentukan kuantitas, harga kesepakatan, dan diskon per baris.</p>
                    </div>
                </div>

                <button type="button" @click="addItem()"
                    class="px-3.5 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 dark:bg-cyan-500/10 dark:hover:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-500/30 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4" aria-hidden="true"></i>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <!-- Items Dynamic Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 whitespace-nowrap">
                        <tr>
                            <th scope="col" class="py-2.5 px-3">Produk / Item</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-36 whitespace-nowrap">Harga Satuan (Rp)</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-24 whitespace-nowrap">Kuantitas</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-32 whitespace-nowrap">Diskon (Rp)</th>
                            <th scope="col" class="py-2.5 px-3 text-right w-36 whitespace-nowrap">Subtotal (Rp)</th>
                            <th scope="col" class="py-2.5 px-3 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                                <!-- Produk Dropdown -->
                                <td class="py-2 px-3 font-sans">
                                    <select :name="'items['+idx+'][product_id]'"
                                        x-model="item.product_id"
                                        @change="productSelected(idx, $event)"
                                        class="w-full px-2.5 py-1.5 rounded-lg text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 transition cursor-pointer">
                                        <option value="">— Pilih dari Katalog Produk —</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}">
                                            {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" :name="'items['+idx+'][product_name]'" x-model="item.product_name">
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-2 px-3">
                                    <input type="number" :name="'items['+idx+'][unit_price]'"
                                        x-model.number="item.unit_price"
                                        @input="calculateTotals()"
                                        min="0" step="100"
                                        class="w-full px-2.5 py-1.5 rounded-lg text-xs text-right bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 font-mono transition">
                                </td>

                                <!-- Quantity -->
                                <td class="py-2 px-3">
                                    <input type="number" :name="'items['+idx+'][quantity]'"
                                        x-model.number="item.quantity"
                                        @input="calculateTotals()"
                                        min="0.01" step="any"
                                        class="w-full px-2.5 py-1.5 rounded-lg text-xs text-right bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-cyan-500 font-mono transition">
                                </td>

                                <!-- Discount Amount -->
                                <td class="py-2 px-3">
                                    <input type="number" :name="'items['+idx+'][discount_amount]'"
                                        x-model.number="item.discount_amount"
                                        @input="calculateTotals()"
                                        min="0" step="100"
                                        class="w-full px-2.5 py-1.5 rounded-lg text-xs text-right bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-rose-600 dark:text-rose-400 focus:outline-none focus:border-rose-500 font-mono transition">
                                </td>

                                <!-- Subtotal Baris -->
                                <td class="py-2 px-3 text-right font-bold text-cyan-600 dark:text-cyan-400 whitespace-nowrap"
                                    x-text="'Rp ' + Math.max(0, ((item.quantity || 0) * (item.unit_price || 0)) - (item.discount_amount || 0)).toLocaleString('id-ID')">
                                </td>

                                <!-- Action Remove -->
                                <td class="py-2 px-3 text-center">
                                    <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 disabled:opacity-25 transition cursor-pointer"
                                        title="Hapus baris">
                                        <i data-lucide="trash-2" class="w-4 h-4" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Notes & Summary Block -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start gap-6 font-sans">
                <!-- Catatan Pesanan -->
                <div class="w-full sm:w-1/2 space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Catatan Pesanan &amp; Pengiriman</label>
                    <textarea name="notes" rows="3"
                        placeholder="Instruksi pengiriman, nama kontak ekspedisi, atau catatan khusus pelanggan..."
                        class="w-full p-3 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-cyan-500 transition">{{ old('notes') }}</textarea>
                </div>

                <!-- Financial Calculation Box -->
                <div class="w-full sm:w-80 p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal Item:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>

                    <div class="flex justify-between items-center gap-2 text-slate-600 dark:text-slate-400">
                        <span>Diskon Keseluruhan (Rp):</span>
                        <input type="number" name="discount_amount"
                            x-model.number="discountAmount"
                            @input="calculateTotals()"
                            min="0"
                            class="w-32 px-2.5 py-1 rounded-lg text-xs text-right font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                    </div>

                    <div class="flex justify-between items-center gap-2 text-slate-600 dark:text-slate-400">
                        <span>Pajak Penjualan (%):</span>
                        <input type="number" name="tax_percentage"
                            x-model.number="taxPercentage"
                            @input="calculateTotals()"
                            min="0" max="100" step="0.01"
                            class="w-32 px-2.5 py-1 rounded-lg text-xs text-right font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <input type="hidden" name="tax_amount" :value="taxAmount">

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center text-sm font-black text-slate-900 dark:text-white">
                        <span>Total Nilai Pesanan:</span>
                        <span class="font-mono text-cyan-600 dark:text-cyan-400 text-lg" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="flex items-center justify-end gap-3 pb-8">
            <a href="{{ route('sales.orders.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-sm shadow-cyan-600/20 transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="check-circle" class="w-4 h-4" aria-hidden="true"></i>
                <span>Konfirmasi &amp; Buat Pesanan</span>
            </button>
        </div>
    </form>
</div>

@php
    $quotationsMapped = $quotations->map(fn($q) => [
        'id'          => $q->id,
        'label'       => $q->quotation_number . ' — ' . $q->customer->name,
        'customer_id' => $q->customer_id,
        'discount'    => (float) $q->discount_amount,
        'tax'         => (float) $q->tax_amount,
        'notes'       => $q->notes,
        'items'       => $q->items->map(fn($i) => [
            'product_id'      => $i->product_id,
            'product_name'    => $i->product_name,
            'unit_price'      => (float) $i->unit_price,
            'quantity'        => (float) $i->quantity,
            'discount_amount' => (float) $i->discount_amount,
        ])->values()->all(),
    ])->values()->all();
@endphp

<script>
    window.SO_QUOTATIONS_DATA = {!! json_encode($quotationsMapped, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.SO_PREFILL_QUOTATION_ID = @json($prefillQuotation?->id);

    function salesOrderForm() {
        return {
            quotations: window.SO_QUOTATIONS_DATA || [],
            selectedQuotationId: window.SO_PREFILL_QUOTATION_ID ? String(window.SO_PREFILL_QUOTATION_ID) : '',
            customerId: '',

            items: [
                { product_id: '', product_name: '', unit_price: 0, quantity: 1, discount_amount: 0 }
            ],
            subtotal: 0,
            discountAmount: 0,
            taxPercentage: {{ $business->pos_enable_tax ? (float) $business->pos_tax_percent : 0 }},
            taxAmount: 0,
            grandTotal: 0,

            init() {
                if (this.selectedQuotationId) {
                    this.$nextTick(() => this.onQuotationChange());
                }
                this.calculateTotals();
            },

            getSelectedQuotationLabel() {
                const q = this.quotations.find(q => String(q.id) === String(this.selectedQuotationId));
                return q ? q.label : '';
            },

            onQuotationChange() {
                if (!this.selectedQuotationId) {
                    return;
                }
                const q = this.quotations.find(q => String(q.id) === String(this.selectedQuotationId));
                if (!q) return;

                // Auto-fill customer
                this.customerId = String(q.customer_id);

                // Auto-fill discount & tax
                this.discountAmount = q.discount || 0;
                this.taxAmount = q.tax || 0;

                // Auto-fill items
                if (q.items && q.items.length > 0) {
                    this.items = q.items.map(i => ({
                        product_id: i.product_id ? String(i.product_id) : '',
                        product_name: i.product_name || '',
                        unit_price: i.unit_price || 0,
                        quantity: i.quantity || 1,
                        discount_amount: i.discount_amount || 0,
                    }));
                }

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    this.calculateTotals();
                });
            },

            addItem() {
                this.items.push({
                    product_id: '',
                    product_name: '',
                    unit_price: 0,
                    quantity: 1,
                    discount_amount: 0
                });
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            removeItem(idx) {
                if (this.items.length > 1) {
                    this.items.splice(idx, 1);
                    this.calculateTotals();
                }
            },

            productSelected(idx, event) {
                const sel = event ? event.target : null;
                if (sel) {
                    const opt = sel.options[sel.selectedIndex];
                    if (opt && opt.value) {
                        this.items[idx].unit_price = parseFloat(opt.dataset.price || 0);
                        this.items[idx].product_name = opt.dataset.name || '';
                    } else {
                        this.items[idx].unit_price = 0;
                        this.items[idx].product_name = '';
                    }
                }
                this.calculateTotals();
            },

            calculateTotals() {
                this.subtotal = this.items.reduce((sum, it) => {
                    return sum + Math.max(0, ((it.quantity || 0) * (it.unit_price || 0)) - (it.discount_amount || 0));
                }, 0);
                const taxableAmount = Math.max(0, this.subtotal - (this.discountAmount || 0));
                this.taxAmount = taxableAmount * (Math.max(0, Number(this.taxPercentage || 0)) / 100);
                this.grandTotal = Math.max(0, taxableAmount + this.taxAmount);
            }
        };
    }
</script>
@endsection
