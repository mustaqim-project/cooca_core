@extends('layouts.public_marketing')

@section('title', $solution['title'] . ' - 100% Gratis Selamanya | Cooca')
@section('description', $solution['subheadline'])
@section('keywords', strtolower($solution['title']) . ', aplikasi kasir gratis indonesia, software pos ' .
    strtolower($solution['badge']) . ', aplikasi pembukuan ' . strtolower($solution['slug']))

@section('content')
    <div class="pt-8 pb-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

            <!-- Breadcrumbs (Apple Inset Style) -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-[#6E6E73] dark:text-[#86868B]">Solusi Industri</span>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">{{ $solution['badge'] }}</span>
            </nav>

            <!-- Hero Section -->
            <div class="max-w-3xl space-y-4">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>{{ $solution['badge'] }}</span>
                </div>
                <h1
                    class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight">
                    {{ $solution['headline'] }}
                </h1>
                <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                    {{ $solution['subheadline'] }}
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                    <a href="{{ route('register') }}"
                        class="px-8 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                        <span>Mulai Sekarang - 100% Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20{{ urlencode($solution['title']) }}"
                        target="_blank"
                        class="px-6 py-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] text-xs font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                        <i data-lucide="phone" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                        <span>Tanya Konsultan (WhatsApp)</span>
                    </a>
                </div>
            </div>

            <!-- Section: Masalah Klasik (Apple Bento Inset Card) -->
            <div
                class="p-5 sm:p-8 rounded-[24px] sm:rounded-[28px] bg-[#FF3B30]/[0.03] dark:bg-[#FF453A]/[0.06] border border-[#FF3B30]/15 space-y-4 sm:space-y-6">
                <div class="max-w-2xl">
                    <span
                        class="text-[10px] uppercase tracking-wider font-bold text-[#FF3B30] dark:text-[#FF453A] block">Tantangan
                        Sehari-hari</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">Sering Mengalami
                        Masalah Ini di Usaha Anda?</h2>
                </div>
                <!-- Bento 2-Col Mobile / 3-Col Desktop -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-5">
                    @foreach ($solution['pain_points'] as $index => $pain)
                        <div
                            class="{{ $loop->first ? 'col-span-2 md:col-span-1' : 'col-span-1' }} p-3.5 sm:p-5 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-2 sm:space-y-3 shadow-sm">
                            <div
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] font-bold text-xs flex items-center justify-center font-mono">
                                0{{ $index + 1 }}
                            </div>
                            <p class="text-[11px] sm:text-xs text-[#1D1D1F]/80 dark:text-[#F5F5F7]/80 leading-relaxed">
                                {{ $pain }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Section: Fitur Solusi Khusus (Apple Bento Grid) -->
            <div class="space-y-6 sm:space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span
                        class="text-[10px] uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Fitur
                        Unggulan</span>
                    <h2 class="text-xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Bagaimana Cooca Membantu Bisnis Anda</h2>
                </div>
                <!-- Bento 2-Col Mobile / 2-Col Desktop -->
                <div class="grid grid-cols-2 md:grid-cols-2 gap-3 sm:gap-6">
                    @foreach ($solution['features'] as $feat)
                        <div
                            class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-7 rounded-[20px] sm:rounded-[24px] space-y-2.5 sm:space-y-3 shadow-sm hover:shadow-md hover:border-[#007AFF]/30 active:scale-[0.98] transition-all">
                            <div
                                class="w-8 h-8 sm:w-10 sm:h-10 rounded-[12px] sm:rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <h3 class="text-xs sm:text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] leading-snug">
                                {{ $feat['title'] }}</h3>
                            <p
                                class="text-[11px] sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed line-clamp-3 sm:line-clamp-none">
                                {{ $feat['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Testimonial Box (Apple Inset Card) -->
            <div
                class="p-8 sm:p-10 rounded-[28px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex flex-col md:flex-row items-center gap-6 sm:gap-8 shadow-sm">
                <div
                    class="w-14 h-14 rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0 text-xl font-bold font-mono">
                    <i data-lucide="quote" class="w-6 h-6"></i>
                </div>
                <div class="space-y-3">
                    <p class="text-sm sm:text-base text-[#1D1D1F]/90 dark:text-[#F5F5F7]/90 italic leading-relaxed">
                        "{{ $solution['testimonial']['quote'] }}"
                    </p>
                    <div>
                        <div class="font-bold text-sm text-[#1D1D1F] dark:text-[#F5F5F7]">
                            {{ $solution['testimonial']['author'] }}</div>
                        <div class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold">
                            {{ $solution['testimonial']['business'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Cross-Link to Other Verticals (Bento Grid) -->
            <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Solusi Kasir Industri Lainnya</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($otherSolutions as $os)
                        <a href="{{ route('solusi.show', $os['slug']) }}"
                            class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 rounded-[20px] group hover:border-[#007AFF]/30 hover:shadow-sm hover:-translate-y-0.5 transition-all">
                            <span
                                class="text-[10px] uppercase font-bold text-[#007AFF] dark:text-[#0A84FF]">{{ $os['badge'] }}</span>
                            <h4
                                class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                {{ $os['title'] }}</h4>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Final CTA (Apple Inset Enterprise Banner) -->
            <div
                class="p-8 sm:p-12 rounded-[28px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.08] text-[#0A84FF] text-xs font-semibold mx-auto">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Semua Fitur Tersedia 100% Gratis</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">Mulai Digitalisasi Usaha Anda Hari
                    Ini</h3>
                <p class="text-xs sm:text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Tidak perlu beli alat mahal. Cukup gunakan HP Android, tablet, atau laptop yang sudah Anda miliki. 100%
                    gratis tanpa batasan waktu atau transaksi.
                </p>
                <div class="pt-2">
                    <a href="{{ route('register') }}"
                        class="px-8 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Daftar Gratis Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection
