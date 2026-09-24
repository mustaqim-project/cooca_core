@extends('layouts.public_marketing')

@section('title', 'Download Gratis Template Pembukuan & Excel UMKM | COOCA')
@section('description', 'Koleksi template pembukuan Excel gratis untuk UMKM: Buku Kas Harian Warung, Laporan Keuangan Sederhana, Kartu Stok Opname, dan Invoice Profesional.')
@section('og_title', 'Download Gratis Template Pembukuan & Excel UMKM | COOCA')
@section('og_description', 'Koleksi template pembukuan Excel gratis untuk UMKM: Buku Kas Harian Warung, Laporan Keuangan Sederhana, Kartu Stok Opname, dan Invoice.')
@section('canonical', route('template.index'))
@section('og_type', 'website')
@section('keywords', 'template pembukuan excel gratis, download format buku kas warung, template laporan laba rugi sederhana, excel stok opname toko, invoice gratis umkm')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Koleksi Template Pembukuan Excel UMKM Gratis COOCA",
  "description": "Koleksi template spreadsheet Excel siap pakai dengan formula otomatis untuk UMKM Indonesia.",
  "url": "{{ route('template.index') }}",
  "publisher": {
    "@type": "Organization",
    "name": "COOCA Indonesia",
    "url": "{{ url('/') }}"
  }
}
</script>
@endpush

