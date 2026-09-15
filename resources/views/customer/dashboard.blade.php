@extends('layouts.customer', ['title' => 'Dashboard Pelanggan'])

@section('content')
<div class="space-y-6">

    {{-- Bento Header / Loyalty Tier Card --}}
    <div class="bento-card p-6 sm:p-8 relative overflow-hidden bg-gradient-to-br from-white via-white to-[#007AFF]/5 dark:from-[#1C1C1E] dark:via-[#1C1C1E] dark:to-[#007AFF]/10">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                        Tier {{ $stats['membership_tier'] }} Member
                    </span>
                    <span class="text-[12px] text-black/50 dark:text-white/50">COOCA Loyalty ID: #{{ substr($customer->id, 0, 8) }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">
                    Halo, {{ $customer->name }}! 👋
                </h1>
                <p class="text-[13.5px] text-black/60 dark:text-white/60 max-w-xl leading-relaxed">
                    Selamat datang di portal pembeli COOCA. Kelola pesanan, unggah bukti transfer, dan kumpulkan poin belanja UMKM Anda di sini.
                </p>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 shrink-0 bg-black/[0.03] dark:bg-white/[0.04] p-3.5 sm:p-4 rounded-[20px] border border-black/5 dark:border-white/10">
                <div class="text-center px-2 sm:px-3">
                    <span class="text-[11px] uppercase tracking-wider text-black/50 dark:text-white/50 font-bold block">Poin Reward</span>
                    <span class="text-xl sm:text-2xl font-black text-[#FF9500] tabular-nums">{{ number_format($stats['points_balance'], 0, ',', '.') }}</span>
                </div>
                <div class="w-px h-9 bg-black/10 dark:bg-white/10"></div>
                <div class="text-center px-2 sm:px-3">
                    <span class="text-[11px] uppercase tracking-wider text-black/50 dark:text-white/50 font-bold block">Total Belanja</span>
                    <span class="text-[15px] sm:text-[17px] font-bold text-black dark:text-white tabular-nums">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stat Bento Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        {{-- Menunggu Bayar --}}
        <a href="{{ route('customer.orders', ['status' => 'pending_payment']) }}"
           class="bento-card bento-card-interactive p-5 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold text-black/55 dark:text-white/55">Menunggu Bayar</span>
                <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-[#FF9500] tabular-nums">{{ $stats['pending_payment'] }}</div>
                <span class="text-[11.5px] text-black/45 dark:text-white/45 block mt-0.5">Segera unggah bukti bayar</span>
            </div>
        </a>

        {{-- Sedang Diproses --}}
        <a href="{{ route('customer.orders', ['status' => 'processing']) }}"
           class="bento-card bento-card-interactive p-5 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold text-black/55 dark:text-white/55">Sedang Diproses</span>
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-[#007AFF] tabular-nums">{{ $stats['processing'] }}</div>
                <span class="text-[11.5px] text-black/45 dark:text-white/45 block mt-0.5">Pesanan disiapkan toko</span>
            </div>
        </a>

        {{-- Selesai --}}
        <a href="{{ route('customer.orders', ['status' => 'completed']) }}"
           class="bento-card bento-card-interactive p-5 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold text-black/55 dark:text-white/55">Pesanan Selesai</span>
                <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-[#34C759] tabular-nums">{{ $stats['completed'] }}</div>
                <span class="text-[11.5px] text-black/45 dark:text-white/45 block mt-0.5">Transaksi sukses</span>
            </div>
        </a>

        {{-- Keranjang Belanja --}}
        <a href="{{ route('customer.cart') }}"
           class="bento-card bento-card-interactive p-5 flex flex-col justify-between group">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold text-black/55 dark:text-white/55">Keranjang Saya</span>
                <div class="w-8 h-8 rounded-full bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tabular-nums">Lihat</div>
                <span class="text-[11.5px] text-[#007AFF] font-semibold block mt-0.5 flex items-center gap-1">
                    Buka Keranjang Belanja &rarr;
                </span>
            </div>
        </a>
    </div>

    {{-- Pesanan Terbaru Section --}}
    <div class="bento-card p-6 sm:p-7 space-y-5">
        <div class="flex items-center justify-between gap-4 border-b border-black/5 dark:border-white/5 pb-4">
            <div>
                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Pesanan Terbaru</h2>
                <p class="text-[12.5px] text-black/50 dark:text-white/50">Status pelacakan dan konfirmasi pembayaran pesanan Anda.</p>
            </div>
            <a href="{{ route('customer.orders') }}"
               class="text-[12.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Lihat Semua ({{ $stats['total_orders'] }})</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="py-12 text-center text-black/40 dark:text-white/40 space-y-3">
                <i data-lucide="shopping-bag" class="w-12 h-12 mx-auto stroke-1 opacity-50"></i>
                <div class="space-y-1">
                    <p class="text-[14px] font-semibold text-black/70 dark:text-white/70">Belum ada riwayat pesanan</p>
                    <p class="text-[12.5px]">Mulai belanja di salah satu toko UMKM mitra COOCA sekarang.</p>
                </div>
                <a href="{{ route('public.discovery.index') }}"
                   class="inline-flex items-center gap-2 h-10 px-5 rounded-full bg-[#007AFF] text-white text-[13px] font-semibold hover:opacity-90 transition shadow-sm">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                    <span>Jelajahi Toko UMKM</span>
                </a>
            </div>
        @else
            <div class="divide-y divide-black/5 dark:divide-white/5">
                @foreach($recentOrders as $order)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        
                        {{-- Order Overview --}}
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono font-bold text-[14px] text-black dark:text-white">{{ $order->order_number }}</span>
                                <span class="text-black/30 dark:text-white/30">&bull;</span>
                                <span class="text-[12.5px] font-semibold text-black/70 dark:text-white/70">{{ $order->business->name ?? 'Toko Mitra' }}</span>
                                <span class="text-black/30 dark:text-white/30">&bull;</span>
                                <span class="text-[12px] text-black/45 dark:text-white/45">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>

                            <p class="text-[12.5px] text-black/60 dark:text-white/60 line-clamp-1">
                                {{ $order->items->pluck('product_name')->take(2)->join(', ') }}
                                @if($order->items->count() > 2)
                                    <span class="text-black/40 dark:text-white/40">(+{{ $order->items->count() - 2 }} item lainnya)</span>
                                @endif
                            </p>
                        </div>

                        {{-- Total & Status & Action --}}
                        <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                            <div class="text-left sm:text-right">
                                <span class="text-[14px] font-extrabold text-black dark:text-white tabular-nums block">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </span>
                                
                                {{-- Status Badges --}}
                                @if($order->status === 'pending_payment')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF9500]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                                        <span>Menunggu Pembayaran</span>
                                    </span>
                                @elseif($order->status === 'proof_submitted')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#007AFF]">
                                        <i data-lucide="file-check" class="w-3 h-3"></i>
                                        <span>Menunggu Verifikasi Toko</span>
                                    </span>
                                @elseif(in_array($order->status, ['paid', 'processing', 'ready']))
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#007AFF]">
                                        <i data-lucide="package" class="w-3 h-3"></i>
                                        <span>Sedang Diproses</span>
                                    </span>
                                @elseif($order->status === 'completed')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759]">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>Selesai</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF3B30]">
                                        <span>Dibatalkan</span>
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if($order->status === 'pending_payment')
                                    <a href="{{ route('customer.orders.detail', $order->id) }}#upload-proof"
                                       class="h-8 px-3 rounded-full bg-[#FF9500] text-white text-[12px] font-semibold hover:opacity-90 transition active:scale-95 flex items-center gap-1 shadow-xs">
                                        <i data-lucide="upload" class="w-3 h-3"></i>
                                        <span>Upload Bukti</span>
                                    </a>
                                @endif

                                <a href="{{ route('customer.orders.detail', $order->id) }}"
                                   class="h-8 px-3 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 text-[12px] font-semibold transition active:scale-95 flex items-center gap-1">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
