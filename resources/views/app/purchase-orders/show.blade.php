@extends('layouts.app', [
    'title' => "Pesanan {$purchaseOrder->po_number}",
    'headerTitle' => "Purchase Order: {$purchaseOrder->po_number}",
    'headerSubtitle' => "Detail dokumen pesanan " . ($purchaseOrder->po_type === 'customer' ? 'dari pelanggan' : 'ke pemasok')
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
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-[#007AFF] transition-colors">Purchase Order</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium tabular-nums">{{ $purchaseOrder->po_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight tabular-nums">
                    {{ $purchaseOrder->po_number }}
                </h1>
                @php
                    $statusConfig = match($purchaseOrder->status) {
                        'draft' => ['bg' => 'bg-black/5 dark:bg-white/8', 'text' => 'text-black/60 dark:text-white/60', 'dot' => 'bg-black/40 dark:bg-white/40', 'label' => 'Draft'],
                        'confirmed' => ['bg' => 'bg-[#007AFF]/12', 'text' => 'text-[#007AFF]', 'dot' => 'bg-[#007AFF]', 'label' => 'Dikonfirmasi'],
                        'partially_invoiced' => ['bg' => 'bg-[#FF9500]/12', 'text' => 'text-[#B25E00] dark:text-[#FF9F0A]', 'dot' => 'bg-[#FF9500]', 'label' => 'Faktur Parsial'],
                        'fully_invoiced' => ['bg' => 'bg-[#34C759]/12', 'text' => 'text-[#248A3D] dark:text-[#30D158]', 'dot' => 'bg-[#34C759]', 'label' => 'Faktur Lengkap'],
                        'completed' => ['bg' => 'bg-[#34C759]/12', 'text' => 'text-[#248A3D] dark:text-[#30D158]', 'dot' => 'bg-[#34C759]', 'label' => 'Selesai'],
                        'cancelled' => ['bg' => 'bg-[#FF3B30]/12', 'text' => 'text-[#C41E17] dark:text-[#FF453A]', 'dot' => 'bg-[#FF3B30]', 'label' => 'Dibatalkan'],
                        default => ['bg' => 'bg-black/5 dark:bg-white/8', 'text' => 'text-black/60 dark:text-white/60', 'dot' => 'bg-black/40', 'label' => ucfirst($purchaseOrder->status)],
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                    {{ $statusConfig['label'] }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                {{ $purchaseOrder->po_type === 'customer' ? 'Customer Purchase Order (Dari Pelanggan)' : 'Vendor Purchase Order (Ke Pemasok)' }}
                @if($purchaseOrder->reference_number)
                    · Ref Eksternal: <span class="tabular-nums font-medium text-black/70 dark:text-white/70">{{ $purchaseOrder->reference_number }}</span>
                @endif
            </p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('purchase-orders.index') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Daftar PO</span>
            </a>

            @if(\App\Support\Context::hasPermission('purchasing.manage') && $purchaseOrder->status === 'draft')
            <form method="POST" action="{{ route('purchase-orders.confirm', $purchaseOrder->id) }}">
                @csrf
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Konfirmasi PO</span>
                </button>
            </form>
            @endif

            @if(\App\Support\Context::hasPermission('invoices.create') && $purchaseOrder->po_type === 'customer' && $purchaseOrder->status !== 'fully_invoiced' && $purchaseOrder->status !== 'cancelled')
            <form method="POST" action="{{ route('purchase-orders.generate-invoice', $purchaseOrder->id) }}">
                @csrf
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                    <span>Generate Faktur Penjualan</span>
                </button>
            </form>
            @endif

            @if((\App\Support\Context::hasPermission('receiving.manage') || \App\Support\Context::hasPermission('purchasing.manage')) && $purchaseOrder->po_type === 'supplier' && in_array($purchaseOrder->status, ['confirmed', 'partially_invoiced'], true))
            <a href="{{ route('purchasing.receipts.create', $purchaseOrder->id) }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span>Terima Barang Fisik</span>
            </a>
            @endif

            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}?download=1" target="_blank" class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Download PDF</span>
            </a>

            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.072-1.263-2.95m0 0A9.97 9.97 0 012.25 9.75c0 .34.02.673.06 1m3.665.079a9.97 9.97 0 001.263 2.95m0 0c.24 1.076.673 2.072 1.263 2.95M6.72 13.829a9.97 9.97 0 011.263-2.95m0 0c.24-1.076.673-2.072 1.263-2.95m0 0A9.97 9.97 0 0112 7.5c.34 0 .673.02 1 .06m-1 0a9.97 9.97 0 00-1 0m1 0a9.97 9.97 0 012.95 1.263m0 0c1.076.24 2.072.673 2.95 1.263m-5.9-2.526a9.97 9.97 0 00-2.95-1.263" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h10.5a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 16.5v-7.5a2.25 2.25 0 012.25-2.25z" />
                </svg>
                <span>Cetak (A4)</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. MAIN DOCUMENT SHEET (Apple HIG Cockpit Container)  -->
    <!-- ===================================================== -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        
        <!-- Sheet Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-black/5 dark:border-white/10">
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                    {{ $purchaseOrder->po_type === 'customer' ? 'Customer Purchase Order' : 'Vendor Purchase Order' }}
                </span>
                <h2 class="text-[26px] font-bold text-black dark:text-white tabular-nums tracking-tight mt-0.5">
                    {{ $purchaseOrder->po_number }}
                </h2>
                @if($purchaseOrder->reference_number)
                    <div class="text-[13px] text-black/50 dark:text-white/50 mt-1">
                        Nomor Referensi Eksternal: <span class="font-medium tabular-nums text-black dark:text-white">{{ $purchaseOrder->reference_number }}</span>
                    </div>
                @endif
            </div>

            <div class="text-left sm:text-right space-y-1.5 self-start sm:self-auto">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                        Status: {{ $statusConfig['label'] }}
                    </span>
                </div>
                <div class="text-[12px] text-black/50 dark:text-white/50">
                    Tanggal Order: <span class="tabular-nums font-medium text-black dark:text-white">{{ $purchaseOrder->order_date?->translatedFormat('d F Y') }}</span>
                </div>
                @if($purchaseOrder->expected_delivery_date)
                    <div class="text-[12px] text-black/50 dark:text-white/50">
                        Target Pengiriman: <span class="tabular-nums font-medium text-black dark:text-white">{{ $purchaseOrder->expected_delivery_date?->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Party Information (2-Column Grouped Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
            <!-- Penerbit Pesanan -->
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 block">
                    Penerbit Dokumen:
                </span>
                <div class="text-[15px] font-semibold text-black dark:text-white">{{ $business->name }}</div>
                <div class="text-black/60 dark:text-white/60 leading-relaxed">{{ $business->address ?? 'Alamat Kantor Pusat' }}</div>
                <div class="text-black/50 dark:text-white/50 text-[12px] pt-1">
                    Mata Uang: <span class="font-medium text-black dark:text-white">{{ $business->currency_code }} ({{ $business->currency_symbol }})</span>
                </div>
            </div>

            <!-- Lawan Transaksi -->
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 block">
                    {{ $purchaseOrder->po_type === 'customer' ? 'Pelanggan / Pemesan:' : 'Pemasok / Vendor Dituju:' }}
                </span>
                @if($purchaseOrder->customer)
                    <div class="text-[15px] font-semibold text-black dark:text-white">{{ $purchaseOrder->customer->name }}</div>
                    @if($purchaseOrder->customer->company_name)
                        <div class="text-[#007AFF] font-medium text-[13px]">{{ $purchaseOrder->customer->company_name }}</div>
                    @endif
                    <div class="text-black/60 dark:text-white/60 leading-relaxed">{{ $purchaseOrder->customer->billing_address ?? '-' }}</div>
                    <div class="text-black/50 dark:text-white/50 text-[12px] tabular-nums pt-1">
                        Kontak: {{ $purchaseOrder->customer->phone ?? $purchaseOrder->customer->email ?? '-' }}
                    </div>
                @elseif($purchaseOrder->supplier)
                    <div class="text-[15px] font-semibold text-black dark:text-white">{{ $purchaseOrder->supplier->name }}</div>
                    <div class="text-black/60 dark:text-white/60">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</div>
                    <div class="text-black/60 dark:text-white/60 leading-relaxed">{{ $purchaseOrder->supplier->address ?? '-' }}</div>
                    <div class="text-black/50 dark:text-white/50 text-[12px] tabular-nums pt-1">
                        Kontak: {{ $purchaseOrder->supplier->phone ?? $purchaseOrder->supplier->email ?? '-' }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="rounded-[12px] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-12 text-center">No</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Deskripsi Item / Barang</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-24">Satuan</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-24">Qty</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Harga Satuan</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($purchaseOrder->items as $idx => $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3 px-3.5 text-center tabular-nums text-black/45 dark:text-white/45">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3.5">
                                <div class="font-medium text-black dark:text-white">{{ $item->item_name }}</div>
                                @if($item->sku)
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">SKU: {{ $item->sku }}</div>
                                @endif
                                @if($item->notes)
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center text-black/60 dark:text-white/60">
                                {{ $item->unit?->name ?? 'pcs' }}
                            </td>
                            <td class="py-3 px-3.5 text-right tabular-nums font-medium text-black dark:text-white">
                                {{ (float)$item->quantity == (int)$item->quantity ? number_format((float)$item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float)$item->quantity, 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-3 px-3.5 text-right tabular-nums text-black/70 dark:text-white/70">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3.5 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals & Notes Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
            <!-- Notes / Terms -->
            <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                @if($purchaseOrder->terms_and_conditions)
                <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3.5 border border-black/5 dark:border-white/5">
                    <span class="font-semibold text-black dark:text-white block mb-1">Syarat & Ketentuan:</span>
                    <p class="text-black/60 dark:text-white/60 whitespace-pre-line leading-relaxed">{{ $purchaseOrder->terms_and_conditions }}</p>
                </div>
                @endif

                @if($purchaseOrder->notes)
                <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3.5 border border-black/5 dark:border-white/5">
                    <span class="font-semibold text-black dark:text-white block mb-1">Catatan Dokumen:</span>
                    <p class="text-black/60 dark:text-white/60 leading-relaxed">{{ $purchaseOrder->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Financial Calculation Summary -->
            <div class="w-full sm:w-80 space-y-2.5 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">
                        {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->subtotal, 0, ',', '.') }}
                    </span>
                </div>

                @if($purchaseOrder->discount_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Potongan Diskon</span>
                    <span class="tabular-nums font-medium text-[#FF3B30] dark:text-[#FF453A]">
                        - {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->discount_amount, 0, ',', '.') }}
                    </span>
                </div>
                @endif

                @if($purchaseOrder->tax_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>PPN ({{ $purchaseOrder->tax_percentage }}%)</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">
                        + {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->tax_amount, 0, ',', '.') }}
                    </span>
                </div>
                @endif

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex justify-between items-center">
                    <span class="text-[14px] font-semibold text-black dark:text-white">Total Nilai Pesanan</span>
                    <span class="text-[20px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                        {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->total_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Invoices Link Section (If generated) -->
        @if($purchaseOrder->invoices->isNotEmpty())
        <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 p-4 space-y-2.5">
            <div class="text-[13px] font-semibold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Faktur Penjualan yang Telah Diterbitkan:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($purchaseOrder->invoices as $inv)
                <a href="{{ route('invoices.show', $inv->id) }}" class="h-8 px-3 rounded-[8px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[12px] font-medium border border-black/5 dark:border-white/10 hover:border-[#007AFF] transition-colors flex items-center gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <span class="tabular-nums font-semibold">{{ $inv->invoice_number }}</span>
                    <span class="text-[11px] text-black/50 dark:text-white/50 uppercase font-semibold">({{ $inv->status }})</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
