@extends('layouts.app', ['title' => 'Riwayat Transaksi POS'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showDetailModal: false,
    showVoidModal: false,
    showRefundModal: false,
    selectedOrder: null,
    selectedOrderId: null,
    statusFilter: '{{ request('status', '') }}',
    isSubmitting: false,
    viewDetail(id) {
        this.selectedOrder = (window.COOCA_POS_ORDERS || []).find(o => o.id === id) || null;
        this.showDetailModal = true;
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
    openVoid(id) {
        this.selectedOrderId = id;
        this.showVoidModal = true;
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
    openRefund(id) {
        this.selectedOrderId = id;
        this.showRefundModal = true;
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    },
    async confirmReprint(url, printCount) {
        const ok = typeof AppAlert !== 'undefined'
            ? await AppAlert.confirm({
                title: 'Cetak Ulang Bill?',
                message: 'Cetak Ulang (Re-Print) Bill ini? Tindakan ini akan dicatat dalam Jejak Audit sebagai Salinan / Cetakan ke-' + (printCount + 1) + '.',
                type: 'warning',
                confirmText: 'Cetak Ulang',
                cancelText: 'Batal'
            })
            : confirm('Cetak Ulang (Re-Print) Bill ini?');
        if (ok) {
            window.open(url, '_blank');
        }
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
    <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm transition-colors">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <span class="text-black/70 dark:text-white/70 font-medium">Penjualan</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <a href="{{ route('pos.terminal') }}" class="hover:text-[#007AFF] transition-colors">Kasir POS</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <span class="text-black dark:text-white font-medium">Riwayat Transaksi</span>
            </nav>
            <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">Riwayat Transaksi POS</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Daftar transaksi kasir, margin HPP terintegrasi, cetak ulang struk, void, dan retur.</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('pos.terminal'))
            <a href="{{ route('pos.terminal') }}" class="min-h-[44px] h-11 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-sm w-full sm:w-auto">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
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
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-semibold text-black dark:text-white tabular-nums">#{{ $o->order_number }}</span>
                                @if($o->vehicle_license_plate)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-mono font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                        {{ $o->vehicle_license_plate }}
                                    </span>
                                @elseif($o->laundry_weight_kg)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                        {{ $o->laundry_weight_kg }} kg
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 flex items-center gap-1.5 flex-wrap">
                                <span class="uppercase font-medium">{{ $o->order_type }}</span>
                                @if($o->sales_channel && $o->sales_channel !== 'dine_in')
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-black uppercase text-white tracking-wider
                                        {{ $o->sales_channel === 'gofood' ? 'bg-[#00AA13]' : ($o->sales_channel === 'grabfood' ? 'bg-[#00B14F]' : ($o->sales_channel === 'shopeefood' ? 'bg-[#EE4D2D]' : 'bg-[#5856D6]')) }}">
                                        {{ $o->sales_channel }}{{ $o->external_order_ref ? ' #' . $o->external_order_ref : '' }}
                                    </span>
                                @endif
                                <span>• {{ $o->location->name ?? 'Outlet' }}</span>
                            </div>
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
                            <div class="mt-1">
                                @if(($o->print_count ?? 0) === 0)
                                    <span class="text-[10px] text-black/40 dark:text-white/40">Belum cetak</span>
                                @elseif($o->print_count === 1)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]" title="Cetakan Asli">
                                        1x Cetak (Asli)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]" title="Telah dicetak ulang {{ $o->print_count }} kali (Salinan ke-{{ $o->reprint_count }})">
                                        {{ $o->print_count }}x Cetak
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if(($o->print_count ?? 0) > 0)
                                    <button type="button"
                                        @click="confirmReprint('{{ route('pos.receipt', $o->id) }}?reprint=1', {{ (int)$o->print_count }})"
                                        title="Cetak Ulang (Salinan ke-{{ $o->print_count }})"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF9500] hover:bg-[#FF9500]/10 transition-colors flex items-center">
                                        Re-Print
                                    </button>
                                @else
                                    <a href="{{ route('pos.receipt', $o->id) }}" target="_blank" title="Cetak Bill Pertama Kali"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors flex items-center">
                                        Struk
                                    </a>
                                @endif
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
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[14px] font-semibold text-black dark:text-white tabular-nums">#{{ $o->order_number }}</span>
                        @if($o->vehicle_license_plate)
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-mono font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                {{ $o->vehicle_license_plate }}
                            </span>
                        @elseif($o->laundry_weight_kg)
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                {{ $o->laundry_weight_kg }} kg
                            </span>
                        @endif
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
                @if(($o->print_count ?? 0) > 0)
                <button type="button"
                    @click="confirmReprint('{{ route('pos.receipt', $o->id) }}?reprint=1', {{ (int)$o->print_count }})"
                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#B25E00] dark:text-[#FF9F0A] bg-[#FF9500]/15 flex items-center gap-1"
                    title="Cetak Salinan (Ke-{{ $o->print_count }})">
                    <span>Re-Print ({{ $o->print_count }}x)</span>
                </button>
                @else
                <a href="{{ route('pos.receipt', $o->id) }}" target="_blank"
                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] flex items-center">
                    Struk
                </a>
                @endif
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
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-sm p-0 sm:p-4"
        @keydown.escape.window="showDetailModal = false">
        <div class="w-full sm:max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto shadow-2xl"
            @click.away="showDetailModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#007AFF]/15 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white"
                            x-text="'Detail Transaksi #' + (selectedOrder ? selectedOrder.order_number : '')"></h3>
                        <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5"
                            x-text="selectedOrder ? (selectedOrder.order_date + ' • Status: ' + selectedOrder.status) : ''"></div>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Industry Specific Attributes (Bengkel / Laundry) -->
            <template x-if="selectedOrder && (selectedOrder.vehicle_license_plate || selectedOrder.laundry_weight_kg)">
                <div class="space-y-2">
                    <!-- Bengkel SPK Card -->
                    <template x-if="selectedOrder.vehicle_license_plate">
                        <div class="p-3.5 rounded-[14px] bg-[#007AFF]/10 border border-[#007AFF]/20 space-y-2 text-[13px]">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#007AFF] uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                                    <span>Layanan Bengkel &amp; SPK</span>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-md font-mono font-bold bg-[#007AFF] text-white text-[12px]" x-text="selectedOrder.vehicle_license_plate"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-black/80 dark:text-white/80">
                                <div><span class="text-black/50 dark:text-white/50">Model:</span> <span class="font-semibold text-black dark:text-white" x-text="selectedOrder.vehicle_model || '-'"></span></div>
                                <div><span class="text-black/50 dark:text-white/50">KM:</span> <span class="font-bold font-mono text-black dark:text-white" x-text="selectedOrder.vehicle_mileage ? Number(selectedOrder.vehicle_mileage).toLocaleString('id-ID') : '-'"></span></div>
                                <div class="col-span-2"><span class="text-black/50 dark:text-white/50">Teknisi / Mekanik:</span> <span class="font-semibold text-black dark:text-white" x-text="selectedOrder.technician ? selectedOrder.technician.name : '-'"></span></div>
                                <template x-if="selectedOrder.service_notes">
                                    <div class="col-span-2 pt-1 border-t border-[#007AFF]/15">
                                        <span class="text-black/50 dark:text-white/50">Keluhan / Catatan:</span>
                                        <p class="font-medium text-black dark:text-white mt-0.5" x-text="selectedOrder.service_notes"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Laundry Card -->
                    <template x-if="selectedOrder.laundry_weight_kg">
                        <div class="p-3.5 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 space-y-2 text-[13px]">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#34C759] uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="shirt" class="w-3.5 h-3.5"></i>
                                    <span>Layanan Laundry Kiloan</span>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-md font-bold bg-[#34C759] text-white text-[12px]" x-text="selectedOrder.laundry_weight_kg + ' Kg'"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-black/80 dark:text-white/80">
                                <div><span class="text-black/50 dark:text-white/50">Rak / Loker:</span> <span class="font-semibold text-black dark:text-white" x-text="selectedOrder.rack_location || '-'"></span></div>
                                <div><span class="text-black/50 dark:text-white/50">Status:</span> <span class="font-bold uppercase text-[#34C759]" x-text="selectedOrder.laundry_status || 'received'"></span></div>
                                <template x-if="selectedOrder.estimated_completion_at">
                                    <div class="col-span-2"><span class="text-black/50 dark:text-white/50">Estimasi Selesai:</span> <span class="font-semibold text-black dark:text-white" x-text="selectedOrder.estimated_completion_at"></span></div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Items List -->
            <div class="space-y-2 text-[13px]">
                <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Item Terjual:</div>
                <template x-for="it in (selectedOrder ? selectedOrder.items : [])" :key="it.id">
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold text-black dark:text-white" x-text="it.product_name"></div>
                                <div class="text-[12px] text-black/55 dark:text-white/55 tabular-nums"
                                    x-text="it.quantity + ' x Rp ' + Number(it.unit_price).toLocaleString('id-ID')"></div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold tabular-nums text-black dark:text-white"
                                    x-text="'Rp ' + Number(it.total_price).toLocaleString('id-ID')"></div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums"
                                    x-text="'HPP: Rp ' + Number(it.total_hpp).toLocaleString('id-ID')"></div>
                            </div>
                        </div>
                        <!-- Pharmacy attributes if present -->
                        <template x-if="it.batch_number || it.expired_date || it.dosage_instructions">
                            <div class="pt-1.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/60 dark:text-white/60 space-y-0.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <template x-if="it.batch_number">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[10px]">
                                            Batch: <span class="font-bold ml-1" x-text="it.batch_number"></span>
                                        </span>
                                    </template>
                                    <template x-if="it.expired_date">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-red-500/10 text-red-600 dark:text-red-400 font-mono text-[10px]">
                                            ED: <span class="font-bold ml-1" x-text="it.expired_date"></span>
                                        </span>
                                    </template>
                                </div>
                                <template x-if="it.dosage_instructions">
                                    <div class="italic text-[11px] text-[#007AFF]" x-text="'Dosis: ' + it.dosage_instructions"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Financial Summary -->
            <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 text-[13px] tabular-nums">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal:</span>
                    <span class="font-bold text-black dark:text-white" x-text="'Rp ' + Number(selectedOrder ? selectedOrder.subtotal : 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Total HPP / Modal:</span>
                    <span class="font-bold text-black dark:text-white" x-text="'Rp ' + Number(selectedOrder ? selectedOrder.total_hpp_cost : 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between text-[#34C759] dark:text-[#30D158] font-bold text-[14px] border-t border-black/5 dark:border-white/5 pt-2">
                    <span>Laba Kotor (Gross Profit):</span>
                    <span x-text="'Rp ' + Number(selectedOrder ? selectedOrder.total_gross_profit : 0).toLocaleString('id-ID')"></span>
                </div>
            </div>

            <template x-if="selectedOrder && selectedOrder.status !== 'completed' && selectedOrder.status !== 'voided' && selectedOrder.gateway_reference">
                <form :action="'{{ url('/pos/orders') }}/' + selectedOrder.id + '/sync-gateway'" method="POST" class="pt-1">
                    @csrf
                    <button type="submit"
                        class="w-full min-h-[44px] h-11 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-[13px] font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98]">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Cek &amp; Sinkronkan Status TriPay</span>
                    </button>
                </form>
            </template>

            <div class="flex justify-end pt-2">
                <button type="button" @click="showDetailModal = false"
                    class="min-h-[44px] h-11 px-5 rounded-[12px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 font-medium text-[13px] transition">
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
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-sm p-0 sm:p-4"
        @keydown.escape.window="showVoidModal = false">
        <div class="w-full sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/5 dark:border-white/10 p-6 space-y-4 shadow-2xl"
            @click.away="showVoidModal = false">
            <div class="flex items-center gap-2.5 text-[#FF3B30]">
                <div class="w-9 h-9 rounded-xl bg-[#FF3B30]/15 flex items-center justify-center">
                    <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                </div>
                <h3 class="text-[17px] font-bold">Batalkan Transaksi (Void)</h3>
            </div>
            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Void akan membatalkan transaksi dan otomatis mengembalikan kuantitas produk ke stok inventori secara akurat.
            </p>
            <form :action="'{{ url('/pos/orders') }}/' + selectedOrderId + '/void'" method="POST" @submit="isSubmitting = true" class="space-y-3.5 text-[13px]">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Alasan Pembatalan <span class="text-red-500">*</span></label>
                    <input type="text" name="reason" required placeholder="Misal: Salah input kasir, pelanggan batal..."
                        class="w-full min-h-[44px] h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>
                @if($business->pos_require_pin_for_void && !($canBypassSupervisor ?? false))
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-[#FF3B30] mb-1 flex items-center justify-between">
                        <span>PIN Otorisasi Supervisor</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40 font-normal">Wajib</span>
                    </label>
                    <input type="password" name="pin" required inputmode="numeric" maxlength="8" placeholder="Masukkan 4-8 digit PIN..."
                        class="w-full min-h-[44px] h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] font-mono tracking-widest text-black dark:text-white placeholder:font-sans placeholder:tracking-normal placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]">
                </div>
                @endif
                <div class="flex justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showVoidModal = false" :disabled="isSubmitting"
                        class="min-h-[44px] h-11 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="min-h-[44px] h-11 px-6 rounded-[12px] bg-[#FF3B30] hover:bg-[#E0352B] text-white font-semibold text-[13px] active:scale-[0.97] transition shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Memproses...' : 'Void'"></span>
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
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-sm p-0 sm:p-4"
        @keydown.escape.window="showRefundModal = false">
        <div class="w-full sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/5 dark:border-white/10 p-6 space-y-4 shadow-2xl"
            @click.away="showRefundModal = false">
            <div class="flex items-center gap-2.5 text-[#FF9500]">
                <div class="w-9 h-9 rounded-xl bg-[#FF9500]/15 flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <h3 class="text-[17px] font-bold">Pengembalian / Retur (Refund)</h3>
            </div>
            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Catat pengembalian barang transaksi pelanggan ke sistem penjualan secara resmi.
            </p>
            <form :action="'{{ url('/pos/orders') }}/' + selectedOrderId + '/refund'" method="POST" @submit="isSubmitting = true" class="space-y-3.5 text-[13px]">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">Alasan Retur <span class="text-red-500">*</span></label>
                    <input type="text" name="reason" required placeholder="Misal: Barang cacat, komplain rasa..."
                        class="w-full min-h-[44px] h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#FF9500]">
                </div>
                @if($business->pos_require_pin_for_refund && !($canBypassSupervisor ?? false))
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-[#FF9500] mb-1 flex items-center justify-between">
                        <span>PIN Otorisasi Supervisor</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40 font-normal">Wajib</span>
                    </label>
                    <input type="password" name="pin" required inputmode="numeric" maxlength="8" placeholder="Masukkan 4-8 digit PIN..."
                        class="w-full min-h-[44px] h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[16px] font-mono tracking-widest text-black dark:text-white placeholder:font-sans placeholder:tracking-normal placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#FF9500]">
                </div>
                @endif
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="restore_stock" value="1" checked id="restore_stock"
                        class="w-4 h-4 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                    <label for="restore_stock" class="text-[13px] text-black/80 dark:text-white/80 cursor-pointer">
                        Kembalikan produk ke stok inventori (jika masih layak)
                    </label>
                </div>
                <div class="flex justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showRefundModal = false" :disabled="isSubmitting"
                        class="min-h-[44px] h-11 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="min-h-[44px] h-11 px-6 rounded-[12px] bg-[#FF9500] hover:bg-[#E08600] text-white font-semibold text-[13px] active:scale-[0.97] transition shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <template x-if="isSubmitting">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Memproses...' : 'Retur'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
