@extends('layouts.app', [
    'title' => 'Pesanan Toko Online - Cooca',
    'headerTitle' => 'Pesanan Toko Online',
    'headerSubtitle' => 'Pantau pesanan masuk dari storefront publik, verifikasi bukti transfer pelanggan, dan kelola pemenuhan order',
])

@section('content')
    <div class="space-y-6 pb-28 sm:pb-32 lg:pb-10 max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

        {{-- FLASH MESSAGES --}}
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

        {{-- UNIFIED STOREFRONT HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => 'Pesanan Toko Online'])

        {{-- BENTO METRIC CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Total Pesanan</span>
                    <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-[#007AFF] shrink-0">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ $orders->total() }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Seluruh riwayat pesanan
                </p>
            </div>

            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Perlu Verifikasi</span>
                    <div class="w-8 h-8 rounded-full {{ $needsVerificationCount > 0 ? 'bg-rose-50 dark:bg-rose-900/30 text-[#FF3B30]' : 'bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40' }} flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight {{ $needsVerificationCount > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tabular-nums">
                    {{ $needsVerificationCount }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Bukti transfer menunggu cek
                </p>
            </div>

            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Mode Pesanan</span>
                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-[#34C759] shrink-0">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-base sm:text-lg font-bold tracking-tight text-black dark:text-white truncate">
                    Pickup &amp; Kurir
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Layanan etalase aktif
                </p>
            </div>

            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Halaman Aktif</span>
                    <div class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-[#5856D6] shrink-0">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ $orders->count() }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Ditampilkan saat ini
                </p>
            </div>
        </div>

        {{-- FILTER TABS & SEARCH BAR --}}
        <div class="space-y-3">
            <div class="w-full overflow-x-auto scrollbar-thin pb-1">
                <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[14px] border border-black/[0.04] dark:border-white/[0.06] gap-1">
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'all', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Semua Pesanan
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'needs_verification', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="relative px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'needs_verification' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        <span>Perlu Verifikasi</span>
                        @if ($needsVerificationCount > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30] text-white tabular-nums">{{ $needsVerificationCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'unpaid', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'unpaid' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Menunggu Bayar
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'processing', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'processing' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Diproses
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'completed', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'completed' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Selesai
                    </a>
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => 'cancelled', 'type' => ($typeFilter ?? 'all') !== 'all' ? $typeFilter : null, 'search' => request('search')])) }}"
                        class="px-3.5 py-1.5 rounded-[10px] text-[13px] font-medium transition-all whitespace-nowrap {{ $statusTab === 'cancelled' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        Batal / Kedaluwarsa
                    </a>
                </div>
            </div>

            {{-- ORDER TYPE FILTER PILLS --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-[12px]">
                <span class="text-black/40 dark:text-white/40 font-medium mr-1 shrink-0">Tipe:</span>
                @php
                    $typeTabs = [
                        'all' => 'Semua Tipe',
                        'direct_checkout' => 'Langsung (Checkout)',
                        'scheduled_order' => 'Pre-Order Terjadwal',
                        'request_order' => 'Request Order',
                        'customer_po' => 'PO B2B & Batch',
                        'reservation' => 'Reservasi',
                    ];
                    $currentType = $typeFilter ?? 'all';
                @endphp
                @foreach ($typeTabs as $tKey => $tLabel)
                    <a href="{{ route('storefront.orders.index', array_filter(['tab' => $statusTab !== 'all' ? $statusTab : null, 'type' => $tKey !== 'all' ? $tKey : null, 'search' => request('search')])) }}"
                        class="px-2.5 py-1 rounded-full border transition whitespace-nowrap {{ $currentType === $tKey ? 'bg-black dark:bg-white text-white dark:text-black font-semibold border-transparent shadow-2xs' : 'border-black/10 dark:border-white/10 text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20' }}">
                        {{ $tLabel }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('storefront.orders.index') }}" class="relative max-w-md w-full">
                <input type="hidden" name="tab" value="{{ $statusTab }}">
                @if (($typeFilter ?? 'all') !== 'all')
                    <input type="hidden" name="type" value="{{ $typeFilter }}">
                @endif
                <i data-lucide="search"
                    class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor order, nama, atau no. WA..."
                    class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition-all shadow-sm">
            </form>
        </div>

        {{-- MOBILE CARD LIST VIEW (< md) --}}
        <div class="block md:hidden space-y-3">
            @forelse($orders as $order)
                <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-mono font-bold text-[14px] text-black dark:text-white">
                                #{{ $order->order_number }}
                            </span>
                            @if ($order->groupOrder)
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20 inline-flex items-center gap-1">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    <span>Pesan Bareng</span>
                                </span>
                            @endif
                        </div>
                        @if ($order->status === 'proof_submitted')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                Perlu Verifikasi
                            </span>
                        @elseif($order->status === 'pending_payment')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                Menunggu Transfer
                            </span>
                        @elseif($order->status === 'paid' || $order->status === 'processing')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                Lunas (Diproses)
                            </span>
                        @elseif($order->status === 'ready')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                Siap Diambil / Antar
                            </span>
                        @elseif($order->status === 'completed')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                Selesai
                            </span>
                        @elseif($order->status === 'payment_rejected')
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                Bukti Ditolak
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                                {{ $order->status }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-[13px]">
                        <div class="min-w-0 flex-1 pr-2">
                            <div class="font-semibold text-black dark:text-white truncate">{{ $order->customer_name }}</div>
                            <div class="text-[12px] text-black/50 dark:text-white/50 truncate tabular-nums">+{{ $order->customer_phone }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center gap-1 text-[12px] font-medium text-black/60 dark:text-white/60">
                                <i data-lucide="{{ $order->fulfillment_type === 'pickup' ? 'store' : 'truck' }}" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ $order->fulfillment_type === 'pickup' ? 'Pickup' : 'Delivery' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[13px]">
                        <div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">Total Tagihan</div>
                            <div class="text-[16px] font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <a href="{{ route('storefront.orders.show', $order) }}"
                            class="h-9 px-4 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold transition flex items-center gap-1.5 active:scale-[0.98] shadow-sm">
                            <span>Lihat</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] text-black/40 dark:text-white/40">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="text-[14px] font-medium">Belum ada pesanan online pada kategori ini.</p>
                </div>
            @endforelse

            @if ($orders->hasPages())
                <div class="pt-2">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- DESKTOP TABLE VIEW (>= md) --}}
        <div class="hidden md:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 text-black/50 dark:text-white/50 bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="py-3.5 px-4 font-semibold">No. Pesanan</th>
                            <th class="py-3.5 px-4 font-semibold">Pelanggan</th>
                            <th class="py-3.5 px-4 font-semibold">Metode</th>
                            <th class="py-3.5 px-4 font-semibold">Item &amp; Tagihan</th>
                            <th class="py-3.5 px-4 font-semibold">Status Pesanan</th>
                            <th class="py-3.5 px-4 font-semibold">Waktu</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($orders as $order)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition">
                                <td class="py-3.5 px-4 font-mono font-semibold text-black dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('storefront.orders.show', $order) }}"
                                            class="text-[#007AFF] hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                        @if ($order->groupOrder)
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20 inline-flex items-center gap-1" title="Pesanan Bersama / Group Order: {{ $order->groupOrder->title }}">
                                                <i data-lucide="users" class="w-3 h-3"></i>
                                                <span>Pesan Bareng</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-black dark:text-white leading-tight">
                                        {{ $order->customer_name }}
                                    </div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                                        +{{ $order->customer_phone }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/70 dark:text-white/70">
                                        <i data-lucide="{{ $order->fulfillment_type === 'pickup' ? 'store' : 'truck' }}"
                                            class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ $order->fulfillment_type === 'pickup' ? 'Ambil Sendiri' : 'Antar Toko' }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-black dark:text-white tabular-nums">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">
                                        {{ $order->items->count() }} jenis produk
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($order->status === 'proof_submitted')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                            Perlu Verifikasi
                                        </span>
                                    @elseif($order->status === 'pending_payment')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Menunggu Transfer
                                        </span>
                                    @elseif($order->status === 'paid' || $order->status === 'processing')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Lunas (Diproses)
                                        </span>
                                    @elseif($order->status === 'ready')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            Siap Diambil / Antar
                                        </span>
                                    @elseif($order->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            Selesai
                                        </span>
                                    @elseif($order->status === 'payment_rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                            Bukti Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                                            {{ $order->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-[12px] text-black/55 dark:text-white/55 whitespace-nowrap tabular-nums">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('storefront.orders.show', $order) }}"
                                        class="inline-flex items-center gap-1 h-8 px-3 rounded-[10px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12px] font-semibold transition active:scale-[0.98]">
                                        <span>Lihat</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                    <p class="text-[14px] font-medium">Belum ada pesanan online pada kategori ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
