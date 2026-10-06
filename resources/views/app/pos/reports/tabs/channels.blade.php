{{-- TAB 14: SALES CHANNELS & ONLINE FOOD DELIVERY (F&B) --}}
@php
    $totalChannelOrders = (int) $channelSales->sum('orders_count');
    $totalGrossGMV = (float) $channelSales->sum('gross_sales');
    $totalDiscountsAll = (float) $channelSales->sum('total_discount');
    $totalNetSales = (float) $channelSales->sum('net_sales');
    $totalPlatformFees = (float) $channelSales->sum('platform_fee_amount');
    $totalNetPayout = (float) $channelSales->sum('net_merchant_payout');
    $totalHppAll = (float) $channelSales->sum('total_hpp');
    $totalRealProfit = (float) $channelSales->sum('real_gross_profit');
    $overallRealMargin = \App\Support\Math\FinancialMath::calculateMargin($totalRealProfit, $totalNetPayout);
    $overallAov = \App\Support\Math\FinancialMath::safeDivide($totalNetSales, (float) $totalChannelOrders);

    $onlineDeliverySales = $channelSales->filter(fn($c) => in_array($c->sales_channel, ['shopeefood', 'gofood', 'grabfood']) || ($c->channel_type ?? '') === 'online_delivery');
    $directSales = $channelSales->filter(fn($c) => !in_array($c->sales_channel, ['shopeefood', 'gofood', 'grabfood']) && ($c->channel_type ?? '') !== 'online_delivery');

    $onlineOrdersCount = (int) $onlineDeliverySales->sum('orders_count');
    $onlineGrossGMV = (float) $onlineDeliverySales->sum('gross_sales');
    $onlinePlatformFees = (float) $onlineDeliverySales->sum('platform_fee_amount');
    $onlineNetPayout = (float) $onlineDeliverySales->sum('net_merchant_payout');
@endphp

