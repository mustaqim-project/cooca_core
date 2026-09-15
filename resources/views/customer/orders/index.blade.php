@extends('layouts.customer', ['title' => 'Riwayat Belanja'])

@section('content')
<div class="space-y-6">

    {{-- Page Header & Search --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-black dark:text-white tracking-tight">Riwayat Belanja</h1>
            <p class="text-[13px] text-black/55 dark:text-white/55">Daftar seluruh transaksi belanja Anda di seluruh etalase UMKM.</p>
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('customer.orders') }}" class="relative max-w-xs w-full">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nomor pesanan / item..."
                   class="w-full h-10 pl-9 pr-3 rounded-full bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
        </form>
    </div>

    {{-- Segmented Status Filter Tabs --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
        @php
            $tabs = [
                'all' => ['label' => 'Semua', 'count' => $statusCounts['all']],
                'pending_payment' => ['label' => 'Menunggu Bayar', 'count' => $statusCounts['pending_payment']],
                'verifying' => ['label' => 'Verifikasi Bukti', 'count' => $statusCounts['verifying']],
                'processing' => ['label' => 'Diproses', 'count' => $statusCounts['processing']],
                'completed' => ['label' => 'Selesai', 'count' => $statusCounts['completed']],
                'cancelled' => ['label' => 'Dibatalkan', 'count' => $statusCounts['cancelled']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a href="{{ route('customer.orders', array_merge(request()->query(), ['status' => $key, 'page' => 1])) }}"
               class="px-3.5 py-1.5 rounded-full text-[12.5px] font-semibold transition whitespace-nowrap active:scale-95 flex items-center gap-1.5 {{ $status === $key ? 'bg-[#007AFF] text-white shadow-xs font-bold' : 'bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black/70 dark:text-white/70' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10.5px] tabular-nums {{ $status === $key ? 'bg-white/20 text-white' : 'bg-black/10 dark:bg-white/10 text-black/60 dark:text-white/60' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Order List --}}
    @if($orders->isEmpty())
        <div class="bento-card py-16 text-center text-black/40 dark:text-white/40 space-y-3">
            <i data-lucide="package-x" class="w-12 h-12 mx-auto stroke-1 opacity-50"></i>
            <div class="space-y-1">
                <p class="text-[14px] font-semibold text-black/70 dark:text-white/70">Tidak ada pesanan yang ditemukan</p>
                <p class="text-[12.5px]">Tidak ada transaksi untuk filter kategori ini.</p>
            </div>
            @if($status !== 'all' || $search !== '')
                <a href="{{ route('customer.orders') }}"
                   class="inline-flex items-center gap-1.5 text-[12.5px] text-[#007AFF] font-semibold hover:underline">
                    <span>Hapus Filter &amp; Tampilkan Semua</span>
                </a>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bento-card p-5 sm:p-6 space-y-4">
                    
                    {{-- Card Header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[10px] bg-black/5 dark:bg-white/10 flex items-center justify-center font-bold text-[13px] text-[#007AFF]">
                                <i data-lucide="store" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="font-bold text-[14px] text-black dark:text-white block leading-snug">{{ $order->business->name ?? 'Toko Mitra' }}</span>
                                <span class="text-[11.5px] text-black/45 dark:text-white/45">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[12.5px] text-black/60 dark:text-white/60 bg-black/5 dark:bg-white/5 px-2.5 py-1 rounded-full font-bold">
                                {{ $order->order_number }}
                            </span>

                            {{-- Status Badges --}}
                            @if($order->status === 'pending_payment')
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                                    <span>Menunggu Pembayaran</span>
                                </span>
                            @elseif($order->status === 'proof_submitted')
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1">
                                    <i data-lucide="file-check" class="w-3 h-3"></i>
                                    <span>Verifikasi Bukti</span>
                                </span>
                            @elseif(in_array($order->status, ['paid', 'processing', 'ready']))
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1">
                                    <i data-lucide="package" class="w-3 h-3"></i>
                                    <span>Sedang Diproses</span>
                                </span>
                            @elseif($order->status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>Selesai</span>
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                                    Dibatalkan
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Items Preview --}}
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 shrink-0 overflow-hidden flex items-center justify-center">
                                        @if($item->product?->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="package" class="w-5 h-5 text-black/30 dark:text-white/30"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-[13.5px] font-semibold text-black dark:text-white truncate">{{ $item->product_name }}</h3>
                                        <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                                            {{ (int) $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[13.5px] font-bold text-black dark:text-white tabular-nums shrink-0">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Card Footer --}}
                    <div class="pt-3 border-t border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 text-[12.5px] text-black/60 dark:text-white/60">
                            <span class="inline-flex items-center gap-1">
                                <i data-lucide="{{ $order->fulfillment_type === 'pickup' ? 'store' : 'truck' }}" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ $order->fulfillment_type === 'pickup' ? 'Ambil di Toko' : 'Kurir Toko' }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="text-black/45 dark:text-white/45">{{ $order->items->count() }} item</span>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <div class="text-left sm:text-right">
                                <span class="text-[11px] text-black/50 dark:text-white/50 block">Total Pesanan</span>
                                <span class="text-[16px] font-extrabold text-[#007AFF] tabular-nums">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($order->status === 'pending_payment')
                                    <a href="{{ route('customer.orders.detail', $order->id) }}#upload-proof"
                                       class="h-9 px-3.5 rounded-full bg-[#FF9500] text-white text-[12.5px] font-semibold hover:opacity-90 transition active:scale-95 flex items-center gap-1.5 shadow-xs">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                        <span>Upload Bukti Bayar</span>
                                    </a>
                                @endif

                                <a href="{{ route('customer.orders.detail', $order->id) }}"
                                   class="h-9 px-4 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white text-[12.5px] font-semibold transition active:scale-95 flex items-center gap-1">
                                    <span>Lihat Rincian</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach

            {{-- Pagination Links --}}
            <div class="pt-2">
                {{ $orders->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
