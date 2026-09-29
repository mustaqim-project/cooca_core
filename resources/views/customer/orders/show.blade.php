@extends('layouts.customer', ['title' => 'Rincian Pesanan ' . $order->order_number])

@section('content')
<div class="space-y-6">

    {{-- Top Back Link & Order Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('customer.orders') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#007AFF] hover:underline mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Riwayat Belanja</span>
            </a>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">
                    Pesanan #{{ $order->order_number }}
                </h1>
                
                {{-- Semantic Status Badge --}}
                @if($order->status === 'pending_payment')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#FF9500] animate-pulse"></span>
                        <span>Menunggu Pembayaran</span>
                    </span>
                @elseif($order->status === 'proof_submitted')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        <span>Menunggu Verifikasi Toko</span>
                    </span>
                @elseif(in_array($order->status, ['paid', 'processing', 'ready']))
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                        <span>Sedang Diproses Toko</span>
                    </span>
                @elseif($order->status === 'completed')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Pesanan Selesai</span>
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                        Pesanan Dibatalkan
                    </span>
                @endif

                @if($order->groupOrder)
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span>Pesan Bareng</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($order->business)
                <a href="{{ $order->business->public_url }}" target="_blank"
                   class="h-9 px-4 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white text-[12.5px] font-semibold transition active:scale-95 flex items-center gap-1.5">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Kunjungi Toko</span>
                </a>
                <a href="{{ url('/' . $order->business->slug . '/order/' . $order->tracking_token) }}" target="_blank"
                   class="h-9 px-4 rounded-full bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] text-[12.5px] font-semibold transition active:scale-95 flex items-center gap-1.5 border border-[#007AFF]/20">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Lacak Publik</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Order Progress Timeline (Apple HIG) --}}
    @php
        $stepIndex = match($order->status) {
            'pending_payment' => 1,
            'proof_submitted' => 2,
            'paid', 'processing' => 3,
            'ready', 'fulfilled' => 4,
            'completed' => 5,
            default => 0,
        };
    @endphp
    @if($stepIndex > 0)
    <div class="bento-card p-5 sm:p-6 overflow-hidden">
        <div class="grid grid-cols-5 gap-2 text-center relative">
            
            {{-- Step 1 --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 1 ? 'bg-[#34C759] text-white shadow-xs' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40' }}">
                    1
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 1 ? 'text-black dark:text-white font-bold' : 'text-black/40 dark:text-white/40' }}">
                    Dipesan
                </span>
            </div>

            {{-- Step 2 --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 2 ? 'bg-[#34C759] text-white shadow-xs' : ($stepIndex === 1 ? 'bg-[#FF9500] text-white animate-pulse' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40') }}">
                    2
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 2 ? 'text-black dark:text-white font-bold' : ($stepIndex === 1 ? 'text-[#FF9500] font-bold' : 'text-black/40 dark:text-white/40') }}">
                    Pembayaran
                </span>
            </div>

            {{-- Step 3 --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 3 ? 'bg-[#34C759] text-white shadow-xs' : ($stepIndex === 2 ? 'bg-[#007AFF] text-white animate-pulse' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40') }}">
                    3
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 3 ? 'text-black dark:text-white font-bold' : 'text-black/40 dark:text-white/40' }}">
                    Diproses
                </span>
            </div>

            {{-- Step 4 --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 4 ? 'bg-[#34C759] text-white shadow-xs' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40' }}">
                    4
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 4 ? 'text-black dark:text-white font-bold' : 'text-black/40 dark:text-white/40' }}">
                    {{ $order->fulfillment_type === 'pickup' ? 'Siap Diambil' : 'Dikirim' }}
                </span>
            </div>

            {{-- Step 5 --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 5 ? 'bg-[#34C759] text-white shadow-xs' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40' }}">
                    5
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 5 ? 'text-[#34C759] font-bold' : 'text-black/40 dark:text-white/40' }}">
                    Selesai
                </span>
            </div>

        </div>
    </div>
    @endif

    {{-- GROUP ORDER & SPLIT BILL CARD (IF APPLICABLE) --}}
    @if ($order->groupOrder)
        @php
            $group = $order->groupOrder;
            $splitSummary = $group->getSplitBillSummary();
            $waBillText = "Halo rekan-rekan! Rincian patungan pesanan {$order->business->name} ({$group->title}) - Order #{$order->order_number}:\n\n";
            foreach ($splitSummary as $s) {
                $waBillText .= "- {$s['member_name']}: Rp " . number_format($s['subtotal'], 0, ',', '.') . "\n";
            }
            $waBillText .= "\nTotal: Rp " . number_format($order->total_amount, 0, ',', '.') . "\nTerima kasih!";
        @endphp
        <div class="bento-card p-5 sm:p-6 bg-[#AF52DE]/5 border-[#AF52DE]/20 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#AF52DE]/15">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#AF52DE] text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">
                                Pesanan Bersama: {{ $group->title }}
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">
                                Group Order
                            </span>
                        </div>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                            Host: <strong class="text-black dark:text-white">{{ $group->host?->name ?? $order->customer_name }}</strong> &bull; Total {{ count($splitSummary) }} Anggota Bergabung
                        </p>
                    </div>
                </div>

                <button type="button"
                    onclick="navigator.clipboard.writeText({{ json_encode($waBillText) }}).then(() => alert('Rincian tagihan patungan berhasil disalin! Silakan bagikan ke WhatsApp grup kantor.'));"
                    class="h-9 px-4 rounded-full bg-[#AF52DE] hover:bg-[#AF52DE]/90 text-white font-semibold text-[12.5px] transition active:scale-[0.98] shadow-sm flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                    <span>Salin Tagihan Patungan</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach ($splitSummary as $s)
                    <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-1 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[13px] text-black dark:text-white flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                <span>{{ $s['member_name'] }}</span>
                            </span>
                            <span class="font-bold text-[13px] text-[#AF52DE] tabular-nums">
                                Rp {{ number_format($s['subtotal'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-[11.5px] text-black/50 dark:text-white/50 pl-5">
                            @foreach ($s['items'] as $item)
                                <div>{{ (float) $item->quantity }}x {{ $item->product?->name ?? 'Produk' }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Main 2-Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left Column: Items & Shipping Details (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Items Card --}}
            <div class="bento-card p-6 space-y-4">
                <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight pb-3 border-b border-black/5 dark:border-white/5">
                    Daftar Produk Pesanan
                </h2>

                <div class="divide-y divide-black/5 dark:divide-white/5">
                    @foreach($order->items as $item)
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-start justify-between gap-3.5">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-14 h-14 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 shrink-0 overflow-hidden flex items-center justify-center">
                                    @if($item->product?->image_url)
                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="package" class="w-6 h-6 text-black/30 dark:text-white/30"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-[14px] font-bold text-black dark:text-white leading-snug truncate">{{ $item->product_name }}</h3>
                                    <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums block mt-0.5">
                                        {{ (int) $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </span>
                                    @if($item->notes)
                                        <p class="text-[11.5px] text-black/60 dark:text-white/60 italic mt-1">Catatan: {{ $item->notes }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[14px] font-bold text-black dark:text-white tabular-nums shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Cost Summary --}}
                <div class="pt-4 border-t border-black/5 dark:border-white/5 space-y-2 text-[13px]">
                    <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                        <span>Subtotal Belanja</span>
                        <span class="tabular-nums font-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                        <span>Ongkos Kirim ({{ $order->shipping_courier_name ?: ($order->fulfillment_type === 'pickup' ? 'Ambil di Toko' : 'Kurir Toko') }})</span>
                        <span class="tabular-nums font-semibold">
                            {{ $order->shipping_cost > 0 ? 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') : 'Gratis' }}
                        </span>
                    </div>
                    @if((float) ($order->biteship_service_fee ?? 0) > 0)
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                            <span class="flex items-center gap-1.5">
                                <span>Biaya Layanan Pengiriman</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#007AFF]/10 text-[#007AFF]">Biteship</span>
                            </span>
                            <span class="tabular-nums font-semibold text-black dark:text-white">
                                Rp {{ number_format((float) $order->biteship_service_fee, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-[#34C759]">
                            <span>Diskon / Potongan</span>
                            <span class="tabular-nums font-semibold">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[15px]">
                        <span class="font-bold text-black dark:text-white">Total Pembayaran</span>
                        <span class="text-[20px] font-extrabold text-[#007AFF] tabular-nums">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Shipping & Recipient Info --}}
            <div class="bento-card p-6 space-y-3.5">
                <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight pb-3 border-b border-black/5 dark:border-white/5">
                    Informasi Pengiriman
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Penerima</span>
                        <span class="font-semibold text-black dark:text-white block mt-0.5">{{ $order->customer_name }}</span>
                        <span class="text-black/60 dark:text-white/60 tabular-nums">{{ $order->customer_phone }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Metode Pengiriman</span>
                        <span class="font-semibold text-black dark:text-white block mt-0.5">
                            {{ $order->fulfillment_type === 'pickup' ? 'Ambil Sendiri di Toko (Pickup)' : 'Kurir Toko (Merchant Delivery)' }}
                        </span>
                        @if($order->scheduled_date)
                            <span class="text-[12px] text-[#007AFF] font-medium block">
                                Jadwal: {{ date('d M Y', strtotime($order->scheduled_date)) }} {{ $order->scheduled_time_slot ? '(' . $order->scheduled_time_slot . ')' : '' }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($order->shipping_address)
                    <div class="pt-2">
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Alamat Tujuan</span>
                        <p class="text-[13px] text-black/75 dark:text-white/75 mt-0.5 leading-relaxed">{{ $order->shipping_address }}</p>
                    </div>
                @endif

                @if($order->notes)
                    <div class="pt-2">
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Catatan Pesanan</span>
                        <p class="text-[12.5px] text-black/65 dark:text-white/65 mt-0.5 italic">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- Right Column: Payment & QRIS Widget (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">

            @if(!$order->isPaid())
                @php
                    $qrData = $order->gateway_qr_string ?: ($order->gateway_qr_url ?: ($order->gateway_pay_url ?: ($order->business ? url("/{$order->business->slug}/order/{$order->tracking_token}") : url("/customer/orders/{$order->id}"))));
                    $qrImageUrl = $order->gateway_qr_url ?: ('https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=8&data=' . urlencode($qrData));
                    $expiryTimestamp = $order->gateway_expired_at?->timestamp ?: ($order->reserved_until?->timestamp ?: ($order->created_at->addMinutes(15)->timestamp));
                    $secondsRemaining = max(0, $expiryTimestamp - time());
                    $publicTrackingUrl = $order->business ? url("/{$order->business->slug}/order/{$order->tracking_token}") : null;
                @endphp

                {{-- DYNAMIC QRIS PAYMENT CARD (APPLE BENTO) --}}
                <div class="bento-card p-6 space-y-5 text-center"
                    x-data="{
                        qrisCountdown: {{ $secondsRemaining }},
                        qrisCountdownFormatted: '',
                        init() {
                            this.updateFormatted();
                            const timer = setInterval(() => {
                                if (this.qrisCountdown > 0) {
                                    this.qrisCountdown--;
                                    this.updateFormatted();
                                } else {
                                    clearInterval(timer);
                                }
                            }, 1000);
                        },
                        updateFormatted() {
                            const m = Math.floor(this.qrisCountdown / 60);
                            const s = this.qrisCountdown % 60;
                            this.qrisCountdownFormatted = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                        }
                    }">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10">
                        <div class="text-left">
                            <h2 class="font-bold text-[16px] text-black dark:text-white">QRIS Pembayaran</h2>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Scan via GoPay, OVO, Dana, BCA, Mandiri, atau m-Banking</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                            Bebas Biaya Admin
                        </span>
                    </div>

                    <!-- Amount Box -->
                    <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                        <span class="text-[12.5px] text-black/60 dark:text-white/60 font-medium">Total Tagihan:</span>
                        <span class="font-black text-[20px] text-[#34C759] dark:text-[#30D158] font-mono tabular-nums">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- QR Code Render Card -->
                    <div class="p-4 rounded-[20px] bg-white border border-black/10 shadow-inner flex flex-col items-center justify-center relative mx-auto max-w-xs">
                        <img src="{{ $qrImageUrl }}" alt="QRIS Code Pesanan #{{ $order->order_number }}" class="w-52 h-52 object-contain rounded-xl">
                        <div class="mt-2.5 text-[10.5px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                            <span>Menunggu Pembayaran...</span>
                        </div>
                    </div>

                    <!-- Countdown Timer & Auto Polling Notice -->
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center justify-center gap-1.5 text-black/70 dark:text-white/70 font-semibold">
                            <span>Sisa Waktu Bayar:</span>
                            <span class="font-mono text-[#FF9500] font-extrabold tabular-nums" x-text="qrisCountdownFormatted">15:00</span>
                        </div>
                        <p class="text-[11px] text-black/45 dark:text-white/45 max-w-xs mx-auto">
                            Status pembayaran akan terverifikasi otomatis begitu transaksi selesai tanpa perlu kirim bukti transfer.
                        </p>
                    </div>

                    <!-- Virtual Account Box (If VA is available) -->
                    @if ($order->gateway_pay_code)
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-2 text-left" x-data="{ copied: false }">
                            <div class="flex items-center justify-between">
                                <span class="text-[11.5px] font-semibold text-black/70 dark:text-white/70">Virtual Account ({{ $order->payment_channel }}):</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Otomatis</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 p-2.5 bg-white dark:bg-black/20 rounded-[10px] border border-black/10">
                                <span class="font-mono text-base font-bold text-black dark:text-white tracking-wider tabular-nums">
                                    {{ $order->gateway_pay_code }}
                                </span>
                                <button type="button" @click="navigator.clipboard.writeText('{{ $order->gateway_pay_code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-2.5 py-1 rounded-md bg-[#007AFF] text-white text-[11px] font-bold transition active:scale-95 cursor-pointer">
                                    <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="pt-1 space-y-2">
                        <a href="{{ $qrImageUrl }}" target="_blank" download="qris-pesanan-{{ $order->order_number }}.png"
                            class="w-full h-10 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Unduh Gambar QR</span>
                        </a>

                        @if ($publicTrackingUrl)
                            <a href="{{ $publicTrackingUrl }}" target="_blank"
                                class="w-full h-10 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                <span>Lanjutkan Pembayaran (Halaman Penuh)</span>
                            </a>
                        @endif

                        @if ($order->gateway_pay_url)
                            <a href="{{ $order->gateway_pay_url }}" target="_blank"
                                class="w-full h-10 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                                <span>Buka Gateway TriPay</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Real-Time Status Polling Script --}}
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const statusCheckInterval = setInterval(async () => {
                            try {
                                const res = await fetch("{{ route('customer.orders.status', $order->id) }}", {
                                    headers: { 'Accept': 'application/json' }
                                });
                                if (res.ok) {
                                    const data = await res.json();
                                    if (data.is_paid) {
                                        clearInterval(statusCheckInterval);
                                        window.location.reload();
                                    }
                                }
                            } catch (e) {}
                        }, 3500);
                    });
                </script>

            @else
                {{-- PAID STATUS CARD --}}
                <div class="bento-card p-6 space-y-4 text-center">
                    <div class="w-14 h-14 rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center mx-auto">
                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-black dark:text-white tracking-tight">Pembayaran Telah Lunas</h3>
                        <p class="text-[12.5px] text-black/55 dark:text-white/55">
                            Transaksi diverifikasi otomatis via {{ $order->payment_channel ?: 'QRIS' }}.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1.5 text-left text-[12.5px]">
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                            <span>Metode:</span>
                            <span class="font-bold text-black dark:text-white">{{ $order->payment_channel ?: 'QRIS Otomatis' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                            <span>Waktu Lunas:</span>
                            <span class="font-medium text-black dark:text-white">{{ $order->paid_at ? $order->paid_at->translatedFormat('d M Y, H:i') : $order->updated_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-black/60 dark:text-white/60 pt-1.5 border-t border-black/5 dark:border-white/5">
                            <span>Total Terbayar:</span>
                            <span class="font-bold text-[#34C759] text-[14px] tabular-nums">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
