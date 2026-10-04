{{-- TAB 2: TRANSACTIONS LEDGER --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Buku Besar Transaksi (Transactions Ledger)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Daftar lengkap nota pesanan POS beserta rincian finansial transaksional</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $transactions->total() }}</span> transaksi
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">No. Nota</th>
                    <th class="py-3 px-4">Tanggal & Jam</th>
                    <th class="py-3 px-4">Kasir & Outlet</th>
                    <th class="py-3 px-4">Pelanggan</th>
                    <th class="py-3 px-4 text-center">Qty</th>
                    <th class="py-3 px-4 text-right">Subtotal</th>
                    <th class="py-3 px-4 text-right">Diskon</th>
                    <th class="py-3 px-4 text-right">Pajak/Svc</th>
                    <th class="py-3 px-4 text-right">Total Akhir</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($transactions as $order)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4 font-mono font-semibold text-black dark:text-white text-[12px]">
                            <a href="{{ route('pos.orders.show', $order->id) }}" class="text-[#007AFF] hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-black/70 dark:text-white/70 text-[12px] whitespace-nowrap">
                            <div>{{ $order->order_date?->format('d/m/Y') ?? '-' }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40">{{ $order->created_at?->format('H:i') ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4 text-[12px]">
                            <div class="font-medium text-black dark:text-white">{{ $order->user?->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40">{{ $order->location?->name ?? 'Outlet Utama' }}</div>
                        </td>
                        <td class="py-3 px-4 text-[12px]">
                            <span class="text-black/80 dark:text-white/80">
                                {{ $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan Umum' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-medium tabular-nums text-black/70 dark:text-white/70">
                            {{ $order->items->count() }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                            @php
                                $disc = (float) $order->discount_amount + (float) $order->voucher_discount_amount + (float) $order->points_discount_amount;
                            @endphp
                            {{ $disc > 0 ? '-Rp ' . number_format($disc, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            @php
                                $taxSvc = (float) $order->tax_amount + (float) $order->service_charge_amount;
                            @endphp
                            {{ $taxSvc > 0 ? 'Rp ' . number_format($taxSvc, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-medium tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format((float) $order->total_gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $statusBadges = [
                                    'completed' => 'bg-[#34C759]/10 text-[#34C759]',
                                    'partial_refund' => 'bg-[#FF9500]/10 text-[#FF9500]',
                                    'refunded' => 'bg-[#FF3B30]/10 text-[#FF3B30]',
                                    'voided' => 'bg-[#FF3B30]/10 text-[#FF3B30]',
                                    'draft_held' => 'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider {{ $statusBadges[$order->status] ?? 'bg-black/5 text-black/60' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('pos.orders.show', $order->id) }}"
                                class="inline-flex items-center justify-center p-1.5 rounded-[8px] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all"
                                title="Lihat Rincian Nota">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada transaksi ditemukan pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($transactions->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="text-[12px] text-black/50 dark:text-white/50">
                Menampilkan halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}
            </div>
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    @endif
</div>
