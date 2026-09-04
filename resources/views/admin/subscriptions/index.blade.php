@extends('layouts.admin', [
    'title' => 'Langganan & Pembayaran — Admin Console',
    'headerTitle' => 'Kelola Langganan & Pembayaran',
    'headerSubtitle' => 'Verifikasi bukti transfer, persetujuan aktivasi paket Cooca UMKM, dan monitoring omzet SaaS'
])

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Awaiting Verification -->
        <div class="glass-card p-6 rounded-2xl border-amber-500/30 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perlu Verifikasi</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <i data-lucide="hourglass" class="w-4 h-4 {{ $pendingCount > 0 ? 'animate-spin' : '' }}"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">{{ number_format($pendingCount, 0, ',', '.') }}</div>
            <p class="text-[11px] text-amber-300/80 mt-1">Bukti transfer menunggu persetujuan</p>
        </div>

        <!-- Approved Subscriptions -->
        <div class="glass-card p-6 rounded-2xl border-emerald-500/20 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Disetujui</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">{{ number_format($approvedCount, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Transaksi langganan aktif berhasil diverifikasi</p>
        </div>

        <!-- Total SaaS Revenue -->
        <div class="glass-card p-6 rounded-2xl border-indigo-500/20 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Omzet Langganan</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Akumulasi pembayaran yang disetujui</p>
        </div>
    </div>

    <!-- Filter Tabs & Search Bar -->
    <div class="glass-card rounded-2xl p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-bold">
            <a href="{{ route('admin.subscriptions.index', ['status' => 'all']) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $status === 'all' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                Semua
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'awaiting_approval']) }}"
               class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $status === 'awaiting_approval' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-amber-400 hover:bg-slate-800/60' }}">
                <span>Perlu Verifikasi</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'awaiting_approval' ? 'bg-slate-950 text-amber-400' : 'bg-amber-500/20 text-amber-300' }}">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'approved']) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-emerald-400 hover:bg-slate-800/60' }}">
                Disetujui
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'rejected']) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-rose-400 hover:bg-slate-800/60' }}">
                Ditolak
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'pending']) }}"
               class="px-3.5 py-2 rounded-xl transition {{ $status === 'pending' ? 'bg-slate-700 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                Belum Bayar
            </a>
        </div>

        <!-- Search input -->
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="relative min-w-[260px]">
            <input type="hidden" name="status" value="{{ $status }}">
            <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari no pesanan, bisnis, user..."
                   class="w-full bg-slate-950/80 border border-slate-700/80 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
        </form>
    </div>

    <!-- Subscriptions Table Card -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 font-mono uppercase text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5">No. Pesanan</th>
                        <th class="py-3.5 px-4">Nama Bisnis & User</th>
                        <th class="py-3.5 px-4">Paket & Periode</th>
                        <th class="py-3.5 px-4">Total Tagihan</th>
                        <th class="py-3.5 px-4">Metode Bayar</th>
                        <th class="py-3.5 px-4">Bukti Transfer</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($payments as $p)
                    @php
                        $badge = $p->getStatusBadge();
                        $method = $p->getPaymentMethodDetails();
                    @endphp
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-4 px-5">
                            <span class="font-mono font-bold text-white">{{ $p->order_number }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-white">{{ $p->business->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $p->user->name ?? '-' }} ({{ $p->user->email ?? '-' }})</div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-indigo-300">{{ $p->cycle === 'annual' ? 'Core Tahunan' : 'Core Bulanan' }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $p->plan_code }}</div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-mono font-black text-sm text-emerald-400">
                                Rp {{ number_format($p->total_payable, 0, ',', '.') }}
                            </div>
                            @if($p->unique_code > 0)
                                <div class="text-[10px] text-amber-400 font-mono">Kode unik: {{ $p->unique_code }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-semibold text-slate-200">{{ $method['name'] }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $method['bank_name'] }}</div>
                        </td>
                        <td class="py-4 px-4">
                            @if($p->payment_proof_path)
                                <a href="{{ $p->getProofUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 hover:bg-cyan-500/20 transition text-[11px] font-bold">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                    <span>Lihat Struk</span>
                                </a>
                            @else
                                <span class="text-slate-500 text-[11px] italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border inline-flex items-center gap-1 {{ $badge['class'] }}">
                                <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3"></i>
                                <span>{{ $badge['label'] }}</span>
                            </span>
                        </td>
                        <td class="py-4 px-4 font-mono text-slate-400 text-[11px]">
                            {{ $p->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('admin.subscriptions.show', $p) }}"
                               class="px-3 py-1.5 rounded-lg {{ $p->isAwaitingApproval() ? 'bg-indigo-600 hover:bg-indigo-500 text-white font-black shadow-md shadow-indigo-500/20' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold' }} text-xs transition inline-flex items-center gap-1">
                                <span>{{ $p->isAwaitingApproval() ? 'Verifikasi' : 'Detail' }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-slate-500 text-xs">
                            Tidak ada transaksi langganan pada filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
