@extends('layouts.app', ['title' => 'Buat Pesanan Penjualan Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="salesOrderForm()">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
                    SALES PIPELINE
                </span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Buat Pesanan Penjualan</h1>
            <p class="text-xs text-slate-400 mt-0.5">Buat pesanan penjualan langsung — dengan atau tanpa penawaran (Quotation).</p>
        </div>
        <a href="{{ route('sales.orders.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    @if($errors->any())
    <div class="glass-card rounded-2xl p-4 border border-rose-500/40 bg-rose-500/10">
        <p class="text-xs font-bold text-rose-400 mb-1">Harap perbaiki kesalahan berikut:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li class="text-xs text-rose-300">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('sales.orders.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Step 1: Referensi Quotation (opsional) -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center">
                    <span class="text-xs font-black text-cyan-400">1</span>
                </div>
                <h3 class="text-sm font-black text-cyan-400 uppercase tracking-wider">Referensi Penawaran (Opsional)</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">
                        Berasal dari Quotation
                        <span class="text-slate-500 font-normal ml-1">(biarkan kosong jika SO langsung)</span>
                    </label>
                    <select name="quotation_id" x-model="selectedQuotationId" @change="onQuotationChange()"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cyan-500">
                        <option value="">— Tanpa Quotation (SO Langsung) —</option>
                        @foreach($quotations as $q)
                        <option value="{{ $q->id }}">{{ $q->quotation_number }} — {{ $q->customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="selectedQuotationId" x-cloak class="flex items-end">
                    <div class="w-full glass-card rounded-xl p-3 border border-cyan-500/30 bg-cyan-500/5 text-xs">
                        <p class="text-cyan-400 font-bold text-[10px] uppercase tracking-wider">Quotation dipilih</p>
                        <p class="text-white font-mono mt-0.5" x-text="getSelectedQuotationLabel()"></p>
                        <p class="text-slate-400 mt-0.5">Item dan pelanggan otomatis diisi ↓</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Informasi Pesanan -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center">
                    <span class="text-xs font-black text-cyan-400">2</span>
                </div>
                <h3 class="text-sm font-black text-cyan-400 uppercase tracking-wider">Informasi Pesanan</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-slate-300 mb-1">
                        Pelanggan <span class="text-rose-400">*</span>
                    </label>
                    <select name="customer_id" x-model="customerId" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cyan-500 @error('customer_id') border-rose-500 @enderror">
                        <option value="">— Pilih Pelanggan —</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}{{ $c->company ? ' (' . $c->company . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. Sales Order</label>
                    <input type="text" name="so_number" value="{{ old('so_number', $nextNumber) }}"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-mono text-cyan-400 focus:outline-none focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">
                        Tanggal Pesanan <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Estimasi Kirim</label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+3 days'))) }}"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cyan-500">
                </div>

                <div class="col-span-2 sm:col-span-3">
                    <label class="block text-xs font-bold text-slate-300 mb-1">Alamat Pengiriman <span class="text-slate-500 font-normal">(opsional)</span></label>
                    <input type="text" name="shipping_address" value="{{ old('shipping_address') }}"
                        placeholder="Jl. Contoh No. 1, Kota..."
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cyan-500">
                </div>
            </div>
        </div>

        <!-- Step 3: Line Items -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center">
                        <span class="text-xs font-black text-cyan-400">3</span>
                    </div>
                    <h3 class="text-sm font-black text-cyan-400 uppercase tracking-wider">Rincian Item Pesanan</h3>
                </div>
                <button type="button" @click="addItem()"
                    class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="table-responsive-wide">
                <table class="w-full text-left text-xs text-slate-300 min-w-[680px]">
                    <thead class="text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3 whitespace-nowrap">Produk / Item</th>
                            <th class="py-2.5 px-3 text-right w-32 whitespace-nowrap">Harga Satuan (Rp)</th>
                            <th class="py-2.5 px-3 text-right w-24 whitespace-nowrap">Jumlah</th>
                            <th class="py-2.5 px-3 text-right w-28 whitespace-nowrap">Diskon (Rp)</th>
                            <th class="py-2.5 px-3 text-right w-36 whitespace-nowrap">Subtotal (Rp)</th>
                            <th class="py-2.5 px-3 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-slate-800/20 transition">
                                <td class="py-2 px-3 font-sans">
                                    <select :name="'items['+idx+'][product_id]'"
                                        x-model="item.product_id"
                                        @change="productSelected(idx, $event)"
                                        class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2.5 py-1.5 text-xs text-white font-sans focus:outline-none focus:border-cyan-500">
                                        <option value="">— Pilih dari Katalog —</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}">
                                            {{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" :name="'items['+idx+'][product_name]'" x-model="item.product_name">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="number" :name="'items['+idx+'][unit_price]'"
                                        x-model.number="item.unit_price"
                                        @input="calculateTotals()"
                                        min="0" step="100"
                                        class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2 py-1.5 text-xs text-right text-white focus:outline-none focus:border-cyan-500">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="number" :name="'items['+idx+'][quantity]'"
                                        x-model.number="item.quantity"
                                        @input="calculateTotals()"
                                        min="0.01" step="any"
                                        class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2 py-1.5 text-xs text-right text-white focus:outline-none focus:border-cyan-500">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="number" :name="'items['+idx+'][discount_amount]'"
                                        x-model.number="item.discount_amount"
                                        @input="calculateTotals()"
                                        min="0" step="100"
                                        class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2 py-1.5 text-xs text-right text-rose-400 focus:outline-none focus:border-rose-500">
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-cyan-400"
                                    x-text="'Rp ' + Math.max(0, ((item.quantity || 0) * (item.unit_price || 0)) - (item.discount_amount || 0)).toLocaleString('id-ID')">
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1"
                                        class="text-slate-500 hover:text-rose-400 disabled:opacity-30 transition cursor-pointer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Summary & Notes -->
            <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-start gap-6 font-sans">
                <div class="w-full sm:w-1/2 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Catatan Pesanan</label>
                        <textarea name="notes" rows="3"
                            placeholder="Instruksi pengiriman, catatan khusus pelanggan..."
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-cyan-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="w-full sm:w-80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal:</span>
                        <span class="font-mono font-bold text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2 text-slate-400">
                        <span>Diskon Keseluruhan (Rp):</span>
                        <input type="number" name="discount_amount"
                            x-model.number="discountAmount"
                            @input="calculateTotals()"
                            min="0"
                            class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs text-right font-mono text-white focus:outline-none focus:border-rose-500">
                    </div>
                    <div class="flex justify-between items-center gap-2 text-slate-400">
                        <span>Pajak (%):</span>
                        <input type="number" name="tax_percentage"
                            x-model.number="taxPercentage"
                            @input="calculateTotals()"
                            min="0" max="100" step="0.01"
                            class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs text-right font-mono text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <input type="hidden" name="tax_amount" :value="taxAmount">
                    <div class="pt-2 border-t border-slate-800 flex justify-between text-base font-black text-white">
                        <span>Total Pesanan:</span>
                        <span class="font-mono text-cyan-400 text-lg" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pb-6">
            <a href="{{ route('sales.orders.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                Batal
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-black shadow-lg shadow-cyan-500/20 transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
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

                // Auto-fill discount and preserve quotation tax as a percentage when possible.
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