@section('content')
<div x-data="{
    refreshIcons() {
        this.$nextTick(() => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }
}" x-init="refreshIcons()"
class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION: 2-Grid Bento Apple HIG Canvas ══════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="pt-12 sm:pt-16 lg:pt-20 pb-12 sm:pb-16 border-b border-black/[0.06] dark:border-white/[0.08]">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Template Spreadsheet UMKM</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- KIRI: Headline, Value Proposition & Actions (Mobile Center, Desktop Left ~ 5 Cols) -->
                <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Kicker -->
                        <div class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            TEMPLATE SPREADSHEET RESMI UMKM
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Format Excel Pembukuan Praktis Siap Pakai
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Koleksi spreadsheet pembukuan usaha yang sudah dilengkapi rumus otomatis. Didesain rapi, bersih, dan mudah diisi dari laptop maupun ponsel tanpa perlu keahlian akuntansi khusus.
                    </p>

                    <!-- Trust Indicators for UMKM -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-left w-full">
                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                <span class="leading-snug">Rumus Otomatis</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">Saldo otomatis terjumlah</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"></i>
                                <span class="leading-snug">Bebas Macro</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">Aman di Excel &amp; WPS</div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <i data-lucide="download" class="w-4 h-4 text-amber-500 shrink-0"></i>
                                <span class="leading-snug">100% Gratis</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">Unduh tanpa biaya</div>
                        </div>
                    </div>

                    <!-- Direct Actions (Centered on Mobile, Row on Desktop) -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                        <a href="#daftar-template"
                            class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition shadow-sm min-h-[48px]">
                            <span>Pilih &amp; Download Template</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ route('register') }}"
                            class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] hover:bg-slate-50 dark:hover:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] text-slate-800 dark:text-slate-200 text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] min-h-[48px]">
                            <i data-lucide="zap" class="w-4 h-4 text-amber-500 shrink-0"></i>
                            <span>Pakai Kasir Cloud Otomatis</span>
                        </a>
                    </div>
                </div>

                <!-- KANAN: High-Fidelity Spreadsheet UI Simulation (7 Cols ~ 58%) -->
                <div class="lg:col-span-7">
                    <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] p-6 sm:p-7 shadow-sm border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 ml-2">
                                    Buku_Kas_Warung_2026.xlsx
                                </span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Rumus Aktif
                            </span>
                        </div>

                        <!-- Mini Sheet Grid Table -->
                        <div class="overflow-hidden rounded-[16px] border border-black/[0.06] dark:border-white/[0.08] text-xs">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-[#2C2C2E] text-slate-700 dark:text-slate-300 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                                        <th class="p-2.5 font-mono">Tgl</th>
                                        <th class="p-2.5">Keterangan</th>
                                        <th class="p-2.5 text-right font-mono">Masuk</th>
                                        <th class="p-2.5 text-right font-mono">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04] font-mono">
                                    <tr class="bg-white dark:bg-[#1C1C1E]">
                                        <td class="p-2.5 text-slate-400">23/09</td>
                                        <td class="p-2.5 font-sans font-medium text-slate-800 dark:text-slate-200">Kas Awal Toko</td>
                                        <td class="p-2.5 text-right text-emerald-600 dark:text-emerald-400">500.000</td>
                                        <td class="p-2.5 text-right font-bold text-slate-900 dark:text-white">500.000</td>
                                    </tr>
                                    <tr class="bg-white dark:bg-[#1C1C1E]">
                                        <td class="p-2.5 text-slate-400">23/09</td>
                                        <td class="p-2.5 font-sans font-medium text-slate-800 dark:text-slate-200">Penjualan Siang</td>
                                        <td class="p-2.5 text-right text-emerald-600 dark:text-emerald-400">1.450.000</td>
                                        <td class="p-2.5 text-right font-bold text-slate-900 dark:text-white">1.950.000</td>
                                    </tr>
                                    <tr class="bg-slate-50/50 dark:bg-white/[0.02]">
                                        <td class="p-2.5 text-slate-400">23/09</td>
                                        <td class="p-2.5 font-sans font-medium text-slate-800 dark:text-slate-200">Kulakan Telur (1 Rak)</td>
                                        <td class="p-2.5 text-right text-rose-500">-280.000</td>
                                        <td class="p-2.5 text-right font-bold text-[#007AFF] dark:text-[#0A84FF]">1.670.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
                            <span>Status File: Siap Diunduh Langsung</span>
                            <span class="font-mono text-[11px]">Format .XLSX Universal</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. TEMPLATES BENTO CATALOG ═══════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-16">

        <section id="daftar-template" class="space-y-8 scroll-mt-24">
            <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                    KATALOG SPREADSHEET
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Pilih Template Sesuai Kebutuhan Usaha
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($templates as $tpl)
                    <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[22px] p-6 sm:p-7 flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                    {{ $tpl['category'] }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                    {{ $tpl['format'] }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                    {{ $tpl['name'] }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-2 line-clamp-3">
                                    {{ $tpl['description'] }}
                                </p>
                            </div>

                            @if (!empty($tpl['highlights']))
                                <div class="space-y-1.5 pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                                    @foreach (array_slice($tpl['highlights'], 0, 3) as $highlight)
                                        <div class="p-2 rounded-[10px] bg-slate-50 dark:bg-[#2C2C2E] border border-black/[0.03] dark:border-white/[0.04] flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                            <div class="w-4 h-4 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                                <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                            </div>
                                            <span class="leading-snug truncate">{{ $highlight }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="pt-5 mt-5 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Gratis Selamanya
                            </span>
                            <a href="{{ route('template.show', $tpl['slug']) }}"
                                class="h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition">
                                <span>Download</span>
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ═══ CONVERSION FUNNEL BANNER ═══ -->
        <section class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-2 max-w-xl text-center lg:text-left">
                <div class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <span>INGIN LEBIH OTOMATIS TANPA EXCEL MANUAL?</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Coba Aplikasi Kasir COOCA 100% Gratis
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Setiap transaksi otomatis membuat laporan laba rugi, memotong stok barang gudang, dan mencetak struk kasir tanpa Anda harus mengetik rumus manual di Excel.
                </p>
            </div>
            <a href="{{ route('register') }}"
                class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center gap-2 shrink-0 shadow-xs active:scale-[0.98] transition">
                <span>Daftar Akun Gratis Sekarang</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </section>

    </div>
</div>
@endsection
