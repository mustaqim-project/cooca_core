{{-- TAB 7: PAYMENT METHODS & SETTLEMENT --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Rekapitulasi Metode Pembayaran</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Rincian penerimaan kas, QRIS, kartu EDC, dan mutasi pembayaran per channel</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $paymentMethods->count() }}</span> saluran pembayaran
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Metode Pembayaran</th>
                    <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                    <th class="py-3 px-4 text-right">Total Diterima</th>
                    <th class="py-3 px-4 text-right">Potongan MDR / Fee</th>
                    <th class="py-3 px-4 text-right">Penerimaan Bersih</th>
                    <th class="py-3 px-4 text-right">Porsi Omzet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($paymentMethods as $idx => $pm)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4 text-[12px] text-black/40 dark:text-white/40 font-mono">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-black dark:text-white">{{ $pm->method }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40 font-mono">{{ $pm->raw_method ?? 'gateway' }}</div>
                        </td>
                        <td class="py-3 px-4 text-center font-bold tabular-nums text-[#007AFF]">
                            {{ number_format($pm->count, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format($pm->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                            {{ ($pm->fee ?? 0) > 0 ? '-Rp ' . number_format($pm->fee, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($pm->net_amount ?? $pm->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60 text-[12px]">
                            {{ number_format($pm->share, 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada data metode pembayaran pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
