@extends('layouts.public_marketing')

@section('title', 'Download Gratis Template Pembukuan & Excel UMKM | Cooca')
@section('description',
    'Koleksi template pembukuan Excel gratis untuk UMKM: Buku Kas Harian Warung, Laporan Keuangan
    Sederhana, Kartu Stok Opname, dan Invoice Profesional.')
@section('keywords',
    'template pembukuan excel gratis, download format buku kas warung, template laporan laba rugi
    sederhana, excel stok opname toko, invoice gratis umkm')

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300 min-h-screen">

        <!-- ═══ HERO SECTION: Midnight Dark with Ambient Glows ═══ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-20 overflow-hidden border-b border-white/10 w-full min-w-full">
            <!-- Dual Ambient Glows -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <!-- Left: Headline, Info, Quick Action (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-md">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Template Spreadsheet Resmi UMKM</span>
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-white tracking-tight leading-[1.15]">
                            Format Excel Pembukuan Praktis <span class="text-[#00C4D8]">Siap Pakai.</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed max-w-xl">
                            Koleksi spreadsheet pembukuan usaha yang sudah dilengkapi rumus otomatis. Didesain rapi, bersih,
                            dan
                            mudah diisi dari laptop maupun ponsel tanpa perlu keahlian akuntansi khusus.
                        </p>

                        <!-- Trust indicators for UMKM -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="calculator" class="w-4 h-4 text-emerald-400"></i>
                                    <span>Rumus Otomatis</span>
                                </div>
                                <div class="text-[12px] text-slate-300 mt-1">Saldo & total terjumlah otomatis</div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-[#00C4D8]"></i>
                                    <span>Bebas Macro</span>
                                </div>
                                <div class="text-[12px] text-slate-300 mt-1">Aman dibuka di Excel & WPS</div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                                <div class="text-xs font-bold text-white flex items-center gap-1.5">
                                    <i data-lucide="download" class="w-4 h-4 text-amber-400"></i>
                                    <span>100% Gratis</span>
                                </div>
                                <div class="text-[12px] text-slate-300 mt-1">Langsung unduh tanpa biaya</div>
                            </div>
                        </div>

                        <!-- Direct Primary Actions -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="#daftar-template"
                                class="h-12 px-7 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all">
                                <span>Pilih &amp; Download Template</span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('register') }}"
                                class="h-12 px-6 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-white font-semibold text-sm flex items-center justify-center gap-2 backdrop-blur-sm transition-all">
                                <i data-lucide="zap" class="w-4 h-4 text-[#00C4D8]"></i>
                                <span>Pakai Kasir Otomatis (Cloud)</span>
                            </a>
                        </div>
                    </div>

                    <!-- Right: High-Fidelity Spreadsheet UI Simulation (5 cols) -->
                    <div class="lg:col-span-5">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-4">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                    <span
                                        class="text-xs font-mono font-semibold text-slate-300 ml-2">Buku_Kas_Warung_2026.xlsx</span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3"></i> Rumus Aktif
                                </span>
                            </div>

                            <!-- Mini Sheet Grid -->
                            <div class="overflow-hidden rounded-xl border border-white/10 text-xs">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white/5 text-slate-300 font-semibold border-b border-white/10">
                                            <th class="p-2.5 font-mono">Tgl</th>
                                            <th class="p-2.5">Keterangan</th>
                                            <th class="p-2.5 text-right font-mono">Masuk</th>
                                            <th class="p-2.5 text-right font-mono">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5 font-mono">
                                        <tr class="text-white">
                                            <td class="p-2.5 text-slate-400">23/09</td>
                                            <td class="p-2.5 font-sans">Kas Awal Toko</td>
                                            <td class="p-2.5 text-right text-emerald-400">500.000</td>
                                            <td class="p-2.5 text-right font-bold text-white">500.000</td>
                                        </tr>
                                        <tr class="text-white">
                                            <td class="p-2.5 text-slate-400">23/09</td>
                                            <td class="p-2.5 font-sans">Penjualan Siang</td>
                                            <td class="p-2.5 text-right text-emerald-400">1.450.000</td>
                                            <td class="p-2.5 text-right font-bold text-white">1.950.000</td>
                                        </tr>
                                        <tr class="text-white bg-white/[0.02]">
                                            <td class="p-2.5 text-slate-400">23/09</td>
                                            <td class="p-2.5 font-sans">Kulakan Telur (1 Rak)</td>
                                            <td class="p-2.5 text-right text-rose-400">-280.000</td>
                                            <td class="p-2.5 text-right font-bold text-[#00C4D8]">1.670.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center justify-between">
                                <span>Status File: Siap Diunduh</span>
                                <span class="font-mono">Format .XLSX</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ TEMPLATES BENTO CATALOG (Light / Dark Compatible) ═══ -->
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

            <section id="daftar-template" class="space-y-8 scroll-mt-20">
                <div class="border-b border-slate-200/80 dark:border-white/10 pb-4">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Katalog Spreadsheet
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                        Pilih Template Sesuai Kebutuhan Usaha
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($templates as $tpl)
                        <div
                            class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-2xl p-6 sm:p-7 flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 transition-all group">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8]">
                                        {{ $tpl['category'] }}
                                    </span>
                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400 font-mono px-2 py-0.5 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200/80 dark:border-white/10">
                                        {{ $tpl['format'] }}
                                    </span>
                                </div>

                                <div>
                                    <h3
                                        class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-snug">
                                        {{ $tpl['name'] }}
                                    </h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-2 line-clamp-3">
                                        {{ $tpl['description'] }}
                                    </p>
                                </div>

                                @if (!empty($tpl['highlights']))
                                    <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-white/10">
                                        @foreach (array_slice($tpl['highlights'], 0, 3) as $highlight)
                                            <div class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300">
                                                <i data-lucide="check"
                                                    class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5"></i>
                                                <span class="leading-tight">{{ $highlight }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div
                                class="pt-5 mt-5 border-t border-slate-100 dark:border-white/10 flex items-center justify-between gap-3">
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    Gratis
                                </span>
                                <a href="{{ route('template.show', $tpl['slug']) }}"
                                    class="h-11 px-5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all">
                                    <span>Download</span>
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- ═══ CONVERSION FUNNEL BANNER ═══ -->
            <section
                class="relative p-8 sm:p-12 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8">
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="space-y-3 max-w-xl text-center lg:text-left relative z-10">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5">
                        <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                        <span>Ingin Lebih Otomatis Tanpa Excel?</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Coba Aplikasi Kasir COOCA 100% Gratis
                    </h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Setiap transaksi langsung otomatis membuat laporan laba rugi, memotong stok barang gudang, dan
                        mencetak struk kasir tanpa Anda harus mengetik rumus manual di Excel.
                    </p>
                </div>
                <a href="{{ route('register') }}"
                    class="h-12 px-8 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center gap-2 shrink-0 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all relative z-10">
                    <span>Daftar Akun Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </section>

        </div>
    </div>
@endsection
