@extends('layouts.app', ['title' => 'Riwayat Transaksi POS'])

@section('content')
<div class="space-y-6" x-data="{
    showDetailModal: false,
    showVoidModal: false,
    showRefundModal: false,
    selectedOrder: null,
    selectedOrderId: null,
    viewDetail(order) {
        this.selectedOrder = order;
        this.showDetailModal = true;
    },
    openVoid(id) {
        this.selectedOrderId = id;
        this.showVoidModal = true;
    },
    openRefund(id) {
        this.selectedOrderId = id;
        this.showRefundModal = true;
    }
}">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Riwayat Transaksi POS</h1>
            <p class="text-sm text-slate-400 mt-1">Daftar lengkap transaksi penjualan kasir, margin HPP terintegrasi, cetak ulang struk, void, dan retur.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pos.terminal') }}" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>Buka Terminal Kasir</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('pos.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Order atau Pelanggan..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Dibatalkan (Voided)</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Diretur (Refunded)</option>
                </select>
            </div>
            <div>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('pos.orders.index') }}" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Order</th>
                        <th class="py-3.5 px-4">Waktu / Kasir</th>
                        <th class="py-3.5 px-4">Pelanggan</th>
                        <th class="py-3.5 px-4 text-right">Total Bayar</th>
                        <th class="py-3.5 px-4 text-right">HPP (Modal)</th>
                        <th class="py-3.5 px-4 text-right">Laba Kotor</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $o)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white font-mono">#{{ $o->order_number }}</div>
                            <div class="text-[10px] text-slate-500 uppercase">{{ $o->order_type }} • {{ $o->location->name ?? 'Outlet' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div>{{ $o->order_date->format('d/m/Y') }} {{ $o->created_at->format('H:i') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $o->user->name ?? 'Kasir' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($o->customer)
                                <div class="font-semibold text-emerald-400">{{ $o->customer->name }}</div>
                                <span class="px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 text-[9px] font-bold uppercase">{{ $o->customer->membership_tier ?? 'Bronze' }}</span>
                            @else
                                <div class="text-slate-300">{{ $o->customer_name_guest ?? 'Pelanggan Umum' }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                            Rp {{ number_format($o->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-400">
                            Rp {{ number_format($o->total_hpp_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                            Rp {{ number_format($o->total_gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($o->status === 'completed')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Selesai</span>
                            @elseif($o->status === 'voided')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">Void</span>
                            @elseif($o->status === 'refunded')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">Retur</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-1">
                            <a href="{{ route('pos.receipt', $o->id) }}" target="_blank" title="Cetak Struk" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white inline-flex items-center">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            </a>
                            <button @click="viewDetail(@json($o))" title="Lihat Detail" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white inline-flex items-center">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            </button>
                            @if($o->status === 'completed')
                                <button @click="openVoid('{{ $o->id }}')" title="Batalkan (Void)" class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-500/30 text-rose-400 inline-flex items-center">
                                    <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                </button>
                                <button @click="openRefund('{{ $o->id }}')" title="Retur / Refund" class="p-1.5 rounded-lg bg-slate-800 hover:bg-amber-500/30 text-amber-400 inline-flex items-center">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">Belum ada transaksi POS yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Detail Order -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-lg glass-card rounded-2xl border border-slate-700 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-black text-lg text-white" x-text="'Detail Order #' + (selectedOrder ? selectedOrder.order_number : '')"></h3>
                    <div class="text-xs text-slate-400" x-text="selectedOrder ? (selectedOrder.order_date + ' • ' + selectedOrder.status) : ''"></div>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <!-- Items -->
            <div class="space-y-2 text-xs">
                <div class="font-bold text-slate-400 uppercase tracking-wider">Item Terjual:</div>
                <template x-for="it in (selectedOrder ? selectedOrder.items : [])" :key="it.id">
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-white" x-text="it.product_name"></div>
                            <div class="text-[11px] text-slate-400 font-mono" x-text="it.quantity + ' x Rp ' + Number(it.unit_price).toLocaleString('id-ID')"></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold font-mono text-white" x-text="'Rp ' + Number(it.total_price).toLocaleString('id-ID')"></div>
                            <div class="text-[10px] text-slate-500 font-mono" x-text="'HPP: Rp ' + Number(it.total_hpp).toLocaleString('id-ID')"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Financial Summary -->
            <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5 text-xs font-mono">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span x-text="'Rp ' + Number(selectedOrder ? selectedOrder.subtotal : 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Total HPP / Modal:</span>
                    <span x-text="'Rp ' + Number(selectedOrder ? selectedOrder.total_hpp_cost : 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-emerald-400 font-bold border-t border-slate-800 pt-1.5 text-sm">
                    <span>Laba Kotor (Gross Profit):</span>
                    <span x-text="'Rp ' + Number(selectedOrder ? selectedOrder.total_gross_profit : 0).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Void -->
    <div x-show="showVoidModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white text-rose-400">Batalkan Transaksi (Void)</h3>
            <p class="text-xs text-slate-400">Void akan membatalkan transaksi dan otomatis mengembalikan kuantitas produk ke stok inventori.</p>
            <form :action="'{{ url('/pos/orders') }}/' + selectedOrderId + '/void'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Alasan Pembatalan</label>
                    <input type="text" name="reason" required placeholder="Misal: Salah input kasir, pelanggan batal..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showVoidModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs">Eksekusi Void</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Refund -->
    <div x-show="showRefundModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white text-amber-400">Pengembalian / Retur (Refund)</h3>
            <form :action="'{{ url('/pos/orders') }}/' + selectedOrderId + '/refund'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Alasan Retur</label>
                    <input type="text" name="reason" required placeholder="Misal: Barang cacat, komplain rasa..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="restore_stock" value="1" checked id="restore_stock" class="rounded bg-slate-900 border-slate-700 text-emerald-500">
                    <label for="restore_stock" class="text-slate-300">Kembalikan produk ke stok inventori (centang jika barang masih layak)</label>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showRefundModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs">Proses Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
