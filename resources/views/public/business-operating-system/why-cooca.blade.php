@extends('layouts.public_marketing')

@section('title', 'Mengapa Memilih COOCA: Solusi Bisnis Terintegrasi vs Terpisah | COOCA')
@section('description', 'Berhenti membayar 5 aplikasi berbeda dan spreadsheet tercecer. COOCA menyatukan kasir POS, stok
    gudang, pembukuan keuangan, dan WhatsApp dalam satu ekosistem.')
@section('keywords', 'kenapa pilih cooca, software terintegrasi vs aplikasi terpisah, kelebihan erp umkm, aplikasi
    bisnis tanpa langganan mahal, efisiensi operasional toko')

    @push('seo')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Mengapa Memilih COOCA Business Operating System",
        "description": "Perbandingan mendalam antara mengelola bisnis dengan software terpisah versus ekosistem terpadu COOCA.",
        "url": "{{ route('public.bos.why-cooca') }}",
        "publisher": {
            "@type": "Organization",
            "name": "COOCA Indonesia",
            "url": "{{ url('/') }}"
        }
    }
    </script>
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Beranda",
                "item": "{{ route('landing') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Business Operating System",
                "item": "{{ route('public.bos.overview') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Kenapa COOCA",
                "item": "{{ route('public.bos.why-cooca') }}"
            }
        ]
    }
    </script>
    @endpush

