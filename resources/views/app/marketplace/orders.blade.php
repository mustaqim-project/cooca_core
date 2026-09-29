@extends('layouts.app', [
    'title' => 'Pesanan Marketplace - ' . $business->name,
    'headerTitle' => 'Pesanan Masuk Marketplace',
    'headerSubtitle' => 'Feed transaksi pesanan terpadu dari Shopee, TikTok Shop, dan Tokopedia',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    pullModal: false,
    submitting: false,
    selectedPullChannel: 'shopee'
}">

    <!-- ========================================== -->
    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        title="Pesanan Masuk Marketplace"
        subtitle="Pantau seluruh pesanan masuk dari Shopee, TikTok Shop, dan Tokopedia secara real-time yang memotong stok gudang otomatis.">
        <x-slot:actions>
            <button type="button" @click="pullModal = true"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                <span>Tarik Pesanan Terbaru</span>
            </button>
            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Hub</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    <!-- ===================================================== -->
    <!-- 2. CHANNEL & STATUS FILTER BAR                        -->
    <!-- ===================================================== -->
    <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <form method="GET" action="{{ route('marketplace-hub.orders') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Channel Filter -->
            <select name="channel" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">Semua Saluran (All Channels)</option>
                <option value="shopee" {{ request('channel') === 'shopee' ? 'selected' : '' }}>Shopee</option>
                <option value="tiktok_shop" {{ request('channel') === 'tiktok_shop' ? 'selected' : '' }}>TikTok Shop</option>
                <option value="tokopedia" {{ request('channel') === 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">Semua Status Pesanan</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Sudah Dibayar / Siap Dikirim</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dalam Pengiriman</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>

            @if(request()->filled('channel') || request()->filled('status'))
                <a href="{{ route('marketplace-hub.orders') }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center">
                    Reset Filter
                </a>
            @endif
        </form>

        <div class="text-[12px] text-black/60 dark:text-white/60">
            <span>Total: <strong>{{ $orders->total() }}</strong> Pesanan</span>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO ORDERS TABLE                                 -->
    <!-- ===================================================== -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/60 dark:text-white/60 font-semibold text-[11.5px] uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">No. Pesanan &amp; Saluran</th>
                        <th class="py-3.5 px-4">Pelanggan</th>
                        <th class="py-3.5 px-4">Item &amp; Produk</th>
                        <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Waktu Pesanan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($orders as $order)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <!-- Col 1: Order ID & Channel Badge -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-2">
                                    @if($order->channel === 'shopee')
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#EE4D2D]/15 text-[#EE4D2D]">SHOPEE</span>
                                    @elseif($order->channel === 'tiktok_shop')
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black text-white dark:bg-white dark:text-black">TIKTOK</span>
                                    @elseif($order->channel === 'tokopedia')
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#00AA5B]/15 text-[#00AA5B]">TOKPED</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black/10">{{ strtoupper($order->channel) }}</span>
                                    @endif
                                    <span class="font-mono font-bold text-black dark:text-white text-[13px]">{{ $order->external_order_sn ?? $order->external_order_id }}</span>
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 font-mono">
                                    Ref ID: #{{ substr($order->id, 0, 8) }}
                                </div>
                            </td>

                            <!-- Col 2: Customer -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-black dark:text-white text-[13px]">
                                    {{ $order->buyer_name ?? 'Pelanggan Marketplace' }}
                                </div>
                                @if($order->buyer_phone)
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-mono">
                                        {{ $order->buyer_phone }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 3: Items Summary -->
                            <td class="py-4 px-4">
                                @php
                                    $items = is_array($order->items_summary) ? $order->items_summary : json_decode($order->items_summary ?? '[]', true);
                                    $itemCount = is_countable($items) ? count($items) : 0;
                                @endphp
                                <div class="font-medium text-black dark:text-white">
                                    {{ $itemCount }} Item Produk
                                </div>
                                @if(!empty($items[0]['item_name'] ?? ($items[0]['name'] ?? null)))
                                    <div class="text-[11px] text-black/60 dark:text-white/60 truncate max-w-[200px]">
                                        {{ $items[0]['item_name'] ?? $items[0]['name'] }}
                                        @if($itemCount > 1)
                                            <span class="text-black/40 dark:text-white/40">(+{{ $itemCount - 1 }})</span>
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($order->shipping_provider))
                                    <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="truck" class="w-3 h-3"></i>
                                        <span>{{ $order->shipping_provider }}</span>
                                        @if(!empty($order->tracking_number))
                                            <span class="font-mono">({{ $order->tracking_number }})</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Col 4: Total Amount -->
                            <td class="py-4 px-4 text-right">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">
                                    Rp {{ number_format((float)$order->total_amount, 0, ',', '.') }}
                                </div>
                                @if($order->channel_fee > 0)
                                    <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                        Fee: Rp {{ number_format((float)$order->channel_fee, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 5: Status Badge -->
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusClass = match(strtolower($order->order_status)) {
                                        'completed', 'delivered' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]',
                                        'shipped', 'in_transit' => 'bg-[#007AFF]/15 text-[#007AFF]',
                                        'paid', 'ready_to_ship' => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]',
                                        'cancelled' => 'bg-[#FF3B30]/15 text-[#FF3B30]',
                                        default => 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $statusClass }}">
                                    {{ strtoupper($order->order_status) }}
                                </span>
                            </td>

                            <!-- Col 6: Time -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="text-[12px] font-medium text-black dark:text-white">
                                    {{ $order->placed_at ? $order->placed_at->format('d M Y, H:i') : $order->created_at->format('d M Y, H:i') }}
                                </div>
                                <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                    {{ ($order->placed_at ?? $order->created_at)->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-black/50 dark:text-white/50">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="shopping-bag" class="w-6 h-6 text-black/40 dark:text-white/40"></i>
                                </div>
                                <div class="font-bold text-[14px]">Belum Ada Pesanan Masuk</div>
                                <p class="text-[12px] mt-0.5">Pesanan dari Shopee, TikTok Shop, dan Tokopedia akan muncul di sini secara otomatis via Webhook atau penarikan manual.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL TARIK PESANAN MANUAL (BENTO HIG)             -->
    <!-- ===================================================== -->
    <div x-show="pullModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="pullModal = false"
            class="w-full max-w-lg rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl">
            
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="download-cloud" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Tarik Pesanan Marketplace</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Sinkronkan transaksi terkini secara manual via API resmi.</p>
                    </div>
                </div>
                <button type="button" @click="pullModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('marketplace-hub.orders.pull') }}" @submit="submitting = true" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2">Pilih Saluran Toko</label>
                    <div class="grid grid-cols-3 gap-2 p-1.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06]">
                        <button type="button" @click="selectedPullChannel = 'shopee'"
                            :class="selectedPullChannel === 'shopee' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#EE4D2D]' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-2.5 text-[12px] rounded-[11px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Shopee</span>
                        </button>
                        <button type="button" @click="selectedPullChannel = 'tiktok_shop'"
                            :class="selectedPullChannel === 'tiktok_shop' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-black dark:text-white' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-2.5 text-[12px] rounded-[11px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>TikTok Shop</span>
                        </button>
                        <button type="button" @click="selectedPullChannel = 'tokopedia'"
                            :class="selectedPullChannel === 'tokopedia' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#00AA5B]' : 'text-black/60 dark:text-white/60 font-medium'"
                            class="py-2.5 text-[12px] rounded-[11px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Tokopedia</span>
                        </button>
                    </div>
                    <input type="hidden" name="channel" :value="selectedPullChannel">
                </div>

                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] text-[12px] text-black/60 dark:text-white/60 flex items-center gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                    <span>Pesanan berstatus terbayar akan otomatis mengurangi stok produk COOCA secara real-time.</span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="pullModal = false" :disabled="submitting"
                        class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                        <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Menarik Pesanan...' : 'Mulai Tarik Pesanan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
