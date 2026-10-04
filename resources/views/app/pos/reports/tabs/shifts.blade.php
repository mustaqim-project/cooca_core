{{-- TAB 11: SHIFTS & CASH RECONCILIATION --}}
<div class="space-y-6">
    {{-- 3-Way Reconciliation Status Card --}}
    <div class="rounded-[16px] {{ $reconciliation->isBalanced ? 'bg-[#34C759]/10 border-[#34C759]/20' : 'bg-[#FF9500]/10 border-[#FF9500]/20' }} border p-5 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full {{ $reconciliation->isBalanced ? 'bg-[#34C759] text-white' : 'bg-[#FF9500] text-white' }} flex items-center justify-center">
                    <i data-lucide="{{ $reconciliation->isBalanced ? 'check' : 'alert-triangle' }}" class="w-5 h-5"></i>
                </span>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">
                        Status Rekonsiliasi 3-Arah: {{ $reconciliation->isBalanced ? 'SEIMBANG & TERTIB (Balanced)' : 'TERDAPAT ANOMALI / SELISIH KAS' }}
                    </h3>
                    <p class="text-[12px] text-black/60 dark:text-white/60">
                        {{ $reconciliation->isBalanced ? 'Seluruh nota pesanan, penerimaan pembayaran kasir, dan fisik register kas sesuai 100% tanpa selisih.' : 'Terdapat perbedaan antara catatan pesanan, pembayaran, atau fisik kas register yang perlu diverifikasi.' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4 text-[13px]">
                <div class="text-right">
                    <div class="text-[11px] text-black/50 dark:text-white/50">Selisih Kas Fisik</div>
                    <div class="font-bold tabular-nums {{ abs($reconciliation->shiftCashDiscrepancy) < 0.01 ? 'text-[#34C759]' : 'text-[#FF3B30]' }}">
                        Rp {{ number_format($reconciliation->shiftCashDiscrepancy, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Anomaly List if Any --}}
        @if ($reconciliation->anomalies->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 space-y-2">
                <h4 class="text-[12px] font-bold text-black/70 dark:text-white/70 uppercase tracking-wider">Daftar Temuan Anomali:</h4>
                <div class="space-y-1.5">
                    @foreach ($reconciliation->anomalies as $anomaly)
                        <div class="p-2.5 rounded-[10px] bg-white/70 dark:bg-black/40 border border-black/5 dark:border-white/5 flex items-start gap-2 text-[12px]">
                            <i data-lucide="{{ $anomaly['severity'] === 'critical' ? 'alert-octagon' : 'alert-circle' }}" class="w-4 h-4 text-[#FF3B30] shrink-0 mt-0.5"></i>
                            <div class="flex-1 min-w-0">
                                <span class="font-bold text-black dark:text-white">{{ $anomaly['type'] }}:</span>
                                <span class="text-black/80 dark:text-white/80">{{ $anomaly['description'] }}</span>
                                <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Rekomendasi: {{ $anomaly['action'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Shift Register Ledger --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">Rekapitulasi Shift Kasir & Fisik Kas</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Daftar sesi shift kasir dengan perbandingan saldo awal, penjualan tunai, dan saldo akhir aktual</p>
            </div>
            <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
                Total: <span class="font-bold text-black dark:text-white">{{ $shiftReconciliation->count() }}</span> shift
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-4">Kasir & Register</th>
                        <th class="py-3 px-4">Jam Buka - Tutup</th>
                        <th class="py-3 px-4 text-right">Modal Awal</th>
                        <th class="py-3 px-4 text-right">Penjualan Tunai</th>
                        <th class="py-3 px-4 text-right">Kas Masuk/Keluar</th>
                        <th class="py-3 px-4 text-right">Kas Seharusnya</th>
                        <th class="py-3 px-4 text-right">Fisik Aktual</th>
                        <th class="py-3 px-4 text-right">Selisih (Variance)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($shiftReconciliation as $shift)
                        @php
                            $diff = (float) $shift['cash_difference'];
                            $diffClass = abs($diff) < 0.01 ? 'text-[#34C759]' : ($diff < 0 ? 'text-[#FF3B30]' : 'text-[#FF9500]');
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-black dark:text-white">{{ $shift['cashier_name'] }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40">{{ $shift['register_name'] }} • {{ $shift['location_name'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-[12px] text-black/70 dark:text-white/70">
                                <div>{{ $shift['opened_at'] }}</div>
                                <div class="text-[11px] text-black/40 dark:text-white/40">{{ $shift['closed_at'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format($shift['opening_cash'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                                Rp {{ number_format($shift['total_cash_sales'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70 text-[12px]">
                                +Rp {{ number_format($shift['total_cash_in'], 0, ',', '.') }}<br>
                                -Rp {{ number_format($shift['total_cash_out'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format($shift['closing_cash_expected'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                                Rp {{ number_format($shift['closing_cash_actual'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ $diffClass }}">
                                {{ $shift['variance_label'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider {{ abs($diff) < 0.01 ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]' }}">
                                    {{ $shift['audit_status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                Tidak ada data shift kasir tercatat pada filter aktif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
