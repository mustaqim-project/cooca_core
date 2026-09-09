@extends('layouts.app', [
    'title' => 'Pesanan Penjualan (Sales Orders) — Cooca UMKM',
    'headerTitle' => 'Pesanan Penjualan (Sales Orders)',
    'headerSubtitle' => 'Kelola pesanan pelanggan terkonfirmasi, alokasi stok pengiriman, dan terbitkan faktur penagihan (Invoice).'
])

@section('content')
@php
    $totalCount = $salesOrders->total();
    $confirmedOrders = $salesOrders->filter(fn($so) => $so->status === 'confirmed');
    $partialOrders = $salesOrders->filter(fn($so) => $so->status === 'partially_fulfilled');
    $fulfilledOrders = $salesOrders->filter(fn($so) => $so->status === 'fulfilled');
    $totalPageValue = $salesOrders->sum('total_amount');
    $currentStatus = request('status', '');
    $currentSearch = request('search', '');
@endphp

<div class="space-y-6">

    <!-- Standard Breadcrumb & Executive Page Header -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800/80" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-cyan-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-700 dark:text-slate-300">Kasir &amp; Penjualan</span>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-900 dark:text-slate-200 font-semibold" aria-current="page">Pesanan Penjualan (SO)</span>
            </li>
            <li class="hidden sm:inline text-slate-400 dark:text-slate-600" aria-hidden="true">•</li>
            <li class="hidden sm:inline">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 border border-cyan-500/20">
                    Sales Pipeline
                </span>
            </li>
        </ol>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('sales.orders.create') }}"
                class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-sm shadow-cyan-600/20 hover:shadow-cyan-600/30 transition-all flex items-center gap-2 focus-visible:ring-2 focus-visible:ring-cyan-500">
                <i data-lucide="plus" class="w-4 h-4" aria-hidden="true"></i>
                <span>Buat Sales Order</span>
            </a>
        </div>
    </nav>

    <!-- 4-Pillar Executive KPI Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Orders -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Sales Order</span>
                <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ number_format($totalCount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Total pesanan terdaftar</p>
        </div>

        <!-- 2. Terkonfirmasi (Confirmed) -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terkonfirmasi</span>
                <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-cyan-600 dark:text-cyan-400">
                {{ $confirmedOrders->count() }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Siap alokasi &amp; invoice</p>
        </div>

        <!-- 3. Sebagian Terpenuhi -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sebagian Terpenuhi</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-amber-600 dark:text-amber-400">
                {{ $partialOrders->count() }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Pengiriman parsial aktif</p>
        </div>

        <!-- 4. Selesai (Fulfilled) -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Selesai (Fulfilled)</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="package-check" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                {{ $fulfilledOrders->count() }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Tuntas terkirim &amp; ditagih</p>
        </div>
    </div>

    <!-- Filter Bar & Quick Status Chips -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-3">
        <form method="GET" action="{{ route('sales.orders.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" aria-hidden="true"></i>
                <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Cari nomor SO atau nama pelanggan..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-cyan-500 transition">
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
                <!-- Dropdown Status -->
                <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-cyan-500 transition cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="confirmed" {{ $currentStatus === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                    <option value="partially_fulfilled" {{ $currentStatus === 'partially_fulfilled' ? 'selected' : '' }}>Sebagian Terpenuhi</option>
                    <option value="fulfilled" {{ $currentStatus === 'fulfilled' ? 'selected' : '' }}>Selesai (Fulfilled)</option>
                </select>

                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5" aria-hidden="true"></i>
                    <span>Terapkan</span>
                </button>

                @if($currentSearch || $currentStatus)
                    <a href="{{ route('sales.orders.index') }}"
                        class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="x" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>

        <!-- Quick Filter Category Chips -->
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Filter Cepat:</span>
            <a href="{{ route('sales.orders.index', ['search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ empty($currentStatus) ? 'bg-cyan-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Semua
            </a>
            <a href="{{ route('sales.orders.index', ['status' => 'confirmed', 'search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $currentStatus === 'confirmed' ? 'bg-cyan-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Terkonfirmasi
            </a>
            <a href="{{ route('sales.orders.index', ['status' => 'partially_fulfilled', 'search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $currentStatus === 'partially_fulfilled' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Sebagian Terpenuhi
            </a>
            <a href="{{ route('sales.orders.index', ['status' => 'fulfilled', 'search' => $currentSearch]) }}"
                class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $currentStatus === 'fulfilled' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Selesai
            </a>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800 whitespace-nowrap">
                    <tr>
                        <th scope="col" class="py-3 px-4">No. Sales Order</th>
                        <th scope="col" class="py-3 px-4">Pelanggan</th>
                        <th scope="col" class="py-3 px-4">Tanggal Pesanan</th>
                        <th scope="col" class="py-3 px-4">Estimasi Kirim</th>
                        <th scope="col" class="py-3 px-4 text-right">Pajak</th>
                        <th scope="col" class="py-3 px-4 text-right">Nilai Pesanan</th>
                        <th scope="col" class="py-3 px-4 text-center">Status Pemenuhan</th>
                        <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($salesOrders as $so)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold">
                            <a href="{{ route('sales.orders.show', $so) }}"
                                class="text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-cyan-500 rounded">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 shrink-0" aria-hidden="true"></i>
                                <span>{{ $so->so_number }}</span>
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900 dark:text-white">
                                {{ $so->customer->name }}
                            </div>
                            @if($so->customer->company)
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $so->customer->company }}
                                </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $so->order_date->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $so->expected_delivery_date ? $so->expected_delivery_date->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-amber-600 dark:text-amber-400 whitespace-nowrap">
                            <div>Rp {{ number_format($so->tax_amount, 0, ',', '.') }}</div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">({{ number_format($so->tax_percentage, 1) }}%)</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white text-sm whitespace-nowrap">
                            Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($so->status === 'fulfilled')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Selesai</span>
                                </span>
                            @elseif($so->status === 'partially_fulfilled')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Sebagian</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-cyan-50 dark:bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                                    <span>Terkonfirmasi</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('sales.orders.show', $so) }}"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs transition flex items-center gap-1">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-14 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                                    <i data-lucide="package-search" class="w-6 h-6" aria-hidden="true"></i>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum ada Pesanan Penjualan (Sales Order)</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm">
                                        Mulai catat pesanan pelanggan untuk mengunci stok, memantau pengiriman, dan menerbitkan tagihan.
                                    </p>
                                </div>
                                <a href="{{ route('sales.orders.create') }}"
                                    class="mt-1 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                                    <i data-lucide="plus" class="w-4 h-4" aria-hidden="true"></i>
                                    <span>Buat Pesanan Pertama</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($salesOrders->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $salesOrders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
