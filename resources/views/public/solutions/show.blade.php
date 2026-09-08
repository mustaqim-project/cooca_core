@extends('layouts.public_marketing')

@section('title', $solution['title'] . ' — 100% Gratis Selamanya | Cooca UMKM')
@section('description', $solution['subheadline'])
@section('keywords', strtolower($solution['title']) . ', aplikasi kasir gratis indonesia, software pos ' . strtolower($solution['badge']) . ', aplikasi pembukuan ' . strtolower($solution['slug']))

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-8">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <span class="text-slate-500">Solusi Industri</span>
            <span>/</span>
            <span class="text-indigo-400 font-semibold">{{ $solution['badge'] }}</span>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-3xl mb-16">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs mb-4">
                <i data-lucide="store" class="w-4 h-4"></i>
                <span>{{ $solution['badge'] }}</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                {{ $solution['headline'] }}
            </h1>
            <p class="text-sm sm:text-base text-slate-300 mt-4 leading-relaxed">
                {{ $solution['subheadline'] }}
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                <a href="{{ route('register') }}" class="glow-btn px-8 py-3.5 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xl shadow-indigo-500/25">
                    <span>Mulai Sekarang - 100% Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="https://wa.me/6282337499577?text=Halo%20saya%20tertarik%20dengan%20solusi%20{{ urlencode($solution['title']) }}" target="_blank" class="px-6 py-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold flex items-center justify-center gap-2">
                    <i data-lucide="phone" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tanya Konsultan (WhatsApp)</span>
                </a>
            </div>
        </div>

        <!-- Section: Masalah Klasik (Pain Points) -->
        <div class="mb-16 p-8 rounded-3xl bg-rose-950/20 border border-rose-500/20">
            <div class="max-w-2xl mb-6">
                <span class="text-[10px] uppercase tracking-wider font-bold text-rose-400 block">Tantangan Sehari-hari</span>
                <h2 class="text-xl sm:text-2xl font-black text-white mt-1">Sering Mengalami Masalah Ini di Usaha Anda?</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($solution['pain_points'] as $index => $pain)
                <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 font-bold text-sm flex items-center justify-center font-mono">
                        0{{ $index + 1 }}
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $pain }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Section: Fitur Solusi Khusus -->
        <div class="mb-20">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-[10px] uppercase tracking-wider font-bold text-indigo-400 block">Fitur Unggulan</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-1">Bagaimana Cooca UMKM Membantu Bisnis Anda</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($solution['features'] as $feat)
                <div class="glass-card p-6 sm:p-7 rounded-3xl space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">{{ $feat['title'] }}</h3>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">{{ $feat['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Testimonial Box -->
        <div class="mb-20 p-8 sm:p-10 rounded-3xl bg-slate-950/80 border border-slate-800 flex flex-col md:flex-row items-center gap-8">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-white shrink-0 text-2xl font-bold shadow-xl">
                “
            </div>
            <div class="space-y-3">
                <p class="text-sm sm:text-base text-slate-200 italic leading-relaxed">
                    "{{ $solution['testimonial']['quote'] }}"
                </p>
                <div>
                    <div class="font-bold text-sm text-white">{{ $solution['testimonial']['author'] }}</div>
                    <div class="text-xs text-indigo-400 font-semibold">{{ $solution['testimonial']['business'] }}</div>
                </div>
            </div>
        </div>

        <!-- Cross-Link to Other Verticals -->
        <div class="border-t border-slate-800 pt-12">
            <h3 class="text-base font-bold text-white mb-6">Solusi Kasir Industri Lainnya:</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($otherSolutions as $os)
                <a href="{{ route('solusi.show', $os['slug']) }}" class="glass-card p-4 rounded-2xl group hover:border-indigo-500/40 transition-all">
                    <span class="text-[10px] uppercase font-bold text-indigo-400">{{ $os['badge'] }}</span>
                    <h4 class="text-xs font-bold text-white mt-1 group-hover:text-indigo-400 transition-colors">{{ $os['title'] }}</h4>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Final CTA -->
        <div class="mt-20 p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-indigo-900/40 via-purple-900/30 to-slate-900 border border-indigo-500/20 text-center space-y-4">
            <h3 class="text-2xl sm:text-3xl font-black text-white">Mulai Digitalisasi Usaha Anda Hari Ini</h3>
            <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed">
                Tidak perlu beli alat mahal. Cukup gunakan HP Android, tablet, atau laptop Anda. 100% Gratis selamanya tanpa batas waktu.
            </p>
            <div class="pt-2">
                <a href="{{ route('register') }}" class="glow-btn px-8 py-3.5 rounded-xl text-white font-bold text-xs inline-flex items-center gap-2 shadow-xl">
                    <span>Daftar Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
