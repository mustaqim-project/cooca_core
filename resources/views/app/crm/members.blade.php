@extends('layouts.app', ['title' => 'CRM & Membership Pelanggan'])

@section('content')
<div class="space-y-6" x-data="{
    showCreditModal: false,
    selectedCustomer: null,
    paymentAmount: 0,
    paymentNotes: '',
    openCreditPayment(cust) {
        this.selectedCustomer = cust;
        this.paymentAmount = Number(cust.current_credit_balance || 0);
        this.showCreditModal = true;
    }
}">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">CRM & Membership Pelanggan</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola database pelanggan, tier loyalty membership, perolehan poin belanja, dan batas tempo (piutang).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.vouchers.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-2">
                <i data-lucide="ticket" class="w-4 h-4 text-emerald-400"></i>
                <span>Kelola Voucher & Promo</span>
            </a>
            <a href="{{ route('customers.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pelanggan</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pelanggan Terdaftar</div>
            <div class="text-2xl font-black text-white font-mono mt-1">{{ number_format($totalMembers, 0, ',', '.') }} Member</div>
            <div class="text-[11px] text-slate-500 mt-1">Database CRM aktif</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Poin Loyalitas Beredar</div>
            <div class="text-2xl font-black text-emerald-400 font-mono mt-1">{{ number_format($totalPointsIssued, 0, ',', '.') }} Poin</div>
            <div class="text-[11px] text-slate-500 mt-1">Nilai tukar: 1 Poin = Rp 100</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Piutang Belanja Pelanggan</div>
            <div class="text-2xl font-black text-amber-400 font-mono mt-1">Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Transaksi tempo yang belum lunas</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
        <form method="GET" action="{{ route('crm.members.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau no. telepon..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <select name="tier" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Tier Membership</option>
                    <option value="bronze" {{ request('tier') === 'bronze' ? 'selected' : '' }}>Bronze</option>
                    <option value="silver" {{ request('tier') === 'silver' ? 'selected' : '' }}>Silver</option>
                    <option value="gold" {{ request('tier') === 'gold' ? 'selected' : '' }}>Gold</option>
                    <option value="platinum" {{ request('tier') === 'platinum' ? 'selected' : '' }}>Platinum</option>
                </select>
            </div>
            <div>
                <select name="segment" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none">
                    <option value="">Semua Segmen</option>
                    <option value="regular" {{ request('segment') === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="vip" {{ request('segment') === 'vip' ? 'selected' : '' }}>VIP</option>
                    <option value="wholesale" {{ request('segment') === 'wholesale' ? 'selected' : '' }}>Wholesale / Grosir</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('crm.members.index') }}" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Members Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Nama Pelanggan</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4 text-center">Tier & Segmen</th>
                        <th class="py-3.5 px-4 text-right">Saldo Poin</th>
                        <th class="py-3.5 px-4 text-right">Akumulasi Belanja</th>
                        <th class="py-3.5 px-4 text-right">Saldo Piutang</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($customers as $c)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">{{ $c->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $c->company_name ?? 'Personal' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div>{{ $c->phone ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $c->email ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center space-x-1">
                            @php
                                $tierColor = match($c->membership_tier) {
                                    'platinum' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
                                    'gold' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                    'silver' => 'bg-slate-300/20 text-slate-200 border-slate-300/30',
                                    default => 'bg-amber-800/20 text-amber-500 border-amber-800/30',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $tierColor }}">
                                {{ $c->membership_tier ?? 'Bronze' }}
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-800 text-slate-400">
                                {{ strtoupper($c->segment ?? 'Regular') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                            {{ number_format($c->points_balance, 0, ',', '.') }} Poin
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-200">
                            Rp {{ number_format($c->total_spent, 0, ',', '.') }}
                            <div class="text-[10px] text-slate-500">{{ $c->total_orders_count }} Transaksi</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold {{ $c->current_credit_balance > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                            Rp {{ number_format($c->current_credit_balance, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($c->current_credit_balance > 0)
                                <button @click="openCreditPayment(@json($c))" class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 text-xs font-semibold transition">
                                    Catat Pelunasan
                                </button>
                            @else
                                <span class="text-slate-500 text-xs">Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">Belum ada data pelanggan CRM.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Pelunasan Piutang -->
    <div x-show="showCreditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Catat Pelunasan Piutang</h3>
            <form :action="'{{ url('/crm/customers') }}/' + (selectedCustomer ? selectedCustomer.id : '') + '/credit-payment'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex justify-between items-center">
                    <div>
                        <div class="font-bold text-white" x-text="selectedCustomer ? selectedCustomer.name : ''"></div>
                        <div class="text-[11px] text-slate-400">Total Piutang Berjalan:</div>
                    </div>
                    <span class="font-bold font-mono text-base text-amber-400" x-text="'Rp ' + Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0).toLocaleString('id-ID')"></span>
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Nominal yang Dibayarkan (Rp)</label>
                    <input type="number" name="amount" x-model.number="paymentAmount" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold font-mono text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Catatan / No. Bukti Transfer</label>
                    <input type="text" name="notes" placeholder="Misal: Transfer BCA pelunasan..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCreditModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
