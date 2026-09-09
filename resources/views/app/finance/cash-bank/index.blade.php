@extends('layouts.app', ['title' => 'Kas & Rekening Bank'])

@section('content')
<div class="space-y-6" x-data="{ showTransferModal: false, showInflowModal: false, showOutflowModal: false }">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-400 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Keuangan</span>
                <span>/</span>
                <span class="text-amber-400 font-semibold">Kas & Bank</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Kas & Rekening Bank</h1>
            <p class="text-sm text-slate-400 mt-0.5">Pantau saldo rekening kasir, bank transfer, dan mutasi arus kas operasional.</p>
        </div>
        <div class="flex items-center flex-wrap gap-2.5">
            <button type="button" @click="showInflowModal = true" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                <span>Kas Masuk</span>
            </button>
            <button type="button" @click="showOutflowModal = true" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-500/20 transition flex items-center gap-1.5">
                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                <span>Kas Keluar</span>
            </button>
            <button type="button" @click="showTransferModal = true" class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/20 transition flex items-center gap-1.5">
                <i data-lucide="repeat" class="w-4 h-4"></i>
                <span>Transfer Antar Akun</span>
            </button>
            <a href="{{ route('finance.cash-bank.ledger') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center gap-1.5">
                <i data-lucide="book" class="w-4 h-4 text-amber-400"></i>
                <span>Buku Kas & Ledger</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-xs text-emerald-300 font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Account Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($accounts as $account)
        @php
            $icon = match($account->type) {
                'bank' => 'landmark',
                'qris', 'ewallet' => 'qr-code',
                'petty_cash' => 'coins',
                default => 'wallet'
            };
            $color = match($account->type) {
                'bank' => 'cyan',
                'qris', 'ewallet' => 'purple',
                'petty_cash' => 'amber',
                default => 'emerald'
            };
        @endphp
        <div class="glass-card rounded-2xl p-5 border border-slate-800 hover:border-slate-700 transition relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-{{ $color }}-500/10 text-{{ $color }}-400 flex items-center justify-center">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">{{ $account->name }}</h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ strtoupper($account->type) }}</span>
                    </div>
                </div>
                @if($account->account_number)
                    <span class="text-[10px] font-mono text-slate-500">{{ $account->account_number }}</span>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-baseline justify-between">
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Saldo Tersedia</span>
                    <div class="text-xl font-black text-white font-mono mt-0.5">
                        Rp {{ number_format($account->current_balance, 0, ',', '.') }}
                    </div>
                </div>
                <a href="{{ route('finance.cash-bank.ledger', ['account_id' => $account->id]) }}" class="text-[11px] font-bold text-emerald-400 hover:underline inline-flex items-center gap-1">
                    <span>Mutasi</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card rounded-2xl p-8 border border-slate-800 text-center text-slate-500 text-xs">
            Belum ada akun kas atau rekening bank yang tercatat. Akun kas akan otomatis dibuat saat transaksi kasir pertama.
        </div>
        @endforelse
    </div>

    <!-- Recent Transactions Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-amber-400"></i>
                <span>Mutasi Transaksi Kas Terbaru</span>
            </h2>
            <a href="{{ route('finance.cash-bank.ledger') }}" class="text-xs font-bold text-emerald-400 hover:underline flex items-center gap-1">
                <span>Lihat Seluruh Buku Kas</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[620px]">
                <thead class="border-b border-slate-800 bg-slate-950/60 uppercase text-[10px] text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 whitespace-nowrap">Tanggal</th>
                        <th class="p-4 whitespace-nowrap">Akun Rekening</th>
                        <th class="p-4 whitespace-nowrap">Arus</th>
                        <th class="p-4 whitespace-nowrap">Keterangan / Referensi</th>
                        <th class="p-4 text-right whitespace-nowrap">Nominal</th>
                        <th class="p-4 text-right whitespace-nowrap">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-4 font-mono text-slate-400">{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td class="p-4 font-semibold text-white">{{ $transaction->cashAccount->name }}</td>
                        <td class="p-4">
                            @if($transaction->type === 'out')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-500/10 text-rose-400 border border-rose-500/20">Keluar</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Masuk</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-300">{{ $transaction->description }}</td>
                        <td class="p-4 text-right font-mono font-bold {{ $transaction->type === 'out' ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ $transaction->type === 'out' ? '-' : '+' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-right font-mono font-black text-white">
                            Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            Belum ada riwayat mutasi kas yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Kas Masuk -->
    <div x-show="showInflowModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card rounded-2xl border border-slate-700 max-w-md w-full p-6 shadow-2xl space-y-4" @click.outside="showInflowModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="arrow-down-left" class="w-4 h-4 text-emerald-400"></i>
                    <span>Catat Penerimaan Kas Masuk</span>
                </h3>
                <button type="button" @click="showInflowModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('finance.cash-bank.inflow') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Metode / Jenis Rekening</label>
                    <select name="account_method" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="cash">Kas Tunai (Cash)</option>
                        <option value="bank_transfer">Rekening Bank</option>
                        <option value="qris">QRIS / E-Wallet</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                    <input name="amount" type="number" min="1" step="0.01" required placeholder="500000" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Keterangan Penerimaan *</label>
                    <input name="description" required placeholder="Contoh: Setoran modal awal atau pendapatan non-POS" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showInflowModal = false" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-500/20">Simpan Kas Masuk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Kas Keluar -->
    <div x-show="showOutflowModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card rounded-2xl border border-slate-700 max-w-md w-full p-6 shadow-2xl space-y-4" @click.outside="showOutflowModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-rose-400"></i>
                    <span>Catat Pengeluaran Kas Keluar</span>
                </h3>
                <button type="button" @click="showOutflowModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('finance.cash-bank.outflow') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Sumber Rekening Kas</label>
                    <select name="account_method" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="cash">Kas Tunai (Cash)</option>
                        <option value="bank_transfer">Rekening Bank</option>
                        <option value="qris">QRIS / E-Wallet</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                    <input name="amount" type="number" min="1" step="0.01" required placeholder="250000" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Keterangan Pengeluaran *</label>
                    <input name="description" required placeholder="Contoh: Pengambilan prive atau pengeluaran khusus" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showOutflowModal = false" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shadow-lg shadow-rose-500/20">Simpan Kas Keluar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Transfer Antar Akun -->
    <div x-show="showTransferModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card rounded-2xl border border-slate-700 max-w-md w-full p-6 shadow-2xl space-y-4" @click.outside="showTransferModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="repeat" class="w-4 h-4 text-cyan-400"></i>
                    <span>Transfer Antar Rekening Kas</span>
                </h3>
                <button type="button" @click="showTransferModal = false" class="text-slate-400 hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('finance.cash-bank.transfer') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Dari Akun Asal *</label>
                    <select name="from_account_id" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} (Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Ke Akun Tujuan *</label>
                    <select name="to_account_id" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} (Rp {{ number_format($account->current_balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nominal Transfer (Rp) *</label>
                    <input name="amount" type="number" min="1" step="0.01" required placeholder="1000000" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Keterangan Transfer *</label>
                    <input name="description" required placeholder="Contoh: Setoran uang tunai kasir ke rekening BCA" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showTransferModal = false" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold shadow-lg shadow-cyan-500/20">Eksekusi Transfer</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
