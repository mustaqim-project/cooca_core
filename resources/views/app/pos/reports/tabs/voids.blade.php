{{-- TAB 10: VOIDS & FRAUD AUDIT --}}
<div class="space-y-6">
    {{-- Top Red-Flag KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Transaksi Void</span>
            <div class="text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A] mt-2">
                {{ number_format($voidAudit->totalVoidOrders, 0, ',', '.') }} <span class="text-[14px] font-normal text-black/40">nota</span>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                Rasio Void: <span class="font-bold {{ $voidAudit->voidRatePercent > 5 ? 'text-[#FF3B30]' : 'text-black/70 dark:text-white/70' }}">{{ number_format($voidAudit->voidRatePercent, 1) }}%</span>
            </div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Nominal Void</span>
            <div class="text-[22px] font-bold tabular-nums text-black dark:text-white mt-2">
                Rp {{ number_format($voidAudit->totalVoidAmount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">Total nilai transaksi yang dibatalkan</div>
        </div>

        <div class="rounded-[16px] {{ $voidAudit->voidAfterPrintCount > 0 ? 'bg-[#FF3B30]/5 border-[#FF3B30]/20' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5' }} border p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold {{ $voidAudit->voidAfterPrintCount > 0 ? 'text-[#FF3B30]' : 'text-black/50 dark:text-white/50' }} uppercase tracking-wider">Void Setelah Cetak Struk</span>
                @if ($voidAudit->voidAfterPrintCount > 0)
                    <span class="px-2 py-0.5 rounded-full bg-[#FF3B30] text-white text-[10px] font-bold">RED FLAG</span>
                @endif
            </div>
            <div class="text-[22px] font-bold tabular-nums {{ $voidAudit->voidAfterPrintCount > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} mt-2">
                {{ number_format($voidAudit->voidAfterPrintCount, 0, ',', '.') }} <span class="text-[14px] font-normal text-black/40">kejadian</span>
            </div>
            <div class="text-[11px] {{ $voidAudit->voidAfterPrintCount > 0 ? 'text-[#FF3B30]/80 font-medium' : 'text-black/40 dark:text-white/40' }} mt-1">
                Rp {{ number_format($voidAudit->voidAfterPrintAmount, 0, ',', '.') }} potensi kebocoran kas
            </div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Cetak Ulang (Reprint)</span>
            <div class="text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] mt-2">
                {{ number_format($voidAudit->totalReprintEvents, 0, ',', '.') }} <span class="text-[14px] font-normal text-black/40">kali</span>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">Frekuensi cetak duplikat nota</div>
        </div>
    </div>

    {{-- Cashier Risk Scoring Table --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-black/5 dark:border-white/5">
            <h3 class="text-[15px] font-bold text-black dark:text-white">Pemeringkatan Risiko Kasir (Cashier Risk Scoring)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Deteksi anomali rasio pembatalan dan cetak ulang nota per staf</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Kasir</th>
                        <th class="py-3 px-4 text-center">Total Order</th>
                        <th class="py-3 px-4 text-center">Jumlah Void</th>
                        <th class="py-3 px-4 text-right">Nominal Void</th>
                        <th class="py-3 px-4 text-right">Rasio Void %</th>
                        <th class="py-3 px-4 text-center">Cetak Ulang</th>
                        <th class="py-3 px-4 text-center">Tingkat Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($voidAudit->cashierRankings as $rank)
                        @php
                            $riskBadges = [
                                'critical' => 'bg-[#FF3B30] text-white',
                                'high' => 'bg-[#FF9500] text-white',
                                'medium' => 'bg-[#FFCC00]/20 text-[#8F7000]',
                                'low' => 'bg-[#34C759]/10 text-[#34C759]',
                            ];
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                {{ $rank['cashier_name'] }}
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums text-black/70 dark:text-white/70">
                                {{ number_format($rank['total_orders'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold tabular-nums text-[#FF3B30]">
                                {{ number_format($rank['void_count'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black dark:text-white">
                                Rp {{ number_format($rank['void_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ $rank['void_rate_percent'] > 5 ? 'text-[#FF3B30]' : 'text-black/70 dark:text-white/70' }}">
                                {{ number_format($rank['void_rate_percent'], 1) }}%
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums text-black/70 dark:text-white/70">
                                {{ number_format($rank['reprint_count'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $riskBadges[$rank['risk_level']] ?? 'bg-black/5 text-black/60' }}">
                                    {{ $rank['risk_level'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                                Tidak ada pembatalan transaksi tercatat
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
