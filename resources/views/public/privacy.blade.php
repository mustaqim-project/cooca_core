@extends('layouts.public_marketing')

@section('title', ($page->meta_title ?? 'Kebijakan Privasi & Perlindungan Data Pribadi') . ' | COOCA')
@section('description',
    $page->meta_description ??
    'Kebijakan privasi resmi COOCA mengenai pengumpulan data pemilik UMKM dan pelanggan toko, enkripsi AES-256 GCM, isolasi multi-tenant, dan hak data UU PDP No. 27/2022.')
@section('og_title', ($page->meta_title ?? 'Kebijakan Privasi & Perlindungan Data Pribadi') . ' | COOCA')
@section('og_description', $page->meta_description ?? 'Kebijakan privasi resmi COOCA mengenai kepatuhan UU PDP, isolasi multi-tenant, dan hak perlindungan data UMKM.')

@section('content')
    <div x-data="{ audienceFilter: 'all' }" class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">
        
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ TYPE D LEGAL HERO (Midnight #060B1E Bento Canvas) ════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <header class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-14 sm:pb-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none"></div>

            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">

                <!-- Breadcrumbs (Clean Apple HIG Hairline Nav) -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 overflow-x-auto py-1">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors shrink-0">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/20 shrink-0"></i>
                    <span class="text-slate-400 shrink-0">Legal</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/20 shrink-0"></i>
                    <span class="text-[#00C4D8] font-semibold shrink-0">Kebijakan Privasi</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Column: Copy, Metadata, & Segment Controls (7 Cols) -->
                    <div class="lg:col-span-7 space-y-5 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-2.5 w-full">
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-emerald-400 flex items-center justify-center lg:justify-start gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span>Kepatuhan Resmi UU Pelindungan Data Pribadi (UU PDP No. 27/2022)</span>
                            </p>

                            <h1 class="text-3xl sm:text-4xl lg:text-[2.65rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                {{ $page->title ?? 'Kebijakan Privasi & Pelindungan Data Pribadi' }}
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            {{ $page->subtitle ?? 'Dokumen ini menguraikan komitmen COOCA dalam mengumpulkan, mengamankan, dan memproses data pemilik bisnis UMKM serta pelanggan toko secara terisolasi tanpa pernah menjual data pribadi kepada pihak ketiga.' }}
                        </p>

                        <!-- Meta Pills & Print Action -->
                        <div class="pt-2 flex flex-wrap items-center justify-center lg:justify-start gap-2.5 text-xs text-slate-300 w-full">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-white/10 border border-white/10 font-mono">
                                <i data-lucide="file-code" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Versi {{ $page?->version ?? '2.1' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-white/10 border border-white/10 font-mono">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Efektif: {{ $page && $page->effective_date ? $page->effective_date->format('d F Y') : '18 September 2026' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-medium">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                <span>Enkripsi AES-256 GCM</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-blue-500/15 border border-blue-500/30 text-blue-300 font-medium">
                                <i data-lucide="database" class="w-3.5 h-3.5"></i>
                                <span>Isolasi Multi-Tenant</span>
                            </div>
                            <button type="button" onclick="window.print()"
                                class="h-8 px-3.5 rounded-[10px] text-xs font-semibold text-white bg-white/10 hover:bg-white/15 border border-white/15 transition-all inline-flex items-center gap-1.5 cursor-pointer active:scale-[0.98]">
                                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-300"></i>
                                <span>Cetak / PDF</span>
                            </button>
                        </div>

                        <!-- Segmented Audience Control (Apple HIG) -->
                        <div class="pt-2 w-full flex justify-center lg:justify-start">
                            <div class="p-1 rounded-[14px] bg-[#0E1E45] border border-white/15 inline-flex items-center gap-1 max-w-full overflow-x-auto shadow-inner">
                                <button type="button" @click="audienceFilter = 'all'"
                                    :class="audienceFilter === 'all' ? 'bg-[#007AFF] text-white shadow-sm font-bold' : 'text-slate-300 hover:text-white font-medium'"
                                    class="h-8 px-3.5 rounded-[10px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                    <span>Semua Ketentuan</span>
                                </button>
                                <button type="button" @click="audienceFilter = 'owner'"
                                    :class="audienceFilter === 'owner' ? 'bg-[#007AFF] text-white shadow-sm font-bold' : 'text-slate-300 hover:text-white font-medium'"
                                    class="h-8 px-3.5 rounded-[10px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    <span>Khusus Pemilik Usaha</span>
                                </button>
                                <button type="button" @click="audienceFilter = 'customer'"
                                    :class="audienceFilter === 'customer' ? 'bg-[#007AFF] text-white shadow-sm font-bold' : 'text-slate-300 hover:text-white font-medium'"
                                    class="h-8 px-3.5 rounded-[10px] text-xs transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                    <span>Khusus Pelanggan Toko</span>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Simulated Privacy & Data Protection Bento Cockpit (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div class="rounded-[24px] bg-[#0E1E45]/80 p-5 sm:p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-4">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></span>
                                    <span class="text-xs font-mono font-semibold text-slate-300 ml-2">Arsitektur Kepatuhan PDP</span>
                                </div>
                                <span class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <i data-lucide="shield" class="w-3 h-3"></i> UU No. 27/2022
                                </span>
                            </div>

                            <div class="space-y-2.5 text-xs">
                                <div class="p-3.5 rounded-[14px] bg-[#060B1E]/70 border border-white/10 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/25">
                                        <i data-lucide="shield-off" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white text-xs">Zero Data Selling Guarantee</div>
                                        <div class="text-[11px] text-slate-300 mt-0.5 leading-snug">COOCA tidak pernah dan tidak akan pernah menjual, menyewakan, atau memonetisasi data pelanggan Anda kepada broker data.</div>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-[14px] bg-[#060B1E]/70 border border-white/10 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#00C4D8] flex items-center justify-center shrink-0 border border-[#007AFF]/25">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white text-xs">Enkripsi Rest &amp; Transit</div>
                                        <div class="text-[11px] text-slate-300 mt-0.5 leading-snug">Data dilindungi TLS 1.3 saat transmisi dan enkripsi hardware AES-256 pada media penyimpanan cloud.</div>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-[14px] bg-[#060B1E]/70 border border-white/10 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/25">
                                        <i data-lucide="user-x" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white text-xs">Hak Penghapusan &amp; Ekspor</div>
                                        <div class="text-[11px] text-slate-300 mt-0.5 leading-snug">Pemilik usaha dan konsumen berhak meminta ekspor salinan data atau penghapusan permanen kapan saja.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                <i data-lucide="list" class="w-4 h-4 text-[#007AFF]"></i>
                                <h3 class="text-[14px] font-bold text-black dark:text-white">Daftar Isi Kebijakan</h3>
                            </div>

                            <nav class="space-y-1.5 text-[13px]">
                                <a href="#general-policy"
                                    class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                                    1. Kerangka Pelindungan Data (UU PDP)
                                </a>
                                <a href="#third-party-integrations"
                                    class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                                    2. Integrasi TriPay, Biteship &amp; WhatsApp
                                </a>
                                <a href="#data-subject-rights"
                                    class="block p-2 rounded-[10px] text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 hover:text-[#007AFF] transition-colors">
                                    3. Hak Subjek Data &amp; Hapus Data
                                </a>
                                <a href="#owner-section" x-show="audienceFilter === 'all' || audienceFilter === 'owner'"
                                    class="block p-2 rounded-[10px] text-[#007AFF] dark:text-[#0A84FF] font-semibold hover:bg-[#007AFF]/10 transition-colors">
                                    4. Bagian Khusus Pemilik Usaha (Owner)
                                </a>
                                <a href="#customer-section"
                                    x-show="audienceFilter === 'all' || audienceFilter === 'customer'"
                                    class="block p-2 rounded-[10px] text-[#34C759] dark:text-[#30D158] font-semibold hover:bg-[#34C759]/10 transition-colors">
                                    5. Bagian Khusus Pelanggan (Customer)
                                </a>
                            </nav>
                        </div>

                        {{-- Legal Compliance Box --}}
                        <div
                            class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2">
                            <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-[13px] font-bold">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span>Enkripsi Simetris AES-256</span>
                            </div>
                            <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed">
                                Access token OAuth, kunci perbankan, dan data kredensial disimpan terenkripsi di tingkat
                                database peladen.
                            </p>
                            <div class="pt-1 text-[11px] text-black/50 dark:text-white/50">
                                Kontak DPO: <a href="mailto:dpo@cooca.id"
                                    class="text-[#007AFF] font-mono">dpo@cooca.id</a>
                            </div>
                        </div>
                    </aside>

                    {{-- Right Column: Content Body (8 cols) --}}
                    <div class="lg:col-span-8 space-y-6">

                        {{-- Section General --}}
                        <div id="general-policy" x-show="audienceFilter === 'all'" x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-xs space-y-8 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            {!! $page?->content_general ?? '<p>Kebijakan privasi resmi sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Section Owner --}}
                        <div id="owner-section" x-show="audienceFilter === 'all' || audienceFilter === 'owner'"
                            x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#007AFF]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            <div
                                class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <h2
                                    class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2 min-w-0 flex-1 leading-snug break-words">
                                    <i data-lucide="briefcase" class="w-5 h-5 text-[#007AFF] shrink-0"></i>
                                    <span>Ketentuan Khusus Pemilik Usaha (Owner UMKM)</span>
                                </h2>
                                <span
                                    class="px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-mono text-[11px] font-bold shrink-0">Mitra Merchant</span>
                            </div>

                            {!! $page?->content_owner ?? '<p>Ketentuan pemilik usaha sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Section Customer --}}
                        <div id="customer-section" x-show="audienceFilter === 'all' || audienceFilter === 'customer'"
                            x-transition
                            class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-[#34C759]/20 backdrop-blur-xl shadow-xs space-y-6 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">
                            <div
                                class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                                <h2
                                    class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2 min-w-0 flex-1 leading-snug break-words">
                                    <i data-lucide="shopping-bag" class="w-5 h-5 text-[#34C759] shrink-0"></i>
                                    <span>Ketentuan Khusus Pelanggan Toko (Customer)</span>
                                </h2>
                                <span
                                    class="px-2.5 py-1 rounded-[8px] bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] font-mono text-[11px] font-bold shrink-0">Pelanggan Toko</span>
                            </div>

                            {!! $page?->content_customer ?? '<p>Ketentuan pelanggan toko sedang diselaraskan dengan pembaruan sistem.</p>' !!}
                        </div>

                        {{-- Official Legal Footer --}}
                        <div
                            class="p-6 rounded-[24px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[12px] text-black/55 dark:text-white/55">
                            <div class="font-bold text-black dark:text-white">PT Cooca Digital Teknologi</div>
                            <p>
                                Kebijakan Privasi ini tunduk pada hukum negara Republik Indonesia. Jika Anda memiliki
                                pertanyaan mengenai pemrosesan data pribadi Anda atau ingin mengajukan permintaan
                                penghapusan akun permanen, silakan hubungi tim kami di <a href="mailto:support@cooca.id"
                                    class="text-[#007AFF] font-medium hover:underline">support@cooca.id</a>.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection
