{{-- TAB 2: TRANSACTIONS LEDGER (SADAR SALURAN & 20 INDUSTRI) --}}
<div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-[15px] font-bold text-black dark:text-white">Buku Besar Transaksi (Transactions Ledger)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">Daftar lengkap nota pesanan POS dengan identifikasi saluran penjualan, nomor meja/ojol, dan metadata spesifik 20 industri</p>
        </div>
        <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
            Total: <span class="font-bold text-black dark:text-white">{{ $transactions->total() }}</span> transaksi
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse whitespace-nowrap">
            <thead>
                <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                    <th class="py-3 px-4">No. Nota & Saluran</th>
                    <th class="py-3 px-4">Tanggal & Waktu</th>
                    <th class="py-3 px-4">Kasir & Outlet</th>
                    <th class="py-3 px-4">Pelanggan & Metadata Industri</th>
                    <th class="py-3 px-4 text-center">Qty Item</th>
                    <th class="py-3 px-4 text-right">Subtotal</th>
                    <th class="py-3 px-4 text-right">Diskon Promo</th>
                    <th class="py-3 px-4 text-right">Pajak / Svc</th>
                    <th class="py-3 px-4 text-right">Total Akhir</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5 dark:divide-white/5">
                @forelse ($transactions as $order)
                    @php
                        $disc = (float) $order->discount_amount + (float) $order->voucher_discount_amount + (float) $order->points_discount_amount;
                        $taxSvc = (float) $order->tax_amount + (float) $order->service_charge_amount;
                        $channel = strtolower((string) ($order->sales_channel ?? 'pos_direct'));

                        // Status Badge Colors
                        $statusBadges = [
                            'completed' => 'bg-[#34C759]/10 text-[#34C759]',
                            'partial_refund' => 'bg-[#FF9500]/10 text-[#FF9500]',
                            'refunded' => 'bg-[#FF3B30]/10 text-[#FF3B30]',
                            'voided' => 'bg-[#FF3B30]/10 text-[#FF3B30]',
                            'draft_held' => 'bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60',
                        ];

                        // Channel Badges & Names
                        $channelMeta = [
                            'shopeefood' => ['name' => 'ShopeeFood', 'bg' => '#EE4D2D', 'tag' => 'SF'],
                            'gofood' => ['name' => 'GoFood', 'bg' => '#EE2724', 'tag' => 'GF'],
                            'grabfood' => ['name' => 'GrabFood', 'bg' => '#00B14F', 'tag' => 'GB'],
                            'storefront' => ['name' => 'Toko Online', 'bg' => '#007AFF', 'tag' => 'WEB'],
                            'dine_in' => ['name' => 'Dine-In', 'bg' => '#AF52DE', 'tag' => 'MEJA'],
                            'takeaway' => ['name' => 'Takeaway', 'bg' => '#FF9500', 'tag' => 'TA'],
                            'pos_direct' => ['name' => 'Kasir POS', 'bg' => '#34C759', 'tag' => 'POS'],
                            'pos' => ['name' => 'Kasir POS', 'bg' => '#34C759', 'tag' => 'POS'],
                        ];
                        $chInfo = $channelMeta[$channel] ?? ['name' => ucfirst($channel), 'bg' => '#8E8E93', 'tag' => 'POS'];
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                        {{-- No. Nota & Channel Badge --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded-[5px] text-[10px] font-bold text-white shrink-0"
                                      style="background-color: {{ $chInfo['bg'] }};">
                                    {{ $chInfo['tag'] }}
                                </span>
                                <div>
                                    <button type="button"
                                            @click="$dispatch('open-pos-order-detail', { orderId: '{{ $order->id }}' })"
                                            class="text-[#007AFF] hover:underline font-mono font-bold text-[12.5px] text-left cursor-pointer">
                                        {{ $order->order_number }}
                                    </button>
                                    @if ($order->external_order_ref)
                                        <div class="text-[10px] text-black/50 dark:text-white/50 font-mono">
                                            Ref: {{ $order->external_order_ref }}
                                        </div>
                                    @elseif ($order->table_or_reference)
                                        <div class="text-[10px] text-black/50 dark:text-white/50">
                                            {{ $order->table_or_reference }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Tanggal & Waktu --}}
                        <td class="py-3 px-4 text-black/70 dark:text-white/70 text-[12px]">
                            <div>{{ $order->order_date?->format('d/m/Y') ?? '-' }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">{{ $order->created_at?->format('H:i:s') ?? '' }}</div>
                        </td>

                        {{-- Kasir & Outlet --}}
                        <td class="py-3 px-4 text-[12px]">
                            <div class="font-medium text-black dark:text-white">{{ $order->user?->name ?? 'Kasir Utama' }}</div>
                            <div class="text-[11px] text-black/40 dark:text-white/40">{{ $order->location?->name ?? 'Outlet Utama' }}</div>
                        </td>

                        {{-- Pelanggan & Metadata 20 Industri --}}
                        <td class="py-3 px-4 text-[12px]">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan Umum' }}
                            </div>

                            {{-- Dynamic 20 Industry Context Badges --}}
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                {{-- Bengkel Otomotif: Nopol & KM --}}
                                @if ($order->vehicle_license_plate)
                                    <span class="px-2 py-0.5 rounded-[5px] bg-black/5 dark:bg-white/10 text-black dark:text-white font-mono font-bold text-[10px] border border-black/10 dark:border-white/10">
                                        🚗 {{ $order->vehicle_license_plate }}
                                    </span>
                                @endif
                                @if ($order->vehicle_mileage)
                                    <span class="text-[10px] text-black/50 dark:text-white/50 tabular-nums">
                                        {{ number_format($order->vehicle_mileage, 0, ',', '.') }} km
                                    </span>
                                @endif
                                @if ($order->technician)
                                    <span class="text-[10px] text-[#007AFF] font-medium">
                                        • Mekanik: {{ $order->technician->name }}
                                    </span>
                                @endif

                                {{-- Laundry Kiloan: Berat & Rak --}}
                                @if ($order->laundry_weight_kg)
                                    <span class="px-2 py-0.5 rounded-[5px] bg-[#007AFF]/10 text-[#007AFF] font-bold text-[10px] border border-[#007AFF]/20">
                                        🧺 {{ $order->laundry_weight_kg }} kg
                                    </span>
                                @endif
                                @if ($order->rack_location)
                                    <span class="px-1.5 py-0.5 rounded-[5px] bg-[#FF9500]/10 text-[#FF9500] font-mono text-[10px]">
                                        Rak {{ $order->rack_location }}
                                    </span>
                                @endif

                                {{-- Apotek / Resep Dokter / Serial --}}
                                @php
                                    $hasBatch = $order->items->whereNotNull('batch_number')->isNotEmpty();
                                    $hasSerial = $order->items->whereNotNull('serial_number')->isNotEmpty();
                                @endphp
                                @if ($hasBatch)
                                    <span class="px-1.5 py-0.5 rounded-[5px] bg-[#34C759]/10 text-[#34C759] text-[10px]">
                                        💊 Batch Terdata
                                    </span>
                                @endif
                                @if ($hasSerial)
                                    <span class="px-1.5 py-0.5 rounded-[5px] bg-[#AF52DE]/10 text-[#AF52DE] font-mono text-[10px]">
                                        🏷️ IMEI/Serial
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Qty Item --}}
                        <td class="py-3 px-4 text-center font-bold tabular-nums text-black/80 dark:text-white/80">
                            {{ $order->items->count() }}
                        </td>

                        {{-- Subtotal --}}
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}
                        </td>

                        {{-- Diskon Promo --}}
                        <td class="py-3 px-4 text-right tabular-nums {{ $disc > 0 ? 'text-[#FF3B30] dark:text-[#FF453A] font-medium' : 'text-black/40 dark:text-white/40' }}">
                            {{ $disc > 0 ? '-Rp ' . number_format($disc, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Pajak / Svc --}}
                        <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                            {{ $taxSvc > 0 ? 'Rp ' . number_format($taxSvc, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Total Akhir --}}
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                            Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                        </td>

                        {{-- Laba Kotor --}}
                        <td class="py-3 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                            Rp {{ number_format((float) $order->total_gross_profit, 0, ',', '.') }}
                        </td>

                        {{-- Status Pill --}}
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider {{ $statusBadges[$order->status] ?? 'bg-black/5 text-black/60' }}">
                                {{ $order->status }}
                            </span>
                        </td>

                        {{-- Aksi Quick-View --}}
                        <td class="py-3 px-4 text-center">
                            <button type="button"
                                    @click="$dispatch('open-pos-order-detail', { orderId: '{{ $order->id }}' })"
                                    class="inline-flex items-center justify-center p-1.5 rounded-[8px] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer"
                                    title="Quick-View Rincian Nota">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
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
                Menampilkan halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }} (Total {{ $transactions->total() }} nota)
            </div>
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    @endif
</div>
