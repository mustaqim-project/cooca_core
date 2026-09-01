@extends('layouts.app', ['title' => 'Beban Operasional Toko'])

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Beban & Biaya Operasional Toko</h1>
            <p class="text-sm text-slate-400 mt-1">Catat biaya operasional toko/outlet (listrik, kemasan, konsumsi, kebersihan) dengan jurnal otomatis.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.journals.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                ← Lihat Buku Jurnal
            </a>
            <button @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs shadow-lg shadow-rose-500/20 transition flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Catat Biaya Baru</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Total Month Expense Card -->
    <div class="p-5 rounded-2xl glass-card border-rose-500/30 bg-gradient-to-r from-rose-950/30 to-slate-900 flex justify-between items-center">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Beban Operasional Bulan Ini</div>
            <div class="text-3xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($totalExpensesThisMonth, 0, ',', '.') }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
            <i data-lucide="receipt" class="w-6 h-6"></i>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">No. Bukti / Tanggal</th>
                        <th class="py-3.5 px-4">Kategori & Keterangan</th>
                        <th class="py-3.5 px-4">Outlet / Lokasi</th>
                        <th class="py-3.5 px-4">Metode Bayar</th>
                        <th class="py-3.5 px-4 text-right">Nominal Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($expenses as $ex)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">#{{ $ex->expense_number }}</div>
                            <div class="text-[11px] text-slate-500 font-sans">{{ $ex->expense_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 uppercase">
                                {{ $ex->category }}
                            </span>
                            <div class="text-slate-200 mt-1">{{ $ex->description }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-sans">
                            {{ $ex->location->name ?? 'Outlet Utama' }}
                        </td>
                        <td class="py-3.5 px-4 font-sans uppercase text-[11px]">
                            {{ str_replace('_', ' ', $ex->payment_method) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-400 text-sm">
                            Rp {{ number_format($ex->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500 font-sans">Belum ada pencatatan biaya operasional.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Catat Biaya -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Catat Beban Operasional Baru</h3>
            <form action="{{ route('finance.expenses.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Tanggal</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Kategori</label>
                        <select name="category" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="operational">Operasional Toko</option>
                            <option value="utilities">Listrik, Air & Internet</option>
                            <option value="supplies">Kemasan & Plastik</option>
                            <option value="salaries">Gaji & Upah Kasir</option>
                            <option value="maintenance">Perawatan & Reparasi</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" required placeholder="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-base font-bold font-mono text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Metode Bayar</label>
                        <select name="payment_method" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="cash">Kas Tunai Kasir</option>
                            <option value="petty_cash">Kas Kecil (Petty Cash)</option>
                            <option value="bank_transfer">Transfer Rekening Bank</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Outlet / Lokasi</label>
                    <select name="location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        <option value="">Semua / Outlet Utama</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Keterangan Pengeluaran</label>
                    <input type="text" name="description" required placeholder="Misal: Beli es batu kristal & kantong kresek..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
