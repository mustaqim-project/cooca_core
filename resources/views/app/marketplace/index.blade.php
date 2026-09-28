@extends('layouts.app', [
    'title' => 'Integrasi Marketplace - ' . $business->name,
    'headerTitle' => 'Integrasi Marketplace',
    'headerSubtitle' => 'Kelola multi-channel Shopee, TikTok Shop, dan Tokopedia dengan sinkronisasi harga & stok otomatis',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    disconnectModal: false,
    submitting: false,
    selectedChannel: '',
    selectedChannelName: '',
    openDisconnect(channel, name) {
        this.selectedChannel = channel;
        this.selectedChannelName = name;
        this.submitting = false;
        this.disconnectModal = true;
    }
}">

    <!-- ========================================== -->
    <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
    <!-- ========================================== -->
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
        <a href="{{ route('products.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">Produk &amp; Stok</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
        <span class="text-black/80 dark:text-white/80 font-medium">Integrasi Marketplace</span>
    </nav>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                               -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                    <span>Omnichannel Sync Engine</span>
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold inline-flex items-center gap-1.5 bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    <span>Multi-Business Isolated</span>
                </span>
            </div>
            <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                Hub Integrasi Marketplace
            </h1>
            <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                Hubungkan toko resmi Anda di Shopee, TikTok Shop, dan Tokopedia. Atur perbedaan harga jual tiap channel, sinkronisasi stok otomatis, dan terima pesanan di satu pintu Cooca.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ route('marketplace-hub.products') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span>Atur Harga &amp; Stok Per Channel</span>
            </a>
            <a href="{{ route('marketplace-hub.orders') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Pesanan Masuk</span>
            </a>
            <a href="{{ route('marketplace-hub.logs') }}"
                class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] flex items-center justify-center gap-1.5 cursor-pointer"
                title="Log Sinkronisasi">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Log</span>
            </a>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. BENTO STATS SUMMARY (4 METRICS)                     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Connected Channels -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">Toko Terhubung</span>
                <i data-lucide="plug-2" class="w-4 h-4 text-[#007AFF]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold text-black dark:text-white tracking-tight">
                {{ $accounts->where('status', 'connected')->count() }} <span class="text-[14px] font-medium text-black/40 dark:text-white/40">/ 3 Channel</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Shopee, TikTok Shop, Tokopedia</p>
        </div>

        <!-- Metric 2: Mapped Products -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">Produk Terpetakan</span>
                <i data-lucide="layers" class="w-4 h-4 text-[#34C759]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold text-black dark:text-white tracking-tight">
                {{ number_format($totalMappings) }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">SKU aktif tersinkronisasi</p>
        </div>

        <!-- Metric 3: Synced Orders -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">Pesanan Marketplace</span>
                <i data-lucide="package-check" class="w-4 h-4 text-[#FF9500]"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold text-black dark:text-white tracking-tight">
                {{ number_format($totalOrders) }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Total pesanan masuk via integrasi</p>
        </div>

        <!-- Metric 4: Sync Error Status -->
        <div class="rounded-[20px] p-4 sm:p-5 bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 backdrop-blur-md shadow-xs space-y-2">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50">
                <span class="text-[12px] font-semibold">Status Sinkronisasi</span>
                <i data-lucide="{{ $totalErrors > 0 ? 'alert-triangle' : 'check-circle-2' }}" class="w-4 h-4 {{ $totalErrors > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }}"></i>
            </div>
            <div class="text-[24px] sm:text-[28px] font-extrabold {{ $totalErrors > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }} tracking-tight">
                {{ $totalErrors > 0 ? $totalErrors . ' Error' : 'Normal (100%)' }}
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">
                {{ $totalErrors > 0 ? 'Ada error perlu ditinjau' : 'Semua jalur sinkronisasi aktif' }}
            </p>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO CHANNEL CARDS (SHOPEE, TIKTOK, TOKOPEDIA)     -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- =================== CHANNEL 1: SHOPEE =================== -->
        @php
            $shopeeAccount = $channels['shopee']['account'] ?? null;
            $shopeeConnected = $shopeeAccount && $shopeeAccount->status === 'connected';
        @endphp
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 flex flex-col justify-between space-y-5 shadow-xs transition-all hover:shadow-md">
            <div class="space-y-4">
                <!-- Channel Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-[16px] bg-[#EE4D2D]/15 text-[#EE4D2D] flex items-center justify-center font-black text-base shadow-xs">
                            SP
                        </div>
                        <div>
                            <h2 class="text-[16px] font-bold text-black dark:text-white">Shopee Indonesia</h2>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Shopee Open Platform V2</p>
                        </div>
                    </div>
                    @if($shopeeConnected)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>Terhubung</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                            <span>Belum Terhubung</span>
                        </span>
                    @endif
                </div>

                <!-- Channel Body Info -->
                @if($shopeeConnected)
                    <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Nama Toko:</span>
                            <span class="font-bold text-black dark:text-white truncate max-w-[170px]">{{ $shopeeAccount->shop_name ?? 'Shopee Store' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Shop ID:</span>
                            <span class="font-mono text-black/80 dark:text-white/80 text-[11px]">{{ $shopeeAccount->shop_id }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Sinkronisasi Terakhir:</span>
                            <span class="text-black/70 dark:text-white/70 text-[11.5px]">{{ $shopeeAccount->last_synced_at?->diffForHumans() ?? 'Belum ada' }}</span>
                        </div>
                    </div>

                    <!-- Active Toggle Form -->
                    <form method="POST" action="{{ route('marketplace-hub.toggle', 'shopee') }}" class="flex items-center justify-between pt-1">
                        @csrf
                        <div class="space-y-0.5">
                            <span class="text-[12.5px] font-bold text-black dark:text-white">Status Penjualan Aktif</span>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Kirim update harga &amp; stok ke Shopee</p>
                        </div>
                        <button type="submit"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $shopeeAccount->is_active ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $shopeeAccount->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                @else
                    <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed py-2">
                        Hubungkan akun Shopee Seller untuk otomatisasi pembaruan stok berkala dan penetapan harga jual khusus Shopee.
                    </p>
                @endif
            </div>

            <!-- Actions -->
            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center gap-2">
                @if($shopeeConnected)
                    <a href="{{ route('marketplace-hub.products', ['channel' => 'shopee']) }}"
                        class="flex-1 h-9 px-3 rounded-[12px] text-[12px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                        <span>Atur Produk</span>
                    </a>
                    <button type="button" @click="openDisconnect('shopee', 'Shopee Indonesia')"
                        class="h-9 px-3 rounded-[12px] text-[12px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-all flex items-center justify-center gap-1 cursor-pointer"
                        title="Putuskan Koneksi">
                        <i data-lucide="unlink" class="w-3.5 h-3.5"></i>
                    </button>
                @else
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'shopee') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full h-10 px-4 rounded-[12px] text-[13px] font-bold text-white bg-[#EE4D2D] hover:bg-[#E03A1A] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <i data-lucide="plug" class="w-4 h-4"></i>
                            <span>Hubungkan Shopee</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- =================== CHANNEL 2: TIKTOK SHOP + TOKOPEDIA =================== -->
        @php
            $tiktokAccount = $channels['tiktok_shop']['account'] ?? $channels['tokopedia']['account'] ?? null;
            $tiktokConnected = $tiktokAccount && $tiktokAccount->status === 'connected';
        @endphp
        <div class="col-span-1 md:col-span-2 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 flex flex-col justify-between space-y-5 shadow-xs transition-all hover:shadow-md">
            <div class="space-y-4">
                <!-- Channel Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-gradient-to-br from-black via-[#1C1C1E] to-[#00AA5B] text-white flex items-center justify-center font-black text-sm shadow-xs tracking-tight">
                            TT+TP
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-[17px] font-bold text-black dark:text-white">TikTok Shop + Tokopedia</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/20">1 Terpadu</span>
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50">TikTok Shop Partner Center (Kanal Penjualan TikTok &amp; Tokopedia Indonesia)</p>
                        </div>
                    </div>
                    @if($tiktokConnected)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                            <span>Terhubung Aktif</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                            <span>Belum Terhubung</span>
                        </span>
                    @endif
                </div>

                <!-- Channel Body Info -->
                @if($tiktokConnected)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <div class="space-y-0.5">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">Nama Toko Resmi:</span>
                            <span class="font-bold text-black dark:text-white text-[13px] truncate block">{{ $tiktokAccount->shop_name ?? 'TikTok Shop + Tokopedia Store' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">Shop Cipher / Seller ID:</span>
                            <span class="font-mono text-black/80 dark:text-white/80 text-[12px] block">{{ $tiktokAccount->shop_id }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">Sinkronisasi Terakhir:</span>
                            <span class="text-black/80 dark:text-white/80 text-[12px] font-medium block">{{ $tiktokAccount->last_synced_at?->diffForHumans() ?? 'Belum ada' }}</span>
                        </div>
                    </div>

                    <!-- Active Toggle Form -->
                    <form method="POST" action="{{ route('marketplace-hub.toggle', 'tiktok_shop') }}" class="flex items-center justify-between pt-1">
                        @csrf
                        <div class="space-y-0.5">
                            <span class="text-[13px] font-bold text-black dark:text-white">Status Penjualan &amp; Sinkronisasi Aktif</span>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">Kirim pembaruan harga multi-channel dan stok inventori otomatis ke TikTok Shop &amp; Tokopedia</p>
                        </div>
                        <button type="submit"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $tiktokAccount->is_active ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $tiktokAccount->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </form>
                @else
                    <div class="p-4 rounded-[18px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/15 space-y-2">
                        <p class="text-[13px] text-black/75 dark:text-white/75 leading-relaxed">
                            Hubungkan akun <strong>TikTok Shop Partner</strong> Anda untuk mengelola penjualan TikTok Shop dan Tokopedia dalam 1 otorisasi terpadu. Sistem akan otomatis menyinkronkan stok gudang dan menarik pesanan masuk secara real-time.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                @if($tiktokConnected)
                    <div class="flex items-center gap-2 flex-1">
                        <a href="{{ route('marketplace-hub.products', ['channel' => 'tiktok_shop']) }}"
                            class="h-10 px-4 rounded-[12px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <span>Atur Harga &amp; Stok TikTok</span>
                        </a>
                        <a href="{{ route('marketplace-hub.products', ['channel' => 'tokopedia']) }}"
                            class="h-10 px-4 rounded-[12px] text-[12.5px] font-bold text-[#00AA5B] bg-[#00AA5B]/10 hover:bg-[#00AA5B]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <span>Atur Harga &amp; Stok Tokopedia</span>
                        </a>
                    </div>
                    <button type="button" @click="openDisconnect('tiktok_shop', 'TikTok Shop + Tokopedia')"
                        class="h-10 px-3.5 rounded-[12px] text-[12px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                        title="Putuskan Koneksi">
                        <i data-lucide="unlink" class="w-4 h-4"></i>
                        <span class="text-[12px]">Putus Koneksi</span>
                    </button>
                @else
                    <form method="POST" action="{{ route('marketplace-hub.connect', 'tiktok-tokopedia') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full h-11 px-5 rounded-[14px] text-[13.5px] font-bold text-white bg-gradient-to-r from-black via-[#1C1C1E] to-[#00AA5B] hover:opacity-95 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 shadow-md shadow-black/10 cursor-pointer">
                            <i data-lucide="plug" class="w-4 h-4"></i>
                            <span>Hubungkan TikTok Shop + Tokopedia (1 Otorisasi)</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL KONFIRMASI PUTUS KONEKSI (BENTO HIG)         -->
    <!-- ===================================================== -->
    <div x-show="disconnectModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="disconnectModal = false"
            class="w-full max-w-lg rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 space-y-5 shadow-2xl">
            
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                        <i data-lucide="unlink" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Putuskan Hubungan Akun?</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50" x-text="'Koneksi ke ' + selectedChannelName + ' akan dinonaktifkan.'"></p>
                    </div>
                </div>
                <button type="button" @click="disconnectModal = false" class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 space-y-2">
                <div class="flex items-center gap-2 text-[#FF3B30] text-[12.5px] font-bold">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>Dampak Operasional Pemutusan:</span>
                </div>
                <ul class="text-[12px] text-black/70 dark:text-white/70 space-y-1 list-disc list-inside">
                    <li>Sinkronisasi harga dan stok fisik dari COOCA akan berhenti seketika.</li>
                    <li>Pesanan baru di marketplace tidak lagi otomatis ditarik ke kasir.</li>
                    <li>Histori transaksi dan pemetaan produk Anda tetap aman tersimpan.</li>
                </ul>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="disconnectModal = false" :disabled="submitting"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer">
                    Batal
                </button>
                <form :action="'{{ url('marketplace-hub/disconnect') }}/' + selectedChannel" method="POST" @submit="submitting = true">
                    @csrf
                    <button type="submit" :disabled="submitting"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                        <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Memproses...' : 'Ya, Putuskan Koneksi'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
