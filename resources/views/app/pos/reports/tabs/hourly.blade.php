{{-- TAB 12: HOURLY HEATMAP & PEAK HOURS --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Analisis Jam Sibuk (24-Hour Hourly Heatmap)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Pemetaan persebaran transaksi dan perolehan omzet per jam operasional</p>
        </div>
    </div>

    @php
        $maxSales = $hourlyData->max('sales') ?: 1;
        $maxOrders = $hourlyData->max('count') ?: 1;
    @endphp

    <div class="p-5 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-3">
        @for ($h = 0; $h < 24; $h++)
            @php
                $hourStr = sprintf('%02d:00', $h);
                $hInfo = $hourlyData->firstWhere('hour', $hourStr) ?? (object)['hour' => $hourStr, 'sales' => 0, 'count' => 0];
                $intensity = $hInfo->sales > 0 ? min(100, round(($hInfo->sales / $maxSales) * 100)) : 0;
            @endphp
            <div class="rounded-[12px] p-3 border border-black/5 dark:border-white/5 flex flex-col justify-between text-center transition-all hover:scale-105"
                style="background-color: rgba(0, 122, 255, {{ max(0.04, $intensity / 100 * 0.4) }})">
                <span class="text-[11px] font-bold text-black/60 dark:text-white/60 font-mono">{{ $hourStr }}</span>
                <div class="my-2">
                    <div class="text-[13px] font-bold text-black dark:text-white tabular-nums">
                        Rp {{ number_format($hInfo->sales / 1000, 0) }}k
                    </div>
                    <div class="text-[10px] text-black/50 dark:text-white/50 tabular-nums">
                        {{ $hInfo->count }} nota
                    </div>
                </div>
                <div class="w-full h-1 bg-black/10 dark:bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-[#007AFF] rounded-full" style="width: {{ $intensity }}%"></div>
                </div>
            </div>
        @endfor
    </div>

    <div class="p-4 border-t border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.01] flex items-center justify-between text-[12px] text-black/50 dark:text-white/50">
        <span>💡 Jam paling produktif diwarnai biru pekat berdasarkan total omzet.</span>
        <span>Maksimum Omzet/Jam: <strong class="text-black dark:text-white">Rp {{ number_format($maxSales, 0, ',', '.') }}</strong></span>
    </div>
</div>
