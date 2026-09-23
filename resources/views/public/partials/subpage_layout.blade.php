@extends('layouts.public_marketing')

@section('title', ($title ?? 'Solusi Bisnis') . ' - Cooca Business Operating System')
@section('description', $description ?? 'Kelola bisnis UMKM lebih cerdas dan terintegrasi dengan COOCA Business Operating System & Omnichannel ERP.')

@section('content')
<div class="pt-8 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B] overflow-x-auto py-1">
            <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors shrink-0">COOCA</a>
            <span>/</span>
            @if(isset($category))
                <span class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] shrink-0">{{ $category }}</span>
                <span>/</span>
            @endif
            <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold shrink-0">{{ $title ?? 'Detail' }}</span>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-3xl space-y-5">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-xs font-semibold">
                <i data-lucide="{{ $icon ?? 'layers' }}" class="w-4 h-4"></i>
                <span>{{ $badge ?? ($category ?? 'COOCA Ecosystem') }}</span>
            </div>

            <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                {{ $headline ?? $title }}
            </h1>

            <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                {{ $subtitle ?? $description ?? 'Otomatisasi bisnis menyeluruh, menghubungkan seluruh lini operasional dalam satu kendali terpusat.' }}
            </p>

            <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                <a href="{{ route('register') }}"
                    class="px-8 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.3)] hover:shadow-[0_4px_16px_rgba(0,122,255,0.4)] active:scale-[0.98] transition-all">
                    <span>Mulai Sekarang - Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo COOCA, saya ingin konsultasi mengenai ' . ($title ?? 'sistem')) }}"
                    target="_blank"
                    class="px-6 py-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                    <i data-lucide="phone" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                    <span>Tanya Konsultan (WhatsApp)</span>
                </a>
            </div>
        </div>

        <!-- Bento Grid Highlights / Features -->
        @if(isset($features) && is_array($features) && count($features) > 0)
        <div class="space-y-6">
            <div class="max-w-2xl">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Fitur &amp; Kemampuan</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">Didesain untuk Efisiensi Bisnis Anda</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($features as $feat)
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm hover:border-[#007AFF]/30 transition-all space-y-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold">
                        <i data-lucide="{{ $feat['icon'] ?? 'check-circle' }}" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-base text-[#1D1D1F] dark:text-[#F5F5F7]">{{ $feat['title'] }}</h3>
                    <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">{{ $feat['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Custom Slot / Injected Content -->
        @yield('subpage_content')

        <!-- CTA Bottom Banner -->
        <div class="p-8 sm:p-12 rounded-[28px] bg-gradient-to-br from-[#0F172A] to-[#1E293B] text-white relative overflow-hidden shadow-xl border border-white/10">
            <div class="relative z-10 max-w-2xl space-y-4">
                <span class="text-xs uppercase tracking-wider font-bold text-[#38BDF8]">Siap Memulai Transformasi?</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Tingkatkan Performa Bisnis Anda Bersama COOCA</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Bergabung dengan ribuan pelaku bisnis UMKM di seluruh Indonesia. Mulai gratis tanpa kartu kredit.
                </p>
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}"
                        class="px-6 py-3 rounded-[14px] bg-[#00C2FF] hover:bg-[#00ade5] text-[#0A0E1A] font-bold text-xs flex items-center gap-2 transition active:scale-95 shadow-lg">
                        <span>Daftar Sekarang - Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('landing') }}"
                        class="px-6 py-3 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-medium text-xs flex items-center gap-2 transition">
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
            <!-- Decorative circle glow -->
            <div class="absolute -right-16 -bottom-16 w-80 h-80 bg-[#00C2FF]/15 rounded-full blur-[100px] pointer-events-none"></div>
        </div>

    </div>
</div>
@endsection