<div class="space-y-6">
    {{-- 1. BENTO APPLE HIG TOP 4 KPI CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Card 1: Total Omzet Bruto (GMV) --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Omzet Bruto (GMV)</span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white">
                    Rp {{ number_format($totalGrossGMV > 0 ? $totalGrossGMV : $totalNetSales, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 tabular-nums">
                    {{ number_format($totalChannelOrders, 0, ',', '.') }} pesanan • {{ $channelSales->count() }} saluran aktif
                </div>
            </div>
        </div>

        {{-- Card 2: Estimasi Komisi Platform Mitra (MDR Ojol) --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Potongan Komisi Platform</span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#EE4D2D]"></span>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                    {{ $totalPlatformFees > 0 ? '-Rp ' . number_format($totalPlatformFees, 0, ',', '.') : 'Rp 0' }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 tabular-nums truncate">
                    ShopeeFood 20% • GoFood 20% • GrabFood 25%
                </div>
            </div>
        </div>

        {{-- Card 3: Net Payout Hak Resto (Dana Bersih Cair) --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Net Payout Hak Resto</span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                    Rp {{ number_format($totalNetPayout, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 tabular-nums">
                    Estimasi dana bersih masuk rekening resto
                </div>
            </div>
        </div>

        {{-- Card 4: Laba Bersih Riil & Margin % --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col justify-between shadow-xs transition-all hover:border-black/10 dark:hover:border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Laba Bersih Riil Saluran</span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#AF52DE]"></span>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2]">
                    Rp {{ number_format($totalRealProfit, 0, ',', '.') }}
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[11px]">
                    <span class="font-bold text-[#AF52DE] dark:text-[#BF5AF2] tabular-nums">Margin Riil: {{ number_format($overallRealMargin, 1) }}%</span>
                    <span class="text-black/40 dark:text-white/40">(Setelah HPP & Komisi)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SALURAN PENJUALAN COMPARATIVE BENTO BARS --}}
    @if ($channelSales->isNotEmpty())
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Komposisi Pendapatan per Saluran</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Proporsi omzet dan perbandingan penjualan langsung kasir vs layanan pesan-antar online</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-[11px] font-medium">
                    <span class="px-2.5 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                        Langsung: {{ number_format($directSales->sum('net_sales') > 0 && $totalNetSales > 0 ? ($directSales->sum('net_sales') / $totalNetSales) * 100 : 0, 1) }}%
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-[#EE4D2D]/10 text-[#EE4D2D] border border-[#EE4D2D]/20">
                        Ojol Delivery: {{ number_format($onlineDeliverySales->sum('net_sales') > 0 && $totalNetSales > 0 ? ($onlineDeliverySales->sum('net_sales') / $totalNetSales) * 100 : 0, 1) }}%
                    </span>
                </div>
            </div>

            {{-- Multi-Segment Horizontal Stacked Bar --}}
            <div class="w-full h-3 bg-black/[0.04] dark:bg-white/[0.06] rounded-full overflow-hidden flex gap-0.5">
                @foreach ($channelSales as $chBar)
                    @php
                        $barPct = $totalNetSales > 0 ? ($chBar->net_sales / $totalNetSales) * 100 : 0;
                    @endphp
                    @if ($barPct > 0)
                        <div class="h-full transition-all duration-300 rounded-sm"
                             style="width: {{ $barPct }}%; background-color: {{ $chBar->badge_bg ?? '#007AFF' }};"
                             title="{{ $chBar->channel_name }}: {{ number_format($barPct, 1) }}%"></div>
                    @endif
                @endforeach
            </div>

            {{-- Brand Badges Legend Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-4 pt-3 border-t border-black/5 dark:border-white/5">
                @foreach ($channelSales as $chLegend)
                    @php
                        $share = (float) ($chLegend->contribution_percent ?? $chLegend->share_percent ?? 0);
                    @endphp
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $chLegend->badge_bg ?? '#8E8E93' }};"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-[12px] font-semibold text-black dark:text-white truncate">{{ $chLegend->channel_name }}</div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums">
                                Rp {{ number_format($chLegend->net_sales, 0, ',', '.') }} ({{ number_format($share, 1) }}%)
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 3. PERFORMA SALURAN PENJUALAN COMPREHENSIVE TABLE --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">Rincian Finansial Saluran Penjualan & Ojol Delivery</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Matriks komprehensif omzet bruto, potongan promo, komisi platform mitra, net payout resto, modal HPP, dan margin riil</p>
            </div>
            <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
                Total: <span class="font-bold text-black dark:text-white">{{ $channelSales->count() }}</span> saluran terdata
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-4">Saluran Penjualan</th>
                        <th class="py-3 px-4 text-center">Tipe Kanal</th>
                        <th class="py-3 px-4 text-center">Jumlah Order</th>
                        <th class="py-3 px-4 text-right">Omzet Bruto (GMV)</th>
                        <th class="py-3 px-4 text-right">Diskon Promo</th>
                        <th class="py-3 px-4 text-right">Penjualan Bersih</th>
                        <th class="py-3 px-4 text-center">Komisi Platform</th>
                        <th class="py-3 px-4 text-right">Potongan Komisi (Rp)</th>
                        <th class="py-3 px-4 text-right">Net Payout (Dana Cair)</th>
                        <th class="py-3 px-4 text-right">Total HPP (BOM)</th>
                        <th class="py-3 px-4 text-right">Laba Bersih Riil</th>
                        <th class="py-3 px-4 text-right">Margin Riil %</th>
                        <th class="py-3 px-4 text-right">AOV (Nota)</th>
                        <th class="py-3 px-4 text-right">Kontribusi %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($channelSales as $ch)
                        @php
                            $feePct = (float) ($ch->platform_fee_percent ?? 0);
                            $feeAmt = (float) ($ch->platform_fee_amount ?? 0);
                            $payout = (float) ($ch->net_merchant_payout ?? $ch->net_sales);
                            $realProfit = (float) ($ch->real_gross_profit ?? ($payout - ($ch->total_hpp ?? 0)));
                            $realMargin = (float) ($ch->real_margin_percent ?? 0);
                            $sharePct = (float) ($ch->contribution_percent ?? $ch->share_percent ?? 0);
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            {{-- Saluran Penjualan with Official Brand Badge --}}
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-[8px] flex items-center justify-center text-white text-[12px] font-bold shadow-xs shrink-0"
                                          style="background-color: {{ $ch->badge_bg ?? '#007AFF' }};">
                                        @if ($ch->sales_channel === 'shopeefood')
                                            SF
                                        @elseif ($ch->sales_channel === 'gofood')
                                            GF
                                        @elseif ($ch->sales_channel === 'grabfood')
                                            GB
                                        @elseif ($ch->sales_channel === 'storefront')
                                            WEB
                                        @elseif (in_array($ch->sales_channel, ['dine_in', 'dinein']))
                                            DI
                                        @elseif (in_array($ch->sales_channel, ['takeaway', 'take_away']))
                                            TA
                                        @else
                                            POS
                                        @endif
                                    </span>
                                    <div>
                                        <div class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                            <span>{{ $ch->channel_name }}</span>
                                        </div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40 font-mono">{{ $ch->sales_channel }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Tipe Kanal Badge --}}
                            <td class="py-3 px-4 text-center">
                                @if (($ch->channel_type ?? '') === 'online_delivery' || in_array($ch->sales_channel, ['shopeefood', 'gofood', 'grabfood']))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20">
                                        Ojol Mitra
                                    </span>
                                @elseif (($ch->channel_type ?? '') === 'direct_online' || $ch->sales_channel === 'storefront')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                        Toko Online
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                                        Langsung (POS)
                                    </span>
                                @endif
                            </td>

                            {{-- Jumlah Order --}}
                            <td class="py-3 px-4 text-center font-bold tabular-nums text-[#007AFF]">
                                {{ number_format($ch->orders_count, 0, ',', '.') }}
                            </td>

                            {{-- Omzet Bruto (GMV) --}}
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format($ch->gross_sales, 0, ',', '.') }}
                            </td>

                            {{-- Diskon Promo --}}
                            <td class="py-3 px-4 text-right tabular-nums {{ $ch->total_discount > 0 ? 'text-[#FF3B30] dark:text-[#FF453A] font-medium' : 'text-black/40 dark:text-white/40' }}">
                                {{ $ch->total_discount > 0 ? '-Rp ' . number_format($ch->total_discount, 0, ',', '.') : '-' }}
                            </td>

                            {{-- Penjualan Bersih (Net Sales) --}}
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                                Rp {{ number_format($ch->net_sales, 0, ',', '.') }}
                            </td>

                            {{-- Komisi Platform % --}}
                            <td class="py-3 px-4 text-center tabular-nums">
                                @if ($feePct > 0)
                                    <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-bold bg-[#EE4D2D]/10 text-[#EE4D2D] border border-[#EE4D2D]/20">
                                        {{ number_format($feePct, 0) }}%
                                    </span>
                                @else
                                    <span class="text-[11px] text-black/40 dark:text-white/40 font-medium">0% (Bebas Fee)</span>
                                @endif
                            </td>

                            {{-- Potongan Komisi (Rp) --}}
                            <td class="py-3 px-4 text-right tabular-nums font-semibold {{ $feeAmt > 0 ? 'text-[#FF3B30] dark:text-[#FF453A]' : 'text-black/40 dark:text-white/40' }}">
                                {{ $feeAmt > 0 ? '-Rp ' . number_format($feeAmt, 0, ',', '.') : '-' }}
                            </td>

                            {{-- Net Payout Hak Resto --}}
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($payout, 0, ',', '.') }}
                            </td>

                            {{-- Total HPP (BOM) --}}
                            <td class="py-3 px-4 text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A] font-medium">
                                Rp {{ number_format($ch->total_hpp, 0, ',', '.') }}
                            </td>

                            {{-- Laba Bersih Riil --}}
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ $realProfit >= 0 ? 'text-[#34C759] dark:text-[#30D158]' : 'text-[#FF3B30] dark:text-[#FF453A]' }}">
                                Rp {{ number_format($realProfit, 0, ',', '.') }}
                            </td>

                            {{-- Margin Riil % --}}
                            <td class="py-3 px-4 text-right tabular-nums">
                                <span class="px-2 py-0.5 rounded-[6px] text-[11px] font-bold {{ $realMargin >= 40 ? 'bg-[#34C759]/10 text-[#34C759]' : ($realMargin >= 20 ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#FF9500]/10 text-[#FF9500]') }}">
                                    {{ number_format($realMargin, 1) }}%
                                </span>
                            </td>

                            {{-- AOV --}}
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format($ch->aov, 0, ',', '.') }}
                            </td>

                            {{-- Porsi Omzet % --}}
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-black/60 dark:text-white/60 text-[12px]">
                                {{ number_format($sharePct, 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                Tidak ada data saluran penjualan pada filter aktif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($channelSales->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-black/10 dark:border-white/10 bg-black/[0.03] dark:bg-white/[0.03] font-bold text-black dark:text-white">
                            <td class="py-3 px-4" colspan="2">
                                TOTAL REKAPITULASI
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums text-[#007AFF]">
                                {{ number_format($totalChannelOrders, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format($totalGrossGMV, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                {{ $totalDiscountsAll > 0 ? '-Rp ' . number_format($totalDiscountsAll, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums">
                                Rp {{ number_format($totalNetSales, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center text-black/40 dark:text-white/40">
                                —
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                {{ $totalPlatformFees > 0 ? '-Rp ' . number_format($totalPlatformFees, 0, ',', '.') : 'Rp 0' }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($totalNetPayout, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                                Rp {{ number_format($totalHppAll, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($totalRealProfit, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#34C759] dark:text-[#30D158]">
                                {{ number_format($overallRealMargin, 1) }}%
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                Rp {{ number_format($overallAov, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums">
                                100.0%
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- 4. FINANCIAL EDUCATION & RECONCILIATION EXPLANATORY CARD (APPLE HIG) --}}
    <div class="rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-5 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-[10px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                <i data-lucide="calculator" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-[14px] font-bold text-black dark:text-white">Standar Perhitungan Finansial Online Food Delivery (F&B)</h4>
                <p class="text-[12px] text-black/70 dark:text-white/70 mt-1">
                    Untuk memberikan visibilitas profitabilitas yang akurat bagi pemilik resto & kafe, sistem COOCA menghitung pembagian hasil kanal pesan-antar berdasarkan rumus resmi:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                    <div class="p-3 rounded-[12px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/5">
                        <div class="text-[11px] font-bold text-[#EE4D2D] uppercase tracking-wider">1. Komisi Platform Mitra (MDR)</div>
                        <div class="text-[12px] text-black/80 dark:text-white/80 mt-1 font-mono">
                            Komisi = Penjualan Murni × % Fee (ShopeeFood 20%, GoFood 20%, GrabFood 25%)
                        </div>
                    </div>
                    <div class="p-3 rounded-[12px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/5">
                        <div class="text-[11px] font-bold text-[#34C759] uppercase tracking-wider">2. Net Payout Hak Resto</div>
                        <div class="text-[12px] text-black/80 dark:text-white/80 mt-1 font-mono">
                            Dana Cair = Penjualan Bersih - Potongan Komisi Platform
                        </div>
                    </div>
                    <div class="p-3 rounded-[12px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/5">
                        <div class="text-[11px] font-bold text-[#AF52DE] uppercase tracking-wider">3. Laba Bersih Riil Resto</div>
                        <div class="text-[12px] text-black/80 dark:text-white/80 mt-1 font-mono">
                            Laba Riil = Dana Cair Rekening - Total Modal HPP (BOM Cost)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
