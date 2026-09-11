@extends('layouts.app', [
    'title' => 'Buat Purchase Order',
    'headerTitle' => 'Buat Dokumen Purchase Order',
    'headerSubtitle' => 'Input pesanan pembelian pelanggan atau pengadaan bahan baku ke pemasok'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-12" x-data="{
    poType: '{{ $defaultType }}',
    items: [
        { product_id: '', material_id: '', item_name: '', sku: '', quantity: 1, unit_id: '{{ $units->first()?->id }}', unit_price: 0, notes: '' }
    ],
    discountType: 'percentage',
    discountValue: 0,
    taxPercentage: {{ $business->pos_enable_tax ? (float) $business->pos_tax_percent : 0 }},

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

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-[#007AFF] transition-colors">Purchase Orders</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Buat Baru</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Buat Dokumen Purchase Order</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Input pesanan pembelian pelanggan atau pengadaan bahan baku ke pemasok.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. FORM BODY                                          -->
    <!-- ===================================================== -->
    <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
        @csrf

        <!-- Card 1: Informasi Dokumen & Tipe PO -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div>
                    <h2 class="text-[15px] font-semibold text-black dark:text-white">Informasi Purchase Order</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Tentukan tipe alur transaksi dan pihak yang bertransaksi.</p>
                </div>

                <!-- PO Type Switcher (Segmented Control) -->
                <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-start sm:self-auto">
                    <button type="button" @click="poType = 'customer'"
                            :class="poType === 'customer' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white'"
                            class="px-3.5 py-1 rounded-[7px] transition-all">
                        PO Pelanggan (Penjualan)
                    </button>
                    <button type="button" @click="poType = 'supplier'"
                            :class="poType === 'supplier' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white'"
                            class="px-3.5 py-1 rounded-[7px] transition-all">
                        PO Vendor (Pengadaan)
                    </button>
                </div>
            </div>

            <input type="hidden" name="po_type" :value="poType">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-[13px]">
                <!-- Customer Select (If PO Pelanggan) -->
                <div x-show="poType === 'customer'" class="sm:col-span-2">
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Pilih Pelanggan / Klien <span class="text-[#FF3B30]">*</span></label>
                    <select name="customer_id" :required="poType === 'customer'" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
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
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Pilih Pemasok / Vendor <span class="text-[#FF3B30]">*</span></label>
                    <select name="supplier_id" :required="poType === 'supplier'" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Pilih Pemasok --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Pesanan <span class="text-[#FF3B30]">*</span></label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Target Pengiriman</label>
                    <input type="date" name="expected_delivery_date"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nomor PO (Opsional)</label>
                    <input type="text" name="po_number" placeholder="Otomatis digenerate sistem jika dikosongkan"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">No. Referensi Klien / PO Eksternal</label>
                    <input type="text" name="reference_number" placeholder="Contoh: PO/EXT/2026/089"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- Card 2: Rincian Item Pesanan (Dynamic Table) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Rincian Item yang Dipesan</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Pilih dari katalog produk atau bahan baku sesuai tipe PO.</p>
                </div>

                <button type="button" @click="addItem()" class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-1/3">Item / Produk *</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-28">Satuan *</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-24 text-right">Kuantitas *</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-36 text-right">Harga Satuan *</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-36 text-right">Subtotal</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-3 py-2.5">
                                    <div x-show="poType === 'customer'">
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductChange(index)"
                                                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <option value="">-- Pilih dari Katalog Produk --</option>
                                            <template x-for="prod in products" :key="prod.id">
                                                <option :value="prod.id" x-text="prod.name + (prod.selling_price > 0 ? ' (Rp ' + new Intl.NumberFormat('id-ID').format(prod.selling_price) + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div x-show="poType === 'supplier'">
                                        <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" @change="onMaterialChange(index)"
                                                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            <template x-for="mat in materials" :key="mat.id">
                                                <option :value="mat.id" x-text="mat.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required placeholder="Nama item / deskripsi kustom"
                                           class="w-full h-8 bg-black/[0.02] dark:bg-white/[0.03] border-none rounded-[6px] px-2 text-[12px] text-black/80 dark:text-white/80 mt-1.5 focus:outline-none focus:ring-1 focus:ring-[#007AFF]/50">
                                    <input type="hidden" :name="'items[' + index + '][sku]'" x-model="item.sku">
                                </td>
                                <td class="px-3 py-2.5">
                                    <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" required
                                            class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <template x-for="u in units" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" step="any" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                           class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required
                                           class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold tabular-nums text-black dark:text-white text-[13px]" x-text="formatCurrency(item.quantity * item.unit_price)">
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button type="button" @click="removeItem(index)" :disabled="items.length <= 1" class="w-7 h-7 rounded-[6px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 disabled:opacity-20 transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Financial Calculation Footer -->
            <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
                <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Syarat &amp; Ketentuan Pengiriman</label>
                        <textarea name="terms_and_conditions" rows="2" placeholder="Pengiriman bertahap Franco Gudang Pembeli..."
                                  class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" placeholder="Catatan internal pengiriman..."
                               class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="w-full sm:w-80 space-y-2.5 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>Subtotal Item:</span>
                        <span class="font-semibold tabular-nums text-black dark:text-white" x-text="formatCurrency(subtotal)"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2 text-black/60 dark:text-white/60">
                        <span>Diskon:</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" min="0" name="discount_value" x-model="discountValue"
                                   class="w-20 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-right text-[13px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <select name="discount_type" x-model="discountType"
                                    class="h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <option value="percentage">%</option>
                                <option value="fixed">{{ $business->currency_symbol }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 text-black/60 dark:text-white/60">
                        <span>Pajak PPN (%):</span>
                        <input type="number" step="any" min="0" max="100" name="tax_percentage" x-model="taxPercentage" placeholder="11"
                               class="w-20 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-right text-[13px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-[15px] font-bold text-black dark:text-white">
                        <span>Nilai Total PO:</span>
                        <span class="text-[#007AFF] dark:text-[#0A84FF] text-[16px] tabular-nums" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('purchase-orders.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center">
                Batal
            </a>
            @if(\App\Support\Context::hasPermission('purchasing.manage') || \App\Support\Context::hasPermission('sales.pipeline'))
            <button type="submit" class="h-9 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>Terbitkan Purchase Order</span>
            </button>
            @endif
        </div>
    </form>
</div>
@endsection
