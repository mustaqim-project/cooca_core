@extends('layouts.app', [
    'title' => 'Detail Pesanan #' . $order->order_number . ' - Cooca',
    'headerTitle' => 'Pesanan #' . $order->order_number,
    'headerSubtitle' => 'Kelola rincian pesanan online, verifikasi bukti bayar, dan perbarui status pemenuhan',
])

@section('content')
    <div class="space-y-6 pb-28 sm:pb-32 lg:pb-10 max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" x-data="{ rejectModalOpen: false, cancelModalOpen: false, imageModalOpen: false, verifyModalOpen: false, activeImageUrl: '' }">

        {{-- FLASH NOTIFICATIONS --}}
        @if (session('success'))
            <div
                class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 text-[13.5px] font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] flex items-center gap-3 text-[13.5px] font-medium">
                <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- TOP NAVIGATION & STATUS BAR --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('storefront.orders.index') }}"
                    class="w-10 h-10 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/70 dark:text-white/70 transition">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">Pesanan
                            #{{ $order->order_number }}</h1>
                        @php
                            $statusStyles = [
                                'pending_payment' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                                'proof_submitted' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                                'paid' => 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/20',
                                'processing' => 'bg-[#5856D6]/10 text-[#5856D6] border-[#5856D6]/20',
                                'ready' => 'bg-[#32ADE6]/10 text-[#32ADE6] border-[#32ADE6]/20',
                                'completed' =>
                                    'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 border-black/10',
                                'cancelled' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                                'payment_rejected' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                            ];
                        @endphp
                        <span
                            class="px-3 py-1 rounded-full text-[11.5px] font-bold border uppercase tracking-wider {{ $statusStyles[$order->status] ?? 'bg-black/5 text-black/60' }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                        Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB &bull;
                        <span class="font-medium text-black/80 dark:text-white/80">Tipe:
                            {{ strtoupper(str_replace('_', ' ', $order->order_type)) }}</span>
                    </p>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ url("/b/{$business->slug}/order/{$order->tracking_token}") }}" target="_blank"
                    class="h-9 px-3.5 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12.5px] font-semibold transition flex items-center gap-2">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Tampilan Pelanggan</span>
                </a>

                @if ($order->customer_phone)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        $waMessage = rawurlencode(
                            "Halo Kak {$order->customer_name}, terima kasih telah memesan di {$business->name}! Terkait pesanan #{$order->order_number}...",
                        );
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                        class="h-9 px-3.5 rounded-full bg-[#25D366]/15 hover:bg-[#25D366]/25 text-[#128C7E] dark:text-[#25D366] text-[12.5px] font-semibold transition flex items-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Hubungi WA</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- SCHEDULED ORDER ALERT / BADGE --}}
        @if ($order->scheduled_date)
            <div
                class="p-4 rounded-[20px] bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#007AFF] text-white flex items-center justify-center shrink-0">
                        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[14.5px] font-bold text-black dark:text-white tracking-tight">Pesanan Terjadwal:
                            {{ $order->scheduled_date->translatedFormat('l, d F Y') }}</h3>
                        <p class="text-[12.5px] text-black/60 dark:text-white/60">
                            Slot Waktu Pengiriman/Pengambilan: <span
                                class="font-bold text-[#007AFF]">{{ $order->scheduled_time_slot ?? 'Fleksibel' }}</span>
                        </p>
                    </div>
                </div>
                <span
                    class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-[#007AFF] text-white">Terjadwal</span>
            </div>
        @endif

        {{-- CUSTOMER PO / B2B BANNER --}}
        @if ($order->isCustomerPo())
            <div
                class="p-4 rounded-[20px] bg-[#5856D6]/10 border border-[#5856D6]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#5856D6] text-white flex items-center justify-center shrink-0">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[14.5px] font-bold text-black dark:text-white tracking-tight">
                                Customer PO: {{ $order->customer_po_number ?: 'PO Klien' }}
                            </h3>
                            @if ($order->company_name)
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#5856D6]/15 text-[#5856D6]">
                                    {{ $order->company_name }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                            Pesanan Institusi / B2B dengan faktur resmi dan pengiriman multi-drop terjadwal.
                        </p>
                    </div>
                </div>
                @if ($order->batches->isNotEmpty())
                    <div class="text-left sm:text-right shrink-0">
                        <span class="text-[11px] font-semibold uppercase text-black/45 dark:text-white/45 block">Progres
                            Drop</span>
                        <span class="text-[14px] font-bold text-[#5856D6]">
                            {{ $order->delivered_batches_count }} / {{ $order->total_batches_count }} Terkirim
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- GROUP ORDER / PESAN BERSAMA BANNER & KITCHEN PACKAGING BREAKDOWN --}}
        @if ($order->groupOrder)
            <div class="p-5 sm:p-6 rounded-[24px] bg-[#AF52DE]/10 border border-[#AF52DE]/25 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#AF52DE]/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#AF52DE] text-white flex items-center justify-center shrink-0 shadow-sm">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">
                                    Pesanan Bersama (Group Order): {{ $order->groupOrder->title }}
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/20 text-[#AF52DE]">
                                    Pesan Bareng
                                </span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                                Host Pembuat Grup: <strong class="text-black dark:text-white">{{ $order->groupOrder->host?->name ?? $order->customer_name }}</strong> &bull; Total {{ $order->groupOrder->items->groupBy('member_name')->count() }} Rekan Kantor
                            </p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0">
                        <span class="text-[11px] font-semibold uppercase text-black/45 dark:text-white/45 block">Total Porsi Bersama</span>
                        <span class="text-[15px] font-extrabold text-[#AF52DE] tabular-nums">
                            {{ (int) $order->groupOrder->items->sum('quantity') }} Porsi
                        </span>
                    </div>
                </div>

                {{-- COLLEAGUE MEAL PACKAGING BREAKDOWN FOR KITCHEN --}}
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-bold uppercase tracking-wider text-[#AF52DE] flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                            <span>Panduan Label Kotak Makanan (Kitchen Packaging Breakdown)</span>
                        </span>
                        <span class="text-[11.5px] text-black/50 dark:text-white/50">Tempelkan nama pemesan pada kemasan</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($order->groupOrder->items->groupBy('member_name') as $memberName => $memberItems)
                            <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 shadow-xs">
                                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-[#AF52DE]/15 text-[#AF52DE] font-bold text-[11px] flex items-center justify-center">
                                            {{ strtoupper(substr($memberName, 0, 1)) }}
                                        </div>
                                        <span class="font-bold text-[13.5px] text-black dark:text-white">{{ $memberName }}</span>
                                    </div>
                                    <span class="text-[11px] font-mono text-black/45 dark:text-white/45">
                                        {{ $memberItems->sum('quantity') }} item
                                    </span>
                                </div>
                                <div class="space-y-1.5 text-[12.5px]">
                                    @foreach ($memberItems as $mItem)
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="font-semibold text-black dark:text-white">{{ (float) $mItem->quantity }}x {{ $mItem->product?->name ?? 'Produk' }}</span>
                                                @if ($mItem->notes)
                                                    <p class="text-[11px] text-[#FF9500] font-medium italic">Catatan: {{ $mItem->notes }}</p>
                                                @endif
                                            </div>
                                            <span class="text-black/60 dark:text-white/60 tabular-nums text-[12px]">
                                                Rp {{ number_format($mItem->unit_price * $mItem->quantity, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- MAIN GRID: 2 COLUMNS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: ORDER ITEMS & CUSTOMER (8 COLS) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- REQUEST ORDER QUOTATION FORM (When pending_review) --}}
                @if ($order->order_type === 'request_order' && $order->status === 'pending_review')
                    <div
                        class="bg-gradient-to-br from-[#007AFF]/5 to-transparent dark:from-[#007AFF]/10 rounded-[24px] border-2 border-[#007AFF]/30 p-6 shadow-md space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-[#007AFF]/20">
                            <div class="w-9 h-9 rounded-[12px] bg-[#007AFF] text-white flex items-center justify-center">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Formulir
                                    Penawaran Harga (Quotation)</h2>
                                <p class="text-[12px] text-black/55 dark:text-white/55">Tentukan harga per item dan ongkos
                                    kirim sebelum pelanggan melakukan pembayaran.</p>
                            </div>
                        </div>

                        <form action="{{ route('storefront.orders.quote', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-3">
                                @foreach ($order->items as $idx => $it)
                                    <div
                                        class="p-3.5 rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex-1">
                                            <input type="hidden" name="items[{{ $idx }}][id]"
                                                value="{{ $it->id }}">
                                            <h4 class="text-[13.5px] font-bold text-black dark:text-white">
                                                {{ $it->product_name }}</h4>
                                            @if ($it->notes)
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50 italic mt-0.5">
                                                    "{{ $it->notes }}"</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <div>
                                                <label
                                                    class="block text-[10.5px] font-semibold text-black/50 dark:text-white/50 mb-0.5">Jumlah</label>
                                                <input type="number" step="0.01" min="0.01"
                                                    name="items[{{ $idx }}][quantity]"
                                                    value="{{ (float) $it->quantity }}"
                                                    class="w-20 h-9 px-2.5 rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white font-bold text-center tabular-nums">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10.5px] font-semibold text-black/50 dark:text-white/50 mb-0.5">Harga
                                                    Satuan (Rp)</label>
                                                <input type="number" step="100" min="0"
                                                    name="items[{{ $idx }}][unit_price]"
                                                    value="{{ (float) $it->unit_price }}" placeholder="0" required
                                                    class="w-36 h-9 px-2.5 rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white font-bold tabular-nums">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Ongkos
                                        Kirim (Rp)</label>
                                    <input type="number" step="100" min="0" name="shipping_cost"
                                        value="{{ (float) $order->shipping_cost }}" placeholder="0"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white font-bold tabular-nums">
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Rekening
                                        Pembayaran</label>
                                    <select name="payment_method_id"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white">
                                        <option value="">-- Rekening Standar Toko --</option>
                                        @foreach ($business->paymentMethods as $pm)
                                            <option value="{{ $pm->id }}"
                                                {{ $order->payment_method_id === $pm->id ? 'selected' : '' }}>
                                                {{ $pm->bank_name }} - {{ $pm->account_number }}
                                                ({{ $pm->account_holder }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Catatan
                                    Penawaran untuk Pelanggan</label>
                                <input type="text" name="notes"
                                    placeholder="Contoh: Sudah termasuk kemasan khusus dan garansi dingin."
                                    value="{{ $order->notes }}"
                                    class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white">
                            </div>

                            <button type="submit"
                                class="w-full h-11 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] transition flex items-center justify-center gap-2 shadow-sm">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Kirim Penawaran</span>
                            </button>
                        </form>
                    </div>
                @endif

                {{-- 1. ITEMS CARD --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10 mb-4">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="package" class="w-5 h-5 text-[#007AFF]"></i>
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Rincian Item
                                Pesanan</h2>
                        </div>
                        <span
                            class="text-[12.5px] font-medium text-black/50 dark:text-white/50">{{ $order->items->count() }}
                            item</span>
                    </div>

                    <div class="divide-y divide-black/5 dark:divide-white/10">
                        @foreach ($order->items as $item)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-[14.5px] font-bold text-black dark:text-white tracking-tight">
                                        {{ $item->product_name }}</h3>
                                    <div
                                        class="flex items-center gap-2 mt-1 text-[12.5px] text-black/55 dark:text-white/55">
                                        <span>{{ number_format((float) $item->quantity, 0, ',', '.') }}
                                            {{ $item->unit_name ?? 'pcs' }}</span>
                                        <span>&times;</span>
                                        <span>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($item->notes)
                                        <p
                                            class="text-[12px] text-black/60 dark:text-white/60 bg-black/5 dark:bg-white/5 px-2.5 py-1 rounded-[8px] mt-2 inline-block">
                                            Catatan: "{{ $item->notes }}"
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-[14.5px] font-bold text-black dark:text-white tracking-tight">
                                        Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- FINANCIAL SUMMARY --}}
                    <div class="mt-6 pt-4 border-t border-black/5 dark:border-white/10 space-y-2 text-[13.5px]">
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                            <span>Subtotal Item</span>
                            <span class="tabular-nums">Rp {{ number_format((float) $order->subtotal_amount, 0, ',', '.') }}</span>
                        </div>
                        @if ((float) $order->shipping_fee > 0)
                            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                <span>Ongkos Kirim
                                    ({{ $order->fulfillment_type === 'pickup' ? 'Ambil Sendiri' : 'Kurir Toko' }})</span>
                                <span class="tabular-nums">Rp {{ number_format((float) $order->shipping_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ((float) $order->discount_amount > 0)
                            <div class="flex items-center justify-between text-[#34C759]">
                                <span>Diskon</span>
                                <span class="tabular-nums">-Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div
                            class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/10 text-[16px] font-extrabold text-black dark:text-white">
                            <span>Total Tagihan</span>
                            <span class="text-[#007AFF] tabular-nums">Rp
                                {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- MULTI-DROP DELIVERY BATCHES (PO BATCHES) --}}
                @if ($order->batches->isNotEmpty())
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                                    <i data-lucide="layers" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Jadwal
                                        Pengiriman Multi-Drop (PO Batches)</h2>
                                    <p class="text-[12px] text-black/55 dark:text-white/55">Pelacakan dan pembaruan
                                        pemenuhan pengiriman bertahap.</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11.5px] font-bold bg-[#5856D6]/10 text-[#5856D6]">
                                {{ $order->delivered_batches_count }} dari {{ $order->total_batches_count }} Selesai
                            </span>
                        </div>

                        <div class="space-y-3 divide-y divide-black/5 dark:divide-white/5">
                            @foreach ($order->batches as $batch)
                                <div class="pt-3 first:pt-0 space-y-3" x-data="{ editing: false }">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 font-bold text-xs flex items-center justify-center shrink-0">
                                                #{{ $batch->batch_number }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="font-bold text-[14px] text-black dark:text-white">{{ $batch->batch_code }}</span>
                                                    <span class="text-[12px] text-black/50 dark:text-white/50">&bull;
                                                        {{ $batch->scheduled_date->translatedFormat('d M Y') }}</span>
                                                    @if ($batch->scheduled_time_slot)
                                                        <span
                                                            class="text-[11px] px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ $batch->scheduled_time_slot }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                                                    Volume: <strong
                                                        class="text-black dark:text-white">{{ number_format($batch->quantity, 0, ',', '.') }}
                                                        unit/porsi</strong>
                                                    @if ($batch->tracking_number)
                                                        &bull; Resi: <code
                                                            class="font-mono text-[#007AFF]">{{ $batch->tracking_number }}</code>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 self-start sm:self-center">
                                            @if ($batch->status === 'delivered')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                    <span>Terkirim ({{ $batch->delivered_at?->format('d/m H:i') }})</span>
                                                </span>
                                            @elseif($batch->status === 'shipped')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6]">
                                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                                    <span>Sedang Dikirim</span>
                                                </span>
                                            @elseif($batch->status === 'in_preparation')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                                    <span>Disiapkan</span>
                                                </span>
                                            @elseif($batch->status === 'cancelled')
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-500/15 text-red-600">
                                                    <span>Dibatalkan</span>
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                                    <span>Terjadwal</span>
                                                </span>
                                            @endif

                                            <button type="button" @click="editing = !editing"
                                                class="p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50 hover:text-black transition">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Inline Status Update Form --}}
                                    <div x-show="editing" x-transition
                                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3">
                                        <form action="{{ route('storefront.orders.batches.status', [$order, $batch]) }}"
                                            method="POST" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label
                                                        class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">Perbarui
                                                        Status Drop</label>
                                                    <select name="status"
                                                        class="w-full h-9 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white">
                                                        <option value="scheduled"
                                                            {{ $batch->status === 'scheduled' ? 'selected' : '' }}>
                                                            Terjadwal (Scheduled)</option>
                                                        <option value="in_preparation"
                                                            {{ $batch->status === 'in_preparation' ? 'selected' : '' }}>
                                                            Sedang Disiapkan (In Preparation)</option>
                                                        <option value="shipped"
                                                            {{ $batch->status === 'shipped' ? 'selected' : '' }}>Dalam
                                                            Pengiriman (Shipped)</option>
                                                        <option value="delivered"
                                                            {{ $batch->status === 'delivered' ? 'selected' : '' }}>Telah
                                                            Diterima (Delivered)</option>
                                                        <option value="cancelled"
                                                            {{ $batch->status === 'cancelled' ? 'selected' : '' }}>
                                                            Dibatalkan (Cancelled)</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-[11px] font-semibold text-black/70 dark:text-white/70 mb-1">Nomor
                                                        Resi / Kurir (Opsional)</label>
                                                    <input type="text" name="tracking_number"
                                                        value="{{ $batch->tracking_number }}"
                                                        placeholder="Contoh: KURIR-01 / JNE-12345"
                                                        class="w-full h-9 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white">
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 pt-1">
                                                <button type="button" @click="editing = false"
                                                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 hover:bg-black/5">Batal</button>
                                                <button type="submit"
                                                    class="px-4 py-1.5 rounded-[10px] bg-[#5856D6] hover:bg-[#4745B8] text-white text-[12px] font-bold transition">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 2. CUSTOMER & FULFILLMENT CARD --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10 mb-4">
                        <i data-lucide="user" class="w-5 h-5 text-[#5856D6]"></i>
                        <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Informasi Pelanggan &
                            Pengiriman</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13.5px]">
                        <div>
                            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 block mb-1">Nama
                                Pemesan</span>
                            <p class="font-bold text-black dark:text-white">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 block mb-1">Nomor
                                WhatsApp</span>
                            <p class="font-bold text-black dark:text-white">{{ $order->customer_phone }}</p>
                        </div>
                        <div>
                            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 block mb-1">Metode
                                Pengiriman</span>
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[12px] font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80">
                                @if ($order->fulfillment_type === 'pickup')
                                    <i data-lucide="store" class="w-3.5 h-3.5"></i> Ambil Sendiri di Toko
                                @else
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> Pengiriman Kurir Toko
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 block mb-1">Email
                                Pelanggan</span>
                            <p class="text-black/80 dark:text-white/80">{{ $order->customer_email ?? '-' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 block mb-1">Alamat
                                Pengiriman</span>
                            <p
                                class="text-black/80 dark:text-white/80 bg-black/5 dark:bg-white/5 p-3 rounded-[12px] leading-relaxed">
                                {{ $order->shipping_address ?? 'Tidak ada alamat khusus (Ambil di Toko).' }}
                            </p>
                        </div>
                        @if ($order->notes)
                            <div class="sm:col-span-2">
                                <span class="text-[12px] font-semibold text-black/45 dark:text-white/45 block mb-1">Catatan
                                    Tambahan Pesanan</span>
                                <p
                                    class="text-black/80 dark:text-white/80 italic bg-black/5 dark:bg-white/5 p-3 rounded-[12px]">
                                    "{{ $order->notes }}"
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: PAYMENT PROOF & OPERATIONS (4 COLS) --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- 1. PAYMENT PROOF & GATEWAY CARD --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10 mb-4">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="{{ $order->isTripay() ? 'zap' : 'credit-card' }}" class="w-5 h-5 {{ $order->isPaid() ? 'text-[#34C759]' : 'text-[#007AFF]' }}"></i>
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">
                                {{ $order->isTripay() ? 'TriPay Gateway' : 'Bukti Transfer' }}
                            </h2>
                        </div>
                        @if ($order->isPaid())
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">Lunas</span>
                        @elseif ($order->isTripay())
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Menunggu Bayar</span>
                        @endif
                    </div>

                    @if ($order->isTripay())
                        {{-- TRIPAY GATEWAY SUMMARY --}}
                        <div class="space-y-3.5">
                            <div class="p-3.5 rounded-[16px] bg-brand-primary/[0.04] border border-brand-primary/15 space-y-2 text-[12.5px]">
                                <div class="flex items-center justify-between">
                                    <span class="text-black/60 dark:text-white/60">Saluran Pembayaran:</span>
                                    <span class="font-bold text-black dark:text-white">{{ $order->payment_channel ?? 'QRIS' }}</span>
                                </div>
                                @if ($order->gateway_reference)
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/60 dark:text-white/60">Ref TriPay:</span>
                                        <span class="font-mono font-semibold text-brand-primary">{{ $order->gateway_reference }}</span>
                                    </div>
                                @endif
                                @if ($order->gateway_pay_code)
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/60 dark:text-white/60">Kode Bayar / VA:</span>
                                        <span class="font-mono font-bold text-black dark:text-white">{{ $order->gateway_pay_code }}</span>
                                    </div>
                                @endif
                                <div class="pt-2 border-t border-black/5 dark:border-white/5 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-black/60 dark:text-white/60">Total Bruto:</span>
                                        <span class="font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11.5px]">
                                        <span class="text-amber-700 dark:text-amber-400">Biaya Gateway (Owner &amp; Admin):</span>
                                        <span class="font-semibold text-amber-700 dark:text-amber-400 tabular-nums">- Rp {{ number_format($order->gateway_fee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between font-bold text-[13px] pt-1 text-emerald-700 dark:text-emerald-400">
                                        <span>Penerimaan Bersih:</span>
                                        <span class="tabular-nums">Rp {{ number_format($order->net_revenue, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($order->isPaid())
                                <div class="p-3 rounded-[14px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-[12px] flex items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                                    <span>Terverifikasi otomatis via Webhook pada {{ $order->paid_at?->translatedFormat('d M Y, H:i') ?? '-' }}.</span>
                                </div>
                            @else
                                <div class="p-3 rounded-[14px] bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-[12px] flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                                    <span>Menunggu pembayaran pelanggan via {{ $order->payment_channel ?? 'QRIS' }}. Sistem otomatis mengonfirmasi saat lunas.</span>
                                </div>
                                @if ($order->gateway_reference)
                                    <form action="{{ route('storefront.orders.sync_gateway', $order->id) }}" method="POST" class="pt-1">
                                        @csrf
                                        <button type="submit"
                                            class="w-full h-9 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] text-[12px] font-semibold flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                            <span>Cek &amp; Sinkronkan Status TriPay</span>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    @else
                        {{-- MANUAL PAYMENT METHOD INFO --}}
                        <div class="p-3.5 rounded-[16px] bg-black/5 dark:bg-white/5 mb-4 text-[12.5px] space-y-1">
                            <span class="text-[11px] font-semibold text-black/45 dark:text-white/45 block">Tujuan Transfer:</span>
                            @if ($order->paymentMethod)
                                <p class="font-bold text-black dark:text-white">{{ $order->paymentMethod->bank_name }}</p>
                                @if ($order->paymentMethod->account_number)
                                    <p class="text-black/70 dark:text-white/70 font-mono">
                                        {{ $order->paymentMethod->account_number }} a/n
                                        {{ $order->paymentMethod->account_holder }}</p>
                                @endif
                            @else
                                <p class="font-medium text-black/70 dark:text-white/70">Rekening Toko / Manual</p>
                            @endif
                        </div>

                        @php
                            $latestProof = $order->latestProof;
                        @endphp

                        @if ($latestProof)
                            <div class="space-y-4">
                                <div
                                    class="relative group rounded-[16px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/5">
                                    <img src="{{ route('storefront.proofs.stream', $latestProof->id) }}" alt="Bukti Transfer"
                                        class="w-full h-48 object-cover cursor-pointer group-hover:scale-105 transition duration-300"
                                        @click="activeImageUrl = '{{ route('storefront.proofs.stream', $latestProof->id) }}'; imageModalOpen = true">
                                    <div
                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center pointer-events-none text-white font-medium text-[12px] gap-1.5">
                                        <i data-lucide="zoom-in" class="w-4 h-4"></i> Klik untuk Memperbesar
                                    </div>
                                </div>

                                <div class="text-[12px] space-y-1 text-black/60 dark:text-white/60">
                                    <div class="flex justify-between">
                                        <span>Diunggah:</span>
                                        <span
                                            class="font-medium text-black dark:text-white">{{ $latestProof->created_at->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                    @if ($latestProof->sender_bank)
                                        <div class="flex justify-between">
                                            <span>Bank Pengirim:</span>
                                            <span
                                                class="font-medium text-black dark:text-white">{{ $latestProof->sender_bank }}</span>
                                        </div>
                                    @endif
                                    @if ($latestProof->sender_account_name)
                                        <div class="flex justify-between">
                                            <span>Nama Pemilik:</span>
                                            <span
                                                class="font-medium text-black dark:text-white">{{ $latestProof->sender_account_name }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center pt-1">
                                        <span>Status Verifikasi:</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10.5px] font-bold uppercase
                                        {{ $latestProof->status === 'verified' ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : ($latestProof->status === 'rejected' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : 'bg-[#007AFF]/10 text-[#007AFF]') }}">
                                            {{ $latestProof->status_label }}
                                        </span>
                                    </div>
                                </div>

                                {{-- VERIFICATION BUTTONS --}}
                                @if ($latestProof->isPending() || $order->status === 'proof_submitted')
                                    <div class="pt-3 border-t border-black/5 dark:border-white/10 space-y-2">
                                        <button type="button" @click="verifyModalOpen = true"
                                            class="w-full h-11 rounded-[14px] bg-[#34C759] hover:bg-[#30B752] text-white text-[13.5px] font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            <span>Verifikasi Pembayaran</span>
                                        </button>
                                        <button type="button" @click="rejectModalOpen = true"
                                            class="w-full h-10 rounded-[14px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] text-[13px] font-bold transition flex items-center justify-center gap-2 cursor-pointer">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                            <span>Tolak Bukti Transfer</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-8 text-center text-black/45 dark:text-white/45">
                                <i data-lucide="image-off" class="w-10 h-10 mx-auto stroke-1 mb-2 opacity-50"></i>
                                <p class="text-[13px] font-medium">Belum ada bukti transfer yang diunggah pelanggan.</p>
                            </div>
                        @endif
                    @endif
                </div>


                {{-- 2. OPERATIONAL STATUS CARD --}}
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10 mb-4">
                        <i data-lucide="refresh-cw" class="w-5 h-5 text-[#FF9500]"></i>
                        <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Status Operasional</h2>
                    </div>

                    <form action="{{ route('storefront.orders.update_status', $order->id) }}" method="POST"
                        class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[12px] font-semibold text-black/50 dark:text-white/50 mb-1.5">Perbarui
                                Tahapan Order</label>
                            <select name="status"
                                class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>
                                    Diproses (Sedang Disiapkan/Dimasak)</option>
                                <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Siap (Siap
                                    Diambil / Siap Kirim)</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Selesai
                                    (Sudah Diterima)</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Batalkan
                                    Pesanan</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full h-10 rounded-[14px] bg-black/10 dark:bg-white/10 hover:bg-black/15 dark:hover:bg-white/15 text-black dark:text-white text-[13px] font-bold transition flex items-center justify-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

        {{-- MODAL TOLAK BUKTI BAYAR (APPLE HIG DIALOG) --}}
        <div x-show="rejectModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4 max-h-[92vh] overflow-y-auto"
                @click.outside="rejectModalOpen = false">
                {{-- Mobile Grab Bar --}}
                <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-1 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 text-[#FF3B30]">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Tolak Bukti Transfer</h3>
                    </div>
                    <button type="button" @click="rejectModalOpen = false"
                        class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pelanggan akan menerima notifikasi bahwa bukti transfer tidak sah/tidak terbaca dan diminta mengunggah
                    ulang.
                </p>

                <form action="{{ route('storefront.orders.reject_payment', $order->id) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Alasan
                            Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" rows="3" required
                            placeholder="Contoh: Nominal transfer kurang, struk buram, atau dana belum masuk mutasi."
                            class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30] resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="rejectModalOpen = false"
                            class="px-4 py-2 rounded-full text-[13px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-full bg-[#FF3B30] hover:bg-[#E0352B] text-white text-[13px] font-bold shadow-sm transition cursor-pointer">
                            Tolak Bukti
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- APPLE ALERT CONFIRMATION DIALOG (VERIFIKASI PEMBAYARAN) --}}
        <div x-show="verifyModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"
                @click.outside="verifyModalOpen = false">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Verifikasi Pembayaran?</h3>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">
                            Total tagihan <strong class="text-black dark:text-white font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> dari <span class="font-medium text-black dark:text-white">{{ $order->customer_name }}</span> akan dikonfirmasi LUNAS.
                        </p>
                    </div>
                </div>

                {{-- PENENANG JIWA & STOCK ALLOCATION NOTICE --}}
                <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-start gap-2.5 text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                    <span>Tenang: Verifikasi pembayaran akan mengubah status pesanan menjadi LUNAS dan stok produk terkait akan otomatis dialokasikan/dipotong secara sah di pembukuan.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="verifyModalOpen = false"
                        class="px-4 py-2 rounded-full text-[13px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition cursor-pointer">
                        Batal
                    </button>
                    <form action="{{ route('storefront.orders.verify_payment', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 rounded-full bg-[#34C759] hover:bg-[#2EB04E] text-white text-[13px] font-bold transition shadow-sm cursor-pointer">
                            Ya, Konfirmasi Lunas
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL ZOOM GAMBAR BUKTI --}}
        <div x-show="imageModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
            @click="imageModalOpen = false">
            <div class="relative max-w-2xl max-h-[90vh] overflow-hidden rounded-[20px] shadow-2xl" @click.stop>
                <button type="button" @click="imageModalOpen = false"
                    class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/60 text-white flex items-center justify-center hover:bg-black/80 transition z-10 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <img :src="activeImageUrl" alt="Preview Bukti Transfer"
                    class="w-full h-auto max-h-[85vh] object-contain rounded-[20px]">
            </div>
        </div>

    </div>
@endsection
