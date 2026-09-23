@extends('layouts.public_marketing')

@section('title', 'Pusat Edukasi & Wawasan Bisnis UMKM | Panduan Finansial, POS, & Otomasi - COOCA')
@section('description', 'Pusat kurikulum, panduan operasional, dan wawasan bisnis UMKM Indonesia. Pelajari strategi arus kas, perhitungan HPP presisi, teknik kasir POS cepat, dan otomasi pelanggan.')
@section('keywords', 'edukasi bisnis umkm, wawasan bisnis indonesia, tips pembukuan toko, belajar hitung hpp, strategi kasir pos, panduan wirausaha mandiri, otomasi whatsapp bisnis')

@push('seo')
    <link rel="canonical" href="{{ route('public.resources.blog') }}">
    <meta property="og:title" content="Pusat Edukasi & Wawasan Bisnis UMKM | COOCA">
    <meta property="og:description" content="Kurikulum bisnis praktis dan panduan operasional toko, kafe, bengkel, serta wirausaha mandiri di Indonesia.">
    <meta property="og:url" content="{{ route('public.resources.blog') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Pusat Edukasi & Wawasan Bisnis UMKM | COOCA">
    <meta name="twitter:description" content="Panduan mendalam pengelolaan arus kas, stok, kasir, dan otomasi operasional untuk pelaku UMKM.">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "CollectionPage",
        "name": "Pusat Edukasi & Wawasan Bisnis UMKM COOCA",
        "description": "Kurikulum dan basis pengetahuan operasional bisnis terstruktur untuk pemilik usaha UMKM Indonesia.",
        "url": "{{ route('public.resources.blog') }}",
        "publisher": {
            "@@type": "Organization",
            "name": "COOCA Indonesia",
            "url": "{{ url('/') }}"
        }
    }
    </script>
@endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300 min-h-screen">

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION: Midnight #060B1E with Ambient Glows ═══════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
            <!-- Dual Ambient Glows -->
            <div class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8">
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-slate-400">Pusat Sumber Daya</span>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Edukasi &amp; Kurikulum Bisnis</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <!-- Left: Headline, Value Proposition & Quick Actions -->
                    <div class="lg:col-span-7 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-sm">
                            <i data-lucide="book-open" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                            <span>Kurikulum &amp; Basis Pengetahuan Operasional UMKM</span>
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words">
                            Kembangkan Usaha Anda dengan <span class="text-[#00C4D8]">Wawasan Finansial &amp; Operasional Nyata.</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl text-pretty font-normal">
                            Menjalankan bisnis bukan sekadar menunggu pembeli datang. Pelajari prinsip pemisahan arus kas, cara tepat menentukan harga pokok penjualan (HPP), teknik memotong antrean kasir, hingga strategi mengikat pelanggan lama.
                        </p>

                        <!-- Tangible Benefit Badges -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                            <div class="p-3 rounded-[14px] bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Bahasa Indonesia praktis tanpa istilah rumit</span>
                            </div>
                            <div class="p-3 rounded-[14px] bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-[8px] bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Studi kasus riil toko, kafe, &amp; bengkel</span>
                            </div>
                        </div>

                        <!-- Direct Buttons -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('blog.index') }}"
                                class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all">
                                <span>Buka Katalog Artikel Terbaru</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="#kurikulum-bisnis"
                                class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] backdrop-blur-sm">
                                <span>Lihat Jalur Belajar</span>
                                <i data-lucide="arrow-down" class="w-4 h-4 text-[#00C4D8]"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Curriculum Roadmap Bento Preview -->
                    <div class="lg:col-span-5">
                        <div
                            class="rounded-[24px] bg-[#0E1E45]/85 border border-white/15 p-6 shadow-2xl backdrop-blur-xl space-y-4 text-white">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#FF5F56]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#FFBD2E]"></span>
                                    <span class="w-3 h-3 rounded-full bg-[#27C93F]"></span>
                                    <span class="text-xs font-mono font-semibold text-slate-300 ml-2">cooca://academy</span>
                                </div>
                                <span class="text-[11px] font-semibold text-[#00C4D8] bg-[#007AFF]/20 border border-[#007AFF]/30 px-2.5 py-0.5 rounded-full">
                                    Akses Bebas
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div class="p-3.5 rounded-[14px] bg-white/[0.05] border border-white/10 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        01
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-white">Fondasi Kas &amp; Pemisahan Dompet</div>
                                        <div class="text-[11px] text-slate-300">Menjaga likuiditas agar modal dagang tidak terpakai</div>
                                    </div>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                </div>

                                <div class="p-3.5 rounded-[14px] bg-white/[0.05] border border-white/10 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs shrink-0">
                                        02
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-white">Kalkulasi HPP &amp; Titik Impas (BEP)</div>
                                        <div class="text-[11px] text-slate-300">Menghitung modal bahan baku, upah kerja, &amp; margin riil</div>
                                    </div>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                </div>

                                <div class="p-3.5 rounded-[14px] bg-white/[0.05] border border-white/10 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-[10px] bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        03
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-white">Otomasi Kasir POS &amp; CRM WhatsApp</div>
                                        <div class="text-[11px] text-slate-300">Pemberitahuan nota digital dan penagihan kasbon tertib</div>
                                    </div>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                </div>
                            </div>

                            <div class="p-3 rounded-[12px] bg-[#007AFF]/15 border border-[#007AFF]/30 text-[#00C4D8] text-xs font-semibold text-center flex items-center justify-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Tersedia untuk Seluruh Mitra Usaha COOCA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. JALUR BELAJAR 4 TAHAP (Learning Path / Curriculum) ═══════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <section id="kurikulum-bisnis" class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                    Tahapan Belajar
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                    4 Langkah Kematangan Bisnis dari Gerai Mandiri ke Multi-Cabang
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                    Kami membagi pembelajaran bisnis menjadi langkah bertahap yang realistis dan dapat dieksekusi oleh siapa pun.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Tahap 1 -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-blue-500/10 text-[#007AFF] font-bold text-xs flex items-center justify-center font-mono">01</span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Fondasi Awal</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Disiplin Arus Kas &amp; Kasir</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Pemisahan rekening pribadi dari saldo toko, pencatatan setiap pengeluaran sekecil apa pun, serta penyeragaman nota kasir.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-white/10 text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8] flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Bebas Kebocoran Uang Laci</span>
                    </div>
                </div>

                <!-- Tahap 2 -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center font-mono">02</span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Presisi Biaya</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Kendali HPP &amp; Stok Resep</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Menghitung modal pokok per porsi produk (Bill of Materials), atur batas minimum stok menipis, dan kurangi bahan kadaluarsa.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-white/10 text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Margin Laba Aman &amp; Terukur</span>
                    </div>
                </div>

                <!-- Tahap 3 -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center font-mono">03</span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Efisiensi Tim</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Otomasi &amp; Retensi Pelanggan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Otomasi pesan WhatsApp status pesanan, pencatatan preferensi pelanggan loyal, dan delegasi tugas kasir tanpa rasa cemas.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-white/10 text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Pelanggan Mengulang Belanja</span>
                    </div>
                </div>

                <!-- Tahap 4 -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-xs flex items-center justify-center font-mono">04</span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Ekspansi</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Multi-Cabang &amp; Kemitraan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Membuka gerai kedua tanpa kehilangan kontrol, membandingkan performa antar outlet, dan transfer stok antar gudang secara tertib.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 dark:border-white/10 text-xs font-semibold text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Bisnis Jalan Sendiri</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. EMPAT PILAR PENGETAHUAN UTAMA (Knowledge Pillars) ════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <section class="bg-slate-50 dark:bg-[#0A0F1E] py-16 sm:py-20 border-y border-slate-200/80 dark:border-white/5">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Pilar Pengetahuan
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                        Koleksi Panduan Terstruktur Sesuai Kebutuhan Anda
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pilar 1: Finansial -->
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div class="w-11 h-11 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Manajemen Finansial &amp; Laporan Laba Rugi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Pahami cara membaca laporan laba rugi sederhana, memisahkan biaya operasional tetap dan variabel, serta menjaga modal kerja aman dari piutang macet pelanggan.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('kalkulator.laba-bersih') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8] inline-flex items-center gap-1.5 hover:underline">
                                <span>Coba Simulasi Laba Bersih</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 2: HPP & Stok -->
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div class="w-11 h-11 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="boxes" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Penetapan Harga Jual &amp; Resep Bahan Baku (BOM)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Hindari jualan laris tapi nombok. Pelajari cara memasukkan takaran gram/ml ke dalam rumus HPP dan perbedaan mendasar antara markup dan margin keuntungan.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('kalkulator.hpp') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8] inline-flex items-center gap-1.5 hover:underline">
                                <span>Hitung HPP Produk Anda</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 3: Kasir POS -->
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div class="w-11 h-11 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Operasional Kasir Kilat &amp; Pairing Perangkat</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Langkah pairing printer struk thermal Bluetooth, tips memproses pesanan saat jam makan siang yang padat, dan prosedur tutup shift kasir tanpa selisih uang fisik.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('public.resources.guides') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8] inline-flex items-center gap-1.5 hover:underline">
                                <span>Buka Panduan Setup Kasir</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 4: WhatsApp & Hubungan Pelanggan -->
                    <div class="p-7 rounded-[24px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div class="w-11 h-11 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Otomasi WhatsApp &amp; Loyalitas Pelanggan</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Cara memanfaatkan pesan WhatsApp resmi untuk mengirimkan nota digital, ucapan terima kasih otomatis, serta pengingat jadwal servis atau promo khusus pelanggan lama.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('public.omnichannel.whatsapp') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#00C4D8] inline-flex items-center gap-1.5 hover:underline">
                                <span>Pelajari Fitur WhatsApp COOCA</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. INTEGRASI TOOLS & RESOURCE LAINNYA (Direct Action Bridge) ════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-8">
            <div class="p-8 sm:p-10 rounded-[28px] bg-[#0E1E45]/80 border border-white/15 text-white shadow-2xl backdrop-blur-xl space-y-6">
                <div class="max-w-2xl space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#00C4D8]">Alat Bantu Langsung</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Koleksi Kalkulator &amp; Template Gratis untuk Toko Anda</h3>
                    <p class="text-sm text-slate-300 leading-relaxed text-pretty">
                        Selain artikel edukasi, kami menyediakan alat hitung instan dan file spreadsheet siap pakai tanpa perlu mendaftar.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ route('blog.index') }}" class="p-4 rounded-[16px] bg-white/[0.05] border border-white/10 hover:bg-white/[0.08] transition flex items-center justify-between gap-3 group">
                        <div class="space-y-1 min-w-0">
                            <div class="text-xs font-bold text-white group-hover:text-[#00C4D8] transition truncate">Katalog Blog Utama</div>
                            <div class="text-[11px] text-slate-400">Arsip seluruh artikel bisnis</div>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-[#00C4D8] shrink-0"></i>
                    </a>

                    <a href="{{ route('kalkulator.index') }}" class="p-4 rounded-[16px] bg-white/[0.05] border border-white/10 hover:bg-white/[0.08] transition flex items-center justify-between gap-3 group">
                        <div class="space-y-1 min-w-0">
                            <div class="text-xs font-bold text-white group-hover:text-[#00C4D8] transition truncate">8 Kalkulator Bisnis</div>
                            <div class="text-[11px] text-slate-400">HPP, BEP, Gaji, PPh 0.5%</div>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-[#00C4D8] shrink-0"></i>
                    </a>

                    <a href="{{ route('template.index') }}" class="p-4 rounded-[16px] bg-white/[0.05] border border-white/10 hover:bg-white/[0.08] transition flex items-center justify-between gap-3 group">
                        <div class="space-y-1 min-w-0">
                            <div class="text-xs font-bold text-white group-hover:text-[#00C4D8] transition truncate">Template Excel Toko</div>
                            <div class="text-[11px] text-slate-400">Download gratis format .xlsx</div>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-[#00C4D8] shrink-0"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. FAQ EDUKASI BISNIS UMKM ══════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24 space-y-6">
            <div class="text-center space-y-2">
                <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya Jawab Edukasi</span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-snug">Pertanyaan Umum Seputar Pengelolaan Bisnis</h2>
            </div>

            <div class="space-y-3.5">
                <details class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                    <summary class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                        <span class="min-w-0 flex-1 leading-snug">Mengapa pemilik UMKM wajib memisahkan uang pribadi dan uang toko?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                    </summary>
                    <p class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                        Mencampurkan uang pribadi dan uang usaha adalah penyebab nomor satu UMKM merasa omzetnya besar namun tidak memiliki sisa kas untuk kulakan bahan baku. Dengan memisahkan rekening dan menetapkan gaji tetap untuk diri sendiri, arus kas usaha terlindungi.
                    </p>
                </details>

                <details class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                    <summary class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                        <span class="min-w-0 flex-1 leading-snug">Bagaimana cara paling mudah menghitung HPP bagi pemula?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                    </summary>
                    <p class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                        Jumlahkan seluruh modal bahan baku yang terpakai untuk satu porsi/satuan produk, lalu tambahkan alokasi upah tenaga kerja dan biaya kemasan/overhead. Anda dapat menggunakan <a href="{{ route('kalkulator.hpp') }}" class="text-[#007AFF] font-semibold underline">Kalkulator HPP COOCA</a> untuk menghitungnya secara otomatis.
                    </p>
                </details>

                <details class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                    <summary class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                        <span class="min-w-0 flex-1 leading-snug">Apakah materi panduan di COOCA dipungut biaya langganan?</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"></i>
                    </summary>
                    <p class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                        Seluruh artikel edukasi, panduan operasional kasir, serta kalkulator bisnis di ekosistem COOCA dapat diakses secara gratis oleh seluruh pengusaha mandiri di Indonesia.
                    </p>
                </details>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. CONVERSION CTA (Midnight Surface) ════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24">
            <div class="relative p-8 sm:p-12 rounded-[24px] bg-[#060B1E] border border-white/10 text-white text-center space-y-5 shadow-2xl overflow-hidden">
                <div class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none"></div>
                <div class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="relative z-10 space-y-4 max-w-2xl mx-auto">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                        <span>Praktekkan Langsung di Usaha Anda</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-[1.2] text-balance break-words">
                        Siap Mempermudah Operasional Toko Hari Ini?
                    </h3>
                    <p class="text-sm text-slate-300 leading-relaxed text-pretty">
                        Terapkan sistem kasir cepat, pemotongan stok otomatis, dan pembukuan jernih tanpa biaya pendaftaran awal.
                    </p>
                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Mulai Coba COOCA Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.resources.case-studies') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm">
                            <i data-lucide="award" class="w-4 h-4 text-amber-400"></i>
                            <span>Baca Kisah Sukses UMKM</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
