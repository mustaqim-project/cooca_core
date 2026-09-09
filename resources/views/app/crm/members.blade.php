@extends('layouts.app', [
    'title' => 'CRM & Membership Pelanggan — Cooca UMKM',
    'headerTitle' => 'CRM & Loyalitas Pelanggan',
    'headerSubtitle' => 'Kelola database pelanggan setia, tier membership, perolehan poin belanja, dan pelunasan piutang tempo'
])

@section('content')
<script>
    window.COOCA_MEMBERS = @json($customers->items());
</script>

<div class="space-y-6 pb-12" x-data="{
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

    <!-- 0. Standard Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <i data-lucide="heart-handshake" class="w-3.5 h-3.5"></i>
            <span>CRM &amp; Loyalitas</span>
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Membership &amp; Poin Pelanggan</span>
        </span>
    </nav>

    <!-- 1. Top Header Banner (Seukuran Dashboard Penuh) -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="heart-handshake" class="w-3.5 h-3.5"></i>
                    <span>CRM &amp; Loyalty Program</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 font-mono">
                    Total: {{ number_format($totalMembers, 0, ',', '.') }} Pelanggan
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                CRM &amp; Membership Pelanggan
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Pantau riwayat perolehan poin belanja member, klasifikasi segmen pelanggan VIP, serta kelola batas tempo dan pelunasan piutang bisnis Anda.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('crm.vouchers.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="ticket" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Kelola Voucher</span>
            </a>
            <a href="{{ route('customers.index') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pelanggan</span>
            </a>
        </div>
    </div>

    <!-- Flash Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- 2. 4 Command Pillars KPI Cards (Grid Penuh 4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Pillar 1: Total Members -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Total Pelanggan</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                    {{ number_format($totalMembers, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Database CRM</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">Member Aktif</span>
            </div>
        </div>

        <!-- Pillar 2: Loyalty Points Issued -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Poin Loyalitas</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200/80 dark:border-teal-800/80 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="coins" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight truncate">
                    {{ number_format($totalPointsIssued, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Konversi</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">1 Poin = Rp 100</span>
            </div>
        </div>

        <!-- Pillar 3: Total Credit Receivable -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Piutang Belanja</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl lg:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono tracking-tight truncate">
                    Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Tempo Piutang</span>
                <span class="font-bold text-amber-600 dark:text-amber-400">Belum Lunas</span>
            </div>
        </div>

        <!-- Pillar 4: Tier Membership -->
        <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Tier Membership</span>
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/80 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                    4 Tingkatan Tier
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Tingkatan</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">Bronze – Platinum</span>
            </div>
        </div>
    </div>

    <!-- 3. Toolbar Filter & Search Container -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-4 sm:p-5">
        <form method="GET" action="{{ route('crm.members.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-center">
            <!-- Search Query -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama pelanggan, nomor telepon, atau email..." 
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden transition">
            </div>

            <!-- Tier Filter -->
            <div class="sm:col-span-3">
                <select name="tier" 
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-hidden transition">
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
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-hidden transition">
                    <option value="">Semua Segmen</option>
                    <option value="regular" {{ request('segment') === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="vip" {{ request('segment') === 'vip' ? 'selected' : '' }}>VIP</option>
                    <option value="wholesale" {{ request('segment') === 'wholesale' ? 'selected' : '' }}>Wholesale / Grosir</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-sm shadow-emerald-600/20 flex items-center justify-center gap-1.5 cursor-pointer active:scale-[0.98]">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->hasAny(['search', 'tier', 'segment']))
                <a href="{{ route('crm.members.index') }}" 
                   class="px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-semibold transition border border-slate-200 dark:border-slate-700"
                   title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. High-Density Data Table Container -->
    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden">

        <!-- Table Header Bar -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="contact" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Daftar Pelanggan CRM &amp; Poin Loyalitas</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Urutan berdasarkan akumulasi nilai transaksi belanja tertinggi.</p>
            </div>
            <span class="text-xs font-mono text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800">
                Menampilkan {{ $customers->count() }} dari {{ $customers->total() }} pelanggan
            </span>
        </div>

        @if($customers->count() > 0)

            <!-- Desktop View: Dense Data Table (Hidden on Mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 font-mono uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 tracking-wider whitespace-nowrap">
                        <tr>
                            <th scope="col" class="py-3.5 px-5">Nama Pelanggan</th>
                            <th scope="col" class="py-3.5 px-4">Kontak</th>
                            <th scope="col" class="py-3.5 px-4 text-center">Tier &amp; Segmen</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Saldo Poin</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Akumulasi Belanja</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Saldo Piutang</th>
                            <th scope="col" class="py-3.5 px-5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @foreach($customers as $c)
                        @php
                            $tier = strtolower($c->membership_tier ?? 'bronze');
                            $tierBadge = match($tier) {
                                'platinum' => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-400 border-cyan-200/90 dark:border-cyan-800/90',
                                'gold' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90',
                                'silver' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                default => 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400 border-orange-200/90 dark:border-orange-800/90',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                            <!-- Name & Avatar -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($c->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $c->name }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $c->company_name ?: 'Personal Member' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-slate-800 dark:text-slate-200">{{ $c->phone ?: '-' }}</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ $c->email ?: '-' }}</div>
                            </td>

                            <!-- Tier & Segment -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $tierBadge }}">
                                        {{ $c->membership_tier ?: 'Bronze' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        {{ $c->segment ?: 'Regular' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Loyalty Points with History Shortcut -->
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" 
                                        @click="openPointHistory('{{ $c->id }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-500/25 transition text-emerald-700 dark:text-emerald-400 font-mono font-bold text-xs group cursor-pointer"
                                        title="Klik untuk melihat riwayat mutasi poin">
                                    <i data-lucide="coins" class="w-3.5 h-3.5 group-hover:rotate-12 transition-transform"></i>
                                    <span>{{ number_format($c->points_balance, 0, ',', '.') }}</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-60"></i>
                                </button>
                            </td>

                            <!-- Total Spent & Transaction Count -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-mono font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($c->total_spent, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $c->total_orders_count ?? ($c->pos_orders_count ?? 0) }} Pesanan
                                </div>
                            </td>

                            <!-- Credit Balance (Piutang) -->
                            <td class="py-3.5 px-4 text-right">
                                @if($c->current_credit_balance > 0)
                                    <div class="font-mono font-black text-amber-600 dark:text-amber-400">
                                        Rp {{ number_format($c->current_credit_balance, 0, ',', '.') }}
                                    </div>
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Tempo Aktif
                                    </span>
                                @else
                                    <span class="font-mono text-xs text-slate-400 dark:text-slate-500">Rp 0</span>
                                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Lunas</div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($c->current_credit_balance > 0)
                                        <button type="button" 
                                                @click="openCreditPayment('{{ $c->id }}')" 
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition flex items-center gap-1 shadow-sm shadow-amber-500/20 cursor-pointer">
                                            <i data-lucide="hand-coins" class="w-3.5 h-3.5"></i>
                                            <span>Pelunasan</span>
                                        </button>
                                    @else
                                        <button type="button" 
                                                @click="openPointHistory('{{ $c->id }}')" 
                                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold transition cursor-pointer"
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

            <!-- Mobile View: Clean Card Stream -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($customers as $c)
                @php
                    $tier = strtolower($c->membership_tier ?? 'bronze');
                    $tierBadge = match($tier) {
                        'platinum' => 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-400 border-cyan-200/90 dark:border-cyan-800/90',
                        'gold' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90',
                        'silver' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                        default => 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400 border-orange-200/90 dark:border-orange-800/90',
                    };
                @endphp
                <div class="p-5 space-y-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                    <!-- Card Top: Customer Name & Tier Badges -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                {{ strtoupper(substr($c->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $c->name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $c->phone ?: ($c->email ?: 'Personal') }}</div>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $tierBadge }}">
                                {{ $c->membership_tier ?: 'Bronze' }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 uppercase">{{ $c->segment ?: 'Regular' }}</span>
                        </div>
                    </div>

                    <!-- Card Body: 3-Pillar Micro Stats -->
                    <div class="grid grid-cols-3 gap-2 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase font-mono font-bold">Poin</span>
                            <button type="button" 
                                    @click="openPointHistory('{{ $c->id }}')"
                                    class="text-xs font-mono font-black text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer">
                                {{ number_format($c->points_balance, 0, ',', '.') }}
                            </button>
                        </div>
                        <div class="space-y-0.5 border-x border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase font-mono font-bold">Total Belanja</span>
                            <div class="text-xs font-mono font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($c->total_spent, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block uppercase font-mono font-bold">Piutang</span>
                            <div class="text-xs font-mono font-black {{ $c->current_credit_balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">
                                Rp {{ number_format($c->current_credit_balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Actions -->
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <button type="button" 
                                @click="openPointHistory('{{ $c->id }}')"
                                class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition font-semibold cursor-pointer">
                            <i data-lucide="history" class="w-3.5 h-3.5"></i>
                            <span>Riwayat Poin</span>
                        </button>

                        @if($c->current_credit_balance > 0)
                            <button type="button" 
                                    @click="openCreditPayment('{{ $c->id }}')"
                                    class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-amber-500/20 cursor-pointer">
                                <i data-lucide="hand-coins" class="w-4 h-4"></i>
                                <span>Pelunasan Piutang</span>
                            </button>
                        @else
                            <span class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
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
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="user-x" class="w-8 h-8"></i>
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Tidak Ada Data Pelanggan Ditemukan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Coba ubah kriteria pencarian atau tambahkan pelanggan baru untuk mulai mencatat poin loyalitas kasir.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah Pelanggan Sekarang</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
        @endif

    </div>

    <!-- Modal 1: Catat Pelunasan Piutang (Credit Repayment) -->
    <div x-show="showCreditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all"
         @keydown.escape.window="showCreditModal = false">
        
        <div class="w-full max-w-md p-6 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 relative"
             @click.away="showCreditModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i data-lucide="hand-coins" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Catat Pelunasan Piutang</h3>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Transaksi Tempo Pelanggan</span>
                    </div>
                </div>
                <button type="button" @click="showCreditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/crm/customers') }}/' + (selectedCustomer ? selectedCustomer.id : '') + '/credit-payment'" 
                  method="POST" 
                  class="space-y-4 text-xs">
                @csrf

                <!-- Customer Info Banner -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedCustomer ? selectedCustomer.name : ''"></div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Total Piutang Berjalan:</div>
                    </div>
                    <span class="font-bold font-mono text-lg text-amber-600 dark:text-amber-400" 
                          x-text="'Rp ' + Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0).toLocaleString('id-ID')"></span>
                </div>

                <!-- Fast Nominal Quick-fill Chips -->
                <div class="space-y-1.5">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">Pilih Cepat Nominal Pelunasan:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" 
                                @click="setQuickAmount(Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0))" 
                                class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-700 dark:text-amber-300 font-bold text-xs border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                            Lunasi Penuh
                        </button>
                        <button type="button" 
                                @click="setQuickAmount(Math.round(Number(selectedCustomer ? selectedCustomer.current_credit_balance : 0) / 2))" 
                                class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                            50% Piutang
                        </button>
                        <button type="button" 
                                @click="setQuickAmount(50000)" 
                                class="py-2 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                            Rp 50.000
                        </button>
                    </div>
                </div>

                <!-- Input Nominal -->
                <div class="space-y-1.5">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                        Nominal Pembayaran (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm">Rp</span>
                        <input type="number" 
                               name="amount" 
                               required
                               min="1"
                               x-model.number="paymentAmount" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl pl-10 pr-4 py-2.5 text-base font-bold font-mono text-slate-900 dark:text-white focus:outline-hidden transition">
                    </div>
                </div>

                <!-- Input Notes -->
                <div class="space-y-1.5">
                    <label class="block text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px] tracking-wider">
                        Catatan / Nomor Bukti Transfer <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                    </label>
                    <input type="text" 
                           name="notes" 
                           x-model="paymentNotes"
                           placeholder="Misal: Transfer Bank BCA / Tunai di Kasir..." 
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-hidden transition">
                </div>

                <!-- Modal Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="showCreditModal = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition flex items-center gap-1.5 cursor-pointer">
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
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all"
         @keydown.escape.window="showPointsModal = false">
        
        <div class="w-full max-w-lg p-6 sm:p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl space-y-5 relative"
             @click.away="showPointsModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="coins" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Riwayat Mutasi Poin</h3>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" x-text="selectedCustomer ? selectedCustomer.name : ''"></span>
                    </div>
                </div>
                <button type="button" @click="showPointsModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Total Point Balance Card -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-mono uppercase text-slate-500 dark:text-slate-400 font-bold block">Saldo Poin Saat Ini</span>
                    <div class="text-xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-0.5" 
                         x-text="(selectedCustomer ? Number(selectedCustomer.points_balance).toLocaleString('id-ID') : '0') + ' Poin'"></div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono block">Nilai Diskon</span>
                    <div class="text-xs font-bold text-slate-900 dark:text-slate-200" 
                         x-text="'Rp ' + (selectedCustomer ? (Number(selectedCustomer.points_balance) * 100).toLocaleString('id-ID') : '0')"></div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div x-show="loadingPoints" class="py-12 text-center space-y-2">
                <i data-lucide="loader-2" class="w-8 h-8 text-emerald-600 dark:text-emerald-400 animate-spin mx-auto"></i>
                <p class="text-xs text-slate-500 dark:text-slate-400">Memuat riwayat transaksi poin...</p>
            </div>

            <!-- Points Timeline List -->
            <div x-show="!loadingPoints" class="space-y-3 max-h-72 overflow-y-auto pr-1">
                <template x-for="h in pointHistories" :key="h.id">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-start justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-900 dark:text-slate-200" x-text="h.notes || h.description || (h.type === 'pos_earn' || h.type === 'earned' ? 'Perolehan Poin Belanja' : (h.type === 'pos_redeem' ? 'Penukaran Diskon Poin' : 'Mutasi Poin Member'))"></div>
                            <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500" x-text="new Date(h.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></div>
                        </div>
                        <div class="text-right font-mono font-black" :class="Number(h.points_change) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            <span x-text="(Number(h.points_change) >= 0 ? '+' : '') + Number(h.points_change).toLocaleString('id-ID') + ' Poin'"></span>
                        </div>
                    </div>
                </template>

                <div x-show="pointHistories.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-slate-500 italic">
                    Belum ada riwayat mutasi poin untuk member ini.
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" @click="showPointsModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
