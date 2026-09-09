@extends('layouts.app', [
    'title' => 'CRM & Membership Pelanggan — Cooca UMKM',
    'headerTitle' => 'CRM & Loyalitas Pelanggan',
    'headerSubtitle' => 'Kelola database pelanggan setia, tier membership, perolehan poin belanja, dan pelunasan piutang tempo'
])

@section('content')
<script>
    window.COOCA_MEMBERS = @json($customers->items());
</script>

<div class="space-y-8" x-data="{
    membersList: window.COOCA_MEMBERS || [],
    showCreditModal: false,
    showPointsModal: false,
    selectedCustomer: null,
    paymentAmount: 0,
    paymentNotes: '',
    pointHistories: [],
    loadingPoints: false,

    openCreditPayment(id) {
        const cust = this.membersList.find(m => m.id == id);
        if (!cust) return;
        this.selectedCustomer = cust;
        this.paymentAmount = Number(cust.current_credit_balance || 0);
        this.paymentNotes = '';
        this.showCreditModal = true;
    },

    async openPointHistory(id) {
        const cust = this.membersList.find(m => m.id == id);
        if (!cust) return;
        this.selectedCustomer = cust;
        this.showPointsModal = true;
        this.loadingPoints = true;
        this.pointHistories = [];

        try {
            const res = await fetch('/crm/customers/' + cust.id + '/points', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                this.pointHistories = data.histories || [];
            }
        } catch (e) {
            console.error('Failed to load point histories:', e);
        } finally {
            this.loadingPoints = false;
        }
    },

    setQuickAmount(val) {
        this.paymentAmount = Math.max(0, Math.min(val, Number(this.selectedCustomer ? this.selectedCustomer.current_credit_balance : 0)));
    }
}">

    <!-- Page Header & Quick Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                    CUSTOMER RELATIONSHIP MANAGEMENT
                </span>
                <span class="text-xs text-slate-500 font-mono hidden sm:inline">• Loyalty &amp; Credit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">CRM &amp; Membership Pelanggan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl leading-relaxed">
                Pantau riwayat perolehan poin belanja member, klasifikasi segmen pelanggan VIP, serta kelola batas tempo dan pelunasan piutang bisnis Anda.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('crm.vouchers.index') }}" 
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-800 text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <i data-lucide="ticket" class="w-4 h-4 text-emerald-400"></i>
                <span>Kelola Voucher &amp; Promo</span>
            </a>
            <a href="{{ route('customers.index') }}" 
               class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2 group">
                <i data-lucide="user-plus" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                <span>Tambah Pelanggan Baru</span>
            </a>
        </div>
    </div>

    <!-- Flash Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 text-emerald-300 text-xs flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top KPI Metrics Cards (4 Columns Responsive) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- 1. Total Members -->
        <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 text-slate-300 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="users" class="w-6 h-6 text-emerald-400"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block">Total Pelanggan Terdaftar</span>
                <div class="text-xl sm:text-2xl font-black text-white font-mono tracking-tight mt-0.5">
                    {{ number_format($totalMembers, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-slate-400">Member database CRM</span>
            </div>
        </div>

        <!-- 2. Loyalty Points Issued -->
        <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="coins" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-400/80 font-bold block">Poin Loyalitas Beredar</span>
                <div class="text-xl sm:text-2xl font-black text-emerald-400 font-mono tracking-tight mt-0.5">
                    {{ number_format($totalPointsIssued, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-slate-400">Nilai: 1 Poin = Rp 100</span>
            </div>
        </div>

        <!-- 3. Total Credit Receivable (Piutang) -->
        <div class="p-5 rounded-3xl bg-slate-900/80 border border-slate-800 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400/80 font-bold block">Piutang Belanja Pelanggan</span>
                <div class="text-xl sm:text-2xl font-black text-amber-400 font-mono tracking-tight mt-0.5">
                    Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-slate-400">Total tempo belum lunas</span>
            </div>
        </div>

        <!-- 4. Quick Shortcut / Loyalty Rules -->
        <div class="p-5 rounded-3xl bg-gradient-to-br from-cyan-950/40 via-slate-900/80 to-slate-900/80 border border-cyan-500/30 shadow-xl backdrop-blur-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center shrink-0 shadow-inner">
                <i data-lucide="award" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-mono uppercase tracking-wider text-cyan-400 font-bold block">Tier Membership</span>
                <div class="text-xs font-bold text-white mt-1">Bronze &bull; Silver &bull; Gold &bull; Platinum</div>
                <span class="text-[11px] text-slate-400">Reward kasir otomatis</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="p-5 sm:p-6 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl backdrop-blur-xl">
        <form method="GET" action="{{ route('crm.members.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-center">
            <!-- Search Query -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama pelanggan, nomor telepon, atau email..." 
                       class="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition">
            </div>

            <!-- Tier Filter -->
            <div class="sm:col-span-3">
                <select name="tier" 
                        class="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-2xl px-3.5 py-2.5 text-xs text-slate-200 focus:outline-none transition">
                    <option value="">Semua Tier Membership</option>
                    <option value="bronze" {{ request('tier') === 'bronze' ? 'selected' : '' }}>Tier: Bronze</option>
                    <option value="silver" {{ request('tier') === 'silver' ? 'selected' : '' }}>Tier: Silver</option>
                    <option value="gold" {{ request('tier') === 'gold' ? 'selected' : '' }}>Tier: Gold</option>
                    <option value="platinum" {{ request('tier') === 'platinum' ? 'selected' : '' }}>Tier: Platinum</option>
                </select>
            </div>

            <!-- Segment Filter -->
            <div class="sm:col-span-2">
                <select name="segment" 
                        class="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-2xl px-3.5 py-2.5 text-xs text-slate-200 focus:outline-none transition">
                    <option value="">Semua Segmen</option>
                    <option value="regular" {{ request('segment') === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="vip" {{ request('segment') === 'vip' ? 'selected' : '' }}>VIP</option>
                    <option value="wholesale" {{ request('segment') === 'wholesale' ? 'selected' : '' }}>Wholesale / Grosir</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->hasAny(['search', 'tier', 'segment']))
                <a href="{{ route('crm.members.index') }}" 
                   class="px-3 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-semibold transition"
                   title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Members Container Card -->
    <div class="rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl overflow-hidden backdrop-blur-xl">

        <!-- Table Header Bar -->
        <div class="p-5 sm:p-6 border-b border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <i data-lucide="contact" class="w-4 h-4 text-emerald-400"></i>
                    <span>Daftar Pelanggan CRM &amp; Poin Loyalitas</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Urutan berdasarkan akumulasi nilai transaksi belanja tertinggi.</p>
            </div>
            <span class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800">
                Menampilkan {{ $customers->count() }} dari {{ $customers->total() }} pelanggan
            </span>
        </div>

        @if($customers->count() > 0)

            <!-- Desktop View: Dense Data Table (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/70 text-slate-400 font-mono uppercase text-[10px] border-b border-slate-800 tracking-wider">
                        <tr>
                            <th class="py-4 px-5">Nama Pelanggan</th>
                            <th class="py-4 px-4">Kontak</th>
                            <th class="py-4 px-4 text-center">Tier &amp; Segmen</th>
                            <th class="py-4 px-4 text-right">Saldo Poin</th>
                            <th class="py-4 px-4 text-right">Akumulasi Belanja</th>
                            <th class="py-4 px-4 text-right">Saldo Piutang</th>
                            <th class="py-4 px-5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @foreach($customers as $c)
                        @php
                            $tier = strtolower($c->membership_tier ?? 'bronze');
                            $tierBadge = match($tier) {
                                'platinum' => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30',
                                'gold' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                'silver' => 'bg-slate-300/15 text-slate-200 border-slate-300/30',
                                default => 'bg-amber-800/20 text-amber-500 border-amber-800/30',
                            };
                        @endphp
                        <tr class="hover:bg-slate-850/50 transition duration-150">
                            <!-- Name & Avatar -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0 shadow-inner">
                                        {{ strtoupper(substr($c->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-sm truncate">{{ $c->name }}</div>
                                        <div class="text-[11px] text-slate-400 truncate">{{ $c->company_name ?: 'Personal Member' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-4 px-4">
                                <div class="font-mono text-slate-200">{{ $c->phone ?: '-' }}</div>
                                <div class="text-[10px] text-slate-500 truncate">{{ $c->email ?: '-' }}</div>
                            </td>

                            <!-- Tier & Segment -->
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $tierBadge }}">
                                        {{ $c->membership_tier ?: 'Bronze' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-slate-800 text-slate-400 border border-slate-700">
                                        {{ $c->segment ?: 'Regular' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Loyalty Points with History Shortcut -->
                            <td class="py-4 px-4 text-right">
                                <button type="button" 
                                        @click="openPointHistory('{{ $c->id }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/25 transition text-emerald-400 font-mono font-bold text-xs group"
                                        title="Klik untuk melihat riwayat mutasi poin">
                                    <i data-lucide="coins" class="w-3.5 h-3.5 group-hover:rotate-12 transition-transform"></i>
                                    <span>{{ number_format($c->points_balance, 0, ',', '.') }}</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-60"></i>
                                </button>
                            </td>

                            <!-- Total Spent & Transaction Count -->
                            <td class="py-4 px-4 text-right">
                                <div class="font-mono font-bold text-slate-100">
                                    Rp {{ number_format($c->total_spent, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono">
                                    {{ $c->total_orders_count ?? ($c->pos_orders_count ?? 0) }} Pesanan
                                </div>
                            </td>

                            <!-- Credit Balance (Piutang) -->
                            <td class="py-4 px-4 text-right">
                                @if($c->current_credit_balance > 0)
                                    <div class="font-mono font-black text-amber-400">
                                        Rp {{ number_format($c->current_credit_balance, 0, ',', '.') }}
                                    </div>
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        Tempo Aktif
                                    </span>
                                @else
                                    <span class="font-mono text-xs text-slate-500">Rp 0</span>
                                    <div class="text-[10px] text-emerald-500/80 font-medium">Lunas</div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($c->current_credit_balance > 0)
                                        <button type="button" 
                                                @click="openCreditPayment('{{ $c->id }}')" 
                                                class="px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center gap-1 shadow-sm">
                                            <i data-lucide="hand-coins" class="w-3.5 h-3.5"></i>
                                            <span>Pelunasan</span>
                                        </button>
                                    @else
                                        <button type="button" 
                                                @click="openPointHistory('{{ $c->id }}')" 
                                                class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition"
                                                title="Riwayat Poin">
                                            <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Clean Card Stream (Visible on Mobile Only, Zero Table Overflow!) -->
            <div class="block md:hidden divide-y divide-slate-800/80">
                @foreach($customers as $c)
                @php
                    $tier = strtolower($c->membership_tier ?? 'bronze');
                    $tierBadge = match($tier) {
                        'platinum' => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30',
                        'gold' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                        'silver' => 'bg-slate-300/15 text-slate-200 border-slate-300/30',
                        default => 'bg-amber-800/20 text-amber-500 border-amber-800/30',
                    };
                @endphp
                <div class="p-5 space-y-4 hover:bg-slate-850/40 transition">
                    <!-- Card Top: Customer Name & Tier Badges -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0 shadow-inner">
                                {{ strtoupper(substr($c->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-white text-sm truncate">{{ $c->name }}</div>
                                <div class="text-[11px] text-slate-400 truncate">{{ $c->phone ?: ($c->email ?: 'Personal') }}</div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border {{ $tierBadge }}">
                                {{ $c->membership_tier ?: 'Bronze' }}
                            </span>
                            <span class="text-[9px] font-mono text-slate-500 uppercase">{{ $c->segment ?: 'Regular' }}</span>
                        </div>
                    </div>

                    <!-- Card Body: 3-Pillar Micro Stats -->
                    <div class="grid grid-cols-3 gap-2 p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800/80 text-center">
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 block uppercase font-mono font-bold">Poin</span>
                            <button type="button" 
                                    @click="openPointHistory('{{ $c->id }}')"
                                    class="text-xs font-mono font-black text-emerald-400 hover:underline">
                                {{ number_format($c->points_balance, 0, ',', '.') }}
                            </button>
                        </div>
                        <div class="space-y-0.5 border-x border-slate-900">
                            <span class="text-[10px] text-slate-500 block uppercase font-mono font-bold">Total Belanja</span>
                            <div class="text-xs font-mono font-bold text-slate-200">
                                Rp {{ number_format($c->total_spent, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 block uppercase font-mono font-bold">Piutang</span>
                            <div class="text-xs font-mono font-black {{ $c->current_credit_balance > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                                Rp {{ number_format($c->current_credit_balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Actions -->
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <button type="button" 
                                @click="openPointHistory('{{ $c->id }}')"
                                class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-emerald-400 transition font-semibold">
                            <i data-lucide="history" class="w-3.5 h-3.5"></i>
                            <span>Riwayat Poin</span>
                        </button>

                        @if($c->current_credit_balance > 0)
                            <button type="button" 
                                    @click="openCreditPayment('{{ $c->id }}')"
                                    class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="hand-coins" class="w-4 h-4"></i>
                                <span>Pelunasan Piutang</span>
                            </button>
                        @else
                            <span class="text-[11px] font-mono text-emerald-500/80 font-bold flex items-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Lunas</span>
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

        @else
            <!-- Empty State -->
            <div class="py-16 px-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-3xl bg-slate-800/80 border border-slate-700/60 text-slate-500 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="user-x" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-sm font-black text-white">Tidak Ada Data Pelanggan Ditemukan</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Coba ubah kriteria pencarian atau tambahkan pelanggan baru untuk mulai mencatat poin loyalitas kasir.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah Pelanggan Sekarang</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="p-5 border-t border-slate-800 bg-slate-950/40">
            {{ $customers->links() }}
        </div>
        @endif

    </div>

    <!-- Modal 1: Catat Pelunasan Piutang (Credit Repayment) -->
    <div x-show="showCreditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4 transition-all"
         @keydown.escape.window="showCreditModal = false">
        
        <div class="w-full max-w-md p-6 sm:p-7 rounded-3xl bg-slate-900 border border-slate-700 shadow-2xl space-y-5 relative"
             @click.away="showCreditModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center">
                        <i data-lucide="hand-coins" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Catat Pelunasan Piutang</h3>
                        <span class="text-[10px] text-slate-400 font-mono">Transaksi Tempo Pelanggan</span>
                    </div>
                </div>
                <button type="button" @click="showCreditModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/crm/customers') }}/' + (selectedCustomer ? selectedCustomer.id : '') + '/credit-payment'" 
                  method="POST" 
                  class="space-y-4 text-xs">
                @csrf

                <!-- Customer Info Banner -->
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex justify-between items-center">
                    <div>
                        <div class="font-bold text-white text-sm" x-text="selectedCustomer ? selectedCustomer.name : ''"></div>
                        <div class="text-[11px] text-slate-400">Total Piutang Berjalan:</div>
                    </div>
                    <span class="font-bold font-mono text-lg text-amber-400" 
                          x-text="'Rp ' + Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0).toLocaleString('id-ID')"></span>
                </div>

                <!-- Fast Nominal Quick-fill Chips -->
                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-bold uppercase text-[10px] tracking-wider">Pilih Cepat Nominal Pelunasan:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" 
                                @click="setQuickAmount(Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0))" 
                                class="py-2 px-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 font-bold text-xs border border-slate-700 transition">
                            Lunasi Penuh
                        </button>
                        <button type="button" 
                                @click="setQuickAmount(Math.round(Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0) / 2))" 
                                class="py-2 px-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition">
                            50% Piutang
                        </button>
                        <button type="button" 
                                @click="setQuickAmount(50000)" 
                                class="py-2 px-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition">
                            Rp 50.000
                        </button>
                    </div>
                </div>

                <!-- Input Nominal -->
                <div class="space-y-1.5">
                    <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                        Nominal Pembayaran (Rp) <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm">Rp</span>
                        <input type="number" 
                               name="amount" 
                               required
                               min="1"
                               x-model.number="paymentAmount" 
                               class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl pl-10 pr-4 py-3 text-lg font-bold font-mono text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                </div>

                <!-- Input Notes -->
                <div class="space-y-1.5">
                    <label class="block text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                        Catatan / Nomor Bukti Transfer <span class="text-slate-500">(Opsional)</span>
                    </label>
                    <input type="text" 
                           name="notes" 
                           x-model="paymentNotes"
                           placeholder="Misal: Transfer Bank BCA / Tunai di Kasir..." 
                           class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                </div>

                <!-- Modal Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                    <button type="button" 
                            @click="showCreditModal = false" 
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Pelunasan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Riwayat Poin Loyalitas Pelanggan (Interactive Timeline) -->
    <div x-show="showPointsModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4 transition-all"
         @keydown.escape.window="showPointsModal = false">
        
        <div class="w-full max-w-lg p-6 sm:p-7 rounded-3xl bg-slate-900 border border-slate-700 shadow-2xl space-y-5 relative"
             @click.away="showPointsModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="coins" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Riwayat Mutasi Poin</h3>
                        <span class="text-[10px] text-slate-400 font-mono" x-text="selectedCustomer ? selectedCustomer.name : ''"></span>
                    </div>
                </div>
                <button type="button" @click="showPointsModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Total Point Balance Card -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-mono uppercase text-slate-400 font-bold block">Saldo Poin Saat Ini</span>
                    <div class="text-xl font-black font-mono text-emerald-400 mt-0.5" 
                         x-text="(selectedCustomer ? Number(selectedCustomer.points_balance).toLocaleString('id-ID') : '0') + ' Poin'"></div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-500 font-mono block">Nilai Diskon</span>
                    <div class="text-xs font-bold text-slate-200" 
                         x-text="'Rp ' + (selectedCustomer ? (Number(selectedCustomer.points_balance) * 100).toLocaleString('id-ID') : '0')"></div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div x-show="loadingPoints" class="py-12 text-center space-y-2">
                <i data-lucide="loader-2" class="w-8 h-8 text-emerald-400 animate-spin mx-auto"></i>
                <p class="text-xs text-slate-400">Memuat riwayat transaksi poin...</p>
            </div>

            <!-- Points Timeline List -->
            <div x-show="!loadingPoints" class="space-y-3 max-h-72 overflow-y-auto pr-1">
                <template x-for="h in pointHistories" :key="h.id">
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800/80 flex items-start justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-200" x-text="h.notes || h.description || (h.type === 'pos_earn' || h.type === 'earned' ? 'Perolehan Poin Belanja' : (h.type === 'pos_redeem' ? 'Penukaran Diskon Poin' : 'Mutasi Poin Member'))"></div>
                            <div class="text-[10px] font-mono text-slate-500" x-text="new Date(h.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></div>
                        </div>
                        <div class="text-right font-mono font-black" :class="Number(h.points_change) >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                            <span x-text="(Number(h.points_change) >= 0 ? '+' : '') + Number(h.points_change).toLocaleString('id-ID') + ' Poin'"></span>
                        </div>
                    </div>
                </template>

                <div x-show="pointHistories.length === 0" class="py-8 text-center text-xs text-slate-500 italic">
                    Belum ada riwayat mutasi poin untuk member ini.
                </div>
            </div>

            <div class="pt-2 border-t border-slate-800 flex justify-end">
                <button type="button" @click="showPointsModal = false" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
