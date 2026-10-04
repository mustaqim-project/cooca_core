@extends('layouts.app', [
    'title' => __('purchasing.detail_title', ['number' => $purchaseOrder->po_number]),
    'headerTitle' => __('purchasing.detail_header', ['number' => $purchaseOrder->po_number]),
    'headerSubtitle' => $purchaseOrder->po_type === 'customer' ? __('purchasing.detail_subtitle_customer') : __('purchasing.detail_subtitle_supplier')
])

@section('content')
@php
    $statusBadges = [
        'draft' => ['bg' => 'bg-black/5 dark:bg-white/8', 'text' => 'text-black/60 dark:text-white/60', 'dot' => 'bg-black/40 dark:bg-white/40'],
        'confirmed' => ['bg' => 'bg-[#007AFF]/12', 'text' => 'text-[#007AFF] dark:text-[#0A84FF]', 'dot' => 'bg-[#007AFF]'],
        'partially_invoiced' => ['bg' => 'bg-[#FF9500]/12', 'text' => 'text-[#B25E00] dark:text-[#FF9F0A]', 'dot' => 'bg-[#FF9500]'],
        'fully_invoiced' => ['bg' => 'bg-[#34C759]/12', 'text' => 'text-[#248A3D] dark:text-[#30D158]', 'dot' => 'bg-[#34C759]'],
        'completed' => ['bg' => 'bg-[#34C759]/12', 'text' => 'text-[#248A3D] dark:text-[#30D158]', 'dot' => 'bg-[#34C759]'],
        'cancelled' => ['bg' => 'bg-[#FF3B30]/12', 'text' => 'text-[#C41E17] dark:text-[#FF453A]', 'dot' => 'bg-[#FF3B30]'],
    ];
    $statusStyle = $statusBadges[$purchaseOrder->status] ?? ['bg' => 'bg-black/5', 'text' => 'text-black/60', 'dot' => 'bg-black/40'];
    $statusLabel = __('purchasing.statuses.' . $purchaseOrder->status);
    $hasGoodsReceipts = $purchaseOrder->goodsReceipts()->exists();
    $hasInvoices = $purchaseOrder->invoices()->exists();
    $canCancel = !in_array($purchaseOrder->status, ['cancelled', 'completed', 'fully_invoiced'], true) && !$hasGoodsReceipts && !$hasInvoices;
@endphp

