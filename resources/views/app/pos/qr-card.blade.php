@php
    $cardList = isset($cards) ? $cards : [
        ['table' => $table, 'qrSvg' => $qrSvg]
    ];
    $isMultiple = count($cardList) > 1;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isMultiple ? 'Cetak Semua Kartu QR Meja (' . count($cardList) . ' Meja)' : 'Kartu QR ' . $cardList[0]['table']->table_number }} — {{ $business->name }}</title>
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F2F2F7;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: transparent !important;
                padding: 0 !important;
            }
            .qr-standee-card {
                box-shadow: none !important;
                border: 1px solid #E5E5EA !important;
                margin: 0 auto !important;
                page-break-after: always;
                break-after: page;
            }
            .qr-cards-container {
                display: block !important;
                gap: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-start p-4 sm:p-8">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="no-print w-full max-w-[760px] mb-6 flex flex-wrap items-center justify-between gap-3 bg-white/90 backdrop-blur-md p-3.5 rounded-[16px] border border-black/10 shadow-sm sticky top-4 z-20">
        <div class="flex items-center gap-2">
            <a href="{{ route('pos.tables.index') }}" class="h-9 px-3.5 rounded-[10px] bg-black/[0.04] hover:bg-black/[0.08] text-xs font-semibold text-black/70 hover:text-black flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                <span>Daftar Meja</span>
            </a>

            @if($isMultiple)
                <span class="text-xs font-semibold text-black/50 bg-black/[0.05] px-2.5 py-1 rounded-full">
                    Total: {{ count($cardList) }} Meja
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if(!$isMultiple)
                <a href="{{ route('pos.tables.qr-svg', $cardList[0]['table']->id) }}" class="h-9 px-3 rounded-[10px] bg-black/[0.04] hover:bg-black/[0.08] text-xs font-semibold text-[#007AFF] flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span>Unduh SVG</span>
                </a>
            @endif

            <button type="button" onclick="window.print()" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                <span>{{ $isMultiple ? 'Cetak Semua Standee (Print All)' : 'Cetak Standee (Print)' }}</span>
            </button>
        </div>
    </div>

    <!-- Standee QR Cards Container -->
    <div class="qr-cards-container flex flex-wrap items-center justify-center gap-6 w-full max-w-[1200px]">
        @foreach($cardList as $item)
        @php
            $tbl = $item['table'];
            $svg = $item['qrSvg'];
        @endphp
        <!-- Standee QR Card Container (A6 Acrylic Proportion) -->
        <div class="qr-standee-card w-full max-w-[360px] bg-white rounded-[24px] border border-black/10 shadow-[0_12px_40px_rgba(0,0,0,0.08)] overflow-hidden text-center flex flex-col justify-between p-7 relative">
            <!-- Top Accents -->
            <div class="space-y-3">
                <!-- Business Brand -->
                <div class="flex items-center justify-center gap-2.5">
                    @if($business->logo_url)
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="w-8 h-8 rounded-full object-contain border border-black/5 p-0.5">
                    @else
                        <div class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs">
                            {{ substr($business->name, 0, 2) }}
                        </div>
                    @endif
                    <div class="font-bold text-sm text-black tracking-tight">{{ $business->name }}</div>
                </div>

                <div>
                    <div class="text-[10px] font-bold text-[#007AFF] tracking-[0.18em] uppercase">Scan to Order</div>
                    <h2 class="text-xs font-medium text-black/60 mt-0.5">Pindai untuk Pesan Menu</h2>
                </div>
            </div>

            <!-- High-Res QR Code Vector -->
            <div class="my-5 p-3 rounded-[20px] bg-[#F9F9FB] border border-black/5 flex items-center justify-center">
                <div class="w-full max-w-[240px] aspect-square flex items-center justify-center">
                    {!! $svg !!}
                </div>
            </div>

            <!-- Table Identifier & Instructions -->
            <div class="space-y-2">
                <div>
                    <div class="text-[11px] font-semibold text-black/40 uppercase tracking-wider">Nomor Meja</div>
                    <div class="text-3xl font-extrabold text-black tracking-tight tabular-nums mt-0.5">{{ $tbl->table_number }}</div>
                    @if($tbl->name)
                        <div class="text-xs font-medium text-black/60">{{ $tbl->name }}</div>
                    @endif
                </div>

                <p class="text-[11px] text-black/60 leading-relaxed max-w-[280px] mx-auto pt-1">
                    Arahkan kamera HP ke QR Code untuk melihat katalog menu, memilih varian, dan memesan langsung.
                </p>

                <div class="pt-3 border-t border-black/5 flex items-center justify-center gap-1.5 text-[10px] text-black/35 font-medium tracking-wide">
                    <span>Self-Ordering System</span>
                    <span>•</span>
                    <span>Powered by COOCA</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</body>
</html>
