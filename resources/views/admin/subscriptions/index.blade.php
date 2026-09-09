@extends('layouts.admin', [
    'title' => 'Langganan & Pembayaran — Admin Console',
    'headerTitle' => 'Kelola Langganan & Pembayaran',
    'headerSubtitle' => 'Verifikasi bukti transfer, persetujuan aktivasi paket Cooca UMKM, dan monitoring omzet SaaS'
])

@section('content')
<div class="space-y-6" x-data="{
    imageModalOpen: false,
    modalImageUrl: '',
    modalTitle: ''
}">

    <!-- Top View Switcher (Dual View: Transaksi Pembayaran vs Status Langganan Tenant) -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.subscriptions.index', ['view' => 'payments']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $view !== 'tenants' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Transaksi & Bukti Pembayaran</span>
                @if($paymentCounts['awaiting_approval'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950 animate-pulse">
                        {{ $paymentCounts['awaiting_approval'] }}
                    </span>
                @endif
            </a>
            <a href="{{ route('admin.subscriptions.index', ['view' => 'tenants']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $view === 'tenants' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                <span>Status Langganan Tenant Bisnis</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                    {{ $tenantCounts['all'] }}
                </span>
            </a>
        </div>

        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-400"></i>
            <span>Sinkronisasi otomatis real-time</span>
        </div>
    </div>

    @if($view === 'tenants')
        {{-- ========================================================================= --}}
        {{-- VIEW 2: TENANT BUSINESS SUBSCRIPTIONS --}}
        {{-- ========================================================================= --}}

        <!-- KPI Metric Cards Grid for Tenants -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Tenants -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Tenant Bisnis</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">{{ number_format($tenantCounts['all'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Seluruh workspace terdaftar</p>
            </div>

            <!-- Active Core -->
            <div class="glass-card p-5 rounded-2xl border-emerald-500/20 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Core Aktif</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-400 font-mono">{{ number_format($tenantCounts['core_active'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-emerald-300/80 mt-1">Berlangganan Cooca Core</p>
            </div>

            <!-- Free Plan -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paket Gratis (Free)</span>
                    <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400">
                        <i data-lucide="gift" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-300 font-mono">{{ number_format($tenantCounts['free'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Belum upgrade ke Core</p>
            </div>

            <!-- Expiring Soon / Expired -->
            <div class="glass-card p-5 rounded-2xl border-amber-500/20 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Masa Aktif Kritis</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-extrabold text-amber-400 font-mono">{{ $tenantCounts['expiring_soon'] }}</span>
                    <span class="text-xs text-slate-500">habis &le; 7 hr /</span>
                    <span class="text-2xl font-extrabold text-rose-400 font-mono">{{ $tenantCounts['expired'] }}</span>
                    <span class="text-xs text-slate-500">expired</span>
                </div>
                <p class="text-[11px] text-amber-300/80 mt-1">Perlu difollow-up perpanjangan</p>
            </div>
        </div>

        <!-- Tenant Filter Tabs & Search Bar -->
        <div class="glass-card rounded-2xl p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-bold">
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'all', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'all' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'all' ? 'bg-indigo-950 text-indigo-200' : 'bg-slate-800 text-slate-400' }}">{{ $tenantCounts['all'] }}</span>
                </a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'core_active', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'core_active' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-emerald-400 hover:bg-slate-800/60' }}">
                    <span>Core Aktif</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'core_active' ? 'bg-emerald-950 text-emerald-200' : 'bg-emerald-500/20 text-emerald-300' }}">{{ $tenantCounts['core_active'] }}</span>
                </a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'free', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'free' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span>Free Plan</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'free' ? 'bg-slate-900 text-slate-300' : 'bg-slate-800 text-slate-400' }}">{{ $tenantCounts['free'] }}</span>
                </a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'expiring_soon', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'expiring_soon' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-amber-400 hover:bg-slate-800/60' }}">
                    <span>Segera Berakhir (&le; 7 Hari)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'expiring_soon' ? 'bg-slate-950 text-amber-400' : 'bg-amber-500/20 text-amber-300' }}">{{ $tenantCounts['expiring_soon'] }}</span>
                </a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'expired', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'expired' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-rose-400 hover:bg-slate-800/60' }}">
                    <span>Kadaluarsa</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'expired' ? 'bg-rose-950 text-rose-200' : 'bg-rose-500/20 text-rose-300' }}">{{ $tenantCounts['expired'] }}</span>
                </a>
            </div>

            <!-- Search input -->
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="relative min-w-[260px]">
                <input type="hidden" name="view" value="tenants">
                <input type="hidden" name="status" value="{{ $status }}">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama bisnis, owner, email..."
                       class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </form>
        </div>

        <!-- Tenant Subscriptions Table Card -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/80 text-slate-400 font-mono uppercase text-[10px] border-b border-slate-800">
                        <tr>
                            <th class="py-3.5 px-5">Nama Bisnis (Tenant)</th>
                            <th class="py-3.5 px-4">Owner & Kontak</th>
                            <th class="py-3.5 px-4">Paket Langganan</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Masa Aktif & Sisa Hari</th>
                            <th class="py-3.5 px-4">Sisa Token AI</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($tenants as $t)
                        @php
                            $sub = $t->subscription;
                            $owner = $t->users->first();
                            $now = now();
                            $isCore = $sub?->isCorePlan() ?? false;
                            $endsAt = $sub?->ends_at;
                            $daysLeft = $endsAt ? (int) $now->diffInDays($endsAt, false) : null;
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-4 px-5">
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="store" class="w-3.5 h-3.5 text-indigo-400 shrink-0"></i>
                                    <span>{{ $t->name }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">slug: {{ $t->slug }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-200">{{ $owner->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $owner->email ?? $t->email ?? '-' }}</div>
                                @if($t->phone || ($owner && $owner->phone))
                                    <div class="text-[10px] text-emerald-400 font-mono mt-0.5 flex items-center gap-1">
                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                        <span>{{ $t->phone ?? $owner->phone }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($isCore)
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-bold">
                                        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-400"></i>
                                        <span>{{ $sub->plan_code === 'core_annual' ? 'Core Tahunan' : 'Core Bulanan' }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 text-slate-400 border border-slate-700 text-xs font-medium">
                                        <i data-lucide="gift" class="w-3.5 h-3.5"></i>
                                        <span>Cooca Free</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($isCore)
                                    @if($daysLeft !== null && $daysLeft < 0)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                                            <span>Expired</span>
                                        </span>
                                    @elseif($daysLeft !== null && $daysLeft <= 7)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30 inline-flex items-center gap-1 animate-pulse">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                            <span>Segera Habis</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 inline-flex items-center gap-1">
                                            <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                            <span>Aktif</span>
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700 inline-flex items-center gap-1">
                                        <i data-lucide="info" class="w-3 h-3"></i>
                                        <span>Gratis Standar</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($isCore && $endsAt)
                                    <div class="font-bold text-slate-200 font-mono text-[11px]">
                                        {{ $endsAt->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] {{ $daysLeft <= 7 ? 'text-amber-400 font-bold' : 'text-slate-400' }}">
                                        @if($daysLeft < 0)
                                            <span class="text-rose-400 font-bold">Kadaluarsa {{ abs($daysLeft) }} hari lalu</span>
                                        @elseif($daysLeft === 0)
                                            <span class="text-rose-400 font-bold">Berakhir hari ini!</span>
                                        @else
                                            Sisa <strong class="text-white">{{ $daysLeft }}</strong> hari lagi
                                        @endif
                                    </div>
                                @elseif($isCore)
                                    <span class="text-slate-400 text-xs">Tanpa batas waktu</span>
                                @else
                                    <span class="text-slate-500 text-xs italic">Permanen Free</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($sub && $sub->ai_tokens_remaining !== null)
                                    <div class="font-mono font-bold text-purple-400">
                                        {{ number_format($sub->ai_tokens_remaining, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">Token AI tersisa</div>
                                @else
                                    <span class="text-slate-500 text-xs">0 Token</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <a href="{{ route('admin.businesses.show', $t) }}"
                                   class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition inline-flex items-center gap-1">
                                    <span>Kelola Bisnis</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-500 text-xs">
                                Tidak ada tenant bisnis pada filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tenants->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $tenants->links() }}
            </div>
            @endif
        </div>

    @else
        {{-- ========================================================================= --}}
        {{-- VIEW 1: PAYMENT ORDERS & TRANSFER PROOFS (DEFAULT) --}}
        {{-- ========================================================================= --}}

        <!-- KPI Metric Cards Grid for Payments -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Orders -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pesanan Masuk</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">{{ number_format($paymentCounts['all'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Semua tagihan & transaksi terbuat</p>
            </div>

            <!-- Awaiting Verification -->
            <div class="glass-card p-5 rounded-2xl border-amber-500/30 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perlu Verifikasi</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                        <i data-lucide="hourglass" class="w-4 h-4 {{ $pendingCount > 0 ? 'animate-spin' : '' }}"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-amber-400 font-mono">{{ number_format($pendingCount, 0, ',', '.') }}</div>
                <p class="text-[11px] text-amber-300/80 mt-1">Bukti transfer menunggu persetujuan admin</p>
            </div>

            <!-- Approved Subscriptions -->
            <div class="glass-card p-5 rounded-2xl border-emerald-500/20 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Disetujui</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-400 font-mono">{{ number_format($approvedCount, 0, ',', '.') }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Transaksi langganan aktif berhasil diverifikasi</p>
            </div>

            <!-- Total SaaS Revenue -->
            <div class="glass-card p-5 rounded-2xl border-indigo-500/20 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Omzet SaaS</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Akumulasi pembayaran yang disetujui</p>
            </div>
        </div>

        <!-- Filter Toolbar (Status Tabs, Type Filter, Search Bar) -->
        <div class="glass-card rounded-2xl p-4 space-y-4">
            <!-- Row 1: Status Tabs with Accurate Dynamic Badges -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-bold scrollbar-thin">
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $status === 'all' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span>Semua Status</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'all' ? 'bg-indigo-950 text-indigo-200' : 'bg-slate-800 text-slate-400' }}">
                        {{ $paymentCounts['all'] }}
                    </span>
                </a>

                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'awaiting_approval', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $status === 'awaiting_approval' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-amber-400 hover:bg-slate-800/60' }}">
                    <span>Perlu Verifikasi</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'awaiting_approval' ? 'bg-slate-950 text-amber-400' : ($paymentCounts['awaiting_approval'] > 0 ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-400') }}">
                        {{ $paymentCounts['awaiting_approval'] }}
                    </span>
                </a>

                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'approved', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-emerald-400 hover:bg-slate-800/60' }}">
                    <span>Disetujui</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'approved' ? 'bg-emerald-950 text-emerald-200' : 'bg-emerald-500/20 text-emerald-300' }}">
                        {{ $paymentCounts['approved'] }}
                    </span>
                </a>

                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'pending', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $status === 'pending' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span>Belum Bayar (Pending)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-slate-900 text-slate-300' : 'bg-slate-800 text-slate-400' }}">
                        {{ $paymentCounts['pending'] }}
                    </span>
                </a>

                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'rejected', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-rose-400 hover:bg-slate-800/60' }}">
                    <span>Ditolak</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'rejected' ? 'bg-rose-950 text-rose-200' : 'bg-rose-500/20 text-rose-300' }}">
                        {{ $paymentCounts['rejected'] }}
                    </span>
                </a>

                @if($paymentCounts['cancelled'] > 0)
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'cancelled', 'page' => 1])) }}"
                   class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $status === 'cancelled' ? 'bg-slate-800 text-slate-200 shadow' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800/60' }}">
                    <span>Dibatalkan</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-900 text-slate-400">
                        {{ $paymentCounts['cancelled'] }}
                    </span>
                </a>
                @endif
            </div>

            <!-- Row 2: Type Filter, Method Filter, Date Filter, and Search Form -->
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-800/60">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="view" value="payments">

                <div class="flex flex-wrap items-center gap-2.5 text-xs">
                    <!-- Type Filter -->
                    <div class="flex items-center gap-1.5 bg-slate-950/80 border border-slate-800 rounded-xl px-2.5 py-1.5">
                        <span class="text-slate-400 text-[11px] font-semibold">Tipe:</span>
                        <select name="type" onchange="this.form.submit()" class="bg-transparent text-slate-200 font-bold focus:outline-none cursor-pointer">
                            <option value="all" class="bg-slate-900 text-white" {{ $type === 'all' ? 'selected' : '' }}>Semua Tipe ({{ $typeCounts['all'] }})</option>
                            <option value="subscription" class="bg-slate-900 text-white" {{ $type === 'subscription' ? 'selected' : '' }}>Langganan Cooca ({{ $typeCounts['subscription'] }})</option>
                            <option value="ai_token" class="bg-slate-900 text-white" {{ $type === 'ai_token' ? 'selected' : '' }}>Topup Token AI ({{ $typeCounts['ai_token'] }})</option>
                            <option value="storage" class="bg-slate-900 text-white" {{ $type === 'storage' ? 'selected' : '' }}>Topup Storage ({{ $typeCounts['storage'] }})</option>
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div class="flex items-center gap-1.5 bg-slate-950/80 border border-slate-800 rounded-xl px-2.5 py-1.5">
                        <span class="text-slate-400 text-[11px] font-semibold">Metode:</span>
                        <select name="method" onchange="this.form.submit()" class="bg-transparent text-slate-200 font-bold focus:outline-none cursor-pointer">
                            <option value="all" class="bg-slate-900 text-white" {{ $method === 'all' ? 'selected' : '' }}>Semua Bank & QRIS</option>
                            <option value="bca" class="bg-slate-900 text-white" {{ $method === 'bca' ? 'selected' : '' }}>Bank BCA</option>
                            <option value="mandiri" class="bg-slate-900 text-white" {{ $method === 'mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                            <option value="bri" class="bg-slate-900 text-white" {{ $method === 'bri' ? 'selected' : '' }}>Bank BRI</option>
                            <option value="qris" class="bg-slate-900 text-white" {{ $method === 'qris' ? 'selected' : '' }}>QRIS Instant</option>
                        </select>
                    </div>

                    @if($search || $type !== 'all' || $method !== 'all' || $status !== 'all')
                    <a href="{{ route('admin.subscriptions.index', ['status' => 'all', 'view' => 'payments']) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-bold transition">
                        <i data-lucide="x" class="w-3.5 h-3.5 text-rose-400"></i>
                        <span>Reset Filter</span>
                    </a>
                    @endif
                </div>

                <!-- Search Input -->
                <div class="relative min-w-[280px] w-full sm:w-auto">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari no pesanan, bisnis, paket, user..."
                           class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>
            </form>
        </div>

        <!-- Subscriptions Table Card -->
        <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/90 text-slate-400 font-mono uppercase text-[10px] border-b border-slate-800">
                        <tr>
                            <th class="py-3.5 px-5">No. Pesanan & Tipe</th>
                            <th class="py-3.5 px-4">Nama Bisnis & Pemesan</th>
                            <th class="py-3.5 px-4">Detail Paket & Kuota</th>
                            <th class="py-3.5 px-4">Total Tagihan</th>
                            <th class="py-3.5 px-4">Metode Bayar</th>
                            <th class="py-3.5 px-4">Bukti Transfer</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Tanggal Order</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($payments as $p)
                        @php
                            $badge = $p->getStatusBadge();
                            $method = $p->getPaymentMethodDetails();

                            // Payment Type styling & label
                            $typeLabel = match($p->payment_type) {
                                'ai_token' => 'Topup Token AI',
                                'storage' => 'Topup Storage',
                                default => 'Langganan SaaS'
                            };
                            $typeClass = match($p->payment_type) {
                                'ai_token' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                                'storage' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                default => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30'
                            };

                            // Accurate Package Name & Details
                            if ($p->payment_type === 'ai_token') {
                                $pkgTitle = $p->package_name ?: 'Topup Token AI';
                                $pkgSubtitle = '+' . number_format($p->topup_quantity ?? 0, 0, ',', '.') . ' Token AI';
                            } elseif ($p->payment_type === 'storage') {
                                $pkgTitle = $p->package_name ?: 'Topup Storage Disk';
                                $pkgSubtitle = '+' . ($p->topup_storage_bytes ? round($p->topup_storage_bytes / 1073741824, 1) . ' GB Storage' : '-');
                            } else {
                                $pkgTitle = $p->package_name ?: ($p->cycle === 'annual' || $p->plan_code === 'core_annual' ? 'Cooca Core Tahunan' : 'Cooca Core Bulanan');
                                $durationText = $p->package_duration_days
                                    ? $p->package_duration_days . ' Hari'
                                    : ($p->cycle === 'annual' || $p->plan_code === 'core_annual' ? '1 Tahun (365 Hari)' : '1 Bulan (30 Hari)');
                                $pkgSubtitle = $durationText . ' • ' . strtoupper($p->plan_code ?: 'core_monthly');
                            }
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition">
                            <!-- Col 1: Order Number & Type Badge -->
                            <td class="py-4 px-5">
                                <div class="font-mono font-bold text-white tracking-wide text-xs">
                                    {{ $p->order_number }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase border {{ $typeClass }}">
                                        @if($p->payment_type === 'ai_token')
                                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                                        @elseif($p->payment_type === 'storage')
                                            <i data-lucide="hard-drive" class="w-3 h-3"></i>
                                        @else
                                            <i data-lucide="layers" class="w-3 h-3"></i>
                                        @endif
                                        <span>{{ $typeLabel }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- Col 2: Business & User -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="store" class="w-3.5 h-3.5 text-indigo-400 shrink-0"></i>
                                    <span>{{ $p->business->name ?? '-' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-300 mt-0.5">
                                    {{ $p->user->name ?? '-' }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono">
                                    {{ $p->user->email ?? '-' }}
                                </div>
                            </td>

                            <!-- Col 3: Accurate Package & Quota -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-indigo-300 text-xs flex items-center gap-1">
                                    @if($p->payment_type === 'ai_token')
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-400 shrink-0"></i>
                                    @elseif($p->payment_type === 'storage')
                                        <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                                    @else
                                        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                                    @endif
                                    <span>{{ $pkgTitle }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $pkgSubtitle }}
                                </div>
                            </td>

                            <!-- Col 4: Total Payable & Unique Code -->
                            <td class="py-4 px-4">
                                <div class="font-mono font-black text-sm text-emerald-400">
                                    Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                                </div>
                                @if($p->unique_code > 0)
                                    <div class="text-[10px] text-amber-400 font-mono font-semibold">
                                        Kode unik: +{{ $p->unique_code }}
                                    </div>
                                @endif
                                @if($p->amount > 0 && $p->amount != $p->total_payable)
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        Pokok: Rp {{ number_format($p->amount, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 5: Payment Method -->
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-200 flex items-center gap-1.5">
                                    <i data-lucide="{{ $method['icon'] ?? 'credit-card' }}" class="w-3.5 h-3.5 text-cyan-400 shrink-0"></i>
                                    <span>{{ $method['name'] }}</span>
                                </div>
                                @if($p->sender_account_name)
                                    <div class="text-[10px] text-emerald-400 mt-0.5 font-medium">
                                        a/n {{ $p->sender_account_name }} ({{ $p->sender_bank ?: 'Bank' }})
                                    </div>
                                @else
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $method['bank_name'] }}</div>
                                @endif
                            </td>

                            <!-- Col 6: Payment Proof (Struk) with Modal Preview -->
                            <td class="py-4 px-4">
                                @if($p->payment_proof_path)
                                    <button type="button"
                                            @click="imageModalOpen = true; modalImageUrl = '{{ $p->getProofUrl() }}'; modalTitle = 'Bukti Transfer #{{ $p->order_number }} - {{ $p->business->name ?? '' }}'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-300 border border-cyan-500/30 hover:bg-cyan-500/20 transition text-[11px] font-bold">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                        <span>Lihat Struk</span>
                                    </button>
                                    <div class="text-[9px] text-slate-500 font-mono mt-0.5">
                                        {{ $p->proof_uploaded_at ? $p->proof_uploaded_at->format('d/m H:i') : 'Diunggah' }}
                                    </div>
                                @else
                                    <span class="text-slate-500 text-[11px] italic flex items-center gap-1">
                                        <i data-lucide="minus" class="w-3 h-3"></i>
                                        <span>Belum ada</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Col 7: Status Badge -->
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border inline-flex items-center gap-1 {{ $badge['class'] }}">
                                    <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3"></i>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>

                            <!-- Col 8: Date -->
                            <td class="py-4 px-4 font-mono text-slate-400 text-[11px]">
                                <div class="text-slate-200 font-semibold">{{ $p->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $p->created_at->format('H:i') }} WIB</div>
                            </td>

                            <!-- Col 9: Action Button -->
                            <td class="py-4 px-5 text-right">
                                <a href="{{ route('admin.subscriptions.show', $p) }}"
                                   class="px-3.5 py-2 rounded-xl {{ $p->isAwaitingApproval() ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 font-black shadow-lg shadow-amber-500/30' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold' }} text-xs transition inline-flex items-center gap-1.5">
                                    <span>{{ $p->isAwaitingApproval() ? 'Verifikasi' : 'Detail' }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-16 text-slate-500 text-xs">
                                <div class="w-12 h-12 rounded-2xl bg-slate-800/80 mx-auto flex items-center justify-center text-slate-400 mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <div class="font-bold text-slate-300 text-sm">Tidak ada data transaksi</div>
                                <p class="text-slate-500 mt-1 max-w-sm mx-auto">
                                    Tidak ditemukan data transaksi yang sesuai dengan filter status <strong class="text-slate-300">{{ $status }}</strong>
                                    @if($type !== 'all') dan tipe <strong class="text-slate-300">{{ $type }}</strong> @endif.
                                </p>
                                @if($search || $status !== 'all' || $type !== 'all' || $method !== 'all')
                                    <div class="mt-4">
                                        <a href="{{ route('admin.subscriptions.index', ['status' => 'all', 'view' => 'payments']) }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs inline-flex items-center gap-1.5 hover:bg-indigo-500 transition">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                            <span>Reset Semua Filter</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/40">
                {{ $payments->links() }}
            </div>
            @endif
        </div>
    @endif

    <!-- Image Preview Modal (Alpine.js) -->
    <div x-show="imageModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95">

        <div class="glass-card max-w-2xl w-full rounded-2xl overflow-hidden border border-slate-700 shadow-2xl p-4 space-y-4"
             @click.away="imageModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="image" class="w-4 h-4 text-cyan-400"></i>
                    <span class="font-bold text-white text-xs" x-text="modalTitle"></span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="modalImageUrl" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                        <span>Buka Tab Baru</span>
                    </a>
                    <button @click="imageModalOpen = false" class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="max-h-[70vh] overflow-auto flex items-center justify-center bg-slate-950/80 rounded-xl p-2 border border-slate-800">
                <img :src="modalImageUrl" alt="Struk Transfer" class="max-h-[65vh] object-contain rounded-lg">
            </div>
        </div>
    </div>

</div>
@endsection
