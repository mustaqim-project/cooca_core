@extends('layouts.app', ['title' => 'Penawaran Harga (Quotations)'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    B2B & SALES PIPELINE
                </span>
                <span class="text-xs text-slate-400 font-mono">Surat Penawaran Resmi</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Penawaran Harga (Quotations)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Buat surat penawaran harga komersial ke calon klien dan konversi ke pesanan penjualan dalam 1 klik.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sales.quotations.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Penawaran Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800 flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('sales.quotations.index') }}" class="flex-1 flex flex-wrap gap-2 w-full">
            <div class="relative flex-1 min-w-[200px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor penawaran atau nama pelanggan..." 
                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            <select name="status" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                <option value="">Semua Status</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluwarsa</option>
            </select>
        </form>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[620px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">No. Penawaran</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Batas Berlaku</th>
                        <th class="py-3 px-4 text-right">Nilai Penawaran</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($quotations as $q)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4 font-bold text-white">
                            <a href="{{ route('sales.quotations.show', $q) }}" class="text-emerald-400 hover:underline">
                                {{ $q->quotation_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-sans font-medium text-white">
                            {{ $q->customer->name }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">
                            {{ $q->date->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400">
                            {{ $q->expiry_date ? $q->expiry_date->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-white text-sm">
                            Rp {{ number_format($q->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-sans">
                            @if($q->status === 'accepted')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Disetujui</span>
                            @elseif($q->status === 'sent')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">Terkirim</span>
                            @elseif($q->status === 'rejected')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">Ditolak</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">{{ strtoupper($q->status) }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-sans">
                            <a href="{{ route('sales.quotations.show', $q) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 font-sans">Belum ada surat penawaran harga.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $quotations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
