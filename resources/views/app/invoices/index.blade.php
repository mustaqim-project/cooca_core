@extends('layouts.app', [
    'title' => 'Faktur Penjualan (Invoices)',
    'headerTitle' => 'Manajemen Faktur & Penagihan',
    'headerSubtitle' => 'Kelola faktur penjualan komersial, pelacakan piutang, dan pencatatan pembayaran'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    deleteModalOpen: false,
    deleteTarget: { url: '', name: '' },
    openDelete(url, name) {
        this.deleteTarget = { url, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { url: '', name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.url) {
            const form = document.getElementById('form-delete-invoice');
            form.action = this.deleteTarget.url;
            form.submit();
        }
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Penjualan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Faktur Penjualan</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Faktur Penjualan</h1>
                    <p class="text-[13px] text-black/50 dark:text-white/50">Kelola faktur komersial, pelacakan piutang, dan pencatatan pembayaran</p>
                </div>
            </div>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
            @if(\App\Support\Context::hasPermission('invoices.export'))
            <a href="{{ route('invoices.export-excel') }}"
               class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Export CSV</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('invoices.create'))
            <a href="{{ route('invoices.create') }}"
               class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Faktur Baru</span>
            </a>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- FLASH MESSAGES (Apple HIG Banner Style)                -->
    <!-- ===================================================== -->
    @if(session('success'))
        <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-[#FF3B30]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Apple HIG Cards)          -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Nilai Faktur (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Nilai Faktur</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white">
                    {{ $business->currency_symbol }} {{ number_format($totalInvoiced, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Tertagih</span>
            </div>
        </div>

        <!-- Tile 2: Sudah Diterima (System Green for Profit/Paid) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sudah Diterima (Lunas)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format($totalPaid, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Kas Masuk</span>
            </div>
        </div>

        <!-- Tile 3: Sisa Piutang (System Orange for Receivables) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Sisa Piutang Belum Bayar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                    {{ $business->currency_symbol }} {{ number_format($totalReceivables, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A]">Tertunda</span>
            </div>
        </div>

        <!-- Tile 4: Laba Kotor Riil (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Laba Kotor Riil (Gross Profit)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[20px] sm:text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $business->currency_symbol }} {{ number_format($totalGrossProfit, 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Margin</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEARCH & STATUS FILTER TOOLBAR            -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Field (macOS Style: Clean Fill, No Thick Border) -->
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari no. faktur, nama klien, atau perusahaan..."
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Filter Status Dropdown Styled in Apple HIG -->
            <div class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()"
                        class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Status Tagihan</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim (Sent)</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar (Unpaid)</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Cicilan (Partially Paid)</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Jatuh Tempo (Overdue)</option>
                    <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                </select>

                @if(request('search') || request('status'))
                    <a href="{{ route('invoices.index') }}" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition">
                        Reset Filter
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
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nomor Faktur &amp; Tanggal</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Pelanggan / Klien</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Jatuh Tempo</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center whitespace-nowrap">Status</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Total Tagihan</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Sisa Piutang</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Laba Kotor</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4">
                            <a href="{{ route('invoices.show', $inv->id) }}" class="font-semibold text-black dark:text-white hover:text-[#007AFF] transition-colors tabular-nums">
                                {{ $inv->invoice_number }}
                            </a>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                {{ $inv->invoice_date?->translatedFormat('d M Y') }}
                            </div>
                            @if($inv->purchaseOrder)
                                <div class="text-[10px] text-black/40 dark:text-white/40 tabular-nums">PO: {{ $inv->purchaseOrder->po_number }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-black dark:text-white">{{ $inv->customer?->name ?? 'Pelanggan' }}</div>
                            @if($inv->customer?->company_name)
                                <div class="text-[11px] text-black/45 dark:text-white/45">{{ $inv->customer->company_name }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 tabular-nums text-black/70 dark:text-white/70">
                            <div>{{ $inv->due_date?->translatedFormat('d M Y') }}</div>
                            @if($inv->balance_due > 0 && $inv->due_date && $inv->due_date->isPast())
                                <span class="inline-flex items-center text-[10px] font-semibold text-[#FF3B30] dark:text-[#FF453A]">
                                    Terlewat {{ $inv->due_date->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $statusStyles = [
                                    'draft' => ['bg' => 'bg-black/6 dark:bg-white/8', 'text' => 'text-black/55 dark:text-white/55', 'dot' => 'bg-black/40 dark:bg-white/40', 'label' => 'Draft'],
                                    'sent' => ['bg' => 'bg-[#007AFF]/12', 'text' => 'text-[#007AFF] dark:text-[#0A84FF]', 'dot' => 'bg-[#007AFF]', 'label' => 'Terkirim'],
                                    'unpaid' => ['bg' => 'bg-[#FF9500]/12', 'text' => 'text-[#B25E00] dark:text-[#FF9F0A]', 'dot' => 'bg-[#FF9500]', 'label' => 'Belum Bayar'],
                                    'partially_paid' => ['bg' => 'bg-[#FF9500]/12', 'text' => 'text-[#B25E00] dark:text-[#FF9F0A]', 'dot' => 'bg-[#FF9500]', 'label' => 'Cicilan'],
                                    'paid' => ['bg' => 'bg-[#34C759]/12', 'text' => 'text-[#248A3D] dark:text-[#30D158]', 'dot' => 'bg-[#34C759]', 'label' => 'Lunas'],
                                    'overdue' => ['bg' => 'bg-[#FF3B30]/12', 'text' => 'text-[#C41E17] dark:text-[#FF453A]', 'dot' => 'bg-[#FF3B30]', 'label' => 'Jatuh Tempo'],
                                    'void' => ['bg' => 'bg-black/6 dark:bg-white/8', 'text' => 'text-black/40 dark:text-white/40 line-through', 'dot' => 'bg-black/30', 'label' => 'Dibatalkan'],
                                ];
                                $style = $statusStyles[$inv->status] ?? $statusStyles['draft'];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $style['bg'] }} {{ $style['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
                                <span>{{ $style['label'] }}</span>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                            {{ $business->currency_symbol }} {{ number_format((float) $inv->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums font-semibold {{ $inv->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                            {{ $business->currency_symbol }} {{ number_format((float) $inv->balance_due, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-[#34C759] dark:text-[#30D158] font-medium">
                            {{ $business->currency_symbol }} {{ number_format((float) $inv->total_gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center" title="Lihat Faktur">
                                    Detail
                                </a>
                                <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors flex items-center" title="Cetak Faktur">
                                    Cetak
                                </a>
                                @if($inv->status === 'draft' || $inv->status === 'void')
                                    <button type="button" @click="openDelete('{{ route('invoices.destroy', $inv->id) }}', '{{ addslashes($inv->invoice_number) }}')" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center" title="Hapus Faktur">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="text-[13px] font-medium">Belum ada faktur penjualan yang diterbitkan.</p>
                                <p class="text-[11px] text-black/40 dark:text-white/40">Buat faktur baru atau terbitkan langsung dari purchase order pelanggan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            <div>
                Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }}</span> dari <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoices->total() }}</span> faktur
            </div>
            <div>
                {{ $invoices->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($invoices as $inv)
            @php
                $statusStyles = [
                    'draft' => ['dot' => 'bg-black/40', 'label' => 'Draft'],
                    'sent' => ['dot' => 'bg-[#007AFF]', 'label' => 'Sent'],
                    'unpaid' => ['dot' => 'bg-[#FF9500]', 'label' => 'Unpaid'],
                    'partially_paid' => ['dot' => 'bg-[#FF9500]', 'label' => 'Cicilan'],
                    'paid' => ['dot' => 'bg-[#34C759]', 'label' => 'Lunas'],
                    'overdue' => ['dot' => 'bg-[#FF3B30]', 'label' => 'Overdue'],
                    'void' => ['dot' => 'bg-black/30', 'label' => 'Void'],
                ];
                $style = $statusStyles[$inv->status] ?? $statusStyles['draft'];
            @endphp
            <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
                <a href="{{ route('invoices.show', $inv->id) }}" class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $style['dot'] }} shrink-0"></span>
                        <p class="text-[15px] font-semibold text-black dark:text-white truncate tabular-nums">{{ $inv->invoice_number }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                            {{ $style['label'] }}
                        </span>
                    </div>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-0.5 truncate">
                        {{ $inv->customer?->name ?? 'Pelanggan' }} · {{ $inv->invoice_date?->translatedFormat('d M Y') }}
                    </p>
                    <p class="text-[13px] font-semibold tabular-nums mt-0.5 text-black dark:text-white">
                        {{ $business->currency_symbol }} {{ number_format((float) $inv->total_amount, 0, ',', '.') }}
                        @if($inv->balance_due > 0)
                            <span class="text-[11px] font-medium text-[#FF9500] dark:text-[#FF9F0A] ml-1">
                                (Sisa: {{ $business->currency_symbol }} {{ number_format((float)$inv->balance_due, 0, ',', '.') }})
                            </span>
                        @endif
                    </p>
                </a>
                <div class="flex items-center gap-1.5 shrink-0">
                    <a href="{{ route('invoices.show', $inv->id) }}" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                        Detail
                    </a>
                    @if($inv->status === 'draft' || $inv->status === 'void')
                        <button type="button" @click="openDelete('{{ route('invoices.destroy', $inv->id) }}', '{{ addslashes($inv->invoice_number) }}')" class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                Belum ada faktur penjualan yang diterbitkan.
            </div>
        @endforelse

        @if($invoices->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. APPLE ALERT DIALOG (Native Centered Modal)          -->
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
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Faktur?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Faktur <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus permanen dari sistem.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()"
                        class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()"
                        class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden Invoices Delete Form -->
    <form id="form-delete-invoice" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection
