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
                <a href="{{ url('/b/' . $order->business->slug) }}" target="_blank"
                   class="h-9 px-4 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white text-[12.5px] font-semibold transition active:scale-95 flex items-center gap-1.5">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Kunjungi Toko</span>
                </a>
                <a href="{{ url('/b/' . $order->business->slug . '/order/' . $order->tracking_token) }}" target="_blank"
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
                    Verifikasi Bayar
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

        {{-- Right Column: Payment & Upload Proof Widget (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Payment Instruction Box --}}
            <div class="bento-card p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                    <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Metode Pembayaran</h2>
                    <span class="text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full {{ $order->isPaid() ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20' }}">
                        {{ $order->isPaid() ? 'Lunas' : 'Menunggu Pembayaran' }}
                    </span>
                </div>

                @if($order->isTripay())
                    {{-- TriPay Automated Gateway Details --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[14px] text-black dark:text-white">{{ $order->payment_channel ?? 'TriPay Gateway' }}</span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                    Otomatis
                                </span>
                            </div>
                            <span class="text-[11px] text-[#34C759] font-bold">Bebas Biaya Admin</span>
                        </div>

                        {{-- Case 1: QRIS Dynamic --}}
                        @if($order->gateway_qr_url)
                            <div class="p-4 rounded-[20px] bg-white border border-black/10 dark:border-white/10 shadow-inner flex flex-col items-center justify-center text-center space-y-3">
                                @if(!$order->isPaid())
                                    <img src="{{ $order->gateway_qr_url }}" alt="QRIS TriPay" class="w-52 h-52 object-contain rounded-lg shadow-sm">
                                    <div class="space-y-1">
                                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest flex items-center justify-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                                            <span>Scan QRIS untuk Bayar</span>
                                        </div>
                                        <p class="text-[11px] text-black/50 dark:text-white/50 max-w-xs">
                                            Buka BCA, Mandiri, BRI, GoPay, OVO, ShopeePay, atau DANA, lalu scan kode di atas.
                                        </p>
                                    </div>

                                    <a href="{{ $order->gateway_qr_url }}" target="_blank" download="qris-order-{{ $order->order_number }}.png"
                                        class="h-9 px-4 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 text-black dark:text-white text-xs font-semibold flex items-center gap-1.5 transition">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                        <span>Unduh Gambar QR</span>
                                    </a>
                                @else
                                    <div class="py-6 text-center space-y-2">
                                        <div class="w-12 h-12 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center mx-auto">
                                            <i data-lucide="check" class="w-6 h-6"></i>
                                        </div>
                                        <h4 class="font-bold text-black text-sm">Pembayaran QRIS Berhasil</h4>
                                        <p class="text-xs text-gray-500">Transaksi telah diverifikasi otomatis oleh sistem.</p>
                                    </div>
                                @endif
                            </div>

                        {{-- Case 2: Virtual Account --}}
                        @elseif($order->gateway_pay_code)
                            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-2.5">
                                <span class="text-[11px] text-black/50 dark:text-white/50 block font-medium">Nomor Virtual Account:</span>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xl font-extrabold text-black dark:text-white tracking-wider tabular-nums select-all">
                                        {{ $order->gateway_pay_code }}
                                    </span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $order->gateway_pay_code }}').then(() => alert('Nomor VA berhasil disalin!'));"
                                        class="h-8 px-3 rounded-full bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 text-xs font-bold transition flex items-center gap-1">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        <span>Salin</span>
                                    </button>
                                </div>
                                <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                    Transfer tepat sesuai total tagihan agar pembayaran terverifikasi otomatis dalam beberapa detik.
                                </p>
                            </div>
                        @endif

                        {{-- Expiration Notice --}}
                        @if(!$order->isPaid() && $order->gateway_expired_at)
                            <div class="text-center text-[11.5px] text-black/50 dark:text-white/50 font-medium">
                                Batas Pembayaran: <strong class="text-[#FF9500]">{{ $order->gateway_expired_at->translatedFormat('d M Y, H:i') }}</strong>
                            </div>
                        @endif

                        {{-- Pay URL Fallback Button --}}
                        @if(!$order->isPaid() && $order->gateway_pay_url)
                            <a href="{{ $order->gateway_pay_url }}" target="_blank"
                                class="w-full h-11 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13px] flex items-center justify-center gap-1.5 shadow-sm transition">
                                <span>Buka Halaman Pembayaran TriPay</span>
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Live Polling Script for TriPay Unpaid Orders --}}
                    @if(!$order->isPaid())
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
                                }, 4000);
                            });
                        </script>
                    @endif

                @elseif($order->paymentMethod)
                    {{-- Manual Bank Transfer Instruction --}}
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[14px] text-black dark:text-white">{{ $order->paymentMethod->bank_name }}</span>
                            <span class="text-[10.5px] font-bold uppercase px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                {{ strtoupper($order->paymentMethod->type) }}
                            </span>
                        </div>
                        @if($order->paymentMethod->account_number)
                            <div>
                                <span class="text-[11px] text-black/50 dark:text-white/50 block">Nomor Rekening Tujuan:</span>
                                <div class="flex items-center justify-between mt-0.5">
                                    <span class="font-mono text-lg font-extrabold text-black dark:text-white tracking-wider tabular-nums">
                                        {{ $order->paymentMethod->account_number }}
                                    </span>
                                </div>
                                <span class="text-[12px] text-black/60 dark:text-white/60 font-medium block">
                                    a/n {{ $order->paymentMethod->account_holder }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if($order->isManualPayment())
            {{-- Payment Proof Upload Widget --}}
            <div id="upload-proof" class="bento-card p-6 space-y-4" x-data="{
                previewUrl: null,
                fileName: '',
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (ev) => { this.previewUrl = ev.target.result; };
                            reader.readAsDataURL(file);
                        } else {
                            this.previewUrl = null;
                        }
                    }
                }
            }">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                    <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Bukti Transfer Pembayaran</h2>
                    @if($order->paymentProofs->isNotEmpty())
                        <span class="text-[11px] font-bold text-[#007AFF]">{{ $order->paymentProofs->count() }} Terunggah</span>
                    @endif
                </div>

                {{-- Status Alerts --}}
                @if($order->status === 'proof_submitted')
                    <div class="p-3.5 rounded-[14px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] text-[12.5px] font-medium flex items-start gap-2.5">
                        <i data-lucide="clock" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span>Bukti pembayaran Anda telah diterima dan sedang menunggu verifikasi oleh toko.</span>
                    </div>
                @elseif($order->status === 'payment_rejected' && $order->rejection_reason)
                    <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium space-y-1">
                        <div class="flex items-center gap-1.5 font-bold">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                            <span>Bukti Pembayaran Ditolak</span>
                        </div>
                        <p class="text-[12px] pl-5.5">Alasan: {{ $order->rejection_reason }}. Silakan unggah bukti pembayaran yang valid di bawah.</p>
                    </div>
                @endif

                {{-- Upload Form --}}
                @if($order->canSubmitProof() || $order->status === 'payment_rejected')
                    <form action="{{ route('customer.orders.upload_proof', $order->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        {{-- File Input Box --}}
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                Unggah Foto / File Bukti Transfer <span class="text-red-500">*</span>
                            </label>
                            <label class="block border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] rounded-[18px] p-4 text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01] group">
                                <input type="file" name="payment_proof" required accept="image/jpeg,image/png,image/webp,application/pdf"
                                       @change="handleFile($event)" class="sr-only">
                                
                                <template x-if="previewUrl">
                                    <div class="space-y-2">
                                        <img :src="previewUrl" alt="Pratinjau Bukti" class="max-h-48 mx-auto rounded-[12px] object-contain shadow-md">
                                        <span class="text-[11.5px] font-bold text-[#007AFF] block truncate" x-text="fileName"></span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45">Klik untuk ganti file</span>
                                    </div>
                                </template>

                                <template x-if="!previewUrl">
                                    <div class="space-y-2 py-3">
                                        <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto group-hover:scale-110 transition">
                                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <span class="text-[13px] font-bold text-black dark:text-white block" x-text="fileName || 'Pilih Berkas Bukti Transfer'"></span>
                                            <span class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5">Format: JPG, PNG, WEBP, atau PDF (Maks. 5 MB)</span>
                                        </div>
                                    </div>
                                </template>
                            </label>
                        </div>

                        {{-- Bank & Sender Name --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Bank Pengirim</label>
                                <input type="text" name="sender_bank" placeholder="Contoh: BCA, BRI, Mandiri"
                                       class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">Nama Pemilik Rekening</label>
                                <input type="text" name="sender_account_name" placeholder="Nama di rekening"
                                       class="w-full h-10 px-3 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full h-11 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[13.5px] transition flex items-center justify-center gap-2 shadow-sm active:scale-[0.98]">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Kirim Bukti Pembayaran</span>
                        </button>
                    </form>
                @endif

                {{-- Previous Proofs History --}}
                @if($order->paymentProofs->isNotEmpty())
                    <div class="pt-3 border-t border-black/5 dark:border-white/5 space-y-2.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Riwayat Unggahan:</span>
                        @foreach($order->paymentProofs as $proof)
                            <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between text-[12px]">
                                <div class="space-y-0.5">
                                    <div class="font-semibold text-black dark:text-white">
                                        {{ $proof->sender_bank ? $proof->sender_bank . ' a/n ' . $proof->sender_account_name : 'Bukti Transfer' }}
                                    </div>
                                    <span class="text-[11px] text-black/45 dark:text-white/45">{{ $proof->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold uppercase {{ $proof->status === 'verified' ? 'bg-[#34C759]/10 text-[#34C759]' : ($proof->status === 'rejected' ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : 'bg-[#007AFF]/10 text-[#007AFF]') }}">
                                    {{ $proof->status === 'verified' ? 'Diverifikasi' : ($proof->status === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
