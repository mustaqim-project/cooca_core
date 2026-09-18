<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman - {{ $waybillNumber }} - #{{ $order->order_number }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F2F2F7;
            color: #000000;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Standard 100x150 mm (4x6 inch) Thermal Shipping Label */
        .shipping-label-container {
            width: 100mm;
            min-height: 148mm;
            background-color: #ffffff;
            margin: 20px auto;
            padding: 6mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            color: #000000;
            box-sizing: border-box;
            border: 2px solid #000000;
        }

        /* A4 print mode switch */
        .shipping-label-container.format-a4 {
            width: 140mm;
            min-height: 200mm;
            padding: 8mm;
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

            .shipping-label-container {
                width: 100% !important;
                max-width: 100mm !important;
                min-height: 148mm !important;
                margin: 0 auto !important;
                padding: 4mm !important;
                box-shadow: none !important;
                border: 2px solid #000000 !important;
                border-radius: 0 !important;
                page-break-inside: avoid;
            }

            .shipping-label-container.format-a4 {
                max-width: 140mm !important;
                min-height: 200mm !important;
                padding: 6mm !important;
            }

            @page {
                size: 100mm 150mm;
                margin: 0;
            }
        }
    </style>
</head>

<body class="p-3 sm:p-6 min-h-screen">

    <!-- FLOATING ACTION TOOLBAR (Hidden on Print) -->
    <div class="no-print max-w-[100mm] mx-auto mb-4 p-3 rounded-[16px] backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/10 dark:border-white/10 shadow-lg flex items-center justify-between gap-2">
        <a href="{{ route('storefront.orders.show', $order) }}"
            class="h-9 px-3.5 rounded-[10px] bg-black/5 hover:bg-black/10 dark:bg-white/10 text-black dark:text-white text-[12.5px] font-semibold transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleFormat()" id="formatBtn"
                class="h-9 px-3 rounded-[10px] bg-black/5 hover:bg-black/10 text-black dark:text-white text-[12px] font-medium transition">
                Format: 100x150mm
            </button>

            <button type="button" onclick="window.print()"
                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-bold active:scale-[0.97] transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.049-.37-2.14-.37-3.254 0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5c0 1.114-.13 2.205-.37 3.254M6.72 13.829A8.966 8.966 0 004 19.5h16a8.966 8.966 0 00-2.72-5.671M6.72 13.829l1.83 1.83m6.9-1.83l-1.83 1.83" />
                </svg>
                <span>Cetak Resi</span>
            </button>
        </div>
    </div>

    <!-- 100x150 MM THERMAL SHIPPING LABEL -->
    <div class="shipping-label-container text-black" id="labelCard">

        {{-- 1. COURIER HEADER & PAYMENT TYPE --}}
        <div class="border-b-2 border-black pb-2 mb-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="px-2.5 py-1 bg-black text-white font-black text-[15px] tracking-wider rounded uppercase">
                    {{ $order->shipping_courier_code ? strtoupper($order->shipping_courier_code) : 'KURIR' }}
                </div>
                <div>
                    <span class="font-extrabold text-[13px] block leading-tight uppercase">
                        {{ $order->shipping_courier_service ?: 'REGULER' }}
                    </span>
                    <span class="text-[10px] text-gray-700 block leading-none font-medium">
                        {{ $order->shipping_courier_name ?: 'Standar Logistik' }}
                    </span>
                </div>
            </div>

            <div class="text-right">
                @if ($order->isCod())
                    <span class="px-2.5 py-1 bg-black text-white font-black text-[13px] tracking-wide rounded block">
                        COD: Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 border-2 border-black font-extrabold text-[11px] uppercase tracking-wider rounded inline-block">
                        NON-COD
                    </span>
                    <span class="text-[9.5px] text-gray-600 block mt-0.5">Ongkir Lunas</span>
                @endif
            </div>
        </div>

        {{-- 2. BARCODE & WAYBILL ID --}}
        <div class="text-center py-1.5 border-b-2 border-black">
            <div class="max-w-[85mm] mx-auto my-1">
                {!! $barcodeSvg !!}
            </div>
            <div class="mono font-black text-[16px] tracking-widest mt-1">
                {{ $waybillNumber }}
            </div>
            <div class="text-[10.5px] font-semibold text-gray-600 mt-0.5">
                No. Order: <span class="mono font-bold text-black">#{{ $order->order_number }}</span> &bull; {{ $order->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- 3. ROUTE / POSTAL CODE STRIP --}}
        <div class="grid grid-cols-2 border-b-2 border-black py-1.5 bg-gray-100 text-center text-[11px] font-bold">
            <div class="border-r border-black">
                ASAL: <span class="mono font-extrabold">{{ $storeSetting->origin_postal_code ?: '-' }}</span>
            </div>
            <div>
                TUJUAN: <span class="mono font-extrabold text-[13px] bg-black text-white px-2 py-0.5 rounded">{{ $order->destination_postal_code ?: '-' }}</span>
            </div>
        </div>

        {{-- 4. RECIPIENT & SENDER ADDRESS BOXES --}}
        <div class="grid grid-cols-1 divide-y-2 divide-black border-b-2 border-black">
            {{-- PENERIMA (KEPADA) --}}
            <div class="p-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-black text-white px-1.5 py-0.5 rounded">
                        PENERIMA (KEPADA):
                    </span>
                    <span class="mono font-extrabold text-[13px]">
                        {{ $order->customer_phone }}
                    </span>
                </div>
                <div class="font-black text-[14.5px] leading-tight mb-1">
                    {{ $order->customer_name }}
                </div>
                <p class="text-[11px] leading-snug font-medium text-gray-900">
                    {{ $order->shipping_address }}
                </p>
                @if ($order->destination_postal_code)
                    <div class="mt-1 text-[11px] font-bold">
                        Kode Pos: <span class="mono text-[12px] underline">{{ $order->destination_postal_code }}</span>
                    </div>
                @endif
                @if ($order->notes)
                    <div class="mt-1 p-1 bg-gray-100 rounded text-[10px] italic font-semibold border border-gray-300">
                        Catatan: "{{ $order->notes }}"
                    </div>
                @endif
            </div>

            {{-- PENGIRIM (DARI) --}}
            <div class="p-2 bg-gray-50/50">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[9.5px] font-bold uppercase tracking-wider text-gray-700">
                        PENGIRIM (DARI):
                    </span>
                    <span class="mono text-[11px] font-bold">
                        {{ $storeSetting->origin_contact_phone ?: ($business->phone ?: '-') }}
                    </span>
                </div>
                <div class="font-bold text-[12.5px] leading-tight">
                    {{ $business->name }}
                    @if ($storeSetting && $storeSetting->origin_contact_name && $storeSetting->origin_contact_name !== $business->name)
                        <span class="text-[10.5px] font-normal text-gray-600">({{ $storeSetting->origin_contact_name }})</span>
                    @endif
                </div>
                <p class="text-[10.5px] leading-snug text-gray-700 mt-0.5">
                    {{ $storeSetting->origin_address ?: ($business->address ?: 'Alamat Toko') }}
                </p>
            </div>
        </div>

        {{-- 5. PACKAGE DETAILS & FRAGILE NOTICE --}}
        <div class="grid grid-cols-3 border-b-2 border-black py-1.5 px-2 text-[10.5px] font-bold items-center">
            <div>
                Berat: <span class="font-extrabold text-[12px]">{{ $totalWeightKg }} kg</span>
            </div>
            <div class="text-center">
                Isi: <span class="font-extrabold">{{ $order->items->sum('quantity') }} pcs</span>
            </div>
            <div class="text-right">
                <span class="px-2 py-0.5 border border-black rounded text-[9.5px] font-black uppercase bg-white">
                    FRAGILE
                </span>
            </div>
        </div>

        {{-- 6. PACKING ITEM BREAKDOWN --}}
        <div class="p-2 border-b-2 border-black text-[10px]">
            <div class="font-bold uppercase tracking-wider text-gray-700 mb-1 flex items-center justify-between">
                <span>Rincian Barang (Packing Slip):</span>
                <span>Qty</span>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach ($order->items as $item)
                    <div class="py-1 flex items-start justify-between gap-2">
                        <div class="leading-tight">
                            <span class="font-bold text-black">{{ $item->product_name }}</span>
                            @if ($item->notes)
                                <span class="text-gray-500 block text-[9px]">Catatan: {{ $item->notes }}</span>
                            @endif
                        </div>
                        <div class="mono font-black text-[11px] text-right shrink-0">
                            {{ (float) $item->quantity }}x
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 7. QR CODE & FOOTER --}}
        <div class="p-2 flex items-center justify-between gap-3">
            <div class="text-[9.5px] text-gray-600 leading-snug flex-1">
                <p class="font-bold text-black mb-0.5">Scan QR untuk Lacak Status</p>
                <p>Kurir / Penerima dapat memindai kode QR di samping untuk mengecek riwayat perjalanan paket secara real-time.</p>
                <p class="mono text-[8.5px] text-gray-400 mt-1">Dicetak dari COOCA Logistics Hub</p>
            </div>
            <div class="w-[20mm] h-[20mm] shrink-0 border border-black p-0.5 bg-white">
                {!! $qrSvg !!}
            </div>
        </div>

    </div>

    <script>
        function toggleFormat() {
            const card = document.getElementById('labelCard');
            const btn = document.getElementById('formatBtn');
            if (card.classList.contains('format-a4')) {
                card.classList.remove('format-a4');
                btn.innerText = 'Format: 100x150mm';
            } else {
                card.classList.add('format-a4');
                btn.innerText = 'Format: A4 Half';
            }
        }
    </script>
</body>

</html>
