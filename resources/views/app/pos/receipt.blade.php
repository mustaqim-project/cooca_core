<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->order_number }} - {{ $business->name }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
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

    <!-- Flash Message Notification (Hidden on Print) -->
    @if (session('success'))
        <div class="no-print max-w-sm mx-auto mb-3 p-2.5 rounded-[10px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[12px] font-sans font-medium text-center flex items-center justify-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Screen Action Bar (macOS Sonoma Floating Toolbar, Hidden on Print) -->
    <div
        class="no-print max-w-md mx-auto mb-4 p-2 rounded-[12px] backdrop-blur-md bg-white/80 dark:bg-[#2C2C2E]/80 border border-black/5 dark:border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.06)] flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <a href="{{ route('pos.terminal') }}"
                class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 text-[12px] font-sans font-medium transition flex items-center gap-1">
                <span>‹ Terminal</span>
            </a>
            @if ($order->print_count > 1)
                <span class="px-2 py-1 rounded-[6px] text-[11px] font-sans font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/30 shrink-0">
                    Salinan (Ke-{{ $order->print_count }})
                </span>
            @else
                <span class="px-2 py-1 rounded-[6px] text-[11px] font-sans font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/30 shrink-0">
                    Cetakan Asli
                </span>
            @endif
        </div>

        <div class="flex items-center gap-1.5">
            <button onclick="window.print()"
                class="h-8 px-3 rounded-[8px] {{ $order->print_count > 1 ? 'bg-[#FF9500] hover:bg-[#E08500]' : 'bg-[#007AFF] hover:bg-[#0071E3]' }} text-white text-[12px] font-sans font-semibold active:scale-[0.97] transition flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,0,0,0.15)]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24-1.049-.37-2.14-.37-3.254 0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.114-.13 2.205-.37 3.254M6.72 13.829A8.966 8.966 0 004 19.5h16a8.966 8.966 0 00-2.72-5.671M6.72 13.829l1.83 1.83m6.9-1.83l-1.83 1.83" />
                </svg>
                <span>{{ $order->print_count > 1 ? 'Cetak Salinan' : 'Cetak Bill' }}</span>
            </button>

            <!-- Re-Print Trigger Action -->
            <form method="POST" action="{{ route('pos.receipt.reprint', $order->id) }}" class="inline"
                onsubmit="return confirm('Cetak Ulang (Re-Print) Bill ini?\n\nTindakan ini akan dicatat dalam Jejak Audit & Anti-Fraud sebagai Salinan / Cetakan ke-{{ $order->print_count + 1 }}.');">
                @csrf
                <button type="submit"
                    class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black/85 dark:text-white/85 text-[12px] font-sans font-medium active:scale-[0.97] transition flex items-center gap-1"
                    title="Cetak Salinan Tambahan & Catat Log Forensik">
                    <svg class="w-3.5 h-3.5 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Re-Print</span>
                </button>
            </form>

            <a href="{{ $whatsappUrl }}" target="_blank"
                class="h-8 px-2 rounded-[8px] bg-[#34C759]/12 hover:bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[12px] font-sans font-semibold active:scale-[0.97] transition flex items-center gap-1">
                <span>WA</span>
            </a>
        </div>
    </div>

    <!-- Thermal Paper Receipt -->
    <div class="thermal-receipt">

        <!-- Header / Merchant Info -->
        <div class="text-center pb-2.5 border-b border-dashed border-gray-400">
            <div class="font-bold text-[14px] tracking-tight uppercase">{{ $business->name }}</div>
            @if ($order->location)
                <div class="text-[10px] text-gray-700 mt-0.5">{{ $order->location->name }}</div>
            @endif
            @if ($business->address)
                <div class="text-[9px] text-gray-600 leading-tight mt-0.5">{{ $business->address }}</div>
            @endif
            @if ($business->phone)
                <div class="text-[9px] text-gray-600">Telp: {{ $business->phone }}</div>
            @endif
        </div>

        {{-- JIKA CETAKAN ASLI (print_count <= 1), TIDAK TERCANTUM CETAKAN KE BERAPA --}}
        {{-- JIKA RE-PRINT (print_count > 1), TERCANTUM WATERMARK SALINAN & CETAKAN KE-N --}}
        @if ($order->print_count > 1)
            <div class="my-2 py-1 px-1.5 border-2 border-dashed border-black dark:border-white text-center font-bold text-[11px] tracking-wider uppercase bg-black/[0.04] dark:bg-white/[0.04]">
                *** SALINAN (CETAKAN KE-{{ $order->print_count }}) ***
            </div>
        @endif

        <!-- Meta Info -->
        <div class="py-2 text-[10px] border-b border-dashed border-gray-400 space-y-0.5">
            @if ($order->print_count > 1)
                <div class="flex justify-between font-bold text-[#FF3B30] dark:text-[#FF453A]">
                    <span>Status Dokumen:</span>
                    <span>SALINAN (CETAKAN KE-{{ $order->print_count }})</span>
                </div>
                @if ($order->last_printed_at)
                    <div class="flex justify-between text-[9px] text-gray-600">
                        <span>Waktu Re-Print:</span>
                        <span>{{ $order->last_printed_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
                @if ($order->lastPrintedBy)
                    <div class="flex justify-between text-[9px] text-gray-600">
                        <span>Operator:</span>
                        <span>{{ $order->lastPrintedBy->name }}</span>
                    </div>
                @endif
            @endif
            <div class="flex justify-between">
                <span>No. Order:</span>
                <span class="font-bold tabular-nums">#{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span class="tabular-nums">{{ $order->order_date->format('d/m/Y') }}
                    {{ $order->created_at->format('H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $order->user->name ?? 'Kasir' }}</span>
            </div>
            @if ($order->customer)
                <div class="flex justify-between">
                    <span>Member:</span>
                    <span class="font-bold">{{ $order->customer->name }}
                        ({{ strtoupper($order->customer->membership_tier ?? 'Bronze') }})</span>
                </div>
            @elseif($order->customer_name_guest)
                <div class="flex justify-between">
                    <span>Pelanggan:</span>
                    <span>{{ $order->customer_name_guest }}</span>
                </div>
            @endif
            @if ($order->table_or_reference)
                <div class="flex justify-between">
                    <span>Meja / Ref:</span>
                    <span>{{ $order->table_or_reference }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span>Tipe:</span>
                <span class="uppercase font-semibold">{{ $order->order_type }}</span>
            </div>

            {{-- Bengkel Otomotif Metadata --}}
            @if ($order->vehicle_license_plate)
                <div class="flex justify-between font-bold text-[10px] pt-1 border-t border-dotted border-gray-300">
                    <span>No. Kendaraan:</span>
                    <span>{{ $order->vehicle_license_plate }} {{ $order->vehicle_model ? '(' . $order->vehicle_model . ')' : '' }}</span>
                </div>
            @endif
            @if ($order->vehicle_mileage)
                <div class="flex justify-between text-[10px]">
                    <span>Odometer:</span>
                    <span class="tabular-nums">{{ number_format($order->vehicle_mileage, 0, ',', '.') }} KM</span>
                </div>
            @endif
            @if ($order->technician)
                <div class="flex justify-between text-[10px]">
                    <span>Mekanik / Teknisi:</span>
                    <span>{{ $order->technician->name }}</span>
                </div>
            @endif
            @if ($order->service_notes)
                <div class="text-[9px] text-gray-600 italic">
                    <span>Catatan: {{ $order->service_notes }}</span>
                </div>
            @endif

            {{-- Laundry Metadata --}}
            @if ($order->laundry_weight_kg)
                <div class="flex justify-between font-bold text-[10px] pt-1 border-t border-dotted border-gray-300">
                    <span>Berat Timbangan:</span>
                    <span class="tabular-nums">{{ number_format($order->laundry_weight_kg, 2, ',', '.') }} kg</span>
                </div>
            @endif
            @if ($order->rack_location)
                <div class="flex justify-between text-[10px]">
                    <span>Loker / Rak:</span>
                    <span class="font-bold">{{ $order->rack_location }}</span>
                </div>
            @endif
            @if ($order->estimated_completion_at)
                <div class="flex justify-between text-[10px]">
                    <span>Est. Selesai:</span>
                    <span class="tabular-nums">{{ $order->estimated_completion_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
        </div>

        <!-- Item Lines -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-1.5">
            @foreach ($order->items as $item)
                <div>
                    <div class="font-bold text-[11px] leading-tight">{{ $item->product_name }}</div>
                    <div class="flex justify-between text-[10px] text-gray-700">
                        <span class="tabular-nums">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} x
                            {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span
                            class="font-semibold text-black tabular-nums">{{ number_format($item->total_price, 0, ',', '.') }}</span>
                    </div>

                    {{-- Apotek & Klinik: Batch, Expired Date & Aturan Pakai --}}
                    @if ($item->batch_number || $item->expired_date)
                        <div class="text-[9px] text-gray-500 flex gap-2">
                            @if ($item->batch_number)
                                <span>Batch: {{ $item->batch_number }}</span>
                            @endif
                            @if ($item->expired_date)
                                <span>Exp: {{ $item->expired_date->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    @endif
                    @if ($item->dosage_instructions)
                        <div class="text-[9px] font-medium text-blue-900 bg-blue-50 px-1 py-0.5 rounded mt-0.5 inline-block">
                            Dosis: {{ $item->dosage_instructions }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-0.5 text-[10px] tabular-nums">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>

            @if ($order->discount_amount > 0 || $order->voucher_discount_amount > 0)
                <div class="flex justify-between text-gray-700">
                    <span>Diskon:</span>
                    <span>-{{ number_format($order->discount_amount + $order->voucher_discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($order->points_discount_amount > 0)
                <div class="flex justify-between text-gray-700">
                    <span>Tukar Poin:</span>
                    <span>-{{ number_format($order->points_discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($order->tax_amount > 0)
                <div class="flex justify-between">
                    <span>PPN ({{ $order->tax_percentage }}%):</span>
                    <span>{{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($order->service_charge_amount > 0)
                <div class="flex justify-between">
                    <span>Service Charge:</span>
                    <span>{{ number_format($order->service_charge_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($order->rounding_amount != 0)
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
            @foreach ($order->payments as $payment)
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
        @if ($order->points_earned > 0 || $order->customer)
            <div
                class="py-1.5 border-b border-dashed border-gray-400 text-center text-[9px] text-gray-700 tabular-nums">
                @if ($order->points_earned > 0)
                    <div>Poin Baru Didapat: +{{ $order->points_earned }} Poin</div>
                @endif
                @if ($order->customer)
                    <div>Total Saldo Poin: {{ $order->customer->points_balance }} Poin</div>
                @endif
            </div>
        @endif

        <!-- Footer Message -->
        <div class="pt-3 text-center text-[10px] text-gray-600 space-y-0.5">
            <div>{{ $business->pos_receipt_footer_note ?? 'Terima Kasih Atas Kunjungan Anda!' }}</div>
            <div class="text-[8px] text-gray-400">Powered by Cooca (cooca.id)</div>
        </div>
    </div>

</body>

</html>
