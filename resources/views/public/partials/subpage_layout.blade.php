@extends('layouts.public_marketing')

@section('title', ($title ?? 'Solusi Bisnis') . ' | COOCA')
@section('description',
    $description ??
    'Kelola bisnis UMKM lebih cerdas dan terintegrasi dengan COOCA Business
    Operating System & Omnichannel ERP.')
@section('og_title', ($title ?? 'Solusi Bisnis') . ' | COOCA')
@section('og_description',
    $description ??
    'Kelola bisnis UMKM lebih cerdas dan terintegrasi dengan COOCA Business
    Operating System & Omnichannel ERP.')

@section('content')
    <div class="w-full">
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ HERO SECTION (Full Above-The-Fold 2-Grid Layout) ════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-8 sm:pt-12 pb-16 lg:pb-20 min-h-[calc(100svh-84px)] lg:flex lg:items-center overflow-hidden border-b border-white/10 w-full min-w-full">

            <!-- Subtle Ambient Background Glows -->
            <div
                class="absolute -top-24 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none -z-0">
            </div>

            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full space-y-6">

                <!-- Breadcrumbs (Clean Apple HIG Hairline Nav) -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 overflow-x-auto py-1">
                    <a href="{{ route('landing') }}" class="hover:text-[#00C4D8] transition-colors shrink-0">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/20 shrink-0"></i>
                    @if (isset($category))
                        <span class="shrink-0 text-slate-400">{{ $category }}</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/20 shrink-0"></i>
                    @endif
                    <span class="text-white font-semibold shrink-0">{{ $title ?? 'Detail' }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTA (Mobile Center, Desktop Left ~ 7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-2 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                {{ $badge ?? ($category ?? 'COOCA Business Operating System') }}
                            </p>
                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                {{ $headline ?? $title }}
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-[32rem] lg:max-w-2xl font-normal text-pretty break-words mx-auto lg:mx-0">
                            {{ $subtitle ?? ($description ?? 'Sistem operasional bisnis terpadu untuk UMKM Indonesia. Menghubungkan kasir, pembukuan, stok, dan pelanggan tanpa pencatatan manual ganda.') }}
                        </p>

                        <!-- Action Buttons (Centered on Mobile, Row on Desktop) -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('register') }}"
                                class="px-8 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[50px]">
                                <span>Mulai Sekarang - Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya ingin konsultasi mengenai ' . ($title ?? 'solusi bisnis')) }}"
                                target="_blank" rel="noopener"
                                class="px-6 py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] min-h-[50px]">
                                <i data-lucide="phone-call" class="w-4 h-4 text-emerald-400"></i>
                                <span>Tanya Konsultan (WhatsApp)</span>
                            </a>
                        </div>

                        <!-- Reassurance Checkpoints (Centered on Mobile) -->
                        <div class="pt-2 flex flex-wrap items-center justify-center lg:justify-start gap-y-2 gap-x-5 text-xs text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>100% Gratis Selamanya</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Buku Panduan &amp; Video Bahasa Indonesia</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Bisa dari HP Android &amp; Komputer</span>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Product UI Visualization (5 Cols) -->
                    <div class="lg:col-span-5">
                        @hasSection('subpage_hero_visual')
                            @yield('subpage_hero_visual')
                        @else
                        <div
                            class="bg-[#0B132B]/90 border border-white/10 rounded-[24px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] backdrop-blur-2xl p-5 sm:p-6 space-y-4 text-white group hover:border-sky-400/40 transition-all duration-300">

                            <!-- macOS Window Bar -->
                            <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="w-3 h-3 rounded-full bg-[#FF5F56] shadow-[inset_0_1px_1px_rgba(0,0,0,0.2)]"></span>
                                    <span
                                        class="w-3 h-3 rounded-full bg-[#FFBD2E] shadow-[inset_0_1px_1px_rgba(0,0,0,0.2)]"></span>
                                    <span
                                        class="w-3 h-3 rounded-full bg-[#27C93F] shadow-[inset_0_1px_1px_rgba(0,0,0,0.2)]"></span>
                                </div>
                                <span class="text-xs font-semibold text-slate-300 truncate max-w-[200px] tracking-tight">
                                    Cooca OS &bull; {{ $badge ?? ($category ?? 'Dashboard Bisnis') }}
                                </span>
                                <div class="w-6"></div>
                            </div>

                            <!-- Live Operational Status Widget -->
                            <div class="p-4 rounded-[16px] bg-white/[0.04] border border-white/[0.08] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-slate-400">Status Sinkronisasi</span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>Real-Time Aktif</span>
                                    </span>
                                </div>
                                <div class="flex items-baseline justify-between pt-1">
                                    <div>
                                        <p class="text-2xl font-bold tabular-nums text-white">100% Otomatis</p>
                                        <p class="text-xs text-slate-400">Tanpa hitung manual ulang</p>
                                    </div>
                                    <div
                                        class="w-10 h-10 rounded-[12px] bg-sky-500/15 text-[#00C4D8] flex items-center justify-center border border-sky-400/30">
                                        <i data-lucide="{{ $icon ?? 'zap' }}" class="w-5 h-5"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Workflow Highlights Checklist -->
                            <div class="space-y-2.5 pt-1">
                                <div
                                    class="flex items-start gap-3 p-3 rounded-[14px] bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] transition-colors">
                                    <div
                                        class="w-7 h-7 rounded-[8px] bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/30">
                                        <i data-lucide="layers" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-white">Pencatatan Sekali Jalan</p>
                                        <p class="text-[11.5px] text-slate-300 leading-snug">Transaksi kasir langsung
                                            memotong stok dan membukukan jurnal laba rugi.</p>
                                    </div>
                                </div>

                                <div
                                    class="flex items-start gap-3 p-3 rounded-[14px] bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.06] transition-colors">
                                    <div
                                        class="w-7 h-7 rounded-[8px] bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/30">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-white">Akurasi Data Finansial</p>
                                        <p class="text-[11.5px] text-slate-300 leading-snug">Menghilangkan selisih kas fisik
                                            dan mencegah kebocoran modal bahan baku.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footnote Note -->
                            <div
                                class="flex items-center gap-2 pt-2 border-t border-white/[0.08] text-[11px] text-slate-400">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                <span>Sistem siap pakai dalam 2 menit tanpa instalasi teknis rumit.</span>
                            </div>

                        </div>
                        @endif
                    </div>

                </div>

            </div>
        </section>

        <!-- ═══ SUBPAGE BODY CONTENT CONTAINER ═══ -->
        <div class="py-16 sm:py-20">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-20">

                <!-- ═══ BENTO GRID: KEMAMPUAN & FITUR UTAMA ═══ -->
                @if (isset($features) && is_array($features) && count($features) > 0)
                    <section class="space-y-8">
                        <div class="max-w-2xl space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                Kemampuan &amp; Fitur</p>
                            <h2
                                class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight text-balance break-words">
                                Didesain Khusus untuk Kemudahan Operasional Anda
                            </h2>
                            <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed text-pretty break-words">
                                Setiap fungsi dirancang agar langsung dapat digunakan tanpa perlu pelatihan teknis yang
                                rumit.
                            </p>
                        </div>

                        <!-- Bento Composition: Asymmetric Hierarchy -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">

                            @foreach ($features as $index => $feat)
                                <div
                                    class="{{ $loop->first ? 'md:col-span-2 lg:col-span-2' : 'col-span-1' }} p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-4 hover:border-[#007AFF]/30 transition-all">

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div
                                                class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold shrink-0">
                                                <i data-lucide="{{ $feat['icon'] ?? 'check-circle' }}" class="w-6 h-6"></i>
                                            </div>
                                            @if ($loop->first)
                                                <span
                                                    class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-3 py-1 rounded-[8px] bg-[#007AFF]/10 shrink-0">
                                                    Fungsi Utama
                                                </span>
                                            @else
                                                <span
                                                    class="text-xs font-mono text-[#8E8E93] dark:text-[#98989D] shrink-0">0{{ $index + 1 }}</span>
                                            @endif
                                        </div>

                                        <h3
                                            class="{{ $loop->first ? 'text-xl sm:text-2xl' : 'text-lg' }} font-bold text-[#1D1D1F] dark:text-[#F5F5F7] leading-snug text-balance break-words">
                                            {{ $feat['title'] }}
                                        </h3>

                                        <p class="text-sm sm:text-base text-[#48484A] dark:text-[#AEAEB2] leading-relaxed text-pretty break-words">
                                            {{ $feat['desc'] }}
                                        </p>
                                    </div>

                                    @if ($loop->first)
                                        <div
                                            class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Terintegrasi penuh dengan seluruh laporan dan kasir POS Cooca</span>
                                        </div>
                                    @endif

                                </div>
                            @endforeach

                        </div>
                    </section>
                @endif

                <!-- Custom Slot / Injected Content -->
                @yield('subpage_content')

                <!-- ═══ BOTTOM CONVERSION CARD (Hero Midnight Blue Glow Surface) ═══ -->
                <section
                    class="relative p-8 sm:p-12 rounded-[24px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden">

                    <!-- Ambient Glows (Identik Hero Section) -->
                    <div
                        class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#007AFF]/20 rounded-full blur-[130px] pointer-events-none">
                    </div>
                    <div
                        class="absolute -bottom-24 left-1/4 w-[350px] h-[350px] bg-[#00C4D8]/15 rounded-full blur-[110px] pointer-events-none">
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

                        <div class="lg:col-span-8 space-y-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#00C4D8]">
                                Langkah Mudah Berikutnya
                            </p>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight text-balance break-words">
                                Mulai Otomatisasi Bisnis Anda Hari Ini
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl font-normal text-pretty break-words">
                                Bergabunglah bersama ribuan pengusaha UMKM di Indonesia yang telah menghemat waktu dan
                                meningkatkan kepastian laba bersama Cooca. Tanpa biaya pendaftaran, tanpa kartu kredit.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition min-h-[48px]">
                                <span>Daftar Gratis Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>

                    </div>
                </section>

            </div>
        </div>
    @endsection
