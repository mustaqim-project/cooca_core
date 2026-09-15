@extends('layouts.public_marketing')

@section('title', 'Download Gratis Template Pembukuan & Excel UMKM | Cooca UMKM')
@section('description', 'Koleksi template pembukuan Excel gratis untuk UMKM: Buku Kas Harian Warung, Laporan Keuangan Sederhana, Kartu Stok Opname, dan Invoice Profesional.')
@section('keywords', 'template pembukuan excel gratis, download format buku kas warung, template laporan laba rugi sederhana, excel stok opname toko, invoice gratis umkm')

@section('content')
<div class="pt-10 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">

        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] font-bold text-xs">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Free Downloadable Resources</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-tight">
                Template &amp; Format Excel <span class="text-[#007AFF] dark:text-[#0A84FF]">Gratis untuk UMKM</span>
            </h1>
            <p class="text-sm sm:text-base text-[#6E6E73] dark:text-[#86868B] max-w-2xl mx-auto leading-relaxed">
                Format spreadsheet siap pakai dengan formula otomatis standar Apple HIG. Didesain khusus untuk mempermudah operasional toko, warung, kafe, dan produsen rumahan tanpa rumus rumit.
            </p>
        </div>

        <!-- Apple Bento Grid of Templates -->
        <div class="grid grid-cols-2 md:grid-cols-2 gap-3 sm:gap-6 lg:gap-8">
            @foreach($templates as $tpl)
            @if($loop->first)
            <!-- Hero Featured Bento Template (Spans 2-col on Mobile & Desktop) -->
            <div class="col-span-2 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[24px] sm:rounded-[28px] p-5 sm:p-8 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-[#007AFF]/30 transition-all group">
                <div class="space-y-3.5 sm:space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </div>
                            <span class="px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]">
                                {{ $tpl['category'] }} • Rekomendasi Utama
                            </span>
                        </div>
                        <span class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] font-mono px-2.5 py-1 rounded-full bg-black/[0.03] dark:bg-white/[0.05]">{{ $tpl['format'] }}</span>
                    </div>

                    <div>
                        <h2 class="text-base sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                            {{ $tpl['name'] }}
                        </h2>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed mt-1">
                            {{ $tpl['description'] }}
                        </p>
                    </div>

                    @if(!empty($tpl['highlights']))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                        @foreach($tpl['highlights'] as $highlight)
                        <div class="flex items-center gap-2 text-xs text-[#1D1D1F]/80 dark:text-[#F5F5F7]/80">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#34C759] dark:text-[#30D158] shrink-0"></i>
                            <span class="truncate">{{ $highlight }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="pt-4 sm:pt-6 mt-4 sm:mt-6 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#34C759] dark:text-[#30D158]">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>100% Gratis Selamanya</span>
                    </div>
                    <a href="{{ route('template.show', $tpl['slug']) }}" class="px-5 py-2.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Download Template</span>
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
            @else
            <!-- Compact Bento Template Card (Spans 1-col on Mobile, 1-col on Desktop) -->
            <div class="col-span-1 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[20px] sm:rounded-[24px] p-4 sm:p-7 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-[#007AFF]/30 transition-all group">
                <div class="space-y-2.5 sm:space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2 sm:px-2.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wider bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] truncate max-w-[100px] sm:max-w-none">
                            {{ $tpl['category'] }}
                        </span>
                        <span class="text-[10px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] font-mono px-2 py-0.5 rounded-full bg-black/[0.03] dark:bg-white/[0.05]">{{ $tpl['format'] }}</span>
                    </div>

                    <div>
                        <h2 class="text-xs sm:text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug line-clamp-2">
                            {{ $tpl['name'] }}
                        </h2>
                        <p class="text-[11px] sm:text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed mt-1 line-clamp-2 hidden sm:block">
                            {{ $tpl['description'] }}
                        </p>
                    </div>

                    @if(!empty($tpl['highlights']))
                    <div class="space-y-1.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06] hidden sm:block">
                        @foreach(array_slice($tpl['highlights'], 0, 2) as $highlight)
                        <div class="flex items-center gap-2 text-[11px] text-[#1D1D1F]/80 dark:text-[#F5F5F7]/80">
                            <i data-lucide="check" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                            <span class="truncate">{{ $highlight }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="pt-3 sm:pt-4 mt-3 sm:mt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-[#34C759] dark:text-[#30D158] hidden sm:inline">Gratis</span>
                    <a href="{{ route('template.show', $tpl['slug']) }}" class="w-full sm:w-auto px-3 sm:px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[11px] sm:text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all">
                        <span>Download</span>
                        <i data-lucide="download" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- Funnel to Cooca UMKM App (Apple Inset Enterprise Banner) -->
        <div class="p-8 sm:p-12 rounded-[28px] bg-[#161618] border border-white/[0.08] text-white flex flex-col lg:flex-row items-center justify-between gap-8 shadow-sm">
            <div class="space-y-3 max-w-xl text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.08] text-[#0A84FF] text-xs font-semibold">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>Solusi Otomatisasi Cloud</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Ingin Pencatatan Tanpa Input Manual di Excel?</h3>
                <p class="text-xs sm:text-sm text-[#86868B] leading-relaxed">
                    Dengan Cooca UMKM, setiap transaksi kasir langsung menghasilkan laporan keuangan, mencatat buku kas harian, dan memotong stok barang secara otomatis.
                </p>
            </div>
            <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center gap-2 shrink-0 shadow-sm active:scale-95 transition-all">
                <span>Daftar Akun Gratis Selamanya</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

    </div>
</div>
@endsection
