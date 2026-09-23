@extends('layouts.public_marketing')

@section('title', 'Kalkulator Target Omzet Harian & Jumlah Transaksi Online | COOCA')
@section('description', 'Kalkulator target omzet harian online untuk toko dan kafe. Pecah target omzet bulanan menjadi target omzet harian, jumlah transaksi pembeli, dan nilai keranjang belanja rata-rata.')
@section('og_title', 'Kalkulator Target Omzet Harian & Jumlah Transaksi Online | COOCA')
@section('og_description', 'Pecah target omzet bulanan Anda menjadi target transaksi harian dan rata-rata struk kasir yang terukur.')
@section('canonical', route('kalkulator.omzet-harian'))
@section('og_type', 'website')
@section('keywords', 'kalkulator omzet harian, hitung target penjualan bulanan, average order value kasir, target transaksi toko, sales target breakdown')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Kalkulator Target Omzet Harian COOCA",
  "url": "{{ route('kalkulator.omzet-harian') }}",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "All",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "description": "Kalkulator pemecah target omzet bulanan menjadi target penjualan harian dan struk kasir."
}
</script>
@endpush

@section('content')
    <div x-data="{
        refreshIcons() {
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        }
    }" x-init="refreshIcons()"
    class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pt-8 pb-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('kalkulator.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Kalkulator</a>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">Kalkulator Target Omzet</span>
            </nav>

            <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- KIRI: Headline & Penjelasan (7 Cols) -->
                <div class="lg:col-span-7 space-y-5 text-left">
                    <div class="space-y-2">
                        <p
                            class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            Pemecahan Target Penjualan Harian
                        </p>
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15] text-balance break-words">
                            Kalkulator Target <span class="text-[#007AFF] dark:text-[#0A84FF]">Omzet Harian</span>
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-xl font-normal text-pretty break-words">
                        Jangan biarkan target bulanan terasa mustahil dicapai. Pecah menjadi target transaksi riil per hari
                        dan jumlah struk kasir yang perlu Anda layani setiap shift.
                    </p>

                    <!-- Reassurance Points for UMKM 40-65 -->
                    <div
                        class="pt-1 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Hitung Target Omzet per Hari Buka</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                            <span>Ketahui Jumlah Struk Kasir (AOV)</span>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Visual Formula Preview Card (5 Cols) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm p-5 sm:p-6 space-y-4">
                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                            </div>
                            <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D]">Logika Pemecahan
                                Target</span>
                            <div class="w-6"></div>
                        </div>

                        <div class="space-y-2.5 text-xs text-[#48484A] dark:text-[#AEAEB2]">
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Target Omzet Bulanan</span>
                                <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF]">Goal Besar</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <span class="font-medium">Hari Aktif Toko Buka</span>
                                <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jumlah Hari</span>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                            <span class="text-[#6E6E73] dark:text-[#86868B]">Target Harian:</span>
                            <span class="font-bold text-[#34C759] dark:text-[#30D158]">Omzet Bulan / Hari Buka</span>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ═══ CALCULATOR INTERACTIVE APP ═══ -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm p-6 sm:p-8 rounded-[24px]"
                x-data="{
                    monthlyTarget: 45000000,
                    openDays: 26,
                    averageTicket: 35000,
                
                    get dailyRevenueTarget() {
                        if (this.openDays <= 0) return 0;
                        return Math.round(this.monthlyTarget / this.openDays);
                    },
                    get dailyTransactionsNeeded() {
                        if (this.averageTicket <= 0) return 0;
                        return Math.ceil(this.dailyRevenueTarget / this.averageTicket);
                    },
                    get hourlyTransactionsNeeded() {
                        return Math.ceil(this.dailyTransactionsNeeded / 10);
                    }
                }">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Apple Inset Inputs (7 Kolom) -->
                    <div class="lg:col-span-7 space-y-4">
                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Target
                                    Omzet Bulanan</label>
                                <span class="font-mono text-sm font-bold text-[#007AFF] dark:text-[#0A84FF]">Rp <span
                                        x-text="Number(monthlyTarget).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="monthlyTarget" min="5000000" max="250000000"
                                step="1000000" class="w-full accent-[#007AFF] cursor-pointer">
                        </div>

                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Jumlah
                                    Hari Buka Toko per Bulan</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]"><span
                                        x-text="openDays"></span> Hari</span>
                            </div>
                            <input type="range" x-model.number="openDays" min="15" max="31" step="1"
                                class="w-full accent-[#007AFF] cursor-pointer">
                        </div>

                        <div
                            class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                            <div class="flex justify-between items-center">
                                <label
                                    class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] uppercase tracking-wide">Rata-rata
                                    Belanja per Pembeli (Basket Size)</label>
                                <span class="font-mono text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Rp <span
                                        x-text="Number(averageTicket).toLocaleString('id-ID')"></span></span>
                            </div>
                            <input type="range" x-model.number="averageTicket" min="5000" max="200000"
                                step="5000" class="w-full accent-[#007AFF] cursor-pointer">
                        </div>
                    </div>

                    <!-- Right: Sticky Bento Output Card (5 Kolom) -->
                    <div class="lg:col-span-5 sticky top-24 space-y-5">
                        <div
                            class="glass-card p-6 sm:p-7 rounded-[26px] space-y-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] block">Target
                                Penjualan Harian</span>

                            <div class="p-4 rounded-[18px] bg-[#007AFF]/10 border border-[#007AFF]/20">
                                <span class="text-xs font-bold uppercase text-[#6E6E73] dark:text-[#86868B] block">Target
                                    Omzet per Hari Buka</span>
                                <div class="text-3xl font-black text-[#007AFF] dark:text-[#0A84FF] font-mono mt-1">
                                    Rp <span x-text="dailyRevenueTarget.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-[#6E6E73] dark:text-[#86868B]">Transaksi Diperlukan:</span>
                                    <span class="font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7] text-base"><span
                                            x-text="dailyTransactionsNeeded"></span> struk / hari</span>
                                </div>
                                <div
                                    class="flex justify-between items-center pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-xs">
                                    <span class="text-[#6E6E73] dark:text-[#86868B]">Rata-rata per Jam (10 Jam):</span>
                                    <span class="font-mono font-bold text-[#34C759] dark:text-[#30D158] text-sm">~<span
                                            x-text="hourlyTransactionsNeeded"></span> pembeli / jam</span>
                                </div>
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('register') }}"
                                    class="w-full glow-btn py-3.5 rounded-[14px] text-white font-semibold text-xs flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                                    <span>Mulai Pantau Omzet Toko Gratis</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
