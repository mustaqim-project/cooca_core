@extends('layouts.app', [
    'title' => 'Buat Faktur Penjualan',
    'headerTitle' => 'Buat Faktur Penjualan (Invoice)',
    'headerSubtitle' => 'Terbitkan tagihan resmi dari katalog produk, purchase order pelanggan, atau hasil kalkulasi HPP'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
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

    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6">
        @csrf

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / STUDIO HEADER (macOS Window Pattern)      -->
        <!-- ===================================================== -->
        <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <!-- Minimal Breadcrumb -->
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <span>›</span>
                    <a href="{{ route('invoices.index') }}" class="hover:text-[#007AFF] transition-colors">Faktur Penjualan</a>
                    <span>›</span>
                    <span class="text-black dark:text-white font-medium">Buat Baru</span>
                </nav>
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Buat Faktur Penjualan</h1>
                <p class="text-[13px] text-black/50 dark:text-white/50">Lengkapi data penagihan komersial atau impor dari Purchase Order</p>
            </div>

            <!-- Toolbar Actions -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <a href="{{ route('invoices.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center">
                    Batal
                </a>
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                    <span>Terbitkan Faktur</span>
                </button>
            </div>
        </header>

        <!-- ===================================================== -->
        <!-- 2. DOCUMENT INFO PANEL                                -->
        <!-- ===================================================== -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
                <div>
                    <h2 class="text-[15px] font-semibold text-black dark:text-white">Identitas Penagihan &amp; Dokumen</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Tentukan pelanggan, tanggal jatuh tempo, dan referensi transaksi</p>
                </div>

                <!-- Opsi Tarik dari Purchase Order -->
                @if($availablePurchaseOrders->isNotEmpty())
                <div class="flex items-center gap-2">
                    <span class="text-[12px] text-black/50 dark:text-white/50">Tarik dari PO:</span>
                    <select name="purchase_order_id" x-model="purchaseOrderId" @change="onPoChange()" class="h-8 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-[#007AFF] font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Buat Faktur Manual --</option>
                        <template x-for="po in availablePos" :key="po.id">
                            <option :value="po.id" x-text="po.po_number + ' - ' + (po.customer ? po.customer.name : '')"></option>
                        </template>
                    </select>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-[13px]">
                <!-- Customer Select -->
                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Pilih Pelanggan / Klien *</label>
                    <select name="customer_id" x-model="customerId" @change="onCustomerChange()" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ ($selectedPo?->customer_id ?? '') === $cust->id ? 'selected' : '' }}>
                                {{ $cust->name }} {{ $cust->company_name ? "({$cust->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Faktur *</label>
                    <input type="date" name="invoice_date" x-model="invoiceDate" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Jatuh Tempo *</label>
                    <input type="date" name="due_date" x-model="dueDate" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nomor Faktur (Opsional)</label>
                    <input type="text" name="invoice_number" placeholder="Otomatis (INV-YYYYMM-XXXX)" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] tabular-nums text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Termin Pembayaran</label>
                    <input type="text" name="payment_terms" x-model="paymentTerms" placeholder="Net 30 / COD" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Status Awal</label>
                    <select name="status" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="draft">Draft (Belum Terbit)</option>
                        <option value="sent" selected>Sent (Terkirim ke Klien)</option>
                        <option value="unpaid">Unpaid (Resmi Menunggu Bayar)</option>
                    </select>
                </div>

                <!-- Gudang / Lokasi Pengeluaran Barang -->
                @if($locations->isNotEmpty())
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">
                        Gudang Pengeluaran
                    </label>
                    <select name="location_id" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Otomatis (Gudang Utama) --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->is_primary ? 'selected' : '' }}>
                                {{ $loc->name }} {{ $loc->type ? "({$loc->type})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 3. ITEMS TABLE (Dynamic Table / Dense List)           -->
        <!-- ===================================================== -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Rincian Item &amp; Harga Jual</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Pilih dari katalog untuk auto-fill harga jual dan snapshot HPP modal</p>
                </div>

                <button type="button" @click="addItem()" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] whitespace-nowrap">
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-2/5">Produk / Item *</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-24">Satuan *</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-20 text-right">Qty *</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-32 text-right">Harga Jual *</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-[#FF9500] dark:text-[#FF9F0A] w-28 text-right">HPP Modal</th>
                                <th class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-32 text-right">Subtotal</th>
                                <th class="py-2.5 px-2 w-8 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                    <td class="py-2.5 px-3">
                                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductChange(index)" 
                                                class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <option value="">-- Pilih dari Katalog Produk --</option>
                                            <template x-for="prod in products" :key="prod.id">
                                                <option :value="prod.id" x-text="prod.name + (prod.selling_price > 0 ? ' (Rp ' + new Intl.NumberFormat('id-ID').format(prod.selling_price) + ')' : '')"></option>
                                            </template>
                                        </select>
                                        <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required placeholder="Nama item pada faktur"
                                            class="w-full h-7 bg-transparent border-b border-black/10 dark:border-white/10 px-1 text-[12px] text-black dark:text-white mt-1 placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:border-[#007AFF] transition">
                                        <input type="hidden" :name="'items[' + index + '][sku]'" x-model="item.sku">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" required class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                            <template x-for="u in units" :key="u.id">
                                                <option :value="u.id" x-text="u.name + ' (' + u.code + ')'"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <input type="number" step="any" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                            class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-right tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required
                                            class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-right tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <input type="number" step="any" min="0" :name="'items[' + index + '][unit_hpp]'" x-model="item.unit_hpp" title="Snapshot HPP Modal"
                                            class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A] focus:outline-none focus:ring-2 focus:ring-[#FF9500]/50 transition">
                                    </td>
                                    <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-black dark:text-white text-[13px]" x-text="formatCurrency(item.quantity * item.unit_price)">
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" :disabled="items.length <= 1" class="p-1 rounded-[6px] text-black/30 hover:text-[#FF3B30] dark:text-white/30 dark:hover:text-[#FF453A] disabled:opacity-20 transition-colors">
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
            </div>

            <!-- Profitability Summary & Financial Calculation -->
            <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
                <!-- Left: Estimated Profit & Notes -->
                <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                    <div class="p-4 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-semibold uppercase tracking-wider">Estimasi Laba Kotor Transaksi:</div>
                            <div class="text-[20px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="formatCurrency(totalGrossProfit)"></div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="'Margin ' + Math.round(grossMarginPercentage) + '%'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Catatan Tagihan (Tampil pada Faktur)</label>
                        <input type="text" name="notes" placeholder="Terima kasih atas kerja sama bisnis Anda..." class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Ketentuan Pembayaran &amp; Garansi</label>
                        <textarea name="terms_conditions" rows="2" placeholder="Pembayaran ditransfer ke rekening resmi. Komplain maksimal 3 hari..." class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                    </div>
                </div>

                <!-- Right: Totals Breakdown -->
                <div class="w-full sm:w-80 p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>Subtotal Barang:</span>
                        <span class="tabular-nums font-medium text-black dark:text-white" x-text="formatCurrency(subtotal)"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-black/60 dark:text-white/60">Potongan Diskon:</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" min="0" name="discount_value" x-model="discountValue" class="w-20 h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-2 text-[12px] tabular-nums text-right text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <select name="discount_type" x-model="discountType" class="h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-1.5 text-[11px] text-black/70 dark:text-white/70 focus:outline-none">
                                <option value="fixed">{{ $business->currency_symbol }}</option>
                                <option value="percentage">%</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-black/60 dark:text-white/60">Pajak PPN (%):</span>
                        <input type="number" step="any" min="0" max="100" name="tax_percentage" x-model="taxPercentage" placeholder="11" class="w-20 h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-2 text-[12px] tabular-nums text-right text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-black/60 dark:text-white/60">Ongkos Kirim:</span>
                        <input type="number" step="any" min="0" name="shipping_cost" x-model="shippingCost" placeholder="0" class="w-24 h-7 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[6px] px-2 text-[12px] tabular-nums text-right text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-[15px] font-bold text-black dark:text-white">
                        <span>Total Tagihan:</span>
                        <span class="tabular-nums text-[18px] text-[#007AFF]" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky/Bottom Form Actions -->
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('invoices.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center">
                Batal
            </a>
            @if(\App\Support\Context::hasPermission('invoices.create'))
            <button type="submit" class="h-9 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
                <span>Terbitkan Faktur Penjualan</span>
            </button>
            @endif
        </div>
    </form>
</div>
@endsection
