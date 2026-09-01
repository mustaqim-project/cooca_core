@extends('layouts.app', ['title' => 'Buat Penawaran Harga Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="quotationForm()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Buat Penawaran Harga Baru</h1>
            <p class="text-xs text-slate-400 mt-0.5">Terbitkan penawaran komersial resmi ke calon pelanggan.</p>
        </div>
        <a href="{{ route('sales.quotations.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('sales.quotations.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <h3 class="text-sm font-black text-white uppercase tracking-wider text-emerald-400">1. Informasi Klien & Tanggal</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-slate-300 mb-1">Pelanggan / Klien <span class="text-rose-400">*</span></label>
                    <select name="customer_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company ?? $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">No. Penawaran</label>
                    <input type="text" name="quotation_number" value="{{ $nextNumber }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs font-mono text-emerald-400 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Tanggal Terbit</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Batas Berlaku</label>
                    <input type="date" name="expiry_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-wider text-emerald-400">2. Rincian Item Penawaran</h3>
                <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-500 uppercase text-[10px] font-bold border-b border-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">Produk / Item</th>
                            <th class="py-2.5 px-3 text-right w-28">Harga (Rp)</th>
                            <th class="py-2.5 px-3 text-right w-24">Jumlah</th>
                            <th class="py-2.5 px-3 text-right w-32">Total (Rp)</th>
                            <th class="py-2.5 px-3 text-center w-12">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr>
                                <td class="py-2 px-3">
                                    <select :name="'items['+idx+'][product_id]'" x-model="item.product_id" @change="productSelected(idx)" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2.5 py-1.5 text-xs text-white font-sans focus:outline-none focus:border-emerald-500">
                                        <option value="">-- Pilih dari Katalog --</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-name="{{ $p->name }}">{{ $p->name }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" :name="'items['+idx+'][product_name]'" x-model="item.product_name">
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <input type="number" :name="'items['+idx+'][unit_price]'" x-model.number="item.unit_price" @input="calculateTotals()" min="0" step="100" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2 py-1.5 text-xs text-right text-white focus:outline-none focus:border-emerald-500">
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <input type="number" :name="'items['+idx+'][quantity]'" x-model.number="item.quantity" @input="calculateTotals()" min="0.01" step="any" class="w-full bg-slate-900 border border-slate-700/80 rounded-lg px-2 py-1.5 text-xs text-right text-white focus:outline-none focus:border-emerald-500">
                                </td>
                                <td class="py-2 px-3 text-right font-bold text-emerald-400" x-text="'Rp ' + (item.quantity * item.unit_price).toLocaleString('id-ID')"></td>
                                <td class="py-2 px-3 text-center">
                                    <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1" class="text-slate-500 hover:text-rose-400 disabled:opacity-30">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-start gap-4 font-sans">
                <div class="w-full sm:w-1/2 space-y-2">
                    <label class="block text-xs font-bold text-slate-400">Catatan & Syarat Penawaran</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Harga sudah termasuk ongkos kirim. Pembayaran DP 50%..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="w-full sm:w-80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal:</span>
                        <span class="font-mono font-bold text-white" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2 text-slate-400">
                        <span>Diskon (Rp):</span>
                        <input type="number" name="discount_amount" x-model.number="discountAmount" @input="calculateTotals()" min="0" class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs text-right font-mono text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="flex justify-between items-center gap-2 text-slate-400">
                        <span>Pajak PPN (Rp):</span>
                        <input type="number" name="tax_amount" x-model.number="taxAmount" @input="calculateTotals()" min="0" class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-xs text-right font-mono text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="pt-2 border-t border-slate-800 flex justify-between text-sm font-black text-white">
                        <span>Total Penawaran:</span>
                        <span class="font-mono text-emerald-400 text-base" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sales.quotations.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
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
