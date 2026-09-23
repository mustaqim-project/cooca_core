@extends('layouts.public_marketing')

@section('title', 'Harga & Paket Langganan - Transparan & Ramah UMKM | COOCA')
@section('description', 'Mulai gratis selamanya untuk seluruh fitur esensial bisnis: kasir POS, stok real-time, dan pembukuan otomatis tanpa biaya tersembunyi.')

@section('content')
<div class="pt-10 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 text-[#007AFF] dark:text-[#0A84FF] text-xs font-semibold">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span>Biaya Transparan &amp; Adil</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                Investasi Cerdas untuk Pertumbuhan Bisnis Anda
            </h1>
            <p class="text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                Tanpa kontrak mengikat, tanpa biaya tersembunyi. Mulai gratis hari ini dan tingkatkan saat bisnis Anda semakin bertumbuh.
            </p>
        </div>

        <!-- Pricing Cards (Apple Bento 3-Tier Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-stretch">

            <!-- Tier 1: Starter (Gratis Selamanya) -->
            <div class="p-8 rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B]">Starter UMKM</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp 0</span>
                        <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">/ selamanya</span>
                    </div>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Cocok untuk warung, toko kelontong, dan usaha rintisan yang baru memulai digitalisasi.
                    </p>

                    <div class="border-t border-black/5 dark:border-white/10 pt-4 space-y-3">
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>1 Gerai / Toko</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Kasir POS &amp; Cetak Struk</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Manajemen Inventori &amp; Stok</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Laporan Laba Rugi Dasar</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Katalog Toko Online</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('register') }}"
                    class="w-full py-3.5 rounded-[16px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-xs text-center transition">
                    Mulai Gratis Sekarang
                </a>
            </div>

            <!-- Tier 2: Pro Growth (Featured) -->
            <div class="p-8 rounded-[28px] bg-white dark:bg-[#1C1C1E] border-2 border-[#007AFF] shadow-xl relative flex flex-col justify-between space-y-6">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-[#007AFF] text-white text-[10px] font-bold uppercase tracking-wider shadow">
                    Paling Populer
                </div>

                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Pro Omnichannel</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp 149.000</span>
                        <span class="text-xs text-[#6E6E73] dark:text-[#86868B]">/ bulan</span>
                    </div>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Untuk kafe, butik, bengkel, dan toko retail yang ingin otomatisasi omnichannel WhatsApp &amp; Marketplace.
                    </p>

                    <div class="border-t border-black/5 dark:border-white/10 pt-4 space-y-3">
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span class="font-medium">Semua Fitur Starter</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Hingga 3 Cabang &amp; Multi-Gudang</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Integrasi WhatsApp Gateway Struk</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Resep Food Costing &amp; SPK Bengkel</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Jurnal Akuntansi Otomatis</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>AI Sales Assistant &amp; Forecast</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('register') }}"
                    class="w-full py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs text-center transition shadow-[0_2px_8px_rgba(0,122,255,0.3)]">
                    Pilih Paket Pro
                </a>
            </div>

            <!-- Tier 3: Enterprise & Multi-Branch -->
            <div class="p-8 rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] dark:text-[#86868B]">Enterprise Scale</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7]">Custom</span>
                    </div>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Untuk franchise, manufaktur pabrikasi, distributor, dan jaringan ritel multi-cabang skala besar.
                    </p>

                    <div class="border-t border-black/5 dark:border-white/10 pt-4 space-y-3">
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span class="font-medium">Semua Fitur Pro</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Cabang &amp; User Tanpa Batas</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Custom Domain Toko Sendiri</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>BOM Manufaktur &amp; Work Orders</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Dedicated Account Manager 24/7</span>
                        </div>
                    </div>
                </div>

                <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo COOCA, saya tertarik dengan paket Enterprise') }}"
                    target="_blank"
                    class="w-full py-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-xs text-center transition">
                    Konsultasi Enterprise
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
