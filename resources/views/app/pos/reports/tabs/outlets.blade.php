{{-- TAB 6: OUTLETS / CABANG PERFORMANCE --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Performa Cabang & Outlet</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Perbandingan perolehan omzet, profitabilitas, dan kontribusi antar cabang</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $outletPerformance->count() }}</span> outlet aktif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Nama Outlet</th>
                    <th class="py-3 px-4">Kode Cabang</th>
                    <th class="py-3 px-4 text-center">Total Transaksi</th>
                    <th class="py-3 px-4 text-right">Total Penjualan</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-right">Margin %</th>
                    <th class="py-3 px-4 text-right">Kontribusi Omzet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($outletPerformance as $idx => $outlet)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4 text-[12px] text-black/40 dark:text-white/40 font-mono">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-black dark:text-white">
                            {{ $outlet->name }}
                        </td>
                        <td class="py-3 px-4 font-mono text-[12px] text-black/60 dark:text-white/60">
                            {{ $outlet->code ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold tabular-nums text-[#007AFF]">
                            {{ number_format($outlet->orders_count, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format($outlet->total_sales, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($outlet->total_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            {{ number_format($outlet->margin_percent, 1) }}%
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60 text-[12px]">
                            {{ number_format($outlet->revenue_share_percent, 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada data cabang/outlet ditemukan pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
