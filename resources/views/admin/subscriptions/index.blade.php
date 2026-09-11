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

    <!-- Top View Switcher (Segmented Control) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
            <a href="{{ route('admin.subscriptions.index', ['view' => 'payments']) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $view !== 'tenants' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80' }} transition-all">
                <i data-lucide="receipt" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Transaksi &amp; Bukti Pembayaran</span>
                @if($paymentCounts['awaiting_approval'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A]">{{ $paymentCounts['awaiting_approval'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.subscriptions.index', ['view' => 'tenants']) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $view === 'tenants' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55 hover:text-black/80 dark:hover:text-white/80' }} transition-all">
                <i data-lucide="building-2" class="w-4 h-4" stroke-width="1.5"></i>
                <span>Status Langganan Tenant</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">{{ $tenantCounts['all'] }}</span>
            </a>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
            <span>Sinkronisasi otomatis real-time</span>
        </div>
    </div>

    @if($view === 'tenants')
        <!-- KPI Metric Cards Grid for Tenants -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Tenant Bisnis</span>
                    <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ number_format($tenantCounts['all'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Seluruh workspace terdaftar</p>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Core Aktif</span>
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($tenantCounts['core_active'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-[#34C759]/80 dark:text-[#30D158]/80 mt-1">Berlangganan Cooca Core</p>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Paket Gratis (Free)</span>
                    <i data-lucide="gift" class="w-4 h-4 text-black/40 dark:text-white/40" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-black/60 dark:text-white/60">{{ number_format($tenantCounts['free'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Belum upgrade ke Core</p>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Masa Aktif Kritis</span>
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[20px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ $tenantCounts['expiring_soon'] }}</span>
                    <span class="text-[11px] text-black/45 dark:text-white/45">habis &le; 7 hr</span>
                    <span class="text-[20px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">{{ $tenantCounts['expired'] }}</span>
                    <span class="text-[11px] text-black/45 dark:text-white/45">expired</span>
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Perlu difollow-up perpanjangan</p>
            </div>
        </div>

        <!-- Tenant Filter Tabs & Search Bar -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[13px] font-medium">
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'all', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $status === 'all' ? 'bg-[#007AFF] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Semua</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'all' ? 'bg-[#007AFF]/20 text-white' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}">{{ $tenantCounts['all'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'core_active', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $status === 'core_active' ? 'bg-[#007AFF] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Core Aktif</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'core_active' ? 'bg-[#007AFF]/20 text-white' : 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]' }}">{{ $tenantCounts['core_active'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'free', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $status === 'free' ? 'bg-[#007AFF] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Free Plan</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'free' ? 'bg-[#007AFF]/20 text-white' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}">{{ $tenantCounts['free'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'expiring_soon', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $status === 'expiring_soon' ? 'bg-[#FF9500] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Segera Berakhir (&le; 7 Hari)</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'expiring_soon' ? 'bg-[#FF9500]/20 text-white' : 'bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A]' }}">{{ $tenantCounts['expiring_soon'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['view' => 'tenants', 'status' => 'expired', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 {{ $status === 'expired' ? 'bg-[#FF3B30] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Kadaluarsa</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'expired' ? 'bg-[#FF3B30]/20 text-white' : 'bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A]' }}">{{ $tenantCounts['expired'] }}</span></a>
            </div>
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="relative min-w-[260px]">
                <input type="hidden" name="view" value="tenants">
                <input type="hidden" name="status" value="{{ $status }}">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama bisnis, owner, email..." class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </form>
        </div>

        <!-- Tenant Subscriptions Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-5 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Bisnis (Tenant)</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Owner &amp; Kontak</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Paket Langganan</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Status</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Masa Aktif &amp; Sisa Hari</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Sisa Token AI</th>
                            <th class="px-5 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($tenants as $t)
                        @php
                            $sub = $t->subscription;
                            $owner = $t->users->first();
                            $now = now();
                            $isCore = $sub?->isCorePlan() ?? false;
                            $endsAt = $sub?->ends_at;
                            $daysLeft = $endsAt ? (int) $now->diffInDays($endsAt, false) : null;
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-5 py-3">
                                <div class="font-medium text-black dark:text-white flex items-center gap-1.5"><i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF] shrink-0" stroke-width="1.5"></i><span>{{ $t->name }}</span></div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">slug: {{ $t->slug }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-black/80 dark:text-white/80">{{ $owner->name ?? '-' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">{{ $owner->email ?? $t->email ?? '-' }}</div>
                                @if($t->phone || ($owner && $owner->phone))
                                    <div class="text-[11px] text-[#34C759] dark:text-[#30D158] tabular-nums mt-0.5 flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3" stroke-width="1.5"></i><span>{{ $t->phone ?? $owner->phone }}</span></div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($isCore)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><i data-lucide="crown" class="w-3.5 h-3.5 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>{{ $sub->plan_code === 'core_annual' ? 'Core Tahunan' : 'Core Bulanan' }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i data-lucide="gift" class="w-3.5 h-3.5" stroke-width="1.5"></i>Cooca Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($isCore)
                                    @if($daysLeft !== null && $daysLeft < 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><i data-lucide="x-circle" class="w-3 h-3" stroke-width="1.5"></i>Expired</span>
                                    @elseif($daysLeft !== null && $daysLeft <= 7)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]"><i data-lucide="alert-triangle" class="w-3 h-3" stroke-width="1.5"></i>Segera Habis</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><i data-lucide="check-circle-2" class="w-3 h-3" stroke-width="1.5"></i>Aktif</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i data-lucide="info" class="w-3 h-3" stroke-width="1.5"></i>Gratis Standar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($isCore && $endsAt)
                                    <div class="font-semibold text-black/80 dark:text-white/80 tabular-nums text-[12px]">{{ $endsAt->format('d M Y') }}</div>
                                    <div class="text-[11px] {{ $daysLeft <= 7 ? 'text-[#FF9500] dark:text-[#FF9F0A] font-semibold' : 'text-black/45 dark:text-white/45' }}">
                                        @if($daysLeft < 0)
                                            <span class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">Kadaluarsa {{ abs($daysLeft) }} hari lalu</span>
                                        @elseif($daysLeft === 0)
                                            <span class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">Berakhir hari ini!</span>
                                        @else
                                            Sisa <strong class="text-black dark:text-white">{{ $daysLeft }}</strong> hari lagi
                                        @endif
                                    </div>
                                @elseif($isCore)
                                    <span class="text-black/45 dark:text-white/45 text-[13px]">Tanpa batas waktu</span>
                                @else
                                    <span class="text-black/40 dark:text-white/40 text-[13px] italic">Permanen Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($sub && $sub->ai_tokens_remaining !== null)
                                    <div class="font-semibold text-[#AF52DE] dark:text-[#BF5AF2] tabular-nums">{{ number_format($sub->ai_tokens_remaining, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Token AI tersisa</div>
                                @else
                                    <span class="text-black/45 dark:text-white/45 text-[13px]">0 Token</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.businesses.show', $t) }}" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1"><span>Kelola Bisnis</span><i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.5"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-[13px] text-black/45 dark:text-white/45">Tidak ada tenant bisnis pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tenants->hasPages())
            <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $tenants->links() }}</div>
            @endif
        </div>

    @else
        <!-- KPI Metric Cards Grid for Payments -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Pesanan Masuk</span>
                    <i data-lucide="shopping-cart" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ number_format($paymentCounts['all'], 0, ',', '.') }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Semua tagihan &amp; transaksi terbuat</p>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Perlu Verifikasi</span>
                    <i data-lucide="hourglass" class="w-4 h-4 {{ $pendingCount > 0 ? 'animate-spin' : '' }} text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ number_format($pendingCount, 0, ',', '.') }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Bukti transfer menunggu persetujuan admin</p>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Disetujui</span>
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($approvedCount, 0, ',', '.') }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Transaksi langganan aktif berhasil diverifikasi</p>
            </div>
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Omzet SaaS</span>
                    <i data-lucide="banknote" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                </div>
                <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Akumulasi pembayaran yang disetujui</p>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 space-y-4">
            <!-- Row 1: Status Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[13px] font-medium">
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 shrink-0 {{ $status === 'all' ? 'bg-[#007AFF] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Semua Status</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'all' ? 'bg-[#007AFF]/20 text-white' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}">{{ $paymentCounts['all'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'awaiting_approval', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 shrink-0 {{ $status === 'awaiting_approval' ? 'bg-[#FF9500] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Perlu Verifikasi</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $status === 'awaiting_approval' ? 'bg-[#FF9500]/20 text-white' : ($paymentCounts['awaiting_approval'] > 0 ? 'bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A]' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55') }}">{{ $paymentCounts['awaiting_approval'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'approved', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 shrink-0 {{ $status === 'approved' ? 'bg-[#34C759] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Disetujui</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'approved' ? 'bg-[#34C759]/20 text-white' : 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]' }}">{{ $paymentCounts['approved'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'pending', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 shrink-0 {{ $status === 'pending' ? 'bg-black/20 dark:bg-white/20 text-black dark:text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Belum Bayar (Pending)</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'pending' ? 'bg-black/10 dark:bg-white/15 text-black/60 dark:text-white/60' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55' }}">{{ $paymentCounts['pending'] }}</span></a>
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'rejected', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 shrink-0 {{ $status === 'rejected' ? 'bg-[#FF3B30] text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Ditolak</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $status === 'rejected' ? 'bg-[#FF3B30]/20 text-white' : 'bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A]' }}">{{ $paymentCounts['rejected'] }}</span></a>
                @if($paymentCounts['cancelled'] > 0)
                <a href="{{ route('admin.subscriptions.index', array_merge(request()->query(), ['status' => 'cancelled', 'page' => 1])) }}" class="px-3 py-1.5 rounded-[7px] inline-flex items-center gap-1.5 shrink-0 {{ $status === 'cancelled' ? 'bg-black/20 dark:bg-white/20 text-black dark:text-white' : 'text-black/55 dark:text-white/55 hover:bg-black/5 dark:hover:bg-white/10' }} transition-all"><span>Dibatalkan</span><span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">{{ $paymentCounts['cancelled'] }}</span></a>
                @endif
            </div>

            <!-- Row 2: Type/Method/Date Filters & Search -->
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-black/5 dark:border-white/10">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="view" value="payments">
                <div class="flex flex-wrap items-center gap-2.5 text-[13px]">
                    <div class="flex items-center gap-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[8px] px-2.5 py-1.5">
                        <span class="text-black/45 dark:text-white/45 text-[11px] font-semibold">Tipe:</span>
                        <select name="type" onchange="this.form.submit()" class="bg-transparent text-black/90 dark:text-white/90 font-semibold focus:outline-none cursor-pointer text-[13px]">
                            <option value="all" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $type === 'all' ? 'selected' : '' }}>Semua Tipe ({{ $typeCounts['all'] }})</option>
                            <option value="subscription" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $type === 'subscription' ? 'selected' : '' }}>Langganan Cooca ({{ $typeCounts['subscription'] }})</option>
                            <option value="ai_token" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $type === 'ai_token' ? 'selected' : '' }}>Topup Token AI ({{ $typeCounts['ai_token'] }})</option>
                            <option value="storage" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $type === 'storage' ? 'selected' : '' }}>Topup Storage ({{ $typeCounts['storage'] }})</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[8px] px-2.5 py-1.5">
                        <span class="text-black/45 dark:text-white/45 text-[11px] font-semibold">Metode:</span>
                        <select name="method" onchange="this.form.submit()" class="bg-transparent text-black/90 dark:text-white/90 font-semibold focus:outline-none cursor-pointer text-[13px]">
                            <option value="all" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $method === 'all' ? 'selected' : '' }}>Semua Bank &amp; QRIS</option>
                            <option value="bca" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $method === 'bca' ? 'selected' : '' }}>Bank BCA</option>
                            <option value="mandiri" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $method === 'mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                            <option value="bri" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $method === 'bri' ? 'selected' : '' }}>Bank BRI</option>
                            <option value="qris" class="bg-white dark:bg-[#1C1C1E] text-black dark:text-white" {{ $method === 'qris' ? 'selected' : '' }}>QRIS Instant</option>
                        </select>
                    </div>
                    @if($search || $type !== 'all' || $method !== 'all' || $status !== 'all')
                    <a href="{{ route('admin.subscriptions.index', ['status' => 'all', 'view' => 'payments']) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[#FF3B30] dark:text-[#FF453A] text-[11px] font-medium transition"><i data-lucide="x" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Reset Filter</span></a>
                    @endif
                </div>
                <div class="relative min-w-[280px] w-full sm:w-auto">
                    <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari no pesanan, bisnis, paket, user..." class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-5 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">No. Pesanan &amp; Tipe</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Bisnis &amp; Pemesan</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Detail Paket &amp; Kuota</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Total Tagihan</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Metode Bayar</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bukti Transfer</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Status</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Tanggal Order</th>
                            <th class="px-5 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($payments as $p)
                        @php
                            $badge = $p->getStatusBadge();
                            $method = $p->getPaymentMethodDetails();
                            $typeLabel = match($p->payment_type) {
                                'ai_token' => 'Topup Token AI',
                                'storage' => 'Topup Storage',
                                default => 'Langganan SaaS'
                            };
                            $typeClass = match($p->payment_type) {
                                'ai_token' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]',
                                'storage' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                default => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]'
                            };
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
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-5 py-3">
                                <div class="font-mono font-semibold text-black dark:text-white tracking-wide tabular-nums">{{ $p->order_number }}</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $typeClass }}">
                                        @if($p->payment_type === 'ai_token')
                                            <i data-lucide="sparkles" class="w-3 h-3" stroke-width="1.5"></i>
                                        @elseif($p->payment_type === 'storage')
                                            <i data-lucide="hard-drive" class="w-3 h-3" stroke-width="1.5"></i>
                                        @else
                                            <i data-lucide="layers" class="w-3 h-3" stroke-width="1.5"></i>
                                        @endif
                                        <span>{{ $typeLabel }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-black dark:text-white flex items-center gap-1.5"><i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF] shrink-0" stroke-width="1.5"></i><span>{{ $p->business->name ?? '-' }}</span></div>
                                <div class="text-[11px] text-black/60 dark:text-white/60 mt-0.5">{{ $p->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-black/45 dark:text-white/45">{{ $p->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-black/80 dark:text-white/80 text-[13px] flex items-center gap-1">
                                    @if($p->payment_type === 'ai_token')
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#AF52DE] dark:text-[#BF5AF2] shrink-0" stroke-width="1.5"></i>
                                    @elseif($p->payment_type === 'storage')
                                        <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0" stroke-width="1.5"></i>
                                    @else
                                        <i data-lucide="crown" class="w-3.5 h-3.5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0" stroke-width="1.5"></i>
                                    @endif
                                    <span>{{ $pkgTitle }}</span>
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $pkgSubtitle }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums">Rp {{ number_format($p->total_payable, 0, ',', '.') }}</div>
                                @if($p->unique_code > 0)
                                    <div class="text-[10px] text-[#FF9500] dark:text-[#FF9F0A] font-semibold tabular-nums">Kode unik: +{{ $p->unique_code }}</div>
                                @endif
                                @if($p->amount > 0 && $p->amount != $p->total_payable)
                                    <div class="text-[10px] text-black/45 dark:text-white/45 tabular-nums">Pokok: Rp {{ number_format($p->amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-black/80 dark:text-white/80 flex items-center gap-1.5"><i data-lucide="{{ $method['icon'] ?? 'credit-card' }}" class="w-3.5 h-3.5 text-[#30B0C7] dark:text-[#40C8E0] shrink-0" stroke-width="1.5"></i><span>{{ $method['name'] }}</span></div>
                                @if($p->sender_account_name)
                                    <div class="text-[10px] text-[#34C759] dark:text-[#30D158] mt-0.5 font-medium">a/n {{ $p->sender_account_name }} ({{ $p->sender_bank ?: 'Bank' }})</div>
                                @else
                                    <div class="text-[10px] text-black/45 dark:text-white/45">{{ $method['bank_name'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($p->payment_proof_path)
                                    <button type="button" @click="imageModalOpen = true; modalImageUrl = '{{ $p->getProofUrl() }}'; modalTitle = 'Bukti Transfer #{{ $p->order_number }} - {{ $p->business->name ?? '' }}'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0] hover:bg-[#30B0C7]/20 transition text-[11px] font-semibold"><i data-lucide="image" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Lihat Struk</span></button>
                                    <div class="text-[10px] text-black/40 dark:text-white/40 tabular-nums mt-0.5">{{ $p->proof_uploaded_at ? $p->proof_uploaded_at->format('d/m H:i') : 'Diunggah' }}</div>
                                @else
                                    <span class="text-black/45 dark:text-white/45 text-[11px] italic flex items-center gap-1"><i data-lucide="minus" class="w-3 h-3" stroke-width="1.5"></i><span>Belum ada</span></span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-semibold {{ $badge['class'] }}"><i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3" stroke-width="1.5"></i><span>{{ $badge['label'] }}</span></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-black/80 dark:text-white/80 tabular-nums text-[12px]">{{ $p->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-black/45 dark:text-white/45 tabular-nums">{{ $p->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.subscriptions.show', $p) }}" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold {{ $p->isAwaitingApproval() ? 'bg-[#FF9500] hover:bg-[#E68A00] text-white' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.09] dark:hover:bg-white/[0.12]' }} active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><span>{{ $p->isAwaitingApproval() ? 'Verifikasi' : 'Detail' }}</span><i data-lucide="arrow-right" class="w-3.5 h-3.5" stroke-width="1.5"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-16">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto text-black/20 dark:text-white/20" stroke-width="1.5"></i>
                            <div class="text-[15px] font-semibold text-black dark:text-white mt-3">Tidak ada data transaksi</div>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-sm mx-auto">Tidak ditemukan data transaksi yang sesuai dengan filter status <strong class="text-black/80 dark:text-white/80">{{ $status }}</strong>@if($type !== 'all') dan tipe <strong class="text-black/80 dark:text-white/80">{{ $type }}</strong> @endif.</p>
                            @if($search || $status !== 'all' || $type !== 'all' || $method !== 'all')
                            <div class="mt-4">
                                <a href="{{ route('admin.subscriptions.index', ['status' => 'all', 'view' => 'payments']) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1.5"><i data-lucide="refresh-cw" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Reset Semua Filter</span></a>
                            </div>
                            @endif
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payments->hasPages())
            <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $payments->links() }}</div>
            @endif
        </div>
    @endif

    <!-- Image Preview Modal (Sheet) -->
    <div x-show="imageModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="sheet-material max-w-2xl w-full rounded-[20px] overflow-hidden border border-black/5 dark:border-white/10 p-4 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.away="imageModalOpen = false">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2"><i data-lucide="image" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i><span class="text-[13px] font-semibold text-black dark:text-white" x-text="modalTitle"></span></div>
                <div class="flex items-center gap-2">
                    <a :href="modalImageUrl" target="_blank" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition flex items-center gap-1"><i data-lucide="external-link" class="w-3 h-3" stroke-width="1.5"></i><span>Buka Tab Baru</span></a>
                    <button @click="imageModalOpen = false" class="p-1.5 rounded-[6px] bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition"><i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i></button>
                </div>
            </div>
            <div class="max-h-[70vh] overflow-auto flex items-center justify-center bg-black/[0.03] dark:bg-white/[0.05] rounded-[10px] p-2"><img :src="modalImageUrl" alt="Struk Transfer" class="max-h-[65vh] object-contain rounded-[10px]"></div>
        </div>
    </div>
</div>
@endsection