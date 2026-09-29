<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('public.partials.seo', [
        'siteName' => $business->name,
        'title' => 'Status Pesanan #' . $order->order_number . ' - ' . $business->name,
        'description' => 'Pantau rincian status pesanan #' . $order->order_number . ' di ' . $business->name . ' secara real-time. Informasi pembayaran, proses kemas, hingga resi pengiriman.',
        'url' => url("/{$business->slug}/order/{$order->tracking_token}"),
        'image' => $business->logo_url ?: asset('assets/seo/cooca-og-default.jpg'),
        'imageAlt' => 'Status Pesanan #' . $order->order_number . ' - ' . $business->name,
        'robots' => 'noindex, follow',
    ])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"Inter"', 'system-ui',
                            'sans-serif'
                        ],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#007AFF',
                            primary: '#007AFF',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] min-h-screen antialiased flex flex-col justify-between">

    {{-- TOP NAVBAR --}}
    <header
        class="sticky top-0 z-40 bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-2xl border-b border-black/[0.06] dark:border-white/[0.08]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <a href="{{ $business->public_url }}" class="flex items-center gap-2 hover:opacity-80 transition">
                <i data-lucide="chevron-left" class="w-5 h-5 text-black/60 dark:text-white/60"></i>
                <span class="font-semibold text-[14.5px] text-black dark:text-white">{{ $business->name }}</span>
            </a>
            <div class="flex items-center gap-3">
                @if (auth('customer')->check())
                    <a href="{{ route('customer.orders') }}"
                        class="px-3 py-1 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/80 dark:text-white/80 text-[12px] font-semibold transition flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Pesanan Saya</span>
                    </a>
                @endif
                <span class="text-[12px] font-mono text-black/50 dark:text-white/50">#{{ $order->order_number }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto w-full px-4 sm:px-6 py-8 space-y-6">

        {{-- FLASH MESSAGES --}}
        @if (session('success'))
            <div
                class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] flex items-center gap-3 text-[13.5px] font-medium shadow-sm">
                <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] flex items-center gap-3 text-[13.5px] font-medium shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ORDER HEADER & STATUS BANNER --}}
        <div
            class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span
                        class="text-[11.5px] font-semibold tracking-wider uppercase text-black/45 dark:text-white/45 block">Status
                        Pesanan</span>
                    <h1 class="text-2xl sm:text-[26px] font-bold tracking-tight text-black dark:text-white mt-0.5">
                        @if ($order->status === 'pending_payment')
                            Menunggu Pembayaran
                        @elseif($order->status === 'proof_submitted')
                            Verifikasi Pembayaran
                        @elseif($order->status === 'payment_rejected')
                            Bukti Transfer Ditolak
                        @elseif($order->status === 'paid' || $order->status === 'processing')
                            Sedang Diproses Toko
                        @elseif($order->status === 'ready')
                            Pesanan Siap
                        @elseif($order->status === 'fulfilled')
                            Dalam Pengantaran
                        @elseif($order->status === 'completed')
                            Pesanan Selesai
                        @elseif($order->status === 'cancelled')
                            Pesanan Dibatalkan
                        @elseif($order->status === 'expired')
                            Waktu Pembayaran Habis
                        @endif
                    </h1>
                </div>

                <div>
                    @if ($order->status === 'pending_payment')
                        <span
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Transfer
                        </span>
                    @elseif($order->status === 'proof_submitted')
                        <span
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-pulse"></span>
                            Sedang Diverifikasi
                        </span>
                    @elseif($order->isPaid())
                        <span
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Sudah Lunas
                        </span>
                    @elseif($order->status === 'payment_rejected')
                        <span
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            Bukti Ditolak
                        </span>
                    @endif
                </div>
            </div>

            @if ($order->status === 'payment_rejected' && $order->rejection_reason)
                <div
                    class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] text-[13px] font-medium border border-[#FF3B30]/20">
                    <strong>Alasan Penolakan:</strong> {{ $order->rejection_reason }}
                </div>
            @endif

            @if ($order->status === 'pending_payment' && $order->reserved_until)
                <div
                    class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[12.5px] text-black/60 dark:text-white/60">
                    <span>Batas Waktu Pembayaran:</span>
                    <span
                        class="font-semibold text-black dark:text-white">{{ $order->reserved_until->format('d M Y, H:i') }}
                        WIB</span>
                </div>
            @endif
        </div>

        {{-- GROUP ORDER / SPLIT BILL BANNER (IF APPLICABLE) --}}
        @if ($order->groupOrder)
            @php
                $group = $order->groupOrder;
                $splitSummary = $group->getSplitBillSummary();
                $waBillText = "Halo semuanya! Berikut rincian patungan pesanan {$business->name} ({$group->title}) - Pesanan #{$order->order_number}:\n\n";
                foreach ($splitSummary as $s) {
                    $waBillText .= "- {$s['member_name']}: Rp " . number_format($s['subtotal'], 0, ',', '.') . " (" . $s['items']->map(fn($it) => (float)$it->quantity . 'x ' . ($it->product?->name ?? 'Menu'))->join(', ') . ")\n";
                }
                $waBillText .= "\nTotal Tagihan: Rp " . number_format($order->total_amount, 0, ',', '.') . "\nSilakan transfer ke Host (" . ($group->host?->name ?? 'Host') . "). Terima kasih!";
            @endphp
            <div x-data="{ splitModalOpen: false }" class="p-5 sm:p-6 rounded-[24px] bg-[#AF52DE]/10 border border-[#AF52DE]/25 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#AF52DE] text-white flex items-center justify-center shrink-0 shadow-sm">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">
                                    Pesanan Bersama: {{ $group->title }}
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#AF52DE]/20 text-[#AF52DE]">
                                    Pesan Bareng
                                </span>
                            </div>
                            <p class="text-[12px] text-black/60 dark:text-white/60 mt-0.5">
                                Host: <strong class="text-black dark:text-white">{{ $group->host?->name ?? $order->customer_name }}</strong> &bull; Total {{ count($splitSummary) }} Rekan Patungan
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="splitModalOpen = true"
                        class="h-9 px-4 rounded-full bg-[#AF52DE] hover:bg-[#AF52DE]/90 text-white font-semibold text-[12.5px] transition active:scale-[0.98] shadow-sm flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span>Rincian Patungan (Split Bill)</span>
                    </button>
                </div>

                {{-- SPLIT BILL MODAL SHEET --}}
                <div x-show="splitModalOpen" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                    x-transition.opacity>
                    <div @click.outside="splitModalOpen = false"
                        class="w-full max-w-full sm:max-w-xl md:max-w-2xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-5 sm:p-7 space-y-4 max-h-[88vh] overflow-y-auto">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">Rincian Patungan (Split Bill)</h3>
                            </div>
                            <button type="button" @click="splitModalOpen = false"
                                class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center text-black/60 dark:text-white/60">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="space-y-3 divide-y divide-black/5 dark:divide-white/5">
                            @foreach ($splitSummary as $split)
                                <div class="pt-3 first:pt-0 space-y-1">
                                    <div class="flex items-center justify-between text-[13.5px]">
                                        <span class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                            <i data-lucide="user" class="w-3.5 h-3.5 text-[#AF52DE]"></i>
                                            <span>{{ $split['member_name'] }}</span>
                                        </span>
                                        <span class="font-bold text-[#AF52DE] tabular-nums">
                                            Rp {{ number_format($split['subtotal'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="text-[12px] text-black/55 dark:text-white/55 pl-5 space-y-0.5">
                                        @foreach ($split['items'] as $item)
                                            <div>&bull; {{ (float) $item->quantity }}x {{ $item->product?->name ?? 'Produk' }} @if($item->notes) <span class="italic text-black/40">({{ $item->notes }})</span> @endif</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t border-black/10 dark:border-white/10 flex items-center justify-between text-[14px] font-bold">
                            <span class="text-black dark:text-white">Total Tagihan Bersama:</span>
                            <span class="text-[#007AFF] tabular-nums text-[16px]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row gap-2">
                            <button type="button"
                                onclick="navigator.clipboard.writeText({{ json_encode($waBillText) }}).then(() => alert('Rincian tagihan berhasil disalin! Silakan tempel di WhatsApp grup kantor.'));"
                                class="flex-1 h-10 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black dark:text-white text-[12.5px] font-semibold transition flex items-center justify-center gap-1.5">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                                <span>Salin Rincian</span>
                            </button>
                            <a href="https://api.whatsapp.com/send?text={{ rawurlencode($waBillText) }}" target="_blank"
                                class="flex-1 h-10 rounded-full bg-[#25D366] hover:bg-[#25D366]/90 text-white text-[12.5px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span>Kirim ke WA Rekan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- UNIFIED DYNAMIC QRIS & GATEWAY PAYMENT SECTION --}}
        @if (!$order->isPaid())
            @php
                $qrData = $order->gateway_qr_string ?: ($order->gateway_qr_url ?: ($order->gateway_pay_url ?: url("/{$business->slug}/order/{$order->tracking_token}")));
                $qrImageUrl = $order->gateway_qr_url ?: ('https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=8&data=' . urlencode($qrData));
                
                $expiryTimestamp = $order->gateway_expired_at?->timestamp ?: ($order->reserved_until?->timestamp ?: ($order->created_at->addMinutes(15)->timestamp));
                $secondsRemaining = max(0, $expiryTimestamp - time());
            @endphp

            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-lg space-y-6 text-center"
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
                        <h2 class="font-bold text-[18px] text-black dark:text-white">QRIS Pembayaran Pesanan</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Scan dengan GoPay, OVO, Dana, BCA, Mandiri, atau Semua m-Banking</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                        Bebas Biaya Admin
                    </span>
                </div>

                <!-- Amount Box -->
                <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                    <span class="text-[13px] text-black/60 dark:text-white/60 font-medium">Total Tagihan Pembayaran:</span>
                    <span class="font-black text-[22px] sm:text-[24px] text-[#34C759] dark:text-[#30D158] font-mono tabular-nums">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </span>
                </div>

                <!-- QR Code Render Card -->
                <div class="p-5 rounded-[22px] bg-white border border-black/10 shadow-inner flex flex-col items-center justify-center relative mx-auto max-w-sm">
                    <img src="{{ $qrImageUrl }}" alt="QRIS Code Pesanan #{{ $order->order_number }}" class="w-56 h-56 sm:w-64 sm:h-64 object-contain rounded-xl">
                    <div class="mt-3 text-[11px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-ping"></span>
                        <span>Menunggu Pembayaran...</span>
                    </div>
                </div>

                <!-- Countdown Timer & Auto Polling Notice -->
                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-center gap-1.5 text-black/70 dark:text-white/70 font-semibold text-sm">
                        <span>Sisa Waktu Bayar:</span>
                        <span class="font-mono text-[#FF9500] font-extrabold tabular-nums" x-text="qrisCountdownFormatted">15:00</span>
                    </div>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50 max-w-md mx-auto">
                        Halaman ini aktif memantau sistem. Begitu pembayaran selesai, status pesanan otomatis berubah lunas tanpa perlu konfirmasi atau kirim struk.
                    </p>
                </div>

                <!-- Virtual Account Section (If VA is available) -->
                @if ($order->gateway_pay_code)
                    <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-2 text-left" x-data="{ copied: false }">
                        <div class="flex items-center justify-between">
                            <span class="text-[12px] font-semibold text-black/70 dark:text-white/70">Atau Bayar via Virtual Account ({{ $order->payment_channel }}):</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">VA Otomatis</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 p-3 bg-white dark:bg-black/20 rounded-[12px] border border-black/10">
                            <span class="font-mono text-lg font-bold text-black dark:text-white tracking-wider tabular-nums">
                                {{ $order->gateway_pay_code }}
                            </span>
                            <button type="button" @click="navigator.clipboard.writeText('{{ $order->gateway_pay_code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-3 py-1 rounded-lg bg-[#007AFF] text-white text-xs font-bold transition active:scale-95 cursor-pointer">
                                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-2.5 max-w-sm mx-auto">
                    <a href="{{ $qrImageUrl }}" target="_blank" download="qris-pesanan-{{ $order->order_number }}.png"
                        class="w-full sm:flex-1 h-11 rounded-[12px] bg-black/5 dark:bg-white/10 hover:bg-black/10 text-black dark:text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Unduh Gambar QR</span>
                    </a>
                    @if ($order->gateway_pay_url)
                        <a href="{{ $order->gateway_pay_url }}" target="_blank"
                            class="w-full sm:flex-1 h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>Buka Gateway</span>
                        </a>
                    @endif
                </div>

            </div>
        @endif


        {{-- MULTI-DROP BATCHES TRACKING --}}
        @if ($order->batches->isNotEmpty())
            <div
                class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-8 h-8 rounded-full bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Jadwal Pengiriman Bertahap (PO Batches)</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Progres pemenuhan setiap termin
                                pengiriman.</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#5856D6]/10 text-[#5856D6]">
                        {{ $order->delivered_batches_count }}/{{ $order->total_batches_count }} Terkirim
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach ($order->batches as $batch)
                        <div
                            class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-bold text-[13.5px] text-black dark:text-white">{{ $batch->batch_code }}</span>
                                    <span class="text-[12px] text-black/50 dark:text-white/50">&bull;
                                        {{ $batch->scheduled_date->translatedFormat('d F Y') }}</span>
                                    @if ($batch->scheduled_time_slot)
                                        <span
                                            class="text-[11px] px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ $batch->scheduled_time_slot }}</span>
                                    @endif
                                </div>
                                <div class="text-[12px] text-black/60 dark:text-white/60">
                                    Jumlah: <strong
                                        class="text-black dark:text-white">{{ number_format($batch->quantity, 0, ',', '.') }}
                                        unit</strong>
                                    @if ($batch->tracking_number)
                                        &bull; Resi: <span
                                            class="font-mono font-medium text-[#007AFF]">{{ $batch->tracking_number }}</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                @if ($batch->status === 'delivered')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        <span>Telah Diterima</span>
                                    </span>
                                @elseif($batch->status === 'shipped')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6]">
                                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                        <span>Dalam Pengiriman</span>
                                    </span>
                                @elseif($batch->status === 'in_preparation')
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                        <span>Sedang Disiapkan</span>
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
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ORDER DETAILS & ITEMS BENTO --}}
        <div
            class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-5">
            <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Rincian Pesanan</h3>

            <div class="divide-y divide-black/5 dark:divide-white/5">
                @foreach ($order->items as $item)
                    <div class="py-3.5 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h4 class="font-semibold text-[14px] text-black dark:text-white leading-snug">
                                {{ $item->product_name }}</h4>
                            <div class="text-[12px] text-black/55 dark:text-white/55">
                                {{ (float) $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </div>
                            @if ($item->notes)
                                <div class="text-[11.5px] text-black/45 dark:text-white/45 italic">Catatan:
                                    {{ $item->notes }}</div>
                            @endif
                        </div>
                        <div class="font-bold text-[14px] text-black dark:text-white tabular-nums">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4 border-t border-black/10 dark:border-white/10 space-y-2 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Subtotal Produk</span>
                    <span class="tabular-nums">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>Ongkos Kirim
                        ({{ $order->shipping_courier_name ?: ($order->fulfillment_type === 'pickup' ? 'Ambil Sendiri' : 'Pengiriman') }})</span>
                    <span class="tabular-nums">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @if ((float) ($order->biteship_service_fee ?? 0) > 0)
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span class="flex items-center gap-1.5">
                            <span>Biaya Layanan Pengiriman</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#007AFF]/10 text-[#007AFF]">Biteship</span>
                        </span>
                        <span class="tabular-nums font-semibold text-black dark:text-white">Rp {{ number_format((float) $order->biteship_service_fee, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div
                    class="pt-2 border-t border-black/10 dark:border-white/10 flex justify-between font-bold text-[16px] text-black dark:text-white">
                    <span>Total Tagihan</span>
                    <span class="text-[#007AFF] tabular-nums">Rp
                        {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- CUSTOMER & DELIVERY INFO --}}
        <div
            class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-4">
            <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Informasi Pengambilan /
                Pengiriman</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
                @if ($order->scheduled_date)
                    <div class="sm:col-span-2 p-3.5 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/20 space-y-1">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                            <span class="text-[11.5px] font-bold uppercase tracking-wider text-[#007AFF]">Jadwal Pre-Order / Tanggal Kirim</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-[14px] font-bold text-black dark:text-white">
                            <span>{{ $order->scheduled_date->translatedFormat('l, d F Y') }}</span>
                            @if ($order->scheduled_time_slot)
                                <span class="text-[11.5px] px-2.5 py-0.5 rounded-full bg-white dark:bg-[#1C1C1E] text-[#007AFF] font-semibold border border-[#007AFF]/20 shadow-xs">{{ $order->scheduled_time_slot }}</span>
                            @endif
                        </div>
                    </div>
                @endif
                <div>
                    <span class="text-[11.5px] text-black/45 dark:text-white/45 block font-medium">Nama Pemesan</span>
                    <span class="font-semibold text-black dark:text-white">{{ $order->customer_name }}</span>
                </div>
                <div>
                    <span class="text-[11.5px] text-black/45 dark:text-white/45 block font-medium">Nomor
                        WhatsApp</span>
                    <span class="font-semibold text-black dark:text-white">+{{ $order->customer_phone }}</span>
                </div>
                @if ($order->fulfillment_type !== 'pickup')
                    <div>
                        <span class="text-[11.5px] text-black/45 dark:text-white/45 block font-medium">Ekspedisi Pengiriman</span>
                        <span class="font-bold text-black dark:text-white">
                            {{ $order->shipping_courier_name ?: ($order->shipping_courier_code ? strtoupper($order->shipping_courier_code . ' ' . $order->shipping_courier_service) : 'Kurir Logistik') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[11.5px] text-black/45 dark:text-white/45 block font-medium">Nomor Resi / AWB</span>
                        @if ($order->shipping_waybill_id)
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono font-bold text-brand-primary text-[14px]">{{ $order->shipping_waybill_id }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $order->shipping_waybill_id }}'); alert('Nomor Resi disalin!')"
                                    class="text-[11px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 cursor-pointer">
                                    Salin
                                </button>
                                @if ($order->shipping_tracking_url)
                                    <a href="{{ $order->shipping_tracking_url }}" target="_blank" class="text-[11px] font-bold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                        <span>Lacak</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                @endif
                            </div>
                        @else
                            <span class="text-black/50 dark:text-white/50 italic text-[12px]">Menunggu penjemputan oleh kurir</span>
                        @endif
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <span class="text-[11.5px] text-black/45 dark:text-white/45 block font-medium">Alamat Pengiriman /
                        Catatan</span>
                    <p class="text-black/80 dark:text-white/80 leading-relaxed bg-black/5 dark:bg-white/5 p-3 rounded-[14px]">
                        {{ $order->shipping_address ?: 'Ambil sendiri di outlet resmi toko.' }}
                        @if ($order->destination_postal_code)
                            <span class="block mt-1 font-mono text-[11.5px] text-brand-primary font-semibold">Kode Pos: {{ $order->destination_postal_code }}</span>
                        @endif
                    </p>
                </div>
            </div>

            @if ($business->phone)
                <div class="pt-4 border-t border-black/5 dark:border-white/5">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $business->phone) }}?text=Halo%20{{ urlencode($business->name) }},%20saya%20ingin%20menanyakan%20status%20pesanan%20%23{{ $order->order_number }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#007AFF] hover:underline">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Butuh Bantuan? Chat Toko via WhatsApp</span>
                    </a>
                </div>
            @endif
        </div>

    </main>

    <footer class="py-6 text-center text-[12px] text-black/40 dark:text-white/40">
        Didukung oleh platform <strong>Cooca</strong>
    </footer>

    <script>
        lucide.createIcons();

        // Real-Time Live Status Polling (Auto-Detect TriPay Payment)
        document.addEventListener('DOMContentLoaded', () => {
            @if (!$order->isPaid())
                let isPolling = true;
                const pollStatus = () => {
                    if (!isPolling) return;
                    fetch('{{ route('public.storefront.order.status', [$business->slug, $order->tracking_token]) }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.is_paid) {
                                isPolling = false;
                                // Smooth reload to show updated lunas state
                                window.location.reload();
                            }
                        })
                        .catch(() => {});
                };

                // Poll every 4.5 seconds
                const timer = setInterval(pollStatus, 4500);

                // Stop polling if tab becomes inactive for 10 minutes to save resources
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        isPolling = false;
                    } else {
                        isPolling = true;
                        pollStatus();
                    }
                });
            @endif
        });
    </script>
</body>


</html>
