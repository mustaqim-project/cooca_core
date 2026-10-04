{{-- TAB 14: SALES CHANNELS --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Performa Saluran Penjualan (Sales Channels)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Perbandingan efektivitas penjualan langsung kasir, QR table order, dan kanal pesan-antar</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $channelSales->count() }}</span> saluran aktif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">Saluran Penjualan</th>
                    <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                    <th class="py-3 px-4 text-right">Total Omzet Bersih</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-right">Rata-rata Nota (AOV)</th>
                    <th class="py-3 px-4 text-right">Porsi Omzet %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($channelSales as $ch)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-black dark:text-white">{{ $ch->channel_name }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40 font-mono">{{ $ch->sales_channel }}</div>
                        </td>
                        <td class="py-3 px-4 text-center font-bold tabular-nums text-[#007AFF]">
                            {{ number_format($ch->orders_count, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format($ch->net_sales, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($ch->gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format($ch->aov, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black/60 dark:text-white/60 text-[12px]">
                            {{ number_format($ch->share_percent, 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada data saluran penjualan pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
