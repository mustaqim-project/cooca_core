{{-- TAB 13: CUSTOMERS & LOYALTY MATRIX --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Matriks Pelanggan & Loyalitas (Customer Matrix)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Analisis kontribusi belanja, frekuensi repeat order, dan rata-rata transaksi per pelanggan</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $customerMatrix->count() }}</span> pelanggan terdata
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Nama Pelanggan</th>
                    <th class="py-3 px-4">Tipe / Status</th>
                    <th class="py-3 px-4">No. Telepon</th>
                    <th class="py-3 px-4 text-center">Jumlah Kunjungan</th>
                    <th class="py-3 px-4 text-right">Total Belanja</th>
                    <th class="py-3 px-4 text-right">Rata-rata Nota (AOV)</th>
                    <th class="py-3 px-4 text-right">Kontribusi Laba</th>
                    <th class="py-3 px-4 text-center">Terakhir Belanja</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($customerMatrix as $idx => $cust)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4 text-[12px] text-black/40 dark:text-white/40 font-mono">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-black dark:text-white">
                            {{ $cust->customer_name }}
                        </td>
                        <td class="py-3 px-4 text-[12px]">
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider {{ $cust->customer_type === 'Member' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/5 text-black/60' }}">
                                {{ $cust->customer_type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-[12px] text-black/60 dark:text-white/60 font-mono">
                            {{ $cust->customer_phone }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold tabular-nums text-[#007AFF]">
                            {{ number_format($cust->orders_count, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format($cust->total_spent, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format($cust->aov, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($cust->total_profit_contribution, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center text-[12px] text-black/60 dark:text-white/60">
                            {{ $cust->last_purchase_date ? \Carbon\Carbon::parse($cust->last_purchase_date)->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada data pelanggan tercatat pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
