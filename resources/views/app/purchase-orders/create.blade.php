@extends('layouts.app', [
    'title' => 'Buat Purchase Order',
    'headerTitle' => 'Buat Dokumen Purchase Order',
    'headerSubtitle' => 'Input pesanan pembelian pelanggan atau pengadaan bahan baku ke pemasok'
])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    poType: '{{ $defaultType }}',
    items: [
        { product_id: '', material_id: '', item_name: '', sku: '', quantity: 1, unit_id: '{{ $units->first()?->id }}', unit_price: 0, notes: '' }
    ],
    discountType: 'percentage',
    discountValue: 0,
    taxPercentage: 0,

    products: {{ Js::from($products) }},
    materials: {{ Js::from($materials) }},
    units: {{ Js::from($units) }},

    addItem() {
        this.items.push({
            product_id: '',
            material_id: '',
            item_name: '',
            sku: '',
            quantity: 1,
            unit_id: this.units.length > 0 ? this.units[0].id : '',
            unit_price: 0,
            notes: ''
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
            this.items[index].unit_price = prod.selling_price > 0 ? prod.selling_price : (prod.base_cost > 0 ? prod.base_cost * 1.4 : 0);
        }
    },

    onMaterialChange(index) {
        const mId = this.items[index].material_id;
        const mat = this.materials.find(m => m.id === mId);
        if (mat) {
            this.items[index].item_name = mat.name;
            this.items[index].sku = mat.code || mat.sku || '';
            this.items[index].unit_id = mat.unit_id;
            const price = mat.prices && mat.prices.length > 0 ? mat.prices[0].purchase_price : 0;
            this.items[index].unit_price = price;
        }
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
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
        return Math.max(0, this.subtotal - this.discountAmount) + this.taxAmount;
    },

    formatCurrency(num) {
        return '{{ $business->currency_symbol }} ' + (new Intl.NumberFormat('id-ID').format(Math.round(num || 0)));
    }
}">

    <div class="flex items-center justify-between">
        <a href="{{ route('purchase-orders.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar PO</span>
        </a>
    </div>

    <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
        @csrf

        <!-- Card Header Dokumen -->
        <div class="glass-card p-6 rounded-2xl border border-slate-800 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-emerald-400"></i>
                        <span>Informasi Purchase Order</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Tentukan tipe pesanan dan pihak yang bertransaksi</p>
                </div>

                <!-- PO Type Switcher -->
                <div class="inline-flex p-1 rounded-xl bg-slate-900 border border-slate-800 self-start sm:self-auto">
                    <button type="button" @click="poType = 'customer'" 
                            :class="poType === 'customer' ? 'bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        PO Pelanggan (Penjualan)
                    </button>
                    <button type="button" @click="poType = 'supplier'" 
                            :class="poType === 'supplier' ? 'bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:text-white'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        PO Vendor (Pengadaan)
                    </button>
                </div>
            </div>

            <input type="hidden" name="po_type" :value="poType">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <!-- Customer Select (If PO Pelanggan) -->
                <div x-show="poType === 'customer'" class="sm:col-span-2">
                    <label class="block font-semibold text-slate-300 mb-1">Pilih Pelanggan / Klien *</label>
                    <select name="customer_id" :required="poType === 'customer'" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ $selectedCustomerId === $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }} {{ $cust->company_name ? "({$cust->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Supplier Select (If PO Vendor) -->
                <div x-show="poType === 'supplier'" class="sm:col-span-2" style="display: none;">
                    <label class="block font-semibold text-slate-300 mb-1">Pilih Pemasok / Vendor *</label>
                    <select name="supplier_id" :required="poType === 'supplier'" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-blue-500 rounded-xl text-white">
                        <option value="">-- Pilih Pemasok --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tanggal Pesanan *</label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Target Pengiriman</label>
                    <input type="date" name="expected_delivery_date" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nomor PO (Opsional)</label>
                    <input type="text" name="po_number" placeholder="Otomatis jika kosong" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">No. Referensi Klien / PO Asli</label>
                    <input type="text" name="reference_number" placeholder="PO/EXT/2026/089" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>
            </div>
        </div>

        <!-- Card Daftar Item Pesanan (Dynamic Table) -->
        <div class="glass-card p-6 rounded-2xl border border-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4 text-emerald-400"></i>
                        <span>Rincian Item yang Dipesan</span>
                    </h3>
                    <p class="text-xs text-slate-400">Pilih dari katalog produk atau bahan baku sesuai tipe PO</p>
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
                            <th class="py-2.5 px-3 font-semibold w-1/3">Item / Produk *</th>
                            <th class="py-2.5 px-3 font-semibold w-24">Satuan *</th>
                            <th class="py-2.5 px-3 font-semibold w-24 text-right">Kuantitas *</th>
                            <th class="py-2.5 px-3 font-semibold w-36 text-right">Harga Satuan *</th>
                            <th class="py-2.5 px-3 font-semibold w-36 text-right">Subtotal</th>
                            <th class="py-2.5 px-2 font-semibold w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-800/20">
                                <td class="py-2 px-3">
                                    <div x-show="poType === 'customer'">
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductChange(index)" 
                                                class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-lg text-xs text-white">
                                            <option value="">-- Pilih dari Katalog Produk --</option>
                                            <template x-for="prod in products" :key="prod.id">
                                                <option :value="prod.id" x-text="prod.name + (prod.selling_price > 0 ? ' (Rp ' + new Intl.NumberFormat('id-ID').format(prod.selling_price) + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div x-show="poType === 'supplier'">
                                        <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" @change="onMaterialChange(index)" 
                                                class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 focus:border-blue-500 rounded-lg text-xs text-white">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            <template x-for="mat in materials" :key="mat.id">
                                                <option :value="mat.id" x-text="mat.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required placeholder="Nama item / deskripsi kustom" class="w-full px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-[11px] text-slate-300 mt-1">
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
                                    <input type="number" step="any" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs text-white font-mono font-semibold">
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs text-white font-mono font-semibold">
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

            <!-- Kalkulasi Finansial Footer -->
            <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-slate-800 gap-6">
                <div class="space-y-3 w-full sm:max-w-md">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Syarat & Ketentuan Pengiriman</label>
                        <textarea name="terms_and_conditions" rows="2" placeholder="Pengiriman bertahap Franco Gudang Pembeli..." class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" placeholder="Catatan internal pengiriman..." class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                    </div>
                </div>

                <div class="w-full sm:w-80 glass-card p-4 rounded-xl border border-slate-800 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal Item:</span>
                        <span class="font-mono font-semibold text-white" x-text="formatCurrency(subtotal)"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">Diskon:</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" min="0" name="discount_value" x-model="discountValue" class="w-20 px-2 py-1 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs font-mono text-white">
                            <select name="discount_type" x-model="discountType" class="px-1.5 py-1 bg-slate-900 border border-slate-800 rounded-lg text-[11px] text-slate-300">
                                <option value="percentage">%</option>
                                <option value="fixed">{{ $business->currency_symbol }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">Pajak PPN (%):</span>
                        <input type="number" step="any" min="0" max="100" name="tax_percentage" x-model="taxPercentage" placeholder="11" class="w-20 px-2 py-1 bg-slate-900 border border-slate-800 text-right rounded-lg text-xs font-mono text-white">
                    </div>

                    <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm font-bold text-white">
                        <span>Nilai Total PO:</span>
                        <span class="font-mono text-emerald-400 text-base" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Terbitkan Purchase Order</span>
            </button>
        </div>
    </form>
</div>
@endsection