<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    cancelModalOpen: false,
    supervisorPin: '',
    cancelReason: '',
    isCancelling: false
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-[#007AFF] transition-colors">Purchase Orders</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium tabular-nums">{{ $purchaseOrder->po_number }}</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight tabular-nums">
                    {{ $purchaseOrder->po_number }}
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                    {{ $statusLabel }}
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                {{ $purchaseOrder->po_type === 'customer' ? __('purchasing.types.customer') : __('purchasing.types.supplier') }}
                @if($purchaseOrder->reference_number)
                    · {{ __('purchasing.fields.reference_number') }}: <span class="tabular-nums font-medium text-black/70 dark:text-white/70">{{ $purchaseOrder->reference_number }}</span>
                @endif
            </p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('purchase-orders.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.actions.back_to_list') }}</span>
            </a>

            @if(\App\Support\Context::hasPermission('purchasing.manage') && $purchaseOrder->status === 'draft')
            <form method="POST" action="{{ route('purchase-orders.confirm', $purchaseOrder->id) }}">
                @csrf
                <button type="submit" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('purchasing.actions.confirm_po') }}</span>
                </button>
            </form>
            @endif

            @if(\App\Support\Context::hasPermission('invoices.create') && $purchaseOrder->po_type === 'customer' && $purchaseOrder->status !== 'fully_invoiced' && $purchaseOrder->status !== 'cancelled')
            <form method="POST" action="{{ route('purchase-orders.generate-invoice', $purchaseOrder->id) }}">
                @csrf
                <button type="submit" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>{{ __('purchasing.actions.generate_invoice') }}</span>
                </button>
            </form>
            @endif

            @if((\App\Support\Context::hasPermission('receiving.manage') || \App\Support\Context::hasPermission('purchasing.manage')) && $purchaseOrder->po_type === 'supplier' && in_array($purchaseOrder->status, ['confirmed', 'partially_invoiced'], true))
            <a href="{{ route('purchasing.receipts.create', $purchaseOrder->id) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(255,149,0,0.25)]">
                <i data-lucide="package-check" class="w-4 h-4"></i>
                <span>{{ __('purchasing.actions.receive_goods') }}</span>
            </a>
            @endif

            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}?download=1" target="_blank" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>{{ __('purchasing.actions.download_pdf') }}</span>
            </a>

            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('purchasing.actions.print') }}</span>
            </a>

            @if(\App\Support\Context::hasPermission('purchasing.manage') && $canCancel)
            <button type="button" @click="cancelModalOpen = true" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                <span>{{ __('purchasing.actions.cancel_po') }}</span>
            </button>
            @endif
        </div>
    </header>

    {{-- Horizontal Visual Stepper Otorisasi Dokumen (PRD-04 MAR) --}}
    @if (!empty($approvalData) && ($approvalData['has_approval'] ?? false))
        <x-document-stepper :approvalData="$approvalData" documentType="purchase_order" :documentId="$purchaseOrder->id" />
    @endif

    <!-- ===================================================== -->
    <!-- 2. MAIN DOCUMENT SHEET (Apple HIG Cockpit Container)  -->
    <!-- ===================================================== -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        
        <!-- Sheet Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-black/5 dark:border-white/10">
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">
                    {{ $purchaseOrder->po_type === 'customer' ? __('purchasing.types.customer') : __('purchasing.types.supplier') }}
                </span>
                <h2 class="text-[26px] font-bold text-black dark:text-white tabular-nums tracking-tight mt-0.5">
                    {{ $purchaseOrder->po_number }}
                </h2>
                @if($purchaseOrder->reference_number)
                    <div class="text-[13px] text-black/50 dark:text-white/50 mt-1">
                        {{ __('purchasing.fields.reference_number') }}: <span class="font-medium tabular-nums text-black dark:text-white">{{ $purchaseOrder->reference_number }}</span>
                    </div>
                @endif
            </div>

            <div class="text-left sm:text-right space-y-1.5 self-start sm:self-auto">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                        {{ __('purchasing.fields.status') }}: {{ $statusLabel }}
                    </span>
                </div>
                <div class="text-[12px] text-black/50 dark:text-white/50">
                    {{ __('purchasing.fields.order_date') }}: <span class="tabular-nums font-medium text-black dark:text-white">{{ $purchaseOrder->order_date?->translatedFormat('d F Y') }}</span>
                </div>
                @if($purchaseOrder->expected_delivery_date)
                    <div class="text-[12px] text-black/50 dark:text-white/50">
                        {{ __('purchasing.fields.expected_delivery_date') }}: <span class="tabular-nums font-medium text-black dark:text-white">{{ $purchaseOrder->expected_delivery_date?->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Party Information (2-Column Grouped Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
            <!-- Penerbit Pesanan -->
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 block">
                    {{ __('purchasing.detail.issuer') }}:
                </span>
                <div class="text-[15px] font-semibold text-black dark:text-white">{{ $business->name }}</div>
                <div class="text-black/60 dark:text-white/60 leading-relaxed">{{ $business->address ?? 'Alamat Kantor Pusat' }}</div>
                <div class="text-black/50 dark:text-white/50 text-[12px] pt-1">
                    {{ __('purchasing.detail.currency') }}: <span class="font-medium text-black dark:text-white">{{ $business->currency_code }} ({{ $business->currency_symbol }})</span>
                </div>
            </div>

            <!-- Lawan Transaksi -->
            <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 block">
                    {{ $purchaseOrder->po_type === 'customer' ? __('purchasing.detail.customer_party') : __('purchasing.detail.supplier_party') }}:
                </span>
                @if($purchaseOrder->customer)
                    <div class="text-[15px] font-semibold text-black dark:text-white">{{ $purchaseOrder->customer->name }}</div>
                    @if($purchaseOrder->customer->company_name)
                        <div class="text-[#007AFF] font-medium text-[13px]">{{ $purchaseOrder->customer->company_name }}</div>
                    @endif
                    <div class="text-black/60 dark:text-white/60 leading-relaxed">{{ $purchaseOrder->customer->billing_address ?? '-' }}</div>
                    <div class="text-black/50 dark:text-white/50 text-[12px] tabular-nums pt-1">
                        {{ __('purchasing.detail.contact') }}: {{ $purchaseOrder->customer->phone ?? $purchaseOrder->customer->email ?? '-' }}
                    </div>
                @elseif($purchaseOrder->supplier)
                    <div class="text-[15px] font-semibold text-black dark:text-white">{{ $purchaseOrder->supplier->name }}</div>
                    <div class="text-black/60 dark:text-white/60">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</div>
                    <div class="text-black/60 dark:text-white/60 leading-relaxed">{{ $purchaseOrder->supplier->address ?? '-' }}</div>
                    <div class="text-black/50 dark:text-white/50 text-[12px] tabular-nums pt-1">
                        {{ __('purchasing.detail.contact') }}: {{ $purchaseOrder->supplier->phone ?? $purchaseOrder->supplier->email ?? '-' }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Line Items (Dual View: Desktop Table & Mobile Cards) -->
        <div class="rounded-[12px] border border-black/5 dark:border-white/5 overflow-hidden">
            <!-- DESKTOP TABLE (hidden sm:block) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-12 text-center">No</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('purchasing.fields.item') }}</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-24">{{ __('purchasing.fields.unit') }}</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-24">{{ __('purchasing.fields.quantity') }}</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('purchasing.fields.unit_price') }}</th>
                            <th class="py-2.5 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('purchasing.fields.subtotal') }}</th>
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

            <!-- MOBILE CARDS (sm:hidden) -->
            <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @foreach($purchaseOrder->items as $idx => $item)
                <div class="p-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase">#{{ $idx + 1 }}</span>
                            <h4 class="text-[15px] font-semibold text-black dark:text-white">{{ $item->item_name }}</h4>
                            @if($item->sku)
                                <p class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">SKU: {{ $item->sku }}</p>
                            @endif
                        </div>
                        <span class="text-[13px] font-bold tabular-nums text-black dark:text-white">
                            {{ $business->currency_symbol }} {{ number_format((float)$item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-[12px] text-black/60 dark:text-white/60 pt-1">
                        <span>{{ (float)$item->quantity == (int)$item->quantity ? number_format((float)$item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float)$item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit?->name ?? 'pcs' }} &times; {{ $business->currency_symbol }} {{ number_format((float)$item->unit_price, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Totals & Notes Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
            <!-- Notes / Terms -->
            <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                @if($purchaseOrder->terms_and_conditions)
                <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3.5 border border-black/5 dark:border-white/5">
                    <span class="font-semibold text-black dark:text-white block mb-1">{{ __('purchasing.fields.terms_and_conditions') }}:</span>
                    <p class="text-black/60 dark:text-white/60 whitespace-pre-line leading-relaxed">{{ $purchaseOrder->terms_and_conditions }}</p>
                </div>
                @endif

                @if($purchaseOrder->notes)
                <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] p-3.5 border border-black/5 dark:border-white/5">
                    <span class="font-semibold text-black dark:text-white block mb-1">{{ __('purchasing.fields.notes') }}:</span>
                    <p class="text-black/60 dark:text-white/60 leading-relaxed">{{ $purchaseOrder->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Financial Calculation Summary -->
            <div class="w-full sm:w-80 space-y-2.5 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('purchasing.sections.subtotal_items') }}</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">
                        {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->subtotal, 0, ',', '.') }}
                    </span>
                </div>

                @if($purchaseOrder->discount_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('purchasing.fields.discount') }}</span>
                    <span class="tabular-nums font-medium text-[#FF3B30] dark:text-[#FF453A]">
                        - {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->discount_amount, 0, ',', '.') }}
                    </span>
                </div>
                @endif

                @if($purchaseOrder->tax_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('purchasing.fields.tax_vat') }} ({{ $purchaseOrder->tax_percentage }}%)</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">
                        + {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->tax_amount, 0, ',', '.') }}
                    </span>
                </div>
                @endif

                <div class="pt-3 border-t border-black/5 dark:border-white/10 flex justify-between items-center">
                    <span class="text-[14px] font-semibold text-black dark:text-white">{{ __('purchasing.fields.grand_total') }}</span>
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
                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759]"></i>
                <span>{{ __('purchasing.invoices_section.title') }}:</span>
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

    <!-- ===================================================== -->
    <!-- 3. SUPERVISOR PIN CANCELLATION MODAL                  -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('purchasing.manage') && $canCancel)
    <div x-show="cancelModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"
            @click.away="cancelModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-5 pt-5 pb-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2 text-[#FF3B30]">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <h3 class="text-[16px] font-semibold text-black dark:text-white">{{ __('purchasing.actions.cancel_po') }}</h3>
                </div>
                <button type="button" @click="cancelModalOpen = false" class="min-h-[44px] min-w-[44px] rounded-[8px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('purchase-orders.cancel', $purchaseOrder->id) }}" method="POST" @submit="if(isCancelling) { $event.preventDefault(); return; } isCancelling = true" class="p-5 pt-0 space-y-4 text-[13px]">
                @csrf
                
                <p class="text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                    {{ __('purchasing.modals.cancel_warning', ['number' => $purchaseOrder->po_number]) }}
                </p>

                @if($purchaseOrder->status === 'confirmed' && !empty($business->pos_supervisor_pin))
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.fields.supervisor_pin') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <input type="password" name="supervisor_pin" required placeholder="{{ __('purchasing.placeholders.supervisor_pin') }}"
                           class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] text-center tracking-widest font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                </div>
                @endif

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">
                        {{ __('purchasing.fields.cancel_reason') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <textarea name="cancel_reason" rows="2" required placeholder="{{ __('purchasing.placeholders.cancel_reason') }}"
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-2.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="cancelModalOpen = false" class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all">
                        {{ __('purchasing.actions.cancel') }}
                    </button>
                    <button type="submit" :disabled="isCancelling" class="min-h-[44px] px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.97] active:opacity-80 disabled:opacity-50 transition-all shadow-[0_1px_2px_rgba(255,59,48,0.25)] flex items-center gap-1.5">
                        <template x-if="!isCancelling">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </template>
                        <template x-if="isCancelling">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </template>
                        <span x-text="isCancelling ? '{{ __('purchasing.actions.submitting') }}' : '{{ __('purchasing.actions.cancel_po') }}'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
