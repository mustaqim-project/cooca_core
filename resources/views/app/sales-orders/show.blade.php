@extends('layouts.app', [
    'title' => 'Pesanan Penjualan ' . $salesOrder->so_number . ' — Cooca UMKM',
    'headerTitle' => 'Detail Pesanan Penjualan',
    'headerSubtitle' => 'Kelola status pemenuhan pesanan, verifikasi rincian barang, dan terbitkan faktur penagihan.'
])

@section('content')
<div class="max-w-[1100px] mx-auto space-y-6 pb-12" x-data="{
    invoiceModalOpen: false,
    promptInvoice() {
        this.invoiceModalOpen = true;
    },
    submitInvoice() {
        this.invoiceModalOpen = false;
        document.getElementById('form-generate-invoice').submit();
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('sales.orders.index') }}" class="hover:text-[#007AFF] transition-colors">Sales Orders</a>
                <span>›</span>
                <span class="text-black dark:text-white font-mono font-medium">{{ $salesOrder->so_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight font-mono">{{ $salesOrder->so_number }}</h1>
                @if($salesOrder->status === 'fulfilled')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Selesai
                    </span>
                @elseif($salesOrder->status === 'partially_fulfilled')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Sebagian
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> Terkonfirmasi
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1-Click Generate Invoice Button -->
            @if($salesOrder->status !== 'fulfilled')
                <button type="button" @click="promptInvoice()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span>Terbitkan Faktur</span>
                </button>

                <form id="form-generate-invoice" action="{{ route('sales.orders.generate-invoice', $salesOrder) }}" method="POST" class="hidden">
                    @csrf
                </form>
            @endif

            <!-- Print Button -->
            <button type="button" onclick="window.print()"
                class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                <span>Cetak</span>
            </button>

            <!-- Back Link -->
            <a href="{{ route('sales.orders.index') }}"
                class="h-9 px-3 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center">
                <span>Kembali</span>
            </a>
        </div>
    </header>

    <!-- Top Summary Banner -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <span class="text-[12px] font-medium text-black/45 dark:text-white/45">Pelanggan Pemesan</span>
            <h2 class="text-[17px] font-semibold text-black dark:text-white mt-0.5">{{ $salesOrder->customer->name }}</h2>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                Diterbitkan pada {{ $salesOrder->order_date->format('d M Y') }} &bull; Estimasi Kirim: {{ $salesOrder->expected_delivery_date ? $salesOrder->expected_delivery_date->format('d M Y') : 'Sesuai kesepakatan' }}
            </p>
        </div>

        <div class="text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-black/5 dark:border-white/5">
            <span class="text-[12px] font-medium text-black/45 dark:text-white/45">Total Nilai Pesanan</span>
            <div class="text-[22px] sm:text-[26px] font-bold tabular-nums text-[#007AFF] mt-0.5">
                Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Linked Quotation / Invoices Callouts -->
    @if($salesOrder->quotation || $salesOrder->invoices->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 print:hidden">
        @if($salesOrder->quotation)
        <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 flex items-center justify-between text-[13px]">
            <div>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Berasal dari Penawaran (Quotation):</span>
                <p class="font-semibold tabular-nums text-black dark:text-white">{{ $salesOrder->quotation->quotation_number }}</p>
            </div>
            <a href="{{ route('sales.quotations.show', $salesOrder->quotation) }}"
                class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center">
                Lihat Penawaran
            </a>
        </div>
        @endif

        @if($salesOrder->invoices->count() > 0)
        <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 flex items-center justify-between text-[13px]">
            <div>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Faktur Tagihan Resmi Terbit:</span>
                <p class="font-semibold tabular-nums text-[#34C759]">{{ $salesOrder->invoices->first()->invoice_number }}</p>
            </div>
            <a href="{{ route('invoices.show', $salesOrder->invoices->first()) }}"
                class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/15 transition flex items-center">
                Buka Faktur
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- OFFICIAL PRINTABLE SALES ORDER DOCUMENT CARD           -->
    <!-- ===================================================== -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 print:p-0 print:border-none print:shadow-none">
        <!-- Letterhead Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-black/5 dark:border-white/10 pb-6">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    @if($business->logo_url)
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="h-9 w-auto object-contain">
                    @endif
                    <span class="text-[20px] font-bold text-black dark:text-white tracking-tight">{{ $business->name }}</span>
                </div>
                <p class="text-[13px] text-black/55 dark:text-white/55 leading-relaxed">{{ $business->address ?? 'Alamat operasional bisnis' }}</p>
                <p class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ $business->phone ?? '' }}</p>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <h2 class="text-[18px] font-bold uppercase tracking-wider text-[#007AFF]">PESANAN PENJUALAN</h2>
                <p class="text-[15px] font-semibold tabular-nums text-black dark:text-white">{{ $salesOrder->so_number }}</p>
                <div class="text-[12px] text-black/50 dark:text-white/50 space-y-0.5 pt-1">
                    <p>Tanggal Pesanan: <span class="font-medium tabular-nums text-black dark:text-white">{{ $salesOrder->order_date->format('d M Y') }}</span></p>
                    @if($salesOrder->expected_delivery_date)
                    <p>Estimasi Kirim: <span class="font-medium tabular-nums text-black dark:text-white">{{ $salesOrder->expected_delivery_date->format('d M Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Addresses Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1 text-[13px]">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">PEMBELI / PELANGGAN:</span>
                <h3 class="font-semibold text-black dark:text-white text-[15px]">{{ $salesOrder->customer->name }}</h3>
                @if($salesOrder->customer->company)
                    <p class="text-black/60 dark:text-white/60">{{ $salesOrder->customer->company }}</p>
                @endif
                <p class="text-black/50 dark:text-white/50 tabular-nums">{{ $salesOrder->customer->phone }}</p>
                <p class="text-black/55 dark:text-white/55">{{ $salesOrder->customer->address }}</p>
            </div>

            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1 text-[13px]">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">ALAMAT PENGIRIMAN:</span>
                @if($salesOrder->shipping_address)
                    <p class="text-black/70 dark:text-white/70 leading-relaxed">{{ $salesOrder->shipping_address }}</p>
                @else
                    <p class="text-black/40 dark:text-white/40 italic">Sesuai alamat pelanggan utama</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto rounded-[10px] border border-black/5 dark:border-white/10">
            <table class="w-full text-left text-[13px] min-w-[650px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                        <th class="py-2.5 px-3 w-10 text-center">No.</th>
                        <th class="py-2.5 px-3">Deskripsi Produk</th>
                        <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-3 text-right">Kuantitas</th>
                        <th class="py-2.5 px-3 text-right">Terpenuhi</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach($salesOrder->items as $idx => $item)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-3 text-center tabular-nums text-black/40 dark:text-white/40">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3 font-medium text-black dark:text-white">
                            {{ $item->product_name }}
                        </td>
                        <td class="py-3 px-3 text-right tabular-nums text-black/60 dark:text-white/60">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            {{ (float) $item->quantity }}
                        </td>
                        <td class="py-3 px-3 text-right tabular-nums font-semibold text-[#007AFF]">
                            {{ (float) $item->fulfilled_quantity }}
                        </td>
                        <td class="py-3 px-3 text-right tabular-nums font-semibold text-black dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notes & Financial Breakdown -->
        <div class="pt-2 flex flex-col sm:flex-row justify-between items-start gap-6">
            <div class="w-full sm:w-1/2 space-y-2">
                @if($salesOrder->notes)
                <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1 text-[13px]">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">CATATAN PESANAN:</span>
                    <p class="text-black/70 dark:text-white/70 leading-relaxed">{{ $salesOrder->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Financial Summary Box -->
            <div class="w-full sm:w-80 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-2.5 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal:</span>
                    <span class="font-semibold tabular-nums text-black dark:text-white">
                        Rp {{ number_format($salesOrder->items->sum('subtotal'), 0, ',', '.') }}
                    </span>
                </div>

                @if($salesOrder->discount_amount > 0)
                <div class="flex justify-between text-[#FF3B30]">
                    <span>Diskon Pesanan:</span>
                    <span class="tabular-nums">- Rp {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($salesOrder->tax_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Pajak PPN ({{ number_format($salesOrder->tax_percentage, 1) }}%):</span>
                    <span class="tabular-nums text-[#34C759]">+ Rp {{ number_format($salesOrder->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="pt-2.5 border-t border-black/5 dark:border-white/10 flex justify-between items-baseline">
                    <span class="font-semibold text-black dark:text-white">Total Nilai Tagihan:</span>
                    <span class="text-[18px] font-bold tabular-nums text-[#007AFF]">Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Signature Authorization Block for Print -->
        <div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">
            <div class="space-y-12">
                <p class="font-semibold">Dibuat Oleh,</p>
                <p class="border-t border-black/30 pt-1">Bagian Penjualan</p>
            </div>
            <div class="space-y-12">
                <p class="font-semibold">Disetujui Oleh,</p>
                <p class="border-t border-black/30 pt-1">Supervisor / Manajer</p>
            </div>
            <div class="space-y-12">
                <p class="font-semibold">Diterima Pelanggan,</p>
                <p class="border-t border-black/30 pt-1">{{ $salesOrder->customer->name }}</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG: TERBITKAN FAKTUR TAGIHAN          -->
    <!-- ===================================================== -->
    <div x-show="invoiceModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="invoiceModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Terbitkan Faktur?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Terbitkan Faktur Tagihan (Invoice) resmi dari pesanan penjualan ini?
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="invoiceModalOpen = false" class="py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitInvoice()" class="py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Terbitkan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
