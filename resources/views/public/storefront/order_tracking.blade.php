<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pesanan #{{ $order->order_number }} - {{ $business->name }}</title>
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
            <a href="{{ url("/b/{$business->slug}") }}" class="flex items-center gap-2 hover:opacity-80 transition">
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

        {{-- PAYMENT INSTRUCTIONS (IF UNPAID) --}}
        {{-- TRIPAY AUTOMATIC PAYMENT CARD (QRIS & VIRTUAL ACCOUNT) --}}
        @if ($order->isTripay() && !$order->isPaid())
            <div class="p-4 sm:p-5 rounded-[20px] bg-gradient-to-r from-[#007AFF]/10 via-[#5856D6]/10 to-[#007AFF]/5 border border-[#007AFF]/30 flex items-start gap-3.5 shadow-2xs">
                <div class="p-2 rounded-xl bg-[#007AFF]/15 text-[#007AFF] shrink-0 mt-0.5">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
                <div class="space-y-0.5">
                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Pembayaran Otomatis Terintegrasi</h3>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        Sistem memverifikasi pembayaran Anda secara langsung tanpa perlu konfirmasi manual atau upload foto struk. Halaman ini akan otomatis diperbarui saat Anda selesai membayar.
                    </p>
                </div>
            </div>

            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-6"
                x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] block">Instruksi Pembayaran Gateway</span>
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight mt-0.5">
                            {{ $order->payment_channel === 'QRIS' ? 'Pindai QRIS Dinamis' : ($order->payment_channel ?? 'Virtual Account') }}
                        </h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11.5px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Verifikasi Real-Time
                    </span>
                </div>

                {{-- Total Tagihan Box --}}
                <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-[12.5px] text-black/60 dark:text-white/60 block">Total Pembayaran:</span>
                        <span class="text-[24px] font-extrabold text-[#007AFF] font-mono tracking-tight">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    @if ($order->gateway_expired_at)
                        <div class="text-left sm:text-right">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 block">Batas Waktu Bayar:</span>
                            <span class="text-[13px] font-semibold text-amber-600 dark:text-amber-400">
                                {{ $order->gateway_expired_at->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                        </div>
                    @endif
                </div>

                {{-- QRIS Section --}}
                @if (strtoupper($order->payment_channel ?? '') === 'QRIS' || !empty($order->gateway_qr_url))
                    <div class="flex flex-col items-center justify-center p-6 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-4 text-center">
                        <div class="p-3 bg-white rounded-2xl border border-black/10 shadow-sm inline-block">
                            @if ($order->gateway_qr_url)
                                <img src="{{ $order->gateway_qr_url }}" alt="QRIS Dinamis" class="w-56 h-56 object-contain rounded-xl">
                            @else
                                <div class="w-56 h-56 flex flex-col items-center justify-center text-center p-4">
                                    <i data-lucide="qr-code" class="w-16 h-16 text-black/40 mb-2"></i>
                                    <span class="text-[12px] text-black/60 font-medium">Memuat kode QRIS...</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1 max-w-md">
                            <h4 class="text-[14px] font-bold text-black dark:text-white">Bisa Pindai dari Semua Aplikasi Pembayaran</h4>
                            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                Buka aplikasi m-Banking (BCA, Mandiri, BRI, BNI) atau e-Wallet (GoPay, OVO, Dana, ShopeePay), pilih fitur scan QRIS, lalu arahkan kamera ke kode di atas.
                            </p>
                        </div>

                        @if ($order->gateway_pay_url)
                            <div class="pt-2">
                                <a href="{{ $order->gateway_pay_url }}" target="_blank"
                                    class="h-10 px-5 rounded-full bg-brand-primary text-white text-[12.5px] font-semibold transition hover:opacity-90 inline-flex items-center gap-1.5 shadow-xs">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                    <span>Buka Halaman Pembayaran TriPay</span>
                                </a>
                            </div>
                        @endif
                    </div>

                {{-- Virtual Account Section --}}
                @elseif ($order->gateway_pay_code)
                    <div class="p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[12.5px] font-semibold text-black/70 dark:text-white/70">Nomor Virtual Account</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                {{ $order->payment_channel }}
                            </span>
                        </div>

                        <div class="p-4 rounded-[16px] bg-white dark:bg-[#111112] border border-black/10 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                            <div class="min-w-0 flex-1">
                                <span class="font-mono text-2xl sm:text-3xl font-bold tracking-wider text-black dark:text-white tabular-nums block break-all">
                                    {{ $order->gateway_pay_code }}
                                </span>
                                <span class="text-[11.5px] text-black/50 dark:text-white/50 block mt-1">
                                    Atas Nama: <strong>{{ $business->name }}</strong>
                                </span>
                            </div>

                            <button type="button"
                                @click="navigator.clipboard.writeText('{{ $order->gateway_pay_code }}'); copied = true; setTimeout(() => copied = false, 2500);"
                                class="h-11 px-5 rounded-[12px] bg-brand-primary hover:opacity-90 active:scale-[0.98] text-white font-bold text-[13px] transition flex items-center justify-center gap-2 shrink-0 cursor-pointer shadow-xs">
                                <i :data-lucide="copied ? 'check' : 'copy'" class="w-4 h-4"></i>
                                <span x-text="copied ? 'Berhasil Disalin!' : 'Salin Nomor VA'"></span>
                            </button>
                        </div>

                        <div class="text-[12px] text-black/60 dark:text-white/60 space-y-1.5 pt-1">
                            <div class="flex items-center gap-1.5 font-semibold text-black/80 dark:text-white/80">
                                <i data-lucide="info" class="w-4 h-4 text-brand-primary"></i>
                                <span>Petunjuk Pembayaran:</span>
                            </div>
                            <p>1. Buka m-Banking atau ATM bank pilihan Anda.</p>
                            <p>2. Pilih menu <strong>Transfer &gt; Virtual Account</strong>.</p>
                            <p>3. Masukkan nomor VA di atas dan pastikan nominal tagihan sesuai.</p>
                            <p>4. Konfirmasi transaksi dan status pesanan ini otomatis berubah menjadi lunas.</p>
                        </div>
                    </div>
                @endif

                {{-- Live Detection Notice --}}
                <div class="p-3.5 rounded-[16px] bg-emerald-500/5 border border-emerald-500/15 flex items-center gap-3 text-[12.5px] text-emerald-800 dark:text-emerald-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping shrink-0"></span>
                    <span>Sistem aktif memantau pembayaran Anda. Jangan tutup halaman ini jika Anda ingin melihat status berubah secara langsung.</span>
                </div>
            </div>
        @endif

        {{-- MANUAL PAYMENT INSTRUCTIONS & PROOF UPLOAD (IF MANUAL & UNPAID) --}}
        @if ($order->isManualPayment() && $order->canSubmitProof())
            <div class="p-4 sm:p-5 rounded-[20px] bg-gradient-to-r from-[#007AFF]/10 via-[#5856D6]/10 to-[#007AFF]/5 border border-[#007AFF]/30 flex items-start gap-3.5 shadow-2xs">
                <div class="p-2 rounded-xl bg-[#007AFF]/15 text-[#007AFF] shrink-0 mt-0.5">
                    <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                </div>
                <div class="space-y-0.5">
                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Selesaikan Pembayaran &amp; Unggah Bukti Transfer</h3>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        Pesanan Anda telah tercatat dan kuota batch pengiriman berhasil diamankan. Silakan transfer sesuai nominal di bawah, lalu langsung unggah bukti transfer agar pesanan segera diverifikasi oleh tim restoran.
                    </p>
                </div>
            </div>

            <div
                class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] space-y-6">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] block">Instruksi
                        Pembayaran</span>
                    <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight mt-0.5">
                        Transfer Manual / QRIS Toko</h2>
                </div>

                <div
                    class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                    <div class="flex items-baseline justify-between">
                        <span class="text-[13px] text-black/60 dark:text-white/60">Total yang Harus Ditransfer:</span>
                        <span class="text-[22px] font-extrabold text-[#007AFF] font-mono tracking-tight">Rp
                            {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if ($order->paymentMethod)
                        <div class="pt-3 border-t border-black/5 dark:border-white/5 space-y-2">
                            <div class="flex justify-between text-[13px]">
                                <span class="text-black/60 dark:text-white/60">Tujuan Pembayaran:</span>
                                <span
                                    class="font-bold text-black dark:text-white">{{ $order->paymentMethod->bank_name }}</span>
                            </div>

                            @if ($order->paymentMethod->account_number)
                                <div class="flex justify-between items-center text-[13px]">
                                    <span class="text-black/60 dark:text-white/60">Nomor Rekening:</span>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-mono font-bold text-black dark:text-white text-[15px]">{{ $order->paymentMethod->account_number }}</span>
                                        <button type="button"
                                            onclick="navigator.clipboard.writeText('{{ $order->paymentMethod->account_number }}'); alert('Nomor rekening berhasil disalin!');"
                                            class="p-1 rounded bg-black/5 dark:bg-white/10 text-[11px] hover:bg-black/10 transition">Salin</button>
                                    </div>
                                </div>
                                <div class="flex justify-between text-[13px]">
                                    <span class="text-black/60 dark:text-white/60">Atas Nama (A/N):</span>
                                    <span
                                        class="font-medium text-black dark:text-white">{{ $order->paymentMethod->account_holder }}</span>
                                </div>
                            @endif

                            @if ($order->paymentMethod->qris_image_path)
                                <div class="pt-2 text-center">
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50 block mb-2">Pindai QRIS
                                        Toko</span>
                                    <img src="{{ asset('storage/' . $order->paymentMethod->qris_image_path) }}"
                                        alt="QRIS {{ $business->name }}"
                                        class="w-48 h-48 mx-auto rounded-xl object-contain border border-black/10 dark:border-white/10 bg-white p-2">
                                </div>
                            @endif

                            @if ($order->paymentMethod->instructions)
                                <p class="text-[12px] text-black/55 dark:text-white/55 italic pt-1">
                                    {{ $order->paymentMethod->instructions }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- UPLOAD PROOF FORM --}}
                <form action="{{ url("/b/{$business->slug}/order/{$order->tracking_token}/proof") }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4" x-data="{ previewUrl: null }">
                    @csrf
                    <div>
                        <label class="block text-[13px] font-semibold text-black dark:text-white mb-2">Unggah Foto Bukti
                            Transfer</label>
                        <div
                            class="relative border-2 border-dashed border-black/15 dark:border-white/15 rounded-[18px] p-6 text-center hover:border-[#007AFF] transition bg-black/[0.01] dark:bg-white/[0.02]">
                            <input type="file" name="payment_proof"
                                accept="image/jpeg,image/png,image/webp,application/pdf" required
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                @change="const file = $event.target.files[0]; if(file && file.type.startsWith('image/')) { previewUrl = URL.createObjectURL(file) } else { previewUrl = null }">

                            <template x-if="previewUrl">
                                <div class="space-y-2">
                                    <img :src="previewUrl"
                                        class="w-32 h-32 object-cover mx-auto rounded-lg border shadow-sm">
                                    <span class="text-[12px] text-[#007AFF] font-medium block">Klik untuk ganti
                                        foto</span>
                                </div>
                            </template>

                            <div x-show="!previewUrl" class="space-y-2">
                                <i data-lucide="upload-cloud"
                                    class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                                <div class="text-[13px] text-black/70 dark:text-white/70">
                                    <span class="font-semibold text-[#007AFF]">Pilih Berkas</span> atau seret ke sini
                                </div>
                                <p class="text-[11.5px] text-black/40 dark:text-white/40">Format JPG, PNG, WEBP, atau
                                    PDF (Maks. 5 MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Bank
                                Pengirim (Opsional)</label>
                            <input type="text" name="sender_bank" placeholder="Contoh: BCA / Mandiri / GoPay"
                                class="w-full h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1">Nama
                                Pemilik Rekening Pengirim (Opsional)</label>
                            <input type="text" name="sender_account_name" placeholder="Contoh: Budi Santoso"
                                class="w-full h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full h-12 rounded-full bg-[#007AFF] hover:bg-[#007AFF]/90 text-white font-semibold text-[14px] shadow-[0_4px_16px_rgba(0,122,255,0.25)] active:scale-[0.98] transition flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Bukti Pembayaran</span>
                    </button>
                </form>
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
                        ({{ $order->fulfillment_type === 'pickup' ? 'Ambil Sendiri' : 'Pengiriman' }})</span>
                    <span class="tabular-nums">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
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
                <div class="sm:col-span-2">
                    <span class="text-[11.5px] text-black/45 dark:text-white/45 block font-medium">Alamat /
                        Catatan</span>
                    <span
                        class="text-black/80 dark:text-white/80 leading-relaxed">{{ $order->shipping_address ?: 'Ambil sendiri di outlet resmi toko.' }}</span>
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
