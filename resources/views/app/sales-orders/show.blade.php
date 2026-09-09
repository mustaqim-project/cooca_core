@extends('layouts.app', [
    'title' => 'Pesanan Penjualan ' . $salesOrder->so_number . ' — Cooca UMKM',
    'headerTitle' => 'Detail Pesanan Penjualan',
    'headerSubtitle' => 'Kelola status pemenuhan pesanan, verifikasi rincian barang, dan terbitkan faktur penagihan.'
])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Standard Breadcrumb & Action Bar -->
    <nav class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800/80 print:hidden" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <li>
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-cyan-500 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <a href="{{ route('sales.orders.index') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors focus-visible:ring-2 focus-visible:ring-cyan-500 rounded px-1">
                    Sales Orders
                </a>
            </li>
            <li class="text-slate-400 dark:text-slate-600" aria-hidden="true">/</li>
            <li>
                <span class="text-slate-900 dark:text-slate-200 font-mono font-bold" aria-current="page">{{ $salesOrder->so_number }}</span>
            </li>
        </ol>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1-Click Generate Invoice Button -->
            @if($salesOrder->status !== 'fulfilled')
            <form action="{{ route('sales.orders.generate-invoice', $salesOrder) }}" method="POST"
                onsubmit="return AppAlert.confirmSubmit(event, this, 'Terbitkan Faktur Tagihan (Invoice) resmi dari pesanan penjualan ini?', 'Terbitkan Invoice?', 'info')">
                @csrf
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm shadow-cyan-600/20 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="receipt" class="w-4 h-4" aria-hidden="true"></i>
                    <span>Terbitkan Faktur Tagihan (Invoice)</span>
                </button>
            </form>
            @endif

            <!-- Print -->
            <button onclick="window.print()"
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4" aria-hidden="true"></i>
                <span>Cetak</span>
            </button>

            <!-- Back -->
            <a href="{{ route('sales.orders.index') }}"
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4" aria-hidden="true"></i>
                <span>Kembali</span>
            </a>
        </div>
    </nav>

    <!-- Top Status Banner -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="file-check-2" class="w-5 h-5" aria-hidden="true"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-mono">{{ $salesOrder->so_number }}</h2>
                    @if($salesOrder->status === 'fulfilled')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Selesai (Fulfilled)</span>
                        </span>
                    @elseif($salesOrder->status === 'partially_fulfilled')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>Sebagian Terpenuhi</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-cyan-50 dark:bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                            <span>Terkonfirmasi (Confirmed)</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Diterbitkan tanggal {{ $salesOrder->order_date->format('d M Y') }} &bull; Pelanggan: <strong class="text-slate-800 dark:text-slate-200">{{ $salesOrder->customer->name }}</strong>
                </p>
            </div>
        </div>

        <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100 dark:border-slate-800">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Nilai Pesanan</span>
            <div class="text-xl sm:text-2xl font-black font-mono text-cyan-600 dark:text-cyan-400">
                Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Linked Quotation / Invoices Notification -->
    @if($salesOrder->quotation || $salesOrder->invoices->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 print:hidden">
        @if($salesOrder->quotation)
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs flex items-center justify-between text-xs">
            <div class="space-y-0.5">
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Berasal dari Penawaran:</span>
                <p class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $salesOrder->quotation->quotation_number }}</p>
            </div>
            <a href="{{ route('sales.quotations.show', $salesOrder->quotation) }}"
                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold transition">
                Lihat Penawaran
            </a>
        </div>
        @endif

        @if($salesOrder->invoices->count() > 0)
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs flex items-center justify-between text-xs">
            <div class="space-y-0.5">
                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Faktur Tagihan Terbit:</span>
                <p class="font-mono font-bold text-slate-900 dark:text-white">{{ $salesOrder->invoices->first()->invoice_number }}</p>
            </div>
            <a href="{{ route('invoices.show', $salesOrder->invoices->first()) }}"
                class="px-3 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 dark:bg-cyan-500/10 dark:hover:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 font-semibold transition">
                Buka Faktur
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Printable Official Sales Order Document Card -->
    <div class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-8 print:p-0 print:border-none print:shadow-none">
        <!-- Letterhead Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-200 dark:border-slate-800 pb-6">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    @if($business->logo_url)
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="h-8 w-auto object-contain">
                    @endif
                    <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $business->name }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $business->address ?? 'Alamat operasional bisnis' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $business->phone ?? '' }}</p>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <h2 class="text-xl sm:text-2xl font-black text-cyan-600 dark:text-cyan-400 tracking-wider">PESANAN PENJUALAN</h2>
                <p class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $salesOrder->so_number }}</p>
                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5 pt-1">
                    <p>Tanggal Pesanan: <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $salesOrder->order_date->format('d M Y') }}</span></p>
                    @if($salesOrder->expected_delivery_date)
                    <p>Estimasi Kirim: <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $salesOrder->expected_delivery_date->format('d M Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer & Shipping Addresses -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PEMBELI / PELANGGAN:</span>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $salesOrder->customer->name }}</h3>
                @if($salesOrder->customer->company)
                    <p class="text-xs text-slate-600 dark:text-slate-400">{{ $salesOrder->customer->company }}</p>
                @endif
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $salesOrder->customer->phone }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $salesOrder->customer->address }}</p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">ALAMAT PENGIRIMAN:</span>
                @if($salesOrder->shipping_address)
                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $salesOrder->shipping_address }}</p>
                @else
                    <p class="text-xs text-slate-400 italic">Sesuai alamat profil pelanggan utama</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 whitespace-nowrap">
                    <tr>
                        <th scope="col" class="py-2.5 px-3 w-10 text-center">No.</th>
                        <th scope="col" class="py-2.5 px-3">Deskripsi Produk</th>
                        <th scope="col" class="py-2.5 px-3 text-right">Harga Satuan</th>
                        <th scope="col" class="py-2.5 px-3 text-right">Kuantitas</th>
                        <th scope="col" class="py-2.5 px-3 text-right">Terpenuhi</th>
                        <th scope="col" class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                    @foreach($salesOrder->items as $idx => $item)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3 font-sans font-medium text-slate-900 dark:text-white">
                            {{ $item->product_name }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-900 dark:text-white font-bold">
                            {{ (float) $item->quantity }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-cyan-600 dark:text-cyan-400">
                            {{ (float) $item->fulfilled_quantity }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notes & Financial Breakdown -->
        <div class="pt-2 flex flex-col sm:flex-row justify-between items-start gap-6 font-sans">
            <!-- Order Notes -->
            <div class="w-full sm:w-1/2 space-y-2">
                @if($salesOrder->notes)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">CATATAN PESANAN:</span>
                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $salesOrder->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Financial Summary Box -->
            <div class="w-full sm:w-80 p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-2.5 text-xs font-mono">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span class="font-sans">Subtotal:</span>
                    <span class="font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($salesOrder->items->sum('subtotal'), 0, ',', '.') }}
                    </span>
                </div>

                @if($salesOrder->discount_amount > 0)
                <div class="flex justify-between text-rose-600 dark:text-rose-400">
                    <span class="font-sans">Diskon Pesanan:</span>
                    <span>- Rp {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($salesOrder->tax_amount > 0)
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span class="font-sans">Pajak ({{ number_format($salesOrder->tax_percentage, 1) }}%):</span>
                    <span class="text-amber-600 dark:text-amber-400">+ Rp {{ number_format($salesOrder->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center text-sm font-black text-slate-900 dark:text-white">
                    <span class="font-sans">Total Tagihan:</span>
                    <span class="text-cyan-600 dark:text-cyan-400 text-lg">Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Signature Authorization Block for Print -->
        <div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-slate-600">
            <div class="space-y-12">
                <p class="font-semibold">Dibuat Oleh,</p>
                <p class="border-t border-slate-300 pt-1">Bagian Penjualan</p>
            </div>
            <div class="space-y-12">
                <p class="font-semibold">Disetujui Oleh,</p>
                <p class="border-t border-slate-300 pt-1">Supervisor / Manajer</p>
            </div>
            <div class="space-y-12">
                <p class="font-semibold">Diterima Pelanggan,</p>
                <p class="border-t border-slate-300 pt-1">{{ $salesOrder->customer->name }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
