@extends('layouts.app', [
    'title' => 'Pengaturan Toko Online & Pembayaran - Cooca UMKM',
    'headerTitle' => 'Pengaturan Toko Online',
    'headerSubtitle' => 'Konfigurasi operasional storefront, visibilitas publik, dan rekening penerimaan transfer bank / QRIS',
])

@section('content')
    <div class="space-y-6 pb-16" x-data="{ addMethodModalOpen: false, methodType: 'bank_transfer' }">

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

        {{-- HEADER WITH DIRECT LINK --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('storefront.orders.index') }}"
                    class="w-10 h-10 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/70 dark:text-white/70 transition">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-[20px] font-bold text-black dark:text-white tracking-tight">Konfigurasi Etalase &
                        Pembayaran</h1>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55">Link Etalase: <a
                            href="{{ url("/b/{$business->slug}") }}" target="_blank"
                            class="text-[#007AFF] hover:underline font-medium">{{ url("/b/{$business->slug}") }}</a></p>
                </div>
            </div>

            <a href="{{ url("/b/{$business->slug}") }}" target="_blank"
                class="h-10 px-4 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold transition flex items-center gap-2 shadow-sm">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Kunjungi Etalase Toko</span>
            </a>
        </div>

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: STOREFRONT OPERATIONAL SETTINGS (7 COLS) --}}
            <div class="lg:col-span-7 space-y-6">
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10 mb-5">
                        <i data-lucide="store" class="w-5 h-5 text-[#007AFF]"></i>
                        <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Pengaturan Operasional
                            Etalase</h2>
                    </div>

                    <form action="{{ route('storefront.settings.update') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- TOGGLE: IS STOREFRONT ENABLED --}}
                        <div class="flex items-center justify-between p-4 rounded-[16px] bg-black/5 dark:bg-white/5">
                            <div class="space-y-0.5">
                                <span class="text-[14px] font-bold text-black dark:text-white block">Aktifkan Toko
                                    Online</span>
                                <p class="text-[12px] text-black/55 dark:text-white/55">Tampilkan keranjang belanja dan
                                    formulir checkout di halaman profil bisnis.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_storefront_enabled" value="1" class="sr-only peer"
                                    {{ $setting->is_storefront_enabled ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                                </div>
                            </label>
                        </div>

                        {{-- TOGGLE: IS DISCOVERABLE --}}
                        <div class="flex items-center justify-between p-4 rounded-[16px] bg-black/5 dark:bg-white/5">
                            <div class="space-y-0.5">
                                <span class="text-[14px] font-bold text-black dark:text-white block">Tampil di Direktori
                                    Publik</span>
                                <p class="text-[12px] text-black/55 dark:text-white/55">Izinkan toko Anda ditemukan oleh
                                    publik pada halaman pencarian direktori COOCA (/jelajah).</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_discoverable" value="1" class="sr-only peer"
                                    {{ $setting->is_discoverable ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                                </div>
                            </label>
                        </div>

                        {{-- FULFILLMENT TOGGLES --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-[16px] bg-black/5 dark:bg-white/5 flex items-center justify-between">
                                <div>
                                    <span class="text-[13px] font-bold text-black dark:text-white block">Ambil
                                        Sendiri</span>
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50">Pickup di outlet
                                        toko</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_pickup" value="1" class="sr-only peer"
                                        {{ $setting->allow_pickup ? 'checked' : '' }}>
                                    <div
                                        class="w-9 h-5 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#34C759]">
                                    </div>
                                </label>
                            </div>

                            <div class="p-4 rounded-[16px] bg-black/5 dark:bg-white/5 flex items-center justify-between">
                                <div>
                                    <span class="text-[13px] font-bold text-black dark:text-white block">Kurir Toko</span>
                                    <span class="text-[11.5px] text-black/50 dark:text-white/50">Antar ke alamat
                                        customer</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_delivery" value="1" class="sr-only peer"
                                        {{ $setting->allow_delivery ? 'checked' : '' }}>
                                    <div
                                        class="w-9 h-5 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#34C759]">
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- TRANSACTION MODES --}}
                        <div class="space-y-3 pt-2">
                            <span
                                class="text-[12px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Mode
                                Transaksi Toko</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div
                                    class="p-3.5 rounded-[16px] bg-black/5 dark:bg-white/5 flex items-center justify-between">
                                    <div>
                                        <span class="text-[13px] font-bold text-black dark:text-white block">Request
                                            Order</span>
                                        <span class="text-[11px] text-black/50 dark:text-white/50">Pesanan kustom /
                                            penawaran harga</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="allow_request_order" value="1"
                                            class="sr-only peer"
                                            {{ $setting->allow_request_order ?? true ? 'checked' : '' }}>
                                        <div
                                            class="w-9 h-5 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                        </div>
                                    </label>
                                </div>

                                <div
                                    class="p-3.5 rounded-[16px] bg-black/5 dark:bg-white/5 flex items-center justify-between">
                                    <div>
                                        <span class="text-[13px] font-bold text-black dark:text-white block">Pesanan
                                            Terjadwal</span>
                                        <span class="text-[11px] text-black/50 dark:text-white/50">Pilih tanggal &amp; slot
                                            waktu (H+)</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="allow_scheduled_order" value="1"
                                            class="sr-only peer"
                                            {{ $setting->allow_scheduled_order ?? true ? 'checked' : '' }}>
                                        <div
                                            class="w-9 h-5 bg-black/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- SCHEDULED ORDER OPERATIONAL SETTINGS --}}
                        <div
                            class="p-4 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-3">
                            <span
                                class="text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 block">Pengaturan
                                Pesanan Terjadwal (Katering &amp; PO)</span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Lead-time
                                        Persiapan (Jam)</label>
                                    <input type="number" name="lead_time_hours"
                                        value="{{ (int) ($setting->lead_time_hours ?? 0) }}" min="0"
                                        max="720"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">0 = Boleh
                                        H+0</span>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Jam
                                        Cut-Off Esok Hari</label>
                                    <input type="time" name="cut_off_time"
                                        value="{{ $setting->cut_off_time ? substr((string) $setting->cut_off_time, 0, 5) : '' }}"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Batas jam
                                        pesan</span>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Batas
                                        Kuota Harian</label>
                                    <input type="number" name="daily_order_quota"
                                        value="{{ (int) ($setting->daily_order_quota ?? 0) }}" min="0"
                                        max="10000"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[13px] font-bold text-black dark:text-white">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">0 = Tanpa
                                        batas</span>
                                </div>
                            </div>
                        </div>

                        {{-- MIN ORDER & AUTO CANCEL --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Minimum
                                    Belanja (Rp)</label>
                                <input type="number" name="min_order_amount"
                                    value="{{ (float) $setting->min_order_amount }}" min="0" step="1000"
                                    required
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Set 0 jika tidak ada batas
                                    minimum.</p>
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Batas
                                    Waktu Bayar (Menit)</label>
                                <input type="number" name="order_auto_cancel_minutes"
                                    value="{{ $setting->order_auto_cancel_minutes }}" min="15" max="1440"
                                    required
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Stok dilepas otomatis jika
                                    bukti belum diunggah.</p>
                            </div>
                        </div>

                        {{-- ANNOUNCEMENT BANNER --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Pesan
                                Pengumuman Etalase (Opsional)</label>
                            <textarea name="announcement_text" rows="2"
                                placeholder="Contoh: Pesanan setelah jam 17:00 akan dikirim keesokan harinya."
                                class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none">{{ $setting->announcement_text }}</textarea>
                        </div>

                        <button type="submit"
                            class="h-11 px-6 rounded-full bg-black dark:bg-white hover:bg-black/90 dark:hover:bg-white/90 text-white dark:text-black text-[13.5px] font-bold transition flex items-center justify-center gap-2 shadow-sm">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan Etalase</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- RIGHT COLUMN: PAYMENT METHODS (5 COLS) --}}
            <div class="lg:col-span-5 space-y-6">
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10 mb-4">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="credit-card" class="w-5 h-5 text-[#34C759]"></i>
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Metode Pembayaran
                                Toko</h2>
                        </div>
                        <button type="button" @click="addMethodModalOpen = true"
                            class="h-8 px-3 rounded-full bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-[12px] font-bold transition flex items-center gap-1.5">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($paymentMethods as $method)
                            <div
                                class="p-4 rounded-[18px] border border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between gap-3">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[14px] font-bold text-black dark:text-white">{{ $method->bank_name }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $method->type === 'qris' ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                            {{ $method->type === 'qris' ? 'QRIS' : 'Transfer' }}
                                        </span>
                                    </div>
                                    @if ($method->account_number)
                                        <p class="text-[12.5px] font-mono text-black/70 dark:text-white/70">
                                            {{ $method->account_number }}</p>
                                        <p class="text-[11.5px] text-black/50 dark:text-white/50">a/n
                                            {{ $method->account_holder }}</p>
                                    @endif
                                    @if ($method->qris_image_path)
                                        <span class="text-[11px] text-[#34C759] font-medium flex items-center gap-1 mt-1">
                                            <i data-lucide="image" class="w-3 h-3"></i> Gambar QRIS terpasang
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- TOGGLE ACTIVE --}}
                                    <form action="{{ route('storefront.settings.payment_methods.toggle', $method->id) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            title="{{ $method->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            class="w-8 h-8 rounded-full flex items-center justify-center transition {{ $method->is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 text-black/40 hover:bg-black/20' }}">
                                            <i data-lucide="{{ $method->is_active ? 'check' : 'power' }}"
                                                class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                    {{-- DELETE --}}
                                    <form action="{{ route('storefront.settings.payment_methods.destroy', $method->id) }}"
                                        method="POST" onsubmit="return confirm('Hapus metode pembayaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="w-8 h-8 rounded-full bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-black/40 dark:text-white/40">
                                <i data-lucide="credit-card" class="w-10 h-10 mx-auto stroke-1 mb-2 opacity-50"></i>
                                <p class="text-[13px] font-medium">Belum ada rekening transfer atau QRIS.</p>
                                <p class="text-[11.5px] mt-0.5">Tambahkan minimal 1 rekening agar pelanggan dapat membayar.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- MODAL TAMBAH METODE PEMBAYARAN --}}
        <div x-show="addMethodModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"
                @click.outside="addMethodModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Tambah Metode Pembayaran</h3>
                    <button type="button" @click="addMethodModalOpen = false"
                        class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('storefront.settings.payment_methods.store') }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Tipe
                            Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="methodType = 'bank_transfer'"
                                :class="methodType === 'bank_transfer' ? 'bg-[#007AFF] text-white' :
                                    'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                class="py-2.5 rounded-[12px] text-[12.5px] font-bold transition">
                                Transfer Bank
                            </button>
                            <button type="button" @click="methodType = 'qris'"
                                :class="methodType === 'qris' ? 'bg-[#007AFF] text-white' :
                                    'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                class="py-2.5 rounded-[12px] text-[12.5px] font-bold transition">
                                QRIS Toko
                            </button>
                        </div>
                        <input type="hidden" name="type" :value="methodType">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Nama Bank /
                            Penyedia <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" required
                            placeholder="Contoh: BCA, Mandiri, BRI, QRIS Toko"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'bank_transfer'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Nomor
                            Rekening</label>
                        <input type="text" name="account_number" placeholder="Contoh: 1234567890"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'bank_transfer'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Atas Nama
                            Rekening</label>
                        <input type="text" name="account_holder" placeholder="Contoh: PT Toko Berkah / Budi Santoso"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'qris'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Unggah Gambar
                            QRIS (PNG / JPG)</label>
                        <input type="file" name="qris_image" accept="image/*"
                            class="w-full text-[12.5px] text-black/60 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[12.5px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Petunjuk
                            Pembayaran (Opsional)</label>
                        <textarea name="instructions" rows="2" placeholder="Contoh: Harap cantumkan nomor order pada berita transfer."
                            class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="addMethodModalOpen = false"
                            class="px-4 py-2 rounded-full text-[13px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold shadow-sm transition">
                            Simpan Rekening
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