@section('content')
    <div class="w-full bg-[#F5F5F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] antialiased">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION ═════════════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10">
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[420px] h-[420px] bg-[#00C2FF]/10 rounded-full blur-[130px] pointer-events-none">
            </div>

            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Breadcrumbs -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 pb-6">
                    <a href="{{ route('landing') }}" class="hover:text-[#00C2FF] transition-colors">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <a href="{{ route('public.bos.overview') }}" class="hover:text-[#00C2FF] transition-colors">Business
                        Operating System</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <span class="text-white font-semibold">Kenapa COOCA</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    <!-- Left: Headline & Rationale (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-[#00C2FF] tracking-wide">
                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                            <span>Efisiensi Nyata Tanpa Biaya Tersembunyi</span>
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-black text-white tracking-tight leading-[1.12]">
                            Berhenti Membayar Banyak Software Terpisah yang Tidak Saling Terhubung
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal">
                            Saat bisnis Anda mulai tumbuh, memakai POS dari vendor A, pencatatan stok di vendor B, pembukuan
                            di spreadsheet, dan broadcast WhatsApp di vendor C justru memicu biaya mahal, data selisih, dan
                            waktu terbuang.
                        </p>

                        <!-- CTAs -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('register') }}"
                                class="px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Beralih ke COOCA Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.pricing') }}"
                                class="px-6 py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Bandingkan Paket & Harga</span>
                            </a>
                        </div>

                        <!-- Bullet Reassurances -->
                        <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Hemat biaya hingga 75% per bulan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Satu akun untuk seluruh operasional</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Migrasi data mudah & didampingi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Head-to-Head Comparison Card (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div class="space-y-4">
                            <!-- Old Way Card -->
                            <div class="p-5 rounded-[22px] bg-red-950/40 border border-red-500/30 text-white space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-red-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i> Cara Lama (Terpisah)
                                    </span>
                                    <span class="text-red-300 font-mono font-bold">Rp 1.200.000+ /bln</span>
                                </div>
                                <ul class="space-y-2 text-xs text-slate-300">
                                    <li class="flex items-center gap-2"><i data-lucide="x"
                                            class="w-3.5 h-3.5 text-red-400 shrink-0"></i> Langganan POS: Rp 250rb/bln</li>
                                    <li class="flex items-center gap-2"><i data-lucide="x"
                                            class="w-3.5 h-3.5 text-red-400 shrink-0"></i> Software Gudang: Rp 350rb/bln
                                    </li>
                                    <li class="flex items-center gap-2"><i data-lucide="x"
                                            class="w-3.5 h-3.5 text-red-400 shrink-0"></i> Software Akuntansi: Rp 300rb/bln
                                    </li>
                                    <li class="flex items-center gap-2"><i data-lucide="x"
                                            class="w-3.5 h-3.5 text-red-400 shrink-0"></i> Rekonsiliasi manual 3 jam tiap
                                        malam</li>
                                </ul>
                            </div>

                            <!-- COOCA Way Card -->
                            <div
                                class="p-5 sm:p-6 rounded-[24px] bg-gradient-to-br from-blue-950/80 to-cyan-950/70 border-2 border-[#00C2FF]/60 text-white space-y-3 shadow-xl">
                                <div class="flex items-center justify-between text-xs">
                                    <span
                                        class="font-bold text-[#00C2FF] uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> Cara Terpadu
                                        COOCA
                                    </span>
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-mono font-extrabold bg-[#00C2FF]/20 text-[#00C2FF]">
                                        Mulai Rp 0 - Rp 99rb /bln
                                    </span>
                                </div>
                                <ul class="space-y-2 text-xs text-slate-200">
                                    <li class="flex items-center gap-2"><i data-lucide="check"
                                            class="w-4 h-4 text-emerald-400 shrink-0"></i> Kasir POS + Resep BOM + Gudang +
                                        Jurnal sudah include</li>
                                    <li class="flex items-center gap-2"><i data-lucide="check"
                                            class="w-4 h-4 text-emerald-400 shrink-0"></i> Toko online mandiri & WA nota
                                        otomatis sudah include</li>
                                    <li class="flex items-center gap-2"><i data-lucide="check"
                                            class="w-4 h-4 text-emerald-400 shrink-0"></i> Nol detik rekonsiliasi karena
                                        database real-time</li>
                                    <li class="flex items-center gap-2"><i data-lucide="check"
                                            class="w-4 h-4 text-emerald-400 shrink-0"></i> Data bisnis milik Anda sepenuhnya
                                        dalam kontrol aman</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. 4 JEBAKAN APLIKASI BISNIS TERPISAH ════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-500">Biaya Tersembunyi</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        4 Dampak Buruk Menggunakan Sistem Terpisah pada Bisnis Anda
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Bukan hanya masalah uang langganan, aplikasi yang tidak terintegrasi menguras energi operasional dan
                        membahayakan kelangsungan usaha.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Point 1 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">1. Kebocoran Modal Akibat Selisih Stok & Kas</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Saat kasir tidak terhubung dengan kartu stok gudang, kehilangan barang sering kali baru disadari
                            berminggu-minggu kemudian saat stock opname. Anda tidak bisa melacak apakah barang hilang,
                            rusak, atau lupa diinput.
                        </p>
                    </div>

                    <!-- Point 2 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">2. Laporan Keuangan Selalu Terlambat Masuk</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pemilik bisnis baru mengetahui apakah toko untung atau rugi di tanggal 15 bulan berikutnya,
                            setelah semua nota kertas diketik ulang. Keputusan bisnis penting jadi terlambat diambil karena
                            data selalu kedaluwarsa.
                        </p>
                    </div>

                    <!-- Point 3 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                            <i data-lucide="users-2" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">3. Karyawan Pusing Menghafal Banyak Akun</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Staf baru harus diajari 3-4 software berbeda: password kasir, password inventory, spreadsheet
                            drive, dan aplikasi perpesanan. Tingkat kesalahan operasional staf meningkat dan onboarding
                            karyawan memakan waktu berminggu-minggu.
                        </p>
                    </div>

                    <!-- Point 4 -->
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="trending-down" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">4. Sulit Buka Cabang Baru dengan Percaya Diri</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Setiap membuka outlet baru, kekacauan operasional berlipat ganda. Pemilik tidak berani
                            berekspansi karena satu cabang saja sudah menyita seluruh waktu dan perhatian akibat kontrol
                            sistem yang rapuh.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. TABEL PERBANDINGAN FITUR LENGKAP ══════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Perbandingan
                        Langsung</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        COOCA vs Kombinasi Aplikasi Konvensional
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-xl mx-auto">
                        Ketahui mengapa ratusan pelaku usaha beralih ke satu sistem operasi terpadu.
                    </p>
                </div>

                <!-- Comparison Table Bento Style -->
                <div
                    class="rounded-[24px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr
                                    class="border-b border-black/[0.08] dark:border-white/[0.1] bg-black/[0.02] dark:bg-white/[0.02]">
                                    <th class="p-4 sm:p-5 font-bold text-neutral-800 dark:text-neutral-200 w-1/3">Kriteria
                                        Penilaian</th>
                                    <th class="p-4 sm:p-5 font-bold text-red-500 w-1/3">Banyak Aplikasi Terpisah</th>
                                    <th
                                        class="p-4 sm:p-5 font-extrabold text-[#007AFF] dark:text-[#00C2FF] w-1/3 bg-blue-50/50 dark:bg-blue-950/20">
                                        COOCA Business OS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium">Biaya Berlangganan</td>
                                    <td class="p-4 sm:p-5 text-neutral-500">Bayar 3-5 vendor berbeda (Rp 1.000.000+ /bln)
                                    </td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10">
                                        1 Paket Terjangkau (Rp 0 - Rp 99rb /bln)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium">Sinkronisasi Kasir & Gudang</td>
                                    <td class="p-4 sm:p-5 text-neutral-500">Manual rekap stok atau pakai API rumit</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10">
                                        Otomatis terpotong saat transaksi selesai</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium">Pencatatan Jurnal Akuntansi</td>
                                    <td class="p-4 sm:p-5 text-neutral-500">Ketik ulang nota bon ke buku kas / Excel</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10">
                                        Auto-Journal debit-kredit secara real-time</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium">Waktu Rekonsiliasi Owner</td>
                                    <td class="p-4 sm:p-5 text-neutral-500">2-3 jam setiap malam menjelang tutup toko</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10">
                                        0 menit (dashboard owner update seketika)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 sm:p-5 font-medium">Dukungan Multi-Outlet / Cabang</td>
                                    <td class="p-4 sm:p-5 text-neutral-500">Biaya tambahan mahal per outlet baru</td>
                                    <td
                                        class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-blue-50/30 dark:bg-blue-950/10">
                                        Satu kontrol pusat untuk seluruh cabang toko</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ SEPUTAR PERBANDINGAN & MIGRASI ════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Tanya
                        Jawab</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Pertanyaan Seputar Beralih ke COOCA
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Ketahui betapa mudahnya memindahkan data bisnis lama Anda ke ekosistem terpadu COOCA.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah saya bisa memindahkan data produk dan pelanggan dari aplikasi lama?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Bisa dengan sangat mudah. COOCA menyediakan template impor Excel (XLSX/CSV) untuk produk, harga
                            jual, stok awal, dan database nomor pelanggan. Cukup salin data dari aplikasi lama Anda dan
                            unggah ke COOCA dalam satu klik.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah saya harus langsung menggunakan semua modul di COOCA sekaligus?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Tidak. COOCA dirancang bertahap (*modular adoption*). Anda dapat memulai hanya dari kasir POS
                            untuk melayani pelanggan. Setelah tim kasir terbiasa, Anda bisa mulai mengaktifkan pencatatan
                            stok gudang, lalu pembukuan otomatis, dan kampanye WhatsApp promosi secara bertahap sesuai
                            kenyamanan Anda.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah ada biaya tersembunyi seperti biaya transaksi atau komisi omzet?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Tidak ada komisi penjualan sama sekali. Seluruh omzet dari toko Anda adalah 100% milik Anda.
                            Biaya layanan COOCA transparan sesuai paket yang Anda pilih, tanpa potongan komisi tersembunyi
                            pada transaksi kasir fisik.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. RELATED MODULES ═══════════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Langkah
                            Cerdas</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight">Pelajari Bagian Lain dari Ekosistem COOCA
                        </h3>
                    </div>
                    <a href="{{ route('public.pricing') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline">
                        <span>Lihat Rincian Biaya</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <a href="{{ route('public.bos.overview') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="cpu" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#007AFF] transition-colors">Overview BOS</div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Konsep dasar sistem
                            operasi bisnis yang menyatukan seluruh toko.</p>
                    </a>

                    <a href="{{ route('public.bos.how-it-works') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="workflow" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#34C759] transition-colors">Cara Kerja Sistem
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Alur data transaksi
                            kasir ke kartu gudang & laporan owner.</p>
                    </a>

                    <a href="{{ route('public.demo') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="play" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#FF9500] transition-colors">Demo Interaktif</div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Uji coba langsung
                            antarmuka kasir dan dasbor tanpa registrasi.</p>
                    </a>

                    <a href="{{ route('public.pricing') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-purple-500/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="tag" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-purple-500 transition-colors">Paket & Harga</div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Pilihan harga
                            terjangkau mulai dari paket Free tanpa komisi.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. BOTTOM CONVERSION CTA ════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden">
                    <div
                        class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#007AFF]/20 rounded-full blur-[140px] pointer-events-none">
                    </div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-8 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">Ambil Kendali Bisnis
                                Anda</span>
                            <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                                Sudah Waktunya Menyatukan Operasional Bisnis Anda
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed">
                                Mulai dari versi gratis hari ini dan rasakan bagaimana rasanya pulang ke rumah dengan tenang
                                karena seluruh kasir, stok, dan kas toko terpantau rapi.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Daftar COOCA Sekarang</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.support') }}"
                                class="w-full py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 transition-all min-h-[44px]">
                                <span>Konsultasi dengan Tim Support</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
