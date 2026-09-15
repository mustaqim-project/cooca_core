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
            <span class="text-[12px] font-mono text-black/50 dark:text-white/50">#{{ $order->order_number }}</span>
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

        {{-- PAYMENT INSTRUCTIONS (IF UNPAID) --}}
        @if ($order->canSubmitProof())
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
                            <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Jadwal
                                Pengiriman Bertahap (PO Batches)</h3>
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
        Didukung oleh platform <strong>COOCA UMKM</strong>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
