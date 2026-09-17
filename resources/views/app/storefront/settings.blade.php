@extends('layouts.app', [
    'title' => 'Pengaturan Toko Online & Pembayaran - Cooca',
    'headerTitle' => 'Pengaturan Toko Online',
    'headerSubtitle' => 'Konfigurasi operasional storefront, visibilitas publik, dan rekening penerimaan transfer bank / QRIS',
])

@section('content')
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-28 sm:pb-32 lg:pb-10"
        x-data="{
            addMethodModalOpen: false,
            methodType: 'bank_transfer',
            deleteModalOpen: false,
            methodToDelete: null
        }">

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

        {{-- UNIFIED STOREFRONT HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => 'Pengaturan Etalase Toko'])

        {{-- BENTO OVERVIEW KPI METRIC CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            {{-- Card 1: Status Toko --}}
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Status Etalase</span>
                    <div
                        class="w-8 h-8 rounded-full {{ $setting->is_storefront_enabled ? 'bg-emerald-50 dark:bg-emerald-950/30 text-[#34C759]' : 'bg-black/5 dark:bg-white/10 text-black/40' }} flex items-center justify-center shrink-0">
                        <i data-lucide="store" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-[22px] font-bold tracking-tight {{ $setting->is_storefront_enabled ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/50 dark:text-white/50' }} truncate">
                    {{ $setting->is_storefront_enabled ? 'Aktif (Publik)' : 'Dinonaktifkan' }}
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    {{ $setting->is_discoverable ? 'Tampil di direktori /jelajah' : 'Hanya via tautan langsung' }}
                </p>
            </div>

            {{-- Card 2: Rekening Aktif --}}
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Metode Bayar</span>
                    <div
                        class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-950/30 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-[22px] font-bold tracking-tight text-black dark:text-white tabular-nums truncate">
                    {{ $paymentMethods->where('is_active', true)->count() }} <span class="text-sm font-normal text-black/50 dark:text-white/50">/ {{ $paymentMethods->count() }} Aktif</span>
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Transfer Bank &amp; QRIS
                </p>
            </div>

            {{-- Card 3: Pemenuhan Order --}}
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Opsi Pengiriman</span>
                    <div
                        class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-950/30 text-[#5856D6] flex items-center justify-center shrink-0">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-base sm:text-lg font-bold tracking-tight text-black dark:text-white truncate">
                    @if ($setting->allow_pickup && $setting->allow_delivery)
                        Pickup &amp; Kurir
                    @elseif($setting->allow_pickup)
                        Pickup Saja
                    @elseif($setting->allow_delivery)
                        Kurir Saja
                    @else
                        Tidak Aktif
                    @endif
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Layanan pengantaran toko
                </p>
            </div>

            {{-- Card 4: Transaksi Kustom --}}
            <div
                class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Mode Transaksi</span>
                    <div
                        class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-950/30 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-[22px] font-bold tracking-tight text-black dark:text-white tabular-nums truncate">
                    @php
                        $activeModesCount = 0;
                        if ($setting->allow_request_order) $activeModesCount++;
                        if ($setting->allow_scheduled_order) $activeModesCount++;
                        if ($setting->allow_customer_po) $activeModesCount++;
                        if ($setting->allow_reservation) $activeModesCount++;
                    @endphp
                    {{ $activeModesCount }} <span class="text-sm font-normal text-black/50 dark:text-white/50">Mode Aktif</span>
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Katering, PO &amp; Reservasi
                </p>
            </div>
        </div>

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: STOREFRONT OPERATIONAL SETTINGS (7 COLS) --}}
            <div class="lg:col-span-7 space-y-6">
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm">
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
                                <input type="hidden" name="is_storefront_enabled" value="0">
                                <input type="checkbox" name="is_storefront_enabled" value="1" class="sr-only peer"
                                    {{ $setting->is_storefront_enabled ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
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
                                <input type="hidden" name="is_discoverable" value="0">
                                <input type="checkbox" name="is_discoverable" value="1" class="sr-only peer"
                                    {{ $setting->is_discoverable ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
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
                                    <input type="hidden" name="allow_pickup" value="0">
                                    <input type="checkbox" name="allow_pickup" value="1" class="sr-only peer"
                                        {{ $setting->allow_pickup ? 'checked' : '' }}>
                                    <div
                                        class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#34C759]">
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
                                    <input type="hidden" name="allow_delivery" value="0">
                                    <input type="checkbox" name="allow_delivery" value="1" class="sr-only peer"
                                        {{ $setting->allow_delivery ? 'checked' : '' }}>
                                    <div
                                        class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#34C759]">
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
                                        <input type="hidden" name="allow_request_order" value="0">
                                        <input type="checkbox" name="allow_request_order" value="1"
                                            class="sr-only peer"
                                            {{ $setting->allow_request_order ?? true ? 'checked' : '' }}>
                                        <div
                                            class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
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
                                        <input type="hidden" name="allow_scheduled_order" value="0">
                                        <input type="checkbox" name="allow_scheduled_order" value="1"
                                            class="sr-only peer"
                                            {{ $setting->allow_scheduled_order ?? true ? 'checked' : '' }}>
                                        <div
                                            class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                        </div>
                                    </label>
                                </div>

                                <div
                                    class="p-3.5 rounded-[16px] bg-black/5 dark:bg-white/5 flex items-center justify-between">
                                    <div>
                                        <span class="text-[13px] font-bold text-black dark:text-white block">Customer PO &amp; Batch</span>
                                        <span class="text-[11px] text-black/50 dark:text-white/50">Pesanan kantor B2B &amp; multi-drop</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="allow_customer_po" value="0">
                                        <input type="checkbox" name="allow_customer_po" value="1"
                                            class="sr-only peer"
                                            {{ $setting->allow_customer_po ?? true ? 'checked' : '' }}>
                                        <div
                                            class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                        </div>
                                    </label>
                                </div>

                                <div
                                    class="p-3.5 rounded-[16px] bg-black/5 dark:bg-white/5 flex items-center justify-between">
                                    <div>
                                        <span class="text-[13px] font-bold text-black dark:text-white block">Reservasi &amp; Booking</span>
                                        <span class="text-[11px] text-black/50 dark:text-white/50">Reservasi meja resto &amp; jasa</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="allow_reservation" value="0">
                                        <input type="checkbox" name="allow_reservation" value="1"
                                            class="sr-only peer"
                                            {{ $setting->allow_reservation ?? true ? 'checked' : '' }}>
                                        <div
                                            class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- SCHEDULED ORDER OPERATIONAL SETTINGS --}}
                        <div
                            class="p-4 sm:p-5 rounded-[18px] bg-black/[0.03] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-4"
                            x-data="{ batchMode: '{{ $setting->batch_dates_mode ?? 'operating_days' }}' }">
                            <span
                                class="text-[12px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 block">Pengaturan
                                Pesanan Terjadwal (Katering, PO &amp; Reservasi)</span>

                            {{-- TOGGLE: ALLOW CUSTOM DATE --}}
                            <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 flex items-center justify-between">
                                <div class="space-y-0.5 pr-3">
                                    <span class="text-[13px] font-bold text-black dark:text-white block">Izinkan Pembeli Memilih Tanggal Bebas</span>
                                    <span class="text-[11px] text-black/50 dark:text-white/50 block">Jika dimatikan, pembeli HANYA bisa memilih dari tanggal batch yang telah Anda sediakan (cocok untuk PO Batch FnB).</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="hidden" name="allow_custom_date" value="0">
                                    <input type="checkbox" name="allow_custom_date" value="1" class="sr-only peer"
                                        {{ ($setting->allow_custom_date ?? true) ? 'checked' : '' }}>
                                    <div
                                        class="w-9 h-5 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Lead-time
                                        Persiapan (Jam)</label>
                                    <input type="number" name="lead_time_hours"
                                        value="{{ (int) ($setting->lead_time_hours ?? 0) }}" min="0"
                                        max="720"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-bold text-black dark:text-white tabular-nums">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">0 = Boleh H+0</span>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Jam
                                        Cut-Off Esok Hari</label>
                                    <input type="time" name="cut_off_time"
                                        value="{{ $setting->cut_off_time ? substr((string) $setting->cut_off_time, 0, 5) : '' }}"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-bold text-black dark:text-white tabular-nums">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Batas jam pesan</span>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Batas
                                        Kuota per Batch / Hari</label>
                                    <input type="number" name="daily_order_quota"
                                        value="{{ (int) ($setting->daily_order_quota ?? 0) }}" min="0"
                                        max="10000"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-bold text-black dark:text-white tabular-nums">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">0 = Tanpa batas</span>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Satuan Kuota</label>
                                    <input type="text" name="preorder_quota_unit"
                                        value="{{ $setting->preorder_quota_unit ?? 'PCS' }}" maxlength="20"
                                        placeholder="PCS / Porsi / Box"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-bold text-black dark:text-white">
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Contoh: PCS, Porsi, Box</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Basis Perhitungan Kuota</label>
                                    <select name="quota_metric"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-medium text-black dark:text-white cursor-pointer">
                                        <option value="quantity" {{ ($setting->quota_metric ?? 'orders') === 'quantity' ? 'selected' : '' }}>
                                            Total Kuantitas Item / Produk (misal: 150 PCS)
                                        </option>
                                        <option value="orders" {{ ($setting->quota_metric ?? 'orders') === 'orders' ? 'selected' : '' }}>
                                            Jumlah Transaksi / Pesanan (misal: 150 Pesanan)
                                        </option>
                                    </select>
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Pilih 'Total Kuantitas Item' jika kuota dihitung per buah/porsi produk.</span>
                                </div>

                                <div>
                                    <label class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Mode Penentuan Tanggal Batch</label>
                                    <select name="batch_dates_mode" x-model="batchMode"
                                        class="w-full h-10 px-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-medium text-black dark:text-white cursor-pointer">
                                        <option value="operating_days">Rutin Mingguan Sesuai Hari Operasional</option>
                                        <option value="custom_dates">Daftar Tanggal Batch Spesifik / Kustom</option>
                                    </select>
                                    <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Pilih mode otomatis mingguan atau tanggal tertentu di bawah.</span>
                                </div>
                            </div>

                            @php
                                $dayNames = [
                                    'monday' => 'Sen',
                                    'tuesday' => 'Sel',
                                    'wednesday' => 'Rab',
                                    'thursday' => 'Kam',
                                    'friday' => 'Jum',
                                    'saturday' => 'Sab',
                                    'sunday' => 'Min',
                                ];
                                $activeDays = is_array($setting->operating_days) && !empty($setting->operating_days)
                                    ? $setting->operating_days
                                    : array_keys($dayNames);
                                $slotList = is_array($setting->available_slots) && !empty($setting->available_slots)
                                    ? implode("\n", $setting->available_slots)
                                    : "09:00 - 11:00\n11:00 - 13:00\n14:00 - 16:00\n16:00 - 18:00\n19:00 - 21:00";
                                
                                $customBatchText = '';
                                if (is_array($setting->custom_batch_dates) && !empty($setting->custom_batch_dates)) {
                                    $lines = [];
                                    foreach ($setting->custom_batch_dates as $cbd) {
                                        $str = $cbd['date'] ?? '';
                                        if (isset($cbd['quota']) && $cbd['quota'] !== null) {
                                            $str .= ' : ' . $cbd['quota'];
                                        }
                                        if (!empty($cbd['note'])) {
                                            $str .= ' : ' . $cbd['note'];
                                        }
                                        $lines[] = $str;
                                    }
                                    $customBatchText = implode("\n", $lines);
                                }
                            @endphp

                            <div x-show="batchMode === 'operating_days'">
                                <label class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1.5">Hari Operasional Menerima Pesanan</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($dayNames as $dKey => $dLabel)
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12.5px] font-medium text-black dark:text-white cursor-pointer hover:border-[#007AFF] transition">
                                            <input type="checkbox" name="operating_days[]" value="{{ $dKey }}"
                                                class="rounded border-gray-300 text-[#007AFF] focus:ring-[#007AFF]"
                                                {{ in_array($dKey, $activeDays, true) ? 'checked' : '' }}>
                                            <span>{{ $dLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div x-show="batchMode === 'custom_dates'" x-cloak>
                                <label class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Daftar Tanggal Batch Spesifik (1 tanggal per baris)</label>
                                <textarea name="custom_batch_dates" rows="3"
                                    class="w-full p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"
                                    placeholder="2026-09-25 : 150 : Batch 1 Jumat&#10;2026-10-02 : 150 : Batch 2 Jumat&#10;2026-10-09 : 200 : Batch Spesifik">{{ $customBatchText }}</textarea>
                                <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Format: TTTT-BB-HH : Kuota : Catatan (opsional).</span>
                            </div>

                            <div>
                                <label class="block text-[11.5px] font-semibold text-black/60 dark:text-white/60 mb-1">Slot Waktu Pengantaran / Reservasi (1 slot per baris)</label>
                                <textarea name="available_slots" rows="3"
                                    class="w-full p-2.5 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"
                                    placeholder="09:00 - 11:00&#10;11:00 - 13:00&#10;14:00 - 16:00">{{ $slotList }}</textarea>
                                <span class="text-[10.5px] text-black/45 dark:text-white/45 mt-0.5 block">Format: Jam Mulai - Jam Selesai (dipisahkan baris baru).</span>
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
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Set 0 jika tidak ada batas
                                    minimum.</p>
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Batas
                                    Waktu Bayar (Menit)</label>
                                <input type="number" name="order_auto_cancel_minutes"
                                    value="{{ $setting->order_auto_cancel_minutes }}" min="15" max="1440"
                                    required
                                    class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
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
                                class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none">{{ $setting->announcement_text }}</textarea>
                        </div>

                        <button type="submit"
                            class="h-11 px-6 rounded-full bg-black dark:bg-white hover:bg-black/90 dark:hover:bg-white/90 text-white dark:text-black text-[13.5px] font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- RIGHT COLUMN: PAYMENT METHODS (5 COLS) --}}
            <div class="lg:col-span-5 space-y-6">
                <div
                    class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10 mb-4">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="credit-card" class="w-5 h-5 text-[#34C759]"></i>
                            <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Metode Pembayaran
                                Toko</h2>
                        </div>
                        <button type="button" @click="addMethodModalOpen = true"
                            class="h-8 px-3.5 rounded-full bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-[12px] font-bold transition flex items-center gap-1.5 cursor-pointer">
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
                                        <p class="text-[12.5px] font-mono tabular-nums text-black/70 dark:text-white/70">
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
                                            class="w-8 h-8 rounded-full flex items-center justify-center transition cursor-pointer {{ $method->is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 text-black/40 hover:bg-black/20' }}">
                                            <i data-lucide="{{ $method->is_active ? 'check' : 'power' }}"
                                                class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                    {{-- DELETE TRIGGER --}}
                                    <button type="button"
                                        @click="methodToDelete = {{ json_encode(['id' => $method->id, 'bank_name' => $method->bank_name, 'type' => $method->type, 'account_number' => $method->account_number]) }}; deleteModalOpen = true;"
                                        title="Hapus Rekening"
                                        class="w-8 h-8 rounded-full bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center transition cursor-pointer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
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

        {{-- APPLE ALERT CONFIRMATION DIALOG (DELETE PAYMENT METHOD) --}}
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"
                @click.outside="deleteModalOpen = false">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Hapus Metode Pembayaran?</h3>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">
                            Anda akan menghapus rekening <strong class="text-black dark:text-white font-semibold" x-text="methodToDelete?.bank_name"></strong>
                            <span x-show="methodToDelete?.account_number" class="font-mono text-xs text-black/70 dark:text-white/70" x-text="'(' + methodToDelete?.account_number + ')'"></span>.
                        </p>
                    </div>
                </div>

                {{-- PENENANG JIWA MICROCOPY --}}
                <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-start gap-2.5 text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                    <span>Tenang: Riwayat pesanan dan bukti transfer pelanggan masa lalu yang pernah menggunakan rekening ini tetap aman tercatat di pembukuan.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="deleteModalOpen = false"
                        class="px-4 py-2 rounded-full text-[13px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition cursor-pointer">
                        Batal
                    </button>
                    <template x-if="methodToDelete">
                        <form :action="'{{ url('/storefront/settings/payment-methods') }}/' + methodToDelete.id" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-5 py-2.5 rounded-full bg-[#FF3B30] hover:bg-[#E0352B] text-white text-[13px] font-bold transition shadow-sm cursor-pointer">
                                Ya, Hapus Rekening
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        {{-- MODAL TAMBAH METODE PEMBAYARAN (APPLE HIG MULTI-DEVICE DIALOG) --}}
        <div x-show="addMethodModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4 max-h-[92vh] overflow-y-auto"
                @click.outside="addMethodModalOpen = false">

                {{-- Mobile Grab Bar --}}
                <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-1 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Tambah Metode Pembayaran</h3>
                    <button type="button" @click="addMethodModalOpen = false"
                        class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
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
                                class="py-2.5 rounded-[12px] text-[12.5px] font-bold transition cursor-pointer">
                                Transfer Bank
                            </button>
                            <button type="button" @click="methodType = 'qris'"
                                :class="methodType === 'qris' ? 'bg-[#007AFF] text-white' :
                                    'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                class="py-2.5 rounded-[12px] text-[12.5px] font-bold transition cursor-pointer">
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
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'bank_transfer'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Nomor
                            Rekening</label>
                        <input type="text" name="account_number" placeholder="Contoh: 1234567890"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium font-mono tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'bank_transfer'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Atas Nama
                            Rekening</label>
                        <input type="text" name="account_holder" placeholder="Contoh: PT Toko Berkah / Budi Santoso"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'qris'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Unggah Gambar
                            QRIS (PNG / JPG)</label>
                        <input type="file" name="qris_image" accept="image/*"
                            class="w-full text-[12.5px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[12.5px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Petunjuk
                            Pembayaran (Opsional)</label>
                        <textarea name="instructions" rows="2" placeholder="Contoh: Harap cantumkan nomor order pada berita transfer."
                            class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="addMethodModalOpen = false"
                            class="px-4 py-2 rounded-full text-[13px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold shadow-sm transition cursor-pointer">
                            Simpan Rekening
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
