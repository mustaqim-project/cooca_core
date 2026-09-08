@extends('layouts.public_marketing')

@section('title', 'Download Gratis Template Pembukuan & Excel UMKM | Cooca UMKM')
@section('description', 'Koleksi template pembukuan Excel gratis untuk UMKM: Buku Kas Harian Warung, Laporan Keuangan Sederhana, Kartu Stok Opname, dan Invoice Profesional.')
@section('keywords', 'template pembukuan excel gratis, download format buku kas warung, template laporan laba rugi sederhana, excel stok opname toko, invoice gratis umkm')

@section('content')
<div class="pt-12 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-xs mb-4">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Free Downloadable Resources</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Template &amp; Format Excel <span class="text-gradient-accent">Gratis untuk UMKM</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-400 mt-4 leading-relaxed">
                Format spreadsheet siap pakai dengan formula otomatis. Didesain khusus untuk mempermudah operasional toko, warung, kafe, dan produsen rumahan.
            </p>
        </div>

        <!-- Grid of Templates -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            @foreach($templates as $tpl)
            <div class="glass-card p-6 sm:p-8 rounded-3xl flex flex-col justify-between group">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            {{ $tpl['category'] }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono">{{ $tpl['format'] }}</span>
                    </div>

                    <h2 class="text-xl font-bold text-white group-hover:text-indigo-400 transition-colors">
                        {{ $tpl['name'] }}
                    </h2>

                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        {{ $tpl['description'] }}
                    </p>

                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        @foreach($tpl['highlights'] as $highlight)
                        <div class="flex items-center gap-2 text-xs text-slate-300">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>{{ $highlight }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400">100% Gratis</span>
                    <a href="{{ route('template.show', $tpl['slug']) }}" class="glow-btn px-5 py-2.5 rounded-xl text-white font-bold text-xs flex items-center gap-2 shadow-md">
                        <span>Download Gratis</span>
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Funnel to Cooca UMKM App -->
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-slate-900 to-indigo-950/60 border border-indigo-500/20 flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-3 max-w-xl text-center lg:text-left">
                <h3 class="text-2xl font-black text-white">Ingin Pencatatan Tanpa Input Manual di Excel?</h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Dengan Cooca UMKM, setiap transaksi kasir langsung menghasilkan laporan keuangan, mencatat buku kas harian, dan memotong stok barang secara otomatis.
                </p>
            </div>
            <a href="{{ route('register') }}" class="glow-btn px-8 py-3.5 rounded-xl text-white font-bold text-xs flex items-center gap-2 shrink-0 shadow-lg">
                <span>Daftar Akun Gratis Selamanya</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

    </div>
</div>
@endsection
