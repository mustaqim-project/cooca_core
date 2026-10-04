{{-- TAB 3: PRODUCTS & MENU ITEM ANALYTICS --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Performa Penjualan & Profitabilitas Produk</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Analisis kuantitas terjual, perolehan omzet, dan margin laba kotor per item</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $topProducts->count() }}</span> produk terjual
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Nama Produk & SKU</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4 text-center">Qty Terjual</th>
                    <th class="py-3 px-4 text-right">Harga Jual Rata-rata</th>
                    <th class="py-3 px-4 text-right">Total Penjualan</th>
                    <th class="py-3 px-4 text-right">Total HPP Modal</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-right">Margin %</th>
                    <th class="py-3 px-4 text-right">Kontribusi Omzet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($topProducts as $idx => $prod)
                    @php
                        $marginClass = $prod->margin >= 50.0 ? 'text-[#34C759] dark:text-[#30D158]' : ($prod->margin >= 20.0 ? 'text-[#007AFF]' : ($prod->margin >= 0 ? 'text-[#FF9500]' : 'text-[#FF3B30]'));
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        <td class="py-3 px-4 text-[12px] text-black/40 dark:text-white/40 font-mono">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-black dark:text-white">{{ $prod->name }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40 font-mono">{{ $prod->sku ?? $prod->code ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-[12px] text-black/70 dark:text-white/70">
                            {{ $prod->category ?? 'Umum' }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold tabular-nums text-black dark:text-white">
                            {{ number_format($prod->qty, 1) }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format($prod->avg_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format($prod->sales, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                            Rp {{ number_format($prod->cogs, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format($prod->profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold tabular-nums {{ $marginClass }}">
                            {{ number_format($prod->margin, 1) }}%
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60 text-[12px]">
                            {{ number_format($prod->share, 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                            Tidak ada data penjualan produk pada filter aktif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
