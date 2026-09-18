{{-- TAB 3: FITUR & MODE TRANSAKSI 20 INDUSTRI --}}
<div class="space-y-6">
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center font-bold">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Mode Transaksi Spesifik Industri</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Aktifkan fitur yang sesuai dengan model bisnis Anda dari 20 sektor industri</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- FEATURE 1: REQUEST ORDER --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-question" class="w-4 h-4 text-[#007AFF]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">Request Order (Kustom)</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        Formulir pengajuan pesanan khusus, penawaran harga, dan negosiasi spesifikasi pelanggan sebelum pembayaran disepakati.
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded mt-1">
                        Cocok: Konveksi, Percetakan, Katering Kustom, Furniture
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_request_order" value="0">
                    <input type="checkbox" name="allow_request_order" value="1" class="sr-only peer"
                        {{ $setting->allow_request_order ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                    </div>
                </label>
            </div>

            {{-- FEATURE 2: PESANAN TERJADWAL --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-[#FF9500]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">Pesanan Terjadwal (Pre-Order)</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        Pelanggan dapat memilih tanggal pengiriman atau pengambilan H+ dengan pembatasan lead time dan kuota per hari.
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#FF9500] bg-[#FF9500]/10 px-2 py-0.5 rounded mt-1">
                        Cocok: Bakery, Katering Diet, Frozen Food, Event Organizer
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_scheduled_order" value="0">
                    <input type="checkbox" name="allow_scheduled_order" value="1" class="sr-only peer"
                        {{ $setting->allow_scheduled_order ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF9500]">
                    </div>
                </label>
            </div>

            {{-- FEATURE 3: CUSTOMER PO & BATCH --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-[#5856D6]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">Customer PO &amp; Batch B2B</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        Penerimaan Purchase Order korporasi dengan pengiriman bertahap (multi-drop batch) dan faktur penagihan formal.
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#5856D6] bg-[#5856D6]/10 px-2 py-0.5 rounded mt-1">
                        Cocok: Katering Pabrik, Suplier Bahan, Distributor FMCG
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_customer_po" value="0">
                    <input type="checkbox" name="allow_customer_po" value="1" class="sr-only peer"
                        {{ $setting->allow_customer_po ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5856D6]">
                    </div>
                </label>
            </div>

            {{-- FEATURE 4: RESERVASI & BOOKING JADWAL --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-check" class="w-4 h-4 text-[#34C759]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">Reservasi Meja &amp; Booking Jadwal</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        Pemesanan slot waktu layanan atau meja makan. Menonaktifkan opsi ini akan menyembunyikan menu reservasi dari etalase.
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/10 px-2 py-0.5 rounded mt-1">
                        Cocok: Resto Dine-In, Cafe, Barbershop, Salon, Bengkel, Klinik
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_reservation" value="0">
                    <input type="checkbox" name="allow_reservation" value="1" class="sr-only peer"
                        {{ $setting->allow_reservation ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- REKOMENDASI PRESET INDUSTRI --}}
    <div class="bg-black/[0.02] dark:bg-white/[0.02] rounded-[22px] border border-black/5 dark:border-white/10 p-5 space-y-3">
        <div class="flex items-center gap-2 text-black/70 dark:text-white/70">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#FF9500]"></i>
            <span class="text-[13px] font-bold">Panduan Optimasi Antarmuka:</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-[12px] text-black/60 dark:text-white/60">
            <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white block">Toko Retail &amp; Produk</span>
                <p>Aktifkan Toko Online + Pickup &amp; Kurir. <strong>Matikan Reservasi &amp; Booking</strong> agar antarmuka ringkas.</p>
            </div>
            <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white block">Restoran Dine-In &amp; Cafe</span>
                <p>Aktifkan Reservasi Meja + Pickup. Integrasi terhubung langsung dengan Kasir Resto POS.</p>
            </div>
            <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white block">Jasa Servis &amp; Perawatan</span>
                <p>Aktifkan Reservasi &amp; Booking (Slot Waktu). <strong>Matikan Pengantaran Kurir</strong>.</p>
            </div>
        </div>
    </div>
</div>
