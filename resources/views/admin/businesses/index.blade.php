@extends('layouts.admin', [
    'title' => 'Kelola Bisnis (Tenants) — Admin Console',
    'headerTitle' => 'Manajemen Tenant Bisnis UMKM',
    'headerSubtitle' => 'Kelola seluruh workspace bisnis yang beroperasi di platform Cooca UMKM'
])

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Workspace</span>
                <i data-lucide="building-2" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ number_format($totalBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Tenant terdaftar di sistem</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Workspace Aktif</span>
                <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($activeBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Dapat bertransaksi normal</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Langganan Core</span>
                <i data-lucide="shield-check" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">{{ number_format($coreBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Pelanggan berbayar aktif</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Ditangguhkan</span>
                <i data-lucide="ban" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">{{ number_format($suspendedBusinesses, 0, ',', '.') }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Akses dibekukan sementara</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4">
        <form method="GET" action="{{ route('admin.businesses.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bisnis, slug, email, telepon..." class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select name="plan" onchange="this.form.submit()" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <option value="">Semua Paket</option>
                    <option value="core" {{ request('plan') === 'core' ? 'selected' : '' }}>Cooca UMKM</option>
                    <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free Plan</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>
                @if(request()->hasAny(['search', 'plan', 'status']))
                <a href="{{ route('admin.businesses.index') }}" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors inline-flex items-center gap-1"><i data-lucide="x" class="w-3.5 h-3.5" stroke-width="1.5"></i>Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Businesses Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Bisnis &amp; Identitas</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Paket Langganan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Pengguna / Tim</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Katalog &amp; Resep</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($businesses as $biz)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">
                                <a href="{{ route('admin.businesses.show', $biz->id) }}" class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">{{ $biz->name }}</a>
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">slug: {{ $biz->slug }} · {{ $biz->currency ?? 'IDR' }}</div>
                            @if($biz->email || $biz->phone)
                            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $biz->email ?? $biz->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($biz->subscription?->isCorePlan())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><i data-lucide="shield-check" class="w-3 h-3" stroke-width="1.5"></i>CORE ({{ strtoupper($biz->subscription->plan_code) }})</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i data-lucide="gift" class="w-3 h-3" stroke-width="1.5"></i>Free Plan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-black/70 dark:text-white/70">
                            <div class="font-medium">{{ $biz->users->count() }} Pengguna</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ $biz->users->count() <= 1 ? 'Solo Owner' : 'Mode Tim' }}</div>
                        </td>
                        <td class="px-4 py-3 text-black/70 dark:text-white/70">
                            <div class="tabular-nums">{{ $biz->products_count }} Produk</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $biz->bom_headers_count }} Resep BOM</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($biz->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>Aktif</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>Ditangguhkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.businesses.show', $biz->id) }}" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/8 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1">Detail</a>
                                <form method="POST" action="{{ route('admin.businesses.toggle-status', $biz->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin mengubah status aktif bisnis {{ addslashes($biz->name) }}?', 'Ubah Status Bisnis?', 'warning')">
                                    @csrf
                                    <button type="submit" class="h-7 px-2 rounded-[6px] text-[12px] font-semibold transition-all inline-flex items-center gap-1 {{ $biz->is_active ? 'text-[#FF3B30] hover:bg-[#FF3B30]/8' : 'text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/8' }}">{{ $biz->is_active ? 'Suspend' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center"><p class="text-[15px] font-semibold text-black dark:text-white">Tidak ada data bisnis</p><p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Tidak ada data bisnis yang sesuai dengan filter.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($businesses->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $businesses->links() }}</div>
        @endif
    </div>
</div>
@endsection