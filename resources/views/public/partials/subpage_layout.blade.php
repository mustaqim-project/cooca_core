@extends('layouts.public_marketing')

@section('title', ($title ?? 'Solusi Bisnis') . ' - Cooca Business Operating System')
@section('description', $description ?? 'Kelola bisnis UMKM lebih cerdas dan terintegrasi dengan COOCA Business Operating System & Omnichannel ERP.')

@section('content')
<div class="pt-6 sm:pt-10 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-20">

        <!-- Breadcrumbs (Clean Apple HIG Hairline Nav) -->
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B] overflow-x-auto py-1">
            <a href="{{ route('landing') }}" class="hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors shrink-0">Beranda</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/20 dark:text-white/20 shrink-0"></i>
            @if(isset($category))
                <span class="shrink-0 text-[#6E6E73] dark:text-[#86868B]">{{ $category }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/20 dark:text-white/20 shrink-0"></i>
            @endif
            <span class="text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold shrink-0">{{ $title ?? 'Detail' }}</span>
        </nav>

        <!-- ═══ HERO SECTION (Mandatory 2-Grid Layout) ═══ -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- KIRI: Headline & CTA (7 Cols) -->
            <div class="lg:col-span-7 space-y-6 text-left">
                <div class="space-y-2">
                    <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        {{ $badge ?? ($category ?? 'COOCA Business Operating System') }}
                    </p>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.12]">
                        {{ $headline ?? $title }}
                    </h1>
                </div>

                <p class="text-base sm:text-lg text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-2xl font-normal">
                    {{ $subtitle ?? $description ?? 'Sistem operasional bisnis terpadu untuk UMKM Indonesia. Menghubungkan kasir, pembukuan, stok, dan pelanggan tanpa pencatatan manual ganda.' }}
                </p>

                <!-- Action Buttons -->
                <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                    <a href="{{ route('register') }}"
                        class="px-8 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all min-h-[50px]">
                        <span>Mulai Sekarang - Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya ingin konsultasi mengenai ' . ($title ?? 'solusi bisnis')) }}"
                        target="_blank"
                        rel="noopener"
                        class="px-6 py-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] min-h-[50px]">
                        <i data-lucide="phone-call" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Tanya Konsultan (WhatsApp)</span>
                    </a>
                </div>

                <!-- Reassurance Checkpoints for UMKM 40-65 -->
                <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-[#6E6E73] dark:text-[#86868B]">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>100% Gratis Selamanya</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Buku Panduan & Video Bahasa Indonesia</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Bisa dari HP Android & Komputer</span>
                    </div>
                </div>
            </div>

            <!-- KANAN: Product UI Visualization (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[24px] shadow-sm overflow-hidden p-5 sm:p-6 space-y-5">
                    
                    <!-- macOS Window Bar -->
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#FF5F56] border border-black/10"></span>
                            <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-black/10"></span>
                            <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-black/10"></span>
                        </div>
                        <span class="text-xs font-semibold text-[#8E8E93] dark:text-[#98989D] truncate max-w-[200px]">
                            Cooca OS &bull; {{ $badge ?? ($category ?? 'Dashboard Bisnis') }}
                        </span>
                        <div class="w-6"></div>
                    </div>

                    <!-- Live Operational Status Widget -->
                    <div class="p-4 rounded-[16px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06] space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-[#6E6E73] dark:text-[#86868B]">Status Sinkronisasi</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>Real-Time Aktif</span>
                            </span>
                        </div>
                        <div class="flex items-baseline justify-between pt-1">
                            <div>
                                <p class="text-2xl font-bold tabular-nums text-[#1D1D1F] dark:text-[#F5F5F7]">100% Otomatis</p>
                                <p class="text-xs text-[#6E6E73] dark:text-[#86868B]">Tanpa hitung manual ulang</p>
                            </div>
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="{{ $icon ?? 'zap' }}" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Highlights Checklist -->
                    <div class="space-y-3 pt-1">
                        <div class="flex items-start gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05]">
                            <div class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pencatatan Sekali Jalan</p>
                                <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-snug">Transaksi kasir langsung memotong stok dan membukukan jurnal laba rugi.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05]">
                            <div class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Akurasi Data Finansial</p>
                                <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-snug">Menghilangkan selisih kas fisik dan mencegah kebocoran modal bahan baku.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footnote Note -->
                    <div class="flex items-center gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-[#8E8E93] dark:text-[#98989D]">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                        <span>Sistem siap pakai dalam 2 menit tanpa instalasi teknis yang rumit.</span>
                    </div>

                </div>
            </div>

        </section>

        <!-- ═══ BENTO GRID: KEMAMPUAN & FITUR UTAMA ═══ -->
        @if(isset($features) && is_array($features) && count($features) > 0)
        <section class="space-y-8">
            <div class="max-w-2xl space-y-2">
                <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Kemampuan &amp; Fitur</p>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                    Didesain Khusus untuk Kemudahan Operasional Anda
                </h2>
                <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    Setiap fungsi dirancang agar langsung dapat digunakan tanpa perlu pelatihan teknis yang rumit.
                </p>
            </div>

            <!-- Bento Composition: Asymmetric Hierarchy -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                
                @foreach($features as $index => $feat)
                <div class="{{ $loop->first ? 'md:col-span-2 lg:col-span-2' : 'col-span-1' }} p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-4 hover:border-[#007AFF]/30 transition-all">
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold">
                                <i data-lucide="{{ $feat['icon'] ?? 'check-circle' }}" class="w-6 h-6"></i>
                            </div>
                            @if($loop->first)
                                <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] px-3 py-1 rounded-[8px] bg-[#007AFF]/10">
                                    Fungsi Utama
                                </span>
                            @else
                                <span class="text-xs font-mono text-[#8E8E93] dark:text-[#98989D]">0{{ $index + 1 }}</span>
                            @endif
                        </div>

                        <h3 class="{{ $loop->first ? 'text-xl sm:text-2xl' : 'text-lg' }} font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            {{ $feat['title'] }}
                        </h3>

                        <p class="text-sm sm:text-base text-[#48484A] dark:text-[#AEAEB2] leading-relaxed">
                            {{ $feat['desc'] }}
                        </p>
                    </div>

                    @if($loop->first)
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
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

        <!-- ═══ BOTTOM CONVERSION CARD (Calm & Trustworthy Apple HIG Surface) ═══ -->
        <section class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <div class="lg:col-span-8 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Langkah Mudah Berikutnya</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Mulai Otomatisasi Bisnis Anda Hari Ini
                    </h2>
                    <p class="text-sm sm:text-base text-[#48484A] dark:text-[#AEAEB2] leading-relaxed max-w-2xl">
                        Bergabunglah bersama ribuan pengusaha UMKM di Indonesia yang telah menghemat waktu dan meningkatkan kepastian laba bersama Cooca. Tanpa biaya pendaftaran, tanpa kartu kredit.
                    </p>
                </div>

                <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                    <a href="{{ route('register') }}"
                        class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition min-h-[48px]">
                        <span>Daftar Gratis Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('landing') }}"
                        class="w-full py-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] text-[#1D1D1F] dark:text-[#F5F5F7] font-semibold text-sm flex items-center justify-center gap-2 transition min-h-[48px]">
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>

            </div>
        </section>

    </div>
</div>
@endsection

