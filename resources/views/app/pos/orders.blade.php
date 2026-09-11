@extends('layouts.app', ['title' => 'Riwayat Transaksi POS'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showDetailModal: false,
    showVoidModal: false,
    showRefundModal: false,
    selectedOrder: null,
    selectedOrderId: null,
    statusFilter: '{{ request('status', '') }}',
    viewDetail(id) {
        this.selectedOrder = (window.COOCA_POS_ORDERS || []).find(o => o.id === id) || null;
        this.showDetailModal = true;
    },
    openVoid(id) {
        this.selectedOrderId = id;
        this.showVoidModal = true;
    },
    openRefund(id) {
        this.selectedOrderId = id;
        this.showRefundModal = true;
    },
    filterByStatus(st) {
        this.statusFilter = st;
        document.getElementById('filterStatusInput').value = st;
        document.getElementById('posOrderFilterForm').submit();
    }
}">
    <script>
        window.COOCA_POS_ORDERS = @json($orders->items());
    </script>
    
    <!-- ===================================================== -->
    <!-- 0. BREADCRUMB & TOOLBAR HEADER (macOS Sonoma Style)   -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Penjualan</span>
                <span>›</span>
                <a href="{{ route('pos.terminal') }}" class="hover:text-[#007AFF] transition-colors">Kasir POS</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Riwayat Transaksi</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Riwayat Transaksi POS</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Daftar transaksi kasir, margin HPP terintegrasi, cetak ulang struk, void, dan retur.</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('pos.terminal'))
            <a href="{{ route('pos.terminal') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span>Buka Terminal Kasir</span>
            </a>
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="p-3.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[13px] font-medium flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 1. KPI SUMMARY (Flat Neutral Material)                 -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Transaksi -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Transaksi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($orders->total(), 0, ',', '.') }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40 font-medium">Order</span>
            </div>
        </div>

        <!-- Tile 2: Total Penjualan (Omzet) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Penjualan Halaman Ini</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp {{ number_format($orders->sum('total_amount'), 0, ',', '.') }}</span>
                <span class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium">Gross</span>
            </div>
        </div>

        <!-- Tile 3: Total HPP Modal -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Modal HPP</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">Rp {{ number_format($orders->sum('total_hpp_cost'), 0, ',', '.') }}</span>
                <span class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A] font-medium">BOM / Recipe</span>
            </div>
        </div>

        <!-- Tile 4: Laba Kotor -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Laba Kotor</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF]">Rp {{ number_format($orders->sum('total_gross_profit'), 0, ',', '.') }}</span>
                <span class="text-[11px] text-[#007AFF] font-medium">Profit</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 2. CONTROLS: SEGMENTED CONTROL & SEARCH BAR           -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <!-- Search & Date Filter Form -->
        <form method="GET" action="{{ route('pos.orders.index') }}" id="posOrderFilterForm" class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <input type="hidden" name="status" id="filterStatusInput" value="{{ request('status', '') }}">

            <!-- Search Field (macOS Style: Clean Fill, No Thick Border) -->
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari No. Order atau Nama Pelanggan..."
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Date Picker Field -->
            <div class="w-full sm:w-44">
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Submit & Reset Actions -->
            <div class="flex items-center gap-1.5">
                <button type="submit" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'date']))
                <a href="{{ route('pos.orders.index') }}" class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 active:scale-[0.97] transition-all flex items-center justify-center">
                    Reset
                </a>
                @endif
            </div>
        </form>

        <!-- Apple Segmented Control for Status -->
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-start lg:self-auto shrink-0">
            <button type="button" @click="filterByStatus('')"
                :class="statusFilter === '' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3 py-1 rounded-[7px] transition-all">
                Semua
            </button>
            <button type="button" @click="filterByStatus('completed')"
                :class="statusFilter === 'completed' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3 py-1 rounded-[7px] transition-all">
                Selesai
            </button>
            <button type="button" @click="filterByStatus('voided')"
                :class="statusFilter === 'voided' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3 py-1 rounded-[7px] transition-all">
                Void
            </button>
            <button type="button" @click="filterByStatus('refunded')"
                :class="statusFilter === 'refunded' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'"
                class="px-3 py-1 rounded-[7px] transition-all">
                Retur
            </button>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Order &amp; Tipe</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Waktu &amp; Kasir</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pelanggan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Total Bayar</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">HPP (Modal)</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Laba Kotor</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($orders as $o)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-black dark:text-white tabular-nums">#{{ $o->order_number }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 uppercase">{{ $o->order_type }} • {{ $o->location->name ?? 'Outlet' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="tabular-nums text-black/80 dark:text-white/80">{{ $o->order_date->format('d/m/Y') }} {{ $o->created_at->format('H:i') }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">{{ $o->user->name ?? 'Kasir' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($o->customer)
                                <div class="font-medium text-black dark:text-white">{{ $o->customer->name }}</div>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[10px] font-medium bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] uppercase">{{ $o->customer->membership_tier ?? 'Bronze' }}</span>
                            @else
                                <div class="text-black/60 dark:text-white/60">{{ $o->customer_name_guest ?? 'Pelanggan Umum' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($o->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($o->total_hpp_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($o->total_gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($o->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Selesai
                                </span>
                            @elseif($o->status === 'voided')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Void
                                </span>
                            @elseif($o->status === 'refunded')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Retur
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('pos.receipt', $o->id) }}" target="_blank" title="Cetak Struk"
                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors flex items-center">
                                    Struk
                                </a>
                                <button type="button" @click="viewDetail('{{ $o->id }}')" title="Lihat Detail"
                                    class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                    Detail
                                </button>
                                @if($o->status === 'completed')
                                    @if(\App\Support\Context::hasPermission('pos.supervisor_pin') || \App\Support\Context::hasPermission('pos.orders'))
                                    <button type="button" @click="openVoid('{{ $o->id }}')" title="Batalkan (Void)"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center">
                                        Void
                                    </button>
                                    @endif
                                    @if(\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('pos.orders'))
                                    <button type="button" @click="openRefund('{{ $o->id }}')" title="Retur / Refund"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF9500] hover:bg-[#FF9500]/8 transition-colors flex items-center">
                                        Retur
                                    </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-black/40 dark:text-white/40">Belum ada transaksi POS yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($orders as $o)
        <div class="p-3.5 space-y-2 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[14px] font-semibold text-black dark:text-white tabular-nums">#{{ $o->order_number }}</span>
                        @if($o->status === 'completed')
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                        @elseif($o->status === 'voided')
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                        @endif
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        {{ $o->customer->name ?? $o->customer_name_guest ?? 'Pelanggan Umum' }} · {{ $o->order_date->format('d/m H:i') }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-[14px] font-bold tabular-nums text-black dark:text-white">
                        Rp {{ number_format($o->total_amount, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-[#34C759] dark:text-[#30D158] font-medium tabular-nums">
                        +Rp {{ number_format($o->total_gross_profit, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-1.5 pt-1">
                <a href="{{ route('pos.receipt', $o->id) }}" target="_blank"
                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] flex items-center">
                    Struk
                </a>
                <button type="button" @click="viewDetail('{{ $o->id }}')"
                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                    Detail
                </button>
                @if($o->status === 'completed')
                    @if(\App\Support\Context::hasPermission('pos.supervisor_pin') || \App\Support\Context::hasPermission('pos.orders'))
                    <button type="button" @click="openVoid('{{ $o->id }}')"
                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 flex items-center">
                        Void
                    </button>
                    @endif
                    @if(\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('pos.orders'))
                    <button type="button" @click="openRefund('{{ $o->id }}')"
                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF9500] bg-[#FF9500]/10 flex items-center">
                        Retur
                    </button>
                    @endif
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
            Belum ada transaksi POS yang tercatat.
        </div>
        @endforelse

        @if($orders->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/5">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL: DETAIL ORDER (Apple Sheet Style)            -->
    <!-- ===================================================== -->
    <div x-show="showDetailModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        style="display: none;">
        <div class="w-full max-w-lg bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[16px] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto shadow-[0_20px_50px_rgba(0,0,0,0.25)]"
            @click.away="showDetailModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="text-[17px] font-semibold text-black dark:text-white"
                        x-text="'Detail Order #' + (selectedOrder ? selectedOrder.order_number : '')"></h3>
                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5"
                        x-text="selectedOrder ? (selectedOrder.order_date + ' • Status: ' + selectedOrder.status) : ''"></div>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">✕</button>
            </div>

            <!-- Items List -->
            <div class="space-y-2 text-[13px]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Item Terjual:</div>
                <template x-for="it in (selectedOrder ? selectedOrder.items : [])" :key="it.id">
                    <div class="p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex justify-between items-center">
                        <div>
                            <div class="font-medium text-black dark:text-white" x-text="it.product_name"></div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums"
                                x-text="it.quantity + ' x Rp ' + Number(it.unit_price).toLocaleString('id-ID')"></div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold tabular-nums text-black dark:text-white"
                                x-text="'Rp ' + Number(it.total_price).toLocaleString('id-ID')"></div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums"
                                x-text="'HPP: Rp ' + Number(it.total_hpp).toLocaleString('id-ID')"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Financial Summary -->
            <div class="p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5 text-[13px] tabular-nums">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal:</span>
                    <span class="font-medium text-black dark:text-white" x-text="'Rp ' + Number(selectedOrder ? selectedOrder.subtotal : 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Total HPP / Modal:</span>
                    <span class="font-medium text-black dark:text-white" x-text="'Rp ' + Number(selectedOrder ? selectedOrder.total_hpp_cost : 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-[#34C759] dark:text-[#30D158] font-semibold border-t border-black/5 dark:border-white/5 pt-1.5">
                    <span>Laba Kotor (Gross Profit):</span>
                    <span x-text="'Rp ' + Number(selectedOrder ? selectedOrder.total_gross_profit : 0).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="showDetailModal = false"
                    class="h-8 px-4 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 font-medium text-[12px] transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: VOID ORDER (Apple Alert Dialog Style)       -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('pos.supervisor_pin') || \App\Support\Context::hasPermission('pos.orders'))
    <div x-show="showVoidModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        style="display: none;">
        <div class="w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[14px] border border-black/5 dark:border-white/10 p-5 space-y-3 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"
            @click.away="showVoidModal = false">
            <h3 class="text-[17px] font-semibold text-[#FF3B30]">Batalkan Transaksi (Void)</h3>
            <p class="text-[12px] text-black/60 dark:text-white/60 leading-normal">
                Void akan membatalkan transaksi dan otomatis mengembalikan kuantitas produk ke stok inventori.
            </p>
            <form :action="'{{ url('/pos/orders') }}/' + selectedOrderId + '/void'" method="POST" class="space-y-3 text-[13px]">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Alasan Pembatalan</label>
                    <input type="text" name="reason" required placeholder="Misal: Salah input kasir, pelanggan batal..."
                        class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showVoidModal = false"
                        class="h-8 px-3.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-8 px-4 rounded-[8px] bg-[#FF3B30] hover:bg-[#E0352B] text-white font-semibold text-[12px] active:scale-[0.97] transition">
                        Eksekusi Void
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL: REFUND ORDER (Apple Alert Dialog Style)     -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('pos.orders'))
    <div x-show="showRefundModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        style="display: none;">
        <div class="w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[14px] border border-black/5 dark:border-white/10 p-5 space-y-3 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"
            @click.away="showRefundModal = false">
            <h3 class="text-[17px] font-semibold text-[#FF9500]">Pengembalian / Retur (Refund)</h3>
            <p class="text-[12px] text-black/60 dark:text-white/60 leading-normal">
                Catat pengembalian barang transaksi pelanggan ke sistem penjualan.
            </p>
            <form :action="'{{ url('/pos/orders') }}/' + selectedOrderId + '/refund'" method="POST" class="space-y-3 text-[13px]">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Alasan Retur</label>
                    <input type="text" name="reason" required placeholder="Misal: Barang cacat, komplain rasa..."
                        class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#FF9500]/50">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="restore_stock" value="1" checked id="restore_stock"
                        class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                    <label for="restore_stock" class="text-[12px] text-black/70 dark:text-white/70">
                        Kembalikan produk ke stok inventori (jika masih layak)
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showRefundModal = false"
                        class="h-8 px-3.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-8 px-4 rounded-[8px] bg-[#FF9500] hover:bg-[#E08600] text-white font-semibold text-[12px] active:scale-[0.97] transition">
                        Proses Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
