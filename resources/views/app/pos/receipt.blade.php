<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->order_number }} — {{ $business->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'JetBrains Mono', monospace;
            background-color: #0f172a;
            color: #0f172a;
        }

        /* 58mm / 80mm Thermal Receipt Layout */
        .thermal-receipt {
            width: 80mm;
            max-width: 100%;
            background-color: #ffffff;
            margin: 20px auto;
            padding: 15px 12px;
            font-size: 11px;
            line-height: 1.35;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border-radius: 4px;
        }

        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .thermal-receipt {
                width: 100%;
                margin: 0;
                padding: 4mm;
                box-shadow: none;
                border-radius: 0;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-6">

    <!-- Screen Action Bar (Hidden on Print) -->
    <div class="no-print max-w-sm mx-auto mb-4 flex items-center justify-between gap-2">
        <a href="{{ route('pos.terminal') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-sans font-semibold transition">
            ← Terminal POS
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-sans font-bold shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                🖨️ Cetak Struk
            </button>
            <a href="{{ $whatsappUrl }}" target="_blank" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-sans font-bold transition flex items-center gap-1.5">
                💬 WhatsApp
            </a>
        </div>
    </div>

    <!-- Thermal Paper Receipt -->
    <div class="thermal-receipt text-black">
        
        <!-- Header / Merchant Info -->
        <div class="text-center pb-2 border-b border-dashed border-gray-400">
            <div class="font-bold text-base tracking-tight uppercase">{{ $business->name }}</div>
            @if($order->location)
                <div class="text-[10px] text-gray-700 mt-0.5">{{ $order->location->name }}</div>
            @endif
            @if($business->address)
                <div class="text-[9px] text-gray-600 leading-tight mt-0.5">{{ $business->address }}</div>
            @endif
            @if($business->phone)
                <div class="text-[9px] text-gray-600">Telp: {{ $business->phone }}</div>
            @endif
        </div>

        <!-- Meta Info -->
        <div class="py-2 text-[10px] border-b border-dashed border-gray-400 space-y-0.5">
            <div class="flex justify-between">
                <span>No. Order:</span>
                <span class="font-bold">#{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span>{{ $order->order_date->format('d/m/Y') }} {{ $order->created_at->format('H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $order->user->name ?? 'Kasir' }}</span>
            </div>
            @if($order->customer)
                <div class="flex justify-between">
                    <span>Member:</span>
                    <span class="font-bold">{{ $order->customer->name }} ({{ strtoupper($order->customer->membership_tier ?? 'Bronze') }})</span>
                </div>
            @elseif($order->customer_name_guest)
                <div class="flex justify-between">
                    <span>Pelanggan:</span>
                    <span>{{ $order->customer_name_guest }}</span>
                </div>
            @endif
            @if($order->table_or_reference)
                <div class="flex justify-between">
                    <span>Meja / Ref:</span>
                    <span>{{ $order->table_or_reference }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span>Tipe:</span>
                <span class="uppercase font-semibold">{{ $order->order_type }}</span>
            </div>
        </div>

        <!-- Item Lines -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-1.5">
            @foreach($order->items as $item)
                <div>
                    <div class="font-bold text-[11px] leading-tight">{{ $item->product_name }}</div>
                    <div class="flex justify-between text-[10px] text-gray-700">
                        <span>{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span class="font-semibold text-black">{{ number_format($item->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-0.5 text-[10px]">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>

            @if($order->discount_amount > 0 || $order->voucher_discount_amount > 0)
                <div class="flex justify-between text-gray-700">
                    <span>Diskon:</span>
                    <span>-{{ number_format($order->discount_amount + $order->voucher_discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($order->points_discount_amount > 0)
                <div class="flex justify-between text-gray-700">
                    <span>Tukar Poin:</span>
                    <span>-{{ number_format($order->points_discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($order->tax_amount > 0)
                <div class="flex justify-between">
                    <span>PPN ({{ $order->tax_percentage }}%):</span>
                    <span>{{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($order->service_charge_amount > 0)
                <div class="flex justify-between">
                    <span>Service Charge:</span>
                    <span>{{ number_format($order->service_charge_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if($order->rounding_amount != 0)
                <div class="flex justify-between">
                    <span>Pembulatan:</span>
                    <span>{{ number_format($order->rounding_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between font-bold text-xs pt-1 border-t border-gray-300">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment & Change -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-0.5 text-[10px]">
            @foreach($order->payments as $payment)
                <div class="flex justify-between">
                    <span>Bayar ({{ strtoupper($payment->payment_method) }}):</span>
                    <span>{{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="flex justify-between font-bold">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Loyalty Points Info -->
        @if($order->points_earned > 0 || $order->customer)
            <div class="py-1.5 border-b border-dashed border-gray-400 text-center text-[9px] text-gray-700">
                @if($order->points_earned > 0)
                    <div>Poin Baru Didapat: +{{ $order->points_earned }} Poin</div>
                @endif
                @if($order->customer)
                    <div>Total Saldo Poin: {{ $order->customer->points_balance }} Poin</div>
                @endif
            </div>
        @endif

        <!-- Footer Message -->
        <div class="pt-3 text-center text-[10px] text-gray-600 space-y-0.5">
            <div>{{ $business->pos_receipt_footer_note ?? 'Terima Kasih Atas Kunjungan Anda!' }}</div>
            <div class="text-[8px] text-gray-400">Powered by Cooca Core (cooca.id)</div>
        </div>
    </div>

</body>
</html>
