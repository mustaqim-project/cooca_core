@extends('layouts.public_marketing')

@section('title', ($page->meta_title ?? 'Syarat & Ketentuan Layanan (Terms of Service)') . ' | Cooca')
@section('description',
    $page->meta_description ??
    'Syarat dan Ketentuan resmi penggunaan platform Cooca, pemisahan hak
    kewajiban Owner UMKM dan Pembeli, aturan settlement TriPay, pengiriman Biteship, dan resolusi sengketa.')

@section('og_title', ($page->meta_title ?? 'Syarat & Ketentuan Layanan') . ' | COOCA')
@section('og_description', $page->meta_description ?? 'Syarat dan Ketentuan resmi penggunaan platform COOCA, hak kewajiban Mitra Usaha & Pelanggan.')

@section('content')
    <div x-data="{ audienceFilter: 'all' }" class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">
        
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ TYPE D LEGAL HEADER (Calm, Pure Typography & Document Navigation) ════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <header class="bg-white dark:bg-[#1C1C1E] border-b border-black/[0.06] dark:border-white/[0.08] pt-8 sm:pt-12 pb-8 sm:pb-10 transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Breadcrumbs (Clean Apple HIG Hairline Nav) -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 overflow-x-auto py-1">
                    <a href="{{ route('landing') }}" class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors shrink-0">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0"></i>
                    <span class="text-slate-500 dark:text-slate-400 shrink-0">Legal</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0"></i>
                    <span class="text-slate-900 dark:text-white font-semibold shrink-0">Syarat &amp; Ketentuan</span>
                </nav>

                <div class="space-y-3">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-2">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                        <span>Perjanjian Kontrak Layanan (Pasal 1338 KUHPerdata &amp; UU ITE)</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.2]">
                        {{ $page->title ?? 'Syarat & Ketentuan Layanan (Terms of Service)' }}
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed max-w-3xl font-normal pt-1">
                        {{ $page->subtitle ?? 'Perjanjian kontraktual penggunaan ekosistem COOCA, hak dan kewajiban Mitra Usaha & Pelanggan, tata kelola langganan SaaS, sistem escrow payment gateway, logistik, dan batas tanggung jawab.' }}
                    </p>
                </div>

                <!-- Meta Pills & Action Row -->
                <div class="pt-2 flex flex-wrap items-center justify-between gap-4 border-t border-black/[0.06] dark:border-white/[0.08] pt-4">
                    <div class="flex flex-wrap items-center gap-2.5 text-xs text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-slate-100 dark:bg-white/[0.06] font-mono">
                            <i data-lucide="file-code" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <span>Versi {{ $page?->version ?? '2.1' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-slate-100 dark:bg-white/[0.06] font-mono">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            <span>Efektif: {{ $page && $page->effective_date ? $page->effective_date->format('d F Y') : '18 September 2026' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-medium">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            <span>Hukum Republik Indonesia</span>
                        </div>
                    </div>

                    <button type="button" onclick="window.print()"
                        class="h-9 px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] transition-all inline-flex items-center gap-2 cursor-pointer shadow-xs active:scale-[0.98]">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak / PDF</span>
                    </button>
                </div>

                <!-- Segmented Audience Control (Apple HIG) -->
                <div class="pt-1">
                    <div class="p-1 rounded-[14px] bg-slate-100 dark:bg-white/[0.06] inline-flex items-center gap-1 max-w-full overflow-x-auto">
                        <button type="button" @click="audienceFilter = 'all'"
                            :class="audienceFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                            class="h-8 px-3.5 rounded-[10px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            <span>Semua Ketentuan</span>
                        </button>
                        <button type="button" @click="audienceFilter = 'owner'"
                            :class="audienceFilter === 'owner' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] dark:text-[#0A84FF] shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                            class="h-8 px-3.5 rounded-[10px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <i data-lucide="store" class="w-3.5 h-3.5"></i>
                            <span>Khusus Pemilik Usaha</span>
                        </button>
                        <button type="button" @click="audienceFilter = 'customer'"
                            :class="audienceFilter === 'customer' ? 'bg-white dark:bg-[#2C2C2E] text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                            class="h-8 px-3.5 rounded-[10px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                            <span>Khusus Pelanggan Toko</span>
                        </button>
                    </div>
                </div>

            </div>
        </header>

        <!-- Content Body Section -->
        <main class="min-h-screen py-12 lg:py-16 bg-[#F5F5F7] dark:bg-[#0A0A0C] text-black dark:text-white antialiased">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                {{-- 2-Column Bento Layout: Sticky TOC & Content --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {{-- Left Column: Sticky Table of Contents (4 cols) --}}
                    <aside class="lg:col-span-4 lg:sticky lg:top-28 space-y-4">
                        <div
                            class="p-6 rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-xs space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <i data-lucide="list" class="w-4 h-4 text-[#FF9500]"></i>
                                <h3 class="text-[14px] font-bold text-black dark:text-white">Daftar Isi Syarat &amp;
                                    Ketentuan</h3>
                            </div>

                            <nav class="space-y-1.5 text-[13px]">
                                <a href="#terms-acceptance"
                                    class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                                    1. Perjanjian Kontraktual &amp; Keabsahan
                                </a>
                                <a href="#prohibited-activities"
                                    class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#FF3B30] transition-colors">
                                    2. Larangan Konten &amp; Barang Ilegal
                                </a>
                                <a href="#limitation-of-liability"
                                    class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                                    3. Batasan Ganti Rugi &amp; Force Majeure
                                </a>
                                <a href="#owner-terms-section"
                                    x-show="audienceFilter === 'all' || audienceFilter === 'owner'"
                                    class="block p-2 rounded-[10px] text-[#007AFF] dark:text-[#0A84FF] font-semibold hover:bg-[#007AFF]/10 transition-colors">
                                    4. Bagian Khusus Pemilik Usaha (Owner)
                                </a>
                                <a href="#customer-terms-section"
                                    x-show="audienceFilter === 'all' || audienceFilter === 'customer'"
                                    class="block p-2 rounded-[10px] text-[#FF9500] dark:text-[#FF9F0A] font-semibold hover:bg-[#FF9500]/10 transition-colors">
                                    5. Bagian Khusus Pelanggan (Customer)
                                </a>
                            </nav>
                        </div>

                        {{-- Highlights Box --}}
                        <div
                            class="p-5 rounded-[22px] bg-gradient-to-br from-[#007AFF]/10 via-transparent to-transparent border border-[#007AFF]/20 backdrop-blur-sm space-y-2">
                            <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] text-[13px] font-bold">
                                <i data-lucide="scale" class="w-4 h-4"></i>
                                <span>Yurisdiksi Hukum Indonesia</span>
                            </div>
                            <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed">
                                Seluruh sengketa diselesaikan secara musyawarah atau melalui Badan Arbitrase Nasional
                                Indonesia (BANI) / Pengadilan Negeri RI.
                            </p>
                            <div class="pt-1 text-[11px] text-black/50 dark:text-white/50">
                                Pusat Bantuan: <a href="mailto:support@cooca.id"
                                    class="text-[#007AFF] font-mono">support@cooca.id</a>
                            </div>
                        </div>
                    </aside>

                    {{-- Right Column: Content Body (8 cols) --}}
                    <div class="lg:col-span-8 space-y-6">

                        {{-- Section General --}}
                        <div x-show="audienceFilter === 'all'" x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-xs space-y-8 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            {!! $page?->content_general ??
                                '<p>Syarat dan ketentuan layanan sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Section Owner --}}
                        <div id="owner-terms-section" x-show="audienceFilter === 'all' || audienceFilter === 'owner'"
                            x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#007AFF]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            <div
                                class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <h2
                                    class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2 min-w-0 flex-1 leading-snug break-words">
                                    <i data-lucide="store" class="w-5 h-5 text-[#007AFF] shrink-0"></i>
                                    <span>Ketentuan Khusus Pemilik Usaha (Owner UMKM)</span>
                                </h2>
                                <span
                                    class="px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-mono text-[11px] font-bold shrink-0">Mitra
                                    Merchant</span>
                            </div>

                            {!! $page?->content_owner ?? '<p>Ketentuan mitra usaha sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Section Customer --}}
                        <div id="customer-terms-section"
                            x-show="audienceFilter === 'all' || audienceFilter === 'customer'" x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#FF9500]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            <div
                                class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <h2
                                    class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2 min-w-0 flex-1 leading-snug break-words">
                                    <i data-lucide="user-check" class="w-5 h-5 text-[#FF9500] shrink-0"></i>
                                    <span>Ketentuan Khusus Pelanggan Toko (Customer)</span>
                                </h2>
                                <span
                                    class="px-2.5 py-1 rounded-[8px] bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] font-mono text-[11px] font-bold shrink-0">Pelanggan
                                    Toko</span>
                            </div>

                            {!! $page?->content_customer ?? '<p>Ketentuan pelanggan toko sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Official Legal Footer --}}
                        <div
                            class="p-6 rounded-[24px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[12px] text-black/55 dark:text-white/55">
                            <div class="font-bold text-black dark:text-white">PT Cooca Digital Teknologi</div>
                            <p>
                                Dengan melanjutkan akses atau transaksi pada ekosistem Cooca, Anda mengonfirmasi pemahaman
                                Anda atas hak dan kewajiban hukum ini. Pertanyaan lebih lanjut dapat diajukan kepada tim
                                legal kami melalui <a href="mailto:support@cooca.id"
                                    class="text-[#007AFF] font-medium hover:underline">support@cooca.id</a>.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection
