@php
    $cardList = isset($cards) ? $cards : [['table' => $table, 'qrSvg' => $qrSvg]];
    $isMultiple = count($cardList) > 1;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ $isMultiple ? 'Cetak Semua Kartu QR Meja (' . count($cardList) . ' Meja)' : 'Kartu QR ' . $cardList[0]['table']->table_number }}
        - {{ $business->name }}</title>
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
    <div class="no-print w-full max-w-[760px] mb-6 flex flex-wrap items-center justify-between gap-3 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md p-4 rounded-[20px] border border-black/10 dark:border-white/10 shadow-sm sticky top-4 z-20">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('pos.tables.index') }}"
                class="min-h-[44px] h-11 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-[13px] font-semibold text-black/80 dark:text-white/80 hover:text-black dark:hover:text-white flex items-center gap-2 transition active:scale-[0.98]">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Daftar Meja</span>
            </a>

            @if ($isMultiple)
                <span class="text-[12px] font-bold text-black/60 dark:text-white/60 bg-black/[0.05] dark:bg-white/[0.08] px-3.5 py-1.5 rounded-full">
                    Total: {{ count($cardList) }} Meja
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2.5">
            @if (!$isMultiple)
                <a href="{{ route('pos.tables.qr-svg', $cardList[0]['table']->id) }}"
                    class="min-h-[44px] h-11 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-[13px] font-semibold text-[#007AFF] flex items-center gap-2 transition active:scale-[0.98]">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh SVG</span>
                </a>
            @endif

            <button type="button" onclick="window.print()"
                class="min-h-[44px] h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold flex items-center gap-2 shadow-sm transition active:scale-[0.98]">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>{{ $isMultiple ? 'Cetak Semua Standee' : 'Cetak Standee' }}</span>
            </button>
        </div>
    </div>

    <!-- Standee QR Cards Container -->
    <div class="qr-cards-container flex flex-wrap items-center justify-center gap-6 w-full max-w-[1200px]">
        @foreach ($cardList as $item)
            @php
                $tbl = $item['table'];
                $svg = $item['qrSvg'];
            @endphp
            <!-- Standee QR Card Container (A6 Acrylic Proportion) -->
            <div
                class="qr-standee-card w-full max-w-[360px] bg-white rounded-[24px] border border-black/10 shadow-[0_12px_40px_rgba(0,0,0,0.08)] overflow-hidden text-center flex flex-col justify-between p-7 relative">
                <!-- Top Accents -->
                <div class="space-y-3">
                    <!-- Business Brand -->
                    <div class="flex items-center justify-center gap-2.5">
                        @if ($business->logo_url)
                            <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                                class="w-8 h-8 rounded-full object-contain border border-black/5 p-0.5">
                        @else
                            <div
                                class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs">
                                {{ substr($business->name, 0, 2) }}
                            </div>
                        @endif
                        <div class="font-bold text-sm text-black tracking-tight">{{ $business->name }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-bold text-[#007AFF] tracking-[0.18em] uppercase">Scan to Order
                        </div>
                        <h2 class="text-xs font-medium text-black/60 mt-0.5">Pindai untuk Pesan Menu</h2>
                    </div>
                </div>

                <!-- High-Res QR Code Vector -->
                <div
                    class="my-5 p-3 rounded-[20px] bg-[#F9F9FB] border border-black/5 flex items-center justify-center">
                    <div class="w-full max-w-[240px] aspect-square flex items-center justify-center">
                        {!! $svg !!}
                    </div>
                </div>

                <!-- Table Identifier & Instructions -->
                <div class="space-y-2">
                    <div>
                        <div class="text-[11px] font-semibold text-black/40 uppercase tracking-wider">Nomor Meja</div>
                        <div class="text-3xl font-extrabold text-black tracking-tight tabular-nums mt-0.5">
                            {{ $tbl->table_number }}</div>
                        @if ($tbl->name)
                            <div class="text-xs font-medium text-black/60">{{ $tbl->name }}</div>
                        @endif
                    </div>

                    <p class="text-[11px] text-black/60 leading-relaxed max-w-[280px] mx-auto pt-1">
                        Arahkan kamera HP ke QR Code untuk melihat katalog menu, memilih varian, dan memesan langsung.
                    </p>

                    <div
                        class="pt-3 border-t border-black/5 flex items-center justify-center gap-1.5 text-[10px] text-black/35 font-medium tracking-wide">
                        <span>Self-Ordering System</span>
                        <span>•</span>
                        <span>Powered by COOCA</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>

</html>
