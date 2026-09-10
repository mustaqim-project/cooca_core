<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->order_number }} — {{ $business->name }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'JetBrains Mono', -apple-system, monospace;
            background-color: #F2F2F7;
            color: #000000;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1E1E1E;
            }
        }

        /* 58mm / 80mm Thermal Receipt Layout */
        .thermal-receipt {
            width: 80mm;
            max-width: 100%;
            background-color: #ffffff;
            margin: 16px auto;
            padding: 16px 14px;
            font-size: 11px;
            line-height: 1.35;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05);
            border-radius: 8px;
            color: #000000;
            font-variant-numeric: tabular-nums;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .thermal-receipt {
                width: 100% !important;
                margin: 0 !important;
                padding: 4mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body class="p-3 sm:p-6 min-h-screen">

    <!-- Screen Action Bar (macOS Sonoma Floating Toolbar, Hidden on Print) -->
    <div class="no-print max-w-sm mx-auto mb-4 p-2 rounded-[12px] backdrop-blur-md bg-white/80 dark:bg-[#2C2C2E]/80 border border-black/5 dark:border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.06)] flex items-center justify-between gap-2">
        <a href="{{ route('pos.terminal') }}" class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 text-[12px] font-sans font-medium transition flex items-center gap-1">
            <span>‹ Terminal POS</span>
        </a>
        <div class="flex items-center gap-1.5">
            <button onclick="window.print()" class="h-8 px-3.5 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-sans font-semibold active:scale-[0.97] transition flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.049-.37-2.14-.37-3.254 0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.114-.13 2.205-.37 3.254M6.72 13.829A8.966 8.966 0 004 19.5h16a8.966 8.966 0 00-2.72-5.671M6.72 13.829l1.83 1.83m6.9-1.83l-1.83 1.83" />
                </svg>
                <span>Cetak Struk</span>
            </button>
            <a href="{{ $whatsappUrl }}" target="_blank" class="h-8 px-2.5 rounded-[8px] bg-[#34C759]/12 hover:bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[12px] font-sans font-semibold active:scale-[0.97] transition flex items-center gap-1">
                <span>WA</span>
            </a>
        </div>
    </div>

    <!-- Thermal Paper Receipt -->
    <div class="thermal-receipt">

        <!-- Header / Merchant Info -->
        <div class="text-center pb-2.5 border-b border-dashed border-gray-400">
            <div class="font-bold text-[14px] tracking-tight uppercase">{{ $business->name }}</div>
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
                <span class="font-bold tabular-nums">#{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span class="tabular-nums">{{ $order->order_date->format('d/m/Y') }} {{ $order->created_at->format('H:i') }}</span>
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
                        <span class="tabular-nums">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span class="font-semibold text-black tabular-nums">{{ number_format($item->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-0.5 text-[10px] tabular-nums">
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

            <div class="flex justify-between font-bold text-[12px] pt-1 border-t border-gray-300">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment & Change -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-0.5 text-[10px] tabular-nums">
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
            <div class="py-1.5 border-b border-dashed border-gray-400 text-center text-[9px] text-gray-700 tabular-nums">
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
            <div class="text-[8px] text-gray-400">Powered by Cooca UMKM (cooca.id)</div>
        </div>
    </div>

</body>
</html>
