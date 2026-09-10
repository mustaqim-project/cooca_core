@extends('layouts.app', [
    'title' => 'Penawaran Harga (Quotations)',
    'headerTitle' => 'Penawaran Harga',
    'headerSubtitle' => 'Buat surat penawaran harga komersial resmi ke klien dan konversi ke pesanan penjualan'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Style)          -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Penjualan &amp; CRM</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Penawaran Harga</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Penawaran Harga (Quotations)</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Terbitkan penawaran harga komersial B2B dan konversi ke Pesanan Penjualan (SO) dalam 1 klik.</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('sales.quotations.create') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Penawaran Baru</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY ROW                                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- Tile 1: Total Penawaran -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Penawaran Terbit</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-black dark:text-white">
                    {{ $quotations->total() }} Dokumen
                </span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Pipeline</span>
            </div>
        </div>

        <!-- Tile 2: Nilai Potensial Total -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Nilai Akumulasi Penawaran</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                    Rp {{ number_format($quotations->sum('total_amount'), 0, ',', '.') }}
                </span>
                <span class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF]">Estimasi Omzet</span>
            </div>
        </div>

        <!-- Tile 3: Status Disetujui (System Green) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Penawaran Deal / Disetujui</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    {{ $quotations->where('status', 'accepted')->count() }} Disetujui
                </span>
                <span class="text-[11px] font-semibold text-[#34C759] dark:text-[#30D158]">Siap Konversi</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEARCH & STATUS FILTER                    -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('sales.quotations.index') }}" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- macOS Search Field -->
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor penawaran atau nama pelanggan..."
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Status Filter -->
            <div class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Status</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluwarsa</option>
                </select>

                @if(request('search') || request('status'))
                <a href="{{ route('sales.quotations.index') }}" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] transition-colors inline-flex items-center gap-1">
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
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Penawaran</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pelanggan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal Terbit</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Batas Berlaku</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Nilai Penawaran</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($quotations as $q)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('sales.quotations.show', $q) }}" class="font-medium text-[#007AFF] hover:underline">
                                {{ $q->quotation_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            {{ $q->customer->name }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $q->date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60 tabular-nums">
                            {{ $q->expiry_date ? $q->expiry_date->format('d M Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($q->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($q->status === 'accepted')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Disetujui
                                </span>
                            @elseif($q->status === 'sent')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> Terkirim
                                </span>
                            @elseif($q->status === 'rejected')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                    {{ strtoupper($q->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('sales.quotations.show', $q) }}" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors inline-flex items-center">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-[13px] text-black/40 dark:text-white/40">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-black/20 dark:text-white/20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>Belum ada surat penawaran harga.</span>
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
        @forelse($quotations as $q)
        <a href="{{ route('sales.quotations.show', $q) }}" class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">{{ $q->quotation_number }}</p>
                    @if($q->status === 'accepted')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                    @elseif($q->status === 'sent')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] shrink-0"></span>
                    @elseif($q->status === 'rejected')
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] shrink-0"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30 shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 truncate mt-0.5">{{ $q->customer->name }}</p>
                <div class="flex items-center gap-2 mt-1 text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                    <span>{{ $q->date->format('d M Y') }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold text-black dark:text-white">Rp {{ number_format($q->total_amount, 0, ',', '.') }}</span>
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
            Belum ada surat penawaran harga.
        </div>
        @endforelse
    </div>

    <!-- ===================================================== -->
    <!-- 6. PAGINATION                                         -->
    <!-- ===================================================== -->
    @if($quotations->hasPages())
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3">
        {{ $quotations->links() }}
    </div>
    @endif
</div>
@endsection
