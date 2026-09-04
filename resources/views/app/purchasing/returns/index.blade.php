@extends('layouts.app', ['title' => 'Retur Pembelian'])

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Pembelian & Vendor</span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-600"></i>
                <span class="text-rose-400 font-semibold">Retur Pembelian</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Retur Pembelian ke Supplier</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola pengembalian barang rusak / tidak sesuai spesifikasi ke vendor dan penyesuaian stok otomatis.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase.returns.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-400 hover:to-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-rose-500/20 flex items-center gap-2 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Retur Pembelian</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Retur List Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Retur</th>
                        <th class="py-3.5 px-4">Supplier / Vendor</th>
                        <th class="py-3.5 px-4">Ref. Penerimaan (GR)</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4 text-right">Nilai Total</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($returns as $return)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-bold text-white">
                            <a href="{{ route('purchase.returns.show', $return) }}" class="text-rose-400 hover:underline flex items-center gap-1.5 font-sans">
                                <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i>
                                <span>{{ $return->return_number }}</span>
                            </a>
                        </td>
                        <td class="py-3 px-4 font-sans font-medium text-slate-200">
                            {{ $return->supplier->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-sans text-slate-400">
                            {{ $return->goodsReceipt->receipt_number ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-sans text-slate-400">
                            {{ $return->created_at->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-white">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center font-sans">
                            @if($return->status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Selesai</span>
                            @elseif($return->status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">Disetujui</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Draft</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-sans">
                            <a href="{{ route('purchase.returns.show', $return) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold inline-flex items-center gap-1 transition">
                                <span>Detail</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 text-xs font-sans">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i data-lucide="corner-up-left" class="w-8 h-8 text-slate-600"></i>
                                <span>Belum ada transaksi retur pembelian.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $returns->links() }}
    </div>
</div>
@endsection
