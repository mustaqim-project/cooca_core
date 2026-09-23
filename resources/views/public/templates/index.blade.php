@extends('layouts.public_marketing')

@section('title', 'Download Gratis Template Pembukuan & Excel UMKM | Cooca')
@section('description', 'Koleksi template pembukuan Excel gratis untuk UMKM: Buku Kas Harian Warung, Laporan Keuangan Sederhana, Kartu Stok Opname, dan Invoice Profesional.')
@section('keywords', 'template pembukuan excel gratis, download format buku kas warung, template laporan laba rugi sederhana, excel stok opname toko, invoice gratis umkm')

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

            <!-- 2-Grid Hero Section -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-4">
                <!-- Left: Headline, Info, Quick Action -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Template Spreadsheet Resmi UMKM</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Format Excel Pembukuan Praktis Siap Pakai.
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Koleksi spreadsheet pembukuan usaha yang sudah dilengkapi rumus otomatis. Didesain rapi, bersih, dan mudah diisi dari laptop maupun ponsel tanpa perlu keahlian akuntansi khusus.
                    </p>

                    <!-- Trust indicators for UMKM 40-65 -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="calculator" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Rumus Otomatis</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Saldo & total terjumlah otomatis</div>
                        </div>

                        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Bebas Macro</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Aman dibuka di Excel & WPS</div>
                        </div>

                        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="download" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>100% Gratis</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Langsung unduh tanpa biaya</div>
                        </div>
                    </div>

                    <!-- Direct Primary Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                        <a href="#daftar-template"
                            class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Pilih & Download Template</span>
                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('register') }}"
                            class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-black/[0.03] dark:hover:bg-white/[0.06] font-semibold text-sm flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="zap" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Pakai Kasir Otomatis (Cloud)</span>
                        </a>
                    </div>
                </div>

                <!-- Right: High-Fidelity Spreadsheet UI Simulation -->
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                <span class="text-xs font-mono font-semibold text-[#6E6E73] dark:text-[#86868B] ml-2">Buku_Kas_Warung_2026.xlsx</span>
                            </div>
                            <span class="text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Rumus Aktif
                            </span>
                        </div>

                        <!-- Mini Sheet Grid -->
                        <div class="overflow-hidden rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] text-xs">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-black/[0.03] dark:bg-white/[0.05] text-[#6E6E73] dark:text-[#86868B] font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                                        <th class="p-2.5 font-mono">Tgl</th>
                                        <th class="p-2.5">Keterangan</th>
                                        <th class="p-2.5 text-right font-mono">Masuk</th>
                                        <th class="p-2.5 text-right font-mono">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] font-mono">
                                    <tr class="text-[#1D1D1F] dark:text-[#F5F5F7]">
                                        <td class="p-2.5 text-[#6E6E73]">23/09</td>
                                        <td class="p-2.5 font-sans">Kas Awal Toko</td>
                                        <td class="p-2.5 text-right text-[#34C759]">500.000</td>
                                        <td class="p-2.5 text-right font-bold">500.000</td>
                                    </tr>
                                    <tr class="text-[#1D1D1F] dark:text-[#F5F5F7]">
                                        <td class="p-2.5 text-[#6E6E73]">23/09</td>
                                        <td class="p-2.5 font-sans">Penjualan Siang</td>
                                        <td class="p-2.5 text-right text-[#34C759]">1.450.000</td>
                                        <td class="p-2.5 text-right font-bold">1.950.000</td>
                                    </tr>
                                    <tr class="text-[#1D1D1F] dark:text-[#F5F5F7] bg-black/[0.01] dark:bg-white/[0.02]">
                                        <td class="p-2.5 text-[#6E6E73]">23/09</td>
                                        <td class="p-2.5 font-sans">Kulakan Telur (1 Rak)</td>
                                        <td class="p-2.5 text-right text-[#FF3B30]">-280.000</td>
                                        <td class="p-2.5 text-right font-bold text-[#007AFF]">1.670.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-xs font-semibold flex items-center justify-between">
                            <span>Status File: Siap Diunduh</span>
                            <span class="font-mono">Format .XLSX</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bento Grid of Templates -->
            <section id="daftar-template" class="space-y-8 pt-4">
                <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Katalog Spreadsheet
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight mt-1">
                        Pilih Template Sesuai Kebutuhan Usaha
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($templates as $tpl)
                        <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[22px] p-6 sm:p-7 flex flex-col justify-between shadow-sm hover:border-[#007AFF]/30 transition-all group">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                        {{ $tpl['category'] }}
                                    </span>
                                    <span class="text-xs text-[#6E6E73] dark:text-[#86868B] font-mono px-2 py-0.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06]">
                                        {{ $tpl['format'] }}
                                    </span>
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                        {{ $tpl['name'] }}
                                    </h3>
                                    <p class="text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed mt-2 line-clamp-3">
                                        {{ $tpl['description'] }}
                                    </p>
                                </div>

                                @if (!empty($tpl['highlights']))
                                    <div class="space-y-2 pt-3 border-t border-black/[0.04] dark:border-white/[0.06]">
                                        @foreach (array_slice($tpl['highlights'], 0, 3) as $highlight)
                                            <div class="flex items-start gap-2 text-xs text-[#1D1D1F] dark:text-[#F5F5F7]">
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                                                <span class="leading-tight">{{ $highlight }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="pt-5 mt-5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-3">
                                <span class="text-xs font-bold text-[#34C759] dark:text-[#30D158]">
                                    Gratis
                                </span>
                                <a href="{{ route('template.show', $tpl['slug']) }}"
                                    class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all">
                                    <span>Download</span>
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Funnel to Cooca App (Apple Inset Enterprise Banner) -->
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white flex flex-col lg:flex-row items-center justify-between gap-8 shadow-sm">
                <div class="space-y-3 max-w-xl text-center lg:text-left">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5">
                        <i data-lucide="zap" class="w-4 h-4 text-[#FFBD2E]"></i>
                        <span>Ingin Lebih Otomatis Tanpa Excel?</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Coba Aplikasi Kasir COOCA 100% Gratis
                    </h3>
                    <p class="text-sm text-[#86868B] leading-relaxed">
                        Setiap transaksi langsung otomatis membuat laporan laba rugi, memotong stok barang gudang, dan mencetak struk kasir tanpa Anda harus mengetik rumus manual di Excel.
                    </p>
                </div>
                <a href="{{ route('register') }}"
                    class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center gap-2 shrink-0 shadow-sm active:scale-95 transition-all">
                    <span>Daftar Akun Gratis Sekarang</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </section>

        </div>
    </div>
@endsection

