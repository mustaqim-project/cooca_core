@extends('layouts.app', [
    'title' => 'Pesanan Toko Online - Cooca UMKM',
    'headerTitle' => 'Pesanan Toko Online',
    'headerSubtitle' => 'Pantau pesanan masuk dari storefront publik, verifikasi bukti transfer pelanggan, dan kelola pemenuhan order',
])

@section('content')
    <div class="space-y-6 pb-12">

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

        {{-- HEADER STATS & ACTION --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-[19px] font-bold text-black dark:text-white tracking-tight">Daftar Pesanan Storefront
                    </h1>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55">Link Etalase: <a
                            href="{{ url("/b/{$business->slug}") }}" target="_blank"
                            class="text-[#007AFF] hover:underline font-medium">{{ url("/b/{$business->slug}") }}</a></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('storefront.settings.index') }}"
                    class="h-10 px-4 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[13px] font-semibold transition flex items-center gap-2">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Pengaturan Toko</span>
                </a>
            </div>
        </div>

        {{-- FILTER TABS --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <a href="{{ route('storefront.orders.index', ['tab' => 'all']) }}"
                class="px-4 py-2 rounded-full text-[12.5px] font-semibold tracking-tight transition whitespace-nowrap {{ $statusTab === 'all' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                Semua Pesanan
            </a>
            <a href="{{ route('storefront.orders.index', ['tab' => 'needs_verification']) }}"
                class="relative px-4 py-2 rounded-full text-[12.5px] font-semibold tracking-tight transition whitespace-nowrap {{ $statusTab === 'needs_verification' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                <span>Perlu Verifikasi Bukti</span>
                @if ($needsVerificationCount > 0)
                    <span
                        class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF3B30] text-white">{{ $needsVerificationCount }}</span>
                @endif
            </a>
            <a href="{{ route('storefront.orders.index', ['tab' => 'unpaid']) }}"
                class="px-4 py-2 rounded-full text-[12.5px] font-semibold tracking-tight transition whitespace-nowrap {{ $statusTab === 'unpaid' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                Menunggu Bayar
            </a>
            <a href="{{ route('storefront.orders.index', ['tab' => 'processing']) }}"
                class="px-4 py-2 rounded-full text-[12.5px] font-semibold tracking-tight transition whitespace-nowrap {{ $statusTab === 'processing' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                Diproses Dapur / Toko
            </a>
            <a href="{{ route('storefront.orders.index', ['tab' => 'completed']) }}"
                class="px-4 py-2 rounded-full text-[12.5px] font-semibold tracking-tight transition whitespace-nowrap {{ $statusTab === 'completed' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                Selesai
            </a>
            <a href="{{ route('storefront.orders.index', ['tab' => 'cancelled']) }}"
                class="px-4 py-2 rounded-full text-[12.5px] font-semibold tracking-tight transition whitespace-nowrap {{ $statusTab === 'cancelled' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                Batal / Expired
            </a>
        </div>

        {{-- SEARCH BAR --}}
        <form method="GET" action="{{ route('storefront.orders.index') }}" class="relative max-w-md">
            <input type="hidden" name="tab" value="{{ $statusTab }}">
            <i data-lucide="search"
                class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="search" name="search" value="{{ request('search') }}"
                placeholder="Cari nomor order, nama, atau no. WA..."
                class="w-full h-10 pl-9 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
        </form>

        {{-- ORDERS TABLE --}}
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/5 text-black/50 dark:text-white/50 bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="py-3 px-4 font-semibold">No. Pesanan</th>
                            <th class="py-3 px-4 font-semibold">Pelanggan</th>
                            <th class="py-3 px-4 font-semibold">Metode</th>
                            <th class="py-3 px-4 font-semibold">Item & Tagihan</th>
                            <th class="py-3 px-4 font-semibold">Status Pesanan</th>
                            <th class="py-3 px-4 font-semibold">Waktu</th>
                            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($orders as $order)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition">
                                <td class="py-3.5 px-4 font-mono font-semibold text-black dark:text-white">
                                    <a href="{{ route('storefront.orders.show', $order) }}"
                                        class="text-[#007AFF] hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-black dark:text-white leading-tight">
                                        {{ $order->customer_name }}</div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                                        +{{ $order->customer_phone }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center gap-1 text-[12px] font-medium text-black/70 dark:text-white/70">
                                        <i data-lucide="{{ $order->fulfillment_type === 'pickup' ? 'store' : 'truck' }}"
                                            class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                        <span>{{ $order->fulfillment_type === 'pickup' ? 'Ambil Sendiri' : 'Antar Toko' }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-black dark:text-white tabular-nums">Rp
                                        {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-[11.5px] text-black/50 dark:text-white/50">
                                        {{ $order->items->count() }} jenis produk</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($order->status === 'proof_submitted')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-pulse"></span>
                                            Perlu Verifikasi Bukti
                                        </span>
                                    @elseif($order->status === 'pending_payment')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Menunggu Transfer
                                        </span>
                                    @elseif($order->status === 'paid' || $order->status === 'processing')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Lunas (Diproses)
                                        </span>
                                    @elseif($order->status === 'ready')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            Siap Diambil / Antar
                                        </span>
                                    @elseif($order->status === 'completed')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                            Selesai
                                        </span>
                                    @elseif($order->status === 'payment_rejected')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                            Bukti Ditolak
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                                            {{ $order->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-[12px] text-black/55 dark:text-white/55 whitespace-nowrap">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('storefront.orders.show', $order) }}"
                                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12px] font-semibold transition">
                                        <span>Detail</span>
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
