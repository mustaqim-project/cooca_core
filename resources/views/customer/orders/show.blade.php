@extends('layouts.customer', ['title' => 'Rincian Pesanan ' . $order->order_number])

@section('content')
<div class="space-y-6">

    {{-- Biteship Logistics Tracking Dictionary & Semantic Mappings --}}
    @php
        $biteshipTrackingMap = [
            'confirmed' => [
                'label' => 'Pengiriman Dikonfirmasi',
                'desc' => 'Order has been confirmed. Locating nearest driver to pick up.',
                'desc_id' => 'Pesanan pengiriman dikonfirmasi. Sistem sedang mencari kurir terdekat untuk penjemputan.',
                'badge_class' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                'icon' => 'clock',
            ],
            'allocated' => [
                'label' => 'Kurir Dialokasikan',
                'desc' => 'Courier has been allocated. Waiting to pick up.',
                'desc_id' => 'Kurir telah ditugaskan dan sedang bersiap menuju lokasi toko untuk penjemputan.',
                'badge_class' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                'icon' => 'user-check',
            ],
            'pickingUp' => [
                'label' => 'Kurir Menuju Toko',
                'desc' => 'Courier is on the way to pick up item.',
                'desc_id' => 'Kurir sedang dalam perjalanan menuju lokasi toko untuk mengambil paket.',
                'badge_class' => 'bg-[#5856D6]/10 text-[#5856D6] border-[#5856D6]/20',
                'icon' => 'navigation',
            ],
            'picking_up' => [
                'label' => 'Kurir Menuju Toko',
                'desc' => 'Courier is on the way to pick up item.',
                'desc_id' => 'Kurir sedang dalam perjalanan menuju lokasi toko untuk mengambil paket.',
                'badge_class' => 'bg-[#5856D6]/10 text-[#5856D6] border-[#5856D6]/20',
                'icon' => 'navigation',
            ],
            'picked' => [
                'label' => 'Paket Berhasil Diambil',
                'desc' => 'Item has been picked and ready to be shipped.',
                'desc_id' => 'Paket telah diambil oleh kurir dan siap diberangkatkan ke kota tujuan.',
                'badge_class' => 'bg-[#30B0C7]/10 text-[#30B0C7] border-[#30B0C7]/20',
                'icon' => 'package-check',
            ],
            'inTransit' => [
                'label' => 'Dalam Perjalanan',
                'desc' => 'Item is on the way to the destination.',
                'desc_id' => 'Paket sedang dalam perjalanan menuju hub transit / kota tujuan penerima.',
                'badge_class' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                'icon' => 'truck',
            ],
            'in_transit' => [
                'label' => 'Dalam Perjalanan',
                'desc' => 'Item is on the way to the destination.',
                'desc_id' => 'Paket sedang dalam perjalanan menuju hub transit / kota tujuan penerima.',
                'badge_class' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                'icon' => 'truck',
            ],
            'droppingOff' => [
                'label' => 'Sedang Diantar ke Penerima',
                'desc' => 'Item is on the way to customer.',
                'desc_id' => 'Kurir sedang dalam perjalanan mengantarkan paket langsung ke alamat tujuan Anda.',
                'badge_class' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                'icon' => 'map-pin',
            ],
            'dropping_off' => [
                'label' => 'Sedang Diantar ke Penerima',
                'desc' => 'Item is on the way to customer.',
                'desc_id' => 'Kurir sedang dalam perjalanan mengantarkan paket langsung ke alamat tujuan Anda.',
                'badge_class' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                'icon' => 'map-pin',
            ],
            'returnInTransit' => [
                'label' => 'Paket Dikembalikan',
                'desc' => 'Order is on the way back to the origin.',
                'desc_id' => 'Paket sedang dalam perjalanan kembali ke alamat asal toko pengirim.',
                'badge_class' => 'bg-[#AF52DE]/10 text-[#AF52DE] border-[#AF52DE]/20',
                'icon' => 'corner-down-left',
            ],
            'return_in_transit' => [
                'label' => 'Paket Dikembalikan',
                'desc' => 'Order is on the way back to the origin.',
                'desc_id' => 'Paket sedang dalam perjalanan kembali ke alamat asal toko pengirim.',
                'badge_class' => 'bg-[#AF52DE]/10 text-[#AF52DE] border-[#AF52DE]/20',
                'icon' => 'corner-down-left',
            ],
            'onHold' => [
                'label' => 'Pengiriman Tertahan',
                'desc' => "Your shipment is on hold at the moment. We'll ship your item after it's resolved.",
                'desc_id' => 'Pengiriman paket sedang ditahan sementara oleh ekspedisi. Paket akan dikirim setelah kendala terselesaikan.',
                'badge_class' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                'icon' => 'alert-triangle',
            ],
            'on_hold' => [
                'label' => 'Pengiriman Tertahan',
                'desc' => "Your shipment is on hold at the moment. We'll ship your item after it's resolved.",
                'desc_id' => 'Pengiriman paket sedang ditahan sementara oleh ekspedisi. Paket akan dikirim setelah kendala terselesaikan.',
                'badge_class' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                'icon' => 'alert-triangle',
            ],
            'delivered' => [
                'label' => 'Paket Telah Tiba / Diterima',
                'desc' => 'Item has been delivered.',
                'desc_id' => 'Paket telah berhasil diantar dan diterima oleh penerima.',
                'badge_class' => 'bg-[#34C759]/10 text-[#34C759] border-[#34C759]/20',
                'icon' => 'check-circle-2',
            ],
            'rejected' => [
                'label' => 'Pengiriman Ditolak',
                'desc' => 'Your shipment has been rejected. Please contact Biteship for more information.',
                'desc_id' => 'Pengiriman telah ditolak oleh pihak kurir atau ekspedisi.',
                'badge_class' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                'icon' => 'x-circle',
            ],
            'courierNotFound' => [
                'label' => 'Kurir Tidak Ditemukan',
                'desc' => "Your shipment is canceled because there's no courier available at the moment.",
                'desc_id' => 'Pengiriman dibatalkan otomatis karena tidak ada kurir yang tersedia di area saat ini.',
                'badge_class' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                'icon' => 'user-x',
            ],
            'courier_not_found' => [
                'label' => 'Kurir Tidak Ditemukan',
                'desc' => "Your shipment is canceled because there's no courier available at the moment.",
                'desc_id' => 'Pengiriman dibatalkan otomatis karena tidak ada kurir yang tersedia di area saat ini.',
                'badge_class' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                'icon' => 'user-x',
            ],
            'returned' => [
                'label' => 'Berhasil Dikembalikan',
                'desc' => 'Order successfully returned.',
                'desc_id' => 'Pesanan pengiriman telah berhasil dikembalikan ke toko penjual.',
                'badge_class' => 'bg-[#8E8E93]/10 text-[#8E8E93] border-[#8E8E93]/20',
                'icon' => 'rotate-ccw',
            ],
            'cancelled' => [
                'label' => 'Pengiriman Dibatalkan',
                'desc' => 'Order is cancelled.',
                'desc_id' => 'Pengiriman pesanan telah dibatalkan.',
                'badge_class' => 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/20',
                'icon' => 'ban',
            ],
            'disposed' => [
                'label' => 'Paket Dimusnahkan',
                'desc' => 'Order successfully disposed.',
                'desc_id' => 'Paket telah dimusnahkan secara resmi sesuai regulasi pihak logistik.',
                'badge_class' => 'bg-[#8E8E93]/10 text-[#8E8E93] border-[#8E8E93]/20',
                'icon' => 'trash-2',
            ],
        ];

        $rawShippingStatus = $order->shipping_status;
        $shippingInfo = $rawShippingStatus ? ($biteshipTrackingMap[$rawShippingStatus] ?? null) : null;
        if (! $shippingInfo && $rawShippingStatus) {
            $camelStatus = \Illuminate\Support\Str::camel($rawShippingStatus);
            $shippingInfo = $biteshipTrackingMap[$camelStatus] ?? [
                'label' => ucwords(str_replace('_', ' ', $rawShippingStatus)),
                'desc' => 'Status pengiriman: ' . $rawShippingStatus,
                'desc_id' => 'Status pelacakan saat ini: ' . $rawShippingStatus,
                'badge_class' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                'icon' => 'truck',
            ];
        }

        $isDelivered = $order->status === 'delivered' || ($order->shipping_status ?? '') === 'delivered';
        $isShipped = in_array($order->status, ['shipped', 'delivered', 'completed'], true)
            || in_array($order->shipping_status ?? '', ['allocated', 'pickingUp', 'picked', 'inTransit', 'droppingOff', 'delivered'], true);

        // Timeline Step Calculation (1: Dipesan, 2: Bayar, 3: Proses, 4: Dikirim, 5: Tiba / Selesai)
        $stepIndex = match(true) {
            $order->status === 'completed' => 5,
            $isDelivered => 5,
            $isShipped || in_array($order->status, ['ready', 'packed', 'fulfilled'], true) => 4,
            in_array($order->status, ['paid', 'processing'], true) => 3,
            $order->status === 'proof_submitted' => 2,
            $order->status === 'pending_payment' => 1,
            default => 0,
        };
    @endphp

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
                
                {{-- 1. Semantic Status Badge (Sesuai Urutan Siklus Hidup Sistem) --}}
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
                @elseif($order->status === 'paid')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        <span>Pembayaran Dikonfirmasi</span>
                    </span>
                @elseif($order->status === 'processing')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                        <span>Sedang Diproses Toko</span>
                    </span>
                @elseif(in_array($order->status, ['ready', 'packed', 'fulfilled']))
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#5856D6]/10 text-[#5856D6] border border-[#5856D6]/20 flex items-center gap-1.5">
                        <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                        <span>{{ $order->fulfillment_type === 'pickup' ? 'Siap Diambil di Toko' : 'Pesanan Siap Dikirim' }}</span>
                    </span>
                @elseif($order->status === 'shipped')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1.5">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                        <span>Pesanan Sedang Dikirim</span>
                    </span>
                @elseif($order->status === 'delivered')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 flex items-center gap-1.5">
                        <i data-lucide="home" class="w-3.5 h-3.5"></i>
                        <span>Pesanan Telah Tiba di Tujuan</span>
                    </span>
                @elseif($order->status === 'completed')
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Pesanan Selesai</span>
                    </span>
                @elseif(in_array($order->status, ['cancelled', 'rejected']))
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20 flex items-center gap-1.5">
                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        <span>{{ $order->status === 'rejected' ? 'Pesanan Ditolak' : 'Pesanan Dibatalkan' }}</span>
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70 border border-black/10">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                @endif

                {{-- 2. Biteship Tracking Live Status Badge (Jika Ada Pengiriman Aktif) --}}
                @if($shippingInfo && !in_array($order->status, ['completed', 'cancelled', 'rejected']))
                    <span class="px-3 py-1 rounded-full text-[12px] font-bold {{ $shippingInfo['badge_class'] }} border flex items-center gap-1.5 shadow-2xs">
                        <i data-lucide="{{ $shippingInfo['icon'] }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $shippingInfo['label'] }}</span>
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

    {{-- Order Progress Timeline (Apple HIG & Biteship Aware) --}}
    @if($stepIndex > 0)
    <div class="bento-card p-5 sm:p-6 overflow-hidden">
        <div class="grid grid-cols-5 gap-2 text-center relative">
            
            {{-- Step 1: Dipesan --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 1 ? 'bg-[#34C759] text-white shadow-xs' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40' }}">
                    @if($stepIndex > 1)
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        1
                    @endif
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 1 ? 'text-black dark:text-white font-bold' : 'text-black/40 dark:text-white/40' }}">
                    Dipesan
                </span>
            </div>

            {{-- Step 2: Pembayaran --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 2 ? 'bg-[#34C759] text-white shadow-xs' : ($stepIndex === 1 ? 'bg-[#FF9500] text-white animate-pulse' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40') }}">
                    @if($stepIndex > 2)
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        2
                    @endif
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 2 ? 'text-black dark:text-white font-bold' : ($stepIndex === 1 ? 'text-[#FF9500] font-bold' : 'text-black/40 dark:text-white/40') }}">
                    Pembayaran
                </span>
            </div>

            {{-- Step 3: Diproses Toko --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 3 ? 'bg-[#34C759] text-white shadow-xs' : ($stepIndex === 2 ? 'bg-[#007AFF] text-white animate-pulse' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40') }}">
                    @if($stepIndex > 3)
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        3
                    @endif
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 3 ? 'text-black dark:text-white font-bold' : 'text-black/40 dark:text-white/40' }}">
                    Diproses
                </span>
            </div>

            {{-- Step 4: Pengiriman / Siap Diambil --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 4 ? 'bg-[#34C759] text-white shadow-xs' : ($stepIndex === 3 ? 'bg-[#007AFF] text-white animate-pulse' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40') }}">
                    @if($stepIndex >= 5)
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        4
                    @endif
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 4 ? 'text-black dark:text-white font-bold' : 'text-black/40 dark:text-white/40' }}">
                    {{ $order->fulfillment_type === 'pickup' ? 'Siap Diambil' : ($order->shipping_status === 'droppingOff' ? 'Diantar Kurir' : 'Dikirim') }}
                </span>
            </div>

            {{-- Step 5: Telah Tiba / Selesai --}}
            <div class="space-y-1.5">
                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center font-bold text-[12px] {{ $stepIndex >= 5 ? 'bg-[#34C759] text-white shadow-xs' : ($stepIndex === 4 ? 'bg-[#007AFF] text-white animate-pulse' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40') }}">
                    @if($stepIndex >= 5)
                        <i data-lucide="check" class="w-4 h-4"></i>
                    @else
                        5
                    @endif
                </div>
                <span class="text-[11px] sm:text-[12px] font-semibold block leading-tight {{ $stepIndex >= 5 ? 'text-[#34C759] font-bold' : 'text-black/40 dark:text-white/40' }}">
                    {{ $isDelivered && $order->status !== 'completed' ? 'Telah Tiba' : 'Selesai' }}
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
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                    <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">
                        Informasi Pengiriman &amp; Logistik
                    </h2>
                    @if($shippingInfo)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $shippingInfo['badge_class'] }} border flex items-center gap-1 shadow-2xs">
                            <i data-lucide="{{ $shippingInfo['icon'] }}" class="w-3 h-3"></i>
                            <span>{{ $shippingInfo['label'] }}</span>
                        </span>
                    @elseif($order->shipping_status)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            {{ str_replace('_', ' ', $order->shipping_status) }}
                        </span>
                    @endif
                </div>

                {{-- Status Pelacakan Biteship Detail --}}
                @if($shippingInfo)
                    <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full {{ $shippingInfo['badge_class'] }} flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <i data-lucide="{{ $shippingInfo['icon'] }}" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[13px] font-bold text-black dark:text-white">{{ $shippingInfo['label'] }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60 uppercase tracking-wider">Ekspedisi Biteship</span>
                            </div>
                            <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed">{{ $shippingInfo['desc_id'] }}</p>
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Penerima</span>
                        <span class="font-semibold text-black dark:text-white block mt-0.5">{{ $order->customer_name }}</span>
                        <span class="text-black/60 dark:text-white/60 tabular-nums">{{ $order->customer_phone }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Metode Pengiriman</span>
                        <span class="font-semibold text-black dark:text-white block mt-0.5">
                            @if($order->fulfillment_type === 'pickup')
                                Ambil Sendiri di Toko (Pickup)
                            @else
                                {{ $order->shipping_courier_name ?: 'Kurir Pengiriman' }}
                                @if($order->shipping_courier_service)
                                    <span class="text-black/50 dark:text-white/50 text-[12px]">({{ $order->shipping_courier_service }})</span>
                                @endif
                            @endif
                        </span>
                        @if($order->scheduled_date)
                            <span class="text-[12px] text-[#007AFF] font-medium block mt-1">
                                Jadwal: {{ date('d M Y', strtotime($order->scheduled_date)) }} {{ $order->scheduled_time_slot ? '(' . $order->scheduled_time_slot . ')' : '' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Nomor Resi AWB (Jika Ada) --}}
                @if($order->shipping_waybill_id)
                    <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 flex flex-wrap items-center justify-between gap-2.5" x-data="{ copied: false }">
                        <div>
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold uppercase tracking-wider">Nomor Resi / AWB Kurir</span>
                            <span class="font-mono text-[14px] font-bold text-black dark:text-white tracking-wide select-all">{{ $order->shipping_waybill_id }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="navigator.clipboard.writeText('{{ $order->shipping_waybill_id }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-2.5 py-1 rounded-[8px] bg-[#007AFF] text-white text-[11px] font-bold transition active:scale-95 cursor-pointer flex items-center gap-1 shadow-xs">
                                <i data-lucide="copy" class="w-3 h-3"></i>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Resi'"></span>
                            </button>
                            @if($order->shipping_tracking_url)
                                <a href="{{ $order->shipping_tracking_url }}" target="_blank"
                                    class="px-2.5 py-1 rounded-[8px] bg-black/10 dark:bg-white/10 hover:bg-black/15 text-black dark:text-white text-[11px] font-bold transition flex items-center gap-1">
                                    <span>Lacak di Ekspedisi</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                @if($order->shipping_address)
                    <div class="pt-2 border-t border-black/5 dark:border-white/5">
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Alamat Tujuan</span>
                        <p class="text-[13px] text-black/75 dark:text-white/75 mt-0.5 leading-relaxed">{{ $order->shipping_address }}</p>
                    </div>
                @endif

                @if($order->notes)
                    <div class="pt-1">
                        <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block">Catatan Pesanan</span>
                        <p class="text-[12.5px] text-black/65 dark:text-white/65 mt-0.5 italic">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Verified Product Reviews (Hanya Tampil Jika Order Completed) --}}
            @if($order->status === 'completed')
                <div class="bento-card p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2">
                            <i data-lucide="star" class="w-4 h-4 text-[#FF9500] fill-[#FF9500]"></i>
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">
                                Ulasan Produk Pembeli
                            </h2>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3 h-3"></i>
                            <span>Verified Purchase</span>
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach($order->items as $idx => $item)
                            @php
                                $existingReview = $order->reviews->firstWhere('product_id', $item->product_id);
                            @endphp

                            <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3"
                                x-data="{
                                    rating: {{ $existingReview ? $existingReview->rating : 5 }},
                                    reviewText: '{{ $existingReview ? addslashes($existingReview->review_text ?? '') : '' }}',
                                    isSubmitted: {{ $existingReview ? 'true' : 'false' }},
                                    isEditing: false
                                }">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-[10px] bg-black/5 dark:bg-white/10 overflow-hidden flex items-center justify-center shrink-0">
                                            @if($item->product?->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="package" class="w-5 h-5 text-black/30 dark:text-white/30"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-bold text-[13.5px] text-black dark:text-white block">{{ $item->product_name }}</span>
                                            <span class="text-[11.5px] text-black/50 dark:text-white/50">{{ (float) $item->quantity }}x @ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <template x-if="isSubmitted && !isEditing">
                                        <button type="button" @click="isEditing = true" class="text-xs text-[#007AFF] font-bold hover:underline cursor-pointer">
                                            Ubah Ulasan
                                        </button>
                                    </template>
                                </div>

                                {{-- Display existing review --}}
                                <template x-if="isSubmitted && !isEditing">
                                    <div class="pt-2 border-t border-black/5 dark:border-white/5 space-y-1.5">
                                        <div class="flex items-center gap-1 text-[#FF9500]">
                                            @for($s = 1; $s <= 5; $s++)
                                                <i data-lucide="star" class="w-3.5 h-3.5 {{ ($existingReview?->rating ?? 5) >= $s ? 'fill-[#FF9500]' : 'text-gray-300' }}"></i>
                                            @endfor
                                            <span class="text-xs font-bold text-black/60 dark:text-white/60 ml-1.5">{{ $existingReview?->rating ?? 5 }}/5</span>
                                        </div>
                                        @if($existingReview?->review_text)
                                            <p class="text-[12.5px] text-black/80 dark:text-white/80 leading-relaxed italic">
                                                &ldquo;{{ $existingReview->review_text }}&rdquo;
                                            </p>
                                        @endif
                                    </div>
                                </template>

                                {{-- Review Form --}}
                                <template x-if="!isSubmitted || isEditing">
                                    <form method="POST" action="{{ route('customer.orders.review', $order->id) }}" class="pt-2 border-t border-black/5 dark:border-white/5 space-y-3">
                                        @csrf
                                        <input type="hidden" name="reviews[0][product_id]" value="{{ $item->product_id }}">
                                        <input type="hidden" name="reviews[0][order_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="reviews[0][rating]" :value="rating">

                                        <div>
                                            <span class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block mb-1.5">Beri Bintang</span>
                                            <div class="flex items-center gap-1.5">
                                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                                    <button type="button" @click="rating = star" class="p-1 transition hover:scale-110 active:scale-95 focus:outline-none cursor-pointer">
                                                        <svg class="w-6 h-6" :class="star <= rating ? 'text-[#FF9500] fill-[#FF9500]' : 'text-black/20 dark:text-white/20'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                        </svg>
                                                    </button>
                                                </template>
                                                <span class="text-xs font-bold text-black/60 dark:text-white/60 ml-2" x-text="rating + ' dari 5 Bintang'"></span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-[11px] uppercase tracking-wider text-black/45 dark:text-white/45 font-bold block mb-1">
                                                Komentar Ulasan
                                            </label>
                                            <textarea name="reviews[0][review_text]" x-model="reviewText" rows="2" placeholder="Bagikan kepuasan Anda mengenai produk ini..."
                                                class="w-full p-2.5 rounded-[10px] bg-white dark:bg-black/30 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none"></textarea>
                                        </div>

                                        <div class="flex items-center justify-end gap-2">
                                            <template x-if="isEditing">
                                                <button type="button" @click="isEditing = false" class="px-3 py-1.5 rounded-[8px] bg-black/5 dark:bg-white/10 text-xs font-semibold text-black dark:text-white cursor-pointer">
                                                    Batal
                                                </button>
                                            </template>
                                            <button type="submit" class="px-4 py-1.5 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                                                Kirim Ulasan
                                            </button>
                                        </div>
                                    </form>
                                </template>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

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

                        {{-- Tombol Batalkan Pesanan (Khusus Belum Bayar) --}}
                        @if ($order->status === 'pending_payment')
                            <form method="POST" action="{{ route('customer.orders.cancel', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok yang dicadangkan akan segera dilepaskan.')" class="pt-2">
                                @csrf
                                <input type="hidden" name="cancel_reason" value="Dibatalkan oleh pelanggan di portal">
                                <button type="submit" class="w-full h-10 rounded-[12px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[#FF3B30] text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                    <span>Batalkan Pesanan Ini</span>
                                </button>
                            </form>
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
                                        window.location.href = "{{ route('customer.orders.detail', $order->id) }}";
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

                    {{-- Tombol Konfirmasi Terima Pesanan --}}
                    @if(in_array($order->status, ['shipped', 'delivered', 'ready', 'fulfilled', 'processing']) || ($order->shipping_status ?? '') === 'delivered')
                        <div class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 space-y-2 text-left">
                            <div class="flex items-center gap-2 text-[#34C759]">
                                <i data-lucide="package-check" class="w-4 h-4"></i>
                                <span class="text-xs font-bold">Konfirmasi Penerimaan</span>
                            </div>
                            <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                Sudah menerima barang/layanan dengan lengkap dan sesuai? Klik di bawah untuk menyelesaikan pesanan.
                            </p>
                            <form method="POST" action="{{ route('customer.orders.complete', $order->id) }}" onsubmit="return confirm('Konfirmasi bahwa pesanan telah diterima dengan baik?')" class="pt-1">
                                @csrf
                                <button type="submit" class="w-full h-10 rounded-[10px] bg-[#34C759] hover:bg-[#30D158] text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Konfirmasi Pesanan Diterima</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
