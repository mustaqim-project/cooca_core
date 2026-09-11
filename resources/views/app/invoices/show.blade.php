@extends('layouts.app', [
    'title' => "Faktur {$invoice->invoice_number}",
    'headerTitle' => "Faktur Penjualan: {$invoice->invoice_number}",
    'headerSubtitle' => "Detail penagihan komersial dan pencatatan pembayaran"
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showPaymentModal: false,
    confirmReleaseModalOpen: false,
    confirmVoidModalOpen: false,
    submitRelease() {
        document.getElementById('form-confirm-release').submit();
    },
    submitVoid() {
        document.getElementById('form-confirm-void').submit();
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Minimal Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('invoices.index') }}" class="hover:text-[#007AFF] transition-colors">Faktur Penjualan</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium tabular-nums">{{ $invoice->invoice_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight tabular-nums">
                    Faktur {{ $invoice->invoice_number }}
                </h1>
                @php
                    $statusTints = [
                        'draft' => 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55',
                        'sent' => 'bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]',
                        'unpaid' => 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]',
                        'partially_paid' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                        'paid' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                        'overdue' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                        'void' => 'bg-black/6 dark:bg-white/8 text-black/40 dark:text-white/40 line-through',
                    ];
                    $dotColors = [
                        'draft' => 'bg-black/40 dark:bg-white/40',
                        'sent' => 'bg-[#5856D6]',
                        'unpaid' => 'bg-[#007AFF]',
                        'partially_paid' => 'bg-[#FF9500]',
                        'paid' => 'bg-[#34C759]',
                        'overdue' => 'bg-[#FF3B30]',
                        'void' => 'bg-black/40 dark:bg-white/40',
                    ];
                    $statusLabel = [
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'unpaid' => 'Menunggu Pembayaran',
                        'partially_paid' => 'Sebagian Dibayar',
                        'paid' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        'void' => 'Dibatalkan (Void)',
                    ];
                    $tintClass = $statusTints[$invoice->status] ?? $statusTints['draft'];
                    $dotClass = $dotColors[$invoice->status] ?? $dotColors['draft'];
                    $currentLabel = $statusLabel[$invoice->status] ?? ucfirst(str_replace('_', ' ', $invoice->status));
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $tintClass }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                    {{ $currentLabel }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                {{ $invoice->customer?->name ?? 'Pelanggan' }} · Diterbitkan {{ $invoice->invoice_date?->translatedFormat('d F Y') }}
            </p>
        </div>

        <!-- Toolbar Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            <a href="{{ route('invoices.index') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Daftar</span>
            </a>

            {{-- Tombol Konfirmasi & Rilis Faktur (hanya tampil saat draft) --}}
            @if($invoice->status === 'draft' && (\App\Support\Context::hasPermission('invoices.create') || \App\Support\Context::hasPermission('invoices.edit')))
            <button type="button" @click="confirmReleaseModalOpen = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB350] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
                <span>Konfirmasi &amp; Rilis</span>
            </button>
            <form id="form-confirm-release" method="POST" action="{{ route('invoices.confirm', $invoice->id) }}" class="hidden">
                @csrf
            </form>
            @endif

            {{-- Tombol Catat Pembayaran (hanya saat ada sisa tagihan dan bukan void) --}}
            @if($invoice->balance_due > 0 && $invoice->status !== 'void' && $invoice->status !== 'draft' && \App\Support\Context::hasPermission('invoices.record_payment'))
            <button type="button" @click="showPaymentModal = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v10.5m0-10.5h19.5m0 0v10.5m0-10.5a.75.75 0 00-.75-.75h-.75m0 0a60.06 60.06 0 00-15.797-2.101C3.226 2.052 2.5 2.592 2.5 3.346V4.5" />
                </svg>
                <span>Catat Pembayaran</span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('invoices.export') || \App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('invoices.print', $invoice->id) }}?download=1" target="_blank" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh PDF</span>
            </a>
            @endif

            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                <span>Cetak (A4)</span>
            </a>

            {{-- Tombol Void (hanya tampil saat bukan draft/void dan belum ada pembayaran) --}}
            @if(!in_array($invoice->status, ['draft','void']) && $invoice->paid_amount == 0 && (\App\Support\Context::hasPermission('invoices.delete') || \App\Support\Context::hasPermission('invoices.edit')))
            <button type="button" @click="confirmVoidModalOpen = true" class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                <span>Batalkan Faktur</span>
            </button>
            <form id="form-confirm-void" method="POST" action="{{ route('invoices.void', $invoice->id) }}" class="hidden">
                @csrf
            </form>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. MAIN INVOICE DOCUMENT CARD (Apple Material & Depth) -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-8 space-y-6">

        <!-- Document Top Info -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-black/5 dark:border-white/10">
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#007AFF]">Faktur Penjualan Komersial</span>
                <h2 class="text-[26px] font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                    {{ $invoice->invoice_number }}
                </h2>
                @if($invoice->purchaseOrder)
                    <div class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mt-1">
                        <span>Berdasarkan PO:</span>
                        <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoice->purchaseOrder->po_number }}</span>
                    </div>
                @endif
            </div>

            <div class="text-left sm:text-right space-y-1 self-start sm:self-auto">
                <div class="text-[13px] text-black/50 dark:text-white/50">
                    Tanggal Faktur: <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoice->invoice_date?->translatedFormat('d F Y') }}</span>
                </div>
                <div class="text-[13px] text-black/50 dark:text-white/50">
                    Jatuh Tempo: <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoice->due_date?->translatedFormat('d F Y') }}</span>
                </div>
                @if($invoice->payment_terms)
                    <div class="text-[12px] text-black/45 dark:text-white/45">
                        Termin: <span class="font-medium text-black/70 dark:text-white/70">{{ $invoice->payment_terms }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Party Information (Two Grouped Inset Blocks) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
            <!-- Seller Info -->
            <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pihak Penjual</span>
                <div class="text-[15px] font-semibold text-black dark:text-white mt-1">{{ $business->name }}</div>
                <p class="text-black/60 dark:text-white/60 leading-snug">{{ $business->address ?? 'Alamat Kantor Pusat Bisnis' }}</p>
                <div class="text-black/50 dark:text-white/50 text-[12px] pt-1">
                    Mata Uang: <span class="font-medium text-black/70 dark:text-white/70">{{ $business->currency_code }} ({{ $business->currency_symbol }})</span>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Ditagihkan Kepada</span>
                <div class="text-[15px] font-semibold text-black dark:text-white mt-1">{{ $invoice->customer?->name ?? 'Pelanggan' }}</div>
                @if($invoice->customer?->company_name)
                    <div class="text-[12px] font-medium text-[#007AFF]">{{ $invoice->customer->company_name }}</div>
                @endif
                <p class="text-black/60 dark:text-white/60 leading-snug">{{ $invoice->customer?->billing_address ?? '-' }}</p>
                <div class="text-black/50 dark:text-white/50 text-[12px] pt-1 flex flex-wrap gap-x-3">
                    <span>Kontak: <span class="tabular-nums text-black/70 dark:text-white/70">{{ $invoice->customer?->phone ?? $invoice->customer?->email ?? '-' }}</span></span>
                    @if($invoice->customer?->tax_identification_number)
                        <span>NPWP: <span class="tabular-nums text-black/70 dark:text-white/70">{{ $invoice->customer->tax_identification_number }}</span></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gudang Asal Pengeluaran Barang --}}
        @if($invoice->location)
        <div class="flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55 px-3 py-2 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
            <svg class="w-4 h-4 text-[#007AFF] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009 9c.896 0 1.706-.395 2.25-1.02a2.993 2.993 0 002.25 1.02c.896 0 1.706-.395 2.25-1.02a3.001 3.001 0 003.75.614m-16.5 0L6 3.75h12l2.25 5.6" />
            </svg>
            <span>Pengeluaran inventori dari:</span>
            <span class="font-medium text-black dark:text-white">{{ $invoice->location->name }}</span>
            @if($invoice->location->type)
                <span class="text-black/40 dark:text-white/40">({{ $invoice->location->type }})</span>
            @endif
        </div>
        @endif

        <!-- Items Table (Desktop & Tablet Dense List) -->
        <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] whitespace-nowrap">
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-12 text-center">No</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Item Produk &amp; Deskripsi</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-24">Satuan</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-20">Qty</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-32">Harga Satuan</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-[#FF9500] dark:text-[#FF9F0A] text-right w-28">HPP Modal</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($invoice->items as $idx => $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3 px-4 text-center tabular-nums text-black/40 dark:text-white/40">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-black dark:text-white">{{ $item->item_name }}</div>
                                @if($item->sku)
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">SKU: {{ $item->sku }}</div>
                                @endif
                                @if($item->description)
                                    <div class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-black/60 dark:text-white/60">
                                {{ $item->unit?->name ?? 'pcs' }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ (float)$item->quantity == (int)$item->quantity ? number_format((float)$item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float)$item->quantity, 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->unit_hpp, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary Breakdown & Margin -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
            <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                <!-- Profit Analysis Card (Apple System Green Tint) -->
                <div class="p-4 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 space-y-1.5">
                    <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-semibold uppercase tracking-wider flex items-center justify-between">
                        <span>Laba Kotor Riil Transaksi</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold tabular-nums">
                            {{ $invoice->subtotal > 0 ? round(($invoice->total_gross_profit / $invoice->subtotal) * 100) : 0 }}% Margin
                        </span>
                    </div>
                    <div class="text-[22px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums">
                        {{ $business->currency_symbol }} {{ number_format((float)$invoice->total_gross_profit, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-black/50 dark:text-white/50">
                        Penjualan: <span class="tabular-nums">{{ $business->currency_symbol }} {{ number_format((float)$invoice->subtotal, 0, ',', '.') }}</span> · HPP Modal: <span class="tabular-nums">{{ $business->currency_symbol }} {{ number_format((float)$invoice->total_hpp_cost, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wide mb-0.5">Catatan Faktur:</div>
                    <div class="text-black/70 dark:text-white/70">{{ $invoice->notes }}</div>
                </div>
                @endif

                @if($invoice->terms_conditions)
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wide mb-0.5">Ketentuan Pembayaran:</div>
                    <div class="text-black/70 dark:text-white/70 whitespace-pre-line">{{ $invoice->terms_conditions }}</div>
                </div>
                @endif
            </div>

            <!-- Financial Calculation Totals -->
            <div class="w-full sm:w-80 space-y-2 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal Barang:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format((float)$invoice->subtotal, 0, ',', '.') }}</span>
                </div>

                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A]">
                    <span>Potongan Diskon:</span>
                    <span class="tabular-nums font-medium">- {{ $business->currency_symbol }} {{ number_format((float)$invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>PPN ({{ $invoice->tax_percentage }}%):</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">+ {{ $business->currency_symbol }} {{ number_format((float)$invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($invoice->shipping_cost > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Ongkos Kirim:</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">+ {{ $business->currency_symbol }} {{ number_format((float)$invoice->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-[15px] font-bold text-black dark:text-white">
                    <span>Total Tagihan:</span>
                    <span class="tabular-nums text-[18px] text-[#007AFF]">{{ $business->currency_symbol }} {{ number_format((float)$invoice->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between text-black/60 dark:text-white/60 pt-1">
                    <span>Sudah Dibayar:</span>
                    <span class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format((float)$invoice->paid_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-black/5 dark:border-white/10 font-bold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                    <span class="text-[13px]">Sisa Piutang:</span>
                    <span class="tabular-nums text-[16px]">{{ $business->currency_symbol }} {{ number_format((float)$invoice->balance_due, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Payments History Section -->
        <div class="pt-6 border-t border-black/5 dark:border-white/10 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Riwayat Penerimaan Pembayaran</h3>
                </div>
                @if($invoice->balance_due > 0 && $invoice->status !== 'void' && $invoice->status !== 'draft')
                <button type="button" @click="showPaymentModal = true" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Pembayaran</span>
                </button>
                @endif
            </div>

            <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Bukti Bayar</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Metode Pembayaran</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Referensi / Bank</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Jumlah Dibayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($invoice->payments as $payment)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="py-3 px-4 font-semibold text-black dark:text-white tabular-nums">{{ $payment->payment_number }}</td>
                                <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60">{{ $payment->payment_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/70 dark:text-white/70">
                                        {{ str_replace('_', ' ', $payment->payment_method) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-black/50 dark:text-white/50 tabular-nums">{{ $payment->reference_number ?? '-' }}</td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                                    {{ $business->currency_symbol }} {{ number_format((float)$payment->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-black/40 dark:text-white/40 text-[13px]">
                                    Belum ada riwayat pembayaran yang tercatat untuk faktur ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================================================== -->
    <!-- 3. APPLE SHEET: CATAT PEMBAYARAN MODAL                -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('invoices.record_payment'))
    <div x-show="showPaymentModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/25 backdrop-blur-[2px] p-0 sm:p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"
            @click.away="showPaymentModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">

            <!-- Mobile Grabber Handle -->
            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Catat Penerimaan Pembayaran</h3>
                <button type="button" @click="showPaymentModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.payments.store', $invoice->id) }}" class="space-y-4 text-[13px]">
                @csrf

                <!-- Balance Due Tile -->
                <div class="p-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] flex justify-between items-center">
                    <span class="text-black/60 dark:text-white/60">Sisa Tagihan:</span>
                    <span class="font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] text-[15px]">
                        {{ $business->currency_symbol }} {{ number_format((float)$invoice->balance_due, 0, ',', '.') }}
                    </span>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nominal Pembayaran Diterima *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium">{{ $business->currency_symbol }}</span>
                        <input type="number" step="any" min="1" max="{{ (float)$invoice->balance_due }}" name="amount" value="{{ (float)$invoice->balance_due }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-10 pr-3.5 text-[15px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Bayar *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Metode Bayar *</label>
                        <select name="payment_method" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS / E-Wallet</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="cheque">Cek / Giro</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">No. Referensi / Bukti Transfer</label>
                    <input type="text" name="reference_number" placeholder="TRX-BCA-981203"
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Catatan Pembayaran</label>
                    <input type="text" name="notes" placeholder="Pelunasan termin 1..."
                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showPaymentModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Konfirmasi Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 4. APPLE ALERT DIALOG: KONFIRMASI RILIS FAKTUR         -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('invoices.create') || \App\Support\Context::hasPermission('invoices.edit'))
    <div x-show="confirmReleaseModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="confirmReleaseModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Konfirmasi Faktur?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Faktur akan dirilis dan stok barang akan dipotong dari gudang yang dipilih.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="confirmReleaseModalOpen = false" class="py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitRelease()" class="py-3 text-[#34C759] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 5. APPLE ALERT DIALOG: VOID / BATALKAN FAKTUR          -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('invoices.delete') || \App\Support\Context::hasPermission('invoices.edit'))
    <div x-show="confirmVoidModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="confirmVoidModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Batalkan (Void) Faktur?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Faktur ini akan dibatalkan permanen dan seluruh stok yang dipotong akan dikembalikan ke gudang.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="confirmVoidModalOpen = false" class="py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Kembali
                </button>
                <button type="button" @click="submitVoid()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Void Faktur
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
