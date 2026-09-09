@extends('layouts.app', [
    'title' => 'Retur Penjualan — Cooca UMKM',
    'headerTitle' => 'Retur Penjualan & Pengembalian Barang',
    'headerSubtitle' => 'Kelola pengembalian barang pelanggan atas faktur penjualan atau kasir POS, penerbitan credit note, dan pengembalian stok gudang.'
])

@section('content')
@php
    $totalReturnsCount = $returns->total();
    $draftReturns = $returns->filter(fn($r) => $r->status === 'draft');
    $approvedReturns = $returns->filter(fn($r) => $r->status === 'approved');
    $completedReturns = $returns->filter(fn($r) => $r->status === 'completed');
    $pageTotalAmount = $returns->sum('total_amount');
@endphp

<div class="space-y-6">

    <!-- Standard Breadcrumb & Executive Page Header -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800/80" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-rose-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-700 dark:text-slate-300">Kasir &amp; Penjualan</span>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-900 dark:text-slate-200 font-semibold" aria-current="page">Retur Penjualan</span>
            </li>
            <li class="hidden sm:inline text-slate-400 dark:text-slate-600" aria-hidden="true">•</li>
            <li class="hidden sm:inline">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20">
                    Sales Returns
                </span>
            </li>
        </ol>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('sales.returns.create') }}"
                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-sm shadow-rose-600/20 hover:shadow-rose-600/30 transition-all flex items-center gap-2 focus-visible:ring-2 focus-visible:ring-rose-500">
                <i data-lucide="plus" class="w-4 h-4" aria-hidden="true"></i>
                <span>Buat Retur Penjualan</span>
            </a>
        </div>
    </nav>

    <!-- 4-Pillar Executive KPI Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Nilai Retur -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nilai Retur (Halaman)</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
                Rp {{ number_format($pageTotalAmount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Total kompensasi tercatat</p>
        </div>

        <!-- 2. Total Transaksi -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Retur</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="undo-2" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ number_format($totalReturnsCount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Frekuensi pengembalian</p>
        </div>

        <!-- 3. Draft Menunggu Approval -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu Persetujuan</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-amber-600 dark:text-amber-400">
                {{ $draftReturns->count() }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Draft retur baru</p>
        </div>

        <!-- 4. Selesai (Completed & Restocked) -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-1.5 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Selesai &amp; Restock</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="package-check" class="w-4 h-4" aria-hidden="true"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                {{ $completedReturns->count() }}
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Stok &amp; dana terselesaikan</p>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2.5">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" aria-hidden="true"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Retur List Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800 whitespace-nowrap">
                    <tr>
                        <th scope="col" class="py-3 px-4">No. Retur</th>
                        <th scope="col" class="py-3 px-4">Referensi Transaksi</th>
                        <th scope="col" class="py-3 px-4">Pelanggan</th>
                        <th scope="col" class="py-3 px-4">Tanggal Retur</th>
                        <th scope="col" class="py-3 px-4">Metode Kompensasi</th>
                        <th scope="col" class="py-3 px-4 text-right">Nilai Retur</th>
                        <th scope="col" class="py-3 px-4 text-center">Status</th>
                        <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($returns as $return)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <!-- No Retur -->
                        <td class="py-3.5 px-4 font-mono font-bold whitespace-nowrap">
                            <a href="{{ route('sales.returns.show', $return) }}"
                                class="text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-rose-500 rounded">
                                <i data-lucide="undo-2" class="w-3.5 h-3.5 shrink-0" aria-hidden="true"></i>
                                <span>{{ $return->return_number }}</span>
                            </a>
                        </td>

                        <!-- Referensi Transaksi -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($return->invoice)
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/30">INV</span>
                                    <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $return->invoice->invoice_number }}</span>
                                </div>
                            @elseif($return->posOrder)
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-500/30">POS</span>
                                    <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $return->posOrder->order_number }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Pelanggan -->
                        <td class="py-3.5 px-4 font-medium text-slate-900 dark:text-white">
                            @if($return->invoice)
                                {{ $return->invoice->customer?->name ?? 'Pelanggan Umum' }}
                            @elseif($return->posOrder)
                                {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: 'Tamu Kasir') }}
                            @else
                                {{ $return->customer?->name ?? 'Pelanggan Umum' }}
                            @endif
                        </td>

                        <!-- Tanggal -->
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap font-mono">
                            {{ $return->return_date ? $return->return_date->format('d/m/Y') : $return->created_at->format('d/m/Y') }}
                        </td>

                        <!-- Metode Refund -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ str_replace('_', ' ', $return->refund_method ?? 'cash_refund') }}
                            </span>
                        </td>

                        <!-- Nilai Retur -->
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($return->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Selesai</span>
                                </span>
                            @elseif($return->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span>Disetujui</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Draft</span>
                                </span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('sales.returns.show', $return) }}"
                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs transition inline-flex items-center gap-1">
                                <span>Detail</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-14 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                    <i data-lucide="undo-2" class="w-6 h-6" aria-hidden="true"></i>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum Ada Transaksi Retur Penjualan</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm">
                                        Seluruh pengembalian barang dari kasir POS atau faktur B2B akan tercatat rapi di sini untuk pemulihan stok dan rekonsiliasi kas.
                                    </p>
                                </div>
                                <a href="{{ route('sales.returns.create') }}"
                                    class="mt-1 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                                    <i data-lucide="plus" class="w-4 h-4" aria-hidden="true"></i>
                                    <span>Buat Retur Pertama</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
