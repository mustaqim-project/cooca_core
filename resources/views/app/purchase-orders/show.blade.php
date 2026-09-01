@extends('layouts.app', [
    'title' => "Pesanan {$purchaseOrder->po_number}",
    'headerTitle' => "Purchase Order: {$purchaseOrder->po_number}",
    'headerSubtitle' => "Detail dokumen pesanan " . ($purchaseOrder->po_type === 'customer' ? 'dari pelanggan' : 'ke pemasok')
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <a href="{{ route('purchase-orders.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar PO</span>
        </a>

        <div class="flex items-center gap-2">
            @if($purchaseOrder->status === 'draft')
            <form method="POST" action="{{ route('purchase-orders.confirm', $purchaseOrder->id) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Konfirmasi PO</span>
                </button>
            </form>
            @endif

            @if($purchaseOrder->po_type === 'customer' && $purchaseOrder->status !== 'fully_invoiced' && $purchaseOrder->status !== 'cancelled')
            <form method="POST" action="{{ route('purchase-orders.generate-invoice', $purchaseOrder->id) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Generate Faktur Penjualan</span>
                </button>
            </form>
            @endif

            @if($purchaseOrder->po_type === 'supplier' && $purchaseOrder->status !== 'completed' && $purchaseOrder->status !== 'cancelled')
            <a href="{{ route('purchasing.receipts.create', $purchaseOrder->id) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 flex items-center gap-1.5 transition-all">
                <i data-lucide="package-check" class="w-4 h-4"></i>
                <span>Terima Barang Fisik</span>
            </a>
            @endif

            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}?download=1" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 transition-all">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Download PDF</span>
            </a>

            <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                <span>Cetak (A4)</span>
            </a>
        </div>
    </div>

    <!-- Main Document Sheet (Print-friendly Bento Sheet) -->
    <div class="glass-card p-8 rounded-2xl border border-slate-800 space-y-6 bg-slate-900/60">
        <!-- Sheet Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-800">
            <div>
                <div class="text-xs font-bold uppercase tracking-widest text-emerald-400">
                    {{ $purchaseOrder->po_type === 'customer' ? 'CUSTOMER PURCHASE ORDER' : 'VENDOR PURCHASE ORDER' }}
                </div>
                <h1 class="text-2xl font-extrabold text-white font-mono mt-1">{{ $purchaseOrder->po_number }}</h1>
                @if($purchaseOrder->reference_number)
                    <div class="text-xs text-slate-400 mt-0.5">Ref Eksternal: <span class="font-mono text-slate-200">{{ $purchaseOrder->reference_number }}</span></div>
                @endif
            </div>

            <div class="text-right space-y-1 self-start sm:self-auto">
                @php
                    $badges = [
                        'draft' => 'bg-slate-800 text-slate-300 border-slate-700',
                        'confirmed' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                        'partially_invoiced' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                        'fully_invoiced' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                        'completed' => 'bg-teal-500/15 text-teal-400 border-teal-500/30',
                        'cancelled' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                    ];
                    $badgeClass = $badges[$purchaseOrder->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';
                @endphp
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badgeClass }}">
                    Status: {{ str_replace('_', ' ', $purchaseOrder->status) }}
                </span>
                <div class="text-xs text-slate-400 font-mono">Tanggal Order: {{ $purchaseOrder->order_date?->translatedFormat('d F Y') }}</div>
                @if($purchaseOrder->expected_delivery_date)
                    <div class="text-xs text-slate-400 font-mono">Tenggat Kirim: {{ $purchaseOrder->expected_delivery_date?->translatedFormat('d F Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Party Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-1">
                <div class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Penerbit Pesanan:</div>
                <div class="text-sm font-bold text-white">{{ $business->name }}</div>
                <div class="text-slate-400">{{ $business->address ?? 'Alamat Kantor Pusat' }}</div>
                <div class="text-slate-400">Mata Uang: {{ $business->currency_code }} ({{ $business->currency_symbol }})</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-1">
                <div class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">
                    {{ $purchaseOrder->po_type === 'customer' ? 'Pelanggan / Pemesan:' : 'Pemasok / Vendor Dituju:' }}
                </div>
                @if($purchaseOrder->customer)
                    <div class="text-sm font-bold text-white">{{ $purchaseOrder->customer->name }}</div>
                    @if($purchaseOrder->customer->company_name)
                        <div class="text-emerald-400 font-semibold">{{ $purchaseOrder->customer->company_name }}</div>
                    @endif
                    <div class="text-slate-400">{{ $purchaseOrder->customer->billing_address ?? '-' }}</div>
                    <div class="text-slate-400 font-mono">Kontak: {{ $purchaseOrder->customer->phone ?? $purchaseOrder->customer->email ?? '-' }}</div>
                @elseif($purchaseOrder->supplier)
                    <div class="text-sm font-bold text-white">{{ $purchaseOrder->supplier->name }}</div>
                    <div class="text-slate-400">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</div>
                    <div class="text-slate-400">{{ $purchaseOrder->supplier->address ?? '-' }}</div>
                    <div class="text-slate-400 font-mono">Kontak: {{ $purchaseOrder->supplier->phone ?? $purchaseOrder->supplier->email ?? '-' }}</div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-950/80">
                        <th class="py-3 px-3 font-semibold w-12 text-center">No</th>
                        <th class="py-3 px-3 font-semibold">Deskripsi Produk / Item</th>
                        <th class="py-3 px-3 font-semibold text-center w-24">Satuan</th>
                        <th class="py-3 px-3 font-semibold text-right w-24">Qty</th>
                        <th class="py-3 px-3 font-semibold text-right w-36">Harga Satuan</th>
                        <th class="py-3 px-3 font-semibold text-right w-36">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($purchaseOrder->items as $idx => $item)
                    <tr class="hover:bg-slate-800/20">
                        <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-white">{{ $item->item_name }}</div>
                            @if($item->sku)
                                <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $item->sku }}</div>
                            @endif
                            @if($item->notes)
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center text-slate-300 font-mono">
                            {{ $item->unit?->name ?? 'pcs' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-semibold text-white">
                            {{ (float)$item->quantity == (int)$item->quantity ? number_format((float)$item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float)$item->quantity, 2, ',', '.'), '0'), ',') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-200">
                            {{ $business->currency_symbol }} {{ number_format((float)$item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-white">
                            {{ $business->currency_symbol }} {{ number_format((float)$item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Notes -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-slate-800 gap-6">
            <div class="space-y-3 w-full sm:max-w-md text-xs">
                @if($purchaseOrder->terms_and_conditions)
                <div>
                    <div class="font-bold text-slate-300 mb-0.5">Syarat & Ketentuan:</div>
                    <div class="text-slate-400 whitespace-pre-line">{{ $purchaseOrder->terms_and_conditions }}</div>
                </div>
                @endif

                @if($purchaseOrder->notes)
                <div>
                    <div class="font-bold text-slate-300 mb-0.5">Catatan:</div>
                    <div class="text-slate-400">{{ $purchaseOrder->notes }}</div>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-80 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-semibold text-white">{{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->subtotal, 0, ',', '.') }}</span>
                </div>

                @if($purchaseOrder->discount_amount > 0)
                <div class="flex justify-between text-slate-400">
                    <span>Potongan Diskon:</span>
                    <span class="font-mono font-semibold text-rose-400">- {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($purchaseOrder->tax_amount > 0)
                <div class="flex justify-between text-slate-400">
                    <span>PPN ({{ $purchaseOrder->tax_percentage }}%):</span>
                    <span class="font-mono font-semibold text-slate-200">+ {{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm font-bold text-white">
                    <span>Total Nilai Pesanan:</span>
                    <span class="font-mono text-emerald-400 text-lg">{{ $business->currency_symbol }} {{ number_format((float)$purchaseOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Invoices Link Section (If generated) -->
        @if($purchaseOrder->invoices->isNotEmpty())
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 space-y-2">
            <div class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                <i data-lucide="check-check" class="w-4 h-4"></i>
                <span>Faktur Penjualan yang Telah Diterbitkan:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($purchaseOrder->invoices as $inv)
                <a href="{{ route('invoices.show', $inv->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs flex items-center gap-2 border border-slate-800 transition-colors">
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $inv->invoice_number }}</span>
                    <span class="text-[10px] text-slate-400 font-sans font-semibold">({{ strtoupper($inv->status) }})</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
