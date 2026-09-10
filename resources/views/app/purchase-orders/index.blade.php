@extends('layouts.app', [
    'title' => 'Purchase Orders (PO)',
    'headerTitle' => 'Manajemen Purchase Order (PO)',
    'headerSubtitle' => 'Kelola pesanan masuk dari pelanggan dan order pengadaan bahan baku ke pemasok'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    instantModalOpen: false,
    deleteModalOpen: false,
    deleteTarget: { id: null, number: '' },
    openDelete(id, number) {
        this.deleteTarget = { id, number };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: null, number: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            document.getElementById('form-delete-po-' + this.deleteTarget.id).submit();
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Style)          -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pembelian &amp; Pengadaan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Purchase Orders</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Manajemen Purchase Order (PO)</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola pesanan penjualan B2B dan order pengadaan bahan baku ke pemasok.</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" @click="instantModalOpen = true"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 hover:bg-[#FF9500]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
                <span>1-Klik Beli ke Stok</span>
            </button>

            <a href="{{ route('purchase-orders.create') }}" 
               class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat PO Baru</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY ROW                                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Pesanan (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Pesanan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ $totalOrders }} PO</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Akumulasi</span>
            </div>
        </div>

        <!-- Tile 2: Dikonfirmasi (System Blue) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Dikonfirmasi (Siap Proses)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">{{ $totalConfirmed }} PO</span>
                <span class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF]">Terjadwal</span>
            </div>
        </div>

        <!-- Tile 3: Sudah Jadi Faktur (System Purple) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sudah Jadi Faktur</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">{{ $totalInvoiced }} PO</span>
                <span class="text-[11px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2]">Terbit Invoice</span>
            </div>
        </div>

        <!-- Tile 4: Nilai Total Pesanan (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Nilai Total Pesanan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format($totalSum, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">Omzet / Biaya</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEARCH & FILTERS                          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- macOS Search Field -->
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari no. PO, nama klien, atau vendor..." 
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Tipe PO Filter -->
            <div class="flex items-center gap-2">
                <select name="po_type" onchange="this.form.submit()"
                        class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Tipe PO</option>
                    <option value="customer" {{ request('po_type') === 'customer' ? 'selected' : '' }}>PO Pelanggan (Penjualan)</option>
                    <option value="supplier" {{ request('po_type') === 'supplier' ? 'selected' : '' }}>PO Supplier (Pengadaan)</option>
                </select>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()"
                        class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="fully_invoiced" {{ request('status') === 'fully_invoiced' ? 'selected' : '' }}>Invoiced</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                @if(request('search') || request('po_type') || request('status'))
                <a href="{{ route('purchase-orders.index') }}" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Reset</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Nomor PO &amp; Tanggal</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tipe</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pihak Terkait</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Item</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nilai Total</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="font-medium text-[#007AFF] hover:underline tabular-nums">
                                {{ $po->po_number }}
                            </a>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">{{ $po->order_date?->translatedFormat('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($po->po_type === 'customer')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    Pesanan Klien
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                    Pengadaan Vendor
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($po->customer)
                                <div class="font-medium text-black dark:text-white">{{ $po->customer->name }}</div>
                                @if($po->customer->company_name)
                                    <div class="text-[11px] text-black/45 dark:text-white/45">{{ $po->customer->company_name }}</div>
                                @endif
                            @elseif($po->supplier)
                                <div class="font-medium text-black dark:text-white">{{ $po->supplier->name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">{{ $po->supplier->contact_person }}</div>
                            @else
                                <span class="text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusBadges = [
                                    'draft' => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                    'confirmed' => 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]',
                                    'partially_invoiced' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                    'fully_invoiced' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                    'completed' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                    'cancelled' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                ];
                                $badgeClass = $statusBadges[$po->status] ?? 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60';
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                {{ str_replace('_', ' ', $po->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center tabular-nums text-black/70 dark:text-white/70">
                            {{ $po->items->count() }} item
                        </td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums text-black dark:text-white">
                            {{ $business->currency_symbol }} {{ number_format((float) $po->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center" title="Lihat Detail">
                                    Detail
                                </a>

                                <a href="{{ route('purchase-orders.print', $po->id) }}?download=1" target="_blank" class="h-7 w-7 rounded-[6px] text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors inline-flex items-center justify-center" title="Download PDF Langsung">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </a>

                                <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="h-7 w-7 rounded-[6px] text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors inline-flex items-center justify-center" title="Cetak / Pratinjau">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.37-2.2-.37-3.329 0-6.075 4.925-11 11-11s11 4.925 11 11c0 1.129-.13 2.252-.37 3.329M3.75 14.25h16.5M6 18h12m-9 3h6" />
                                    </svg>
                                </a>

                                @if($po->po_type === 'customer' && $po->status !== 'fully_invoiced' && $po->status !== 'cancelled')
                                <form method="POST" action="{{ route('purchase-orders.generate-invoice', $po->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#34C759] hover:bg-[#34C759]/10 transition-colors inline-flex items-center" title="Generate Faktur Langsung">
                                        Faktur
                                    </button>
                                </form>
                                @endif

                                @if($po->po_type === 'supplier' && $po->status === 'confirmed')
                                <a href="{{ route('purchasing.receipts.create', $po->id) }}" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF9500] hover:bg-[#FF9500]/10 transition-colors inline-flex items-center" title="Terima Barang Masuk ke Gudang">
                                    Terima
                                </a>
                                @endif

                                @if($po->status === 'draft')
                                <button type="button" @click="openDelete({{ $po->id }}, '{{ addslashes($po->po_number) }}')" class="h-7 w-7 rounded-[6px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors inline-flex items-center justify-center" title="Hapus Draft">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                                <form id="form-delete-po-{{ $po->id }}" method="POST" action="{{ route('purchase-orders.destroy', $po->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>Belum ada dokumen Purchase Order.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (iOS 18 Style)            -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($purchaseOrders as $po)
        <a href="{{ route('purchase-orders.show', $po->id) }}" class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate tabular-nums">{{ $po->po_number }}</p>
                    @if($po->status === 'fully_invoiced' || $po->status === 'completed')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($po->status === 'confirmed')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                    @elseif($po->status === 'partially_invoiced')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                    @elseif($po->status === 'cancelled')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30 shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate mt-0.5">
                    {{ $po->customer ? $po->customer->name : ($po->supplier ? $po->supplier->name : '-') }}
                </p>
                <div class="flex items-center gap-2 mt-1 text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $po->order_date?->translatedFormat('d M Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format((float) $po->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-black/30 dark:text-white/30 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>
        @empty
        <div class="p-8 text-center text-[13px] text-black/40 dark:text-white/40">
            Belum ada dokumen Purchase Order.
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 6. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($purchaseOrders->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $purchaseOrders->links() }}
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. INSTANT STOCK-IN SHEET MODAL                       -->
    <!-- ===================================================== -->
    <div x-show="instantModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-lg rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"
            @click.away="instantModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 pt-5 pb-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">1-Klik Beli Langsung ke Stok</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Beli bahan/produk di pasar &amp; langsung tambah stok seketika tanpa alur PO bertingkat.</p>
                </div>
                <button type="button" @click="instantModalOpen = false" class="w-8 h-8 rounded-[8px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('purchasing.instant-stock-in') }}" method="POST" class="p-5 pt-0 space-y-4 text-[13px]">
                @csrf
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Gudang Tujuan Masuk <span class="text-[#FF3B30]">*</span></label>
                    <select name="location_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Pilih Produk / Bahan Masuk <span class="text-[#FF3B30]">*</span></label>
                    <select name="product_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Jumlah Masuk <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="quantity" min="0.01" step="any" required placeholder="Contoh: 10"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Harga Beli Satuan (Rp) <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="unit_cost" min="0" step="any" required placeholder="Contoh: 25000"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Pemasok / Toko Pembelian (Opsional)</label>
                    <select name="supplier_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="">-- Tanpa Pemasok Tertentu (Belanja Pasar) --</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Catatan</label>
                    <input type="text" name="notes" placeholder="Contoh: Nota Toko Sumber Rejeki"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="instantModalOpen = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Beli &amp; Tambah ke Stok</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 8. APPLE ALERT DIALOG (Hapus Draft PO)                 -->
    <!-- ===================================================== -->
    <div x-show="deleteModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Draf PO?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Draf <span x-text="deleteTarget.number" class="font-medium text-black dark:text-white tabular-nums"></span> akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
