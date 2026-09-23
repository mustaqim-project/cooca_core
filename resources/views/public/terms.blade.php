@extends('layouts.public_marketing')

@section('title', ($page->meta_title ?? 'Syarat & Ketentuan Layanan (Terms of Service)') . ' | Cooca')
@section('description',
    $page->meta_description ??
    'Syarat dan Ketentuan resmi penggunaan platform Cooca, pemisahan hak
    kewajiban Owner UMKM dan Pembeli, aturan settlement TriPay, pengiriman Biteship, dan resolusi sengketa.')

@section('content')
    <div x-data="{ audienceFilter: 'all' }" class="w-full font-sans antialiased overflow-hidden">
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout with Midnight Blue Glow) ═══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-8 sm:pt-12 pb-16 lg:pb-20 overflow-hidden border-b border-white/10">

            <!-- Subtle Ambient Background Glows -->
            <div
                class="absolute -top-24 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none -z-0">
            </div>

            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full space-y-6">

                <!-- Breadcrumbs -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 overflow-x-auto py-1">
                    <a href="{{ route('landing') }}" class="hover:text-[#00C4D8] transition-colors shrink-0">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/20 shrink-0"></i>
                    <span class="text-white font-semibold shrink-0">Syarat &amp; Ketentuan Layanan</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                    <!-- KIRI: Headline, Subtitle, Metadata & Audience Segment (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div class="space-y-3">
                            <div
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold tracking-wide">
                                <i data-lucide="scale" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Perjanjian Kontrak Layanan (Pasal 1338 KUHPerdata &amp; UU ITE)</span>
                            </div>
                            <h1
                                class="text-3xl sm:text-5xl lg:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12]">
                                {{ $page->title ?? 'Syarat & Ketentuan Layanan (Terms of Service)' }}
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal">
                            {{ $page->subtitle ?? 'Perjanjian kontraktual penggunaan ekosistem Cooca, hak dan kewajiban Mitra Usaha & Pelanggan, tata kelola langganan SaaS, sistem escrow payment gateway, logistik, dan batas tanggung jawab.' }}
                        </p>

                        <!-- Meta Tags Row & Print Button -->
                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <div
                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-slate-300 font-mono">
                                <i data-lucide="file-code" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Versi {{ $page?->version ?? '2.1' }}</span>
                            </div>
                            <div
                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-slate-300 font-mono">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Efektif:
                                    {{ $page && $page->effective_date ? $page->effective_date->format('d F Y') : '18 September 2026' }}</span>
                            </div>
                            <button type="button" onclick="window.print()"
                                class="h-9 px-4 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/15 border border-white/15 active:scale-[0.98] transition-all inline-flex items-center gap-2 cursor-pointer ml-auto">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Cetak / PDF</span>
                            </button>
                        </div>

                        <!-- Segmented Audience Filter (Apple HIG Style in Dark Glass) -->
                        <div class="pt-2">
                            <div
                                class="p-1 rounded-[16px] bg-white/5 border border-white/10 inline-flex items-center gap-1 max-w-full overflow-x-auto">
                                <button type="button" @click="audienceFilter = 'all'"
                                    :class="audienceFilter === 'all' ? 'bg-[#007AFF] text-white shadow-sm font-bold' :
                                        'text-slate-400 hover:text-white font-medium'"
                                    class="h-9 px-3.5 sm:px-4 rounded-[12px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                    <span>Semua Ketentuan</span>
                                </button>
                                <button type="button" @click="audienceFilter = 'owner'"
                                    :class="audienceFilter === 'owner' ? 'bg-[#007AFF] text-white shadow-sm font-bold' :
                                        'text-slate-400 hover:text-white font-medium'"
                                    class="h-9 px-3.5 sm:px-4 rounded-[12px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    <span>Khusus Pemilik Usaha</span>
                                </button>
                                <button type="button" @click="audienceFilter = 'customer'"
                                    :class="audienceFilter === 'customer' ? 'bg-[#007AFF] text-white shadow-sm font-bold' :
                                        'text-slate-400 hover:text-white font-medium'"
                                    class="h-9 px-3.5 sm:px-4 rounded-[12px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                    <span>Khusus Pelanggan Toko</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Legal Compliance & Contractual Trust Bento Card (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div
                            class="relative rounded-2xl bg-white/[0.04] border border-white/10 backdrop-blur-xl p-6 shadow-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                    <span class="text-xs font-mono text-slate-400 ml-2">cooca://legal-framework</span>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-[#00C4D8] text-[11px] font-semibold">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                    Legally Binding
                                </span>
                            </div>

                            <div class="space-y-3">
                                <!-- Item 1: Escrow & Settlement -->
                                <div
                                    class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                                            <i data-lucide="wallet" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-white">Sistem Escrow &amp; Settlement Resmi
                                            </div>
                                            <div class="text-[11px] text-slate-400">Payment Gateway Berlisensi Bank
                                                Indonesia</div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                </div>

                                <!-- Item 2: SLA & Uptime -->
                                <div
                                    class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-[#007AFF]/10 border border-[#007AFF]/30 flex items-center justify-center text-[#007AFF]">
                                            <i data-lucide="activity" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-white">99.9% Cloud Service Level Agreement
                                            </div>
                                            <div class="text-[11px] text-slate-400">Jaminan Ketersediaan POS &amp; Toko
                                                Online</div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                </div>

                                <!-- Item 3: Indonesia Jurisdiction -->
                                <div
                                    class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                                            <i data-lucide="scale" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-white">Yurisdiksi Republik Indonesia</div>
                                            <div class="text-[11px] text-slate-400">Tunduk pada Hukum Perdata &amp; UU ITE
                                            </div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                </div>
                            </div>

                            <!-- Footer Micro Info -->
                            <div
                                class="pt-2 flex items-center justify-between text-[11px] text-slate-400 font-mono border-t border-white/10">
                                <span>Legal: legal@cooca.id</span>
                                <span>Resolusi: Musyawarah / PN</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

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
                                class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <h2
                                    class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="store" class="w-5 h-5 text-[#007AFF]"></i>
                                    <span>Ketentuan Khusus Pemilik Usaha (Owner UMKM)</span>
                                </h2>
                                <span
                                    class="px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-mono text-[11px] font-bold">Mitra
                                    Merchant</span>
                            </div>

                            {!! $page?->content_owner ?? '<p>Ketentuan mitra usaha sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Section Customer --}}
                        <div id="customer-terms-section"
                            x-show="audienceFilter === 'all' || audienceFilter === 'customer'" x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#FF9500]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            <div
                                class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <h2
                                    class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2">
                                    <i data-lucide="user-check" class="w-5 h-5 text-[#FF9500]"></i>
                                    <span>Ketentuan Khusus Pelanggan Toko (Customer)</span>
                                </h2>
                                <span
                                    class="px-2.5 py-1 rounded-[8px] bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] font-mono text-[11px] font-bold">Pelanggan
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
