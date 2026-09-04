@extends('layouts.app', [
    'title' => 'Buat Faktur Penjualan',
    'headerTitle' => 'Buat Faktur Penjualan (Invoice)',
    'headerSubtitle' => 'Terbitkan tagihan resmi dari katalog produk, purchase order pelanggan, atau hasil kalkulasi HPP'
])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    customerId: '{{ $selectedPo?->customer_id ?? '' }}',
    purchaseOrderId: '{{ $selectedPo?->id ?? '' }}',
    paymentTerms: 'Net 30',
    invoiceDate: '{{ date('Y-m-d') }}',
    dueDate: '{{ date('Y-m-d', strtotime('+30 days')) }}',
    
    items: [
        @if($selectedPo)
            @foreach($selectedPo->items as $it)
            {
                product_id: '{{ $it->product_id }}',
                item_name: '{{ addslashes($it->item_name) }}',
                sku: '{{ addslashes($it->sku ?? '') }}',
                description: '{{ addslashes($it->notes ?? '') }}',
                quantity: {{ (float) $it->quantity }},
                unit_id: '{{ $it->unit_id }}',
                unit_price: {{ (float) $it->unit_price }},
                unit_hpp: {{ (float) $it->cost_price_snapshot }}
            },
            @endforeach
        @elseif($prefillProduct)
            {
                product_id: '{{ $prefillProduct->id }}',
                item_name: '{{ addslashes($prefillProduct->name) }}',
                sku: '{{ addslashes($prefillProduct->code ?? '') }}',
                description: 'Hasil kalkulasi HPP sistem',
                quantity: {{ $prefillQty > 0 ? $prefillQty : 1 }},
                unit_id: '{{ $prefillProduct->output_unit_id }}',
                unit_price: {{ $prefillPrice > 0 ? $prefillPrice : ($prefillProduct->selling_price > 0 ? $prefillProduct->selling_price : 0) }},
                unit_hpp: {{ $prefillCost > 0 ? $prefillCost : ($prefillProduct->base_cost > 0 ? $prefillProduct->base_cost : 0) }}
            }
        @else
            {
                product_id: '',
                item_name: '',
                sku: '',
                description: '',
                quantity: 1,
                unit_id: '{{ $units->first()?->id }}',
                unit_price: 0,
                unit_hpp: 0
            }
        @endif
    ],

    discountType: '{{ $selectedPo?->discount_type ?? 'fixed' }}',
    discountValue: {{ (float) ($selectedPo?->discount_value ?? 0) }},
    taxPercentage: {{ (float) ($selectedPo?->tax_percentage ?? 0) }},
    shippingCost: 0,

    customers: {{ Js::from($customers) }},
    products: {{ Js::from($products) }},
    units: {{ Js::from($units) }},
    availablePos: {{ Js::from($availablePurchaseOrders) }},

    onCustomerChange() {
        const cust = this.customers.find(c => c.id === this.customerId);
        if (cust && cust.payment_terms_days) {
            const days = parseInt(cust.payment_terms_days) || 30;
            this.paymentTerms = 'Net ' + days;
            const d = new Date(this.invoiceDate || new Date());
            d.setDate(d.getDate() + days);
            this.dueDate = d.toISOString().split('T')[0];
        }
    },

    onPoChange() {
        if (!this.purchaseOrderId) return;
        const po = this.availablePos.find(p => p.id === this.purchaseOrderId);
        if (po) {
            this.customerId = po.customer_id;
            this.discountType = po.discount_type || 'percentage';
            this.discountValue = parseFloat(po.discount_value) || 0;
            this.taxPercentage = parseFloat(po.tax_percentage) || 0;
            
            if (po.items && po.items.length > 0) {
                this.items = po.items.map(it => ({
                    product_id: it.product_id || '',
                    item_name: it.item_name,
                    sku: it.sku || '',
                    description: it.notes || '',
                    quantity: parseFloat(it.quantity) || 1,
                    unit_id: it.unit_id,
                    unit_price: parseFloat(it.unit_price) || 0,
                    unit_hpp: parseFloat(it.cost_price_snapshot) || 0
                }));
            }
        }
    },

    addItem() {
        this.items.push({
            product_id: '',
            item_name: '',
            sku: '',
            description: '',
            quantity: 1,
            unit_id: this.units.length > 0 ? this.units[0].id : '',
            unit_price: 0,
            unit_hpp: 0
        });
    },

    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },

    onProductChange(index) {
        const pId = this.items[index].product_id;
        const prod = this.products.find(p => p.id === pId);
        if (prod) {
            this.items[index].item_name = prod.name;
            this.items[index].sku = prod.code || prod.sku || '';
            this.items[index].unit_id = prod.output_unit_id;
            this.items[index].unit_price = parseFloat(prod.selling_price) || (parseFloat(prod.base_cost) > 0 ? parseFloat(prod.base_cost) * 1.4 : 0);
            this.items[index].unit_hpp = parseFloat(prod.base_cost) || 0;
        }
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
    },

    get totalHppCost() {
        return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_hpp) || 0)), 0);
    },

    get discountAmount() {
        if (this.discountType === 'percentage') {
            return (parseFloat(this.discountValue) || 0) / 100 * this.subtotal;
        }
        return Math.min(parseFloat(this.discountValue) || 0, this.subtotal);
    },

    get taxAmount() {
        const taxable = Math.max(0, this.subtotal - this.discountAmount);
        return (parseFloat(this.taxPercentage) || 0) / 100 * taxable;
    },

    get grandTotal() {
        return Math.max(0, this.subtotal - this.discountAmount) + this.taxAmount + (parseFloat(this.shippingCost) || 0);
    },

    get totalGrossProfit() {
        return this.subtotal - this.totalHppCost;
    },

    get grossMarginPercentage() {
        return this.subtotal > 0 ? (this.totalGrossProfit / this.subtotal) * 100 : 0;
    },

    formatCurrency(num) {
        return '{{ $business->currency_symbol }} ' + (new Intl.NumberFormat('id-ID').format(Math.round(num || 0)));
    }
}">

    <div class="flex items-center justify-between">
        <a href="{{ route('invoices.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Faktur</span>
        </a>
    </div>

    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6">
        @csrf

        <!-- Card Header Dokumen Faktur -->
        <div class="glass-card p-6 rounded-2xl border border-slate-800 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5 h-5 text-emerald-400"></i>
                        <span>Informasi Faktur Penjualan</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Lengkapi identitas penagihan dan referensi transaksi</p>
                </div>

                <!-- Opsi Tarik dari Purchase Order -->
                @if($availablePurchaseOrders->isNotEmpty())
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Ambil dari PO Pelanggan:</span>
                    <select name="purchase_order_id" x-model="purchaseOrderId" @change="onPoChange()" class="px-3 py-1.5 bg-slate-900 border border-slate-700 focus:border-emerald-500 rounded-xl text-xs text-emerald-400 font-medium">
                        <option value="">-- Buat Faktur Manual --</option>
                        <template x-for="po in availablePos" :key="po.id">
                            <option :value="po.id" x-text="po.po_number + ' - ' + (po.customer ? po.customer.name : '')"></option>
                        </template>
                    </select>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <!-- Customer Select -->
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-300 mb-1">Pilih Pelanggan / Klien *</label>
                    <select name="customer_id" x-model="customerId" @change="onCustomerChange()" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ ($selectedPo?->customer_id ?? '') === $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }} {{ $cust->company_name ? "({$cust->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tanggal Faktur *</label>
                    <input type="date" name="invoice_date" x-model="invoiceDate" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tanggal Jatuh Tempo *</label>
                    <input type="date" name="due_date" x-model="dueDate" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nomor Faktur (Opsional)</label>
                    <input type="text" name="invoice_number" placeholder="Otomatis (INV-YYYYMM-XXXX)" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Termin Pembayaran</label>
                    <input type="text" name="payment_terms" x-model="paymentTerms" placeholder="Net 30 / COD" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Status Awal</label>
                    <select name="status" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                        <option value="draft">Draft (Belum Terbit)</option>
                        <option value="sent" selected>Sent (Terkirim ke Klien)</option>
                        <option value="unpaid">Unpaid (Resmi Menunggu Bayar)</option>
                    </select>
                </div>

                <!-- Gudang / Lokasi Pengeluaran Barang -->
                @if($locations->isNotEmpty())
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">
                        <i data-lucide="warehouse" class="inline w-3.5 h-3.5 text-emerald-400 mr-1"></i>
                        Gudang Asal Barang
                    </label>
                    <select name="location_id" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                        <option value="">-- Otomatis (Gudang Utama) --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->is_primary ? 'selected' : '' }}>
                                {{ $loc->name }} {{ $loc->type ? "({$loc->type})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-slate-500 mt-0.5 text-[10px]">Stok barang akan dipotong dari gudang/outlet yang dipilih saat faktur dikonfirmasi.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Card Daftar Item Faktur (Dynamic Table) -->
        <div class="glass-card p-6 rounded-2xl border border-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="package-check" class="w-4 h-4 text-emerald-400"></i>
                        <span>Item Produk & Harga Jual</span>
                    </h3>
                    <p class="text-xs text-slate-400">Pilih produk katalog untuk auto-fill harga jual dan snapshot HPP modal</p>
                </div>

                <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                            <th class="py-2.5 px-3 font-semibold w-1/3">Produk / Item *</th>
                            <th class="py-2.5 px-3 font-semibold w-24">Satuan *</th>
                            <th class="py-2.5 px-3 font-semibold w-20 text-right">Qty *</th>
                            <th class="py-2.5 px-3 font-semibold w-32 text-right">Harga Jual *</th>
                            <th class="py-2.5 px-3 font-semibold w-28 text-right text-emerald-400">HPP (Modal)</th>
                            <th class="py-2.5 px-3 font-semibold w-32 text-right">Subtotal</th>
                            <th class="py-2.5 px-2 font-semibold w-8 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-800/20">
                                <td class="py-2 px-3">
                                    <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductChange(index)" 
                                            class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-lg text-xs text-white">
                                        <option value="">-- Pilih dari Katalog Produk --</option>
                                        <template x-for="prod in products" :key="prod.id">
                                            <option :value="prod.id" x-text="prod.name + (prod.selling_price > 0 ? ' (Jual: Rp ' + new Intl.NumberFormat('id-ID').format(prod.selling_price) + ')' : '')"></option>
                                        </template>
                                    </select>
                                    <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required placeholder="Nama item pada faktur" class="w-full px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-[11px] text-slate-200 mt-1">
                                    <input type="hidden" :name="'items[' + index + '][sku]'" x-model="item.sku">
                                </td>
                                <td class="py-2 px-3">
                                    <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" required class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white">
                                        <template x-for="u in units" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <input type="number" step="any" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs text-white font-mono font-semibold">
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs text-white font-mono font-semibold">
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][unit_hpp]'" x-model="item.unit_hpp" title="Snapshot HPP Modal" class="w-full px-2 py-1.5 bg-slate-950 border border-slate-800 text-right rounded-lg text-xs text-emerald-400 font-mono">
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-white text-xs" x-text="formatCurrency(item.quantity * item.unit_price)">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <button type="button" @click="removeItem(index)" :disabled="items.length <= 1" class="p-1 text-slate-500 hover:text-rose-400 disabled:opacity-30 rounded transition-colors">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Profitability Summary Widget & Totals -->
            <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-slate-800 gap-6">
                <!-- Left: Estimated Profit Badge & Notes -->
                <div class="space-y-3 w-full sm:max-w-md">
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] text-emerald-400 font-bold uppercase tracking-wider">Estimasi Laba Kotor Transaksi:</div>
                            <div class="text-lg font-extrabold text-emerald-400 font-mono" x-text="formatCurrency(totalGrossProfit)"></div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 font-mono" x-text="'Margin ' + Math.round(grossMarginPercentage) + '%'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tagihan (Tampil pada Faktur)</label>
                        <input type="text" name="notes" placeholder="Terima kasih atas kerja sama bisnis Anda..." class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Ketentuan Pembayaran & Garansi</label>
                        <textarea name="terms_conditions" rows="2" placeholder="Pembayaran ditransfer ke rekening resmi. Komplain maksimal 3 hari..." class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white"></textarea>
                    </div>
                </div>

                <!-- Right: Financial Breakdown -->
                <div class="w-full sm:w-80 glass-card p-4 rounded-xl border border-slate-800 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal Barang:</span>
                        <span class="font-mono font-semibold text-white" x-text="formatCurrency(subtotal)"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">Potongan Diskon:</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" min="0" name="discount_value" x-model="discountValue" class="w-20 px-2 py-1 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs font-mono text-white">
                            <select name="discount_type" x-model="discountType" class="px-1.5 py-1 bg-slate-900 border border-slate-800 rounded-lg text-[11px] text-slate-300">
                                <option value="fixed">{{ $business->currency_symbol }}</option>
                                <option value="percentage">%</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">Pajak PPN (%):</span>
                        <input type="number" step="any" min="0" max="100" name="tax_percentage" x-model="taxPercentage" placeholder="11" class="w-20 px-2 py-1 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs font-mono text-white">
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">Ongkos Kirim:</span>
                        <input type="number" step="any" min="0" name="shipping_cost" x-model="shippingCost" placeholder="0" class="w-24 px-2 py-1 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs font-mono text-white">
                    </div>

                    <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm font-bold text-white">
                        <span>Total Tagihan:</span>
                        <span class="font-mono text-emerald-400 text-base" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Terbitkan Faktur Penjualan</span>
            </button>
        </div>
    </form>
</div>
@endsection
