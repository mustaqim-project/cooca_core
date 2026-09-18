@extends('layouts.public_marketing')

@section('title', ($page->meta_title ?? 'Syarat & Ketentuan Layanan (Terms of Service)') . ' | Cooca')
@section('description', $page->meta_description ?? 'Syarat dan Ketentuan resmi penggunaan platform Cooca, pemisahan hak kewajiban Owner UMKM dan Pembeli, aturan settlement TriPay, pengiriman Biteship, dan resolusi sengketa.')

@section('content')
<main x-data="{ audienceFilter: 'all' }" class="min-h-screen pt-28 pb-20 bg-[#F5F5F7] dark:bg-[#0A0A0C] text-black dark:text-white antialiased">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Hero Header Bento --}}
        <div class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-semibold bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/20 w-fit">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Perjanjian Kontrak Layanan (Pasal 1338 KUHPerdata &amp; UU ITE)</span>
                </div>

                <div class="flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50 font-mono">
                    <span>Versi {{ $page->version ?? '2.1' }}</span>
                    <span>&bull;</span>
                    <span>Efektif: {{ $page->effective_date ? $page->effective_date->format('d F Y') : '18 September 2026' }}</span>
                </div>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-black dark:text-white">
                {{ $page->title ?? 'Syarat & Ketentuan Layanan (Terms of Service)' }}
            </h1>

            <p class="text-[14px] sm:text-[15px] text-black/65 dark:text-white/65 leading-relaxed max-w-3xl">
                {{ $page->subtitle ?? 'Perjanjian kontraktual penggunaan ekosistem Cooca, hak dan kewajiban Mitra Usaha & Pelanggan, tata kelola langganan SaaS, sistem escrow payment gateway, logistik, dan batas tanggung jawab.' }}
            </p>

            {{-- Segmented Audience Filter (Apple HIG Style) --}}
            <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-t border-black/[0.04] dark:border-white/[0.06]">
                <div class="p-1 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] inline-flex items-center gap-1 max-w-full overflow-x-auto">
                    <button type="button" @click="audienceFilter = 'all'"
                        :class="audienceFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="h-9 px-3.5 sm:px-4 rounded-[12px] text-[12px] transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>Semua Ketentuan</span>
                    </button>

                    <button type="button" @click="audienceFilter = 'owner'"
                        :class="audienceFilter === 'owner' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] dark:text-[#0A84FF] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="h-9 px-3.5 sm:px-4 rounded-[12px] text-[12px] transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        <span>Khusus Pemilik Usaha (Owner)</span>
                    </button>

                    <button type="button" @click="audienceFilter = 'customer'"
                        :class="audienceFilter === 'customer' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] dark:text-[#FF9F0A] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="h-9 px-3.5 sm:px-4 rounded-[12px] text-[12px] transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Khusus Pelanggan Toko (Customer)</span>
                    </button>
                </div>

                <button type="button" onclick="window.print()"
                    class="h-9 px-3.5 rounded-[12px] text-[12px] font-bold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span>Cetak / PDF</span>
                </button>
            </div>
        </div>

        {{-- 2-Column Bento Layout: Sticky TOC & Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Left Column: Sticky Table of Contents (4 cols) --}}
            <aside class="lg:col-span-4 lg:sticky lg:top-28 space-y-4">
                <div class="p-6 rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <i data-lucide="list" class="w-4 h-4 text-[#FF9500]"></i>
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Daftar Isi Syarat &amp; Ketentuan</h3>
                    </div>

                    <nav class="space-y-1.5 text-[13px]">
                        <a href="#terms-acceptance" class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                            1. Perjanjian Kontraktual &amp; Keabsahan
                        </a>
                        <a href="#prohibited-activities" class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#FF3B30] transition-colors">
                            2. Larangan Konten &amp; Barang Ilegal
                        </a>
                        <a href="#limitation-of-liability" class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                            3. Batasan Ganti Rugi &amp; Force Majeure
                        </a>
                        <a href="#owner-terms-section" x-show="audienceFilter === 'all' || audienceFilter === 'owner'" class="block p-2 rounded-[10px] text-[#007AFF] dark:text-[#0A84FF] font-semibold hover:bg-[#007AFF]/10 transition-colors">
                            4. Bagian Khusus Pemilik Usaha (Owner)
                        </a>
                        <a href="#customer-terms-section" x-show="audienceFilter === 'all' || audienceFilter === 'customer'" class="block p-2 rounded-[10px] text-[#FF9500] dark:text-[#FF9F0A] font-semibold hover:bg-[#FF9500]/10 transition-colors">
                            5. Bagian Khusus Pelanggan (Customer)
                        </a>
                    </nav>
                </div>

                {{-- Highlights Box --}}
                <div class="p-5 rounded-[22px] bg-gradient-to-br from-[#007AFF]/10 via-transparent to-transparent border border-[#007AFF]/20 backdrop-blur-sm space-y-2">
                    <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] text-[13px] font-bold">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                        <span>Yurisdiksi Hukum Indonesia</span>
                    </div>
                    <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed">
                        Seluruh sengketa diselesaikan secara musyawarah atau melalui Badan Arbitrase Nasional Indonesia (BANI) / Pengadilan Negeri RI.
                    </p>
                    <div class="pt-1 text-[11px] text-black/50 dark:text-white/50">
                        Pusat Bantuan: <a href="mailto:support@cooca.id" class="text-[#007AFF] font-mono">support@cooca.id</a>
                    </div>
                </div>
            </aside>

            {{-- Right Column: Content Body (8 cols) --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Section General --}}
                <div x-show="audienceFilter === 'all'" x-transition
                    class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-xs space-y-8 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                    {!! $page->content_general !!}
                </div>

                {{-- Section Owner --}}
                <div id="owner-terms-section" x-show="audienceFilter === 'all' || audienceFilter === 'owner'" x-transition
                    class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#007AFF]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                    <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="store" class="w-5 h-5 text-[#007AFF]"></i>
                            <span>Ketentuan Khusus Pemilik Usaha (Owner UMKM)</span>
                        </h2>
                        <span class="px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-mono text-[11px] font-bold">Mitra Merchant</span>
                    </div>

                    {!! $page->content_owner !!}
                </div>

                {{-- Section Customer --}}
                <div id="customer-terms-section" x-show="audienceFilter === 'all' || audienceFilter === 'customer'" x-transition
                    class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#FF9500]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                    <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2">
                            <i data-lucide="user-check" class="w-5 h-5 text-[#FF9500]"></i>
                            <span>Ketentuan Khusus Pelanggan Toko (Customer)</span>
                        </h2>
                        <span class="px-2.5 py-1 rounded-[8px] bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] font-mono text-[11px] font-bold">Pelanggan Toko</span>
                    </div>

                    {!! $page->content_customer !!}
                </div>

                {{-- Official Legal Footer --}}
                <div class="p-6 rounded-[24px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[12px] text-black/55 dark:text-white/55">
                    <div class="font-bold text-black dark:text-white">PT Cooca Digital Teknologi</div>
                    <p>
                        Dengan melanjutkan akses atau transaksi pada ekosistem Cooca, Anda mengonfirmasi pemahaman Anda atas hak dan kewajiban hukum ini. Pertanyaan lebih lanjut dapat diajukan kepada tim legal kami melalui <a href="mailto:support@cooca.id" class="text-[#007AFF] font-medium hover:underline">support@cooca.id</a>.
                    </p>
                </div>

            </div>
        </div>

    </div>
</main>
@endsection
