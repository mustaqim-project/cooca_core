@extends('layouts.public_marketing')

@section('title', 'Harga & Paket Langganan - Transparan & Ramah UMKM | COOCA')
@section('description', 'Mulai gratis selamanya untuk seluruh fitur esensial bisnis: kasir POS, stok real-time, dan pembukuan otomatis tanpa biaya tersembunyi.')

@section('content')
<div class="pt-6 sm:pt-10 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-20">

        <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- KIRI: Headline & CTA (7 Cols) -->
            <div class="lg:col-span-7 space-y-6 text-left">
                <div class="space-y-2">
                    <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        Investasi Terbuka &amp; Bebas Biaya Tersembunyi
                    </p>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.12]">
                        Sistem Bisnis Lengkap, <span class="text-[#007AFF] dark:text-[#0A84FF]">Mulai Rp 0</span>
                    </h1>
                </div>

                <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-2xl font-normal">
                    Kelola penjualan toko, stok gudang, dan pembukuan tanpa terbebani biaya langganan bulanan yang mahal. Mulai gunakan fitur esensial hari ini secara gratis selamanya.
                </p>

                <!-- Action Buttons -->
                <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                    <a href="{{ route('register') }}"
                        class="px-8 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all min-h-[50px]">
                        <span>Mulai Gratis Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya ingin bertanya seputar paket langganan dan fitur sistem') }}"
                        target="_blank"
                        rel="noopener"
                        class="px-6 py-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] min-h-[50px]">
                        <i data-lucide="phone-call" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Konsultasi Paket (WhatsApp)</span>
                    </a>
                </div>

                <!-- Reassurance Points for UMKM 40-65 -->
                <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Tanpa Kartu Kredit</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Tanpa Kontrak Mengikat</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Data Usaha Aman &amp; Terenkripsi</span>
                    </div>
                </div>
            </div>

            <!-- KANAN: Comparison Value Card (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-6 sm:p-7 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                            <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                            <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                        </div>
                        <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">
                            Simulasi Efisiensi Anggaran
                        </span>
                        <div class="w-6"></div>
                    </div>

                    <!-- Comparison Rows -->
                    <div class="space-y-3 text-xs">
                        <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/[0.05] border border-[#FF3B30]/15 space-y-1">
                            <div class="flex items-center justify-between text-[#FF3B30] dark:text-[#FF453A] font-bold">
                                <span>Software Kasir Biasa</span>
                                <span class="tabular-nums">Rp 250rb - 500rb / bln</span>
                            </div>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Biaya lisensi per gerai, biaya cetak struk tambahan, dan batasan transaksi.</p>
                        </div>

                        <div class="p-3.5 rounded-[14px] bg-[#34C759]/[0.08] border border-[#34C759]/20 space-y-1">
                            <div class="flex items-center justify-between text-[#34C759] dark:text-[#30D158] font-bold">
                                <span>Cooca Starter UMKM</span>
                                <span class="tabular-nums text-sm font-extrabold">Rp 0 (Gratis Selamanya)</span>
                            </div>
                            <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Kasir POS lengkap, manajemen stok, dan laporan laba rugi dasar tanpa batas waktu.</p>
                        </div>
                    </div>

                    <!-- Summary Highlight -->
                    <div class="p-4 rounded-[16px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                        <div>
                            <p class="text-xs text-[#6E6E73] dark:text-[#86868B]">Penghematan Biaya per Tahun</p>
                            <p class="text-2xl font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158]">
                                Rp 3.000.000+
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                            <i data-lucide="piggy-bank" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <p class="text-[11px] text-center text-[#8E8E93] dark:text-[#98989D]">
                        Alokasikan anggaran langganan software untuk modal belanja bahan baku toko Anda.
                    </p>

                </div>
            </div>

        </section>

        <!-- ═══ 3-TIER PRICING CARDS ═══ -->
        <section class="space-y-8">
            <div class="max-w-2xl space-y-2">
                <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Pilihan Paket</p>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                    Pilih Paket Sesuai Skala Bisnis Anda
                </h2>
                <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    Mulai dari paket gratis untuk pemula hingga sistem terintegrasi untuk bisnis yang sedang berkembang pesat.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-stretch">

                <!-- Tier 1: Starter UMKM (Gratis) -->
                <div class="p-7 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-5">
                        <div class="space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B]">Starter UMKM</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tabular-nums">Rp 0</span>
                                <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">/ selamanya</span>
                            </div>
                        </div>
                        
                        <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed">
                            Cocok untuk warung makan, toko kelontong, kios pulsa, dan usaha rintisan yang ingin mencatat penjualan dengan rapi.
                        </p>

                        <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-5 space-y-3.5">
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>1 Gerai Toko Aktif</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Kasir POS &amp; Cetak Struk Bluetooth</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Manajemen Stok Produk &amp; Peringatan Habis</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Laporan Penjualan &amp; Laba Rugi Harian</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Katalog Toko Online &amp; Etalase Publik</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('register') }}"
                        class="w-full py-4 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-sm text-center transition min-h-[48px] flex items-center justify-center">
                        Mulai Gratis Sekarang
                    </a>
                </div>

                <!-- Tier 2: Pro Omnichannel (Featured) -->
                <div class="p-7 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border-2 border-[#007AFF] shadow-md relative flex flex-col justify-between space-y-6">
                    
                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Pro Omnichannel</span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                Paling Populer
                            </span>
                        </div>

                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tabular-nums">Rp 149.000</span>
                            <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">/ bulan</span>
                        </div>
                        
                        <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed">
                            Untuk kafe, butik, bengkel, dan toko retail yang ingin kirim struk via WhatsApp otomatis dan sinkron stok marketplace.
                        </p>

                        <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-5 space-y-3.5">
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span class="font-semibold">Semua Fitur Starter UMKM</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span>Hingga 3 Cabang Toko &amp; Multi-Gudang</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span>Kirim Nota Struk Otomatis via WhatsApp</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span>HPP Resep Kuliner &amp; SPK Bengkel</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span>Jurnal Akuntansi &amp; Buku Kas Otomatis</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <span>Asisten AI Rekomendasi Penjualan</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('register') }}"
                        class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm text-center transition shadow-sm min-h-[48px] flex items-center justify-center">
                        Pilih Paket Pro
                    </a>
                </div>

                <!-- Tier 3: Enterprise Scale -->
                <div class="p-7 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-5">
                        <div class="space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B]">Enterprise Scale</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7]">Kustom</span>
                            </div>
                        </div>
                        
                        <p class="text-xs sm:text-sm text-[#48484A] dark:text-[#AEAEB2] leading-relaxed">
                            Untuk jaringan waralaba (franchise), pabrikasi, distributor, dan jaringan ritel dengan puluhan cabang di berbagai kota.
                        </p>

                        <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-5 space-y-3.5">
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span class="font-semibold">Semua Fitur Pro Omnichannel</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Cabang Toko &amp; Akun Staf Tanpa Batas</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Domain Sendiri (namatoko.com)</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>BOM Manufaktur &amp; Perintah Kerja Pabrik</span>
                            </div>
                            <div class="flex items-start gap-3 text-xs sm:text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                                <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <span>Pendampingan Khusus &amp; Prioritas 24/7</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya tertarik untuk berkonsultasi mengenai paket Enterprise untuk bisnis saya.') }}"
                        target="_blank"
                        rel="noopener"
                        class="w-full py-4 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-sm text-center transition min-h-[48px] flex items-center justify-center">
                        Konsultasi Enterprise
                    </a>
                </div>

            </div>
        </section>

    </div>
</div>
@endsection

