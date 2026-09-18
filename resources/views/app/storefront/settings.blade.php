@extends('layouts.app', [
    'title' => 'Pengaturan Toko Online & Pembayaran - Cooca',
    'headerTitle' => 'Pengaturan Toko Online',
    'headerSubtitle' => 'Konfigurasi operasional storefront, visibilitas publik, saldo gateway, dan rekening penerimaan transfer bank / QRIS',
])

@section('content')
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-28 sm:pb-32 lg:pb-10"
        x-data="{
            activeTab: '{{ request('tab', 'general') }}',
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
            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Status Etalase</span>
                    <div class="w-8 h-8 rounded-full {{ $setting->is_storefront_enabled ? 'bg-emerald-50 dark:bg-emerald-950/30 text-[#34C759]' : 'bg-black/5 dark:bg-white/10 text-black/40' }} flex items-center justify-center shrink-0">
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

            {{-- Card 2: Saldo Bersih Gateway --}}
            <div @click="activeTab = 'payment'" class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)] cursor-pointer hover:border-[#007AFF]/40 transition">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Saldo Siap Cair</span>
                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-[22px] font-bold tracking-tight text-[#248A3D] dark:text-[#30D158] tabular-nums truncate">
                    {{ $business->currency_symbol ?? 'Rp' }} {{ number_format($unsettledData['summary']['total_net'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11.5px] text-[#007AFF] font-medium mt-1 truncate flex items-center gap-1">
                    <span>{{ $paymentMethods->where('is_active', true)->count() }} Rekening &bull; Lihat Saldo</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </p>
            </div>

            {{-- Card 3: Pemenuhan Order --}}
            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Opsi Pengiriman</span>
                    <div class="w-8 h-8 rounded-full bg-purple-50 dark:bg-purple-950/30 text-[#5856D6] flex items-center justify-center shrink-0">
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
                    Metode penyerahan pesanan
                </p>
            </div>

            {{-- Card 4: Mode Transaksi --}}
            <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[12px] sm:text-[13px] font-medium text-black/55 dark:text-white/55 truncate">Mode Transaksi</span>
                    <div class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-950/30 text-[#FF9500] flex items-center justify-center shrink-0">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl sm:text-[22px] font-bold tracking-tight text-black dark:text-white tabular-nums truncate">
                    @php
                        $activeModesCount = 0;
                        if ($setting->allow_request_order ?? true) $activeModesCount++;
                        if ($setting->allow_scheduled_order ?? true) $activeModesCount++;
                        if ($setting->allow_customer_po ?? true) $activeModesCount++;
                        if ($setting->allow_reservation ?? true) $activeModesCount++;
                    @endphp
                    {{ $activeModesCount }} <span class="text-sm font-normal text-black/50 dark:text-white/50">Mode Aktif</span>
                </div>
                <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                    Kustom, PO, Jadwal &amp; Reservasi
                </p>
            </div>
        </div>

        {{-- BENTO APPLE HIG MODULAR TAB BAR --}}
        <div class="w-full overflow-x-auto no-scrollbar py-1">
            <div class="inline-flex p-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] border border-black/[0.04] dark:border-white/[0.06] gap-1.5 min-w-max">
                {{-- Tab 1: General --}}
                <button type="button" @click="activeTab = 'general'"
                    class="px-4 py-2 rounded-[12px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'general' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'">
                    <i data-lucide="store" class="w-4 h-4" :class="activeTab === 'general' ? 'text-[#007AFF]' : 'opacity-70'"></i>
                    <span>Operasional &amp; Toko</span>
                </button>

                {{-- Tab 2: Fulfillment --}}
                <button type="button" @click="activeTab = 'fulfillment'"
                    class="px-4 py-2 rounded-[12px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'fulfillment' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'">
                    <i data-lucide="package-check" class="w-4 h-4" :class="activeTab === 'fulfillment' ? 'text-[#34C759]' : 'opacity-70'"></i>
                    <span>Mode Pemenuhan</span>
                </button>

                {{-- Tab 3: Features --}}
                <button type="button" @click="activeTab = 'features'"
                    class="px-4 py-2 rounded-[12px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'features' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'">
                    <i data-lucide="layout-grid" class="w-4 h-4" :class="activeTab === 'features' ? 'text-[#5856D6]' : 'opacity-70'"></i>
                    <span>Fitur 20 Industri</span>
                </button>

                {{-- Tab 4: Schedule --}}
                <button type="button" @click="activeTab = 'schedule'"
                    class="px-4 py-2 rounded-[12px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'schedule' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'">
                    <i data-lucide="clock" class="w-4 h-4" :class="activeTab === 'schedule' ? 'text-[#FF9500]' : 'opacity-70'"></i>
                    <span>Jam &amp; Slot Waktu</span>
                </button>

                {{-- Tab 5: Payment & Settlement --}}
                <button type="button" @click="activeTab = 'payment'"
                    class="px-4 py-2 rounded-[12px] text-[13px] font-medium transition-all duration-150 flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'payment' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.03]'">
                    <i data-lucide="wallet" class="w-4 h-4" :class="activeTab === 'payment' ? 'text-[#34C759]' : 'opacity-70'"></i>
                    <span>Pembayaran &amp; Saldo Gateway</span>
                    @if(($unsettledData['summary']['total_net'] ?? 0) > 0)
                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    @endif
                </button>
            </div>
        </div>

        {{-- FORM CONTAINER FOR TABS 1, 2, 3, 4 --}}
        <form action="{{ route('storefront.settings.update') }}" method="POST">
            @csrf

            <div x-show="activeTab === 'general'" x-cloak>
                @include('app.storefront.tabs.tab-general')
            </div>

            <div x-show="activeTab === 'fulfillment'" x-cloak>
                @include('app.storefront.tabs.tab-fulfillment')
            </div>

            <div x-show="activeTab === 'features'" x-cloak>
                @include('app.storefront.tabs.tab-features')
            </div>

            <div x-show="activeTab === 'schedule'" x-cloak>
                @include('app.storefront.tabs.tab-schedule')
            </div>

            {{-- BOTTOM ACTION BAR FOR SETTINGS TABS (1-4) --}}
            <div x-show="activeTab !== 'payment'" class="pt-4 flex items-center justify-end">
                <button type="submit"
                    class="h-11 px-7 rounded-full bg-black dark:bg-white hover:bg-black/90 dark:hover:bg-white/90 text-white dark:text-black text-[13.5px] font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer active:scale-[0.98]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </form>

        {{-- TAB 5: PAYMENT & GATEWAY SETTLEMENT (INDEPENDENT ACTIONS) --}}
        <div x-show="activeTab === 'payment'" x-cloak>
            @include('app.storefront.tabs.tab-payment-settlement')
        </div>

        {{-- ========================================================== --}}
        {{-- APPLE ALERT CONFIRMATION DIALOG (DELETE PAYMENT METHOD)    --}}
        {{-- ========================================================== --}}
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
                    <span>Riwayat pesanan dan bukti transfer pelanggan masa lalu yang pernah menggunakan rekening ini tetap aman tercatat di pembukuan.</span>
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

        {{-- ========================================================== --}}
        {{-- MODAL TAMBAH METODE PEMBAYARAN (APPLE HIG DIALOG)          --}}
        {{-- ========================================================== --}}
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
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Tambah Rekening Manual</h3>
                    <button type="button" @click="addMethodModalOpen = false"
                        class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('storefront.settings.payment_methods.store') }}" method="POST"
                    enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1.5">Tipe Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="methodType = 'bank_transfer'"
                                :class="methodType === 'bank_transfer' ? 'bg-[#007AFF] text-white' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                class="py-2.5 rounded-[12px] text-[12.5px] font-bold transition cursor-pointer">
                                Transfer Bank
                            </button>
                            <button type="button" @click="methodType = 'qris'"
                                :class="methodType === 'qris' ? 'bg-[#007AFF] text-white' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70'"
                                class="py-2.5 rounded-[12px] text-[12.5px] font-bold transition cursor-pointer">
                                QRIS Toko
                            </button>
                        </div>
                        <input type="hidden" name="type" :value="methodType">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Nama Bank / Penyedia <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" required
                            placeholder="Contoh: BCA, Mandiri, BRI, QRIS Toko"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'bank_transfer'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Nomor Rekening</label>
                        <input type="text" name="account_number" placeholder="Contoh: 1234567890"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium font-mono tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'bank_transfer'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Atas Nama Rekening</label>
                        <input type="text" name="account_holder" placeholder="Contoh: PT Toko Berkah / Budi Santoso"
                            class="w-full h-11 px-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div x-show="methodType === 'qris'">
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Unggah Gambar QRIS (PNG / JPG)</label>
                        <input type="file" name="qris_image" accept="image/*"
                            class="w-full text-[12.5px] text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[12.5px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">Petunjuk Pembayaran (Opsional)</label>
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
