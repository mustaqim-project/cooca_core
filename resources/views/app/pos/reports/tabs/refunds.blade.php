{{-- TAB 9: REFUNDS & SALES RETURNS --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Retur Penjualan & Pengembalian Dana (Refunds)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Daftar transaksi yang mengalami pengembalian barang dan pemotongan omzet</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total Retur: <span class="font-bold text-[#FF3B30]">{{ $refundSummary->count() }}</span> kejadian
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">No. Retur</th>
                    <th class="py-3 px-4">Tanggal Retur</th>
                    <th class="py-3 px-4">No. Nota Asal</th>
                    <th class="py-3 px-4">Staf / Pemroses</th>
                    <th class="py-3 px-4">Alasan Retur</th>
                    <th class="py-3 px-4 text-right">Nominal Pengembalian</th>
                    <th class="py-3 px-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($refundSummary as $refund)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4 font-mono font-semibold text-black dark:text-white text-[12px]">
                            {{ $refund->return_number }}
                        </td>
                        <td class="py-3 px-4 text-black/70 dark:text-white/70 text-[12px] whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($refund->return_date)->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4 font-mono text-[12px] text-[#007AFF]">
                            {{ $refund->order_number ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-[12px] text-black/80 dark:text-white/80">
                            {{ $refund->creator_name ?? 'Staf' }}
                        </td>
                        <td class="py-3 px-4 text-[12px] text-black/70 dark:text-white/70">
                            {{ $refund->reason ?? 'Tidak disebutkan' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                            Rp {{ number_format((float) $refund->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider {{ $refund->status === 'completed' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF9500]/10 text-[#FF9500]' }}">
                                {{ $refund->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada retur penjualan tercatat pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
