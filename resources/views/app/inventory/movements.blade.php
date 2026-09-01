@extends('layouts.app', ['title' => 'Kartu Stok & Mutasi'])

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Kartu Stok & Riwayat Mutasi</h1>
            <p class="text-sm text-slate-400 mt-1">Audit trail lengkap seluruh pergerakan barang (penjualan kasir, retur, transfer, opname, dan penyesuaian).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.stocks') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                ← Kembali ke Saldo Stok
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <select name="product_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') === $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Lokasi / Outlet</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="movement_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="pos_sale" {{ request('movement_type') === 'pos_sale' ? 'selected' : '' }}>Penjualan POS</option>
                    <option value="pos_refund" {{ request('movement_type') === 'pos_refund' ? 'selected' : '' }}>Retur POS</option>
                    <option value="transfer_in" {{ request('movement_type') === 'transfer_in' ? 'selected' : '' }}>Transfer Masuk</option>
                    <option value="transfer_out" {{ request('movement_type') === 'transfer_out' ? 'selected' : '' }}>Transfer Keluar</option>
                    <option value="opname" {{ request('movement_type') === 'opname' ? 'selected' : '' }}>Stock Opname</option>
                    <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('inventory.movements') }}" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Lokasi</th>
                        <th class="py-3.5 px-4">Tipe Mutasi</th>
                        <th class="py-3.5 px-4 text-right">Perubahan Qty</th>
                        <th class="py-3.5 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3.5 px-4">Keterangan / Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4 font-sans text-slate-400">
                            {{ $m->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4 font-sans font-bold text-white">
                            {{ $m->product->name ?? 'Produk' }}
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            {{ $m->location->name ?? 'Outlet' }}
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $m->quantity_change > 0 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                {{ str_replace('_', ' ', $m->movement_type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold {{ $m->quantity_change > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim((string)$m->quantity_change, '0'), '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-white">
                            {{ rtrim(rtrim((string)$m->balance_after, '0'), '.') }}
                        </td>
                        <td class="py-3.5 px-4 font-sans text-slate-400">
                            <div>{{ $m->notes ?? '-' }}</div>
                            @if($m->reference_number)
                                <div class="text-[10px] text-slate-500 font-mono">Ref: {{ $m->reference_number }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-sans">Belum ada riwayat mutasi stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
