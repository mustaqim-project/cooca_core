{{-- TAB 1: OPERASIONAL & PROFIL ETALASE --}}
<div class="space-y-6">
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Status &amp; Visibilitas Toko</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Kontrol apakah etalase publik dapat diakses dan dijelajahi pelanggan</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $setting->is_storefront_enabled ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                {{ $setting->is_storefront_enabled ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- TOGGLE: IS STOREFRONT ENABLED --}}
            <div class="flex items-center justify-between p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                <div class="space-y-0.5 pr-4">
                    <span class="text-[13.5px] font-bold text-black dark:text-white block">Aktifkan Toko Online</span>
                    <p class="text-[11.5px] text-black/55 dark:text-white/55">Tampilkan keranjang belanja dan formulir checkout di halaman profil bisnis.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="hidden" name="is_storefront_enabled" value="0">
                    <input type="checkbox" name="is_storefront_enabled" value="1" class="sr-only peer"
                        {{ $setting->is_storefront_enabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                    </div>
                </label>
            </div>

            {{-- TOGGLE: IS DISCOVERABLE --}}
            <div class="flex items-center justify-between p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                <div class="space-y-0.5 pr-4">
                    <span class="text-[13.5px] font-bold text-black dark:text-white block">Tampil di Direktori Publik</span>
                    <p class="text-[11.5px] text-black/55 dark:text-white/55">Izinkan toko ditemukan pelanggan pada halaman pencarian direktori COOCA (/jelajah).</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="hidden" name="is_discoverable" value="0">
                    <input type="checkbox" name="is_discoverable" value="1" class="sr-only peer"
                        {{ $setting->is_discoverable ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- ATURAN TRANSAKSI DASAR --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold">
                <i data-lucide="sliders" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Aturan Belanja &amp; Pembatalan</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Nilai minimum order dan perlindungan penguncian stok otomatis</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Minimum Nilai Belanja (Rp)
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-black/40 dark:text-white/40">Rp</span>
                    <input type="number" name="min_order_amount"
                        value="{{ (float) $setting->min_order_amount }}" min="0" step="1000" required
                        class="w-full h-11 pl-10 pr-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1.5">Isi 0 jika toko Anda tidak membatasi minimum pembelian.</p>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                    Batas Waktu Pembayaran (Menit)
                </label>
                <div class="relative">
                    <input type="number" name="order_auto_cancel_minutes"
                        value="{{ $setting->order_auto_cancel_minutes }}" min="15" max="1440" required
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-black/40 dark:text-white/40">Menit</span>
                </div>
                <p class="text-[11px] text-black/45 dark:text-white/45 mt-1.5">Pesanan otomatis dibatalkan dan stok dikembalikan jika belum dibayar.</p>
            </div>
        </div>
    </div>

    {{-- PENGUMUMAN ETALASE --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center font-bold">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Banner Pengumuman Toko</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">Teks sorotan yang tampil di bagian paling atas halaman etalase publik</p>
            </div>
        </div>

        <div>
            <textarea name="announcement_text" rows="3"
                placeholder="Contoh: Toko libur Hari Raya Idul Fitri tgl 20-25 April. Pesanan katering harap dipesan H-2."
                class="w-full p-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-none leading-relaxed">{{ $setting->announcement_text }}</textarea>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1.5">Kosongkan jika sedang tidak ada pengumuman khusus untuk pelanggan.</p>
        </div>
    </div>
</div>
