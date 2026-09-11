@extends('layouts.app', [
    'title' => 'Kartu Stok & Mutasi',
    'headerTitle' => 'Kartu Stok & Mutasi',
    'headerSubtitle' => 'Audit trail lengkap seluruh pergerakan barang (penjualan kasir, retur, transfer, opname, dan penyesuaian)'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">
    
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('inventory.stocks') }}" class="hover:text-[#007AFF] transition-colors">Inventori</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Kartu Stok &amp; Mutasi</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Kartu Stok &amp; Riwayat Mutasi</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Audit trail pergerakan kuantitas stok dari kasir POS, opname, dan mutasi</p>
        </div>

        <!-- Quick Navigation -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('inventory.stocks') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Saldo Stok</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. FILTER BAR (macOS Style Toolbar Controls)          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3.5 sm:p-4">
        <form method="GET" action="{{ route('inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 items-center">
            <!-- Product Filter -->
            <div class="sm:col-span-4">
                <select name="product_id" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') === $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Location Filter -->
            <div class="sm:col-span-3">
                <select name="location_id" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Lokasi / Outlet</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Movement Type Filter -->
            <div class="sm:col-span-3">
                <select name="movement_type" class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="pos_sale" {{ request('movement_type') === 'pos_sale' ? 'selected' : '' }}>Penjualan POS</option>
                    <option value="pos_refund" {{ request('movement_type') === 'pos_refund' ? 'selected' : '' }}>Retur POS</option>
                    <option value="transfer_in" {{ request('movement_type') === 'transfer_in' ? 'selected' : '' }}>Transfer Masuk</option>
                    <option value="transfer_out" {{ request('movement_type') === 'transfer_out' ? 'selected' : '' }}>Transfer Keluar</option>
                    <option value="opname" {{ request('movement_type') === 'opname' ? 'selected' : '' }}>Stock Opname</option>
                    <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 h-9 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold active:scale-[0.97] transition-all flex items-center justify-center">
                    Filter
                </button>
                <a href="{{ route('inventory.movements') }}" class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 3. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 whitespace-nowrap">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Waktu</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Lokasi</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tipe Mutasi</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Perubahan Qty</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Saldo Akhir</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Keterangan / Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($movements as $m)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 text-black/50 dark:text-white/50 tabular-nums">
                            {{ $m->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3 px-4 font-medium text-black dark:text-white">
                            {{ $m->product->name ?? 'Produk' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70">
                                {{ $m->location->name ?? 'Outlet' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $isPositive = $m->quantity_change > 0;
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $isPositive ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isPositive ? 'bg-[#34C759]' : 'bg-[#FF3B30]' }}"></span>
                                {{ str_replace('_', ' ', $m->movement_type) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold {{ $m->quantity_change > 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim((string)$m->quantity_change, '0'), '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                            {{ rtrim(rtrim((string)$m->balance_after, '0'), '.') }}
                        </td>
                        <td class="py-3 px-4 text-black/60 dark:text-white/60">
                            <div>{{ $m->notes ?? '-' }}</div>
                            @if($m->reference_number)
                                <div class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">Ref: {{ $m->reference_number }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-black/40 dark:text-white/40">Belum ada riwayat mutasi stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($movements as $m)
        <div class="p-3.5 flex items-start justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $m->product->name ?? 'Produk' }}</p>
                    <span class="w-1.5 h-1.5 rounded-full {{ $m->quantity_change > 0 ? 'bg-[#34C759]' : 'bg-[#FF3B30]' }} shrink-0"></span>
                </div>
                <p class="text-[13px] text-black/45 dark:text-white/45 mt-0.5">
                    {{ $m->location->name ?? 'Outlet' }} · {{ str_replace('_', ' ', $m->movement_type) }}
                </p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5 tabular-nums">
                    {{ $m->created_at->format('d/m/Y H:i') }}
                    @if($m->notes) · {{ $m->notes }} @endif
                </p>
            </div>
            <div class="text-right shrink-0">
                <div class="text-[14px] font-bold tabular-nums {{ $m->quantity_change > 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                    {{ $m->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim((string)$m->quantity_change, '0'), '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums mt-0.5">
                    Saldo: {{ rtrim(rtrim((string)$m->balance_after, '0'), '.') }}
                </div>
            </div>
        </div>
        @empty
        <div class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">Belum ada riwayat mutasi stok.</div>
        @endforelse

        @if($movements->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
