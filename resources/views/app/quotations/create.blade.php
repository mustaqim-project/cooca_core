@extends('layouts.app', [
    'title' => 'Buat Penawaran Harga Baru',
    'headerTitle' => 'Buat Penawaran Baru',
    'headerSubtitle' => 'Terbitkan dokumen penawaran harga resmi ke calon pelanggan atau klien B2B'
])

@section('content')
<div class="max-w-[1080px] mx-auto space-y-6 pb-12" x-data="quotationForm()">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / HEADER (macOS Sonoma Style)              -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('sales.quotations.index') }}" class="hover:text-[#007AFF] transition-colors">Penawaran Harga</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Buat Baru</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Buat Penawaran Harga Baru</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Terbitkan penawaran komersial resmi ke calon pelanggan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sales.quotations.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
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
    <form action="{{ route('sales.quotations.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Data Klien & Tanggal -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
            <h3 class="text-[14px] font-semibold text-black dark:text-white">1. Informasi Klien &amp; Tanggal</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Pelanggan / Klien <span class="text-[#FF3B30]">*</span></label>
                    <select name="customer_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company ?? $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">No. Penawaran</label>
                    <input type="text" name="quotation_number" value="{{ $nextNumber }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Tanggal Terbit <span class="text-[#FF3B30]">*</span></label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Batas Berlaku</label>
                    <input type="date" name="expiry_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </div>
        </div>

        <!-- Section 2: Rincian Item Penawaran -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-[14px] font-semibold text-black dark:text-white">2. Rincian Item Penawaran</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Tentukan daftar produk komersial, harga, dan kuantitas pesanan.</p>
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
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk / Item</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Harga (Rp)</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-28">Jumlah</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Total (Rp)</th>
                            <th class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-3 py-2.5">
                                    <select :name="'items['+idx+'][product_id]'" x-model="item.product_id" @change="productSelected(idx)"
                                            class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">-- Pilih dari Katalog --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}">{{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" :name="'items['+idx+'][product_name]'" x-model="item.product_name">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" :name="'items['+idx+'][unit_price]'" x-model.number="item.unit_price" @input="calculateTotals()" min="0" step="100"
                                           class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" :name="'items['+idx+'][quantity]'" x-model.number="item.quantity" @input="calculateTotals()" min="0.01" step="any"
                                           class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold tabular-nums text-black dark:text-white" x-text="'Rp ' + (item.quantity * item.unit_price).toLocaleString('id-ID')"></td>
                                <td class="px-3 py-2.5 text-center">
                                    <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1" class="w-7 h-7 rounded-[6px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 disabled:opacity-20 transition-all inline-flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Summary & Tax -->
            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row justify-between items-start gap-5">
                <div class="w-full sm:w-1/2 space-y-1.5">
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60">Catatan &amp; Syarat Penawaran</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Harga sudah termasuk ongkos kirim. Pembayaran DP 50% saat PO diterbitkan..."
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                </div>

                <div class="w-full sm:w-80 space-y-2 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>Subtotal:</span>
                        <span class="font-semibold tabular-nums text-black dark:text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                        <span>Diskon (Rp):</span>
                        <input type="number" name="discount_amount" x-model.number="discountAmount" @input="calculateTotals()" min="0"
                               class="w-32 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="flex justify-between items-center gap-2 text-black/60 dark:text-white/60">
                        <span>Pajak PPN (Rp):</span>
                        <input type="number" name="tax_amount" x-model.number="taxAmount" @input="calculateTotals()" min="0"
                               class="w-32 h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[13px] text-right font-medium tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between text-[15px] font-bold text-black dark:text-white">
                        <span>Total Penawaran:</span>
                        <span class="text-[#007AFF] dark:text-[#0A84FF] tabular-nums" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('sales.quotations.index') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] active:opacity-80 transition-all flex items-center">
                Batal
            </a>
            <button type="submit" class="h-9 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>Terbitkan Penawaran</span>
            </button>
        </div>
    </form>
</div>

<script>
    function quotationForm() {
        return {
            items: [
                { product_id: '', product_name: '', unit_price: 0, quantity: 1 }
            ],
            subtotal: 0,
            discountAmount: 0,
            taxAmount: 0,
            grandTotal: 0,

            init() {
                this.calculateTotals();
            },

            addItem() {
                this.items.push({ product_id: '', product_name: '', unit_price: 0, quantity: 1 });
            },

            removeItem(idx) {
                if (this.items.length > 1) {
                    this.items.splice(idx, 1);
                    this.calculateTotals();
                }
            },

            productSelected(idx) {
                const sel = event.target;
                const opt = sel.options[sel.selectedIndex];
                if (opt && opt.value) {
                    this.items[idx].unit_price = parseFloat(opt.dataset.price || 0);
                    this.items[idx].product_name = opt.dataset.name || '';
                }
                this.calculateTotals();
            },

            calculateTotals() {
                this.subtotal = this.items.reduce((sum, it) => sum + ((it.quantity || 0) * (it.unit_price || 0)), 0);
                this.grandTotal = Math.max(0, (this.subtotal - (this.discountAmount || 0)) + (this.taxAmount || 0));
            }
        };
    }
</script>
@endsection
