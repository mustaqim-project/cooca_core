@extends('layouts.public_marketing')

@section('title', 'Daftar Harga & Paket Transparan Tanpa Biaya Tersembunyi | COOCA')
@section('description', 'Pilihan paket jujur dan transparan untuk UMKM Indonesia. Mulai dari gratis selamanya hingga
    paket lengkap multi-cabang. Tanpa biaya instalasi dan bebas upgrade kapan saja.')
@section('keywords', 'harga cooca, paket aplikasi kasir, biaya software pos umkm, software akuntansi toko murah, erp
    toko murah indonesia')

@section('content')
    <div x-data="{
        pricingCycle: 'monthly',
        openFaq: null,
        showComparisonModal: false,
        activeDetailModal: null,
        calcOutlets: '1',
        calcTeam: 'solo',
        calcNeed: 'pos_nota',
        toggleFaq(idx) {
            this.openFaq = this.openFaq === idx ? null : idx;
            this.refreshIcons();
        },
        openDetail(plan) {
            this.activeDetailModal = plan;
            this.refreshIcons();
        },
        closeDetail() {
            this.activeDetailModal = null;
        },
        openComparison() {
            this.showComparisonModal = true;
            this.refreshIcons();
        },
        closeComparison() {
            this.showComparisonModal = false;
        },
        refreshIcons() {
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },
        get recommendedPlan() {
            if (this.calcOutlets === 'multi' || this.calcNeed === 'ai_buss') {
                return 'prestige';
            }
            if (this.calcNeed === 'omnichannel' || this.calcOutlets === '2_3') {
                return 'premium';
            }
            if (this.calcTeam === 'team' || this.calcNeed === 'stock_hpp') {
                return 'standard';
            }
            return 'free';
        }
    }" x-init="refreshIcons()"
        class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Bento Apple HIG Canvas with Pure Typography) ═══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="pt-12 sm:pt-16 lg:pt-20 pb-12 sm:pb-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Header Title & Reassurance -->
                <div class="max-w-3xl mx-auto text-center space-y-4">
                    <!-- Pure Typographic Kicker (Zero Pill Abuse) -->
                    <div
                        class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        PILIHAN PAKET &amp; BIAYA TRANSPARAN
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.2] text-balance">
                        Investasi Jujur dan Terjangkau untuk Kemajuan Usaha Anda
                    </h1>

                    <p
                        class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl mx-auto pt-1">
                        Dirancang khusus agar mudah digunakan oleh pemilik usaha dari berbagai rentang usia. Mulai dari
                        paket gratis tanpa kartu kredit, hingga paket lengkap multi-cabang.
                    </p>

                    <!-- 3 Core Guarantees for UMKM (40-65 y.o. peace of mind) -->
                    <div class="pt-6 grid grid-cols-1 sm:grid-cols-3 gap-3 text-left">
                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Tanpa Biaya Pasang</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Pakai langsung dari HP atau laptop</div>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                <i data-lucide="unlock" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Bebas Ikatan Kontrak</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Ganti paket atau berhenti kapan saja</div>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <i data-lucide="database" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Data Milik Anda 100%</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Bisa diekspor ke Excel kapan pun</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Cycle Segmented Switcher -->
                <div class="mt-10 sm:mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <div
                        class="inline-flex p-1 rounded-[16px] bg-black/[0.06] dark:bg-white/[0.08] border border-black/[0.04] dark:border-white/[0.06]">
                        <button type="button" @click="pricingCycle = 'monthly'; refreshIcons()"
                            :class="pricingCycle === 'monthly' ?
                                'bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white shadow-sm font-bold' :
                                'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white'"
                            class="px-6 py-2.5 rounded-[12px] text-xs sm:text-sm transition-all cursor-pointer min-h-[44px] flex items-center justify-center">
                            Langganan Bulanan
                        </button>
                        <button type="button" @click="pricingCycle = 'annual'; refreshIcons()"
                            :class="pricingCycle === 'annual' ?
                                'bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white shadow-sm font-bold' :
                                'text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white'"
                            class="px-6 py-2.5 rounded-[12px] text-xs sm:text-sm transition-all cursor-pointer min-h-[44px] flex items-center justify-center gap-1.5">
                            <span>Langganan Tahunan</span>
                            <span
                                class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">Hemat
                                20%</span>
                        </button>
                    </div>

                    <!-- Comparison Modal Trigger Button -->
                    <button type="button" @click="openComparison()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-black/[0.02] dark:hover:bg-white/[0.04] transition active:scale-[0.98] min-h-[44px]">
                        <i data-lucide="columns-3" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                        <span>Bandingkan Semua Fitur</span>
                    </button>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. 4 BENTO PRICING CARDS SECTION ═════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-12 sm:py-16">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 items-stretch">

                    <!-- 1. FREE PLAN CARD -->
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-7 flex flex-col justify-between space-y-6 shadow-xs hover:border-black/20 dark:hover:border-white/20 transition-all">
                        <div class="space-y-4">
                            <div>
                                <div
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    Mulai Usaha</div>
                                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">Gratis</h2>
                                <p
                                    class="text-xs text-slate-600 dark:text-slate-400 mt-1 min-h-[34px] leading-relaxed text-pretty">
                                    Cocok untuk toko kelontong, warung, atau usaha rumahan yang baru merintis.
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="pt-3 pb-1 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tabular-nums tracking-tight">0</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/ selamanya</span>
                                </div>
                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">
                                    Tanpa syarat kartu kredit
                                </div>
                            </div>

                            <!-- Core Highlights for 40-65 y.o. -->
                            <div class="space-y-2.5 pt-2">
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug"><strong>1 Toko &amp; 1 Pengguna</strong> (Owner/Kasir)</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Maksimal 50 Produk &amp; 20 Bahan Baku</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">100 Transaksi Kasir POS per bulan</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Cetak Struk &amp; Nota Penjualan</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Penyimpanan Cloud Aman 3 GB</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <a href="{{ route('register') }}"
                                class="w-full py-3 px-4 rounded-[14px] bg-slate-100 dark:bg-white/[0.08] hover:bg-slate-200 dark:hover:bg-white/[0.12] text-slate-900 dark:text-white font-bold text-xs sm:text-sm text-center transition active:scale-[0.98] flex items-center justify-center min-h-[48px]">
                                Mulai Gratis Sekarang
                            </a>

                            <button type="button" @click="openDetail('free')"
                                class="w-full py-2 text-center text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition">
                                Lihat Rincian Fitur
                            </button>
                        </div>
                    </div>

                    <!-- 2. STANDARD PLAN CARD (HIGHLIGHTED / POPULER) -->
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border-2 border-[#007AFF] dark:border-[#0A84FF] p-6 sm:p-7 flex flex-col justify-between space-y-6 shadow-md relative hover:shadow-xl transition-all">

                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div
                                        class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                        Toko Berkembang</div>
                                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">Standard</h2>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-[#007AFF] text-white text-[11px] font-bold shrink-0">
                                    Paling Populer
                                </span>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 min-h-[34px] leading-relaxed text-pretty">
                                Pilihan utama toko kelontong modern, bengkel, laundry, dan kafe yang memiliki kasir.
                            </p>

                            <!-- Price -->
                            <div class="pt-3 pb-1 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tabular-nums tracking-tight"
                                        x-text="pricingCycle === 'annual' ? '39.000' : '49.000'">49.000</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/ bulan</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span x-show="pricingCycle === 'annual'">Ditagih Rp 468.000 per tahun (hemat Rp
                                        120.000)</span>
                                    <span x-show="pricingCycle === 'monthly'">Bayar bulanan tanpa komitmen jangka
                                        panjang</span>
                                </div>
                            </div>

                            <div class="text-[11px] font-bold text-slate-900 dark:text-white pt-1">
                                Semua fitur Gratis, ditambah:
                            </div>

                            <!-- Core Highlights -->
                            <div class="space-y-2.5">
                                <div class="flex items-start gap-2.5 text-xs text-slate-800 dark:text-slate-200">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                    <span class="leading-snug"><strong>Banyak Kasir &amp; Staf</strong> dengan pembatasan
                                        hak akses aman</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-800 dark:text-slate-200">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Hingga 3 Cabang Toko &amp; Gudang Terpisah</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-800 dark:text-slate-200">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                    <span class="leading-snug"><strong>Transaksi Kasir Tanpa Batas</strong> (bebas
                                        kuota)</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-800 dark:text-slate-200">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Kirim Nota Otomatis ke WhatsApp Pelanggan</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-800 dark:text-slate-200">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Laporan Penjualan &amp; Laba Bersih Otomatis</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <a href="{{ route('register') }}"
                                class="w-full py-3 px-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0066D6] text-white font-bold text-xs sm:text-sm text-center transition shadow-sm active:scale-[0.98] flex items-center justify-center min-h-[48px]">
                                Pilih Paket Standard
                            </a>

                            <button type="button" @click="openDetail('standard')"
                                class="w-full py-2 text-center text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition">
                                Lihat Rincian Fitur
                            </button>
                        </div>
                    </div>

                    <!-- 3. PREMIUM PLAN CARD -->
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-7 flex flex-col justify-between space-y-6 shadow-xs hover:border-black/20 dark:hover:border-white/20 transition-all">
                        <div class="space-y-4">
                            <div>
                                <div
                                    class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                                    Jualan Online &amp; Offline</div>
                                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">Premium</h2>
                                <p
                                    class="text-xs text-slate-600 dark:text-slate-400 mt-1 min-h-[34px] leading-relaxed text-pretty">
                                    Untuk bisnis yang aktif berjualan di marketplace (Shopee, TikTok) dan media sosial.
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="pt-3 pb-1 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tabular-nums tracking-tight"
                                        x-text="pricingCycle === 'annual' ? '79.000' : '99.000'">99.000</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/ bulan</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span x-show="pricingCycle === 'annual'">Ditagih Rp 948.000 per tahun (hemat Rp
                                        240.000)</span>
                                    <span x-show="pricingCycle === 'monthly'">Bayar bulanan tanpa komitmen jangka
                                        panjang</span>
                                </div>
                            </div>

                            <div class="text-[11px] font-bold text-slate-900 dark:text-white pt-1">
                                Semua fitur Standard, ditambah:
                            </div>

                            <!-- Core Highlights -->
                            <div class="space-y-2.5">
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug"><strong>Sinkronisasi Stok Otomatis</strong> ke Shopee &amp;
                                        TikTok Shop</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Otomasi Jadwal Posting Konten ke Media Sosial</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Manajemen Pelanggan Setia &amp; Program Poin Belanja</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Integrasi Webhook &amp; Notifikasi Pesanan</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Bantuan Teknis Prioritas via WhatsApp</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <a href="{{ route('register') }}"
                                class="w-full py-3 px-4 rounded-[14px] bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-200 text-white dark:text-slate-900 font-bold text-xs sm:text-sm text-center transition active:scale-[0.98] flex items-center justify-center min-h-[48px]">
                                Pilih Paket Premium
                            </a>

                            <button type="button" @click="openDetail('premium')"
                                class="w-full py-2 text-center text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition">
                                Lihat Rincian Fitur
                            </button>
                        </div>
                    </div>

                    <!-- 4. PRESTIGE PLAN CARD -->
                    <div
                        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-7 flex flex-col justify-between space-y-6 shadow-xs hover:border-black/20 dark:hover:border-white/20 transition-all">
                        <div class="space-y-4">
                            <div>
                                <div
                                    class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                    Skala Besar &amp; AI</div>
                                <h2
                                    class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5 flex items-center gap-1.5">
                                    <span>Prestige</span>
                                </h2>
                                <p
                                    class="text-xs text-slate-600 dark:text-slate-400 mt-1 min-h-[34px] leading-relaxed text-pretty">
                                    Untuk usaha pabrikasi/kuliner beresep, distributor, dan multi-perusahaan.
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="pt-3 pb-1 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rp</span>
                                    <span
                                        class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tabular-nums tracking-tight"
                                        x-text="pricingCycle === 'annual' ? '159.000' : '199.000'">199.000</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">/ bulan</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span x-show="pricingCycle === 'annual'">Ditagih Rp 1.908.000 per tahun (hemat Rp
                                        480.000)</span>
                                    <span x-show="pricingCycle === 'monthly'">Bayar bulanan tanpa komitmen jangka
                                        panjang</span>
                                </div>
                            </div>

                            <div class="text-[11px] font-bold text-slate-900 dark:text-white pt-1">
                                Semua fitur Premium, ditambah:
                            </div>

                            <!-- Core Highlights -->
                            <div class="space-y-2.5">
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug"><strong>Asisten Bisnis AI Cerdas</strong> (Tanya laba, tren
                                        &amp; stok lewat chat)</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">AI Pembuat Teks Iklan Promosi &amp; Deskripsi Produk</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug"><strong>Resep Bahan Baku Produksi (BOM)</strong> &amp;
                                        Potong Stok Otomatis</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Multi-Perusahaan &amp; Konsolidasi Neraca Keuangan</span>
                                </div>
                                <div class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                    <i data-lucide="check"
                                        class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                                    <span class="leading-snug">Pendamping Khusus Pribadi &amp; Bantuan VIP 24 Jam</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <a href="{{ route('register') }}"
                                class="w-full py-3 px-4 rounded-[14px] bg-slate-100 dark:bg-white/[0.08] hover:bg-slate-200 dark:hover:bg-white/[0.12] text-slate-900 dark:text-white font-bold text-xs sm:text-sm text-center transition active:scale-[0.98] flex items-center justify-center min-h-[48px]">
                                Pilih Paket Prestige
                            </a>

                            <button type="button" @click="openDetail('prestige')"
                                class="w-full py-2 text-center text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition">
                                Lihat Rincian Fitur
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. INTERACTIVE BENTO TOOL: KALKULATOR KEBUTUHAN PAKET UMKM ══════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-8 sm:py-12">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div
                    class="p-6 sm:p-8 lg:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

                        <!-- Left Questions Column -->
                        <div class="lg:col-span-7 space-y-6">
                            <div>
                                <div
                                    class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                    PANDUAN PEMILIHAN
                                </div>
                                <h3
                                    class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                                    Bingung Memilih Paket? Jawab 3 Pertanyaan Ini
                                </h3>
                                <p
                                    class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1 leading-relaxed text-pretty">
                                    Kami merekomendasikan paket yang paling hemat dan sesuai dengan kebutuhan operasional
                                    harian toko Anda.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <!-- Q1: Jumlah Cabang -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">
                                        1. Berapa lokasi usaha atau cabang yang Anda miliki saat ini?
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button type="button" @click="calcOutlets = '1'; refreshIcons()"
                                            :class="calcOutlets === '1' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-3 rounded-[14px] border text-xs sm:text-sm text-center transition min-h-[48px] flex items-center justify-center">
                                            1 Lokasi Toko
                                        </button>
                                        <button type="button" @click="calcOutlets = '2_3'; refreshIcons()"
                                            :class="calcOutlets === '2_3' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-3 rounded-[14px] border text-xs sm:text-sm text-center transition min-h-[48px] flex items-center justify-center">
                                            2 – 3 Cabang
                                        </button>
                                        <button type="button" @click="calcOutlets = 'multi'; refreshIcons()"
                                            :class="calcOutlets === 'multi' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-3 rounded-[14px] border text-xs sm:text-sm text-center transition min-h-[48px] flex items-center justify-center">
                                            Lebih dari 3 Cabang
                                        </button>
                                    </div>
                                </div>

                                <!-- Q2: Pengguna / Kasir -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">
                                        2. Siapa yang mengoperasikan kasir dan pembukuan toko?
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" @click="calcTeam = 'solo'; refreshIcons()"
                                            :class="calcTeam === 'solo' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-3 rounded-[14px] border text-xs sm:text-sm text-center transition min-h-[48px] flex items-center justify-center">
                                            Dikelola Sendiri (Owner Tunggal)
                                        </button>
                                        <button type="button" @click="calcTeam = 'team'; refreshIcons()"
                                            :class="calcTeam === 'team' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-3 rounded-[14px] border text-xs sm:text-sm text-center transition min-h-[48px] flex items-center justify-center">
                                            Ada Kasir, Staf Gudang, atau Admin
                                        </button>
                                    </div>
                                </div>

                                <!-- Q3: Kebutuhan Kunci -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">
                                        3. Apa fitur yang paling mendesak dibutuhkan saat ini?
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <button type="button" @click="calcNeed = 'pos_nota'; refreshIcons()"
                                            :class="calcNeed === 'pos_nota' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-2.5 rounded-[14px] border text-xs text-center transition min-h-[48px] flex items-center justify-center leading-tight">
                                            Cetak Struk &amp; Nota Cepat
                                        </button>
                                        <button type="button" @click="calcNeed = 'stock_hpp'; refreshIcons()"
                                            :class="calcNeed === 'stock_hpp' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-2.5 rounded-[14px] border text-xs text-center transition min-h-[48px] flex items-center justify-center leading-tight">
                                            Kontrol Stok &amp; Modal HPP
                                        </button>
                                        <button type="button" @click="calcNeed = 'omnichannel'; refreshIcons()"
                                            :class="calcNeed === 'omnichannel' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-2.5 rounded-[14px] border text-xs text-center transition min-h-[48px] flex items-center justify-center leading-tight">
                                            Sinkron Stok Marketplace
                                        </button>
                                        <button type="button" @click="calcNeed = 'ai_buss'; refreshIcons()"
                                            :class="calcNeed === 'ai_buss' ?
                                                'border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold' :
                                                'border-black/[0.08] dark:border-white/[0.1] text-slate-700 dark:text-slate-300 hover:bg-black/[0.02] dark:hover:bg-white/[0.04]'"
                                            class="p-2.5 rounded-[14px] border text-xs text-center transition min-h-[48px] flex items-center justify-center leading-tight">
                                            Analisa Laba Cerdas &amp; AI
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Recommendation Result Card -->
                        <div
                            class="lg:col-span-5 p-6 sm:p-7 rounded-[20px] bg-slate-50 dark:bg-[#252528] border border-black/[0.06] dark:border-white/[0.08] space-y-5">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                HASIL REKOMENDASI UNTUK ANDA
                            </div>

                            <!-- Case Free -->
                            <div x-show="recommendedPlan === 'free'" class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-[14px] bg-slate-200 dark:bg-white/10 text-slate-800 dark:text-white flex items-center justify-center font-bold text-lg">
                                        <i data-lucide="store" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">Paket Gratis</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Rp 0 / selamanya</div>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    Sangat cocok untuk memulai. Anda sudah bisa mencatat kasir, mengelola hingga 50 produk,
                                    dan mencetak nota tanpa keluar biaya sepeser pun.
                                </p>
                            </div>

                            <!-- Case Standard -->
                            <div x-show="recommendedPlan === 'standard'" class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-[14px] bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold text-lg">
                                        <i data-lucide="zap" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">Paket Standard</div>
                                        <div class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold">Mulai Rp
                                            39.000 / bulan</div>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    Rekomendasi terbaik untuk toko Anda. Memberikan akses multi-user untuk staf kasir dengan
                                    hak akses terjaga, kuota transaksi tanpa batas, dan nota WhatsApp otomatis.
                                </p>
                            </div>

                            <!-- Case Premium -->
                            <div x-show="recommendedPlan === 'premium'" class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-[14px] bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-lg">
                                        <i data-lucide="share-2" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">Paket Premium</div>
                                        <div class="text-xs text-purple-600 dark:text-purple-400 font-semibold">Mulai Rp
                                            79.000 / bulan</div>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    Tepat karena Anda mengelola banyak cabang atau berjualan di Shopee/TikTok Shop, sehingga
                                    stok toko fisik dan online tidak akan pernah selisih lagi.
                                </p>
                            </div>

                            <!-- Case Prestige -->
                            <div x-show="recommendedPlan === 'prestige'" class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-[14px] bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                                        <i data-lucide="crown" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">Paket Prestige</div>
                                        <div class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Mulai Rp
                                            159.000 / bulan</div>
                                    </div>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    Dirancang untuk bisnis skala matang dengan banyak cabang, proses produksi bahan baku
                                    bertingkat (BOM), dan asisten AI pintar yang siap menganalisa data usaha Anda 24 jam.
                                </p>
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('register') }}"
                                    class="w-full py-3 px-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0066D6] text-white font-bold text-xs sm:text-sm text-center transition shadow-sm active:scale-[0.98] flex items-center justify-center min-h-[48px]">
                                    Daftar dengan Paket Ini
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. ADD-ON & LAYANAN TAMBAHAN BERSIFAT OPSIONAL ═══════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-8 sm:py-12">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <div>
                    <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        LAYANAN TAMBAHAN SESUAI KEBUTUHAN
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                        Add-On Fleksibel Tanpa Memaksa Beli Paket Mahal
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                        Jika kuota paket bawaan Anda telah mencukupi, Anda tidak perlu menambah apa pun. Add-on ini hanya
                        diaktifkan jika toko Anda memerlukan kapasitas ekstra.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    <!-- 1. Storage Cloud Tambahan -->
                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div
                                class="w-11 h-11 rounded-[12px] bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                <i data-lucide="cloud" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">Kapasitas Cloud Ekstra</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed text-pretty">
                                    Untuk toko dengan puluhan ribu foto produk beresolusi tinggi dan arsip bukti transaksi
                                    nota digital.
                                </p>
                            </div>
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white tabular-nums">
                                Rp 10.000 <span class="text-xs font-normal text-slate-500 dark:text-slate-400">/ 1 GB per
                                    bulan</span>
                            </div>
                        </div>

                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            Dapat diaktifkan langsung di dalam akun toko Anda
                        </div>
                    </div>

                    <!-- 2. Kuota AI Assistant Token -->
                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div
                                class="w-11 h-11 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i data-lucide="cpu" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">Token AI Assistant Ekstra
                                </h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed text-pretty">
                                    Untuk konsultasi data keuangan otomatis, pembuatan teks promosi medsos, dan analisa
                                    pergerakan stok barang.
                                </p>
                            </div>
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white tabular-nums">
                                Rp 20.000 <span class="text-xs font-normal text-slate-500 dark:text-slate-400">/ 1 juta
                                    token</span>
                            </div>
                        </div>

                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            Dapat diisi ulang kapan saja sesuai pemakaian
                        </div>
                    </div>

                    <!-- 3. Hardware Thermal & Scanner Support -->
                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div
                                class="w-11 h-11 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i data-lucide="printer" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">Dukungan Mesin Kasir &amp;
                                    Printer</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed text-pretty">
                                    Kompatibel dengan printer thermal bluetooth 58mm/80mm, laci uang kasir (cash drawer),
                                    dan barcode scanner.
                                </p>
                            </div>
                            <div class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">
                                Gratis <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Tersedia di
                                    semua paket)</span>
                            </div>
                        </div>

                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                            Tanpa perlu membeli perangkat khusus bermerek mahal
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. PROMO KHUSUS PENDAFTARAN BARU ═════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-6 sm:py-8">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div
                    class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xs">
                    <div class="flex items-center gap-4 sm:gap-6">
                        <div
                            class="w-14 h-14 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                            <i data-lucide="gift" class="w-7 h-7"></i>
                        </div>
                        <div class="space-y-1">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                KESEMPATAN KHUSUS
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                                Coba 1 Bulan Gratis Paket Berbayar Tanpa Risiko
                            </h3>
                            <p
                                class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed text-pretty">
                                Setiap pemilik toko yang mendaftar baru mendapatkan kesempatan mencoba seluruh fitur
                                Standard tanpa biaya, agar dapat membuktikan kemudahan operasionalnya sendiri.
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 w-full sm:w-auto">
                        <a href="{{ route('register') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-[14px] bg-[#007AFF] hover:bg-[#0066D6] text-white font-bold text-xs sm:text-sm transition active:scale-[0.98] min-h-[48px]">
                            <span>Daftar Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. FAQ RAMAH UMKM USIA 40-65 TAHUN ═══════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-12 sm:py-16" id="faq">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="text-[12px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            JAWABAN PERTANYAAN
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                            Pertanyaan yang Paling Sering Diajukan Pemilik Toko
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                            Jawaban jujur seputar pengoperasian harian, keamanan data, dan bantuan teknis.
                        </p>
                    </div>

                    <a href="{{ route('public.resources.faq') }}"
                        class="text-xs sm:text-sm font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1 transition-colors self-start sm:self-auto shrink-0">
                        <span>Lihat Semua FAQ</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                    </a>
                </div>

                <!-- 2-Column Clean Apple Accordion Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Left Column -->
                    <div class="space-y-3">
                        <!-- FAQ 1 -->
                        <div
                            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <button type="button" @click="toggleFaq(1)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer min-h-[48px]">
                                <span class="min-w-0 flex-1 leading-snug">Apakah saya harus membeli mesin kasir atau
                                    komputer baru?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 1 ? 'rotate-180 text-[#007AFF]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 1" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] mt-1">
                                Sama sekali tidak perlu. COOCA dapat dibuka langsung dari browser HP Android, iPhone,
                                tablet, maupun laptop atau komputer lama yang sudah Anda miliki di toko.
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div
                            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <button type="button" @click="toggleFaq(2)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer min-h-[48px]">
                                <span class="min-w-0 flex-1 leading-snug">Saya kurang paham teknologi (gaptek), apakah ada
                                    yang mengajari?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 2 ? 'rotate-180 text-[#007AFF]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 2" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] mt-1">
                                Tentu saja. Tampilan COOCA dibuat sangat sederhana dengan tulisan besar dan tombol jelas.
                                Tim pendamping kami di WhatsApp siap memandu Anda langkah demi langkah sampai toko Anda siap
                                beroperasi.
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div
                            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <button type="button" @click="toggleFaq(3)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer min-h-[48px]">
                                <span class="min-w-0 flex-1 leading-snug">Bagaimana jika koneksi internet di toko saya
                                    sedang lambat atau mati?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 3 ? 'rotate-180 text-[#007AFF]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 3" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] mt-1">
                                Kasir POS COOCA dirancang dengan sistem perlindungan offline. Anda tetap bisa melayani
                                antrean pembeli dan mencetak struk. Begitu internet terhubung kembali, seluruh nota otomatis
                                tersinkronisasi.
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-3">
                        <!-- FAQ 4 -->
                        <div
                            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <button type="button" @click="toggleFaq(4)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer min-h-[48px]">
                                <span class="min-w-0 flex-1 leading-snug">Apakah data penjualan dan keuangan saya aman dari
                                    orang lain?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 4 ? 'rotate-180 text-[#007AFF]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 4" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] mt-1">
                                Sangat aman. Seluruh data transaksi toko Anda dienkripsi secara privat dan diisolasi khusus
                                untuk bisnis Anda, tidak bisa diintip toko lain, serta dicadangkan (backup) otomatis setiap
                                hari.
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div
                            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <button type="button" @click="toggleFaq(5)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer min-h-[48px]">
                                <span class="min-w-0 flex-1 leading-snug">Apakah ada potongan biaya per transaksi atau per
                                    struk cetak?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 5 ? 'rotate-180 text-[#007AFF]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 5" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] mt-1">
                                Nol rupiah. COOCA tidak mengenakan potongan komisi per struk penjualan Anda. Keuntungan
                                hasil penjualan toko adalah 100% hak milik Anda.
                            </div>
                        </div>

                        <!-- FAQ 6 -->
                        <div
                            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <button type="button" @click="toggleFaq(6)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer min-h-[48px]">
                                <span class="min-w-0 flex-1 leading-snug">Apakah saya bisa berpindah paket kapan
                                    saja?</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform shrink-0"
                                    :class="openFaq === 6 ? 'rotate-180 text-[#007AFF]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 6" x-collapse x-cloak
                                class="px-4 pb-4 sm:px-5 sm:pb-5 pt-0 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] mt-1">
                                Bisa sewaktu-waktu. Anda dapat memulai dari Paket Gratis, lalu beralih ke Paket Standard
                                saat kasir bertambah. Seluruh riwayat penjualan masa lalu Anda tetap tersimpan utuh.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 7. WHATSAPP CONSULTATION BANNER (EMPATHIC HUMAN REASSURANCE) ═════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-6 sm:py-10">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <div
                    class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="space-y-3 max-w-2xl text-center lg:text-left">
                        <div class="text-[12px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                            KONSULTASI GRATIS TANPA KEWAJIBAN MEMBELI
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Masih Ragu atau Butuh Penjelasan Lebih Lanjut?
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed text-pretty">
                            Bapak dan Ibu dapat langsung berkonsultasi santai dengan tim kami via WhatsApp. Kami siap
                            mendengarkan alur jualan toko Anda dan memberikan saran yang paling pas tanpa memaksakan paket
                            apa pun.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya pemilik usaha ingin berkonsultasi mengenai paket yang pas untuk toko saya.') }}"
                            target="_blank" rel="noopener"
                            class="px-6 py-3.5 rounded-[14px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm transition active:scale-[0.98] flex items-center justify-center gap-2 min-h-[48px]">
                            <i data-lucide="message-circle" class="w-5 h-5 shrink-0"></i>
                            <span>Chat WhatsApp Tim Kami</span>
                        </a>

                        <a href="{{ route('register') }}"
                            class="px-6 py-3.5 rounded-[14px] bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-900 dark:text-white font-semibold text-xs sm:text-sm transition active:scale-[0.98] flex items-center justify-center min-h-[48px]">
                            Coba Paket Gratis Sendiri
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 8. MODAL 1: TABEL PERBANDINGAN FITUR LENGKAP (FULL LAYOUT XXL) ═══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <div x-show="showComparisonModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
            @keydown.escape.window="closeComparison()">

            <!-- Backdrop -->
            <div x-show="showComparisonModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-md"
                @click="closeComparison()"></div>

            <!-- Modal Content (Full Layout XXL Centered Bento Dialog) -->
            <div x-show="showComparisonModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative w-full max-w-5xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-hidden flex flex-col max-h-[92vh] z-10">

                <!-- Mobile Grab Handle -->
                <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Header -->
                <div
                    class="p-5 sm:p-6 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4 shrink-0 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            MATRIKS PERBANDINGAN LENGKAP
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">
                            Bandingkan Semua Fitur Paket COOCA
                        </h3>
                    </div>

                    <button type="button" @click="closeComparison()"
                        class="w-10 h-10 rounded-full bg-slate-100 dark:bg-white/[0.08] hover:bg-slate-200 dark:hover:bg-white/[0.12] text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer shrink-0">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Scrollable Body with Comparison Matrix -->
                <div class="p-5 sm:p-8 overflow-y-auto space-y-8">

                    <!-- Pricing Summary Header Grid inside Modal -->
                    <div
                        class="grid grid-cols-4 gap-2 sm:gap-4 text-center border-b border-black/[0.06] dark:border-white/[0.08] pb-6">
                        <div class="p-3 rounded-[16px] bg-slate-50 dark:bg-white/[0.03]">
                            <div class="text-xs font-bold text-slate-500">Gratis</div>
                            <div
                                class="text-base sm:text-xl font-extrabold text-slate-900 dark:text-white mt-0.5 tabular-nums">
                                Rp 0</div>
                        </div>
                        <div class="p-3 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/30">
                            <div class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">Standard</div>
                            <div
                                class="text-base sm:text-xl font-extrabold text-slate-900 dark:text-white mt-0.5 tabular-nums">
                                <span x-text="pricingCycle === 'annual' ? 'Rp 39rb' : 'Rp 49rb'">Rp 49rb</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-[16px] bg-purple-500/10 border border-purple-500/20">
                            <div class="text-xs font-bold text-purple-600 dark:text-purple-400">Premium</div>
                            <div
                                class="text-base sm:text-xl font-extrabold text-slate-900 dark:text-white mt-0.5 tabular-nums">
                                <span x-text="pricingCycle === 'annual' ? 'Rp 79rb' : 'Rp 99rb'">Rp 99rb</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-[16px] bg-amber-500/10 border border-amber-500/20">
                            <div class="text-xs font-bold text-amber-600 dark:text-amber-400">Prestige</div>
                            <div
                                class="text-base sm:text-xl font-extrabold text-slate-900 dark:text-white mt-0.5 tabular-nums">
                                <span x-text="pricingCycle === 'annual' ? 'Rp 159rb' : 'Rp 199rb'">Rp 199rb</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kategori 1: Kapasitas & Kuota Dasar -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            1. Kapasitas &amp; Batas Kuota Toko
                        </h4>
                        <div class="rounded-[16px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Jumlah Toko
                                            / Cabang</td>
                                        <td class="p-3 sm:p-4 text-center">1 Toko</td>
                                        <td class="p-3 sm:p-4 text-center font-bold text-[#007AFF]">Hingga 3 Cabang</td>
                                        <td class="p-3 sm:p-4 text-center">Hingga 10 Cabang</td>
                                        <td class="p-3 sm:p-4 text-center font-bold">Tanpa Batas</td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Pengguna /
                                            Kasir Terdaftar</td>
                                        <td class="p-3 sm:p-4 text-center">1 Pengguna</td>
                                        <td class="p-3 sm:p-4 text-center font-bold text-[#007AFF]">Multi-User (3 Kasir)
                                        </td>
                                        <td class="p-3 sm:p-4 text-center">Multi-User (10 Kasir)</td>
                                        <td class="p-3 sm:p-4 text-center font-bold">Tanpa Batas</td>
                                    </tr>
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Kapasitas
                                            Produk / Barang</td>
                                        <td class="p-3 sm:p-4 text-center">50 Produk</td>
                                        <td class="p-3 sm:p-4 text-center font-bold text-[#007AFF]">Tanpa Batas</td>
                                        <td class="p-3 sm:p-4 text-center">Tanpa Batas</td>
                                        <td class="p-3 sm:p-4 text-center font-bold">Tanpa Batas</td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Batas
                                            Transaksi Kasir / bln</td>
                                        <td class="p-3 sm:p-4 text-center">100 Transaksi</td>
                                        <td class="p-3 sm:p-4 text-center font-bold text-[#007AFF]">Tanpa Batas</td>
                                        <td class="p-3 sm:p-4 text-center">Tanpa Batas</td>
                                        <td class="p-3 sm:p-4 text-center font-bold">Tanpa Batas</td>
                                    </tr>
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Penyimpanan
                                            Cloud Storage</td>
                                        <td class="p-3 sm:p-4 text-center">3 GB</td>
                                        <td class="p-3 sm:p-4 text-center">10 GB</td>
                                        <td class="p-3 sm:p-4 text-center">25 GB</td>
                                        <td class="p-3 sm:p-4 text-center font-bold">100 GB</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kategori 2: Kasir, Penjualan & Nota -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            2. Fitur Kasir &amp; Nota Penjualan
                        </h4>
                        <div class="rounded-[16px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Cetak Struk
                                            Thermal (Bluetooth/USB)</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Kirim Nota
                                            Otomatis ke WhatsApp Pembeli</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Dukungan
                                            Penjualan Grosir (Tingkat Harga)</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">
                                            Perlindungan Kasir (Supervisor PIN &amp; Anti Void)</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kategori 3: Manajemen Inventori & Produksi -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            3. Inventori, Stok &amp; Bahan Baku
                        </h4>
                        <div class="rounded-[16px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Peringatan
                                            Stok Menipis &amp; Habis</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600"><i data-lucide="check"
                                                class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Transfer
                                            Stok Antar Cabang / Gudang</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Resep Bahan
                                            Baku Kuliner / Pabrik (BOM)</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kategori 4: Marketplace, AI & Layanan -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            4. Integrasi Marketplace, AI &amp; Pendampingan
                        </h4>
                        <div class="rounded-[16px] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                            <table class="w-full text-left text-xs sm:text-sm">
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">
                                            Sinkronisasi Stok Shopee &amp; TikTok Shop</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr>
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Asisten
                                            Bisnis Cerdas AI</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-300 dark:text-slate-600">—</td>
                                        <td class="p-3 sm:p-4 text-center text-emerald-600 font-bold"><i
                                                data-lucide="check" class="w-4 h-4 mx-auto"></i></td>
                                    </tr>
                                    <tr class="bg-black/[0.01] dark:bg-white/[0.01]">
                                        <td class="p-3 sm:p-4 font-semibold text-slate-800 dark:text-slate-200">Jalur
                                            Bantuan &amp; Konsultasi</td>
                                        <td class="p-3 sm:p-4 text-center text-slate-600 dark:text-slate-300">Pusat Bantuan
                                            &amp; Panduan</td>
                                        <td class="p-3 sm:p-4 text-center font-bold text-[#007AFF]">Chat &amp; WhatsApp
                                            Resmi</td>
                                        <td class="p-3 sm:p-4 text-center font-bold">WhatsApp Prioritas Cepat</td>
                                        <td class="p-3 sm:p-4 text-center font-bold text-amber-600">VIP Pendamping Pribadi
                                            24 Jam</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Sticky Footer Action -->
                <div
                    class="p-4 sm:p-6 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Seluruh paket bebas biaya setup dan dapat di-upgrade kapan saja.
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" @click="closeComparison()"
                            class="px-5 py-2.5 rounded-[12px] bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-800 dark:text-slate-200 font-semibold text-xs sm:text-sm min-h-[44px]">
                            Tutup
                        </button>
                        <a href="{{ route('register') }}"
                            class="flex-1 sm:flex-initial px-6 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066D6] text-white font-bold text-xs sm:text-sm min-h-[44px] flex items-center justify-center">
                            Mulai Sekarang
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 9. MODAL 2: DETAIL RINCIAN PER PAKET (MODAL-FIRST SHEET) ═════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <div x-show="activeDetailModal !== null" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6" @keydown.escape.window="closeDetail()">

            <!-- Backdrop -->
            <div x-show="activeDetailModal !== null" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-md"
                @click="closeDetail()"></div>

            <!-- Modal Content (Centered Dialog) -->
            <div x-show="activeDetailModal !== null" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative w-full max-w-xl bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] z-10">

                <!-- Mobile Grab Handle -->
                <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Header -->
                <div
                    class="p-5 sm:p-6 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4 shrink-0">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            RINCIAN SPESIFIKASI
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white capitalize mt-0.5">
                            Paket <span x-text="activeDetailModal"></span>
                        </h3>
                    </div>

                    <button type="button" @click="closeDetail()"
                        class="w-9 h-9 rounded-full bg-slate-100 dark:bg-white/[0.08] text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Scrollable Body with Details -->
                <div
                    class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">

                    <!-- Detail: FREE -->
                    <template x-if="activeDetailModal === 'free'">
                        <div class="space-y-4">
                            <p class="text-slate-600 dark:text-slate-300">
                                Paket Gratis dirancang agar siapa saja dapat mendigitalkan warung atau usahanya tanpa rasa
                                takut keluar biaya. Fitur dasar kasir dan struk sudah sangat lengkap untuk kebutuhan
                                operasional 1 toko.
                            </p>
                            <div class="p-4 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] space-y-2">
                                <div class="font-bold text-slate-900 dark:text-white">Yang Anda Dapatkan:</div>
                                <ul class="space-y-1.5 list-disc list-inside">
                                    <li>1 User Akun Pemilik / Kasir</li>
                                    <li>Maksimal 50 Produk barang atau jasa</li>
                                    <li>100 transaksi kasir POS per bulan</li>
                                    <li>Cetak struk thermal bluetooth langsung dari HP</li>
                                    <li>Buku kas dan laporan omset harian otomatis</li>
                                    <li>Penyimpanan data cloud aman 3 GB</li>
                                </ul>
                            </div>
                        </div>
                    </template>

                    <!-- Detail: STANDARD -->
                    <template x-if="activeDetailModal === 'standard'">
                        <div class="space-y-4">
                            <p class="text-slate-600 dark:text-slate-300">
                                Paket Standard adalah pilihan paling ideal untuk toko kelontong modern, bengkel, kafe, atau
                                barbershop yang memiliki karyawan kasir. Kuota transaksi dibuka tanpa batas sehingga tidak
                                ada kekhawatiran antrean tertolak.
                            </p>
                            <div
                                class="p-4 rounded-[16px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-500/20 space-y-2">
                                <div class="font-bold text-slate-900 dark:text-white">Yang Anda Dapatkan:</div>
                                <ul class="space-y-1.5 list-disc list-inside">
                                    <li>Hingga 3 Cabang Toko &amp; Gudang terpisah</li>
                                    <li>Multi-User kasir dengan PIN supervisor (mencegah kecurangan)</li>
                                    <li>Produk &amp; Transaksi Kasir Tanpa Batas Kuota</li>
                                    <li>Kirim nota PDF otomatis ke WhatsApp pelanggan</li>
                                    <li>Tingkat harga grosir dan eceran otomatis</li>
                                    <li>Laporan laba kotor &amp; bersih real-time</li>
                                    <li>Bantuan teknis via WhatsApp resmi</li>
                                </ul>
                            </div>
                        </div>
                    </template>

                    <!-- Detail: PREMIUM -->
                    <template x-if="activeDetailModal === 'premium'">
                        <div class="space-y-4">
                            <p class="text-slate-600 dark:text-slate-300">
                                Paket Premium menjembatani jualan offline di toko dengan penjualan online di marketplace.
                                Stok gudang otomatis berkurang saat ada pesanan di Shopee atau TikTok Shop.
                            </p>
                            <div
                                class="p-4 rounded-[16px] bg-purple-50/50 dark:bg-purple-950/20 border border-purple-500/20 space-y-2">
                                <div class="font-bold text-slate-900 dark:text-white">Yang Anda Dapatkan:</div>
                                <ul class="space-y-1.5 list-disc list-inside">
                                    <li>Seluruh fitur Paket Standard</li>
                                    <li>Sinkronisasi inventori marketplace otomatis (Shopee &amp; TikTok)</li>
                                    <li>Otomasi jadwal tayang konten katalog di media sosial</li>
                                    <li>Integrasi Webhook pesanan &amp; notifikasi instan</li>
                                    <li>Cloud storage 25 GB untuk arsip file bisnis</li>
                                    <li>Prioritas antrean bantuan teknis 7 hari seminggu</li>
                                </ul>
                            </div>
                        </div>
                    </template>

                    <!-- Detail: PRESTIGE -->
                    <template x-if="activeDetailModal === 'prestige'">
                        <div class="space-y-4">
                            <p class="text-slate-600 dark:text-slate-300">
                                Paket Prestige menghadirkan kecerdasan buatan (AI) yang bertindak seperti konsultan pribadi
                                Anda. Sangat kuat untuk bisnis F&amp;B yang memerlukan resep bahan baku (BOM) atau
                                distributor multi-perusahaan.
                            </p>
                            <div
                                class="p-4 rounded-[16px] bg-amber-50/50 dark:bg-amber-950/20 border border-amber-500/20 space-y-2">
                                <div class="font-bold text-slate-900 dark:text-white">Yang Anda Dapatkan:</div>
                                <ul class="space-y-1.5 list-disc list-inside">
                                    <li>Seluruh fitur Paket Premium</li>
                                    <li>Asisten Bisnis Cerdas AI (Tanya jawab omset, tren &amp; analisa laba)</li>
                                    <li>AI Pembuat Konten Promosi &amp; Deskripsi Produk</li>
                                    <li>Modul Resep Bahan Baku (BOM) &amp; Potong Stok Otomatis</li>
                                    <li>Multi-Perusahaan &amp; Konsolidasi Laporan Keuangan</li>
                                    <li>Pendamping Khusus Pribadi (Account Manager) &amp; Bantuan VIP 24 Jam</li>
                                </ul>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- Sticky Footer -->
                <div
                    class="p-4 sm:p-5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="closeDetail()"
                        class="px-4 py-2.5 rounded-[12px] bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-800 dark:text-slate-200 font-semibold text-xs sm:text-sm min-h-[44px]">
                        Tutup
                    </button>
                    <a href="{{ route('register') }}"
                        class="px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066D6] text-white font-bold text-xs sm:text-sm min-h-[44px] flex items-center justify-center">
                        Daftar Sekarang
                    </a>
                </div>

            </div>
        </div>

    </div>
@endsection
